<?php
global $pluginbuddy_slideshow;

// If they clicked the button to reset plugin defaults...
if (!empty($_POST['slideshow_reset_defaults'])) {
	$this->_options = $pluginbuddy_slideshow->_defaults;
	$pluginbuddy_slideshow->save();
	$this->alert( 'Plugin settings have been reset to defaults.' );
}
?>

<br />
Slideshow lets you display a rotating set of images anywhere on your site with customizable animations, content, and effects.
Multiple groups of images may be created for use anywhere on your site.  Each group can be fully customized.
<br /><br />

	<ul>
		<li><h3>Step 1</h3></li>
		<li>
			<ol>
				<li>Click the <a href="<?php echo $pluginbuddy_slideshow->_selfLink; ?>-settings">Slideshow</a> link in the DisplayBuddy menu to begin creating a group.</li>
				<li>After you have created a group you can select a group to edit or add new images to.</li>
				<li>Once you have added images to your group you can edit settings and reorder them.</li>
			</ol>
		</li>
	
		<li><h3>Step 2</h3></li>
		<li>Now that you have created a group and added images to it you can display it on your website.</li>
		<li>
			<ol>
				<li>One way you can display a Slideshow group on your site is by going to "Widgets" under appearance and add it into your widget areas.</li>
				<li>Another way to display a Slideshow group on your site is by getting the shortcode snippet from the <a href="<?php echo $pluginbuddy_slideshow->_selfLink; ?>-settings">Slideshow</a> groups table and adding that shortcode to the content anywhere on your site.</li>
			</ol>
		</li>
	</ul>

<h3>Shortcode</h3>
Display your Slideshow in style by using the shortcode <b>[pb_slideshow group="#"]</b> or by adding a widget.
<br /><br />



<h3>Version History</h3>
<textarea rows="7" cols="65"><?php readfile( $pluginbuddy_slideshow->_pluginPath . '/history.txt' ); ?></textarea>
<br /><br />
<script type="text/javascript">
	jQuery(document).ready(function() {
		jQuery("#pluginbuddy_slideshow_debugtoggle").click(function() {
			jQuery("#pluginbuddy_slideshow_debugtoggle_div").slideToggle();
		});
	});
</script>

<a id="pluginbuddy_slideshow_debugtoggle" class="button secondary-button">Debugging Information</a>
<div id="pluginbuddy_slideshow_debugtoggle_div" style="display: none;">
	<h3>Debugging Information</h3>
	<?php
	echo '<textarea rows="7" cols="65">';
	echo 'Plugin Version = '.$pluginbuddy_slideshow->_name.' '.$pluginbuddy_slideshow->_version.' ('.$pluginbuddy_slideshow->_var.')'."\n";
	echo 'WordPress Version = '.get_bloginfo("version")."\n";
	echo 'PHP Version = '.phpversion()."\n";
	global $wpdb;
	echo 'DB Version = '.$wpdb->db_version()."\n";
	echo "\n".serialize($pluginbuddy_slideshow->_options);
	echo '</textarea>';
	?>
	<p>
	<form method="post" action="<?php
	admin_url( "admin.php?page={$this->_parent->_series}-settings" )
	?>">
		<input type="hidden" name="slideshow_reset_defaults" value="true" />
		<input type="submit" name="submit" value="Reset Plugin Settings & Defaults" id="reset_defaults" class="button secondary-button" onclick="if ( !confirm('WARNING: This will reset all settings associated with this plugin to their defaults. Are you sure you want to do this?') ) { return false; }" />
	</form>
	</p>
</div><br /><br />
<p>
	A special thanks to <a href="http://nivo.dev7studios.com">dev7studios</a> & <a href="http://jquery.malsup.com/cycle/">malsup</a> for creating some great javascript that is used in this plugin.
</p>