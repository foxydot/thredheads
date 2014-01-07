<?php
/*
Plugin Name: SlideDeck 2 - Professional Addon Package
Plugin URI: http://www.slidedeck.com/wordpress
Description: Professional level addons for SlideDeck 2
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

if( !defined( "SLIDEDECK2_PROFESSIONAL_DIRNAME" ) ) define( "SLIDEDECK2_PROFESSIONAL_DIRNAME", dirname( __FILE__ ) );
if( !defined( "SLIDEDECK2_PROFESSIONAL_URLPATH" ) ) define( "SLIDEDECK2_PROFESSIONAL_URLPATH", trailingslashit( plugins_url() ) . basename( SLIDEDECK2_PROFESSIONAL_DIRNAME ) );
if( !defined( "SLIDEDECK2_PROFESSIONAL_VERSION" ) ) define( "SLIDEDECK2_PROFESSIONAL_VERSION", "2.3.2" );

class SlideDeckPluginProfessional {
    var $namespace = "slidedeck-professional";
	var $package_slug = 'tier_20';
    
    static $friendly_name = "SlideDeck 2 Professional Addon";
    
    // Additional source types loaded by this plugin
    var $sources = array();
    
    function __construct() {
        global $SlideDeckPlugin;
        
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
        add_action( 'init', array( &$this, 'wp_register_scripts' ), 2 );
        add_action( 'init', array( &$this, 'wp_register_styles' ), 2 );
        add_filter( 'slidedeck_create_custom_slidedeck_block', array( &$this, 'slidedeck_create_custom_slidedeck_block' ), 20 );
        add_filter( 'slidedeck_get_lenses', array( &$this, 'slidedeck_get_lenses' ), 9, 2 );
        add_filter( 'slidedeck_get_slide_types', array( &$this, 'slidedeck_get_slide_types' ) );
        add_action( 'slidedeck_after_options_group_wrapper', array( &$this, 'slidedeck_custom_css_field' ) );
        add_action( 'slidedeck_after_save', array( &$this, 'slidedeck_save_custom_css_field' ), 10, 4 );
        add_filter( 'slidedeck_footer_styles', array( &$this, 'slidedeck_add_custom_css' ), 22, 2 );
        
        // Remove After Sources Filter for Source Upsell
        remove_filter( "{$this->slidedeck_namespace}_source_modal_after_sources", array( $SlideDeckPlugin, 'slidedeck_source_modal_after_sources' ) );

        // Only load additional Slide Types if the Custom SlideDeck SlideDeckSlide class exists
        if( class_exists( "SlideDeckSlideModel" ) ) {
            $slide_type_files = (array) glob( SLIDEDECK2_PROFESSIONAL_DIRNAME . '/slides/*/slide.php' );
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
        
        // Stock Lenses that come with SlideDeck Professional distribution
        $lens_files = glob( SLIDEDECK2_PROFESSIONAL_DIRNAME . '/lenses/*/lens.php' );
        
        // Load all the custom Lens types
        foreach( (array) $lens_files as $filename ) {
            if( is_readable( $filename ) ) {
                $classname = slidedeck2_get_classname_from_filename( dirname( $filename ) );
                $prefix_classname = "SlideDeckLens_{$classname}";
                $slug = basename( dirname( $filename ) );
                
                if( !class_exists( $prefix_classname ) ) {
                    include_once ( $filename );
                    $SlideDeckPlugin->installed_lenses[] = $slug;
                }
                
                if( class_exists( $prefix_classname ) ) {
                    $SlideDeckPlugin->lenses[$classname] = new $prefix_classname;
                }
            }
        }

        $source_files = (array) glob( SLIDEDECK2_PROFESSIONAL_DIRNAME . '/sources/*/source.php' );
        foreach( (array) $source_files as $filename ) {
            if( is_readable( $filename ) ) {
                include_once ($filename);

                $slug = basename( dirname( $filename ) );
                $classname = slidedeck2_get_classname_from_filename( dirname( $filename ) );
                $prefix_classname = "SlideDeckSource_{$classname}";
                if( class_exists( $prefix_classname ) ) {
                    $SlideDeckPlugin->sources[$slug] = new $prefix_classname;
                } elseif( class_exists( "{$prefix_classname}Content" ) && $slug == "custom" ) {
                    $custom_content_classname = "{$prefix_classname}Content";
                    $SlideDeckPlugin->sources[$slug] = new $custom_content_classname;
                }
            }
        }

    }

    static function activate() {
        $installed_version = get_option( "slidedeck2_professional_version", "2.0" );
        
        if( !defined( 'SLIDEDECK2_DIRNAME' ) ) {
            die( "<strong>ERROR:</strong> SlideDeck 2 Personal is required for this plugin to be installed" );
        }
        
        update_option( "slidedeck2_professional_version", SLIDEDECK2_PROFESSIONAL_VERSION );
    }
    
    /**
     * Load JavaScript for the admin options page
     *
     * @uses wp_enqueue_script()
     */
    function admin_print_scripts() {
        wp_enqueue_script( "{$this->namespace}-admin" );
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
        }
    }

    function get_custom_css( $id, $prepend = false ) {
        global $SlideDeckPlugin;

        $custom_css = get_post_meta( $id, $SlideDeckPlugin->namespace . '_custom_css', true );

        if( $prepend ) {
            /**
             * Add a dummy comma. The leading comma ensures that the
             * preg_match does its job more reliably. We remove it after.
             */
            $custom_css = ',' . $custom_css;
            // Process the CSS and append the deck ID
            $custom_css = preg_replace( '/([,|\}][\s$]*)([\.#]?-?[_a-zA-Z]+[_a-zA-Z0-9-]*)/m', '$1.' . $SlideDeckPlugin->namespace . '-custom-css-wrapper-' . $id . ' $2', $custom_css );
            // remove the dummy comma
            $custom_css = ltrim( $custom_css, ',' );
        }

        return $custom_css;
    }

    /**
     * Initialization function to hook into the WordPress init action
     * 
     * Instantiates the class on a global variable and sets the class, actions
     * etc. up for use.
     */
    static function instance() {
        global $SlideDeckPluginProfessional;
        
        $slidedeck2_version = defined( 'SLIDEDECK2_VERSION' ) ? SLIDEDECK2_VERSION : "2.0";
        
        if( version_compare( $slidedeck2_version, '2.1', ">=" ) ) {
            // Only instantiate the Class if it hasn't been already
            if( !isset( $SlideDeckPluginProfessional ) ) $SlideDeckPluginProfessional = new SlideDeckPluginProfessional();
        }
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
            include( SLIDEDECK2_PROFESSIONAL_DIRNAME . '/views/_create-custom-slidedeck-block.php' );
            $html = ob_get_contents();
        ob_end_clean();
        
        return $html;
    }

    function slidedeck_custom_css_field( $slidedeck ) {
        global $SlideDeckPlugin;

        $custom_css = $this->get_custom_css( $slidedeck['id'] );
        $help_message = sprintf(__('See  %1$sour knowlegde base%2$s for tips on using this section and writing selectors.'), "<a href='http://dtelepathy.zendesk.com/entries/21769140' target='_blank'>", '</a>');
        include( SLIDEDECK2_PROFESSIONAL_DIRNAME . '/views/_custom-css-block.php' );
    }

    function slidedeck_save_custom_css_field( $id, $data, $deprecated, $sources ) {
        global $SlideDeckPlugin;

        $custom_css = $_REQUEST['custom_css'];
        update_post_meta( $id, $SlideDeckPlugin->namespace . '_custom_css', $custom_css );
    }

    function slidedeck_add_custom_css( $styles, $slidedeck ) {
        $custom_css = $this->get_custom_css( $slidedeck['id'], true );
        return $styles . $custom_css;
    }
    
    /**
     * Hook into slidedeck_get_lenses filter
     * 
     * Add the Professional tier lenses to the lenses array
     * 
     * @param array $lenses Available slide lenses
     * @param string $slug Slug of lens
     * 
     * @uses $SlideDeckPlugin->Lens->get_meta()
     * 
     * @return array
     */
    function slidedeck_get_lenses( $lenses, $slug ) {
        global $SlideDeckPlugin;
        
        $all_lens_files = array();
        $folders = !empty( $slug ) ? $slug : "*";
        
        // Get stock lens files that come with SlideDeck distribution
        $lens_files = (array) glob( SLIDEDECK2_PROFESSIONAL_DIRNAME . '/lenses/' . $folders . '/lens.json' );
        
        // Loop through each lens file to build an array of lenses
        foreach( (array) $lens_files as $lens_file ) {
            $key = basename( dirname( $lens_file ) );
            $all_lens_files[$key] = $lens_file;
        }
        
        // Append each lens to the $lenses array including the lens' meta
        foreach ( (array) array_values( $all_lens_files ) as $lens_file ) {
            if ( is_readable( $lens_file ) ) {
                $lens_meta = $SlideDeckPlugin->Lens->get_meta( $lens_file );
                $lenses[$lens_meta['slug']] = $lens_meta;
            }
        }
        
        return $lenses;
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
     * Register scripts used by this plugin for enqueuing elsewhere
     *
     * @uses wp_register_script()
     */
    function wp_register_scripts() {
        // Admin JavaScript
        wp_register_script( "{$this->namespace}-admin", SLIDEDECK2_PROFESSIONAL_URLPATH . "/js/admin" . ( SLIDEDECK2_ENVIRONMENT == 'development' ? '.dev' : '' ) . ".js", array( 'jquery', 'slidedeck-admin' ), SLIDEDECK2_PROFESSIONAL_VERSION, true );
        
        // CodeMirror JavaScript Library
        wp_register_script( "codemirror", SLIDEDECK2_PROFESSIONAL_URLPATH . "/js/codemirror/codemirror.js", array(), '2.25', true );
        wp_register_script( "codemirror-mode-css", SLIDEDECK2_PROFESSIONAL_URLPATH . "/js/codemirror/mode/css.js", array( 'codemirror' ), '2.25', true );
        wp_register_script( "codemirror-mode-htmlmixed", SLIDEDECK2_PROFESSIONAL_URLPATH . "/js/codemirror/mode/htmlmixed.js", array( 'codemirror' ), '2.25', true );
        wp_register_script( "codemirror-mode-javascript", SLIDEDECK2_PROFESSIONAL_URLPATH . "/js/codemirror/mode/javascript.js", array( 'codemirror' ), '2.25', true );
        wp_register_script( "codemirror-mode-clike", SLIDEDECK2_PROFESSIONAL_URLPATH . "/js/codemirror/mode/clike.js", array( 'codemirror' ), '2.25', true );
        wp_register_script( "codemirror-mode-php", SLIDEDECK2_PROFESSIONAL_URLPATH . "/js/codemirror/mode/php.js", array( 'codemirror', 'codemirror-mode-clike' ), '2.25', true );
    }

    /**
     * Register styles used by this plugin for enqueuing elsewhere
     *
     * @uses wp_register_style()
     */
    function wp_register_styles() {
        // Professional Tier Admin Stylesheet
        wp_register_style( "{$this->namespace}-admin", SLIDEDECK2_PROFESSIONAL_URLPATH . "/css/admin.css", array(), '2.1', 'screen' );
        // CodeMirror Library
        wp_register_style( "codemirror", SLIDEDECK2_PROFESSIONAL_URLPATH . "/css/codemirror.css", array(), '2.25', 'screen' );
    }

}

register_activation_hook( __FILE__, array( 'SlideDeckPluginProfessional', 'activate' ) );

// SlideDeck Personal should load, then Lite, then Professional, then Developer
add_action( 'plugins_loaded', array( 'SlideDeckPluginProfessional', 'instance' ), 20 );
