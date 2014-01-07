<?php
/*** HEADER ***/
/**
 * Add pre-header with social and search
 */
function msdlab_pre_header(){
    print '<div class="pre-header">
        <div class="wrap">';
           do_action('msdlab_pre_header');
           do_shortcode('[msd-digits]');
           do_shortcode('[msd-social]');
           get_search_form();
    print '
        </div>
    </div>';
}

//add language widget after subnav
function good_advice_language_widget(){
    $instance = array (
    'type' => 'both',
    'hide-title' => 'on',
  );
  $attr = array();
  ob_start();
  the_widget('qTranslateWidget',$instance,$attr);
  $ret = ob_get_contents();
  ob_end_clean();
  preg_match('@<ul.*?>(.*?)</ul>@i',$ret,$matches);
  return $matches[0];
}

function good_advice_subnav_right( $menu, $args ) {
    $args = (array) $args;
    $langs = good_advice_language_widget();
    $menu = preg_replace('@<a.*?>Choose Language</a>@i','<a href="#">Choose Language</a>'."\n".$langs,$menu);
    return $menu;
}


 /**
 * Customize search form input
 */
function msdlab_search_text($text) {
    $text = esc_attr( 'Search...' );
    return $text;
} 
 
 /**
 * Customize search button text
 */
function msdlab_search_button($text) {
    $text = "&#xF002;";
    return $text;
}

/**
 * Customize search form 
 */
function msdlab_search_form($form, $search_text, $button_text, $label){
   if ( genesis_html5() )
        $form = sprintf( '<form method="get" class="search-form" action="%s" role="search">%s<input type="search" name="s" placeholder="%s" /><input type="submit" value="%s" /></form>', home_url( '/' ), esc_html( $label ), esc_attr( $search_text ), esc_attr( $button_text ) );
    else
        $form = sprintf( '<form method="get" class="searchform search-form" action="%s" role="search" >%s<input type="text" value="%s" name="s" class="s search-input" onfocus="%s" onblur="%s" /><input type="submit" class="searchsubmit search-submit" value="%s" /></form>', home_url( '/' ), esc_html( $label ), esc_attr( $search_text ), esc_attr( $onfocus ), esc_attr( $onblur ), esc_attr( $button_text ) );
    return $form;
}

/*** NAV ***/


/*** SIDEBARS ***/
/**
 * Reversed out style SCS
 * This ensures that the primary sidebar is always to the left.
 */
function msdlab_ro_layout_logic() {
    $site_layout = genesis_site_layout();    
    if ( $site_layout == 'sidebar-content-sidebar' ) {
        // Remove default genesis sidebars
        remove_action( 'genesis_after_content', 'genesis_get_sidebar' );
        remove_action( 'genesis_after_content_sidebar_wrap', 'genesis_get_sidebar_alt');
        // Add layout specific sidebars
        add_action( 'genesis_before_content_sidebar_wrap', 'genesis_get_sidebar' );
        add_action( 'genesis_after_content', 'genesis_get_sidebar_alt');
    }
}

/*** CONTENT ***/

/**
 * Move titles
 */
function msdlab_do_title_area(){
    print '<div id="page-title-area" class="page-title-area">';
    print '<div class="wrap">';
    do_action('msdlab_title_area');
    print '</div>';
    print '</div>';
}

/**
 * Customize Breadcrumb output
 */
function msdlab_breadcrumb_args($args) {
    $args['labels']['prefix'] = ''; //marks the spot
    $args['sep'] = ' > ';
    return $args;
}
function sp_post_info_filter($post_info) {
    $post_info = '[post_date]';
    return $post_info;
}
/**
 * Custom blog loop
 */
// Setup Grid Loop
function msdlab_blog_grid(){
    if(is_home()){
        remove_action( 'genesis_loop', 'genesis_do_loop' );
        add_action( 'genesis_loop', 'msdlab_grid_loop_helper' );
        add_action('genesis_before_post', 'msdlab_switch_content');
        remove_action( 'genesis_after_post_content', 'genesis_post_meta' );
        add_filter('genesis_grid_loop_post_class', 'msdlab_grid_add_bootstrap');
    }
}
function msdlab_grid_loop_helper() {
    if ( function_exists( 'genesis_grid_loop' ) ) {
        genesis_grid_loop( array(
        'features' => 1,
        'feature_image_size' => 'child_full',
        'feature_image_class' => 'aligncenter post-image',
        'feature_content_limit' => 0,
        'grid_image_size' => 'child_thumbnail',
        'grid_image_class' => 'alignright post-image',
        'grid_content_limit' => 0,
        'more' => __( '[Continue reading...]', 'adaptation' ),
        'posts_per_page' => 7,
        ) );
    } else {
        genesis_standard_loop();
    }
}

// Customize Grid Loop Content
function msdlab_switch_content() {
    remove_action('genesis_post_content', 'genesis_grid_loop_content');
    add_action('genesis_post_content', 'msdlab_grid_loop_content');
    add_action('genesis_after_post', 'msdlab_grid_divider');
    add_action('genesis_before_post_title', 'msdlab_grid_loop_image');
}

function msdlab_grid_loop_content() {

    global $_genesis_loop_args;

    if ( in_array( 'genesis-feature', get_post_class() ) ) {
        if ( $_genesis_loop_args['feature_image_size'] ) {
            printf( '<a href="%s" title="%s">%s</a>', get_permalink(), the_title_attribute('echo=0'), genesis_get_image( array( 'size' => $_genesis_loop_args['feature_image_size'], 'attr' => array( 'class' => esc_attr( $_genesis_loop_args['feature_image_class'] ) ) ) ) );
        }

        the_excerpt();
        $num_comments = get_comments_number();
        if ($num_comments == '1') $comments = '<span>'.$num_comments.'</span> ' . __( 'comment', 'adaptation' );
        else $comments = '<span>'.$num_comments.'</span> ' . __( 'comments', 'adaptation' );
        echo '<p class="to_comments"><span class="bracket">{</span><a href="'.get_permalink().'/#comments" rel="nofollow">'.$comments.'</a><span class="bracket">}</span></p>';
        
    }
    else {

        the_excerpt();
        $num_comments = get_comments_number();
        if ($num_comments == '1') $comments = $num_comments.' ' . __( 'comment', 'adaptation' );
        else $comments = $num_comments.' ' . __( 'comments', 'adaptation' );
        echo '<p class="more"><a class="comments" href="'.get_permalink().'/#comments">'.$comments.'</a> <a href="'.get_permalink().'">' . __( 'Read the full article &#187;', 'adaptation' ) . '</a></p>';
    }

}

function msdlab_grid_loop_image() {
    if ( in_array( 'genesis-grid', get_post_class() ) ) {
        global $post;
        echo '<p class="thumbnail"><a href="'.get_permalink().'">'.get_the_post_thumbnail($post->ID, 'child_thumbnail').'</a></p>';
    }
}

function msdlab_grid_divider() {
    global $loop_counter, $paged;
    if ((($loop_counter + 1) % 2 == 0) && !($paged == 0 && $loop_counter < 2)) echo '<hr />';
}

 function msdlab_grid_add_bootstrap($classes){
     if(in_array('genesis-grid',$classes)){
         $classes[] = 'col-md-6';
     }
     return $classes;
 }


/*** FOOTER ***/

/**
 * Footer replacement with MSDSocial support
 */
function msdlab_do_social_footer(){
    global $msd_social;
    if(has_nav_menu('footer_menu')){$social .= wp_nav_menu( array( 'theme_location' => 'footer_menu','container_class' => 'ftr-menu ftr-links','echo' => FALSE ) );}
    
    if($msd_social){
        $copyright .= '&copy; Copyright '.date('Y').' '.$msd_social->get_bizname().' &middot; All Rights Reserved';
    } else {
        $copyright .= '&copy; Copyright '.date('Y').' '.get_bloginfo('name').' &middot; All Rights Reserved ';
    }
    
    print '<div id="social" class="social creds">'.$social.'</div>';
    print '<div id="copyright" class="copyright gototop">'.$copyright.'</div>';
}

/**
 * Menu area for above footer treatment
 */
register_nav_menus( array(
    'footer_menu' => 'Footer Menu'
) );

function add_drop_shadow_to_footer(){
    print '<div class="footer-shadow"></div>';
}

/*** Blog Header ***/
function msd_add_blog_header(){
    global $post;
    if(get_post_type() == 'post' || get_section()=='blog'){
        $header = '
        <div id="blog-header" class="blog-header">
            <h3></h3>
            <p></p>
        </div>';
    }
    print $header;
}
