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
 * @package   WC-Gateway-Authorize-Net-AIM/Gateway/Credit-Card
 * @author    SkyVerge
 * @copyright Copyright (c) 2011-2014, SkyVerge, Inc.
 * @license   http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License v3.0
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * Authorize.net AIM Payment Gateway
 *
 * Handles all credit card purchases
 *
 * This is a direct credit card gateway that supports card types, charge,
 * authorization, and TODO subscriptions via ARB.
 *
 * @since 3.0
 */
class WC_Gateway_Authorize_Net_AIM_Credit_Card extends WC_Gateway_Authorize_Net_AIM {


	/**
	 * Initialize the gateway
	 *
	 * @since 3.0
	 */
	public function __construct() {

		// parent plugin
		global $wc_authorize_net_aim;

		parent::__construct(
			WC_Authorize_Net_AIM::CREDIT_CARD_GATEWAY_ID,
			$wc_authorize_net_aim,
			WC_Authorize_Net_AIM::TEXT_DOMAIN,
			array(
				'method_title'       => __( 'Authorize.net AIM', WC_Authorize_Net_AIM::TEXT_DOMAIN ),
				'method_description' => __( 'Allow customers to securely pay using their credit cards with Authorize.net AIM.', WC_Authorize_Net_AIM::TEXT_DOMAIN ),
				'supports'           => array(
					'products',
					'card_types',
					'charge',
					'authorization',
				 ),
				'payment_type'       => 'credit-card',
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

		woocommerce_authorize_net_aim_payment_fields( $this );
	}


	/**
	 * Add original transaction ID for capturing a prior authorization
	 *
	 * @since 3.0
	 * @param WC_Order $order order object
	 * @return WC_Order object with payment and transaction information attached
	 */
	protected function get_order_for_capture( $order ) {

		$order = parent::get_order_for_capture( $order );

		$order->auth_net_aim_ref_trans_id = SV_WC_Plugin_Compatibility::get_order_custom_field( $order, 'wc_authorize_net_aim_trans_id' );

		$order->description = sprintf( __( '%s - Capture for Order %s', $this->text_domain ), esc_html( get_bloginfo( 'name' ) ), $order->get_order_number() );

		return $order;
	}


} // end \WC_Gateway_Authorize_Net_AIM_Credit_Card class
