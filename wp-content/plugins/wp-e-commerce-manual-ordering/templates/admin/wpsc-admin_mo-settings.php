<form method="post" action="<?php the_permalink(); ?>" id="your-profile">
<?php
/*
	<h3><?php _e( 'General', 'wpsc_mo' ); ?></h3>
	<table class="form-table">

		<tr>
			<td>&nbsp;</td>
		</tr>

	</table>
*/
?>
	<h3><?php _e( 'Presentation', 'wpsc_mo' ); ?></h3>
	<table class="form-table">

		<tr>
			<th scope="row"><label><?php _e( 'Sale Status Layout', 'wpsc_mo' ); ?>:</label></th>
			<td>
				<fieldset>
					<label><input type="radio" name="status_layout" value="1"<?php checked( $status_layout, 1 ); ?> /> <?php _e( 'Display Sale Status as a drop-down menu', 'wpsc_mo' ); ?></label><br />
					<label><input type="radio" name="status_layout" value="0"<?php checked( $status_layout, 0 ); ?> /> <?php _e( 'Display Sale Status in a list', 'wpsc_mo' ); ?></label>
				</fieldset>
				<p class="description"><?php _e( 'Control the display of Sale Status.', 'wpsc_mo' ); ?></p>
			</td>
		</tr>

		<tr>
			<th scope="row"><label for="default_status"><?php _e( 'Default Sale Status', 'wpsc_mo' ); ?>:</label></th>
			<td>
	<?php if( $wpsc_purchlog_statuses ) { ?>
										<select id="default_status" name="default_status">
		<?php foreach( $wpsc_purchlog_statuses as $status ) { ?>
											<option value="<?php echo $status['order']; ?>" <?php echo selected( $default_status, $status['order'] ); ?>><?php echo $status['label']; ?></option>
		<?php } ?>
										</select>
	<?php } ?>
				<p class="description"><?php _e( 'The default Sale Status.', 'wpsc_mo' ); ?></p>
			</td>
		</tr>

		<tr>
			<th scope="row"><label><?php _e( 'Payment Method Layout', 'wpsc_mo' ); ?>:</label></th>
			<td>
				<fieldset>
					<label><input type="radio" name="payment_method_layout" value="1"<?php checked( $payment_method_layout, 1 ); ?> /> <?php _e( 'Display Payment Method as a drop-down menu', 'wpsc_mo' ); ?></label><br />
					<label><input type="radio" name="payment_method_layout" value="0"<?php checked( $payment_method_layout, 0 ); ?> /> <?php _e( 'Display Payment Method in a list', 'wpsc_mo' ); ?></label>
				</fieldset>
				<p class="description"><?php _e( 'Control the display of Payment Method.', 'wpsc_mo' ); ?></p>
			</td>
		</tr>

		<tr>
			<th scope="row"><label for="default_payment_method"><?php _e( 'Default Payment Method', 'wpsc_mo' ); ?>:</label></th>
			<td>
	<?php if( $payment_methods ) { ?>
										<select id="default_payment_method" name="default_payment_method">
		<?php foreach( $payment_methods as $key => $payment_method ) { ?>
											<option value="<?php echo $key; ?>" <?php echo selected( $key, $default_payment_method ); ?>><?php echo $payment_method['name']; ?></option>
		<?php } ?>
										</select>
	<?php } ?>
				<p class="description"><?php _e( 'The default Payment Method.', 'wpsc_mo' ); ?></p>
			</td>
		</tr>

		<tr>
			<th scope="row"><label for="show_session_id"><?php _e( 'Default Payment Method', 'wpsc_mo' ); ?>:</label></th>
			<td>
				<label>
					<input type="checkbox" id="show_session_id" name="show_session_id" <?php checked( $show_session_id, 'on' ); ?> />
					<?php _e( 'Display the Session ID after each Sale', 'wpsc_mo' ); ?>
				</label>
				<p class="description"><?php _e( 'Control the display of the Session ID after each Sale.', 'wpsc_mo' ); ?></p>
			</td>
		</tr>

	</table>

	<p class="submit">
		<input type="submit" value="<?php _e( 'Save Changes', 'wpsc_mo' ); ?>" class="button-primary" />
	</p>
	<input type="hidden" name="action" value="update" />

</form>