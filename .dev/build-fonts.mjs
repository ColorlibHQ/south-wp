/**
 * Puts the theme's webfonts in assets/fonts/.
 *
 * Open Sans is copied from the South HTML template (Fontsource 5.2.6, the same
 * files the template self-hosts, Latin and Latin Extended). The two alternates
 * the typography presets offer are fetched from Fontsource on jsDelivr, Latin
 * only. theme.json (written by build_theme.py) declares exactly these files:
 * an undeclared file is dead weight in the zip, and a declared one that is
 * missing is a 404 on every page.
 *
 *   node .dev/build-fonts.mjs [path-to-south-html-template]
 */

import { copyFileSync, writeFileSync, mkdirSync } from 'node:fs';
import { dirname, join, resolve } from 'node:path';
import { fileURLToPath } from 'node:url';

const root = resolve( dirname( fileURLToPath( import.meta.url ) ), '..' );
const out = join( root, 'assets/fonts' );
const template = process.argv[ 2 ] || '/Users/silkalns/Fresh Projects/colorlib-premium-upgrade/templates/south';
const VERSION = '5.2.6';

mkdirSync( out, { recursive: true } );
let bytes = 0;

for ( const weight of [ 300, 400, 600, 700 ] ) {
	for ( const subset of [ 'latin', 'latin-ext' ] ) {
		const name = `open-sans-${ subset }-${ weight }-normal.woff2`;
		copyFileSync( join( template, 'fonts/open-sans', name ), join( out, name ) );
		console.log( `  ${ name } (copied)` );
	}
}
copyFileSync( join( template, 'fonts/open-sans/LICENSE' ), join( out, 'OFL-open-sans.txt' ) );

const FETCH = [
	{ pkg: 'playfair-display', weights: [ 400, 700 ] },
	{ pkg: 'montserrat', weights: [ 400, 600, 700 ] },
];

for ( const { pkg, weights } of FETCH ) {
	for ( const weight of weights ) {
		const name = `${ pkg }-latin-${ weight }-normal.woff2`;
		const url = `https://cdn.jsdelivr.net/npm/@fontsource/${ pkg }@${ VERSION }/files/${ name }`;
		const res = await fetch( url );
		if ( ! res.ok ) {
			throw new Error( `${ res.status } ${ url }` );
		}
		const buf = Buffer.from( await res.arrayBuffer() );
		writeFileSync( join( out, name ), buf );
		bytes += buf.length;
		console.log( `  ${ name.padEnd( 40 ) } ${ ( buf.length / 1024 ).toFixed( 1 ) }KB` );
	}
}

console.log( `\n${ ( bytes / 1024 ).toFixed( 0 ) }KB fetched into assets/fonts/` );
