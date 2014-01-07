<?php global $PluginBuddyVideoShowcase;

// If they clicked the button to reset plugin defaults...
if (!empty($_POST['videoshowcase_reset_defaults'])) {
	$this->_options = $PluginBuddyVideoShowcase->_defaults;
	$PluginBuddyVideoShowcase->save();
	$this->alert( 'Plugin settings have been reset to defaults.' );
}
?>
	<p>
		Video Showcase is a plugin that allows you to easily create a
		group of images that link to embeded videos. When a visitor 
		clicks one of the image links the video for that link is displayed
		in a styled thickbox. The different video links are stored in
		groups so that the different groups can be displayed throughout
		your website by placing them in widget areas or using shortcodes.
	</p>
	<ul>
		<li><h3>Step 1</h3></li>
		<li>
			<ol>
				<li>Click the <a href="<?php echo $PluginBuddyVideoShowcase->_selfLink; ?>-settings">Video Showcase</a> link in the DisplayBuddy menu to begin creating a group.</li>
				<li>After you have created a group you can select a group to edit or add new videos to.</li>
				<li>Once you have added videos to your group you can edit and reorder them.</li>
			</ol>
		</li>
	
		<li><h3>Step 2</h3></li>
		<li>Now that you have created a group and added videos to it you can display it on your website.</li>
		<li>
			<ol>
				<li>One way you can display a Video Showcase group on your web site is by going to "Widgets" under appearance and add it into your widget areas.</li>
				<li>Another way to display a Video Showcase group on your web site is by getting the shortcode snippet from the <a href="<?php echo $PluginBuddyVideoShowcase->_selfLink; ?>-settings">Video Showcase</a> groups table and adding that shortcode to the content anywhere on your site.</li>
			</ol>
		</li>
	</ul>
	<br/>

	<script type="text/javascript">
		jQuery(document).ready(function() {
			jQuery("#pluginbuddy_videoshowcase_debugtoggle").click(function() {
				jQuery("#pluginbuddy_videoshowcase_debugtoggle_div").slideToggle();
			});
		});
	</script>

	<a id="pluginbuddy_videoshowcase_debugtoggle" class="button secondary-button">Debugging Information</a>
	<div id="pluginbuddy_videoshowcase_debugtoggle_div" style="display: none;">
		<h3>Debugging Information</h3>
		<?php
		echo '<textarea rows="7" cols="65">';
		echo 'Plugin Version = '.$PluginBuddyVideoShowcase->_name.' '.$PluginBuddyVideoShowcase->_version.' ('.$PluginBuddyVideoShowcase->_var.')'."\n";
		echo 'WordPress Version = '.get_bloginfo("version")."\n";
		echo 'PHP Version = '.phpversion()."\n";
		global $wpdb;
		echo 'DB Version = '.$wpdb->db_version()."\n";
		echo "\n".serialize($PluginBuddyVideoShowcase->_options);
		echo '</textarea>';
		?>
		<p>
		<form method="post" action="<?php admin_url( 'admin.php?page={$this->_parent->_series}-settings' )?>">
			<input type="hidden" name="videoshowcase_reset_defaults" value="true" />
			<input type="submit" name="submit" value="Reset Plugin Settings & Defaults" id="reset_defaults" class="button secondary-button" onclick="if ( !confirm('WARNING: This will reset all settings associated with this plugin to their defaults. Are you sure you want to do this?') ) { return false; }" />
		</form>
		</p>
	</div>
	<p>A special thanks to <a href="http://www.no-margin-for-errors.com/projects/prettyphoto-jquery-lightbox-clone/" target="_blank" >prettyPhoto</a> for creating some great javascript that is used in this plugin.</p>

