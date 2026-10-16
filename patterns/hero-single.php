<?php
/**
 * Title: Hero photograph with property search
 * Slug: south/hero-single
 * Categories: south-sections, south-listings
 * Keywords: hero, banner, search
 * Description: One full-height photograph with the title on a black band and the property search overlapping it.
 *
 * @package South
 */

defined( 'ABSPATH' ) || exit;
?>
<!-- wp:cover {"url":"<?php echo esc_url( get_theme_file_uri( 'assets/images/hero1.avif' ) ); ?>","dimRatio":0,"overlayColor":"dark","isUserOverlayColor":true,"align":"full","className":"south-hero south-hero__slide","layout":{"type":"constrained"}} -->
<div class="wp-block-cover alignfull south-hero south-hero__slide"><img class="wp-block-cover__image-background" alt="" src="<?php echo esc_url( get_theme_file_uri( 'assets/images/hero1.avif' ) ); ?>" data-object-fit="cover"/><span aria-hidden="true" class="wp-block-cover__background has-dark-background-color has-background-dim-0 has-background-dim"></span><div class="wp-block-cover__inner-container"><!-- wp:heading {"level":1,"className":"south-band","style":{"typography":{"textAlign":"center"}}} -->
<h1 class="wp-block-heading has-text-align-center south-band">Find your home</h1>
<!-- /wp:heading --></div></div>
<!-- /wp:cover -->

<!-- wp:group {"align":"full","className":"south-search-area","layout":{"type":"constrained"}} -->
<div class="wp-block-group alignfull south-search-area"><!-- wp:shortcode -->
[south_property_search]
<!-- /wp:shortcode --></div>
<!-- /wp:group -->
