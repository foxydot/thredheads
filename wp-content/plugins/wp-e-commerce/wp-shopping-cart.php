<?php
/**
  * Plugin Name: WP e-Commerce
  * Plugin URI: http://getshopped.org/
  * Description: A plugin that provides a WordPress Shopping Cart. See also: <a href="http://getshopped.org" target="_blank">GetShopped.org</a> | <a href="http://getshopped.org/forums/" target="_blank">Support Forum</a> | <a href="http://docs.getshopped.org/" target="_blank">Documentation</a>
  * Version: 3.8.13.3
  * Author: Instinct Entertainment
  * Author URI: http://getshopped.org/
  **/

/**
 * WP_eCommerce
 *
 * Main WPEC Plugin Class
 *
 * @package wp-e-commerce
 */
class WP_eCommerce {
	private $components = array(
		'merchant'    => array(),
		'marketplace' => array(),
	);

	/**
	 * Start WPEC on plugins loaded
	 *
	 * @uses add_action()   Attaches to 'plugins_loaded' hook
	 * @uses add_action()   Attaches to 'wpsc_components' hook
	 */
	function __construct() {
		add_action( 'plugins_loaded' , array( $this, 'init' ), 8 );
		add_filter( 'wpsc_components', array( $this, '_register_core_components' ) );
	}

	/**
	 * Takes care of loading up WPEC
	 *
	 * @uses WP_eCommerce::start()      Initializes basic WPEC constants
	 * @uses WP_eCommerce::constants()  Setup WPEC core constants
	 * @uses WP_eCommerce::includes()   Includes the WPEC files
	 * @uses WP_eCommerce::load()       Setup WPEC Core
	 * @uses do_action()                Calls 'wpsc_pre_init' which runs before WPEC initializes
	 * @uses do_action()                Calls 'wpsc_init' runs just after WPEC initializes
	 */
	function init() {
		// Previous to initializing
		do_action( 'wpsc_pre_init' );

		// Initialize
		$this->start();
		$this->constants();
		$this->includes();
		$this->load();

		// Finished initializing
		do_action( 'wpsc_init' );
	}

	/**
	 * New WPSC components API.
	 *
	 * Allows for modular coupling of different functionalities within WPSC.
	 * This is the way we'll be introducing cutting-edge APIs
	 *
	 * @since 3.8.9.5
	 *
	 * @param   array $components
	 * @return  array $components
	 */
	public function _register_core_components( $components ) {
		$components['merchant']['core-v2'] = array(
			'title'    => __( 'WP e-Commerce Merchant API v2', 'wpsc' ),
			'includes' =>
				WPSC_FILE_PATH . '/wpsc-components/merchant-core-v2/merchant-core-v2.php'
		);

		$components['theme-engine']['core-v1'] = array(
			'title'    => __( 'WP e-Commerce Theme Engine v1', 'wpsc' ),
			'includes' =>
				WPSC_FILE_PATH . '/wpsc-components/theme-engine-v1/theme-engine-v1.php'
		);

		$components['marketplace']['core-v1'] = array(
			'title'    => __( 'WP e-Commerce Marketplace API v1', 'wpsc' ),
			'includes' =>
				WPSC_FILE_PATH . '/wpsc-components/marketplace-core-v1/marketplace-core-v1.php'
		);

		return $components;
	}

	/**
	 * Initialize the basic WPEC constants
	 *
	 * @uses plugins_url()              Retrieves url to plugins directory
	 * @uses load_plugin_textdomain()   Loads plugin transations strings
	 * @uses plugin_basename()          Gets the basename of a plugin (extracts the name of a plugin from its filename)
	 * @uses do_action()                Calls 'wpsc_started' which runs after WPEC has started
	 */
	function start() {
		// Set the core file path
		define( 'WPSC_FILE_PATH', dirname( __FILE__ ) );

		// Define the path to the plugin folder
		define( 'WPSC_DIR_NAME',  basename( WPSC_FILE_PATH ) );

		// Define the URL to the plugin folder
		define( 'WPSC_FOLDER',    dirname( plugin_basename( __FILE__ ) ) );
		define( 'WPSC_URL',       plugins_url( '', __FILE__ ) );

		//load text domain
		if ( ! load_plugin_textdomain( 'wpsc', false, '../languages/' ) )
			load_plugin_textdomain( 'wpsc', false, dirname( plugin_basename( __FILE__ ) ) . '/wpsc-languages/' );

		// Finished starting
		do_action( 'wpsc_started' );
	}

	function setup_table_names() {
		global $wpdb;
		$wpdb->wpsc_meta                = WPSC_TABLE_META;
		$wpdb->wpsc_also_bought         = WPSC_TABLE_ALSO_BOUGHT;
		$wpdb->wpsc_region_tax          = WPSC_TABLE_REGION_TAX;
		$wpdb->wpsc_coupon_codes        = WPSC_TABLE_COUPON_CODES;
		$wpdb->wpsc_cart_contents       = WPSC_TABLE_CART_CONTENTS;
		$wpdb->wpsc_claimed_stock       = WPSC_TABLE_CLAIMED_STOCK;
		$wpdb->wpsc_currency_list       = WPSC_TABLE_CURRENCY_LIST;
		$wpdb->wpsc_purchase_logs       = WPSC_TABLE_PURCHASE_LOGS;
		$wpdb->wpsc_checkout_forms      = WPSC_TABLE_CHECKOUT_FORMS;
		$wpdb->wpsc_cart_itemmeta       = WPSC_TABLE_CART_ITEM_META; // required for _get_meta_table()
		$wpdb->wpsc_cart_item_meta      = WPSC_TABLE_CART_ITEM_META;
		$wpdb->wpsc_product_rating      = WPSC_TABLE_PRODUCT_RATING;
		$wpdb->wpsc_download_status     = WPSC_TABLE_DOWNLOAD_STATUS;
		$wpdb->wpsc_submitted_form_data = WPSC_TABLE_SUBMITTED_FORM_DATA;
	}

	/**
	 * Setup the WPEC core constants
	 *
	 * @uses wpsc_core_constants()                      Loads the WPEC Core constants
	 * @uses wpsc_core_is_multisite()                   Checks if this is a multisite install. True if is multisite
	 * @uses wpsc_core_load_session()                   Loads the WPEC core session
	 * @uses wpsc_core_constants_version_processing()   Checks and sets a constant for WordPress version
	 * @uses wpsc_core_constants_table_names()          Sets constants for WPEC table names
	 * @uses wpsc_core_constants_uploads()              Set the upload related constants
	 * @uses do_action()                                Calls 'wpsc_constants' which runs after the WPEC constants are defined
	 */
	function constants() {
		// Define globals and constants used by wp-e-commerce
		require_once( WPSC_FILE_PATH . '/wpsc-core/wpsc-constants.php' );

		// Load the WPEC core constants
		wpsc_core_constants();

		// Is WordPress Multisite
		wpsc_core_is_multisite();

		// Start the wpsc session
		wpsc_core_load_session();

		// Which version of WPEC
		wpsc_core_constants_version_processing();

		// WPEC Table names and related constants
		wpsc_core_constants_table_names();

		// setup wpdb table name attributes
		$this->setup_table_names();

		// Uploads directory info
		wpsc_core_constants_uploads();

		// Any additional constants can hook in here
		do_action( 'wpsc_constants' );
	}

	/**
	 * Include the rest of WPEC's files
	 *
	 * @uses apply_filters()    Calls 'wpsc_components' private merchant components
	 * @uses do_action()        Calls 'wpsc_includes' which runs after WPEC files have been included
	 */
	function includes() {
		require_once( WPSC_FILE_PATH . '/wpsc-includes/wpsc-meta-init.php' );
		require_once( WPSC_FILE_PATH . '/wpsc-core/wpsc-functions.php' );
		require_once( WPSC_FILE_PATH . '/wpsc-core/wpsc-installer.php' );
		require_once( WPSC_FILE_PATH . '/wpsc-core/wpsc-includes.php' );

		$this->components = apply_filters( 'wpsc_components', $this->components );

		foreach ( $this->components as $type => $registered ) {
			foreach ( $registered as $component ) {
				if ( ! is_array( $component['includes'] ) )
					$component['includes'] = array( $component['includes' ] );
				foreach ( $component['includes'] as $include ) {
					require_once( $include );
				}
			}
		}

		// Any additional file includes can hook in here
		do_action( 'wpsc_includes' );
	}

	/**
	 * Setup the WPEC core
	 *
	 * @uses do_action()                            Calls 'wpsc_pre_load' which runs before WPEC setup
	 * @uses do_action()                            Calls 'wpsc_before_init' which is a legacy action
	 * @uses _wpsc_action_create_customer_id()      Sets up a customer id just in case we don't have it
	 * @uses wpsc_core_setup_globals()              Sets up the WPEC core globals
	 * @uses wpsc_core_setup_cart()                 Sets up the WPEC core cart
	 * @uses wpsc_core_load_thumbnail_sizes()       Sets up the core WPEC thumbnail sizes
	 * @uses wpsc_core_load_purchase_log_statuses() Loads the statuses for the purchase logs
	 * @uses wpsc_core_load_checkout_data()         Sets up the core WPEC form checkout data
	 * @uses wpsc_core_load_gateways()              Loads the merchants from the directory
	 * @uses wpsc_core_load_shipping_modules()      Gets shipping modules from the shipping directory
	 * @uses wpsc_core_load_page_titles()           Loads the core WPEC pagetitles
	 * @uses do_action()                            Calls 'wpsc_loaded' which runs after WPEC is fully loaded
	 */
	function load() {
		// Before setup
		do_action( 'wpsc_pre_load' );

		// Legacy action
		do_action( 'wpsc_before_init' );

		// Setup the core WPEC globals
		wpsc_core_setup_globals();

		// Setup the customer ID just in case to make sure it's set up correctly
		add_action( 'init', '_wpsc_action_setup_customer', 1 );

		// Load the purchase log statuses
		wpsc_core_load_purchase_log_statuses();

		// Load unique names and checout form types
		wpsc_core_load_checkout_data();

		// Load the gateways
		wpsc_core_load_gateways();

		// Load the shipping modules
		wpsc_core_load_shipping_modules();

		// WPEC is fully loaded
		do_action( 'wpsc_loaded' );
	}

	/**
	 * WPEC Activation Hook
	 *
	 * @uses deactivate_plugins()     Deactivates plugins by string
	 * @uses wp_die()                 Kills loading and returns the HTML
	 * @uses wpsc_install()           Performs checks to see if this is a clean install or not
	 */
	function install() {
		global $wp_version;

		if ( ( float ) $wp_version < 3.0 ) {
			 deactivate_plugins( plugin_basename( __FILE__ ) ); // Deactivate ourselves
			 wp_die( __( 'Looks like you\'re running an older version of WordPress, you need to be running at least WordPress 3.0 to use WP e-Commerce 3.8', 'wpsc' ), __( 'WP e-Commerce 3.8 not compatible', 'wpsc' ), array( 'back_link' => true ) );
		}
		define( 'WPSC_FILE_PATH', dirname( __FILE__ ) );
		require_once( WPSC_FILE_PATH . '/wpsc-core/wpsc-installer.php' );
		$this->constants();
		wpsc_install();

	}

	/**
	 * Runs the WPEC deactivation routines which basically just removes the cron
	 * jobs that WPEC has set.
	 *
	 * @uses wp_get_schedules()           Retrieves all filtered Cron recurrences
	 * @uses wp_clear_scheduled_hook()    Removes any hooks on cron
	 */
	public function deactivate() {
		foreach ( wp_get_schedules() as $cron => $schedule ) {
			wp_clear_scheduled_hook( "wpsc_{$cron}_cron_task" );
		}
	}
}

// Start WPEC
$wpec = new WP_eCommerce();

// Activation
register_activation_hook( __FILE__  , array( $wpec, 'install' ) );
register_deactivation_hook( __FILE__, array( $wpec, 'deactivate' ) );
