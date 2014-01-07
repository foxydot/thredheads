jQuery(document).ready(function($) {
    $('.conferences .widget-title').prepend('<span class="fa-stack"><i class="fa fa-circle fa-stack-2x"></i><i class="fa fa-comments fa-stack-1x fa-inverse"></i></span>');
    $('.white-papers .widget-icon i.fa').addClass('fa-file-text-o');
    $('.case-studies .widget-icon i.fa').addClass('fa-folder-o');
    $('.featured-article .widget-icon i.fa').addClass('fa-bookmark-o');
    $('.right .readmore').append('<i class="fa fa-chevron-circle-right"></i>');
    $('.carousel').carousel();
});

