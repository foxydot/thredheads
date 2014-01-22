<?php

/*
Plugin Name: Billboard
Plugin URI: http://pluginbuddy.com/
Description: Easy to use Sponsor Ad and linked image management tool.
Version: 1.2.43
Author: The PluginBuddy Team
Author URI: http://pluginbuddy.com/

For sales and support visit http://pluginbuddy.com

Installation

1. Download and unzip the latest release zip file
2. Upload the entire billboard directory to your `/wp-content/plugins/` directory
3. Activate the plugin through the 'Plugins' menu in WordPress


Using

1. Activate the plugin
2. Click on "Manage"
3. Click on "Billboard"
*/


if ( ! class_exists( 'iThemesBillboard' ) ) {
	class iThemesBillboard {
		var $_version = '1.2.43';
		var $_updater = '1.0.7';
		
		var $_var = 'ithemes-billboard';
		var $_class = 'ithemes-billboard';
		var $_name = 'Billboard';
		var $_series = 'DisplayBuddy';
		var $_page = 'billboard';
		var $_tab = 'Billboard';
		var $_widgetName = 'Billboard';
		var $_widgetDescription = 'Display a Billboard group';
		
		var $_initialized = false;
		var $_options = array();
		var $_errors = array();
		var $_pageRef = '';
		
		var $_usedInputs = array();
		var $_selectedVars = array();
		var $_pluginPath = '';
		var $_pluginRelativePath = '';
		var $_pluginURL = '';
		
		
		function iThemesBillboard() {
			$this->_pluginPath = dirname( __FILE__ );
			$this->_pluginRelativePath = ltrim( str_replace( '\\', '/', str_replace( rtrim( ABSPATH, '\\\/' ), '', $this->_pluginPath ) ), '\\\/' );
			$this->_pluginURL = site_url() . '/' . $this->_pluginRelativePath;
			if ( isset( $_SERVER['HTTPS'] ) && $_SERVER['HTTPS'] == 'on' ) { $this->_pluginURL = str_replace( 'http://', 'https://', $this->_pluginURL ); }
			$this->_selfLink = array_shift( explode( '?', $_SERVER['REQUEST_URI'] ) ) . '?page=' . $this->_var;
			
			
			
			$this->_setVars();
			
			if ( is_admin() ) { // Runs when in the dashboard.
				add_action( 'init', array( &$this, 'upgrader_register' ), 50 );
				add_action( 'init', array( &$this, 'upgrader_select' ), 100 );
				add_action( 'init', array( &$this, 'upgrader_instantiate' ), 101 );
			}
			
			add_action( 'init', array( &$this, 'init' ), -10 );
			add_action( 'widgets_init', array( &$this, 'widgetsInit' ) );
		}
		
		function init() {
			$this->load();
			
			add_action( 'admin_menu', array( &$this, '_addPages' ), 15 );
			
			$this->_initialized = true;
			
			if ( is_admin() ) {
				register_activation_hook(__FILE__, array(&$this, '_activate'));
				require_once(dirname( __FILE__ ).'/lib/updater/updater.php');
			} else {
				add_shortcode('it-billboard', array( &$this, 'shortcode' ) );
			}
		}

		// REMOVE THIS EVENTUALLY - migrates from 0.1.32 to newer.
		function _activate() {
			$old_ver = get_option('ithemes_billboard');
			echo'<i>Migrated old version of PluginBuddy Billboard to new.</i>';
			if ( is_array( $old_ver) ) {
				add_option($this->_var, $old_ver, '', 'no'); // No autoload.
				update_option($this->_var, $old_ver);
				delete_option('ithemes_billboard');
			}
		}
		// END REMOVE
		
		function shortcode( $atts ) {
			extract(shortcode_atts(array(
				'group' => '0'
			), $atts));
			$this->widgetsRender( $atts );
			//return $this->fadeImages($atts['group'],false);
		}
		
		function widgetsInit() {
			global $wp_registered_sidebars;
			
			
			if ( ! is_array( $this->_options['widgets'] ) )
				$this->_options['widgets'] = array();
			
			
			$widget_ops = array( 'classname' => 'widget_' . $this->_var, 'description' => $this->_widgetDescription );
			$control_ops = array( 'width' => 280, 'height' => 350, 'id_base' => $this->_var );
			
			$registered = false;
			
			foreach ( (array) array_keys( $this->_options['widgets'] ) as $num ) {
				$id = $this->_var . '-' . $num;
				
				$registered = true;
				
				wp_register_sidebar_widget( $id, $this->_widgetName, array( &$this, 'widgetsRender' ), $widget_ops, array( 'number' => $num ) );
				wp_register_widget_control( $id, $this->_widgetName, array( &$this, 'widgetsControl' ), $control_ops, array( 'number' => $num ) );
			}
			
			if ( ! $registered ) {
				wp_register_sidebar_widget( $this->_var . '-1', $this->_widgetName, array( &$this, 'widgetsRender' ), $widget_ops, array( 'number' => -1 ) );
				wp_register_widget_control( $this->_var . '-1', $this->_widgetName, array( &$this, 'widgetsControl' ), $control_ops, array( 'number' => -1 ) );
			}
		}

		function _addPages() {
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
			//wp_enqueue_script( 'jquery' );
			wp_enqueue_script( 'pluginbuddy-tooltip-js', $this->_pluginURL . '/js/tooltip.js' );
			wp_print_scripts( 'pluginbuddy-tooltip-js' );
			wp_enqueue_script( 'pluginbuddy-swiftpopup-js', $this->_pluginURL . '/js/swiftpopup.js' );
			wp_print_scripts( 'pluginbuddy-swiftpopup-js' );
			wp_enqueue_script( 'pluginbuddy-'.$this->_var.'-admin-js', $this->_pluginURL . '/js/admin.js' );
			wp_print_scripts( 'pluginbuddy-'.$this->_var.'-admin-js' );
			echo '<link rel="stylesheet" href="'.$this->_pluginURL . '/css/admin.css" type="text/css" media="all" />';
			
			$this->_addScripts();
			$this->_addStyles();
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
		

		/*
		function _addPages() {
			global $wp_theme_page_name;
			
			if ( ! preg_match( '/plugins/', dirname( __FILE__ ) ) && ( ! empty( $wp_theme_page_name ) ) )
				$this->_pageRef = add_submenu_page( $wp_theme_page_name, $this->_tab, $this->_tab, 'edit_themes', $this->_page, array( &$this, 'index' ) );
			else
				$this->_pageRef = add_management_page( $this->_tab, $this->_tab, 'edit_themes', $this->_page, array( &$this, 'index' ) );
			
			add_action( 'admin_print_scripts-' . $this->_pageRef, array( $this, '_addScripts' ) );
			add_action( 'admin_print_styles-' . $this->_pageRef, array( $this, '_addStyles' ) );
		}
		*/
		
		function _addScripts() {
			global $wp_scripts;
			
			
			$queue = array();
			
			foreach ( (array) $wp_scripts->queue as $item )
				if ( ! in_array( $item, array( 'page', 'editor', 'editor_functions', 'tiny_mce', 'media-upload', 'post' ) ) )
					$queue[] = $item;
			
			$wp_scripts->queue = $queue;
			
			wp_enqueue_script( 'jquery' );
			wp_enqueue_script( 'thickbox' );
			
			wp_enqueue_script( $this->_var . '-theme-options', $this->_pluginURL . '/js/script.js' );
			wp_print_scripts( $this->_var . '-theme-options' );
		}
		
		function _addStyles() {
			wp_enqueue_style( 'thickbox' );
			
			wp_enqueue_style( $this->_var . '-theme-options', $this->_pluginURL . '/css/style.css' );
			wp_print_styles( $this->_var . '-theme-options' );
		}
		
		function _setVars() {
			$this->_pluginPath = dirname( __FILE__ );
			$this->_pluginRelativePath = ltrim( str_replace( '\\', '/', str_replace( rtrim( ABSPATH, '\\\/' ), '', $this->_pluginPath ) ), '\\\/' );
			$this->_pluginURL = get_option( 'siteurl' ) . '/' . $this->_pluginRelativePath;
			if ( isset( $_SERVER['HTTPS'] ) && $_SERVER['HTTPS'] == 'on' ) {
				$this->_pluginURL = str_replace( 'http://', 'https://', $this->_pluginURL );
			}
			$page = ( isset( $_REQUEST['page'] ) ) ? $_REQUEST['page'] : '';
			$this->_selfLink = array_shift( explode( '?', $_SERVER['REQUEST_URI'] ) ) . '?page=' . $page;
		}
		
		
		// Options Storage ////////////////////////////
		
		function _initializeOptions() {
			$this->_options = array();
			
			$this->_options['groups'] = array();
			$this->_options['widgets'] = array();
			
			$this->save();
		}
		
		function save() {
			$data['groups'] = $this->_options['groups'];
			$data['widgets'] = $this->_options['widgets'];
			if ( isset( $this->_options['updater'] ) ) {
				$data['updater'] = $this->_options['updater'];
			}
			
			
			if ( $data == @get_option( $this->_var ) )
				return true;
			
			return @update_option( $this->_var, $data );
		}
		
		function load() {
			$data = @get_option( $this->_var );
			
			if ( is_array( $data ) )
				$this->_options = $data;
			else
				$this->_initializeOptions();
		}
		
		
		// Pages //////////////////////////////////////
		
		function index() {
			if ( ! current_user_can( 'administrator' ) )
				die( __( 'Cheatin&#8217; uh?' ) );
			
			
			if ( isset( $_REQUEST['group_id'] ) && empty( $_REQUEST['cancelsave_group'] ) ) {
				$this->_groupID = (int) $_REQUEST['group_id'];
				$this->_group = (array) $this->_options['groups'][$this->_groupID];
				
				if ( ! empty( $_REQUEST['view_entries'] ) ) {
					if ( isset( $_REQUEST['entry_id'] ) && empty( $_POST['cancelsave_entry'] ) ) {
						$this->_entryID = (int) $_REQUEST['entry_id'];
						$this->_entry = (array) $this->_options['groups'][$this->_groupID]['entries'][$this->_entryID];
						
						if ( ! empty( $_POST['save_entry'] ) ) {
							$this->_entrySave();
							
							if ( $this->_errors )
								$this->_entryEdit();
							else
								$this->_entriesRender();
						}
						else
							$this->_entryEdit();
					}
					else {
						if ( ! empty( $_POST['add_entry'] ) )
							$this->_entriesCreate();
						elseif ( ! empty( $_POST['save_entry_order'] ) )
							$this->_entriesSaveOrder();
						elseif ( ! empty( $_POST['delete_entry'] ) )
							$this->_entriesDelete();
						
						$this->_entriesRender();
					}
				}
				else {
					if ( ! empty( $_POST['save_group'] ) ) {
						$this->_groupSave();
						
						if ( $this->_errors )
							$this->_groupEdit();
						else
							$this->_groupsRender();
					}
					else
						$this->_groupEdit();
				}
			}
			else {
				if ( ! empty( $_POST['add_group'] ) )
					$this->_groupsCreate();
				elseif ( ! empty( $_POST['delete_group'] ) )
					$this->_groupsDelete();
				
				$this->_groupsRender();
			}
		}
		
		function _entrySave() {
			check_admin_referer( $this->_var . '-nonce' );
			
			
			$description = (string) $_POST[$this->_var . '-description'];
			$url = (string) $_POST[$this->_var . '-url'];
			$image = (string) $_POST[$this->_var . '-image_id'];
			$priority = (string) $_POST[$this->_var . '-priority'];
			$require_link = (string) $_POST[$this->_var . '-require_link'];
			
			if ( empty( $description ) ) {
				$this->_errors[] = 'description';
				$this->_showErrorMessage( 'A Description is required' );
			}
			elseif ( is_array( $this->_options['groups'][$this->_groupID]['entries'] ) ) {
				foreach ( (array) $this->_options['groups'][$this->_groupID]['entries'] as $id => $entry ) {
					if ( ( $entry['description'] == $description ) && ( $id != $this->_entryID ) ) {
						$this->_errors[] = 'description';
						$this->_showErrorMessage( 'An entry with that Description already exists' );
						
						break;
					}
				}
			}
			
			if ( empty( $url ) && ! empty( $require_link ) ) {
				$this->_errors[] = 'url';
				$this->_showErrorMessage( 'A Link URL is required if "Require Link URL" is "Yes"' );
			}
			elseif ( ! empty( $url ) && ! preg_match( '[^(https?|ftp)://]i', $url ) ) {
				$this->_errors[] = 'url';
				$this->_showErrorMessage( 'The URL value must be a valid link in the form of either "http://domain.com/", "https://domain.com/", or "ftp://domain.com/"' );
			}
			
			
			if ( is_array( $_FILES['image_upload'] ) && ( 0 === $_FILES['image_upload']['error'] ) ) {
				require_once( $this->_pluginPath . '/lib/file-utility/file-utility.php' );
				
				$file = iThemesFileUtility::uploadFile( 'image_upload' );
				
				if ( is_wp_error( $file ) ) {
					$this->_errors[] = 'image_upload';
					$this->_showErrorMessage( 'Unable to save uploaded image. Ensure that the web server has permissions to write to the uploads folder' );
				}
				else {
					if ( iThemesFileUtility::is_animated_gif( $file['file'] ) )
						$this->_addedAnimatedFile = true;
					
					$image = $file['id'];
				}
			}
			elseif ( empty( $image ) )
				$image = $this->_entry['image'];
			
			
			if ( $this->_errors ) {
				$this->_showErrorMessage( 'Please correct the ' . __ngettext( 'error', 'errors', count( $this->_errors ) ) . ' in order to modify this entry\'s settings' );
				
				$this->_options['image_id'] = $image;
			}
			else {
				$entry = array();
				
				$this->_options['groups'][$this->_groupID]['entries'][$this->_entryID]['description'] = $description;
				$this->_options['groups'][$this->_groupID]['entries'][$this->_entryID]['url'] = $url;
				$this->_options['groups'][$this->_groupID]['entries'][$this->_entryID]['image'] = $image;
				$this->_options['groups'][$this->_groupID]['entries'][$this->_entryID]['priority'] = $priority;
				$this->_options['groups'][$this->_groupID]['entries'][$this->_entryID]['require_link'] = $require_link;
				
				$this->save();
				$this->_group = (array) $this->_options['groups'][$this->_groupID];
				$this->_entry + (array) $this->_options['groups'][$this->_groupID]['entries'][$this->_entryID];
				
				$this->_showStatusMessage( "Entry \"$description\" settings updated" );
			}
		}
		
		function _entryEdit() {
			
?>
	<div class="wrap">
		<h2><a href="<?php echo $this->_selfLink; ?>">Billboard Groups</a> &raquo; <a href="<?php echo $this->_selfLink . '&group_id=' . $this->_groupID . '&view_entries=1'; ?>">Entries for <?php echo $this->_group['name']; ?></a> &raquo; Settings for <?php echo $this->_entry['description']; ?></h2>
		
		<?php
			if ( $this->_errors ) {
				$this->_options['description'] = $_POST[$this->_var . '-description'];
				$this->_options['url'] = $_POST[$this->_var . '-url'];
				$this->_options['priority'] = $_POST[$this->_var . '-priority'];
				$this->_options['require_link'] = $_POST[$this->_var . '-require_link'];
			}
			else {
				$this->_options['description'] = $this->_entry['description'];
				$this->_options['url'] = $this->_entry['url'];
				$this->_options['priority'] = $this->_entry['priority'];
				
				if ( ! isset( $this->_entry['require_link'] ) )
					$this->_options['require_link'] = '1';
				else
					$this->_options['require_link'] = $this->_entry['require_link'];
			}
			
			
			$image = '';
			if ( ! empty( $this->_options['image_id'] ) ) {
				require_once( $this->_pluginPath . '/lib/file-utility/file-utility.php' );
				
				$image = iThemesFileUtility::resize_image( $this->_options['image_id'], 100, 100, false );
			}
			elseif ( ! empty( $this->_entry['image'] ) ) {
				require_once( $this->_pluginPath . '/lib/file-utility/file-utility.php' );
				
				$image = iThemesFileUtility::resize_image( $this->_entry['image'], 100, 100, false );
			}
		?>
		
		<form name="addnew" id="addnew" enctype="multipart/form-data" method="post" action="<?php echo $this->_selfLink; ?>">
			<?php wp_nonce_field( $this->_var . '-nonce' ); ?>
			<table class="form-table">
				<tr><th scope="row">Description</th>
					<td><?php $this->_addTextBox( 'description', array( 'size' => '20' ) ); ?></td>
				</tr>
				<tr><th scope="row">Link URL</th>
					<td><?php $this->_addTextBox( 'url', array( 'size' => '60' ) ); ?></td>
				</tr>
				<tr><th scope="row">Image</th>
					<td>
						<?php if ( ! is_wp_error( $image ) ) : ?>
							<img src="<?php echo $image['url']; ?>" /><br />
							
							<?php $this->_addHidden( 'image_id' ); ?>
						<?php endif; ?>
						
						<?php $this->_addFileUpload( 'image_upload' ); ?>
					</td>
				</tr>
				<tr><th scope="row">Priority</th>
					<td>
						<?php $this->_addDropDown( 'priority', array( 'normal' => 'Normal (default)', 'top' => 'Top (show before default priority entries)' ) ); ?>
					</td>
				</tr>
				<tr><th scope="row">Require Link URL</th>
					<td>
						<?php $this->_addDropDown( 'require_link', array( '' => 'No', '1' => 'Yes (default)' ) ); ?><br />
						<i>Selecting "No" will allow you to add images without supplying a link.</i>
					</td>
				</tr>
			</table>
			
			<p class="submit">
				<?php $this->_addSubmit( 'save_entry', 'Save Settings' ); ?>
				<?php $this->_addSubmit( 'cancelsave_entry', array( 'value' => 'Cancel', 'class' => 'button-secondary' ) ); ?>
			</p>
			
			<?php $this->_addHiddenNoSave( 'group_id', $this->_groupID ); ?>
			<?php $this->_addHiddenNoSave( 'entry_id', $this->_entryID ); ?>
			<?php $this->_addHiddenNoSave( 'view_entries', 1 ); ?>
		</form>
	</div>
<?php
			
		}
		
		function _entriesSaveOrder() {
			check_admin_referer( $this->_var . '-nonce' );
			
			
			foreach ( (array) $_POST as $var => $value ) {
				if ( preg_match( '/^' . $this->_var . '-entry-order-(\d+)$/', $var, $matches ) ) {
					$entry_id = $matches[1];
					
					if ( ! empty( $this->_group['entries'][$entry_id] ) && is_array( $this->_group['entries'][$entry_id] ) )
						$this->_options['groups'][$this->_groupID]['entries'][$entry_id]['order'] = $value;
				}
			}
			
			$this->_group = $this->_options['groups'][$this->_groupID];
			
			$this->save();
			
			
			$this->_showStatusMessage( 'Successfully updated the Entry Order' );
		}
		
		function _entriesDelete() {
			check_admin_referer( $this->_var . '-nonce' );
			
			require_once( $this->_pluginPath . '/lib/file-utility/file-utility.php' );
			
			
			$names = array();
			
			if ( ! empty( $_POST['entries'] ) && is_array( $_POST['entries'] ) ) {
				foreach ( (array) $_POST['entries'] as $id ) {
					$names[] = $this->_options['groups'][$this->_groupID]['entries'][$id]['description'];
					
					iThemesFileUtility::delete_file_attachment( $this->_options['groups'][$this->_groupID]['entries'][$id]['image'] );
					
					unset( $this->_options['groups'][$this->_groupID]['entries'][$id] );
				}
			}
			
			natcasesort( $names );
			
			if ( $names ) {
				$this->save();
				$this->_group = (array) $this->_options['groups'][$this->_groupID];
				
				$this->_showStatusMessage( 'Successfully deleted the following ' . __ngettext( 'entry', 'entries', count( $names ) ) . ': ' . implode( ', ', $names ) );
			}
			else
				$this->_showErrorMessage( 'No entries were selected for deletion' );
		}
		
		function _entriesCreate() {
			check_admin_referer( $this->_var . '-nonce' );
			
			
			if ( ! isset( $_POST[$this->_var . '-image_id'] ) )
				$_POST[$this->_var . '-image_id'] = '';
			
			$description = (string) $_POST[$this->_var . '-description'];
			$url = (string) $_POST[$this->_var . '-url'];
			$image = (string) $_POST[$this->_var . '-image_id'];
			$priority = (string) $_POST[$this->_var . '-priority'];
			$require_link = (string) $_POST[$this->_var . '-require_link'];
			
			if ( empty( $description ) ) {
				$this->_errors[] = 'description';
				$this->_showErrorMessage( 'A Description is required to create a new entry' );
			}
			elseif ( is_array( $this->_options['groups'][$this->_groupID]['entries'] ) ) {
				foreach ( (array) $this->_options['groups'][$this->_groupID]['entries'] as $id => $entry ) {
					if ( $entry['description'] == $description ) {
						$this->_errors[] = 'description';
						$this->_showErrorMessage( 'An entry with that Description already exists' );
						
						break;
					}
				}
			}
			
			if ( empty( $url ) && ! empty( $require_link ) ) {
				$this->_errors[] = 'url';
				$this->_showErrorMessage( 'A Link URL is required to create a new entry if "Require Link URL" is "Yes"' );
			}
			elseif ( ! empty( $url ) && ! preg_match( '[^(https?|ftp)://]i', $url ) ) {
				$this->_errors[] = 'url';
				$this->_showErrorMessage( 'The URL value must be a valid link in the form of either "http://domain.com/", "https://domain.com/", or "ftp://domain.com/"' );
			}
			
			
			if ( is_array( $_FILES['image_upload'] ) && ( 0 === $_FILES['image_upload']['error'] ) ) {
				require_once( $this->_pluginPath . '/lib/file-utility/file-utility.php' );
				
				$file = iThemesFileUtility::uploadFile( 'image_upload' );
				
				if ( is_wp_error( $file ) ) {
					$this->_errors[] = 'image_upload';
					$this->_showErrorMessage( 'Unable to save uploaded image. Ensure that the web server has permissions to write to the uploads folder' );
				}
				else {
					if ( iThemesFileUtility::is_animated_gif( $file['file'] ) )
						$this->_addedAnimatedFile = true;
					
					$image = $file['id'];
				}
			}
			elseif ( empty( $image ) ) {
				$this->_errors[] = 'image_upload';
				$this->_showErrorMessage( 'An Image is required to create an entry.' );
			}
			
			
			if ( $this->_errors ) {
				$this->_showErrorMessage( 'Please correct the ' . __ngettext( 'error', 'errors', count( $this->_errors ) ) . ' in order to add the new entry' );
				
				$this->_options['image_id'] = $image;
			}
			else {
				$entry = array();
				
				$entry['description'] = $description;
				$entry['url'] = $url;
				$entry['image'] = $image;
				$entry['priority'] = $priority;
				$entry['require_link'] = $require_link;
				
				$entry['order'] = 0;
				if ( is_array( $this->_options['groups'][$this->_groupID]['entries'] ) )
					foreach ( (array) $this->_options['groups'][$this->_groupID]['entries'] as $id => $ent )
						if ( $ent['order'] > $entry['order'] )
							$entry['order'] = $ent['order'];
				$entry['order']++;
				
				if ( is_array( $this->_options['groups'][$this->_groupID]['entries'] ) && ! empty( $this->_options['groups'][$this->_groupID]['entries'] ) )
					$newID = max( array_keys( $this->_options['groups'][$this->_groupID]['entries'] ) ) + 1;
				else
					$newID = 0;
				
				$this->_options['groups'][$this->_groupID]['entries'][$newID] = $entry;
				
				
				$this->save();
				$this->_group = (array) $this->_options['groups'][$this->_groupID];
				
				$this->_showStatusMessage( "Entry \"$description\" added" );
			}
		}
		
		function _entriesRender() {
			$this->admin_scripts();
			
			if ( isset( $this->_addedAnimatedFile ) && ( true === $this->_addedAnimatedFile ) )
				$this->_showStatusMessage( "An animated image was just uploaded. It may take a moment for this screen to fully render as the animation is resized." );
			
			
?>
	<?php if ( ! $this->_errors ) : ?>
		<div class="wrap">
			<form id="posts-filter" enctype="multipart/form-data" method="post" action="<?php echo $this->_selfLink; ?>">
				<?php wp_nonce_field( $this->_var . '-nonce' ); ?>
				
				<h2><a href="<?php echo $this->_selfLink; ?>">Billboard Groups</a> &raquo; Entries for <?php echo $this->_group['name']; ?> (<a href="#addnew">add&nbsp;new</a>)</h2>
				
				<?php if ( count( $this->_group['entries'] ) > 0 ) : ?>
					<div class="tablenav">
						<div class="alignleft actions">
							<?php $this->_addSubmit( 'delete_entry', array( 'value' => 'Delete', 'class' => 'button-secondary delete' ) ); ?>
							<?php $this->_addSubmit( 'save_entry_order', array( 'value' => 'Save Order', 'class' => 'button-secondary' ) ); ?>
						</div>
						
						<br class="clear" />
					</div>
					
					<br class="clear" />
					
					<table class="widefat">
						<thead>
							<tr class="thead">
								<th scope="col" class="check-column"><input type="checkbox" id="check-all-entries" /></th>
								<th>Entry Description</th>
								<th>Link</th>
								<th>Image</th>
								<th>Priority</th>
								<th class="num">Reorder</th>
							</tr>
						</thead>
						<tfoot>
							<tr class="thead">
								<th scope="col" class="check-column"><input type="checkbox" id="check-all-entries" /></th>
								<th>Entry Description</th>
								<th>Link</th>
								<th>Image</th>
								<th>Priority</th>
								<th class="num">Reorder</th>
							</tr>
						</tfoot>
						<tbody id="users" class="list:user user-list">
							<?php
								$class = ' class="alternate"';
								$order = 1;
								
								uksort( $this->_group['entries'], array( &$this, '_orderedSort' ) );
							?>
							<?php foreach ( (array) $this->_group['entries'] as $id => $entry ) : ?>
								<?php
									flush();
									
									$image = $this->_get_resized_image( $this->_groupID, $entry['image'] );
									
									$this->_options['entry-order-' . $id] = $entry['order'];
								?>
								<tr class="entry-row" id="entry-<?php echo $id; ?>"<?php echo $class; ?>>
									<th scope="row" class="check-column">
										<input type="checkbox" name="entries[]" class="administrator entries" value="<?php echo $id; ?>" />
									</th>
									<td>
										<strong><a href="<?php echo $this->_selfLink; ?>&group_id=<?php echo $this->_groupID; ?>&view_entries=1&entry_id=<?php echo $id; ?>" title="Modify Entry Settings"><?php echo $entry['description']; ?></a></strong>
									</td>
									<td>
										<?php if ( ! empty( $entry['url'] ) ) : ?>
											<a href="<?php echo $entry['url']; ?>" target="group-<?php echo $this->_groupID; ?>-entry-<?php echo $id; ?>" title="<?php echo $entry['description']; ?>"><?php echo $entry['url']; ?></a>
										<?php else : ?>
											<!-- no link -->
										<?php endif; ?>
									</td>
									<td>
										<?php if ( ! is_wp_error( $image ) ) : ?>
											<img src="<?php echo $image['url']; ?>" />
										<?php else : ?>
											Thumbnail generation error: <?php echo $image->get_error_message(); ?>
										<?php endif; ?>
									</td>
									<td>
										<div class="entry-priority"><?php echo ucfirst( $entry['priority'] ); ?></div>
									</td>
									<td class="num">
										<div style="margin-bottom:5px;" class="entry-up"><img src="<?php echo $this->_pluginURL; ?>/images/blue-up.png" alt="move up" /></div>
										<div class="entry-down"><img src="<?php echo $this->_pluginURL; ?>/images/blue-down.png" alt="move down" /></div>
										<?php $this->_addHidden( 'entry-order-' . $id, array( 'class' => 'entry-order' ) ); ?>
									</td>
								</tr>
								<?php $class = ( $class == '' ) ? ' class="alternate"' : ''; ?>
								<?php $order++; ?>
							<?php endforeach; ?>
						</tbody>
					</table>
					
					<div class="tablenav">
						<div class="alignleft actions">
							<?php $this->_addSubmit( 'delete_entry', array( 'value' => 'Delete', 'class' => 'button-secondary delete' ) ); ?>
							<?php $this->_addSubmit( 'save_entry_order', array( 'value' => 'Save Order', 'class' => 'button-secondary' ) ); ?>
						</div>
						
						<br class="clear" />
					</div>
					
					<?php $this->_addHiddenNoSave( 'group_id', $this->_groupID ); ?>
					<?php $this->_addHiddenNoSave( 'view_entries', $_REQUEST['view_entries'] ); ?>
				<?php endif; ?>
			</form>
		</div>
		
		<br class="clear" />
	<?php endif; ?>
	
	<div class="wrap">
		<h2>Add New Entry</h2>
		
		<?php
			if ( $this->_errors ) {
				$this->_options['description'] = $_POST[$this->_var . '-description'];
				$this->_options['url'] = $_POST[$this->_var . '-url'];
				$this->_options['priority'] = $_POST[$this->_var . '-priority'];
				$this->_options['require_link'] = $_POST[$this->_var . '-require_link'];
			}
			else {
				$this->_options['priority'] = 'normal';
				$this->_options['require_link'] = '1';
			}
			
			$image = '';
			if ( ! empty( $this->_options['image_id'] ) ) {
				require_once( $this->_pluginPath . '/lib/file-utility/file-utility.php' );
				
				$image = iThemesFileUtility::resize_image( $this->_options['image_id'], 100, 100, false );
			}
		?>
		
		<form name="addnew" id="addnew" enctype="multipart/form-data" method="post" action="<?php echo $this->_selfLink; ?>">
			<?php wp_nonce_field( $this->_var . '-nonce' ); ?>
			<table class="form-table">
				<tr><th scope="row">Description</th>
					<td><?php $this->_addTextBox( 'description', array( 'size' => '20' ) ); ?></td>
				</tr>
				<tr><th scope="row">Link URL</th>
					<td><?php $this->_addTextBox( 'url', array( 'size' => '60' ) ); ?></td>
				</tr>
				<tr><th scope="row">Image</th>
					<td>
						<?php if ( ! empty( $image ) && ! is_wp_error( $image ) ) : ?>
							<img src="<?php echo $image['url']; ?>" /><br />
							
							<?php $this->_addHidden( 'image_id' ); ?>
						<?php endif; ?>
						
						<?php $this->_addFileUpload( 'image_upload' ); ?>
					</td>
				</tr>
				<tr><th scope="row">Priority</th>
					<td>
						<?php $this->_addDropDown( 'priority', array( 'normal' => 'Normal (default)', 'top' => 'Top (show before default priority entries)' ) ); ?>
					</td>
				</tr>
				<tr><th scope="row">Require Link URL</th>
					<td>
						<?php $this->_addDropDown( 'require_link', array( '' => 'No', '1' => 'Yes (default)' ) ); ?><br />
						<i>Selecting "No" will allow you to add images without supplying a link.</i>
					</td>
				</tr>
			</table>
			
			<p class="submit">
				<?php $this->_addSubmit( 'add_entry', 'Add Entry' ); ?>
			</p>
			
			<?php $this->_addHiddenNoSave( 'group_id', $this->_groupID ); ?>
			<?php $this->_addHiddenNoSave( 'view_entries', 1 ); ?>
		</form>
	</div>
<?php
			
		}
		
		function _groupSave() {
			$name = (string) $_POST[$this->_var . '-name'];
			$resize = (string) $_POST[$this->_var . '-resize'];
			$width = (int) $_POST[$this->_var . '-width'];
			$height = (int) $_POST[$this->_var . '-height'];
			
			if ( empty( $name ) ) {
				$this->_errors[] = 'name';
				$this->_showErrorMessage( 'A Name is required' );
			}
			elseif ( is_array( $this->_options['groups'] ) ) {
				foreach ( (array) $this->_options['groups'] as $id => $group ) {
					if ( ( $group['name'] == $name ) && ( $id != $this->_groupID ) ) {
						$this->_errors[] = 'name';
						$this->_showErrorMessage( 'A Billboard Group with that Name already exists' );
						
						break;
					}
				}
			}
			
			if ( empty( $_POST[$this->_var . '-width'] ) ) {
				$this->_errors[] = 'width';
				$this->_showErrorMessage( 'You must supply a Width value' );
			}
			elseif ( ( $width != $_POST[$this->_var . '-width'] ) || ( $width < 1 ) ) {
				$this->_errors[] = 'width';
				$this->_showErrorMessage( 'The Width must be an integer value greater than 0' );
			}
			
			if ( empty( $_POST[$this->_var . '-height'] ) ) {
				$this->_errors[] = 'height';
				$this->_showErrorMessage( 'You must supply a Height value' );
			}
			elseif ( ( $height != $_POST[$this->_var . '-height'] ) || ( $height < 1 ) ) {
				$this->_errors[] = 'height';
				$this->_showErrorMessage( 'The Height must be an integer value greater than 0' );
			}
			
			if ( $this->_errors )
				$this->_showErrorMessage( 'Please correct the ' . __ngettext( 'error', 'errors', count( $this->_errors ) ) . ' in order to modify this Billboard Group\'s settings' );
			else {
				$this->_options['groups'][$this->_groupID]['name'] = $name;
				$this->_options['groups'][$this->_groupID]['resize'] = $resize;
				$this->_options['groups'][$this->_groupID]['width'] = $width;
				$this->_options['groups'][$this->_groupID]['height'] = $height;
				
				
				$this->save();
				
				$this->_showStatusMessage( "Billboard Group \"$name\" settings updated" );
			}
		}
		
		function _groupEdit() {
			$resizeOptions = array( 'none' => 'Do not resize images', 'width' => 'Limit width', 'height' => 'Limit height', 'bothcrop' => 'Limit width and height with crop (default)', 'bothnocrop' => 'Limit width and height without crop' );
			
?>
	<div class="wrap">
		<h2><a href="<?php echo $this->_selfLink; ?>">Billboard Groups</a> &raquo; Settings for <?php echo $this->_group['name']; ?></h2>
		
		<?php
			if ( $this->_errors ) {
				$this->_options['name'] = $_POST[$this->_var . '-name'];
				$this->_options['resize'] = $_POST[$this->_var . '-resize'];
				$this->_options['width'] = $_POST[$this->_var . '-width'];
				$this->_options['height'] = $_POST[$this->_var . '-height'];
			}
			else {
				$this->_options['name'] = $this->_group['name'];
				$this->_options['resize'] = $this->_group['resize'];
				$this->_options['width'] = $this->_group['width'];
				$this->_options['height'] = $this->_group['height'];
			}
		?>
		
		<form enctype="multipart/form-data" method="post" action="<?php echo $this->_selfLink; ?>">
			<?php wp_nonce_field( $this->_var . '-nonce' ); ?>
			<table class="form-table">
				<tr><th scope="row">Billboard Group Name</th>
					<td><?php $this->_addTextBox( 'name' ); ?></td>
				</tr>
				<tr><th scope="row">Resizing</th>
					<td>
						<table>
							<tr><th scope="row">Resize Method</th>
								<td style="border-bottom:0px;"><?php $this->_addDropDown( 'resize', $resizeOptions ); ?></td>
							</tr>
							<tr id="width-container">
								<th scope="row">Width in Pixels</th>
								<td><?php $this->_addTextBox( 'width', array( 'size' => '5', 'maxlength' => '5' ) ); ?></td>
							</tr>
							<tr id="height-container">
								<th scope="row">Height in Pixels</th>
								<td><?php $this->_addTextBox( 'height', array( 'size' => '5', 'maxlength' => '5' ) ); ?></td>
							</tr>
						</table>
					</td>
				</tr>
			</table>
			
			<p class="submit">
				<?php $this->_addSubmit( 'save_group', 'Save Settings' ); ?>
				<?php $this->_addSubmit( 'cancelsave_group', 'Cancel' ); ?>
			</p>
			
			<?php $this->_addHiddenNoSave( 'group_id', $this->_groupID ); ?>
		</form>
	</div>
<?php
			
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
				$this->_showStatusMessage( 'Successfully deleted the following Billboard ' . __ngettext( 'Group', 'Groups', count( $names ) ) . ': ' . implode( ', ', $names ) );
			else
				$this->_showErrorMessage( 'No Billboard Groups were selected for deletion' );
		}
		
		function _groupsCreate() {
			$name = (string) $_POST[$this->_var . '-name'];
			$resize = (string) $_POST[$this->_var . '-resize'];
			$width = (int) $_POST[$this->_var . '-width'];
			$height = (int) $_POST[$this->_var . '-height'];
			
			if ( empty( $name ) ) {
				$this->_errors[] = 'name';
				$this->_showErrorMessage( 'A Name is required to create a new Billboard Group' );
			}
			elseif ( is_array( $this->_options['groups'] ) ) {
				foreach ( (array) $this->_options['groups'] as $id => $group ) {
					if ( $group['name'] == $name ) {
						$this->_errors[] = 'name';
						$this->_showErrorMessage( 'A Billboard Group with that Name already exists' );
						
						break;
					}
				}
			}
			
			if ( empty( $_POST[$this->_var . '-width'] ) ) {
				$this->_errors[] = 'width';
				$this->_showErrorMessage( 'You must supply a Width value' );
			}
			elseif ( ( $width != $_POST[$this->_var . '-width'] ) || ( $width < 1 ) ) {
				$this->_errors[] = 'width';
				$this->_showErrorMessage( 'The Width must be an integer value greater than 0' );
			}
			
			if ( empty( $_POST[$this->_var . '-height'] ) ) {
				$this->_errors[] = 'height';
				$this->_showErrorMessage( 'You must supply a Height value' );
			}
			elseif ( ( $height != $_POST[$this->_var . '-height'] ) || ( $height < 1 ) ) {
				$this->_errors[] = 'height';
				$this->_showErrorMessage( 'The Height must be an integer value greater than 0' );
			}
			
			if ( $this->_errors )
				$this->_showErrorMessage( 'Please correct the ' . __ngettext( 'error', 'errors', count( $this->_errors ) ) . ' in order to add the new Billboard Group' );
			else {
				$group = array();
				
				$group['name'] = $name;
				$group['resize'] = $resize;
				$group['width'] = $width;
				$group['height'] = $height;
				$group['entries'] = array();
				
				if ( is_array( $this->_options['groups'] ) && ! empty( $this->_options['groups'] ) )
					$newID = max( array_keys( $this->_options['groups'] ) ) + 1;
				else
					$newID = 0;
				
				$this->_options['groups'][$newID] = $group;
				
				
				$this->save();
				
				$this->_showStatusMessage( "Billboard Group \"$name\" added" );
			}
		}
		
		function _groupsRender() {
			$resizeOptions = array( 'none' => 'Do not resize images', 'width' => 'Limit width', 'height' => 'Limit height', 'bothcrop' => 'Limit width and height with crop (default)', 'bothnocrop' => 'Limit width and height without crop' );
			$resizeOptionsDisplay = array( 'none' => 'Do not resize images', 'width' => 'Limit width', 'height' => 'Limit height', 'bothcrop' => 'Limit width and height with crop', 'bothnocrop' => 'Limit width and height without crop' );
			
			uksort( $this->_options['groups'], array( &$this, '_sortGroupsByName' ) );
			
?>
	<div class="wrap">
		<form id="posts-filter" enctype="multipart/form-data" method="post" action="<?php echo $this->_selfLink; ?>">
			<h2>Billboard Groups (<a href="#addnew">add&nbsp;new</a>)</h2>
			
			<?php if ( count( $this->_options['groups'] ) > 0 ) : ?>
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
							<th>Entries</th>
							<th>Resize Method</th>
							<th class="num">Width</th>
							<th class="num">Height</th>
							<th>Group-Specific CSS Class</th>
						</tr>
					</thead>
					<tfoot>
						<tr class="thead">
							<th scope="col" class="check-column"><input type="checkbox" id="check-all-groups" /></th>
							<th>Group Name</th>
							<th>Entries</th>
							<th>Resize Method</th>
							<th class="num">Width</th>
							<th class="num">Height</th>
							<th>Group-Specific CSS Class</th>
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
								<td><strong><a href="<?php echo $this->_selfLink; ?>&group_id=<?php echo $id; ?>" title="Modify Billboard Group Settings"><?php echo $group['name']; ?></a></strong></td>
								<td>
									<?php if ( ! empty( $group['entries'] ) && is_array( $group['entries'] ) ) : ?>
										<?php echo count( $group['entries'] ); ?>
									<?php else : ?>
										0
									<?php endif; ?>
									(<a href="<?php echo $this->_selfLink; ?>&group_id=<?php echo $id; ?>&view_entries=1" title="Add, Modify, and Delete Entries"><?php echo $entriesDescription; ?></a>)
								</td>
								<td><?php echo $resizeOptionsDisplay[$group['resize']]; ?></td>
								<td class="num"><?php echo $group['width']; ?></td>
								<td class="num"><?php echo $group['height']; ?></td>
								<td><?php echo $css_class; ?></td>
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
	</div>
	
	<br class="clear" />
	
	<div class="wrap">
		<h2>Add New Billboard Group</h2>
		
		<?php
			if ( $this->_errors ) {
				$this->_options['resize'] = $_POST[$this->_var . '-resize'];
				$this->_options['width'] = $_POST[$this->_var . '-width'];
				$this->_options['height'] = $_POST[$this->_var . '-height'];
			}
			else {
				$this->_options['resize'] = 'bothcrop';
				$this->_options['width'] = '125';
				$this->_options['height'] = '125';
			}
		?>
		
		<form name="addnew" id="addnew" enctype="multipart/form-data" method="post" action="<?php echo $this->_selfLink; ?>">
			<?php wp_nonce_field( $this->_var . '-nonce' ); ?>
			<table class="form-table">
				<tr><th scope="row"><label for="name">Billboard Group Name</label></th>
					<td><?php $this->_addTextBox( 'name' ); ?></td>
				</tr>
				<tr><th scope="row"><label for="resize">Resizing</label></th>
					<td>
						<table>
							<tr><th scope="row"><label for="resize">Resize Method</label></th>
								<td style="border-bottom:0px;"><?php $this->_addDropDown( 'resize', $resizeOptions ); ?></td>
							</tr>
							<tr id="width-container">
								<th scope="row"><label for="width">Width in Pixels</label></th>
								<td><?php $this->_addTextBox( 'width', array( 'size' => '5', 'maxlength' => '5' ) ); ?></td>
							</tr>
							<tr id="height-container">
								<th scope="row"><label for="height">Height in Pixels</label></th>
								<td><?php $this->_addTextBox( 'height', array( 'size' => '5', 'maxlength' => '5' ) ); ?></td>
							</tr>
						</table>
					</td>
				</tr>
			</table>
			
			<p class="submit">
				<?php $this->_addSubmit( 'add_group', 'Add Group' ); ?>
			</p>
		</form>
		
		<br /><br />
		<a href="http://pluginbuddy.com" style="text-decoration: none;"><img src="<?php echo $this->_pluginURL; ?>/images/pluginbuddy.png" style="vertical-align: -3px;" /> PluginBuddy.com</a>
	</div>
<?php
			
		}
		
		
		// Widget Functions /////////////////////////
		
		function widgetsRender( $args, $widget_args = 1 ) {
			extract( $args, EXTR_SKIP );
			
			
			if ( is_numeric( $widget_args ) )
				$widget_args = array( 'number' => $widget_args );
			$widget_args = wp_parse_args( $widget_args, array( 'number' => -1 ) );
			
			$widget = $this->_options['widgets'][$widget_args['number']];
			
			$this->_group_id = $widget['group_id'];
			
			
			$group = $this->_options['groups'][$widget['group_id']];
			
			if ( ! is_array( $group['entries'] ) )
				$group['entries'] = array();
			
			
			echo $before_widget;
			
			if ( ! empty( $widget['title'] ) )
				echo $before_title . $widget['title'] . $after_title;
			
			
			$css_id = $this->_class . '-' . $widget_args['number'];
			
			$css_class = strtolower( $group['name'] );
			$css_class = preg_replace( '/\s+/', '-', $css_class );
			$css_class = preg_replace( '/[^\w\-]/', '', $css_class );
			
			$alignment = '';
			if ( 'none' !== $widget['alignment'] )
				$alignment = ' style="text-align:' . $widget['alignment'] . ';"';
			
			echo '<div class="' . $this->_class . ' ' . $this->_class . "-$css_class\" $alignment>\n";
			
			
			if ( 'random' === $widget['order'] )
				$this->_randomSort( $group['entries'] );
			elseif ( 'alphabetical' === $widget['order'] ) {
				$this->_group = $group;
				uksort( $group['entries'], array( &$this, '_alphaSort' ) );
			}
			else {
				$this->_group = $group;
				uksort( $group['entries'], array( &$this, '_orderedSort' ) );
			}
			
			if ( ( 'all' !== $widget['max'] ) && intval( $widget['max'] ) > 0 )
				$group['entries'] = array_slice( $group['entries'], 0, intval( $widget['max'] ) );
			
			
			ob_start( array( &$this, 'filter_widget_output' ) );
			
			foreach ( (array) $group['entries'] as $id => $entry ) {
				$image = $this->_get_resized_image( $widget['group_id'], $entry['image'] );
				
				if ( ! isset( $entry['description'] ) )
					$entry['description'] = '';
				
				$target = '';
				if ( 'yes' === $widget['new_window'] )
					$target = ' target="group-' . $widget['group_id'] . '-entry-' . $id . '"';
				
?>
	<?php if ( ! empty( $entry['url'] ) ) : ?>
		<a href="<?php echo $entry['url']; ?>" title="<?php echo $entry['description']; ?>"<?php echo $target; ?>>
	<?php endif; ?>
		<?php if ( ! is_wp_error( $image ) ) : ?>
			<img src="<?php echo $image['url']; ?>" alt="<?php echo $entry['description']; ?>" />
		<?php elseif ( ! empty( $entry['description'] ) ) : ?>
			<!-- <?php echo $image->get_error_message(); ?> -->
			<?php echo $entry['description']; ?>
		<?php endif; ?>
	<?php if ( ! empty( $entry['url'] ) ) : ?>
		</a>
	<?php endif; ?>
<?php
				
			}
			
			ob_end_flush();
			
			echo "</div>\n";
			
			
			echo $after_widget;
		}
		
		function filter_widget_output( $content ) {
			// Run content through ShadowBox JS plugin's filter if it exists
			global $ShadowboxFrontend;
			
			if ( isset( $ShadowboxFrontend ) && method_exists( $ShadowboxFrontend, 'add_attr_to_link' ) ) {
				global $post;
				
				// The add_attr_to_link function uses the post ID to generate gallery groups.
				// A random post ID is generated to ensure that each Billboard group is its own group.
				$post_id = $post->ID;
				$post->ID = "billboard-$this->_group_id-" . rand( 1, 10000 );
				
				$content = $ShadowboxFrontend->add_attr_to_link( $content );
				
				$post->ID = $post_id;
			}
			
			
			$content = apply_filters( 'it_billboard_widget_output', $content );
			
			
			return $content;
		}
		
		function widgetsControl( $widget_args = 1 ) {
			global $wp_registered_widgets;
			static $updated = false;
			
			
			if ( is_numeric( $widget_args ) )
				$number = (int) $widget_args;
			elseif ( is_array( $widget_args ) && ! empty( $widget_args['number'] ) )
				$number = (int) $widget_args['number'];
			
			if ( empty( $number ) )
				$number = -1;
			
			
			if ( ! is_array( $this->_options['widgets'] ) )
				$this->_options['widgets'] = array();
			
			
			if ( ! $updated && ! empty( $_POST['sidebar'] ) ) {
				$sidebar = (string) $_POST['sidebar'];
				
				$widgets = wp_get_sidebars_widgets();
				
				if ( is_array( $widgets[$sidebar] ) ) {
					foreach ( (array) $widgets[$sidebar] as $id ) {
						if ( ( array( &$this, 'widgetsRender' ) == $wp_registered_widgets[$id]['callback'] ) && isset( $wp_registered_widgets[$id]['params'][0]['number'] ) ) {
							$num = $wp_registered_widgets[$id]['params'][0]['number'];
							if ( ! class_exists( 'WP_Widget' ) && ! in_array( $this->_var . '-' . $num, $_POST['id'] ) )
								unset( $this->_options['widgets'][$num] );
						}
					}
				}
				
				foreach ( (array) $_POST[$this->_var] as $num => $widget )
					$this->_options['widgets'][$num] = $widget;
				
				$this->save();
				
				$updated = true;
			}
			
			
			if ( -1 == $number )
				$number = '%i%';
			
			
			$groupIDs = array();
			foreach ( (array) $this->_options['groups'] as $id => $group )
				$groupIDs[$id] = $group['name'];
			
			
			$orders = array( 'ordered' => 'As Ordered (default)', 'alphabetical' => 'Alphabetical by Description', 'random' => 'Random' );
			
			
			$limits = array();
			$limits['all'] = 'Show All (default)';
			
			for ( $count = 1; $count <= 20; $count++ )
				$limits[$count] = $count;
			
			
			if ( ! isset( $this->_options['widgets'] ) )
				$this->_options['widgets'] = array();
			if ( ! isset( $this->_options['widgets'][$number] ) )
				$this->_options['widgets'][$number] = array();
			
			$defaults = array(
				'alignment'		=> 'center',
				'title'			=> '',
				'group_id'		=> '',
				'order'			=> 'ordered',
				'max'			=> 'all',
				'new_window'	=> 'yes',
			);
			
			$this->_options['widgets'][$number] = wp_parse_args( $this->_options['widgets'][$number], $defaults );
			
?>
		<p><label for="<?php echo $this->_var . "-${number}-title"; ?>">
			Title (optional):<br />
			<?php $this->_addTextBox( "[$number][title]", array(), false, $this->_options['widgets'][$number]['title'] ); ?>
		</label></p>
		<p><label for="<?php echo $this->_var . "-${number}-group_id"; ?>">
			Billboard Group:<br />
			<?php $this->_addDropDown( "[$number][group_id]", $groupIDs, false, $this->_options['widgets'][$number]['group_id'] ); ?>
		</label></p>
		<br />
		
		<p><label for="<?php echo $this->_var . "-${number}-order"; ?>">
			Entry order:<br />
			<?php $this->_addDropDown( "[$number][order]", $orders, false, $this->_options['widgets'][$number]['order'] ); ?>
		</label></p>
		<p><label for="<?php echo $this->_var . "-${number}-max"; ?>">
			Maximum number to show:<br />
			<?php $this->_addDropDown( "[$number][max]", $limits, false, $this->_options['widgets'][$number]['max'] ); ?>
		</label></p>
		<p><label for="<?php echo $this->_var . "-${number}-alignment"; ?>">
			Horizontal alignment:<br />
			<?php $this->_addDropDown( "[$number][alignment]", array( 'left' => 'Left', 'center' => 'Center (default)', 'right' => 'Right', 'none' => 'None (controlled by CSS)' ), false, $this->_options['widgets'][$number]['alignment'] ); ?>
		</label></p>
		<p><label for="<?php echo $this->_var . "-${number}-new_window"; ?>">
			Open links in new window/tab:<br />
			<?php $this->_addDropDown( "[$number][new_window]", array( 'yes' => 'Yes (default)', 'no' => 'No' ), false, $this->_options['widgets'][$number]['new_window'] ); ?>
		</label></p>
		<br />
		
		<p>
			This widget's CSS ID:
			<div style="text-align:center; padding:5px; background-color:#CCC;"><?php echo $this->_class . '-' . $number; ?></div>
		</p>
<?php
			
		}
		
		
		// Form Functions ///////////////////////////
		
		function _newForm() {
			$this->_usedInputs = array();
		}
		
		function _addSubmit( $var, $options = array(), $override_value = true, $widget = false ) {
			if ( ! is_array( $options ) )
				$options = array( 'value' => $options );
			
			$options['type'] = 'submit';
			$options['name'] = $var;
			$options['class'] = ( empty( $options['class'] ) ) ? 'button-primary' : $options['class'];
			
			if ( false !== $widget )
				$this->_addWidgetInput( $var, $widget, $options, $override_value );
			else
				$this->_addSimpleInput( $var, $options, $override_value );
		}
		
		function _addButton( $var, $options = array(), $override_value = true, $widget = false ) {
			if ( ! is_array( $options ) )
				$options = array( 'value' => $options );
			
			$options['type'] = 'button';
			$options['name'] = $var;
			
			if ( false !== $widget )
				$this->_addWidgetInput( $var, $widget, $options, $override_value );
			else
				$this->_addSimpleInput( $var, $options, $override_value );
		}
		
		function _addTextBox( $var, $options = array(), $override_value = false, $widget = false ) {
			if ( ! is_array( $options ) )
				$options = array( 'value' => $options );
			
			$options['type'] = 'text';
			
			if ( false !== $widget )
				$this->_addWidgetInput( $var, $widget, $options, $override_value );
			else
				$this->_addSimpleInput( $var, $options, $override_value );
		}
		
		function _addTextArea( $var, $options = array(), $override_value = false, $widget = false ) {
			if ( ! is_array( $options ) )
				$options = array( 'value' => $options );
			
			$options['type'] = 'textarea';
			
			if ( false !== $widget )
				$this->_addWidgetInput( $var, $widget, $options, $override_value );
			else
				$this->_addSimpleInput( $var, $options, $override_value );
		}
		
		function _addFileUpload( $var, $options = array(), $override_value = false, $widget = false ) {
			if ( ! is_array( $options ) )
				$options = array( 'value' => $options );
			
			$options['type'] = 'file';
			$options['name'] = $var;
			
			if ( false !== $widget )
				$this->_addWidgetInput( $var, $widget, $options, $override_value );
			else
				$this->_addSimpleInput( $var, $options, $override_value );
		}
		
		function _addCheckBox( $var, $options = array(), $override_value = false, $widget = false ) {
			if ( ! is_array( $options ) )
				$options = array( 'value' => $options );
			
			$options['type'] = 'checkbox';
			
			if ( false !== $widget )
				$this->_addWidgetInput( $var, $widget, $options, $override_value );
			else
				$this->_addSimpleInput( $var, $options, $override_value );
		}
		
		function _addMultiCheckBox( $var, $options = array(), $override_value = false, $widget = false ) {
			if ( ! is_array( $options ) )
				$options = array( 'value' => $options );
			
			$options['type'] = 'checkbox';
			$var = $var . '[]';
			
			if ( false !== $widget )
				$this->_addWidgetInput( $var, $widget, $options, $override_value );
			else
				$this->_addSimpleInput( $var, $options, $override_value );
		}
		
		function _addRadio( $var, $options = array(), $override_value = false, $widget = false ) {
			if ( ! is_array( $options ) )
				$options = array( 'value' => $options );
			
			$options['type'] = 'radio';
			
			if ( false !== $widget )
				$this->_addWidgetInput( $var, $widget, $options, $override_value );
			else
				$this->_addSimpleInput( $var, $options, $override_value );
		}
		
		function _addDropDown( $var, $options = array(), $override_value = false, $widget = false ) {
			if ( ! is_array( $options ) )
				$options = array();
			else if ( ! isset( $options['value'] ) || ! is_array( $options['value'] ) )
				$options = array( 'value' => $options );
			
			$options['type'] = 'dropdown';
			
			if ( false !== $widget )
				$this->_addWidgetInput( $var, $widget, $options, $override_value );
			else
				$this->_addSimpleInput( $var, $options, $override_value );
		}
		
		function _addHidden( $var, $options = array(), $override_value = false, $widget = false ) {
			if ( ! is_array( $options ) )
				$options = array( 'value' => $options );
			
			$options['type'] = 'hidden';
			
			if ( false !== $widget )
				$this->_addWidgetInput( $var, $widget, $options, $override_value );
			else
				$this->_addSimpleInput( $var, $options, $override_value );
		}
		
		function _addHiddenNoSave( $var, $options = array(), $override_value = true, $widget = false ) {
			if ( ! is_array( $options ) )
				$options = array( 'value' => $options );
			
			$options['name'] = $var;
			
			$this->_addHidden( $var, $options, $override_value, $widget );
		}
		
		function _addDefaultHidden( $var ) {
			$options = array();
			$options['value'] = $this->defaults[$var];
			
			$var = "default_option_$var";
			
			if ( false !== $widget )
				$this->_addWidgetInput( $var, $options );
			else
				$this->_addSimpleInput( $var, $options );
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
					if ( (string) $value === (string) $options['value'] )
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
							$attributes .= "$name=\"" . esc_html( $val ) . '" ';
			
			
			if ( 'textarea' === $options['type'] )
				echo '<textarea ' . $attributes . '>' . $options['value'] . '</textarea>';
			elseif ( 'dropdown' === $options['type'] ) {
				echo "<select $attributes>\n";
				
				foreach ( (array) $options['value'] as $val => $name ) {
					$selected = ( (string) $this->_options[$var] === (string) $val ) ? ' selected="selected"' : '';
					echo "<option value=\"$val\"$selected>$name</option>\n";
				}
				
				echo "</select>\n";
			}
			else
				echo '<input ' . $attributes . '/>';
		}
		
		function _addWidgetInput( $var, $value, $options = false, $override_value = false ) {
			if ( empty( $options['type'] ) ) {
				echo "<!-- _addWidgetInput called without a type option set. -->\n";
				return false;
			}
			
			
			$scrublist['textarea']['value'] = true;
			$scrublist['file']['value'] = true;
			$scrublist['dropdown']['value'] = true;
			
			$defaults = array();
			$defaults['name'] = $this->_var . $var;
			
			$clean_var = $this->_var . $var;
			$clean_var = str_replace( '[', '-', $clean_var );
			$clean_var = str_replace( ']', '' , $clean_var );
			
			if ( 'checkbox' === $options['type'] )
				$defaults['class'] = $clean_var;
			else
				$defaults['id'] = $clean_var;
			
			$options = $this->_merge_defaults( $options, $defaults );
			
			if ( ( false === $override_value ) && isset( $value ) ) {
				if ( 'checkbox' === $options['type'] ) {
					if ( (string) $value === (string) $options['value'] )
						$options['checked'] = 'checked';
				}
				elseif ( 'dropdown' !== $options['type'] )
					$options['value'] = $value;
			}
			
			if ( ( preg_match( '/^' . $this->_var . '/', $options['name'] ) ) && ( ! in_array( $options['name'], $this->_usedInputs ) ) )
				$this->_usedInputs[] = $options['name'];
			
			
			$attributes = '';
			
			if ( false !== $options ) {
				foreach ( (array) $options as $name => $val ) {
					if ( ! is_array( $val ) && ( ! isset( $scrublist[$options['type']][$name] ) || ( true !== $scrublist[$options['type']][$name] ) ) ) {
						if ( ( 'submit' === $options['type'] ) || ( 'button' === $options['type'] ) )
							$attributes .= "$name=\"$val\" ";
						else
							$attributes .= "$name=\"" . esc_html( $val ) . '" ';
					}
				}
			}
			
			
			if ( 'textarea' === $options['type'] )
				echo '<textarea ' . $attributes . '>' . $options['value'] . '</textarea>';
			elseif ( 'dropdown' === $options['type'] ) {
				echo "<select $attributes>\n";
				
				foreach ( (array) $options['value'] as $val => $name ) {
					$selected = ( (string) $value === (string) $val ) ? ' selected="selected"' : '';
					echo "<option value=\"$val\"$selected>$name</option>\n";
				}
				
				echo "</select>\n";
			}
			else
				echo '<input ' . $attributes . '/>';
		}
		
		
		// Plugin Functions ///////////////////////////
		
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
		
		function _get_resized_image( $group_id, $file_id ) {
			require_once( $this->_pluginPath . '/lib/file-utility/file-utility.php' );
			
			
			if ( ! is_array( $this->_options['groups'] ) || ! is_array( $this->_options['groups'][$group_id] ) )
				return new WP_Error( 'cannot_find_group', "Unable to find requested group ($group_id)" );
			
			$group = $this->_options['groups'][$group_id];
			
			if ( ! is_int( $group['width'] ) || ! is_int( $group['height'] ) || empty( $group['resize'] ) )
				return new WP_Error( 'invalid_group_data', 'Invalid group data: resize = [' . $group['resize'] . '], width = [' . $group['width'] . '], height = [' . $group['height'] . ']' );
			
			
			if ( 'none' === $group['resize'] )
				return iThemesFileUtility::get_file_attachment( $file_id );
			elseif ( 'width' === $group['resize'] )
				return iThemesFileUtility::resize_image( $file_id, $group['width'] );
			elseif ( 'height' === $group['resize'] )
				return iThemesFileUtility::resize_image( $file_id, 0, $group['height'] );
			elseif ( 'bothcrop' === $group['resize'] )
				return iThemesFileUtility::resize_image( $file_id, $group['width'], $group['height'] );
			elseif ( 'bothnocrop' === $group['resize'] )
				return iThemesFileUtility::resize_image( $file_id, $group['width'], $group['height'], false );
			
			return new WP_Error( 'invalid_group_resize', 'Invalid group resize option: ' . $group['resize'] );
		}
		
		function _alphaSort( $a, $b ) {
			return strcasecmp( $this->_group['entries'][$a]['description'], $this->_group['entries'][$b]['description'] );
		}
		
		function _orderedSort( $a, $b ) {
			$a = $this->_group['entries'][$a];
			$b = $this->_group['entries'][$b];
			
			
			if ( 'top' === $a['priority'] ) {
				if ( 'top' !== $b['priority'] )
					return -1;
			}
			elseif ( 'top' === $b['priority'] )
				return 1;
			
			if ( $a['order'] < $b['order'] )
				return -1;
			
			return 1;
		}
		
		function _randomSort( &$array ) {
			if ( ! is_array( $array ) )
				return;
			
			$savedGroup = $this->_group;
			$this->_group = $array;
			
			uksort( $array, array( &$this, '_orderedSort' ) );
			
			$this->_group = $savedGroup;
			
			
			$top = array();
			$normal = array();
			
			foreach ( (array) $array as $key => $val ) {
				if ( 'top' == $val['priority'] )
					$top[$key] = $val;
				else
					$normal[$key] = $val;
			}
			
			$keys = array_keys( $normal );
			shuffle( $keys );
			
			$new = array();
			foreach( $keys as $key )
				$new[$key] = $normal[$key];
			
			$array = array_merge( $top, $new );
		}
		
		function _sortGroupsByName( $a, $b ) {
			if ( $this->_options['groups'][$a]['name'] < $this->_options['groups'][$b]['name'] )
				return -1;
			
			return 1;
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

		} //end upgrader_instantiate	// Utility Functions //////////////////////////
		
	}
	
	$GLOBALS['iThemesBillboard'] =& new iThemesBillboard();
}

?>
