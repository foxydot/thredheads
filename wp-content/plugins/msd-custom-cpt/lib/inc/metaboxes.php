<?php 
global $location_info,$client_info,$additional_files;

$client_info = new WPAlchemy_MetaBox(array
        (
            'id' => '_Client_information',
            'title' => 'Client Information',
            'types' => array('project'),
            'context' => 'normal',
            'priority' => 'high',
            'template' => WP_PLUGIN_DIR.'/'.plugin_dir_path('msd-custom-cpt/msd-custom-cpt.php').'lib/template/client-information.php',
            'autosave' => TRUE,
            'mode' => WPALCHEMY_MODE_EXTRACT, // defaults to WPALCHEMY_MODE_ARRAY
            'prefix' => '_client_' // defaults to NULL
        ));
$location_info = new WPAlchemy_MetaBox(array
		(
			'id' => '_location_information',
			'title' => 'Location Information',
			'types' => array('location','project'),
			'context' => 'normal',
			'priority' => 'high',
			'template' => WP_PLUGIN_DIR.'/'.plugin_dir_path('msd-custom-cpt/msd-custom-cpt.php').'lib/template/location-information.php',
			'autosave' => TRUE,
			'mode' => WPALCHEMY_MODE_EXTRACT, // defaults to WPALCHEMY_MODE_ARRAY
			'prefix' => '_location_' // defaults to NULL
		));
$additional_files = new WPAlchemy_MetaBox(array
        (
            'id' => '_additional_files',
            'title' => 'Additional Files',
            'types' => array('project'),
            'context' => 'normal',
            'priority' => 'high',
            'template' => WP_PLUGIN_DIR.'/'.plugin_dir_path('msd-custom-cpt/msd-custom-cpt.php').'lib/template/additional-files.php',
            'autosave' => TRUE,
            'mode' => WPALCHEMY_MODE_EXTRACT, // defaults to WPALCHEMY_MODE_ARRAY
            'prefix' => '_files_' // defaults to NULL
        ));