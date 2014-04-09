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
 * @package   WC-Gateway-Authorize-Net-AIM/Gateway/eCheck
 * @author    SkyVerge
 * @copyright Copyright (c) 2011-2014, SkyVerge, Inc.
 * @license   http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License v3.0
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * Authorize.net AIM eCheck Payment Gateway
 *
 * Handles all purchases with eChecks
 *
 * This is a direct check gateway
 *
 * @since 3.0
 */
class WC_Gateway_Authorize_Net_AIM_eCheck extends WC_Gateway_Authorize_Net_AIM {


	/**
	 * Initialize the gateway
	 *
	 * @since 3.0
	 */
	public function __construct() {

		// parent plugin
		global $wc_authorize_net_aim;

		parent::__construct(
			WC_Authorize_Net_AIM::ECHECK_GATEWAY_ID,
			$wc_authorize_net_aim,
			WC_Authorize_Net_AIM::TEXT_DOMAIN,
			array(
				'method_title'       => __( 'Authorize.net AIM eCheck', WC_Authorize_Net_AIM::TEXT_DOMAIN ),
				'method_description' => __( 'Allow customers to securely pay using their checking accounts with Authorize.net AIM.', WC_Authorize_Net_AIM::TEXT_DOMAIN ),
				'supports'           => array(
					'products',
				 ),
				'payment_type'       => 'echeck',
				'environments'       => array( 'production' => __( 'Production', WC_Authorize_Net_AIM::TEXT_DOMAIN ), 'test' => __( 'Test', WC_Authorize_Net_AIM::TEXT_DOMAIN ) ),
				'shared_settings'    => $this->shared_settings_names,
			)
		);

		// API logging
		if ( ! has_action( 'wc_' . $this->get_id() . '_api_request_performed' ) ) {
			add_action( 'wc_' . $this->get_id() . '_api_request_performed', array( $this, 'log_api_communication' ), 10, 2 );
		}

	}


	/**
	 * Display the payment fields on the checkout page
	 *
	 * @since 3.0
	 * @see WC_Payment_Gateway::payment_fields()
	 */
	public function payment_fields() {

		woocommerce_authorize_net_aim_echeck_payment_fields( $this );

	}


	/**
	 * Adds any gateway-specific transaction data to the order
	 *
	 * @since 1.0
	 * @see SV_WC_Payment_Gateway::add_payment_gateway_transaction_data()
	 * @param WC_Order $order the order object
	 * @param WC_Intuit_QBMS_API_Response $response the transaction response
	 */
	protected function add_payment_gateway_transaction_data( $order, $response ) {

		// transaction results
		//update_post_meta( $order->id, '_wc_' . $this->get_id() . '_authorization_code', $response->get_check_authorization_code() );
		//update_post_meta( $order->id, '_wc_' . $this->get_id() . '_client_trans_id',    $response->get_client_trans_id() );

		// TODO
	}


	/** Subscriptions ******************************************************/

	// TODO

	/**
	 * Returns the query fragment to remove the given subscription renewal
	 * order meta, plus the Intuit QBMS specific meta
	 *
	 * @since 1.0
	 * @see SV_WC_Payment_Gateway::get_remove_subscription_renewal_order_meta_fragment()
	 * @see SV_WC_Payment_Gateway::remove_subscription_renewal_order_meta()
	 * @param array $meta_names array of string meta names to remove
	 * @return string query fragment
	 */
	protected function get_remove_subscription_renewal_order_meta_fragment( $meta_names ) {

		$meta_names[] = '_wc_intuit_' . $this->get_id() . '_authorization_code';
		$meta_names[] = '_wc_intuit_' . $this->get_id() . '_client_trans_id';

		return parent::get_remove_subscription_renewal_order_meta_fragment( $meta_names );

	}


} // end \WC_Gateway_Authorize_Net_AIM_eCheck class
