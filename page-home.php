<?php
/**
 * Template Name: Home
 *
 * Clean homepage template. Hero background = page featured image.
 * Suites pulled dynamically from MotoPress Hotel Booking (mphb_room_type CPT).
 * Map embed: replace the src below with your Google Maps embed URL.
 */

require_once __DIR__ . '/inc/home-data.php';

get_header();

$hero_img = get_the_post_thumbnail_url( get_the_ID(), 'full' );
?>

<main class="page-home">

	<!-- ── Hero ────────────────────────────────────────────────────────── -->

	<section
		class="home-hero"
		<?php if ( $hero_img ) : ?>
			style="--hero-bg-url: url('<?php echo esc_url( $hero_img ); ?>')"
		<?php endif; ?>
	>
		<div class="home-hero__inner">
			<h1 class="home-hero__title">Stay in the Heart of Athens</h1>
			<span class="home-hero__tagline"></span>
			<a
				class="home-hero__cta"
				href="https://direct-book.com/properties/chiccentresuitesathens"
				target="_blank"
				rel="noopener noreferrer"
			>Book Now</a>
		</div>
	</section>

	<!-- ── Intro ───────────────────────────────────────────────────────── -->

	<section class="home-intro">
		<div class="home-intro__inner">
			<h2 class="home-intro__title">Welcome</h2>
			<p class="home-intro__lede">Our fully equipped apartments are located in three buildings on the same road in the heart of Athens, just a 5-minute walk from Syntagma Square and Monastiraki, and only steps away from the iconic pedestrian street of Ermou, where you can explore the city&rsquo;s treasures, experience its culture and history, and, of course, enjoy shopping.</p>
		</div>
	</section>

	<!-- ── Buildings + Suite grids ─────────────────────────────────────── -->

	<?php foreach ( chic_home_buildings() as $building ) :
		$suites = chic_home_get_suites( $building['term'], 'large' );
		if ( empty( $suites ) ) continue;
	?>

	<section class="home-building">
		<div class="home-building__inner">
			<p class="home-building__address"><?php echo esc_html( $building['label'] ); ?></p>
			<a
				class="home-building__map-link"
				href="<?php echo esc_url( $building['maps'] ); ?>"
				target="_blank"
				rel="noopener noreferrer"
			>View Location on Google Maps</a>
		</div>
	</section>

	<section class="home-suites">
		<div class="home-suites__inner">
			<ul class="home-suites__grid">
				<?php foreach ( $suites as $suite ) : ?>
				<li>
					<article class="home-suite-card">
						<a class="home-suite-card__link" href="<?php echo esc_url( $suite['permalink'] ); ?>">
							<figure class="home-suite-card__media">
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
							<div class="home-suite-card__body">
								<h3 class="home-suite-card__title"><?php echo esc_html( $suite['title'] ); ?></h3>
								<?php if ( $suite['capacity_label'] ) : ?>
									<p class="home-suite-card__capacity"><?php echo esc_html( $suite['capacity_label'] ); ?></p>
								<?php endif; ?>
								<span class="home-suite-card__cta">View this room</span>
							</div>
						</a>
					</article>
				</li>
				<?php endforeach; ?>
			</ul>
		</div>
	</section>

	<?php endforeach; ?>

	<!-- ── Map ─────────────────────────────────────────────────────────── -->

	<section class="home-map">
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
