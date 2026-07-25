<?php
/**
 * Header: document head, skip link, sticky nav, overlay menu.
 *
 * @package DigitalDistrict
 */

defined( 'ABSPATH' ) || exit;
?>
<!doctype html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>" />
	<meta name="viewport" content="width=device-width, initial-scale=1" />
	<link rel="profile" href="https://gmpg.org/xfn/11" />
	<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<a class="skip-link" href="#main"><?php esc_html_e( 'Skip to content', 'digital-district' ); ?></a>

<header class="site-header">
	<div class="container nav">
		<?php if ( has_custom_logo() ) : ?>
			<div class="brand"><?php the_custom_logo(); ?></div>
		<?php else : ?>
			<a class="brand" href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home">
				<span class="mark" aria-hidden="true"></span>
				<span><?php bloginfo( 'name' ); ?></span>
			</a>
		<?php endif; ?>

		<nav class="primary-nav" aria-label="<?php esc_attr_e( 'Primary', 'digital-district' ); ?>">
			<?php
			wp_nav_menu( array(
				'theme_location' => 'primary',
				'container'      => false,
				'menu_class'     => 'nav-menu',
				'fallback_cb'    => 'dd_nav_fallback',
				'depth'          => 2,
			) );
			?>
		</nav>

		<button class="nav-toggle" type="button" data-menu-open aria-haspopup="dialog" aria-controls="overlay-menu">
			<?php esc_html_e( 'Menu', 'digital-district' ); ?>
			<kbd>Esc</kbd>
		</button>
	</div>
</header>

<div class="overlay-menu" id="overlay-menu" role="dialog" aria-modal="true" aria-label="<?php esc_attr_e( 'Menu', 'digital-district' ); ?>">
	<button class="overlay-menu__backdrop" type="button" data-menu-close aria-label="<?php esc_attr_e( 'Close menu', 'digital-district' ); ?>"></button>
	<div class="overlay-menu__panel panel">
		<button class="overlay-menu__close" type="button" data-menu-close aria-label="<?php esc_attr_e( 'Close menu', 'digital-district' ); ?>">&#10005;</button>
		<p class="hud"><?php esc_html_e( 'Navigate', 'digital-district' ); ?></p>
		<ul class="overlay-menu__list">
			<?php
			foreach ( dd_nav_groups() as $g ) :
				if ( ! empty( $g['children'] ) ) :
					?>
					<li class="overlay-menu__group">
						<span class="overlay-menu__grouplabel"><?php echo esc_html( $g['label'] ); ?></span>
						<?php foreach ( $g['children'] as $c ) : ?>
							<a href="<?php echo esc_url( $c['url'] ); ?>"><?php echo esc_html( $c['label'] ); ?></a>
						<?php endforeach; ?>
					</li>
					<?php
				else :
					?>
					<li><a href="<?php echo esc_url( $g['url'] ); ?>"><?php echo esc_html( $g['label'] ); ?></a></li>
					<?php
				endif;
			endforeach;
			?>
		</ul>
	</div>
</div>

<main id="main">
