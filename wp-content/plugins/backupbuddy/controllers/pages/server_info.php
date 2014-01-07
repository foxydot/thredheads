<?php
// Tutorial
pb_backupbuddy::load_script( 'jquery.joyride-2.0.3.js' );
pb_backupbuddy::load_script( 'modernizr.mq.js' );
pb_backupbuddy::load_style( 'joyride.css' );
?>
<a href="" class="pb_backupbuddy_begintour">Tour This Page</a>
<ol id="pb_backupbuddy_tour" style="display: none;">
	<li data-id="ui-id-1">View server configuration details, security information, server paths, etc.</li>
	<li data-id="ui-id-2">View database information as well as tables excluded from backups.</li>
	<li data-id="ui-id-3">View your site's files in either a graphical format or a listing. The listing also notes exclusions from backups.</li>
	<li data-id="ui-id-4" data-button="Finish">Additional site tools for managing CRON schedules and database text search & replace.</li>
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
if ( !defined( 'PB_IMPORTBUDDY' ) ) { // NOT IN IMPORTBUDDY:
	pb_backupbuddy::load_script( 'admin.js' );
	
	
	
	pb_backupbuddy::$ui->title( __( 'Server Information', 'it-l10n-backupbuddy' ) . ' ' . pb_backupbuddy::video( '7NI7oePvxZg', __( 'Server information', 'it-l10n-backupbuddy' ), false ) );
	pb_backupbuddy::$classes['core']->versions_confirm();
	
	$default_tab = 0;
	if ( is_numeric( pb_backupbuddy::_GET( 'tab' ) ) ) {
		$default_tab = pb_backupbuddy::_GET( 'tab' );
	}
	
	echo '<br><br>';
	pb_backupbuddy::$ui->start_tabs(
		'getting_started',
		array(
			array(
				'title'		=>		__( 'Server', 'it-l10n-backupbuddy' ),
				'slug'		=>		'server',
			),
			array(
				'title'		=>		__( 'Database', 'it-l10n-backupbuddy' ),
				'slug'		=>		'database',
			),
			array(
				'title'		=>		__( 'Files', 'it-l10n-backupbuddy' ),
				'slug'		=>		'files',
			),
			array(
				'title'		=>		__( 'Tools', 'it-l10n-backupbuddy' ),
				'slug'		=>		'tools',
			),
		),
		'width: 100%;',
		true,
		$default_tab
	);
	echo '<br>';
	
	
	
	pb_backupbuddy::$ui->start_tab( 'server' );
		
		require_once( 'server_info/server.php' );
		
		
		require_once( 'server_info/permissions.php' );
		
		
		$wp_upload_dir = wp_upload_dir();
		$wp_settings = array();
		
		if ( isset( $wp_upload_dir['path'] ) ) {
			$wp_settings[] = array( 'Upload File Path', $wp_upload_dir['path'], 'wp_upload_dir()' );
		}
		if ( isset( $wp_upload_dir['url'] ) ) {
			$wp_settings[] = array( 'Upload URL', $wp_upload_dir['url'], 'wp_upload_dir()' );
		}
		if ( isset( $wp_upload_dir['subdir'] ) ) {
			$wp_settings[] = array( 'Upload Subdirectory', $wp_upload_dir['subdir'], 'wp_upload_dir()');
		}
		if ( isset( $wp_upload_dir['baseurl'] ) ) {
			$wp_settings[] = array( 'Upload Base URL', $wp_upload_dir['baseurl'], 'wp_upload_dir()' );
		}
		if ( isset( $wp_upload_dir['basedir'] ) ) {
			$wp_settings[] = array( 'Upload Base Directory', $wp_upload_dir['basedir'], 'wp_upload_dir()' );
		}
		$wp_settings[] = array( 'Site URL', site_url(), 'site_url()' );
		$wp_settings[] = array( 'Home URL', home_url(), 'home_url()' );
		$wp_settings[] = array( 'WordPress Root Path', ABSPATH, 'ABSPATH' );
		
		// Multisite extras:
		$wp_settings_multisite = array();
		if ( is_multisite() ) {
			$wp_settings[] = array( 'Network Site URL', network_site_url(), 'network_site_url()' );
			$wp_settings[] = array( 'Network Home URL', network_home_url(), 'network_home_url()' );
		}
		
		// Display WP settings..
		pb_backupbuddy::$ui->list_table(
			$wp_settings,
			array(
				'action'					=>	pb_backupbuddy::page_url(),
				'columns'					=>	array(
													__( 'URLs & Paths', 'it-l10n-backupbuddy' ),
													__( 'Value', 'it-l10n-backupbuddy' ),
													__( 'Obtained via', 'it-l10n-backupbuddy' ),
												),
				'css'						=>		'width: 100%;',
			)
		);
		
		
	pb_backupbuddy::$ui->end_tab();
	
	
	
	// This page can take a bit to run.
	// Runs AFTER server information is displayed so we can view the default limits for the server.
	pb_backupbuddy::set_greedy_script_limits();
	
	
	
	pb_backupbuddy::$ui->start_tab( 'database' );
		
		require_once( 'server_info/database.php' );
		
	pb_backupbuddy::$ui->end_tab();
	
	
	
	pb_backupbuddy::$ui->start_tab( 'files' );
		
		pb_backupbuddy::$ui->start_metabox( __( 'Site Size Map', 'it-l10n-backupbuddy' ) . ' ' .pb_backupbuddy::video( 'XfZy-7DdbS0#67', __( 'Site Size Map', 'it-l10n-backupbuddy' ), false ), true, 'width: 100%;' );
		require_once( 'server_info/site_size.php' );
		pb_backupbuddy::$ui->end_metabox();
		
		
	pb_backupbuddy::$ui->end_tab();
	
	
	
	pb_backupbuddy::$ui->start_tab( 'tools' );
		
		pb_backupbuddy::$ui->start_metabox( __( 'WordPress Scheduled Actions (CRON)', 'it-l10n-backupbuddy' ) . ' ' .pb_backupbuddy::video( '7NI7oePvxZg#94', __( 'WordPress Scheduled Actions (CRON)', 'it-l10n-backupbuddy' ), false ), true, 'width: 100%;' );
		require_once( 'server_info/cron.php' );
		pb_backupbuddy::$ui->end_metabox();
		
		echo '<a name="database_replace"></a>';
		pb_backupbuddy::$ui->start_metabox( 'Advanced: ' . __( 'Database Mass Text Replacement', 'it-l10n-backupbuddy' ), true, 'width: 100%;' );
		pb_backupbuddy::load_view( '_server_tools-database_replace' );
		pb_backupbuddy::$ui->end_metabox();
		
	pb_backupbuddy::$ui->end_tab();
	
	
	echo '<br style="clear: both;"><br><br>';
	pb_backupbuddy::$ui->end_tabs();
	
	
	
	// Handles thickbox auto-resizing. Keep at bottom of page to avoid issues.
	if ( !wp_script_is( 'media-upload' ) ) {
		wp_enqueue_script( 'media-upload' );
		wp_print_scripts( 'media-upload' );
	}
	
} else { // INSIDE IMPORTBUDDY:
	if ( pb_backupbuddy::_GET( 'skip_serverinfo' ) == '' ) { // Give a workaround to skip this.
		require_once( 'server_info/server.php' );
	} else {
		echo '{Skipping Server Info. section based on querystring.}';
	}
}
?>