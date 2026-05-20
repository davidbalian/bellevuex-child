<?php
/**
 * Chic Centre Suites — 404 template.
 * Fullscreen static hero using the second home-hero slide as background.
 */

require_once __DIR__ . '/inc/home-data.php';

get_header();

$home_id     = (int) get_option( 'page_on_front' );
$hero_slides = chic_home_hero_slides( $home_id );
$slide       = $hero_slides[1] ?? $hero_slides[0];
?>

<main class="page-404">

	<section class="home-hero home-hero--static home-hero--404" aria-label="<?php echo Chic_Page_Content::get_attr( 'option', '404', 'aria' ); ?>">

		<div class="home-hero__slider-bleed">
			<picture>
				<source
					media="(max-width: 768px)"
					srcset="<?php echo esc_url( $slide['mobile'] ); ?>"
					sizes="100vw"
				>
				<img
					src="<?php echo esc_url( $slide['desktop'] ); ?>"
					alt="<?php echo esc_attr( $slide['alt'] ); ?>"
					loading="eager"
					decoding="async"
					fetchpriority="high"
					<?php if ( $slide['srcset'] ) : ?>
					srcset="<?php echo esc_attr( $slide['srcset'] ); ?>"
					sizes="100vw"
					<?php endif; ?>
				>
			</picture>
		</div>

		<div class="home-hero__overlay">
			<div class="home-hero__inner">
				<div class="home-hero__content home-hero__content--centered fade-in">
					<p class="hero-404__label"><?php echo esc_html( Chic_Page_Content::get_text_uc( 'option', '404', 'error_label' ) ); ?></p>
					<h1 class="home-hero__title hero-404__heading"><?php echo esc_html( Chic_Page_Content::get_text_uc( 'option', '404', 'heading' ) ); ?></h1>
					<p class="home-hero__text"><?php echo esc_html( Chic_Page_Content::get_text( 'option', '404', 'body' ) ); ?></p>
					<div class="home-hero__ctas">
						<a href="<?php echo esc_url( chic_localized_url( '/' ) ); ?>" class="btn btn--secondary">
							<?php echo esc_html( Chic_Page_Content::get_text_uc( 'option', '404', 'return_label' ) ); ?>
						</a>
					</div>
				</div>
			</div>
		</div>

	</section>

</main>

<?php get_footer(); ?>
