<?php

class pb_backupbuddy_cron extends pb_backupbuddy_croncore {
	
	function process_backup( $serial = 'blank' ) {
		pb_backupbuddy::set_status_serial( $serial );
		pb_backupbuddy::status( 'details', '--- New PHP process.' );
		pb_backupbuddy::set_greedy_script_limits();
		pb_backupbuddy::status( 'message', 'Processing cron step for serial `' . $serial . '`...' );
		
		if ( !isset( pb_backupbuddy::$classes['backup'] ) ) {
			require_once( pb_backupbuddy::plugin_path() . '/classes/backup.php' );
			pb_backupbuddy::$classes['backup'] = new pb_backupbuddy_backup();
		}
		pb_backupbuddy::$classes['backup']->process_backup( $serial );
	}
	
	
	
	// Cleanup final remaining bits post backup. Handled here so log file can be accessed by AJAX temporarily after backup.
	// Also called when finished_backup action is seen being sent to AJAX signalling we can clear it NOW since AJAX is done.
	// Also pre_backup() of backup.php schedules this 6 hours in the future of the backup in case of failure.
	public function final_cleanup( $serial ) {
		
		if ( !isset( pb_backupbuddy::$classes['core'] ) ) {
			require_once( pb_backupbuddy::plugin_path() . '/classes/core.php' );
			pb_backupbuddy::$classes['core'] = new pb_backupbuddy_core( $serial );
		}
		
		pb_backupbuddy::$classes['core']->final_cleanup( $serial );
		
	} // End final_cleanup().
	
	
	
	/* remote_send()
	 *
	 * Advanced cron-based remote file sending.
	 *
	 * @param	int		$destination_id		Numeric array key for remote destination to send to.
	 * @param	string	$backup_file		Full file path to file to send.
	 * @param	string	$trigger			Trigger of this cron event. Valid values: scheduled, manual
	 *
	 */
	public function remote_send( $destination_id, $backup_file, $trigger, $send_importbuddy = false, $delete_after = false ) {
		pb_backupbuddy::set_greedy_script_limits();
		
		if ( ( '' == $backup_file ) && ( $send_importbuddy ) ) {
			pb_backupbuddy::status( 'message', 'Only sending ImportBuddy to remote destination `' . $destination_id . '`.' );
		} else {
			pb_backupbuddy::status( 'message', 'Sending `' . $backup_file . '` to remote destination `' . $destination_id . '`. Importbuddy?: `' . $send_importbuddy . '`. Delete after?: `' . $delete_after . '`.' );
		}
		
		if ( !isset( pb_backupbuddy::$options ) ) {
			pb_backupbuddy::load();
		}
		
		
		pb_backupbuddy::status( 'details', 'Launching remote send via cron.' );
		if ( !isset( pb_backupbuddy::$classes['core'] ) ) {
			require_once( pb_backupbuddy::plugin_path() . '/classes/core.php' );
			pb_backupbuddy::$classes['core'] = new pb_backupbuddy_core();
		}
		pb_backupbuddy::$classes['core']->send_remote_destination( $destination_id, $backup_file, $trigger, $send_importbuddy, $delete_after );
	} // End remote_send().
	
	
	
	/*	destination_send()
	 *	
	 *	Straight-forward send file(s) to a destination. Pass full array of destination settings.
	 *	NOTE: DOES NOT SUPPORT MULTIPART. SEE remote_send() ABOVE!
	 *	
	 *	@param		array		$destination_settings		All settings for this destination for this action.
	 *	@param		array		$files						Array of files to send (full path).
	 *	@return		null
	 */
	public function destination_send( $destination_settings, $files ) {
		
		pb_backupbuddy::status( 'details', 'Launching destination send via cron.' );
		if ( !isset( pb_backupbuddy::$classes['core'] ) ) {
			require_once( pb_backupbuddy::plugin_path() . '/classes/core.php' );
			pb_backupbuddy::$classes['core'] = new pb_backupbuddy_core();
		}
		pb_backupbuddy::$classes['core']->destination_send( $destination_settings, $files );
		
	} // End destination_send().
	
	
	
	// TODO: Merge into v3.1 destinations system in destinations directory.
	// Copy a remote S3 backup to local backup directory
	// $ssl boolean
	function process_s3_copy( $s3file, $accesskey, $secretkey, $bucket, $directory, $ssl ) {
		pb_backupbuddy::status( 'details', 'Copying remote S3 file `' . $s3file . '` down to local.' );
		pb_backupbuddy::set_greedy_script_limits();
		
		require_once( pb_backupbuddy::plugin_path() . '/destinations/s3/lib/s3.php');
		$s3 = new pb_backupbuddy_S3( $accesskey, $secretkey, $ssl );
		
		$destination_file = pb_backupbuddy::$options['backup_directory'] . $s3file;
		if ( file_exists( $destination_file ) ) {
			$destination_file = str_replace( 'backup-', 'backup_copy_' . pb_backupbuddy::random_string( 5 ) . '-', $destination_file );
		}
		
		pb_backupbuddy::status( 'details', 'About to get S3 object...' );
		$s3->getObject($bucket, $directory . $s3file, $destination_file );
		pb_backupbuddy::status( 'details', 'S3 object retrieved.' );
	} // End process_s3_copy().
	
	
	
	/*	process_remote_copy()
	 *	
	 *	Copy a file from a remote destination down to local.
	 *	
	 *	@param		$destination_type	string		Slug of destination type.
	 *	@param		$file				string		Remote file to copy down.
	 *	@param		$settings			array		Remote destination settings.
	 *	@return		bool							true on success, else false.
	 */
	function process_remote_copy( $destination_type, $file, $settings ) {
		pb_backupbuddy::status( 'details', 'Copying remote `' . $destination_type . '` file `' . $file . '` down to local.' );
		pb_backupbuddy::set_greedy_script_limits();
		
		if ( $destination_type == 'stash' ) {
			
			$itxapi_username = $settings['itxapi_username'];
			$itxapi_password = $settings['itxapi_password'];
			
			// Load required files.
			require_once( pb_backupbuddy::plugin_path() . '/destinations/stash/init.php' );
			require_once( pb_backupbuddy::plugin_path() . '/destinations/stash/lib/class.itx_helper.php' );
			require_once( pb_backupbuddy::plugin_path() . '/destinations/stash/lib/aws-sdk/sdk.class.php' );
			
			// Talk with the Stash API to get access to do things.
			$stash = new ITXAPI_Helper( pb_backupbuddy_destination_stash::ITXAPI_KEY, pb_backupbuddy_destination_stash::ITXAPI_URL, $itxapi_username, $itxapi_password );
			$manage_url = $stash->get_manage_url();
			$request = new RequestCore($manage_url);
			$response = $request->send_request(true);
			
			// Validate response.
			if(!$response->isOK()) {
				$error = 'Request for management credentials failed.';
				pb_backupbuddy::status( 'error', $error );
				pb_backupbuddy::alert( $error );
				return false;
			}
			if(!$manage_data = json_decode($response->body, true)) {
				$error = 'Did not get valid JSON response.';
				pb_backupbuddy::status( 'error', $error );
				pb_backupbuddy::alert( $error );
				return false;
			}
			if(isset($manage_data['error'])) {
				$error = 'Error: ' . implode(' - ', $manage_data['error']);
				pb_backupbuddy::status( 'error', $error );
				pb_backupbuddy::alert( $error );
				return false;
			}
			
			// Determine destination filename.
			$destination_file = pb_backupbuddy::$options['backup_directory'] . basename( $file );
			if ( file_exists( $destination_file ) ) {
				$destination_file = str_replace( 'backup-', 'backup_copy_' . pb_backupbuddy::random_string( 5 ) . '-', $destination_file );
			}
			pb_backupbuddy::status( 'details', 'Filename of resulting local copy: `' . $destination_file . '`.' );
			
			// Connect to Amazon S3.
			pb_backupbuddy::status( 'details', 'Instantiating S3 object.' );
			$s3 = new AmazonS3( $manage_data['credentials'] );
			pb_backupbuddy::status( 'details', 'About to get Stash object `' . $file . '`...' );
			$response = $s3->get_object( $manage_data['bucket'], $manage_data['subkey'] . '/' . $file, array( 'fileDownload' => $destination_file ) );
			
			if ( $response->isOK() ) {
				pb_backupbuddy::status( 'details', 'Stash copy to local success.' );
				return true;
			} else {
				pb_backupbuddy::status( 'error', 'Error #894597845. Stash copy to local FAILURE. Details: `' . print_r( $response, true ) . '`.' );
				return false;
			}

			
		} else {
			pb_backupbuddy::status( 'error', 'Error #859485. Unknown destination type `' . $destination_type . '`.' );
			return false;
		}
		
		
	} // End process_remote_copy().
	
	
	
	// TODO: Merge into v3.1 destinations system in destinations directory.
	// Copy Dropbox backup to local backup directory
	function process_dropbox_copy( $destination_id, $file ) {
		pb_backupbuddy::set_greedy_script_limits();
		
		require_once( pb_backupbuddy::plugin_path() . '/destinations/dropbox/lib/dropbuddy/dropbuddy.php' );
		$dropbuddy = new pb_backupbuddy_dropbuddy( pb_backupbuddy::$options['remote_destinations'][$destination_id]['token'] );
		if ( $dropbuddy->authenticate() !== true ) {
			if ( !isset( pb_backupbuddy::$classes['core'] ) ) {
				require_once( pb_backupbuddy::plugin_path() . '/classes/core.php' );
				pb_backupbuddy::$classes['core'] = new pb_backupbuddy_core();
			}
			pb_backupbuddy::$classes['core']->mail_error( 'Dropbox authentication failed in cron_process_dropbox_copy.' );
			return false;
		}
		
		$destination_file = pb_backupbuddy::$options['backup_directory'] . basename( $file );
		if ( file_exists( $destination_file ) ) {
			$destination_file = str_replace( 'backup-', 'backup_copy_' . pb_backupbuddy::random_string( 5 ) . '-', $destination_file );
		}
		
		pb_backupbuddy::status( 'error', 'About to get file `' . $file . '` from Dropbox and save to `' . $destination_file . '`.' );
		file_put_contents( $destination_file, $dropbuddy->get_file( $file ) );
		pb_backupbuddy::status( 'error', 'Got object from Dropbox cron.' );
	}
	
	
	
	// TODO: Merge into v3.1 destinations system in destinations directory.
	// Copy Rackspace backup to local backup directory
	function process_rackspace_copy( $rs_backup, $rs_username, $rs_api_key, $rs_container, $rs_server ) {
		pb_backupbuddy::set_greedy_script_limits();
		
		require_once( pb_backupbuddy::plugin_path() . '/destinations/rackspace/lib/rackspace/cloudfiles.php' );
		$auth = new CF_Authentication( $rs_username, $rs_api_key, NULL, $rs_server );
		$auth->authenticate();
		$conn = new CF_Connection( $auth );

		// Set container
		$container = $conn->get_container( $rs_container );
		
		// Get file from Rackspace
		$rsfile = $container->get_object( $rs_backup );
		
		$destination_file = pb_backupbuddy::$options['backup_directory'] . $rs_backup;
		if ( file_exists( $destination_file ) ) {
			$destination_file = str_replace( 'backup-', 'backup_copy_' . pb_backupbuddy::random_string( 5 ) . '-', $destination_file );
		}
		
		$fso = fopen( ABSPATH . 'wp-content/uploads/backupbuddy_backups/' . $rs_backup, 'w' );
		$rsfile->stream($fso);
		fclose($fso);
	}
	
	
	
	// TODO: Merge into v3.1 destinations system in destinations directory.
	// Copy FTP backup to local backup directory
	function process_ftp_copy( $backup, $ftp_server, $ftp_username, $ftp_password, $ftp_directory, $port = '21', $ftps = '0' ) {
		pb_backupbuddy::set_greedy_script_limits();
		
		
		// Connect to server.
		if ( $ftps == '1' ) { // Connect with FTPs.
			if ( function_exists( 'ftp_ssl_connect' ) ) {
				$conn_id = ftp_ssl_connect( $ftp_server, $port );
				if ( $conn_id === false ) {
					pb_backupbuddy::status( 'details',  'Unable to connect to FTPS  (check address/FTPS support).', 'error' );
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
				$conn_id = ftp_connect( $ftp_server, $port );
				if ( $conn_id === false ) {
					pb_backupbuddy::status( 'details',  'ERROR: Unable to connect to FTP (check address).', 'error' );
					return false;
				} else {
					pb_backupbuddy::status( 'details',  'Connected to FTP.' );
				}
			} else {
				pb_backupbuddy::status( 'details',  'Your web server doesnt support FTP in PHP.', 'error' );
				return false;
			}
		}
		
		
		// login with username and password
		$login_result = ftp_login( $conn_id, $ftp_username, $ftp_password );
	
		// try to download $server_file and save to $local_file
		$destination_file = pb_backupbuddy::$options['backup_directory'] . $backup;
		if ( file_exists( $destination_file ) ) {
			$destination_file = str_replace( 'backup-', 'backup_copy_' . pb_backupbuddy::random_string( 5 ) . '-', $destination_file );
		}
		if ( ftp_get( $conn_id, $destination_file, $ftp_directory . $backup, FTP_BINARY ) ) {
		    pb_backupbuddy::status( 'message', 'Successfully wrote remote file locally to `' . $destination_file . '`.' );
		} else {
		    pb_backupbuddy::status( 'error', 'Error writing remote file locally to `' . $destination_file . '`.' );
		}
		
		// close this connection
		ftp_close( $conn_id );
	}
	
	
	
	function housekeeping() {
		
		if ( !isset( pb_backupbuddy::$classes['core'] ) ) {
			require_once( pb_backupbuddy::plugin_path() . '/classes/core.php' );
			pb_backupbuddy::$classes['core'] = new pb_backupbuddy_core();
		}
		
		pb_backupbuddy::$classes['core']->periodic_cleanup();
		
	} // End housekeeping().
	
	
}
?>