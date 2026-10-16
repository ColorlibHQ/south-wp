<?php
/**
 * Reading and formatting a home's fields. Every visible value goes through
 * here, so the card, the single page, the block bindings and the search
 * results always agree.
 *
 * @package South_Listings
 */

defined( 'ABSPATH' ) || exit;

/**
 * A field's stored value.
 *
 * @param int    $post_id Property ID.
 * @param string $key     Meta key without the `south_` prefix.
 * @return mixed
 */
function south_listings_meta( $post_id, $key ) {
	return get_post_meta( $post_id, 'south_' . $key, true );
}

/**
 * `sale`, `rent` or '' from the Status term.
 *
 * @param int $post_id Property ID.
 * @return string
 */
function south_listings_status( $post_id ) {
	$terms = get_the_terms( $post_id, 'property_status' );
	if ( ! $terms || is_wp_error( $terms ) ) {
		return '';
	}
	return $terms[0]->slug;
}

/**
 * "For Sale" / "For Rent", or whatever the Status term is called.
 *
 * @param int $post_id Property ID.
 * @return string
 */
function south_listings_status_label( $post_id ) {
	$terms = get_the_terms( $post_id, 'property_status' );
	return ( $terms && ! is_wp_error( $terms ) ) ? $terms[0]->name : '';
}

/**
 * The name of a home's first term in a taxonomy.
 *
 * @param int    $post_id  Property ID.
 * @param string $taxonomy Taxonomy.
 * @return string
 */
function south_listings_term_name( $post_id, $taxonomy ) {
	$terms = get_the_terms( $post_id, $taxonomy );
	return ( $terms && ! is_wp_error( $terms ) ) ? $terms[0]->name : '';
}

/**
 * A number with thousands grouped, as the design writes floor areas: 4,200.
 *
 * @param float|int $number Number.
 * @return string
 */
function south_listings_number( $number ) {
	return number_format_i18n( (float) $number );
}

/**
 * The price as the design writes it: "$2 450 000", "$4 800 /mo".
 *
 * @param int $post_id Property ID.
 * @return string
 */
function south_listings_price( $post_id ) {
	$price = south_listings_meta( $post_id, 'price' );
	if ( '' === $price || null === $price ) {
		return '';
	}

	/**
	 * Filters how prices are written.
	 *
	 * @param array $format `currency`, `position` (before|after), `thousands`, `decimals`, `point`.
	 */
	$format = apply_filters(
		'south_listings_price_format',
		array(
			'currency'  => '$',
			'position'  => 'before',
			'thousands' => ' ',
			'decimals'  => 0,
			'point'     => '.',
		)
	);

	$amount = number_format( (float) $price, (int) $format['decimals'], $format['point'], $format['thousands'] );
	$text   = 'after' === $format['position'] ? $amount . ' ' . $format['currency'] : $format['currency'] . $amount;

	if ( 'rent' === south_listings_status( $post_id ) ) {
		$periods = array(
			'month' => _x( '/mo', 'rent per month, after the price', 'south-listings' ),
			'week'  => _x( '/wk', 'rent per week, after the price', 'south-listings' ),
			'year'  => _x( '/yr', 'rent per year, after the price', 'south-listings' ),
		);
		$period  = (string) south_listings_meta( $post_id, 'rent_period' );
		$text   .= ' ' . ( isset( $periods[ $period ] ) ? $periods[ $period ] : $periods['month'] );
	}

	return $text;
}

/**
 * One fact as the card shows it: the number, with a label for screen readers
 * (the design shows an icon in its place).
 *
 * @param int    $post_id Property ID.
 * @param string $key     beds|baths|garages|area|new.
 * @return string HTML; '' when the fact does not apply.
 */
function south_listings_fact( $post_id, $key ) {
	$labels = array(
		'beds'    => __( 'Bedrooms', 'south-listings' ),
		'baths'   => __( 'Bathrooms', 'south-listings' ),
		'garages' => __( 'Garage spaces', 'south-listings' ),
		'area'    => __( 'Floor area', 'south-listings' ),
	);

	if ( 'new' === $key ) {
		return south_listings_meta( $post_id, 'new' )
			? '<span class="screen-reader-text">' . esc_html__( 'New listing', 'south-listings' ) . '</span>'
			: '';
	}
	if ( ! isset( $labels[ $key ] ) ) {
		return '';
	}

	$value = south_listings_meta( $post_id, $key );
	if ( '' === $value || null === $value ) {
		return '';
	}

	if ( 'area' === $key ) {
		/* translators: %s: floor area, e.g. 4,200. */
		$text = sprintf( __( '%s sq ft', 'south-listings' ), south_listings_number( $value ) );
	} else {
		$text = (string) (int) $value;
	}

	return '<span class="screen-reader-text">' . esc_html( $labels[ $key ] ) . ' </span>' . esc_html( $text );
}

/**
 * The agent of a home, as an array, or null.
 *
 * @param int $post_id Property ID.
 * @return array|null
 */
function south_listings_agent( $post_id ) {
	$agent = (int) south_listings_meta( $post_id, 'agent' );
	if ( ! $agent || 'south_agent' !== get_post_type( $agent ) || 'publish' !== get_post_status( $agent ) ) {
		return null;
	}
	$role = get_post_meta( $agent, 'south_agent_role', true );
	return array(
		'id'    => $agent,
		'name'  => get_the_title( $agent ),
		'role'  => '' !== $role ? $role : __( 'Realtor', 'south-listings' ),
		'phone' => get_post_meta( $agent, 'south_agent_phone', true ),
		'email' => get_post_meta( $agent, 'south_agent_email', true ),
		'photo' => get_post_thumbnail_id( $agent ),
	);
}

/**
 * Lines of a multi-line field, trimmed, empties dropped.
 *
 * @param int    $post_id Property ID.
 * @param string $key     Field.
 * @return string[]
 */
function south_listings_lines( $post_id, $key ) {
	$text = (string) south_listings_meta( $post_id, $key );
	return array_values( array_filter( array_map( 'trim', preg_split( '/\r\n|\r|\n/', $text ) ) ) );
}

/**
 * The gallery: the featured image first, then the gallery field's IDs.
 *
 * @param int $post_id Property ID.
 * @return int[]
 */
function south_listings_gallery_ids( $post_id ) {
	$ids   = array_filter( array_map( 'absint', explode( ',', (string) south_listings_meta( $post_id, 'gallery' ) ) ) );
	$thumb = get_post_thumbnail_id( $post_id );
	// The design's gallery is the large photographs; the card's photo leads only
	// when there is no gallery at all.
	if ( ! $ids && $thumb ) {
		$ids = array( $thumb );
	}
	return array_values( array_unique( $ids ) );
}
