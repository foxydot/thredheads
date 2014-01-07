<?php
// THIS FILE IS CURRENTLY NOT IN USE.

die('This file (PluginBuddy FeaturedBuddy public.php is disabled.');







if ( !class_exists( "PluginBuddyFeaturedBuddy_public" ) ) {
    class PluginBuddyFeaturedBuddy_public {
		function PluginBuddyFeaturedBuddy_public(&$parent) {
			$this->_parent = &$parent;
			
			echo 'public stuff...';
			
			// Example to call the function display_page when post content is being displayed on a page:
			// add_filter('the_content', array(&$this, 'display_page'));
		}
	}
	$PluginBuddyFeaturedBuddy_public = new PluginBuddyFeaturedBuddy_public($this);
}
?>