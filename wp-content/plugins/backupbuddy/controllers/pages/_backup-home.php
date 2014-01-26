<?php
/*
require_once( pb_backupbuddy::plugin_path() . '/classes/live.php' );
pb_backupbuddy_live::generate_queue();
*/

//backupbuddy_core::get_stable_options( 'xxx', 'test', 5 );
//die();


?>

<style type="text/css">
#backupbuddy-meta-link-wrap a.show-settings {
	float: right;
	margin: 0 0 0 6px;
}
#screen-meta-links #backupbuddy-meta-link-wrap a {
	background: none;
}
#screen-meta-links #backupbuddy-meta-link-wrap a:after {
	content: '';
	margin-right: 5px;
}
</style>
<script type="text/javascript">
jQuery(document).ready( function() {
	jQuery('#screen-meta-links').append(
		'<div id="backupbuddy-meta-link-wrap" class="hide-if-no-js screen-meta-toggle">' +
			'<a href="" class="show-settings pb_backupbuddy_begintour"><?php _e( "Tour Page", "it-l10n-backupbuddy" ); ?></a>' +
			'<a href="" class="show-settings" onClick="tb_show( \'BackupBuddy Quick Setup\', \'<?php echo pb_backupbuddy::ajax_url( "quickstart" ); ?>&TB_iframe=1&width=640&height=455\', null ); return false;"><?php _e( "Quick Setup", "it-l10n-backupbuddy" ); ?></a>' +
		'</div>'
	);
});
</script>
<?php
// Tutorial
pb_backupbuddy::load_script( 'jquery.joyride-2.0.3.js' );
pb_backupbuddy::load_script( 'modernizr.mq.js' );
pb_backupbuddy::load_style( 'joyride.css' );
?>
<ol id="pb_backupbuddy_tour" style="display: none;">
	<li data-class="profile_choose">Click a backup type to start a backup now...</li>
	<li data-id="pb_backupbuddy_afterbackupremote">Select to send a backup to a remote destination after the manual backup completes. After selecting this option select a profile above to start a backup.</li>
	<li data-class="nav-tab-0">Backups stored on this server are listed here... Hover over backups listed for additional options such as sending to another server or restoring files.</li>
	<li data-class="nav-tab-1" data-button="Finish">This provides a historical listing of recently created backups and the status of each.</li>
</ol>
<script>
jQuery(window).load(function() {
	jQuery(document).on( 'click', '.pb_backupbuddy_begintour', function(e) {
		jQuery("#pb_backupbuddy_tour").joyride({
			tipLocation: 'top',
		});
		return false;
	});
});
</script>
<?php
// END TOUR.



$time_start = time();

//echo 'A:' . ( $time_start - time() ) . '<br>';

backupbuddy_core::versions_confirm();

//echo 'B:' . ( $time_start - time() ) . '<br>';

$alert_message = array();
$preflight_checks = backupbuddy_core::preflight_check();
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


pb_backupbuddy::disalert( 'backup_helpeminder', __( 'Click the "Help" tab at the upper right of any screen for additional help and information about the current page including links to Technical Support and the Knowledge Base.', 'it-l10n-backupbuddy' ) );

pb_backupbuddy::disalert( 'backup_stash_advert', 'Active BackupBuddy customers already have a <b>BackupBuddy Stash</b> account with <span class="pb_label pb_label">1 GB Free Storage</span>. Just login on the <a href="?page=pb_backupbuddy_destinations">Remote Destinations</a> page.' );

$view_data['backups'] = backupbuddy_core::backups_list( 'default' );

//echo 'D:' . ( $time_start - time() ) . '<br>';

pb_backupbuddy::load_view( '_backup-home', $view_data );
?>