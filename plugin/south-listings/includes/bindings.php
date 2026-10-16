<?php
/**
 * Block bindings: a home's fields as the content of ordinary blocks.
 *
 * The theme's property card is a core/query loop of plain blocks. Its price,
 * tag, address and facts are paragraphs bound to this source:
 *
 *   <!-- wp:paragraph {"metadata":{"bindings":{"content":{
 *       "source":"south/property","args":{"key":"price"}}}}} -->
 *
 * The source returns FORMATTED values ("$2 450 000", "For Rent", "4,200 sq ft"),
 * which is why it exists beside core/post-meta: every field is also registered
 * with show_in_rest and an unprotected key, so core/post-meta can bind the raw
 * numbers too. Values resolve at render time, so a pattern expanded into a
 * page never freezes them (Academia's lesson about PHP in patterns).
 *
 * @package South_Listings
 */

defined( 'ABSPATH' ) || exit;

/**
 * The keys this source answers, with a label for the editor.
 *
 * @return array<string, string>
 */
function south_listings_binding_keys() {
	return array(
		'status'        => __( 'Status (For Sale / For Rent)', 'south-listings' ),
		'price'         => __( 'Price', 'south-listings' ),
		'address'       => __( 'Address', 'south-listings' ),
		'beds'          => __( 'Bedrooms', 'south-listings' ),
		'baths'         => __( 'Bathrooms', 'south-listings' ),
		'garages'       => __( 'Garage spaces', 'south-listings' ),
		'area'          => __( 'Floor area', 'south-listings' ),
		'new'           => __( 'New listing tag', 'south-listings' ),
		'type'          => __( 'Property type', 'south-listings' ),
		'city'          => __( 'City', 'south-listings' ),
		'neighbourhood' => __( 'Neighbourhood', 'south-listings' ),
		'reference'     => __( 'Reference', 'south-listings' ),
		'year'          => __( 'Year built', 'south-listings' ),
	);
}

/**
 * Register the source.
 */
function south_listings_register_bindings() {
	if ( ! function_exists( 'register_block_bindings_source' ) ) {
		return;
	}
	register_block_bindings_source(
		'south/property',
		array(
			'label'              => __( 'South property', 'south-listings' ),
			'get_value_callback' => 'south_listings_binding_value',
			'uses_context'       => array( 'postId', 'postType' ),
		)
	);
}
add_action( 'init', 'south_listings_register_bindings' );

/**
 * The value of one key for the post in context.
 *
 * @param array    $args           `key`.
 * @param WP_Block $block_instance The bound block.
 * @return string|null Null leaves the block's own content in place.
 */
function south_listings_binding_value( array $args, $block_instance ) {
	$post_id = isset( $block_instance->context['postId'] ) ? (int) $block_instance->context['postId'] : get_the_ID();
	$key     = isset( $args['key'] ) ? (string) $args['key'] : '';

	if ( ! $post_id || 'property' !== get_post_type( $post_id ) || ! array_key_exists( $key, south_listings_binding_keys() ) ) {
		return null;
	}
	if ( post_password_required( $post_id ) ) {
		return '';
	}

	switch ( $key ) {
		case 'status':
			return esc_html( south_listings_status_label( $post_id ) );
		case 'price':
			return esc_html( south_listings_price( $post_id ) );
		case 'address':
			return esc_html( (string) south_listings_meta( $post_id, 'address' ) );
		case 'beds':
		case 'baths':
		case 'garages':
		case 'area':
		case 'new':
			return south_listings_fact( $post_id, $key );
		case 'type':
			return esc_html( south_listings_term_name( $post_id, 'property_type' ) );
		case 'city':
			return esc_html( south_listings_term_name( $post_id, 'property_city' ) );
		case 'year':
			$year = (int) south_listings_meta( $post_id, 'year' );
			return $year ? (string) $year : '';
		default:
			return esc_html( (string) south_listings_meta( $post_id, $key ) );
	}
}

/**
 * The editor half: the same source in JavaScript, so the editor shows each
 * home's own values instead of the source's name.
 */
function south_listings_bindings_editor_script() {
	wp_enqueue_script(
		'south-listings-bindings',
		south_listings_url( 'assets/bindings-editor.js' ),
		array( 'wp-blocks', 'wp-data', 'wp-core-data', 'wp-i18n' ),
		SOUTH_LISTINGS_VERSION,
		true
	);
	wp_add_inline_script(
		'south-listings-bindings',
		'window.southListingsBindings = ' . wp_json_encode(
			array(
				'label' => __( 'South property', 'south-listings' ),
				'keys'  => south_listings_binding_keys(),
				'sqft'  => __( '%s sq ft', 'south-listings' ),
				'month' => _x( '/mo', 'rent per month, after the price', 'south-listings' ),
			)
		) . ';',
		'before'
	);
}
add_action( 'enqueue_block_editor_assets', 'south_listings_bindings_editor_script' );
