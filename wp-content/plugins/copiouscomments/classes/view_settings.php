<?php
$this->_parent->load();
$this->admin_scripts();

if ( !empty( $_POST['save'] ) ) {
	$this->savesettings();
}
?>
<div class="wrap">
	<h2><?php echo $this->_name; ?> Settings</h2>
	<p>
		<b>Note:</b> Optional widget and shortcode settings will override default values below (except post type option).
	</p>
	<form method="post" action="<?php echo $this->_selfLink; ?>-settings">
		<?php // Ex. for saving in a group you might do something like: $this->_options['groups'][$_GET['group_id']['settings'] which would be: ['groups'][$_GET['group_id']['settings'] ?>
		<input type="hidden" name="savepoint" value="" />
		<table class="form-table">
			<tr>
				<td><label for="width">Default width (in percent) <?php $this->tip( 'Default width (in percent) to display the entity. You may use this to reduce the width or to allow for padding or other styling. You may override this in the widget or shortcode settings.' ); ?></label></td>
				<td><input type="text" name="#width" id="width" size="5" maxlength="3" value="<?php echo $this->_options['width']; ?>" />%</td>
			</tr>
			<tr>
				<td><label for="posts">Default number of posts to show <?php $this->tip( 'Default number of posts to show. You may override this in the widget or shortcode settings.' ); ?></label></td>
				<td><input type="text" name="#posts" id="posts" size="5" maxlength="5" value="<?php echo $this->_options['posts']; ?>" /></td>
			</tr>
			<tr>
				<td><label for="truncate">Default maximum title length <?php $this->tip( 'Default maximum number of characters to show in a title. If a title is longer than this it will be truncated an an elipses will be appended (...).' ); ?></label></td>
				<td><input type="text" name="#truncate" id="truncate" size="5" maxlength="5" value="<?php echo $this->_options['truncate']; ?>" /> characters</td>
			</tr>
			<tr>
				<td><label for="post_type">Post Type (global setting)<?php $this->tip( 'Post type to pull posts from for inclusion in entity. This allows inclusion of custom post types. The post type name is in brackets to the right of the label. This option cannot currently be overridden by Widgets or Shortcodes and applies to all instances.' ); ?></label></td>
				<td><select name="#post_type" id="post_type">
					<?php
					global $wp_post_types;
					foreach ( (array) $wp_post_types as $post_type_key => $post_type ) {
						if ( ( $post_type_key != 'attachment' ) && ( $post_type_key != 'revision' ) && ( $post_type_key != 'nav_menu_item' ) ) {
							echo '<option value="' . $post_type_key . '"';
							if ( $this->_options['post_type'] == $post_type_key ) {
								echo ' selected';
							}
							echo '>' . $post_type->label;
							echo ' [' . $post_type_key . ']';
							if ( $post_type_key == 'post' ) { echo ' (default)'; }
							echo '</option>';
						}
					}
					?>
					</select>
				</td>
			</tr>
		</table>
		<p class="submit"><input type="submit" name="save" value="Save Settings" class="button-primary" id="save" /></p>
		<?php $this->nonce(); ?>
		
	</form>
	
	<?php
	require_once( $this->_parent->_pluginPath . '/lib/styleman/styleman.php' );
	$wp_upload_dir = WP_UPLOAD_DIR();
	$style_definitions_file = $this->_parent->_pluginPath . '/layouts/' . $this->_options['layout'] . '/style_definitions.txt';
	$style_file = $this->_parent->_pluginPath . '/layouts/' . $this->_options['layout'] . '/style.css';
	$custom_style_file = $wp_upload_dir['basedir'] . '/copiouscomments/' . $this->_options['layout'] . '/style_custom.css';
	// Definitions text file, default styling css file, custom styling css file for overriding, variable to set true or false to on whether to include custom styling
	$pbstyles->styleman( $style_definitions_file, $style_file, $custom_style_file, $this->_options['customstyles_enabled'] );
	?>
		<?php $this->nonce(); ?>
		</form>
</div>
