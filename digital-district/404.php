<?php
/**
 * 404 template.
 *
 * @package DigitalDistrict
 */

defined( 'ABSPATH' ) || exit;

get_header();
?>

<section class="container" style="min-height:60svh;display:grid;place-items:center;text-align:center">
	<div style="max-width:32rem">
		<p class="hud" style="color:var(--acid)"><?php esc_html_e( 'Error 404', 'digital-district' ); ?></p>
		<h1 style="margin-top:12px"><?php esc_html_e( 'Signal lost', 'digital-district' ); ?></h1>
		<p class="lede" style="margin-inline:auto"><?php esc_html_e( "That address isn't on the grid. The relay is still open, though.", 'digital-district' ); ?></p>
		<div class="hero__cta" style="justify-content:center;margin-top:24px">
			<a class="btn btn--primary magnetic" href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Return to base', 'digital-district' ); ?> &rarr;</a>
			<a class="btn btn--ghost magnetic" href="<?php echo esc_url( get_post_type_archive_link( 'client_work' ) ); ?>"><?php esc_html_e( 'See work', 'digital-district' ); ?></a>
		</div>
	</div>
</section>

<?php
get_footer();
