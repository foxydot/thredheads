<?php
// Needed for fancy boxes...
wp_enqueue_style('dashboard');
wp_print_styles('dashboard');
wp_enqueue_script('dashboard');
wp_print_scripts('dashboard');
// Load scripts and CSS used on this page.
$this->admin_scripts();



// If they clicked the button to reset plugin defaults...
if (!empty($_POST['reset_defaults'])) {
	$this->_options = $this->_parent->_defaults;
	$this->_parent->save();
	$this->_parent->alert( __( 'Plugin settings have been reset to defaults.', 'it-l10n-loopbuddy' ) );
}
?>

<div class="wrap">
	<div class="postbox-container" style="width:70%;">
		<h2><img src="<?php echo $this->_pluginURL; ?>/images/loopbuddy_rings.png" style="vertical-align: -4px;"><?php printf( __( "Getting Started with %s v%s", 'it-l10n-loopbuddy' ), $this->_parent->_name, $this->_parent->_version ); ?></h2>
		
		
		
		<p>
			<img src="<?php echo $this->_pluginURL; ?>/images/lightbulb.png" style="vertical-align: -3px;" /> <?php printf( __( "Hover over question marks %s for textual help or click the play icons %s for popup video tutorials", 'it-l10n-loopbuddy' ), $this->tip( __( 'Question mark icons are your friends! They will help give you tips and detailed explanations.', 'it-l10n-loopbuddy' ), __( 'Helpful Question Marks', 'it-l10n-loopbuddy' ), false ), $this->video( 'Q6hzhW693SU', __( 'Click this icon to pop up a video tutorial without having to leave the page.', 'it-l10n-loopbuddy' ), false ) ); ?>
		</p>
		
		<p><br />
			<ol>
				<li><?php esc_html_e( 'Create your loop by configuring its two parts:', 'it-l10n-loopbuddy' ); ?><br />
					<ul style="list-style: disc; margin-top: 7px; margin-left: 16px;">
						<li><?php printf( __( 'Use the <a href="%s">Layout Editor</a> to set up how your loop looks.', 'it-l10n-loopbuddy' ), $this->_selfLink . '-layouts' ); ?><?php $this->video( 'Q6hzhW693SU', __( 'Introduction to the Layout Editor', 'it-l10n-loopbuddy' ) ); ?></li>
						<li><?php printf( __( 'Use the <a href="%s">Query Editor</a> to determine what posts are displayed.', 'it-l10n-loopbuddy' ), $this->_selfLink . '-queries' ); ?><?php $this->video( 'Wq08f4GnsGc', __( 'Introduction to the Query Editor', 'it-l10n-loopbuddy' ) ); ?></li>
					</ul><br />
				<li><?php esc_html_e( 'Display your new loop using one or more of four methods:
', 'it-l10n-loopbuddy' ); ?>					<ul style="list-style: disc; margin-top: 7px; margin-left: 16px;">
						<li><?php printf( __( 'Override the default loop for every post and/or page of the site in the <a href="%s">Settings</a>.', 'it-l10n-loopbuddy' ), $this->_selfLink . '-queries' ); ?><?php $this->video( 'TkBtc85-rY4', __( 'Introduction to the Settings Page', 'it-l10n-loopbuddy' ) ); ?></li>
						<li><?php printf( __( 'Override the default loop for an individual post/page by selecting the loop in the post/page editor %s.', 'it-l10n-loopbuddy' ),  $this->video( 'v689PhVNw_8', __( 'Override the default loop for a post/page', 'it-l10n-loopbuddy' ), false )  ); ?></li>
						<li><?php printf( __( 'Display in a sidebar with a <a href="%s">widget</a> %s.', 'it-l10n-loopbuddy' ), esc_url( admin_url( 'widgets.php' ) ), $this->video( 'vLlLzeneiHc', __( 'How to use a Widget', 'it-l10n-loopbuddy' ), false ) ); ?></li>
						<li><?php printf( __( 'Display in a post with a shortcode %s.', 'it-l10n-loopbuddy' ), $this->video( 'vsYY9PMkTXQ', __( 'How to use a Shortcode', 'it-l10n-loopbuddy' ), false ) ); ?></li>
					</ul>
			</ol>
		</p>
		
		
		
		
		
		<br />
		<h3><?php esc_html_e( 'Version History', 'it-l10n-loopbuddy' ); ?></h3>
		<textarea rows="7" cols="70"><?php readfile( $this->_parent->_pluginPath . '/history.txt' ); ?></textarea>
		<br /><br />
		<script type="text/javascript">
			jQuery(document).ready(function() {
				jQuery("#pluginbuddy_debugtoggle").click(function() {
					jQuery("#pluginbuddy_debugtoggle_div").slideToggle();
				});
			});
		</script>
		
		<a id="pluginbuddy_debugtoggle" class="button secondary-button"><?php esc_html_e( 'Debugging Information', 'it-l10n-loopbuddy' ); ?></a>
		<div id="pluginbuddy_debugtoggle_div" style="display: none;">
			<h3><?php esc_html_e( 'Debugging Information', 'it-l10n-loopbuddy' ); ?></h3>
			<?php
			echo '<textarea rows="7" cols="65">';
			echo 'Plugin Version = '.$this->_name.' '.$this->_parent->_version.' ('.$this->_parent->_var.')'."\n";
			echo 'WordPress Version = '.get_bloginfo("version")."\n";
			echo 'PHP Version = '.phpversion()."\n";
			global $wpdb;
			echo 'DB Version = '.$wpdb->db_version()."\n";
			echo "\n".serialize($this->_options);
			echo '</textarea>';
			?>
			<p>
			<form method="post" action="<?php echo $this->_selfLink; ?>">
				<input type="hidden" name="reset_defaults" value="true" />
				<input type="submit" name="submit" value="<?php esc_attr_e( 'Reset Plugin Settings & Defaults', 'it-l10n-loopbuddy' ); ?>" id="reset_defaults" class="button secondary-button" onclick="if ( !confirm('<?php esc_attr_e( 'WARNING: This will reset all settings associated with this plugin to their defaults. Are you sure you want to do this?', 'it-l10n-loopbuddy' ); ?>') ) { return false; }" />
			</form>
			</p>
		</div>
		<br /><br /><br />
		<a href="http://pluginbuddy.com" style="text-decoration: none;"><img src="<?php echo $this->_pluginURL; ?>/images/pluginbuddy.png" style="vertical-align: -3px;" /> PluginBuddy.com</a><br /><br />
	</div>
	<div class="postbox-container" style="width:20%; margin-top: 35px; margin-left: 15px;">
		<div class="metabox-holder">
			<div class="meta-box-sortables">
				
				<div id="breadcrumbslike" class="postbox">
					<div class="handlediv" title="<?php esc_attr_e( 'Click to toggle', 'it-l10n-loopbuddy' ); ?>"><br /></div>
					<h3 class="hndle"><span><?php esc_html_e( 'Things to do...', 'it-l10n-loopbuddy' ); ?></span></h3>
					<div class="inside">
						<ul class="pluginbuddy-nodecor">
							<li>- <a href="http://twitter.com/home?status=<?php echo urlencode('Check out this awesome plugin, ' . $this->_parent->_name . '! ' . $this->_parent->_url . ' @pluginbuddy'); ?>" title="<?php esc_attr_e( 'Share on Twitter', 'it-l10n-loopbuddy' ); ?>" onClick="window.open(jQuery(this).attr('href'),'ithemes_popup','toolbar=0,status=0,width=820,height=500,scrollbars=1'); return false;"><?php esc_html_e( 'Tweet about this plugin.', 'it-l10n-loopbuddy' ); ?></a></li>
							<li>- <a href="http://pluginbuddy.com/purchase/"><?php esc_html_e( 'Check out PluginBuddy plugins.', 'it-l10n-loopbuddy' ); ?></a></li>
							<li>- <a href="http://pluginbuddy.com/purchase/"><?php esc_html_e( 'Check out iThemes themes.', 'it-l10n-loopbuddy' ); ?></a></li>
							<li>- <a href="http://secure.hostgator.com/cgi-bin/affiliates/clickthru.cgi?id=ithemes"><?php esc_html_e( 'Get HostGator web hosting.', 'it-l10n-loopbuddy' ); ?></a></li>
							<li>- <a href="http://pluginbuddy.com/purchase/backupbuddy/"><?php esc_html_e( 'Backup with BackupBuddy.', 'it-l10n-loopbuddy' ); ?></a></li>
						</ul>
					</div>
				</div>

				<div id="breadcrumsnews" class="postbox">
					<div class="handlediv" title="Click to toggle"><br /></div>
					<h3 class="hndle"><span><?php esc_html_e( 'Latest news from PluginBuddy', 'it-l10n-loopbuddy' ); ?></span></h3>
					<div class="inside">
						<p style="font-weight: bold;"><?php esc_html_e( 'PluginBuddy.com', 'it-l10n-loopbuddy' ); ?></p>
						<?php $this->get_feed( 'http://pluginbuddy.com/feed/', 5 );  ?>
						<p style="font-weight: bold;"><?php esc_html_e( 'Twitter @pluginbuddy', 'it-l10n-loopbuddy' ); ?></p>
						<?php
						$twit_append = '<li>&nbsp;</li>';
						$twit_append .= '<li><img src="'.$this->_pluginURL.'/images/twitter.png" style="vertical-align: -3px;" /> <a href="http://twitter.com/pluginbuddy/">' . __( 'Follow @pluginbuddy on Twitter.', 'it-l10n-loopbuddy' ) . '</a></li>';
						$twit_append .= '<li><img src="'.$this->_pluginURL.'/images/feed.png" style="vertical-align: -3px;" /> <a href="http://pluginbuddy.com/feed/">' . __( 'Subscribe to RSS news feed.', 'it-l10n-loopbuddy' ) . '</a></li>';
						$twit_append .= '<li><img src="'.$this->_pluginURL.'/images/email.png" style="vertical-align: -3px;" /> <a href="http://pluginbuddy.com/subscribe/">' . __( 'Subscribe to Email Newsletter.', 'it-l10n-loopbuddy' ) . '</a></li>';
						$this->get_feed( 'http://twitter.com/statuses/user_timeline/108700480.rss', 5, $twit_append, 'pluginbuddy: ' );
						?>
					</div>
				</div>
				
				<div id="breadcrumbssupport" class="postbox">
					<div class="handlediv" title="<?php esc_attr_e( 'Click to toggle', 'it-l10n-loopbuddy' ); ?>"><br /></div>
					<h3 class="hndle"><span><?php esc_html_e( 'Need support?', 'it-l10n-loopbuddy' ); ?></span></h3>
					<div class="inside">
						<p><?php printf( __( 'See our <a href="%s">tutorials & videos</a> or visit our <a href="%s">support forum</a> for additional information and help.', 'it-l10n-loopbuddy' ), 'http://pluginbuddy.com/tutorials/', 'http://pluginbuddy.com/support' ); ?></p>
					</div>
				</div>
				
			</div>
		</div>
	</div>
</div>