<?php
/**
 * Widget template. This template can be overriden using the "sp_template_image-widget_widget.php" filter.
 * See the readme.txt file for more info.
 */

// Block direct requests
if ( !defined('ABSPATH') )
	die('-1');

echo $before_widget;

echo $this->get_image_html( $instance, true );

if ( !empty( $title ) ) { echo $before_title . $title . $after_title; }

if ( !empty( $description ) ) {
	echo '<div class="'.$this->widget_options['classname'].'-description" >';
	echo wpautop( $description );
	echo "</div>";
}
if ( $link ) {
	$linktext = $linktext != ''?$linktext:'Read More';
	echo '<div class="link"><a class="'.$this->widget_options['classname'].'-link readmore" href="'.$link.'" target="'.$linktarget.'">'.$linktext.' ></a><div class="clear"></div></div>';
}
echo '<div class="clear"></div>';
echo $after_widget;