jQuery(document).ready(function(e){e(".conferences .widget-title").prepend('<span class="fa-stack"><i class="fa fa-circle fa-stack-2x"></i><i class="fa fa-comments fa-stack-1x fa-inverse"></i></span>');e(".white-papers .widget-icon i.fa").addClass("fa-file-text-o");e(".case-studies .widget-icon i.fa").addClass("fa-folder-o");e(".featured-article .widget-icon i.fa").addClass("fa-bookmark-o");e(".right .readmore").append('<i class="fa fa-chevron-circle-right"></i>');e(".carousel").carousel({interval:4e3})});