<?php
/**
 * WPSC Dynamic Gallery Variations Class
 *
 *
 * Table Of Contents
 *
 * media_fields()
 */
class WPSC_Dynamic_Gallery_Variations
{
	
	public static function media_fields( $form_fields, $attachment ) {
		
		if ( !isset( $_GET['post_id'] ) ) return $form_fields;
		$product_id = $_GET['post_id'];
	
		if (!$attachment->post_parent) return $form_fields;
		$parent = get_post( $attachment->post_parent );
		if ($parent->post_type!=='wpsc-product') return $form_fields;
				
		$product_id = $parent->ID;
		
		if ( isset($_GET['tab']) && $_GET['tab'] == 'gallery' && get_post_type($product_id) == 'wpsc-product' && class_exists('wpsc_variations') && wp_attachment_is_image($attachment->ID) ) {
			global $wpsc_variations;
			$wpsc_variations = new wpsc_variations( $product_id );
												
			if ( wpsc_have_variation_groups() ) {
				$form_fields['start_variation'] = array(
						'label' => __('Variations', 'wpsc_dgallery'),
						'input' => 'html',
						'html' => '<style>.start_variation {border-width:2px 2px 0} .end_variation {border-width:0 2px 2px} .start_variation, .end_variation {border-style:solid ;border-color:#E6DB55;-webkit-border-radius:10px;-moz-border-radius:10px;-o-border-radius:10px; border-radius: 10px;position:relative;}</style>',
						'value' => '',
						'helps'	=> __('Upgrade to the PRO version to use this feature.', 'wpsc_dgallery'),
					);
				while ( wpsc_have_variation_groups() ) : wpsc_the_variation_group();
					
					$html = "<style>.in_variations_".wpsc_vargrp_id()." {border-width:0 2px;border-style:solid ;border-color:#E6DB55;}</style>";
					
					$html .= "<input disabled='disabled' type='checkbox' id='".$attachment->ID."_".wpsc_vargrp_id()."' name='".$attachment->ID."_".wpsc_vargrp_id()."' value=''> <label for='".$attachment->ID."_".wpsc_vargrp_id()."'><strong>".__('Apply to All', 'wpsc_dgallery')."</strong></label><br />";
					
					while ( wpsc_have_variations() ): wpsc_the_variation();
						if (wpsc_the_variation_id() > 0 ) {
							$html .= "&nbsp;- &nbsp; <input disabled='disabled' type='checkbox' id='".$attachment->ID."_".wpsc_vargrp_id()."_".wpsc_the_variation_id()."'> <label for='".$attachment->ID."_".wpsc_vargrp_id()."_".wpsc_the_variation_id()."'>".esc_html( wpsc_the_variation_name() )."</label><br />";
						}
					endwhile;
						
					$form_fields['in_variations_'.wpsc_vargrp_id()] = array(
						'label' => esc_html( wpsc_the_vargrp_name() ),
						'input' => 'html',
						'html' => $html,
						'value' => ''
					);
					
				endwhile;
				$form_fields['end_variation'] = array(
						'label' => '',
						'input' => 'html',
						'html' => '&nbsp;',
						'value' => ''
					);
			}
			
		}
	
		return $form_fields;
	}
}
?>