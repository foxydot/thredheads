<?php
/**
 * widget_PluginBuddyCopiousComments Class
 *
 * Adds widget capabilities.
 *
 * Author:	Dustin Bolton
 * Date:	January 2010
 *
 */

 
class widget_PluginBuddyCopiousComments extends WP_Widget {
	var $_widget_control_width = 300;
	var $_widget_control_height = 300;
	
	
	/**
	 * widget_PluginBuddyCopiousComments::widget_PluginBuddyCopiousComments()
	 * 
	 * Default constructor.
	 * 
	 * @return void
	 */
	function widget_PluginBuddyCopiousComments() {
		$widget_ops = array('description' => __('Displays most commented posts with comment count and bar.', 'PluginBuddyCopiousComments'));
		$this->WP_Widget('PluginBuddyCopiousComments', __('Copious Comments'), $widget_ops);
	}
	
	
	/**
	 * widget_PluginBuddyCopiousComments::widget()
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
		
		do_action( 'pluginbuddy-copiouscomments-widget', $instance );
		
		echo $after_widget;
	}
	
	
	/**
	 * widget_PluginBuddyCopiousComments::update()
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
		$instance['posts'] = intval($new_instance['posts']);
		$instance['width'] = intval($new_instance['width']);
		$instance['truncate'] = intval($new_instance['truncate']);
		return $instance;
	}
	
	
	/**
	 * widget_PluginBuddyCopiousComments::form()
	 *
	 * Display widget control panel.
	 *
	 * @param	array	$instance	Instance data including title, group id, etc.
	 * @return	void
	 */
	function form($instance) {
		global $PluginBuddyCopiousComments;
		$PluginBuddyCopiousComments->load();
		
		$title = ( isset( $instance['title'] ) ) ? $instance['title'] : '';
		$posts = ( isset( $instance['posts'] ) ) ? $instance['posts'] : $PluginBuddyCopiousComments->_options['posts'];
		$width = ( isset( $instance['width'] ) ) ? $instance['width'] : $PluginBuddyCopiousComments->_options['width'];
		$truncate = ( isset( $instance['truncate'] ) ) ? $instance['truncate'] : $PluginBuddyCopiousComments->_options['truncate'];
		?>
		<label for="<?php echo $this->get_field_id('title'); ?>">Title:
			<input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $title; ?>" />
		</label>
		<label for="<?php echo $this->get_field_id('posts'); ?>">Number of posts to display in list:
			<input class="widefat" id="<?php echo $this->get_field_id('posts'); ?>" name="<?php echo $this->get_field_name('posts'); ?>" type="text" value="<?php echo $posts; ?>" />
		</label>
		<label for="<?php echo $this->get_field_id('width'); ?>">Max width of widget (in percent) <?php $PluginBuddyCopiousComments->tip( 'Maximum width in percent to allow this to use. If you want to limit the width or correct for padding issues, reduce this number to a lower percent. Valid values are 1 to 100.' ); ?> :
			<input class="widefat" id="<?php echo $this->get_field_id('width'); ?>" name="<?php echo $this->get_field_name('width'); ?>" type="text" value="<?php echo $width; ?>" />
		</label>
		<label for="<?php echo $this->get_field_id('truncate'); ?>">Max characters in post title <?php $PluginBuddyCopiousComments->tip( 'Maximum number of characters to display from a post title before truncating and adding an elipses (...). This must be a number. Default: 60' ); ?> :
			<input class="widefat" id="<?php echo $this->get_field_id('truncate'); ?>" name="<?php echo $this->get_field_name('truncate'); ?>" type="text" value="<?php echo $truncate; ?>" />
		</label>
					
		<input type="hidden" id="<?php echo $this->get_field_id('submit'); ?>" name="<?php echo $this->get_field_name('submit'); ?>" value="1" />
		<?php
	}
	
	
} // End widget_PluginBuddyCopiousComments class.

// Register function to create widget.
add_action('widgets_init', 'widget_PluginBuddyCopiousComments_init');

function widget_PluginBuddyCopiousComments_init() {
	register_widget('widget_PluginBuddyCopiousComments');
}
?>