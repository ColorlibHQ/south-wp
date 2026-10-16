/**
 * Every button must look like a button: its fill (or border) against what is
 * behind it at 3:1, and its label at 4.5:1.
 *
 * That is deliberately stricter than WCAG. 1.4.11 (non-text contrast) does not
 * require a boundary on a button whose readable label already identifies it,
 * so strictly a transparent button with a legible label passes. This theme
 * holds itself to the stricter reading because the failure it exists to catch —
 * every button rendering as bare text — would pass the letter of the rule while
 * leaving a visitor with nothing that reads as a control.
 *
 * contrast-rendered.mjs cannot see this, because it measures TEXT. A button
 * whose fill has gone transparent still has a readable label, so every text
 * check passes while every button on the site has stopped looking like one.
 * That shipped in dark mode: scheme.css lifted `primary` with a colour-mix on a
 * `flame` slug this theme does not have, the variable went invalid, and all 25
 * buttons rendered as bare text.
 *
 * On a plain ground the check climbs ancestors to the first opaque background.
 * On a photograph — a cover, or any background image — it measures the pixels
 * instead (photo-ground.mjs), against the worst tenth of them. Climbing there
 * finds the page, not the photograph, and reported a white outline button on a
 * darkened hero as white-on-white.
 *
 *   WP_URL=https://colorlibhub.com/south node .dev/button-boundary.mjs
 *   SOUTH_PALETTES=all SOUTH_PATHS=/,/about-us/ WP_URL=… node .dev/button-boundary.mjs
 *
 * Exits non-zero on any failure.
 */

import { chromium } from 'playwright';
import { contrast, parseColor, sampleBehind, worstTenth } from './photo-ground.mjs';
import { applyScheme, base as siteBase, paths as pathList, selectedPalettes, visit } from './lib.mjs';

const base = siteBase();
const paths = pathList();
const schemes = process.env.SOUTH_SCHEMES ? process.env.SOUTH_SCHEMES.split( ',' ) : [ 'light', 'dark' ];

const browser = await chromium.launch();
const failures = [];
let checked = 0;
let onPhotos = 0;

for ( const path of paths ) {
	// Reduced motion so nothing is mid-reveal while it is being measured.
	const context = await browser.newContext( {
		viewport: { width: 1440, height: 900 },
		deviceScaleFactor: 1,
		reducedMotion: 'reduce',
	} );
	const page = await context.newPage();
	await visit( page, base + path, { allow404: true } );
	for ( const [ palette, vars ] of selectedPalettes() ) {
	for ( const scheme of schemes ) {
	await applyScheme( page, vars, 'dark' === scheme );
	const where = `${ path } [${ palette }, ${ scheme }]`;

	const buttons = await page.evaluate( () => {
		return [ ...document.querySelectorAll( '.wp-block-button__link, .wp-element-button' ) ]
			// The scheme toggle is an icon-only control by design; its boundary
			// is its 44px hit area, not a fill.
			.filter( ( el ) => el.offsetWidth > 0 && 'hidden' !== getComputedStyle( el ).visibility && ! el.closest( '.south-scheme-toggle, .south-header-search, .south-sidebar-search' ) )
			.map( ( el, i ) => {
				el.setAttribute( 'data-uc-bb', String( i ) );
				const cs = getComputedStyle( el );
				let onPhoto = false;
				let surface = null;
				for ( let node = el.parentElement; node; node = node.parentElement ) {
					const s = getComputedStyle( node );
					if ( node.classList.contains( 'wp-block-cover' ) || ( s.backgroundImage && 'none' !== s.backgroundImage ) ) {
						onPhoto = true;
						break;
					}
					if ( ! surface && s.backgroundColor && 'rgba(0, 0, 0, 0)' !== s.backgroundColor && 'transparent' !== s.backgroundColor ) {
						surface = s.backgroundColor;
					}
				}
				return {
					i,
					text: el.innerText.trim().slice( 0, 28 ),
					fill: cs.backgroundColor,
					border: cs.borderTopColor,
					borderWidth: parseFloat( cs.borderTopWidth ) || 0,
					label: cs.color,
					onPhoto,
					surface: surface || 'rgb(255, 255, 255)',
				};
			} );
	} );

	for ( const b of buttons ) {
		checked++;
		const fill = parseColor( b.fill );
		const border = parseColor( b.border );
		const label = parseColor( b.label );
		const fillOpaque = !! fill && fill.a > 0.5;
		const hasBorder = !! border && border.a > 0.5 && b.borderWidth > 0;

		let boundary = 0;
		let labelRatio = 0;
		let onPhotoNote = '';

		if ( b.onPhoto ) {
			onPhotos++;
			const handle = await page.$( `[data-uc-bb="${ b.i }"]` );
			const ground = handle ? await sampleBehind( page, handle ) : null;
			if ( ! ground ) {
				failures.push( `${ where } "${ b.text }": could not be measured on its photograph` );
				continue;
			}
			boundary = Math.max( fillOpaque ? worstTenth( fill, ground.ring ) : 0, hasBorder ? worstTenth( border, ground.ring ) : 0 );
			labelRatio = fillOpaque ? contrast( label, fill ) : worstTenth( label, ground.interior );
			onPhotoNote = ' (on a photograph, worst tenth of its pixels)';
		} else {
			const surface = parseColor( b.surface );
			boundary = Math.max( fillOpaque ? contrast( fill, surface ) : 0, hasBorder ? contrast( border, surface ) : 0 );
			labelRatio = contrast( label, fillOpaque ? fill : surface );
		}

		const problems = [];
		if ( boundary < 3 ) {
			problems.push( `boundary ${ boundary.toFixed( 2 ) }:1${ fillOpaque || hasBorder ? '' : ' (no fill, no border)' }` );
		}
		if ( labelRatio < 4.5 ) {
			problems.push( `label ${ labelRatio.toFixed( 2 ) }:1` );
		}
		if ( problems.length ) {
			failures.push( `${ where } "${ b.text }": ${ problems.join( ', ' ) }${ onPhotoNote }` );
		}
	}
	}
	}

	await context.close();
}

await browser.close();

if ( failures.length ) {
	console.error( `${ failures.length } of ${ checked } button measurements fail (${ onPhotos } on photographs):` );
	[ ...new Set( failures ) ].slice( 0, 40 ).forEach( ( f ) => console.error( '  ' + f ) );
	process.exit( 1 );
}
console.log( `All ${ checked } button measurements pass: boundary >= 3:1, label >= 4.5:1 (${ onPhotos } on photographs; ${ paths.length } pages x ${ selectedPalettes().length } palettes x ${ schemes.length } schemes).` );
