<?php
if ( !class_exists( "pluginbuddy_accordion_admin" ) ) {
    class pluginbuddy_accordion_admin {
	
		function pluginbuddy_accordion_admin(&$parent) {
			$this->_parent = &$parent;
			$this->_var = &$parent->_var;
			$this->_name = &$parent->_name;
			$this->_options = &$parent->_options;
			$this->_pluginPath = &$parent->_pluginPath;
			$this->_pluginURL = &$parent->_pluginURL;
			$this->_selfLink = &$parent->_selfLink;

			add_action('admin_menu', array(&$this, 'admin_menu')); // Add menu in admin.
			add_action( 'wp_ajax_pb_accordion_add_item', array( &$this, 'ajax_add_accordion_item' ) );
			
			//Register scripts/styles
			wp_register_script( 'pluginbuddy-tooltip-js', $this->_parent->_pluginURL . '/js/tooltip.js', array( 'jquery' ) );
			wp_register_script( 'pluginbuddy-swiftpopup-js', $this->_parent->_pluginURL . '/js/swiftpopup.js', array( 'jquery' ) );
			wp_register_script( 'pluginbuddy-'.$this->_var.'-admin-js', $this->_parent->_pluginURL . '/js/admin.js', array( 'jquery' ) );

		}
		function ajax_add_accordion_item() {
			check_ajax_referer( 'pb-add-accordion-item' );
			require( $this->_parent->_pluginPath . '/classes/view_accordion_ajax.php' );
			exit();
		} //end add_accordion_item
		function get_accordion_item_html( $item ) {
			ob_start();
			$ajax_assist_url = add_query_arg( 
				array(
					'action' => 'pb_accordion_add_item',
					'_ajax_nonce' => wp_create_nonce( 'pb-add-accordion-item' ),
					'parent_id' => absint( $item->post_parent ),
					'TB_iframe' => true
				),
				admin_url( 'admin-ajax.php' )
			);
			global $post;
			$post = $item;
			setup_postdata( $post );
			?>
			<tr id='accordion_item_<?php echo esc_attr( get_the_ID() ); ?>'>
					<th scope="row" class="check-column"><input type="checkbox" name="items[]" class="entries" value="<?php echo esc_attr( absint( $item->ID ) ); ?>" /></th>
					<td><?php the_title(); ?>
					<div class="row-actions" style="margin:0; padding:0;">
					<?php
						//Build a custom query string for the edit URL.  The TB_iframe param nees to be last, so we remove it and add it back
						$ajax_edit_url = remove_query_arg( 'TB_iframe', $ajax_assist_url );
						$ajax_edit_url = add_query_arg( array( 'edit_post_id' => $item->ID, 'TB_iframe' => true ), $ajax_edit_url );
					?>
						<a class='thickbox' title='<?php esc_attr_e( 'Edit Accordion Item', 'it-l10n-accordion' ); ?>' href="<?php echo esc_url( $ajax_edit_url ); ?>"><?php esc_html_e( 'Edit Accordion Item', 'it-l10n-accordion' ); ?></a>
					</div>
					</td>
					<td>
						<?php
							$content = $item->post_content;
							if ( empty( $content ) ) echo __( 'No content', 'it-l10n-accordion' );
							else the_excerpt();
						?>
					</td>
					<td>
						<?php
							$meta = get_post_meta( $item->ID, 'post_id', true );
							if ( $meta ) {
								echo absint( $meta );
							} else {
								echo __( 'No post selected', 'it-l10n-accordion' );
							}
						?>
					</td>
					<td class="dragHandle">
						<img alt="<?php esc_attr_e( 'Click and drag to reorder', 'it-l10n-accordion' ); ?>" src="<?php echo esc_url ( $this->_parent->_pluginURL .  '/images/draghandle2.png' ); ?>">
					</td>
				</tr>	
			<?php
			return ob_get_clean();
		} //end get_accordion_item_html
		function alert( $arg1, $arg2 = false ) {
			$this->_parent->alert( $arg1, $arg2 );
		}
		function tip( $message, $title = '', $echo_tip = true ) {
			if ( $echo_tip === true ) {
				$this->_parent->tip( $message, $title = '', $echo_tip = true );
			} else {
				return $this->_parent->tip( $title = '', $message, $echo_tip = true );
			}
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
			$this->alert( __('Settings saved...', 'it-l10n-backupbuddy') );
		}
		
		function admin_scripts_settings() {
			wp_enqueue_script( 'pluginbuddy-tooltip-js' );
			wp_enqueue_script( 'pluginbuddy-'.$this->_var.'-admin-js' );
			wp_enqueue_script( 'pluginbuddy-reorder-js', $this->_parent->_pluginURL . '/js/tablednd.js' );
		} //end admin_scripts_settings
		function admin_styles_settings() {
			wp_enqueue_style( 'pluginbuddy-admin-css', $this->_pluginURL . '/css/admin.css' );
		} //end admin_styles_settings
		
		function admin_scripts() {
			wp_print_scripts( array( 'pluginbuddy-tooltip-js', 'pluginbuddy-'.$this->_var.'-admin-js' ) );			
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
		 */
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
		//Ensure an item is a percentage (e.g., 98%) - If it's not, it'll return the string as an integer
		function sanitize_percentage( $string ) {
			if ( substr( $string, -1 ) == '%' ) { /*percentages should be on the end */
				preg_match( '/(\d\d?\d?)/', $string, $matches );
				if ( !$matches ) {
					return absint( $string );
				} else {
					return absint( $matches[ 0 ] ) . '%';
				}
			} else {
				return absint( $string );
			}
		} //end sanitize_percentage
		
		function view_gettingstarted() {
			require( $this->_parent->_pluginPath . '/classes/view_gettingstarted.php' );
		}
		
		
		function view_settings() {
			require( $this->_parent->_pluginPath . '/classes/view_settings.php' );
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
					add_menu_page( $this->_parent->_series . ' Getting Started', $this->_parent->_series, 'edit_posts', 'pluginbuddy-' . strtolower( $this->_parent->_series ), array(&$this, 'view_gettingstarted'), $this->_parent->_pluginURL.'/images/pluginbuddy.png' );
					add_submenu_page( 'pluginbuddy-' . strtolower( $this->_parent->_series ), $this->_parent->_name.' Getting Started', 'Getting Started', 'edit_posts', 'pluginbuddy-' . strtolower( $this->_parent->_series ), array(&$this, 'view_gettingstarted') );
				}
				// Register for getting started page
				global $pluginbuddy_series;
				if ( !isset( $pluginbuddy_series[ $this->_parent->_series ] ) ) {
					$pluginbuddy_series[ $this->_parent->_series ] = array();
				}
				$pluginbuddy_series[ $this->_parent->_series ][ $this->_parent->_name ] = $this->_pluginPath;
				
				add_submenu_page( 'pluginbuddy-' . strtolower( $this->_parent->_series ), $this->_parent->_name, $this->_parent->_name, $this->_options['access'], $this->_parent->_var.'-settings', array(&$this, 'view_settings'));
			} else { // NOT IN A SERIES!
				// Add main menu (default when clicking top of menu)
				add_menu_page($this->_parent->_name.' Getting Started', $this->_parent->_name, 'administrator', $this->_parent->_var, array(&$this, 'view_gettingstarted'), $this->_parent->_pluginURL.'/images/pluginbuddy.png');
				// Add sub-menu items (first should match default page above)
				add_submenu_page( $this->_parent->_var, $this->_parent->_name.' Getting Started', 'Getting Started', 'administrator', $this->_parent->_var, array(&$this, 'view_gettingstarted'));
				//add_submenu_page( $this->_parent->_var, $this->_parent->_name.' Themes & Devices', 'Themes & Devices', 'administrator', $this->_parent->_var.'-themes', array(&$this, 'view_themes'));
				add_submenu_page( $this->_parent->_var, $this->_parent->_name.' Settings', 'Settings', 'administrator', $this->_parent->_var.'-settings', array(&$this, 'view_settings'));
			}
		}


    } // End class
	
	$pluginbuddy_accordion_admin = new pluginbuddy_accordion_admin($this); // Create instance
}
