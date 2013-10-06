<?php

/**
* @file   index.php
* @brief  The main script that executes the libraries GIT.php and FTP.php.
* @date   2013-10-06 15:06:00
* @license http://opensource.org/licenses/GPL-3.0 GNU Public License
* @company: Tipui Co. Ltda.
* @author: Daniel Omine <omine@tipui.com>
* @updated: 2013-10-06 15:06:00
*
* Git: https://github.com/tipui/PHP-GIT-FTP
*/

/**
* Date and time zone.
* @see http://php.net/date_default_timezone_set
*/
date_default_timezone_set('Asia/Tokyo');

/**
* Avoid long executions.
* Define the limit as your needs
*/
set_time_limit(15);

/**
* General constants
*/
define( 'CHARSET', 'UTF-8' );
define( 'BASE_DIR', dirname( __FILE__ ) . DIRECTORY_SEPARATOR );

/**
* Optional PHP settings.
*/
ini_set( 'display_errors', 1 );
ini_set( 'default_charset', CHARSET );
ini_set( 'mbstring.http_output', CHARSET );
ini_set( 'mbstring.internal_encoding', CHARSET );

/**
* Optional to send page charset infromation.
*/
header( 'Content-Type: text/html; charset=' . CHARSET );

/**
* Load settings file
*/
require_once BASE_DIR . 'settings.php';

?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html lang="english" xmlns="http://www.w3.org/1999/xhtml">
<head>
<title>TIPUI PHP GIT changes to FTP</title>
<meta name="description" content="Sincronizes GIT changed files and folders with FTP." />
<meta name="owner" content="tipui.com" />
<meta name="author" content="Daniel Omine" />
<meta name="rating" content="general" />
<meta name="robots" content="noindex,nofollow,noarchive" />
<meta name="language" content="english" />
<link rel="stylesheet" type="text/css" href="css/reset.css" />
<link rel="stylesheet" type="text/css" href="css/commons.css" />
<link rel="stylesheet" type="text/css" href="css/layout.css" />
<link rel="stylesheet" type="text/css" href="css/code.css" />
<link rel="stylesheet" type="text/css" href="css/form.css" />
</head>
<body>

<div id="container_top" class="container_default_width ha_center va_middle block">
	<div class="container_padding">
	<div id="container_top_header" class="fl_left"><a href="./">TIPUI GIT FTP</a></div>
	<div id="container_top_slogan" class="fl_right"><a class="over_blue" href="https://github.com/tipui/Tipui-GIT-FTP" target="_blank">github</a></div>
	</div><br clear="all" />
</div>
<div id="container_middle" class="container_default_width">
<div class="container_padding ha_left">

<form action="./" method="POST">
<div class="tb">
	<div class="tb_row">
	<div class="tb_cell ha_right FormInputLabel">FTP Host</div><div class="tb_cell"><input class="FormInput" type="text" name="ftp_host" value="<?php echo $ftp_host;?>" size="15" / ></div>
	</div>
	<div class="tb_row">
	<div class="tb_cell ha_right FormInputLabel">FTP User</div><div class="tb_cell"><input class="FormInput" type="password" name="ftp_user" value="<?php echo $ftp_user;?>" size="15" / ></div>
	</div>
	<div class="tb_row">
	<div class="tb_cell ha_right FormInputLabel">FTP Pass</div><div class="tb_cell"><input class="FormInput" type="password" name="ftp_pass" value="<?php echo $ftp_pass;?>" size="15" / ></div>
	</div>
	<div class="tb_row">
	<div class="tb_cell ha_right FormInputLabel">FTP Remote base</div><div class="tb_cell"><input class="FormInput" type="text" name="remote_base" value="<?php echo $remote_base;?>" size="30" / ></div>
	</div>
	<div class="tb_row">
	<div class="tb_cell ha_right FormInputLabel">Local base (local machine)</div><div class="tb_cell"><input class="FormInput" type="text" name="local_base" value="<?php echo $local_base;?>" size="30" / ></div>
	</div>
	<div class="tb_row">
	<div class="tb_cell ha_right FormInputLabel">Git changes file (local machine)</div><div class="tb_cell"><input class="FormInput" type="text" name="queue_file" value="<?php echo $queue_file;?>" size="30" / ></div>
	</div>
	<div class="tb_row"><div class="tb_cell"></div><div class="tb_cell ha_right"><input type="submit" value=" send " class="BtnGo" /></div></div>
</div>

</form>

<br /><br /><br />

<?php
$fields = array( 'ftp_host', 'ftp_user', 'ftp_pass', 'remote_base', 'local_base', 'queue_file' );

if( isset( $_POST ) and count( $_POST ) == count( $fields ) )
{

	$error  = false;

	foreach( $fields as $k )
	{
		if( isset( $_POST[$k] ) and trim( $_POST[$k] ) != '' )
		{
			$$k = trim( $_POST[$k] );
		}else{
			$error[$k] = true;
		}
	}

	if( $error )
	{
		echo PHP_EOL . '<div class="code_returns ha_left">';
		foreach( $error as $k => $v )
		{
			echo '[!] "' . $k . '" field is required.<br />' . PHP_EOL;
		}
		echo PHP_EOL . '</div>';

	}else{

		/**
		* Load GIT library file
		*/
		require_once BASE_DIR . 'GIT.php';

		/**
		* Load FTP library file
		*/
		require_once BASE_DIR . 'FTP.php';

		/**
		* Starts GIT instance.
		*/
		$git = new \Tipui\Builtin\Libs\GIT;

		/**
		* Prepares and execute FTP queue.
		*/
		$rs = $git -> FTP( 
						$ftp_host, 
						$ftp_user, 
						$ftp_pass, 
						$queue_file, 
						$local_base, 
						$remote_base
					);

		/**
		* Clear GIT instance.
		*/
		unset( $git );
		
		echo PHP_EOL . '<div class="code_returns ha_left">';

		if( !$rs -> error )
		{
			/**
			* Check if FTP login failed or not.
			*/
			if( $rs -> Logged )
			{

				/**
				* Display results
				*/
				if( $rs -> rs['results'] )
				{
					echo 'OK:';
					foreach( $rs -> rs['results'] as $k => $v )
					{
						echo PHP_EOL . '<br />[' .  $v[0] . '] ' . $v[1];
					}
					echo PHP_EOL . PHP_EOL . '<br /><br />';
				}

				/**
				* Display errors
				*/
				if( $rs -> rs['errors'] )
				{
					echo 'ERRORS:';
					foreach( $rs -> rs['errors'] as $k => $v )
					{
						echo PHP_EOL . '<br />[' .  $v[0] . '] ' . $v[1];

						/**
						* Display unexpected errors
						*/
						if( isset( $v[2] ) )
						{
							echo ' ![' . $v[2] . ']';
						}
					}
				}

			}

		}else{

			echo PHP_EOL . '<br />GIT::FTP Error Code: ' . $rs -> error;
			echo PHP_EOL . '<br />Message: ' . $rs -> error_message;

		}

		unset( $rs );

		echo PHP_EOL . '</div>';

	}

}
?>


</div>

</div>
<div id="container_footer" class="container_default_width ha_middle va_middle">
	<div id="footer_notice" class="container_padding">&reg; <a class="over_blue" href="http://tipui.com" target="_blank">Tipui.com</a></div>
</div>
</body>
</html>