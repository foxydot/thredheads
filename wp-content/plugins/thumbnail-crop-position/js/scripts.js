jQuery(document).ready(function($){

	$('body').on('change', '.thumbnail-crop-position input', function(e) {

		var $this = $(this),
			data = {
				action: 'tcp',
				_wpnonce: tcpL10n._wpnonce,
				ajax_position_option: parseInt($this.val(), 10)
			};
		$this.attr('disabled', 'disabled').parent().addClass('loading');

		$.post(ajaxurl, data)
			.done( function(response) {
				response = parseInt(response, 10);
				console.log(' Server answer: ' + response);
				if ( response < 9 && response >= 0 ) {
					$this.parents('.thumbnail-crop-position').find('label').parent().removeClass('button-primary');
					$this.parent().addClass('button-primary');
				}
			})
			.always( function() {
				$this.removeAttr('disabled').parent().removeClass('loading');
			});

	}).removeAttr('disabled');

});