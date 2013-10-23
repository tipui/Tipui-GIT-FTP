<?php

/**
* @class  FTP
* @file   FTP.php
* @brief  FTP functions.
* @date   2013-10-05 22:13:00
* @license http://opensource.org/licenses/GPL-3.0 GNU Public License
* @company: Tipui Co. Ltda.
* @author: Daniel Omine <omine@tipui.com>
* @updated: 2013-10-05 22:13:00
*/

namespace Tipui\Builtin\Libs;

/**
* FTP manipulation classes
*/
class FTP
{

	/**
	* Set properties
	*/
	const ADDED    = 'A';
	const MODIFIED = 'M';
	const DELETED  = 'D';

	private $conn;
	private $host;
	private $user;
	private $pass;

	/**
	* Set properties
	*/
	public function __construct( $host, $user, $pass )
	{
		$this -> host = $host;
		$this -> user = $user;
		$this -> pass = $pass;
	}

	/**
	* Reset properties
	*/
	public function __destruct()
	{
		$this -> conn = null;
		$this -> user = null;
		$this -> user = null;
		$this -> pass = null;
	}

	/**
	* Set connection
	*/
	public function Connect()
	{
		$this -> conn = ftp_connect( $this -> host );
	}

	/**
	* Close connection
	*/
	public function Close()
	{
		ftp_close( $this -> conn );
	}

	/**
	* Performs authentication
	*/
	public function Login()
	{
		return @ftp_login( $this -> conn, $this -> user, $this -> pass );
	}

	/**
	* Set passive mode
	*/
	public function PassiveMode( $mode = true )
	{
		return @ftp_pasv( $this -> conn, $mode );
	}

	/**
	* Upload file
	* Do not check if $local_path exists.
	*/
	public function Send( $remote_path, $local_path, $mode = FTP_ASCII )
	{
		return @ftp_put( $this -> conn, $remote_path, $local_path, $mode );
	}

	/**
	* Delete file
	*/
	public function Delete( $path )
	{
		return @ftp_delete( $this -> conn, $path );
	}

	/**
	* Delete directory
	*/
	public function DeleteDir( $path )
	{
		return @ftp_rmdir( $this -> conn, $path );
	}

	/**
	* Creates folders and subfolders recursively
	*/
	public function MakeDirRecursively( $ftpbasedir, $ftpath )
	{

		if( ftp_chdir( $this -> conn, $ftpbasedir ) )
		{

			$parts = explode( '/', $ftpath );

			foreach( $parts as $part )
			{
				if( !@ftp_chdir( $this -> conn, $part ) )
				{
					ftp_mkdir( $this -> conn, $part );
					ftp_chdir( $this -> conn, $part );
					//ftp_chmod( $this -> conn, 0777, $part );
				}
			}

			ftp_chdir( $this -> conn, $ftpbasedir );
		}

	}

	/**
	* Delete folders and subfolders recursively
	*/
	public function DeleteDirRecursively( $path )
	{

		/**
		* Technique (workaround) to check if is folder or not.
		* If is a folder, will return boolen false and proceed the others operations.
		*/
		if( @ftp_delete( $this -> conn, $path ) === false )
		{
	
			if( $children = @ftp_nlist( $this -> conn, $path ) )
			{
				/**
				* Debug purposes
				*/
				//print_r( $children ); //exit;

				/**
				* Reading the list of files and folders if exists
				*/
				foreach( $children as $p )
				{

					/**
					* Avoid to change directory under the targeted path
					*/
					if( substr( $p, -1 ) == '.' or $p == '.' )
					{
						// ignore
					}else{
						/**
						* Do the recursivity
						*/
						$this -> DeleteDirRecursively( $p );
					}

				}

			}

			/**
			* Remove the current path and subpaths, each one on own time.
			*/
			if( !@ftp_rmdir( $this -> conn, $path ) )
			{
				return false;
			}

		}

		return true;

	}

	/**
	* Queue execution
	*
	* @param $queue array(
						array( 'M', 'folder/subfolder/file.txt' ),
						array( 'D', 'folder/file.txt' ),
						array( 'A', 'file.txt' ),
					)
	*
	* Local base of files. The files and folders to be uploaded.
	* @param $local_base D:\www\foo.bar\app\
	*
	* Remote base of files. Where the files will be uploaded.
	* @param $remote_base /var/www/vhosts/foo.bar/app/
	*/
	public function QueueExec( $queue, $local_base, $remote_base )
	{

		/**
		* Result handlers
		*/
		$rs    = false; // positive results
		$error = false; // errors

		/**
		* Looping the list of actions
		* Add / Remove files and folders
		*/
		foreach( $queue as $k => $v )
		{

			/**
			* Sets action codes to uppercase letters.
			* A, M, D...
			*/
			$v[0] = strtoupper( $v[0] );

			/**
			* Sets the local and remote paths.
			*/
			$local_path  = $local_base . str_replace( '/', DIRECTORY_SEPARATOR, $v[1] );
			$remote_path = $remote_base . $v[1];

			/**
			* Debug purposes.
			*/
			//echo PHP_EOL . '<br />Local path: ' . $local_path;
			//echo PHP_EOL . '<br />Remote path: ' . $remote_path;

			$subpath = $v[1];

			/**
			* Important to avoid bypass the function is_file() bellow.
			* The problem is, when the file not exists locally, the method MakeDirRecursively() will creates like a folder.
			*/
			$local_path_not_found_bypass = false;
			if( !file_exists( $local_path ) )
			{
				if( $v[0] == self::DELETED )
				{
					$local_path_not_found_bypass = ( substr( $v[1], -4, 1 ) == '.' ) ? 'file' : 'folder';
				}else{
					$error[$k] = array( $v[0], $v[1], 'local path not found' );
					continue;
				}
			}

			/**
			* Important, must check if file exists. The script above do.
			*/
			if( is_file( $local_path ) or $local_path_not_found_bypass == 'file' )
			{
				$subpath = dirname( $subpath );

				/**
				* Flag to identify that the local path is a file.
				*/
				$is_file = true;
				//echo PHP_EOL . '<br />File folder: ' . $subpath;
			}else{
				/**
				* Flag to identify that the local path is NOT a file.
				*/
				$is_file = false;
			}

			/**
			* Debug purposes.
			*/
			//echo PHP_EOL . '<br />subpath: ' . $subpath;
			//exit;

			/**
			* Creates subdirectories/subfolders.
			* Cases
			* Add new file in /folder/subfolder/foo.txt, but the subfolder do not exists.
			* The method MakeDirRecursively() do the job to check if folders and subfolders exists or not. If not exists, creates them to finally, send the file.
			*/
			$this -> MakeDirRecursively( $remote_base, $subpath );

			switch( true )
			{
				/**
				* Send file (modified or new added)
				*/
				case ( $is_file and $v[0] == self::MODIFIED ):
				case ( $is_file and $v[0] == self::ADDED ):

					if( $this -> Send( $remote_path, $local_path ) )
					{
						/**
						* ok, uploaded
						*/
						$rs[$k] = array( $v[0], $v[1] );
					}else{
						//echo PHP_EOL . '<br />There was a problem while uploading ' .  $v[1];
						$error[$k] = array( $v[0], $v[1] );
					}

				break;

				case ( $v[0] == self::DELETED ):
					/**
					* Delete file
					*/
					if( $is_file )
					{
						if( $this -> Delete( $remote_path ) )
						{
							/**
							* ok, delete
							*/
							$rs[$k] = array( $v[0], $v[1] );
						}else{
							//echo PHP_EOL . '<br />There was a problem while deleting ' .  $v[1];
							$error[$k] = array( $v[0], $v[1] );
						}
					}else{
						/**
						* Delete dir
						*/
						//echo PHP_EOL . '<br /><br /> aaa: ' . $remote_path; exit;
						if( !$this -> DeleteDir( $remote_path ) )
						{
							if( $this -> DeleteDirRecursively( $remote_path ) )
							{
								/**
								* ok, deleted
								*/
								$rs[$k] = array( $v[0], $v[1] );
							}else{
								//echo PHP_EOL . '<br />There was a problem while deleting ' .  $v[1];
								$error[$k] = array( $v[0], $v[1] );
							}
						}else{
							/**
							* ok, deleted
							*/
							$rs[$k] = array( $v[0], $v[1] );
						}
					}

				break;

				default:
					//echo PHP_EOL . '<br />Unknow command "' . $v[0] . '" for "' .  $v[1] . '"';
					$error[$k] =  array( $v[0], $v[1], 'unknow command' );
				break;
			}

		}

		return array( 'results' => $rs, 'errors' => $error );
	}
}