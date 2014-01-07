<?php

global $wp_filter;
//ts_var( $wp_filter['body_class'] );
remove_action( 'genesis_entry_content', 'genesis_do_post_content' );
add_action('genesis_entry_header','msdlab_category_do_post_title', 20); //move the title out of the content area
function msdlab_category_do_post_title() {

    $title = apply_filters( 'genesis_post_title_text', get_the_title() );

    if ( 0 === mb_strlen( $title ) )
        return;

    //* Link it, if necessary
    if ( ! is_singular() && !is_category('press-room') && apply_filters( 'genesis_link_post_title', true ) )
        $title = sprintf( '<a href="%s" title="%s" rel="bookmark">%s</a>', get_permalink(), the_title_attribute( 'echo=0' ), $title );

    //* Wrap in H1 on singular pages
    $wrap = is_singular()? 'h1' : 'h3';

    //* Also, if HTML5 with semantic headings, wrap in H1
    $wrap = genesis_html5() && genesis_get_seo_option( 'semantic_headings' ) ? 'h1' : $wrap;

    //* Build the output
    $output = genesis_markup( array(
        'html5'   => "<{$wrap} %s>",
        'xhtml'   => sprintf( '<%s class="entry-title">%s</%s>', $wrap, $title, $wrap ),
        'context' => 'entry-title',
        'echo'    => false,
    ) );

    $output .= genesis_html5() ? "{$title}</{$wrap}>" : '';
    
    if(is_category('press-room'))
        $output .= sprintf( '<a href="%s" title="%s" rel="bookmark">%s</a>', get_permalink(), the_title_attribute( 'echo=0' ), 'View this Press Release' );

    echo apply_filters( 'genesis_post_title_output', "$output \n" );

}
remove_action('msdlab_title_area','genesis_do_post_title');
add_action('msdlab_title_area','msdlab_category_title');
function msdlab_category_title(){
    print '<h1 class="entry-title" itemprop="headline">'.single_cat_title( '', false ).'</h1>';
}
add_action('genesis_before_loop','msdlab_category_header');
function msdlab_category_header(){
    print category_description();
}
genesis();
