<div id="support-modal" class="support-modal">
    <div class="slidedeck-header">
        <h1><?php _e( "Still Have Questions?", $this->namespace ); ?></h1>
    </div>
    <div class="background">
        <div class="inner">
            <div class="copyblock">
                <div class="support-frame-wrapper">
                   <iframe height="100%" frameborder="0" scrolling="no" width="100%" allowtransparency="true" src="<?php echo $support_modal_url; ?>"></iframe>
                </div>
            </div>
            <div class="cta">
                <h3>Can't find an answer to your question?</h3>
                <a class="slidedeck-noisy-button" href="<?php admin_url( 'admin.php' ); ?>?page=slidedeck2.php/support" class="button slidedeck-noisy-button"><span><span>Get Support</span></span></a>
                <span>We'll get back to you in 24-48 hours, M-F 8am-5pm, PST</span>
            </div>
        </div>
    </div>
</div>