<?php
/**
 * The property search, done by WordPress's own query.
 *
 * The search box submits a plain GET form to /listings/ with the query in the
 * address, the same parameters the HTML template uses:
 *
 *   /listings/?city=pasadena&type=house&beds=3&sort=price-asc
 *
 * pre_get_posts turns those into a tax_query and a meta_query on the MAIN
 * query, so the archive template's core/query loop (inherit: true), its
 * pagination and its "N properties" line all describe the same result, with
 * JavaScript or without it. Pagination links keep the parameters because
 * core builds them from the current request.
 *
 * Parameter names are not registered as query vars on purpose: several
 * (`type`, `status`, `city`) are words other plugins use, and as plain GET
 * parameters they can never change which template WordPress picks.
 *
 * @package South_Listings
 */

defined( 'ABSPATH' ) || exit;

/** Listings per page, as the template shows them. */
const SOUTH_LISTINGS_PER_PAGE = 6;

/**
 * The ranges the sliders cover. A value at a range's edge means "no limit",
 * so an untouched slider never narrows the search.
 *
 * @return array<string, array{0:int,1:int,2:int}> name => [min, max, step]
 */
function south_listings_ranges() {
	return apply_filters(
		'south_listings_ranges',
		array(
			'area' => array( 500, 4500, 50 ),
			'year' => array( 1900, (int) gmdate( 'Y' ), 1 ),
		)
	);
}

/**
 * The sort orders offered.
 *
 * @return array<string, string>
 */
function south_listings_sorts() {
	return array(
		''           => __( 'Default', 'south-listings' ),
		'newest'     => __( 'Newest', 'south-listings' ),
		'price-asc'  => __( 'Lowest price', 'south-listings' ),
		'price-desc' => __( 'Highest price', 'south-listings' ),
		'area-desc'  => __( 'Largest', 'south-listings' ),
	);
}

/**
 * Read, validate and default the search from the query string.
 *
 * @return array<string, string|int>
 */
function south_listings_state() {
	static $state = null;
	if ( null !== $state ) {
		return $state;
	}

	// phpcs:disable WordPress.Security.NonceVerification.Recommended -- a read-only public search.
	$get = function ( $key ) {
		return isset( $_GET[ $key ] ) && is_string( $_GET[ $key ] ) ? trim( sanitize_text_field( wp_unslash( $_GET[ $key ] ) ) ) : '';
	};
	// phpcs:enable WordPress.Security.NonceVerification.Recommended

	$ranges = south_listings_ranges();
	$sort   = $get( 'sort' );

	$state = array(
		'q'         => $get( 'q' ),
		'city'      => sanitize_title( $get( 'city' ) ),
		'type'      => sanitize_title( $get( 'type' ) ),
		'status'    => sanitize_title( $get( 'status' ) ),
		'garages'   => absint( $get( 'garages' ) ),
		'beds'      => absint( $get( 'beds' ) ),
		'baths'     => absint( $get( 'baths' ) ),
		'area_min'  => absint( $get( 'area_min' ) ),
		'area_max'  => absint( $get( 'area_max' ) ),
		'year_min'  => absint( $get( 'year_min' ) ),
		'year_max'  => absint( $get( 'year_max' ) ),
		'max_price' => absint( $get( 'max_price' ) ),
		'max_rent'  => absint( $get( 'max_rent' ) ),
		'outdoor'   => sanitize_key( $get( 'outdoor' ) ),
		'view'      => sanitize_key( $get( 'view' ) ),
		'furnished' => in_array( $get( 'furnished' ), array( 'yes', 'no' ), true ) ? $get( 'furnished' ) : '',
		'pets'      => 'yes' === $get( 'pets' ) ? 'yes' : '',
		'sort'      => array_key_exists( $sort, south_listings_sorts() ) ? $sort : '',
	);

	// A slider left at its edge is no filter at all.
	foreach ( array( 'area', 'year' ) as $range ) {
		list( $min, $max ) = $ranges[ $range ];
		if ( $state[ $range . '_min' ] <= $min ) {
			$state[ $range . '_min' ] = 0;
		}
		if ( $state[ $range . '_max' ] >= $max || ! $state[ $range . '_max' ] ) {
			$state[ $range . '_max' ] = 0;
		}
	}

	return $state;
}

/**
 * Whether any filter (not just a sort order) is in force.
 *
 * @return bool
 */
function south_listings_filtered() {
	foreach ( south_listings_state() as $key => $value ) {
		if ( 'sort' !== $key && $value ) {
			return true;
		}
	}
	return false;
}

/**
 * The current search as query arguments, without empties: what the toolbar
 * carries in its hidden fields and the "clear" link drops.
 *
 * @return array<string, string|int>
 */
function south_listings_state_args() {
	return array_filter( south_listings_state() );
}

/**
 * Apply the search to the main query on the listings and on the property
 * type, city and status archives.
 *
 * @param WP_Query $query The query.
 */
function south_listings_pre_get_posts( $query ) {
	if ( is_admin() || ! $query->is_main_query() ) {
		return;
	}
	if ( ! $query->is_post_type_archive( 'property' ) && ! $query->is_tax( array( 'property_type', 'property_city', 'property_status' ) ) ) {
		return;
	}

	$state = south_listings_state();

	$query->set( 'posts_per_page', (int) apply_filters( 'south_listings_per_page', SOUTH_LISTINGS_PER_PAGE ) );

	$tax = array();
	foreach ( array( 'city' => 'property_city', 'type' => 'property_type', 'status' => 'property_status' ) as $key => $taxonomy ) {
		if ( $state[ $key ] ) {
			$tax[] = array(
				'taxonomy' => $taxonomy,
				'field'    => 'slug',
				'terms'    => $state[ $key ],
			);
		}
	}
	if ( $tax ) {
		$existing = $query->get( 'tax_query' );
		$query->set( 'tax_query', array_merge( is_array( $existing ) ? $existing : array(), array( 'relation' => 'AND' ), $tax ) );
	}

	$meta = array( 'relation' => 'AND' );
	foreach ( array( 'garages', 'beds', 'baths' ) as $key ) {
		if ( $state[ $key ] ) {
			$meta[] = array(
				'key'     => 'south_' . $key,
				'value'   => $state[ $key ],
				'compare' => '>=',
				'type'    => 'NUMERIC',
			);
		}
	}
	foreach ( array( 'area', 'year' ) as $range ) {
		if ( $state[ $range . '_min' ] ) {
			$meta[] = array(
				'key'     => 'south_' . $range,
				'value'   => $state[ $range . '_min' ],
				'compare' => '>=',
				'type'    => 'NUMERIC',
			);
		}
		if ( $state[ $range . '_max' ] ) {
			$meta[] = array(
				'key'     => 'south_' . $range,
				'value'   => $state[ $range . '_max' ],
				'compare' => '<=',
				'type'    => 'NUMERIC',
			);
		}
	}
	// A price cap applies to homes for sale, a rent cap to rentals; each leaves
	// the other kind alone, as the template's search does.
	foreach ( array( 'max_price' => 'sale', 'max_rent' => 'rent' ) as $key => $status ) {
		if ( ! $state[ $key ] ) {
			continue;
		}
		$capped = get_posts(
			array(
				'post_type'      => 'property',
				'posts_per_page' => -1,
				'fields'         => 'ids',
				'no_found_rows'  => true,
				'tax_query'      => array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query -- small, bounded set.
					array(
						'taxonomy' => 'property_status',
						'field'    => 'slug',
						'terms'    => $status,
					),
				),
				'meta_query'     => array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
					array(
						'key'     => 'south_price',
						'value'   => $state[ $key ],
						'compare' => '>',
						'type'    => 'NUMERIC',
					),
				),
			)
		);
		if ( $capped ) {
			$query->set( 'post__not_in', array_merge( (array) $query->get( 'post__not_in' ), $capped ) );
		}
	}
	foreach ( array( 'furnished', 'pets' ) as $key ) {
		if ( $state[ $key ] ) {
			$meta[] = array(
				'key'   => 'south_' . $key,
				'value' => 'yes' === $state[ $key ] ? '1' : '',
			);
		}
	}
	if ( $state['view'] ) {
		$meta[] = array(
			'key'   => 'south_view',
			'value' => $state['view'],
		);
	}
	if ( $state['outdoor'] ) {
		$meta[] = array(
			'key'     => 'south_outdoor',
			'value'   => $state['outdoor'],
			'compare' => 'LIKE',
		);
	}
	if ( count( $meta ) > 1 ) {
		$query->set( 'meta_query', $meta );
	}

	if ( '' !== $state['q'] ) {
		$query->set( 's', $state['q'] );
	}

	switch ( $state['sort'] ) {
		case 'newest':
			$query->set( 'orderby', 'date' );
			$query->set( 'order', 'DESC' );
			break;
		case 'price-asc':
		case 'price-desc':
			$query->set( 'meta_key', 'south_price' ); // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
			$query->set( 'orderby', 'meta_value_num' );
			$query->set( 'order', 'price-asc' === $state['sort'] ? 'ASC' : 'DESC' );
			break;
		case 'area-desc':
			$query->set( 'meta_key', 'south_area' ); // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
			$query->set( 'orderby', 'meta_value_num' );
			$query->set( 'order', 'DESC' );
			break;
		default:
			// The order the agency gave its listings (Order, under Page
			// attributes), newest first within it.
			$query->set(
				'orderby',
				array(
					'menu_order' => 'ASC',
					'date'       => 'DESC',
				)
			);
	}
}
add_action( 'pre_get_posts', 'south_listings_pre_get_posts' );

/**
 * A search for `q` on the listings must not turn the page into the site's
 * search results: keep the listings template and its title.
 *
 * @param WP_Query $query The query.
 */
function south_listings_keep_archive( $query ) {
	if ( $query->is_main_query() && ! is_admin() && $query->is_post_type_archive( 'property' ) ) {
		$query->is_search = false;
	}
}
add_action( 'parse_query', 'south_listings_keep_archive', 20 );

/**
 * Query loops in patterns ask for featured homes with `southFeatured` in
 * their query object; the editor's serialiser keeps the extra key.
 *
 * @param array    $query Query vars.
 * @param WP_Block $block The post-template block.
 * @return array
 */
function south_listings_featured_loop( $query, $block ) {
	$context = isset( $block->context['query'] ) ? $block->context['query'] : array();
	if ( empty( $context['southFeatured'] ) || 'property' !== ( $context['postType'] ?? '' ) ) {
		return $query;
	}
	$query['meta_query'] = array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
		array(
			'key'   => 'south_featured',
			'value' => '1',
		),
	);
	$query['orderby'] = array(
		'menu_order' => 'ASC',
		'date'       => 'DESC',
	);
	return $query;
}
add_filter( 'query_loop_block_query_vars', 'south_listings_featured_loop', 10, 2 );
