<?php
// cleanup tinymce for SEO
function fb_change_mce_buttons( $initArray ) {
	//@see http://wiki.moxiecode.com/index.php/TinyMCE:Control_reference
	$initArray['theme_advanced_blockformats'] = 'p,address,pre,code,h3,h4,h5,h6';
	$initArray['theme_advanced_disable'] = 'forecolor';

	return $initArray;
}
add_filter('tiny_mce_before_init', 'fb_change_mce_buttons');
	
// add classes for various browsers
add_filter('body_class','browser_body_class');
function browser_body_class($classes) {
    global $is_lynx, $is_gecko, $is_IE, $is_opera, $is_NS4, $is_safari, $is_chrome, $is_iphone;
 
    if($is_lynx) $classes[] = 'lynx';
    elseif($is_gecko) $classes[] = 'gecko';
    elseif($is_opera) $classes[] = 'opera';
    elseif($is_NS4) $classes[] = 'ns4';
    elseif($is_safari) $classes[] = 'safari';
    elseif($is_chrome) $classes[] = 'chrome';
    elseif($is_IE) $classes[] = 'ie';
    else $classes[] = 'unknown';
 
    if($is_iphone) $classes[] = 'iphone';
    return $classes;
}

add_filter('body_class','pagename_body_class');
function pagename_body_class($classes) {
	global $post;
	if(is_page()){
		$classes[] = $post->post_name;
	}
	return $classes;
}

add_filter('body_class','section_body_class');
function section_body_class($classes) {
	global $post;
	$post_data = get_post(get_topmost_parent($post->ID));
	$classes[] = 'section-'.$post_data->post_name;
	return $classes;
}
add_filter('body_class','category_body_class');
function category_body_class($classes) {
    global $post;
	$post_categories = wp_get_post_categories( $post->ID );
	foreach($post_categories as $c){
		$cat = get_category( $c );
		$classes[] = 'category-'.$cat->slug;
	}
    return $classes;
}

// add classes for subdomain
if(is_multisite()){
	add_filter('body_class','subdomain_body_class');
	function subdomain_body_class($classes) {
		global $subdomain;
		$site = get_current_site()->domain;
		$url = get_bloginfo('url');
		$sub = preg_replace('@http://@i','',$url);
		$sub = preg_replace('@'.$site.'@i','',$sub);
		$sub = preg_replace('@\.@i','',$sub);
		$classes[] = 'site-'.$sub;
		$subdomain = $sub;
		return $classes;
	}
}

add_action('template_redirect','set_section');
function set_section(){
	global $post, $section;
	$section = get_section();
}

function get_section(){
    global $post;
    $post_data = get_post(get_topmost_parent($post->ID));
    $section = $post_data->post_name;
    return $section;
}

function get_topmost_parent($post_id){
	$parent_id = get_post($post_id)->post_parent;
	if($parent_id == 0){
		$parent_id = $post_id;
	}else{
		$parent_id = get_topmost_parent($parent_id);
	}
	return $parent_id;
}
add_filter( 'the_content', 'msd_remove_msword_formatting' );
function msd_remove_msword_formatting($content){
	global $allowedposttags;
	$allowedposttags['span']['style'] = false;
	$content = wp_kses($content,$allowedposttags);
	return $content;
}
add_action('init','msd_allow_all_embeds');
function msd_allow_all_embeds(){
	global $allowedposttags;
	$allowedposttags["iframe"] = array(
			"src" => array(),
			"height" => array(),
			"width" => array()
	);
	$allowedposttags["object"] = array(
			"height" => array(),
			"width" => array()
	);

	$allowedposttags["param"] = array(
			"name" => array(),
			"value" => array()
	);

	$allowedposttags["embed"] = array(
			"src" => array(),
			"type" => array(),
			"allowfullscreen" => array(),
			"allowscriptaccess" => array(),
			"height" => array(),
			"width" => array()
	);
}

/* ---------------------------------------------------------------------- */
/* Check the current post for the existence of a short code
/* ---------------------------------------------------------------------- */

if ( !function_exists('msdlab_has_shortcode') ) {

    function msdlab_has_shortcode($shortcode = '') {
    
        global $post;
        $post_obj = get_post( $post->ID );
        $found = false;
        
        if ( !$shortcode )
            return $found;
        if ( stripos( $post_obj->post_content, '[' . $shortcode ) !== false )
            $found = true;
        
        // return our results
        return $found;
    
    }
}

/**
 * Check if a post is a particular post type.
 */
if(!function_exists('is_cpt')){
	function is_cpt($cpt){
		global $post;
		$ret = get_post_type( $post ) == $cpt?TRUE:FALSE;
		return $ret;
	}
}

function remove_wpautop( $content ) { 
    $content = do_shortcode( shortcode_unautop( $content ) ); 
    $content = preg_replace( '#^<\/p>|^<br \/>|<p>$#', '', $content );
    return $content;
}