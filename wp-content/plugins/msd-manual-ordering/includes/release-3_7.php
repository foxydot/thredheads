<?php
if( is_admin() ) {

	/* Start of: WordPress Administration */

	function wpsc_mo_return_base_country() {

		$output = get_option( 'base_country' );
		return $output;

	}

	function wpsc_mo_return_wpsc_purchlog_statuses() {

		$statuses = array();
		$statuses[] = array(
			'order' => 1,
			'label' => __( 'Order Received', 'wpsc_mo' )
		);
		$statuses[] = array(
			'order' => 2,
			'label' => __( 'Accepted Payment', 'wpsc_mo' )
		);
		$statuses[] = array(
			'order' => 3,
			'label' => __( 'Job Dispatched', 'wpsc_mo' )
		);
		$statuses[] = array(
			'order' => 4,
			'label' => __( 'Closed Order', 'wpsc_mo' )
		);
		return $statuses;

	}

	function wpsc_mo_return_products() {

		global $wpdb;

		$products_sql = "SELECT `id` as ID, `name`, `price`, `special`, `special_price` FROM " . $wpdb->prefix . "wpsc_product_list WHERE `active` = 1 ORDER BY `date_added`";
		$products = $wpdb->get_results( $products_sql );
		for( $i = 0; $i < count( $products ); $i++ ) {
			$products[$i]->sku = get_product_meta( $products[$i]->ID, 'sku', true );
			if( !$products[$i]->sku )
				$products[$i]->sku = '-';
			if( $products[$i]->special == '1' )
				$products[$i]->price = $products[$i]->special_price;
			$products[$i]->price = nzshpcrt_currency_display( $products[$i]->price, null, null, null );
		}
		return $products;

	}

	function wpsc_mo_return_product( $product_id ) {

		global $wpdb;

		$product_sql = $wpdb->prepare( "SELECT `id` as ID, `name`, `price`, `special_price` FROM " . $wpdb->prefix . "wpsc_product_list WHERE `id` = " . $product_id . " LIMIT 1" );
		$product = $wpdb->get_row( $product_sql );
		$product->sku = get_product_meta( $product->ID, 'sku', true );
		if( !$product->sku )
			$product->sku = '-';
		if( $product->special_price )
			$product->price = $product->special_price;
		return $product;

	}

	function wpsc_mo_return_sku( $product_id ) {

		$sku = get_product_meta( $product_id, 'sku',true );
		return $sku;

	}

	function wpsc_mo_reduce_product_stock( $product_id, $cart_quantity ) {

		global $wpdb;

		$product_quantity_sql = $wpdb->prepare( "SELECT `quantity` FROM `" . $wpdb->prefix . "wpsc_product_list` WHERE `id` = %d LIMIT 1", $product_id );
		$product_quantity = $wpdb->get_var( $product_quantity_sql );
		if( $product_quantity ) {
			$product_quantity = $product_quantity - $cart_quantity;
			$wpdb->update( $wpdb->prefix . 'wpsc_product_list', array(
				'quantity' => $product_quantity
			), array( 'id' => $product_id ) );
		}

	}

	/* End of: WordPress Administration */

}
?>