<tr id="product-<?php echo $product->ID; ?>" class="product <?php wpsc_mo_product_has_stock( $product, 'class' ); ?>">
hahaha
<?php $wpsc_variations = new wpsc_variations( $product->ID ); ?>
<?php if( !empty( $wpsc_variations->variation_groups ) ) { ?>
	<td colspan="4" class="align-left">
		<div class="wpsc_variation_forms">
	<?php while ( $wpsc_variations->have_variation_groups() ) : $wpsc_variations->the_variation_group(); ?>
			<label for="<?php echo 'variation_select_{' . $product->ID . '}_{' . $wpsc_variations->variation_group->term_id . '}'; ?>"><?php echo esc_html( $wpsc_variations->variation_group->name ); ?>:</label>
			<select name="variation[<?php echo $wpsc_variations->variation_group->term_id; ?>]" id="<?php echo 'variation_select_{' . $product->ID . '}_{' . $wpsc_variations->variation_group->term_id . '}'; ?>" class="wpsc_select_variation">
		<?php while ( $wpsc_variations->have_variations() ): $wpsc_variations->the_variation(); ?>
					<option value="<?php echo $wpsc_variations->variation->term_id; ?>"<?php disabled( wpsc_mo_variation_stock_available( $product->ID, $wpsc_variations->variation->slug ), 0 ); ?>><?php echo esc_html( stripslashes( $wpsc_variations->variation->name ) ); ?></option>
		<?php endwhile; ?>
			</select>
	<?php endwhile; ?>
			<button class="button addvariation"><?php _e( 'Add Product', 'wpsc_mo' ); ?></button>
			<img src="<?php echo plugins_url( $wpsc_mo['dirname'] . '/templates/admin/images/loading.gif' ); ?>" style="display:none;" class="mo-loading" />
		</div>
	</td>
<?php } else { ?>
	<td class="column-qty align-left">
		<input type="text" id="quantity_<?php echo $product->ID; ?>" name="quantity[<?php echo $product->ID; ?>]" size="2" class="align-center" value="<?php if( isset( $order->products[$product->ID]->quantity ) ) { echo $order->products[$product->ID]->quantity; } else { echo '1'; } ?>" />
	<?php if( wpsc_mo_product_has_stock( $product ) ) { ?>
		<?php if( wpsc_mo_product_has_limited_stock( $product ) ) { ?>
		<span>/</span> <?php echo $product->stock; ?>
		<?php } ?>
	<?php } else { ?>
		-
	<?php } ?>
	</td>
	<td class="column-sku"><?php echo $product->sku; ?></td>
	<td>
		<?php echo $product->name; ?>
	</td>
	<td class="align-right column-price" nowrap>
		<?php echo wpsc_currency_display( $product->price ); ?>
	</td>
<?php } ?>
	<td class="align-center column-remove">
		<img src="<?php echo plugins_url( $wpsc_mo['dirname'] . '/templates/admin/images/cross.png' ); ?>" class="remove" />
		<input type="hidden" class="product_id" id="product_<?php echo $product->ID; ?>" name="product_id[<?php echo $product->ID; ?>]" value="<?php echo $product->ID; ?>" />
	</td>
</tr>
