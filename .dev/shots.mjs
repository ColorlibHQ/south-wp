/**
 * Full-page captures of a list of URLs at given widths, for side-by-side
 * comparison with the HTML template.
 *
 *   node .dev/shots.mjs <outdir> <width,width> <name=url> [name=url ...]
 *
 * Reduced motion and an eager-image pass, so reveals and lazy images are
 * settled before the capture. Requests to analytics hosts are aborted (an
 * edge-injected ga fetch on preview.colorlib.com never finishes).
 */
import { chromium } from 'playwright';
import { mkdirSync } from 'node:fs';

const [ out, widthsArg, ...pairs ] = process.argv.slice( 2 );
const widths = widthsArg.split( ',' ).map( Number );
mkdirSync( out, { recursive: true } );

const browser = await chromium.launch();
for ( const width of widths ) {
	const context = await browser.newContext( {
		viewport: { width, height: width < 600 ? 844 : 900 },
		deviceScaleFactor: 1,
		reducedMotion: 'reduce',
		isMobile: width < 600,
		hasTouch: width < 600,
	} );
	await context.route( /google|doubleclick|googletagmanager/, ( r ) => r.abort() );
	const page = await context.newPage();
	for ( const pair of pairs ) {
		const i = pair.indexOf( '=' );
		const name = pair.slice( 0, i );
		const url = pair.slice( i + 1 );
		try {
			await page.goto( url, { waitUntil: 'load', timeout: 60000 } );
			await page.evaluate( async () => {
				document.querySelectorAll( 'img[loading="lazy"]' ).forEach( ( img ) => ( img.loading = 'eager' ) );
				for ( let y = 0; y < document.body.scrollHeight; y += 500 ) {
					window.scrollTo( 0, y );
					await new Promise( ( r ) => setTimeout( r, 250 ) );
				}
				window.scrollTo( 0, 0 );
				// Playground serves images slowly: wait until each has loaded.
				await Promise.race( [
					Promise.allSettled( [ ...document.images ].map( ( img ) => img.complete ? null : new Promise( ( r ) => {
						img.addEventListener( 'load', r, { once: true } );
						img.addEventListener( 'error', r, { once: true } );
					} ) ) ),
					new Promise( ( r ) => setTimeout( r, 20000 ) ),
				] );
				await Promise.allSettled( [ ...document.images ].map( ( img ) => img.decode().catch( () => {} ) ) );
			} );
			await page.waitForTimeout( 900 );
			await page.screenshot( { path: `${ out }/${ name }-${ width }.png`, fullPage: true } );
			console.log( `${ name }-${ width }` );
		} catch ( e ) {
			console.log( `FAIL ${ name }-${ width }: ${ e.message.split( '\n' )[ 0 ] }` );
		}
	}
	await context.close();
}
await browser.close();
