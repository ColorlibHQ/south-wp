<?php
/**
 * Title: Header
 * Slug: south/header
 * Keywords: header, navigation
 * Block Types: core/template-part/header
 * Description: The black top bar with the office email and phone, then the logo, menu, search and the dark mode switch over the first photograph.
 *
 * @package South
 */

defined( 'ABSPATH' ) || exit;
?>
<!-- wp:group {"className":"south-header","layout":{"type":"default"}} -->
<div class="wp-block-group south-header"><!-- wp:group {"className":"south-topbar","layout":{"type":"flex","flexWrap":"nowrap","justifyContent":"space-between"}} -->
<div class="wp-block-group south-topbar"><!-- wp:paragraph {"className":"south-topbar__email"} -->
<p class="south-topbar__email"><a href="mailto:office@example.com">office@example.com</a></p>
<!-- /wp:paragraph -->

<!-- wp:group {"className":"south-topbar__phone","layout":{"type":"flex","flexWrap":"nowrap"}} -->
<div class="wp-block-group south-topbar__phone"><!-- wp:paragraph {"placeholder":" ","className":"south-topbar__icon"} -->
<p class="south-topbar__icon"></p>
<!-- /wp:paragraph -->

<!-- wp:paragraph {"className":"south-topbar__number"} -->
<p class="south-topbar__number"><a href="tel:+13105550140">+1 (310) 555-0140</a></p>
<!-- /wp:paragraph --></div>
<!-- /wp:group --></div>
<!-- /wp:group -->

<!-- wp:group {"className":"south-navbar","layout":{"type":"flex","flexWrap":"nowrap","justifyContent":"space-between"}} -->
<div class="wp-block-group south-navbar"><!-- wp:group {"className":"south-brand","layout":{"type":"flex","flexWrap":"nowrap"}} -->
<div class="wp-block-group south-brand"><!-- wp:site-logo {"width":136,"className":"south-logo"} /-->

<!-- wp:site-title {"level":0,"className":"south-wordmark"} /--></div>
<!-- /wp:group -->

<!-- wp:group {"className":"south-navbar__end","layout":{"type":"flex","flexWrap":"nowrap"}} -->
<div class="wp-block-group south-navbar__end"><!-- wp:navigation {"overlayBackgroundColor":"dark","overlayTextColor":"overlay","className":"south-nav","layout":{"type":"flex","justifyContent":"right","flexWrap":"nowrap"}} /-->

<!-- wp:search {"label":"Search","showLabel":false,"placeholder":"Search properties ...","buttonText":"Search","buttonPosition":"button-only","buttonUseIcon":true,"className":"south-header-search"} /-->

<!-- wp:buttons -->
<div class="wp-block-buttons"><!-- wp:button {"className":"south-scheme-toggle"} -->
<div class="wp-block-button south-scheme-toggle"><a class="wp-block-button__link wp-element-button" href="#"><span class="screen-reader-text">Switch between light and dark mode</span></a></div>
<!-- /wp:button --></div>
<!-- /wp:buttons --></div>
<!-- /wp:group --></div>
<!-- /wp:group --></div>
<!-- /wp:group -->
