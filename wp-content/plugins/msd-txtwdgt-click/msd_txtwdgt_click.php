<?php

/*
Plugin Name: MSD Text Widget with Click
Plugin URI: http://msdlab.com/plugins/msd-txtwdgt-click
Description: Replacement text widget for clickable widget area.
Version: 0.4
Author: Catherine M OBrien Sandrick (CMOS)
Author URI: http://msdlab.com/biological-assets/catherine-obrien-sandrick/
License: GPL v2
*/
define('MSD_ALT_API','http://msdlab.com/plugin-api/');

class MSD_Widget_Text extends WP_Widget {

	function __construct() {
		add_action('wp_print_styles', array($this,'add_css'));
		add_action('wp_print_scripts', array($this,'add_js'));
		$widget_ops = array('classname' => 'widget_text', 'description' => __('Arbitrary text or HTML with optional URL'));
		$control_ops = array('width' => 400, 'height' => 350);
		parent::__construct('text', __('Text'), $widget_ops, $control_ops);
		// For testing purpose, the site transient will be reset on each page load
		add_action( 'init', array(&$this,'msd_altapi_delete_transient') );
		// Hook into the plugin update check
		add_filter('pre_set_site_transient_update_plugins', array(&$this,'msd_altapi_check'));
		// Hook into the plugin details screen
		add_filter('plugins_api', array(&$this,'msd_altapi_information'), 10, 3);
	}

	function widget( $args, $instance ) {
		extract($args);
		$title = apply_filters( 'widget_title', empty($instance['title']) ? '' : $instance['title'], $instance, $this->id_base);
		$text = apply_filters( 'widget_text', $instance['text'], $instance );
		$url = empty($instance['url']) ? FALSE : $instance['url'];
		$target = $instance['target'] ? ' target="_blank"':'';
		$linktext = apply_filters( 'widget_title', empty($instance['linktext']) ? 'Read More' : $instance['linktext'], $instance, $this->id_base);
		echo $before_widget; ?>
		<div class="textwidget">
			<?php if ( !empty( $title ) ) { echo $before_title . $title . $after_title; } 
			echo $instance['filter'] ? wpautop($text) : $text; 
			if ( !empty( $url ) ) { echo '<div class="readmore"><span>'.$linktext.'</span></div>'; } ?>
		</div>
		<?php
		
		print $url?'<a href="'.$url.'"'.$target.' class="msd-widget-text"></a>':'';
		echo $after_widget;
	}

	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		$instance['title'] = strip_tags($new_instance['title']);
		if ( current_user_can('unfiltered_html') )
			$instance['text'] =  $new_instance['text'];
		else
			$instance['text'] = stripslashes( wp_filter_post_kses( addslashes($new_instance['text']) ) ); // wp_filter_post_kses() expects slashed
		$instance['filter'] = isset($new_instance['filter']);
		$instance['url'] = strip_tags($new_instance['url']);
		$instance['target'] = isset($new_instance['target']);
		$instance['linktext'] = strip_tags($new_instance['linktext']);
		
		return $instance;
	}

	function form( $instance ) {
		$instance = wp_parse_args( (array) $instance, array( 'title' => '', 'text' => '' ) );
		$title = strip_tags($instance['title']);
		$text = esc_textarea($instance['text']);
		$url = strip_tags($instance['url']);
		$linktext = strip_tags($instance['linktext']);
?>
		<p><label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:'); ?></label>
		<input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo esc_attr($title); ?>" /></p>

		<textarea class="widefat" rows="16" cols="20" id="<?php echo $this->get_field_id('text'); ?>" name="<?php echo $this->get_field_name('text'); ?>"><?php echo $text; ?></textarea>

		<p><input id="<?php echo $this->get_field_id('filter'); ?>" name="<?php echo $this->get_field_name('filter'); ?>" type="checkbox" <?php checked(isset($instance['filter']) ? $instance['filter'] : 0); ?> />&nbsp;<label for="<?php echo $this->get_field_id('filter'); ?>"><?php _e('Automatically add paragraphs'); ?></label></p>
		
		<p><label for="<?php echo $this->get_field_id('url'); ?>"><?php _e('URL:'); ?></label>
		<input class="widefat" id="<?php echo $this->get_field_id('url'); ?>" name="<?php echo $this->get_field_name('url'); ?>" type="text" value="<?php echo esc_attr($url); ?>" /></p>
		<p><input id="<?php echo $this->get_field_id('target'); ?>" name="<?php echo $this->get_field_name('target'); ?>" type="checkbox" <?php checked(isset($instance['target']) ? $instance['target'] : 0); ?> />&nbsp;<label for="<?php echo $this->get_field_id('target'); ?>"><?php _e('Open in new window'); ?></label></p>
		<p><label for="<?php echo $this->get_field_id('linktext'); ?>"><?php _e('Link Text:'); ?></label><input class="widefat" id="<?php echo $this->get_field_id('linktext'); ?>" name="<?php echo $this->get_field_name('linktext'); ?>" type="text" value="<?php echo esc_attr($linktext); ?>" /></p>
		
<?php
	}
	
	function init() {
		if ( !is_blog_installed() )
			return;
	
		unregister_widget('WP_Widget_Text');
		register_widget('MSD_Widget_Text');
	}
	
	function add_css(){
		if(!is_admin()){
			wp_enqueue_style('msd-widget-text',plugin_dir_url(__FILE__).'/css/msd-widget-text.css');
		}
	}
	function add_js(){
		if(!is_admin()){
			wp_enqueue_script('msd-widget-text',plugin_dir_url(__FILE__).'/js/msd-widget-text.js','jquery','0.4',TRUE);
		}
	}
	
	function msd_altapi_delete_transient() {
	    delete_site_transient( 'update_plugins' );
	}
	
	
	function msd_altapi_check( $transient ) {
	
	    // Check if the transient contains the 'checked' information
	    // If no, just return its value without hacking it
	    if( empty( $transient->checked ) )
	        return $transient;
	    
	    // The transient contains the 'checked' information
	    // Now append to it information form your own API
	    
	    $plugin_slug = plugin_basename( __FILE__ );
	    
	    // POST data to send to your API
	    $args = array(
	        'action' => 'update-check',
	        'plugin_name' => $plugin_slug,
	        'version' => $transient->checked[$plugin_slug],
	    );
	    
	    // Send request checking for an update
	    $response = $this->msd_altapi_request( $args );
	    
	    // If response is false, don't alter the transient
	    if( false !== $response ) {
	        $transient->response[$plugin_slug] = $response;
	    }
	    
	    return $transient;
	}
	
	// Send a request to the alternative API, return an object
	function msd_altapi_request( $args ) {
	
	    // Send request
	    $request = wp_remote_post( MSD_ALT_API, array( 'body' => $args ) );
	    
	    // Make sure the request was successful
	    if( is_wp_error( $request )
	    or
	    wp_remote_retrieve_response_code( $request ) != 200
	    ) {
	        // Request failed
	        return false;
	    }
	    
	    // Read server response, which should be an object
	    $response = unserialize( wp_remote_retrieve_body( $request ) );
	    if( is_object( $response ) ) {
	        return $response;
	    } else {
	        // Unexpected response
	        return false;
	    }
	}
	
	
	
	function msd_altapi_information( $false, $action, $args ) {
	
	    $plugin_slug = plugin_basename( __FILE__ );
	
	    // Check if this plugins API is about this plugin
	    if( $args->slug != $plugin_slug ) {
	        return false;
	    }
	        
	    // POST data to send to your API
	    $args = array(
	        'action' => 'plugin_information',
	        'plugin_name' => $plugin_slug,
	        'version' => $transient->checked[$plugin_slug],
	    );
	    
	    // Send request for detailed information
	    $response = $this->msd_altapi_request( $args );
	    
	    // Send request checking for information
	    $request = wp_remote_post( MSD_ALT_API, array( 'body' => $args ) );
	
	    return $response;
	}
	
}

	
	add_action('widgets_init',array('MSD_Widget_Text','init'),10);
?>