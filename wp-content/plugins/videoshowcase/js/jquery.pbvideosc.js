/* ------------------------------------------------------------------------
	Class: pbvideosc
	Use: Lightbox clone for jQuery
	Author: Stephane Caron (http://www.no-margin-for-errors.com)
	Version: 3.0
	Edited by: Skyler Moore, Dustin Bolton - Last updated Sept 29, 2011
------------------------------------------------------------------------- */

(function($) {
	$.pbvideosc = {version: '3.0'};
	
	$.fn.pbvideosc = function(pbv_settings) {
		pbv_settings = jQuery.extend({
			animation_speed: 'fast', /* fast/slow/normal */
			slideshow: false, /* false OR interval time in ms */
			autoplay_slideshow: false, /* true/false */
			opacity: 0.80, /* Value between 0 and 1 */
			show_title: true, /* true/false */
			allow_resize: true, /* Resize the photos bigger than viewport. true/false */
			default_width: 500,
			default_height: 344,
			counter_separator_label: '/', /* The separator for the gallery counter 1 "of" 2 */
			theme: 'default', /* light_rounded / dark_rounded / light_square / dark_square / default */
			hideflash: false, /* Hides all the flash object on a page, set to TRUE if flash appears over pbvideosc */
			wmode: 'opaque', /* Set the flash wmode attribute */
			autoplay: true, /* Automatically start videos: True/False */
			norelated: false, /* true = Don't show related videos */
			pluginpath: '', /* plugin path for calling js files */
			modal: false, /* If set to true, only the close button will close the window */
			overlay_gallery: true, /* If set to true, a gallery will overlay the fullscreen image on mouse over */
			keyboard_shortcuts: true, /* Set to false if you open forms inside pbvideosc */
			changepicturecallback: function(){}, /* Called everytime an item is shown/changed */
			callback: function(){}, /* Called when pbvideosc is closed */
			markup: '<div class="pbv_pic_holder"> \
						<div class="pbvt">&nbsp;</div> \
						<div class="pbv_top"> \
							<div class="pbv_left"></div> \
							<div class="pbv_middle"></div> \
							<div class="pbv_right"></div> \
						</div> \
						<div class="pbv_content_container"> \
							<div class="pbv_left"> \
							<div class="pbv_right"> \
								<div class="pbv_content"> \
									<div class="pbv_loaderIcon"></div> \
									<div class="pbv_fade"> \
										<a href="#" class="pbv_expand" title="Expand the image">Expand</a> \
										<div class="pbv_hoverContainer"> \
											<a class="pbv_next" href="#">next</a> \
											<a class="pbv_previous" href="#">previous</a> \
										</div> \
										<div id="pbv_full_res"></div> \
										<div class="pbv_details clearfix"> \
											<p class="pbv_description"></p> \
											<a class="pbv_close" href="#">Close</a> \
											<div class="pbv_nav"> \
												<a href="#" class="pbv_arrow_previous">Previous</a> \
												<p class="currentTextHolder">0/0</p> \
												<a href="#" class="pbv_arrow_next">Next</a> \
											</div> \
										</div> \
									</div> \
								</div> \
							</div> \
							</div> \
						</div> \
						<div class="pbv_bottom"> \
							<div class="pbv_left"></div> \
							<div class="pbv_middle"></div> \
							<div class="pbv_right"></div> \
						</div> \
					</div> \
					<div class="pbv_overlay"></div>',
			gallery_markup: '<div class="pbv_gallery"> \
								<a href="#" class="pbv_arrow_previous">Previous</a> \
								<ul> \
									{gallery} \
								</ul> \
								<a href="#" class="pbv_arrow_next">Next</a> \
							</div>',
			image_markup: '<img id="fullResImage" src="" />',
			flash_markup: '<object classid="clsid:D27CDB6E-AE6D-11cf-96B8-444553540000" width="{width}" height="{height}"><param name="wmode" value="{wmode}" /><param name="allowfullscreen" value="true" /><param name="allowscriptaccess" value="always" /><param name="movie" value="{path}" /><embed src="{path}" type="application/x-shockwave-flash" allowfullscreen="true" allowscriptaccess="always" width="{width}" height="{height}" wmode="{wmode}"></embed></object>',
			quicktime_markup: '<object classid="clsid:02BF25D5-8C17-4B23-BC80-D3488ABDDC6B" codebase="http://www.apple.com/qtactivex/qtplugin.cab" height="{height}" width="{width}"><param name="src" value="{path}"><param name="autoplay" value="{autoplay}"><param name="type" value="video/quicktime"><embed src="{path}" height="{height}" width="{width}" autoplay="{autoplay}" type="video/quicktime" pluginspage="http://www.apple.com/quicktime/download/"></embed></object>',
			iframe_markup: '<iframe src ="{path}" width="{width}" height="{height}" frameborder="no" webkitAllowFullScreen allowFullScreen></iframe>',
			vimeo_oldmarkup: '<iframe src ="{path}" width="{width}" height="{height}" frameborder="no" webkitAllowFullScreen allowFullScreen></iframe>',
			openplayer: '<iframe src ="{path}" width="{width}" height="{height}" marginheight="0" marginwidth="0" scrolling="no" frameborder="0">no iframe doom</iframe>'
		}, pbv_settings);
		
		// Global variables accessible only by pbvideosc
		var matchedObjects = this, percentBased = false, correctSizes, pbv_open,
		
		// pbvideosc container specific
		pbv_contentHeight, pbv_contentWidth, pbv_containerHeight, pbv_containerWidth,
		
		// Window size
		windowHeight = $(window).height(), windowWidth = $(window).width(),

		// Global elements
		pbv_slideshow;
		
		doresize = true, scroll_pos = _get_scroll();
	
		// Window/Keyboard events
		$(window).unbind('resize').resize(function(){ _center_overlay(); _resize_overlay(); });
		
		if(pbv_settings.keyboard_shortcuts) {
			$(document).unbind('keydown').keydown(function(e){
				if(typeof $pbv_pic_holder != 'undefined'){
					if($pbv_pic_holder.is(':visible')){
						switch(e.keyCode){
							case 37:
								$.pbvideosc.changePage('previous');
								break;
							case 39:
								$.pbvideosc.changePage('next');
								break;
							case 27:
								if(!pbv_settings.modal)
								$.pbvideosc.close();
								break;
						};
						return false;
					};
				};
			});
		}
		
		
		/**
		* Initialize pbvideosc.
		*/
		$.pbvideosc.initialize = function() {
			settings = pbv_settings;
			
			if($.browser.msie && parseInt($.browser.version) == 6) {
				// Fallback to a supported theme for IE6
				if ((settings.theme == "dark_rounded") || (settings.theme == "dark_square") || (settings.theme == "darker_square")) {
					settings.theme = "darker_square";
				}
				else {
					settings.theme = "light_square";
				}
			}
			
			_buildOverlay(this); // Build the overlay {this} being the caller
			
			if(settings.allow_resize)
				$(window).scroll(function(){ _center_overlay(); });
				
			_center_overlay();
			
			set_position = jQuery.inArray($(this).attr('href'), pbv_images); // Define where in the array the clicked item is positionned
			
			$.pbvideosc.open();
			
			return false;
		}


		/**
		* Opens the pbvideosc modal box.
		* @param image {String,Array} Full path to the image to be open, can also be an array containing full images paths.
		* @param title {String,Array} The title to be displayed with the picture, can also be an array containing all the titles.
		* @param description {String,Array} The description to be displayed with the picture, can also be an array containing all the descriptions.
		*/
		$.pbvideosc.open = function() {
			settings = pbv_settings;
			if(typeof settings == "undefined"){ // Means it's an API call, need to manually get the settings and set the variables
				// if($.browser.msie && $.browser.version == 6) settings.theme = "light_square"; // Fallback to a supported theme for IE6
				if($.browser.msie && parseInt($.browser.version) == 6) {
					// Fallback to a supported theme for IE6
					if ((settings.theme == "dark_rounded") || (settings.theme == "dark_square") || (settings.theme == "darker_square")) {
						settings.theme = "darker_square";
					}
					else {
						settings.theme = "light_square";
					}
				}

				_buildOverlay(this); // Build the overlay {this} being the caller
				pbv_images = $.makeArray(arguments[0]);
				pbv_titles = (arguments[1]) ? $.makeArray(arguments[1]) : $.makeArray("");
				pbv_descriptions = (arguments[2]) ? $.makeArray(arguments[2]) : $.makeArray("");
				isSet = (pbv_images.length > 1) ? true : false;
				set_position = 0;
			}
			if($.browser.msie && $.browser.version == 6) $('select').css('visibility','hidden'); // To fix the bug with IE select boxes
			
			if(settings.hideflash) $('object,embed').css('visibility','hidden'); // Hide the flash

			_checkPosition($(pbv_images).size()); // Hide the next/previous links if on first or last images.
		
			$('.pbv_loaderIcon').show();
		
			// Fade the content in
			if($pbvt.is(':hidden')) $pbvt.css('opacity',0).show();
			$pbv_overlay.show().fadeTo(settings.animation_speed,settings.opacity);

			// Display the current position
			$pbv_pic_holder.find('.currentTextHolder').text((set_position+1) + settings.counter_separator_label + $(pbv_images).size());

			// Set the description
			$pbv_pic_holder.find('.pbv_description').show().html(unescape(pbv_descriptions[set_position]));

			// Set the title
			(settings.show_title && pbv_titles[set_position] != "") ? $pbvt.html(unescape(pbv_titles[set_position])) : $pbvt.html('&nbsp;');
			
			// Get the dimensions
			movie_width = ( parseFloat(grab_param('width',pbv_images[set_position])) ) ? grab_param('width',pbv_images[set_position]) : settings.default_width.toString();
			movie_height = ( parseFloat(grab_param('height',pbv_images[set_position])) ) ? grab_param('height',pbv_images[set_position]) : settings.default_height.toString();
			
			// If the size is % based, calculate according to window dimensions
			if(movie_width.indexOf('%') != -1 || movie_height.indexOf('%') != -1){
				movie_height = parseFloat(($(window).height() * parseFloat(movie_height) / 100) - 150);
				movie_width = parseFloat(($(window).width() * parseFloat(movie_width) / 100) - 150);
				percentBased = true;
			}else{
				percentBased = false;
			}
			
			// Fade the holder
			$pbv_pic_holder.fadeIn(function(){
				settings = pbv_settings;
				imgPreloader = "";
				
				// Inject the proper content
				switch(_getFileType(pbv_images[set_position])){
					case 'youtube':
						correctSizes = _fitToViewport(movie_width,movie_height); // Fit item to viewport

						movie = 'http://www.youtube.com/v/'+grab_param('v',pbv_images[set_position]);
						if(settings.autoplay) movie += "&fs=1&autoplay=1";
						if(settings.norelated == 'true') movie += "&rel=0";
					
						toInject = settings.flash_markup.replace(/{width}/g,correctSizes['width']).replace(/{height}/g,correctSizes['height']).replace(/{wmode}/g,settings.wmode).replace(/{path}/g,movie);
					break;
				
					case 'vimeo':
						correctSizes = _fitToViewport(movie_width,movie_height); // Fit item to viewport
						
						//movie_id = pbv_images[set_position];
						//var regExp = /http:\/\/(www\.)?vimeo.com\/(\d+)/;
						//var match = movie_id.match(regExp);
						
						movie = pbv_images[set_position];
						movie = movie + '?autoplay=1&amp;title=0&amp;byline=0&amp;portrait=0&amp;color=00ADEF&amp;fullscreen=1&amp;loop=0';
						// movie = 'http://player.vimeo.com/video/'+ match[2] +'?title=0&amp;byline=0&amp;portrait=0';
						// if(settings.autoplay) movie += "&autoplay=1;";
				
						vimeo_width = correctSizes['width'] + '/embed/?moog_width='+ correctSizes['width'];
				
						toInject = settings.vimeo_oldmarkup.replace(/{width}/g,correctSizes['width']).replace(/{height}/g,correctSizes['height']).replace(/{path}/g,movie);
					break;
					
					case 'quicktime':
						correctSizes = _fitToViewport(movie_width,movie_height); // Fit item to viewport
						correctSizes['height']+=15; correctSizes['contentHeight']+=15; correctSizes['containerHeight']+=15; // Add space for the control bar
				
						toInject = settings.quicktime_markup.replace(/{width}/g,correctSizes['width']).replace(/{height}/g,correctSizes['height']).replace(/{wmode}/g,settings.wmode).replace(/{path}/g,pbv_images[set_position]).replace(/{autoplay}/g,settings.autoplay);
					break;
					
					case 'flash':
						correctSizes = _fitToViewport(movie_width,movie_height); // Fit item to viewport
					
						flash_vars = pbv_images[set_position];
						flash_vars = flash_vars.substring(pbv_images[set_position].indexOf('flashvars') + 10,pbv_images[set_position].length);

						filename = pbv_images[set_position];
						filename = filename.substring(0,filename.indexOf('?'));
					
						toInject =  settings.flash_markup.replace(/{width}/g,correctSizes['width']).replace(/{height}/g,correctSizes['height']).replace(/{wmode}/g,settings.wmode).replace(/{path}/g,filename+'?'+flash_vars);
					break;
					
					case 'iframe':
						correctSizes = _fitToViewport(movie_width,movie_height); // Fit item to viewport
				
						frame_url = pbv_images[set_position];
						frame_url = frame_url.substr(0,frame_url.indexOf('iframe')-1);
				
						toInject = settings.iframe_markup.replace(/{width}/g,correctSizes['width']).replace(/{height}/g,correctSizes['height']).replace(/{path}/g,frame_url);
					break;
					
					case 'openplayer':
						correctSizes = _fitToViewport(movie_width,movie_height); // Fit item to viewport
					
						flash_vars = pbv_images[set_position];
						flash_vars = flash_vars.substring(pbv_images[set_position].indexOf('flashvars') + 10,pbv_images[set_position].length);

						filename = pbv_images[set_position];
						//filename = filename.substring(0,filename.indexOf('?'));
						cwidth = (correctSizes['width']);
						cheight = (correctSizes['height']);
						
						toInject =  settings.openplayer.replace(/{width}/g,correctSizes['width']).replace(/{height}/g,correctSizes['height']).replace(/{wmode}/g,settings.wmode).replace(/{path}/g,filename+'&width='+cwidth+'&height='+cheight).replace(/{plugpth}/g,settings.pluginpath);
					break;
				};

				if(!imgPreloader){
					$pbv_pic_holder.find('#pbv_full_res')[0].innerHTML = toInject;
				
					// Show content
					_showContent();
				};
			});

			return false;
		};
		
		
		/**
		* Closes pbvideosc.
		*/
		$.pbvideosc.close = function(){
			settings = pbv_settings;
			clearInterval(pbv_slideshow);
			
			$pbv_pic_holder.stop().find('object,embed').css('visibility','hidden');
			
			$('div.pbv_pic_holder,div.pbvt,.pbv_fade').fadeOut(settings.animation_speed,function(){ $(this).remove(); });
			
			$pbv_overlay.fadeOut(settings.animation_speed, function(){
				settings = pbv_settings;
				if($.browser.msie && $.browser.version == 6) $('select').css('visibility','visible'); // To fix the bug with IE select boxes
				
				if(pbv_settings.hideflash) $('object,embed').css('visibility','visible'); // Show the flash
				
				$(this).remove(); // No more need for the pbvideosc markup
				
				$(window).unbind('scroll');
				
				pbv_settings.callback();
				
				doresize = true;
				
				pbv_open = false;
				
				delete pbv_settings;
			});
		};
	
		/**
		* Set the proper sizes on the containers and animate the content in.
		*/
		_showContent = function(){
			settings = pbv_settings;
			$('.pbv_loaderIcon').hide();
			
			$pbvt.fadeTo(settings.animation_speed,1);

			// Calculate the opened top position of the pic holder
			projectedTop = scroll_pos['scrollTop'] + ((windowHeight/2) - (correctSizes['containerHeight']/2));
			if(projectedTop < 0) projectedTop = 0;

			// Resize the content holder
			$pbv_pic_holder.find('.pbv_content').animate({'height':correctSizes['contentHeight']},settings.animation_speed);
			
			// Resize picture the holder
			$pbv_pic_holder.animate({
				'top': projectedTop,
				'left': (windowWidth/2) - (correctSizes['containerWidth']/2),
				'width': correctSizes['containerWidth']
			},settings.animation_speed,function(){
				settings = pbv_settings;
				$pbv_pic_holder.find('.pbv_hoverContainer,#fullResImage').height(correctSizes['height']).width(correctSizes['width']);

				$pbv_pic_holder.find('.pbv_fade').fadeIn(settings.animation_speed); // Fade the new content

				// Show the nav
				if(isSet && _getFileType(pbv_images[set_position])=="image") { $pbv_pic_holder.find('.pbv_hoverContainer').show(); }else{ $pbv_pic_holder.find('.pbv_hoverContainer').hide(); }
			
				if(correctSizes['resized']) $('a.pbv_expand,a.pbv_contract').fadeIn(settings.animation_speed); // Fade the resizing link if the image is resized
				
				if(settings.autoplay_slideshow && !pbv_slideshow && !pbv_open) $.pbvideosc.startSlideshow();
				
				settings.changepicturecallback(); // Callback!
				
				pbv_open = true;
			});
			
			_insert_gallery();
		};
		
		/**
		* Hide the content...DUH!
		*/
		function _hideContent(callback){
			settings = pbv_settings;
			// Fade out the current picture
			$pbv_pic_holder.find('#pbv_full_res object,#pbv_full_res embed').css('visibility','hidden');
			$pbv_pic_holder.find('.pbv_fade').fadeOut(settings.animation_speed,function(){
				$('.pbv_loaderIcon').show();
				
				callback();
			});
		};
	
		/**
		* Check the item position in the gallery array, hide or show the navigation links
		* @param setCount {integer} The total number of items in the set
		*/
		function _checkPosition(setCount){
			// If at the end, hide the next link
			if(set_position == setCount-1) {
				$pbv_pic_holder.find('a.pbv_next').css('visibility','hidden');
				$pbv_pic_holder.find('a.pbv_next').addClass('disabled').unbind('click');
			}else{ 
				$pbv_pic_holder.find('a.pbv_next').css('visibility','visible');
				$pbv_pic_holder.find('a.pbv_next.disabled').removeClass('disabled').bind('click',function(){
					$.pbvideosc.changePage('next');
					return false;
				});
			};
		
			// If at the beginning, hide the previous link
			if(set_position == 0) {
				$pbv_pic_holder
					.find('a.pbv_previous')
					.css('visibility','hidden')
					.addClass('disabled')
					.unbind('click');
			}else{
				$pbv_pic_holder.find('a.pbv_previous.disabled')
					.css('visibility','visible')
					.removeClass('disabled')
					.bind('click',function(){
						$.pbvideosc.changePage('previous');
						return false;
					});
			};
			
			(setCount > 1) ? $('.pbv_nav').show() : $('.pbv_nav').hide(); // Hide the bottom nav if it's not a set.
		};
	
		/**
		* Resize the item dimensions if it's bigger than the viewport
		* @param width {integer} Width of the item to be opened
		* @param height {integer} Height of the item to be opened
		* @return An array containin the "fitted" dimensions
		*/
		function _fitToViewport(width,height){
			settings = pbv_settings;
			resized = false;

			_getDimensions(width,height);
			
			// Define them in case there's no resize needed
			imageWidth = width, imageHeight = height;

			if( ((pbv_containerWidth > windowWidth) || (pbv_containerHeight > windowHeight)) && doresize && settings.allow_resize && !percentBased) {
				resized = true, fitting = false;
			
				while (!fitting){
					if((pbv_containerWidth > windowWidth)){
						imageWidth = (windowWidth - 200);
						imageHeight = (height/width) * imageWidth;
					}else if((pbv_containerHeight > windowHeight)){
						imageHeight = (windowHeight - 200);
						imageWidth = (width/height) * imageHeight;
					}else{
						fitting = true;
					};

					pbv_containerHeight = imageHeight, pbv_containerWidth = imageWidth;
				};
			
				_getDimensions(imageWidth,imageHeight);
			};

			return {
				width:Math.floor(imageWidth),
				height:Math.floor(imageHeight),
				containerHeight:Math.floor(pbv_containerHeight),
				containerWidth:Math.floor(pbv_containerWidth) + 40, // 40 behind the side padding
				contentHeight:Math.floor(pbv_contentHeight),
				contentWidth:Math.floor(pbv_contentWidth),
				resized:resized
			};
		};
		
		/**
		* Get the containers dimensions according to the item size
		* @param width {integer} Width of the item to be opened
		* @param height {integer} Height of the item to be opened
		*/
		function _getDimensions(width,height){
			width = parseFloat(width);
			height = parseFloat(height);
			
			// Get the details height, to do so, I need to clone it since it's invisible
			$pbv_details = $pbv_pic_holder.find('.pbv_details');
			$pbv_details.width(width);
			detailsHeight = parseFloat($pbv_details.css('marginTop')) + parseFloat($pbv_details.css('marginBottom'));
			$pbv_details = $pbv_details.clone().appendTo($('body')).css({
				'position':'absolute',
				'top':-10000
			});
			detailsHeight += $pbv_details.height();
			detailsHeight = (detailsHeight <= 34) ? 36 : detailsHeight; // Min-height for the details
			if($.browser.msie && $.browser.version==7) detailsHeight+=8;
			$pbv_details.remove();
			
			// Get the container size, to resize the holder to the right dimensions
			pbv_contentHeight = height + detailsHeight;
			pbv_contentWidth = width;
			pbv_containerHeight = pbv_contentHeight + $pbvt.height() + $pbv_pic_holder.find('.pbv_top').height() + $pbv_pic_holder.find('.pbv_bottom').height();
			pbv_containerWidth = width;
		}
	
		function _getFileType(itemSrc){
			if (itemSrc.match(/youtube\.com\/watch/i)) {
				return 'youtube';
			}else if (itemSrc.match(/vimeo\.com/i)) {
				return 'vimeo';
			}else if(itemSrc.indexOf('.mov') != -1){ 
				return 'quicktime';
			}else if(itemSrc.indexOf('.swf') != -1){
				return 'flash';
			}else if(itemSrc.indexOf('iframe') != -1){
				return 'iframe';
			}else if((itemSrc.indexOf('mp4') != -1) || (itemSrc.indexOf('flv') != -1) || (itemSrc.indexOf('3gp') != -1) || (itemSrc.indexOf('avi') != -1)){
				return 'openplayer';
			}else if(itemSrc.substr(0,1) == '#'){
				return 'inline';
			}else{
				return 'image';
			};
		};
	
		function _center_overlay(){
			if(doresize && typeof $pbv_pic_holder != 'undefined') {
				scroll_pos = _get_scroll();
				
				titleHeight = $pbvt.height(), contentHeight = $pbv_pic_holder.height(), contentwidth = $pbv_pic_holder.width();
				
				projectedTop = (windowHeight/2) + scroll_pos['scrollTop'] - (contentHeight/2);
				
				$pbv_pic_holder.css({
					'top': projectedTop,
					'left': (windowWidth/2) + scroll_pos['scrollLeft'] - (contentwidth/2)
				});
			};
		};
	
		function _get_scroll(){
			if (self.pageYOffset) {
				return {scrollTop:self.pageYOffset,scrollLeft:self.pageXOffset};
			} else if (document.documentElement && document.documentElement.scrollTop) { // Explorer 6 Strict
				return {scrollTop:document.documentElement.scrollTop,scrollLeft:document.documentElement.scrollLeft};
			} else if (document.body) {// all other Explorers
				return {scrollTop:document.body.scrollTop,scrollLeft:document.body.scrollLeft};
			};
		};
	
		function _resize_overlay() {
			windowHeight = $(window).height(), windowWidth = $(window).width();
			
			if(typeof $pbv_overlay != "undefined") $pbv_overlay.height($(document).height());
		};
	
		function _insert_gallery(){
			settings = pbv_settings;
			if(isSet && settings.overlay_gallery && _getFileType(pbv_images[set_position])=="image") {
				itemWidth = 52+5; // 52 beign the thumb width, 5 being the right margin.
				navWidth = (settings.theme == "default") ? 58 : 38; // Define the arrow width depending on the theme
				
				itemsPerPage = Math.floor((correctSizes['containerWidth'] - 100 - navWidth) / itemWidth);
				itemsPerPage = (itemsPerPage < pbv_images.length) ? itemsPerPage : pbv_images.length;
				totalPage = Math.ceil(pbv_images.length / itemsPerPage) - 1;

				// Hide the nav in the case there's no need for links
				if(totalPage == 0){
					navWidth = 0; // No nav means no width!
					$pbv_pic_holder.find('.pbv_gallery .pbv_arrow_next,.pbv_gallery .pbv_arrow_previous').hide();
				}else{
					$pbv_pic_holder.find('.pbv_gallery .pbv_arrow_next,.pbv_gallery .pbv_arrow_previous').show();
				};

				galleryWidth = itemsPerPage * itemWidth + navWidth;
				
				// Set the proper width to the gallery items
				$pbv_pic_holder.find('.pbv_gallery')
					.width(galleryWidth)
					.css('margin-left',-(galleryWidth/2));
					
				$pbv_pic_holder
					.find('.pbv_gallery ul')
					.width(itemsPerPage * itemWidth)
					.find('li.selected')
					.removeClass('selected');
				
				goToPage = (Math.floor(set_position/itemsPerPage) <= totalPage) ? Math.floor(set_position/itemsPerPage) : totalPage;
				
				
				if(itemsPerPage) {
					$pbv_pic_holder.find('.pbv_gallery').hide().show().removeClass('disabled');
				}else{
					$pbv_pic_holder.find('.pbv_gallery').hide().addClass('disabled');
				}
				
				$.pbvideosc.changeGalleryPage(goToPage);
				
				$pbv_pic_holder
					.find('.pbv_gallery ul li:eq('+set_position+')')
					.addClass('selected');
			}else{
				$pbv_pic_holder.find('.pbv_content').unbind('mouseenter mouseleave');
				$pbv_pic_holder.find('.pbv_gallery').hide();
			}
		}
	
		function _buildOverlay(caller){
			settings = pbv_settings;
			// Find out if the picture is part of a set
			theRel = $(caller).attr('rel');
			galleryRegExp = /\[(?:.*)\]/;
			isSet = (galleryRegExp.exec(theRel)) ? true : false;
			
			// Put the SRCs, TITLEs, ALTs into an array.
			pbv_images = (isSet) ? jQuery.map(matchedObjects, function(n, i){ if($(n).attr('rel').indexOf(theRel) != -1) return $(n).attr('href'); }) : $.makeArray($(caller).attr('href'));
			//pbv_titles = (isSet) ? jQuery.map(matchedObjects, function(n, i){ if($(n).attr('rel').indexOf(theRel) != -1) return ($(n).find('img').attr('alt')) ? $(n).find('img').attr('alt') : ""; }) : $.makeArray($(caller).find('img').attr('alt'));
			pbv_titles = (isSet) ? jQuery.map(matchedObjects, function(n, i){ if($(n).attr('rel').indexOf(theRel) != -1) return ($(n).attr('title')) ? $(n).attr('title') : ""; }) : $.makeArray($(caller).attr('title'));
			pbv_descriptions = (isSet) ? jQuery.map(matchedObjects, function(n, i){ if($(n).attr('rel').indexOf(theRel) != -1) return ($(n).attr('title')) ? $(n).attr('title') : ""; }) : $.makeArray($(caller).attr('title'));
			
			$('body').append(settings.markup); // Inject the markup
			
			$pbv_pic_holder = $('.pbv_pic_holder') , $pbvt = $('.pbvt'), $pbv_overlay = $('div.pbv_overlay'); // Set my global selectors
			
			// Inject the inline gallery!
			if(isSet && settings.overlay_gallery) {
				currentGalleryPage = 0;
				toInject = "";
				for (var i=0; i < pbv_images.length; i++) {
					var regex = new RegExp("(.*?)\.(jpg|jpeg|png|gif)$");
					var results = regex.exec( pbv_images[i] );
					if(!results){
						classname = 'default';
					}else{
						classname = '';
					}
					toInject += "<li class='"+classname+"'><a href='#'><img src='" + pbv_images[i] + "' width='50' alt='' /></a></li>";
				};
				
				toInject = settings.gallery_markup.replace(/{gallery}/g,toInject);
				
				$pbv_pic_holder.find('#pbv_full_res').after(toInject);
				
				$pbv_pic_holder.find('.pbv_gallery .pbv_arrow_next').click(function(){
					$.pbvideosc.changeGalleryPage('next');
					$.pbvideosc.stopSlideshow();
					return false;
				});
				
				$pbv_pic_holder.find('.pbv_gallery .pbv_arrow_previous').click(function(){
					$.pbvideosc.changeGalleryPage('previous');
					$.pbvideosc.stopSlideshow();
					return false;
				});
				
				$pbv_pic_holder.find('.pbv_content').hover(
					function(){
						$pbv_pic_holder.find('.pbv_gallery:not(.disabled)').fadeIn();
					},
					function(){
						$pbv_pic_holder.find('.pbv_gallery:not(.disabled)').fadeOut();
					});

				itemWidth = 52+5; // 52 beign the thumb width, 5 being the right margin.
				$pbv_pic_holder.find('.pbv_gallery ul li').each(function(i){
					$(this).css({
						'position':'absolute',
						'left': i * itemWidth
					});

					$(this).find('a').unbind('click').click(function(){
						$.pbvideosc.changePage(i);
						$.pbvideosc.stopSlideshow();
						return false;
					});
				});
			};
			
			
			// Inject the play/pause if it's a slideshow
			if(settings.slideshow){
				$pbv_pic_holder.find('.pbv_nav').prepend('<a href="#" class="pbv_play">Play</a>')
				$pbv_pic_holder.find('.pbv_nav .pbv_play').click(function(){
					$.pbvideosc.startSlideshow();
					return false;
				});
			}
			
			$pbv_pic_holder.attr('class','pbv_pic_holder ' + settings.theme); // Set the proper theme
			
			$pbv_overlay
				.css({
					'opacity':0,
					'height':$(document).height(),
					'width':$(document).width()
					})
				.bind('click',function(){
					settings = pbv_settings;
					if(!settings.modal) $.pbvideosc.close();
				});

			$('a.pbv_close').bind('click',function(){ $.pbvideosc.close(); return false; });

			$('a.pbv_expand').bind('click',function(e){
				// Expand the image
				if($(this).hasClass('pbv_expand')){
					$(this).removeClass('pbv_expand').addClass('pbv_contract');
					doresize = false;
				}else{
					$(this).removeClass('pbv_contract').addClass('pbv_expand');
					doresize = true;
				};
			
				_hideContent(function(){ $.pbvideosc.open(); });
		
				return false;
			});
		
			$pbv_pic_holder.find('.pbv_previous, .pbv_nav .pbv_arrow_previous').bind('click',function(){
				$.pbvideosc.changePage('previous');
				$.pbvideosc.stopSlideshow();
				return false;
			});
		
			$pbv_pic_holder.find('.pbv_next, .pbv_nav .pbv_arrow_next').bind('click',function(){
				$.pbvideosc.changePage('next');
				$.pbvideosc.stopSlideshow();
				return false;
			});
			
			_center_overlay(); // Center it
		};
		
		return this.unbind('click').click($.pbvideosc.initialize); // Return the jQuery object for chaining. The unbind method is used to avoid click conflict when the plugin is called more than once
	};
	
	function grab_param(name,url){
	  name = name.replace(/[\[]/,"\\\[").replace(/[\]]/,"\\\]");
	  var regexS = "[\\?&]"+name+"=([^&#]*)";
	  var regex = new RegExp( regexS );
	  var results = regex.exec( url );
	  return ( results == null ) ? "" : results[1];
	}
	
})(jQuery);
