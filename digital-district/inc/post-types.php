<?php
/**
 * Custom post types and taxonomies.
 *
 * - project      : personal / open-source builds (the GitHub-style projects).
 * - client_work  : websites and apps built for clients.
 * - tech         : shared technology taxonomy for both.
 *
 * @package DigitalDistrict
 */

defined( 'ABSPATH' ) || exit;

function dd_register_content() {
	register_post_type( 'project', array(
		'labels' => array(
			'name'               => __( 'Projects', 'digital-district' ),
			'singular_name'      => __( 'Project', 'digital-district' ),
			'add_new_item'       => __( 'Add New Project', 'digital-district' ),
			'edit_item'          => __( 'Edit Project', 'digital-district' ),
			'menu_name'          => __( 'Projects', 'digital-district' ),
		),
		'public'       => true,
		'has_archive'  => true,
		'menu_icon'    => 'dashicons-portfolio',
		'menu_position'=> 22,
		'rewrite'      => array( 'slug' => 'projects', 'with_front' => false ),
		'supports'     => array( 'title', 'editor', 'excerpt', 'thumbnail', 'revisions', 'custom-fields', 'page-attributes' ),
		'show_in_rest' => true,
	) );

	register_post_type( 'client_work', array(
		'labels' => array(
			'name'               => __( 'Client Work', 'digital-district' ),
			'singular_name'      => __( 'Client Project', 'digital-district' ),
			'add_new_item'       => __( 'Add New Client Project', 'digital-district' ),
			'edit_item'          => __( 'Edit Client Project', 'digital-district' ),
			'menu_name'          => __( 'Client Work', 'digital-district' ),
		),
		'public'       => true,
		'has_archive'  => true,
		'menu_icon'    => 'dashicons-businessman',
		'menu_position'=> 23,
		'rewrite'      => array( 'slug' => 'work', 'with_front' => false ),
		'supports'     => array( 'title', 'editor', 'excerpt', 'thumbnail', 'revisions', 'custom-fields', 'page-attributes' ),
		'show_in_rest' => true,
	) );

	register_post_type( 'guide', array(
		'labels' => array(
			'name'          => __( 'Guides', 'digital-district' ),
			'singular_name' => __( 'Guide', 'digital-district' ),
			'add_new_item'  => __( 'Add New Guide', 'digital-district' ),
			'edit_item'     => __( 'Edit Guide', 'digital-district' ),
			'menu_name'     => __( 'Guides', 'digital-district' ),
		),
		'public'       => true,
		'has_archive'  => true,
		'menu_icon'    => 'dashicons-book-alt',
		'menu_position'=> 24,
		'rewrite'      => array( 'slug' => 'guides', 'with_front' => false ),
		'supports'     => array( 'title', 'editor', 'excerpt', 'thumbnail', 'revisions', 'comments', 'author' ),
		'show_in_rest' => true,
	) );

	register_post_type( 'review', array(
		'labels' => array(
			'name'          => __( 'Reviews', 'digital-district' ),
			'singular_name' => __( 'Review', 'digital-district' ),
			'add_new_item'  => __( 'Add New Review', 'digital-district' ),
			'edit_item'     => __( 'Edit Review', 'digital-district' ),
			'menu_name'     => __( 'Reviews', 'digital-district' ),
		),
		'public'       => true,
		'has_archive'  => true,
		'menu_icon'    => 'dashicons-star-half',
		'menu_position'=> 25,
		'rewrite'      => array( 'slug' => 'reviews', 'with_front' => false ),
		'supports'     => array( 'title', 'editor', 'excerpt', 'thumbnail', 'revisions', 'comments', 'author' ),
		'show_in_rest' => true,
	) );

	register_taxonomy( 'tech', array( 'project', 'client_work', 'guide', 'review' ), array(
		'labels' => array(
			'name'          => __( 'Technologies', 'digital-district' ),
			'singular_name' => __( 'Technology', 'digital-district' ),
		),
		'public'            => true,
		'hierarchical'      => false,
		'show_admin_column' => true,
		'show_in_rest'      => true,
		'rewrite'           => array( 'slug' => 'tech' ),
	) );
}
add_action( 'init', 'dd_register_content' );

/**
 * Make both archives sort by menu order then newest, so the owner can pin
 * flagship work to the top with the "Order" attribute.
 */
function dd_archive_order( $query ) {
	if ( is_admin() || ! $query->is_main_query() ) {
		return;
	}
	if ( $query->is_post_type_archive( array( 'project', 'client_work', 'guide', 'review' ) ) ) {
		$query->set( 'orderby', array( 'menu_order' => 'ASC', 'date' => 'DESC' ) );
	}
}
add_action( 'pre_get_posts', 'dd_archive_order' );
