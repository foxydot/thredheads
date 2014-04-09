<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * WC_Shipping_Fedex class.
 *
 * @extends WC_Shipping_Method
 */
class WC_Shipping_Fedex extends WC_Shipping_Method {
	private $default_boxes;
	private $found_rates;
	private $services;

	/**
	 * Constructor
	 */
	public function __construct() {
		$this->id                               = 'fedex';
		$this->method_title                     = __( 'FedEx', 'wc_fedex' );
		$this->method_description               = __( 'The <strong>FedEx</strong> extension obtains rates dynamically from the FedEx API during cart/checkout.', 'wc_fedex' );
		$this->rateservice_version              = 13;
		$this->addressvalidationservice_version = 2;
		$this->default_boxes                    = include( 'data/data-box-sizes.php' );
		$this->services                         = include( 'data/data-service-codes.php' );
		$this->init();
	}

    /**
     * init function.
     */
    private function init() {
    	global $woocommerce;

		// Load the settings.
		$this->init_form_fields();
		$this->init_settings();

		// Define user set variables
		$this->title           = $this->get_option( 'title', $this->method_title );
		$this->availability    = $this->get_option( 'availability', 'all' );
		$this->countries       = $this->get_option( 'countries', array() );
		$this->origin          = $this->get_option( 'origin' );
		$this->account_number  = $this->get_option( 'account_number' );
		$this->meter_number    = $this->get_option( 'meter_number' );
		$this->smartpost_hub   = $this->get_option( 'smartpost_hub' );
		$this->api_key         = $this->get_option( 'api_key' );
		$this->api_pass        = $this->get_option( 'api_pass' );
		$this->production      = ( $bool = $this->get_option( 'production' ) ) && $bool == 'yes' ? true : false;
		$this->debug           = ( $bool = $this->get_option( 'debug' ) ) && $bool == 'yes' ? true : false;
		$this->insure_contents = ( $bool = $this->get_option( 'insure_contents' ) ) && $bool == 'yes' ? true : false;
		$this->request_type    = $this->get_option( 'request_type', 'LIST' );
		$this->packing_method  = $this->get_option( 'packing_method', 'per_item' );
		$this->boxes           = $this->get_option( 'boxes', array( ));
		$this->custom_services = $this->get_option( 'services', array( ));
		$this->offer_rates     = $this->get_option( 'offer_rates', 'all' );
		$this->residential     = ( $bool = $this->get_option( 'residential' ) ) && $bool == 'yes' ? true : false;
		$this->freight_enabled = ( $bool = $this->get_option( 'freight_enabled' ) ) && $bool == 'yes' ? true : false;

		if ( $this->freight_enabled ) {
			$this->freight_class               = $this->get_option( 'freight_class' );
			$this->freight_number              = $this->get_option( 'freight_number', $this->account_number );
			$this->freight_billing_street      = $this->get_option( 'freight_billing_street' );
			$this->freight_billing_street_2    = $this->get_option( 'freight_billing_street_2' );
			$this->freight_billing_city        = $this->get_option( 'freight_billing_city' );
			$this->freight_billing_state       = $this->get_option( 'freight_billing_state' );
			$this->freight_billing_postcode    = $this->get_option( 'freight_billing_postcode' );
			$this->freight_billing_country     = $this->get_option( 'freight_billing_country' );
			$this->freight_shipper_street      = $this->get_option( 'freight_shipper_street' );
			$this->freight_shipper_street_2    = $this->get_option( 'freight_shipper_street_2' );
			$this->freight_shipper_city        = $this->get_option( 'freight_shipper_city' );
			$this->freight_shipper_state       = $this->get_option( 'freight_shipper_state' );
			$this->freight_shipper_postcode    = $this->get_option( 'freight_shipper_postcode' );
			$this->freight_shipper_country     = $this->get_option( 'freight_shipper_country' );
			$this->freight_shipper_residential = ( $bool = $this->get_option( 'freight_shipper_residential' ) ) && $bool == 'yes' ? true : false;

			$this->freight_class = str_replace( array( 'CLASS_', '.' ), array( '', '_' ), $this->freight_class );
		}

		// Insure contents requires matching currency to country
		switch ( $woocommerce->countries->get_base_country() ) {
			case 'US' :
				if ( 'USD' !== get_woocommerce_currency() ) {
					$this->insure_contents = false;
				}
			break;
			case 'CA' :
				if ( 'CAD' !== get_woocommerce_currency() ) {
					$this->insure_contents = false;
				}
			break;
		}

		add_action( 'woocommerce_update_options_shipping_' . $this->id, array( $this, 'process_admin_options' ) );
	}

    /**
     * Output a message
     */
    public function debug( $message, $type = 'notice' ) {
    	if ( $this->debug ) {
    		if ( version_compare( WOOCOMMERCE_VERSION, '2.1', '>=' ) ) {
    			wc_add_notice( $message, $type );
    		} else {
    			global $woocommerce;

    			$woocommerce->add_message( $message );
    		}
		}
    }

	/**
	 * environment_check function.
	 */
	private function environment_check() {
		global $woocommerce;

		if ( ! in_array( get_woocommerce_currency(), array( 'USD', 'CAD' ) ) || ! in_array( $woocommerce->countries->get_base_country(), array( 'US', 'CA' ) ) ) {
			echo '<div class="error">
				<p>' . __( 'FedEx requires that the WooCommerce currency is set to US Dollars and that the base country/region is set to United States.', 'wc_fedex' ) . '</p>
			</div>';
		} elseif ( ! $this->origin && $this->enabled == 'yes' ) {
			echo '<div class="error">
				<p>' . __( 'FedEx is enabled, but the origin postcode has not been set.', 'wc_fedex' ) . '</p>
			</div>';
		}
	}

	/**
	 * admin_options function.
	 */
	public function admin_options() {
		// Check users environment supports this method
		$this->environment_check();

		// Show settings
		parent::admin_options();
	}

    /**
     * init_form_fields function.
     */
    public function init_form_fields() {
	    $this->form_fields  = include( 'data/data-settings.php' );
    }

	/**
	 * generate_services_html function.
	 */
	public function generate_services_html() {
		ob_start();
		include( 'views/html-services.php' );
		return ob_get_clean();
	}

	/**
	 * generate_box_packing_html function.
	 */
	public function generate_box_packing_html() {
		ob_start();
		include( 'views/html-box-packing.php' );
		return ob_get_clean();
	}

	/**
	 * validate_box_packing_field function.
	 *
	 * @param mixed $key
	 */
	public function validate_box_packing_field( $key ) {
		$boxes_length     = isset( $_POST['boxes_length'] ) ? $_POST['boxes_length'] : array();
		$boxes_width      = isset( $_POST['boxes_width'] ) ? $_POST['boxes_width'] : array();
		$boxes_height     = isset( $_POST['boxes_height'] ) ? $_POST['boxes_height'] : array();
		$boxes_box_weight = isset( $_POST['boxes_box_weight'] ) ? $_POST['boxes_box_weight'] : array();
		$boxes_max_weight = isset( $_POST['boxes_max_weight'] ) ? $_POST['boxes_max_weight'] :  array();
		$boxes_enabled    = isset( $_POST['boxes_enabled'] ) ? $_POST['boxes_enabled'] : array();

		$boxes = array();

		if ( ! empty( $boxes_length ) && sizeof( $boxes_length ) > 0 ) {
			for ( $i = 0; $i <= max( array_keys( $boxes_length ) ); $i ++ ) {

				if ( ! isset( $boxes_length[ $i ] ) )
					continue;

				if ( $boxes_length[ $i ] && $boxes_width[ $i ] && $boxes_height[ $i ] ) {

					$boxes[] = array(
						'length'     => floatval( $boxes_length[ $i ] ),
						'width'      => floatval( $boxes_width[ $i ] ),
						'height'     => floatval( $boxes_height[ $i ] ),
						'box_weight' => floatval( $boxes_box_weight[ $i ] ),
						'max_weight' => floatval( $boxes_max_weight[ $i ] ),
						'enabled'    => isset( $boxes_enabled[ $i ] ) ? true : false
					);
				}
			}
			foreach ( $this->default_boxes as $box ) {
				$boxes[ $box['id'] ] = array(
					'enabled' => isset( $boxes_enabled[ $box['id'] ] ) ? true : false
				);
			}
		}
		return $boxes;
	}

	/**
	 * validate_services_field function.
	 *
	 * @param mixed $key
	 */
	public function validate_services_field( $key ) {
		$services         = array();
		$posted_services  = $_POST['fedex_service'];

		foreach ( $posted_services as $code => $settings ) {
			$services[ $code ] = array(
				'name'               => woocommerce_clean( $settings['name'] ),
				'order'              => woocommerce_clean( $settings['order'] ),
				'enabled'            => isset( $settings['enabled'] ) ? true : false,
				'adjustment'         => woocommerce_clean( $settings['adjustment'] ),
				'adjustment_percent' => str_replace( '%', '', woocommerce_clean( $settings['adjustment_percent'] ) )
			);
		}

		return $services;
	}

    /**
     * Get packages - divide the WC package into packages/parcels suitable for a FEDEX quote
     */
    public function get_fedex_packages( $package ) {
    	switch ( $this->packing_method ) {
	    	case 'box_packing' :
	    		return $this->box_shipping( $package );
	    	break;
	    	case 'per_item' :
	    	default :
	    		return $this->per_item_shipping( $package );
	    	break;
    	}
    }

    /**
     * Get the freight class
     * @param  int $shipping_class_id
     * @return string
     */
    public function get_freight_class( $shipping_class_id ) {
    	$class = get_woocommerce_term_meta( $shipping_class_id, 'fedex_freight_class', true );
    	return $class ? $class : '';
    }

    /**
     * per_item_shipping function.
     *
     * @access private
     * @param mixed $package
     * @return array
     */
    private function per_item_shipping( $package ) {
	    $to_ship  = array();
	    $group_id = 1;

    	// Get weight of order
    	foreach ( $package['contents'] as $item_id => $values ) {

    		if ( ! $values['data']->needs_shipping() ) {
    			$this->debug( sprintf( __( 'Product # is virtual. Skipping.', 'wc_fedex' ), $item_id ), 'error' );
    			continue;
    		}

    		if ( ! $values['data']->get_weight() ) {
	    		$this->debug( sprintf( __( 'Product # is missing weight. Aborting.', 'wc_fedex' ), $item_id ), 'error' );
	    		return;
    		}

    		$group = array();

    		$group = array(
				'GroupNumber'       => $group_id,
				'GroupPackageCount' => $values['quantity'],
				'Weight' => array(
					'Value' => max( '0.5', round( woocommerce_get_weight( $values['data']->get_weight(), 'lbs' ), 2 ) ),
					'Units' => 'LB'
		    	),
		    	'packed_products' => array( $values['data'] )
    		);

			if ( $values['data']->length && $values['data']->height && $values['data']->width ) {

				$dimensions = array( $values['data']->length, $values['data']->width, $values['data']->height );

				sort( $dimensions );

				$group['Dimensions'] = array(
					'Length' => max( 1, round( woocommerce_get_dimension( $dimensions[2], 'in' ), 2 ) ),
					'Width'  => max( 1, round( woocommerce_get_dimension( $dimensions[1], 'in' ), 2 ) ),
					'Height' => max( 1, round( woocommerce_get_dimension( $dimensions[0], 'in' ), 2 ) ),
					'Units'  => 'IN'
				);
			}

			$group['InsuredValue'] = array( 
				'Amount'   => round( $values['data']->get_price() * $values['quantity'] ), 
				'Currency' => get_woocommerce_currency() 
			);

			$to_ship[] = $group;

			$group_id++;
    	}

		return $to_ship;
    }

    /**
     * box_shipping function.
     *
     * @access private
     * @param mixed $package
     * @return array
     */
    private function box_shipping( $package ) {
	  	if ( ! class_exists( 'WC_Boxpack' ) )
	  		include_once 'box-packer/class-wc-boxpack.php';

	    $boxpack = new WC_Boxpack();

	    // Merge default boxes
	    foreach ( $this->default_boxes as $key => $box ) {
	    	$box['enabled'] = isset( $this->boxes[ $box['id'] ]['enabled'] ) ? $this->boxes[ $box['id'] ]['enabled'] : true;
		 	$this->boxes[] = $box;
	    }

	    // Define boxes
		foreach ( $this->boxes as $key => $box ) {
			if ( ! is_numeric( $key ) )
				continue;

			if ( ! $box['enabled'] )
				continue;

			$newbox = $boxpack->add_box( $box['length'], $box['width'], $box['height'], $box['box_weight'] );

			if ( isset( $box['id'] ) )
				$newbox->set_id( $box['id'] );

			if ( $box['max_weight'] )
				$newbox->set_max_weight( $box['max_weight'] );
		}

		// Add items
		foreach ( $package['contents'] as $item_id => $values ) {

			if ( ! $values['data']->needs_shipping() ) {
    			$this->debug( sprintf( __( 'Product # is virtual. Skipping.', 'wc_fedex' ), $item_id ), 'error' );
    			continue;
    		}

			if ( $values['data']->length && $values['data']->height && $values['data']->width && $values['data']->weight ) {

				$dimensions = array( $values['data']->length, $values['data']->height, $values['data']->width );

				for ( $i = 0; $i < $values['quantity']; $i ++ ) {
					$boxpack->add_item(
						woocommerce_get_dimension( $dimensions[2], 'in' ),
						woocommerce_get_dimension( $dimensions[1], 'in' ),
						woocommerce_get_dimension( $dimensions[0], 'in' ),
						woocommerce_get_weight( $values['data']->get_weight(), 'lbs' ),
						$values['data']->get_price(),
						array(
							'data' => $values['data']
						)
					);
				}

			} else {
				$this->debug( sprintf( __( 'Product # is missing dimensions. Aborting.', 'wc_fedex' ), $item_id ), 'error' );
				return;
			}
		}

		// Pack it
		$boxpack->pack();

		// Get packages
		$packages = $boxpack->get_packages();
		$to_ship  = array();
		$group_id = 1;

		foreach ( $packages as $package ) {

			$dimensions = array( $package->length, $package->width, $package->height );

			sort( $dimensions );

    		$group = array(
				'GroupNumber'       => $group_id,
				'GroupPackageCount' => 1,
				'Weight' => array(
					'Value' => max( '0.5', round( $package->weight, 2 ) ),
					'Units' => 'LB'
		    	),
		    	'Dimensions'        => array(
					'Length' => max( 1, round( $dimensions[2], 2 ) ),
					'Width'  => max( 1, round( $dimensions[1], 2 ) ),
					'Height' => max( 1, round( $dimensions[0], 2 ) ),
					'Units'  => 'IN'
				),
				'InsuredValue'      => array( 
					'Amount'   => round( $package->value ), 
					'Currency' => get_woocommerce_currency() 
				),
				'packed_products' => array()
    		);

    		if ( ! empty( $package->packed ) && is_array( $package->packed ) ) {
	    		foreach ( $package->packed as $packed ) {
	    			$group['packed_products'][] = $packed->get_meta( 'data' );
	    		}
	    	}

    		if ( $this->freight_enabled ) {
    			$highest_freight_class = '';

    			if ( ! empty( $package->packed ) && is_array( $package->packed ) ) {
	    			foreach( $package->packed as $item ) {
	    				if ( $item->get_meta( 'data' )->get_shipping_class_id() ) {
	    					$freight_class = $this->get_freight_class( $item->get_meta( 'data' )->get_shipping_class_id() );

		    				if ( $freight_class > $highest_freight_class ) {
		    					$highest_freight_class = $freight_class;
		    				}
		    			}
	    			}
	    		}

    			$group['freight_class'] = $highest_freight_class ? $highest_freight_class : '';
    		}

    		$to_ship[] = $group;

    		$group_id++;
		}

		return $to_ship;
    }

    /**
     * See if address is residential
     */
    public function residential_address_validation( $package ) {
		$residential = $this->residential;

	    // Address Validation API only available for production
	    if ( $this->production ) {

		    // Check if address is residential or commerical
	    	try {

				$client = new SoapClient( plugin_dir_path( dirname( __FILE__ ) ) . 'api/production/AddressValidationService_v' . $this->addressvalidationservice_version. '.wsdl', array( 'trace' => 1 ) );

				$request = array();

				$request['WebAuthenticationDetail'] = array(
					'UserCredential' => array(
						'Key'      => $this->api_key,
						'Password' => $this->api_pass
					)
				);
				$request['ClientDetail'] = array(
					'AccountNumber' => $this->account_number,
					'MeterNumber'   => $this->meter_number
				);
				$request['TransactionDetail'] = array( 'CustomerTransactionId' => ' *** Address Validation Request v2 from WooCommerce ***' );
				$request['Version'] = array( 'ServiceId' => 'aval', 'Major' => $this->addressvalidationservice_version, 'Intermediate' => '0', 'Minor' => '0' );
				$request['RequestTimestamp'] = date( 'c' );
				$request['Options'] = array(
					'CheckResidentialStatus' => 1,
					'MaximumNumberOfMatches' => 1,
					'StreetAccuracy' => 'LOOSE',
					'DirectionalAccuracy' => 'LOOSE',
					'CompanyNameAccuracy' => 'LOOSE',
					'ConvertToUpperCase' => 1,
					'RecognizeAlternateCityNames' => 1,
					'ReturnParsedElements' => 1
				);
				$request['AddressesToValidate'] = array(
					0 => array(
						'AddressId' => 'WTC',
						'Address' => array(
							'StreetLines' => array( $package['destination']['address'], $package['destination']['address_2'] ),
							'PostalCode'  => $package['destination']['postcode'],
						)
					)
				);

				$response = $client->addressValidation( $request );

				if ( $response->HighestSeverity == 'SUCCESS' ) {
					if ( is_array( $response->AddressResults ) )
						$addressResult = $response->AddressResults[0];
					else
						$addressResult = $response->AddressResults;

	        		if ( $addressResult->ProposedAddressDetails->ResidentialStatus == 'BUSINESS' )
		        		$residential = false;
	        		elseif ( $addressResult->ProposedAddressDetails->ResidentialStatus == 'RESIDENTIAL' )
		        		$residential = true;
	        	}

			} catch (Exception $e) {}

		}

		$this->residential = apply_filters( 'woocommerce_fedex_address_type', $residential, $package );

		if ( $this->residential == false )
    		$this->debug( __( 'Business Address', 'wc_fedex' ) );    	
    }

    /**
     * get_fedex_api_request function.
     *
     * @access private
     * @param mixed $package
     * @return array
     */
    private function get_fedex_api_request( $package ) {
		global $woocommerce;

		$request = array();

		// Prepare Shipping Request for FedEx
		$request['WebAuthenticationDetail'] = array(
			'UserCredential' => array(
				'Key'      => $this->api_key,
				'Password' => $this->api_pass
			)
		);
		$request['ClientDetail'] = array(
			'AccountNumber' => $this->account_number,
			'MeterNumber'   => $this->meter_number
		);
		$request['TransactionDetail'] = array(
			'CustomerTransactionId'     => ' *** WooCommerce Rate Request ***'
		);
        $request['Version'] = array(
			'ServiceId'              => 'crs',
		    'Major'                  => $this->rateservice_version,
		    'Intermediate'           => '0',
		    'Minor'                  => '0'
		);
		$request['ReturnTransitAndCommit'] = true;
		$request['RequestedShipment']['DropoffType'] = 'REGULAR_PICKUP';
		$request['RequestedShipment']['ShipTimestamp'] = date( 'c' , strtotime( '+1 Weekday' ) );
		$request['RequestedShipment']['PackagingType'] = 'YOUR_PACKAGING';
		$request['RequestedShipment']['Shipper'] = array(
		    'Address'               => array(
				'PostalCode'              => str_replace( ' ', '', strtoupper( $this->origin ) ),
				'CountryCode'             => $woocommerce->countries->get_base_country(),
		    )
		);
		$request['RequestedShipment']['ShippingChargesPayment'] = array(
			'PaymentType' => 'SENDER',
            'Payor' => array(
				'ResponsibleParty' => array(
					'AccountNumber'           => $this->account_number,
					'CountryCode'             => $woocommerce->countries->get_base_country()
				)
			)
		);
		$request['RequestedShipment']['RateRequestTypes'] = $this->request_type;
		$request['RequestedShipment']['PackageDetail'] = 'INDIVIDUAL_PACKAGES';

		// SMART_POST
		if ( ! empty( $this->smartpost_hub ) && $package['destination']['country'] == 'US' ) {
			// Fix old hub IDs
			if ( $this->smartpost_hub < 1000 ) {
				$smartpost_hubs      = array_values( include( 'data/data-smartpost-hubs.php' ) );

				if ( isset( $smartpost_hubs[ $this->smartpost_hub ] ) )
					$this->smartpost_hub = $smartpost_hubs[ $this->smartpost_hub ];
			}
			$request['RequestedShipment']['SmartPostDetail'] = array(
				'Indicia'              => 'PARCEL_SELECT',
				'HubId'                => $this->smartpost_hub,
				'AncillaryEndorsement' => 'ADDRESS_CORRECTION',
				'SpecialServices'      => ''
			);
		}

		return $request;
    }

    /**
     * get_fedex_requests function.
     *
     * @access private
     * @return void
     */
    private function get_fedex_requests( $fedex_packages, $package ) {
	   	global $woocommerce;

	    $requests    = array();

	    // All reguests for this package get this data
	    $package_request = $this->get_fedex_api_request( $package );
	    $package_request['RequestedShipment']['Recipient'] = array(
			'Address' => array(
				'Residential' => $this->residential,
				'PostalCode'  => str_replace( ' ', '', strtoupper( $package['destination']['postcode'] ) ),
				'CountryCode' => $package['destination']['country']
			)
	    );

		// Add state to US/Canadian requests
		if ( in_array( $package['destination']['country'], array( 'US', 'CA' ) ) )
			$package_request['RequestedShipment']['Recipient']['Address']['StateOrProvinceCode'] = $package['destination']['state'];

    	if ( $fedex_packages ) {
	    	// Max 99
	    	$parcel_chunks = array_chunk( $fedex_packages, 99 );

	    	foreach ( $parcel_chunks as $parcels ) {

	    		// Make request
	    		$request = $package_request;

	    		// Store value
	    		$total_value    = 0;
	    		$total_packages = 0;
	    		$total_weight   = 0;
	    		$commodoties    = array();

	    		// Store parcels as line items
	    		$request['RequestedShipment']['RequestedPackageLineItems'] = array();

	    		foreach ( $parcels as $key => $parcel ) {
	    			$parcel_request  = $parcel;
		    		$total_value    += $parcel['InsuredValue']['Amount'];
		    		$total_packages += $parcel['GroupPackageCount'];
		    		$total_weight   += $parcel['Weight']['Value'];

		    		if ( $parcel_request['packed_products'] ) {
		    			foreach ( $parcel_request['packed_products'] as $product ) {
		    				if ( isset( $commodoties[ $product->id ] ) ) {
		    					$commodoties[ $product->id ]['Quantity'] ++;
		    					$commodoties[ $product->id ]['CustomsValue']['Amount'] += round( $product->get_price() );
		    					continue;
		    				}

				    		$commodoties[ $product->id ] = array(
				    			'Name'                 => $product->get_title(), 
								'NumberOfPieces'       => 1,
								'Description'          => '',
								'CountryOfManufacture' => ( $country = get_post_meta( $product->id, 'CountryOfManufacture', true ) ) ? $country : $woocommerce->countries->get_base_country(),
								'Weight'               => array(
									'Units'            => 'LB',
									'Value'            => max( '0.5', round( woocommerce_get_weight( $product->get_weight(), 'lbs' ), 2 ) ),
								),
								'Quantity'             => 1,
								'UnitPrice'            => array(
									'Amount'           => round( $product->get_price() ),
									'Currency'         => get_woocommerce_currency() 
								),
								'CustomsValue'         => array(
									'Amount'           => round( $product->get_price() ),
									'Currency'         => get_woocommerce_currency() 
								)
				    		);
				    	}
				    }

		    		unset( $parcel_request['freight_class'] );
		    		unset( $parcel_request['packed_products'] );

		    		if ( ! $this->insure_contents )
		    			unset( $parcel_request['InsuredValue'] );

		    		$parcel_request = array_merge( array( 'SequenceNumber' => $key + 1 ), $parcel_request );
		    		$request['RequestedShipment']['RequestedPackageLineItems'][] = $parcel_request;
	    		}

	    		// Add insurance
	    		if ( $this->insure_contents )
					$request['RequestedShipment']['TotalInsuredValue'] = array( 
						'Amount' => round( $total_value ), 
						'Currency' => get_woocommerce_currency() 
					);

				// Size
	    		$request['RequestedShipment']['PackageCount'] = $total_packages;

				// Canada broker fees
				if ( $package['destination']['country'] == 'CA' && $woocommerce->countries->get_base_country() == 'US' ) {
					$request['RequestedShipment']['CustomsClearanceDetail']['DutiesPayment'] = array(
						'PaymentType' => 'SENDER',
			            'Payor' => array(
							'ResponsibleParty' => array(
								'AccountNumber'           => strtoupper( $this->account_number ),
								'CountryCode'             => $woocommerce->countries->get_base_country()
							)
						)
					);
					$request['RequestedShipment']['CustomsClearanceDetail']['Commodities'] = array_values( $commodoties );
				}

	    		// Add request
	    		$requests[] = $request;
	    	}
    	}

    	return $requests;
    }

    /**
     * get_freight_requests function.
     *
     * @access private
     * @return void
     */
    private function get_freight_requests( $fedex_packages, $package ) {
    	global $woocommerce;

    	if ( ! $this->freight_enabled )
    		return false;

	    global $woocommerce;

	    $requests    = array();

	    // All reguests for this package get this data
	    $package_request = $this->get_fedex_api_request( $package );
	    $package_request['RequestedShipment']['Recipient'] = array(
			'Address' => array(
				'Residential' => $this->residential,
				'PostalCode'  => str_replace( ' ', '', strtoupper( $package['destination']['postcode'] ) ),
				'CountryCode' => $package['destination']['country']
			)
	    );

		// Add state to US/Canadian requests
		if ( in_array( $package['destination']['country'], array( 'US', 'CA' ) ) )
			$package_request['RequestedShipment']['Recipient']['Address']['StateOrProvinceCode'] = $package['destination']['state'];

    	if ( $fedex_packages ) {
	    	// Max 99
	    	$parcel_chunks = array_chunk( $fedex_packages, 99 );

	    	foreach ( $parcel_chunks as $parcels ) {

	    		// Make request
	    		$request = $package_request;

	    		// Store value
	    		$total_value    = 0;
	    		$total_packages = 0;
	    		$total_weight   = 0;
	    		$package_freight_class = '';

	    		// Store parcels as line items
	    		$request['RequestedShipment']['RequestedPackageLineItems'] = array();

	    		foreach ( $parcels as $key => $parcel ) {
	    			$parcel_request  = $parcel;
		    		$total_value    += $parcel['InsuredValue']['Amount'];
		    		$total_packages += $parcel['GroupPackageCount'];
		    		$total_weight   += $parcel['Weight']['Value'];

		    		if ( isset( $parcel['freight_class'] ) && $parcel['freight_class'] > $package_freight_class ) {
		    			$package_freight_class = $parcel['freight_class'];
		    		}

		    		unset( $parcel_request['freight_class'] );

		    		if ( ! $this->insure_contents )
		    			unset( $parcel_request['InsuredValue'] );

		    		$parcel_request = array_merge( array( 'SequenceNumber' => $key + 1 ), $parcel_request );
		    		$request['RequestedShipment']['RequestedPackageLineItems'][] = $parcel_request;
	    		}

	    		// Add insurance
	    		if ( $this->insure_contents )
					$request['RequestedShipment']['TotalInsuredValue'] = array( 
						'Amount' => round( $total_value ), 
						'Currency' => get_woocommerce_currency() 
					);

				// Size
	    		$request['RequestedShipment']['PackageCount'] = $total_packages;

				$request['RequestedShipment']['Shipper'] = array(
				    'Address'               => array(
				    	'StreetLines'         => array( strtoupper( $this->freight_shipper_street ), strtoupper( $this->freight_shipper_street_2 ) ),
						'City'                => strtoupper( $this->freight_shipper_city ),
						'StateOrProvinceCode' => strtoupper( $this->freight_shipper_state ),
						'PostalCode'          => strtoupper( $this->freight_shipper_postcode ),
						'CountryCode'         => strtoupper( $this->freight_shipper_country ),
						'Residential'         => $this->freight_shipper_residential
				    )
				);
		    	$request['CarrierCodes'] = 'FXFR';
		    	$request['RequestedShipment']['FreightShipmentDetail'] = array(
					'FedExFreightAccountNumber'            => strtoupper( $this->freight_number ),
					'FedExFreightBillingContactAndAddress' => array(
						'Address'                             => array(
							'StreetLines'                        => array( strtoupper( $this->freight_billing_street ), strtoupper( $this->freight_billing_street_2 ) ),
							'City'                               => strtoupper( $this->freight_billing_city ),
							'StateOrProvinceCode'                => strtoupper( $this->freight_billing_state ),
							'PostalCode'                         => strtoupper( $this->freight_billing_postcode ),
							'CountryCode'                        => strtoupper( $this->freight_billing_country )
						)
					),
					'Role'                                 => 'SHIPPER',
					'PaymentType'                          => 'PREPAID',
				);

		    	// Format freight class
		    	$freight_class = $package_freight_class ? $package_freight_class : $this->freight_class;
		    	if ( $freight_class < 100 ) {
		    		$freight_class = '0' . $freight_class;
		    	}
		    	$freight_class = 'CLASS_' . str_replace( '.', '_', $freight_class );

				$request['RequestedShipment']['FreightShipmentDetail']['LineItems'] = array(
					'FreightClass' => $freight_class,
					'Packaging'    => 'SKID',
					'Weight'       => array(
						'Units'    => 'LB',
						'Value'    => round( $total_weight, 2 )
					)
				);
				$request['RequestedShipment']['ShippingChargesPayment'] = array(
					'PaymentType' => 'SENDER',
		            'Payor' => array(
						'ResponsibleParty' => array(
							'AccountNumber'           => strtoupper( $this->freight_number ),
							'CountryCode'             => $woocommerce->countries->get_base_country()
						)
					)
				);
				$request['RequestedShipment']['Recipient']['Address']['City'] = strtoupper( $package['destination']['city'] );

				$requests[] = $request;
	    	}
    	}

    	return $requests;
    }

    /**
     * calculate_shipping function.
     *
     * @param mixed $package
     */
    public function calculate_shipping( $package ) {
    	// Clear rates
    	$this->found_rates = array();

    	// Debugging
    	$this->debug( __( 'FEDEX debug mode is on - to hide these messages, turn debug mode off in the settings.', 'wc_fedex' ) );

    	// See if address is residential
    	$this->residential_address_validation( $package );

		// Get requests		
		$fedex_packages    = $this->get_fedex_packages( $package );
		$fedex_requests    = $this->get_fedex_requests( $fedex_packages, $package );
		$freight_requests  = $this->get_freight_requests( $fedex_packages, $package );
		
		if ( $fedex_requests ) {
			$this->run_package_request( $fedex_requests );
		}

		if ( $freight_requests ) {
			$this->run_package_request( $freight_requests );
		}

		// Ensure rates were found for all packages
		$packages_to_quote_count = sizeof( $fedex_requests );

		if ( $this->found_rates ) {
			foreach ( $this->found_rates as $key => $value ) {
				if ( $value['packages'] < $packages_to_quote_count )
					unset( $this->found_rates[ $key ] );
			}
		}

		$this->add_found_rates();
    }

    /**
     * Run requests and get/parse results
     * @param  array $requests
     */
    public function run_package_request( $requests ) {
    	try {
			
			foreach ( $requests as $key => $request )
	    		$this->process_result( $this->get_result( $request ) );

		} catch ( Exception $e ) {
			$this->debug( print_r( $e, true ), 'error' );
			return false;
		}
    }

    /**
     * get_result function.
     *
     * @access private
     * @param mixed $request
     * @return array
     */
    private function get_result( $request ) {
		$this->debug( 'FedEx REQUEST: <pre style="height:200px; overflow-y:scroll; border:1px solid #000000; padding:5px;">' . print_r( $request, true ) . '</pre>' );

		$client = new SoapClient( plugin_dir_path( dirname( __FILE__ ) ) . 'api/' . ( $this->production ? 'production' : 'test' ) . '/RateService_v' . $this->rateservice_version. '.wsdl', array( 'trace' => 1 ) );
    	$result = $client->getRates( $request );

		$this->debug( 'FedEx RESPONSE: <pre style="height:200px; overflow-y:scroll; border:1px solid #000000; padding:5px;">' . print_r( $result, true ) . '</pre>' );

		return $result;
    }

    /**
     * process_result function.
     *
     * @access private
     * @param mixed $result
     * @return void
     */
    private function process_result( $result = '' ) {
	    if ( $result && ! empty ( $result->RateReplyDetails ) ) {

			$rate_reply_details = $result->RateReplyDetails;

			// Workaround for when an object is returned instead of array
			if ( is_object( $rate_reply_details ) && isset( $rate_reply_details->ServiceType ) )
				$rate_reply_details = array( $rate_reply_details );

			if ( ! is_array( $rate_reply_details ) )
				return false;

			foreach ( $rate_reply_details as $quote ) {

				if ( is_array( $quote->RatedShipmentDetails ) ) {

					if ( $this->request_type == "LIST" ) {
						// LIST quotes return both ACCOUNT rates (in RatedShipmentDetails[1])
						// and LIST rates (in RatedShipmentDetails[3])
						foreach ( $quote->RatedShipmentDetails as $i => $d ) {
							if ( strstr( $d->ShipmentRateDetail->RateType, 'PAYOR_LIST' ) ) {
								$details = $quote->RatedShipmentDetails[ $i ];
								break;
							}
						}
					} else {
						// ACCOUNT quotes may return either ACCOUNT rates only OR
         				// ACCOUNT rates and LIST rates.
						foreach ( $quote->RatedShipmentDetails as $i => $d ) {
							if ( strstr( $d->ShipmentRateDetail->RateType, 'PAYOR_ACCOUNT' ) ) {
								$details = $quote->RatedShipmentDetails[ $i ];
								break;
							}
						}
					}

				} else {
					$details = $quote->RatedShipmentDetails;
				}

				if ( empty( $details ) )
					continue;

				$rate_code = strval( $quote->ServiceType );
				$rate_id   = $this->id . ':' . $rate_code;
				$rate_name = strval( $this->services[ $quote->ServiceType ] );
				$rate_cost = floatval( $details->ShipmentRateDetail->TotalNetCharge->Amount );

				$this->prepare_rate( $rate_code, $rate_id, $rate_name, $rate_cost );
			}
		}
    }

    /**
     * prepare_rate function.
     *
     * @access private
     * @param mixed $rate_code
     * @param mixed $rate_id
     * @param mixed $rate_name
     * @param mixed $rate_cost
     */
    private function prepare_rate( $rate_code, $rate_id, $rate_name, $rate_cost ) {

	    // Name adjustment
		if ( ! empty( $this->custom_services[ $rate_code ]['name'] ) )
			$rate_name = $this->custom_services[ $rate_code ]['name'];

		// Cost adjustment %
		if ( ! empty( $this->custom_services[ $rate_code ]['adjustment_percent'] ) )
			$rate_cost = $rate_cost + ( $rate_cost * ( floatval( $this->custom_services[ $rate_code ]['adjustment_percent'] ) / 100 ) );
		// Cost adjustment
		if ( ! empty( $this->custom_services[ $rate_code ]['adjustment'] ) )
			$rate_cost = $rate_cost + floatval( $this->custom_services[ $rate_code ]['adjustment'] );

		// Enabled check
		if ( isset( $this->custom_services[ $rate_code ] ) && empty( $this->custom_services[ $rate_code ]['enabled'] ) )
			return;

		// Merging
		if ( isset( $this->found_rates[ $rate_id ] ) ) {
			$rate_cost = $rate_cost + $this->found_rates[ $rate_id ]['cost'];
			$packages  = 1 + $this->found_rates[ $rate_id ]['packages'];
		} else {
			$packages  = 1;
		}

		// Sort
		if ( isset( $this->custom_services[ $rate_code ]['order'] ) ) {
			$sort = $this->custom_services[ $rate_code ]['order'];
		} else {
			$sort = 999;
		}

		$this->found_rates[ $rate_id ] = array(
			'id'       => $rate_id,
			'label'    => $rate_name,
			'cost'     => $rate_cost,
			'sort'     => $sort,
			'packages' => $packages
		);
    }

    /**
     * Add found rates to WooCommerce
     */
    public function add_found_rates() {
    	if ( $this->found_rates ) {

			if ( $this->offer_rates == 'all' ) {

				uasort( $this->found_rates, array( $this, 'sort_rates' ) );

				foreach ( $this->found_rates as $key => $rate ) {
					$this->add_rate( $rate );
				}

			} else {

				$cheapest_rate = '';

				foreach ( $this->found_rates as $key => $rate ) {
					if ( ! $cheapest_rate || $cheapest_rate['cost'] > $rate['cost'] )
						$cheapest_rate = $rate;
				}

				$cheapest_rate['label'] = $this->title;

				$this->add_rate( $cheapest_rate );
			}
		}
    }

    /**
     * sort_rates function.
     *
     * @param mixed $a
     * @param mixed $b
     * @return int
     */
    public function sort_rates( $a, $b ) {
		if ( $a['sort'] == $b['sort'] ) return 0;
		return ( $a['sort'] < $b['sort'] ) ? -1 : 1;
    }
}