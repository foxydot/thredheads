<?php
class SlideDeckSlideType_HTML extends SlideDeckSlideModel {
	var $name = "html";
	var $label = "HTML";
	
	function __construct() {
		$this->filepath = dirname( __FILE__ );
		$this->url = SLIDEDECK2_DEVELOPER_URLPATH . '/slides/html';
		$this->thumbnail = $this->url . '/thumbnail.png';
        $this->thumbnail_small = $this->url . '/thumbnail-small.png';
        $this->slide_default_thumbnail = $this->url . '/default-thumbnail.jpg';
		
		add_action( "{$this->namespace}_update_slide", array( &$this, 'slidedeck_update_slide' ), 10, 2 );
		add_action( "{$this->namespace}_custom_slide_editor_form", array( &$this, 'slidedeck_custom_slide_editor_form' ), 10, 2 );
        
		add_filter( "{$this->namespace}_custom_slide_nodes", array( &$this, 'slidedeck_slide_nodes' ), 10, 3 );
	}
	
	/**
	 * Hook into slidedeck_custom_slide_editor_form action
	 * 
	 * Output the editing form for the slide editor modal for this slide type
	 * 
	 * @param object $slide The Slide object
	 * @param array $slidedeck The SlideDeck object
	 */
	function slidedeck_custom_slide_editor_form( $slide, $slidedeck ) {
		if( !$this->is_valid( $slide->meta['_slide_type'] ) ) {
			return false;
		}
		
		$namespace = $this->namespace;
        $url = $this->url;
        
		include( $this->filepath . '/views/show.php' );
	}
	
	/**
	 * Hook into slidedeck_slide_nodes filter
	 * 
	 * Add additional nodes to the slide when rendering SlideDecks
	 * 
	 * @param array $slide_nodes Array of slide nodes
	 * @param object $slide The slide object itself
	 * @param array $slidedeck The SlideDeck
	 * 
	 * @return array
	 */
	function slidedeck_slide_nodes( $slide_nodes, $slide, $slidedeck ) {
		global $SlideDeckPlugin;
		
		if( $this->is_valid( $slide->meta['_slide_type'] ) ) {
			$slide_nodes['content'] = $slide_nodes['excerpt'] = $slide->post_content;
		}
		
		return $slide_nodes;
	}
	
	/**
	 * Hook into slidedeck_update_slide action
	 * 
	 * Save image data when the edit form is submitted.
	 * 
	 * @param object $slide Slide object
     * @param array $data Santized $_POST data
     * 
     * @global $SlideDeckPlugin
     * 
     * @uses SlideDeck::get_video_meta_from_url()
     * @uses SlideDeckSlide::is_valid()
     * @uses update_post_meta()
     * @uses wp_update_post()
	 */
	function slidedeck_update_slide( $slide, $data ) {
	    global $SlideDeckPlugin, $wpdb;
        
		if( $this->is_valid( $slide->meta['_slide_type'] ) ) {
			$post_excerpt = $_POST['post_excerpt'];
			$post_title = strip_tags( $data['post_title'] );
			
			$args = array(
				'ID' => $slide->ID,
				'post_title' => $post_title,
				'post_content' => $post_excerpt
			);
			wp_update_post( $args );
		}
	}
}
