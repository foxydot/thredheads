<?php
/**
 * WP e-Commerce Dynamic Gallery Display Class
 *
 * Class Function into WP e-Commerce plugin
 *
 * Table Of Contents
 *
 * wpsc_dynamic_gallery_display()
 */
class WPSC_Dynamic_Gallery_Display_Class
{
	public static function wpsc_dynamic_gallery_display( $product_id = 0 ) {
		/**
		 * Single Product Image
		 */
		global $post;
		global $wpsc_dgallery_global_settings, $wpsc_dgallery_style_setting, $wpsc_dgallery_thumbnail_settings, $wpsc_dgallery_fonts_face;
		
		if ( $product_id <= 0 ) {
			$product_id = $post->ID;
		}
		$lightbox_class = 'lightbox';
		
		// Get all attached images to this product
		
		$featured_img = get_post_meta( $product_id, '_thumbnail_id' );
		$attached_images = (array) get_posts( array(
			'post_type'   => 'attachment',
			'post_mime_type' => 'image',
			'numberposts' => -1,
			'post_status' => null,
			'post_parent' => $product_id ,
			'orderby'     => 'menu_order',
			'order'       => 'ASC',
		) );
		
		$attached_thumb = array();
		if ( count($attached_images) > 0 ) {
			$i = 0;
			foreach ( $attached_images as $key => $object ) {
				if ( get_post_meta( $object->ID, '_wpsc_exclude_image', true ) == 1) continue;
				$i++;
				if ( in_array( $object->ID, $featured_img ) ) {
					$attached_thumb[0] = $object;
				} else {
					$attached_thumb[$i] = $object;
				}
			}	
		}
		ksort($attached_thumb);
		$product_id .= '_'.rand(100,10000);
		
		//Gallery settings
		$g_width = $wpsc_dgallery_style_setting['product_gallery_width_fixed'];
        $g_height = $wpsc_dgallery_style_setting['product_gallery_height'];
		$g_auto = $wpsc_dgallery_style_setting['product_gallery_auto_start'];
        $g_speed = $wpsc_dgallery_style_setting['product_gallery_speed'];
        $g_effect = $wpsc_dgallery_style_setting['product_gallery_effect'];
        $g_animation_speed = $wpsc_dgallery_style_setting['product_gallery_animation_speed'];
		$bg_image_wrapper = $wpsc_dgallery_style_setting['bg_image_wrapper'];
		$border_image_wrapper_color = $wpsc_dgallery_style_setting['border_image_wrapper_color'];
		$popup_gallery = $wpsc_dgallery_global_settings['popup_gallery'];
		
		//Caption text settings
		$caption_font = $wpsc_dgallery_style_setting['caption_font'];
		$product_gallery_bg_des = $wpsc_dgallery_style_setting['product_gallery_bg_des'];
		$bg_des = WPSC_Dynamic_Gallery_Functions::html2rgb($product_gallery_bg_des,true);
		$des_background =str_replace('#','',$product_gallery_bg_des);
		
		//Nav bar settings
		if( $wpsc_dgallery_style_setting['product_gallery_nav'] == 'yes') {
			$product_gallery_nav = $wpsc_dgallery_style_setting['product_gallery_nav'];
		} else {
			$product_gallery_nav = 'no';
		}
		$navbar_font = $wpsc_dgallery_style_setting['navbar_font'];
		$bg_nav_color = $wpsc_dgallery_style_setting['bg_nav_color'];
		$navbar_height = $wpsc_dgallery_style_setting['navbar_height'];
		if ( $product_gallery_nav == 'yes' ) {
			$display_ctrl = 'display:block !important;';
			$mg = $navbar_height;
			$ldm = $navbar_height;		
		} else {
			$display_ctrl = 'display:none !important;';
			$mg = '0';
			$ldm = '0';
		}
			
		//Lazy-load scroll settings
		$transition_scroll_bar = $wpsc_dgallery_style_setting['transition_scroll_bar'];
		$lazy_load_scroll = $wpsc_dgallery_style_setting['lazy_load_scroll'];
		
		//Image Thumbnails settings
		if ( $wpsc_dgallery_thumbnail_settings['enable_gallery_thumb'] == 'yes') {
			$enable_gallery_thumb = $wpsc_dgallery_thumbnail_settings['enable_gallery_thumb'];
		} else {
			$enable_gallery_thumb = 'no';
		}
        $g_thumb_width = $wpsc_dgallery_thumbnail_settings['thumb_width'];
		if ( $g_thumb_width <= 0 ) $g_thumb_width = 105;
        $g_thumb_height = $wpsc_dgallery_thumbnail_settings['thumb_height'];
		if ( $g_thumb_height <= 0 ) $g_thumb_height = 75;
        $g_thumb_spacing = $wpsc_dgallery_thumbnail_settings['thumb_spacing'];
		
		$html = '';
		
        $html .= '<div class="images">
          <div class="product_gallery">';
                
            $html .=  '<style>
				.single_product_display .imagecol { max-width:100%; }
				.single_product_display .imagecol > a { display: none !important;position: absolute;z-index: -1;left:-1000em;}
				#TB_window{width:auto !important;}
                .ad-gallery {
                        width: '.$g_width.'px;
						position:relative;
                }
                .ad-gallery .ad-image-wrapper {
					background:'.$bg_image_wrapper.';
                    width: '.($g_width-2).'px;
                    height: '.($g_height-2).'px;
                    margin: 0px;
                    position: relative;
                    overflow: hidden !important;
                    padding:0;
                    border:1px solid #'.$border_image_wrapper_color.';
					z-index:8 !important;
                }
				.ad-gallery .ad-image-wrapper .ad-image{width:100% !important;text-align:center;}
                .ad-image img{
                    max-width:'.$g_width.'px !important;
                }
                .ad-gallery .ad-thumbs li{
                    padding-right: '.$g_thumb_spacing.'px !important;
                }
                .ad-gallery .ad-thumbs li.last_item{
                    padding-right: '.($g_thumb_spacing+13).'px !important;
                }
                .ad-gallery .ad-thumbs li div{
                    height: '.$g_thumb_height.'px !important;
                    width: '.$g_thumb_width.'px !important;
                }
                .ad-gallery .ad-thumbs li a {
                    width: '.$g_thumb_width.'px !important;
                    height: '.$g_thumb_height.'px !important;	
                }
                * html .ad-gallery .ad-forward, .ad-gallery .ad-back{
                    height:	'.($g_thumb_height).'px !important;
                }
				
				/*Gallery*/
				.ad-image-wrapper{
					overflow:inherit !important;
				}
				
				.ad-gallery .ad-controls {
					background: '.$bg_nav_color.' !important;
					border:1px solid '.$bg_nav_color.';
					color: #FFFFFF;
					font-size: 12px;
					height: 22px;
					margin-top: 20px !important;
					padding: 8px 2% !important;
					position: relative;
					width: 95.8%;
					-khtml-border-radius:5px;
					-webkit-border-radius: 5px;
					-moz-border-radius: 5px;
					border-radius: 5px;display:none;
				}
				
				.ad-gallery .ad-info {
					float: right;
					font-size: 14px;
					position: relative;
					right: 8px;
					text-shadow: 1px 1px 1px #000000 !important;
					top: 1px !important;
				}
				.ad-gallery .ad-nav .ad-thumbs{
					margin:7px 4% 0 !important;
				}
				.ad-gallery .ad-thumbs .ad-thumb-list {
					margin-top: 0px !important;
				}
				.ad-thumb-list{
				}
				.ad-thumb-list li{
					background:none !important;
					padding-bottom:0 !important;
					padding-left:0 !important;
					padding-top:0 !important;
				}
				.ad-gallery .ad-image-wrapper .ad-image-description {
					background: rgba('.$bg_des.',0.5);
					filter:progid:DXImageTransform.Microsoft.Gradient(GradientType=1, StartColorStr="#88'.$des_background.'", EndColorStr="#88'.$des_background.'");

					margin: 0 0 '.$mg.'px !important;';
					
					$html .= $wpsc_dgallery_fonts_face->generate_font_css( $caption_font );
					
					$html .=  '
					left: 0;
					line-height: 1.4em;
					padding:2% 2% 2% !important;
					position: absolute;
					text-align: left;
					width: 96.1% !important;
					z-index: 10;
					font-weight:normal;
				}
				.product_gallery .ad-gallery .ad-image-wrapper {
					background: none repeat scroll 0 0 '.$bg_image_wrapper.';
					border: 1px solid '.$border_image_wrapper_color.' !important;
					padding-bottom:'.$mg.'px;
				}
				.product_gallery .slide-ctrl, .product_gallery .icon_zoom {
					'.$display_ctrl.';
					height: '.($navbar_height-16).'px !important;
					line-height: '.($navbar_height-16).'px !important;';
					$html .= $wpsc_dgallery_fonts_face->generate_font_css( $navbar_font );
				$html .=  '
				}';
				if($lazy_load_scroll == 'yes'){
					$html .=  '.ad-gallery .lazy-load{
						background:'.$transition_scroll_bar.' !important;
						top:'.($g_height + 9).'px !important;
						opacity:1 !important;
						margin-top:'.$ldm.'px !important;
					}';
				}else{
					$html .=  '.ad-gallery .lazy-load{display:none!important;}';
				}
				$html .= '
				.product_gallery .icon_zoom {
					background: '.$bg_nav_color.';
					border-right: 1px solid '.$bg_nav_color.';
					border-top: 1px solid '.$border_image_wrapper_color.';
				}
				.product_gallery .slide-ctrl {
					background:'.$bg_nav_color.';
					border-left: 1px solid '.$border_image_wrapper_color.';
					border-top: 1px solid '.$border_image_wrapper_color.';
				}
				.product_gallery .slide-ctrl .ad-slideshow-stop-slide,.product_gallery .slide-ctrl .ad-slideshow-start-slide,.product_gallery .icon_zoom{
					line-height: '.($navbar_height-16).'px !important;';
					$html .= $wpsc_dgallery_fonts_face->generate_font_css( $navbar_font );
				$html .= '
				}
				.product_gallery .ad-gallery .ad-thumbs li a {
					border:1px solid '.$border_image_wrapper_color.' !important;
				}
				.ad-gallery .ad-thumbs li a.ad-active {
					border: 1px solid '.$transition_scroll_bar.' !important;
				}';
			if($enable_gallery_thumb == 'no'){
				$html .=  '.ad-nav{visibility:hidden !important; height: 0 !important;}.woocommerce .images { margin-bottom: 15px;}';
			}	
			
			if($product_gallery_nav == 'no'){
				$html .=  '
				.ad-image-wrapper:hover .slide-ctrl{display: block !important;}
				.product_gallery .slide-ctrl {
					background: none repeat scroll 0 0 transparent;
					border: medium none;
					height: 50px !important;
					left: 41.5% !important;
					top: 38% !important;
					width: 50px !important;
				}';
				$html .=  '.product_gallery .slide-ctrl .ad-slideshow-start-slide {background: url('.WPSC_DYNAMIC_GALLERY_JS_URL.'/mygallery/play.png) !important;height: 50px !important;text-indent: -999em !important; width: 50px !important;}';
				$html .=  '.product_gallery .slide-ctrl .ad-slideshow-stop-slide {background: url('.WPSC_DYNAMIC_GALLERY_JS_URL.'/mygallery/pause.png) !important;height: 50px !important;text-indent: -999em !important; width: 50px !important;}';
			}
			
			$html .=  '
            </style>';
            
            $html .=  '<script type="text/javascript">
                jQuery(function() {
                    var settings_defaults_'.$product_id.' = { loader_image: "'.WPSC_DYNAMIC_GALLERY_JS_URL.'/mygallery/loader.gif",
                        start_at_index: 0,
                        gallery_ID: "'.$product_id.'",
						lightbox_class: "'.$lightbox_class.'",
                        description_wrapper: false,
                        thumb_opacity: 0.5,
                        animate_first_image: false,
                        animation_speed: '.$g_animation_speed.'000,
                        width: false,
                        height: false,
                        display_next_and_prev: true,
                        display_back_and_forward: true,
                        scroll_jump: 0,
                        slideshow: {
                            enable: true,
                            autostart: '.$g_auto.',
                            speed: '.$g_speed.'000,
                            start_label: "'.__('START SLIDESHOW', 'wpsc_dgallery').'",
                            stop_label: "'.__('STOP SLIDESHOW', 'wpsc_dgallery').'",
							zoom_label: "'.__('ZOOM +', 'wpsc_dgallery').'",
                            stop_on_scroll: true,
                            countdown_prefix: "(",
                            countdown_sufix: ")",
                            onStart: false,
                            onStop: false
                        },
                        effect: "'.$g_effect.'", 
                        enable_keyboard_move: true,
                        cycle: true,
                        callbacks: {
                        init: false,
                        afterImageVisible: false,
                        beforeImageVisible: false
                    }
                };
                jQuery("#gallery_'.$product_id.'").adGallery(settings_defaults_'.$product_id.');
            });
            </script>';
            $html .=  '<div id="gallery_'.$product_id.'" class="ad-gallery" style="width: '.$g_width.'px;">
                <div class="ad-image-wrapper"></div>
                <div class="ad-controls"> </div>
                  <div class="ad-nav">
                    <div class="ad-thumbs">
                      <ul class="ad-thumb-list">';
                        
						$script_colorbox = '';
						$script_fancybox = '';
                        if ( is_array($attached_thumb) && count($attached_thumb) > 0 ) {	
                            $i = 0;
                            $display = '';
							
                                $script_colorbox .= '<script type="text/javascript">';
								$script_fancybox .= '<script type="text/javascript">';
                                $script_colorbox .= '(function($){';		  
								$script_fancybox .= '(function($){';		  
                                $script_colorbox .= '$(function(){';
								$script_fancybox .= '$(function(){';
                                $script_colorbox .= '$(document).on("click", ".ad-gallery .lightbox", function(ev) { if( $(this).attr("rel") == "gallery_'.$product_id.'") {
									var idx = $("#gallery_'.$product_id.' .ad-image img").attr("idx");';
								$script_fancybox .= '$(document).on("click", ".ad-gallery .lightbox", function(ev) { if( $(this).attr("rel") == "gallery_'.$product_id.'") {
									var idx = $("#gallery_'.$product_id.' .ad-image img").attr("idx");';
                                if (count($attached_thumb) <= 1 ) {
                                   $script_colorbox .= '$(".gallery_product_'.$product_id.'").colorbox({open:true, maxWidth:"100%", title: function() { return "&nbsp;";} });';
									$script_fancybox .= '$.fancybox(';
                                } else {
                                    $script_colorbox .= '$(".gallery_product_'.$product_id.'").colorbox({rel:"gallery_product_'.$product_id.'", maxWidth:"100%", title: function() { return "&nbsp;";} }); $(".gallery_product_'.$product_id.'_"+idx).colorbox({open:true, maxWidth:"100%", title: function() { return "&nbsp;";} });';
									$script_fancybox .= '$.fancybox([';
                                }
                                $common = '';
                                
								$idx = 0;
                                foreach ($attached_thumb as $item_thumb) {
									if ( get_post_meta( $item_thumb->ID, '_wpsc_exclude_image', true ) == 1 ) continue;
                                    $li_class = '';
                                    if ( $i == 0 ){ $li_class = 'first_item'; } elseif ( $i == count($attached_thumb)-1 ) { $li_class = 'last_item'; }
                                    $image_attribute = wp_get_attachment_image_src( $item_thumb->ID, 'full');
                                    $image_lager_default_url = $image_attribute[0];
									
									$image_thumb_attribute = wp_get_attachment_image_src( $item_thumb->ID, 'wpsc-dynamic-gallery-thumb');
                                    $image_thumb_default_url = $image_thumb_attribute[0];
									
                                    $thumb_height = $g_thumb_height;
                                    $thumb_width = $g_thumb_width;
                                    $width_old = $image_thumb_attribute[1];
                                    $height_old = $image_thumb_attribute[2];
									if ( $width_old > $g_thumb_width || $height_old > $g_thumb_height ){
                                        if ( $height_old > $g_thumb_height && $g_thumb_height > 0 ) {
                                            $factor = ($height_old / $g_thumb_height);
                                            $thumb_height = $g_thumb_height;
                                            $thumb_width = $width_old / $factor;
                                        }
                                        if ( $thumb_width > $g_thumb_width && $g_thumb_width > 0 ) {
                                            $factor = ($width_old / $g_thumb_width);
                                            $thumb_height = $height_old / $factor;
                                            $thumb_width = $g_thumb_width;
                                        } elseif ( $thumb_width == $g_thumb_width && $width_old > $g_thumb_width && $g_thumb_width > 0 ) {
                                            $factor = ($width_old / $g_thumb_width);
                                            $thumb_height = $height_old / $factor;
                                            $thumb_width = $g_thumb_width;
                                        }						
                                    } else {
										$thumb_height = $height_old;
                                        $thumb_width = $width_old;
                                    }
                                   $alt = get_post_meta($item_thumb->ID, '_wp_attachment_image_alt', true);
								   $img_description = $item_thumb->post_excerpt;
								   if ($img_description == '') {
									   $img_description = $alt;
								   }
                                            
                                    $html .=  '<li class="'.$li_class.'"><a alt="'.$alt.'" class="gallery_product_'.$product_id.' gallery_product_'.$product_id.'_'.$idx.'"  title="'. esc_attr( $img_description ) .'" rel="gallery_product_'.$product_id.'" href="'.$image_lager_default_url.'"><div><img idx="'.$idx.'" style="width:'.$thumb_width.'px !important;height:'.$thumb_height.'px !important" src="'.$image_thumb_default_url.'" alt="'. esc_attr( $img_description ) .'" class="image'.$i.'" width="'.$thumb_width.'" height="'.$thumb_height.'"></div></a></li>';
                                    $img_description =  esc_js( $img_description ) ;
                                    if ( $img_description != '' ) {
										$script_fancybox .= $common.'{href:"'.$image_lager_default_url.'",title:"'.$img_description.'"}';
                                    } else {
										$script_fancybox .= $common.'{href:"'.$image_lager_default_url.'",title:""}';
                                    }
                                    $common = ',';
                                    $i++;
									$idx++;
								}
								 //$.fancybox([ {href : 'img1.jpg', title : 'Title'}, {href : 'img2.jpg', title : 'Title'} ])
                                if ( count($attached_thumb) <= 1 ) {
									$script_fancybox .= ');';
                                } else {
									$script_fancybox .= '],{
										 \'index\': idx
      								});';
                                }
                                $script_colorbox .= 'ev.preventDefault();';
                                $script_colorbox .= '} });';
								$script_fancybox .= '} });';
                                $script_colorbox .= '});';
								$script_fancybox .= '});';
                                $script_colorbox .= '})(jQuery);';
								$script_fancybox .= '})(jQuery);';
                                $script_colorbox .= '</script>';
								$script_fancybox .= '</script>';
                        } else {
                            $html .=  '<li style="width:'.$g_thumb_width.'px;height:'.$g_thumb_height.'px;"> <a style="width:'.($g_thumb_width-2).'px !important;height:'.($g_thumb_height - 2).'px !important;overflow:hidden;float:left !important" class="" rel="gallery_product_'.$product_id.'" href="'.WPSC_DYNAMIC_GALLERY_JS_URL . '/mygallery/no-image.png"> <div><img style="width:'.$g_thumb_width.'px;height:'.$g_thumb_height.'px;" src="'.WPSC_DYNAMIC_GALLERY_JS_URL . '/mygallery/no-image.png" class="image" alt=""> </div></a> </li>';	
                        }
						if ( $popup_gallery == 'colorbox' ) {
                        	$html .=  $script_colorbox;
						} else {
							$html .=  $script_fancybox;
						}
                        $html .=  '</ul>
                        </div>
                      </div>
                    </div>
          		</div>
        </div>';
		
		return $html;
	}
}
?>