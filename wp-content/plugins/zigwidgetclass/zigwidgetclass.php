<?php
/*
Plugin Name: ZigWidgetClass
Plugin URI: http://www.zigpress.com/plugins/zigwidgetclass/
Description: Lets you add a custom class to each widget instance.
Version: 0.5
Author: ZigPress
Requires at least: 3.5
Tested up to: 3.5.2
Author URI: http://www.zigpress.com/
License: GPLv2
*/


/*
Copyright (c) 2011-2013 ZigPress

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
			if (version_compare($wp_version, '3.5', '<')) wp_die('ZigWidgetClass requires WordPress 3.5 or newer. Please update your installation.');
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

			if (!($widgetlogicfix = $widget['callback'][0]->option_name)) {
				# we do this because the Widget Logic plugin changes this structure
#				$widgetlogicfix = $widget['callback_wl_redirect'][0]->option_name; COMMENTED BECAUSE THIS ASSIGNMENT IS IN THE SECOND TEST BELOW

				if (!($widgetlogicfix = $widget['callback_wl_redirect'][0]->option_name)) {
					# same thing but for widget context plugin. i'm not convinced this is needed but it's in anyway.
					$widgetlogicfix = $widget['callback_original_wc'][0]->option_name; 
				}

			}

			$option_name = get_option($widgetlogicfix);
			$number = $widget['params'][0]['number'];
			if (isset($option_name[$number]['zigclass']) && !empty($option_name[$number]['zigclass'])) {
				# add our class to the start of the existing class declaration
				$params[0]['before_widget'] = preg_replace('/class="/', "class=\"{$option_name[$number]['zigclass']} ", $params[0]['before_widget'], 1);
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
