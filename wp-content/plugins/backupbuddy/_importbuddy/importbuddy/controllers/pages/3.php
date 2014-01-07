<?php
if ( ! defined( 'PB_IMPORTBUDDY' ) || ( true !== PB_IMPORTBUDDY ) ) {
	die( '<html></html>' );
}

Auth::require_authentication(); // Die if not logged in.

$data = array(
	'step'		=>		'3',
);


pb_backupbuddy::set_greedy_script_limits( true );




function get_previous_database_settings() {
	// If in high security mode then no guesses or previous values will be given.
	if ( ( pb_backupbuddy::$options['force_high_security'] != false ) || ( isset( pb_backupbuddy::$options['dat_file']['high_security'] ) && ( pb_backupbuddy::$options['dat_file']['high_security'] === true ) ) ) {
		$response['server'] = '';
		$response['database'] = '';
		$response['user'] = '';
		$response['password'] = '';
		$response['prefix'] = '';
		return $response;
	} else { // normal mode. provide previous values.
		$response['server'] = pb_backupbuddy::$options['dat_file']['db_server'];
		$response['database'] = pb_backupbuddy::$options['dat_file']['db_name'];
		$response['user'] = pb_backupbuddy::$options['dat_file']['db_user'];
		$response['password'] = pb_backupbuddy::$options['dat_file']['db_password'];
		$response['prefix'] = pb_backupbuddy::$options['dat_file']['db_prefix'];
		return $response;
	}
}


/**
 *	get_default_values()
 *
 *	Parses various submitted options and settings from step 1.
 *
 *	@return		null
 */
function get_database_defaults() {
	// Database defaults.
	$response['server'] = 'localhost';
	$response['database'] = '';
	$response['user'] = '';
	$response['password'] = '';
	$response['prefix'] = 'wp_';
	$response['wipe'] = pb_backupbuddy::$options['wipe_database']; // just tables matching prefix
	$response['wipe_all'] = pb_backupbuddy::$options['wipe_database_all']; // all tables
	
	if ( count( pb_backupbuddy::$options['dat_file'] ) == 0 ) {
		die( 'Error #854894. DAT file contents unexpectedly went missing. This usually means the import process was reset. Possible causes:The import process was restarted in another browser or tab, the backup ZIP file was renamed from its original filename, or permissions need reset recursively on all files from your webroot.' );
	}
	
	// If in high security mode then no guesses or previous values will be given.
	if ( isset( pb_backupbuddy::$options['dat_file']['high_security'] ) && ( pb_backupbuddy::$options['dat_file']['high_security'] === true ) ) { 
		return $response;
	}
	
	$old_connect_timeout = @ini_get( 'mysql.connect_timeout' );
	$old_socket_timeout = @ini_get( 'default_socket_timeout' );
	@ini_set( 'mysql.connect_timeout', 5 );
	@ini_set( 'default_socket_timeout', 5 );
	
	if ( false !== @mysql_connect( pb_backupbuddy::$options['dat_file']['db_server'], pb_backupbuddy::$options['dat_file']['db_user'], pb_backupbuddy::$options['dat_file']['db_password'] ) ) { // Couldnt connect to server or invalid credentials.
		$response['server'] = pb_backupbuddy::$options['dat_file']['db_server'];
		$response['user'] = pb_backupbuddy::$options['dat_file']['db_user'];
		$response['password'] = pb_backupbuddy::$options['dat_file']['db_password'];
		
		if ( false !== @mysql_select_db( pb_backupbuddy::$options['dat_file']['db_name'] ) ) {
			$response['database'] = pb_backupbuddy::$options['dat_file']['db_name'];
			
			$result = mysql_query( "SHOW TABLES LIKE '" . mysql_real_escape_string( str_replace( '_', '\_', pb_backupbuddy::$options['dat_file']['db_prefix'] ) ) . "%'" );
			if ( mysql_num_rows( $result ) == 0 ) {
				$response['prefix'] = pb_backupbuddy::$options['dat_file']['db_prefix'];
			}
		}
	}
	
	@ini_set( 'mysql.connect_timeout', $old_connect_timeout );
	@ini_set( 'default_socket_timeout', $old_socket_timeout );
	
	return $response;
}


/**
 *	get_default_url()
 *
 *	Returns the default site URL.
 *
 *	@return		string		URL.
 */
function get_default_url() {
	// Get the current URL of where the importbuddy tool is running.
	$url = str_replace( $_SERVER['QUERY_STRING'], '', $_SERVER['REQUEST_URI'] );
	$url = str_replace( basename( $url ) , '', $url );
	$url = preg_replace( '|/*$|', '', $url );  // strips trailing slash(es).
	$url = 'http://' . $_SERVER['HTTP_HOST'] . $url;
	
	return $url;
}


function get_default_domain() {
	preg_match("/^(http:\/\/)?([^\/]+)/i", get_default_url(), $domain );
	return $domain[2];
}




pb_backupbuddy::load_view( 'html_3', $data );
?>