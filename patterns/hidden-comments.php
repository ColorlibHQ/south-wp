<?php
/**
 * Title: Comments
 * Slug: south/hidden-comments
 * Description: The comments and the reply form under a single post.
 * Inserter: no
 *
 * @package South
 */

defined( 'ABSPATH' ) || exit;
?>
<!-- wp:comments {"className":"south-comments"} -->
<div class="wp-block-comments south-comments"><!-- wp:comments-title /-->

<!-- wp:comment-template -->
<!-- wp:group {"className":"south-comment","layout":{"type":"flex","flexWrap":"nowrap","verticalAlignment":"top"}} -->
<div class="wp-block-group south-comment"><!-- wp:avatar {"size":60,"className":"south-comment__avatar"} /-->

<!-- wp:group {"className":"south-comment__body","layout":{"type":"default"}} -->
<div class="wp-block-group south-comment__body"><!-- wp:group {"className":"south-comment__meta","layout":{"type":"flex","flexWrap":"wrap"}} -->
<div class="wp-block-group south-comment__meta"><!-- wp:comment-author-name {"className":"south-comment__name"} /-->

<!-- wp:comment-date {"format":"M j, Y","className":"south-comment__date"} /-->

<!-- wp:comment-reply-link {"className":"south-comment__reply"} /--></div>
<!-- /wp:group -->

<!-- wp:comment-content {"className":"south-comment__content"} /--></div>
<!-- /wp:group --></div>
<!-- /wp:group -->
<!-- /wp:comment-template -->

<!-- wp:comments-pagination {"layout":{"type":"flex","justifyContent":"left"}} -->
<!-- wp:comments-pagination-numbers /-->
<!-- /wp:comments-pagination -->

<!-- wp:post-comments-form {"className":"south-comment-form"} /--></div>
<!-- /wp:comments -->
