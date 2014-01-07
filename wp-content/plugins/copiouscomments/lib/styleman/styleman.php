<?php
/*
 * PluginBuddy.com
 * Created: June 10, 2010
 * Updated: September 21, 2010
 * Iteration: 2
 *
 */

if (!class_exists("PluginBuddy_styleman")) {
    class PluginBuddy_styleman {
		function PluginBuddy_styleman(&$parent) {
			$this->_parent = &$parent;
			if ( !isset( $this->_parent->_var ) ) {
				$this->_parent->_var = $this->_parent->_parent->_var;
				$this->_parent->_selfLink = $this->_parent->_parent->_selfLink;
				$this->_parent->_pluginURL = $this->_parent->_parent->_pluginURL;
			}
		}
		
		
		
		
		function styleman( $style_definitions_file, $style_file, $custom_style_file, &$enabled_var ) {
			//error_reporting(E_ALL ^ E_NOTICE);
			
			wp_enqueue_script( 'jpicker', $this->_parent->_pluginURL . '/js/jpicker.js' );
			wp_print_scripts( 'jpicker' );
			echo '<link rel="stylesheet" href="'.$this->_parent->_pluginURL . '/css/jpicker.css" type="text/css" media="all" />';	
			echo '<script type="text/javascript">';
			echo 'jQuery(document).ready(function() {';
			echo "jQuery('.jpicker').jPicker( { window: { expandable: true, alphaSupport: false, effects: { type: 'fade' } }, images : {  clientPath: '" . $this->_parent->_pluginURL . "/images/' } });";
			echo '});';
			echo '</script>';
			
			if ( isset( $_POST['disable_customstyles'] ) ) {
				//$this->_parent->_themeoptions['customstyles_enabled'] = false;
				$enabled_var = false;
				if ( is_callable( $this->_parent, 'save' ) ) { // Call save in parent if its callable there.  In case we are two classes in, we will need to go up two levels.
					$this->_parent->save();
				} else {
					$this->_parent->_parent->save();
				}
				$this->_showStatusMessage( 'Custom styles disabled.' );
			}
			if ( isset( $_POST['enable_customstyles'] ) ) {
				//$this->_parent->_themeoptions['customstyles_enabled'] = true;
				$enabled_var = true;
				if ( is_callable( $this->_parent, 'save' ) ) { // Call save in parent if its callable there.  In case we are two classes in, we will need to go up two levels.
					$this->_parent->save();
				} else {
					$this->_parent->_parent->save();
				}
				$this->_showStatusMessage( 'Custom styles enabled.' );
			}
		
			if ( isset( $_POST['save_styles'] ) ) {

				
				//echo '<pre>';
				//print_r( $_POST );
				//echo '</pre>';
				
			
				//echo '<pre>';
				$last_select = '';
				$block_top = '';
				$block_inner = '';
				$final_css = '';
				foreach ($_POST as $line_key => $line) {
					$line_key = str_replace( '_', '.', $line_key );
					$line_key = urldecode( $line_key );
					if ( strstr( $line_key, ',' ) ) {
						$selector = explode( ',', $line_key );
						if ( $selector[0] != $last_select ) {
							if ( $last_select != '' ) {
								if ( $block_inner != '' ) { // Only print this selector if it has stuff inside its block.
									$final_css .= $block_top;
									$final_css .= $block_inner;
									$final_css .= "}\r\n\r\n";
								}
								$block_top = '';
								$block_inner = '';
							}
							$block_top .= str_replace( '_', ' ', $selector[0]) . ' {' . "\r\n";
							$last_select = $selector[0];
						}
						if ( $line != '' ) {
							$block_inner .= "\t" . $selector[1] . ': ';
							if ( ( ( $selector[1] == 'color' ) || ( $selector[1] == 'background' ) || ( $selector[1] == 'border-color' ) ) && ( substr($line, 0, 1) != '#' ) ) {
								$block_inner .= '#';
							}
							if ( ( $selector[1] == 'font-family' ) && strstr($line, ' ') ) {
								$line = '"' . $line . '"';
							}
							$block_inner .= $line . ";\r\n";
						}
					}
				}
				//echo "}\n";
				//echo '</pre>';
				
				// Add the last item
				$final_css .= $block_top;
				$final_css .= $block_inner;
				$final_css .= "}\r\n\r\n";
				
				if ( !file_exists( dirname( $custom_style_file ) ) ) {
					// mkdir( dir, permissions, recursive_boolean );
					@mkdir( dirname( $custom_style_file ), 0755, true ) or die( 'Error #3489721. Cannot create directory to write custom style file Check permissions: ' . dirname( $custom_style_file ) );
				}
				$fh = @fopen( $custom_style_file, 'w' ) or die( 'Error #6545443443. Cannot write to custom style file Check permissions: ' . $custom_style_file );
				fwrite($fh, $final_css);
				fclose($fh);
				
				//$this->_parent->_themeoptions['customstyles_enabled'] = true;
				$enabled_var = true;
				if ( is_callable( $this->_parent, 'save' ) ) { // Call save in parent if its callable there.  In case we are two classes in, we will need to go up two levels.
					$this->_parent->save();
				} else {
					$this->_parent->_parent->save();
				}
				
				$this->_showStatusMessage( 'Your styles have been saved.' );

			}
				
			
			//echo $style_definitions_file;
			if ( !file_exists( $style_definitions_file ) ) {
				echo '<td colspan="2">ALERT! Style Manager is unavailable for this theme. Ask the developer to create a style_definitions.txt file.</td>';
				} else {
					?>
		
						<form method="post" action="<?php echo $this->_parent->_selfLink; ?>-settings">
						
							<table class="form-table">
								<tr>
									<td colspan="2">
										<h3>Style Manager</h3>
					<?php
					
					
					if ( isset( $_GET['developer'] ) ) {
						echo '<h3>Theme Settings:</h3><pre>';
						print_r( $this->_parent->_options['themeoptions'] );
						echo '</pre>';
					}
					
					
					
					
					
					
					
					
					
					
					
					
					$css = $this->get_css( $style_file ); // Get style.css styles
					if ( file_exists( $custom_style_file ) ) {
						$custom_css = $this->get_css( $custom_style_file ); // Get custom styles
						if ( is_array( $custom_css ) ) {
							$css = array_merge( $css, $custom_css ); // Merge custom over the normal styles
						}
					}
					
					
					
					
					function style_color($item,$css) {
						//echo 'yo'.$item[0] . '=' . $item[1];
						$return = '';
						$return .= '<input type="text" name="' . urlencode($item[0] . ',' . $item[1]) . '" value="';
						if ( isset( $css[$item[0]][$item[1]] ) ) {
							$return .= $css[$item[0]][$item[1]];
						}
						$return .= '" class="jpicker" />';
						//$return .= '<span class="jpicker"></span>';
						return $return;
					}
					// $negative_values		boolean		Set to true to allow for zero and negative values to be generated.
					function style_pixel_size( $item, $css, $negative_values = false ) {
						$return = '';
						
						$pixelsizes[0] = '';
						if ( $negative_values === true ) {
							$c = -21;
						} else {
							$c = 0;
						}
						
						while ( $c <= 45 ) {
							$c++;
							$pixelsizes[$c] = $c . 'px';
						}
						$return .= '<select name="' . urlencode($item[0] . ',' . $item[1]) . '">';
						foreach ( $pixelsizes as $pixelsize ) {
							$return .= '<option value="' . $pixelsize . '"';
							if ( isset( $css[$item[0]][$item[1]] ) && ( str_replace( '"', '', $css[$item[0]][$item[1]]) == $pixelsize ) ) {
								$return .= ' selected';
							}
							$return .= '>' . $pixelsize . '</option>';
						}
						$return .= '</select>';
						
						return $return;
					}
					function style_font_family($item,$css) {
						$return = '';
						$fonts = Array(
							'',
							'American Typewriter',
							'American Typewriter Condensed',
							'Arial',
							'Arial Rounded MT Bold',
							'Courier New',
							'Georgia',
							'Helvetica',
							'Marker Felt',
							'Times New Roman',
							'Trebuchet MS',
							'Verdana',
							'Zapfino',
						);
						$return .= '<select name="' . urlencode($item[0] . ',' . $item[1]) . '">';
						foreach ( $fonts as $font ) {
							$return .= '<option value="' . $font . '"';
							if ( isset( $css[$item[0]][$item[1]] ) && ( str_replace( '"', '', $css[$item[0]][$item[1]]) == $font ) ) {
								$return .= ' selected';
							}
							$return .= '>' . $font . '</option>';
						}
						$return .= '</select>';
						
						return $return;
					}



					
					if ( isset( $_GET['developer'] ) ) {
						echo '<h3>CSS:</h3><pre>';
						print_r( $css );
						echo '</pre>';
					}



					$style_file = explode("\n", file_get_contents( $style_definitions_file ) );
					foreach ( (array) $style_file as $item ) {
						//if ( (substr($item, 0, 2) != '//') && ($item != '') ) { // Ignore commented and blank lines.
						if ( strstr( $item, ',' ) ) {
							//echo $item.'wuzhere<br />';
							
							$item = explode( ",", $item );
							//echo $item[1] . '<br />';
							echo '<tr>';
							
							if ( $item[0] == '-' ) {
								echo '<td colspan="2"><b>' . $item[1] . '</b></td>';
							} elseif ( $item[1] == 'background' ) {
								echo '<td>' . $item[2] . '</td>';
								echo '<td>' . style_color($item,$css) . '</td>';
							} elseif ( $item[1] == 'font-size' ) {
								echo '<td>' . $item[2] . '</td>';
								echo '<td>' . style_pixel_size($item,$css) . '</td>';
							} elseif ( $item[1] == 'border-width' ) {
								echo '<td>' . $item[2] . '</td>';
								echo '<td>' . style_pixel_size($item,$css) . '</td>';
							} elseif ( $item[1] == 'top' ) {
								echo '<td>' . $item[2] . '</td>';
								echo '<td>' . style_pixel_size($item,$css, true) . '</td>';
							} elseif ( $item[1] == 'font-family' ) {
								echo '<td>' . $item[2] . '</td>';
								echo '<td>' . style_font_family($item,$css) . '</td>';
							} elseif ( $item[1] == 'color' ) {
								echo '<td>' . $item[2] . '</td>';
								echo '<td>' . style_color($item,$css) . '</td>';
							} elseif ( $item[1] == 'border-color' ) {
								echo '<td>' . $item[2] . '</td>';
								echo '<td>' . style_color($item,$css) . '</td>';
							} elseif ( $item[1] == 'border-color' ) {
								echo '<td>' . $item[2] . '</td>';
								echo '<td>' . style_color($item,$css) . '</td>';
							}
							
							echo '</tr>';
						}
					}
					unset($style_file);
				}
			?>
			</table>
			<p class="submit"><input value="Save Styles" type="submit" name="save_styles" class="button-primary" id="save_styles" /></p>
			<?php //$this->_addUsedInputs(); ?>
			<?php wp_nonce_field( $this->_parent->_var . '-nonce' ); ?>
			</form>
			
			
			
			<?php
		}
		
		
		
				// Reads a CSS file and puts it into an array.  Key is the selector path and value is the contents.
		function get_css( $style_file ) {
			$style_file = file_get_contents( $style_file );

			$style_file = preg_replace('/(\/\*[\s\S]*?\*\/)/', '', $style_file); 

			//echo $style_file;

			$style_lines = explode("\n", $style_file );


			$cssstyles = '';
			foreach ($style_lines as $line_num => $line) {
				$cssstyles .= trim($line);
			}
			$tok = strtok($cssstyles, "{}"); // remove brackets. p{color:#000000;} -> p color:#000000;
			$sarray = array();
			$spos = 0;
			while ($tok !== false) { //separating selectors from styles and store those values in the $sarray
				$sarray[$spos] = $tok;
				$spos++; 
				$tok = strtok("{}");
			}
			$size = count($sarray);
			$selectors = array();
			$sstyles = array();

			$npos = 0;
			$sstl = 0;

			for($i = 0; $i<$size; $i++){ // separate styles from selectors
				if ($i % 2 == 0) {
					$selectors[$npos] = trim( $sarray[$i] );
					$npos++;
				} else {
					$sstyles[$sstl] = trim( $sarray[$i] );
					$sstl++;
				}
			}

			foreach ($selectors as $selector => $selector_val) {

				$css[$selector_val] = Array();
				$styles = explode(';', $sstyles[$selector]); // Put all the styling for this selector in an array.
				
				foreach ($styles as $style) { // Loop through array to assign each to their own array index for easy access.
					if ( $style != '' ) {
						$style_pair = explode(':', $style );
						$css[$selector_val][ $style_pair[0] ] = trim( $style_pair[1] );
					}
				}
			}
			
			return $css;
		}
		
		function _showStatusMessage( $message ) {
			echo '<div id="message" class="updated fade"><p><strong>'.$message.'</strong></p></div>';			
		}
		function _showErrorMessage( $message ) {
			echo '<div id="message" class="error"><p><strong>'.$message.'</strong></p></div>';
		}
	}
	$pbstyles = new PluginBuddy_styleman( $this );
}
?>