<?php
/**
 * The HTML: the search form, the results toolbar and the parts of a single
 * home (gallery, details, agent, map).
 *
 * Under the South theme this answers the theme's entry points through the
 * `south_listings_render` filter; the theme owns the shortcodes, and nothing
 * here is declared by the theme too (see inc/listings.php in the theme for
 * why that matters). Under any other theme the plugin registers the same
 * shortcodes itself and adds a home's details to its content.
 *
 * @package South_Listings
 */

defined( 'ABSPATH' ) || exit;

/**
 * Answer the theme's entry points.
 *
 * @param string|null $html Rendered HTML so far.
 * @param string      $tag  Shortcode tag.
 * @param array       $atts Attributes.
 * @return string|null
 */
function south_listings_render( $html, $tag, $atts ) {
	if ( null !== $html ) {
		return $html;
	}
	switch ( $tag ) {
		case 'south_property_search':
			return south_listings_search_form();
		case 'south_property_toolbar':
			return south_listings_toolbar();
		case 'south_property':
			return south_listings_property_part( isset( $atts['part'] ) ? (string) $atts['part'] : '' );
	}
	return $html;
}
add_filter( 'south_listings_render', 'south_listings_render', 10, 3 );

/**
 * Without the South theme, register the shortcodes here (after the theme
 * has had its chance, so a theme that owns them keeps them).
 */
function south_listings_own_shortcodes() {
	if ( south_listings_theme_active() ) {
		return;
	}
	$map = array(
		'south_property_search'  => static function () {
			return '<div class="south-search"><div class="south-search__box">' . south_listings_search_form() . '</div></div>';
		},
		'south_property_toolbar' => 'south_listings_toolbar',
		'south_property'         => static function ( $atts ) {
			return south_listings_property_part( isset( $atts['part'] ) ? (string) $atts['part'] : '' );
		},
	);
	foreach ( $map as $tag => $callback ) {
		if ( ! shortcode_exists( $tag ) ) {
			add_shortcode( $tag, $callback );
		}
	}
}
add_action( 'init', 'south_listings_own_shortcodes', 20 );

/**
 * Without the South theme, a single home shows its details after its text.
 *
 * @param string $content Post content.
 * @return string
 */
function south_listings_content_details( $content ) {
	if ( south_listings_theme_active() || ! is_singular( 'property' ) || ! in_the_loop() || ! is_main_query() ) {
		return $content;
	}
	return south_listings_property_part( 'gallery' ) . $content . south_listings_property_part( 'details' ) . south_listings_property_part( 'agent' ) . south_listings_property_part( 'map' );
}
add_filter( 'the_content', 'south_listings_content_details', 20 );

/**
 * The script and, under other themes, a small base stylesheet.
 */
function south_listings_assets() {
	wp_enqueue_script(
		'south-listings',
		south_listings_url( 'assets/listings.js' ),
		array(),
		SOUTH_LISTINGS_VERSION,
		array(
			'in_footer' => true,
			'strategy'  => 'defer',
		)
	);
	wp_add_inline_script(
		'south-listings',
		'window.southListings = ' . wp_json_encode(
			array(
				'more'    => __( '+ More filters', 'south-listings' ),
				'fewer'   => __( '- Fewer filters', 'south-listings' ),
				'sqft'    => __( '%s sq ft', 'south-listings' ),
				'built'   => __( 'Built', 'south-listings' ),
				'monthly' => __( 'Estimated monthly payment:', 'south-listings' ),
			)
		) . ';',
		'before'
	);
	if ( ! south_listings_theme_active() ) {
		wp_enqueue_style( 'south-listings-base', south_listings_url( 'assets/listings-base.css' ), array(), SOUTH_LISTINGS_VERSION );
	}
}

/* -------------------------------------------------------------------------
 * The search form
 * ---------------------------------------------------------------------- */

/**
 * A <select> as one field of the form.
 *
 * @param string $name    Parameter.
 * @param string $label   Visually hidden label.
 * @param array  $options value => text; the '' option is "any".
 * @param string $current Current value.
 * @param string $class   Extra class on the wrapper.
 * @return string
 */
function south_listings_select( $name, $label, $options, $current, $class = '' ) {
	$id  = 'south-search-' . $name;
	$out = '<p class="south-search__field' . ( $class ? ' ' . esc_attr( $class ) : '' ) . '">';
	$out .= '<label class="screen-reader-text" for="' . esc_attr( $id ) . '">' . esc_html( $label ) . '</label>';
	$out .= '<select id="' . esc_attr( $id ) . '" name="' . esc_attr( $name ) . '">';
	foreach ( $options as $value => $text ) {
		$out .= '<option value="' . esc_attr( $value ) . '"' . selected( (string) $current, (string) $value, false ) . '>' . esc_html( $text ) . '</option>';
	}
	return $out . '</select></p>';
}

/**
 * Terms of a taxonomy as select options.
 *
 * @param string $taxonomy Taxonomy.
 * @param string $any      The "any" label.
 * @return array<string, string>
 */
function south_listings_term_options( $taxonomy, $any ) {
	$options = array( '' => $any );
	$terms   = get_terms(
		array(
			'taxonomy'   => $taxonomy,
			'hide_empty' => false,
			'orderby'    => 'name',
		)
	);
	if ( ! is_wp_error( $terms ) ) {
		foreach ( $terms as $term ) {
			$options[ $term->slug ] = $term->name;
		}
	}
	return $options;
}

/**
 * The money options, written as the design writes prices.
 *
 * @param int[]  $amounts Amounts.
 * @param string $any     The "any" label.
 * @param bool   $rent    Whether these are monthly rents.
 * @return array<string, string>
 */
function south_listings_money_options( $amounts, $any, $rent ) {
	$options = array( '' => $any );
	foreach ( $amounts as $amount ) {
		$money = '$' . number_format( $amount, 0, '.', ' ' );
		/* translators: %s: an amount of money, e.g. $1 000 000. */
		$options[ (string) $amount ] = $rent ? sprintf( __( 'Up to %s /mo', 'south-listings' ), $money ) : sprintf( __( 'Up to %s', 'south-listings' ), $money );
	}
	return $options;
}

/**
 * One dual range slider.
 *
 * @param string $name  area|year.
 * @param array  $state Search state.
 * @return string
 */
function south_listings_range( $name, $state ) {
	$ranges                   = south_listings_ranges();
	list( $min, $max, $step ) = $ranges[ $name ];
	$low  = $state[ $name . '_min' ] ? $state[ $name . '_min' ] : $min;
	$high = $state[ $name . '_max' ] ? $state[ $name . '_max' ] : $max;

	if ( 'area' === $name ) {
		$labels = array( __( 'Minimum floor area', 'south-listings' ), __( 'Maximum floor area', 'south-listings' ) );
	} else {
		$labels = array( __( 'Built in or after', 'south-listings' ), __( 'Built in or before', 'south-listings' ) );
	}

	if ( 'area' === $name ) {
		/* translators: %s: floor area. */
		$text = sprintf( __( '%s sq ft', 'south-listings' ), number_format_i18n( $low ) ) . ' - ' . sprintf( __( '%s sq ft', 'south-listings' ), number_format_i18n( $high ) ); // phpcs:ignore WordPress.WP.I18n.MissingTranslatorsComment
		$prefix = '';
	} else {
		$text   = $low . ' - ' . $high;
		$prefix = __( 'Built', 'south-listings' ) . ' ';
	}

	$out  = '<div class="south-range" data-south-range="' . esc_attr( $name ) . '">';
	$out .= '<div class="south-range__track">';
	$out .= '<input type="range" name="' . esc_attr( $name ) . '_min" min="' . esc_attr( $min ) . '" max="' . esc_attr( $max ) . '" step="' . esc_attr( $step ) . '" value="' . esc_attr( $low ) . '" aria-label="' . esc_attr( $labels[0] ) . '">';
	$out .= '<input type="range" name="' . esc_attr( $name ) . '_max" min="' . esc_attr( $min ) . '" max="' . esc_attr( $max ) . '" step="' . esc_attr( $step ) . '" value="' . esc_attr( $high ) . '" aria-label="' . esc_attr( $labels[1] ) . '">';
	$out .= '</div>';
	$out .= '<p class="south-range__value">' . esc_html( $prefix ) . '<output>' . esc_html( $text ) . '</output></p>';
	return $out . '</div>';
}

/**
 * The search form: a GET form to the listings with the template's fields.
 *
 * @return string
 */
function south_listings_search_form() {
	south_listings_assets();
	$state  = south_listings_state();
	$action = get_post_type_archive_link( 'property' );

	$more_keys = array( 'max_price', 'max_rent', 'outdoor', 'view', 'furnished', 'pets', 'sort' );
	$more_open = false;
	foreach ( $more_keys as $key ) {
		$more_open = $more_open || ! empty( $state[ $key ] );
	}

	$out  = '<form class="south-search__form south-search__form--listings" method="get" action="' . esc_url( $action ) . '" data-south-search>';
	$out .= '<div class="south-search__grid">';
	$out .= '<p class="south-search__field"><label class="screen-reader-text" for="south-search-q">' . esc_html__( 'Keyword', 'south-listings' ) . '</label>';
	$out .= '<input type="search" id="south-search-q" class="south-search__input" name="q" placeholder="' . esc_attr__( 'Keyword', 'south-listings' ) . '" value="' . esc_attr( $state['q'] ) . '"></p>';
	$out .= south_listings_select( 'city', __( 'City', 'south-listings' ), south_listings_term_options( 'property_city', __( 'All Cities', 'south-listings' ) ), $state['city'] );
	$out .= south_listings_select( 'type', __( 'Property type', 'south-listings' ), south_listings_term_options( 'property_type', __( 'All Types', 'south-listings' ) ), $state['type'] );
	$out .= south_listings_select( 'status', __( 'Sale or rent', 'south-listings' ), south_listings_term_options( 'property_status', __( 'For Sale or Rent', 'south-listings' ) ), $state['status'] );
	$out .= south_listings_select(
		'garages',
		__( 'Garage spaces', 'south-listings' ),
		array(
			''  => __( 'Any Garage', 'south-listings' ),
			'1' => __( '1+ Garage spaces', 'south-listings' ),
			'2' => __( '2+ Garage spaces', 'south-listings' ),
		),
		$state['garages'] ? $state['garages'] : ''
	);
	$counts = array( '' => __( 'Bedrooms', 'south-listings' ) );
	for ( $i = 1; $i <= 5; $i++ ) {
		$counts[ (string) $i ] = $i . '+';
	}
	$out   .= south_listings_select( 'beds', __( 'Bedrooms', 'south-listings' ), $counts, $state['beds'] ? $state['beds'] : '', 'south-search__field--narrow' );
	$counts = array( '' => __( 'Bathrooms', 'south-listings' ) );
	for ( $i = 1; $i <= 4; $i++ ) {
		$counts[ (string) $i ] = $i . '+';
	}
	$out .= south_listings_select( 'baths', __( 'Bathrooms', 'south-listings' ), $counts, $state['baths'] ? $state['baths'] : '', 'south-search__field--narrow' );
	$out .= '<div class="south-search__ranges">' . south_listings_range( 'area', $state ) . south_listings_range( 'year', $state ) . '</div>';
	$out .= '</div>';

	// Visible in the markup, so every filter works without JavaScript; the
	// script folds it away behind "+ More filters" unless one is in use.
	$out .= '<div class="south-search__more" id="south-search-more" data-south-more' . ( $more_open ? ' data-open="1"' : '' ) . '>';
	$out .= '<div class="south-search__grid">';
	$out .= south_listings_select( 'max_price', __( 'Maximum price', 'south-listings' ), south_listings_money_options( array( 750000, 1000000, 1500000, 2500000 ), __( 'Any Price', 'south-listings' ), false ), $state['max_price'] ? $state['max_price'] : '' );
	$out .= south_listings_select( 'max_rent', __( 'Maximum monthly rent', 'south-listings' ), south_listings_money_options( array( 2500, 3500, 5000 ), __( 'Any Monthly Rent', 'south-listings' ), true ), $state['max_rent'] ? $state['max_rent'] : '' );
	$out .= south_listings_select( 'outdoor', __( 'Outdoor space', 'south-listings' ), array( '' => __( 'Any Outdoor Space', 'south-listings' ) ) + south_listings_outdoor_choices(), $state['outdoor'] );
	$views = south_listings_views();
	unset( $views[''] );
	$out .= south_listings_select( 'view', __( 'View', 'south-listings' ), array( '' => __( 'Any View', 'south-listings' ) ) + $views, $state['view'] );
	$out .= south_listings_select(
		'furnished',
		__( 'Furnishing', 'south-listings' ),
		array(
			''    => __( 'Furnished or Not', 'south-listings' ),
			'yes' => __( 'Furnished', 'south-listings' ),
			'no'  => __( 'Unfurnished', 'south-listings' ),
		),
		$state['furnished']
	);
	$out  .= south_listings_select(
		'pets',
		__( 'Pets', 'south-listings' ),
		array(
			''    => __( 'Pets: No Preference', 'south-listings' ),
			'yes' => __( 'Pets Allowed', 'south-listings' ),
		),
		$state['pets']
	);
	$sorts = south_listings_sorts();
	/* translators: %s: sort order name, e.g. Default. */
	$sorts[''] = sprintf( __( 'Sort: %s', 'south-listings' ), $sorts[''] );
	$out      .= south_listings_select( 'sort', __( 'Sort results', 'south-listings' ), $sorts, $state['sort'] );
	$out      .= '</div></div>';

	$out .= '<div class="south-search__actions">';
	$out .= '<button type="button" class="south-search__toggle" aria-controls="south-search-more" aria-expanded="true" hidden>' . esc_html__( '- Fewer filters', 'south-listings' ) . '</button>';
	$out .= '<button type="submit" class="wp-element-button south-search__submit">' . esc_html__( 'Search', 'south-listings' ) . '</button>';
	$out .= '</div>';

	return $out . '</form>';
}

/* -------------------------------------------------------------------------
 * The results toolbar
 * ---------------------------------------------------------------------- */

/**
 * "View as", "Order by" and the results line, for the main query.
 *
 * @return string
 */
function south_listings_toolbar() {
	global $wp_query;
	south_listings_assets();

	$total    = (int) $wp_query->found_posts;
	$pages    = max( 1, (int) $wp_query->max_num_pages );
	$page     = min( $pages, max( 1, (int) get_query_var( 'paged' ) ) );
	$filtered = south_listings_filtered();
	$base     = is_tax() ? get_term_link( get_queried_object() ) : get_post_type_archive_link( 'property' );
	$state    = south_listings_state();

	if ( 0 === $total ) {
		$text = __( 'No properties match your search.', 'south-listings' );
	} else {
		$count = $total;
		$text  = $filtered
			/* translators: %s: number of homes. */
			? sprintf( _n( '%s property matches your search', '%s properties match your search', $count, 'south-listings' ), number_format_i18n( $count ) )
			/* translators: %s: number of homes. */
			: sprintf( _n( '%s property', '%s properties', $count, 'south-listings' ), number_format_i18n( $count ) );
		if ( $pages > 1 ) {
			/* translators: 1: current page, 2: number of pages. */
			$text .= ' ' . sprintf( __( '(page %1$d of %2$d)', 'south-listings' ), $page, $pages );
		}
		$text .= '.';
	}

	$out  = '<div class="south-toolbar" data-south-toolbar>';
	$out .= '<div class="south-toolbar__view" data-south-view hidden><span>' . esc_html__( 'View as:', 'south-listings' ) . '</span>';
	$out .= '<button type="button" class="south-view south-view--grid is-active" data-view="grid" aria-pressed="true" aria-label="' . esc_attr__( 'Grid view', 'south-listings' ) . '"></button>';
	$out .= '<button type="button" class="south-view south-view--list" data-view="list" aria-pressed="false" aria-label="' . esc_attr__( 'List view', 'south-listings' ) . '"></button>';
	$out .= '</div>';

	$out .= '<form class="south-toolbar__order" method="get" action="' . esc_url( is_string( $base ) ? $base : home_url( '/' ) ) . '" data-south-order>';
	foreach ( south_listings_state_args() as $key => $value ) {
		if ( 'sort' !== $key ) {
			$out .= '<input type="hidden" name="' . esc_attr( $key ) . '" value="' . esc_attr( $value ) . '">';
		}
	}
	$out .= '<label for="south-order">' . esc_html__( 'Order by:', 'south-listings' ) . '</label>';
	$out .= '<select id="south-order" name="sort">';
	foreach ( south_listings_sorts() as $value => $label ) {
		$out .= '<option value="' . esc_attr( $value ) . '"' . selected( $state['sort'], $value, false ) . '>' . esc_html( $label ) . '</option>';
	}
	$out .= '</select>';
	$out .= '<button type="submit" class="wp-element-button south-toolbar__apply">' . esc_html__( 'Sort', 'south-listings' ) . '</button>';
	$out .= '</form>';

	$out .= '<p class="south-toolbar__summary" role="status">' . esc_html( $text );
	if ( $filtered && is_string( $base ) ) {
		$out .= ' <a href="' . esc_url( $base ) . '">' . esc_html( $total ? __( 'Clear filters', 'south-listings' ) : __( 'Show all properties', 'south-listings' ) ) . '</a>';
	}
	$out .= '</p>';

	return $out . '</div>';
}

/* -------------------------------------------------------------------------
 * A single home
 * ---------------------------------------------------------------------- */

/**
 * One part of the single-home page.
 *
 * @param string $part gallery|details|agent|map.
 * @return string
 */
function south_listings_property_part( $part ) {
	$post_id = get_the_ID();
	if ( ! $post_id || 'property' !== get_post_type( $post_id ) ) {
		return '';
	}
	if ( post_password_required( $post_id ) ) {
		return '';
	}
	south_listings_assets();
	switch ( $part ) {
		case 'gallery':
			return south_listings_gallery( $post_id );
		case 'details':
			return south_listings_details( $post_id );
		case 'agent':
			return south_listings_agent_box( $post_id );
		case 'map':
			return south_listings_map( $post_id );
	}
	return '';
}

/**
 * The photographs above the details, one at a time.
 *
 * @param int $post_id Property ID.
 * @return string
 */
function south_listings_gallery( $post_id ) {
	$ids = south_listings_gallery_ids( $post_id );
	if ( ! $ids ) {
		return '';
	}
	$out = '<div class="south-gallery south-slider"><div class="south-gallery__track" data-south-track>';
	foreach ( $ids as $i => $id ) {
		$alt  = get_post_meta( $id, '_wp_attachment_image_alt', true );
		$out .= '<figure class="south-gallery__slide' . ( 0 === $i ? ' is-active' : '' ) . '">';
		$out .= wp_get_attachment_image(
			$id,
			'full',
			false,
			array(
				'alt'           => '' !== $alt ? $alt : get_the_title( $post_id ),
				'loading'       => 0 === $i ? 'eager' : 'lazy',
				'fetchpriority' => 0 === $i ? 'high' : 'auto',
				'sizes'         => '(max-width: 1140px) 100vw, 1110px',
			)
		);
		$out .= '</figure>';
	}
	return $out . '</div></div>';
}

/**
 * Facts row, facts list, features, and the rooms and mortgage panels.
 *
 * @param int $post_id Property ID.
 * @return string
 */
function south_listings_details( $post_id ) {
	$out = '<div class="south-details">';

	$out .= '<div class="south-facts-row">';
	foreach ( array( 'new', 'beds', 'baths', 'garages', 'area' ) as $key ) {
		$fact = south_listings_fact( $post_id, $key );
		if ( '' !== $fact ) {
			$out .= '<p class="south-fact south-fact--' . esc_attr( $key ) . '">' . $fact . '</p>';
		}
	}
	$out .= '</div>';

	$pets  = south_listings_meta( $post_id, 'pets' );
	$facts = array(
		__( 'Reference', 'south-listings' )     => south_listings_meta( $post_id, 'reference' ),
		__( 'Type', 'south-listings' )          => south_listings_term_name( $post_id, 'property_type' ),
		__( 'Status', 'south-listings' )        => south_listings_status_label( $post_id ),
		__( 'Built', 'south-listings' )         => south_listings_meta( $post_id, 'year' ),
		__( 'Neighbourhood', 'south-listings' ) => south_listings_meta( $post_id, 'neighbourhood' ),
		__( 'Pets', 'south-listings' )          => $pets ? __( 'Allowed', 'south-listings' ) : __( 'Not allowed', 'south-listings' ),
	);
	$out  .= '<ul class="south-facts">';
	foreach ( $facts as $label => $value ) {
		if ( '' !== (string) $value ) {
			$out .= '<li><span>' . esc_html( $label ) . '</span>' . esc_html( (string) $value ) . '</li>';
		}
	}
	$out .= '</ul>';

	$features = south_listings_lines( $post_id, 'features' );
	if ( $features ) {
		$out .= '<ul class="south-features">';
		foreach ( $features as $feature ) {
			$out .= '<li>' . esc_html( $feature ) . '</li>';
		}
		$out .= '</ul>';
	}

	$out .= '<div class="south-panels">';
	$rooms = south_listings_lines( $post_id, 'rooms' );
	if ( $rooms ) {
		$out .= '<details class="south-panel-toggle"><summary class="wp-element-button">' . esc_html__( 'See floor plans', 'south-listings' ) . '</summary>';
		$out .= '<div class="south-panel"><h2>' . esc_html__( 'Rooms and sizes', 'south-listings' ) . '</h2><table class="south-rooms"><caption class="screen-reader-text">';
		/* translators: %s: property title. */
		$out .= esc_html( sprintf( __( 'Room sizes of %s', 'south-listings' ), get_the_title( $post_id ) ) ) . '</caption><tbody>';
		foreach ( $rooms as $room ) {
			$parts = array_map( 'trim', explode( '|', $room, 2 ) );
			$out  .= '<tr><th scope="row">' . esc_html( $parts[0] ) . '</th><td>' . esc_html( isset( $parts[1] ) ? $parts[1] : '' ) . '</td></tr>';
		}
		$out .= '</tbody></table>';
		$area = south_listings_meta( $post_id, 'area' );
		if ( $area ) {
			/* translators: %s: floor area, e.g. 4,200. */
			$out .= '<p>' . esc_html( sprintf( __( 'Total floor area %s sq ft. Sizes are approximate; ask the agent for the full plans.', 'south-listings' ), south_listings_number( $area ) ) ) . '</p>';
		}
		$out .= '</div></details>';
	}

	if ( 'rent' !== south_listings_status( $post_id ) && south_listings_meta( $post_id, 'price' ) ) {
		$price = (float) south_listings_meta( $post_id, 'price' );
		$out  .= '<details class="south-panel-toggle"><summary class="wp-element-button south-button--dark">' . esc_html__( 'Calculate mortgage', 'south-listings' ) . '</summary>';
		$out  .= '<div class="south-panel"><h2>' . esc_html__( 'Mortgage calculator', 'south-listings' ) . '</h2>';
		$out  .= '<form class="south-mortgage" data-south-mortgage onsubmit="return false">';
		$out  .= '<p><label for="south-mortgage-price">' . esc_html__( 'Price ($)', 'south-listings' ) . '</label><input id="south-mortgage-price" type="number" name="price" min="0" step="1000" value="' . esc_attr( (string) round( $price ) ) . '"></p>';
		$out  .= '<p><label for="south-mortgage-down">' . esc_html__( 'Down payment (%)', 'south-listings' ) . '</label><input id="south-mortgage-down" type="number" name="down" min="0" max="100" step="1" value="20"></p>';
		$out  .= '<p><label for="south-mortgage-rate">' . esc_html__( 'Interest rate (% a year)', 'south-listings' ) . '</label><input id="south-mortgage-rate" type="number" name="rate" min="0" max="25" step="0.05" value="6.5"></p>';
		$out  .= '<p><label for="south-mortgage-term">' . esc_html__( 'Term (years)', 'south-listings' ) . '</label><select id="south-mortgage-term" name="term">';
		foreach ( array( 15, 20, 25, 30 ) as $years ) {
			$out .= '<option value="' . esc_attr( $years ) . '"' . selected( 30, $years, false ) . '>' . esc_html( $years ) . '</option>';
		}
		$out .= '</select></p>';
		$out .= '<p class="south-mortgage__result">' . esc_html__( 'Estimated monthly payment:', 'south-listings' ) . ' <strong><output name="result" aria-live="polite">&mdash;</output></strong></p>';
		$out .= '<p class="south-mortgage__note">' . esc_html__( 'An estimate of principal and interest only. Taxes, insurance and fees are extra; ask your lender for a quote.', 'south-listings' ) . '</p>';
		$out .= '</form></div></details>';
	}
	$out .= '</div>';

	return $out . '</div>';
}

/**
 * The agent's box: portrait, name, number, email, and the enquiry form.
 *
 * @param int $post_id Property ID.
 * @return string
 */
function south_listings_agent_box( $post_id ) {
	$agent = south_listings_agent( $post_id );
	$out   = '<aside class="south-agent" aria-label="' . esc_attr__( 'Contact the agent', 'south-listings' ) . '">';
	if ( $agent ) {
		if ( $agent['photo'] ) {
			$out .= wp_get_attachment_image(
				$agent['photo'],
				'medium_large',
				false,
				array(
					'class' => 'south-agent__photo',
					'alt'   => $agent['name'],
				)
			);
		}
		$out .= '<div class="south-agent__info"><h2 class="south-agent__name">' . esc_html( $agent['name'] ) . '</h2>';
		$out .= '<p class="south-agent__role">' . esc_html( $agent['role'] ) . '</p>';
		if ( $agent['phone'] ) {
			$out .= '<p class="south-line south-line--phone"><span class="screen-reader-text">' . esc_html__( 'Phone', 'south-listings' ) . ' </span><a href="tel:' . esc_attr( preg_replace( '/[^0-9+]/', '', $agent['phone'] ) ) . '">' . esc_html( $agent['phone'] ) . '</a></p>';
		}
		if ( $agent['email'] ) {
			$out .= '<p class="south-line south-line--mail"><span class="screen-reader-text">' . esc_html__( 'Email', 'south-listings' ) . ' </span><a href="mailto:' . esc_attr( antispambot( $agent['email'] ) ) . '">' . esc_html( antispambot( $agent['email'] ) ) . '</a></p>';
		}
		$out .= '</div>';
	} else {
		$out .= '<div class="south-agent__info"><h2 class="south-agent__name">' . esc_html__( 'Ask about this home', 'south-listings' ) . '</h2></div>';
	}

	/**
	 * Filters the enquiry form in the agent box. The South theme provides its
	 * own form here; anything else gets an email link.
	 *
	 * @param string|null $html Form HTML, or null.
	 * @param array       $args `property` and `button`.
	 */
	$form = apply_filters(
		'south_listings_enquiry_form',
		null,
		array(
			'property' => $post_id,
			'button'   => __( 'Send Message', 'south-listings' ),
		)
	);
	if ( null === $form ) {
		$to   = $agent && $agent['email'] ? $agent['email'] : get_option( 'admin_email' );
		$form = '<p><a class="wp-element-button" href="mailto:' . esc_attr( antispambot( $to ) ) . '?subject=' . rawurlencode( get_the_title( $post_id ) ) . '">' . esc_html__( 'Send Message', 'south-listings' ) . '</a></p>';
	}
	$out .= '<div class="south-agent__form">' . $form . '</div>';

	return $out . '</aside>';
}

/**
 * The neighbourhood map, loaded only when asked.
 *
 * @param int $post_id Property ID.
 * @return string
 */
function south_listings_map( $post_id ) {
	$lat = (float) south_listings_meta( $post_id, 'lat' );
	$lon = (float) south_listings_meta( $post_id, 'lon' );
	if ( ! $lat && ! $lon ) {
		return '';
	}
	$bbox  = sprintf( '%.4f,%.4f,%.4f,%.4f', $lon - 0.0192, $lat - 0.012, $lon + 0.0192, $lat + 0.012 );
	$embed = 'https://www.openstreetmap.org/export/embed.html?bbox=' . rawurlencode( $bbox ) . '&layer=mapnik';
	$link  = sprintf( 'https://www.openstreetmap.org/#map=15/%.4f/%.4f', $lat, $lon );
	$place = implode( ', ', array_filter( array( south_listings_meta( $post_id, 'neighbourhood' ), south_listings_term_name( $post_id, 'property_city' ) ) ) );

	$out  = '<div class="south-map" data-map-src="' . esc_url( $embed ) . '"><div class="south-map__placeholder">';
	$out .= '<p class="south-map__address">' . esc_html( $place ) . '<span>' . esc_html__( 'The map shows the neighbourhood; the agent shares the exact address when you book a viewing.', 'south-listings' ) . '</span></p>';
	$out .= '<div class="south-map__actions"><button type="button" class="wp-element-button south-map__show" hidden>' . esc_html__( 'Show map', 'south-listings' ) . '</button>';
	$out .= '<a href="' . esc_url( $link ) . '">' . esc_html__( 'Open in maps', 'south-listings' ) . '</a></div>';
	return $out . '</div></div>';
}

/**
 * Enquiries about a home go to its agent.
 *
 * @param string $to      Recipient so far.
 * @param array  $enquiry The theme's sanitised enquiry.
 * @return string
 */
function south_listings_enquiry_recipient( $to, $enquiry ) {
	if ( empty( $enquiry['property'] ) || 'property' !== get_post_type( $enquiry['property'] ) ) {
		return $to;
	}
	$agent = south_listings_agent( (int) $enquiry['property'] );
	return $agent && is_email( $agent['email'] ) ? $agent['email'] : $to;
}
add_filter( 'south_enquiry_email_to', 'south_listings_enquiry_recipient', 10, 2 );
