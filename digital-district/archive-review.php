<?php
/**
 * Archive: Reviews.
 *
 * @package DigitalDistrict
 */

defined( 'ABSPATH' ) || exit;

get_header();
?>

<section class="single-hero">
	<div class="container">
		<p class="hud reveal">// <?php esc_html_e( 'Review Laboratory', 'digital-district' ); ?></p>
		<h1 class="reveal" data-delay="60"><?php esc_html_e( 'Reviews', 'digital-district' ); ?></h1>
		<p class="lede reveal" data-delay="120"><?php esc_html_e( 'Hands-on reviews of IDEs, developer tools, and software — written from real use.', 'digital-district' ); ?></p>
	</div>
</section>

<section class="container">
	<?php if ( have_posts() ) : ?>
		<div class="grid grid--3">
			<?php $dd_i = 0; while ( have_posts() ) : the_post(); dd_card( $dd_i ); $dd_i++; endwhile; ?>
		</div>
		<div style="margin-top:48px"><?php the_posts_pagination( array( 'mid_size' => 1 ) ); ?></div>
	<?php else : ?>
		<div class="empty reveal"><p><?php esc_html_e( '// No reviews published yet. Add them in wp-admin → Reviews.', 'digital-district' ); ?></p></div>
	<?php endif; ?>
</section>

<?php
get_footer();
