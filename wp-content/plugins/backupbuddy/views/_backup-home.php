<?php

pb_backupbuddy::$ui->title( 'Backup Site' . ' ' . pb_backupbuddy::video( '9ZHWGjBr84s', __('Backups page tutorial', 'it-l10n-backupbuddy' ), false ) );

/*
wp_enqueue_style('dashboard');
wp_print_styles('dashboard');
wp_enqueue_script('dashboard');
wp_print_scripts('dashboard');
*/
wp_enqueue_script( 'thickbox' );
wp_print_scripts( 'thickbox' );
wp_print_styles( 'thickbox' );
?>
<script type="text/javascript">
	jQuery(document).ready(function() {
		
		jQuery( '.pb_backupbuddy_backuplaunch' ).click( function() {
			var url = jQuery(this).attr( 'href' );
			url = url + '&after_destination=' + jQuery( '#pb_backupbuddy_backup_remotedestination' ).val();
			url = url + '&delete_after=' + jQuery( '#pb_backupbuddy_backup_deleteafter' ).val();
			window.location.href = url;
			return false;
		});
		
		jQuery( '.pb_backupbuddy_hoveraction_send' ).click( function(e) {
			tb_show( 'BackupBuddy', '<?php echo pb_backupbuddy::ajax_url( 'destination_picker' ); ?>&callback_data=' + jQuery(this).attr('rel') + '&sending=1&action_verb=to%20send%20to&TB_iframe=1&width=640&height=455', null );
			return false;
		});
		
		
		// Click label for after backup remote send.
		jQuery( '#pb_backupbuddy_afterbackupremote' ).click( function(e) {
			var checkbox = jQuery( '#pb_backupbuddy_afterbackupremote_box' );
			checkbox.prop('checked', !checkbox[0].checked);
			
			if ( checkbox[0].checked ) { // Only show if just checked.
				afterbackupremote();
			}
			return false;
		});
		
		
		// Click checkbox for after backup remote send.
		jQuery( '#pb_backupbuddy_afterbackupremote_box' ).click( function(e) {
			var checkbox = jQuery( '#pb_backupbuddy_afterbackupremote_box' );
			if ( checkbox[0].checked ) { // Only show if just checked.
				afterbackupremote();
			}
		});
		
		
		jQuery( '.pb_backupbuddy_hoveraction_hash' ).click( function(e) {
			tb_show( 'BackupBuddy', '<?php echo pb_backupbuddy::ajax_url( 'hash' ); ?>&callback_data=' + jQuery(this).attr('rel') + '&TB_iframe=1&width=640&height=455', null );
			return false;
		});
		
		
		
		jQuery( '.pb_backupbuddy_hoveraction_note' ).click( function(e) {
			
			var existing_note = jQuery(this).parents( 'td' ).find('.pb_backupbuddy_notetext').text();
			if ( existing_note == '' ) {
				existing_note = 'My first backup';
			}
			
			var note_text = prompt( '<?php _e( 'Enter a short descriptive note to apply to this archive for your reference. (175 characters max)', 'it-l10n-backupbuddy' ); ?>', existing_note );
			if ( ( note_text == null ) || ( note_text == '' ) ) {
				// User cancelled.
			} else {
				jQuery( '.pb_backupbuddy_backuplist_loading' ).show();
				jQuery.post( '<?php echo pb_backupbuddy::ajax_url( 'set_backup_note' ); ?>', { backup_file: jQuery(this).attr('rel'), note: note_text }, 
					function(data) {
						data = jQuery.trim( data );
						jQuery( '.pb_backupbuddy_backuplist_loading' ).hide();
						if ( data != '1' ) {
							alert( '<?php _e('Error', 'it-l10n-backupbuddy' );?>: ' + data );
						}
						javascript:location.reload(true);
					}
				);
			}
			return false;
		});
		
		
		
	});
	
	function pb_backupbuddy_selectdestination( destination_id, destination_title, callback_data, delete_after ) {
		
		if ( ( callback_data != '' ) && ( callback_data != 'delayed_send' ) ) {
			jQuery.post( '<?php echo pb_backupbuddy::ajax_url( 'remote_send' ); ?>', { destination_id: destination_id, destination_title: destination_title, file: callback_data, trigger: 'manual', delete_after: delete_after }, 
				function(data) {
					data = jQuery.trim( data );
					if ( data.charAt(0) != '1' ) {
						alert( '<?php _e('Error starting remote send', 'it-l10n-backupbuddy' ); ?>:' + "\n\n" + data );
					} else {
						if ( delete_after == true ) {
							var delete_alert = "<?php _e( 'The local backup will be deleted upon successful transfer as selected.', 'it-l10n-backupbuddy' ); ?>";
						} else {
							var delete_alert = '';
						}
						alert( "<?php _e('Your file has been scheduled to be sent now. It should arrive shortly.', 'it-l10n-backupbuddy' ); ?> <?php _e( 'You will be notified by email if any problems are encountered.', 'it-l10n-backupbuddy' ); ?>" + " " + delete_alert + "\n\n" + data.slice(1) );
						/* Try to ping server to nudge cron along since sometimes it doesnt trigger as expected. */
						jQuery.post( '<?php echo admin_url('admin-ajax.php'); ?>',
							function(data) {
							}
						);
					}
				}
			);
		} else if ( callback_data == 'delayed_send' ) { // Specified a destination to send to later.
			/*
			if ( delete_after == true ) {
				var delete_alert_text = "<?php _e( 'The local backup will be deleted upon successful transfer as selected.', 'it-l10n-backupbuddy' ); ?>";
			} else {
				var delete_alert_text = '';
			}
			alert( 'delayed' + delete_alert_text );
			*/
			jQuery( '#pb_backupbuddy_backup_remotedestination' ).val( destination_id );
			jQuery( '#pb_backupbuddy_backup_deleteafter' ).val( delete_after );
			jQuery( '#pb_backupbuddy_backup_remotetitle' ).html( 'Destination: "' + destination_title + '".' );
			jQuery( '#pb_backupbuddy_backup_remotetitle' ).slideDown();
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
	
	
	function afterbackupremote() {
		tb_show( 'BackupBuddy', '<?php echo pb_backupbuddy::ajax_url( 'destination_picker' ); ?>&callback_data=delayed_send&sending=1&action_verb=to%20send%20to&TB_iframe=1&width=640&height=455', null );
	}
	
	
</script>

<style> 
	.therightspot {
		margin: 105px 0 0 158px;
		background: #fff;
		display: block;
		height: 160px;
		width: 985px;
	}
	
	.duo-button {
		background: #f5f5f5;
		background: #ECECEC;
		margin: 0;
		display: inline-block;
		border-radius: 5px;
		padding: 9px 10px;
		border-radius: 5px;
		border: 1px solid #d6d6d6;
		border-top: 1px solid #ebebeb;
		box-shadow: 0px 3px 0px 0px #aaaaaa;
		box-shadow: 0px 3px 0px 0px #CFCFCF;
		font-family: 'Helvetica Neue', 'Helvetica', Arial, sans-serif;
	}
	.duo-button .choose {
		font-size: 20px;
		font-family: "HelveticaNeue-Light","Helvetica Neue Light","Helvetica Neue",sans-serif;
		padding: 5px 0 15px 5px;
		color: #464646;
	}
	.duo-button a {
		font-size: 18px;
		//line-height: 17px;
		display: block;
		float: left;
		margin: 0;
		margin-right: 3px;
		text-decoration: none;
		background: #fff;
		border: 1px solid #CFCFCF;
		border-top: 1px solid #ebebeb;
		border-bottom: 1px solid #c9c9c9;
		border-radius: 2px;
		padding: 20px 30px;
		color: #666;
	}
	.duo-button a:hover {
		box-shadow: inset 0 1px 8px #aaaaaa;
		background: #fff;
	}
	.duo-button a:active {
		color: #fff;
		background: #da2828;
		background: #da2828 url('<?php echo pb_backupbuddy::plugin_url();; ?>/images/red-grad.png') top repeat-x;
		box-shadow: inset 0 1px 4px #561818;
		text-shadow: 0 -1px #561818;
	}
	.duo-button .left {
		border-radius: 4px 0 0 4px;
		border-right: 1px solid #d6d6d6;
	}
	.duo-button .right {
		border-radius: 0 4px 4px 0;
		border-left: none;
	}
	.duo-button .leftright {
		border-radius: 4px 4px 4px 4px;
		border-right: 1px solid #d6d6d6;
	}
	.backupbutton {
		background: url('<?php echo pb_backupbuddy::plugin_url();; ?>/images/press.png') top no-repeat;
		width: 400px;
		height: 32px;
		display: block;
		margin: 12px auto 0;
	}
	.backupbutton:active {
		background-position: bottom;
	}
	
	
	
	.step {
	/*	background: url('blue.png') 0 5px no-repeat; */
		padding: 11px 30px 11px 45px;
		width: 120px;
		color: #464646;
		display: block;
		float: left;
	}
	.step.settings {
		background: url('settings.png') 6px 4px no-repeat;
	}
	.step.database {
		background: url('database.png') 6px 4px no-repeat;
	}
	.step.files {
		background: url('files.png') 6px 4px no-repeat;
	}
	.glow {
		background: url('blue.png') -4px 1px no-repeat;
		width: 35px;
		height: 41px;
		float: left;
		border-right: 1px solid #d6d6d6;
	}
	.step.end {
		width: 20px;
		height: 19px;
		padding: 11px;
		border: none;
		border-radius: 0 4px 4px 0;
	}
	.empty {
		background: url('empty.png') -4px 1px no-repeat;
		width: 35px;
		height: 41px;
		float: left;
		border-right: 1px solid #d6d6d6;
	}
	.end.empty {
		background: url('empty.png') 1px 1px no-repeat;
	}
	.step.end.win {
		background: #ffffff url('green.png') 1px 1px no-repeat;
	}
	.step.end.fail {
		background: #ffffff url('yellow.png') 1px 1px no-repeat;
	}
	.step.end.codered {
		background: #ffffff url('red.png') 1px 1px no-repeat;
	}
	.activate {
		background-color: #fff !important;
	}
	.afterbackupoptionswp {
		margin: 10px 0;
	}
	.afterbackupoptions a {
		font-size: 16px;
		color: #21759B;
		margin-right: 20px;
		font-family: 'Helvetica Neue', 'Helvetica', Arial, sans-serif;
		text-decoration: none;
	}
	.afterbackupoptionswp a {
		background: #f5f5f5;
		text-shadow: rgba(255, 255, 255, 1) 0 1px 0;
		border: 1px solid #BBB;
		border-radius: 11px;
		color: #464646;
		text-decoration: none;
		font-family: 'Helvetica Neue', 'Helvetica', Arial, sans-serif;
		font-size: 12px;
		line-height: 13px;
		padding: 3px 8px;
		cursor: pointer;
	}
</style>


<br>
<div class="duo-button">
	<div class="choose"><?php _e( 'Choose a backup profile to run', 'it-l10n-backupbuddy' ) ?>:</div>
	<?php
	global $pb_backupbuddy_directory_verification;
	if ( false === $pb_backupbuddy_directory_verification ) {
		echo '&nbsp;&nbsp;&nbsp;&nbsp;<b>' . __( 'Error: Fatal errors listed above prevent ability to create a backup. Correct them to proceed.', 'it-l10n-backupbuddy' ) . '</b>';
	} else {
		foreach( pb_backupbuddy::$options['profiles'] as $profile_id => $profile ) {
			if ( $profile_id == 0 ) { continue; }
			if ( $profile['type'] == 'db' ) {
				$type = 'database';
			} elseif ( $profile['type'] == 'full' ) {
				$type = 'full';
			} else {
				$type = '{unknown_type:' . htmlentities( $profile['type'] ) . '}';
			}
			?>
			<a class="pb_backupbuddy_backuplaunch leftright" title="Create this <?php echo $type; ?> backup" href="<?php echo pb_backupbuddy::page_url(); ?>&backupbuddy_backup=<?php echo $profile_id; ?>"><?php echo htmlentities( $profile['title'] ); ?></a>
			<?php
		}
	}
	?>
	<div style="clear: both; padding-top: 9px; padding-left: 4px;">
		<input type="checkbox" name="pb_backupbuddy_afterbackupremote" id="pb_backupbuddy_afterbackupremote_box"> <label id="pb_backupbuddy_afterbackupremote" for="pb_backupbuddy_afterbackupremote">Send to remote destination as part of backup process. <span id="pb_backupbuddy_backup_remotetitle"></span></label>
		
		<input type="hidden" name="remote_destination" id="pb_backupbuddy_backup_remotedestination">
		<input type="hidden" name="delete_after" id="pb_backupbuddy_backup_deleteafter">
		
		<?php //echo '<a href="' . pb_backupbuddy::ajax_url( 'destination_picker' ) . '&#038;TB_iframe=1&#038;width=640&#038;height=600" class="thickbox button secondary-button" style="margin-top: 3px;" title="' . __( 'Select a Destination', 'it-l10n-backupbuddy' ) . '">' . __('Select Remote Destination', 'it-l10n-backupbuddy' ) . '</a>'; ?>
	</div>
	<div class="clearfix"></div>
</div>





<br style="clear: both;"><br><br><br>

<?php
pb_backupbuddy::flush();






/********** START TABS **********/

echo '<br>';
pb_backupbuddy::$ui->start_tabs(
	'backup_locations',
	array(
		array(
			'title'		=>		'Local Archive Files',
			'slug'		=>		'local',
			'css'		=>		'margin-top: -11px;',
		),
		array(
			'title'		=>		'Recent Backups Status',
			'slug'		=>		'recent_backups',
			'css'		=>		'margin-top: -11px;',
		),
	),
	'width: 100%;'
);






pb_backupbuddy::$ui->start_tab( 'local' );
echo '<br>';
$listing_mode = 'default';
require_once( '_backup_listing.php' );

echo '<br><br>';
echo '<a href="';
if ( is_network_admin() ) {
	echo network_admin_url( 'admin.php' );
} else {
	echo admin_url( 'admin.php' );
}
echo '?page=pb_backupbuddy_destinations" class="button button-primary">View & Manage remote destination files</a>';

pb_backupbuddy::$ui->end_tab();








pb_backupbuddy::$ui->start_tab( 'recent_backups' );

?>
<br>
<h3 style="
		margin: 6px 0 10px 0px;
		font-weight: 200;
		font-size: 20px;
		font-family: " helveticaneue-light","helvetica="" neue="" light","helvetica="" neue",sans-serif;="" color:="" #464646;="" "="">Most recent backups (including scheduled, transferred, or deleted):</h3>
<br>
<?php

$backups_list = glob( pb_backupbuddy::$options['log_directory'] . 'fileoptions/*.txt' );
if ( ! is_array( $backups_list ) ) {
	$backups_list = array();
}

if ( count( $backups_list ) == 0 ) {
	_e( 'No backups have been created recently.', 'it-l10n-backupbuddy' );
} else {
	$log_directory = WP_CONTENT_DIR . '/uploads/pb_' . pb_backupbuddy::settings( 'slug' ) . '/';
	
	$recent_backup_count = 0; // Counter.
	$recent_backup_count_cap = 5; // Max number of recent backups to list.
	$backups = array();
	foreach( $backups_list as $backup_fileoptions ) {
		if ( $recent_backup_count > $recent_backup_count_cap ) {
			break;
		}
		
		require_once( pb_backupbuddy::plugin_path() . '/classes/fileoptions.php' );
		$backup = new pb_backupbuddy_fileoptions( $backup_fileoptions, $read_only = true );
		if ( true !== ( $result = $backup->is_ok() ) ) {
			pb_backupbuddy::status( 'error', __('Unable to access fileoptions data file.', 'it-l10n-backupbuddy' ) . ' Error: ' . $result );
			continue;
		}
		$backup = &$backup->options;
		
		if ( !isset( $backup['serial'] ) || ( $backup['serial'] == '' ) ) {
			continue;
		}
		if ( $backup['finish_time'] > $backup['start_time'] ) {
			$status = '<span class="pb_label pb_label-success">Completed</span>';
		} elseif ( $backup['finish_time'] == -1 ) {
			$status = '<span class="pb_label pb_label-warning">Cancelled</span>';
		} else {
			$status = '<span class="pb_label pb_label-warning">In progress or timed out</span>';
		}
		$status .= '<br>';
		
		// Log link (if log file exists still).
		/*
		$serial_file = $log_directory . 'status-' . $backup['serial'] . '_' . pb_backupbuddy::$options['log_serial'] . '.txt';
		if ( file_exists( $serial_file ) ) {
			$status .= '<a title="' . __( 'Backup Process Status Log', 'it-l10n-backupbuddy' ) . '" href="' . pb_backupbuddy::ajax_url( 'view_status_log' ) . '&serial=' . $backup['serial'] . '&#038;TB_iframe=1&#038;width=640&#038;height=600" class="thickbox">Status Log</a> | ';
		}
		*/
		
		// Technical details link.
		$status .= '<div class="row-actions">';
		$status .= '<a title="' . __( 'Backup Process Technical Details', 'it-l10n-backupbuddy' ) . '" href="' . pb_backupbuddy::ajax_url( 'integrity_status' ) . '&serial=' . $backup['serial'] . '&#038;TB_iframe=1&#038;width=640&#038;height=600" class="thickbox">View Details</a>';
		$status .= '</div>';
		
		// Calculate finish time (if finished).
		if ( $backup['finish_time'] > 0 ) {
			$finish_time = pb_backupbuddy::$format->date( pb_backupbuddy::$format->localize_time( $backup['finish_time'] ) ) . '<br><span class="description">' . pb_backupbuddy::$format->time_ago( $backup['finish_time'] ) . ' ago</span>';
		} else { // unfinished.
			$finish_time = '<i>Unfinished</i>';
		}
		
		// Append to list.
		$backups[ $backup['serial'] ] = array(
			basename( $backup['archive_file'] ),
			pb_backupbuddy::$format->date( pb_backupbuddy::$format->localize_time( $backup['start_time'] ) ) . '<br><span class="description">' . pb_backupbuddy::$format->time_ago( $backup['start_time'] ) . ' ago</span>',
			$finish_time,
			$backup['trigger'],
			$status,
		);
		
		$recent_backup_count++;
	}

	$columns = array(
		__('Backup', 'it-l10n-backupbuddy' ),
		__('Started', 'it-l10n-backupbuddy' ),
		__('Finished', 'it-l10n-backupbuddy' ),
		__('Trigger', 'it-l10n-backupbuddy' ),
		__('Status', 'it-l10n-backupbuddy' ),
	);

	$backups = array_reverse( $backups );

	pb_backupbuddy::$ui->list_table(
		$backups,
		array(
			'action'		=>	pb_backupbuddy::page_url(),
			'columns'		=>	$columns,
			'css'			=>	'width: 100%;',
		)
	);
	
	echo '<div class="alignright actions">';
	pb_backupbuddy::$ui->note( 'Hover over items above for additional options.' );
	echo '</div>';
	
} // end if recent backups exist.


pb_backupbuddy::$ui->end_tab();




pb_backupbuddy::$ui->end_tabs();


/********** END TABS **********/











echo '<br /><br />';
?>





<?php
// Handles thickbox auto-resizing. Keep at bottom of page to avoid issues.
if ( !wp_script_is( 'media-upload' ) ) {
	wp_enqueue_script( 'media-upload' );
	wp_print_scripts( 'media-upload' );
}
?>