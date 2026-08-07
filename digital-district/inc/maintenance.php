<?php
/**
 * Maintenance mode — a toggle in wp-admin (Settings → Maintenance Mode) that
 * shows visitors an on-brand "Back Soon" page while logged-in administrators
 * keep seeing the real site. Sends a proper 503 so search engines don't index
 * the maintenance page or drop the site from their index.
 *
 * @package DigitalDistrict
 */

defined( 'ABSPATH' ) || exit;

/**
 * Whether maintenance mode is currently switched on.
 *
 * @return bool
 */
function dd_maintenance_enabled() {
	return (bool) get_option( 'dd_maintenance_mode', false );
}

/**
 * The message shown on the maintenance page (falls back to a default).
 *
 * @return string
 */
function dd_maintenance_message() {
	$msg = trim( (string) get_option( 'dd_maintenance_message', '' ) );
	if ( '' !== $msg ) {
		return $msg;
	}
	return __( "We're tuning a few systems behind the scenes. The site will be back online shortly — thanks for your patience.", 'digital-district' );
}

/**
 * The contact address shown on the maintenance page (falls back to hello@ the
 * site's own domain, which matches the mail-map default).
 *
 * @return string
 */
function dd_maintenance_contact() {
	$email = trim( (string) get_option( 'dd_maintenance_contact', '' ) );
	if ( '' !== $email && is_email( $email ) ) {
		return $email;
	}
	$host = wp_parse_url( home_url(), PHP_URL_HOST );
	return 'hello@' . preg_replace( '/^www\./', '', (string) $host );
}

/**
 * Requests that must always reach the real site, even while maintenance mode
 * is on: logged-in admins, WP-CLI, cron, and REST requests (so the block
 * editor, health checks, and any API-driven publishing keep working).
 *
 * @return bool
 */
function dd_maintenance_should_bypass() {
	if ( is_user_logged_in() && current_user_can( 'manage_options' ) ) {
		return true;
	}
	if ( defined( 'WP_CLI' ) && WP_CLI ) {
		return true;
	}
	if ( wp_doing_cron() ) {
		return true;
	}
	if ( defined( 'REST_REQUEST' ) && REST_REQUEST ) {
		return true;
	}
	return false;
}

/**
 * Intercept front-end requests and show the maintenance page. Never touches
 * wp-admin or wp-login.php — those are not routed through template_redirect,
 * so an administrator can always log in and turn maintenance mode back off.
 */
function dd_maintenance_gate() {
	if ( ! dd_maintenance_enabled() || is_admin() || dd_maintenance_should_bypass() ) {
		return;
	}

	status_header( 503 );
	header( 'Retry-After: 3600' );
	nocache_headers();

	if ( ! defined( 'DD_MAINTENANCE_RENDERING' ) ) {
		define( 'DD_MAINTENANCE_RENDERING', true );
	}

	include get_template_directory() . '/maintenance.php';
	exit;
}
add_action( 'template_redirect', 'dd_maintenance_gate', 0 );

/**
 * On the maintenance page itself, load only the design tokens, fonts and a
 * small dedicated stylesheet — skip main.css/main.js, which the page doesn't
 * use, so it loads as fast as possible even under whatever is being fixed.
 */
function dd_maintenance_trim_assets() {
	if ( ! defined( 'DD_MAINTENANCE_RENDERING' ) || ! DD_MAINTENANCE_RENDERING ) {
		return;
	}
	wp_dequeue_style( 'dd-main' );
	wp_dequeue_style( 'dd-style' );
	wp_dequeue_script( 'dd-main' );

	$dir = get_template_directory_uri();
	wp_enqueue_style( 'dd-maintenance', $dir . '/assets/css/maintenance.css', array( 'dd-tokens' ), DD_VERSION );
}
add_action( 'wp_enqueue_scripts', 'dd_maintenance_trim_assets', 20 );

/**
 * The theme declares title-tag support, so WordPress renders the <title> for
 * us via wp_head() — short-circuit it here rather than fighting it with a
 * second hard-coded <title> tag (which would render two).
 *
 * @param string $title Unused; always overridden while the flag is set.
 * @return string
 */
function dd_maintenance_document_title( $title ) {
	if ( ! defined( 'DD_MAINTENANCE_RENDERING' ) || ! DD_MAINTENANCE_RENDERING ) {
		return $title;
	}
	/* translators: %s: site name. */
	return sprintf( __( '%s &mdash; Back soon', 'digital-district' ), get_bloginfo( 'name' ) );
}
add_filter( 'pre_get_document_title', 'dd_maintenance_document_title' );

/**
 * Settings → Maintenance Mode admin page.
 */
function dd_maintenance_admin_menu() {
	add_options_page(
		__( 'Maintenance Mode', 'digital-district' ),
		__( 'Maintenance Mode', 'digital-district' ),
		'manage_options',
		'dd-maintenance',
		'dd_maintenance_settings_page'
	);
}
add_action( 'admin_menu', 'dd_maintenance_admin_menu' );

function dd_maintenance_register_settings() {
	register_setting( 'dd_maintenance_group', 'dd_maintenance_mode', array(
		'type'              => 'boolean',
		'sanitize_callback' => 'rest_sanitize_boolean',
		'default'           => false,
	) );
	register_setting( 'dd_maintenance_group', 'dd_maintenance_message', array(
		'type'              => 'string',
		'sanitize_callback' => 'sanitize_textarea_field',
		'default'           => '',
	) );
	register_setting( 'dd_maintenance_group', 'dd_maintenance_contact', array(
		'type'              => 'string',
		'sanitize_callback' => 'sanitize_email',
		'default'           => '',
	) );
}
add_action( 'admin_init', 'dd_maintenance_register_settings' );

/**
 * Render the settings page.
 */
function dd_maintenance_settings_page() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}
	$host = wp_parse_url( home_url(), PHP_URL_HOST );
	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'Maintenance Mode', 'digital-district' ); ?></h1>
		<p><?php esc_html_e( 'When enabled, visitors see a "Back Soon" page instead of the site (with a proper 503 status, so search engines know to check back rather than drop the site). You — while logged in as an administrator — always keep seeing the real site.', 'digital-district' ); ?></p>
		<form method="post" action="options.php">
			<?php settings_fields( 'dd_maintenance_group' ); ?>
			<table class="form-table" role="presentation">
				<tr>
					<th scope="row"><?php esc_html_e( 'Enable maintenance mode', 'digital-district' ); ?></th>
					<td>
						<label>
							<input type="checkbox" name="dd_maintenance_mode" value="1" <?php checked( dd_maintenance_enabled() ); ?> />
							<?php esc_html_e( 'Show the maintenance page to visitors', 'digital-district' ); ?>
						</label>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="dd_maintenance_message"><?php esc_html_e( 'Message', 'digital-district' ); ?></label></th>
					<td>
						<textarea id="dd_maintenance_message" name="dd_maintenance_message" rows="3" class="large-text"><?php echo esc_textarea( get_option( 'dd_maintenance_message', '' ) ); ?></textarea>
						<p class="description"><?php esc_html_e( 'Leave blank to use the default message.', 'digital-district' ); ?></p>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="dd_maintenance_contact"><?php esc_html_e( 'Contact email', 'digital-district' ); ?></label></th>
					<td>
						<input type="email" id="dd_maintenance_contact" name="dd_maintenance_contact" class="regular-text" value="<?php echo esc_attr( get_option( 'dd_maintenance_contact', '' ) ); ?>" placeholder="hello@<?php echo esc_attr( preg_replace( '/^www\./', '', (string) $host ) ); ?>" />
					</td>
				</tr>
			</table>
			<?php submit_button(); ?>
		</form>
	</div>
	<?php
}

/**
 * A hard-to-miss admin bar notice while maintenance mode is on, so it's never
 * left on by accident.
 *
 * @param WP_Admin_Bar $wp_admin_bar Admin bar instance.
 */
function dd_maintenance_admin_bar_notice( $wp_admin_bar ) {
	if ( ! dd_maintenance_enabled() || ! current_user_can( 'manage_options' ) ) {
		return;
	}
	$wp_admin_bar->add_node( array(
		'id'    => 'dd-maintenance-active',
		'title' => '⚠ ' . __( 'Maintenance mode is ON', 'digital-district' ),
		'href'  => admin_url( 'options-general.php?page=dd-maintenance' ),
	) );
}
add_action( 'admin_bar_menu', 'dd_maintenance_admin_bar_notice', 90 );
