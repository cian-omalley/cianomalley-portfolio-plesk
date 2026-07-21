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
		array( 'Self-Hosting from a Home Server', 'Standing up a portable web stack at home behind a Cloudflare Tunnel.', 'Planned', array( 'Self-Hosting', 'Nginx', 'Docker' ) ),
		array( 'Building with Hermes Agent', 'Patterns and notes from working with the Hermes Agent tooling.', 'Planned', array( 'AI' ) ),
		array( 'WordPress Without a Page Builder', 'A fast, maintainable WordPress site using core tooling — like this one.', 'In Progress', array( 'WordPress', 'PHP' ) ),
		array( 'A JetBrains-Centred Workflow', 'Getting the most out of JetBrains IDEs day to day.', 'Planned', array( 'JetBrains' ) ),
	);
	foreach ( $guides as $i => $g ) {
		$id = wp_insert_post( array(
			'post_type'    => 'guide',
			'post_status'  => 'publish',
			'post_title'   => $g[0],
			'post_excerpt' => $g[1],
			'post_content' => $g[1] . "\n\nWrite the full guide in wp-admin.",
			'menu_order'   => $i,
		) );
		if ( $id && ! is_wp_error( $id ) ) {
			update_post_meta( $id, 'dd_status', $g[2] );
			wp_set_object_terms( $id, $g[3], 'tech' );
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
		array( 'Oxygen Builder 6', 'Oxygen Builder 6', 'In Progress', '' ),
		array( 'IntelliJ IDEA Ultimate', 'IntelliJ IDEA Ultimate', 'Planned', '' ),
		array( 'Antigravity', 'Antigravity', 'Planned', '' ),
		array( 'Codex', 'Codex', 'Planned', '' ),
	);
	foreach ( $reviews as $i => $r ) {
		$id = wp_insert_post( array(
			'post_type'    => 'review',
			'post_status'  => 'publish',
			'post_title'   => $r[0],
			'post_excerpt' => sprintf( __( 'A hands-on review of %s.', 'digital-district' ), $r[0] ),
			'post_content' => sprintf( __( 'A hands-on review of %s — written from real use.', 'digital-district' ), $r[0] ) . "\n\nWrite the full review in wp-admin.",
			'menu_order'   => $i,
		) );
		if ( $id && ! is_wp_error( $id ) ) {
			update_post_meta( $id, 'dd_subject', $r[1] );
			update_post_meta( $id, 'dd_status', $r[2] );
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
