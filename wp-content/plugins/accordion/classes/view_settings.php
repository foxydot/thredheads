<?php
$this->_parent->load();

if ( !empty( $_POST['save'] ) ) {
	$this->savesettings();
}

//Create an accordion
if ( isset( $_POST[ 'create_accordion' ] ) ) {
	if ( !wp_verify_nonce( $_REQUEST[ 'pb_accordion_create' ], 'pb-create-accordion' ) ) {
		$this->_parent->alert( __( 'Accordion could not be created - Security credentials could not be validated', 'it-l10n-accordion' ), true );
	} else {
		//Insert the post type
		$args = array(
			'post_type' => 'pb_accordion_group',
			'post_status' => 'publish',
			'post_title' => $_POST[ 'accordion_name' ]
		);
		$post_id = wp_insert_post( $args );
	
	}//end verify nonce
} //end $_POST[ 'create_accordion' ]

//Delete an accordion
if ( isset( $_POST[ 'items' ] ) && isset( $_POST[ 'delete_accordions' ] ) ) {
	if ( !wp_verify_nonce( $_REQUEST[ 'pb_accordion_delete' ], 'pb-delete-accordion' ) ) {
		$this->_parent->alert( __( 'Accordion could not be deleted - Security credentials could not be validated', 'it-l10n-accordion' ), true );
	} else {
		$items = $_POST[ 'items' ];
		$count = 0;
		foreach ( $items as $parent_id ) {
			$count += 1;
			//Delete the children (sorry)
			$children = get_posts( array( 'post_type' => 'pb_accordion_child', 'posts_per_page' => -1, 'numberposts' => -1, 'post_parent' => $parent_id ) );
			if ( $children ) {
				foreach ( $children as $child ) {
					wp_delete_post( $child->ID, true );
				}
			}
			//Delete the parent
			wp_delete_post( $parent_id, true );
		} //end foreach items
		$this->_parent->alert( sprintf( _n( '%s accordion deleted', '%s accordions deleted', $count, 'it-l10n-accordion' ), number_format( $count ) ), false );	
	}//end verify nonce
} //end $_POST[ 'delete_accordions' ]
?>
<div class="wrap">
	<?php
	if ( isset( $_GET[ 'edit' ] ) ) :
		include_once( $this->_parent->_pluginPath . '/classes/view_settings-edit.php' );
	else:
	?>
	<h2><?php printf( __( '%s Settings', 'it-l10n-accordion' ), $this->_name ); ?></h2><br />
	
	<form id="posts-filter" enctype="multipart/form-data" method="post" action="<?php echo $this->_selfLink; ?>-settings">
		<div class="tablenav">
			<div class="alignleft actions">
				<input type="submit" name="delete_accordions" value="<?php esc_attr_e( 'Delete', 'it-l10n-accordion' ); ?>" class="button-secondary delete" />
			</div>
		</div>
		<table class="widefat">
			<thead>
				<tr class="thead">
					<th scope="col" class="check-column"><input type="checkbox" class="check-all-entries" /></th>
					<th><?php esc_html_e( 'Accordion Name', 'it-l10n-accordion' ); ?></th>
					<th><?php esc_html_e( 'Shortcode', 'it-l10n-accordion' ); ?></th>
				</tr>
			</thead>
			<tfoot>
				<tr class="thead">
					<th scope="col" class="check-column"><input type="checkbox" class="check-all-entries" /></th>
					<th><?php esc_html_e( 'Accordion Name', 'it-l10n-accordion' ); ?></th>
					<th><?php esc_html_e( 'Shortcode', 'it-l10n-accordion' ); ?></th>
				</tr>
			</tfoot>
			<tbody>
			<?php
				$posts = get_posts( array( 'post_type' => 'pb_accordion_group', 'posts_per_page' => -1, 'numberposts' => -1 ) );
				if ( !$posts ) {
					?>
					<tr><td colspan='3'>No Accordions</td></tr>
					<?php
				} else {
					foreach ( $posts as $post ) {
						?>
						<tr>
							<th scope="row" class="check-column"><input class="entries" type='checkbox' name='items[]' value='<?php echo esc_attr( $post->ID ); ?>' /></th>
							<td><?php echo stripslashes( apply_filters( 'the_title', $post->post_title ) ); ?>
							<div class="row-actions" style="margin:0; padding:0;">
							<a href="<?php echo $this->_selfLink; ?>-settings&edit=<?php echo $post->ID; ?>"><?php esc_html_e( 'Edit Accordion', 'it-l10n-accordion' ); ?></a>
								</div>
							</td>
							<td>[pb_accordion id='<?php echo esc_html( $post->ID ); ?>']</td>
						</tr>
						<?php
					} //end foreach
				} //end if $posts
			?>
				
			</tbody>
		</table>
		<div class="tablenav">
			<div class="alignleft actions">
				<input type="submit" name="delete_accordions" value="<?php esc_attr_e( 'Delete', 'it-l10n-accordion' ); ?>" class="button-secondary delete" />
			</div>
		</div>
		
		<?php wp_nonce_field( 'pb-delete-accordion', 'pb_accordion_delete' ); ?>
	</form><br />
	
	
	
	<h3><?php printf( __( 'Add New %s', 'it-l10n-accordion' ) , $this->_name ); ?></h3>
	<form method="post" action="<?php echo esc_url( $this->_selfLink ); ?>-settings">
		<table class="form-table">
			<tr>
				<td><label for="accordion_name"><?php esc_html_e( 'Accordion Name', 'it-l10n-accordion' ); ?><?php $this->tip( __( 'Name of the new Accordion to create. This is not publicly displayed.', 'it-l10n-accordion' ) ); ?></label></td>
				<td><input type="text" name="accordion_name" id="accordion_name" size="45" maxlength="45" /></td>
			</tr>
		</table>
		<p class="submit"><input type="submit" name="create_accordion" value="<?php esc_attr_e( 'Add Accordion', 'it-l10n-accordion' ); ?>" class="button-primary" /></p>
		<?php wp_nonce_field( 'pb-create-accordion', 'pb_accordion_create' ); ?>
			<tr>
				<td><label for="role_access">Plugin access limits <?php $this->tip( '[Default: administrator] - Determine which user roles are allowed to have access to all plugin features. WARNING: This will allow other 							roles access to configure plugin settings and content and could be a security risk if you are not careful and use caution.' ); ?></label></td>
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
	<?php endif; ?>
</div><!--/.wrap-->
