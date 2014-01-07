<?php
/**
 *
 * Plugin Name: Copious Comments
 * Plugin URI: http://pluginbuddy.com/purchase/displaybuddy/
 * Description: DisplayBuddy Series - Displays your top commented posts with a graphical post count in a widget or via shortcode.
 * Version: 1.0.9
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
 * 1. Navigate to the new plugin menu in the Wordpress Administration Panel.
 *
 */


if (!class_exists("PluginBuddyCopiousComments")) {
	class PluginBuddyCopiousComments {
		var $_version = '1.0.9';
		var $_updater = '1.0.7';
		
		var $_var = 'pluginbuddy-copiouscomments';					// Format: pluginbuddy-pluginnamehere. All lowecase, no dashes.
		var $_name = 'Copious Comments';							// Pretty plugin name. Only used for display so any format is valid.
		var $_series = 'DisplayBuddy';								// Series name if applicable.
		var $url = 'http://pluginbuddy.com/purchase/displaybuddy/';	// Purchase URL.
		var $_timeformat = '%b %e, %Y, %l:%i%p';					// Mysql time format.
		var $_timestamp = 'M j, Y, g:iA';							// PHP timestamp format.
		var $_defaults = array(
			'post_type'					=>	'post',
			'posts'						=>	'5',					// Default number of posts to display.
			'width'						=>	'90',					// Maximum width in percent of entity.
			'layout'					=>	'default',
			'truncate'					=>	'60',
			'customstyles_enabled'		=>	false,
			'access'	=>	'activate_plugins',
		);
		var $instance_count = 0;
		
		function PluginBuddyCopiousComments() {
			$this->_pluginPath = dirname( __FILE__ );
			$this->_pluginRelativePath = ltrim( str_replace( '\\', '/', str_replace( rtrim( ABSPATH, '\\\/' ), '', $this->_pluginPath ) ), '\\\/' );
			$this->_pluginURL = site_url() . '/' . $this->_pluginRelativePath;
			if ( isset( $_SERVER['HTTPS'] ) && $_SERVER['HTTPS'] == 'on' ) { $this->_pluginURL = str_replace( 'http://', 'https://', $this->_pluginURL ); }
			$this->_selfLink = array_shift( explode( '?', $_SERVER['REQUEST_URI'] ) ) . '?page=' . $this->_var;
			
			require_once( dirname( __FILE__ ) . '/classes/widget.php' );
			
			if ( is_admin() ) { // Runs when in the admin dashboard.
				add_action( 'init', array( &$this, 'upgrader_register' ), 50 );
				add_action( 'init', array( &$this, 'upgrader_select' ), 100 );
				add_action( 'init', array( &$this, 'upgrader_instantiate' ), 101 );
				require_once( $this->_pluginPath . '/classes/admin.php' );
				require_once( $this->_pluginPath . '/lib/updater/updater.php' );
				register_activation_hook( $this->_pluginPath, array( &$this, 'activate' ) ); // Run some code when plugin is activated in dashboard.
			} else { // Runs when in non-dashboard parts of the site.
				wp_enqueue_script( 'jquery' );
				
				add_shortcode( 'copiouscomments', array( &$this, 'shortcode' ) );
				
				add_action( $this->_var . '-widget', array( &$this, 'widget' ), 10, 2 ); // Add action to run widget function.
			}
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
		 *	log()
		 *
		 *	Logs to a text file depending on settings.
		 *	0 = none, 1 = errors only, 2 = errors + warnings, 3 = debugging (all kinds of actions)
		 *
		 *	$text	string			Text to log.
		 *	$log_type	string		Valid options: error, warning, all (default so may be omitted).
		 *
		 */
		function log( $text, $log_type = 'all' ) {
			$write = false;
			
			if ( !isset( $this->_options['log_level'] ) ) {
				$this->load();
			}
			
			if ( $this->_options['log_level'] == 0 ) { // No logging.
				return;
			} elseif ( $this->_options['log_level'] == 1 ) { // Errors only.
				if ( $log_type == 'error' ) {
					$write = true;
				}
			} elseif ( $this->_options['log_level'] == 2 ) { // Errors and warnings only.
				if ( ( $log_type == 'error' ) || ( $log_type == 'warning' ) ) {
					$write = true;
				}
			} elseif ( $this->_options['log_level'] == 3 ) { // Log all; Errors, warnings, actions, notes, etc.
				$write = true;
			}
			
			if ( $write === true ) {
				$fh = fopen( WP_CONTENT_DIR . '/uploads/emailbuddy.txt', 'a');
				fwrite( $fh, '[' . date( $this->_timestamp . ' ' . get_option( 'gmt_offset' ), time() + (get_option( 'gmt_offset' )*3600) ) . '-' . $log_type . '] ' . $text . "\n" );
				fclose( $fh );
			}
		}
		
		
		/**
		 * activate()
		 *
		 * Run on plugin activation. Useful for setting up initial stuff.
		 *
		 */
		function activate() {
			$this->load(); // load in and save defaults for first run.
			$this->save();
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
		
		
		function shortcode( $instance ) {
			$this->load();
			
			if ( isset( $instance['width'] ) ) {
				$width = intval( $instance['width'] );
			} else {
				$width = intval( $this->_options['width'] );
			}
			if ( isset( $instance['posts'] ) ) {
				$posts = intval( $instance['posts'] );
			} else {
				$posts = intval( $this->_options['posts'] );
			}
			if ( isset( $instance['truncate'] ) ) {
				$truncate = intval( $instance['truncate'] );
			} else {
				$truncate = intval( $this->_options['truncate'] );
			}
			
			return $this->create( $posts, 'default', $width, $truncate );
		}
		
		
		function widget( $instance ) {
			$this->load();
			if ( isset( $instance['width'] ) ) {
				$width = intval( $instance['width'] );
			} else {
				$width = intval( $this->_options['width'] );
			}
			if ( isset( $instance['posts'] ) ) {
				$posts = intval( $instance['posts'] );
			} else {
				$posts = intval( $this->_options['posts'] );
			}
			if ( isset( $instance['truncate'] ) ) {
				$truncate = intval( $instance['truncate'] );
			} else {
				$truncate = intval( $this->_options['truncate'] );
			}
			if ( isset( $instance['post_type'] ) ) {
				$post_type = intval( $instance['post_type'] );
			} else {
				$post_type = intval( $this->_options['post_type'] );
			}
			
			//echo apply_filters( 'widget_title', $instance['title'] );
			echo $this->create( $posts, 'default', $width, $truncate );
		}
		
		// $width	string		Maximum width that a bar can be in percent.
		function create( $post_limit, $layout, $width, $truncate ) {
			$this->instance_count++;
			
			add_action( 'wp_footer', array( &$this, 'print_footer') );
			
			$css = '';
			if ( file_exists( $this->_pluginPath . '/layouts/' . $layout . '/init.txt' ) ) {
				eval( file_get_contents( $this->_pluginPath . '/layouts/' . $layout . '/init.txt' ) );
			}
			
			global $wpdb;
			$query = "select * from $wpdb->posts order by comment_count DESC limit $post_limit";
			$posts = $wpdb->get_results( $wpdb->prepare( $query ) );
			
			$return = '';
			$return .= '<ul id="pb-copious-' . $this->instance_count . '" class="pb-copious pb-copious-' . $this->instance_count . '">';
			$i = 0;
			if ( $posts ) {
				global $post;
				foreach ( $posts as $post ) {
					setup_postdata( $post );
					$i++;
				
					$this_count = get_comments_number('0','1','%');
					if ( $i == 1 ) {
						$max_count = $this_count;
					}
					if ( $max_count == 0 ) {
						$this_percent = 0;
					} else {
						$this_percent = ( $this_count / $max_count ) * $width;
					}
				
				
					$return .= '<li>';
					$return .= '	<a style="width: ' . $width . '%;" href="' . get_permalink() . '" title="' . get_the_title() . '">' . $this->truncate( get_the_title(), $truncate, '...' ) . '</a>';
					$return .= '	<span class="comment-bar" style="width: ' . $this_percent . '%;"><span class="comment-count">' . $this_count . '</span></span>';
					$return .= '</li>';
				} //end foreach
			} //posts
			$return .= '</ul>';
			
			$return .= '<style type="text/css">';
			$return .= $css;
			$return .= '</style>';
			
			return $return;
		}
		
		function print_footer() {
			wp_enqueue_style('copiouscomments_public', $this->_pluginURL . '/layouts/' . $this->_options['layout'] . '/style.css' );
			wp_print_styles('copiouscomments_public');
			
			if ( $this->_options['customstyles_enabled'] === true ) {
				$wp_upload_dir = WP_UPLOAD_DIR();
				$custom_style_file = $wp_upload_dir['basedir'] . '/copiouscomments/' . $this->_options['layout'] . '/style_custom.css';
				if ( file_exists( $custom_style_file ) ) {
					$custom_style_url = $wp_upload_dir['baseurl'] . '/copiouscomments/' . $this->_options['layout'] . '/style_custom.css';
					wp_enqueue_style('copiouscomments_public-custom', $custom_style_url );
					wp_print_styles('copiouscomments_public-custom');
				} else {
					echo '<!-- CopiousComments ERROR: Missing style file! -->';
				}
			}
		}
		
		function truncate($string, $max = 20, $replacement = '')
{
    if (strlen($string) <= $max)
    {
        return $string;
    }
    $leave = $max - strlen ($replacement);
    return substr_replace($string, $replacement, $leave);
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
	
	$PluginBuddyCopiousComments = new PluginBuddyCopiousComments(); // Create instance
}



?>
