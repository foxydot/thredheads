<?php
// Tutorial
pb_backupbuddy::load_script( 'jquery.joyride-2.0.3.js' );
pb_backupbuddy::load_script( 'modernizr.mq.js' );
pb_backupbuddy::load_style( 'joyride.css' );
?>
<a href="" class="pb_backupbuddy_begintour">Tour This Page</a>
<ol id="pb_backupbuddy_tour" style="display: none;">
	<li data-id="pb_backupbuddy_downloadimportbuddy">Download the ImportBuddy tool (importbuddy.php) to restore or migrate your site.</li>
	<li data-id="pb_backupbuddy_sendimportbuddy">Send the ImportBuddy tool (importbuddy.php) to a remote destination for restoring or migrating on another server.</li>
	<li data-id="pb_backupbuddy_restoremigratelisttitle" data-button="Finish">Hover over a backup below for additional options including viewing a list of files within, viewing the contents of text-based files, restoring files, and more.</li>
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



// Check if performing an actual migration now. If so then load file and skip the rest of this page.
if ( ( pb_backupbuddy::_GET( 'callback_data' ) != '' ) && ( pb_backupbuddy::_GET( 'callback_data' ) != 'importbuddy.php' ) ) {
	require_once( '_migrate.php' );
	return;
}


// Handle remote sending ImportBuddy.
if ( pb_backupbuddy::_GET( 'callback_data' ) == 'importbuddy.php' ) {
	
	pb_backupbuddy::alert( '<span id="pb_backupbuddy_ib_sent">Sending ImportBuddy file. This may take several seconds. Please wait ...</span>' );
	pb_backupbuddy::flush();
	
	$importbuddy_file = pb_backupbuddy::$options['temp_directory'] . 'importbuddy.php';
	
	// Render ImportBuddy to temp location.
	pb_backupbuddy::$classes['core']->importbuddy( $importbuddy_file );
	$response = pb_backupbuddy::$classes['core']->send_remote_destination( $_GET['destination'], $importbuddy_file, $trigger = 'manual' );
	
	if ( file_exists( $importbuddy_file ) ) {
		unlink( $importbuddy_file ); // Delete temporary ImportBuddy file.
	}
	
	
	if ( $response === true ) {
		?>
		<script type="text/javascript">
			jQuery( '#pb_backupbuddy_ib_sent' ).html( 'ImportBuddy file successfully sent.' );
		</script>
		<?php
	} else {
		?>
		<script type="text/javascript">
			jQuery( '#pb_backupbuddy_ib_sent' ).html( 'ImportBuddy file send failure. Verify your destination settings & check logs for details.' );
		</script>
		<?php
	}
}


wp_enqueue_script( 'thickbox' );
wp_print_scripts( 'thickbox' );
wp_print_styles( 'thickbox' );



pb_backupbuddy::$ui->title( __( 'Restore / Migrate', 'it-l10n-backupbuddy' ) );



/********* Begin Migrate Settings Form *********/

$migrate_form = new pb_backupbuddy_settings( 'migrate', false, '', 200 ); // form_name, savepoint, action_destination, title_width

$migrate_form->add_setting( array(
	'type'		=>		'text',
	'name'		=>		'web_address',
	'title'		=>		__('Website address', 'it-l10n-backupbuddy' ),
	'tip'		=>		__('Website address that corresponds to the FTP path.ß', 'it-l10n-backupbuddy' ),
	'rules'		=>		'required|string[1-500]',
	'default'	=>		'http://',
	'css'		=>		'width: 200px;',
	'after'		=>		' <span class="description">(ftp path must correspond to this address)</span>',
) );

$migrate_form->add_setting( array(
	'type'		=>		'text',
	'name'		=>		'ftp_server',
	'title'		=>		__('FTP server address', 'it-l10n-backupbuddy' ),
	'tip'		=>		__('FTP server address. This must correspond to the website address URL, including path, to the destination site.', 'it-l10n-backupbuddy' ),
	'rules'		=>		'required|string[1-500]',
	'css'		=>		'width: 200px;',
) );

$migrate_form->add_setting( array(
	'type'		=>		'text',
	'name'		=>		'ftp_username',
	'title'		=>		__('FTP username', 'it-l10n-backupbuddy' ),
	'rules'		=>		'required|string[1-500]',
	'css'		=>		'width: 200px;',
) );

$migrate_form->add_setting( array(
	'type'		=>		'text',
	'name'		=>		'ftp_password',
	'title'		=>		__('FTP password', 'it-l10n-backupbuddy' ),
	'rules'		=>		'required|string[1-500]',
	'css'		=>		'width: 200px;',
) );

$migrate_form->add_setting( array(
	'type'		=>		'text',
	'name'		=>		'ftp_path',
	'title'		=>		__('FTP remote path (optional)', 'it-l10n-backupbuddy' ),
	'tip'		=>		__('This is the remote path / directory for the server. You may use an FTP client to connect to your FTP to determine the exact path.', 'it-l10n-backupbuddy' ),
	'rules'		=>		'required|string[1-500]',
	'after'		=>		' <span class="description">(must correspond to website address)</span>',
	'css'		=>		'width: 200px;',
) );

$migrate_form->add_setting( array(
	'type'		=>		'checkbox',
	'name'		=>		'ftps',
	'title'		=>		__( 'Use FTPs encryption', 'it-l10n-backupbuddy' ),
	'options'	=>		array( 'unchecked' => '0', 'checked' => '1' ),
	//'tip'		=>		__('[Default: Modern] - If you are encountering difficulty backing up due to WordPress cron, HTTP Loopbacks, or other features specific to version 2.x you can try classic mode which runs like BackupBuddy v1.x did.', 'it-l10n-backupbuddy' ),
	'rules'		=>		'required',
) );

$result = $migrate_form->process(); // Handles processing the submitted form (if applicable).
echo '<pre>' . print_r( $result, true ) . '</pre>';

if ( count( $result['errors'] ) > 0 ) { // Form errors.
} else { // No errors.
	
	
	/*
	wp_schedule_single_event( time(), pb_backupbuddy::cron_tag( 'remote_send' ), array( $_POST['destination_id'], pb_backupbuddy::$options['backup_directory'] . $_POST['file'] ) );
	spawn_cron( time() + 150 ); // Adds > 60 seconds to get around once per minute cron running limit.
	update_option( '_transient_doing_cron', 0 ); // Prevent cron-blocking for next item.
	*/
}

$view_data['migrate_form'] = &$migrate_form; // For use in view.

/********* End Migrate Settings Form *********/


// Load view.
$view_data['backups'] = pb_backupbuddy::$classes['core']->backups_list( 'migrate' );
pb_backupbuddy::load_view( 'migrate-home', $view_data );



?>
