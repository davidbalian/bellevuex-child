<?php
/**
 * Template Name: Testimonials
 *
 * Hero + masonry grid of reviews from chic_centre_suites_reviews.csv.
 */

require_once __DIR__ . '/inc/testimonials-data.php';

get_header();

while ( have_posts() ) :
	the_post();

	$post_id = get_the_ID();

	$hero_desktop = get_the_post_thumbnail_url( $post_id, 'full' ) ?: '';
	$hero_mobile  = get_the_post_thumbnail_url( $post_id, 'medium_large' ) ?: $hero_desktop;

	if ( '' === $hero_desktop ) {
		$slides = chic_home_hero_slides( $post_id );
		if ( ! empty( $slides[0]['desktop'] ) ) {
			$hero_desktop = $slides[0]['desktop'];
			$hero_mobile  = $slides[0]['mobile'] ?: $hero_desktop;
		}
	}

	$reviews = chic_testimonials_enriched_rows();
	?>

<main class="page-testimonials">

	<section class="home-hero home-hero--static">

		<div class="home-hero__slider-bleed js-hero-parallax">
			<?php if ( $hero_desktop ) : ?>
			<picture>
				<source media="(max-width: 768px)" srcset="<?php echo esc_url( $hero_mobile ); ?>">
				<img
					src="<?php echo esc_url( $hero_desktop ); ?>"
					alt="<?php echo esc_attr__( 'Latest Reviews', 'bellevue' ); ?>"
					loading="eager"
					decoding="async"
					fetchpriority="high"
				>
			</picture>
			<?php endif; ?>
		</div>

		<div class="home-hero__overlay">
			<div class="home-hero__inner">
				<div class="home-hero__content home-hero__content--centered">
					<h1 class="home-hero__title fade-in fade-in-delay-0">Latest Reviews</h1>
				</div>
			</div>
		</div>

	</section>

	<section class="testimonials-section" aria-label="<?php echo esc_attr__( 'Guest reviews', 'bellevue' ); ?>">
		<div class="testimonials-section__inner">
			<div class="testimonials-masonry" data-fade-stagger>
				<?php foreach ( $reviews as $row ) : ?>
				<article class="suite-card testimonials-card">
					<figure class="suite-card__media">
						<?php if ( ! empty( $row['thumb_url'] ) ) : ?>
							<img
								src="<?php echo esc_url( $row['thumb_url'] ); ?>"
								alt="<?php echo esc_attr( $row['suite_source'] . ' — ' . $row['reviewer'] ); ?>"
								loading="lazy"
								width="605"
								height="605"
							>
						<?php endif; ?>
					</figure>
					<div class="suite-card__body">
						<?php if ( ! empty( $row['suite_linked'] ) && ! empty( $row['permalink'] ) ) : ?>
							<h3 class="suite-card__title">
								<a class="suite-card__title-link" href="<?php echo esc_url( $row['permalink'] ); ?>"><?php echo esc_html( $row['suite_source'] ); ?></a>
							</h3>
						<?php else : ?>
							<h3 class="suite-card__title suite-card__title--plain"><?php echo esc_html( $row['suite_source'] ); ?></h3>
						<?php endif; ?>

						<p class="testimonials-card__meta chic-type-meta">
							<?php echo esc_html( $row['reviewer'] ); ?>
							<?php if ( $row['country'] !== '' ) : ?>
								<span class="testimonials-card__meta-sep" aria-hidden="true"> · </span><?php echo esc_html( $row['country'] ); ?>
							<?php endif; ?>
							<?php if ( $row['date'] !== '' && '-' !== $row['date'] ) : ?>
								<span class="testimonials-card__meta-sep" aria-hidden="true"> · </span><?php echo esc_html( $row['date'] ); ?>
							<?php endif; ?>
						</p>

						<p class="testimonials-card__review"><?php echo esc_html( $row['review'] ); ?></p>
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
