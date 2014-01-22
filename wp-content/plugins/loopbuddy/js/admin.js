jQuery(document).ready(function( $ ) {
	jQuery('.pluginbuddy_tip').tooltip({ 
		track: true, 
		delay: 0, 
		showURL: false, 
		showBody: " - ", 
		fade: 250 
	});
	jQuery( 'input#add_taxonomy' ).live( 'click', function() {
		var taxonomy_count = $( '#taxonomy_count' ).val();
		$.post( ajaxurl, { action: 'pb_loopbuddy_queryaddtaxonomy', taxonomy_count: taxonomy_count }, 
		function ( response ) {
			var $last_row = $( '#taxonomy-parameters #taxonomy_relation_container' );
			$( '#taxonomy_count' ).val( response.taxonomy_count );
			$last_row.before( response.html );
			if ( $( '#taxonomy-parameters tbody' ).length > 3 ) {
				$( "#taxonomy_relation_container" ).show();
			}
			console.log( response );
		}, 'json' );
	
	} );
	jQuery( 'input#add_meta' ).live( 'click', function() {
		var meta_count = $( '#meta_count' ).val();
		$.post( ajaxurl, { action: 'pb_loopbuddy_queryaddmeta', meta_count: meta_count }, 
		function ( response ) {
			var $last_row = $( '#post-meta-parameters #meta_relation_container' );
			$( '#meta_count' ).val( response.meta_count );
			$last_row.before( response.html );
			if ( $( '#post-meta-parameters tbody' ).length > 3 ) {
				$( "#meta_relation_container" ).show();
			}
			console.log( response );
		}, 'json' );
	
	} );
	$.lb_taxonomies = { 
		clear: function( id ) {
			$( "#taxonomy_name" + id ).val( '' );
			$( "#taxonomy_terms" + id ).val( '' );
		},
		remove_tax: function( element ) {
			if ( $( '#taxonomy-parameters tbody' ).length <= 4 ) {
				$( "#taxonomy_relation_container" ).hide();
			}
			$( element ).remove();
		},
		remove_meta: function( element ) {
			if ( $( '#post-meta-parameters tbody' ).length <= 4 ) {
				$( "#meta_relation_container" ).hide();
			}
			$( element ).remove();
		}
	};
});
