<?php
/**
 * Title: Contact: office hours and form
 * Slug: south/contact
 * Categories: south-sections
 * Keywords: contact, form, hours, office
 * Description: Office hours, phone, email and address beside the enquiry form.
 *
 * @package South
 */

defined( 'ABSPATH' ) || exit;
?>
<!-- wp:group {"align":"full","className":"south-section south-contact-area","style":{"spacing":{"padding":{"top":"var:preset|spacing|70","bottom":"var:preset|spacing|70"}}},"layout":{"type":"constrained"}} -->
<div class="wp-block-group alignfull south-section south-contact-area" style="padding-top:var(--wp--preset--spacing--70);padding-bottom:var(--wp--preset--spacing--70)"><!-- wp:heading {"className":"south-underline-title"} -->
<h2 class="wp-block-heading south-underline-title">Contact info</h2>
<!-- /wp:heading -->

<!-- wp:columns {"className":"south-contact","style":{"spacing":{"blockGap":{"top":"var:preset|spacing|40","left":"var:preset|spacing|40"}}}} -->
<div class="wp-block-columns south-contact"><!-- wp:column {"width":"31.53%"} -->
<div class="wp-block-column" style="flex-basis:31.53%"><!-- wp:group {"className":"south-office","layout":{"type":"default"},"anchor":"office-hours"} -->
<div id="office-hours" class="wp-block-group south-office"><!-- wp:group {"className":"south-hours","layout":{"type":"default"}} -->
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
<!-- /wp:group --></div>
<!-- /wp:column -->

<!-- wp:column {"width":"65.77%"} -->
<div class="wp-block-column" style="flex-basis:65.77%"><!-- wp:shortcode -->
[south_enquiry_form]
<!-- /wp:shortcode --></div>
<!-- /wp:column --></div>
<!-- /wp:columns --></div>
<!-- /wp:group -->
