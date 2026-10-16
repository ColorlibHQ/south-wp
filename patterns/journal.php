<?php
/**
 * Title: Journal: latest three posts
 * Slug: south/journal
 * Categories: south-sections
 * Keywords: blog, journal, posts, news
 * Description: The three newest journal posts, each with its photograph, date, title and a Read More button.
 *
 * @package South
 */

defined( 'ABSPATH' ) || exit;
?>
<!-- wp:group {"align":"full","className":"south-section south-journal-area","style":{"spacing":{"padding":{"top":"var:preset|spacing|70","bottom":"var:preset|spacing|70"}}},"layout":{"type":"constrained"}} -->
<div class="wp-block-group alignfull south-section south-journal-area" style="padding-top:var(--wp--preset--spacing--70);padding-bottom:var(--wp--preset--spacing--70)"><!-- wp:group {"className":"south-section-head","layout":{"type":"default"}} -->
<div class="wp-block-group south-section-head"><!-- wp:heading {"className":"south-section-title","style":{"typography":{"textAlign":"center"}}} -->
<h2 class="wp-block-heading has-text-align-center south-section-title">From the journal</h2>
<!-- /wp:heading -->

<!-- wp:paragraph {"className":"south-section-sub","style":{"typography":{"textAlign":"center"}}} -->
<p class="has-text-align-center south-section-sub">Notes on buying, renting and the neighbourhoods we know.</p>
<!-- /wp:paragraph --></div>
<!-- /wp:group -->

<!-- wp:query {"queryId":51,"query":{"perPage":3,"pages":0,"offset":0,"postType":"post","order":"desc","orderBy":"date","inherit":false},"className":"south-journal","layout":{"type":"default"}} -->
<div class="wp-block-query south-journal"><!-- wp:post-template {"layout":{"type":"grid","columnCount":3}} -->
<!-- wp:group {"className":"south-journal__item","layout":{"type":"default"}} -->
<div class="wp-block-group south-journal__item"><!-- wp:post-featured-image {"isLink":true,"aspectRatio":"730/346"} /-->

<!-- wp:post-date {"metadata":{"bindings":{"datetime":{"source":"core/post-data","args":{"field":"date"}}}},"className":"south-post__date"} /-->

<!-- wp:post-title {"level":3,"isLink":true,"className":"south-journal__title"} /-->

<!-- wp:post-excerpt {"moreText":"","excerptLength":22} /-->

<!-- wp:read-more {"content":"Read More","className":"south-button"} /--></div>
<!-- /wp:group -->
<!-- /wp:post-template -->

<!-- wp:query-no-results -->
<!-- wp:paragraph {"className":"south-empty"} -->
<p class="south-empty">The journal is empty for now.</p>
<!-- /wp:paragraph -->
<!-- /wp:query-no-results --></div>
<!-- /wp:query --></div>
<!-- /wp:group -->
