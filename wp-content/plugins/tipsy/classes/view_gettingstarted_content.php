<?php
global $pluginbuddy_tipsy;

// If they clicked the button to reset plugin defaults...
if (!empty($_POST['tipsy_reset_defaults'])) {
	$pluginbuddy_tipsy->_options = $pluginbuddy_tipsy->_defaults;
	$pluginbuddy_tipsy->save();
	$this->alert( 'Plugin settings have been reset to defaults.' );
}
?>

<br />
The Tipsy plugin adds the ability to create tooltips for placement in posts, pages, and widgets (through the use of shortcodes). Tooltips are displayed when hovering or clicking on specified content. This allows you to provide additional information to visitors. 
<br /><br />

	<ul>
		<li><h3>Instructions</h3></li>
		<li>
			<ol>
				<li>Click the <a href="<?php echo $pluginbuddy_tipsy->_selfLink; ?>-settings">Tipsy</a> link in the DisplayBuddy menu to begin creating a group.</li>
				<li>After you have created a group go to any post or page where you want to display the tooltip.</li>
				<li>Highlight the content that you want to apply the tooltip to and click the WSIWYG Tipsy button. <img src="<?php  echo $pluginbuddy_tipsy->_pluginURL . '/images/tipsy1.png' ?>"/></li>
				<li>Choose the Tipsy group you want applied to the content.</li>
				<li>Click submit.</li>
			</ol>
		</li>
	</ul>
<h3>Shortcode Example</h3>
<i>Display your Tipsy in style by using the shortcode <b>[tipsy content="tip content goes here" group="0"]</b>CONTENT THAT THE TIP APPLIES TO<b>[/tipsy]</b></i>
<br /><br />
<b>Shortcode Parameters</b><br />
&nbsp;&nbsp;&nbsp;<i>content</i> &middot; Content that goes inside of the tooltip.<br />
&nbsp;&nbsp;&nbsp;<i>group</i> &middot; group number for the tooltip group that you want to display.<br /><br />

<b>Addition Information and Support</b><br>
<i>For documentation on Tipsy, visit the <a href="http://ithemes.com/codex/page/Tipsy">Tipsy Codex</a>.</i><br />
<i>Visit the Tipsy <a href="http://ithemes.com/support/tipsy">Forums</a> for Support.</i>



<h3>Version History</h3>
<textarea rows="7" cols="65"><?php readfile( $pluginbuddy_tipsy->_pluginPath . '/history.txt' ); ?></textarea>
<br /><br />
<script type="text/javascript">
	jQuery(document).ready(function() {
		jQuery("#pluginbuddy_tipsy_debugtoggle").click(function() {
			jQuery("#pluginbuddy_tipsy_debugtoggle_div").slideToggle();
		});
	});
</script>

<a id="pluginbuddy_tipsy_debugtoggle" class="button secondary-button">Debugging Information</a>
<div id="pluginbuddy_tipsy_debugtoggle_div" style="display: none;">
	<h3>Debugging Information</h3>
	<?php
	echo '<textarea rows="7" cols="65">';
	echo 'Plugin Version = '.$pluginbuddy_tipsy->_name.' '.$pluginbuddy_tipsy->_version.' ('.$pluginbuddy_tipsy->_var.')'."\n";
	echo 'WordPress Version = '.get_bloginfo("version")."\n";
	echo 'PHP Version = '.phpversion()."\n";
	global $wpdb;
	echo 'DB Version = '.$wpdb->db_version()."\n";
	echo "\n".serialize($pluginbuddy_tipsy->_options);
	echo '</textarea>';
	?>
	<p>
	<form method="post" action="<?php
	admin_url( "admin.php?page={$this->_parent->_series}-settings" )
	?>">
		<input type="hidden" name="tipsy_reset_defaults" value="true" />
		<input type="submit" name="submit" value="Reset Plugin Settings & Defaults" id="reset_defaults" class="button secondary-button" onclick="if ( !confirm('WARNING: This will reset all settings associated with this plugin to their defaults. Are you sure you want to do this?') ) { return false; }" />
	</form>
	</p>
</div><br /><br />
<p>
	A special thanks to <a href="http://code.drewwilson.com/entry/tiptip-jquery-plugin">drewwilson.com</a> for creating some great javascript that is used in this plugin.
</p>
