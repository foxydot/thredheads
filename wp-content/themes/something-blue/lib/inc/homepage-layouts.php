<?php
/*** WIDGET AREAS ***/
/**
 * Hero and (3) widget areas
 */
function msdlab_add_homepage_hero_flex_sidebars(){
    genesis_register_sidebar(array(
    'name' => 'Homepage Hero',
    'description' => 'Homepage hero space',
    'id' => 'homepage-top'
            ));
    genesis_register_sidebar(array(
    'name' => 'Homepage Widgets Right',
    'description' => 'Homepage central widget areas',
    'id' => 'homepage-widgets',
    'before_widget' => genesis_markup( array(
        'html5' => '<section id="%1$s" class="widget %2$s"><div class="widget-icon"><i class="fa fa-4x"></i></div><div class="widget-wrap">',
        'xhtml' => '<div id="%1$s" class="widget %2$s"><div class="widget-icon"><i class="fa fa-4x"></i></div><div class="widget-wrap">',
        'echo'  => false,
    ) ),
    
    'after_widget'  => genesis_markup( array(
        'html5' => '</div><div class="clear"></div></section>' . "\n",
        'xhtml' => '</div><div class="clear"></div></div>' . "\n",
        'echo'  => false
    ) ),
            )); 
}

/**
 * Callout Bar widget area
 */
function msdlab_add_homepage_callout_sidebars(){
    genesis_register_sidebar(array(
    'name' => 'Homepage Widgets Left',
    'description' => 'Homepage call to action',
    'id' => 'homepage-callout'
            ));
}
/**
 * Add a hero space with the site description
 */
function msdlab_hero(){
	if(is_active_sidebar('homepage-top')){
		print '<div id="hp-top">';
		dynamic_sidebar('homepage-top');
		print '</div>';
	}
}

/**
 * Add a hero space with the site description
 */
function msdlab_callout(){
	print '<div id="hp-callout">';
	print '<div class="wrap">';
    if(is_active_sidebar('homepage-callout')){
    	dynamic_sidebar('homepage-callout');
	} else {
        do_action( 'genesis_site_description' );
    }
	print '</div>';
	print '</div>';
}

/**
 * Add flaxible widget area
 */
function msdlab_homepage_widgets(){
	print '<div id="homepage-widgets" class="widget-area">';
	print '<div class="wrap">';
        print '<div class="left">';
        dynamic_sidebar('homepage-callout');
        print '</div>';
        print '<div class="right">';
        dynamic_sidebar('homepage-widgets');
        print '</div>';
  	print '</div>';
	print '</div>';
}

/**
 * Create a long scrollie page with child pages of homepage.
 * Uses featured image for background of each wrap section.
 */
function msdlab_scrollie_page(){
    global $post;
    $edit = get_edit_post_link($post->ID) != ''?'<a href="'.get_edit_post_link($post->ID).'"><i class="icon-edit"></i></a>':'';
    $thumbnail = wp_get_attachment_image_src( get_post_thumbnail_id($post->ID), 'full' );
    $background = $thumbnail?' style="background-image:url('.$thumbnail[0].');"':'';
    remove_filter('the_content','wpautop',12);
    print '<div id="intro" class="scrollie parent div-intro div0">
                <div class="background-wrapper"'.$background.'>
                        <div class="wrap">
                            <div class="page-content">
                                    <div class="entry-content">';
    print apply_filters('the_content', $post->post_content);
    print '                     </div>
                            '.$edit.'
                            </div>
                        </div>
                    </div>
                </div>';
    print '<div id="callout"><p>'.get_option('blogdescription').'</p></div>';

    add_filter('the_content','wpautop',12);
    $my_wp_query = new WP_Query();
    $args = array(
            'post_type' => 'page',
            'order' => 'ASC',
            'orderby' => 'menu_order',
            'tax_query' => array(
                    array(
                        'taxonomy' => 'msdlab_scrollie',
                        'field' => 'slug',
                        'terms' => 'home'
                        )
                    )
            );
    $children = $my_wp_query->query($args);
    $i = 1;
    foreach($children AS $child){
        $thumbnail = wp_get_attachment_image_src( get_post_thumbnail_id($child->ID), 'full' );
        $background = $thumbnail?' style="background-image:url('.$thumbnail[0].');"':'';
        $form = $child->post_name=='contact-us'?do_shortcode('[gravityform id="1" name="Untitled Form" title="false" ajax="true"]'):'';
        $edit = get_edit_post_link($child->ID) != ''?'<a href="'.get_edit_post_link($child->ID).'"><i class="icon-edit"></i></a>':'';
        print '<div id="'.$child->post_name.'" class="scrollie child div-'.$child->post_name.' div'.$i.' trigger" postid="'.$child->ID.'">
                <div class="background-wrapper"'.$background.'>
                        <div class="wrap">'.$form.'
                            <div class="page-content">
                                <h2 class="entry-title">'.$child->post_title.'</h2>
                                <div class="entry-content">'.apply_filters('the_content', $child->post_content).'</div>
                                '.$edit.'
                            </div>
                        </div>
                    </div>
                </div>';
        $i++;
    }
}

/**
 * create a taxonomy for long scrollies
 */
function register_taxonomy_scrollie() {

    $labels = array(
            'name' => _x( 'Scrollie Sections', 'scrollie' ),
            'singular_name' => _x( 'Scrollie Section', 'scrollie' ),
            'search_items' => _x( 'Search Scrollie Sections', 'scrollie' ),
            'popular_items' => _x( 'Popular Scrollie Sections', 'scrollie' ),
            'all_items' => _x( 'All Scrollie Sections', 'scrollie' ),
            'parent_item' => _x( 'Parent Scrollie Section', 'scrollie' ),
            'parent_item_colon' => _x( 'Parent Scrollie Section:', 'scrollie' ),
            'edit_item' => _x( 'Edit Scrollie Section', 'scrollie' ),
            'update_item' => _x( 'Update Scrollie Section', 'scrollie' ),
            'add_new_item' => _x( 'Add New Scrollie Section', 'scrollie' ),
            'new_item_name' => _x( 'New Scrollie Section Name', 'scrollie' ),
            'separate_items_with_commas' => _x( 'Separate scrollies with commas', 'scrollie' ),
            'add_or_remove_items' => _x( 'Add or remove scrollies', 'scrollie' ),
            'choose_from_most_used' => _x( 'Choose from the most used scrollies', 'scrollie' ),
            'menu_name' => _x( 'Scrollie Sections', 'scrollie' ),
    );

    $args = array(
            'labels' => $labels,
            'public' => false,
            'show_in_nav_menus' => false,
            'show_ui' => true,
            'show_tagcloud' => false,
            'hierarchical' => true,

            'rewrite' => true,
            'query_var' => true
    );

    register_taxonomy( 'msdlab_scrollie', array('page'), $args );
}   