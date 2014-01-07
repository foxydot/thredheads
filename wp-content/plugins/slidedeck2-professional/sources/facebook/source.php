<?php
class SlideDeckSource_Facebook extends SlideDeck {
    var $label = "Facebook Pages";
    var $name = "facebook";
    var $taxonomies = array( 'images', 'social' );
    var $default_lens = "tool-kit";
    
    var $options_model = array(
        'Setup' => array(
            'total_slides' => array(
                'value' => 5,
                'data' => 'integer'
            ),
            'facebook_page_name' => array(
                'value' => 'slidedeck'
            ),
            'facebook_page_show_other_users' => array(
                'value' => 'page'
            ),
            'facebook_access_token' => array(
                'value' => "",
                'data' => 'string'
            )
        )
    );
    
    function add_hooks() {
        global $SlideDeckPlugin;
        $slidedeck_namespace = $SlideDeckPlugin->namespace;
        
        add_action( "{$this->namespace}_form_content_source", array( &$this, "slidedeck_form_content_source" ), 10, 2 );
        add_action( "wp_ajax_{$this->namespace}_get_facebook_access_token", array( &$this, 'ajax_get_facebook_access_token' ) );
        add_action( "{$slidedeck_namespace}_before_form", array( &$this, 'before_facebook_form' ), 10, 2 );
    }
    
    /**
     * AJAX response for redirecting the user to get an Facebook access token
     * 
     * Saves the SlideDeck and dies with an URL to re-direct the user to link their Facebook account
     * and return back to the user's SlideDeck installation with the access token.
     * 
     * @uses wp_die()
     * @uses wp_verify_nonce()
     */
    function ajax_get_facebook_access_token() {
        global $SlideDeckPlugin;
        
        if( !wp_verify_nonce( $_REQUEST['_wpnonce_get_facebook_access_token'], "{$this->namespace}-get-facebook-access-token" ) ) {
            wp_die( __( "You do not have the proper authority to access this page", $this->namespace ) );
        }

        $response = array(
            'valid' => true,
            'url' => ""
        );
        
        $slidedeck_id = intval( $_POST['id'] );
        
        // SlideDeck save command called from $SlideDeckPlugin instance instead of $this to avoid default_options failures due to $this->options_model override
        $slidedeck = $SlideDeckPlugin->SlideDeck->save( $slidedeck_id, $_POST );
        
        // Sets custom post data that is checked in content retrieval to block erroneous requests
        update_post_meta( $slidedeck_id, $this->name . '_api_failed', false );
        
        if( $slidedeck ) {
            $response['url'] = 'http://www.slidedeck.com/fb-plugin-page-auth?return_to=';
            $response['url'].= base64_encode( $SlideDeckPlugin->action( '&action=edit&slidedeck=' . $slidedeck_id ) ) . '&response_type=code';
        } else {
            $response['valid'] = false;
        }
        
        die( json_encode( $response ) );
    }
    
    /**
     * Custom Output Flash Message on Edit and Create views where Facebook is the source
     * 
     * @uses $SlideDeckPlugin->namespace
     */
    function before_facebook_form( $slidedeck, $form_action ) {
        global $SlideDeckPlugin;
        
        switch( $form_action ){
            case "create":
            case "edit":
                if( in_array( $this->name, $slidedeck['source'] ) ) {
                    echo( "<div class=\"{$SlideDeckPlugin->namespace}-flash-message updated {$this->name}\"><p><strong>" . esc_html( __( "Facebook expires access tokens after 60 days.", $SlideDeckPlugin->namespace ) ) . "</strong> " . esc_html( __( "We will still display your cached deck, but to refresh the content after 60 days you will need to update your access token.", $SlideDeckPlugin->namespace ) ) . "</p></div>" );
                };
                break;
        }
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
        
        $namespace = $this->namespace;
        
        if( isset( $_GET['token'] ) && !empty( $_GET['token'] ) )
            $token = $_GET['token'];
        else
            $token = $slidedeck['options']['facebook_access_token'];
        
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
     * Get Feed
     * 
     * Fetches a feed, caches it and returns the 
     * cached result or the results after caching them.
     * 
     * @param string $feed_url The URL of the gplus feed with a JSON response
     * @param integer $slidedeck_id The ID of the deck (for caching)
     * 
     * @return array An array of arrays containing the images and various meta.
     */
    function get_slides_nodes( $slidedeck ){
        global $SlideDeckPlugin;
        
        $args = array(
            'sslverify' => false,
            'timeout' => 10
        );
		
        $facebook_page_name = $slidedeck['options']['facebook_page_name'];

        $feed_type = 'feed';
        switch( $slidedeck['options']['facebook_page_show_other_users'] ) {
            case 'page':
                $feed_type = 'posts';
            break;
            case 'everyone':
                $feed_type = 'feed';
            break;
        }
        $feed_url = 'https://graph.facebook.com/' . $facebook_page_name . '/' . $feed_type . '?access_token=' . $slidedeck['options']['facebook_access_token'] . '&limit=' . $slidedeck['options']['total_slides'];

        // $root_feed_url = 'https://graph.facebook.com/' . $facebook_page_name . '?access_token=' . $slidedeck['options']['facebook_access_token'];
        // $root_feed_response = wp_remote_get( $root_feed_url, $args );
        // $root_feed_response_json = json_decode( $root_feed_response['body'] );
        // $user_or_page_id = $root_feed_response_json->id;

        // Get the appropriate Parent ID of the post, in case this is an autosave or revision - used for proper meta storage
        $slidedeck_id = $SlideDeckPlugin->SlideDeck->get_parent_id( $slidedeck['id'] );
        
        // Create a cache key - using this to read/set Cache time validity
        $cache_key = $slidedeck_id . $feed_url . $slidedeck['options']['cache_duration'] . $this->name;
        $cache_time_valid = slidedeck2_cache_read( $cache_key );
        
        // Check to see if Access Failed Custom Meta is set for this deck
        $api_failed = (boolean) get_post_meta( $slidedeck_id, $this->name . '_api_failed', true );
        $valid_response = true;
        
        // If we have had a valid response in the past or this is new
        if( !$api_failed ) {
            // Check the age of the "ping" to Facebook, otherwise get it from the custom post meta.
            if( !$cache_time_valid ) {
                $response = wp_remote_get( $feed_url, $args );
                if( is_wp_error( $response ) ){
                    $valid_response = false;
                }
                $response_body = $response['body'];
            }
        }
        
        // Check to see if FB response is valid
        $temp_response = json_decode( $response_body );
        
        // Check if there is an error from Facebook and set $valid_response to false
        if( isset( $temp_response->error ) ) {
            $valid_response = false;
        }
        
        // Check if $api_failed or if $valid_response is false and get the Response from the Post Meta
        // This runs so we always have some content to show for the user
        if( $api_failed || !$valid_response ) {
            $response_body = base64_decode( get_post_meta( $slidedeck_id, $this->name . '_response', true ) );
        }else{
            // IF the $cache_time_valid is not set, update the meta with the response( since this is new ) and write the cache.
            if( !$cache_time_valid ) {
                update_post_meta( $slidedeck_id, $this->name . '_response', base64_encode( $response_body ) );
                slidedeck2_cache_write( $cache_key, true, $slidedeck['options']['cache_duration'] );
            }else{
                // Retrieve from Meta if cache is not expired, or not new.
                $response_body = base64_decode( get_post_meta( $slidedeck_id, $this->name . '_response', true ) );
            }
            update_post_meta( $slidedeck_id, $this->name . '_api_failed', false );
        }
        
        // Prep. response for use
        $response_json = json_decode( $response_body );
        $results = array();
        if( !empty($response_json) ) {
            foreach( (array) $response_json->data as $index => $entry ){
                $type = $entry->type;
                
                // Title Switch - get the title from the most relevant piece of content on a per entry basis
                switch( $type ) {
                    case 'status':
                    case 'question':
                        if ( isset( $entry->message ) ) {
                            $results[ $index ]['title'] = $entry->message;
                            break;
                        }
                        if ( isset( $entry->story ) ) {
                            $results[ $index ]['title'] = $entry->story;
                            break;
                        }
                    break;
                    case 'photo':
                        if ( isset( $entry->caption ) ) {
                            $results[ $index ]['title'] = $entry->caption;
                            break;
                        }
                        if ( isset( $entry->message ) ) {
                            $results[ $index ]['title'] = $entry->message;
                            break;
                        }
                        if ( isset( $entry->story ) ) {
                            $results[ $index ]['title'] = $entry->story;
                            break;
                        }
                    break;
                    case 'link':
                    case 'video':
                        if( isset( $entry->name ) ){
                            $results[ $index ]['title'] = $entry->name;
                            break;
                        }
                        if ( isset( $entry->message ) ) {
                            $results[ $index ]['title'] = $entry->message;
                            break;
                        }
                        if ( isset( $entry->story ) ) {
                            $results[ $index ]['title'] = $entry->story;
                            break;
                        }
                    break;
                }
                
                // Description switch - get the description based off the most relevant piece of content on a per entry basis
                switch( $type ) {
                    case 'status':
                    case 'question':
                        if ( isset( $entry->message ) ) {
                            $results[ $index ]['description'] = $entry->message;
                            break;
                        }
                        if ( isset( $entry->story ) ) {
                            $results[ $index ]['description'] = $entry->story;
                            break;
                        }
                    break;
                    case 'photo':
                        if ( isset( $entry->message ) ) {
                            $results[ $index ]['description'] = $entry->message;
                            break;
                        }
                        if ( isset( $entry->caption ) ) {
                            $results[ $index ]['description'] = $entry->caption;
                            break;
                        }
                        if ( isset( $entry->story ) ) {
                            $results[ $index ]['description'] = $entry->story;
                            break;
                        }
                    break;
                    case 'link':
                    case 'video':
                        if ( isset( $entry->description ) ) {
                            $results[ $index ]['description'] = $entry->description;
                            break;
                        }
                        if ( isset( $entry->message ) ) {
                            $results[ $index ]['description'] = $entry->message;
                            break;
                        }
                        if ( isset( $entry->story ) ) {
                            $results[ $index ]['description'] = $entry->story;
                            break;
                        }
                    break;
                }

                // Permalink switch - set the permalink based off type
                switch( $type ) {
                    case 'status':
                    case 'question':
                    case 'link':
                    case 'video':
                        $content_id = preg_match( '/([0-9]+)_([0-9]+)/', $entry->id, $matches );
                        $content_id = $matches[2];
                        $results[ $index ]['permalink'] = 'http://www.facebook.com/' . $facebook_page_name . '/posts/' . $content_id ;
                    break;
                    case 'photo':
                        $results[ $index ]['permalink'] = isset( $entry->link ) ? $entry->link : 'http://www.facebook.com/' . $facebook_page_name ;
                    break;
                }
                
                // If the description is the same as the title, set the description to false
                if( $results[ $index ]['description'] == $results[ $index ]['title'] ) {
                    $results[ $index ]['description'] = false;
                }
                
                // If there is no title, set the title to false
                if( !isset( $results[ $index ]['title'] ) ) {
                    $results[ $index ]['title'] = false;
                }
                
                // Handle the images as best we can.
                $results[ $index ]['image'] = isset( $entry->picture ) ? $entry->picture : false ;
                $results[ $index ]['thumbnail'] = $results[ $index ]['image'] ;
                
                if( preg_match('/\/safe_image\.php.*&url=([^&]+)/', $results[ $index ]['image'], $matches ) ){
                    $results[ $index ]['image'] = urldecode( $matches[1] );
                }else{
                    $results[ $index ]['image'] = preg_replace('/([0-9]+)_([0-9]+)_([0-9]+)_([a-zA-Z])\./', '$1_$2_$3_o.', $results[ $index ]['image']);
                }
                
                $results[ $index ]['created_at'] = strtotime( $entry->created_time );
                
                $results[ $index ]['comments_count'] = isset( $entry->comments ) ? count( $entry->comments->data ) : false ;
                $results[ $index ]['likes_count'] = isset( $entry->likes ) ? $entry->likes->count : false ;
                $results[ $index ]['author_name'] = $entry->from->name;
                $results[ $index ]['author_url'] = 'http://facebook.com/' . $entry->from->id;
                $results[ $index ]['author_avatar'] = 'http://graph.facebook.com/' . $entry->from->id . '/picture?type=large';

                if( count( $results ) == $slidedeck['options']['total_slides'] ) {
                    break;
                }
                
            }

        }

        return $results;
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
     * @uses Legacy::get_slides()
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
        if( is_array( $slides_nodes ) ){
            foreach( $slides_nodes as &$slide_nodes ) {
                $slide = array(
                	'source' => $this->name,
                    'title' => $slide_nodes['title'],
                    'created_at' => $slide_nodes['created_at']
                );
                $slide = array_merge( $this->slide_node_model, $slide );
                
				
                // Build an in-line style tag if needed
                if( !empty( $slide_styles ) ) {
                    foreach( $slide_styles as $property => $value ) {
                        $slide['styles'] .= "{$property}:{$value};";
                    }
                }
                
                $slide['title'] = $slide_nodes['title'] = slidedeck2_stip_tags_and_truncate_text( $slide_nodes['title'], $slidedeck['options']['titleLengthWithImages'], "&hellip;" );
                $slide_nodes['content'] = isset( $slide_nodes['description'] ) ? $slide_nodes['description'] : "";
                $slide_nodes['excerpt'] = slidedeck2_stip_tags_and_truncate_text( $slide_nodes['content'], $slidedeck['options']['excerptLengthWithImages'], "&hellip;" );
                
                if( !empty( $slide_nodes['image'] ) ) {
                    $slide['classes'][] = "has-image";
                    $slide['type'] = "image";
                    $slide['thumbnail'] = $slide_nodes['thumbnail'];
                } else {
                    $slide['classes'][] = "no-image";
                }
                
				if( !empty( $slide_nodes['title'] ) ) {
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
                
                $slide_nodes['source'] = $slide['source'];
                $slide_nodes['type'] = $slide['type'];
				
                $slide['content'] = $SlideDeckPlugin->Lens->process_template( $slide_nodes, $slidedeck );
                
                $slide_counter++;
                
                $slides[] = $slide;
            }
        }
        return $slides;
    }
}