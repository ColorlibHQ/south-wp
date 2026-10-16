<?php
/**
 * Title: Post meta
 * Slug: south/hidden-post-meta
 * Description: Author and categories under a post title.
 * Inserter: no
 *
 * @package South
 */

defined( 'ABSPATH' ) || exit;
?>
<!-- wp:group {"className":"south-post__meta","layout":{"type":"flex","flexWrap":"wrap"}} -->
<div class="wp-block-group south-post__meta"><!-- wp:post-author {"showAvatar":false,"byline":"By","isLink":true,"className":"south-post__author"} /-->

<!-- wp:post-terms {"term":"category","prefix":"in ","className":"south-post__terms"} /--></div>
<!-- /wp:group -->
