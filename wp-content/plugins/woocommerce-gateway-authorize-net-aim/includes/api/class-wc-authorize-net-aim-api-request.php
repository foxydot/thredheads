<?php
/**
 * WooCommerce Authorize.net AIM Gateway
 *
 * This source file is subject to the GNU General Public License v3.0
 * that is bundled with this package in the file license.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.gnu.org/licenses/gpl-3.0.html
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@skyverge.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade WooCommerce Authorize.net AIM Gateway to newer
 * versions in the future. If you wish to customize WooCommerce Authorize.net AIM Gateway for your
 * needs please refer to http://docs.woothemes.com/document/authorize-net-aim/
 *
 * @package   WC-Gateway-Authorize-Net-AIM/API/Request
 * @author    SkyVerge
 * @copyright Copyright (c) 2011-2014, SkyVerge, Inc.
 * @license   http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License v3.0
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly


/**
 * Authorize.net AIM API Request Class
 *
 * Generates XML required by API specs to perform an API request
 *
 * @link http://www.authorize.net/support/AIM_guide_XML.pdf
 *
 * @since 3.0
 */
class WC_Authorize_Net_AIM_API_Request extends XMLWriter implements SV_WC_Payment_Gateway_API_Request {


	/** auth/capture transaction type */
	const AUTH_CAPTURE = 'authCaptureTransaction';

	/** authorize only transaction type */
	const AUTH_ONLY = 'authOnlyTransaction';

	/** prior auth-only capture transaction type */
	const PRIOR_AUTH_CAPTURE = 'priorAuthCaptureTransaction';

	/** @var string request xml */
	private $request_xml;

	/** @var string API login ID value */
	private $api_login_id;

	/** @var string API transaction key value */
	private $api_transaction_key;

	/** @var WC_Order optional order object if this request was associated with an order */
	protected $order;


	/**
	 * Construct request object
	 *
	 * @since 3.0
	 * @param string $api_login_id API login ID
	 * @param string $api_transaction_key API transaction key
	 */
	public function __construct( $api_login_id, $api_transaction_key ) {

		$this->api_login_id        = $api_login_id;
		$this->api_transaction_key = $api_transaction_key;

		// Create XML document in memory
		$this->openMemory();

		// Set XML version & encoding
		$this->startDocument( '1.0', 'UTF-8' );
	}


	/**
	 * Creates a credit card charge request for the payment method / customer associated with $order
	 *
	 * @since 3.0
	 * @param WC_Order $order the order object
	 */
	public function create_credit_card_charge( WC_Order $order ) {

		$this->create_transaction( self::AUTH_CAPTURE, $order );
	}


	/**
	 * Creates a credit card auth request for the payment method / customer associated with $order
	 *
	 * @since 3.0
	 * @param WC_Order $order the order object
	 */
	public function create_credit_card_auth( WC_Order $order ) {

		$this->create_transaction( self::AUTH_ONLY, $order );
	}


	/**
	 * Capture funds for a previous credit card authorization
	 *
	 * @since 3.0
	 * @param WC_Order $order the order object
	 */
	public function create_credit_card_capture( WC_Order $order ) {

		// store the order object for later use
		$this->order = $order;

		// <createTransactionRequest xmlns="AnetApi/xml/v1/schema/AnetApiSchema.xsd">
		$this->startElementNs( null, 'createTransactionRequest', 'AnetApi/xml/v1/schema/AnetApiSchema.xsd' );

			// add authentication info
			$this->add_authentication();

			// <transactionRequest>
			$this->startElement( 'transactionRequest' );

				// <transactionType>
				$this->writeElement( 'transactionType', self::PRIOR_AUTH_CAPTURE );

				// <amount>
				$this->writeElement( 'amount', $order->capture_total );

				// <refTransId>
				$this->writeElement( 'refTransId', $order->auth_net_aim_ref_trans_id );

			// </transactionRequest>
			$this->endElement();

		// </createTransactionRequest>
		$this->endElement();
	}


	/**
	 * Creates a customer check debit request for the given $order
	 *
	 * @since 3.0
	 * @param WC_Order $order the order object
	 */
	public function create_echeck_debit( WC_Order $order ) {

		$this->create_transaction( self::AUTH_CAPTURE, $order );
	}


	/**
	 * Helper to return completed XML document
	 *
	 * @since 3.0
	 * @return string XML
	 */
	public function to_xml() {

		if ( ! empty( $this->request_xml ) ) {

			return $this->request_xml;
		}

		$this->endDocument();

		return $this->request_xml = $this->outputMemory();
	}


	/**
	 * Returns the string representation of this request
	 *
	 * @since 3.0
	 * @see SV_WC_Payment_Gateway_API_Request::to_string()
	 * @return string request XML
	 */
	public function to_string() {

		$string = $this->to_xml();

		$dom = new DOMDocument();

		// suppress errors for invalid XML syntax issues
		if ( @$dom->loadXML( $string ) ) {
			$dom->formatOutput = true;
			$string = $dom->saveXML();
		}

		return $string;
	}


	/**
	 * Returns the string representation of this request with any and all
	 * sensitive elements masked or removed
	 *
	 * @since 3.0
	 * @see SV_WC_Payment_Gateway_API_Request::to_string_safe()
	 * @return string the request XML, safe for logging/displaying
	 */
	public function to_string_safe() {

		$request = $this->to_string();

		// replace API login ID
		if ( preg_match( '/<merchantAuthentication>(\n\s*)<name>(\w+)<\/name>/', $request, $matches ) ) {
			$request = preg_replace( '/<merchantAuthentication>[\n\s]*<name>\w+<\/name>/', "<merchantAuthentication>{$matches[1]}<name>" . str_repeat( '*', strlen( $matches[2] ) ) . '</name>', $request );
		}

		// replace API transaction key
		if ( preg_match( '/<transactionKey>(\w+)<\/transactionKey>/', $request, $matches ) ) {
			$request = preg_replace( '/<transactionKey>\w+<\/transactionKey>/', '<transactionKey>' . str_repeat( '*', strlen( $matches[1] ) ) . '</transactionKey>', $request );
		}

		// replace card number
		if ( preg_match( '/<cardNumber>(\d+)<\/cardNumber>/', $request, $matches ) ) {
			$request = preg_replace( '/<cardNumber>\d+<\/cardNumber>/', '<cardNumber>' . substr( $matches[1], 0, 1 ) . str_repeat( '*', strlen( $matches[1] ) - 5 ) . substr( $matches[1], -4 ) . '</cardNumber>', $request );
		}

		// replace real CSC code
		$request = preg_replace( '/<cardCode>\d+<\/cardCode>/', '<cardCode>***</cardCode>', $request );

		// replace bank account number
		if ( preg_match( '/<accountNumber>(\d+)<\/accountNumber>/', $request, $matches ) ) {
			$request = preg_replace( '/<accountNumber>\d+<\/accountNumber>/', '<accountNumber>' . str_repeat( '*', strlen( $matches[1] ) ) . '</accountNumber>', $request );
		}

		// replace routing number
		if ( preg_match( '/<routingNumber>(\d+)<\/routingNumber>/', $request, $matches ) ) {
			$request = preg_replace( '/<routingNumber>\d+<\/routingNumber>/', '<routingNumber>' . str_repeat( '*', strlen( $matches[1] ) ) . '</routingNumber>', $request );
		}

		return $request;
	}


	/**
	 * Returns the order associated with this request, if there was one
	 *
	 * @since 3.0
	 * @return WC_Order the order object
	 */
	public function get_order() {

		return $this->order;
	}


	/** Helper Methods ******************************************************/


	/**
	 * Create the transaction XML, this handles all transaction types and both credit card/eCheck transactions
	 *
	 * @since 3.0
	 * @param string $type transaction type
	 * @param WC_Order $order order object
	 */
	private function create_transaction( $type, WC_Order $order ) {

		// store the order object for later use
		$this->order = $order;

		// <createTransactionRequest xmlns="AnetApi/xml/v1/schema/AnetApiSchema.xsd">
		$this->startElementNs( null, 'createTransactionRequest', 'AnetApi/xml/v1/schema/AnetApiSchema.xsd' );

			// add authentication info
			$this->add_authentication();

			// <refId>
			$this->writeElement( 'refId', $order->id );

			// <transactionRequest>
			$this->startElement( 'transactionRequest' );

				// <transactionType>
				$this->writeElement( 'transactionType', $type );

				// <amount>
				$this->writeElement( 'amount', $order->payment_total );

				// payment info
				$this->add_payment();

				// <order>
				$this->startElement( 'order' );

					// <invoiceNumber>
					$this->writeElement( 'invoiceNumber', ltrim( $order->get_order_number(), _x( '#', 'hash before the order number', WC_Authorize_Net_AIM::TEXT_DOMAIN ) ) );

					// <description>
					$this->writeElement( 'description', $order->description );

				// </order>
				$this->endElement();

				// <lineItems>
				$this->add_line_items();

				// <tax>
				if ( $order->get_total_tax() > 0 ) {

					$this->add_taxes();
				}

				// <shipping>
				if ( SV_WC_Plugin_Compatibility::get_total_shipping( $order ) > 0 ) {

					$this->add_shipping();
				}

				// <poNumber>
				if ( isset( $order->po_number ) ) {
					$this->writeElement( 'poNumber', substr( preg_replace( '/\W/', '', $order->po_number ), 0, 25 ) );
				}

				// <customer>
				$this->startElement( 'customer' );

					// <id>
					$this->writeElement( 'id', $order->user_id );

					// <email>
					if ( $order->billing_email ) {

						$this->writeElement( 'email', $order->billing_email );
					}

				// </customer>
				$this->endElement();

				// <billTo> + <shipTo>
				$this->add_addresses();

				// <customerIP>
				$this->writeElement( 'customerIP', SV_WC_Plugin_Compatibility::get_order_custom_field( $order, 'customer_ip_address' ) );

				// <transactionSettings>
				$this->add_transaction_settings();

				// <userFields>
				$this->add_user_defined_fields();

			// </transactionRequest>
			$this->endElement();

		// </createTransactionRequest>
		$this->endElement();
	}


	/**
	 * Adds authentication information to the request
	 *
	 * @since 3.0
	 */
	private function add_authentication() {

		// <merchantAuthentication>
		$this->startElement( 'merchantAuthentication' );

			// <name>
			$this->writeElement( 'name', $this->api_login_id );

			// <transactionKey>
			$this->writeElement( 'transactionKey', $this->api_transaction_key );

		// </merchantAuthentication>
		$this->endElement();
	}


	/**
	 * Adds payment information to the request
	 *
	 * @since 3.0
	 */
	private function add_payment() {

		// <payment>
		$this->startElement( 'payment' );

		if ( 'credit_card' == $this->order->payment->type ) {

			// <creditCard>
			$this->startElement( 'creditCard' );

				// <cardNumber>
				$this->writeElement( 'cardNumber', $this->order->payment->account_number );

				// <expirationDate>
				$this->writeElement( 'expirationDate', "{$this->order->payment->exp_month}-{$this->order->payment->exp_year}" );

				if ( ! empty( $this->order->payment->csc ) ) {

					// <cardCode>
					$this->writeElement( 'cardCode', $this->order->payment->csc );
				}

			// </creditCard>
			$this->endElement();

		} else {

			// <bankAccount>
			$this->startElement( 'bankAccount' );

				// <accountType>
				$this->writeElement( 'accountType', $this->order->payment->account_type );

				// <routingNumber>
				$this->writeElement( 'routingNumber', $this->order->payment->routing_number );

				// <accountNumber>
				$this->writeElement( 'accountNumber', $this->order->payment->account_number );

				// <nameOnAccount>
				$this->writeElement( 'nameOnAccount', substr( "{$this->order->billing_first_name} {$this->order->billing_last_name}", 0, 22 ) );

				// <echeckType>
				$this->writeElement( 'echeckType', 'WEB' );

			// </bankAccount>
			$this->endElement();
		}

		// </payment>
		$this->endElement();
	}


	/**
	 * Adds line items to the request
	 *
	 * @since 3.0
	 */
	private function add_line_items() {

		// <lineItems>
		$this->startElement( 'lineItems' );

			$line_items = array();

			// order line items
			foreach ( $this->order->get_items() as $item_id => $item ) {

				$product = $this->order->get_product_from_item( $item );

				$line_items[] = array(
					'item_id'     => $item_id,
					'name'        => $item['name'],
					'description' => trim( is_object( $product ) ? $product->get_sku() . ' ' . $product->get_title() : ''),
					'quantity'    => $item['qty'],
					'unit_price'  => $this->order->get_item_total( $item ),
				);
			}

			// order fees
			foreach ( $this->order->get_fees() as $fee_id => $fee ) {

				$line_items[] = array(
					'item_id'     => $fee_id,
					'name'        => $fee['name'],
					'description' => __( 'Order Fee', WC_Authorize_Net_AIM::TEXT_DOMAIN ),
					'quantity'    => 1,
					'unit_price'  => $this->order->get_item_total( $fee ),
				);
			}

			// add items
			foreach ( $line_items as $line_item ) {

				// <lineItem>
				$this->startElement( 'lineItem' );

					// <itemId>
					$this->writeElement( 'itemId', $line_item['item_id'] );

					// <name>
					$this->startElement( 'name' );
						$this->writeCdata( substr( $line_item['name'], 0, 31 ) );
					$this->endElement();

					// <description>
					$this->startElement( 'description' );
						$this->writeCdata( substr( $line_item['description'], 0, 255 ) );
					$this->endElement();

					// <quantity>
					$this->writeElement( 'quantity', $line_item['quantity'] );

					// <unitPrice>
					$this->writeElement( 'unitPrice', $line_item['unit_price'] );

				// </lineItem>
				$this->endElement();
			}

		// </lineItems>
		$this->endElement();
	}


	/**
	 * Adds tax information to the request
	 *
	 * @since 3.0
	 */
	private function add_taxes() {

		// <tax>
		$this->startElement( 'tax' );

			// <amount>
			$this->writeElement( 'amount', number_format( $this->order->get_total_tax(), 2, '.', '' ) );

			// <name>
			$this->writeElement( 'name', __( 'Taxes', WC_Authorize_Net_AIM::TEXT_DOMAIN ) );

			$taxes = array();

			foreach ( $this->order->get_tax_totals() as $tax_code => $tax ) {

				$taxes[] = sprintf( '%s (%s) - %s', $tax->label, $tax_code, $tax->amount );
			}

			// description
			$this->writeElement( 'description', substr( implode( ',', $taxes ), 0, 255 ) );

		// </tax>
		$this->endElement();
	}


	/**
	 * Adds shipping information to the request
	 *
	 * @since 3.0
	 */
	private function add_shipping() {

		// <shipping>
		$this->startElement( 'shipping' );

			// <amount>
			$this->writeElement( 'amount', number_format( SV_WC_Plugin_Compatibility::get_total_shipping( $this->order ), 2, '.', '' ) );

			// <name>
			$this->writeElement( 'name', __( 'Shipping', WC_Authorize_Net_AIM::TEXT_DOMAIN ) );

			// <description>
			$this->writeElement( 'description', substr( $this->order->get_shipping_method(), 0, 255 ) );

		// </shipping>
		$this->endElement();
	}


	/**
	 * Adds billing/shipping address information to the request
	 *
	 * @since 3.0
	 */
	private function add_addresses() {

		// address fields
		$fields = array(
			'billing'  => array(
				'firstName'   => array( 'value' => $this->order->billing_first_name,                                        'limit' => 50 ),
				'lastName'    => array( 'value' => $this->order->billing_last_name,                                         'limit' => 50 ),
				'company'     => array( 'value' => $this->order->billing_company,                                           'limit' => 50 ),
				'address'     => array( 'value' => $this->order->billing_address_1 . ' ' . $this->order->billing_address_2, 'limit' => 60 ),
				'city'        => array( 'value' => $this->order->billing_city,                                              'limit' => 40 ),
				'state'       => array( 'value' => $this->order->billing_state,                                             'limit' => 40 ),
				'zip'         => array( 'value' => $this->order->billing_postcode,                                          'limit' => 20 ),
				'country'     => array( 'value' => $this->order->billing_country,                                           'limit' => 60 ),
				'phoneNumber' => array( 'value' => $this->order->billing_phone,                                             'limit' => 25 ),
			),
			'shipping' => array(
				'firstName' => array( 'value' => $this->order->shipping_first_name,                                         'limit' => 50 ),
				'lastName'  => array( 'value' => $this->order->shipping_last_name,                                          'limit' => 50 ),
				'company'   => array( 'value' => $this->order->shipping_company,                                            'limit' => 50 ),
				'address'   => array( 'value' => $this->order->shipping_address_1 . ' ' . $this->order->shipping_address_2, 'limit' => 60 ),
				'city'      => array( 'value' => $this->order->shipping_city,                                               'limit' => 40 ),
				'state'     => array( 'value' => $this->order->shipping_state,                                              'limit' => 40 ),
				'zip'       => array( 'value' => $this->order->shipping_postcode,                                           'limit' => 20 ),
				'country'   => array( 'value' => $this->order->shipping_country,                                            'limit' => 60 ),
			),
		);

		// <billTo>
		$this->startElement( 'billTo' );

			foreach ( $fields['billing'] as $element_name => $field ) {

				if ( 'phone' == $element_name ) {

					$value = preg_replace( '/\D/', '', $field['value'] );

				} else {

					$value = preg_replace( '/[^\w\d\s!]/', '', $field['value'] );
				}

				// write field if populated
				if ( $value ) {
					$this->writeElement( $element_name, substr( $value, 0, $field['limit'] ) );
				}
			}

		// </billTo>
		$this->endElement();

		// <shipTo>
		$this->startElement( 'shipTo' );

			foreach ( $fields['shipping'] as $element_name => $field ) {

				$value = preg_replace( '/[^\w\d\s!]/', '', $field['value'] );

				// write field if populated
				if ( $value ) {
					$this->writeElement( $element_name, substr( $value, 0, $field['limit'] ) );
				}
			}

		// </shipTo>
		$this->endElement();
	}


	/**
	 * Add transactions settings, primarily used for setting the duplicate window check when the CSC is required
	 *
	 * This is important because of this use case:
	 *
	 * 1) Customer enters payment info and accidentally enters an incorrect CVV
	 * 2) Auth.net properly declines the transaction
	 * 3) Customer notices the CVV was incorrect, re-enters the correct CVV and tries to submit order
	 * 4) Auth.net rejects this second transaction attempt as a "duplicate transaction"
	 *
	 * For some reason, Auth.net doesn't consider the CVV changing evidence of a non-duplicate transaction and recommends
	 * changing the `duplicateWindow` transaction option between transactions (https://support.authorize.net/authkb/index?page=content&id=A425&actp=search&viewlocale=en_US&searchid=1375994496602)
	 * to avoid this error. However, simply changing the `duplicateWindow` between transactions *does not* prevent
	 * the "duplicate transaction" error.
	 *
	 * The `duplicateWindow` must actually be set to 0 to suppress this error. However, this has the side affect of
	 * potentially allowing duplicate transactions through.
	 *
	 * @since 3.0
	 */
	private function add_transaction_settings() {

		$settings = array();

		if ( ! empty( $this->order->payment->csc ) ) {

			$settings['duplicateWindow'] = 0;
		}

		// <transactionSettings>
		$this->startElement( 'transactionSettings' );

			foreach ( $settings as $setting_name => $setting_value ) {

				// <setting>
				$this->startElement( 'setting' );

					// <settingName>
					$this->writeElement( 'settingName', $setting_name );

					// <settingValue>
					$this->writeElement( 'settingValue', $setting_value );

				// </setting>
				$this->endElement();
			}

		// </transactionSettings>
		$this->endElement();
	}


	/**
	 * Adds custom user-defined fields to the request
	 *
	 * @since 3.0
	 */
	private function add_user_defined_fields() {

		if ( ! empty( $this->order->auth_net_aim_merchant_defined_fields ) ) {

			// <userFields>
			$this->startElement( 'userFields' );

				foreach ( $this->order->auth_net_aim_merchant_defined_fields as $field_name => $field_value ) {

					// <userField>
					$this->startElement( 'userField' );

						// <name>
						$this->writeElement( 'name', $field_name );

						// <value>
						$this->writeElement( 'value', $field_value );

					// </userField>
					$this->endElement();
				}
			// </userFields>
			$this->endElement();
		}
	}


} // end WC_Authorize_Net_AIM_API_Request class
