<?php
class pb_lb_dynamic_loop {
	//Construct
	function pb_lb_dynamic_loop( &$parent ) {
		$this->_parent = &$parent;
		if ( !function_exists( 'dynamic_loop' ) || !function_exists( 'register_dynamic_loop_handler' ) ) return false;
		register_dynamic_loop_handler( array( &$this, 'render_loop' ) );
		
	} //pb_lb_dynamic_loop
	function render_loop() {
		
		$this->_parent->load();
		$options = $this->_parent->_options;
		//Are we on a single post or page?  If so, let's check to see if we're overriding the loop
		if ( is_single() || is_page() ) {
			global $post;
			if ( is_object( $post ) ) {
				$meta = $this->_parent->get_post_meta( $post->ID );
				if ( is_array( $meta ) ) {
					if ( $meta[ 'enabled' ] ) {
						//Do custom loop
						//Check query variables to see if they are still valid
						if ( isset( $this->_parent->_options[ 'layouts' ][ $meta[ 'layout' ] ] ) ) {
							if ( !is_wp_error( $loop_result  = ($this->_parent->render_loop( isset( $this->_parent->_options[ 'queries' ][ $meta[ 'query' ] ] ) ? $meta[ 'query' ]  : false , $meta[ 'layout' ] ) ) ) ) {
								echo $loop_result;
								return true;
							} else {
								return false;
							}
						}
					} //end meta enabled
				} //end is_array( $meta )

			} //end is_object( $post );	
		} //end if single || page
		
		//Check to see if the loop object is present
		if ( !isset( $options[ 'loops' ] ) ) return false;
		//Get the current post type
		$loop_type = $this->get_loop_type();

		$loop_settings = isset( $options[ 'loops' ][ $loop_type ] ) ? $options[ 'loops' ][ $loop_type ] : false;
		if ( $loop_settings ) {
			$query = $loop_settings[ 'query' ];
			$layout = $loop_settings[ 'layout' ];
			
			if ( $query == 'default' && $layout == 'default' ) return false;
			
			if ( !is_wp_error( $loop_result  = ($this->_parent->render_loop( $query == 'default' ? -1 : $query, $layout == 'default' ? -1 : $layout ) ) ) ) {
				echo $loop_result;
				return true;
			} else {
				
				return false;
			}
		} 
		return false;
	} //end render_loop
	//Gets the loop type - false on failure
	function get_loop_type() {
		
		if ( is_single() || is_page() || is_attachment() ) {
			return get_post_type();
		} elseif ( is_category() ) {
			return 'category';
		} elseif ( is_search() ) {
			return 'search';
		} elseif ( is_tag() ) {
			return 'post_tag';
		} elseif ( is_404() ) {
			return '404';
		} elseif ( is_archive() ) {
			global $wp_query;
			$taxonomy_name = get_query_var( 'taxonomy' );
			if ( is_day() ) return 'day_archive';
			elseif ( is_month() ) return 'month_archive';
			elseif ( is_year() ) return 'year_archive';
			elseif ( $taxonomy_name ) return $taxonomy_name;
			else return false;
		} elseif ( is_home() ) {
			return 'home';
		} elseif ( is_front_page() ) {
			return 'front';
		} else { 
			
			return false;
		}
		return false;
	} //end get_loop_type
} //end class pb_lb_dynamic_loop
$pb_lb_dynamic_loop = new pb_lb_dynamic_loop( $this );
?>