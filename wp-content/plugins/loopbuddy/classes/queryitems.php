<?php
class loopbuddy_queryitems {
	private $keys = array();
	private $parent = '';
	private $wp31 = false;
	function loopbuddy_queryitems( &$parent ) {
		$this->keys = array();
		$this->parent = $parent;
		if ( version_compare( get_bloginfo( 'version' ), '3.1', '>=' ) ) {
			$this->wp31 = true;
		}

		//General Parameters
		$this->keys[ 'General Parameters' ] = array(
			'author' => array(
				'label' => __( 'Post Author', 'it-l10n-loopbuddy' ),
				'tip' => __( "Choose which items to display based on the Post Author's ID", 'it-l10n-loopbuddy' ),
				'type' => 'input',
				'callback' => 'text_input',
				'args' => array( 
					'ajax_assist' => 
						array( 
							'label' => __( 'Look up Author', 'it-l10n-loopbuddy' ),
							'type' => 'author'
					, 'size' => 5 )
				)
			)
			,'orderby' => array(
				'label' => __( 'Order By', 'it-l10n-loopbuddy' ),
				'tip' => __( 'Choose which Post Type parameter to sort by', 'it-l10n-loopbuddy' ),
				'type' => 'dropdown',
				'callback' => 'orderby',
				'args' => array( 'value' => 'date' )
			)
			,'order' => array(
				'label' => __( 'Order', 'it-l10n-loopbuddy' ),
				'tip' => __( 'Order by high to low (DESC) or low to high (ASC)', 'it-l10n-loopbuddy' ),
				'type' => 'dropdown',
				'callback' => 'order',
				'args' => array( 'value' => 'DESC' )
			)
			,'post_type' => array(
				'label' => __( 'Post Type', 'it-l10n-loopbuddy' ),
				'tip' => __( 'Select which Post Types to use', 'it-l10n-loopbuddy' ),
				'type' => 'checkbox',
				'callback' => 'post_types',
				'args' => array( 'value' => array( 'post' ) )
			)
			,'post_status' => array(
				'label' => __( 'Post Status', 'it-l10n-loopbuddy' ),
				'tip' => __( 'Select the Post Status (e.g., Published Posts only)', 'it-l10n-loopbuddy' ),
				'type' => 'checkbox',
				'callback' => 'post_status',
				'args' => array( 'value' => 'publish' )
			)
			,'posts_per_page' => array(
				'label' => __( 'Number of Posts to Display', 'it-l10n-loopbuddy' ),
				'tip' => __( 'Type in how many posts to display.  Use -1 for unlimited', 'it-l10n-loopbuddy' ),
				'type' => 'input',
				'disabled' => false,
				'callback' => 'text_input',
				'args' => array( 'size' => 5, 'value' => get_option( 'posts_per_page' ) )
			)
			,'offset' => array(
				'label' => __( 'Offset - The number of posts to pass over', 'it-l10n-loopbuddy' ),
				'tip' => __( 'The number of posts to offset, or pass over', 'it-l10n-loopbuddy' ),
				'type' => 'input',
				'disabled' => false,
				'callback' => 'text_input',
				'args' => array( 'value' => 0 )
			)
			,'nopaging' => array(
				'label' => __( 'Enable Paging?', 'it-l10n-loopbuddy' ),
				'tip' => __( 'Do you want to allow pagination (e.g., next/prev buttons)?', 'it-l10n-loopbuddy' ),
				'type' => 'radio',
				'args' => array( 'size' => 5, 'value' => 'on' )
			)
			,'enable_comments' => array(
				'label' => __( 'Enable Comments?', 'it-l10n-loopbuddy' ),
				'tip' => __( 'Enable comments?  Posts must have comments enabled as well.', 'it-l10n-loopbuddy' ),
				'type' => 'radio',
				'args' => array( 'size' => 5, 'value' => 'on' )
			)
			,'merge_queries' => array(
				'label' => __( 'Merge the default query with this one (ideal for archive pages)', 'it-l10n-loopbuddy' ),
				'tip' => __( 'Merging queries allows you to keep the default query values and selectively overwrite other values.  This is ideal if you are going to use this query on an archive page.', 'it-l10n-loopbuddy' ),
				'type' => 'radio',
				'args' => array( 'size' => 5, 'value' => 'off' )
			)

		); 
		//end General Parameters
		//Post Parameters
		$this->keys[ 'Post/Page Parameters' ] = array(
			'use_current' => array(
				'label' => __( 'Use Current Post or Page ID', 'it-l10n-loopbuddy' ),
				'tip' => __( "For single posts or pages, you may want to use the current page's ID so you can overwrite the loop for the actual post", 'it-l10n-loopbuddy' ),
				'type' => 'radio',
				'callback' => 'text_input',
				'args' => array( 'size' => 5, 'value' => 'off' )
			)
			,'p' => array(
				'label' => __( 'Post IDs', 'it-l10n-loopbuddy' ),
				'tip' => __( 'Select which posts to include or exclude', 'it-l10n-loopbuddy' ),
				'type' => 'input',
				'callback' => 'text_input',
				'args' => array( 
					'ajax_assist' => 
						array( 
							'label' => __( 'Look up Post', 'it-l10n-loopbuddy' ),
							'type' => 'post_id'
					, 'size' => 5 )
				)
			)
			,'include_exclude' => array(
				'label' => __( 'Include or Exclude the Post IDs', 'it-l10n-loopbuddy' ),
				'tip' => __( 'Choose whether to include or exclude the Post IDs you have selected', 'it-l10n-loopbuddy' ),
				'type' => 'radio',
				'callback' => 'text_input',
				'args' => array( 'size' => 5, 'value' => 'on' )
			)
			
		);
		//Sticky Post Parameters
		$this->keys[ 'Sticky Post Parameters' ] = array(
			'ignore_sticky_posts' => array(
				'label' => __( 'Ignore Sticky Post Priority?', 'it-l10n-loopbuddy' ),
				'tip' => __( 'Choose whether to ignore the sticky status of posts in the query (posts are sorted by regular order)', 'it-l10n-loopbuddy' ),
				'type' => 'radio',
				'callback' => 'text_input',
				'args' => array( 'size' => 5, 'value' => 'off' )
			),
			'exclude_sticky_posts' => array(
				'label' => __( 'Exclude all sticky posts?', 'it-l10n-loopbuddy' ),
				'tip' => __( 'If a post is sticky, do not show it.', 'it-l10n-loopbuddy' ),
				'type' => 'radio',
				'callback' => 'text_input',
				'args' => array( 'size' => 5, 'value' => 'off' )
			),
			'sticky_posts_note' => array(
				'type' => 'note',
				'tip' => __( 'WordPress, by default, will not permit a custom number of Sticky Posts AND regular posts at the same time. If you place a number greater than zero in this field, only sticky posts will display.', 'it-l10n-loopbuddy' ),
			),
			'sticky_posts_display' => array(
				'label' => __( 'Number of Sticky Posts to Display', 'it-l10n-loopbuddy' ),
				'tip' => __( 'By default, WordPress will show all available sticky posts.  You can choose to limit this amount.  Leave at zero for no limit.', 'it-l10n-loopbuddy' ),
				'type' => 'input',
				'callback' => 'text_input',
				'args' => array( 'value' => 0 )
			)
		);
		//Category Parameters
		$this->keys[ 'Category Parameters' ] = array(
			'cat' => array(
				'label' => __( 'Posts that have these categories', 'it-l10n-loopbuddy' ),
				'tip' => __( 'Choose which posts to display based on these categories', 'it-l10n-loopbuddy' ),
				'type' => 'input',
				'callback' => 'text_input',
				'args' => array( 
					'ajax_assist' => 
						array( 
							'label' => __( 'Posts that have these categories', 'it-l10n-loopbuddy' ),
							'type' => 'cat_ids'
					, 'size' => 5 )
				)
			)
			,'category_name' => array(
				'label' => __( 'Category Slug', 'it-l10n-loopbuddy' ),
				'tip' => __( 'Choose which posts to display based on these categories', 'it-l10n-loopbuddy' ),
				'type' => 'input',
				'callback' => 'text_input',
				'args' => array( 
					'ajax_assist' => 
						array( 
							'label' => __( 'Look up Category Slug', 'it-l10n-loopbuddy' ),
							'type' => 'cat_slug'
					, 'size' => 5 )
				)
			)
			,'category__and' => array(
				'label' => __( 'All posts that match these categories', 'it-l10n-loopbuddy' ),
				'tip' => __( 'Show only posts that match all the categories selected', 'it-l10n-loopbuddy' ),
				'type' => 'input',
				'callback' => 'text_input',
				'args' => array( 
					'ajax_assist' => 
						array( 
							'label' => __( 'All posts that match these categories', 'it-l10n-loopbuddy' ),
							'type' => 'cat_ids'
					, 'size' => 5 )
				)
			)
			,'category__in' => array(
				'label' => __( 'Include Posts with these categories', 'it-l10n-loopbuddy' ),
				'tip' => __( 'Show posts that only have these categories', 'it-l10n-loopbuddy' ),
				'type' => 'input',
				'callback' => 'text_input',
				'args' => array( 
					'ajax_assist' => 
						array( 
							'label' => __( 'Look up Page Slug', 'it-l10n-loopbuddy' ),
							'type' => 'cat_ids'
					, 'size' => 5 )
				)
			)
			,'category__not_in' => array(
				'label' => __( 'Exclude posts with these categories', 'it-l10n-loopbuddy' ),
				'tip' => __( 'Exclude posts with these categories', 'it-l10n-loopbuddy' ),
				'type' => 'input',
				'callback' => 'text_input',
				'args' => array( 
					'ajax_assist' => 
						array( 
							'label' => __( 'Look up Page', 'it-l10n-loopbuddy' ),
							'type' => 'cat_ids'
					, 'size' => 5 )
				)
			)
		);
		//Tag Parameters
		$this->keys[ 'Tag Parameters' ] = array(
			'tag_id' => array(
				'label' => __( 'Tag ID', 'it-l10n-loopbuddy' ),
				'tip' => __( 'Only display posts that have this tag', 'it-l10n-loopbuddy' ),
				'type' => 'input',
				'callback' => 'text_input',
				'args' => array( 
					'ajax_assist' => 
						array( 
							'label' => __( 'Look up Tag ID', 'it-l10n-loopbuddy' ),
							'type' => 'tag_id'
					, 'size' => 5 )
				)
			)
			,'tag__and' => array(
				'label' => __( 'All posts that match these tags', 'it-l10n-loopbuddy' ),
				'tip' => __( 'Show all the posts that match these tags', 'it-l10n-loopbuddy' ),
				'type' => 'input',
				'callback' => 'text_input',
				'args' => array( 
					'ajax_assist' => 
						array( 
							'label' => __( 'All posts that match these tags', 'it-l10n-loopbuddy' ),
							'type' => 'tag_ids'
					, 'size' => 5 )
				)
			)
			,'tag__in' => array(
				'label' => __( 'Include Posts with these tags', 'it-l10n-loopbuddy' ),
				'tip' => __( 'Only include posts that have these tags', 'it-l10n-loopbuddy' ),
				'type' => 'input',
				'callback' => 'text_input',
				'args' => array( 
					'ajax_assist' => 
						array( 
							'label' => __( 'Include Posts with these tags', 'it-l10n-loopbuddy' ),
							'type' => 'tag_ids'
					, 'size' => 5 )
				)
			)
			,'tag__not_in' => array(
				'label' => __( 'Exclude posts with these tags', 'it-l10n-loopbuddy' ),
				'tip' => __( 'Exclude posts that have these tags', 'it-l10n-loopbuddy' ),
				'type' => 'input',
				'callback' => 'text_input',
				'args' => array( 
					'ajax_assist' => 
						array( 
							'label' => __( 'Exclude posts with these tags', 'it-l10n-loopbuddy' ),
							'type' => 'tag_ids'
					, 'size' => 5 )
				)
			)
		);
		//Taxonomy Params
		//todo - Add more items for 3.1 compatibility
		$this->keys[ 'Taxonomy Parameters' ] = array(
			'taxonomy' => array(
			)
		);
		//Meta, aka, Custom Field Params
		//todo - Add more items for 3.1 compatibility
		$this->keys[ 'Post Meta Parameters' ] = array(
			'meta' => array(
			)
		);
		//Time Parameters
		$this->keys[ 'Time Parameters' ] = array(
				 'second' => array(
				 	'label' => __( 'Second (0 - 60)', 'it-l10n-loopbuddy' ),
				 	'tip' => __( 'Posts that were published on this second value (leave as default if there is no preference)', 'it-l10n-loopbuddy' ),
				 	'type' => 'dropdown',
				 	'callback' => 'integer_range',
				 	'args' => array( 'min' => 0, 'max' => 60 )
				 )
				, 'minute' => array(
				 	'label' => __( 'Minute (0 - 60)', 'it-l10n-loopbuddy' ),
				 	'tip' => __( 'Posts that were published on this minute value (leave as default if there is no preference)', 'it-l10n-loopbuddy' ),
				 	'type' => 'dropdown',
				 	'callback' => 'integer_range',
				 	'args' => array( 'min' => 0, 'max' => 60 )
				 )
				, 'hour' => array(
				 	'label' => __( 'Minute (0 - 23)', 'it-l10n-loopbuddy' ),
				 	'tip' => __( 'Posts that were published on this hour value (leave as default if there is no preference)', 'it-l10n-loopbuddy' ),
				 	'type' => 'dropdown',
				 	'callback' => 'integer_range',
				 	'args' => array( 'min' => 0, 'max' => 23 )
				 )
				, 'day' => array(
				 	'label' => __( 'Day (1 - 31)', 'it-l10n-loopbuddy' ),
				 	'tip' => __( 'Posts that were published on this day (leave as default if there is no preference)', 'it-l10n-loopbuddy' ),
				 	'type' => 'dropdown',
				 	'callback' => 'integer_range',
				 	'args' => array( 'min' => 1, 'max' => 31 )
				 )
				, 'monthnum' => array(
				 	'label' => __( 'Month Number (1 - 12)', 'it-l10n-loopbuddy' ),
				 	'tip' => __( 'Posts that were published on this month (leave as default if there is no preference)', 'it-l10n-loopbuddy' ),
				 	'type' => 'dropdown',
				 	'callback' => 'integer_range',
				 	'args' => array( 'min' => 1, 'max' => 12 )
				 )
				, 'year' => array(
				 	'label' => __( 'Year', 'it-l10n-loopbuddy' ),
				 	'tip' => __( 'Posts that were published on this year (leave as default if there is no preference)', 'it-l10n-loopbuddy' ),
				 	'type' => 'dropdown',
				 	'callback' => 'integer_range',
				 	'args' => array( 'min' => 1990, 'max' => intval( date( 'Y', time() ) ) + 20 )
				 )
				, 'w' => array(
				 	'label' => __( 'Week (0 - 53)', 'it-l10n-loopbuddy' ),
				 	'tip' => __( 'Posts that were published on this week (leave as default if there is no preference)', 'it-l10n-loopbuddy' ),
				 	'type' => 'dropdown',
				 	'callback' => 'integer_range',
				 	'args' => array( 'min' => 0, 'max' => 53 )
				 )
		); //end time parameters

		//Just when you think we're done, we keep going!  Let's test the $_POST variable, check a nonce, map the keys, and save the data
		//wp_print_r( $_POST );
		if ( isset( $_POST[ 'save' ] ) && isset( $_POST[ 'loopbuddy' ] ) ) {
			$query_args = $_POST[ 'loopbuddy' ];
			if ( !wp_verify_nonce( $_REQUEST[ 'lb_query' ], 'lb_save_query' ) ) die( '' ); //Nonce not verified
			$query_args = $this->map_query_args( $query_args );
			
			$options = &$this->parent->_options[ 'queries' ];
			$query_id = intval( $_GET[ 'edit' ] );
			$options[ $query_id ] = array(
				'title' => isset( $_POST[ 'title' ] ) ? $_POST[ 'title' ] : 'default',
				'query' => $query_args
			);
			$this->parent->save();
		} //end save
	} //end constructor
	//Map saved values to the default keys - Returned in a format for editing
	private function get_keys_to_edit( $query_id = -1 ) {
		//Get Saved Keys
		$saved_query = false;
		if ( $query_id != -1 ) { 
			if ( isset( $this->parent->_options[ 'queries' ][ $query_id ][ 'query' ] ) ) {
				$saved_query = $this->parent->_options[ 'queries' ][ $query_id ][ 'query' ];
				
			}
		} elseif ( isset( $_GET[ 'edit' ] ) ) {
			if ( isset( $this->parent->_options[ 'queries' ][ $_GET[ 'edit' ] ][ 'query' ] ) ) {
				$saved_query = $this->parent->_options[ 'queries' ][ $_GET[ 'edit' ] ][ 'query' ];
			}
		}
		if ( !$saved_query ) return new WP_Error( 'no_keys', __( 'This is not a valid query', 'it-l10n-loopbuddy' ) );
		
		//Overwrite the defaults keys with the saved ones
		$defaults = $this->keys;
		foreach ( $defaults as $label => &$data ) {
			foreach ( $data as $data_key => $values ) {
				if ( $data_key == 'taxonomy' ) {
					$data[ 'taxonomy' ] = $saved_query[ 'taxonomy' ];
					continue;
				}
				if ( $data_key == 'meta' ) {
					$data[ 'meta' ] = $saved_query[ 'meta' ];
					continue;
				}
				if ( array_key_exists( $data_key, $saved_query ) ) {
					foreach ( $values as $key => $value ) {
						if ( array_key_exists( $key, $values ) && $key != 'tip' && $key != 'type' ) {
							$data[ $data_key ][ $key ] = $saved_query[ $data_key ][ $key ];
						}
					}
				}
			}
		}
		return $defaults;
	} //end get_keys_to_edit
	
	//Gets keys in a parsable format (e.g., 'key' => 'value' )
	private function get_keys_to_parse( $query_id = -1 ) {
		$keys = $this->get_keys_to_edit( $query_id );
		
		$default_keys = array();
		foreach ( $keys as $label => $data ) {
			foreach ( $data as $data_key => $values ) {
				if ( $data_key == 'taxonomy' || $data_key == 'meta' ) {
					$default_keys[ $data_key ] = $values;
					continue;
				}
				$data_value = isset( $values[ 'args' ][ 'value' ] ) ? $values[ 'args' ][ 'value' ] : false; //todo - need to set default values on items
				$default_keys[ $data_key ] = $data_value;
			}
		}
		return $default_keys;
	} //end get_keys_to_parse
	//Needed to check to see if the WordPress version is 3.1 or higher.  3.1 introduces some advanced meta/taxonomy queries
	private function is_wp31() {
		if ( $this->wp31 ) return true;
		return false;
	} //end is_wp31
	
	//Get into WP_Query Format
	public function get_wp_query( $query_id = -1 ) {
		$keys = $this->get_keys_to_parse( $query_id );
		
		$array_keys = array(
			'taxonomy_terms',
			'tag__not_in',
			'tag__in',
			'tag__and',
			'category__not_in',
			'category__in',
			'category__and',
			'post__not_in',
			'post__in',
			'post_type',
			'post_status'
		);
		foreach ( $keys as $key => &$value ) {
			//Unset empty values
			if ( !is_array( $value ) ) {
				$value = trim( $value );
				if ( empty( $value ) ) {
					unset( $keys[ $key ] );
					continue;
				}
				if ( in_array( $key, $array_keys ) ) {
					$value = explode( ',', $value );
					foreach ( $value as &$v ) {
						$v = trim( $v );
					}
				}
			}
		}//end foreach
		
		//Get into post format
		if ( isset( $keys[ 'p' ] ) ) {
			$post_ids = explode( ',', $keys[ 'p' ] );
			if ( !empty( $post_ids ) ) {
				unset( $keys[ 'p' ] );
				if ( $keys[ 'include_exclude' ] == 'on' ) {
					$keys[ 'post__in' ] = $post_ids;
				} else {
					$keys[ 'post__not_in' ] = $post_ids;
				}
			}
		}
		
		//Post type and post status both can take array values, however, when any is selected, just convert to string
		if ( isset( $keys[ 'post_type' ] ) && in_array( 'any', $keys[ 'post_type' ] ) ) {
			$keys[ 'post_type' ] = 'any';
		}
		if ( isset( $keys[ 'post_status' ] ) && in_array( 'any', $keys[ 'post_status' ] ) ) {
			$keys[ 'post_status' ] = 'any';
		}
		
		/* META - Get in the right format */
		/* Basically, meta_key and meta_value can still be used - So if there is only one item and meta_key or meta_value is set, use that.
		If both are set, and it's WP 3.1 or above, use a meta query - Otherwise ignore the type */
		$meta_params = isset( $keys[ 'meta' ] ) ? $keys[ 'meta' ] : false;
		$meta_items = isset( $meta_params[ 'meta_items' ] ) ? $meta_params[ 'meta_items' ] : false;
		$meta_relation = isset( $meta_params[ 'relation' ] ) ? $meta_params[ 'relation' ] : 'AND';
		$meta_query = array(); //temporary array until assigned to keys
		if ( $meta_items ) {
			$meta_count = count( $meta_items );
			if ( $this->is_wp31() && $meta_count > 1 ) {
				$meta_query[ 'relation' ] = $meta_relation;
			}
			foreach ( $meta_items as $key => $meta ) {
				extract( $meta ); //meta_key, meta_value, meta_compare, meta_type
				$meta_compare = str_replace( "&lt;", "<", $meta_compare );
				$meta_compare = str_replace( "&gt;", ">", $meta_compare );
				//If meta count is only 1, see if we need to do a meta_query
				if ( empty( $meta_value ) && empty( $meta_key ) ) continue; //skip empty meta_items
				if ( $meta_count == 1 ) {
					if ( empty( $meta_value ) && !empty( $meta_key ) ) {
						$keys[ 'meta_key' ] = $meta_key;
					} elseif ( empty( $meta_key ) && !empty( $meta_value ) ) {
						$keys[ 'meta_value' ] = $meta_value;
					} elseif ( $this->is_wp31() ) {
						if ( $keys[ 'orderby' ] = 'meta_value_num' ) {
							$keys[ 'meta_key' ] = $meta_key;
						}
						//If WP 31, get meta value in appropriate format - Check for comma separated and convert to array, etc
						$meta_value = $this->get_meta_value( $meta_value, $meta_compare );
						
						//Meta query format
						$meta_query[] = array(
								'key' => $meta_key,
								'value' => $meta_value,
								'compare' => $meta_compare,
								'type' => $meta_type
						);
					} else {
						//Regular format
						$keys[ 'meta_key' ] = $meta_key;
						$keys[ 'meta_value' ] = $meta_value;
						$keys[ 'meta_compare' ] = $meta_compare;
					}
				} elseif ( $meta_count > 1 && $this->is_wp31() ) {
					$meta_value = $this->get_meta_value( $meta_value, $meta_compare );
					
					//Meta query format
					$meta_query[] = array(
						'key' => $meta_key,
						'value' => $meta_value,
						'compare' => $meta_compare,
						'type' => $meta_type
					);
				}
			} //end foreach $meta_items
			if ( !empty( $meta_query ) ) {
				$keys[ 'meta_query' ] = $meta_query;
			}
			//So someone wants to have multiple meta values on < WP 3.1?  :(
		}		
		unset( $keys[ 'meta' ] );
		
		//Strip out taxonomy keys (if applicable) and/or get into 3.1 taxonomy format
		//This really only works well in 3.1, so if the person isn't running 3.1, let's strip these out
		$taxonomy_params = isset( $keys[ 'taxonomy' ] ) ? $keys[ 'taxonomy' ] : false;
		$taxonomies = isset( $taxonomy_params[ 'taxonomies' ] ) ? $taxonomy_params[ 'taxonomies' ] : array();
		$taxonomy_relation = isset( $taxonomy_params[ 'relation' ] ) ? $taxonomy_params[ 'relation' ] : 'AND';
		if ( $taxonomy_params && $this->is_wp31() ) {
			$keys[ 'tax_query' ] = array();
			if ( count( $taxonomies ) > 1 ) {
				//Skip a relation if there is only one taxonomy query
				$keys[ 'tax_query' ][ 'relation' ] = $taxonomy_relation;
			}
			foreach ( $taxonomies as $index => $values ) {
				//do a quick check (if there is only one term, we want to change the operator to IN)
				$tax_query = array(
					'taxonomy' => $values[ 'taxonomy_name' ],
					'terms' => explode( ',', $values[ 'taxonomy_terms' ] ),
					'operator' => $values[ 'taxonomy_operator' ],
					'field' => 'slug'
				);
				if ( $values[ 'taxonomy_operator' ] == 'OR' ) {
					unset( $tax_query[ 'operator' ] ); //There's no such thing as OR, but we'll pretend there is
				}
				$keys[ 'tax_query' ][] = $tax_query;
			}
		}
		//Clear the entire taxonomy key if there aren't any
		if ( count( $taxonomies ) == 0 ) unset( $keys[ 'tax_query' ] );
		
		unset( $keys[ 'taxonomy' ] );
		unset( $keys[ 'taxonomy_terms' ] );
		unset( $keys[ 'taxonomy_operator' ] );
		
		//Set Sticky Posts
		if ( isset( $keys[ 'ignore_sticky_posts' ] ) ) {
			$keys[ 'ignore_sticky_posts' ] = $keys[ 'ignore_sticky_posts' ] == 'on' ? true : false;
		}
		if ( isset( $keys[ 'exclude_sticky_posts' ] ) ) {
			if ( $keys[ 'exclude_sticky_posts' ] == 'on' ) {
				//Exclude all sticky posts if there are any - otherwise business as usual
				$sticky_posts_exclude = get_option( 'sticky_posts', array() );
				if ( count( $sticky_posts_exclude ) > 0 ) {
					$keys[ 'post__not_in' ] = $sticky_posts_exclude;
					unset( $keys[ 'post__in' ] ); //post__not_in doesn't work with post__in
				}
			} elseif ( isset( $keys[ 'sticky_posts_display' ] ) && absint( $keys[ 'sticky_posts_display' ] ) > 0 ) {
				//The user hasn't excluded sticky posts, but wants to only show a certain number
				$sticky_posts = get_option( 'sticky_posts', array() );
				if ( count( $sticky_posts ) > 0 ) {
					$sticky_posts = array_slice( $sticky_posts, 0, absint( $keys[ 'sticky_posts_display' ] ) );
					$keys[ 'post__in' ] = $sticky_posts;
					$keys[ 'ignore_sticky_posts' ] = true;
					unset( $keys[ 'post__not_in' ] ); //post__in doesn't work with post__not_in
				}
				
			}
		}
		unset( $keys[ 'sticky_posts_display' ] );
		unset( $keys[ 'exclude_sticky_posts' ] );
		
		//Strip out offset
		if ( isset( $keys[ 'offset' ] ) ) {
			$keys[ 'offset' ] = absint( $keys[ 'offset' ] );
			if ( $keys[ 'offset' ] == 0 ) {
				unset( $keys[ 'offset' ] );
			}
		}
		
		//Strip out date/time items
		$date_time_keys = array(
			'second',
			'minute',
			'hour',
			'day',
			'monthnum',
			'year',
			'w'
		);
		foreach ( $date_time_keys as $item_key => $item ) {
			if ( !is_int( $key[ $item ] ) ) unset( $keys[ $item ] );
		}
		//Return 
		return $keys;
	} //end get_wp_query
	//Get default keys for the initial group creation
	public function get_defaults( $args ) {
		$options = array();
		$options[ 'title' ] = isset( $args[ 'title'] ) ? $args[ 'title' ] : 'default';
		
		$defaults = $this->keys;
		$default_keys = array();
		foreach ( $defaults as $label => $data ) {
			foreach ( $data as $data_key => $values ) {
				$default_keys[ $data_key ] = $values;
			}
		}
		$options[ 'query' ] = $default_keys;
		return $options;
	} //end get_defaults
	//Determines if meta_value should be a string or array
	//Basically the meta_compare param determines if the meta_value should be a string or array
	private function get_meta_value( $meta_value, $meta_compare ) {
		if ( in_array( $meta_compare, array( 'IN', 'NOT IN', 'BETWEEN', 'NOT BETWEEN' ) ) ) {
			$meta_value_temp = explode( ',', $meta_value );
			if ( count( $meta_value_temp ) > 1 ) $meta_value = $meta_value_temp;
		}
		return $meta_value;
	} //end get_meta_value
	//Maps default keys to post arguments and assigns/overwrites values
	private function map_query_args( $args ) {
		
		//Map default keys
		$defaults = $this->keys;
		$default_keys = array();
		foreach ( $defaults as $label => $data ) {
			foreach ( $data as $data_key => $values ) {
				$default_keys[ $data_key ] = $values;
			}
		}
		//Assign values to default keys
		foreach ( $args as $key => $value ) {
			if ( is_array( $value ) ) {
				foreach ( $value as $value_key => &$value_data ) {
					$value_data = trim( sanitize_text_field( $value_data ) );
				}
			} else {
				$value = trim( sanitize_text_field( $value ) );
			}
			if ( array_key_exists( $key, $default_keys ) ) {
				
				$item_args = isset( $default_keys[ $key ][ 'args' ] ) ? $default_keys[ $key ][ 'args' ] : false;
				if ( !$item_args ) {
					$default_keys[ $key ][ 'args' ] = array( 'value' => $value );
				} else {
					$default_keys[ $key ][ 'args' ][ 'value' ] = $value;
				}
			}
		}
		
		//Build Taxonomy Array
		if ( isset( $args[ 'taxonomies' ] ) && is_array( $args[ 'taxonomies' ] ) ) {
			$tax_array = array(
				'taxonomies' => array(),
				'relation' => isset( $args[ 'taxonomy_relation' ] ) ? sanitize_text_field( $args[ 'taxonomy_relation' ] ) : 'AND'
			);
			foreach ( $args[ 'taxonomies' ] as $index => $values ) {
				foreach ( $values as $key => &$value ) {
					$value = sanitize_text_field( $value );
				}
				$tax_array[ 'taxonomies' ][] = $values;
			}
			$default_keys[ 'taxonomy' ] = $tax_array;
		}
		//Build Meta Array
		if ( isset( $args[ 'meta' ] ) && is_array( $args[ 'meta' ] ) ) {
			$meta_array = array(
				'meta_items' => array(),
				'relation' => isset( $args[ 'meta_relation' ] ) ? sanitize_text_field( $args[ 'meta_relation' ] ) : 'AND',
			);
			foreach ( $args[ 'meta' ] as $index => $values ) {
				foreach ( $values as $key => &$value ) {
					$value = sanitize_text_field( $value );
				}
				$meta_array[ 'meta_items' ][] = $values;
			}
			$default_keys[ 'meta' ] = $meta_array;
		}
		return $default_keys;
	} //end map_query_args
	
	public function display_taxonomy( $args ) {
		$defaults = array(
			'taxonomy_name' => '',
			'taxonomy_terms' => '',
			'taxonomy_operator' => 'OR',
			'taxonomy_count' => 0
		);
		extract( wp_parse_args( $args, $defaults ) );
		$taxonomy_count = absint( $taxonomy_count );
		?>
		<tbody class='pb_admin_container' id='taxonomy<?php echo esc_attr( $taxonomy_count ); ?>'>
		<tr>
			<th>
			<strong><label for='taxonomy_name<?php echo esc_attr( $taxonomy_count ); ?>'><?php echo esc_html( __( 'Taxonomy Name', 'it-l10n-loopbuddy' ) ); ?></label><?php $this->parent->tip( __( 'The name of the Taxonomy to display', 'it-l10n-loopbuddy' ) ); ?></strong>
			</th>
			<td>
			<?php
				$ajax_assist_url = false;
				$ajax_assist_url = add_query_arg( 
						array(
							'action' => 'pb_loopbuddy_assist',
							'type' => 'tax_name',
							'id' => $taxonomy_count,
							'TB_iframe' => true
						),
						admin_url( 'admin-ajax.php' )
					);
				?>
					<input type='text' size='50' id='taxonomy_name<?php echo esc_attr( $taxonomy_count ); ?>' name='loopbuddy[taxonomies][<?php echo esc_attr( $taxonomy_count ); ?>][taxonomy_name]'  value='<?php echo esc_attr( $taxonomy_name ); ?>' />
					<?php if ( $ajax_assist_url ): ?>
					<a href='<?php echo esc_url( $ajax_assist_url ); ?>' class="thickbox pb_ajax_assist" ><?php _e( 'Assist', 'it-l10n-loopbuddy' ); ?></a>&nbsp;|&nbsp;<a onclick='jQuery.lb_taxonomies.clear( "<?php echo esc_js( $taxonomy_count ); ?>" ); return false;' href='#' class="pb_ajax_assist" alt='<?php _e( 'Clear Item', 'it-l10n-loopbuddy' ); ?>'><?php _e( 'Clear Taxonomy', 'it-l10n-loopbuddy' ); ?></a>
					<?php
					endif; //and ajax_assist
			?>
			</td>
		</tr>
		<tr>
			<th>
			<strong><label for='taxonomy_terms<?php echo esc_attr( $taxonomy_count ); ?>'><?php echo esc_html( __( 'Taxonomy Terms', 'it-l10n-loopbuddy' ) ); ?></label><?php $this->parent->tip( __( 'The terms within the taxonomy to match', 'it-l10n-loopbuddy' ) ); ?></strong>
			</th>
			<td>
			<input type='text' size='50' id='taxonomy_terms<?php echo esc_attr( $taxonomy_count ); ?>' name='loopbuddy[taxonomies][<?php echo esc_attr( $taxonomy_count ); ?>][taxonomy_terms]'  value='<?php echo esc_attr( $taxonomy_terms ); ?>' />
			</td>
		</tr>
		<tr>
			<th>
			<strong><label for='taxonomy_operator<?php echo esc_attr( $taxonomy_count ); ?>'><?php echo esc_html( __( 'Taxonomy Operator', 'it-l10n-loopbuddy' ) ); ?></label><?php $this->parent->tip( __( 'Determine the relationship between the taxonomy terms.', 'it-l10n-loopbuddy' ) ); ?></strong>
			</th>
			<td>
			<select id='taxonomy_operator<?php echo esc_attr( $taxonomy_count ); ?>' name='loopbuddy[taxonomies][<?php echo esc_attr( $taxonomy_count ); ?>][taxonomy_operator]'>
				<?php $this->operator( array( 'value' => $taxonomy_operator ) ); ?>
			</select>
			</td>
		</tr>
		<tr>
			<td colspan='2'>
			<input style="float: right;" type='button' class='button-secondary' onclick='jQuery.lb_taxonomies.remove_tax( "#taxonomy<?php echo esc_js( $taxonomy_count ); ?>" ); return false;' value='<?php esc_attr_e( 'Remove Taxonomy', 'it-l10n-loopbuddy' ); ?>' />
			</td>
		</tr>
		</tbody><!-- .pb_admin_container-->
		<?php
	} //end display_taxonomy
	
	public function taxonomy_button( $args ) {
		$defaults = array(
			'class' => 'button-secondary',
			'label' => __( 'Add Taxonomy', 'it-l10n-loopbuddy' ),
			'id' => 'add_taxonomy'
		);
		extract( wp_parse_args( $args, $defaults ) );
		?>
		<p><input type='button' class='<?php echo esc_attr( $class ); ?>' id='<?php echo esc_attr( $id ); ?>' value='<?php echo esc_attr( $label ); ?>' /></p>
		<?php
	} //end taxonomy_button
	public function taxonomy_hidden( $taxonomy_count = 0 ) {
		?>
		<input type='hidden' name='taxonomy_count' id='taxonomy_count' value='<?php echo esc_attr( $taxonomy_count ); ?>' />
		<?php
	} //end taxonomy_hidden
	public function taxonomy_relation( $args ) {
		$defaults = array(
			'taxonomy_count' => 0,
			'relation' => 'AND'
		);
		extract( wp_parse_args( $args, $defaults ) );
		$style = "style='display: none;'";
		if ( $taxonomy_count >= 2 ) {
			$style = '';
		}
		?>
		<tbody id='taxonomy_relation_container' <?php echo $style; ?>>
		<tr>
			<th>
			<strong><label for='taxonomy_relation'><?php echo esc_html( __( 'Relation', 'it-l10n-loopbuddy' ) ); ?></label><?php $this->parent->tip( __( 'The conditional relationship between the other taxonomies.', 'it-l10n-loopbuddy' ) ); ?></strong>
			</th>
			<td>
			<select name='loopbuddy[taxonomy_relation]'>
				<option value='AND' <?php selected( $relation, 'AND' ); ?>>AND</option>
				<option value='OR' <?php selected( $relation, 'OR' ); ?>>OR</option>
			</select>
			</td>
		</tr>
		</tbody><!-- #taxonomy_relation_container -->
		<?php
	} //end taxonomy_relation
			
	public function display_meta( $args ) {
		$defaults = array(
			'meta_key' => '',
			'meta_value' => '',
			'meta_compare' => '=',
			'meta_type' => 'CHAR',
			'meta_count' => 0,
		);
		extract( wp_parse_args( $args, $defaults ) );
		$meta_count = absint( $meta_count );
		?>
		<tbody class='pb_admin_container' id='meta<?php echo esc_attr( $meta_count ); ?>'>
		<tr>
			<th>
			<strong><label for='meta_key<?php echo esc_attr( $meta_count ); ?>'><?php echo esc_html( __( 'Meta Key', 'it-l10n-loopbuddy' ) ); ?></label><?php $this->parent->tip( __( 'Choose which meta key to search for', 'it-l10n-loopbuddy' ) ); ?></strong>
			</th>
			<td>
			
				<input type='text' size='50' id='meta_key<?php echo esc_attr( $meta_count ); ?>' name='loopbuddy[meta][<?php echo esc_attr( $meta_count ); ?>][meta_key]'  value='<?php echo esc_attr( $meta_key ); ?>' />
			</td>
		</tr>
		<tr>
			<th>
			<strong><label for='meta_value<?php echo esc_attr( $meta_count ); ?>'><?php echo esc_html( __( 'Meta Value', 'it-l10n-loopbuddy' ) ); ?></label><?php $this->parent->tip( __( 'Choose the meta value that the meta key must be compared to', 'it-l10n-loopbuddy' ) ); ?></strong>
			</th>
			<td>
			
				<input type='text' size='50' id='meta_value<?php echo esc_attr( $meta_count ); ?>' name='loopbuddy[meta][<?php echo esc_attr( $meta_count ); ?>][meta_value]'  value='<?php echo esc_attr( $meta_value ); ?>' />
			</td>
		</tr>
		<tr>
			<th>
			<strong><label for='meta_compare<?php echo esc_attr( $meta_count ); ?>'><?php echo esc_html( __( 'Meta Compare', 'it-l10n-loopbuddy' ) ); ?></label><?php $this->parent->tip( __( 'Choose how the meta value should be compared.', 'it-l10n-loopbuddy' ) ); ?></strong>
			</th>
			<td>
			<select id='meta_compare<?php echo esc_attr( $meta_count ); ?>' name='loopbuddy[meta][<?php echo esc_attr( $meta_count ); ?>][meta_compare]'>
				<?php $this->meta_compare( array( 'value' => $meta_compare ) ); ?>
			</select>
			</td>
		</tr>
		<tr>
			<th>
			<strong><label for='meta_type<?php echo esc_attr( $meta_count ); ?>'><?php echo esc_html( __( 'Meta Type', 'it-l10n-loopbuddy' ) ); ?></label><?php $this->parent->tip( __( 'Choose the meta value data type', 'it-l10n-loopbuddy' ) ); ?></strong>
			</th>
			<td>
			<select id='meta_type<?php echo esc_attr( $meta_count ); ?>' name='loopbuddy[meta][<?php echo esc_attr( $meta_count ); ?>][meta_type]'>
				<?php $this->meta_type( array( 'value' => $meta_type ) ); ?>
			</select>
			</td>
		</tr>
		<tr>
			<td colspan='2'>
			<input style="float: right;" type='button' class='button-secondary' onclick='jQuery.lb_taxonomies.remove_meta( "#meta<?php echo esc_js( $meta_count ); ?>" ); return false;' value='<?php esc_attr_e( 'Remove Meta', 'it-l10n-loopbuddy' ); ?>' />
			</td>
		</tr>
		</tbody><!-- .pb_admin_container-->
		<?php
	} //end display_meta
	public function meta_button( $args ) {
		$defaults = array(
			'class' => 'button-secondary',
			'label' => __( 'Add Meta', 'it-l10n-loopbuddy' ),
			'id' => 'add_meta'
		);
		extract( wp_parse_args( $args, $defaults ) );
		?>
		<p><input type='button' class='<?php echo esc_attr( $class ); ?>' id='<?php echo esc_attr( $id ); ?>' value='<?php echo esc_attr( $label ); ?>' /></p>
		<?php
	} //end meta_button
	public function meta_hidden( $meta_count = 0 ) {
		?>
		<input type='hidden' name='meta_count' id='meta_count' value='<?php echo esc_attr( $meta_count ); ?>' />
		<?php
	} //end meta_hidden
	public function meta_relation( $args ) {
		$defaults = array(
			'meta_count' => 0,
			'relation' => 'AND'
		);
		extract( wp_parse_args( $args, $defaults ) );
		$style = "style='display: none;'";
		if ( $meta_count >= 2 ) {
			$style = '';
		}
		?>
		<tbody id='meta_relation_container' <?php echo $style; ?>>
		<tr>
			<th>
			<strong><label for='meta_relation'><?php echo esc_html( __( 'Relation', 'it-l10n-loopbuddy' ) ); ?></label><?php $this->parent->tip( __( 'The conditional relationship between the other meta items.', 'it-l10n-loopbuddy' ) ); ?></strong>
			</th>
			<td>
			<select name='loopbuddy[meta_relation]'>
				<option value='AND' <?php selected( $relation, 'AND' ); ?>>AND</option>
				<option value='OR' <?php selected( $relation, 'OR' ); ?>>OR</option>
			</select>
			</td>
		</tr>
		</tbody><!-- #meta_relation_container -->
		<?php
	} //end meta_relation
	
	public function text_input( $args ) {
	
	} //end text_input
	public function integer_range( $args ) {
		extract( wp_parse_args( $args, array( 'min' => 0, 'max' => 0, 'value' => false ) ) );
		?>
		<option <?php selected( false, $value ); ?> value='default' ><?php _e( 'Select an Option', 'it-l10n-loopbuddy' ); ?></option>
		<?php
		for( $i = $min; $i <= $max; $i++ ) {
			?>
			<option <?php selected( $value, $i ); ?> value='<?php echo esc_attr( $i ); ?>'><?php echo esc_html( $i ); ?></option>
			<?php
		}
	} //end integer_range
	public function post_types( $args, $unique_id ) {
		extract( wp_parse_args( $args, array( 'value' => 'post' ) ) );
		//Get $value into array format
		if ( !is_array( $value ) ) {
			$value = array( $value );
		}
		$operators = get_post_types( array( 'public' => true ) );
		foreach ( $operators as $operator ) {
		?>
		<input id='loopbuddy_<?php echo esc_attr( $operator ); ?>' name='loopbuddy[<?php echo $unique_id; ?>][]' type="checkbox" value='<?php echo esc_attr( $operator ); ?>' <?php checked( in_array( $operator, $value ), true ); ?> />&nbsp;<label for="loopbuddy_<?php echo esc_attr( $operator ); ?>"><?php echo esc_html( $operator ); ?></label><br />
		<?php
		}
		?>
		<input id='loopbuddy_any_posts' name='loopbuddy[<?php echo $unique_id; ?>][]' type="checkbox" value='any' <?php checked( in_array( 'any', $value ), true ); ?> />&nbsp;<label for="loopbuddy_any_posts">Any</label><br />
		<?php
	} //end post_types
	public function post_status( $args = array(), $unique_id = '' ) {
		extract( wp_parse_args( $args, array( 'value' => 'publish', 'type' => 'checkbox' ) ) );
		if ( !is_array( $value ) ) {
			$value = array( $value );
		}
		$operators = array( 
			'publish' => __( 'Published', 'it-l10n-loopbuddy' ),
			'pending' => __( 'Pending Review', 'it-l10n-loopbuddy' ),
			'draft' => __( 'Draft Posts', 'it-l10n-loopbuddy' ),
			'auto-draft' => __( 'A draft post with no content', 'it-l10n-loopbuddy' ),
			'future' => __( 'Posts scheduled in the future', 'it-l10n-loopbuddy' ),
			'private' => __( 'Private Posts', 'it-l10n-loopbuddy' ),
			'inherit' => __( 'Revisions or Attachments', 'it-l10n-loopbuddy' ),
			'trash' => __( 'Trashed Posts', 'it-l10n-loopbuddy' ),
			'any' => __( 'Any Status', 'it-l10n-loopbuddy' ), 
		);
		foreach ( $operators as $operator => $label ) {
			if ( $type == 'select' ):
				?>
				<option value='<?php echo esc_attr( $operator ); ?>'><?php echo esc_html( $operator ); ?></option>
				<?php
			else:
				?>
				<input id='loopbuddy_<?php echo esc_attr( $operator ); ?>' name='loopbuddy[<?php echo $unique_id; ?>][]' type="checkbox" value='<?php echo esc_attr( $operator ); ?>' <?php checked( in_array( $operator, $value ), true ); ?> />&nbsp;<label for="loopbuddy_<?php echo esc_attr( $operator ); ?>"><?php echo esc_html( $label ); ?></label><br />
				<?php
			endif;
		?>
		
		<?php
		}
	} //end post_status
	public function order( $args ) {
		extract( wp_parse_args( $args, array( 'value' => 'DESC' ) ) );
		$operators = array( 
			'ASC' => __( 'Low to High (e.g., A-Z)', 'it-l10n-loopbuddy' ),
			'DESC' => __( 'High to Low (e.g., Z-A)', 'it-l10n-loopbuddy' ) 
		);
		foreach ( $operators as $operator => $label ) {
		?>
		<option value='<?php echo esc_attr( $operator ); ?>' <?php selected( $value, $operator ); ?>><?php echo esc_html( $label ); ?></option>
		<?php
		}
	} //end orderby
	public function orderby( $args ) {
		extract( wp_parse_args( $args, array( 'value' => 'date' ) ) );
		$operators = array( 
			'none' => __( 'None', 'it-l10n-loopbuddy' ),
			'id' => __( 'Post or Page ID', 'it-l10n-loopbuddy' ), 
			'author' => __( 'Author', 'it-l10n-loopbuddy' ), 
			'title' => __( 'Title', 'it-l10n-loopbuddy' ), 
			'date' => __( 'Date Published', 'it-l10n-loopbuddy' ), 
			'modified' => __( 'Date Modified', 'it-l10n-loopbuddy' ), 
			'parent' => __( 'Post/Page Parent', 'it-l10n-loopbuddy' ), 
			'rand' => __( 'Random Order', 'it-l10n-loopbuddy' ), 
			'comment_count' => __( 'Number of Comments', 'it-l10n-loopbuddy' ), 
			'menu_order' => __( 'Menu Order', 'it-l10n-loopbuddy' ), 
			'meta_value' => __( 'Meta Value', 'it-l10n-loopbuddy' ), 
			'meta_value_num' => __( 'Numeric Meta Value', 'it-l10n-loopbuddy' ) 
		);
		foreach ( $operators as $operator  => $label) {
		?>
		<option value='<?php echo esc_attr( $operator ); ?>' <?php selected( $value, $operator ); ?>><?php echo esc_html( $label ); ?></option>
		<?php
		}
	} //end orderby
	public function operator( $args ) {
		extract( wp_parse_args( $args, array( 'value' => 'OR' ) ) );
		$operators = array( 'OR', 'AND', 'IN', 'NOT IN' );
		foreach ( $operators as $operator ) {
		?>
		<option value='<?php echo esc_attr( $operator ); ?>' <?php selected( $value, $operator ); ?>><?php echo esc_html( $operator ); ?></option>
		<?php
		}
	} //end operator
	public function meta_compare( $args ) {
		
		extract( wp_parse_args( $args, array( 'value' => '=' ) ) );
		$value = htmlspecialchars_decode( $value );
		$operators = array( '=', '!=', '>', '>=', '<', '<=', 'LIKE', 'NOT LIKE', 'IN', 'NOT IN', 'BETWEEN', 'NOT BETWEEN' );
		foreach ( $operators as $operator ) {
		?>
		<option value='<?php echo esc_attr( $operator ); ?>' <?php selected( $value, $operator ); ?>><?php echo esc_html( $operator ); ?></option>
		<?php
		}
	} //end meta_compare
	public function meta_type( $args ) {
		extract( wp_parse_args( $args, array( 'value' => 'CHAR' ) ) );
		$operators = array( 'NUMERIC', 'BINARY', 'CHAR', 'DATE', 'DATETIME', 'DECIMAL', 'SIGNED', 'TIME', 'UNSIGNED' );
		foreach ( $operators as $operator ) {
		?>
		<option value='<?php echo esc_attr( $operator ); ?>' <?php selected( $value, $operator ); ?>><?php echo esc_html( $operator ); ?></option>
		<?php
		}
	} //end meta_type
	//Retrieves a query title from the saved options
	public function get_query_title() {
		$title = 'Default';
		if ( isset( $_GET[ 'edit' ] ) ) {
			if ( isset( $this->parent->_options[ 'queries' ][ $_GET[ 'edit' ] ] ) ) {
				$query = $this->parent->_options[ 'queries' ][ $_GET[ 'edit' ] ];
				$title = isset( $query[ 'title' ] ) ? $query[ 'title' ] : $title;
			}
		}
		return $title;
	} //end get_query_title
	private function get_value( $args ) {
		if ( is_array( $args ) ) {
			if ( isset( $args[ 'value' ] ) ) {
				return esc_attr( $args[ 'value' ] );
			}
		}
	} //end get_value
	public function output_html() {
		?>
		<form method="post" id="pb_edit_queries" action="<?php echo $this->parent->_selfLink; ?>-queries&edit=<?php echo esc_js( $_GET['edit'] ); ?>">
		<h2><img src="<?php echo esc_url( $this->parent->_pluginURL . "/images/loopbuddy_rings.png" ); ?>" style="vertical-align: -4px;" /> Query Settings</h2>
		<h3 style="margin-bottom: 40px;">Query Editor for <?php echo esc_html( $this->get_query_title() ); ?></h3>
		<table class="form-table">
		<tr>
			<th><label for='title'>Query Title</label></th>
			<td><input type='text' size='30' name='title' value='<?php echo esc_html( $this->get_query_title() ); ?>' />
			</td>
		</tr>
		</table>
<div style="width: 80%; min-width: 750px;" class="postbox-container">
	<div class="metabox-holder">
	<div class="meta-box-sortables ui-sortable">
		
		<?php
		$keys = $this->get_keys_to_edit();
		foreach ( $keys as $label => $key ) :
			$class = '';
			if ( $label != 'Post/Page Parameters' && $label != 'General Parameters' ) {
				$class = ' closed';
			}
	     ?>
	    <div class="postbox<?php echo esc_attr( $class ); ?>" style="display: block;">
	        <div title="Click to toggle" class="handlediv"><br></div><!--/.handlediv-->
	        <h3 class="hndle"><span><?php echo esc_html( $label ); ?></span></h3>
	        <div class="inside">
	        	<?php
	        		$table_slug = esc_attr( sanitize_title( $label ) );
	        	?>
				<table class="form-table" id='<?php echo $table_slug; ?>'>
					<?php
					
						foreach ( $key as $item => $data) {
							//If the type is a note, just display the note and skip to the next item
							
							if ( !isset( $data[ 'type' ] ) ) $data[ 'type' ] = false; //Fail safe
							//Taxonomy params
							if ( is_string( $item ) && $item == 'taxonomy' ) {
								//Go through the taxonomies array and call the display_taxonomy function and pass the taxonomy params
								/*[0] => Array
					                (
					                    [taxonomy_name] => category
					                    [taxonomy_terms] => 18,1
					                    [taxonomy_operator] => AND
					                )
					                */
					               $taxonomy_count = 0;
								if ( isset( $data[ 'taxonomies' ] ) && is_array( $data[ 'taxonomies' ] ) ) {
									foreach ( $data[ 'taxonomies' ] as $index => $taxonomy ) {
										$taxonomy_count += 1;
										$taxonomy[ 'taxonomy_count' ] = $taxonomy_count;
										call_user_func( array( &$this, 'display_taxonomy' ), $taxonomy );
									}
								}
								$relation = isset( $data[ 'relation' ] ) ? sanitize_text_field( $data[ 'relation' ] ) : 'AND'; 
								
								call_user_func( array( &$this, 'taxonomy_relation' ), array( 'taxonomy_count' => $taxonomy_count, 'relation' => $relation ) );
								?>
								<tbody id='add_taxonomy_container'>
								<tr>
									<td colspan='2'>
										<?php call_user_func( array( &$this, 'taxonomy_hidden' ), $taxonomy_count ); ?>
										<?php call_user_func( array( &$this, 'taxonomy_button' ), array() ); ?>
									</td>
								</tr>
								</tbody>
								<?php
								 continue;
							} /*Meta*/ elseif ( is_string( $item ) && $item == 'meta' ) {
								$meta_count = 0;
								if ( isset( $data[ 'meta_items' ] ) && is_array( $data[ 'meta_items' ] ) ) {
									foreach ( $data[ 'meta_items' ] as $index => $meta ) {
										$meta_count += 1;
										$meta[ 'meta_count' ] = $meta_count;
										call_user_func( array( &$this, 'display_meta' ), $meta );
									}
								}
								$relation = isset( $data[ 'relation' ] ) ? sanitize_text_field( $data[ 'relation' ] ) : 'AND'; 
								call_user_func( array( &$this, 'meta_relation' ), array( 'meta_count' => $meta_count, 'relation' => $relation ) );
								?>
								<tbody id='add_meta_container'>
								<tr>
									<td colspan='2'>
										<?php call_user_func( array( &$this, 'meta_hidden' ), $meta_count ); ?>
										<?php call_user_func( array( &$this, 'meta_button' ), array() ); ?>
									</td>
								</tr>
								</tbody>
								<?php	
								continue;						
							} /*Notes*/ elseif ( isset( $data[ 'type' ] ) && isset( $data[ 'tip' ] ) && $data[ 'type' ] == 'note' ) {
								?>
								<tr>
									<td colspan='2'>
										<p><?php echo esc_html( $data[ 'tip' ] ); ?></p>
									</td>
								</tr>
								<?php
								continue;
							}
							?>
							<tr>
								<th>
								<strong><label for='<?php echo esc_attr( 'pb_' . $item ); ?>'><?php echo esc_html( $data[ 'label' ] ); ?></label><?php if ( isset( $data[ 'tip' ] ) ) $this->parent->tip( $data[ 'tip' ] ); ?></strong>
								</th>
								<td>
								<?php
									$unique_id = esc_attr( $item );
									$ajax_assist_url = false;
										if ( isset( $data[ 'args' ][ 'ajax_assist' ] ) ) {
											$ajax_assist_url = add_query_arg( 
												array(
													'action' => 'pb_loopbuddy_assist',
													'type' => $data[ 'args' ][ 'ajax_assist' ][ 'type' ],
													'id' => $unique_id,
													'TB_iframe' => true
												),
												admin_url( 'admin-ajax.php' )
											);
										} //end ajax_assist url
									//Generate dropdown items
									if ( $data[ 'type' ] == 'dropdown' ) {
										?>
										<select name='loopbuddy[<?php echo $unique_id; ?>]' id='<?php echo $unique_id; ?>'>
										<?php call_user_func( array( &$this, $data[ 'callback' ] ), $data[ 'args' ] ); ?>
										</select>
										<?php
									} elseif ( $data[ 'type' ] == 'input' ) {
										//Generate Text input items
										?>
										<input type='text' size='<?php echo $unique_id; ?>' id='<?php echo $unique_id; ?>' name='loopbuddy[<?php echo $unique_id; ?>]'  value='<?php echo $this->get_value( $data[ 'args' ] ); ?>' />
										<?php if ( $ajax_assist_url ): ?>
										<a href='<?php echo esc_url( $ajax_assist_url ); ?>' class="thickbox pb_ajax_assist" alt='<?php echo esc_attr( 'pb_' . $item ); ?>'><?php _e( 'Assist', 'it-l10n-loopbuddy' ); ?></a>&nbsp;|&nbsp;<a onclick='jQuery( "#<?php echo esc_js( $unique_id ); ?>" ).val(""); return false;' href='#' class="pb_ajax_assist" alt='<?php _e( 'Clear Item', 'it-l10n-loopbuddy' ); ?>'><?php _e( 'Clear Item', 'it-l10n-loopbuddy' ); ?></a>
										<?php
										endif; //and ajax_assist
									} elseif ( $data[ 'type' ] == 'radio' ) {
										$yes_label = __( 'Yes', 'it-l10n-loopbuddy' );
										$no_label = __( 'No', 'it-l10n-loopbuddy' );
										if ( $unique_id == 'include_exclude' ) {
											$yes_label = __( 'Include', 'it-l10n-loopbuddy' );
											$no_label = __( 'Exclude', 'it-l10n-loopbuddy' );
										}
										//Generate radio items
										$checked = isset( $data[ 'args' ][ 'value' ] ) ? $data[ 'args' ][ 'value' ] : 'on';
										?>
										<label for='<?php echo $unique_id; ?>_on'><?php echo esc_html( $yes_label ); ?></label><input value='on' type='radio'' id='<?php echo $unique_id; ?>_on' name='loopbuddy[<?php echo $unique_id; ?>]' <?php checked( 'on', $checked ); ?> />&nbsp;&nbsp;&nbsp;
										<label for='<?php echo $unique_id; ?>_off'><?php echo esc_html( $no_label ); ?></label><input value='off' type='radio'' id='<?php echo $unique_id; ?>_off' name='loopbuddy[<?php echo $unique_id; ?>]' <?php checked( 'off', $checked ); ?> />
										<?php
									} elseif ( $data[ 'type' ] == 'checkbox' ) {
										call_user_func( array( &$this, $data[ 'callback' ] ), $data[ 'args' ], $unique_id );
									}
								?>
								</td>
							</tr>
							<?php
						} //end foreach
					?>
				</table>
			 </div><!--/.inside-->
	    </div><!--/.postbox-->
	    <?php endforeach; ?>
	</div><!--/.meta-box-sortables ui-sortable -->
	</div><!--/.metabox-holder-->
</div><!--/.postbox-container-->
<p class="submit clear"><input type="submit" name="save" value="<?php esc_attr_e( 'Save Query', 'it-l10n-loopbuddy' ); ?>" class="button-primary" id="save" /></p>
<?php 
	wp_nonce_field( 'lb_save_query', 'lb_query' );
?>
</form><!--#pb_edit_queries-->
            <?php
	} //end output_html
} //end class
?>