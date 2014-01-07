<?php
$this->_parent->load();
$this->admin_scripts();

if ( !empty( $_POST['save'] ) ) {
	$this->savesettings();
}

if ( !empty( $_GET['set_layout'] ) ) {
	$this->_parent->_options['layout'] = $_GET['set_layout'];
	
	$defaults = array();
	if ( file_exists( $this->_pluginPath . '/layouts/' . $_GET['set_layout'] . '/activate.txt' ) ) {
		// Usually sets $defaults var with array of defaults.
		eval( file_get_contents( $this->_pluginPath . '/layouts/' . $_GET['set_layout'] . '/activate.txt' ) );
	}
	 // Merge defaults over any existing settings for this layout.
	 if ( !empty( $this->_parent->_options['layouts'][ $_GET['set_layout'] ] ) ) {
		$this->_parent->_options['layouts'][ $_GET['set_layout'] ] =  array_merge( (array)$defaults, (array)$this->_parent->_options['layouts'][ $_GET['set_layout'] ] );
	} else { // No previous options so use new only.
		$this->_parent->_options['layouts'][ $_GET['set_layout'] ] =  (array)$defaults;
	}
	
	$this->_parent->save();
	$this->_parent->alert( 'Updated your selected layout to "' . htmlentities( $_GET['set_layout'] ) . '".' );
}
?>
<div class="wrap">
	<h2><?php echo $this->_name; ?> Settings</h2>
	
	<h3>Select Layout</h3>
	<div style="padding-left: 10px;">
		<p>Click a layout below to activate it. Only one layout may be used at a time.</p>
		<table><tr>
			
			<?php
			$layout_dir = $this->_pluginPath . '/layouts/';
			$layout_url = $this->_pluginURL . '/layouts/';
			if ( $handle = opendir( $layout_dir ) ) {
				$i = 0;
				while ( false !== ( $file = readdir( $handle ) ) ) {
					if ( $file != "." && $file != ".." ) {
						if ( is_dir( $layout_dir . $file ) ) {
							$i++;
							if ( $i > 3 ) {
								echo '</tr><tr>';
								$i = 1;
							}
							
							echo '<td style="padding: 0 15px 10px 0;">';
							$layout_title = ucwords( str_replace( '_', ' ', $file ) );
							echo '<b>' . $layout_title;
							if ( $this->_parent->_options['layout'] == $file ) {
								echo ' [active]';
								$active_layout = $layout_title;
							}
							echo '</b><br />';
							if ( file_exists($this->_pluginPath . '/layouts/' . $file . '/screenshot.png') ) {
								if ( ( $layout_title == 'Right-solid-dark' ) || ( $layout_title == 'Headline-dark' ) ) {
									$tmbstyle = '';
								} else {
									$tmbstyle = 'style="border: 1px solid #C6C6C6;"';
								}
								echo '<a href="' . $this->_selfLink . '-settings&set_layout=' . $file . '"><img src="' . $layout_url . $file . '/screenshot.png" ' . $tmbstyle . '/></a>';
							} else {
								echo '<a href="' . $this->_selfLink . '-settings&set_layout=' . $file . '"><img src="'.$this->_pluginURL.'/images/danger.png" style="border: 1px solid #C6C6C6;" /></a>';
							}
							echo '</td>';
						}
					}
				}
			}
			?>
		</tr></table>
	</div>
		
		

	
	
	<form method="post" action="<?php echo $this->_selfLink; ?>-settings">
		<input type="hidden" name="savepoint" value="layouts#<?php echo $this->_options['layout']; ?>" />
		<table class="form-table">
			
			<h3>Settings for Layout "<?php echo $active_layout; ?>"</h3>
			<?php
			$options = &$this->_options['layouts'][ $this->_options['layout'] ];
			
			if ( file_exists( $this->_pluginPath . '/layouts/' . $this->_options['layout'] . '/settings.txt' ) ) {
				$options_file = explode("\n", file_get_contents( $this->_pluginPath . '/layouts/' . $this->_options['layout'] . '/settings.txt' ) );
			
				// variable name [0],input type [1],display name [2],Tooltip text [3]
				foreach ( (array) $options_file as $item ) {
					$item = explode( ",", $item );
					if ( $item[1] == 'int' ) {
						echo '<tr>';
						echo '	<td><label for="width">' . $item[2] . ' ' . $this->tip( $item[3], '', false ) . '</label></td>';
						echo '	<td><input type="text" name="#' . $item[0] . '" id="width" size="5" maxlength="5" value="' . $options[ $item[0] ] . '" /> pixels</td>';
						echo '</tr>';
					} else {
						echo '<tr><td colspan="2">ERROR #5343: Unknown input type (' . $item[1] . ')</td></tr>';
					}
				}
			} else {
				echo '<tr><td colspan="2"><i>This layout does not have any layout-specific options.</i></td></tr>';
			}
			?>
			
		</table>
		
		<p class="submit"><input type="submit" name="save" value="Save Layout Settings" class="button-primary" id="save" /></p>
		<?php $this->nonce(); ?>

	</form>
	
	
	
	
		<form method="post" action="<?php echo $this->_selfLink; ?>-settings">
		<?php // Ex. for saving in a group you might do something like: $this->_options['groups'][$_GET['group_id']['settings'] which would be: ['groups'][$_GET['group_id']['settings'] ?>
		<input type="hidden" name="savepoint" value="" />
		<table class="form-table">
		
			<tr>
				<td>
					<h3>Global Plugin Settings</h3>
				</td>
				<td style="min-width: 450px"></td>
			</tr>
			
			<tr>
				<?php
				$categories = array();
				foreach ( (array) get_categories( array( 'hide_empty' => false ) ) as $category ) {
					$categories[$category->cat_ID] = $category->name;
				}
				?>
				<td><label for="category">Content Category<?php $this->tip( 'Post category to pull posts from for inclusion in the rotating posts entity.' ); ?></label></td>
				<td><select name="#category" id="category">
					<option value="0">Any Category (default)</option>
					<?php
					foreach ( (array) get_categories( array( 'hide_empty' => false ) ) as $category ) {
						echo '<option value="' . $category->cat_ID . '"';
						if ( $this->_options['category'] == $category->cat_ID ) {
							echo ' selected';
						}
						echo '>' . $category->name . '</option>';
					}
					?>
					</select>
				</td>
			</tr>
			
			<tr>
				<td><label for="post_type">Post Type<?php $this->tip( 'Post type to pull content from for inclusion in the rotating posts entity. This allows inclusion of custom post types. The post type name is in brackets to the right of the label.' ); ?></label></td>
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
			
			<tr>
				<td><label for="posts_count">Number of posts to show<?php $this->tip( '[Default: 5] - Number of features posts to show in the FeaturedBuddy slider' ); ?></label></td>
				<td><input type="text" name="#posts_count" id="posts_count" size="5" maxlength="5" value="<?php echo $this->_options['posts_count']; ?>" /> posts</td>
			</tr>
			<tr>
				<td><label for="excerpt_length">Number of words to show in excerpt<?php $this->tip( '[Default: 55] - Number of words that will in appear in the excerpt.' ); ?></label></td>
				<td><input type="text" name="#excerpt_length" id="excerpt_length" size="5" maxlength="5" value="<?php echo $this->_options['excerpt_length']; ?>" /> words</td>
			</tr>
			<tr>
				<td><label for="excerpt_readmore">Custom Read More link<?php $this->tip( '[Default: Read More &rarr;] - Link to the full article that appears below the excerpt.' ); ?></label></td>
				<td><input type="text" name="#excerpt_readmore" id="excerpt_readmore" size="20" maxlength="145" value="<?php echo $this->_options['excerpt_readmore']; ?>" /> </td>
			</tr>
			<tr>
				<td><label for="autostart">Time between automatic transitions<?php $this->tip( '[Default: 3000] - Time in milliseconds (ie. 3000 = 3 seconds) until transitioning to the next slide. Set to 0 to disable automatic sliding' ); ?></label></td>
				<td><input type="text" name="#autostart" id="autostart" size="8" maxlength="8" value="<?php echo $this->_options['autostart']; ?>" /> milliseconds</td>
			</tr>
			<tr>
				<td><label for="restart">Time after pause until autostart resumes<?php $this->tip( '[Default: 6000] - Time in milliseconds (ie. 6000 = 6 seconds) until transitioning resumes after manual action from a user. Set to 0 to disable automatic resuming of sliding' ); ?></label></td>
				<td><input type="text" name="#restart" id="restart" size="8" maxlength="8" value="<?php echo $this->_options['restart']; ?>" /> milliseconds</td>
			</tr>
			<tr>
				<td><label for="slidespeed">Slide speed<?php $this->tip( '[Default: 300] - Time in milliseconds (ie. 300 = 0.3 seconds) for slide animation' ); ?></label></td>
				<td><input type="text" name="#slidespeed" id="slidespeed" size="5" maxlength="5" value="<?php echo $this->_options['slidespeed']; ?>" /> milliseconds</td>
			</tr>
			<tr>
				<td><label for="fadespeed">Fade speed<?php $this->tip( '[Default: 200] - Time in milliseconds (ie. 200 = 0.2 seconds) for fade animation' ); ?></label></td>
				<td><input type="text" name="#fadespeed" id="fadespeed" size="5" maxlength="5" value="<?php echo $this->_options['fadespeed']; ?>" /> milliseconds</td>
			</tr>
			<tr>
				<td><label for="role_access">Plugin access limits <?php $this->tip( '[Default: administrator] - Determine which user roles are allowed to have access to all plugin features. WARNING: This will allow other roles access to configure plugin settings and content and could be a security risk if you are not careful and use caution.' ); ?></label></td>
				<td style="min-width: 450px;">
					<select name="#access" <?php
					if ( !current_user_can( 'activate_plugins' ) ) {
						echo ' disabled';
					}
					?>>
						<option value="manage_network" <?php if ( $this->_options['access'] == 'manage_network' ) { echo 'selected'; } ?>>Network Administrator</option>
						<option value="activate_plugins" <?php if ( $this->_options['access'] == 'activate_plugins' ) { echo 'selected'; } ?>>Administrator</option>
						<option value="moderate_comments" <?php if ( $this->_options['access'] == 'moderate_comments' ) { echo 'selected'; } ?>>Editor</option>
						<option value="edit_published_posts" <?php if ( $this->_options['access'] == 'edit_published_posts' ) { echo 'selected'; } ?>>Author</option>
						<option value="edit_posts" <?php if ( $this->_options['access'] == 'edit_posts' ) { echo 'selected'; } ?>>Contributer</option>
					</select>
				</td>
			</tr>
			
		</table>
		
		<p class="submit"><input type="submit" name="save" value="Save Plugin Settings" class="button-primary" id="save" /></p>
		<?php $this->nonce(); ?>
		
	</form>
</div>
