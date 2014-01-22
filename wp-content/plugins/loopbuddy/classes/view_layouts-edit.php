<?php
require_once( $this->_pluginPath . '/classes/ajax_slotitems.php' );
$this->_slot_items = new pluginbuddy_loopbuddy_slotitems($this);
?>

<div class="postbox-container" style="width:60%;">
	<?php
	wp_enqueue_script( 'thickbox' );
	wp_print_scripts( 'thickbox' );
	wp_print_styles( 'thickbox' );
	
	$layout = &$this->_parent->get_layout( $_GET['edit'] );
	
	if ( !empty( $_POST['save'] ) ) {
		$this->savesettings();
	}
	
	wp_enqueue_style('dashboard');
	wp_print_styles('dashboard');
	wp_enqueue_script('dashboard');
	wp_print_scripts('dashboard');
	
	wp_enqueue_script( 'jquery-ui-draggable' );
	wp_print_scripts( 'jquery-ui-draggable' );
	wp_enqueue_script( 'jquery-ui-droppable' );
	wp_print_scripts( 'jquery-ui-droppable' );
	wp_enqueue_script( 'jquery-ui-sortable' );
	wp_print_scripts( 'jquery-ui-sortable' );
	
	$the_loop = $this->_parent->get_the_loop();

	// Load layout defaults if needed.
	if ( empty( $layout['items'] ) ) {
		$loop_defaults = array(
			'title' => array( 'the_title'),
			'utility_above' => array(),
			'meta_above' => array(),
			'content' => array( 'the_content' ),
			'meta_below' => array(),
			'utility_below' => array( 'edit_post_link' )
		);
		$item_settings = array();
		require_once( $this->_pluginPath . '/classes/ajax_slotitems.php' );
		$slot_items = new pluginbuddy_loopbuddy_slotitems( $this->_parent );
		
		foreach( $loop_defaults as $default_line => $default_items ) {
			foreach( $default_items as $item ) {
				if ( !isset( $item_settings[ $default_line ] ) ) {
					$item_settings[ $default_line ] = array();
				}
				
				$item_id = uniqid();
				
				$item_defaults = $slot_items->get_tag_settings( $item );
				$item_defaults[ 'tag' ] = $item;
				$item_settings[ $default_line ][$item_id] = $item_defaults;
				
			}
		} //end foreach

		$layout['items'] = $item_settings;
		$this->alert( 'Loaded layout defaults.' );
		$this->_parent->save();
	}
	// If items exist in our saved data structure. Use these to populate the respective slots.
	if ( !empty( $layout['items'] ) ) {
		
		foreach( $layout['items'] as $index => $loop_slot ) {
			$this_replace = '';
			foreach( $loop_slot as $loop_item_id => $loop_item ) {
				
				$this_title = $this->_slot_items->get_tag_title( $loop_item['tag'], $this->_slot_items->_tags );
				
				$this_replace .= '<div class="loop_item_placed" id="pbloop-' . $loop_item_id . '" title="' . $loop_item['tag'] . '"><div class="loop_item_buttons"><a class="loop_action" title="' . $this_title . ' ' . __( 'Settings', 'it-l10n-loopbuddy' ) . '"></a><a class="loop_delete" title="' . $this_title . ' ' . __( 'Delete', 'it-l10n-loopbuddy' ) . '"></a></div><h4>' . $this_title . '</h4></div>';
				
			}
			// Replace slot item placeholder with the item(s) that are inside it.
			$the_loop = str_replace( '{' . $index . '}', '<div class="loop_slot" id="pbslot-' . $index . '" title="' . $index . '">' . $this_replace . '</div>', $the_loop );
		}
		
		// This handles placing slots for both slots with no default content and slots with unknown default tags in them.
		$the_loop = preg_replace('/\{(\w+)\}/', "<div class=\"loop_slot\" id=\"pbslot-$1\" title=\"$1\"></div>", $the_loop);
		
	}
	//wp_print_r( $the_loop );
							

	?>
	
	<h2><img src="<?php echo $this->_pluginURL; ?>/images/loopbuddy_rings.png" style="vertical-align: -4px;"> <?php esc_html_e( 'Layout Editor', 'it-l10n-loopbuddy' ); ?> (<a href="<?php echo $this->_parent->_selfLink; ?>-layouts"><?php esc_html_e( 'Layouts List', 'it-l10n-loopbuddy' ); ?></a>)</h2>
	
	<script type="text/javascript">
		jQuery(document).ready(function() {
			jQuery('.loop_slot').sortable({
				connectWith: '.loop_slot',
				revert: true,
				update: function(e,ui) {
					// Only save changes on drop IN, not pull out phase of the update.  Prevents double firing and pulling out of things too early.
					if (this === ui.item.parent()[0]) {
						var this_placed_item = jQuery(ui.item);
						jQuery(ui.item).removeClass('loop_item');
						jQuery(ui.item).addClass('loop_item_placed');
						slot_items = new Array();
						slot_name = jQuery(this).attr('id').substring(7);
						jQuery(this).children('.loop_item_placed').each(function(j) {
							slot_items.push( jQuery(this).attr('id').substring(7) );
						});
						
						//alert('Slot `'+slot_name +  '` updated with items `' + slot_items + '`.' );
						jQuery('#pb_saving').show();
						
						jQuery.ajax({
							type: 'POST',
							url: '<?php echo admin_url('admin-ajax.php'); ?>?action=pb_loopbuddy_savelayout&group=<?php echo htmlentities( $_GET['edit'] ); ?>',
							data: 'slot=' + slot_name + '&items=' + slot_items,
							success: function(msg){
								this_placed_item.children('.loop_item_buttons').css({'display':'block'}); // Show settings icon on newly placed slot item.
								if ( msg.unique_id != 0 ) {
									this_placed_item.attr('id', 'pbloop-' + msg.unique_id);
								}
								jQuery('#pb_saving').hide();
							},
							'dataType': 'json'
						});
					}
				},
				start: function(e,ui) {
					/* New jQuery fix (new slot items do not cause over method to trigger. */
					jQuery('.loop_slot').css({'background':'transparent'}); /* New jQuery fix. Changing existing slot item keeps its slot bg colored. */
					jQuery(this).css({'background':'#EAF2FA'});
					
					jQuery( '.loop_item' ).draggable( 'option', 'revert', false );
				},
				stop: function(e,ui) {
					jQuery(this).css({'background':'transparent'});
					jQuery( '.loop_item' ).draggable( 'option', 'revert', true );
				},
				
				over: function(e,ui) {
					jQuery('.loop_slot').css({'background':'transparent'}); /* New jQuery fix. Changing existing slot item keeps its slot bg colored. */
					
					jQuery(this).css({'background':'#EAF2FA'});
					jQuery( '.loop_item' ).draggable( 'option', 'revert', false );
				},
				out: function(e,ui) {
					jQuery(this).css({'background':'transparent'});
					/* jQuery( '.loop_item' ).draggable( 'option', 'revert', true ); New jQuery fix. Prevents revert from double flying. */
				}
			});
			
			jQuery('.loop_item').draggable({
				revert: true,
				opacity: '.9',
				zIndex: 15,
				helper: 'clone',
				appendTo: 'body',
				connectToSortable: '.loop_slot'
			});
			
			//A loop action button has been clicked so show the thickbox options
			jQuery('.loop_action').live('click',function() {
				tb_show( jQuery(this).attr('title'), '<?php echo admin_url('admin-ajax.php'); ?>?action=pb_loopbuddy_editslotitem&id=' + jQuery(this).parent().parent().attr('id').substring(7) + '&slot=' + jQuery(this).parent().parent().parent().attr('id').substring(7) + '&group=<?php echo htmlentities( $_GET['edit'] ); ?>&slot=' + jQuery(this).closest('.loop_slot').attr('title'), null );
				return false;
			});
			
			//A loop item delete button has been clicked, so delete the item
			jQuery('.loop_delete').live('click',function() {
				var $parent_container = jQuery( this ).parent().parent().parent();
				var slot = jQuery(this).parent().parent().attr('id').substring(7);
				
				
				jQuery( "#pbloop-" + slot ).fadeOut( 'fast', function() { 
					jQuery( this ).remove();
					var action = 'pb_loopbuddy_savelayout';
					var group = '<?php echo esc_js( htmlentities( $_GET[ 'edit' ] ) ); ?>';
					var slot_items = new Array();
					var slot_name = $parent_container.attr('id').substring(7);
					jQuery( $parent_container).children('.loop_item_placed').each(function(j) {
						slot_items.push( jQuery(this).attr('id').substring(7) );
					});
					jQuery.ajax({
							type: 'POST',
							url: '<?php echo admin_url('admin-ajax.php'); ?>?action=pb_loopbuddy_savelayout&group=<?php echo htmlentities( $_GET['edit'] ); ?>',
							data: 'slot=' + slot_name + '&items=' + slot_items,
							success: function(msg){
								
								jQuery('#pb_saving').hide();
							}
						});
				} );
				return false;
			});
			/* Save slot item settings when user saves an item. */
			
			jQuery('.pb_loopbuddy_slotitemsave').live('submit',function() {
				//alert( jQuery(this).attr('action'));
				jQuery('#pb_slot_saving').show();
				
				jQuery.post( jQuery(this).attr('action'), jQuery(this).serialize(),
					function(data) {
						jQuery('#pb_slot_saving').hide();
						if ( data != '1' ) {
							//console.log( data );
							//alert('Error Saving Changes: ' + data );
						} else {
							tb_remove();
							return false;
						}
					}
				);
				return false;
			});
			
			
			/* Update selected layout. */
			jQuery('.pb_layout').live('click',function() {
				jQuery('#pb_layout_value').val( jQuery(this).attr('id').substring(13) );
				jQuery('#pb_layout_value_text').html( jQuery(this).attr('id').substring(13) );
				tb_remove();
				return false;
			});
		});
		
	</script>
	
	<style type="text/css">
		.loop_slot {
			min-height: 34px;
			min-width: 70px;
			border: 1px dashed #E6E7ED;
			overflow: auto;
		}
		.loop_item, .loop_item_placed {
			cursor: move;
			color: #000000;
			border: 1px solid #DFDFDF;
			white-space: nowrap;
			-moz-border-radius: 5px;
			-webkit-border-radius: 5px;
			
			background:url("images/gray-grad.png") repeat-x scroll left top #DFDFDF;
			text-shadow:0 1px 0 #FFFFFF;
			font-size:12px;
			font-weight:bold;
			
			float: left;
			margin: 3px;
		}
		.loop_item_placed {
			min-width: 68px;
		}
		.loop_item h4 {
			line-height:1.3;
			margin:0;
			overflow:hidden;
			white-space:nowrap;
			
			padding:5px 9px;
			padding-right: 9px;
		}
		.loop_item_placed h4 {
			line-height:1.3;
			margin:0;
			overflow:hidden;
			white-space:nowrap;
			float: left;
			padding:5px 9px;
			padding-right: 0;
		}
		.loop_item_buttons {
			position: relative;
			float: right;
			height: 26px;
			width: 48px;
			padding-left: 15px;
		}
		.loop_action {
			float: left;
			background:url("<?php echo $this->_pluginURL; ?>/images/layoutgear.png") no-repeat scroll transparent;
			display:block;
			height:26px;
			width:24px;
			cursor: pointer;
		}
		.loop_delete {
			float: left;
			background:url("<?php echo $this->_pluginURL; ?>/images/layoutdelete.png") no-repeat scroll transparent;
			display:block;
			height:26px;
			width:24px;
			cursor: pointer;
		}

		.pb_drophover {
			background: #EAF2FA;
			border: 1px dashed #E6E7ED !important;
		}
		<?php // http://www.positioniseverything.net/easyclearing.html ?>
		.drag_clearfix:after {
			content: ".";
			display: block;
			height: 0;
			clear: both;
			visibility: hidden;
		}
		
	</style>
	
	<!--[if IE]>
		<style type="text/css">
			.drag_clearfix {
				zoom: 1;			/* triggers hasLayout */
				display: block;		/* resets display for IE/Win */
			}
			.drag_clearfix {
				display: inline-block;
			}
		</style>
	<![endif]-->
	<div class="metabox-holder">
		<div class="meta-box-sortables">
			
			<div id="breadcrumbslike" class="postbox">
				<!-- div class="handlediv" title="Click to toggle"><br /></div -->
				<h3 class="hndle" style="height: 16px;"><span style="font-size: 15px;"><?php esc_html_e( 'Editing', 'it-l10n-loopbuddy' ); ?> " <?php echo $layout['title']; ?>" <?php $this->tip( __( 'Drag and drop items from the right menu into the `slots` below. You can then click on the gear icon for each item for additional configuration options.', 'it-l10n-loopbuddy' ) ); echo ' '; ?></span> <div style="float: right;"><span id="pb_saving" style="display: none;"><img src="<?php echo $this->_pluginURL; ?>/images/loading.gif" alt="<?php esc_attr_e( 'Saving...', 'it-l10n-loopbuddy' ); ?>" title="<?php esc_attr_e( 'Saving...', 'it-l10n-loopbuddy' ); ?>" style="cursor: default;" width="16" height="16" /></span></div></h3>
				<div class="inside">
					<?php
					echo $the_loop;
					?>
					<div style="clear: both;"><!-- clearfix --></div>
				</div>
			</div>
			<div style="text-align: center; margin-top: -20px;"><small><i><?php esc_html_e( 'Drag & drop changes automatically saved.', 'it-l10n-loopbuddy' ); ?></i></small></div>
			
			<br />
			<!--
			<div id="breadcrumbslike" class="postbox">
				<div class="handlediv" title="Click to toggle"><br /></div>
				<h3 class="hndle"><span>Settings</span></h3>
				<div class="inside">
			-->
					<h3><?php esc_html_e( 'Settings', 'it-l10n-loopbuddy' ); ?></h3>
					<form method="post" action="<?php echo $this->_selfLink; ?>-layouts&edit=<?php echo htmlentities( $_GET['edit'] ); ?>">
						<?php // Ex. for saving in a group you might do something like: $this->_options['groups'][$_GET['group_id']['settings'] which would be: ['groups'][$_GET['group_id']['settings'] ?>
						<input type="hidden" name="savepoint" value="layouts#<?php echo $_GET['edit']; ?>" />
						
						<table class="form-table">
							<tr>
								<td><label for="title"><?php esc_html_e( 'Group Title', 'it-l10n-loopbuddy' ); ?><?php $this->tip( __( 'Title of the layout. This is for your reference only.', 'it-l10n-loopbuddy' ) ); ?></label></td>
								<td><input type="text" name="#title" id="title" size="45" maxlength="45" value="<?php echo stripslashes( $layout['title'] ); ?>" /></td>
							</tr>
							<?php $loop_css = isset( $layout[ 'loop_css' ] ) ? stripslashes( $layout[ 'loop_css' ] ) : ''; ?>
							<tr>
								<td><label for="title"><?php esc_html_e( 'Group CSS Class', 'it-l10n-loopbuddy' ); ?><?php $this->tip( __( 'Give your Loop a custom CSS class to make styling easier.', 'it-l10n-loopbuddy' ) ); ?></label></td>
								<td><input type="text" name="#loop_css" id="title" size="45" maxlength="45" value="<?php echo esc_attr( $loop_css ); ?>" /></td>
							</tr>
							<tr>
								<td valign="top"><label for="before_loop"><?php esc_html_e( 'Pre-Loop Text / HTML', 'it-l10n-loopbuddy' ); ?><?php $this->tip( __( 'Arbitrary text or HTML to insert before the start of this loop. This will NOT be displayed if there are no results for the loop.', 'it-l10n-loopbuddy' ) ); echo ' '; ?></label></td>
								<td><textarea name="#before_loop" id="before_loop" rows="5" cols="30" style="width: 100%;" /><?php echo stripslashes( $layout['before_loop'] ); ?></textarea></td>
							</tr>
							<tr>
								<td valign="top"><label for="after_loop"><?php esc_html_e( 'Post-Loop Text / HTML', 'it-l10n-loopbuddy' ); ?><?php $this->tip( __( 'Arbitrary text or HTML to insert before the start of this loop. This will NOT be displayed if there are no results for the loop.', 'it-l10n-loopbuddy' ) ); echo ' ';  ?></label></td>
								<td><textarea name="#after_loop" id="after_loop" rows="5" cols="30" style="width: 100%;" /><?php echo stripslashes( $layout['after_loop'] ); ?></textarea></td>
							</tr>
							<tr>
								<td valign="top"><label for="no_results"><?php esc_html_e( 'No Results Text / HTML', 'it-l10n-loopbuddy' ); ?><?php $this->tip( __( 'Arbitrary text or HTML to insert if no results/content was found to be displayed. This will be displayed instead of your loop.', 'it-l10n-loopbuddy' ) ); echo ' ';  ?></label></td>
								<td><textarea name="#no_results" id="no_results" rows="5" cols="30" style="width: 100%;" /><?php echo stripslashes( $layout['no_results'] ); ?></textarea></td>
							</tr>
						</table>
					
						<p class="submit"><input type="submit" name="save" value="<?php esc_attr_e( 'Save Settings', 'it-l10n-loopbuddy' ); ?>" class="button-primary" id="save" /></p>
						<?php $this->nonce(); ?>
					</form>
			<!--
				</div>
			</div>
			-->
			
		</div>
	</div>
	
</div>	
<div class="postbox-container" style="width:30%; margin-top: 52px; margin-left: 15px;">
	<div class="metabox-holder">
		<div class="meta-box-sortables">
			<?php
			foreach ( $this->_slot_items->_tags as $tag_category => $tag_categories ) {
				echo '<div id="breadcrumbslike" class="postbox">';
				echo '	<div class="handlediv" title="' . __( 'Settings', 'it-l10n-loopbuddy' ) . '"><br /></div>';
				echo '	<h3 class="hndle"><span>' . $tag_category . '</span></h3>';
				echo '	<div class="inside">';
				foreach ( $tag_categories as $tag_code => $tag ) {
					echo '<div class="loop_item" id="pbloop-new-' . $tag_code . '" title="' . $tag_code . '"><div class="loop_item_buttons" style="display: none;"><a class="loop_action" title="' . $tag[0] . ' ' . __( 'Settings', 'it-l10n-loopbuddy' ) . '"></a><a class="loop_delete" title="' . $this_title . ' ' . __( 'Delete', 'it-l10n-loopbuddy' ) . '"></a></div><h4>' . $tag[0] . '</h4></div>';
				}
				echo '		<br class="clear">';
				echo '	</div>';
				echo '</div>';
			}
			?>			
			
			
		</div>
</div>
