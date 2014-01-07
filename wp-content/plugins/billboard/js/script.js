jQuery(function($) {
	$(document).ready(function() {
		$("#resize").change(
			function(e) {
				$("#width-container").hide();
				$("#height-container").hide();
				
				if(($(this).attr("value") == 'width') || ($(this).attr("value") == 'bothcrop') || ($(this).attr("value") == 'bothnocrop')) {
					$("#width-container").show();
				}
				if(($(this).attr("value") == 'height') || ($(this).attr("value") == 'bothcrop') || ($(this).attr("value") == 'bothnocrop')) {
					$("#height-container").show();
				}
			}
		);
		
		$("#check-all-groups").change(
			function(e) {
				if($(this).attr("checked")) {
					$(".groups").attr("checked", "checked");
				}
			}
		);
		
		$(".groups").change(
			function(e) {
				if(!$(this).attr("checked")) {
					$("#check-all-groups").attr("checked", "");
				}
			}
		);
		
		$('input[name=save_entry_order]').css('background-color', '#ffffe0').click(
			function(e) {
				window.onbeforeunload = null;
			}
		);
		
		
		iThemesAddClickEvents();
	});
	
	var iThemesReminder = false;
	
	function iThemesDoReminder() {
		if(iThemesReminder) {
			return;
		}
		
		window.onbeforeunload = function () { return 'You must click the "Save Order" button in order to save changes to the entry order.'; };
		
		iThemesReminder = true;
	}
	
	function iThemesAddClickEvents() {
		$(".entry-up").click(
			function(e) {
				$row = $(this).parents(".entry-row");
				
				if(($row.find(".entry-priority").text() != 'Top') && ($row.prev().find(".entry-priority").text() == 'Top')) {
					return true;
				}
				
				if(!$row.is(":first-child")) {
					$newRow = $row.clone();
					
					$newRow.find(".entry-order").attr("value", (parseInt($newRow.find(".entry-order").attr("value")) - 1));
					$row.prev().find(".entry-order").attr("value", (parseInt($row.prev().find(".entry-order").attr("value")) + 1));
					
					$newRow.insertBefore($row.prev());
					
					$row.remove();
					
					iThemesAddClickEvents();
					iThemesDoReminder();
				}
			}
		);
		
		$(".entry-down").click(
			function(e) {
				$row = $(this).parents(".entry-row");
				
				if(($row.find(".entry-priority").text() == 'Top') && ($row.next().find(".entry-priority").text() != 'Top')) {
					return true;
				}
				
				if(!$row.is(":last-child")) {
					$newRow = $row.clone();
					
					$newRow.find(".entry-order").attr("value", (parseInt($newRow.find(".entry-order").attr("value")) + 1));
					$row.next().find(".entry-order").attr("value", (parseInt($row.next().find(".entry-order").attr("value")) - 1));
					
					$newRow.insertAfter($row.next());
					
					$row.remove();
					
					iThemesAddClickEvents();
					iThemesDoReminder();
				}
			}
		);
	}
});