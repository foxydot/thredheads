<?php
/**
 *
 * Adds widget capabilities.
 *
 * Author:	Dustin Bolton
 * Created:	January 15, 2010
 * Update:	October 13, 2010
 *
 * Version: 2.0.0
 *
 */

 
class widget_pluginbuddy_loopbuddy extends WP_Widget {
	var $_widget_control_width = 300;
	var $_widget_control_height = 300;
	
	
	/**
	 * Default constructor.
	 * 
	 * @return void
	 */
	function widget_pluginbuddy_loopbuddy() {
		global $pluginbuddy_loopbuddy;
		$this->_parent = &$pluginbuddy_loopbuddy;
		
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
	function widget($args, $instance) {
		do_action( 'pluginbuddy_loopbuddy-widget', $instance, true);
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
	function update($new_instance, $old_instance) {
		if (!isset($new_instance['submit'])) {
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
		global $pluginbuddy_loopbuddy;
		$instance = array_merge( (array)$pluginbuddy_loopbuddy->_widgetdefaults, (array)$instance );
		$this->_parent->widget_form( $instance, $this );
	}
}

function widget_pluginbuddy_loopbuddy_init() {
	register_widget('widget_pluginbuddy_loopbuddy');
}
add_action('widgets_init', 'widget_pluginbuddy_loopbuddy_init' );

add_action( $pluginbuddy_loopbuddy->_var . '-widget', array( &$pluginbuddy_loopbuddy, 'widget' ) );
?>