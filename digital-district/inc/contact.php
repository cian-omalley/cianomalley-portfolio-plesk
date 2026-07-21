<?php
/**
 * Contact form handler. Posts to admin-post.php and sends with wp_mail() — no
 * third-party service. Nonce + honeypot + validation. On Plesk, enable the
 * domain's mail service so wp_mail() delivers.
 *
 * @package DigitalDistrict
 */

defined( 'ABSPATH' ) || exit;

function dd_handle_contact() {
	$redirect = wp_get_referer() ? wp_get_referer() : home_url( '/' );

	// Honeypot — bots fill hidden fields.
	if ( ! empty( $_POST['company'] ) ) {
		wp_safe_redirect( add_query_arg( 'contact', 'ok', $redirect ) );
		exit;
	}

	if ( ! isset( $_POST['dd_contact_nonce'] ) || ! wp_verify_nonce( sanitize_key( $_POST['dd_contact_nonce'] ), 'dd_contact' ) ) {
		wp_safe_redirect( add_query_arg( 'contact', 'err', $redirect ) );
		exit;
	}

	$name    = isset( $_POST['name'] ) ? sanitize_text_field( wp_unslash( $_POST['name'] ) ) : '';
	$email   = isset( $_POST['email'] ) ? sanitize_email( wp_unslash( $_POST['email'] ) ) : '';
	$message = isset( $_POST['message'] ) ? sanitize_textarea_field( wp_unslash( $_POST['message'] ) ) : '';

	if ( '' === $name || '' === $message || ! is_email( $email ) ) {
		wp_safe_redirect( add_query_arg( 'contact', 'err', $redirect ) );
		exit;
	}

	$to      = apply_filters( 'dd_contact_recipient', get_option( 'admin_email' ) );
	$subject = sprintf( '[%s] Message from %s', wp_specialchars_decode( get_bloginfo( 'name' ) ), $name );
	$body    = sprintf( "From: %s <%s>\n\n%s\n", $name, $email, $message );
	$headers = array(
		'Content-Type: text/plain; charset=UTF-8',
		'Reply-To: ' . $name . ' <' . $email . '>',
	);

	$sent = wp_mail( $to, $subject, $body, $headers );

	wp_safe_redirect( add_query_arg( 'contact', $sent ? 'ok' : 'err', $redirect ) );
	exit;
}
add_action( 'admin_post_dd_contact', 'dd_handle_contact' );
add_action( 'admin_post_nopriv_dd_contact', 'dd_handle_contact' );

/**
 * Render the status banner after a submission.
 */
function dd_contact_status() {
	if ( empty( $_GET['contact'] ) ) {
		return;
	}
	$state = sanitize_key( wp_unslash( $_GET['contact'] ) );
	if ( 'ok' === $state ) {
		echo '<p class="form-status form-status--ok">' . esc_html__( 'Message transmitted. I will be in touch.', 'digital-district' ) . '</p>';
	} elseif ( 'err' === $state ) {
		echo '<p class="form-status form-status--err">' . esc_html__( 'Something went wrong — check the fields, or email me directly.', 'digital-district' ) . '</p>';
	}
}
