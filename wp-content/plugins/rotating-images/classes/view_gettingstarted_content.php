<?php
global $iThemesRotatingImages;
?>

<p>
Rotating Images provides a way to display images in a widget area or shortcode with animated transitions or display randomized static images.
Rotating Images supports multiple instances of itself on one or more pages.
</p>

<p>
	<ol>
		<li>Create a new image group on the Rotating Images page for each collection of images.</li>
		<li>Select a created group to add images into it and configure settings.</li>
		<li>Use one or more of the methods below to insert the Rotating Images plugin for viewing:
			<ul style="padding-top: 5px;">
				<li>&middot; Use the Wordpress widget administrator to place the widget in a widget space or sidebar</li>
				<li>&middot; Enter a shortcode from the group list in a post or page as desired to have it displayed. Ex: [it-rotate group="0"]</li>
			</ul>
		</li>
	</ol>
</p>

<h3>Version History</h3>
<textarea rows="7" cols="65"><?php readfile( $path . '/history.txt' ); ?></textarea>
<br /><br />
<script type="text/javascript">
	jQuery(document).ready(function() {
		jQuery("#pluginbuddy_rotatingimages_debugtoggle").click(function() {
			jQuery("#pluginbuddy_rotatingimages_debugtoggle_div").slideToggle();
		});
	});
</script>

<a id="pluginbuddy_rotatingimages_debugtoggle" class="button secondary-button">Debugging Information</a>
<div id="pluginbuddy_rotatingimages_debugtoggle_div" style="display: none;">
	<h3>Debugging Information</h3>
	<?php
	echo '<textarea rows="7" cols="65">';
	echo 'Plugin Version = '.$iThemesRotatingImages->_name.' '.$iThemesRotatingImages->_version.' ('.$iThemesRotatingImages->_var.')'."\n";
	echo 'WordPress Version = '.get_bloginfo("version")."\n";
	echo 'PHP Version = '.phpversion()."\n";
	global $wpdb;
	echo 'DB Version = '.$wpdb->db_version()."\n";
	echo "\n".serialize($iThemesRotatingImages->_options);
	echo '</textarea>';
	?>
	<p>
	<form method="post" action="<?php echo $this->_selfLink; ?>">
		<input type="hidden" name="rotatingimages_reset_defaults" value="true" />
		<input type="submit" name="submit" value="Reset Plugin Settings & Defaults" id="reset_defaults" class="button secondary-button" onclick="if ( !confirm('WARNING: This will reset all settings associated with this plugin to their defaults. Are you sure you want to do this?') ) { return false; }" />
	</form>
	</p>
</div>