<?php
/**
 * Title: Latest properties
 * Slug: south/latest-properties
 * Categories: south-sections, south-listings
 * Keywords: properties, latest, new
 * Description: The three newest listings in one row. Needs the South Listings plugin.
 *
 * @package South
 */

defined( 'ABSPATH' ) || exit;
?>
<!-- wp:group {"align":"full","className":"south-section south-featured south-requires-listings","style":{"spacing":{"padding":{"top":"var:preset|spacing|70","bottom":"var:preset|spacing|50"}}},"layout":{"type":"constrained"}} -->
<div class="wp-block-group alignfull south-section south-featured south-requires-listings" style="padding-top:var(--wp--preset--spacing--70);padding-bottom:var(--wp--preset--spacing--50)"><!-- wp:group {"className":"south-section-head","layout":{"type":"default"}} -->
<div class="wp-block-group south-section-head"><!-- wp:heading {"className":"south-section-title","style":{"typography":{"textAlign":"center"}}} -->
<h2 class="wp-block-heading has-text-align-center south-section-title">New on the market</h2>
<!-- /wp:heading -->

<!-- wp:paragraph {"className":"south-section-sub","style":{"typography":{"textAlign":"center"}}} -->
<p class="has-text-align-center south-section-sub">The three most recent listings.</p>
<!-- /wp:paragraph --></div>
<!-- /wp:group -->

<!-- wp:query {"queryId":12,"query":{"perPage":3,"pages":0,"offset":0,"postType":"property","order":"desc","orderBy":"date","inherit":false},"className":"south-properties","layout":{"type":"default"}} -->
<div class="wp-block-query south-properties"><!-- wp:post-template {"className":"south-properties__grid","layout":{"type":"grid","columnCount":3}} -->
<!-- wp:group {"className":"south-card","layout":{"type":"default"}} -->
<div class="wp-block-group south-card"><!-- wp:group {"className":"south-card__media","layout":{"type":"default"}} -->
<div class="wp-block-group south-card__media"><!-- wp:post-featured-image {"aspectRatio":"1000/724","className":"south-card__image"} /-->

<!-- wp:paragraph {"metadata":{"bindings":{"content":{"source":"south/property","args":{"key":"status"}}}},"className":"south-card__tag"} -->
<p class="south-card__tag">For Sale</p>
<!-- /wp:paragraph -->

<!-- wp:paragraph {"metadata":{"bindings":{"content":{"source":"south/property","args":{"key":"price"}}}},"className":"south-card__price"} -->
<p class="south-card__price">$945 679</p>
<!-- /wp:paragraph --></div>
<!-- /wp:group -->

<!-- wp:group {"className":"south-card__body","layout":{"type":"default"}} -->
<div class="wp-block-group south-card__body"><!-- wp:post-title {"level":3,"isLink":true,"className":"south-card__title"} /-->

<!-- wp:paragraph {"metadata":{"bindings":{"content":{"source":"south/property","args":{"key":"address"}}}},"className":"south-card__location"} -->
<p class="south-card__location">Upper Road 3411, Los Angeles, CA</p>
<!-- /wp:paragraph -->

<!-- wp:post-excerpt {"moreText":"","excerptLength":24,"className":"south-card__excerpt"} /-->

<!-- wp:group {"className":"south-card__facts","layout":{"type":"flex","flexWrap":"nowrap","justifyContent":"space-between"}} -->
<div class="wp-block-group south-card__facts"><!-- wp:paragraph {"metadata":{"bindings":{"content":{"source":"south/property","args":{"key":"new"}}}},"className":"south-fact south-fact\u002d\u002dnew"} -->
<p class="south-fact south-fact--new"></p>
<!-- /wp:paragraph -->

<!-- wp:paragraph {"metadata":{"bindings":{"content":{"source":"south/property","args":{"key":"beds"}}}},"className":"south-fact south-fact\u002d\u002dbeds"} -->
<p class="south-fact south-fact--beds">2</p>
<!-- /wp:paragraph -->

<!-- wp:paragraph {"metadata":{"bindings":{"content":{"source":"south/property","args":{"key":"baths"}}}},"className":"south-fact south-fact\u002d\u002dbaths"} -->
<p class="south-fact south-fact--baths">2</p>
<!-- /wp:paragraph -->

<!-- wp:paragraph {"metadata":{"bindings":{"content":{"source":"south/property","args":{"key":"garages"}}}},"className":"south-fact south-fact\u002d\u002dgarages"} -->
<p class="south-fact south-fact--garages">1</p>
<!-- /wp:paragraph -->

<!-- wp:paragraph {"metadata":{"bindings":{"content":{"source":"south/property","args":{"key":"area"}}}},"className":"south-fact south-fact\u002d\u002darea"} -->
<p class="south-fact south-fact--area">1,200 sq ft</p>
<!-- /wp:paragraph --></div>
<!-- /wp:group --></div>
<!-- /wp:group --></div>
<!-- /wp:group -->
<!-- /wp:post-template -->

<!-- wp:query-no-results -->
<!-- wp:paragraph {"className":"south-empty"} -->
<p class="south-empty">No homes match that search yet. Try fewer filters, or call the office: new homes often sell before they are listed.</p>
<!-- /wp:paragraph -->
<!-- /wp:query-no-results --></div>
<!-- /wp:query --></div>
<!-- /wp:group -->
