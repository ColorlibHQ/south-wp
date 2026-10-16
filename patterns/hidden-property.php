<?php
/**
 * Title: Single property
 * Slug: south/hidden-property
 * Description: One home: its photographs, price, title, address, description, facts, features, the agent and the map.
 * Inserter: no
 *
 * @package South
 */

defined( 'ABSPATH' ) || exit;
?>
<!-- wp:group {"align":"full","className":"south-section south-listing-area","style":{"spacing":{"padding":{"top":"var:preset|spacing|70","bottom":"var:preset|spacing|70"}}},"layout":{"type":"constrained"}} -->
<div class="wp-block-group alignfull south-section south-listing-area" style="padding-top:var(--wp--preset--spacing--70);padding-bottom:var(--wp--preset--spacing--70)"><!-- wp:group {"className":"south-listing","layout":{"type":"default"}} -->
<div class="wp-block-group south-listing"><!-- wp:shortcode -->
[south_property part="gallery"]
<!-- /wp:shortcode -->

<!-- wp:columns {"className":"south-listing__columns","style":{"spacing":{"blockGap":{"top":"var:preset|spacing|40","left":"var:preset|spacing|40"}}}} -->
<div class="wp-block-columns south-listing__columns"><!-- wp:column {"width":"65.77%"} -->
<div class="wp-block-column" style="flex-basis:65.77%"><!-- wp:paragraph {"metadata":{"bindings":{"content":{"source":"south/property","args":{"key":"price"}}}},"className":"south-listing__price"} -->
<p class="south-listing__price">$2 450 000</p>
<!-- /wp:paragraph -->

<!-- wp:post-title {"level":1,"className":"south-listing__title"} /-->

<!-- wp:paragraph {"metadata":{"bindings":{"content":{"source":"south/property","args":{"key":"address"}}}},"className":"south-listing__location"} -->
<p class="south-listing__location">Calle Serena 24, Malibu, CA</p>
<!-- /wp:paragraph -->

<!-- wp:post-content {"className":"south-listing__content","layout":{"type":"default"}} /-->

<!-- wp:shortcode -->
[south_property part="details"]
<!-- /wp:shortcode --></div>
<!-- /wp:column -->

<!-- wp:column {"width":"31.53%"} -->
<div class="wp-block-column" style="flex-basis:31.53%"><!-- wp:shortcode -->
[south_property part="agent"]
<!-- /wp:shortcode --></div>
<!-- /wp:column --></div>
<!-- /wp:columns -->

<!-- wp:shortcode -->
[south_property part="map"]
<!-- /wp:shortcode --></div>
<!-- /wp:group --></div>
<!-- /wp:group -->
