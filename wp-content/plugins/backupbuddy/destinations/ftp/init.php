<?php

// DO NOTE CALL THIS CLASS DIRECTLY. CALL VIA: pb_backupbuddy_destination in bootstrap.php.

class pb_backupbuddy_destination_ftp {
	
	public static $destination_info = array(
		'name'			=>		'FTP',
		'description'	=>		'File Transport Protocol. This is the most common way of sending larger files between servers. Most web hosting accounts provide FTP access. This common and well-known transfer method is tried and true.',
	);
	
	// Default settings. Should be public static for auto-merging.
	public static $default_settings = array(
		'type'			=>		'ftp',	// MUST MATCH your destination slug.
		'title'			=>		'',		// Required destination field.
		'address'		=>		'',
		'username'		=>		'',
		'password'		=>		'',
		'path'			=>		'',
		'active_mode'	=>		0,   // 1 = active, 0=passive mode (default > v3.1.8).
		'ftps'			=>		0,
		'archive_limit'	=>		0,
		'url'			=>		'',		// optional url for migration that corresponds to this ftp/path.
	);
	
	
	
	/*	send()
	 *	
	 *	Send one or more files.
	 *	
	 *	@param		array			$files		Array of one or more files to send.
	 *	@return		boolean						True on success, else false.
	 */
	public static function send( $settings = array(), $files = array() ) {
		
		pb_backupbuddy::status( 'details', 'FTP class send() function started.' );
		
		if ( $settings['active_mode'] == '0' ) {
			$active_mode = false;
		} else {
			$active_mode = true;
		}
		$server = $settings['address'];
		$username = $settings['username'];
		$password = $settings['password'];
		$path = $settings['path'];
		$ftps = $settings['ftps'];
		$limit = $settings['archive_limit'];
		$active = $settings['active_mode'];
		
		
		$port = '21'; // Default FTP port.
		if ( strstr( $server, ':' ) ) { // Handle custom FTP port.
			$server_params = explode( ':', $server );
			$server = $server_params[0];
			$port = $server_params[1];
		}
		
		
		// Connect to server.
		if ( $ftps == '1' ) { // Connect with FTPs.
			if ( function_exists( 'ftp_ssl_connect' ) ) {
				$conn_id = ftp_ssl_connect( $server, $port );
				if ( $conn_id === false ) {
					pb_backupbuddy::status( 'details',  'Unable to connect to FTPS  `' . $server . '` on port `' . $port . '` (check address/FTPS support and that server can connect to this address via this port).', 'error' );
					return false;
				} else {
					pb_backupbuddy::status( 'details',  'Connected to FTPs.' );
				}
			} else {
				pb_backupbuddy::status( 'details',  'Your web server doesnt support FTPS in PHP.', 'error' );
				return false;
			}
		} else { // Connect with FTP (normal).
			if ( function_exists( 'ftp_connect' ) ) {
				$conn_id = ftp_connect( $server, $port );
				if ( $conn_id === false ) {
					pb_backupbuddy::status( 'details',  'ERROR: Unable to connect to FTP server `' . $server . '` on port `' . $port . '` (check address and that server can connect to this address via this port).', 'error' );
					return false;
				} else {
					pb_backupbuddy::status( 'details',  'Connected to FTP.' );
				}
			} else {
				pb_backupbuddy::status( 'details',  'Your web server doesnt support FTP in PHP.', 'error' );
				return false;
			}
		}
		
		
		// Log in.
		$login_result = @ftp_login( $conn_id, $username, $password );
		if ( $login_result === false ) {
			pb_backupbuddy::$classes['core']->mail_error( 'ERROR #9011 ( http://ithemes.com/codex/page/BackupBuddy:_Error_Codes#9011 ).  FTP/FTPs login failed on scheduled FTP.' );
			return false;
		} else {
			pb_backupbuddy::status( 'details',  'Logged in. Sending backup via FTP/FTPs ...' );
		}
		
		
		if ( $active_mode === true ) {
			// do nothing, active is default.
			pb_backupbuddy::status( 'details', 'Active FTP mode based on settings.' );
		} elseif ( $active_mode === false ) {
			// Turn passive mode on.
			pb_backupbuddy::status( 'details', 'Passive FTP mode based on settings.' );
			ftp_pasv( $conn_id, true );
		} else {
			pb_backupbuddy::status( 'error', 'Unknown FTP active/passive mode: `' . $active_mode . '`.' );
		}
		
		
		// Create directory if it does not exist.
		@ftp_mkdir( $conn_id, $path );
		
		
		// Change to directory.
		pb_backupbuddy::status( 'details', 'Entering FTP directory `' . $path . '`.' );
		ftp_chdir( $conn_id, $path );
		
		// Upload files.
		foreach( $files as $file ) {
			
			if ( ! file_exists( $file ) ) {
				pb_backupbuddy::status( 'error', 'Error #859485495. Could not upload local file `' . $file . '` to send to FTP as it does not exist. Verify the file exists, permissions of file, parent directory, and that ownership is correct. You may need suphp installed on the server.' );
			}
			if ( ! is_readable( $file ) ) {
				pb_backupbuddy::status( 'error', 'Error #8594846548. Could not read local file `' . $file . '` to sendto FTP as it is not readable. Verify permissions of file, parent directory, and that ownership is correct. You may need suphp installed on the server.' );
			}
			
			$destination_file = $path . '/' . basename( $file );
			pb_backupbuddy::status( 'details', 'About to put to FTP local file `' . $file . '` to remote file `' . $destination_file . '`.' );
			$upload = ftp_put( $conn_id, $destination_file, $file, FTP_BINARY );
			if ( $upload === false ) {
				$error_message = 'ERROR #9012 ( http://ithemes.com/codex/page/BackupBuddy:_Error_Codes#9012 ).  FTP/FTPs file upload failed. Check file permissions & disk quota.';
				pb_backupbuddy::status( 'error',  $error_message );
				pb_backupbuddy::$classes['core']->mail_error( $error_message );
				
				return false;
			} else {
				pb_backupbuddy::status( 'details',  'Success completely sending `' . basename( $file ) . '` to destination.' );
				
				
				// Start remote backup limit
				if ( $limit > 0 ) {
					pb_backupbuddy::status( 'details', 'Getting contents of backup directory.' );
					ftp_chdir( $conn_id, $path );
					$contents = ftp_nlist( $conn_id, '' );
					
					// Create array of backups
					$bkupprefix = pb_backupbuddy::$classes['core']->backup_prefix();
					
					$backups = array();
					foreach ( $contents as $backup ) {
						// check if file is backup
						$pos = strpos( $backup, 'backup-' . $bkupprefix . '-' );
						if ( $pos !== FALSE ) {
							array_push( $backups, $backup );
						}
					}
					arsort( $backups ); // some ftp servers seem to not report in proper order so reversing insufficiently reliable. need to reverse sort by filename. array_reverse( (array)$backups );
					
					
					if ( ( count( $backups ) ) > $limit ) {
						$delete_fail_count = 0;
						$i = 0;
						foreach( $backups as $backup ) {
							$i++;
							if ( $i > $limit ) {
								if ( !ftp_delete( $conn_id, $backup ) ) {
									pb_backupbuddy::status( 'details', 'Unable to delete excess FTP file `' . $backup . '` in path `' . $path . '`.' );
									$delete_fail_count++;
								}
							}
						}
						if ( $delete_fail_count !== 0 ) {
							pb_backupbuddy::$classes['core']->mail_error( sprintf( __('FTP remote limit could not delete %s backups. Please check and verify file permissions.', 'it-l10n-backupbuddy' ), $delete_fail_count  ) );
						}
					}
				} else {
					pb_backupbuddy::status( 'details',  'No FTP file limit to enforce.' );
				}
				// End remote backup limit
			}
			
		} // end $files loop.
		
		
		ftp_close( $conn_id );
		
		return true;
		
		
	} // End send().
	
	
	
	/*	test()
	 *	
	 *	function description
	 *	
	 *	@param		array			$settings	Destination settings.
	 *	@return		bool|string					True on success, string error message on failure.
	 */
	public static function test( $settings ) {
		
		$server = $settings['address'];
		$username = $settings['username'];
		$password = $settings['password'];
		$path = $settings['path'];
		$ftps = $settings['ftps'];
		if ( $settings['active_mode'] == '0' ) {
			$active_mode = false;
		} else {
			$active_mode = true;
		}
		$url = $settings['url']; // optional url for using with migration.
		
				
		if ( ( $server == '' ) || ( $username == '' ) || ( $password == '' ) ) {
			return __('Missing required input.', 'it-l10n-backupbuddy' );
		}
		
		$port = '21';
		if ( strstr( $server, ':' ) ) {
			$server_params = explode( ':', $server );
			
			$server = $server_params[0];
			$port = $server_params[1];
		}
		
		if ( $ftps == '0' ) {
			$conn_id = @ftp_connect( $server, $port, 10 ); // timeout of 10 seconds.
			if ( $conn_id === false ) {
				$error = __( 'Unable to connect to FTP address `' . $server . '` on port `' . $port . '`.', 'it-l10n-backupbuddy' );
				$error .= "\n" . __( 'Verify the server address and port (default 21). Verify your host allows outgoing FTP connections.', 'it-l10n-backupbuddy' );
				return $error;
			}
		} else {
			if ( function_exists( 'ftp_ssl_connect' ) ) {
				$conn_id = @ftp_ssl_connect( $server, $port );
				if ( $conn_id === false ) {
					return __('Destination server does not support FTPS?', 'it-l10n-backupbuddy' );
				}
			} else {
				return __('Your web server doesnt support FTPS.', 'it-l10n-backupbuddy' );
			}
		}
		
		$login_result = @ftp_login( $conn_id, $username, $password );
		
		if ( ( !$conn_id ) || ( !$login_result ) ) {
			pb_backupbuddy::status( 'details', 'FTP test: Invalid user/pass.' );
			$response = __('Unable to login. Bad user/pass.', 'it-l10n-backupbuddy' );
			if ( $ftps != '0' ) {
				$response .= "\n\nNote: You have FTPs enabled. You may get this error if your host does not support encryption at this address/port.";
			}
			return $response;
		}
		
		pb_backupbuddy::status( 'details', 'FTP test: Success logging in.' );
		
		
		if ( $active_mode === true ) {
			// do nothing, active is default.
			pb_backupbuddy::status( 'details', 'Active FTP mode based on settings.' );
		} elseif ( $active_mode === false ) {
			// Turn passive mode on.
			pb_backupbuddy::status( 'details', 'Passive FTP mode based on settings.' );
			ftp_pasv( $conn_id, true );
		} else {
			pb_backupbuddy::status( 'error', 'Unknown FTP active/passive mode: `' . $active_mode . '`.' );
		}
		
		
		// Create directory if it does not exist.
		pb_backupbuddy::status( 'details', 'FTP test: Making directory.' );
		@ftp_mkdir( $conn_id, $path );
	
		pb_backupbuddy::status( 'details', 'FTP test: Uploading temp test file.' );
		$tmp = tmpfile(); // Write tempory text file to stream.
		fwrite( $tmp, 'Upload test for BackupBuddy' );
		rewind( $tmp );
		$upload = @ftp_fput( $conn_id, $path . '/backupbuddy.txt', $tmp, FTP_BINARY );
		fclose( $tmp );
		
		if ( !$upload ) {
			pb_backupbuddy::status( 'details', 'FTP test: Failure uploading test file.' );
			@ftp_delete( $conn_id, $path . '/backupbuddy.txt' ); // Just in case it partionally made file. This has happened oddly.
			return __('Failure uploading. Check path & permissions.', 'it-l10n-backupbuddy' );
		} else { // File uploaded.
			
			
			if ( $url != '' ) {
				$response = wp_remote_get( $url . '/backupbuddy.txt', array(
						'method' => 'GET',
						'timeout' => 20,
						'redirection' => 5,
						'httpversion' => '1.0',
						'blocking' => true,
						'headers' => array(),
						'body' => null,
						'cookies' => array()
					)
				);
								
				if ( is_wp_error( $response ) ) {
					return __( 'Failure. Unable to connect to the provided optional URL.', 'it-l10n-backupbuddy' );
				}
				
				if ( stristr( $response['body'], 'backupbuddy' ) === false ) {
					return __('Failure. The path appears valid but the URL does not correspond to it. Leave the URL blank if not using this destination for migrations.', 'it-l10n-backupbuddy' );
				}
			}
			
			
			pb_backupbuddy::status( 'details', 'FTP test: Deleting temp test file.' );
			ftp_delete( $conn_id, $path . '/backupbuddy.txt' );
		}
		
		pb_backupbuddy::status( 'details', 'FTP test: Closing FTP connection.' );
		@ftp_close($conn_id);
		
		return true; // Success if we got this far.
	} // End test().
	
	
} // End class.