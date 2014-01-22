<?php
if ( !class_exists('Thumbnail_Crop_Position') ):
class Thumbnail_Crop_Position {

	const version = '1.3';
	static $plugin;
	static $plugin_url;

	//Initialize
	function __construct( $plugin = '' ) {
		if ( !$plugin )
			return;

		self::$plugin     = $plugin;
		self::$plugin_url = plugin_dir_url($plugin);

		load_plugin_textdomain( 'thumb_crop_position', false, dirname( plugin_basename( $plugin ) ) . '/languages/' );

		// Action and filter hooks
		global $pagenow;
		if ($pagenow == 'media-new.php')
			add_action( 'post-upload-ui', array( $this, 'uploader_controls' ) );
		else
			add_action( 'pre-upload-ui', array( $this, 'uploader_controls' ) );

		add_action( 'pre-plupload-upload-ui', array( $this, 'uploader_scripts_styles' ) );
		add_action( 'wp_ajax_tcp', array( $this, 'ajax_callback' ) );
		add_filter( 'image_resize_dimensions', array( $this, 'image_resize_dimensions' ), 10, 6 );
	}


	//load scripts and styles only in uploader
	function uploader_scripts_styles() {
		$suffix = defined('SCRIPT_DEBUG') && SCRIPT_DEBUG ? '' : '.min';
		wp_enqueue_style( 'thumb_crop_position', self::$plugin_url.'css/styles'.$suffix.'.css', false, self::version, 'all' );
		wp_enqueue_script( 'thumb_crop_position_js', self::$plugin_url.'js/scripts'.$suffix.'.js', array('jquery'), self::version, true );
		wp_localize_script( 'thumb_crop_position_js', 'tcpL10n', array('_wpnonce' => wp_create_nonce('tcp_update-position')) );
	}


	// ajax response to update thumb_crop_position_option
	function ajax_callback() {
		if ( empty($_REQUEST['_wpnonce']) || !check_ajax_referer( 'tcp_update-position', '_wpnonce', false ) || !current_user_can('upload_files') ) {
			echo -1;
			die();
		}
		$pos = !empty($_POST['ajax_position_option']) ? min( 8, absint( $_POST['ajax_position_option'] ) ) : 4;
		update_option( 'thumb_crop_position_option', array( 'position' => $pos ) );
		echo $pos;
		die();
	}


	// Shorthand to get the position option
	function get_position() {
		$options = get_option( 'thumb_crop_position_option' );
		return is_array($options) && isset($options['position']) ? min( 8, absint( $options['position'] ) ) : 4;
	}


	//write html controls
	function uploader_controls() {

		$pos = self::get_position();
		?>
		<div class="thumbnail-crop-position">
			<p><?php _e( 'Select thumbnail crop position:', 'thumb_crop_position' );?></p>
			<div class="tcp-controls">

				<div class="button button-hero<?php echo $pos == 0 ? ' button-primary' : ''; ?>">
					<input id="ci-0" class="screen-reader-text" name="position" type="radio" value="0"<?php checked( 0, $pos ); ?> />
					<label for="ci-0" class="ci-0"><?php echo __('Top') .'-'. __('Left'); ?></label>
				</div>
				<div class="button button-hero<?php echo $pos == 1 ? ' button-primary' : ''; ?>">
					<input id="ci-1" class="screen-reader-text" name="position" type="radio" value="1"<?php checked( 1, $pos ); ?> />
					<label for="ci-1" class="ci-1"><?php echo __('Top') .'-'. __('Center'); ?></label>
				</div>
				<div class="button button-hero<?php echo $pos == 2 ? ' button-primary' : ''; ?>">
					<input id="ci-2" class="screen-reader-text" name="position" type="radio" value="2"<?php checked( 2, $pos ); ?> />
					<label for="ci-2" class="ci-2"><?php echo __('Top') .'-'. __('Right'); ?></label>
				</div>

				<div class="button button-hero<?php echo $pos == 3 ? ' button-primary' : ''; ?>">
					<input id="ci-3" class="screen-reader-text" name="position" type="radio" value="3"<?php checked( 3, $pos ); ?> />
					<label for="ci-3" class="ci-3"><?php echo __('Center') .'-'. __('Left'); ?></label>
				</div>
				<div class="button button-hero<?php echo $pos == 4 ? ' button-primary' : ''; ?>">
					<input id="ci-4" class="screen-reader-text" name="position" type="radio" value="4"<?php checked( 4, $pos ); ?> />
					<label for="ci-4" class="ci-4"><?php echo __('Center') .'-'. __('Center'); ?></label>
				</div>
				<div class="button button-hero<?php echo $pos == 5 ? ' button-primary' : ''; ?>">
					<input id="ci-5" class="screen-reader-text" name="position" type="radio" value="5"<?php checked( 5, $pos ); ?> />
					<label for="ci-5" class="ci-5"><?php echo __('Center') .'-'. __('Right'); ?></label>
				</div>

				<div class="button button-hero<?php echo $pos == 6 ? ' button-primary' : ''; ?>">
					<input id="ci-6" class="screen-reader-text" name="position" type="radio" value="6"<?php checked( 6, $pos ); ?> />
					<label for="ci-6" class="ci-6"><?php echo __('Bottom') .'-'. __('Left'); ?></label>
				</div>
				<div class="button button-hero<?php echo $pos == 7 ? ' button-primary' : ''; ?>">
					<input id="ci-7" class="screen-reader-text" name="position" type="radio" value="7"<?php checked( 7, $pos ); ?> />
					<label for="ci-7" class="ci-7"><?php echo __('Bottom') .'-'. __('Center'); ?></label>
				</div>
				<div class="button button-hero<?php echo $pos == 8 ? ' button-primary' : ''; ?>">
					<input id="ci-8" class="screen-reader-text" name="position" type="radio" value="8"<?php checked( 8, $pos ); ?> />
					<label for="ci-8" class="ci-8"><?php echo __('Bottom') .'-'. __('Right'); ?></label>
				</div>

			</div>
		</div>
	<?php
	}


	//hook for custom thumbnail crop position
	//reference: http://codex.wordpress.org/Plugin_API/Filter_Reference/image_resize_dimensions
	//---------------------------------------
	function image_resize_dimensions( $payload, $orig_w, $orig_h, $dest_w, $dest_h, $crop) {

		// Change this to a conditional that decides whether you
		// want to override the defaults for this image or not.;
		if( false )
			return $payload;

		// crop the largest possible portion of the original image that we can size to $dest_w x $dest_h
		if ( $crop ) {
			$aspect_ratio = $orig_w / $orig_h;
			$new_w = min($dest_w, $orig_w);
			$new_h = min($dest_h, $orig_h);

			$new_w = $new_w ? $new_w : intval($new_h * $aspect_ratio);
			$new_h = $new_h ? $new_h : intval($new_w / $aspect_ratio);

			$size_ratio = max($new_w / $orig_w, $new_h / $orig_h);

			$crop_w = round($new_w / $size_ratio);
			$crop_h = round($new_h / $size_ratio);


			// positions
			$s_x_left = 0;
			$s_x_center = floor( ($orig_w - $crop_w) / 2 );
			$s_x_right = floor( ($orig_w - $crop_w) );

			$s_y_top = 0;
			$s_y_center = floor( ($orig_h - $crop_h) / 2 );
			$s_y_bottom = floor( ($orig_h - $crop_h) );

			$pos = self::get_position();

			//row 1
			if ( $pos == 0 ) {
				$s_x = $s_x_left;
				$s_y = $s_y_top;
			}
			elseif ( $pos == 1 ) {
				$s_x = $s_x_center;
				$s_y = $s_y_top;
			}
			elseif ( $pos == 2 ) {
				$s_x = $s_x_right;
				$s_y = $s_y_top;
			}

			//row 2
			elseif ( $pos == 3 ) {
				$s_x = $s_x_left;
				$s_y = $s_y_center;
			}
			elseif ( $pos == 4 ) {
				$s_x = $s_x_center;
				$s_y = $s_y_center;
			}
			elseif ( $pos == 5 ) {
				$s_x = $s_x_right;
				$s_y = $s_y_center;
			}

			//row 3
			elseif ( $pos == 6 ) {
				$s_x = $s_x_left;
				$s_y = $s_y_bottom;
			} elseif ( $pos == 7 ) {
				$s_x = $s_x_center;
				$s_y = $s_y_bottom;
			} elseif ( $pos == 8 ) {
				$s_x = $s_x_right;
				$s_y = $s_y_bottom;
			}

			// for security
			else {
				$s_x = $s_x_center;
				$s_y = $s_y_center;
			}

		// don't crop, just resize using $dest_w x $dest_h as a maximum bounding box
		} else {
			$crop_w = $orig_w;
			$crop_h = $orig_h;

			$s_x = 0;
			$s_y = 0;

			list( $new_w, $new_h ) = wp_constrain_dimensions( $orig_w, $orig_h, $dest_w, $dest_h );
		}

		// if the resulting image would be the same size or larger we don't want to resize it
		if ( $new_w >= $orig_w && $new_h >= $orig_h )
			return false;

		// the return array matches the parameters to imagecopyresampled()
		// int dst_x, int dst_y, int src_x, int src_y, int dst_w, int dst_h, int src_w, int src_h
		return array( 0, 0, (int) $s_x, (int) $s_y, (int) $new_w, (int) $new_h, (int) $crop_w, (int) $crop_h );

	}

}
endif;