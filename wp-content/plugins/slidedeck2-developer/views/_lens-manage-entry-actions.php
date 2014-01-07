<a href="<?php echo slidedeck2_action( "/lenses" ); ?>&action=copy&lens=<?php echo $lens['slug']; ?>" class="copy-lens"><?php _e( "Copy", $namespace ); ?></a>

<?php if( !$lens['is_protected'] ): ?>
    <a href="<?php echo slidedeck2_action( "/lenses&action=edit&slidedeck-lens={$lens['slug']}" ); ?>" class="edit-lens"><?php _e( "Edit", $namespace ); ?></a>
<?php endif; ?>
