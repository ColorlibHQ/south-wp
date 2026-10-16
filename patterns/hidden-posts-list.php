<?php
/**
 * Title: Journal list
 * Slug: south/hidden-posts-list
 * Description: The post list used by the journal, archives and search.
 * Inserter: no
 *
 * @package South
 */

defined( 'ABSPATH' ) || exit;
?>
<!-- wp:query {"queryId":0,"query":{"perPage":6,"pages":0,"offset":0,"postType":"post","order":"desc","orderBy":"date","inherit":true},"className":"south-posts","layout":{"type":"default"}} -->
<div class="wp-block-query south-posts"><!-- wp:post-template -->
<!-- wp:group {"className":"south-post","layout":{"type":"default"}} -->
<div class="wp-block-group south-post"><!-- wp:post-featured-image {"isLink":true,"aspectRatio":"730/346","className":"south-post__image"} /-->

<!-- wp:group {"className":"south-post__body","layout":{"type":"default"}} -->
<div class="wp-block-group south-post__body"><!-- wp:post-date {"isLink":true,"metadata":{"bindings":{"datetime":{"source":"core/post-data","args":{"field":"date"}}}},"className":"south-post__date"} /-->

<!-- wp:post-title {"isLink":true,"className":"south-post__title"} /-->

<!-- wp:group {"className":"south-post__meta","layout":{"type":"flex","flexWrap":"wrap"}} -->
<div class="wp-block-group south-post__meta"><!-- wp:post-author {"showAvatar":false,"byline":"By","isLink":true,"className":"south-post__author"} /-->

<!-- wp:post-terms {"term":"category","prefix":"in ","className":"south-post__terms"} /--></div>
<!-- /wp:group -->

<!-- wp:post-excerpt {"moreText":"","excerptLength":30,"className":"south-post__excerpt"} /-->

<!-- wp:read-more {"content":"Read More","className":"south-button"} /--></div>
<!-- /wp:group --></div>
<!-- /wp:group -->
<!-- /wp:post-template -->

<!-- wp:query-pagination {"showLabel":false,"className":"south-pagination","layout":{"type":"flex","justifyContent":"left"}} -->
<!-- wp:query-pagination-numbers /-->
<!-- /wp:query-pagination -->

<!-- wp:query-no-results -->
<!-- wp:paragraph {"className":"south-empty"} -->
<p class="south-empty">Nothing here yet. Try a search, or start again from the home page.</p>
<!-- /wp:paragraph -->
<!-- /wp:query-no-results --></div>
<!-- /wp:query -->
