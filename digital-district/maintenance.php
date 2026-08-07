<?php
/**
 * Maintenance mode page. Not a selectable page template — it is included
 * directly by dd_maintenance_gate() in inc/maintenance.php whenever
 * maintenance mode is on and the current visitor isn't an admin. Deliberately
 * self-contained (no header.php/footer.php) so it never depends on menus,
 * widgets, or anything else that might be mid-change during maintenance.
 *
 * @package DigitalDistrict
 */

defined( 'ABSPATH' ) || exit;
?>
<!doctype html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo( 'charset' ); ?>" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<meta name="robots" content="noindex,nofollow" />
<?php // Title comes from wp_head() below via the dd_maintenance_document_title filter — the theme supports title-tag, which already renders it. ?>
<?php wp_head(); ?>
</head>
<body <?php body_class( 'dd-maintenance' ); ?>>
<?php wp_body_open(); ?>

<main class="maint">
	<div class="maint__bg" aria-hidden="true"></div>
	<div class="maint__panel">
		<?php if ( has_custom_logo() ) : ?>
			<div class="maint__logo"><?php the_custom_logo(); ?></div>
		<?php else : ?>
			<span class="maint__mark" aria-hidden="true"></span>
		<?php endif; ?>

		<p class="maint__hud">// <?php esc_html_e( 'System status', 'digital-district' ); ?></p>
		<h1 class="maint__title"><?php esc_html_e( 'Back', 'digital-district' ); ?> <span><?php esc_html_e( 'Soon', 'digital-district' ); ?></span></h1>
		<p class="maint__msg"><?php echo esc_html( dd_maintenance_message() ); ?></p>

		<div class="maint__status">
			<span class="maint__dot" aria-hidden="true"></span>
			<?php esc_html_e( 'Scheduled maintenance in progress', 'digital-district' ); ?>
		</div>

		<a class="maint__mail" href="mailto:<?php echo esc_attr( dd_maintenance_contact() ); ?>">
			<?php echo esc_html( dd_maintenance_contact() ); ?>
		</a>
	</div>
</main>

<?php wp_footer(); ?>
</body>
</html>
