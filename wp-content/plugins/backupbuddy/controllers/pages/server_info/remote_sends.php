<?php

pb_backupbuddy::$classes['core']->trim_remote_send_stats();

$remote_sends = array_reverse( pb_backupbuddy::$options['remote_sends'] ); // Reverse array so most recent is first.

$sends = array();
//echo '<pre>' . print_r( $remote_sends, true ) . '</pre>';
foreach( $remote_sends as $remote_send ) {
	
	// Set up some variables based on whether file finished sending yet or not.
	if ( $remote_send['finish_time'] > 0 ) { // Finished sending.
		$time_ago = pb_backupbuddy::$format->time_ago( $remote_send['finish_time'] ) . ' ago; <b>took ';
		$duration = pb_backupbuddy::$format->time_duration( $remote_send['finish_time'] - $remote_send['start_time'] ) . '</b>';
		$finish_time = pb_backupbuddy::$format->date( pb_backupbuddy::$format->localize_time( $remote_send['finish_time'] ) );
	} else { // Did not finish (yet?).
		$time_ago = pb_backupbuddy::$format->time_ago( $remote_send['start_time'] ) . ' ago; <b>unfinished</b>';
		$duration = '';
		$finish_time = '<span class="description">Unknown</span>';
	}
	
	// Handle showing sent ImportBuddy (if sent).
	if ( isset( $remote_send['send_importbuddy'] ) && ( $remote_send['send_importbuddy'] === true ) ) {
		$send_importbuddy = '<br><span class="description" style="margin-left: 10px;">+ importbuddy.php</span>';
	} else {
		$send_importbuddy = '';
	}
	
	// Show file size (if available).
	if ( isset( $remote_send['file_size'] ) ) {
		$file_size = '<br><span class="description" style="margin-left: 10px;">Size: ' . pb_backupbuddy::$format->file_size( $remote_send['file_size'] ) . '</span>';
	} else {
		$file_size = '';
	}
	
	// Status verbage & styling based on send status.
	if ( $remote_send['status'] == 'success' ) {
		$status = '<span class="pb_label pb_label-success">Success</span>';
	} elseif ( $remote_send['status'] == 'timeout' ) {
		$status = '<span class="pb_label pb_label-warning">In progress or timed out</span>';
	} elseif ( $remote_send['status'] == 'multipart' ) {
		$status = '<span class="pb_label pb_label-info">Multipart transfer</span>';
		if ( isset( $remote_send['_multipart_status'] ) ) {
			$status .= '<br>' . $remote_send['_multipart_status'];
		}
	} else {
		$status = '<span class="pb_label pb_label-important">' . ucfirst( $remote_send['status'] ) . '</span>';
	}
	
	// Determine destination.
	if ( isset( pb_backupbuddy::$options['remote_destinations'][$remote_send['destination']] ) ) { // Valid destination.
		$destination = pb_backupbuddy::$options['remote_destinations'][$remote_send['destination']]['title'] . ' (' . pb_backupbuddy::$options['remote_destinations'][$remote_send['destination']]['type'] . ')';
	} else { // Invalid destination (been deleted since send?).
		$destination = '<span class="description">Unknown</span>';
	}
	
	// Push into array.
	$sends[] = array(
		basename( $remote_send['file'] ) . $file_size . $send_importbuddy,
		$destination,
		ucfirst( $remote_send['trigger'] ),
		'Start: ' . pb_backupbuddy::$format->date( pb_backupbuddy::$format->localize_time(  $remote_send['start_time'] ) ) . '<br>' .
		'Finish: ' . $finish_time . '<br>' .
		'<span class="description">' . $time_ago  . $duration . '</span>',
		$status,
	);
} // End foreach.


if ( count( $sends ) == 0 ) {
	echo '<br>' . __( 'There have been no recent file transfers.', 'it-l10n-backupbuddy' ) . '<br>';
} else {
	pb_backupbuddy::$ui->list_table(
		$sends,
		array(
			'action'		=>	pb_backupbuddy::page_url(),
			'columns'		=>	array(
				__( 'Backup File', 'it-l10n-backupbuddy' ),
				__( 'Destination', 'it-l10n-backupbuddy' ),
				__( 'Trigger', 'it-l10n-backupbuddy' ),
				__( 'Transfer Time', 'it-l10n-backupbuddy' ) . ' <img src="' . pb_backupbuddy::plugin_url() . '/images/sort_down.png" style="vertical-align: 0px;" title="Sorted most recent started first">',
				__( 'Status', 'it-l10n-backupbuddy' ),
				),
			'css'			=>		'width: 100%;',
		)
	);
}

?><br>