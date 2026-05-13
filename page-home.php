<?php
/**
 * Template Name: Home
 *
 * Homepage template. Hero = full-bleed Swiper slider (slides managed via
 * "Home Hero Slides" meta box in WP admin; falls back to featured image).
 * Suites pulled dynamically from MotoPress Hotel Booking (mphb_room_type CPT).
 */

require_once __DIR__ . '/inc/home-data.php';

get_header();

$hero_slides = chic_home_hero_slides( get_the_ID() );
?>

<main class="page-home">

	<!-- ── Hero ────────────────────────────────────────────────────────── -->

	<section class="home-hero">

		<div class="home-hero__slider-bleed js-hero-parallax">
			<div class="swiper home-hero__carousel js-hero-swiper" aria-label="Chic Centre Suites hero photos">
				<div class="swiper-wrapper">

					<?php foreach ( $hero_slides as $slide ) : ?>
					<div class="swiper-slide">
						<div class="home-hero__slide">
							<picture>
								<source media="(max-width: 768px)" srcset="<?php echo esc_url( $slide['mobile'] ); ?>">
								<img
									src="<?php echo esc_url( $slide['desktop'] ); ?>"
									alt="<?php echo esc_attr( $slide['alt'] ); ?>"
									loading="eager"
									decoding="async"
									fetchpriority="<?php echo esc_attr( $slide['fetchpriority'] ); ?>"
								>
							</picture>
						</div>
					</div>
					<?php endforeach; ?>

				</div>
			</div>
		</div>

		<div class="home-hero__overlay">
			<div class="home-hero__inner">
				<div class="home-hero__content">
					<h1 class="home-hero__title fade-in fade-in-delay-0">Stay in the Heart<br> of Athens</h1>
					<p class="home-hero__text fade-in fade-in-delay-1">Our suites, spread across three buildings on the same road — steps from Syntagma Square, Monastiraki, and the Ermou pedestrian street.</p>
					<div class="home-hero__ctas fade-in fade-in-delay-2">
						<a
							class="home-hero__cta btn btn--secondary"
							href="https://direct-book.com/properties/chiccentresuitesathens"
							target="_blank"
							rel="noopener noreferrer"
						>Book Now</a>
					</div>
				</div>
			</div>
			<a
				href="#home-intro"
				class="home-hero__scroll-down js-hero-scroll-next fade-in fade-in-delay-2"
				aria-label="Scroll to next section"
			>
				<svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
					<polyline points="6 9 12 15 18 9"></polyline>
				</svg>
			</a>
		</div>

	</section>

	<!-- ── Intro ───────────────────────────────────────────────────────── -->

	<section class="home-intro" id="home-intro">
		<div class="home-intro__inner">
			<h2 class="home-intro__title fade-in fade-in-delay-0">Welcome</h2>
			<p class="home-intro__lede fade-in fade-in-delay-1">Our fully equipped apartments are located in three buildings on the same road in the heart of Athens, just a 5-minute walk from Syntagma Square and Monastiraki, and only steps away from the iconic pedestrian street of Ermou, where you can explore the city&rsquo;s treasures, experience its culture and history, and, of course, enjoy shopping.</p>
		</div>
	</section>

	<!-- ── Buildings + Suite grids ─────────────────────────────────────── -->

	<?php
	$_suite_instance = 0;
	foreach ( chic_home_buildings() as $building ) :
		$suites = chic_home_get_suites( $building['term'], 'large' );
		if ( empty( $suites ) ) continue;
		$_suite_instance++;
		$_carousel_id = 'home-suites-' . $_suite_instance;
	?>

	<section class="home-building fade-in">
		<div class="home-building__inner">
			<p class="home-building__address"><?php echo esc_html( $building['label'] ); ?></p>
			<div class="home-building__actions">
				<a
					class="home-building__map-link chic-type-meta btn btn--primary"
					href="<?php echo esc_url( $building['maps'] ); ?>"
					target="_blank"
					rel="noopener noreferrer"
				>View Location on Google Maps</a>
				<div class="home-suites__nav" aria-label="<?php echo esc_attr( $building['label'] ); ?> suites navigation">
					<button type="button"
						class="home-suites__btn"
						data-nav="prev"
						data-target="<?php echo esc_attr( $_carousel_id ); ?>"
						aria-label="Previous suites"
					><span aria-hidden="true">&#8592;</span></button>
					<button type="button"
						class="home-suites__btn"
						data-nav="next"
						data-target="<?php echo esc_attr( $_carousel_id ); ?>"
						aria-label="Next suites"
					><span aria-hidden="true">&#8594;</span></button>
				</div>
			</div>
		</div>
	</section>

	<section class="home-suites fade-in">
		<div class="home-suites__inner">
			<div
				id="<?php echo esc_attr( $_carousel_id ); ?>"
				class="swiper home-suites__carousel js-suites-swiper"
			>
				<div class="swiper-wrapper">
					<?php foreach ( $suites as $suite ) : ?>
					<article class="suite-card swiper-slide">
						<a class="suite-card__link" href="<?php echo esc_url( $suite['permalink'] ); ?>">
							<figure class="suite-card__media">
								<?php if ( $suite['thumb_url'] ) : ?>
									<img
										src="<?php echo esc_url( $suite['thumb_url'] ); ?>"
										alt="<?php echo esc_attr( $suite['title'] ); ?>"
										loading="lazy"
										width="605"
										height="605"
									>
								<?php endif; ?>
							</figure>
							<div class="suite-card__body">
								<h3 class="suite-card__title"><?php echo esc_html( $suite['title'] ); ?></h3>
								<?php if ( $suite['capacity_label'] ) : ?>
									<p class="suite-card__capacity chic-type-meta"><?php echo esc_html( $suite['capacity_label'] ); ?></p>
								<?php endif; ?>
								<span class="suite-card__cta chic-type-meta">View <svg width="11" height="11" viewBox="0 0 11 11" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><line x1="1" y1="10" x2="10" y2="1"/><polyline points="4,1 10,1 10,7"/></svg></span>
							</div>
						</a>
					</article>
					<?php endforeach; ?>
				</div>
			</div>
			<div class="home-suites__mobile-nav">
				<button type="button"
					class="home-suites__btn"
					data-nav="prev"
					data-target="<?php echo esc_attr( $_carousel_id ); ?>"
					aria-label="Previous suites"
				><span aria-hidden="true">&#8592;</span></button>
				<button type="button"
					class="home-suites__btn"
					data-nav="next"
					data-target="<?php echo esc_attr( $_carousel_id ); ?>"
					aria-label="Next suites"
				><span aria-hidden="true">&#8594;</span></button>
			</div>
		</div>
	</section>

	<?php endforeach; ?>

	<!-- ── Map ─────────────────────────────────────────────────────────── -->

	<section class="home-map fade-in">
		<!-- Replace the src with your Google Maps embed URL: Maps → Share → Embed a map → copy iframe src -->
		<iframe
			class="home-map__frame"
			src="https://www.google.com/maps?q=Thiseos+11,+Athina+105+62,+Greece&output=embed"
			title="Map of Chic Centre Suites Athens"
			allowfullscreen
			loading="lazy"
			referrerpolicy="no-referrer-when-downgrade"
		></iframe>
	</section>

</main>

<?php get_footer(); ?>
