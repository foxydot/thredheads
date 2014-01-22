<?php
if ( !class_exists( "pluginbuddy_loopbuddy_admin" ) ) {
	class pluginbuddy_loopbuddy_admin {
		
		function pluginbuddy_loopbuddy_admin(&$parent) {
			$this->_parent = &$parent;
			$this->_var = &$parent->_var;
			$this->_name = &$parent->_name;
			$this->_options = &$parent->_options;
			$this->_pluginPath = &$parent->_pluginPath;
			$this->_pluginURL = &$parent->_pluginURL;
			$this->_selfLink = &$parent->_selfLink;
			
			add_action('admin_menu', array(&$this, 'admin_menu')); // Add menu in admin.
			add_action('admin_menu', array(&$this, 'export')); //For the export function
		}
		
		//Spits out a text file for download when exporting
		function export() {
			if ( isset( $_POST[ 'export_groups' ] ) ) {
				check_admin_referer( $this->_parent->_var . '-nonce' );
				if ( ! empty( $_POST['items'] ) && is_array( $_POST['items'] ) ) {
					$export_groups = array();
					
					foreach ( (array) $_POST['items'] as $id ) {
						$export_groups[] = $this->_parent->get_layout( $id );
					}
					$export_groups = base64_encode( maybe_serialize( $export_groups ) );
					$now = gmdate('D, d M Y H:i:s') . ' GMT';

					header('Content-Type: text/plain');
					header('Expires: ' . $now);
		
					header('Content-Disposition: attachment; filename="loopbuddy_layouts.txt"');
					header('Pragma: no-cache');
		
					echo trim( $export_groups );
					exit;	
				}
			} //end export layouts
			
			if ( isset( $_POST[ 'export_queries' ] ) ) {
				check_admin_referer( $this->_parent->_var . '-nonce' );
				if ( ! empty( $_POST['items'] ) && is_array( $_POST['items'] ) ) {
					$export_queries = array();
					
					foreach ( (array) $_POST['items'] as $id ) {
						$export_queries[] = $this->_parent->get_query( $id );
					}
					$export_queries = base64_encode( maybe_serialize( $export_queries ) );
					$now = gmdate('D, d M Y H:i:s') . ' GMT';

					header('Content-Type: text/plain');
					header('Expires: ' . $now);
		
					header('Content-Disposition: attachment; filename="loopbuddy_queries.txt"');
					header('Pragma: no-cache');
		
					echo trim( $export_queries );
					exit;	
				}
			} //end export queries
		} //end export
		
		function alert( $arg1, $arg2 = false ) {
			$this->_parent->alert( $arg1, $arg2 );
		}
		
		
		function tip( $message, $title = '', $echo_tip = true ) {
			if ( $echo_tip === true ) {
				$this->_parent->tip( $message, $title, $echo_tip );
			} else {
				return $this->_parent->tip( $message, $title = '', $echo_tip );
			}
		}
		
		
		function video( $video_key, $title = '', $echo_tip = true ) {
			
			if ( $echo_tip === true ) {
				$this->_parent->video( $video_key, $title, $echo_tip );
			} else {
				$return = $this->_parent->video( $video_key, $title, false );
				return $return;
			}
		}
		
		
		function title( $title ) {
			echo sprintf( "<h2>%s</h2>", esc_html( $title ) );
		}
		
		
		function nonce() {
			wp_nonce_field( $this->_parent->_var . '-nonce' );
		}
		
		
		/**
		 *	savesettings()
		 *	
		 *	Saves a form into the _options array.
		 *	
		 *	Use savepoint to set the root array key path. Accepts variable depth, dividing array keys with pound signs.
		 *	Ex:	$_POST['savepoint'] value something like array_key_name#subkey
		 *		<input type="hidden" name="savepoint" value="files#exclusions" /> to set the root to be $this->_options['files']['exclusions']
		 *	
		 *	All inputs with the name beginning with pound will act as the array keys to be set in the _options with the associated posted value.
		 *	Ex:	$_POST['#key_name'] or $_POST['#key_name#subarray_key_name'] value is the array value to set.
		 *		<input type="text" name="#name" /> will save to $this->_options['name']
		 *		<input type="text" name="#group#17#name" /> will save to $this->_options['groups'][17]['name']
		 *
		 *	$savepoint_root		string		Override the savepoint. Same format as the form savepoint.
		 */
		function savesettings( $savepoint_root = '' ) {
			check_admin_referer( $this->_parent->_var . '-nonce' );
			
			
			if ( !empty( $savepoint_root ) ) { // Override savepoint.
				$_POST['savepoint'] = $savepoint_root;
			}
			
			if ( !empty( $_POST['savepoint'] ) ) {
				$savepoint_root = stripslashes( $_POST['savepoint'] ) . '#';
			} else {
				$savepoint_root = '';
			}
			
			$posted = stripslashes_deep( $_POST ); // Unescape all the stuff WordPress escaped. Sigh @ WordPress for being like PHP magic quotes.
			foreach( $posted as $index => $item ) {
				if ( substr( $index, 0, 1 ) == '#' ) {
					$savepoint_subsection = &$this->_options;
					$savepoint_levels = explode( '#', $savepoint_root . substr( $index, 1 ) );
					foreach ( $savepoint_levels as $savepoint_level ) {
						$savepoint_subsection = &$savepoint_subsection{$savepoint_level};
					}
					$savepoint_subsection = $item;
				}
			}
			
			$this->_parent->save();
			$this->alert( __( 'Settings saved...', 'it-l10n-loopbuddy' ) );
		}
		
		
		function admin_scripts() {
			//wp_enqueue_script( 'jquery' );
			wp_enqueue_script( 'pluginbuddy-tooltip-js', $this->_parent->_pluginURL . '/js/tooltip.js', array( 'jquery' ) );
			wp_print_scripts( 'pluginbuddy-tooltip-js' );
			wp_enqueue_script( 'pluginbuddy-'.$this->_var.'-admin-js', $this->_parent->_pluginURL . '/js/admin.js' );
			wp_print_scripts( 'pluginbuddy-'.$this->_var.'-admin-js' );
			echo '<link rel="stylesheet" href="'.$this->_pluginURL . '/css/admin.css" type="text/css" media="all" />';
		}
		
		
		/**
		 *	get_feed()
		 *
		 *	Gets an RSS or other feed and inserts it as a list of links...
		 *
		 *	$feed		string		URL to the feed.
		 *	$limit		integer		Number of items to retrieve.
		 *	$append		string		HTML to include in the list. Should usually be <li> items including the <li> code.
		 *	$replace	string		String to replace in every title returned. ie twitter includes your own username at the beginning of each line.
		 *	$cache_time	int			Amount of time to cache the feed, in seconds.
		 */
		function get_feed( $feed, $limit, $append = '', $replace = '', $cache_time = 300 ) {
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
					set_transient( md5( $feed ), $feed_html, $cache_time ); // expires in 300secs aka 5min
				}
				echo $feed_html;
				
				echo $append;
				echo '</ul>';
			} else {
				echo __( 'Temporarily unable to load feed...', 'it-l10n-loopbuddy' );
			}
		}
		
		
		function view_gettingstarted() {
			require( 'view_gettingstarted.php' );
		}
		
		
		function view_queries() {
			require( 'view_queries.php' );
		}
		
		
		function view_layouts() {
			require( 'view_layouts.php' );
		}
		
		
		function view_settings() {
			require( 'view_settings.php' );
		}
		
		
		/** admin_menu()
		 *
		 * Initialize menu for admin section.
		 *
		 */		
		function admin_menu() {
			if ( isset( $this->_parent->_series ) && ( $this->_parent->_series != '' ) ) {
				// Handle series menu. Create series menu if it does not exist.
				global $menu;
				$found_series = false;
				foreach ( $menu as $menus => $item ) {
					if ( $item[0] == $this->_parent->_series ) {
						$found_series = true;
					}
				}
				if ( $found_series === false ) {
					add_menu_page( $this->_parent->_series . ' ' . __( 'Getting Started', 'it-l10n-loopbuddy' ), $this->_parent->_series, 'administrator', 'pluginbuddy-' . strtolower( $this->_parent->_series ), array(&$this, 'view_gettingstarted'), $this->_parent->_pluginURL.'/images/pluginbuddy.png' );
					add_submenu_page( 'pluginbuddy-' . strtolower( $this->_parent->_series ), $this->_parent->_name.' ' . __( 'Getting Started', 'it-l10n-loopbuddy' ), __( 'Getting Started', 'it-l10n-loopbuddy' ), 'administrator', 'pluginbuddy-' . strtolower( $this->_parent->_series ), array(&$this, 'view_gettingstarted') );
				}
				// Register for getting started page
				global $pluginbuddy_series;
				if ( !isset( $pluginbuddy_series[ $this->_parent->_series ] ) ) {
					$pluginbuddy_series[ $this->_parent->_series ] = array();
				}
				$pluginbuddy_series[ $this->_parent->_series ][ $this->_parent->_name ] = $this->_pluginPath;
				
				add_submenu_page( 'pluginbuddy-' . strtolower( $this->_parent->_series ), $this->_parent->_name, $this->_parent->_name, 'administrator', $this->_parent->_var.'-settings', array(&$this, 'view_settings'));
			} else { // NOT IN A SERIES!
				// Add main menu (default when clicking top of menu)
				add_menu_page($this->_parent->_name.' ' . __( 'Getting Started', 'it-l10n-loopbuddy' ), $this->_parent->_name, 'administrator', $this->_parent->_var, array(&$this, 'view_gettingstarted'), $this->_parent->_pluginURL.'/images/pluginbuddy.png');
				// Add sub-menu items (first should match default page above)
				add_submenu_page( $this->_parent->_var, $this->_parent->_name.' ' . __( 'Getting Started', 'it-l10n-loopbuddy' ), __( 'Getting Started', 'it-l10n-loopbuddy' ), 'administrator', $this->_parent->_var, array(&$this, 'view_gettingstarted'));
				add_submenu_page( $this->_parent->_var, $this->_parent->_name.' ' . __( 'Query Editor', 'it-l10n-loopbuddy' ), __( 'Query Editor', 'it-l10n-loopbuddy' ), 'administrator', $this->_parent->_var.'-queries', array(&$this, 'view_queries'));
				add_submenu_page( $this->_parent->_var, $this->_parent->_name.' ' . __( 'Layout Editor', 'it-l10n-loopbuddy' ), __( 'Layout Editor', 'it-l10n-loopbuddy' ), 'administrator', $this->_parent->_var.'-layouts', array(&$this, 'view_layouts'));
				$admin_hook = add_submenu_page( $this->_parent->_var, $this->_parent->_name.' ' . __( 'Settings', 'it-l10n-loopbuddy' ), __( 'Settings', 'it-l10n-loopbuddy' ), 'administrator', $this->_parent->_var.'-settings', array(&$this, 'view_settings'));
				add_action( 'admin_print_scripts-' . $admin_hook, array( &$this, 'admin_scripts' ) );
			}
		}
		
	} // End class
	
	$pluginbuddy_loopbuddy_admin = new pluginbuddy_loopbuddy_admin($this);
}