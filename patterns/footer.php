<?php
/**
 * Title: Footer
 * Slug: south/footer
 * Keywords: footer
 * Block Types: core/template-part/footer
 * Description: Four columns on a darkened photograph: about, office hours, links and featured homes, with a copyright line.
 *
 * @package South
 */

defined( 'ABSPATH' ) || exit;
?>
<!-- wp:cover {"url":"<?php echo esc_url( get_theme_file_uri( 'assets/images/cta.avif' ) ); ?>","customGradient":"linear-gradient(180deg,rgb(0,0,0) 0%,rgba(0,0,0,0.9) 90%,rgba(0,0,0,0) 100%)","align":"full","className":"south-footer","layout":{"type":"constrained"}} -->
<div class="wp-block-cover alignfull south-footer"><img class="wp-block-cover__image-background" alt="" src="<?php echo esc_url( get_theme_file_uri( 'assets/images/cta.avif' ) ); ?>" data-object-fit="cover"/><span aria-hidden="true" class="wp-block-cover__background has-background-dim-100 has-background-dim wp-block-cover__gradient-background has-background-gradient" style="background:linear-gradient(180deg,rgb(0,0,0) 0%,rgba(0,0,0,0.9) 90%,rgba(0,0,0,0) 100%)"></span><div class="wp-block-cover__inner-container"><!-- wp:group {"className":"south-footer__main","layout":{"type":"constrained"}} -->
<div class="wp-block-group south-footer__main"><!-- wp:columns {"className":"south-footer__columns","style":{"spacing":{"blockGap":{"top":"var:preset|spacing|40","left":"var:preset|spacing|40"}}}} -->
<div class="wp-block-columns south-footer__columns"><!-- wp:column -->
<div class="wp-block-column"><!-- wp:heading {"className":"south-widget-title"} -->
<h2 class="wp-block-heading south-widget-title">About Us</h2>
<!-- /wp:heading -->

<!-- wp:image {"aspectRatio":"792/471","scale":"cover","sizeSlug":"large","linkDestination":"none","className":"south-footer__photo"} -->
<figure class="wp-block-image size-large south-footer__photo"><img src="<?php echo esc_url( get_theme_file_uri( 'assets/images/footer.avif' ) ); ?>" alt="A low glass house lit up at dusk beside a rock pool, with trees behind" style="aspect-ratio:792/471;object-fit:cover"/></figure>
<!-- /wp:image -->

<!-- wp:site-title {"level":0,"className":"south-wordmark south-wordmark\u002d\u002dfooter"} /-->

<!-- wp:paragraph -->
<p>South is a small independent agency selling and letting homes across Los Angeles since 2009. Valuations are free and come with a written report.</p>
<!-- /wp:paragraph --></div>
<!-- /wp:column -->

<!-- wp:column -->
<div class="wp-block-column"><!-- wp:heading {"className":"south-widget-title"} -->
<h2 class="wp-block-heading south-widget-title">Hours</h2>
<!-- /wp:heading -->

<!-- wp:group {"className":"south-hours","layout":{"type":"default"}} -->
<div class="wp-block-group south-hours"><!-- wp:group {"className":"south-hours__row","layout":{"type":"flex","flexWrap":"nowrap","justifyContent":"space-between"}} -->
<div class="wp-block-group south-hours__row"><!-- wp:paragraph -->
<p>Monday - Friday</p>
<!-- /wp:paragraph -->

<!-- wp:paragraph -->
<p>9 AM - 7 PM</p>
<!-- /wp:paragraph --></div>
<!-- /wp:group -->

<!-- wp:group {"className":"south-hours__row","layout":{"type":"flex","flexWrap":"nowrap","justifyContent":"space-between"}} -->
<div class="wp-block-group south-hours__row"><!-- wp:paragraph -->
<p>Saturday</p>
<!-- /wp:paragraph -->

<!-- wp:paragraph -->
<p>9 AM - 2 PM</p>
<!-- /wp:paragraph --></div>
<!-- /wp:group -->

<!-- wp:group {"className":"south-hours__row","layout":{"type":"flex","flexWrap":"nowrap","justifyContent":"space-between"}} -->
<div class="wp-block-group south-hours__row"><!-- wp:paragraph -->
<p>Sunday</p>
<!-- /wp:paragraph -->

<!-- wp:paragraph -->
<p>Closed</p>
<!-- /wp:paragraph --></div>
<!-- /wp:group --></div>
<!-- /wp:group -->

<!-- wp:group {"className":"south-lines","layout":{"type":"default"}} -->
<div class="wp-block-group south-lines"><!-- wp:paragraph {"className":"south-line south-line\u002d\u002dphone"} -->
<p class="south-line south-line--phone"><a href="tel:+13105550140">+1 (310) 555-0140</a></p>
<!-- /wp:paragraph -->

<!-- wp:paragraph {"className":"south-line south-line\u002d\u002dmail"} -->
<p class="south-line south-line--mail"><a href="mailto:office@example.com">office@example.com</a></p>
<!-- /wp:paragraph -->

<!-- wp:paragraph {"className":"south-line south-line\u002d\u002dpin"} -->
<p class="south-line south-line--pin">Main Str. no 45-46, b3, Los Angeles, CA 90012</p>
<!-- /wp:paragraph --></div>
<!-- /wp:group --></div>
<!-- /wp:column -->

<!-- wp:column -->
<div class="wp-block-column"><!-- wp:heading {"className":"south-widget-title"} -->
<h2 class="wp-block-heading south-widget-title">Useful Links</h2>
<!-- /wp:heading -->

<!-- wp:list {"className":"south-links"} -->
<ul class="wp-block-list south-links"><!-- wp:list-item -->
<li><a href="<?php echo esc_url( home_url( '/' ) ); ?>">Home</a></li>
<!-- /wp:list-item -->

<!-- wp:list-item -->
<li><a href="<?php echo esc_url( home_url( '/about/' ) ); ?>">About us</a></li>
<!-- /wp:list-item -->

<!-- wp:list-item -->
<li><a href="<?php echo esc_url( home_url( '/about/#team' ) ); ?>">Our realtors</a></li>
<!-- /wp:list-item -->

<!-- wp:list-item -->
<li><a href="<?php echo esc_url( home_url( '/listings/' ) ); ?>">Properties</a></li>
<!-- /wp:list-item -->

<!-- wp:list-item -->
<li><a href="<?php echo esc_url( home_url( '/listings/?status=sale' ) ); ?>">For sale</a></li>
<!-- /wp:list-item -->

<!-- wp:list-item -->
<li><a href="<?php echo esc_url( home_url( '/listings/?status=rent' ) ); ?>">For rent</a></li>
<!-- /wp:list-item -->

<!-- wp:list-item -->
<li><a href="<?php echo esc_url( home_url( '/#testimonials' ) ); ?>">Testimonials</a></li>
<!-- /wp:list-item -->

<!-- wp:list-item -->
<li><a href="<?php echo esc_url( home_url( '/blog/' ) ); ?>">Journal</a></li>
<!-- /wp:list-item -->

<!-- wp:list-item -->
<li><a href="<?php echo esc_url( home_url( '/contact/' ) ); ?>">Contact</a></li>
<!-- /wp:list-item -->

<!-- wp:list-item -->
<li><a href="<?php echo esc_url( home_url( '/contact/#office-hours' ) ); ?>">Office hours</a></li>
<!-- /wp:list-item --></ul>
<!-- /wp:list --></div>
<!-- /wp:column -->

<!-- wp:column -->
<div class="wp-block-column"><!-- wp:heading {"className":"south-widget-title"} -->
<h2 class="wp-block-heading south-widget-title">Featured Properties</h2>
<!-- /wp:heading -->

<!-- wp:query {"queryId":31,"query":{"perPage":3,"pages":0,"offset":0,"postType":"property","order":"desc","orderBy":"date","inherit":false,"southFeatured":true},"className":"south-footer-homes south-requires-listings","layout":{"type":"default"}} -->
<div class="wp-block-query south-footer-homes south-requires-listings"><!-- wp:post-template {"className":"south-slider__track"} -->
<!-- wp:post-featured-image {"isLink":true,"aspectRatio":"258/333","className":"south-footer-homes__image"} /-->
<!-- /wp:post-template --></div>
<!-- /wp:query -->

<!-- wp:query {"queryId":32,"query":{"perPage":1,"pages":0,"offset":0,"postType":"post","order":"desc","orderBy":"date","inherit":false},"className":"south-footer-homes south-without-listings","layout":{"type":"default"}} -->
<div class="wp-block-query south-footer-homes south-without-listings"><!-- wp:post-template -->
<!-- wp:post-featured-image {"isLink":true,"aspectRatio":"258/333","className":"south-footer-homes__image"} /-->
<!-- /wp:post-template --></div>
<!-- /wp:query --></div>
<!-- /wp:column --></div>
<!-- /wp:columns --></div>
<!-- /wp:group -->

<!-- wp:group {"align":"full","className":"south-copyright","layout":{"type":"constrained"}} -->
<div class="wp-block-group alignfull south-copyright"><!-- wp:paragraph {"style":{"typography":{"textAlign":"center"}}} -->
<p class="has-text-align-center">Copyright ©<?php echo esc_html( gmdate( 'Y' ) ); ?> All rights reserved | This theme is made with <span aria-hidden="true">♡</span><span class="screen-reader-text">love</span> by <a href="https://colorlib.com" rel="nofollow">Colorlib</a></p>
<!-- /wp:paragraph --></div>
<!-- /wp:group --></div></div>
<!-- /wp:cover -->
