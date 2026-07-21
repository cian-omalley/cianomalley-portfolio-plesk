<?php
/**
 * Template Name: Contact
 *
 * Contact page with a working form (wp_mail via admin-post.php). Assign this to
 * the Contact page, or it is used automatically for a page named "contact".
 *
 * @package DigitalDistrict
 */

defined( 'ABSPATH' ) || exit;

get_header();

while ( have_posts() ) :
	the_post();
	?>
	<section class="single-hero">
		<div class="container">
			<p class="hud reveal">// <?php esc_html_e( 'Communications Relay', 'digital-district' ); ?></p>
			<h1 class="reveal" data-delay="60"><?php esc_html_e( 'Get in touch', 'digital-district' ); ?></h1>
			<p class="lede reveal" data-delay="120"><?php esc_html_e( 'Client work, collaborations, or questions — send a signal. This form emails me directly; no third-party service.', 'digital-district' ); ?></p>
		</div>
	</section>

	<section class="container">
		<div class="contact-grid">
			<div class="reveal">
				<?php dd_contact_status(); ?>

				<?php if ( trim( get_the_content() ) ) : ?>
					<div class="entry-content" style="margin-bottom:24px"><?php the_content(); ?></div>
				<?php endif; ?>

				<form class="panel" style="padding:32px" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" method="post">
					<input type="hidden" name="action" value="dd_contact" />
					<?php wp_nonce_field( 'dd_contact', 'dd_contact_nonce' ); ?>

					<div class="field">
						<label for="dd-name"><?php esc_html_e( 'Name', 'digital-district' ); ?></label>
						<input id="dd-name" name="name" type="text" required maxlength="120" autocomplete="name" />
					</div>
					<div class="field">
						<label for="dd-email"><?php esc_html_e( 'Email', 'digital-district' ); ?></label>
						<input id="dd-email" name="email" type="email" required maxlength="180" autocomplete="email" />
					</div>
					<div class="field">
						<label for="dd-message"><?php esc_html_e( 'Message', 'digital-district' ); ?></label>
						<textarea id="dd-message" name="message" rows="6" required maxlength="4000"></textarea>
					</div>
					<div class="field--trap" aria-hidden="true">
						<label for="dd-company"><?php esc_html_e( 'Company', 'digital-district' ); ?></label>
						<input id="dd-company" name="company" type="text" tabindex="-1" autocomplete="off" />
					</div>
					<button class="btn btn--primary magnetic" type="submit"><?php esc_html_e( 'Transmit', 'digital-district' ); ?> &rarr;</button>
				</form>
			</div>

			<aside class="reveal" data-delay="80">
				<div class="panel" style="padding:24px">
					<p class="hud"><?php esc_html_e( 'Direct channels', 'digital-district' ); ?></p>
					<p style="margin-top:16px"><a href="https://github.com/cian-omalley" rel="noopener">GitHub &#8599;</a></p>
					<p><a href="https://cianomalley.dev" rel="noopener">cianomalley.dev &#8599;</a></p>
					<p class="hud" style="margin-top:24px;letter-spacing:.12em">Stuttgart, Germany</p>
					<p class="form-note" style="margin-top:16px"><?php esc_html_e( 'On Plesk, enable the domain mail service so messages deliver.', 'digital-district' ); ?></p>
				</div>
			</aside>
		</div>
	</section>
	<?php
endwhile;

get_footer();
