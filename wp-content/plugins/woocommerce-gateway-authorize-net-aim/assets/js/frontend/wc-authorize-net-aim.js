/*!
 * WooCommerce Authorize.net AIM Gateway
 * Version 3.0
 *
 * Copyright (c) 2011-2014, SkyVerge, Inc.
 * Licensed under the GNU General Public License v3.0
 * http://www.gnu.org/licenses/gpl-3.0.html
 */
jQuery( document ).ready( function ( $ ) {

	"use strict";

	var $checkout = $( 'form.checkout' );

	// checkout page
	if ( $checkout.length ) {

		// validate payment data before order is submitted
		$checkout.bind( 'checkout_place_order_authorize_net_aim', function() { return validateCardData( $( this ) ) } );
		$checkout.bind( 'checkout_place_order_authorize_net_aim_echeck', function() { return validateAccountData( $( this ) ) } );

	// checkout->pay page
	} else {

		var paymentMethod = $( '#order_review input[name=payment_method]:checked' ).val();

		// validate card data before order is submitted when the payment gateway is selected
		$( 'form#order_review' ).submit( function () {

			if ( 'authorize_net_aim' == paymentMethod ) {
				return validateCardData( $( this ) );
			}

			if ( 'authorize_net_aim_echeck' == paymentMethod ) {
				return validateAccountData( $( this ) );
			}

		} );
	}


	// Perform validation on the card info entered
	function validateCardData( $form ) {

		if ( $form.is( '.processing' ) ) return false;

		var $paymentFields = $( '.payment_method_authorize_net_aim' );


		var errors = [];

		var accountNumber = $paymentFields.find( '.js-wc-payment-gateway-account-number' ).val();
		var csc           = $paymentFields.find( '.js-wc-payment-gateway-csc' ).val();  // optional element
		var expMonth      = $paymentFields.find( '.js-wc-payment-gateway-card-exp-month' ).val();
		var expYear       = $paymentFields.find( '.js-wc-payment-gateway-card-exp-year' ).val();

		// replace any dashes or spaces in the card number
		accountNumber = accountNumber.replace( /-|\s/g, '' );

		// validate card number
		if ( ! accountNumber ) {

			errors.push( authorize_net_aim_params.card_number_missing );

		} else {

			if ( accountNumber.length < 12 || accountNumber.length > 19 ) {
				errors.push( authorize_net_aim_params.card_number_length_invalid );
			}

			if ( /\D/.test( accountNumber ) ) {
				errors.push( authorize_net_aim_params.card_number_digits_invalid );
			}

			if ( ! luhnCheck( accountNumber ) ) {
				errors.push( authorize_net_aim_params.card_number_invalid );
			}

		}

		// validate expiration date
		var currentYear = new Date().getFullYear();

		if ( /\D/.test( expMonth ) || /\D/.test( expYear ) ||
				expMonth > 12 ||
				expMonth < 1 ||
				expYear < currentYear ||
				expYear > currentYear + 20 ) {
			errors.push( authorize_net_aim_params.card_exp_date_invalid );
		}

		// validate CSC if present
		if ( 'undefined' !== typeof csc ) {

			if ( ! csc ) {
				errors.push( authorize_net_aim_params.cvv_missing );
			} else {

				if (/\D/.test( csc ) ) {
					errors.push( authorize_net_aim_params.cvv_digits_invalid );
				}

				if ( csc.length < 3 || csc.length > 4 ) {
					errors.push( authorize_net_aim_params.cvv_length_invalid );
				}

			}

		}

		if ( errors.length > 0 ) {

			renderErrors( $form, errors );

			return false;

		} else {

			// get rid of any space/dash characters
			$paymentFields.find( '.js-wc-account-number' ).val( accountNumber );

			return true;
		}
	}


	// luhn check
	function luhnCheck( accountNumber ) {
		var sum = 0;
		for ( var i = 0, ix = accountNumber.length; i < ix - 1; i++ ) {
			var weight = parseInt( accountNumber.substr( ix - ( i + 2 ), 1 ) * ( 2 - ( i % 2 ) ) );
			sum += weight < 10 ? weight : weight - 9;
		}

		return accountNumber.substr( ix - 1 ) == ( ( 10 - sum % 10 ) % 10 );
	}


	// Perform validation on the checking account info entered
	function validateAccountData( $form ) {

		if ( $form.is( '.processing' ) ) return false;

		var $paymentFields = $( '.payment_method_authorize_net_aim_echeck' );


		var errors = [];

		var routingNumber        = $paymentFields.find( '.js-wc-payment-gateway-routing-number' ).val();
		var accountNumber        = $paymentFields.find( '.js-wc-payment-gateway-account-number' ).val();

		// validate routing number
		if ( ! routingNumber ) {

			errors.push( authorize_net_aim_params.routing_number_missing );

		} else {

			if ( 9 != routingNumber.length ) {
				errors.push( authorize_net_aim_params.routing_number_length_invalid );
			}

			if ( /\D/.test( routingNumber ) ) {
				errors.push( authorize_net_aim_params.routing_number_digits_invalid );
			}

		}

		// validate account number
		if ( ! accountNumber ) {

			errors.push( authorize_net_aim_params.account_number_missing );

		} else {

			if ( accountNumber.length < 5 || accountNumber.length > 17 ) {
				errors.push( authorize_net_aim_params.account_number_length_invalid );
			}

			if ( /\D/.test( accountNumber ) ) {
				errors.push( authorize_net_aim_params.account_number_invalid );
			}

		}

		if ( errors.length > 0 ) {

			renderErrors( $form, errors );

			return false;

		} else {

			// get rid of any space/dash characters
			$paymentFields.find( '.js-wc-account-number' ).val( accountNumber );

			return true;
		}
	}


	// render any new errors and bring them into the viewport
	function renderErrors( $form, errors ) {

		// hide and remove any previous errors
		$( '.woocommerce-error, .woocommerce-message' ).remove();

		// add errors
		$form.prepend( '<ul class="woocommerce-error"><li>' + errors.join( '</li><li>' ) + '</li></ul>' );

		// unblock UI
		$form.removeClass( 'processing' ).unblock();

		$form.find( '.input-text, select' ).blur();

		// scroll to top
		$( 'html, body' ).animate( {
			scrollTop: ( $form.offset().top - 100 )
		}, 1000 );

	}


	// Show the sample check image when the help bubble is clicked
	$( 'body' ).bind( 'updated_checkout', function() {

		$( 'img.js-wc-authorize-net-aim-echeck-account-help' ).click( function() {
			if ( ! $( this ).closest( '.payment_method_authorize_net_aim_echeck' ).find( '.sample-check' ).is( ':visible' ) ) {
				$( this ).closest( '.payment_method_authorize_net_aim_echeck' ).find( '.sample-check' ).slideDown();
			}
		} );
	} );


} );
