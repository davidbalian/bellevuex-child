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

	$_pid = get_the_ID();

	// Reviews: ACF repeater, fallback to PHP data function.
	$_reviews = Chic_Page_Content::get_repeater( $_pid, 'chic_tsm_reviews' );
	if ( empty( $_reviews ) ) {
		$_lang = chic_get_current_lang();
		foreach ( chic_testimonials_enriched_rows() as $_row ) {
			$_reviews[] = [
				'author'       => $_row['author'],
				'country_code' => $_row['country_code'],
				'suite'        => $_row['room_id'] ?? 0,
				'suite_label'  => 'el' === $_lang ? chic_translate_suite_title_uc( $_row['suite_label'] ) : ( $_row['suite_label'] ?? '' ),
				'source_label' => 'el' === $_lang ? t( $_row['source_label'] ) : ( $_row['source_label'] ?? '' ),
				'date_label'   => 'el' === $_lang ? t( $_row['date_label'] )   : ( $_row['date_label'] ?? '' ),
				'review'       => 'el' === $_lang ? t( $_row['review'] )       : $_row['review'],
				'permalink'    => $_row['permalink'] ?? '',
				'suite_linked' => $_row['suite_linked'] ?? false,
			];
		}
	}
	?>

<main class="page-testimonials">

	<section class="home-hero home-hero--static">

		<div class="home-hero__slider-bleed js-hero-parallax">
			<picture>
				<img
					src="<?php echo esc_url( Chic_Page_Content::get_image_url( $_pid, 'testimonials', 'hero_image', 'full' ) ); ?>"
					alt="<?php echo esc_attr( Chic_Page_Content::get_image_alt( $_pid, 'testimonials', 'hero_image' ) ); ?>"
					loading="eager"
					decoding="async"
					fetchpriority="high"
				>
			</picture>
		</div>

		<div class="home-hero__overlay">
			<div class="home-hero__inner">
				<div class="home-hero__content home-hero__content--centered">
					<h1 class="home-hero__title fade-in fade-in-delay-0"><?php echo esc_html( Chic_Page_Content::get_text_uc( $_pid, 'testimonials', 'hero_heading' ) ); ?></h1>
				</div>
			</div>
		</div>

	</section>

	<section class="testimonials-section" aria-label="<?php echo Chic_Page_Content::get_attr( $_pid, 'testimonials', 'section_aria' ); ?>">
		<div class="testimonials-section__inner">
			<div class="testimonials-masonry" data-fade-stagger>
				<?php foreach ( $_reviews as $_row ) :
					$_author       = $_row['author'] ?? '';
					$_cc           = $_row['country_code'] ?? '';
					$_suite_id     = isset( $_row['suite'] ) ? (int) $_row['suite'] : 0;
					$_suite_label  = $_row['suite_label'] ?? '';
					$_source_label = $_row['source_label'] ?? '';
					$_date_label   = $_row['date_label'] ?? '';
					$_review       = $_row['review'] ?? '';
					$_permalink    = '';
					$_suite_linked = false;
					if ( $_suite_id > 0 ) {
						$_permalink    = (string) ( get_permalink( $_suite_id ) ?: '' );
						$_suite_linked = '' !== $_permalink && '' !== $_suite_label;
					}
				?>
				<article class="suite-card testimonials-card testimonials-card--text">
					<div class="suite-card__body">
						<?php if ( ! empty( $_author ) ) : ?>
						<p class="testimonials-card__author">
							<?php
							$_flag_url = ! empty( $_cc ) ? chic_testimonials_flag_url( $_cc ) : '';
							if ( $_flag_url ) :
								?>
							<img class="testimonials-card__flag"
							     src="<?php echo esc_url( $_flag_url ); ?>"
							     alt=""
							     width="24" height="18"
							     loading="lazy" decoding="async">
							<?php endif; ?>
							<span class="testimonials-card__author-name"><?php echo esc_html( $_author ); ?></span>
						</p>
						<?php endif; ?>

						<?php if ( $_suite_linked && '' !== $_permalink && '' !== $_suite_label ) : ?>
						<h3 class="suite-card__title testimonials-card__suite-title">
							<a class="suite-card__title-link" href="<?php echo esc_url( $_permalink ); ?>"><?php echo esc_html( chic_translate_suite_title_uc( $_suite_label ) ); ?></a><?php if ( '' !== $_date_label ) : ?><span class="testimonials-card__date">, <?php echo esc_html( $_date_label ); ?></span><?php endif; ?>
						</h3>
						<?php elseif ( ! empty( $_source_label ) ) : ?>
						<p class="testimonials-card__source chic-type-meta"><?php echo esc_html( chic_el_strip_monotonic_tonos( $_source_label ) ); ?></p>
						<?php endif; ?>

						<p class="testimonials-card__review"><?php echo esc_html( $_review ); ?></p>
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
