<?php
/*
Plugin Name: SlideDeck 2 - Developer Addon Package
Plugin URI: http://www.slidedeck.com/wordpress
Description: Developer level addons for SlideDeck 2
Version: 2.3.3
Author: digital-telepathy
Author URI: http://www.dtelepathy.com
License: GPL3

Copyright 2012 digital-telepathy  (email : support@digital-telepathy.com)

This file is part of SlideDeck.

SlideDeck is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

SlideDeck is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with SlideDeck.  If not, see <http://www.gnu.org/licenses/>.
*/

if( !defined( "SLIDEDECK2_DEVELOPER_DIRNAME" ) ) define( "SLIDEDECK2_DEVELOPER_DIRNAME", dirname( __FILE__ ) );
if( !defined( "SLIDEDECK2_DEVELOPER_URLPATH" ) ) define( "SLIDEDECK2_DEVELOPER_URLPATH", trailingslashit( plugins_url() ) . basename( SLIDEDECK2_DEVELOPER_DIRNAME ) );
if( !defined( "SLIDEDECK2_DEVELOPER_VERSION" ) ) define( "SLIDEDECK2_DEVELOPER_VERSION", "2.3.2" );

class SlideDeckPluginDeveloper {
    var $namespace = "slidedeck-developer";
	var $package_slug = 'tier_30';

    static $friendly_name = "SlideDeck 2 Developer Addon";

    // Additional slide types to be loaded
    var $slide_types = array();

    function __construct() {
        // Fail silently if SlideDeck core is not installed
        if( !class_exists( 'SlideDeckPlugin' ) ) {
            return false;
        }

        SlideDeckPlugin::$addons_installed[$this->package_slug] = $this->package_slug;

        $this->slidedeck_namespace = SlideDeckPlugin::$namespace;

        /**
         * Make this plugin available for translation.
         * Translations can be added to the /languages/ directory.
         */
        load_plugin_textdomain( $this->slidedeck_namespace, false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );

        add_action( 'admin_print_scripts-toplevel_page_' . SLIDEDECK2_HOOK, array( &$this, 'admin_print_scripts' ) );
        add_action( 'admin_print_styles-toplevel_page_' . SLIDEDECK2_HOOK, array( &$this, 'admin_print_styles' ) );
        add_action( 'admin_print_scripts-slidedeck-2_page_' . SLIDEDECK2_HOOK . '/lenses', array( &$this, 'admin_print_scripts' ) );
        add_action( 'admin_print_styles-slidedeck-2_page_' . SLIDEDECK2_HOOK . '/lenses', array( &$this, 'admin_print_styles' ) );
        add_action( 'init', array( &$this, 'wp_register_scripts' ), 2 );
        add_action( 'init', array( &$this, 'wp_register_styles' ), 2 );
        add_action( "slidedeck_page_lenses_route", array( &$this, 'slidedeck_page_lenses_route' ) );
        add_action( 'slidedeck_lens_management_header', array( &$this, 'slidedeck_lens_management_header' ) );
        add_action( 'slidedeck_lens_manage_entry_actions', array( &$this, 'slidedeck_lens_manage_entry_actions' ), 10, 2 );

        add_filter( 'slidedeck_get_slide_types', array( &$this, 'slidedeck_get_slide_types' ) );
        add_filter( 'slidedeck_create_custom_slidedeck_block', array( &$this, 'slidedeck_create_custom_slidedeck_block' ), 30 );

        // Only load additional Slide Types if the Custom SlideDeck SlideDeckSlide class exists
        if( class_exists( "SlideDeckSlideModel" ) ) {
            $slide_type_files = (array) glob( SLIDEDECK2_DEVELOPER_DIRNAME . '/slides/*/slide.php' );
            foreach( (array) $slide_type_files as $filename ) {
                if( is_readable( $filename ) ) {
                    include_once( $filename );

                    $slug = basename( dirname( $filename ) );
                    $classname = slidedeck2_get_classname_from_filename( dirname( $filename ) );
                    $prefix_classname = "SlideDeckSlideType_{$classname}";
                    if( class_exists( $prefix_classname ) ) {
                        $this->slide_types[$slug] = new $prefix_classname;
                    }
                }
            }
        }
    }

    /**
     * Load JavaScript for the admin options page
     *
     * @uses wp_enqueue_script()
     */
    function admin_print_scripts() {
        wp_enqueue_script( "{$this->namespace}-admin" );
        wp_enqueue_script( 'media-upload' );
        wp_enqueue_script( 'slidedeck-fancy-form' );
        wp_enqueue_script( 'codemirror' );
        wp_enqueue_script( 'codemirror-mode-css' );
        wp_enqueue_script( 'codemirror-mode-javascript' );
        wp_enqueue_script( 'codemirror-mode-clike' );
        wp_enqueue_script( 'codemirror-mode-php' );
    }

    /**
     * Load stylesheets for the admin pages
     *
     * @uses wp_enqueue_style()
     * @uses SlideDeckPlugin::is_plugin()
     */
    function admin_print_styles() {
        global $SlideDeckPlugin;

        wp_enqueue_style( "{$this->namespace}-admin" );

        if( $SlideDeckPlugin->is_plugin() ) {
            wp_enqueue_style( 'codemirror' );
            wp_enqueue_style( 'codemirror-theme-default' );
            wp_enqueue_style( 'jquery-minicolors' );
        }
    }

    /**
     * Initialization function to hook into the WordPress init action
     *
     * Instantiates the class on a global variable and sets the class, actions
     * etc. up for use.
     */
    static function instance() {
        global $SlideDeckPluginDeveloper;

        $slidedeck2_version = defined( 'SLIDEDECK2_VERSION' ) ? SLIDEDECK2_VERSION : "2.0";

        if( version_compare( $slidedeck2_version, '2.1', ">=" ) ) {
            // Only instantiate the Class if it hasn't been already
            if( !isset( $SlideDeckPluginDeveloper ) ) $SlideDeckPluginDeveloper = new SlideDeckPluginDeveloper();
        }
    }

    /**
     * SlideDeck Lens Copy View
     *
     * Page to enter new SlideDeck lens name and slug. This is an interim page for copying a lens.
     *
     * @uses current_user_can()
     * @uses wp_die()
     * @uses SlideDeckPlugin::action()
     * @uses slidedeck2_sanitize()
     * @uses SlideDeckLens::copy_inc()
     */
    function page_lenses_copy() {
        global $SlideDeckPlugin;

        if( !current_user_can( 'upload_files' ) )
            wp_die( __( "You do not have privileges to access this page", $this->slidedeck_namespace ) );

        if( !isset( $_REQUEST['lens'] ) )
            wp_die( '<h3>' . __( "You did not specify a lens", $this->slidedeck_namespace ) . '</h3><p><a href="' . $this->action( '/lenses' ) . '">' . __( "Return to Manage Lenses", $this->slidedeck_namespace ) . '</a></p>' );

        $lens_slug = slidedeck2_sanitize( $_REQUEST['lens'] );
        $lens = $SlideDeckPlugin->Lens->get( $lens_slug );
        $namespace = $this->slidedeck_namespace;

        $new_lens_slug_base = isset( $_REQUEST['new_lens_slug'] ) ? $_REQUEST['new_lens_slug'] : $lens['slug'];
        $new_lens_name_base = isset( $_REQUEST['new_lens_name'] ) ? $_REQUEST['new_lens_name'] : $lens['meta']['name'];

        // Find an incremented value to use as a suffix for what copy of the lens this would be
        $copy_inc = $SlideDeckPlugin->Lens->copy_inc( $new_lens_slug_base );

        // Suggested new lens name
        $new_lens_name = $new_lens_name_base;
        if( $copy_inc > 0 ) $new_lens_name .= " (Copy $copy_inc)";
        // Suggested new lens slug
        $new_lens_slug = "$new_lens_slug_base";
        if( $copy_inc > 0 ) $new_lens_slug .= "-$copy_inc";

        $create_or_copy = isset( $_REQUEST['create_or_copy'] ) && $_REQUEST['create_or_copy'] == "create" ? 'create' : 'copy';

        include( SLIDEDECK2_DEVELOPER_DIRNAME . '/views/copy.php' );
    }

    /**
     * SlideDeck Lens Editor View
     *
     * Page to edit SlideDeck lenses. This page will load the requested lens' primary lens CSS
     * file by default and can also load other lens CSS and JavaScript files for editing as well.
     * Lenses are intelligently displayed to prevent modification of protected lens files.
     *
     * @uses current_user_can()
     * @uses wp_die()
     * @uses SlideDeckPlugin::action()
     * @uses SlideDeckLens::get()
     * @uses SlideDeckLens::is_protected()
     * @uses esc_textarea()
     * @uses SlideDeckLens::get_content()
     */
    function page_lenses_edit() {
        global $SlideDeckPlugin;

        // Die if user cannot edit themes
        if( !current_user_can( 'upload_files' ) )
            wp_die( __( "You do not have privileges to access this page", $this->slidedeck_namespace ) );

        $lens_slug = array_key_exists( 'slidedeck-lens', $_REQUEST ) ? $_REQUEST['slidedeck-lens'] : "";

        // Redirect back to lens management page if no lens was specified
        if( empty( $lens_slug ) )
            wp_die( '<h3>' . __( "You did not specify a lens", $this->slidedeck_namespace ) . '</h3><p><a href="' . $this->action( '/lenses' ) . '">' . __( "Return to Manage Lenses", $this->slidedeck_namespace ) . '</a></p>' );

        // Namespace
        $namespace = $this->slidedeck_namespace;
        // This lens
        $lens = $SlideDeckPlugin->Lens->get( $lens_slug );
        // All lenses for drop-down selection
        $lenses = $SlideDeckPlugin->Lens->get();

        // Editable lens files and their labels
        $lens_file_labels = array(
            'lens.css' => __( "Lens Stylesheet", $namespace ),
            'lens.ie.css' => __( "Lens Stylesheet (IE)", $namespace ),
            'lens.ie7.css' => __( "Lens Stylesheet (IE 7)", $namespace ),
            'lens.ie8.css' => __( "Lens Stylesheet (IE 8)", $namespace ),
            'lens.php' => __( "Lens PHP Logic", $namespace ),
            'lens.js' => __( "Lens JavaScript", $namespace ),
            'lens.admin.js' => __( "Lens Admin JavaScript", $namespace ),
            'template.thtml' => __( "Default Slide Template", $namespace )
        );
        foreach( $SlideDeckPlugin->SlideDeck->slide_types as $name => $label ) {
            $lens_file_labels["template.{$name}.thtml"] = __( "{$label} Template", $namespace );
        }

        $sources = $SlideDeckPlugin->get_sources();

        // Check for editable source templates
        foreach( $sources as $source ) {
            $lens_file_labels["template.source.{$source->name}.thtml"] = __( "{$source->label} Template", $namespace );
        }

        // Get the lens file to load from the lens itself or the requested file
        $lens_filename = isset( $_REQUEST['filename'] ) ? dirname( $lens['files']['css'] ) . "/" . $_REQUEST['filename'] : $lens['files']['css'];

        // Check writable status of lens
        $read_only = !is_writable( $lens_filename );

        // Check if this is a protected lens and set it to un-writable
        if( $SlideDeckPlugin->Lens->is_protected( $lens_filename ) )
            $read_only = true;

        // Make sure that the lens filename being requested for editing is a valid file to edit
        if( !in_array( basename( $lens_filename ), array_keys( $lens_file_labels ) ) )
            wp_die( '<h3>' . __( "Invalid file specified", $this->slidedeck_namespace ) . '</h3><p>' . __( "The lens file you requested is not a valid editable file.", $this->slidedeck_namespace ) . '</p><p><a href="' . $SlideDeckPlugin->action( '/lenses' ) . '">' . __( "Return to Manage Lenses", $this->slidedeck_namespace ) . '</a></p>' );

        // Raw CSS content of the lens.css file (without the meta comment)
        $lens_file_content = esc_textarea( $SlideDeckPlugin->Lens->get_content( $lens_filename, ( basename( $lens_filename ) == basename( $lens['files']['meta'] ) ) ) );

        // Get all editable lens files
        $lens_files = array();
        foreach( glob( dirname( $lens['files']['css'] ) . "/*" ) as $file )
            if( in_array( basename( $file ), array_keys( $lens_file_labels ) ) )
                $lens_files[] = $file;

        include( SLIDEDECK2_DEVELOPER_DIRNAME . '/views/edit.php' );
    }

    /**
     * Hook into slidedeck_lens_management_header action
     *
     * Output button to create a new lens on the Lens Management page
     *
     * @param object $is_writeable Object that specifies if the custom lens folder is writable
     */
    function slidedeck_lens_management_header( $is_writable ) {
        include( SLIDEDECK2_DEVELOPER_DIRNAME . '/views/_lens-management-header.php' );
    }

    /**
     * Hook into slidedeck_get_slide_types filter
     *
     * Adds additional slide types to the custom SlideDeck content source
     *
     * @param array $slide_types Available slide types
     *
     * @return array
     */
    function slidedeck_get_slide_types( $slide_types ) {
        // Loop through this plugin's slide type additions
        foreach( $this->slide_types as $slide_type_key => $slide ) {
            // Only add it to the array if it isn't defined already
            if( !isset( $slide_types[$slide_type_key] ) ) {
                // Add the additional slide type to the available slide types array
                $slide_types[$slide_type_key] = $this->slide_types[$slide_type_key];
            }
        }

        return $slide_types;
    }

    /**
     * Hook into slidedeck_create_custom_slidedeck_block filter
     *
     * Outputs the create custom slidedeck block on the manage page, replacing the default
     * with one that actually links to the creation of a Custom SlideDeck since this plugin
     * add-on adds that capability.
     *
     * @param string $html The HTML to be output
     *
     * @return string
     */
    function slidedeck_create_custom_slidedeck_block( $html ) {
        ob_start();
            include( SLIDEDECK2_DEVELOPER_DIRNAME . '/views/_create-custom-slidedeck-block.php' );
            $html = ob_get_contents();
        ob_end_clean();

        return $html;
    }

    /**
     * Hook into slidedeck_lens_management_entry_bottom action
     *
     * Output button to create a new lens on the Lens Management page
     *
     * @param array $lens The lens currently being looped through
     * @param object $is_writeable Object that specifies if the custom lens folder is writable
     */
    function slidedeck_lens_manage_entry_actions( $lens, $is_writable ) {
        $namespace = $this->slidedeck_namespace;

        include( SLIDEDECK2_DEVELOPER_DIRNAME . '/views/_lens-manage-entry-actions.php' );
    }

    /**
     * Hook into slidedeck_page_lenses_route action
     *
     * Routes actions for additional lens management/modification pages
     *
     * @param string $action The Action being requested
     *
     * @uses SlideDeckPluginDeveloper::page_lenses_edit()
     * @uses SlideDeckPluginDeveloper::page_lenses_copy()
     * @uses SlideDeckPluginDeveloper::page_lenses_add()
     */
    function slidedeck_page_lenses_route( $action ) {
        switch( $action ) {
            case "edit":
                $this->page_lenses_edit();
            break;

            case "copy":
                $this->page_lenses_copy();
            break;
        }
    }

    /**
     * Register scripts used by this plugin for enqueuing elsewhere
     *
     * @uses wp_register_script()
     */
    function wp_register_scripts() {
        // Admin JavaScript
        wp_register_script( "{$this->namespace}-admin", SLIDEDECK2_DEVELOPER_URLPATH . "/js/admin" . ( SLIDEDECK2_ENVIRONMENT == 'development' ? '.dev' : '' ) . ".js", array( 'jquery', 'media-upload', 'slidedeck-fancy-form', 'simplemodal', 'slidedeck-admin', 'jquery-masonry' ), SLIDEDECK2_DEVELOPER_VERSION, true );

        // CodeMirror JavaScript Library
        wp_register_script( "codemirror", SLIDEDECK2_DEVELOPER_URLPATH . "/js/codemirror/codemirror.js", array(), '2.25', true );
        wp_register_script( "codemirror-mode-css", SLIDEDECK2_DEVELOPER_URLPATH . "/js/codemirror/mode/css.js", array( 'codemirror' ), '2.25', true );
        wp_register_script( "codemirror-mode-htmlmixed", SLIDEDECK2_DEVELOPER_URLPATH . "/js/codemirror/mode/htmlmixed.js", array( 'codemirror' ), '2.25', true );
        wp_register_script( "codemirror-mode-javascript", SLIDEDECK2_DEVELOPER_URLPATH . "/js/codemirror/mode/javascript.js", array( 'codemirror' ), '2.25', true );
        wp_register_script( "codemirror-mode-clike", SLIDEDECK2_DEVELOPER_URLPATH . "/js/codemirror/mode/clike.js", array( 'codemirror' ), '2.25', true );
        wp_register_script( "codemirror-mode-php", SLIDEDECK2_DEVELOPER_URLPATH . "/js/codemirror/mode/php.js", array( 'codemirror', 'codemirror-mode-clike' ), '2.25', true );
    }

    /**
     * Register styles used by this plugin for enqueuing elsewhere
     *
     * @uses wp_register_style()
     */
    function wp_register_styles() {
        // Developer Tier Admin Stylesheet
        wp_register_style( "{$this->namespace}-admin", SLIDEDECK2_DEVELOPER_URLPATH . "/css/admin.css", array(), '2.1', 'screen' );
        // CodeMirror Library
        wp_register_style( "codemirror", SLIDEDECK2_DEVELOPER_URLPATH . "/css/codemirror.css", array(), '2.25', 'screen' );
    }
}

// SlideDeck Personal should load, then Lite, then Professional, then Developer
add_action( 'plugins_loaded', array( 'SlideDeckPluginDeveloper', 'instance' ), 30 );
