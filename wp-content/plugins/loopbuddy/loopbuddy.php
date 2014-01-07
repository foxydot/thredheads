<?php
/**
 *
 * Plugin Name: LoopBuddy
 * Plugin URI: http://pluginbuddy.com/purchase/loopbuddy/
 * Description: Create & manage fully custom loops with drag & drop ease and custom queries. Unlimited loops, queries, shortcodes, and widgets!
 * Version: 1.2.3
 * Author: The PluginBuddy Team
 * Requires at least: 3.0
 * Author URI: http://pluginbuddy.com/
 * Contributors:  Dustin Bolton and Ronald Huereca
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


if ( !function_exists( 'wp_print_r' ) ) {
	function wp_print_r( $args, $die = true ) {
		$echo = '<pre>' . print_r( $args, true ) . '</pre>';
		if ( $die ) die( $echo );
		else echo $echo;
	}
}
global $loopbuddy;
if (!class_exists("pluginbuddy_loopbuddy")) {
	class pluginbuddy_loopbuddy {
		var $_version = '1.2.3';
		var $_updater = '1.0.7';
		var $_var = 'pluginbuddy_loopbuddy';						// Format: pluginbuddy-pluginnamehere. All lowecase, no dashes.
		var $_name = 'LoopBuddy';									// Pretty plugin name. Only used for display so any format is valid.
		var $_series = '';											// Series name if applicable.
		var $_url = 'http://pluginbuddy.com/purchase/loopbuddy/';	// Purchase URL.
		var $_timeformat = '%b %e, %Y, %l:%i%p';					// Mysql time format.
		var $_timestamp = 'M j, Y, g:iA';							// PHP timestamp format.
		var $_defaults = array(
			'layouts'						=>		array(),
			'queries'						=>		array(),
			'upgrade_layouts'				=>		'0',
			'loops' => array(),
			'debug_mode' => 'off',
			'log_level' => '0'
		);
		var $_layoutdefaults = array(
			'layout'						=>		'default',
			'query'							=>		'',
			'before_loop'					=>		'',
			'after_loop'					=>		'',
			'no_results'					=>		'',
		);
		var $_querydefaults = array(
			'rules'							=>		'',
			'orderby'						=>		'post_date',
			'order'							=>		'DESC',
			'posts_per_page'				=>		'10',
			'pagination'					=>		'on',
			'sql'							=>		'',
			'show_comments' => 'off'
		);
		/*
		
		*/
		var $_widget = '';
		var $_widgetdefaults = array();
				
		// Default constructor. This is run when the plugin first runs.
		function pluginbuddy_loopbuddy() {
			$this->_widget = __( 'Displays custom query and/or layout.', 'it-l10n-loopbuddy' );
			$this->_widgetdefaults = array(
				'title' => __( 'Programmer', 'it-l10n-loopbuddy' ),
			);
			$this->_pluginPath = dirname( __FILE__ );
			$this->_pluginRelativePath = ltrim( str_replace( '\\', '/', str_replace( rtrim( ABSPATH, '\\\/' ), '', $this->_pluginPath ) ), '\\\/' );
			$this->_pluginURL = site_url() . '/' . $this->_pluginRelativePath;
			$this->_pluginBase = plugin_basename( __FILE__  );
			if ( isset( $_SERVER['HTTPS'] ) && $_SERVER['HTTPS'] == 'on' ) { $this->_pluginURL = str_replace( 'http://', 'https://', $this->_pluginURL ); }
			$this->_selfLink = array_shift( explode( '?', $_SERVER['REQUEST_URI'] ) ) . '?page=' . $this->_var;
			
			$this->load();
			
			if ( is_admin() ) {				
				require_once( $this->_pluginPath . '/classes/admin.php' );
				
				add_action( 'init', array( &$this, 'upgrader_register' ), 50 );
				add_action( 'init', array( &$this, 'upgrader_select' ), 100 );
				add_action( 'init', array( &$this, 'upgrader_instantiate' ), 101 );
				
				
				
				// AJAX Hooks
				add_action( 'wp_ajax_pb_loopbuddy_savelayout', array(&$this, 'ajax_savelayout') );
				add_action( 'wp_ajax_pb_loopbuddy_editslotitem', array(&$this, 'ajax_editslotitem') );
				add_action( 'wp_ajax_pb_loopbuddy_deleteslotitem', array(&$this, 'ajax_deleteslotitem') );
				add_action( 'wp_ajax_pb_loopbuddy_assist', array(&$this, 'ajax_assist') );
				add_action( 'wp_ajax_pb_loopbuddy_query_save', array(&$this, 'ajax_query_save') );
				add_action( 'wp_ajax_pb_loopbuddy_layout_browser', array(&$this, 'ajax_layout_browser') );
				add_action( 'wp_ajax_pb_loopbuddy_slotitemsave', array(&$this, 'ajax_slotitemsave') );
				add_action( 'wp_ajax_pb_loopbuddy_queryaddtaxonomy', array(&$this, 'ajax_queryaddtaxonomy') );
				add_action( 'wp_ajax_pb_loopbuddy_queryaddmeta', array(&$this, 'ajax_queryaddmeta') );
				
				add_filter( 'plugin_row_meta', array( &$this, 'filter_plugin_row_meta' ), 10, 2 );
			} else { // Runs when in non-dashboard parts of the site.
				add_shortcode( 'loopbuddy', array( &$this, 'shortcode' ) );
				add_action( $this->_var . '-widget', array( &$this, 'widget' ), 10, 2 ); // Add action to run widget function.
			}
			//More actions
			add_action( 'init', array( &$this, 'init' ) );
			
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
			
									
		} //end constructor
		
		
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
			if ( $error == false ) {
				echo 'updated fade';
			} else {
				echo 'error';
				$log_error = true;
			}
			if ( $error_code != '' ) {
				$message .= '<p><a href="http://ithemes.com/codex/page/' . $this->_name . ':_Error_Codes#' . $error_code . '" target="_new"><i>' . sprintf( __( '%s Error Code %s - Click for more details.', 'it-l10n-loopbuddy' ), $this->_name, $error_code ) . '</i></a></p>';
				$log_error = true;
			}
			if ( $log_error === true ) {
				$this->log( sprintf( __( '%s Error Code: %s', 'it-l10n-loopbuddy' ), $message, $error_code ), 'error' );
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
			
			$tip = '<a href="http://www.youtube.com/embed/' . urlencode( $video_key ) . '?autoplay=1&TB_iframe=1&width=640&height=400" class="thickbox pluginbuddy_tip" title="Video Tutorial - ' . $title . '"><img src="' . $this->_pluginURL . '/images/pluginbuddy_play.png" alt="(video)" /></a>';
			
			if ( $echo_tip ) {
				echo $tip;
			} else {
				return $tip;
			}
		}
				
		//Pass an items array for a full check, otherwise return back the straight HTML
		function get_the_loop( $items_array = array() ) {
			$output_loop = count( $items_array ) > 0 ? true : false;
			$loop_rows = array(
				'title' => "<div class='entry-title'>{title}</div><!--/.entry-title-->",
				'utility_above' => "<div class='entry-utility'>{utility_above}</div><!--/.entry-utility-->
	",
				'meta_above' => "<div class='entry-meta'>{meta_above}</div><!--/.entry-meta-->",
				'content' => "{content}",
				'meta_below' => "<div class='entry-meta'>{meta_below}</div>",
				'utility_below' => "<div class='entry-utility'>{utility_below}</div>"
			);
			//If outputting the loop to the front-end, skip empty divs, otherwise return everything
			$item_keys = array();
			if ( $output_loop ) {
				foreach ( $items_array as $key => $item ) {
					$item_keys[] = $key;
				}
			}
			$html = '';
			$loop_array = array();
			foreach ( $loop_rows as $key => $loop_row ) {
				if ( $output_loop ) {
					if ( !in_array( $key, $item_keys ) && !is_admin() )  continue;
				}
				switch( $key ) {
					case 'title':
					case 'utility_above':
					case 'meta_above':
						$loop_array[ 'header' ] = isset( $loop_array[ 'header' ] ) ? $loop_array[ 'header' ] . $loop_row : '' . $loop_row;
						break;
					case 'content':
						$loop_array[ 'content' ] = isset( $loop_array[ 'content' ] ) ? $loop_array[ 'content' ] . $loop_row: '' . $loop_row;
						break;
					case 'meta_below':
					case 'utility_below':
						$loop_array[ 'footer' ] = isset( $loop_array[ 'footer' ] ) ? $loop_array[ 'footer' ] . $loop_row: '' . $loop_row;
						break;
				} //end switch $key
			} //end foreach
			foreach ( $loop_array as $section => $data ) {
				$html .= sprintf( "<div class='entry-%s'>%s</div>", esc_attr( $section ), $data );
			}
			$html = '<div class="hentry">' . $html . '</div>';
			return $html;
		} //end get_the_loop
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
		
		
		/**
		 * activate()
		 *
		 * Run on plugin activation. Useful for setting up initial stuff.
		 *
		 */
		function activate() {
			$plugin_path = rtrim( plugin_dir_path(__FILE__), '/' ) . '/lib/import/';
			$this->import_layout( $plugin_path . 'loopbuddy_layouts.txt' );
			$this->import_query( $plugin_path . 'loopbuddy_queries.txt' );
		} //end activate
		
		/**
		 * init()
		 *
		 * General initialization function - called by add_action( 'init' ... )
		 *
		 */
		function init() {
			add_action( 'add_meta_boxes', array( &$this, 'init_meta_boxes' ) );
			add_action( 'save_post', array( &$this, 'save_post_meta' ) );
			
			require_once( $this->_pluginPath . '/classes/dynamic_loop.php' );
			
			load_plugin_textdomain( 'it-l10n-loopbuddy', false, dirname( plugin_basename( __FILE__ ) ) . '/lang/' );


		} //end init
		
		/**
		 * init_meta_boxes()
		 *
		 * Initializes the meta boxes for a post or page 
		 * todo - Add support for custom post types
		 *
		 */
		function init_meta_boxes() {
			$post_types = $this->get_post_types();
			foreach ( $post_types as $type ) {
				add_meta_box( 
			        'lb_loop_meta',
			        'LoopBuddy',
			        array( &$this, 'get_meta_html' ),
			        $type,
			        'side', 
			        'core'
			    );
			} //end foreach
		} //init_meta_boxes
		
		/**
		 * save_post_meta()
		 *
		 * Saves the post meta for posts 
		 * todo - Add support for custom post types
		 *
		 */
		function save_post_meta( $post_id ) {
			if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;
			if ( !isset( $_POST[ 'lb_save-meta' ] ) ) return;
			if ( !wp_verify_nonce( $_POST['lb_save-meta'], plugin_basename( __FILE__ ) ) )
      return;
      
      		$post_types = $this->get_post_types();
      		$current_post_type = $_POST[ 'post_type' ];
      		if ( !in_array( $current_post_type, $post_types ) ) return;
      		
      		if ( $current_post_type == 'page' && !current_user_can( 'edit_page', $post_id ) ) return;
      		if ( $current_post_type == 'post' && !current_user_can( 'edit_post', $post_id ) ) return;
      		
      		if ( !isset( $_POST[ 'lb_query' ] ) || !isset( $_POST[ 'lb_layout' ] ) ) return;
      		//Phew, done with permissions.  Let's save some post meta
      		$lb_enabled = isset( $_POST[ 'lb_enable' ] ) ? true : false;
      		$query_id = absint( $_POST[ 'lb_query' ] );
      		$layout_id = absint( $_POST[ 'lb_layout' ] );
      		
      		$post_meta = array(
      			'enabled' => $lb_enabled,
      			'query' => $query_id,
      			'layout' => $layout_id
      		);
      		delete_post_meta( $post_id, 'lb_meta' );
      		update_post_meta( $post_id, '_lb_meta', $post_meta );
		} //end save_post_meta
		
		/**
		 * get_post_types()
		 *
		 * Get the post types this plugin supports
		 * todo - Add support for custom post types
		 *
		 */
		function get_post_types() {
			$args = array(
				'public' => true,
				'show_ui' => true,
			);
			return get_post_types( $args, 'names' );
		} //end get_post_types
		
		function filter_plugin_row_meta( $plugin_meta, $plugin_file ) {
			if ( strstr( $plugin_file, strtolower( $this->_name ) ) ) {
				$plugin_meta[2] = '<a title="Visit plugin site" href="http://pluginbuddy.com/backupbuddy/">Visit PluginBuddy.com</a>';
				return $plugin_meta;
			} else {
				return $plugin_meta;
			}
		}
		
		
		function ajax_slotitemsave() {
			check_admin_referer( $this->_var . '-nonce' );
			
			$old_settings = &$this->_options['layouts'][$_POST['group']]['items'][$_POST['slot_title']][$_POST['slot_id']];
			
			$new_settings['tag'] = $old_settings['tag'];
			foreach( $_POST as $index => $item ) {
				if ( substr( $index, 0, 1 ) == '#' ) {
					$new_settings[substr( $index, 1 )] = $item;
				}
			}
			
			$old_settings = $new_settings; // old_settings was set by reference.
			$this->save();
			echo '1';
			
			die();
		}
		
		function ajax_deleteslotitem() {
			//todo nonce						
			$old_settings = &$this->_options['layouts'][$_POST['group']]['items'][$_POST['slot_title']][$_POST['slot_id']];			
			$this->save();
			echo '1';
			
			die();
		} //end ajax_slotitemdelete
		
		
		function ajax_layout_browser() {
			//wp_enqueue_script( 'jquery' );
			//wp_print_scripts( 'jquery' );
			
			?>
			<style type="text/css">
				.pb_layout {
					float: left; padding: 10px; margin: 10px; border: 1px solid #DFDFDF;
					cursor: pointer;
				}
				.pb_layout:hover {
					border-color: #2786B2;
					background: #EAF2FA;
				}
			</style>
			<?php
			
			$wp_upload_dir = WP_UPLOAD_DIR();
			$layout_root = $wp_upload_dir['basedir'] . '/loopbuddy/layouts/';
			
			$layouts = scandir( $layout_root );
			foreach ( $layouts as $layout ) {
				if ( ( $layout != '.' ) && ( $layout != '..' ) ) {
					echo '<div class="pb_layout" id="pb_loopbuddy_' . $layout . '"><h3 style="margin-top: 5px; margin-bottom: 5px;">' . $layout . '</h3><img src="' . $wp_upload_dir['baseurl'] . '/loopbuddy/layouts/' . $layout . '/screenshot.png" width="150" /></div>';
				}
			}
			
			die();
		}
		
		
		function ajax_savelayout() {
			$layout = &$this->get_layout( $_REQUEST['group'] );
			
			if ( !isset( $layout['items'] ) ) {
				die( __( 'Error #533. No layout data structure found. Please re-load the defaults for this layout.', 'it-l10n-loopbuddy' ) );
			}
			
			// Hold original group pre-alterations.
			$original_group = $layout;
			
			// Clear out changed slot so we can re-insert in proper order.
			unset( $layout['items'][$_POST['slot']] );
			
			$items = explode( ',', $_POST['items'] );
			// Loop through each item in this slot now.
			foreach( $items as $item ) {
				if ( substr( $item, 0, 3) == 'new' ) { // New item so we need to insert it.
					$this_unique_id = uniqid();
					$layout['items'][$_POST['slot']][$this_unique_id]['tag'] = substr( $item, 4 );
					
					// Set defaults for this slot item.
					require_once( $this->_pluginPath . '/classes/ajax_slotitems.php' );
					$this->_slot_items = new pluginbuddy_loopbuddy_slotitems($this);
					$settings = &$layout['items'][$_POST['slot']][$this_unique_id];
					$default_settings = $this->_slot_items->get_tag_settings( substr( $item, 4 ), $this->_slot_items->_tags );
					
					$settings = wp_parse_args( (array)$settings, $default_settings );
				} else { // Existing item so we need to move it.
					// Loop through each existing slot to see if this item is in it.
					foreach( $original_group['items'] as $layout_item_key => $layout_item ) {
						// If we found the previous position copy it over and remove it.
						/*
						echo '<pre>';
						print_r( $layout_item );
						echo '</pre>';
						*/
						if ( array_key_exists( $item, $layout_item ) ) {
							//echo 'found item to move: ' . $layout_item_key;
							$layout['items'][$_POST['slot']][$item] = $original_group['items'][$layout_item_key][$item]; // Copy from old location to new.
							
							//echo 'tag: '.$layout['items'][$layout_item_key][$item]['tag'] . ', postslot: ' . $_POST['slot'] . ', thisslot: ' . $layout_item_key . "\n";
							if ( $_POST['slot'] != $layout_item_key ) {
								unset( $layout['items'][$layout_item_key][$item] );
							}
							/*
							echo '<pre>';
							print_r( $layout['items'][$layout_item_key] );
							echo '</pre>';
							echo 'yo' . $layout['items'][$layout_item_key][$item];
							*/
							//unset( $layout['items'][$layout_item_key] ); // Delete old location.
							//if ( !empty( $layout['items'][$layout_item_key][$item] ) ) {
								//unset( $layout['items'][$layout_item_key][$item] );
							//}
							break; // Found this item so we can force moving on to the next by breaking out of this loop.
						}
					}
				}
			}
			
			
			unset( $layout['items'][''] ); // Empty out the `just recently deleted` slot.
			
			$this->save();
			die( json_encode( array( 'unique_id' => isset( $this_unique_id ) ? $this_unique_id : 0 ) ) );
		}
		
		function ajax_query_save() {
			$group = &$this->get_query( $_GET['edit'] );
			$group['rules'] = $_POST['data'];
			
			/* Generate SQL Query from new query settings. */
			require_once( $this->_pluginPath . '/classes/queryitems.php' );
			$this->_query_items = new pluginbuddy_loopbuddy_queryitems($this);
			$group['sql'] = $this->_query_items->generate_sql( $group['rules'] );
			$this->save();
			
			die('1');
		}
		
		
		function ajax_editslotitem() {
			echo '<!-- Editing `' . htmlentities( $_GET['id'] ) . '` in slot `' . htmlentities( $_GET['slot'] ) . '` -->';
			
			$group = &$this->get_layout( $_GET['group'] );
			
			if ( !isset( $group['items'] ) ) {
				die( __( 'Error #3533. No layout data structure found. Please re-load the defaults for this layout.', 'it-l10n-loopbuddy' ) );
			}
			if ( !isset( $group['items'][$_GET['slot']][$_GET['id']] ) ) {
				die( __( 'Error #9782. No such item found in the requested slot. It may have already been deleted. Please re-load the page and try again.', 'it-l10n-loopbuddy' ) );
			}
			
			$slot_item = &$group['items'][$_GET['slot']][$_GET['id']];
			
			require_once( $this->_pluginPath . '/classes/ajax_slotitems.php' );
			
			$edit = new pluginbuddy_loopbuddy_slotitems($this);
			
			$edit->edit( $slot_item['tag'] );
		}
		
		function ajax_queryaddtaxonomy() {
			include_once( $this->_pluginPath . '/classes/queryitems.php' );
			$lb_query_items = new loopbuddy_queryitems( $this );
			$taxonomy_count = isset( $_POST[ 'taxonomy_count' ] ) ? absint( $_POST[ 'taxonomy_count' ] ) : 0;
			$taxonomy_count += 1;
			ob_start();
			$lb_query_items->display_taxonomy( array( 'taxonomy_count' => $taxonomy_count ) );
			$html = ob_get_clean();
			
			$return = array( 
				'taxonomy_count' => $taxonomy_count,
				'html' => $html
			);
			die( json_encode( $return ) );
		} //end ajax_queryaddtaxonomy
		
		function ajax_queryaddmeta() {
			include_once( $this->_pluginPath . '/classes/queryitems.php' );
			$lb_query_items = new loopbuddy_queryitems( $this );
			$meta_count = isset( $_POST[ 'meta_count' ] ) ? absint( $_POST[ 'meta_count' ] ) : 0;
			$meta_count += 1;
			ob_start();
			$lb_query_items->display_meta( array( 'meta_count' => $meta_count ) );
			$html = ob_get_clean();
			
			$return = array( 
				'meta_count' => $meta_count,
				'html' => $html
			);
			die( json_encode( $return ) );
		} //end ajax_queryaddtaxonomy

		
		function ajax_assist() {
			?>
			<html>
			<head>
			<title><?php _e( 'LoopBuddy Ajax Assist', 'it-l10n-loopbuddy' ); ?></title>
			<?php
			wp_enqueue_script( 'jquery' );
			wp_admin_css( 'global' );
			wp_admin_css( 'admin' );
			wp_admin_css();
			wp_admin_css( 'colors' );
			do_action('admin_print_styles');
			do_action('admin_print_scripts');
			do_action('admin_head');
			?>
			<style type='text/css'>
				.pb_ajax_assist {
					margin-bottom: 40px;
					margin-top: 10px;
				}
			</style>
			<script type="text/javascript">
				jQuery(document).ready(function($){
					jQuery('.pb_ajax_assist_tr').live( "click", function(e) {
						//If we're searching for post types - change the post type and post status
						var post_type = jQuery( 'input[name="post_type"]:first' ).val();
						var post_status = jQuery( 'input[name="post_status"]:first' ).val();
						if ( post_type != 'false' ) {
						
							jQuery( parent.document ).find( 'input[name^="loopbuddy[post_type]"]' ).attr( 'checked', false );
							jQuery( parent.document ).find( 'input[name^="loopbuddy[post_status]"]' ).attr( 'checked', false );
							jQuery( parent.document ).find( "#loopbuddy_" + post_type ).attr( 'checked', true );
							jQuery( parent.document ).find( "#loopbuddy_" + post_status ).attr( 'checked', true );
						}
						var results = $( 'input[name^="pb_items[]"]:checked' ).map(function() {
 							return jQuery( this ).val();
						}).get().join(',');
						
						jQuery( parent.document ).find( '#<?php echo esc_js( $_GET['id'] ); ?>').val( results );
					});
					//For taxonomy searches
					jQuery( '.pb_ajax_assist_taxonomy').live( 'click', function( e ) {
						var taxonomy = '<?php echo esc_js( isset( $_POST[ 'taxonomy' ] ) ? $_POST[ 'taxonomy' ] : '' ); ?>';
						var term_length = $( 'input[name^="pb_items[]"]:checked' ).length;
						var results = $( 'input[name^="pb_items[]"]:checked' ).map(function() {
 							return jQuery( this ).val();
						}).get().join(',');
						
						jQuery( parent.document ).find( '#taxonomy_name<?php echo esc_js( $_GET['id'] ); ?>').val( taxonomy );
						jQuery( parent.document ).find( '#taxonomy_terms<?php echo esc_js( $_GET['id'] ); ?>').val( results );
					} );
				});
			</script>
			</head>
			<body>
			<div class='wrap'>
			<?php
			$show_submit = $show_search = true;
			$search_box = $interface = '';
			ob_start();
			if ( !empty( $_POST['s'] ) ) { $search_term = $_POST['s']; } else { $search_term = ''; }
			?>
			<div style="float: right;">
			<p>
			<form method="post" action="<?php esc_url( add_query_arg( array( 'action' => 'pb_loopbuddy_assist', 'id' => esc_js( $_GET[ 'id' ] ), 'type' => esc_js( $_GET[ 'type' ] ) ), admin_url( 'admin-ajax.php' ) ) ); ?>">
				<input type='hidden' name='post_type' value='<?php echo esc_attr( isset( $_POST[ 'post_type' ] ) ? $_POST[ 'post_type' ] : 'false' );?>' />
				<input type='hidden' name='post_status' value='<?php echo esc_attr( isset( $_POST[ 'post_status' ] ) ? $_POST[ 'post_status' ] : 'false' );?>' />
				<input type='hidden' name='taxonomy' value='<?php echo esc_attr( isset( $_POST[ 'taxonomy' ] ) ? $_POST[ 'taxonomy' ] : 'false' );?>' />
				<input type='hidden' name='include_exclude' value='<?php echo esc_attr( isset( $_POST[ 'include_exclude' ] ) ? $_POST[ 'include_exclude' ] : 'false' );?>' />
				<input type="text" name="s" value="<?php echo esc_attr( $search_term ); ?>" />
				<input type="submit" class="button-secondary thickbox" value="<?php esc_attr_e( 'Search', 'it-l10n-loopbuddy' ); ?>" />
			</form></p>
			</div>
			<br /><br />
			<?php
			$search_box = ob_get_clean();
			
			ob_start();
			global $wpdb;
			$multiselect = false;
			$search = isset( $_POST[ 's' ] ) ? $wpdb->escape( $_POST[ 's' ] ) : '';
			$type = $_REQUEST[ 'type' ];
			/**********
			POSTS
			**********/
			$display_types = array( 
				'post_id' => array(
					'post_type' => 'post',
					'select' => 'multiple',
					'display' => 'ID'
				)
				, 'page_id' => array(
					'post_type' => 'page',
					'select' => 'multiple',
					'display' => 'ID'
				)
				, 'post_slug' => array(
					'post_type' => 'post',
					'select' => 'multiple',
					'display' => 'post_name'
				)
				, 'page_slug' => array(
					'post_type' => 'page',
					'select' => 'multiple',
					'display' => 'post_name'
				)
				, 'post_ids' => array(
					'post_type' => 'post',
					'select' => 'multiple',
					'display' => 'ID'
				) );
			if ( array_key_exists( $type, $display_types ) ) {
				if ( !isset( $_POST[ 'post_type' ] ) ) {
					$show_submit = $show_search = false;
					//Display post type search box
					include_once( $this->_pluginPath . '/classes/queryitems.php' );
					$lb_query_items = new loopbuddy_queryitems( $this );
					$post_types = get_post_types( array(
				                 		'public' => true
				                 	), 'objects' );
				     if ( $post_types ) {
				          ob_start();
				          foreach ( $post_types as $key => $post_type ) {
				          	$label = $key;	
				          	?>
				          	<option value='<?php echo esc_attr( $key ); ?>' <?php selected( isset( $_POST[ 'post_type' ] ) ? $_POST[ 'post_type' ] : false, $key); ?>><?php echo esc_html( $label ); ?></option>
				          	<?php
				          } //end foreach post_types
				          $select = ob_get_clean();
				          ?>
				          <form method="post" action="<?php echo add_query_arg( array( 'action' => 'pb_loopbuddy_assist', 'type' => esc_js( $type ), 'id' => esc_js( $_GET[ 'id' ] ) ), admin_url( 'admin-ajax.php' ) );?>">

				          <table class="widefat pb_ajax_assist">
						<tbody>
						<tr>
							<td><p><?php esc_html_e( 'Post Type:', 'it-l10n-loopbuddy' ); ?></p></td>
							<td> <select  id="post_type" name="post_type">
				         			<?php echo $select; ?>
				          		</select></td>
						</tr>
						<tr>
							<td><p><?php esc_html_e( 'Post Status:', 'it-l10n-loopbuddy' ); ?></p></td>
							<td><select id='post_status' name='post_status'>
				          		<?php $lb_query_items->post_status( array( 'type' => 'select' ) ); ?>
				          		</select></td>
						</tr>
						<tr>
							<td><p><?php esc_html_e( 'Show Sticky Posts Only?', 'it-l10n-loopbuddy' ); ?></p></td>
							<td>
								<input type='checkbox' name='sticky_posts' value='yes' id='sticky_posts_yes' /><label for='sticky_posts_yes'><?php esc_html_e( 'Yes', 'it-l10n-loopbuddy' ); ?></label>
							</td>
						</tr>
				          <tr>
				          <td colspan='2'>
				          	<input type="submit" class="button-secondary thickbox" value="<?php esc_attr_e( 'Submit', 'it-l10n-loopbuddy' ); ?>" name='post_type_search' />
							</form>
				          </td>
				          </tr>		
							
				          
				     <?php
				     } //end if $post_type
			     } else { /* someone has selected a post type - show them */
			     	$post_type = $_POST[ 'post_type' ];
			     	$post_status = $_POST[ 'post_status' ];
			     	$sticky_posts = isset( $_POST[ 'sticky_posts' ] ) ? true : false;
			     	$sticky_posts_to_include = get_option( 'sticky_posts', array() );
			     	$sticky_posts_query = '';
			     	if ( $sticky_posts && count( $sticky_posts_to_include ) > 0 ) {
			     		$sticky_posts_query = sprintf( 'AND ID IN (%s)', implode( ',', $sticky_posts_to_include ) );
			     	}
					?>
					<table class="widefat pb_ajax_assist">
						<thead><tr><th>&nbsp;</th></th><th><?php esc_html_e( 'ID', 'it-l10n-loopbuddy' ); ?></th><th><?php esc_html_e( 'Post Title', 'it-l10n-loopbuddy' ); ?></th><th><?php esc_html_e( 'Post Date', 'it-l10n-loopbuddy' ); ?></th></tr></thead>
						<tfoot><tr><th>&nbsp;</th><th><?php esc_html_e( 'ID', 'it-l10n-loopbuddy' ); ?></th><th><?php esc_html_e( 'Post Title', 'it-l10n-loopbuddy' ); ?></th><th><?php esc_html_e( 'Post Date', 'it-l10n-loopbuddy' ); ?></th></tr></tfoot>
						<tbody>
					<?php
					$alt = true;
					global $wpdb;
								
					$search_query = "AND post_title LIKE %s";
					if ( !empty( $search ) ) {
						$search = "%{$search}%";
					} else {
						$search = $search_query = '';
					}
					$display_type = $display_types[ $type ];
					
					$sql = $wpdb->prepare( "SELECT ID, post_title, post_date, post_name from $wpdb->posts WHERE post_status ='{$post_status}' AND post_type='{$post_type}'  {$search_query} {$sticky_posts_query} ORDER BY post_date DESC", $search);
					$posts = $wpdb->get_results( $sql, ARRAY_A);
					foreach( $posts as $post ) {
						?>
						<tr <?php echo $alt === 'true' ? 'class="alternate"' : ''; ?> id="pb_ajax_assist_id_<?php echo esc_attr( $post[ 'ID' ] ); ?>">
							<th class="check-column" scope="row"><input class='pb_ajax_assist_tr' type="<?php echo $display_type[ 'select' ] == 'single' ? 'radio' : 'checkbox'; ?>" value="<?php echo esc_attr( $post[ $display_type[ 'display' ] ] ); ?>" name="pb_items[]"></th>
							<td><?php echo esc_html( $post[ 'ID' ] ); ?></td>
							<td><?php echo stripslashes( esc_html( $post[ 'post_title' ] ) ); ?></td>
							<td><?php echo stripslashes( esc_html( $post[ 'post_date' ] ) ); ?></td>
	
							</td>
						</tr>
						<?php
						if ( $alt === false ) { $alt = true; } else { $alt = false; }
					} //end foreach
				?>			
				</tbody>
				</table>
				<?php
				} //end isset $_POST[ 'post_type' ]
			} //end if type = post_types 
			/**********
			CATEGORIES
			**********/
			$display_types = array( 
				'cat_ids' => array(
					'post_type' => 'post',
					'select' => 'multiple',
					'display' => 'ID'
				)
				, 'cat_slug' => array(
					'post_type' => 'page',
					'select' => 'single',
					'display' => 'term_slug'
				) );
			if ( array_key_exists( $type, $display_types ) ) {
				?>
				<table class="widefat pb_ajax_assist">
					<thead><tr><th>&nbsp;</th></th><th><?php esc_html_e( 'ID', 'it-l10n-loopbuddy' ); ?></th><th><?php esc_html_e( 'Category Title', 'it-l10n-loopbuddy' ); ?></th><th><?php esc_html_e( 'Slug', 'it-l10n-loopbuddy' ); ?></th></tr></thead>
					<tfoot><tr><th>&nbsp;</th><th><?php esc_html_e( 'ID', 'it-l10n-loopbuddy' ); ?></th><th><?php esc_html_e( 'Category Title', 'it-l10n-loopbuddy' ); ?></th><th><?php esc_html_e( 'Slug', 'it-l10n-loopbuddy' ); ?></th></tr></tfoot>
					<tbody>
				<?php
				$alt = true;
				global $wpdb;
				$search_query = "AND {$wpdb->terms}.name LIKE %s";
				if ( !empty( $search ) ) {
					$search = "%{$search}%";
				} else {
					$search = $search_query = '';
				}
				$display_type = $display_types[ $type ];				
				
				$sql = $wpdb->prepare( "SELECT $wpdb->terms.term_id as ID,$wpdb->terms.name as term_name, $wpdb->terms.slug as term_slug,$wpdb->term_taxonomy.count FROM $wpdb->terms,$wpdb->term_taxonomy WHERE $wpdb->terms.term_id= $wpdb->term_taxonomy.term_id AND $wpdb->term_taxonomy.taxonomy='category' {$search_query} ORDER BY term_name DESC", $search);
				$cats = $wpdb->get_results( $sql, ARRAY_A);
				foreach( $cats as $cat ) {
					?>
					<tr <?php echo $alt === 'true' ? 'class="alternate"' : ''; ?> id="pb_ajax_assist_id_<?php echo esc_attr( $cat[ 'ID' ] ); ?>">
						<th class="check-column" scope="row"><input class='pb_ajax_assist_tr' type="<?php echo $display_type[ 'select' ] == 'single' ? 'radio' : 'checkbox'; ?>" value="<?php echo esc_attr( $cat[ $display_type[ 'display' ] ] ); ?>" name="pb_items[]"></th>
						<td><?php echo esc_html( $cat[ 'ID' ] ); ?></td>
						<td><?php echo stripslashes( esc_html( $cat[ 'term_name' ] ) ); ?></td>
						<td><?php echo stripslashes( esc_html( $cat[ 'term_slug' ] ) ); ?></td>

						</td>
					</tr>
					<?php
					if ( $alt === false ) { $alt = true; } else { $alt = false; }
				} //end foreach
			?>			
			</tbody>
			</table>
			<?php
			} //end if type = Categories
			/**********
			TAGS
			**********/
			$display_types = array( 
				'tag_id' => array(
					'post_type' => 'post',
					'select' => 'single',
					'display' => 'ID'
				)
				, 'tag_ids' => array(
					'post_type' => 'page',
					'select' => 'multiple',
					'display' => 'ID'
				) );
			if ( array_key_exists( $type, $display_types ) ) {
				?>
				<table class="widefat pb_ajax_assist">
					<thead><tr><th>&nbsp;</th></th><th><?php esc_html_e( 'ID', 'it-l10n-loopbuddy' ); ?></th><th><?php esc_html_e( 'Tag Title', 'it-l10n-loopbuddy' ); ?></th><th><?php esc_html_e( 'Slug', 'it-l10n-loopbuddy' ); ?></th></tr></thead>
					<tfoot><tr><th>&nbsp;</th><th><?php esc_html_e( 'ID', 'it-l10n-loopbuddy' ); ?></th><th><?php esc_html_e( 'Tag Title', 'it-l10n-loopbuddy' ); ?></th><th><?php esc_html_e( 'Slug', 'it-l10n-loopbuddy' ); ?></th></tr></tfoot>
					<tbody>
				<?php
				$alt = true;
				global $wpdb;
				$search_query = "AND {$wpdb->terms}.name LIKE %s";
				if ( !empty( $search ) ) {
					$search = "%{$search}%";
				} else {
					$search = $search_query = '';
				}
				$display_type = $display_types[ $type ];				
				
				$sql = $wpdb->prepare( "SELECT $wpdb->terms.term_id as ID,$wpdb->terms.name as term_name, $wpdb->terms.slug as term_slug,$wpdb->term_taxonomy.count FROM $wpdb->terms,$wpdb->term_taxonomy WHERE $wpdb->terms.term_id= $wpdb->term_taxonomy.term_id AND $wpdb->term_taxonomy.taxonomy='post_tag' {$search_query} ORDER BY term_name DESC", $search);
				$tags = $wpdb->get_results( $sql, ARRAY_A);
				foreach( $tags as $tag ) {
					?>
					<tr <?php echo $alt === 'true' ? 'class="alternate"' : ''; ?> id="pb_ajax_assist_id_<?php echo esc_attr( $tag[ 'ID' ] ); ?>">
						<th class="check-column" scope="row"><input class='pb_ajax_assist_tr' type="<?php echo $display_type[ 'select' ] == 'single' ? 'radio' : 'checkbox'; ?>" value="<?php echo esc_attr( $tag[ $display_type[ 'display' ] ] ); ?>" name="pb_items[]"></th>
						<td><?php echo esc_html( $tag[ 'ID' ] ); ?></td>
						<td><?php echo stripslashes( esc_html( $tag[ 'term_name' ] ) ); ?></td>
						<td><?php echo stripslashes( esc_html( $tag[ 'term_slug' ] ) ); ?></td>

						</td>
					</tr>
					<?php
					if ( $alt === false ) { $alt = true; } else { $alt = false; }
				} //end foreach
			?>			
			<?php
			} //end if type = tags
			
			//Taxonomies!  Woot!
			//Basically we do a search for the taxonomy, then let the user select the terms - This updates the front-end with both the taxonomy name and term ids
			if ( $type == 'tax_name' ):
				if ( !isset( $_POST[ 'taxonomy' ] ) ) {
				$show_submit = $show_search = false;
			?>
			<table class="widefat pb_ajax_assist">
				<tbody>
				<tr>
					<td>
						<form method="post" action="<?php echo add_query_arg( array( 'action' => 'pb_loopbuddy_assist', 'type' => esc_js( $type ), 'id' => esc_js( $_GET[ 'id' ] ) ), admin_url( 'admin-ajax.php' ) );?>">
						
						<select name='taxonomy'>
						<?php
						$taxonomies = get_taxonomies( array( 'public' => true ), 'names');
						foreach ( $taxonomies as $taxonomy ) {
							?>
							<option value='<?php echo esc_attr( $taxonomy ); ?>'><?php echo esc_html( $taxonomy ); ?></option>
							<?php
						} //end foreach $taxonomies
					?>
					</select>
					<input type="submit" class="button-secondary thickbox" value="Get Terms" name='taxonomy_search' />
					</form>
					</td>
				</tr>
			<?php
				} else {
				$search_query = "AND {$wpdb->terms}.name LIKE %s";
				if ( !empty( $search ) ) {
					$search = "%{$search}%";
				} else {
					$search = $search_query = '';
				}

			?>
			<table class="widefat pb_ajax_assist">
				<thead><tr><th>&nbsp;</th></th><th><?php esc_html_e( 'ID', 'it-l10n-loopbuddy' ); ?></th><th><?php esc_html_e( 'Term Title', 'it-l10n-loopbuddy' ); ?></th><th><?php esc_html_e( 'Slug', 'it-l10n-loopbuddy' ); ?></th></tr></thead>
				<tfoot><tr><th>&nbsp;</th><th><?php esc_html_e( 'ID', 'it-l10n-loopbuddy' ); ?></th><th><?php esc_html_e( 'Term Title', 'it-l10n-loopbuddy' ); ?></th><th><?php esc_html_e( 'Slug', 'it-l10n-loopbuddy' ); ?></th></tr></tfoot>
				<tbody>
				<?php
					$alt = true;
					$sql = $wpdb->prepare( "SELECT $wpdb->terms.term_id as ID,$wpdb->terms.name as term_name, $wpdb->terms.slug as term_slug,$wpdb->term_taxonomy.count FROM $wpdb->terms,$wpdb->term_taxonomy WHERE $wpdb->terms.term_id= $wpdb->term_taxonomy.term_id AND $wpdb->term_taxonomy.taxonomy=%s {$search_query} ORDER BY term_name DESC", $_POST[ 'taxonomy' ], $search );
					$terms = $wpdb->get_results( $sql, ARRAY_A);
					foreach( $terms as $term ) {
						?>
						<tr <?php echo $alt === 'true' ? 'class="alternate"' : ''; ?> id="pb_ajax_assist_id_<?php echo esc_attr( $term[ 'ID' ] ); ?>">
							<th class="check-column" scope="row"><input class='pb_ajax_assist_taxonomy' type="checkbox" value="<?php echo stripslashes( esc_html( $term[ 'term_slug' ] ) ); ?>" name="pb_items[]"></th>
							<td><?php echo esc_html( $term[ 'ID' ] ); ?></td>
							<td><?php echo stripslashes( esc_html( $term[ 'term_name' ] ) ); ?></td>
							<td><?php echo stripslashes( esc_html( $term[ 'term_slug' ] ) ); ?></td>
						</tr>
						<?php
						if ( $alt === false ) { $alt = true; } else { $alt = false; }
					} //end foreach
				} //end if a taxonomy search
			endif;
			
			//Authors
			if ( $type == 'author' ) {
				?>
				<table class="widefat pb_ajax_assist">
					<thead><tr><th>&nbsp;</th></th><th><?php esc_html_e( 'ID', 'it-l10n-loopbuddy' ); ?></th><th><?php esc_html_e( 'User', 'it-l10n-loopbuddy' ); ?></th></tr></thead>
					<tfoot><tr><th>&nbsp;</th><th><?php esc_html_e( 'ID', 'it-l10n-loopbuddy' ); ?></th><th><?php esc_html_e( 'User', 'it-l10n-loopbuddy' ); ?></th></tr></tfoot>
					<tbody>
				<?php
				$alt = true;
				global $wpdb;
				$search_query = "AND user_nicename LIKE %s";
				if ( !empty( $search ) ) {
					$search = "%{$search}%";
				} else {
					$search = $search_query = '';
				}
				
				$sql = $wpdb->prepare( "SELECT ID, user_nicename from $wpdb->users WHERE 1=1 {$search_query} ORDER BY display_name DESC", $search);
				$users = $wpdb->get_results( $sql, ARRAY_A);
				foreach( $users as $user ) {
					?>
					<tr <?php echo $alt === 'true' ? 'class="alternate"' : ''; ?> id="pb_ajax_assist_id_<?php echo esc_attr( $user[ 'ID' ] ); ?>">
						<th class="check-column" scope="row"><input class='pb_ajax_assist_tr' type="checkbox" value="<?php echo esc_attr( $user[ 'ID' ] ); ?>" name="pb_items[]"></th>
						<td><?php echo esc_html( $user[ 'ID' ] ); ?></td>
						<td><?php echo stripslashes( esc_html( $user[ 'user_nicename' ] ) ); ?></td>
					</tr>
					<?php
					if ( $alt === false ) { $alt = true; } else { $alt = false; }
				} //end foreach
			}
			//end Authors
			?>
			</tbody>
			</table>
			
			</div><!--/.wrap-->
			<?php
			$interface = ob_get_clean();
			if ( $show_search ) echo $search_box;
			echo $interface;
			if ( $show_submit ) {
			//This submit button just closes the window.  It doesn't do anything.  The JavaScript is what populates the text inputs
			?>
			<div style='width: 100%; padding: 5px; background: #C9C9C9; border-top: 1px solid #B9B9B9;  position: fixed; bottom: 0;'>
			<input type='button' class='button-primary' value='<?php esc_attr_e( 'Submit', 'it-l10n-loopbuddy' ); ?>' style='float: right; margin-right: 30px' onclick='parent.tb_remove(); return false;'/>
			</div>
			<?php
			} //end show_submit
			?>
			</body>
			</head>
			</html>
			<?php 
			exit;
		} //end ajax_assist
		
		/* For pre-WP3.1 users. */
		function ajax_internal_linking() {
			require_once( $this->_pluginPath . '/lib/internal-linking.php' );
			
			$args = array();
			
			if ( isset( $_POST['search'] ) )
				$args['s'] = stripslashes( $_POST['search'] );
			$args['pagenum'] = ! empty( $_POST['page'] ) ? absint( $_POST['page'] ) : 1;
			
			$results = wp_link_query( $args );
			
			if ( ! isset( $results ) )
				die( '0' );
			
			echo json_encode( $results );
			echo "\n";
			
			exit;
		}
		
		// OPTIONS STORAGE //////////////////////
		
		
		function save() {
			add_option($this->_var, $this->_options, '', 'no'); // 'No' prevents autoload if we wont always need the data loaded.
			update_option($this->_var, $this->_options);
			
			return true;
		}
		
		//Gets an updated version of $this->_options[ 'loops' ]
		function get_loops() {
			$options = ( array )$this->_options;
			
			
			//Populate loop items
			$loops = array();
			//POST TYPES
			$post_types = get_post_types( array( 'public' => true, 'show_ui' => true ), 'objects' );
			$post_types_to_exclude = array(
					'pb_accordion_items',
			);
			$post_types_to_exclude = apply_filters( 'pb_loopbuddy_exclude_post_types', $post_types_to_exclude ); //For other plugin authors
			
			foreach ( $post_types as $post_type => $args ) {
				if ( in_array( $post_type, $post_types_to_exclude ) ) continue;
				$label = isset( $args->labels->name ) ? $args->labels->name : $post_type;
				$loops[ $post_type ] = array(
					'type' => 'post_types',
					'label' => $label,
					'query' => 'default',
					'layout' => 'default',
				);
			}
			//Add in non show_ui post types
			/*$loops[ 'mediapage' ] = array(
				'type' => 'post_types',
				'label' => __( 'Media Pages', 'it-l10n-loopbuddy' ),
				'query' => 'default',
				'layout' => 'default', 
			);*/
			$loops[ 'attachment' ] = array(
				'type' => 'post_types',
				'label' => __( 'Attachments', 'it-l10n-loopbuddy' ),
				'query' => 'default',
				'layout' => 'default', 
			);
			
			//ARCHIVES
			$archive_loop_items = array(
				'day_archive' => __( 'Daily Archives', 'it-l10n-loopbuddy' ),
				'month_archive' => __( 'Monthly Archives', 'it-l10n-loopbuddy' ),
				'year_archive' => __( 'Yearly Archives', 'it-l10n-loopbuddy' ),
			);
			foreach ( $archive_loop_items as $key => $item ) {
				$loops[ $key ] = array(
					'type' => 'archives',
					'label' => $item,
					'query' => 'default',
					'layout' => 'default',
				);
			}
			//TAXONOMIES
			$taxonomies = get_taxonomies( array( 'public' => true, 'show_ui' => true ), 'objects' );
			if ( $taxonomies ) {
				foreach ( $taxonomies as $slug => $taxonomy ) {
					$name = isset( $taxonomy->labels->name ) ? $taxonomy->labels->name : $slug;
					$loops[ $slug ] = array(
						'type' => 'taxonomy',
						'label' => $name,
						'query' => 'default',
						'layout' => 'default',
					);
				} //end foreach $taxonomies
			}			
			//GENERAL
			$general_loop_items = array(
				'home' => __( 'Home Page', 'it-l10n-loopbuddy' ),
				'front' => __( 'Front Page', 'it-l10n-loopbuddy' ),
				'search' => __( 'Search Results', 'it-l10n-loopbuddy' ),
				'404' => __( '404 Page', 'it-l10n-loopbuddy' ),
			);
			foreach ( $general_loop_items as $key => $item ) {
				$loops[ $key ] = array(
					'type' => 'general',
					'label' => $item,
					'query' => 'default',
					'layout' => 'default',
				);
			}
			
			//Add new defaults to options
			foreach ( $loops as $key => $data ) {
				if ( !array_key_exists( $key, $options[ 'loops' ] ) ) {
					$options[ 'loops' ][ $key ] = $data;
				} 
			}
			
			//Clear out old options that no longer exist
			foreach ( $options[ 'loops' ] as $key => $data ) {
				if ( !array_key_exists( $key, $loops ) ) {
					unset( $options[ 'loops' ][ $key ] );
				}
			}
			

			$this->_options[ 'loops' ] = $options[ 'loops' ];
			return $options[ 'loops' ];
		}
		
		function load() {
			$this->_options=get_option($this->_var);
			
			
			$options = wp_parse_args( (array)$this->_options, $this->_defaults );	
			
			if ( !$this->_options ) {

				// Defaults existed that werent already in the options so we need to update their settings to include some new options.
				$this->_options = $options;
				
				$this->save();
			}
			

			return true;
		}
		
		
		function shortcode( $atts ) {
			$defaults = array(
				'query_id' => -1,
				'layout_id' => -1,
			);
			extract( wp_parse_args( $atts, $defaults ) );
			$this->load();
			if ( $query_id == -1 || $layout_id == -1 ) return '';
			return $this->render_loop( $query_id, $layout_id );
		} //end shortcode
		
		//Renders the loop, returns true if successful
		function render_loop( $query_id, $layout_id ) {
			global $wpdb, $wp_query, $post;
			$temp_query = $wp_query;
			$temp_post = $post;
			
			
			//If $query_id != false, use the default query
			if ( $query_id != -1 ) {
				$query_id = absint( $query_id );
				$query = &$this->get_query( $query_id );
				
				include_once( $this->_pluginPath . '/classes/queryitems.php' );
				$lb_query_items = new loopbuddy_queryitems( $this );
				
				$query_args = $lb_query_items->get_wp_query( $query_id );

				if ( is_wp_error( $query_args ) ) return false;
				
				
	
				//If Use Current Post (for single or pages), set the p = variable
				if ( $query_args[ 'use_current' ] == 'on' && ( is_single() || is_page() ) ) {
					$post_id = $wp_query->get_queried_object_id();
					if ( $post_id ) {
						$query_args[ 'p' ] = $post_id;
					}
				}
				unset( $query_args[ 'use_current' ] );
				
				//There's some issues with the nopaging argument, so let's unset it and assign it to a different variable
				$enable_paging = $query_args[ 'nopaging' ];
				unset( $query_args[ 'nopaging' ] );
				
				//For comments
				$enable_comments = $query_args[ 'enable_comments' ];
				unset( $query_args[ 'enable_comments' ] );
				
				//Merge the queries
				if ( $query_args[ 'merge_queries' ] == 'on' ) {
					$default_query = $wp_query->query;
					$query_args = wp_parse_args( $query_args, $default_query ); 
					unset( $query_args[ 'merge_queries' ] );
					//if attachments, just use the default query
					if ( isset( $default_query[ 'attachment' ] ) ) {
						$query_args = $default_query;
					}
				}
				//For paging
				if ( get_query_var( 'paged' ) > 0 && $enable_paging == 'on' ) {
					$query_args[ 'paged' ] = get_query_var( 'paged' );
				}
				
				//Do the query
				if ( $this->_options[ 'debug_mode' ] == 'on' && current_user_can( 'administrator' ) ) {
					wp_print_r( $query_args, false );
					
				}
				
				$wp_query = new WP_Query( $query_args );
				if ( $layout_id == -1 ) {
					return new WP_Error( 'No default layout' );
				}

			} //end if !$query_id
			
			$layout_id = absint( $layout_id );
			
			$layout = &$this->get_layout( $layout_id );
						
			$posts = $wp_query->posts; //For some reason, $wp_query->get_posts wasn't returning accurate results
			if ( empty( $posts ) || !is_array( $posts ) ) { // No results so there is no need to continue. If false then continue to process the loop...
				return $layout['no_results'];
			}
			$default_loop_classes = array( 'loop' );
			if ( isset( $layout[ 'loop_css' ] ) ) {
				if ( !empty( $layout[ 'loop_css' ] ) ) {
					array_push( $default_loop_classes, esc_attr( $layout[ 'loop_css' ] ) );
				}
			}
			$return = sprintf( "<div class='%s'>", implode( ' ', $default_loop_classes ) );
			
			// Display `before loop` content.
			$before_loop = empty( $layout[ 'before_loop' ] ) ? '' : sprintf( "<div class='loop-header'><div class='loop-meta'>%s</div></div>", apply_filters( 'pb_the_content', $layout[ 'before_loop' ] ) );
			global $wp_query;
			$before_loop = sprintf( $before_loop, trim( wp_title( '', false ) ) );
			$return .= $before_loop;
			$return .= "<div class='loop-content'>";
			
			// Loop through each post in the recordset.
			require_once( $this->_pluginPath . '/classes/render_slotitems.php' );
			$render_slotitems = new pluginbuddy_loopbuddy_render_slotitems($this);
			foreach ( $posts as $post ) {
				setup_postdata( $post );
				// Prepare & populate all variables for this post.
				$the_loop = $this->get_the_loop( $layout[ 'items' ] );
				foreach( $layout['items'] as $index => $loop_slot ) {
					$this_replace = '';
					foreach( $loop_slot as $loop_item_id => $loop_item ) {
						if ( method_exists( $render_slotitems, $loop_item[ 'tag' ] ) ) {
							$this_replace .= call_user_func_array( array( $render_slotitems, $loop_item[ 'tag' ] ), array( $post, $loop_item ) );
						} else {
							//For custom tag rendering 
							$custom_tag_render = apply_filters( 'pb_loopbuddy_render-' . $loop_item[ 'tag' ], $post, $loop_item ); 
							if ( !is_object( $custom_tag_render ) ) $this_replace .= $custom_tag_render;
						}
					}
					// Replace slot item placeholder with the item(s) that are inside it.
					//Insert attachment if $index = content
					if ( $index == 'content' && is_attachment() && $enable_paging == 'on' ) {
						
						ob_start();
						?>
						<div id="nav-below" class="navigation loop-utility loop-utility-below">
							<div class="nav-previous"><?php previous_image_link( false ); ?></div>
							<div class="nav-next"><?php next_image_link( false ); ?></div>
						</div><!-- #nav-below -->
						<?php
						$this_replace .= ob_get_clean();
					} //end attachment
					//Strip out entry-title
					if ( $index == 'title' ) {
						$search =  "<div class='entry-title'>{title}</div>";
						$the_loop = str_replace( $search, $this_replace, $the_loop );
					}
					$the_loop = str_replace( '{' . $index . '}', $this_replace, $the_loop );
				} //end for each $layout[ 'items' ]
				$return .= $the_loop;
				//Render comments if only one post is showing
				if ( count( $posts ) === 1 && isset( $enable_comments ) && $enable_comments == 'on' ) {
					$return .= pluginbuddy_loopbuddy_render_slotitems::comments();
				}				
				
			} //end foreach $posts
			$return .= "</div><!--/.loop-content-->";
						
						
			
			//$max_num_pages, $total_posts and $post_offset
			$loop_footer = '';			
			//Pagination
			if ( isset( $enable_paging ) && $enable_paging == 'on' ) {
				$loop_footer .= "<div class='loop-utility'>";
				ob_start();
				//global $wp_query;
				//$wp_query->is_single = false; //todo - hack - get rid of this if you can
				if ( !function_exists( "wp_pagenavi" ) ) {
				?>
					<div id="nav-below" class="navigation loop-utility loop-utility-below">
						<div class="nav-previous"><?php next_posts_link( __( '<span class="meta-nav">&larr;</span> Older posts', 'it-l10n-loopbuddy' ) ); ?></div>
						<div class="nav-next"><?php previous_posts_link( __( 'Newer posts <span class="meta-nav">&rarr;</span>', 'it-l10n-loopbuddy' ) ); ?></div>
					</div><!-- #nav-below -->
				<?php
				} else {
					wp_pagenavi();
				}
				$loop_footer .= ob_get_clean();
				$loop_footer .= "</div><!/.loop-meta-below-->";
			} //end pagination
			
			// Display `after loop` content.
			$loop_footer .= empty( $layout[ 'after_loop' ] ) ? '' : sprintf( "<div class='loop-meta'>%s</div>", apply_filters( 'pb_the_content', $layout[ 'after_loop' ] ) );
			if ( !empty( $loop_footer ) ) {
				$return .= sprintf( "<div class='loop-footer'>%s</div><!--/.loop-footer-->", $loop_footer );
			}
			$return .= "</div><!--/.loop-->";
			
			if ( isset( $temp_post ) ) {
				$wp_query = $temp_query;
				$post = $temp_post;
				setup_postdata( $post );
			}
			
			return $return;
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
			echo $this->render_loop( $instance['query'], $instance['layout'] );
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
			?>
			<p><?php esc_html_e( 'Choose a query and layout', 'it-l10n-loopbuddy' ); ?></p>
			<label for="<?php echo $widget->get_field_id('query'); ?>">
				<?php _e( 'Query:', 'it-l10n-loopbuddy' ); ?>
				<select class="widefat" id="<?php echo $widget->get_field_id('query'); ?>" name="<?php echo $widget->get_field_name('query'); ?>">
					<?php
					if ( !empty( $instance['query'] ) ) { $selected_id = $instance['query']; } else { $selected_id = ''; }
					
					foreach ( (array) $this->_options['queries'] as $id => $query ) {
						echo '<option value="' . $id . '"';
						if ( $selected_id == $id ) { echo ' selected'; }
						echo '>' . $query['title'] . '</option>';
					}
					?>
				</select>
			</label>
			<label for="<?php echo $widget->get_field_id('layout'); ?>">
				<?php _e( 'Layout:', 'it-l10n-loopbuddy' ); ?>
				<select class="widefat" id="<?php echo $widget->get_field_id('layout'); ?>" name="<?php echo $widget->get_field_name('layout'); ?>">
					<?php
					if ( !empty( $instance['layout'] ) ) { $selected_id = $instance['layout']; } else { $selected_id = ''; }
					
					foreach ( (array) $this->_options['layouts'] as $id => $layout ) {
						echo '<option value="' . $id . '"';
						if ( $selected_id == $id ) { echo ' selected'; }
						echo '>' . $layout['title'] . '</option>';
					}
					?>
				</select>
			</label>
			<input type="hidden" id="<?php echo $widget->get_field_id('submit'); ?>" name="<?php echo $widget->get_field_name('submit'); ?>" value="1" />
			<?php
			
		} //end widget_form
		
		/**
		 * get_post_meta()
		 * $post_id Optional INT 
		 * Returns post meta defaults for LoopBuddy
		 * @return		array of post meta
		 *
		 */
		function get_post_meta( $post_id = 0 ) {
			global $post;
			if ( is_object( $post ) ) $post_id = $post->ID;
			$post_meta = get_post_meta( $post_id, '_lb_meta', true );
			if ( !$post_meta ) {
				$post_meta = get_post_meta( $post_id, 'lb_meta', true );
				if ( $post_meta ) {
					delete_post_meta( $post_id, 'lb_meta' );
					update_post_meta( $post_id, '_lb_meta', $post_meta );
				}
			}
			if ( !$post_meta ) {
				$post_meta = array(
					'enabled' => false,
					'query' => '',
					'layout' => ''
				);
			}
			return $post_meta;
		} //end get_post_meta
		/**
		 * get_meta_html()
		 *
		 * Displays the HTML for a post meta box
		 * @return		none
		 *
		 */
		function get_meta_html() {
			global $post;
			if ( !is_object( $post ) ) return;
			$post_meta = $this->get_post_meta( $post->ID );
			
			?>
			<div class='form-wrap'>
			<?php wp_nonce_field( plugin_basename( __FILE__ ), 'lb_save-meta' ); ?>
			<p><?php esc_html_e( 'Choose a layout and query' ); ?></p>
			<label for="lb_enable" class='form-help'>
				<input type='checkbox' name='lb_enable' id='lb_enable' <?php checked( $post_meta[ 'enabled' ], true ); ?>/>
				<?php _e( "Enable LoopBuddy", 'it-l10n-loopbuddy' ); ?>
			</label>
			<br />
			<label for="lb_query" class='form-help'>
				<?php _e( 'Query:', 'it-l10n-loopbuddy' ); ?>
				<select class="widefat" id="lb_query" name="lb_query">
					<?php
					foreach ( (array) $this->_options['queries'] as $id => $query ) {
						printf( '<option value="%s" %s>%s</option>', $id, selected( $post_meta[ 'query' ], esc_attr( $id ) ), esc_html( $query[ 'title' ] ) );
					} //end foreach
					?>
				</select>
			</label>
			<br />
			<label for="lb_layout" class='form-help'>
				<?php _e( 'Layout:', 'it-l10n-loopbuddy' ); ?>
				<select class="widefat" id="lb_layout" name="lb_layout">
					<?php					
					foreach ( (array) $this->_options['layouts'] as $id => $layout ) {
						printf( '<option value="%s" %s>%s</option>', $id, selected( $post_meta[ 'layout' ], esc_attr( $id ) ), esc_html( $layout[ 'title' ] ) );	
					} //end foreach
					?>
				</select>
			</label>
			</div><!--/.form-wrap-->
			<?php
		} //end get_meta_html
		
		
		function &get_layout( $group_id ) {
			$this->load();
			if ( empty( $this->_options['layouts'][$group_id] ) ) {
				echo 'Error #545455. INVALI LAYOUT.';
				return;
			}
			
			$group = &$this->_options['layouts'][$group_id];
			$combined_group = array_merge( $this->_layoutdefaults, (array)$group );
			if ( $combined_group !== $group ) {
				// Defaults existed that werent already in the options so we need to update their settings to include some new options.
				$group = $combined_group;
				$this->save();
			}
			
			return $group;
		}
		
		
		function &get_query( $group_id ) {
			$this->load();
			
			if ( empty( $this->_options['queries'][$group_id] ) ) {
				echo 'Error #86278. INVALID QUERY.';
				return;
			}
			
			$group = &$this->_options['queries'][$group_id];
			
			$combined_group = array_merge( $this->_querydefaults, (array)$group );
			if ( $combined_group !== $group ) {
				// Defaults existed that werent already in the options so we need to update their settings to include some new options.
				$group = $combined_group;
				$this->save();
			}
			return $group;
		}
		
		function import( $import_text, $type, $force = false ) {
			$options = &$this->_options[ $type ];
			
			if ( !empty( $options ) && !$force ) return;
			$group_titles = array();
			
			//Get the group titles to prevent duplicate names
			foreach ( $options as $id => $group ) {
				array_push( $group_titles, $group[ 'title' ] );
			}
			
			if ( is_array( $import_text ) ) {
				
				$import_count = 0;
				foreach ( $import_text as $id => &$group ) {
					$import_count+= 1;
					//Come up with a unique name if applicable
					if ( in_array( $group[ 'title' ], $group_titles ) ) {
						$group[ 'title' ] = $group[ 'title' ] . ' ' . wp_generate_password( '3', 'false' );
					}
					array_push( $options, $group );
				} //foreach
				$this->save();
				return $import_count;
				
			} else {
				//Invalid format
				return new WP_Error( __( 'Imported file is not a valid format.', 'it-l10n-loopbuddy' ) );
			}
		}
		function import_query( $filename ) {			
			$import_text = str_replace( array( ' ', "\n", "\r", "\s" ), '', file_get_contents( $filename ) );
			
			//Convert text back to arrays
			$import_text = maybe_unserialize( base64_decode( $import_text ) );
			return $this->import( $import_text, 'queries', true);
		} //end import_query
		
		function import_layout( $filename ) {	
			$import_text = str_replace( array( ' ', "\n", "\r", "\s" ), '', file_get_contents( $filename ) );
			
			//Convert text back to arrays
			$import_text = maybe_unserialize( base64_decode( $import_text ) );
			
			return $this->import( $import_text, 'layouts', true );
		} //end import_layout
		
		// For copying layouts on activation.
		function recursive_copy($src,$dst) {
			$dir = opendir($src);
			@mkdir($dst);
			while(false !== ( $file = readdir($dir)) ) {
				if (( $file != '.' ) && ( $file != '..' )) {
					if ( is_dir($src . '/' . $file) ) {
						$this->recursive_copy($src . '/' . $file,$dst . '/' . $file);
					}
					else {
						copy($src . '/' . $file,$dst . '/' . $file);
					}
				}
			}
			closedir($dir);
		} //end recursive_copy
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
	global $pluginbuddy_loopbuddy;
	$pluginbuddy_loopbuddy = new pluginbuddy_loopbuddy();
	register_activation_hook( __FILE__, array( &$pluginbuddy_loopbuddy, 'activate' ) ); // Run some code when plugin is activated in dashboard.
	require_once( dirname( __FILE__ ) . '/classes/widget.php');
	
}


?>