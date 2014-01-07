<?php // Settings to display in a form for a user to configure.

/*
Available incoming variables:
$mode
(there's another)
*/
//echo 'mode: ' . $mode;


$settings_form->add_setting( array(
	'type'		=>		'text',
	'name'		=>		'title',
	'title'		=>		__( 'Destination name', 'it-l10n-backupbuddy' ),
	'tip'		=>		__( 'Name of the new destination to create. This is for your convenience only.', 'it-l10n-backupbuddy' ),
	'rules'		=>		'required|string[1-45]',
	'destination' =>	'My S3',
) );

$settings_form->add_setting( array(
	'type'		=>		'text',
	'name'		=>		'accesskey',
	'title'		=>		__( 'AWS access key', 'it-l10n-backupbuddy' ),
	'tip'		=>		__( '[Example: BSEGHGSDEUOXSQOPGSBE] - Log in to your Amazon S3 AWS Account and navigate to Account: Access Credentials: Security Credentials.', 'it-l10n-backupbuddy' ),
	'rules'		=>		'required|string[1-45]',
) );


if ( $mode == 'add' ) { // text mode to show secret key during adding.
	$secretkey_type_mode = 'text';
} else { // pass field to hide secret key for editing.
	$secretkey_type_mode = 'password';
}
$settings_form->add_setting( array(
	'type'		=>		$secretkey_type_mode,
	'name'		=>		'secretkey',
	'title'		=>		__( 'AWS secret key', 'it-l10n-backupbuddy' ),
	'tip'		=>		__( '[Example: GHOIDDWE56SDSAZXMOPR] - Log in to your Amazon S3 AWS Account and navigate to Account: Access Credentials: Security Credentials.', 'it-l10n-backupbuddy' ),
	'after'		=>		' <a href="https://aws-portal.amazon.com/gp/aws/developer/account/index.html?ie=UTF8&action=access-key" target="_blank" title="' . __('Opens a new tab where you can get your Amazon S3 key', 'it-l10n-backupbuddy' ) . '"><small>' . __('Get Key', 'it-l10n-backupbuddy' ) . '</small></a>',
	'css'		=>		'width: 212px;',
	'rules'		=>		'required|string[1-45]',
) );


$settings_form->add_setting( array(
	'type'		=>		'text',
	'name'		=>		'bucket',
	'title'		=>		__( 'Bucket name', 'it-l10n-backupbuddy' ),
	'tip'		=>		__( '[Example: wordpress_backups] - This bucket will be created for you automatically if it does not already exist. Bucket names must be globally unique amongst all Amazon S3 users.', 'it-l10n-backupbuddy' ),
	'after'		=>		'',
	'css'		=>		'width: 255px;',
	'rules'		=>		'required|string[1-45]',
) );

$settings_form->add_setting( array(
	'type'		=>		'text',
	'name'		=>		'directory',
	'title'		=>		__( 'Directory (optional)', 'it-l10n-backupbuddy' ),
	'tip'		=>		__( '[Example: backupbuddy] - Directory name to place the backup within.', 'it-l10n-backupbuddy' ),
	'rules'		=>		'string[0-45]',
) );

/*
$settings_form->add_setting( array(
	'type'		=>		'select',
	'name'		=>		'storage_class',
	'title'		=>		__('Storage class', 'it-l10n-backupbuddy' ),
	'options'	=>		array(
								'STANDARD'				=>		__( 'Standard (default)', 'it-l10n-backupbuddy' ),
								'REDUCED_REDUNDANCY'	=>		__( 'Reduced Redundancy', 'it-l10n-backupbuddy' ),
							),
	'tip'		=>		__('[Default: Standard] - Amazon S3 currently offers two storage classes: standard (the default), and reduced redundancy.  Reduced redundancy storage typically costs less at the expense of less redundancy (the files ar replicated less in the cloud so are less protected from loss to some degree). See Amazon documentation for details.', 'it-l10n-backupbuddy' ),
	'rules'		=>		'required',
) );
*/

$settings_form->add_setting( array(
	'type'		=>		'text',
	'name'		=>		'archive_limit',
	'title'		=>		__( 'Archive limit', 'it-l10n-backupbuddy' ),
	'tip'		=>		__( '[Example: 5] - Enter 0 for no limit. This is the maximum number of archives to be stored in this specific destination. If this limit is met the oldest backups will be deleted.', 'it-l10n-backupbuddy' ),
	'rules'		=>		'required|int[0-9999999]',
	'css'		=>		'width: 50px;',
	'after'		=>		' backups',
) );

/*
TODO: disabled due to signature errors.

$settings_form->add_setting( array(
	'type'		=>		'checkbox',
	'name'		=>		'server_encryption',
	'options'	=>		array( 'unchecked' => '', 'checked' => 'AES256' ),
	'title'		=>		__( 'S3-side encryption', 'it-l10n-backupbuddy' ),
	'tip'		=>		__( '[Default: disabled] - When enabled, Amazon S3 will encrypt your files with AES256 once they are tranferred & reach the servers. They are automatically decrypted upon retrieval.', 'it-l10n-backupbuddy' ),
	'css'		=>		'',
	'after'		=>		'<span class="description"> ' . __('Enable server-side AES256 encryption', 'it-l10n-backupbuddy' ) . '</span>',
	'rules'		=>		'',
) );
*/

$settings_form->add_setting( array(
	'type'		=>		'checkbox',
	'name'		=>		'ssl',
	'options'	=>		array( 'unchecked' => '0', 'checked' => '1' ),
	'title'		=>		__( 'Encrypt connection', 'it-l10n-backupbuddy' ),
	'tip'		=>		__( '[Default: enabled] - When enabled, all transfers will be encrypted with SSL encryption. Please note that encryption introduces overhead and may slow down the transfer. If Amazon S3 sends are failing try disabling this feature to speed up the process.  Note that 32-bit servers cannot encrypt transfers of 2GB or larger with SSL, causing large file transfers to fail.', 'it-l10n-backupbuddy' ),
	'css'		=>		'',
	'after'		=>		'<span class="description"> ' . __('Enable connecting over SSL', 'it-l10n-backupbuddy' ) . '</span>',
	'rules'		=>		'',
) );
