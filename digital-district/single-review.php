<?php
/**
 * Single: Review.
 *
 * @package DigitalDistrict
 */

defined( 'ABSPATH' ) || exit;

get_header();

while ( have_posts() ) :
	the_post();
	dd_single();
endwhile;

get_footer();
