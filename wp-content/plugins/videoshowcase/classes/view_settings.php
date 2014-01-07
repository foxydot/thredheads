<?php
if ( !empty( $_POST['save'] ) ) {
	$this->savesettings();
}
$this->_parent->load();
$this->admin_scripts();

// SUBMIT ADD GROUP
if ( !empty( $_POST[$this->_var . '-group-save'] ) ) {
	$this->_createGroup();
}

// SUBMIT EDIT GROUP
if ( !empty( $_POST[$this->_var . '-group_set'] ) ) {
	$this->_editGroup();
}

// SUBMIT DELETE GROUPS
if ( !empty( $_POST[$this->_var . '-delete_groups'] ) ) {
	$this->_deleteGroups();
}

// SUBMIT ADD VIDEO
if ( !empty( $_POST[$this->_var . '-add_video'] ) ) {
	$this->_addVideo();
}

// SUBMIT ADD CUSTOM VIDEO
if ( !empty( $_POST[$this->_var . '-add_cvideo'] ) ) {
	$this->_addcustVideo();
}

// SUBMIT EDIT VIDEO
if ( !empty( $_POST[$this->_var . '-edit_video'] ) ) {
	$this->_editVideo();
}

// SUBMIT DELETE VIDEO
if ( !empty( $_POST[$this->_var . '-delete_videos'] ) ) {
	$this->_deleteVideos();
}

// SUBMIT ORDER VIDEO
if ( !empty( $_POST['save_order'] ) ) {
	$this->_saveOrder();
}

?>
<div class="wrap">
	<?php
	// EDITING A VIDEO
	if ( isset( $_GET['video_id'] ) ) {

		$video = $this->_options['groups'][$_GET['group_id']]['videos'][$_GET['video_id']];
		echo '<h2>Editing &quot;' . stripslashes($video['vtitle']) . '&quot;</h2>';
		echo '<h4>';
			echo '<a href="' . $this->_selfLink . '-settings">Group list</a> -> ';
			echo '<a href="' . $this->_selfLink . '-settings&group_id=' . $_GET['group_id'] . '">Inside group</a> -> ';
			echo '<a href="' . $this->_selfLink . '-settings&group_id=' . $_GET['group_id'] . '&video_id=' . $_GET['video_id'] . '">Editing video</a>';
		echo '</h4>';
		
		echo '<form method="post" action="' . $this->_selfLink . '-settings&group_id=' . $_GET['group_id'] . '">';
		echo '<table class="form-table">';
			echo '<tr>';
			?>
				<th><label for="video_title">Video Title <?php $this->_parent->tip('Insert the title you want for the video.'); ?></label></th>
			<?php
				echo '<td><input type="text" name="' . $this->_var . '-title" value="' . stripslashes($video['vtitle']) . '" size="45" id="video_title" /></td>';
			echo '</tr>';
			echo '<tr>';
				echo '<th><label for="video_img">Current Image</label></th>';
				$imagedata = wp_get_attachment_image_src( $video['vimage'], 'thumbnail' );
				echo '<td><img id="current_image" src="' . $imagedata['0'] . '" alt="' . $video['vtitle'] . '" /></td>';
			echo '</tr>';
			echo '<tr>';
				echo '<th><strong>Optional</strong></th>';
			echo '</tr>';
			echo '<tr>';
			?>
				<th><label for="custom_img">Select or upload your own image <?php $this->_parent->tip('Upload your own image from your media library or computer.'); ?></label></th>
			<?php
				$imagelink = $this->_parent->_medialibrary->get_link();
				echo '<td><a href="' . $imagelink . '" class="button button-secondary thickbox">Custom Image</a></td>';
				echo '<input type="hidden" name="attachment_data" id="pb_attachment_data" value="" />';
				?>
				<script type="text/javascript">
					function pb_medialibrary( $response ) {
						jQuery('#pb_attachment_data').val( $response );
						jQuery.post( ajaxurl, { action : 'handle_attachment', 'image' : $response }, function(results){
							if ( results ){
								jQuery( '#current_image' ).attr( 'src', results );
							}
						} );
						
					}
				</script>
				<?php
			echo '</tr>';
			echo '<input type="hidden" name="' . $this->_var . '-groupid" value="' . $_GET['group_id'] . '" />';
			echo '<input type="hidden" name="' . $this->_var . '-videoid" value="' . $_GET['video_id'] . '" />';
		echo '</table>';
		echo '<p class="submit"><input type="submit" name="' . $this->_var . '-edit_video" value="Update Video" class="button-primary" /></p>';
		
		wp_nonce_field( $this->_var . '-nonce' );
		echo '</form>';
		
	}
	// ADDING VIDEOS & EDITING GROUP SETTINGS
	elseif ( isset( $_GET['group_id'] ) ) {
		// group database path
		$group = $this->_options['groups'][$_GET['group_id']];
		
		echo '<h2>' . $this->_name . ' Inside &quot;' . stripslashes($group['name']) . '&quot; </h2>';
		echo '<h4>';
			echo '<a href="' . $this->_selfLink . '-settings">Group list</a> -> ';
			echo '<a href="' . $this->_selfLink . '-settings&group_id=' . $_GET['group_id'] . '">Inside group</a>';
		echo '</h4>';
		
		$videonum = count($this->_options['groups'][$_GET['group_id']]['videos']);
				
		if ( $videonum >= 1 ) {
			// Videos Table
			?>
			<!-- ORDER ROWS JAVASCRIPT -->
			<script type="text/javascript">
				jQuery(document).ready(function() {
					jQuery('#reorder-table').tableDnD({
						onDrop: function(tbody, row) {
							setValue(jQuery.tableDnD.serialize());
						},
						dragHandle: "dragHandle"
					});
				});

				var orderValue;

				function setValue($test) {
				    orderValue = $test;
				}

				function getValue() {
					jQuery( '#hidnorder' ).val( window.orderValue );
				}
			</script>
			
			<form method="post" action="<?php echo $this->_selfLink . '-settings&group_id=' . $_GET['group_id']; ?>">
				<div class="tablenav">
					<div class="alignleft actions">
						<input type="submit" name="<?php echo $this->_var; ?>-delete_videos" value="Delete" class="button-secondary delete" />
					</div>
					<div class="alignright actions">
						<input type="submit" onclick="getValue();" name="save_order" value="Save order" class="button-secondary" />
					</div>
					<br class="clear" />
				</div>
				<br class="clear" />
				
				<table class="widefat">
					<thead>
						<tr>
							<th scope="col" class="check-column"><input type="checkbox" id="check-all-groups" /></th>
							<th>Thumb</th>
							<th></th>
							<th>URL</th>
							<th class="num"><label>Reorder <?php $this->_parent->tip('Click and drag the double sided arrow up or down to reorder the videos.'); ?></lable></th>
						</tr>
					</thead>
					<tbody id="reorder-table">
						<?php
						$url = $this->_selfLink . '-settings';
						$order = $this->_options['groups'][$_GET['group_id']]['order'];


						foreach ($order as $ordnum) {
							$video = $this->_options['groups'][$_GET['group_id']]['videos'][$ordnum];
							echo '<tr id="'. $ordnum . '">';
							echo '<th scope="col" class="check-column"><input type="checkbox" name="' . $this->_var . '-videos[]" class="administrator groups" value="' . $ordnum . '" /></th>';
							echo '<td class="vidimg">';
								echo '<a href="' . $url . '&group_id=' . $_GET['group_id'] . '&video_id=' . $ordnum . '">';
									echo stripslashes($video['vtitle']);
									echo '<br/>';
									$imagedata = wp_get_attachment_image_src( $video['vimage'], 'default_thumb' );
									echo '<img src="' . $imagedata['0'] . '" alt="' . $video['vtitle'] . '" />';
								echo '</a>';
							echo '</td>';
							echo '<td>';
								echo '<a href="' . $url . '&group_id=' . $_GET['group_id'] . '&video_id=' . $ordnum . '">(Edit Video)</a>';
							echo '</td>';
							echo '<td>';
								echo '<a href="' . $video['vurl'] . '" target="_blank">' . $video['vurl'] . '</a>';
							echo '</td>';
							?>
							<td class="dragHandle">
								<img src="<?php echo $this->_pluginURL; ?>/images/draghandle2.png" alt="Click and drag to reorder" />
							</td>
							<?php
							echo '</tr>';
						}
						
						?>
						<input type="hidden" name="<?php echo $this->_var; ?>-groupid" value="<?php echo $_GET['group_id']; ?>" />
					</tbody>

					<tfoot>
						<tr>
							<th scope="col" class="check-column"><input type="checkbox" id="check-all-groups" /></th>
							<th>Thumb</th>
							<th></th>
							<th>URL</th>
							<th class="num"><label>Reorder <?php $this->_parent->tip('Click and drag the double sided arrow up or down to reorder the videos.'); ?></label></th>
						</tr>
					</tfoot>
				</table>
				
				<div class="tablenav">
					<div class="alignleft actions">
						<input type="submit" name="<?php echo $this->_var; ?>-delete_videos" value="Delete" class="button-secondary delete" />
					</div>
					<div class="alignright actions">
						<input type="hidden" id="hidnorder" name="hidnorder" value="" />
						<input type="submit" onclick="getValue();" name="save_order" value="Save order" class="button-secondary" />
					</div>
					<br class="clear" />
				</div>
				
				<br class="clear" />
			<?php wp_nonce_field( $this->_var . '-nonce' ); ?>
			</form>
			<?php
		}
		?>

		<!-- ADD VIDEO YouTube or Vimeo-->
		<h2 id="addnew">Add YouTube or Vimeo Video</h2>
		<p>Find videos at <a href="http://www.youtube.com/" target="_blank">Youtube</a> and 
		<a href="http://vimeo.com/" target="_blank">Vimeo</a></p>
		<form method="post" action="<?php echo $this->_selfLink . '-settings&group_id=' . $_GET['group_id']; ?>">
			<table class="form-table">
				<tr>
					<th><label for="YVvideo_title">Video Title <?php $this->_parent->tip('Insert the title you want for the video.'); ?></label></th>
					<td><input type="text" name="<?php echo $this->_var; ?>-title" value="" size="45" id="YVvideo_title" /></td>
				</tr>
				<tr>
					<th><label for="YVvideo_url">Video Page URL <?php $this->_parent->tip("Insert the url from video's source site here. (ex. http://www.youtube.com/watch?v=6fmXUVdo28U)"); ?></label></th>
					<td><input type="text" name="<?php echo $this->_var; ?>-url" value="" size="45" id="YVvideo_url" /><br /><span style="color: #AFAFAF;">Ex: http://www.youtube.com/watch?v=6fmXUVdo28U</span></td>
				</tr>
				<input type="hidden" name="<?php echo $this->_var; ?>-groupid" value="<?php echo $_GET['group_id']; ?>" />
			</table>
			<p class="submit"><input type="submit" name="<?php echo $this->_var; ?>-add_video" value="+ Add Video" class="button-primary" /></p>
			<?php wp_nonce_field( $this->_var . '-nonce' ); ?>
		</form>
		
		<!-- ADD Stand alone VIDEO -->
		<h2 id="addnew">Add Stand Alone Video</h2>
		<p>Use this form to add stand alone videos by linking directly to the hosted video file.
		<br/>The supported video formats are mp4, flv, and mov.</p>
		<form method="post" action="<?php echo $this->_selfLink . '-settings&group_id=' . $_GET['group_id']; ?>">
			<table class="form-table">
				<tr>
					<th><label for="SAvideo_title">Video Title <?php $this->_parent->tip('Insert the title you want for the video.'); ?></label></th>
					<td><input type="text" name="<?php echo $this->_var; ?>-cvtitle" value="" size="45" id="SAvideo_title" /></td>
				</tr>
				<tr>
					<th><label for="SAvideo_url">Video File URL <?php $this->_parent->tip("Insert the path to the video here. (ex. http://mysite/videos/anyvideo.mp4)"); ?></label></th>
					<td><input type="text" name="<?php echo $this->_var; ?>-cvurl" value="" size="45" id="SAvideo_url" /><br /><span style="color: #AFAFAF;">Ex: http://mysite/videos/anyvideo.mp4</span></td>
				</tr>
				</tr>
					<th><label for="custom_img">Select or upload an image <?php $this->_parent->tip('Upload your own image from your media library or computer.'); ?></label></th>
					<?php
					$imagelink = $this->_parent->_medialibrary->get_link();
					echo '<td><a href="' . $imagelink . '" class="button button-secondary thickbox">Select Image</a></td>';
					echo '<input type="hidden" name="attachment_data" id="pb_attachment_data" value="" />';
					?>
					<script type="text/javascript">
						function pb_medialibrary( $response ) {
							jQuery('#pb_attachment_data').val( $response );
						}
					</script>
				</tr>
				<input type="hidden" name="<?php echo $this->_var; ?>-groupid" value="<?php echo $_GET['group_id']; ?>" />
			</table>
			<p class="submit"><input type="submit" name="<?php echo $this->_var; ?>-add_cvideo" value="+ Add Video" class="button-primary" /></p>
			<?php wp_nonce_field( $this->_var . '-nonce' ); ?>
		</form>
		<!-- EDIT GROUP SETTINGS -->
		<h2>Group Settings</h2>
		<form method="post" action="<?php echo $this->_selfLink . '-settings&group_id=' . $_GET['group_id']; ?>">
			<table class="form-table">
			<?php $gpath = $this->_options['groups'][$_GET['group_id']]; ?>
				<tr>
					<th><label for="group_name">Group Name <?php $this->_parent->tip('Insert new name for the group or leave same.'); ?></label></th>
					<td><input type="text" name="<?php echo $this->_var; ?>-name" id="group_name" size="45" maxlength="200" value="<?php echo stripslashes($gpath['name']); ?>" /></td>
				</tr>
				<tr>
					<td><label for="group_width">Group Thumbnail Width <?php $this->_parent->tip('Enter a common width in pixels for all the thumbnail images in this group.'); ?></label></td>
					<td><input type="text" name="<?php echo $this->_var; ?>-group-width" id="group_width" size="6" maxlength="18" value="<?php echo $gpath['width']; ?>" class="vscpixels" />px</td>
				</tr>
				<tr>
					<td><label for="group_height">Group Thumbnail Height <?php $this->_parent->tip('Enter a common height in pixels for all the thumbnail images in this group.'); ?></label></td>
					<td><input type="text" name="<?php echo $this->_var; ?>-group-height" id="group_height" size="6" maxlength="18" value="<?php echo $gpath['height']; ?>" class="vscpixels" />px</td>
				</tr>
				<tr>
					<td><label for="group_tlink">Text link for thumbnail images <?php $this->_parent->tip('Show text link with each thumbnail image.'); ?></label></td>
					<td>
						<?php $tlist = array('none' => 'None','above' => 'Above','below' => 'Below','both' => 'Both'); ?>
						<select name="<?php echo $this->_var; ?>-tlink" id="group_tlink">
						<?php
							foreach( $tlist as $tval => $tlab) {
								$select = '';
								if (isset($gpath['tlink'])){								
									if ($gpath['tlink'] == $tval) { $select = " selected "; }
								}
								echo '<option value="' . $tval . '"' . $select . '>' . $tlab . '</option>';
							}
						?>
						</select>
					</td>				
				</tr>
				<tr>
					<td valign="top"><label for="group_relate">Hide related videos <?php $this->_parent->tip('Select yes to hide related videos after your video is finished.'); ?></label></td>
					<td>
						<label><input type="radio" name="<?php echo $this->_var; ?>-relate" value="false" <?php if ( isset($gpath['related']) ) { if ($gpath['related'] != 'true') { echo " checked "; } } else { echo " checked "; } ?> /> No</label><br />
						<label><input type="radio" name="<?php echo $this->_var; ?>-relate" value="true" <?php if ( isset($gpath['related']) ) { if ($gpath['related'] == 'true') { echo " checked "; }} ?>/> Yes</label>
					</td>
				</tr>
				<input type="hidden" name="<?php echo $this->_var; ?>-groupid" value="<?php echo $_GET['group_id']; ?>" />
			</table>
			<p class="submit"><input type="submit" name="<?php echo $this->_var; ?>-group_set" value="Save Settings" class="button-primary" /></p>
			<?php wp_nonce_field( $this->_var . '-nonce' ); ?>
		</form>
		<?php
	}
	// ADDING GROUPS
	else {
		echo '<h2>' . $this->_name . ' Groups</h2>';
		
		$groupsnum = count($this->_options['groups']);
		if ($groupsnum >= 1) {
			?>
			<form method="post" action="<?php echo $this->_selfLink; ?>-settings">
				<div class="tablenav">
					<div class="alignleft actions">
						<input type="submit" name="<?php echo $this->_var; ?>-delete_groups" value="Delete" class="button-secondary delete" />
					</div>
			
					<br class="clear" />
				</div>
		
				<br class="clear" />
				<table class="widefat">
					<thead>
						<tr>
							<th scope="col" class="check-column"><input type="checkbox" id="check-all-groups" /></th>
							<th>Group Name</th>
							<th>Videos</th>
							<th>Shortcode</th>
							<th class="num">Dimensions (W x H)</th>
						</tr>
					</thead>
					<tbody>
					<?php
					
						$url = $this->_selfLink . '-settings';
					
						foreach( (array)($this->_options['groups']) as $id => $article ) {
							echo '<tr>';
							echo '<th scope="col" class="check-column"><input type="checkbox" name="' . $this->_var . '-groups[]" class="administrator groups" value="' . $id . '" /></th>';
							echo '<td><strong><a href="' . $url . '&group_id=' . $id . '" title="Modify Group Settings"> ' . stripslashes($article['name']) . '</a></strong></td>';
							echo '<td>' . count($this->_options['groups'][$id]['videos']) . ' (<a href="' . $url . '&group_id=' . $id . '">Add Videos</a>)</td>';
							// echo '<td><a href="' . $url . '&shortid=' . $id . '" title="Generate Shortcode">Generate Shortcode</a></td>';

							echo '<td><a href="' . admin_url('admin-ajax.php') . '?shortid=' . $id . '&action=vsc_shortgen&TB_iframe=0&width=448&height=248" class="thickbox">Create Shortcode</a></td>';

							echo '<td class="num">' . $article['width'] . ' x ' . $article['height'] . ' px</td>';
							echo '</tr>';
						}
					?>
					</tbody>
					<tfoot>
						<tr>
							<th scope="col" class="check-column"><input type="checkbox" id="check-all-groups" /></th>
							<th>Group Name</th>
							<th>Videos</th>
							<th>Shortcode</th>
							<th class="num">Dimensions (W x H)</th>
						</tr>
					</tfoot>
				</table>

				<div class="tablenav">
					<div class="alignleft actions">
						<input type="submit" name="<?php echo $this->_var; ?>-delete_groups" value="Delete" class="button-secondary delete" />
					</div>
			
					<br class="clear" />
				</div>
		
				<br class="clear" />

			<?php wp_nonce_field( $this->_var . '-nonce' ); ?>
			</form>
		<?php } ?>
		
		<!-- CREATE GROUP -->
		<h2>Create New Group</h2>
		<form method="post" action="<?php echo $this->_selfLink; ?>-settings">
			<input type="hidden" name="savepoint" value="" />
			<table class="form-table">
				<tr>
					<td><label for="group_name">Group Name <?php $this->_parent->tip('Enter a group name here.'); ?></label></td>
					<td><input type="text" name="<?php echo $this->_var; ?>-group-name" id="group_name" size="45" maxlength="45" value="" /></td>
				</tr>
				<tr>
					<td><label for="group_width">Group Thumbnail Width <?php $this->_parent->tip('Enter a common width in pixels for all the thumbnail images in this group.'); ?></label></td>
					<td><input type="text" name="<?php echo $this->_var; ?>-group-width" size="6" maxlength="18" value="120" class="vscpixels" />px</td>
				</tr>
				<tr>
					<td><label for="group_height">Group Thumbnail Height <?php $this->_parent->tip('Enter a common height in pixels for all the thumbnail images in this group.'); ?></label></td>
					<td><input type="text" name="<?php echo $this->_var; ?>-group-height" size="6" maxlength="18" value="90" class="vscpixels" />px</td>
				</tr>
			</table>
			<p class="submit"><input type="submit" name="<?php echo $this->_var; ?>-group-save" value="Create Group" class="button-primary" /></p> 
			<?php $this->nonce(); ?>
			<tr>
				<td><label for="role_access">Plugin access limits <?php $this->_parent->tip( '[Default: administrator] - Determine which user roles are allowed to have access to all plugin features. WARNING: This will allow other roles access to configure plugin settings and content and could be a security risk if you are not careful and use caution.' ); ?></label></td>
				<td style="min-width: 450px;">
					<select name="#access" <?php
					if ( !current_user_can( 'activate_plugins' ) ) {
						echo ' disabled';
					}
					?>>
						<option value="manage_network" <?php if ( $this->_options['access'] == 'manage_network' ) { echo 'selected'; } ?>>Network Administrator</option>
						<option value="activate_plugins" <?php if ( $this->_options['access'] == 'activate_plugins' ) { echo 'selected'; } ?>>Administrator</option>
						<option value="moderate_comments" <?php if ( $this->_options['access'] == 'moderate_comments' ) { echo 'selected'; } ?>>Editor</option>
						<option value="edit_published_posts" <?php if ( $this->_options['access'] == 'edit_published_posts' ) { echo 'selected'; } ?>>Author</option>
						<option value="edit_posts" <?php if ( $this->_options['access'] == 'edit_posts' ) { echo 'selected'; } ?>>Contributer</option>
					</select>
				</td>
			</tr>
		</table>
		<p class="submit clear"><input type="submit" name="save" value="Save Settings" class="button-primary" id="save" /></p>
		<?php $this->nonce(); ?> 
		</form>
	<?php } ?>
</div>
