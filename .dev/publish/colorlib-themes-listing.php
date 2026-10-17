<?php
/**
 * Add South to the /wp/themes/ listing (page 5091).
 *
 * Two edits, each idempotent:
 *
 *  - a card in the free themes grid, right after Unapp's, where the newest
 *    block themes go (Martine, Nonprofit, Lagoon … follow it);
 *  - South added to the existing picker line "A property developer or estate
 *    agent", which named only Dreamrs, a developer's theme. South is the one
 *    for an agency with listings.
 *
 * Unioncorp's version also rewrote an intro sentence listing the self-hosted
 * themes; that sentence is no longer on the page, so there is nothing to edit.
 *
 * Safe to re-run: if South is already listed, only the card image is refreshed.
 */

defined( 'ABSPATH' ) || exit;

$page_id  = 5091;
$page_url = 'https://colorlib.com/wp/themes/south/';

// The card image comes from the attachment, found by file name: a hardcoded
// URL silently points at the wrong file when an import adds `-1`.
global $wpdb;
$card_file = 'south-free-real-estate-wordpress-theme.jpg';
$card_att  = (int) $wpdb->get_var(
	$wpdb->prepare(
		"SELECT post_id FROM {$wpdb->postmeta}
		 WHERE meta_key = '_wp_attached_file' AND ( meta_value = %s OR meta_value LIKE %s )
		 ORDER BY post_id DESC LIMIT 1",
		$card_file,
		'%/' . $wpdb->esc_like( $card_file )
	)
);
$card_img = $card_att ? wp_get_attachment_url( $card_att ) : '';
$card_dim = $card_att ? wp_get_attachment_image_src( $card_att, 'full' ) : false;

$content = get_post_field( 'post_content', $page_id );

if ( ! $card_img || ! $card_dim ) {
	echo "ERROR: $card_file is not in the media library yet\n";
	return;
}

if ( '' === $content ) {
	echo "ERROR: page $page_id has no content\n";
	return;
}

if ( false !== stripos( $content, 'id="theme-south"' ) ) {
	if ( false !== strpos( $content, $card_img ) ) {
		echo "already listed, card image current — nothing to do\n";
		return;
	}
	$updated = preg_replace(
		'~(<li class="clt-theme" id="theme-south">.*?<img src=")[^"]+~s',
		'$1' . $card_img,
		$content,
		1
	);
	if ( null === $updated || $updated === $content ) {
		echo "ERROR: listed, but the card image could not be replaced\n";
		return;
	}
	$content = $updated;
	echo "card image updated to $card_img\n";
} else {
	$anchor = '<li class="clt-theme" id="theme-martine">';
	if ( 1 !== substr_count( $content, $anchor ) ) {
		echo "ERROR: could not find exactly one Martine card to insert before\n";
		return;
	}

	$card = '<li class="clt-theme" id="theme-south">'
		. '<span class="clt-theme__shot">'
		. '<img src="' . esc_url( $card_img ) . '"'
		. ' alt="South WordPress theme home page: a modern house beside a lit pond at dusk under the headline Find your home, with the property search below"'
		. ' width="' . (int) $card_dim[1] . '" height="' . (int) $card_dim[2] . '"'
		. ' loading="lazy" decoding="async" />'
		. '</span>'
		. '<span class="clt-theme__body">'
		. '<span class="clt-theme__kind">Block theme and listings plugin</span>'
		. '<h3 class="clt-theme__name"><a href="' . esc_url( $page_url ) . '">South</a></h3>'
		. '<span class="clt-theme__desc">A theme for real estate agents and agencies: property listings with real fields, a search that filters, sorts and pages, a page for every home with its agent, and eight colour palettes.</span>'
		. '<span class="clt-theme__foot"><span class="clt-theme__installs"></span><span class="clt-theme__cta">View theme &rarr;</span></span>'
		. '</span></li>';

	$content = str_replace( $anchor, $card . $anchor, $content );
	echo "card added before Martine\n";
}

// The picker line. Matched exactly; left alone, and reported, if it changed.
$pick_old = '<span class="clt-pick__lab">A property developer or estate agent</span><span class="clt-pick__themes"><a href="#theme-dreamrs">Dreamrs</a></span>';
$pick_new = '<span class="clt-pick__lab">A property developer or estate agent</span><span class="clt-pick__themes"><a href="#theme-south">South</a>, <a href="#theme-dreamrs">Dreamrs</a></span>';
if ( false !== strpos( $content, $pick_new ) ) {
	echo "picker already lists South\n";
} elseif ( 1 === substr_count( $content, $pick_old ) ) {
	$content = str_replace( $pick_old, $pick_new, $content );
	echo "picker: South added to the estate agent line\n";
} else {
	echo "NOTE: estate agent picker line not found as expected — left unchanged\n";
}

$kses = has_filter( 'content_save_pre', 'wp_filter_post_kses' );
if ( $kses ) {
	kses_remove_filters();
}

// wp_update_post() unslashes; the SVG and attribute quotes are exactly what a
// stray unslash would damage.
$result = wp_update_post( array( 'ID' => $page_id, 'post_content' => wp_slash( $content ) ), true );

if ( $kses ) {
	kses_init_filters();
}

if ( is_wp_error( $result ) ) {
	echo 'ERROR: ' . $result->get_error_message() . "\n";
	return;
}

if ( function_exists( 'visual_composer' ) && method_exists( visual_composer(), 'buildShortcodesCss' ) ) {
	visual_composer()->buildShortcodesCss( $page_id, 'custom' );
	visual_composer()->buildShortcodesCss( $page_id, 'default' );
}

$saved = get_post_field( 'post_content', $page_id );

echo 'page: ' . $page_id . ' (' . get_post_status( $page_id ) . ")\n";
echo 'saved intact: ' . ( $saved === $content ? 'yes' : 'NO' ) . "\n";
echo 'south cards: ' . substr_count( $saved, 'id="theme-south"' ) . " (must be 1)\n";
echo 'south picker links: ' . substr_count( $saved, '#theme-south' ) . " (must be 1)\n";
echo 'martine cards still present: ' . substr_count( $saved, 'id="theme-martine"' ) . " (must be 1)\n";
echo 'total cards: ' . substr_count( $saved, 'class="clt-theme"' ) . "\n";
