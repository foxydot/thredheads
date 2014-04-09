<?php
global $wp_filter;
//ts_var( $wp_filter['genesis_before_footer'] );

add_theme_support( 'genesis-connect-woocommerce' );
require_once('genesis_tweak_functions.php');
/*** GENERAL ***/
add_theme_support( 'html5' );//* Add HTML5 markup structure
add_theme_support( 'genesis-responsive-viewport' );//* Add viewport meta tag for mobile browsers
add_theme_support( 'custom-background' );//* Add support for custom background

/*** HEADER ***/
add_filter( 'genesis_search_text', 'msdlab_search_text' ); //customizes the serach bar placeholder
add_filter('genesis_search_button_text', 'msdlab_search_button'); //customize the search form to add fontawesome search button.
add_action('genesis_before_header','msdlab_pre_header');
add_filter('genesis_do_subnav','good_advice_subnav_right',10,2);
/*** NAV ***/
/**
 * Move nav into header
 */
remove_action( 'genesis_after_header', 'genesis_do_nav' );
add_action( 'genesis_header', 'genesis_do_nav' );
/**
 * Move secodary nav into pre-header
 */
remove_action( 'genesis_after_header', 'genesis_do_subnav' );
add_action( 'msdlab_pre_header', 'genesis_do_subnav' );
add_action('msdlab_pre_header','msdlab_header_right');
add_action('genesis_after_header',array('MSDSocial','msdlab_bang_bar'));

add_action('genesis_after_header','msd_post_image');//add the image above the entry

//*** SIDEBARS ***/
//add_action('genesis_before', 'msdlab_ro_layout_logic'); //This ensures that the primary sidebar is always to the left.
add_action('after_setup_theme','msdlab_add_product_sidebar');
add_filter('widget_text', 'do_shortcode');//shortcodes in widgets

/*** CONTENT ***/
add_filter('genesis_breadcrumb_args', 'msdlab_breadcrumb_args'); //customize the breadcrumb output
remove_action('genesis_before_loop', 'genesis_do_breadcrumbs'); //move the breadcrumbs 
add_filter( 'genesis_post_info', 'sp_post_info_filter' );
//remove_action('genesis_entry_header','genesis_do_post_title'); //move the title out of the content area
//add_action('msdlab_title_area','genesis_do_post_title');
//add_action('genesis_after_header','msdlab_do_title_area');
add_action('genesis_entry_header','msdlab_do_post_subtitle'); 
add_action('genesis_before_content_sidebar_wrap', 'genesis_do_breadcrumbs'); //to outside of the loop area

remove_action( 'genesis_before_post_content', 'genesis_post_info' ); //remove the info (date, posted by,etc.)
remove_action( 'genesis_after_post_content', 'genesis_post_meta' ); //remove the meta (filed under, tags, etc.)
add_action( 'msdlab_title_area', 'msdlab_do_post_subtitle' );

add_action( 'genesis_before_post', 'msdlab_post_image', 8 ); //add feature image across top of content on *pages*.
add_action('template_redirect','msdlab_blog_grid');

remove_action( 'genesis_entry_footer', 'genesis_post_meta' );
 
/*** FOOTER ***/
add_theme_support( 'genesis-footer-widgets', 1 ); //adds automatic footer widgets

remove_action('genesis_before_footer','genesis_footer_widget_areas',10);
add_action('genesis_after_footer','genesis_footer_widget_areas');
remove_action('genesis_footer','genesis_do_footer'); //replace the footer
add_action('genesis_footer','msdlab_do_social_footer');//with a msdsocial support one
add_action('genesis_before_footer','add_drop_shadow_to_footer',50);
/*** HOMEPAGE (BACKEND SUPPORT) ***/
add_action('after_setup_theme','msdlab_add_homepage_hero_flex_sidebars'); //creates widget areas for a hero and flexible widget area
add_action('after_setup_theme','msdlab_add_homepage_callout_sidebars'); //creates a widget area for a callout bar, usually between the hero and the widget area

/*** Blog Header ***/
//add_action('genesis_before_loop','msd_add_blog_header');
//add_action('wp_head', 'collections');

/* WPSC Tweaks */
add_action('wp','msdlab_single_product_layout');