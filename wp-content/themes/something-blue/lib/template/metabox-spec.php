<?php
global $subtitle_metabox;
$subtitle_metabox = new WPAlchemy_MetaBox(array
(
	'id' => '_subtitle',
	'title' => 'Subtitle',
	'types' => array('post','page'),
    'context' => 'normal', // same as above, defaults to "normal"
    'priority' => 'high', // same as above, defaults to "high"
	'template' => get_stylesheet_directory() . '/lib/template/subtitle-meta.php',
	'autosave' => TRUE,
    'mode' => WPALCHEMY_MODE_EXTRACT, // defaults to WPALCHEMY_MODE_ARRAY
    'prefix' => '_msdlab_' // defaults to NULL
));

/* eof */