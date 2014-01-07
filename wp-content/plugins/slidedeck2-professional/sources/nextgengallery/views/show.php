<?php
/**
 * SlideDeck NextGEN Gallery Content Source
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

$gallery_hidden = ( $slidedeck['options']['ngg_gallery_or_album'] == 'album' ) ? ' style="display: none;"' : '';
$album_hidden = ( $slidedeck['options']['ngg_gallery_or_album'] == 'gallery' ) ? ' style="display: none;"' : '';
?>
<div id="content-source-ngg"> 
    <input type="hidden" name="source[]" value="<?php echo $this->name; ?>" />
    <div class="inner">
<?php if( $ngg_active ) : ?>
        <ul class="content-source-fields">
    <?php if( $at_least_one_gallery ) : ?>
        <?php if( $at_least_one_album ) : ?>
            <li>
                <?php slidedeck2_html_input( 'options[ngg_gallery_or_album]', $slidedeck['options']['ngg_gallery_or_album'], array( 'type' => 'radio', 'label' => __( "Images From", $this->namespace ), 'attr' => array( 'class' => 'fancy' ), 'values' => array( 
                    'gallery' => __( "Gallery", $this->namespace ),
                    'album' => __( "Album", $this->namespace )
                 ) ) ); ?>
            </li>
        <?php endif; ?>

            <li class="nextgen-album"<?php echo $album_hidden; ?>>
                <?php if( $ngg_album_select ): ?>
                <div id="ngg_albums">
                    <?php echo $ngg_album_select; ?>
                </div>
                <?php endif; ?>
            </li>

            <li class="nextgen-gallery"<?php echo $gallery_hidden; ?>>
                <?php if( $ngg_gallery_select ): ?>
                <div id="ngg_galleries">
                    <?php echo $ngg_gallery_select; ?>
                </div>
                <?php endif; ?>
            </li>

        </ul>
    <?php else: ?>
    <p>
        NextGEN Gallery is installed and activated, but it looks like there aren&rsquo;t any galleries. Before you can use this content source, you&rsquo;ll need to make sure you&rsquo;ve added some images to NextGEN Gallery.
    </p>
    <?php endif; ?>
<?php else: ?>
    <p>
        Whoops! It looks like NextGEN Gallery isn&rsquo;t activated on this site. If you&rsquo;ve already installed NextGEN Gallery, you&rsquo;ll need to activate it on <a href="<?php echo admin_url('plugins.php'); ?>">the plugin page</a>.
    </p>
    <p>
        If you haven&rsquo;t installed NextGEN Gallery yet, you can find more info about it in the <a href="http://wordpress.org/plugins/nextgen-gallery/" target="_blank">WordPress plugin repository</a>.
    </p>
<?php endif; ?>

    </div>
</div>
