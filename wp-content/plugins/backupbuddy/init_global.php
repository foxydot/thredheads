<?php // This code runs everywhere.

// Make localization happen.
if ( ! defined( 'PB_STANDALONE' ) ) {
	load_plugin_textdomain( 'it-l10n-backupbuddy', false, dirname( plugin_basename( __FILE__ ) ) . '/lang' );
}



/********** ACTIONS (global) **********/
pb_backupbuddy::add_action( array( 'pb_backupbuddy-cron_scheduled_backup', 'process_scheduled_backup' ), 10, 5 ); // Scheduled backup.



/********** AJAX (global) **********/



/********** CRON (global) **********/
pb_backupbuddy::add_cron( 'process_backup', 10, 1 ); // Normal (manual) backup. Normal backups use cron system for scheduling each step when in modern mode. Classic mode skips this and runs all in one PHP process.
pb_backupbuddy::add_cron( 'final_cleanup', 10, 1 ); // Cleanup after backup.
pb_backupbuddy::add_cron( 'remote_send', 10, 5 ); // Manual remote destination sending.
pb_backupbuddy::add_cron( 'destination_send', 10, 2 ); // Manual remote destination sending.

// Remote destination copying. Eventually combine into one function to pass to individual remote destination classes to process.
pb_backupbuddy::add_cron( 'process_s3_copy', 10, 6 );
pb_backupbuddy::add_cron( 'process_remote_copy', 10, 3 );
pb_backupbuddy::add_cron( 'process_dropbox_copy', 10, 2 );
pb_backupbuddy::add_cron( 'process_rackspace_copy', 10, 5 );
pb_backupbuddy::add_cron( 'process_ftp_copy', 10, 7 );
pb_backupbuddy::add_cron( 'housekeeping', 10, 0 );


/********** FILTERS (global) **********/
pb_backupbuddy::add_filter( 'cron_schedules', 10, 0 ); // Add schedule periods such as bimonthly, etc into cron.
if ( '1' == pb_backupbuddy::$options['disable_https_local_ssl_verify'] ) {
	$disable_local_ssl_verify_anon_function = create_function( '', 'return false;' );
	add_filter( 'https_local_ssl_verify', $disable_local_ssl_verify_anon_function, 100 );
}



/********** WIDGETS (global) **********/



/********** HANDLE DELETING EXPIRED TRANSIENTS **********/
// TODO: In the future when WordPress handles this for us, remove on WP versions where it is no longer needed.


function pb_backupbuddy_clean_transients() {
	pb_backupbuddy_transient_delete( true );
}

function pb_backupbuddy_clear_transients() {
	pb_backupbuddy_transient_delete( false );
}

function pb_backupbuddy_transient_delete( $expired ) {
	global $_wp_using_ext_object_cache;
	if ( !$_wp_using_ext_object_cache ) {
		global $wpdb;
		// Build required SQL
		$sql = "SELECT option_name FROM $wpdb->options WHERE option_name LIKE '_transient_timeout%'";
		if ( $expired ) {
			$time = time();
			$sql .=  " AND option_value < $time";
		}
		// Get all transients
		$transients = $wpdb->get_col( $sql );
		// Loop through each transient and delete them
		foreach( $transients as $transient ) { $deletion = delete_transient( str_replace( '_transient_timeout_', '', $transient ) ); }
		// Optimize the table after the deletions
		$wpdb->query( "OPTIMIZE TABLE $wpdb->options" );
	}
}

add_action( 'wp_scheduled_delete', 'pb_backupbuddy_clean_transients' );
add_action( 'after_db_upgrade', 'pb_backupbuddy_clear_transients' );
?>