<?php
class SlideDeckSource_Nextgengallery extends SlideDeck {
    var $label = "NextGEN Gallery";
    var $name = "nextgengallery";
    var $default_lens = "tool-kit";
    var $taxonomies = array( 'images' );
    
    var $default_options = array(
        'cache_duration' => 1800 // seconds
    );
    
    var $options_model = array(
        'Setup' => array(
            'ngg_gallery_or_album' => array(
                'type' => 'radio',
                'data' => "string",
                'value' => 'gallery',
            ),
            'ngg_gallery' => array(
                'value' => 1
            ),
            'ngg_album' => array(
                'value' => 1
            )
        ),
        'Content' => array(
            'show-readmore' => array(
                'value' => false,
            )
        )
    );
            
    function add_hooks() {
        global $SlideDeckPlugin;
        $slidedeck_namespace = $SlideDeckPlugin->namespace;
        
        add_action( "{$slidedeck_namespace}_form_content_source", array( &$this, "slidedeck_form_content_source" ), 10, 2 );

    }

    function get_galleries_in_album( $album_id ) {
        global $wpdb;

        $sql = "
            SELECT sortorder FROM {$wpdb->prefix}ngg_album
            WHERE id = $album_id
        ";
        $result = $wpdb->get_results( $sql, ARRAY_A );

        foreach( $result as $gallery ) {
            $galleries = unserialize($gallery['sortorder']);
        }

        return $galleries;
    }

    function get_gallery_info( $gallery_id ) {
        global $wpdb;

        $sql = "
            SELECT path, author, pageid FROM {$wpdb->prefix}ngg_gallery
            WHERE gid = $gallery_id
        ";
        return $wpdb->get_results( $sql, ARRAY_A );
    }

    function get_images_by_gallery_id( $gallery_id ) {
        global $wpdb;

        $gallery_meta = $this->get_gallery_info( $gallery_id );
        
        $sql = "
            SELECT * FROM {$wpdb->prefix}ngg_pictures
            WHERE exclude = 0 AND galleryid = $gallery_id
            ORDER BY sortorder
        ";
        $results = $wpdb->get_results( $sql, ARRAY_A );
        foreach( $results as &$result ) {
            $result["meta_data"] = unserialize( $result["meta_data"] );
            $result["gallery_meta"] = reset( $gallery_meta );
        }
        return $results;
    }

    /**
     * Load all slides associated with this SlideDeck
     * 
     * @param integer $slidedeck_id The ID of the SlideDeck being loaded
     * 
     * @uses WP_Query
     * @uses get_the_title()
     * @uses maybe_unserialize()
     */
    function get_slides_nodes( $slidedeck ) {
        $images = array();

        switch( $slidedeck['options']['ngg_gallery_or_album'] ) {
            case 'gallery':
                if( $slidedeck["options"]["ngg_gallery"] != "none" ) {
                    $images = $this->get_images_by_gallery_id( $slidedeck["options"]["ngg_gallery"] );
                }
            break;
            case 'album':
                if( $slidedeck["options"]["ngg_album"] != 'none' ) {
                    $galleries = $this->get_galleries_in_album( $slidedeck["options"]["ngg_album"] );
                    if ( $galleries ) {
                        foreach( $galleries as $gallery ) {
                            $gallery_images = $this->get_images_by_gallery_id( $gallery );
                            $images = array_merge( $images, $gallery_images );
                        }
                    }
                }

            break;
        }

        foreach( $images as $index => $entry ) {
            $images[ $index ]['title'] = $entry['alttext'];
            $images[ $index ]['created_at'] = strtotime( $entry['imagedate'] );
            $images[ $index ]['image'] = site_url() . '/' . $entry['gallery_meta']['path'] . '/' . $entry['filename'];
            $images[ $index ]['thumbnail'] = site_url() . '/' . $entry['gallery_meta']['path'] . '/thumbs/thumbs_' . $entry['filename'];
            $images[ $index ]['permalink'] = site_url() . '/' . $entry['gallery_meta']['path'] . '/' . $entry['filename'];

            $images[ $index ]['author_name'] = get_userdata( $entry['gallery_meta']['author'] )->display_name;
            $images[ $index ]['author_avatar'] = slidedeck2_get_avatar( get_userdata( $entry['gallery_meta']['author'] )->user_email ) ;
            $images[ $index ]['author_url'] = get_userdata( $entry['gallery_meta']['author'] )->user_url;

            // If the gallery is associated with a WP Page id, set permalink to that...
            if( $entry['gallery_meta']['pageid'] != 0 ) {
                $images[ $index ]['permalink'] = get_page_link( $entry['gallery_meta']['pageid'] ) ;
            }
        }

        return $images;
    }

    /**
     * Register scripts used by Decks
     * 
     * @uses wp_register_script()
     */
    function register_scripts() {
        // Fail silently if this is not a sub-class instance
        if( !isset( $this->name ) ) {
            return false;
        }
        
        wp_register_script( "slidedeck-deck-{$this->name}-admin", SLIDEDECK2_PROFESSIONAL_URLPATH . '/sources/' . $this->name . '/source.js', array( 'jquery', 'slidedeck-admin' ), SLIDEDECK2_PROFESSIONAL_VERSION, true );
    }
        
    /**
     * Register styles used by Decks
     * 
     * @uses wp_register_style()
     */
    function register_styles() {
        // Fail silently if this is not a sub-class instance
        if( !isset( $this->name ) ) {
            return false;
        }
        
        wp_register_style( "slidedeck-deck-{$this->name}-admin", SLIDEDECK2_PROFESSIONAL_URLPATH . '/sources/' . $this->name . '/source.css', array( 'slidedeck-admin' ), SLIDEDECK2_PROFESSIONAL_VERSION, 'screen' );
    }

    function slidedeck_form_content_source( $slidedeck, $source ) {
        // Fail silently if the SlideDeck is not this type or source
        if( !$this->is_valid( $source ) ) {
            return false;
        }

        global $wpdb;

        $ngg_active = false;
        $at_least_one_gallery = false;
        $at_least_one_album = false;

        // Check if NextGEN is activated
        if( is_plugin_active('nextgen-gallery/nggallery.php') ) {
            $ngg_active = true;
        }
        
        if ( $ngg_active ) {

            // Get list of albums
            $sql = "
                SELECT id, name FROM {$wpdb->prefix}ngg_album
            ";
            $albums = $wpdb->get_results( $sql, ARRAY_A );
            $ngg_albums = array(
                'none' => __( 'Choose an album', $this->namespace )
            );
            if( $albums ) {
                foreach( $albums as $album ) {
                    $ngg_albums[ $album['id'] ] = $album['name'];
                }
                $at_least_one_album = true;
            }

            $html_input = array(
                'type' => 'select',
                'label' => "NextGEN Album",
                'attr' => array( 'class' => 'fancy' ),
                'values' => $ngg_albums
            );
            $ngg_album_select = slidedeck2_html_input( 'options[ngg_album]', $slidedeck['options']['ngg_album'], $html_input, false);

            // Get list of galleries
            $sql = "
                SELECT gid, title FROM {$wpdb->prefix}ngg_gallery
            ";
            $galleries = $wpdb->get_results( $sql, ARRAY_A );
            $ngg_galleries = array(
                'none' => __( 'Choose a gallery', $this->namespace )
            );
            if( $galleries ) {
                foreach( $galleries as $gallery ) {
                    $ngg_galleries[ $gallery['gid'] ] = $gallery['title'];
                }
                $at_least_one_gallery = true;
            }
            $html_input = array(
                'type' => 'select',
                'label' => "NextGEN Gallery",
                'attr' => array( 'class' => 'fancy' ),
                'values' => $ngg_galleries
            );
            $ngg_gallery_select = slidedeck2_html_input( 'options[ngg_gallery]', $slidedeck['options']['ngg_gallery'], $html_input, false);

        }

        include( dirname( __FILE__ ) . '/views/show.php' );
    }
    
    /**
     * Hook into slidedeck_get_source_file_basedir filter
     * 
     * Modifies the source's basedir value for relative file referencing
     * 
     * @param string $basedir The defined base directory
     * @param string $source_slug The slug of the source being requested
     * 
     * @uses SlideDeck::is_valid()
     * 
     * @return string
     */
    function slidedeck_get_source_file_basedir( $basedir, $source_slug ) {
        if( $this->is_valid( $source_slug ) ) {
            $basedir = dirname( __FILE__ );
        }
        
        return $basedir;
    }
    
    /**
     * Hook into slidedeck_get_source_file_baseurl filter
     * 
     * Modifies the source's basedir value for relative file referencing
     * 
     * @param string $baseurl The defined base directory
     * @param string $source_slug The slug of the source being requested
     * 
     * @uses SlideDeck::is_valid()
     * 
     * @return string
     */
    function slidedeck_get_source_file_baseurl( $baseurl, $source_slug ) {
        if( $this->is_valid( $source_slug ) ) {
           $baseurl = SLIDEDECK2_PROFESSIONAL_URLPATH . '/sources/' . basename( dirname( __FILE__ ) );
        }
        
        return $baseurl;
    }
    
    /**
     * Render slides for SlideDecks of this type
     * 
     * Loads the slides associated with this SlideDeck if it matches this Deck type and returns
     * a string of HTML markup.
     * 
     * @param array $slides_arr Array of slides
     * @param object $slidedeck SlideDeck object
     * 
     * @global $SlideDeckPlugin
     * 
     * @uses SlideDeckPlugin::process_slide_content()
     * @uses Legacy::get_slides_nodes()
     * 
     * @return string
     */
    function slidedeck_get_slides( $slides, $slidedeck ) {
        global $SlideDeckPlugin;
        
        // Fail silently if not this Deck type
        if( !$this->is_valid( $slidedeck['source'] ) ) {
            return $slides;
        }
        
        // How many decks are on the page as of now.
        $deck_iteration = 0;
        if( isset( $SlideDeckPlugin->SlideDeck->rendered_slidedecks[ $slidedeck['id'] ] ) )
            $deck_iteration = $SlideDeckPlugin->SlideDeck->rendered_slidedecks[ $slidedeck['id'] ];
        
        // Slides associated with this SlideDeck
        $slides_nodes = $this->get_slides_nodes( $slidedeck );
        
        $slide_counter = 1;
        foreach( (array) $slides_nodes as $slide_nodes ) {
            $slide = array(
                'source' => $this->name,
                'title' => $slide_nodes['title'],
                'thumbnail' => (string) $slide_nodes['thumbnail'],
                'created_at' => $slide_nodes['created_at'],
                'classes' => array( 'has-image' ),
                'type' => 'image'
            );
            $slide = array_merge( $this->slide_node_model, $slide );
            
            $slide_nodes['source'] = $slide['source'];
            $slide_nodes['type'] = $slide['type'];
            
            // In-line styles to apply to the slide DD element
            $slide_styles = array();
            $slide_nodes['slide_counter'] = $slide_counter;
            $slide_nodes['deck_iteration'] = $deck_iteration;
            
            $slide['title'] = $slide_nodes['title'] = slidedeck2_stip_tags_and_truncate_text( $slide_nodes['title'], $slidedeck['options']['titleLengthWithImages'] );
            $slide_nodes['permalink'] = $slide_nodes['permalink'];
            $slide_nodes['excerpt'] = slidedeck2_stip_tags_and_truncate_text( $slide_nodes['description'], $slidedeck['options']['excerptLengthWithImages'] );
            
            // Build an in-line style tag if needed
            if( !empty( $slide_styles ) ) {
                foreach( $slide_styles as $property => $value ) {
                    $slide['styles'] .= "{$property}:{$value};";
                }
            }
            
            if( !empty( $slide['title'] ) ) {
                $slide['classes'][] = "has-title";
            } else {
                $slide['classes'][] = "no-title";
            }
            
            if( !empty( $slide_nodes['description'] ) ) {
                $slide['classes'][] = "has-excerpt";
            } else {
                $slide['classes'][] = "no-excerpt";
            }
            
            // Set link target node
            $slide_nodes['target'] = $slidedeck['options']['linkTarget'];
            
            $slide['content'] = $SlideDeckPlugin->Lens->process_template( $slide_nodes, $slidedeck );
            
            $slide_counter++;
            
            $slides[] = $slide;
        }
        
        return $slides;
    }
}