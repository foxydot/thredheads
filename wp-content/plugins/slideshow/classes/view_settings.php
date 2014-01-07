<?php
$this->_parent->load();
$this->admin_scripts();

if ( !empty( $_POST['save'] ) ) {
	$this->savesettings();
}
?>
<div class="wrap">
<?php
if ( isset( $_POST['create_group'] ) ) {
	check_admin_referer( $this->_parent->_var . '-nonce' );
	if ( isset( $_POST['group_name'] ) && ( $_POST['group_name'] != '' ) ) {
		$errors = false;
		foreach ( (array) $this->_options['groups'] as $id => $group ) {
			if ( $group['title'] == htmlentities( $_POST['group_name'] ) ) {
				$this->alert( 'This group name already exists. Please choose another name.', true );
				$errors = true;
				break;
			}
		}
		
		if ( $errors === false ) {
			$this_groupoptions = $this->_parent->_groupdefaults;
			
			$this_groupoptions['title'] = htmlentities( $_POST['group_name'] );
			array_push( $this->_options['groups'], $this_groupoptions );
			$this->_parent->save();
			
			$this->alert( 'Group "' . htmlentities( stripslashes( $_POST['group_name'] ) ) . '" has been added.' );
		}
	} else {
		$this->alert( 'You must provide a group name to add.', true );
	}
}

if ( isset( $_POST['delete_groups'] ) ) {
	if ( ! empty( $_POST['items'] ) && is_array( $_POST['items'] ) ) {
		$deleted_groups = '';
		
		foreach ( (array) $_POST['items'] as $id ) {
			$deleted_groups .= ' "' . stripslashes( $this->_options['groups'][$id]['title'] ) . '",';
			unset( $this->_options['groups'][$id] );
		}
		
		$this->_parent->save();
		$this->alert( 'Deleted group(s) ' . trim( $deleted_groups, ',' ) . '.' );
	}
}

if ( isset( $_GET['edit'] ) ) {
	require( 'view_settings-edit.php' );
} else {
	?>
	<h2><?php echo $this->_name; ?> Settings</h2><br />
	
	<form id="posts-filter" enctype="multipart/form-data" method="post" action="<?php echo $this->_selfLink; ?>-settings">
		<div class="tablenav">
			<div class="alignleft actions">
				<input type="submit" name="delete_groups" value="Delete" class="button-secondary delete" />
			</div>
		</div>
		<table class="widefat">
			<thead>
				<tr class="thead">
					<th scope="col" class="check-column"><input type="checkbox" class="check-all-entries" /></th>
					<th>Group Name</th>
					<th>Images</th>
					<th>Shortcode</th>
				</tr>
			</thead>
			<tfoot>
				<tr class="thead">
					<th scope="col" class="check-column"><input type="checkbox" class="check-all-entries" /></th>
					<th>Group Name</th>
					<th>Images</th>
					<th>Shortcode</th>
				</tr>
			</tfoot>
			<tbody>
				<?php
				if ( empty( $this->_options['groups'] ) ) {
					echo '<tr><td colspan="4" style="text-align: center;"><i>Please add a new ' . $this->_name . ' group below to get started.</i></td></tr>';
				} else {
					foreach ( (array) $this->_options['groups'] as $id => $group ) {
						?>
						<tr class="entry-row alternate" id="entry-<?php echo $id; ?>">
							<th scope="row" class="check-column"><input type="checkbox" name="items[]" class="entries" value="<?php echo $id; ?>" /></th>
							<td>
								<?php echo stripslashes( $group['title'] ); ?>
								<div class="row-actions" style="margin:0; padding:0;">
									<a href="<?php echo $this->_selfLink; ?>-settings&edit=<?php echo $id; ?>">Edit Group</a>
								</div>
							</td>
							<td>
								<?php echo count( $group['images'] ); ?>
								<div class="row-actions" style="margin:0; padding:0;">
									<a href="<?php echo $this->_selfLink; ?>-settings&edit=<?php echo $id; ?>">Manage Images</a>
								</div>
							</td>
							<td>
								[pb_slideshow group="<?php echo $id; ?>"]
							</td>
						</tr>
						<?php
					}
				}
				?>
			</tbody>
		</table>
		<div class="tablenav">
			<div class="alignleft actions">
				<input type="submit" name="delete_groups" value="Delete" class="button-secondary delete" />
			</div>
			<div style="float: right;"><small><i>Hover over a group above to edit group settings or manage images.</i></small></div>
		</div>
		
		<?php $this->nonce(); ?>
	</form><br />
	
	<h3>Add New <?php echo $this->_name; ?> Group</h3>
	<form method="post" action="<?php echo $this->_selfLink; ?>-settings">
		<table class="form-table">
			<tr>
				<td><label for="group_name">Group Name<?php $this->tip( 'Name of the new group to create. This is not publicly displayed.' ); ?></label></td>
				<td><input type="text" name="group_name" id="group_name" size="45" maxlength="45" /></td>
			</tr>
		</table>
		<p class="submit"><input type="submit" name="create_group" value="+ Add Group" class="button-primary" /></p>
		<?php $this->nonce(); ?>
	</form>
	
	<br>
	<h3>Plugin Settings</h3>
	<form method="post" action="<?php echo $this->_selfLink; ?>-settings">
		<?php // Ex. for saving in a group you might do something like: $this->_options['groups'][$_GET['group_id']['settings'] which would be: ['groups'][$_GET['group_id']['settings'] ?>
		<input type="hidden" name="savepoint" value="" />
		
		<table class="form-table">
			<tr>
				<td><label for="role_access">Plugin access limits <?php $this->tip( '[Default: administrator] - Determine which user roles are allowed to have access to all plugin features. WARNING: This will allow other roles access to configure plugin settings and content and could be a security risk if you are not careful and use caution.' ); ?></label></td>
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
		<br />
	</form>
<?php
}
?>
</div>
