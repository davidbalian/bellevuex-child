<?php
/**
 * Template Name: Explore Athens
 */

require_once __DIR__ . '/inc/explore-athens-data.php';

get_header();

$_pid = get_the_ID();

// Attractions: ACF repeater, fallback to PHP data function.
$_attractions = Chic_Page_Content::get_repeater( $_pid, 'chic_ea_attractions' );
if ( empty( $_attractions ) ) {
	$_lang = chic_get_current_lang();
	foreach ( chic_explore_athens_attractions() as $_att ) {
		$_attractions[] = [
			'title' => 'el' === $_lang ? t( $_att['title'] ) : $_att['title'],
			'body'  => 'el' === $_lang ? t( $_att['body'] )  : $_att['body'],
			'image' => $_att['image'],
			'alt'   => 'el' === $_lang ? t( $_att['alt'] )   : $_att['alt'],
		];
	}
}

// "Also worth exploring": ACF repeater, fallback to PHP data function.
$_more = Chic_Page_Content::get_repeater( $_pid, 'chic_ea_more_items' );
if ( empty( $_more ) ) {
	$_lang = chic_get_current_lang();
	foreach ( chic_explore_athens_more() as $_item ) {
		$_more[] = [
			'title' => 'el' === $_lang ? t( $_item['title'] ) : $_item['title'],
			'body'  => 'el' === $_lang ? t( $_item['body'] )  : $_item['body'],
		];
	}
}
?>

<main class="page-explore-athens">

	<!-- ── Hero ──────────────────────────────────────────────────────────── -->

	<section class="home-hero home-hero--static">

		<div class="home-hero__slider-bleed js-hero-parallax">
			<picture>
				<img
					src="<?php echo esc_url( Chic_Page_Content::get_image_url( $_pid, 'explore', 'hero_image', 'full' ) ); ?>"
					alt="<?php echo esc_attr( Chic_Page_Content::get_image_alt( $_pid, 'explore', 'hero_image' ) ); ?>"
					loading="eager"
					decoding="async"
					fetchpriority="high"
				>
			</picture>
		</div>

		<div class="home-hero__overlay">
			<div class="home-hero__inner">
				<div class="home-hero__content home-hero__content--centered">
					<h1 class="home-hero__title fade-in fade-in-delay-0"><?php echo wp_kses_post( Chic_Page_Content::get_text_uc_html( $_pid, 'explore', 'hero_heading' ) ); ?></h1>
					<p class="home-hero__text fade-in fade-in-delay-1"><?php echo esc_html( Chic_Page_Content::get_text( $_pid, 'explore', 'hero_subhead' ) ); ?></p>
				</div>
			</div>
		</div>

	</section>

	<!-- ── Intro ─────────────────────────────────────────────────────────── -->

	<section class="explore-athens-intro fade-in">
		<div class="explore-athens-intro__inner">
			<span class="explore-athens-intro__eyebrow"><?php echo esc_html( Chic_Page_Content::get_text_uc( $_pid, 'explore', 'intro_eyebrow' ) ); ?></span>
			<h2 class="explore-athens-intro__title"><?php echo esc_html( Chic_Page_Content::get_text( $_pid, 'explore', 'intro_heading' ) ); ?></h2>
			<p class="explore-athens-intro__lede"><?php echo esc_html( Chic_Page_Content::get_text( $_pid, 'explore', 'intro_body_1' ) ); ?></p>
			<p class="explore-athens-intro__lede"><?php echo esc_html( Chic_Page_Content::get_text( $_pid, 'explore', 'intro_body_2' ) ); ?></p>
		</div>
	</section>

	<!-- ── Attractions ───────────────────────────────────────────────────── -->

	<section class="explore-athens-attractions">
		<div class="explore-athens-attractions__inner">

			<?php foreach ( $_attractions as $_i => $_attraction ) :
				$_reverse = ( $_i % 2 !== 0 ) ? ' explore-attraction--reverse' : '';
			?>
			<article class="explore-attraction<?php echo esc_attr( $_reverse ); ?>">
				<div class="explore-attraction__media fade-in fade-in-delay-0">
					<img
						src="<?php echo esc_url( $_attraction['image'] ); ?>"
						alt="<?php echo esc_attr( $_attraction['alt'] ); ?>"
						loading="lazy"
					>
				</div>
				<div class="explore-attraction__body fade-in fade-in-delay-1">
					<h2><?php echo esc_html( $_attraction['title'] ); ?></h2>
					<p><?php echo esc_html( $_attraction['body'] ); ?></p>
				</div>
			</article>
			<?php endforeach; ?>

		</div>
	</section>

	<!-- ── Also worth exploring ──────────────────────────────────────────── -->

	<section class="explore-athens-more fade-in">
		<div class="explore-athens-more__inner">
			<h2 class="explore-athens-more__heading"><?php echo esc_html( Chic_Page_Content::get_text( $_pid, 'explore', 'more_heading' ) ); ?></h2>
			<div class="explore-more__grid">
				<?php foreach ( $_more as $_item ) : ?>
				<div class="explore-more__item">
					<h3><?php echo esc_html( $_item['title'] ); ?></h3>
					<p><?php echo esc_html( $_item['body'] ); ?></p>
				</div>
				<?php endforeach; ?>
			</div>
		</div>
	</section>

</main>

<?php get_footer(); ?>
