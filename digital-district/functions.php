<?php
/**
 * Digital District theme setup.
 *
 * @package DigitalDistrict
 */

defined( 'ABSPATH' ) || exit;

define( 'DD_VERSION', '1.0.0' );

if ( ! function_exists( 'dd_setup' ) ) {
	/**
	 * Theme supports and menus. `title-tag` is essential so WordPress SEO
	 * plugins (Yoast, The SEO Framework, Rank Math) can manage the document
	 * title without the theme fighting them.
	 */
	function dd_setup() {
		load_theme_textdomain( 'digital-district', get_template_directory() . '/languages' );

		add_theme_support( 'title-tag' );
		add_theme_support( 'automatic-feed-links' );
		add_theme_support( 'post-thumbnails' );
		add_theme_support( 'responsive-embeds' );
		add_theme_support( 'align-wide' );
		add_theme_support( 'custom-logo', array(
			'height'      => 48,
			'width'       => 48,
			'flex-height' => true,
			'flex-width'  => true,
		) );
		add_theme_support( 'html5', array(
			'search-form', 'comment-form', 'comment-list', 'gallery', 'caption', 'style', 'script',
		) );

		// Feature images at a portfolio-friendly ratio.
		add_image_size( 'dd_card', 900, 560, true );
		add_image_size( 'dd_cover', 1600, 900, true );

		register_nav_menus( array(
			'primary' => __( 'Primary Menu', 'digital-district' ),
		) );
	}
}
add_action( 'after_setup_theme', 'dd_setup' );

/**
 * Front-end styles and scripts.
 */
function dd_assets() {
	$dir = get_template_directory_uri();

	// Self-describing Google Fonts request (swap for self-hosted if preferred).
	wp_enqueue_style(
		'dd-fonts',
		'https://fonts.googleapis.com/css2?family=Inter:wght@400;600&family=JetBrains+Mono:wght@400;500&family=Space+Grotesk:wght@500;700&display=swap',
		array(),
		null
	);

	wp_enqueue_style( 'dd-tokens', $dir . '/assets/css/tokens.css', array(), DD_VERSION );
	wp_enqueue_style( 'dd-main', $dir . '/assets/css/main.css', array( 'dd-tokens' ), DD_VERSION );
	wp_enqueue_style( 'dd-style', get_stylesheet_uri(), array( 'dd-main' ), DD_VERSION );

	wp_enqueue_script( 'dd-main', $dir . '/assets/js/main.js', array(), DD_VERSION, true );

	if ( is_singular() && comments_open() && get_option( 'thread_comments' ) ) {
		wp_enqueue_script( 'comment-reply' );
	}
}
add_action( 'wp_enqueue_scripts', 'dd_assets' );

/**
 * Preconnect to the font host for a faster first paint.
 */
function dd_resource_hints( $hints, $relation ) {
	if ( 'preconnect' === $relation ) {
		$hints[] = array( 'href' => 'https://fonts.gstatic.com', 'crossorigin' );
	}
	return $hints;
}
add_filter( 'wp_resource_hints', 'dd_resource_hints', 10, 2 );

/**
 * Add a no-js class so CSS can reveal content when JS is unavailable; main.js
 * removes it immediately when it runs.
 */
function dd_html_no_js() {
	echo '<script>document.documentElement.classList.add("no-js");</script>' . "\n";
}
add_action( 'wp_head', 'dd_html_no_js', 1 );

/**
 * Body classes: expose whether the primary menu is set for fallbacks.
 */
function dd_body_classes( $classes ) {
	if ( ! is_active_sidebar( 'primary' ) ) {
		$classes[] = 'dd-theme';
	}
	return $classes;
}
add_filter( 'body_class', 'dd_body_classes' );

require get_template_directory() . '/inc/post-types.php';
require get_template_directory() . '/inc/meta-boxes.php';
require get_template_directory() . '/inc/template-tags.php';
require get_template_directory() . '/inc/contact.php';
require get_template_directory() . '/inc/github-sync.php';
require get_template_directory() . '/inc/setup-content.php';
