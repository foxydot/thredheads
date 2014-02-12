<?php
/*

Filename: common.php
Description: common.php loads commonly accessed functions across the Visser Labs suite.

- wpsc_vl_plugin_update_prepare

- wpsc_vl_currency_display
- wpsc_vl_get_currency_display

- wpsc_is_admin_icon_valid
- wpsc_get_action
- wpsc_get_major_version
- wpsc_get_minor_version

*/

if( is_admin() ) {

	/* Start of: WordPress Administration */

	include_once( 'common-update.php' );
	include_once( 'common-dashboard_widgets.php' );

	if( !function_exists( 'wpsc_vl_plugin_update_prepare' ) ) {

		function wpsc_vl_plugin_update_prepare( $action, $args ) {

			global $wp_version;

			return array(
				'body' => array(
					'action' => $action,
					'request' => serialize( $args ),
					'api-key' => md5( get_bloginfo( 'url' ) ),
					'site' => get_bloginfo( 'url' )
				),
				'user-agent' => 'WordPress/' . $wp_version . '; ' . get_bloginfo( 'url' )
			);	

		}

	}

	if( !function_exists( 'wpsc_is_admin_icon_valid' ) ) {
		function wpsc_is_admin_icon_valid( $icon = 'tools' ) {

			switch( $icon ) {

				case 'index':
				case 'edit':
				case 'post':
				case 'link':
				case 'comments':
				case 'page':
				case 'users':
				case 'upload':
				case 'tools':
				case 'plugins':
				case 'themes':
				case 'profile':
				case 'admin':
					return $icon;
					break;

			}

		}
	}

	/* End of: WordPress Administration */

}

if( !function_exists( 'wpsc_vl_currency_display' ) ) {

	function wpsc_vl_currency_display( $price = null, $echo = true ) {

		if( !isset( $price ) )
			$price = wpsc_vl_product_price();
		$args = array(
			'price' => $price,
			'echo' => $echo
		);
		if( $echo )
			wpsc_vl_get_currency_display( $args );
		else
			return wpsc_vl_get_currency_display( $args );

	}

}

if( !function_exists( 'wpsc_vl_get_currency_display' ) ) {

	function wpsc_vl_get_currency_display( $args = null ) {

		$defaults = array(
			'price' => wpsc_vl_get_product_price( array( 'currency_display' => false ) ),
			'echo' => false
		);
		$args = wp_parse_args( $args, $defaults );
		extract( $args, EXTR_SKIP );

		switch( wpsc_get_major_version() ) {

			case '3.7':
				$output = nzshpcrt_currency_display( $price, true, true );
				break;

			case '3.8':
				$output = wpsc_currency_display( $price );
				break;

		}
		if( $echo )
			echo $output;
		else
			return $output;

	}

}

if( !function_exists( 'wpsc_get_action' ) ) {

	function wpsc_get_action( $switch = false ) {

		if( $switch ) {

			if( isset( $_GET['action'] ) )
				$action = $_GET['action'];
			else if( !isset( $action ) && isset( $_POST['action'] ) )
				$action = $_POST['action'];
			else
				$action = false;

		} else {

			if( isset( $_POST['action'] ) )
				$action = $_POST['action'];
			else if( !isset( $action ) && isset( $_GET['action'] ) )
				$action = $_GET['action'];
			else
				$action = false;

		}
		return $action;

	}

}

if( !function_exists( 'wpsc_get_major_version' ) ) {

	function wpsc_get_major_version() {

		$output = '';
		if( defined( 'WPSC_VERSION' ) )
			$version = WPSC_VERSION;
		else
			$version = get_option( 'wpsc_version' );
		if( $version )
			$output = substr( $version, 0, 3 );
		return $output;

	}

}

if( !function_exists( 'wpsc_get_minor_version' ) ) {

	function wpsc_get_minor_version() {

		$output = '';
		if( defined( 'WPSC_VERSION' ) )
			$version = WPSC_VERSION;
		else
			$version = get_option( 'wpsc_version' );
		if( $version )
			$output = $version;
		return $output;

	}

}
?>