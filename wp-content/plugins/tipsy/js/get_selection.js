jQuery( document ).ready( function( $ ) {
	//Get selected text function
	jQuery.pb_tipsy = {
		insert_content: function(myField, myValue) {
			var sel, startPos, endPos, scrollTop;

			//IE support
			if (document.selection) {
				myField.focus();
				sel = document.selection.createRange();
				sel.text = myValue;
				myField.focus();
			}
			//MOZILLA/NETSCAPE support
			else if (myField.selectionStart || myField.selectionStart == '0') {
				startPos = myField.selectionStart;
				endPos = myField.selectionEnd;
				scrollTop = myField.scrollTop;
				myField.value = myField.value.substring(0, startPos)
					      + myValue
				      + myField.value.substring(endPos, myField.value.length);
				myField.focus();
				myField.selectionStart = startPos + myValue.length;
				myField.selectionEnd = startPos + myValue.length;
				myField.scrollTop = scrollTop;
			} else {
				myField.value += myValue;
				myField.focus();
			}
		},
		create_shortcode: function(myField, content, group_id) {
			var content_attr =  ' content="' + content +'" ';
			var group_attr = ' group="' + group_id + '" ';
			var short_start = '[tipsy' + content_attr + group_attr + ']';
			var short_end = '[/tipsy]'; 

			var is_tinyMCE_active = false;
			 
			if (typeof(tinyMCE) != "undefined" && ( tinyMCE.activeEditor != null && tinyMCE.activeEditor != "null" ) ) {
				var is_hidden = true;  
				if ( typeof tinyMCE.activeEditor.isHidden != 'undefined' ) {
					is_hidden = tinyMCE.activeEditor.isHidden();
				}
				if ( typeof( tinyMCE.activeEditor ) != 'undefined' && is_hidden == false ) {
					is_tinyMCE_active = true;
				} 				
			}
			if ( is_tinyMCE_active == true ) {
				//Tiny MCE editor is active
				var tinymce_selected_content = tinyMCE.activeEditor.selection.getContent();
                		tinyMCE.activeEditor.selection.setContent( short_start + tinymce_selected_content + short_end );
			} else {
				//Tiny MCE is not active - we're in HTML mode
				//IE support
				if (document.selection) {
					myField.focus();
				    var sel = document.selection.createRange();
					if (sel.text.length > 0) {
						sel.text = short_start + sel.text + short_end;
					}
					myField.focus();
				}
				//MOZILLA/NETSCAPE support
				else if (myField.selectionStart || myField.selectionStart == '0') {
					var startPos = myField.selectionStart, endPos = myField.selectionEnd, cursorPos = endPos, scrollTop = myField.scrollTop;

					if (startPos != endPos) {
						myField.value = myField.value.substring(0, startPos)
							      + short_start
							      + myField.value.substring(startPos, endPos)
							      + short_end
							      + myField.value.substring(endPos, myField.value.length);
						cursorPos += short_start.length + short_end.length;
					}
					myField.focus();
					myField.selectionStart = cursorPos;
					myField.selectionEnd = cursorPos;
					myField.scrollTop = scrollTop;
				}
			} //end if tinymce_active
			
			
			
		} /*end function create_shortcode*/		
	};
	//prefilling content 
	var select_default = jQuery('#pb_tipsy_group_selection').val();
	jQuery( '#pb_tipsy_content' ).val(
		jQuery.trim( jQuery( '#pb_tipsy_' + select_default ).val() )
	);
	jQuery( '#pb_tipsy_content' ).show();
	
	//Fills the textarea with the group content
	jQuery( '#pb_tipsy_group_selection' ).change( function() {
		jQuery( '#pb_tipsy_content' ).val(
			jQuery.trim( jQuery( '#pb_tipsy_' + jQuery(this).val() ).val() )
		);
		jQuery( '#pb_tipsy_content' ).show();
	} );
	
	//Insert Shortcode event
	jQuery( '#pb_tipsy_save' ).bind( 'click', function() {
		var $textarea = jQuery('#content');
		var group_id = jQuery( '#pb_tipsy_group_selection' ).val();	

		var content_text = jQuery( '#pb_tipsy_content' ).val();

		var content_text = content_text.replace( new RegExp( "\\n", "g" ), '' );

		jQuery.pb_tipsy.create_shortcode($textarea[0], content_text, group_id);
		tb_remove();
		return false;
	} );
} );

