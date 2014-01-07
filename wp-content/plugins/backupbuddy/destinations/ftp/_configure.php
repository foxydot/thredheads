<?php

$settings_form->add_setting( array(
	'type'		=>		'text',
	'name'		=>		'title',
	'title'		=>		__( 'Destination name', 'it-l10n-backupbuddy' ),
	'tip'		=>		__( 'Name of the new destination to create. This is for your convenience only.', 'it-l10n-backupbuddy' ),
	'rules'		=>		'required|string[0-45]',
	'default'	=>		'My FTP',
) );

$settings_form->add_setting( array(
	'type'		=>		'text',
	'name'		=>		'address',
	'title'		=>		__( 'Server address', 'it-l10n-backupbuddy' ),
	'tip'		=>		__( '[Example: ftp.foo.com] - FTP server address.  Do not include http:// or ftp:// or any other prefixes. You may specify an alternate port in the format of ftp_address:ip_address such as yourftp.com:21', 'it-l10n-backupbuddy' ),
	'rules'		=>		'required|string[0-45]',
) );

$settings_form->add_setting( array(
	'type'		=>		'text',
	'name'		=>		'username',
	'title'		=>		__( 'Username', 'it-l10n-backupbuddy' ),
	'tip'		=>		__( '[Example: foo] - Username to use when connecting to the FTP server.', 'it-l10n-backupbuddy' ),
	'rules'		=>		'required|string[0-100]',
) );

$settings_form->add_setting( array(
	'type'		=>		'password',
	'name'		=>		'password',
	'title'		=>		__( 'Password', 'it-l10n-backupbuddy' ),
	'tip'		=>		__( '[Example: 1234xyz] - Password to use when connecting to the FTP server.', 'it-l10n-backupbuddy' ),
	'rules'		=>		'required|string[0-100]',
) );

$settings_form->add_setting( array(
	'type'		=>		'text',
	'name'		=>		'path',
	'title'		=>		__( 'Remote path (optional)', 'it-l10n-backupbuddy' ),
	'tip'		=>		__( '[Example: /public_html/backups] - Remote path to place uploaded files into on the destination FTP server. Make sure this path is correct; if it does not exist BackupBuddy will attempt to create it. No trailing slash is needed.', 'it-l10n-backupbuddy' ),
	'rules'		=>		'string[0-500]',
) );




if ( pb_backupbuddy::_GET('add') != '' ) { // set default only when adding.
	$default_url = rtrim( site_url(), '/\\' ) . '/';
} else {
	$default_url = '';
}

$settings_form->add_setting( array(
	'type'		=>		'text',
	'name'		=>		'url',
	'title'		=>		__( 'Migration URL', 'it-l10n-backupbuddy' ) . '<br><span class="description">Optional, for migrations</span>',
	'tip'		=>		__( 'Enter the URL corresponding to the FTP destination path. This URL must lead to the location where files uploaded to this remote destination would end up. If the destination is in a subdirectory make sure to match it in the corresponding URL.', 'it-l10n-backupbuddy' ),
	'css'		=>		'width: 100%;',
	'default'	=>		$default_url,
	'rules'		=>		'string[0-100]',
) );





$settings_form->add_setting( array(
	'type'		=>		'text',
	'name'		=>		'archive_limit',
	'title'		=>		__( 'Archive limit', 'it-l10n-backupbuddy' ),
	'tip'		=>		__( '[Example: 5] - Enter 0 for no limit. This is the maximum number of archives to be stored in this specific destination. If this limit is met the oldest backups will be deleted.', 'it-l10n-backupbuddy' ),
	'rules'		=>		'required|int[0-9999999]',
	'css'		=>		'width: 50px;',
	'after'		=>		' backups',
) );

$settings_form->add_setting( array(
	'type'		=>		'select',
	'name'		=>		'active_mode',
	'title'		=>		__( 'Transfer mode', 'it-l10n-backupbuddy' ),
	'options'	=>		array(
								'1'		=>		__( 'Active (default)', 'it-l10n-backupbuddy' ),
								'0'		=>		__( 'Passive', 'it-l10n-backupbuddy' ),
							),
	'tip'		=>		__('[Default: Active] - Determines whether the FTP file transfer happens in FTP active or passive mode.  Some servers or those behind a firewall may need to use PASV, or passive mode as a workaround.', 'it-l10n-backupbuddy' ),
	'rules'		=>		'required',
) );

$settings_form->add_setting( array(
	'type'		=>		'checkbox',
	'name'		=>		'ftps',
	'options'	=>		array( 'unchecked' => '0', 'checked' => '1' ),
	'title'		=>		__( 'Use FTPs encryption', 'it-l10n-backupbuddy' ),
	'tip'		=>		__( '[Default: disabled] - Select whether this connection is for FTP or FTPs (enabled; FTP over SSL). Note that FTPs is NOT the same as sFTP (FTP over SSH) and is not compatible or equal.', 'it-l10n-backupbuddy' ),
	'css'		=>		'',
	'after'		=>		'<span class="description"> ' . __( 'Enable high security mode (not supported by most servers)', 'it-l10n-backupbuddy' ) . '</span>',
	'rules'		=>		'required',
) );
