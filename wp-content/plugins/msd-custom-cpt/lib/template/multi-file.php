<?php global $wpalchemy_media_access; ?>
<div class="my_meta_control">
 <p id="warning" style="display: none;background:lightYellow;border:1px solid #E6DB55;padding:5px;">Order has changed. Please click Save or Update to preserve order.</p>

    <h4>Event Date</h4>  
    
    <?php $mb->the_field('event_date'); ?>
    <input type="text" class="datepicker" name="<?php $mb->the_name(); ?>" value="<?php $mb->the_value(); ?>"/>
     
    <h4>Add Files</h4>

    <div class="table">
    <?php $i = 0; ?>
    <?php while($mb->have_fields_and_multi('otherfiles')): ?>
    <?php $mb->the_group_open(); ?>
    <?php if($i == 0){?>
    <div class="row">
        <div class="cell">Thumb</div>
        <div class="cell">Title</div>
        <div class="cell">Download URL</div>
    </div>
    <?php } ?>
    <div class="row <?php print $i%2==0?'even':'odd'; ?>">
        <div class="cell">
            <img src="<?php print $mb->get_the_value(downloadurl); ?>" height="72" width="72" />
        </div>
        <div class="cell">
        <?php $mb->the_field('title'); ?>
        <input type="text" name="<?php $mb->the_name(); ?>" value="<?php $mb->the_value(); ?>"/>
        </div><div class="cell file">
        <?php $mb->the_field('downloadurl'); ?>
        <?php $groupname = 'otherfiles_'. $mb->get_the_index(); ?>
        <?php $wpalchemy_media_access->setGroupName($groupname)->setInsertButtonLabel('Insert This')->setTab('gallery'); ?>
        
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
    <p style="margin-bottom:15px; padding-top:5px;"><a href="#" class="docopy-otherfiles button">Add Document</a>
    <a href="#" class="dodelete-otherfiles button">Remove All Documents</a></p>
</div>
<script>
jQuery(function($){
    $("#wpa_loop-otherfiles").sortable({
        change: function(){
            $("#warning").show();
        }
    });
    $( ".datepicker" ).datepicker();
});
</script>