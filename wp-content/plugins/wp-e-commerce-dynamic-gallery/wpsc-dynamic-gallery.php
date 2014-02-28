<?php
/*
Plugin Name: WP e-Commerce Dynamic Gallery LITE
Plugin URI: http://a3rev.com/shop/wp-e-commerce-dynamic-gallery/
Description: Bring your product pages and presentation alive with WP e-Commerce Dynamic Gallery. Simply and Beautifully.
Version: 1.1.9.4
Author: A3 Revolution
Author URI: http://www.a3rev.com/
License: GPLv2 or later
*/

/*
	WP e-Commerce Dynamic Gallery. Plugin for the WP e-Commerce plugin.
	Copyright © 2011 A3 Revolution Software Development team
	
	A3 Revolution Software Development team
	admin@a3rev.com
	PO Box 1170
	Gympie 4570
	QLD Australia
*/
?>
<?php
define( 'WPSC_DYNAMIC_GALLERY_FILE_PATH', dirname(__FILE__) );
define( 'WPSC_DYNAMIC_GALLERY_DIR_NAME', basename(WPSC_DYNAMIC_GALLERY_FILE_PATH) );
define( 'WPSC_DYNAMIC_GALLERY_FOLDER', dirname(plugin_basename(__FILE__)) );
define( 'WPSC_DYNAMIC_GALLERY_NAME', plugin_basename(__FILE__) );
define( 'WPSC_DYNAMIC_GALLERY_URL', untrailingslashit( plugins_url( '/', __FILE__ ) ) );
define( 'WPSC_DYNAMIC_GALLERY_DIR', WP_CONTENT_DIR.'/plugins/'.WPSC_DYNAMIC_GALLERY_FOLDER );
define( 'WPSC_DYNAMIC_GALLERY_CSS_URL',  WPSC_DYNAMIC_GALLERY_URL . '/assets/css' );
define( 'WPSC_DYNAMIC_GALLERY_IMAGES_URL',  WPSC_DYNAMIC_GALLERY_URL . '/assets/images' );
define( 'WPSC_DYNAMIC_GALLERY_JS_URL',  WPSC_DYNAMIC_GALLERY_URL . '/assets/js' );
if(!defined("WPSC_DYNAMIC_GALLERY_DOCS_URI"))
    define("WPSC_DYNAMIC_GALLERY_DOCS_URI", "http://docs.a3rev.com/user-guides/plugins-extensions/wp-e-commerce/wpec-dynamic-gallery/");

include('admin/admin-ui.php');
include('admin/admin-interface.php');

include('admin/admin-pages/dynamic-gallery-page.php');

include('admin/admin-init.php');

include( 'classes/class-wpsc-dynamic-gallery-functions.php' );
include( 'classes/class-wpsc-dynamic-gallery-variations.php');
include( 'classes/class-wpsc-dynamic-gallery-preview.php' );
include( 'classes/class-wpsc-dynamic-gallery-hook-filter.php' );
include( 'classes/class-wpsc-dynamic-gallery-metaboxes.php' );
include( 'classes/class-wpsc-dynamic-gallery-display.php' );

include( 'admin/wpsc-dynamic-gallery-admin.php' );

/**
* Call when the plugin is activated
*/
register_activation_hook(__FILE__,'wpsc_dynamic_gallery_install');

function wpsc_dynamic_gallery_lite_uninstall() {
	if ( get_option('wpsc_dgallery_lite_clean_on_deletion') == 'yes' ) {
		delete_option( 'wpsc_dgallery_container_settings' );
		delete_option( 'wpsc_dgallery_global_settings' );
		delete_option( 'wpsc_dgallery_caption_settings' );
		delete_option( 'wpsc_dgallery_navbar_settings' );
		delete_option( 'wpsc_dgallery_lazyload_settings' );
		delete_option( 'wpsc_dgallery_thumbnail_settings' );
		
		delete_option( 'wpsc_dgallery_style_setting' );
		
		delete_option( 'product_gallery_width' );
		delete_option( 'width_type' );
		delete_option( 'product_gallery_height' );
		delete_option( 'product_gallery_auto_start' );
		delete_option( 'product_gallery_speed' );
		delete_option( 'product_gallery_effect' );
		delete_option( 'product_gallery_animation_speed' );
		delete_option( 'dynamic_gallery_stop_scroll_1image' );
		delete_option( 'bg_image_wrapper' );
		delete_option( 'border_image_wrapper_color' );
		
		delete_option( 'popup_gallery' );
		delete_option( 'dynamic_gallery_show_variation' );
		
		delete_option( 'caption_font' );
		delete_option( 'caption_font_size' );
		delete_option( 'caption_font_style' );
		delete_option( 'product_gallery_text_color' );
		delete_option( 'product_gallery_bg_des' );
		
		delete_option( 'product_gallery_nav' );
		delete_option( 'navbar_font' );
		delete_option( 'navbar_font_size' );
		delete_option( 'navbar_font_style' );
		delete_option( 'bg_nav_color' );
		delete_option( 'bg_nav_text_color' );
		delete_option( 'navbar_height' );
		
		delete_option( 'lazy_load_scroll' );
		delete_option( 'transition_scroll_bar' );
		
		delete_option( 'enable_gallery_thumb' );
		delete_option( 'dynamic_gallery_hide_thumb_1image' );
		delete_option( 'thumb_width' );
		delete_option( 'thumb_height' );
		delete_option( 'thumb_spacing' );
		
		delete_option('wpsc_dgallery_lite_clean_on_deletion');
		
		delete_post_meta_by_key('_actived_d_gallery');
		delete_post_meta_by_key('_wpsc_dgallery_show_variation');
		delete_post_meta_by_key('_wpsc_exclude_image');
		delete_post_meta_by_key('_wpsc_dgallery_in_variations');
	}
}
if ( get_option('wpsc_dgallery_lite_clean_on_deletion') == 'yes' ) {
	register_uninstall_hook( __FILE__, 'wpsc_dynamic_gallery_lite_uninstall' );
}
?>