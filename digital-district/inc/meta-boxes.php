<?php
/**
 * Lightweight meta boxes for project + client_work details. No plugin
 * dependency: native WordPress meta boxes, nonce-protected, sanitised in and
 * escaped out.
 *
 * @package DigitalDistrict
 */

defined( 'ABSPATH' ) || exit;

/**
 * Field definitions per post type.
 *
 * @return array<string, array<string, array>>
 */
function dd_fields() {
	$project_statuses = array( 'In Progress', 'Planning', 'Complete', 'Prototype', 'Research', 'Live' );

	return array(
		'project' => array(
			'dd_status'   => array( 'label' => 'Status', 'type' => 'select', 'options' => $project_statuses ),
			'dd_role'     => array( 'label' => 'My role', 'type' => 'text' ),
			'dd_year'     => array( 'label' => 'Year', 'type' => 'text' ),
			'dd_repo_url' => array( 'label' => 'Repository URL', 'type' => 'url' ),
			'dd_live_url' => array( 'label' => 'Live URL', 'type' => 'url' ),
		),
		'client_work' => array(
			'dd_client'   => array( 'label' => 'Client', 'type' => 'text' ),
			'dd_status'   => array( 'label' => 'Status', 'type' => 'select', 'options' => array( 'Live', 'In Progress', 'Complete', 'Prototype' ) ),
			'dd_services' => array( 'label' => 'Services provided', 'type' => 'text' ),
			'dd_year'     => array( 'label' => 'Year', 'type' => 'text' ),
			'dd_live_url' => array( 'label' => 'Live URL', 'type' => 'url' ),
		),
		'guide' => array(
			'dd_status' => array( 'label' => 'Status', 'type' => 'select', 'options' => array( 'Published', 'In Progress', 'Planned' ) ),
			'dd_read'   => array( 'label' => 'Read time (e.g. 8 min)', 'type' => 'text' ),
		),
		'review' => array(
			'dd_subject' => array( 'label' => 'Subject (what is reviewed)', 'type' => 'text' ),
			'dd_rating'  => array( 'label' => 'Rating (1–5)', 'type' => 'select', 'options' => array( '5', '4', '3', '2', '1' ) ),
			'dd_status'  => array( 'label' => 'Verdict', 'type' => 'select', 'options' => array( 'Recommended', 'Mixed', 'Not Recommended', 'In Progress', 'Planned' ) ),
			'dd_live_url'=> array( 'label' => 'Link', 'type' => 'url' ),
		),
	);
}

function dd_add_meta_boxes() {
	foreach ( array_keys( dd_fields() ) as $type ) {
		add_meta_box( 'dd_details', __( 'Details', 'digital-district' ), 'dd_render_meta_box', $type, 'side', 'high' );
	}
}
add_action( 'add_meta_boxes', 'dd_add_meta_boxes' );

/**
 * Render the details meta box.
 *
 * @param WP_Post $post Current post.
 */
function dd_render_meta_box( $post ) {
	$fields = dd_fields();
	$type   = $post->post_type;
	if ( empty( $fields[ $type ] ) ) {
		return;
	}
	wp_nonce_field( 'dd_save_details', 'dd_details_nonce' );

	foreach ( $fields[ $type ] as $key => $conf ) {
		$value = get_post_meta( $post->ID, $key, true );
		echo '<p><label style="display:block;font-weight:600;margin-bottom:4px" for="' . esc_attr( $key ) . '">' . esc_html( $conf['label'] ) . '</label>';

		if ( 'select' === $conf['type'] ) {
			echo '<select style="width:100%" id="' . esc_attr( $key ) . '" name="' . esc_attr( $key ) . '">';
			echo '<option value="">' . esc_html__( '— none —', 'digital-district' ) . '</option>';
			foreach ( $conf['options'] as $opt ) {
				echo '<option value="' . esc_attr( $opt ) . '"' . selected( $value, $opt, false ) . '>' . esc_html( $opt ) . '</option>';
			}
			echo '</select>';
		} else {
			$input_type = ( 'url' === $conf['type'] ) ? 'url' : 'text';
			echo '<input style="width:100%" type="' . esc_attr( $input_type ) . '" id="' . esc_attr( $key ) . '" name="' . esc_attr( $key ) . '" value="' . esc_attr( $value ) . '" />';
		}
		echo '</p>';
	}
}

/**
 * Save meta box values.
 *
 * @param int $post_id Post ID.
 */
function dd_save_details( $post_id ) {
	if ( ! isset( $_POST['dd_details_nonce'] ) || ! wp_verify_nonce( sanitize_key( $_POST['dd_details_nonce'] ), 'dd_save_details' ) ) {
		return;
	}
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}
	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}

	$fields = dd_fields();
	$type   = get_post_type( $post_id );
	if ( empty( $fields[ $type ] ) ) {
		return;
	}

	foreach ( $fields[ $type ] as $key => $conf ) {
		if ( ! isset( $_POST[ $key ] ) ) {
			continue;
		}
		$raw = wp_unslash( $_POST[ $key ] );
		$clean = ( 'url' === $conf['type'] ) ? esc_url_raw( $raw ) : sanitize_text_field( $raw );
		if ( '' === $clean ) {
			delete_post_meta( $post_id, $key );
		} else {
			update_post_meta( $post_id, $key, $clean );
		}
	}
}
add_action( 'save_post', 'dd_save_details' );
