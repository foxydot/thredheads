<?php
function wpsc_mo_install() {

	wpsc_mo_create_options();
	wpsc_mo_assign_capabilities();

}

function wpsc_mo_create_options() {

	$prefix = 'wpsc_mo';

	if( !get_option( $prefix . '_status_layout' ) )
		add_option( $prefix . '_status_layout', 0 );
	if( !get_option( $prefix . '_default_status' ) )
		add_option( $prefix . '_default_status', 3 );
	if( !get_option( $prefix . '_payment_method_layout' ) )
		add_option( $prefix . '_payment_method_layout', 1 );
	if( !get_option( $prefix . '_default_payment_method' ) )
		add_option( $prefix .'_default_payment_method', 'wpsc_merchant_testmode' );

}

function wpsc_mo_assign_capabilities() {

	$role = get_role( 'administrator' );
	if( $role )
		$role->add_cap( 'add_sales' );
	update_option( 'wpsc_mo_cap_added', true );

}
?>