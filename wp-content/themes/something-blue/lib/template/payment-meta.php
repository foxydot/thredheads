<?php
global $post, $order;
if ( ! is_object( $theorder ) )
    $theorder = new WC_Order( $post->ID );

$order = $theorder;
?>
<div class="my_meta_control" id="subtitle_metabox">
	<p>
		<a href="<?php print $order->get_checkout_payment_url(); ?>" class="button" target_"_blank">Pay Now</a>
	</p>
</div>