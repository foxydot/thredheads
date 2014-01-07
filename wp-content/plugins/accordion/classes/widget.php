<?php
/**
 *
 * Adds widget capabilities.
 *
 * Author:	Dustin Bolton
 * Created:	January 15, 2010
 * Update:	October 13, 2010
 *
 */

 
class widget_pluginbuddy_accordion extends WP_Widget {
	var $_widget_control_width = 300;
	var $_widget_control_height = 300;
	
	
	/**
	 * Default constructor.
	 * 
	 * @return void
	 */
	function widget_pluginbuddy_accordion() {
		global $pluginbuddy_accordion;
		$this->_parent = &$pluginbuddy_accordion;
		
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
		$instance[ 'widget_id' ] = $this->id;
		do_action( 'pluginbuddy_accordion-widget', $instance, true);
	}
	
	
	/**
	 * update()
	 *
	 * Save widget form settings.
	 *
	 * @param	array	$new_instance	NEW instance data including title, group id, etc.
	 * @param	array	$old_instance	PREVIOUS instance data including title, group id, etc.
	 * @return	void
	 */
	function update($new_instance, $old_instance) {
		$new_instance[ 'accordion' ] = absint( $new_instance[ 'accordion' ] );
		$new_instance[ 'id' ] = $new_instance[ 'accordion' ];
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
		global $pluginbuddy_accordion;
		$instance = array_merge( (array)$pluginbuddy_accordion->_widgetdefaults, (array)$instance );
		$this->_parent->widget_form( $instance, $this );
	}
}

function widget_pluginbuddy_accordion_init() {
	register_widget('widget_pluginbuddy_accordion');
}
add_action('widgets_init', 'widget_pluginbuddy_accordion_init' );

add_action( $pluginbuddy_accordion->_var . '-widget', array( &$pluginbuddy_accordion, 'widget' ) );
?>