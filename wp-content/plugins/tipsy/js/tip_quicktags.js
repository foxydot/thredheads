jQuery( document ).ready( function( $ ) {
	var html = "<input type='button' id='pb_tipsy_htmlbutton' class='thickbox' value='Tipsy' />";
	$( "#ed_toolbar" ).append( html );
	//post.php
	jQuery( '#pb_tipsy_htmlbutton' ).click( function() { tb_show( 'Tipsy', 'post.php#TB_inline?inlineId=pb_tiptip' ); return false; } );	
} );

