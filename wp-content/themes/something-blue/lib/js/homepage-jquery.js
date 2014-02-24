jQuery(document).ready(function($) {
    $('.conferences .widget-title').prepend('<span class="fa-stack"><i class="fa fa-circle fa-stack-2x"></i><i class="fa fa-comments fa-stack-1x fa-inverse"></i></span>');
    $('.white-papers .widget-icon i.fa').addClass('fa-file-text-o');
    $('.case-studies .widget-icon i.fa').addClass('fa-folder-o');
    $('.featured-article .widget-icon i.fa').addClass('fa-bookmark-o');
    $('.right .readmore').append('<i class="fa fa-chevron-circle-right"></i>');
    $('.carousel').carousel({
       interval: 10000
    });
   // $('.gform_widget .gform_footer').after('<div class="gform_post_footer"><a href="http://thredheads-store.com" target="_blank"><button type="button" class="button" data-dismiss="modal">Thanks, just browsing</button></a></div>');
   /* $('.modal').wrapInner('<div class="modal-dialog"><div class="modal-content"></div></div>');
    setTimeout(function(){ 
        $('.modal').modal('show');
    }, 5000);
    */
});