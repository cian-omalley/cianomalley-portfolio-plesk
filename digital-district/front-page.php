<?php
/**
 * Front page: hero, stack marquee, stats, client work, projects, principles,
 * and a contact call-to-action. Content comes from the custom post types, so
 * the owner edits everything in wp-admin.
 *
 * @package DigitalDistrict
 */

defined( 'ABSPATH' ) || exit;

$dd_name      = get_bloginfo( 'name' );
$dd_parts     = explode( ' ', $dd_name, 2 );
$dd_manifesto = __( "The future isn't magic. It's systematic.", 'digital-district' );
$dd_pitch     = __( 'Developer & systems builder — I design self-hosted infrastructure, developer tooling, and interactive software, and build websites and apps for clients.', 'digital-district' );

$dd_stack = array( 'WordPress', 'PHP', 'JavaScript', 'Three.js', 'Next.js', 'React', 'TypeScript', 'MariaDB', 'Redis', 'Nginx', 'Docker', 'Cloudflare', 'Self-Hosting', 'JetBrains' );

get_header();
?>

<section class="hero" aria-label="<?php esc_attr_e( 'Introduction', 'digital-district' ); ?>">
	<div class="hero__scene" aria-hidden="true">
		<canvas id="hero-canvas"></canvas>
		<div class="hero__fallback"></div>
	</div>
	<div class="hero__scrim" aria-hidden="true"></div>

	<div class="hero__hud" aria-hidden="true">
		<span class="hud hud--tl">48.7758&deg; N, 9.1829&deg; E</span>
		<span class="hud hud--tr">// SYS.ONLINE</span>
		<span class="hud hud--br"><?php esc_html_e( 'BUILD · WORDPRESS · PLESK', 'digital-district' ); ?></span>
	</div>

	<div class="container hero__inner">
		<p class="hud reveal"><?php echo esc_html( get_bloginfo( 'description' ) ? get_bloginfo( 'description' ) : __( 'Developer & Systems Builder', 'digital-district' ) ); ?></p>
		<h1 class="hero__title reveal" data-delay="60">
			<?php echo esc_html( $dd_parts[0] ); ?>
			<?php if ( ! empty( $dd_parts[1] ) ) : ?>
				<br /><span class="second"><?php echo esc_html( $dd_parts[1] ); ?></span>
			<?php endif; ?>
		</h1>
		<p class="lede reveal" data-delay="120" style="font-family:var(--font-mono);font-size:.95rem">
			<span class="hero__manifesto"><?php echo esc_html( $dd_manifesto ); ?></span>
			<?php echo esc_html( $dd_pitch ); ?>
		</p>
		<div class="hero__cta reveal" data-delay="180">
			<a class="btn btn--primary magnetic" href="<?php echo esc_url( get_post_type_archive_link( 'client_work' ) ); ?>"><?php esc_html_e( 'See client work', 'digital-district' ); ?> &rarr;</a>
			<a class="btn btn--ghost magnetic" href="<?php echo esc_url( get_post_type_archive_link( 'project' ) ); ?>"><?php esc_html_e( 'Personal projects', 'digital-district' ); ?></a>
		</div>
	</div>
</section>

<div class="marquee" aria-label="<?php esc_attr_e( 'Technology stack', 'digital-district' ); ?>">
	<div class="marquee__track">
		<span><?php echo esc_html( implode( ' ', $dd_stack ) ); ?></span>
		<span aria-hidden="true"><?php echo esc_html( implode( ' ', $dd_stack ) ); ?></span>
	</div>
</div>

<section class="stats" aria-label="<?php esc_attr_e( 'By the numbers', 'digital-district' ); ?>">
	<?php foreach ( dd_stats() as $stat ) : ?>
		<div class="stat">
			<div class="stat__n" data-count="<?php echo esc_attr( $stat['n'] ); ?>">0</div>
			<p class="stat__l hud"><?php echo esc_html( $stat['label'] ); ?></p>
		</div>
	<?php endforeach; ?>
</section>

<?php
$dd_clients = new WP_Query( array(
	'post_type'      => 'client_work',
	'posts_per_page' => 6,
	'orderby'        => array( 'menu_order' => 'ASC', 'date' => 'DESC' ),
	'no_found_rows'  => true,
) );
?>
<section id="work" class="container" aria-labelledby="work-h" style="scroll-margin-top:80px">
	<?php
	dd_section_heading(
		'01',
		__( 'Client Work', 'digital-district' ),
		__( 'Websites & apps for clients', 'digital-district' ),
		__( 'Selected projects built for clients — sites and applications shipped to real briefs.', 'digital-district' )
	);
	?>
	<?php if ( $dd_clients->have_posts() ) : ?>
		<div class="grid grid--3">
			<?php $dd_i = 0; while ( $dd_clients->have_posts() ) : $dd_clients->the_post(); dd_card( $dd_i ); $dd_i++; endwhile; ?>
		</div>
		<p class="reveal" style="margin-top:32px"><a class="btn btn--ghost magnetic" href="<?php echo esc_url( get_post_type_archive_link( 'client_work' ) ); ?>"><?php esc_html_e( 'All client work', 'digital-district' ); ?> &rarr;</a></p>
	<?php else : ?>
		<div class="empty reveal">
			<p><?php esc_html_e( '// No client work published yet. Add client projects in wp-admin → Client Work.', 'digital-district' ); ?></p>
		</div>
	<?php endif; ?>
	<?php wp_reset_postdata(); ?>
</section>

<?php
$dd_projects = new WP_Query( array(
	'post_type'      => 'project',
	'posts_per_page' => 6,
	'orderby'        => array( 'menu_order' => 'ASC', 'date' => 'DESC' ),
	'no_found_rows'  => true,
) );
?>
<section id="projects" class="container" aria-labelledby="projects-h" style="scroll-margin-top:80px">
	<?php
	dd_section_heading(
		'02',
		__( 'Projects', 'digital-district' ),
		__( 'Personal & open-source builds', 'digital-district' ),
		__( 'Systems I build for myself, entered with honest statuses — In Progress, Prototype, or Research.', 'digital-district' )
	);
	?>
	<?php if ( $dd_projects->have_posts() ) : ?>
		<div class="grid grid--3">
			<?php $dd_i = 0; while ( $dd_projects->have_posts() ) : $dd_projects->the_post(); dd_card( $dd_i ); $dd_i++; endwhile; ?>
		</div>
		<p class="reveal" style="margin-top:32px"><a class="btn btn--ghost magnetic" href="<?php echo esc_url( get_post_type_archive_link( 'project' ) ); ?>"><?php esc_html_e( 'All projects', 'digital-district' ); ?> &rarr;</a></p>
	<?php else : ?>
		<div class="empty reveal"><p><?php esc_html_e( '// No projects yet. Add them in wp-admin → Projects.', 'digital-district' ); ?></p></div>
	<?php endif; ?>
	<?php wp_reset_postdata(); ?>
</section>

<?php
$dd_guides = new WP_Query( array(
	'post_type'      => 'guide',
	'posts_per_page' => 3,
	'orderby'        => array( 'menu_order' => 'ASC', 'date' => 'DESC' ),
	'no_found_rows'  => true,
) );
if ( $dd_guides->have_posts() ) :
	?>
	<section id="guides" class="container" style="scroll-margin-top:80px">
		<?php
		dd_section_heading(
			'03',
			__( 'Guides', 'digital-district' ),
			__( 'Written guides', 'digital-district' ),
			__( 'Notes and how-tos on self-hosting, WordPress, and tooling.', 'digital-district' )
		);
		?>
		<div class="grid grid--3">
			<?php $dd_i = 0; while ( $dd_guides->have_posts() ) : $dd_guides->the_post(); dd_card( $dd_i ); $dd_i++; endwhile; ?>
		</div>
		<p class="reveal" style="margin-top:32px"><a class="btn btn--ghost magnetic" href="<?php echo esc_url( get_post_type_archive_link( 'guide' ) ); ?>"><?php esc_html_e( 'All guides', 'digital-district' ); ?> &rarr;</a></p>
	</section>
	<?php
endif;
wp_reset_postdata();

$dd_reviews = new WP_Query( array(
	'post_type'      => 'review',
	'posts_per_page' => 3,
	'orderby'        => array( 'menu_order' => 'ASC', 'date' => 'DESC' ),
	'no_found_rows'  => true,
) );
if ( $dd_reviews->have_posts() ) :
	?>
	<section id="reviews" class="container" style="scroll-margin-top:80px">
		<?php
		dd_section_heading(
			'04',
			__( 'Reviews', 'digital-district' ),
			__( 'Tools, reviewed', 'digital-district' ),
			__( 'Hands-on reviews of IDEs and developer tools, written from real use.', 'digital-district' )
		);
		?>
		<div class="grid grid--3">
			<?php $dd_i = 0; while ( $dd_reviews->have_posts() ) : $dd_reviews->the_post(); dd_card( $dd_i ); $dd_i++; endwhile; ?>
		</div>
		<p class="reveal" style="margin-top:32px"><a class="btn btn--ghost magnetic" href="<?php echo esc_url( get_post_type_archive_link( 'review' ) ); ?>"><?php esc_html_e( 'All reviews', 'digital-district' ); ?> &rarr;</a></p>
	</section>
	<?php
endif;
wp_reset_postdata();
?>

<section id="principles" class="container" style="scroll-margin-top:80px">
	<?php
	dd_section_heading(
		'05',
		__( 'Principles', 'digital-district' ),
		__( 'How the work gets built', 'digital-district' )
	);
	$dd_principles = array(
		array( '01', __( 'Function Over Form', 'digital-district' ), __( 'The accessible baseline ships first. Spectacle is layered on top, never required to use the site.', 'digital-district' ) ),
		array( '02', __( 'Own The Stack', 'digital-district' ), __( 'Self-hosted first, on a portable stack. No lock-in to a builder or platform I cannot move off quickly.', 'digital-district' ) ),
		array( '03', __( 'Document Everything', 'digital-district' ), __( 'Every system worth building is worth a written guide. The writing ships early.', 'digital-district' ) ),
		array( '04', __( 'Honest Status', 'digital-district' ), __( 'Projects carry real states. No invented results or fabricated credentials, ever.', 'digital-district' ) ),
	);
	?>
	<div class="grid grid--2">
		<?php foreach ( $dd_principles as $i => $pr ) : ?>
			<div class="principle reveal" data-delay="<?php echo esc_attr( ( $i % 2 ) * 80 ); ?>">
				<p class="principle__no"><?php echo esc_html( $pr[0] ); ?></p>
				<h3 style="margin:12px 0 8px"><?php echo esc_html( $pr[1] ); ?></h3>
				<p style="color:var(--muted);margin:0"><?php echo esc_html( $pr[2] ); ?></p>
			</div>
		<?php endforeach; ?>
	</div>
</section>

<section class="container" aria-label="<?php esc_attr_e( 'Contact', 'digital-district' ); ?>">
	<div class="panel reveal" style="padding:48px;display:flex;flex-wrap:wrap;gap:32px;align-items:center;justify-content:space-between">
		<div>
			<p class="hud"><?php esc_html_e( 'Communications Relay', 'digital-district' ); ?></p>
			<h2 style="margin:12px 0 8px"><?php esc_html_e( 'Have a project in mind?', 'digital-district' ); ?></h2>
			<p class="lede" style="margin:0"><?php esc_html_e( 'Client work, collaborations, or questions about self-hosting — open a channel.', 'digital-district' ); ?></p>
		</div>
		<?php $dd_contact = get_page_by_path( 'contact' ); ?>
		<a class="btn btn--primary magnetic" href="<?php echo esc_url( $dd_contact ? get_permalink( $dd_contact ) : home_url( '/' ) ); ?>"><?php esc_html_e( 'Get in touch', 'digital-district' ); ?> &rarr;</a>
	</div>
</section>

<?php
get_footer();
