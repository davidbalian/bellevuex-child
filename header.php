<?php
/**
 * Chic Centre Suites — site header.
 * Replaces the parent theme header.php.
 */
?><!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo( 'charset' ); ?>">
<meta name="viewport" content="width=device-width, initial-scale=1">
<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<header class="site-header" id="site-header">
	<div class="site-header__inner">

		<a class="site-header__brand" href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home">
			<?php
			$logo = get_custom_logo();
			if ( $logo ) {
				echo $logo;
			} else {
				echo '<span class="site-header__brand-text">' . esc_html( get_bloginfo( 'name' ) ) . '</span>';
			}
			?>
		</a>

		<div class="site-header__tools">
			<nav class="primary-navigation" aria-label="<?php esc_attr_e( 'Primary', 'bellevue' ); ?>">
				<ul class="nav-menu" role="list">
					<?php chic_header_render_items( false ); ?>
				</ul>
				<button
					type="button"
					class="mobile-nav-toggle"
					aria-controls="mobile-nav"
					aria-expanded="false"
					aria-label="<?php esc_attr_e( 'Open menu', 'bellevue' ); ?>"
				>
					<span class="mobile-nav-toggle__bar"></span>
					<span class="mobile-nav-toggle__bar"></span>
					<span class="mobile-nav-toggle__bar"></span>
				</button>
			</nav>
		</div>

	</div>
	<div class="site-mega-backdrop" id="site-mega-backdrop" aria-hidden="true"></div>
</header>

<div id="mobile-nav" class="mobile-nav-overlay" aria-hidden="true">
	<div class="mobile-nav__inner">
		<button
			type="button"
			class="mobile-nav-close"
			aria-label="<?php esc_attr_e( 'Close menu', 'bellevue' ); ?>"
		>
			<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" aria-hidden="true">
				<path d="M18 6L6 18M6 6L18 18" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
			</svg>
		</button>
		<ul class="mobile-nav__links" role="list">
			<?php chic_header_render_items( true ); ?>
		</ul>
		<div class="mobile-nav__footer">
			<a href="<?php echo esc_url( chic_header_book_now_url() ); ?>" class="mobile-nav__cta">
				<?php esc_html_e( 'Book Now', 'bellevue' ); ?>
			</a>
		</div>
	</div>
</div>
