<?php
/**
 * Title: About the agency
 * Slug: south/about
 * Categories: south-sections
 * Keywords: about, agency, story
 * Description: The agency's story with a photograph, beside a slider of featured homes (shown when South Listings is active).
 *
 * @package South
 */

defined( 'ABSPATH' ) || exit;
?>
<!-- wp:group {"align":"full","className":"south-section south-about-area","style":{"spacing":{"padding":{"top":"var:preset|spacing|70","bottom":"var:preset|spacing|70"}}},"layout":{"type":"constrained"}} -->
<div class="wp-block-group alignfull south-section south-about-area" style="padding-top:var(--wp--preset--spacing--70);padding-bottom:var(--wp--preset--spacing--70)"><!-- wp:columns {"className":"south-about","style":{"spacing":{"blockGap":{"top":"var:preset|spacing|40","left":"var:preset|spacing|40"}}}} -->
<div class="wp-block-columns south-about"><!-- wp:column {"width":"65.77%"} -->
<div class="wp-block-column" style="flex-basis:65.77%"><!-- wp:group {"className":"south-section-head","layout":{"type":"default"}} -->
<div class="wp-block-group south-section-head"><!-- wp:heading {"className":"south-section-title","style":{"typography":{"textAlign":"left"}}} -->
<h2 class="wp-block-heading has-text-align-left south-section-title">We search for the perfect home</h2>
<!-- /wp:heading -->

<!-- wp:paragraph {"className":"south-section-sub","style":{"typography":{"textAlign":"left"}}} -->
<p class="has-text-align-left south-section-sub">An independent Los Angeles agency since 2009</p>
<!-- /wp:paragraph --></div>
<!-- /wp:group -->

<!-- wp:image {"sizeSlug":"large","linkDestination":"none","className":"south-about__photo"} -->
<figure class="wp-block-image size-large south-about__photo"><img src="<?php echo esc_url( get_theme_file_uri( 'assets/images/about.avif' ) ); ?>" alt="A white stairwell with oak treads, black railings and brass pendant lights"/></figure>
<!-- /wp:image -->

<!-- wp:paragraph -->
<p>South helps people buy, sell and rent homes across Los Angeles, from Malibu to Long Beach. We are three realtors and an office manager, and we keep it that way on purpose: every client talks to the same person from the first viewing to the day the keys change hands. We visit and photograph every home ourselves, write the descriptions from what we saw, and publish the real room sizes. Sellers get a free written valuation; buyers and tenants get honest notes about noise, light and parking before they book a viewing.</p>
<!-- /wp:paragraph --></div>
<!-- /wp:column -->

<!-- wp:column {"width":"31.53%","className":"south-requires-listings"} -->
<div class="wp-block-column south-requires-listings" style="flex-basis:31.53%"><!-- wp:group {"className":"south-section-head","layout":{"type":"default"}} -->
<div class="wp-block-group south-section-head"><!-- wp:heading {"className":"south-section-title","style":{"typography":{"textAlign":"left"}}} -->
<h2 class="wp-block-heading has-text-align-left south-section-title">Featured Properties</h2>
<!-- /wp:heading -->

<!-- wp:paragraph {"className":"south-section-sub","style":{"typography":{"textAlign":"left"}}} -->
<p class="has-text-align-left south-section-sub">New on the market this month</p>
<!-- /wp:paragraph --></div>
<!-- /wp:group -->

<!-- wp:query {"queryId":21,"query":{"perPage":4,"pages":0,"offset":0,"postType":"property","order":"desc","orderBy":"date","inherit":false,"southFeatured":true},"className":"south-properties south-slider south-properties\u002d\u002dsingle","layout":{"type":"default"}} -->
<div class="wp-block-query south-properties south-slider south-properties--single"><!-- wp:post-template {"className":"south-properties__grid","layout":{"type":"grid","columnCount":3}} -->
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
<!-- /wp:column --></div>
<!-- /wp:columns --></div>
<!-- /wp:group -->
