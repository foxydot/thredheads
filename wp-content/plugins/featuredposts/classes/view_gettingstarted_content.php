<?php
global $PluginBuddyFeaturedPosts;

// If they clicked the button to reset plugin defaults...
if (!empty($_POST['featuredposts_reset_defaults'])) {
	$this->_options = $PluginBuddyFeaturedPosts->_defaults;
	$PluginBuddyFeaturedPosts->save();
	$this->alert( 'Plugin settings have been reset to defaults.' );
}
?>

<p>
	FeaturedPosts is a tool to display the latest posts with featured images attached to them as rotating slides. With easy to use controls and stylish animations you can easily display more information to your users in an eye-pleasing manner.
</p>
<p>
	Display your featured content in style by using the shortcode <b>[featuredposts]</b> or by adding a widget.
</p>
<p>
Due to variances in theme style and container sizes, you may need to make slight adjustments to the size and width you specify.
For optimum performance image and entity width should be the same.
</p>


<h3>Version History</h3>
<textarea rows="7" cols="65"><?php readfile( $PluginBuddyFeaturedPosts->_pluginPath . '/history.txt' ); ?></textarea>
<br /><br />
<script type="text/javascript">
	jQuery(document).ready(function() {
		jQuery("#pluginbuddy_featuredposts_debugtoggle").click(function() {
			jQuery("#pluginbuddy_featuredposts_debugtoggle_div").slideToggle();
		});
	});
</script>

<a id="pluginbuddy_featuredposts_debugtoggle" class="button secondary-button">Debugging Information</a>
<div id="pluginbuddy_featuredposts_debugtoggle_div" style="display: none;">
	<h3>Debugging Information</h3>
	<?php
	echo '<textarea rows="7" cols="65">';
	echo 'Plugin Version = '.$PluginBuddyFeaturedPosts->_name.' '.$PluginBuddyFeaturedPosts->_version.' ('.$PluginBuddyFeaturedPosts->_var.')'."\n";
	echo 'WordPress Version = '.get_bloginfo("version")."\n";
	echo 'PHP Version = '.phpversion()."\n";
	global $wpdb;
	echo 'DB Version = '.$wpdb->db_version()."\n";
	echo "\n".serialize($PluginBuddyFeaturedPosts->_options);
	echo '</textarea>';
	?>
	<p>
	<form method="post" action="<?php
	admin_url( "admin.php?page={$this->_parent->_series}-settings" )
	?>">
		<input type="hidden" name="featuredposts_reset_defaults" value="true" />
		<input type="submit" name="submit" value="Reset Plugin Settings & Defaults" id="reset_defaults" class="button secondary-button" onclick="if ( !confirm('WARNING: This will reset all settings associated with this plugin to their defaults. Are you sure you want to do this?') ) { return false; }" />
	</form>
	</p>
</div>