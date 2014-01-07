<?php
wp_enqueue_script( 'pluginbuddy-reorder-js', $this->_parent->_pluginURL . '/js/tablednd.js', array('jquery') );
wp_enqueue_script( 'thickbox', 'dashboard' );
wp_print_scripts( array( 'thickbox', 'dashboard', 'pluginbuddy-reorder-js' ) );
wp_print_styles( 'thickbox' );

?>
<script type='text/javascript'>
jQuery(document).ready(function() {
	jQuery('#pb_reorder').tableDnD({
		onDrop: function(tbody, row) {
	//alert( jQuery.tableDnD.serialize() );
			jQuery( '#pb_order' ).val( jQuery.tableDnD.serialize() )
		},
		dragHandle: "dragHandle"
	});
});
</script>
<?php

if ( isset( $_POST[ 'delete_accordion_items' ] ) && isset( $_POST[ 'items' ] ) ) {
	if ( !wp_verify_nonce( $_REQUEST[ 'pb_accordion' ], 'pb-delete-accordion-items' ) ) {
		$this->_parent->alert( __( 'Accordion item could not be deleted - Security credentials could not be validated', 'it-l10n-accordion' ), true );
	} else {
		$items = $_POST[ 'items' ];
		$count = 0;
		foreach ( $items as $item_id ) {
			$count += 1;
			//Delete the item
			wp_delete_post( $item_id, true );
		} //end foreach items
		$this->_parent->alert( sprintf( _n( '%s accordion item deleted', '%s accordion items deleted', $count, 'it-l10n-accordion' ), number_format( $count ) ), false );	
	}//end verify nonce

} //end post delete_accordion_items
if ( isset( $_POST[ 'save_order' ] ) ) {
	if ( !wp_verify_nonce( $_REQUEST[ 'pb_accordion' ], 'pb-delete-accordion-items' ) ) {
		$this->_parent->alert( __( 'Accordion item could not be sorted - Security credentials could not be validated', 'it-l10n-accordion' ), true );
		die( '' );
	}
	
	if ( empty( $_POST['order'] ) ) {
		$this->alert( 'No changes made to order.' );
	} else {
		$order = str_replace( 'pb_reorder[]=', '', $_POST['order'] );
		$order = str_replace( 'accordion_item_', '', $order );
		$orders = explode( '&', $order );
		
		//Update the post's menu order parameter
		if ( is_array( $orders ) ) {		
			foreach ( $orders as $key => $post_id ) {
				$post = get_post( $post_id, ARRAY_A );
				$post[ 'menu_order' ] = $key + 1;
				wp_update_post( $post );
			} //end foreach $orders
		} //end is_array( $order )
		
		$this->alert( __( 'The Accordion Item order has been updated.', 'it-l10n-accordion' ) );
	}
}
?>
<h2><?php __( 'Accordion Items', 'it-l10n-accordion' ); ?></h2>
<?php
	$form_url = add_query_arg( array(
		'edit' => absint( $_GET[ 'edit' ] )
	), $this->_selfLink . '-settings' );
	$ajax_assist_url = add_query_arg( 
		array(
			'action' => 'pb_accordion_add_item',
			'_ajax_nonce' => wp_create_nonce( 'pb-add-accordion-item' ),
			'parent_id' => absint( $_GET[ 'edit' ] ),
			'TB_iframe' => true
		),
		admin_url( 'admin-ajax.php' )
	);
?>
		<form id="posts-filter" enctype="multipart/form-data" method="post" action="<?php echo esc_url( $form_url ); ?>">
		<input type="hidden" name="order" id="pb_order" value="" />
		<div class="tablenav">
		<div class="alignleft actions">
			<input type="submit" name="delete_accordion_items" value="<?php esc_attr_e( 'Remove', 'it-l10n-accordion' ); ?>" class="button-secondary delete" title="<?php esc_attr_e( 'Remove this accordion item from this accordion', 'it-l10n-accordion' ); ?>" />
		</div>
		<div class="alignright actions">
			<input type="submit" name="save_order" id="save_order" value="<?php esc_attr_e( 'Save Order', 'it-l10n-accordion' ); ?>" class="button-secondary" />
		</div>
	</div><!--/.tablenav-->
	<table class="widefat">
		<thead>
			<tr class="thead">
				<th scope="col" class="check-column"><input type="checkbox" class="check-all-entries" /></th>
				<th><?php esc_html_e( 'Item Title', 'it-l10n-accordion' ); ?></th>
				<th><?php esc_html_e( 'Content', 'it-l10n-accordion' ); ?></th>
				<th><?php esc_html_e( 'Post ID', 'it-l10n-accordion' ); ?></th>
				<th><?php esc_html_e( 'Reorder', 'it-l10n-accordion' ); ?></th>
			</tr>
		</thead>
		<tfoot>
			<tr class="thead">
				<th scope="col" class="check-column"><input type="checkbox" class="check-all-entries" /></th>
				<th><?php esc_html_e( 'Item Title', 'it-l10n-accordion' ); ?></th>
				<th><?php esc_html_e( 'Content', 'it-l10n-accordion' ); ?></th>
				<th><?php esc_html_e( 'Post ID', 'it-l10n-accordion' ); ?></th>
				<th><?php esc_html_e( 'Reorder', 'it-l10n-accordion' ); ?></th>
			</tr>
		</tfoot>
		<tbody id="pb_reorder">
			<?php
				$args = array(
					'post_type' => 'pb_accordion_child',
					'post_parent' => absint( $_GET[ 'edit' ] ),
					'orderby' => 'menu_order',
					'posts_per_page' => -1,
					'numberposts' => -1,
					'order' => 'ASC'
					
				);
				$accordion_items = get_posts( $args );

				if ( $accordion_items ) {
					foreach ( $accordion_items as $item ) {
						echo $this->get_accordion_item_html( $item );
					} //end foreach $accordion_items
				} else {
					?>
					<?php
				} //end if $accordion_items
			
			?>
			
		</tbody>
	</table>
	<div class="tablenav">
		<div class="alignleft actions">
			<input type="submit" name="delete_accordion_items" value="<?php esc_attr_e( 'Remove', 'it-l10n-accordion' ); ?>" class="button-secondary delete" title="<?php esc_attr_e( 'Remove this accordion item from this accordion', 'it-l10n-accordion' ); ?>" />
		</div>
		<div class="alignright actions">
			<input type="submit" name="save_order" id="save_order" value="<?php esc_attr_e( 'Save Order', 'it-l10n-accordion' ); ?>" class="button-secondary" />
		</div>
	</div><!--/.tablenav-->
	<?php wp_nonce_field( 'pb-delete-accordion-items', 'pb_accordion' ); ?>
</form>
<h3><?php esc_html_e( 'Add Accordion Items', 'it-l10n-accordion' ); ?></h3>
<div class="tablenav">
		<div class="alignleft actions">
			<?php
?>
<a class='button-primary thickbox' title='<?php esc_attr_e( 'Add Accordion Item' ); ?>' href='<?php echo esc_url( $ajax_assist_url ); ?>'><?php esc_attr_e( 'Add an Accordion Item', 'it-l10n-accordion' ); ?></a>		
</div><!--/.alignleft actions-->
</div><!--/.tablenav-->
<h3><?php esc_html_e( 'Edit Accordion Settings', 'it-l10n-accordion' ); ?></h3>
	<?php
	//advise user of possible errors when using horizontal theme and -1 index
	if( isset($_POST['accordion_settings']['theme'] ) && (($_POST['accordion_settings']['theme'] == 'accordion-horizontal-1' || $_POST['accordion_settings']['theme'] == 'accordion-horizontal-2' ) && $_POST['accordion_settings']['selected_index'] == -1 )) 
	{
		$this->alert(_( 'Using a Horizontal Theme with an Index of -1 May Result in Unexpected Behavior'), true);
	}
	//Save data
	if ( isset( $_POST[ 'edit_accordion_settings' ] ) ) {
		$post_id = isset( $_POST[ 'post_id' ] ) ? absint( $_POST[ 'post_id' ] ) : 0;
		if ( $post_id == 0 ) {
			$this->alert( __( 'Accordion settings could not be saved', 'it-l10n-accordion' ), true );
		} else {
			$this->alert( __( 'Accordion settings saved', 'it-l10n-accordion' ), false );
			//Sanitize post meta
			foreach ( $_POST[ 'accordion_settings' ] as $key => &$value ) {
				if ( $key == 'selected_index' ) {
					$value = intval( $value );
				} elseif ( in_array( $key, array( 'accordion_max_width', 'accordion_max_height', 'accordion_item_max_width', 'accordion_item_max_height' ) ) ) {
					//Sanitize percentage values
					$value = $this->sanitize_percentage( $value );
				} elseif ( is_string( $value ) ) {
					$value = sanitize_text_field( $value );
				} elseif ( is_numeric( $value  ) ) {
					$value = absint( $value );
				} else {
					$value = false;
				}	

			} //end foreach settings
			//Save image sizes
			$image_width = absint( $_POST[ 'accordion_settings' ][ 'thumbnail_width' ] );
			$image_height = absint( $_POST[ 'accordion_settings' ][ 'thumbnail_height' ] );
			if ( $image_width > 0 && $image_height > 0 ) {
				$this->_parent->_options[ 'sizes' ][] = array(
					'image_width' => $image_width,
					'image_height' => $image_height
				);
			}
			$this->_parent->save();
			//Save the post meta
			update_post_meta( $post_id, 'accordion_settings', $_POST[ 'accordion_settings' ] );
		}
	}
	//Get defaults
	$form_url = add_query_arg( array(
		'edit' => absint( $_GET[ 'edit' ] )
	), $this->_selfLink . '-settings' );
	$parent_id = absint( $_GET[ 'edit' ] );
	$accordion_settings = get_post_meta( $parent_id, 'accordion_settings', true );
	$defaults = $this->_parent->get_accordion_defaults();
	if ( !is_array( $accordion_settings ) ) {
		update_post_meta( $parent_id, 'accordion_settings', $defaults );
		$accordion_settings = array();	
	}

	$accordion_settings = wp_parse_args( $accordion_settings, $defaults );
	
	
?>
	<form id="accordion-edit-form" method="post" action="<?php echo esc_url( $form_url ); ?>">
	<input type='hidden' name='post_id' value='<?php echo esc_attr( absint( $_GET[ 'edit' ] ) ); ?>' />
	<table class="widefat clear">
		<tr>
			<td><?php esc_html_e( 'Selected Index', 'it-l10n-accordion' ); ?><?php $this->tip( __( "Choose which item to display by default - Enter -1 for a closed Accordion, the closed option only works in verticle mode", 'it-l10n-accordion' ) ); ?></td>
			<td><input type='text' name='accordion_settings[selected_index]' value='<?php echo esc_attr( intval( $accordion_settings[ 'selected_index' ] ) ); ?>' /></td>
		</tr>
		<tr>
			<td><?php esc_html_e( 'Show Post Thumbnail', 'it-l10n-accordion' ); ?><?php $this->tip( __( "Displays a post's featured image above the Accordion item content", 'it-l10n-accordion' ) ); ?></td>
			<td>
				<select name='accordion_settings[post_thumbnail]'>
					<option value='on' <?php selected( $accordion_settings[ 'post_thumbnail' ], 'on' ); ?>>Yes</option>
					<option value='off' <?php selected( $accordion_settings[ 'post_thumbnail' ], 'off' ); ?>>No</option>
				</select>
			</td>
		</tr>
		<tr>
			<td><?php esc_html_e( 'Thumbnail links to post', 'it-l10n-accordion' ); ?><?php $this->tip( __( "Thumbnail will link back to the original post", 'it-l10n-accordion' ) ); ?></td>
			<td>
				<select name='accordion_settings[thumb_link]'>
					<option value='on' <?php selected( $accordion_settings[ 'thumb_link' ], 'on' ); ?>>Yes</option>
					<option value='off' <?php selected( $accordion_settings[ 'thumb_link' ], 'off' ); ?>>No</option>
				</select>
			</td>
		</tr>
		<tr class='alternate'>
			<td><?php esc_html_e( 'Post Thumbnail Size', 'it-l10n-accordion' ); ?><?php $this->tip( __( "Select a thumbnail size from some build-in options.  Select custom if you want to specify your own image width and height.", 'it-l10n-accordion' ) ); ?></td>
			<td>
				<?php
				//Add image sizes (which are dynamic )
				global $_wp_additional_image_sizes;
				$sizes = array( 'thumbnail', 'medium', 'large', 'full', 'post-thumbnail', 'custom' );
				if ( is_array( $_wp_additional_image_sizes ) ) { /* todo - this will break eventually, find a better way */
					foreach ( $_wp_additional_image_sizes as $image_size => $size ) {
						if ( !in_array( $image_size, $sizes ) ) $sizes[] = $image_size;
					}
				}
				?>
				<select name='accordion_settings[thumbnail_size]'>
					<?php
					foreach ( $sizes as $size ) {
						?>
					<option value='<?php echo esc_attr( $size ); ?>' <?php selected( $accordion_settings[ 'thumbnail_size' ], $size ); ?>><?php echo esc_html( $size ); ?></option>
						<?php
					} //end foreach
					?>
				</select>
			</td>
		</tr>
		<tr>
			<td><?php esc_html_e( 'Thumbnail Width (if custom)', 'it-l10n-accordion' ); ?><?php $this->tip( __( "Select a maximum width for the thumbnail (this value should be an integer)", 'it-l10n-accordion' ) ); ?></td>
			<td><input type='text' name='accordion_settings[thumbnail_width]' value='<?php echo esc_attr( absint( $accordion_settings[ 'thumbnail_width' ] ) ); ?>' /></td>
		</tr>
		<tr class='alternate'>
			<td><?php esc_html_e( 'Thumbnail Height (if custom)', 'it-l10n-accordion' ); ?><?php $this->tip( __( "Select a maximum height for the thumbnail (this value should be an integer)", 'it-l10n-accordion' ) ); ?></td>
			<td><input type='text' name='accordion_settings[thumbnail_height]' value='<?php echo esc_attr( absint( $accordion_settings[ 'thumbnail_height' ] ) ); ?>' /></td>
		</tr>
		<tr>
			<td><?php esc_html_e( 'Accordion Theme', 'it-l10n-accordion' ); ?><?php $this->tip( __( "Select which theme you would like this Accordion to have - Select none to apply your own styles", 'it-l10n-accordion' ) ); ?></td>
			<td>
				<select name='accordion_settings[theme]'>
					<option value='accordion-vertical-1' <?php selected( $accordion_settings[ 'theme' ], 'accordion-vertical-1' ); ?>>Accordion Vertical 1</option>
					<option value='accordion-vertical-2' <?php selected( $accordion_settings[ 'theme' ], 'accordion-vertical-2' ); ?>>Accordion Vertical 2</option>
					<option value='accordion-horizontal-1' <?php selected( $accordion_settings[ 'theme' ], 'accordion-horizontal-1' ); ?>>Accordion Horizontal 1</option>
					<option value='accordion-horizontal-2' <?php selected( $accordion_settings[ 'theme' ], 'accordion-horizontal-2' ); ?>>Accordion Horizontal 2</option>
					<option value='accordion-custom' <?php selected( $accordion_settings[ 'theme' ], 'accordion-custom' ); ?>><?php esc_html_e( 'None', 'it-l10n-accordion' ); ?></option>
				</select>
			</td>
		</tr>
		<tr class='alternate'>
			<td><?php esc_html_e( 'Accordion Tab Type (for horizontal themes)', 'it-l10n-accordion' ); ?><?php $this->tip( __( "For horizontal themes, select whether you want the tab to be text or to use a post's featured image", 'it-l10n-accordion' ) ); ?></td>
			<td>
				<select name='accordion_settings[accordion_tab_type]'>
					<!--<option value='thumbnail' <?php selected( $accordion_settings[ 'accordion_tab_type' ], 'title' ); ?>>Item Title (Coming soon - default is thumbnail)</option>-->
					<option value='thumbnail' <?php selected( $accordion_settings[ 'accordion_tab_type' ], 'thumbnail' ); ?>>Post Thumbnail</option>
				</select>
			</td>
		</tr>
		<tr>
			<td><?php esc_html_e( 'Accordion Max Width (for Vertical and Horizontal themes)', 'it-l10n-accordion' ); ?><?php $this->tip( __( "Choose the maximum width for this Accordion", 'it-l10n-accordion' ) ); ?></td>
			<td><input type='text' name='accordion_settings[accordion_max_width]' value='<?php echo esc_attr( $this->sanitize_percentage( $accordion_settings[ 'accordion_max_width' ] ) ); ?>' /></td>
		</tr>
		<tr class='alternate'>
			<td><?php esc_html_e( 'Accordion Max Height (for Horizontal themes)', 'it-l10n-accordion' ); ?><?php $this->tip( __( "Choose the maximum height for this Accordion (Horizontal themes only)", 'it-l10n-accordion' ) ); ?></td>
			<td><input type='text' name='accordion_settings[accordion_max_height]' value='<?php echo esc_attr( $this->sanitize_percentage( $accordion_settings[ 'accordion_max_height' ] ) ); ?>' /></td>
		</tr>
		<tr>
			<td><?php esc_html_e( 'Accordion Item Max Width (for Horizontal themes)', 'it-l10n-accordion' ); ?><?php $this->tip( __( "Choose the maximum width for Accordion items (Horizontal themes only)", 'it-l10n-accordion' ) ); ?></td>
			<td><input type='text' name='accordion_settings[accordion_item_max_width]' value='<?php echo esc_attr( $this->sanitize_percentage( $accordion_settings[ 'accordion_item_max_width' ] ) ); ?>' /></td>
		</tr>
		<tr class='alternate'>
			<td><?php esc_html_e( 'Accordion Item Max Height (for Vertical themes)', 'it-l10n-accordion' ); ?><?php $this->tip( __( "Select a maximum height for Accordion items (Vertical themes only)", 'it-l10n-accordion' ) ); ?></td>
			<td><input type='text' name='accordion_settings[accordion_item_max_height]' value='<?php echo esc_attr( $this->sanitize_percentage( $accordion_settings[ 'accordion_item_max_height' ] ) ); ?>' /></td>
		</tr>

		<tr>
			<td><?php esc_html_e( 'Accordion Slide Up Transition Speed (milliseconds)', 'it-l10n-accordion' ); ?><?php $this->tip( __( "Select how long (in milliseconds) the Accordion should slide up content (for Vertical themes only)", 'it-l10n-accordion' ) ); ?></td>
			<td><input type='text' name='accordion_settings[accordion_slide_up]' value='<?php echo esc_attr( absint( $accordion_settings[ 'accordion_slide_up' ] ) ); ?>' /></td>
		</tr>
		<tr class='alternate'>
			<td><?php esc_html_e( 'Accordion Slide Down Transition Speed (milliseconds)', 'it-l10n-accordion' ); ?><?php $this->tip( __( "Select how long (in milliseconds) the Accordion should slide down content (for Vertical themes only)", 'it-l10n-accordion' ) ); ?></td>
			<td><input type='text' name='accordion_settings[accordion_slide_down]' value='<?php echo esc_attr( absint( $accordion_settings[ 'accordion_slide_up' ] ) ); ?>' /></td>
		</tr>
		<tr>
			<td><?php esc_html_e( 'Accordion Slide Left Transition Speed (milliseconds)', 'it-l10n-accordion' ); ?><?php $this->tip( __( "Select how long (in milliseconds) the Accordion should slide left content (for Horizontal themes only)", 'it-l10n-accordion' ) ); ?></td>
			<td><input type='text' name='accordion_settings[accordion_slide_left]' value='<?php echo esc_attr( absint( $accordion_settings[ 'accordion_slide_left' ] ) ); ?>' /></td>
		</tr>
		<tr class='alternate'>
			<td><?php esc_html_e( 'Accordion Slide Right Transition Speed (milliseconds)', 'it-l10n-accordion' ); ?><?php $this->tip( __( "Select how long (in milliseconds) the Accordion should slide right content (for Horizontal themes only)", 'it-l10n-accordion' ) ); ?></td>
			<td><input type='text' name='accordion_settings[accordion_slide_right]' value='<?php echo esc_attr( absint( $accordion_settings[ 'accordion_slide_right' ] ) ); ?>' /></td>
		</tr>
		<tr>
			<td><?php esc_html_e( 'Open Items on Hover?', 'it-l10n-accordion' ); ?><?php $this->tip( __( "Would you like the tab items to open on a mouse over?", 'it-l10n-accordion' ) ); ?></td>
			<td>
				<select name='accordion_settings[hover_items]'>
					<option value='on' <?php selected( $accordion_settings[ 'hover_items' ], 'on' ); ?>><?php esc_html_e( 'Yes', 'it-l10n-accordion' ); ?></option>
					<option value='off' <?php selected( $accordion_settings[ 'hover_items' ], 'off' ); ?>><?php esc_html_e( 'No', 'it-l10n-accordion' ); ?></option>
				</select>
			</td>
		</tr>
	</table>
	<div class="tablenav">
		<div class="alignleft actions">
			<input type="submit" name="edit_accordion_settings" value="<?php esc_attr_e( 'Submit', 'it-l10n-accordion' ); ?>" class="button-primary" title="<?php esc_attr_e( 'Submit Accordion Settings', 'it-l10n-accordion' ); ?>" />
		</div>
	</div><!--/.tablenav-->
	</form>
