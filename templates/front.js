( function( $ ) {
	var supportForm = $( '#support-container' );

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

	$( '.comment-list' ).on( 'click', '.ac-support-button', function( event ) {
		event.preventDefault();

		var parentID = $( event.currentTarget ).data( 'topic-id' ),
		    heart    = $( event.currentTarget ).clone().removeClass( 'ac-support-button' )
		                                               .addClass( 'ac-heart' )
		                                               .removeAttr( 'data-topic-id' );

		if ( ! parentID ) {
			return;
		}

		$( event.currentTarget ).parent().append( supportForm.addClass( 'active' ) );
		$( supportForm ).find( '#ac-topic-id' ).val( parentID );

		var hearts = ''
		for ( var i=0; i < 10 ; i++ ) {
			heart.attr( 'data-amount', i + 1 );
			heart.find( '.ac-loved' ).removeClass( 'ac-loved' ).addClass( 'ac-love' );
            hearts += heart.get( 0 ).outerHTML;
        }

        if ( $( supportForm ).find( '.ac-hearts' ).length ) {
        	return;
        }

		$( supportForm ).find( 'div.submit' )
		                .before(
		                	$( '<div></div>' ).addClass( 'ac-hearts' )
			                                  .html( hearts )
			            );
	} );

	window.selectHearts = function( event ) {
		event.preventDefault();

		var heart = $( event.currentTarget );

		heart.addClass( 'selected' );

		$.each( heart.siblings(), function( s, element ) {
			if ( $( element ).data( 'amount' ) <= heart.data( 'amount' ) ) {
				$( element ).addClass( 'selected' );
			} else {
				$( element ).removeClass( 'selected' );
			}
		} );
	}
	$( '.comment-list' ).on( 'mouseenter', '.ac-heart', function( event ) {
		return window.selectHearts( event );
	} );
	$( '.comment-list' ).on( 'click', '.ac-heart', function( event ) {
		return window.selectHearts( event );
	} );

	$( '.comment-list' ).on( 'submit', '.support-form', function( event ) {
		var comment = $( event.currentTarget ).find( '.ac-heart.selected' )
		                                      .last()
		                                      .data( 'amount' );

		if ( ! comment ) {
			event.preventDefault();
			return;
		}

		$( '#ac-support-amount' ).val( comment );

		if ( ! $( '#ac-support-author' ).val() ) {
			$( '#ac-support-author' ).val( $( '#support-email' ).val().split( '@' )[0] );
		}

		return event;
	} );

	$( '.comment-list' ).on( 'reset', '.support-form', function( event ) {
		$( '#ac-support-amount' ).val( '' );
		$( event.delegateTarget ).append( supportForm.removeClass( 'active' ) );

		return event;
	} );

} )( jQuery );
