<?php
/*
Plugin Name: wpsc-simple-shipping
Plugin URI: http://getshopped.org
Description: Enables free input for fixed rate shipping options, like "pickup - $0, regular - $5, overnght - $10"
Version: 1.1
Author: Instinct Entertainment
Author URI: http://getshopped.org/
License: GPL2
*/

/*  Copyright 2013  Instinct ent.

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as 
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/


class wpsc_fixedrate {

	var $internal_name, $name;

	/**
	 * Constructor
	 *
	 * @return boolean Always returns true.
	 */
	function wpsc_fixedrate() {
		$this->internal_name = "fixedrate";
		$this->name= __( "Fixed Rate", 'wpsc' );
		$this->is_external=false;
		return true;
	}

	/**
	 * Returns i18n-ized name of shipping module.
	 *
	 * @return string
	 */
	function getName() {
		return $this->name;
	}
	
	/**
	 * Returns internal name of shipping module.
	 *
	 * @return string
	 */	
	function getInternalName() {
		return $this->internal_name;
	}

	/**
	 * generates row of table rate fields
	 */
	private function output_row( $key = '', $shipping = '' ) {
		$currency = wpsc_get_currency_symbol();
		$class = ( $this->alt ) ? 'class="alternate"' : '';
		$this->alt = ! $this->alt;
		?>
			<tr>
				<td <?php echo $class; ?>>
					<div class="cell-wrapper">
						<small><?php echo esc_html( $currency ); ?></small>
						<input type="text" name="wpsc_shipping_fixedrate_layer[]" value="<?php echo esc_attr( $key ); ?>" size="4" />
					</div>
				</td>
				<td <?php echo $class; ?>>
					<div class="cell-wrapper">
						<small><?php echo esc_html( $currency ); ?></small>
						<input type="text" name="wpsc_shipping_fixedrate_shipping[]" value="<?php echo esc_attr( $shipping ); ?>" size="4" />
						<span class="actions">
							<a tabindex="-1" title="<?php _e( 'Delete Layer', 'wpsc' ); ?>" class="button-secondary wpsc-button-round wpsc-button-minus" href="#"><?php echo _x( '&ndash;', 'delete item', 'wpsc' ); ?></a>
							<a tabindex="-1" title="<?php _e( 'Add Layer', 'wpsc' ); ?>" class="button-secondary wpsc-button-round wpsc-button-plus" href="#"><?php echo _x( '+', 'add item', 'wpsc' ); ?></a>
						</span>
					</div>
				</td>
			</tr>
		<?php
	}
	
	/**
	 * Returns HTML settings form. Should be a collection of <tr> elements containing two columns.
	 *
	 * @return string HTML snippet.
	 */
	function getForm() {
		$layers = get_option( 'fixedrate_layers', array() );
		$this->alt = false;
		ob_start();
		?>
		<tr>
			<td colspan='2'>
				<table>
					<thead>
						<tr>
							<th class="option"><?php _e('Shipping Option', 'wpsc' ); ?></th>
							<th class="shipping"><?php _e( 'Shipping Price', 'wpsc' ); ?></th>
						</tr>
					</thead>
					<tbody class="table-rate">
						<tr class="js-warning">
							<td colspan="2">
								<small><?php echo sprintf( __( 'To remove a rate layer, simply leave the values on that row blank. By the way, <a href="%s">enable JavaScript</a> for a better user experience.', 'wpsc'), 'http://www.google.com/support/bin/answer.py?answer=23852' ); ?></small>
							</td>
						</tr>
						<?php if ( ! empty( $layers ) ): ?>
							<?php
								foreach( $layers as $key => $shipping ){
									$this->output_row( $key, $shipping );
								}
							?>
						<?php else: ?>
							<?php $this->output_row(); ?>
						<?php endif ?>
					</tbody>
				</table>
			</td>
		</tr>
		<?php
		return ob_get_clean();
	}	
	
	/**
	 * Saves shipping module settings.
	 *
	 * @return boolean Always returns true.
	 */
	function submit_form() {
		if ( ! isset( $_POST['wpsc_shipping_fixedrate_layer'] ) || ! isset( $_POST['wpsc_shipping_fixedrate_shipping'] ) )
			return false;

		$layers = (array) $_POST['wpsc_shipping_fixedrate_layer'];
		$shippings = (array) $_POST['wpsc_shipping_fixedrate_shipping'];
		$new_layer = array();
		if ( $shippings != '' ) {
			foreach ( $shippings as $key => $price ) {
				if ( ! is_numeric( $key ) || ! is_numeric( $price ) )
					continue;

				$new_layer[ $layers[ $key ] ] = $price;
			}
		}

		// Sort the data before it goes into the database. Makes the UI make more sense
		krsort( $new_layer );
		update_option( 'fixedrate_layers', $new_layer );
		return true;
	}	
	
	/**
	 * returns shipping quotes using this shipping module.
	 *
	 * @return array collection of rates applicable.
	 */
	function getQuote() {

		global $wpdb, $wpsc_cart;
		if ( wpsc_get_customer_meta( 'nzshpcart' ) ) {
			$shopping_cart = wpsc_get_customer_meta( 'nzshpcart' );
		}
		if ( is_object( $wpsc_cart ) ) {
			$price = $wpsc_cart->calculate_subtotal( true );
		}

		$layers = get_option( 'fixedrate_layers' );

		if ($layers != '') {

			// At some point we should probably remove this as the sorting should be
			// done when we save the data to the database. But need to leave it here
			// for people who have non-sorted settings in their database
			krsort( $layers );
			return $layers;

			
		}
	}

	/**
	 * calculates shipping price for an individual cart item.
	 *
	 * @param object $cart_item (reference)
	 * @return float price of shipping for the item.
	 */
	function get_item_shipping( &$cart_item ) {

		global $wpdb, $wpsc_cart;

		$unit_price = $cart_item->unit_price;
		$quantity = $cart_item->quantity;
		$weight = $cart_item->weight;
		$product_id = $cart_item->product_id;

		$uses_billing_address = false;
		foreach ( $cart_item->category_id_list as $category_id ) {
			$uses_billing_address = (bool) wpsc_get_categorymeta( $category_id, 'uses_billing_address' );
			if ( $uses_billing_address === true ) {
				break; /// just one true value is sufficient
			}
		}

		if ( is_numeric( $product_id ) && ( get_option( 'do_not_use_shipping' ) != 1 ) ) {
			if ( $uses_billing_address == true ) {
				$country_code = $wpsc_cart->selected_country;
			} else {
				$country_code = $wpsc_cart->delivery_country;
			}

			if ( $cart_item->uses_shipping == true ) {
				//if the item has shipping
				$additional_shipping = '';
				if ( isset( $cart_item->meta[0]['shipping'] ) ) {
					$shipping_values = $cart_item->meta[0]['shipping'];
				}
				if ( isset( $shipping_values['local'] ) && $country_code == get_option( 'base_country' ) ) {
					$additional_shipping = $shipping_values['local'];
				} else {
					if ( isset( $shipping_values['international'] ) ) {
						$additional_shipping = $shipping_values['international'];
					}
				}
				$shipping = $quantity * $additional_shipping;
			} else {
				//if the item does not have shipping
				$shipping = 0;
			}
		} else {
			//if the item is invalid or all items do not have shipping
			$shipping = 0;
		}
		return $shipping;
	}

}

$wpsc_fixedrate = new wpsc_fixedrate();
$wpsc_shipping_modules[$wpsc_fixedrate->getInternalName()] = $wpsc_fixedrate;
?>