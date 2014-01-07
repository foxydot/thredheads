<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html>
	<head>
		<title><?php esc_html_e( 'Accordion Item', 'it-l10n-accordion' ); ?></title>
		<?php
			wp_enqueue_script( 'jquery' );
			wp_enqueue_script( 'jquery-tools', $this->_parent->_pluginURL . '/js/jquery.tools.min.js', array( 'jquery' ), '1.2.5', true );
			wp_enqueue_style( 'jquery-tools', $this->_parent->_pluginURL . '/css/tabs.css' );
			wp_admin_css( 'global' );
			wp_admin_css( 'admin' );
			wp_admin_css();
			wp_admin_css( 'colors' );
			do_action('admin_print_styles');
			do_action('admin_print_scripts');
			do_action('admin_head');
			?>
	<script type='text/javascript'>
	jQuery(document).ready(function( $ ) {

	// setup ul.tabs to work as tabs for each div directly under div.panes
	$("ul.tabs").tabs("div.panes > div");
});
	</script>
	<style type='text/css'>
		.pb_ajax_assist {
			margin-bottom: 40px;
			margin-top: 10px;
		}
	</style>
	</head>
	<body>
	<div class='wrap'>
	<!-- the tabs -->
<ul class="tabs">
	<li><a href="#">Use a Post</a></li>
	<li><a href="#">Enter Content</a></li>
</ul>

<!-- tab "panes" -->
<div class="panes">
	<!--POST-->
	<div>
<?php
//Variable used for all form action attributes
$ajax_assist_url = add_query_arg( 
	array(
		'action' => 'pb_accordion_add_item',
		'_ajax_nonce' => wp_create_nonce( 'pb-add-accordion-item' ),
	),
	admin_url( 'admin-ajax.php' )
);
//Variable used for the parent ID
$parent_id = 0;
if ( isset( $_GET[ 'parent_id' ] ) ) {
	$parent_id = absint( $_GET[ 'parent_id' ] );
} elseif ( isset( $_POST[ 'parent_id' ] ) ) {
	$parent_id = absint( $_POST[ 'parent_id' ] );
}
//Variable used when editing an Accordion item
$edit_post_id = 0;
if ( isset( $_GET[ 'edit_post_id' ] ) ) {
	$edit_post_id = absint( $_GET[ 'edit_post_id' ] );
} elseif ( isset( $_POST[ 'edit_post_id' ] ) ) {
	$edit_post_id = absint( $_POST[ 'edit_post_id' ] );
}
//Get edit item content and title
$edit_item_content = $edit_item_title = '';
$edit_item = get_post( $edit_post_id );
if ( $edit_item ) {
	$edit_item_title = apply_filters( 'title_edit_pre', $edit_item->post_title );
	$edit_item_content = apply_filters( 'content_edit_pre', $edit_item->post_content );
}
//Get edit item post meta
$edit_item_meta = absint( get_post_meta( $edit_post_id, 'post_id', true ) );



if ( !isset( $_POST[ 'post_type_search' ] ) && !isset( $_POST[ 'create_post' ] ) ) {
	$post_types = get_post_types( array(
	       		'public' => true,
	       		'show_ui' => true
	       	), 'objects' );
	if ( $post_types ) {
		ob_start();
		foreach ( $post_types as $key => $post_type ) {
			$label = isset( $post_type->labels->name ) ? $post_type->labels->name : $key;	
			?>
			<option value='<?php echo esc_attr( $key ); ?>' <?php selected( isset( $_POST[ 'post_type' ] ) ? $_POST[ 'post_type' ] : 'pb_accordion_items', $key ); ?>><?php echo esc_html( $label ); ?></option>
			<?php
		} //end foreach post_types
		$select = ob_get_clean();
		$ajax_assist_url = add_query_arg( 
				array(
					'action' => 'pb_accordion_add_item',
					'_ajax_nonce' => wp_create_nonce( 'pb-add-accordion-item' ),
				),
				admin_url( 'admin-ajax.php' )
			);
		?>
		<form method="post" action="<?php echo esc_url( $ajax_assist_url ); ?>">
		<input type='hidden' name='parent_id' value='<?php echo $parent_id ?>' />
		<input type='hidden' name='edit_post_id' value='<?php echo $edit_post_id ?>' />
		<table class="widefat pb_ajax_assist">
		<tbody>
		<tr>
			<td><p><?php esc_html_e( 'Item Title:', 'it-l10n-accordion' ); ?></p></td>
			<td> <input type='text' name='item_title' value='<?php echo esc_attr( $edit_item_title ); ?>' /></td>
		</tr>
		<tr>
			<td><p><?php esc_html_e( 'Post Type:', 'it-l10n-accordion' ); ?></p></td>
			<td> <select  id="post_type" name="post_type">
					<?php echo $select; ?>
				</select></td>
		</tr>
		<tr>
		<td colspan='2'>
			<input type="submit" class="button-secondary" value="<?php esc_attr_e( 'Submit', 'it-l10n-accordion' ); ?>" name='post_type_search' />
			</form>
		</td>
		</tr>
		</table>
		<?php
	} //end $post_types
} elseif ( !isset( $_POST[ 'create_post' ] ) ) {
	$post_type = $_POST[ 'post_type' ];
	$search = isset( $_POST[ 's' ] ) ? $_POST[ 's' ] : '';			
	?>
	<div style="float: right;">
	<p>
	<?php
			?>
	<form method="post" action="<?php echo esc_url( $ajax_assist_url ); ?>">
		<input type='hidden' name='post_type' value='<?php echo esc_attr( $post_type ); ?>' />
		<input type='hidden' name='post_type_search' value='1' />
		<input type='hidden' name='type' value='post' />
		<input type='hidden' name='parent_id' value='<?php echo esc_attr( $parent_id ); ?>' />
		<input type='hidden' name='edit_post_id' value='<?php echo $edit_post_id ?>' />
		<input type='hidden' name='item_title' value='<?php echo esc_attr( $_POST[ 'item_title' ] ); ?>' />
		<input type="text" name="s" value="<?php echo esc_attr( $search ); ?>" />
		<input type="submit" class="button-secondary" value="<?php esc_attr_e( 'Search', 'it-l10n-accordion' ); ?>" />
	</form></p>
	</div>
	<form method="post" action="<?php echo esc_url( $ajax_assist_url ); ?>">
	<input type='hidden' name='type' value='post' />
	<input type='hidden' name='item_title' value='<?php echo esc_attr( $_POST[ 'item_title' ] ); ?>' />
	<input type='hidden' name='parent_id' value='<?php echo esc_attr( $parent_id ); ?>' />
	<input type='hidden' name='edit_post_id' value='<?php echo $edit_post_id ?>' />

	<table class="widefat pb_ajax_assist">
		<thead><tr><th>&nbsp;</th></th><th><?php esc_html_e( 'ID', 'it-l10n-accordion' ); ?></th><th><?php esc_html_e( 'Post Title', 'it-l10n-accordion' ); ?></th><th><?php esc_html_e( 'Post Date', 'it-l10n-accordion' ); ?></th></tr></thead>
		<tfoot><tr><th>&nbsp;</th><th><?php esc_html_e( 'ID', 'it-l10n-accordion' ); ?></th><th><?php esc_html_e( 'Post Title', 'it-l10n-accordion' ); ?></th><th><?php esc_html_e( 'Post Date', 'it-l10n-accordion' ); ?></th></tr></tfoot>
		<tbody>
	<?php
	$alt = true;
	global $wpdb;
	$search_query = "AND post_title LIKE %s";
	if ( !empty( $search ) ) {
		$search = "%{$search}%";
	} else {
		$search = $search_query = '';
	}
	
	$sql = $wpdb->prepare( "SELECT ID, post_title, post_date, post_name from $wpdb->posts WHERE post_status ='publish' AND post_type='{$post_type}'  {$search_query} ORDER BY post_date DESC", $search);
	$posts = $wpdb->get_results( $sql, ARRAY_A);
	foreach( $posts as $post ) {
		?>
		<tr <?php echo $alt === 'true' ? 'class="alternate"' : ''; ?> id="pb_ajax_assist_id_<?php echo esc_attr( $post[ 'ID' ] ); ?>">
			<th class="check-column" scope="row"><input class='pb_ajax_assist_tr' type="radio" value="<?php echo esc_attr( $post[ 'ID' ] ); ?>" name="post_id" <?php checked( $post[ 'ID' ], $edit_item_meta ); ?>></th>
			<td><?php echo esc_html( $post[ 'ID' ] ); ?></td>
			<td><?php echo stripslashes( esc_html( $post[ 'post_title' ] ) ); ?></td>
			<td><?php echo stripslashes( esc_html( $post[ 'post_date' ] ) ); ?></td>
	
			</td>
		</tr>
		<?php
		if ( $alt === false ) { $alt = true; } else { $alt = false; }
	} //end foreach
	?>			
	</tbody>
	</table>
	<div style='width: 100%; padding: 5px; background: #C9C9C9; border-top: 1px solid #B9B9B9;  position: fixed; bottom: 0;'>
	<input type='submit' class='button-primary' value='<?php esc_attr_e( 'Submit', 'it-l10n-accordion' ); ?>' style='float: right; margin-right: 30px' name='create_post' />
	</div>
	</form>
	<?php
} else {
	//Create the posts
	$submit_type = $_POST[ 'type' ];
	$post_content = isset( $_POST[ 'post_content' ] ) ? apply_filters( 'content_save_pre', $_POST[ 'post_content' ] ) : '';
	$post_id = isset( $_POST[ 'post_id' ] ) ? absint( $_POST[ 'post_id' ] ) : 0;
	if ( $post_id == 0 && $submit_type == 'post' ) {
		$this->alert( __( 'No posts were selected', 'it-l10n-accordion' ), true );
	} else {
		$args = array(
			'post_title' => sanitize_text_field( $_POST[ 'item_title' ] ),
			'post_type' => 'pb_accordion_child',
			'post_status' => 'publish',
			'post_parent' => absint( $_POST[ 'parent_id' ] ),
			'post_content' => $post_content,
		);
		if ( $edit_post_id != 0 ) {
			$args[ 'ID' ] = $edit_post_id;
		}
		$item_id = wp_insert_post( $args );
		//Remove or add post meta
		if ( $submit_type == 'post' ) update_post_meta( $item_id, 'post_id', $post_id );
		else delete_post_meta( $item_id, 'post_id' );
		
		$this->alert( __( 'Item has been added.', 'it-l10n-accordion' ) );
		$edit_item = get_post( $item_id );
		$accordion_item_html = str_replace( "'", "\'", $this->get_accordion_item_html( $edit_item ) );
		$accordion_item_html = str_replace( "\n", "", $accordion_item_html );
		$accordion_item_html = str_replace( "\t", "", $accordion_item_html );
		
		//$accordion_item_html = preg_replace( '/\s/', '', $accordion_item_html );
		?>
		<script type='text/javascript'>
			var item_html = '<?php echo $accordion_item_html; ?>';
			var accordion_item = jQuery( parent.document ).find( '#accordion_item_<?php echo esc_js( $edit_post_id ); ?>:first' );
			if ( accordion_item.length > 0 ) {
				//Editing the item
				accordion_item.replaceWith( item_html );
			} else {
				//Adding a new item
				jQuery( parent.document ).find( "#pb_reorder" ).append( item_html );
			}
			parent.tb_remove();
		</script>
		<?php

	
	} //end $post_id = 0
}//end post_type_search
?>
	
	</div>
	<!--/END POST-->
	<!--CONTENT-->
	<div>
		
		<form method="post" action="<?php echo esc_url( $ajax_assist_url ); ?>">
		<input type='hidden' name='type' value='content' />
		<input type='hidden' name='parent_id' value='<?php echo esc_attr( $parent_id ); ?>' />
		<input type='hidden' name='edit_post_id' value='<?php echo $edit_post_id ?>' />
		<p><?php esc_html_e( 'Enter normal post content here, including HTML and shortcodes' ); ?></p>
		<table class="widefat pb_ajax_assist">
		<tbody>
		<tr>
			<td><p><?php esc_html_e( 'Item Title:', 'it-l10n-accordion' ); ?></p></td>
			<td> <input type='text' name='item_title' value='<?php echo esc_attr( $edit_item_title ); ?>' /></td>
		</tr>
		<tr>
			<td><p><?php esc_html_e( 'Post Content:', 'it-l10n-accordion' ); ?></p></td>
			<td><textarea name='post_content' rows='8' style='width: 90%'><?php echo esc_html( $edit_item_content ); ?></textarea></td>
		</tr>
		<tr>
		</tr>
		</table>
		<p><input type='submit' class='button-primary' value='<?php esc_attr_e( 'Submit', 'it-l10n-accordion' ); ?>' style='float: right; margin-right: 30px' name='create_post' /></p>
		</form>
	</div>
	<!--END CONTENT -->
</div>


	</div>
<?php
do_action('admin_footer', '');
do_action('admin_print_footer_scripts');
?>
	</body>
</html>
