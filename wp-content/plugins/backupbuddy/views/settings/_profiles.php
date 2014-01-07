<?php
if ( !is_admin() ) { die( 'Access Denied.' ); }


if ( pb_backupbuddy::_POST( 'add_profile' ) == 'true' ) {
	
	$error = false;
	if ( pb_backupbuddy::_POST( 'title' ) == '' ) {
		pb_backupbuddy::alert( 'Error: You must provide a new profile title.', true );
		$error = true;
	}
	
	if ( false === $error ) {
		$profile = array(
			'title'		=> htmlentities( pb_backupbuddy::_POST( 'title' ) ),
			'type'		=>	pb_backupbuddy::_POST( 'type' ),
		);
		$profile = array_merge( pb_backupbuddy::settings( 'profile_defaults' ), $profile );
		pb_backupbuddy::$options['profiles'][] = $profile;
		pb_backupbuddy::save();
		pb_backupbuddy::alert( 'New profile "' . htmlentities( pb_backupbuddy::_POST( 'title' ) ) . '" added. Select it from the list below to customize its settings and override global defaults.' );
	}
	
} // end if add profile.


if ( ( pb_backupbuddy::_GET( 'delete_profile' ) != '' ) && ( is_numeric( pb_backupbuddy::_GET( 'delete_profile' ) ) ) ) {
	if ( pb_backupbuddy::_GET( 'delete_profile' ) > 2 ) {
		$profile_title = pb_backupbuddy::$options['profiles'][pb_backupbuddy::_GET( 'delete_profile' )]['title'];
		unset( pb_backupbuddy::$options['profiles'][pb_backupbuddy::_GET( 'delete_profile' )] );
		pb_backupbuddy::save();
		pb_backupbuddy::alert( 'Deleted profile "' . htmlentities( $profile_title ) . '".' );
	} else {
		pb_backupbuddy::alert( 'Invalid profile ID. Cannot delete base profiles.' );
	}
}

?>

<style>
.profile_box {
	background: #ECECEC;
	margin: 0;
	display: block;
	border-radius: 5px;
	padding: 10px 10px 0px 10px;
	margin-bottom: 40px;
	border-radius: 5px;
	border: 1px solid #d6d6d6;
	border-top: 1px solid #ebebeb;
	box-shadow: 0px 3px 0px 0px #aaaaaa;
	box-shadow: 0px 3px 0px 0px #CFCFCF;
	font-size: auto;
	//min-height: 65px;
}
.profile_text {
	display: block;
	float: left;
	line-height: 26px;
	//margin-right: 8px;
	margin-left: 10px;
	font-weight: bold;
}
.profile_type {
	display: block;
	float: left;
	line-height: 26px;
	margin-right: 10px;
	//width: 68px;
	color: #aaa;
}
.profile_divide {
	border-right: 1px solid #ebebeb;
	display: block;
	float: left;
	width: 1px;
	height: 100%;
}
.profile_item {
	display: block;
	background: #fff;
	border: 1px solid #e7e7e7;
	border-top: 1px solid #ebebeb;
	border-bottom: 1px solid #c9c9c9;
	border-radius: 4px 0 0 4px;
	padding: 15px;
	margin-bottom: 13px;
	text-decoration: none;
	color: #252525;
	line-height: 2;
	font-size: medium;
	height: 25px;
	
	float: left;
	margin-right: 10px;
}

.profile_item:hover {
	color: #da2828;
	cursor: pointer;
}

.profile_item_selected {
	border-bottom: 3px solid #da2828;
	margin-bottom: 10px;
}

.profile_choose {
	font-size: 20px;
	font-family: "HelveticaNeue-Light","Helvetica Neue Light","Helvetica Neue",sans-serif;
	padding: 5px 0 15px 5px;
	color: #464646;
}
.profile_add {
	display: block;
	width: 32px;
	height: 32px;
	background: transparent url('<?php echo pb_backupbuddy::plugin_url(); ?>/images/dest_plus.png') top left no-repeat;
	vertical-align: -3px;
}
.profile_add:hover {
	background: transparent url('<?php echo pb_backupbuddy::plugin_url(); ?>/images/dest_plus.png') bottom left no-repeat;
}
</style>

<script type="text/javascript">
	jQuery(document).ready(function() {
		jQuery( '.profile_item' ).click( function() {
			if ( jQuery(this).attr( 'id' ) == 'pb_backupbuddy_profileadd' ) {
				return;
			}
			jQuery( '.profile_item' ).removeClass( 'profile_item_selected' );
			jQuery(this).addClass( 'profile_item_selected' );
			jQuery( '#pb_backupbuddy_iframe' ).attr( 'src', jQuery(this).attr( 'href' ) );
		});
		
		jQuery( '#pb_backupbuddy_profileadd_plusbutton' ).click( function() {
			jQuery( '#pb_backupbuddy_iframe' ).attr( 'src', 'about:blank' );
			jQuery(this).hide();
			jQuery( '#pb_backupbuddy_profileadd' ).slideDown();
		});
	});
</script>


<br>
<span class="pb_label" style="font-size: 12px; margin-left: 10px; position: relative; top: -3px;">Tip</span>
Customized Profiles override default settings.
Defaults are configured in other tabs.
Profiles only need modified when choosing to override defaults on a per-profile basis.
<br><br>

<div class="profile_box">
	<div class="profile_choose">
		Choose a profile below to edit:
	</div>
	<!-- div class="profile_item" style="border-bottom: 4px solid #c04343; height: 23px;">
		<span class="profile_type">Defaults</span>
		<span class="profile_divide"></span>
		<span class="profile_text">Global Default Settings</span>
	</div -->
	
	<?php
	foreach( pb_backupbuddy::$options['profiles'] as $profile_id => $profile ) {
		if ( $profile['type'] == 'defaults' ) { continue; } // Skip showing defaults here...
		?>
		<div class="profile_item" href="<?php echo pb_backupbuddy::ajax_url( 'backup_profile_settings' ); ?>&profile=<?php echo $profile_id; ?>">
			<span class="profile_type"><?php
				if ( $profile['type'] == 'db' ) {
					echo 'Database';
				} elseif ( $profile['type'] == 'full' ) {
					echo 'Full';
				} elseif( $profile['type'] == 'files' ) {
					echo 'Files';
				} else {
					echo 'unknown(' . htmlentities( $profile['type'] ). ')';
				}
			?></span>
			<span class="profile_divide"></span>
			<span class="profile_text"><?php echo htmlentities( $profile['title'] ); ?></span>
		</div>
		<?php
	}
	?>
	
	<div class="profile_item" id="pb_backupbuddy_profileadd" style="display: none;" href="<?php echo pb_backupbuddy::ajax_url( 'backup_profile_settings' ); ?>&profile=<?php echo $profile_id; ?>">
		<form method="post" action="?page=pb_backupbuddy_settings&tab=1">
			<input type="hidden" name="add_profile" value="true">
			<span class="profile_type">
				<select name="type">
					<option value="db">Database only</option>
					<option value="full">Full (DB + Files)</option>
					<!-- option value="files">Files only</option -->
				</select>
			</span>
			<span class="profile_divide" style="min-height: 30px;"></span>
			<span class="profile_text"><input type="text" name="title" style="width: 150px" maxlength="20"></span>
			<input type="submit" name="submit" value="+ Add" class="button button-primary" style="vertical-align: 3px; margin-left: 3px;">
		</form>
	</div>
	
	<div class="profile_item" id="pb_backupbuddy_profileadd_plusbutton" style="padding-top: 12px; padding-bottom: 18px;">
		<span class="profile_add">
	</div>
	
	<br style="clear: both;">
	
</div>




<iframe id="pb_backupbuddy_iframe" src="" width="100%" style="max-width: 1000px;" height="1800" frameBorder="0" padding="0" margin="0">Error #4584594579. Browser not compatible with iframes.</iframe>

