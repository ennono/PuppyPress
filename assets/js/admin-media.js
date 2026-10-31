/**
 * PuppyPress media picker: stores a comma separated list of attachment IDs.
 */
( function ( $ ) {
	'use strict';

	function ids( $input ) {
		return $input.val().split( ',' ).filter( Boolean );
	}

	function addItem( $preview, attachment ) {
		var sizes = attachment.sizes || {};
		var image = sizes.thumbnail || sizes.medium || sizes.full || { url: attachment.icon };
		var $item = $( '<li />' ).attr( 'data-id', attachment.id );

		$( '<img />' ).attr( { src: image.url, alt: attachment.alt || '' } ).appendTo( $item );
		$( '<button type="button" class="puppypress-media__remove" />' )
			.attr( 'aria-label', puppyPressMedia.remove )
			.html( '&times;' )
			.appendTo( $item );

		$preview.append( $item );
	}

	$( document ).on( 'click', '[data-puppypress-media-add]', function ( event ) {
		var $wrap = $( this ).closest( '[data-puppypress-media]' );
		var $input = $wrap.find( '[data-puppypress-media-input]' );
		var $preview = $wrap.find( '[data-puppypress-media-preview]' );
		var frame = wp.media( {
			title: puppyPressMedia.title,
			button: { text: puppyPressMedia.button },
			library: { type: 'image' },
			multiple: true
		} );

		event.preventDefault();

		frame.on( 'select', function () {
			var current = ids( $input );

			frame.state().get( 'selection' ).each( function ( model ) {
				var attachment = model.toJSON();
				var id = String( attachment.id );

				if ( current.indexOf( id ) === -1 ) {
					current.push( id );
					addItem( $preview, attachment );
				}
			} );

			$input.val( current.join( ',' ) );
		} );

		frame.open();
	} );

	$( document ).on( 'click', '.puppypress-media__remove', function () {
		var $item = $( this ).closest( 'li' );
		var $input = $item.closest( '[data-puppypress-media]' ).find( '[data-puppypress-media-input]' );
		var id = String( $item.data( 'id' ) );

		$input.val( ids( $input ).filter( function ( value ) {
			return value !== id;
		} ).join( ',' ) );

		$item.remove();
	} );
}( jQuery ) );
