<?php
/**
 * The enquiry form: the contact page, and the agent box on every property.
 *
 * An agency's site exists to start a conversation, so South ships the form
 * rather than requiring a plugin for the one thing a visitor came to do.
 * Ported from Unioncorp's enquiry form.
 *
 * It is a **shortcode**, not inline PHP in a pattern: inc/front-page-setup.php
 * expands patterns into real post content so the copy stays editable, and PHP
 * inside stored content never runs, so an inline form would be frozen into
 * the page at activation.
 *
 *   [south_enquiry_form]                          the contact page
 *   [south_enquiry_form property="123"]           about a home; the South
 *                                                 Listings plugin routes it
 *                                                 to that home's agent
 *
 * A theme must not store data, so South keeps nothing. It validates, then
 * hands the enquiry to whoever wants it:
 *
 *   - `south_enquiry_handlers`  return true to say it has been dealt with,
 *                               and the built-in email is skipped (a CRM, a
 *                               helpdesk, a webhook).
 *   - `south_enquiry_email_to` / `_subject` / `_body`  adjust that email.
 *   - `south_enquiry_fields`    add, remove or relabel fields.
 *
 * It works with JavaScript off: a plain POST to the same URL, answered with a
 * redirect carrying the result (POST/redirect/GET). The return address is a
 * hidden field validated with wp_validate_redirect(): wp_get_referer() returns
 * false for a form that posts to its own page (Pato's lesson).
 *
 * @package South
 */

defined( 'ABSPATH' ) || exit;

const SOUTH_ENQUIRY_ACTION = 'south_enquiry';

/**
 * The fields the form asks for, as the design draws them: name, phone,
 * email, message. Labels are real <label for> elements, visually hidden
 * because the design shows the placeholder in the field.
 *
 * @return array<string, array<string, mixed>>
 */
function south_enquiry_fields() {
	$fields = array(
		'name'    => array(
			'label'        => __( 'Your name', 'south' ),
			'placeholder'  => __( 'Your Name', 'south' ),
			'type'         => 'text',
			'autocomplete' => 'name',
			'required'     => true,
		),
		'phone'   => array(
			'label'        => __( 'Your phone (optional)', 'south' ),
			'placeholder'  => __( 'Your Phone', 'south' ),
			'type'         => 'tel',
			'autocomplete' => 'tel',
			'required'     => false,
		),
		'email'   => array(
			'label'        => __( 'Your email', 'south' ),
			'placeholder'  => __( 'Your Email', 'south' ),
			'type'         => 'email',
			'autocomplete' => 'email',
			'required'     => true,
		),
		'message' => array(
			'label'       => __( 'Your message', 'south' ),
			'placeholder' => __( 'Your Message', 'south' ),
			'type'        => 'textarea',
			'required'    => true,
		),
	);

	/**
	 * Filters the enquiry form fields.
	 *
	 * @param array $fields Field definitions keyed by name.
	 */
	return apply_filters( 'south_enquiry_fields', $fields );
}

/**
 * Build an attribute string from a map, escaping every value.
 *
 * @param array $attributes Attribute map.
 * @return string
 */
function south_attributes( $attributes ) {
	$out = '';
	foreach ( $attributes as $key => $value ) {
		if ( '' === $value && 'value' !== $key ) {
			continue;
		}
		$out .= ' ' . esc_attr( $key ) . '="' . esc_attr( $value ) . '"';
	}
	return $out;
}

/**
 * Render one field, label included.
 *
 * @param string $name   Field name.
 * @param array  $field  Field definition.
 * @param string $prefix Id prefix, unique per form on the page.
 * @return string
 */
function south_enquiry_field( $name, $field, $prefix ) {
	$id       = $prefix . '-' . $name;
	$required = ! empty( $field['required'] );

	$attributes = array(
		'id'          => $id,
		'name'        => $name,
		'class'       => 'south-field__control',
		'placeholder' => isset( $field['placeholder'] ) ? $field['placeholder'] : '',
	);

	if ( $required ) {
		$attributes['required'] = 'required';
	}
	if ( ! empty( $field['autocomplete'] ) ) {
		$attributes['autocomplete'] = $field['autocomplete'];
	}

	$out  = '<p class="south-field south-field--' . esc_attr( $name ) . '">';
	$out .= '<label class="south-field__label screen-reader-text" for="' . esc_attr( $id ) . '">' . esc_html( $field['label'] );
	if ( $required ) {
		$out .= ' <span class="south-field__required" aria-hidden="true">*</span>';
	}
	$out .= '</label>';

	if ( 'textarea' === $field['type'] ) {
		$attributes['rows'] = 6;
		$out               .= '<textarea' . south_attributes( $attributes ) . '></textarea>';
	} else {
		$attributes['type'] = $field['type'];
		$out               .= '<input' . south_attributes( $attributes ) . '>';
	}

	return $out . '</p>';
}

/**
 * The enquiry form.
 *
 * @param array $atts Shortcode attributes: `button`, and `property` (a post ID)
 *                    when the form is about one home.
 * @return string
 */
function south_enquiry_form( $atts = array() ) {
	static $count = 0;
	++$count;

	$atts = shortcode_atts(
		array(
			'button'   => __( 'Send Message', 'south' ),
			'property' => '',
		),
		$atts,
		'south_enquiry_form'
	);

	$prefix   = 1 === $count ? 'south-enquiry' : 'south-enquiry-' . $count;
	$property = absint( $atts['property'] );
	$url      = south_current_url();

	$out  = '<form class="south-enquiry" method="post" action="' . esc_url( $url ) . '#' . esc_attr( $prefix ) . '" data-south-enquiry>';
	$out .= '<div id="' . esc_attr( $prefix ) . '" class="south-enquiry__anchor"></div>';
	$out .= south_enquiry_notice();
	$out .= wp_nonce_field( SOUTH_ENQUIRY_ACTION, 'south_enquiry_nonce', true, false );
	$out .= '<input type="hidden" name="action" value="' . esc_attr( SOUTH_ENQUIRY_ACTION ) . '">';
	$out .= '<input type="hidden" name="south_redirect" value="' . esc_url( $url ) . '">';
	$out .= '<input type="hidden" name="south_anchor" value="' . esc_attr( $prefix ) . '">';
	if ( $property ) {
		$out .= '<input type="hidden" name="south_property" value="' . esc_attr( $property ) . '">';
	}

	// A field nobody sees and nobody fills in; bots fill everything. Parked
	// off-screen by .south-enquiry__trap in assets/css/forms.css, out of the
	// tab order and hidden from assistive technology.
	$out .= '<p class="south-enquiry__trap" aria-hidden="true">';
	$out .= '<label for="' . esc_attr( $prefix ) . '-website">' . esc_html__( 'Leave this field empty', 'south' ) . '</label>';
	$out .= '<input id="' . esc_attr( $prefix ) . '-website" type="text" name="south_website" tabindex="-1" autocomplete="off">';
	$out .= '</p>';

	$out .= '<div class="south-enquiry__grid">';
	foreach ( south_enquiry_fields() as $name => $field ) {
		$out .= south_enquiry_field( $name, $field, $prefix );
	}
	$out .= '</div>';

	$out .= '<p class="south-enquiry__actions">';
	$out .= '<button type="submit" class="wp-element-button south-enquiry__submit" data-sending="' . esc_attr__( 'Sending…', 'south' ) . '">' . esc_html( $atts['button'] ) . '</button>';
	$out .= '</p>';

	return $out . '</form>';
}
add_shortcode( 'south_enquiry_form', 'south_enquiry_form' );

/**
 * The current URL, without a previous result parameter.
 *
 * @return string
 */
function south_current_url() {
	$permalink = is_singular() ? get_permalink() : '';
	if ( ! $permalink ) {
		$permalink = home_url( add_query_arg( array() ) );
	}
	return remove_query_arg( array( 'south-enquiry' ), $permalink );
}

/**
 * The message shown after a submission, if there is one.
 *
 * Success is `contrast` text with a `success`-like accent border rather than
 * green words: green text failed AA in most palettes in Unioncorp.
 *
 * @return string
 */
function south_enquiry_notice() {
	// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- display only.
	$result = isset( $_GET['south-enquiry'] ) ? sanitize_key( wp_unslash( $_GET['south-enquiry'] ) ) : '';

	$messages = array(
		'sent'    => array( 'ok', __( 'Thank you, your message is with us. We will reply by email or phone within one working day.', 'south' ) ),
		'invalid' => array( 'error', __( 'Please check the form: your name, email and a message are needed.', 'south' ) ),
		'email'   => array( 'error', __( 'That email address does not look right.', 'south' ) ),
		'failed'  => array( 'error', __( 'Sorry, the message could not be sent. Please call or email the office instead.', 'south' ) ),
		'expired' => array( 'error', __( 'That form had been open a while and expired. Please send it again.', 'south' ) ),
	);

	if ( ! isset( $messages[ $result ] ) ) {
		return '';
	}

	list( $kind, $text ) = $messages[ $result ];

	return '<p class="south-enquiry__notice is-' . esc_attr( $kind ) . '" role="' . ( 'ok' === $kind ? 'status' : 'alert' ) . '">' . esc_html( $text ) . '</p>';
}

/**
 * Handle a submitted enquiry, on template_redirect so it can redirect first.
 */
function south_handle_enquiry() {
	if ( 'POST' !== ( isset( $_SERVER['REQUEST_METHOD'] ) ? $_SERVER['REQUEST_METHOD'] : '' ) ) {
		return;
	}
	// phpcs:ignore WordPress.Security.NonceVerification.Missing -- checked below.
	if ( ! isset( $_POST['action'] ) || SOUTH_ENQUIRY_ACTION !== $_POST['action'] ) {
		return;
	}

	// phpcs:disable WordPress.Security.NonceVerification.Missing -- nonce checked below; these only choose where to return.
	$posted   = isset( $_POST['south_redirect'] ) ? esc_url_raw( wp_unslash( $_POST['south_redirect'] ) ) : '';
	$anchor   = isset( $_POST['south_anchor'] ) ? sanitize_html_class( wp_unslash( $_POST['south_anchor'] ) ) : 'south-enquiry';
	$redirect = remove_query_arg( array( 'south-enquiry' ), wp_validate_redirect( $posted, home_url( '/' ) ) );
	// phpcs:enable WordPress.Security.NonceVerification.Missing

	$nonce = isset( $_POST['south_enquiry_nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['south_enquiry_nonce'] ) ) : '';
	if ( ! wp_verify_nonce( $nonce, SOUTH_ENQUIRY_ACTION ) ) {
		south_enquiry_redirect( $redirect, 'expired', $anchor );
	}

	// Silently accept and discard anything that filled the honeypot.
	if ( ! empty( $_POST['south_website'] ) ) {
		south_enquiry_redirect( $redirect, 'sent', $anchor );
	}

	$enquiry = array();
	foreach ( south_enquiry_fields() as $name => $field ) {
		$raw = isset( $_POST[ $name ] ) ? wp_unslash( $_POST[ $name ] ) : ''; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- sanitised per type below.
		$raw = is_string( $raw ) ? $raw : '';

		if ( 'textarea' === $field['type'] ) {
			$value = sanitize_textarea_field( $raw );
		} elseif ( 'email' === $field['type'] ) {
			$value = sanitize_email( $raw );
		} else {
			$value = sanitize_text_field( $raw );
		}

		if ( ! empty( $field['required'] ) && '' === $value ) {
			south_enquiry_redirect( $redirect, 'invalid', $anchor );
		}

		$enquiry[ $name ] = $value;
	}

	if ( ! empty( $enquiry['email'] ) && ! is_email( $enquiry['email'] ) ) {
		south_enquiry_redirect( $redirect, 'email', $anchor );
	}

	$property = isset( $_POST['south_property'] ) ? absint( $_POST['south_property'] ) : 0;
	if ( $property && 'publish' === get_post_status( $property ) ) {
		$enquiry['property'] = $property;
	}

	/**
	 * Filters whether the enquiry has already been handled.
	 *
	 * @param bool  $handled Whether something has dealt with it.
	 * @param array $enquiry The sanitised enquiry; `property` is a post ID when it is about a home.
	 */
	$handled = apply_filters( 'south_enquiry_handlers', false, $enquiry );

	if ( ! $handled ) {
		$handled = south_enquiry_email( $enquiry );
	}

	south_enquiry_redirect( $redirect, $handled ? 'sent' : 'failed', $anchor );
}
add_action( 'template_redirect', 'south_handle_enquiry' );

/**
 * Redirect back to the form with a result, and stop.
 *
 * @param string $url    Where to go.
 * @param string $result Result key.
 * @param string $anchor The form's anchor.
 */
function south_enquiry_redirect( $url, $result, $anchor = 'south-enquiry' ) {
	wp_safe_redirect( add_query_arg( 'south-enquiry', $result, $url ) . '#' . $anchor, 303 );
	exit;
}

/**
 * Email the enquiry.
 *
 * From: is the site's own address, never the visitor's (SPF/DMARC, and header
 * injection); Reply-To carries the visitor.
 *
 * @param array $enquiry Sanitised enquiry.
 * @return bool
 */
function south_enquiry_email( $enquiry ) {
	/**
	 * Filters who receives the enquiry. South Listings answers with the
	 * property's agent when the enquiry is about a home.
	 *
	 * @param string $to      Recipient address.
	 * @param array  $enquiry The sanitised enquiry.
	 */
	$to = apply_filters( 'south_enquiry_email_to', get_option( 'admin_email' ), $enquiry );

	if ( ! $to || ! is_email( $to ) ) {
		return false;
	}

	$site = wp_specialchars_decode( get_bloginfo( 'name' ), ENT_QUOTES );
	if ( ! empty( $enquiry['property'] ) ) {
		/* translators: 1: site name, 2: property title. */
		$subject = sprintf( __( '[%1$s] Enquiry about %2$s', 'south' ), $site, wp_specialchars_decode( get_the_title( $enquiry['property'] ), ENT_QUOTES ) );
	} else {
		/* translators: %s: site name. */
		$subject = sprintf( __( '[%s] Website enquiry', 'south' ), $site );
	}
	$subject = apply_filters( 'south_enquiry_email_subject', $subject, $enquiry );

	$lines  = array();
	$fields = south_enquiry_fields();
	foreach ( $enquiry as $name => $value ) {
		if ( '' === $value || 'property' === $name ) {
			continue;
		}
		$label   = isset( $fields[ $name ]['label'] ) ? $fields[ $name ]['label'] : $name;
		$lines[] = $label . ': ' . $value;
	}
	if ( ! empty( $enquiry['property'] ) ) {
		$lines[] = __( 'Property', 'south' ) . ': ' . get_the_title( $enquiry['property'] ) . ' — ' . get_permalink( $enquiry['property'] );
	}

	$body = apply_filters( 'south_enquiry_email_body', implode( "\n", $lines ), $enquiry );

	$headers = array( 'Content-Type: text/plain; charset=UTF-8' );
	if ( ! empty( $enquiry['email'] ) && is_email( $enquiry['email'] ) ) {
		$headers[] = 'Reply-To: ' . $enquiry['email'];
	}

	return (bool) wp_mail( $to, $subject, $body, $headers );
}
