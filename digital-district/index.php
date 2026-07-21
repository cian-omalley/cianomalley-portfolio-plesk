<?php
/**
 * Fallback template — also used for the blog / posts index if enabled.
 *
 * @package DigitalDistrict
 */

defined( 'ABSPATH' ) || exit;

get_header();
?>

<section class="single-hero">
	<div class="container">
		<h1 class="reveal">
			<?php
			if ( is_home() && ! is_front_page() ) {
				single_post_title();
			} elseif ( is_search() ) {
				/* translators: %s: search query. */
				printf( esc_html__( 'Search: %s', 'digital-district' ), '<span style="color:var(--acid)">' . esc_html( get_search_query() ) . '</span>' );
			} else {
				esc_html_e( 'Latest', 'digital-district' );
			}
			?>
		</h1>
	</div>
</section>

<section class="container">
	<?php if ( have_posts() ) : ?>
		<div class="grid grid--3">
			<?php
			while ( have_posts() ) :
				the_post();
				?>
				<article <?php post_class( 'card reveal' ); ?>>
					<?php if ( has_post_thumbnail() ) : ?>
						<a class="card__thumb" href="<?php the_permalink(); ?>" tabindex="-1" aria-hidden="true"><?php the_post_thumbnail( 'dd_card', array( 'loading' => 'lazy' ) ); ?></a>
					<?php endif; ?>
					<h3 style="margin:0"><a class="title-link" href="<?php the_permalink(); ?>"><span><?php the_title(); ?></span> <span class="arrow" aria-hidden="true">&rarr;</span></a></h3>
					<p style="color:var(--muted);font-size:.95rem;margin:0"><?php echo esc_html( get_the_excerpt() ); ?></p>
					<span class="card__sweep" aria-hidden="true"></span>
				</article>
				<?php
			endwhile;
			?>
		</div>
		<div style="margin-top:48px"><?php the_posts_pagination( array( 'mid_size' => 1 ) ); ?></div>
	<?php else : ?>
		<div class="empty"><p><?php esc_html_e( '// Nothing here yet.', 'digital-district' ); ?></p></div>
	<?php endif; ?>
</section>

<?php
get_footer();
