<?php
/**
 * Title: Post banner
 * Slug: south/hidden-banner-post
 * Description: The banner over a single post; the post title below is the page's heading.
 * Inserter: no
 *
 * @package South
 */

defined( 'ABSPATH' ) || exit;
?>
<!-- wp:cover {"url":"<?php echo esc_url( get_theme_file_uri( 'assets/images/hero1.avif' ) ); ?>","dimRatio":0,"overlayColor":"dark","isUserOverlayColor":true,"align":"full","className":"south-banner","layout":{"type":"constrained"}} -->
<div class="wp-block-cover alignfull south-banner"><img class="wp-block-cover__image-background" alt="" src="<?php echo esc_url( get_theme_file_uri( 'assets/images/hero1.avif' ) ); ?>" data-object-fit="cover"/><span aria-hidden="true" class="wp-block-cover__background has-dark-background-color has-background-dim-0 has-background-dim"></span><div class="wp-block-cover__inner-container"><!-- wp:paragraph {"className":"south-band","style":{"typography":{"textAlign":"center"}}} -->
<p class="has-text-align-center south-band">Blog Details</p>
<!-- /wp:paragraph --></div></div>
<!-- /wp:cover -->
