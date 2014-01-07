<fieldset id="slidedeck-section-custom-css" class="slidedeck-form-section collapsible clearfix<?php echo empty( $custom_css ) ? ' closed' : '' ; ?>">
    <div class="hndl-container">
        <h3 class="hndl"><span class="indicator"></span>Custom CSS</h3>
    </div>
    <div class="inner clearfix" <?php echo empty( $custom_css ) ? ' style="height:0px;"' : '' ; ?>>
        <div class="instructions">
            <p>
                <?php echo $help_message; ?>
            </p>
        </div>
        <div id="custom-slidedeck-css">
            <textarea name="custom_css"><?php echo $custom_css; ?></textarea>
        </div>
    </div>
</fieldset>
