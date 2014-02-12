<?php
if( is_admin() ) {

	/* Start of: WordPress Administration */

	function wpsc_mo_status_layout( $layout = 'dropdown' ) {
	
		switch( $layout ) {

			case 'dropdown':
				$output = '1';
				break;

			case 'list':
				$output = '0';
				break;

		}
		$value = wpsc_mo_get_option( 'status_layout' );
		if( $output == $value )
			return true;

	}

	function wpsc_mo_default_status() {

		$output = wpsc_mo_get_option( 'default_status' );
		return $output;

	}

	function wpsc_mo_payment_method_layout( $layout = 'list' ) {
	
		switch( $layout ) {

			case 'dropdown':
				$output = '1';
				break;

			case 'list':
				$output = '0';
				break;

		}
		$value = wpsc_mo_get_option( 'payment_method_layout' );
		if( $output == $value )
			return true;

	}

	function wpsc_mo_default_payment_method() {

		$output = wpsc_mo_get_option( 'default_payment_method' );
		return $output;

	}

	function wpsc_mo_show_session_id() {

	global $wpsc_mo;

	$session_id = wpsc_mo_get_option( 'show_session_id' );

	if( $session_id )
		return true;

	}

	/* End of: WordPress Administration */

}
?>