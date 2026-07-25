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
	<div class="read-progress" aria-hidden="true"><span></span></div>
	<article <?php post_class(); ?>>
		<section class="single-hero">
			<div class="container">
				<p class="hud reveal"><a href="<?php echo esc_url( get_permalink( get_option( 'page_for_posts' ) ) ); ?>" style="color:var(--muted)">&larr; <?php esc_html_e( 'Back to Blog', 'digital-district' ); ?></a></p>
				<h1 class="reveal" data-delay="60" style="margin-top:16px"><?php the_title(); ?></h1>
				<dl class="single-meta reveal" data-delay="120">
					<div><dt><?php esc_html_e( 'Published', 'digital-district' ); ?></dt><dd><?php echo esc_html( get_the_date() ); ?></dd></div>
					<div><dt><?php esc_html_e( 'Read', 'digital-district' ); ?></dt><dd><?php echo esc_html( dd_reading_time() ); ?> <?php esc_html_e( 'min', 'digital-district' ); ?></dd></div>
					<?php $dd_cats = get_the_category(); if ( $dd_cats ) : ?>
						<div><dt><?php esc_html_e( 'Filed under', 'digital-district' ); ?></dt><dd><?php echo get_the_category_list( ', ' ); // phpcs:ignore WordPress.Security.EscapeOutput -- returns escaped links ?></dd></div>
					<?php endif; ?>
				</dl>
			</div>
		</section>
		<div class="container">
			<?php if ( has_post_thumbnail() ) : ?>
				<div class="single-cover reveal"><?php the_post_thumbnail( 'dd_cover' ); ?></div>
			<?php else : ?>
				<div class="single-cover single-cover--ph reveal" data-label="<?php echo esc_attr( $dd_cats ? $dd_cats[0]->name : __( 'Journal', 'digital-district' ) ); ?>" aria-hidden="true"></div>
			<?php endif; ?>
			<div class="narrow entry-content reveal">
				<?php
				the_content();
				wp_link_pages( array( 'before' => '<p class="hud">' . esc_html__( 'Pages:', 'digital-district' ), 'after' => '</p>' ) );
				?>
			</div>

			<nav class="narrow post-nav" aria-label="<?php esc_attr_e( 'More posts', 'digital-district' ); ?>">
				<span><?php previous_post_link( '%link', '&larr; ' . esc_html__( 'Previous', 'digital-district' ) ); ?></span>
				<span><?php next_post_link( '%link', esc_html__( 'Next', 'digital-district' ) . ' &rarr;' ); ?></span>
			</nav>

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
