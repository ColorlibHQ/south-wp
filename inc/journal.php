<?php
/**
 * The journal's comments, as the design draws them: "3 Comments" over the
 * thread, and a reply form of Name, Email and Message with the labels
 * visually hidden behind placeholders and a "Submit Reply" button.
 *
 * @package South
 */

defined( 'ABSPATH' ) || exit;

/**
 * The comment form's wording and fields.
 *
 * @param array $defaults Comment form defaults.
 * @return array
 */
function south_comment_form_defaults( $defaults ) {
	$defaults['title_reply']          = __( 'Leave a reply', 'south' );
	$defaults['label_submit']         = __( 'Submit Reply', 'south' );
	$defaults['class_submit']         = 'submit wp-element-button';
	$defaults['comment_notes_before'] = '';
	$defaults['comment_field']        = '<p class="comment-form-comment"><label class="screen-reader-text" for="comment">' . esc_html__( 'Your reply', 'south' ) . '</label><textarea id="comment" name="comment" rows="8" maxlength="65525" required placeholder="' . esc_attr__( 'Message', 'south' ) . '"></textarea></p>';
	return $defaults;
}
add_filter( 'comment_form_defaults', 'south_comment_form_defaults' );

/**
 * Name and Email first, as in the design, each with its label hidden behind
 * a placeholder; no Website field.
 *
 * @param array $fields Default fields.
 * @return array
 */
function south_comment_form_fields( $fields ) {
	$commenter = wp_get_current_commenter();
	$out       = array(
		'author' => '<p class="comment-form-author"><label class="screen-reader-text" for="author">' . esc_html__( 'Name', 'south' ) . '</label><input id="author" name="author" type="text" value="' . esc_attr( $commenter['comment_author'] ) . '" maxlength="245" autocomplete="name" required placeholder="' . esc_attr__( 'Name', 'south' ) . '"></p>',
		'email'  => '<p class="comment-form-email"><label class="screen-reader-text" for="email">' . esc_html__( 'Email (not published)', 'south' ) . '</label><input id="email" name="email" type="email" value="' . esc_attr( $commenter['comment_author_email'] ) . '" maxlength="100" autocomplete="email" required placeholder="' . esc_attr__( 'Email', 'south' ) . '"></p>',
	);
	if ( isset( $fields['cookies'] ) ) {
		$out['cookies'] = $fields['cookies'];
	}
	return $out;
}
add_filter( 'comment_form_default_fields', 'south_comment_form_fields' );

/**
 * Fields before the message, as the design orders them.
 *
 * @param array $fields All fields, comment first.
 * @return array
 */
function south_comment_fields_order( $fields ) {
	if ( isset( $fields['comment'] ) ) {
		$comment = $fields['comment'];
		unset( $fields['comment'] );
		$cookies = isset( $fields['cookies'] ) ? $fields['cookies'] : null;
		unset( $fields['cookies'] );
		$fields['comment'] = $comment;
		if ( null !== $cookies ) {
			$fields['cookies'] = $cookies;
		}
	}
	return $fields;
}
add_filter( 'comment_form_fields', 'south_comment_fields_order' );

/**
 * "3 Comments" rather than "3 responses to …".
 *
 * @param string $content Rendered block.
 * @return string
 */
function south_comments_title( $content ) {
	$count = (int) get_comments_number();
	if ( ! $count ) {
		return $content;
	}
	/* translators: %s: number of comments. */
	$text = sprintf( _n( '%s Comment', '%s Comments', $count, 'south' ), number_format_i18n( $count ) );
	return (string) preg_replace( '#(<h[1-6][^>]*>).*?(</h[1-6]>)#s', '${1}' . esc_html( $text ) . '${2}', $content, 1 );
}
add_filter( 'render_block_core/comments-title', 'south_comments_title' );
