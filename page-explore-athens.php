<?php
/**
 * Template Name: Explore Athens
 */

require_once __DIR__ . '/inc/explore-athens-data.php';

get_header();

$attractions = chic_explore_athens_attractions();
$more        = chic_explore_athens_more();
?>

<main class="page-explore-athens">

	<!-- ── Hero ──────────────────────────────────────────────────────────── -->

	<section class="home-hero home-hero--static">

		<div class="home-hero__slider-bleed js-hero-parallax">
			<picture>
				<img
					src="https://davidb1553.sg-host.com/wp-content/uploads/7-chic-centre-suites-athens-nearby-attractions-acropolis.webp"
					alt="<?php echo ta( 'The Acropolis of Athens at sunset, overlooking the city' ); ?>"
					loading="eager"
					decoding="async"
					fetchpriority="high"
				>
			</picture>
		</div>

		<div class="home-hero__overlay">
			<div class="home-hero__inner">
				<div class="home-hero__content home-hero__content--centered">
					<h1 class="home-hero__title fade-in fade-in-delay-0"><?php echo wp_kses_post( t_uc_html( 'EXPLORE AND<br>EXPERIENCE ATHENS' ) ); ?></h1>
					<p class="home-hero__text fade-in fade-in-delay-1"><?php te( "Discover the timeless beauty of Greece's capital from the heart of the city" ); ?></p>
				</div>
			</div>
		</div>

	</section>

	<!-- ── Intro ─────────────────────────────────────────────────────────── -->

	<section class="explore-athens-intro fade-in">
		<div class="explore-athens-intro__inner">
			<span class="explore-athens-intro__eyebrow"><?php te_uc( 'Explore Athens / Athina' ); ?></span>
			<h2 class="explore-athens-intro__title"><?php te( 'A City Steeped in History, Culture, and Ancient Landmarks' ); ?></h2>
			<p class="explore-athens-intro__lede"><?php te( 'Staying at Athens means immersing yourself in a destination where ancient history meets vibrant modern life. From iconic landmarks to hidden local gems, Athens offers an unforgettable experience for every traveler, and at Chic Centre Suites Athens, you are perfectly positioned to explore it all.' ); ?></p>
			<p class="explore-athens-intro__lede"><?php te( 'Located just steps from Ermou Street and within a short walk of Syntagma Square and Monastiraki, your stay places you at the very center of culture, history, shopping, and gastronomy.' ); ?></p>
		</div>
	</section>

	<!-- ── Attractions ───────────────────────────────────────────────────── -->

	<section class="explore-athens-attractions">
		<div class="explore-athens-attractions__inner">

			<?php foreach ( $attractions as $i => $attraction ) :
				$reverse = ( $i % 2 !== 0 ) ? ' explore-attraction--reverse' : '';
			?>
			<article class="explore-attraction<?php echo esc_attr( $reverse ); ?>">
				<div class="explore-attraction__media fade-in fade-in-delay-0">
					<img
						src="<?php echo esc_url( $attraction['image'] ); ?>"
						alt="<?php echo ta( $attraction['alt'] ); ?>"
						loading="lazy"
					>
				</div>
				<div class="explore-attraction__body fade-in fade-in-delay-1">
					<h2><?php echo esc_html( t( $attraction['title'] ) ); ?></h2>
					<p><?php echo esc_html( t( $attraction['body'] ) ); ?></p>
				</div>
			</article>
			<?php endforeach; ?>

		</div>
	</section>

	<!-- ── Also worth exploring ──────────────────────────────────────────── -->

	<section class="explore-athens-more fade-in">
		<div class="explore-athens-more__inner">
			<h2 class="explore-athens-more__heading"><?php te( 'Also Worth Exploring' ); ?></h2>
			<div class="explore-more__grid">
				<?php foreach ( $more as $item ) : ?>
				<div class="explore-more__item">
					<h3><?php echo esc_html( t( $item['title'] ) ); ?></h3>
					<p><?php echo esc_html( t( $item['body'] ) ); ?></p>
				</div>
				<?php endforeach; ?>
			</div>
		</div>
	</section>

</main>

<?php get_footer(); ?>
