<?php if( wpsc_mo_show_session_id() ) { ?>
<p><strong><?php _e( 'Session ID', 'wpsc_mo' ); ?></strong>: <?php echo $order->session_id; ?></p>
<?php } ?>
<p>Would you like to <a href="<?php echo $view_sale_url; ?>">view this order</a>, <?php if( isset( $edit_sale_url ) ) { ?><a href="<?php echo $edit_sale_url; ?>">edit this order</a>, <?php } ?>or <a href="<?php echo $new_form_url; ?>">create another order</a>?</p>
<form method="post">
	<p class="submit">
		<input type="submit" value="<?php _e( 'Cancel this order', 'wpsc_mo' ); ?>" class="button" />
	</p>
	<input type="hidden" name="action" value="delete" />
	<input type="hidden" name="purchase_id" value="<?php echo $order->purchase_id; ?>" />
</form>
<?php do_action( 'wpsc_mo_order_confirm_addons', $order ); ?>