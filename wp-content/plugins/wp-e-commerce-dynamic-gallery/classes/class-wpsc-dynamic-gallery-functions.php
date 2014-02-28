<?php
/**
 * WPEC Dynamic Gallery Functions
 *
 * Table Of Contents
 *
 * reset_products_galleries_activate()
 * html2rgb()
 * get_font()
 * get_font_sizes()
 * plugin_pro_notice()
 * upgrade_1_1_4()
 */
class WPSC_Dynamic_Gallery_Functions 
{
	
	public static function reset_products_galleries_activate() {
		global $wpdb;
		$wpdb->query( "DELETE FROM ".$wpdb->postmeta." WHERE meta_key='_actived_d_gallery' " );
	}
	
	public static function html2rgb($color,$text = false){
		if ($color[0] == '#')
			$color = substr($color, 1);
	
		if (strlen($color) == 6)
			list($r, $g, $b) = array($color[0].$color[1],
									 $color[2].$color[3],
									 $color[4].$color[5]);
		elseif (strlen($color) == 3)
			list($r, $g, $b) = array($color[0].$color[0], $color[1].$color[1], $color[2].$color[2]);
		else
			return false;
	
		$r = hexdec($r); $g = hexdec($g); $b = hexdec($b);
		if($text){
			return $r.','.$g.','.$b;
		}else{
			return array($r, $g, $b);
		}
	}	
	
	public static function plugin_extension() {
		$html = '';
		$html .= '<a href="http://a3rev.com/shop/" target="_blank" style="float:right;margin-top:5px; margin-left:10px;" ><div class="a3-plugin-ui-icon a3-plugin-ui-a3-rev-logo"></div></a>';
		$html .= '<h3>'.__('Upgrade available for WPEC Dynamic Gallery Pro', 'wpsc_dgallery').'</h3>';
		$html .= '<p>'.__("<strong>NOTE:</strong> All the functions inside the Yellow border on the plugins admin panel are extra functionality that is activated by upgrading to the Pro version", 'wpsc_dgallery').':</p>';
		$html .= '<h3>* <a href="http://a3rev.com/shop/wp-e-commerce-dynamic-gallery/" target="_blank">'.__('WPEC Dynamic Gallery Pro', 'wpsc_dgallery').'</a> '.__('Features', 'wpsc_dgallery').':</h3>';
		$html .= '<p>';
		$html .= '<ul style="padding-left:10px;">';
		$html .= '<li>1. '.__('Show Multiple Product Variation images in Gallery. As users selects options from the drop down menu that options product image auto shows in the Dynamic Gallery complete with caption text.', 'wpsc_dgallery').'</li>';
		$html .= '<li>2. '.__('Fully Responsive Gallery option. Set gallery wide to % and it becomes fully responsive image product gallery including the image zoom pop up.', 'wpsc_dgallery').'</li>';
		$html .= '<li>3. '.__('Activate all of the Gallery customization settings you see here on this page to style and fine tune your product presentation.', 'wpsc_dgallery').'</li>';
		$html .= '<li>4. '.__('Option to Deactivate the Gallery on any Single product page - default WP e-Commerce product image will show.', 'wpsc_dgallery').'</li>';
		$html .= '</ul>';
		$html .= '</p>';
		$html .= '<h3>'.__('View this plugins', 'wpsc_dgallery').' <a href="http://docs.a3rev.com/user-guides/wp-e-commerce/wpec-dynamic-gallery/" target="_blank">'.__('documentation', 'wpsc_dgallery').'</a></h3>';
		$html .= '<h3>'.__('Visit this plugins', 'wpsc_dgallery').' <a href="http://wordpress.org/support/plugin/wp-e-commerce-dynamic-gallery/" target="_blank">'.__('support forum', 'wpsc_dgallery').'</a></h3>';
		$html .= '<h3>'.__('More FREE a3rev WP e-Commerce Plugins', 'wpsc_dgallery').'</h3>';
		$html .= '<p>';
		$html .= '<ul style="padding-left:10px;">';
		$html .= '<li>* <a href="http://wordpress.org/plugins/wp-e-commerce-products-quick-view/" target="_blank">'.__('WP e-Commerce Products Quick View', 'wpsc_dgallery').'</a></li>';
		$html .= '<li>* <a href="http://wordpress.org/plugins/wp-e-commerce-predictive-search/" target="_blank">'.__('WP e-Commerce Predictive Search', 'wpsc_dgallery').'</a></li>';
		$html .= '<li>* <a href="http://wordpress.org/plugins/wp-ecommerce-compare-products/" target="_blank">'.__('WP e-Commerce Compare Products', 'wpsc_dgallery').'</a></li>';
		$html .= '<li>* <a href="http://wordpress.org/plugins/wp-e-commerce-catalog-visibility-and-email-inquiry/" target="_blank">'.__('WP e-Commerce Catalog Visibility & Email Inquiry', 'wpsc_dgallery').'</a></li>';
		$html .= '<li>* <a href="http://wordpress.org/plugins/wp-e-commerce-grid-view/" target="_blank">'.__('WP e-Commerce Grid View', 'wpsc_dgallery').'</a></li>';
		$html .= '</ul>';
		$html .= '</p>';
		
		$html .= '<h3>'.__('FREE a3rev WordPress plugins', 'wpsc_dgallery').'</h3>';
		$html .= '<p>';
		$html .= '<ul style="padding-left:10px;">';
		$html .= '<li>* <a href="http://wordpress.org/plugins/contact-us-page-contact-people/" target="_blank">'.__('Contact Us Page - Contact People', 'wpsc_dgallery').'</a></li>';
		$html .= '<li>* <a href="http://wordpress.org/plugins/wp-email-template/" target="_blank">'.__('WordPress Email Template', 'wpsc_dgallery').'</a></li>';
		$html .= '<li>* <a href="http://wordpress.org/plugins/page-views-count/" target="_blank">'.__('Page View Count', 'wpsc_dgallery').'</a></li>';
		$html .= '</ul>';
		$html .= '</p>';
		
		$html .= '<h3>'.__('Help spread the Word about this plugin', 'wpsc_dgallery').'</h3>';
		$html .= '<p>'.__("Things you can do to help others find this plugin", 'wpsc_dgallery');
		$html .= '<ul style="padding-left:10px;">';
		$html .= '<li>* <a href="http://wordpress.org/plugins/wp-e-commerce-dynamic-gallery/" target="_blank">'.__('Rate this plugin 5', 'wpsc_dgallery').' <img src="'.WPSC_DYNAMIC_GALLERY_IMAGES_URL.'/stars.png" align="top" style="width:auto !important; height:auto !important" /> '.__('on WordPress.org', 'wpsc_dgallery').'</a></li>';
		$html .= '<li>* <a href="http://wordpress.org/plugins/wp-e-commerce-dynamic-gallery/" target="_blank">'.__('Mark the plugin as a fourite', 'wpsc_dgallery').'</a></li>';
		$html .= '<li>* <a href="http://www.facebook.com/a3revolution/" target="_blank">'.__('Follow a3rev on facebook', 'wpsc_dgallery').'</a></li>';
		$html .= '<li>* <a href="https://twitter.com/a3rev/" target="_blank">'.__('Follow a3rev on Twitter', 'wpsc_dgallery').'</a></li>';
		$html .= '</ul>';
		$html .= '</p>';
		return $html;
	}
	
	public static function upgrade_1_1_4() {
		$default_settings = array(
			'product_gallery_width'					=> get_option('product_gallery_width', 100),
			'width_type'							=> get_option('width_type', '%'),
			'product_gallery_height'				=> get_option('product_gallery_height', 75),
			'product_gallery_auto_start'			=> get_option('product_gallery_auto_start', 'true'),
			'product_gallery_speed'					=> get_option('product_gallery_speed', 5),
			'product_gallery_effect'				=> get_option('product_gallery_effect', 'slide-vert'),
			'product_gallery_animation_speed'		=> get_option('product_gallery_animation_speed', 2),
			'stop_scroll_1image'					=> get_option('dynamic_gallery_stop_scroll_1image', 'no'),
			'bg_image_wrapper'						=> get_option('bg_image_wrapper', '#FFFFFF'),
			'border_image_wrapper_color'			=> get_option('border_image_wrapper_color', '#CCCCCC'),
		);
		update_option( 'wpsc_dgallery_container_settings', $default_settings );
		
		$default_settings = array(
			'popup_gallery'							=> get_option('popup_gallery', 'fb'),
			'dgallery_activate'						=> 'yes',
			'dgallery_show_variation'				=> get_option('dynamic_gallery_show_variation', 'yes'),
		);
		update_option( 'wpsc_dgallery_global_settings', $default_settings );
		
		$default_settings = array(
			'caption_font'							=> get_option('caption_font', 'Arial, sans-serif'),
			'caption_font_size'						=> get_option('caption_font_size', '12px'),
			'caption_font_style'					=> get_option('caption_font_style', 'normal'),
			'product_gallery_text_color'			=> get_option('product_gallery_text_color', '#FFFFFF'),
			'product_gallery_bg_des'				=> get_option('product_gallery_bg_des', '#000000'),
		);
		update_option( 'wpsc_dgallery_caption_settings', $default_settings );
		
		$default_settings = array(
			'product_gallery_nav'					=> get_option('product_gallery_nav', 'yes'),
			'navbar_font'							=> get_option('navbar_font', 'Arial, sans-serif'),
			'navbar_font_size'						=> get_option('navbar_font_size', '12px'),
			'navbar_font_style'						=> get_option('navbar_font_style', 'bold'),
			'bg_nav_text_color'						=> get_option('bg_nav_text_color', '#000000'),
			'bg_nav_color'							=> get_option('bg_nav_color', '#FFFFFF'),
			'navbar_height'							=> get_option('navbar_height', 25),
		);
		update_option( 'wpsc_dgallery_navbar_settings', $default_settings );
		
		$default_settings = array(
			'lazy_load_scroll'						=> get_option('lazy_load_scroll', 'yes'),
			'transition_scroll_bar'					=> get_option('transition_scroll_bar', '#000000'),
		);
		update_option( 'wpsc_dgallery_lazyload_settings', $default_settings );
		
		$default_settings = array(
			'enable_gallery_thumb'					=> get_option('enable_gallery_thumb', 'yes'),
			'hide_thumb_1image'						=> get_option('dynamic_gallery_hide_thumb_1image', 'no'),
			'thumb_width'							=> get_option('thumb_width', 105),
			'thumb_height'							=> get_option('thumb_height', 75),
			'thumb_spacing'							=> get_option('thumb_spacing', 2),
		);
		update_option( 'wpsc_dgallery_thumbnail_settings', $default_settings );
		
		
		global $wpdb;
		$wpdb->query( "UPDATE ".$wpdb->postmeta." SET meta_key='_wpsc_dgallery_show_variation' WHERE meta_key='_show_variation' " );
		$wpdb->query( "UPDATE ".$wpdb->postmeta." SET meta_key='_wpsc_dgallery_in_variations' WHERE meta_key='_in_variations' " );
	}
	
	public static function upgrade_1_1_5() {
		$wpsc_dgallery_global_settings = get_option('wpsc_dgallery_global_settings', array() );
		if ( isset( $wpsc_dgallery_global_settings['popup_gallery'] ) && $wpsc_dgallery_global_settings['popup_gallery'] == 'lb' ) {
			$wpsc_dgallery_global_settings['popup_gallery'] = 'colorbox';
			update_option('wpsc_dgallery_global_settings', $wpsc_dgallery_global_settings );	
		}
	}
	
	public static function upgrade_1_1_9() {
		$wpsc_dgallery_container_settings = get_option( 'wpsc_dgallery_container_settings' );
		
		$wpsc_dgallery_style_setting = array();
		$wpsc_dgallery_style_setting = array_merge( $wpsc_dgallery_style_setting, $wpsc_dgallery_container_settings );
		$wpsc_dgallery_style_setting['product_gallery_width_responsive'] = trim( $wpsc_dgallery_container_settings['product_gallery_width'] );
		$wpsc_dgallery_style_setting['product_gallery_width_fixed'] = trim( $wpsc_dgallery_container_settings['product_gallery_width'] );
		
		update_option( 'wpsc_dgallery_style_setting', $wpsc_dgallery_style_setting );
		
	}
}

?>