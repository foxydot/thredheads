<?php
require_once( $this->_pluginPath . '/classes/ajax_slotitems.php' );
$this->_slot_items = new pluginbuddy_loopbuddy_slotitems($this);
?>

<div class="postbox-container" style="width:60%;">
	<?php
	wp_enqueue_script( 'thickbox' );
	wp_print_scripts( 'thickbox' );
	wp_print_styles( 'thickbox' );
	
	$group = &$this->_parent->get_loop( $_GET['edit'] );
	
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
	
	$wp_upload_dir = WP_UPLOAD_DIR();
	if ( !file_exists( $wp_upload_dir['basedir'] . '/loopbuddy/layouts/' . $group['layout'] . '/' ) ) {
		$this->alert( 'ERROR #37: The layout files assigned to this group are missing.', true );
	} else {
		$the_loop = file_get_contents( $wp_upload_dir['basedir'] . '/loopbuddy/layouts/' . $group['layout'] . '/loop.txt' );
		
		// Load layout defaults if needed.
		if ( empty( $group['items'] ) ) {
			$loop_defaults = file_get_contents( $wp_upload_dir['basedir'] . '/loopbuddy/layouts/' . $group['layout'] . '/defaults.txt' );
			$loop_defaults = explode( "\n", $loop_defaults );
			
			$item_settings = array();
			
			foreach( $loop_defaults as $defaults_line ) {
				$defaults_line = explode( '=', $defaults_line );
				$defaults_items = explode( ',', $defaults_line[1] );
				
				foreach( $defaults_items as $defaults_item ) {
					if ( strstr( $defaults_item, '"' ) ) {
						$defaults_item = 'text';
					}
					if ( empty( $item_settings[$defaults_line[0]] ) ) {
						$item_settings[$defaults_line[0]] = array();
					}
					
					$item_id = uniqid();
					
					$item_settings[$defaults_line[0]][$item_id]['tag'] = $defaults_item;
					//array_push( $item_settings[$defaults_line[0]], $defaults_item );
					//$this_replace .= '<div class="loop_item_placed" id="loop_post_title" title="' . $defaults_item . '"><a class="loop_action"></a><h4>' . $this->_slot_items->get_tag_title( $defaults_item, $$this->_slot_items->_tags ) . '</h4></div>';
				}
				//$the_loop = str_replace( '{' . $defaults_line[0] . '}', $this_replace, $the_loop );
			}
			
			$group['items'] = $item_settings;
			$this->alert( 'Loaded layout defaults.' );
			
			$this->_parent->save();
		}
		
		/*
		echo '<pre>';
		print_r( $group['items'] );
		echo '</pre>';
		*/
		
		// If items exist in our saved data structure. Use these to populate the respective slots.
		if ( !empty( $group['items'] ) ) {
		
			foreach( $group['items'] as $slot_name => $loop_slot ) {
				$this_replace = '';
				foreach( $loop_slot as $loop_item_id => $loop_item ) {
					$this_title = $this->_slot_items->get_tag_title( $loop_item['tag'], $this->_slot_items->_tags );
					$this_replace .= '<div class="loop_item_placed" id="pbloop-' . $loop_item_id . '" title="' . $loop_item['tag'] . '"><a class="loop_action" title="' . $this_title . ' Settings"></a><h4>' . $this_title . '</h4></div>';
				}
				
				// Replace slot item placeholder with the item(s) that are inside it.
				$the_loop = str_replace( '{' . $slot_name . '}', '<div class="loop_slot" id="pbslot-' . $slot_name . '" title="' . $slot_name . '">' . $this_replace . '</div>', $the_loop );
			}
			
			// This handles placing slots for both slots with no default content and slots with unknown default tags in them.
			$the_loop = preg_replace('/\{(\w+)\}/', "<div class=\"loop_slot\" id=\"pbslot-$1\" title=\"$1\"></div>", $the_loop);
			
		}
	}
	?>
	
	<h2><?php echo $this->_name; ?> Loop Editor (<a href="<?php echo $this->_parent->_selfLink; ?>-loops">loops list</a>)</h2>
	
	<script type="text/javascript">
		jQuery(document).ready(function() {
			jQuery('.loop_slot').sortable({
				connectWith: '.loop_slot',
				revert: true,
				update: function(e,ui) {
					// Only save changes on drop IN, not pull out phase of the update.  Prevents double firing and pulling out of things too early.
					if (this === ui.item.parent()[0]) {
						this_placed_item = jQuery(ui.item);
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
							url: '<?php echo admin_url('admin-ajax.php'); ?>?action=pb_loopbuddy_saveloop&group=<?php echo htmlentities( $_GET['edit'] ); ?>',
							data: 'slot=' + slot_name + '&items=' + slot_items,
							success: function(msg){
								if ( msg != '1' ) {
									alert( 'Error Saving: ' + msg );
								}
								this_placed_item.children('.loop_action').css({'display':'block'}); // Show settings icon on newly placed slot item.
								jQuery('#pb_saving').hide();
							}
						});
					}
				},
				start: function(e,ui) {
				},
				stop: function(e,ui) {
				},
				over: function(e,ui) {
					jQuery(this).css({'background':'#EAF2FA'});
					
					jQuery( '.loop_item' ).draggable( 'option', 'revert', false );
				},
				out: function(e,ui) {
					jQuery(this).css({'background':'transparent'});
					
					jQuery( '.loop_item' ).draggable( 'option', 'revert', true );
				},
				receive: function(e,ui) {
					
					//e.dragCfg.revert = false;
					//jQuery('.ui-draggable-dragging').css('visibility','hidden');
				},
				change: function(e,ui) {
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
			
			jQuery('.loop_action').live('click',function() {
				tb_show( jQuery(this).attr('title'), '<?php echo admin_url('admin-ajax.php'); ?>?action=pb_loopbuddy_editslotitem&id=' + jQuery(this).parent().attr('id').substring(7) + '&slot=' + jQuery(this).parent().parent().attr('id').substring(7) + '&group=<?php echo htmlentities( $_GET['edit'] ); ?>', null );
				return false;
			});
			
			/* Save slot item settings when user saves an item. */
			
			jQuery('.pb_loopbuddy_slotitemsave').live('submit',function() {
				alert( jQuery(this).attr('action'));
				jQuery.post( jQuery(this).attr('action'), jQuery(this).serialize(),
					function(data) {
						alert("Data Loaded: " + data);
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
			/* min-width: 70px; */
		}
		.loop_item_placed {
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
			
			padding:5px 9px;
			padding-right: 35px;
		}
		.loop_action {
			float: right;
			background:url("<?php echo $this->_pluginURL; ?>/images/gear.gif") no-repeat scroll transparent;
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
				<h3 class="hndle" style="height: 16px;"><span style="font-size: 15px;">Editing "<?php echo $group['title']; ?>"</span> <div style="float: right;"><span id="pb_saving" style="display: none;"><img src="<?php echo $this->_pluginURL; ?>/images/loading.gif" alt="Saving..." title="Saving..." style="cursor: default;" width="16" height="16" /></span></div></h3>
				<div class="inside">
					<?php
					echo $the_loop;
					?>
					<div style="clear: both;"><!-- clearfix --></div>
				</div>
			</div>
			<div style="text-align: center; margin-top: -20px;"><small><i>Loop layout changes are automatically saved.</i></small></div>
			
			<br />
			<!--
			<div id="breadcrumbslike" class="postbox">
				<div class="handlediv" title="Click to toggle"><br /></div>
				<h3 class="hndle"><span>Settings</span></h3>
				<div class="inside">
			-->
					<h3>Settings</h3>
					<form method="post" action="<?php echo $this->_selfLink; ?>-loops&edit=<?php echo htmlentities( $_GET['edit'] ); ?>">
						<?php // Ex. for saving in a group you might do something like: $this->_options['groups'][$_GET['group_id']['settings'] which would be: ['groups'][$_GET['group_id']['settings'] ?>
						<input type="hidden" name="savepoint" value="['loops'][$_GET['edit']]" />
						
						<table class="form-table">
							<tr>
								<td valign="top"><label for="layout">Layout<?php $this->tip( 'Arbitrary text or HTML to insert before the start of this loop.' ); ?></label></td>
								<td><input type="hidden" name="#layout" id="pb_layout_value" value="<?php echo stripslashes( $group['layout'] ); ?>" /><span id="pb_layout_value_text"><?php echo $group['layout']; ?></span> <a href="<?php echo admin_url('admin-ajax.php'); ?>?action=pb_loopbuddy_layout_browser&loop=<?php echo htmlentities( $_GET['edit'] ); ?>" class="thickbox" title="Browse or change layout" style="text-decoration: none;"><small>[ Browse or change layout ]</small></a></td>
							</tr>
							<tr>
								<td valign="top"><label for="before_loop">Pre-Loop Text / HTML<?php $this->tip( 'Arbitrary text or HTML to insert before the start of this loop.' ); ?></label></td>
								<td><textarea name="#before_loop" id="before_loop" rows="5" cols="30" style="width: 100%;" value="<?php echo stripslashes( $group['before_loop'] ); ?>" /></textarea></td>
							</tr>
							<tr>
								<td valign="top"><label for="after_loop">Post-Loop Text / HTML<?php $this->tip( 'Arbitrary text or HTML to insert before the start of this loop.' ); ?></label></td>
								<td><textarea name="#after_loop" id="after_loop" rows="5" cols="30" style="width: 100%;" value="<?php echo stripslashes( $group['after_loop'] ); ?>" /></textarea></td>
							</tr>
						</table>
					
						<p class="submit"><input type="submit" name="save" value="Save Settings" class="button-primary" id="save" /></p>
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
				echo '	<div class="handlediv" title="Settings"><br /></div>';
				echo '	<h3 class="hndle"><span>' . $tag_category . '</span></h3>';
				echo '	<div class="inside">';
				foreach ( $tag_categories as $tag_code => $tag ) {
					echo '<div class="loop_item" id="pbloop-new-' . $tag_code . '" title="' . $tag_code . '"><a class="loop_action" style="display: none;"></a><h4>' . $tag[0] . '</h4></div>';
				}
				echo '		<br class="clear">';
				echo '	</div>';
				echo '</div>';
			}
			?>
			
			
			
			
			
			
			<div id="breadcrumbslike" class="postbox">
				<div class="handlediv" title="Click to toggle"><br /></div>
				<h3 class="hndle"><span>Inactive</span></h3>
				<div class="inside loop_slot drag_clearfix" style="border: 0px; padding: 0px;">
					<br class="clear">
				</div>
			</div>
			
		</div>
</div>
