var $j = jQuery.noConflict();

$j(function(){

	$j(".chzn-select").chosen({
		search_contains: true
	});

});

$j(function(){

	$j('.wpsc-product_page_wpsc_mo').delegate('#copybilling', 'click', function() {
		if ( $j(this).is(':checked') ) { 
			$j('.shippingfirstname input').val( $j('.billingfirstname input').val() );
			$j('.shippinglastname input').val( $j('.billinglastname input').val() );
			$j('.shippingaddress textarea').val( $j('.billingaddress textarea').val() );
			$j('.shippingcity input').val( $j('.billingcity input').val() );
			$j('.shippingstate input').val( $j('.billingstate input').val() );
			$j('.shippingcountry select').val( $j('.billingcountry select').val() );
			$j('.shippingpostcode input').val( $j('.billingpostcode input').val() );
		} else {
			$j('.shippingfirstname input').val( '' );
			$j('.shippinglastname input').val( '' );
			$j('.shippingaddress textarea').val( '' );
			$j('.shippingcity input').val( '' );
			$j('.shippingstate input').val( '' );
			$j('.shippingcountry select').val( '' );
			$j('.shippingpostcode input').val( '' );
		}
	});
	
	$j('.wpsc-product_page_wpsc_mo').delegate('#addproduct', 'click', function(e) {
		e.preventDefault();
		
		if ( $j('#product_select').val() != '' && $j('#product_select option').length > 0 ) {

			$j('#addproduct').hide();
			$j('#addproduct').next('.mo-loading').show();
			$j('#product_select').removeClass('error')

			var data = {
				'action': 'get_product_row',
				'product_id': $j('#product_select').val(),
				'user_id': $j('#user_select').val()
			}
	
			$j.post(Global.ajaxurl, data, function(data){
	
				$j('#addproduct').show();
				$j('#addproduct').next('.mo-loading').hide();

				$j('#product_select option:selected').attr('disabled', 'disabled')
				$j('#product_select option:not(:disabled)').eq(0).attr('selected', 'selected');
				if ( $j('#product_select option:not(:disabled)').length == 0 ) $j('#addproduct,#product_select').hide();
				$j('.placeholder_row').hide();
				$j('#products-full-width table tbody').append(data);
				$j('#product_select').trigger('liszt:updated');
			});
			
		} else {
			$j('#product_select').focus();
		}

	});
	
	$j('.wpsc-product_page_wpsc_mo').delegate('.addvariation', 'click', function(e) {
		e.preventDefault();
		
		var addVariation = $j(this);
		var variationRow = $j(this).parent().parent().parent();
		var emptyVariation = 0;

		addVariation.parent().children('.wpsc_select_variation').each(function(){
			if ( $j(this).val() == '0' ) emptyVariation++;
		});
		
		if ( emptyVariation == 0 ) {
			addVariation.hide();
			addVariation.next('.mo-loading').show();
			
		    var selectedVariations = addVariation.parent().children('.wpsc_select_variation').map(function(i,n) {
		        return $j(n).val();
		    }).get(); //get converts it to an array

			var data = {
				'action': 'get_product_row',
				'product_id': variationRow.find('input.product_id').val(),
				'user_id': $j('#user_select').val(),
				'variation[]': selectedVariations
			}
	
			$j.post(Global.ajaxurl, data, function(data){

				$j('#product_select option[value="'+variationRow.find('input.product_id').val()+'"]').removeAttr('disabled');
				$j('#product_select option:not(:disabled)').eq(0).attr('selected', 'selected');
				variationRow.after(data).remove();
	
			});
		}
		
	});

	$j('.wpsc-product_page_wpsc_mo').delegate('#products-full-width .remove', 'click', function() {
		rowID = $j(this).parent().parent().find('input.product_id').val();
		$j('#product_select option[value="'+rowID+'"]').removeAttr('disabled');
		$j('#product_select option:not(:disabled)').eq(0).attr('selected', 'selected');
		$j('#product_select').trigger('liszt:updated');
		$j(this).parent().parent().remove();
		if ( $j('#products-full-width table tbody tr').length == 1 ) $j('.placeholder_row').show();
	});

	$j('.wpsc-product_page_wpsc_mo form').submit(function(){
		if ( $j('input.required,textarea.required').val() == '' ) {
			$j('input.required,textarea.required').addClass('error').focus();
			return false;
		}
		if ( $j('#products-full-width table tbody tr').length == 1 && $j('#product_select').length > 0 ) {
			$j('#product_select').addClass('error').focus();
			return false;
		}
	});

	$j('.wpsc-product_page_wpsc_mo').delegate('input.required,textarea.required', 'keydown', function() {
		$j(this).removeClass('error');
	});
	
	$j('.wpsc-product_page_wpsc_mo').delegate('select#user_select', 'change', function() {

		var billing_firstName	= $j('.billingfirstname input');
		var billing_lastName	= $j('.billinglastname input');
		var billing_address		= $j('.billingaddress textarea');
		var billing_city		= $j('.billingcity input');
		var billing_state		= $j('.billingstate input');
		var billing_country		= $j('.billingcountry select');
		var billing_postcode	= $j('.billingpostcode input');
		var billing_phone		= $j('.billingphone input');
		var billing_email		= $j('.billingemail input');
		var shipping_firstName	= $j('.shippingfirstname input');
		var shipping_lastName	= $j('.shippinglastname input');
		var shipping_address	= $j('.shippingaddress textarea');
		var shipping_city		= $j('.shippingcity input');
		var shipping_state		= $j('.shippingstate input');
		var shipping_country	= $j('.shippingcountry select');
		var shipping_postcode	= $j('.shippingpostcode input');

		if ( $j(this).val() != '' ) {

			$j('.fields input, .fields select, .fields textarea').attr( 'disabled', 'disabled' );	
			$j('.customer.mo-loading').show();
		
			var data = {
				'action': 'get_user_data',
				'user_id': $j('select#user_select').val()
			}
	
			$j.post(Global.ajaxurl, data, function(r){

				if ( r.status == 'success' ) {
					billing_firstName.val(r.billing.first_name);
					billing_lastName.val(r.billing.last_name);
					billing_address.val(r.billing.address);
					billing_city.val(r.billing.city);
					billing_state.val(r.billing.state);
					billing_country.val(r.billing.country);
					billing_postcode.val(r.billing.postcode);
					billing_phone.val(r.billing.phone);
					billing_email.val(r.billing.email);
					shipping_firstName.val(r.shipping.first_name);
					shipping_lastName.val(r.shipping.last_name);
					shipping_address.val(r.shipping.address);
					shipping_city.val(r.shipping.city);
					shipping_state.val(r.shipping.state);
					shipping_country.val(r.shipping.country);
					shipping_postcode.val(r.shipping.postcode);
				}
	
				$j('.customer.mo-loading').hide();
				$j('.fields input, .fields select, .fields textarea').removeAttr( 'disabled' );
	
			});
		} else {
			$j('.fields input, .fields select, .fields textarea').val( '' );
		}

	});

	if ( $j('select#user_select').val() != '' && $j('.billingemail input').val() == '' )
		$j('select#user_select').trigger('change');

});