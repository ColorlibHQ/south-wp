/**
 * Measure text contrast on a real rendered page, under every colour palette.
 *
 * theme.json's own audit checks the palette's numbers. It cannot catch a
 * pattern that puts the wrong slug on the wrong ground — which is how every
 * cover headline and the whole footer came out black-on-black in the two dark
 * palettes: they asked for `base`, and `base` is the page background, so in a
 * dark palette it is nearly black.
 *
 * This walks the rendered DOM, resolves each text node's effective background
 * by climbing its ancestors, and reports anything under 4.5:1. It is the check
 * that would have caught that bug, so it runs over all eight palettes.
 *
 * Text on a photograph is measured too, by its pixels (photo-ground.mjs). This
 * check used to skip it: climbing ancestors from a cover headline walks past
 * the photograph and its dimming layer, which are siblings, and finds the page.
 * Skipping meant the hero, the page banners, the statistics and the "Why us"
 * section were never measured at all — and the primary-coloured eyebrow on the
 * darkened "Why us" photograph shipped at under 3:1 without a single warning.
 * An element taken out of flow (absolute or fixed) is measured the same way,
 * since what is behind it is not in its ancestry either.
 *
 * South runs it over every page in SOUTH_PATHS, in light and dark, under the
 * default palette or (SOUTH_PALETTES=all) all eight, applied in the page the
 * way a style variation applies them. Text of 24px, or 18.66px bold, is
 * large text under WCAG and needs 3:1; everything else 4.5:1.
 *
 *   WP_URL=http://127.0.0.1:9481 SOUTH_PALETTES=all node .dev/contrast-rendered.mjs
 *
 * @package South
 */

import { chromium } from 'playwright';
import { contrast, parseColor, sampleBehind, worstTenth } from './photo-ground.mjs';
import { applyScheme, base, paths, selectedPalettes, visit } from './lib.mjs';

const site = base();
const schemes = process.env.SOUTH_SCHEMES ? process.env.SOUTH_SCHEMES.split( ',' ) : [ 'light', 'dark' ];

const browser = await chromium.launch();
// Reduced motion, so no reveal or slide is mid-fade while the pixels behind
// it are photographed. Scale 1, so a screenshot pixel is a CSS pixel.
const page = await ( await browser.newContext( {
	viewport: { width: 1400, height: 1000 },
	deviceScaleFactor: 1,
	reducedMotion: 'reduce',
} ) ).newPage();

let measured = 0;
let onPhotos = 0;
const failures = [];

for ( const path of paths() ) {
	await visit( page, site + path, { allow404: true } );
	for ( const [ palette, vars ] of selectedPalettes() ) {
		for ( const scheme of schemes ) {
			await applyScheme( page, vars, 'dark' === scheme );
			const where = `${ path } [${ palette }, ${ scheme }]`;
			const candidates = await page.evaluate( () => {
				// rgb()/rgba(), or color(srgb r g b / a), which color-mix() produces.
				const alphaOf = ( value ) => {
					if ( ! value || 'transparent' === value ) {
						return 0;
					}
					const m = value.match( /\/\s*([\d.]+)\s*\)$/ ) || value.match( /^rgba\([^,]+,[^,]+,[^,]+,\s*([\d.]+)\s*\)$/ );
					return m ? Number( m[ 1 ] ) : 1;
				};

				const out = [];

				document.querySelectorAll( 'h1,h2,h3,h4,h5,h6,p,li,a,dt,dd,summary,label,button,span' ).forEach( ( el, i ) => {
					const own = [ ...el.childNodes ].some( ( n ) => 3 === n.nodeType && n.textContent.trim() );
					if ( ! own ) {
						return;
					}

					// Too small to be read, or parked off the side of the page on purpose, as
					// the enquiry form's honeypot is: nobody sees either.
					// WCAG 1.4.3 exempts logotypes: the site title is the brand's
				// wordmark (the template draws it as an image).
				if ( el.closest( '.wp-block-site-title' ) ) {
					return;
				}

				const box = el.getBoundingClientRect();
					if ( box.width < 6 || box.height < 6 || box.right <= 0 || box.left >= document.documentElement.clientWidth ) {
						return;
					}

					const style = getComputedStyle( el );
					if ( 'hidden' === style.visibility || '0' === style.opacity || 'none' === style.display ) {
						return;
					}

					// Climb to whichever comes first: an opaque background colour, which is
					// the ground; or a photograph, or an element out of flow, where the
					// ground is pixels. A translucent background — a tinted panel laid on a
					// photograph — is not a ground on its own, so the climb carries on past it.
					let ground = null;
					let pixels = false;
					for ( let node = el; node; node = node.parentElement ) {
						const s = getComputedStyle( node );
						if ( node.classList.contains( 'wp-block-cover' ) || ( s.backgroundImage && 'none' !== s.backgroundImage ) ) {
							pixels = true;
							break;
						}
						const alpha = alphaOf( s.backgroundColor );
						if ( alpha >= 0.99 ) {
							ground = s.backgroundColor;
							break;
						}
						if ( alpha > 0 || 'absolute' === s.position || 'fixed' === s.position ) {
							pixels = true;
							break;
						}
					}

					el.setAttribute( 'data-uc-cr', String( i ) );
					out.push( {
						i,
						text: el.textContent.trim().replace( /\s+/g, ' ' ).slice( 0, 40 ),
						colour: style.color,
						large: parseFloat( style.fontSize ) >= 24 || ( parseFloat( style.fontSize ) >= 18.66 && Number( style.fontWeight ) >= 700 ),
						// Nothing painted anywhere up the tree is the browser's white canvas.
						ground: pixels ? null : ( ground || 'rgb(255, 255, 255)' ),
					} );
				} );

				return out;
			} );

			for ( const c of candidates ) {
				const colour = parseColor( c.colour );
				if ( ! colour ) {
					continue;
				}
				measured++;
				const need = c.large ? 3 : 4.5;
				if ( null !== c.ground ) {
					const ground = parseColor( c.ground );
					const r = ground ? contrast( colour, ground ) : null;
					if ( null !== r && r < need ) {
						failures.push( `${ where } ${ r.toFixed( 2 ) } < ${ need } "${ c.text }" ${ c.colour } on ${ c.ground }` );
					}
					continue;
				}
				onPhotos++;
				const handle = await page.$( `[data-uc-cr="${ c.i }"]` );
				const sampled = handle ? await sampleBehind( page, handle, 0, { text: true } ) : null;
				const r = sampled ? worstTenth( colour, sampled.interior ) : null;
				if ( null === r ) {
					failures.push( `${ where } unmeasurable on its photograph: "${ c.text }"` );
				} else if ( r < need ) {
					failures.push( `${ where } ${ r.toFixed( 2 ) } < ${ need } "${ c.text }" ${ c.colour } on the photograph (worst tenth)` );
				}
			}
		}
	}
}

await browser.close();

if ( failures.length ) {
	console.error( `${ failures.length } text nodes under their WCAG ratio (${ measured } measured, ${ onPhotos } on photographs):` );
	[ ...new Set( failures ) ].forEach( ( f ) => console.error( '  ' + f ) );
	process.exit( 1 );
}
console.log( `every text node is at least 4.5:1 (3:1 when large): ${ measured } measurements, ${ onPhotos } on photographs, ${ paths().length } pages x ${ selectedPalettes().length } palettes x ${ schemes.length } schemes` );
