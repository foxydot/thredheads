<?php
class WPMDBPro_Media_Files extends WPMDBPro_Addon {
	protected $files_to_migrate;
	protected $responding_to_get_remote_media_listing = false;

	function __construct( $plugin_file_path ) {
		parent::__construct( $plugin_file_path );

		if( ! $this->meets_version_requirements( '1.3.1' ) ) return;

		add_action( 'wpmdb_after_advanced_options', array( $this, 'migration_form_controls' ) );
		add_action( 'wpmdb_load_assets', array( $this, 'load_assets' ) );
		add_action( 'wpmdb_js_variables', array( $this, 'js_variables' ) );
		add_filter( 'wpmdb_accepted_profile_fields', array( $this, 'accepted_profile_fields' ) );
		add_filter( 'wpmdb_establish_remote_connection_data', array( $this, 'establish_remote_connection_data' ) );

		// internal AJAX handlers
		add_action( 'wp_ajax_wpmdbmf_determine_media_to_migrate', array( $this, 'ajax_determine_media_to_migrate' ) );
		add_action( 'wp_ajax_wpmdbmf_migrate_media', array( $this, 'ajax_migrate_media' ) );

		// external AJAX handlers
		add_action( 'wp_ajax_nopriv_wpmdbmf_get_remote_media_listing', array( $this, 'respond_to_get_remote_media_listing' ) );
		add_action( 'wp_ajax_nopriv_wpmdbmf_push_request', array( $this, 'respond_to_push_request' ) );
		add_action( 'wp_ajax_nopriv_wpmdbmf_remove_local_attachments', array( $this, 'respond_to_remove_local_attachments' ) );
	}

	function get_local_attachments() {
		global $wpdb;
		$prefix = $wpdb->prefix;
		$temp_prefix = stripslashes( $_POST['temp_prefix'] );

		/*
		* We determine which media files need migrating BEFORE the database migration is finalized.
		* Because of this we need to scan the *_post & *_postmeta that are prefixed using the temporary prefix. 
		* Though this should only happen when we're responding to a get_remote_media_listing() call AND it's a push OR
		* we're scanning local files AND it's a pull.
		*/

		if( 
			( true == $this->responding_to_get_remote_media_listing && $_POST['intent'] == 'push' ) ||
			( false == $this->responding_to_get_remote_media_listing && $_POST['intent'] == 'pull' )
		) {

			$local_tables = array_flip( $this->get_tables() );

			$posts_table_name = "{$temp_prefix}{$prefix}posts";
			$postmeta_table_name = "{$temp_prefix}{$prefix}postmeta";

			if( isset( $local_tables[$posts_table_name] ) && isset( $local_tables[$postmeta_table_name] ) ) {
				$prefix = $temp_prefix . $prefix;
			}

		}

		$local_attachments = $wpdb->get_results(
			"SELECT `{$prefix}posts`.`post_modified_gmt` AS 'date', pm1.`meta_value` AS 'file', pm2.`meta_value` AS 'metadata'
			FROM `{$prefix}posts`
			INNER JOIN `{$prefix}postmeta` pm1 ON `{$prefix}posts`.`ID` = pm1.`post_id` AND pm1.`meta_key` = '_wp_attached_file'
			LEFT OUTER JOIN `{$prefix}postmeta` pm2 ON `{$prefix}posts`.`ID` = pm2.`post_id` AND pm2.`meta_key` = '_wp_attachment_metadata'
			WHERE `{$prefix}posts`.`post_type` = 'attachment'", ARRAY_A
		);

		if( is_multisite() ) {
			$blogs = $this->get_blogs();
			$prefix = $wpdb->prefix;
			foreach( $blogs as $blog ) {
				$posts_table_name = "{$temp_prefix}{$prefix}{$blog}_posts";
				$postmeta_table_name = "{$temp_prefix}{$prefix}{$blog}_postmeta";
				if( isset( $local_tables[$posts_table_name] ) && isset( $local_tables[$postmeta_table_name] ) ) {
					$prefix = $temp_prefix . $prefix;
				}
				$attachments = $wpdb->get_results(
					"SELECT `{$prefix}{$blog}_posts`.`post_modified_gmt` AS 'date', pm1.`meta_value` AS 'file', pm2.`meta_value` AS 'metadata', {$blog} AS 'blog_id'
					FROM `{$prefix}{$blog}_posts`
					INNER JOIN `{$prefix}{$blog}_postmeta` pm1 ON `{$prefix}{$blog}_posts`.`ID` = pm1.`post_id` AND pm1.`meta_key` = '_wp_attached_file'
					LEFT OUTER JOIN `{$prefix}{$blog}_postmeta` pm2 ON `{$prefix}{$blog}_posts`.`ID` = pm2.`post_id` AND pm2.`meta_key` = '_wp_attachment_metadata'
					WHERE `{$prefix}{$blog}_posts`.`post_type` = 'attachment'", ARRAY_A
				);

				$local_attachments = array_merge( $attachments, $local_attachments );
			}
		}

		$local_attachments = array_map( array( $this, 'process_attachment_data' ), $local_attachments );
		$local_attachments = array_filter( $local_attachments );

		return $local_attachments;
	}

	function get_flat_attachments( $attachments ) {
		$flat_attachments = array();
		foreach( $attachments as $attachment ) {
			$flat_attachments[] = $attachment['file'];
			if( isset( $attachment['sizes'] ) ) {
				$flat_attachments = array_merge( $flat_attachments, $attachment['sizes'] );
			}
		}
		return $flat_attachments;
	}

	function process_attachment_data( $attachment ) {
		if( isset( $attachment['blog_id'] ) ) { // used for multisite
			if( defined( 'UPLOADBLOGSDIR' ) ) {
				$upload_dir = sprintf( '%s/files/', $attachment['blog_id'] );
			}
			else {
				$upload_dir = sprintf( 'sites/%s/', $attachment['blog_id'] );
			}
			$attachment['file'] = $upload_dir . $attachment['file'];
		}
		$upload_dir = str_replace( basename( $attachment['file'] ), '', $attachment['file'] );
		if( ! empty( $attachment['metadata'] ) ) {
			$attachment['metadata'] = @unserialize( $attachment['metadata'] );
			if( isset( $attachment['metadata']['sizes'] ) ) {
				foreach( $attachment['metadata']['sizes'] as $size ) {
					$attachment['sizes'][] = $upload_dir . $size['file'];
				}
			}
		}
		unset( $attachment['metadata'] );
		return $attachment;
	}

	function uploads_dir() {
		if( defined( 'UPLOADBLOGSDIR' ) ) {
			$upload_dir = trailingslashit( ABSPATH ) . UPLOADBLOGSDIR;
		} 
		else {
			$upload_dir = wp_upload_dir();
			$upload_dir = $upload_dir['basedir'];
		}
		return trailingslashit( $upload_dir );
	}

	function get_local_media() {
		$upload_dir = untrailingslashit( $this->uploads_dir() );
		if( ! file_exists( $upload_dir ) ) return array();

		$files = new RecursiveIteratorIterator( new RecursiveDirectoryIterator( $upload_dir ), RecursiveIteratorIterator::SELF_FIRST );
		$local_media = array();

		foreach( $files as $name => $object ){
			$name = str_replace( array( $upload_dir . DS, '\\' ), array( '', '/' ), $name );
			$local_media[$name] = $object->getSize();
		}

		return $local_media;
	}

	function ajax_migrate_media() {
		$this->set_time_limit();

		if( $_POST['intent'] == 'pull' ) {
			$this->process_pull_request();
		}
		else {
			$this->process_push_request();
		}
	}

	function process_pull_request() {
		$files_to_download = $_POST['file_chunk'];
		$remote_uploads_url = trailingslashit( $_POST['remote_uploads_url'] );
		$parsed = parse_url( $_POST['url'] );
		if( ! empty( $parsed['user'] ) ) {
			$credentials = sprintf( '%s:%s@', $parsed['user'], $parsed['pass'] );
			$remote_uploads_url = str_replace( '://', '://' . $credentials, $remote_uploads_url );
		}

		$upload_dir = $this->uploads_dir();

		$errors = array();
		foreach( $files_to_download as $file_to_download ) {
			$temp_file_path = $this->download_url( $remote_uploads_url . $file_to_download );
			
			if( is_wp_error( $temp_file_path ) ) {
				$download_error = $temp_file_path->get_error_message();
				$errors[] = 'Could not download file: ' . $remote_uploads_url . $file_to_download . ' - ' . $download_error;
				continue;
			}

			$date = str_replace( basename( $file_to_download ), '', $file_to_download );
			$new_path = $upload_dir . $date . basename( $file_to_download  );

			$move_result = @rename( $temp_file_path, $new_path );

			if( false === $move_result ) {
				$folder = dirname( $new_path );
				if( @file_exists( $folder ) ) {
					$errors[] =  'Error attempting to move downloaded file. Temp path: ' . $temp_file_path . ' - New Path: ' . $new_path . ' (#103mf)';
				}
				else{
					if( false === @mkdir( $folder, 0755, true ) ) {
						$errors[] =  'Error attempting to create required directory: ' . $folder . ' (#104mf)';
					}
					else {
						$move_result = @rename( $temp_file_path, $new_path );
						if( false === $move_result ) {
							$errors[] =  'Error attempting to move downloaded file. Temp path: ' . $temp_file_path . ' - New Path: ' . $new_path . ' (#105mf)';
						}
					}
				}
			}
		}

		if( ! empty( $errors ) ) {
			$return = array(
				'wpmdb_error'	=> 1,
				'body'			=> implode( '<br />', $errors ) . '<br />'
			);
			echo json_encode( $return );
			exit;
		}

		// not required, just here because we have to return something otherwise the AJAX fails
		$return['success'] = 1;
		echo json_encode( $return );
		exit;
	}

	function process_push_request() {
		$files_to_migrate = $_POST['file_chunk'];

		$upload_dir = $this->uploads_dir();

		$body = '';
		foreach( $files_to_migrate as $file_to_migrate ) {
			$body .= $this->file_to_multipart( $upload_dir . $file_to_migrate );
		}

		$post_args = array(
			'action'	=> 'wpmdbmf_push_request',
			'files'		=> serialize( $files_to_migrate )
		);

		$post_args['sig'] = $this->create_signature( $post_args, $_POST['key'] );

		$body .= $this->array_to_multipart( $post_args );

		$args['body'] = $body;
		$ajax_url = trailingslashit( $_POST['url'] ) . 'wp-admin/admin-ajax.php';
		$response = $this->remote_post( $ajax_url, '', __FUNCTION__, $args );
		$response = $this->verify_remote_post_response( $response );

		echo json_encode( $response );
		exit;
	}

	function respond_to_push_request() {
		$filtered_post = $this->filter_post_elements( $_POST, array( 'action', 'files' ) );
		$filtered_post['files'] = stripslashes( $filtered_post['files'] );
		if ( ! $this->verify_signature( $filtered_post, $this->settings['key'] ) ) {
			$return = array(
				'wpmdb_error' 	=> 1,
				'body'			=> $this->invalid_content_verification_error . ' (#103mf)',
			);
			echo serialize( $return );
			exit;
		}

		if( ! isset( $_FILES['media'] ) ) {
			$return = array(
				'wpmdb_error' 	=> 1,
				'body'			=> '$_FILES is empty, the upload appears to have failed (#106mf)',
			);
			echo serialize( $return );
			exit;
		}

		$upload_dir = $this->uploads_dir();

		$files = $this->diverse_array( $_FILES['media'] );
		$file_paths = unserialize( $filtered_post['files'] );
		$i = 0;
		$errors = array();
		foreach( $files as &$file ) {
			$destination = $upload_dir . $file_paths[$i];
			$folder = dirname( $destination );

			if( false === @file_exists( $folder ) && false === @mkdir( $folder, 0755, true ) ) {
				$errors[] = 'Error attempting to create required directory: ' . $folder . ' (#108mf)';
				++$i;
				continue;
			}

			if( false === @move_uploaded_file( $file['tmp_name'], $destination ) ) {
				$errors[] = sprintf( 'A problem occurred when attempting to move the temp file "%s" to "%s" (#107mf)', $file['tmp_name'], $destination );
			}
			++$i;
		}

		$return = array( 'success' => 1 );
		if( ! empty( $errors ) ) {
			$return = array(
				'wpmdb_error' 	=> 1,
				'body'			=> implode( '<br />', $errors ) . '<br />'
			);
		}
		echo serialize( $return );
		exit;
	}

	function ajax_determine_media_to_migrate() {
		$this->set_time_limit();

		$local_attachments = $this->get_local_attachments();
		$local_media = $this->get_local_media();

		$data = array();
		$data['action'] = 'wpmdbmf_get_remote_media_listing';
		$data['temp_prefix'] = $this->temp_prefix;
		$data['intent'] = $_POST['intent'];
		$data['sig'] = $this->create_signature( $data, $_POST['key'] );
		$ajax_url = trailingslashit( $_POST['url'] ) . 'wp-admin/admin-ajax.php';
		$response = $this->remote_post( $ajax_url, $data, __FUNCTION__ );
		$response = $this->verify_remote_post_response( $response );

		$upload_dir = $this->uploads_dir();

		$remote_attachments = $response['remote_attachments'];
		$remote_media = $response['remote_media'];

		$this->files_to_migrate = array();

		if( $_POST['intent'] == 'pull' ) {
			$this->media_diff( $local_attachments, $remote_attachments, $local_media, $remote_media );
		}
		else {
			$this->media_diff( $remote_attachments, $local_attachments, $remote_media, $local_media );
		}

		$return['files_to_migrate'] = $this->files_to_migrate;
		$return['total_size'] = array_sum( $this->files_to_migrate );
		$return['remote_uploads_url'] = $response['remote_uploads_url'];

		// remove local/remote media if it doesn't exist on the local/remote site
		if( $_POST['remove_local_media'] == '1' ) {
			if( $_POST['intent'] == 'pull' ) {
				$this->remove_local_attachments( $remote_attachments );
			}
			else {
				$data = array();
				$data['action'] = 'wpmdbmf_remove_local_attachments';
				$data['remote_attachments'] = serialize( $local_attachments );
				$data['sig'] = $this->create_signature( $data, $_POST['key'] );
				$ajax_url = trailingslashit( $_POST['url'] ) . 'wp-admin/admin-ajax.php';
				$response = $this->remote_post( $ajax_url, $data, __FUNCTION__ );
				// the response is ignored here (for now) as this is not a critical task
			}
		}

		echo json_encode( $return );
		exit;
	}

	function respond_to_remove_local_attachments() {
		$filtered_post = $this->filter_post_elements( $_POST, array( 'action', 'remote_attachments' ) );
		$filtered_post['remote_attachments'] = stripslashes( $filtered_post['remote_attachments'] );
		if ( ! $this->verify_signature( $filtered_post, $this->settings['key'] ) ) {
			$return = array(
				'wpmdb_error' 	=> 1,
				'body'			=> $this->invalid_content_verification_error . ' (#109mf)',
			);
			echo serialize( $return );
			exit;
		}

		$remote_attachments = @unserialize( $filtered_post['remote_attachments'] );
		if( false === $remote_attachments ) {
			$return = array(
				'wpmdb_error' 	=> 1,
				'body'			=> 'Error attempting to unserialize the remote attachment data (#110mf)',
			);
			echo serialize( $return );
			exit;
		}

		$this->remove_local_attachments( $remote_attachments );

		$return = array(
			'success' 	=> 1,
		);
		echo serialize( $return );
		exit;
	}

	function remove_local_attachments( $remote_attachments ) {
		$flat_remote_attachments = array_flip( $this->get_flat_attachments( $remote_attachments ) );
		$local_media = $this->get_local_media();
		// remove local media if it doesn't exist on the remote site
		$temp_local_media = array_keys( $local_media );
		$allowed_mime_types = array_flip( get_allowed_mime_types() );
		$upload_dir = $this->uploads_dir();
		foreach( $temp_local_media as $local_media_file ) {
			// don't remove folders
			if( false === is_file( $upload_dir . $local_media_file ) ) continue;
			$filetype = wp_check_filetype( $local_media_file );
			// don't remove files that we shouldn't remove, e.g. .php, .sql, etc
			if( false === isset( $allowed_mime_types[$filetype['type']] ) ) continue;
			// don't remove files that exist on the remote site
			if( true === isset( $flat_remote_attachments[$local_media_file] ) ) continue;
			
			@unlink( $upload_dir . $local_media_file );
		}
	}

	function media_diff( $site_a_attachments, $site_b_attachments, $site_a_media, $site_b_media ) {
		foreach( $site_b_attachments as $attachment ) {
			$local_attachment_key = $this->multidimensional_search( array( 'file' => $attachment['file'] ), $site_a_attachments );
			if( false === $local_attachment_key ) continue;
			$remote_timestamp = strtotime( $attachment['date'] );
			$local_timestamp = strtotime( $site_a_attachments[$local_attachment_key]['date'] );
			if( $local_timestamp >= $remote_timestamp ) {
				if( ! isset( $site_a_media[$attachment['file']] ) ) {
					$this->add_files_to_migrate( $attachment, $site_b_media );
				}
				else {
					$this->maybe_add_resized_images( $attachment, $site_b_media, $site_a_media );
				}
			}
			else {
				$this->add_files_to_migrate( $attachment, $site_b_media );
			}
		}
	}

	function add_files_to_migrate( $attachment, $remote_media ) {
		if( isset( $remote_media[$attachment['file']] ) ) {
			$this->files_to_migrate[$attachment['file']] = $remote_media[$attachment['file']];
		}
		if( empty( $attachment['sizes'] ) ) return;
		foreach( $attachment['sizes'] as $size ) {
			if( isset( $remote_media[$size] ) ) {
				$this->files_to_migrate[$size] = $remote_media[$size];
			}
		}
	}

	function maybe_add_resized_images( $attachment, $site_b_media, $site_a_media ) {
		if( empty( $attachment['sizes'] ) ) return;
		foreach( $attachment['sizes'] as $size ) {
			if( isset( $site_b_media[$size] ) && ! isset( $site_a_media[$size] ) ) {
				$this->files_to_migrate[$size] = $site_b_media[$size];
			}
		}
	}

	function respond_to_get_remote_media_listing() {
		$filtered_post = $this->filter_post_elements( $_POST, array( 'action', 'temp_prefix', 'intent' ) );
		if ( ! $this->verify_signature( $filtered_post, $this->settings['key'] ) ) {
			$return = array(
				'wpmdb_error' 	=> 1,
				'body'			=> $this->invalid_content_verification_error . ' (#100mf)',
			);
			echo serialize( $return );
			exit;
		}

		if( defined( 'UPLOADBLOGSDIR' ) ) {
			$upload_url = home_url( UPLOADBLOGSDIR );
		}
		else {
			$upload_dir = wp_upload_dir();
			$upload_url = $upload_dir['baseurl'];
		}

		$this->responding_to_get_remote_media_listing = true;

		$return['remote_attachments'] = $this->get_local_attachments();
		$return['remote_media'] = $this->get_local_media();
		$return['remote_uploads_url'] = $upload_url;

		echo serialize( $return );
		exit;
	}

	function migration_form_controls() {
		$this->template( 'migrate' );
	}

	function accepted_profile_fields( $profile_fields ) {
		$profile_fields[] = 'media_files';
		$profile_fields[] = 'remove_local_media';
		return $profile_fields;
	}

	function load_assets() {
		$plugins_url = trailingslashit( plugins_url() ) . trailingslashit( $this->plugin_slug );
		$src = $plugins_url . 'asset/js/script.js';
		$version = defined('SCRIPT_DEBUG') && SCRIPT_DEBUG ? time() : $this->get_installed_version();
		wp_enqueue_script( 'wp-migrate-db-pro-media-files-script', $src, array( 'jquery' ), $version, true );
	}

	function establish_remote_connection_data( $data ) {
		$data['media_files_available'] = '1';
		$data['media_files_version'] = $this->get_installed_version();
		if( function_exists( 'ini_get' ) ) {
			$max_file_uploads = ini_get( 'max_file_uploads' );
		}
		$max_file_uploads = ( empty( $max_file_uploads ) ) ? 20 : $max_file_uploads;
		$data['media_files_max_file_uploads'] = apply_filters( 'wpmdbmf_max_file_uploads', $max_file_uploads );
		return $data;
	}

	function multidimensional_search( $needle, $haystack ) {
		if( empty( $needle ) || empty( $haystack ) ) return false;

		foreach( $haystack as $key => $value ) {
			foreach ( $needle as $skey => $svalue ) {
				$exists = ( isset( $haystack[$key][$skey] ) && $haystack[$key][$skey] === $svalue );
			}
			if( $exists ) return $key;
		}

		return false;
	}

	function get_blogs() { 
		global $wpdb;

		$blogs = $wpdb->get_results(
			"SELECT blog_id
			FROM {$wpdb->blogs}
			WHERE site_id = '{$wpdb->siteid}'
			AND spam = '0'
			AND deleted = '0'
			AND archived = '0'
			AND blog_id != 1
		");

		$clean_blogs = array();
		foreach( $blogs as $blog ) {
			$clean_blogs[] = $blog->blog_id;
		}

		return $clean_blogs;
	}

	function download_url( $url, $timeout = 300 ) {
		//WARNING: The file is not automatically deleted, The script must unlink() the file.
		if ( ! $url )
			return new WP_Error('http_no_url', __('Invalid URL Provided.'));

		$tmpfname = wp_tempnam($url);
		if ( ! $tmpfname )
			return new WP_Error('http_no_file', __('Could not create Temporary file.'));

		$response = wp_remote_get( $url, array( 'timeout' => $timeout, 'stream' => true, 'filename' => $tmpfname, 'reject_unsafe_urls' => false ) );

		if ( is_wp_error( $response ) ) {
			unlink( $tmpfname );
			return $response;
		}

		if ( 200 != wp_remote_retrieve_response_code( $response ) ){
			unlink( $tmpfname );
			return new WP_Error( 'http_404', trim( wp_remote_retrieve_response_message( $response ) ) );
		}

		return $tmpfname;
	}

	function js_variables() {
		?>
		var wpmdb_media_files_version = '<?php echo $this->get_installed_version(); ?>';
		<?php
	}

	function verify_remote_post_response( $response ) {
		if ( false === $response ) {
			$return = array( 'wpmdb_error' => 1, 'body' => $this->error );
			echo json_encode( $return );
			exit;
		}

		if ( ! is_serialized( trim( $response ) ) ) {
			$return = array( 'wpmdb_error'	=> 1, 'body' => $response );
			echo json_encode( $return );
			exit;
		}

		$response = unserialize( trim( $response ) );

		if ( isset( $response['wpmdb_error'] ) ) {
			echo json_encode( $response );
			exit;
		}

		return $response;
	}

}