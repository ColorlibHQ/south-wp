<?php
/**
 * Title: Realtor's note
 * Slug: south/realtor-note
 * Categories: south-sections
 * Keywords: about, founder, realtor, editor
 * Description: The founder's note on a pale band, with his number, email and signature beside his portrait.
 *
 * @package South
 */

defined( 'ABSPATH' ) || exit;
?>
<!-- wp:group {"align":"full","className":"south-editor-area","backgroundColor":"surface","layout":{"type":"default"}} -->
<div class="wp-block-group alignfull south-editor-area has-surface-background-color has-background"><!-- wp:columns {"align":"full","className":"south-editor"} -->
<div class="wp-block-columns alignfull south-editor"><!-- wp:column {"width":"50%","className":"south-editor__text"} -->
<div class="wp-block-column south-editor__text" style="flex-basis:50%"><!-- wp:paragraph {"placeholder":" ","className":"south-icon south-icon\u002d\u002daward"} -->
<p class="south-icon south-icon--award"></p>
<!-- /wp:paragraph -->

<!-- wp:group {"className":"south-section-head","layout":{"type":"default"}} -->
<div class="wp-block-group south-section-head"><!-- wp:heading {"className":"south-section-title","style":{"typography":{"textAlign":"left"}}} -->
<h2 class="wp-block-heading has-text-align-left south-section-title">Jeremy Scott</h2>
<!-- /wp:heading -->

<!-- wp:paragraph {"className":"south-section-sub","style":{"typography":{"textAlign":"left"}}} -->
<p class="has-text-align-left south-section-sub">Realtor</p>
<!-- /wp:paragraph --></div>
<!-- /wp:group -->

<!-- wp:paragraph -->
<p>I started South in 2009 with one desk and a borrowed car. Fifteen years later we are a team of three realtors, and we still work the same way: we visit every home before we list it, we tell buyers what we would want to know ourselves, and we answer the phone on Saturdays. If you are thinking of buying, selling or renting in Los Angeles, call me directly or stop by the office for a coffee.</p>
<!-- /wp:paragraph -->

<!-- wp:group {"className":"south-lines","layout":{"type":"default"}} -->
<div class="wp-block-group south-lines"><!-- wp:paragraph {"className":"south-line south-line\u002d\u002dphone"} -->
<p class="south-line south-line--phone"><a href="tel:+13105550142">+1 (310) 555-0142</a></p>
<!-- /wp:paragraph -->

<!-- wp:paragraph {"className":"south-line south-line\u002d\u002dmail"} -->
<p class="south-line south-line--mail"><a href="mailto:jeremy@example.com">jeremy@example.com</a></p>
<!-- /wp:paragraph --></div>
<!-- /wp:group -->

<!-- wp:image {"width":"126px","sizeSlug":"large","linkDestination":"none","className":"south-signature"} -->
<figure class="wp-block-image size-large is-resized south-signature"><img src="<?php echo esc_url( get_theme_file_uri( 'assets/images/signature.svg' ) ); ?>" alt="Jeremy Scott's signature" style="width:126px;height:auto"/></figure>
<!-- /wp:image --></div>
<!-- /wp:column -->

<!-- wp:column {"width":"50%","className":"south-editor__media"} -->
<div class="wp-block-column south-editor__media" style="flex-basis:50%"><!-- wp:image {"sizeSlug":"large","linkDestination":"none","className":"south-editor__photo"} -->
<figure class="wp-block-image size-large south-editor__photo"><img src="<?php echo esc_url( get_theme_file_uri( 'assets/images/editor.avif' ) ); ?>" alt="Jeremy Scott, a bearded man in a dark suit and red tie, straightening his jacket"/></figure>
<!-- /wp:image --></div>
<!-- /wp:column --></div>
<!-- /wp:columns --></div>
<!-- /wp:group -->
