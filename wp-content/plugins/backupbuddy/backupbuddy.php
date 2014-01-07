<?php
/**
 *
 *	Plugin Name: BackupBuddy
 *	Plugin URI: http://ithemes.com/purchase/backupbuddy/
 *	Description: The most complete WordPress solution for Backup, Restoration, and Migration. Backs up a customizable selection of files, settings, and content for the complete snapshot of your site. Restore and/or migrate your site to a new host or new domain with complete ease-of-mind.
 *	Version: 4.0.2.1
 *	Author: Dustin Bolton
 *	Author URI: http://dustinbolton.com/
 *	Author URL: http://pluginbuddy.com/
 *	
 *	Installation:
 * 
 *	1. Download and unzip the latest release zip file.
 *	2. If you use the WordPress plugin uploader to install this plugin skip to step 4.
 *	3. Upload the entire plugin directory to your `/wp-content/plugins/` directory.
 *	4. Activate the plugin through the 'Plugins' menu in WordPress Administration.
 * 
 *	Usage:
 * 
 *	1. Navigate to the new plugin menu in the Wordpress Administration Panel.
 *
 *	NOTE: DO NOT EDIT THIS OR ANY OTHER PLUGIN FILES. NO USER-CONFIGURABLE OPTIONS WITHIN.
 */



// Plugin defaults:
$pluginbuddy_settings = array(
				'slug'				=>		'backupbuddy',
				'series'			=>		'',
				'default_options'	=>		array(
												'data_version'						=>		'7',				// Data structure version. Added BB 2.0 to ease updating.												
												'importbuddy_pass_hash'				=>		'',					// ImportBuddy password hash.
												'importbuddy_pass_length'			=>		0,					// Length of the ImportBuddy password before it was hashed.
												
												'backup_reminders'					=>		1,					// Remind to backup after post, pre-upgrade, etc.
												//'dashboard_stats'					=>		1,					// Stats box in dashboard.
												'edits_since_last'					=>		0,					// Number of post/page edits since the last backup began.
												'last_backup_start'					=>		0,					// Timestamp of when last backup started.
												'last_backup_finish'				=>		0,					// Timestamp of when the last backup finished.
												'last_backup_serial'				=>		'',					// Serial of last backup zip.
												'force_compatibility'				=>		0,					// Force compatibility mode even if normal is detected.
												'force_mysqldump_compatibility'		=>		0,					// Force compatibility mode for mysql db dumping. Uses PHP-based rather than command line mysqldump.
												'schedules'							=>		array(),			// Array of scheduled schedules.
												'log_level'							=>		'1',				// Valid options: 0 = none, 1 = errors only, 2 = errors + warnings, 3 = debugging (all kinds of actions)
												'backup_reminders'					=>		1,					// Whether or not to show reminders to backup on post/page edits & on the WP upgrade page.
												'high_security'						=>		0,					// TODO: Future feature. Strip mysql password & admin user password. Prompt on import.
												'next_schedule_index'				=>		100,				// Next schedule index. Prevent any risk of hanging scheduled crons from having the same ID as a new schedule.
												'archive_limit'						=>		0,					// Maximum number of archives to storage. Deletes oldest if exceeded.
												'archive_limit_size'				=>		0,					// Maximum size of all archives to store. Deletes oldest if exeeded.
												'archive_limit_age'					=>		0,					// Maximum age (in days) backup files can be before being deleted. Any exceeding age deleted on backup.
												'delete_archives_pre_backup'		=>		0,					// Whether or not to delete all backups prior to backing up.
												'lock_archives_directory'			=>		0,					// Whether or not to lock archives directory via htaccess and lift lock temporarily for download.
												
												'email_notify_scheduled_start'             => '',					// Email address(es) to send to when a scheduled backup begins.
												'email_notify_scheduled_start_subject'     => 'BackupBuddy Scheduled Backup Started - {site_url}',
												'email_notify_scheduled_start_body'	       => "A scheduled backup has started with BackupBuddy v{backupbuddy_version} on {current_datetime} for the site {site_url}.\n\nDetails:\r\n\r\n{message}",
												'email_notify_scheduled_complete'          => '',					// Email address(es) to send to when a scheduled backup completes.
												'email_notify_scheduled_complete_subject'  => 'BackupBuddy Scheduled Backup Complete - {site_url}',
												'email_notify_scheduled_complete_body'     => "A scheduled backup has completed with BackupBuddy v{backupbuddy_version} on {current_datetime} for the site {site_url}.\n\nDetails:\r\n\r\n{message}",
												'email_notify_error'                       => '',					// Email address(es) to send to when an error is encountered.
												'email_notify_error_subject'               => 'BackupBuddy Error - {site_url}',
												'email_notify_error_body'                  => "An error occurred with BackupBuddy v{backupbuddy_version} on {current_datetime} for the site {site_url}. Error details:\r\n\r\n{message}",
												'email_return'								=> '',				// Return email address for emails sent. Defaults to admin email if none specified.
												
												'remote_sends'						=>		array(),			// Keep a record of several remote sends.
												'remote_destinations'				=>		array(),			// Array of remote destinations (S3, Rackspace, email, ftp, etc)
												'role_access'						=>		'activate_plugins',	// Default role access to the plugin.
												'dropboxtemptoken'					=>		'',					// Temporary Dropbox token for oauth.
												'backup_mode'						=>		'2',				// 1 = 1.x, 2 = 2.x mode
												'multisite_export'					=>		'0',				// Allow individual sites to be exported by admins of said subsite? (Network Admins can always export individual sites).
												'backup_directory'					=>		'',					// Backup directory to store all archives in.
												'temp_directory'					=>		'',					// Temporary directory to use for writing into.
												'log_directory'						=>		'',					// Log directory. Also holds fileoptions.
												'log_serial'						=>		'',					// Current log serial to send all output to. Used during backups.
												'notifications'						=>		array(),			// TODO: currently not used.
												'zip_method_strategy'				=>		'0',				// 0 = Not Set, 1 = Best Available, 2 = All Available, 3 = Force Compatibility.
												'alternative_zip_2'					=>		'0',				// Alternative zip system (Jeremy).
												'ignore_zip_warnings'				=>		'0',				// Ignore non-fatal zip warnings during the zip process (ie symlink, cant read file, etc).
												'ignore_zip_symlinks'				=>		'1',				// When enabled (1) zip will not-follow (zip utility) or ignore (pclzip) any symbolic links
												'disable_zipmethod_caching'			=>		'0',				// When enabled the available zip methods are not cached. Useful for always showing the test for debugging or customer logging purposes for support.
												'archive_name_format'				=>		'date',				// Valid options: date, datetime
												'disable_https_local_ssl_verify'	=>		'0',				// When enabled (1) disabled WordPress from verifying SSL certificates for loopbacks, etc.
												'save_comment_meta'					=>		'1',				// When enabled (1) meta data will not be stored in backups during creation.
												'ignore_command_length_check'		=>		'0',				// When enabled, the command line length result provided by the OS will be ignored. Sometimes we cannot reliably get it.
												'stats'								=>		array(
																								'site_size'				=>		0,
																								'site_size_excluded'	=>		0,
																								'site_size_updated'		=>		0,
																								'db_size'				=>		0,
																								'db_size_excluded'		=>		0,
																								'db_size_updated'		=>		0,
																							),
												'disalerts'							=>		array(),			// Array of alerts that have been dismissed/hidden.
												'breakout_tables'					=>		'0',						// Whether or not to breakout some tables into individual steps (for sites with larger dbs).
												'include_importbuddy'				=>		'1',						// Whether or not to include importbuddy.php script inside backup ZIP file.
												'max_site_log_size'					=>		'10',						// Size in MB to clear the log file if it is exceeded.
												'compression'						=>		'1',						// Zip compression.
												'no_new_backups_error_days'			=>		'45',						// Send an error email notification if no new backups have been created in X number of days.
												'profiles'							=>		array(
																								0 => array(
																													'type'							=>		'defaults',
																													'title'							=>		'Global Defaults',
																													'skip_database_dump'			=>		'0',						// When enabled the database dump step will be skipped.
																													'backup_nonwp_tables'			=>		'0',						// Backup tables not prefixed with the WP prefix.
																													'integrity_check'				=>		'1',						// Zip file integrity check on the backup listing.
																													'mysqldump_additional_includes'	=>		'',
																													'mysqldump_additional_excludes'	=>		'',
																													'excludes'						=>		''
																												),
																								1 => array(
																													'type'		=>	'db',
																													'title'		=>	'Database Only',
																													'tip'		=>	'Just your database. I like your minimalist style.',
																												),
																								2	   => array(
																													'type'		=>	'full',
																													'title'		=>	'Complete Backup',
																												),
																							),
											),
				'profile_defaults'			=>	array(
													'type'							=>		'',						// defaults, db, or full
													'title'							=>		'',						// Friendly title/name.
													'skip_database_dump'			=>		'-1',					// When enabled the database dump step will be skipped.
													'mysqldump_additional_includes'	=>		'-1',					// Additional db tables to backup in addition to those calculated by mysql_dumpmode.
													'mysqldump_additional_excludes'	=>		'-1',					// Additional db tables to EXCLUDE. This is taken into account last, after tables are calculated by mysql_dumpmode AND additional includes calculated.
													'backup_nonwp_tables'			=>		'-1',					// Backup tables not prefixed with the WP prefix.
													//'compression'					=>		'-1',					// Zip compression.
													'excludes'						=>		'-1',					// Newline deliminated list of directories to exclude from the backup.
													'integrity_check'				=>		'-1',					// Zip file integrity check on the backup listing.
													'profile_globaltables'			=>		'1',					// Whether or not custom table inclusions/exclusions enabled for this profile.
													'profile_globalexcludes'		=>		'1',					// Whether or not custom file excludes enabled for this profile.
												),
				'migration_defaults'		=>	array(
													'web_address'			=>		'',
													'ftp_server'			=>		'',
													'ftp_username'			=>		'',
													'ftp_password'			=>		'',
													'ftp_path'				=>		'',
													'ftps'					=>		'0',
												),
				'backups_integrity_defaults'=>	array( // key is serial
													'is_ok'					=>		false,
													'tests'					=>		array(),
													'scan_time'				=>		0,
													'scan_log'				=>		array(),
													'size'					=>		0,
													'modified'				=>		0,
													'detected_type'			=>		'',
													'file'					=>		'',
												),
				'schedule_defaults'	=>		array(
												'title'						=>		'',
												'profile'					=>		'',
												'interval'					=>		'monthly',
												'first_run'					=>		'',
												'delete_after'				=>		'0',
												'remote_destinations'		=>		'',
												'last_run'					=>		0,
												'on_off'					=>		'1',
											),
				'wp_minimum'		=>		'3.3.0',
				'php_minimum'		=>		'5.2',
				
				'modules'			=>		array(
												'updater'		=>		true,
												'downsizer'		=>		false,
												'filesystem'	=>		true,
												'format'		=>		true,
											),
			);



// Main plugin file.
$pluginbuddy_init = 'backupbuddy.php';


// Load compatibility functions.
require_once( dirname( __FILE__ ) . '/_compat.php' );

// $settings is expected to be populated prior to including PluginBuddy framework. Do not edit below.
require( dirname( __FILE__ ) . '/pluginbuddy/_pluginbuddy.php' );
?>
