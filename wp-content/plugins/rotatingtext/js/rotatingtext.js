(function($) {
	$.fn.rotatingtext = function(o) {
			// Default variable values if they arent set:
			if( o.fadeinspeed == undefined ) o.fadeinspeed = 1800;
			if( o.fadedisplay == undefined ) o.fadedisplay = 1080;
			if( o.fadeoutspeed == undefined ) o.fadeoutspeed = 1800;
			if( o.fadetimeout == undefined ) o.fadetimeout = 500;
			if( o.current == undefined ) o.current = 'current';
		
		
			var count;
			count = 0;
			var element;


			
			// For each element in this rotating text class instance...
			$(this).each( function() {
				element = $(this);
				rotate();
			
				function checkit() {
					if(count >= ($(element).children().size()-1) ) {
						count = 0;
					}
					else {
						count++;
					}
					rotate();
				}				
				function rotate() {
					o.current = $(element).children().eq(count);
					//o.current.html(o.current.html() + count);

					o.current.fadeIn(o.fadeinspeed, function() {
						setTimeout(function() {
							o.current.fadeOut(o.fadeoutspeed, function() {
								setTimeout(function(){
									checkit();
								},o.fadetimeout)
							});
						}, o.fadedisplay);
					});
				}

			
			});
		}
})(jQuery);
