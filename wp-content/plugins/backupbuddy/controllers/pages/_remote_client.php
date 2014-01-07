<?php
	if ( isset( pb_backupbuddy::$options['remote_destinations'][$_GET['destination_id']] ) ) {
		$destination = &pb_backupbuddy::$options['remote_destinations'][$_GET['destination_id']];
	} else {
		echo __('Error #438934894349. Invalid destination ID.', 'it-l10n-backupbuddy' );
		return;
	}
	
	
	$manage_file = pb_backupbuddy::plugin_path() . '/destinations/' . $destination['type'] . '/_manage.php';
	if ( file_exists( $manage_file ) ) {
		require( $manage_file );
	} else {
		_e( 'A remote destination client is not available for this destination. Its files cannot be viewed & managed from within BackupBuddy.', 'it-l10n-backupbuddy' );
	}
	
	echo '<br><br><br>';
	echo '<a class="button" href="';
	if ( is_network_admin() ) {
		echo network_admin_url( 'admin.php' );
	} else {
		echo admin_url( 'admin.php' );
	}
	echo '?page=pb_backupbuddy_destinations">&larr; back to destinations</a><br><br>';
?>
