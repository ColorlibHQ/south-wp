<?php
/**
 * Title: Not found content
 * Slug: south/hidden-404
 * Description: What a visitor sees when nothing is at the address.
 * Inserter: no
 *
 * @package South
 */

defined( 'ABSPATH' ) || exit;
?>
<!-- wp:group {"align":"full","className":"south-section south-404","style":{"spacing":{"padding":{"top":"var:preset|spacing|70","bottom":"var:preset|spacing|70"}}},"layout":{"type":"constrained"}} -->
<div class="wp-block-group alignfull south-section south-404" style="padding-top:var(--wp--preset--spacing--70);padding-bottom:var(--wp--preset--spacing--70)"><!-- wp:group {"layout":{"type":"constrained","contentSize":"620px"}} -->
<div class="wp-block-group"><!-- wp:heading {"className":"south-section-title","style":{"typography":{"textAlign":"center"}}} -->
<h2 class="wp-block-heading has-text-align-center south-section-title">That page has moved</h2>
<!-- /wp:heading -->

<!-- wp:paragraph {"style":{"typography":{"textAlign":"center"}}} -->
<p class="has-text-align-center">The address may be old, or the home may have been sold and its page taken down. Search the site, or start again from the home page.</p>
<!-- /wp:paragraph -->

<!-- wp:search {"label":"Search","showLabel":false,"placeholder":"Search the site","buttonText":"Search","buttonPosition":"button-inside","buttonUseIcon":true,"className":"south-sidebar-search"} /-->

<!-- wp:buttons {"layout":{"type":"flex","justifyContent":"center"}} -->
<div class="wp-block-buttons"><!-- wp:button -->
<div class="wp-block-button"><a class="wp-block-button__link wp-element-button" href="<?php echo esc_url( home_url( '/' ) ); ?>">Back to the home page</a></div>
<!-- /wp:button --></div>
<!-- /wp:buttons --></div>
<!-- /wp:group --></div>
<!-- /wp:group -->
