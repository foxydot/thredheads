<?php
/**
 *
 * Plugin Name: Carousel
 * Plugin URI: http://pluginbuddy.com/purchase/displaybuddy/
 * Description: DisplayBuddy Series - Carousel lets you display a sliding image Carousel.
 * Version: 1.0.17
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


if (!class_exists('pluginbuddy_carousel')) {
	class pluginbuddy_carousel {
		var $_version = '1.0.17';
		var $_updater = '1.0.7';
		var $_var = 'pluginbuddy_carousel'; // Format: pluginbuddy-pluginnamehere. All lowecase, no dashes.
		var $_name = 'Carousel'; // Pretty plugin name. Only used for display so any format is valid.
		var $_series = 'DisplayBuddy'; // Series name if applicable.
		var $_url = 'http://pluginbuddy.com/purchase/displaybuddy/';
		var $_timeformat = '%b %e, %Y, %l:%i%p';	// Mysql time format.
		var $_timestamp = 'M j, Y, g:iA';			// PHP timestamp format.
		var $_defaults = array(
			'groups'						=> array(),
			'access'						=>	'activate_plugins',
		);
		var $_groupdefaults = array(
			'title'								=>		'',
			'images'							=>		array(),
			'layout'							=>		'default',
			'image_width'						=>		'140',
			'image_height'						=>		'140',
			'align'								=>		'center',
			'entity_width'						=>		'520',
			'direction'							=>		'left',
			'circular'							=>		'true',
			'infinite'							=>		'true',
			'items'								=>		'3',
			'scroll_duration'					=>		'500',
			'scroll_pauseOnHover'				=>		'false',
			'auto_play'							=>		'true',
			'auto_pauseDuration'				=>		'2500',
			'auto_delay'						=>		'0',
			'show_navigation'					=>		'true',
			'show_pagination'					=>		'true',
			'random_order'						=>		'false',
		);
		
		var $_widget = 'Display an image carousel.';
		var $_widgetdefaults = array(
			'group'								=>		'',
		);
		
		var $instance_count = 0;
		
		// Default constructor. This is run when the plugin first runs.
		function pluginbuddy_carousel() {
			$this->_pluginPath = dirname( __FILE__ );
			$this->_pluginRelativePath = ltrim( str_replace( '\\', '/', str_replace( rtrim( ABSPATH, '\\\/' ), '', $this->_pluginPath ) ), '\\\/' );
			$this->_pluginURL = site_url() . '/' . $this->_pluginRelativePath;
			if ( isset( $_SERVER['HTTPS'] ) && $_SERVER['HTTPS'] == 'on' ) { $this->_pluginURL = str_replace( 'http://', 'https://', $this->_pluginURL ); }
			$this->_selfLink = array_shift( explode( '?', $_SERVER['REQUEST_URI'] ) ) . '?page=' . $this->_var;
			
			$this->load();
			foreach( $this->_options['groups'] as $group ) {
				add_image_size( 'pb_carousel_' . $group['image_width'] . 'x' . $group['image_height'], $group['image_width'], $group['image_height'], true);
			}
			
			if ( is_admin() ) { // Runs when in the dashboard.
				
				add_action( 'init', array( &$this, 'upgrader_register' ), 50 );
				add_action( 'init', array( &$this, 'upgrader_select' ), 100 );
				add_action( 'init', array( &$this, 'upgrader_instantiate' ), 101 );
				require_once( $this->_pluginPath . '/lib/updater/updater.php' );
				
				require_once( $this->_pluginPath . '/lib/medialibrary/medialibrary.php' );
				$this->_medialibrary = new PluginBuddyMediaLibrary( $this,
					array(
						'select_button_text'			=>			'Select this Image',
						'tabs'							=>			array( 'pb_uploader' => 'Upload Images to Media Library', 'library' => 'Select from Media Library' ),
						'show_input-image_alt_text'		=>			false,
						'show_input-url'				=>			false,
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
				
				require_once( $this->_pluginPath . '/classes/admin.php' );
				register_activation_hook( $this->_pluginPath, array( &$this, 'activate' ) ); // Run some code when plugin is activated in dashboard.
				
				
			} else { // Runs when in non-dashboard parts of the site.
				add_shortcode( 'pb_carousel', array( &$this, 'shortcode' ) );
				
				
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
			$fh = fopen( WP_CONTENT_DIR . '/uploads/emailbuddy.txt', 'a');
			fwrite( $fh, '[' . date( $this->_timestamp . ' ' . get_option( 'gmt_offset' ), time() + (get_option( 'gmt_offset' )*3600) ) . '-' . $log_type . '] ' . $text . "\n" );
			fclose( $fh );
		}
		
		
		/**
		 * activate()
		 *
		 * Run on plugin activation. Useful for setting up initial stuff.
		 *
		 */
		function activate() {
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
		
		
		// Same as widget but return.
		function shortcode( $instance ) {
			$this->load();
			if ( ( $instance['group'] != '' ) && ( isset( $this->_options['groups'][$instance['group']] ) ) ) {
				return $this->run_carousel( $instance['group'] );
			} else {
				return '{Unknown ' . $this->_name . ' group}';
			}
		}
		
		
		/**
		 * widget()
		 *
		 * Function is called when a widget is to be displayed. Use echo to display to page.
		 *
		 * $instance	array		Associative array containing the options saved on the widget form.
		 * @return		none
		 *
		 */
		function widget( $instance ) {
			$this->load();
			if ( ( $instance['group'] != '' ) && ( isset( $this->_options['groups'][$instance['group']] ) ) ) {
				echo $this->run_carousel( $instance['group'] );
			} else {
				echo '{Unknown ' . $this->_name . ' group}';
			}
		}
		
		
		/**
		 * widget_form()
		 *
		 * Displays the widget form on the widget selection page for setting widget settings.
		 * Widget defaults are pre-merged into $instance right before this function is called.
		 * Use $widget->get_field_id() and $widget->get_field_name() to get field IDs and names for form elements.
		 *
		 * $instance	array		Associative array containing the options set previously in this form and/or the widget defaults (merged already).
		 * &$widget		object		Reference to the widget object/class that handles parsing data. Use $widget->get_field_id(), $widget->get_field_name(), etc.
		 * @return		none
		 *
		 */
		function widget_form( $instance, &$widget ) {
			if ( empty( $this->_options['groups'] ) ) {
				echo 'You must create a PluginBuddy Carousel group to place this widget. Please do so within the plugin\'s page.';
			} else {
				?>
				<label for="<?php echo $widget->get_field_id('group'); ?>">
					Carousel Group:
					<select class="widefat" id="<?php echo $widget->get_field_id('group'); ?>" name="<?php echo $widget->get_field_name('group'); ?>">
						<?php
						foreach ( (array) $this->_options['groups'] as $id => $group ) {
							if($instance['group'] == $id) {
								$select = ' selected ';
							} else {
								$select = '';
							}
							echo '<option value="' . $id . '"' . $select . '>' . stripslashes( $group['title'] ) . '</option>';
						}
						?>
					</select>
				</label>
				
				<input type="hidden" id="<?php echo $widget->get_field_id('submit'); ?>" name="<?php echo $widget->get_field_name('submit'); ?>" value="1" />
				<?php
			}
		}
		
		
		function run_carousel( $group_id ) {
			$this->instance_count++;
			
			$captions = '';
			
			$group = &$this->get_group( $group_id );
			
			if ( !wp_script_is( 'jquery' ) ) {
				wp_print_scripts( 'jquery' );
			}
			wp_enqueue_script( $this->_var . '-carousel', $this->_pluginURL . '/js/carousel.js' );
			wp_print_scripts( $this->_var . '-carousel' );
			wp_enqueue_style( $this->_var . '-carousel', $this->_pluginURL . '/layouts/' . $group['layout'] . '/style.css' );
			wp_print_styles( $this->_var . '-carousel' );
			
			$css = ''; // Usually added in init.txt.
			
			if ( file_exists( $this->_pluginPath . '/layouts/' . $group['layout'] . '/init.txt' ) ) {
				eval( file_get_contents( $this->_pluginPath . '/layouts/' . $group['layout'] . '/init.txt' ) );
			}
			
			$return = '';
			$return .= '<script type="text/javascript">';
			$return .= '	jQuery(window).load(function() {';
			$return .= '		jQuery("#pb_carousel-' . $this->instance_count . '").carouFredSel({';
			//$return .= '			width : ' . $group['entity_width'] . ',' . "\n";
			$return .= '			direction : "' . $group['direction'] . '",' . "\n";
			$return .= '			circular : ' . $group['circular'] . ',' . "\n";
			$return .= '			infinite : ' . $group['infinite'] . ',' . "\n";
			$return .= '			items : ' . $group['items'] . ',' . "\n";
			$return .= '			scroll : {' . "\n";
			$return .= '						duration : ' . $group['scroll_duration'] . ',' . "\n";
			$return .= '						pauseOnHover : ' . $group['scroll_pauseOnHover'] . "\n";
			$return .= '					},' . "\n";
			$return .= '			auto : {' . "\n";
			$return .= '						play : ' . $group['auto_play'] . ',' . "\n";
			$return .= '						pauseDuration : ' . $group['auto_pauseDuration'] . ',' . "\n";
			$return .= '						delay : ' . $group['auto_delay'] . "\n";
			$return .= '					}' . "\n";
			
			if ( $group['show_navigation'] == 'true' ) {
				$return .= ',			prev : {' . "\n";
				$return .= '				button	: "#pb_carousel_prev-' . $this->instance_count . '"' . "\n";
				$return .= '			},' . "\n";
				$return .= '			next : { ' . "\n";
				$return .= '				button	: "#pb_carousel_next-' . $this->instance_count . '"' . "\n";
				$return .= '			}' . "\n";
			}
			if ( $group['show_pagination'] == 'true' ) {
				$return .= ',			pagination	: "#pb_carousel_pag-' . $this->instance_count . '"' . "\n";
			}
			
			
			$return .= '		});' . "\n";
			$return .= '	});';
			$return .= '</script>';
			$return .= "\n";
			
			if ( $group['random_order'] == 'true' ) {
				shuffle( $group['images'] );
			}
			
			$return .= '<div class="pb_carousel-' . $this->instance_count . ' pb_carousel_default" style="width: ' . $group['entity_width'] . 'px;">';
			$return .= '<div class="pb_carousel_safetynet" style="width: ' .$group['entity_width'] . 'px; overflow: hidden;">';//dan's bandage
			$return .= '<div id="pb_carousel-' . $this->instance_count . '">';
			foreach( $group['images'] as $image ) {
				$attachment_data = get_post( $image, ARRAY_A );
				
				// Open link tag if defined.
				if ( !empty( $attachment_data['post_content'] ) ) {
					$return .= '<a href="' . $attachment_data['post_content'] . '">';
				}
				
				// Create actual image tag.
				$image_dat = wp_get_attachment_image_src( $image, 'pb_carousel_' . $group['image_width'] . 'x' . $group['image_height'] );
				$return .= '<img src="' . $image_dat[0] . '" alt="' . $attachment_data['post_title'] . '" title="' . strip_tags( stripslashes( $attachment_data['post_excerpt'] ) ) . '" />';
				
				if ( !empty( $attachment_data['post_content'] ) ) { // Link close tag.
					$return .= '</a>';
				}
			}
			$return .= '</div>';
			$return .= '</div>';
			
			if ( $group['show_navigation'] == 'true' ) {
				$return .= '<a class="pb_carousel_' . $group['layout'] . '_prev" id="pb_carousel_prev-' . $this->instance_count . '" href="#"><span>prev</span></a>';
				$return .= '<a class="pb_carousel_' . $group['layout'] . '_next" id="pb_carousel_next-' . $this->instance_count . '" href="#"><span>next</span></a>';
			}
			if ( $group['show_pagination'] == 'true' ) {
				$return .= '<div class="pb_carousel_' . $group['layout'] . '_pag" id="pb_carousel_pag-' . $this->instance_count . '"></div>';
			}
			
			$return .= '</div>';
			$return .= "\n";
			
			$return .= '<style type="text/css">' . "\n";
			$return .=	$css;
			$return .= '</style>' . "\n";
			
			return $return;
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
	
	$pluginbuddy_carousel = new pluginbuddy_carousel(); // Create instance
	require_once( dirname( __FILE__ ) . '/classes/widget.php');
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
