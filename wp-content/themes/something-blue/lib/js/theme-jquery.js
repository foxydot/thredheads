jQuery(document).ready(function($) {	
	$('*:first-child').addClass('first-child');
	$('*:last-child').addClass('last-child');
	$('*:nth-child(even)').addClass('even');
	$('*:nth-child(odd)').addClass('odd');


	$('#footer-widgets div.widget:first-child').addClass('first-child');
	$('#footer-widgets div.widget:last-child').addClass('last-child');
	$('#footer-widgets div.widget:nth-child(even)').addClass('even');
	$('#footer-widgets div.widget:nth-child(odd)').addClass('odd');
	
	var numwidgets = $('#footer-widgets div.widget').length;
	$('#footer-widgets').addClass('cols-'+numwidgets);
	
	//special for lifestyle
	$('.ftr-menu ul.menu>li').after(function(){
		if(!$(this).hasClass('last-child') && $(this).hasClass('menu-item') && $(this).css('display')!='none'){
			return '<li class="separator">|</li>';
		}
	});
	
	//bootstrap
    $('.site-inner').addClass('container');
    //$('.wrap').addClass('row');
    $('.content-sidebar .content-sidebar-wrap').addClass('row');
    $('.content-sidebar main.content').addClass('col-md-8 col-sm-12');
    $('.content-sidebar div.sidebar').addClass('col-md-4');
    
    //icons
    $('.menu li[class*="icon-"]>a').prepend('<i></i>');
    
    $('.menu-primary .home-btn a').html('<i class="fa fa-home"></i>');
    $('.menu-primary .cart-btn a').html('<i class="fa fa-shopping-cart"></i>');
    
    $('.pre-header .wrap .header-widget-area .gform_widget .gform_wrapper .gform_footer').after('<br /><div class="gform_post_footer"><button type="button" class="button">Thanks, just browsing</button></div>');
    var formwrapper = $('.pre-header .wrap .header-widget-area .gform_widget .gform_wrapper');
    $('.pre-header .wrap .header-widget-area .gform_widget .widget-title,.pre-header .gform_widget .gform_post_footer .button,.pre-header .gform_widget .gform_post_footer .button').click(function(){
        if(!formwrapper.hasClass('open_form')){
            formwrapper.addClass('open_form');
        } else {
            formwrapper.removeClass('open_form');
        }
    });
    
	
	/*RESPONSIVE NAVIGATION, COMBINES MENUS EXCEPT FOR FOOTER MENU*/

    //jQuery('.menu').not('#footer .menu, #footer-widgets .menu').wrap('<div id="nav-response" class="nav-responsive">');
    jQuery('#menu-primary-links').wrap('<div id="nav-response" class="nav-responsive">');
    jQuery('#nav-response').append('<a href="#" id="pull" class="closed"><strong>MENU</strong></a>');   
    
    //move the search box
    if(jQuery('#pull').css('display') != 'none'){
        var mysearch = jQuery('.nav-responsive').find('li.search');
        jQuery('#pull').before(mysearch);
    }
    
    //combinate
    sf_duplicate_menu( jQuery('.nav-responsive>ul'), jQuery('#pull'), 'mobile_menu', 'sf_mobile_menu' );
    
            
            function sf_duplicate_menu( menu, append_to, menu_id, menu_class ){
                var jQuerycloned_nav;
                
                menu.clone().attr('id',menu_id).removeClass().attr('class',menu_class).appendTo( append_to );
                jQuerycloned_nav = append_to.find('> ul');
                jQuerycloned_nav.find('.menu_slide').remove();
                jQuerycloned_nav.find('li:first').addClass('sf_first_mobile_item');
                
                append_to.click( function(){
                    if ( jQuery(this).hasClass('closed') ){
                        jQuery(this).removeClass( 'closed' ).addClass( 'opened' );
                        jQuerycloned_nav.slideDown( 500 );
                    } else {
                        jQuery(this).removeClass( 'opened' ).addClass( 'closed' );
                        jQuerycloned_nav.slideUp( 500 );
                    }
                    return false;
                } );
                
                append_to.find('a').click( function(event){
                    event.stopPropagation();
                } );
            }
});