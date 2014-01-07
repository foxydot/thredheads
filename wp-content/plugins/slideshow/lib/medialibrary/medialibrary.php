<?php
/*
 * PluginBuddy.com & iThemes.com
 * Author: Dustin Bolton < http://dustinbolton.com >
 * Created: 9-20-2010
 * Updated: 2-18-2011
 * Version: *See line 16 of this file*
 * 
 * Uses built-in WordPress Media Library system for selecting images.
 * Uses hook to override built-in uploader since it is not as friendly to customizing.
 *
 * Public functions:
 *
 *	get_link()
 *	get_anchor()
 *
 * Files selected for insertion are returning to parent calling window via the javascript function pb_medialibrary().
 * Data is either a serialized PHP array of JSON encoded version of the same data based on settings.
 * Example:
 *		<script type="text/javascript">
 *			function pb_medialibrary( $response ) {
 *				alert( $response );
 *			}
 *		</script>
 * Example link to open media library in thickbox (use html or get_anchor function):
 *		  <a href='media-upload.php?post_id=pb_medialibrary&#038;type=image&#038;tab=library&#038;TB_iframe=1&#038;width=640&#038;height=821' id='add_image' class='thickbox' title='Add an Image'>Add Image(s)</a>
 *		OR use get_anchor() function:
 *		  <?php echo $this->_parent->_medialibrary->get_anchor() . 'Click me to add an image!</a>'; ?>
 */

if (!class_exists("PluginBuddyMediaLibrary")) {
	class PluginBuddyMediaLibrary {
		var $_version = '0.0.7';
		
		var $_options = array(
			'js_return_type'				=>		'serialize',						// Format to return data to parent window when insert image/file is selected. Valid options: serialize, json
			'select_button_text'			=>		'Select This Image',				// Text for the button to be clicked to select image.
			'tabs'							=>		array(
														'pb_uploader'	=>		'PluginBuddy Uploader',
														'type'			=>		'From Computer',
														'type_url'		=>		'From URL',
														'gallery'		=>		'Gallery',
														'library'		=>		'Media Library'
													),
			'image_title_required'			=>		false,								// Is image title required.
			'show_input-image_alt_text'		=>		true,
			'show_input-image_align'		=>		true,
			'show_input-image_size'			=>		true,
			'show_input-title'				=>		true,
			'show_input-caption'			=>		true,
			'show_input-description'		=>		true,
			'show_input-url'				=>		true,
			'show_input-image_url'			=>		true,
			'custom_help-image_alt_text'	=>		false,								// Set a custom help option to anything other than false to override default help text.
			'custom_help-image_align'		=>		false,
			'custom_help-image_size'		=>		false,
			'custom_help-title'				=>		false,
			'custom_help-caption'			=>		false,
			'custom_help-description'		=>		false,
			'custom_help-url'				=>		false,
			'custom_help-image_url'			=>		false,
			'custom_label-image_alt_text'	=>		'',
			'custom_label-description'		=>		'',
			'use_textarea-caption'			=>		false,
			'use_textarea-description'		=>		true,
			'editor_tab_text'				=>		'Edit Image Settings',
		);
		
		
		/**
		 *	PluginBuddyMediaLibrary()
		 *
		 *	Default constructor.  Passes reference to calling class and optional overriding options.
		 *
		 *	&$parent	object		Parent class. Usually $this.
		 *	$options	array		Array of options to be merged over $_options.
		 */
		function PluginBuddyMediaLibrary( &$parent, $options = '' ) {
			if ( !empty( $options ) ) {
				$this->_options = array_merge( $this->_options, (array)$options );
			}
			$this->_parent = &$parent;
			
			if ( ( basename( $_SERVER['PHP_SELF'] ) == 'media-upload.php' ) || ( basename( $_SERVER['PHP_SELF'] ) == 'async-upload.php' ) || ( basename( $_SERVER['PHP_SELF'] ) == 'admin-ajax.php' ) ) {
				if ( ( isset( $_GET['post_id'] ) && ( $_GET['post_id'] == 'pb_medialibrary' ) ) || ( ( isset( $_GET['post_id'] ) && ( $_GET['post_id'] == '0' ) ) ) ) {
					if ( !empty( $_GET['attachment_id'] ) ) { // Only when editing an image.
						add_filter( 'media_upload_library', array( &$this, 'filter_media_upload_library' ) );
						add_filter( 'media_upload_tabs', array( &$this, 'filter_media_upload_tabs_notabs' ) ); // Disable all tabs in editor!
					} else { // These dont need filtered in editor mode.
						add_filter( 'media_upload_tabs', array( &$this, 'filter_media_upload_tabs' ) );
						
					}
					
					add_filter( 'attachment_fields_to_edit', array( &$this, 'filter_attachment_fields_to_edit' ), 5, 2 );
					add_action( 'media_upload_pb_uploader', array( &$this, 'action_media_upload_pb_uploader' ) );
					add_filter( 'media_send_to_editor', array( &$this, 'filter_media_send_to_editor' ), 5, 3 );
					add_action( 'wp_ajax_pb_medialibrary_edit', array(&$this, 'ajax_edit_attachment' ) );
				}
				if ( isset( $_GET['post_id'] ) && ( $_GET['post_id'] == 'pb_medialibrary' ) ) {
					add_filter( 'gettext', array( &$this, 'filter_gettext' ), 1 );
				}
			}
		}
		
		// For use when editing
		function filter_media_upload_library() {
			if ( !empty( $_POST['save'] ) ) {
				$return = $_POST['attachments'][ $_GET['attachment_id'] ];
				$return['attachment_id'] = intval( $_GET['attachment_id'] );
				
				/*
				echo '<pre>';
				print_r( $return );
				echo '</pre>';
				*/
				echo 'PluginBuddy sending data to parent window... Please wait...';
				
				if ( $this->_options['js_return_type'] == 'serialize' ) {
					$return = serialize( $return );
				} elseif ( $this->_options['js_return_type'] == 'serialize' ) {
					$return = json_encode( $return );
				}
				
				echo '<script type="text/javascript">';
				echo '	var win = window.dialogArguments || opener || parent || top;';
				echo '	win.pb_medialibrary_edit(\'' . addslashes( $return ) . '\');';
				echo '	win.tb_remove();';
				echo '</script>';
			}
		
		ob_start();
		media_upload_library();
		$buffer = ob_get_contents();
		ob_end_clean();
		
		$buffer = str_replace( 'startclosed', 'form-table', $buffer );
		$buffer = str_replace( '<a class=\'toggle describe-toggle-on\' href=\'#\'>Show</a>', '', $buffer );
		
		$buffer = str_replace( 'post_id=0" class="media-upload-form validate" id="library-form">', 'post_id=pb_medialibrary&attachment_id=' . htmlentities( $_GET['attachment_id'] ) . '" class="media-upload-form validate" id="library-form">', $buffer );
		
		$buffer = str_replace( '<input type=\'submit\' class=\'button\' name=\'', '<input type=\'submit\' class=\'button\' style="display: none;" name=\'', $buffer );
		
		echo $buffer;
		
		die();
			echo '<pre>';
			//print_r( debug_backtrace() );
			echo '</pre>';
		}
		
		/**
		 *	get_anchor()
		 *
		 *	Returns full opening link anchor ( <a href= ......> ) without closing tag ( </a> ) for opening this media library in a thickbox.
		 *
		 *	$title		string		Optional title to be used at top of the thickbox when open. Default: Add Media
		 *	$tab		string		Optional default media library tab to display when the thickbox is opened. Default: library
		 *	@return		string		Anchor tag with link, class, and title configured.
		 */
		function get_anchor( $title = 'Add Media', $tab = 'library' ) {
			return '<a href="' . $this->get_link() . '" class="thickbox" title="' . $title . '">';
		}
		
		
		/**
		 *	get_link()
		 *
		 *	Returns actual link URL to the media library.
		 *
		 *	$tab		string		Optional default media library tab to display when the thickbox is opened. Default: library
		 *	@return		string		Link URL.
		 */
		function get_link( $tab = 'library' ) {
			return 'media-upload.php?post_id=pb_medialibrary&#038;type=image&#038;tab=' . $tab . '&#038;TB_iframe=1&#038;width=640&#038;height=821';
		}
		
		function get_edit_link( $attachment_id, $tab = 'library' ) {
			//return admin_url('admin-ajax.php') . '?action=pb_medialibrary_edit&post_id=pb_medialibrary&attachment_id=' . $attachment_id . '&#038;TB_iframe=1&#038;width=640&#038;height=821';
			return 'media-upload.php?post_id=pb_medialibrary&#038;type=image&#038;tab=' . $tab . '&#038;attachment_id=' . $attachment_id . '&#038;TB_iframe=1&#038;width=640&#038;height=821';
		}
		
		
		/**
		 *	filter_media_send_to_editor()
		 *
		 *	Intercepts sending data to editor of parent calling page so format of the data can be changed for easier processing.
		 *	Data send to page is either in serialized array form or JSON depending on settings.
		 *
		 *	$html		string		HTML image and link. Not currently used.
		 *	$send_id	int			ID number of the uploaded file. This is passed to the 
		 *	$attachment	array		Data about the uploaded file and the user selected settings for it.
		 *	@return		string		Altered text of the button.
		 */
		function filter_media_send_to_editor( $html, $send_id, $attachment ) {
			// Make compatible with Image Widget plugin:
			if ( reset( $_POST['send'] ) == 'Insert Into Widget' ) {
				return;
			}
			// Make compatible with other plugins using the default button text:
			if ( reset( $_POST['send'] ) == 'Insert into Post' ) {
				return;
			}
			
			$attachment['attachment_id'] = $send_id;
			
			if ( $this->_options['js_return_type'] == 'serialize' ) {
				$return = serialize( $attachment );
			} elseif ( $this->_options['js_return_type'] == 'serialize' ) {
				$return = json_encode( $attachment );
			}
			
			echo '<script type="text/javascript">';
			echo '	/* <![CDATA[ */';
			echo '	var win = window.dialogArguments || opener || parent || top;';
			echo '	win.pb_medialibrary(\'' . addslashes( $return ) . '\');';
			echo '	win.tb_remove();';
			echo '	/* ]]> */';
			echo '</script>';
			
			die();
		}
		
		
		/**
		 *	text_filter()
		 *
		 *	Changes the text of the 'Insert into Post' button.
		 *
		 *	$text		string		Original text of the button.
		 *	@return		string		Altered text of the button.
		 */
		function filter_gettext( $text ) {
			if ( $text == 'Insert into Post' ) {
				if ( !empty( $_GET['attachment_id'] ) ) { // In editor mode set to DISABLED for easier removal through output buffering later.
					return 'DISABLED';
				} else {
					$text = $this->_options['select_button_text'];
				}
			}
			return $text;
		}
		
		
		/**
		 *	mediatabs_filter()
		 *
		 *	Changes the tabs displayed at the top of the window.
		 *
		 *	$tabs		array		Original associated array of tabs at the top of the window.
		 *	@return		array		Altered associated array of tabs at the top of the window.
		 */
		function filter_media_upload_tabs( $tabs ) {
			return $this->_options['tabs'];
		}
		
		
		/**
		 *	filter_media_upload_tabs_notabs()
		 *
		 *	Strip out all tabs. Used in editor thickbox.
		 *
		 *	$tabs		array		Original associated array of tabs at the top of the window.
		 *	@return		array		Altered associated array of tabs at the top of the window.
		 */
		function filter_media_upload_tabs_notabs( $tabs ) {
			return array( 'library' => $this->_options['editor_tab_text'] );
		}
		
		
		/**
		 *	mediafields_filter()
		 *
		 *	Changes the tabs displayed at the top of the window.
		 *
		 *	$form_fields	array		Original associated array of form fields to display.
		 *	$post			int			ID number of the post associated with attachment (if any).
		 *	@return			array		Altered array of form fields.
		 */
		function filter_attachment_fields_to_edit( $form_fields, $post ) {
			remove_filter('attachment_fields_to_edit', 'image_attachment_fields_to_edit', 10, 2); // Removes bubble down to default WP items.
			
			if ( is_int($post) )
				$post = &get_post($post);
			if ( is_array($post) )
				$post = (object) $post;
			
			$image_url = wp_get_attachment_url($post->ID);
			$edit_post = sanitize_post($post, 'edit');
			
			$form_fields = array();
			if ( $this->_options['show_input-title'] === true ) {
				$form_fields['post_title'] = array(
												'label'      => __('Title'),
												'value'      => $edit_post->post_title
											);
				if ( $this->_options['custom_help-title'] !== true ) {
					$form_fields['post_title']['helps'] = $this->_options['custom_help-title'];
				}
			}
			if ( $this->_options['show_input-caption'] === true ) {
				$form_fields['post_excerpt'] = array(
												'label'      => __('Caption'),
												'value'      => $edit_post->post_excerpt,
											);
				if ( $this->_options['custom_help-caption'] !== true ) {
					$form_fields['post_excerpt']['helps'] = $this->_options['custom_help-caption'];
				}
				if ( $this->_options['use_textarea-caption'] === true ) {
					$form_fields['post_excerpt']['input'] = 'textarea';
				}
			}
			if ( $this->_options['show_input-description'] === true ) {
				$form_fields['post_content'] = array(
													'value'      => $edit_post->post_content,
												);
				if ( !empty( $this->_options['custom_label-description'] ) ) {
					$form_fields['post_content']['label'] = $this->_options['custom_label-description'];
				} else {
					$form_fields['post_content']['label'] = __('Description');
				}
				if ( $this->_options['custom_help-description'] !== true ) {
					$form_fields['post_content']['helps'] = $this->_options['custom_help-description'];
				}
				if ( $this->_options['use_textarea-description'] === true ) {
					$form_fields['post_content']['input'] = 'textarea';
				}
			}
			if ( $this->_options['show_input-url'] === true ) {
				$form_fields['url'] = array(
										'label'      => __('Link URL'),
										'input'      => 'html',
										'html'       => image_link_input_fields($post, get_option('image_default_link_type')),
										'helps'      => __('Enter a link URL or click above for presets.')
									);
				if ( $this->_options['custom_help-url'] !== true ) {
					$form_fields['url']['helps'] = $this->_options['custom_help-url'];
				}
			}
			if ( $this->_options['show_input-image_url'] === true ) {
				$form_fields['image_url'] = array(
												'label'      => __('File URL'),
												'input'      => 'html',
												'html'       => "<input type='text' class='text urlfield' readonly='readonly' name='attachments[$post->ID][url]' value='" . esc_attr($image_url) . "' /><br />",
												'value'      => wp_get_attachment_url($post->ID),
												'helps'      => __('Location of the uploaded file.')
											);
				if ( $this->_options['custom_help-image_url'] !== true ) {
					$form_fields['image_url']['helps'] = $this->_options['custom_help-image_url'];
				}
			}
			
			if ( substr($post->post_mime_type, 0, 5) == 'image' ) {
				$alt = get_post_meta($post->ID, '_wp_attachment_image_alt', true);
				if ( empty($alt) ) {
					$alt = '';
				}
				
				$form_fields['post_title']['required'] = $this->_options['image_title_required'];
				
				if ( $this->_options['show_input-image_alt_text'] === true ) {
					$form_fields['image_alt'] = array(
						'value' => $alt,
						'helps' => __('Alt text for the image, e.g. &#8220;The Mona Lisa&#8221;')
					);
					if ( !empty( $this->_options['custom_label-image_alt_text'] ) ) {
						$form_fields['image_alt']['label'] = $this->_options['custom_label-image_alt_text'];
					} else {
						$form_fields['image_alt']['label'] = __('Alternate Text');
					}
					if ( $this->_options['custom_help-image_alt_text'] !== true ) {
						$form_fields['image_alt']['helps'] = $this->_options['custom_help-image_alt_text'];
					}
				}
				
				if ( $this->_options['show_input-image_align'] === true ) {
					$form_fields['align'] = array(
						'label' => __('Alignment'),
						'input' => 'html',
						'html'  => image_align_input_fields($post, get_option('image_default_align')),
					);
					if ( $this->_options['custom_help-image_align'] !== true ) {
						$form_fields['align']['helps'] = $this->_options['custom_help-image_align'];
					}
				}
				
				if ( $this->_options['show_input-image_size'] === true ) {
					$form_fields['image-size'] = image_size_input_fields( $post, get_option('image_default_size', 'medium') );
					if ( $this->_options['custom_help-image_size'] !== true ) {
						$form_fields['image-size']['helps'] = $this->_options['custom_help-image_size'];
					}
				}
			} else {
				unset( $form_fields['image_alt'] );
			}
			
			return $form_fields;
		}
		
		
		function action_media_upload_pb_uploader() {
			return wp_iframe( array( &$this, 'media_uploader' ) ); // callback function must begin with the word media due to WordPress code ridiculousness.
		}
		
		
		function media_uploader() {
			media_upload_header();

			//media_upload_form();
			
			
			
			
			echo '<div style="margin: 1em;">';
			
			
			
			global $type, $tab;

			//$flash_action_url = $this->_parent->_pluginURL . '/lib/medialibrary/async-upload.php'; //admin_url('async-upload2.php');
			$flash_action_url = admin_url('async-upload.php');

			// If Mac and mod_security, no Flash. :(
			$flash = true;
			if ( false !== stripos($_SERVER['HTTP_USER_AGENT'], 'mac') && apache_mod_loaded('mod_security') )
				$flash = false;

			$flash = apply_filters('flash_uploader', $flash);
			$post_id = isset($_REQUEST['post_id']) ? intval($_REQUEST['post_id']) : 0;
			//$post_id = 'pb_medialibrary';

			$upload_size_unit = $max_upload_size =  wp_max_upload_size();
			$sizes = array( 'KB', 'MB', 'GB' );
			for ( $u = -1; $upload_size_unit > 1024 && $u < count( $sizes ) - 1; $u++ )
				$upload_size_unit /= 1024;
			if ( $u < 0 ) {
				$upload_size_unit = 0;
				$u = 0;
			} else {
				$upload_size_unit = (int) $upload_size_unit;
			}
			?>
			<script type="text/javascript">
			//<![CDATA[
			var uploaderMode = 0;
			jQuery(document).ready(function($){
				uploaderMode = getUserSetting('uploader');
				$('.upload-html-bypass a').click(function(){deleteUserSetting('uploader');uploaderMode=0;swfuploadPreLoad();return false;});
				$('.upload-flash-bypass a').click(function(){setUserSetting('uploader', '1');uploaderMode=1;swfuploadPreLoad();return false;});
			});
			//]]>
			</script>
			<div id="media-upload-notice">
			<?php if (isset($errors['upload_notice']) ) { ?>
				<?php echo $errors['upload_notice']; ?>
			<?php } ?>
			</div>
			<div id="media-upload-error">
			<?php if (isset($errors['upload_error']) && is_wp_error($errors['upload_error'])) { ?>
				<?php echo $errors['upload_error']->get_error_message(); ?>
			<?php } ?>
			</div>
			<?php
			// Check quota for this blog if multisite
			if ( function_exists( 'is_multisite' ) && is_multisite() && !is_upload_space_available() ) {
				echo '<p>' . sprintf( __( 'Sorry, you have filled your storage quota (%s MB).' ), get_space_allowed() ) . '</p>';
				return;
			}

			do_action('pre-upload-ui');

			if ( $flash ) : ?>
			<script type="text/javascript">
			//<![CDATA[
			var swfu;
			SWFUpload.onload = function() {
				var settings = {
						button_text: '<span class="button"><?php _e('Select Files'); ?><\/span>',
						button_text_style: '.button { text-align: center; font-weight: bold; font-family:"Lucida Grande",Verdana,Arial,"Bitstream Vera Sans",sans-serif; font-size: 11px; text-shadow: 0 1px 0 #FFFFFF; color:#464646; }',
						button_height: "23",
						button_width: "132",
						button_text_top_padding: 3,
						button_image_url: '<?php echo includes_url('images/upload.png?ver=20100531'); ?>',
						button_placeholder_id: "flash-browse-button",
						upload_url : "<?php echo esc_attr( $flash_action_url ); ?>",
						flash_url : "<?php echo includes_url('js/swfupload/swfupload.swf'); ?>",
						file_post_name: "async-upload",
						file_types: "<?php echo apply_filters('upload_file_glob', '*.*'); ?>",
						post_params : {
							"post_id" : "<?php echo $post_id; ?>",
							"auth_cookie" : "<?php echo (is_ssl() ? $_COOKIE[SECURE_AUTH_COOKIE] : $_COOKIE[AUTH_COOKIE]); ?>",
							"logged_in_cookie": "<?php echo $_COOKIE[LOGGED_IN_COOKIE]; ?>",
							"_wpnonce" : "<?php echo wp_create_nonce('media-form'); ?>",
							"type" : "<?php echo $type; ?>",
							"tab" : "<?php echo $tab; ?>",
							"short" : "1"
						},
						file_size_limit : "<?php echo $max_upload_size; ?>b",
						file_dialog_start_handler : fileDialogStart,
						file_queued_handler : fileQueued,
						upload_start_handler : uploadStart,
						upload_progress_handler : uploadProgress,
						upload_error_handler : uploadError,
						upload_success_handler : pb_uploadSuccess,
						upload_complete_handler : uploadComplete,
						file_queue_error_handler : fileQueueError,
						file_dialog_complete_handler : fileDialogComplete,
						swfupload_pre_load_handler: swfuploadPreLoad,
						swfupload_load_failed_handler: swfuploadLoadFailed,
						custom_settings : {
							degraded_element_id : "html-upload-ui", // id of the element displayed when swfupload is unavailable
							swfupload_element_id : "flash-upload-ui" // id of the element displayed when swfupload is available
						},
						debug: false
					};
					swfu = new SWFUpload(settings);
			};
			//uploadSuccess
			
			function pb_uploadSuccess( fileObj, serverData ) {
				//jQuery("#cancel-upload").attr("disabled","disabled");
				//alert( test );
				//alert( fileObj.id + '~' + post_id + '~' + fileObj.name + '~' +  fileObj.id.replace(/[^0-9]/g,"") );
				
				//jQuery("#media-items").append('<div id="media-item-'+fileObj.id+'" class="media-item child-of-'+post_id+'"><div class="progress"><div class="bar"></div></div><div class="filename original"><span class="percent"></span> '+fileObj.name+"</div></div>");
				//return false;
				
				if(serverData.match("media-upload-error")){
					jQuery("#media-item-"+fileObj.id).html(serverData);
					return
				}
				pb_prepareMediaItem(fileObj,serverData);
				updateMediaForm();
				if(jQuery("#media-item-"+fileObj.id).hasClass("child-of-"+post_id)){
					jQuery("#attachments-count").text(1*jQuery("#attachments-count").text()+1)
				}
				
				
			}
			function pb_prepareMediaItem(fileObj,serverData){
				var f=(typeof shortform=="undefined")?1:2,item=jQuery("#media-item-"+fileObj.id);
				jQuery(".bar",item).remove();
				jQuery(".progress",item).hide();
				try{
					if(typeof topWin.tb_remove!="undefined"){
						topWin.jQuery("#TB_overlay").click(topWin.tb_remove)
					}
				}catch(e){}if(isNaN(serverData)||!serverData){
					item.append(serverData);
					prepareMediaItemInit(fileObj)
				} else {
					item.load("<?php echo $this->_parent->_pluginURL . '/lib/medialibrary/async-upload.php'; ?>?post_id=pb_medialibrary",{attachment_id:serverData,fetch:f},function(){prepareMediaItemInit(fileObj);updateMediaForm()})
				}
			}
			//]]>
			</script>

			<div id="flash-upload-ui" class="hide-if-no-js">
			<?php do_action('pre-flash-upload-ui'); ?>

				<div>
				<?php _e( 'Choose files to upload' ); ?>
				<div id="flash-browse-button"></div>
				<span><input id="cancel-upload" disabled="disabled" onclick="cancelUpload()" type="button" value="<?php esc_attr_e('Cancel Upload'); ?>" class="button" /></span>
				</div>
				<p class="media-upload-size"><?php printf( __( 'Maximum upload file size: %d%s' ), $upload_size_unit, $sizes[$u] ); ?></p>
			<?php do_action('post-flash-upload-ui'); ?>
				<p class="howto"><?php _e('After a file has been uploaded, you can add titles and descriptions.'); ?></p>
			</div>
			<?php endif; // $flash ?>

			<div id="html-upload-ui">
			<?php do_action('pre-html-upload-ui'); ?>
				<p id="async-upload-wrap">
				<label class="screen-reader-text" for="async-upload"><?php _e('Upload'); ?></label>
				<input type="file" name="async-upload" id="async-upload" /> <input type="submit" class="button" name="html-upload" value="<?php esc_attr_e('Upload'); ?>" /> <a href="#" onclick="try{top.tb_remove();}catch(e){}; return false;"><?php _e('Cancel'); ?></a>
				</p>
				<div class="clear"></div>
				<p class="media-upload-size"><?php printf( __( 'Maximum upload file size: %d%s' ), $upload_size_unit, $sizes[$u] ); ?></p>
				<?php if ( is_lighttpd_before_150() ): ?>
				<p><?php _e('If you want to use all capabilities of the uploader, like uploading multiple files at once, please upgrade to lighttpd 1.5.'); ?></p>
				<?php endif;?>
			<?php do_action('post-html-upload-ui', $flash); ?>
			</div>
			<?php do_action('post-upload-ui'); ?>
			<?php
			
			echo '</div>';
			
			$form_action_url = admin_url( 'media-upload.php?type=image&tab=library&post_id=pb_medialibrary' );
			echo '<form enctype="multipart/form-data" method="post" action="' . esc_attr( $form_action_url ) . '" class="media-upload-form type-form validate" id="library-form">';
			wp_nonce_field('media-form');
			echo '<div id="media-items" class="hide-if-no-js"> </div>';
			echo '</form>';
			
			echo '<script type="text/javascript">if(typeof wpOnload==\'function\')wpOnload();</script>';
			echo '</body>';
			echo '</html>';
			
			die(); // Without this the filter will run once per plugin using this lib.
		}
		
		
		function ajax_edit_attachment() {
			return wp_iframe( array( &$this, 'media_editor' ) ); // callback function must begin with the word media due to WordPress code ridiculousness.
		}
		
		
		function media_editor() {
		//media_upload_header();
		
			echo 'moose';
			$errors = '';
			media_upload_library_form( $errors );
			//echo get_media_item( 385, array( 'toggle' => false ) );
			//media_upload_type_form('','',385);
			
			echo '</body></html>';
			die(); // Prevent 0 at end of ajax return.
		}
		
		
		/**
		 *	file_to_library()
		 *
		 *	Takes a local file and inserts it into the WordPress Media Library.
		 *
		 *	$source_file	string		Full file path OR URL with filename to image file. Ex: /home/www/temp_dsijdlfj.jpg
		 *	$file_name		string		Final filename without path. Ex: image.jpg
		 *	$post_id		int			Optional. Post ID to associate attachment with.  Post ID of 0 will not associate it. Default: 0
		 *	$delete_source	boolean		Optional. Delete source file after insertion. Default: false
		 *	$post_data		array		Optional. Array of data to assign to attachment such as a title, caption, etc.
		 */
		function file_to_library( $source_file, $file_name, $post_id = 0, $delete_source = false, $post_data = array() ) {
			$time = current_time('mysql');
			if ( $post = get_post($post_id) ) {
				if ( substr( $post->post_date, 0, 4 ) > 0 )
					$time = $post->post_date;
			}
			
			$mimes = false;
			
			if ( function_exists( 'wp_check_filetype_and_ext' ) ) {
				$wp_filetype = wp_check_filetype_and_ext( $source_file, $file_name, $mimes );
			} else {
				$wp_filetype = wp_check_filetype( $file_name, $mimes );
			}
			extract( $wp_filetype );
			// Check to see if wp_check_filetype_and_ext() determined the filename was incorrect
			if ( $proper_filename )
				$file_name = $proper_filename;
			if ( ( !$type || !$ext ) && !current_user_can( 'unfiltered_upload' ) )
				die( __( 'File type does not meet security guidelines. Try another.' ));
			if ( !$ext )
				$ext = ltrim(strrchr($file_name, '.'), '.');
			if ( !$type )
				$type = 'image/jpeg';
			// A writable uploads dir will pass this test. Again, there's no point overriding this one.
			if ( ! ( ( $uploads = wp_upload_dir($time) ) && false === $uploads['error'] ) )
				die( 'Directory not writable.' );
			
			$filename = wp_unique_filename( $uploads['path'], $file_name, null );
			
			// Move the file to the uploads dir
			$new_file = $uploads['path'] . "/$filename";
			
			if ( false === @ copy( $source_file, $new_file ) ) {
				die( __('The uploaded file could not be moved.' ));
			} else {
				if ( $delete_source === true ) {
					unlink( $source_file );
				}
			}
			// Set correct file permissions
			$stat = stat( dirname( $new_file ));
			$perms = $stat['mode'] & 0000666;
			@ chmod( $new_file, $perms );
			
			// Compute the URL
			$url = $uploads['url'] . "/$filename";
			
			if ( is_multisite() )
				delete_transient( 'dirsize_cache' );
			
			$file = apply_filters( 'wp_handle_upload', array( 'file' => $new_file, 'url' => $url, 'type' => $type ), 'upload' );
			
			$source_file_parts = pathinfo($source_file);
			$source_file = trim( substr( $source_file, 0, -(1 + strlen($source_file_parts['extension'])) ) );
			
			$url = $file['url'];
			$type = $file['type'];
			$file = $file['file'];
			$title = $file_name;
			$content = '';
			
			// use image exif/iptc data for title and caption defaults if possible
			if ( $image_meta = @wp_read_image_metadata($file) ) {
				if ( trim( $image_meta['title'] ) && ! is_numeric( sanitize_title( $image_meta['title'] ) ) )
					$title = $image_meta['title'];
				if ( trim( $image_meta['caption'] ) )
					$content = $image_meta['caption'];
			}
			
			// Construct the attachment array
			$attachment = array_merge( array(
				'post_id'		=> 0,
				'post_mime_type' => $type,
				'guid' => $url,
				'post_parent' => $post_id,
				'post_title' => $title,
				'post_content' => $content,
			), $post_data );
			
			// Save the data
			$id = wp_insert_attachment($attachment, $file, $post_id);
			if ( !is_wp_error($id) ) {
				wp_update_attachment_metadata( $id, wp_generate_attachment_metadata( $id, $file ) );
			}
			
			return $id;
		}
	}
}

/*
if ( !empty( $medialibrary_options ) ) {
	$this->_medialibrary = new PluginBuddyMediaLibrary( $this, $medialibrary_options );
} else {
	$this->_medialibrary = new PluginBuddyMediaLibrary( $this );
}
*/
?>