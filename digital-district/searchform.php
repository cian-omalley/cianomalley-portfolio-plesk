<?php
/**
 * Search form.
 *
 * @package DigitalDistrict
 */

defined( 'ABSPATH' ) || exit;
?>
<form role="search" method="get" class="dd-search" action="<?php echo esc_url( home_url( '/' ) ); ?>">
	<label class="screen-reader-text" for="dd-s"><?php esc_html_e( 'Search for:', 'digital-district' ); ?></label>
	<div class="field" style="margin:0">
		<input id="dd-s" type="search" name="s" value="<?php echo esc_attr( get_search_query() ); ?>" placeholder="<?php esc_attr_e( 'Search…', 'digital-district' ); ?>" />
	</div>
</form>
