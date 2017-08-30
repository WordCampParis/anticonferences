( function( $ ) {
	$( '#ac-new-topic' ).on( 'click', function( event ) {
		event.preventDefault();

		if ( ! $( '#camp-modal-holder' ).length ) {
			$( 'body' ).append( $( '<div></div>' ).prop( 'id', 'camp-modal-holder' ) );

			$( '#camp-modal-holder' ).append( $( '<div></div>' ).addClass( 'camp-backdrop' ) );

			$( '#respond-container' ).addClass( 'camp-modal' );
		}
	} );

	$( '#commentform' ).on( 'reset', function( event ) {
		event.preventDefault();

		if ( $( '#camp-modal-holder' ).length ) {
			$( '#camp-modal-holder' ).remove();
		}

		$( '#respond-container' ).removeClass( 'camp-modal' );
	} );

} )( jQuery );
