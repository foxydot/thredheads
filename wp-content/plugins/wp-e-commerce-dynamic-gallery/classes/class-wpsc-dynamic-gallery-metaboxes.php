<?php
/**
 * WP e-Commerce Dynamic Gallery Metaboxes Class
 *
 * Class Function into WP e-Commerce plugin
 *
 * Table Of Contents
 *
 *
 * remove_wpsc_metaboxes()
 * wpsc_meta_boxes_image()
 * wpsc_product_image_box()
 * save_actived_d_gallery()
 */
class WPSC_Dynamic_Gallery_Metaboxes_Class
{
	public static function remove_wpsc_metaboxes(){
		global $post;
		if (is_admin()) :
			remove_meta_box('wpsc_product_image_forms', 'wpsc-product', 'normal');
		endif;
	}
	
	
	public static function wpsc_meta_boxes_image() {
		global $post;
		global $wpsc_dgallery_global_settings;
		
		$global_wpsc_dgallery_activate = $wpsc_dgallery_global_settings['dgallery_activate'];
		$actived_d_gallery = get_post_meta($post->ID, '_actived_d_gallery', true);
		if ( $actived_d_gallery == '' && $global_wpsc_dgallery_activate != 'no' ) {
			$actived_d_gallery = 1;
		}
		
		add_meta_box( 'wpsc_product_gallery_image_forms', '<label class="a3_actived_d_gallery" style="margin-right: 50px;"><input type="checkbox" '.checked( $actived_d_gallery, 1, false).' value="1" name="_actived_d_gallery" /> '.__('A3 Dynamic Image Gallery activated', 'wpsc_dgallery').'</label> <label class="a3_wpsc_dgallery_show_variation"><input disabled="disabled" type="checkbox" value="1" name="_show_variation" /> '.__('Product Variation Images activated', 'wpsc_dgallery').'</label>', array('WPSC_Dynamic_Gallery_Metaboxes_Class','wpsc_product_image_box'), 'wpsc-product', 'normal', 'high' );
	}
	
	public static function wpsc_product_image_box() {
		global $post, $thepostid;
	?>
    	<style>
		@media screen and ( max-width: 782px ) {
			.a3_actived_d_gallery {
				padding-bottom:5px;	
				display:inline-block;
			}
			.a3_wpsc_dgallery_show_variation {
				white-space:nowrap;
				padding-bottom:5px;
				display:inline-block;
			}
		}
		@media screen and ( max-width: 480px ) {
			.a3_wpsc_dgallery_show_variation {
				white-space:inherit;
			}
		}
        </style>
    <?php
		echo '<script type="text/javascript">
		jQuery(document).on("click", "#wpsc_product_gallery_image_forms h3", function(){
			jQuery("#wpsc_product_gallery_image_forms").removeClass("closed");
		});
		jQuery(document).on("click", ".upload_image_button", function(){
			var post_id = '.$post->ID.';
			//window.send_to_editor = window.send_to_termmeta;
			tb_show("", "media-upload.php?parent_page=wpsc-edit-products&post_id=" + post_id + "&type=image&tab=gallery&TB_iframe=true");
			return false;
		});
		
		</script>';
		echo '<a class="add-new-h2 a3-view-docs-button" style="background-color: #FFFFE0 !important; border: 1px solid #E6DB55 !important; text-shadow:none !important; font-weight:normal !important; margin: 5px 0 !important; display: inline-block !important;" target="_blank" href="'.WPSC_DYNAMIC_GALLERY_DOCS_URI.'#section-13" >'.__('View Docs', 'wpsc_dgallery').'</a>';
		echo '<div class="wpsc_options_panel">';
		$attached_images = (array) get_posts( array(
			'post_type'   => 'attachment',
			'post_mime_type' => 'image',
			'numberposts' => -1,
			'post_status' => null,
			'post_parent' => $post->ID ,
			'orderby'     => 'menu_order',
			'order'       => 'ASC',
		) );
		
		$featured_img = get_post_meta($post->ID, '_thumbnail_id');
		$attached_thumb = array();
		if ( count($attached_images) > 0 ) {
			$i = 0;
			foreach ( $attached_images as $key => $object ) {
				$i++;
				if ( in_array( $object->ID, $featured_img ) ) {
					$attached_thumb[0] = $object;
				} else {
					$attached_thumb[$i] = $object;
				}
			}			
		}
		ksort($attached_thumb);
		
		if ( is_array($attached_thumb) && count($attached_thumb) > 0 ) {
	
			echo '<a href="#" onclick="tb_show(\'\', \'media-upload.php?parent_page=wpsc-edit-products&post_id='.$post->ID.'&type=image&TB_iframe=true\');return false;" style="margin-right:10px;margin-bottom:10px;" class="upload_image_button1" rel="'.$post->ID.'"><img src="'.WPSC_DYNAMIC_GALLERY_JS_URL.'/mygallery/no-image.jpg" style="width:69px;height:69px;border:2px solid #CCC" /><input type="hidden" name="upload_image_id[1]" class="upload_image_id" value="0" /></a>';
			
			$i = 0 ;
			foreach ( $attached_thumb as $item_thumb ) {
				$i++;
				if ( get_post_meta( $item_thumb->ID, '_wpsc_exclude_image', true ) == 1 ) continue;
				$image_attribute = wp_get_attachment_image_src( $item_thumb->ID, array( 70 , 70) );
				echo '<a href="#" style="margin-right:10px;margin-bottom:10px;" class="upload_image_button" rel="'.$post->ID.'"><img src="'.$image_attribute[0].'" style="width:69px;height:69px;border:2px solid #CCC" /><input type="hidden" name="upload_image_id['.$i.']" class="upload_image_id" value="'.$item_thumb->ID.'" /></a>';
			}
		} else {
			echo '<a href="#" class="upload_image_button" rel="'.$post->ID.'"><img src="'.WPSC_DYNAMIC_GALLERY_JS_URL.'/mygallery/no-image.jpg" style="width:69px;height:69px;border:2px solid #CCC" /><input type="hidden" name="upload_image_id[1]" class="upload_image_id" value="0" /></a>';
		}
		
		echo '</div>';			
	}
	
	public static function save_actived_d_gallery( $post_id ) {
		global $post;
		$post_status = get_post_status($post_id);
		$post_type = get_post_type($post_id);
		if ( empty($post_id) || empty($post) || empty($_POST) ) return;
		if ( defined('DOING_AUTOSAVE') && DOING_AUTOSAVE ) return;
		if ( is_int( wp_is_post_revision( $post ) ) ) return;
		if ( is_int( wp_is_post_autosave( $post ) ) ) return;
		if ( !current_user_can( 'edit_post', $post_id )) return;
		if ( $post->post_type != 'wpsc-product' || $post_status == false  || $post_status == 'inherit' ) return;
		
		if ( isset($_REQUEST['_actived_d_gallery']) ) {
			update_post_meta($post_id, '_actived_d_gallery', 1); 
		} else {
			update_post_meta($post_id, '_actived_d_gallery', 0); 
		}
	}
}

add_action( 'admin_head', array('WPSC_Dynamic_Gallery_Metaboxes_Class','remove_wpsc_metaboxes') );
add_action( 'add_meta_boxes', array('WPSC_Dynamic_Gallery_Metaboxes_Class','wpsc_meta_boxes_image'), 9);
if (in_array( basename( $_SERVER['PHP_SELF']), array('post.php', 'page.php', 'page-new.php', 'post-new.php') ) ) {
	add_action( 'save_post', array('WPSC_Dynamic_Gallery_Metaboxes_Class','save_actived_d_gallery') );
}
?>
