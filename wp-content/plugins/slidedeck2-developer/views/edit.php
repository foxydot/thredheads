<?php
/**
 * SlideDeck Lens Editing Page
 * 
 * More information on this project:
 * http://www.slidedeck.com/
 * 
 * Full Usage Documentation: http://www.slidedeck.com/usage-documentation 
 * 
 * @package SlideDeck
 * @subpackage SlideDeck 2 Pro for WordPress
 * @author dtelepathy
 */

/*
Copyright 2012 digital-telepathy  (email : support@digital-telepathy.com)

This file is part of SlideDeck.

SlideDeck is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

SlideDeck is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with SlideDeck.  If not, see <http://www.gnu.org/licenses/>.
*/
?>
<div class="wrap">
	<div class="icon32" id="icon-themes"><br /></div>
    <h2><?php _e( "Edit SlideDeck Lenses", $namespace ); ?></h2>
    
    <?php slidedeck2_flash(); ?>
        
    <?php if( $read_only ): ?>
        <div class="updated settings-error"><p><strong>NOTE:</strong> <?php _e( "This lens cannot be modified because the files are write protected or it is a protected lens that is packaged with SlideDeck.", $namespace ); ?></p></div>
	<?php endif; ?>
    
	<div class="fileedit-sub">
		<div class="alignleft">
			<h3><?php echo $lens['meta']['name']; ?> <span>(<?php echo basename( $lens_filename ); ?>)</span></h3>
		</div>
		<div class="alignright">
			<form method="get" action="<?php echo admin_url( 'admin.php' ); ?>">
				<strong><label for="lens"><?php _e( "Select lens to edit", $namespace ); ?>: </label></strong>
				
				<input type="hidden" name="page" value="<?php echo SLIDEDECK2_BASENAME; ?>/lenses" />
				<input type="hidden" name="action" value="edit" />
				
				<select id="lens" name="slidedeck-lens">
					<?php foreach( $lenses as &$_lens ): ?>
						<option value="<?php echo $_lens['slug']; ?>"<?php if( $_lens['slug'] == $lens['slug'] ) echo ' selected="selected"'; ?>><?php echo $_lens['meta']['name']; ?></option>
					<?php endforeach; ?>
				</select>
				
				<input type="submit" value="Select" class="button" id="lens-change-submit">
			</form>
		</div>
		<br class="clear" />
	</div>
	
	<div id="slidedeck-lens-editor-side">
	    
	    <h3>Lens Files</h3>
	    
	    <ul>
	        <?php foreach( $lens_files as $lens_file ): ?>
                <li>
                    <?php
                        $file_basename = basename( $lens_file );
                        $label = array_key_exists( $file_basename, $lens_file_labels ) ? $lens_file_labels[$file_basename] : $file_basename;
                    ?>
                    <a href="<?php echo slidedeck2_action( "/lenses&action=edit&slidedeck-lens={$lens['slug']}&filename=" . $file_basename ); ?>"><?php echo $label; ?></a><br />
                    <span class="nonessential"><?php echo $file_basename; ?></span>
                </li>
	        <?php endforeach; ?>
	    </ul>
	    
	</div>
    
    <form action="<?php echo slidedeck2_action( '/lenses' ); ?>" method="post" id="slidedeck-lens-editor">
        
        <?php wp_nonce_field( "{$namespace}-save-lens" ); ?>
		<input type="hidden" name="lens" value="<?php echo $lens['slug']; ?>" />
        <input type="hidden" name="filename" id="slidedeck-lens-filename" value="<?php echo basename( $lens_filename ); ?>" />
        
        <fieldset class="lens-content">
            
            <div class="textarea-wrapper">
                <textarea name="lens_content" rows="20" cols="120" autofocus="autofocus"<?php if( $read_only ) echo ' readonly="readonly"'; ?>><?php echo $lens_file_content; ?></textarea>
            </div>
            
        </fieldset>
        
        <?php
            // Only include the lens meta section if this is the lens' primary CSS file
            if( basename( $lens_filename ) == basename( $lens['files']['css'] ) ):
        ?>
            
            <table class="form-table meta">
                
                <h3 class="title">Lens Meta Information</h3>
                
                <tbody>
                    <tr>
                        <th scope="row">
                        	<label for="slidedeck_lens_name"><?php _e( "Name", $namespace ); ?></label>
                    	</th>
                    	<td>
                    		<input type="text" name="data[name]" size="40" maxlength="255" id="slidedeck_lens_name" value="<?php echo $lens['meta']['name']; ?>"<?php if( $read_only ) echo ' readonly="readonly"'; ?> />
                		</td>
                    </tr>
                    <tr>
                    	<th scope="row">
                			<label id="slidedeck_lens_uri"><?php _e( "Lens URI", $namespace ); ?></label>
                    	</th>
                        <td>
                        	<input type="text" name="data[uri]" size="40" maxlength="255" id="slidedeck_lens_uri" value="<?php echo $lens['meta']['uri']; ?>"<?php if( $read_only ) echo ' readonly="readonly"'; ?> />
                    	</td>
                    </tr>
                    <tr id="slidedeck-lens-content-sources">
                    	<th scope="row"><?php _e( "Content Sources:", $namespace ); ?></th>
                        <td>
    	                    <fieldset>
    	                    	<legend class="screen-reader-text">
    	                    		<span><?php _e( "Content Sources:", $namespace ); ?></span>
    	                    	</legend>
                                <?php foreach( $sources as $source ): ?>
                                <?php if( $source->name == 'twitter' ) { continue; } ?>
    		                        <label>
    			                        <input type="checkbox" value="<?php echo $source->name; ?>" name="data[sources][]"<?php if( in_array( $source->name, $lens['meta']['sources'] ) ) echo ' checked="checked"'; ?><?php if( $read_only ) echo ' disabled="disabled"'; ?> />
    			                        <?php echo $source->label; ?>
    		                        </label>
		                        <?php endforeach; ?>
    	                    </fieldset>
                        </td>
                    </tr>
                    <tr>
                    	<th scope="row">
                    		<label for="slidedeck_lens_version"><?php _e( "Version", $namespace ); ?></label>
                    	</th>
                        <td>
                        	<input type="text" name="data[version]" size="5" maxlength="5" id="slidedeck_lens_version" value="<?php echo $lens['meta']['version']; ?>"<?php if( $read_only ) echo ' readonly="readonly"'; ?> />
                    	</td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label for="slidedeck_lens_author"><?php _e( "Author", $namespace ); ?></label>
                        </th>
                        <td>
                            <input type="text" name="data[author]" size="60" maxlength="255" id="slidedeck_lens_author" value="<?php echo $lens['meta']['author']; ?>"<?php if( $read_only ) echo ' readonly="readonly"'; ?> />
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label for="slidedeck_lens_author-uri"><?php _e( "Author URI", $namespace ); ?></label>
                        </th>
                        <td>
                            <input type="text" name="data[author_uri]" size="60" maxlength="255" id="slidedeck_lens_author-uri" value="<?php echo $lens['meta']['author_uri']; ?>"<?php if( $read_only ) echo ' readonly="readonly"'; ?> />
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label for="slidedeck_lens_contributors"><?php _e( "Contributors", $namespace ); ?></label>
                        </th>
                        <td>
                            <input type="text" name="data[contributors]" size="60" maxlength="255" id="slidedeck_lens_contributors" value="<?php echo implode( ', ', $lens['meta']['contributors'] ); ?>"<?php if( $read_only ) echo ' readonly="readonly"'; ?> /><br />
                            <em><?php _e( "Comma delimited list of contributors to this lens" ); ?></em>
                        </td>
                    </tr>
                    <tr>
                    	<th scope="row">
                    		<label for="slidedeck_lens_description"><?php _e( "Description", $namespace ); ?></label>
                    	</th>
    					<td>                    
    						<input type="text" name="data[description]" size="60" maxlength="255" id="slidedeck_lens_description" value="<?php echo $lens['meta']['description']; ?>"<?php if( $read_only ) echo ' readonly="readonly"'; ?> /><br />
    						<em><?php _e( "A short, one sentence description of this lens" ); ?></em>
    					</td>
                    </tr>
                    <tr>
                    	<th scope="row">
                    		<label for="slidedeck_lens_tags"><?php _e( "Tags", $namespace ); ?></label>
                    	</th>
                        <td>
                        	<input type="text" name="data[tags]" size="60" maxlength="255" value="<?php echo implode( ', ', $lens['meta']['tags'] ); ?>"<?php if( $read_only ) echo ' readonly="readonly"'; ?> /><br />
                        	<em><?php _e( "Comma delimited list of tags that describe properties of this lens" ); ?></em>
                    	</td>
                    </tr>
                    <tr>
                    	<th scope="row">
                    		<label for="slidedeck_lens_variations"><?php _e( "CSS Variations", $namespace ); ?></label>
                    	</th>
                        <td>
                        	<input type="text" name="data[variations]" id="slidedeck_lens_variations" size="60" maxlength="255" value="<?php echo implode( ',', $lens['meta']['variations'] ); ?>"<?php if( $read_only ) echo ' readonly="readonly"'; ?> /><br />
                        	<em><?php _e( "Comma delimited list of extra classes to apply to the SlideDeck for lens variations" ); ?></em>
                    	</td>
                    </tr>
                    <tr>
                    	<th scope="row">
                    		<label for="slidedeck_default_nav_styles"><?php _e( "Default Nav Styles", $namespace ); ?></label>
                    	</th>
                        <td>
                        	<input type="checkbox" value="1" name="data[default_nav_styles]" id="slidedeck_default_nav_styles"<?php if( $lens['meta']['default_nav_styles'] == true ) echo ' checked="checked"'; ?> />
                        	<em><?php _e( "Use default CSS navigation styles" ); ?></em>
                    	</td>
                    </tr>
                </tbody>
                
            </table>
            
        <?php endif; ?>
        
        <?php if( !$read_only ): ?>
            <p><input type="submit" class="button-primary" value="<?php _e( "Update Lens File", $namespace ); ?>" /></p>
        <?php endif; ?>
        
    </form>
    
</div>