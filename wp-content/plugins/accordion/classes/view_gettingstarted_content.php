<?php
global $pluginbuddy_accordion;

// If they clicked the button to reset plugin defaults...
if (!empty($_POST['accordion_reset_defaults'])) {
	$this->_options = $pluginbuddy_accordion->_defaults;
	$pluginbuddy_accordion->save();
	$this->alert( 'Plugin settings have been reset to defaults.' );
}
?>

<br />
<?php esc_html_e( 'Accordion lets you group several items together in Accordion format (either horizontal or vertical)
', 'it-l10n-accordion' ); ?><br /><br />

	<ul>
		<li><h3>Step 1</h3></li>
		<li>
			<ol>
				<li><?php printf( __( 'Click the <a href="%s">Accordion</a> link in the DisplayBuddy menu to begin creating an Accordion.', 'it-l10n-accordion' ), $pluginbuddy_accordion->_selfLink . '-settings' ); ?></li>
				<li><?php esc_html_e( 'After you have created an Accordion, you can select an Accordion to edit or add Accordion items.', 'it-l10n-accordion' ); ?></li>
				<li><?php esc_html_e( 'Once you have created several Accordion items and configured your Accordion, you are ready to display it', 'it-l10n-accordion' ); ?></li>
			</ol>
		</li>
	
		<li><h3><?php esc_html_e( 'Step 2', 'it-l10n-accordion' ); ?></h3></li>
		<li><?php esc_html_e( 'Now that you have created an Accordion, it\'s time to display it on your site.', 'it-l10n-accordion' ); ?></li>
		<li>
			<ol>
				<li><?php esc_html_e( 'One way you can display a Accordion on your site is by going to "Widgets" under appearance and add an Accordion into your widget areas.', 'it-l10n-accordion' ); ?></li>
				<li><?php printf( __( 'Another way to display a Accordion group on your site is by getting the shortcode snippet from the <a href="%s">Accordion</a> groups table and adding that shortcode to the content anywhere on your site.', 'it-l10n-accordion' ), $pluginbuddy_accordion->_selfLink . '-settings' ); ?></li>
			</ol>
		</li>
	</ul>

<h3><?php esc_html_e( 'Shortcode', 'it-l10n-accordion' ); ?></h3>
<?php esc_html_e( 'Display your Accordion in style by using the shortcode [pb_accordion id="#"] or by adding a widget.', 'it-l10n-accordion' ); ?>
<br /><br />



<h3>Version History</h3>
<textarea rows="7" cols="65"><?php readfile( $pluginbuddy_accordion->_pluginPath . '/history.txt' ); ?></textarea>
<br /><br />
<script type="text/javascript">
	jQuery(document).ready(function() {
		jQuery("#pluginbuddy_accordion_debugtoggle").click(function() {
			jQuery("#pluginbuddy_accordion_debugtoggle_div").slideToggle();
		});
	});
</script>

<a id="pluginbuddy_accordion_debugtoggle" class="button secondary-button"><?php esc_html_e( 'Debugging Information', 'it-l10n-accordion' ); ?></a>
<div id="pluginbuddy_accordion_debugtoggle_div" style="display: none;">
	<h3><?php esc_html_e( 'Debugging Information', 'it-l10n-accordion' ); ?></h3>
	<?php
	echo '<textarea rows="7" cols="65">';
	echo 'Plugin Version = '.$pluginbuddy_accordion->_name.' '.$pluginbuddy_accordion->_version.' ('.$pluginbuddy_accordion->_var.')'."\n";
	echo 'WordPress Version = '.get_bloginfo("version")."\n";
	echo 'PHP Version = '.phpversion()."\n";
	global $wpdb;
	echo 'DB Version = '.$wpdb->db_version()."\n";
	echo "\n".serialize($pluginbuddy_accordion->_options);
	echo '</textarea>';
	?>
	<p>
	<form method="post" action="<?php
	admin_url( "admin.php?page={$this->_parent->_series}-settings" )
	?>">
		<input type="hidden" name="accordion_reset_defaults" value="true" />
		<input type="submit" name="submit" value="<?php esc_attr_e( 'Reset Plugin Settings & Defaults', 'it-l10n-accordion' ); ?>" id="reset_defaults" class="button secondary-button" onclick="if ( !confirm('<?php esc_attr_e( 'WARNING: This will reset all settings associated with this plugin to their defaults. Are you sure you want to do this?', 'it-l10n-accordion' ); ?>') ) { return false; }" />
	</form>
	</p>
</div>