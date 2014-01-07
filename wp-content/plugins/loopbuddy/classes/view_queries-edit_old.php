<?php
// SCRAP AND MOVE TO USING THIS?
// http://codex.wordpress.org/Displaying_Posts_Using_a_Custom_Select_Query
//

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

$group = &$this->_parent->get_query( $_GET['edit'] );

if ( !empty( $_POST['save'] ) ) {
	$this->savesettings();
}
?>

<h2><?php echo $this->_name; ?> Query Editor (<a href="<?php echo $this->_parent->_selfLink; ?>-settings">group list</a>)</h2>
<h3>Editing "<?php echo stripslashes( $group['title'] ); ?>"</h3>



<form method="post" action="<?php echo $this->_selfLink; ?>-settings&edit=<?php echo htmlentities( $_GET['edit'] ); ?>">
	<?php // Ex. for saving in a group you might do something like: $this->_options['groups'][$_GET['group_id']['settings'] which would be: ['groups'][$_GET['group_id']['settings'] ?>
	<input type="hidden" name="savepoint" value="['groups'][$_GET['edit']]" />
	
	<table class="form-table">
		<tr>
			<td><label for="title">Group Title<?php $this->tip( 'Title of the group. This is for internal reference only.' ); ?></label></td>
			<td><input type="text" name="#title" id="title" size="45" maxlength="45" value="<?php echo stripslashes( $group['title'] ); ?>" /></td>
		</tr>
		<tr>
			<td valign="top"><label for="post_type">Post Type <?php $this->tip( '' ); ?></label></td>
			<td>
				<label for="post_type_any"><input type="checkbox" name="post_type[]" value="any" id="post_type_any" <?php if ( 'any' == $group['post_type'] ) { echo 'checked'; } ?> /> Any</label><br />
				<?php
				global $wp_post_types;
				foreach ( (array) $wp_post_types as $post_type_key => $post_type ) {
					if ( ( $post_type_key != 'widget_content' ) && ( $post_type_key != 'nav_menu_item' ) ) {
						echo '<label for="post_type_' . $post_type_key . '"><input type="checkbox" name="post_type[]" value="' . $post_type_key . '" id="post_type_' . $post_type_key . '" ';
						if ( $post_type_key == $group['post_type'] ) {
							echo 'checked';
						}
						echo ' /> ' . $post_type->label . ' (' . $post_type_key . ')</label><br />';
					}
				}
				?>
				<label for="post_type_custom"><input type="checkbox" name="post_type[]" value="custom" id="post_type_custom" <?php if ( 'custom' == $group['post_type'] ) { echo 'checked'; } ?> /> Custom Post Type: </label>
				<input type="text" name="#custom_post_type" id="custom_post_type" size="22" maxlength="45" value="<?php echo $group['custom_post_type']; ?>" />
			</td>
		</tr>
		<tr>
			<td><label for="author">Author IDs<?php $this->tip( 'author (id; support comma delim, neg number to exclude only on first item since rest are inclusive)' ); ?></label></td>
			<td><input type="text" name="#author" id="author" size="45" maxlength="45" value="<?php echo $group['author']; ?>" /> <small><a href="#">Browse & Select</a></small></td>
		</tr>
		<tr>
			<td><label for="orderby">Order by <?php $this->tip( 'orderby author, date, title, modified, menu_order, parent, ID, rand, meta_value, meta_value_num, none, comment_count' ); ?></label></td>
			<td>
				<select name="#orderby" id="orderby">
					<option value="author" <?php if ( 'author' == $group['orderby'] ) { echo 'selected'; } ?> />Author</option>
					<option value="date" <?php if ( 'date' == $group['orderby'] ) { echo 'selected'; } ?> />Date</option>
					<option value="title" <?php if ( 'title' == $group['orderby'] ) { echo 'selected'; } ?> />Title</option>
					<option value="modified" <?php if ( 'modified' == $group['orderby'] ) { echo 'selected'; } ?> />Modified</option>
					<option value="menu_order" <?php if ( 'menu_order' == $group['orderby'] ) { echo 'selected'; } ?> />Menu Order</option>
					<option value="parent" <?php if ( 'parent' == $group['orderby'] ) { echo 'selected'; } ?> />Parent</option>
					<option value="ID" <?php if ( 'ID' == $group['orderby'] ) { echo 'selected'; } ?> />ID</option>
					<option value="rand" <?php if ( 'rand' == $group['orderby'] ) { echo 'selected'; } ?> />Random</option>
					<option value="meta_value" <?php if ( 'meta_value' == $group['orderby'] ) { echo 'selected'; } ?> />Meta Value</option>
					<option value="meta_value_num" <?php if ( 'meta_value_num' == $group['orderby'] ) { echo 'selected'; } ?> />Meta Value Number</option>
					<option value="none" <?php if ( 'none' == $group['orderby'] ) { echo 'selected'; } ?> />None</option>
					<option value="comment_count" <?php if ( 'comment_count' == $group['orderby'] ) { echo 'selected'; } ?> />Comment Count</option>
				</select>
			</td>
		</tr>
		<tr>
			<td><label for="order">Sort Direction <?php $this->tip( 'order=ASC (default),DESC' ); ?></label></td>
			<td>
				<label for="order-1">
					<input class="radio_toggle" type="radio" name="#order" id="order-1" value="ASC" <?php if ( $group['order'] == 'ASC' ) { echo ' checked '; } ?>/>
					Ascending
				</label>
				&nbsp;
				<label for="order-0">
					<input class="radio_toggle" type="radio" name="#order" id="order-0" value="DESC" <?php if ( $group['order'] == 'DESC' ) { echo ' checked '; } ?>/>
					Descending
				</label>
			</td>
		</tr>
		<tr>
			<td><label for="posts_per_page">Posts per Page <?php $this->tip( 'posts_per_page' ); ?></label></td>
			<td><input type="text" name="#posts_per_page" id="posts_per_page" size="5" maxlength="45" value="<?php echo $group['posts_per_page']; ?>" /></td>
		</tr>
		<tr>
			<td><label for="post_parent">Post Parent <?php $this->tip( 'post_parent' ); ?></label></td>
			<td><input type="text" name="#post_parent" id="post_parent" size="5" maxlength="45" value="<?php echo $group['post_parent']; ?>" /></td>
		</tr>
		<tr>
			<td><label for="hour">Hour <?php $this->tip( 'hour (0 to 23)' ); ?></label></td>
			<td><input type="text" name="#hour" id="hour" size="5" maxlength="45" value="<?php echo $group['hour']; ?>" /></td>
		</tr>
		<tr>
			<td><label for="minute">minute <?php $this->tip( 'minute (0 to 60)' ); ?></label></td>
			<td><input type="text" name="#minute" id="minute" size="5" maxlength="45" value="<?php echo $group['minute']; ?>" /></td>
		</tr>
		<tr>
			<td><label for="second">second <?php $this->tip( 'second (0 to 60)' ); ?></label></td>
			<td><input type="text" name="#second" id="second" size="5" maxlength="45" value="<?php echo $group['second']; ?>" /></td>
		</tr>
		<tr>
			<td><label for="day">Day <?php $this->tip( 'day (1 to 31)' ); ?></label></td>
			<td><input type="text" name="#day" id="day" size="5" maxlength="45" value="<?php echo $group['day']; ?>" /></td>
		</tr>
		<tr>
			<td><label for="monthnum">monthnum <?php $this->tip( 'monthnum (1 to 12)' ); ?></label></td>
			<td><input type="text" name="#monthnum" id="monthnum" size="5" maxlength="45" value="<?php echo $group['monthnum']; ?>" /></td>
		</tr>
		<tr>
			<td><label for="year">year <?php $this->tip( 'year Four digit year (e.g. 2011)' ); ?></label></td>
			<td><input type="text" name="#year" id="year" size="5" maxlength="45" value="<?php echo $group['year']; ?>" /></td>
		</tr>
		<tr>
			<td><label for="w">week <?php $this->tip( 'w (0 to 53)' ); ?></label></td>
			<td><input type="text" name="#w" id="w" size="5" maxlength="45" value="<?php echo $group['w']; ?>" /></td>
		</tr>
		<tr>
			<td><label for="post_status">Post Status <?php $this->tip( 'post_status ( publish, pending, draft, future, private, trash; auto-draft )' ); ?></label></td>
			<td>
				<select name="#post_status" id="post_status">
					<option value="publish" <?php if ( 'publish' == $group['post_status'] ) { echo 'selected'; } ?> />Published</option>
					<option value="pending" <?php if ( 'pending' == $group['post_status'] ) { echo 'selected'; } ?> />Pending</option>
					<option value="draft" <?php if ( 'draft' == $group['post_status'] ) { echo 'selected'; } ?> />Draft</option>
					<option value="auto-draft" <?php if ( 'auto-draft' == $group['post_status'] ) { echo 'selected'; } ?> />Auto Draft</option>
					<option value="future" <?php if ( 'future' == $group['post_status'] ) { echo 'selected'; } ?> />Future</option>
					<option value="private" <?php if ( 'private' == $group['post_status'] ) { echo 'selected'; } ?> />Private</option>
					<option value="trash" <?php if ( 'trash' == $group['post_status'] ) { echo 'selected'; } ?> />Trash</option>
				</select>
			</td>
		</tr>
		<tr>
			<td colspan="2"><h3>Categories</h3></td>
		</tr>
		<tr>
			<td><label for="category__and">Require all<?php $this->tip( 'category__and (id)' ); ?></label></td>
			<td><input type="text" name="#category__and" id="category__and" size="45" maxlength="45" value="<?php echo $group['category__and']; ?>" /> <small><a href="#">Browse & Select</a></small></td>
		</tr>
		<tr>
			<td><label for="category__in">Require at least one<?php $this->tip( 'category__in (id)' ); ?></label></td>
			<td><input type="text" name="#category__in" id="category__in" size="45" maxlength="45" value="<?php echo $group['category__in']; ?>" /> <small><a href="#">Browse & Select</a></small></td>
		</tr>
		<tr>
			<td><label for="category__not_in">Exclude<?php $this->tip( 'category__not_in (id)' ); ?></label></td>
			<td><input type="text" name="#category__not_in" id="category__not_in" size="45" maxlength="45" value="<?php echo $group['category__not_in']; ?>" /> <small><a href="#">Browse & Select</a></small></td>
		</tr>
		
		
		
		
	tag__not_in (id)
		<tr>
			<td colspan="2"><h3>Tags</h3></td>
		</tr>
		<tr>
			<td><label for="tag__and">Require all<?php $this->tip( 'tag__and (id)' ); ?></label></td>
			<td><input type="text" name="#tag__and" id="tag__and" size="45" maxlength="45" value="<?php echo $group['tag__and']; ?>" /> <small><a href="#">Browse & Select</a></small></td>
		</tr>
		<tr>
			<td><label for="tag__in">Require at least one<?php $this->tip( 'tag__in (id)' ); ?></label></td>
			<td><input type="text" name="#tag__in" id="tag__in" size="45" maxlength="45" value="<?php echo $group['tag__in']; ?>" /> <small><a href="#">Browse & Select</a></small></td>
		</tr>
		<tr>
			<td><label for="tag__not_in">Exclude<?php $this->tip( 'tag__not_in (id)' ); ?></label></td>
			<td><input type="text" name="#tag__not_in" id="tag__not_in" size="45" maxlength="45" value="<?php echo $group['tag__not_in']; ?>" /> <small><a href="#">Browse & Select</a></small></td>
		</tr>
		
		
		
		
		
		
		<tr>
			<td><label for="post_type">Post Type <?php $this->tip( '' ); ?></label></td>
			<td>
				<select name="#post_type" id="post_type">
					<option value="any" <?php if ( 'any' == $group['post_type'] ) { echo 'selected'; } ?> />Any</option>
					<option value="attachment" <?php if ( 'attachment' == $group['post_type'] ) { echo 'selected'; } ?> />Attachment</option>
					<option value="page" <?php if ( 'page' == $group['post_type'] ) { echo 'selected'; } ?> />Page</option>
					<option value="post" <?php if ( 'post' == $group['post_type'] ) { echo 'selected'; } ?> />Post</option>
					<option value="revision" <?php if ( 'revision' == $group['post_type'] ) { echo 'selected'; } ?> />Revision</option>
					<option value="custom" <?php if ( 'custom' == $group['post_type'] ) { echo 'selected'; } ?> />Custom Post Type*</option>
				</select>
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
	</table>
	
	<pre>
	http://codex.wordpress.org/Function_Reference/query_posts
	
	KEY:
	IN ALL OF THESE			AND			__and
	WITHIN ANY OF THESE		OR			__in
	EXCLUDE					IF IN		__not_in
	
	

	
	
	
	

	
	post__in
	post__not_in
	
	caller_get_posts (pre-3.1); ignore_sticky_posts (post-3.1)
	

	
	ADVANCED:
	
	offset
	custom mysql using posts_where filter
	meta_key,meta_value,meta_compare
	
	nopaging=true
	paged=get_query_var( 'paged' );
	</pre>
	
	
	
	<p class="submit"><input type="submit" name="save" value="Save Settings" class="button-primary" id="save" /></p>
	<?php $this->nonce(); ?>
</form>