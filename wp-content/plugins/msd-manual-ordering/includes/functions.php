<?php
if( is_admin() ) {

	/* Start of: WordPress Administration */

	function wpsc_mo_template_header( $title = '', $icon = 'tools' ) {

		global $wpsc_mo;

		if( $title )
			$output = $title;
		else
			$output = $wpsc_mo['menu'];
		$icon = wpsc_is_admin_icon_valid( $icon ); ?>
<div id="profile-page" class="wrap">
	<div id="icon-<?php echo $icon; ?>" class="icon32"><br /></div>
	<h2><?php echo $output; ?></h2>
<?php
	}

	function wpsc_mo_template_footer() { ?>
</div>
<?php
	}

	function wpsc_mo_users_add_order( $columns ) {

		if( current_user_can( 'add_sales' ) )
			$columns['add_order'] = __( 'Add Order', 'wpsc_mo' );
		return $columns;

	}
	add_filter( 'manage_users_columns', 'wpsc_mo_users_add_order' );

	function wpsc_mo_users_add_order_columns( $value, $column_name, $user_id ) {

		if( 'add_order' != $column_name )
			return $value;
		if( current_user_can( 'manage_options' ) ) {
			$output = '<a href="edit.php?post_type=wpsc-product&page=wpsc_mo&user=' . $user_id . '" class="button-secondary">' . __( 'Add Order', 'wpsc_mo' ) . '</a>';
			return $output;
		}

	}
	add_action( 'manage_users_custom_column', 'wpsc_mo_users_add_order_columns', 10, 3 );

	function wpsc_mo_user_add_order( $profileuser ) {
		
		global $wpsc_mo;

		$output = '';
		if( current_user_can( 'add_sales' ) ) {
			$output = '<h3>' . __( 'Add Order', 'wpsc_mo' ) . '</h3>';
			$output .= sprintf( '<a href="%s" class="button-secondary">' . __( 'Add Order', 'wpsc_mo' ) . '</a>', add_query_arg( array( 'post_type' => 'wpsc-product', 'page' => 'wpsc_mo', 'user' => $profileuser->ID ) ) );
			echo $output;
		}

	}
	add_action( 'show_user_profile', 'wpsc_mo_user_add_order' );
	add_action( 'edit_user_profile', 'wpsc_mo_user_add_order' );

	if( !function_exists( 'wpsc_add_wpsc_capability' ) ) {

		function wpsc_add_wpsc_capability( $user_id, $capability = '' ) {

			global $wpdb;

			if( $capability != '' ) {
				$roles = (array)get_usermeta( $user_id, $wpdb->prefix . 'capabilities' );
				if( !in_array( $capability, $roles ) ) {
					$roles[$capability] = true;
					update_usermeta( $user_id, $wpdb->prefix . 'capabilities', $roles );
				}
			}

		}

	}

	function wpsc_mo_membership_length( $membership_length ) {

		switch( $membership_length['unit'] ) {

			case 'd':
				$future_time = mktime( date( 'h' ), date( 'm' ), date( 's' ), date( 'm' ), ( date( 'd' ) + $membership_length['length'] ), date('Y') );
				break;

			case 'm':
				$future_time = mktime( date( 'h' ), date( 'm' ), date( 's' ), ( date( 'm' ) + $membership_length['length'] ), date( 'd' ), date( 'Y' ) );
				break;

			case 'Y':
				$future_time = mktime( date( 'h' ), date( 'm' ), date( 's' ), date( 'm' ), date( 'd' ), ( date( 'Y' ) + $membership_length['length'] ) );
				break;

			case 'w':
				$length = 7 * (int)$membership_length['length'];	
				$future_time = mktime( date( 'h' ), date( 'm' ), date( 's' ), date( 'm' ), ( date( 'd' ) + $length ), date( 'Y' ) );
				break;

		}
		return $future_time;

	}

	function wpsc_mo_product_has_stock( $product, $type = null ) {

		switch( $type ) {

			case 'class':
				$output = '';
				if( isset( $product->stock ) ) {
					if( $product->stock == '0' )
						$output = 'product-nostock';
				}
				echo $output;
				break;

			default:
				if( isset( $product->stock ) ) {
					if( $product->stock <> '0' )
						return true;
				} else {
					return true;
				}
				break;

		}

	}

	function wpsc_mo_product_has_limited_stock( $product, $type = null ) {

		if( isset( $product->stock ) && $product->stock > 0 )
			return true;

	}

	function wpsc_mo_get_max_option() {

		switch( wpsc_get_major_version() ) {

			case '3.7':
				$max_option = get_option( 'max_downloads' );
				break;

			case '3.8':
				if( version_compare( wpsc_get_minor_version(), '3.8.8', '>=' ) )
					$max_option = get_option( 'max_downloads' );
				else
					$max_option = get_option( 'wpsc_max_downloads' );
				break;

		}
		return $max_option;

	}

	function wpsc_mo_get_db_plugin_version() {

		switch( wpsc_get_major_version() ) {

			case '3.7':
				$plugin_version = '3.7';
				break;

			case '3.8':
				$plugin_version = WPSC_PRESENTABLE_VERSION;
				break;

		}
		return $plugin_version;

	}

	function wpsc_mo_checkout_rows() {

		global $wpdb;

		switch( wpsc_get_major_version() ) {

			case '3.7':
				$checkout_rows_sql = "SELECT `id`, `name`, `type`, `mandatory`, `unique_name`, `order` as checkout_order FROM `" . $wpdb->prefix . "wpsc_checkout_forms` WHERE `active` = 1 AND `checkout_set` = 0 ORDER BY `checkout_order` ASC";
				break;

			case '3.8':
				$checkout_rows_sql = "SELECT `id`, `name`, `type`, `mandatory`, `unique_name`, `checkout_order` FROM `" . $wpdb->prefix . "wpsc_checkout_forms` WHERE `active` = 1 AND `checkout_set` = 0 ORDER BY `checkout_order` ASC";
				break;

		}
		$checkout_rows = $wpdb->get_results( $checkout_rows_sql );
		return $checkout_rows;

	}

	function wpsc_mo_get_countries() {

		global $wpdb;

		$output = '';
		$countries_sql = "SELECT `isocode`, `country` FROM `" . $wpdb->prefix . "wpsc_currency_list` WHERE `visible` = 1 ORDER BY `country` ASC";
		$countries = $wpdb->get_results( $countries_sql );
		if( $countries )
			$output = $countries;
		return $output;

	}

	function wpsc_mo_get_payment_methods() {

		global $wpsc_gateways;

		$output = array();
		$payment_methods = $wpsc_gateways;
		if( $payment_methods ) {
			$output = $payment_methods;
			foreach( $payment_methods as $key => $payment_method ) {
				if( !isset( $payment_method['display_name'] ) )
					$payment_methods[$key]['display_name'] = $payment_method['name'];
			}
		}
		return $output;

	}

	function wpsc_mo_get_coupon( $coupon ) {

		global $wpdb;

		$output = '';
		$coupon_sql = $wpdb->prepare( "SELECT * FROM `" . $wpdb->prefix . "wpsc_coupon_codes` WHERE `active` = 1 AND `coupon_code` = '%s' LIMIT 1", $coupon );
		$coupon = $wpdb->get_row( $coupon_sql );
		if( $coupon )
			$output = $coupon;
		return $coupon;

	}

	function wpsc_mo_get_option( $option = null, $default = false ) {

		global $wpsc_mo;

		$output = '';
		if( isset( $option ) ) {
			$separator = '_';
			$output = get_option( $wpsc_mo['prefix'] . $separator . $option, $default );
		}
		return $output;

	}

	function wpsc_mo_update_option( $option = null, $value = null ) {

		global $wpsc_mo;

		$output = false;
		if( isset( $option ) && isset( $value ) ) {
			$separator = '_';
			$output = update_option( $wpsc_mo['prefix'] . $separator . $option, $value );
		}
		return $output;

	}

	/* End of: WordPress Administration */

}
?>