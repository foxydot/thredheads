<?php
/**
 * widget_PluginBuddyFeaturedPosts Class
 *
 * Adds widget capabilities.
 *
 * Author:	Dustin Bolton
 * Date:	January 2010
 *
 */

 
class widget_PluginBuddyFeaturedPosts extends WP_Widget {
	var $_widget_control_width = 300;
	var $_widget_control_height = 300;
	
	
	/**
	 * widget_PluginBuddyFeaturedPosts::widget_PluginBuddyFeaturedPosts()
	 * 
	 * Default constructor.
	 * 
	 * @return void
	 */
	function widget_PluginBuddyFeaturedPosts() {
		$widget_ops = array('description' => __('Display your featured posts.', 'PluginBuddyFeaturedPosts'));
		$this->WP_Widget('PluginBuddyFeaturedPosts', __('FeaturedPosts'), $widget_ops);
	}
	
	
	/**
	 * widget_PluginBuddyFeaturedPosts::widget()
	 *
	 * Display public widget.
	 *
	 * @param	array	$args		Widget arguments -- currently not in use.
	 * @param	array	$instance	Instance data including title, group id, etc.
	 * @return	void
	 */
	function widget($args, $instance) {
		extract( $args );
		$title = apply_filters( 'widget_title', $instance['title'] );
		echo $before_widget;
		
		if ( $title )
			echo $before_title . $title . $after_title;
		
		$group = intval( $instance['group'] );
		do_action( 'pluginbuddy-featuredposts-widget', $group, true);
		
		echo $after_widget;
	}
	
	
	/**
	 * widget_PluginBuddyFeaturedPosts::update()
	 *
	 * Save widget form settings.
	 *
	 * @param	array	$new_instance	NEW instance data including title, group id, etc.
	 * @param	array	$old_instance	PREVIOUS instance data including title, group id, etc.
	 * @return	void
	 */
	function update($new_instance, $old_instance) {
		if (!isset($new_instance['submit'])) {
			return false;
		}
		$instance = $old_instance;
		$instance['title'] = strip_tags($new_instance['title']);
		$instance['group'] = intval($new_instance['group']);
		return $instance;
	}
	
	
	/**
	 * widget_PluginBuddyFeaturedPosts::form()
	 *
	 * Display widget control panel.
	 *
	 * @param	array	$instance	Instance data including title, group id, etc.
	 * @return	void
	 */
	function form($instance) {
		$title = ( isset( $instance['title'] ) ) ? $instance['title'] : '';
		?>
		<label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:', 'PluginBuddyFeaturedPosts'); ?>
			<input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $title; ?>" />
		</label>
		
		<input type="hidden" id="<?php echo $this->get_field_id('submit'); ?>" name="<?php echo $this->get_field_name('submit'); ?>" value="1" />
		<?php
	}
	
	
} // End widget_PluginBuddyFeaturedPosts class.

// Register function to create widget.
add_action('widgets_init', 'widget_PluginBuddyFeaturedPosts_init');

function widget_PluginBuddyFeaturedPosts_init() {
	register_widget('widget_PluginBuddyFeaturedPosts');
}
?>