/**
 * Shared by the rendered checks: which pages, which palettes, how to apply a
 * palette or dark mode in the page, and a goto that fails on a bad status.
 *
 * Every check reads the same SOUTH_PATHS. Unioncorp's overflow check read a
 * differently named variable from the others, so `PATHS=...` silently checked
 * only the home page there. One name, one default list.
 */
import { readFileSync, readdirSync } from 'node:fs';
import { dirname, join, resolve } from 'node:path';
import { fileURLToPath } from 'node:url';

export const ROOT = resolve( dirname( fileURLToPath( import.meta.url ) ), '..' );

export const DEFAULT_PATHS = [
	'/',
	'/about/',
	'/contact/',
	'/blog/',
	'/listings/',
	'/property/mediterranean-villa/',
	'/how-to-get-the-best-deal-for-your-house/',
	'/?s=house',
	'/this-page-does-not-exist/',
];

export function paths() {
	return ( process.env.SOUTH_PATHS ? process.env.SOUTH_PATHS.split( ',' ) : DEFAULT_PATHS ).filter( Boolean );
}

export function base() {
	return ( process.env.WP_URL || 'http://127.0.0.1:9481' ).replace( /\/$/, '' );
}

/** Every colour variation as [title, css-declarations]. */
export function palettes() {
	const dir = join( ROOT, 'styles/colors' );
	return readdirSync( dir ).filter( ( f ) => f.endsWith( '.json' ) ).sort().map( ( f ) => {
		const json = JSON.parse( readFileSync( join( dir, f ), 'utf8' ) );
		const vars = json.settings.color.palette.map( ( p ) => `--wp--preset--color--${ p.slug }: ${ p.color };` ).join( ' ' );
		return [ json.title, vars ];
	} );
}

/** Which palettes to run: SOUTH_PALETTES=all for every one, else the default. */
export function selectedPalettes() {
	const all = palettes();
	return 'all' === process.env.SOUTH_PALETTES ? all : all.slice( 0, 1 );
}

const schemeCss = readFileSync( join( ROOT, 'assets/css/scheme.css' ), 'utf8' );

/**
 * Apply a palette (as a style variation would) and, optionally, dark mode.
 * The palette goes in as a late `:root` rule; in dark mode the scheme rules
 * are appended again after it, so they win exactly as they do on a site
 * where the variation's palette sits in the global styles.
 */
export async function applyScheme( page, vars, dark ) {
	await page.evaluate( ( { vars, dark, schemeCss } ) => {
		let el = document.getElementById( 'south-check-palette' );
		if ( ! el ) {
			el = document.createElement( 'style' );
			el.id = 'south-check-palette';
			document.head.appendChild( el );
		}
		el.textContent = `:root { ${ vars } }` + ( dark ? '\n' + schemeCss : '' );
		document.documentElement.classList.toggle( 'south-dark', dark );
	}, { vars, dark, schemeCss } );
	await page.waitForTimeout( 150 );
}

/** goto that throws on a 4xx/5xx, except where a 404 is the point. */
export async function visit( page, url, { allow404 = false } = {} ) {
	const response = await page.goto( url, { waitUntil: 'domcontentloaded', timeout: 90000 } );
	const status = response ? response.status() : 0;
	if ( status >= 400 && ! ( allow404 && 404 === status ) ) {
		throw new Error( `${ url } answered ${ status }` );
	}
	// Load every lazy image before anything is measured: an image arriving
	// mid-check moves the layout between measuring a box and photographing
	// what is behind it, and the sample lands on the wrong pixels.
	await page.evaluate( async () => {
		document.querySelectorAll( 'img[loading="lazy"]' ).forEach( ( img ) => ( img.loading = 'eager' ) );
		for ( let y = 0; y < document.body.scrollHeight; y += 500 ) {
			window.scrollTo( 0, y );
			await new Promise( ( r ) => setTimeout( r, 120 ) );
		}
		await Promise.race( [
			Promise.allSettled( [ ...document.images ].map( ( img ) => img.complete ? null : new Promise( ( r ) => {
				img.addEventListener( 'load', r, { once: true } );
				img.addEventListener( 'error', r, { once: true } );
			} ) ) ),
			new Promise( ( r ) => setTimeout( r, 30000 ) ),
		] );
		window.scrollTo( 0, 0 );
	} );
	await page.waitForTimeout( 800 );
	return status;
}
