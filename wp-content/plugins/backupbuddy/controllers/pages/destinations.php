<?php
// Tutorial
pb_backupbuddy::load_script( 'jquery.joyride-2.0.3.js' );
pb_backupbuddy::load_script( 'modernizr.mq.js' );
pb_backupbuddy::load_style( 'joyride.css' );
?>
<a href="" class="pb_backupbuddy_begintour">Tour This Page</a>
<ol id="pb_backupbuddy_tour" style="display: none;">
	<li data-id="ui-id-1">Remote destinations allow you to send your backups offsite to another location for safe-keeping.</li>
	<li data-id="ui-id-2" data-button="Finish">View a list of backups recently sent to a remote destination.</li>
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



<script type="text/javascript">
	function pb_backupbuddy_selectdestination( destination_id, destination_title, callback_data ) {
		if ( callback_data != '' ) {
			jQuery.post( '<?php echo pb_backupbuddy::ajax_url( 'remote_send' ); ?>', { destination_id: destination_id, destination_title: destination_title, file: callback_data, trigger: 'manual' }, 
				function(data) {
					data = jQuery.trim( data );
					if ( data.charAt(0) != '1' ) {
						alert( '<?php _e('Error starting remote send', 'it-l10n-backupbuddy' ); ?>:' + "\n\n" + data );
					} else {
						alert( "<?php _e('Your file has been scheduled to be sent now. It should arrive shortly.', 'it-l10n-backupbuddy' ); ?> <?php _e( 'You will be notified by email if any problems are encountered.', 'it-l10n-backupbuddy' ); ?>" + "\n\n" + data.slice(1) );
					}
				}
			);
			
			/* Try to ping server to nudge cron along since sometimes it doesnt trigger as expected. */
			jQuery.post( '<?php echo admin_url('admin-ajax.php'); ?>',
				function(data) {
				}
			);

		} else {
			//window.location.href = '<?php echo pb_backupbuddy::page_url(); ?>&custom=remoteclient&destination_id=' + destination_id;
			window.location.href = '<?php
			if ( is_network_admin() ) {
				echo network_admin_url( 'admin.php' );
			} else {
				echo admin_url( 'admin.php' );
			}
			?>?page=pb_backupbuddy_backup&custom=remoteclient&destination_id=' + destination_id;
		}
	}
</script>


<?php
pb_backupbuddy::$ui->title( __( 'Remote Destinations', 'it-l10n-backupbuddy' ) );
echo '<div style="width: 100%;">';
_e( 'BackupBuddy supports many remote destinations which you may transfer backups to.  You may manually send backups to these locations or automatically have them sent for scheduled backups. You may view the files in a remote destination by selecting a destination below once created. In addition to viewing files, you may copy remote backups to your server, and delete files.  All subscribed BackupBuddy customers are provided <b>free</b> storage to our own BackupBuddy Stash cloud destination.', 'it-l10n-backupbuddy' );
echo '</div>';

echo '<br><br><br>';
pb_backupbuddy::$ui->start_tabs(
	'destinations',
	array(
		array(
			'title'		=>		'Remote Destinations',
			'slug'		=>		'destinations',
		),
		array(
			'title'		=>		'Recent Transfers Status',
			'slug'		=>		'transfers',
		),
	),
	'width: 100%;'
);


pb_backupbuddy::$ui->start_tab( 'destinations' );
echo '<br>';
echo '<iframe id="pb_backupbuddy_iframe" src="' . pb_backupbuddy::ajax_url( 'destination_picker' ) . '&action_verb=to%20manage%20files" width="100%" style="max-width: 850px;" height="1800" frameBorder="0">Error #4584594579. Browser not compatible with iframes.</iframe>';
pb_backupbuddy::$ui->end_tab();


pb_backupbuddy::$ui->start_tab( 'transfers' );
echo '<div style="margin-top: 30px; margin-left: 15px;">';
	echo '<h3 style="
		margin: 6px 0 10px 0px;
		font-weight: 200;
		font-size: 20px;
		font-family: "HelveticaNeue-Light","Helvetica Neue Light","Helvetica Neue",sans-serif;
		color: #464646;
	">' . __( 'Most recent file transfers to remote destinations', 'it-l10n-backupbuddy' ) . ':</h3>';
	echo '<br>';
	echo '<div style="margin-left: 0px;">';
		require_once( 'server_info/remote_sends.php' );
	echo '</div>';
echo '</div>';
pb_backupbuddy::$ui->end_tab();
?>

<br style="clear: both;"><br style="clear: both;">