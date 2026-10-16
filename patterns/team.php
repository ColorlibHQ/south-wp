<?php
/**
 * Title: Team
 * Slug: south/team
 * Categories: south-sections
 * Keywords: team, realtors, agents, staff
 * Description: Three realtors with portraits, direct numbers and email addresses.
 *
 * @package South
 */

defined( 'ABSPATH' ) || exit;
?>
<!-- wp:group {"align":"full","className":"south-section south-team-area","style":{"spacing":{"padding":{"top":"var:preset|spacing|70","bottom":"var:preset|spacing|70"}}},"layout":{"type":"constrained"},"anchor":"team"} -->
<div class="wp-block-group alignfull south-section south-team-area" id="team" style="padding-top:var(--wp--preset--spacing--70);padding-bottom:var(--wp--preset--spacing--70)"><!-- wp:group {"className":"south-section-head","layout":{"type":"default"}} -->
<div class="wp-block-group south-section-head"><!-- wp:heading {"className":"south-section-title","style":{"typography":{"textAlign":"center"}}} -->
<h2 class="wp-block-heading has-text-align-center south-section-title">Meet The Team</h2>
<!-- /wp:heading -->

<!-- wp:paragraph {"className":"south-section-sub","style":{"typography":{"textAlign":"center"}}} -->
<p class="has-text-align-center south-section-sub">Call your realtor directly</p>
<!-- /wp:paragraph --></div>
<!-- /wp:group -->

<!-- wp:columns {"className":"south-team","style":{"spacing":{"blockGap":{"top":"var:preset|spacing|40","left":"var:preset|spacing|40"}}}} -->
<div class="wp-block-columns south-team"><!-- wp:column {"className":"south-member"} -->
<div class="wp-block-column south-member"><!-- wp:image {"aspectRatio":"1/1","scale":"cover","sizeSlug":"large","linkDestination":"none","className":"south-member__photo"} -->
<figure class="wp-block-image size-large south-member__photo"><img src="<?php echo esc_url( get_theme_file_uri( 'assets/images/team1.avif' ) ); ?>" alt="Jeremy Scott, a bearded man in a dark suit and red tie" style="aspect-ratio:1/1;object-fit:cover"/></figure>
<!-- /wp:image -->

<!-- wp:group {"className":"south-member__info","layout":{"type":"default"}} -->
<div class="wp-block-group south-member__info"><!-- wp:paragraph {"placeholder":" ","className":"south-icon south-icon\u002d\u002daward"} -->
<p class="south-icon south-icon--award"></p>
<!-- /wp:paragraph -->

<!-- wp:heading {"level":3,"className":"south-member__name","style":{"typography":{"textAlign":"center"}}} -->
<h3 class="wp-block-heading has-text-align-center south-member__name">Jeremy Scott</h3>
<!-- /wp:heading -->

<!-- wp:paragraph {"className":"south-section-sub","style":{"typography":{"textAlign":"center"}}} -->
<p class="has-text-align-center south-section-sub">Realtor</p>
<!-- /wp:paragraph -->

<!-- wp:group {"className":"south-lines south-lines\u002d\u002dcenter","layout":{"type":"default"}} -->
<div class="wp-block-group south-lines south-lines--center"><!-- wp:paragraph {"className":"south-line south-line\u002d\u002dphone"} -->
<p class="south-line south-line--phone"><a href="tel:+13105550142">+1 (310) 555-0142</a></p>
<!-- /wp:paragraph -->

<!-- wp:paragraph {"className":"south-line south-line\u002d\u002dmail"} -->
<p class="south-line south-line--mail"><a href="mailto:jeremy@example.com">jeremy@example.com</a></p>
<!-- /wp:paragraph --></div>
<!-- /wp:group --></div>
<!-- /wp:group --></div>
<!-- /wp:column -->

<!-- wp:column {"className":"south-member"} -->
<div class="wp-block-column south-member"><!-- wp:image {"aspectRatio":"1/1","scale":"cover","sizeSlug":"large","linkDestination":"none","className":"south-member__photo"} -->
<figure class="wp-block-image size-large south-member__photo"><img src="<?php echo esc_url( get_theme_file_uri( 'assets/images/team2.avif' ) ); ?>" alt="Maria Williams, smiling in glasses and a pale blue jacket as she reads her phone" style="aspect-ratio:1/1;object-fit:cover"/></figure>
<!-- /wp:image -->

<!-- wp:group {"className":"south-member__info","layout":{"type":"default"}} -->
<div class="wp-block-group south-member__info"><!-- wp:paragraph {"placeholder":" ","className":"south-icon south-icon\u002d\u002daward"} -->
<p class="south-icon south-icon--award"></p>
<!-- /wp:paragraph -->

<!-- wp:heading {"level":3,"className":"south-member__name","style":{"typography":{"textAlign":"center"}}} -->
<h3 class="wp-block-heading has-text-align-center south-member__name">Maria Williams</h3>
<!-- /wp:heading -->

<!-- wp:paragraph {"className":"south-section-sub","style":{"typography":{"textAlign":"center"}}} -->
<p class="has-text-align-center south-section-sub">Realtor</p>
<!-- /wp:paragraph -->

<!-- wp:group {"className":"south-lines south-lines\u002d\u002dcenter","layout":{"type":"default"}} -->
<div class="wp-block-group south-lines south-lines--center"><!-- wp:paragraph {"className":"south-line south-line\u002d\u002dphone"} -->
<p class="south-line south-line--phone"><a href="tel:+13105550157">+1 (310) 555-0157</a></p>
<!-- /wp:paragraph -->

<!-- wp:paragraph {"className":"south-line south-line\u002d\u002dmail"} -->
<p class="south-line south-line--mail"><a href="mailto:maria@example.com">maria@example.com</a></p>
<!-- /wp:paragraph --></div>
<!-- /wp:group --></div>
<!-- /wp:group --></div>
<!-- /wp:column -->

<!-- wp:column {"className":"south-member"} -->
<div class="wp-block-column south-member"><!-- wp:image {"aspectRatio":"1/1","scale":"cover","sizeSlug":"large","linkDestination":"none","className":"south-member__photo"} -->
<figure class="wp-block-image size-large south-member__photo"><img src="<?php echo esc_url( get_theme_file_uri( 'assets/images/team3.avif' ) ); ?>" alt="Patrick Joe, smiling in a black suit and tie on a city street" style="aspect-ratio:1/1;object-fit:cover"/></figure>
<!-- /wp:image -->

<!-- wp:group {"className":"south-member__info","layout":{"type":"default"}} -->
<div class="wp-block-group south-member__info"><!-- wp:paragraph {"placeholder":" ","className":"south-icon south-icon\u002d\u002daward"} -->
<p class="south-icon south-icon--award"></p>
<!-- /wp:paragraph -->

<!-- wp:heading {"level":3,"className":"south-member__name","style":{"typography":{"textAlign":"center"}}} -->
<h3 class="wp-block-heading has-text-align-center south-member__name">Patrick Joe</h3>
<!-- /wp:heading -->

<!-- wp:paragraph {"className":"south-section-sub","style":{"typography":{"textAlign":"center"}}} -->
<p class="has-text-align-center south-section-sub">Realtor</p>
<!-- /wp:paragraph -->

<!-- wp:group {"className":"south-lines south-lines\u002d\u002dcenter","layout":{"type":"default"}} -->
<div class="wp-block-group south-lines south-lines--center"><!-- wp:paragraph {"className":"south-line south-line\u002d\u002dphone"} -->
<p class="south-line south-line--phone"><a href="tel:+13105550168">+1 (310) 555-0168</a></p>
<!-- /wp:paragraph -->

<!-- wp:paragraph {"className":"south-line south-line\u002d\u002dmail"} -->
<p class="south-line south-line--mail"><a href="mailto:patrick@example.com">patrick@example.com</a></p>
<!-- /wp:paragraph --></div>
<!-- /wp:group --></div>
<!-- /wp:group --></div>
<!-- /wp:column --></div>
<!-- /wp:columns --></div>
<!-- /wp:group -->
