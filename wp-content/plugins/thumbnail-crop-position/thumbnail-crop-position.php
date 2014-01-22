<?php
	/*
	Plugin Name: Thumbnail Crop Position
	Plugin URI: http://www.poselab.com/
	Description: Select the crop position of your thumbnails.
	Author: Javier Gómez Pose
	Author URI: http://www.poselab.com/
	Text Domain: thumb_crop_position
	Domain Path: /languages
	Version: 1.3
	License: GPL2

		Copyright 2013 Javier Gómez Pose  (email : javierpose@gmail.com)

		This program is free software; you can redistribute it and/or modify
		it under the terms of the GNU General Public License, version 2, as
		published by the Free Software Foundation.

		This program is distributed in the hope that it will be useful,
		but WITHOUT ANY WARRANTY; without even the implied warranty of
		MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
		GNU General Public License for more details.

		You should have received a copy of the GNU General Public License
		along with this program; if not, write to the Free Software
		Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
	*/


/*-----------------------------------------------------------------------------------*/
/* !Activation ===================================================================== */
/*-----------------------------------------------------------------------------------*/

register_activation_hook( __FILE__, 'tcp_activation' );
function tcp_activation() {
	$opts = get_option( 'thumb_crop_position_option' );

	if ( !$opts || !is_array($opts) || empty($opts) )
		update_option( 'thumb_crop_position_option', array( 'position' => 4 ) );
}


/*-----------------------------------------------------------------------------------*/
/* !Uninstall ====================================================================== */
/*-----------------------------------------------------------------------------------*/

register_uninstall_hook( __FILE__, 'tcp_uninstall' );
function tcp_uninstall() {
	delete_option( 'thumb_crop_position_option' );
}


/*-----------------------------------------------------------------------------------*/
/* !Include and launch the class only on admin side ================================ */
/*-----------------------------------------------------------------------------------*/

add_action('admin_init', 'thumbnail_crop_position_init');
function thumbnail_crop_position_init() {
	if ( !class_exists('Thumbnail_Crop_Position') )
		include( plugin_dir_path(__FILE__).'class-thumbnail-crop-position.php' );

	new Thumbnail_Crop_Position( __FILE__ );
}
