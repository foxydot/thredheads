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

<h2><?php echo $this->_name; ?> Group Settings for "<?php echo $group['title']; ?>" (<a href="<?php echo $this->_parent->_selfLink; ?>-settings">group list</a>)</h2>

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
				<th style="text-align: center;">Reorder<?php $this->tip( 'This order determines the order images are displayed in slides. Click and drag to reorder.' ); ?></th>
			</tr>
		</thead>
		<tfoot>
			<tr class="thead">
				<th scope="col" class="check-column"><input type="checkbox" class="check-all-entries" /></th>
				<th>Image</th>
				<th>Title + Caption</th>
				<th>File Details</th>
				<th style="text-align: center;">Reorder<?php $this->tip( 'This order determines the order images are displayed in slides. Click and drag to reorder.' ); ?></th>
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
						?>
						<tr class="entry-row alternate" id="entry-<?php echo $id; ?>">
							<th scope="row" class="check-column"><input type="checkbox" name="items[]" class="entries" value="<?php echo $id; ?>" /></th>
							<td style="min-width: 160px;">
								<?php
								//$image_dat = wp_get_attachment_image_src( $attachment_data->ID, array( 50, 50 ) );
								$image_dat = wp_get_attachment_image_src( $attachment_data->ID );
								?>
								<img src="<?php echo $image_dat[0]; ?>" width="100" height="100" style="float: left; margin-right: 10px;" />
								<div class="row-actions" style="margin: 0; padding: 0;">
									<a href="<?php echo $this->_parent->_medialibrary->get_edit_link( $attachment_data->ID ); ?>" class="thickbox">Edit Image Settings</a>
								</div>
							</td>
							<td>
								<?php
								if ( !empty( $attachment_data->post_content ) && ( ( stristr( $attachment_data->post_content, 'http' ) ) || ( stristr( $attachment_data->post_content, 'http' ) ) ) ) {
									echo '<b><a href="' . $attachment_data->post_content . '">' . $attachment_data->post_title . '</a></b> <i>(link)</i>';
								} else {
									echo '<b>' . strip_tags( stripslashes( $attachment_data->post_title ) ) . '</b> <i>(no link)</i>';
								}
								echo '<br />';
								if ( !empty( $attachment_data->post_excerpt ) ) {
									echo strip_tags( stripslashes( $attachment_data->post_excerpt ) );
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
								<img src="<?php echo $this->_pluginURL; ?>/images/draghandle2.png" title="Click and drag to reorder" />
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
	<p><label for="enable_css_files">Enable CSS Files     <?php $this->tip( 'Warning! When disabled will prevent the css files used for image transitions to load.'); ?> </label>
	<label for="enable_css_files-1">
		<input class="radio_toggle" type="radio" name="#enable_css_files" id="enable_css_files-1" value="true" <?php if ( $group[ 'enable_css_files' ] == 'true' ) { echo 'checked'; } ?>/>yes</label>
		&nbsp;
	<label for="enable_css_file-0">
		<input class="radio_toggle" type="radio" name="#enable_css_files" id="enable_css_files-0" value="false" <?php if ( $group[ 'enable_css_files' ] == 'false' ) { echo 'checked'; } ?> />no</label>
		&nbsp;</p>


	<b>Slide Image Dimensions:</b>
	<label for="image_width"></label><input type="text" name="#image_width" id="image_width" size="5" maxlength="5" style="text-align: right;" value="<?php echo $group['image_width']; ?>" />px wide
	<label for="image_height"></label><input type="text" name="#image_height" id="image_height" size="5" maxlength="5" style="text-align: right;" value="<?php echo $group['image_height']; ?>" />px high. Images will be adjusted to this size.
	<?php $this->tip('This controls the size of the images in the slideshow. Images will be generated from the original images uploaded. Images will not be upscaled larger than the originals. You may change this at any time.'); ?>
	<br /><br />
	
	
	<input type="hidden" name="savepoint" value="groups#<?php echo $_GET['edit']; ?>" />
	
	<h2>Slideshow Mode</h2>
	
	<h3>
		<label for="type_slider">
			<input type="radio" name="#type" id="type_slider" value="slider" onclick="jQuery('#slider_settings').show(); jQuery('#cycle_settings').hide();" <?php if ( $group['type'] == 'slider' ) { echo ' checked '; } ?>/>
			Slider
		</label>
	</h3>
	Transitions for this mode focus on slicing the image up into many pieces and transitioning those pieces between images.
	
	<br />
	<h3>
		<label for="type_cycle">
			<input type="radio" name="#type" id="type_cycle" value="cycle" onclick="jQuery('#cycle_settings').show(); jQuery('#slider_settings').hide();" <?php if ( $group['type'] == 'cycle' ) { echo ' checked '; } ?>/>
			Cycle
		</label>
	</h3>
	Transitions for this mode focus on shuffling images around; for example images may push others out of the way, fly out and around, or zoom in from a corner.
	
	<br />
	
	<table class="form-table" id="slider_settings" <?php if ( $group['type'] != 'slider' ) { echo 'style="display: none;"'; } ?>>
		<tr><td><h2>Slider Mode Settings</h2></td><td style="min-width: 450px;"></td></tr>
		<tr>
			<td><label for="slider-effect">Animation transition effect <?php $this->tip( 'Controls the animation/effect that will occur to transition between different slides.' ); ?></label></td>
			<td>
				<select name="#slider-effect" id="slider-effect">
					<option value="sliceDown" <?php if ( 'sliceDown' == $group['slider-effect'] ) { echo 'selected'; } ?> />Slice Down</option>
					<option value="sliceDownLeft" <?php if ( 'sliceDownLeft' == $group['slider-effect'] ) { echo 'selected'; } ?> />Slice Down Left</option>
					<option value="sliceUp" <?php if ( 'sliceUp' == $group['slider-effect'] ) { echo 'selected'; } ?> />Slice Up</option>
					<option value="sliceUpLeft" <?php if ( 'UpLeft' == $group['slider-effect'] ) { echo 'selected'; } ?> />Slice Up Left</option>
					<option value="sliceUpDown" <?php if ( 'sliceUpDown' == $group['slider-effect'] ) { echo 'selected'; } ?> />Slice Up Down</option>
					<option value="sliceUpDownLeft" <?php if ( 'sliceUpDownLeft' == $group['slider-effect'] ) { echo 'selected'; } ?> />Slice Up Down Left</option>
					<option value="fold" <?php if ( 'fold' == $group['slider-effect'] ) { echo 'selected'; } ?> />Fold</option>
					<option value="fade" <?php if ( 'fade' == $group['slider-effect'] ) { echo 'selected'; } ?> />Fade</option>
					<option value="slideInRight" <?php if ( 'slideInRight' == $group['slider-effect'] ) { echo 'selected'; } ?> />Slide In Right</option>
					<option value="slideInLeft" <?php if ( 'slideInLeft' == $group['slider-effect'] ) { echo 'selected'; } ?> />Slide In Left</option>
					<option value="boxRandom" <?php if ( 'boxRandom' == $group['slider-effect'] ) { echo 'selected'; } ?> />Box Random</option>
					<option value="boxRain" <?php if ( 'boxRain' == $group['slider-effect'] ) { echo 'selected'; } ?> />Box Rain</option>
					<option value="boxRainReverse" <?php if ( 'boxRainReverse' == $group['slider-effect'] ) { echo 'selected'; } ?> />Box Rain Reverse</option>
					<option value="boxRainGrow" <?php if ( 'boxRainGrow' == $group['slider-effect'] ) { echo 'selected'; } ?> />Box Rain Grow</option>
					<option value="boxRainGrowReverse" <?php if ( 'boxRainGrowReverse' == $group['slider-effect'] ) { echo 'selected'; } ?> />Box Rain Grow Reverse</option>
					<option value="random" <?php if ( 'random' == $group['slider-effect'] ) { echo 'selected'; } ?> />Random (default)</option>
				</select>
			</td>
		</tr>
		<tr>
			<td><label for="slider-align">Horizontal Alignment <?php $this->tip( 'Controls the horizontal alignment of the Slideshow in its container.' ); ?></label></td>
			<td>
				<select name="#slider-align" id="slider-align">
					<option value="left" <?php if ( 'left' == $group['slider-align'] ) { echo 'selected'; } ?> />Left</option>
					<option value="center" <?php if ( 'center' == $group['slider-align'] ) { echo 'selected'; } ?> />Center</option>
					<option value="right" <?php if ( 'right' == $group['slider-align'] ) { echo 'selected'; } ?> />Right</option>
				</select>
			</td>
		</tr>
		<tr>
			<td><label for="slider-directionNav">Enable directional navigation on images<?php $this->tip( 'When enabled, arrows will appear on the slideshow (on hover or always depending on the next option), allowing the user to move forward or backward in the slideshow.' ); ?></label></td>
			<td>
				<label for="slider-directionNav-1">
					<input class="radio_toggle" type="radio" name="#slider-directionNav" id="slider-directionNav-1" value="true" onclick="jQuery('#pb_slider_navon').show();" <?php if ( $group['slider-directionNav'] == 'true' ) { echo ' checked '; } ?>/>
					Yes
				</label>
				&nbsp;
				<label for="slider-directionNav-0">
					<input class="radio_toggle" type="radio" name="#slider-directionNav" id="slider-directionNav-0" value="false" onclick="jQuery('#pb_slider_navon').hide();" <?php if ( $group['slider-directionNav'] == 'false' ) { echo ' checked '; } ?>/>
					No
				</label>
			</td>
		</tr>
		<tr id="pb_slider_navon" <?php if ( $group['slider-directionNav'] == 'false' ) { echo ' style="display: none;" '; } ?>>
			<td>&nbsp;</td>
			<td>
				<label for="slider-directionNavHide-1">
					<input class="radio_toggle" type="radio" name="#slider-directionNavHide" id="slider-directionNavHide-1" value="true" <?php if ( $group['slider-directionNavHide'] == 'true' ) { echo ' checked '; } ?>/>
					Hover
				</label>
				&nbsp;
				<label for="slider-directionNavHide-0">
					<input class="radio_toggle" type="radio" name="#slider-directionNavHide" id="slider-directionNavHide-0" value="false" <?php if ( $group['slider-directionNavHide'] == 'false' ) { echo ' checked '; } ?>/>
					Always
				</label>
			</td>
		</tr>
		<tr>
			<td><label for="slider-controlNav">Show navigation below slideshow<?php $this->tip( 'When enabled, bullets or thumbnails depending on the next option will be displayed below the slideshow for instant naviation to any slide. ' ); ?></label></td>
			<td>
				<label for="slider-controlNav-1">
					<input class="radio_toggle" type="radio" name="#slider-controlNav" id="slider-controlNav-1" value="true" onclick="jQuery('#pb_slider_navstyle').show();" <?php if ( $group['slider-controlNav'] == 'true' ) { echo ' checked '; } ?>/>
					Yes
				</label>
				&nbsp;
				<label for="slider-controlNav-0">
					<input class="radio_toggle" type="radio" name="#slider-controlNav" id="slider-controlNav-0" value="false" onclick="jQuery('#pb_slider_navstyle').hide();" <?php if ( $group['slider-controlNav'] == 'false' ) { echo ' checked '; } ?>/>
					No
				</label>
			</td>
		</tr>
		<tr id="pb_slider_navstyle" <?php if ( $group['slider-controlNav'] == 'false' ) { echo ' style="display: none;" '; } ?>>
			<td><label for="slider-controlNavThumbs">Navigation style below slideshow<?php $this->tip( 'Determines whether bullets (dots) or smaller thumbnails of each respective slide is shown below the slideshow.' ); ?></label></td>
			<td>
				<select name="#slider-controlNavThumbs" id="slider-controlNavThumbs" onchange="
					if ( jQuery(this).val() == 'true' ) {
						jQuery('#pb_slider_thumbsizes').show();
					} else {
						jQuery('#pb_slider_thumbsizes').hide();
					}
				">
					<option value="true" <?php if ( 'true' == $group['slider-controlNavThumbs'] ) { echo 'selected'; } ?> />Thumbnails</option>
					<option value="false" <?php if ( 'false' == $group['slider-controlNavThumbs'] ) { echo 'selected'; } ?> />Bullets</option>
				</select>
				<span id="pb_slider_thumbsizes" <?php if ( $group['slider-controlNavThumbs'] == 'false' ) { echo ' style="display: none;" '; } ?>>
					Dimensions: <input type="text" name="#thumb_image_width" id="thumb_image_width" size="5" maxlength="5" value="<?php echo $group['thumb_image_width']; ?>" style="text-align: right;" />px wide
					<input type="text" name="#thumb_image_height" id="thumb_image_height" size="5" maxlength="5" value="<?php echo $group['thumb_image_height']; ?>" style="text-align: right;" />px high
				</span>
			</td>
		</tr>
		<tr>
			<td><label for="slider-keyboardNav">Enable Keyboard Navigation<?php $this->tip( 'When enabled users may use their left and right arrow keys on their keyboard to navigate between slides.' ); ?></label></td>
			<td>
				<label for="slider-keyboardNav-1">
					<input class="radio_toggle" type="radio" name="#slider-keyboardNav" id="slider-keyboardNav-1" value="true" <?php if ( $group['slider-keyboardNav'] == 'true' ) { echo ' checked '; } ?>/>
					Yes
				</label>
				&nbsp;
				<label for="slider-keyboardNav-0">
					<input class="radio_toggle" type="radio" name="#slider-keyboardNav" id="slider-keyboardNav-0" value="false" <?php if ( $group['slider-keyboardNav'] == 'false' ) { echo ' checked '; } ?>/>
					No
				</label>
			</td>
		</tr>
		<tr>
			<td><label for="slider-pauseOnHover">Pause on mouse hover<?php $this->tip( 'When enabled moving the mouse cursor over the slideshow will cause it to pause.' ); ?></label></td>
			<td>
				<label for="slider-pauseOnHover-1">
					<input class="radio_toggle" type="radio" name="#slider-pauseOnHover" id="slider-pauseOnHover-1" value="true" <?php if ( $group['slider-pauseOnHover'] == 'true' ) { echo ' checked '; } ?>/>
					Yes
				</label>
				&nbsp;
				<label for="slider-pauseOnHover-0">
					<input class="radio_toggle" type="radio" name="#slider-pauseOnHover" id="slider-pauseOnHover-0" value="false" <?php if ( $group['slider-pauseOnHover'] == 'false' ) { echo ' checked '; } ?>/>
					No
				</label>
			</td>
		</tr>
		<tr>
			<td><label for="slider-shadows">Shadows around slideshow<?php $this->tip( 'When enabled a subtle shadow will be displayed around the slideshow and thumbnails (if enabled).' ); ?></label></td>
			<td>
				<label for="slider-shadows-1">
					<input class="radio_toggle" type="radio" name="#slider-shadows" id="slider-shadows-1" value="true" <?php if ( $group['slider-shadows'] == 'true' ) { echo ' checked '; } ?>/>
					Yes
				</label>
				&nbsp;
				<label for="slider-shadows-0">
					<input class="radio_toggle" type="radio" name="#slider-shadows" id="slider-shadows-0" value="false" <?php if ( $group['slider-shadows'] == 'false' ) { echo ' checked '; } ?>/>
					No
				</label>
			</td>
		</tr>
		<tr>
			<td><label for="slider-captionOpacity">Caption opacity<?php $this->tip( 'Controls the opacity (translucency) the captions are. Higher numbers mean more `solid`.' ); ?></label></td>
			<td>
				<select name="#slider-captionOpacity" id="slider-captionOpacity">
					<option value="0.1" <?php if ( '0.1' == $group['slider-captionOpacity'] ) { echo 'selected'; } ?> />10%</option>
					<option value="0.2" <?php if ( '0.2' == $group['slider-captionOpacity'] ) { echo 'selected'; } ?> />20%</option>
					<option value="0.3" <?php if ( '0.3' == $group['slider-captionOpacity'] ) { echo 'selected'; } ?> />30%</option>
					<option value="0.4" <?php if ( '0.4' == $group['slider-captionOpacity'] ) { echo 'selected'; } ?> />40%</option>
					<option value="0.5" <?php if ( '0.5' == $group['slider-captionOpacity'] ) { echo 'selected'; } ?> />50%</option>
					<option value="0.6" <?php if ( '0.6' == $group['slider-captionOpacity'] ) { echo 'selected'; } ?> />60%</option>
					<option value="0.7" <?php if ( '0.7' == $group['slider-captionOpacity'] ) { echo 'selected'; } ?> />70%</option>
					<option value="0.8" <?php if ( '0.8' == $group['slider-captionOpacity'] ) { echo 'selected'; } ?> />80%</option>
					<option value="0.9" <?php if ( '0.9' == $group['slider-captionOpacity'] ) { echo 'selected'; } ?> />90%</option>
					<option value="1.0" <?php if ( '1.0' == $group['slider-captionOpacity'] ) { echo 'selected'; } ?> />100%</option>
				</select>
			</td>
		</tr>
		<tr>
			<td><label for="slider-slices">Slices<?php $this->tip( 'Number of slices that slides will be cut up into for transition effects. Note that more slices require more processing power and may slow down slower computers.' ); ?></label></td>
			<td><input type="text" name="#slider-slices" id="slider-slices" size="5" maxlength="5" value="<?php echo $group['slider-slices']; ?>" style="text-align: right;" />slices</td>
		</tr>
		<tr>
			<td><label for="slider-animSpeed">Animation speed<?php $this->tip( 'Speed of animation during transitions (in milliseconds).' ); ?></label></td>
			<td><input type="text" name="#slider-animSpeed" id="slider-animSpeed" size="5" maxlength="5" value="<?php echo $group['slider-animSpeed']; ?>" style="text-align: right;" />ms</td>
		</tr>
		<tr>
			<td><label for="slider-pauseTime">Pause time between slide changes<?php $this->tip( 'Amount of time to display a slide before transitioning to the next slide (in milliseconds; ex: 4000 = 4 seconds).' ); ?></label></td>
			<td><input type="text" name="#slider-pauseTime" id="slider-pauseTime" size="5" maxlength="5" value="<?php echo $group['slider-pauseTime']; ?>" style="text-align: right;" />ms</td>
		</tr>
	</table>
	
	
	<table class="form-table" id="cycle_settings" <?php if ( $group['type'] != 'cycle' ) { echo 'style="display: none;"'; } ?>>
		<tr><td colspan="2"><h2>Cycle Mode Settings</h2></td></tr>
		<tr>
			<td><label for="cycle-fx">Animation transition effect(s)<?php $this->tip( 'Controls the animation/effect that will occur to transition between different slides. When a `Combo` is selected, all effects of that type will be alternated. The next option allows randomizing this order.' ); ?></label></td>
			<td>
				<select name="#cycle-fx" id="cycle-fx">
					<option value="blindX,blindY,blindZ" <?php if ( 'blindX,blindY,blindZ' == $group['cycle-fx'] ) { echo 'selected'; } ?> />Combo: All blind effects</option>
					<option value="curtainX,curtainY" <?php if ( 'curtainX,curtainY' == $group['cycle-fx'] ) { echo 'selected'; } ?> />Combo: All curtain effects</option>
					<option value="scrollUp,scrollDown,scrollLeft,scrollRight,scrollHorz,scrollVert" <?php if ( 'scrollUp,scrollDown,scrollLeft,scrollRight,scrollHorz,scrollVert' == $group['cycle-fx'] ) { echo 'selected'; } ?> />Combo: All scroll effects</option>
					<option value="turnUp,turnDown,turnLeft,turnRight" <?php if ( 'turnUp,turnDown,turnLeft,turnRight' == $group['cycle-fx'] ) { echo 'selected'; } ?> />Combo: All turn effects</option>
					<option value="blindX" <?php if ( 'blindX' == $group['cycle-fx'] ) { echo 'selected'; } ?> />blindX</option>
					<option value="blindY" <?php if ( 'blindY' == $group['cycle-fx'] ) { echo 'selected'; } ?> />blindY</option>
					<option value="blindZ" <?php if ( 'blindZ' == $group['cycle-fx'] ) { echo 'selected'; } ?> />blindZ</option>
					<option value="cover" <?php if ( 'cover' == $group['cycle-fx'] ) { echo 'selected'; } ?> />cover</option>
					<option value="curtainX" <?php if ( 'curtainX' == $group['cycle-fx'] ) { echo 'selected'; } ?> />curtainX</option>
					<option value="curtainY" <?php if ( 'curtainY' == $group['cycle-fx'] ) { echo 'selected'; } ?> />curtainY</option>
					<option value="fade" <?php if ( 'fade' == $group['cycle-fx'] ) { echo 'selected'; } ?> />fade</option>
					<option value="fadeZoom" <?php if ( 'fadeZoom' == $group['cycle-fx'] ) { echo 'selected'; } ?> />fadeZoom</option>
					<option value="growX,growY" <?php if ( 'growX,growY' == $group['cycle-fx'] ) { echo 'selected'; } ?> />growX,growY</option>
					<option value="growX" <?php if ( 'growX' == $group['cycle-fx'] ) { echo 'selected'; } ?> />growX</option>
					<option value="growY" <?php if ( 'growY' == $group['cycle-fx'] ) { echo 'selected'; } ?> />growY</option>
					<option value="none" <?php if ( 'none' == $group['cycle-fx'] ) { echo 'selected'; } ?> />none</option>
					<option value="scrollUp" <?php if ( 'scrollUp' == $group['cycle-fx'] ) { echo 'selected'; } ?> />scrollUp</option>
					<option value="scrollDown" <?php if ( 'scrollDown' == $group['cycle-fx'] ) { echo 'selected'; } ?> />scrollDown</option>
					<option value="scrollLeft" <?php if ( 'scrollLeft' == $group['cycle-fx'] ) { echo 'selected'; } ?> />scrollLeft</option>
					<option value="scrollRight" <?php if ( 'scrollRight' == $group['cycle-fx'] ) { echo 'selected'; } ?> />scrollRight</option>
					<option value="scrollHorz" <?php if ( 'scrollHorz' == $group['cycle-fx'] ) { echo 'selected'; } ?> />scrollHorz</option>
					<option value="scrollVert" <?php if ( 'scrollVert' == $group['cycle-fx'] ) { echo 'selected'; } ?> />scrollVert</option>
					<option value="shuffle" <?php if ( 'shuffle' == $group['cycle-fx'] ) { echo 'selected'; } ?> />shuffle</option>
					<option value="slideX,slideY" <?php if ( 'slideX,slideY' == $group['cycle-fx'] ) { echo 'selected'; } ?> />slideX,slideY</option>
					<option value="slideX" <?php if ( 'slideX' == $group['cycle-fx'] ) { echo 'selected'; } ?> />slideX</option>
					<option value="slideY" <?php if ( 'slideY' == $group['cycle-fx'] ) { echo 'selected'; } ?> />slideY</option>
					<option value="toss" <?php if ( 'toss' == $group['cycle-fx'] ) { echo 'selected'; } ?> />toss</option>
					<option value="turnUp" <?php if ( 'turnUp' == $group['cycle-fx'] ) { echo 'selected'; } ?> />turnUp</option>
					<option value="turnDown" <?php if ( 'turnDown' == $group['cycle-fx'] ) { echo 'selected'; } ?> />turnDown</option>
					<option value="turnLeft" <?php if ( 'turnLeft' == $group['cycle-fx'] ) { echo 'selected'; } ?> />turnLeft</option>
					<option value="turnRight" <?php if ( 'turnRight' == $group['cycle-fx'] ) { echo 'selected'; } ?> />turnRight</option>
					<option value="uncover" <?php if ( 'uncover' == $group['cycle-fx'] ) { echo 'selected'; } ?> />uncover</option>
					<option value="wipe" <?php if ( 'wipe' == $group['cycle-fx'] ) { echo 'selected'; } ?> />wipe</option>
					<option value="zoom" <?php if ( 'zoom' == $group['cycle-fx'] ) { echo 'selected'; } ?> />zoom</option>
				</select>
			</td>
		</tr>
		<tr>
			<td><label for="cycle-align">Horizontal Alignment <?php $this->tip( 'Controls the horizontal alignment of the Slideshow in its container.' ); ?></label></td>
			<td>
				<select name="#cycle-align" id="cycle-align">
					<option value="left" <?php if ( 'left' == $group['cycle-align'] ) { echo 'selected'; } ?> />Left</option>
					<option value="center" <?php if ( 'center' == $group['cycle-align'] ) { echo 'selected'; } ?> />Center</option>
					<option value="right" <?php if ( 'right' == $group['cycle-align'] ) { echo 'selected'; } ?> />Right</option>
				</select>
			</td>
		</tr>
		<tr>
			<td><label for="cycle-randomizeEffects">Randomize combo effects<?php $this->tip( 'When a `Combo` effect is selected in the option above, the effects will be randomly applied to each transition.' ); ?></label></td>
			<td>
				<label for="cycle-randomizeEffects-1">
					<input class="radio_toggle" type="radio" name="#cycle-randomizeEffects" id="cycle-randomizeEffects-1" value="1" <?php if ( $group['cycle-randomizeEffects'] == '1' ) { echo ' checked '; } ?>/>
					Yes
				</label>
				&nbsp;
				<label for="cycle-randomizeEffects-0">
					<input class="radio_toggle" type="radio" name="#cycle-randomizeEffects" id="cycle-randomizeEffects-0" value="0" <?php if ( $group['cycle-randomizeEffects'] == '0' ) { echo ' checked '; } ?>/>
					No
				</label>
			</td>
		</tr>
		<tr>
			<td><label for="cycle-pb_pager">Display numeric navigation below Cycler<?php $this->tip( 'When enabled numbered bullets will be displayed below the slideshow allowing users to select which slide to view.' ); ?></label></td>
			<td>
				<label for="cycle-pb_pager-1">
					<input class="radio_toggle" type="radio" name="#cycle-pb_pager" id="cycle-pb_pager-1" value="1" <?php if ( $group['cycle-pb_pager'] == '1' ) { echo ' checked '; } ?>/>
					Yes
				</label>
				&nbsp;
				<label for="cycle-pb_pager-0">
					<input class="radio_toggle" type="radio" name="#cycle-pb_pager" id="cycle-pb_pager-0" value="0" <?php if ( $group['cycle-pb_pager'] == '0' ) { echo ' checked '; } ?>/>
					No
				</label>
			</td>
		</tr>
		<tr>
			<td><label for="cycle-sync">Sync in/out transitions to occur simultaneously (changes look of effect)<?php $this->tip( 'When enabled, the animation for the slide that is entering view will be displayed at the same time as the slide leaving view. Changing this option can drastically change the look of the selected transition effect. Experiment!' ); ?></label></td>
			<td>
				<label for="cycle-sync-1">
					<input class="radio_toggle" type="radio" name="#cycle-sync" id="cycle-sync-1" value="1" <?php if ( $group['cycle-sync'] == '1' ) { echo ' checked '; } ?>/>
					Yes
				</label>
				&nbsp;
				<label for="cycle-sync-0">
					<input class="radio_toggle" type="radio" name="#cycle-sync" id="cycle-sync-0" value="0" <?php if ( $group['cycle-sync'] == '0' ) { echo ' checked '; } ?>/>
					No
				</label>
			</td>
		</tr>
		<tr>
			<td><label for="cycle-random">Randomize slide sequence<?php $this->tip( 'When enabled slides will be displayed in a random order.' ); ?></label></td>
			<td>
				<label for="cycle-random-1">
					<input class="radio_toggle" type="radio" name="#cycle-random" id="cycle-random-1" value="1" <?php if ( $group['cycle-random'] == '1' ) { echo ' checked '; } ?>/>
					Yes
				</label>
				&nbsp;
				<label for="cycle-random-0">
					<input class="radio_toggle" type="radio" name="#cycle-random" id="cycle-random-0" value="0" <?php if ( $group['cycle-random'] == '0' ) { echo ' checked '; } ?>/>
					No
				</label>
			</td>
		</tr>
		<tr>
			<td><label for="cycle-pause">Pause on mouse hover<?php $this->tip( 'When enabled moving the mouse cursor over the slideshow will cause it to pause.' ); ?></label></td>
			<td>
				<label for="cycle-pause-1">
					<input class="radio_toggle" type="radio" name="#cycle-pause" id="cycle-pause-1" value="1" <?php if ( $group['cycle-pause'] == '1' ) { echo ' checked '; } ?>/>
					Yes
				</label>
				&nbsp;
				<label for="cycle-pause-0">
					<input class="radio_toggle" type="radio" name="#cycle-pause" id="cycle-pause-0" value="0" <?php if ( $group['cycle-pause'] == '0' ) { echo ' checked '; } ?>/>
					No
				</label>
			</td>
		</tr>
		<tr>
			<td><label for="cycle-autostop">Automatically stop after showing certain number of slides<?php $this->tip( 'When enabled the slideshow will automatically stop after displaying a number of slides you define.' ); ?></label></td>
			<td>
				<label for="cycle-autostop-1">
					<input class="radio_toggle" type="radio" name="#cycle-autostop" id="cycle-autostop-1" value="1" onclick="jQuery('#pb_cycle_autostop').show();" <?php if ( $group['cycle-autostop'] == '1' ) { echo ' checked '; } ?>/>
					Yes
				</label>
				&nbsp;
				<label for="cycle-autostop-0">
					<input class="radio_toggle" type="radio" name="#cycle-autostop" id="cycle-autostop-0" value="0" onclick="jQuery('#pb_cycle_autostop').hide();" <?php if ( $group['cycle-autostop'] == '0' ) { echo ' checked '; } ?>/>
					No
				</label>
			</td>
		</tr>
		<tr id="pb_cycle_autostop" <?php if ( $group['cycle-autostop'] != '1' ) { echo 'style="display: none;"'; } ?>>
			<td>&nbsp;</td>
			<td><input type="text" name="#cycle-autostopCount" id="cycle-autostopCount" size="5" maxlength="5" value="<?php echo $group['cycle-autostopCount']; ?>" style="text-align: right;" />slides</td>
		</tr>
		<tr>
			<td><label for="cycle-delay">Delay before first slide change occurs<?php $this->tip( 'Time to hold the first slide on the screen before transitioning to the next (in milliseconds; ex: 4000ms = 4seconds).' ); ?></label></td>
			<td><input type="text" name="#cycle-delay" id="cycle-delay" size="5" maxlength="5" value="<?php echo $group['cycle-delay']; ?>" style="text-align: right;" />ms</td>
		</tr>
		<tr>
			<td><label for="cycle-timeout">Time between slide changes (0=no auto advance)<?php $this->tip( 'Time to display each slide before transitioning to the next slide (in milliseconds; ex: 4000ms = 4seconds).' ); ?></label></td>
			<td><input type="text" name="#cycle-timeout" id="cycle-timeout" size="5" maxlength="5" value="<?php echo $group['cycle-timeout']; ?>" style="text-align: right;" />ms</td>
		</tr>
		<tr>
			<td><label for="cycle-speed">Transition animation speed<?php $this->tip( 'Speed of the animation transition (in milliseconds; ex: 4000ms = 4seconds).' ); ?></label></td>
			<td><input type="text" name="#cycle-speed" id="cycle-speed" size="5" maxlength="5" value="<?php echo $group['cycle-speed']; ?>" style="text-align: right;" />ms</td>
		</tr>
		<tr>
			<td><label for="cycle-speedIn">IN Transition speed override<?php $this->tip( 'Override the `Transition animation speed` option for slides coming IN to view (in milliseconds; ex: 4000ms = 4seconds).' ); ?></label></td>
			<td><input type="text" name="#cycle-speedIn" id="cycle-speedIn" size="5" maxlength="5" value="<?php echo $group['cycle-speedIn']; ?>" style="text-align: right;" />ms</td>
		</tr>
		<tr>
			<td><label for="cycle-speedOut">OUT Transition speed override<?php $this->tip( 'Override the `Transition animation speed` option for slides coming OUT of view (in milliseconds; ex: 4000ms = 4seconds).' ); ?></label></td>
			<td><input type="text" name="#cycle-speedOut" id="cycle-speedOut" size="5" maxlength="5" value="<?php echo $group['cycle-speedOut']; ?>" style="text-align: right;" />ms</td>
		</tr>
	</table>
	
	<p class="submit"><input type="submit" name="save" value="Save Settings" class="button-primary" id="save" /></p>
	<?php $this->nonce(); ?>
</form>
