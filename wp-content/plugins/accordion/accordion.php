<?php
/**
 *
 * Plugin Name: Accordion
 * Plugin URI: http://pluginbuddy.com/purchase/displaybuddy/
 * Description: DisplayBuddy Series - Accordion lets you group items in accordion style via a shortcode or widget
 * Version: 1.0.15
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


if (!class_exists('pluginbuddy_accordion')) {
	class pluginbuddy_accordion {
		var $_version = '1.0.15';
		var $_updater = '1.0.7';
		var $_var = 'pluginbuddy_accordion'; // Format: pluginbuddy-pluginnamehere. All lowecase, no dashes.
		var $_name = 'Accordion'; // Pretty plugin name. Only used for display so any format is valid.
		var $_series = 'DisplayBuddy'; // Series name if applicable.
		var $_url = 'http://pluginbuddy.com/purchase/displaybuddy/';
		var $_timeformat = '%b %e, %Y, %l:%i%p';	// Mysql time format.
		var $_timestamp = 'M j, Y, g:iA';			// PHP timestamp format.
		var $_defaults = array(
			'groups'						=> array(),
			'sizes' 						=> array(),	
			'access'	=>	'activate_plugins',
		);
		var $_groupdefaults = array(
			'accordions' => array(
			
			)
		);
		
		var $_widget = 'Display an Accordion.';
		var $_widgetdefaults = array(
			'group'								=>		'',
		);
		
		var $instance_count = 0;
		
		// Default constructor. This is run when the plugin first runs.
		function pluginbuddy_accordion() {
			$this->_pluginPath = dirname( __FILE__ );
			$this->_pluginRelativePath = ltrim( str_replace( '\\', '/', str_replace( rtrim( ABSPATH, '\\\/' ), '', $this->_pluginPath ) ), '\\\/' );
			$this->_pluginURL = site_url() . '/' . $this->_pluginRelativePath;
			$this->_pluginBase = plugin_basename( __FILE__  );
			if ( isset( $_SERVER['HTTPS'] ) && $_SERVER['HTTPS'] == 'on' ) { $this->_pluginURL = str_replace( 'http://', 'https://', $this->_pluginURL ); }
			$this->_selfLink = array_shift( explode( '?', $_SERVER['REQUEST_URI'] ) ) . '?page=' . $this->_var;
			
			$this->load();
			foreach( $this->_options['sizes'] as $size ) {
				add_image_size( 'pb_accordion_' . $size['image_width'] . 'x' . $size['image_height'], $size['image_width'], $size['image_height'], true);
			}
			
			if ( is_admin() ) { // Runs when in the dashboard.
				add_action( 'init', array( &$this, 'upgrader_register' ), 50 );
				add_action( 'init', array( &$this, 'upgrader_select' ), 100 );
				add_action( 'init', array( &$this, 'upgrader_instantiate' ), 101 );
				//require_once( $this->_pluginPath . '/lib/updater/updater.php' );
				
				require_once( $this->_pluginPath . '/classes/admin.php' );
				register_activation_hook( $this->_pluginPath, array( &$this, 'activate' ) ); // Run some code when plugin is activated in dashboard.
				
				
			} else { // Runs when in non-dashboard parts of the site.
				add_shortcode( 'pb_accordion', array( &$this, 'shortcode' ) );
				
			
			} //end if is_admin
			add_action( 'init', array( &$this, 'init' ) );
		} //end constructor
		
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
		
		function init() {
			//Register our scripts and styles
			wp_register_style( 'pb_accordion_css', $this->_pluginURL . '/css/thumbsup.css', array(), $this->_version, 'all' );
			wp_register_script( 'pb_accordion_js', $this->_pluginURL . '/js/thumbsup.js', array( 'jquery' ), $this->_version, true );

			//* Localization Code */
			load_plugin_textdomain( 'it-l10n-accordion', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
						
			
			//Let's setup a content filter
			////Create our own version of the_content so that others can't accidentally loop into our output - Taken from default-filters.php, shortcodes.php, and media.php
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
				add_filter( 'pb_the_content', 'do_shortcode', 11);
			} //end has_filter
			
			//Create the hidden post types
			$args = array(
				'labels' => array(),
				'public' => true,
				'publicly_queryable' => true,
				'show_ui' => false, 
				'rewrite' => true,
				'query_var' => true,
				'capability_type' => 'post',
				'exclude_from_search' => true,
				'hierarchical' => false,
				'show_in_nav_menus' => false,
				'menu_position' => 1000,
				'supports' => array(
					'title',  'thumbnail', 'editor', 'excerpt', 'custom-fields', 'page-attributes'
				)
			);
			
			register_post_type( 'pb_accordion_group',$args );
			register_post_type( 'pb_accordion_child', $args );
			
			//Create a public post type			
			$labels = array(
			    'name' => __('Accordion Items', 'it-l10n-accordion' ),
			    'singular_name' =>__('Accordion Item', 'it-l10n-accordion' ),
			    'add_new' => __('Add New Item', 'it-l10n-accordion' ),
			    'add_new_item' => __('Add New Accordion Item', 'it-l10n-accordion'),
			    'edit_item' => __('Edit Accordion Item', 'it-l10n-accordion'),
			    'new_item' => __('New Accordion Item', 'it-l10n-accordion'),
			    'view_item' => __('View Accordion Item', 'it-l10n-accordion'),
			    'search_items' => __('Search Accordion Items', 'it-l10n-accordion'),
			    'not_found' =>  __('No items found', 'it-l10n-accordion'),
			    'not_found_in_trash' => __('No items found in Trash', 'it-l10n-accordion'), 
			    'parent_item_colon' => '',
			    'menu_name' => 'Accordions'
			  );
			  $args = array(
			  	'label' => 'Accordions',
				'labels' => $labels,
				'public' => true,
				'publicly_queryable' => true,
				'show_ui' => true, 
				'rewrite' => true,
				'query_var' => true,
				'capability_type' => 'post',
				'exclude_from_search' => true,
				'hierarchical' => false,
				'show_in_nav_menus' => true,
				'menu_position' => 21,
				'menu_icon' => $this->_pluginURL . '/images/accordion-blank.png',
				'supports' => array(
					'title',  'thumbnail', 'editor'
				)
			);
		

			register_post_type( 'pb_accordion_items', $args );
			
			//Add Shortcode
			add_action('media_buttons_context', array( &$this, 'add_media_button') );
			add_action( 'wp_ajax_pb_accordion_shortcode_add', array( &$this, 'shortcode_popup' ) );
				
		} //end init
		
		/**
		* add_media_button()
		* 
		* Displays an icon in the media bar when editing a post or page
		*
		* @param		string    $context	Media bar string
		* @return		string    Updated context variable with shortcode button added
		*/
		function add_media_button( $context ) {	
			$image_btn = $this->_pluginURL . '/images/shortcode_icon.png';
			
			$out = '<a href="' . esc_url( admin_url( 'admin-ajax.php' ) ) . '?action=pb_accordion_shortcode_add&width=450&height=300&TB_iframe=true" class="thickbox" title="' . __("Add Accordion Shortcode", 'it-l10n-accordion' ) . '"><img src="'.$image_btn.'" alt="' . __("Add Accordion Shortcode", 'it-l10n-accordion' ) . '" /></a>';
			return $context . $out;
		} //end add_media_button
		
		function shortcode_popup() {
			?>
			<html>
				<head>
				<title><?php _e( 'Insert Accordion Shortcode', 'it-l10n-accordion' ); ?></title>
				<?php
				wp_enqueue_script( 'jquery' );
				wp_admin_css( 'global' );
				wp_admin_css();
				wp_admin_css( 'colors' );
				do_action('admin_print_styles');
				do_action('admin_print_scripts');
				do_action('admin_head');
				
				?>
				<script type="text/javascript">
				var ajaxurl = "<?php echo esc_js( admin_url( 'admin-ajax.php' ) ); ?>";
				jQuery( document ).ready(
					function( $ ) {
						$( "#pb_accordion_form" ).bind( "submit", function() {
							var $selected = $( '#pb_accordion_select' ).val();
							var response = "[pb_accordion id='" + $selected + "']";
							var win = parent.window.dialogArguments || parent.opener || parent.parent || parent.top;
							win.send_to_editor( response );			
							return false;
						} );
					}
				);
				</script>
				</head>
				<body>
				<div class='wrap'>
					<form id="pb_accordion_form" method='post' action='<?php esc_url( admin_url( 'admin-ajax.php' ) ); ?>'>
				<?php
				$posts = get_posts( array( 'post_type' => 'pb_accordion_group', 'posts_per_page' => -1, 'numberposts' => -1 ) );
			
			if ( !$posts ) {
				esc_html_e( 'You must create an Accordion to place this widget. Please do so within the plugin\'s page.', 'it-l10n-accordion' );
			} else {
				?>
			<label for="pb_accordion_select">
				<?php esc_html_e( 'Select an Accordion:', 'it-l10n-accordion' ); ?>
				<select  id="pb_accordion_select" name="pb_accordion_select">
					<?php
					foreach ( $posts as $post ) {
						?>
						<option value='<?php echo esc_attr( absint( $post->ID ) ); ?>'><?php echo esc_html( apply_filters( 'the_title', $post->post_title ) ); ?></option>
						<?php
					} //end foreach $posts
					?>
				</select>
			</label>
			<div class="submit">
                 <input class='button-primary' type="submit" name="update" value="<?php esc_attr_e('Insert Shortcode', 'it-l10n-accordion' ) ?>" />
               </div><!--/.submit-->
		<?php
			} //end if $posts
			?>
                </form>
				</div><!--/wrap-->
				</body>
			</html>
			<?php
			exit;
		} //end shortcode_popup
		
		
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
		
		
		function get_accordion_defaults() {
			$defaults = array(
				'thumb_link' => 'off',
				'selected_index' => 0,
				'thumbnail_width' => 0,
				'thumbnail_height' => 0,
				'theme' => 'accordion-vertical-1',
				'post_thumbnail' => 'on',
				'hover_items' => 'off',
				'thumbnail_size' => 'thumbnail',
				'accordion_max_width' => 500,
				'accordion_max_height' => 300,
				'accordion_item_max_width' => 500,
				'accordion_item_max_height' => 300,
				'accordion_slide_up' => 500,
				'accordion_slide_down' => 500,
				'accordion_slide_left' => 500,
				'accordion_slide_right' => 500,
				'accordion_tab_type' => 'thumbnail',
			);
			return $defaults;
		} //end get_accordion_defaults
		
		function sanitize_css_integer( $width_or_height ) {
			if ( is_numeric( $width_or_height ) ) {
				return absint( $width_or_height ) . 'px';
			} else {
				if ( substr( $width_or_height, -1 ) == '%' ) { /*percentages should be on the end */
					preg_match( '/(\d\d?\d?)/', $width_or_height, $matches );
					if ( !$matches ) {
						return absint( $width_or_height );
					} else {
						return absint( $matches[ 0 ] ) . '%';
					}
				} else {
					return absint( $width_or_height );
				}
			}
			
		} //end sanitize_css_integer
		// Same as widget but return.
		function shortcode( $atts ) {
			//For some reason, WP doesn't always load the has_post_thumbnail function
			require_once( ABSPATH . WPINC . '/post-thumbnail-template.php' );
			$this->load();
			extract( wp_parse_args( $atts, array( 'id' => 0 ) ) );
			$parent_id = absint( $id );
			$is_widget = isset( $atts[ 'widget_id' ] ) ? esc_attr( '_' . $atts[ 'widget_id' ] ) : '';
			$accordion_settings = get_post_meta( $id, 'accordion_settings', true );
			$defaults = $this->get_accordion_defaults();
			if ( !is_array( $accordion_settings ) ) {
				update_post_meta( $id, 'accordion_settings', $defaults );
				$accordion_settings = array();	
			}
		
			$accordion_settings = wp_parse_args( $accordion_settings, $defaults );

			$children = get_children( 
				array(
					'post_type' => 'pb_accordion_child',
					'posts_per_page' => -1,
					'numberposts' => -1,
					'post_parent' => $id,
					'orderby' => 'menu_order',
					'order' => 'ASC'
				)
			);
			$accordion_class_name = esc_attr( $accordion_settings[ 'theme' ] );
			if ( !$children ) return '';
			$stylesheet_url = sprintf( "%s/css/%s.css", $this->_pluginURL, $accordion_settings[ 'theme' ] );
			$is_horizontal = false;
			if ( strstr( $accordion_settings[ 'theme' ], 'horizontal' ) ) $is_horizontal = true;
			ob_start();
			wp_enqueue_script( 'jquery-tools', $this->_pluginURL . '/js/jquery.tools.min.js', array( 'jquery' ), '1.2.5', true );
			if ( $accordion_settings[ 'theme' ] != 'accordion-custom' ) {
				wp_enqueue_style( 'jquery-tools-' . $accordion_class_name, $stylesheet_url );
			}
			if ( !wp_script_is( 'jquery-tools', 'done' ) ) wp_print_scripts( array( 'jquery-tools' ) );
			if ( !wp_style_is( 'jquery-tools-' . $accordion_class_name, 'done' ) ) wp_print_styles( array( 'jquery-tools-' . $accordion_class_name) );
			
			?>
			<script type='text/javascript'>
				jQuery(document).ready(function( $ ) {
					<?php
					$event_hover = '';
					if ( $accordion_settings[ 'hover_items' ] == 'on' ) {
						$event_hover = ",event: 'mouseover'";
					}
					if ( !$is_horizontal ) {
					?>
					jQuery( "#accordion_group_<?php echo esc_js( absint( $parent_id ) ) . $is_widget; ?>" ).tabs( "#accordion_group_<?php echo esc_js( absint( $parent_id ) ) . $is_widget; ?> .accordion div.pane", {tabs: 'h2.accordion-item', effect: 'slide', initialIndex: <?php echo esc_js( $accordion_settings[ 'selected_index' ] ); ?><?php echo $event_hover; ?>} );
					<?php
					} else {
					?>
					jQuery( "#accordion_group_<?php echo esc_js( absint( $parent_id ) ) . $is_widget; ?>" ).tabs( "#accordion_group_<?php echo esc_js( absint( $parent_id ) ) . $is_widget; ?> .<?php echo $accordion_class_name; ?> .accordion-item div", {tabs: 'h2.horizontal-tab', effect: 'horizontal', initialIndex: <?php echo esc_js( $accordion_settings[ 'selected_index' ] ); ?><?php echo $event_hover; ?>} );
					<?php
					} //end if horizontal or vertical
					?>
					$.tools.tabs.addEffect("slide", function(i, done) {

						// 1. upon hiding, the active pane has a ruby background color
						this.getPanes().slideUp( <?php echo esc_js( absint( $accordion_settings[ 'accordion_slide_up' ] ) ); ?> );
					
						// 2. after a pane is revealed, its background is set to its original color (transparent)
						this.getPanes().eq(i).slideDown( <?php echo esc_js( absint( $accordion_settings[ 'accordion_slide_down' ] ) ); ?>, function()  {
							$(this).css({backgroundColor: 'transparent'});
					
							// the supplied callback must be called after the effect has finished its job
							done.call();
						});
					}); //end addEffect slide
					var pb_pane_width = 0;
					$.tools.tabs.addEffect("horizontal", function(i, done) {
						var tabwidth = this.getCurrentPane().width();
						if ( tabwidth == 0 ) { tabwidth = pb_pane_width; }
						else { pb_pane_width = tabwidth; } 
						var pane = this;
						this.getCurrentPane().animate( {width: 0}, <?php echo esc_js( absint( $accordion_settings[ 'accordion_slide_left' ] ) ); ?>, function() {
							jQuery( this ).hide();
							pane.getPanes().eq( i ).show();
							pane.getPanes().eq( i ).animate( { width: tabwidth }, <?php echo esc_js( absint( $accordion_settings[ 'accordion_slide_right' ] ) ); ?>, function() {
								done.call();
							} );
						} );
						
				}); //end addEffect horizontal
				} );
			</script>
			<?php
			$item_styles_array = array();
			$item_styles_array[ 'max-width' ] = $this->sanitize_css_integer( $accordion_settings[ 'accordion_max_width' ] );
			if ( $is_horizontal ) {
				$item_styles_array[ 'height' ] = $this->sanitize_css_integer( $accordion_settings[ 'accordion_max_height' ] );
			}
			$item_styles = '';
			foreach ( $item_styles_array as $key => $style) {
				$item_styles .= sprintf( "%s: %s;", $key, $style );
			}
			?>
			<div id='accordion_group_<?php echo esc_attr( absint( $parent_id ) ) . $is_widget; ?>'>
			<div style='<?php echo esc_attr( $item_styles ); ?>' class="accordion <?php echo $accordion_class_name; ?>">
			<?php
			$count = 0;
			foreach ( $children as $child ) {
				$post_id = get_post_meta( $child->ID, 'post_id', true );
				$post = false;
				if ( $post_id ) {
					$post = get_post( $post_id );
				}
				$post_thumbnail = false;
				if ( $post ) {
					if ( $accordion_settings[ 'post_thumbnail' ] == 'on' && has_post_thumbnail( $post_id ) ) {
						if ( $accordion_settings[ 'thumbnail_size' ] == 'custom' ) {
							$width = absint( $accordion_settings[ 'thumbnail_width' ] );
							$height = absint( $accordion_settings[ 'thumbnail_height' ] );
							$post_thumbnail = get_the_post_thumbnail( $post_id, 'pb_accordion_' . $width . 'x' . $height );
						} else {
							$post_thumbnail = get_the_post_thumbnail( $post_id, (string)$accordion_settings[ 'thumbnail_size' ] );
						}
					}
					$post_title = apply_filters( 'the_title', $post->post_title );
					$post_content = apply_filters( 'pb_the_content', $post->post_content );
				} else {
					$post_title = apply_filters( 'the_title', $child->post_title );
					$post_content = apply_filters( 'pb_the_content', $child->post_content );
				}
				if ( !$is_horizontal ) {
				?>
				<h2 class='accordion-item'><?php echo $post_title ?></h2>
				<?php
					$item_styles_array = array();
					$item_styles_array[ 'max-height' ] = $this->sanitize_css_integer( $accordion_settings[ 'accordion_item_max_height' ] ); 
					$item_styles = '';
					foreach ( $item_styles_array as $key => $style) {
						$item_styles .= sprintf( "%s: %s;", $key, $style );
					}
				?>
				<div  class='pane' style='<?php echo esc_attr( $item_styles ); ?>'>
					<?php
						if ( $post_thumbnail ) { //check for thumbnail
							if ( $accordion_settings[ 'thumb_link' ] == 'on') { // if option is on, then wrap thumbnail with permalink to post_id
								?>
								<div class='accordion_thumbnail'><a href="<?php echo get_permalink($post_id);?>"><?php echo $post_thumbnail; ?></a></div>
								<?php
							}else if ( $accordion_settings[ 'thumb_link' ] == 'off' ) { // if off, then display as usual.
								?>
								<div class='accordion_thumbnail'><?php echo $post_thumbnail; ?> </div>
								<?php
							}
						} //end if $post_thumbnail
					?>
					<div class='accordion_content'><?php echo $post_content; ?></div>
				</div>
				<?php
				
				} else {
					//We're in horizontal mode
					if ( !$post_thumbnail ) continue;
					?>
					<div class='accordion-item'>
					<h2 class='horizontal-tab<?php echo $accordion_settings[ 'accordion_tab_type' ]  != 'thumbnail' ? ' accordion_vertical_text_container' : ''; ?>'>
					<?php
						if ( $accordion_settings[ 'accordion_tab_type' ] == 'thumbnail' ) {
							echo $post_thumbnail;
						} else {
							echo $post_title;
						   
						} //end if accordion_tab_type
					?>					
					</h2>
				<?php
					$item_styles_array = array();
					$item_styles_array[ 'width' ] = 'auto';
					$item_styles_array[ 'overflow' ] = 'hidden';
					$item_styles_array[ 'max-width' ] = $this->sanitize_css_integer( $accordion_settings[ 'accordion_item_max_width' ] );
					if ( $count == $accordion_settings['selected_index'] ) { $item_styles_array[ 'display' ] = 'block'; }
					if ( $count > 0 ) $item_styles_array[ 'width' ] = $item_styles_array[ 'max-width' ];
					$item_styles = '';
					foreach ( $item_styles_array as $key => $style) {
						$item_styles .= sprintf( "%s: %s;", $key, $style );
					}
				?>
				<div style='<?php echo esc_attr( $item_styles ); ?>'>
					<?php
					if ( $accordion_settings[ 'accordion_tab_type' ]  != 'thumbnail' && $post_thumbnail ) {
						?>
						<p class='accordion_thumbnail'><?php echo $post_thumbnail; ?></p>
						<?php
					} //end show thumbnail
					?>
					<?php echo $post_content; ?>
				</div></div><!--/.accordion-item-->
					<?php
				} //end if is_horizontal
				$count += 1;
			} //end foreach $child
			?>
			</div><!--/#accordion-->
		
		</div><!--accordion_group_<?php echo esc_attr( absint( $parent_id ) ); ?>-->


			<?php
			return ob_get_clean();
		} //end shortcode
		
		
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
			echo $this->shortcode( $instance );
		} //end widget
		
		
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
			$defaults = array(
				'accordion' => 0,
				'id' => 0,
			);
			$instance = wp_parse_args( $instance, $defaults );
			//die( print_r( $instance, true ) );
			$posts = get_posts( array( 'post_type' => 'pb_accordion_group', 'posts_per_page' => -1, 'numberposts' => -1 ) );
			
			if ( !$posts ) {
				esc_html_e( 'You must create an Accordion to place this widget. Please do so within the plugin\'s page.', 'it-l10n-accordion' );
			} else {
				?>
				<label for="<?php echo $widget->get_field_id( 'accordion' ); ?>">
					<?php esc_html_e( 'Select an Accordion:', 'it-l10n-accordion' ); ?>
					<select class="widefat" id="<?php echo $widget->get_field_id( 'accordion' ); ?>" name="<?php echo $widget->get_field_name('accordion'); ?>">
						<?php
						foreach ( $posts as $post ) {
							?>
							<option value='<?php echo esc_attr( absint( $post->ID ) ); ?>' <?php selected( $instance[ 'accordion' ], $post->ID ); ?>><?php echo esc_html( apply_filters( 'the_title', $post->post_title ) ); ?></option>
							<?php
						} //end foreach $posts
						?>
					</select>
				</label>
		<?php
			} //end if $posts
		} //end function widget_form
		
		
		
	} // End class
	
	$pluginbuddy_accordion = new pluginbuddy_accordion(); // Create instance
	require_once( dirname( __FILE__ ) . '/classes/widget.php');
} //end class_exists pluginbuddy_accordion

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
} //end function_exists ithemes_filter_image_downsize
?>
