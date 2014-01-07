<?php
$group = &$this->_parent->get_group( $_GET['edit'] );
?>
<h2><?php echo $this->_name; ?> Group Settings for "<?php echo htmlentities( $group['title'] ); ?>" (<a href="<?php echo $this->_parent->_selfLink; ?>-settings">group list</a>)</h2>
<?php
if ( !empty( $_POST['save'] ) ) {
	$this->savesettings();
}

if ( isset( $_GET['set_layout'] ) ) {
	$group['layout'] = $_GET['set_layout'];
	$this->_parent->save();
	$this->alert( __( 'Layout saved.', 'it-l10n-tipsy' ) );
}
if ( $group['layout'] == 'light-header' ) {
	$this->alert( __( 'Note: For the header to appear in this layout you must use an "h3" tag in the post editor for what you want in the header of the tip.' , 'it-l10n-tipsy') );
}	
?>
<form method="post" action="<?php echo $this->_selfLink; ?>-settings&edit=<?php echo htmlentities( $_GET['edit'] ); ?>">
	<input type="hidden" name="savepoint" value="groups#<?php echo $_GET['edit']; ?>" />	
		<table class="form-table">
			<tr>
				<td><label for="title">Group name<?php $this->tip( 'Name of the new group to create. This is not publicly displayed.' ); ?></label></td>
				<td><input type="text" name="#title" id="title" size="45" maxlength="45" value="<?php echo stripslashes($group['title']); ?>" /></td>
			</tr>
			<tr>
				<td><label for="direction">Tip activation<?php $this->tip( 'Controls the way the tip is activated.' ); ?></label></td>
				<td>
					<select name="#activation" id="activation">
						<option value="hover" <?php selected( 'hover', $group[ 'activation' ] ); ?> />Hover</option>
						<option value="focus" <?php selected( 'focus', $group[ 'focus'] ); ?> />Focus</option>
						<option value="click" <?php if ( 'click' == $group['activation'] ) { echo 'selected'; } ?> />Click</option>
					</select>
				</td>
			</tr>
			<tr>
				<td><label for="position">Tip position<?php $this->tip( 'Default orientation tooltip should show up as.' ); ?></label></td>
				<td>
					<select name="#position" id="position">
						<option value="bottom" <?php if ( 'bottom' == $group['position'] ) { echo 'selected'; } ?> />Bottom</option>
						<option value="top" <?php if ( 'top' == $group['position'] ) { echo 'selected'; } ?> />Top</option>
						<option value="left" <?php if ( 'left' == $group['position'] ) { echo 'selected'; } ?> />Left</option>
						<option value="right" <?php if ( 'right' == $group['position'] ) { echo 'selected'; } ?> />Right</option>
					</select>
				</td>
			</tr>
			<tr>
				<td><label for="auto_hide">Auto hide<?php $this->tip( 'When set to true the tooltip will only fadeout when you hover over the actual tooltip and then hover off of it.' ); ?></label></td>
				<td>
					<label for="auto_hide-1">
						<input class="radio_toggle" type="radio" name="#auto_hide" id="auto_hide-1" value="true" <?php checked( 'true', $group[ 'auto_hide']); ?>/>
						True
					</label>
					&nbsp;
					<label for="auto_hide-0">
						<input class="radio_toggle" type="radio" name="#auto_hide" id="auto_hide-0" value="false" <?php if ( $group['auto_hide'] == 'false' ) { echo ' checked '; } ?>/>
						False
					</label>
				</td>
			</tr>	
			<tr>
				<td><label for="max_width">Max width<?php $this->tip( 'CSS max-width property for the Tipsy element. ' ); ?></label></td>
				<td><input type="text" name="#max_width" id="max_width" size="5" maxlength="5" value="<?php echo absint($group['max_width']); ?>" style="text-align: right;" />px</td>
			</tr>
			<tr>
				<td><label for="edge_offset">Edge offset<?php $this->tip( 'Distances the Tipsy popup from the element with tooltip applied to it by the number of pixels specified.' ); ?></label></td>
				<td><input type="text" name="#edge_offset" id="edge_offset" size="5" maxlength="5" value="<?php echo absint($group['edge_offset']); ?>" style="text-align: right;" />px</td>
			</tr>	
			<tr>
				<td><label for="display_delay">Display delay<?php $this->tip( 'Number of milliseconds to delay before showing the Tipsy popup after you mouseover an element with tooltip applied to it. ' ); ?></label></td>
				<td><input type="text" name="#display_delay" id="display_delay" size="5" maxlength="5" value="<?php echo absint($group['display_delay']); ?>" style="text-align: right;" />ms</td>
			</tr>	
			<tr>
				<td><label for="fade_in_speed">Fade in speed<?php $this->tip( 'Speed at which the Tipsy popup will fade into view. ' ); ?></label></td>
				<td><input type="text" name="#fade_in_speed" id="fade_in_speed" size="5" maxlength="5" value="<?php echo absint($group['fade_in_speed']); ?>" style="text-align: right;" />ms</td>
			</tr>
			<tr>
				<td><label for="fade_out_speed">Fade out speed<?php $this->tip( 'Speed at which the Tipsy popup will fade out of view.' ); ?></label></td>
				<td><input type="text" name="#fade_out_speed" id="fade_out_speed" size="5" maxlength="5" value="<?php echo absint($group['fade_out_speed']); ?>" style="text-align: right;" />ms</td>
			</tr>
			<tr>
				<td align="top">
					<label for="tip_content">Tip content<?php $this->tip( 'Default content that goes inside of the Tipsy element. ' ); ?></label>
				</td>
				<td><textarea rows="3" cols="35" name="#tip_content" id="tip_content"><?php echo isset( $group[ 'tip_content' ] ) ? esc_html( $group[ 'tip_content' ] ) : ''; ?></textarea></td>
			</tr>	
			
		</table>
		<p class="submit"><input type="submit" name="save" value="Save Settings" class="button-primary" id="save" /></p>
		<?php $this->nonce(); ?>
	</form>
	
	<h3>Select a Layout</h3>
	<table><tr>
		<?php
		$directories = glob( $this->_pluginPath . '/layouts/*' ); 
		$i = 0; 
		foreach( $directories as $directory ) {
			$i++;
			if ( $i > 3 ) {
				echo '</tr><tr>';
				$i = 1;
			}
			$directory = basename( $directory );
			$self_link = $this->_selfLink . '-settings';
			$image_link = add_query_arg( array(
				'set_layout' => $directory,
				'edit'	=> $_GET[ 'edit' ],
				),
				$self_link
			);
			echo '<td style="padding: 0 15px 10px 0;">';
	
			if ( $group['layout'] == $directory ) { // SELECTED LAYOUT.
				echo sprintf( '<a href="%s" title="Currently active layout."><img src="%s" style="border: 3px solid #216F94; padding: 2px;" /></a>', esc_url( $image_link ), esc_url( $this->_pluginURL . '/layouts/' . $directory .  '/screenshot.png' ) );
			} else {
				echo sprintf( '<a href="%s" title="Click to select layout."><img src="%s" /></a>', esc_url( $image_link ), esc_url( $this->_pluginURL . '/layouts/' . $directory .  '/screenshot.png' ) );
			}
			echo '</td>';
		}
		?>
	</tr></table>
