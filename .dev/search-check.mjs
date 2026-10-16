/**
 * The property search, with JavaScript off and on.
 *
 * Off: the forms are plain GET forms. Filtering from the home page's search
 * box, sorting with the toolbar's own button and paging must all work, and the
 * query must stay in the address (and in the page links).
 * On: the same actions on /listings/ must swap the results without a single
 * document navigation, update the address, and survive Back.
 *
 *   WP_URL=http://127.0.0.1:9481 node .dev/search-check.mjs
 */
import { chromium } from 'playwright';
import { base } from './lib.mjs';

const site = base();
const browser = await chromium.launch();
const failures = [];
const check = ( ok, label ) => {
	console.log( ( ok ? 'ok   ' : 'FAIL ' ) + label );
	if ( ! ok ) {
		failures.push( label );
	}
};

async function cards( page ) {
	return page.$$eval( '.south-results .south-card', ( list ) => list.map( ( c ) => ( {
		title: c.querySelector( '.south-card__title' )?.textContent.trim(),
		price: Number( ( c.querySelector( '.south-card__price' )?.textContent || '' ).replace( /[^0-9]/g, '' ) ),
		status: c.querySelector( '.south-card__tag' )?.textContent.trim(),
		where: c.querySelector( '.south-card__location' )?.textContent.trim(),
	} ) ) );
}
const summary = ( page ) => page.$eval( '.south-toolbar__summary', ( el ) => el.textContent.trim() );

// ---------------------------------------------------------------- JS off
{
	const context = await browser.newContext( { javaScriptEnabled: false, viewport: { width: 1440, height: 900 } } );
	const page = await context.newPage();
	await page.goto( site + '/', { waitUntil: 'domcontentloaded' } );
	check( await page.isVisible( '[data-south-more]' ), 'no JS: the extra filters are visible' );
	await page.selectOption( '#south-search-city', 'pasadena' );
	await Promise.all( [ page.waitForNavigation(), page.click( '.south-search__submit' ) ] );
	const url = new URL( page.url() );
	check( '/listings/' === url.pathname && 'pasadena' === url.searchParams.get( 'city' ), `no JS: search lands on /listings/?city=pasadena (${ url.pathname }${ url.search.slice( 0, 60 ) })` );
	let list = await cards( page );
	check( list.length === 2 && list.every( ( c ) => /Pasadena/.test( c.where ) ), `no JS: 2 Pasadena homes (${ list.map( ( c ) => c.where ).join( '; ' ) })` );
	check( /2 properties match your search/.test( await summary( page ) ), `no JS: results line "${ await summary( page ) }"` );

	await page.goto( site + '/listings/?status=rent', { waitUntil: 'domcontentloaded' } );
	list = await cards( page );
	check( list.length === 3 && list.every( ( c ) => /Rent/i.test( c.status ) ), `no JS: ?status=rent gives the 3 rentals (${ list.length })` );

	await page.goto( site + '/listings/', { waitUntil: 'domcontentloaded' } );
	await page.selectOption( '#south-order', 'price-desc' );
	await Promise.all( [ page.waitForNavigation(), page.click( '.south-toolbar__apply' ) ] );
	list = await cards( page );
	const prices = list.map( ( c ) => c.price );
	check( prices.length === 6 && prices.every( ( p, i ) => ! i || prices[ i - 1 ] >= p ), `no JS: sort by highest price (${ prices.join( ', ' ) })` );
	const next = await page.$eval( '.south-pagination a.page-numbers', ( a ) => a.href ).catch( () => '' );
	check( /page\/2/.test( next ) && /sort=price-desc/.test( next ), `no JS: page 2 link keeps the sort (${ next.replace( site, '' ) })` );
	await page.goto( next, { waitUntil: 'domcontentloaded' } );
	check( ( await cards( page ) ).length === 3 && /page 2 of 2/.test( await summary( page ) ), 'no JS: page 2 shows the last 3 homes' );

	await page.goto( site + '/listings/?beds=4&area_min=500&area_max=4500&year_min=1900', { waitUntil: 'domcontentloaded' } );
	list = await cards( page );
	check( list.length === 2, `no JS: 4+ bedrooms with untouched sliders gives 2 homes (${ list.map( ( c ) => c.title ).join( '; ' ) })` );
	await context.close();
}

// ---------------------------------------------------------------- JS on
{
	const context = await browser.newContext( { viewport: { width: 1440, height: 900 } } );
	const page = await context.newPage();
	await page.goto( site + '/listings/', { waitUntil: 'load' } );
	await page.waitForTimeout( 1500 );
	// A real page load wipes window; pushState does not. (Playwright's
	// framenavigated also fires for pushState, so it cannot tell them apart.)
	const mark = () => page.evaluate( () => {
		window.__southMark = 1;
	} );
	const reloaded = async () => ( await page.evaluate( () => window.__southMark ) ) ? 0 : 1;
	await mark();
	let navigations = 0;
	check( await page.isHidden( '[data-south-more]' ), 'JS: the extra filters fold behind "+ More filters"' );
	check( await page.isVisible( '[data-south-view]' ), 'JS: the grid/list view buttons appear' );

	await page.selectOption( '#south-search-type', 'house' );
	await page.click( '.south-search__submit' );
	await page.waitForFunction( () => /match your search/.test( document.querySelector( '.south-toolbar__summary' )?.textContent || '' ), null, { timeout: 30000 } );
	let list = await cards( page );
	const url = new URL( page.url() );
	navigations = await reloaded();
	check( 0 === navigations, `JS: searching swapped the results without a page load (${ navigations } navigations)` );
	check( 'house' === url.searchParams.get( 'type' ) && ! url.searchParams.has( 'area_min' ), `JS: the address carries the search and drops untouched sliders (${ url.search })` );
	check( list.length === 2, `JS: two houses (${ list.map( ( c ) => c.title ).join( '; ' ) })` );

	await page.goto( site + '/listings/', { waitUntil: 'load' } );
	await page.waitForTimeout( 1500 );
	await mark();
	await page.selectOption( '#south-order', 'price-asc' );
	await page.waitForFunction( () => /sort=price-asc/.test( location.search ), null, { timeout: 30000 } );
	await page.waitForTimeout( 800 );
	list = await cards( page );
	const prices = list.map( ( c ) => c.price );
	navigations = await reloaded();
	check( 0 === navigations && prices.every( ( p, i ) => ! i || prices[ i - 1 ] <= p ), `JS: sorting by lowest price swaps in place (${ prices.join( ', ' ) })` );
	await page.click( '.south-pagination a.page-numbers' );
	await page.waitForFunction( () => /page\/2/.test( location.pathname ), null, { timeout: 30000 } );
	await page.waitForTimeout( 800 );
	navigations = await reloaded();
	check( 0 === navigations && ( await cards( page ) ).length === 3, `JS: page 2 without a page load (${ navigations } navigations)` );
	await page.goBack();
	await page.waitForTimeout( 2500 );
	check( ( await cards( page ) ).length === 6, 'JS: Back restores page 1' );

	await page.click( '.south-view--list' );
	check( await page.$eval( '.south-results', ( el ) => el.classList.contains( 'is-list' ) ), 'JS: list view switches the layout' );
	await context.close();
}

await browser.close();
if ( failures.length ) {
	console.error( `\n${ failures.length } search check(s) failed` );
	process.exit( 1 );
}
console.log( '\nsearch works with JavaScript off and on' );
