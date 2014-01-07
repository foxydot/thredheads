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


pb_backupbuddy::load_script( 'filetree.js' );
pb_backupbuddy::load_style( 'filetree.css' );

?>
<script type="text/javascript">
	jQuery(document).ready(function() {
		// Show options on hover.
		jQuery(document).on('mouseover mouseout', '.jqueryFileTree > li a', function(event) {
			if ( event.type == 'mouseover' ) {
				jQuery(this).children( '.pb_backupbuddy_treeselect_control' ).css( 'visibility', 'visible' );
			} else {
				jQuery(this).children( '.pb_backupbuddy_treeselect_control' ).css( 'visibility', 'hidden' );
			}
		});
		
		jQuery('#exlude_dirs').fileTree(
			{
				root: '/',
				multiFolder: false,
				script: '<?php echo pb_backupbuddy::ajax_url( 'exclude_tree' ); ?>'
			},
			function(file) {
				if ( ( file == 'wp-config.php' ) ) {
					alert( '<?php _e('You cannot exclude wp-config.php.', 'it-l10n-backupbuddy' );?>' );
				} else {
					jQuery( '#pb_backupbuddy_profiles__<?php echo $profile_id; ?>__excludes' ).val( file + "\n" + jQuery( '#pb_backupbuddy_profiles__<?php echo $profile_id; ?>__excludes' ).val() );
				}
			},
			function(directory) {
				if ( ( directory == '/wp-content/' ) || ( directory == '/wp-content/uploads/' ) || ( directory == '<?php echo pb_backupbuddy::$options['backup_directory']; ?>' ) || ( directory == '/wp-content/uploads/backupbuddy_temp/' ) ) {
					alert( '<?php _e('You cannot exclude /wp-content/, /wp-content/uploads/, or BackupBuddy directories.  However, you may exclude subdirectories within these. BackupBuddy directories such as backupbuddy_backups are automatically excluded and cannot be added to exclusion list.', 'it-l10n-backupbuddy' );?>' );
				} else {
					jQuery( '#pb_backupbuddy_profiles__<?php echo $profile_id; ?>__excludes' ).val( directory + "\n" + jQuery( '#pb_backupbuddy_profiles__<?php echo $profile_id; ?>__excludes' ).val() );
				}
			}
		);
		
		
		/* Begin Table Selector */
		jQuery( '.pb_backupbuddy_table_addexclude' ).click(function(){
			jQuery('#pb_backupbuddy_profiles__<?php echo $profile_id; ?>__mysqldump_additional_excludes').val( jQuery(this).parent().parent().parent().find( 'a' ).attr( 'alt' ) + "\n" + jQuery('#pb_backupbuddy_profiles__<?php echo $profile_id; ?>__mysqldump_additional_excludes').val() );
			return false;
		});
		jQuery( '.pb_backupbuddy_table_addinclude' ).click(function(){
			jQuery('#pb_backupbuddy_profiles__<?php echo $profile_id; ?>__mysqldump_additional_includes').val( jQuery(this).parent().parent().parent().find( 'a' ).attr( 'alt' ) + "\n" + jQuery('#pb_backupbuddy_profiles__<?php echo $profile_id; ?>__mysqldump_additional_includes').val() );
			return false;
		});
	});
</script>



<?php


global $wpdb;
if ( $profile_array['type'] == 'defaults' ) {
	$title = '<b>Default</b> base database tables to backup';
	$options = array( '0' => 'This WordPress\' tables (prefix ' . $wpdb->prefix . ')', '1' => 'All tables in database (including non-WordPress)' );
} else {
	$title = 'Base database tables to backup for this profile';
	$options = array( '-1' => 'Use global default', '0' => 'This WordPress\' tables (prefix ' . $wpdb->prefix . ')', '1' => 'All tables in database (including non-WordPress)' );
}
$settings_form->add_setting( array(
	'type'		=>		'radio',
	'name'		=>		'profiles#' . $profile_id . '#backup_nonwp_tables',
	'options'	=>		$options,
	'title'		=>		$title,
	'tip'		=>		__( '[Default: This WordPress\' tables prefix (' . $wpdb->prefix . ')] - Determines the default set of tables to backup.  If this WordPress\' tables is selected then only tables with the same prefix (for example ' . $wpdb->prefix . ' for this installation) will be backed up by default.  If all are selected then all tables will be backed up by default. Additional inclusions & exclusions may be defined below.', 'it-l10n-backupbuddy' ),
	'css'		=>		'',
	'rules'		=>		'required',
) );


if ( $profile_array['type'] != 'defaults' ) {
	$settings_form->add_setting( array(
		'type'		=>		'checkbox',
		'name'		=>		'profiles#' . $profile_id . '#profile_globaltables',
		'options'	=>		array( 'unchecked' => '0', 'checked' => '1' ),
		'title'		=>		'Use global defaults for tables to backup?',
		'after'		=>		' Use global defaults',
		'css'		=>		'',
	) );
}


function pb_additional_tables( $profile_array, $display_size = false ) {
	
	$return = '';
	$size_string = '';
	
	if ( true === $display_size ) {
		$result = mysql_query("SHOW TABLE STATUS");
	} else {
		$result = mysql_query("SHOW TABLES");
	}
	while( $rs = mysql_fetch_array( $result ) ) {
		
		if ( true === $display_size ) {
			// Fix up row count and average row length for InnoDB engine which returns inaccurate (and changing) values for these.
			if ( 'InnoDB' === $rs[ 'Engine' ] ) {
				if ( false !== ( $resultc = mysql_query( "SELECT COUNT(1) FROM `{$rs[ 'Name' ]}`" ) ) ) {
					if ( false !== ( $row = mysql_fetch_row( $resultc ) ) ) {
						if ( 0 < ( $rs[ 'Rows' ] = $row[ 0 ] ) ) {
							$rs[ 'Avg_row_length' ] = ( $rs[ 'Data_length' ] / $rs[ 'Rows' ] );
						}
					}
					mysql_free_result( $resultc );
				}
			}
			
			// Table size.
			$size_string = ' (' . pb_backupbuddy::$format->file_size( ( $rs['Data_length'] + $rs['Index_length'] ) ) . ') ';
			
		} // end if display size enabled.
		
		$return .= '<li class="file ext_sql collapsed">';
		$return .= '<a rel="/" alt="' . $rs[0] . '">' . $rs[0] . $size_string;
		$return .= '<div class="pb_backupbuddy_treeselect_control">';
		$return .= '<img src="' . pb_backupbuddy::plugin_url() . '/images/redminus.png" style="vertical-align: -3px;" title="Add to exclusions..." class="pb_backupbuddy_table_addexclude"> <img src="' . pb_backupbuddy::plugin_url() . '/images/greenplus.png" style="vertical-align: -3px;" title="Add to inclusions..." class="pb_backupbuddy_table_addinclude">';
		$return .= '</div>';
		$return .= '</a>';
		$return .= '</li>';
	}
	
	return '<div class="jQueryOuterTree" style="position: absolute; height: 160px;"><ul class="jqueryFileTree">' . $return . '</ul></div>';
	
} // end pb_additional_tables().



$settings_form->add_setting( array(
	'type'		=>		'textarea',
	'name'		=>		'profiles#' . $profile_id . '#mysqldump_additional_includes',
	'title'		=>		'Hover tables & click <img src="' . pb_backupbuddy::plugin_url() .'/images/greenplus.png" style="vertical-align: -3px;"> to include, <img src="' . pb_backupbuddy::plugin_url() .'/images/redminus.png" style="vertical-align: -3px;"> to exclude.' . ' ' . pb_additional_tables( $profile_array ),
	'before'	=>		__('<b>Inclusions</b> beyond base', 'it-l10n-backupbuddy' ) . ':',
	//'after'		=>		'<span class="description">' . __( 'One table per line. This may be manually edited.', 'it-l10n-backupbuddy' ) . '</span>',
	'tip'		=>		__('Additional databases tables to include OR exclude IN ADDITION to the DEFAULTS determined by the previous option. You may override defaults with exclusions. Excluding tables may result in an incomplete or broken backup so exercise caution.', 'it-l10n-backupbuddy' ),
	'rules'		=>		'',
	'css'		=>		'width: 100%;',
) );
$settings_form->add_setting( array(
	'type'		=>		'textarea',
	'name'		=>		'profiles#' . $profile_id . '#mysqldump_additional_excludes',
	//'title'		=>		__('Additional tables to <b>exclude</b>', 'it-l10n-backupbuddy' ) . '<br><span class="description">' . __( 'One table per line.', 'it-l10n-backupbuddy' ) . '</span>',
	'title'		=>		'&nbsp;',
	'before'	=>		__('<b>Exclusions</b> beyond base', 'it-l10n-backupbuddy' ) . ':',
	'after'		=>		'<span class="description">' . __( 'One table per line. This may be manually edited.', 'it-l10n-backupbuddy' ) . '</span>',
	'tip'		=>		__('Additional databases tables to EXCLUDE from the backup. Exclusions are exempted after calculating defaults and additional table includes first. These may include non-WordPress and WordPress tables. WARNING: Excluding WordPress tables results in an incomplete backup and could result in failure in the ability to restore or data loss. Use with caution.', 'it-l10n-backupbuddy' ),
	'rules'		=>		'',
	'css'		=>		'width: 100%;',
) );



?>





<style type="text/css">
	/* Core Styles - USED BY DIRECTORY EXCLUDER */
	.jqueryFileTree LI.directory { background: url('<?php echo pb_backupbuddy::plugin_url(); ?>/images/filetree/directory.png') 6px 6px no-repeat; }
	.jqueryFileTree LI.expanded { background: url('<?php echo pb_backupbuddy::plugin_url(); ?>/images/filetree/folder_open.png') 6px 6px no-repeat; }
	.jqueryFileTree LI.file { background: url('<?php echo pb_backupbuddy::plugin_url(); ?>/images/filetree/file.png') 6px 6px no-repeat; }
	.jqueryFileTree LI.wait { background: url('<?php echo pb_backupbuddy::plugin_url(); ?>/images/filetree/spinner.gif') 6px 6px no-repeat; }
	/* File Extensions*/
	.jqueryFileTree LI.ext_3gp { background: url('<?php echo pb_backupbuddy::plugin_url(); ?>/images/filetree/film.png') 6px 6px no-repeat; }
	.jqueryFileTree LI.ext_afp { background: url('<?php echo pb_backupbuddy::plugin_url(); ?>/images/filetree/code.png') 6px 6px no-repeat; }
	.jqueryFileTree LI.ext_afpa { background: url('<?php echo pb_backupbuddy::plugin_url(); ?>/images/filetree/code.png') 6px 6px no-repeat; }
	.jqueryFileTree LI.ext_asp { background: url('<?php echo pb_backupbuddy::plugin_url(); ?>/images/filetree/code.png') 6px 6px no-repeat; }
	.jqueryFileTree LI.ext_aspx { background: url('<?php echo pb_backupbuddy::plugin_url(); ?>/images/filetree/code.png') 6px 6px no-repeat; }
	.jqueryFileTree LI.ext_avi { background: url('<?php echo pb_backupbuddy::plugin_url(); ?>/images/filetree/film.png') 6px 6px no-repeat; }
	.jqueryFileTree LI.ext_bat { background: url('<?php echo pb_backupbuddy::plugin_url(); ?>/images/filetree/application.png') 6px 6px no-repeat; }
	.jqueryFileTree LI.ext_bmp { background: url('<?php echo pb_backupbuddy::plugin_url(); ?>/images/filetree/picture.png') 6px 6px no-repeat; }
	.jqueryFileTree LI.ext_c { background: url('<?php echo pb_backupbuddy::plugin_url(); ?>/images/filetree/code.png') 6px 6px no-repeat; }
	.jqueryFileTree LI.ext_cfm { background: url('<?php echo pb_backupbuddy::plugin_url(); ?>/images/filetree/code.png') 6px 6px no-repeat; }
	.jqueryFileTree LI.ext_cgi { background: url('<?php echo pb_backupbuddy::plugin_url(); ?>/images/filetree/code.png') 6px 6px no-repeat; }
	.jqueryFileTree LI.ext_com { background: url('<?php echo pb_backupbuddy::plugin_url(); ?>/images/filetree/application.png') 6px 6px no-repeat; }
	.jqueryFileTree LI.ext_cpp { background: url('<?php echo pb_backupbuddy::plugin_url(); ?>/images/filetree/code.png') 6px 6px no-repeat; }
	.jqueryFileTree LI.ext_css { background: url('<?php echo pb_backupbuddy::plugin_url(); ?>/images/filetree/css.png') 6px 6px no-repeat; }
	.jqueryFileTree LI.ext_doc { background: url('<?php echo pb_backupbuddy::plugin_url(); ?>/images/filetree/doc.png') 6px 6px no-repeat; }
	.jqueryFileTree LI.ext_exe { background: url('<?php echo pb_backupbuddy::plugin_url(); ?>/images/filetree/application.png') 6px 6px no-repeat; }
	.jqueryFileTree LI.ext_gif { background: url('<?php echo pb_backupbuddy::plugin_url(); ?>/images/filetree/picture.png') 6px 6px no-repeat; }
	.jqueryFileTree LI.ext_fla { background: url('<?php echo pb_backupbuddy::plugin_url(); ?>/images/filetree/flash.png') 6px 6px no-repeat; }
	.jqueryFileTree LI.ext_h { background: url('<?php echo pb_backupbuddy::plugin_url(); ?>/images/filetree/code.png') 6px 6px no-repeat; }
	.jqueryFileTree LI.ext_htm { background: url('<?php echo pb_backupbuddy::plugin_url(); ?>/images/filetree/html.png') 6px 6px no-repeat; }
	.jqueryFileTree LI.ext_html { background: url('<?php echo pb_backupbuddy::plugin_url(); ?>/images/filetree/html.png') 6px 6px no-repeat; }
	.jqueryFileTree LI.ext_jar { background: url('<?php echo pb_backupbuddy::plugin_url(); ?>/images/filetree/java.png') 6px 6px no-repeat; }
	.jqueryFileTree LI.ext_jpg { background: url('<?php echo pb_backupbuddy::plugin_url(); ?>/images/filetree/picture.png') 6px 6px no-repeat; }
	.jqueryFileTree LI.ext_jpeg { background: url('<?php echo pb_backupbuddy::plugin_url(); ?>/images/filetree/picture.png') 6px 6px no-repeat; }
	.jqueryFileTree LI.ext_js { background: url('<?php echo pb_backupbuddy::plugin_url(); ?>/images/filetree/script.png') 6px 6px no-repeat; }
	.jqueryFileTree LI.ext_lasso { background: url('<?php echo pb_backupbuddy::plugin_url(); ?>/images/filetree/code.png') 6px 6px no-repeat; }
	.jqueryFileTree LI.ext_log { background: url('<?php echo pb_backupbuddy::plugin_url(); ?>/images/filetree/txt.png') 6px 6px no-repeat; }
	.jqueryFileTree LI.ext_m4p { background: url('<?php echo pb_backupbuddy::plugin_url(); ?>/images/filetree/music.png') 6px 6px no-repeat; }
	.jqueryFileTree LI.ext_mov { background: url('<?php echo pb_backupbuddy::plugin_url(); ?>/images/filetree/film.png') 6px 6px no-repeat; }
	.jqueryFileTree LI.ext_mp3 { background: url('<?php echo pb_backupbuddy::plugin_url(); ?>/images/filetree/music.png') 6px 6px no-repeat; }
	.jqueryFileTree LI.ext_mp4 { background: url('<?php echo pb_backupbuddy::plugin_url(); ?>/images/filetree/film.png') 6px 6px no-repeat; }
	.jqueryFileTree LI.ext_mpg { background: url('<?php echo pb_backupbuddy::plugin_url(); ?>/images/filetree/film.png') 6px 6px no-repeat; }
	.jqueryFileTree LI.ext_mpeg { background: url('<?php echo pb_backupbuddy::plugin_url(); ?>/images/filetree/film.png') 6px 6px no-repeat; }
	.jqueryFileTree LI.ext_ogg { background: url('<?php echo pb_backupbuddy::plugin_url(); ?>/images/filetree/music.png') 6px 6px no-repeat; }
	.jqueryFileTree LI.ext_pcx { background: url('<?php echo pb_backupbuddy::plugin_url(); ?>/images/filetree/picture.png') 6px 6px no-repeat; }
	.jqueryFileTree LI.ext_pdf { background: url('<?php echo pb_backupbuddy::plugin_url(); ?>/images/filetree/pdf.png') 6px 6px no-repeat; }
	.jqueryFileTree LI.ext_php { background: url('<?php echo pb_backupbuddy::plugin_url(); ?>/images/filetree/php.png') 6px 6px no-repeat; }
	.jqueryFileTree LI.ext_png { background: url('<?php echo pb_backupbuddy::plugin_url(); ?>/images/filetree/picture.png') 6px 6px no-repeat; }
	.jqueryFileTree LI.ext_ppt { background: url('<?php echo pb_backupbuddy::plugin_url(); ?>/images/filetree/ppt.png') 6px 6px no-repeat; }
	.jqueryFileTree LI.ext_psd { background: url('<?php echo pb_backupbuddy::plugin_url(); ?>/images/filetree/psd.png') 6px 6px no-repeat; }
	.jqueryFileTree LI.ext_pl { background: url('<?php echo pb_backupbuddy::plugin_url(); ?>/images/filetree/script.png') 6px 6px no-repeat; }
	.jqueryFileTree LI.ext_py { background: url('<?php echo pb_backupbuddy::plugin_url(); ?>/images/filetree/script.png') 6px 6px no-repeat; }
	.jqueryFileTree LI.ext_rb { background: url('<?php echo pb_backupbuddy::plugin_url(); ?>/images/filetree/ruby.png') 6px 6px no-repeat; }
	.jqueryFileTree LI.ext_rbx { background: url('<?php echo pb_backupbuddy::plugin_url(); ?>/images/filetree/ruby.png') 6px 6px no-repeat; }
	.jqueryFileTree LI.ext_rhtml { background: url('<?php echo pb_backupbuddy::plugin_url(); ?>/images/filetree/ruby.png') 6px 6px no-repeat; }
	.jqueryFileTree LI.ext_rpm { background: url('<?php echo pb_backupbuddy::plugin_url(); ?>/images/filetree/linux.png') 6px 6px no-repeat; }
	.jqueryFileTree LI.ext_ruby { background: url('<?php echo pb_backupbuddy::plugin_url(); ?>/images/filetree/ruby.png') 6px 6px no-repeat; }
	.jqueryFileTree LI.ext_sql { background: url('<?php echo pb_backupbuddy::plugin_url(); ?>/images/filetree/db.png') 6px 6px no-repeat; }
	.jqueryFileTree LI.ext_swf { background: url('<?php echo pb_backupbuddy::plugin_url(); ?>/images/filetree/flash.png') 6px 6px no-repeat; }
	.jqueryFileTree LI.ext_tif { background: url('<?php echo pb_backupbuddy::plugin_url(); ?>/images/filetree/picture.png') 6px 6px no-repeat; }
	.jqueryFileTree LI.ext_tiff { background: url('<?php echo pb_backupbuddy::plugin_url(); ?>/images/filetree/picture.png') 6px 6px no-repeat; }
	.jqueryFileTree LI.ext_txt { background: url('<?php echo pb_backupbuddy::plugin_url(); ?>/images/filetree/txt.png') 6px 6px no-repeat; }
	.jqueryFileTree LI.ext_vb { background: url('<?php echo pb_backupbuddy::plugin_url(); ?>/images/filetree/code.png') 6px 6px no-repeat; }
	.jqueryFileTree LI.ext_wav { background: url('<?php echo pb_backupbuddy::plugin_url(); ?>/images/filetree/music.png') 6px 6px no-repeat; }
	.jqueryFileTree LI.ext_wmv { background: url('<?php echo pb_backupbuddy::plugin_url(); ?>/images/filetree/film.png') 6px 6px no-repeat; }
	.jqueryFileTree LI.ext_xls { background: url('<?php echo pb_backupbuddy::plugin_url(); ?>/images/filetree/xls.png') 6px 6px no-repeat; }
	.jqueryFileTree LI.ext_xml { background: url('<?php echo pb_backupbuddy::plugin_url(); ?>/images/filetree/code.png') 6px 6px no-repeat; }
	.jqueryFileTree LI.ext_zip { background: url('<?php echo pb_backupbuddy::plugin_url(); ?>/images/filetree/zip.png') 6px 6px no-repeat; }
</style>