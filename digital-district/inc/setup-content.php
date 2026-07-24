<?php
/**
 * One-time setup on theme activation: register rewrite rules, create the
 * About/Contact pages, build a primary menu with friendly labels, and seed the
 * owner's honest personal projects. Client Work is intentionally left empty —
 * no clients are invented; the owner adds real client projects in wp-admin.
 *
 * @package DigitalDistrict
 */

defined( 'ABSPATH' ) || exit;

function dd_after_switch_theme() {
	dd_register_content();
	flush_rewrite_rules();

	dd_create_pages();

	// Import every repository from the GitHub account. If the server can't
	// reach GitHub during activation, fall back to a small honest seed so the
	// site isn't empty; the owner can re-run the sync from Projects → Sync GitHub.
	$synced = function_exists( 'dd_sync_github' ) ? dd_sync_github() : 0;
	if ( is_wp_error( $synced ) || 0 === $synced ) {
		dd_seed_projects();
	}

	dd_seed_guides();
	dd_seed_reviews();
	dd_seed_client_templates();
	dd_build_primary_menu();

	if ( ! wp_next_scheduled( 'dd_github_cron' ) ) {
		wp_schedule_event( time() + DAY_IN_SECONDS, 'daily', 'dd_github_cron' );
	}
}
add_action( 'after_switch_theme', 'dd_after_switch_theme' );

/**
 * Tidy up the cron when the theme is switched away.
 */
function dd_on_switch_theme() {
	$ts = wp_next_scheduled( 'dd_github_cron' );
	if ( $ts ) {
		wp_unschedule_event( $ts, 'dd_github_cron' );
	}
}
add_action( 'switch_theme', 'dd_on_switch_theme' );

/**
 * Create the About and Contact pages and set a static front page.
 */
function dd_create_pages() {
	// Front page (uses front-page.php automatically).
	$front = get_page_by_path( 'home' );
	if ( ! $front ) {
		$front_id = wp_insert_post( array(
			'post_title'   => __( 'Home', 'digital-district' ),
			'post_name'    => 'home',
			'post_status'  => 'publish',
			'post_type'    => 'page',
			'post_content' => '',
		) );
		if ( $front_id && ! is_wp_error( $front_id ) ) {
			update_option( 'show_on_front', 'page' );
			update_option( 'page_on_front', $front_id );
		}
	}

	if ( ! get_page_by_path( 'about' ) ) {
		wp_insert_post( array(
			'post_title'   => __( 'About', 'digital-district' ),
			'post_name'    => 'about',
			'post_status'  => 'publish',
			'post_type'    => 'page',
			'post_content' => "I build systems — self-hosted infrastructure, developer tooling, and interactive software — and document how they are made. Based in Stuttgart, Germany.\n\nEdit this page in wp-admin to tell your story.",
		) );
	}

	if ( ! get_page_by_path( 'contact' ) ) {
		wp_insert_post( array(
			'post_title'    => __( 'Contact', 'digital-district' ),
			'post_name'     => 'contact',
			'post_status'   => 'publish',
			'post_type'     => 'page',
			'page_template' => 'page-contact.php',
			'post_content'  => '',
		) );
	}

	// Blog: a posts page so the native post type has a home in the nav.
	$blog = get_page_by_path( 'blog' );
	if ( ! $blog ) {
		$blog_id = wp_insert_post( array(
			'post_title'  => __( 'Blog', 'digital-district' ),
			'post_name'   => 'blog',
			'post_status' => 'publish',
			'post_type'   => 'page',
		) );
	} else {
		$blog_id = $blog->ID;
	}
	if ( $blog_id && ! is_wp_error( $blog_id ) ) {
		update_option( 'page_for_posts', $blog_id );
	}
}

/**
 * Seed guide topics (honest, from the discovery record). Statuses are honest —
 * these are planned/in-progress writing, not claimed as published.
 */
function dd_seed_guides() {
	if ( get_posts( array( 'post_type' => 'guide', 'posts_per_page' => 1, 'fields' => 'ids', 'post_status' => 'any' ) ) ) {
		return;
	}
	$guides = array(
		array(
			'Self-Hosting from a Home Server',
			'Standing up a portable web stack at home behind a Cloudflare Tunnel.',
			'In Progress', '9 min', array( 'Self-Hosting', 'Nginx', 'Docker' ),
			"<h2>Why self-host first</h2>\n<p>Owning the stack means no lock-in and a clear migration path. The same stack — Nginx, PHP 8.3, MariaDB, Redis — runs at home now and on a VPS later, so moving is an rsync and a DNS change, not a rebuild.</p>\n<h2>The stack</h2>\n<ul><li><strong>Nginx</strong> as the web server and reverse proxy.</li><li><strong>PHP 8.3</strong> and <strong>MariaDB</strong> for WordPress.</li><li><strong>Redis</strong> for object caching.</li><li><strong>Cloudflare Tunnel</strong> so the residential IP is never exposed.</li></ul>\n<h2>Keeping it portable</h2>\n<p>No panel-only assumptions live in the site itself, backups go off-site, and both domains sit behind Cloudflare — so the eventual VPS move is painless.</p>",
		),
		array(
			'Building with Hermes Agent',
			'Patterns and notes from working with the Hermes Workspace OS tooling.',
			'In Progress', '7 min', array( 'AI', 'Python' ),
			"<h2>What Hermes is</h2>\n<p>An agent-driven workspace operating layer — coordinating tools, memory, and tasks. This guide collects the patterns that have held up while building it.</p>\n<h2>Architecture first</h2>\n<p>Get the core loops right before adding capabilities: a clear task model, durable memory, and a predictable tool interface.</p>",
		),
		array(
			'WordPress Without a Page Builder',
			'A fast, maintainable WordPress site using core tooling — like this one.',
			'Published', '11 min', array( 'WordPress', 'PHP' ),
			"<h2>Hand-coded, not builder-locked</h2>\n<p>This very site is a hand-coded theme: PHP templates, CSS custom properties, and a little vanilla JavaScript. No Oxygen, no Elementor — nothing you cannot move off in an afternoon.</p>\n<h2>Custom post types do the heavy lifting</h2>\n<p>Projects, client work, guides, and reviews are custom post types with their own templates. Content lives in the database and is edited in wp-admin; the theme just renders it.</p>\n<h2>Keep SEO plugins happy</h2>\n<p>Declare <code>title-tag</code> support and never hard-code titles, so Yoast, The SEO Framework, or Rank Math can manage metadata cleanly.</p>",
		),
		array(
			'A JetBrains-Centred Workflow',
			'Getting the most out of JetBrains IDEs day to day.',
			'Planned', '6 min', array( 'JetBrains' ),
			"<h2>One IDE, many languages</h2>\n<p>Notes on running a polyglot workflow — PHP, Python, and TypeScript — from JetBrains tooling, and the shortcuts and inspections that actually save time.</p>",
		),
	);
	foreach ( $guides as $i => $g ) {
		$id = wp_insert_post( array(
			'post_type'    => 'guide',
			'post_status'  => 'publish',
			'post_title'   => $g[0],
			'post_excerpt' => $g[1],
			'post_content' => $g[5],
			'menu_order'   => $i,
		) );
		if ( $id && ! is_wp_error( $id ) ) {
			update_post_meta( $id, 'dd_status', $g[2] );
			update_post_meta( $id, 'dd_read', $g[3] );
			wp_set_object_terms( $id, $g[4], 'tech' );
		}
	}
}

/**
 * Seed Client Work with detailed SAMPLE case studies so the Work archive and
 * single layout are fully populated and ready to duplicate. These are clearly
 * marked demo projects with fictional clients — each body carries a note to
 * replace it with a real project. Nothing here is presented as a genuine
 * engagement.
 */
function dd_seed_client_templates() {
	if ( get_posts( array( 'post_type' => 'client_work', 'posts_per_page' => 1, 'fields' => 'ids', 'post_status' => 'any' ) ) ) {
		return;
	}

	$note = "\n<hr />\n<p><em>Sample case study — replace with a real project in wp-admin → Client Work.</em></p>";

	$items = array(
		array(
			'title'    => 'Aurora Studio — Portfolio & Booking Site',
			'excerpt'  => 'A fast, bookable portfolio site for a photography studio.',
			'client'   => 'Aurora Studio (sample)',
			'status'   => 'Live',
			'services' => 'Design, WordPress build, Booking integration, SEO',
			'year'     => '2026',
			'tech'     => array( 'WordPress', 'PHP', 'JavaScript' ),
			'body'     => "<h2>The brief</h2>\n<p>A photography studio needed a portfolio that loaded instantly, showed their work at full quality, and let clients request sessions without back-and-forth email.</p>\n<h2>What I built</h2>\n<p>A hand-coded WordPress theme with lazy-loaded galleries, a lightweight booking request flow wired to their mailbox, and structured data so shoots rank in local search. No page builder, so the site stays fast and easy to maintain.</p>\n<ul><li>Sub-second first paint on mobile</li><li>Self-hosted, no third-party booking fees</li><li>Editable galleries and packages in wp-admin</li></ul>\n<h2>Outcome</h2>\n<p>The studio manages everything themselves and books sessions straight from the site.</p>",
		),
		array(
			'title'    => 'Meridian Coffee — E-commerce Storefront',
			'excerpt'  => 'A subscription-ready storefront for a specialty coffee roaster.',
			'client'   => 'Meridian Coffee (sample)',
			'status'   => 'Live',
			'services' => 'WooCommerce, Subscriptions, Performance, Hosting',
			'year'     => '2025',
			'tech'     => array( 'WordPress', 'PHP', 'MariaDB', 'Redis' ),
			'body'     => "<h2>The brief</h2>\n<p>A roaster wanted to sell bags and recurring subscriptions directly, off the marketplace platforms that were taking a cut of every order.</p>\n<h2>What I built</h2>\n<p>A WooCommerce storefront with a subscription flow, Redis object caching, and a self-hosted stack behind Cloudflare. Product and subscription management stays entirely in the client's hands.</p>\n<ul><li>Recurring subscriptions with pause/skip</li><li>Cached, cart-safe pages for speed under load</li><li>Owned infrastructure — no per-sale platform fees</li></ul>\n<h2>Outcome</h2>\n<p>Direct sales and repeat subscriptions, fully under the roaster's control.</p>",
		),
		array(
			'title'    => 'Northwind Labs — SaaS Marketing Site',
			'excerpt'  => 'A marketing site and docs hub for a developer-tools startup.',
			'client'   => 'Northwind Labs (sample)',
			'status'   => 'In Progress',
			'services' => 'Design system, Front-end, Docs, Analytics',
			'year'     => '2026',
			'tech'     => array( 'TypeScript', 'JavaScript', 'PHP' ),
			'body'     => "<h2>The brief</h2>\n<p>A developer-tools startup needed a marketing site that felt technical and trustworthy, plus a documentation hub that engineers could actually navigate.</p>\n<h2>What I built</h2>\n<p>A component-driven front end built on a shared design system, an accessible docs section with fast search, and privacy-respecting analytics — no invasive trackers.</p>\n<ul><li>Reusable design-system components</li><li>Keyboard-navigable, searchable docs</li><li>Accessible to WCAG AA</li></ul>\n<h2>Outcome</h2>\n<p>In progress — launching alongside the product's public beta.</p>",
		),
	);

	foreach ( $items as $i => $c ) {
		$id = wp_insert_post( array(
			'post_type'    => 'client_work',
			'post_status'  => 'publish',
			'post_title'   => $c['title'],
			'post_excerpt' => $c['excerpt'],
			'post_content' => $c['body'] . $note,
			'menu_order'   => $i,
		) );
		if ( $id && ! is_wp_error( $id ) ) {
			update_post_meta( $id, 'dd_client', $c['client'] );
			update_post_meta( $id, 'dd_status', $c['status'] );
			update_post_meta( $id, 'dd_services', $c['services'] );
			update_post_meta( $id, 'dd_year', $c['year'] );
			wp_set_object_terms( $id, $c['tech'], 'tech' );
		}
	}
}

/**
 * Seed review subjects (honest, from the discovery record).
 */
function dd_seed_reviews() {
	if ( get_posts( array( 'post_type' => 'review', 'posts_per_page' => 1, 'fields' => 'ids', 'post_status' => 'any' ) ) ) {
		return;
	}
	$reviews = array(
		array(
			'Oxygen Builder 6', 'Oxygen Builder 6', 'In Progress', '4',
			"<p>A hands-on review written while building a real portfolio with it — the companion piece to the tutorial series.</p>\n<h2>First impressions</h2>\n<p>Version 6 is a significant step up. The component model is more coherent and the output is cleaner than earlier releases.</p>\n<h2>Where it fits</h2>\n<p>Great for template-owned layouts; the architecture contract still keeps data and logic in a plugin, not in builder code. Full verdict once the build ships.</p>",
		),
		array(
			'IntelliJ IDEA Ultimate', 'IntelliJ IDEA Ultimate', 'Planned', '',
			"<p>The Ultimate edition for polyglot, full-stack development — planned review covering PHP, Python, and web tooling in one IDE.</p>",
		),
		array(
			'Antigravity', 'Antigravity', 'Planned', '',
			"<p>First impressions of the Antigravity editor — planned once it has had a proper run in a real project.</p>",
		),
		array(
			'Codex', 'Codex', 'Planned', '',
			"<p>Using Codex in a real development loop — planned review focusing on where it helps and where it gets in the way.</p>",
		),
	);
	foreach ( $reviews as $i => $r ) {
		$id = wp_insert_post( array(
			'post_type'    => 'review',
			'post_status'  => 'publish',
			'post_title'   => $r[0],
			'post_excerpt' => sprintf( __( 'A hands-on review of %s.', 'digital-district' ), $r[0] ),
			'post_content' => $r[4],
			'menu_order'   => $i,
		) );
		if ( $id && ! is_wp_error( $id ) ) {
			update_post_meta( $id, 'dd_subject', $r[1] );
			update_post_meta( $id, 'dd_status', $r[2] );
			if ( '' !== $r[3] ) {
				update_post_meta( $id, 'dd_rating', $r[3] );
			}
		}
	}
}

/**
 * Seed the owner's honest personal projects (only if none exist yet).
 */
function dd_seed_projects() {
	$existing = get_posts( array( 'post_type' => 'project', 'posts_per_page' => 1, 'fields' => 'ids', 'post_status' => 'any' ) );
	if ( ! empty( $existing ) ) {
		return;
	}

	$projects = array(
		array( 'AI Operating System', 'An agent-driven layer that orchestrates tools, memory, and tasks.', 'In Progress', array( 'AI', 'Agents', 'Systems' ) ),
		array( 'Self-Hosted Knowledge Hub', 'A private, searchable knowledge base on my own hardware.', 'In Progress', array( 'Self-Hosting', 'Search', 'Docker' ) ),
		array( 'Interactive Portfolio', 'A cyberpunk portfolio rendered in the browser — this build.', 'In Progress', array( 'WordPress', 'PHP', 'Frontend' ) ),
		array( 'Tactical Streaming Interface', 'An overlay and control surface for live streaming.', 'Prototype', array( 'Realtime', 'UI', 'Tooling' ) ),
		array( 'Home Server Platform', 'The self-hosting foundation: Nginx, PHP, MariaDB, Redis, Cloudflare.', 'In Progress', array( 'Self-Hosting', 'Infra', 'Cloudflare' ) ),
		array( 'AI Research Workspace', 'A workspace for running and comparing AI research experiments.', 'Research', array( 'AI', 'Research' ) ),
	);

	foreach ( $projects as $i => $p ) {
		$id = wp_insert_post( array(
			'post_type'    => 'project',
			'post_status'  => 'publish',
			'post_title'   => $p[0],
			'post_excerpt' => $p[1],
			'post_content' => $p[1] . "\n\nAdd a full write-up, screenshots, and links in wp-admin.",
			'menu_order'   => $i,
		) );
		if ( $id && ! is_wp_error( $id ) ) {
			update_post_meta( $id, 'dd_status', $p[2] );
			wp_set_object_terms( $id, $p[3], 'tech' );
		}
	}
}

/**
 * Build a primary menu with clear, user-friendly labels.
 */
function dd_build_primary_menu() {
	$menu_name = 'Primary';
	$menu      = wp_get_nav_menu_object( $menu_name );
	if ( ! $menu ) {
		$menu_id = wp_create_nav_menu( $menu_name );
	} else {
		$menu_id = $menu->term_id;
		// Don't rebuild if it already has items.
		if ( wp_get_nav_menu_items( $menu_id ) ) {
			return;
		}
	}
	if ( is_wp_error( $menu_id ) ) {
		return;
	}

	wp_update_nav_menu_item( $menu_id, 0, array(
		'menu-item-title' => __( 'Home', 'digital-district' ),
		'menu-item-url'   => home_url( '/' ),
		'menu-item-status'=> 'publish',
		'menu-item-type'  => 'custom',
	) );
	wp_update_nav_menu_item( $menu_id, 0, array(
		'menu-item-title'     => __( 'Work', 'digital-district' ),
		'menu-item-object'    => 'client_work',
		'menu-item-type'      => 'post_type_archive',
		'menu-item-status'    => 'publish',
	) );
	wp_update_nav_menu_item( $menu_id, 0, array(
		'menu-item-title'  => __( 'Projects', 'digital-district' ),
		'menu-item-object' => 'project',
		'menu-item-type'   => 'post_type_archive',
		'menu-item-status' => 'publish',
	) );
	wp_update_nav_menu_item( $menu_id, 0, array(
		'menu-item-title'  => __( 'Guides', 'digital-district' ),
		'menu-item-object' => 'guide',
		'menu-item-type'   => 'post_type_archive',
		'menu-item-status' => 'publish',
	) );
	wp_update_nav_menu_item( $menu_id, 0, array(
		'menu-item-title'  => __( 'Reviews', 'digital-district' ),
		'menu-item-object' => 'review',
		'menu-item-type'   => 'post_type_archive',
		'menu-item-status' => 'publish',
	) );
	$blog_id = (int) get_option( 'page_for_posts' );
	if ( $blog_id ) {
		wp_update_nav_menu_item( $menu_id, 0, array(
			'menu-item-title'     => __( 'Blog', 'digital-district' ),
			'menu-item-object'    => 'page',
			'menu-item-object-id' => $blog_id,
			'menu-item-type'      => 'post_type',
			'menu-item-status'    => 'publish',
		) );
	}
	$about = get_page_by_path( 'about' );
	if ( $about ) {
		wp_update_nav_menu_item( $menu_id, 0, array(
			'menu-item-title'     => __( 'About', 'digital-district' ),
			'menu-item-object'    => 'page',
			'menu-item-object-id' => $about->ID,
			'menu-item-type'      => 'post_type',
			'menu-item-status'    => 'publish',
		) );
	}
	$contact = get_page_by_path( 'contact' );
	if ( $contact ) {
		wp_update_nav_menu_item( $menu_id, 0, array(
			'menu-item-title'     => __( 'Contact', 'digital-district' ),
			'menu-item-object'    => 'page',
			'menu-item-object-id' => $contact->ID,
			'menu-item-type'      => 'post_type',
			'menu-item-status'    => 'publish',
		) );
	}

	$locations            = get_theme_mod( 'nav_menu_locations', array() );
	$locations['primary'] = $menu_id;
	set_theme_mod( 'nav_menu_locations', $locations );
}
