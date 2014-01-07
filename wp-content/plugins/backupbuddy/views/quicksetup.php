<?php
if ( !current_user_can( 'activate_plugins' ) ) {
	die( 'Access Denied. Error 445543454754.' );
}
?>
<style type="text/css">
#wizard {
	margin: 60px 0 120px;
	border-radius: 5px;
	max-width: 800px;
}
.wiz-header {
	background: #677177;
	background: #f5f5f5;
	border: 1px solid #d6d6d6;
	border-radius: 5px 5px 0 0;
	padding: 0 15px;
}
.wiz-header h2 {
	float: left;
	font-weight: bold;
	color: #fff;
	color: #677177;
	font-size: 18px;
	margin: .7em 0 .8em;
	line-height: 1;
	-webkit-font-smoothing: antialiased;
}
.wiz-right {
	float: right;
	padding: 11px 0 0;
}
.wiz-right .wiz-steps {
	color: #677177;
	-webkit-font-smoothing: antialiased;
	font-weight: bold;
}
.wiz-right .wiz-close {
	background: #cacfd2;
	color: #6f7679;
	-webkit-font-smoothing: antialiased;
	padding: 3px 8px 4px;
	margin-left: 10px;
	border-radius: 50%;
	border-radius: 3px;
	font-size: 12px;
	font-weight: bold;
	line-height: 1;
	text-decoration: none;
}
.wiz-right .wiz-close:hover {
	background: #a3a9ad;
	color: #fff;
}
.wiz-step {
	padding: 40px;
	border-left: 1px solid #d6d6d6;
	border-right: 1px solid #d6d6d6;
}
.wiz-step-content {
	margin-bottom: 20px;
	color: #687379;
	line-height: 1.6;
}
.wiz-step-content p {
	margin: 0 0 .75em;
}
.wiz-step h3 {
	font-weight: bold;
	font-size: 28px;
	color: #44494D;
	margin: 0;
	-webkit-font-smoothing: antialiased;
}
.wiz-step-content .wiz-input-wrapper {
	margin: 30px 0 10px;
}
.wiz-step-content .wiz-input {
	float: left;
	margin: 0 10px 20px 0;
}
.wiz-step-content label {
	display: block;
	font-size: 12px;
	color: #677177;
}
.wiz-step-content input[type="text"],
.wiz-step-content input[type="email"],
.wiz-step-content input[type="password"] {
	font-size: 22px;
	padding: 10px;
	border: 1px solid #c9c9c9;
	border-radius: 2px;
	outline: none;
	max-width: 100%;
}
.wiz-step-content input[type="email"] {
	width: 265px;
}
.wiz-step-content input[type="text"]:focus,
.wiz-step-content input[type="email"]:focus,
.wiz-step-content input[type="password"]:focus {
	border-bottom: 1px solid #f95050;
}
.match-check {
	display: block;
	margin-top: 30px;
	color: #3ad07e;
}
.check {
	display: none;
	margin-top: 22px;
	position: absolute;
}
.wiz-step .wiz-backup {
	display: block;
	position: relative;
	float: left;
	padding: 20px 30px;
	margin: 30px 0 30px 0;
	text-align: center;
	background: #f95050;
	border-bottom: 4px solid #c04343;
	color: #fff;
	text-decoration: none;
	border-radius: 3px;
	font-size: 24px;
	width: 40%;
	font-weight: bold;
	-webkit-font-smoothing: antialiased;
}
.wiz-step .wiz-backup.database {
	border-radius:4px 0 0 4px;
	border-right: 1px solid #c04343;
}
.wiz-step .wiz-backup.full {
	border-radius: 0 4px 4px 0;
}
.wiz-step .wiz-backup:hover {
	background: #ff7474;
}
.waiting-tip {
	display: none;
}
.wiz-step .wiz-backup:active {
	background: #f95050;
	border-top: 3px solid transparent;
	border-bottom: 1px solid #c04343;
	top: 3px;
	padding: 17px 30px 20px 30px;
}
.wiz-step a.step {
	display: inline-block;
	padding: 10px 20px;
	margin-right: 3px;
	margin-bottom: 5px;
	background: #f95050;
	color: #fff;
	text-decoration: none;
	border-radius: 3px;
	font-size: 18px;
	font-weight: bold;
	-webkit-font-smoothing: antialiased;
}
.wiz-step a.step:hover {
	background: #fd2b2b;
	background: #e43333;
	background: #ff7373;
}
.wiz-step a.step.skip {
	background: #cacfd2;
	color: #6f7679;
}
.wiz-step a.step.skip:hover {
	background: #d6dce0;
}
.wiz-progress {
	border-radius: 0 0 5px 5px;
	border-bottom: 1px solid #d6d6d6;
	box-shadow: inset 0 0 2px #aaa;
}
.wiz-fill {
	height: 4px;
	background: #f95050;
	border-radius: 0 0 0 5px;
}
.wiz-fill.per20 {
	width: 20%;
}
.wiz-fill.per25 {
	width: 25%;
}
.wiz-fill.per40 {
	width: 40%;
}
.wiz-fill.per50 {
	width: 50%;
}
.wiz-fill.per60 {
	width: 60%;
}
.wiz-fill.per75 {
	width: 75%;
}
.wiz-fill.per80 {
	width: 80%;
}
.wiz-fill.per100 {
	width: 100%;
	border-radius: 0 0 4px 4px;
}

.tip-wrapper {
	position: absolute;
	top: 800px;
	left: 245px;
	z-index: 1000;
}
.tip {
	display: block;
	position: relative;
	background: #fff;
	background: rgba(255,255,255,0.9);
	box-shadow: 0 0 30px 10px rgba(0,0,0,0.1);
	padding: 20px 15px 15px 20px;
	border: 2px solid #f95050;
	border-radius: 5px;
	font-size: 16px;
	max-width: 350px;
}
.tip h2 {
	font-size: 12px;
	text-transform: uppercase;
	letter-spacing: 3px;
	color: #f95050;
	margin: 0;
}
.tip p {
	font-size: 18px;
	margin: 1em 0;
	color: #333;
	clear: both;
}
.tip button {
	background: #f95050;
	padding: 10px 20px;
	margin-top: 1em;
	font-size: 16px;
	font-weight: bold;
	color: #fff;
	outline: none;
	border: none;
	border-radius: 3px;
	-webkit-font-smoothing: antialiased;
	font-family: 'Helvetica Neue';
	cursor: pointer;
}
.tip button:hover {
	background: #ff7373;
}



.tip:after {
	bottom: 100%;
	border: solid transparent;
	content: " ";
	height: 0;
	width: 0;
	position: absolute;
	pointer-events: none;
}

.tip:after {
	border-color: rgba(49, 186, 213, 0);
	border-bottom-color: #f95050;
	border-width: 7px;
	left: 27px;
	margin-left: -7px;
}






.setup {
	margin: 40px 0 0 0;
}
input[type="text"],
input[type="email"],
input[type="password"] {
	font-size: 16px;
	padding: 8px;
	border: 1px solid #c9c9c9;
	border-radius: 2px;
	outline: none;
	width: 265px;
	max-width: 100%;
}
input[type="email"] {
	width: 265px;
}
input[type="text"]:focus,
input[type="email"]:focus,
input[type="password"]:focus {
	border-bottom: 1px solid #f95050;
	border: 1px solid #e0e0e0;
	border-bottom: 1px solid #b3b3b3;
}
p {
	font-size: 13px;
	color: #333;
	margin: 0.5em 0 1.25em;
}
label {
	display: block;
	font-size: 10px;
	line-height: 1.8;
	text-transform: uppercase;
	letter-spacing: 2px;
	color: #8a8f96;
}
select {
	font-size: 16px;
	width: 400px;
}
.input-float {
	float: left;
	margin: 0 10px 0 0;
}
.step {
	padding: 0;
	margin: 0 0 3em;

}
.step h4 {
	font-size: 16px;
	font-weight: normal;
	margin: 0 0 0.75em;
	color: #333;
	-webkit-font-smoothing: antialiased;
}
.number {
	font-weight: bold;
}
.box-options {
	margin: 1em 0;
}
.box-options ul {
	margin: 0;
	padding: 0;
	list-style: none;
}
.box-options ul li a {
	display: block;
	float: left;
	text-align: center;
	margin: 0 1em 1em 0;
	width: 120px;
	height: 30px;
	background: #fff;
	background-position: center center;
	background-size: contain;
	border: 2px solid #ebebeb;
	border-radius: 4px;
	text-decoration: none;
	padding: 98px 10px 12px 10px;
	position: relative;
	cursor: pointer;
}
.box-options ul li a:hover {
	border: 2px solid #9de4f5;
}
.box-options .ui-state-active a {
	border: 2px solid #6ce16c;
	border: 2px solid #fff;
	box-shadow: 0 0 0 3px #6be06b;
	box-shadow:	0 0 0 1px white,0 0 0 5px #1E8CBE;
	-webkit-transition:  all .1s linear;
	-moz-transition:  all .1s linear 0s;
}
.box-options ul li a.selected {
	border: 2px solid #fff;
	box-shadow: 0 0 0 3px #6be06b;
	box-shadow:	0 0 0 1px white,0 0 0 5px #1E8CBE;
	-webkit-transition:  all .1s linear;
	-moz-transition:  all .1s linear 0s;
}
.box-options p {
	display: inline-block;
	background: #d5ffba;
	border: 1px solid #bfefa1;
	color: #333;
	padding: 12px 14px;
	border-radius: 3px;
	margin: 1em 0 2em;
	font-size: 14px;
}
.box-options .stash a {
	background: url('destinations/stash2.jpg') center center no-repeat;
}
.box-options .stash span {
	position: absolute;
	bottom: 0;
	font-size: 12px;
	left: 0;
	background: #ebebeb;
	border-top: 1px solid #e1e1e1;
	color: #666;
	padding: 6px 0;
	right: 0;
}
#dest-1 {
	display: none;
}
.box-options .ftp a {
	background: url('destinations/ftp.jpeg') center center no-repeat;
}
.box-options .email a {
	background: url('destinations/email.jpeg') center center no-repeat;
}
.box-options .s3 a {
	background: url('destinations/amazon.jpeg') center center no-repeat;
}
.box-options .rackspace a {
	background: url('destinations/rackspace.jpeg') center center no-repeat;
}
.box-options .dropbox a {
	background: url('destinations/dropbox.jpeg') center center no-repeat;
}
.box-options .monthly a {
	background: url('monthly.png') center center no-repeat;
	background: url('schedule1.png') center 20px no-repeat;
}
.box-options .weekly a {
	background: url('weekly.png') center center no-repeat;
	background: url('schedule2.png') center 20px no-repeat;
}
.box-options .daily a {
	background: url('daily.png') center center no-repeat;
	background: url('schedule3.png') center 20px no-repeat;
}
.setup .check {
	margin: 7px 0 0 7px;
	max-width: 25px;
	height: auto;
}
.setup .checkborder,
.setup .checkborder:focus {
	border-bottom-color: #64ee9b;
}
/* totally flat style
.save button {
	background: #f95050;
	padding: 20px 40px;
	margin-top: 1em;
	font-size: 22px;
	font-weight: bold;
	color: #fff;
	outline: none;
	border: none;
	border-radius: 3px;
	-webkit-font-smoothing: antialiased;
	font-family: 'Helvetica Neue';
	cursor: pointer;
	-webkit-transition:  all .2s linear;
	-moz-transition:  all .2s linear 0s;
}
.save button:active {
	background: #97e9ec;
	color: #466c7d;
}
*/
.save button {
	display: block;
	position: relative;
	float: left;
	outline: none;
	padding: 20px 30px;
	margin: 30px 0 30px 0;
	text-align: center;
	background: #f95050;
	border: none;
	font-family: 'Helvetica Neue';
	border-bottom: 4px solid #c04343;
	color: #fff;
	text-decoration: none;
	border-radius: 3px;
	font-size: 24px;
	font-weight: bold;
	-webkit-font-smoothing: antialiased;
	cursor: pointer;
}
.save button:hover {
	background: #ff7373;
}

.save button:active {
	background: #f95050;
	border-top: 3px solid transparent;
	border-bottom: 1px solid #c04343;
	top: 3px;
	padding: 17px 30px 20px 30px;
}
/*wp style button

.save button {
	background-color: #21759B;
	background-image: -webkit-gradient(linear,left top,left bottom,from(#2A95C5),to(#21759B));
	background-image: -webkit-linear-gradient(top,#2A95C5,#21759B);
	background-image: -moz-linear-gradient(top,#2A95C5,#21759B);
	background-image: -ms-linear-gradient(top,#2A95C5,#21759B);
	background-image: -o-linear-gradient(top,#2A95C5,#21759B);
	background-image: linear-gradient(to bottom,#2A95C5,#21759B);
	border-color: #21759B;
	border-bottom-color: #1E6A8D;
	-webkit-box-shadow: inset 0 1px 0 rgba(120, 200, 230, 0.5);
	box-shadow: inset 0 1px 0 rgba(120, 200, 230, 0.5);
	color: white;
	text-decoration: none;
	text-shadow: 0 1px 0 rgba(0, 0, 0, 0.1);
}
.save button:hover {
	background-color: #278AB7;
	background-image: -webkit-gradient(linear,left top,left bottom,from(#2E9FD2),to(#21759B));
	background-image: -webkit-linear-gradient(top,#2E9FD2,#21759B);
	background-image: -moz-linear-gradient(top,#2E9FD2,#21759B);
	background-image: -ms-linear-gradient(top,#2E9FD2,#21759B);
	background-image: -o-linear-gradient(top,#2E9FD2,#21759B);
	background-image: linear-gradient(to bottom,#2E9FD2,#21759B);
	border-color: #1B607F;
	-webkit-box-shadow: inset 0 1px 0 rgba(120, 200, 230, 0.6);
	box-shadow: inset 0 1px 0 rgba(120, 200, 230, 0.6);
	color: white;
	text-shadow: 0 -1px 0 rgba(0, 0, 0, 0.3);
}
*/
</style>


<script type="text/javascript">
	jQuery(document).ready(function() {
		
		jQuery( '#pb_backupbuddy_quickstart_password, #pb_backupbuddy_quickstart_passwordconfirm' ).keyup( function() {
			if ( ( jQuery( '#pb_backupbuddy_quickstart_password' ).val() != '' ) && ( jQuery( '#pb_backupbuddy_quickstart_password' ).val() == jQuery( '#pb_backupbuddy_quickstart_passwordconfirm' ).val() ) ) {
				jQuery( '#pb_backupbuddy_quickstart_password_check_fail,#pb_backupbuddy_quickstart_password_check_fail > img' ).hide();
				jQuery( '#pb_backupbuddy_quickstart_password_check' ).show();
			} else {
				jQuery( '#pb_backupbuddy_quickstart_password_check' ).hide();
				if ( ( jQuery( '#pb_backupbuddy_quickstart_password' ).val() != '' ) || ( jQuery( '#pb_backupbuddy_quickstart_passwordconfirm' ).val() != '' ) ) { // Mismatch non-blank.
					jQuery( '#pb_backupbuddy_quickstart_password_check_fail,#pb_backupbuddy_quickstart_password_check_fail > img' ).show();
				} else if ( ( jQuery( '#pb_backupbuddy_quickstart_password' ).val() == '' ) && ( jQuery( '#pb_backupbuddy_quickstart_passwordconfirm' ).val() == '' ) ) { // both blank
					jQuery( '#pb_backupbuddy_quickstart_password_check_fail,#pb_backupbuddy_quickstart_password_check_fail > img' ).hide();
				}
			}
		} );
		
		jQuery( '#pb_backupbuddy_quickstart_email' ).change( function() {
			if ( ( jQuery(this).val() != '' ) && ( jQuery(this).val().indexOf( '@' ) >= 0 ) ) {
				jQuery( '#pb_backupbuddy_quickstart_email_check' ).show();
			} else {
				jQuery( '#pb_backupbuddy_quickstart_email_check' ).hide();
			}
		});
		
		/* Show success checkmark if pre-filled email looks valid. */
		quickstart_email = jQuery( '#pb_backupbuddy_quickstart_email' ).val();
		if ( ( quickstart_email != '' ) && ( quickstart_email.indexOf( '@' ) >= 0 ) ) {
			jQuery( '#pb_backupbuddy_quickstart_email_check' ).show();
		}
		
		jQuery( '#pb_backupbuddy_quickstart_destination' ).change( function() {
			if ( jQuery(this).val() == 'stash' ) {
				jQuery( '.stash-fields' ).slideDown();
			} else { // non-Stash destination.
				jQuery( '.stash-fields' ).slideUp();
			}
			if ( jQuery(this).val() != '' ) {
				tb_show( 'BackupBuddy', '<?php echo pb_backupbuddy::ajax_url( 'destination_picker' ); ?>&quickstart=true&add=' + jQuery(this).val() + '&filter=' + jQuery(this).val() + '&callback_data=&sending=0&TB_iframe=1&width=640&height=455', null );
			}
		});
		
		jQuery( '#pb_backupbuddy_quickstart_stashuser' ).change( function() {
			if ( ( jQuery(this).val() != '' ) && ( jQuery( '#pb_backupbuddy_quickstart_stashpass' ).val() != '' ) ) {
				pb_backupbuddy_stashtest();
			}
		});
		jQuery( '#pb_backupbuddy_quickstart_stashpass' ).change( function() {
			if ( ( jQuery(this).val() != '' ) && ( jQuery( '#pb_backupbuddy_quickstart_stashuser' ).val() != '' ) ) {
				pb_backupbuddy_stashtest();
			}
		});
		
		jQuery( '#pb_backupbuddy_quickstart_destination' ).change( function() {
			if ( jQuery(this).val() == '' ) {
				jQuery( '#pb_backupbuddy_quickstart_destination_check' ).hide();
			}
		});
		
		jQuery( '#pb_backupbuddy_quickstart_schedule' ).change( function() {
			if ( jQuery(this).val() != '' ) {
				jQuery( '#pb_backupbuddy_quickstart_schedule_check' ).show();
			} else {
				jQuery( '#pb_backupbuddy_quickstart_schedule_check' ).hide();
			}
		});
		
		
		
		jQuery( '#pb_backupbuddy_quickstart_form' ).submit( function() {
			jQuery( '#pb_backupbuddy_quickstart_saveloading' ).show();
			jQuery.post( '<?php echo pb_backupbuddy::ajax_url( 'quickstart_form' ); ?>', jQuery(this).serialize(), 
				function(data) {
					jQuery( '#pb_backupbuddy_quickstart_saveloading' ).hide();
					data = jQuery.trim( data );
					
					if ( data == 'Success.' ) {
						window.location.href = '<?php echo admin_url( 'admin.php' ); ?>?page=pb_backupbuddy_backup&backupbuddy_backup=full&quickstart_wizard=true';
						return false;
					} else {
						alert( "Error: \n\n" + data );
					}
					
				}
			);
			
			return false;
		});
		
		
	});
	
	
	
	function pb_backupbuddy_quickstart_destinationselected( dest_id ) {
		alert( 'Destination added. Returning to Quick Start Setup ...' );
		if ( jQuery( '#pb_backupbuddy_quickstart_destination' ).val() != '' ) {
			jQuery( '#pb_backupbuddy_quickstart_destination_check' ).show();
			jQuery( '#pb_backupbuddy_quickstart_destinationid' ).val( dest_id );
		} else {
			jQuery( '#pb_backupbuddy_quickstart_destination_check' ).hide();
		}
	}
	
	function pb_backupbuddy_stashtest() {
		jQuery( '#pb_backupbuddy_quickstart_stashloading' ).show();
		jQuery.post( '<?php echo pb_backupbuddy::ajax_url( 'quickstart_stash_test' ); ?>', {
				user: jQuery( '#pb_backupbuddy_quickstart_stashuser' ).val(),
				pass: jQuery( '#pb_backupbuddy_quickstart_stashpass' ).val()
			}, 
			function(data) {
				jQuery( '#pb_backupbuddy_quickstart_stashloading' ).hide();
				data = jQuery.trim( data );
				alert( data );
			}
		);
	}
</script>


<br>
Quickly get up and running by answering a few questions below. You may configure these and other settings at any time from the <a href="?page=pb_backupbuddy_settings">Settings</a> page.


<form id="pb_backupbuddy_quickstart_form" method="post">
	<input type="hidden" name="quicksetup" value="true">
	<div class="setup">
		<div class="step email">
			<h4><span class="number">1.</span> Enter your e-mail address to get backup and error notifications.</h4>
<!--			<p>You'll get emails when backups are completed or if there is an error with a backup.</p> -->
			<label>E-mail Address</label>
			<input type="email" id="pb_backupbuddy_quickstart_email" name="email" value="<?php echo pb_backupbuddy::$options['email_notify_error']; ?>">
			<img src="<?php echo pb_backupbuddy::plugin_url(); ?>/images/check.png" class="check" id="pb_backupbuddy_quickstart_email_check">
		</div>
		<div class="step password">
			<h4><span class="number">2.</span> Create a password for restoring or migrating your backups.</h4>
<!--			<p>You need to create a password to protect your backups so only you can restore them.</p> -->
			<div class="input-float">
				<label>Password</label>
				<input type="password" id="pb_backupbuddy_quickstart_password" name="password">
			</div>
			<div class="input-float">
				<label>Confirm Password</label>
				<input class="checkfield" type="password" id="pb_backupbuddy_quickstart_passwordconfirm" name="password_confirm">
			</div>
			<div class="input-float">
				<img src="<?php echo pb_backupbuddy::plugin_url(); ?>/images/check.png" class="check" id="pb_backupbuddy_quickstart_password_check" style="margin-top: 26px; margin-left: 0;">
				<div id="pb_backupbuddy_quickstart_password_check_fail" style="color: #E38282; display: none;">
					<img src="<?php echo pb_backupbuddy::plugin_url(); ?>/images/nomatch-x.png" class="check" style="margin-top: 26px; margin-left: 0;">
					<div style="display: inline-block; margin-left: 35px; margin-top: 32px;">
						<?php _e( "These don't match", 'it-l10n-backupbuddy' ); ?>
					</div>
				</div>
			</div>
			<div class="clearfix"></div>
		</div><br style="clear: both;"><br><br>
		
		<!--
		<div class="step limit">
			<h4><span class="number">3.</span> How many backups should be ket locally before deleting the oldest?</h4>
			<label>Number of backups to keep</label>
			<input type="email" id="pb_backupbuddy_quickstart_archive_limit name="archive_limit" size="7" style="width: 180px;" value="12">
			<img src="<?php echo pb_backupbuddy::plugin_url(); ?>/images/check.png" class="check" id="pb_backupbuddy_quickstart_archive_limit_check">
		</div>
	-->
		
		<div class="step destination">
			<h4><span class="number">3.</span> Where do you want to send your backups (scheduled or manually sent)?</h4>
			<div id="dest" class="box-options">
				<input type="hidden" id="pb_backupbuddy_quickstart_destinationid" name="destination_id" value="">
				<select id="pb_backupbuddy_quickstart_destination"  name="destination" class="change">
					<option value="">Local Only (no remote destination)</option>
					<option value="stash">BackupBuddy Stash (recommended)</option>
					<option value="ftp">FTP</option>
					<option value="email">Email</option>
					<option value="s3">Amazon S3</option>
					<option value="rackspace">Rackspace</option>
					<option value="dropbox">Dropbox</option>
				</select>
				<img src="<?php echo pb_backupbuddy::plugin_url(); ?>/images/check.png" class="check" id="pb_backupbuddy_quickstart_destination_check" />

					<div id="dest-1" class="stash-fields">
						<p style="margin-bottom: 0;">You get <strong>1GB</strong> of free storage on BackupBuddy Stash, our managed backup storage. <a href="#">Learn more about BackupBuddy Stash</a></p>
						<!--
						<div class="clearfix"></div>
						<div class="input-float">
							<label>iThemes Username</label>
							<input type="text" id="pb_backupbuddy_quickstart_stashuser">
						</div>
						<div>
							<label>Password</label>
							<input class="checkfield" type="password" id="pb_backupbuddy_quickstart_stashpass">
							<img src="check.png" class="check" />
							<span id="pb_backupbuddy_quickstart_stashloading" style="display: inline-block; display: none; margin-left: 35px;"><img src="<?php echo pb_backupbuddy::plugin_url(); ?>/images/loading.gif" <?php echo 'alt="', __('Loading...', 'it-l10n-backupbuddy' ),'" title="',__('Loading...', 'it-l10n-backupbuddy' ),'"';?> width="16" height="16" style="vertical-align: -3px" /></span>
						</div>
						<div class="clearfix"></div>
					-->
					</div>
					
			</div>
		</div>
		<div class="step schedule">
			<h4><span class="number">4.</span> How often do you want to back up your site?</h4>
<!--			<p>You should backup your site with the same regularity as you are updating it. If you don't update often, you don't need daily backups. But if you blog daily, you should backup daily. No sense losing content.</p> -->
			<div id="schedule" class="box-options clearfix">
				<select id="pb_backupbuddy_quickstart_schedule" name="schedule">
					<option value="">No Automated Schedule (manual only)</option>
					<option value="starter">Starter [Recommended] (Monthly complete backup + weekly database backup)</option>
					<option value="blogger">Active Blogger (Weekly complete backup + daily database backup)</option>
					<!-- <option value="custom">Custom</option> -->
				</select>
				<img src="<?php echo pb_backupbuddy::plugin_url(); ?>/images/check.png" class="check" id="pb_backupbuddy_quickstart_schedule_check" />
<!--
				<ul>
					<li class="monthly"><a href="javascript:void(0)">Monthly</a></li>
					<li class="weekly"><a href="javascript:void(0)">Weekly</a></li>
					<li class="daily"><a href="javascript:void(0)">Daily</a></li>
				</ul>
-->
			</div>
		</div>
		
		<div class="save">
			<button>Save & Make Your First Backup</button>
			<span id="pb_backupbuddy_quickstart_saveloading" style="display: inline-block; display: none; margin-left: 35px;"><img src="<?php echo pb_backupbuddy::plugin_url(); ?>/images/loading_large.gif" <?php echo 'alt="', __('Loading...', 'it-l10n-backupbuddy' ),'" title="',__('Loading...', 'it-l10n-backupbuddy' ),'"';?> style="vertical-align: -3px; margin-top: 30px;" /></span>
		</div>
	</div>
</form>