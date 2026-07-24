<?php
/**
 * Template Name: About
 *
 * Designed About page. Content is drawn from the real project ecosystem (the
 * repositories that make up cianomalley.works) and the confirmed focus areas —
 * no invented biography, employers, or qualifications.
 *
 * @package DigitalDistrict
 */

defined( 'ABSPATH' ) || exit;

$dd_focus = array(
	array( __( 'Self-Hosting & Infrastructure', 'digital-district' ), __( 'Portable Linux web stacks — Nginx, PHP, MariaDB, Redis — fronted by Cloudflare, running at home first and built to migrate to a VPS without a rebuild.', 'digital-district' ) ),
	array( __( 'AI Systems & Agents', 'digital-district' ), __( 'Agent orchestration, memory, and tooling — the AI OS and Hermes Workspace OS repositories are where this work happens.', 'digital-district' ) ),
	array( __( 'Web & Interactive Front-End', 'digital-district' ), __( 'Accessible, progressively-enhanced interfaces with real-time visuals as an enhancement — like this portfolio.', 'digital-district' ) ),
	array( __( 'Design Systems & Tooling', 'digital-district' ), __( 'A shared design system, brand, and documentation hub keep the whole ecosystem consistent and repeatable.', 'digital-district' ) ),
);

$dd_ecosystem = array(
	array( 'Portfolio', __( 'The public portfolio and this Plesk/WordPress build.', 'digital-district' ) ),
	array( 'Design System', __( 'Design tokens, components, motion, and 3D interaction concepts.', 'digital-district' ) ),
	array( 'Brand', __( 'Identity, messaging, writing style, and visual guidelines.', 'digital-district' ) ),
	array( 'Documentation', __( 'Architecture, planning, deployment guides, and workflows.', 'digital-district' ) ),
	array( 'Assets', __( 'Logos, icons, mockups, 3D models, and branding resources.', 'digital-district' ) ),
	array( 'AI OS / Hermes', __( 'Agent-driven operating layers and a research workspace.', 'digital-district' ) ),
);

$dd_stack = array( 'WordPress', 'PHP', 'Python', 'TypeScript', 'JavaScript', 'Three.js', 'Docker', 'Nginx', 'MariaDB', 'Redis', 'Cloudflare', 'JetBrains' );

get_header();

while ( have_posts() ) :
	the_post();
	?>
	<section class="single-hero">
		<div class="container">
			<p class="hud reveal">// <?php esc_html_e( 'Identity', 'digital-district' ); ?></p>
			<h1 class="reveal" data-delay="60"><?php esc_html_e( 'About', 'digital-district' ); ?></h1>
			<p class="lede reveal" data-delay="120">
				<?php esc_html_e( 'I build systems — self-hosted infrastructure, developer tooling, and interactive software — and document how they are made. Based in Stuttgart, Germany.', 'digital-district' ); ?>
			</p>
			<div class="hero__cta reveal" data-delay="160" style="margin-top:24px">
				<a class="btn btn--primary magnetic" href="<?php echo esc_url( get_post_type_archive_link( 'client_work' ) ); ?>"><?php esc_html_e( 'See client work', 'digital-district' ); ?> &rarr;</a>
				<a class="btn btn--ghost magnetic" href="https://github.com/cian-omalley" rel="noopener"><?php esc_html_e( 'GitHub', 'digital-district' ); ?> &#8599;</a>
			</div>
		</div>
	</section>

	<section class="container">
		<?php dd_section_heading( '01', __( 'Focus', 'digital-district' ), __( 'What I work on', 'digital-district' ) ); ?>
		<div class="grid grid--2">
			<?php foreach ( $dd_focus as $i => $f ) : ?>
				<div class="principle reveal" data-delay="<?php echo esc_attr( ( $i % 2 ) * 80 ); ?>">
					<h3 style="margin:0 0 8px"><?php echo esc_html( $f[0] ); ?></h3>
					<p style="color:var(--muted);margin:0"><?php echo esc_html( $f[1] ); ?></p>
				</div>
			<?php endforeach; ?>
		</div>
	</section>

	<section class="container">
		<?php dd_section_heading( '02', __( 'Ecosystem', 'digital-district' ), __( 'One connected system', 'digital-district' ), __( 'The portfolio is one repository in a larger set that share a design system, brand, and documentation.', 'digital-district' ) ); ?>
		<div class="grid grid--3">
			<?php foreach ( $dd_ecosystem as $i => $e ) : ?>
				<div class="card reveal" data-delay="<?php echo esc_attr( ( $i % 3 ) * 70 ); ?>" style="cursor:default">
					<div class="card__top"><span class="hud" style="letter-spacing:.12em"><?php echo esc_html( $e[0] ); ?></span><span class="card__no"><?php echo esc_html( str_pad( (string) ( $i + 1 ), 2, '0', STR_PAD_LEFT ) ); ?></span></div>
					<p style="color:var(--silver);margin:0"><?php echo esc_html( $e[1] ); ?></p>
					<span class="card__sweep" aria-hidden="true"></span>
				</div>
			<?php endforeach; ?>
		</div>
	</section>

	<section class="container">
		<?php dd_section_heading( '03', __( 'Services', 'digital-district' ), __( 'How I can help', 'digital-district' ), __( 'Available for client work — websites, apps, and the infrastructure behind them.', 'digital-district' ) ); ?>
		<div class="grid grid--3">
			<?php
			$dd_services = array(
				array( __( 'Websites & Web Apps', 'digital-district' ), __( 'Fast, accessible, hand-built sites and applications — WordPress or custom, no bloated builders.', 'digital-district' ) ),
				array( __( 'Self-Hosting & DevOps', 'digital-district' ), __( 'Portable Linux stacks, Cloudflare, backups, and painless migrations you actually own.', 'digital-district' ) ),
				array( __( 'Design Systems', 'digital-district' ), __( 'Tokens, components, and motion that keep a brand consistent across every surface.', 'digital-district' ) ),
			);
			foreach ( $dd_services as $i => $s ) :
				?>
				<div class="service reveal" data-delay="<?php echo esc_attr( ( $i % 3 ) * 70 ); ?>">
					<h3><?php echo esc_html( $s[0] ); ?></h3>
					<p><?php echo esc_html( $s[1] ); ?></p>
				</div>
			<?php endforeach; ?>
		</div>
	</section>

	<section class="container">
		<?php dd_section_heading( '04', __( 'Stack', 'digital-district' ), __( 'Tools I build with', 'digital-district' ) ); ?>
		<ul class="reveal" style="display:flex;flex-wrap:wrap;gap:8px;list-style:none;padding:0;margin:0">
			<?php foreach ( $dd_stack as $t ) : ?>
				<li class="chip"><?php echo esc_html( $t ); ?></li>
			<?php endforeach; ?>
		</ul>
	</section>

	<?php if ( trim( get_the_content() ) ) : ?>
		<section class="container">
			<div class="narrow entry-content reveal"><?php the_content(); ?></div>
		</section>
	<?php endif; ?>
	<?php
endwhile;

get_footer();
