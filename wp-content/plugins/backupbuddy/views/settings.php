<?php
if ( !is_admin() ) { die( 'Access Denied.' ); }
?>



<style type="text/css">
	.pb_backupbuddy_customize_email_error_row, .pb_backupbuddy_customize_email_scheduled_start_row, .pb_backupbuddy_customize_email_scheduled_complete_row {
		display: none;
	}
</style>
<script type="text/javascript">
	var pb_settings_changed = false;
	
	jQuery(document).ready(function() {
		
		
		jQuery( 'a' ) .click( function(e) {
			if ( jQuery(this).attr( 'class' ) == 'ui-tabs-anchor' ) {
				if ( true == pb_settings_changed ) {
					
					if ( confirm( 'You have made changes that you have not saved by selecting the "Save Settings" button at the bottom of the page. Abandon changes without saving?' ) ) {
						// Abandon!
						pb_settings_changed = false;
						return true;
					} else {
						e.stopPropagation();
						e.stopImmediatePropagation();
						return false;
					}
				}
			}
		});
		jQuery( '.pb_form' ).change( function() {
			pb_settings_changed = true;
		});
		
		
		
		
	});
	
	function pb_backupbuddy_selectdestination( destination_id, destination_title, callback_data ) {
		window.location.href = '<?php echo pb_backupbuddy::page_url(); ?>&custom=remoteclient&destination_id=' + destination_id;
	}
</script>



<br><br>



<?php
if ( is_numeric( pb_backupbuddy::_GET( 'tab' ) ) ) {
	$active_tab = pb_backupbuddy::_GET( 'tab' );
} else {
	$active_tab = 0;
}
pb_backupbuddy::$ui->start_tabs(
	'settings',
	array(
		array(
			'title'		=>		__( 'General', 'it-l10n-backupbuddy' ),
			'slug'		=>		'general',
			'css'		=>		'margin-top: -12px;',
		),
		array(
			'title'		=>		__( 'Backup Profiles', 'it-l10n-backupbuddy' ),
			'slug'		=>		'profiles',
			'css'		=>		'margin-top: -12px;',
		),
		array(
			'title'		=>		__( 'Advanced & Troubleshooting', 'it-l10n-backupbuddy' ),
			'slug'		=>		'advanced',
			'css'		=>		'margin-top: -12px;',
		),
	),
	'width: 100%;',
	true,
	$active_tab
);



pb_backupbuddy::$ui->start_tab( 'general' );
require_once( 'settings/_general.php' );
pb_backupbuddy::$ui->end_tab();



pb_backupbuddy::$ui->start_tab( 'profiles' );
require_once( 'settings/_profiles.php' );
pb_backupbuddy::$ui->end_tab();



pb_backupbuddy::$ui->start_tab( 'advanced' );
require_once( 'settings/_advanced.php' );
pb_backupbuddy::$ui->end_tab();



?>





<script type="text/javascript">
	
	
	
	function pb_backupbuddy_selectdestination( destination_id, destination_title, callback_data ) {
		window.location.href = '<?php
			if ( is_network_admin() ) {
				echo network_admin_url( 'admin.php' );
			} else {
				echo admin_url( 'admin.php' );
			}
		?>?page=pb_backupbuddy_backup&custom=remoteclient&destination_id=' + destination_id;
	}
</script>


<?php
// Handles thickbox auto-resizing. Keep at bottom of page to avoid issues.
if ( !wp_script_is( 'media-upload' ) ) {
	wp_enqueue_script( 'media-upload' );
	wp_print_scripts( 'media-upload' );
}
?>







</div>



