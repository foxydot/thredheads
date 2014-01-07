<?php
/**
 *
 * Plugin Name: Slideshow
 * Plugin URI: http://pluginbuddy.com/purchase/displaybuddy/
 * Description: DisplayBuddy Series - Slideshow lets you display images anywhere on your site with fully customizable animated transitions and advanced effects.
 * Version: 1.0.33
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


if (!class_exists('pluginbuddy_slideshow')) {
	class pluginbuddy_slideshow {
		var $_version = '1.0.33';
		var $_updater = '1.0.7';
		
		var $_var = 'pluginbuddy_slideshow'; // Format: pluginbuddy-pluginnamehere. All lowecase, no dashes.
		var $_name = 'Slideshow'; // Pretty plugin name. Only used for display so any format is valid.
		var $_series = 'DisplayBuddy'; // Series name if applicable.
		var $_url = 'http://pluginbuddy.com/purchase/displaybuddy/';
		var $_timeformat = '%b %e, %Y, %l:%i%p';	// Mysql time format.
		var $_timestamp = 'M j, Y, g:iA';		// PHP timestamp format.
		var $_defaults = array(
			'groups'	=>	array(),
			'access'	=>	'activate_plugins',
		);
		var $_groupdefaults = array(
			'title'				=>		'',
			'images'			=>		array(),
			'layout'			=>		'default',
			'type'				=>		'slider',		// jQuery library for this group. Default: slider. Possible values: slider, cycle.
			
			'enable_css_files'		=>		'true',
			'image_width'			=>		'500',
			'image_height'			=>		'300',
			'thumb_image_width'		=>		'85',
			'thumb_image_height'		=>		'65',
			
			'slider-align'			=>		'center',
			'slider-effect'			=>		'random',
			'slider-slices'			=>		'15',
			'slider-animSpeed'		=>		'500',
			'slider-pauseTime'		=>		'3000',
			'slider-directionNav'		=>		'true',			// Next & Prev.
			'slider-directionNavHide'	=>		'true',			//Only show on hover
			'slider-controlNav'		=>		'true',			//1,2,3...
			'slider-controlNavThumbs'	=>		'false',		//Use thumbnails for Control Nav
			'slider-keyboardNav'		=>		'false',		//Use left & right arrows
			'slider-pauseOnHover'		=>		'true',			//Stop animation while hovering
			'slider-captionOpacity'		=>		'0.8',			//Universal caption opacity
			'slider-shadows'		=>		'true',
			
			'cycle-align'			=>		'center',
			'cycle-fx'			=>		'fade',			// name of transition effect (or comma separated names, ex: fade,scrollUp,shuffle) 
			'cycle-timeout'			=>		'4000',			// milliseconds between slide transitions (0 to disable auto advance) 
			'cycle-continuous'		=>		'0',			// true to start next transition immediately after current one completes 
			'cycle-speed'			=>		'1000',			// speed of the transition (any valid fx speed value) 
			'cycle-speedIn'			=>		'0',			// speed of the 'in' transition 
			'cycle-speedOut'		=>		'0',			// speed of the 'out' transition 
			'cycle-sync'			=>		'1',			// true if in/out transitions should occur simultaneously 
			'cycle-random'			=>		'0',			// true for random, false for sequence (not applicable to shuffle fx) 
			'cycle-pause'			=>		'1',			// true to enable "pause on hover" 
			'cycle-autostop'		=>		'0',			// true to end slideshow after X transitions (where X == slide count) 
			'cycle-autostopCount'		=>		'3',			// number of transitions (optionally used with autostop to define X) 
			'cycle-delay'			=>		'0',			// additional delay (in ms) for first transition (hint: can be negative) 
			'cycle-randomizeEffects'	=>		'1',			// valid when multiple effects are used; true to make the effect sequence random 
			'cycle-pb_pager'		=>		'0',			// valid when multiple effects are used; true to make the effect sequence random 
		);
		
		var $_widget = 'Display an image slideshow.';
		var $_widgetdefaults = array(
			'group'	=>	'',
		);
		
		var $instance_count = 0;
		
		// Default constructor. This is run when the plugin first runs.
		function pluginbuddy_slideshow() {
			$this->_pluginPath = dirname( __FILE__ );
			$this->_pluginRelativePath = ltrim( str_replace( '\\', '/', str_replace( rtrim( ABSPATH, '\\\/' ), '', $this->_pluginPath ) ), '\\\/' );
			$this->_pluginURL = site_url() . '/' . $this->_pluginRelativePath;
			$this->_pluginBase = plugin_basename( __FILE__  );
			if ( isset( $_SERVER['HTTPS'] ) && $_SERVER['HTTPS'] == 'on' ) { $this->_pluginURL = str_replace( 'http://', 'https://', $this->_pluginURL ); }
			$this->_selfLink = array_shift( explode( '?', $_SERVER['REQUEST_URI'] ) ) . '?page=' . $this->_var;
			
			$this->load();
			foreach( $this->_options['groups'] as $group ) {
				add_image_size( 'pb_slideshow_' . $group['image_width'] . 'x' . $group['image_height'], $group['image_width'], $group['image_height'], true);
				// If thumbnail navigation enabled then create their thumbs...
				if ( $group['slider-controlNavThumbs'] == 'true' ) {
					add_image_size( 'pb_slideshow_thumb_' . $group['thumb_image_width'] . 'x' . $group['thumb_image_height'], $group['thumb_image_width'], $group['thumb_image_height'], true);
				}
			}
			
			if ( is_admin() ) { // Runs when in the dashboard.
				add_action( 'init', array( &$this, 'upgrader_register' ), 50 );
				add_action( 'init', array( &$this, 'upgrader_select' ), 100 );
				add_action( 'init', array( &$this, 'upgrader_instantiate' ), 101 );
				
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
				
				require_once( $this->_pluginPath . '/classes/admin.php' );
			} else {
				add_shortcode( 'pb_slideshow', array( &$this, 'shortcode' ) );
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
				if ( $this->_options['groups'][$instance['group']]['type'] == 'slider' ) {
					return $this->run_slider( $instance['group'] );
				} elseif ( $this->_options['groups'][$instance['group']]['type'] == 'cycle' ) {
					return $this->run_cycle( $instance['group'] );
				}
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
				if ( $this->_options['groups'][$instance['group']]['type'] == 'slider' ) {
					echo $this->run_slider( $instance['group'] );
				} elseif ( $this->_options['groups'][$instance['group']]['type'] == 'cycle' ) {
					echo $this->run_cycle( $instance['group'] );
				}
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
				echo 'You must create a PluginBuddy Slideshow group to place this widget. Please do so within the plugin\'s page.';
			} else {
				?>
				<label for="<?php echo $widget->get_field_id('group'); ?>">
					Slideshow Group:
					<select class="widefat" id="<?php echo $widget->get_field_id('group'); ?>" name="<?php echo $widget->get_field_name('group'); ?>">
						<?php
						foreach ( (array) $this->_options['groups'] as $id => $group ) {
							echo '<option value="' . $id . '"';
							if ( $instance['group'] == $id ) { echo ' selected'; }
							echo '>' . $group['title'] . '</option>';
						}
						?>
					</select>
				</label>
				
				<input type="hidden" id="<?php echo $widget->get_field_id('submit'); ?>" name="<?php echo $widget->get_field_name('submit'); ?>" value="1" />
				<?php
			}
		}
		
		
		function run_slider( $group_id ) {
			$this->instance_count++;
			
			$captions = '';
			$caption_count = 0;
			
			$group = &$this->get_group( $group_id );
			
			if ( !wp_script_is( 'jquery' ) ) {
				wp_print_scripts( 'jquery' );
			}
			if ( !wp_script_is( $this->_var . '-slider' ) ) {
				wp_enqueue_script( $this->_var . '-slider', $this->_pluginURL . '/js/nivo-slider.js' );
				wp_print_scripts( $this->_var . '-slider' );
			}
			if ( !wp_style_is( $this->_var . '-slider-' . $group['layout'] ) && ( $group['enable_css_files'] == 'true' ) ) {
				wp_enqueue_style( $this->_var . '-slider-' . $group['layout'], $this->_pluginURL . '/layouts/slider/' . $group['layout'] . '/style.css' );
				wp_print_styles( $this->_var . '-slider-' . $group['layout'] );
			}
			
			$css = ''; // Usually added in init.txt.
			
			if ( file_exists( $this->_pluginPath . '/layouts/slider/' . $group['layout'] . '/init.txt' ) ) {
				eval( file_get_contents( $this->_pluginPath . '/layouts/slider/' . $group['layout'] . '/init.txt' ) );
			}
			
			$return = '';
			$return .= '<script type="text/javascript">' . "\n";
			$return .= '	jQuery(window).load(function() {' . "\n";
			$return .= '		jQuery(\'#pb_slideshow_slider-' . $this->instance_count . '\').nivoSlider({' . "\n";
			$return .= '			effect: \'' . $group['slider-effect'] . '\',' . "\n";
			$return .= '			slices: ' . $group['slider-slices'] . ',' . "\n";
			$return .= '			animSpeed: ' . $group['slider-animSpeed'] . ',' . "\n";
			$return .= '			pauseTime: ' . $group['slider-pauseTime'] . ',' . "\n";
			$return .= '			directionNav: ' . $group['slider-directionNav'] . ',' . "\n";
			$return .= '			directionNavHide: ' . $group['slider-directionNavHide'] . ',' . "\n";
			$return .= '			controlNav: ' . $group['slider-controlNav'] . ',' . "\n";
			$return .= '			controlNavThumbs: ' . $group['slider-controlNavThumbs'] . ',' . "\n";
			$return .= '			controlNavThumbsFromRel: ' . $group['slider-controlNavThumbs'] . ',' . "\n";
			$return .= '			keyboardNav: ' . $group['slider-keyboardNav'] . ',' . "\n";
			$return .= '			pauseOnHover: ' . $group['slider-pauseOnHover'] . ',' . "\n";
			$return .= '			captionOpacity: ' . $group['slider-captionOpacity'] . "\n";
			$return .= '		});' . "\n";
			$return .= '	});' . "\n";
			$return .= '</script>' . "\n";
			$return .= "\n";
			
			$return .= '<div id="pb_slideshow_slider-' . $this->instance_count . '" class="nivoSlider" style="width: ' . $group['image_width'] . 'px;">';
			foreach( $group['images'] as $image ) {
				$attachment_data = get_post( $image, ARRAY_A );
				
				// Render caption if defined.
				if ( !empty( $attachment_data['post_excerpt'] ) ) {
					$caption_count++;
					
					$title_val = ' title="#pb_slideshow_caption-' . $this->instance_count . '-' . $caption_count . '"';
					$captions .= '<div id="pb_slideshow_caption-' . $this->instance_count . '-' . $caption_count . '" class="nivo-html-caption">';
					$captions .= $attachment_data['post_excerpt'];
					$captions .= '</div>';
				} else {
					$title_val = '';
				}
				
				// Open link tag if defined.
				if ( !empty( $attachment_data['post_content'] ) && stristr( $attachment_data['post_content'], 'http' ) ) {
					$return .= '<a href="' . $attachment_data['post_content'] . '">';
				}
				// echo "dan, the current option is : " . $group['enable_css_files'] . ".";
				
				// Create actual image tag.
				$image_dat = wp_get_attachment_image_src( $image, 'pb_slideshow_' . $group['image_width'] . 'x' . $group['image_height'] );
				$return .= '<img src="' . $image_dat[0] . '"' . $title_val;
				if ( $group['slider-controlNavThumbs'] == 'true' ) { // If thumbnail nav enabled then set rel parameter to be URL to thumbs.
					$image_dat_thumb = wp_get_attachment_image_src( $image, 'pb_slideshow_thumb_' . $group['thumb_image_width'] . 'x' . $group['thumb_image_height'] );
					$return .= ' rel="' . $image_dat_thumb[0] . '"';
				}
				$return .= ' alt="' . $attachment_data['post_title'] . '"';
				$return .= ' />';
				
				if ( !empty( $attachment_data['post_content'] ) && stristr( $attachment_data['post_content'], 'http' ) ) {
					$return .= '</a>';
				}
			}
			$return .= '</div>';
			
			$return .= $captions;
			
			$return .= "\n";
			
			$return .= '<style type="text/css">' . "\n";
			$return .=	$css;
			$return .= '</style>' . "\n";
			
			return $return;
		}
		
		
		function run_cycle( $group_id ) {
			$this->instance_count++;
			$slide_count = 0;
			
			$captions = '';
			$pager = '';
			
			$group = &$this->get_group( $group_id );
			
			if ( !wp_script_is( 'jquery' ) ) {
				wp_print_scripts( 'jquery' );
			}
			if ( !wp_script_is( $this->_var . '-cycle' ) ) {
				wp_enqueue_script( $this->_var . '-cycle', $this->_pluginURL . '/js/jquery-cycle-all.js' );
				wp_print_scripts( $this->_var . '-cycle' );
			}
			if ( !wp_style_is( $this->_var . '-cycle-' . $group['layout'] ) && ( $group['enable_css_files'] == 'true' ) ) {
				wp_enqueue_style( $this->_var . '-cycle-' . $group['layout'], $this->_pluginURL . '/layouts/cycle/' . $group['layout'] . '/style.css' );
				wp_print_styles( $this->_var . '-cycle-' . $group['layout'] );
			}
			
			$css = ''; // Usually added in init.txt.
			
			if ( file_exists( $this->_pluginPath . '/layouts/cycle/' . $group['layout'] . '/init.txt' ) ) {
				eval( file_get_contents( $this->_pluginPath . '/layouts/cycle/' . $group['layout'] . '/init.txt' ) );
			}
			
			$return = '';
			$return .= '<script type="text/javascript">' . "\n";
			$return .= '	jQuery(document).ready(function() {' . "\n";
			$return .= '		jQuery(\'#pb_slideshow_cycle-' . $this->instance_count . '\').cycle({' . "\n";
			$return .= '			fx: \'' . $group['cycle-fx'] . '\',' . "\n";
			$return .= '			timeout: ' . $group['cycle-timeout'] . ',' . "\n";
			$return .= '			continuous: ' . $group['cycle-continuous'] . ',' . "\n";
			$return .= '			speed: ' . $group['cycle-speed'] . ',' . "\n";
			$return .= '			speedIn: ' . $group['cycle-speedIn'] . ',' . "\n";
			$return .= '			speedOut: ' . $group['cycle-speedOut'] . ',' . "\n";
			$return .= '			sync: ' . $group['cycle-sync'] . ',' . "\n";
			$return .= '			random: ' . $group['cycle-random'] . ',' . "\n";
			$return .= '			pause: ' . $group['cycle-pause'] . ',' . "\n";
			$return .= '			autostop: ' . $group['cycle-autostop'] . ',' . "\n";
			$return .= '			autostopCount: ' . $group['cycle-autostopCount'] . ',' . "\n";
			$return .= '			delay: ' . $group['cycle-delay'] . ',' . "\n";
			$return .= '			randomizeEffects: ' . $group['cycle-randomizeEffects'] . "\n";
			if ( $group['cycle-pb_pager'] == '1' ) {
				$return .= ',			pager: \'#pb_slideshow_cycle_pager-' . $this->instance_count . '\'' . "\n";
			}
			$return .= '		});' . "\n";
			$return .= '	});' . "\n";
			$return .= '</script>' . "\n";
			$return .= "\n";
			
			$return .= '<div id="pb_slideshow_cycle-' . $this->instance_count . '" class="pb_slideshow_cycle">';
			foreach( $group['images'] as $image ) {
				$slide_count++;
				
				$attachment_data = get_post( $image, ARRAY_A );
				
				// Render caption if defined.
				if ( !empty( $attachment_data['post_excerpt'] ) ) {
					$title_val = ' title="' . strip_tags( $attachment_data['post_excerpt'] ) . '" alt="' . strip_tags( $attachment_data['post_excerpt'] ) . '"';
				} else {
					$title_val = ' alt="' . $attachment_data['post_title'] . '"';
				}
				
				// Open link tag if defined.
				if ( !empty( $attachment_data['post_content'] ) && stristr( $attachment_data['post_content'], 'http' ) ) {
					$return .= '<a href="' . $attachment_data['post_content'] . '">';
				}
				
				// Create actual image tag.
				$image_dat = wp_get_attachment_image_src( $image, 'pb_slideshow_' . $group['image_width'] . 'x' . $group['image_height'] );
				$return .= '<img src="' . $image_dat[0] . '"' . $title_val;
				$return .= ' width="' . $group['image_width'] . '" height="' . $group['image_height'] . '" ';
				$return .= ' />';
				
				if ( !empty( $attachment_data['post_content'] ) && stristr( $attachment_data['post_content'], 'http' ) ) {
					$return .= '</a>';
				}
			}
			$return .= '</div>';
			
			$return .= "\n";
			
			if ( $group['cycle-pb_pager'] == '1' ) {
				$return .= '<div id="pb_slideshow_cycle_pager-' . $this->instance_count . '">';
				$return .= '</div>';
			}
			
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
		
		/*
		// Custom image resize code.
		function filter_image_downsize( $result, $id, $size ) {
			if ( is_array( $size ) ) {
				return;
			}
			// Iteration: 11
			
			// Store current meta information and size data.
			$this->_temp_downsize_size = $size;
			$this->_temp_downsize_meta = wp_get_attachment_metadata( $id );
			
			if ( ! is_array( $imagedata = wp_get_attachment_metadata( $id ) ) ) { return $result; }
			
			if ( ! is_array( $size ) && ! empty( $imagedata['sizes'][$size] ) ) {
				$data = $imagedata['sizes'][$size];
				// Handles if the size defined for this size name has changed.
				global $_wp_additional_image_sizes;
				if ( empty( $_wp_additional_image_sizes[$size] ) ) { // Not a custom size so return data as is.
					$img_url = wp_get_attachment_url( $id );
					$img_url = path_join( dirname( $img_url ), $data['file'] );
					return array( $img_url, $data['width'], $data['height'], true );
				} else { // Custom size so only return if current image file sizes match the defined sizes.
					$img_url = wp_get_attachment_url( $id );
					$img_url = path_join( dirname( $img_url ), $data['file'] );
					return array( $img_url, $data['width'], $data['height'], true );
				}
			}
			
			require_once( ABSPATH . '/wp-admin/includes/image.php' );
			$uploads = wp_upload_dir();
			if ( ! is_array( $uploads ) || ( false !== $uploads['error'] ) ) { return $result; }
			
			$file_path = "{$uploads['basedir']}/{$imagedata['file']}";
			
			// Image is resized within the function in the following line.
			$temp_meta_information = wp_generate_attachment_metadata( $id, $file_path ); // triggers filter_image_downsize_blockextra() function via filter within. generate images. returns new meta data for image (only includes the just-generated image size).
			
			$meta_information = $this->_temp_downsize_meta; // Get the old original meta information.
			//if ( is_array( $size ) ) {
			//	$meta_information = $temp_meta_information;
			//} else {
				$meta_information['sizes'][$this->_temp_downsize_size] = $temp_meta_information['sizes'][$this->_temp_downsize_size]; // Merge old meta back in.
			//}
			wp_update_attachment_metadata( $id, $meta_information ); // Update image meta data.
			
			return $result;
		}
		*/
		/* Prevents image resizer from resizing ALL images; just the currently requested size. */
		/*
		function filter_image_downsize_blockextra( $sizes ) {
			//if ( is_array( $sizes ) ) {
			//	return $sizes;
			//}
			$sizes = array( $this->_temp_downsize_size => $sizes[$this->_temp_downsize_size] ); // Strip out all extra meta data so only one size will be generated.
			return $sizes;
		}
		*/
		
	} // End class
	
	$pluginbuddy_slideshow = new pluginbuddy_slideshow(); // Create instance
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
