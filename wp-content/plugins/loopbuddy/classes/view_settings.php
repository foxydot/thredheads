<?php

if ( !empty( $_POST['save'] ) ) {
	$this->savesettings();
}
wp_print_scripts( array( 'dashboard') );
?>


<style type="text/css">
	.form-table tr > td:first-child {
		width: 300px;
	}
</style>


<?php
	//Get loop items
	$combined_types = array(
		'general' => '',
		'post_types' => '',
		'taxonomy' => '',
		'archives' => ''
	);
	$loops = $this->_parent->get_loops();
	foreach ( $loops as $key => $data ) {
		$combined_types[ $data[ 'type' ] ][ $key ] = $data;
	}
	//die( '<pre>' . print_r( $loops, true ) );
	?>	

<div class="wrap">
	<?php
	if ( !current_theme_supports( 'loop-standard' ) ) {
		$this->alert( sprintf( __( 'Your theme <a href="%2$s">doesn\'t support %1$s</a>, so these settings will not have any effect.  You can still use %1$s with Widgets.', 'it-l10n-loopbuddy' ), 'LoopBuddy', 'http://loopstandard.com/' ), true );
	}

	?>
	<?php $this->title( __( 'Settings', 'it-l10n-loopbuddy' ) ); ?><br />
	<form method="post" action="<?php echo $this->_selfLink; ?>-settings">
		<?php // Ex. for saving in a group you might do something like: $this->_options['groups'][$_GET['group_id']['settings'] which would be: ['groups'][$_GET['group_id']['settings'] ?>
		<input type="hidden" name="savepoint" value="" />
		
		<div style="width: 80%; min-width: 750px;" class="postbox-container">
                <div class="metabox-holder">
                    <div class="meta-box-sortables ui-sortable">
                    	<?php
                    	foreach ( $combined_types as $label => $loops ) :
                    		switch( $label ) {
                    			case 'general':
                    				$label = __( 'General', 'it-l10n-loopbuddy' );
                    				break;
                    			case 'post_types':
                    				$label = __( 'Post Types', 'it-l10n-loopbuddy' );
                    				break;
                    			case 'taxonomy':
                    				$label = __( 'Taxonomies', 'it-l10n-loopbuddy' );
                    				break;
                    			case 'archives':
                    				$label = __( 'Archives', 'it-l10n-loopbuddy' );
                    				break;
                    			default:
                    				continue;
                    				break;
                    		} //end switch
                         ?>
                        <div class="postbox" style="display: block;">
                            <div title="Click to toggle" class="handlediv"><br></div><!--/.handlediv-->
                            <h3 class="hndle"><span><?php echo esc_html( $label ); ?></span></h3>
                            <div class="inside">
							<table class="form-table">
								<?php
									foreach ( $loops as $loop_item => $loop_args ) {
										?>
										<tr>
											<td><label for="#loops[<?php echo esc_attr( $loop_item ); ?>][query]"><?php printf( __( 'Default %s query', 'it-l10n-loopbuddy' ), $loop_args[ 'label' ] ); ?></label></td>
											<td>
												<select name="#loops[<?php echo esc_attr( $loop_item ); ?>][query]">
													<option value="default" <?php selected( $loop_args[ 'query'], 'default' ); ?>><?php _e( 'WordPress Default', 'it-l10n-loopbuddy' ); ?></option>
												<?php
												foreach ( (array) $this->_options['queries'] as $query_id => $query ) {
													echo '<option value="' . $query_id . '"';
													selected( $loop_args[ 'query'], $query_id );
													echo '>' . esc_html( $query['title'] ) . '</option>';
												}
												?>
												</select>
												<input type='hidden' name="#loops[<?php echo esc_attr( $loop_item ); ?>][label]" value="<?php echo esc_attr( $loop_args[ 'label' ] ); ?>" />
												<input type='hidden' name="#loops[<?php echo esc_attr( $loop_item ); ?>][type]" value="<?php echo esc_attr( $loop_args[ 'type' ] ); ?>" />
											</td>
										</tr>
										<tr>
											<td><label for="#loops[<?php echo esc_attr( $loop_item ); ?>][layout]"><?php printf( __( 'Default %s layout', 'it-l10n-loopbuddy' ), $loop_args[ 'label' ] ); ?></label></td>
											<td>
												<select name="#loops[<?php echo esc_attr( $loop_item ); ?>][layout]">
													<option value="default" <?php selected( $loop_args[ 'layout'], 'default' ); ?>><?php _e( 'WordPress Default', 'it-l10n-loopbuddy' ); ?></option>
												<?php
												foreach ( (array) $this->_options['layouts'] as $layout_id => $layout ) {
													echo '<option value="' . $layout_id . '"';
													selected( $loop_args[ 'layout'], $layout_id );
													echo '>' . esc_html( $layout['title'] ) . '</option>';
												}
												?>
												</select>
											</td>
										</tr>
										<?php
									} //end foreach
								?>
							</table>
						 </div><!--/.inside-->
                        </div><!--/.postbox-->
                        <?php endforeach; ?>
                    </div><!--/.meta-box-sortables ui-sortable -->
                </div><!--/.metabox-holder-->
            </div><!--/.postbox-container-->
		<div style="width: 80%; min-width: 750px;" class="postbox-container">
                <div class="metabox-holder">
                    <div class="meta-box-sortables ui-sortable" style='min-height: 0;'>
                    <div class="postbox closed" style="display: block;">
                            <div title="Click to toggle" class="handlediv"><br></div><!--/.handlediv-->
                            <h3 class="hndle"><span><?php esc_html_e( 'Advanced', 'it-l10n-loopbuddy' ); ?></span></h3>
                            <div class="inside">
							<table class="form-table">
							<tr>
								<td><?php esc_html_e( 'Debug Mode', 'it-l10n-loopbuddy' ); $this->tip( __( 'Show the query variables above the LoopBuddy output - Only Administrators will see this' , 'it-l10n-loopbuddy' ) ); ?></td>
								<td><select name="#debug_mode">
									  <option <?php selected( 'on', $this->_options[ 'debug_mode' ] ); ?> value="on">On</option>
												<option <?php selected( 'off', $this->_options[ 'debug_mode' ] ); ?> value="off">Off</option></select></td>
							</tr>
							</table>
						 </div><!--/.inside-->
                        </div><!--/.postbox-->
                    </div><!--/.meta-box-sortables ui-sortable -->
                </div><!--/.metabox-holder-->
            </div><!--/.postbox-container-->
		
		<p class="submit clear"><input type="submit" name="save" value="<?php esc_attr_e( 'Save Settings', 'it-l10n-loopbuddy' ); ?>" class="button-primary" id="save" /></p>
		<?php $this->nonce(); ?>
		<br />
	</form>
</div>
