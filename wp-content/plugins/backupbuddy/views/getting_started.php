<?php
pb_backupbuddy::$classes['core']->versions_confirm();

pb_backupbuddy::load_script( 'jquery' );


$selected_tab_index = 0;
if ( ( pb_backupbuddy::$options['email_notify_error'] != '' ) && ( pb_backupbuddy::$options['importbuddy_pass_hash'] != '' ) ) {
	$selected_tab_index = 1;
}


echo '<div style="margin-top: 40px;">';
	pb_backupbuddy::disalert( 'getting_started_hover_tip', '<span class="pb_label">Tip</span> ' . sprintf(
		__('Throughout the plugin you may hover your mouse over question marks %1$s for tips or click play icons %2$s for video tutorials.', 'it-l10n-backupbuddy' ), 
		pb_backupbuddy::tip( __('This tip provides additional help.', 'it-l10n-backupbuddy' ), '', false ), //the flag false returns a string
		pb_backupbuddy::video( 'WQrOCvOYof4', __('Introduction to BackupBuddy', 'it-l10n-backupbuddy' ), false )
	) );
	
	//pb_backupbuddy::disalert( 'getting_started_tabs_tip', '<span class="pb_label">Tip</span> Select the from the tabs above for details & tutorials covering BackupBuddy\'s primary features.' );
	
	pb_backupbuddy::$ui->start_tabs(
		'getting_started',
		array(
			array(
				'title'		=>		__( 'Quick Setup', 'it-l10n-backupbuddy' ),
				'slug'		=>		'quicksetup',
				'css'		=>		'margin-top: -11px;',
			),
			array(
				'title'		=>		__( 'Overview', 'it-l10n-backupbuddy' ),
				'slug'		=>		'overview',
				'css'		=>		'margin-top: -11px;',
			),
			array(
				'title'		=>		__( 'Backup', 'it-l10n-backupbuddy' ),
				'slug'		=>		'backup',
				'css'		=>		'margin-top: -11px;',
			),
			array(
				'title'		=>		__( 'Migrate / Restore', 'it-l10n-backupbuddy' ),
				'slug'		=>		'restore_migrate',
				'css'		=>		'margin-top: -11px;',
			),
			/*
			array(
				'title'		=>		__( 'Troubleshoot', 'it-l10n-backupbuddy' ),
				'slug'		=>		'troubleshooting',
			),
			*/
		),
		'width: 100%;',
		true,
		$selected_tab_index
	);
	
	
	
	pb_backupbuddy::$ui->start_tab( 'quicksetup' );
	require_once( '_quicksetup.php' );
	pb_backupbuddy::$ui->end_tab();
	
	
	
	pb_backupbuddy::$ui->start_tab( 'overview' );
		
		//echo '<p><i>"' . pb_backupbuddy::settings( 'description' ) . '"</i></p>';
		
		/*
		echo '<p>';
		echo '<span class="pb_label">Tip</span> <b>Select the from the tabs above for details & tutorials covering BackupBuddy\'s primary features.</b>';
		echo '</p>';
		*/
		
		//echo '<p>';
		echo '<h2>Quick Start</h2>';
		//echo '</p>';
		?>
		<ol>
			<li type="disc">Receive error notifications by verifying your "Error Notification Email" on the <a href="?page=pb_backupbuddy_settings">Settings</a> page.</li>
			<li type="disc">Keep backups from piling up with "Local Archive Storage Limits" on the <a href="?page=pb_backupbuddy_settings">Settings</a> page.</li>
			<li type="disc">Keep your site backed up regularly by creating schedules on the <a href="?page=pb_backupbuddy_scheduling">Scheduling</a> page.</li>
			<li type="disc">Read the tabbed sections above or tutorials & videos linked in the "Tutorials & Support" box to the right for more in-depth information.</li>
		</ol>
		
		<h2>Main Features</h2><br>
		Select from the tabs above for information & <b>tutorials</b> about BackupBuddy's main features. The "Tutorials & Support" box to the upper right contains valuable resources for learning how to use BackupBuddy to its fullest potential as well as tips and troubleshooting information.
		<br><br>
		
		<h2>Additional Features</h2>
		
		<br><b style="font-size: 15px">Remote Destinations</b><br>
		Create and manage remote destinations where backups can be manually or automatically sent to by scheduled backups. Select an existing destination to view backups stored there, delete remote backups, or copy remote backups back to this server.
		<br><br>
		<span class="pb_label pb_label">1 GB Free Storage</span> Active BackupBuddy customers already have a <b>BackupBuddy Stash</b> account. Just login on the <a href="?page=pb_backupbuddy_destinations">Remote Destinations</a> page.
		<br>
		
		<br><b style="font-size: 15px">Server Information</b><br>
		View server settings, WordPress permissions, site size maps, database information, and more.
		<br>
		
		<br><b style="font-size: 15px">Malware Scan</b><br>
		Scan your website for malware to help keep your site safe and secure.
		<br>
		
		<br><b style="font-size: 15px">Scheduling</b><br>
		Schedule regular backups to occur automatically to keep your site backed up and safe. Scheduled backups can be automatically sent to any Remote Destinations that have been set up.
		<br>
		
		
		
		<?php
		echo '<br><br>';
		
		
		
		if ( stristr( PHP_OS, 'WIN' ) && !stristr( PHP_OS, 'DARWIN' ) ) { // Show in WINdows but not darWIN. ?>
			<?php pb_backupbuddy::$ui->start_metabox( __('Windows Server Performance Boost', 'it-l10n-backupbuddy' ), true, 'width: 70%;' ); ?>
				<?php
				_e('Windows servers may be able to significantly boost performance, if the server allows executing .exe files, by adding native Zip compatibility executable files <a href="http://ithemes.com/wp-content/uploads/2010/05/backupbuddy_windows_unzip.zip">available for download here</a>. Instructions are provided within the readme.txt in the package.  This package prevents Windows from falling back to Zip compatiblity mode and works for both BackupBuddy and importbuddy.php. This is particularly useful for <a href="http://ithemes.com/codex/page/BackupBuddy:_Local_Development">local development on a Windows machine using a system like XAMPP</a>.', 'it-l10n-backupbuddy' );
				?>
			<?php pb_backupbuddy::$ui->end_metabox(); ?>
		<?php } ?>
		
		<?php pb_backupbuddy::$ui->start_metabox( __('Logging & Debugging', 'it-l10n-backupbuddy' ), true, 'width: 100%;' ); ?>
			<?php
			if ( pb_backupbuddy::_GET( 'cleanup_now' ) != '' ) {
				pb_backupbuddy::alert( 'Performing cleanup procedures now.' );
				pb_backupbuddy::$classes['core']->periodic_cleanup();
			}
			
			$log_file = WP_CONTENT_DIR . '/uploads/pb_' . self::settings( 'slug' ) . '/log-' . self::$options['log_serial'] . '.txt';
			
			// Reset log.
			if ( pb_backupbuddy::_GET( 'reset_log' ) != '' ) {
				if ( file_exists( $log_file ) ) {
					@unlink( $log_file );
				}
				if ( file_exists( $log_file ) ) { // Didnt unlink.
					pb_backupbuddy::alert( 'Unable to clear log file. Please verify permissions on file `' . $log_file . '`.' );
				} else { // Unlinked.
					pb_backupbuddy::alert( 'Cleared log file.' );
				}
			}
			
			// Reset disalerts.
			if ( pb_backupbuddy::_GET( 'reset_disalerts' ) != '' ) {
				pb_backupbuddy::$options['disalerts'] = array();
				pb_backupbuddy::save();
				
				pb_backupbuddy::alert( 'Dismissed alerts have been reset. They may now be visible again.' );
			}
			
			echo '<textarea readonly="readonly" style="width: 100%;" wrap="off" cols="65" rows="7">';
			if ( file_exists( $log_file ) ) {
				readfile( $log_file );
			} else {
				echo __('Nothing has been logged.', 'it-l10n-backupbuddy' );
			}
			echo '</textarea>';
			echo '<a href="' . pb_backupbuddy::page_url() . '&reset_log=true" class="button secondary-button">' . __('Clear Log', 'it-l10n-backupbuddy' ) . '</a>';
		pb_backupbuddy::$ui->end_metabox();
		
		
		plugin_information( pb_backupbuddy::settings( 'slug' ), array( 'name' => pb_backupbuddy::settings( 'name' ), 'path' => pb_backupbuddy::plugin_path() ) );
		?>
		
		
		<br style="clear: both;">

		<div style="float: left;">
			<a href="<?php echo pb_backupbuddy::page_url(); ?>&cleanup_now=true" class="button secondary-button"><?php _e('Cleanup Temporary Files', 'it-l10n-backupbuddy' );?></a>
			&nbsp;
			<a href="<?php echo pb_backupbuddy::page_url(); ?>&reset_disalerts=true" class="button secondary-button"><?php _e('Reset Dismissed Alerts (' . count( pb_backupbuddy::$options['disalerts'] ) . ')', 'it-l10n-backupbuddy' );?></a>
			&nbsp;
			<a id="pluginbuddy_debugtoggle" class="button secondary-button">Debugging Settings Data</a>
			&nbsp;
		</div>
		<br style="clear: both;">
		<div id="pluginbuddy_debugtoggle_div" style="display: none;">
			<?php pb_backupbuddy::$ui->start_metabox( __('Raw Settings (debugging)', 'it-l10n-backupbuddy' ), true, 'width: 100%;' ); ?>
			<h4><?php _e('Raw Settings for Debugging', 'it-l10n-backupbuddy');?></h4>
			<?php
			$temp_options = pb_backupbuddy::$options;
			$temp_options['importbuddy_pass_hash'] = '*hidden*';
			$temp_options['repairbuddy_pass_hash'] = '*hidden*';
			echo '<textarea rows="7" cols="65" style="width: 100%;" wrap="off" readonly="readonly">';
			echo 'Plugin Version = '.pb_backupbuddy::settings('name').' '.pb_backupbuddy::settings('version').' ('.pb_backupbuddy::settings('slug').')'."\n";
			echo 'WordPress Version = '.get_bloginfo("version")."\n";
			echo 'PHP Version = '.phpversion()."\n";
			global $wpdb;
			echo 'DB Version = '.$wpdb->db_version()."\n";
			echo "\n".print_r($temp_options);
			echo '</textarea>';
			pb_backupbuddy::$ui->end_metabox();
			?>
		</div>
		
		<br><br><br>
		
		
		<?php
	pb_backupbuddy::$ui->end_tab();
	
	
	
	pb_backupbuddy::$ui->start_tab( 'backup' );
		if ( is_network_admin() ) {
			$backup_page_url = network_admin_url( 'admin.php' );
		} else {
			$backup_page_url = admin_url( 'admin.php' );
		}
		$backup_page_url .= '?page=pb_backupbuddy_backup';
		?>
		
		
		
		<h2>Database Backup</h2><br>
		Backup the database regularly by clicking "Database Backup" button on the <a href="<?php echo $backup_page_url;?>">Backup</a> page or
		by creating a Database-only scheduled backup.
		The database contains posts, pages, comments widget content, media titles & descriptions (but not media files), and other WordPress settings.
		It may be backed up more often without impacting your available storage space or server performance as much as a Full Backup.
		
		<br><br>
		<span class="pb_label">Tip</span> Determine which database tables are backed up by default plus additional inclusions & exclusions on the <a href="?page=pb_backupbuddy_settings">Settings</a> page.
		<br><br><br>
		
		<h2>Full Backup</h2><br>
		Select the "Full Backup" button on the <a href="<?php echo $backup_page_url; ?>">Backup</a> page or by creating a Full scheduled backup.
		This backs up all files in your WordPress installation directory (and subdirectories) as well as the database.
		This will capture everything from the Database Only Backup and also all files in the WordPress directory and subdirectories.
		This includes files such as media, plugins, themes, images, and any other files found.
		
		<br><br>
		<span class="pb_label">Tip</span> Exclude unneeded directories & files from your Full Backup by setting up exclusions on the <a href="?page=pb_backupbuddy_settings">Settings</a> page.
		
		
		<br><br><br><br>
		<?php _e('Local backup storage directory', 'it-l10n-backupbuddy' );?>: <span style="background-color: #EEEEEE; padding: 4px;"><i><?php echo str_replace( '\\', '/', pb_backupbuddy::$options['backup_directory'] ); ?></i></span> <?php pb_backupbuddy::tip(' ' . __('This is the local directory that backups are stored in. Backup files include random characters in their name for increased security. BackupBuddy must be able to create this directory & write to it.', 'it-l10n-backupbuddy' ) ); ?>
		<br>
		
		
		<?php
	pb_backupbuddy::$ui->end_tab();



	pb_backupbuddy::$ui->start_tab( 'restore_migrate' );
		
		echo '<br>';
		if ( is_network_admin() ) {
			$migrate_page_url = network_admin_url( 'admin.php' );
		} else {
			$migrate_page_url = admin_url( 'admin.php' );
		}
		$migrate_page_url .= '?page=pb_backupbuddy_migrate_restore';
		?>
		
		You may <b>restore sites or database only backups to the same server</b> or <b>migrate to a new server / URL</b>.
		The importbuddy.php tool will handle extracting all backed up files, setting up WordPress,
		and migrating the URL and paths if you are migrating to a new server and/or URL. You can
		manually upload the files to the destination server, use BackupBuddy to send them there as a
		remote destination, or use automated migration to handle sending these files and loading
		importbuddy.php for you. Below are instructions for the most common method, manual migration.
		Note: If restoring / migrating a database only backup then the full site must already exist such as having been restored
		from a full backup prior.
		<br>
		<br>
		
		
		<h2>What You Need</h2>
		<ol>
			<li>The backup ZIP file you want to restore / migrate.</li>
			<br>
			<li>The importbuddy.php tool (download on the <a href="<?php echo $migrate_page_url; ?>">Migrate, Restore</a> page).</li>
		</ol>
		<br>
		
		
		<h2>Procedure</h2>
		<br>
		
		<ol>
			<li>
				Upload the <b>backup ZIP file</b> and <b>importbuddy.php tool</b> (download on the <a href="<?php echo $migrate_page_url; ?>">Restore / Migrate</a> page)
				to the directory where you would like your WordPress site to be installed on the destination server.
				<span class="pb_label pb_label-important">Important</span> You should not install WordPress first before restoring/migrating. If you did, you should clear it out before continuing.
			</li>
			<br>
			<li>
				Navigate to the ImportBuddy.php tool in your web browser on the destination server. The URL will look something like <span style="background-color: #EEEEEE; padding: 4px;"><i>http://yoursite.com/importbuddy.php</i></span>
				<span class="pb_label">Can't load importbuddy.php?</span> Verify you uploaded to the correct directory & the file has correct permissions. If you receive a 500 server error please contact your hosting provider as PHP configuration may be wrong.
			</li>
			<br>
			<li>
				You will be prompted for your ImportBuddy password you entered when downloading or on the Settings page. Enter it and click to continue.
			</li>
			<br>
			<li>
				Follow the on-screen instructions until the restore or migration is complete.
			</li>
		</ol>
		<?php
	pb_backupbuddy::$ui->end_tab();
	
	
	
	pb_backupbuddy::$ui->start_tab( 'troubleshooting' );
		?>
		<!--
		<br>
		Please see the links to the right in the "Help & Support" box.<br>
		-->
		<?php
	pb_backupbuddy::$ui->end_tab();
	
	
	
	pb_backupbuddy::$ui->end_tabs();
	?>
</div>





<script type="text/javascript">
	jQuery(document).ready(function() {
		jQuery("#pluginbuddy_debugtoggle").click(function() {
			jQuery("#pluginbuddy_debugtoggle_div").slideToggle();
		});
	});
</script>