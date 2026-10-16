/**
 * Open every south/ pattern in the block editor and photograph it.
 *
 * The front end can look right while the editor shows "Type / to choose a
 * block" where an icon should be (Unioncorp 1.1.2) or an "unexpected or
 * invalid content" warning. This inserts each pattern into a new page in the
 * editor, waits, screenshots the canvas, and fails on either signal.
 *
 *   WP_URL=… WP_USER=admin WP_PASS=password node .dev/editor-shots.mjs [outdir]
 */
import { chromium } from 'playwright';
import { mkdirSync } from 'node:fs';
import { fileURLToPath } from 'node:url';
import { base } from './lib.mjs';

const site = base();
const out = process.argv[ 2 ] || fileURLToPath( new URL( './tmp/editor', import.meta.url ) );
mkdirSync( out, { recursive: true } );

const browser = await chromium.launch();
const page = await ( await browser.newContext( { viewport: { width: 1440, height: 1000 } } ) ).newPage();
await page.goto( site + '/wp-login.php', { waitUntil: 'domcontentloaded' } );
await page.fill( '#user_login', process.env.WP_USER || 'admin' );
await page.fill( '#user_pass', process.env.WP_PASS || 'password' );
await Promise.all( [ page.waitForNavigation(), page.click( '#wp-submit' ) ] );

await page.goto( site + '/wp-admin/post-new.php?post_type=page', { waitUntil: 'domcontentloaded', timeout: 120000 } );
await page.waitForFunction( () => window.wp?.data?.select( 'core/block-editor' ), null, { timeout: 120000 } );
await page.waitForTimeout( 5000 );
// Dismiss the welcome guide and any pattern chooser.
await page.keyboard.press( 'Escape' );
const close = page.locator( '.components-guide button[aria-label="Close"], .components-modal__header button[aria-label="Close"]' );
if ( await close.count() ) {
	await close.first().click().catch( () => {} );
}
await page.evaluate( () => {
	window.wp.data.dispatch( 'core/preferences' )?.set( 'core/edit-post', 'welcomeGuide', false );
} );

const patterns = await page.evaluate( async () => ( await window.wp.apiFetch( { path: '/wp/v2/block-patterns/patterns' } ) )
	.filter( ( p ) => p.name.startsWith( 'south/' ) ).map( ( p ) => ( { name: p.name, content: p.content } ) ) );

const problems = [];
for ( const pattern of patterns ) {
	await page.evaluate( ( content ) => {
		const { parse } = window.wp.blocks;
		window.wp.data.dispatch( 'core/block-editor' ).resetBlocks( parse( content ) );
		window.wp.data.dispatch( 'core/block-editor' ).clearSelectedBlock();
	}, pattern.content );
	await page.waitForTimeout( 3500 );
	const frame = page.frameLocator( 'iframe[name="editor-canvas"]' );
	const report = await page.frame( { name: 'editor-canvas' } )?.evaluate( () => ( {
		placeholder: [ ...document.querySelectorAll( '[data-empty="true"], .block-editor-default-block-appender' ) ]
			.filter( ( el ) => /Type \/ to choose a block/.test( el.getAttribute( 'aria-label' ) || el.textContent || '' ) ).length,
		// Only invalid-content warnings count. "Block cannot be rendered inside
		// itself" and "Comments are not enabled" are what a TEMPLATE's
		// post-content and comments say when dropped into a page.
		warnings: [ ...document.querySelectorAll( '.block-editor-warning' ) ]
			.filter( ( el ) => ! /rendered inside itself|not enabled/i.test( el.textContent ) ).length,
		context: [ ...document.querySelectorAll( '.block-editor-warning' ) ]
			.filter( ( el ) => /rendered inside itself|not enabled/i.test( el.textContent ) ).length,
		missing: [ ...document.querySelectorAll( '.wp-block-missing, [data-type="core/missing"]' ) ].length,
	} ) ) || { placeholder: -1, warnings: -1, missing: -1 };
	const slug = pattern.name.replace( 'south/', '' );
	await frame.locator( 'body' ).screenshot( { path: `${ out }/${ slug }.png` } ).catch( () => page.screenshot( { path: `${ out }/${ slug }.png` } ) );
	const bad = report.placeholder > 0 || report.warnings || report.missing;
	console.log( `${ bad ? 'FAIL' : 'ok  ' } ${ slug.padEnd( 26 ) } placeholders ${ report.placeholder }, invalid-block warnings ${ report.warnings }, missing ${ report.missing }${ report.context ? `, context notices ${ report.context }` : '' }` );
	if ( bad ) {
		problems.push( slug );
	}
}

await browser.close();
if ( problems.length ) {
	console.error( `${ problems.length } pattern(s) look wrong in the editor: ${ problems.join( ', ' ) }` );
	process.exit( 1 );
}
console.log( `all ${ patterns.length } patterns open in the editor without placeholders or invalid blocks; screenshots in ${ out }` );
