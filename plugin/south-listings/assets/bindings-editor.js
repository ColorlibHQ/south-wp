/**
 * The `south/property` block bindings source in the editor.
 *
 * The server formats the values on the site (includes/bindings.php). Here the
 * same values are read from the home's fields in the editor's data store, so
 * a card in a query loop shows its own price and facts while it is being
 * edited instead of the source's name. Read-only: the fields are edited in
 * the property's own screen.
 */
( function ( wp ) {
	'use strict';

	if ( ! wp || ! wp.blocks || ! wp.blocks.registerBlockBindingsSource ) {
		return;
	}

	var config = window.southListingsBindings || {};

	function group( number ) {
		return String( Math.round( Number( number ) || 0 ) ).replace( /\B(?=(\d{3})+(?!\d))/g, ' ' );
	}

	function termName( select, ids, taxonomy ) {
		if ( ! ids || ! ids.length ) {
			return '';
		}
		var term = select( 'core' ).getEntityRecord( 'taxonomy', taxonomy, ids[ 0 ] );
		return term ? term.name : '';
	}

	wp.blocks.registerBlockBindingsSource( {
		name: 'south/property',
		label: config.label || 'South property',
		usesContext: [ 'postId', 'postType' ],
		getValues: function ( args ) {
			var select = args.select;
			var context = args.context || {};
			var out = {};
			var record = context.postId && 'property' === context.postType
				? select( 'core' ).getEditedEntityRecord( 'postType', 'property', context.postId )
				: null;

			Object.keys( args.bindings ).forEach( function ( attribute ) {
				var key = ( args.bindings[ attribute ].args || {} ).key;
				var label = ( config.keys || {} )[ key ] || key;
				if ( ! record || ! record.meta ) {
					out[ attribute ] = label;
					return;
				}
				var meta = record.meta;
				var status = termName( select, record.property_status, 'property_status' );
				var value = '';
				switch ( key ) {
					case 'status':
						value = status;
						break;
					case 'price':
						value = '' === meta.south_price || null === meta.south_price ? '' : '$' + group( meta.south_price );
						if ( value && /rent/i.test( status ) ) {
							value += ' ' + ( config.month || '/mo' );
						}
						break;
					case 'area':
						value = meta.south_area ? ( config.sqft || '%s sq ft' ).replace( '%s', Number( meta.south_area ).toLocaleString( 'en-US' ) ) : '';
						break;
					case 'new':
						value = '';
						break;
					case 'type':
						value = termName( select, record.property_type, 'property_type' );
						break;
					case 'city':
						value = termName( select, record.property_city, 'property_city' );
						break;
					default:
						value = undefined !== meta[ 'south_' + key ] ? String( meta[ 'south_' + key ] ) : '';
				}
				out[ attribute ] = value;
			} );
			return out;
		},
		canUserEditValue: function () {
			return false;
		},
	} );
}( window.wp ) );
