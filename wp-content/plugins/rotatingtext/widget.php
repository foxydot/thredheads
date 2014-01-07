<?php
/**
 * widget_rotatingtext Class
 *
 * Adds widget capabilities.
 *
 * Author:Skyler Moore
 * Date:April 2010
 *
 */

 
class widget_rotatingtext extends WP_Widget {
	var $_widget_control_width = 300;
	var $_widget_control_height = 300;
	
	
	/**
	 * widget_rotatingtext::widget_rotatingtext()
	 * 
	 * Default constructor.
	 * 
	 * @return void
	 */
	function widget_rotatingtext() {
		$widget_ops = array('description' => __('Displays rotating text in a widget..', 'rotatingtext'));
		$this->WP_Widget('rotatingtext', __('Rotating Text'), $widget_ops);
	}
	
	
	/**
	 * widget_rotatingtext::widget()
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
		do_action( 'rotatingtext-widget', $group, true);
		
		echo $after_widget;
	}
	
	
	/**
	 * widget_rotatingtext::update()
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
	 * widget_rotatingtext::form()
	 *
	 * Display widget control panel.
	 *
	 * @param	array	$instance	Instance data including title, group id, etc.
	 * @return	void
	 */
	function form($instance) {
		//global $wpdb, $ithemes_theme_options;
		
		// Group indicates rotating images group for this instance.
		$group = ( isset( $instance['group'] ) ) ? $instance['group'] : '';
		$title = ( isset( $instance['title'] ) ) ? $instance['title'] : '';
		
		$instance = wp_parse_args( (array) $instance, array( 'title' => __( 'Rotating Text', 'rotatingtext' ), 'group' => $group ) );
		$title = esc_attr( $title );
		$group = intval( $group );
		
		$temp_options = get_option('rotatingtext');
		
?>
		<label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:', 'rotatingtext'); ?>
			<input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $title; ?>" />
		</label>
		
		<label for="<?php echo $this->get_field_id('group'); ?>"><?php _e('Text Group:', 'rotatingtext'); ?>
			<select class="widefat" id="<?php echo $this->get_field_id('group'); ?>" name="<?php echo $this->get_field_name('group'); ?>">
				<?php foreach ( (array) $temp_options['groups'] as $id => $grouploop ) : ?>
					<option value="<?php echo $id; ?>" <?php if ($group == $id) { echo " selected "; } ?>><?php echo stripslashes($grouploop['name']); ?></option>
				<?php endforeach; ?>
			</select>
		</label>
		
		<input type="hidden" id="<?php echo $this->get_field_id('submit'); ?>" name="<?php echo $this->get_field_name('submit'); ?>" value="1" />
<?php
	}
	
	
} // End widget_rotatingtext class.

// Register function to create widget.
add_action('widgets_init', 'widget_rotatingtext_init');

function widget_rotatingtext_init() {
	register_widget('widget_rotatingtext');
}
?>
