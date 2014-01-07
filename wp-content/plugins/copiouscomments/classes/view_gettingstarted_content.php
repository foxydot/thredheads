<?php
global $PluginBuddyCopiousComments;

// If they clicked the button to reset plugin defaults...
if (!empty($_POST['copiouscomments_reset_defaults'])) {
	$this->_options = $PluginBuddyCopiousComments->_defaults;
	$PluginBuddyCopiousComments->save();
	$this->alert( 'Plugin settings have been reset to defaults.' );
}
?>

<p>
CopiousComments displays a list of your top posts with customizable graphical representation when added to a <b>widget</b> or inserted with a <b>shortcode</b>.
</p>

<h3>Shortcode</h3>
You may quickly insert the comment listing by using the shortcode <b>[copiouscomments]</b>. Defaults may be overridden by supplying any option and value
pairs listed below. All parameters are optional.<br /><br />

<b>Default Shortcode</b><br />
&nbsp;&nbsp;&nbsp;[copiouscomments]
<br /><br />

<b>Parameters (optional)</b><br />
&nbsp;&nbsp;&nbsp;<i>posts</i> &middot; Number of posts to display. (number)<br />
&nbsp;&nbsp;&nbsp;<i>width</i> &middot; Width, in percent based on its container, of the CopiousComments entity.<br />
&nbsp;&nbsp;&nbsp;<i>truncate</i> &middot; Maximum number of title characters to display. An elipses (...) is appended to truncated titles.
<br /><br />

<b>Example</b><br />
&nbsp;&nbsp;&nbsp;[copiouscomments posts="10" width="40" truncate="60"]

<br /><br />

<h3>Version History</h3>
<textarea rows="7" cols="65"><?php readfile( $PluginBuddyCopiousComments->_pluginPath . '/history.txt' ); ?></textarea>
<br /><br />
<script type="text/javascript">
	jQuery(document).ready(function() {
		jQuery("#pluginbuddy_copiouscomments_debugtoggle").click(function() {
			jQuery("#pluginbuddy_copiouscomments_debugtoggle_div").slideToggle();
		});
	});
</script>

<a id="pluginbuddy_copiouscomments_debugtoggle" class="button secondary-button">Debugging Information</a>
<div id="pluginbuddy_copiouscomments_debugtoggle_div" style="display: none;">
	<h3>Debugging Information</h3>
	<?php
	echo '<textarea rows="7" cols="65">';
	echo 'Plugin Version = '.$PluginBuddyCopiousComments->_name.' '.$PluginBuddyCopiousComments->_version.' ('.$PluginBuddyCopiousComments->_var.')'."\n";
	echo 'WordPress Version = '.get_bloginfo("version")."\n";
	echo 'PHP Version = '.phpversion()."\n";
	global $wpdb;
	echo 'DB Version = '.$wpdb->db_version()."\n";
	echo "\n".serialize($PluginBuddyCopiousComments->_options);
	echo '</textarea>';
	?>
	<p>
	<form method="post" action="<?php
	admin_url( "admin.php?page={$this->_parent->_series}-settings" )
	?>">
		<input type="hidden" name="copiouscomments_reset_defaults" value="true" />
		<input type="submit" name="submit" value="Reset Plugin Settings & Defaults" id="reset_defaults" class="button secondary-button" onclick="if ( !confirm('WARNING: This will reset all settings associated with this plugin to their defaults. Are you sure you want to do this?') ) { return false; }" />
	</form>
	</p>
</div>