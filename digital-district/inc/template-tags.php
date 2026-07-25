<?php
/**
 * Template tags — reusable rendering helpers.
 *
 * @package DigitalDistrict
 */

defined( 'ABSPATH' ) || exit;

/**
 * Get a sanitised meta value for the current or given post.
 *
 * @param string   $key     Meta key.
 * @param int|null $post_id Optional post ID.
 * @return string
 */
function dd_meta( $key, $post_id = null ) {
	$post_id = $post_id ? $post_id : get_the_ID();
	return (string) get_post_meta( $post_id, $key, true );
}

/**
 * Map a human status to a CSS modifier.
 *
 * @param string $status Status label.
 * @return string
 */
function dd_status_class( $status ) {
	$slug = sanitize_title( $status );
	return $slug ? 'status--' . $slug : '';
}

/**
 * Render a status pill if a status is set.
 *
 * @param string $status Status label.
 */
function dd_status_pill( $status ) {
	if ( ! $status ) {
		return;
	}
	printf(
		'<span class="status %1$s">%2$s</span>',
		esc_attr( dd_status_class( $status ) ),
		esc_html( $status )
	);
}

/**
 * Estimate reading time in minutes for the current post (≈220 wpm, min 1).
 *
 * @return int
 */
function dd_reading_time() {
	$words = str_word_count( wp_strip_all_tags( get_the_content() ) );
	return max( 1, (int) round( $words / 220 ) );
}

/**
 * Render a 1–5 star rating from a numeric value.
 *
 * @param int|string $rating Rating value.
 */
function dd_rating( $rating ) {
	$rating = (int) $rating;
	if ( $rating < 1 ) {
		return;
	}
	echo '<span class="rating" aria-label="' . esc_attr( sprintf( /* translators: %d: rating out of 5. */ __( '%d out of 5', 'digital-district' ), $rating ) ) . '">';
	for ( $i = 1; $i <= 5; $i++ ) {
		printf( '<span class="%s" aria-hidden="true">%s</span>', $i <= $rating ? 'on' : 'off', '★' );
	}
	echo '</span>';
}

/**
 * Render a project / client_work card with an animated, clickable title.
 *
 * @param int $index Zero-based position (for the corner number).
 */
function dd_card( $index = 0 ) {
	$status = dd_meta( 'dd_status' );
	$client = dd_meta( 'dd_client' );
	$terms  = get_the_terms( get_the_ID(), 'tech' );
	?>
	<article <?php post_class( 'card reveal' ); ?> data-delay="<?php echo esc_attr( ( $index % 3 ) * 80 ); ?>">
		<?php
		$dd_ph = ( $terms && ! is_wp_error( $terms ) ) ? $terms[0]->name : ucfirst( str_replace( '_', ' ', get_post_type() ) );
		if ( has_post_thumbnail() ) :
			?>
			<a class="card__thumb" href="<?php the_permalink(); ?>" tabindex="-1" aria-hidden="true">
				<?php the_post_thumbnail( 'dd_card', array( 'loading' => 'lazy', 'alt' => '' ) ); ?>
			</a>
		<?php else : ?>
			<a class="card__thumb card__thumb--ph" href="<?php the_permalink(); ?>" tabindex="-1" aria-hidden="true" data-label="<?php echo esc_attr( $dd_ph ); ?>"></a>
		<?php endif; ?>

		<div class="card__top">
			<?php dd_status_pill( $status ); ?>
			<?php if ( 'review' === get_post_type() && dd_meta( 'dd_rating' ) ) : ?>
				<?php dd_rating( dd_meta( 'dd_rating' ) ); ?>
			<?php else : ?>
				<span class="card__no"><?php echo esc_html( str_pad( (string) ( $index + 1 ), 2, '0', STR_PAD_LEFT ) ); ?></span>
			<?php endif; ?>
		</div>

		<h3 style="margin:0">
			<a class="title-link" href="<?php the_permalink(); ?>">
				<span><?php the_title(); ?></span>
				<span class="arrow" aria-hidden="true">→</span>
			</a>
		</h3>

		<?php if ( $client ) : ?>
			<p class="hud" style="letter-spacing:.12em"><?php echo esc_html__( 'Client', 'digital-district' ) . ' · ' . esc_html( $client ); ?></p>
		<?php endif; ?>

		<?php if ( has_excerpt() ) : ?>
			<p style="color:var(--muted);font-size:.95rem;margin:0"><?php echo esc_html( get_the_excerpt() ); ?></p>
		<?php endif; ?>

		<?php if ( $terms && ! is_wp_error( $terms ) ) : ?>
			<ul class="card__tags" style="list-style:none;margin:0;padding:0">
				<?php foreach ( array_slice( $terms, 0, 4 ) as $term ) : ?>
					<li class="card__tag"><?php echo esc_html( $term->name ); ?></li>
				<?php endforeach; ?>
			</ul>
		<?php endif; ?>

		<span class="card__sweep" aria-hidden="true"></span>
	</article>
	<?php
}

/**
 * Render a numbered brutalist section heading.
 *
 * @param string $index Two-digit index, e.g. "01".
 * @param string $tag   Small mono tag.
 * @param string $title Heading text.
 * @param string $lede  Optional intro paragraph.
 */
function dd_section_heading( $index, $tag, $title, $lede = '' ) {
	?>
	<div class="section-head">
		<p class="hud reveal"><span class="idx"><?php echo esc_html( $index ); ?></span> &nbsp;/&nbsp; <?php echo esc_html( $tag ); ?></p>
		<h2 class="reveal" data-delay="60"><?php echo esc_html( $title ); ?></h2>
		<?php if ( $lede ) : ?>
			<p class="lede reveal" data-delay="120"><?php echo esc_html( $lede ); ?></p>
		<?php endif; ?>
	</div>
	<?php
}

/**
 * Counts derived from real content — never fabricated.
 *
 * @return array<int, array{n:int,label:string}>
 */
function dd_stats() {
	$projects = wp_count_posts( 'project' );
	$clients  = wp_count_posts( 'client_work' );
	$tech     = wp_count_terms( array( 'taxonomy' => 'tech', 'hide_empty' => true ) );

	return array(
		array( 'n' => (int) ( $clients->publish ?? 0 ), 'label' => __( 'Client projects', 'digital-district' ) ),
		array( 'n' => (int) ( $projects->publish ?? 0 ), 'label' => __( 'Personal builds', 'digital-district' ) ),
		array( 'n' => is_wp_error( $tech ) ? 0 : (int) $tech, 'label' => __( 'Technologies', 'digital-district' ) ),
		array( 'n' => (int) ( ( $clients->publish ?? 0 ) + ( $projects->publish ?? 0 ) ), 'label' => __( 'Total shipped', 'digital-district' ) ),
	);
}

/**
 * Render a single project / client_work case-study page. Shared by both single
 * templates so the layout stays consistent.
 */
function dd_single() {
	$type    = get_post_type();
	$status  = dd_meta( 'dd_status' );
	$client  = dd_meta( 'dd_client' );
	$role    = dd_meta( 'dd_role' );
	$year    = dd_meta( 'dd_year' );
	$live    = dd_meta( 'dd_live_url' );
	$repo    = dd_meta( 'dd_repo_url' );
	$svcs    = dd_meta( 'dd_services' );
	$terms   = get_the_terms( get_the_ID(), 'tech' );
	$archive = ( 'client_work' === $type ) ? get_post_type_archive_link( 'client_work' ) : get_post_type_archive_link( 'project' );
	$back    = ( 'client_work' === $type ) ? __( 'Back to Work', 'digital-district' ) : __( 'Back to Projects', 'digital-district' );
	?>
	<div class="read-progress" aria-hidden="true"><span></span></div>
	<article <?php post_class(); ?>>
		<section class="single-hero">
			<div class="container">
				<p class="hud reveal"><a href="<?php echo esc_url( $archive ); ?>" style="color:var(--muted)">&larr; <?php echo esc_html( $back ); ?></a></p>
				<h1 class="reveal" data-delay="60" style="margin-top:16px"><?php the_title(); ?></h1>

				<?php if ( has_excerpt() ) : ?>
					<p class="lede reveal" data-delay="100"><?php echo esc_html( get_the_excerpt() ); ?></p>
				<?php endif; ?>

				<dl class="single-meta reveal" data-delay="140">
					<?php if ( $status ) : ?>
						<div><dt><?php echo 'review' === $type ? esc_html__( 'Verdict', 'digital-district' ) : esc_html__( 'Status', 'digital-district' ); ?></dt><dd><?php dd_status_pill( $status ); ?></dd></div>
					<?php endif; ?>
					<?php if ( 'review' === $type && dd_meta( 'dd_rating' ) ) : ?>
						<div><dt><?php esc_html_e( 'Rating', 'digital-district' ); ?></dt><dd><?php dd_rating( dd_meta( 'dd_rating' ) ); ?></dd></div>
					<?php endif; ?>
					<?php if ( 'review' === $type && dd_meta( 'dd_subject' ) ) : ?>
						<div><dt><?php esc_html_e( 'Subject', 'digital-district' ); ?></dt><dd><?php echo esc_html( dd_meta( 'dd_subject' ) ); ?></dd></div>
					<?php endif; ?>
					<?php if ( dd_meta( 'dd_read' ) ) : ?>
						<div><dt><?php esc_html_e( 'Read', 'digital-district' ); ?></dt><dd><?php echo esc_html( dd_meta( 'dd_read' ) ); ?></dd></div>
					<?php endif; ?>
					<?php if ( $client ) : ?>
						<div><dt><?php esc_html_e( 'Client', 'digital-district' ); ?></dt><dd><?php echo esc_html( $client ); ?></dd></div>
					<?php endif; ?>
					<?php if ( $role ) : ?>
						<div><dt><?php esc_html_e( 'Role', 'digital-district' ); ?></dt><dd><?php echo esc_html( $role ); ?></dd></div>
					<?php endif; ?>
					<?php if ( $svcs ) : ?>
						<div><dt><?php esc_html_e( 'Services', 'digital-district' ); ?></dt><dd><?php echo esc_html( $svcs ); ?></dd></div>
					<?php endif; ?>
					<?php if ( $year ) : ?>
						<div><dt><?php esc_html_e( 'Year', 'digital-district' ); ?></dt><dd><?php echo esc_html( $year ); ?></dd></div>
					<?php endif; ?>
					<?php if ( $terms && ! is_wp_error( $terms ) ) : ?>
						<div><dt><?php esc_html_e( 'Stack', 'digital-district' ); ?></dt><dd><?php echo esc_html( implode( ', ', wp_list_pluck( $terms, 'name' ) ) ); ?></dd></div>
					<?php endif; ?>
				</dl>

				<?php if ( $live || $repo ) : ?>
					<div class="hero__cta reveal" data-delay="180" style="margin-top:24px">
						<?php if ( $live ) : ?>
							<a class="btn btn--primary magnetic" href="<?php echo esc_url( $live ); ?>" rel="noopener"><?php esc_html_e( 'Visit live site', 'digital-district' ); ?> &#8599;</a>
						<?php endif; ?>
						<?php if ( $repo ) : ?>
							<a class="btn btn--ghost magnetic" href="<?php echo esc_url( $repo ); ?>" rel="noopener"><?php esc_html_e( 'View code', 'digital-district' ); ?> &#8599;</a>
						<?php endif; ?>
					</div>
				<?php endif; ?>
			</div>
		</section>

		<div class="container">
			<?php if ( has_post_thumbnail() ) : ?>
				<div class="single-cover reveal"><?php the_post_thumbnail( 'dd_cover', array( 'alt' => esc_attr( get_the_title() ) ) ); ?></div>
			<?php else : ?>
				<div class="single-cover single-cover--ph reveal" data-label="<?php echo esc_attr( ucwords( str_replace( '_', ' ', $type ) ) ); ?>" aria-hidden="true"></div>
			<?php endif; ?>

			<div class="narrow entry-content reveal">
				<?php
				the_content();
				wp_link_pages( array( 'before' => '<p class="hud">' . esc_html__( 'Pages:', 'digital-district' ), 'after' => '</p>' ) );
				?>
			</div>

			<nav class="narrow post-nav" aria-label="<?php esc_attr_e( 'More work', 'digital-district' ); ?>">
				<span><?php previous_post_link( '%link', '&larr; ' . esc_html__( 'Previous', 'digital-district' ) ); ?></span>
				<span><?php next_post_link( '%link', esc_html__( 'Next', 'digital-district' ) . ' &rarr;' ); ?></span>
			</nav>
		</div>
	</article>
	<?php
}

/**
 * Friendly, user-readable nav links used both as the wp_nav_menu fallback and
 * for the overlay menu. Labels are deliberately plain: Work, Projects, etc.
 *
 * @return array<int, array{label:string,url:string}>
 */
function dd_nav_links() {
	$links = array(
		array( 'label' => __( 'Home', 'digital-district' ), 'url' => home_url( '/' ) ),
	);
	$work = get_post_type_archive_link( 'client_work' );
	if ( $work ) {
		$links[] = array( 'label' => __( 'Work', 'digital-district' ), 'url' => $work );
	}
	$projects = get_post_type_archive_link( 'project' );
	if ( $projects ) {
		$links[] = array( 'label' => __( 'Projects', 'digital-district' ), 'url' => $projects );
	}
	$guides = get_post_type_archive_link( 'guide' );
	if ( $guides ) {
		$links[] = array( 'label' => __( 'Guides', 'digital-district' ), 'url' => $guides );
	}
	$reviews = get_post_type_archive_link( 'review' );
	if ( $reviews ) {
		$links[] = array( 'label' => __( 'Reviews', 'digital-district' ), 'url' => $reviews );
	}
	$blog_id = (int) get_option( 'page_for_posts' );
	if ( $blog_id ) {
		$links[] = array( 'label' => __( 'Blog', 'digital-district' ), 'url' => get_permalink( $blog_id ) );
	}
	$about = get_page_by_path( 'about' );
	if ( $about ) {
		$links[] = array( 'label' => __( 'About', 'digital-district' ), 'url' => get_permalink( $about ) );
	}
	$contact = get_page_by_path( 'contact' );
	if ( $contact ) {
		$links[] = array( 'label' => __( 'Contact', 'digital-district' ), 'url' => get_permalink( $contact ) );
	}
	return $links;
}

/**
 * Fallback menu for wp_nav_menu when no menu is assigned to "primary".
 */
function dd_nav_fallback() {
	echo '<ul class="nav-menu">';
	foreach ( dd_nav_links() as $l ) {
		printf(
			'<li><a href="%1$s">%2$s</a></li>',
			esc_url( $l['url'] ),
			esc_html( $l['label'] )
		);
	}
	echo '</ul>';
}

/**
 * External link for a card/single (live URL preferred, else repo).
 *
 * @param int|null $post_id Optional post ID.
 * @return string
 */
function dd_primary_link( $post_id = null ) {
	$live = dd_meta( 'dd_live_url', $post_id );
	if ( $live ) {
		return $live;
	}
	return dd_meta( 'dd_repo_url', $post_id );
}
