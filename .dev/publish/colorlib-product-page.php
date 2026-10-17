<?php
/**
 * Build the South product page on colorlib.com/wp, as a child of the themes
 * listing (5091).
 *
 * Same shape as the Unioncorp, Pato, Academia and Philosophy pages: what the
 * theme is, what it looks like, what you get, and the questions people ask.
 * South leads with what a real estate site needs and usually buys a plugin
 * for: property listings with real fields and a search that works.
 *
 * Every claim below was checked against this repository, not carried over
 * from Unioncorp's page. South is two downloads (the theme and the South
 * Listings plugin), its demo import creates no user accounts, and its palettes
 * and type pairings have different names.
 *
 * Idempotent: creates the page the first time, rewrites it after that, and
 * never demotes a published page. New pages start as drafts.
 *
 * Images are found by file name, not attachment ID, and the script refuses to
 * save while any is missing.
 *
 *   Copy to a UNIQUE path on the server (never a fixed /tmp name: a stale file
 *   from another theme once ran in its place), then from the WordPress root:
 *     sudo -u web_colorlib_com wp --url=https://colorlib.com/wp/ eval "require '/tmp/<unique>.php';"
 *
 * Use `wp eval "require …"`, not `wp eval-file`, which runs in a function scope.
 */

defined( 'ABSPATH' ) || exit;

$slug   = 'south';
$parent = 5091;

$download        = 'https://updates.colorlib.com/download/theme/south.zip';
$plugin_download = 'https://updates.colorlib.com/download/plugin/south-listings.zip';
$demo            = 'https://colorlibhub.com/south/';
$docs            = 'https://colorlib.com/wp/themes/south/documentation/';
$github          = 'https://github.com/ColorlibHQ/south-wp';
$html            = 'https://colorlib.com/wp/template/south/';

// ---------------------------------------------------------------------------
// Images, by file name
// ---------------------------------------------------------------------------

/*
 * colorlib.com's uploads have no year/month folder, so `_wp_attached_file` is
 * the bare file name and `LIKE '%/name'` alone never matches. A file over the
 * size threshold is stored as `name-scaled.jpg`, so accept that too.
 */
$find_image = static function ( $file ) {
	global $wpdb;
	$scaled = preg_replace( '/\.(jpe?g|png)$/i', '-scaled.$1', $file );
	return (int) $wpdb->get_var(
		$wpdb->prepare(
			"SELECT post_id FROM {$wpdb->postmeta}
			 WHERE meta_key = '_wp_attached_file'
			   AND ( meta_value = %s OR meta_value = %s OR meta_value LIKE %s OR meta_value LIKE %s )
			 ORDER BY post_id DESC LIMIT 1",
			$file,
			$scaled,
			'%/' . $wpdb->esc_like( $file ),
			'%/' . $wpdb->esc_like( $scaled )
		)
	);
};

$wanted = array(
	'card'     => 'south-free-real-estate-wordpress-theme.jpg',
	'home'     => 'south-block-theme-home.jpg',
	'listings' => 'south-block-theme-listings.jpg',
	'property' => 'south-block-theme-property.jpg',
	'palettes' => 'south-block-theme-palettes.jpg',
	'dark'     => 'south-block-theme-dark.jpg',
	'blog'     => 'south-block-theme-blog.jpg',
);

$img     = array();
$missing = array();
foreach ( $wanted as $key => $file ) {
	$img[ $key ] = $find_image( $file );
	if ( ! $img[ $key ] ) {
		$missing[] = $file;
	}
}

if ( $missing ) {
	echo "ERROR: not in the media library yet, refusing to build a page with gaps:\n  " . implode( "\n  ", $missing ) . "\n";
	return;
}

// vc_btn's `link` attribute is WPBakery's own "url:…|title:…|target:…" encoding.
// It splits on "|" then on the first ":", so a raw URL is cut off at "https:"
// and the button renders href="http://https". Percent-encode anything in a link.
$btn_css = 'display:inline-block !important;vertical-align:middle !important;margin-right:12px !important;margin-bottom:10px !important;';
$tint    = '#f5f7f9';
$accent  = '#86634a';

// Look up by slug AND parent: get_page_by_path( 'south' ) finds only a
// top-level page, and this one is a child of 5091.
$found = get_posts(
	array(
		'post_type'   => 'page',
		'name'        => $slug,
		'post_parent' => $parent,
		// An explicit list, not 'any': 'any' leaves drafts out, so an idempotent
		// script would keep re-creating its own draft.
		'post_status' => array( 'publish', 'draft', 'pending', 'private', 'future' ),
		'numberposts' => 1,
	)
);

$existing = $found ? $found[0] : null;
$page_id  = $existing ? $existing->ID : 0;

// ---------------------------------------------------------------------------
// Content
// ---------------------------------------------------------------------------

$features = array(
	array( 'home', 'Properties with real fields', 'Price, sale or rent, type, bedrooms, bathrooms, garages, floor area, year built, address, neighbourhood, features, rooms, a gallery and the listing agent — 22 fields per home, entered in a panel beside the editor.' ),
	array( 'search', 'A search that works', 'Filter by city, type, sale or rent, bedrooms, bathrooms, garages, floor area and year built, then sort and page through the results. It runs on the address bar, so it works with JavaScript off and a search can be bookmarked.' ),
	array( 'user', 'An agent on every listing', 'Each home names its agent, with a photo, phone, email and an enquiry form beside the description. Enquiries arrive by email, or go wherever one filter sends them.' ),
	array( 'paint-brush', 'Eight palettes, five type pairings', 'South’s own brown, Terracotta, Olive, Harbour, Sage, Charcoal and two dark palettes, each measured against WCAG AA before the theme is built.' ),
	array( 'moon-o', 'Dark mode', 'A switch for the visitor, separate from the palette you chose. It follows the reader’s system setting until they pick one themselves.' ),
	array( 'map-marker', 'Maps that load on request', 'Each property and the contact page carry an OpenStreetMap map that loads only when the visitor asks for it. No API key, and nothing is sent anywhere before the click.' ),
	array( 'th-large', 'Built from patterns', 'The hero with its search box, featured homes, the rent call to action, testimonials, the agent band, team, contact and the journal are all patterns you insert and edit like any other block.' ),
	array( 'plug', 'Your form plugin, styled', 'Contact Form 7, WPForms, Gravity Forms, Fluent Forms, Forminator and Ninja Forms take on the theme’s colours and spacing.' ),
	array( 'download', 'Demo content in one click', 'South Listings can import the template’s nine homes, three agents and three journal posts with their photographs, and remove them again. It creates no user accounts and changes nothing you made.' ),
);

// Three to a row, each row its own [vc_row]: WPBakery columns are floats, and
// nine thirds in one row snag on the tallest box above them.
$feature_rows = '';
foreach ( array_chunk( $features, 3 ) as $r => $row ) {
	$last          = ( $r === (int) ceil( count( $features ) / 3 ) - 1 );
	$feature_rows .= '[vc_row css=".vc_custom_so05' . ( $r + 1 ) . '{padding-bottom:' . ( $last ? '40' : '0' ) . 'px !important;}"]';
	foreach ( $row as $f ) {
		list( $icon, $heading, $body ) = $f;
		$feature_rows .= '[vc_column width="1/3"][vcex_icon_box style="two" heading="' . esc_attr( $heading ) . '" heading_type="h3"'
			. ' icon="fa fa-' . $icon . '" icon_color="' . $accent . '" icon_size="28px" heading_size="20px"'
			. ' content_font_size="15px" css=".vc_custom_so_f_' . sanitize_key( $icon ) . '{margin-bottom:26px !important;}"]'
			. $body . '[/vcex_icon_box][/vc_column]';
	}
	$feature_rows .= '[/vc_row]';
}

$faqs = array(
	array( 'Do I need the South Listings plugin?', 'Only for property listings. The theme on its own is a complete agency site — pages, journal, enquiry form, dark mode — and its property templates simply stay empty. Install the plugin for the Property post type, its fields, the search and the demo import. Keeping listings in a plugin means they survive if you ever change theme.' ),
	array( 'How do I add a property?', 'Properties → Add New. Write the description in the editor, set a featured image, and fill in price, rooms, area, address and the rest in the Property details panel. The card, the property page and the search all read from those fields.' ),
	array( 'Can visitors search without JavaScript?', 'Yes. The search form submits ordinary address parameters to the Properties archive and WordPress filters the listings on the server. With JavaScript, the same search updates in place without a page reload.' ),
	array( 'Where do the enquiries go?', 'To the site’s admin email address by default. The <code>south_enquiry_handlers</code> filter hands each enquiry to a CRM, a webhook or a form plugin instead, and South then sends nothing of its own.' ),
	array( 'What does the demo import add?', 'The template’s nine homes (six featured), three agents and three journal posts, with their photographs, plus Properties and Find a Home in the menu. Running it twice adds nothing new, and Remove demo content deletes exactly what it added.' ),
	array( 'Can I turn dark mode off?', 'Yes. Add <code>add_filter( \'south_enable_dark_mode\', \'__return_false\' );</code> to a child theme or a small plugin.' ),
	array( 'Is it translation ready?', 'Yes. Every string is translatable; <code>languages/south.pot</code> and the plugin’s <code>south-listings.pot</code> are included.' ),
	array( 'Does it check for updates?', 'Yes, twice a day, because the theme and the plugin are distributed outside the WordPress.org directory. The check sends the product, WordPress and PHP versions, the locale, whether the site is a multisite, and an identifier derived from the site’s address with the site’s own secret key, so it cannot be turned back into the address. The <code>south_check_for_updates</code> filter switches it off for both.' ),
	array( 'Is there an HTML version?', 'Yes. South began as an HTML template, which is still available on its own <a href="' . esc_url( $html ) . '">template page</a>. This WordPress edition is a separate, free theme.' ),
);

$toggles = '';
foreach ( $faqs as $faq ) {
	// vcex_toggle takes `heading`: with `title` every toggle renders the
	// shortcode's placeholder text.
	$toggles .= '[vcex_toggle heading="' . esc_attr( $faq[0] ) . '" heading_type="h3" heading_font_size="17px" style="boxed" padding_y="16px" padding_x="20px" bottom_margin="12px"]'
		. $faq[1] . '[/vcex_toggle]';
}

$specs = array(
	'Requires'      => 'WordPress 6.6 or newer',
	'PHP'           => '7.4 or newer',
	'Tested up to'  => 'WordPress 7.1',
	'Licence'       => 'GNU General Public License v2 or later',
	'Downloads'     => 'The South theme, and the South Listings plugin for properties',
	'Patterns'      => '19 to insert, plus 14 the templates use',
	'Templates'     => '15, including the property archive, a single property and three property taxonomies, plus 3 template parts',
	'Styles'        => '8 colour palettes × 5 type pairings, and dark mode',
	'Fonts'         => 'Open Sans, Montserrat and Playfair Display, self-hosted',
	'Form plugins'  => 'Contact Form 7, WPForms, Gravity Forms, Fluent Forms, Forminator, Ninja Forms',
	'Source'        => '<a href="' . esc_url( $github ) . '">github.com/ColorlibHQ/south-wp</a>',
);

$spec_rows = '';
foreach ( $specs as $label => $value ) {
	$spec_rows .= '<tr><th style="text-align:left;padding:10px 18px 10px 0;border-bottom:1px solid #e1dddd;font-weight:600;white-space:nowrap;vertical-align:top;">'
		. esc_html( $label ) . '</th><td style="padding:10px 0;border-bottom:1px solid #e1dddd;">' . $value . '</td></tr>';
}

$buttons = '[vc_btn title="Download South" style="flat" color="green" link="url:{DL}|title:Download%20South|target:_blank" css=".vc_custom_so{N}a{{CSS}}" i_icon_fontawesome="fa fa-download" add_icon="true"]'
	. '[vc_btn title="South Listings plugin" style="flat" color="green" link="url:{PL}|title:South%20Listings%20plugin|target:_blank" css=".vc_custom_so{N}d{{CSS}}" i_icon_fontawesome="fa fa-plug" add_icon="true"]'
	. '[vc_btn title="Live demo" style="flat" color="grey" link="url:{DEMO}|title:Live%20demo|target:_blank" css=".vc_custom_so{N}b{{CSS}}" i_icon_fontawesome="fa fa-eye" add_icon="true"]'
	. '[vc_btn title="Documentation" style="flat" color="grey" link="url:{DOCS}|title:Documentation" css=".vc_custom_so{N}c{{CSS}}" i_icon_fontawesome="fa fa-book" add_icon="true"]';
$buttons = str_replace(
	array( '{DL}', '{PL}', '{DEMO}', '{DOCS}', '{CSS}' ),
	array( rawurlencode( $download ), rawurlencode( $plugin_download ), rawurlencode( $demo ), rawurlencode( $docs ), $btn_css ),
	$buttons
);
$top_buttons    = str_replace( '{N}', '004', $buttons );
$bottom_buttons = str_replace( '{N}', '083', $buttons );

$section = static function ( $n, $bg, $heading, $text, $image ) use ( $tint ) {
	$background = $bg ? "background-color:{$tint} !important;" : '';
	return "[vc_row css=\".vc_custom_so{$n}0{padding-top:56px !important;padding-bottom:20px !important;{$background}}\"][vc_column width=\"1/1\"]"
		. '[vcex_heading text="' . esc_attr( $heading ) . '" tag="h2" font_size="34px" text_align="center" bottom_margin="14px" font_weight="700"]'
		. "[vc_column_text css=\".vc_custom_so{$n}1{text-align:center !important;max-width:790px !important;margin-left:auto !important;margin-right:auto !important;}\"]{$text}[/vc_column_text]"
		. "[/vc_column][/vc_row][vc_row css=\".vc_custom_so{$n}2{padding-bottom:56px !important;{$background}}\"][vc_column width=\"1/1\"]"
		. "[vcex_image image_id=\"{$image}\" align=\"center\" border_radius=\"12px\" bottom_margin=\"0px\"][/vc_column][/vc_row]";
};

$content = "[vc_row css=\".vc_custom_so001{padding-top:64px !important;padding-bottom:40px !important;background-color:{$tint} !important;}\"][vc_column width=\"1/1\"]"
	. '[vcex_heading text="A real estate theme with listings that actually work" tag="h2" font_size="46px" text_align="center" bottom_margin="20px" font_weight="700"]'
	. '[vc_column_text css=".vc_custom_so002{text-align:center !important;font-size:18px !important;max-width:820px !important;margin-left:auto !important;margin-right:auto !important;}"]'
	. 'South is a free block theme for real estate agents and agencies. Its companion plugin, South Listings, adds properties with real fields — price, rooms, area, address, gallery and agent — and a search visitors can filter, sort and page through, with or without JavaScript. The theme brings the rest: a hero with the search box, featured homes, testimonials, an agent band, a journal, an enquiry form, eight colour palettes and dark mode.'
	. '[/vc_column_text][vc_column_text css=".vc_custom_so003{text-align:center !important;margin-top:26px !important;}"]' . $top_buttons . '[/vc_column_text]'
	. "[/vc_column][/vc_row][vc_row css=\".vc_custom_so006{padding-top:0px !important;padding-bottom:64px !important;background-color:{$tint} !important;}\"][vc_column width=\"1/1\"]"
	. "[vcex_image image_id=\"{$img['home']}\" align=\"center\" border_radius=\"14px\" bottom_margin=\"0px\"][/vc_column][/vc_row]\n\n"

	. $section( '01', false, 'Listings with real fields, and a search that works',
		'Every home is a Property with its own fields, so the price badge, the facts row and the search all read the same data. Visitors filter by city, type, sale or rent, rooms, garages, floor area and year built, then sort and page through the results — and because the search lives in the address bar, it works without JavaScript and can be shared as a link.',
		$img['listings'] ) . "\n\n"

	. $section( '02', true, 'A page for every home',
		'Price and address up top, the description, a table of facts, the features, a rooms-and-sizes panel, a mortgage estimate and a map that loads on request. Beside it, the listing agent with a photo, phone, email and an enquiry form.',
		$img['property'] ) . "\n\n"

	. $section( '03', false, 'Eight palettes, checked before release',
		'South’s own brown, Terracotta, Olive, Harbour, Sage and Charcoal, and two dark palettes, Midnight and Graphite. Every one is measured against WCAG AA before the theme is built — text, links, button labels and button edges, in light mode and in dark — and a palette that fails is not written.',
		$img['palettes'] ) . "\n\n"

	. $section( '04', true, 'Dark mode the visitor controls',
		'The switch in the header is the visitor’s, separate from the palette you chose. It follows the reader’s system setting until they decide for themselves.',
		$img['dark'] ) . "\n\n"

	. "[vc_row css=\".vc_custom_so050{padding-top:56px !important;padding-bottom:16px !important;}\"][vc_column width=\"1/1\"]"
	. '[vcex_heading text="What you get" tag="h2" font_size="34px" text_align="center" bottom_margin="34px" font_weight="700"][/vc_column][/vc_row]'
	. $feature_rows . "\n\n"

	. $section( '06', true, 'A journal, too',
		'Market notes, neighbourhood guides, news from the agency. Archives, single posts, categories, author pages, search and a 404 are all designed, with a sidebar that can feature a home.',
		$img['blog'] ) . "\n\n"

	. "[vc_row css=\".vc_custom_so070{padding-top:56px !important;padding-bottom:18px !important;}\"][vc_column width=\"1/1\"]"
	. '[vcex_heading text="Questions" tag="h2" font_size="34px" text_align="center" bottom_margin="28px" font_weight="700"][/vc_column][/vc_row]'
	. "[vc_row css=\".vc_custom_so071{padding-bottom:48px !important;}\"][vc_column width=\"1/1\"]{$toggles}[/vc_column][/vc_row]\n\n"

	. "[vc_row css=\".vc_custom_so080{padding-top:48px !important;padding-bottom:56px !important;background-color:{$tint} !important;}\"][vc_column width=\"1/1\"]"
	. '[vcex_heading text="The details" tag="h2" font_size="34px" text_align="center" bottom_margin="26px" font_weight="700"]'
	. '[vc_column_text css=".vc_custom_so081{max-width:680px !important;margin-left:auto !important;margin-right:auto !important;}"]<table style="width:100%;border-collapse:collapse;">' . $spec_rows . '</table>[/vc_column_text]'
	. '[vc_column_text css=".vc_custom_so082{text-align:center !important;margin-top:34px !important;}"]' . $bottom_buttons . '[/vc_column_text]'
	. '[/vc_column][/vc_row]';

// ---------------------------------------------------------------------------
// Save
// ---------------------------------------------------------------------------

// kses strips the shortcode attributes this page is made of.
$kses = has_filter( 'content_save_pre', 'wp_filter_post_kses' );
if ( $kses ) {
	kses_remove_filters();
}

$args = array(
	'post_title'   => 'South',
	'post_name'    => $slug,
	'post_content' => $content,
	// Draft on creation, but never demote a page that is already published.
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

set_post_thumbnail( $page_id, $img['card'] );

update_post_meta( $page_id, '_wp_page_template', 'templates/no-sidebar.php' );
update_post_meta( $page_id, '_wpb_vc_js_status', 'true' );
update_post_meta( $page_id, '_yoast_wpseo_title', 'South – Free Real Estate WordPress Theme - %%sitename%%' );
update_post_meta( $page_id, '_yoast_wpseo_metadesc', 'A free WordPress block theme for real estate agencies, with a listings plugin, a property search that works without JavaScript, eight colour palettes and dark mode.' );

// WPBakery keeps every css="…" rule in _wpb_shortcodes_custom_css and only
// regenerates it when the page is saved through the builder UI.
if ( function_exists( 'visual_composer' ) && method_exists( visual_composer(), 'buildShortcodesCss' ) ) {
	visual_composer()->buildShortcodesCss( $page_id, 'custom' );
	visual_composer()->buildShortcodesCss( $page_id, 'default' );
	echo "custom css rebuilt\n";
} else {
	echo "WARNING: could not rebuild the WPBakery custom css\n";
}

$saved = get_post_field( 'post_content', $page_id );

echo 'page: ' . $page_id . ' (' . get_post_status( $page_id ) . '), parent ' . wp_get_post_parent_id( $page_id ) . "\n";
echo 'images: ' . wp_json_encode( $img ) . "\n";
echo 'icon boxes: ' . substr_count( $saved, '[vcex_icon_box' ) . "\n";
echo 'toggles: ' . substr_count( $saved, '[vcex_toggle' ) . ' (with heading=: ' . substr_count( $saved, '[vcex_toggle heading=' ) . ")\n";
echo 'section images: ' . substr_count( $saved, '[vcex_image' ) . "\n";
echo 'buttons: ' . substr_count( $saved, '[vc_btn' ) . "\n";
echo 'unbalanced rows: ' . ( substr_count( $saved, '[vc_row' ) - substr_count( $saved, '[/vc_row]' ) ) . "\n";

// Assert on the rendered anchors, not the shortcode text: a raw URL in a
// vc_btn link renders href="http://https" while the source looks right.
$rendered = do_shortcode( $saved );
preg_match_all( '/<a[^>]+href="([^"]+)"/', $rendered, $m );
$dead = array_filter( $m[1], static function ( $h ) { return 0 === strpos( $h, 'http://https' ) || '#' === $h; } );
echo 'rendered links: ' . count( $m[1] ) . ', dead: ' . count( $dead ) . "\n";
