<?php

// DO NOTE CALL THIS CLASS DIRECTLY. CALL VIA: pb_backupbuddy_destination in bootstrap.php.

class pb_backupbuddy_destination_s3 { // Change class name end to match destination name.
	
	public static $destination_info = array(
		'name'			=>		'Amazon S3',
		'description'	=>		'Amazon S3 is a well known cloud storage provider. This destination is known to be reliable and works well with BackupBuddy. <a href="http://aws.amazon.com/s3/" target="_new">Learn more here.</a>',
	);
	
	// Default settings. Should be public static for auto-merging.
	public static $default_settings = array(
		'type'				=>		's3',		// MUST MATCH your destination slug. Required destination field.
		'title'				=>		'',			// Required destination field.
		'accesskey'			=>		'',			// Amazon access key.
		'secretkey'			=>		'',			// Amazon secret key.
		'bucket'			=>		'',			// Amazon bucket to put into.
		'directory'			=>		'',			// Amazon directory to put into.
		'storage_class'		=>		'STANDARD',	// Amazon S3 storage class (different values have different prices). Valid values: STANDARD, REDUCED_REDUNDANCY.
		'server_encryption'	=>		'',			// Valid values: '' (blank), 'AES256'.
		'ssl'				=>		'1',		// Whether or not to use SSL encryption for connecting.
		'archive_limit'		=>		'0',		// Maximum number of backups for this site in this directory for this account. No limit if zero 0.
	);
	
	
	
	/*	send()
	 *	
	 *	Send one or more files.
	 *	
	 *	@param		array			$files			Array of one or more files to send.
	 *	@return		boolean							True on success, else false.
	 */
	public static function send( $settings = array(), $files = array() ) {
		
		$accesskey = $settings['accesskey'];
		$secretkey = $settings['secretkey'];
		$bucket = $settings['bucket'];
		$directory = $settings['directory'];
		$ssl = $settings['ssl'];
		$storage_class = $settings['storage_class'];
		$server_encryption = $settings['server_encryption'];
		$limit = $settings['archive_limit'];
		
		// Add trailing slash to end of directory if one defined.
		if ( $directory != '' ) {
			$directory = rtrim( $directory, '/\\' ) . '/';
		}
		
		
		require_once( dirname( __FILE__ ) . '/lib/s3.php' );
		$s3 = new pb_backupbuddy_S3( $accesskey, $secretkey, $ssl );
		
		
		// Set bucket with permissions.
		pb_backupbuddy::status( 'details',  'About to create S3 bucket `' . $bucket . '`.' );
		$s3->putBucket( $bucket, pb_backupbuddy_S3::ACL_PRIVATE );
		pb_backupbuddy::status( 'details',  'Bucket created.' );
		
		
		// Send each file.
		foreach( $files as $file ) {
		
			// Send file.
			if ( false === pb_backupbuddy_S3::inputFile( $file ) ) {
				pb_backupbuddy::status( 'error', 'Error #3443434: Bad input. File not found or access denied. Verify permissions.' );
				return false;
			}
			pb_backupbuddy::status( 'details', 'Using S3 storage class `' . $storage_class . '`.' );
			
			// TODO: adding this header does NOT work properly... gives signature errors...
			$meta = array();
			if ( $server_encryption != '' ) { // Add encryption header if enabled.
				$meta['x-amz-server-side-encryption'] = $server_encryption;
			}
			
			pb_backupbuddy::status( 'details', 'About to put file to S3.' );
			$s3_response = $s3->putObject( pb_backupbuddy_S3::inputFile( $file ), $bucket, $directory . basename( $file ), pb_backupbuddy_S3::ACL_PRIVATE, array(), $meta ); // , $storage_class
			
			if ( $s3_response !== true ) { // Failed.
				
				$error_message = 'ERROR #9024: Connected to Amazon S3 but unable to put file. There is a problem with one of the following S3 settings: bucket, directory, or S3 permissions. Details:' . "\n\n" . $s3_response . "\n\n" . 'http://ithemes.com/codex/page/BackupBuddy:_Error_Codes#9024';
				pb_backupbuddy::status( 'details',  $error_message, 'error' );
				pb_backupbuddy::$classes['core']->mail_error( __( $error_message, 'it-l10n-backupbuddy' ) );
				
				return false; // Failed.
			} else {
				pb_backupbuddy::status( 'details', 'File put successfully.' );
			}
			
			// Success sending this file if we made it this far.
			
			pb_backupbuddy::status( 'details',  'SUCCESS sending to Amazon S3! Response: ' . $s3_response );
			
			// Start remote backup limit
			if ( $limit > 0 ) {
				pb_backupbuddy::status( 'details', 'Archive limit of `' . $limit . '` in settings.' );
				
				$results = $s3->getBucket( $bucket );
				
				// Create array of backups and organize by date
				$bkupprefix = pb_backupbuddy::$classes['core']->backup_prefix();
				
				$backups = array();
				foreach( $results as $rekey => $reval ) {
					$pos = strpos( $rekey, $directory . 'backup-' . $bkupprefix . '-' );
					if ( $pos !== FALSE ) {
						$backups[$rekey] = $results[$rekey]['time'];
					}
				}
				arsort( $backups );
				
				
				if ( ( count( $backups ) ) > $limit ) {
					pb_backupbuddy::status( 'details', 'More archives (' . count( $backups ) . ') than limit (' . $limit . ') allows. Trimming...' );
					$i = 0;
					$delete_fail_count = 0;
					foreach( $backups as $buname => $butime ) {
						$i++;
						if ( $i > $limit ) {
							pb_backupbuddy::status ( 'details', 'Trimming excess file `' . $buname . '`...' );
							if ( !$s3->deleteObject( $bucket, $buname ) ) {
								pb_backupbuddy::status( 'details',  'Unable to delete excess S3 file `' . $buname . '` in bucket `' . $bucket . '`.' );
								$delete_fail_count++;
							}
						}
					}
					pb_backupbuddy::status( 'details', 'Finished trimming excess backups.' );
					if ( $delete_fail_count !== 0 ) {
						$error_message = 'Amazon S3 remote limit could not delete ' . $delete_fail_count . ' backups.';
						pb_backupbuddy::status( 'error', $error_message );
						pb_backupbuddy::$classes['core']->mail_error( $error_message );
					}
				}
			} else {
				pb_backupbuddy::status( 'details',  'No S3 file limit to enforce.' );
			} // End remote backup limit
				
		} // end foreach file.
		
		
		// Success sending all files if we made it this far.
		return true;
		
	} // End send().
	
	
	
	/*	test()
	 *	
	 *	Tests ability to write to this remote destination.
	 *	
	 *	@param		array			$settings	Destination settings.
	 *	@return		bool|string					True on success, string error message on failure.
	 */
	public static function test( $settings ) {
		
		pb_backupbuddy::status( 'details', 'Beginning Amazon S3 destination test.' );
		
		$accesskey = $settings['accesskey'];
		$secretkey = $settings['secretkey'];
		$bucket = $settings['bucket'];
		$directory = $settings['directory'];
		$storage_class = $settings['storage_class'];
		$ssl = $settings['ssl'];
		
		// Add trailing slash to end of directory if one defined.
		if ( $directory != '' ) {
			$directory = rtrim( $directory, '/\\' ) . '/';
		}
		
		// Verify all required fields passed.
		if ( empty( $accesskey ) || empty( $secretkey ) || empty( $bucket ) ) {
			return __('Missing one or more required fields.', 'it-l10n-backupbuddy' );
		}
		
		// Verify bucket naming requirements.
		$bucket_requirements = __( "Your bucket name must meet certain criteria. It must fulfill the following: \n\n Characters may be lowercase letters, numbers, periods (.), and dashes (-). \n Must start with a number or letter. \n Must be between 3 and 63 characters long. \n Must not be formatted as an IP address (e.g., 192.168.5.4). \n Should be between 3 and 63 characters long. \n Should not end with a dash. \n Cannot contain two, adjacent periods. \n Cannot contain dashes next to periods.", 'it-l10n-backupbuddy' );
		if ( preg_match( "/^[a-z0-9][a-z0-9\-\.\_]*(?<!-)$/i", $bucket ) == 0 ) { // Starts with a-z or 0-9; middle is a-z, 0-9, -, or .; cannot end in a dash.
			return __( 'Your bucket failed one or more things in the check: Starts with a-z or 0-9; middle is a-z, 0-9, -, or .; cannot end in a dash.', 'it-l10n-backupbuddy' ) . ' ' . $bucket_requirements;
		}
		if ( ( strlen( $bucket ) < 3 ) || ( strlen( $bucket ) > 63 ) ) { // Must be between 3 and 63 characters long
			return __( 'Your bucket must be between 3 and 63 characters long.', 'it-l10n-backupbuddy' ) . ' ' . $bucket_requirements;
		}
		if ( ( strstr( $bucket, '.-' ) !== false ) || ( strstr( $bucket, '-.' ) !== false ) || ( strstr( $bucket, '..' ) !== false ) ) { // Bucket names cannot contain dashes next to periods (e.g., "my-.bucket.com" and "my.-bucket" are invalid)
			return __( 'Your bucket contains a period next to a dash.', 'it-l10n-backupbuddy' ) . ' ' . $bucket_requirements;
		}
		
		pb_backupbuddy::status( 'details', 'Loading S3 library.' );
		require_once( dirname( __FILE__ ) . '/lib/s3.php' );
		pb_backupbuddy::status( 'details', 'Creating S3 object.' );
		$s3 = new pb_backupbuddy_S3( $accesskey, $secretkey, $ssl );
		
		// Check if target bucket exists. Create it if not.
		pb_backupbuddy::status( 'details', 'Check if bucket already exists.' );
		if ( $s3->getBucketLocation( $bucket ) === false ) { // Easy way to see if bucket already exists.
			pb_backupbuddy::status( 'details', 'Bucket does not exist; creating it.' );
			$s3->putBucket( $bucket, pb_backupbuddy_S3::ACL_PRIVATE );
		}
		
		// Send temporary test file.
		//pb_backupbuddy::status( 'details', 'Using S3 storage class `' . $storage_class . '`.' );
		pb_backupbuddy::status( 'details', 'About to put file to S3.' );
		if ( $s3->putObject( __('Upload test for BackupBuddy for Amazon S3', 'it-l10n-backupbuddy' ), $bucket, $directory . 'backupbuddy.txt', pb_backupbuddy_S3::ACL_PRIVATE) ) {
			// Success... just delete temp test file later...
			pb_backupbuddy::status( 'details', 'Success uploading test file to S3.' );
		} else {
			pb_backupbuddy::status( 'error', 'Failure uploading test file to S3.' );
			return __('Unable to upload. Verify your keys, bucket name, and account permissions.', 'it-l10n-backupbuddy' );
		}
		
		// Delete temporary test file.
		if ( ! pb_backupbuddy_S3::deleteObject( $bucket, $directory . 'backupbuddy.txt' ) ) {
			pb_backupbuddy::status( 'details', 'Partial S3 test success. Could not delete temp file.' );
			return __('Partial success. Could not delete temp file.', 'it-l10n-backupbuddy' );
		}
		
		return true; // Success!
		
	} // End test().
	
	
} // End class.