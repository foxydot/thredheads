<?php
/**
 *
 * Plugin Name: Rotating Text
 * Plugin URI: http://pluginbuddy.com/purchase/diplaybuddy/
 * Description: DisplayBuddy Series - A plugin that rotates text using animation to fade the text in and out.
 * Version: 1.0.13
 * Author: The PluginBuddy Team
 * Author URI: http://pluginbuddy.com/
 *
 * Installation:
 * 
 * 1. Download and unzip the latest release zip file.
 * 2. If you use the WordPress plugin uploader to install this plugin skip to step 4.
 * 3. Upload the entire rotating-text directory to your `/wp-content/plugins/` directory.
 * 4. Activate the plugin through the 'Plugins' menu in WordPress Administration.
 * 
 * Usage:
 * 
 * 1. Navigate to the Rotating Text menu in the Wordpress Administration Panel.
 * 2. Create a group.
 * 3. Click a group name to customize the group and add/customize entries.
 * 4. Add rotating-text by adding to widget areas or use shortcode.
 *
 */


if (!class_exists("rotatingtext")) {
    class rotatingtext {
		var $_version = '1.0.13';
		var $_updater = '1.0.7';
		var $_var = 'rotatingtext';
		var $_series = 'DisplayBuddy';
		var $_name = 'rotating-text';
		var $_timeformat = '%b %e, %Y, %l:%i%p';	// mysql time format
		var $_timestamp = 'M j, Y, g:iA';		// php timestamp format
		var $_usedInputs = array();
		var $_pluginPath = '';
		var $_pluginRelativePath = '';
		var $_pluginURL = '';
		var $_selfLink = '';
		var $_defaults = array(
			'groups'	=>	array(),
		);
		var $_options = array();
		var $_groupdefaults = array(
			'entries'		=>	array(),
			'order'			=>	array(),
			'random'		=>	'false',
			'fadein'		=>	'1800',
			'fadedisplay'		=>	'1080',
			'fadeout'		=>	'1800',
			'between'		=>	'400',
			'background-color'	=>	'FFFFFF',
			'transparent'		=>	'0',
			'width'			=>	'100',
			'auto-width'		=>	'0',
			'height'		=>	'100',
			'auto-height'		=>	'0',
			'horizontal'		=>	'left',
			'vertical'		=>	'top',
			'font-size'		=>	'inherit',
			'font-family'		=>	'inherit',
			'font-color'		=>	'inherit'
		);
		var $_entrydefaults = array(
			'font-size'	=>	'inherit',
			'font-color'	=>	'inherit',
			'font-family'	=>	'inherit'
		);
		var $_instance = '';

		
		/**
		 * rotatingtext()
		 *
		 * Default Constructor
		 *
		 */
        function rotatingtext() {
		$this->_pluginPath = dirname( __FILE__ );
		$this->_pluginRelativePath = ltrim( str_replace( '\\', '/', str_replace( rtrim( ABSPATH, '\\\/' ), '', $this->_pluginPath ) ), '\\\/' );
		$this->_pluginURL = get_option( 'siteurl' ) . '/' . $this->_pluginRelativePath;
		$this->_selfLink = array_shift( explode( '?', $_SERVER['REQUEST_URI'] ) ) . '?page=' . $this->_var;
		
		// Admin.
		if ( is_admin() ) {
			add_action( 'init', array( &$this, 'upgrader_register' ), 50 );
			add_action( 'init', array( &$this, 'upgrader_select' ), 100 );
			add_action( 'init', array( &$this, 'upgrader_instantiate' ), 101 );
			add_action('admin_menu', array(&$this, 'admin_menu')); // Add menu in admin.
			add_action('admin_init', array(&$this, 'init_admin' )); // Run on admin initialization.
			require_once( $this->_pluginPath . '/lib/updater/updater.php');
			// When user activates plugin in plugin menu.
			register_activation_hook( $this->_pluginPath, array( &$this, 'activate' ) );
		} else { // Non-Admin.
			add_action('template_redirect', array(&$this, 'init_public'));
			add_shortcode('rotatingtext', array( &$this, 'shortcode' ) );
			add_action($this->_var.'-widget', array( &$this, 'widget' ), 10, 2 ); // Add action to run widget function.
			add_action('wp_print_scripts', array( &$this, 'rt_scripts' ) );
			add_action('wp_print_styles', array( &$this, 'rt_styles' ) );
		}
	}
	
	// FUNCTIONS TO CALL FRONT END SCRIPTS & STYLES
	function rt_scripts() {
		wp_enqueue_script('jquery');
		wp_enqueue_script('rotatingtext_script', $this->_pluginURL . "/js/rotatingtext.js");
	}
	function rt_styles() {
		wp_enqueue_style('rotatingtext_style', $this->_pluginURL . "/css/rotatingtext.css");
	}
		
	/**
	 * rotatingtext::_activate()
	 *
	 * Run on plugin activation.
	 *
	 */
	function activate() {
	}
	
	
	/**
	 * rotatingtext::init_admin()
	 *
	 * Run on admin load.
	 *
	 */
	function init_admin() {
	}
	
	
	/**
	 * rotatingtext::init_public()
	 *
	 * Run on on public load.
	 *
	 */
	function init_public() {
	}

	/**
	 * TOOLTIP FUNCTION
	 * Displays a message to the user when they hover over the question mark.
	**/
	function tip( $message, $title = '', $echo_tip = true ) {
		$tip = ' <a class="pluginbuddy_tip" title="' . $title . ' - ' . $message . '"><img src="' . $this->_pluginURL . '/images/pluginbuddy_tip.png" alt="(?)" /></a>';
		if ( $echo_tip === true ) {
			echo $tip;
		} else {
			return $tip;
		}
	}		
	
	
	// PAGES //////////////////////////////

	/**
	 * rotatingtext::view_index()
	 *
	 * Displays default plugin page.
	 *
	 */		
	function view_gettingstarted() {
		echo '<link rel="stylesheet" href="' . $this->_pluginURL . '/css/admin.css" type="text/css" media="all" />';
		require('classes/view_gettingstarted.php');
	}
	
	/**
	 * rotatingtext::view_settings()
	 *
	 * Displays settings form and values for viewing & editing.
	 *
	 */		
	function view_settings() {
		$this->load();

		if (!empty( $_POST['add_group'])) {
			$this->_groupsCreate();
		}
		
		if (!empty( $_POST['group_settings'])) {
			$this->_groupSettings();
		}

		if (!empty( $_POST['delete_groups'])) {
			$this->_groupsDelete();
		}

		if (!empty($_POST['add_entry'])) {
			$this->_entriesCreate();
		}

		if (!empty( $_POST['update'])) {
			$this->_updateEntry();
		}

		if (!empty( $_POST['delete_entries'])) {
			$this->_entriesDelete();
		}
		
		if( !empty($_POST['save_order'])) {
			$this->_saveOrder();
		}

		// Load scripts and CSS used on this page.
		wp_enqueue_script( 'jquery' );
		wp_print_scripts('jquery');
		wp_enqueue_script( 'ithemes-tooltip-js', $this->_pluginURL . '/js/tooltip.js' );
		wp_print_scripts( 'ithemes-tooltip-js' );
		wp_enqueue_script( 'ithemes-'.$this->_var.'-admin-js', $this->_pluginURL . '/js/admin.js' );
		wp_print_scripts( 'ithemes-'.$this->_var.'-admin-js' );
		wp_enqueue_script('jpicker-js', $this->_pluginURL . '/js/jpicker.js' );
		wp_print_scripts('jpicker-js');
		wp_enqueue_script('tablednd-js', $this->_pluginURL . '/js/jquery.tablednd_0_5.js' );
		wp_print_scripts('tablednd-js');

		echo '<link rel="stylesheet" href="' . $this->_pluginURL . '/colorpicker/css/colorpicker.css" media="all" />';
		echo '<link rel="stylesheet" href="' . $this->_pluginURL . '/css/admin.css" type="text/css" media="all" />';
		echo '<link rel="stylesheet" href="' . $this->_pluginURL . '/css/jpicker.css" type="text/css" media="all" />';
		?>
		<!-- order rows javascript -->
		<script type="text/javascript">
			jQuery(document).ready(function() {
				jQuery('#reorder-table').tableDnD({
					onDrop: function(tbody, row) {
						setValue(jQuery.tableDnD.serialize());
					},
					dragHandle: "dragHandle"
				});
			});

			var orderValue;

			function setValue($test) {
			    orderValue = $test;
			}

			function getValue() {
				jQuery( '#hidnorder' ).val( window.orderValue );
			}
		</script>

		<?php
		// color picker javascript
		$colorpicker = "
		<script type='text/javascript'>
			jQuery(document).ready(function() {
				jQuery('.jpicker').jPicker( {
					window: {
						expandable: true,
						alphaSupport: false,
						effects: { type: 'fade' }
					}, 
					images : {  clientPath: '" . $this->_pluginURL . "/images/' }
				});
			});
		</script>
		";
		echo $colorpicker;
		?>
		<?php
		echo '<div class="wrap">';

			if ( isset( $_GET['entry_id'] ) ) {

				echo '<h2>Edit Entry Settings (<a href="'. $this->_selfLink . '-settings&group_id=' . $_GET['group_id'] . '">list of entries</a>)</h2>';
				
				$entry = $this->_options['groups'][$_GET['group_id']]['entries'][$_GET['entry_id']];
				$this->_usedInputs=array();
				?>
				<form method="post" action="<?php echo $this->_selfLink . '-settings&group_id=' . $_GET['group_id']; ?>">
					<table class="form-table">
						<tr>
							<th scope="row">Entry</th>
							<td><?php $this->_addTextArea('entry', array( 'rows' => '6', 'cols' => '42', 'value' => stripslashes($entry['entry']) ) ); ?></td>
						</tr>
						<tr>
							<th scope="row">Link</th>
							<td><?php $this->_addTextBox('link', array( 'size' => '45', 'maxlength' => '45', 'value' => $entry['link'] ) ); ?></td>
						</tr>
						<tr>
							<td>Open URL in New Tab/Window</td>
							<?php
								if (($entry['newtab']) == '1') {
									$checked = array( 'checked' => 'yes', 'value' => '1' );
								}
								else {
									$checked = array( 'value' => '1');
								}
							?>
							<td><?php $this->_addCheckBox('newtab', $checked); ?></td>
						</tr>
						<tr>
							<th scope="row"><strong>Font Styles</strong></th>
						</tr>
						<tr>
							<td>To use default styles leave form field blank.</td>
						</tr>
						<tr>
							<th>Font-size in pixels:</th>
							<td>
								<?php
									if ( $entry['font-size'] == 'inherit') {
										$font_s = '';
									}
									else {
										$font_s = $entry['font-size'];
									}
								 	$this->_addTextBox('font-size', array( 'size' => '3', 'maxlength' => '8', 'value' => $font_s ) );
								?>
							</td>
						</tr>
						<tr>
							<th>Font-Family:</th>
							<td>
								<?php
								$family_options = array(
											'inherit'			  => 'default',
											'Arial, Helvetica, sans-serif'	  => 'Arial',
											'Georgia, Serif'		  => 'Georgia',
											'Verdana, Geneva, sans-serif'	  => 'Verdana',
											'‘Times New Roman’, Times, serif' => 'Times New Roman',
											'Tahoma, Geneva, sans-serif'	  => 'Tahoma',
											'Impact, Charcoal, sans-serif'	  => 'Impact'
										 );
								?>
								<select name="<?php echo $this->_var; ?>-font-family">
									<?php
									foreach( $family_options as $def => $val ) {
										if($entry['font-family'] == $def) {
											echo '<option selected value="' . $def . '">' . $val . '</option selected>';
										}
										else {
											echo '<option value="' . $def . '">' . $val . '</option>';
										}
									}
									?>
								</select>
							</td>
						</tr>
						<tr>
							<th>Font-color:</th>
							<?php
								if ( $entry['font-color'] == 'inherit' ) {
									$font_c = '';
								}
								else {
									$font_c = $entry['font-color'];
								}
							?>
							<td>#<input type="text" maxlength="6" size="6" class="jpicker" name="<?php echo $this->_var; ?>-font-color" value="<?php echo $font_c; ?>" /></td>
						</tr>
						<?php
							$this->_addHidden('groupid', array( 'size' => '45', 'maxlength' => '200', 'value' => $_GET['group_id']));
							$this->_addHidden('entryid', array( 'size' => '45', 'maxlength' => '200', 'value' => $_GET['entry_id']));
						?>
					</table>
					<p class="submit"><?php $this->_addSubmit( 'update', 'Update Entry' ); ?></p>
					<?php $this->_addUsedInputs(); ?>
					<?php wp_nonce_field( $this->_var . '-nonce' ); ?>
				</form>
				<?php
			}
			elseif ( isset( $_GET['group_id'] ) ) {
				echo '<h2>Rotating Text in Group (<a href="'. $this->_selfLink . '-settings">group list</a>)</h2>';
				echo "<h4>Currently editing " . stripslashes($this->_options['groups'][$_GET['group_id']]['name']) . "</h4>";

				$entrynum = count($this->_options['groups'][$_GET['group_id']]['entries']);
				
				if ( $entrynum >= 1 ) {
					// Entry Table
					$this->_usedInputs=array();
					?>
					<form method="post" action="<?php echo $this->_selfLink . '-settings&group_id=' . $_GET['group_id']; ?>">
						<div class="tablenav">
							<div class="alignleft actions">
								<?php $this->_addSubmit( 'delete_entries', array( 'value' => 'Delete', 'class' => 'button-secondary delete' ) ); ?>
								<input type="submit" onclick="getValue();" name="save_order" value="Save order" class="button-secondary" />
							</div>
							<br class="clear" />
						</div>
						<br class="clear" />
						
						<table class="widefat">
							<thead>
								<tr>
									<th scope="col" class="check-column"><input type="checkbox" id="check-all-groups" /></th>
									<th>Entry</th>
									<th></th>
									<th>Link</th>
									<th class="num"><label>Reorder <?php $this->tip('Click and drag the double sided arrow up or down to rearrange the entries.'); ?></label></th>
								</tr>
							</thead>
							<tbody id="reorder-table">
								<?php
								$url = $this->_selfLink . '-settings';
								$order = $this->_options['groups'][$_GET['group_id']]['order'];


								foreach ($order as $ordnum) {
									$entry = $this->_options['groups'][$_GET['group_id']]['entries'][$ordnum];
									echo '<tr id="'. $ordnum . '">';
									echo '<th scope="col" class="check-column"><input type="checkbox" name="' . $this->_var . '-entries[]" class="administrator groups" value="' . $ordnum . '" /></th>';
									echo '<td class="entsum">';
										$wordcount = str_word_count($entry['entry']);
										if ($wordcount > 30) {
											preg_match('/^([^.!?\s]*[\.!?\s]+){0,30}/', strip_tags($entry['entry']), $abstract);
											$sument = $abstract[0] . ' ...';
										}
										else {
											$sument = $entry['entry'];
										}
										echo '<p>' . stripslashes($sument) . '</p>';
									echo '</td>';
									echo '<td>';
										echo '<a href="' . $url . '&group_id=' . $_GET['group_id'] . '&entry_id=' . $ordnum . '">Edit Entry</a>';
									echo '</td>';
									if ( ($entry['link']) != 'none') {
										echo '<td><a href="' . $entry['link'] . '">' . $entry['link'] . '</a></td>';
									}
									else {
										echo '<td>' . $entry['link'] . '</td>';
									}
									?>
									<td class="dragHandle">
										<img src="<?php echo $this->_pluginURL; ?>/images/draghandle2.png" alt="Click and drag to reorder" />
									</td>
									<?php
									echo '</tr>';
								}
								$this->_addHidden('groupid', array( 'size' => '45', 'maxlength' => '200', 'value' => $_GET['group_id']));
								?>
							</tbody>

							<tfoot>
								<tr>
									<th scope="col" class="check-column"><input type="checkbox" id="check-all-groups" /></th>
									<th>Entry</th>
									<th></th>
									<th>Link</th>
									<th class="num"><label>Reorder <?php $this->tip('Click and drag the double sided arrow up or down to rearrange the entries.'); ?></label></th>
								</tr>
							</tfoot>
						</table>
						
						<div class="tablenav">
							<div class="alignleft actions">
								<?php $this->_addSubmit( 'delete_entries', array( 'value' => 'Delete', 'class' => 'button-secondary delete' ) ); ?>
								<input type="hidden" id="hidnorder" name="hidnorder" value="" />
								<input type="submit" onclick="getValue();" name="save_order" value="Save order" class="button-secondary" />
							</div>
							
							<br class="clear" />
						</div>
						
						<br class="clear" />
					
					<?php $this->_addUsedInputs(); ?>
					<?php wp_nonce_field( $this->_var . '-nonce' ); ?>
					</form>
					<?php
				}
				?>

				<!-- ADD Entry -->
				<h2 id="addnew">Add New Entry</h2>
				<?php $this->_usedInputs=array(); ?>
				<form method="post" action="<?php echo $this->_selfLink . '-settings&group_id=' . $_GET['group_id']; ?>">
					<table class="form-table">
						<tr>
							<th><label for="entry">Entry <?php $this->tip('Insert entry here.'); ?>:</label></th>
							<td><?php $this->_addTextArea('entry', array( 'rows' => '2', 'cols' => '42', 'value' => '' ) ); ?></td>
						</tr>
						<tr>	
							<th><label for="entry_link">Optional-Link <?php $this->tip('Optional: Insert a URL for the entry to become a link.'); ?>:</label></th>
							<td><?php $this->_addTextBox('link', array( 'size' => '45', 'maxlength' => '200', 'value' => '' ) ); ?></td>
						</tr>
						<tr>
							<td>Open URL in New Tab/Window</td>
							<td><?php $this->_addCheckBox('newtab', '1'); ?></td>
						</tr>
							<?php $this->_addHidden('groupid', array( 'size' => '45', 'maxlength' => '200', 'value' => $_GET['group_id']));?>
					</table>
					<p class="submit"><?php $this->_addSubmit( 'add_entry', '+ Add Entry' ); ?></p>
					<?php $this->_addUsedInputs(); ?>
					<?php wp_nonce_field( $this->_var . '-nonce' ); ?>
				</form>
				<!-- Update group settings -->
				<h2 id="addnew" name="group_settings">Group Settings</h2>
				<?php $this->_usedInputs=array(); ?>
				<form method="post" action="<?php echo $this->_selfLink . '-settings&group_id=' . $_GET['group_id']; ?>">
					<table class="form-table">
					<?php $gpath = $this->_options['groups'][$_GET['group_id']]; ?>
						<tr>
							<th><label for="name">Group Name <?php $this->tip('Insert new name for the group or leave same.'); ?>:</label></th>
							<td><?php $this->_addtextBox('name', array( 'size' => '45', 'maxlength' => '200', 'value' => stripslashes($gpath['name']) ) ); ?></td>
						</tr>
						<tr>
							<th><label for="random">Entry order <?php $this->tip('Select ordered or random for how the order of the entries.'); ?>:</label></th>
							<td>
								<input type="radio" name="<?php echo $this->_var; ?>-random" value="false" <?php if ( isset($gpath['random']) ) { if ($gpath['random'] != 'true') { echo " checked "; } } else { echo " checked "; } ?> /> Ordered<br />
								<input type="radio" name="<?php echo $this->_var; ?>-random" value="true" <?php if ( isset($gpath['random']) ) { if ($gpath['random'] == 'true') { echo " checked "; }} ?>/> Random
							</td>
						</tr>
						<tr>
							<th><strong>Rotation Speed Settings</strong></th>
						</tr>
						<tr>
							<th><label for="fadein">Fade in duration <?php $this->tip('Insert length of time to fadein each entry here.'); ?>:</label></th>
							<td>
								<?php
									$fadeinmilli = ($gpath['fadein']) % 1000;
									$fadeinseconds = (($gpath['fadein']) - $fadeinmilli) / 1000;
								?>
								Seconds:<?php $this->_addTextBox('insec', array( 'size' => '5', 'maxlength' => '200', 'value' => $fadeinseconds ) ); ?>
								+ Milliseconds:<?php $this->_addTextBox('inmil', array( 'size' => '5', 'maxlength' => '200', 'value' => $fadeinmilli ) ); ?>
							</td>
						</tr>
						<tr>	
							<th><label for="fadedisplay">Display text duration <?php $this->tip('Insert length of time to display each entry here.'); ?>:</label></th>
							<td>
								<?php
									$displaymilli = ($gpath['fadedisplay']) % 1000;
									$displayseconds = (($gpath['fadedisplay']) - $displaymilli) / 1000;
								?>
								Seconds:<?php $this->_addTextBox('displaysec', array( 'size' => '5', 'maxlength' => '200', 'value' => $displayseconds ) ); ?>
								+ Milliseconds:<?php $this->_addTextBox('displaymil', array( 'size' => '5', 'maxlength' => '200', 'value' => $displaymilli ) ); ?>
							</td>
						</tr>
						<tr>	
							<th><label for="fadeout">Fade out duration <?php $this->tip('Insert length of time to fadeout each entry here.'); ?>:</label></th>
							<td>
								<?php
									$fadeoutmilli = ($gpath['fadeout']) % 1000;
									$fadeoutseconds = (($gpath['fadeout']) - $fadeoutmilli) / 1000;
								?>
								Seconds:<?php $this->_addTextBox('outsec', array( 'size' => '5', 'maxlength' => '200', 'value' => $fadeoutseconds ) ); ?>
								+ Milliseconds:<?php $this->_addTextBox('outmil', array( 'size' => '5', 'maxlength' => '200', 'value' => $fadeoutmilli ) ); ?>
							</td>
						</tr>
						<tr>	
							<th><label for="fadewait">Between animations <?php $this->tip('Insert length of time between each animation.'); ?>:</label></th>
							<td>
								<?php
									$betweenmilli = ($gpath['between']) % 1000;
									$betweenseconds = (($gpath['between']) - $betweenmilli) / 1000;
								?>
								Seconds:<?php $this->_addTextBox('betweensec', array( 'size' => '5', 'maxlength' => '200', 'value' => $betweenseconds ) ); ?>
								+ Milliseconds:<?php $this->_addTextBox('betweenmil', array( 'size' => '5', 'maxlength' => '200', 'value' => $betweenmilli ) ); ?>
							</td>
						</tr>
						<tr>
							<th><strong>Group Styles</strong></th>
						</tr>
						<tr>
							<th><label for="width">Group Width in pixels <?php $this->tip('Insert a width in pixels for the group.'); ?>:</label></th>
							<td>Width in pixels:<?php $this->_addtextBox('width', array( 'size' => '5', 'maxlength' => '5', 'value' => $gpath['width'] ) ); ?></td>
						</tr>
						<tr>
							<td></td>
							<td>
							<?php
								if (($gpath['auto-width']) == '1') {
									$checked = array( 'checked' => 'yes', 'value' => '1' );
								}
								else {
									$checked = array( 'value' => '1');
								}
							?>
								<?php $this->_addCheckBox('auto-width', $checked); ?> Auto width
							</td>
						</tr>
						<tr>
							<th><label for="height">Group Height in pixels <?php $this->tip('Insert a height in pixels for the group.'); ?>:</label></th>
							<td>Height in pixels:<?php $this->_addtextBox('height', array( 'size' => '5', 'maxlength' => '5', 'value' => $gpath['height'] ) ); ?></td>
						</tr>
						<tr>
							<td></td>
							<td>
							<?php
								if (($gpath['auto-height']) == '1') {
									$checked = array( 'checked' => 'yes', 'value' => '1' );
								}
								else {
									$checked = array( 'value' => '1');
								}
							?>
								<?php $this->_addCheckBox('auto-height', $checked); ?> Auto height
							</td>
						</tr>
						<tr>
							<th><label for="horizontal">Horizontal text alignment <?php $this->tip('Choose the desired horizontal alignment for the text.'); ?>:</label></th>
							<td>
								<?php
								$horizontal_options = array(
											'left'	=> 'left',
											'center'=> 'center',
											'right'	=> 'right'
										 );
								?>
								<select name="<?php echo $this->_var; ?>-horizontal">
									<?php
									foreach( $horizontal_options as $def => $val ) {
										if($gpath['horizontal'] == $def) {
											echo '<option selected value="' . $def . '">' . $val . '</option selected>';
										}
										else {
											echo '<option value="' . $def . '">' . $val . '</option>';
										}
									}
									?>
								</select>
							</td>
						</tr>
						<tr>
							<th><label for="vertical">Vertical text alignment <?php $this->tip('Choose the desired vertical alignment for the text.'); ?>:</label></th>
							<td>
								<?php
								$vertical_options = array(
											'top'	=> 'top',
											'middle'=> 'middle',
											'bottom'=> 'bottom'
										 );
								?>
								<select name="<?php echo $this->_var; ?>-vertical">
									<?php
									foreach( $vertical_options as $v_def => $v_val ) {
										if($gpath['vertical'] == $v_def) {
											echo '<option selected value="' . $v_def . '">' . $v_val . '</option selected>';
										}
										else {
											echo '<option value="' . $v_def . '">' . $v_val . '</option>';
										}
									}
									?>
								</select>
							</td>
						</tr>
						<tr>
							<th><label for="background">Background Color <?php $this->tip('Insert the group background color.'); ?>:</label></th>
							<td>
								#<input type="text" maxlength="6" size="6" class="jpicker" name="<?php echo $this->_var; ?>-back-color" value="<?php echo $gpath['background-color']; ?>" />
							</td>
						</tr>
						<tr>
							<td></td>
							<td>
							<?php
								if (($gpath['transparent']) == '1') {
									$checked = array( 'checked' => 'yes', 'value' => '1' );
								}
								else {
									$checked = array( 'value' => '1');
								}
							?>
								<?php $this->_addCheckBox('transparent', $checked); ?> transparent
							</td>
						</tr>
						<tr>
							<th><strong>Group Font Styles</strong></th>
						</tr>
						<tr>
							<th colspan="2">To use default styles leave form fields blank.</th>
						</tr>
						<tr>
							<th>Font-size in pixels:</th>
							<td>
								<?php
									if ( $gpath['font-size'] == 'inherit') {
										$font_s = '';
									}
									else {
										$font_s = $gpath['font-size'];
									}
									$this->_addTextBox('font-size', array( 'size' => '3', 'maxlength' => '8', 'value' => $font_s ) );
								?>
							</td>
						</tr>

						<tr>
							<th>Font-Family:</th>
							<td>
								<?php
								$family_options = array(
											'inherit'			  => 'default',
											'Arial, Helvetica, sans-serif'	  => 'Arial',
											'Georgia, Serif'		  => 'Georgia',
											'Verdana, Geneva, sans-serif'	  => 'Verdana',
											'‘Times New Roman’, Times, serif' => 'Times New Roman',
											'Tahoma, Geneva, sans-serif'	  => 'Tahoma',
											'Impact, Charcoal, sans-serif'	  => 'Impact'
										 );
								?>
								<select name="<?php echo $this->_var; ?>-font-family">
									<?php
									foreach( $family_options as $def => $val ) {
										if($gpath['font-family'] == $def) {
											echo '<option selected value="' . $def . '">' . $val . '</option selected>';
										}
										else {
											echo '<option value="' . $def . '">' . $val . '</option>';
										}
									}
									?>
								</select>
							</td>
						</tr>
						<tr>
							<th>Font-color:</th>
							<?php
								if ( $gpath['font-color'] == 'inherit' ) {
									$font_c = '';
								}
								else {
									$font_c = $gpath['font-color'];
								}
							?>
							<td>#<input type="text" maxlength="6" size="6" class="jpicker" name="<?php echo $this->_var; ?>-font-color" value="<?php echo $font_c; ?>" /></td>
							
						</tr>

						<?php $this->_addHidden('groupid', array( 'size' => '45', 'maxlength' => '200', 'value' => $_GET['group_id']));?>
					</table>
					<p class="submit"><?php $this->_addSubmit( 'group_settings', 'Save Settings' ); ?></p>
					<?php $this->_addUsedInputs(); ?>
					<?php wp_nonce_field( $this->_var . '-nonce' ); ?>
				</form>

				<?php

			}
			else {
				echo '<h2>Rotating Text Groups</h2>';

				$groupsnum = count($this->_options['groups']);
				if ($groupsnum >= 1) {
					$this->_usedInputs=array();
					?>
					<form method="post" action="<?php echo $this->_selfLink; ?>-settings">
						<div class="tablenav">
							<div class="alignleft actions">
								<?php $this->_addSubmit( 'delete_groups', array( 'value' => 'Delete', 'class' => 'button-secondary delete' ) ); ?>
							</div>
						
							<br class="clear" />
						</div>
					
						<br class="clear" />
						<table class="widefat">
							<thead>
								<tr>
									<th scope="col" class="check-column"><input type="checkbox" id="check-all-groups" /></th>
									<th>Group Name</th>
									<th>Entries</th>
									<th>Shortcode</th>
									<th class="num">Dimensions (W x H)</th>
								</tr>
							</thead>
							<tbody>
							<?php
								
								$url = $this->_selfLink . '-settings';
								
								foreach(($this->_options['groups']) as $id => $article) {
									echo '<tr>';
									echo '<th scope="col" class="check-column"><input type="checkbox" name="' . $this->_var . '-groups[]" class="administrator groups" value="' . $id . '" /></th>';
									echo '<td><strong><a href="' . $url . '&group_id=' . $id . '" title="Modify Group Settings"> ' . stripslashes($article['name']) . '</a></strong></td>';
									echo '<td>' . count($this->_options['groups'][$id]['entries']) . ' (<a href="' . $url . '&group_id=' . $id . '">Add Entries</a>)</td>';
									echo '<td>[rotatingtext group="' . $id . '"]</td>';
									echo '<td class="num">' . $article['width'] . ' x ' . $article['height'] . ' px</td>';
									echo '</tr>';
								}
							?>
							</tbody>
							<tfoot>
								<tr>
									<th scope="col" class="check-column"><input type="checkbox" id="check-all-groups" /></th>
									<th>Group Name</th>
									<th>Entries</th>
									<th>Shortcode</th>
									<th class="num">Dimensions (W x H)</th>
								</tr>
							</tfoot>
						</table>

						<div class="tablenav">
							<div class="alignleft actions">
								<?php $this->_addSubmit( 'delete_groups', array( 'value' => 'Delete', 'class' => 'button-secondary delete' ) ); ?>
							</div>
						
							<br class="clear" />
						</div>
					
						<br class="clear" />

					<?php $this->_addUsedInputs(); ?>
					<?php wp_nonce_field( $this->_var . '-nonce' ); ?>
					</form>
				<?php } ?>

				<!-- ADD GROUP FORM -->
				<h2>Add New Rotating Text Group</h2>
				<?php $this->_usedInputs=array(); ?>
				<form method="post" action="<?php echo $this->_selfLink; ?>-settings">
					<table class="form-table">
						<tr>
							<td><label for="group name">Name for New Group <?php $this->tip('Enter a group name here.'); ?>:</label></td>
							<td><?php $this->_addTextBox('name', array( 'size' => '45', 'maxlength' => '200', 'value' => '' ) ); ?></td>
						</tr>
					</table>
					<p class="submit"><?php $this->_addSubmit( 'add_group', 'Add Group' ); ?></p>
					<?php $this->_addUsedInputs(); ?>
					<?php wp_nonce_field( $this->_var . '-nonce' ); ?>
				</form>
				<?php
			}
		echo '</div>';
	}
	
	// OPTIONS STORAGE //////////////////////
	
	
	function save() {
		add_option($this->_var, $this->_options, '', 'no'); // 'No' prevents autoload if we wont always need the data loaded.
		update_option($this->_var, $this->_options);
		return true;
	}
	
	
	function load() {
		$this->_options=get_option($this->_var);
		if ( empty( $this->_options ) ) { // No options set so use defaults.
			$this->_options = $this->_defaults;
		}
		return true;
	}
	
	
	function shortcode($atts) {
		$this->load();

		$group = $atts['group'];
		
		// Call rotator function
		return $this->rotator($group);
	}
	
	function widget($group) {
		$this->load();
		
		// Call rotator function
		echo $this->rotator($group);
	}
	
	function rotator($group) {	
		// Defines a css ID for each widget of rotatingtext
		$this->_instance++;
		
		$return = '';

		// Defines group database path
		$gpath = $this->_options['groups'][$group];
		
		// Put javascript inside variable to input PHP variables
		$executer = "
			<script type='text/javascript'>
				jQuery(document).ready(function() {
					jQuery('#rotatingtextid-" . $this->_instance . "').rotatingtext({
						fadeinspeed: " . $gpath['fadein'] . ",
						fadedisplay: " . $gpath['fadedisplay'] . ",
						fadeoutspeed: " . $gpath['fadeout'] . ",
						fadetimeout: " . $gpath['between'] . ",
						current: 'current" . $this->_instance . "'
					});
				});
			</script>
		";
		$return .= $executer;
		
		// Check if group background is transparent
		if ( $gpath['transparent'] == '1' ) {
			$background = 'transparent';
		}
		else {
			$background = '#' . $gpath['background-color'];
		}

		// Check for auto width or height
		if ( $gpath['auto-width'] == '1' ) {
			$width = '';
			$smwidth = '';
		}
		else {
			$width = "width: " . $gpath['width'] . "px;";
			$smwidth = "width:" . ($gpath['width'] - 10) . "px;";
		}
		if ( $gpath['auto-height'] == '1' ) {
			$height = '';
			$smheight = '';
		}
		else {
			$height = "height: " . $gpath['height'] . "px;";
		}

		// Dispaly entries for rotation
		$return .= '<div class="rotatingtext-wrapper" style="' . $width . '' . $height . '">';
		$return .= '<table style="background: '. $background . '; ' . $smwidth . '' . $height . '">';
			$return .= '<tr>';
			$return .= '<td style=" ' . $smwidth . ' vertical-align: ' . $gpath['vertical'] . '; text-align: ' . $gpath['horizontal'] . ' !important;">';
			$return .= '<div class="rotatingtext" id="rotatingtextid-' . $this->_instance . '" style=" ' . $smwidth . '" >';


				// ORDER FILTER
				$max = count($gpath['order']);
				if ( $gpath['random'] === 'true' ) {
					$preorder = (array_rand((array)$gpath['order'], $max));

					for($i=0; $i<$max; $i++){
						$neworder[$i] = $gpath['order'][$preorder[$i]];
					}
					shuffle($neworder);
				}
				else {
					$neworder = $gpath['order'];
				}

				foreach ($neworder as $ordnum) {
					$article = $gpath['entries'][$ordnum];
					
					// determine inheritance
					if ( $article['font-family'] != 'inherit' ) {
						$fam = 'font-family: ' . $article['font-family'] . ';';
					}
					else {
						if ( $gpath['font-family'] != 'inherit' ) {
							$fam = 'font-family: ' . $gpath['font-family'] . ';';
						}
						else {
							$fam = '';
						}
					}
					if ( $article['font-size'] != 'inherit' ) {
						$size = 'font-size: ' . $article['font-size'] . 'px;';
					}
					else {
						if ( $gpath['font-size'] != 'inherit' ) {
							$size = 'font-size: ' . $gpath['font-size'] . 'px;';
						}
						else {
							$size = '';
						}
					}
					if ( $article['font-color'] != 'inherit' ) {
						$color = 'color: #' . $article['font-color'] . ';';
					}
					else {
						if ( $gpath['font-color'] != 'inherit' ) {
							$color = 'color: #' . $gpath['font-color'] . ';';
						}
						else {
							$color = '';
						}
					}
					
					$return .= '<div class="' . $this->_var . '-' . $group . '-' . $ordnum . ' rselector" style="' . $size . '' . $fam . '' . $color . '">';
					if ( ($article['link']) != 'none') {
						if ( ($article['newtab']) != '1') {
							$return .= '<a href="' . $article['link'] . '">' . stripslashes($article['entry']) . '</a>';
						}
						else {
							$return .= '<a href="' . $article['link'] . '" target="_blank">' . stripslashes($article['entry']) . '</a>';
						}
					}
					else {
						$return .= stripslashes($article['entry']);
					}
					$return .= '</div>';
				}
			$return .= '</div>';
			$return .= '</td>';
			$return .= '</tr>';
		$return .= '</table>';
		$return .= '</div>';
		
		return $return;
	
	}
	
	
	// ADMIN MENU FUNCTIONS /////////////////

	/** admin_menu()
	 *
	 * Initialize menu for admin section.
	 *
	 */		
	function admin_menu() {
		// Handle series menu. Create series menu if it does not exist.
		global $menu;
		$found_series = false;
		foreach ( $menu as $menus => $item ) {
			if ( $item[0] == $this->_series ) {
				$found_series = true;
			}
		}
		if ( $found_series === false ) {
			add_menu_page( $this->_series . ' Getting Started', $this->_series, 'administrator', 'pluginbuddy-' . strtolower( $this->_series ), array(&$this, 'view_gettingstarted'), $this->_pluginURL.'/images/pluginbuddy.png' );
			add_submenu_page( 'pluginbuddy-' . strtolower( $this->_series ), $this->_name.' Getting Started', 'Getting Started', 'administrator', 'pluginbuddy-' . strtolower( $this->_series ), array(&$this, 'view_gettingstarted') );
		}
		// Register for getting started page
		global $pluginbuddy_series;
		if ( !isset( $pluginbuddy_series[ $this->_series ] ) ) {
			$pluginbuddy_series[ $this->_series ] = array();
		}
		$pluginbuddy_series[ $this->_series ][ $this->_name ] = $this->_pluginPath;
		
		add_submenu_page( 'pluginbuddy-' . strtolower( $this->_series ), $this->_name.' Settings', 'Rotating Text', 'administrator', $this->_var.'-settings', array(&$this, 'view_settings'));
	}

	function admin_scripts() {
	}

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
	
	
	/////////////////////////////////////////////
	// CHRIS' FORM CREATION FUNCTIONS: //////////
	/////////////////////////////////////////////
	
	function _addSubmit( $var, $options = array(), $override_value = true ) {
		if ( ! is_array( $options ) )
			$options = array( 'value' => $options );
		
		$options['type'] = 'submit';
		$options['name'] = $var;
		$options['class'] = ( empty( $options['class'] ) ) ? 'button-primary' : $options['class'];
		$this->_addSimpleInput( $var, $options, $override_value );
	}
	
	function _addTextBox( $var, $options = array(), $override_value = false ) {
		if ( ! is_array( $options ) )
			$options = array( 'value' => $options );
		
		$options['type'] = 'text';
		$this->_addSimpleInput( $var, $options, $override_value );
	}
	
	function _addTextArea( $var, $options = array(), $override_value = false ) {
		if ( ! is_array( $options ) )
			$options = array( 'value' => $options );
		
		$options['type'] = 'textarea';
		$this->_addSimpleInput( $var, $options, $override_value );
	}
	
	function _addCheckBox( $var, $options = array(), $override_value = false ) {
		if ( ! is_array( $options ) )
			$options = array( 'value' => $options );
		
		$options['type'] = 'checkbox';
		$this->_addSimpleInput( $var, $options, $override_value );
	}
	
	function _addHidden( $var, $options = array(), $override_value = false ) {
		if ( ! is_array( $options ) )
			$options = array( 'value' => $options );
		
		$options['type'] = 'hidden';
		$this->_addSimpleInput( $var, $options, $override_value );
	}
	
	function _addUsedInputs() {
		$options['type'] = 'hidden';
		$options['value'] = implode( ',', $this->_usedInputs );
		$options['name'] = 'used-inputs';
		$this->_addSimpleInput( 'used-inputs', $options, true );
	}
	
	function _addSimpleInput( $var, $options = false, $override_value = false ) {
		if ( empty( $options['type'] ) ) {
			echo "<!-- _addSimpleInput called without a type option set. -->\n";
			return false;
		}

		$scrublist['textarea']['value'] = true;
		$scrublist['file']['value'] = true;
		$scrublist['dropdown']['value'] = true;
		$defaults = array();
		$defaults['name'] = $this->_var . '-' . $var;
		$var = str_replace( '[]', '', $var );
		
		if ( 'checkbox' === $options['type'] )
			$defaults['class'] = $var;
		else
			$defaults['id'] = $var;
		
		$options = $this->_merge_defaults( $options, $defaults );
		
		if ( ( false === $override_value ) && isset( $this->_options[$var] ) ) {
			if ( 'checkbox' === $options['type'] ) {
				if ( $this->_options[$var] == $options['value'] )
					$options['checked'] = 'checked';
			}
			elseif ( 'dropdown' !== $options['type'] )
				$options['value'] = $this->_options[$var];
		}
		
		if ( ( preg_match( '/^' . $this->_var . '/', $options['name'] ) ) && ( ! in_array( $options['name'], $this->_usedInputs ) ) )
			$this->_usedInputs[] = $options['name'];
		
		$attributes = '';
		
		if ( false !== $options )
			foreach ( (array) $options as $name => $val )
				if ( ! is_array( $val ) && ( ! isset( $scrublist[$options['type']][$name] ) || ( true !== $scrublist[$options['type']][$name] ) ) )
					if ( ( 'submit' === $options['type'] ) || ( 'button' === $options['type'] ) )
						$attributes .= "$name=\"$val\" ";
					else
						$attributes .= "$name=\"" . htmlspecialchars( $val ) . '" ';
		
		if ( 'textarea' === $options['type'] )
			echo '<textarea ' . $attributes . '>' . $options['value'] . '</textarea>';
		elseif ( 'dropdown' === $options['type'] ) {
			echo "<select ".$class." $attributes>\n";
			foreach ( (array) $options['value'] as $val => $name ) {
			
				$selected = ( $this->_options[$var] == $val ) ? ' selected="selected"' : '';
				echo "<option value=\"$val\"$selected>$name</option>\n";
			}
			
			echo "</select>\n";
		}
		else
			echo '<input ' . $attributes . '/>';
	}
	
	function _merge_defaults( $values, $defaults, $force = false ) {
		if ( ! $this->_is_associative_array( $defaults ) ) {
			if ( ! isset( $values ) ) {
				return $defaults;
			}
			if ( false === $force ) {
				return $values;
			}
			if ( isset( $values ) || is_array( $values ) )
				return $values;
			return $defaults;
		}
		
		foreach ( (array) $defaults as $key => $val ) {
			if ( ! isset( $values[$key] ) ) {
				$values[$key] = null;
			}
			$values[$key] = $this->_merge_defaults($values[$key], $val, $force );
		}
		return $values;
	}
	
	function _is_associative_array( &$array ) {
		if ( ! is_array( $array ) || empty( $array ) ) {
			return false;
		}
		$next = 0;
		foreach ( $array as $k => $v ) {
			if ( $k !== $next++ ) {
				return true;
			}
		}
		return false;
	}
	
	function _groupsCreate() {
		foreach ( (array) explode( ',', $_POST['used-inputs'] ) as $name ) {
			$is_array = ( preg_match( '/\[\]$/', $name ) ) ? true : false;
			$name = str_replace( '[]', '', $name );
			$var_name = preg_replace( '/^' . $this->_var . '-/', '', $name );
			if ( $is_array && empty( $_POST[$name] ) ) {
				$_POST[$name] = array();
			}

			if ( empty( $_POST[$name] ) ) { // If they gave a blank group name, fail.
				$this->_errors[] = 'name';
				$this->_showErrorMessage( 'A name is required to create a new group.' );
			}

			foreach ( (array) $this->_options['groups'] as $id => $group ) { // Loop through to make sure group name doesnt already exist
				if ( $group['name'] == $_POST[$name] ) { // Found a match.
					$this->_errors[] = 'name';
					$this->_showErrorMessage( 'A group with the entered name already exists.' );
					break; // exit loop. no need to keep looping if we found one matching group already. one error is enough to stop
				}
			}

			if ( isset( $this->_errors ) ) {
				$this->_showErrorMessage( 'Please correct the ' . ngettext( 'error', 'errors', count( $this->_errors ) ) . ' in order to add the new group.' );
			} else { // No errors, so add the group!
	
				// Get index for new group by adding 1 to the largest index currently in the groups. Put in $newID
				if ( is_array( $this->_options['groups'] ) && !empty( $this->_options['groups'] ) ) {
					$newID = max( array_keys( $this->_options['groups'] ) ) + 1;
				} else {
					$newID = 0;
				}
	
				$this->_options['groups'][$newID] = $this->_groupdefaults; // Load group defaults.
				$this->_options['groups'][$newID]['name'] = $_POST[$name]; // Set name of new group.
	
				$this->save(); // Save changes to database.
	
				$this->_showStatusMessage( "The group \"" . stripslashes($_POST[$name]) . "\" has been added." );
			}
		}
	}
	
	function _groupSettings() {
		$name = $_POST[$this->_var . '-name'];
		$group = $_POST[$this->_var . '-groupid'];
		
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
					$_POST[$this->_var . '-name'] = $this->_options['groups'][$group]['name'];
					break; // exit loop. no need to keep looping if we found one matching group already. one error is enough to stop
				}
			}
		}
		
		if ( empty( $_POST[$this->_var . '-name'] )) {
			$_POST[$this->_var . '-name'] = $this->_options['groups'][$group]['name'];
		}
		if ( empty( $_POST[$this->_var . '-insec'] )) {
			$_POST[$this->_var . '-insec'] = 0;
		}
		if ( empty( $_POST[$this->_var . '-inmil'] )) {
			$_POST[$this->_var . '-inmil'] = 0;
		}
		if ( empty( $_POST[$this->_var . '-displaysec'] )) {
			$_POST[$this->_var . '-displaysec'] = 0;
		}
		if ( empty( $_POST[$this->_var . '-displaymil'] )) {
			$_POST[$this->_var . '-displaymil'] = 0;
		}
		if ( empty( $_POST[$this->_var . '-outsec'] )) {
			$_POST[$this->_var . '-outsec'] = 0;
		}
		if ( empty( $_POST[$this->_var . '-outmil'] )) {
			$_POST[$this->_var . '-outmil'] = 0;
		}
		if ( empty( $_POST[$this->_var . '-betweensec'] )) {
			$_POST[$this->_var . '-betweensec'] = 0;
		}
		if ( empty( $_POST[$this->_var . '-betweenmil'] )) {
			$_POST[$this->_var . '-betweenmil'] = 0;
		}
		
		if ( empty( $_POST[$this->_var . '-back-color']) ) {
			$_POST[$this->_var . '-back-color'] = $this->_options['groups'][$group]['background-color'];
		}
		if ( empty($_POST[$this->_var . '-transparent'])) {
			$_POST[$this->_var . '-transparent'] = '0';
		}
		
		if ( ( empty($_POST[$this->_var . '-font-size']) ) || ( !is_numeric($_POST[$this->_var . '-font-size']) ) ) {
			$_POST[$this->_var . '-font-size'] = 'inherit';
		}
		
		if ( (empty( $_POST[$this->_var . '-width'])) || (!is_numeric($_POST[$this->_var . '-width'])) ) {
			$_POST[$this->_var . '-width'] = $this->_options['groups'][$group]['width'];
			$this->_errors[] = 'width';
			$this->_showErrorMessage( 'width must be a number.' );
		}
		if ( empty($_POST[$this->_var . '-auto-width'])) {
			$_POST[$this->_var . '-auto-width'] = '0';
		}

		if ( (empty( $_POST[$this->_var . '-height'])) || (!is_numeric($_POST[$this->_var . '-height'])) ) {
			$_POST[$this->_var . '-height'] = $this->_options['groups'][$group]['height'];
			$this->_errors[] = 'height';
			$this->_showErrorMessage( 'height must be a number.' );
		}
		if ( empty($_POST[$this->_var . '-auto-height'])) {
			$_POST[$this->_var . '-auto-height'] = '0';
		}

		if ( ( empty($_POST[$this->_var . '-font-color']) ) ) {
			$_POST[$this->_var . '-font-color'] = 'inherit';
		}
		
		// calculate duration times
		if ( (!is_numeric($_POST[$this->_var . '-insec'])) || (!is_numeric($_POST[$this->_var . '-inmil'])) ) {
			$fadein = $this->_options['groups'][$group]['fadein'];
			$this->_errors[] = 'fadein';
			$this->_showErrorMessage( 'fadein time must be a number.' );
		}
		else {
			$fadein = ($_POST[$this->_var . '-insec'] * 1000) + ($_POST[$this->_var . '-inmil']);
		}

		if ( (!is_numeric($_POST[$this->_var . '-displaysec'])) || (!is_numeric($_POST[$this->_var . '-displaymil'])) ) {
			$display = $this->_options['groups'][$group]['fadedisplay'];
			$this->_errors[] = 'display';
			$this->_showErrorMessage( 'display time must be a number.' );
		}
		else {
			$display = ($_POST[$this->_var . '-displaysec'] * 1000) + ($_POST[$this->_var . '-displaymil']);
		}
		
		if ( (!is_numeric($_POST[$this->_var . '-outsec'])) || (!is_numeric($_POST[$this->_var . '-outmil'])) ) {
			$fadeout = $this->_options['groups'][$group]['fadeout'];
			$this->_errors[] = 'fadeout';
			$this->_showErrorMessage( 'fadeout time must be a number.' );
		}
		else {
			$fadeout = ($_POST[$this->_var . '-outsec'] * 1000) + ($_POST[$this->_var . '-outmil']);
		}

		if ( (!is_numeric($_POST[$this->_var . '-betweensec'])) || (!is_numeric($_POST[$this->_var . '-betweenmil'])) ) {
			$between = $this->_options['groups'][$group]['between'];
			$this->_errors[] = 'between';
			$this->_showErrorMessage( 'between animations time must be a number.' );
		}
		else {
			$between = ($_POST[$this->_var . '-betweensec'] * 1000) + ($_POST[$this->_var . '-betweenmil']);
		}

		// update group settings.
		$this->_options['groups'][$group]['name'] = $_POST[$this->_var . '-name'];
		$this->_options['groups'][$group]['random'] = $_POST[$this->_var . '-random'];
		$this->_options['groups'][$group]['fadein'] = $fadein;
		$this->_options['groups'][$group]['fadedisplay'] = $display;
		$this->_options['groups'][$group]['fadeout'] = $fadeout;
		$this->_options['groups'][$group]['between'] = $between;
		$this->_options['groups'][$group]['background-color'] = $_POST[$this->_var . '-back-color'];
		$this->_options['groups'][$group]['transparent'] = $_POST[$this->_var . '-transparent'];
		$this->_options['groups'][$group]['width'] = $_POST[$this->_var . '-width'];
		$this->_options['groups'][$group]['auto-width'] = $_POST[$this->_var . '-auto-width'];
		$this->_options['groups'][$group]['height'] = $_POST[$this->_var . '-height'];
		$this->_options['groups'][$group]['auto-height'] = $_POST[$this->_var . '-auto-height'];
		$this->_options['groups'][$group]['horizontal'] = $_POST[$this->_var . '-horizontal'];
		$this->_options['groups'][$group]['vertical'] = $_POST[$this->_var . '-vertical'];
		// update group font styles
		$this->_options['groups'][$group]['font-size'] = $_POST[$this->_var . '-font-size'];
		$this->_options['groups'][$group]['font-family'] = $_POST[$this->_var . '-font-family'];
		$this->_options['groups'][$group]['font-color'] = $_POST[$this->_var . '-font-color'];
		
		$this->save(); // save the changes to database.
		
		$this->_showStatusMessage( stripslashes($_POST[$this->_var . '-name'] ) . " has been updated.");
		
	}
	
	function _groupsDelete() {
		$names = array();
		
		if ( ! empty( $_POST[$this->_var . '-groups'] ) && is_array( $_POST[$this->_var . '-groups'] ) ) {
			foreach ( (array) $_POST[$this->_var . '-groups'] as $id ) {
				$names[] = $this->_options['groups'][$id]['name'];
				unset( $this->_options['groups'][$id] );
			}
			$this->save();
		}

		natcasesort( $names );
		
		if ( $names ) {
			$this->_showStatusMessage( 'Successfully deleted the group.' );
		}
		else {
			$this->_showErrorMessage( 'No Groups were selected for deletion' );
		}
	}
	
	function _entriesCreate() {
		
		$entry = $_POST[$this->_var . '-entry'];
		$link = $_POST[$this->_var . '-link'];
		$group = $_POST[$this->_var . '-groupid'];
		
		$is_array = ( preg_match( '/\[\]$/', $entry ) ) ? true : false;
		$entry = str_replace( '[]', '', $entry );
		$var_entry = preg_replace( '/^' . $this->_var . '-/', '', $entry );
		
		if ( $is_array && empty( $_POST[$this->_var . '-entry'] ) ) {
			$_POST[$this->_var . '-entry'] = array();
		}

		if ( empty( $_POST[$this->_var . '-entry'] ) ) { // If they gave a blank entry, fail.
			$this->_errors[] = 'entry';
			$this->_showErrorMessage( 'Entry is required.' );
		}

		if ( empty( $_POST[$this->_var . '-link'] ) ) { // If they gave a blank link, define none.
			$_POST[$this->_var . '-link'] = 'none';
		}
		
		if ( empty($_POST[$this->_var . '-newtab']) ) {
			$_POST[$this->_var . '-newtab'] = '0';
		}

		if ( isset( $this->_errors ) ) {
			$this->_showErrorMessage( 'Please correct the ' . ngettext( 'error', 'errors', count( $this->_errors ) ) . ' in order to add the new entry.' );
		}
		else { // No errors, so add the entry.

			// Get index for new entry by adding 1 to the largest index currently in the entry. Put in $newID
			if ( is_array( $this->_options['groups'][$group]['entries'] ) && !empty( $this->_options['groups'][$group]['entries'] ) ) {
				$newID = max( array_keys( $this->_options['groups'][$group]['entries'] ) ) + 1;
				$ordkey = max( array_keys( $this->_options['groups'][$group]['order'] ) ) + 1;
			} else {
				$newID = 0;
				$ordkey = 0;
			}

			$this->_options['groups'][$group]['entries'][$newID] = $this->_entrydefaults; // load entry defaults

			$this->_options['groups'][$group]['entries'][$newID]['entry'] = $_POST[$this->_var . '-entry']; // Set entry.
			$this->_options['groups'][$group]['entries'][$newID]['link'] = $_POST[$this->_var . '-link']; // Set entry link.
			$this->_options['groups'][$group]['entries'][$newID]['newtab'] = $_POST[$this->_var . '-newtab']; // sets link target
			$this->_options['groups'][$group]['order'][$ordkey] = $newID; // sets default order num
			
			$this->save(); // Save changes to database.

			$this->_showStatusMessage( "The entry has been added." );
		}
		
	}
	
	function _updateEntry() {

		check_admin_referer( $this->_var . '-nonce' );
		
		$entry = $_POST[$this->_var . '-entry'];

		$is_array = ( preg_match( '/\[\]$/', $entry ) ) ? true : false;
			
		$entry = str_replace( '[]', '', $entry );
		$var_name = preg_replace( '/^' . $this->_var . '-/', '', $entry );
		
		$groupid = $_POST[$this->_var . '-groupid'];
		$entryid = $_POST[$this->_var . '-entryid'];
		
		if ( $is_array && empty( $_POST[$this->_var . '-entry'] ) ) {
			$_POST[$this->_var . '-entry'] = array();
		}
		if ( isset( $_POST[$this->_var . '-entry'] ) && ! is_array( $_POST[$this->_var . '-entry'] ) ) {
			$this->_options['groups'][$groupid]['entries'][$entryid]['entry'] = stripslashes($var_name);
		}
		else if ( isset( $_POST[$this->_var . '-entry'] ) ) {
			$this->_options['groups'][$groupid]['entries'][$entryid]['entry'] = $var_name;
		}
		else {
			$this->_options['groups'][$groupid]['entries'][$entryid]['entry'] = '';
		}

		if ( empty( $_POST[$this->_var . '-link'] ) ) { // If they gave a blank link, define none.
			$_POST[$this->_var . '-link'] = 'none';
		}

		if ( empty($_POST[$this->_var . '-newtab'])) {
			$_POST[$this->_var . '-newtab'] = '0';
		}
		
		if ( ( empty($_POST[$this->_var . '-font-size']) ) || ( !is_numeric($_POST[$this->_var . '-font-size']) ) ) {
			$_POST[$this->_var . '-font-size'] = 'inherit';
		}
		
		if ( ( empty($_POST[$this->_var . '-font-color']) ) ) {
			$_POST[$this->_var . '-font-color'] = 'inherit';
		}

		// Add link
		$this->_options['groups'][$groupid]['entries'][$entryid]['link'] = $_POST[$this->_var . '-link'];
		
		// Add open in new window or tab status
		$this->_options['groups'][$groupid]['entries'][$entryid]['newtab'] = $_POST[$this->_var . '-newtab'];
		
		// Add styles
		$this->_options['groups'][$groupid]['entries'][$entryid]['font-size'] = $_POST[$this->_var . '-font-size'];
		$this->_options['groups'][$groupid]['entries'][$entryid]['font-family'] = $_POST[$this->_var . '-font-family'];
		$this->_options['groups'][$groupid]['entries'][$entryid]['font-color'] = $_POST[$this->_var . '-font-color'];
		
		$errorCount = 0;
		
		// ERROR CHECKING OF INPUT
		if ( $errorCount < 1 ) {
			if ( $this->save() )
				$this->_showStatusMessage( __( 'Settings updated', $this->_var ) );
			else
				$this->_showErrorMessage( __( 'Error while updating settings', $this->_var ) );
		}
		else {
			$this->_showErrorMessage( __ngettext( 'Please fix the input marked in red below.', 'Please fix the inputs marked in red below.', $errorCount ) );
		}
		
	}
	
	function _entriesDelete() {
		$names = array();

		$group = $_POST[$this->_var . '-groupid'];

		if ( ! empty( $_POST[$this->_var . '-entries'] ) && is_array( $_POST[$this->_var . '-entries'] ) ) {
			foreach ( (array) $_POST[$this->_var . '-entries'] as $id ) {
				$key = array_search($id, $this->_options['groups'][$group]['order']);
				$names[] = $this->_options['groups'][$group]['name'];
				unset( $this->_options['groups'][$group]['entries'][$id] );
				unset( $this->_options['groups'][$group]['order'][$key] );
			}
			$this->save();
		}

		natcasesort( $names );
		
		if ( $names ) {
			$this->_showStatusMessage( 'Successfully deleted the entry.' );
		}
		else {
			$this->_showErrorMessage( 'No Image Groups were selected for deletion' );
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
			

			$this->save();
			$this->_showStatusMessage( ' Succesfully updated the entry order' );
		}
	}
	
	
	// PUBLIC DISPLAY OF MESSAGES ////////////////////////
	
	function _showStatusMessage( $message ) {
		echo '<div id="message" class="updated fade"><p><strong>'.$message.'</strong></p></div>';			
	}
	function _showErrorMessage( $message ) {
		echo '<div id="message" class="error"><p><strong>'.$message.'</strong></p></div>';
	}
	

	
	
	/////////////////////////////////////////////
	// END CHRIS' FUNCTIONS /////////////////////
	/////////////////////////////////////////////
	
		//Register the updater version
		function upgrader_register() {
			$GLOBALS['pb_classes_upgrade_registration_list'][$this->_var] = $this->_updater;
		} //end register_upgrader
		//Select the greatest version
		function upgrader_select() {
			if ( !isset( $GLOBALS[ 'pb_classes_upgrade_registration_list' ] ) ) {
				//Fallback - Just include this class
				require_once( $this->_pluginPath . '/lib/updater/updater.php' );
				return;
			}
			//Go through each global and find the highest updater version and the plugin slug
			$updater_version = 0;
			$plugin_var = '';
			foreach ( $GLOBALS[ 'pb_classes_upgrade_registration_list' ] as $var => $version) {
				if ( version_compare( $version, $updater_version, '>=' ) ) {
					$updater_version = $version;
					$plugin_var = $var;
				}
			}
			//If the slugs match, load this version
			if ( $this->_var == $plugin_var ) {
				require_once( $this->_pluginPath . '/lib/updater/updater.php' );
			}
		} //end upgrader_select
		function upgrader_instantiate() {
			
			$pb_product = strtolower( $this->_var );
			$pb_product = str_replace( 'ithemes-', '', $pb_product );
			$pb_product = str_replace( 'pluginbuddy-', '', $pb_product );
			$pb_product = str_replace( 'pluginbuddy_', '', $pb_product );
			$pb_product = str_replace( 'pb_thumbsup', '', $pb_product );
			
			$args = array(
				'parent' => $this, 
				'remote_url' => 'http://updater2.ithemes.com/index.php',
				'version' => $this->_version,
				'plugin_slug' => $this->_var,
				'plugin_path' => plugin_basename( __FILE__ ),
				'plugin_url' => $this->_pluginURL,
				'product' => $pb_product,
				'time' => 43200,
				'return_format' => 'json',
				'method' => 'POST',
				'upgrade_action' => 'check' );
			$this->_pluginbuddy_upgrader = new iThemesPluginUpgrade( $args );

		} //end upgrader_instantiate
	
	
    } // End class

	$PluginBuddyrotatingtext = new rotatingtext(); // Create instance
}

// Load widget functionality.
require_once('widget.php');

?>
