<?php
wp_enqueue_script( 'thickbox' );
wp_print_scripts( 'thickbox' );
wp_print_styles( 'thickbox' );

// Handles resizing thickbox.
if ( !wp_script_is( 'media-upload' ) ) {
	wp_enqueue_script( 'media-upload' );
	wp_print_scripts( 'media-upload' );
}

wp_enqueue_script( 'pluginbuddy-reorder-js', $this->_parent->_pluginURL . '/js/tablednd.js' );
wp_print_scripts( 'pluginbuddy-reorder-js' );

/*
$group = &$this->_options['groups'][$_GET['edit']];
$combined_group = array_merge( $this->_parent->_groupdefaults, (array)$group );
if ( $combined_group !== $group ) {
	// Defaults existed that werent already in the options so we need to update their settings to include some new options.
	$group = $combined_group;
	$this->_parent->save();
}
*/
$group = &$this->_parent->get_group( $_GET['edit'] );

if ( !empty( $_POST['save'] ) ) {
	$this->savesettings();
}

if ( !empty( $_POST['attachment_data'] ) ) {

	$attachment_data = unserialize( stripslashes( $_POST['attachment_data'] ) );

	array_push( $group['images'], $attachment_data['attachment_id'] );
	$this->_parent->save();
	
	$this->alert( 'Added the selected image to this group.' );
}

if ( isset( $_POST['delete_images'] ) ) {
	check_admin_referer( $this->_var . '-nonce' );
	
	if ( ! empty( $_POST['items'] ) && is_array( $_POST['items'] ) ) {
		$deleted_images = 0;
	
		foreach ( (array) $_POST['items'] as $id ) {
			$deleted_images++;
			unset( $this->_options['groups'][ $_GET['edit'] ]['images'][$id] );
		}
		
		$this->_parent->save();
		$this->alert( 'Removed ' . $deleted_images . ' images from this group.' );
	}
}
if ( !empty( $_GET['image_update'] ) ) {
	$this->alert( 'Image settings updated.' );
}

// Re-orders the array of images; index order matches the user selected order.
if ( !empty( $_POST['save_order'] ) ) {
	check_admin_referer( $this->_var . '-nonce' );
	
	if ( empty( $_POST['order'] ) ) {
		$this->alert( 'No changes made to order.' );
	} else {
		$order = str_replace( 'pb_reorder[]=', '', $_POST['order'] );
		$orders = explode( '&', $order );
		
		$new_images = array();
		$old_images = &$this->_options['groups'][ $_GET['edit'] ]['images'];
		foreach( $orders as $key => $value ) {
			$new_images[$key] = $old_images[$value];
		}
		
		$this->_options['groups'][ $_GET['edit'] ]['images'] = &$new_images;
		$this->_parent->save();
		$this->alert( 'The image order has been updated.' );
	}
}
?>

<h2><?php echo $this->_name; ?> Group Settings for "<?php echo stripslashes( $group['title'] ); ?>" (<a href="<?php echo $this->_parent->_selfLink; ?>-settings">group list</a>)</h2>

<br />

<form method="post" id="pb_attachment_form" action="<?php echo $this->_selfLink; ?>-settings&edit=<?php echo htmlentities( $_GET['edit'] ); ?>">
	<input type="hidden" name="attachment_data" id="pb_attachment_data" value="" />
</form>
<script type="text/javascript">
	function pb_medialibrary( $response ) {
		jQuery('#pb_attachment_data').val( $response );
		jQuery('#pb_attachment_form').submit();
	}
	function pb_medialibrary_edit( $response ) {
		//alert( $response );
		window.location.href = '<?php echo $this->_selfLink; ?>-settings&edit=<?php echo htmlentities( $_GET['edit'] ); ?>&image_update=true';
	}
	
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


<form id="posts-filter" enctype="multipart/form-data" method="post" action="<?php echo $this->_selfLink; ?>-settings&edit=<?php echo htmlentities( $_GET['edit'] ); ?>">
	<div class="tablenav">
		<div class="alignleft actions">
			<input type="submit" name="delete_images" value="Remove" class="button-secondary delete" title="Remove image from this group. It will not be deleted from the Media Library." />
			<a href="<?php echo $this->_parent->_medialibrary->get_link(); ?>" class="button button-primary thickbox">+ Add Image</a>
			<input type="hidden" name="order" id="pb_order" value="" />
		</div>
		
		<div class="alignright actions">
			<input type="submit" name="save_order" id="save_order" value="Save Order" class="button-secondary" />
		</div>
	</div>
	<table class="widefat">
		<thead>
			<tr class="thead">
				<th scope="col" class="check-column"><input type="checkbox" class="check-all-entries" /></th>
				<th>Image</th>
				<th>Title + Caption</th>
				<th>File Details</th>
				<th>Reorder</th>
			</tr>
		</thead>
		<tfoot>
			<tr class="thead">
				<th scope="col" class="check-column"><input type="checkbox" class="check-all-entries" /></th>
				<th>Image</th>
				<th>Title + Caption</th>
				<th>File Details</th>
				<th>Reorder</th>
			</tr>
		</tfoot>
		<tbody id="pb_reorder">
			<?php
			if ( empty( $group['images'] ) ) {
				echo '<tr><td colspan="5" style="text-align: center;"><i>Please add an image to this group with the button on the right to get started.</i></td></tr>';
			} else {
				foreach ( (array) $group['images'] as $id => $image_id ) {
				
					$attachment_data = get_post( $image_id );
					if ( empty( $attachment_data ) ) {
						$this->alert( 'An image (ID: ' . $image_id . ') in this group was not found in the Media Library. Removing image from group...' );
						unset( $this->_options['groups'][ $_GET['edit'] ]['images'][$id] );
						$this->_parent->save();
					} else {
						/*
						echo '<pre>';
						print_r( $attachment_data );
						echo '</pre>';
						*/
					
						?>
						<tr class="entry-row alternate" id="entry-<?php echo $id; ?>">
							<th scope="row" class="check-column"><input type="checkbox" name="items[]" class="entries" value="<?php echo $id; ?>" /></th>
							<td style="min-width: 160px;">
								<?php
								//$image_dat = wp_get_attachment_image_src( $attachment_data->ID, array( 50, 50 ) );
								$image_dat = wp_get_attachment_image_src( $attachment_data->ID );
								
								/*
								echo '<pre>';
								print_r( $image_dat );
								echo '</pre>';
								*/
								?>
								<img src="<?php echo $image_dat[0]; ?>" width="100" height="100" style="float: left; margin-right: 10px;" />
								<div class="row-actions" style="margin: 0; padding: 0;">
									<a href="<?php echo $this->_parent->_medialibrary->get_edit_link( $attachment_data->ID ); ?>" class="thickbox">Edit Image Settings</a>
								</div>
							</td>
							<td>
								<?php
								if ( !empty( $attachment_data->post_content ) && ( ( stristr( $attachment_data->post_content, 'http' ) ) || ( stristr( $attachment_data->post_content, 'http' ) ) ) ) {
									echo '<b><a href="' . $attachment_data->post_content . '">' . stripslashes( $attachment_data->post_title ) . '</a></b> <i>(link)</i>';
								} else {
									echo '<b>' . stripslashes( $attachment_data->post_title ) . '</b> <i>(no link)</i>';
								}
								echo '<br />';
								if ( !empty( $attachment_data->post_excerpt ) ) {
									echo stripslashes( $attachment_data->post_excerpt );
								} else {
									echo '<i>(no caption)</i>';
								}
								?>
							</td>
							<td>
								Original Image:<br />
								<?php echo '<a href="' . $attachment_data->guid . '">' . basename( $attachment_data->guid ) . '</a>'; ?><br /><br />
								Uploaded:<br />
								<span title="<?php echo $attachment_data->post_date; ?>"><?php echo human_time_diff( strtotime( $attachment_data->post_date ), time() ); ?> ago</span>
							</td>
							<td class="dragHandle">
								<img src="<?php echo $this->_pluginURL; ?>/images/draghandle2.png" alt="Click and drag to reorder" />
							</td>
						</tr>
						<?php
					}
				}
			}
			?>
		</tbody>
	</table>
	<div class="tablenav">
		<div class="alignleft actions">
			<input type="submit" name="delete_images" value="Remove" class="button-secondary delete" title="Remove image from this group. It will not be deleted from the Media Library." />
			<a href="<?php echo $this->_parent->_medialibrary->get_link(); ?>" class="button button-primary thickbox">+ Add Image</a>
		</div>
		
		<div class="alignright actions">
			<input type="submit" name="save_order" id="save_order" value="Save Order" class="button-secondary" />
		</div>
	</div>
	<?php $this->nonce(); ?>
</form><br />

<form method="post" action="<?php echo $this->_selfLink; ?>-settings&edit=<?php echo htmlentities( $_GET['edit'] ); ?>">
	
	<b>Slide Image Dimensions:</b>
	<label for="image_width"></label><input type="text" name="#image_width" id="image_width" size="5" maxlength="5" style="text-align: right;" value="<?php echo $group['image_width']; ?>" />px wide
	<label for="image_height"></label><input type="text" name="#image_height" id="image_height" size="5" maxlength="5" style="text-align: right;" value="<?php echo $group['image_height']; ?>" />px high. Images will be adjusted to this size.
	<?php $this->tip('This controls the size of the images in the Carousel. Images will be generated from the original images uploaded. Images will not be upscaled larger than the originals. You may change this at any time.'); ?>
	<br />
	
	
	<?php // Ex. for saving in a group you might do something like: $this->_options['groups'][$_GET['group_id']['settings'] which would be: ['groups'][$_GET['group_id']['settings'] ?>
	<input type="hidden" name="savepoint" value="groups#<?php echo $_GET['edit']; ?>" />
	
	
	<table class="form-table">
		<tr><td><h2>Carousel Settings</h2></td><td style="min-width: 450px;"></td></tr>
		<tr>
			<td><label for="title">Group Title<?php $this->tip( 'Title of the group. This is for internal reference only.' ); ?></label></td>
			<td><input type="text" name="#title" id="title" size="45" maxlength="45" value="<?php echo stripslashes( $group['title'] ); ?>" /></td>
		</tr>
		<tr>
			<td><label for="direction">Carousel orientation & direction <?php $this->tip( 'Controls the animation direction and orientation of the Carousel.' ); ?></label></td>
			<td>
				<select name="#direction" id="direction">
					<option value="left" <?php if ( 'left' == $group['direction'] ) { echo 'selected'; } ?> />Horizontal - Slide left</option>
					<option value="right" <?php if ( 'right' == $group['direction'] ) { echo 'selected'; } ?> />Horizonal - Slide right</option>
					<option value="up" <?php if ( 'up' == $group['direction'] ) { echo 'selected'; } ?> />Vertical - Slide up</option>
					<option value="down" <?php if ( 'down' == $group['direction'] ) { echo 'selected'; } ?> />Vertical - Slide down</option>
				</select>
			</td>
		</tr>
		<tr>
			<td><label for="align">Horizontal Alignment <?php $this->tip( 'Controls the horizontal alignment of the Carousel in its container.' ); ?></label></td>
			<td>
				<select name="#align" id="align">
					<option value="left" <?php if ( 'left' == $group['align'] ) { echo 'selected'; } ?> />Left</option>
					<option value="center" <?php if ( 'center' == $group['align'] ) { echo 'selected'; } ?> />Center</option>
					<option value="right" <?php if ( 'right' == $group['align'] ) { echo 'selected'; } ?> />Right</option>
				</select>
			</td>
		</tr>
		<tr>
			<td><label for="entity_width">Entity width<?php $this->tip( 'Width of the entire Carousel itself.' ); ?></label></td>
			<td><input type="text" name="#entity_width" id="entity_width" size="5" maxlength="5" value="<?php echo $group['entity_width']; ?>" style="text-align: right;" />px wide</td>
		</tr>
		<tr>
			<td><label for="items">Images to show at once<?php $this->tip( 'Number of images to display in the Carousel at once. Additional images will be scrolled either automatically or manually using navigation arrows or bullets.' ); ?></label></td>
			<td><input type="text" name="#items" id="items" size="5" maxlength="5" value="<?php echo $group['items']; ?>" style="text-align: right;" />images</td>
		</tr>
		<tr>
			<td><label for="scroll_duration">Scroll duration<?php $this->tip( 'Determines how long the scroll transition will run, in milliseconds.' ); ?></label></td>
			<td><input type="text" name="#scroll_duration" id="scroll_duration" size="5" maxlength="5" value="<?php echo $group['scroll_duration']; ?>" style="text-align: right;" />ms</td>
		</tr>
		<tr>
			<td><label for="auto_play">Auto scroll<?php $this->tip( 'When enabled, the Carousel will automatically rotate through all of the images.' ); ?></label></td>
			<td>
				<label for="auto_play-1">
					<input class="radio_toggle" type="radio" name="#auto_play" id="auto_play-1" value="true" onclick="jQuery('.pb_auto_on').show();" <?php if ( $group['auto_play'] == 'true' ) { echo ' checked '; } ?>/>
					Yes
				</label>
				&nbsp;
				<label for="auto_play-0">
					<input class="radio_toggle" type="radio" name="#auto_play" id="auto_play-0" value="false" onclick="jQuery('.pb_auto_on').hide();" <?php if ( $group['auto_play'] == 'false' ) { echo ' checked '; } ?>/>
					No
				</label>
			</td>
		</tr>
		<tr class="pb_auto_on" <?php if ( $group['auto_play'] == 'false' ) { echo ' style="display: none;" '; } ?>>
			<td><label for="auto_pauseDuration">Pause duration (auto mode)<?php $this->tip( 'The amount of milliseconds the carousel will pause when in auto mode.' ); ?></label></td>
			<td><input type="text" name="#auto_pauseDuration" id="auto_pauseDuration" size="5" maxlength="5" value="<?php echo $group['auto_pauseDuration']; ?>" style="text-align: right;" />ms</td>
		</tr>
		<tr class="pb_auto_on" <?php if ( $group['auto_play'] == 'false' ) { echo ' style="display: none;" '; } ?>>
			<td><label for="auto_delay">Start delay (auto mode)<?php $this->tip( 'Additional delay in milliseconds before the carousel starts scrolling the first time when in auto mode.' ); ?></label></td>
			<td><input type="text" name="#auto_delay" id="auto_delay" size="5" maxlength="5" value="<?php echo $group['auto_delay']; ?>" style="text-align: right;" />ms</td>
		</tr>
		<tr class="pb_auto_on" <?php if ( $group['auto_play'] == 'false' ) { echo ' style="display: none;" '; } ?>>
			<td><label for="scroll_pauseOnHover">Pause on Hover (auto mode)<?php $this->tip( 'When enabled, hovering the mouse over the Carousel will cause it to pause and not continue rotating.' ); ?></label></td>
			<td>
				<label for="scroll_pauseOnHover-1">
					<input class="radio_toggle" type="radio" name="#scroll_pauseOnHover" id="scroll_pauseOnHover-1" value="true" <?php if ( $group['scroll_pauseOnHover'] == 'true' ) { echo ' checked '; } ?>/>
					Yes
				</label>
				&nbsp;
				<label for="scroll_pauseOnHover-0">
					<input class="radio_toggle" type="radio" name="#scroll_pauseOnHover" id="scroll_pauseOnHover-0" value="false" <?php if ( $group['scroll_pauseOnHover'] == 'false' ) { echo ' checked '; } ?>/>
					No
				</label>
			</td>
		</tr>
		<tr>
			<td><label for="random_order">Randomize image order each page load<?php $this->tip( 'When enabled, the order of images within the Carousel will be randomly ordered each page load. This is useful for things like displaying sponsors or affiliates.' ); ?></label></td>
			<td>
				<label for="random_order-1">
					<input class="radio_toggle" type="radio" name="#random_order" id="random_order-1" value="true" <?php if ( $group['random_order'] == 'true' ) { echo ' checked '; } ?>/>
					Yes
				</label>
				&nbsp;
				<label for="random_order-0">
					<input class="radio_toggle" type="radio" name="#random_order" id="random_order-0" value="false" <?php if ( $group['random_order'] == 'false' ) { echo ' checked '; } ?>/>
					No
				</label>
			</td>
		</tr>
		<tr>
			<td><label for="circular">Circular loop<?php $this->tip( 'When enabled, when the user proceeds past the end of the Carousel the user will be sent back to the beginning.' ); ?></label></td>
			<td>
				<label for="circular-1">
					<input class="radio_toggle" type="radio" name="#circular" id="circular-1" value="true" <?php if ( $group['circular'] == 'true' ) { echo ' checked '; } ?>/>
					Yes
				</label>
				&nbsp;
				<label for="circular-0">
					<input class="radio_toggle" type="radio" name="#circular" id="circular-0" value="false" <?php if ( $group['circular'] == 'false' ) { echo ' checked '; } ?>/>
					No
				</label>
			</td>
		</tr>
		<tr>
			<td><label for="infinite">Infinite<?php $this->tip( 'When enabled, the Carousel may go infinitely in one direction. When disabled there will be no more images after the end and the user must click back.' ); ?></label></td>
			<td>
				<label for="infinite-1">
					<input class="radio_toggle" type="radio" name="#infinite" id="infinite-1" value="true" <?php if ( $group['infinite'] == 'true' ) { echo ' checked '; } ?>/>
					Yes
				</label>
				&nbsp;
				<label for="infinite-0">
					<input class="radio_toggle" type="radio" name="#infinite" id="infinite-0" value="false" <?php if ( $group['infinite'] == 'false' ) { echo ' checked '; } ?>/>
					No
				</label>
			</td>
		</tr>

		<tr>
			<td><label for="show_navigation">Display navigation<?php $this->tip( 'When enabled, arrows will appear on the Carousel (on hover or always depending on the next option), allowing the user to move forward or backward in the Carousel.' ); ?></label></td>
			<td>
				<label for="show_navigation-1">
					<input class="radio_toggle" type="radio" name="#show_navigation" id="show_navigation-1" value="true" <?php if ( $group['show_navigation'] == 'true' ) { echo ' checked '; } ?>/>
					Yes
				</label>
				&nbsp;
				<label for="show_navigation-0">
					<input class="radio_toggle" type="radio" name="#show_navigation" id="show_navigation-0" value="false" <?php if ( $group['show_navigation'] == 'false' ) { echo ' checked '; } ?>/>
					No
				</label>
			</td>
		</tr>
		<tr>
			<td><label for="show_pagination">Display pagination<?php $this->tip( 'When enabled, bullets will appear for easy navigation.' ); ?></label></td>
			<td>
				<label for="show_pagination-1">
					<input class="radio_toggle" type="radio" name="#show_pagination" id="show_pagination-1" value="true" <?php if ( $group['show_pagination'] == 'true' ) { echo ' checked '; } ?>/>
					Yes
				</label>
				&nbsp;
				<label for="show_pagination-0">
					<input class="radio_toggle" type="radio" name="#show_pagination" id="show_pagination-0" value="false" <?php if ( $group['show_pagination'] == 'false' ) { echo ' checked '; } ?>/>
					No
				</label>
			</td>
		</tr>
		

		
	</table>
	
	
	<p class="submit"><input type="submit" name="save" value="Save Settings" class="button-primary" id="save" /></p>
	<?php $this->nonce(); ?>
</form>
