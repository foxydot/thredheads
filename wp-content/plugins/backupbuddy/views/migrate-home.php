<script type="text/javascript">
	jQuery(document).ready(function() {
		
		jQuery( '.pb_backupbuddy_hoveraction_migrate' ).click( function(e) {
			tb_show( 'BackupBuddy', '<?php echo pb_backupbuddy::ajax_url( 'migration_picker' ); ?>&callback_data=' + jQuery(this).attr('rel') + '&migrate=1&TB_iframe=1&width=640&height=455', null );
			return false;
		});
		
		jQuery( '.pb_backupbuddy_hoveraction_hash' ).click( function(e) {
			tb_show( 'BackupBuddy', '<?php echo pb_backupbuddy::ajax_url( 'hash' ); ?>&callback_data=' + jQuery(this).attr('rel') + '&TB_iframe=1&width=640&height=455', null );
			return false;
		});
		
		jQuery( '.pb_backupbuddy_hoveraction_send' ).click( function(e) {
			<?php if ( pb_backupbuddy::$options['importbuddy_pass_hash'] == '' ) { ?>
				alert( 'You must set an ImportBuddy password via the BackupBuddy settings page before you can send this file.' );
				return false;
			<?php } ?>
			tb_show( 'BackupBuddy', '<?php echo pb_backupbuddy::ajax_url( 'destination_picker' ); ?>&callback_data=' + jQuery(this).attr('rel') + '&sending=1&TB_iframe=1&width=640&height=455', null );
			return false;
		});
		
		jQuery( '.pb_backupbuddy_get_importbuddy' ).click( function(e) {
			<?php
			if ( pb_backupbuddy::$options['importbuddy_pass_hash'] == '' ) {
				//echo 'alert(\'' . __( 'Please set an ImportBuddy password on the BackupBuddy Settings page to download this script. This is required to prevent unauthorized access to the script when in use.', 'it-l10n-backupbuddy' ) . '\');';
				?>
				
				var password = prompt( '<?php _e( 'To download, enter a password to lock the ImportBuddy script from unauthorized access. You will be prompted for this password when you go to importbuddy.php in your browser. Since you have not defined a default password yet this will be used as your default and can be changed later from the Settings page.', 'it-l10n-backupbuddy' ); ?>' );
				if ( ( password != null ) && ( password != '' ) ) {
					window.location.href = '<?php echo pb_backupbuddy::ajax_url( 'importbuddy' ); ?>&p=' + encodeURIComponent( password );
				}
				if ( password == '' ) {
					alert( 'You have not set a default password on the Settings page so you must provide a password here to download ImportBuddy.' );
				}
				
				return false;
				<?php
			} else {
				?>
				var password = prompt( '<?php _e( 'To download, either enter a new password for just this download OR LEAVE BLANK to use your default ImportBuddy password (set on the Settings page) to lock the ImportBuddy script from unauthorized access.', 'it-l10n-backupbuddy' ); ?>' );
				if ( password != null ) {
					window.location.href = '<?php echo pb_backupbuddy::ajax_url( 'importbuddy' ); ?>&p=' + encodeURIComponent( password );
				}
				return false;
				<?php
			}
			?>
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
		
	}); // end ready.
	
	
	
	
	function pb_backupbuddy_selectdestination( destination_id, destination_title, callback_data ) {
		if ( callback_data != '' ) {
			if ( callback_data == 'importbuddy.php' ) {
				window.location.href = '<?php echo pb_backupbuddy::page_url(); ?>&destination=' + destination_id + '&destination_title=' + destination_title + '&callback_data=' + callback_data;
				return false;
			}
			jQuery.post( '<?php echo pb_backupbuddy::ajax_url( 'remote_send' ); ?>', { destination_id: destination_id, destination_title: destination_title, file: callback_data, trigger: 'migration', send_importbuddy: '1' }, 
				function(data) {
					data = jQuery.trim( data );
					if ( data.charAt(0) != '1' ) {
						alert( '<?php _e('Error starting remote send of file to migrate', 'it-l10n-backupbuddy' ); ?>:' + "\n\n" + data );
					} else {
						window.location.href = '<?php echo pb_backupbuddy::page_url(); ?>&destination=' + destination_id + '&destination_title=' + destination_title + '&callback_data=' + callback_data;
					}
				}
			);
			
			/* Try to ping server to nudge cron along since sometimes it doesnt trigger as expected. */
			jQuery.post( '<?php echo admin_url('admin-ajax.php'); ?>',
				function(data) {
				}
			);

		} else {
			window.location.href = '<?php echo pb_backupbuddy::page_url(); ?>&custom=remoteclient&destination_id=' + destination_id;
		}
	}
	
	
	
</script>



You may Restore or Migrate your site using the ImportBuddy tool with instructions below.
Hover backups in the list below for additional options such as restoring individual files (aka undelete) or
selecting to Migrate this backup to another server which sends the backup and importbuddy.php to the new server
and attempts to load it to initiate the migration. Keep a copy of this script with your backups for restoring sites directly from backups.

<?php
if ( pb_backupbuddy::$options['importbuddy_pass_hash'] == '' ) { // NO HASH SET.
	echo '<br><br><br><span class="pb_label pb_label">Important</span> <b>Set an ImportBuddy password on the <a href="';
		if ( is_network_admin() ) {
			echo network_admin_url( 'admin.php' );
		} else {
			echo admin_url( 'admin.php' );
		}
		echo '?page=pb_backupbuddy_settings">Settings</a> page before attempting to Migrate to a new server with the link in the backup list.
	</b>';
}
?>
	
<br><br>

<h3 id="pb_backupbuddy_restoremigratetitle">Restore / Migrate with ImportBuddy</h3>
<ol>
	<li>
		<a id="pb_backupbuddy_downloadimportbuddy" href="<?php echo pb_backupbuddy::ajax_url( 'importbuddy' ); ?>" class="button button-primary pb_backupbuddy_get_importbuddy">Download ImportBuddy</a> or
		<a id="pb_backupbuddy_sendimportbuddy" href="" rel="importbuddy.php" class="button button-primary pb_backupbuddy_hoveraction_send">Send ImportBuddy to a Destination</a>
	</li>
	<li>Download a backup or send it directly to destination (hover backup in list below for options).</li>
	<li>Upload importbuddy.php & backup ZIP file to the destination server directory where you want your site restored to (ie into FTP directory /public_html/your.com/ or similar).</li>
	<li>Navigate to the uploaded importbuddy.php URL in your web browser (ie http://your.com/importbuddy.php).</li>
	<li>Follow the on-screen directions until the restore / migration is complete.</li>
</ol>
<br><br>

<h3 id="pb_backupbuddy_restoremigratelisttitle">Additional Restore Options (highlight a backup)</h3>
<?php


$listing_mode = 'restore_migrate';
require_once( '_backup_listing.php' );





echo '<br><br><br><br><br>';
if ( pb_backupbuddy::$options['importbuddy_pass_hash'] == '' ) {
	echo '<a class="description" onclick="alert(\'' . __( 'Please set a RepairBuddy password on the BackupBuddy Settings page to download this script. This is required to prevent unauthorized access to the script when in use.', 'it-l10n-backupbuddy' ) . '\'); return false;" href="" style="text-decoration: none;" title="' . __( 'Download the troubleshooting & repair script, repairbuddy.php', 'it-l10n-backupbuddy' ) . '">';
} else {
	echo '<a class="description" href="' . admin_url( 'admin-ajax.php' ) . '?action=pb_backupbuddy_repairbuddy" style="text-decoration: none;" title="' . __('Download the troubleshooting & repair script, repairbuddy.php', 'it-l10n-backupbuddy' ) . '">';
}
echo __( 'Download RepairBuddy troubleshooting & repair tool.', 'it-l10n-backupbuddy' ) . '</a>';

?>