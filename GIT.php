<?php

/**
* @class  FTP
* @file   FTP.php
* @brief  FTP functions.
* @date   2013-10-06 00:33:00
* @license http://opensource.org/licenses/GPL-3.0 GNU Public License
* @company: Tipui Co. Ltda.
* @author: Daniel Omine <omine@tipui.com>
* @updated: 2013-10-06 00:33:00
*/

namespace Tipui\Builtin\Libs;

use \Tipui\Builtin\Libs\FTP as FTP;

/**
* GIT manipulation classes
*/
class GIT
{

	/**
	* Prepares the GIT whatchanged command results to FTP library queue format.
	* @see \Tipui\Builtin\Libs\FTP::QueueExec()
	*/
	public function WhatChangedListToFTPQueue( $file )
	{

		/**
		* Load file content and convert to array.
		* Each new line is an array index.
		*/
		$queue = explode( "\n", file_get_contents( $file ) );

		/**
		* Removes duplicated values.
		*/
		$queue = array_unique( $queue );

		/**
		* Debug purposes
		*/
		//print_r( $queue ); exit;

		/**
		* The GIT format must be the following:
		*
			A	folder/subfolder/file.txt
			D	folder/file.txt
		*
		*
		* The script bellow will convert to array format
		*
		* array(
				array( 'M', 'folder/subfolder/file.txt' ),
				array( 'D', 'folder/file.txt' ),
				array( 'A', 'file.txt' ),
			)
		*/
		foreach( $queue as $k => $v )
		{
			if( substr( $v, 1, 1 ) != "\t" )
			{
				unset( $queue[$k] );
			}else{
				if( !empty( $v ) )
				{
					$queue[$k] = explode( "\t", $v );
				}
			}
		}

		return $queue;

	}

	/**
	* Prepares FTP connection.
	*/
	public function FTP( $host, $user, $pass, $queue_file, $local_base, $remote_base )
	{

		/**
		* Instantiates an StdClass
		*/
		$c = new \StdClass;

		/**
		* Starts error to false.
		*/
		$c -> error = false;

		/**
		* Check if queue file exists
		*/
		if( !file_exists( $queue_file ) )
		{
			$c -> error         = 1.0;
			$c -> error_message = 'Queue file not exists in "' . $queue_file . '"';
			return $c;
		}else{
			/**
			* Check if queue file is a file
			*/
			if( !is_file( $queue_file ) )
			{
				$c -> error         = 1.1;
				$c -> error_message = 'Queue file must be a valid file "' . $queue_file . '"';
				return $c;
			}
		}

		/**
		* Check if local base exists
		*/
		if( !file_exists( $local_base ) )
		{
			$c -> error         = 2.0;
			$c -> error_message = 'Local base not exists in "' . $local_base . '"';
			return $c;
		}else{
			/**
			* Check if local base is a directory
			*/
			if( !is_dir( $local_base ) )
			{
				$c -> error         = 2.1;
				$c -> error_message = 'Local base must be a valid folder "' . $local_base . '"';
				return $c;
			}
		}

		/**
		* Starts FTP instance.
		*/
		$ftp = new FTP( $host, $user, $pass );

		/**
		* Stablishing connection.
		*/
		$ftp -> Connect();

		/**
		* Authenticate.
		*/
		if( $ftp -> Login() )
		{

			/**
			* Logged into FTP
			*/
			$c -> Logged = true;

			/**
			* true: enables passive mode.
			* false: disables passive mode.
			*/
			$ftp -> PassiveMode();

			/**
			* Executes the actions list
			* @see self::WhatChangedListToFTPQueue()
			*/
			$c -> rs = $ftp -> QueueExec( $this -> WhatChangedListToFTPQueue( $queue_file ), $local_base, $remote_base );

		}else{

			/**
			* Couldn't connect/login
			*/
			$c -> Logged = false;

		}

		/**
		* Close FTP and clear the object instance.
		*/
		$ftp -> Close();
		unset( $ftp );

		/**
		* Returns the StdClass object
		*/
		return $c;
	}

}