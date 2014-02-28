jQuery(document).ready(function() {
	jQuery('.preview_gallery').click(function(){
		var url = jQuery(this).attr("href");
		var data = jQuery('.a3rev_panel_container').find('form').serialize();
		var height = 500;
		if ( jQuery('.a3rev_panel_container').find('form').find("#wpsc_dgallery_style_setting_width_type:checked").val() == '%') {
			var gallery_height = 350;
		} else {
			var gallery_height = jQuery('.a3rev_panel_container').find('form').find('.wpsc_product_gallery_height').val();
		}
		var navbar_height = jQuery('.a3rev_panel_container').find('form').find('.wpsc_dgallery_navbar_height').val();
		var thumb_height = 75;
		height = parseInt(gallery_height) + parseInt(navbar_height) + parseInt(thumb_height) + 80;
		tb_show('Dynamic gallery preview', url+'&width=700&height='+height+'&action=wpsc_dynamic_gallery&KeepThis=false&'+data);
		return false;
	});
});