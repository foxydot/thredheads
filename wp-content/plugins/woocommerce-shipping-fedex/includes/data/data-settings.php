<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

global $woocommerce;

$freight_classes = include( 'data-freight-classes.php' );
$smartpost_hubs  = include( 'data-smartpost-hubs.php' );
$smartpost_hubs  = array( '' => __( 'N/A', 'wc_fedex' ) ) + $smartpost_hubs;

/**
 * Array of settings
 */
return array(
	'enabled'          => array(
		'title'           => __( 'Enable FedEx', 'wc_fedex' ),
		'type'            => 'checkbox',
		'label'           => __( 'Enable this shipping method', 'wc_fedex' ),
		'default'         => 'no'
	),
	'debug'      => array(
		'title'           => __( 'Debug Mode', 'wc_fedex' ),
		'label'           => __( 'Enable debug mode', 'wc_fedex' ),
		'type'            => 'checkbox',
		'default'         => 'no',
		'description'     => __( 'Enable debug mode to show debugging information on the cart/checkout.', 'wc_fedex' )
	),
	'title'            => array(
		'title'           => __( 'Method Title', 'wc_fedex' ),
		'type'            => 'text',
		'description'     => __( 'This controls the title which the user sees during checkout.', 'wc_fedex' ),
		'default'         => __( 'FedEx', 'wc_fedex' ),
		'desc_tip'        => true
	),
	'origin'           => array(
		'title'           => __( 'Origin Postcode', 'wc_fedex' ),
		'type'            => 'text',
		'description'     => __( 'Enter the postcode for the <strong>sender</strong>.', 'wc_fedex' ),
		'default'         => '',
		'desc_tip'        => true
    ),
    'availability'  => array(
		'title'           => __( 'Method Availability', 'wc_fedex' ),
		'type'            => 'select',
		'default'         => 'all',
		'class'           => 'availability',
		'options'         => array(
			'all'            => __( 'All Countries', 'wc_fedex' ),
			'specific'       => __( 'Specific Countries', 'wc_fedex' ),
		),
	),
	'countries'        => array(
		'title'           => __( 'Specific Countries', 'wc_fedex' ),
		'type'            => 'multiselect',
		'class'           => 'chosen_select',
		'css'             => 'width: 450px;',
		'default'         => '',
		'options'         => $woocommerce->countries->get_allowed_countries(),
	),
    'api'              => array(
		'title'           => __( 'API Settings', 'wc_fedex' ),
		'type'            => 'title',
		'description'     => __( 'Your API access details are obtained from the FedEx website. After signup, get a <a href="https://www.fedex.com/wpor/web/jsp/drclinks.jsp?links=wss/develop.html">developer key here</a>. After testing you can get a <a href="https://www.fedex.com/wpor/web/jsp/drclinks.jsp?links=wss/production.html">production key here</a>.', 'wc_fedex' ),
    ),
    'account_number'           => array(
		'title'           => __( 'FedEx Account Number', 'wc_fedex' ),
		'type'            => 'text',
		'description'     => '',
		'default'         => ''
    ),
    'meter_number'           => array(
		'title'           => __( 'Fedex Meter Number', 'wc_fedex' ),
		'type'            => 'text',
		'description'     => '',
		'default'         => ''
    ),
    'api_key'           => array(
		'title'           => __( 'Web Services Key', 'wc_fedex' ),
		'type'            => 'text',
		'description'     => '',
		'default'         => '',
		'custom_attributes' => array(
			'autocomplete' => 'off'
		)
    ),
    'api_pass'           => array(
		'title'           => __( 'Web Services Password', 'wc_fedex' ),
		'type'            => 'password',
		'description'     => '',
		'default'         => '',
		'custom_attributes' => array(
			'autocomplete' => 'off'
		)
    ),
    'production'      => array(
		'title'           => __( 'Production Key', 'wc_fedex' ),
		'label'           => __( 'This is a production key', 'wc_fedex' ),
		'type'            => 'checkbox',
		'default'         => 'no',
		'description'     => __( 'If this is a production API key and not a developer key, check this box.', 'wc_fedex' )
	),
    'packing'           => array(
		'title'           => __( 'Packages', 'wc_fedex' ),
		'type'            => 'title',
		'description'     => __( 'The following settings determine how items are packed before being sent to FedEx.', 'wc_fedex' ),
    ),
	'packing_method'   => array(
		'title'           => __( 'Parcel Packing Method', 'wc_fedex' ),
		'type'            => 'select',
		'default'         => '',
		'class'           => 'packing_method',
		'options'         => array(
			'per_item'       => __( 'Default: Pack items individually', 'wc_fedex' ),
			'box_packing'    => __( 'Recommended: Pack into boxes with weights and dimensions', 'wc_fedex' ),
		),
	),
	'boxes'  => array(
		'type'            => 'box_packing'
	),
    'rates'           => array(
		'title'           => __( 'Rates and Services', 'wc_fedex' ),
		'type'            => 'title',
		'description'     => __( 'The following settings determine the rates you offer your customers.', 'wc_fedex' ),
    ),
    'residential'      => array(
		'title'           => __( 'Residential', 'wc_fedex' ),
		'label'           => __( 'Default to residential delivery', 'wc_fedex' ),
		'type'            => 'checkbox',
		'default'         => 'no',
		'description'     => __( 'Enables residential flag. If you account has Address Validation enabled, this will be turned off/on automatically.', 'wc_fedex' )
	),
    'insure_contents'      => array(
		'title'           => __( 'Insurance', 'wc_fedex' ),
		'label'           => __( 'Enable Insurance', 'wc_fedex' ),
		'type'            => 'checkbox',
		'default'         => 'yes',
		'description'     => __( 'Sends the package value to FedEx for insurance.', 'wc_fedex' )
	),
	'request_type'     => array(
		'title'           => __( 'Request Type', 'wc_fedex' ),
		'type'            => 'select',
		'default'         => 'LIST',
		'class'           => '',
		'desc_tip'        => true,
		'options'         => array(
			'LIST'        => __( 'List rates', 'wc_fedex' ),
			'ACCOUNT'     => __( 'Account rates', 'wc_fedex' ),
		),
		'description'     => __( 'Choose whether to return List or Account (discounted) rates from the API.', 'wc_fedex' )
	),
	'smartpost_hub'           => array(
		'title'           => __( 'Fedex SmartPost Hub', 'wc_fedex' ),
		'type'            => 'select',
		'description'     => __( 'Only required if using SmartPost.', 'wc_fedex' ),
		'desc_tip'        => true,
		'default'         => '',
		'options'         => $smartpost_hubs
    ),
	'offer_rates'   => array(
		'title'           => __( 'Offer Rates', 'wc_fedex' ),
		'type'            => 'select',
		'description'     => '',
		'default'         => 'all',
		'options'         => array(
		    'all'         => __( 'Offer the customer all returned rates', 'wc_fedex' ),
		    'cheapest'    => __( 'Offer the customer the cheapest rate only, anonymously', 'wc_fedex' ),
		),
    ),
	'services'  => array(
		'type'            => 'services'
	),
	'freight'           => array(
		'title'           => __( 'FedEx LTL Freight', 'wc_fedex' ),
		'type'            => 'title',
		'description'     => __( 'If your account supports Freight, we need some additional details to get LTL rates. Note: These rates require the customers CITY so won\'t display until checkout.', 'wc_fedex' ),
    ),
    'freight_enabled'      => array(
		'title'           => __( 'Enable', 'wc_fedex' ),
		'label'           => __( 'Enable Freight', 'wc_fedex' ),
		'type'            => 'checkbox',
		'default'         => 'no'
	),
	'freight_number' => array(
		'title'       => __( 'FedEx Freight Account Number', 'wc_fedex' ),
		'type'        => 'text',
		'description' => '',
		'default'     => '',
		'placeholder' => __( 'Defaults to your main account number', 'wc_fedex' )
	),
	'freight_billing_street'           => array(
		'title'           => __( 'Billing Street Address', 'wc_fedex' ),
		'type'            => 'text',
		'default'         => ''
    ),
    'freight_billing_street_2'           => array(
		'title'           => __( 'Billing Street Address', 'wc_fedex' ),
		'type'            => 'text',
		'default'         => ''
    ),
    'freight_billing_city'           => array(
		'title'           => __( 'Billing City', 'wc_fedex' ),
		'type'            => 'text',
		'default'         => ''
    ),
    'freight_billing_state'           => array(
		'title'           => __( 'Billing State Code', 'wc_fedex' ),
		'type'            => 'text',
		'default'         => '',
    ),
    'freight_billing_postcode'           => array(
		'title'           => __( 'Billing ZIP / Postcode', 'wc_fedex' ),
		'type'            => 'text',
		'default'         => '',
    ),
    'freight_billing_country'           => array(
		'title'           => __( 'Billing Country Code', 'wc_fedex' ),
		'type'            => 'text',
		'default'         => '',
    ),
    'freight_shipper_street'           => array(
		'title'           => __( 'Shipper Street Address', 'wc_fedex' ),
		'type'            => 'text',
		'default'         => ''
    ),
    'freight_shipper_street_2'           => array(
		'title'           => __( 'Shipper Street Address 2', 'wc_fedex' ),
		'type'            => 'text',
		'default'         => ''
    ),
    'freight_shipper_city'           => array(
		'title'           => __( 'Shipper City', 'wc_fedex' ),
		'type'            => 'text',
		'default'         => ''
    ),
    'freight_shipper_state'           => array(
		'title'           => __( 'Shipper State Code', 'wc_fedex' ),
		'type'            => 'text',
		'default'         => '',
    ),
    'freight_shipper_postcode'           => array(
		'title'           => __( 'Shipper ZIP / Postcode', 'wc_fedex' ),
		'type'            => 'text',
		'default'         => '',
    ),
    'freight_shipper_country'           => array(
		'title'           => __( 'Shipper Country Code', 'wc_fedex' ),
		'type'            => 'text',
		'default'         => '',
    ),
    'freight_shipper_residential'           => array(
    	'title'           => __( 'Residential', 'wc_fedex' ),
		'label'           => __( 'Shipper Address is Residential?', 'wc_fedex' ),
		'type'            => 'checkbox',
		'default'         => 'no'
    ),
    'freight_class'           => array(
		'title'           => __( 'Default Freight Class', 'wc_fedex' ),
		'description'     => sprintf( __( 'This is the default freight class for shipments. This can be overridden using <a href="%s">shipping classes</a>', 'wc_fedex' ), admin_url( 'edit-tags.php?taxonomy=product_shipping_class&post_type=product' ) ),
		'type'            => 'select',
		'default'         => '50',
		'options'         => $freight_classes
    ),
);