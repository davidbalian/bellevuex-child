<?php
/**
 * Template Name: Testimonials
 *
 * Hero + masonry grid of curated guest reviews (see inc/testimonials-data.php).
 */

require_once __DIR__ . '/inc/testimonials-data.php';

get_header();

while ( have_posts() ) :
	the_post();

	$reviews = chic_testimonials_enriched_rows();
	?>

<main class="page-testimonials">

	<section class="home-hero home-hero--static">

		<div class="home-hero__slider-bleed js-hero-parallax">
			<picture>
				<img
					src="https://davidb1553.sg-host.com/wp-content/uploads/11-chic-centre-suites-athens-nearby-attractions-syntagma-square.webp"
					alt="<?php echo ta( 'Syntagma Square with the Hellenic Parliament building and fountain' ); ?>"
					loading="eager"
					decoding="async"
					fetchpriority="high"
				>
			</picture>
		</div>

		<div class="home-hero__overlay">
			<div class="home-hero__inner">
				<div class="home-hero__content home-hero__content--centered">
					<h1 class="home-hero__title fade-in fade-in-delay-0"><?php te_uc( 'Latest Reviews' ); ?></h1>
				</div>
			</div>
		</div>

	</section>

	<section class="testimonials-section" aria-label="<?php echo ta( 'Guest reviews' ); ?>">
		<div class="testimonials-section__inner">
			<div class="testimonials-masonry" data-fade-stagger>
				<?php foreach ( $reviews as $row ) : ?>
				<article class="suite-card testimonials-card testimonials-card--text">
					<div class="suite-card__body">
						<?php if ( ! empty( $row['author'] ) ) : ?>
						<p class="testimonials-card__author">
							<?php
							$_flag_url = ! empty( $row['country_code'] ) ? chic_testimonials_flag_url( (string) $row['country_code'] ) : '';
							if ( $_flag_url ) :
								?>
							<img class="testimonials-card__flag"
							     src="<?php echo esc_url( $_flag_url ); ?>"
							     alt=""
							     width="24" height="18"
							     loading="lazy" decoding="async">
							<?php endif; ?>
							<span class="testimonials-card__author-name"><?php echo esc_html( $row['author'] ); ?></span>
						</p>
						<?php endif; ?>

						<?php if ( ! empty( $row['suite_linked'] ) && ! empty( $row['permalink'] ) && ! empty( $row['suite_label'] ) ) : ?>
						<h3 class="suite-card__title testimonials-card__suite-title">
							<a class="suite-card__title-link" href="<?php echo esc_url( $row['permalink'] ); ?>"><?php echo esc_html( chic_translate_suite_title_uc( $row['suite_label'] ) ); ?></a>
						</h3>
						<?php elseif ( ! empty( $row['source_label'] ) ) : ?>
						<p class="testimonials-card__source chic-type-meta"><?php echo esc_html( chic_el_strip_monotonic_tonos( t( $row['source_label'] ) ) ); ?></p>
						<?php endif; ?>

						<p class="testimonials-card__review"><?php echo esc_html( t( $row['review'] ) ); ?></p>
					</div>
				</article>
				<?php endforeach; ?>
			</div>
		</div>
	</section>

</main>

<?php
endwhile;

get_footer();
