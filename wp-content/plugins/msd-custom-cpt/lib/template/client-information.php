<ul class="location_meta_control">
<?php while($mb->have_fields('client',1)): ?>
    <li>
    <?php $metabox->the_field('name'); ?>
    <label id="<?php $metabox->the_name(); ?>_label" for="<?php $metabox->the_name(); ?>">Client Name</label>
    <div class="ginput_container"><input type="text" tabindex="1" value="<?php $metabox->the_value(); ?>" id="<?php $metabox->the_name(); ?>" name="<?php $metabox->the_name(); ?>"></div>
    </li>
<?php endwhile; ?>
</ul>