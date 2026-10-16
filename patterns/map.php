<?php
/**
 * Title: Map (loads on request)
 * Slug: south/map
 * Categories: south-sections
 * Keywords: map, location, openstreetmap
 * Description: An address card with a Show map button: nothing is loaded from OpenStreetMap until a visitor asks for it, and Open in maps works without JavaScript.
 *
 * @package South
 */

defined( 'ABSPATH' ) || exit;
?>
<!-- wp:group {"align":"full","className":"south-section south-map-area","style":{"spacing":{"padding":{"bottom":"var:preset|spacing|70"}}},"layout":{"type":"constrained"}} -->
<div class="wp-block-group alignfull south-section south-map-area" style="padding-bottom:var(--wp--preset--spacing--70)"><!-- wp:html -->
<div class="south-map" data-map-src="https://www.openstreetmap.org/export/embed.html?bbox=-118.2637%2C34.0385%2C-118.2253%2C34.0625&amp;layer=mapnik"><div class="south-map__placeholder"><p class="south-map__address">Main Str. no 45-46, b3, Los Angeles, CA 90012<span>The office is a short walk from the Civic Center Metro station. The map loads from OpenStreetMap when you ask for it.</span></p><div class="south-map__actions"><button type="button" class="wp-element-button south-map__show" hidden>Show map</button><a href="https://www.openstreetmap.org/#map=15/34.0505/-118.2445">Open in maps</a></div></div></div>
<!-- /wp:html --></div>
<!-- /wp:group -->
