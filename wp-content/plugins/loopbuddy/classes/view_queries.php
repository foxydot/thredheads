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
		foreach ( (array) $this->_options['queries'] as $id => $group ) {
			
			if ( $group['title'] == htmlentities( $_POST['group_name'] ) ) {
				$this->alert( __( 'This group name already exists. Please choose another name.', 'it-l10n-loopbuddy' ), true );
				$errors = true;
				break;
			}
		}
		include_once( $this->_pluginPath . '/classes/queryitems.php' );
		$lb_query_items = new loopbuddy_queryitems( $this->_parent );
		if ( $errors === false ) {
			$this_groupoptions = $lb_query_items->get_defaults( array( 'title' => sanitize_text_field( $_POST[ 'group_name' ] ) ) );
			
			array_push( $this->_options['queries'], $this_groupoptions );
			$this->_parent->save();
			
			$this->alert( sprintf( __( 'Group %s has been added.', 'it-l10n-loopbuddy' ), htmlentities( stripslashes( $_POST['group_name'] ) ) ) );
		}
	} else {
		$this->alert( __( 'You must provide a group name to add.', 'it-l10n-loopbuddy' ), true );
	}
}

if ( isset( $_POST['delete_groups'] ) ) {
	if ( ! empty( $_POST['items'] ) && is_array( $_POST['items'] ) ) {
		$deleted_groups = '';
		
		foreach ( (array) $_POST['items'] as $id ) {
			$deleted_groups .= ' "' . stripslashes( $this->_options['queries'][$id]['title'] ) . '",';
			unset( $this->_options['queries'][$id] );
		}
		
		$this->_parent->save();
		$this->alert( sprintf( 'Deleted group(s) %s.', trim( $deleted_groups, ',' ) ) );
	}
}

//Layout importing
if ( isset( $_POST['import_queries'] ) ) {
	if ( isset( $_FILES[ 'import_data' ] ) ) {
		$filename = $_FILES[ 'import_data' ][ 'tmp_name' ];
		
		$result = $this->_parent->import_query( $filename );
		if ( is_wp_error( $result ) ) {
			$this->alert( $result->get_error_message(), true );
		} else {
			$this->alert( sprintf( _n( '%1$s item has been imported', '%1$s items have been imported', $result, 'it-l10n-loopbuddy' ), number_format( $result ) ) );
		}
	}
} //end import groups

//Duplicating
if ( isset( $_POST['duplicate_queries'] ) ) {
	if ( ! empty( $_POST['items'] ) && is_array( $_POST['items'] ) ) {
		$duplicate_items = array();
		
		foreach ( (array) $_POST['items'] as $id ) {
			$duplicate_items[ $id ] = $this->_options[ 'queries' ][ $id ];
		}
		$result = $this->_parent->import( $duplicate_items, 'queries', true );
		if ( $result ) {
			$this->alert( __( 'Item(s) duplicated.', 'it-l10n-loopbuddy' ) ); 
		}		
	}
} //end duplicate


if ( isset( $_GET['edit'] ) ) {
	require( 'view_queries-edit.php' );
} else {
	?>
	<h2><img src="<?php echo $this->_pluginURL; ?>/images/loopbuddy_rings.png" style="vertical-align: -4px;"> <?php _e( 'Queries', 'it-l10n-loopbuddy' ); ?></h2><br />
	
	<form id="posts-filter" enctype="multipart/form-data" method="post" action="<?php echo $this->_selfLink; ?>-queries">
		<div class="tablenav">
			<div class="alignleft actions">
				<input type="submit" name="delete_groups" value="<?php esc_attr_e( 'Delete', 'it-l10n-loopbuddy' ); ?>" class="button-secondary delete" /><input type="submit" name="export_queries" value="<?php _e( 'Export', 'it-l10n-loopbuddy' ); ?>" class="button-secondary export" /><input type="submit" name="duplicate_queries" value="<?php _e( 'Duplicate', 'it-l10n-loopbuddy' ); ?>" class="button-secondary export" />

			</div>
		</div>
		<table class="widefat">
			<thead>
				<tr class="thead">
					<th scope="col" class="manage-column column-cb check-column"><input type="checkbox" class="check-all-entries" /></th>
					<th><?php _e( 'Query Name', 'it-l10n-loopbuddy' ); ?></th>
				</tr>
			</thead>
			<tfoot>
				<tr class="thead">
					<th scope="col" class="manage-column column-cb check-column"><input type="checkbox" class="check-all-entries" /></th>
					<th><?php _e( 'Query Name', 'it-l10n-loopbuddy' ); ?></th>
				</tr>
			</tfoot>
			<tbody>
				<?php
				if ( empty( $this->_options['queries'] ) ) {
					?>
					<tr><td colspan="4" style="text-align: center;"><i><?php printf( __( 'Please add a new %s group below to get started.', 'it-l10n-loopbuddy' ), $this->_name ); ?></i></td></tr>
					<?php
				} else {
					foreach ( (array) $this->_options['queries'] as $id => $group ) {
						?>
						<tr class="entry-row alternate" id="entry-<?php echo $id; ?>">
							<th scope="row" class="check-column"><input type="checkbox" name="items[]" class="entries" value="<?php echo $id; ?>" /></th>
							<td>
								<?php echo stripslashes( $group['title'] ); ?>
								<div class="row-actions" style="margin:0; padding:0;">
									<a href="<?php echo $this->_selfLink; ?>-queries&edit=<?php echo $id; ?>"><?php _e( 'Edit Query', 'it-l10n-loopbuddy' ); ?></a><!-- |
									<a href="<?php echo $this->_selfLink; ?>-queries&edit=<?php echo $id; ?>"><?php _e( 'Preview', 'it-l10n-loopbuddy' ); ?></a>-->
								</div>
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
				<input type="submit" name="delete_groups" value="<?php esc_attr_e( 'Delete', 'it-l10n-loopbuddy' ); ?>" class="button-secondary delete" /><input type="submit" name="export_queries" value="<?php _e( 'Export', 'it-l10n-loopbuddy' ); ?>" class="button-secondary export" /><input type="submit" name="duplicate_queries" value="<?php _e( 'Duplicate', 'it-l10n-loopbuddy' ); ?>" class="button-secondary export" />

			</div>
			<div style="float: right;"><small><i><?php _e( 'Hover over a query above to edit settings.', 'it-l10n-loopbuddy' ); ?></i></small></div>
		</div>
		
		<?php $this->nonce(); ?>
	</form><br />
	
	<h3><?php printf( __( 'Add New %s Query', 'it-l10n-loopbuddy' ), esc_html( $this->_name ) ); ?></h3>
	<form method="post" action="<?php echo esc_attr( $this->_selfLink ); ?>-queries">
		<table class="form-table">
			<tr>
				<td><label for="group_name"><?php _e( 'Query Name', 'it-l10n-loopbuddy' ); ?><?php $this->tip( __( 'Name of the new group to create. This is for your convenience and is not publicly displayed.', 'it-l10n-loopbuddy' ) ); ?></label></td>
				<td><input type="text" name="group_name" id="group_name" size="45" maxlength="45" /></td>
			</tr>
		</table>
		<p class="submit"><input type="submit" name="create_group" value="<?php _e( '+ Add Query', 'it-l10n-loopbuddy' ); ?>" class="button-primary" /></p>
		<?php $this->nonce(); ?>
	</form>
	<h3><?php printf( __( 'Import Queries', 'it-l10n-loopbuddy' ), esc_html( $this->_name ) ); ?></h3>
	<form method="post" action="<?php echo esc_attr( $this->_selfLink ); ?>-queries" enctype="multipart/form-data">
		<table class="form-table">
			<tr>
				<td><input type="file" name='import_data' /></td>
			</tr>
		</table>
		<p class="submit"><input type="submit" name="import_queries" value="<?php _e( 'Import', 'it-l10n-loopbuddy' ); ?>" class="button-primary" /></p>
		<?php $this->nonce(); ?>
	</form>

<?php
}
?>
</div>