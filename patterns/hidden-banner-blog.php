<?php
/**
 * Title: Journal banner
 * Slug: south/hidden-banner-blog
 * Description: The heading of the posts page.
 * Inserter: no
 *
 * @package South
 */

defined( 'ABSPATH' ) || exit;
?>
<!-- wp:cover {"url":"<?php echo esc_url( get_theme_file_uri( 'assets/images/hero1.avif' ) ); ?>","dimRatio":0,"overlayColor":"dark","isUserOverlayColor":true,"align":"full","className":"south-banner","layout":{"type":"constrained"}} -->
<div class="wp-block-cover alignfull south-banner"><img class="wp-block-cover__image-background" alt="" src="<?php echo esc_url( get_theme_file_uri( 'assets/images/hero1.avif' ) ); ?>" data-object-fit="cover"/><span aria-hidden="true" class="wp-block-cover__background has-dark-background-color has-background-dim-0 has-background-dim"></span><div class="wp-block-cover__inner-container"><!-- wp:heading {"level":1,"className":"south-band","style":{"typography":{"textAlign":"center"}}} -->
<h1 class="wp-block-heading has-text-align-center south-band">Blog</h1>
<!-- /wp:heading --></div></div>
<!-- /wp:cover -->
