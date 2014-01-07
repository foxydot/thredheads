<?php
if ( !is_admin() ) { die( 'Access Denied.' ); }


/*
IMPORTANT INCOMING VARIABLES (expected to be set before this file is loaded):
$profile	Index number of profile.
*/
if ( isset( pb_backupbuddy::$options['profiles'][$profile] ) ) {
	$profile_id = $profile;
	$profile_array = &pb_backupbuddy::$options['profiles'][$profile];
	$profile_array = array_merge( pb_backupbuddy::settings( 'profile_defaults' ), $profile_array );
} else {
	die( 'Error #565676756. Invalid profile ID index.' );
}


?>
<script type="text/javascript">
	jQuery(document).ready(function() {
		
		jQuery( '.pb_backupbuddy_filetree_exclude' ).click( function() { alert( 'wut!' ); } );
		
		/* Begin Directory / File Selector */
		jQuery(document).on( 'click', '.pb_backupbuddy_filetree_exclude', function(){
			text = jQuery(this).parent().parent().find( 'a' ).attr( 'rel' );
			if ( ( text == 'wp-config.php' ) || ( text == '/wp-content/' ) || ( text == '/wp-content/uploads/' ) || ( text == '<?php echo pb_backupbuddy::$options['backup_directory']; ?>' ) || ( text == '/wp-content/uploads/backupbuddy_temp/' ) ) {
				alert( '<?php _e('You cannot exclude /wp-content/, /wp-content/uploads/, or BackupBuddy directories.  However, you may exclude subdirectories within these. BackupBuddy directories such as backupbuddy_backups are automatically excluded and cannot be added to exclusion list.', 'it-l10n-backupbuddy' );?>' );
			} else {
				jQuery('#pb_backupbuddy_excludes').val( text + "\n" + jQuery('#pb_backupbuddy_excludes').val() );
			}
			return false;
		});
	});
</script>



<?php




if ( $profile_array['type'] == 'defaults' ) {
	$before_text = __('Excluded files & directories (relative to WordPress root)' , 'it-l10n-backupbuddy' );
} else {
	$before_text = __('Excluded files & directories for this profile (Global defaults do not apply; relative to WordPress root)' , 'it-l10n-backupbuddy' );
}


if ( $profile_array['type'] != 'defaults' ) {
	$settings_form->add_setting( array(
		'type'		=>		'checkbox',
		'name'		=>		'profiles#' . $profile_id . '#profile_globalexcludes',
		'options'	=>		array( 'unchecked' => '0', 'checked' => '1' ),
		'title'		=>		'Use global defaults for files to backup?',
		'after'		=>		' Use global defaults',
		'css'		=>		'',
	) );
}


$settings_form->add_setting( array(
	'type'		=>		'textarea',
	'name'		=>		'profiles#' . $profile_id . '#excludes',
	'title'		=>		'Click directories to navigate or click <img src="' . pb_backupbuddy::plugin_url() .'/images/redminus.png" style="vertical-align: -3px;"> to exclude.' . ' ' .
						pb_backupbuddy::tip( __('Click on a directory name to navigate directories. Click the red minus sign to the right of a directory to place it in the exclusion list. /wp-content/, /wp-content/uploads/, and BackupBuddy backup & temporary directories cannot be excluded. BackupBuddy directories are automatically excluded.', 'it-l10n-backupbuddy' ), '', false ) .
						'<br><div id="exlude_dirs" class="jQueryOuterTree"></div>',
	//'tip'		=>		,
	'rules'		=>		'string[0-9000]',
	'css'		=>		'width: 100%; height: 135px;',
	'before'	=>		$before_text . pb_backupbuddy::tip( __('List paths relative to the WordPress installation directory to be excluded from backups.  You may use the directory selector to the left to easily exclude directories by ctrl+clicking them.  Paths are relative to root, for example: /wp-content/uploads/junk/', 'it-l10n-backupbuddy' ), '', false ) . '<br>',
	'after'		=>		'<span class="description">' . __( 'One file or directory exclusion per line. This may be manually edited.', 'it-l10n-backupbuddy' ) . '</span>',
) );



