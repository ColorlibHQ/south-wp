<?php
/**
 * Plugin Name: South Listings
 * Plugin URI: https://colorlib.com/wp/themes/south/
 * Description: Homes and agents for the South theme: a Property content type with real fields (price, sale or rent, beds, baths, garages, floor area, year built, address, features, room sizes, gallery and agent), property types, cities and status, a search that filters, sorts and pages with or without JavaScript, and one-click demo listings. Your listings stay in the database if you change theme.
 * Version: 1.0.0
 * Requires at least: 6.6
 * Requires PHP: 7.4
 * Author: Colorlib
 * Author URI: https://colorlib.com/
 * Update URI: https://updates.colorlib.com/plugin/south-listings.json
 * Text Domain: south-listings
 * Domain Path: /languages
 * License: GPL-2.0-or-later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 *
 * @package South_Listings
 */

defined( 'ABSPATH' ) || exit;

const SOUTH_LISTINGS_VERSION = '1.0.0';

/** Must match the host in the `Update URI` header, or core never calls the filter. */
const SOUTH_LISTINGS_UPDATE_ENDPOINT = 'https://updates.colorlib.com/plugin/south-listings.json';

/**
 * Where the plugin's files live.
 *
 * @param string $path Relative path.
 * @return string
 */
function south_listings_path( $path = '' ) {
	return plugin_dir_path( __FILE__ ) . $path;
}

/**
 * Public URL of a plugin file.
 *
 * @param string $path Relative path.
 * @return string
 */
function south_listings_url( $path = '' ) {
	return plugin_dir_url( __FILE__ ) . $path;
}

/**
 * Whether the South theme (or a child of it) is active.
 *
 * The plugin works under any theme: the post types, fields, search and
 * single-home details are always there. Under South it hands its HTML to the
 * theme's entry points; under another theme it registers those shortcodes
 * itself and adds the details to the content.
 *
 * @return bool
 */
function south_listings_theme_active() {
	$theme = wp_get_theme();
	return 'south' === $theme->get_stylesheet() || 'south' === $theme->get_template();
}

/* -------------------------------------------------------------------------
 * The data model
 * ---------------------------------------------------------------------- */

/**
 * Every property field. Keys are NOT underscore-prefixed on purpose: a
 * protected (`_`) key cannot be bound to a block through core/post-meta, and
 * binding them is the point.
 *
 * @return array<string, array> Meta key => definition.
 */
function south_listings_fields() {
	return array(
		'south_price'         => array( 'type' => 'number', 'label' => __( 'Price', 'south-listings' ), 'hint' => __( 'Numbers only: the sale price, or the rent per period below.', 'south-listings' ), 'group' => 'price' ),
		'south_rent_period'   => array( 'type' => 'string', 'label' => __( 'Rent period', 'south-listings' ), 'hint' => __( 'Used when the status is For Rent.', 'south-listings' ), 'group' => 'price', 'choices' => south_listings_rent_periods(), 'default' => 'month' ),
		'south_reference'     => array( 'type' => 'string', 'label' => __( 'Reference', 'south-listings' ), 'hint' => __( 'Your listing number, for example SO-1041.', 'south-listings' ), 'group' => 'price' ),
		'south_featured'      => array( 'type' => 'boolean', 'label' => __( 'Featured', 'south-listings' ), 'hint' => __( 'Shown in the Featured Properties grid and the footer.', 'south-listings' ), 'group' => 'price' ),
		'south_new'           => array( 'type' => 'boolean', 'label' => __( 'New listing', 'south-listings' ), 'hint' => __( 'Shows the tag icon on the card.', 'south-listings' ), 'group' => 'price' ),
		'south_beds'          => array( 'type' => 'integer', 'label' => __( 'Bedrooms', 'south-listings' ), 'group' => 'rooms' ),
		'south_baths'         => array( 'type' => 'integer', 'label' => __( 'Bathrooms', 'south-listings' ), 'group' => 'rooms' ),
		'south_garages'       => array( 'type' => 'integer', 'label' => __( 'Garage spaces', 'south-listings' ), 'group' => 'rooms' ),
		'south_area'          => array( 'type' => 'integer', 'label' => __( 'Floor area (sq ft)', 'south-listings' ), 'group' => 'rooms' ),
		'south_year'          => array( 'type' => 'integer', 'label' => __( 'Year built', 'south-listings' ), 'group' => 'rooms' ),
		'south_address'       => array( 'type' => 'string', 'label' => __( 'Address', 'south-listings' ), 'hint' => __( 'As it should appear on the card.', 'south-listings' ), 'group' => 'place' ),
		'south_neighbourhood' => array( 'type' => 'string', 'label' => __( 'Neighbourhood', 'south-listings' ), 'group' => 'place' ),
		'south_lat'           => array( 'type' => 'number', 'label' => __( 'Latitude', 'south-listings' ), 'hint' => __( 'For the map. The map shows the neighbourhood, not the front door.', 'south-listings' ), 'group' => 'place' ),
		'south_lon'           => array( 'type' => 'number', 'label' => __( 'Longitude', 'south-listings' ), 'group' => 'place' ),
		'south_outdoor'       => array( 'type' => 'string', 'label' => __( 'Outdoor space', 'south-listings' ), 'hint' => __( 'Any of: garden, terrace, balcony, pool. Separate with commas.', 'south-listings' ), 'group' => 'living' ),
		'south_view'          => array( 'type' => 'string', 'label' => __( 'View', 'south-listings' ), 'group' => 'living', 'choices' => south_listings_views() ),
		'south_furnished'     => array( 'type' => 'boolean', 'label' => __( 'Furnished', 'south-listings' ), 'group' => 'living' ),
		'south_pets'          => array( 'type' => 'boolean', 'label' => __( 'Pets allowed', 'south-listings' ), 'group' => 'living' ),
		'south_features'      => array( 'type' => 'string', 'label' => __( 'Features', 'south-listings' ), 'hint' => __( 'One per line.', 'south-listings' ), 'group' => 'living', 'textarea' => true ),
		'south_rooms'         => array( 'type' => 'string', 'label' => __( 'Rooms and sizes', 'south-listings' ), 'hint' => __( 'One room per line: Living room | 26 × 18 ft', 'south-listings' ), 'group' => 'living', 'textarea' => true ),
		'south_gallery'       => array( 'type' => 'string', 'label' => __( 'Gallery', 'south-listings' ), 'hint' => __( 'Photographs shown above the details, after the featured image.', 'south-listings' ), 'group' => 'gallery' ),
		'south_agent'         => array( 'type' => 'integer', 'label' => __( 'Agent', 'south-listings' ), 'group' => 'agent' ),
	);
}

/**
 * The fields of an agent.
 *
 * @return array<string, array>
 */
function south_listings_agent_fields() {
	return array(
		'south_agent_role'  => array( 'type' => 'string', 'label' => __( 'Role', 'south-listings' ), 'default' => __( 'Realtor', 'south-listings' ) ),
		'south_agent_phone' => array( 'type' => 'string', 'label' => __( 'Phone', 'south-listings' ) ),
		'south_agent_email' => array( 'type' => 'string', 'label' => __( 'Email', 'south-listings' ), 'hint' => __( 'Enquiries about this agent\'s homes are sent here.', 'south-listings' ) ),
	);
}

/**
 * @return array<string, string>
 */
function south_listings_rent_periods() {
	return array(
		'month' => __( 'per month', 'south-listings' ),
		'week'  => __( 'per week', 'south-listings' ),
		'year'  => __( 'per year', 'south-listings' ),
	);
}

/**
 * @return array<string, string>
 */
function south_listings_views() {
	return array(
		''       => __( 'None', 'south-listings' ),
		'city'   => __( 'City view', 'south-listings' ),
		'garden' => __( 'Garden view', 'south-listings' ),
		'park'   => __( 'Park view', 'south-listings' ),
		'ocean'  => __( 'Ocean view', 'south-listings' ),
	);
}

/**
 * @return array<string, string>
 */
function south_listings_outdoor_choices() {
	return array(
		'garden'  => __( 'Garden', 'south-listings' ),
		'terrace' => __( 'Terrace', 'south-listings' ),
		'balcony' => __( 'Balcony', 'south-listings' ),
		'pool'    => __( 'Pool', 'south-listings' ),
	);
}

/**
 * Register the post types, taxonomies and fields.
 */
function south_listings_register() {
	register_post_type(
		'property',
		array(
			'labels'          => array(
				'name'                  => _x( 'Properties', 'post type general name', 'south-listings' ),
				'singular_name'         => _x( 'Property', 'post type singular name', 'south-listings' ),
				'menu_name'             => _x( 'Properties', 'admin menu', 'south-listings' ),
				'add_new_item'          => __( 'Add property', 'south-listings' ),
				'edit_item'             => __( 'Edit property', 'south-listings' ),
				'new_item'              => __( 'New property', 'south-listings' ),
				'view_item'             => __( 'View property', 'south-listings' ),
				'view_items'            => __( 'View listings', 'south-listings' ),
				'search_items'          => __( 'Search properties', 'south-listings' ),
				'not_found'             => __( 'No properties found', 'south-listings' ),
				'not_found_in_trash'    => __( 'No properties in the bin', 'south-listings' ),
				'all_items'             => __( 'All properties', 'south-listings' ),
				'archives'              => __( 'Listings', 'south-listings' ),
				'featured_image'        => __( 'Main photograph', 'south-listings' ),
				'set_featured_image'    => __( 'Set main photograph', 'south-listings' ),
				'remove_featured_image' => __( 'Remove main photograph', 'south-listings' ),
				'use_featured_image'    => __( 'Use as main photograph', 'south-listings' ),
				'item_published'        => __( 'Property published.', 'south-listings' ),
				'item_updated'          => __( 'Property updated.', 'south-listings' ),
			),
			'public'          => true,
			'has_archive'     => 'listings',
			'rewrite'         => array(
				'slug'       => 'property',
				'with_front' => false,
			),
			'menu_icon'       => 'dashicons-admin-home',
			'menu_position'   => 20,
			// custom-fields is what exposes registered meta to the REST API and
			// therefore to the editor's sidebar and to block bindings.
			'supports'        => array( 'title', 'editor', 'excerpt', 'thumbnail', 'revisions', 'custom-fields', 'page-attributes' ),
			'show_in_rest'    => true,
			'rest_base'       => 'properties',
			'capability_type' => 'post',
		)
	);

	register_post_type(
		'south_agent',
		array(
			'labels'          => array(
				'name'               => _x( 'Agents', 'post type general name', 'south-listings' ),
				'singular_name'      => _x( 'Agent', 'post type singular name', 'south-listings' ),
				'add_new_item'       => __( 'Add agent', 'south-listings' ),
				'edit_item'          => __( 'Edit agent', 'south-listings' ),
				'all_items'          => __( 'Agents', 'south-listings' ),
				'not_found'          => __( 'No agents found', 'south-listings' ),
				'featured_image'     => __( 'Portrait', 'south-listings' ),
				'set_featured_image' => __( 'Set portrait', 'south-listings' ),
			),
			'public'          => false,
			'show_ui'         => true,
			'show_in_menu'    => 'edit.php?post_type=property',
			'supports'        => array( 'title', 'thumbnail', 'custom-fields' ),
			'show_in_rest'    => true,
			'rest_base'       => 'agents',
			'capability_type' => 'post',
		)
	);

	$taxonomies = array(
		'property_type'   => array( _x( 'Property types', 'taxonomy general name', 'south-listings' ), _x( 'Property type', 'taxonomy singular name', 'south-listings' ), 'property-type' ),
		'property_city'   => array( _x( 'Cities', 'taxonomy general name', 'south-listings' ), _x( 'City', 'taxonomy singular name', 'south-listings' ), 'city' ),
		'property_status' => array( _x( 'Status', 'taxonomy general name', 'south-listings' ), _x( 'Status', 'taxonomy singular name', 'south-listings' ), 'status' ),
	);
	foreach ( $taxonomies as $taxonomy => $names ) {
		register_taxonomy(
			$taxonomy,
			array( 'property' ),
			array(
				'labels'            => array(
					'name'          => $names[0],
					'singular_name' => $names[1],
				),
				'public'            => true,
				// Checkboxes in the editor: one home has one of each, picked from a short list.
				'hierarchical'      => true,
				'show_in_rest'      => true,
				'show_admin_column' => true,
				'rewrite'           => array(
					'slug'       => $names[2],
					'with_front' => false,
				),
			)
		);
	}

	foreach ( south_listings_fields() as $key => $field ) {
		$args = array(
			'type'              => $field['type'],
			'single'            => true,
			'show_in_rest'      => true,
			'sanitize_callback' => 'south_listings_sanitize_' . $field['type'],
			'auth_callback'     => 'south_listings_can_edit_meta',
		);
		if ( isset( $field['default'] ) ) {
			$args['default'] = $field['default'];
		}
		register_post_meta( 'property', $key, $args );
	}
	foreach ( south_listings_agent_fields() as $key => $field ) {
		register_post_meta(
			'south_agent',
			$key,
			array(
				'type'              => 'string',
				'single'            => true,
				'show_in_rest'      => true,
				'sanitize_callback' => 'south_agent_email' === $key ? 'sanitize_email' : 'sanitize_text_field',
				'auth_callback'     => 'south_listings_can_edit_meta',
			)
		);
	}
}
add_action( 'init', 'south_listings_register' );

/**
 * Whether the current user may write a field.
 *
 * @param bool   $allowed  Whether allowed so far.
 * @param string $meta_key Meta key.
 * @param int    $post_id  Post ID.
 * @return bool
 */
function south_listings_can_edit_meta( $allowed, $meta_key, $post_id ) {
	return current_user_can( 'edit_post', $post_id );
}

/**
 * @param mixed $value Raw value.
 * @return float|string Empty string when there is no number.
 */
function south_listings_sanitize_number( $value ) {
	if ( '' === $value || null === $value ) {
		return '';
	}
	$value = preg_replace( '/[^0-9.\-]/', '', (string) $value );
	return is_numeric( $value ) ? (float) $value : '';
}

/**
 * @param mixed $value Raw value.
 * @return int
 */
function south_listings_sanitize_integer( $value ) {
	return max( 0, (int) $value );
}

/**
 * @param mixed $value Raw value.
 * @return bool
 */
function south_listings_sanitize_boolean( $value ) {
	return (bool) rest_sanitize_boolean( $value );
}

/**
 * Strings may run over several lines (features, rooms); single-line fields
 * are cleaned by the admin form before they arrive here.
 *
 * @param mixed $value Raw value.
 * @return string
 */
function south_listings_sanitize_string( $value ) {
	return sanitize_textarea_field( (string) $value );
}

/**
 * The default status terms, and the cities and types of the demo, so the
 * search form has choices from the first minute.
 */
function south_listings_ensure_terms() {
	$terms = array(
		'property_status' => array(
			'sale' => __( 'For Sale', 'south-listings' ),
			'rent' => __( 'For Rent', 'south-listings' ),
		),
	);
	foreach ( $terms as $taxonomy => $list ) {
		foreach ( $list as $slug => $name ) {
			if ( ! term_exists( $slug, $taxonomy ) ) {
				wp_insert_term( $name, $taxonomy, array( 'slug' => $slug ) );
			}
		}
	}
}

/**
 * Activation: register, create the two status terms, flush rewrites so
 * /listings/ resolves at once, and link the listings from the South menu.
 */
function south_listings_activate() {
	south_listings_register();
	south_listings_ensure_terms();
	flush_rewrite_rules();
	south_listings_add_menu_link();
}
register_activation_hook( __FILE__, 'south_listings_activate' );

/**
 * Deactivation: drop the dead /listings/ rules.
 */
function south_listings_deactivate() {
	flush_rewrite_rules();
}
register_deactivation_hook( __FILE__, 'south_listings_deactivate' );

/**
 * A navigation-link block pointing at the listings.
 *
 * Named so the theme can call it while it builds its menu (inc/front-page-
 * setup.php checks function_exists first). The theme never declares it.
 *
 * @return string
 */
function south_listings_archive_link_block() {
	return sprintf(
		'<!-- wp:navigation-link {"label":"%s","url":"%s","kind":"custom","className":"south-nav-listings"} /-->',
		esc_attr__( 'Properties', 'south-listings' ),
		esc_url( home_url( '/listings/' ) )
	);
}

/**
 * Add "Properties" to the menu the South theme built, after "About Us", once.
 */
function south_listings_add_menu_link() {
	$menus = get_posts(
		array(
			'post_type'      => 'wp_navigation',
			'name'           => 'south-primary',
			'post_status'    => 'publish',
			'posts_per_page' => 1,
		)
	);
	if ( ! $menus || false !== strpos( $menus[0]->post_content, 'south-nav-listings' ) ) {
		return;
	}
	$content = $menus[0]->post_content;
	$link    = south_listings_archive_link_block();
	// After the second item (Home, About Us), or at the end.
	$parts = preg_split( '/(?<=\/-->)/', $content, 3 );
	$content = count( $parts ) === 3 ? $parts[0] . $parts[1] . $link . $parts[2] : $content . $link;
	wp_update_post(
		array(
			'ID'           => $menus[0]->ID,
			'post_content' => wp_slash( $content ),
		)
	);
}

/**
 * Updates, through the same first-class hook the theme uses. The theme's
 * opt-out filter covers the plugin too.
 *
 * @param array|false $update      Update data, or false.
 * @param array       $plugin_data Plugin headers.
 * @param string      $plugin_file Plugin file.
 * @return array|false
 */
function south_listings_check_update( $update, $plugin_data, $plugin_file ) {
	if ( $update || plugin_basename( __FILE__ ) !== $plugin_file ) {
		return $update;
	}
	if ( ! apply_filters( 'south_check_for_updates', true ) ) {
		return $update;
	}

	$cached = get_site_transient( 'south_listings_update' );
	if ( ! is_array( $cached ) ) {
		/**
		 * Filters what the plugin's update check sends. Mirrors the theme: no
		 * site address, only a one-way hash of it.
		 *
		 * @param array $payload What will be sent.
		 */
		$payload  = apply_filters(
			'south_listings_update_payload',
			array(
				'plugin'    => 'south-listings',
				'version'   => SOUTH_LISTINGS_VERSION,
				'wp'        => get_bloginfo( 'version' ),
				'php'       => PHP_MAJOR_VERSION . '.' . PHP_MINOR_VERSION,
				'locale'    => get_locale(),
				'multisite' => is_multisite() ? 1 : 0,
				'site'      => substr( hash_hmac( 'sha256', home_url( '/' ), wp_salt( 'auth' ) ), 0, 32 ),
			)
		);
		$response = wp_remote_get(
			add_query_arg( $payload, SOUTH_LISTINGS_UPDATE_ENDPOINT ),
			array(
				'timeout'    => 8,
				'user-agent' => 'SouthListings/' . SOUTH_LISTINGS_VERSION . '; WordPress/' . get_bloginfo( 'version' ),
			)
		);
		$cached   = ( ! is_wp_error( $response ) && 200 === wp_remote_retrieve_response_code( $response ) )
			? (array) json_decode( wp_remote_retrieve_body( $response ), true )
			: array();
		set_site_transient( 'south_listings_update', $cached, $cached ? 12 * HOUR_IN_SECONDS : HOUR_IN_SECONDS );
	}

	if ( empty( $cached['version'] ) || version_compare( $cached['version'], SOUTH_LISTINGS_VERSION, '<=' ) ) {
		return $update;
	}

	return array(
		'id'      => SOUTH_LISTINGS_UPDATE_ENDPOINT,
		'slug'    => 'south-listings',
		'plugin'  => $plugin_file,
		'version' => $cached['version'],
		'url'     => isset( $cached['url'] ) ? $cached['url'] : 'https://colorlib.com/wp/themes/south/',
		'package' => isset( $cached['package'] ) ? $cached['package'] : '',
	);
}
add_filter( 'update_plugins_updates.colorlib.com', 'south_listings_check_update', 10, 3 );

require_once south_listings_path( 'includes/format.php' );
require_once south_listings_path( 'includes/query.php' );
require_once south_listings_path( 'includes/bindings.php' );
require_once south_listings_path( 'includes/render.php' );
require_once south_listings_path( 'includes/admin.php' );
require_once south_listings_path( 'includes/demo.php' );
