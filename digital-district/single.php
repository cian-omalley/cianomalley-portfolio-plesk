<?php
/**
 * Single: standard blog posts.
 *
 * @package DigitalDistrict
 */

defined( 'ABSPATH' ) || exit;

get_header();

while ( have_posts() ) :
	the_post();
	?>
	<article <?php post_class(); ?>>
		<section class="single-hero">
			<div class="container">
				<p class="hud reveal"><?php echo esc_html( get_the_date() ); ?></p>
				<h1 class="reveal" data-delay="60"><?php the_title(); ?></h1>
			</div>
		</section>
		<div class="container">
			<?php if ( has_post_thumbnail() ) : ?>
				<div class="single-cover reveal"><?php the_post_thumbnail( 'dd_cover' ); ?></div>
			<?php endif; ?>
			<div class="narrow entry-content reveal">
				<?php
				the_content();
				wp_link_pages( array( 'before' => '<p class="hud">' . esc_html__( 'Pages:', 'digital-district' ), 'after' => '</p>' ) );
				?>
			</div>
			<?php
			if ( comments_open() || get_comments_number() ) {
				echo '<div class="narrow" style="margin-top:48px">';
				comments_template();
				echo '</div>';
			}
			?>
		</div>
	</article>
	<?php
endwhile;

get_footer();
