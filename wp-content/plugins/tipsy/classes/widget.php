<?php
/**
 *
 * Adds widget capabilities.
 *
 * Author:	Dustin Bolton
 * Created:	January 15, 2010
 * Update:	February 15, 2010
 *
 * Version: 2.0.2
 *
 */

class widget_pluginbuddy_tipsy extends WP_Widget {
	var $_widget_control_width = 300;
	var $_widget_control_height = 300;
	
	
	/**
	 * Default constructor.
	 * 
	 * @return void
	 */
	function widget_pluginbuddy_tipsy() {
		global $pluginbuddy_tipsy;
		$this->_parent = &$pluginbuddy_tipsy;
		
		$this->WP_Widget( $this->_parent->_var, $this->_parent->_name, array( 'description' => $this->_parent->_widget ) );
	}
	
	
	/**
	 * widget()
	 *
	 * Display public widget.
	 *
	 * @param	array	$args		Widget arguments -- currently not in use.
	 * @param	array	$instance	Instance data including title, group id, etc.
	 * @return	void
	 */
	function widget( $args, $instance ) {
		echo $args['before_widget']; // These handle some styling and wrappers in WordPress.
		
		if ( !empty( $instance['title'] ) ) {
			echo $args['before_title'];
			echo apply_filters( 'widget_title', $instance['title'] );
			echo $args['after_title'];
		}
		
		$this->_parent->widget( $instance );
		
		echo $args['after_widget'];
	}
	
	
	/**
	 * update()
	 *
	 * Save widget form settings.
	 *
	 * @param	array	$new_instance	NEW instance data including title, group id, etc.
	 * @param	array	$old_instance	PREVIOUS instance data including title, group id, etc.
	 * @return	array					Instance data to save for this widget.
	 */
	function update( $new_instance, $old_instance ) {
		if ( !isset( $new_instance['submit'] ) ) {
			return false;
		}
		return $new_instance;
	}
	
	
	/**
	 * form()
	 *
	 * Display widget control panel.
	 *
	 * @param	array	$instance	Instance data including title, group id, etc.
	 * @return	void
	 */
	function form( $instance ) {
		global $pluginbuddy_tipsy;
		$instance = array_merge( (array)$pluginbuddy_tipsy->_widgetdefaults, (array)$instance );
		
		echo '<label for="' . $this->get_field_id( 'title' ) . '">';
		echo '	Title (optional):';
		echo '	<input class="widefat" id="' . $this->get_field_id( 'title' ) . '" name="' . $this->get_field_name( 'title' ) . '" type="text" value="' . $instance['title'] . '" />';
		echo '</label>';
		
		$this->_parent->widget_form( $instance, $this );
	}
}

function widget_pluginbuddy_tipsy_init() {
	register_widget('widget_pluginbuddy_tipsy');
}

add_action('widgets_init', 'widget_pluginbuddy_tipsy_init' );
?>
