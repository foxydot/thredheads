<?php
if( is_admin() ) {

	/* Start of: WordPress Administration */

	/* WordPress Administration Menu */
	function wpsc_mo_admin_menu() {

		add_options_page( __( 'Manual Ordering for WP e-Commerce', 'wpsc_mo' ), __( 'Manual Ordering', 'wpsc_mo' ), 'manage_options', 'wpsc_mo_settings', 'wpsc_mo_settings_page' );

	}
	add_action( 'admin_menu', 'wpsc_mo_admin_menu' );

	function wpsc_mo_add_toolbar_items( $admin_bar ){

		$admin_bar->add_menu( array(
			'id'    => 'new-order',
			'parent' => 'new-content',
			'title' => __( 'Order', 'wpsc_mo' ),
			'href'  => self_admin_url( 'edit.php?post_type=wpsc-product&page=wpsc_mo' ),
			'meta'  => array(
				'title' => __( 'Order' )
			),
		));

	}
	add_action( 'admin_bar_menu', 'wpsc_mo_add_toolbar_items', 100 );

	function wpsc_mo_admin_help( $text, $current_screen ) {

		global $wp_version;

		if( version_compare( $wp_version, '3.3' >= 0 ) ) {
			$page_parent = 'wpsc-product_page_';
			$screen = $page_parent . 'wpsc_mo';
			if( $current_screen == $screen ) {
				get_current_screen()->add_help_tab( array(
					'id' => 'overview',
					'title' => __( 'Overview', 'wpsc_mo' ),
					'content' => '<p>' . __( 'To add a new Order to your store, fill in the form on this screen and click the Add Order in the right sidebar.', 'wpsc_mo' ) . '</p>' . 
				'<p>' . __( 'You must assign at least a single Product to the Order and the required Checkout fields must be filled. You can change the Sale Status at a later time from the Dashboard &raquo; Manage Sales screen.', 'wpsc_mo' ) . '</p>' . 
				'<p>' . __( 'The default Add Order settings - including Sale Status, Payment Type, etc. - can be changed from the Settings &raquo; Manual Ordering screen.', 'wpsc_mo' ) . '</p>' . 
				'<p>' . __( 'Remember to click the Add Order button at the bottom of this screen when you are finished.', 'wpsc_mo' ) . '</p>'
				) );
				get_current_screen()->set_help_sidebar( 
					'<p><strong>' . __( 'For more information:', 'wpsc_mo' ) . '</strong></p>' . 
					'<p><a href="http://www.visser.com.au/wp-ecommerce/documentation/plugins/manual-ordering/" target="_blank">' . __( 'Documentation', 'wpsc_mo' ) . '</a></p>' . 
					'<p><a href="http://www.visser.com.au/wp-ecommerce/forums/" target="_blank">' . __( 'Support Forums', 'wpsc_mo' ) . '</a></p>'
				);
			}
		}
		return $text;

	}
	add_filter( 'contextual_help', 'wpsc_mo_admin_help', 10, 2 );

	function wpsc_mo_admin_page_item( $menu = array() ) {

		global $wpsc_mo;

		$title = $wpsc_mo['menu'];
		$link = add_query_arg( array( 'post_type' => 'wpsc-product', 'page' => 'wpsc_mo' ), 'edit.php' );
		$description = __( 'Manually process orders on behalf of the customer within WP e-Commerce.', 'wpsc_mo' );

		$menu[] = array( 'title' => $title, 'link' => $link, 'description' => $description );

		return $menu;

	}
	add_filter( 'wpsc_sm_store_admin_page', 'wpsc_mo_admin_page_item', 1 );

	function wpsc_mo_purchase_logs_add_order() {

		$output = '';
		$title = __( 'Add New' );
		$link = 'edit.php?post_type=wpsc-product&page=wpsc_mo';
		$output = '<a href="' . $title . '" class="add-new-h2">' . $title . '</a>';
		echo $output;

	}
	add_action( 'wpsc_purchase_logs_subtitle', 'wpsc_mo_purchase_logs_add_order' );

	function wpsc_mo_return_base_country() {

		$output = get_option( 'wpsc_base_country' );
		return $output;

	}

	function wpsc_mo_return_purchase_status() {

		global $wpsc_purchlog_statuses;

		$statuses = array();
		if( $wpsc_purchlog_statuses ) {
			$size = count( $wpsc_purchlog_statuses );
			for( $i = 0; $i < $size; $i++ ) {
				$statuses[] = array(
					'name' => $wpsc_purchlog_statuses[$i]['internalname'],
					'label' => $wpsc_purchlog_statuses[$i]['label'],
					'order' => $wpsc_purchlog_statuses[$i]['order'],
					'active' => 0
				);
			}
		}
		return $statuses;

	}

	function wpsc_mo_return_products() {

		$post_type = 'wpsc-product';
		$products = (array)get_posts( array( 
			'post_type' => $post_type,
			'numberposts' => -1,
			'post_status' => 'publish',
			'orderby' => 'menu_order',
			'order' => 'ASC'
		) );
		$size = count( $products );
		for( $i = 0; $i < $size; $i++ ) {
			$products[$i]->name = $products[$i]->post_title;
			$products[$i]->price = wpsc_currency_display( wpsc_calculate_price( $products[$i]->ID, null, true ) );
			$products[$i]->sku = get_product_meta( $products[$i]->ID, 'sku', true );
			if( !$products[$i]->sku )
				$products[$i]->sku = '-';
			$products[$i]->stock = get_product_meta( $products[$i]->ID, 'stock', true );
		}
		return $products;

	}

	function wpsc_mo_return_product( $product_id = null, $user_id = null ) {

		global $wpdb;

		if( $product_id ) {
			$product = get_post( $product_id );
			$product->name = $product->post_title;
			$product->price = get_product_meta( $product_id, 'price', true );
			$product->sale_price = get_product_meta( $product_id, 'special_price', true );
			if( $product->sale_price )
				$product->price = $product->sale_price;
			if( isset( $wpsc_mo['debug'] ) && $wpsc_mo['debug'] ) {
				error_log( 'Product: ' . $product_id );
				error_log( 'User: ' . $user_id );
			}
			if( function_exists( 'wpsc_wp_wholesale_price' ) && function_exists( 'wpsc_wp_get_user_role' ) ) {
				$user_role = wpsc_wp_get_user_role( $user_id );
				if( isset( $wpsc_mo['debug'] ) && $wpsc_mo['debug'] )
					error_log( 'User Role: ' . $user_role );
				if( $user_role )
					$product->price = wpsc_wp_wholesale_price( $product->price, $product_id, $user_role );
				if( isset( $wpsc_mo['debug'] ) && $wpsc_mo['debug'] )
					error_log( 'New Price: ' . $product->price );
			}
			$product->sku = get_product_meta( $product_id, 'sku', true );
			if( !$product->sku )
				$product->sku = '-';
			$product->stock = get_product_meta( $product_id, 'stock', true );
/*
			$product->subscription_capabilities = get_post_meta( $product_id, '_wpsc_product-capabilities', true );
			$product->membership_length = get_post_meta( $product_id, '_wpsc_membership_length', true );
*/
			$product_downloads = wpsc_mo_get_file_downloads( $product_id );
			if( $product_downloads ) {
				$product->downloads = '';
				foreach( $product_downloads as $product_download )
					$product->downloads .= $product_download->ID . ',';
				if( $product->downloads )
					$product->downloads = substr( $product->downloads, 0, -1 );
			}
			return $product;
		}

	}

	function wpsc_mo_get_file_downloads( $product_id = null ) {

		global $wpdb;

		$output = '';
		if( $product_id ) {
			$post_type = 'wpsc-product-file';
			$product_downloads_sql = $wpdb->prepare( "SELECT `ID` FROM `" . $wpdb->posts . "` WHERE `post_type` = '%s' AND `post_parent` = %d", $post_type, $product_id );
			$product_downloads = $wpdb->get_results( $product_downloads_sql );
			if( $product_downloads )
				$output = $product_downloads;
		}
		return $output;

	}

	function wpsc_mo_reduce_product_stock( $product_id, $cart_quantity ) {

		$product_quantity = get_product_meta( $product_id, 'stock', true );
		if( $product_quantity ) {
			$product_quantity = $product_quantity - $cart_quantity;
			update_post_meta( $product_id, '_wpsc_stock', $product_quantity );
		}

	}

	/* End of: WordPress Administration */

}
?>