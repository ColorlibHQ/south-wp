<?php
/**
 * The edit screens: a Property details box and an Agent box, plus list
 * columns. Fields are also editable through the REST API (and so through the
 * editor's own data store), because every one is registered with
 * show_in_rest; this box is the human way in.
 *
 * @package South_Listings
 */

defined( 'ABSPATH' ) || exit;

/**
 * Register the boxes.
 */
function south_listings_meta_boxes() {
	add_meta_box( 'south-listings-details', __( 'Property details', 'south-listings' ), 'south_listings_details_box', 'property', 'normal', 'high' );
	add_meta_box( 'south-listings-agent', __( 'Agent details', 'south-listings' ), 'south_listings_agent_details_box', 'south_agent', 'normal', 'high' );
}
add_action( 'add_meta_boxes', 'south_listings_meta_boxes' );

/**
 * One field's control.
 *
 * @param string $key   Meta key.
 * @param array  $field Definition.
 * @param mixed  $value Current value.
 * @return string
 */
function south_listings_control( $key, $field, $value ) {
	$id  = 'south-field-' . $key;
	$out = '<p class="south-listings-field"><label for="' . esc_attr( $id ) . '"><strong>' . esc_html( $field['label'] ) . '</strong></label><br>';

	if ( 'south_agent' === $key ) {
		$agents = get_posts(
			array(
				'post_type'      => 'south_agent',
				'posts_per_page' => 100,
				'orderby'        => 'title',
				'order'          => 'ASC',
			)
		);
		$out   .= '<select id="' . esc_attr( $id ) . '" name="' . esc_attr( $key ) . '"><option value="">' . esc_html__( 'No agent', 'south-listings' ) . '</option>';
		foreach ( $agents as $agent ) {
			$out .= '<option value="' . esc_attr( $agent->ID ) . '"' . selected( (int) $value, $agent->ID, false ) . '>' . esc_html( $agent->post_title ) . '</option>';
		}
		$out .= '</select>';
	} elseif ( 'south_gallery' === $key ) {
		$out .= '<input type="text" class="widefat" id="' . esc_attr( $id ) . '" name="' . esc_attr( $key ) . '" value="' . esc_attr( (string) $value ) . '"> ';
		$out .= '<button type="button" class="button" data-south-gallery="' . esc_attr( $id ) . '">' . esc_html__( 'Choose photographs', 'south-listings' ) . '</button>';
	} elseif ( isset( $field['choices'] ) ) {
		$out .= '<select id="' . esc_attr( $id ) . '" name="' . esc_attr( $key ) . '">';
		foreach ( $field['choices'] as $choice => $text ) {
			$out .= '<option value="' . esc_attr( $choice ) . '"' . selected( (string) $value, (string) $choice, false ) . '>' . esc_html( $text ) . '</option>';
		}
		$out .= '</select>';
	} elseif ( 'boolean' === $field['type'] ) {
		$out = '<p class="south-listings-field"><label for="' . esc_attr( $id ) . '"><input type="checkbox" id="' . esc_attr( $id ) . '" name="' . esc_attr( $key ) . '" value="1"' . checked( (bool) $value, true, false ) . '> <strong>' . esc_html( $field['label'] ) . '</strong></label>';
	} elseif ( ! empty( $field['textarea'] ) ) {
		$out .= '<textarea class="widefat" rows="5" id="' . esc_attr( $id ) . '" name="' . esc_attr( $key ) . '">' . esc_textarea( (string) $value ) . '</textarea>';
	} else {
		$type = in_array( $field['type'], array( 'number', 'integer' ), true ) ? 'number' : 'text';
		$step = 'number' === $field['type'] ? ' step="any"' : '';
		$out .= '<input type="' . esc_attr( $type ) . '"' . $step . ' class="' . ( 'text' === $type ? 'widefat' : 'small-text' ) . '" id="' . esc_attr( $id ) . '" name="' . esc_attr( $key ) . '" value="' . esc_attr( (string) $value ) . '">';
	}

	if ( ! empty( $field['hint'] ) ) {
		$out .= '<br><span class="description">' . esc_html( $field['hint'] ) . '</span>';
	}
	return $out . '</p>';
}

/**
 * The property box, its fields in groups.
 *
 * @param WP_Post $post The property.
 */
function south_listings_details_box( $post ) {
	wp_nonce_field( 'south_listings_save', 'south_listings_nonce' );
	$groups = array(
		'price'   => __( 'Price and listing', 'south-listings' ),
		'rooms'   => __( 'Rooms and size', 'south-listings' ),
		'place'   => __( 'Where it is', 'south-listings' ),
		'living'  => __( 'Living', 'south-listings' ),
		'gallery' => __( 'Photographs', 'south-listings' ),
		'agent'   => __( 'Agent', 'south-listings' ),
	);
	echo '<div class="south-listings-box">';
	foreach ( $groups as $group => $title ) {
		echo '<fieldset><legend>' . esc_html( $title ) . '</legend>';
		foreach ( south_listings_fields() as $key => $field ) {
			if ( $field['group'] === $group ) {
				echo south_listings_control( $key, $field, get_post_meta( $post->ID, $key, true ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in south_listings_control().
			}
		}
		echo '</fieldset>';
	}
	echo '</div>';
	echo '<p class="description">' . esc_html__( 'Set For Sale or For Rent, the property type and the city in the boxes on the right. The excerpt is the line under the address on the card.', 'south-listings' ) . '</p>';
}

/**
 * The agent box.
 *
 * @param WP_Post $post The agent.
 */
function south_listings_agent_details_box( $post ) {
	wp_nonce_field( 'south_listings_save', 'south_listings_nonce' );
	foreach ( south_listings_agent_fields() as $key => $field ) {
		$field['type'] = 'string';
		$value         = get_post_meta( $post->ID, $key, true );
		if ( '' === $value && isset( $field['default'] ) ) {
			$value = $field['default'];
		}
		echo south_listings_control( $key, $field, $value ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in south_listings_control().
	}
	echo '<p class="description">' . esc_html__( 'The portrait is the featured image.', 'south-listings' ) . '</p>';
}

/**
 * Save either box.
 *
 * @param int     $post_id Post ID.
 * @param WP_Post $post    Post.
 */
function south_listings_save( $post_id, $post ) {
	if ( ! isset( $_POST['south_listings_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['south_listings_nonce'] ) ), 'south_listings_save' ) ) {
		return;
	}
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}
	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}

	if ( 'property' === $post->post_type ) {
		$fields = south_listings_fields();
	} elseif ( 'south_agent' === $post->post_type ) {
		$fields = south_listings_agent_fields();
	} else {
		return;
	}

	foreach ( $fields as $key => $field ) {
		$type = isset( $field['type'] ) ? $field['type'] : 'string';
		if ( 'boolean' === $type ) {
			update_post_meta( $post_id, $key, isset( $_POST[ $key ] ) ? '1' : '' );
			continue;
		}
		if ( ! isset( $_POST[ $key ] ) ) {
			continue;
		}
		$raw = wp_unslash( $_POST[ $key ] ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- sanitised below by type.
		if ( empty( $field['textarea'] ) && 'south_rooms' !== $key && 'south_features' !== $key ) {
			$raw = sanitize_text_field( $raw );
		}
		if ( 'south_gallery' === $key ) {
			$raw = implode( ',', array_filter( array_map( 'absint', explode( ',', $raw ) ) ) );
		}
		if ( 'south_agent_email' === $key ) {
			$raw = sanitize_email( $raw );
		}
		// register_post_meta()'s sanitize callbacks run inside update_post_meta().
		update_post_meta( $post_id, $key, $raw );
	}
}
add_action( 'save_post', 'south_listings_save', 10, 2 );

/**
 * The gallery picker and a little layout for the box.
 *
 * @param string $hook Admin page.
 */
function south_listings_admin_assets( $hook ) {
	$screen = get_current_screen();
	if ( ! $screen || 'property' !== $screen->post_type || ! in_array( $hook, array( 'post.php', 'post-new.php' ), true ) ) {
		return;
	}
	wp_enqueue_media();
	wp_add_inline_script(
		'media-editor',
		"document.addEventListener('click',function(e){var b=e.target.closest('[data-south-gallery]');if(!b)return;e.preventDefault();var input=document.getElementById(b.getAttribute('data-south-gallery'));var frame=wp.media({title:b.textContent,multiple:true,library:{type:'image'}});frame.on('select',function(){input.value=frame.state().get('selection').map(function(a){return a.id;}).join(',');});frame.open();});"
	);
	wp_add_inline_style( 'common', '.south-listings-box fieldset{margin:0 0 16px;padding:8px 12px;border:1px solid #dcdcde}.south-listings-box legend{padding:0 4px;font-weight:600}.south-listings-field{margin:8px 0}' );
}
add_action( 'admin_enqueue_scripts', 'south_listings_admin_assets' );

/**
 * List columns: price, beds and featured.
 *
 * @param array $columns Columns.
 * @return array
 */
function south_listings_columns( $columns ) {
	$out = array();
	foreach ( $columns as $key => $label ) {
		$out[ $key ] = $label;
		if ( 'title' === $key ) {
			$out['south_price']    = __( 'Price', 'south-listings' );
			$out['south_beds']     = __( 'Beds', 'south-listings' );
			$out['south_featured'] = __( 'Featured', 'south-listings' );
		}
	}
	return $out;
}
add_filter( 'manage_property_posts_columns', 'south_listings_columns' );

/**
 * Column content.
 *
 * @param string $column  Column.
 * @param int    $post_id Post ID.
 */
function south_listings_column_content( $column, $post_id ) {
	switch ( $column ) {
		case 'south_price':
			echo esc_html( south_listings_price( $post_id ) );
			break;
		case 'south_beds':
			echo esc_html( (string) get_post_meta( $post_id, 'south_beds', true ) );
			break;
		case 'south_featured':
			echo get_post_meta( $post_id, 'south_featured', true ) ? '&#9733;' : '';
			break;
	}
}
add_action( 'manage_property_posts_custom_column', 'south_listings_column_content', 10, 2 );
