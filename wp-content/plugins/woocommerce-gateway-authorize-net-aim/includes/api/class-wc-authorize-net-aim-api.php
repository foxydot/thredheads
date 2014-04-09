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
 * @package   WC-Gateway-Authorize-Net-AIM/API
 * @author    SkyVerge
 * @copyright Copyright (c) 2011-2014, SkyVerge, Inc.
 * @license   http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License v3.0
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * Authorize.net AIM API Class
 *
 * Handles sending/receiving/parsing of Authorize.net AIM XML, this is the main API
 * class responsible for communication with the Authorize.net AIM API
 *
 * @since 3.0
 */
class WC_Authorize_Net_AIM_API implements SV_WC_Payment_Gateway_API {


	/** the production endpoint */
	const PRODUCTION_ENDPOINT = 'https://api.authorize.net/xml/v1/request.api';

	/** the test endpoint */
	const TEST_ENDPOINT = 'https://apitest.authorize.net/xml/v1/request.api';

	/** @var string API URL endpoint */
	private $endpoint;

	/** @var string gateway id, used for logging */
	private $gateway_id;

	/** @var string API login ID value */
	private $api_login_id;

	/** @var string API transaction key value */
	private $api_transaction_key;

	/** @var \WC_Authorize_Net_AIM_API_Request most recent request */
	private $request;

	/** @var \WC_Authorize_Net_AIM_API_Response most recent response */
	private $response;


	/**
	 * Constructor - setup request object and set endpoint
	 *
	 * @since 3.0
	 * @param string $gateway_id gateway id
	 * @param string $environment current API environment, either `production` or `test`
	 * @param string $api_login_id API login ID
	 * @param string $api_transaction_key API transaction key
	 * @return \WC_Authorize_Net_AIM_API
	 */
	public function __construct( $gateway_id, $environment, $api_login_id, $api_transaction_key ) {

		$this->gateway_id          = $gateway_id;
		$this->endpoint            = ( 'production' == $environment ) ? self::PRODUCTION_ENDPOINT : self::TEST_ENDPOINT;
		$this->api_login_id        = $api_login_id;
		$this->api_transaction_key = $api_transaction_key;
	}


	/**
	 * Create a new credit card charge transaction
	 *
	 * This request, if successful, causes a charge to be incurred by the
	 * specified credit card. Notice that the authorization for the charge is
	 * obtained when the card issuer receives this request. The resulting
	 * authorization code is returned in the response to this request.
	 *
	 * @since 3.0
	 * @see SV_WC_Payment_Gateway_API::credit_card_charge()
	 * @param WC_Order $order order
	 * @return \WC_Authorize_Net_AIM_API_Response Authorize.net API response object
	 * @throws Exception network timeouts, etc
	 */
	public function credit_card_charge( WC_Order $order ) {

		$request = $this->get_new_request();

		$request->create_credit_card_charge( $order );

		return $this->perform_request( $request );
	}


	/**
	 * Create a new credit card auth transaction
	 *
	 * This request is used for a transaction in which the merchant needs
	 * authorization of a charge, but does not wish to actually make the charge
	 * at this point in time. For example, if a customer orders merchandise to
	 * be shipped, you could issue this request at the time of the order to
	 * make sure the merchandise will be paid for by the card issuer. Then at
	 * the time of actual merchandise shipment, you can capture the charge.
	 *
	 * It is very important to save the transaction ID from the response to
	 * this request, because this is required for the subsequent capture request.
	 *
	 * Note: The authorization is valid only for a fixed amount of time, which
	 * may vary by card issuer, but which is usually several days. Authorize.net imposes
	 * its own maximum of 30 days after the date of the original authorization,
	 * but most issuers are expected to have a validity period significantly
	 * less than this.
	 *
	 * @since 3.0
	 * @see SV_WC_Payment_Gateway_API::credit_card_authorization()
	 * @param WC_Order $order order
	 * @return \WC_Authorize_Net_AIM_API_Response Authorize.net API response object
	 * @throws Exception network timeouts, etc
	 */
	public function credit_card_authorization( WC_Order $order ) {

		$request = $this->get_new_request();

		$request->create_credit_card_auth( $order );

		return $this->perform_request( $request );
	}


	/**
	 * Capture funds for a credit card authorization
	 *
	 * This request can be made only after a previous and successful
	 * authorization request, where the card issuer has authorized a
	 * charge to be made against the specified credit card in the future. The
	 * transaction ID from that prior transaction must be used in this
	 * subsequent and related transaction. This request actually causes that
	 * authorized charge to be incurred against the customer's credit card.
	 *
	 * Notice that you cannot have multiple capture requests against a single
	 * authorization request. Each authorization request must
	 * have one and only one capture request.
	 *
	 * Note: The authorization to be captured is valid only for a fixed amount
	 * of time, which may vary by card issuer, but which is usually several
	 * days. Authorize.net imposes its own maximum of 30 days after the date of the
	 * original authorization, but most issuers are expected to have a validity
	 * period significantly less than this.
	 *
	 * @since 3.0
	 * @see SV_WC_Payment_Gateway_API::credit_card_capture()
	 * @param WC_Order $order order
	 * @return \WC_Authorize_Net_AIM_API_Response Authorize.net API response object
	 * @throws Exception network timeouts, etc
	 */
	public function credit_card_capture( WC_Order $order ) {

		$request = $this->get_new_request();

		$request->create_credit_card_capture( $order );

		return $this->perform_request( $request );
	}


	/**
	 * Perform a customer check debit transaction
	 *
	 * An amount will be debited from the customer's account to the merchant's account.
	 *
	 * @since 3.0
	 * @param WC_Order $order order
	 * @return \WC_Authorize_Net_AIM_API_Response Authorize.net API response object
	 * @throws Exception network timeouts, etc
	 */
	public function check_debit( WC_Order $order ) {

		$request = $this->get_new_request();

		$request->create_echeck_debit( $order );

		return $this->perform_request( $request );
	}


	/** Tokenization methods - all no-op as Authorize.net AIM does not support tokenization ***************************/


	/**
	 * Returns false, as Authorize.net AIM does not support tokenization.
	 *
	 * @since 3.0
	 * @see SV_WC_Payment_Gateway_API::supports_get_tokenized_payment_methods()
	 * @return boolean true
	 */
	public function supports_get_tokenized_payment_methods() {

		return false;
	}


	/**
	 * Returns false, as Authorize.net AIM does not support tokenization.
	 *
	 * @since 3.0
	 * @see SV_WC_Payment_Gateway_API::supports_remove_tokenized_payment_method()
	 * @return boolean true
	 */
	public function supports_remove_tokenized_payment_method() {

		return false;
	}


	/**
	 * Authorize.net AIM does not support tokenization.
	 *
	 * @since 3.0
	 * @see SV_WC_Payment_Gateway_API::tokenize_payment_method()
	 * @param WC_Order $order the order with associated payment and customer info
	 * @return \SV_WC_Payment_Gateway_API_Create_Payment_Token_Response|void
	 */
	public function tokenize_payment_method( WC_Order $order ) {

		// no-op
	}


	/**
	 * Authorize.net AIM does not support tokenization.
	 *
	 * @since 3.0
	 * @see SV_WC_Payment_Gateway_API::remove_tokenized_payment_method()
	 * @param string $token the payment method token
	 * @param string $customer_id unique
	 * @return \SV_WC_Payment_Gateway_API_Response|void
	 */
	public function remove_tokenized_payment_method( $token, $customer_id ) {

		// no-op
	}


	/**
	 * Authorize.net AIM does not support tokenization.
	 *
	 * @since 3.0
	 * @see SV_WC_Payment_Gateway_API::get_tokenized_payment_methods()
	 * @param string $customer_id unique
	 * @return \SV_WC_API_Get_Tokenized_Payment_Methods_Response|void
	 */
	public function get_tokenized_payment_methods( $customer_id ) {

		// no-op
	}


	/**
	 * Perform the request post to the active endpoint
	 *
	 * @since 3.0
	 * @param $request \WC_Authorize_Net_AIM_API_Request object
	 * @return \WC_Authorize_Net_AIM_API_Response response object
	 * @throws Exception network timeouts
	 */
	private function perform_request( $request ) {

		// save the request object
		$this->request = $request;

		// perform the request
		$wp_http_args = array(
			'method'      => 'POST',
			'timeout'     => 30, // seconds
			'redirection' => 0,
			'httpversion' => '1.0',
			'sslverify'   => false,
			'blocking'    => true,
			'user-agent'  => "WordPress/{$GLOBALS['wp_version']}",
			'headers'     => array(
				'accept'       => 'application/xml',
				'content-type' => 'application/xml',
			),
			'body'        => trim( $request->to_xml() ),
			'cookies'     => array(),
		);

		$start_time = microtime( true );
		$response = wp_remote_post( $this->endpoint, $wp_http_args );
		$time = round( microtime( true ) - $start_time, 5 );

		// prepare the request data
		$request_data  = array( 'method' => $wp_http_args['method'], 'uri' => $this->endpoint, 'body' => $request->to_string_safe(), 'time' => $time );

		return $this->handle_response( $response, $request_data );
	}


	/**
	 * Return a new WC_Authorize_Net_AIM_API_Response object from the response XML
	 *
	 * @since 3.0
	 * @param array|WP_Error $response the response data
	 * @param array $request_data the request data
	 * @return \WC_Authorize_Net_AIM_API_Response API response object
	 * @throws Exception network timeouts, non-200 HTTP status, invalid response body, etc
	 */
	private function handle_response( $response, $request_data ) {

		// WP_Error is returned for network timeouts, etc
		if ( is_wp_error( $response ) ) {

			do_action( "wc_{$this->gateway_id}_api_request_performed", $request_data, null );

			throw new Exception( $response->get_error_message() );
		}

		// valid response
		$response_data = array(
			'code'    => ( isset( $response['response']['code'] ) ) ? $response['response']['code'] : '',
			'message' => ( isset( $response['response']['message'] ) ) ? $response['response']['message'] : '',
			'body'    => ( isset( $response['body'] ) ) ? $response['body'] : ''
		);

		// check HTTP status code
		if ( 200 != $response_data['code'] ) {

			// authorize.net should rarely return a non-200 status
			$error_message = sprintf( 'HTTP %s: %s', $response_data['code'], $response_data['message'] );

			// the body (if any)
			if ( trim( $response_data['body'] ) ) {
				$error_message .= ' - ' . strip_tags( $response['body'] );
			}

			do_action( "wc_{$this->gateway_id}_api_request_performed", $request_data, $response_data );

			throw new Exception( $error_message );
		}

		// response body is required
		if ( ! $response_data['body'] ) {

			do_action( "wc_{$this->gateway_id}_api_request_performed", $request_data, $response_data );

			throw new Exception( __( 'Empty response body', WC_Authorize_Net_AIM::TEXT_DOMAIN ) );
		}

		// create the response and tie it to the request
		$response = new WC_Authorize_Net_AIM_API_Response( $response_data['body'] );

		// full response object
		$response_data['body'] = $response->to_string_safe();

		do_action( "wc_{$this->gateway_id}_api_request_performed", $request_data, $response_data );

		// return and save the most recent response object
		return $this->response = $response;
	}


	/**
	 * Builds and returns a new API request object
	 *
	 * @since 3.0
	 * @return \WC_Authorize_Net_AIM_API_Request API request object
	 */
	private function get_new_request() {

		return new WC_Authorize_Net_AIM_API_Request( $this->api_login_id, $this->api_transaction_key );
	}


	/**
	 * Returns the most recent request object
	 *
	 * @since 3.0
	 * @see SV_WC_Payment_Gateway_API::get_request()
	 * @return \WC_Authorize_Net_AIM_API_Request the most recent request object
	 */
	public function get_request() {

		return $this->request;
	}


	/**
	 * Returns the most recent response object
	 *
	 * @since 3.0
	 * @see SV_WC_Payment_Gateway_API::get_response()
	 * @return \WC_Authorize_Net_AIM_API_Response the most recent response object
	 */
	public function get_response() {

		return $this->response;
	}


} // end WC_Authorize_Net_AIM_API class
