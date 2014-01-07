<?php // Settings to display in a form for a user to configure.
/*
	Pre-populated variables coming into this script:
		$destination_settings
		$mode
*/

global $pb_hide_test, $pb_hide_save;
$pb_hide_test = false;




$default_name = 'My S3';


// Form settings.
$settings_form->add_setting( array(
	'type'		=>		'text',
	'name'		=>		'title',
	'title'		=>		__( 'Destination name', 'it-l10n-backupbuddy' ),
	'tip'		=>		__( 'Name of the new destination to create. This is for your convenience only.', 'it-l10n-backupbuddy' ),
	'rules'		=>		'required|string[1-45]',
	'default'	=>		$default_name,
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

$settings_form->add_setting( array(
	'type'		=>		'text',
	'name'		=>		'db_archive_limit',
	'title'		=>		__( 'Database backup limit', 'it-l10n-backupbuddy' ),
	'tip'		=>		__( '[Example: 5] - Enter 0 for no limit. This is the maximum number of database backup archives to be stored in this specific destination. If this limit is met the oldest backup of this type will be deleted.', 'it-l10n-backupbuddy' ),
	'rules'		=>		'required|int[0-9999999]',
	'css'		=>		'width: 50px;',
	'after'		=>		' backups',
) );
$settings_form->add_setting( array(
	'type'		=>		'text',
	'name'		=>		'full_archive_limit',
	'title'		=>		__( 'Full backup limit', 'it-l10n-backupbuddy' ),
	'tip'		=>		__( '[Example: 5] - Enter 0 for no limit. This is the maximum number of full backup archives to be stored in this specific destination. If this limit is met the oldest backup of this type will be deleted.', 'it-l10n-backupbuddy' ),
	'rules'		=>		'required|int[0-9999999]',
	'css'		=>		'width: 50px;',
	'after'		=>		' backups',
) );
/*
TODO: Un-aborted failed multipart uploads charge the user so we must take extra care with chunking to S3.
$settings_form->add_setting( array(
	'type'		=>		'text',
	'name'		=>		'max_chunk_size',
	'title'		=>		__( 'Max chunk size', 'it-l10n-backupbuddy' ),
	'tip'		=>		__( '[Example: 5] - Enter 0 for no chunking; minimum of 5 if enabling. This is the maximum file size to send in one whole piece. Files larger than this will be transferred in pieces up to this file size one part at a time. This allows to transfer of larger files than you server may allow by breaking up the send process. Chunked files may be delayed if there is little site traffic to trigger them.', 'it-l10n-backupbuddy' ),
	'rules'		=>		'required|int[0-9999999]',
	'css'		=>		'width: 50px;',
	'after'		=>		' MB (leave at 0 if unsure)',
) );
*/
$settings_form->add_setting( array(
	'type'		=>		'checkbox',
	'name'		=>		'server_encryption',
	'options'	=>		array( 'unchecked' => '', 'checked' => 'AES256' ),
	'title'		=>		__( 'Encrypt files on S3 server', 'it-l10n-backupbuddy' ),
	'tip'		=>		__( '[Default: disabled] - When enabled, Amazon S3 will encrypt your files using AES256 encryption after they have fully arrived in the Amazon S3 cloud.', 'it-l10n-backupbuddy' ),
	'css'		=>		'',
	'after'		=>		'<span class="description"> ' . __('Enable S3-side AES encryption after send', 'it-l10n-backupbuddy' ) . '</span>',
	'rules'		=>		'',
) );
$settings_form->add_setting( array(
	'type'		=>		'checkbox',
	'name'		=>		'ssl',
	'options'	=>		array( 'unchecked' => '0', 'checked' => '1' ),
	'title'		=>		__( 'Encrypt connection', 'it-l10n-backupbuddy' ),
	'tip'		=>		__( '[Default: enabled] - When enabled, all transfers will be encrypted with SSL encryption. Disabling this may aid in connection troubles but results in lessened security. Note: Once your files arrive on our server they are encrypted using AES256 encryption. They are automatically decrypted upon download as needed.', 'it-l10n-backupbuddy' ),
	'css'		=>		'',
	'after'		=>		'<span class="description"> ' . __('Enable connecting over SSL', 'it-l10n-backupbuddy' ) . '</span>',
	'rules'		=>		'',
) );