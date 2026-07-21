<?php
/**
 * Footer: system-status footer + wp_footer.
 *
 * @package DigitalDistrict
 */

defined( 'ABSPATH' ) || exit;
?>
</main><!-- #main -->

<footer class="site-footer">
	<div class="container">
		<div class="footer-grid">
			<div class="footer-col">
				<p class="brand" style="font-size:1.1rem">
					<span class="mark" aria-hidden="true"></span>
					<?php bloginfo( 'name' ); ?>
				</p>
				<p class="hud" style="margin-top:12px;letter-spacing:.12em"><?php echo esc_html( get_bloginfo( 'description' ) ); ?></p>
			</div>

			<nav class="footer-col" aria-label="<?php esc_attr_e( 'Footer', 'digital-district' ); ?>">
				<?php foreach ( dd_nav_links() as $l ) : ?>
					<a href="<?php echo esc_url( $l['url'] ); ?>"><?php echo esc_html( $l['label'] ); ?></a>
				<?php endforeach; ?>
			</nav>

			<div class="footer-col">
				<a href="https://github.com/cian-omalley" rel="noopener">GitHub &#8599;</a>
				<a href="https://cianomalley.dev" rel="noopener">cianomalley.dev &#8599;</a>
			</div>
		</div>

		<div class="footer-legal">
			<span>&copy; <?php echo esc_html( gmdate( 'Y' ) ); ?> <?php bloginfo( 'name' ); ?></span>
			<span class="live"><?php esc_html_e( 'SYS.ONLINE · WordPress · no builder', 'digital-district' ); ?></span>
		</div>
	</div>
</footer>

<?php wp_footer(); ?>
</body>
</html>
