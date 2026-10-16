/**
 * The theme and the plugin, switched on and off in the orders real sites use.
 *
 * Academia shipped a "Cannot redeclare" fatal that appeared only when the
 * plugin was activated with the theme ALREADY active (on activation the theme
 * is loaded first; on every other request plugins load first). So:
 *
 *   1. theme active, plugin absent: the site is complete, nothing leaks
 *   2. activate the plugin with the theme active
 *   3. deactivate it again
 *   4. another theme active: activate the plugin, import the demo, view homes
 *   5. switch back to South with the plugin active
 *
 * Run against a Playground started with .dev/blueprint-theme-only.json.
 *
 *   WP_URL=http://127.0.0.1:9482 node .dev/activation-check.mjs
 */
import { chromium } from 'playwright';

const site = ( process.env.WP_URL || 'http://127.0.0.1:9482' ).replace( /\/$/, '' );
const browser = await chromium.launch();
const page = await ( await browser.newContext( { viewport: { width: 1280, height: 900 } } ) ).newPage();
const failures = [];
const check = ( ok, label ) => {
	console.log( ( ok ? 'ok   ' : 'FAIL ' ) + label );
	if ( ! ok ) {
		failures.push( label );
	}
};
const ERRORS = /Fatal error|Cannot redeclare|critical error|Warning:|Notice:|Deprecated:|Parse error/;

async function visit( path ) {
	const response = await page.goto( site + path, { waitUntil: 'domcontentloaded', timeout: 120000 } );
	const html = await page.content();
	return { status: response ? response.status() : 0, html, text: await page.innerText( 'body' ).catch( () => '' ) };
}

async function clickAndLoad( selector ) {
	await Promise.all( [ page.waitForNavigation( { timeout: 120000 } ), page.click( selector ) ] );
	return page.content();
}

await page.goto( site + '/wp-login.php', { waitUntil: 'domcontentloaded' } );
await page.fill( '#user_login', 'admin' );
await page.fill( '#user_pass', 'password' );
await Promise.all( [ page.waitForNavigation(), page.click( '#wp-submit' ) ] );
// The first admin load finishes the starter site (the admin_init retry).
await visit( '/wp-admin/' );

// 1. Theme alone.
let r = await visit( '/' );
check( 200 === r.status && ! ERRORS.test( r.html ), 'theme alone: the home page renders with no PHP error' );
check( ! /\[south_/.test( r.text ), 'theme alone: no shortcode is printed as text' );
check( r.html.includes( 'south-search__form--site' ), 'theme alone: the search box searches the site' );
check( ! r.html.includes( 'south-featured' ), 'theme alone: the featured-homes section is left out' );
check( r.html.includes( 'south-without-listings' ), 'theme alone: the footer shows the newest post instead of homes' );
for ( const path of [ '/about/', '/contact/', '/blog/', '/?s=home' ] ) {
	r = await visit( path );
	check( 200 === r.status && ! ERRORS.test( r.html ) && ! /\[south_/.test( r.text ), `theme alone: ${ path } renders cleanly` );
}

// 2. Activate the plugin with the theme active.
await visit( '/wp-admin/plugins.php' );
let html = await clickAndLoad( 'a[id="activate-south-listings"], tr[data-slug="south-listings"] .activate a' );
check( ! ERRORS.test( html ) && /Plugin activated/.test( html ), 'plugin activated with South already active: no fatal' );
r = await visit( '/listings/' );
check( 200 === r.status && r.html.includes( 'south-toolbar' ) && ! ERRORS.test( r.html ), '/listings/ resolves at once (rewrites flushed)' );
r = await visit( '/' );
check( r.html.includes( 'south-search__form--listings' ) && /Properties<\/span>/.test( r.html ), 'the full search form and a Properties menu link appear' );

// 3. Deactivate.
await visit( '/wp-admin/plugins.php' );
html = await clickAndLoad( 'tr[data-slug="south-listings"] .deactivate a' );
check( ! ERRORS.test( html ) && /Plugin deactivated/.test( html ), 'plugin deactivated cleanly' );
r = await visit( '/' );
check( 200 === r.status && ! ERRORS.test( r.html ) && r.html.includes( 'south-search__form--site' ), 'theme falls back again after deactivation' );

// 4. Another theme: activate the plugin and import the demo there.
await visit( '/wp-admin/themes.php' );
html = await clickAndLoad( '.theme[data-slug="twentytwentyfive"] a.activate, a[aria-label*="Twenty Twenty-Five"].activate' );
check( ! ERRORS.test( html ), 'switched to Twenty Twenty-Five' );
await visit( '/wp-admin/plugins.php' );
html = await clickAndLoad( 'a[id="activate-south-listings"], tr[data-slug="south-listings"] .activate a' );
check( ! ERRORS.test( html ) && /Plugin activated/.test( html ), 'plugin activated under another theme' );
await visit( '/wp-admin/edit.php?post_type=property&page=south-listings-demo' );
html = await clickAndLoad( '#import' );
check( ! ERRORS.test( html ) && /demo homes, agents and journal posts are in place/.test( html ), 'demo imported under another theme' );
r = await visit( '/property/mediterranean-villa/' );
check( 200 === r.status && ! ERRORS.test( r.html ) && r.html.includes( 'south-facts' ) && r.html.includes( 'south-agent' ), 'under another theme a home shows its details and agent' );
r = await visit( '/listings/?status=rent' );
check( 200 === r.status && ! ERRORS.test( r.html ) && /Fireplace|loft/i.test( r.text ), 'under another theme the listings filter' );

// 5. Back to South, plugin still active.
await visit( '/wp-admin/themes.php' );
html = await clickAndLoad( '.theme[data-slug="south"] a.activate, a[aria-label*="South"].activate' );
check( ! ERRORS.test( html ), 'switched back to South with the plugin active' );
r = await visit( '/' );
check( 200 === r.status && ! ERRORS.test( r.html ) && r.html.includes( 'south-card__price' ), 'South home shows the imported homes' );
r = await visit( '/property/mediterranean-villa/' );
check( 200 === r.status && ! ERRORS.test( r.html ) && 1 === ( r.html.match( /<h1/g ) || [] ).length, 'South single home renders with one h1' );

await browser.close();
if ( failures.length ) {
	console.error( `\n${ failures.length } activation check(s) failed` );
	process.exit( 1 );
}
console.log( '\ntheme and plugin activate, deactivate and switch in every order without errors' );
