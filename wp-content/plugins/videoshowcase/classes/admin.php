<?php
if ( !class_exists( "PluginBuddyVideoShowcase_admin" ) ) {
    class PluginBuddyVideoShowcase_admin {
	
		function PluginBuddyVideoShowcase_admin(&$parent) {
			$this->_parent = &$parent;
			$this->_var = &$parent->_var;
			$this->_name = &$parent->_name;
			$this->_options = &$parent->_options;
			$this->_pluginPath = &$parent->_pluginPath;
			$this->_pluginURL = &$parent->_pluginURL;
			$this->_selfLink = &$parent->_selfLink;

			add_action('admin_menu', array(&$this, 'admin_menu')); // Add menu in admin.
			// SHORTCODE THICKBOX HOOK
			add_action('wp_ajax_vsc_shortgen', array(&$this, 'shortcodeGenerator'));
			// Handle ajax attachment for instantly showing image on upload
			add_action( 'wp_ajax_handle_attachment', array( &$this, 'handle_ajax_attachment' ) );
			wp_enqueue_script( 'thickbox' );
			wp_enqueue_style( 'thickbox' );
		}
		function alert( $arg1, $arg2 = false ) {
			$this->_parent->alert( $arg1, $arg2 );
		}
		
		// Gets image id on upload to instantly show new image
		function handle_ajax_attachment() {
			$attachment_data = unserialize( stripslashes( $_POST['image'] ) );
			$imagedata = wp_get_attachment_image_src( $attachment_data['attachment_id'], 'thumbnail' );
			die( $imagedata[0] );		
		}
		
		// SHORTCODE GENERATOR FUNCTION
		function shortcodeGenerator() {
			?>
			<html>
			<head>
			<style type="text/css" >
				body {
					font-family: "Lucida Grande",Verdana,Arial,"Bitstream Vera Sans",sans-serif;
					color: #333333;
					background: #F9F9F9;
				}
				input.shortsub {
					background:url("../images/button-grad.png") repeat-x scroll left top #21759B;
					border-color:#298CBA;
					color:#FFFFFF;
					font-weight:bold;
					text-shadow:0 -1px 0 rgba(0, 0, 0, 0.3);
					-moz-border-radius:11px 11px 11px 11px;
					-moz-box-sizing:content-box;
					border-style:solid;
					border-width:1px;
					cursor:pointer;
					font-size:11px !important;
					line-height:13px;
					padding:3px 8px;
					text-decoration:none;
				}
			</style>
			</head>
			<body>
			<?php
			$limits = array();
			$limits['all'] = 'Show All (default)';
			
			// Count group videos for max
			$vidsum = (count($this->_options['groups'][$_GET['shortid']]['videos']) - 1);
			
			for ( $count = 1; $count <= $vidsum; $count++ ) {
				$limits[$count] = $count;
			}

			// Generate shortcode
			if ( !empty($_POST[$this->_var . '-createshort']) ) {
				$groupid = $_POST[$this->_var . '-gid'];
				$align = '';
				if($_POST[$this->_var . '-align'] !== 'center') {
					$align = ' align="' . $_POST[$this->_var . '-align'] . '"';
				}
				$max = '';
				if($_POST[$this->_var . '-max'] !== 'all') {
					$max = ' max="' . $_POST[$this->_var . '-max'] . '"';	
				}
				$order = '';
				if($_POST[$this->_var . '-order'] !== 'ordered') {
					$order = ' order="random"';
				}
				$theme = '';
				if($_POST[$this->_var . '-theme'] !== 'default') {
					$theme = ' theme="' . $_POST[$this->_var . '-theme'] . '"';
				}
				
				echo '<h3>Copy the shortcode below</h3>';
				echo '[pb_videoshowcase group="' . $groupid . '"' . $align . '' . $max . '' . $order . '' . $theme . ']';
			}
			else {
				// Show shortcode form
				echo '<h3>Create Shortcode for ' . $this->_options['groups'][$_GET['shortid']]['name'] . '</h3>';
				echo '<form method="post" action="">';
				echo '<table class="form-table">';
					echo '<tr>';
						echo '<td><label for="max">Maximum videos to show:</label></td>';
						echo '<td>';
							echo '<select name="' . $this->_var . '-max">';
								foreach ($limits as $key => $opt) {
									echo '<option value="' . $key . '">' . $opt . '</option>';
								}
							echo '</select>';
						echo '</td>';
					echo '</tr>';
					echo '<tr>';
						echo '<td><label for="align">Horizontal alignment:</label></td>';
						echo '<td>';
							echo '<select name="' . $this->_var . '-align">';
								echo '<option value="center">Center (default)</option>';
								echo '<option value="left">Left</option>';
								echo '<option value="right">Right</option>';
								echo '<option value="none">None (controlled by CSS)</option>';
							echo '</select>';
						echo '<td>';
					echo '</tr>';
					echo '<tr>';
						echo '<td><label for="order">Video Order:</label></td>';
						echo '<td>';
							echo '<input type="radio" name="' . $this->_var . '-order" value="ordered" checked /> Ordered<br />';
							echo '<input type="radio" name="' . $this->_var . '-order" value="random" /> Random';
						echo '</td>';
					echo '</tr>';
					$themes = array('default','light_rounded', 'dark_rounded', 'light_square', 'dark_square');
					echo '<tr>';
						echo '<td><label for="order">Thickbox Theme:</label></td>';
						echo '<td>';
							echo '<select name="' . $this->_var . '-theme">';
								foreach ( (array) $themes as $tbtheme ){
									echo '<option value="' . $tbtheme . '">' . $tbtheme . '</option>';
								}
							echo '</select>';
						echo '</td>';
					echo '</tr>';
					echo '<input type="hidden" name="' . $this->_var . '-gid" value="' . $_GET['shortid'] . '" />';
				echo '</table>';
				echo '<p><input type="submit" name="' . $this->_var . '-createshort" value="Create Shortcode" class="shortsub" /></p>';
				$this->nonce();
				echo '</form>';
			}
			?>
			</body>
			</html>
			<?php
			die();
		}

		function nonce() {
			wp_nonce_field( $this->_parent->_var . '-nonce' );
		}
		
		/**
		 *	savesettings()
		 *	
		 *	Saves a form into the _options array.
		 *	
		 *	Use savepoint to set the root array key path. Accepts variable depth, dividing array keys with pound signs.
		 *	Ex:	$_POST['savepoint'] value something like array_key_name#subkey
		 *		<input type="hidden" name="savepoint" value="files#exclusions" /> to set the root to be $this->_options['files']['exclusions']
		 *	
		 *	All inputs with the name beginning with pound will act as the array keys to be set in the _options with the associated posted value.
		 *	Ex:	$_POST['#key_name'] or $_POST['#key_name#subarray_key_name'] value is the array value to set.
		 *		<input type="text" name="#name" /> will save to $this->_options['name']
		 *		<input type="text" name="#group#17#name" /> will save to $this->_options['groups'][17]['name']
		 *
		 *	$savepoint_root		string		Override the savepoint. Same format as the form savepoint.
		 */
		function savesettings( $savepoint_root = '' ) {
			check_admin_referer( $this->_parent->_var . '-nonce' );
			
			if ( !empty( $savepoint_root ) ) { // Override savepoint.
				$_POST['savepoint'] = $savepoint_root;
			}
			
			if ( !empty( $_POST['savepoint'] ) ) {
				$savepoint_root = stripslashes( $_POST['savepoint'] ) . '#';
			} else {
				$savepoint_root = '';
			}
			
			$posted = stripslashes_deep( $_POST ); // Unescape all the stuff WordPress escaped. Sigh @ WordPress for being like PHP magic quotes.
			foreach( $posted as $index => $item ) {
				if ( substr( $index, 0, 1 ) == '#' ) {
					$savepoint_subsection = &$this->_options;
					$savepoint_levels = explode( '#', $savepoint_root . substr( $index, 1 ) );
					foreach ( $savepoint_levels as $savepoint_level ) {
						$savepoint_subsection = &$savepoint_subsection{$savepoint_level};
					}
					$savepoint_subsection = $item;
				}
			}
			
			$this->_parent->save();
			$this->alert( __('Settings saved...', 'it-l10n-backupbuddy') );
		}

		function _createGroup() {
			$this->_parent->load();

			if ( empty( $_POST[$this->_var . '-group-name'] ) ) { // If they gave a blank group name, fail.
				$this->_errors[] = 'name';
				$this->_showErrorMessage( 'A name is required to create a new group.' );
			}
			if ( !isset( $this->_errors['name'] ) ) {
				foreach ( (array) $this->_options['groups'] as $id => $group ) { // Loop through to make sure group name doesnt already exist
					if ( $group['name'] == $_POST[$this->_var . '-group-name'] ) { // Found a match.
						$this->_errors[] = 'duplicatename';
						$this->_showErrorMessage( 'A group with the entered name already exists.' );
						break; // exit loop. no need to keep looping if we found one matching group already. one error is enough to stop
					}
				}
			}
			
			// Group Width Validation
			if ( empty( $_POST[$this->_var . '-group-width']) ) {
				$this->_errors[] = 'width';
				$this->_showErrorMessage( 'A width is required to create a new group.' );
			}
			else{
				if( !is_numeric( $_POST[$this->_var . '-group-width'] ) ) {
					$this->_errors[] = 'notnumwidth';
					$this->_showErrorMessage( 'Group Width must be a number');
				}
			}
			
			// Group Height Validation
			if ( empty( $_POST[$this->_var . '-group-height']) ) {
				$this->_errors[] = 'height';
				$this->_showErrorMessage( 'A height is required to create a new group.' );
			}
			else{
				if( !is_numeric( $_POST[$this->_var . '-group-height'] ) ) {
					$this->_errors[] = 'notnumheight';
					$this->_showErrorMessage( 'Group Height must be a number');
				}
			}
			
			if ( isset( $this->_errors ) ) {
				$this->_showErrorMessage( 'Please correct the ' . ngettext( 'error', 'errors', count( $this->_errors ) ) . ' in order to add the new group.' );
			} else { // No errors, so add the group

				// Get index for new group by adding 1 to the largest index currently in the groups. Put in $newID
				if ( is_array( $this->_options['groups'] ) && !empty( $this->_options['groups'] ) ) {
					$newID = max( array_keys( $this->_options['groups'] ) ) + 1;
				} else {
					$newID = 0;
				}

				$this->_options['groups'][$newID] = $this->_parent->_groupdefaults; // Load group defaults.
				$this->_options['groups'][$newID]['name'] = $_POST[$this->_var . '-group-name']; // Set name of new group.
				$this->_options['groups'][$newID]['width'] = $_POST[$this->_var . '-group-width'];
				$this->_options['groups'][$newID]['height'] = $_POST[$this->_var . '-group-height'];

				$this->_parent->save(); // Save changes to database

				$this->_showStatusMessage( "The group \"" . stripslashes($_POST[$this->_var . '-group-name']) . "\" has been added." );
			}
			
		}
		
		function _editGroup() {
			$group = $_POST[$this->_var . '-groupid'];
			
			// NAME VALIDATION
			if ( empty( $_POST[$this->_var . '-name'] )) {
				$this->_errors[] = 'noname';
				$this->_showErrorMessage( 'You must add a name.');
			}
			else {
				$name = $_POST[$this->_var . '-name'];
				$is_array = ( preg_match( '/\[\]$/', $name ));
				$name = str_replace( '[]', '', $name );
				$var_name = preg_replace('/^' . $this->_var . '-/', '', $name );
		
				if ( $is_array && empty( $_POST[$this->_var . '-name'] )) {
					$_POST[$this->_var . '-name'] = array();
				}
		
				if ( ($_POST[$this->_var . '-name']) !== ($this->_options['groups'][$group]['name']) ) {
					foreach ( ($this->_options['groups']) as $id => $path ) { // Loop through to make sure group name doesnt already exist
						if ( $path['name'] == $_POST[$this->_var . '-name'] ) { // Found a match.
							$this->_errors[] = 'name';
							$this->_showErrorMessage( 'A group with the entered name already exists.' );
							break; // exit loop found match
						}
					}
				}
			}
			
			$gpath = $this->_options['groups'][$group];
			
			// CHECK WIDTH
			if ( (empty( $_POST[$this->_var . '-group-width'])) || (!is_numeric($_POST[$this->_var . '-group-width'])) ) {
				$this->_errors[] = 'width';
				$this->_showErrorMessage( 'Width must be a number.' );
			}
			// CHECK HEIGHT
			if ( empty( $_POST[$this->_var . '-group-height']) || !is_numeric($_POST[$this->_var . '-group-height']) ) {
				$this->_errors[] = 'height';
				$this->_showErrorMessage( 'Height must be a number.' );
			}
			
			if ( isset( $this->_errors ) ) {
				$this->_showErrorMessage( 'Please correct the ' . ngettext( 'error', 'errors', count( $this->_errors ) ) . ' in order to add the new group.' );
			}
			else {
				// UPDATE GROUP SETTINGS
				$this->_options['groups'][$group]['width'] = $_POST[$this->_var . '-group-width'];
				$this->_options['groups'][$group]['height'] = $_POST[$this->_var . '-group-height'];
				$this->_options['groups'][$group]['name'] = $_POST[$this->_var . '-name'];
				$this->_options['groups'][$group]['tlink'] = $_POST[$this->_var . '-tlink'];
				$this->_options['groups'][$group]['related'] = $_POST[$this->_var . '-relate'];

				$this->_parent->save(); // Save changes to database

				$this->_showStatusMessage( stripslashes($_POST[$this->_var . '-name']) . " has been updated.");
			}
		}
		
		function _deleteGroups() {
			$names = array();
		
			if ( ! empty( $_POST[$this->_var . '-groups'] ) && is_array( $_POST[$this->_var . '-groups'] ) ) {
				foreach ( (array) $_POST[$this->_var . '-groups'] as $id ) {
					$names[] = $this->_options['groups'][$id]['name'];
					unset( $this->_options['groups'][$id] );
				}
				$this->_parent->save();
			}

			natcasesort( $names );
		
			if ( !empty($names) ) {
				$this->_showStatusMessage( 'Successfully deleted the group.' );
			}
			else {
				$this->_showErrorMessage( 'No Groups were selected for deletion' );
			}
		}

		function _addVideo() {
			if ( empty($_POST[$this->_var . '-title']) ) {
				$this->_errors[] = 'no title';
				$this->_showErrorMessage('You must have a title');
			}
			else {
				$vtitle = $_POST[$this->_var . '-title'];
			}
			
			if ( !empty($_POST[$this->_var . '-url']) ) {
				$HTML_SOURCE = $_POST[$this->_var . '-url'];

				$Ypos = strpos( $HTML_SOURCE, 'youtube' );
				$Vpos = strpos( $HTML_SOURCE, 'vimeo' );

				// IF YOUTUBE
				if( $Ypos !== false ) {
					// GET VERSION # FROM URL
					function getYTid($ytURL) {
						
						$ytvIDlen = 11; // This is the length of YouTube's video IDs
						
						// The ID string starts after "v=", which is usually right after
						// "youtube.com/watch?" in the URL
						$idStarts = strpos($ytURL, "?v=");
						
						// In case the "v=" is NOT right after the "?" (not likely, but I like to keep my
						// bases covered), it will be after an "&":
						if($idStarts === FALSE){
							$idStarts = strpos($ytURL, "&v=");
						}
						
						// If still FALSE, URL doesn't have a vid ID
						if($idStarts === FALSE){
							die("YouTube video ID not found. Please double-check your URL.");
						}
						
						// Offset the start location to match the beginning of the ID string
						$idStarts +=3;
						
						// Get the ID string and return it
						$ytvID = substr($ytURL, $idStarts, $ytvIDlen);
						
						return $ytvID;
						
					}
					
					$YTid = getYTid($HTML_SOURCE);
					$YTimage = 'http://img.youtube.com/vi/' . $YTid . '/0.jpg';
					
					$group = $_POST[$this->_var . '-groupid'];

					// Get index for new entry by adding 1 to the largest index currently in the entry. Put in $newID
					if ( is_array( $this->_options['groups'][$group]['videos'] ) && !empty( $this->_options['groups'][$group]['videos'] ) ) {
						$newID = max( array_keys( $this->_options['groups'][$group]['videos'] ) ) + 1;
						$ordkey = max( array_keys( $this->_options['groups'][$group]['order'] ) ) + 1;
					} else {
						$newID = 0;
						$ordkey = 0;
					}

					// Add image to media library
					$name = uniqid();
					$image_id = $this->_parent->_medialibrary->file_to_library( $YTimage, $name . '.jpg', 0, false );
					
					$this->_options['groups'][$group]['videos'][$newID]['vurl'] = $HTML_SOURCE;
					$this->_options['groups'][$group]['videos'][$newID]['vid'] = $YTid;
					$this->_options['groups'][$group]['videos'][$newID]['vimage'] = $image_id;
					$this->_options['groups'][$group]['videos'][$newID]['vtitle'] = $vtitle;
					$this->_options['groups'][$group]['videos'][$newID]['vsourc'] = 'youtube';
					$this->_options['groups'][$group]['order'][$ordkey] = $newID; // sets default order num
		
					$this->_parent->save(); // Save changes to database.

					$this->_showStatusMessage( "The video has been added." );
					
				}
				// IF VIMEO
				elseif ( $Vpos !== false ) {
					$path = parse_url($HTML_SOURCE, PHP_URL_PATH);
					$VEMid = str_replace( '/', '', $path);
					
					$response = wp_remote_get( 'http://vimeo.com/api/v2/video/' . $VEMid . '.php' );
					if( is_wp_error( $response ) ) {
						$this->_errors[] = 'cant connect';
						$this->_showErrorMessage( 'file_get_contents, curl, or fopen must be enabled to get Vimeo thumbnail images.' );
						echo $response->get_error_message();
					} else {
						$output = unserialize($response['body']);
						$VEMimage = '';
						if (isset($output[0]['thumbnail_large'])) {
							$VEMimage = $output[0]['thumbnail_large'];
						}
					}
					
					if ( isset( $this->_errors ) ) {
						$this->_showErrorMessage( 'Please correct the ' . count( $this->_errors ) . ' in order to add the new group.' );
					} else {
						$group = $_POST[$this->_var . '-groupid'];

						// Get index for new entry by adding 1 to the largest index currently in the entry. Put in $newID
						if ( is_array( $this->_options['groups'][$group]['videos'] ) && !empty( $this->_options['groups'][$group]['videos'] ) ) {
							$newID = max( array_keys( $this->_options['groups'][$group]['videos'] ) ) + 1;
							$ordkey = max( array_keys( $this->_options['groups'][$group]['order'] ) ) + 1;
						} else {
							$newID = 0;
							$ordkey = 0;
						}
						
						// Add image to media library
						$name = uniqid();
						
						// check if was able to pull image from Vimeo api
						if ( $VEMimage == '') {
							// run search for existing default image
							global $post;
							$args = array( 'post_type' => 'attachment', 'numberposts' => 1, 'post_status' => null, 'post_parent' => null, 'name' => 'pb_vsc_imgdefault.png');
							$defaultimgcheck = get_posts( $args );
							
							// if default image exist get image id
							if(!empty($defaultimgcheck)) {
								foreach ($defaultimgcheck as $defaultvimg) {
									//define default vimeo image id
									$image_id = $defaultvimg->ID;
								}
							} else { // if no existing default image create one
								$VEMimage = $this->_pluginURL . '/images/pb_vsc_imgdefault.png';
								$image_id = $this->_parent->_medialibrary->file_to_library( $VEMimage, 'pb_vsc_imgdefault.png', 0, false );
							}
						} else {
							$image_id = $this->_parent->_medialibrary->file_to_library( $VEMimage, $name . '.jpg', 0, false );
						}
						$this->_options['groups'][$group]['videos'][$newID]['vurl'] = $HTML_SOURCE;
						$this->_options['groups'][$group]['videos'][$newID]['vid'] = $VEMid;
						$this->_options['groups'][$group]['videos'][$newID]['vimage'] = $image_id;
						$this->_options['groups'][$group]['videos'][$newID]['vtitle'] = $vtitle;
						$this->_options['groups'][$group]['videos'][$newID]['vsourc'] = 'vimeo';
						$this->_options['groups'][$group]['order'][$ordkey] = $newID; // sets default order num
						
						$this->_parent->save(); // Save changes to database.
						
						$this->_showStatusMessage( "The video has been added." );
					}
					
				}
				// INCORRECT URL
				else {
					$this->_errors[] = 'incorrecturl';
					$this->_showErrorMessage( 'Url is not youtube or vimeo.');
				}
			}
			else {
				$this->_showErrorMessage( 'You must enter a url' );
			}
		}
		
		function _addcustVideo() {
			if ( empty($_POST[$this->_var . '-cvtitle']) ) {
				$this->_errors[] = 'title';
				$this->_showErrorMessage( 'Video must have a title' );
			}
			if ( empty($_POST[$this->_var . '-cvurl']) ) {
				$this->_errors[] = 'title';
				$this->_showErrorMessage( 'Video must have a url');
			}
			
			if ( isset($this->_errors) ) {
				$this->_showErrorMessage( 'Please correct the ' . ngettext( 'error', 'errors', count( $this->_errors ) ) . ' to edit the video.' );
			}
			else {
				$group = $_POST[$this->_var . '-groupid'];

				// Get index for new entry by adding 1 to the largest index currently in the entry. Put in $newID
				if ( is_array( $this->_options['groups'][$group]['videos'] ) && !empty( $this->_options['groups'][$group]['videos'] ) ) {
					$newID = max( array_keys( $this->_options['groups'][$group]['videos'] ) ) + 1;
					$ordkey = max( array_keys( $this->_options['groups'][$group]['order'] ) ) + 1;
				} else {
					$newID = 0;
					$ordkey = 0;
				}
				
				$HTML_SOURCE = $_POST[$this->_var . '-cvurl'];
				$source = 'custom';
				if(strpos($HTML_SOURCE,'.mov') !== false) {
					$source = 'quick';
				}
				
				$this->_options['groups'][$_POST[$this->_var . '-groupid']]['videos'][$newID]['vtitle'] = $_POST[$this->_var . '-cvtitle'];
				$this->_options['groups'][$_POST[$this->_var . '-groupid']]['videos'][$newID]['vid'] = 'custom';
				$this->_options['groups'][$_POST[$this->_var . '-groupid']]['videos'][$newID]['vurl'] = $_POST[$this->_var . '-cvurl'];
				$this->_options['groups'][$_POST[$this->_var . '-groupid']]['videos'][$newID]['vsourc'] = $source;
				$this->_options['groups'][$group]['order'][$ordkey] = $newID; // sets default order num
				
				// If upload custom image
				if (!empty($_POST['attachment_data'])) {
					$attachment_data = unserialize( stripslashes( $_POST['attachment_data'] ) );
					$this->_options['groups'][$_POST[$this->_var . '-groupid']]['videos'][$newID]['vimage'] = $attachment_data['attachment_id'];
				}
				
				$this->_showStatusMessage( 'The video has been updated.' );
				$this->_parent->save();
			}
		}
		
		function _editVideo() {
			if ( empty($_POST[$this->_var . '-title']) ) {
				$this->_errors[] = 'title';
				$this->_showErrorMessage( 'Video must have a title' );
			}
			
			if ( isset($this->_errors) ) {
				$this->_showErrorMessage( 'Please correct the ' . ngettext( 'error', 'errors', count( $this->_errors ) ) . ' to edit the video.' );
			}
			else {
				$this->_options['groups'][$_POST[$this->_var . '-groupid']]['videos'][$_POST[$this->_var . '-videoid']]['vtitle'] = $_POST[$this->_var . '-title'];
				
				// If upload custom image
				if (!empty($_POST['attachment_data'])) {
					$attachment_data = unserialize( stripslashes( $_POST['attachment_data'] ) );
					$this->_options['groups'][$_POST[$this->_var . '-groupid']]['videos'][$_POST[$this->_var . '-videoid']]['vimage'] = $attachment_data['attachment_id'];
				}
				
				$this->_showStatusMessage( 'The video has been updated.' );
				$this->_parent->save();
			}
		}

		function _deleteVideos() {
			$names = array();

			$group = $_POST[$this->_var . '-groupid'];

			if ( ! empty( $_POST[$this->_var . '-videos'] ) && is_array( $_POST[$this->_var . '-videos'] ) ) {
				foreach ( (array) $_POST[$this->_var . '-videos'] as $id ) {
					$key = array_search($id, $this->_options['groups'][$group]['order']);
					$names[] = $this->_options['groups'][$group]['name'];
					unset( $this->_options['groups'][$group]['videos'][$id] );
					unset( $this->_options['groups'][$group]['order'][$key] );
				}
				$this->_parent->save();
			}

			natcasesort( $names );
		
			if ( $names ) {
				$this->_showStatusMessage( 'Successfully deleted the video(s).' );
			}
			else {
				$this->_showErrorMessage( 'No videos were selected to be deleted.' );
			}
		}

		function _saveOrder() {
			check_admin_referer( $this->_var . '-nonce' );
		
			$group = $_POST[$this->_var . '-groupid'];

			$beforder = $_POST['hidnorder'];
			$midorder = str_replace('&reorder-table[]=', ',', $beforder);
			$aftorder = str_replace('reorder-table[]=', '', $midorder);
			$finorder = explode(',', $aftorder);
		
			if( $finorder[0] == '' ) {
				$this->_showStatusMessage( ' Order stayed the same ' );
			}
			else {		
			
					$this->_options['groups'][$group]['order'] = $finorder;
			

				$this->_parent->save();
				$this->_showStatusMessage( ' Succesfully updated the video order' );
			}
		}
		
		
		// PUBLIC DISPLAY OF MESSAGES ////////////////////////
	
		function _showStatusMessage( $message ) {
			echo '<div id="message" class="updated fade"><p><strong>'.$message.'</strong></p></div>';			
		}
		function _showErrorMessage( $message ) {
			echo '<div id="message" class="error"><p><strong>'.$message.'</strong></p></div>';
		}

		
		
		function admin_scripts() {
			//wp_enqueue_script( 'jquery' );
			wp_enqueue_script( 'pluginbuddy-tooltip-js', $this->_parent->_pluginURL . '/js/tooltip.js' );
			wp_print_scripts( 'pluginbuddy-tooltip-js' );
			wp_enqueue_script( 'pluginbuddy-'.$this->_var.'-admin-js', $this->_parent->_pluginURL . '/js/admin.js' );
			wp_print_scripts( 'pluginbuddy-'.$this->_var.'-admin-js' );
			wp_enqueue_script('tablednd-js', $this->_pluginURL . '/js/jquery.tablednd_0_5.js' );
			wp_print_scripts('tablednd-js');
			if ( !wp_script_is( 'media-upload' ) ) {
				wp_enqueue_script( 'media-upload' );
				wp_print_scripts( 'media-upload' );
			}
			echo '<link rel="stylesheet" href="'.$this->_pluginURL . '/css/admin.css" type="text/css" media="all" />';
		}

		/**
		 *	get_feed()
		 *
		 *	Gets an RSS or other feed and inserts it as a list of links...
		 *
		 *	$feed		string		URL to the feed.
		 *	$limit		integer		Number of items to retrieve.
		 *	$append		string		HTML to include in the list. Should usually be <li> items including the <li> code.
		 *	$replace	string		String to replace in every title returned. ie twitter includes your own username at the beginning of each line.
		 */
		function get_feed( $feed, $limit, $append = '', $replace = '' ) {
			require_once(ABSPATH.WPINC.'/feed.php');  
			$rss = fetch_feed( $feed );
			if (!is_wp_error( $rss ) ) {
				$maxitems = $rss->get_item_quantity( $limit ); // Limit 
				$rss_items = $rss->get_items(0, $maxitems); 
				
				echo '<ul class="pluginbuddy-nodecor">';

				$feed_html = get_transient( md5( $feed ) );
				if ( $feed_html == '' ) {
					foreach ( (array) $rss_items as $item ) {
						$feed_html .= '<li>- <a href="' . $item->get_permalink() . '">';
						$title =  $item->get_title(); //, ENT_NOQUOTES, 'UTF-8');
						if ( $replace != '' ) {
							$title = str_replace( $replace, '', $title );
						}
						if ( strlen( $title ) < 30 ) {
							$feed_html .= $title;
						} else {
							$feed_html .= substr( $title, 0, 32 ) . ' ...';
						}
						$feed_html .= '</a></li>';
					}
					set_transient( md5( $feed ), $feed_html, 300 ); // expires in 300secs aka 5min
				}
				echo $feed_html;
				
				echo $append;
				echo '</ul>';
			} else {
				echo 'Temporarily unable to load feed...';
			}
		}
		
		
		function view_gettingstarted() {
			echo '<link rel="stylesheet" href="' . $this->_pluginURL . '/css/admin.css" type="text/css" media="all" />';
			require( 'view_gettingstarted.php' );
		}
		
		
		function view_settings() {
			require( 'view_settings.php' );
		}
		
		
		/** admin_menu()
		 *
		 * Initialize menu for admin section.
		 *
		 */		
		function admin_menu() {
			if ( isset( $this->_parent->_series ) && ( $this->_parent->_series != '' ) ) {
				// Handle series menu. Create series menu if it does not exist.
				global $menu;
				$found_series = false;
				foreach ( $menu as $menus => $item ) {
					if ( $item[0] == $this->_parent->_series ) {
						$found_series = true;
					}
				}
				if ( $found_series === false ) {
					add_menu_page( $this->_parent->_series . ' Getting Started', $this->_parent->_series, 'edit_posts', 'pluginbuddy-' . strtolower( $this->_parent->_series ), array(&$this, 'view_gettingstarted'), $this->_parent->_pluginURL.'/images/pluginbuddy.png' );
					add_submenu_page( 'pluginbuddy-' . strtolower( $this->_parent->_series ), $this->_parent->_name.' Getting Started', 'Getting Started', 'edit_posts', 'pluginbuddy-' . strtolower( $this->_parent->_series ), array(&$this, 'view_gettingstarted') );
				}
				// Register for getting started page
				global $pluginbuddy_series;
				if ( !isset( $pluginbuddy_series[ $this->_parent->_series ] ) ) {
					$pluginbuddy_series[ $this->_parent->_series ] = array();
				}
				$pluginbuddy_series[ $this->_parent->_series ][ $this->_parent->_name ] = $this->_pluginPath;
				
				add_submenu_page( 'pluginbuddy-' . strtolower( $this->_parent->_series ), $this->_parent->_name, $this->_parent->_name, $this->_options['access'], $this->_parent->_var.'-settings', array(&$this, 'view_settings'));
			} else { // NOT IN A SERIES!
				// Add main menu (default when clicking top of menu)
				add_menu_page($this->_parent->_name.' Getting Started', $this->_parent->_name, 'administrator', $this->_parent->_var, array(&$this, 'view_gettingstarted'), $this->_parent->_pluginURL.'/images/pluginbuddy.png');
				// Add sub-menu items (first should match default page above)
				add_submenu_page( $this->_parent->_var, $this->_parent->_name.' Getting Started', 'Getting Started', 'administrator', $this->_parent->_var, array(&$this, 'view_gettingstarted'));
				//add_submenu_page( $this->_parent->_var, $this->_parent->_name.' Themes & Devices', 'Themes & Devices', 'administrator', $this->_parent->_var.'-themes', array(&$this, 'view_themes'));
				add_submenu_page( $this->_parent->_var, $this->_parent->_name.' Settings', 'Settings', 'administrator', $this->_parent->_var.'-settings', array(&$this, 'view_settings'));
			}
		}


    } // End class
	
	$PluginBuddyVideoShowcase_admin = new PluginBuddyVideoShowcase_admin($this); // Create instance
}
