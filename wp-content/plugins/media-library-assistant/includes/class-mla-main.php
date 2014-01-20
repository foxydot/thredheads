<?php
/**
 * Top-level functions for the Media Library Assistant
 *
 * @package Media Library Assistant
 * @since 0.1
 */

/* 
 * The Meta Boxes functions are't automatically available to plugins.
 */
if ( !function_exists( 'post_categories_meta_box' ) ) {
	require_once( ABSPATH . 'wp-admin/includes/meta-boxes.php' );
}

/**
 * Class MLA (Media Library Assistant) provides several enhancements to the handling
 * of images and files held in the WordPress Media Library.
 *
 * @package Media Library Assistant
 * @since 0.1
 */
class MLA {

	/**
	 * Current version number
	 *
	 * @since 0.1
	 *
	 * @var	string
	 */
	const CURRENT_MLA_VERSION = '1.70';

	/**
	 * Slug for registering and enqueueing plugin style sheet
	 *
	 * @since 0.1
	 *
	 * @var	string
	 */
	const STYLESHEET_SLUG = 'mla-style';

	/**
	 * Slug for localizing and enqueueing JavaScript - edit single item page
	 *
	 * @since 0.1
	 *
	 * @var	string
	 */
	const JAVASCRIPT_SINGLE_EDIT_SLUG = 'mla-single-edit-scripts';

	/**
	 * Object name for localizing JavaScript - edit single item page
	 *
	 * @since 0.1
	 *
	 * @var	string
	 */
	const JAVASCRIPT_SINGLE_EDIT_OBJECT = 'mla_single_edit_vars';

	/**
	 * Slug for localizing and enqueueing JavaScript - MLA List Table
	 *
	 * @since 0.20
	 *
	 * @var	string
	 */
	const JAVASCRIPT_INLINE_EDIT_SLUG = 'mla-inline-edit-scripts';

	/**
	 * Object name for localizing JavaScript - MLA List Table
	 *
	 * @since 0.20
	 *
	 * @var	string
	 */
	const JAVASCRIPT_INLINE_EDIT_OBJECT = 'mla_inline_edit_vars';

	/**
	 * Slug for adding plugin submenu
	 *
	 * @since 0.1
	 *
	 * @var	string
	 */
	const ADMIN_PAGE_SLUG = 'mla-menu';

	/**
	 * Action name; uniquely identifies the nonce
	 *
	 * @since 0.1
	 *
	 * @var	string
	 */
	const MLA_ADMIN_NONCE = 'mla_admin';

	/**
	 * mla_admin_action value for permanently deleting a single item
	 *
	 * @since 0.1
	 *
	 * @var	string
	 */
	const MLA_ADMIN_SINGLE_DELETE = 'single_item_delete';

	/**
	 * mla_admin_action value for displaying a single item
	 *
	 * @since 0.1
	 *
	 * @var	string
	 */
	const MLA_ADMIN_SINGLE_EDIT_DISPLAY = 'single_item_edit_display';

	/**
	 * mla_admin_action value for updating a single item
	 *
	 * @since 0.1
	 *
	 * @var	string
	 */
	const MLA_ADMIN_SINGLE_EDIT_UPDATE = 'single_item_edit_update';

	/**
	 * mla_admin_action value for restoring a single item from the trash
	 *
	 * @since 0.1
	 *
	 * @var	string
	 */
	const MLA_ADMIN_SINGLE_RESTORE = 'single_item_restore';

	/**
	 * mla_admin_action value for moving a single item to the trash
	 *
	 * @since 0.1
	 *
	 * @var	string
	 */
	const MLA_ADMIN_SINGLE_TRASH = 'single_item_trash';

	/**
	 * mla_admin_action value for mapping Custom Field metadata
	 *
	 * @since 1.10
	 *
	 * @var	string
	 */
	const MLA_ADMIN_SINGLE_CUSTOM_FIELD_MAP = 'single_item_custom_field_map';

	/**
	 * mla_admin_action value for mapping IPTC/EXIF metadata
	 *
	 * @since 1.00
	 *
	 * @var	string
	 */
	const MLA_ADMIN_SINGLE_MAP = 'single_item_map';

	/**
	 * Holds screen ids to match help text to corresponding screen
	 *
	 * @since 0.1
	 *
	 * @var	array
	 */
	private static $page_hooks = array();

	/**
	 * Initialization function, similar to __construct()
	 *
	 * This function contains add_action and add_filter calls
	 * to set up the Ajax handlers, enqueue JavaScript and CSS files, and 
	 * set up the Assistant submenu.
	 *
	 * @since 0.1
	 *
	 * @return	void
	 */
	public static function initialize( )
	{
		add_action( 'admin_init', 'MLA::mla_admin_init_action' );
		add_action( 'admin_enqueue_scripts', 'MLA::mla_admin_enqueue_scripts_action' );
		add_action( 'admin_menu', 'MLA::mla_admin_menu_action' );
		add_filter( 'set-screen-option', 'MLA::mla_set_screen_option_filter', 10, 3 ); // $status, $option, $value
		add_filter( 'screen_options_show_screen', 'MLA::mla_screen_options_show_screen_filter', 10, 2 ); // $show_screen, $this
	}

	/**
	 * Load a plugin text domain
	 * 
	 * The "add_action" for this function is in mla-plugin-loader.php, because the "initialize"
	 * function above doesn't run in time.
	 * Defined as public because it's an action.
	 *
	 * @since 1.60
	 *
	 * @return	void
	 */
	public static function mla_plugins_loaded_action(){
		$text_domain = 'media-library-assistant';
		$locale = apply_filters( 'mla_plugin_locale', get_locale(), $text_domain );

		/*
		 * To override the plugin's translation files for one, some or all strings,
		 * create a sub-directory named 'media-library-assistant' in the WordPress
		 * WP_LANG_DIR (e.g., /wp-content/languages) directory.
		 */
		load_textdomain( $text_domain, trailingslashit( WP_LANG_DIR ) . $text_domain . '/' . $text_domain . '-' . $locale . '.mo' );
		load_plugin_textdomain( $text_domain, false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );

		/*
		 * Now we can localize values in other plugin components
		 */
		MLAOptions::mla_localize_option_definitions_array();
		MLASettings::mla_localize_tablist();
	}

	/**
	 * Load the plugin's Ajax handler or process Edit Media update actions
	 *
	 * @since 0.20
	 *
	 * @return	void
	 */
	public static function mla_admin_init_action() {
		/*
		 * Process row-level actions from the Edit Media screen
		 */
		if ( !empty( $_REQUEST['mla_admin_action'] ) ) {
			check_admin_referer( self::MLA_ADMIN_NONCE );

			switch ( $_REQUEST['mla_admin_action'] ) {
				case self::MLA_ADMIN_SINGLE_CUSTOM_FIELD_MAP:
					$updates = MLAOptions::mla_evaluate_custom_field_mapping( $_REQUEST['mla_item_ID'], 'single_attachment_mapping' );

					if ( !empty( $updates ) ) {
						$item_content = MLAData::mla_update_single_item( $_REQUEST['mla_item_ID'], $updates );
					}

					$view_args = isset( $_REQUEST['mla_source'] ) ? array( 'mla_source' => $_REQUEST['mla_source']) : array();
					wp_redirect( add_query_arg( $view_args, admin_url( 'post.php' ) . '?post=' . $_REQUEST['mla_item_ID'] . '&action=edit&message=101' ), 302 );
					exit;
				case self::MLA_ADMIN_SINGLE_MAP:
					$item = get_post( $_REQUEST['mla_item_ID'] );
					$updates = MLAOptions::mla_evaluate_iptc_exif_mapping( $item, 'iptc_exif_mapping' );
					$page_content = MLAData::mla_update_single_item( $_REQUEST['mla_item_ID'], $updates );

					$view_args = isset( $_REQUEST['mla_source'] ) ? array( 'mla_source' => $_REQUEST['mla_source']) : array();
					wp_redirect( add_query_arg( $view_args, admin_url( 'post.php' ) . '?post=' . $_REQUEST['mla_item_ID'] . '&action=edit&message=102' ), 302 );
					exit;
				default:
					// ignore the rest
			} // switch ($_REQUEST['mla_admin_action'])
		} // (!empty($_REQUEST['mla_admin_action'])

		add_action( 'wp_ajax_' . self::JAVASCRIPT_INLINE_EDIT_SLUG, 'MLA::mla_inline_edit_action' );
	}

	/**
	 * Load the plugin's Style Sheet and Javascript files
	 *
	 * @since 0.1
	 *
	 * @param	string	Name of the page being loaded
	 *
	 * @return	void
	 */
	public static function mla_admin_enqueue_scripts_action( $page_hook ) {
		$suffix = defined('SCRIPT_DEBUG') && SCRIPT_DEBUG ? '' : '.min';

		if ( 'checked' != MLAOptions::mla_get_option( MLAOptions::MLA_SCREEN_DISPLAY_LIBRARY ) ) {
			wp_register_style( self::STYLESHEET_SLUG . '-nolibrary', MLA_PLUGIN_URL . 'css/mla-nolibrary.css', false, self::CURRENT_MLA_VERSION );
			wp_enqueue_style( self::STYLESHEET_SLUG . '-nolibrary' );
		}

		if ( 'edit-tags.php' == $page_hook ) {
			wp_register_style( self::STYLESHEET_SLUG, MLA_PLUGIN_URL . 'css/mla-edit-tags-style.css', false, self::CURRENT_MLA_VERSION );
			wp_enqueue_style( self::STYLESHEET_SLUG );
			return;
		}

		if ( 'media_page_' . self::ADMIN_PAGE_SLUG != $page_hook ) {
			return;
		}

		wp_register_style( self::STYLESHEET_SLUG, MLA_PLUGIN_URL . 'css/mla-style.css', false, self::CURRENT_MLA_VERSION );
		wp_enqueue_style( self::STYLESHEET_SLUG );

		if ( isset( $_REQUEST['mla_admin_action'] ) && ( $_REQUEST['mla_admin_action'] == self::MLA_ADMIN_SINGLE_EDIT_DISPLAY ) ) {
			wp_enqueue_script( self::JAVASCRIPT_SINGLE_EDIT_SLUG, MLA_PLUGIN_URL . "js/mla-single-edit-scripts{$suffix}.js", 
				array( 'wp-lists', 'suggest', 'jquery' ), self::CURRENT_MLA_VERSION, false );
			$script_variables = array(
				'comma' => _x( ',', 'tag_delimiter', 'media-library-assistant' ),
				'Ajax_Url' => admin_url( 'admin-ajax.php' ) 
			);
			wp_localize_script( self::JAVASCRIPT_SINGLE_EDIT_SLUG, self::JAVASCRIPT_SINGLE_EDIT_OBJECT, $script_variables );
		} else {
			wp_enqueue_script( self::JAVASCRIPT_INLINE_EDIT_SLUG, MLA_PLUGIN_URL . "js/mla-inline-edit-scripts{$suffix}.js", 
				array( 'wp-lists', 'suggest', 'jquery' ), self::CURRENT_MLA_VERSION, false );

			$fields = array( 'post_title', 'post_name', 'post_excerpt', 'post_content', 'image_alt', 'post_parent', 'menu_order', 'post_author' );
			$custom_fields = MLAOptions::mla_custom_field_support( 'quick_edit' );
			$custom_fields = array_merge( $custom_fields, MLAOptions::mla_custom_field_support( 'bulk_edit' ) );
			foreach ($custom_fields as $slug => $label ) {
				$fields[] = $slug;
			}

			$script_variables = array(
				'fields' => $fields,
				'error' => __( 'Error while saving the changes.', 'media-library-assistant' ),
				'ntdeltitle' => __( 'Remove From Bulk Edit', 'media-library-assistant' ),
				'notitle' => __( '(no title)', 'media-library-assistant' ),
				'comma' => _x( ',', 'tag_delimiter', 'media-library-assistant' ),
				'ajax_action' => self::JAVASCRIPT_INLINE_EDIT_SLUG,
				'ajax_nonce' => wp_create_nonce( self::MLA_ADMIN_NONCE ) 
			);
			wp_localize_script( self::JAVASCRIPT_INLINE_EDIT_SLUG, self::JAVASCRIPT_INLINE_EDIT_OBJECT, $script_variables );
		}
	}

	/**
	 * Add the submenu pages
	 *
	 * Add a submenu page in the "Media" section,
	 * add settings page in the "Settings" section.
	 * add settings link in the Plugins section entry for MLA.
	 *
	 * For WordPress versions before 3.5, 
	 * add submenu page(s) for attachment taxonomies,
	 * add filter to clean up taxonomy submenu labels.
	 *
	 * @since 0.1
	 *
	 * @return	void
	 */
	public static function mla_admin_menu_action( ) {
		if ( 'checked' != MLAOptions::mla_get_option( MLAOptions::MLA_SCREEN_DISPLAY_LIBRARY ) ) {
			add_action( 'load-upload.php', 'MLA::mla_load_media_action' );
		}

		$page_title = MLAOptions::mla_get_option( MLAOptions::MLA_SCREEN_PAGE_TITLE );
		$menu_title = MLAOptions::mla_get_option( MLAOptions::MLA_SCREEN_MENU_TITLE );
		$hook = add_submenu_page( 'upload.php', $page_title, $menu_title, 'upload_files', self::ADMIN_PAGE_SLUG, 'MLA::mla_render_admin_page' );
		add_action( 'load-' . $hook, 'MLA::mla_add_menu_options' );
		add_action( 'load-' . $hook, 'MLA::mla_add_help_tab' );
		self::$page_hooks[ $hook ] = $hook;

		$taxonomies = get_object_taxonomies( 'attachment', 'objects' );
		if ( !empty( $taxonomies ) ) {
			foreach ( $taxonomies as $tax_name => $tax_object ) {
				/*
				 * WordPress 3.5 adds native support for taxonomies
				 */
				if ( ! MLATest::$wordpress_3point5_plus ) {
					$hook = add_submenu_page( 'upload.php', $tax_object->label, $tax_object->label, 'manage_categories', 'mla-edit-tax-' . $tax_name, 'MLA::mla_edit_tax_redirect' );
					add_action( 'load-' . $hook, 'MLA::mla_edit_tax_redirect' );
				} // ! MLATest::$wordpress_3point5_plus

				/*
				 * The page_hook we need for taxonomy edits is slightly different
				 */
				$hook = 'edit-' . $tax_name;
				self::$page_hooks[ $hook ] = 't_' . $tax_name;
			} // foreach $taxonomies

			/*
			 * Load here, not 'load-edit-tags.php', to put our tab after the defaults
			 */
			add_action( 'admin_head-edit-tags.php', 'MLA::mla_add_help_tab' );
		}

		/*
		 * If we are suppressing the Media/Library submenu, force Media/Assistant to come first
		 */
		if ( 'checked' != MLAOptions::mla_get_option( MLAOptions::MLA_SCREEN_DISPLAY_LIBRARY ) ) {
			$menu_position = 4;
		} else {
			$menu_position = (integer) MLAOptions::mla_get_option( MLAOptions::MLA_SCREEN_ORDER );
		}

		if ( $menu_position ) {
			global $submenu_file, $submenu;
			foreach ( $submenu['upload.php'] as $menu_order => $menu_item ) {
				if ( self::ADMIN_PAGE_SLUG == $menu_item[2] ) {
					$submenu['upload.php'][$menu_position] = $menu_item;
					unset( $submenu['upload.php'][$menu_order] );
					ksort( $submenu['upload.php'] );
					break;
				}
			}
		}

		add_filter( 'parent_file', 'MLA::mla_parent_file_filter', 10, 1 );
	}

	/**
	 * Redirect to Media/Assistant if Media/Library is hidden
	 *
	 * @since 1.60
	 *
	 * @return	void
	 */
	public static function mla_load_media_action( ) {
		if ( 'checked' != MLAOptions::mla_get_option( MLAOptions::MLA_SCREEN_DISPLAY_LIBRARY ) ) {
			$query_args = '?page=mla-menu';

			/*
			 * Compose a message if returning from the Edit Media screen
			 */
			if ( ! empty( $_GET['deleted'] ) && $deleted = absint( $_GET['deleted'] ) ) {
				$query_args .= '&mla_admin_message=' . urlencode( sprintf( _n( 'Item permanently deleted.', '%d items permanently deleted.', $deleted, 'media-library-assistant' ), number_format_i18n( $_GET['deleted'] ) ) );
			}

			if ( ! empty( $_GET['trashed'] ) && $trashed = absint( $_GET['trashed'] ) ) {
				/* translators: 1: post ID */
				$query_args .= '&mla_admin_message=' . urlencode( sprintf( __( 'Item %1$d moved to Trash.', 'media-library-assistant' ), $_GET['ids'] ) );
			}

			wp_redirect( admin_url( 'upload.php' ) . $query_args, 302 );
			exit;
		}
	}

	/**
	 * Add the "XX Entries per page" filter to the Screen Options tab
	 *
	 * @since 0.1
	 *
	 * @return	void
	 */
	public static function mla_add_menu_options( ) {
		$option = 'per_page';

		$args = array(
			 'label' => __( 'Entries per page', 'media-library-assistant' ),
			'default' => 10,
			'option' => 'mla_entries_per_page' 
		);

		add_screen_option( $option, $args );
	}

	/**
	 * Add contextual help tabs to all the MLA pages
	 *
	 * @since 0.1
	 *
	 * @return	void
	 */
	public static function mla_add_help_tab( )
	{
		$screen = get_current_screen();
		/*
		 * Is this one of our pages?
		 */
		if ( !array_key_exists( $screen->id, self::$page_hooks ) ) {
			return;
		}

		if ( 'edit-tags' == $screen->base && 'attachment' != $screen->post_type ) {
			return;
		}

		$file_suffix = $screen->id;

		/*
		 * Override the screen suffix if we are going to display something other than the attachment table
		 */
		if ( isset( $_REQUEST['mla_admin_action'] ) ) {
			switch ( $_REQUEST['mla_admin_action'] ) {
				case self::MLA_ADMIN_SINGLE_EDIT_DISPLAY:
					$file_suffix = self::MLA_ADMIN_SINGLE_EDIT_DISPLAY;
					break;
			} // switch
		} else { // isset( $_REQUEST['mla_admin_action'] )
			/*
			 * Use a generic page for edit taxonomy screens
			 */
			if ( 't_' == substr( self::$page_hooks[ $file_suffix ], 0, 2 ) ) {
				$taxonomy = substr( self::$page_hooks[ $file_suffix ], 2 );
				switch ( $taxonomy ) {
					case 'attachment_category':
					case 'attachment_tag':
						break;
					default:
						$tax_object = get_taxonomy( $taxonomy );

						if ( $tax_object->hierarchical ) {
							$file_suffix = 'edit-hierarchical-taxonomy';
						} else {
							$file_suffix = 'edit-flat-taxonomy';
						}
				} // $taxonomy switch
			} // is taxonomy
		}

		$template_array = MLAData::mla_load_template( 'help-for-' . $file_suffix . '.tpl' );
		if ( empty( $template_array ) ) {
			return;
		}

		/*
		 * Don't add sidebar to the WordPress category and post_tag screens
		 */
		if ( ! ( 'edit-tags' == $screen->base && in_array( $screen->taxonomy, array( 'post_tag', 'category' ) ) ) ) {
			if ( !empty( $template_array['sidebar'] ) ) {
				$screen->set_help_sidebar( $template_array['sidebar'] );
			}
		}
		unset( $template_array['sidebar'] );

		/*
		 * Provide explicit control over tab order
		 */
		$tab_array = array();

		foreach ( $template_array as $id => $content ) {
			$match_count = preg_match( '#\<!-- title="(.+)" order="(.+)" --\>#', $content, $matches, PREG_OFFSET_CAPTURE );

			if ( $match_count > 0 ) {
				$tab_array[ $matches[ 2 ][ 0 ] ] = array(
					 'id' => $id,
					'title' => $matches[ 1 ][ 0 ],
					'content' => $content 
				);
			} else {
				/* translators: 1: function name 2: template key */
				error_log( sprintf( _x( 'ERROR: %1$s discarding "%2$s"; no title/order', 'error_log', 'media-library-assistant' ), 'mla_add_help_tab', $id ), 0 );
			}
		}

		ksort( $tab_array, SORT_NUMERIC );
		foreach ( $tab_array as $indx => $value ) {
			/*
			 * Don't add duplicate tabs to the WordPress category and post_tag screens
			 */
			if ( 'edit-tags' == $screen->base && in_array( $screen->taxonomy, array( 'post_tag', 'category' ) ) ) {
				if ( 'mla-attachments-column' != $value['id'] ) {
					continue;
				}
			}

			$screen->add_help_tab( $value );
		}
	}

	/**
	 * Only show screen options on the table-list screen
	 *
	 * @since 0.1
	 *
	 * @param	boolean	True to display "Screen Options", false to suppress them
	 * @param	string	Name of the page being loaded
	 *
	 * @return	boolean	True to display "Screen Options", false to suppress them
	 */
	public static function mla_screen_options_show_screen_filter( $show_screen, $this_screen ) {
		if ( isset( $_REQUEST['mla_admin_action'] ) && ( $_REQUEST['mla_admin_action'] == self::MLA_ADMIN_SINGLE_EDIT_DISPLAY ) ) {
			return false;
		}

		return $show_screen;
	}

	/**
	 * Save the "Entries per page" option set by this user
	 *
	 * @since 0.1
	 *
	 * @param	mixed	false or value returned by previous filter
	 * @param	string	Name of the option being changed
	 * @param	string	New value of the option
	 *
	 * @return	string|void	New value if this is our option, otherwise nothing
	 */
	public static function mla_set_screen_option_filter( $status, $option, $value )
	{
		if ( 'mla_entries_per_page' == $option ) {
			return $value;
		} elseif ( $status ) {
			return $status;
		}
	}

	/**
	 * Redirect to the Edit Tags/Categories page
	 *
	 * The custom taxonomy add/edit submenu entries go to "upload.php" by default.
	 * This filter is the only way to redirect them to the correct WordPress page.
	 * The filter is not required for WordPress 3.5 and later.
	 *
	 * @since 0.1
	 *
	 * @return	void
	 */
	public static function mla_edit_tax_redirect( )
	{
		/*
		 * WordPress 3.5 adds native support for taxonomies
		 */
		if ( MLATest::$wordpress_3point5_plus ) {
			return;
		}

		$screen = get_current_screen();

		if ( isset( $_REQUEST['page'] ) && ( substr( $_REQUEST['page'], 0, 13 ) == 'mla-edit-tax-' ) ) {
			$taxonomy = substr( $_REQUEST['page'], 13 );
			wp_redirect( admin_url( 'edit-tags.php?taxonomy=' . $taxonomy . '&post_type=attachment' ), 302 );
			exit;
		}
	}

	/**
	 * Cleanup menus for Edit Tags/Categories page
	 *
	 * For WordPress before 3.5, the submenu entries for custom taxonomies
	 * under the "Media" menu are not set up correctly by WordPress, so this
	 * function cleans them up, redirecting the request to the right WordPress
	 * page for editing/adding taxonomy terms.
	 * For WordPress 3.5 and later, the function fixes the submenu bolding when
	 * going to the Edit Media screen.
	 *
	 * @since 0.1
	 *
	 * @param	array	The top-level menu page
	 *
	 * @return	string	The updated top-level menu page
	 */
	public static function mla_parent_file_filter( $parent_file ) {
		global $submenu_file, $submenu, $hook_suffix;

		/*
		 * Make sure the "Assistant" submenu line is bolded if it's the default
		 */
		if ( 'media_page_' . self::ADMIN_PAGE_SLUG == $hook_suffix ) {
			$submenu_file = self::ADMIN_PAGE_SLUG;
		}

		/*
		 * Make sure the "Assistant" submenu line is bolded if the Media/Library submenu is hidden
		 */
		if ( 'checked' != MLAOptions::mla_get_option( MLAOptions::MLA_SCREEN_DISPLAY_LIBRARY ) &&
		     'upload.php' == $parent_file && 'upload.php' == $submenu_file ) {
			$submenu_file = self::ADMIN_PAGE_SLUG;
		}

		/*
		 * Make sure the "Assistant" submenu line is bolded when we go to the Edit Media page
		 */
		if ( isset( $_REQUEST['mla_source'] ) ) {
			$submenu_file = self::ADMIN_PAGE_SLUG;
		}

		/*
		 * WordPress 3.5 adds native support for taxonomies
		 */
		if ( MLATest::$wordpress_3point5_plus ) {
			return $parent_file;
		}

		if ( isset( $_REQUEST['taxonomy'] ) ) {
			$taxonomies = get_object_taxonomies( 'attachment', 'objects' );

			foreach ( $taxonomies as $tax_name => $tax_object ) {
				if ( $_REQUEST['taxonomy'] == $tax_name ) {
					$mla_page = 'mla-edit-tax-' . $tax_name;
					$real_page = 'edit-tags.php?taxonomy=' . $tax_name . '&post_type=attachment';

					foreach ( $submenu['upload.php'] as $submenu_index => $submenu_entry ) {
						if ( $submenu_entry[ 2 ] == $mla_page ) {
							$submenu['upload.php'][ $submenu_index ][ 2 ] = $real_page;
							return 'upload.php';
						}
					}
				}
			}
		}

		return $parent_file;
	}

	/**
	 * Render the "Assistant" subpage in the Media section, using the list_table package
	 *
	 * @since 0.1
	 *
	 * @return	void
	 */
	public static function mla_render_admin_page( ) {
		/*
		 * WordPress class-wp-list-table.php doesn't look in hidden fields to set
		 * the month filter dropdown or sorting parameters
		 */
		if ( isset( $_REQUEST['m'] ) ) {
			$_GET['m'] = $_REQUEST['m'];
		}

		if ( isset( $_REQUEST['order'] ) ) {
			$_GET['order'] = $_REQUEST['order'];
		}

		if ( isset( $_REQUEST['orderby'] ) ) {
			$_GET['orderby'] = $_REQUEST['orderby'];
		}

		$bulk_action = self::_current_bulk_action();

		$page_title = MLAOptions::mla_get_option( MLAOptions::MLA_SCREEN_PAGE_TITLE );
		echo "<div class=\"wrap\">\r\n";
		echo "<div id=\"icon-upload\" class=\"icon32\"><br/></div>\r\n";
		echo "<h2>{$page_title}"; // trailing </h2> is action-specific

		if ( !current_user_can( 'upload_files' ) ) {
			echo " - Error</h2>\r\n";
			wp_die( __( 'You do not have permission to manage attachments.', 'media-library-assistant' ) );
		}

		$page_content = array(
			'message' => '',
			'body' => '' 
		);

		if ( !empty( $_REQUEST['mla_admin_message'] ) ) {
			$page_content['message'] = $_REQUEST['mla_admin_message'];
		}

		/*
		 * The category taxonomy (edit screens) is a special case because 
		 * post_categories_meta_box() changes the input name
		 */
		if ( !isset( $_REQUEST['tax_input'] ) ) {
			$_REQUEST['tax_input'] = array();
		}

		if ( isset( $_REQUEST['post_category'] ) ) {
			$_REQUEST['tax_input']['category'] = $_REQUEST['post_category'];
			unset ( $_REQUEST['post_category'] );
		}

		/*
		 * Process bulk actions that affect an array of items
		 */
		if ( $bulk_action && ( $bulk_action != 'none' ) ) {

			if ( isset( $_REQUEST['cb_attachment'] ) ) {
				foreach ( $_REQUEST['cb_attachment'] as $index => $post_id ) {
					switch ( $bulk_action ) {
						case 'delete':
							$item_content = self::_delete_single_item( $post_id );
							break;
						case 'edit':
							if ( !empty( $_REQUEST['bulk_custom_field_map'] ) ) {
								$updates = MLAOptions::mla_evaluate_custom_field_mapping( $post_id, 'single_attachment_mapping' );
								$item_content = MLAData::mla_update_single_item( $post_id, $updates );
								break;
							}

							if ( !empty( $_REQUEST['bulk_map'] ) ) {
								$item = get_post( $post_id );
								$updates = MLAOptions::mla_evaluate_iptc_exif_mapping( $item, 'iptc_exif_mapping' );
								$item_content = MLAData::mla_update_single_item( $post_id, $updates );
								break;
							}

							/*
							 * Copy the edit form contents to $new_data
							 */
							$new_data = array() ;
							if ( isset( $_REQUEST['post_parent'] ) ) {
								if ( is_numeric( $_REQUEST['post_parent'] ) ) {
									$new_data['post_parent'] = $_REQUEST['post_parent'];
								}
							}

							if ( isset( $_REQUEST['post_author'] ) ) {
								if ( -1 != $_REQUEST['post_author'] ) {
										$new_data['post_author'] = $_REQUEST['post_author'];
								}
							}

							/*
							 * Custom field support
							 */
							$custom_fields = array();
							foreach (MLAOptions::mla_custom_field_support( 'bulk_edit' ) as $slug => $label ) {
								if ( isset( $_REQUEST[ $slug ] ) ) {
									if ( ! empty( $_REQUEST[ $slug ] ) ) {
										$custom_fields[ $label ] = $_REQUEST[ $slug ];
									}
								}
							} // foreach

							if ( ! empty( $custom_fields ) ) {
								$new_data[ 'custom_updates' ] = $custom_fields;
							}

							$item_content = MLAData::mla_update_single_item( $post_id, $new_data, $_REQUEST['tax_input'], $_REQUEST['tax_action'] );
							break;
						case 'restore':
							$item_content = self::_restore_single_item( $post_id );
							break;
						//case 'tag':
						case 'trash':
							$item_content = self::_trash_single_item( $post_id );
							break;
						default:
							$item_content = array(
								/* translators: 1: bulk_action, e.g., delete, edit, restore, trash */
								 'message' => sprintf( __( 'Unknown bulk action %1$s', 'media-library-assistant' ), $bulk_action ),
								'body' => '' 
							);
					} // switch $bulk_action

					$page_content['message'] .= $item_content['message'] . '<br>';
				} // foreach cb_attachment

				unset( $_REQUEST['post_parent'] );
				unset( $_REQUEST['post_author'] );
				unset( $_REQUEST['tax_input'] );
				unset( $_REQUEST['tax_action'] );

				foreach (MLAOptions::mla_custom_field_support( 'bulk_edit' ) as $slug => $label )
					unset( $_REQUEST[ $slug ] );

				unset( $_REQUEST['cb_attachment'] );
			} else { // isset cb_attachment
				/* translators: 1: action name, e.g., edit */
				$page_content['message'] = sprintf( __( 'Bulk Action %1$s - no items selected.', 'media-library-assistant' ), $bulk_action );
			}

			unset( $_REQUEST['action'] );
			unset( $_REQUEST['bulk_edit'] );
			unset( $_REQUEST['action2'] );
		} // $bulk_action

		if ( isset( $_REQUEST['clear_filter_by'] ) ) {
			unset( $_REQUEST['heading_suffix'] );
			unset( $_REQUEST['parent'] );
			unset( $_REQUEST['author'] );
			unset( $_REQUEST['mla-tax'] );
			unset( $_REQUEST['mla-term'] );
			unset( $_REQUEST['mla-metakey'] );
			unset( $_REQUEST['mla-metavalue'] );
		}

		if ( isset( $_REQUEST['delete_all'] ) ) {
			global $wpdb;

			$ids = $wpdb->get_col( $wpdb->prepare( "SELECT ID FROM $wpdb->posts WHERE post_type=%s AND post_status = %s", 'attachment', 'trash' ) );
			$delete_count = 0;
			foreach ( $ids as $post_id ) {
				$item_content = self::_delete_single_item( $post_id );

				if ( false !== strpos( $item_content['message'], __( 'ERROR:', 'media-library-assistant' ) ) ) {
					$page_content['message'] .= $item_content['message'] . '<br>';
				} else {
					$delete_count++;
				}
			}

			if ( $delete_count ) {
				$page_content['message'] .= sprintf( _nx( '%s item deleted.', '%s items deleted.', $delete_count, 'deleted items', 'media-library-assistant' ), number_format_i18n( $delete_count ) );
			} else {
				$page_content['message'] .= __( 'No items deleted.', 'media-library-assistant' );
			}
		}

		/*
		 * Process row-level actions that affect a single item
		 */
		if ( !empty( $_REQUEST['mla_admin_action'] ) ) {
			check_admin_referer( self::MLA_ADMIN_NONCE );

			switch ( $_REQUEST['mla_admin_action'] ) {
				case self::MLA_ADMIN_SINGLE_DELETE:
					$page_content = self::_delete_single_item( $_REQUEST['mla_item_ID'] );
					break;
				case self::MLA_ADMIN_SINGLE_EDIT_DISPLAY:
					echo ' - ' . __( 'Edit single item', 'media-library-assistant' ) . '</h2>';
					$page_content = self::_display_single_item( $_REQUEST['mla_item_ID'] );
					break;
				case self::MLA_ADMIN_SINGLE_EDIT_UPDATE:
					if ( !empty( $_REQUEST['update'] ) ) {
						$page_content = MLAData::mla_update_single_item( $_REQUEST['mla_item_ID'], $_REQUEST['attachments'][ $_REQUEST['mla_item_ID'] ], $_REQUEST['tax_input'] );
					} elseif ( !empty( $_REQUEST['map-iptc-exif'] ) ) {
						$item = get_post( $_REQUEST['mla_item_ID'] );
						$updates = MLAOptions::mla_evaluate_iptc_exif_mapping( $item, 'iptc_exif_mapping' );
						$page_content = MLAData::mla_update_single_item( $_REQUEST['mla_item_ID'], $updates );
					} else {
						$page_content = array(
							/* translators: 1: post ID */
							'message' => sprintf( __( 'Item %1$d cancelled.', 'media-library-assistant' ), $_REQUEST['mla_item_ID'] ),
							'body' => '' 
						);
					}
					break;
				case self::MLA_ADMIN_SINGLE_RESTORE:
					$page_content = self::_restore_single_item( $_REQUEST['mla_item_ID'] );
					break;
				case self::MLA_ADMIN_SINGLE_TRASH:
					$page_content = self::_trash_single_item( $_REQUEST['mla_item_ID'] );
					break;
				default:
					$page_content = array(
						/* translators: 1: bulk_action, e.g., single_item_delete, single_item_edit */
						 'message' => sprintf( __( 'Unknown mla_admin_action - "%1$s"', 'media-library-assistant' ), $_REQUEST['mla_admin_action'] ),
						'body' => '' 
					);
					break;
			} // switch ($_REQUEST['mla_admin_action'])
		} // (!empty($_REQUEST['mla_admin_action'])

		if ( !empty( $page_content['body'] ) ) {
			if ( !empty( $page_content['message'] ) ) {
				if ( false !== strpos( $page_content['message'], __( 'ERROR:', 'media-library-assistant' ) ) ) {
					$messages_class = 'mla_errors';
				} else {
					$messages_class = 'mla_messages';
				}

				echo "  <div class=\"{$messages_class}\"><p>\r\n";
				echo '    ' . $page_content['message'] . "\r\n";
				echo "  </p></div>\r\n"; // id="message"
			}

			echo $page_content['body'];
		} else {
			/*
			 * Display Attachments list
			 */
			if ( !empty( $_REQUEST['heading_suffix'] ) ) {
				echo ' - ' . esc_html( $_REQUEST['heading_suffix'] ) . "</h2>\r\n";
			} elseif ( !empty( $_REQUEST['s'] ) && !empty( $_REQUEST['mla_search_fields'] ) ) {
				echo ' - search results for "' . esc_html( stripslashes( trim( $_REQUEST['s'] ) ) ) . "\"</h2>\r\n";
			} else {
				echo "</h2>\r\n";
			}

			if ( !empty( $page_content['message'] ) ) {
				if ( false !== strpos( $page_content['message'], __( 'ERROR:', 'media-library-assistant' ) ) ) {
					$messages_class = 'mla_errors';
				} else {
					$messages_class = 'mla_messages';
				}

				echo "  <div class=\"{$messages_class}\"><p>\r\n";
				echo '    ' . $page_content['message'] . "\r\n";
				echo "  </p></div>\r\n"; // id="message"
			}

			/*
			 * Optional - limit width of the views list
			 */
			$view_width = MLAOptions::mla_get_option( MLAOptions::MLA_TABLE_VIEWS_WIDTH );
			if ( !empty( $view_width ) ) {
				if ( is_numeric( $view_width ) ) {
					$view_width .= 'px';
				}

				echo "  <style type='text/css'>\r\n";
				echo "    ul.subsubsub {\r\n";
				echo "      width: {$view_width};\r\n";
				echo "      max-width: {$view_width};\r\n";
				echo "    }\r\n";
				echo "  </style>\r\n";
			}

			//	Create an instance of our package class...
			$MLAListTable = new MLA_List_Table();

			//	Fetch, prepare, sort, and filter our data...
			$MLAListTable->prepare_items();
			$MLAListTable->views();

			//	 Forms are NOT created automatically, so you need to wrap the table in one to use features like bulk actions
//			echo '<form action="' . admin_url( 'upload.php' ) . '" method="get" id="mla-filter">' . "\r\n";
			echo '<form action="' . admin_url( 'upload.php?page=' . self::ADMIN_PAGE_SLUG ) . '" method="post" id="mla-filter">' . "\r\n";
//			echo '<form action="' . admin_url( 'upload.php?page=' . self::ADMIN_PAGE_SLUG ) . '" method="get" id="mla-filter">' . "\r\n";
			/*
			 * Compose the Search Media box
			 */
			if ( !empty( $_REQUEST['s'] ) && !empty( $_REQUEST['mla_search_fields'] ) ) {
				$search_value = esc_attr( stripslashes( trim( $_REQUEST['s'] ) ) );
				$search_fields = $_REQUEST['mla_search_fields'];
				$search_connector = $_REQUEST['mla_search_connector'];
			} else {
				$search_value = '';
				$search_fields = array ( 'title', 'content' );
				$search_connector = 'AND';
			}

			echo '<p class="search-box">' . "\r\n";
			echo '<label class="screen-reader-text" for="media-search-input">' . __( 'Search Media', 'media-library-assistant' ) . ':</label>' . "\r\n";
			echo '<input type="text" size="45"  id="media-search-input" name="s" value="' . $search_value . '" />' . "\r\n";
			echo '<input type="submit" name="mla-search-submit" id="search-submit" class="button" value="Search Media"  /><br>' . "\r\n";
			if ( 'OR' == $search_connector ) {
				echo '<input type="radio" name="mla_search_connector" value="AND" />&nbsp;' . __( 'and', 'media-library-assistant' ) . "&nbsp;\r\n";
				echo '<input type="radio" name="mla_search_connector" checked="checked" value="OR" />&nbsp;' . __( 'or', 'media-library-assistant' ) . "&nbsp;\r\n";
			} else {
				echo '<input type="radio" name="mla_search_connector" checked="checked" value="AND" />&nbsp;' . __( 'and', 'media-library-assistant' ) . "&nbsp;\r\n";
				echo '<input type="radio" name="mla_search_connector" value="OR" />&nbsp;' . __( 'or', 'media-library-assistant' ) . "&nbsp;\r\n";
			}

			if ( in_array( 'title', $search_fields ) ) {
				echo '<input type="checkbox" name="mla_search_fields[]" id="search-title" checked="checked" value="title" />&nbsp;' . __( 'Title', 'media-library-assistant' ) . "&nbsp;\r\n";
			} else {
				echo '<input type="checkbox" name="mla_search_fields[]" id="search-title" value="title" />&nbsp;' . __( 'Title', 'media-library-assistant' ) . "&nbsp;\r\n";
			}

			if ( in_array( 'name', $search_fields ) ) {
				echo '<input type="checkbox" name="mla_search_fields[]" id="search-name" checked="checked" value="name" />&nbsp;' . __( 'Name', 'media-library-assistant' ) . "&nbsp;\r\n";
			} else {
				echo '<input type="checkbox" name="mla_search_fields[]" id="search-name" value="name" />&nbsp;' . __( 'Name', 'media-library-assistant' ) . "&nbsp;\r\n";
			}

			if ( in_array( 'alt-text', $search_fields ) ) {
				echo '<input type="checkbox" name="mla_search_fields[]" id="search-alt-text" checked="checked" value="alt-text" />&nbsp;' . __( 'ALT Text', 'media-library-assistant' ) . "&nbsp;\r\n";
			} else {
				echo '<input type="checkbox" name="mla_search_fields[]" id="search-alt-text" value="alt-text" />&nbsp;' . __( 'ALT Text', 'media-library-assistant' ) . "&nbsp;\r\n";
			}

			if ( in_array( 'excerpt', $search_fields ) ) {
				echo '<input type="checkbox" name="mla_search_fields[]" id="search-excerpt" checked="checked" value="excerpt" />&nbsp;' . __( 'Caption', 'media-library-assistant' ) . "&nbsp;\r\n";
			} else {
				echo '<input type="checkbox" name="mla_search_fields[]" id="search-excerpt" value="excerpt" />&nbsp;' . __( 'Caption', 'media-library-assistant' ) . "&nbsp;\r\n";
			}

			if ( in_array( 'content', $search_fields ) ) {
				echo '<input type="checkbox" name="mla_search_fields[]" id="search-content" checked="checked" value="content" />&nbsp;' . __( 'Description', 'media-library-assistant' ) . "&nbsp;\r\n";
			} else {
				echo '<input type="checkbox" name="mla_search_fields[]" id="search-content" value="content" />&nbsp;' . __( 'Description', 'media-library-assistant' ) . "&nbsp;\r\n";
			}

			echo '</p>' . "\r\n";

			/*
			 * We also need to ensure that the form posts back to our current page and remember all the view arguments
			 */
			echo sprintf( '<input type="hidden" name="page" value="%1$s" />', $_REQUEST['page'] ) . "\r\n";

			$view_arguments = MLA_List_Table::mla_submenu_arguments();
			foreach ( $view_arguments as $key => $value ) {
				if ( 'meta_query' == $key ) {
					$value = stripslashes( $_REQUEST['meta_query'] );
				}

				/*
				 * Search box elements are already set up in the above "search-box"
				 */
				if ( in_array( $key, array( 's', 'mla_search_connector', 'mla_search_fields' ) ) ) {
					continue;
				}

				if ( is_array( $value ) ) {
					foreach ( $value as $element_key => $element_value )
						echo sprintf( '<input type="hidden" name="%1$s[%2$s]" value="%3$s" />', $key, $element_key, esc_attr( $element_value ) ) . "\r\n";
				} else {
					echo sprintf( '<input type="hidden" name="%1$s" value="%2$s" />', $key, esc_attr( $value ) ) . "\r\n";
				}
			}

			//	 Now we can render the completed list table
			$MLAListTable->display();
			echo "</form><!-- id=mla-filter -->\r\n";

			/*
			 * Insert the hidden form and table for inline edits (quick & bulk)
			 */
			echo self::_build_inline_edit_form($MLAListTable);

			echo "<div id=\"ajax-response\"></div>\r\n";
			echo "<br class=\"clear\" />\r\n";
			echo "</div><!-- class=wrap -->\r\n";
		} // display attachments list
	}

	/**
	 * Ajax handler for inline editing (quick and bulk edit)
	 *
	 * Adapted from wp_ajax_inline_save in /wp-admin/includes/ajax-actions.php
	 *
	 * @since 0.20
	 *
	 * @return	void	echo HTML <tr> markup for updated row or error message, then die()
	 */
	public static function mla_inline_edit_action() {
		set_current_screen( $_REQUEST['screen'] );

		check_ajax_referer( self::MLA_ADMIN_NONCE, 'nonce' );

		if ( empty( $_REQUEST['post_ID'] ) ) {
			echo __( 'ERROR: No post ID found', 'media-library-assistant' );
			die();
		} else {
			$post_id = $_REQUEST['post_ID'];
		}

		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			wp_die( __( 'You are not allowed to edit this Attachment.', 'media-library-assistant' ) );
		}

		/*
		 * Custom field support
		 */
		$custom_fields = array();
		foreach (MLAOptions::mla_custom_field_support( 'quick_edit' ) as $slug => $label ) {
			if ( isset( $_REQUEST[ $slug ] ) ) {
				$custom_fields[ $label ] = $_REQUEST[ $slug ];
				unset ( $_REQUEST[ $slug ] );
			  }
		}

		if ( ! empty( $custom_fields ) ) {
			$_REQUEST[ 'custom_updates' ] = $custom_fields;
		}

		/*
		 * The category taxonomy is a special case because post_categories_meta_box() changes the input name
		 */
		if ( !isset( $_REQUEST['tax_input'] ) ) {
			$_REQUEST['tax_input'] = array();
		}

		if ( isset( $_REQUEST['post_category'] ) ) {
			$_REQUEST['tax_input']['category'] = $_REQUEST['post_category'];
			unset ( $_REQUEST['post_category'] );
		}

		if ( ! empty( $_REQUEST['tax_input'] ) ) {
			/*
			 * Flat taxonomy strings must be cleaned up and duplicates removed
			 */
			$tax_output = array();
			$tax_input = $_REQUEST['tax_input'];
			foreach ( $tax_input as $tax_name => $tax_value ) {
				if ( ! is_array( $tax_value ) ) {
					$comma = _x( ',', 'tag_delimiter', 'media-library-assistant' );
					if ( ',' != $comma ) {
						$tax_value = str_replace( $comma, ',', $tax_value );
					}

					$tax_value = preg_replace( '#\s*,\s*#', ',', $tax_value );
					$tax_value = preg_replace( '#,+#', ',', $tax_value );
					$tax_value = preg_replace( '#[,\s]+$#', '', $tax_value );
					$tax_value = preg_replace( '#^[,\s]+#', '', $tax_value );

					if ( ',' != $comma ) {
						$tax_value = str_replace( ',', $comma, $tax_value );
					}

					$tax_array = array();
					$dedup_array = explode( $comma, $tax_value );
					foreach ( $dedup_array as $tax_value )
						$tax_array [$tax_value] = $tax_value;

					$tax_value = implode( $comma, $tax_array );
				} // ! array( $tax_value )

				$tax_output[$tax_name] = $tax_value;
			} // foreach $tax_input
		} else { // ! empty( $_REQUEST['tax_input'] )
			$tax_output = NULL;
		}

		$results = MLAData::mla_update_single_item( $post_id, $_REQUEST, $tax_output );
		$new_item = (object) MLAData::mla_get_attachment_by_id( $post_id );

		//	Create an instance of our package class and echo the new HTML
		$MLAListTable = new MLA_List_Table();
		$MLAListTable->single_row( $new_item );
		die(); // this is required to return a proper result
	}

	/**
	 * Build the hidden row templates for inline editing (quick and bulk edit)
	 *
	 * inspired by inline_edit() in wp-admin\includes\class-wp-posts-list-table.php.
	 *
	 * @since 0.20
	 *
	 * @param	object	MLA List Table object
	 *
	 * @return	string	HTML <form> markup for hidden rows
	 */
	private static function _build_inline_edit_form( $MLAListTable ) {
		$taxonomies = get_object_taxonomies( 'attachment', 'objects' );

		$hierarchical_taxonomies = array();
		$flat_taxonomies = array();
		foreach ( $taxonomies as $tax_name => $tax_object ) {
			if ( $tax_object->hierarchical && $tax_object->show_ui && MLAOptions::mla_taxonomy_support($tax_name, 'quick-edit') ) {
				$hierarchical_taxonomies[$tax_name] = $tax_object;
			} elseif ( $tax_object->show_ui && MLAOptions::mla_taxonomy_support($tax_name, 'quick-edit') ) {
				$flat_taxonomies[$tax_name] = $tax_object;
			}
		}

		$page_template_array = MLAData::mla_load_template( 'admin-inline-edit-form.tpl' );
		if ( ! array( $page_template_array ) ) {
			/* translators: 1: function name 2: non-array value */
			error_log( sprintf( _x( 'ERROR: %1$s non-array "%2$s"', 'error_log', 'media-library-assistant' ), 'MLA::_build_inline_edit_form', var_export( $page_template_array, true ) ), 0 );
			return '';
		}

		if ( $authors = self::_authors_dropdown() ) {
			$authors_dropdown  = '              <label class="inline-edit-author">' . "\r\n";
			$authors_dropdown .= '                <span class="title">' . __( 'Author', 'media-library-assistant' ) . '</span>' . "\r\n";
			$authors_dropdown .= $authors . "\r\n";
			$authors_dropdown .= '              </label>' . "\r\n";
		} else {
			$authors_dropdown = '';
		}

		$custom_fields = '';
		foreach (MLAOptions::mla_custom_field_support( 'quick_edit' ) as $slug => $label ) {
			  $page_values = array(
				  'slug' => $slug,
				  'label' => esc_attr( $label ),
			  );
			  $custom_fields .= MLAData::mla_parse_template( $page_template_array['custom_field'], $page_values );
		}

		/*
		 * The middle column contains the hierarchical taxonomies, e.g., Attachment Category
		 */
		$quick_middle_column = '';
		$bulk_middle_column = '';

		if ( count( $hierarchical_taxonomies ) ) {
			$quick_category_blocks = '';
			$bulk_category_blocks = '';

			foreach ( $hierarchical_taxonomies as $tax_name => $tax_object ) {
				if ( current_user_can( $tax_object->cap->assign_terms ) ) {
				  ob_start();
				  wp_terms_checklist( NULL, array( 'taxonomy' => $tax_name ) );
				  $tax_checklist = ob_get_contents();
				  ob_end_clean();
  
				  $page_values = array(
					  'tax_html' => esc_html( $tax_object->labels->name ),
					  'more' => __( 'more', 'media-library-assistant' ),
					  'less' => __( 'less', 'media-library-assistant' ),
					  'tax_attr' => esc_attr( $tax_name ),
					  'tax_checklist' => $tax_checklist,
					  'Add' => __( 'Add', 'media-library-assistant' ),
					  'Remove' => __( 'Remove', 'media-library-assistant' ),
					  'Replace' => __( 'Replace', 'media-library-assistant' ),
				  );
				  $category_block = MLAData::mla_parse_template( $page_template_array['category_block'], $page_values );
				  $taxonomy_options = MLAData::mla_parse_template( $page_template_array['taxonomy_options'], $page_values );
  
				  $quick_category_blocks .= $category_block;
				  $bulk_category_blocks .= $category_block . $taxonomy_options;
				} // current_user_can
			} // foreach $hierarchical_taxonomies

			$page_values = array(
				'category_blocks' => $quick_category_blocks
			);
			$quick_middle_column = MLAData::mla_parse_template( $page_template_array['category_fieldset'], $page_values );

			$page_values = array(
				'category_blocks' => $bulk_category_blocks
			);
			$bulk_middle_column = MLAData::mla_parse_template( $page_template_array['category_fieldset'], $page_values );
		} // count( $hierarchical_taxonomies )

		/*
		 * The right-hand column contains the flat taxonomies, e.g., Attachment Tag
		 */
		$quick_right_column = '';
		$bulk_right_column = '';

		if ( count( $flat_taxonomies ) ) {
			$quick_tag_blocks = '';
			$bulk_tag_blocks = '';

			foreach ( $flat_taxonomies as $tax_name => $tax_object ) {
				if ( current_user_can( $tax_object->cap->assign_terms ) ) {
					$page_values = array(
						'tax_html' => esc_html( $tax_object->labels->name ),
						'tax_attr' => esc_attr( $tax_name ),
						'Add' => __( 'Add', 'media-library-assistant' ),
						'Remove' => __( 'Remove', 'media-library-assistant' ),
						'Replace' => __( 'Replace', 'media-library-assistant' ),
					);
					$tag_block = MLAData::mla_parse_template( $page_template_array['tag_block'], $page_values );
					$taxonomy_options = MLAData::mla_parse_template( $page_template_array['taxonomy_options'], $page_values );

				$quick_tag_blocks .= $tag_block;
				$bulk_tag_blocks .= $tag_block . $taxonomy_options;
				} // current_user_can
			} // foreach $flat_taxonomies

			$page_values = array(
				'tag_blocks' => $quick_tag_blocks
			);
			$quick_right_column = MLAData::mla_parse_template( $page_template_array['tag_fieldset'], $page_values );

			$page_values = array(
				'tag_blocks' => $bulk_tag_blocks
			);
			$bulk_right_column = MLAData::mla_parse_template( $page_template_array['tag_fieldset'], $page_values );
		} // count( $flat_taxonomies )

		if ( $authors = self::_authors_dropdown( -1 ) ) {
			$bulk_authors_dropdown  = '              <label class="inline-edit-author">' . "\r\n";
			$bulk_authors_dropdown .= '                <span class="title">' . __( 'Author', 'media-library-assistant' ) . '</span>' . "\r\n";
			$bulk_authors_dropdown .= $authors . "\r\n";
			$bulk_authors_dropdown .= '              </label>' . "\r\n";
		} else {
			$bulk_authors_dropdown = '';
		}

		$bulk_custom_fields = '';
		foreach (MLAOptions::mla_custom_field_support( 'bulk_edit' ) as $slug => $label ) {
			  $page_values = array(
				  'slug' => $slug,
				  'label' => esc_attr( $label ),
			  );
			  $bulk_custom_fields .= MLAData::mla_parse_template( $page_template_array['custom_field'], $page_values );
		}

		$page_values = array(
			'colspan' => count( $MLAListTable->get_columns() ),
			'Quick Edit' => __( 'Quick Edit', 'media-library-assistant' ),
			'Title' => __( 'Title', 'media-library-assistant' ),
			'Name/Slug' => __( 'Name/Slug', 'media-library-assistant' ),
			'Caption' => __( 'Caption', 'media-library-assistant' ),
			'Description' => __( 'Description', 'media-library-assistant' ),
			'ALT Text' => __( 'ALT Text', 'media-library-assistant' ),
			'Parent ID' => __( 'Parent ID', 'media-library-assistant' ),
			'Menu Order' => __( 'Menu Order', 'media-library-assistant' ),
			'authors' => $authors_dropdown,
			'custom_fields' => $custom_fields,
			'quick_middle_column' => $quick_middle_column,
			'quick_right_column' => $quick_right_column,
			'Cancel' => __( 'Cancel', 'media-library-assistant' ),
			'Update' => __( 'Update', 'media-library-assistant' ),
			'Bulk Edit' => __( 'Bulk Edit', 'media-library-assistant' ),
			'bulk_middle_column' => $bulk_middle_column,
			'bulk_right_column' => $bulk_right_column,
			'bulk_authors' => $bulk_authors_dropdown,
			'bulk_custom_fields' => $bulk_custom_fields,
			'Map IPTC/EXIF metadata' =>  __( 'Map IPTC/EXIF metadata', 'media-library-assistant' ),
			'Map Custom Field metadata' =>  __( 'Map Custom Field Metadata', 'media-library-assistant' ),
		);
		$page_template = MLAData::mla_parse_template( $page_template_array['page'], $page_values );
		return $page_template;
	}

	/**
	 * Get the edit Authors dropdown box, if user has suitable permissions
	 *
	 * @since 0.20
	 *
	 * @param	integer	Optional User ID of the current author, default 0
	 * @param	string	Optional HTML name attribute, default 'post_author'
	 * @param	string	Optional HTML class attribute, default 'authors'
	 *
	 * @return string|false HTML markup for the dropdown field or False
	 */
	private static function _authors_dropdown( $author = 0, $name = 'post_author', $class = 'authors' ) {
		$post_type_object = get_post_type_object('attachment');
		if ( is_super_admin() || current_user_can( $post_type_object->cap->edit_others_posts ) ) {
			$users_opt = array(
				'hide_if_only_one_author' => false,
				'who' => 'authors',
				'name' => $name,
				'class'=> $class,
				'multi' => 1,
				'echo' => 0
			);

			if ( $author > 0 ) {
				$users_opt['selected'] = $author;
				$users_opt['include_selected'] = true;
			} elseif ( -1 == $author ) {
				$users_opt['show_option_none'] = '&mdash; ' . __( 'No Change', 'media-library-assistant' ) . ' &mdash;';
			}

			if ( $authors = wp_dropdown_users( $users_opt ) ) {
				return $authors;
			}
		}

		return false;
	}

	/**
	 * Get the current action selected from the bulk actions dropdown
	 *
	 * @since 0.1
	 *
	 * @return string|false The action name or False if no action was selected
	 */
	private static function _current_bulk_action( )	{
		$action = false;

		if ( isset( $_REQUEST['action'] ) ) {
			if ( -1 != $_REQUEST['action'] ) {
				return $_REQUEST['action'];
			} else {
				$action = 'none';
			}
		} // isset action

		if ( isset( $_REQUEST['action2'] ) ) {
			if ( -1 != $_REQUEST['action2'] ) {
				return $_REQUEST['action2'];
			} else {
				$action = 'none';
			}
		} // isset action2

		return $action;
	}

	/**
	 * Delete a single item permanently
	 * 
	 * @since 0.1
	 * 
	 * @param	array The form POST data
	 *
	 * @return	array success/failure message and NULL content
	 */
	private static function _delete_single_item( $post_id ) {
		if ( !current_user_can( 'delete_post', $post_id ) ) {
			return array(
				'message' => __( 'ERROR: You are not allowed to delete this item.', 'media-library-assistant' ),
				'body' => '' 
			);
		}

		if ( !wp_delete_attachment( $post_id, true ) ) {
			return array(
				/* translators: 1: post ID */
				'message' => sprintf( __( 'ERROR: Item %1$d could NOT be deleted.', 'media-library-assistant' ), $post_id ),
				'body' => '' 
			);
		}

		return array(
			/* translators: 1: post ID */
			'message' => sprintf( __( 'Item %1$d permanently deleted.', 'media-library-assistant' ), $post_id ),
			'body' => '' 
		);
	}

	/**
	 * Display a single item sub page; prepare the form to 
	 * change the meta data for a single attachment.
	 * 
	 * This function is not used in WordPress 3.5 and later.
	 *
	 * @since 0.1
	 * 
	 * @param	int		The WordPress Post ID of the attachment item
	 *
	 * @return	array	message and/or HTML content
	 */
	private static function _display_single_item( $post_id ) {
		global $post;

		/*
		 * This function sets the global $post
		 */
		$post_data = MLAData::mla_get_attachment_by_id( $post_id );
		if ( !isset( $post_data ) ) {
			return array(
				'message' => __( 'ERROR: Could not retrieve Attachment.', 'media-library-assistant' ),
				'body' => '' 
			);
		}

		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return array(
				'message' => __( 'You are not allowed to edit this Attachment.', 'media-library-assistant' ),
				'body' => '' 
			);
		}

		if ( 0 === strpos( strtolower( $post_data['post_mime_type'] ), 'image' )  ) {
			$page_template_array = MLAData::mla_load_template( 'admin-display-single-image.tpl' );
			$width = isset( $post_data['mla_wp_attachment_metadata']['width'] ) ? $post_data['mla_wp_attachment_metadata']['width'] : '';
			$height = isset( $post_data['mla_wp_attachment_metadata']['height'] ) ? $post_data['mla_wp_attachment_metadata']['height'] : '';
			$image_meta = var_export( $post_data['mla_wp_attachment_metadata'], true );

			if ( !isset( $post_data['mla_wp_attachment_image_alt'] ) ) {
				$post_data['mla_wp_attachment_image_alt'] = '';
			}
		} else {
			$page_template_array = MLAData::mla_load_template( 'admin-display-single-document.tpl' );
			$width = '';
			$height = '';
			$image_meta = '';
		}

		if ( array( $page_template_array ) ) {
			$page_template = $page_template_array['page'];
			$authors_template = $page_template_array['authors'];
			$postbox_template = $page_template_array['postbox'];
		} else {
			/* translators: 1: page_template_array */
			error_log( sprintf( _x( 'ERROR: MLA::_display_single_item \$page_template_array = "%1$s"', 'error_log', 'media-library-assistant' ), var_export( $page_template_array, true ) ), 0 );
			$page_template = $page_template_array;
			$authors_template = '';
			$postbox_template = '';
		}

		if ( empty($post_data['mla_references']['parent_title'] ) ) {
			$parent_info = $post_data['mla_references']['parent_errors'];
		} else {
			$parent_info = sprintf( '(%1$s) %2$s %3$s', $post_data['mla_references']['parent_type'], $post_data['mla_references']['parent_title'], $post_data['mla_references']['parent_errors'] );
		}

		if ( $authors = self::_authors_dropdown( $post_data['post_author'], 'attachments[' . $post_data['ID'] . '][post_author]' ) ) {
			$args = array (
				'ID' => $post_data['ID'],
				'Author' => __( 'Author', 'media-library-assistant' ),
				'authors' => $authors
				);
			$authors = MLAData::mla_parse_template( $authors_template, $args );
		} else {
			$authors = '';
		}

		if ( MLAOptions::$process_featured_in ) {
			$features = '';

			foreach ( $post_data['mla_references']['features'] as $feature_id => $feature ) {
				if ( $feature_id == $post_data['post_parent'] ) {
					$parent = __( 'PARENT', 'media-library-assistant' ) . ' ';
				} else {
					$parent = '';
				}

				$features .= sprintf( '%1$s (%2$s %3$s), %4$s', /*$1%s*/ $parent, /*$2%s*/ $feature->post_type, /*$3%s*/ $feature_id, /*$4%s*/ $feature->post_title ) . "\r\n";
			} // foreach $feature
		} else {
			$features = __( 'Disabled', 'media-library-assistant' );
		}

		if ( MLAOptions::$process_inserted_in ) {
			$inserts = '';

			foreach ( $post_data['mla_references']['inserts'] as $file => $insert_array ) {
				$inserts .= $file . "\r\n";

				foreach ( $insert_array as $insert ) {
					if ( $insert->ID == $post_data['post_parent'] ) {
						$parent = '  ' . __( 'PARENT', 'media-library-assistant' ) . ' ';
					} else {
						$parent = '  ';
					}

					$inserts .= sprintf( '%1$s (%2$s %3$s), %4$s', /*$1%s*/ $parent, /*$2%s*/ $insert->post_type, /*$3%s*/ $insert->ID, /*$4%s*/ $insert->post_title ) . "\r\n";
				} // foreach $insert
			} // foreach $file
		} else {
			$inserts = __( 'Disabled', 'media-library-assistant' );
		}

		if ( MLAOptions::$process_gallery_in ) {
			$galleries = '';

			foreach ( $post_data['mla_references']['galleries'] as $gallery_id => $gallery ) {
				if ( $gallery_id == $post_data['post_parent'] ) {
					$parent = __( 'PARENT', 'media-library-assistant' ) . ' ';
				} else {
					$parent = '';
				}

				$galleries .= sprintf( '%1$s (%2$s %3$s), %4$s', /*$1%s*/ $parent, /*$2%s*/ $gallery['post_type'], /*$3%s*/ $gallery_id, /*$4%s*/ $gallery['post_title'] ) . "\r\n";
			} // foreach $gallery
		} else {
			$galleries = __( 'Disabled', 'media-library-assistant' );
		}

		if ( MLAOptions::$process_mla_gallery_in ) {
			$mla_galleries = '';

			foreach ( $post_data['mla_references']['mla_galleries'] as $gallery_id => $gallery ) {
				if ( $gallery_id == $post_data['post_parent'] ) {
					$parent = __( 'PARENT', 'media-library-assistant' ) . ' ';
				} else {
					$parent = '';
				}

				$mla_galleries .= sprintf( '%1$s (%2$s %3$s), %4$s', /*$1%s*/ $parent, /*$2%s*/ $gallery['post_type'], /*$3%s*/ $gallery_id, /*$4%s*/ $gallery['post_title'] ) . "\r\n";
			} // foreach $gallery
		} else {
			$mla_galleries = __( 'Disabled', 'media-library-assistant' );
		}

		/*
		 * WordPress doesn't look in hidden fields to set the month filter dropdown or sorting parameters
		 */
		if ( isset( $_REQUEST['m'] ) ) {
			$url_args = '&m=' . $_REQUEST['m'];
		} else {
			$url_args = '';
		}

		if ( isset( $_REQUEST['post_mime_type'] ) ) {
			$url_args .= '&post_mime_type=' . $_REQUEST['post_mime_type'];
		}

		if ( isset( $_REQUEST['order'] ) ) {
			$url_args .= '&order=' . $_REQUEST['order'];
		}

		if ( isset( $_REQUEST['orderby'] ) ) {
			$url_args .= '&orderby=' . $_REQUEST['orderby'];
		}

		/*
		 * Add the current view arguments
		 */
		if ( isset( $_REQUEST['detached'] ) ) {
			$view_args = '<input type="hidden" name="detached" value="' . $_REQUEST['detached'] . "\" />\r\n";
		} elseif ( isset( $_REQUEST['status'] ) ) {
			$view_args = '<input type="hidden" name="status" value="' . $_REQUEST['status'] . "\" />\r\n";
		} else {
			$view_args = '';
		}

		if ( isset( $_REQUEST['paged'] ) ) {
			$view_args .= sprintf( '<input type="hidden" name="paged" value="%1$s" />', $_REQUEST['paged'] ) . "\r\n";
		}

		$side_info_column = '';
		$taxonomies = get_object_taxonomies( 'attachment', 'objects' );

		foreach ( $taxonomies as $tax_name => $tax_object ) {
			ob_start();

			if ( $tax_object->hierarchical && $tax_object->show_ui ) {
				$box = array(
					 'id' => $tax_name . 'div',
					'title' => esc_html( $tax_object->labels->name ),
					'callback' => 'categories_meta_box',
					'args' => array(
						 'taxonomy' => $tax_name 
					),
					'inside_html' => '' 
				);
				post_categories_meta_box( $post, $box );
			} elseif ( $tax_object->show_ui ) {
				$box = array(
					 'id' => 'tagsdiv-' . $tax_name,
					'title' => esc_html( $tax_object->labels->name ),
					'callback' => 'post_tags_meta_box',
					'args' => array(
						 'taxonomy' => $tax_name 
					),
					'inside_html' => '' 
				);
				post_tags_meta_box( $post, $box );
			}

			$box['inside_html'] = ob_get_contents();
			ob_end_clean();
			$side_info_column .= MLAData::mla_parse_template( $postbox_template, $box );
		}

		$page_values = array(
			'form_url' => admin_url( 'upload.php' ) . '?page=' . self::ADMIN_PAGE_SLUG . $url_args,
			'ID' => $post_data['ID'],
			'post_mime_type' => $post_data['post_mime_type'],
			'menu_order' => $post_data['menu_order'],
			'mla_admin_action' => self::MLA_ADMIN_SINGLE_EDIT_UPDATE,
			'view_args' => $view_args,
			'wpnonce' => wp_nonce_field( self::MLA_ADMIN_NONCE, '_wpnonce', true, false ),
			'Cancel' => __( 'Cancel', 'media-library-assistant' ),
			'Update' => __( 'Update', 'media-library-assistant' ),
			'Map IPTC/EXIF metadata' =>  __( 'Map IPTC/EXIF metadata', 'media-library-assistant' ),
			'attachment_icon' => wp_get_attachment_image( $post_id, array( 160, 120 ), true ),
			'File name' => __( 'File name', 'media-library-assistant' ),
			'file_name' => esc_html( $post_data['mla_references']['file'] ),
			'File type' => __( 'File type', 'media-library-assistant' ),
			'Upload date' => __( 'Upload date', 'media-library-assistant' ),
			'post_date' => $post_data['post_date'],
			'Last modified' => __( 'Last modified', 'media-library-assistant' ),
			'post_modified' => $post_data['post_modified'],
			'Dimensions' => __( 'Dimensions', 'media-library-assistant' ),
			'width' => $width,
			'height' => $height,
			'Title' => __( 'Title', 'media-library-assistant' ),
			'required' => __( 'required', 'media-library-assistant' ),
			'post_title_attr' => esc_attr( $post_data['post_title'] ),
			'Name/Slug' => __( 'Name/Slug', 'media-library-assistant' ),
			'post_name_attr' => esc_attr( $post_data['post_name'] ),
			'Must be unique' => __( 'Must be unique; will be validated.', 'media-library-assistant' ),
			'ALT Text' => __( 'ALT Text', 'media-library-assistant' ),
			'image_alt_attr' => '',
			'ALT Text Help' => __( 'Alternate text for the image, e.g. &#8220;The Mona Lisa&#8221;', 'media-library-assistant' ),
			'Caption' => __( 'Caption', 'media-library-assistant' ),
			'post_excerpt_attr' => esc_attr( $post_data['post_excerpt'] ),
			'Description' => __( 'Description', 'media-library-assistant' ),
			'post_content' => esc_textarea( $post_data['post_content'] ),
			'Parent Info' => __( 'Parent Info', 'media-library-assistant' ),
			'post_parent' => $post_data['post_parent'],
			'parent_info' => esc_attr( $parent_info ),
			'Parent Info Help' => __( 'ID, type and title of parent, if any.', 'media-library-assistant' ),
			'Menu Order' => __( 'Menu Order', 'media-library-assistant' ),
			'authors' => $authors,
			'File URL' => __( 'File URL', 'media-library-assistant' ),
			'guid_attr' => esc_attr( $post_data['guid'] ),
			'File URL Help' => __( 'Location of the uploaded file.', 'media-library-assistant' ),
			'Image Metadata' => __( 'Image Metadata', 'media-library-assistant' ),
			'image_meta' => esc_textarea( $image_meta ),
			'Featured in' => __( 'Featured in', 'media-library-assistant' ),
			'features' => esc_textarea( $features ),
			'Inserted in' => __( 'Inserted in', 'media-library-assistant' ),
			'inserts' => esc_textarea( $inserts ),
			'Gallery in' => __( 'Gallery in', 'media-library-assistant' ),
			'galleries' => esc_textarea( $galleries ),
			'MLA Gallery in' => __( 'MLA Gallery in', 'media-library-assistant' ),
			'mla_galleries' => esc_textarea( $mla_galleries ),
			'side_info_column' => $side_info_column 
		);

		if ( !empty( $post_data['mla_wp_attachment_image_alt'] ) ) {
			$page_values['image_alt_attr'] = esc_attr( $post_data['mla_wp_attachment_image_alt'] );
		}

		return array(
			'message' => '',
			'body' => MLAData::mla_parse_template( $page_template, $page_values ) 
		);
	}

	/**
	 * Restore a single item from the Trash
	 * 
	 * @since 0.1
	 * 
	 * @param	array	The form POST data
	 *
	 * @return	array	success/failure message and NULL content
	 */
	private static function _restore_single_item( $post_id ) {
		if ( !current_user_can( 'delete_post', $post_id ) ) {
			return array(
				'message' => __( 'ERROR: You are not allowed to move this item out of the Trash.', 'media-library-assistant' ),
				'body' => '' 
			);
		}

		if ( !wp_untrash_post( $post_id ) ) {
			return array(
				/* translators: 1: post ID */
				'message' => sprintf( __( 'ERROR: Item %1$d could NOT be restored from Trash.', 'media-library-assistant' ), $post_id ),
				'body' => '' 
			);
		}

		/*
		 * Posts are restored to "draft" status, so this must be updated.
		 */
		$update_post = array();
		$update_post['ID'] = $post_id;
		$update_post['post_status'] = 'inherit';
		wp_update_post( $update_post );

		return array(
			/* translators: 1: post ID */
			'message' => sprintf( __( 'Item %1$d restored from Trash.', 'media-library-assistant' ), $post_id ),
			'body' => '' 
		);
	}

	/**
	 * Move a single item to Trash
	 * 
	 * @since 0.1
	 * 
	 * @param	array	The form POST data
	 *
	 * @return	array	success/failure message and NULL content
	 */
	private static function _trash_single_item( $post_id ) {
		if ( !current_user_can( 'delete_post', $post_id ) ) {
			return array(
				'message' => __( 'ERROR: You are not allowed to move this item to the Trash.', 'media-library-assistant' ),
				'body' => '' 
			);
		}

		if ( !wp_trash_post( $post_id, false ) ) {
			return array(
				/* translators: 1: post ID */
				'message' => sprintf( __( 'ERROR: Item %1$d could NOT be moved to Trash.', 'media-library-assistant' ), $post_id ),
				'body' => '' 
			);
		}

		return array(
			/* translators: 1: post ID */
			'message' => sprintf( __( 'Item %1$d moved to Trash.', 'media-library-assistant' ), $post_id ),
			'body' => '' 
		);
	}
} // class MLA
?>