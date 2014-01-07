<?php
/**
 * SlideDeck Facebook Content Source
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

<div id="content-source-facebook">
    <input type="hidden" name="source[]" value="<?php echo $this->name; ?>" />
    <div class="inner">
        <ul class="content-source-fields">
            <li>
				<?php $tooltip =  __( 'This can be the name of any page', $namespace ) ?>
			    <?php slidedeck2_html_input( 'options[facebook_page_name]', $slidedeck['options']['facebook_page_name'], array( 'label' => __( "Page Name", $namespace ) . '<span class="tooltip" title="' . $tooltip . '"></span>', array( 'size' => 20, 'maxlength' => 255 ) ) ); ?>
            </li>
            <li>
				<?php $tooltip =  __( 'Choose whether to show all posts or just your own.', $namespace ) ?>
			    <?php slidedeck2_html_input( 'options[facebook_page_show_other_users]', $slidedeck['options']['facebook_page_show_other_users'], array( 'type' => 'radio', 'attr' => array( 'class' => 'fancy' ), 'label' => __( 'Which Posts?', $namespace ), 'values' => array(
			        'page' => __( 'Posts by Page', $namespace ),
			        'everyone' => __( 'All Posts', $namespace )
			    ), 'description' => "Choose whether to show all posts or just posts by page owner." ) ); ?>
            </li>
            <li>
				<?php 
					$tooltip =  __( 'Facebook\'s API requires an access token to get your content.', $namespace );
					$tooltip .= '<br />';
				?>
				<?php slidedeck2_html_input( 'options[facebook_access_token]', $token, array( 'type' => 'password', 'label' => __( "Access Token", $namespace ) . '<span class="tooltip" title="' . $tooltip . '"></span>', array( 'size' => 40, 'maxlength' => 255 ), 'required' => true ) ); ?>
				<em class="note-below">Facebook expires access tokens after 60 days, we will still display your cached content in the event that your access token expires.<br/><br/>Get your access token <a href="<?php echo admin_url( 'admin-ajax.php' ); ?>?action=<?php echo $namespace; ?>_get_facebook_access_token&_wpnonce_get_facebook_access_token=<?php echo wp_create_nonce( $namespace . '-get-facebook-access-token' ); ?>" id="get-facebook-access-token-link">here</a>.</em>
            </li>
        </ul>
    </div>
</div>