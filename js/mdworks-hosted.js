// javascript

( function ( $ ) {

	$( document ).ready( function () {

		$( document ).on( 'change', '#slt_db', function ( e ) {
			e.preventDefault();

			if( $(this).val() != "" ) $( '#frm-db' ).submit();

			return false;
		});
        
		
	
	});
	
	
} )( jQuery );