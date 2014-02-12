<form method="post" id="post">
<?php if( !$products ) { ?>
	<div class="error settings-error"><p><?php _e( 'You must add Products to your store before you can add an order.', 'wpsc_mo' ); ?></p></div>
<?php } ?>
	<div id="poststuff" class="metabox-holder has-right-sidebar">
		<div id="side-info-column" class="inner-sidebar">
			<div id="side-sortables" class="meta-box-sortables ui-sortable">

				<div id="submitdiv" class="postbox">
					<div class="handlediv" title="<?php _e( 'Click to toggle' ); ?>"><br></div>
					<h3 class="hndle"><span><?php _e( 'Sale', 'wpsc_mo' ); ?></span></h3>
					<div class="inside">

						<div id="submitpost" class="submitbox">
							<div id="minor-publishing">
								<div id="misc-publishing-actions">

<?php if( $statuses ) { ?>
									<div class="misc-pub-section">
										<label for="status"><?php _e( 'Sale Status:', 'wpsc_mo' ); ?></label>
	<?php if( wpsc_mo_status_layout( 'dropdown' ) ) { ?>
										<select id="status" name="status" data-placeholder="<?php _e( 'Choose a sale status...', 'wpsc_mo' ); ?>" class="chzn-select">
		<?php foreach( $statuses as $status ) { ?>
											<option value="<?php echo $status['order']; ?>"<?php selected( $status['order'], wpsc_mo_default_status() ); ?>><?php echo $status['label']; ?></option>
		<?php } ?>
										</select>
	<?php } ?>
	<?php if( wpsc_mo_status_layout( 'list' ) ) { ?>
										<fieldset class="status">
		<?php foreach( $statuses as $status ) { ?>
											<label title="<?php echo $status['label']; ?>">
												<input type="radio" id="status_<?php echo $status['order']; ?>" name="status" value="<?php echo $status['order']; ?>"<?php checked( $status['order'], wpsc_mo_default_status() ); ?> />
												<?php echo $status['label']; ?>
											</label>
		<?php } ?>
										</fieldset>
	<?php } ?>
									</div>
									<!-- .misc-pub-section -->

<?php } ?>
									<div class="misc-pub-section">
										<label for="user_select"><?php _e( 'Customer:', 'wpsc_mo' ); ?><img src="<?php echo plugins_url( '/templates/admin/images/loading.gif', $wpsc_mo['relpath'] ); ?>" style="display:none;" class="customer mo-loading" alt="loading" /></label>
										<select id="user_select" name="user" data-placeholder="<?php _e( 'Choose a User...', 'wpsc_mo' ); ?>" class="chzn-select">
											<option value=""><?php _e( 'Guest', 'wpsc_mo' ); ?></option>
<?php if( $users ) { ?>
	<?php foreach( $users as $user ) { ?>
											<option value="<?php echo $user->ID; ?>"<?php selected( ( isset( $order->user_id ) ? $order->user_id : false ), $user->ID ); ?>><?php echo $user->menu_name; ?></option>
	<?php } ?>
<?php } ?>
										</select>
									</div>
									<!-- .misc-pub-section -->

<?php if( $payment_methods ) { ?>
									<div class="misc-pub-section">
										<label for="payment_method"><?php _e( 'Payment Type:', 'wpsc_mo' ); ?></label>
	<?php if( wpsc_mo_payment_method_layout( 'dropdown' ) ) { ?>
										<select id="payment_method" name="payment_method" data-placeholder="<?php _e( 'Choose a payment method...', 'wpsc_mo' ); ?>" class="chzn-select">
		<?php foreach( $payment_methods as $key => $payment_method ) { ?>
											<option value="<?php echo $key; ?>"<?php selected( $key, wpsc_mo_default_payment_method() ); ?>><?php echo $payment_method['name']; ?></option>
		<?php } ?>
										</select>
	<?php } ?>
	<?php if( wpsc_mo_payment_method_layout( 'list' ) ) { ?>
										<fieldset class="payment_methods">
		<?php foreach( $payment_methods as $key => $payment_method ) { ?>
											<label title="<?php echo $key; ?>">
												<input type="radio" id="payment_method_<?php echo $key; ?>" name="payment_method" value="<?php echo $key; ?>"<?php checked( $key, wpsc_mo_default_payment_method() ); ?> />
												<?php echo $payment_method['display_name']; ?>
											</label>
		<?php } ?>
										</fieldset>
	<?php } ?>
									</div>
									<!-- .misc-pub-section -->
<?php } ?>

									<div class="misc-pub-section">
										<label for="assigned_to"><?php _e( 'Assigned To:', 'wpsc_mo' ); ?></label>
										<select id="assigned_to" name="assigned_to" class="chzn-select">
<?php if( $users ) { ?>
	<?php foreach( $users as $user ) { ?>
											<option value="<?php echo $user->ID; ?>"<?php selected( ( isset( $order->assigned_to ) ? $order->assigned_to : false ), $user->ID ); ?>><?php echo $user->menu_name; ?></option>
	<?php } ?>
<?php } ?>
										</select>
									</div>
									<!-- .misc-pub-section -->

								</div>
							</div>
							<div id="major-publishing-actions">
								<div id="publishing-action">
									<input type="submit" id="publish" name="publish" class="button-primary" value="<?php _e( 'Add Order', 'wpsc_mo' ); ?>" tabindex="5" accesskey="p" />
									<input type="hidden" name="action" value="save" />
								</div>
								<div class="clear"></div>
							</div>
						</div>
						<!-- .submitbox -->

					</div>
				</div>
				<!-- .postbox -->

				<div id="submitdiv" class="postbox">
					<div class="handlediv" title="<?php _e( 'Click to toggle' ); ?>"><br></div>
					<h3 class="hndle"><span><?php _e( 'Totals', 'wpsc_mo' ); ?></span></h3>
					<div class="inside">

						<div class="submitbox">
							<div id="minor-publishing">
								<div id="misc-publishing-actions">

									<div class="misc-pub-section">
										<label for="total_discount"><?php _e( 'Discount:', 'wpsc_mo' ); ?></label>
										<input type="text" id="total_discount" name="total[discount]" value="<?php if ( isset( $order->totals->discount) ) echo $order->totals->discount; ?>" />
										<select name="discount_type" class="auto-width"<?php if( isset( $order->discount_type ) ) { ?> selected="<?php echo $order->discount_type; ?>"<?php } ?>>
											<option value="value"><?php _e( 'Value', 'wpsc_mo' ); ?></option>
											<option value="percent"><?php _e( 'Percent', 'wpsc_mo' ); ?></option>
											<option value="coupon"><?php _e( 'Coupon Code', 'wpsc_mo' ); ?></option>
										</select>
									</div>
									<!-- .misc-pub-section -->

									<div class="misc-pub-section">
										<label for="total_shipping"><?php _e( 'Shipping:', 'wpsc_mo' ); ?></label>
										<input type="text" id="total_shipping" name="total[shipping]" value="<?php if ( isset( $order->totals->shipping) ) echo $order->totals->shipping; ?>" />
									</div>
									<!-- .misc-pub-section -->

									<div class="misc-pub-section">
										<label for="total_tax"><?php _e( 'Tax:', 'wpsc_mo' ); ?></label>
										<input type="text" id="total_tax" name="total[tax]" value="<?php if ( isset( $order->totals->tax) ) echo $order->totals->tax; ?>" />
									</div>
									<!-- .misc-pub-section -->

									<div class="misc-pub-section">
										<label for="total_subtotal"><?php _e( 'Sub-total:', 'wpsc_mo' ); ?></label>
										<input type="text" id="total_subtotal" name="total[subtotal]" value="<?php if ( isset( $order->totals->subtotal) ) echo $order->totals->subtotal; ?>" />
									</div>
									<!-- .misc-pub-section -->

								</div>
							</div>
						</div>
						<!-- .submitbox -->

					</div>
				</div>
				<!-- .postbox -->

				<div id="submitdiv" class="postbox">
					<div class="handlediv" title="<?php _e( 'Click to toggle' ); ?>"><br></div>
					<h3 class="hndle"><span><?php _e( 'Notes', 'wpsc_mo' ); ?></span></h3>
					<div class="inside">

						<div class="submitbox">
							<div id="minor-publishing">
								<div id="misc-publishing-actions">

									<div class="misc-pub-section">
										<fieldset>
											<textarea id="notes" name="notes" rows="10"><?php if ( isset( $order->notes) ) echo $order->notes; ?></textarea>
										</fieldset>
									</div>

								</div>
							</div>
						</div>
						<!-- .submitbox -->

					</div>
				</div>
				<!-- .postbox -->

			</div>
		</div>
		<!-- .inner-sidebar -->
		<div id="post-body">
			<div id="post-body-content">

				<div id="normal-sortables" class="meta-box-sortables">

					<div id="products-full-width">
						<?php do_action( 'wpsc_mo_before_products' ); ?>
						<table class="widefat page fixed">
							<thead>
								<tr>
									<th class="manage-column align-left column-qty"><?php _e( 'Qty.', 'wpsc_mo' ); ?></th>
									<th class="manage-column column-sku"><?php _e( 'SKU', 'wpsc_mo' ); ?></th>
									<th class="manage-column align-left"><?php _e( 'Product', 'wpsc_mo' ); ?></th>
									<th class="manage-column align-right column-price"><?php _e( 'Price', 'wpsc_mo' ); ?></th>
									<th class="manage-column align-left column-remove">&nbsp;</th>
								</tr>
							</thead>
							<tbody>
<?php if( $products ) { ?>
								<tr class="placeholder_row">
									<td colspan="5"><p><?php _e( 'Choose a product to add to the order.', 'wpsc_mo' ); ?></p></td>
								</tr>
<?php } else { ?>
								<tr class="no_products">
									<td colspan="5"><?php _e( 'You have no products to add.', 'wpsc_mo' ); ?></td>
								</tr>
<?php } ?>
							</tbody>
							<tfoot>
<?php if( $products ) { ?>
								<tr>
									<th id="product_select_holder" class="manage-column align-left" colspan="5">
										<select name="product_select" id="product_select" data-placeholder="<?php _e( 'Choose a Product...', 'wpsc_mo' ); ?>" class="chzn-select">
											<option></option>
	<?php foreach( $products as $product ) { ?>
											<option value="<?php echo $product->ID; ?>"<?php echo disabled( $product->stock, 0 ); ?>><?php echo $product->name; ?><?php if( $product->sku ) { ?> (<?php echo $product->sku; ?>)<?php } ?></option>
	<?php } ?>
										</select>
										<button class="button" id="addproduct"><?php _e( 'Add Product', 'wpsc_mo' ); ?></button>
										<img src="<?php echo plugins_url( '/templates/admin/images/loading.gif', $wpsc_mo['relpath'] ); ?>" style="display:none;" class="add-product mo-loading" alt="" />
									</th>
								</tr>
<?php } ?>
							</tfoot>
						</table>
						<input type="hidden" name="rows" value="<?php echo count( $products ); ?>" />
						<?php do_action( 'wpsc_mo_after_products' ); ?>
					</div>
					<!-- #products-full-width -->

					<?php do_action( 'wpsc_mo_before_order_information' ); ?>

<?php
if( !$heading_exists ) {
	$open = true; ?>
					<div class="postbox">
						<h3 class="hndle"><span><?php _e( 'Order Details', 'wpsc_mo' ); ?></span></h3>
						<div class="inside">
							<ul class="fields">
<?php
}
if( $checkout_rows ) {
	foreach( $checkout_rows as $checkout_row ) {
		switch( $checkout_row->type ) {

			case 'heading':
				if( $checkout_row->checkout_order > 1 ) { ?>
							</ul>
						</div>
					</div>
					<!-- .postbox -->
<?php
				} ?>

					<div class="postbox">
						<h3 class="hndle"><span><?php echo $checkout_row->name; ?></span></h3>
						<div class="inside">
<?php
				if ( $checkout_row->name == 'Shipping Address' ) { ?>
						<p><input type="checkbox" name="copybilling" id="copybilling"> <label for="copybilling"><?php _e( 'Same as billing address', 'wpsc_mo' ); ?></label></p>
<?php
				} ?>
							<ul class="fields">
<?php
				break;

			case 'country':
			case 'delivery_country':
				$output = '
								<li class="field_type-' . $checkout_row->type . ' ' . $checkout_row->unique_name . '">
									<label for="checkout_form_' . $checkout_row->id . '">' . $checkout_row->name . '</label>
									<select id="checkout_form_' . $checkout_row->id . '" name="checkout_form[' . $checkout_row->id . ']" data-placeholder="' . __( 'Choose a country...', 'wpsc_mo' ) . '" class="chzn-select ';
				if( $checkout_row->mandatory )
					$output .= 'required';
				$output .= '">';
				$output .= '
										<option></option>
';
				foreach( $countries as $country )
					$output .= '
										<option value="' . $country->isocode . '"' . selected( $country->isocode, $base_country, false ) . '>' . $country->country . '</option>' . "\n";
				$output .= '
										</select>';
				if( $checkout_row->mandatory )
					$output .= ' <span class="required">(' . __( 'required', 'wpsc_mo' ) . ')</span>';
				$output .= '
									</li>' . "\n";
				echo $output;
				break;

			default:
			case 'text':
			case 'city':
			case 'email':
			case 'billingpostcode':
			case 'shippingpostcode':
				switch( $checkout_row->unique_name ) {

					case 'billingpostcode':
					case 'shippingpostcode':
						$size = ' size="5"';
						break;

					case 'billingemail':
						$size = ' size="48"';
						break;

					default:
						$size = '';
						break;

				}
				$output = '<li class="field_type-' . $checkout_row->type . ' ' . $checkout_row->unique_name . '">';
				$output .= '<label for="checkout_form_' . $checkout_row->id . '">' . $checkout_row->name . '</label>';
				$output .= '<input type="text" id="checkout_form_' . $checkout_row->id . '" name="checkout_form[' . $checkout_row->id . ']" value="' . $checkout_row->value . '" class="text';
				if( $checkout_row->mandatory )
					$output .= ' required';
				$output .= '"' . $size . ' />';
				if( $checkout_row->mandatory )
					$output .= ' <span class="required">(' . __( 'required', 'wpsc_mo' ) . ')</span>';
				$output .= '</li>' . "\n";
				echo $output;
				break;

			case 'address':
			case 'delivery_address':
			case 'textarea':
				$output = '
								<li class="field_type-textarea ' . $checkout_row->unique_name . '">';
				$output .= '
									<label for="checkout_form_' . $checkout_row->id . '">' . $checkout_row->name . '</label>';
				$output .= '
									<textarea id="checkout_form_' . $checkout_row->id . '" name="checkout_form[' . $checkout_row->id . ']" rows="3" cols="50" class="';
				if( $checkout_row->mandatory )
					$output .= 'required';
				$output .= '">' . $checkout_row->value . '</textarea>';
				if( $checkout_row->mandatory )
					$output .= ' <span class="required">(' . __( 'required', 'wpsc_mo' ) . ')</span>';
				$output .= '
								</li>' . "\n";
				echo $output;
				break;

		}
	}
	if( ! isset( $open ) || $open ) { ?>
							</ul>
						</div>
					</div>
					<!-- .postbox -->
<?php
	}
} ?>
					<?php do_action( 'wpsc_mo_after_order_information' ); ?>

				</div>
				<!-- #normal-sortables -->

			</div>
			<!-- #post-body-content -->
		</div>
		<!-- #post-body -->
	</div>
	<!-- #poststuff -->
</form>