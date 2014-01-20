<?php
/*
 * Plugin Name: GC Testimonials
 * Description: This plugin will help you to collect and show testimonials
 * Author: Erin Garscadden
 * Version: 1.3.2
 * Requires: 3.0 or higher
 *  
 *  
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 * 
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

define('GCT_NAME', 'gc-testimonials');
define('GCT_TAXONOMY', 'testimonial-category');
define('GCT_POST_TYPE', 'testimonial');

$url = plugins_url().'/'.GCT_NAME;
define('GCT_URL', $url);

//=============TRANSLATE===================================

function start_plugin_textdomain() {
    load_plugin_textdomain('gc-testimonials', FALSE, dirname(plugin_basename(__FILE__)).'/languages/');
}
add_action('plugins_loaded', 'start_plugin_textdomain');

/**********************************************************
* Register Scripts/CSS
**********************************************************/

function gct_testimominal_custom_scripts() { 
	
	global $post; 
	
	if ( strstr($post->post_content, '[single-testimonial') || 
		 strstr($post->post_content, '[random-testimonial') || 
		 strstr($post->post_content, '[full-testimonials') || 
		 strstr($post->post_content, '[testimonial-form')) {
			wp_enqueue_style('gctstyles', plugins_url('/assets/css/gctestimonial.css', __FILE__ ), false, '1.0', 'all');
	}
	
	if ( strstr($post->post_content, '[full-testimonials') ) {
		add_action('wp_footer', 'gct_custom_pagination');
		wp_enqueue_script('gct-pager', plugins_url('/assets/js/quickpager.jquery.js', __FILE__ ), array('jquery'), '1.0', true);
	}
	
	if ( strstr($post->post_content, '[testimonial-form') ) {
		add_action('wp_footer', 'gct_custom_validate');
		wp_enqueue_script('gct-validation', plugins_url('/assets/js/jquery.validate.min.js', __FILE__ ), array('jquery'));
	}
}

add_action( 'wp_enqueue_scripts', 'gct_testimominal_custom_scripts');

function gct_custom_validate() {
	$output = '<script type="text/javascript">jQuery(document).ready(function($) { $("#create_testimonial_form").validate(); });</script>';
	echo $output;	
}

function gct_custom_pagination() {
	$page = get_option('att_pagination_no');
	if($page != '') { $no = $page; } else { $no = '5'; }
	$output = '<script type="text/javascript">';
	$output .= 'jQuery(document).ready(function($) {';
	$output .= '$("#testimonials_container").quickPager({ pageSize: '.$no.', currentPage: 1, pagerLocation: "after" });';
	$output .= '});';
	$output .= '</script>';
	echo $output;		
}

/* Widget */

function gct_widget_script() {
	wp_enqueue_script('gct-slider', plugins_url('/assets/js/jquery.cycle.all.js', __FILE__ ), array('jquery'));
}

function gct_widget_code() {
	$output = '<script type="text/javascript">jQuery(document).ready(function($) { $("#tcycle").cycle({ fx: "fade", speed: 1500, timeout: 8000, pause: 1}); });</script>';
	echo $output;		
}

/* Admin Scripts */

function kcvt_admin_scripts_init() {
    wp_enqueue_style('kc-testimonial-admin', plugins_url('/assets/css/gctestimonial-admin.css', __FILE__ ), false, '1.0', 'all');
    wp_enqueue_script('kc-testimonial-admin', plugins_url('/assets/js/gctestimonial-admin.js', __FILE__), array('jquery'));
    wp_enqueue_script('jquery');
	wp_enqueue_script('jquery-ui-core');
	wp_enqueue_script('jquery-ui-tabs');
}
add_action( 'admin_init', 'kcvt_admin_scripts_init' );

/* Helper functions */

function gct_get_website($website) {
	if (!preg_match("~^(?:f|ht)tps?://~i", $website)) {
        $url = "http://" . $website;
    } else {
    	$url = $website;	
    }
	return $url;
}	

/**********************************************************
* Register Post Type and Taxonomy
**********************************************************/
	
add_action('init', 'gct_testimonial_register');

function gct_testimonial_register() {
	$testimonial_labels = array(
		'name'                  => _x('Testimonials', 'post type general name', GCT_NAME),
		'singular_name'         => _x('Testimonial', 'post type singular name', GCT_NAME),
		'add_new'               => __('Add New', GCT_NAME),
		'add_new_item'          => __('Add New Testimonial', GCT_NAME),
		'edit_item'             => __('Edit Testimonial', GCT_NAME),
		'new_item'              => __('New Testimonial', GCT_NAME),
		'all_items' 			=> __('All Testimonials', GCT_NAME),
		'view_item'             => __('View Testimonial', GCT_NAME),
		'search_items'          => __('Search Testimonials', GCT_NAME),
		'not_found'             => __('Nothing Found', GCT_NAME),
		'not_found_in_trash'    => __('Nothing found in Trash', GCT_NAME),
		'parent_item_colon'     => ''
	);
	
	$testimonial_args = array(
		'labels'                => $testimonial_labels,
		'singular_label'        => __('testimonial', GCT_NAME),
		'public'                => true,
		'show_ui'               => true,
		'capability_type'       => 'post',
		'hierarchical'          => false,
		'rewrite'               => true,
		'menu_icon'				=> GCT_URL.'/assets/images/icon_16.png',
		'menu_position'			=> 20,
		'exclude_from_search' 	=> true,
		'supports'              => array('title', 'excerpt', 'editor', 'thumbnail')
	);
	
	register_post_type('testimonial',$testimonial_args);
	
	$categories_labels = array(
		'name'                  => __('Categories', GCT_NAME),
		'singular_name'         => _x('Category', GCT_NAME),
		'all_items' 			=> __('All Categories', GCT_NAME),
		'add_new_item'          => _x('Add New Category', GCT_NAME),
		'edit_item'             => __('Edit Category', GCT_NAME),
		'new_item'              => __('New Category', GCT_NAME),
		'view_item'             => __('View Category', GCT_NAME),
		'search_items'          => __('Search Category', GCT_NAME),
		'not_found'             => __('Nothing Found', GCT_NAME),
		'not_found_in_trash'    => __('Nothing found in Trash', GCT_NAME),
		'parent_item_colon'     => ''
	);
	
	register_taxonomy("testimonial-category", array("testimonial"), array(
		"hierarchical" => true, 
		"labels" => $categories_labels,
		"rewrite" => array("slug" => "view", "hierarchical" => false, "with_front" => false)
	));
	
	add_filter("manage_edit-testimonial_columns", "gct_edit_columns");
	add_action("manage_posts_custom_column", "gct_custom_columns");
	
}
	
/**********************************************************
* Add Custom Columns to the Admin Screen
**********************************************************/

function gct_custom_columns($column) {
	global $post;
	$custom = get_post_custom();
	if ("post_id" == $column) echo $post->ID;
	elseif ("description" == $column) echo substr($post->post_content, 0, 100).'...';
	elseif ("client_name" == $column)  echo $custom["client_name"][0];
	elseif ("thumbnail" == $column) echo $post->post_thumbnail;
	elseif ("shortcode" == $column)  echo '[single-testimonial id="'.$post->ID.'"]';
	elseif("category" == $column) {
		$categories = get_the_terms(0, "testimonial-category");
			if(!is_array($categories)) return;
			$category = reset($categories);
		if(is_object($category)){
			echo $category->name;
		}
	}
}

function gct_edit_columns($columns) {
	$columns = array(
		"cb" 			=> "<input type=\"checkbox\" />",
		"title" 		=> __('Title', GCT_NAME),
		"client_name" 	=> __('Client', GCT_NAME),
		"thumbnail" 	=> __('Thumbnail', GCT_NAME),
		"category" 		=> __('Category', GCT_NAME),
		"shortcode" 	=> __('Shortcode', GCT_NAME),
		"date" 			=> __('Date', GCT_NAME),
	);
	return $columns;
}

add_theme_support('post-thumbnails');
set_post_thumbnail_size(75, 75, true);
 
// For Thumbnail Preview in the admin screen
if (function_exists('add_theme_support')) {
 
	function add_thumbnail_column($cols) { 
		$cols['thumbnail'] = __('Thumbnail', GCT_NAME); 
		return $cols;
	}
 
	function add_thumbnail_value($column_name, $post_id) {
		$width = (int) 75;
		$height = (int) 75;
		
		if ( 'thumbnail' == $column_name ) {
			$thumbnail_id = get_post_meta( $post_id, '_thumbnail_id', true );
			$attachments = get_children( array('post_parent' => $post_id, 'post_type' => 'attachment', 'post_mime_type' => 'image') );
			
			if ($thumbnail_id)
				$thumb = wp_get_attachment_image( $thumbnail_id, array($width, $height), true );
			elseif ($attachments) {
				foreach ( $attachments as $attachment_id => $attachment ) {
					$thumb = wp_get_attachment_image( $attachment_id, array($width, $height), true );
				}
			}
			
			if ( isset($thumb) && $thumb ) {
				echo $thumb;
			} else {
				echo __('None', GCT_NAME);
			}
		}
	}
 	
	add_filter( 'manage_posts_columns', 'add_thumbnail_column' );
	add_action( 'manage_posts_custom_column', 'add_thumbnail_value', 10, 2 );
}

/**********************************************************
* Add Extra Custom Fields to the Post Type Add / Edit screen
* Plus Update Method
**********************************************************/
	
add_action("admin_init", "gct_admin_init");  
add_action('save_post', 'gct_save_details');

function gct_admin_init() {
	add_meta_box("details", "Details", "gct_meta_options", "testimonial", "normal", "low");
}

function gct_meta_options() {  
       global $post;  
       $custom = get_post_custom($post->ID);  
       $client_name = $custom["client_name"][0]; 
       $client_photo = $custom["client_photo"][0];
       $email = $custom["email"][0];
       $company_website = $custom["company_website"][0];
       $company_name = $custom["company_name"][0];
	?>
	<table width="100%" border="0" class="options" cellspacing="5" cellpadding="5">
		<tr>
		<td width="10%"><label for="client_name"><?php _e('Client', GCT_NAME); ?></label></td>
		<td width="10%"><input type="text" id="client_name" name="client_name" value="<?php echo $client_name; ?>" size="40"/></td>
		<td width="70%" class="extra small"><small><?php _e('The Clients Name', GCT_NAME); ?></small></td>
		</tr>
		<tr>
		<td width="10%"><label for="email"><?php _e('Email', GCT_NAME); ?></label></td>
		<td width="10%"><input type="text" id="email" name="email" value="<?php echo $email; ?>" size="40"/></td>
		<td width="70%" class="extra small"><small><?php _e('The Clients Email', GCT_NAME); ?></small></td>
		</tr>
		<tr>
		<td width="10%"><label for="company_website"><?php _e('Website', GCT_NAME); ?></label></td>
		<td width="10%"><input type="text" id="company_website" name="company_website" value="<?php echo $company_website; ?>" size="40"/></td>
		<td width="70%" class="extra small"><small><?php _e('The Company Website', GCT_NAME); ?></small></td>
		</tr>
		<tr>
		<td width="10%"><label for="company_name"><?php _e('Company Name', GCT_NAME); ?></label></td>
		<td width="10%"><input type="text" id="company_name" name="company_name" value="<?php echo $company_name; ?>" size="40"/></td>
		<td width="70%" class="extra small"><small><?php _e('The Company Name', GCT_NAME); ?></small></td>
		</tr>
	</table>
	<?php
   } 
   
function gct_save_details() {  
	global $post;  
	$custom_meta_fields = array( 'client_name','client_photo','email','company_website','company_name' );
	foreach( $custom_meta_fields as $custom_meta_field ):
		if(isset($_POST[$custom_meta_field]) && $_POST[$custom_meta_field] != ""):
			update_post_meta($post->ID, $custom_meta_field, $_POST[$custom_meta_field]);
		endif;
	endforeach;
}

/**********************************************************
* Add Columns to the Testimonials Categories Screen
**********************************************************/

add_filter("manage_edit-testimonial-category_columns", 'gct_manage_categories');

function gct_manage_categories($columns) {
	$new_columns = array(
		'cb' 			=> '<input type="checkbox" />',
		'ID'			=> __('ID', GCT_NAME),
		'name' 			=> __('Name', GCT_NAME),
		'slug' 			=> __('Slug', GCT_NAME),
		'shortcode' 	=> __('Shortcode', GCT_NAME),
		'posts' 		=> __('Posts', GCT_NAME)
		);
	return $new_columns;
}

add_filter("manage_testimonial-category_custom_column", 'gct_manage_columns', 10, 3);	
 
function gct_manage_columns($out, $column_name, $id) {
	$column = get_term($id, 'testimonial-category');
	switch ($column_name) {
		case 'shortcode':	
			$output .= '[full-testimonials category="'.$id.'"]'; 
 			break;
 		case 'ID':	
			$output .= $id; 
 			break;
		default:
			break;
	}
	return $output;	
}
	
/**********************************************************
* Shortcodes
**********************************************************/

/* Single Testimonial LAYOUT */

function gct_single_testimonial($testimonial) {
	$display .= '<div class="testimonial">';
	$display .= '<div class="inner">';
	
	if(!empty($testimonial->post_title)):
		$display .=  '<h3>'.$testimonial->post_title.'</h3>';
	endif;
	
	if(has_post_thumbnail($testimonial->ID)) {
		$display .= '<div class="photo">'.get_the_post_thumbnail($testimonial->ID, 'thumbnail').'</div>';
	}
	
	$display .=   '<div class="content">'.wpautop($testimonial->post_content).'</div>';
	$display .=   '<div class="clear"></div>';
	$display .=   '<div class="client"><span class="name">'.$testimonial->client_name.'</span><br/>';
	
	if(!empty($testimonial->company_name) && !empty($testimonial->company_website)):
		$display .=   '<span class="company">';
		$display .=   '<a href="'.gct_get_website($testimonial->company_website).'" target="blank">'.$testimonial->company_name.'</a>';
		$display .=   '</span>';
	 elseif(!empty($testimonial->company_name)):
	 	$display .=   '<span class="company">';
	 	$display .=   $testimonial->company_name;
	 	$display .=   '</span>';
	 elseif(!empty($testimonial->company_website)):
	 	$display .=   '<span class="website">';
	 	$display .=   $testimonial->company_website;
	 	$display .=   '</span>';
	 endif;
	 
	 $display .=   '</div>';
	 $display .=   '</div>';
	 $display .=   '</div>';
	 
	 return $display;
}

/* Single Testimonial Shortcode */
    
function gct_single_testimonial_shortcode($atts) {
	
	global $add_styles;
	$add_styles = true;
	
	extract(shortcode_atts(array('id' => ''), $atts));
	
	$post = get_post($id);
				
	// Add custom fields
	$selected_extended_posts = array();
	$custom = get_post_custom($post->ID);
		foreach(array('client_name', 'client_photo', 'email', 'company_website', 'company_name') as $field) {
			if(isset($custom[$field])){
				$post->$field = $custom[$field][0];
			}
		}
		
	$selected_extended_posts[] = $post;		
	$testimonial = $post;
	$display .= gct_single_testimonial($testimonial);
	 
	return $display;
}

add_shortcode('single-testimonial', 'gct_single_testimonial_shortcode');

/* Random Testimonial Shortcode */
    
function gct_random_testimonial_shortcode($atts) {
	
	global $add_styles;
	$add_styles = true;
	
	extract(shortcode_atts(array('category' => '', 'limit' => ''), $atts));
	
	if(isset($limit) && $limit != '') { $ppp = $limit; } else { $ppp = "1"; }
	
	if(isset($category) && $category != '') {
		$term = get_term_by('id', $category, 'testimonial-category');
		$args = array(
			$term->taxonomy 	=> $term->slug,
			'post_type' 		=> 'testimonial', 
			'posts_per_page' 	=> $ppp,
			'orderby'         	=> 'rand',
			'post_status'     	=> 'publish'
		);
	} else { 
		$args = array(
			'post_type' 		=> 'testimonial', 
			'posts_per_page' 	=> $ppp,
			'orderby'         	=> 'rand',
			'post_status'     	=> 'publish'
		);
	}
	
	$temp = $wp_query;
	$wp_query= null;
	$wp_query = new WP_Query();
	$posts_array  = $wp_query->query($args);
	
	foreach($posts_array as $post) {	
		// Add custom fields
		$selected_extended_posts = array();
		$custom = get_post_custom($post->ID);
			foreach(array('client_name', 'client_photo', 'email', 'company_website', 'company_name') as $field) {
				if(isset($custom[$field])){
					$post->$field = $custom[$field][0];
				}
			}
			
		$selected_extended_posts[] = $post;		
		$testimonial = $post;
		$display .= gct_single_testimonial($testimonial);
	}
	 
	return $display;

}

add_shortcode('random-testimonial', 'gct_random_testimonial_shortcode');

/* Full Testimonials Shortcode */
    
function gct_full_testimonials_shortcode($atts) {
	
	global $add_styles, $add_pagination;
	$add_styles = true;
	$add_pagination = true;
	
	extract(shortcode_atts(array('category' => ''), $atts));
	
	if ($category != '') { 
		$term = get_term_by('id', $category, 'testimonial-category');
		$term_id = $term->term_id;
		$term_taxonomy = $term->taxonomy;
		$term_slug = $term->slug;
	} else { 
		$term_taxonomy = '';
		$term_slug = '';
	}
	
	$args = array(
		$term_taxonomy 		=> $term_slug,
		'post_type' 		=> 'testimonial', 
		'posts_per_page' 	=> -1,
		'orderby'         	=> 'post_date',
		'order'				=> 'DESC',
		'post_status'     	=> 'publish'
	);
	
	$temp = $wp_query;
	$wp_query= null;
	$wp_query = new WP_Query();
	$posts_array  = $wp_query->query($args);
	
	$display .= '<div id="testimonials_container">';
	
	foreach($posts_array as $post) {
					
		// Add custom fields
		$selected_extended_posts = array();
		$custom = get_post_custom($post->ID);
			foreach(array('client_name', 'client_photo', 'email', 'company_website', 'company_name') as $field) {
				if(isset($custom[$field])){
					$post->$field = $custom[$field][0];
				}
			}
			
		$selected_extended_posts[] = $post;		
		$testimonial = $post;
		
		$display .= '<div class="result">';
		$display .= gct_single_testimonial($testimonial);
		$display .= '</div>';
	}
	
	$display .= '</div>';
	$display .= '<div id="pagingControls"></div>';
	 
	return $display;

}

add_shortcode('full-testimonials', 'gct_full_testimonials_shortcode');

/* Testimonial Submit Form */

function gct_form_shortcode($atts) {
	
	global $add_styles, $add_validation;
	$add_styles = true;
	$add_validation = true;
    
	if (isset( $_POST['gct_create_testimonial_form_submitted'] ) && wp_verify_nonce($_POST['gct_create_testimonial_form_submitted'], 'gct_create_testimonial_form') ){
	
		$gct_client_name = trim($_POST['gct_client_name']);
		$gct_email = trim($_POST['gct_email']);
		$gct_company_name = trim($_POST['gct_company_name']);
		$gct_company_website = trim($_POST['gct_company_website']);
		$gct_headline = trim($_POST['gct_headline']);  
		$gct_text = trim($_POST['gct_text']);  
		$gct_agree = trim($_POST['gct_agree']); 
		
		if ($gct_client_name != '' && $gct_email != '' && $gct_text != '' && $gct_agree != '') {
		
			$testimonial_data = array(
				'post_title' => $gct_headline,
				'post_content' => $gct_text,
				'post_status' => 'pending',
				'post_type' => 'testimonial'
			);
			
			if ($testimonial_id = wp_insert_post($testimonial_data)) {
				
				add_post_meta($testimonial_id, "client_name", $gct_client_name);
				add_post_meta($testimonial_id, "email", $gct_email);
				add_post_meta($testimonial_id, "company_name", $gct_company_name);
				add_post_meta($testimonial_id, "company_website", $gct_company_website);
				
				if($_FILES['gct_client_photo']['size'] > 1) {
					foreach($_FILES as $field => $file){
						
						// Upload File
						$overrides = array('test_form' => false); 
						$uploaded_file = gct_wp_handle_upload($_FILES['gct_client_photo'], $overrides);
						$gct_client_photo = $uploaded_file['url'];
					
						// Create an Attachment
						$attachment = array(
						    'post_title' => $file['name'],
						    'post_content' => '',
						    'post_type' => 'attachment',
						    'post_parent' => $testimonial_id,
						    'post_mime_type' => $file['type'],
						    'guid' => $uploaded_file['url']
					    );
					    
					    $attach_id = wp_insert_attachment( $attachment, $uploaded_file['file'], $testimonial_id );
					  	$attach_data = wp_generate_attachment_metadata( $attach_id, $uploaded_file['file'] );
					  	wp_update_attachment_metadata( $attach_id,  $attach_data );
  						add_post_meta($testimonial_id, 'client_photo', $gct_client_photo);
					    set_post_thumbnail( $testimonial_id, $attach_id );

					}
				}
				
				$email_me = get_option('att_email_me');
				$email_address = get_option('att_email_address');
				
				if($email_me == 'yes' && $email_address != '') {
					$to = $email_address;
					$subject = 'Testimonial Submission on '.get_option('blogname');
					$headers = 'From: noreply@'.$_SERVER['HTTP_HOST'] . "\r\n" . 'X-Mailer: PHP/' . phpversion();
					$message = 'You have received a testimonial submission on '.get_option('blogname').'. This is awaiting action from the administrator of the website.';
					wp_mail($to, $subject, $message, $headers);
				}
				
				return '<div class="testimonial-success">'. __("Thank You! Your Testimonial is awaiting moderation.", GCT_NAME) .'</div>';
			}
	
		} else {
		
			$error = '<strong>'. __("Please check that you have filled out the required fields.", GCT_NAME) .'</strong><br />';  
			if ($gct_agree != 'yes') { $error .= '- '. __("Please agree to your testimonial being published.", GCT_NAME) .'<br />'; }
			if ($gct_client_name == '') { $error .= '- '. __("Please enter your name.", GCT_NAME) .'<br />'; }
			if ($gct_email == '') { $error .= '- '. __("Please enter your email address.", GCT_NAME) .'<br />'; }	
			if ($gct_text == '') { $error .= '- '. __("Please enter your testimonial.", GCT_NAME) .'<br />'; }
		}
	
	}
	
	return gct_get_create_testimonial_form($error, $success, $gct_client_name, $gct_email, $gct_company_name, $gct_company_website, $gct_headline, $gct_text, $gct_agree, $gct_client_photo);

}

add_shortcode('testimonial-form', 'gct_form_shortcode');
		
function gct_get_create_testimonial_form($error = '', $success = '', $gct_client_name = '', $gct_email = '', $gct_company_name = '', $gct_company_website = '', $gct_headline = '', $gct_text = '', $gct_agree = '', $gct_client_photo = '') {
	
	$html .= '<div id="testimonial-form">';
	if($error != '') { $html .= '<div class="error">'.$error.'</div>'; }
	$html .= '<div class="required_notice"><span class="required">* </span>= '.__("Required Field", GCT_NAME).'</div>';
	$html .= '<form id="create_testimonial_form" method="post" action="" enctype="multipart/form-data">';
	$html .= wp_nonce_field('gct_create_testimonial_form', 'gct_create_testimonial_form_submitted');
	
	$html .= '
		<p class=" form-field">
			<label for="gct_client_name">'.__("Full Name:", GCT_NAME).' <span class="req">*</span></label>
			<input type="text" value="' . $gct_client_name . '" name="gct_client_name" id="gct_client_name" class="text required"  minlength="2" />
			<span>'.__("What is your fullname?", GCT_NAME).'</span>
		</p>
		
		<p class=" form-field">
			<label for="gct_email">'.__("Email:", GCT_NAME).' <span class="req">*</span></label>
			<input type="text" value="' . $gct_email . '" name="gct_email" id="gct_email" class="text required email" />
			<span>'.__("Fill in your email address", GCT_NAME).'</span>
		</p>
		
		<p class=" form-field">
			<label for="gct_company_name">'.__("Company Name:", GCT_NAME).'</label>
			<input type="text" value="' . $gct_company_name . '" name="gct_company_name" id="gct_company_name" class="text" />
			<span>'.__("What is your company name?", GCT_NAME).'</span>
		</p>
		
		<p class=" form-field">
			<label for="gct_company_website">'.__("Company Website:", GCT_NAME).'</label>
			<input type="text" value="' . $gct_company_website . '" name="gct_company_website" id="gct_company_website" class="text" />
			<span>'.__("Does your company have a website?", GCT_NAME).'</span>
		</p>
		
		<p class=" form-field">
			<label for="gct_headline">'.__("Heading:", GCT_NAME).'</label>
			<input type="text" value="' . $gct_headline . '" name="gct_headline" id="gct_headline" class="text" />
			<span>'.__("Describe our company in a few short words", GCT_NAME).'</span>
		</p>
		
		<p class=" form-field">
			<label for="gct_text">'.__("Testimonial:", GCT_NAME).' <span class="req">*</span></label>
			<textarea name="gct_text" id="gct_text" class="textarea  required">' . $gct_text . '</textarea><br />
			<span>'.__("What do you think about our company?", GCT_NAME).'</span>
		</p>
		
		<div class="clear"></div>
		
		<p class=" form-field">
			<label for="gct_client_photo">'.__("Photo:", GCT_NAME).'</label>
			<input type="file" name="gct_client_photo" id="gct_client_photo" value="' . $gct_client_photo . '" class="text" /><br />
			<span>'.__("Do you have a photo we can use?", GCT_NAME).'</span>
		</p>
			
		<p class=" form-field agree">
			<input type="checkbox" value="yes" name="gct_agree" id="gct_agree" class="checkbox required" checked="checked" />  
			<span><span class="req">*</span>'.__("I agree that this testimonial can be published.", GCT_NAME).'</span>
		</p>
		
		<div class="clear"></div>
		
		<p class="form-field">
			<input type="submit" id="gct_submit_testimonial" name="gct_submit_testimonial" value="'.__("Add Testimonial", GCT_NAME).'" class="button" validate="required:true" /> 
		</p>
	';
	
	$html .= '</form>';
	$html .= '</div>';
	
	return $html;
	
}

function gct_wp_handle_upload($file_handler,$overrides) {

  require_once(ABSPATH . "wp-admin" . '/includes/image.php');
  require_once(ABSPATH . "wp-admin" . '/includes/file.php');
  require_once(ABSPATH . "wp-admin" . '/includes/media.php');

  $upload = wp_handle_upload( $file_handler, $overrides );
  return $upload ;
}

/**********************************************************
* Testimonial Widget
**********************************************************/

add_action('widgets_init', 'gct_load_testimonial_widgets' );

function gct_load_testimonial_widgets() {
	register_widget('GCT_Testimonial_Menu_Widget');
}

class GCT_Testimonial_Menu_Widget extends WP_Widget {
	
	function GCT_Testimonial_Menu_Widget() {
		
		$widget_ops = array( 'classname' => 'gc-testimonial-widget', 'description' => __('Use this widget to show testimonials in your sidebar.') );
		$control_ops = array( 'width' => 300, 'height' => 350, 'id_base' => 'gc-testimonial-widget' );
		$this->WP_Widget( 'gc-testimonial-widget', __('Testimonial Widget'), $widget_ops, $control_ops );
	}

	function widget($args, $instance) {
		
		if (is_active_widget( '', '', 'gc-testimonial-widget' )) {
			wp_enqueue_style('gctwidgetstyles', plugins_url('/assets/css/gctwidget.css', __FILE__ ), false, '1.0', 'all');
			add_action('wp_footer', 'gct_widget_script');
			add_action('wp_footer', 'gct_widget_code');
		}
		
		$data = array_merge($args, $instance);
		
		echo $data['before_widget'];
		
		if ( !empty( $data['title'] ) ) { echo $data['before_title'] . $data['title'] . $data['after_title']; };
		if ( !empty( $data['limit'] ) ) { $no =  $data['limit']; } else { $no = '2'; }
		
		if ($data['category'] != 'all') { 
			$term = get_term_by('id', $data['category'], 'testimonial-category');
			$term_taxonomy = $term->taxonomy;
			$term_slug = $term->slug;
		} else { 
			$term_taxonomy = '';
			$term_slug = '';
		}
		
		$args = array(
			$term_taxonomy 		=> $term_slug,
			'posts_per_page' 	=> $no,
			'orderby'         	=> 'post_date',
			'order'				=> 'DESC',
			'post_type' 		=> 'testimonial', 
			'post_status'     	=> 'publish'
		);
		
		$temp = $wp_query;
		$wp_query= null;
		$wp_query = new WP_Query();
		$posts_array  = $wp_query->query($args);
		
		if ($data['type'] == 'cycle') { echo '<div id="tcycle">'; }
		
		if($data['words'] !== '') { $words = $data['words']; } else { $words = '250'; }
				
		foreach($posts_array as $post) {
					
			// Add custom fields
			$selected_extended_posts = array();
			$custom = get_post_custom($post->ID);
				foreach(array('client_name', 'client_photo', 'company_website', 'company_name') as $field) {
					if(isset($custom[$field])){
						$post->$field = $custom[$field][0];
					}
				}
			$selected_extended_posts[] = $post;
						
			$testimonial = $post;
		
			echo '<div class="testimonial-widget">';
		
			if(!empty($testimonial->post_title)):
				echo '<h5>'.$testimonial->post_title.'</h5>';
			endif; 
			
			if ($data['images'] == 'yes') {
				if(has_post_thumbnail($testimonial->ID)) {
					echo '<div class="photo">'.get_the_post_thumbnail($testimonial->ID, array(75, 75)).'</div>';
				}
			}
			
			if( strlen($testimonial->post_content) > $words) {
				echo '<div class="content">'.substr(wpautop($testimonial->post_content), 0, $words).'...</div>';
			} else {
				echo '<div class="content">'.wpautop($testimonial->post_content).'</div>';
			}
			
			//echo '<div class="content">'.wp_trim_words( $testimonial->post_content, $num_words = 50, $more = '...' ).'</div>';
			
			echo '<div class="clear"></div>';
			
			echo '<div class="client"><span class="name">'.$testimonial->client_name.'</span><br/>';
			
			if(!empty($testimonial->company_name) && !empty($testimonial->company_website)):
				echo '<span class="company">';
				echo '<a href="'.gct_get_website($testimonial->company_website).'" target="blank">'.$testimonial->company_name.'</a>';
				echo '</span>';
			 elseif(!empty($testimonial->company_name)):
				echo '<span class="company">';
				echo $testimonial->company_name;
				echo '</span>';
			 elseif(!empty($testimonial->company_website)):
				echo '<span class="website">';
				echo $testimonial->company_website;
				echo '</span>';
			 endif;
		 
		 	echo '</div>';
		 	
		 echo '</div>';
		 
	}
	
	if ($data['type'] == 'cycle') { echo '</div><div class="clear"></div>'; }
	
	if ($data['more'] == 'yes') { 
		$link = get_permalink($data['fullpage']);
		echo '<p class="gctst-widget-readmore"><a href="'.$link.'">'. __('Read More Testimonials', GCT_NAME) .' &#187;</a></p>'; 
	}
		
	echo $data['after_widget'];
	
	}
	
	function update($new_instance, $old_instance) {
		$instance = $old_instance;
		$instance['title'] = strip_tags($new_instance['title']);
		$instance['category'] = strip_tags($new_instance['category']);
		$instance['limit'] = strip_tags($new_instance['limit']);
		$instance['more'] = strip_tags($new_instance['more']);
		$instance['fullpage'] = strip_tags($new_instance['fullpage']);
		$instance['images'] = strip_tags($new_instance['images']);
		$instance['type'] = strip_tags($new_instance['type']);
		$instance['words'] = strip_tags($new_instance['words']);
		return $instance;
	}
	
	function form($instance) {
		$defaults = array( 'title' => 'Testimonial');
		$instance = wp_parse_args( (array) $instance, $defaults); 
		$category_list = get_terms('testimonial-category', array(
			'hide_empty' 	=> false,
			'order_by'		=> 'name',
			'pad_counts'	=> true
		));
		
		$pages_list = get_pages(array(
		    'sort_order' => 'ASC',
		    'sort_column' => 'post_title',
		    'post_type' => 'page',
		    'post_status' => 'publish'
    	));
		
		?>
		
		<p>
			<label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title', GCT_NAME) ?>:</label>
			<input type="text" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" value="<?php echo $instance['title']; ?>" class="widefat" />
		</p>
		
		<p>
			<label for="<?php echo $this->get_field_id('category'); ?>"><?php _e('Category', GCT_NAME) ?>:</label>
			<select id="<?php echo $this->get_field_id('category'); ?>" name="<?php echo $this->get_field_name('category'); ?>" class="widefat">
				<option value="all"><?php _e('Show All') ?></option>
				<?php
				foreach($category_list as $category) {
					$data['categories'][$category->term_id] = $category->name . ' (' . $category->count . ')';
					echo '<option value="'.$category->term_id.'"';
					if ($category->term_id == $instance['category']) { echo ' selected="selected"'; }
					echo '>'.$category->name.'</option>';
				}
				?>
			</select>
		</p>
		
		<p>
			<label for="<?php echo $this->get_field_id('type'); ?>"><?php _e('Type', GCT_NAME) ?></label>
			<select id="<?php echo $this->get_field_id('type'); ?>" name="<?php echo $this->get_field_name('type'); ?>" class="widefat">
				<option value="static" <?php if($instance['type'] == 'static') { echo 'selected="selected"'; } ?>><?php _e('Static') ?></option>
				<option value="cycle" <?php if($instance['type'] == 'cycle') { echo 'selected="selected"'; } ?>><?php _e('Cycle') ?></option>
			</select>
		</p>
		
		<p>
			<label for="<?php echo $this->get_field_id('limit'); ?>"><?php _e('Number of testimonials to show', GCT_NAME) ?>:</label>
			<input type="text" id="<?php echo $this->get_field_id('limit'); ?>" name="<?php echo $this->get_field_name('limit'); ?>" value="<?php echo $instance['limit']; ?>" size="3" />
		</p>
		
		<p>
			<label for="<?php echo $this->get_field_id('words'); ?>"><?php _e('Character Count', GCT_NAME) ?>: <small>(Default is 250)</small></label>
			<input type="text" id="<?php echo $this->get_field_id('words'); ?>" name="<?php echo $this->get_field_name('words'); ?>" value="<?php echo $instance['words']; ?>" size="3" />
		</p>
		
		<p>
			<input type="checkbox" id="<?php echo $this->get_field_id('images'); ?>" name="<?php echo $this->get_field_name('images'); ?>" value="yes" <?php if ($instance['images'] == 'yes') { echo 'checked="checked"'; } ?>  class="checkbox" style="width: 5%; position: relative; top: -1px;" /> 
			<label for="<?php echo $this->get_field_id('images'); ?>"><?php _e('Show Images', GCT_NAME) ?>?</label>
		</p>
		
		<p>
			<input type="checkbox" id="<?php echo $this->get_field_id('more'); ?>" name="<?php echo $this->get_field_name('more'); ?>" value="yes" <?php if ($instance['more'] == 'yes') { echo 'checked="checked"'; } ?>  class="checkbox" style="width: 5%; position: relative; top: -1px;" /> 
			<label for="<?php echo $this->get_field_id('more'); ?>"><?php _e('Show Read More Link', GCT_NAME) ?>?</label>
		</p>
		
		<p>
			<label for="<?php echo $this->get_field_id('fullpage'); ?>"><?php _e('Full Testimonials Page', GCT_NAME) ?>:</label>
			<select id="<?php echo $this->get_field_id('fullpage'); ?>" name="<?php echo $this->get_field_name('fullpage'); ?>" class="widefat">
				<option value="*"><?php _e('Please Select') ?></option>
				<?php
				foreach($pages_list as $pages) {
					echo '<option value="'.$pages->ID.'"';
					if ($pages->ID == $instance['fullpage']) { echo ' selected="selected"'; }
					echo '>'.$pages->post_title.'</option>';
				}
				?>
			</select>
		</p>
				
		<?php
	}
	
} 


/**********************************************************
* Settings Panel
**********************************************************/	

 /* Setup Default Options */
 
if ( is_admin() ) { 
	//register_uninstall_hook(KCVT_URL.'/testimonials.php', 'gct_testimonials_de_register_settings');
	register_activation_hook(KCVT_URL.'/testimonials.php', 'gct_testimonials_register_settings');
}

function gct_testimonials_register_settings() { 
	update_option( 'att_pagination_no', '5' );
	update_option( 'att_email_me', 'no' );
	update_option( 'att_email_address', '' );
}

/* Options Panel */

add_action('admin_menu', 'gct_settings_menu');

function gct_settings_menu() {
	add_submenu_page( 'edit.php?post_type=testimonial', 'Settings', 'Settings', 'manage_options', 'settings', 'gct_settings_options' ); 
}

function gct_settings_options() {
	if (!current_user_can('manage_options'))  {
		wp_die( __('You do not have sufficient permissions to access this page.') );
	}
	
	// variables for the field and option names
	$opt_name = array(
		'pagination_no' 				=> 'att_pagination_no',
		'email_me' 						=> 'att_email_me',
		'email_address' 				=> 'att_email_address'
	);
			      
    $hidden_field_name = 'att_submit_hidden';
    
    // Read in existing option value from database
	$opt_val = array(
		'pagination_no' 				=> get_option( $opt_name['pagination_no'] ),
		'email_me' 						=> get_option( $opt_name['email_me'] ),
		'email_address' 				=> get_option( $opt_name['email_address'] )
	);
	
// Check if the user has posted us some information
if(isset($_POST[ $hidden_field_name ]) && $_POST[ $hidden_field_name ] == 'Y' ) {
	
	// Read their posted value
	$opt_val = array(
		'pagination_no' 				=> $_POST[ $opt_name['pagination_no'] ],
		'email_me' 						=> $_POST[ $opt_name['email_me'] ],
		'email_address' 				=> $_POST[ $opt_name['email_address'] ]
	);
	
	// Save the posted value in the database	
	update_option( $opt_name['pagination_no'], $opt_val['pagination_no'] );
	update_option( $opt_name['email_me'], $opt_val['email_me'] );
	update_option( $opt_name['email_address'], $opt_val['email_address'] );
	
	// Put an options updated message on the screen
	?>
	<div id="message" class="updated fade">
		<p><strong><?php _e('Options saved.', GCT_NAME ); ?></strong></p>
	</div>
	
<?php } ?>

	<div class="wrap">
	
		<div class="icon32" id="icon-options-general"><br/></div>
		<h2><?php _e( 'Testimonial Settings', GCT_NAME ); ?></h2>
		
		<div id="tabs">
		
			<ul>
		       <li><a href="#sets"><span>Settings</span></a></li>
		       <li><a href="#shortcodes"><span>Shortcodes</span></a></li>
		   </ul>
		   
		 	<div id="settings">  
		 	
		 		<form name="gc-testimonials" method="post" action="<?php echo str_replace( '%7E', '~', $_SERVER['REQUEST_URI']); ?>">
						
				<input type="hidden" name="<?php echo $hidden_field_name; ?>" value="Y">
				<input type="hidden" name="<?php echo $opt_name['testimonial_layout']; ?>" value="default">
											
				<div id="sets">
				
					<h2>General Settings</h2>
					
					<div class="boxed">
						<p>
							<label for="general_pagination" style="width: 300px; display: inline-block;">Number of testimonials to show per page: </label>
							<input type="text" id="pagination_no" name="<?php echo $opt_name['pagination_no']; ?>" value="<?php echo $opt_val['pagination_no'] ?>" size="3" /> 
						</p>
						<p>
							<label for="email_me" style="width: 300px; display: inline-block;">Email when a testimonial is submitted?</label>
							<input type="checkbox" id="email_me" name="<?php echo $opt_name['email_me']; ?>" value="yes" <?php echo ($opt_val['email_me'] == "yes") ? 'checked="checked"' : ''; ?> /> Yes
						</p>
						<p>
							<label for="email_address" style="width: 300px; display: inline-block;">Email address for submission notification: </label>
							<input type="text" id="email_address" name="<?php echo $opt_name['email_address']; ?>" value="<?php echo $opt_val['email_address'] ?>" /> 
						</p>
						
					</div>
					
				</div> <!-- end #settings -->
				
				<div id="shortcodes">
			
					<h2>Shortcodes</h2>
					
					<div class="boxed">
						<h3>Random Testimonial</h3>
						<p>This shortcode is used to display random testimonials on your page or post. <br />The shortcode can take an optional category ID for when you want to display testimonials from a specific category. You will find the category ID on the <a href="<?php bloginfo('wpurl'); ?>/wp-admin/edit-tags.php?taxonomy=testimonial-category&post_type=testimonial">categories screen</a>. <br />You can also optionally set how many testimonials you would like to show by changing the "limit" attribute. Default is 1.</p>
						<p><strong>Shortcode:</strong> [random-testimonial category="xx" limit="x"]</p>
					</div>
					
					<div class="boxed">
						<h3>Single Testimonial</h3>
						<p>This shortcode is used to show a single specific testimonial on your page or post. The shortcode requires the Testimonial ID. <br />You will find this shortcode on the <a href="<?php bloginfo('wpurl'); ?>/wp-admin/edit.php?post_type=testimonial">testimonials screen</a> next to each of your posts.</p>
						<p><strong>Shortcode:</strong> [single-testimonial id="xx"]</p>
					</div>
					
					<div class="boxed">
						<h3>Full Testimonials</h3>
						<p>This shortcode is used to show a page with all your testimonials displayed. The shortcode can take an optional category ID for when you want to display testimonials from a specific category. <br />You will find this shortcode on the <a href="<?php bloginfo('wpurl'); ?>/wp-admin/edit-tags.php?taxonomy=testimonial-category&post_type=testimonial">categories screen</a> next to each of your categories.</p>
						<p><strong>Shortcode:</strong> [full-testimonials category="xx"]</p>
					</div>
					
					<div class="boxed">
						<h3>Testimonial Submit Form</h3>
						<p>This shortcode is used to show a form on your site where a user can submit their own testimonial. Once a testimonial has been submitted, the status will be set to "Pending" and will need to be approved by an administrator before it is shown publicaly. If you would like to be notified once a testimonial has been submitted, you can add your email address on the settings tab for this.</p>
						<p><strong>Shortcode:</strong> [testimonial-form]</p>
					</div>
					
				</div> <!-- end #shortcodes -->
				
				<p><input class="button-primary" type="submit" name="Save" value="<?php _e('Save Options', GCT_NAME); ?>" id="submitbutton" /></p>
				
				</form>
			
			</div> <!-- end #settings -->
			
		</div> <!-- end #tabs -->
		
	</div> <!-- end .wrap -->

<?php
}

	
