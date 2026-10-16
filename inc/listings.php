<?php
/**
 * Where the theme meets the South Listings plugin.
 *
 * A theme must not register a post type, so homes, agents and the search all
 * belong to the companion plugin. The theme owns only the ENTRY POINTS its
 * templates and patterns name, and the plugin plugs into them through one
 * filter:
 *
 *   [south_property_search]          the search box (the frame is the theme's)
 *   [south_property_toolbar]         the results line, view and order controls
 *   [south_property part="..."]      gallery, details, agent, map of one home
 *
 *   apply_filters( 'south_listings_render', null, $tag, $atts )
 *
 * The plugin returns HTML; null means "nobody answered" and the theme falls
 * back: the search box searches the site, everything else renders nothing.
 *
 * Why a filter and not a shared function name: Academia declared the same
 * function in its theme and its plugin, relying on plugins loading first. That
 * is true for an ordinary request and false while a plugin is being ACTIVATED
 * (the theme is already loaded), so activating the plugin died with "Cannot
 * redeclare". Nothing here is declared by both.
 *
 * Sections built from the plugin's homes carry `south-requires-listings` and
 * are removed when the `property` type does not exist, so the theme looks
 * complete without the plugin rather than showing empty grids.
 *
 * @package South
 */

defined( 'ABSPATH' ) || exit;

/**
 * Whether the plugin's homes exist on this site.
 *
 * @return bool
 */
function south_has_listings() {
	return post_type_exists( 'property' );
}

/**
 * Register the entry points.
 */
function south_register_listing_shortcodes() {
	foreach ( array( 'south_property_search', 'south_property_toolbar', 'south_property' ) as $tag ) {
		add_shortcode( $tag, 'south_listing_shortcode' );
	}
}
add_action( 'init', 'south_register_listing_shortcodes' );

/**
 * Render an entry point: the plugin's answer, or the theme's fallback.
 *
 * @param array|string $atts    Shortcode attributes.
 * @param string|null  $content Enclosed content (unused).
 * @param string       $tag     Which shortcode.
 * @return string
 */
function south_listing_shortcode( $atts, $content = null, $tag = '' ) {
	$atts = is_array( $atts ) ? $atts : array();

	/**
	 * Filters the HTML of one of the theme's listing entry points.
	 *
	 * South Listings answers every one of them. Return null to leave the
	 * theme's fallback in place.
	 *
	 * @param string|null $html Rendered HTML, or null.
	 * @param string      $tag  `south_property_search`, `south_property_toolbar` or `south_property`.
	 * @param array       $atts Shortcode attributes.
	 */
	$html = apply_filters( 'south_listings_render', null, $tag, $atts );

	if ( 'south_property_search' === $tag ) {
		return south_search_frame( null === $html ? south_site_search_form() : $html );
	}

	return null === $html ? '' : $html;
}

/**
 * The search box's frame: the white panel and its brown tab.
 *
 * @param string $form The form inside it.
 * @return string
 */
function south_search_frame( $form ) {
	$out  = '<div class="south-search">';
	$out .= '<div class="south-search__box">';
	$out .= '<p class="south-search__title">' . esc_html__( 'Search for your home', 'south' ) . '</p>';
	$out .= $form;
	return $out . '</div></div>';
}

/**
 * Without the plugin: a keyword search of the whole site, in the same box.
 *
 * @return string
 */
function south_site_search_form() {
	$out  = '<form class="south-search__form south-search__form--site" role="search" method="get" action="' . esc_url( home_url( '/' ) ) . '">';
	$out .= '<label class="screen-reader-text" for="south-site-search">' . esc_html__( 'Keyword', 'south' ) . '</label>';
	$out .= '<input type="search" id="south-site-search" class="south-search__input" name="s" placeholder="' . esc_attr__( 'Keyword', 'south' ) . '" value="' . esc_attr( get_search_query() ) . '">';
	$out .= '<button type="submit" class="wp-element-button south-search__submit">' . esc_html__( 'Search', 'south' ) . '</button>';
	return $out . '</form>';
}

/**
 * Render core/shortcode blocks with their shortcodes expanded, everywhere.
 *
 * The block's own render callback only runs wpautop(); expansion normally
 * comes from do_shortcode on `the_content`, which never runs for a block
 * TEMPLATE, so without this the listings template prints its shortcodes as
 * text. And wpautop() wraps the result in <p>, which nests the search box,
 * the agent box and every form inside a paragraph: invalid HTML that the
 * browser repairs into empty paragraphs with margins. So the block's raw
 * inner HTML is expanded instead of its autop'd output.
 *
 * @param string $content Rendered block.
 * @param array  $block   Parsed block.
 * @return string
 */
function south_render_shortcode_block( $content, $block = array() ) {
	if ( isset( $block['innerHTML'] ) && '' !== trim( $block['innerHTML'] ) ) {
		return do_shortcode( trim( $block['innerHTML'] ) );
	}
	return do_shortcode( $content );
}
add_filter( 'render_block_core/shortcode', 'south_render_shortcode_block', 10, 2 );

/**
 * Drop listing sections when the plugin is not there, and their stand-ins
 * when it is.
 *
 * @param string $content Rendered block.
 * @param array  $block   Parsed block.
 * @return string
 */
function south_listing_dependent_blocks( $content, $block ) {
	$class = isset( $block['attrs']['className'] ) ? (string) $block['attrs']['className'] : '';
	if ( '' === $class || false === strpos( $class, 'listings' ) ) {
		return $content;
	}

	$classes = preg_split( '/\s+/', $class );
	if ( in_array( 'south-requires-listings', $classes, true ) && ! south_has_listings() ) {
		return '';
	}
	if ( in_array( 'south-without-listings', $classes, true ) && south_has_listings() ) {
		return '';
	}

	return $content;
}
add_filter( 'render_block', 'south_listing_dependent_blocks', 10, 2 );

/**
 * front-page.html is for a static front page. When the front page shows the
 * latest posts, take it out of the hierarchy so home.html renders instead of
 * a page template with no page in it.
 *
 * @param string[] $templates Candidate templates.
 * @return string[]
 */
function south_front_page_hierarchy( $templates ) {
	return 'page' === get_option( 'show_on_front' ) ? $templates : array();
}
add_filter( 'frontpage_template_hierarchy', 'south_front_page_hierarchy' );

/**
 * Body classes the stylesheet keys on.
 *
 * @param string[] $classes Body classes.
 * @return string[]
 */
function south_body_classes( $classes ) {
	if ( south_has_listings() ) {
		$classes[] = 'south-has-listings';
	}
	return $classes;
}
add_filter( 'body_class', 'south_body_classes' );

/**
 * Let the plugin's agent box use the theme's form without depending on it.
 *
 * @param string|null $html Form HTML, or null.
 * @param array       $args `property` (post ID) and `button` label.
 * @return string|null
 */
function south_provide_enquiry_form( $html, $args ) {
	if ( null !== $html ) {
		return $html;
	}
	return south_enquiry_form(
		array(
			'property' => isset( $args['property'] ) ? (int) $args['property'] : 0,
			'button'   => isset( $args['button'] ) ? $args['button'] : __( 'Send Message', 'south' ),
		)
	);
}
add_filter( 'south_listings_enquiry_form', 'south_provide_enquiry_form', 10, 2 );
