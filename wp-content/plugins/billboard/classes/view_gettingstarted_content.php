<?php
global $iThemesBillboard;
?>

<p>
	<ol>
		<li>Create a new group on the Billboard page for each collection of items.</li>
		<li>Select 'Add Entries' next to the group to items and configure settings.</li>
		<li>Use the Wordpress widget administrator to place the widget in a widget space or sidebar.</li>
	</ol>
</p>


<h3>Version History</h3>
<textarea rows="7" cols="65"><?php readfile( $iThemesBillboard->_pluginPath . '/history.txt' ); ?></textarea>
<br /><br />
<script type="text/javascript">
	jQuery(document).ready(function() {
		jQuery("#pluginbuddy_billboard_debugtoggle").click(function() {
			jQuery("#pluginbuddy_billboard_debugtoggle_div").slideToggle();
		});
	});
</script>

<a id="pluginbuddy_billboard_debugtoggle" class="button secondary-button">Debugging Information</a>
<div id="pluginbuddy_billboard_debugtoggle_div" style="display: none;">
	<h3>Debugging Information</h3>
	<?php
	echo '<textarea rows="7" cols="65">';
	echo 'Plugin Version = '.$iThemesBillboard->_name.' '.$iThemesBillboard->_version.' ('.$iThemesBillboard->_var.')'."\n";
	echo 'WordPress Version = '.get_bloginfo("version")."\n";
	echo 'PHP Version = '.phpversion()."\n";
	global $wpdb;
	echo 'DB Version = '.$wpdb->db_version()."\n";
	echo "\n".serialize($iThemesBillboard->_options);
	echo '</textarea>';
	?>
</div>