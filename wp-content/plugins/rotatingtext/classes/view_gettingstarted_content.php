<?php global $PluginBuddyrotatingtext; ?>
	<p>
		Rotating Text is a plugin that allows you
		to create groups of different text entries to rotate
		through by fading each entry in and out. You can display
		the groups throughout your web site by placing them in
		widget areas or by using shortcode.
	</p>
	<ul>
		<li><h3>Step 1</h3></li>
		<li>
			<ol>
				<li>Click the <a href="<?php echo $PluginBuddyrotatingtext->_selfLink; ?>-settings">Rotating Text</a> link in the DisplayBuddy menu to begin creating a group.</li>
				<li>After you have created a group you can click the group name to edit your group settings or add new entries.</li>
				<li>Once you have added entries to your group you can edit them to customize their styles or reorder them.</li>
			</ol>
		</li>
	
		<li><h3>Step 2</h3></li>
		<li>Now that you have created your group and added entries to it you can add it to you web page.</li>
		<li>
			<ol>
				<li>One way you can display a Rotating Text group on your web site is by going to "Widgets" under appearance and adding it into your widget areas.</li>
				<li>Another way to display a Rotating Text group on your web site is by getting the shortcode snippet from the <a href="<?php echo $PluginBuddyrotatingtext->_selfLink; ?>-settings">Rotating Text</a> groups table and adding that to the content anywhere on your site.</li>
			</ol>
		</li>
	</ul>
	<br/>

	<script type="text/javascript">
		jQuery(document).ready(function() {
			jQuery("#pluginbuddy_rotatingtext_debugtoggle").click(function() {
				jQuery("#pluginbuddy_rotatingtext_debugtoggle_div").slideToggle();
			});
		});
	</script>

	<a id="pluginbuddy_rotatingtext_debugtoggle" class="button secondary-button">Debugging Information</a>
	<div id="pluginbuddy_rotatingtext_debugtoggle_div" style="display: none;">
		<h3>Debugging Information</h3>
		<?php
		echo '<textarea rows="7" cols="55">';
		echo 'Plugin Version = rotatingtext ' . $PluginBuddyrotatingtext->_version . ' (' . $PluginBuddyrotatingtext->_var . ')' . "\n";
		echo 'WordPress Version = ' . get_bloginfo("version") . "\n";
		echo 'PHP Version = ' . phpversion() . "\n";
		global $wpdb;
		echo 'DB Version = ' . $wpdb->db_version() . "\n";
		echo "\n" . serialize($PluginBuddyrotatingtext->_options);
		echo '</textarea>';
		?>

	</div>

