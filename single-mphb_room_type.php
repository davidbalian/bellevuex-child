<?php
/**
 * Single accommodation template (mphb_room_type).
 * Overrides Elementor/parent-theme rendering for all /accommodation/* URLs.
 */

require_once __DIR__ . '/inc/home-data.php';
require_once __DIR__ . '/inc/suite-data.php';

get_header();

while ( have_posts() ) :
	the_post();

	$post_id   = get_the_ID();
	$amenities = chic_suite_amenities( $post_id );
	$building  = chic_suite_building( $post_id );
	$gallery   = chic_suite_gallery_ids( $post_id );

	// Suite description: ACF WYSIWYG, fallback to PHP data (+ t() on paragraphs).
	$_lang             = chic_get_current_lang();
	$_description_html = function_exists( 'get_field' )
		? (string) ( get_field( 'chic_suite_description_' . $_lang, $post_id ) ?: '' )
		: '';
	if ( 'el' === $_lang && $_description_html !== '' && function_exists( 'get_field' ) ) {
		$_en_desc = (string) ( get_field( 'chic_suite_description_en', $post_id ) ?: '' );
		if ( $_description_html === $_en_desc ) {
			$_description_html = '';
		}
	}
	if ( empty( $_description_html ) ) {
		$_desc_paras = chic_suite_description( $post_id );
		foreach ( $_desc_paras as $_para ) {
			$_description_html .= '<p>' . esc_html( $_para ) . '</p>';
		}
	}

	// Suite facilities: Site Settings ACF repeater, fallback to hardcoded list.
	$_facilities = Chic_Page_Content::get_repeater( 'option', 'chic_ss_facilities' );
	$_hygiene    = Chic_Page_Content::get_repeater( 'option', 'chic_ss_hygiene' );

	$book_now_url = chic_header_book_now_url();
	if ( '#' === $book_now_url ) {
		$book_now_url = 'https://direct-book.com/properties/chiccentresuitesathens';
	}

	$featured_desktop = get_the_post_thumbnail_url( $post_id, 'full' ) ?: '';
	$featured_mobile  = get_the_post_thumbnail_url( $post_id, 'medium_large' ) ?: $featured_desktop;
	$suite_title          = get_the_title();
	$suite_title_display  = chic_translate_suite_title( $suite_title );
	$suite_title_caps     = chic_translate_suite_title_uc( $suite_title );
?>

<main class="page-accommodation">

	<!-- ── Hero ──────────────────────────────────────────────────────────── -->

	<section class="home-hero home-hero--static">

		<div class="home-hero__slider-bleed js-hero-parallax">
			<?php if ( $featured_desktop ) : ?>
			<picture>
				<source media="(max-width: 768px)" srcset="<?php echo esc_url( $featured_mobile ); ?>">
				<img
					src="<?php echo esc_url( $featured_desktop ); ?>"
					alt="<?php echo esc_attr( $suite_title_display ); ?>"
					loading="eager"
					decoding="async"
					fetchpriority="high"
				>
			</picture>
			<?php endif; ?>
		</div>

		<div class="home-hero__overlay">
			<div class="suite-hero__title-bar fade-in fade-in-delay-0">
				<h1 class="suite-hero__title"><?php echo esc_html( $suite_title_caps ); ?></h1>
			</div>
		</div>

	</section>

	<!-- ── Amenities strip ───────────────────────────────────────────────── -->

	<section class="suite-amenities fade-in">
		<div class="suite-amenities__inner">
			<div class="suite-amenities__items">
				<div class="th-tour-nav-items">
					<?php foreach ( $amenities as $item ) : ?>
					<span class="th-tour-nav-item">
						<i aria-hidden="true" class="<?php echo esc_attr( $item['icon'] ); ?>"></i>
						<span><?php echo esc_html( $item['label'] ); ?></span>
					</span>
					<?php endforeach; ?>
				</div>
			</div>

			<a
				class="suite-amenities__book btn btn--secondary"
				href="<?php echo esc_url( $book_now_url ); ?>"
				target="_blank"
				rel="noopener noreferrer"
			><?php te_uc( 'Book Now' ); ?></a>

		</div>
	</section>

	<!-- ── Suite description ─────────────────────────────────────────────── -->

	<section class="suite-description fade-in">
		<div class="suite-description__inner">
			<h2 class="suite-description__title"><?php te_uc( 'Suite Description' ); ?></h2>
			<div class="suite-description__body fade-in fade-in-delay-0">
				<?php echo wp_kses_post( $_description_html ); ?>
			</div>
		</div>
	</section>

	<!-- ── Photos ─────────────────────────────────────────────────────────── -->

	<section id="suite-photos" class="home-building fade-in">
		<div class="home-building__inner">
			<h2 class="home-building__title"><?php te_uc( 'Photos' ); ?></h2>
			<?php if ( $building ) : ?>
			<a
				class="home-building__address-link chic-type-meta"
				href="<?php echo esc_url( $building['maps'] ); ?>"
				target="_blank"
				rel="noopener noreferrer"
			><?php echo esc_html( t( $building['label'] ) ); ?> <svg width="11" height="11" viewBox="0 0 11 11" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><line x1="1" y1="10" x2="10" y2="1"/><polyline points="4,1 10,1 10,7"/></svg></a>
			<?php endif; ?>
		</div>
	</section>

	<?php if ( ! empty( $gallery ) ) : ?>
	<section class="home-suites fade-in">
		<div class="home-suites__inner">
			<div class="home-suites__carousel-wrap">
				<div
					id="home-suites-<?php echo esc_attr( $post_id ); ?>"
					class="swiper home-suites__carousel js-gallery-swiper"
				>
					<div class="swiper-wrapper">
						<?php foreach ( $gallery as $att_id ) :
							if ( ! wp_get_attachment_url( $att_id ) ) continue;
							$img_alt = get_post_meta( $att_id, '_wp_attachment_image_alt', true );
						?>
						<div class="swiper-slide">
							<figure class="suite-gallery__media">
								<?php echo wp_get_attachment_image( $att_id, 'large', false, [
									'alt'     => esc_attr( $img_alt ?: $suite_title_display ),
									'loading' => 'lazy',
									'class'   => 'suite-gallery__img',
								] ); ?>
							</figure>
						</div>
						<?php endforeach; ?>
					</div>
				</div>
			</div>
			<div class="home-suites__controls">
				<button type="button" class="home-suites__btn" data-nav="prev" aria-label="<?php echo ta( 'Previous photo' ); ?>"><span aria-hidden="true">&#8592;</span></button>
				<button type="button" class="home-suites__btn" data-nav="next" aria-label="<?php echo ta( 'Next photo' ); ?>"><span aria-hidden="true">&#8594;</span></button>
			</div>
		</div>
	</section>
	<?php endif; ?>

	<!-- ── Suite facilities ──────────────────────────────────────────────── -->

	<section class="suite-facilities fade-in">
		<div class="suite-facilities__inner">

			<h2 class="suite-facilities__title"><?php te_uc( 'Suite Facilities' ); ?></h2>

			<?php if ( ! empty( $_facilities ) ) : ?>
			<ul class="suite-facilities__list">
				<?php foreach ( $_facilities as $_fac ) : ?>
				<li><?php echo esc_html( $_fac['label'] ?? '' ); ?></li>
				<?php endforeach; ?>
			</ul>
			<?php else : ?>
			<ul class="suite-facilities__list">
				<li><?php te( 'Free Wi-Fi' ); ?></li>
				<li><?php te( 'Fully Equipped Kitchen included Nespresso coffee machine' ); ?></li>
				<li><?php te( 'Towels, bath robes, slippers' ); ?></li>
				<li><?php te( 'USB-A Sockets' ); ?></li>
				<li><?php te( 'Hangers' ); ?></li>
				<li><?php te( 'Easy keyless secure access' ); ?></li>
				<li><?php te( 'Elevator in building' ); ?></li>
				<li><?php te( 'Safe box' ); ?></li>
				<li>55&#8243; Smart TV with Netflix</li>
				<li><?php te( 'Air Conditioning' ); ?></li>
				<li><?php te( 'Iron with iron board' ); ?></li>
				<li><?php te( 'Hair dryer' ); ?></li>
				<li><?php te( 'Welcome consumables upon arrival' ); ?></li>
				<li><?php te( 'High Quality Mattress &amp; Pillows' ); ?></li>
				<li><?php te( 'Smoke detector &amp; extinguisher' ); ?></li>
				<li><?php te( 'Ambient lighting' ); ?></li>
				<li><?php te( 'First Aid Kit' ); ?></li>
				<li><?php te( 'Linen' ); ?></li>
				<li><?php te( 'Soap, shampoo, shower gel' ); ?></li>
			</ul>
			<?php endif; ?>

			<div class="suite-facilities__notes">
				<?php if ( ! empty( $_hygiene ) ) : ?>
					<?php foreach ( $_hygiene as $_note ) :
						$_h = $_note['heading'] ?? '';
						$_b = $_note['body'] ?? '';
					?>
					<p><?php if ( '' !== $_h ) : ?><strong><?php echo esc_html( $_h ); ?></strong><br><?php endif; ?><?php echo esc_html( $_b ); ?></p>
					<?php endforeach; ?>
				<?php else : ?>
					<p><strong><?php te( 'Hygiene:' ); ?></strong><br><?php te( 'Fully cleaned and disinfected suite upon your arrival.' ); ?></p>
					<p><strong><?php te( 'Room Cleaning Service:' ); ?></strong><br><?php te( 'Towels will be changed every 4 days.' ); ?></p>
					<p><?php te( 'However, we also offer the following options for your convenience:' ); ?></p>
					<p><strong><?php te( 'Room Cleaning:' ); ?></strong> <?php te( 'Available upon request for an additional cost of €30. This service includes general cleaning of the room, fresh bed sheets, and towels.' ); ?></p>
					<p><strong><?php te( 'Extra Towels:' ); ?></strong> <?php te( 'Guests may request an additional set of fresh bath towels for an extra cost of €5 per set.' ); ?></p>
				<?php endif; ?>
			</div>

		</div>
	</section>

	<!-- ── Floating Book Now (mobile) ───────────────────────────────────────── -->
	<div class="suite-float-book js-suite-float-book" aria-hidden="true">
		<a
			class="btn btn--primary"
			href="<?php echo esc_url( $book_now_url ); ?>"
			target="_blank"
			rel="noopener noreferrer"
			tabindex="-1"
		><?php te_uc( 'Book Now' ); ?></a>
	</div>

</main>

<?php
endwhile;
get_footer();
?>
