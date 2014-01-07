jQuery(document).ready(function() {
	jQuery('.pluginbuddy_tip').tooltip({ 
		track: true, 
		delay: 0, 
		showURL: false, 
		showBody: " - ", 
		fade: 250 
	});
	jQuery('.option_toggle').change(function(e) {
		if (jQuery(this).attr('checked')) {
			jQuery('.' + jQuery(this).attr('id') + '_toggle' ).show();
		} else {
			jQuery('.' + jQuery(this).attr('id') + '_toggle' ).hide();
		}
	});
});

