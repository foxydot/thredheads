<?php global $wpalchemy_media_access; ?>
<style>
    .my_meta_control .table {display: table;}
    .my_meta_control .row {display: table-row;padding-left: 1em;cursor: move;}
    .my_meta_control .cell {display: table-cell;padding: 0 6px;}
    .my_meta_control .cell:first-child {padding-left: 20px;}
    .even {background: #eee;}
    .odd {background: #fff;}
    .file input[type="text"] {width: 75%}
</style>
<div class="my_meta_control">
    <h4>Other Documents</h4>

    <div class="table">
    <?php $i = 0; ?>
    <?php while($mb->have_fields_and_multi('otherdocs')): ?>
    <?php $mb->the_group_open(); ?>
    <?php if($i == 0){?>
    <div class="row">
        <div class="cell">Group</div>
        <div class="cell">Title</div>
        <div class="cell">Download URL</div>
    </div>
    <?php } ?>
    <div class="row <?php print $i%2==0?'even':'odd'; ?>">
        <div class="cell">
        <?php $mb->the_field('group'); ?>
        <input type="text" name="<?php $mb->the_name(); ?>" value="<?php $mb->the_value(); ?>"/>
        </div>
        <div class="cell">
        <?php $mb->the_field('title'); ?>
        <input type="text" name="<?php $mb->the_name(); ?>" value="<?php $mb->the_value(); ?>"/>
        </div><div class="cell file">
        <?php $mb->the_field('downloadurl'); ?>
        <?php $wpalchemy_media_access->setGroupName('otherdocdl'. $mb->get_the_index())->setInsertButtonLabel('Insert This')->setTab('gallery'); ?>
        
        <?php echo $wpalchemy_media_access->getField(array('name' => $mb->get_the_name(), 'value' => $mb->get_the_value())); ?>
        <?php echo $wpalchemy_media_access->getButton(array('label' => '+')); ?>
        </div>
        <div class="cell">
            <a href="#" class="dodelete button">Remove Document</a>
        </div>
    </div>
    <?php $i++; ?>
    <?php $mb->the_group_close(); ?>
    <?php endwhile; ?>
    </div>
    <p style="margin-bottom:15px; padding-top:5px;"><a href="#" class="docopy-otherdocs button">Add Document</a>
    <a href="#" class="dodelete-otherdocs button">Remove All Documents</a></p>
</div>