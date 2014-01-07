<?php

/*
require_once( pb_backupbuddy::plugin_path() . '/classes/live.php' );
pb_backupbuddy_live::generate_queue();
*/

//pb_backupbuddy::$classes['core']->get_stable_options( 'xxx', 'test', 5 );
//die();



// Tutorial
pb_backupbuddy::load_script( 'jquery.joyride-2.0.3.js' );
pb_backupbuddy::load_script( 'modernizr.mq.js' );
pb_backupbuddy::load_style( 'joyride.css' );
?>
<a href="" class="pb_backupbuddy_begintour">Tour This Page</a>
<ol id="pb_backupbuddy_tour" style="display: none;">
	<li data-class="pb_backupbuddy_backuplaunch">Click a backup type to start a backup now...</li>
	<li data-id="pb_backupbuddy_afterbackupremote">Select to send a backup to a remote destination after the manual backup completes. After selecting this option select a profile above to start a backup.</li>
	<li data-id="ui-id-1">Backups stored on this server are listed here... Hover over backups listed for additional options such as sending to another server or restoring files.</li>
	<li data-id="ui-id-2" data-button="Finish">This provides a historical listing of recently created backups and the status of each.</li>
</ol>
<script>
jQuery(window).load(function() {
	jQuery( '.pb_backupbuddy_begintour' ).click( function() {
		jQuery("#pb_backupbuddy_tour").joyride({
			tipLocation: 'top',
		});
		return false;
	});
});
</script>
<?php
// END TOUR.


pb_backupbuddy::disalert( 'new_filerestore_tip', 'New in BackupBuddy v4.0! Hover over a Local Archive File in the listing below and select "View & Restore Files" to see the contents of your backup, view text-based file contents, or restore files.' );



$time_start = time();

//echo 'A:' . ( $time_start - time() ) . '<br>';

pb_backupbuddy::$classes['core']->versions_confirm();

//echo 'B:' . ( $time_start - time() ) . '<br>';

$alert_message = array();
$preflight_checks = pb_backupbuddy::$classes['core']->preflight_check();
foreach( $preflight_checks as $preflight_check ) {
	if ( $preflight_check['success'] !== true ) {
		//$alert_message[] = $preflight_check['message'];
		pb_backupbuddy::disalert( $preflight_check['test'], $preflight_check['message'] );
	}
}
if ( count( $alert_message ) > 0 ) {
	//pb_backupbuddy::alert( implode( '<hr style="border: 1px dashed #E6DB55; border-bottom: 0;">', $alert_message ) );
}

//echo 'C:' . ( $time_start - time() ) . '<br>';



$view_data['backups'] = pb_backupbuddy::$classes['core']->backups_list( 'default' );

//echo 'D:' . ( $time_start - time() ) . '<br>';

pb_backupbuddy::load_view( '_backup-home', $view_data );
?>