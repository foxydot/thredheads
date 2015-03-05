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
        extract($instance);
		$title = apply_filters( 'widget_title', empty($instance['title']) ? '' : $instance['title'], $instance );
		$text = apply_filters( 'widget_text', empty( $instance['text'] ) ? '' : $instance['text'], $instance );
		echo $before_widget;
		if ( !empty( $title ) ) { print $before_title.$title.$after_title; } 
        if ( !empty( $text )){ print '<div class="connected-text">'.$text.'</div>'; }
        if ( $form_id > 0 ){
            print '<div class="connected-form">';
            print do_shortcode('[gravityform id="'.$form_id.'" title="true" description="true" ajax="true" tabindex=1000]');
            print '</div>';
        }
        
        if ( $address ){
            $address = do_shortcode('[msd-address]'); 
            if ( $address ){
                print '<div class="connected-address">'.$address.'</div>';
            }
        }
        if ( $phone ){
            $phone = '';
            if((get_option('msdsocial_tracking_phone')!='')){
                if(wp_is_mobile()){
                  $phone .= 'Phone: <a href="tel:+1'.get_option('msdsocial_tracking_phone').'">'.get_option('msdsocial_tracking_phone').'</a> ';
                } else {
                  $phone .= 'Phone: <span>'.get_option('msdsocial_tracking_phone').'</span> ';
                }
              $phone .= '<span itemprop="telephone" style="display: none;">'.get_option('msdsocial_phone').'</span> ';
            } else {
                if(wp_is_mobile()){
                  $phone .= (get_option('msdsocial_phone')!='')?'Phone: <a href="tel:+1'.get_option('msdsocial_phone').'" itemprop="telephone">'.get_option('msdsocial_phone').'</a> ':'';
                } else {
                  $phone .= (get_option('msdsocial_phone')!='')?'Phone: <span itemprop="telephone">'.get_option('msdsocial_phone').'</span> ':'';
                }
            }
            if ( $phone ){ print '<div class="connected-phone">'.$phone.'</div>'; }
        }
        if ( $tollfree ){
            $tollfree = '';
            if((get_option('msdsocial_tracking_tollfree')!='')){
                if(wp_is_mobile()){
                  $tollfree .= 'Toll Free: <a href="tel:+1'.get_option('msdsocial_tracking_tollfree').'">'.get_option('msdsocial_tracking_tollfree').'</a> ';
                } else {
                  $tollfree .= 'Toll Free: <span>'.get_option('msdsocial_tracking_tollfree').'</span> ';
                }
              $tollfree .= '<span itemprop="telephone" style="display: none;">'.get_option('msdsocial_tollfree').'</span> ';
            } else {
                if(wp_is_mobile()){
                  $tollfree .= (get_option('msdsocial_tollfree')!='')?'Toll Free: <a href="tel:+1'.get_option('msdsocial_tollfree').'" itemprop="telephone">'.get_option('msdsocial_tollfree').'</a> ':'';
                } else {
                  $tollfree .= (get_option('msdsocial_tollfree')!='')?'Toll Free: <span itemprop="telephone">'.get_option('msdsocial_tollfree').'</span> ':'';
                }
            }
            if ( $tollfree ){ print '<div class="connected-tollfree">'.$tollfree.'</div>'; }
        }
        if ( $fax ){
            $fax = (get_option('msdsocial_fax')!='')?'Fax: <span itemprop="faxNumber">'.get_option('msdsocial_fax').'</span> ':'';
            if ( $fax ){ print '<div class="connected-fax">'.$fax.'</div>'; }
        }
        if ( $email ){
            $email = (get_option('msdsocial_email')!='')?'Email: <span itemprop="email"><a href="mailto:'.antispambot(get_option('msdsocial_email')).'">'.antispambot(get_option('msdsocial_email')).'</a></span> ':'';
            if ( $email ){ print '<div class="connected-email">'.$email.'</div>'; }
        }
        if ( $social ){
            $social = do_shortcode('[msd-social]');
            if( $social ){ print '<div class="connected-social">'.$social.'</div>'; }
        }	
		echo $after_widget;
	}

	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		$instance['title'] = strip_tags($new_instance['title']);
		if ( current_user_can('unfiltered_html') )
			$instance['text'] =  $new_instance['text'];
		else
			$instance['text'] = stripslashes( wp_filter_post_kses( addslashes($new_instance['text']) ) ); // wp_filter_post_kses() expects slashed
		
        $instance['form_id'] = $new_instance['form_id'];
        $shows = array('address','phone','tollfree','fax','email','social');
        foreach($shows AS $s){
        $instance[$s] = $new_instance[$s];
        }
		return $instance;
	}

	function form( $instance ) {
		$instance = wp_parse_args( (array) $instance, array( 'title' => '', 'text' => '' ) );
		$title = strip_tags($instance['title']);
		$text = esc_textarea($instance['text']);
?>
		<p><label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:'); ?></label>
		<input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo esc_attr($title); ?>" /></p>	
		<textarea class="widefat" rows="5" cols="20" id="<?php echo $this->get_field_id('text'); ?>" name="<?php echo $this->get_field_name('text'); ?>"><?php echo $text; ?></textarea>
        <?php if(class_exists('GFForms')){ ?>
            <p><label for="<?php echo $this->get_field_id('form_id'); ?>"><?php _e('Show form:'); ?></label>
            <select id="<?php echo $this->get_field_id( 'form_id' ); ?>" name="<?php echo $this->get_field_name( 'form_id' ); ?>" style="width:90%;">
                <option value="0">Do not use form</option>
                <?php
                    $forms = RGFormsModel::get_forms(1, "title");
                    foreach ($forms as $form) {
                        $selected = '';
                        if ($form->id == rgar($instance, 'form_id'))
                            $selected = ' selected="selected"';
                        echo '<option value="'.$form->id.'" '.$selected.'>'.$form->title.'</option>';
                    }
                ?>
            </select>
        <?php } ?>
        <?php $shows = array('address','phone','tollfree','fax','email','social'); ?>
        <p>
            <?php foreach($shows AS $s){ ?>
            <input type="checkbox" name="<?php echo $this->get_field_name( $s ); ?>" id="<?php echo $this->get_field_id( $s ); ?>" <?php checked($instance[$s]); ?> value="1" /> <label for="<?php echo $this->get_field_id( $s ); ?>"><?php _e("Display ".$s); ?></label><br/>
            <?php } ?>
        </p>



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