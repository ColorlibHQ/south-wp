<?php
/**
 * Title: Testimonials
 * Slug: south/testimonials
 * Categories: south-sections
 * Keywords: testimonials, reviews, quotes
 * Description: Three client quotes, the middle one in focus; the arrows bring the next one to the centre.
 *
 * @package South
 */

defined( 'ABSPATH' ) || exit;
?>
<!-- wp:group {"align":"full","className":"south-section south-testimonials","style":{"spacing":{"padding":{"top":"var:preset|spacing|70","bottom":"var:preset|spacing|70"}}},"layout":{"type":"constrained"},"anchor":"testimonials"} -->
<div class="wp-block-group alignfull south-section south-testimonials" id="testimonials" style="padding-top:var(--wp--preset--spacing--70);padding-bottom:var(--wp--preset--spacing--70)"><!-- wp:group {"className":"south-section-head","layout":{"type":"default"}} -->
<div class="wp-block-group south-section-head"><!-- wp:heading {"className":"south-section-title","style":{"typography":{"textAlign":"center"}}} -->
<h2 class="wp-block-heading has-text-align-center south-section-title">Client testimonials</h2>
<!-- /wp:heading -->

<!-- wp:paragraph {"className":"south-section-sub","style":{"typography":{"textAlign":"center"}}} -->
<p class="has-text-align-center south-section-sub">What buyers and tenants said after they moved in.</p>
<!-- /wp:paragraph --></div>
<!-- /wp:group -->

<!-- wp:group {"className":"south-quotes south-slider\u002d\u002dcenter","layout":{"type":"default"}} -->
<div class="wp-block-group south-quotes south-slider--center"><!-- wp:columns {"className":"south-quotes__track","style":{"spacing":{"blockGap":{"top":"var:preset|spacing|40","left":"var:preset|spacing|40"}}}} -->
<div class="wp-block-columns south-quotes__track"><!-- wp:column -->
<div class="wp-block-column"><!-- wp:group {"className":"south-quote","layout":{"type":"default"}} -->
<div class="wp-block-group south-quote"><!-- wp:heading {"level":3,"className":"south-quote__title","style":{"typography":{"textAlign":"center"}}} -->
<h3 class="wp-block-heading has-text-align-center south-quote__title">A fair price, quickly</h3>
<!-- /wp:heading -->

<!-- wp:paragraph {"className":"south-quote__text","style":{"typography":{"textAlign":"center"}}} -->
<p class="has-text-align-center south-quote__text">We sold our townhouse through South. The valuation came with a clear written report, the photographs were excellent and the first open house brought three offers.</p>
<!-- /wp:paragraph -->

<!-- wp:image {"width":"50px","aspectRatio":"1/1","scale":"cover","sizeSlug":"large","linkDestination":"none","className":"south-quote__avatar","style":{"border":{"radius":"50%"}}} -->
<figure class="wp-block-image size-large is-resized has-custom-border south-quote__avatar"><img src="<?php echo esc_url( get_theme_file_uri( 'assets/images/client-3.avif' ) ); ?>" alt="" style="border-radius:50%;aspect-ratio:1/1;object-fit:cover;width:50px;height:auto"/></figure>
<!-- /wp:image -->

<!-- wp:paragraph {"className":"south-quote__author","style":{"typography":{"textAlign":"center"}}} -->
<p class="has-text-align-center south-quote__author">Grace Okafor, <span>Seller, Long Beach</span></p>
<!-- /wp:paragraph --></div>
<!-- /wp:group --></div>
<!-- /wp:column -->

<!-- wp:column -->
<div class="wp-block-column"><!-- wp:group {"className":"south-quote","layout":{"type":"default"}} -->
<div class="wp-block-group south-quote"><!-- wp:heading {"level":3,"className":"south-quote__title","style":{"typography":{"textAlign":"center"}}} -->
<h3 class="wp-block-heading has-text-align-center south-quote__title">Perfect Home for me</h3>
<!-- /wp:heading -->

<!-- wp:paragraph {"className":"south-quote__text","style":{"typography":{"textAlign":"center"}}} -->
<p class="has-text-align-center south-quote__text">We had looked at houses for a year. Maria listened to what we actually needed, showed us four places instead of forty, and the Tudor house in Pasadena was the second one. The paperwork was done in six weeks.</p>
<!-- /wp:paragraph -->

<!-- wp:image {"width":"50px","aspectRatio":"1/1","scale":"cover","sizeSlug":"large","linkDestination":"none","className":"south-quote__avatar","style":{"border":{"radius":"50%"}}} -->
<figure class="wp-block-image size-large is-resized has-custom-border south-quote__avatar"><img src="<?php echo esc_url( get_theme_file_uri( 'assets/images/client-1.avif' ) ); ?>" alt="" style="border-radius:50%;aspect-ratio:1/1;object-fit:cover;width:50px;height:auto"/></figure>
<!-- /wp:image -->

<!-- wp:paragraph {"className":"south-quote__author","style":{"typography":{"textAlign":"center"}}} -->
<p class="has-text-align-center south-quote__author">Daiane Smith, <span>Home buyer, Pasadena</span></p>
<!-- /wp:paragraph --></div>
<!-- /wp:group --></div>
<!-- /wp:column -->

<!-- wp:column -->
<div class="wp-block-column"><!-- wp:group {"className":"south-quote","layout":{"type":"default"}} -->
<div class="wp-block-group south-quote"><!-- wp:heading {"level":3,"className":"south-quote__title","style":{"typography":{"textAlign":"center"}}} -->
<h3 class="wp-block-heading has-text-align-center south-quote__title">Honest about every flat</h3>
<!-- /wp:heading -->

<!-- wp:paragraph {"className":"south-quote__text","style":{"typography":{"textAlign":"center"}}} -->
<p class="has-text-align-center south-quote__text">Jeremy told us which apartments were noisy before we went to see them, which saved us whole weekends. We rented the loft in Santa Monica and the move-in checklist covered everything.</p>
<!-- /wp:paragraph -->

<!-- wp:image {"width":"50px","aspectRatio":"1/1","scale":"cover","sizeSlug":"large","linkDestination":"none","className":"south-quote__avatar","style":{"border":{"radius":"50%"}}} -->
<figure class="wp-block-image size-large is-resized has-custom-border south-quote__avatar"><img src="<?php echo esc_url( get_theme_file_uri( 'assets/images/client-2.avif' ) ); ?>" alt="" style="border-radius:50%;aspect-ratio:1/1;object-fit:cover;width:50px;height:auto"/></figure>
<!-- /wp:image -->

<!-- wp:paragraph {"className":"south-quote__author","style":{"typography":{"textAlign":"center"}}} -->
<p class="has-text-align-center south-quote__author">Tom Alvarez, <span>Tenant, Santa Monica</span></p>
<!-- /wp:paragraph --></div>
<!-- /wp:group --></div>
<!-- /wp:column --></div>
<!-- /wp:columns --></div>
<!-- /wp:group --></div>
<!-- /wp:group -->
