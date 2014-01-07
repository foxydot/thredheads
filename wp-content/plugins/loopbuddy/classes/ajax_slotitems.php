<?php
if ( !class_exists( "pluginbuddy_loopbuddy_slotitems" ) ) {
	class pluginbuddy_loopbuddy_slotitems {
		
	var $_tags = array();
		
		
		function pluginbuddy_loopbuddy_slotitems(&$parent) {
			$this->_parent = &$parent;
			$this->_var = &$parent->_var;
			$this->_name = &$parent->_name;
			$this->_options = &$parent->_options;
			$this->_pluginPath = &$parent->_pluginPath;
			$this->_pluginURL = &$parent->_pluginURL;
			$this->_selfLink = &$parent->_selfLink;
			
			$this->_tags = array(
				'Post Tags' => array(
					'wp_get_attachment_image' => array( __( 'Attachment Image', 'it-l10n-loopbuddy' ),
						array(
							'image_size' => 'thumbnail',
							'max_width' => '',
							'max_height' => '',
							'wrap' => 'p',
							'before_text' => '', 
							'after_text' => '',
							'custom_url' => '',
							'custom_class' => 'attachment',
						),
					),
					'edit_post_link' => array( __( 'Edit Post Link', 'it-l10n-loopbuddy' ),
						array(
							'wrap' => 'none',
							'link_text' => 'Edit Post',
							'before_text' => '', 
							'after_text' => '',
							'custom_url' => '',
							'custom_class' => '',
						),
					),
					'the_attachment_link' => array( __( 'Attachment Link', 'it-l10n-loopbuddy' ),
						array(
							'attachment_link_display' => 'image',
							'image_size' => 'thumbnail',
							'max_width' => '',
							'max_height' => '',
							'wrap' => 'p',
							'link_text' => '',
							'custom_class' => 'attachment',
						),
					),
					'the_author' => array( __( 'Author', 'it-l10n-loopbuddy' ),
						array(
							'author_link_destination' => 'the_author',
							'custom_url' => '',
							'after_text' => ' ',
							'before_text' => 'By: ',
							'wrap' => 'none',
							'custom_class' => '',
						),
					),
					'the_author_meta' => array( __( 'Author Info.', 'it-l10n-loopbuddy' ),
						array(
							'user_profile' => 'display_name',
							'wrap' => 'none',
							'before_text' => '',
							'meta_key' => '',
							'after_text' => '',
							'custom_url' => '',
							'custom_class' => '',
						),																		
					),
					'the_category' => array( __( 'Category', 'it-l10n-loopbuddy' ),
						array(
							'separator' => ', ',
							'before_text' => 'Posted in: ',
							'after_text' => ' ',
							'custom_class' => '',
							'wrap' => 'none'
						),
					),
					'get_search_form' => array( __( 'Search Form', 'it-l10n-loopbuddy' ),
						array(
							'custom_class' => '',
							'wrap' => 'none',
							'custom_search' => 'theme_search',
							'custom_search_button' => __( 'Submit', 'it-l10n-loopbuddy' ),
							'custom_search_label' => __( 'Search for:', 'it-l10n-loopbuddy' ),
							'custom_search_placeholder' => __( 'Search', 'it-l10n-loopbuddy' ),
						),
					),
					'comments_number' => array( __( 'Comment #', 'it-l10n-loopbuddy' ),
						array(
							'wrap' => 'div',
							'custom_class' => 'comments-link',
							'before_text' => '', 
							'after_text' => '',
							'comment_number_zero' => '<span class="leave-reply">' . __( 'Reply', 'it-l10n-loopbuddy' ) . '</span>',
							'comment_number_one' => __( '1', 'it-l10n-loopbuddy' ),
							'comment_number_more' => __( '%', 'it-l10n-loopbuddy' ),
							'comment_number_link' => 'on'
						)
					),
					'the_content' => array( __( 'Content', 'it-l10n-loopbuddy' ),
						array( 
							'wrap' => 'none',
							'custom_class' => '',
							'content_more' => ''
						)
					),
					'post_custom' => array( __( 'Custom Field', 'it-l10n-loopbuddy' ),
						array(
							'meta_key' => '',
							'wrap' => 'none',
							'before_text' => '',
							'after_text' => '',
							'custom_class' => '',
						),
					),
					'the_date' => array( __( 'Date/Time', 'it-l10n-loopbuddy' ),
						array(
							'wrap' => 'none',
							'post_date' => 'date_posted',
							'date_format' => '',
							'before_text' => 'Posted on: ',
							'after_text' => ' ',
							'custom_class' => ''
						),
					),
					'the_excerpt' => array( __( 'Excerpt', 'it-l10n-loopbuddy' ),
						array( 
							'wrap' => 'div',
							'custom_class' => 'entry-summary'
						)
					),
					'the_ID' => array( 'ID',
						array( 
							'wrap' => 'none',
							'before_text' => '',
							'after_text' => '',
							'custom_class' => ''
						)
					),
					'the_meta' => array( __( 'Custom Field List', 'it-l10n-loopbuddy' ),
						array(
							'wrap' => 'none',
							'before_text' => '',
							'after_text' => '',
							'custom_class' => ''
						),
					),
					'the_permalink' => array( __( 'Permalink', 'it-l10n-loopbuddy' ),
						array(
							'wrap' => 'none',
							'custom_class' => '',
							'before_text' => '',
							'after_text' => '',
							'url_display' => 'url_link',
							'link_text' => ''
						)
					),
					'the_post_thumbnail' => array( __( 'Post Thumbnail', 'it-l10n-loopbuddy' ),
						array(
							'attachment_link_display' => 'none',
							'custom_url' => '',
							'image_size' => 'thumbnail',
							'max_width' => '',
							'max_height' => '',
							'wrap' => 'none',
							'before_text' => '', 
							'after_text' => '',
							'custom_class' => '',
						),
					),
					'the_search_query' => array( __( 'Search Query', 'it-l10n-loopbuddy' ),
						array(
							'wrap' => 'none',
							'before_text' => '',
							'after_text' => '',
							'custom_class' => ''
						)
					),
					'the_shortlink' => array( __( 'Shortlink', 'it-l10n-loopbuddy' ), 
						array(
							'url_display' => '',
							'link_text' => '',
							'link_title' => '',
							'before_text' => '',
							'after_text' => '',
							'wrap' => 'none',
							'custom_class' => ''
						) 
					),
					'the_tags' => array( __( 'Tags', 'it-l10n-loopbuddy' ),
						array( // Defaults
							'separator' => ', ',
							'after_text' => '',
							'before_text' => __( 'Tags:', 'it-l10n-loopbuddy' ) . ' ',
							'wrap' => 'none',
							'custom_class' => ''
						),
					),
					'the_taxonomies' => array( __( 'Taxonomies', 'it-l10n-loopbuddy' ),
						array(
							'separator' => ', ',
							'before_text' => '',
							'after_text' => '',
							'wrap' => 'none',
							'custom_class' => '',
							'type' => 'list',
							'taxonomies' => 'all',
							'taxonomy_template' => '%s: %l.',
						),
					),
					'the_title' => array( __( 'Title', 'it-l10n-loopbuddy' ), 
						array(
							'wrap' => 'h2',
							'permalink' => 'on',
							'custom_url' => '',
							'custom_class' => 'entry-title',
							'before_text' => '',
							'after_text' => ''
						),
					),
					'post_type_name' => array( __( 'Post Type Name', 'it-l10n-loopbuddy' ),
						array(
							'wrap' => 'h2',
							'before_text' => '',
							'after_text' => '',
							'custom_class' => ''
						),
					),
					), /* End POST TAGS */
					'Special Tags' => array(
						'bloginfo' => array( __( 'Site Information', 'it-l10n-loopbuddy' ),
							array(
								'bloginfo' => 'site_name',
								'wrap' => 'none',
								'before_text' => '',
								'after_text' => '',
								'custom_class' => ''
							),
						),
						'current_datetime' => array( __( 'Current Date/Time', 'it-l10n-loopbuddy' ),
							array(
								'date_format' => '',
								'wrap' => 'none',
								'before_text' => '',
								'after_text' => '', 
								'custom_class' => ''
							),
						),
						'shortcode' => array( __( 'Shortcode', 'it-l10n-loopbuddy' ),
							array(
								'shortcode' => '',
								'wrap' => 'none',
								'custom_class' => ''
							),
						),
						'text' => array( __( 'Text', 'it-l10n-loopbuddy' ),
							array(
							'text' => '',
							'custom_class' => '',
							'wrap' => 'none'
							),
						),
						'start_wrap' => array( __( 'Start Wrapper', 'it-l10n-loopbuddy' ),
							array(
							'wrap' => '',
							'custom_class' => '',
							),
						),
						'end_wrap' => array( __( 'End Wrapper', 'it-l10n-loopbuddy' ),
							array(
							'wrap' => '',
							),
						),
			
						
					) /* END SPECIAL TAGS */
				);
			$this->_tags[ 'Custom Tags' ] = apply_filters( 'pb_loopbuddy_custom_tags', array() );
						
			$this->_items = array(
				'link_text' => array(
					'function' => array( &$this, 'get_input_html' ),
					'args' => array(
						'label' => __( 'Link Text', 'it-l10n-loopbuddy' ),
						'tip' => __( 'Optional text for link.  Leave blank to use link as the text.', 'it-l10n-loopbuddy' )
					)
				),
				'custom_url' => array(
					'function' => array( &$this, 'get_input_html' ),
					'args' => array(
						'label' => __( 'Custom URL', 'it-l10n-loopbuddy' ),
						'tip' => __( 'Enter a web location where you would like the item to point to.', 'it-l10n-loopbuddy' )
					)
				),
				'custom_class' => array(
					'function' => array( &$this, 'get_input_html' ),
					'args' => array(
						'label' => __( 'Custom Class Name', 'it-l10n-loopbuddy' ),
						'tip' => __( 'Custom CSS class name to apply to the wrapper. This allows custom CSS styling to target this specifically.', 'it-l10n-loopbuddy' )
					)
				),
				'link_title' => array(
					'function' => array( &$this, 'get_input_html' ),
					'args' => array(
						'label' => __( 'Link Title', 'it-l10n-loopbuddy' ),
						'tip' => __( 'Optional text for a link\'s title attribute.', 'it-l10n-loopbuddy' )
					)
				),
				'before_text' => array(
					'function' => array( &$this, 'get_input_html' ),
					'args' => array(
						'label' => __( 'Before Item', 'it-l10n-loopbuddy' ),
						'tip' => __( 'Text to go before the item', 'it-l10n-loopbuddy' )
					)
				),
				'after_text' => array(
					'function' => array( &$this, 'get_input_html' ),
					'args' => array(
						'label' => __( 'After Item', 'it-l10n-loopbuddy' ),
						'tip' => __( 'Text to go after the item', 'it-l10n-loopbuddy' )
					)
				),
				'meta_key' => array(
					'function' => array( &$this, 'get_input_html' ),
					'args' => array(
						'label' => __( 'Meta Key', 'it-l10n-loopbuddy' ),
						'tip' => __( 'Text name of the meta key you want the value to be displayed from', 'it-l10n-loopbuddy' )
					)
				),
				'max_width' => array(
					'function' => array( &$this, 'get_input_html' ),
					'args' => array(
						'label' => __( 'Max Width', 'it-l10n-loopbuddy' ),
						'tip' => __( 'Choose the maximum width of an item', 'it-l10n-loopbuddy' )
					)
				),
				'max_height' => array(
					'function' => array( &$this, 'get_input_html' ),
					'args' => array(
						'label' => __( 'Max Height', 'it-l10n-loopbuddy' ),
						'tip' => __( 'Choose the maximum height of an item', 'it-l10n-loopbuddy' )
					)
				),
				'shortcode' => array(
					'function' => array( &$this, 'get_textarea_html' ),
					'args' => array(
						'label' => __( 'Shortcode', 'it-l10n-loopbuddy' ),
						'tip' => __( 'Shortcode as you would type it in content.', 'it-l10n-loopbuddy' )
					)
				),
				'wrap' => array(
					'function' => array( &$this, 'get_dropdown_html' ),
					'args' => array(
						'label' => __( 'HTML Wrapper', 'it-l10n-loopbuddy' ),
						'tip' => __( 'Choose which HTML element to wrap around the item', 'it-l10n-loopbuddy' ),
						'options' => array( 
							'none', 'span', 'div', 'p', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6'
						)
					)
				),
				'author_link_destination' => array(
					'function' => array( &$this, 'get_dropdown_html' ),
					'args' => array(
						'label' => __( 'Link Destination', 'it-l10n-loopbuddy' ),
						'tip' => __( 'Bit of author information to show', 'it-l10n-loopbuddy' ),
						'options' => array( 
							'the_author_link', 'the_author_posts_link', 'none', 'custom'
						)
					)
				),
				'user_profile' => array(
					'function' => array( &$this, 'get_dropdown_html' ),
					'args' => array(
						'label' => __( 'Parameter', 'it-l10n-loopbuddy' ),
						'tip' => __( 'Bit of author information to show', 'it-l10n-loopbuddy' ),
						'options' => array( 
							'display_name', 'user_login', 'user_nicename', 'user_email', 'user_url', 'user_registered', 'user_status', 'nickname', 'first_name', 'last_name', 'jabber', 'aim', 'yim', 'description', 'ID', 'the_author_posts', 'custom' 
						)
					)
				),
				'permalink' => array(
					'function' => array( &$this, 'get_dropdown_html' ),
					'args' => array(
						'label' => __( 'Add Permalink?', 'it-l10n-loopbuddy' ),
						'tip' => __( 'Decide whether to show a permalink or use a custom URL', 'it-l10n-loopbuddy' ),
						'options' => array( 
							'on', 'off', 'custom' 
						)
					)
				),
				'attachment_link_display' => array(
					'function' => array( &$this, 'get_dropdown_html' ),
					'args' => array(
						'label' => __( 'Link Display', 'it-l10n-loopbuddy' ),
						'tip' => __( 'Decide whether to show a permalink or use a custom URL', 'it-l10n-loopbuddy' ),
						'options' => array( 
							'post_permalink', 'attachment_permalink', 'image', 'custom', 'none' 
						)
					)
				),
				'separator' => array(
					'function' => array( &$this, 'get_input_html' ),
					'args' => array(
						'label' => __( 'Item Separation Character', 'it-l10n-loopbuddy' ),
						'tip' => __( 'Text or character to display between each tag link, such as a comma (,)', 'it-l10n-loopbuddy' ),
					)
				),
				'text' => array(
					'function' => array( &$this, 'get_textarea_html' ),
					'args' => array(
						'label' => __( 'Insert Text or HTML', 'it-l10n-loopbuddy' ),
						'tip' => __( 'Plain text or HTML will be inserted for this object', 'it-l10n-loopbuddy' )
					)
				),
				'date_format' => array(
					'function' => array( &$this, 'get_input_html' ),
					'args' => array(
						'label' => __( 'Date Format', 'it-l10n-loopbuddy' ),
						'tip' => __( 'PHP style date format. Leave blank to use WordPress format', 'it-l10n-loopbuddy' )
					)
				),
				'comment_number_zero' => array(
					'function' => array( &$this, 'get_input_html' ),
					'args' => array(
						'label' => __( 'Zero Comments', 'it-l10n-loopbuddy' ),
						'tip' => __( 'Text to display when there are zero comments', 'it-l10n-loopbuddy' )
					)
				),
				'comment_number_one' => array(
					'function' => array( &$this, 'get_input_html' ),
					'args' => array(
						'label' => __( 'One Comment', 'it-l10n-loopbuddy' ),
						'tip' => __( 'Text to display when there is only one comment', 'it-l10n-loopbuddy' )
					)
				),
				'comment_number_more' => array(
					'function' => array( &$this, 'get_input_html' ),
					'args' => array(
						'label' => __( 'Multiple Comments', 'it-l10n-loopbuddy' ),
						'tip' => __( 'Text to display when there is more than one comment', 'it-l10n-loopbuddy' )
					)
				),
				'comment_number_link' => array(
					'function' => array( &$this, 'get_dropdown_html' ),
					'args' => array(
						'label' => __( 'Add Link to Response Section?', 'it-l10n-loopbuddy' ),
						'tip' => __( 'Decide whether to show a permalink or not', 'it-l10n-loopbuddy' ),
						'options' => array( 
							'on', 'off' 
						)
					)
				),
				'post_date' => array(
					'function' => array( &$this, 'get_dropdown_html' ),
					'args' => array(
						'label' => __( 'Date', 'it-l10n-loopbuddy' ),
						'tip' => __( 'Determines whether to show the posted date or the modified date', 'it-l10n-loopbuddy' ),
						'options' => array( 'date_posted', 'date_modified' )
					)
				),
				'bloginfo' => array(
					'function' => array( &$this, 'get_dropdown_html' ),
					'args' => array(
						'label' => __( 'Site Information', 'it-l10n-loopbuddy' ),
						'tip' => __( 'Select the site information you would like to display', 'it-l10n-loopbuddy' ),
						'options' => array( 'admin_email', 'atom_url', 'description', 'site_name', 'comments_atom_url', 'comments_rss2_url', 'pingback_url', 'rss_url', 'site_url', 'wpurl' )
					)
				),
				'custom_search' => array(
					'function' => array( &$this, 'get_dropdown_html' ),
					'args' => array(
						'label' => __( 'Custom Search', 'it-l10n-loopbuddy' ),
						'tip' => __( 'Use the standard theme search or a custom version?', 'it-l10n-loopbuddy' ),
						'options' => array( 'theme_search', 'custom_search' )
					)
				),
				'custom_search_button' => array(
					'function' => array( &$this, 'get_input_html' ),
					'args' => array(
						'label' => __( 'Button Text (if custom)', 'it-l10n-loopbuddy' ),
						'tip' => __( 'The text to use for the search button', 'it-l10n-loopbuddy' ),
					)
				),
				'custom_search_label' => array(
					'function' => array( &$this, 'get_input_html' ),
					'args' => array(
						'label' => __( 'Label Text (if custom)', 'it-l10n-loopbuddy' ),
						'tip' => __( 'The label to use for the search form', 'it-l10n-loopbuddy' ),
					)
				),
				'taxonomy_template' => array(
					'function' => array( &$this, 'get_input_html' ),
					'args' => array(
						'label' => __( 'Taxonomy template (for all taxonomies)', 'it-l10n-loopbuddy' ),
						'tip' => __( 'The template to use for the taxonomies', 'it-l10n-loopbuddy' ),
					)
				),
				'custom_search_placeholder' => array(
					'function' => array( &$this, 'get_input_html' ),
					'args' => array(
						'label' => __( 'Placeholder Text (if custom)', 'it-l10n-loopbuddy' ),
						'tip' => __( 'The placeholder text to use for the search form', 'it-l10n-loopbuddy' ),
					)
				),
				'content_more' => array(
					'function' => array( &$this, 'get_input_html' ),
					'args' => array(
						'label' => __( 'More Text', 'it-l10n-loopbuddy' ),
						'tip' => __( 'The link text to display for the "more" link.  Leave blank to show the full content.', 'it-l10n-loopbuddy' ),
					)
				),
				'url_display' => array(
					'function' => array( &$this, 'get_dropdown_html' ),
					'args' => array(
						'label' => __( 'URL Display', 'it-l10n-loopbuddy' ),
						'tip' => __( 'Select how the URL should be displayed (i.e., as a link or as text)', 'it-l10n-loopbuddy' ),
						'options' => array( 'url_link', 'url_text' )
					)
				),


			);
			//Add image sizes (which are dynamic )
			global $_wp_additional_image_sizes;
			$sizes = array( 'thumbnail', 'medium', 'large', 'full', 'post-thumbnail', 'custom' );
			if ( is_array( $_wp_additional_image_sizes ) ) { /* todo - this will break eventually, find a better way */
				foreach ( $_wp_additional_image_sizes as $image_size => $size ) {
					if ( !in_array( $image_size, $sizes ) ) $sizes[] = $image_size;
				}
			}
			
			$this->_items['image_size'] = array(
					'function' => array( &$this, 'get_dropdown_html' ),
					'args' => array(
						'label' => 'Image Size',
						'tip' => 'Select an image size for the image',
						'options' =>	$sizes
					)
				);		
			
			//Hook for adding in custom items
			$this->_items = apply_filters( 'pb_loopbuddy_tag_items', $this->_items );
	
			$this->_form_labels = array(
				'theme_search' => __( 'Built-in Search Form', 'it-l10n-loopbuddy' ),
				'custom_search' => __( 'Custom Search Form', 'it-l10n-loopbuddy' ),
				'url_link' => __( 'As a link', 'it-l10n-loopbuddy' ),
				'url_text' => __( 'As text', 'it-l10n-loopbuddy' ),
				'admin_email' => __( 'Admin Email Address', 'it-l10n-loopbuddy' ),
				'atom_url' => __( 'Atom URL', 'it-l10n-loopbuddy' ),
				'site_description' => __( 'Site Description', 'it-l10n-loopbuddy' ),
				'site_name' => __( 'Site Name', 'it-l10n-loopbuddy' ),
				'comments_atom_url' => __( 'Comments Atom URL', 'it-l10n-loopbuddy' ),
				'comments_rss2_url' => __( 'Comments RSS URL', 'it-l10n-loopbuddy' ),
				'pingback_url' => __( 'Pingback URL', 'it-l10n-loopbuddy' ),
				'rss_url' => __( 'RSS URL', 'it-l10n-loopbuddy' ),
				'site_url' => __( 'Site URL', 'it-l10n-loopbuddy' ),
				'wpurl' => __( 'WordPress URL', 'it-l10n-loopbuddy' ),
				'date_posted' => __( 'Date Posted', 'it-l10n-loopbuddy' ),
				'date_modified' => __( 'Date Modified', 'it-l10n-loopbuddy' ),
				'post-thumbnail' => __( 'Post Thumbnail', 'it-l10n-loopbuddy' ),
				'thumbnail' => _x( 'Thumbnail', 'image size', 'it-l10n-loopbuddy' ),
				'medium' => _x( 'Medium', 'image size',  'it-l10n-loopbuddy' ),
				'large' => _x( 'Large', 'image size', 'it-l10n-loopbuddy' ),
				'full' => _x( 'Full', 'image size', 'it-l10n-loopbuddy' ),
				'custom' => __( 'Custom', 'it-l10n-loopbuddy' ),
				'none' => __( 'None', 'it-l10n-loopbuddy' ),
				'span' => 'SPAN',
				'div' => 'DIV',
				'p' => 'P',
				'h1' => 'H1',
				'h2' => 'H2',
				'h3' => 'H3',
				'h4' => 'H4',
				'h5' => 'H5',
				'h6' => 'H6',
				'the_author_link' => __( 'Author Profile', 'it-l10n-loopbuddy' ),
				'the_author_posts_link' => __( 'Author Posts', 'it-l10n-loopbuddy' ),
				'display_name' => __( 'Display Name', 'it-l10n-loopbuddy' ),
				'user_login' => __( 'Login', 'it-l10n-loopbuddy' ),
				'user_nicename' => __( 'Nice Name', 'it-l10n-loopbuddy' ),
				'user_email' => __( 'Email', 'it-l10n-loopbuddy' ),
				'user_url' => __( 'URL', 'it-l10n-loopbuddy' ),
				'user_registered' => __( 'Registration Date', 'it-l10n-loopbuddy' ),
				'user_status' => __( 'Status', 'it-l10n-loopbuddy' ),
				'nickname' => __( 'Nickname', 'it-l10n-loopbuddy' ),
				'first_name' => __( 'First Name', 'it-l10n-loopbuddy' ),
				'last_name' => __( 'Last Name', 'it-l10n-loopbuddy' ),
				'jabber' => 'Jabber',
				'aim' => 'AIM',
				'yim' => 'Yahoo',
				'description' => __( 'Description', 'it-l10n-loopbuddy' ),
				'ID' => 'ID',
				'the_author_posts' => __( 'Number of Posts Authored', 'it-l10n-loopbuddy' ),
				'on' => __( 'Yes', 'it-l10n-loopbuddy' ),
				'off' => __( 'No', 'it-l10n-loopbuddy' ),
				'permalink' => __( 'Permalink', 'it-l10n-loopbuddy' ),
				'image' => __( 'Image', 'it-l10n-loopbuddy' ),
				'post_permalink' => __( 'Post Permalink', 'it-l10n-loopbuddy' ),
				'attachment_permalink' => __( 'Attachment Permalink', 'it-l10n-loopbuddy' )
				
			);
		} //end constructor
		
		
		function get_tag_title( $tag, $_tags = false ) {
			$_tags = $this->_tags;
			foreach ( $_tags as $tag_set ) {
				if ( !empty( $tag_set[ $tag ] ) ) {
					return $tag_set[ $tag ][0];
				}
			}
			return __( 'Unknown_Tag:', 'it-l10n-loopbuddy' ) . $tag;
		}
		
		function get_tag_settings( $tag, $_tags = false ) {
			$_tags = $this->_tags;
			foreach ( $_tags as $tag_set ) {
				if ( !empty( $tag_set[ $tag ] ) ) {
					if ( isset( $tag_set[ $tag ][1] ) ) {
						return $tag_set[ $tag ][1];
					} else {
						return array(); // No settings for this tag.
					}
				}
			}
			return __( 'Unknown_Tag:', 'it-l10n-loopbuddy' ) . $tag;
		}
		
		function nonce() {
			wp_nonce_field( $this->_parent->_var . '-nonce' );
		}
		
		function edit( $slot_item_tag ) {
				
				wp_enqueue_style( 'global' );
				wp_enqueue_style( 'wp-admin' );
				
				wp_enqueue_script( 'pluginbuddy-tooltip-js', $this->_parent->_pluginURL . '/js/tooltip.js' );
				wp_print_scripts( 'pluginbuddy-tooltip-js' );
				wp_enqueue_script( 'pluginbuddy-'.$this->_var.'-admin-js', $this->_parent->_pluginURL . '/js/admin.js' );
				wp_print_scripts( 'pluginbuddy-'.$this->_var.'-admin-js' );
				?>
				<link rel="stylesheet" href="<?php echo esc_url( $this->_pluginURL . '/css/admin.css' ); ?>" type="text/css" media="all" />
				
				<form class="pb_loopbuddy_slotitemsave" method="post" action="<?php echo esc_url( add_query_arg( array( 'action' => 'pb_loopbuddy_slotitemsave', 'edit' => esc_js( $_GET[ 'slot' ] ), 'id' => esc_js( $_GET[ 'id' ] ), 'slot' => esc_js( $_GET[ 'slot' ] ) ), admin_url( 'admin-ajax.php' ) ) ); ?>">
				<input type="hidden" name="slot_id" value="<?php echo esc_attr( $_GET['id'] ); ?>" />
				<input type="hidden" name="slot_title" value="<?php echo esc_attr( $_GET['slot'] ); ?>" />
				<input type="hidden" name="group" value="<?php echo esc_attr( $_GET['group'] ); ?>" />
				<?php
				$settings = $this->_options['layouts'][$_GET['group']]['items'][$_GET['slot']][$_GET['id']];
				
				$default_settings = $this->get_tag_settings( $slot_item_tag, $this->_tags );
				
				$settings = wp_parse_args( (array)$settings, $default_settings );
				
				$method_exists = method_exists( $this, 'edit_' . $slot_item_tag );
				if ( !$method_exists ) do_action( 'pb_loopbuddy_tag_header-' . $slot_item_tag, $settings ); 
				?>
				<table class="form-table">
				<?php
				
				
				/* Editable settings in addition to class name, url, orientation, etc. */
				//keep track of this variable so that we can run the do actions in the appropriate spot in the table structure
				if ( $method_exists ) {
					call_user_func( array(&$this, 'edit_' . $slot_item_tag  ), $settings );
				} else {
					//For custom tag rendering 
					$support_tag_items = apply_filters( 'pb_loopbuddy_tag_supports-' . $slot_item_tag, array( 'wrap', 'custom_class' ) );
					$this->get_html( $settings, $support_tag_items );
				}
				?>
				</table>
				<?php
				if ( !$method_exists ) {
					do_action( 'pb_loopbuddy_tag_footer-' . $slot_item_tag, $settings ); 
				}
				
				$this->nonce();
				?>
				
				<br />
				<p class="submit">
				<input type="submit" name="submit" value="<?php esc_attr_e( 'Save Settings', 'it-l10n-loopbuddy' ); ?>" class="button-primary" /> <span id="pb_slot_saving" style="display: none;"><img src="<?php echo esc_url( $this->_pluginURL . '/images/loading.gif' ); ?> alt="<?php esc_attr_e( 'Saving...', 'it-l10n-loopbuddy' ); ?>" title="<?php esc_attr_e( 'Saving...', 'it-l10n-loopbuddy' ); ?>" style="cursor: default; vertical-align: -3px;" width="16" height="16" /> <?php esc_html_e( 'Saving...', 'it-l10n-loopbuddy' ); ?></span></p>

				</form>
				<?php
				die();
		}
		function get_custom_search( $settings ) {
			$this->get_note_html( __( 'Special search allows you to search a custom query', 'it-l10n-loopbuddy' ) );
			$this->get_html( $settings, array( 'wrap', 'custom_class' ) );
		}
		function edit_get_search_form( $settings ) {
			$this->get_html( $settings, array( 'wrap', 'custom_search', 'custom_search_button', 'custom_search_label', 'custom_search_placeholder', 'custom_class' ) );
		} //end get_search_form
		function edit_start_wrap( $settings ) {
			$this->get_note_html( __( "Choose a starting wrapper HTML tag.", 'it-l10n-loopbuddy' ) );
			$this->get_html( $settings, array( 'wrap', 'custom_class' ) );
		}
		function edit_end_wrap( $settings ) {
			$this->get_note_html( __( "Choose an ending wrapper HTML tag.", 'it-l10n-loopbuddy' ) );
			$this->get_html( $settings, array( 'wrap' ) );
		}
		function edit_edit_post_link( $settings ) {
			$this->get_html( $settings, array( 'wrap', 'link_text', 'before_text', 'after_text', 'custom_class' ) );
		} //end edit_post_link
		function edit_comments_number( $settings ) {
			$this->get_html( $settings, array( 'wrap', 'comment_number_link', 'comment_number_zero', 'comment_number_one', 'comment_number_more', 'before_text', 'after_text', 'custom_class' ) );
		} //end edit_comments_number
				
		function edit_the_meta( $settings ) {
			$this->get_html( $settings, array( 'wrap', 'custom_class' ) );
		} //end edit_the_meta
		function edit_the_permalink( $settings ) {
			$this->get_html( $settings, array( 'wrap', 'url_display', 'link_text', 'before_text', 'after_text', 'custom_class' ) );
		} //end edit_the_permalink
		
		function edit_the_ID( $settings ) {
			$this->get_html( $settings, array( 'wrap', 'before_text', 'after_text', 'custom_class' ) );
		} //end edit_the_ID
		function edit_the_content( $settings ) {
			$this->get_html( $settings, array( 'wrap', 'content_more', 'custom_class' ) );

		} //edit the content
		
		function edit_the_excerpt( $settings ) {
			$this->get_html( $settings, array( 'wrap','custom_class' ) );
		} //end edit_the_excerpt
		
		function edit_the_title( $settings ) {
			$this->get_html( $settings, array( 'wrap', 'permalink', 'custom_url', 'after_text', 'before_text', 'custom_class' ) );
		} //end edit_the_title
		
		function edit_text( $settings ) {
			$this->get_html( $settings, array( 'wrap', 'text', 'custom_class' ) );
		} //end edit_text
		
		function edit_the_tags( $settings ) {
			$this->get_html( $settings, array( 'wrap', 'separator', 'before_text', 'after_text', 'custom_class' ) );
		} //end edit_the_tags
		
		function edit_the_post_thumbnail( $settings ) {
			$this->get_html( $settings, array(
				'wrap', 'attachment_link_display', 'custom_url', 'image_size', 'max_width', 'max_height', 'before_text', 'after_text',  'custom_class'
			) );
		} //end edit_the_post_thumbnail
		
		function edit_the_author( $settings ) {
			$this->get_html( $settings, array( 'wrap', 'author_link_destination', 'custom_url', 'before_text', 'after_text',  'custom_class' ) );
		} //end edit_the_author		
		
		function edit_the_author_meta( $settings ) {
			$this->get_html( $settings, array(
				'wrap', 'user_profile', 'meta_key', 'custom_url', 'before_text', 'after_text', 'custom_class'
			) );		
			
		} //end edit_the_author_meta
		
		function edit_the_date( $settings ) {
			$this->get_note_html( sprintf( __( "Visit %s for help formatting date/time.", 'it-l10n-loopbuddy' ), "<a href='" . "http://strftime.net/" . "'>STRFTIME</a>" ) );
			$this->get_html( $settings, array(
				'wrap', 'post_date', 'date_format', 'before_text', 'after_text', 'custom_class'
			) );
		}
		
		function edit_the_taxonomies( $settings ) {
			
			//Display list of current public taxonomies
			$taxonomies = get_taxonomies( array( 'public' => true, 'show_ui' => true ), 'objects' );
			if ( !$taxonomies ) {
				$this->get_note_html( __( "There aren't any taxonomies to display", 'it-l10n-loopbuddy' ) );
				return;
			}
			$this->get_html( $settings, array(
				'wrap', 'separator'
			) );
			?>
			<tr>
				<td><label for="#taxonomies"><?php esc_html_e( 'Select a taxonomy', 'it-l10n-loopbuddy' ); ?><?php echo $this->_parent->tip( __( 'Taxonomies are like labels, categories, and tags', 'it-l10n-loopbuddy' ), '', false ); ?></label></td>
				<td>
				<select name='#taxonomies'>
					<option value='all' <?php selected( $settings[ 'taxonomies' ], 'all' ); ?>><?php esc_html_e( 'All', 'it-l10n-loopbuddy' ); ?></option>
				<?php
					foreach ( $taxonomies as $slug => $taxonomy ) {
						$name = isset( $taxonomy->labels->name ) ? $taxonomy->labels->name : false;
						if ( !$name ) continue;
						?>
						<option value="<?php echo esc_attr( $slug ); ?>" <?php selected( $settings[ 'taxonomies' ], $slug ); ?>><?php echo esc_html( $name ); ?></option>
						<?php
					} //end foreach $options
					
				?>
				</select>
			</td>
		</tr>

			<?php
			foreach ( $taxonomies as $taxonomy ) {
			
			}
			$this->get_html( $settings, array(
				'taxonomy_template', 'before_text', 'after_text', 'custom_class'
			) );
		}
		
		function edit_the_shortlink( $settings ) {
			$this->get_note_html( __( 'The shortlink will only display properly if you have defined a custom permalink structure', 'it-l10n-loopbuddy' ) );
			$this->get_html( $settings, array(
				'wrap', 'url_display', 'link_text', 'link_title', 'before_text', 'after_text', 'custom_class'
			) );
		} //end edit_shortlink
		
		function edit_shortcode( $settings ) {
			$this->get_html( $settings, array(
				'wrap', 'shortcode', 'custom_class'
			) );
		} //end edit_shortcode
		
		function edit_wp_get_attachment_image( $settings ) {
			$this->get_html( $settings, array(
				'wrap', 'image_size', 'max_width', 'max_height', 'before_text', 'after_text', 'custom_class'
			) );
		} //end edit_wp_get_attachment_image
		
		function edit_the_attachment_link( $settings ) {
			$this->get_html( $settings, array(
				'wrap', 'attachment_link_display','link_text','image_size', 'max_width', 'max_height', 'custom_class'
			) );
		} //end edit_the_attachment_link
		
		function edit_the_category( $settings ) {
			$this->get_html( $settings, array(
				'wrap', 'separator', 'before_text', 'after_text', 'custom_class'
			) );		}
		
		function edit_post_custom( $settings ) {
			$this->get_html( $settings, array(
				'wrap', 'meta_key', 'before_text', 'after_text', 'custom_class'
			) );

		} //end edit_post_custom
		
		function edit_bloginfo( $settings ) {
			$this->get_html( $settings, array(
				'wrap', 'bloginfo', 'before_text', 'after_text', 'custom_class'
			) );
		} //end edit_bloginfo
		
		function edit_current_datetime( $settings ) {
			$this->get_note_html( sprintf( __( "Visit %s for help formatting date/time.", 'it-l10n-loopbuddy' ), "<a href='" . "http://strftime.net/" . "'>STRFTIME</a>" ) );
			$this->get_html( $settings, array(
				'wrap', 'date_format', 'before_text', 'after_text', 'custom_class'
			) );
		} //end edit_current_datetime
		function edit_the_search_query( $settings ) {
			$this->get_note_html( __( 'If you are displaying search results, this will output what the user has searched for.', 'it-l10n-loopbuddy' ) );
			$this->get_html( $settings, array(
				'wrap', 'before_text', 'after_text', 'custom_class'
			) );
		} //end the_search_query
		
		function edit_post_type_name( $settings ) {
			$this->get_html( $settings, array(
				'wrap', 'before_text', 'after_text', 'custom_class'
			) );
		} //end edit_post_type_name
		
		function get_html( $settings = array(), $keys = array() ) {
			if ( !is_array( $settings ) ) return new WP_Error( 'html_settings', __( 'Could not retrieve HTML because $settings isn\'t an array', 'it-l10n-loopbuddy' ) );
			if ( !is_array( $keys ) ) return new WP_Error( 'html_keys', __( 'Could not retrieve HTML because $keys isn\'t an array', 'it-l10n-loopbuddy' ) );
			foreach ( $keys as $key ) {
				if ( array_key_exists( $key, $this->_items ) ) {
					$item = $this->_items[ $key ];
					$args = isset( $item[ 'args' ] ) ? $item[ 'args' ] : array();
					$args[ 'key' ] = $key;
					$args[ 'settings' ] = $settings;
					
					//Call the function and pass the args
					$callback_exists = false;
					if ( is_array( $item[ 'function' ] ) ) {
						if ( method_exists( $item[ 'function' ][ 0 ], $item[ 'function' ][ 1 ] ) ) {
							$callback_exists = true;
						}
					} elseif ( is_string( $item[ 'function' ] ) ) {
						if ( function_exists( $item[ 'function' ] ) ) {
							$callback_exists = true;
						}
					}
					if ( $callback_exists ) {
						call_user_func( $item[ 'function' ], $args );
					}
				} //end array_key_exists
			} //end foreach $keys				
		} //end get_html
		
		function get_note_html( $note ) {
			?>
			<tr><td colspan='2'><p><?php echo $note; ?></p></td></tr>
			<?php
		} //end get_note_html
		function get_input_html( $args = array() ) {
			$defaults = array(
				'key' => '',
				'label' => '',
				'tip' => false,
				'settings' => array()
			);
			extract( wp_parse_args( $args, $defaults ) );
			if ( !isset( $settings[ $key ] ) ) return new WP_Error( 'lb_key', sprintf( __( "The %s key doesn't exist in the settings", 'it-l10n-loopbuddy' ), $key ) );
			?>
			<tr>
				<td><label for="<?php echo esc_attr( $key ); ?>"><?php echo esc_html( $label ); ?><?php echo $tip ? $this->_parent->tip( esc_js( $tip ), '', false ) : ''; ?></label></td>
				<td><input type="text" name="#<?php echo esc_attr( $key ); ?>" id="<?php echo esc_attr( $key ); ?>" size="45" maxlength="45" value='<?php echo stripslashes( esc_attr( $settings[ $key ] ) ); ?>' /></td>
			</tr>
			<?php
		} //end get_input_html
		
		function get_textarea_html( $args = array() ) {
			$defaults = array(
				'key' => '',
				'label' => '',
				'tip' => false,
				'settings' => array()
			);
			extract( wp_parse_args( $args, $defaults ) );
			if ( !isset( $settings[ $key ] ) ) return new WP_Error( 'lb_key', sprintf( __( "The %s key doesn't exist in the settings", 'it-l10n-loopbuddy' ), $key ) );
			?>
			<tr>
				<td><label for="<?php echo esc_attr( $key ); ?>"><?php echo esc_html( $label ); ?><?php echo $tip ? $this->_parent->tip( esc_js( $tip ), '', false ) : ''; ?></label></td>
				<td><textarea name="#<?php echo esc_attr( $key ); ?>" id="<?php echo esc_attr( $key ); ?>"><?php echo stripslashes( esc_html( $settings[ $key ] ) ); ?></textarea></td>
			</tr>
			<?php
		} //end get_input_html
		
		function get_dropdown_html( $args = array() ) {
			$defaults = array(
				'key' => '',
				'label' => '',
				'tip' => false,
				'settings' => array(),
				'options' => array()
			);
			extract( wp_parse_args( $args, $defaults ) );
			if ( !isset( $settings[ $key ] ) ) return new WP_Error( 'lb_key', sprintf( __( "The %s key doesn't exist in the settings", 'it-l10n-loopbuddy' ), $key ) );
			
			?>
			<tr>
				<td><label for="<?php echo esc_attr( $key ); ?>"><?php echo esc_html( $label ); ?><?php echo $tip ? $this->_parent->tip( esc_js( $tip ), '', false ) : ''; ?></label></td>
				<td>
				<select name="#<?php echo esc_attr( $key ); ?>">
				<?php
				
					foreach ( $options as $option ) {
						?>
						<option value="<?php echo esc_attr( $option ); ?>" <?php selected( $settings[ $key ], $option ); ?>><?php echo array_key_exists( $option, $this->_form_labels ) ? $this->_form_labels[ $option ] : esc_html( $option ); ?></option>
						<?php
					} //end foreach $options
				?>
				</select>
			</td>
		</tr>
			<?php
		} //end get_dropdown_html
	} // End class
	
		

	
	//$pluginbuddy_loopbuddy_slotitems = new pluginbuddy_loopbuddy_slotitems($this);
}
//Global function for displaying tips
function loopbuddy_tip( $message, $title = '', $echo_tip = true ) {
	global $pluginbuddy_loopbuddy;
	$pluginbuddy_loopbuddy->tip( $message, $title, $echo_tip );
}