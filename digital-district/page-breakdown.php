<?php
/**
 * Template Name: Project Breakdown
 *
 * A designed case-study page telling the story of how this site was built. The
 * copy is honest and mirrors PROJECT-BREAKDOWN.md; the layout reuses the
 * theme's section, card, and status components.
 *
 * @package DigitalDistrict
 */

defined( 'ABSPATH' ) || exit;

get_header();

$dd_arc = array(
	array( '01', 'Astro', __( 'Static site', 'digital-district' ), __( 'First read of “Plesk, no builder” — ship static HTML.', 'digital-district' ), 'status--complete' ),
	array( '02', 'Next.js + React', __( 'App Router build', 'digital-district' ), __( 'A governance file mandated this stack; rebuilt against it, with the look taken from a reference board.', 'digital-district' ), 'status--complete' ),
	array( '03', 'WordPress theme', __( 'Shipped', 'digital-district' ), __( '“Must use WordPress so WP Toolkit + SEO plugins work” — rebuilt as a hand-coded classic theme.', 'digital-district' ), 'status--live' ),
);

$dd_used = array(
	array( __( 'The theme', 'digital-district' ), __( 'Classic WordPress theme, PHP 8.1+, no page builder. Custom post types for projects, client work, guides and reviews, each with their own templates and native meta boxes.', 'digital-district' ) ),
	array( __( 'Design system', 'digital-district' ), __( 'CSS custom-property tokens as the single source of truth. Cyberpunk-brutalist: near-black ground, acid-lime accent, violet / magenta / cyan neon, monospace HUD.', 'digital-district' ) ),
	array( __( 'Front-end', 'digital-district' ), __( 'Vanilla JavaScript for every interaction, a dependency-free canvas hero, and self-hosted fonts (woff2, no external calls).', 'digital-district' ) ),
	array( __( 'Data', 'digital-district' ), __( 'A live GitHub sync imports every repository into Projects via the REST API, converting each README to HTML.', 'digital-district' ) ),
	array( __( 'Build tooling', 'digital-district' ), __( 'WordPress on SQLite + WP-CLI for real local testing, Playwright + Chromium for screenshots and checks, Node for the one-file demo.', 'digital-district' ) ),
	array( __( 'SEO & a11y', 'digital-district' ), __( 'title-tag support so SEO plugins own the meta; semantic landmarks, skip link, focus rings, and reduced-motion throughout.', 'digital-district' ) ),
);

$dd_problems = array(
	array( __( 'No repo creation from the session', 'digital-district' ), __( 'The integration returned 403. Delivered the project as a git bundle; the repo was created, added, and pushed.', 'digital-district' ) ),
	array( __( 'No way to preview WordPress', 'digital-district' ), __( 'No MySQL and wordpress.org was blocked. Ran WordPress on the SQLite drop-in and pulled core from its GitHub mirror.', 'digital-district' ) ),
	array( __( 'GitHub sync blocked in the sandbox', 'digital-district' ), __( 'api.github.com was policy-blocked for that call. Imported the real repos from metadata; the sync runs itself on Plesk.', 'digital-district' ) ),
	array( __( 'No access to the sandbox localhost', 'digital-district' ), __( 'Built a single self-contained HTML file — inlined CSS, JS and fonts with a hash router — click-testable anywhere.', 'digital-district' ) ),
	array( __( 'Font privacy', 'digital-district' ), __( 'Replaced the Google Fonts call by bundling the three families as local woff2 with unicode-range subsetting.', 'digital-district' ) ),
	array( __( 'Empty, bare blog', 'digital-district' ), __( 'Seeded honest journal posts and built a proper index + single with a reading-progress bar and themed comments.', 'digital-district' ) ),
);

$dd_change = array(
	__( 'Pin the platform first — one early question about WordPress would have skipped two full rebuilds.', 'digital-district' ),
	__( 'The governance file mandated React, which fought the “must be WordPress” goal; reconciling that sooner would have saved a rewrite.', 'digital-district' ),
	__( 'Swap the hand-rolled Markdown converter for a vetted library for tougher READMEs.', 'digital-district' ),
	__( 'Add real cover art instead of gradient placeholders, and a small PHPUnit suite for the content model.', 'digital-district' ),
	__( 'A tiny settings page (GitHub username, contact recipient) would beat editing filters.', 'digital-district' ),
);
?>

<article <?php post_class(); ?>>
	<section class="single-hero">
		<div class="container">
			<p class="hud reveal">// <?php esc_html_e( 'Case Study', 'digital-district' ); ?></p>
			<h1 class="reveal" data-delay="60"><?php esc_html_e( 'Project Breakdown', 'digital-district' ); ?></h1>
			<p class="lede reveal" data-delay="120"><?php esc_html_e( 'How this site was built — why it exists, what it is made of, the problems hit and how they were solved, and an honest read on how it went.', 'digital-district' ); ?></p>
		</div>
	</section>

	<section class="container">
		<?php dd_section_heading( '01', __( 'Why', 'digital-district' ), __( 'The brief', 'digital-district' ), __( 'A clean fork of the original portfolio, re-targeted for Plesk with a different set of constraints.', 'digital-district' ) ); ?>
		<div class="grid grid--2">
			<div class="principle reveal"><h3 style="margin:0 0 8px"><?php esc_html_e( 'The constraints', 'digital-district' ); ?></h3><p style="color:var(--muted);margin:0"><?php esc_html_e( 'Host on Plesk. No Oxygen — no page builder at all. Manage it in WordPress so WP Toolkit and free SEO plugins work.', 'digital-district' ); ?></p></div>
			<div class="principle reveal" data-delay="80"><h3 style="margin:0 0 8px"><?php esc_html_e( 'The goal', 'digital-district' ); ?></h3><p style="color:var(--muted);margin:0"><?php esc_html_e( 'Show both personal GitHub projects and client work, keep the cyberpunk identity, and make it dynamic, animated, responsive, and easy to install.', 'digital-district' ); ?></p></div>
		</div>
	</section>

	<section class="container">
		<?php dd_section_heading( '02', __( 'The Arc', 'digital-district' ), __( 'Two pivots, then it landed', 'digital-district' ), __( 'Each change of stack was driven by a new constraint, not indecision. The design language carried through all three.', 'digital-district' ) ); ?>
		<div class="grid grid--3">
			<?php foreach ( $dd_arc as $i => $a ) : ?>
				<div class="card reveal" data-delay="<?php echo esc_attr( ( $i % 3 ) * 70 ); ?>" style="cursor:default">
					<div class="card__top">
						<span class="status <?php echo esc_attr( $a[4] ); ?>"><?php echo esc_html( $a[2] ); ?></span>
						<span class="card__no"><?php echo esc_html( $a[0] ); ?></span>
					</div>
					<h3 style="margin:0"><?php echo esc_html( $a[1] ); ?></h3>
					<p style="color:var(--muted);font-size:.95rem;margin:0"><?php echo esc_html( $a[3] ); ?></p>
					<span class="card__sweep" aria-hidden="true"></span>
				</div>
			<?php endforeach; ?>
		</div>
	</section>

	<section class="container">
		<?php dd_section_heading( '03', __( 'What was used', 'digital-district' ), __( 'The build, in parts', 'digital-district' ) ); ?>
		<div class="grid grid--3">
			<?php foreach ( $dd_used as $i => $u ) : ?>
				<div class="principle reveal" data-delay="<?php echo esc_attr( ( $i % 3 ) * 70 ); ?>">
					<h3 style="margin:0 0 8px"><?php echo esc_html( $u[0] ); ?></h3>
					<p style="color:var(--muted);margin:0;font-size:.95rem"><?php echo esc_html( $u[1] ); ?></p>
				</div>
			<?php endforeach; ?>
		</div>
	</section>

	<section class="container">
		<?php dd_section_heading( '04', __( 'Problems solved', 'digital-district' ), __( 'What broke, and the fix', 'digital-district' ), __( 'Most friction came from the sandbox, not the code — each was worked around to keep a real, verifiable result.', 'digital-district' ) ); ?>
		<div class="grid grid--2">
			<?php foreach ( $dd_problems as $i => $pr ) : ?>
				<div class="card reveal" data-delay="<?php echo esc_attr( ( $i % 2 ) * 80 ); ?>" style="cursor:default">
					<p class="hud" style="color:var(--red-warning, var(--warn));letter-spacing:.1em">// <?php echo esc_html( $pr[0] ); ?></p>
					<p style="color:var(--silver);margin:8px 0 0"><?php echo esc_html( $pr[1] ); ?></p>
					<span class="card__sweep" aria-hidden="true"></span>
				</div>
			<?php endforeach; ?>
		</div>
	</section>

	<section class="container">
		<?php dd_section_heading( '05', __( 'What I would change', 'digital-district' ), __( 'Next time', 'digital-district' ) ); ?>
		<ul class="narrow reveal" style="list-style:none;margin:0;padding:0;display:grid;gap:12px">
			<?php foreach ( $dd_change as $c ) : ?>
				<li style="display:flex;gap:12px;align-items:flex-start"><span class="hud" style="color:var(--acid);margin-top:2px">→</span><span style="color:var(--silver)"><?php echo esc_html( $c ); ?></span></li>
			<?php endforeach; ?>
		</ul>
	</section>

	<section class="container">
		<div class="panel reveal" style="padding:48px">
			<p class="hud" style="color:var(--acid)"><?php esc_html_e( '06 / How it went', 'digital-district' ); ?></p>
			<h2 style="margin:12px 0 12px"><?php esc_html_e( 'Landed in a strong place', 'digital-district' ); ?></h2>
			<p class="lede" style="margin:0 0 16px"><?php esc_html_e( 'Despite two stack pivots, the project ended as a complete, self-contained WordPress theme that builds the whole site on activation and was verified on a real install every step of the way.', 'digital-district' ); ?></p>
			<p style="color:var(--muted);margin:0"><?php esc_html_e( 'All pages return 200, the contact form processes end to end, the admin tools work, and there are zero console errors across desktop, tablet and phone. The clearest lesson: lock the platform constraints before writing code — the host and CMS decide the whole stack, and everything after that decision carried forward cleanly.', 'digital-district' ); ?></p>
			<div class="hero__cta" style="margin-top:24px">
				<a class="btn btn--primary magnetic" href="<?php echo esc_url( get_post_type_archive_link( 'project' ) ); ?>"><?php esc_html_e( 'See the projects', 'digital-district' ); ?> &rarr;</a>
				<a class="btn btn--ghost magnetic" href="https://github.com/cian-omalley/cianomalley-portfolio-plesk" rel="noopener"><?php esc_html_e( 'View the source', 'digital-district' ); ?> &#8599;</a>
			</div>
		</div>
	</section>
</article>

<?php
get_footer();
