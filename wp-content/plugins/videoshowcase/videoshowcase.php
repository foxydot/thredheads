<?php
/**
 *
 * Plugin Name: Video Showcase
 * Plugin URI: http://pluginbuddy.com/purchase/displaybuddy/
 * Description: DisplayBuddy Series - Embed thickbox videos with image links.
 * Version: 1.1.21
 * Author: The PluginBuddy Team
 * Author URI: http://pluginbuddy.com/
 *
 * Installation:
 * 
 * 1. Download and unzip the latest release zip file.
 * 2. If you use the WordPress plugin uploader to install this plugin skip to step 4.
 * 3. Upload the entire plugin directory to your `/wp-content/plugins/` directory.
 * 4. Activate the plugin through the 'Plugins' menu in WordPress Administration.
 * 
 * Usage:
 * 
 * 1. Navigate to the DisplayBuddy menu in the Wordpress Administration Panel.
 * 2. Go to the Video Showcase section.
 * 3. Create a group.
 * 4. Click on a group to change group settings and add videos.
 * 5. Display Video Showcase by adding to widget areas or use shortcode.
 *
 */


if (!class_exists("PluginBuddyVideoShowcase")) {
	class PluginBuddyVideoShowcase {
		var $_version = '1.1.21';
		var $_updater = '1.0.7';
		
		var $_var = 'pluginbuddy-videoshowcase'; // Format: pluginbuddy-pluginnamehere. All lowecase, no dashes.
		var $_name = 'Video Showcase'; // front end plugin name.
		var $_series = 'DisplayBuddy'; // Series name if applicable.
		var $url = 'http://pluginbuddy.com/purchase/displaybuddy/';
		var $_timeformat = '%b %e, %Y, %l:%i%p';	// Mysql time format.
		var $_timestamp = 'M j, Y, g:iA';		// PHP timestamp format.
		var $_defaults = array(
			'groups'	=>	array(),
			'access'	=>	'activate_plugins',
		);
		var $_groupdefaults = array(
			'videos'		=>	array(),
			'order'			=>	array(),
			'background-color'	=>	'FFFFFF',
			'transparent'		=>	'0',
			'tlink'			=>	'none',
			'related'		=>	'false'
		);
		var $_instance = '';
		
		// Default constructor. This is run when the plugin first runs.
		function PluginBuddyVideoShowcase() {
			$this->_pluginPath = dirname( __FILE__ );
			$this->_pluginRelativePath = ltrim( str_replace( '\\', '/', str_replace( rtrim( ABSPATH, '\\\/' ), '', $this->_pluginPath ) ), '\\\/' );
			$this->_pluginURL = site_url() . '/' . $this->_pluginRelativePath;
			if ( isset( $_SERVER['HTTPS'] ) && $_SERVER['HTTPS'] == 'on' ) { $this->_pluginURL = str_replace( 'http://', 'https://', $this->_pluginURL ); }
			$this->_selfLink = array_shift( explode( '?', $_SERVER['REQUEST_URI'] ) ) . '?page=' . $this->_var;
			
			require_once( dirname( __FILE__ ) . '/classes/widget.php' );
			// load image group sizes
			$this->load();
			$gpath = $this->_options['groups'];
			foreach($this->_options['groups'] as $id => $gar) {
				add_image_size('pb_videoshowcase_' . $gpath[$id]['width'] . 'x' . $gpath[$id]['height'], $gpath[$id]['width'], $gpath[$id]['height'], true);
			}
			add_image_size('default_thumb', 120, 90, true);
			
			if ( is_admin() ) { // Runs when an admin is in the dashboard.
				add_action( 'init', array( &$this, 'upgrader_register' ), 50 );
				add_action( 'init', array( &$this, 'upgrader_select' ), 100 );
				add_action( 'init', array( &$this, 'upgrader_instantiate' ), 101 );
				require_once( $this->_pluginPath . '/classes/admin.php' );
				require_once( $this->_pluginPath . '/lib/updater/updater.php' );
				register_activation_hook( $this->_pluginPath, array( &$this, 'activate' ) ); // Run some code when plugin is activated in dashboard.
				// Require custom media uploader
				require_once( $this->_pluginPath . '/lib/medialibrary/medialibrary.php' );
				$this->_medialibrary = new PluginBuddyMediaLibrary( $this,
					array(
						'select_button_text'			=>			'Select this Image',
						'tabs'					=>			array( 'pb_uploader' => 'Upload Images to Media Library', 'library' => 'Select from Media Library' ),
						'show_input-image_alt_text'		=>			false,
						'show_input-url'			=>			false,
						'show_input-image_align'		=>			false,
						'show_input-image_size'			=>			false,
						'show_input-description'		=>			true,
						'custom_help-caption'			=>			'Overlaying text to be displayed if captions are enabled.',
						'custom_help-description'		=>			'Optional URL for this image to link to.',
						'custom_label-description'		=>			'Link URL',
						'use_textarea-caption'			=>			true,
						'use_textarea-description'		=>			false,
					)
				);
			}
			else { // Runs when in non-dashboard parts of the site.
				add_shortcode( 'pb_videoshowcase', array( &$this, 'shortcode' ) );
				add_action( $this->_var . '-widget', array( &$this, 'widget' ), 10, 2 ); // Add action to run widget function.
				add_action('wp_print_scripts', array( &$this, 'vsc_scripts' ) );
				add_action('wp_print_styles', array( &$this, 'vsc_styles' ) );
			}
			add_action('wp_ajax_vscdoom', array(&$this, 'vscdoom') );
			add_action('wp_ajax_nopriv_vscdoom', array(&$this, 'vscdoom') );
		}
		
		// FUNCTIONS TO CALL FRONT END SCRIPTS & STYLES
		function vsc_scripts() {
			wp_enqueue_script('jquery');
			wp_enqueue_script('videoshowcase_script', $this->_pluginURL . "/js/jquery.pbvideosc.js");
		}
		function vsc_styles() {
			wp_enqueue_style('videoshowcase_style', $this->_pluginURL . "/css/vsc.css");
		}
		
		
		/**
		 *	alert()
		 *
		 *	Displays a message to the user at the top of the page when in the dashboard.
		 *
		 *	$message		string		Message you want to display to the user.
		 *	$error			boolean		OPTIONAL! true indicates this alert is an error and displays as red. Default: false
		 *	$error_code		int			OPTIONAL! Error code number to use in linking in the wiki for easy reference.
		 */
		function alert( $message, $error = false, $error_code = '' ) {
			echo '<div id="message" class="';
			if ( $error == false ) {
				echo 'updated fade';
			} else {
				echo 'error';
			}
			if ( $error_code != '' ) {
				$message .= '<p><a href="http://ithemes.com/codex/page/' . $this->_name . ':_Error_Codes#' . $error_code . '" target="_new"><i>' . $this->_name . ' Error Code ' . $error_code . ' - Click for more details.</i></a></p>';
				$this->log( $message . ' Error Code: ' . $error_code, true );
			}
			echo '"><p><strong>'.$message.'</strong></p></div>';
		}
		
		/**
		 * TOOLTIP FUNCTION
		 * Displays a message to the user when they hover over the question mark.
		**/
		function tip( $message, $title = '', $echo_tip = true ) {
			$tip = ' <a class="pluginbuddy_tip" title="' . $title . ' - ' . $message . '"><img src="' . $this->_pluginURL . '/images/pluginbuddy_tip.png" alt="(?)" /></a>';
			if ( $echo_tip === true ) {
				echo $tip;
			} else {
				return $tip;
			}
		}
		
		
		/**
		 * activate()
		 *
		 * Run on plugin activation. Useful for setting up initial stuff.
		 *
		 */
		function activate() {
		}
		

		// FRONT END DISPLAY //////////////////////
		
		function shortcode($atts) {
			$group = $atts['group'];
			
			if(!isset($atts['max'])) {
				$max = 'all';
			}
			else {
				$max = $atts['max'];
			}
			
			if(!isset($atts['align'])) {
				$align = 'center';
			}
			else {
				$align = $atts['align'];
			}
			
			if(!isset($atts['order'])) {
				$order = 'ordered';
			}
			else {
				$order = 'random';
			}
			
			if(!isset($atts['theme'])) {
				$theme = 'default';
			}
			else {
				$theme = $atts['theme'];
			}
			
			return $this->_display_showbox($group, $align, $max, $order, $theme);
		}
		
		function widget($instance) {
			$group = $instance['group'];
			$align = $instance['align'];
			$max = $instance['max'];
			$order = $instance['order'];
			$theme = $instance['theme'];
			
			echo $this->_display_showbox($group, $align, $max, $order, $theme);
		}
		
		
		function _display_showbox($group, $align, $max, $order, $theme) {
			$this->load();
			
			$this->_instance++;

			$gpath = $this->_options['groups'][$group];
			if($max == 'all') {
				$max = count($gpath['videos']);
			}
			elseif ( $max > (count($gpath['videos'])) ) {
				$max = count($gpath['videos']);		
			}
			else {
				$max = $max;
			}
			
			$return = '';
			
			// ORDER FILTER
			if ( $order === 'random' ) {
				$preorder = (array)(array_rand((array)$gpath['order'], $max));
				for($i=0; $i<$max; $i++){
					$neworder[$i] = $gpath['order'][$preorder[$i]];
				}
				shuffle($neworder);
			}
			else {
				$neworder = array_values((array)$gpath['order']);
			}
			
			// HORIZONTAL ALIGNMENT
			$alignment = '';
			if ( $align !== 'none' ) {
				$alignment = ' style="text-align:' . $align . ';"';
			}
			
			// START CONTAINER
			$return .= '<div id="videoshowcaseid-' . $this->_instance . '" class="videoshowcase"' . $alignment . '>';
				
				// TEST CORRECT CALL WITH MAX ADDED IN
				for($i=0; $i<$max; $i++){
					$vidnum = $neworder[$i];
					$video = $gpath['videos'][$vidnum];
					$imagedata = wp_get_attachment_image_src( $video['vimage'], 'pb_videoshowcase_' . $gpath['width'] . 'x' . $gpath['height'] );
					$tvert = 'top';
					if (isset($gpath['tlink'])) {
						if($gpath['tlink'] == 'above') {
							$tvert = 'bottom';
						}
						elseif($gpath['tlink'] == 'both') {
							$tvert = 'middle';
						}
						else {
							$tvert = 'top';
						}
					}
					
					$return .= '<div id="vsc-video' . $this->_instance . '-' . $i . '" class="vsc-video-container" style="width:' . $gpath['width'] . 'px; vertical-align: ' . $tvert . '">';
					if (isset($gpath['tlink'])) {
						if(($gpath['tlink'] == 'above') || ($gpath['tlink'] == 'both')) {
							if ($video['vsourc'] == 'custom') {
								$return .= '<a href="' . admin_url('admin-ajax.php') . '?action=vscdoom&movie=' . $video['vurl'] . '" rel="' . $this->_var . "-" . $this->_instance . '" title="' . stripslashes($video['vtitle']) . '">' . stripslashes($video['vtitle']) . '</a><br/>';
							} else {
								
								$return .= '<a href="' . $video['vurl'] . '" rel="' . $this->_var . "-" . $this->_instance . '" title="' . stripslashes($video['vtitle']) . '">' . stripslashes($video['vtitle']) . '</a><br/>';
							}
						}
					}
					if ($video['vsourc'] == 'custom' || $video[ 'vsourc' ] == 'youtube') {
						$return .= '<a href="' . admin_url('admin-ajax.php') . '?action=vscdoom&movie=' . $video['vurl'] . '" rel="' . $this->_var . "-" . $this->_instance . '" title="' . stripslashes($video['vtitle']) . '" width="' . $gpath['width'] . 'px" height="' . $gpath['height'] . 'px" ><img src="' . $imagedata['0'] . '" alt="' . stripslashes($video['vtitle']) . '" /></a>';
					}
					else {
						

						$video_url = preg_replace( '/http:\/\/(.)+\//i', 'http://player.vimeo.com/video/', $video['vurl'] );
						
						$return .= '<a href="' . $video_url . '" rel="' . $this->_var . "-" . $this->_instance . '" title="' . stripslashes($video['vtitle']) . '" width="' . $gpath['width'] . 'px" height="' . $gpath['height'] . 'px" ><img src="' . $imagedata['0'] . '" alt="' . stripslashes($video['vtitle']) . '" /></a>';
					}
					if (isset($gpath['tlink'])) {
						if(($gpath['tlink'] == 'below') || ($gpath['tlink'] == 'both')) {
							if ($video['vsourc'] == 'custom') {
								$return .= '<br/><a href="' . admin_url('admin-ajax.php') . '?action=vscdoom&movie=' . $video['vurl'] . '" rel="' . $this->_var . "-" . $this->_instance . '" title="' . stripslashes($video['vtitle']) . '">' . stripslashes($video['vtitle']) . '</a>';
							} else {
								$return .= '<br/><a href="' . $video['vurl'] . '" rel="' . $this->_var . "-" . $this->_instance . '" title="' . stripslashes($video['vtitle']) . '">' . stripslashes($video['vtitle']) . '</a>';
							}
						}
					}
					$return .= '</div>';
				}
				
			$return .= '</div>';
			
			// INSTANCE JAVASCRIPT VARIABLES
			$vsctheme = "'" . $theme . "'";
			$marker = "'" . $this->_var . "-" . $this->_instance . "'";
			$related = "'false'";
			if( isset($gpath['related']) ) {
				$related = "'" . $gpath['related'] . "'";
			}
			$pluginpath = "'" . $this->_pluginURL . "'";
			
			$return .= '
				<script type="text/javascript" charset="utf-8">
					jQuery(document).ready(function(){
						jQuery("a[rel^=' . $marker . ']").pbvideosc({
							theme: ' . $vsctheme . ',
							norelated: ' . $related . ',
							pluginpath: ' . $pluginpath . '
						});
					});
				</script>
			';

			
			return $return;
			
		}
		
		
		// Ajax custom video iframe
		function vscdoom() {
			?>
			<html>
			<body style="padding:0,margin:0">
			<?php
			$pluginpath = "'" . $this->_pluginURL . "'";
			$test = '<script type="text/javascript" src="' . $this->_pluginURL . '/js/swfobject.js"></script>
				<script type="text/javascript">
					var flashvars = {
						src: "' . $_GET['movie'] . '",
						autostart: "true",
						themeColor: "0395d3",
						mode: "sidebyside",
						scaleMode: "fit",
						frameColor: "333333",
						fontColor: "cccccc",
						link: "",embed: ""
					};
					var params = {allowFullScreen: "true"};
					var attributes = {id: "myPlayer",name: "myPlayer"};
					swfobject.embedSWF("' . $this->_pluginURL . '/js/AkamaiFlashPlayer.swf","myPlayerGoesHere","' . $_GET['width'] . '","' . $_GET['height'] . '","9.0.0","' . $this->_pluginURL . '/js/expressInstall.swf",flashvars,params,attributes);
				</script>
				<div id="myPlayerGoesHere">
					<a href="http://www.adobe.com/go/getflashplayer"><img src="http://www.adobe.com/images/shared/download_buttons/get_flash_player.gif" alt="Get Adobe Flash player" /></a>
				</div>';
			echo $test;
			?>
			</body>
			</html>
			<?php
			die();
		}
		

		// OPTIONS STORAGE //////////////////////
		
		
		function save() {
			add_option($this->_var, $this->_options, '', 'no'); // 'No' prevents autoload if we wont always need the data loaded.
			update_option($this->_var, $this->_options);
			return true;
		}
		
		
		function load() {
			$this->_options=get_option($this->_var);
			$options = array_merge( $this->_defaults, (array)$this->_options );

			if ( $options !== $this->_options ) {
				// Defaults existed that werent already in the options so we need to update their settings to include some new options.
				$this->_options = $options;
				$this->save();
			}

			return true;
		}
		//Register the updater version
		function upgrader_register() {
			$GLOBALS['pb_classes_upgrade_registration_list'][$this->_var] = $this->_updater;
		} //end register_upgrader
		//Select the greatest version
		function upgrader_select() {
			if ( !isset( $GLOBALS[ 'pb_classes_upgrade_registration_list' ] ) ) {
				//Fallback - Just include this class
				require_once( $this->_pluginPath . '/lib/updater/updater.php' );
				return;
			}
			//Go through each global and find the highest updater version and the plugin slug
			$updater_version = 0;
			$plugin_var = '';
			foreach ( $GLOBALS[ 'pb_classes_upgrade_registration_list' ] as $var => $version) {
				if ( version_compare( $version, $updater_version, '>=' ) ) {
					$updater_version = $version;
					$plugin_var = $var;
				}
			}
			//If the slugs match, load this version
			if ( $this->_var == $plugin_var ) {
				require_once( $this->_pluginPath . '/lib/updater/updater.php' );
			}
		} //end upgrader_select
		function upgrader_instantiate() {
			
			$pb_product = strtolower( $this->_var );
			$pb_product = str_replace( 'ithemes-', '', $pb_product );
			$pb_product = str_replace( 'pluginbuddy-', '', $pb_product );
			$pb_product = str_replace( 'pluginbuddy_', '', $pb_product );
			$pb_product = str_replace( 'pb_thumbsup', '', $pb_product );
			
			$args = array(
				'parent' => $this, 
				'remote_url' => 'http://updater2.ithemes.com/index.php',
				'version' => $this->_version,
				'plugin_slug' => $this->_var,
				'plugin_path' => plugin_basename( __FILE__ ),
				'plugin_url' => $this->_pluginURL,
				'product' => $pb_product,
				'time' => 43200,
				'return_format' => 'json',
				'method' => 'POST',
				'upgrade_action' => 'check' );
			$this->_pluginbuddy_upgrader = new iThemesPluginUpgrade( $args );

		} //end upgrader_instantiate
		
		
	} // End class

	$PluginBuddyVideoShowcase = new PluginBuddyVideoShowcase(); // Create instance
	//require_once( dirname( __FILE__ ) . '/classes/widget.php');
}

// Custom image resize code by iThemes.com Dustin Bolton - Iteration 20 - 3/24/11
if ( !function_exists( 'ithemes_filter_image_downsize' ) ) {
	add_filter( 'image_downsize', 'ithemes_filter_image_downsize', 10, 3 ); // Latch in when a custom image size is called.
	add_filter( 'intermediate_image_sizes_advanced', 'ithemes_filter_image_downsize_blockextra', 10, 3 ); // Custom image size blocker to block generation of thumbs for sizes other sizes except when called.
	function ithemes_filter_image_downsize( $result, $id, $size ) {
		global $_ithemes_temp_downsize_size;
		if ( is_array( $size ) ) { // Dont bother with non-named sizes. Let them proceed normally. We need to set something to block the blocker though.
			$_ithemes_temp_downsize_size = 'array_size';
			return;
		}
		
		// Store current meta information and size data.
		global $_ithemes_temp_downsize_meta;
		$_ithemes_temp_downsize_size = $size;
		$_ithemes_temp_downsize_meta = wp_get_attachment_metadata( $id );
		
		if ( !is_array( $_ithemes_temp_downsize_meta ) ) { return $result; }
		if ( !is_array( $size ) && !empty( $_ithemes_temp_downsize_meta['sizes'][$size] ) ) {
			$data = $_ithemes_temp_downsize_meta['sizes'][$size];
			// Some handling if the size defined for this size name has changed.
			global $_wp_additional_image_sizes;
			if ( empty( $_wp_additional_image_sizes[$size] ) ) { // Not a custom size so return data as is.
				$img_url = wp_get_attachment_url( $id );
				$img_url = path_join( dirname( $img_url ), $data['file'] );
				return array( $img_url, $data['width'], $data['height'], true );
			} else { // Custom size so only return if current image file dimensions match the defined ones.
				$img_url = wp_get_attachment_url( $id );
				$img_url = path_join( dirname( $img_url ), $data['file'] );
				return array( $img_url, $data['width'], $data['height'], true );
			}
		}
		
		require_once( ABSPATH . '/wp-admin/includes/image.php' );
		$uploads = wp_upload_dir();
		if ( !is_array( $uploads ) || ( false !== $uploads['error'] ) ) { return $result; }
		$file_path = "{$uploads['basedir']}/{$_ithemes_temp_downsize_meta['file']}";
		
		// Image is resized within the function in the following line.
		$temp_meta_information = wp_generate_attachment_metadata( $id, $file_path ); // triggers filter_image_downsize_blockextra() function via filter within. generate images. returns new meta data for image (only includes the just-generated image size).
		$meta_information = $_ithemes_temp_downsize_meta; // Get the old original meta information.
		
		if ( !empty( $temp_meta_information['sizes'][$_ithemes_temp_downsize_size] ) ) { // This named size returned size dimensions in the size array key so copy it.
			$meta_information['sizes'][$_ithemes_temp_downsize_size] = $temp_meta_information['sizes'][$_ithemes_temp_downsize_size]; // Merge old meta back in.
			wp_update_attachment_metadata( $id, $meta_information ); // Update image meta data.
		}
		
		unset( $_ithemes_temp_downsize_size ); // Cleanup.
		unset( $_ithemes_temp_downsize_meta );
		
		return $result;
	}
	/* Prevents image resizer from resizing ALL images; just the currently requested size. */
	function ithemes_filter_image_downsize_blockextra( $sizes ) {
		//return $sizes;
		global $_ithemes_temp_downsize_size;
		if ( empty( $_ithemes_temp_downsize_size ) || ( $_ithemes_temp_downsize_size == 'array_size' ) ) { // Dont bother with non-named sizes. Let them proceed normally.
			return $sizes;
		}
		if ( !empty( $sizes[$_ithemes_temp_downsize_size] ) ) { // unavailable size so don't set.
			$sizes = array( $_ithemes_temp_downsize_size => $sizes[$_ithemes_temp_downsize_size] ); // Strip out all extra meta data so only the requested size will be generated.
		}
		return $sizes;
	}
}
?>
