<?php
/**
 *
 * Plugin Name: Tipsy
 * Plugin URI: http://pluginbuddy.com/purchase/tipsy/
 * Description: Displaybuddy Series - Tipsy lets you display customized tooltips.
 * Version: 1.0.2
 * Author: Josh Benham
 * Author URI: http://pluginbuddy.com
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


if ( !class_exists( 'pluginbuddy_tipsy' ) ) {
	class pluginbuddy_tipsy {
		// DEPRECATED VARS; use plugin_info():
		var $_version = '1.0.2';
		var $_updater = '1.0.7';										// DEPRECATED v0.3.7. Use $this->plugin_info( 'version' ) to get plugin version. Update this number until obsolete.
		var $_url = 'http://pluginbuddy.com/purchase/tipsy/';	// DEPRECATED v0.3.7. Use $this->plugin_info( 'url' ) to get plugin url. Update this url until obsolete.
		var $_var = 'pluginbuddy_tipsy';						// DEPRECATED v0.3.10. Use $this->_slug. Match _var and _slug until removal.
		var $_slug = 'pluginbuddy_tipsy';						// Format: pluginbuddy-pluginnamehere. All lowecase, no dashes.
		var $_name = 'Tipsy';									// Pretty plugin name. Only used for display so any format is valid.
		var $_series = 'DisplayBuddy';											// Series name if applicable.
		var $_timestamp = 'M j, Y, g:iA';							// PHP timestamp format.
		var $_defaults = array(
			'groups'							=>		array(),
			'role_access'						=>		'administrator',
		);
		var $_groupdefaults = array( 
			'title'								=>		'',
			'layout'							=>		'default',
			'activation'						=> 		'hover',
			'position'							=>		'bottom',
			'auto_hide'							=>		'true',
			'max_width'							=>		'200', 
			'edge_offset'						=>		'3',
			'display_delay'						=>		'400',
			'fade_in_speed'						=>		'200',
			'fade_out_speed'					=>		'200',
			'tip_content'						=>		'', 
		);
		var $instance_count = 0;
		
		
		// Default constructor. This is automatically run on each page load.
		function __construct() {
			$this->_pluginPath = dirname( __FILE__ );
			$this->_pluginRelativePath = ltrim( str_replace( '\\', '/', str_replace( rtrim( ABSPATH, '\\\/' ), '', $this->_pluginPath ) ), '\\\/' );
			$this->_pluginURL = site_url() . '/' . $this->_pluginRelativePath;
			if ( isset( $_SERVER['HTTPS'] ) && $_SERVER['HTTPS'] == 'on' ) { $this->_pluginURL = str_replace( 'http://', 'https://', $this->_pluginURL ); }
			$this->_selfLink = array_shift( explode( '?', $_SERVER['REQUEST_URI'] ) ) . '?page=' . $this->_var;
			
			if ( is_admin() ) { // Runs when in the admin dashboard.
				$this->load();	
				require_once( $this->_pluginPath . '/classes/admin.php' );
				require_once( $this->_pluginPath . '/lib/updater/updater.php' );
			} else { // Runs when in non-dashboard parts of the site.
				add_shortcode( 'tipsy', array( &$this, 'shortcode' ) );
			}
			add_action( 'init', array( &$this, 'init' ) );
			add_action( 'init', array( &$this, 'upgrader_register' ), 50 );
			add_action( 'init', array( &$this, 'upgrader_select' ), 100 );
			add_action( 'init', array( &$this, 'upgrader_instantiate' ), 101 );
		}
		
		
		function init() {
			if ( !has_filter( 'pb_the_content', 'wptexturize' ) ) {
				add_filter( 'pb_the_content', 'wptexturize'        );
				add_filter( 'pb_the_content', 'convert_smilies'    );
				add_filter( 'pb_the_content', 'convert_chars'      );
				add_filter( 'pb_the_content', 'wpautop'            );
				add_filter( 'pb_the_content', 'shortcode_unautop'  );
				add_filter( 'pb_the_content', 'prepend_attachment' );
				$vidembed = new WP_Embed();
				add_filter( 'pb_the_content', array( &$vidembed, 'run_shortcode'), 8 );
				add_filter( 'pb_the_content', array( &$vidembed, 'autoembed'), 8 );
				add_filter( 'pb_the_content', 'do_shortcode', 11 );
			} //end has_filter
			add_action( 'admin_print_scripts-post.php', array( &$this, 'post_admin_scripts' ) );
			add_action( 'admin_print_scripts-post-new.php', array( &$this, 'post_admin_scripts' ) );
		 	add_filter( 'tiny_mce_version', array( &$this, 'tiny_mce_version' ) );
			add_filter( 'mce_external_plugins', array( &$this, 'mce_external_plugins' ) );
			add_filter( 'mce_buttons', array( &$this, 'mce_buttons' ) );
		} //end init
		
		
		// TODO: REMOVE ME?
		function post_admin_scripts() { ?>
				<script type="text/javascript"> 
					var pb_tipsy_location = '<?php echo esc_js( $this->_pluginURL ); ?>';
				</script>
			<?php

			wp_enqueue_script( 'admin_get_selection', $this->_pluginURL . '/js/get_selection.js', array( 'jquery', 'thickbox' ) );
			wp_enqueue_script( 'admin_quick_tags', $this->_pluginURL . '/js/tip_quicktags.js', array( 'jquery', 'thickbox', 'admin_get_selection' ) ); 
		}
		
		
		// name, title, description, author, authoruri, version, pluginuri OR url, textdomain, domainpath, network
		function plugin_info( $type ) {
			if ( empty( $this->_info ) ) {
				require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
				$this->_info = array_change_key_case( get_plugin_data( __FILE__, false, false ), CASE_LOWER );
				$this->_info['url'] = $this->_info['pluginuri'];
			}
			
			if ( !empty( $this->_info[$type] ) ) {
				return $this->_info[$type];
			} else {
				return 'UNKNOWN_VAR_354-' . $type;
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
			$log_error = false;
			
			echo '<div id="message" class="';
			if ( $error === false ) {
				echo 'updated fade';
			} else {
				echo 'error';
				$log_error = true;
			}
			if ( $error_code != '' ) {
				$message .= '<p><a href="http://ithemes.com/codex/page/' . $this->_name . ':_Error_Codes#' . $error_code . '" target="_new"><i>' . $this->_name . ' Error Code ' . $error_code . ' - Click for more details.</i></a></p>';
				$log_error = true;
			}
			if ( $log_error === true ) {
				$this->log( $message . ' Error Code: ' . $error_code, 'error' );
			}
			echo '"><p><strong>' . $message . '</strong></p></div>';
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
		 *	video()
		 *
		 *	Displays a message to the user when they hover over the question mark. Gracefully falls back to normal tooltip.
		 *	HTML is supposed within tooltips.
		 *
		 *	$video_key		string		YouTube video key from the URL ?v=VIDEO_KEY_HERE
		 *	$title			string		Title of message to show to user. This is displayed at top of tip in bigger letters. Default is blank. (optional)
		 *	$echo_tip		boolean		Whether to echo the tip (default; true), or return the tip (false). (optional)
		 */
		function video( $video_key, $title = '', $echo_tip = true ) {
			global $wp_scripts;
			if ( !in_array( 'thickbox', $wp_scripts->done ) ) {
				wp_enqueue_script( 'thickbox' );
				wp_print_scripts( 'thickbox' );
				wp_print_styles( 'thickbox' );
			}
			
			$tip = '<a href="http://www.youtube.com/embed/' . $video_key . '?autoplay=1&TB_iframe=1&width=640&height=400" class="thickbox pluginbuddy_tip" title="Video Tutorial - ' . $title . '"><img src="' . $this->_pluginURL . '/images/pluginbuddy_play.png" alt="(video)" /></a>';
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
				$fh = fopen( WP_CONTENT_DIR . '/uploads/' . $this->_var . '.txt', 'a');
				fwrite( $fh, '[' . date( $this->_timestamp . ' ' . get_option( 'gmt_offset' ), time() + (get_option( 'gmt_offset' )*3600) ) . '-' . $log_type . '] ' . $text . "\n" );
				fclose( $fh );
			}
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
		
		
		function save() {
			add_option( $this->_var, $this->_options, '', 'no' ); // 'No' prevents autoload if we wont always need the data loaded.
			update_option( $this->_var, $this->_options );
			return true;
		}
		
		
		function &get_group( $group_id ) {
			$group = &$this->_options['groups'][$group_id];
			
			$combined_group = array_merge( $this->_groupdefaults, (array)$group );
			if ( $combined_group !== $group ) {
				// Defaults existed that werent already in the options so we need to update their settings to include some new options.
				$group = $combined_group;
				$this->save();
			}
			return $group;
		}
		
		
		function shortcode( $args=array() , $filler='' ) {
			$this->load();			
			$defaults = array(
				'content' => '',
				'group' => '',
				'use_oembed' => false,
			);
			
			
			$args = wp_parse_args( $args , $defaults );
			extract( $args );
			$content = preg_replace( '/\n*/', '', $content );
			$content = trim( $content );
			
			if ( ( $use_oembed === false ) || ( $use_oembed === 'false' ) ) {
				remove_filter( 'pb_the_content' , 'wpautop');
				$use_oembed = false;
			}
			
			
			/*
			if ( !(bool)$use_oembed ) {
				die( 'killed filter wpautop' );
				remove_filter( 'pb_the_content' , 'wpautop');
			} 
			*/
			$content = apply_filters( 'pb_the_content',  $content  );
			if ( $use_oembed != true ) {
				add_filter( 'pb_the_content', 'wpautop' );
			}	
			$content = preg_replace( '/\n*/', '', $content );
			if ( $use_oembed != true ) {
				$content = strip_tags( $content , '<h1><h2><h3><h4><h6><p><a><div><span><span><pre><i><b><strong><li><ul><ol><iframe><q><img>');
			}	
			$return = sprintf( "<a href='#' class='%s pb_tispy' title='%s'>%s </a>" , 'pb_tipsy_' . esc_attr( $group ) , $content, esc_html( $filler ) );
			$return = $this->run_tipsy( $args['group'] ) . $return;
			return $return;
		}
		
		
		function run_tipsy( $group_id ) { 
			$this->instance_count++;
			$group = &$this->get_group( $group_id );
			
			if ( !wp_script_is( 'jquery' ) ) {
				wp_print_scripts( 'jquery' );
			}
			if ( $this->instance_count == 1 ) {
				wp_enqueue_script( $this->_var . '-tipsy', $this->_pluginURL . '/js/jquery.tipTip.js' );
				wp_print_scripts( $this->_var . '-tipsy' );
			}
			// TODO: Only print once per unique group.
			// if ( !wp_style_is( $this->_var . '-tipsy-' . $group['layout'] ) ) {
			wp_enqueue_style( $this->_var . '-tipsy-' . $group['layout'], $this->_pluginURL . '/layouts/' . $group['layout'] . '/style.css' );
			wp_print_styles( $this->_var . '-tipsy-' . $group['layout'] );
			
			if ( $group['auto_hide'] == 'true' ) {
				$keepAlive = 'false';
			} else {
				$keepAlive = 'true';
			}
			
			
			$css = ''; // Usually added in init.txt.
			$return = '<script type="text/javascript">';
			$return .= '	jQuery(document).ready(function(){';
			$return .= '		jQuery(".pb_tipsy_'.$group_id.'").tipTip({';
			$return .= '			activation : "' . $group['activation'] . '",' . "\n";
			$return .= '			defaultPosition : "' . $group['position'] . '",' . "\n";
			$return .= '			keepAlive : ' . $keepAlive . ',' . "\n";
			$return .= '			maxWidth : "' . $group['max_width'] . 'px",' . "\n";
			$return .= '			edgeOffset : ' . $group['edge_offset'] . ',' . "\n";
			$return .= '			delay : "' . $group['display_delay'] . '",' . "\n";
			$return .= '			fadeIn : ' . $group['fade_in_speed'] . ',' . "\n";
			$return .= '			fadeOut : ' . $group['fade_out_speed'] . '' . "\n";
			$return .= '							});' . "\n";	
			$return .= '					});';
			$return .= '</script>';
			return $return;
			
		}
		
		
		//Button functions for tinymce
		function mce_buttons( $buttons ) {
			array_push( $buttons , 'tipbutton' );
			return $buttons;
		}
		
		
		function mce_external_plugins($plugin_array) {
			$plugin_array['pbtipsy']  =  $this->_pluginURL . '/js/tinymcebutton.js';
			return $plugin_array;
		}
		
		
		function tiny_mce_version($version) {
			return ++$version;
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
	
	
} //end class exists

if ( class_exists( 'pluginbuddy_tipsy' ) ) {
	global $pluginbuddy_tipsy;
	$pluginbuddy_tipsy = new pluginbuddy_tipsy();
	
	function pb_tipsy( $args = array() ) {
		$defaults = array(
			'content' => '',
			'group' => 0,
			'filler' => '',
			'use_oembed' => false,
			'echo' => false
		);
		$args = wp_parse_args( $args, $defaults );
		extract( $args );
	
		global $pluginbuddy_tipsy;
		$return = $pluginbuddy_tipsy->shortcode( $args, $filler );
		if ( $echo ) {
			echo $return;
		}
		return $return;
	} //end pb_tipsy
} //end class_Exists


?>
