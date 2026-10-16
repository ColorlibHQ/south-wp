<?php
/**
 * Title: Call to action: rentals
 * Slug: south/rent-cta
 * Categories: south-sections
 * Keywords: call to action, rent, banner
 * Description: A fixed photograph under a dark veil with an uppercase question, an italic line and a button.
 *
 * @package South
 */

defined( 'ABSPATH' ) || exit;
?>
<!-- wp:cover {"url":"<?php echo esc_url( get_theme_file_uri( 'assets/images/cta.avif' ) ); ?>","hasParallax":true,"dimRatio":70,"overlayColor":"dark","isUserOverlayColor":true,"align":"full","className":"south-cta","layout":{"type":"constrained"}} -->
<div class="wp-block-cover alignfull has-parallax south-cta"><div class="wp-block-cover__image-background has-parallax" style="background-position:50% 50%;background-image:url(<?php echo esc_url( get_theme_file_uri( 'assets/images/cta.avif' ) ); ?>)"></div><span aria-hidden="true" class="wp-block-cover__background has-dark-background-color has-background-dim-70 has-background-dim"></span><div class="wp-block-cover__inner-container"><!-- wp:group {"layout":{"type":"constrained"}} -->
<div class="wp-block-group"><!-- wp:heading {"className":"south-cta__title","style":{"typography":{"textAlign":"center"}}} -->
<h2 class="wp-block-heading has-text-align-center south-cta__title">Are you looking for a place to rent?</h2>
<!-- /wp:heading -->

<!-- wp:paragraph {"className":"south-cta__lead","style":{"typography":{"textAlign":"center"}}} -->
<p class="has-text-align-center south-cta__lead">Furnished lofts, apartments and townhouses, ready to move into.</p>
<!-- /wp:paragraph -->

<!-- wp:buttons {"layout":{"type":"flex","justifyContent":"center"}} -->
<div class="wp-block-buttons"><!-- wp:button -->
<div class="wp-block-button"><a class="wp-block-button__link wp-element-button" href="<?php echo esc_url( home_url( '/listings/?status=rent' ) ); ?>">See rentals</a></div>
<!-- /wp:button --></div>
<!-- /wp:buttons --></div>
<!-- /wp:group --></div></div>
<!-- /wp:cover -->
