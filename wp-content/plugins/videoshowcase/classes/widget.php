<?php
/**
 * widget_PluginBuddyVideoShowcase Class
 *
 * Adds widget capabilities.
 *
 * Author:	Skyler Moore
 * Date:	2010-09-13
 *
 */

 
class widget_PluginBuddyVideoShowcase extends WP_Widget {
	var $_widget_control_width = 300;
	var $_widget_control_height = 300;
	
	
	/**
	 * widget_PluginBuddyVideoShowcase::widget_PluginBuddyVideoShowcase()
	 * 
	 * Default constructor.
	 * 
	 * @return void
	 */
	function widget_PluginBuddyVideoShowcase() {
		$widget_ops = array('description' => __('Images that link to thickbox videos.', 'PluginBuddyVideoShowcase'));
		$this->WP_Widget('PluginBuddyVideoShowcase', __('Video Showcase'), $widget_ops);
	}
	
	
	/**
	 * widget_PluginBuddyVideoShowcase::widget()
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
		do_action( 'pluginbuddy-videoshowcase-widget', $instance, true);
		
		echo $after_widget;
	}
	
	
	/**
	 * widget_PluginBuddyVideoShowcase::update()
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
		$instance['max'] = $new_instance['max'];
		$instance['align'] = $new_instance['align'];
		$instance['order'] = $new_instance['order'];
		$instance['theme'] = $new_instance['theme'];
		return $instance;
	}
	
	
	/**
	 * widget_PluginBuddyVideoShowcase::form()
	 *
	 * Display widget control panel.
	 *
	 * @param	array	$instance	Instance data including title, group id, etc.
	 * @return	void
	 */
	function form($instance) {
		$title = ( isset( $instance['title'] ) ) ? $instance['title'] : '';
		$group = ( isset( $instance['group'] ) ) ? $instance['group'] : '';
		$max = ( isset( $instance['max'] ) ) ? $instance['max'] : '';
		$align = ( isset( $instance['align'] ) ) ? $instance['align'] : '';
		$order = ( isset( $instance['order'] ) ) ? $instance['order'] : '';
		$theme = ( isset( $instance['theme'] ) ) ? $instance['theme'] : '';
		
		$instance = wp_parse_args( (array) $instance, array( 'title' => __( 'VideoShowcase', 'PluginBuddyVideoShowcase' ), 'group' => $group ) );
		$title = esc_attr( $title );
		$group = intval( $group );
		
		$temp_options = get_option('pluginbuddy-videoshowcase');
		?>
		<!-- TITLE -->
		<p>
			<label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:', 'PluginBuddyVideoShowcase'); ?>
				<input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $title; ?>" />
			</label><br/>
		</p>
		<!-- GROUP -->
		<p>
			<label for="<?php echo $this->get_field_id('group'); ?>"><?php _e('Group:', 'PluginBuddyVideoShowcase'); ?>
				<select class="widefat" id="<?php echo $this->get_field_id('group'); ?>" name="<?php echo $this->get_field_name('group'); ?>">
					<?php foreach ( (array) $temp_options['groups'] as $id => $grouploop ) : ?>
						<option value="<?php echo $id; ?>"  <?php if ($group == $id) { echo " selected "; } ?>><?php echo stripslashes($grouploop['name']); ?></option>
					<?php endforeach; ?>
				</select>
			</label>
		</p>
		<!-- MAX -->
		<?php
			$limits = array();
			$limits['all'] = 'Show All (default)';
			
			for ( $count = 1; $count <= 20; $count++ ) {
				$limits[$count] = $count;
			}
		?>
		<p>
			<label for="<?php echo $this->get_field_id('max'); ?>"><?php _e('Maximum videos to show:', 'PluginBuddyVideoShowcase'); ?>
				<select class="widefat" id="<?php echo $this->get_field_id('max'); ?>" name="<?php echo $this->get_field_name('max'); ?>">
					<?php foreach ( (array) $limits as $id => $vlim ) : ?>
						<option value="<?php echo $id; ?>" <?php if ($max == $id) { echo " selected "; } ?>><?php echo $vlim; ?></option>
					<?php endforeach; ?>
				</select>
			</label>
		</p>
		<!-- ALIGNMENT -->
		<p>
			<label for="<?php echo $this->get_field_id('align'); ?>"><?php _e('Horizontal Alignment:', 'PluginBuddyVideoShowcase'); ?>
				<select class="widefat" id="<?php echo $this->get_field_id('align'); ?>" name="<?php echo $this->get_field_name('align'); ?>">
					<option value="center" <?php if ($align == 'center') { echo " selected "; } ?>>Center (default)</option>
					<option value="left" <?php if ($align == 'left') { echo " selected "; } ?>>Left</option>
					<option value="right" <?php if ($align == 'right') { echo " selected "; } ?>>Right</option>
					<option value="none" <?php if ($align == 'none') { echo " selected "; } ?>>None (controlled by CSS)</option>
				</select>
			</label>
		</p>
		<!-- ORDER -->
		<p>
			<label for="<?php echo $this->get_field_id('order'); ?>"><?php _e('Video Order:', 'PluginBuddyVideoShowcase'); ?>
				<br/><input type="radio" name="<?php echo $this->get_field_name('order'); ?>" value="ordered" <?php if ($order != 'random') { echo " checked "; } ?> /> Ordered<br/>
				<input type="radio" name="<?php echo $this->get_field_name('order'); ?>" value="random" <?php if ($order == 'random') { echo " checked "; } ?> /> Random
			</label>
		</p>
		<!-- THEME -->
		<?php
			$themes = array('default', 'light_rounded', 'dark_rounded', 'light_square', 'dark_square');
		?>
		<label for="<?php echo $this->get_field_id('theme'); ?>"><?php _e('Thickbox Theme:', 'PluginBuddyVideoShowcase'); ?>
			<select class="widefat" id="<?php echo $this->get_field_id('theme'); ?>" name="<?php echo $this->get_field_name('theme'); ?>">
				<?php foreach ( (array) $themes as $tbtheme ) : ?>
					<option value="<?php echo $tbtheme; ?>" <?php if ($theme == $tbtheme) { echo " selected "; } ?>><?php echo $tbtheme; ?></option>
				<?php endforeach; ?>
			</select>
		</label>
		
		<input type="hidden" id="<?php echo $this->get_field_id('submit'); ?>" name="<?php echo $this->get_field_name('submit'); ?>" value="1" />
		<?php
	}
	
	
} // End widget_PluginBuddyVideoShowcase class.

// Register function to create widget.
add_action('widgets_init', 'widget_PluginBuddyVideoShowcase_init');

function widget_PluginBuddyVideoShowcase_init() {
	register_widget('widget_PluginBuddyVideoShowcase');
}
?>
