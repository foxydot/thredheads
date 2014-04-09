<?php
/*
Plugin Name: WP e-Commerce - Manual Ordering
Plugin URI: http://www.visser.com.au/wp-ecommerce/plugins/manual-ordering/
Description: Manually process orders on behalf of the customer within WP e-Commerce.
Version: 1.4.2
Author: Visser Labs
Author URI: http://www.visser.com.au/about/
License: GPL2
*/

load_plugin_textdomain( 'wpsc_mo', null, dirname( plugin_basename( __FILE__ ) ) . '/languages' );

include_once( 'includes/functions.php' );

include_once( 'includes/common.php' );

switch( wpsc_get_major_version() ) {

	case '3.7':
		include_once( 'includes/release-3_7.php' );
		break;

	case '3.8':
		include_once( 'includes/release-3_8.php' );
		break;

}

$wpsc_mo = array(
	'filename' => basename( __FILE__ ),
	'dirname' => basename( dirname( __FILE__ ) ),
	'abspath' => dirname( __FILE__ ),
	'relpath' => basename( dirname( __FILE__ ) ) . '/' . basename( __FILE__ )
);

$wpsc_mo['prefix'] = 'wpsc_mo';
$wpsc_mo['name'] = __( 'Manual Ordering for WP e-Commerce', 'wpsc_mo' );
$wpsc_mo['menu'] = __( 'Add New Order', 'wpsc_mo' );
$wpsc_mo['pluginpath'] = WP_PLUGIN_URL . '/' . basename( dirname( __FILE__ ) );

function wpsc_mo_init() {

	global $wp_roles;

	if( !get_option( 'wpsc_mo_cap_added', false ) ) {
		if( is_object( $wp_roles ) && method_exists( $wp_roles,'add_cap' ) ) {
			$role = get_role( 'administrator' );
			if( $role )
				$role->add_cap( 'add_sales' );
			wpsc_mo_update_option( 'cap_added', true );
		}
	}

}
add_action( 'init', 'wpsc_mo_init' );

if( is_admin() ) {

	/* Start of: WordPress Administration */

	include_once( 'includes/install.php' );
	register_activation_hook( __FILE__, 'wpsc_mo_install' );

	function wpsc_mo_add_settings_link( $links, $file ) {

		static $this_plugin;
		if( !$this_plugin ) $this_plugin = plugin_basename( __FILE__ );
		if( $file == $this_plugin ) {
			$settings_link = sprintf( '<a href="%s">' . __( 'Settings', 'wpsc_mo' ) . '</a>', add_query_arg( 'page', 'wpsc_mo', 'options-general.php' ) );
			array_unshift( $links, $settings_link );
		}
		return $links;

	}
	add_filter( 'plugin_action_links', 'wpsc_mo_add_settings_link', 10, 2 );

	function wpsc_mo_enqueue_scripts( $hook ) {

		/* Manual Ordering */
		$page = 'wpsc-product_page_wpsc_mo';
		if( $page == $hook ) {
			/* Chosen */
			wp_enqueue_style( 'ajax-chosen', plugins_url( '/templates/admin/chosen.css', __FILE__ ) );
			wp_enqueue_script( 'ajax-chosen', plugins_url( '/js/chosen.jquery.js', __FILE__ ), array( 'jquery' ) );

			/* Common */
			wp_enqueue_script( 'wpsc_mo_scripts', plugins_url( '/templates/admin/wpsc-admin_mo-order.js', __FILE__ ), array( 'jquery' ) );
			wp_localize_script( 'wpsc_mo_scripts', 'Global', array( 'ajaxurl' => admin_url( 'admin-ajax.php' ) ) );
			wp_enqueue_style( 'wpsc_mo_styles', plugins_url( '/templates/admin/wpsc-admin_mo-order.css', __FILE__ ) );
		}

		$page = 'dashboard_page_wpsc-purchase-logs';
		if( $page == $hook ) {
			wp_enqueue_script( 'wpsc_mo_button', plugins_url( '/templates/admin/wpsc-admin_mo-manage_sales.js', __FILE__ ), array( 'jquery' ) );
		}

	}
	add_action( 'admin_enqueue_scripts', 'wpsc_mo_enqueue_scripts' );

	function wpsc_mo_add_modules_admin_pages( $page_hooks, $base_page ) {

		$page_hooks[] = add_submenu_page( $base_page, __( 'Manual Ordering', 'wpsc_mo' ), __( 'Add Order', 'wpsc_mo' ), 'add_sales', 'wpsc_mo', 'wpsc_mo_html_page' );
		return $page_hooks;

	}
	add_filter( 'wpsc_additional_pages', 'wpsc_mo_add_modules_admin_pages', 10, 2 );

	function wpsc_mo_store_admin_menu() {

		add_submenu_page( 'wpsc_sm', __( 'Add New Order', 'wpsc_mo' ), __( 'Add Order', 'wpsc_mo' ), 'add_sales', 'wpsc_mo', 'wpsc_mo_html_page' );
		remove_filter( 'wpsc_additional_pages', 'wpsc_mo_add_modules_admin_pages', 10 );

	}
	add_action( 'wpsc_sm_store_admin_subpages', 'wpsc_mo_store_admin_menu' );

	function wpsc_mo_html_page() {

		global $wpdb, $wpsc_mo;

		if( !current_user_can( 'add_sales' ) )
			wp_die( __( 'You do not have sufficient permissions to access this page.', 'wpsc_mo' ) );

		include_once( $wpsc_mo['abspath'] . '/includes/template.php' );

		wpsc_mo_template_header();
		$action = wpsc_get_action();
		switch( $action ) {

			case 'save':
				wpsc_mo_order_save();
				break;

			case 'delete':
				wpsc_mo_order_delete();
				break;

			default:
				wpsc_mo_order_defaults();
				wpsc_mo_order_form();
				break;

		}
		wpsc_mo_template_footer();

	}

	function wpsc_mo_order_defaults() {

		global $order;

		$order = new stdClass();
		$order->totals = (object)array(
			'discount' => '-',
			'shipping' => '-',
			'tax' => '-',
			'subtotal' => '-'
		);

	}

	function wpsc_mo_order_form() {

		global $wpsc_mo, $wpdb, $wpsc_gateways, $order, $product;

		$products = wpsc_mo_return_products();
		$base_country = wpsc_mo_return_base_country();
		$status_layout = wpsc_mo_get_option( 'status_layout' );
		$payment_method_layout = wpsc_mo_get_option( 'payment_method_layout' );

		/* Users */
		$users = get_users( array( 'orderby' => 'user_login' ) );
		if( $users ) {
			if( isset( $_GET['user'] ) )
				$order->user_id = $_GET['user'];
			foreach( $users as $key => $user ) {
				$user->first_name = get_user_meta( $user->ID, 'first_name', true );
				$user->last_name = get_user_meta( $user->ID, 'last_name', true );
				if( $user->first_name && $user->last_name )
					$users[$key]->menu_name = $user->first_name . ' ' . $user->last_name . ' (' . $user->user_login . ')';
				else
					$users[$key]->menu_name = $user->user_login;
			}
		}

		/* Checkout fields */
		if( isset( $order->checkout_rows ) )
			$checkout_rows = $order->checkout_rows;
		else
			$checkout_rows = wpsc_mo_checkout_rows();
		$heading_exists = false;
		$heading_set = false;
		for( $i = 0; $i < count( $checkout_rows ); $i++ ) {
			if( ! isset( $checkout_rows[$i]->value ) )
				$checkout_rows[$i]->value = '';
			if( $checkout_rows[$i]->type == 'heading' && $heading_set == false ) {
				if( $i == 0 )
					$heading_exists = true;
				else if( $i > 0 )
					$heading_exists = false;
				$heading_set = true;
			}
		}
		$countries = wpsc_mo_get_countries();


		/* Payment methods */
		$payment_methods = wpsc_mo_get_payment_methods();
		if( ! isset( $order->gateway ) )
			$order->gateway = 'wpsc_merchant_testmode';

		/* Sale Status */
		$statuses = wpsc_mo_return_purchase_status();

		/* Subscription */
/*
		$data = 'a:2:{s:10:"subscriber";s:1:"1";s:10:"posts-base";s:1:"1";}';
		echo 'Working: ';
		$output = maybe_unserialize( $data );
		print_r( $output );
		echo '<br />';

		echo 'Failed: ';
		$data = 'a:2:{s:10:"subscriber";s:1:"1";i:0;s:10:"posts-base";}';
		$output = maybe_unserialize( $data );
		print_r( $output );
*/

		include_once( 'templates/admin/wpsc-admin_mo-order.php' );

	}

	function wpsc_mo_order_delete() {

		if( isset( $_GET['purchase_id'] ) )
			$purchase_id = $_GET['purchase_id'];
		else if( isset( $_POST['purchase_id'] ) )
			$purchase_id = $_POST['purchase_id'];

		$output = '';
		if( isset( $purchase_id ) ) {
			wpsc_delete_purchlog( $purchase_id );

			$message = __( 'Order has been removed.', 'wpsc_mo' );
			$output .= '<div class="updated settings-error"><p><strong>' . $message . '</strong></p></div>';
			echo $output;
		} else {
			$message = 'You failed to supply the required data.';
			$output .= '<div class="updated settings-error"><p>' . $message . '</p></div>';
			echo $output;
		}
		wpsc_mo_order_form();

	}

	function wpsc_mo_order_save() {

		global $wpdb, $order;

		$order = new stdClass();
		$order->total_price = '0.00';
		$error = array();
		$checkout_rows = wpsc_mo_checkout_rows();
		$checkout_values = $_POST['checkout_form'];
		$checkout_temp = array();
		foreach( $checkout_rows as $checkout_row ) {
			$checkout_row_value = isset( $checkout_values[$checkout_row->id] ) ? $checkout_values[$checkout_row->id] : '';
			$checkout_temp[] = (object)array(
				'id' => $checkout_row->id,
				'name' => $checkout_row->name,
				'type' => $checkout_row->type,
				'mandatory' => $checkout_row->mandatory,
				'unique_name' => $checkout_row->unique_name,
				'checkout_order' => $checkout_row->checkout_order,
				'value' => $checkout_row_value
			);
		}
		$order->checkout_rows = $checkout_temp;
		for( $i = 0; $i < count( $order->checkout_rows ); $i++ ) {

			/* Country for purchase logs and Labels for Offline Credit Card Processing */
			switch( $order->checkout_rows[$i]->unique_name ) {

				case 'billingfirstname':
					$billing_firstname = $order->checkout_rows[$i]->value;
					break;

				case 'billinglastname':
					$billing_lastname = $order->checkout_rows[$i]->value;
					break;

				case 'billingemail':
					$billing_email = $order->checkout_rows[$i]->value;
					break;

				case 'billingcountry':
					$order->billing_country = $order->checkout_rows[$i]->value;
					break;

				case 'shippingcountry':
					$order->shipping_country = $order->checkout_rows[$i]->value;
					break;

			}

			/* Validation check */
			if( $order->checkout_rows[$i]->mandatory && !$order->checkout_rows[$i]->value )
				$error[] = sprintf( __( '%s is a required field.', 'wpsc_mo' ), $order->checkout_rows[$i]->name );

		}
		$order->status = $_POST['status'];
		$order->discount_type = $_POST['discount_type'];
		$order->discount_data = '';

		/* Start of: Offline Credit Card Processing */

		global $wpsc_oc;

		if ( isset( $wpsc_oc ) ) {
			$show_name = wpsc_oc_get_option( 'show_name' );
			$show_type = wpsc_oc_get_option( 'show_type' );
			$show_cvv = wpsc_oc_get_option( 'show_cvv' );
	
			$card_number = $_POST['wpsc_oc_number'];
			if( $show_name )
				$card_name = $_POST['wpsc_oc_name'];
			if( $show_type )
				$card_type = $_POST['wpsc_oc_type'];
			$expiry_month = $_POST['wpsc_oc_expiry_month'];
			$expiry_year = $_POST['wpsc_oc_expiry_year'];
			$card_expiry = $expiry_month . '/' . $expiry_year;
			if( $show_cvv ) {
				$card_cvv = $_POST['wpsc_oc_cvv'];
				if( $card_number && $expiry_month && $expiry_year && $card_cvv )
					$order->gateway = 'wpsc_oc';
				else
					$order->gateway = $_POST['payment_method'];
			} else {
				if( $card_number && $expiry_month && $expiry_year )
					$order->gateway = 'wpsc_oc';
				else
					$order->gateway = $_POST['payment_method'];
			}
		}

		/* End of: Offline Credit Card Processing */

		if( isset( $_POST['rows'] ) )
			$rows = $_POST['rows'];
		if( isset( $_POST['product_id'] ) )
			$product_id = $_POST['product_id'];
		if( isset( $_POST['quantity'] ) )
			$quantity = $_POST['quantity'];
		if( isset( $_POST['price'] ) )
			$price = $_POST['price'];

		$order->products = array();
		if( isset( $product_id ) && isset( $quantity ) ) {
			foreach( $product_id as $key ) {
				if( isset( $product_id[$key] ) && $quantity[$key] > 0 ) {
					$order->products[$key] = (object)array(
						'ID' => $product_id[$key],
						'quantity' => $quantity[$key]
					);
					if( isset( $price[$key] ) )
						$order->products[$key]->price = $price[$key];
				}
			}
		}
		if( !$order->products )
			$error[] = __( 'No Products were assigned to the Order', 'wpsc_mo' );
		$totals = $_POST['total'];
		if( $totals ) {
			$order->totals = (object)array(
				'discount' => $totals['discount'],
				'shipping' => (double)$totals['shipping'],
				'tax' => (double)$totals['tax'],
				'subtotal' => (double)$totals['subtotal']
			);
		}
		$order->user_id = $_POST['user'];
		$order->assigned_to = $_POST['assigned_to'];
		$order->notes = $_POST['notes'];

		/* Validation */
		if( $error ) {
			/* Validation: Failed */

			$message = '';
			for( $i = 0; $i < count( $error ); $i++ )
				$message .= $error[$i] . '<br />';
			$output = '<div class="error settings-error"><p><strong>' . $message . '</strong></p></div>';
			echo $output;

			wpsc_mo_order_form();
			return;
		}

		/* Validation passed, save to database */

		$order->session_id = ( mt_rand( 100, 999 ) . time() );
		$plugin_version = wpsc_mo_get_db_plugin_version();

		if( !isset( $order->gateway ) )
			$order->gateway = $_POST['payment_method'];

		$wpdb->insert( $wpdb->prefix . 'wpsc_purchase_logs', array(
			'sessionid' => $order->session_id,
			'processed' => $order->status,
			'date' => time(),
			'gateway' => $order->gateway,
			'billing_country' => $order->billing_country,
			'shipping_country' => $order->shipping_country,
			'email_sent' => '1',
			'discount_data' => '',
			'shipping_method' => '',
			'shipping_option' => '',
			'plugin_version' => $plugin_version,
			'notes' => $order->notes,
			'wpec_taxes_total' => '0.00',
			'wpec_taxes_rate' => '0.00'
		) );
		$order->purchase_id = $wpdb->insert_id;
		if( $order->user_id ) {
			$wpdb->update( $wpdb->prefix . 'wpsc_purchase_logs', array(
				'user_ID' => $order->user_id
			), array( 'id' => $order->purchase_id ) );
		}
		if( $order->assigned_to )
			wpsc_update_meta( $order->purchase_id, 'assigned_to', $order->assigned_to, 'purchase_log' );

		foreach( $order->checkout_rows as $checkout_row ) {
			switch( $checkout_row->type ) {

				case 'text':
				case 'address':
				case 'delivery_address':
				case 'city':
				case 'country':
				case 'delivery_country':
				case 'email':
				case 'textarea':
					$wpdb->insert( $wpdb->prefix . 'wpsc_submited_form_data', array( 
						'log_id' => $order->purchase_id,
						'form_id' => $checkout_row->id,
						'value' => $checkout_row->value
					) );
					break;

			}
		}

		if( $order->products ) {
			$current_time = strtotime( current_time( 'mysql' ) );
/*
			if( $user_id ) {
				$subscription_length = (array)get_user_meta( $user_id, '_subscription_length' );
				$subscription_starts = (array)get_user_meta( $user_id, '_subscription_starts' );
				$subscription_ends = (array)get_user_meta( $user_id, '_subscription_ends' );
			}
*/
			foreach( $order->products as $product ) {
				$product_data = wpsc_mo_return_product( $product->ID );
				wpsc_mo_reduce_product_stock( $product->ID, $product->quantity );

				/* Product Price override */
				if( isset( $product->price ) )
					$product_data->price = $product->price;

				if( $order->totals->subtotal <> '-' ) {
					if( $product->quantity > 1 )
						$product_data->price = ( ( $order->totals->subtotal / count( $order->products ) ) / $product->quantity );
					else
						$product_data->price = ( $order->totals->subtotal / count( $order->products ) );
				}
				$wpdb->insert( $wpdb->prefix . 'wpsc_cart_contents', array(
					'prodid' => $product->ID,
					'name' => $product_data->name,
					'purchaseid' => $order->purchase_id,
					'price' => $product_data->price,
					'quantity' => $product->quantity,
					'files' => 'N;'
				) );
				$cart_id = $wpdb->insert_id;
				if( isset( $product_data->downloads ) ) {
					$downloads = explode( ',', $product_data->downloads );
					foreach( $downloads as $download ) {
						$wpdb->insert( $wpdb->prefix . 'wpsc_download_status', array(
							'product_id' => $product->ID,
							'fileid' => $download,
							'purchid' => $order->purchase_id,
							'cartid' => $cart_id,
							'uniqueid' => sha1( uniqid( rand(), true ) ),
							'downloads' => wpsc_mo_get_max_option(),
							'active' => 1,
							'datetime' => current_time( 'mysql' )
						) );
					}
				}
				$order->total_price += ( $product_data->price * $product->quantity );
/*
				if( $product_data->subscription_capabilities && $user_id ) {
					foreach( $product_data->subscription_capabilities as $subscription_capability ) {
						wpsc_add_wpsc_capability( $user_id, $subscription_capability );
						$end = wpsc_mo_membership_length( $wpsc_productdata->membership_length );
						$length = $end - $current_time;
						$subscription_length[$subscription_capability] = $length;
						update_user_meta( $user_id, '_subscription_length', $subscription_length );
						$subscription_starts[$subscription_capability] = $current_time;
						update_user_meta( $user_id, '_subscription_starts', $subscription_starts );
						$subscription_ends[$subscription_capability] = $end;
						update_user_meta( $user_id, '_subscription_ends', $subscription_ends );
					}
				}
*/
				unset( $product_data );
			}

			/* Total overrides */
			if( $order->totals->discount ) {
				if( $order->totals->discount <> '-' ) {
					/* Process discount */
					switch( $order->discount_type ) {

						case 'percent':
							$order->totals->discount = $order->total_price / (int)$order->totals->discount;
							break;

						case 'coupon':
							$coupon = wpsc_mo_get_coupon( $order->totals->discount );
							if( $coupon ) {
								$order->discount_data = $order->totals->discount;
								$order->totals->discount = $coupon->value;
							}
							break;

					}
					$wpdb->update( $wpdb->prefix . 'wpsc_purchase_logs', array(
						'discount_data' => $order->discount_data,
						'discount_value' => $order->totals->discount,
					), array( 'id' => $order->purchase_id ) );
				}
			}
			if( $order->totals->shipping ) {
				if( $order->totals->shipping <> '-' ) {
					$wpdb->update( $wpdb->prefix . 'wpsc_purchase_logs', array(
						'base_shipping' => $order->totals->shipping,
					), array( 'id' => $order->purchase_id ) );
				}
			}
			if( $order->totals->tax ) {
				if( $order->totals->tax <> '-' ) {
					$wpdb->update( $wpdb->prefix . 'wpsc_purchase_logs', array(
						'wpec_taxes_total' => $order->totals->tax,
					), array( 'id' => $order->purchase_id ) );
				}
			}
			if( $order->totals->subtotal ) {
				if( $order->totals->subtotal <> '-' )
					$order->total_price = $order->totals->subtotal;
			}

			/* Set Sale total */
			$wpdb->update( $wpdb->prefix . 'wpsc_purchase_logs', array(
				'totalprice' => $order->total_price,
			), array( 'id' => $order->purchase_id ) );

		}

		/* Start of: Offline Credit Card integration */
		if( isset( $card_number ) && ! empty( $card_number ) ) {
			$chars1 = $card_number[0];
			$chars2 = substr( $card_number, 0, 2 );
			$chars3 = substr( $card_number, 0, 3 );
			$chars4 = substr( $card_number, 0, 4 );
			if( $chars1 == '4' )
				$cardtype = __( 'VISA', 'wpsc_oc' );
			if( ( $chars2 >= '51' ) && ( $chars2 <= '55' ) )
				$cardtype = __( 'Mastercard', 'wpsc_oc' );
			if( ( $chars2 == '34' ) || ( $chars2 == '37' ) )
				$cardtype = __( 'American Express', 'wpsc_oc' );
			if( $chars4 == '6011' )
				$cardtype = __( 'Discover', 'wpsc_oc' );
			$stored = array();
			$stored[] = array( 'card_type', wpsc_oc_get_option( 'label_type', __( 'Card Type', 'wpsc_oc' ) ), 'XXXX' );
			if( $show_name )
				$stored[] = array( 'name', wpsc_oc_get_option( 'label_name', __( 'Name', 'wpsc_oc' ) ), $card_name );
			$stored[] = array( 'credit_card', 'Credit Card #', substr_replace( $card_number, 'XXXX', -4, 4 ) );
			$stored[] = array( 'expiry', 'Expiry Date', 'XX/XXXX' );
			if( $show_cvv )
				$stored[] = array( 'cvv', 'CVV', 'XXX' );
			$stored = serialize( $stored );
			$wpdb->insert( $wpdb->prefix . 'wpsc_creditcard', array(
				'log_id' => $order->purchase_id, 
				'data' => $stored, 
				'datetime' => current_time( 'mysql' ), 
				'expire' => date( 'Y-m-d H:i:s', strtotime( '+3 days' ) ),
				'views' => wpsc_oc_get_option( 'sale_views' ) 
			) );
			$email_body = __( 'Hi ', 'wpsc_oc' ) . get_bloginfo() . ",\n\r";
			$email_body .= __( 'A new Sale has been received requiring manual payment processing, please notify the relevant member of staff.', 'wpsc_oc' ) . "\n\r";
			$email_body .= "---\n\r";
			$email_body .= __( 'Process a Sale as soon as possible. If a Sale is older than 3 days (72 hours) and has not been processed credit card details are automatically erased to protect the customer.', 'wpsc_oc' ) . "\n\r";
			$email_body .= "---\n\r";
			$email_body .= __( 'Customer Details', 'wpsc_oc' ) . "\n\r";
			if( $billing_firstname )
				$email_body .= __( 'First Name', 'wpsc_oc' ) . ": " . $billing_firstname . "\n";
			if( $billing_lastname )
				$email_body .= __( 'Last Name', 'wpsc_oc' ) . ": " . $billing_lastname . "\n";
			$email_body .= __( 'E-mail address', 'wpsc_oc' ) . ": " . $billing_email . "\n\r";
			$email_body .= __( 'Payment Details', 'wpsc_oc' ) . "\n\r";
			$email_body .= __( 'Card Type', 'wpsc_oc' ) . ": " . $card_type . "\n";
			$email_body .= __( 'Last 4 Digits', 'wpsc_oc' ) . ": " . substr( $card_number, -4, 4 ) . "\n";
			$email_body .= __( 'Expiry Date', 'wpsc_oc' ) . ": " . $card_expiry . "\n";
			if( $show_cvv )
				$email_body .= __( 'CVV', 'wpsc_oc' ) . ": " . $card_cvv . "\n\r";
			$email_body .= "\n---\n\r";
			global $wp_version;
			if( $wp_version == '2.9.2' )
				$email_body .= __( 'To process this Sale please click the following link', 'wpsc_oc' ) . ":\n\r" . admin_url() . "admin.php?page=wpsc-sales-logs&purchaselog_id=" . $order->purchase_id;
			else
				$email_body .= __( 'To process this Sale please click the following link', 'wpsc_oc' ) . ":\n\r" . get_admin_url() . "admin.php?page=wpsc-sales-logs&purchaselog_id=" . $order->purchase_id;
			wp_mail( wpsc_oc_get_option( 'owner_email', get_option( 'purch_log_email' ) ), wpsc_oc_get_option( 'email_subject', 'Purchase Report: Payment Details' ), $email_body );
		}
		/* End of: Offline Credit Card integration */

		$message = __( 'New order created.', 'wpsc_mo' );
		$output = '<div class="updated settings-error"><p><strong>' . $message . '</strong></p></div>';
		echo $output;

		switch( wpsc_get_major_version() ) {

			case '3.7':
				$view_sale_url = add_query_arg( array( 'page' => 'wpsc-sales-logs', 'purchaselog_id' => $order->purchase_id ), 'index.php' );
				$new_form_url = add_query_arg( array( 'post_type' => 'wpsc-product', 'page', 'wpsc_mo' ), 'edit.php' );
				$delete_form_url = add_query_arg( array( 'post_type' => 'wpsc-product', 'page' => 'wpsc_mo', 'action' => 'delete', 'purchase_id' => $order->purchase_id ), 'edit.php' );
				break;

			case '3.8':
				$view_sale_url = add_query_arg( array( 'page' => 'wpsc-purchase-logs', 'c' => 'item_details', 'id' => $order->purchase_id ), 'index.php' );
				$new_form_url = add_query_arg( array( 'post_type' => 'wpsc-product', 'page' => 'wpsc_mo' ), 'edit.php' );
				$delete_form_url = add_query_arg( array( 'post_type' => 'wpsc-product', 'page' => 'wpsc_mo', 'action' => 'delete', 'purchase_id' => $order->purchase_id ), 'edit.php' );
				if( function_exists( 'wpsc_eo_admin_init' ) )
					$edit_sale_url = add_query_arg( array( 'post_type' => 'wpsc-product', 'page' => 'wpsc_eo', 'action' => 'edit', 'purchase_id' => $order->purchase_id ), 'edit.php' );
				break;

		}

		include_once( 'templates/admin/wpsc-admin_mo-order_confirm.php' );

		$order = null;
		wpsc_mo_order_defaults();
		wpsc_mo_order_form();

	}

	function wpsc_mo_settings_page() {

		global $wpsc_mo;

		wpsc_mo_template_header( __( 'Manual Ordering', 'wpsc_mo' ) );
		$action = wpsc_get_action();
		switch( $action ) {

			case 'update':
				$status_layout = $_POST['status_layout'];
				$default_status = $_POST['default_status'];
				$payment_method_layout = $_POST['payment_method_layout'];
				$default_payment_method = $_POST['default_payment_method'];
				$show_session_id = $_POST['show_session_id'];

				wpsc_mo_update_option( 'status_layout', $status_layout );
				wpsc_mo_update_option( 'default_status', $default_status );
				wpsc_mo_update_option( 'payment_method_layout', $payment_method_layout );
				wpsc_mo_update_option( 'default_payment_method', $default_payment_method );
				wpsc_mo_update_option( 'show_session_id', $show_session_id );

				$message = __( 'Settings saved.', 'wpsc_mo' );
				$output = '<div class="updated settings-error"><p>' . $message . '</p></div>';
				echo $output;

				wpsc_mo_settings_form();
				break;

			default:
				wpsc_mo_settings_form();
				break;

		}
		wpsc_mo_template_footer();

	}

	function wpsc_mo_settings_form() {

		global $wpsc_mo;

		$wpsc_purchlog_statuses = wpsc_mo_return_purchase_status();
		$payment_methods = wpsc_mo_get_payment_methods();

		$status_layout = wpsc_mo_get_option( 'status_layout' );
		$default_status = wpsc_mo_get_option( 'default_status' );
		$payment_method_layout = wpsc_mo_get_option( 'payment_method_layout' );
		$default_payment_method = wpsc_mo_get_option( 'default_payment_method' );
		$show_session_id = wpsc_mo_get_option( 'show_session_id' );

		include_once( 'templates/admin/wpsc-admin_mo-settings.php' );

	}

	/* End of: WordPress Administration */

}

/* Start of: Admin Ajax */

function wpsc_mo_get_product_row() {

	global $wpsc_mo;

	if( isset( $_POST['product_id'] ) ) {
		$product_id = $_POST['product_id'];
		if( isset( $_POST['variation'] ) ) {
			$provided_parameters = array();
			foreach( $_POST['variation'] as $key => $variation )
				$provided_parameters['variation_values'][$key] = $variation;
			$size = count( $provided_parameters['variation_values'] );
			if( $size > 0 ) {
				$variation_product_id = wpsc_get_child_object_in_terms( $product_id, $provided_parameters['variation_values'], 'wpsc-variation' );
				if( $variation_product_id > 0 )
					$product_id = $variation_product_id;
			}
		}
		$product = wpsc_mo_return_product( $product_id );
		$wpsc_variations = new wpsc_variations( $product_id );

		include_once( 'templates/admin/wpsc-admin_mo-product_row.php' );

	}
	die();

}
add_action( 'wp_ajax_get_product_row', 'wpsc_mo_get_product_row' );

function wpsc_mo_get_user_data() {

	if( isset( $_POST['user_id'] ) ) {

		// Defaults
		$return = array(
			'billing'	=> array(
				'first_name'	=> '',
				'last_name'		=> '',
				'email'			=> '',
				'address'		=> '',
				'city'			=> '',
				'state'			=> '',
				'country'		=> '',
				'postcode'		=> '',
				'phone'			=> ''
			),
			'shipping'	=> array(
				'first_name'	=> '',
				'last_name'		=> '',
				'address'		=> '',
				'city'			=> '',
				'state'			=> '',
				'country'		=> '',
				'postcode'		=> '',
			),
			'status'	=> 'success'
		);

		$user_id = $_POST['user_id'];
		/* Check for previous Checkout information */
		$user = get_user_meta( $user_id, 'wpshpcrt_usr_profile', 1 );
		if ( !empty( $user ) ) {
			$return['billing']['first_name']	= $user[2];
			$return['billing']['last_name']		= $user[3];
			$return['billing']['email']			= $user[9];
			$return['billing']['address']		= $user[4];
			$return['billing']['city']			= $user[5];
			$return['billing']['state']			= $user[6];
			$return['billing']['country']		= $user[7][0];
			$return['billing']['postcode']		= $user[8];
			$return['billing']['phone']			= $user[18];
			$return['shipping']['first_name']	= $user[11];
			$return['shipping']['last_name']	= $user[12];
			$return['shipping']['address']		= $user[13];
			$return['shipping']['city']			= $user[14];
			$return['shipping']['state']		= $user[15];
			$return['shipping']['country']		= $user[16][0];
			$return['shipping']['postcode']		= $user[17];
		} else {
			$user = get_userdata( $user_id );
			$return['billing']['first_name']	= $user->first_name;
			$return['billing']['last_name']		= $user->last_name;
			$return['billing']['email']			= $user->user_email;
		}
		header( "Content-type: application/json" );
		echo json_encode( $return );
	}
	die();

}
add_action( 'wp_ajax_get_user_data', 'wpsc_mo_get_user_data' );

/* End of: Admin Ajax */
?>