<?php
/**
 * Blog index (the posts page). WordPress uses home.php for the page assigned to
 * "Posts page", so this renders the Blog with a proper header and post cards.
 *
 * @package DigitalDistrict
 */

defined( 'ABSPATH' ) || exit;

get_header();
?>

<section class="single-hero">
	<div class="container">
		<p class="hud reveal">// <?php esc_html_e( 'Journal', 'digital-district' ); ?></p>
		<h1 class="reveal" data-delay="60"><?php esc_html_e( 'Blog', 'digital-district' ); ?></h1>
		<p class="lede reveal" data-delay="120"><?php esc_html_e( 'Build notes, self-hosting field reports, and the thinking behind the work.', 'digital-district' ); ?></p>
	</div>
</section>

<section class="container">
	<?php if ( have_posts() ) : ?>
		<div class="grid grid--3">
			<?php
			$dd_i = 0;
			while ( have_posts() ) :
				the_post();
				$dd_cats = get_the_category();
				$dd_label = $dd_cats ? $dd_cats[0]->name : __( 'Post', 'digital-district' );
				?>
				<article <?php post_class( 'card reveal' ); ?> data-delay="<?php echo esc_attr( ( $dd_i % 3 ) * 70 ); ?>">
					<?php if ( has_post_thumbnail() ) : ?>
						<a class="card__thumb" href="<?php the_permalink(); ?>" tabindex="-1" aria-hidden="true"><?php the_post_thumbnail( 'dd_card', array( 'loading' => 'lazy' ) ); ?></a>
					<?php else : ?>
						<a class="card__thumb card__thumb--ph" href="<?php the_permalink(); ?>" tabindex="-1" aria-hidden="true" data-label="<?php echo esc_attr( $dd_label ); ?>"></a>
					<?php endif; ?>

					<div class="card__top">
						<span class="hud" style="letter-spacing:.12em;color:var(--cyan)"><?php echo esc_html( $dd_label ); ?></span>
						<span class="card__no"><?php echo esc_html( get_the_date( 'M j' ) ); ?></span>
					</div>

					<h3 style="margin:0">
						<a class="title-link" href="<?php the_permalink(); ?>"><span><?php the_title(); ?></span> <span class="arrow" aria-hidden="true">&rarr;</span></a>
					</h3>

					<p style="color:var(--muted);font-size:.95rem;margin:0"><?php echo esc_html( get_the_excerpt() ); ?></p>

					<p class="hud" style="margin-top:auto;letter-spacing:.1em"><?php echo esc_html( dd_reading_time() ); ?> <?php esc_html_e( 'min read', 'digital-district' ); ?></p>
					<span class="card__sweep" aria-hidden="true"></span>
				</article>
				<?php
				$dd_i++;
			endwhile;
			?>
		</div>
		<div style="margin-top:48px"><?php the_posts_pagination( array( 'mid_size' => 1, 'prev_text' => __( '&larr; Prev', 'digital-district' ), 'next_text' => __( 'Next &rarr;', 'digital-district' ) ) ); ?></div>
	<?php else : ?>
		<div class="empty reveal"><p><?php esc_html_e( '// No posts yet. Write your first in wp-admin → Posts.', 'digital-district' ); ?></p></div>
	<?php endif; ?>
</section>

<?php
get_footer();
