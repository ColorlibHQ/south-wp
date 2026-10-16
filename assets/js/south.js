/**
 * South: the sticky header, the three sliders, the map that loads on request
 * and the enquiry form's sending state.
 *
 * Everything is an enhancement over a page that already works: with
 * JavaScript off the first hero slide shows, every testimonial and card is in
 * the page, "Open in maps" is an ordinary link and forms post normally. One
 * function per feature; each returns early when its element is absent.
 *
 * @package South
 */

( function () {
	'use strict';

	var settings = window.southSettings || {};
	var reduceMotion = window.matchMedia && window.matchMedia( '(prefers-reduced-motion: reduce)' ).matches;

	function label( key, fallback ) {
		return settings[ key ] || fallback;
	}

	function arrow( direction ) {
		var button = document.createElement( 'button' );
		button.type = 'button';
		button.className = 'south-arrow south-arrow--' + direction;
		button.setAttribute( 'aria-label', 'prev' === direction ? label( 'previous', 'Previous' ) : label( 'next', 'Next' ) );
		return button;
	}

	/**
	 * The navigation bar turns solid black and stays at the top once the page
	 * scrolls past the top bar, as the template's does.
	 */
	function initStickyHeader() {
		var bar = document.querySelector( '.south-navbar' );
		var header = document.querySelector( '.south-site-header' );
		if ( ! bar || ! header ) {
			return;
		}
		var ticking = false;
		function update() {
			ticking = false;
			var start = header.getBoundingClientRect().top + window.scrollY + bar.offsetTop;
			var stuck = window.scrollY > start + bar.offsetHeight;
			bar.classList.toggle( 'is-sticky', stuck );
			document.body.classList.toggle( 'south-header-stuck', stuck );
		}
		window.addEventListener( 'scroll', function () {
			if ( ! ticking ) {
				ticking = true;
				window.requestAnimationFrame( update );
			}
		}, { passive: true } );
		update();
	}

	/**
	 * The hero: photographs that fade into each other, with numbered squares
	 * (01, 02, 03) at the lower left and arrows that appear on hover.
	 */
	function initHeroSlider() {
		document.querySelectorAll( '.south-slider--fade' ).forEach( function ( slider ) {
			var slides = Array.prototype.filter.call( slider.children, function ( el ) {
				return el.classList.contains( 'wp-block-cover' );
			} );
			if ( slides.length < 2 ) {
				return;
			}
			var current = 0;
			var timer = null;
			var dots = document.createElement( 'div' );
			dots.className = 'south-dots';

			slider.setAttribute( 'role', 'region' );
			slider.setAttribute( 'aria-roledescription', 'carousel' );

			slides.forEach( function ( slide, i ) {
				slide.setAttribute( 'role', 'group' );
				slide.setAttribute( 'aria-roledescription', 'slide' );
				var dot = document.createElement( 'button' );
				dot.type = 'button';
				dot.textContent = ( i < 9 ? '0' : '' ) + ( i + 1 );
				dot.setAttribute( 'aria-label', label( 'slide', 'Show slide %d' ).replace( '%d', i + 1 ) );
				dot.addEventListener( 'click', function () {
					show( i );
					restart();
				} );
				dots.appendChild( dot );
			} );

			var prev = arrow( 'prev' );
			var next = arrow( 'next' );
			prev.addEventListener( 'click', function () {
				show( current - 1 );
				restart();
			} );
			next.addEventListener( 'click', function () {
				show( current + 1 );
				restart();
			} );
			slider.appendChild( prev );
			slider.appendChild( next );
			slider.appendChild( dots );

			function show( index ) {
				current = ( index + slides.length ) % slides.length;
				slides.forEach( function ( slide, i ) {
					var active = i === current;
					slide.classList.toggle( 'is-active', active );
					slide.setAttribute( 'aria-hidden', active ? 'false' : 'true' );
					if ( 'inert' in slide ) {
						slide.inert = ! active;
					}
					dots.children[ i ].classList.toggle( 'is-active', active );
					dots.children[ i ].setAttribute( 'aria-current', active ? 'true' : 'false' );
				} );
			}

			function restart() {
				window.clearInterval( timer );
				if ( settings.autoplay !== false && ! reduceMotion ) {
					timer = window.setInterval( function () {
						show( current + 1 );
					}, 6000 );
				}
			}

			slider.addEventListener( 'mouseenter', function () {
				window.clearInterval( timer );
			} );
			slider.addEventListener( 'mouseleave', restart );
			slider.addEventListener( 'focusin', function () {
				window.clearInterval( timer );
			} );

			slider.classList.add( 'is-ready' );
			show( 0 );
			restart();
		} );
	}

	/**
	 * Testimonials: three quotes with the middle one in focus. The arrows move
	 * the next quote to the centre by rotating the order.
	 */
	function initCenterSlider() {
		document.querySelectorAll( '.south-slider--center' ).forEach( function ( slider ) {
			var track = slider.querySelector( '.wp-block-columns' );
			if ( ! track || track.children.length < 2 ) {
				return;
			}
			function mark() {
				var items = track.children;
				var middle = Math.floor( items.length / 2 );
				Array.prototype.forEach.call( items, function ( item, i ) {
					item.classList.toggle( 'is-center', i === middle );
				} );
			}
			var prev = arrow( 'prev' );
			var next = arrow( 'next' );
			prev.addEventListener( 'click', function () {
				track.insertBefore( track.lastElementChild, track.firstElementChild );
				mark();
			} );
			next.addEventListener( 'click', function () {
				track.appendChild( track.firstElementChild );
				mark();
			} );
			slider.appendChild( prev );
			slider.appendChild( next );
			slider.classList.add( 'is-ready' );
			mark();
		} );
	}

	/**
	 * One card or photograph at a time (the featured homes beside the about
	 * text and in the sidebar, the footer thumbnails, a home's gallery), with
	 * arrows on hover.
	 */
	function initSingleSlider() {
		document.querySelectorAll( '.south-slider, .south-footer-homes' ).forEach( function ( slider ) {
			var track = slider.querySelector( '.wp-block-post-template, [data-south-track]' );
			if ( ! track || track.children.length < 2 ) {
				return;
			}
			var items = Array.prototype.slice.call( track.children );
			var current = 0;
			function show( index ) {
				current = ( index + items.length ) % items.length;
				items.forEach( function ( item, i ) {
					item.classList.toggle( 'is-active', i === current );
				} );
			}
			var prev = arrow( 'prev' );
			var next = arrow( 'next' );
			prev.addEventListener( 'click', function () {
				show( current - 1 );
			} );
			next.addEventListener( 'click', function () {
				show( current + 1 );
			} );
			slider.appendChild( prev );
			slider.appendChild( next );
			slider.classList.add( 'is-ready' );
			show( 0 );
		} );
	}

	/**
	 * The map loads only when asked: nothing is requested from OpenStreetMap
	 * until a visitor presses "Show map". Without JavaScript the button stays
	 * hidden and "Open in maps" is an ordinary link.
	 */
	function initMaps() {
		document.querySelectorAll( '.south-map[data-map-src]' ).forEach( function ( map ) {
			var button = map.querySelector( '.south-map__show' );
			if ( ! button ) {
				return;
			}
			button.hidden = false;
			button.addEventListener( 'click', function () {
				var frame = document.createElement( 'iframe' );
				frame.src = map.getAttribute( 'data-map-src' );
				frame.title = label( 'mapTitle', 'Map' ) + ': ' + ( map.querySelector( '.south-map__address' ) || {} ).firstChild.textContent;
				frame.loading = 'lazy';
				frame.referrerPolicy = 'no-referrer-when-downgrade';
				map.classList.add( 'is-loaded' );
				map.appendChild( frame );
				frame.focus();
			} );
		} );
	}

	/**
	 * The enquiry form's pending state: once the browser has accepted the
	 * fields, the button says it is sending and cannot be pressed twice.
	 */
	function initEnquiryForms() {
		document.querySelectorAll( '[data-south-enquiry]' ).forEach( function ( form ) {
			form.addEventListener( 'submit', function () {
				var button = form.querySelector( '.south-enquiry__submit' );
				form.classList.add( 'is-sending' );
				form.setAttribute( 'aria-busy', 'true' );
				if ( button ) {
					button.textContent = button.getAttribute( 'data-sending' ) || button.textContent;
					window.setTimeout( function () {
						button.disabled = true;
					}, 0 );
				}
			} );
		} );
	}

	function init() {
		initStickyHeader();
		initHeroSlider();
		initCenterSlider();
		initSingleSlider();
		initMaps();
		initEnquiryForms();
	}

	if ( 'loading' === document.readyState ) {
		document.addEventListener( 'DOMContentLoaded', init );
	} else {
		init();
	}
}() );
