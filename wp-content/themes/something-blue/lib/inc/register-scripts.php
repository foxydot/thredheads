<?php
/*
 * Add styles and scripts
*/
add_action('wp_enqueue_scripts', 'msdlab_add_styles');
add_action('wp_enqueue_scripts', 'msdlab_add_scripts');

function msdlab_add_styles() {
    global $is_IE;
    if(!is_admin()){
        //use cdn        
            wp_enqueue_style('bootstrap-style','//netdna.bootstrapcdn.com/bootstrap/3.0.0/css/bootstrap.no-icons.min.css');
            wp_enqueue_style('font-awesome-style','//netdna.bootstrapcdn.com/font-awesome/4.0.3/css/font-awesome.css',array('bootstrap-style'));
            wp_enqueue_style('jqueryui-style',get_stylesheet_directory_uri().'/lib/css/blitzer/jquery-ui-1.10.3.custom.css',array('bootstrap-style','msd-style'));
        //use local
            //wp_enqueue_style('bootstrap-style',get_stylesheet_directory_uri().'/lib/bootstrap/css/bootstrap.css');
            //wp_enqueue_style('font-awesome-style',get_stylesheet_directory_uri().'/lib/font-awesome/css/font-awesome.css',array('bootstrap-style'));
            
            //wp_enqueue_style('infinitive-icon-font-style',get_stylesheet_directory_uri().'/lib/infinitive-icon-font/styles.css',array('bootstrap-style','font-awesome-style'));
            wp_enqueue_style('msd-style',get_stylesheet_directory_uri().'/lib/css/style.css',array('bootstrap-style','font-awesome-style'));
        if($is_IE){
            wp_enqueue_script('ie-style',get_stylesheet_directory_uri().'/lib/css/ie.css');
        }
        if(is_front_page()){
            wp_enqueue_style('msd-homepage-style',get_stylesheet_directory_uri().'/lib/css/homepage.css',array('bootstrap-style','font-awesome-style'));
        }
    }
}

function msdlab_add_scripts() {
    global $is_IE;
    if(!is_admin()){
        wp_enqueue_script( 'jquery' );
        //use cdn
            wp_enqueue_script('bootstrap-jquery','//netdna.bootstrapcdn.com/bootstrap/3.0.0/js/bootstrap.min.js',array('jquery'));
        //use local
            wp_enqueue_script('bootstrap-jquery',get_stylesheet_directory_uri().'/lib/bootstrap/js/bootstrap.min.js',array('jquery'));
        wp_enqueue_script('msd-jquery',get_stylesheet_directory_uri().'/lib/js/theme-jquery.js',array('jquery','bootstrap-jquery'));
        wp_enqueue_script('equalHeights',get_stylesheet_directory_uri().'/lib/js/jquery.equal-height-columns.js',array('jquery'));
        if($is_IE){
            wp_enqueue_script('columnizr',get_stylesheet_directory_uri().'/lib/js/jquery.columnizer.js',array('jquery'));
            wp_enqueue_script('ie-fixes',get_stylesheet_directory_uri().'/lib/js/ie-jquery.js',array('jquery'));
        }
        if(is_front_page()){
            wp_enqueue_script('msd-homepage-jquery',get_stylesheet_directory_uri().'/lib/js/homepage-jquery.js',array('jquery','bootstrap-jquery'));
        }
    }
}