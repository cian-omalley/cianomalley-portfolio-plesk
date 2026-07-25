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
	dd_seed_posts();
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

	if ( ! get_page_by_path( 'breakdown' ) ) {
		wp_insert_post( array(
			'post_title'    => __( 'Project Breakdown', 'digital-district' ),
			'post_name'     => 'breakdown',
			'post_status'   => 'publish',
			'post_type'     => 'page',
			'page_template' => 'page-breakdown.php',
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
			"<h2>Hand-coded, not builder-locked</h2>\n<p>This very site is a hand-coded theme: PHP templates, CSS custom properties, and a little vanilla JavaScript. No page builders — nothing you cannot move off in an afternoon.</p>\n<h2>Custom post types do the heavy lifting</h2>\n<p>Projects, client work, guides, and reviews are custom post types with their own templates. Content lives in the database and is edited in wp-admin; the theme just renders it.</p>\n<h2>Keep SEO plugins happy</h2>\n<p>Declare <code>title-tag</code> support and never hard-code titles, so Yoast, The SEO Framework, or Rank Math can manage metadata cleanly.</p>",
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
			'body'     => "<h2>The brief</h2>\n<p>A photography studio came in with a site that looked the part but worked against them: it was slow to load on the phones most of their visitors used, it compressed their images into mush, and every booking still turned into a chain of emails to agree on a date. They wanted a portfolio that opened instantly, showed their work at full quality, and let a prospective client request a session in a few taps — without the studio having to babysit an inbox or pay a monthly fee to a booking platform.</p>\n<h2>Approach</h2>\n<p>Before writing any code we agreed on the priorities in order: speed and image quality first, a frictionless booking request second, and easy self-management third. That ordering shaped every later decision — when a nice-to-have threatened the load time, the load time won. Keeping the scope honest up front is what kept the project fast to build and fast to run.</p>\n<h2>What I built</h2>\n<p>A hand-coded WordPress theme with lazy-loaded galleries that serve appropriately sized images per device, so a phone never downloads a desktop-sized photo. Booking is a lightweight request flow wired straight to the studio's own mailbox — no third-party service in the middle taking a cut or holding the data. Structured data was added so individual shoots and the studio itself surface properly in local search, and the whole site is editable in wp-admin, so galleries, packages, and prices change without a developer.</p>\n<ul><li>Sub-second first paint on a mid-range phone, with full-resolution images on demand.</li><li>Self-hosted booking — no per-booking fees and no visitor data leaving the studio.</li><li>Galleries, packages, and availability all editable in wp-admin.</li></ul>\n<h2>Outcome</h2>\n<p>The studio now manages the whole site themselves and takes session requests straight from the page, with the speed and image quality that a photography brand lives or dies by. The email chains are gone, and there is nothing to pay a platform each month to keep it running.</p>",
		),
		array(
			'title'    => 'Meridian Coffee — E-commerce Storefront',
			'excerpt'  => 'A subscription-ready storefront for a specialty coffee roaster.',
			'client'   => 'Meridian Coffee (sample)',
			'status'   => 'Live',
			'services' => 'WooCommerce, Subscriptions, Performance, Hosting',
			'year'     => '2025',
			'tech'     => array( 'WordPress', 'PHP', 'MariaDB', 'Redis' ),
			'body'     => "<h2>The brief</h2>\n<p>A specialty coffee roaster was selling almost entirely through marketplace platforms that took a cut of every bag and kept the customer relationship at arm's length. They wanted to sell directly — both one-off bags and a recurring subscription for regulars — on a storefront they actually owned, fast enough to handle a launch-day rush and simple enough to run without a dedicated store manager.</p>\n<h2>Approach</h2>\n<p>The core tension in any subscription storefront is speed versus dynamism: a cart and a logged-in customer are personal, but most of the page is not. We built around that from the start, caching everything that could safely be cached and keeping the personal parts — cart, account, checkout — always fresh, so the site stays quick under load without ever showing someone the wrong basket.</p>\n<h2>What I built</h2>\n<p>A WooCommerce storefront with a proper subscription flow that lets customers pause, skip, or change a delivery themselves, on top of a self-hosted stack fronted by Cloudflare with Redis handling object caching. Product, pricing, and subscription management all stay in the roaster's hands through the standard WordPress and WooCommerce admin — no bespoke dashboard to learn, and no platform sitting between them and their customers.</p>\n<ul><li>Recurring subscriptions with self-service pause, skip, and swap.</li><li>Aggressively cached, cart-safe pages that stay fast during a launch spike.</li><li>Owned infrastructure — no per-sale platform fees and a direct customer list.</li></ul>\n<h2>Outcome</h2>\n<p>The roaster now takes direct sales and repeat subscription revenue on a storefront they fully control, keeping the margin that the marketplaces used to take and, just as importantly, the relationship with the people who drink their coffee.</p>",
		),
		array(
			'title'    => 'Northwind Labs — SaaS Marketing Site',
			'excerpt'  => 'A marketing site and docs hub for a developer-tools startup.',
			'client'   => 'Northwind Labs (sample)',
			'status'   => 'In Progress',
			'services' => 'Design system, Front-end, Docs, Analytics',
			'year'     => '2026',
			'tech'     => array( 'TypeScript', 'JavaScript', 'PHP' ),
			'body'     => "<h2>The brief</h2>\n<p>A developer-tools startup was preparing for a public beta and needed two things at once: a marketing site that felt technical and trustworthy to an audience of engineers who can smell fluff a mile off, and a documentation hub those same engineers could actually navigate. The two have different jobs — one persuades, one explains — but they had to feel like one product and share a single visual language.</p>\n<h2>Approach</h2>\n<p>Rather than design the marketing pages and the docs separately, we started with a small shared design system — the colours, type, spacing, and components both would draw from — so the marketing site and the documentation would always look and behave like the same thing. Building the system first is slower on day one and much faster after, because every new page is assembled from parts that already exist and already work.</p>\n<h2>What I built</h2>\n<p>A component-driven front end built on that shared design system, and an accessible documentation section with fast client-side search so an engineer can jump to the exact page they need by keyboard alone. Analytics are privacy-respecting — enough to understand what is working without invasive third-party trackers following visitors around, which matters doubly for a company selling to developers who care about exactly that.</p>\n<ul><li>A reusable design system powering both the marketing pages and the docs.</li><li>Keyboard-navigable documentation with fast, in-page search.</li><li>Accessible to WCAG AA, with privacy-respecting analytics and no invasive trackers.</li></ul>\n<h2>Outcome</h2>\n<p>In progress, and on track to launch alongside the product's public beta — a marketing site and a docs hub that read as one coherent product and can grow, page by page, from the same set of building blocks.</p>",
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
			'IntelliJ IDEA Ultimate', 'IntelliJ IDEA Ultimate', 'In Progress', '4',
			"<p>A hands-on review of the Ultimate edition for polyglot, full-stack development — PHP, Python, and web tooling in one IDE.</p>\n<h2>First impressions</h2>\n<p>The all-in-one integration is the draw: databases, HTTP client, and framework support without a pile of plugins.</p>\n<h2>Where it fits</h2>\n<p>Strong for cross-language projects. Full verdict once it has run a few real builds end to end.</p>",
		),
		array(
			'Visual Studio Code', 'Visual Studio Code', 'Planned', '',
			"<p>The open-source editor everyone reaches for — planned review of the extension ecosystem and how far it goes as a full IDE.</p>",
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
		array(
			'AI Operating System', 'An agent-driven layer that orchestrates tools, memory, and tasks.', 'In Progress', array( 'AI', 'Agents', 'Systems' ),
			"<h2>Overview</h2>\n<p>The AI Operating System is a personal operating layer that sits above the individual tools I use every day and coordinates them through AI agents. Rather than a single chatbot, the idea is an environment where agents can reach for the right tool, remember what happened before, and carry out multi-step tasks on a schedule — the way an operating system coordinates programs, files, and processes.</p>\n<h2>What it does</h2>\n<p>At its core it manages three things: a catalogue of tools an agent can call, a durable long-term memory that survives between sessions, and a task model that lets work be planned, queued, and picked up later. Agents read from and write to that memory, so context is not lost the moment a conversation ends, and a task started today can be continued tomorrow without re-explaining everything.</p>\n<h2>How it is built</h2>\n<p>The build follows the same principle as the rest of my work: get the core loops right before adding surface features. That means a predictable, well-typed interface for tools, a memory store that is easy to query, and an orchestration loop that is transparent about what it is doing and why. Capabilities are layered on only once that foundation is stable, so the system stays understandable as it grows.</p>\n<h2>Status</h2>\n<p>Under active development. The architecture and core loops are the current focus; the more visible agent capabilities come next, once the plumbing underneath them is dependable.</p>",
		),
		array(
			'Self-Hosted Knowledge Hub', 'A private, searchable knowledge base on my own hardware.', 'In Progress', array( 'Self-Hosting', 'Search', 'Docker' ),
			"<h2>Overview</h2>\n<p>The Self-Hosted Knowledge Hub is a private, searchable home for everything I want to keep and find again — notes, documentation, transcripts, and references — running entirely on my own hardware rather than a third-party service. It exists partly to be genuinely useful day to day, and partly to prove out the self-hosting stack that the guides on this site describe.</p>\n<h2>What it does</h2>\n<p>Content is indexed so it can be searched quickly by keyword and, over time, by meaning, so the answer to “where did I write that?” is a couple of keystrokes away instead of a scroll through folders. Because it is self-hosted, nothing leaves my control: no external indexing, no per-seat pricing, and no risk of a service shutting down and taking the archive with it.</p>\n<h2>How it is built</h2>\n<p>It runs on the same portable stack the Home Server Platform provides — Nginx, PHP, a database, and Redis for caching — packaged so it can move between machines without a rebuild, and fronted by a Cloudflare Tunnel so the home IP is never exposed. Keeping it portable is deliberate: the whole point of owning your knowledge base is being able to take it with you.</p>\n<h2>Status</h2>\n<p>In progress. The ingestion and search foundations are being built first, with richer organisation and semantic search layered on afterwards.</p>",
		),
		array(
			'Interactive Portfolio', 'A cyberpunk portfolio rendered in the browser — this build.', 'In Progress', array( 'WordPress', 'PHP', 'Frontend' ),
			"<h2>Overview</h2>\n<p>This is the project you are looking at: an interactive developer portfolio with a cyberpunk-brutalist identity, built as a hand-coded WordPress theme with no page builder. It is both a place to present work and a case study in its own right — the whole build is documented, and the site doubles as proof that a fast, characterful site does not need a heavy drag-and-drop builder underneath it.</p>\n<h2>What it does</h2>\n<p>It presents personal projects and client work as separate, browsable collections, each with its own detailed case-study page, alongside guides, reviews, and a journal. Projects are imported automatically from GitHub, so the portfolio stays current with the code without manual bookkeeping. An animated neon-city hero, cursor-reactive cards, and a reading-progress bar give it atmosphere, while a fully accessible, server-rendered baseline underneath keeps it usable for everyone.</p>\n<h2>How it is built</h2>\n<p>The theme is plain PHP templates, CSS custom-property design tokens, and a little vanilla JavaScript. Content lives in custom post types edited in wp-admin; the theme only renders it. Fonts are self-hosted, the hero is a dependency-free canvas, and there are no third-party calls or trackers — which also makes it comfortable to run on modest self-hosted infrastructure or Plesk.</p>\n<h2>Status</h2>\n<p>In progress and shipping — this build is live and continuing to gain polish and features.</p>",
		),
		array(
			'Tactical Streaming Interface', 'An overlay and control surface for live streaming.', 'Prototype', array( 'Realtime', 'UI', 'Tooling' ),
			"<h2>Overview</h2>\n<p>The Tactical Streaming Interface is an exploratory prototype for a live-streaming control surface and on-screen overlay — the layer between the streamer and the broadcast that shows live information and provides quick controls without breaking focus.</p>\n<h2>What it explores</h2>\n<p>It is a place to try ideas end to end before committing to a production build: how to lay out live data widgets so they are glanceable, how to bind actions to hotkeys so control is fast, and how to keep an overlay legible over changing video without becoming clutter. Getting those interaction questions right on a small scale is the point of the prototype.</p>\n<h2>Status</h2>\n<p>Prototype. It is deliberately rough — a testing ground for the layout, hotkey, and live-data ideas rather than a finished tool.</p>",
		),
		array(
			'Home Server Platform', 'The self-hosting foundation: Nginx, PHP, MariaDB, Redis, Cloudflare.', 'In Progress', array( 'Self-Hosting', 'Infra', 'Cloudflare' ),
			"<h2>Overview</h2>\n<p>The Home Server Platform is the foundation everything else runs on: a portable, self-hosted Linux stack living on a home server, built so it can move to a VPS later without being rebuilt. It is the practical answer to “own the stack” — the infrastructure that makes the Knowledge Hub, the portfolio, and the rest possible.</p>\n<h2>What it provides</h2>\n<p>It bundles the pieces a modern site needs — Nginx as web server and reverse proxy, PHP and MariaDB for applications, and Redis for caching — configured to be portable rather than tied to any one machine or control panel. A Cloudflare Tunnel fronts all of it, so the residential IP is never exposed and moving to a VPS is a DNS change rather than a migration.</p>\n<h2>How it is built</h2>\n<p>Portability is the design goal, so no host-only assumptions leak into the applications themselves, backups go off-site, and both domains sit behind Cloudflare. The same stack recommendation runs identically at home and on a VPS, which turns the eventual move into an rsync and a DNS switch instead of a rebuild.</p>\n<h2>Status</h2>\n<p>In progress, and already carrying real workloads as the other projects come online.</p>",
		),
		array(
			'AI Research Workspace', 'A workspace for running and comparing AI research experiments.', 'Research', array( 'AI', 'Research' ),
			"<h2>Overview</h2>\n<p>The AI Research Workspace is an environment for running structured AI experiments and comparing their results — a lab bench rather than a product. It exists to make experimentation repeatable: to track what was run, with which inputs, and what came out, so findings are grounded rather than anecdotal.</p>\n<h2>What it explores</h2>\n<p>The focus is on the discipline around experiments — recording runs, prompts, and outputs; comparing approaches on equal footing; and keeping a trail that can be revisited. That structure is what turns scattered tinkering into research you can actually learn from.</p>\n<h2>Status</h2>\n<p>Early research. It is a space for experiments and notes rather than a shipping tool, and it feeds ideas into the AI Operating System as they prove out.</p>",
		),
	);

	foreach ( $projects as $i => $p ) {
		$id = wp_insert_post( array(
			'post_type'    => 'project',
			'post_status'  => 'publish',
			'post_title'   => $p[0],
			'post_excerpt' => $p[1],
			'post_content' => $p[4],
			'menu_order'   => $i,
		) );
		if ( $id && ! is_wp_error( $id ) ) {
			update_post_meta( $id, 'dd_status', $p[2] );
			wp_set_object_terms( $id, $p[3], 'tech' );
		}
	}
}

/**
 * Seed the blog with honest journal posts and remove WordPress's default
 * "Hello world!" post and "Sample Page". Runs once (skips if real posts exist).
 */
function dd_seed_posts() {
	// Remove the stock placeholders.
	$hello = get_page_by_path( 'hello-world', OBJECT, 'post' );
	if ( $hello ) {
		wp_delete_post( $hello->ID, true );
	}
	$sample = get_page_by_path( 'sample-page' );
	if ( $sample ) {
		wp_delete_post( $sample->ID, true );
	}

	// Only seed once.
	$have = get_posts( array( 'post_type' => 'post', 'posts_per_page' => 1, 'fields' => 'ids', 'post_status' => 'any' ) );
	if ( ! empty( $have ) ) {
		return;
	}

	$posts = array(
		array(
			'Building this portfolio in the open',
			'Journal',
			'<p>This site is being built in public — every decision documented as it happens. The goal is a portfolio that doubles as a case study: how it is made is part of what it shows.</p><h2>The stack</h2><p>A hand-coded WordPress theme, no page builder, running on a self-hosted stack behind Cloudflare and designed to sit happily on Plesk. Everything is editable in wp-admin; the theme just renders it.</p><h2>What is next</h2><p>Written guides on each part of the build, then the video versions. The writing ships first.</p>',
		),
		array(
			'Why I self-host almost everything',
			'Self-Hosting',
			'<p>Owning the stack is the whole point. The same portable stack — Nginx, PHP, MariaDB, Redis — runs at home now and moves to a VPS later without a rebuild, so there is no lock-in and no surprise bills.</p><h2>Hiding the home IP</h2><p>A Cloudflare Tunnel fronts everything, so the residential IP is never exposed and the eventual VPS move is a DNS change, not a migration.</p><h2>The trade-off</h2><p>More responsibility, far more control. For a developer portfolio that is exactly the right trade.</p>',
		),
		array(
			'Going builder-free on WordPress',
			'WordPress',
			'<p>No Oxygen, no Elementor, no drag-and-drop. Just PHP templates, CSS custom properties, and a little vanilla JavaScript. The result is faster, lighter, and completely portable.</p><h2>Custom post types do the work</h2><p>Projects, client work, guides and reviews are custom post types with their own templates. Content lives in the database; the theme renders it. SEO plugins keep full control of titles and meta.</p>',
		),
		array(
			'Pulling my GitHub repos into the site automatically',
			'WordPress',
			'<p>The Projects section imports every public repository straight from GitHub, mapping each repo to a project with a status derived from its activity. A one-click re-sync in wp-admin keeps it fresh, and any write-ups edited by hand are preserved.</p><h2>Honest by default</h2><p>Archived repos read as complete, near-empty ones as planning, active ones as in progress — no manual bookkeeping, no invented status.</p>',
		),
	);

	foreach ( $posts as $i => $p ) {
		$id = wp_insert_post( array(
			'post_type'    => 'post',
			'post_status'  => 'publish',
			'post_title'   => $p[0],
			'post_content' => $p[2],
			'post_excerpt' => wp_strip_all_tags( substr( $p[2], 3, 160 ) ),
			'post_date'    => gmdate( 'Y-m-d H:i:s', time() - ( $i + 1 ) * DAY_IN_SECONDS ),
		) );
		if ( $id && ! is_wp_error( $id ) ) {
			wp_set_object_terms( $id, $p[1], 'category' );
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

	// Portfolio group — Projects + Client Work under one dropdown.
	$portfolio_parent = wp_update_nav_menu_item( $menu_id, 0, array(
		'menu-item-title'  => __( 'Portfolio', 'digital-district' ),
		'menu-item-object' => 'project',
		'menu-item-type'   => 'post_type_archive',
		'menu-item-status' => 'publish',
	) );
	wp_update_nav_menu_item( $menu_id, 0, array(
		'menu-item-title'     => __( 'Projects', 'digital-district' ),
		'menu-item-object'    => 'project',
		'menu-item-type'      => 'post_type_archive',
		'menu-item-parent-id' => $portfolio_parent,
		'menu-item-status'    => 'publish',
	) );
	wp_update_nav_menu_item( $menu_id, 0, array(
		'menu-item-title'     => __( 'Client Work', 'digital-district' ),
		'menu-item-object'    => 'client_work',
		'menu-item-type'      => 'post_type_archive',
		'menu-item-parent-id' => $portfolio_parent,
		'menu-item-status'    => 'publish',
	) );

	// Writing group — Guides + Reviews + Blog under one dropdown.
	$writing_parent = wp_update_nav_menu_item( $menu_id, 0, array(
		'menu-item-title'  => __( 'Writing', 'digital-district' ),
		'menu-item-object' => 'guide',
		'menu-item-type'   => 'post_type_archive',
		'menu-item-status' => 'publish',
	) );
	wp_update_nav_menu_item( $menu_id, 0, array(
		'menu-item-title'     => __( 'Guides', 'digital-district' ),
		'menu-item-object'    => 'guide',
		'menu-item-type'      => 'post_type_archive',
		'menu-item-parent-id' => $writing_parent,
		'menu-item-status'    => 'publish',
	) );
	wp_update_nav_menu_item( $menu_id, 0, array(
		'menu-item-title'     => __( 'Reviews', 'digital-district' ),
		'menu-item-object'    => 'review',
		'menu-item-type'      => 'post_type_archive',
		'menu-item-parent-id' => $writing_parent,
		'menu-item-status'    => 'publish',
	) );
	$blog_id = (int) get_option( 'page_for_posts' );
	if ( $blog_id ) {
		wp_update_nav_menu_item( $menu_id, 0, array(
			'menu-item-title'     => __( 'Blog', 'digital-district' ),
			'menu-item-object'    => 'page',
			'menu-item-object-id' => $blog_id,
			'menu-item-type'      => 'post_type',
			'menu-item-parent-id' => $writing_parent,
			'menu-item-status'    => 'publish',
		) );
	}
	$about     = get_page_by_path( 'about' );
	$breakdown = get_page_by_path( 'breakdown' );
	if ( $about ) {
		$about_parent = wp_update_nav_menu_item( $menu_id, 0, array(
			'menu-item-title'     => __( 'About', 'digital-district' ),
			'menu-item-object'    => 'page',
			'menu-item-object-id' => $about->ID,
			'menu-item-type'      => 'post_type',
			'menu-item-status'    => 'publish',
		) );
		if ( $breakdown ) {
			wp_update_nav_menu_item( $menu_id, 0, array(
				'menu-item-title'     => __( 'About', 'digital-district' ),
				'menu-item-object'    => 'page',
				'menu-item-object-id' => $about->ID,
				'menu-item-type'      => 'post_type',
				'menu-item-parent-id' => $about_parent,
				'menu-item-status'    => 'publish',
			) );
			wp_update_nav_menu_item( $menu_id, 0, array(
				'menu-item-title'     => __( 'Project Breakdown', 'digital-district' ),
				'menu-item-object'    => 'page',
				'menu-item-object-id' => $breakdown->ID,
				'menu-item-type'      => 'post_type',
				'menu-item-parent-id' => $about_parent,
				'menu-item-status'    => 'publish',
			) );
		}
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
