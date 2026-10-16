/**
 * Measure the same components on the HTML template and on the WordPress
 * theme, with one selector per site for each, and write the comparison as
 * Markdown tables (pasted into .dev/FIDELITY.md).
 *
 *   HTML_URL=http://127.0.0.1:8731 WP_URL=http://127.0.0.1:9481 \
 *     node .dev/fidelity.mjs > .dev/tmp/fidelity-tables.md
 *
 * Boxes are CSS pixels at 1440 and 390 wide; y is from the top of the page.
 * Rows where size or type differ by more than a tolerance are marked.
 */
import { chromium } from 'playwright';
import { measure, openPage } from './measure.mjs';

const HTML = ( process.env.HTML_URL || 'http://127.0.0.1:8731' ).replace( /\/$/, '' );
const WP = ( process.env.WP_URL || 'http://127.0.0.1:9481' ).replace( /\/$/, '' );

const PAGES = {
	home: [ '/index.html', '/', {
		'top bar': [ '.top-header-area', '.south-topbar' ],
		'menu bar': [ '.main-header-area', '.south-navbar' ],
		'menu link': [ '.classynav > ul > li:nth-child(3) > a', '.south-nav li:nth-child(2) .wp-block-navigation-item__content' ],
		hero: [ '.hero-area', '.south-hero' ],
		'hero title band': [ '.hero-slides-content h1', '.south-hero .is-active .south-band, .south-hero .south-band' ],
		'search tab': [ '.search-title', '.south-search__title' ],
		'search box': [ '.advanced-search-form', '.south-search__box' ],
		'search field': [ '#search-q', '#south-search-q' ],
		'search button': [ '.advanced-search-form .south-btn', '.south-search__submit' ],
		'section title': [ '.featured-properties-area .section-heading h2', '.south-featured .south-section-title' ],
		'section subtitle': [ '.featured-properties-area .section-heading p', '.south-featured .south-section-sub' ],
		card: [ '.single-featured-property', '.south-card' ],
		'card photo': [ '.property-thumb img', '.south-card__image img' ],
		'card tag': [ '.property-thumb .tag span', '.south-card__tag' ],
		'card price': [ '.list-price p', '.south-card__price' ],
		'card body': [ '.property-content', '.south-card__body' ],
		'card title': [ '.property-content h3', '.south-card__title' ],
		'card address': [ '.property-content .location', '.south-card__location' ],
		'card excerpt': [ '.property-content > p:not(.location)', '.south-card__excerpt p' ],
		'card facts row': [ '.property-meta-data', '.south-card__facts' ],
		'rent CTA': [ '.call-to-action-area', '.south-cta' ],
		'CTA title': [ '.cta-content h2', '.south-cta__title' ],
		'CTA line': [ '.cta-content .h6', '.south-cta__lead' ],
		testimonials: [ '.south-testimonials-area', '.south-testimonials' ],
		'realtor band': [ '.south-editor-area', '.south-editor-area' ],
		'realtor name': [ '.editor-content-area h2', '.south-editor__text .south-section-title' ],
		'realtor text': [ '.editor-content-area > p', '.south-editor__text p:not(.south-icon):not(.south-section-sub):not(.south-line)' ],
		footer: [ '.footer-area', '.south-footer' ],
		'footer heading': [ '.widget-title h2', '.south-widget-title' ],
		'footer text': [ '.footer-widget-area p', '.south-footer__columns .wp-block-column > p:not(.wp-block-site-title):not(.south-line)' ],
		copyright: [ '.copywrite-text', '.south-copyright' ],
	} ],
	listings: [ '/listings.html', '/listings/', {
		banner: [ '.breadcumb-area', '.south-banner' ],
		'banner title': [ '.breadcumb-title', '.south-banner .south-band' ],
		'results line': [ '#listingsSummary', '.south-toolbar__summary' ],
		'order select': [ '#orderBy', '#south-order' ],
		card: [ '#listingsGrid .single-featured-property', '.south-results .south-card' ],
		'page number': [ '.page-link', '.south-pagination .page-numbers' ],
	} ],
	property: [ '/property-mediterranean-villa.html', '/property/mediterranean-villa/', {
		photo: [ '.single-listings-photo img', '.south-gallery__slide img' ],
		price: [ '.listings-content .list-price p', '.south-listing__price' ],
		title: [ '.listing-title', '.south-listing__title' ],
		address: [ '.listings-content .location', '.south-listing__location' ],
		'fact cell': [ '.listing-facts li', '.south-facts li' ],
		feature: [ '.listings-core-features li', '.south-features li' ],
		'panel button': [ '.listings-btn-groups .btn', '.south-panels summary' ],
		'agent box': [ '.contact-realtor-wrapper', '.south-agent' ],
		'agent name': [ '.realtor---info h2', '.south-agent__name' ],
		'agent field': [ '#realtor-name', '.south-agent .south-field__control' ],
		map: [ '.listings-maps .kit-map__placeholder', '.south-listing .south-map' ],
	} ],
	journal: [ '/blog.html', '/blog/', {
		'post photo': [ '.blog-post-thumbnail img', '.south-post__image img' ],
		date: [ '.post-date a', '.south-post__date' ],
		headline: [ '.headline', '.south-post__title' ],
		byline: [ '.post-meta p', '.south-post__meta' ],
		excerpt: [ '.single-blog-area .post-content > p', '.south-post__excerpt p' ],
		'read more': [ '.single-blog-area .btn', '.south-button' ],
		'sidebar search': [ '.search-widget-area input', '.south-sidebar-search input' ],
		'sidebar heading': [ '.south-catagories-card h2', '.south-widget__title' ],
	} ],
	post: [ '/single-blog.html', '/how-to-get-the-best-deal-for-your-house/', {
		title: [ '.single-blog-area h1.headline', 'h1.south-post__title' ],
		subheading: [ '.post-subheading', '.south-post__content h2' ],
		paragraph: [ '.single-blog-area .post-content > p', '.south-post__content > p' ],
		'comments title': [ '.comments-title', '.wp-block-comments-title' ],
		'reply field': [ '#comment-name', '#author' ],
		'reply button': [ '.comment-form .btn', '.wp-block-post-comments-form .submit' ],
	} ],
	about: [ '/about-us.html', '/about/', {
		'about title': [ '.about-content-wrapper .section-heading h2', '.south-about .south-section-title' ],
		'about photo': [ '.about-content img', '.south-about__photo img' ],
		'about text': [ '.about-content p', '.south-about .wp-block-column > p' ],
		'team photo': [ '.team-member-thumb img', '.south-member__photo img' ],
		'team name': [ '.team-member-info h2', '.south-member__name' ],
	} ],
	contact: [ '/contact.html', '/contact/', {
		heading: [ '.contact-heading h2', '.south-underline-title' ],
		'hours row': [ '.content-sidebar .weekly-office-hours li', '.south-office .south-hours__row' ],
		'form field': [ '#contact-name', '.south-enquiry .south-field__control' ],
		'message field': [ '#contact-message', '.south-enquiry textarea' ],
		'send button': [ '.contact-form .btn', '.south-enquiry__submit' ],
		map: [ '.map-area .kit-map__placeholder', '.south-map' ],
	} ],
};

const fmt = ( m ) => m && m.found
	? `${ m.w }×${ m.h } @${ m.y } · ${ m.fontSize } ${ m.fontWeight }${ 'uppercase' === m.transform ? ' UC' : '' }${ 'italic' === m.style ? ' it' : '' } · ${ m.color.replace( /rgba?\(|\)/g, '' ) }`
	: '—';

const browser = await chromium.launch();
let marked = 0;
let rows = 0;
for ( const width of [ 1440, 390 ] ) {
	console.log( `\n## ${ width }px\n` );
	for ( const [ name, [ htmlPath, wpPath, map ] ] of Object.entries( PAGES ) ) {
		const htmlMap = Object.fromEntries( Object.entries( map ).map( ( [ k, v ] ) => [ k, v[ 0 ] ] ) );
		const wpMap = Object.fromEntries( Object.entries( map ).map( ( [ k, v ] ) => [ k, v[ 1 ] ] ) );
		const a = await openPage( browser, HTML + htmlPath, width );
		const left = await measure( a.page, htmlMap );
		const pageHeightA = await a.page.evaluate( () => document.documentElement.scrollHeight );
		await a.context.close();
		const b = await openPage( browser, WP + wpPath, width );
		const right = await measure( b.page, wpMap );
		const pageHeightB = await b.page.evaluate( () => document.documentElement.scrollHeight );
		await b.context.close();

		console.log( `### ${ name } (page height ${ pageHeightA } → ${ pageHeightB }, ${ ( ( pageHeightB - pageHeightA ) / pageHeightA * 100 ).toFixed( 1 ) }%)\n` );
		console.log( '| component | HTML template | WordPress | |' );
		console.log( '| --- | --- | --- | --- |' );
		for ( const key of Object.keys( map ) ) {
			const l = left[ key ];
			const r = right[ key ];
			rows++;
			let flag = '';
			if ( l?.found && r?.found ) {
				const dw = Math.abs( l.w - r.w ) > Math.max( 6, l.w * 0.05 );
				const dh = Math.abs( l.h - r.h ) > Math.max( 6, l.h * 0.08 );
				// Type only matters where the element holds words itself.
				const df = ( l.own || r.own ) && ( l.fontSize !== r.fontSize || l.fontWeight !== r.fontWeight || l.transform !== r.transform );
				flag = [ dw ? 'w' : '', dh ? 'h' : '', df ? 'type' : '' ].filter( Boolean ).join( ' ' );
			} else {
				flag = 'missing';
			}
			if ( flag ) {
				marked++;
			}
			console.log( `| ${ key } | ${ fmt( l ) } | ${ fmt( r ) } | ${ flag ? '**' + flag + '**' : '=' } |` );
		}
		console.log( '' );
	}
}
await browser.close();
console.log( `\n${ rows } component measurements, ${ marked } outside tolerance (w ±5%, h ±8%, any type difference).` );
