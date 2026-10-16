/**
 * South Listings: the search form, the results and the mortgage estimate.
 *
 * The server already renders the correct result for whatever is in the
 * address, so everything here is an enhancement. With JavaScript off the
 * search form is a plain GET form, every filter is visible, the order
 * select has its own submit button and the page numbers are links.
 *
 * With it: the extra filters fold behind "+ More filters", the sliders show
 * their values, untouched filters are left out of the address, and on the
 * listings page a search, a sort or a page number fetches the same address
 * and swaps the results region in place (history kept, Back works). Any
 * failure falls back to an ordinary navigation, never a half-updated page.
 */
( function () {
	'use strict';

	var strings = window.southListings || {};
	var VIEW_KEY = 'south-view';

	function text( key, fallback ) {
		return strings[ key ] || fallback;
	}

	function groupNumber( n ) {
		return Number( n ).toLocaleString( 'en-US' );
	}

	/* The two-thumb sliders: keep min <= max, paint the track, show values. */
	function initRanges( root ) {
		root.querySelectorAll( '[data-south-range]' ).forEach( function ( range ) {
			var inputs = range.querySelectorAll( 'input[type="range"]' );
			var track = range.querySelector( '.south-range__track' );
			var output = range.querySelector( 'output' );
			if ( 2 !== inputs.length ) {
				return;
			}
			var isArea = 'area' === range.getAttribute( 'data-south-range' );
			function paint( moved ) {
				var lo = Number( inputs[ 0 ].value );
				var hi = Number( inputs[ 1 ].value );
				if ( lo > hi ) {
					if ( moved === inputs[ 0 ] ) {
						inputs[ 0 ].value = hi;
						lo = hi;
					} else {
						inputs[ 1 ].value = lo;
						hi = lo;
					}
				}
				var min = Number( inputs[ 0 ].min );
				var span = Number( inputs[ 0 ].max ) - min || 1;
				track.style.setProperty( '--low', ( ( lo - min ) / span * 100 ) + '%' );
				track.style.setProperty( '--high', ( ( hi - min ) / span * 100 ) + '%' );
				if ( output ) {
					var unit = text( 'sqft', '%s sq ft' );
					output.textContent = isArea
						? unit.replace( '%s', groupNumber( lo ) ) + ' - ' + unit.replace( '%s', groupNumber( hi ) )
						: lo + ' - ' + hi;
				}
			}
			inputs.forEach( function ( input ) {
				input.addEventListener( 'input', function () {
					paint( input );
				} );
			} );
			paint();
		} );
	}

	/* "+ More filters": folded unless one of its filters is in use. */
	function initMore( form ) {
		var panel = form.querySelector( '[data-south-more]' );
		var toggle = form.querySelector( '.south-search__toggle' );
		if ( ! panel || ! toggle ) {
			return;
		}
		function set( open ) {
			panel.hidden = ! open;
			toggle.setAttribute( 'aria-expanded', open ? 'true' : 'false' );
			toggle.textContent = open ? text( 'fewer', '- Fewer filters' ) : text( 'more', '+ More filters' );
		}
		toggle.hidden = false;
		set( '1' === panel.getAttribute( 'data-open' ) );
		toggle.addEventListener( 'click', function () {
			set( panel.hidden );
		} );
	}

	/* The address a form would produce, without empty or untouched fields. */
	function urlFor( form ) {
		var params = new URLSearchParams();
		new FormData( form ).forEach( function ( value, key ) {
			var field = form.elements[ key ];
			value = String( value ).trim();
			if ( '' === value ) {
				return;
			}
			if ( field && 'range' === field.type ) {
				var edge = /_min$/.test( key ) ? field.min : field.max;
				if ( value === String( edge ) ) {
					return;
				}
			}
			params.append( key, value );
		} );
		var query = params.toString();
		return form.action.split( '?' )[ 0 ] + ( query ? '?' + query : '' );
	}

	var results = null;
	var pending = null;

	function samePage( url ) {
		var target = new URL( url, window.location.href );
		return target.origin === window.location.origin && target.pathname.replace( /\/page\/\d+\/?$/, '/' ) === window.location.pathname.replace( /\/page\/\d+\/?$/, '/' );
	}

	function applyView( region ) {
		var view = 'grid';
		try {
			view = window.localStorage.getItem( VIEW_KEY ) || 'grid';
		} catch ( e ) {}
		region.classList.toggle( 'is-list', 'list' === view );
		region.querySelectorAll( '.south-view' ).forEach( function ( button ) {
			var on = button.getAttribute( 'data-view' ) === view;
			button.classList.toggle( 'is-active', on );
			button.setAttribute( 'aria-pressed', on ? 'true' : 'false' );
		} );
	}

	function enhanceResults( region ) {
		var toolbar = region.querySelector( '[data-south-toolbar]' );
		if ( toolbar ) {
			toolbar.classList.add( 'is-enhanced' );
			var view = toolbar.querySelector( '[data-south-view]' );
			if ( view ) {
				view.hidden = false;
			}
		}
		applyView( region );
	}

	function load( url, push ) {
		if ( ! results ) {
			window.location.assign( url );
			return;
		}
		if ( pending ) {
			pending.abort();
		}
		pending = new AbortController();
		results.classList.add( 'is-loading' );
		results.setAttribute( 'aria-busy', 'true' );
		window.fetch( url, { signal: pending.signal, credentials: 'same-origin', headers: { 'X-Requested-With': 'south-listings' } } )
			.then( function ( response ) {
				if ( ! response.ok ) {
					throw new Error( 'HTTP ' + response.status );
				}
				return response.text();
			} )
			.then( function ( html ) {
				var doc = new DOMParser().parseFromString( html, 'text/html' );
				var fresh = doc.querySelector( '.south-results' );
				if ( ! fresh ) {
					throw new Error( 'no results region' );
				}
				results.innerHTML = fresh.innerHTML;
				enhanceResults( results );
				if ( push ) {
					window.history.pushState( { south: true }, '', url );
				}
				var summary = results.querySelector( '.south-toolbar__summary' );
				if ( summary && results.getBoundingClientRect().top < 0 ) {
					results.scrollIntoView( { block: 'start' } );
				}
			} )
			.catch( function ( error ) {
				if ( 'AbortError' !== error.name ) {
					window.location.assign( url );
				}
			} )
			.finally( function () {
				results.classList.remove( 'is-loading' );
				results.setAttribute( 'aria-busy', 'false' );
				pending = null;
			} );
	}

	function initSearch() {
		document.querySelectorAll( '[data-south-search]' ).forEach( function ( form ) {
			initRanges( form );
			initMore( form );
			form.addEventListener( 'submit', function ( event ) {
				event.preventDefault();
				var url = urlFor( form );
				if ( results && samePage( url ) ) {
					load( url, true );
				} else {
					window.location.assign( url );
				}
			} );
		} );
	}

	function initResults() {
		results = document.querySelector( '.south-results' );
		if ( ! results ) {
			return;
		}
		enhanceResults( results );

		results.addEventListener( 'change', function ( event ) {
			var form = event.target.closest( '[data-south-order]' );
			if ( form ) {
				load( urlFor( form ), true );
				var search = document.querySelector( '[data-south-search] select[name="sort"]' );
				if ( search ) {
					search.value = event.target.value;
				}
			}
		} );
		results.addEventListener( 'submit', function ( event ) {
			var form = event.target.closest( '[data-south-order]' );
			if ( form ) {
				event.preventDefault();
				load( urlFor( form ), true );
			}
		} );
		results.addEventListener( 'click', function ( event ) {
			var view = event.target.closest( '.south-view' );
			if ( view ) {
				try {
					window.localStorage.setItem( VIEW_KEY, view.getAttribute( 'data-view' ) );
				} catch ( e ) {}
				applyView( results );
				return;
			}
			var link = event.target.closest( '.page-numbers, .south-toolbar__summary a' );
			if ( link && link.href && samePage( link.href ) && ! event.metaKey && ! event.ctrlKey ) {
				event.preventDefault();
				load( link.href, true );
			}
		} );
		window.addEventListener( 'popstate', function () {
			load( window.location.href, false );
		} );
	}

	/* The mortgage estimate: principal and interest, monthly. */
	function initMortgage() {
		document.querySelectorAll( '[data-south-mortgage]' ).forEach( function ( form ) {
			function update() {
				var price = Number( form.elements.price.value ) || 0;
				var down = Math.min( 100, Math.max( 0, Number( form.elements.down.value ) || 0 ) );
				var rate = ( Number( form.elements.rate.value ) || 0 ) / 100 / 12;
				var months = ( Number( form.elements.term.value ) || 30 ) * 12;
				var loan = price * ( 1 - down / 100 );
				var payment = rate ? loan * rate / ( 1 - Math.pow( 1 + rate, -months ) ) : loan / months;
				form.elements.result.value = isFinite( payment ) && payment > 0 ? '$' + Math.round( payment ).toLocaleString( 'en-US' ) : '—';
			}
			form.addEventListener( 'input', update );
			form.addEventListener( 'change', update );
			update();
		} );
	}

	function init() {
		initResults();
		initSearch();
		initMortgage();
	}

	if ( 'loading' === document.readyState ) {
		document.addEventListener( 'DOMContentLoaded', init );
	} else {
		init();
	}
}() );
