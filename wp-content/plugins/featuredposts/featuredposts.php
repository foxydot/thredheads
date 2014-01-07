<?php
/**
 *
 * Plugin Name: Featured Posts
 * Plugin URI: http://pluginbuddy.com/purchase/displaybuddy/
 * Description: DisplayBuddy Series - Display featured posts and images in a widget or shortcode.
 * Version: 2.0.6
 * Author: The PluginBuddy Team
 * Author URI: http://pluginbuddy.com/
 *
 * Installation:
 * 
 * 1. Download and unzip the latest release zip file.
 * 2. If you use the WordPress plugin uploader to install this plugin skip to step 4.
 * 3. Upload the entire featured directory to your `/wp-content/plugins/` directory.
 * 4. Activate the plugin through the 'Plugins' menu in WordPress Administration.
 * 
 * Usage:
 * 
 * 1. Navigate to the new DisplayBuddy menu in the Wordpress Administration Panel.
 * 2. Then click on the Featured Posts link and begin customizing your settings.
 * 3. To display featured posts using the shortcode [featuredposts] or a widget area.
 *
 */


if (!class_exists("PluginBuddyFeaturedPosts")) {
	class PluginBuddyFeaturedPosts {
		var $_version = '2.0.6';
		var $_updater = '1.0.7';
		
		var $_var = 'pluginbuddy-featuredposts';
		var $_series = 'DisplayBuddy';
		var $_url = 'http://pluginbuddy.com/';
		var $_name = 'Featured Posts';
		var $_timeformat = '%b %e, %Y, %l:%i%p';	// mysql time format
		var $_timestamp = 'M j, Y, g:iA';		// php timestamp format
		var $_defaults = array(
		'access'			=>	'activate_plugins',			
			'category'		=>	'',
			'post_type'		=>	'post',
			'layout'		=>	'default',
			'posts_count'		=>	'5',
			'autostart'		=>	'3000',
			'restart'		=>	'15000',
			'slidespeed'		=>	'300',
			'fadespeed'		=>	'200',
			'excerpt_length'	=>	'55',
			'excerpt_readmore'	=> 	'Read More',
			'layouts'		=>	array('default'	=> array(
								'width'			=>	'600',
								'height'		=>	'500',
								'image_width'		=>	'600',
								'image_height'		=>	'300',
							),
						),
		);
		var $instance_count = 0;
		
		// Default constructor. This is run when the plugin first runs.
		function PluginBuddyFeaturedPosts() {
			$this->_pluginPath = dirname( __FILE__ );
			$this->_pluginRelativePath = ltrim( str_replace( '\\', '/', str_replace( rtrim( ABSPATH, '\\\/' ), '', $this->_pluginPath ) ), '\\\/' );
			$this->_pluginURL = site_url() . '/' . $this->_pluginRelativePath;
			if ( isset( $_SERVER['HTTPS'] ) && $_SERVER['HTTPS'] == 'on' ) { $this->_pluginURL = str_replace( 'http://', 'https://', $this->_pluginURL ); }
			$this->_selfLink = array_shift( explode( '?', $_SERVER['REQUEST_URI'] ) ) . '?page=' . $this->_var;
			
			require_once( dirname( __FILE__ ) . '/classes/widget.php' );
			
			add_theme_support( 'post-thumbnails' ); // Add support for featured image on posts/pages/etc.
			$this->load();
			// set image resize width and height
			$inlayout = $this->_options['layouts'][$this->_options['layout']];
			add_image_size( 'pb_featuredposts' . $inlayout['image_width'] . 'x' . $inlayout['image_height'], $inlayout['image_width'], $inlayout['image_height'], true);
			
			if ( is_admin() ) { // Runs when an admin is in the dashboard.
				add_action( 'init', array( &$this, 'upgrader_register' ), 50 );
				add_action( 'init', array( &$this, 'upgrader_select' ), 100 );
				add_action( 'init', array( &$this, 'upgrader_instantiate' ), 101 );
				require_once( $this->_pluginPath . '/classes/admin.php');
				require_once( $this->_pluginPath . '/lib/updater/updater.php');
				register_activation_hook( __FILE__, array( &$this, 'activate' ) ); // Run some code when plugin is activated in dashboard.
			} else { // Runs when in non-dashboard parts of the site.
				//add_action( 'template_redirect', array( &$this, 'init_public') );
				
				wp_enqueue_script( 'jquery' );
				
				add_shortcode( 'featuredposts', array( &$this, 'shortcode' ) );
				
				add_action( $this->_var . '-widget', array( &$this, 'widget' ), 10, 2 ); // Add action to run widget function.
			}
		}
		function pb_excerpt_length($length) {
			return $this->_options['excerpt_length'];
		}
		function pb_excerpt_readmore($more) {
			global $post;
			return '<br /><a href="'. get_permalink($post->ID) . '">' . $this->_options['excerpt_readmore'] . '</a>';
		}
		
		/**
		 *	alert()
		 *
		 *	Displays a message to the user at the top of the page when in the dashboard.
		 *
		 *	$message		string		Message you want to display to the user.
		 *	$error			boolean		OPTIONAL! true indicates this alert is an error and displays as red. Default: false
		 *	$error_code		int		OPTIONAL! Error code number to use in linking in the wiki for easy reference.
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
			}
			echo '"><p><strong>'.$message.'</strong></p></div>';
		}
		
		
		/**
		 *	tip()
		 *
		 *	Displays a message to the user when they hover over the question mark. Gracefully falls back to normal tooltip.
		 *	HTML is supposed within tooltips.
		 *
		 *	$message		string		Actual message to show to user.
		 *	$title			string		Title of message to show to user. This is displayed at top of tip in bigger letters. Default is blank. (optional)
		 *	$echo_tip		boolean		Whether to echo the tip (default; true), or return the tip (false). (optional)
		 */
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
			$this->load();
			if ( !empty( $this->_options['width'] ) ) {
				$this->_options['layouts']['default']['width'] = $this->_options['width'];
				$this->_options['layouts']['default']['height'] = $this->_options['height'];
				
				$this->_options['layouts']['default']['image_width'] = $this->_options['image_width'];
				$this->_options['layouts']['default']['image_height'] = $this->_options['image_height'];
				
				unset( $this->_options['layouts']['default']['width'] );
				unset( $this->_options['layouts']['default']['height'] );
				unset( $this->_options['layouts']['default']['image_width'] );
				unset( $this->_options['layouts']['default']['image_height'] );
				
				$this->save();
			}
		}
		
		/**
		 * init_public()
		 *
		 * Run on on public pages (non-dashboard).
		 *
		 */
		function init_public() {
			require_once(dirname( __FILE__ ).'/classes/public.php');
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
		
		
		function shortcode() {
			$this->load();
			return $this->print_slider();
		}
		
		
		function widget() {
			$this->load();
			echo $this->print_slider();
		}
		
		function print_slider() {
		
			$this->instance_count++;
			
			$css = ''; // Additional CSS that can be set by init.txt
			add_action( 'wp_footer', array( &$this, 'print_footer') );
			
			$options = &$this->_options['layouts'][ $this->_options['layout'] ];
			
			// If a once per page load script exists, run it!
			if ( file_exists( $this->_pluginPath . '/layouts/' . $this->_options['layout'] . '/init.txt' ) ) {
				eval( file_get_contents( $this->_pluginPath . '/layouts/' . $this->_options['layout'] . '/init.txt' ) );
			}
			
			$output = '';
			
			if ( $this->instance_count == 1 ) { // Only print once.
				$output .= '<script type="text/javascript" src="' . $this->_pluginURL . '/js/jquery.loopedslider.js"></script>';
			}
			$output .= '<div id="featuredposts-' . $this->instance_count . '" class="featuredposts">';
			
			$output .= '<div class="featuredposts-container">';
			$output .= '	<div class="featuredposts-slides">';
			
			global $post;
			$original_post = $post; // Retain post ID for this page in case already set. Restore this at end.
			$post_args = array(
				'numberposts'	=>	$this->_options['posts_count'],
				'offset'	=>	'0',
				'post_type'	=>	$this->_options['post_type'],
				'category'	=>	$this->_options['category'],
			);
			
			// Apply featured posts custom excerpt and readmore
			add_filter( 'excerpt_length', array( &$this, 'pb_excerpt_length' ), 1000 );
			add_filter( 'excerpt_more', array( &$this, 'pb_excerpt_readmore' ), 1000 );
				
				$posts = get_posts( $post_args );
			
				if ( file_exists( $this->_pluginPath . '/layouts/' . $this->_options['layout'] . '/layout.txt' ) ) {
					$layout = file_get_contents( $this->_pluginPath . '/layouts/' . $this->_options['layout'] . '/layout.txt' );
				} else {
					$layout = 'echo "ERROR #544334. FEATUREDPOSTS LAYOUT FILE MISSING! Choose another layout!";';
				}
			
				$posts_found = 0;
				foreach( $posts as $post ) {
					$posts_found++;
					setup_postdata($post);
				
					eval( $layout );
					$output .= "\n\r";
				}
				
			// Remove featuredposts custom excerpt and readmore to avoid effect other areas of site
			remove_filter( 'excerpt_length', array( &$this, 'pb_excerpt_length' ), 1000 );
			remove_filter( 'excerpt_more', array( &$this, 'pb_excerpt_readmore' ), 1000 );
			
			if ( !empty( $post->ID ) ) {
				$post = $original_post;
			}
			
			$output .= '	</div>';
			$output .= '</div>';
			$output .= '<a href="#" class="previous"><img src="' . $this->_pluginURL . '/layouts/' . $this->_options['layout'] . '/images/previous.png" width="40" height="40" alt="Previous" /></a>';
			$output .= '<a href="#" class="next"><img src="' . $this->_pluginURL . '/layouts/' . $this->_options['layout'] . '/images/next.png" width="40" height="40" alt="Next" /></a>';
			$output .= '<ul class="featuredposts-pagination">';
			$i = 0;
			while ( $i < $posts_found ) {
				$i++;
				$output .= '	<li><a href="#">' . $i . '</a></li>';
			}
			$output .= '</ul>';
			$output .= '</div>';
			
			$output .= "\r\n";
			
			$output .= '<script type="text/javascript" charset="utf-8">';
			$output .= 'jQuery(document).ready(function() {';
			$output .= '	jQuery(function(){';
			$output .= '		jQuery("#featuredposts-' . $this->instance_count . '").loopedSlider({';
			
			$output .= 'container: ".featuredposts-container",';
			$output .= 'slides: ".featuredposts-slides",';
			$output .= 'pagination: "featuredposts-pagination",'; // dont include period prefix for class here.
			$output .= 'autoStart: ' . $this->_options['autostart'] . ',';
			$output .= 'containerClick: false,';
			$output .= 'restart: ' . $this->_options['restart'] . ',';
			
			$output .= 'slidespeed: ' . $this->_options['slidespeed'] . ',';
			$output .= 'fadespeed: ' . $this->_options['fadespeed'] . ',';
			
			$output .= 'addPagination: false';
				
			$output .= '});';
			$output .= '	});';
			$output .= '});';
			$output .= '</script>';
			
			$output .= "\r\n";
							
			$output .= '<style type="text/css">';

			$output .= '	.featuredposts-container {';
			$output .= '		width: ' . $options['width'] . 'px;';
			$output .= '		height: ' . $options['height'] . 'px;';
			$output .= '	}';
			$output .= '	.featuredposts-slides div {';
			$output .= '		width: ' . $options['width'] . 'px;';
			$output .= '	}';
			$output .= '	.featuredposts {';
			$output .= '		width: ' . $options['width'] . 'px;';
			$output .= '	}';
			$output .= '	.featuredposts-image {';
			$output .= '		height: ' . $options['image_height'] . 'px;';
			$output .= '	}';
			
			
			$output .= '	ul.featuredposts-pagination a {';
			$output .= '		background-image:url(' . $this->_pluginURL . '/layouts/' . $this->_options['layout'] . '/images/pagination.png);';
			$output .= '	}';
			
			$output .= $css;
			
			$output .= '</style>';
			$output .= "\r\n";
			
			return $output;
		}
		
		function print_footer() {
			wp_enqueue_style('featuredposts_public', $this->_pluginURL . '/layouts/' . $this->_options['layout'] . '/style.css' );
			wp_print_styles('featuredposts_public');
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

	$PluginBuddyFeaturedPosts = new PluginBuddyFeaturedPosts(); // Create instance
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
