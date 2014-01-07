<?php
if ( !is_admin() ) { die( 'Access Denied.' ); }

echo '<br>';
$settings_form = new pb_backupbuddy_settings( 'advanced_settings', '', 'tab=2', 320 );



$settings_form->add_setting( array(
	'type'		=>		'title',
	'name'		=>		'title_basic',
	'title'		=>		__( 'Basic Operation', 'it-l10n-backupbuddy' ),
) );






$settings_form->add_setting( array(
	'type'		=>		'checkbox',
	'name'		=>		'lock_archives_directory',
	'options'	=>		array( 'unchecked' => '0', 'checked' => '1' ),
	'title'		=>		__( 'Lock archive directory (high security)', 'it-l10n-backupbuddy' ),
	'tip'		=>		__( '[Default: disabled] - When enabled all downloads of archives via the web will be prevented under all circumstances via .htaccess file. If your server permits it, they will only be unlocked temporarily on click to download. If your server does not support this unlocking then you will have to access the archives via the server (such as by FTP).', 'it-l10n-backupbuddy' ),
	'css'		=>		'',
	'after'		=>		'<span class="description"> ' . __('Check for enhanced security to block backup downloading.', 'it-l10n-backupbuddy' ) . ' This may<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;result in an inability to download backups while enabled on some servers.</span>',
	'rules'		=>		'required',
) );
$settings_form->add_setting( array(
	'type'		=>		'checkbox',
	'name'		=>		'delete_archives_pre_backup',
	'options'	=>		array( 'unchecked' => '0', 'checked' => '1' ),
	'title'		=>		__( 'Delete all backup archives prior to backups', 'it-l10n-backupbuddy' ),
	'tip'		=>		__( '[Default: disabled] - When enabled all local backup archives will be deleted prior to each backup. This is useful if in compatibilty mode to prevent backing up existing files.', 'it-l10n-backupbuddy' ),
	'css'		=>		'',
	'after'		=>		'<span class="description"> ' . __('Check if using compatibilty mode & exclusions are unavailable.', 'it-l10n-backupbuddy' ) . '</span>',
	'rules'		=>		'required',
) );
$settings_form->add_setting( array(
	'type'		=>		'checkbox',
	'name'		=>		'include_importbuddy',
	'options'	=>		array( 'unchecked' => '0', 'checked' => '1' ),
	'title'		=>		__('Include ImportBuddy in full backup archive', 'it-l10n-backupbuddy' ),
	'tip'		=>		__('[Default: enabled] - When enabled, the importbuddy.php file will be included within the backup archive ZIP file.  This file can be used to restore your site.  Inclusion in the ZIP file itself insures you always have access to it. importbuddy.php is only included in full backups and only when this option is enabled.', 'it-l10n-backupbuddy' ),
	'css'		=>		'',
	'after'		=>		'<span class="description"> ' . __('Uncheck to skip adding ImportBuddy to backup archive.', 'it-l10n-backupbuddy' ) . '</span>',
	'rules'		=>		'required',
) );
$settings_form->add_setting( array(
	'type'		=>		'checkbox',
	'name'		=>		'disable_https_local_ssl_verify',
	'options'	=>		array( 'unchecked' => '0', 'checked' => '1' ),
	'title'		=>		__( 'Disable local SSL certificate verification', 'it-l10n-backupbuddy' ),
	'tip'		=>		__( '[Default: Disabled] When checked, WordPress will skip local https SSL verification.', 'it-l10n-backupbuddy' ) . '</span>',
	'css'		=>		'',
	'after'		=>		'<span class="description"> Check if local SSL verification fails (ie. for loopbacks).</span>',
	'rules'		=>		'required',
) );
$settings_form->add_setting( array(
	'type'		=>		'checkbox',
	'name'		=>		'save_comment_meta',
	'options'	=>		array( 'unchecked' => '0', 'checked' => '1' ),
	'title'		=>		__( 'Save meta data in comment', 'it-l10n-backupbuddy' ),
	'tip'		=>		__( '[Default: Enabled] When enabled, BackupBuddy will store general backup information in the ZIP comment header such as Site URL, backup type & time, serial, etc. during backup creation.', 'it-l10n-backupbuddy' ) . '</span>',
	'css'		=>		'',
	'after'		=>		'<span class="description"> Uncheck to skip storing meta data in zip comment.</span>',
	'rules'		=>		'required',
) );
$settings_form->add_setting( array(
	'type'		=>		'checkbox',
	'name'		=>		'profiles#0#integrity_check',
	'options'	=>		array( 'unchecked' => '0', 'checked' => '1' ),
	'title'		=>		__('Perform integrity check on backup files', 'it-l10n-backupbuddy' ),
	'tip'		=>		__('[Default: enabled] - By default each backup file is checked for integrity and completion the first time it is viewed on the Backup page.  On some server configurations this may cause memory problems as the integrity checking process is intensive.  If you are experiencing out of memory errors on the Backup file listing, you can uncheck this to disable this feature.', 'it-l10n-backupbuddy' ),
	'css'		=>		'',
	'after'		=>		'<span class="description"> ' . __( 'Uncheck if having problems viewing your backup listing.', 'it-l10n-backupbuddy' ) . '</span>',
	'rules'		=>		'required',
) );
$settings_form->add_setting( array(
	'type'		=>		'select',
	'name'		=>		'backup_mode',
	'title'		=>		__('Manual backup mode', 'it-l10n-backupbuddy' ),
	'options'	=>		array(
								'1'		=>		__( 'Classic (v1.x)', 'it-l10n-backupbuddy' ),
								'2'		=>		__( 'Modern (v2.x)', 'it-l10n-backupbuddy' ),
							),
	'tip'		=>		__('[Default: Modern] - If you are encountering difficulty backing up due to WordPress cron, HTTP Loopbacks, or other features specific to version 2.x you can try classic mode which runs like BackupBuddy v1.x did.', 'it-l10n-backupbuddy' ),
	'rules'		=>		'required',
) );

$log_file = WP_CONTENT_DIR . '/uploads/pb_' . pb_backupbuddy::settings( 'slug' ) . '/log-' . pb_backupbuddy::$options['log_serial'] . '.txt';
$settings_form->add_setting( array(
	'type'		=>		'select',
	'name'		=>		'log_level',
	'title'		=>		__('Logging / Debug level', 'it-l10n-backupbuddy' ),
	'options'	=>		array(
								'0'		=>		__( 'None', 'it-l10n-backupbuddy' ),
								'1'		=>		__( 'Errors Only', 'it-l10n-backupbuddy' ),
								'2'		=>		__( 'Errors & Warnings', 'it-l10n-backupbuddy' ),
								'3'		=>		__( 'Everything (debug mode)', 'it-l10n-backupbuddy' ),
							),
	'tip'		=>		sprintf( __('[Default: Errors Only] - This option controls how much activity is logged for records or debugging. When in debug mode error emails will contain encrypted debugging data for support. Log file: %s', 'it-l10n-backupbuddy' ), $log_file ),
	'rules'		=>		'required',
) );

$settings_form->add_setting( array(
	'type'		=>		'text',
	'name'		=>		'max_site_log_size',
	'title'		=>		__('Maximum log file size', 'it-l10n-backupbuddy' ),
	'tip'		=>		__('[Default: 10 MB] - If the log file exceeds this size then it will be cleared to prevent it from using too much space.' ),
	'rules'		=>		'required',
	'css'		=>		'width: 50px;',
	'after'		=>		' MB',
) );



$settings_form->add_setting( array(
	'type'		=>		'title',
	'name'		=>		'title_database',
	'title'		=>		__( 'Database', 'it-l10n-backupbuddy' ),
) );
$settings_form->add_setting( array(
	'type'		=>		'checkbox',
	'name'		=>		'profiles#0#skip_database_dump',
	'options'	=>		array( 'unchecked' => '0', 'checked' => '1' ),
	'title'		=>		__('Skip database dump on backup', 'it-l10n-backupbuddy' ),
	'tip'		=>		__('[Default: disabled] - (WARNING: This prevents BackupBuddy from backing up the database during any kind of backup. This is for troubleshooting / advanced usage only to work around being unable to backup the database.', 'it-l10n-backupbuddy' ),
	'css'		=>		'',
	'after'		=>		'<span class="description"> ' . __('Check if to completely bypass backing up database. Use with caution.', 'it-l10n-backupbuddy' ) . '</span>',
	'rules'		=>		'required',
) );
$settings_form->add_setting( array(
	'type'		=>		'checkbox',
	'name'		=>		'breakout_tables',
	'options'	=>		array( 'unchecked' => '0', 'checked' => '1' ),
	'title'		=>		__( 'Break out big table dumps into steps', 'it-l10n-backupbuddy' ),
	'tip'		=>		__( '[Default: Disabled] Currently in beta. Breaks up some commonly known database tables to be backed up separately rather than all at once. Helps with larger databases.', 'it-l10n-backupbuddy' ) . '</span>',
	'css'		=>		'',
	'after'		=>		'<span class="description"> Check to backup large tables in separate steps to help handle large databases.</span>',
	'rules'		=>		'required',
) );
$settings_form->add_setting( array(
	'type'		=>		'checkbox',
	'name'		=>		'force_mysqldump_compatibility',
	'options'	=>		array( 'unchecked' => '0', 'checked' => '1' ),
	'title'		=>		__('Force compatibility mode database dump', 'it-l10n-backupbuddy' ),
	'tip'		=>		__('[Default: disabled] - WARNING: This forces the potentially slower mode of database dumping. Under normal circumstances mysql dump compatibility mode is automatically entered as needed without user intervention.', 'it-l10n-backupbuddy' ),
	'css'		=>		'',
	'after'		=>		'<span class="description"> ' . __( 'Check to force PHP-based database dump instead of command line. Pre-v3.x mode.', 'it-l10n-backupbuddy' ) . '</span>',
	'rules'		=>		'required',
) );
$settings_form->add_setting( array(
	'type'		=>		'checkbox',
	'name'		=>		'ignore_command_length_check',
	'options'	=>		array( 'unchecked' => '0', 'checked' => '1' ),
	'title'		=>		__('Ignore command line length check results', 'it-l10n-backupbuddy' ),
	'tip'		=>		__('[Default: disabled] - WARNING: BackupBuddy attempts to determine your system\'s maximum command line length to insure that database operation commands do not get inadvertantly cut off. On some systems it is not possible to reliably detect this information which could result infalling back into compatibility mode even though the system is capable of running in normal operational modes. This option instructs BackupBuddy to ignore the results of the command line length check.', 'it-l10n-backupbuddy' ),
	'css'		=>		'',
	'after'		=>		'<span class="description"> ' . __( 'Check if directed by support.', 'it-l10n-backupbuddy' ) . '</span>',
	'rules'		=>		'required',
) );



$settings_form->add_setting( array(
	'type'		=>		'title',
	'name'		=>		'title_zip',
	'title'		=>		__( 'Zip', 'it-l10n-backupbuddy' ),
) );
$settings_form->add_setting( array(
	'type'		=>		'checkbox',
	'name'		=>		'compression', //'profiles#0#compression',
	'options'	=>		array( 'unchecked' => '0', 'checked' => '1' ),
	'title'		=>		__( 'Enable zip compression', 'it-l10n-backupbuddy' ),
	'tip'		=>		__( '[Default: enabled] - ZIP compression decreases file sizes of stored backups. If you are encountering timeouts due to the script running too long, disabling compression may allow the process to complete faster.', 'it-l10n-backupbuddy' ),
	'css'		=>		'',
	'after'		=>		'<span class="description"> ' . __('Uncheck for large sites causing backups to not complete.', 'it-l10n-backupbuddy' ) . '</span>',
	'rules'		=>		'required',
) );
$settings_form->add_setting( array(
	'type'		=>		'select',
	'name'		=>		'zip_method_strategy',
	'title'		=>		__('Zip method strategy', 'it-l10n-backupbuddy' ),
	'options'	=>		array(
								'1'		=>		__( 'Best Available', 'it-l10n-backupbuddy' ),
								'2'		=>		__( 'All Available', 'it-l10n-backupbuddy' ),
								'3'		=>		__( 'Force Compatibility', 'it-l10n-backupbuddy' ),
							),
	'tip'		=>		__('[Default: Best Only] - Normally use Best Available but if the server is unreliable in this mode can try All Available or Force Compatibility', 'it-l10n-backupbuddy' ),
	'after'		=>		'<span class="description"> ' . __('Select Force Compatibility if absolutely necessary.', 'it-l10n-backupbuddy' ) . '</span>',
	'rules'		=>		'required',
) );
$settings_form->add_setting( array(
	'type'		=>		'checkbox',
	'name'		=>		'alternative_zip_2',
	'options'	=>		array( 'unchecked' => '0', 'checked' => '1' ),
	'title'		=>		__( 'Alternative zip system (BETA)', 'it-l10n-backupbuddy' ),
	'tip'		=>		__( '[Default: Disabled] Use if directed by support.', 'it-l10n-backupbuddy' ) . '</span>',
	'css'		=>		'',
	'after'		=>		'<span class="description"> Check if directed by support.</span>',
	'rules'		=>		'required',
) );
$settings_form->add_setting( array(
	'type'		=>		'checkbox',
	'name'		=>		'disable_zipmethod_caching',
	'options'	=>		array( 'unchecked' => '0', 'checked' => '1' ),
	'title'		=>		__( 'Disable zip method caching', 'it-l10n-backupbuddy' ),
	'tip'		=>		__( '[Default: Disabled] Use if directed by support. Bypasses caching available zip methods so they are always displayed in logs. When unchecked BackupBuddy will cache command line zip testing for a few minutes so it does not run too often. This means that your backup status log may not always show the test results unless you disable caching.', 'it-l10n-backupbuddy' ) . '</span>',
	'css'		=>		'',
	'after'		=>		'<span class="description"> Check if directed by support to always log zip detection.</span>',
	'rules'		=>		'required',
) );
$settings_form->add_setting( array(
	'type'		=>		'checkbox',
	'name'		=>		'ignore_zip_warnings',
	'options'	=>		array( 'unchecked' => '0', 'checked' => '1' ),
	'title'		=>		__( 'Ignore zip archive warnings', 'it-l10n-backupbuddy' ),
	'tip'		=>		__( '[Default: Disabled] When enabled BackupBuddy will ignore non-fatal warnings encountered during the backup process such as inability to read or access a file, symlink problems, etc. These non-fatal warnings will still be logged.', 'it-l10n-backupbuddy' ) . '</span>',
	'css'		=>		'',
	'after'		=>		'<span class="description"> Check to ignore non-fatal errors when zipping files.</span>',
	'rules'		=>		'required',
) );
$settings_form->add_setting( array(
	'type'		=>		'checkbox',
	'name'		=>		'ignore_zip_symlinks',
	'options'	=>		array( 'unchecked' => '0', 'checked' => '1' ),
	'title'		=>		__( 'Ignore/do-not-follow symbolic links', 'it-l10n-backupbuddy' ),
	'tip'		=>		__( '[Default: Enabled] When enabled BackupBuddy will ignore/not-follow symbolic links encountered during the backup process', 'it-l10n-backupbuddy' ) . '</span>',
	'css'		=>		'',
	'after'		=>		'<span class="description"> Check to ignore/not-follow symbolic links when zipping files.</span>',
	'rules'		=>		'required',
) );







$settings_form->process(); // Handles processing the submitted form (if applicable).
$settings_form->display_settings( 'Save Advanced Settings' );


?>


<br><br><br>
<span class="pb_label" style="font-size: 12px; margin-left: 10px; position: relative; top: -3px;">Tip</span> Some advanced options can be set on a per-profile basis on the <a href="?page=pb_backupbuddy_settings&tab=1">Backup Profiles</a> tab.
<br>
