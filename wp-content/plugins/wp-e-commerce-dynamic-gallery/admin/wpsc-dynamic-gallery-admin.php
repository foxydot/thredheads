<?php
function wpsc_dynamic_gallery_install(){
	update_option('a3rev_wpsc_dgallery_version', '1.1.9.4');
	update_option('a3rev_wpsc_dgallery_lite_version', '1.1.9.4');
	
	// Set Settings Default from Admin Init
	global $wpsc_dgallery_admin_init;
	$wpsc_dgallery_admin_init->set_default_settings();
	
	update_option('a3rev_wpsc_dgallery_just_installed', true);
}

/**
 * Load languages file
 */
function wpsc_dynamic_gallery_init() {
	if ( get_option('a3rev_wpsc_dgallery_just_installed') ) {
		delete_option('a3rev_wpsc_dgallery_just_installed');
		wp_redirect( admin_url( 'edit.php?post_type=wpsc-product&page=wpsc-dynamic-gallery', 'relative' ) );
		exit;
	}
	load_plugin_textdomain( 'wpsc_dgallery', false, WPSC_DYNAMIC_GALLERY_FOLDER.'/languages' );
	$wpsc_dgallery_thumbnail_settings = get_option( 'wpsc_dgallery_thumbnail_settings', array('thumb_width' => '105', 'thumb_height' => '75' ) );
	$thumb_width = $wpsc_dgallery_thumbnail_settings['thumb_width'];
	if ( $thumb_width <= 0 ) $thumb_width = 105;
	$thumb_height = $wpsc_dgallery_thumbnail_settings['thumb_height'];
	if ( $thumb_height <= 0 ) $thumb_height = 75;
	add_image_size( 'wpsc-dynamic-gallery-thumb', $thumb_width, $thumb_height, false  );
}
// Add language
add_action('init', 'wpsc_dynamic_gallery_init');

// Add custom style to dashboard
add_action( 'admin_enqueue_scripts', array( 'WPSC_Dynamic_Gallery_Hook_Filter', 'a3_wp_admin' ) );

// Add text on right of Visit the plugin on Plugin manager page
add_filter( 'plugin_row_meta', array('WPSC_Dynamic_Gallery_Hook_Filter', 'plugin_extra_links'), 10, 2 );

// Need to call Admin Init to show Admin UI
global $wpsc_dgallery_admin_init;
$wpsc_dgallery_admin_init->init();

// Add upgrade notice to Dashboard pages
add_filter( $wpsc_dgallery_admin_init->plugin_name . '_plugin_extension', array( 'WPSC_Dynamic_Gallery_Functions', 'plugin_extension' ) );

// Add extra fields for image in Product Edit Page
add_filter( 'attachment_fields_to_edit', array('WPSC_Dynamic_Gallery_Hook_Filter', 'wpsc_attachment_fields_filter'), 12, 2 );
add_filter( 'attachment_fields_to_save', array('WPSC_Dynamic_Gallery_Hook_Filter', 'wpsc_exclude_image_from_product_page_field_save'), 1, 2 );
add_action( 'add_attachment', array('WPSC_Dynamic_Gallery_Hook_Filter', 'wpsc_exclude_image_from_product_page_field_add') );

// Version 1.0.5
add_filter( 'attachment_fields_to_edit', array('WPSC_Dynamic_Gallery_Variations', 'media_fields'), 13, 2 );

add_action('admin_footer', array('WPSC_Dynamic_Gallery_Hook_Filter', 'wp_admin_footer_scripts') );

//Ajax do dynamic gallery frontend
add_action('wp_ajax_wpsc_dynamic_gallery_frontend', array('WPSC_Dynamic_Gallery_Hook_Filter', 'wpsc_dynamic_gallery_frontend') );
add_action('wp_ajax_nopriv_wpsc_dynamic_gallery_frontend', array('WPSC_Dynamic_Gallery_Hook_Filter', 'wpsc_dynamic_gallery_frontend') );

//Frontend do dynamic gallery
if (!is_admin()) 
	add_action('init', array('WPSC_Dynamic_Gallery_Hook_Filter', 'dynamic_gallery_frontend_script') );

//Frontend do dynamic gallery
add_action('wp_head', 'wpsc_show_dynamic_gallery_with_goldcart' );

function wpsc_show_dynamic_gallery_with_goldcart() {
	global $post;
	
	if ( !function_exists( 'gold_shpcrt_display_gallery' ) ){
		function gold_shpcrt_display_gallery($product_id){
			global $wpsc_dgallery_global_settings;
			
			$global_wpsc_dgallery_activate = $wpsc_dgallery_global_settings['dgallery_activate'];
			
			if ($product_id <= 0) {
				$product_id = $post->ID;
			}
			$actived_d_gallery = get_post_meta($product_id, '_actived_d_gallery',true);
			if ($actived_d_gallery == '' && $global_wpsc_dgallery_activate != 'no') {
				$actived_d_gallery = 1;
			}
			
			if ( is_singular('wpsc-product') && $actived_d_gallery == 1 ) {
				wp_enqueue_script( 'filter-gallery-script', WPSC_DYNAMIC_GALLERY_JS_URL . '/filter_gallery.js', array(), false, true );
				
				WPSC_Dynamic_Gallery_Hook_Filter::dynamic_gallery_frontend_script();
				echo WPSC_Dynamic_Gallery_Display_Class::wpsc_dynamic_gallery_display($product_id);
			}
		}
	} else {
		global $wpsc_dgallery_global_settings;
		
		$global_wpsc_dgallery_activate = $wpsc_dgallery_global_settings['dgallery_activate'];
		$actived_d_gallery = get_post_meta($post->ID, '_actived_d_gallery',true);
		if ($actived_d_gallery == '' && $global_wpsc_dgallery_activate != 'no') {
			$actived_d_gallery = 1;
		}
		
		if ( is_singular('wpsc-product') && $actived_d_gallery == 1 ) {
			wp_enqueue_script( 'filter-gallery-script', WPSC_DYNAMIC_GALLERY_JS_URL . '/filter_gallery.js', array(), false, true );
			
			WPSC_Dynamic_Gallery_Hook_Filter::dynamic_gallery_frontend_script();
			add_action('get_footer', array('WPSC_Dynamic_Gallery_Hook_Filter', 'do_dynamic_gallery'), 8 );
		}
	}	
}

// Check upgrade functions
add_action('plugins_loaded', 'wpsc_dgallery_lite_upgrade_plugin');
function wpsc_dgallery_lite_upgrade_plugin () {
	
	// Upgrade to 1.0.4
	if ( version_compare(get_option('a3rev_wpsc_dgallery_version'), '1.0.4') === -1 ) {
		update_option('width_type','px');
		update_option('a3rev_wpsc_dgallery_version', '1.0.4');
	}
	
	// Upgrade to 1.1.4
	if (version_compare(get_option('a3rev_wpsc_dgallery_version'), '1.1.4') === -1 ) {
		WPSC_Dynamic_Gallery_Functions::upgrade_1_1_4();
		
		update_option('a3rev_wpsc_dgallery_version', '1.1.4');
	}
	
	// Upgrade to 1.1.5
	if (version_compare(get_option('a3rev_wpsc_dgallery_version'), '1.1.5') === -1) {
		WPSC_Dynamic_Gallery_Functions::upgrade_1_1_5();
		
		update_option('a3rev_wpsc_dgallery_version', '1.1.5');
	}
	
	// Upgrade to 1.1.9
	if (version_compare(get_option('a3rev_wpsc_dgallery_version'), '1.1.9') === -1) {
		WPSC_Dynamic_Gallery_Functions::upgrade_1_1_9();
		
		update_option('a3rev_wpsc_dgallery_version', '1.1.9');
	}
	
	update_option('a3rev_wpsc_dgallery_version', '1.1.9.4');
	update_option('a3rev_wpsc_dgallery_lite_version', '1.1.9.4');

}

?>