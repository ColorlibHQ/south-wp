/**
 * Computed styles and boxes for a named list of selectors on one page.
 *
 *   node .dev/measure.mjs <url> <width> '<json {name: selector}>'
 *
 * Prints JSON: name -> { found, x, y, w, h, fontSize, fontWeight, lineHeight,
 * color, bg, transform, style, letterSpacing, padding, margin, border, family }.
 * The first VISIBLE match is measured. Used by fidelity.mjs with one selector
 * map per site, so the same component is measured the same way on both.
 */
import { chromium } from 'playwright';
import { pathToFileURL } from 'node:url';

export async function measure( page, map ) {
	return page.evaluate( ( m ) => {
		const out = {};
		for ( const [ name, sel ] of Object.entries( m ) ) {
			let el = null;
			try {
				el = [ ...document.querySelectorAll( sel ) ].find( ( e ) => {
					const r = e.getBoundingClientRect();
					return r.width > 0 && r.height > 0;
				} );
			} catch ( e ) {
				out[ name ] = { found: false, error: 'bad selector' };
				continue;
			}
			if ( ! el ) {
				out[ name ] = { found: false };
				continue;
			}
			const r = el.getBoundingClientRect();
			const s = getComputedStyle( el );
			out[ name ] = {
				found: true,
				own: [ ...el.childNodes ].some( ( n ) => 3 === n.nodeType && n.textContent.trim() ) || /^(INPUT|TEXTAREA|SELECT|BUTTON)$/.test( el.tagName ),
				x: Math.round( r.left ),
				y: Math.round( r.top + window.scrollY ),
				w: Math.round( r.width ),
				h: Math.round( r.height ),
				fontSize: s.fontSize,
				fontWeight: s.fontWeight,
				lineHeight: s.lineHeight,
				family: s.fontFamily.split( ',' )[ 0 ].replace( /["']/g, '' ),
				color: s.color,
				bg: s.backgroundColor,
				transform: s.textTransform,
				style: s.fontStyle,
				letterSpacing: s.letterSpacing,
				padding: [ s.paddingTop, s.paddingRight, s.paddingBottom, s.paddingLeft ].map( ( v ) => Math.round( parseFloat( v ) ) ).join( ' ' ),
				margin: [ s.marginTop, s.marginBottom ].map( ( v ) => Math.round( parseFloat( v ) ) ).join( ' ' ),
				border: s.borderTopWidth + ' ' + s.borderTopColor,
				shadow: s.boxShadow === 'none' ? '' : s.boxShadow.slice( 0, 40 ),
			};
		}
		return out;
	}, map );
}

export async function openPage( browser, url, width ) {
	const context = await browser.newContext( {
		viewport: { width, height: width < 600 ? 844 : 900 },
		deviceScaleFactor: 1,
		reducedMotion: 'reduce',
		isMobile: width < 600,
		hasTouch: width < 600,
	} );
	await context.route( /google|doubleclick|googletagmanager/, ( r ) => r.abort() );
	const page = await context.newPage();
	await page.goto( url, { waitUntil: 'load', timeout: 60000 } );
	await page.waitForTimeout( 800 );
	return { context, page };
}

if ( import.meta.url === pathToFileURL( process.argv[ 1 ] ).href ) {
	const [ url, width, json ] = process.argv.slice( 2 );
	const browser = await chromium.launch();
	const { page } = await openPage( browser, url, Number( width ) );
	console.log( JSON.stringify( await measure( page, JSON.parse( json ) ), null, 1 ) );
	await browser.close();
}
