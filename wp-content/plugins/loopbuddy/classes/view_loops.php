<?php
$this->_parent->load();
$this->admin_scripts();
?>
<div class="wrap">
<?php
if ( isset( $_POST['create_group'] ) ) {
	check_admin_referer( $this->_parent->_var . '-nonce' );
	if ( isset( $_POST['group_name'] ) && ( $_POST['group_name'] != '' ) ) {
		$errors = false;
		foreach ( (array) $this->_options['loops'] as $id => $group ) {
			if ( $group['title'] == htmlentities( $_POST['group_name'] ) ) {
				$this->alert( 'This group name already exists. Please choose another name.', true );
				$errors = true;
				break;
			}
		}
		
		if ( $errors === false ) {
			$this_groupoptions = $this->_parent->_loopdefaults;
			
			$this_groupoptions['title'] = htmlentities( $_POST['group_name'] );
			array_push( $this->_options['loops'], $this_groupoptions );
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
			$deleted_groups .= ' "' . stripslashes( $this->_options['loops'][$id]['title'] ) . '",';
			unset( $this->_options['loops'][$id] );
		}
		
		$this->_parent->save();
		$this->alert( 'Deleted group(s) ' . trim( $deleted_groups, ',' ) . '.' );
	}
}

if ( isset( $_GET['edit'] ) ) {
	require( 'view_loops-edit.php' );
} else {
	?>
	<h2><?php echo $this->_name; ?> Loops</h2><br />
	
	<form id="posts-filter" enctype="multipart/form-data" method="post" action="<?php echo $this->_selfLink; ?>-loops">
		<div class="tablenav">
			<div class="alignleft actions">
				<input type="submit" name="delete_groups" value="Delete" class="button-secondary delete" />
			</div>
		</div>
		<table class="widefat">
			<thead>
				<tr class="thead">
					<th scope="col" class="check-column"><input type="checkbox" class="check-all-entries" /></th>
					<th>Loop Name</th>
					<th>Shortcode</th>
				</tr>
			</thead>
			<tfoot>
				<tr class="thead">
					<th scope="col" class="check-column"><input type="checkbox" class="check-all-entries" /></th>
					<th>Loop Name</th>
					<th>Shortcode</th>
				</tr>
			</tfoot>
			<tbody>
				<?php
				if ( empty( $this->_options['loops'] ) ) {
					echo '<tr><td colspan="4" style="text-align: center;"><i>Please add a new ' . $this->_name . ' group below to get started.</i></td></tr>';
				} else {
					foreach ( (array) $this->_options['loops'] as $id => $group ) {
						?>
						<tr class="entry-row alternate" id="entry-<?php echo $id; ?>">
							<th scope="row" class="check-column"><input type="checkbox" name="items[]" class="entries" value="<?php echo $id; ?>" /></th>
							<td>
								<?php echo stripslashes( $group['title'] ); ?>
								<div class="row-actions" style="margin:0; padding:0;">
									<a href="<?php echo $this->_selfLink; ?>-loops&edit=<?php echo $id; ?>">Edit Loop</a> |
									<a href="<?php echo $this->_selfLink; ?>-loops&edit=<?php echo $id; ?>">Visual Loop Editor</a>
								</div>
							</td>
							<td>
								[pb_loop loop="<?php echo $id; ?>"]
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
			<div style="float: right;"><small><i>Hover over a loop above to edit settings or use the visual loop editor.</i></small></div>
		</div>
		
		<?php $this->nonce(); ?>
	</form><br />
	
	<h3>Add New <?php echo $this->_name; ?> Loop</h3>
	<form method="post" action="<?php echo $this->_selfLink; ?>-loops">
		<table class="form-table">
			<tr>
				<td><label for="group_name">Loop Name<?php $this->tip( 'Name of the new group to create. This is not publicly displayed.' ); ?></label></td>
				<td><input type="text" name="group_name" id="group_name" size="45" maxlength="45" /></td>
			</tr>
		</table>
		<p class="submit"><input type="submit" name="create_group" value="+ Add Group" class="button-primary" /></p>
		<?php $this->nonce(); ?>
	</form>
<?php



}
?>
</div>