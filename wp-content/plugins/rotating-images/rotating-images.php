<?php
/**
 *
 * Plugin Name: Rotating Images
 * Plugin URI: http://pluginbuddy.com/
 * Description: DisplayBuddy Series - Rotate images using transitions, such as fade or slide, or static random image on page load.
 * Version: 1.0.32
 * Author: The PluginBuddy Team
 * Author URI: http://pluginbuddy.com/
 *
 */

/*
Written by Chris Jean for iThemes.com
Extended by Dustin Bolton

Version History
	See history.txt
*/


if ( ! class_exists( 'iThemesRotatingImages' ) ) {
	class iThemesRotatingImages {
		var $_version = '1.0.32';
		var $_updater = '1.0.7';
	
		var $_var = 'ithemes-rotating-images';
		var $_name = 'Rotating Images';
		var $_title = 'Rotating Images';
		var $_series = 'DisplayBuddy';
		var $_page = 'ithemes-rotating-images';
		var $_groupID;
		
		var $_defaults = array(
			'width'					=> '100',
			'height'				=> '100',
			'sleep'					=> '2',
			'fade'					=> '1',
			'fade_sort'				=> 'ordered',
			'enable_fade'				=> '1',
			'link'					=> '',
			'open_new_window'			=> '',
			'enable_overlay'			=> '0',
			'enable_slide'				=> '0',
			'double_fade'				=> '0',
			'overlay_text_alignment'		=> 'center',
			'overlay_text_vertical_position'	=> 'middle',
			'overlay_text_padding'			=> '10',
			'overlay_header_text'			=> '',
			'overlay_header_size'			=> '36',
			'overlay_header_color'			=> '#FFFFFF',
			'overlay_subheader_text'		=> '',
			'overlay_subheader_size'		=> '18',
			'overlay_subheader_color'		=> '#FFFFFF',
			
			'variable_width'			=> true,
			'variable_height'			=> true,
			'force_disable_overlay'			=> false,
			'groups'				=> array(),
		);
		
		var $_options = array();
		var $_optionsupdater = array();
		var $_groups = array(); // All group options.
		
		var $_class = '';
		var $_initialized = false;
		
		var $_usedInputs = array();
		var $_selectedVars = array();
		var $_pluginPath = '';
		var $_pluginRelativePath = '';
		var $_pluginURL = '';
		var $_pageRef = '';
		
		var $_instanceCount = 0; // Counter for widget numbering for jquery.
		
		function iThemesRotatingImages() {
			$this->_pluginPath = dirname( __FILE__ );
			$this->_pluginRelativePath = ltrim( str_replace( '\\', '/', str_replace( rtrim( ABSPATH, '\\\/' ), '', $this->_pluginPath ) ), '\\\/' );
			$this->_pluginURL = site_url() . '/' . $this->_pluginRelativePath;
			if ( isset( $_SERVER['HTTPS'] ) && $_SERVER['HTTPS'] == 'on' ) { $this->_pluginURL = str_replace( 'http://', 'https://', $this->_pluginURL ); }
			$this->_selfLink = array_shift( explode( '?', $_SERVER['REQUEST_URI'] ) ) . '?page=' . $this->_var;
			
			
			$this->_defaults['link'] = get_option( 'home' );
			$this->_defaults['overlay_header_text'] = get_bloginfo( 'name' );
			$this->_defaults['overlay_subheader_text'] = get_bloginfo( 'description' );
			
			$this->_defaults = apply_filters( 'it_rotating_images_options', $this->_defaults );
			
			
			$this->_setVars();
			
			// Only run admin backend if on admin page for this plugin or non-admin page below...
			if ( is_admin() && isset( $_GET['page'] ) && ( $_GET['page'] === $this->_page ) ) {
				add_action( 'admin_init', array( &$this, 'init' ) );
			} else {
				add_action( 'template_redirect', array( &$this, 'init' ) ); // non-admin page.
				add_shortcode('it-rotate', array( &$this, 'shortcode' ) );
			}
			
			add_action( 'admin_menu', array( &$this, 'admin_menu' ) );
			add_action( 'ithemes_rotating_images_fade_images', array( &$this, 'fadeImages' ), 10, 2 );
			
			if ( is_admin() ) {
				add_action( 'init', array( &$this, 'upgrader_register' ), 50 );
				add_action( 'init', array( &$this, 'upgrader_select' ), 100 );
				add_action( 'init', array( &$this, 'upgrader_instantiate' ), 101 );
				register_activation_hook(__FILE__, array(&$this, '_activate'));
				require_once(dirname( __FILE__ ).'/lib/updater/updater.php');
			}
		}
		
		// REMOVE THIS EVENTUALLY - migrates from 0.1.32 to newer.
		function _activate() {
			$old_ver = get_option('ithemes_rotating_images');
			//echo'<i>Migrated old version of PluginBuddy Rotating Images to new.</i>';
			if ( is_array( $old_ver) ) {
				add_option($this->_var, $old_ver, '', 'no'); // No autoload.
				update_option($this->_var, $old_ver);
				delete_option('ithemes_rotating_images');
			}
		}
		// END REMOVE
			
			
		function init() {
			$this->load();
		}
		
		function shortcode($atts) {
			extract(shortcode_atts(array(
				'group' => '0'
			), $atts));
			return $this->fadeImages($atts['group'],false);
		}
		
		function addPages() {
			global $wp_theme_name, $wp_theme_page_name;
			
			//add_menu_page('Rotating Images', $this->_name, 'administrator', $this->_name, array( &$this, 'index' ), $this->_pluginURL.'/images/pluginbuddy.png');
			/*
			if ( ! empty( $wp_theme_page_name ) )
				$this->_pageRef = add_submenu_page( $wp_theme_page_name, $this->_name, 'Rotating Images', 'edit_themes', $this->_page, array( &$this, 'index' ) );
			else
				$this->_pageRef = add_theme_page( $wp_theme_name . ' ' . $this->_name, $wp_theme_name . ' ' . $this->_name, 'edit_themes', $this->_page, array( &$this, 'index' ) );
			*/
			add_action( 'admin_print_scripts-' . $this->_pageRef, array( $this, 'addAdminScripts' ) );
			add_action( 'admin_print_styles-' . $this->_pageRef, array( $this, 'addAdminStyles' ) );
		}
		
		function admin_menu() {
			// Handle series menu. Create series menu if it does not exist.
			global $menu;
			$found_series = false;
			foreach ( $menu as $menus => $item ) {
				if ( $item[0] == $this->_series ) {
					$found_series = true;
				}
			}
			if ( $found_series === false ) {
				add_menu_page( $this->_series . ' Getting Started', $this->_series, 'administrator', 'pluginbuddy-' . strtolower( $this->_series ), array(&$this, 'view_gettingstarted'), $this->_pluginURL.'/images/pluginbuddy.png' );
				add_submenu_page( 'pluginbuddy-' . strtolower( $this->_series ), $this->_name.' Getting Started', 'Getting Started', 'administrator', 'pluginbuddy-' . strtolower( $this->_series ), array(&$this, 'view_gettingstarted') );
			}
			// Register for getting started page
			global $pluginbuddy_series;
			if ( !isset( $pluginbuddy_series[ $this->_series ] ) ) {
				$pluginbuddy_series[ $this->_series ] = array();
			}
			$pluginbuddy_series[ $this->_series ][ $this->_name ] = $this->_pluginPath;
			
			add_submenu_page( 'pluginbuddy-' . strtolower( $this->_series ), $this->_name, $this->_name, 'administrator', $this->_var, array(&$this, 'index'));
		}
		
		function view_gettingstarted() {
			echo '<link rel="stylesheet" href="' . $this->_pluginURL . '/css/admin.css" type="text/css" media="all" />';
			require('classes/view_gettingstarted.php');
		}
		function admin_scripts() {
/*
			wp_enqueue_script( 'jquery' );
			wp_print_scripts( 'jquery' );
			wp_enqueue_script( 'pluginbuddy-tooltip-js', $this->_pluginURL . '/js/tooltip.js' );
			wp_print_scripts( 'pluginbuddy-tooltip-js' );
			wp_enqueue_script( 'pluginbuddy-swiftpopup-js', $this->_pluginURL . '/js/swiftpopup.js' );
			wp_print_scripts( 'pluginbuddy-swiftpopup-js' );
			wp_enqueue_script( 'pluginbuddy-'.$this->_var.'-admin-js', $this->_pluginURL . '/js/admin.js' );
			wp_print_scripts( 'pluginbuddy-'.$this->_var.'-admin-js' );
			echo '<link rel="stylesheet" href="'.$this->_pluginURL . '/css/admin.css" type="text/css" media="all" />';
*/
$this->addAdminStyles();
$this->addAdminScripts();
		}
		function get_feed( $feed, $limit, $append = '', $replace = '' ) {
			require_once(ABSPATH.WPINC.'/feed.php');  
			$rss = fetch_feed( $feed );
			if (!is_wp_error( $rss ) ) {
				$maxitems = $rss->get_item_quantity( $limit ); // Limit 
				$rss_items = $rss->get_items(0, $maxitems); 
				
				echo '<ul class="pluginbuddy-nodecor">';

				$feed_html = get_transient( md5( $feed ) );
				if ( $feed_html == '' ) {
					foreach ( (array) $rss_items as $item ) {
						$feed_html .= '<li>- <a href="' . $item->get_permalink() . '">';
						$title =  $item->get_title(); //, ENT_NOQUOTES, 'UTF-8');
						if ( $replace != '' ) {
							$title = str_replace( $replace, '', $title );
						}
						if ( strlen( $title ) < 30 ) {
							$feed_html .= $title;
						} else {
							$feed_html .= substr( $title, 0, 32 ) . ' ...';
						}
						$feed_html .= '</a></li>';
					}
					set_transient( md5( $feed ), $feed_html, 300 ); // expires in 300secs aka 5min
				}
				echo $feed_html;
				
				echo $append;
				echo '</ul>';
			} else {
				echo 'Temporarily unable to load feed...';
			}
		}
		

		
		function addAdminStyles() {
			wp_enqueue_style( 'thickbox' );
			
			wp_enqueue_style( $this->_var . '-rotating-images', $this->_pluginURL . '/css/admin-style.css' );
		}
		
		function addAdminScripts() {
			global $wp_scripts;
			
			$queue = array();
			
			foreach ( (array) $wp_scripts->queue as $item )
				if ( ! in_array( $item, array( 'page', 'editor', 'editor_functions', 'tiny_mce', 'media-upload', 'post' ) ) )
					$queue[] = $item;
			
			$wp_scripts->queue = $queue;
			
			
			wp_enqueue_script( 'jquery' );
			wp_enqueue_script( 'thickbox' );
			
			wp_enqueue_script( $this->_var . '-prototype', $this->_pluginURL . '/js/prototype.js' );
			wp_print_scripts( $this->_var . '-prototype' );
			wp_enqueue_script( $this->_var . '-color-methods', $this->_pluginURL . '/js/colorpicker/ColorMethods.js' );
			wp_print_scripts( $this->_var . '-color-methods' );
			wp_enqueue_script( $this->_var . '-color-value-picker', $this->_pluginURL . '/js/colorpicker/ColorValuePicker.js' );
			wp_print_scripts( $this->_var . '-color-value-picker' );
			wp_enqueue_script( $this->_var . '-slider', $this->_pluginURL . '/js/colorpicker/Slider.js' );
			wp_print_scripts( $this->_var . '-slider' );
			wp_enqueue_script( $this->_var . '-color-picker', $this->_pluginURL . '/js/colorpicker/ColorPicker.js' );
			wp_print_scripts( $this->_var . '-color-picker' );
			
			wp_enqueue_script( $this->_var . '-toolkit', $this->_pluginURL . '/js/javascript-toolbox-toolkit.js' );
			wp_print_scripts( $this->_var . '-toolkit' );
			if ( isset( $_GET['group_id'] ) ) { // Only show when viewing a group to avoid errors.
				wp_enqueue_script( $this->_var . '-rotating-images', $this->_pluginURL . '/js/admin-rotating-images.js' );
				wp_print_scripts( $this->_var . '-rotating-images');
			}
		}
		
		function _setVars() {
			$this->_class = get_class( $this );
			
			$this->_pluginPath = dirname( __FILE__ );
			$this->_pluginRelativePath = ltrim( str_replace( '\\', '/', str_replace( rtrim( ABSPATH, '\\\/' ), '', $this->_pluginPath ) ), '\\\/' );
			$this->_pluginURL = get_option( 'siteurl' ) . '/' . $this->_pluginRelativePath;
			if ( isset( $_SERVER['HTTPS'] ) && $_SERVER['HTTPS'] == 'on' ) {
				$this->_pluginURL = str_replace( 'http://', 'https://', $this->_pluginURL );
			}
			$this->_selfLink = array_shift( explode( '?', $_SERVER['REQUEST_URI'] ) ) . '?page=' . $this->_page;
		}
		
		
		// Options Storage ////////////////////////////
		
		function save() {
			// This is re-added later in this function.
			$options_updater = $this->_options['updater'];
			unset( $this->_options['updater'] );
			
			// Copy array of groups under options into groups array
			if ( isset( $this->_options['groups'] ) )
				$this->_groups['groups'] = $this->_options['groups'];
			
			if ( isset( $this->_groupID ) ) { // Saving within a group to copy current settings into groups array
				$this->_groups['groups'][$this->_groupID]['options']=$this->_options; // Copy current settings into proper groups array position
				if ( is_array($this->_groups['groups'][$this->_groupID]['options']['groups']) ) {
					unset($this->_groups['groups'][$this->_groupID]['options']['groups']); // clear temporary groups holder
				}
				// Moved the following into array checker.
				//unset($this->_groups['groups'][$this->_groupID]['options']['groups']);
			}
			
			$this->_groups['updater'] = $options_updater;
			
			add_option($this->_var, $this->_groups, '', 'no'); // No autoload.
			update_option($this->_var, $this->_groups);
			
			$this->_options['updater'] = $this->_groups['updater'];
			unset( $this->_groups['updater'] );
			
			return true;
		}
		
		function load() {
			$temp_options = get_option($this->_var);
			
			
			$options_updater = $temp_options['updater'];
			unset( $temp_options['updater'] );
			
			if (isset($_REQUEST['group_id'])) {  // Set group ID if passed via querystring
				$this->_groupID = (int) $_REQUEST['group_id']; // assign current group id number into variable
			}
			
			$errorcount = 0;
			
			if ( isset( $this->_groupID ) && isset( $temp_options['groups'][$this->_groupID]['options'] ) ) { // Load settings for within a group.
				$this->_options=$temp_options['groups'][$this->_groupID]['options']; // Load group settings into options.
				
				$this->_options['sleep'] = floatval( $this->_options['sleep'] );
				$this->_options['fade'] = floatval( $this->_options['fade'] );
				
				if ( $this->_options['sleep'] <= 0 )
					$this->_options['sleep'] = $this->_defaults['sleep'];
				if ( $this->_options['fade'] <= 0 )
					$this->_options['fade'] = $this->_defaults['fade'];
				if ( empty( $this->_options['fade_sort'] ) )
					$this->_options['fade_sort'] = 'ordered';
				
				foreach ( array( 'width', 'height', 'sleep', 'fade' ) as $option ) {
					if ( ! is_numeric( $this->_defaults[$option] ) )
						$this->_options[$option] = $GLOBALS[$this->_defaults[$option]];
					else if ( ( empty( $this->_options[$option] ) ) && ( '0' !== $this->_options[$option] ) )
						$this->_options[$option] = $this->_defaults[$option];
				}
				if ( empty( $this->_options['image_ids'] ) ) {
					if ( (! isset($_GET['group_id']) ) && ( ! isset($_POST['add_group']) ) ) { // Do not display warning in admin or group creation.
						$this->_errors[] = 'Warning: Empty Rotating Images Group! Upload images for this widget to function.';
						$errorcount = 1;
					}
				} else if ( ! is_array( reset( $this->_options['image_ids'] ) ) ) {
					$entries = array();
					
					$order = 1;
					
					foreach ( (array) $this->_options['image_ids'] as $id ) {
						$entry = array();
						$entry['attachment_id'] = $id;
						$entry['url'] = '';
						$entry['order'] = $order;
						
						$entries[] = $entry;
						
						$order++;
					}
					
					$this->_options['image_ids'] = $entries;
				}
				
				if ( ( false === $this->_defaults['variable_height'] ) && is_numeric( $this->_defaults['height'] ) )
					$this->_options['height'] = $this->_defaults['height'];
				if ( ( false === $this->_defaults['variable_width'] ) && is_numeric( $this->_defaults['width'] ) )
					$this->_options['width'] = $this->_defaults['width'];
			}
			
			
			if ( isset( $temp_options['groups'] ) ) {
				$this->_options['groups']=$temp_options['groups']; // Load all group names into options variable.
			}
			$this->_options['updater'] = $options_updater;
			
			/*			echo '<pre>';
			print_r( $this->_options['updater'] );
			echo '</pre>'; */
		}
		
		
		// Pages //////////////////////////////////////
		
		function index() {
			$action = ( isset( $_REQUEST['action'] ) ) ? $_REQUEST['action'] : '';
			
			if ( 'save' === $action )
				$this->saveForm();
			else if ( 'save_image' === $action )
				$this->saveImage();
			else if ( ! empty( $_POST['save_entry_order'] ) )
				$this->saveOrder();
			else if ( 'upload' === $action )
				$this->_uploadImage();
			else if ( ! empty( $_REQUEST['delete_images'] ) )
				$this->_deleteImages();
			else {
				if ( ! empty( $_POST['add_group'] ) )
					$this->_groupsCreate();
				elseif ( ! empty( $_POST['delete_group'] ) )
					$this->_groupsDelete();
				//$this->_groupsRender();
			}
			
			$this->admin_scripts();
			$this->_showForm();
		}
		
		function saveForm() {
			check_admin_referer( $this->_var . '-nonce' );

			foreach ( (array) explode( ',', $_POST['used-inputs'] ) as $name ) {
				$is_array = ( preg_match( '/\[\]$/', $name ) ) ? true : false;
				
				$name = str_replace( '[]', '', $name );
				$var_name = preg_replace( '/^' . $this->_var . '-/', '', $name );
				
				if ( $is_array && empty( $_POST[$name] ) )
					$_POST[$name] = array();
				
				if ( isset( $_POST[$name] ) && ! is_array( $_POST[$name] ) )
					$this->_options[$var_name] = stripslashes( $_POST[$name] );
				else if ( isset( $_POST[$name] ) )
					$this->_options[$var_name] = $_POST[$name];
				else
					$this->_options[$var_name] = '';
			}
			
			$errorCount = 0;
			
			if ( ( $this->_options['sleep'] != floatval( $this->_options['sleep'] ) ) || ( floatval( $this->_options['sleep'] ) <= 0 ) )
				$errorCount++;
			if ( ( $this->_options['fade'] != floatval( $this->_options['fade'] ) ) || ( floatval( $this->_options['fade'] ) <= 0 ) )
				$errorCount++;
			if ( ( $this->_options['height'] != intval( $this->_options['height'] ) ) || ( intval( $this->_options['height'] ) < 0 ) )
				$errorCount++;
			
			if ( $errorCount < 1 ) {
				$this->_options['sleep'] = floatval( $this->_options['sleep'] );
				$this->_options['fade'] = floatval( $this->_options['fade'] );
				
				if ( $this->_options['sleep'] <= 0 )
					$this->_options['sleep'] = $this->_defaults['sleep'];
				if ( $this->_options['fade'] <= 0 )
					$this->_options['fade'] = $this->_defaults['fade'];
				if ( empty( $this->_options['fade_sort'] ) )
					$this->_options['fade_sort'] = 'ordered';
				
				foreach ( array( 'width', 'height', 'sleep', 'fade' ) as $option )
					if ( ! is_numeric( $this->_defaults[$option] ) )
						$this->_options[$option] = $GLOBALS[$this->_defaults[$option]];
					elseif ( ( empty( $this->_options[$option] ) ) && ( '0' !== $this->_options[$option] ) )
						$this->_options[$option] = $this->_defaults[$option];
				
				if ( $this->save() )
					$this->_showStatusMessage( __( 'Settings updated', $this->_var ) );
				else
					$this->_showErrorMessage( __( 'Error while updating settings', $this->_var ) );
			}
			else {
				$this->_showErrorMessage( __( 'The fade options timing values must be numeric values greater than 0.', $this->_var ) );
				
				$this->_showErrorMessage( __ngettext( 'Please fix the input marked in red below.', 'Please fix the inputs marked in red below.', $errorCount ) );
			}
		}
		
		function saveOrder() {
			check_admin_referer( $this->_var . '-nonce' );
			
			/* OLD:

			
			foreach ( (array) $_POST as $var => $value ) {
				if ( preg_match( '/^' . $this->_var . '-entry-order-(\d+)$/', $var, $matches ) ) {
					
					$entry_id = $matches[1];
					
					if ( ! empty( $this->_options['image_ids'][$entry_id] ) && is_array( $this->_options['image_ids'][$entry_id] ) )
						$this->_options['image_ids'][$entry_id]['order'] = $value;
				}
			}
			*/
			
			$i =0;
			foreach ( (array) $_POST as $var => $value ) {
				if ( preg_match( '/^' . $this->_var . '-entry-order-(\d+)$/', $var, $matches ) ) {
					$image_id = $matches[1];
					$this->_options['image_ids'][$image_id]['order'] = $i;
					$i++;
				}
			}
			
			$this->_options['max_order'] = 500; // Reset
			
			$this->save();
			
			
			$this->_showStatusMessage( 'Successfully updated the entry order' );
		}
		
		function saveImage() {
			check_admin_referer( $this->_var . '-nonce' );
			
			if ( isset( $_POST[$this->_var . '-attachment_id'] ) )
				$attachment_id = $_POST[$this->_var . '-attachment_id'];
			
			if ( is_array( $_FILES['image_upload'] ) && ( 0 === $_FILES['image_upload']['error'] ) ) {
				require_once( $this->_pluginPath . '/lib/file-utility/file-utility.php' );
				
				$file = iThemesFileUtility::uploadFile( 'image_upload' );
				
				if ( is_wp_error( $file ) )
					$this->_errors[] = 'Unable to save uploaded image. Ensure that the web server has permissions to write to the uploads folder';
				else
					$attachment_id = $file['id'];
			}
			else if ( ! isset( $_POST['image_id'] ) )
				$this->_errors[] = 'You must use the browse button to select an image to upload.';
			else if ( empty( $attachment_id ) ) {
				$this->_errors[] = 'An unexpected error occurred. Unable to find the needed image attachment.';
				$this->_errors[] = 'Please click on the Rotating Images menu link and try again.';
			}
			
			if ( ! empty( $this->_errors ) ) {
				$this->_attachment_id = $attachment_id;
				return;
			}
			
			/*
			if ( -1 !== $_POST[$this->_var . '-order'] )
				$order = $_POST[$this->_var . '-order'];
			else {
				$order = 0;
				
				foreach ( (array) $this->_options['image_ids'] as $entry )
					if ( $entry['order'] > $order )
						$order = $entry['order'];
				
				$order++;
			}
			*/
			
			if ( !empty( $this->_options['max_order'] ) ) {
				$this->_options['max_order'] = $this->_options['max_order'] + 1;
			} else {
				$this->_options['max_order'] = 500;
			}
			
			$entry = array();
			$entry['attachment_id'] = $attachment_id;
			$entry['url'] = $_POST[$this->_var . '-url'];
			$entry['order'] = $this->_options['max_order'];
			
			if ( isset( $_POST['image_id'] ) && is_array( $this->_options['image_ids'][$_POST['image_id']] ) )
				$this->_options['image_ids'][$_POST['image_id']] = $entry;
			else
				$this->_options['image_ids'][] = $entry;
			
			$this->save();
			
			if ( isset( $_POST['image_id'] ) ) {
				$this->_showStatusMessage( 'Updated Image Settings' );
				unset( $_POST['image_id'] );
				unset( $_REQUEST['image_id'] );
			}
			else
				$this->_showStatusMessage( 'Added New Image' );
			
			unset( $_POST['action'] );
			unset( $_REQUEST['action'] );
		}
		
		function _deleteImages() {
			check_admin_referer( $this->_var . '-nonce' );
			
			require_once( $this->_pluginPath . '/lib/file-utility/file-utility.php' );
			
			$names = array();
			
			if ( ! empty( $_POST['entries'] ) && is_array( $_POST['entries'] ) ) {
				foreach ( (array) $_POST['entries'] as $id ) {
					$file_name = basename( get_attached_file( $this->_options['image_ids'][$id]['attachment_id'] ) );
					$names[] = $file_name;
					
					iThemesFileUtility::delete_file_attachment( $this->_options['image_ids'][$id]['attachment_id'] );
					
					if (isset($this->_options['image_ids'][$id])) {
						unset( $this->_options['image_ids'][$id] );
					}
				}
			}
			
			natcasesort( $names );
			
			if ( ! empty( $names ) ) {
				$this->save();
				$this->_showStatusMessage( 'Successfully deleted the following ' . __ngettext( 'image', 'images', count( $names ) ) . ': ' . implode( ', ', $names ) );
			}
			else
				$this->_showErrorMessage( 'No entries were selected for deletion' );
		}
		
		function _showForm() {
			if ( isset( $this->_addedAnimatedFile ) && ( true === $this->_addedAnimatedFile ) )
				$this->_showStatusMessage( 'An animated image was just uploaded. It may take a moment for this screen to fully render as the animation is resized.' );
			
	
	if ( isset( $_REQUEST['group_id'] ) && empty( $_REQUEST['cancelsave_group'] ) ) { // dustin
		
		if ( empty( $this->_options['height'] ) ) {
			echo 'WARNING: You must set a valid image width and height.';
		}
		
		$ratio = $this->_options['width'] / $this->_options['height'];
		
		$thumb_height = $thumb_width = 100;
		
		if ( $ratio > 1 )
			$thumb_height = intval( 100 / ( $this->_options['width'] ) * $this->_options['height'] );
		else
			$thumb_width = intval( 100 / ( $this->_options['height'] ) * $this->_options['width'] );
		
		
		require_once( $this->_pluginPath . '/lib/file-utility/file-utility.php' );
			
		if ( ! isset( $this->_errors ) && ! isset( $_REQUEST['image_id'] ) && ( ! isset( $_REQUEST['action'] ) || ( 'save_image' !== $_REQUEST['action'] ) ) ) : ?>
			<div class="wrap">
				<form id="posts-filter" enctype="multipart/form-data" method="post" action="<?php echo $this->_selfLink; ?>&group_id=<?php echo $this->_groupID; ?>">
					<?php wp_nonce_field( $this->_var . '-nonce' ); ?>
					<?php //$this->_addHiddenNoSave( 'group_id', 'save_image' ); ?>
					
					<h2>Rotating Images in Group (<a href="<?php echo $this->_selfLink; ?>">group list</a>)</h2>
					
					<?php if ( isset( $this->_options['image_ids'] ) && ( count( $this->_options['image_ids'] ) > 0 ) ) : ?>
						<div class="tablenav">
							<div class="alignleft actions">
								<?php $this->_addSubmit( 'delete_images', array( 'value' => 'Delete', 'class' => 'button-secondary delete' ) ); ?>
								<?php $this->_addSubmit( 'save_entry_order', array( 'value' => 'Save Order', 'class' => 'button-secondary' ) ); ?>
							</div>
							
							<br class="clear" />
						</div>
						
						<br class="clear" />
						
						<table class="widefat">
							<thead>
								<tr class="thead">
									<th scope="col" class="check-column"><input type="checkbox" class="check-all-entries" /></th>
									<th>Image</th>
									<th>File Name</th>
									<th>Link</th>
									<th class="num">Reorder</th>
								</tr>
							</thead>
							<tfoot>
								<tr class="thead">
									<th scope="col" class="check-column"><input type="checkbox" class="check-all-entries" /></th>
									<th>Image</th>
									<th>File Name</th>
									<th>Link</th>
									<th class="num">Reorder</th>
								</tr>
							</tfoot>
							<tbody>
								<?php
									$class = 'alternate';
									$order = 1;
									
									uksort( $this->_options['image_ids'], array( &$this, '_orderedSort' ) );
								?>
								<?php foreach ( (array) $this->_options['image_ids'] as $id => $entry ) : ?>
									<?php
										flush();
										
										$file_name = basename( get_attached_file( $entry['attachment_id'] ) );
										
										$thumb = iThemesFileUtility::resize_image( $entry['attachment_id'], $thumb_width, $thumb_height, true );
										
										$this->_options['entry-order-' . $id] = $entry['order'];
									?>
									<tr class="entry-row <?php echo $class; ?>" id="entry-<?php echo $id; ?>">
										<th scope="row" class="check-column">
											<input type="checkbox" name="entries[]" class="entries" value="<?php echo $id; ?>" />
										</th>
										<td>
											<?php if ( ! is_wp_error( $thumb ) ) : ?>
												<img src="<?php echo $thumb['url']; ?>" alt="<?php echo $thumb['file']; ?>" style="float:left; margin-right:10px;" />
											<?php else : ?>
												Thumbnail generation error: <?php echo $thumb->get_error_message(); ?>
											<?php endif; ?>
											<div class="row-actions" style="margin:0; padding:0;">
												<span class="edit"><a href="<?php echo $this->_selfLink; ?>&image_id=<?php echo $id; ?>&group_id=<?php echo $this->_groupID; ?>">Edit Image Settings</a></span>
											</div>
										</td>
										<td>
											<?php echo $file_name; ?>
										</td>
										<td>
											<a href="<?php echo $entry['url']; ?>" target="_blank" title="<?php echo $entry['url']; ?>"><?php echo $entry['url']; ?></a>
										</td>
										<td class="num">
											<div style="margin-bottom:5px;" class="entry-up"><img src="<?php echo $this->_pluginURL; ?>/images/blue-up.png" alt="move up" /></div>
											<div class="entry-down"><img src="<?php echo $this->_pluginURL; ?>/images/blue-down.png" alt="move down" /></div>
											<?php $this->_addHidden( 'entry-order-' . $id, array( 'class' => 'entry-order' ) ); ?>
										</td>
									</tr>
									<?php $class = ( $class === '' ) ? 'alternate' : ''; ?>
									<?php $order++; ?>
								<?php endforeach; ?>
							</tbody>
						</table>
						
						<div class="tablenav">
							<div class="alignleft actions">
								<?php $this->_addSubmit( 'delete_images', array( 'value' => 'Delete', 'class' => 'button-secondary delete' ) ); ?>
								<?php $this->_addSubmit( 'save_entry_order', array( 'value' => 'Save Order', 'class' => 'button-secondary' ) ); ?>
							</div>
							
							<br class="clear" />
						</div>
					<?php endif; ?>
				</form>
			</div>
			
			<br class="clear" />
		<?php endif; ?>
		
		
		<?php if ( ! isset( $this->_errors ) || isset( $_REQUEST['image_id'] ) || ( isset( $_REQUEST['action'] ) && ( 'save_image' === $_REQUEST['action'] ) ) ) : ?>
			<div class="wrap">
				<?php if ( ! isset( $_REQUEST['image_id'] ) ) : ?>
					<h2 id="addnew">Add New Image</h2>
				<?php else : ?>
					<h2>Edit Image Settings</h2>
				<?php endif; ?>
				
				<p>The uploaded image should be <?php echo "{$this->_options['width']}x{$this->_options['height']}"; ?> (<?php echo $this->_options['width']; ?> pixels wide by <?php echo $this->_options['height']; ?> pixels high).</p>
				<p>Images not matching the exact size will be resized and cropped to fit upon display.</p>
				
				<?php
					$this->_options['order'] = -1;
					$this->_options['group_id'] = $this->_groupID;
					
					if ( isset( $this->_errors ) ) {
						$this->_options['attachment_id'] = $this->_attachment_id;
						$this->_options['url'] = $_POST[$this->_var . '-url'];
						$this->_options['order'] = $_POST[$this->_var . '-order'];
						$this->_options['group_id'] = $_POST['group_id']; // new image group feature
					}
					else if ( isset( $_REQUEST['image_id'] ) ) {
						$entry = $this->_options['image_ids'][$_REQUEST['image_id']];
						
						$this->_options['attachment_id'] = $entry['attachment_id'];
						$this->_options['url'] = $entry['url'];
						$this->_options['order'] = $entry['order'];
					}
					
					$image = '';
					if ( ! empty( $this->_options['attachment_id'] ) ) {
						require_once( $this->_pluginPath . '/lib/file-utility/file-utility.php' );
						
						$image = iThemesFileUtility::resize_image( $this->_options['attachment_id'], $thumb_width, $thumb_height, true );
					}
					
					if ( isset( $this->_errors ) && is_array( $this->_errors ) ) {
						foreach ( (array) $this->_errors as $error )
							$this->_showErrorMessage( $error );
					}
				?>
				
				<form enctype="multipart/form-data" method="post" action="<?php echo $this->_selfLink; ?>&group_id=<?php echo  $this->_groupID; ?>">
					<?php wp_nonce_field( $this->_var . '-nonce' ); ?>
					<table class="form-table">
						<tr><th scope="row">Image</th>
							<td>
								<?php if ( ! empty( $image ) && ! is_wp_error( $image ) ) : ?>
									<img src="<?php echo $image['url']; ?>" /><br />
									
									<?php $this->_addHidden( 'attachment_id' ); ?>
									
									<p>Upload a new file to replace the current image.</p>
								<?php endif; ?>
								
								<?php $this->_addFileUpload( 'image_upload' ); ?>
							</td>
						</tr>
						<tr><th scope="row">Link URL</th>
							<td>
								<?php $this->_addTextBox( 'url', array( 'size' => '60' ) ); ?>
								<br />
								<i>Example: http://site.domain/</i>
							</td>
						</tr>
					</table>
					
					<p class="submit">
						<?php if ( ! isset( $_REQUEST['image_id'] ) ) : ?>
							<?php $this->_addSubmit( 'save_image', 'Add Image' ); ?>
						<?php else : ?>
							<?php $this->_addSubmit( 'save_image', 'Update Image Settings' ); ?>
							<?php $this->_addHiddenNoSave( 'image_id', $_REQUEST['image_id'] ); ?>
						<?php endif; ?>
					</p>
					
					<?php $this->_addHiddenNoSave( 'action', 'save_image' ); ?>
					<?php $this->_addHidden( 'order' ); ?>
				</form>
			</div>
		<?php
		endif;
		
		if ( ! isset( $_REQUEST['image_id'] ) && ( ! isset( $_REQUEST['action'] ) || ( 'save_image' !== $_REQUEST['action'] ) ) ) : ?>
			<div class="wrap">
				<h2 id="rotating-images-settings"><?php _e( 'Rotating Images Settings', $this->_var ); ?></h2>
				
				<?php
					if ( isset( $this->_errors ) && is_array( $this->_errors ) ) {
						foreach ( (array) $this->_errors as $error )
							$this->showErrorMessage( $error );
					}
				?>
				
				<form enctype="multipart/form-data" method="post" action="<?php echo $this->_selfLink; ?>&group_id=<?php echo $this->_groupID; ?>">
					<table class="form-table">
						<?php if ( true === $this->_defaults['variable_width'] ) : ?>
							<tr>
								<th scope="row">Rotating&nbsp;Images&nbsp;Width</th>
								<td>
									<table>
										<tr>
											<td>Width in pixels:</td>
											<?php if ( ( ! empty( $_POST['save'] ) ) && ( intval( $_POST[$this->_var . '-width'] ) < 0 ) ) : ?>
												<td style="background-color:red;">
											<?php else: ?>
												<td>
											<?php endif; ?>
												<?php $this->_addTextBox( 'width', array( 'size' => '3', 'maxlength' => '5' ) ); ?>
											</td>
										</tr>
									</table>
								</td>
							</tr>
						<?php endif; ?>
						<?php if ( true === $this->_defaults['variable_height'] ) : ?>
							<tr>
								<th scope="row">Rotating&nbsp;Images&nbsp;Height</th>
								<td>
									<table>
										<tr>
											<td>Height in pixels:</td>
											<?php if ( ( ! empty( $_POST['save'] ) ) && ( intval( $_POST[$this->_var . '-height'] ) < 0 ) ) : ?>
												<td style="background-color:red;">
											<?php else: ?>
												<td>
											<?php endif; ?>
												<?php $this->_addTextBox( 'height', array( 'size' => '3', 'maxlength' => '5' ) ); ?>
											</td>
										</tr>
									</table>
								</td>
							</tr>
								<?php endif; ?>
								<tr>
									<th scope="row">Default&nbsp;URL</th>
									<td>
										<?php $this->_addTextBox( 'link', array( 'size' => '70' ) ); ?>
										<br />
										<i>This link will be used if an image doesn't have a URL.</i>
									</td>
								</tr>
								<tr>
								
								<?php
								/*
								Line 220 crossslide js edit to add new window functionality:
								if (p.href) {
									if (opts.open_new_window) {
										windowTarget=' target="_new"';
									} else {
										windowTarget='';
									}
									elm = jQuery(format('<a href="{0}"'+windowTarget+'><img src="{1}"/></a>', p.href, p.src));
								} else {
									elm = jQuery(format('<img src="{0}"/>', p.src));
								}
								*/
								?>
								
									<th scope="row">Open&nbsp;URL&nbsp;in&nbsp;New&nbsp;Tab/Window</th>
									<td>
										<?php $this->_addCheckBox( 'open_new_window', '1' ); ?>
									</td>
								</tr>
								<tr>
									<th scope="row">Center in Displayed Area</th>
									<td>
										<?php $this->_addCheckBox( 'widget_align', 'center' ); ?>
									</td>
								</tr>
								<tr>
									<th scope="row">Fade Animation</th>
									<td>
										<div>The fade animation will show each of the images with a smooth fade transition between each image.</div>
										<div>If the animation is disabled, a single random image will be shown.</div>
										<br />
										
										<?php $this->_addCheckBox( 'enable_fade', '1' ); ?> Enable Fade
									</td>
								</tr>
								<tr id="fade-options">
									<th scope="row">Fade Options</th>
									<td>
										<div>The following options control the fade animation.</div>
										<div>If the animation is disabled, these options will not make any effect.</div>
										<br />
										
										<div>Choose an image sort order: <?php $this->_addDropDown( 'fade_sort', array( 'ordered' => 'As ordered (default)', 'alpha' => 'Alphabetical by file name', 'random' => 'Random' ) ); ?></div>
										<br />
										
										<table>
											<tr>
												<td>Length of time to display each image in seconds</td>
												<?php if ( ( ! empty( $_POST['save'] ) ) && ( floatval( $_POST[$this->_var . '-sleep'] ) <= 0 ) ) : ?>
													<td style="background-color:red;">
												<?php else: ?>
													<td>
												<?php endif; ?>
													<?php $this->_addTextBox( 'sleep', array( 'size' => '3', 'maxlength' => '5' ) ); ?>
												</td>
											</tr>
											<tr>
												<td>
													Length of time to fade each image in seconds
												</td>
												<?php if ( ( ! empty( $_POST['save'] ) ) && ( floatval( $_POST[$this->_var . '-fade'] ) <= 0 ) ) : ?>
													<td style="background-color:red;">
												<?php else: ?>
													<td>
												<?php endif; ?>
													<?php $this->_addTextBox( 'fade', array( 'size' => '3', 'maxlength' => '5' ) ); ?>
												</td>
											</tr>
											<tr>
												<td colspan="2"><br />
													<?php $this->_addCheckBox( 'enable_slide', '1' ); ?> Enable Sliding Effect *<br />
													<small>* Overrides display and fade times. Original images must<br />
													be larger than configured dimensions to function properly.</small>
												</td>
											</tr>
											<tr>
												<td colspan="2"><br />
													<?php $this->_addCheckBox( 'double_fade', '1' ); ?> Double Fade Transparent Images<br />
													<small>* When using images with transparency, this can fix problems with<br />
													one image showing through and changing suddenly. Only use if needed.</small>
												</td>
											</tr>
										</table>
									</td>
								</tr>
								
								<?php if( false === $this->_defaults['force_disable_overlay'] ) : ?>
									<tr id="text-overlay">
										<th scope="row">Text Overlay</th>
										<td>
										<div>Use this feature to overlay custom text on top of rotating image(s).<br />When enabled, all images & overlay text in this group will link to the Default URL.</div>
											<br />
											
											<div><?php $this->_addCheckBox( 'enable_overlay', '1' ); ?> Enable Text Overlay</div>
										</td>
									</tr>
								<?php endif; ?>
								<tr id="text-overlay-options">
									<th scope="row">Text Overlay Options</th>
									<td>
										<table>
											<tr><td>Text Horizontal Alignment:</td>
												<td><?php $this->_addDropDown( 'overlay_text_alignment', array( 'center' => 'Center (default)', 'left' => 'Left', 'right' => 'Right' ) ); ?></td>
											</tr>
											<tr><td>Text Vertical Position:</td>
												<td><?php $this->_addDropDown( 'overlay_text_vertical_position', array( 'bottom' => 'Bottom', 'middle' => 'Middle (default)', 'top' => 'Top' ) ); ?></td>
											</tr>
											<tr><td>Text Padding in Pixels:</td>
												<td><?php $this->_addTextBox( 'overlay_text_padding', array( 'size' => '4' ) ); ?></td>
											</tr>
										</table>
										
										<h3>Header Text</h3>
										<table>
											<tr><td>Text:</td>
												<td><?php $this->_addTextBox( 'overlay_header_text', array( 'size' => '40' ) ); ?></td>
											</tr>
											<tr><td>Size in pixels:</td>
												<td><?php $this->_addTextBox( 'overlay_header_size', array( 'size' => '4' ) ); ?></td>
											</tr>
											<tr><td>Color:</td>
												<td><?php $this->_addTextBox( 'overlay_header_color', array( 'size' => '7' ) ); ?>&nbsp;<?php $this->_addButton( 'show_overlay_header_color_picker', 'Show Picker' ); ?></td>
											</tr>
											<tr><td>Font Family:</td>
												<td><?php $this->_addTextBox( 'overlay_header_font', array( 'size' => '20' ) ); ?> <small>(blank for default)</small></td>
											</tr>
										</table>
										
										<h3>Subheader Text</h3>
										<table>
											<tr><td>Text:</td>
												<td><?php $this->_addTextBox( 'overlay_subheader_text', array( 'size' => '40' ) ); ?></td>
											</tr>
											<tr><td>Size in pixels:</td>
												<td><?php $this->_addTextBox( 'overlay_subheader_size', array( 'size' => '4' ) ); ?></td>
											</tr>
											<tr><td>Color:</td>
												<td><?php $this->_addTextBox( 'overlay_subheader_color', array( 'size' => '7' ) ); ?>&nbsp;<?php $this->_addButton( 'show_overlay_subheader_color_picker', 'Show Picker' ); ?></td>
											</tr>
											<tr><td>Font Family:</td>
												<td><?php $this->_addTextBox( 'overlay_subheader_font', array( 'size' => '20' ) ); ?> <small>(blank for default)</small></td>
											</tr>
										</table>
									</td>
								</tr>
							</table>
							<br />
							
							<p class="submit"><?php $this->_addSubmit( 'save', 'Save' ); ?></p>
							<?php $this->_addHiddenNoSave( 'action', 'save' ); ?>
							<?php $this->_addUsedInputs(); ?>
							<?php wp_nonce_field( $this->_var . '-nonce' ); ?>
							
							<div id="overlay_header_color_ColorPickerWrapper" style="padding:10px; border:1px solid black; position:absolute; z-index:10; background-color:white; display:none;">
								<table><tr>
									<td style="vertical-align:top;"><div id="overlay_header_color_ColorMap"></div><br /><a href="javascript:void(0);" style="float:right;" id="overlay_header_color_hide_div">save selection</a></td>
									<td style="vertical-align:top;"><div id="overlay_header_color_ColorBar"></div></td>
									<td style="vertical-align:top;">
										<table>
											<tr><td colspan="3"><div id="overlay_header_color_Preview" style="background-color:#fff; width:95px; height:60px; padding:0; margin:0; border:solid 1px #000;"><br /></div></td></tr>
											<tr><td><input type="radio" id="overlay_header_color_HueRadio" name="overlay_header_color_Mode" value="0" /></td><td><label for="overlay_header_color_HueRadio">H:</label></td><td><input type="text" id="overlay_header_color_Hue" value="0" style="width: 40px;" /> &deg;</td></tr>
											<tr><td><input type="radio" id="overlay_header_color_SaturationRadio" name="overlay_header_color_Mode" value="1" /></td><td><label for="overlay_header_color_SaturationRadio">S:</label></td><td><input type="text" id="overlay_header_color_Saturation" value="100" style="width: 40px;" /> %</td></tr>
											<tr><td><input type="radio" id="overlay_header_color_BrightnessRadio" name="overlay_header_color_Mode" value="2" /></td><td><label for="overlay_header_color_BrightnessRadio">B:</label></td><td><input type="text" id="overlay_header_color_Brightness" value="100" style="width: 40px;" /> %</td></tr>
											<tr><td colspan="3" height="5"></td></tr>
											<tr><td><input type="radio" id="overlay_header_color_RedRadio" name="overlay_header_color_Mode" value="r" /></td><td><label for="overlay_header_color_RedRadio">R:</label></td><td><input type="text" id="overlay_header_color_Red" value="255" style="width: 40px;" /></td></tr>
											<tr><td><input type="radio" id="overlay_header_color_GreenRadio" name="overlay_header_color_Mode" value="g" /></td><td><label for="overlay_header_color_GreenRadio">G:</label></td><td><input type="text" id="overlay_header_color_Green" value="0" style="width: 40px;" /></td></tr>
											<tr><td><input type="radio" id="overlay_header_color_BlueRadio" name="overlay_header_color_Mode" value="b" /></td><td><label for="overlay_header_color_BlueRadio">B:</label></td><td><input type="text" id="overlay_header_color_Blue" value="0" style="width: 40px;" /></td></tr>
											<tr><td>#:</td><td colspan="2"><input type="text" id="overlay_header_color_Hex" value="FF0000" style="width: 60px;" /></td></tr>
										</table>
									</td>
								</tr></table>
							</div>
							
							<div id="overlay_subheader_color_ColorPickerWrapper" style="padding:10px; border:1px solid black; position:absolute; z-index:10; background-color:white; display:none;">
								<table><tr>
									<td style="vertical-align:top;"><div id="overlay_subheader_color_ColorMap"></div><br /><a href="javascript:void(0);" style="float:right;" id="overlay_subheader_color_hide_div">save selection</a></td>
									<td style="vertical-align:top;"><div id="overlay_subheader_color_ColorBar"></div></td>
									<td style="vertical-align:top;">
										<table>
											<tr><td colspan="3"><div id="overlay_subheader_color_Preview" style="background-color:#fff; width:95px; height:60px; padding:0; margin:0; border:solid 1px #000;"><br /></div></td></tr>
											<tr><td><input type="radio" id="overlay_subheader_color_HueRadio" name="overlay_subheader_color_Mode" value="0" /></td><td><label for="overlay_subheader_color_HueRadio">H:</label></td><td><input type="text" id="overlay_subheader_color_Hue" value="0" style="width: 40px;" /> &deg;</td></tr>
											<tr><td><input type="radio" id="overlay_subheader_color_SaturationRadio" name="overlay_subheader_color_Mode" value="1" /></td><td><label for="overlay_subheader_color_SaturationRadio">S:</label></td><td><input type="text" id="overlay_subheader_color_Saturation" value="100" style="width: 40px;" /> %</td></tr>
											<tr><td><input type="radio" id="overlay_subheader_color_BrightnessRadio" name="overlay_subheader_color_Mode" value="2" /></td><td><label for="overlay_subheader_color_BrightnessRadio">B:</label></td><td><input type="text" id="overlay_subheader_color_Brightness" value="100" style="width: 40px;" /> %</td></tr>
											<tr><td colspan="3" height="5"></td></tr>
											<tr><td><input type="radio" id="overlay_subheader_color_RedRadio" name="overlay_subheader_color_Mode" value="r" /></td><td><label for="overlay_subheader_color_RedRadio">R:</label></td><td><input type="text" id="overlay_subheader_color_Red" value="255" style="width: 40px;" /></td></tr>
											<tr><td><input type="radio" id="overlay_subheader_color_GreenRadio" name="overlay_subheader_color_Mode" value="g" /></td><td><label for="overlay_subheader_color_GreenRadio">G:</label></td><td><input type="text" id="overlay_subheader_color_Green" value="0" style="width: 40px;" /></td></tr>
											<tr><td><input type="radio" id="overlay_subheader_color_BlueRadio" name="overlay_subheader_color_Mode" value="b" /></td><td><label for="overlay_subheader_color_BlueRadio">B:</label></td><td><input type="text" id="overlay_subheader_color_Blue" value="0" style="width: 40px;" /></td></tr>
											<tr><td>#:</td><td colspan="2"><input type="text" id="overlay_subheader_color_Hex" value="FF0000" style="width: 60px;" /></td></tr>
										</table>
									</td>
								</tr></table>
							</div>
							
							<div style="display:none;">
								<?php
									$images = array( 'rangearrows.gif', 'mappoint.gif', 'bar-saturation.png', 'bar-brightness.png', 'bar-blue-tl.png', 'bar-blue-tr.png', 'bar-blue-bl.png', 'bar-blue-br.png', 'bar-red-tl.png',
										'bar-red-tr.png', 'bar-red-bl.png', 'bar-red-br.png', 'bar-green-tl.png', 'bar-green-tr.png', 'bar-green-bl.png', 'bar-green-br.png', 'map-red-max.png', 'map-red-min.png',
										'map-green-max.png', 'map-green-min.png', 'map-blue-max.png', 'map-blue-min.png', 'map-saturation.png', 'map-saturation-overlay.png', 'map-brightness.png', 'map-hue.png' );
									
									foreach( (array) $images as $image )
										echo '<img src="' . $this->_pluginURL . '/js/colorpicker/images/' . $image . "\" />\n";
								?>
								
							</div>
						</form>
					</div>
				<?php endif;
			} else { // SHOW GROUP LISTING - Dustin Bolton
				echo '<div class="wrap">';
				
				
				?>
				<form id="posts-filter" enctype="multipart/form-data" method="post" action="<?php echo $this->_selfLink; ?>">
					<h2>Rotating Images</h2>
					<ol>
						<li>Create a <a href="#addnew">new image group</a> below for each collection of images.</li>
						<li>Select the group to add images to the group and configure settings.</li>
						<li>Use the Wordpress widget administrator to place the widget in a widget space or sidebar OR<br />
						Enter a shortcode from the group list below in a post or page where you want it displayed. Ex: [it-rotate group="0"]
						</li>
					</ol><br />
					
					<?php if ( isset( $this->_options['groups'] ) && ( count( $this->_options['groups'] ) > 0 ) ) :
					uksort( $this->_options['groups'], array( &$this, '_sortGroupsByName' ) );
					?>
						<div class="tablenav">
							<div class="alignleft actions">
								<?php $this->_addSubmit( 'delete_group', array( 'value' => 'Delete', 'class' => 'button-secondary delete' ) ); ?>
							</div>
							
							<br class="clear" />
						</div>
						
						<br class="clear" />
						
						<table class="widefat">
							<thead>
								<tr class="thead">
									<th scope="col" class="check-column"><input type="checkbox" id="check-all-groups" /></th>
									<th>Group Name</th>
									<th>Images</th>
									<th>Fading</th>
									<th>Shortcode</th>
									<th class="num">Dimensions (W x H)</th>
								</tr>
							</thead>
							<tfoot>
								<tr class="thead">
									<th scope="col" class="check-column"><input type="checkbox" id="check-all-groups" /></th>
									<th>Group Name</th>
									<th>Images</th>
									<th>Fading</th>
									<th>Shortcode</th>
									<th class="num">Dimensions (W x H)</th>
								</tr>
							</tfoot>
							<tbody id="users" class="list:user user-list">
								<?php $class = ' class="alternate"'; ?>
								<?php foreach ( (array) $this->_options['groups'] as $id => $group ) : ?>
									<?php
										$entriesDescription = ( ! empty( $group['entries'] ) && is_array( $group['entries'] ) && ( count( $group['entries'] ) > 0 ) ) ? 'Modify Entries' : 'Add Entries';
										
										$css_class = strtolower( $group['name'] );
										$css_class = preg_replace( '/\s+/', '-', $css_class );
										$css_class = $this->_class . '-' . preg_replace( '/[^\w\-]/', '', $css_class );
									?>
									<tr id="group-<?php echo $id; ?>"<?php echo $class; ?>>
										<th scope="row" class="check-column"><input type="checkbox" name="groups[]" class="administrator groups" value="<?php echo $id; ?>" /></th>
										<td><strong><a href="<?php echo $this->_selfLink; ?>&group_id=<?php echo $id; ?>" title="Modify Group Settings"><?php echo $group['name']; ?></a></strong></td>
										<td>
											<?php
											if ( ! empty( $group['options']['image_ids'] ) && is_array( $group['options']['image_ids'] ) ) {
												echo count( $group['options']['image_ids'] ); // Number of images in this groups array.
											} else {
												echo '0';
											}
											?>
											(<a href="<?php echo $this->_selfLink; ?>&group_id=<?php echo $id; ?>&view_entries=1" title="Add, Modify, and Delete Entries"><?php echo $entriesDescription; ?></a>)
										</td>
										<td>
											<?php
											if ( $group['options']['enable_fade'] == '1' ) {
												echo 'Yes';
											} else {
												echo 'No';
											}
											?>
										</td>
										<td>
											[it-rotate group="<?php echo $id; ?>"]
										</td>
										<td class="num"><?php echo $group['options']['width'].' x '.$group['options']['height']; ?> px</td>
									</tr>
									<?php $class = ( $class == '' ) ? ' class="alternate"' : ''; ?>
								<?php endforeach; ?>
							</tbody>
						</table>
						
						<div class="tablenav">
							<div class="alignleft actions">
								<?php $this->_addSubmit( 'delete_group', array( 'value' => 'Delete', 'class' => 'button-secondary delete' ) ); ?>
							</div>
							
							<br class="clear" />
						</div>
					<?php endif; ?>
					
					<?php wp_nonce_field( $this->_var . '-nonce' ); ?>
				</form>
			
				<!-- ADD GROUP FORM -->
				<h2>Add New Rotating Images Group</h2>
				
				<form name="addnew" id="addnew" enctype="multipart/form-data" method="post" action="<?php echo $this->_selfLink; ?>">
					<?php wp_nonce_field( $this->_var . '-nonce' ); ?>
					<table class="form-table">
						<tr><th scope="row"><label for="name">Name for New Group:</label></th>
							<td><?php $this->_addTextBox( 'name' ); ?></td>
						</tr>
					</table>
					
					<p class="submit">
						<?php $this->_addSubmit( 'add_group', 'Add Group' ); ?>
					</p>
				</form>
				
				
				
				
				<?php
				echo '<br /><br /><a href="http://pluginbuddy.com" style="text-decoration: none;"><img src="'.$this->_pluginURL.'/images/pluginbuddy.png" style="vertical-align: -3px;" /> PluginBuddy.com</a>';
				echo '</div>';
			} // End if.
		} // End function.
		
		function _groupsCreate() {
			$name = (string) $_POST[$this->_var . '-name'];
			
			if ( empty( $name ) ) {
				$this->_errors[] = 'name';
				$this->_showErrorMessage( 'A Name is required to create a new Image Group' );
			}
			elseif ( is_array( $this->_options['groups'] ) ) {
				foreach ( (array) $this->_options['groups'] as $id => $group ) {
					if ( $group['name'] == $name ) {
						$this->_errors[] = 'name';
						$this->_showErrorMessage( 'An Image Group with that Name already exists' );
						
						break;
					}
				}
			}
			if ( isset( $this->_errors ) )
				$this->_showErrorMessage( 'Please correct the ' . __ngettext( 'error', 'errors', count( $this->_errors ) ) . ' in order to add the new Image Group' );
			else {
				$group = array();
				
				$group['name'] = $name;
				//$group['entries'] = array();
				
				if ( is_array( $this->_options['groups'] ) && ! empty( $this->_options['groups'] ) )
					$newID = max( array_keys( $this->_options['groups'] ) ) + 1;
				else
					$newID = 0;
				
				$this->_options['groups'][$newID]['name'] = $name; // Set name.
				
				$this->_groups=$this->_options['groups']; // Copy existing groups so won't be overwritten by defaults.
				
				$this->_groupID=$newID;
				
				$options_updater = $this->_options['updater'];
				$this->_options=$this->_defaults; // Load defaults into current settings.
				$this->_options['updater'] = $options_updater;
				
				$this->_options['groups']=$this->_groups; // Restore other groups.
				
				$this->save();
				$this->load(); // Temporary fix for defaults not showing until refresh. - Dustin
				
				$this->_showStatusMessage( "Rotating Image Group \"$name\" added" );
			}
		}
		
		function _groupsDelete() {
			$names = array();
			
			if ( ! empty( $_POST['groups'] ) && is_array( $_POST['groups'] ) ) {
				foreach ( (array) $_POST['groups'] as $id ) {
					$names[] = $this->_options['groups'][$id]['name'];
					unset( $this->_options['groups'][$id] );
				}
				$this->save();
			}
	
			natcasesort( $names );
			
			if ( $names )
				$this->_showStatusMessage( 'Successfully deleted the group.' );
			else
				$this->_showErrorMessage( 'No Image Groups were selected for deletion' );
		}
		
		// Form Functions ///////////////////////////
		
		function _newForm() {
			$this->_usedInputs = array();
		}
		
		function _addSubmit( $var, $options = array(), $override_value = true ) {
			if ( ! is_array( $options ) )
				$options = array( 'value' => $options );
			
			$options['type'] = 'submit';
			$options['name'] = $var;
			$options['class'] = ( empty( $options['class'] ) ) ? 'button-primary' : $options['class'];
			
			$this->_addSimpleInput( $var, $options, $override_value );
		}
		
		function _addButton( $var, $options = array(), $override_value = true ) {
			if ( ! is_array( $options ) )
				$options = array( 'value' => $options );
			
			$options['type'] = 'button';
			$options['name'] = $var;
			
			$this->_addSimpleInput( $var, $options, $override_value );
		}
		
		function _addTextBox( $var, $options = array(), $override_value = false ) {
			if ( ! is_array( $options ) )
				$options = array( 'value' => $options );
			
			$options['type'] = 'text';
			
			$this->_addSimpleInput( $var, $options, $override_value );
		}
		
		function _addTextArea( $var, $options = array(), $override_value = false ) {
			if ( ! is_array( $options ) )
				$options = array( 'value' => $options );
			
			$options['type'] = 'textarea';
			
			$this->_addSimpleInput( $var, $options, $override_value );
		}
		
		function _addFileUpload( $var, $options = array(), $override_value = false ) {
			if ( ! is_array( $options ) )
				$options = array( 'value' => $options );
			
			$options['type'] = 'file';
			$options['name'] = $var;
			
			$this->_addSimpleInput( $var, $options, $override_value );
		}
		
		function _addCheckBox( $var, $options = array(), $override_value = false ) {
			if ( ! is_array( $options ) )
				$options = array( 'value' => $options );
			
			$options['type'] = 'checkbox';
			
			$this->_addSimpleInput( $var, $options, $override_value );
		}
		
		function _addMultiCheckBox( $var, $options = array(), $override_value = false ) {
			if ( ! is_array( $options ) )
				$options = array( 'value' => $options );
			
			$options['type'] = 'checkbox';
			$var = $var . '[]';
			
			$this->_addSimpleInput( $var, $options, $override_value );
		}
		
		function _addRadio( $var, $options = array(), $override_value = false ) {
			if ( ! is_array( $options ) )
				$options = array( 'value' => $options );
			
			$options['type'] = 'radio';
			
			$this->_addSimpleInput( $var, $options, $override_value );
		}
		
		function _addDropDown( $var, $options = array(), $override_value = false ) {
			if ( ! is_array( $options ) )
				$options = array();
			else if ( ! isset( $options['value'] ) || ! is_array( $options['value'] ) )
				$options = array( 'value' => $options );
			
			$options['type'] = 'dropdown';
			
			$this->_addSimpleInput( $var, $options, $override_value );
		}
		
		function _addHidden( $var, $options = array(), $override_value = false ) {
			if ( ! is_array( $options ) )
				$options = array( 'value' => $options );
			
			$options['type'] = 'hidden';
			
			$this->_addSimpleInput( $var, $options, $override_value );
		}
		
		function _addHiddenNoSave( $var, $options = array(), $override_value = true ) {
			if ( ! is_array( $options ) )
				$options = array( 'value' => $options );
			
			$options['name'] = $var;
			
			$this->_addHidden( $var, $options, $override_value );
		}
		
		function _addDefaultHidden( $var ) {
			$options = array();
			$options['value'] = $this->defaults[$var];
			
			$var = "default_option_$var";
			
			$this->_addHiddenNoSave( $var, $options );
		}
		
		function _addUsedInputs() {
			$options['type'] = 'hidden';
			$options['value'] = implode( ',', $this->_usedInputs );
			$options['name'] = 'used-inputs';
			
			$this->_addSimpleInput( 'used-inputs', $options, true );
		}
		
		function _addSimpleInput( $var, $options = false, $override_value = false ) {
			if ( empty( $options['type'] ) ) {
				echo "<!-- _addSimpleInput called without a type option set. -->\n";
				return false;
			}
			
			
			$scrublist['textarea']['value'] = true;
			$scrublist['file']['value'] = true;
			$scrublist['dropdown']['value'] = true;
			
			$defaults = array();
			$defaults['name'] = $this->_var . '-' . $var;
			
			$var = str_replace( '[]', '', $var );
			
			if ( 'checkbox' === $options['type'] )
				$defaults['class'] = $var;
			else
				$defaults['id'] = $var;
			
			$options = $this->_merge_defaults( $options, $defaults );
			
			if ( ( false === $override_value ) && isset( $this->_options[$var] ) ) {
				if ( 'checkbox' === $options['type'] ) {
					if ( $this->_options[$var] == $options['value'] )
						$options['checked'] = 'checked';
				}
				elseif ( 'dropdown' !== $options['type'] )
					$options['value'] = $this->_options[$var];
			}
			
			if ( ( preg_match( '/^' . $this->_var . '/', $options['name'] ) ) && ( ! in_array( $options['name'], $this->_usedInputs ) ) )
				$this->_usedInputs[] = $options['name'];
			
			$attributes = '';
			
			if ( false !== $options )
				foreach ( (array) $options as $name => $val )
					if ( ! is_array( $val ) && ( ! isset( $scrublist[$options['type']][$name] ) || ( true !== $scrublist[$options['type']][$name] ) ) )
						if ( ( 'submit' === $options['type'] ) || ( 'button' === $options['type'] ) )
							$attributes .= "$name=\"$val\" ";
						else
							$attributes .= "$name=\"" . htmlspecialchars( $val ) . '" ';
			
			if ( 'textarea' === $options['type'] )
				echo '<textarea ' . $attributes . '>' . $options['value'] . '</textarea>';
			elseif ( 'dropdown' === $options['type'] ) {
				echo "<select $attributes>\n";
				
				foreach ( (array) $options['value'] as $val => $name ) {
					$selected = ( $this->_options[$var] == $val ) ? ' selected="selected"' : '';
					echo "<option value=\"$val\"$selected>$name</option>\n";
				}
				
				echo "</select>\n";
			}
			else
				echo '<input ' . $attributes . '/>';
		}
		
		
		// Plugin Functions ///////////////////////////
		
		function print_scripts() {
			wp_enqueue_script( 'jquery-cross-slide', $this->_pluginURL . '/js/jquery.cross-slide.js', array(), false, true );
			wp_print_scripts( 'jquery-cross-slide' );
		}
		
		// If param2=true, data is echoed. If false, it is returned (for shortcode)
		function fadeImages($group, $widget = true) {
			require_once( $this->_pluginPath . '/lib/file-utility/file-utility.php' );
			$return = "";
			
			$this->_groupID = (int) $group;
			$this->load();
			
			if ( empty ($this->_options['image_ids']) ) { // Dont proceed if there are no images.
				echo 'Warning: Empty Rotating Images Group! Upload images for this widget to function.';
				return;
			}
			
			if ( ! empty($this->_errors) ) { // Report errors.
				echo $this->_errors[0]; // Give first error.
			} else {
				
				$this->_sortImages();
				
				$files = array();
				
				foreach ( (array) $this->_options['image_ids'] as $entry ) {
					$id = $entry['attachment_id'];
					
					$link = ( ! empty( $entry['url'] ) ) ? $entry['url'] : $this->_options['link'];
					
					if ( wp_attachment_is_image( $id ) ) {
						$file = get_attached_file( $id );
						
						if ( ! empty( $this->_options['enable_slide'] ) ) { // Sliding enabled!
							$sizemult=2;
						} else {
							$sizemult=1;
						}
						
						$data = iThemesFileUtility::resize_image( $file, $this->_options['width']*$sizemult, $this->_options['height']*$sizemult, true );
						
						if ( ! is_array( $data ) && is_wp_error( $data ) )
							$return .= "<!-- Resize Error: " . $data->get_error_message() . " -->";
						else
							$files[] = array( 'image' => $data['url'], 'url' => $link );
					}
				}
				if ( 0 === count( $files ) )
					return;
					
				$this->_instanceCount++; // Increment instance count for javascript for unique instances
				
				if ( ( '1' == $this->_options['enable_fade'] ) && ( count( $files ) > 1 ) ) {
					$list = '';
					$slidevar=", dir: 'up'";
					
					foreach ( (array) $files as $id => $file ) {
						if ($slidevar==", dir: 'up'") { $slidevar=", dir: 'down'"; } else { $slidevar=", dir: 'up'"; }
						
						if ( ! empty( $list ) )
							$list .= ",\n";
						
						
						if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') { // If SSL in use, use https.
							$file['image'] = str_replace('http://', 'https://', $file['image']);
						}
						
						if ( ! empty( $link ) )
							$list .= "{src: '{$file['image']}', href: '{$file['url']}'".$slidevar."}";
						else
							$list .= "{src: '{$file['image']}'".$slidevar."}";
					}
					
					
					if ( ! wp_script_is( 'jquery' ) )
						wp_print_scripts( 'jquery' );
					
					add_action( 'wp_footer', array( &$this, 'print_scripts' ) );
					$target = ( ! empty( $this->_options['open_new_window'] ) ) ? ', open_new_window: true' : '';
					
	$return .="
		<script type='text/javascript'>
			/* <![CDATA[ */
				jQuery(document).ready(
					function() {
						yo = jQuery('#rotating-images-rotator_".$this->_instanceCount."').crossSlide(
							{
							";
							if ( ! empty( $this->_options['enable_slide'] ) ) { // Sliding enabled!
								$return .= 'speed: '.(40+$this->_options['sleep']).',';
							} else {
								$return .= 'sleep: '.$this->_options['sleep'].',';
							}
							if ( ! empty( $this->_options['double_fade'] ) ) { // Double fade enabled!
								$return .= 'doubleFade: true,';
							}
							$return .= "
							fade: ".$this->_options['fade'].$target."},
							[
								".$list."
							]
						);
					}
				);
			/* ]]> */
		</script>
	";
					
				} else {
					shuffle( $files );
				}
				
				if ( 'bottom' === $this->_options['overlay_text_vertical_position'] )
					$title_overlay_vertical = "bottom: 0;\n";
				else
					$title_overlay_vertical = "top: 0;\n";
				
				
				$target = ( ! empty ( $this->_options['open_new_window'] ) ) ? ' target="_blank"' : '';
				
				$link_start = "\n";
				$link_end = "\n";
				
				if ( ! empty( $files[0]['url'] ) ) {
					$link_start = "					<a href=\"{$files[0]['url']}\" class=\"rotating-images-link_".$this->_instanceCount."\"{$target}>\n";
					$link_end = "					</a>\n";
				}
				
				$overlay_text = "					<div class=\"rotating-images-title-overlay-header_".$this->_instanceCount."\">\n$link_start";
				$overlay_text .= "						{$this->_options['overlay_header_text']}\n$link_end";
				$overlay_text .= "					</div>\n";
				
				if ( ! empty( $this->_options['overlay_subheader_text'] ) ) {
					$overlay_text .= "					<div class=\"rotating-images-title-overlay-subheader_".$this->_instanceCount."\">\n$link_start";
					$overlay_text .= "						{$this->_options['overlay_subheader_text']}\n$link_end";
					$overlay_text .= "					</div>\n";
				}
				
				
	$return .= "
		<style type=\"text/css\">";
			if ( ( '1' != $this->_options['enable_fade'] ) || ( count( $files ) == 1 ) ) {
				$return .= "#rotating-images-rotator_".$this->_instanceCount." {";
				if ($_SERVER['HTTPS'] == 'on') { // If SSL in use, use https.
					$files[0]['image'] = str_replace('http://', 'https://', $files[0]['image']);
				}
				$return .= "background: url('".$files[0]['image']."');";
				$return .= "}";
			}
			$return .= "#rotating-images-rotator_".$this->_instanceCount.",";
			$return .= "#rotating-images-rotator-wrapper_".$this->_instanceCount." {";
			$return .= "	width: ".$this->_options['width']."px;";
			$return .= "	height: ".$this->_options['height']."px;";

				if ( $this->_options['widget_align'] == 'center' ) {
					$return .= 'margin-left: auto;';
					$return .= 'margin-right: auto;';
				}

			$return .= "}";

			$return .= "#rotating-images-rotator-wrapper_".$this->_instanceCount." img {";
			$return .= "	padding: 0px;";
			$return .= "}";
			$return .= "#rotating-images-rotator-container_".$this->_instanceCount." .rotating-images-link-overlay_".$this->_instanceCount." {";
			$return .= "	height: ".$this->_options['height']."px;";
			$return .= "	width: ".$this->_options['width']."px;";
			$return .= "	position: absolute;";
			$return .= "	top: 0;";
			$return .= "	display: block;";
			$return .= " }";
			$return .= " #rotating-images-rotator-container_".$this->_instanceCount." .rotating-images-link_".$this->_instanceCount." {";
			$return .= "	text-decoration: none;";
			$return .= "}";
			$return .= "#rotating-images-rotator-container_".$this->_instanceCount." .rotating-images-title-overlay_".$this->_instanceCount." {";
			$return .= " 	width: ".($this->_options['width'] - ( $this->_options['overlay_text_padding'] * 2 ) )."px;";
			$return .= "	position: absolute;";
			$return .= "	".$title_overlay_vertical.";";
			$return .= "	text-align: ".$this->_options['overlay_text_alignment'].";";
			$return .= "	padding: ".$this->_options['overlay_text_padding']."px;";
			$return .= "	display: block;";
			$return .= "}";
			$return .= "#rotating-images-rotator-container_".$this->_instanceCount." .rotating-images-title-overlay-header_".$this->_instanceCount.",";
			$return .= "#rotating-images-rotator-container_".$this->_instanceCount." .rotating-images-title-overlay-subheader_".$this->_instanceCount." {";
			$return .= "width: 100%;";
			$return .= "}";
			$return .= "#rotating-images-rotator-container_".$this->_instanceCount." .rotating-images-title-overlay-header_".$this->_instanceCount." {";
			$return .= "	padding-bottom: ".$this->_options['overlay_text_padding']."px;";
			$return .= "}";
			$return .= "#rotating-images-rotator-container_".$this->_instanceCount." .rotating-images-title-overlay-header_".$this->_instanceCount." a {";
			$return .= "	color: ".$this->_options['overlay_header_color'].";";
			$return .= "	font-size: ".$this->_options['overlay_header_size']."px;";
			if ((isset($this->_options['overlay_header_font'])) && ($this->_options['overlay_header_font'] != '')) {
				$return .= "	font-family: \"".$this->_options['overlay_header_font']."\";";
			}
			$return .= "	line-height: 1;";
			$return .= "}";
			$return .= "#rotating-images-rotator-container_".$this->_instanceCount." .rotating-images-title-overlay-subheader_".$this->_instanceCount." a {";
			$return .= "	color: ".$this->_options['overlay_subheader_color'].";";
			$return .= "	font-size: ".$this->_options['overlay_subheader_size']."px;";
			if ((isset($this->_options['overlay_subheader_font'])) && ($this->_options['overlay_subheader_font'] != '')) {
				$return .= "	font-family: \"".$this->_options['overlay_subheader_font']."\";";
			}
			$return .= "	line-height: 1;";
			$return .= "}";
			$return .= "</style>";
		
		$return .= '<div id="rotating-images-rotator-wrapper_'.$this->_instanceCount.'" style="position:relative;">';
		$return .= '	<div id="rotating-images-rotator-container_'.$this->_instanceCount.'" style="position:relative;">';
		$return .= '		<div id="rotating-images-rotator_'.$this->_instanceCount.'"><!-- placeholder --></div>';
				
				 if ( ( false === $this->_defaults['force_disable_overlay'] ) && ! empty( $this->_options['enable_overlay'] ) ) : 
					
		$return .= '			<span class="rotating-images-title-overlay_'.$this->_instanceCount.'">';
						 if ( 'middle' === $this->_options['overlay_text_vertical_position'] ) :
							
		$return .= '					<div style="display: table; height: '. ( $this->_options['height'] - ( $this->_options['overlay_text_padding'] * 2 ) ).'px; width: '.( $this->_options['width'] - ( $this->_options['overlay_text_padding'] * 2 ) ).'px; #position: relative; overflow: hidden;">';
		$return .= '						<div style="left: 0; #position: absolute; #top: 50%; display: table-cell; vertical-align: middle; width: '.( $this->_options['width'] - ( $this->_options['overlay_text_padding'] * 2 ) ).'px;">';
		$return .= '							<div style="#position: relative; #top: -50%; width: '.( $this->_options['width'] - ( $this->_options['overlay_text_padding'] * 2 ) ).'px; display:block;">';
		$return .= 									$overlay_text;
		$return .= '							</div>';
		$return .= '						</div>';
		$return .= '					</div>';
						 else : 
							
							$return .= $overlay_text;
						endif;
						
		$return .= '			</span>';
				endif;
				
				if ( ! empty( $files[0]['url'] ) ) :
					
					$target = ( ! empty ( $this->_options['open_new_window'] ) ) ? ' target="_blank"' : '';
					
		$return .= '			<a href="'.$files[0]['url'].'" class="rotating-images-link_'.$this->_instanceCount;

		if ( ( $this->_options['enable_fade'] != 1 ) || (count( $files )==1) ) {
			$return .=  ' rotating-images-link-overlay_'.$this->_instanceCount;
		}
		$return .= '" '. $target.'>';
		
		$return .= '				<!-- filler content -->';
		$return .= '			</a>';
				endif;
				
		$return .= 	'</div>';
		$return .= '</div>';

			} // End error if.
			
			if ($widget == true) {
				echo $return;
			} else {
				return $return;
			}
		} // End fadeimages.
		
		function _showStatusMessage( $message ) {
			
?>
	<div id="message" class="updated fade"><p><strong><?php echo $message; ?></strong></p></div>
<?php
			
		}
		
		function _showErrorMessage( $message ) {
			
?>
	<div id="message" class="error"><p><strong><?php echo $message; ?></strong></p></div>
<?php
			
		}
		
		function _merge_defaults( $values, $defaults, $force = false ) {
			if ( ! $this->_is_associative_array( $defaults ) ) {
				if ( ! isset( $values ) )
					return $defaults;
				
				if ( false === $force )
					return $values;
				
				if ( isset( $values ) || is_array( $values ) )
					return $values;
				return $defaults;
			}
			
			foreach ( (array) $defaults as $key => $val ) {
				if ( ! isset( $values[$key] ) )
					$values[$key] = null;
				
				$values[$key] = $this->_merge_defaults($values[$key], $val, $force );
			}
			
			return $values;
		}
		
		function _is_associative_array( &$array ) {
			if ( ! is_array( $array ) || empty( $array ) )
				return false;
			
			$next = 0;
			
			foreach ( $array as $k => $v )
				if ( $k !== $next++ )
					return true;
			
			return false;
		}
		
		
		// Utility Functions //////////////////////////
		
		function _sortImages() {
			if (is_null($this->_options['image_ids'])) $this->_load(); // Fix missing header
			if ( 'ordered' === $this->_options['fade_sort'] )
				uksort( $this->_options['image_ids'], array( &$this, '_orderedSort' ) );
			else if ( 'alpha' === $this->_options['fade_sort'] )
				uksort( $this->_options['image_ids'], array( &$this, '_alphaSort' ) );
			else
				uksort( $this->_options['image_ids'], array( &$this, '_randomSort' ) );
		}
		
		function _orderedSort( $a, $b ) {
			$a = $this->_options['image_ids'][$a];
			$b = $this->_options['image_ids'][$b];
			
			if ( $a['order'] < $b['order'] )
				return -1;
			
			return 1;
		}
		
		function _alphaSort( $a, $b ) {
			$a = basename( get_attached_file( (array) $this->_options['image_ids'][$a]['attachment_id'] ) );
			$b = basename( get_attached_file( (array) $this->_options['image_ids'][$b]['attachment_id'] ) );
			
			return strnatcasecmp( $a, $b );
		}
		
		function _randomSort( $a, $b ) {
			if ( mt_rand( 0, 1 ) === 1 )
				return -1;
			
			return 1;
		}
		
		function _sortGroupsByName( $a, $b ) {
			if ( $this->_options['groups'][$a]['name'] < $this->_options['groups'][$b]['name'] )
				return -1;
			
			return 1;
		}
		
		function _initializeImages() {
			if ( $dir = @opendir( $this->_pluginPath . '/images/random/' ) ) {
				require_once( $this->_pluginPath . '/lib/file-utility/file-utility.php' );
				
				if ( ! ( ( $uploads = wp_upload_dir() ) && false === $uploads['error'] ) )
					return new WP_Error( 'upload_dir_failure', 'Unable to load images into the uploads directory: ' . $uploads['error'] );
				
				
				$this->_options['image_ids'] = array();
				
				$order = 1;
				
				while ( ( $file = readdir( $dir ) ) !== false ) {
					if ( is_file( $this->_pluginPath . '/images/random/' . $file ) && ( preg_match( '/gif$|jpg$|jpeg$|png$/i', $file ) ) ) {
						$filename = wp_unique_filename( $uploads['path'], basename( $file ) );
						
						// Move the file to the uploads dir
						$new_file = $uploads['path'] . "/$filename";
						if ( false === copy( $this->_pluginPath . '/images/random/' . $file, $new_file ) ) {
							closedir( $dir );
							return new WP_Error( 'copy_file_failure', 'The theme images were unable to be loaded into the uploads directory' );
						}
						
						// Set correct file permissions
						$stat = stat( dirname( $new_file ));
						$perms = $stat['mode'] & 0000666;
						@chmod( $new_file, $perms );
						
						// Compute the URL
						$url = $uploads['url'] . "/$filename";
						
						
						$wp_filetype = wp_check_filetype( $file );
						$type = $wp_filetype['type'];
						
						
						$file_obj['url'] = $url;
						$file_obj['type'] = $type;
						$file_obj['file'] = $new_file;
						
						
						$title = preg_replace( '/\.[^.]+$/', '', basename( $file ) );
						$content = '';
						
						require_once( ABSPATH . 'wp-admin/includes/image.php' );
						
						// use image exif/iptc data for title and caption defaults if possible
						if ( $image_meta = @wp_read_image_metadata( $new_file ) ) {
							if ( trim( $image_meta['title'] ) )
								$title = $image_meta['title'];
							if ( trim( $image_meta['caption'] ) )
								$content = $image_meta['caption'];
						}
						
						// Construct the attachment array
						$attachment = array(
							'post_mime_type' => $type,
							'guid' => $url,
							'post_title' => $title,
							'post_content' => $content
						);
						
						// Save the data
						$id = wp_insert_attachment( $attachment, $new_file );
						if ( ! is_wp_error( $id ) ) {
							wp_update_attachment_metadata( $id, wp_generate_attachment_metadata( $id, $new_file ) );
						}
						
						
						$entry = array();
						$entry['attachment_id'] = $id;
						$entry['order'] = $order;
						$entry['url'] = '';
						
						$this->_options['image_ids'][] = $entry;
						
						
						$order++;
					}
				}
				
				closedir( $dir );
				
				
				$this->save();
			}
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
	}
	
	$iThemesRotatingImages = new iThemesRotatingImages();
}


// Widget Functionality //////////////////////////////////////


/**
 * widget_iThemesRotatingImages Class
 *
 * Adds widget capabilities to Rotating Images.
 *
 * Author:	Dustin Bolton
 * Date:	January 2010
 *
 */

class widget_iThemesRotatingImages extends WP_Widget 
{
	var $_widget_control_width = 300;
	var $_widget_control_height = 300;
	
	/**
	 * widget_iThemesRotatingImages::widget_iThemesRotatingImages()
	 * 
	 * Default constructor ran by WP_Widget class.
	 * 
	 * @return void
	 */
	function widget_iThemesRotatingImages() {
		$widget_ops = array('description' => __('Display Rotating Images as a widget.', 'iThemesRotatingImages'));
		$this->WP_Widget('iThemesRotatingImages', __('Rotating Images'), $widget_ops);
	}
	
	/**
	 * widget_iThemesRotatingImages::widget()
	 *
	 * Display public widget.
	 *
	 * @param	array	$args		Widget arguments -- currently not in use.
	 * @param	array	$instance	Instance data including title, group id, etc.
	 * @return	void
	 */
	function widget($args, $instance) {
		extract( $args );
		$title = apply_filters( 'widget_title', $instance['title'] );
		echo $before_widget;
		
		if ( $title )
			echo $before_title . $title . $after_title;
		
		$group = intval( $instance['group'] );
		do_action( 'ithemes_rotating_images_fade_images', $group, true);
		
		echo $after_widget;
	}
	
	/**
	 * widget_iThemesRotatingImages::update()
	 *
	 * Save widget form settings.
	 *
	 * @param	array	$new_instance	NEW instance data including title, group id, etc.
	 * @param	array	$old_instance	PREVIOUS instance data including title, group id, etc.
	 * @return	void
	 */
	function update($new_instance, $old_instance) {
		if (!isset($new_instance['submit'])) {
			return false;
		}
		$instance = $old_instance;
		$instance['title'] = strip_tags($new_instance['title']);
		$instance['group'] = intval($new_instance['group']);
		return $instance;
	}
		
	/**
	 * widget_iThemesRotatingImages::form()
	 *
	 * Display widget control panel.
	 *
	 * @param	array	$instance	Instance data including title, group id, etc.
	 * @return	void
	 */
	function form($instance) {
		//global $wpdb, $ithemes_theme_options;
		
		// Group indicates rotating images group for this instance.
		$group = ( isset( $instance['group'] ) ) ? $instance['group'] : '';
		$title = ( isset( $instance['title'] ) ) ? $instance['title'] : '';
		
		$instance = wp_parse_args( (array) $instance, array( 'title' => __( 'Rotating Images', 'iThemesRotatingImages' ), 'group' => $group ) );
		$title = esc_attr( $title );
		$group = intval( $group );
		
		$temp_options = get_option('ithemes-rotating-images');
		
?>
		<label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:', 'iThemesRotatingImages'); ?>
			<input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $title; ?>" />
		</label>
		
		<label for="<?php echo $this->get_field_id('group'); ?>"><?php _e('Image Group:', 'iThemesRotatingImages'); ?>
			<select class="widefat" id="<?php echo $this->get_field_id('group'); ?>" name="<?php echo $this->get_field_name('group'); ?>">
				<?php
					foreach ( (array)$temp_options['groups'] as $id => $grouploop ) {
						$selected = '';
						if ( $group == $id ) { $selected = ' selected '; }
						echo '<option value="' . $id . '"' . $selected . '>' . $grouploop['name'] . '</option>';
					}
				?>
			</select>
		</label>
		
		<input type="hidden" id="<?php echo $this->get_field_id('submit'); ?>" name="<?php echo $this->get_field_name('submit'); ?>" value="1" />
<?php

	}
		

} // End widget_iThemesRotatingImages class.

// Register function to create widget.
add_action('widgets_init', 'widget_iThemesRotatingImages_init');

/**
 * widget_iThemesRotatingImages_init()
 *
 * Instantiate widget via WP registration.
 *
 * @return	void
 */
function widget_iThemesRotatingImages_init() {
	register_widget('widget_iThemesRotatingImages');
}
?>
