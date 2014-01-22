<?php
if ( !class_exists( "pluginbuddy_loopbuddy_render_slotitems" ) ) {
	class pluginbuddy_loopbuddy_render_slotitems {
		function post_type_name( $rs, $slot_item ) {
			$before = empty( $slot_item[ 'before_text' ] ) ? '' : esc_html( $slot_item[ 'before_text' ] );
			$after = empty( $slot_item[ 'after_text' ] ) ? '' : esc_html( $slot_item[ 'after_text' ] );
			
			//Try to get a label
			$label = false;
			$post_type = get_post_type_object( get_post_type() );
			$labels = isset( $post_type->labels ) ? $post_type->labels : false;
			if ( $labels ) {
				if ( isset( $labels->name ) ) {
					$label = $labels->name;
				}
			} else {
				if ( isset( $post_type->label ) ) {
					$label = $post_type->label;
				}
			}
			if ( !$label ) return '';
			$return = esc_html( $label );
			$return = $before . $return . $after;
			$return = pluginbuddy_loopbuddy_render_slotitems::get_output( $return, $rs, $slot_item, true );
			return $return;
		} //end post_type_name
		
		function get_search_form( $rs, $slot_item ) {
			ob_start();
			if ( isset( $slot_item[ 'custom_search' ] ) && $slot_item[ 'custom_search' ] == 'custom_search'  ) {
				?>
				<form role="search" method="get" id="searchform_<?php echo esc_attr( absint( $rs->ID ) ); ?>" action="<?php echo esc_url( home_url( '/' ) ); ?>" >
				<div><label class="screen-reader-text" for="s"><?php echo esc_html( $slot_item[ 'custom_search_label' ] ); ?></label>
				<input type="text" placeholder="<?php echo esc_attr( $slot_item[ 'custom_search_placeholder' ] ); ?>" value="<?php echo esc_attr( get_search_query() ); ?>" name="s" id="s" />
				<input type="submit" id="searchsubmit" value="<?php echo esc_attr( $slot_item[ 'custom_search_button' ] ); ?>" />
				</div>
				</form>
				<?php
			} else {
				get_search_form();
			}
			$return = ob_get_clean();
			$return = pluginbuddy_loopbuddy_render_slotitems::get_output( $return, $rs, $slot_item, true );
			return $return;
		} //end get_search_form
		function the_title( $rs, $slot_item ) {
			
			//Wrap Title URL in an anchor tag if applicable		
			$title_url = false;
			$custom_url = empty( $slot_item[ 'custom_url' ] ) ? false : esc_url( $slot_item[ 'custom_url' ] );
			$permalink = $slot_item[ 'permalink' ] == 'on' ? true : false;
			if ( $custom_url && !$permalink ) {
				$title_url = $custom_url;
			} elseif ( $permalink ) {
				$title_url = esc_url( get_permalink( $rs->ID ) );
			}
			$before = empty( $slot_item[ 'before_text' ] ) ? '' : esc_html( $slot_item[ 'before_text' ] );
			$after = empty( $slot_item[ 'after_text' ] ) ? '' : esc_html( $slot_item[ 'after_text' ] );
			ob_start();
			the_title();
			$return = ob_get_clean();
			//Wrap anchor around title
			if ( $title_url ) {
				$return = sprintf( "<a href='%s'>%s</a>", $title_url, $return );
			}
			$return = $before . $return . $after;
			$return = pluginbuddy_loopbuddy_render_slotitems::get_output( $return, $rs, $slot_item, true );
			return $return;
		}
		function comments_number( $rs, $slot_item ) {
			$slot_item = stripslashes_deep( $slot_item );
			$before = empty( $slot_item[ 'before_text' ] ) ? '' : esc_html( $slot_item[ 'before_text' ] );
			$after = empty( $slot_item[ 'after_text' ] ) ? '' : esc_html( $slot_item[ 'after_text' ] );
			$comment_link = isset( $slot_item[ 'comment_number_link' ] ) ? $slot_item[ 'comment_number_link' ] : 'off';
			ob_start();
			if ( $comment_link == 'off' ) :
				comments_number( $slot_item[ 'comment_number_zero' ], $slot_item[ 'comment_number_one' ], $slot_item[ 'comment_number_more' ]);
			else:
				comments_popup_link( $slot_item[ 'comment_number_zero' ], $slot_item[ 'comment_number_one' ], $slot_item[ 'comment_number_more' ] );
			endif;
			$return = ob_get_clean();
			$return = $before . $return . $after;
			$return = pluginbuddy_loopbuddy_render_slotitems::get_output( $return, $rs, $slot_item, true );
			return $return;
		}
		function the_content( $rs, $slot_item ) {
						
			ob_start();
			the_content();
			$return = ob_get_clean();
			$return = pluginbuddy_loopbuddy_render_slotitems::get_output( $return, $rs, $slot_item, true );
			return $return;
		}
		function the_date( $rs, $slot_item ) {
			
			$before = empty( $slot_item[ 'before_text' ] ) ? '' : esc_html( $slot_item[ 'before_text' ] );
			$after = empty( $slot_item[ 'after_text' ] ) ? '' : esc_html( $slot_item[ 'after_text' ] );
			$date_format = isset( $slot_item[ 'date_format' ] ) ? trim( $slot_item[ 'date_format' ] ) : '';
			if ( empty( $date_format ) ) $date_format = false;
			
			$date_type = isset( $slot_item[ 'post_date' ] ) ? $slot_item[ 'post_date' ] : 'date_posted';
			$timestamp = $date_type == 'posted' ? strtotime( $rs->post_date ) : strtotime( $rs->post_modified );
			
			if ( !$date_format ) $date_format = get_option( 'date_format' ) . ' ' . get_option( 'time_format' );
			
			ob_start();
			if ( $date_type == 'date_posted' ) {
				echo $before . get_the_date( $date_format ) . $after;
			} else {
				echo $before . get_the_modified_date( $date_format) . $after;
			}
			$return = ob_get_clean();
			
			$return = pluginbuddy_loopbuddy_render_slotitems::get_output( $return, $rs, $slot_item, true );
			return $return;
		} //end the_date
		
		function the_excerpt( $rs, $slot_item ) {
			ob_start();
			the_excerpt();
			$return = ob_get_clean();
			$return = pluginbuddy_loopbuddy_render_slotitems::get_output( $return, $rs, $slot_item, true );
			return $return;
		} //end the_excerpt
		function the_ID( $rs, $slot_item ) {
			$before = empty( $slot_item[ 'before_text' ] ) ? '' : esc_html( $slot_item[ 'before_text' ] );
			$after = empty( $slot_item[ 'after_text' ] ) ? '' : esc_html( $slot_item[ 'after_text' ] );
			ob_start();
			the_ID();
			$return = $before . ob_get_clean() . $after;
			$return = pluginbuddy_loopbuddy_render_slotitems::get_output( $return, $rs, $slot_item, true );
			return $return;
		} //end the_ID
		function the_permalink( $rs, $slot_item ) {
			$before = empty( $slot_item[ 'before_text' ] ) ? '' : esc_html( $slot_item[ 'before_text' ] );
			$after = empty( $slot_item[ 'after_text' ] ) ? '' : esc_html( $slot_item[ 'after_text' ] );
			$permalink_display = isset( $slot_item[ 'url_display' ] ) ? $slot_item[ 'url_display' ] : 'url_link';
			$permalink = $custom = get_permalink();
			if ( $permalink_display == 'url_link' ) {
				$custom = empty( $slot_item[ 'link_text' ] ) ? $custom : esc_html( $slot_item[ 'link_text' ] );
				$return = sprintf( '<a href="%s">%s</a>', $permalink, $custom );
			} else {
				$return = $permalink;
			} 
			$return = $before . $return . $after;
			$return = pluginbuddy_loopbuddy_render_slotitems::get_output( $return, $rs, $slot_item, true );
			return $return;
		} //end the_permalink
		function the_post_thumbnail( $rs, $slot_item ) {
			
			//Get the image size and before/after text
			if ( !has_post_thumbnail( $rs->ID ) ) return '';
			$before = empty( $slot_item[ 'before_text' ] ) ? '' : esc_html( $slot_item[ 'before_text' ] );
			$after = empty( $slot_item[ 'after_text' ] ) ? '' : esc_html( $slot_item[ 'after_text' ] );
			$max_width = empty( $slot_item[ 'max_width' ] ) ? 0 : absint( $slot_item[ 'max_width' ] );
			$max_height = empty( $slot_item[ 'max_height' ] ) ? 0 : absint( $slot_item[ 'max_height' ] );
			if ( $max_width > 1 && $max_height > 1 && $slot_item[ 'image_size' ] == 'custom' ) $size = array( $max_width, $max_height );
			else $size = $slot_item[ 'image_size' ];
			
			//Get the link display
			$link_display = isset( $slot_item[ 'attachment_link_display' ] ) ? $slot_item[ 'attachment_link_display' ] : 'none';
			$link = false;
			if ( $link_display == 'post_permalink' ) {
				$link = esc_url( get_permalink() );
			} elseif ( $link_display == 'attachment_permalink' || $link_display == 'permalink' /*permalink for LB 1.1 and below*/ ) {
				$src = get_attachment_link( get_post_thumbnail_id( get_the_ID() ) );
				if ( $src ) $link = esc_url( $src );
			} elseif ( $link_display == 'image' ) {
				$src = wp_get_attachment_image_src( get_post_thumbnail_id( get_the_ID() ), $size );
				if ( is_array( $src ) ) {
					$link = esc_url( $src[ 0 ] );
				}
			} elseif ( $link_display == 'custom' ) {
				$custom_url = trim( $slot_item[ 'custom_url' ] );
				if ( !empty( $custom_url ) ) {
					$link = esc_url( $custom_url );
				}
			}
			ob_start();
			the_post_thumbnail( $size );
			$return = ob_get_clean();
			if ( $link ) {
				$return = sprintf( "<a href='%s'>%s</a>", $link, $return );
			}
			$return = $before . $return . $after;
			$return = pluginbuddy_loopbuddy_render_slotitems::get_output( $return, $rs, $slot_item, true );
			return $return;
		}
		function the_tags( $rs, $slot_item ) {
			
			$return = get_the_term_list( $rs->ID, 'post_tag', isset( $slot_item[ 'before_text' ] ) ? $slot_item[ 'before_text' ] : '', $slot_item['separator'], isset( $slot_item[ 'after_text' ] ) ? $slot_item[ 'after_text' ] : '' );
			
			$return = pluginbuddy_loopbuddy_render_slotitems::get_output( $return, $rs, $slot_item, true );
			return $return;
		}
		function the_shortlink( $rs, $slot_item ) {
			$link_text = empty( $slot_item[ 'link_text' ] ) ? '' : esc_attr( $slot_item[ 'link_text' ] );
			$link_title = empty( $slot_item[ 'link_title' ] ) ? '' : esc_attr( $slot_item[ 'link_title' ] );
			$before = empty( $slot_item[ 'before_text' ] ) ? '' : esc_html( $slot_item[ 'before_text' ] );
			$after = empty( $slot_item[ 'after_text' ] ) ? '' : esc_html( $slot_item[ 'after_text' ] );
			$url_display = isset( $slot_item[ 'url_display' ] ) ? $slot_item[ 'url_display' ] : 'url_link';
			
			//Get the shortlink
			$shortlink = wp_get_shortlink( $rs->ID, 'query' );
			if ( empty( $shortlink ) ) {
				//WordPress (as of 3.1) returns an empty string if a permalink structure hasn't been defined.  Let's at least output the permalink
				$shortlink = get_permalink( $rs->ID );
			}
				
			if ( $url_display == 'url_link'  ) {
				if ( empty( $link_text ) ) {
					$link_text = $shortlink; //Make the link text the shortlink if the link text isn't specified
				}
				ob_start();
				the_shortlink( $link_text, $link_title, $before, $after ); //There's really nothing we can do here if the permalink structure hasn't been set up
				$return = ob_get_clean();
			} else {
				//Just output the shortlink if there's no link text
				$return = $before . $shortlink . $after;
			}
			
			$return = pluginbuddy_loopbuddy_render_slotitems::get_output( $return, $rs, $slot_item, true );
			return $return;
		} //end the_shortlink
		
		function edit_post_link( $rs, $slot_item ) {
			$before = empty( $slot_item[ 'before_text' ] ) ? '' : esc_html( $slot_item[ 'before_text' ] );
			$after = empty( $slot_item[ 'after_text' ] ) ? '' : esc_html( $slot_item[ 'after_text' ] );
			$link_text = empty( $slot_item[ 'link_text' ] ) ? 'Edit Post' : esc_html( $slot_item[ 'link_text' ] );
			ob_start();
			edit_post_link( $link_text, $before, $after ); 
			$return = ob_get_clean();
			$return = pluginbuddy_loopbuddy_render_slotitems::get_output( $return, $rs, $slot_item, true );
			return $return;
		} //end edit_post_link
		function shortcode( $rs, $slot_item ) {
			$return = do_shortcode( $slot_item['shortcode'] );
			$return = pluginbuddy_loopbuddy_render_slotitems::get_output( $return, $rs, $slot_item );
			return $return;
		} //end shortcode
		function the_category( $rs, $slot_item ) {
			$before = empty( $slot_item[ 'before_text' ] ) ? '' : esc_html( $slot_item[ 'before_text' ] );
			$after = empty( $slot_item[ 'after_text' ] ) ? '' : esc_html( $slot_item[ 'after_text' ] );
			ob_start();
				the_category( isset( $slot_item[ 'separator' ] ) ? $slot_item[ 'separator' ] : '' ); //There's really nothing we can do here if the permalink structure hasn't been set up
			$return = ob_get_clean();
			$return = $before . $return . $after;
			$return = pluginbuddy_loopbuddy_render_slotitems::get_output( $return, $rs, $slot_item, true );
			return $return;
		} //end the_category
		
		function text( $rs, $slot_item ) {
			if ( !isset( $slot_item[ 'text' ] ) ) return '';
			$return = do_shortcode( stripslashes( apply_filters( 'get_the_content', $slot_item[ 'text' ] ) ) );
			$return = pluginbuddy_loopbuddy_render_slotitems::get_output( $return, $rs, $slot_item, true );
			return $return;
		} //end text
		function comments() {
			ob_start();
			comments_template( '', true );
			return ob_get_clean();
		} //end comments
		
		function the_author( $rs, $slot_item ) {
			$after = empty( $slot_item[ 'after_text' ] ) ? '' : esc_html( $slot_item[ 'after_text' ] );
			ob_start();
			if ( !isset( $slot_item[ 'author_link_destination' ] ) ) {
				echo esc_html( get_the_author() );
			} elseif ( $slot_item['author_link_destination'] == 'custom' && !empty( $slot_item[ 'custom_url' ] ) ) {
				printf( "<a href='%s'>%s</a>", esc_url( $slot_item[ 'custom_url' ] ), esc_html( get_the_author() ) );
			} elseif ( $slot_item['author_link_destination'] == 'the_author' || $slot_item['author_link_destination'] == 'none' ) {
				echo esc_html( get_the_author() );
			} elseif ( $slot_item['author_link_destination'] == 'the_author_link' ) {
				the_author_link();
			} elseif ( $slot_item['author_link_destination'] == 'the_author_posts_link' ) {
				the_author_posts_link();
			}
			$return = ob_get_clean();
			$return = pluginbuddy_loopbuddy_render_slotitems::get_output( $return, $rs, $slot_item ) . $after;
			return $return;
		} //end the_author
		
		//Formats the output in classes and adds before text
		function get_output( $return, $rs, $slot_item, $skip_before = false ) {
			if ( is_wp_error( $return ) ) return '';
			$before = isset( $slot_item[ 'before_text' ]  ) && !$skip_before ? esc_html( $slot_item[ 'before_text' ] ) : '';
			$wrap_tag = isset( $slot_item[ 'wrap' ] ) ? strtolower( $slot_item[ 'wrap' ] ) : false;
			switch( $wrap_tag ) {
				case "span":
				case "div":
				case "p":
				case "h1":
				case "h2":
				case "h3":
				case "h4":
				case "h5":
					break;
				default:
					$wrap_tag = false;
					break;
			} //end switch
			$custom_class = isset( $slot_item[ 'custom_class' ] ) ? esc_attr( $slot_item[ 'custom_class' ] ) : '';
			if ( $wrap_tag ) {
				$return = sprintf( '<%1$s class="%2$s">%3$s%4$s</%1$s>', $wrap_tag, $custom_class, $before, $return ); 
			} else {
				$return = $before . $return;
			}
			return $return;
		} //get_output
		function the_author_meta( $rs, $slot_item ) {
			update_user_meta( $rs->post_author, 'kickin', 'hi there' );
			$before = empty( $slot_item[ 'before_text' ] ) ? '' : esc_html( $slot_item[ 'before_text' ] );
			$after = empty( $slot_item[ 'after_text' ] ) ? '' : esc_html( $slot_item[ 'after_text' ] );
			$user_meta = empty( $slot_item[ 'user_profile' ] ) ? false : $slot_item[ 'user_profile' ];
			$custom_url = empty( $slot_item[ 'custom_url' ] ) ? false: $slot_item[ 'custom_url' ];
			
			//See if they have a custom meta value
			if ( $user_meta == 'custom' && !empty( $slot_item[ 'meta_key' ] ) ) $user_meta = $slot_item[ 'meta_key' ];
			
			//Try to get the author meta
			$meta_value = get_the_author_meta( $user_meta, $rs->post_author );
			if ( !$meta_value ) return '';
			
			//Meta value is valid, let's go
			ob_start();
			the_author_meta( $user_meta );
			$return = ob_get_clean();
			if ( $custom_url ) $return = sprintf( "<a href='%s'>%s</a>", esc_url( $custom_url ), stripslashes( esc_html( $return ) ) );
			$return = $before . $return . $after;
			$return = pluginbuddy_loopbuddy_render_slotitems::get_output( $return, $rs, $slot_item, true );
			return $return;
		} //end the_author_meta
		
		function wp_get_attachment_image( $rs, $slot_item ) {
			if ( $slot_item[ 'image_size' ] == 'custom' ) {
				$slot_item[ 'image_size' ] = array(
					absint( $slot_item[ 'max_width' ] ),
					absint( $slot_item[ 'max_height' ] )
				);
			}
			$return = wp_get_attachment_image( $rs->ID, $slot_item['image_size'] );
			$return = pluginbuddy_loopbuddy_render_slotitems::get_output( $return, $rs, $slot_item, true );
			return $return;	
		}
		
		function the_attachment_link( $rs, $slot_item ) {
			$link_display = isset( $slot_item[ 'attachment_link_display' ] ) ? $slot_item[ 'attachment_link_display' ] : 'image';
			if ( $slot_item[ 'image_size' ] == 'custom' ) {
				$slot_item[ 'image_size' ] = array(
					absint( $slot_item[ 'max_width' ] ),
					absint( $slot_item[ 'max_height' ] )
				);
			}
			if ( $link_display != 'image' ) $slot_item[ 'image_size' ] = false;
			$custom_text = false;
			if ( $link_display == 'custom' ) {
				$custom_text = empty( $slot_item[ 'link_text' ] ) ? false : $slot_item[ 'link_text' ];
			}
			
			
			$return = wp_get_attachment_link( $rs->ID, $slot_item['image_size'], false, false, $custom_text );
			$return = pluginbuddy_loopbuddy_render_slotitems::get_output( $return, $rs, $slot_item, true );
			return $return;	
		}
		
		function post_custom( $rs, $slot_item ) {
			$before = empty( $slot_item[ 'before_text' ] ) ? '' : esc_html( $slot_item[ 'before_text' ] );
			$after = empty( $slot_item[ 'after_text' ] ) ? '' : esc_html( $slot_item[ 'after_text' ] );
			$meta_key = isset( $slot_item[ 'meta_key' ] ) ? trim( $slot_item[ 'meta_key' ] ) : '';
			if ( empty( $meta_key ) ) return '';
			
			$meta_value = get_post_meta( $rs->ID, $meta_key, true );
			//Don't return anything that isn't a string - todo - maybe printr non-string values
			$meta_value = maybe_unserialize( $meta_value );
			if ( is_object( $meta_value ) || is_array( $meta_value ) ) {
				ob_start();
				wp_print_r( $meta_value, false );
				$meta_value = ob_get_clean();
			}
			$return = $before . $meta_value . $after;
			$return = pluginbuddy_loopbuddy_render_slotitems::get_output( $return, $rs, $slot_item, true );
			return $return;
		} //end post_custom
		
		function the_meta( $rs, $slot_item ) {
			ob_start();
			$custom = get_post_custom();
			if ( $custom && count( $custom ) > 0 ) {
				?>
				<ul class="post-meta">
					<?php
					foreach ( $custom as $key => $value ) {
						if ( $key == 'lb_meta' || substr( $key, 0, 1 ) == '_' ) continue;
						$value = $value[ 0 ];
						$value = maybe_unserialize( $value );
						if ( is_object( $value ) || is_array( $value ) ) {
							ob_start();
							wp_print_r( $value, false );
							$value = ob_get_clean();
						} else {
							$value = esc_html( $value );
						}
						?>
						<li><span class="post-meta-key"><?php echo esc_html( $key ); ?>:&nbsp;</span><span class="post-meta-value"><?php echo $value; ?></span></li>
						<?php
					}
					?>
				</ul>
				<?php
			} else {
				return '';
			}
			$return = ob_get_clean();			
			$return = pluginbuddy_loopbuddy_render_slotitems::get_output( $return, $rs, $slot_item );
			return $return;
		} //end the_meta
		
		function the_search_query( $rs, $slot_item ) {
			$before = empty( $slot_item[ 'before_text' ] ) ? '' : esc_html( $slot_item[ 'before_text' ] );
			$after = empty( $slot_item[ 'after_text' ] ) ? '' : esc_html( $slot_item[ 'after_text' ] );
			$search_query = the_search_query();
			if ( empty( $search_query ) ) return '';
			$return = $before . $search_query . $after;
			$return = pluginbuddy_loopbuddy_render_slotitems::get_output( $return, $rs, $slot_item, true );
			return $return;	
		} //end the_search_query
		
		function the_taxonomies( $rs, $slot_item ) {
			$before = empty( $slot_item[ 'before_text' ] ) ? '' : esc_html( $slot_item[ 'before_text' ] );
			$after = empty( $slot_item[ 'after_text' ] ) ? '' : esc_html( $slot_item[ 'after_text' ] );
			$taxonomy = $slot_item[ 'taxonomies' ];
			$separator = $slot_item[ 'separator' ];
			$template = isset( $slot_item[ 'taxonomy_template' ] ) ? $slot_item[ 'taxonomy_template' ] : '%s: %l.';
			if ( $taxonomy == 'all' ) {
				$args = array(
					'post' => $rs->ID,
					'before' => $before,
					'after' => $after,
					'sep' => $separator,
					'template' => $template,
				);
				ob_start();
				the_taxonomies( $args );
				$return = ob_get_clean();
			} else {
				$return = get_the_term_list( $rs->ID, $taxonomy, $before, $separator, $after );
				if ( is_wp_error( $return ) ) return '';
			}
			$return = pluginbuddy_loopbuddy_render_slotitems::get_output( $return, $rs, $slot_item, true );
			return $return;	
		} //end the_taxonomies
		
		function bloginfo( $rs, $slot_item ) {
			if ( $slot_item[ 'bloginfo' ] == 'site_description' ) {
				$slot_item[ 'bloginfo' ] = 'description';
			}
			$before = empty( $slot_item[ 'before_text' ] ) ? '' : esc_html( $slot_item[ 'before_text' ] );
			$after = empty( $slot_item[ 'after_text' ] ) ? '' : esc_html( $slot_item[ 'after_text' ] );
			$return = get_bloginfo( $slot_item[ 'bloginfo' ] );
			$return = $before . $return . $after;
			$return = pluginbuddy_loopbuddy_render_slotitems::get_output( $return, $rs, $slot_item, true );
			return $return;
		} //end bloginfo
		
		function current_datetime( $rs, $slot_item ) {
			$before = empty( $slot_item[ 'before_text' ] ) ? '' : esc_html( $slot_item[ 'before_text' ] );
			$after = empty( $slot_item[ 'after_text' ] ) ? '' : esc_html( $slot_item[ 'after_text' ] );
			$date_format = isset( $slot_item[ 'date_format' ] ) ? trim( $slot_item[ 'date_format' ] ) : '';
			if ( empty( $date_format ) ) $date_format = false;
						
			if ( !$date_format ) $date_format = get_option( 'date_format' ) . ' ' . get_option( 'time_format' );
			
			$return = $before . date( $date_format, current_time( 'timestamp' ) ) . $after;
			$return = pluginbuddy_loopbuddy_render_slotitems::get_output( $return, $rs, $slot_item, true );
			return $return;			
		} //end current_datetime
		function start_wrap( $rs, $slot_item ) {
			$wrap = $slot_item[ 'wrap' ];
			$custom_class = $slot_item[ 'custom_class' ];
			return sprintf( '<%1$s class="%2$s">', $wrap, $custom_class ); 
		} //end start_wrap
		function end_wrap( $rs, $slot_item ) {
			$wrap = $slot_item[ 'wrap' ];
			return sprintf( '</%s>', $wrap ); 
		} //end end_wrap
		/*
		
		
		
	'wp_get_attachment_image'	=>		array( 'Attachment Image' ),
	'the_attachment_link'		=>		array( 'Attachment Link' ),
	'the_author'				=>		array( 'Author' ),
	'the_author_meta'			=>		array( 'Author Info.' ),
	'custom_field'				=>		array( 'Custom Field',
	'the_meta'					=>		array( 'Meta List' ),
	'the_search_query'			=>		array( 'Search Query' ),
	'the_taxonomies'			=>		array( 'Taxonomies',
	'the_time'					=>		array( 'Time' ),

	'bloginfo'					=>		array( 'Blog Info.' ),
	'shortcode'					=>		array( 'Shortcode' ),
	'text'						=>		array( 'Text' ),
	'get_option_siteurl'		=>		array( 'Site URL' ),
	'get_option_blogname'		=>		array( 'Blog Name' ),
	'get_option_blogdescription'=>		array( 'Blog Description' ),
	'current_datetime'			=>		array( 'Current Date/Time' ),
	
	
	
	*/
	
	
	
	}
}
?>