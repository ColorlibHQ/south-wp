<?php
/**
 * Build the South documentation page, a child of the product page.
 *
 * Written for someone who has just installed South and wants their listings
 * online. Same structure as Pato's documentation page; every fact was checked
 * against this repository: menu locations, field names, search parameters,
 * filter names and the enquiry array keys.
 *
 * Images are found by file name; the importer set their alt text. The run stops
 * before saving if an image is missing. Idempotent; keeps the page's current
 * status, and a new page starts as a draft.
 *
 *   Copy to a UNIQUE /tmp path, then from the WordPress root:
 *     sudo -u web_colorlib_com wp --url=https://colorlib.com/wp/ eval "require '/tmp/<unique>.php';"
 */

defined( 'ABSPATH' ) || exit;

$slug     = 'documentation';
$parent   = 387242;                    // the South product page
$demo     = 'https://colorlibhub.com/south/';
$download = 'https://updates.colorlib.com/download/theme/south.zip';
$plugin   = 'https://updates.colorlib.com/download/plugin/south-listings.zip';
$support  = 'https://colorlibsupport.com/';
$btn_css  = 'display:inline-block !important;vertical-align:middle !important;margin-right:12px !important;margin-bottom:10px !important;';

$found    = get_posts(
	array(
		'post_type'   => 'page',
		'name'        => $slug,
		'post_parent' => $parent,
		'post_status' => array( 'publish', 'draft', 'pending', 'private', 'future' ),
		'numberposts' => 1,
	)
);
$existing = $found ? $found[0] : null;
$page_id  = $existing ? $existing->ID : 0;

// Token => [file in the media library, caption (HTML), max width in px or 0].
// $GLOBALS because `wp eval` runs this file inside a function.
$GLOBALS['south_docs_missing'] = array();
$GLOBALS['south_docs_figures'] = array(
	'demo'       => array( 'south-docs-demo-import.png', 'Properties → Demo content.', 0 ),
	'properties' => array( 'south-docs-properties.png', 'Properties → All properties, after the demo import.', 0 ),
	'details'    => array( 'south-docs-property-details.png', 'The Property details box. In the block editor it sits in the <em>Meta Boxes</em> panel along the bottom of the screen — open it if you only see its title bar.', 0 ),
	'listings'   => array( 'south-block-theme-listings.jpg', 'The cards on the Properties page read the same fields: tag, price, address, rooms and area.', 0 ),
	'palettes'   => array( 'south-block-theme-palettes.jpg', 'One section in four of the eight palettes: South, Terracotta, Harbour and Midnight.', 0 ),
);

// An upload over WordPress's 2560px threshold is stored as `name-scaled.jpg`
// (the 2992px screenshots are), so accept that name too.
function south_docs_attachment_id( $file ) {
	global $wpdb;
	$scaled = preg_replace( '/\.(jpe?g|png)$/i', '-scaled.$1', $file );
	return (int) $wpdb->get_var(
		$wpdb->prepare(
			"SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key = '_wp_attached_file' AND ( meta_value IN ( %s, %s ) OR meta_value LIKE %s OR meta_value LIKE %s ) ORDER BY post_id ASC LIMIT 1",
			$file,
			$scaled,
			'%/' . $wpdb->esc_like( $file ),
			'%/' . $wpdb->esc_like( $scaled )
		)
	);
}

function south_docs_figure( $m ) {
	$figures = $GLOBALS['south_docs_figures'];
	if ( ! isset( $figures[ $m[1] ] ) ) {
		$GLOBALS['south_docs_missing'][] = 'unknown token ' . $m[1];
		return '';
	}
	list( $file, $caption, $max ) = $figures[ $m[1] ];
	$id = south_docs_attachment_id( $file );
	if ( ! $id ) {
		$GLOBALS['south_docs_missing'][] = $file;
		return '';
	}
	$url  = wp_get_attachment_url( $id );
	$meta = wp_get_attachment_metadata( $id );
	$size = ( ! empty( $meta['width'] ) && ! empty( $meta['height'] ) ) ? ' width="' . (int) $meta['width'] . '" height="' . (int) $meta['height'] . '"' : '';
	$alt  = get_post_meta( $id, '_wp_attachment_image_alt', true );

	return '<figure class="south-docs-fig"' . ( $max ? ' style="max-width:' . (int) $max . 'px"' : '' ) . '>'
		. '<a href="' . esc_url( $url ) . '" target="_blank" rel="noopener"><img src="' . esc_url( $url ) . '" alt="' . esc_attr( $alt ) . '"' . $size . ' loading="lazy" decoding="async"></a>'
		. ( $caption ? '<figcaption>' . $caption . '</figcaption>' : '' )
		. '</figure>';
}

function south_docs_section( $n, $anchor, $head, $body ) {
	$body = preg_replace_callback( '/\{\{fig:([a-z0-9-]+)\}\}/', 'south_docs_figure', $body );

	return '[vc_row el_id="' . $anchor . '" el_class="south-docs" css=".vc_custom_southdoc' . $n . '{padding-top:34px !important;padding-bottom:6px !important;}"][vc_column width="1/1"]'
		. '[vcex_heading text="' . esc_attr( $head ) . '" tag="h2" font_size="28px" bottom_margin="14px" font_weight="700"]'
		. '[vc_column_text css=".vc_custom_southdoct' . $n . '{max-width:860px !important;}"]' . $body . '[/vc_column_text]'
		. '[/vc_column][/vc_row]';
}

$css = '<style>'
	. 'html{scroll-behavior:smooth}.south-docs{scroll-margin-top:110px}'
	. '.south-docs h3{font-size:20px;margin:30px 0 10px}'
	. '.south-docs-fig{margin:22px 0 30px}.south-docs-fig img{display:block;width:100%;height:auto;border:1px solid #e1dddd;border-radius:8px}'
	. '.south-docs-fig figcaption{font-size:14px;line-height:1.55;color:#6b6b6b;margin-top:9px}'
	. '.south-docs-steps li{margin-bottom:8px}'
	. '.south-docs-table{overflow-x:auto;margin:18px 0 24px}.south-docs-table table{border-collapse:collapse;width:100%;min-width:520px;font-size:15px}'
	. '.south-docs-table th,.south-docs-table td{border-bottom:1px solid #e1dddd;padding:9px 10px;text-align:left;vertical-align:top}.south-docs-table th{background:#f5f7f9}'
	. '.south-docs pre{background:#f5f7f9;padding:16px 18px;border-radius:6px;overflow-x:auto;font-size:14px;line-height:1.6;white-space:pre}'
	. '.south-docs-toc{columns:2;column-gap:40px;max-width:720px;margin:0 auto;text-align:left;padding-left:22px}.south-docs-toc li{margin-bottom:6px;break-inside:avoid}'
	. '@media (max-width:640px){.south-docs-toc{columns:1}}'
	. '</style>';

$toc_items = array(
	'quick-start' => 'The short version',
	'install'     => 'Install the theme and the plugin',
	'demo'        => 'Demo content',
	'properties'  => 'Add a property',
	'agents'      => 'Agents',
	'search'      => 'The property search',
	'pages'       => 'Home page and sections',
	'enquiries'   => 'Enquiries',
	'styles'      => 'Colours, fonts and dark mode',
	'maps'        => 'Maps',
	'updates'     => 'Updates and privacy',
);
$toc = '<ol class="south-docs-toc">';
foreach ( $toc_items as $anchor => $label ) {
	$toc .= '<li><a href="#' . $anchor . '">' . esc_html( $label ) . '</a></li>';
}
$toc .= '</ol>';

$sections = '';

$sections .= south_docs_section( '01', 'quick-start', 'The short version', <<<'HTML'
<ol class="south-docs-steps">
<li><strong>Install and activate the South theme, then the South Listings plugin.</strong> The theme builds your Home, About, Contact and Blog pages on its own. <a href="#install">Install</a></li>
<li><strong>Optionally import the demo homes</strong> to see everything working, then replace them with your own. <a href="#demo">Demo content</a></li>
<li><strong>Add your agents, then your properties.</strong> <a href="#agents">Agents</a> · <a href="#properties">Add a property</a></li>
<li><strong>Change the contact details, hours and copy</strong> on the pages by clicking the text and typing. <a href="#pages">Home page and sections</a></li>
<li><strong>Send yourself a test enquiry</strong> from the contact page and a property page. <a href="#enquiries">Enquiries</a></li>
<li><strong>Pick colours and fonts</strong> if South’s brown is not yours. <a href="#styles">Colours, fonts and dark mode</a></li>
</ol>
HTML
);

$sections .= south_docs_section( '02', 'install', 'Install the theme and the plugin', <<<'HTML'
<p>South is two downloads, both buttons at the top of this page: the <strong>theme</strong> (south.zip) and the <strong>South Listings plugin</strong> (south-listings.zip). <strong>Do not unzip them</strong> — WordPress installs the zip files.</p>
<ol class="south-docs-steps">
<li>Appearance → Themes → <strong>Add New Theme</strong> → <strong>Upload Theme</strong>, choose <code>south.zip</code>, install and <strong>Activate</strong>.</li>
<li>Plugins → <strong>Add New Plugin</strong> → <strong>Upload Plugin</strong>, choose <code>south-listings.zip</code>, install and <strong>Activate</strong>.</li>
</ol>
<p>Activating the theme creates Home, About, Contact and Blog pages from South’s patterns, sets Home as the front page and Blog as the posts page, and fills the header menu. It never changes pages you already had.</p>
<p><strong>Why a separate plugin?</strong> Your properties are content, and content should not disappear when you change theme. With the plugin, homes, agents and the search stay put whatever theme the site uses. Without it, South still works as an agency site: its property sections are simply left out.</p>
HTML
);

$sections .= south_docs_section( '03', 'demo', 'Demo content', <<<'HTML'
<p>To see South as it looks in the <a href="https://colorlibhub.com/south/">live demo</a>, go to <strong>Properties → Demo content</strong> and click <strong>Import demo content</strong>.</p>
{{fig:demo}}
<p>It adds the template’s nine homes (six of them featured), three agents and three journal posts with their photographs, and puts Properties and Find a Home in the menu. It creates no user accounts; the journal posts are attributed to you. Running it again adds nothing new.</p>
<p>When you are ready for your own listings, <strong>Remove demo content</strong> deletes exactly what the import added — the homes, agents, posts and their photographs — and nothing else.</p>
HTML
);

$sections .= south_docs_section( '04', 'properties', 'Add a property', <<<'HTML'
<p>Go to <strong>Properties → Add property</strong>.</p>
<ol class="south-docs-steps">
<li><strong>Title</strong> — the name on the card and the page, for example “Mediterranean villa with pool”.</li>
<li><strong>Description</strong> — write it in the editor like any post.</li>
<li><strong>Featured image</strong> — the card photo and the photo at the top of the property page.</li>
<li><strong>Excerpt</strong> — the one or two lines under the address on the card.</li>
<li>On the right: <strong>Status</strong> (For Sale or For Rent), <strong>Property types</strong> and <strong>Cities</strong>. These are what the search filters on.</li>
<li>Below the editor: <strong>Property details</strong>, with everything else.</li>
</ol>
{{fig:details}}
<h3>The fields</h3>
<div class="south-docs-table"><table>
<tr><th>Group</th><th>Fields</th></tr>
<tr><td>Price and listing</td><td>Price (numbers only — the sale price, or the rent per period), rent period, reference, Featured (shows the home in the Featured Properties grid and the footer), New listing (adds the tag icon on the card)</td></tr>
<tr><td>Rooms and size</td><td>Bedrooms, bathrooms, garage spaces, floor area in sq ft, year built</td></tr>
<tr><td>Location</td><td>Address, neighbourhood, latitude and longitude (for the map)</td></tr>
<tr><td>More</td><td>Outdoor space, view, furnished, pets, features (one per line), rooms and sizes, a gallery, and the agent</td></tr>
</table></div>
{{fig:properties}}
{{fig:listings}}
HTML
);

$sections .= south_docs_section( '05', 'agents', 'Agents', <<<'HTML'
<p>Agents live under <strong>Properties → Agents</strong>. Each one has a name, a role (for example “Realtor”), a phone number and an email address; the portrait is the agent’s featured image. Choose the agent in a property’s details, and their card and an enquiry form appear beside that property’s description.</p>
HTML
);

$sections .= south_docs_section( '06', 'search', 'The property search', <<<'HTML'
<p>The search box under the hero and on the Properties page filters by keyword, city, type, sale or rent, garages, bedrooms, bathrooms, floor area and year built, and <strong>More filters</strong> adds price, rent, outdoor space, view, furnished and pets. Results can be sorted, shown as a grid or a list, and paged six at a time.</p>
<p>The search runs on ordinary address parameters, so it works with JavaScript turned off, and any search can be bookmarked or linked to. With JavaScript, results update without reloading the page and the Back button still works. You can link straight to a search, for example from a menu or a button:</p>
<pre>/listings/?city=malibu
/listings/?status=for-rent&amp;beds=2
/listings/?type=house&amp;area_min=2000&amp;sort=newest</pre>
<div class="south-docs-table"><table>
<tr><th>Parameter</th><th>Meaning</th></tr>
<tr><td><code>q</code></td><td>Keyword</td></tr>
<tr><td><code>city</code>, <code>type</code>, <code>status</code></td><td>The slug of a city, property type or status (<code>for-sale</code>, <code>for-rent</code>)</td></tr>
<tr><td><code>beds</code>, <code>baths</code>, <code>garages</code></td><td>At least this many</td></tr>
<tr><td><code>area_min</code>, <code>area_max</code>, <code>year_min</code>, <code>year_max</code></td><td>Floor area and year-built ranges</td></tr>
<tr><td><code>max_price</code>, <code>max_rent</code></td><td>Upper price or rent</td></tr>
<tr><td><code>sort</code></td><td>Order of the results</td></tr>
</table></div>
<p>To show a different number per page, add this to a child theme or a small plugin:</p>
<pre>add_filter( 'south_listings_per_page', function () {
	return 9;
} );</pre>
HTML
);

$sections .= south_docs_section( '07', 'pages', 'Home page and sections', <<<'HTML'
<p>Every section on South’s pages — the hero with the search box, Featured Properties, the rent call to action, testimonials, the agent band, the team, contact details and the journal — is a <strong>pattern</strong>. Open a page in the editor and click any text to change it. To add a section, open the block inserter, choose <strong>Patterns</strong> and look under the South categories.</p>
<p>Featured Properties shows the homes you ticked <strong>Featured</strong>, newest first. In the editor its preview shows the newest homes, because the “featured only” rule runs on the site rather than in the editor.</p>
<p>The header and footer are template parts: <strong>Appearance → Editor → Patterns → Template parts</strong>.</p>
HTML
);

$sections .= south_docs_section( '08', 'enquiries', 'Enquiries', <<<'HTML'
<p>The contact page and every property page carry an enquiry form. It validates on the server, is protected against spam by a nonce and a hidden field, and works with JavaScript off. Enquiries go to the site’s admin email address (Settings → General). An enquiry from a property page says which home it was about.</p>
<p>If your site cannot send email, install an SMTP plugin; South uses WordPress’s own mail function. To send enquiries somewhere else — a CRM, a spreadsheet, a webhook — add this to a child theme or a small plugin. Returning <code>true</code> tells South the enquiry is handled, and it sends no email of its own:</p>
<pre>add_filter( 'south_enquiry_handlers', function ( $handled, $enquiry ) {
	// $enquiry: name, phone, email, message and, from a property page, property (its ID).
	wp_remote_post( 'https://example.com/webhook', array( 'body' =&gt; $enquiry ) );
	return true;
}, 10, 2 );</pre>
<p>Prefer a form plugin? Contact Form 7, WPForms, Gravity Forms, Fluent Forms, Forminator and Ninja Forms are styled to match.</p>
HTML
);

$sections .= south_docs_section( '09', 'styles', 'Colours, fonts and dark mode', <<<'HTML'
<p>Go to <strong>Appearance → Editor → Styles</strong>. South ships eight colour palettes — South, Terracotta, Olive, Harbour, Sage, Charcoal, and the dark Midnight and Graphite — and five type pairings, based on Open Sans, Montserrat and Playfair Display. Every palette is checked for readable contrast before release.</p>
{{fig:palettes}}
<p>The moon icon in the header switches the site to dark mode for that visitor; it follows their system setting until they choose. To remove the switch:</p>
<pre>add_filter( 'south_enable_dark_mode', '__return_false' );</pre>
HTML
);

$sections .= south_docs_section( '10', 'maps', 'Maps', <<<'HTML'
<p>A property shows a map once it has a <strong>latitude</strong> and <strong>longitude</strong> in its details. The map is OpenStreetMap and loads only when the visitor clicks <strong>Show map</strong>, so no API key is needed and nothing is requested before the click. It shows the neighbourhood rather than the door: the note under it says the agent shares the exact address when a viewing is booked.</p>
HTML
);

$sections .= south_docs_section( '11', 'updates', 'Updates and privacy', <<<'HTML'
<p>South and South Listings are not in the WordPress.org directory, so they check updates.colorlib.com twice a day and updates appear in Dashboard → Updates like any other. The check sends the product and its version, the WordPress and PHP versions, the locale, whether the site is a multisite, and an identifier made from the site’s address with the site’s own secret key, which cannot be turned back into the address. To switch it off for both:</p>
<pre>add_filter( 'south_check_for_updates', '__return_false' );</pre>
HTML
);

$content = '[vc_row el_id="top" css=".vc_custom_southdoc00{padding-top:44px !important;padding-bottom:30px !important;background-color:#f5f7f9 !important;}"][vc_column width="1/1"]'
	. '[vcex_heading text="South documentation" tag="h2" font_size="40px" text_align="center" bottom_margin="16px" font_weight="700"]'
	. '[vc_column_text css=".vc_custom_southdoc00t{text-align:center !important;font-size:18px !important;max-width:760px !important;margin-left:auto !important;margin-right:auto !important;}"]'
	. $css
	. 'How to take a real estate site from install to listings, in the order you will need it. South is a free block theme for agents and agencies; South Listings adds the properties and the search.'
	. '[/vc_column_text]'
	. '[vc_column_text css=".vc_custom_southdoc00b{text-align:center !important;margin-top:22px !important;}"]'
	. '[vc_btn title="Download South" style="flat" color="green" link="url:' . rawurlencode( $download ) . '|title:Download%20South|target:_blank" css=".vc_custom_southdoc00c{' . $btn_css . '}" i_icon_fontawesome="fa fa-download" add_icon="true"]'
	. '[vc_btn title="South Listings plugin" style="flat" color="green" link="url:' . rawurlencode( $plugin ) . '|title:South%20Listings%20plugin|target:_blank" css=".vc_custom_southdoc00e{' . $btn_css . '}" i_icon_fontawesome="fa fa-plug" add_icon="true"]'
	. '[vc_btn title="Live demo" style="flat" color="grey" link="url:' . rawurlencode( $demo ) . '|title:Live%20demo|target:_blank" css=".vc_custom_southdoc00d{' . $btn_css . '}" i_icon_fontawesome="fa fa-eye" add_icon="true"]'
	. '[/vc_column_text]'
	. '[vc_column_text css=".vc_custom_southdoc00m{text-align:center !important;font-size:14px !important;color:#6b6b6b !important;margin-top:6px !important;}"]Written for South 1.0.0 and South Listings 1.0.1 on WordPress 7.1.[/vc_column_text]'
	. '[/vc_column][/vc_row]'
	. '[vc_row el_id="contents" el_class="south-docs" css=".vc_custom_southdoc0c{padding-top:34px !important;padding-bottom:4px !important;}"][vc_column width="1/1"]'
	. '[vcex_heading text="On this page" tag="h2" font_size="24px" text_align="center" bottom_margin="16px" font_weight="700"]'
	. '[vc_column_text]' . $toc . '[/vc_column_text]'
	. '[/vc_column][/vc_row]'
	. $sections
	. '[vc_row el_class="south-docs" css=".vc_custom_southdoc99{padding-top:34px !important;padding-bottom:56px !important;}"][vc_column width="1/1"]'
	. '[vcex_heading text="Still stuck?" tag="h2" font_size="28px" bottom_margin="14px" font_weight="700"]'
	. '[vc_column_text css=".vc_custom_southdoc99t{max-width:860px !important;}"]'
	. '<p>Ask on the <a href="' . esc_url( $support ) . '" target="_blank" rel="noopener">Colorlib support forum</a>. Include your WordPress and PHP versions, what you did, and what you expected to happen — a screenshot helps.</p>'
	. '[/vc_column_text][/vc_column][/vc_row]';

$problems = $GLOBALS['south_docs_missing'];
if ( $problems ) {
	echo 'NOT SAVED. Missing: ' . implode( ', ', $problems ) . "\n";
	return;
}

$kses = has_filter( 'content_save_pre', 'wp_filter_post_kses' );
if ( $kses ) {
	kses_remove_filters();
}

$args = array(
	'post_title'   => 'South documentation',
	'post_name'    => $slug,
	// wp_slash(): wp_insert_post() and wp_update_post() unslash.
	'post_content' => wp_slash( $content ),
	'post_status'  => $existing ? $existing->post_status : 'draft',
	'post_type'    => 'page',
	'post_parent'  => $parent,
);

if ( $page_id ) {
	$args['ID'] = $page_id;
	$result     = wp_update_post( $args, true );
} else {
	$result  = wp_insert_post( $args, true );
	$page_id = is_wp_error( $result ) ? 0 : $result;
}

if ( $kses ) {
	kses_init_filters();
}

if ( is_wp_error( $result ) ) {
	echo 'ERROR: ' . $result->get_error_message() . "\n";
	return;
}

update_post_meta( $page_id, '_wp_page_template', 'templates/no-sidebar.php' );
update_post_meta( $page_id, '_wpb_vc_js_status', 'true' );
update_post_meta( $page_id, '_yoast_wpseo_title', 'South Documentation – Real Estate WordPress Theme - %%sitename%%' );
update_post_meta( $page_id, '_yoast_wpseo_metadesc', 'How to install South and South Listings, import the demo, add properties and agents, link to searches, route enquiries and change colours.' );

if ( function_exists( 'visual_composer' ) ) {
	$vc = visual_composer();
	if ( method_exists( $vc, 'buildShortcodesCss' ) ) {
		$vc->buildShortcodesCss( $page_id, 'custom' );
		$vc->buildShortcodesCss( $page_id, 'default' );
		echo "custom css rebuilt\n";
	}
}

$saved    = get_post_field( 'post_content', $page_id );
$rendered = do_shortcode( $saved );

echo 'page: ' . $page_id . ' (' . get_post_status( $page_id ) . '), parent ' . wp_get_post_parent_id( $page_id ) . "\n";
echo 'saved intact: ' . ( $saved === $content ? 'yes' : 'NO' ) . "\n";
echo 'sections: ' . substr_count( $saved, '[vcex_heading' ) . ', figures: ' . substr_count( $saved, '<figure' ) . "\n";
echo 'unbalanced rows: ' . ( substr_count( $saved, '[vc_row' ) - substr_count( $saved, '[/vc_row]' ) ) . "\n";

preg_match_all( '#<a[^>]+href="([^"]*)"#', $rendered, $hrefs );
$dead = array_filter(
	array_unique( $hrefs[1] ),
	function ( $h ) {
		return '' === $h || '#' === $h || false !== strpos( $h, 'http://https' ) || 0 === strpos( $h, 'url:' );
	}
);
echo 'rendered links: ' . count( array_unique( $hrefs[1] ) ) . ', dead: ' . count( $dead ) . "\n";

preg_match_all( '/href="#([a-z0-9-]+)"/', $saved, $anchors );
preg_match_all( '/el_id="([a-z0-9-]+)"/', $saved, $ids );
$orphans = array_diff( array_unique( $anchors[1] ), $ids[1] );
echo 'anchor links: ' . count( array_unique( $anchors[1] ) ) . ', without a target: ' . ( $orphans ? implode( ', ', $orphans ) : 'none' ) . "\n";
