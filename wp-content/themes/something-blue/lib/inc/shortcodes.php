<?php
add_shortcode('product-sidebar','product_sidebar_function');
function product_sidebar_function(){
    $ret = '<div class="product-sidebar pull-right col-md-4">';
    $ret .= wp_nav_menu(array('menu'=>'Shirt Tools'));
    $ret .= '</div>';
    return $ret;
}
add_filter('product_images_box_filters','do_shortcode');
add_shortcode('product-images-box', 'product_images_box_function');
function product_images_box_function($atts,$content){
    $ret = '<div class="product-images-box container"><div class="row">';
    $ret .= apply_filters('product_images_box_filters',$content);
    $ret .= '</div></div>';
    return $ret;
}
add_shortcode('button','msdlab_button_function');
function msdlab_button_function($atts, $content = null){	
	extract( shortcode_atts( array(
      'url' => null,
	  'target' => '_self'
      ), $atts ) );
	$ret = '<div class="button-wrapper">
<a class="button" href="'.$url.'" target="'.$target.'">'.remove_wpautop($content).'</a>
</div>';
	return $ret;
}
add_shortcode('hero','msdlab_landing_page_hero');
function msdlab_landing_page_hero($atts, $content = null){
	$ret = '<div class="hero">'.remove_wpautop($content).'</div>';
	return $ret;
}
add_shortcode('callout','msdlab_landing_page_callout');
function msdlab_landing_page_callout($atts, $content = null){
	$ret = '<div class="callout">'.remove_wpautop($content).'</div>';
	return $ret;
}
function column_shortcode($atts, $content = null){
	extract( shortcode_atts( array(
	'cols' => '3',
	'position' => '',
	), $atts ) );
	switch($cols){
		case 5:
			$classes[] = 'one-fifth';
			break;
		case 4:
			$classes[] = 'one-fouth';
			break;
		case 3:
			$classes[] = 'one-third';
			break;
		case 2:
			$classes[] = 'one-half';
			break;
	}
	switch($position){
		case 'first':
		case '1':
			$classes[] = 'first';
		case 'last':
			$classes[] = 'last';
	}
	return '<div class="'.implode(' ',$classes).'">'.$content.'</div>';
}

add_shortcode('columns','column_shortcode');

function good_advice_sitemap() { 
        $ret = '
            <div class="archive-page">

                <h4>'. _e( 'Pages:', 'genesis' ).'</h4>
                <ul>
                    '. wp_list_pages( 'sort_column=menu_order&title_li=' ).'
                </ul>
            </div><!-- end .archive-page-->

            <div class="archive-page">

                <h4>'. _e( 'Categories:', 'genesis' ).'</h4>
                <ul>
                    '. wp_list_categories( 'sort_column=name&title_li=' ).'
                </ul>

            </div><!-- end .archive-page-->';
        return $ret;
}

add_shortcode('sitemap','good_advice_sitemap');