<?php
remove_all_actions('woocommerce_before_main_content');
add_action('woocommerce_before_main_content','msdlab_gravity_to_woo_header');
add_action('woocommerce_after_main_content','msdlab_gravity_to_woo_footer');
function msdlab_gravity_to_woo_header(){
    remove_action('genesis_entry_header', 'genesis_post_info', 12);
    add_filter('genesis_post_title_text','msdlab_subcat_title_replacement');
   do_action( 'genesis_before_entry' );
    printf( '<article %s>', genesis_attr( 'entry' ) );
    do_action( 'genesis_entry_header' );
    do_action( 'genesis_before_entry_content' );
    printf( '<div %s>', genesis_attr( 'entry-content' ) );
}

function msdlab_gravity_to_woo_footer(){
   echo '</div>'; //* end .entry-content
   do_action( 'genesis_after_entry_content' );
   do_action( 'genesis_entry_footer' );
   echo '</article>';
    do_action( 'genesis_after_entry' );
}

add_action('wp_enqueue_scripts','msdlab_remove_woo_styles', 100);
function msdlab_remove_woo_styles(){
    wp_dequeue_style('woocommerce-general');
}

function msdlab_reset_button($text){
    return 'Take a Closer Look';
}

function msdlab_subcat_title_replacement($title){
    global $wp_query;
    if(strpos($_SERVER['REQUEST_URI'],'shop')){
        return "Shop";
    } elseif(is_main_query() && $wp_query->query['product_cat']!=''){
        return $wp_query->queried_object->name;
    } else {
        return $title;
    }
}


if(!class_exists('WPAlchemy_MetaBox')){
    include_once WP_CONTENT_DIR.'/wpalchemy/MetaBox.php';
}
add_action('init','msdlab_payment_metabox',-1);
function msdlab_payment_metabox(){
    global $payment_metabox;
    $payment_metabox = new WPAlchemy_MetaBox(array
    (
        'id' => '_payment_link',
        'title' => 'Payment Link',
        'types' => array('shop_order'),
        'context' => 'side', // same as above, defaults to "normal"
        'priority' => 'high', // same as above, defaults to "high"
        'template' => get_stylesheet_directory() . '/lib/template/payment-meta.php',
        'autosave' => TRUE,
        'mode' => WPALCHEMY_MODE_EXTRACT, // defaults to WPALCHEMY_MODE_ARRAY
        'prefix' => '_msdlab_' // defaults to NULL
    ));
}