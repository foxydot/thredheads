<?php
/*
Plugin Name: ZigWidgetClass
Plugin URI: http://www.zigpress.com/plugins/zigwidgetclass/
Description: Lets you add a custom class to each widget instance.
Version: 0.6.1
Author: ZigPress
Requires at least: 3.6
Tested up to: 3.8
Author URI: http://www.zigpress.com/
License: GPLv2
*/


/*
Copyright (c) 2011-2014 ZigPress

This program is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
as published by the Free Software Foundation; either version 2
of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation Inc, 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301, USA.
*/


# DEFINE PLUGIN


if (!class_exists('zigwidgetclass')) {


	class zigwidgetclass
	{


		public function __construct() {
			global $wp_version;
			if (version_compare(phpversion(), '5.2.4', '<')) wp_die('ZigWidgetClass requires PHP 5.2.4 or newer. Please update your server.');
			if (version_compare($wp_version, '3.6', '<')) wp_die('ZigWidgetClass requires WordPress 3.5 or newer. Please update your installation.');
			add_filter('widget_form_callback', array($this, 'filter_widget_form_callback'), 10, 2);
			add_filter('widget_update_callback', array($this, 'filter_widget_update_callback'), 10, 2);
			add_filter('dynamic_sidebar_params', array($this, 'filter_dynamic_sidebar_params'));
			add_filter('plugin_row_meta', array($this, 'filter_plugin_row_meta'), 10, 2 );
		}


		function filter_widget_form_callback($instance, $widget) {
			if (!isset($instance['zigclass'])) $instance['zigclass'] = null;
			?>
			<p>
			<label for='widget-<?php echo $widget->id_base?>-<?php echo $widget->number?>-zigclass'>CSS Class:</label>
			<input class='widefat' type='text' name='widget-<?php echo $widget->id_base?>[<?php echo $widget->number?>][zigclass]' id='widget-<?php echo $widget->id_base?>-<?php echo $widget->number?>-zigclass' value='<?php echo $instance['zigclass']?>'/>
			</p>
			<?php
			return $instance;
		}


		function filter_widget_update_callback($instance, $new_instance) {
			$instance['zigclass'] = $new_instance['zigclass'];
			return $instance;
		}


		function filter_dynamic_sidebar_params($params) {
			global $wp_registered_widgets;
			$widget_id = $params[0]['widget_id'];
			$widget = $wp_registered_widgets[$widget_id];

			# We're looking for the option_name (in wp_options) of where this widget's data is stored
			# Default location
			if (!($ouroptionname = $widget['callback'][0]->option_name)) {
				# Alternate location of option name if widget logic installed
				if (!($ouroptionname = $widget['callback_wl_redirect'][0]->option_name)) {
					# Alternate location of option name if widget context installed
					$ouroptionname = $widget['callback_original_wc'][0]->option_name; 
				}
			}
			$option_name = get_option($ouroptionname);

			# within the option, we're looking for the data for the right widget number
			# that's where we'll find the zigclass value if it exists
			$number = $widget['params'][0]['number'];
			if (isset($option_name[$number]['zigclass']) && !empty($option_name[$number]['zigclass'])) {
				# add our class to the start of the existing class declaration
				$params[0]['before_widget'] = preg_replace('/class="/', "class=\"{$option_name[$number]['zigclass']} ", $params[0]['before_widget'], 1);
			} else {
				# No zigclass found - but if we're using wp page widget, there could be one elsewhere
				
				# WP Page Widget plugin fix - the function exists test works because my plugin's name starts with Z so will always be loaded after WP Page Widget.
				# If another plugin also uses this function name then you've got bigger problems than adding a class to a widget...
				if (function_exists('pw_filter_widget_display_instance')) {
					global $post;
					$ouroptionname = 'widget_' . $post->ID . '_' . $widget['callback'][0]->id_base;
					# did we find a wp page widget option for this post					
					if ($option_name = get_option($ouroptionname)) {
						$number = $widget['params'][0]['number'];
						if (isset($option_name[$number]['zigclass']) && !empty($option_name[$number]['zigclass'])) {
							$params[0]['before_widget'] = preg_replace('/class="/', "class=\"{$option_name[$number]['zigclass']} ", $params[0]['before_widget'], 1);
						}
					}
				}
			}
			return $params;
		}


		public function filter_plugin_row_meta($links, $file) {
			$plugin = plugin_basename(__FILE__);
			if ($file == $plugin) { 
				return array_merge($links, array('<a target="_blank" href="http://www.zigpress.com/donations/">Donate</a>'));
			}
			return $links;
		}


	} # END OF CLASS


} else {
	wp_die('Namespace clash! Class zigwidgetclass already declared.');
}


# INSTANTIATE PLUGIN


$zigwidgetclass = new zigwidgetclass();


# EOF
