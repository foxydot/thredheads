function setupColorPicker(colorPicker, id) {
	colorPicker.hide();
	
	var pageHeight = JTTScreen.getDocumentHeight();
	var position = jQuery("#" + id).position();
	var height = jQuery("#" + id).outerHeight();
	var wrapperHeight = jQuery("#" + id + "_ColorPickerWrapper").outerHeight();
	
	var top = 0;
	
	if(((position.top - wrapperHeight) <= 0) || ((pageHeight) > (position.top + parseInt(height) + 5 + wrapperHeight))) {
		top = position.top + parseInt(height) + 5;
	}
	else {
		top = position.top - wrapperHeight;
	}
	
	jQuery("#" + id + "_ColorPickerWrapper").css("left", position.left).css("top", top);
	
	jQuery("#show_" + id + "_picker,#" + id + "_hide_div").click(
		function(e) {
			colorPickerToggle(colorPicker, id);
		}
	);
}

function colorPickerToggle(colorPicker, id, show) {
	if(show == "show") {
		jQuery("#" + id + "_ColorPickerWrapper").fadeIn("fast");
		colorPicker.show();
		
		jQuery("#show_" + id + "_picker").attr("value", "Hide Picker");
	}
	else if(show == "hide") {
		colorPicker.hide();
		jQuery("#" + id + "_ColorPickerWrapper").fadeOut("fast");
		
		jQuery("#show_" + id + "_picker").attr("value", "Show Picker");
	}
	else if((jQuery("#" + id + "_ColorPickerWrapper").css("display") == 'none')) {
		jQuery("#" + id + "_ColorPickerWrapper").fadeIn("fast");
		colorPicker.show();
		
		jQuery("#show_" + id + "_picker").attr("value", "Hide Picker");
	}
	else {
		colorPicker.hide();
		jQuery("#" + id + "_ColorPickerWrapper").fadeOut("fast");
		
		jQuery("#show_" + id + "_picker").attr("value", "Show Picker");
	}
}

jQuery(document).ready(
	function(){
		var match = jQuery("img[src*='js/colorpicker/images/mappoint.gif']").attr("src").match(/^(.+)\/js\/colorpicker\/images\/mappoint\.gif/);
		var url_base = match[1];
		
		overlayHeaderColor = new Refresh.Web.ColorPicker('overlay_header_color', '#overlay_header_color', {startHex: jQuery("#overlay_header_color").attr("value").substr(1), startMode: 's', clientFilesPath: url_base + '/js/colorpicker/images/'});
		setupColorPicker(overlayHeaderColor, 'overlay_header_color');
		
		overlaySubheaderColor = new Refresh.Web.ColorPicker('overlay_subheader_color', '#overlay_subheader_color', {startHex: jQuery("#overlay_subheader_color").attr("value").substr(1), startMode: 's', clientFilesPath: url_base + '/js/colorpicker/images/'});
		setupColorPicker(overlaySubheaderColor, 'overlay_subheader_color');
		
		
		if(!jQuery(".enable_fade").attr('checked')) {
			jQuery("#fade-options").hide();
		}
		
		jQuery(".enable_fade").change(
			function(e) {
				if(jQuery(".enable_fade").attr('checked')) {
					jQuery("#fade-options").fadeIn();
				}
				else {
					jQuery("#fade-options").fadeOut();
				}
			}
		);
		
		
		if(!jQuery(".enable_overlay").attr('checked')) {
			jQuery("#text-overlay-options").hide();
		}
		
		jQuery(".enable_overlay").change(
			function(e) {
				if(jQuery(".enable_overlay").attr('checked')) {
					jQuery("#text-overlay-options").fadeIn();
				}
				else {
					jQuery("#text-overlay-options").fadeOut();
				}
			}
		);
		
		jQuery("input.entries").change(
			function(e) {
				if(!jQuery(this).attr("checked")) {
					jQuery(".check-all-entries").attr("checked", "");
				}
			}
		);
		
		jQuery('input[name=save_entry_order]').click(
			function(e) {
				window.onbeforeunload = null;
			}
		);
		
		
		iThemesAddClickEvents();
	}
);

var iThemesReminder = false;

function iThemesDoReminder() {
	if(iThemesReminder) {
		return;
	}
	
	window.onbeforeunload = function () { return 'You must click the "Save Order" button in order to save changes to the entry order.'; };
	
	iThemesReminder = true;
}

function iThemesAddClickEvents() {
	jQuery(".entry-up").click(
		function(e) {
			row = jQuery(this).parents(".entry-row");
			
			if(!row.is(":first-child")) {
				newRow = row.clone();
				
				newRow.find(".entry-order").attr("value", (parseInt(newRow.find(".entry-order").attr("value")) - 1));
				row.prev().find(".entry-order").attr("value", (parseInt(row.prev().find(".entry-order").attr("value")) + 1));
				
				newRow.insertBefore(row.prev());
				
				row.remove();
				
				jQuery(".entry-row").removeClass("alternate");
				jQuery(".entry-row:even").addClass("alternate");
				
				iThemesAddClickEvents();
				iThemesDoReminder();
			}
		}
	);
	
	jQuery(".entry-down").click(
		function(e) {
			row = jQuery(this).parents(".entry-row");
			
			if(!row.is(":last-child")) {
				newRow = row.clone();
				
				newRow.find(".entry-order").attr("value", (parseInt(newRow.find(".entry-order").attr("value")) + 1));
				row.next().find(".entry-order").attr("value", (parseInt(row.next().find(".entry-order").attr("value")) - 1));
				
				newRow.insertAfter(row.next());
				
				row.remove();
				
				jQuery(".entry-row").removeClass("alternate");
				jQuery(".entry-row:even").addClass("alternate");
				
				iThemesAddClickEvents();
				iThemesDoReminder();
			}
		}
	);
}
