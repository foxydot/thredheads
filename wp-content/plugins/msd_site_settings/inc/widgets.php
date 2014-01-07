<?php
/**
 * Connected Class
 */
class MSDConnected extends WP_Widget {
    /** constructor */
    function MSDConnected() {
		$widget_ops = array('classname' => 'msd-connected', 'description' => __('Show social icons'));
		$control_ops = array('width' => 400, 'height' => 350);
		$this->WP_Widget('connected', __('Connected'), $widget_ops, $control_ops);
	}

	function widget( $args, $instance ) {
		extract($args);
		$title = apply_filters( 'widget_title', empty($instance['title']) ? '' : $instance['title'], $instance );
		$text = apply_filters( 'widget_text', empty( $instance['text'] ) ? '' : $instance['text'], $instance );
		echo $before_widget;
		if ( !empty( $title ) ) { print $before_title.$title.$after_title; } 
		?>
		<p><?php echo $text; ?></p>
		<?php do_shortcode('[msd-social]'); ?>
	<div class="clear"></div>
	<div id="digits">
		<?php print (get_option('msdsocial_phone')!='')?'PHONE: '.get_option('msdsocial_phone').'<br /> ':''; ?>
		<?php print (get_option('msdsocial_fax')!='')?'FAX: '.get_option('msdsocial_fax').'<br /> ':''; ?>
		<?php print (get_option('msdsocial_email')!='')?'<a href="mailto:'.get_option('msdsocial_email').'">'.strtoupper(get_option('msdsocial_email')).'</a><br /> ':''; ?>
	</div>
		<?php 	
		echo $after_widget;
	}

	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		$instance['title'] = strip_tags($new_instance['title']);
		if ( current_user_can('unfiltered_html') )
			$instance['text'] =  $new_instance['text'];
		else
			$instance['text'] = stripslashes( wp_filter_post_kses( addslashes($new_instance['text']) ) ); // wp_filter_post_kses() expects slashed
		
		return $instance;
	}

	function form( $instance ) {
		$instance = wp_parse_args( (array) $instance, array( 'title' => '', 'text' => '' ) );
		$title = strip_tags($instance['title']);
		$text = esc_textarea($instance['text']);
?>
		<p><label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:'); ?></label>
		<input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo esc_attr($title); ?>" /></p>	
		<textarea class="widefat" rows="16" cols="20" id="<?php echo $this->get_field_id('text'); ?>" name="<?php echo $this->get_field_name('text'); ?>"><?php echo $text; ?></textarea>
<?php
	}
}

add_action('widgets_init', create_function('', 'return register_widget("MSDConnected");'));/**
 * Connected Class
 */
class MSDAddress extends WP_Widget {
    /** constructor */
    function MSDAddress() {
		$widget_ops = array('classname' => 'msd-address', 'description' => __('Display addresses'));
		$control_ops = array('width' => 400, 'height' => 350);
		$this->WP_Widget('address', __('Address'), $widget_ops, $control_ops);
	}

	function widget( $args, $instance ) {
		extract($args);
		$title = apply_filters( 'widget_title', empty($instance['title']) ? '' : $instance['title'], $instance );
		echo $before_widget;
		if ( !empty( $title ) ) { print $before_title.$title.$after_title; } 
		?>
		<div id="address" class="address">
		<?php print (get_option('msdsocial_mailing_street')!='' && get_option('msdsocial_mailing_city')!='' && get_option('msdsocial_mailing_state')!='')?'<strong>MAILING ADDRESS:</strong> '.get_option('msdsocial_mailing_street').' '.get_option('msdsocial_mailing_street2').' | '.get_option('msdsocial_mailing_city').', '.get_option('msdsocial_mailing_state').' '.get_option('msdsocial_mailing_zip').'<br /> ':''; ?>
		<?php print (get_option('msdsocial_street')!='' && get_option('msdsocial_city')!='' && get_option('msdsocial_state')!='')?'<strong>PHYSICAL ADDRESS:</strong> '.get_option('msdsocial_street').' '.get_option('msdsocial_street2').', '.get_option('msdsocial_city').' | '.get_option('msdsocial_state').' '.get_option('msdsocial_zip').'<br /> ':''; ?>
	</div>
		<?php 	
		echo $after_widget;
	}

	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		$instance['title'] = strip_tags($new_instance['title']);

		return $instance;
	}

	function form( $instance ) {
		$instance = wp_parse_args( (array) $instance, array( 'title' => '', 'text' => '' ) );
		$title = strip_tags($instance['title']);
?>
		<p><label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:'); ?></label>
		<input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo esc_attr($title); ?>" /></p>	
<?php
	}
}

add_action('widgets_init', create_function('', 'return register_widget("MSDAddress");'));

class MSDCopyright extends WP_Widget {
    /** constructor */
    function MSDCopyright() {
		$widget_ops = array('classname' => 'msd-copyright', 'description' => __('Display copyright notice'));
		$control_ops = array('width' => 400, 'height' => 350);
		$this->WP_Widget('copyright', __('Copyright'), $widget_ops, $control_ops);
	}

	function widget( $args, $instance ) {
		extract($args);
		$title = apply_filters( 'widget_title', empty($instance['title']) ? '' : $instance['title'], $instance );
		echo $before_widget;
		if ( !empty( $title ) ) { print $before_title.$title.$after_title; } 
		?>
		<div id="copyright" class="copyright">Copyright &copy;<?php print date("Y"); ?>, <?php get_option('blogname') ?>. All rights reserved.
	</div>
		<?php 	
		echo $after_widget;
	}

	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		$instance['title'] = strip_tags($new_instance['title']);

		return $instance;
	}

	function form( $instance ) {
		$instance = wp_parse_args( (array) $instance, array( 'title' => '', 'text' => '' ) );
		$title = strip_tags($instance['title']);
?>
		<p><label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:'); ?></label>
		<input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo esc_attr($title); ?>" /></p>	
<?php
	}
}

add_action('widgets_init', create_function('', 'return register_widget("MSDCopyright");'));