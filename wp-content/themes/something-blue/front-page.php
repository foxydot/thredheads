<?php
remove_action('genesis_after_header','msdlab_do_title_area');



//remove sidebars (jsut in case)
remove_all_actions('genesis_sidebar');
remove_all_actions('genesis_sidebar_alt');
//remove loop
remove_all_actions('genesis_loop');

/**
 * hero + 3 widgets
 */
//add the callout
//add_action('genesis_after_header','msdlab_callout');
//add the hero
add_action('genesis_after_header','msdlab_hero');
//move footer and add three homepage widgets
remove_action('genesis_before_footer','genesis_footer_widget_areas');
add_action('genesis_before_footer','msdlab_homepage_widgets');
/**
 * long scrollie
 */
//remove_all_actions('genesis_loop');
//add_action('genesis_loop','msd_scrollie_page');

genesis();