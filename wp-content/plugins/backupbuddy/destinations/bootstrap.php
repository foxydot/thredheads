<?php
/* Destinations class
 *
 * Handles everything remote destinations and passes onto individual destination
 * class functions.
 *
 * @author Dustin Bolton
 *
 */

class pb_backupbuddy_destinations {

	private $_destination; // Object containing destination.
	private $_settings; // Array of settings for the destination.
	private $_destination_type; // Destination type.
	
	// Default destination information.
	private static $_destination_info_defaults = array(
		'name'			=>		'{Err_3448}',
		'description'	=>		'{Err_4586. Unknown destination type.}',
	);
	
	
	
	// boolean false on failure, destination class name on success.
	private static function _init_destination( $destination_type ) {
		
		// Load init file.
		$destination_init_file = pb_backupbuddy::plugin_path() . '/destinations/' . $destination_type . '/init.php';
		if ( file_exists( $destination_init_file ) ) {
			require_once( $destination_init_file );
		} else {
			pb_backupbuddy::status( 'error', 'Destination type `' . $destination_type . '` init.php file not found.' );
			return false;
		}
		
		// Load class.
		$destination_class = 'pb_backupbuddy_destination_' . $destination_type;
		if ( ! class_exists( $destination_class ) ) {
			pb_backupbuddy::status( 'error', 'Destination type `' . $destination_type . '` class not found.' );
			return false;
		}
		
		if ( method_exists( $destination_class, 'init' ) ) {
			call_user_func_array( "{$destination_class}::init", array() ); // Initialize.
		}
		
		pb_backupbuddy::status( 'details', 'Initialized `' . $destination_type . '` destination.' );
		
		return $destination_class;
		
	} // End _init_destination().
	
	
	
	public static function get_info( $destination_type ) {
		
		// Initialize destination.
		$destination_class = self::_init_destination( $destination_type );
		if ( !class_exists( $destination_class ) ) {
			pb_backupbuddy::alert( 'Unable to load class `' . $destination_class . '` for destination type `' . $destination_type . '`.' );
			return self::$_destination_info_defaults;
		}
		
		// Get default dest info from class. Was using a variable class name but had to change this for PHP 5.2 compat.
		$vars = get_class_vars( $destination_class );
  		$default_info = $vars['destination_info'];
  		unset( $vars );
		$destination_info = array_merge( self::$_destination_info_defaults, $default_info ); // Merge in defaults from destination over defaults for BB destinations.
		
		return $destination_info;
		
	} // End get_details().
	
	
	// returns settings form object. false on error.
	// mode = add, edit, or save
	public static function configure( $destination_settings, $mode ) {
		
		// Initialize destination.
		$destination_class = self::_init_destination( $destination_settings['type'] );
		if ( !class_exists( $destination_class ) ) {
			echo '{Error #546893498a. Destination configuration file missing. Missing class: `' . $destination_class . '`}';
			return false;
		}
		
		// Default settings.
		// Get default settings from class. Was using a variable class name but had to change this for PHP 5.2 compat.
		$vars = get_class_vars( $destination_class );
  		$default_settings = $vars['default_settings'];
  		unset( $vars );
		$destination_settings = array_merge( $default_settings, $destination_settings ); // Merge in defaults.
				
		
		// Get default info from class. Was using a variable class name but had to change this for PHP 5.2 compat.
		$vars = get_class_vars( $destination_class );
  		$default_info = $vars['destination_info'];
  		unset( $vars );
		$destination_info = array_merge( self::$_destination_info_defaults, $default_info ); // Merge in defaults.
		
		
		$settings_form = new pb_backupbuddy_settings( 'settings', $destination_settings, 'sending=' . pb_backupbuddy::_GET( 'sending' ) );
		$settings_form->add_setting( array(
			'type'		=>		'hidden',
			'name'		=>		'type',
			'value'		=>		$destination_settings['type'],
			/*
			'title'		=>		__( 'Destination name', 'it-l10n-backupbuddy' ),
			'tip'		=>		__( 'Name of the new destination to create. This is for your convenience only.', 'it-l10n-backupbuddy' ),
			'rules'		=>		'required|string[0-500]',
			*/
		) );
		
		$config_file = pb_backupbuddy::plugin_path() . '/destinations/' . $destination_settings['type'] . '/_configure.php';
		if ( file_exists( $config_file ) ) {
			require( $config_file );
		} else {
			echo '{Error #54556543. Missing destination config file `' . $config_file . '`.}';
			return false;
		}
		
		return $settings_form;
		
	} // End configure().
	
	
	
	
	
	
	
	/*	send()
	 *	
	 *	function description
	 *	
	 *	@param		array			Array of settings to pass to destination.
	 *	@param		array			Array of files to send (full path).
	 *	@return		boolean|array	true success, false on failure, array for multipart send information (transfer is being chunked up into parts).
	 */
	public function send( $destination_settings, $files ) {
		
		// Register PHP shutdown function to help catch and log fatal PHP errors during backup.
		register_shutdown_function( 'pb_backupbuddy_destinations::shutdown_function' );
		
		// Initialize destination.
		$destination_class = self::_init_destination( $destination_settings['type'] );
		
		// Get default settings from class. Was using a variable class name but had to change this for PHP 5.2 compat.
		$vars = get_class_vars( $destination_class );
  		$default_settings = $vars['default_settings'];
  		unset( $vars );
		
		$destination_settings = array_merge( $default_settings, $destination_settings ); // Merge in defaults.
		
		if ( !is_array( $files ) ) {
			$files = array( $files );
		}
		
		$files_with_sizes = '';
		foreach( $files as $index => $file ) {
			if ( '' == $file ) {
				unset( $files[$index] );
				continue; // Not actually a file to send.
			}
			if ( ! file_exists( $file ) ) {
				pb_backupbuddy::status( 'error', 'Error #58459458743. The file that was attempted to be sent to a remote destination, `' . $file . '`, was not found. It either does not exist or permissions prevent accessing it.' );
				return false;
			}
			$files_with_sizes .= $file .' (' . pb_backupbuddy::$format->file_size( filesize( $file ) ) . '); ';
		}
		pb_backupbuddy::status( 'details', 'Sending files `' . $files_with_sizes . '` to destination type `' . $destination_settings['type'] . '` titled `' . $destination_settings['title'] . '`.' );
		unset( $files_with_sizes );
		pb_backupbuddy::status( 'details', 'Calling send function.' );
		
		//$result = $destination_class::send( $destination_settings, $files );
		global $pb_backupbuddy_destination_errors;
		$pb_backupbuddy_destination_errors = array();
		$result = call_user_func_array( "{$destination_class}::send", array( $destination_settings, $files ) );
		if ( $result === false ) {
			$error_details = implode( '; ', $pb_backupbuddy_destination_errors );
			pb_backupbuddy::$classes['core']->mail_error( 'There was an error sending to the remote destination. One or more files may have not been fully transferred. Please see error details for additional information. If the error persists, enable full error logging and try again for full details and troubleshooting. Details: ' . "\n\n" . $error_details );
		}
		
		if ( is_array( $result ) ) { // Send is multipart.
			pb_backupbuddy::status( 'details', 'Completed send function. This file will be sent in multipart chunks. Result: `' . implode( '; ', $result ) . '`.' );
		} else { // Single all-at-once send.
			pb_backupbuddy::status( 'details', 'Completed send function. Result: `' . $result . '`.' );
		}
		
		return $result;
		
	} // End send().
	
	
	// return true on success, else error message.
	public function test( $destination_settings ) {
	
		// Initialize destination.
		$destination_class = self::_init_destination( $destination_settings['type'] );
		if ( !class_exists( $destination_class ) ) {
			echo '{Error #546893498b. Destination configuration file missing.}';
			return false;
		}
		
		// Get default settings from class. Was using a variable class name but had to change this for PHP 5.2 compat.
		$vars = get_class_vars( $destination_class );
  		$default_settings = $vars['default_settings'];
  		unset( $vars );
		$destination_settings = array_merge( $default_settings, $destination_settings ); // Merge in defaults.
		
		// test() returns true on success, else error message.
		return call_user_func_array( "{$destination_class}::test", array( $destination_settings ) );
		
	} // End test().
	
	
	
	/*	shutdown_function()
	 *	
	 *	Used for catching fatal PHP errors during backup to write to log for debugging.
	 *	
	 *	@return		null
	 */
	public static function shutdown_function() {
		//error_log ('shutdown_function()');
		
		// Get error message.
		// Error types: http://php.net/manual/en/errorfunc.constants.php
		$e = error_get_last();
		if ( $e === NULL ) { // No error of any kind.
			return;
		} else { // Some type of error.
			if ( !is_array( $e ) || ( $e['type'] != E_ERROR ) && ( $e['type'] != E_USER_ERROR ) ) { // Return if not a fatal error.
				return;
			}
		}
		
		
		// Calculate log directory.
		if ( defined( 'PB_STANDALONE' ) && PB_STANDALONE === true ) {
			$log_directory = ABSPATH . 'importbuddy/';
		} else {
			$log_directory = pb_backupbuddy::$options['log_directory'];
		}
		$main_file = $log_directory . 'log-' . pb_backupbuddy::$options['log_serial'] . '.txt';
		
		
		// Determine if writing to a serial log.
		if ( pb_backupbuddy::$_status_serial != '' ) {
			$serial = pb_backupbuddy::$_status_serial;
			$serial_file = $log_directory . 'status-' . $serial . '_' . pb_backupbuddy::$options['log_serial'] . '.txt';
			$write_serial = true;
		} else {
			$write_serial = false;
		}
		
		
		// Format error message.
		$e_string = "---\n" . __( 'Fatal PHP error encountered:', 'it-l10n-backupbuddy' ) . "\n";
		foreach( (array)$e as $e_line_title => $e_line ) {
			$e_string .= $e_line_title . ' => ' . $e_line . "\n";
		}
		$e_string .= "---\n";
		
		
		// Write to log.
		file_put_contents( $main_file, $e_string, FILE_APPEND );
		if ( $write_serial === true ) {
			@file_put_contents( $serial_file, $e_string, FILE_APPEND );
		}
		
		
	} // End shutdown_function.
	
	
	
	public static function get_destinations_list() {
		$destinations_root = dirname( __FILE__ ) . '/';
		
		$destination_dirs = glob( $destinations_root . '*', GLOB_ONLYDIR );
		if ( !is_array( $destination_dirs ) ) {
			$destination_dirs = array();
		}
		
		$destination_list = array();
		foreach( $destination_dirs as $destination_dir ) {
			$destination_dir = str_replace( $destinations_root, '', $destination_dir );
			if ( substr( $destination_dir, 0, 1 ) == '_' ) { // Skip destinations beginning in underscore as they are not an actual destination.
				continue;
			}
			$destination_list[$destination_dir] = self::get_info( $destination_dir );
		}
		
		// Change some ordering.
		$stash_destination = array( 'stash' => $destination_list['stash'] );
		unset( $destination_list['stash'] );
		
		$s3_destination = array( 's3' => $destination_list['s3'] );
		unset( $destination_list['s3'] );
		
		$destination_list = array_merge( $stash_destination, $s3_destination, $destination_list );
		
		
		return $destination_list;
	} // End get_destinations().
	
	
	// Handles removing destination from schedules also.
	// True on success, else error message.
	public static function delete_destination( $destination_id, $confirm = false ) {
		
		if ( $confirm === false ) {
			return 'Error #54858597. Not deleted. Confirmation parameter missing.';
		}
		
		// Delete destination.
		unset( pb_backupbuddy::$options['remote_destinations'][$destination_id] );
		
		// Remove this destination from all schedules using it.
		foreach( pb_backupbuddy::$options['schedules'] as $schedule_id => $schedule ) {
			$remote_list = '';
			$trimmed_destination = false;
			
			$remote_destinations = explode( '|', $schedule['remote_destinations'] );
			foreach( $remote_destinations as $remote_destination ) {
				if ( $remote_destination == $destination_id ) {
					$trimmed_destination = true;
				} else {
					$remote_list .= $remote_destination . '|';
				}
			}
			
			if ( $trimmed_destination === true ) {
				pb_backupbuddy::$options['schedules'][$schedule_id]['remote_destinations'] = $remote_list;
			}
		} // end foreach.
		
		pb_backupbuddy::save();
		return true;

	} // End delete_destination().
	
}


