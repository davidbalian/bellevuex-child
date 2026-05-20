<?php
/**
 * Chic Centre Suites — footer.php
 * Overrides the parent theme footer entirely.
 */
defined( 'ABSPATH' ) || exit;

// Read footer data from ACF Site Settings.
// get_direct() / get_text() / get_repeater() each guard against ACF being unavailable.
$_brand   = Chic_Page_Content::get_direct( 'option', 'chic_footer_brand_name',  'P. Yiatros I.K.E' );
$_email   = Chic_Page_Content::get_direct( 'option', 'chic_footer_email',        'contact@chiccentresuites.com' );
$_phone1  = Chic_Page_Content::get_direct( 'option', 'chic_footer_phone_1',      '+357 99674630' );
$_phone1h = Chic_Page_Content::get_direct( 'option', 'chic_footer_phone_1_href', 'tel:+35799674630' );
$_phone2  = Chic_Page_Content::get_direct( 'option', 'chic_footer_phone_2',      '+30 6982102221' );
$_phone2h = Chic_Page_Content::get_direct( 'option', 'chic_footer_phone_2_href', 'tel:+306982102221' );
$_addr1   = Chic_Page_Content::get_direct( 'option', 'chic_footer_address_1',    '11 Thiseos, 10562 Athens, Greece' );
$_addr2   = Chic_Page_Content::get_direct( 'option', 'chic_footer_address_2',    '13 Thiseos, 10562 Athens, Greece' );
$_addr3   = Chic_Page_Content::get_direct( 'option', 'chic_footer_address_3',    '2 Chavriou, 10562 Athens, Greece' );
$_gemi    = Chic_Page_Content::get_direct( 'option', 'chic_footer_gemi',          '150824901000' );

$_privacy = Chic_Page_Content::get_text( 'option', 'footer', 'privacy_label' );
$_cookie  = Chic_Page_Content::get_text( 'option', 'footer', 'cookie_label' );
$_terms   = Chic_Page_Content::get_text( 'option', 'footer', 'terms_label' );

$_awards = Chic_Page_Content::get_repeater( 'option', 'chic_ss_awards' );
$_social = Chic_Page_Content::get_repeater( 'option', 'chic_ss_social' );
?>

<footer class="site-footer chic-site-footer fade-in" role="contentinfo">
	<div class="site-footer__inner">

		<div class="site-footer__columns">

			<!-- Contact Info -->
			<section class="site-footer__column">
				<h3 class="site-footer__heading"><?php echo esc_html( Chic_Page_Content::get_text_uc( 'option', 'ss', 'contact_info' ) ); ?></h3>
				<ul class="site-footer__list site-footer__list--contact">
					<?php if ( $_brand ) : ?>
					<li class="site-footer__brand-name"><?php echo esc_html( $_brand ); ?></li>
					<?php endif; ?>
					<?php if ( $_email ) : ?>
					<li><a href="mailto:<?php echo esc_attr( $_email ); ?>"><?php echo esc_html( $_email ); ?></a></li>
					<?php endif; ?>
					<?php if ( $_phone1 ) : ?>
					<li><a href="<?php echo esc_attr( $_phone1h ); ?>"><?php echo esc_html( $_phone1 ); ?></a></li>
					<?php endif; ?>
					<?php if ( $_phone2 ) : ?>
					<li><a href="<?php echo esc_attr( $_phone2h ); ?>"><?php echo esc_html( $_phone2 ); ?></a></li>
					<?php endif; ?>
					<?php if ( $_addr1 ) : ?>
					<li><?php echo esc_html( $_addr1 ); ?></li>
					<?php endif; ?>
					<?php if ( $_addr2 ) : ?>
					<li><?php echo esc_html( $_addr2 ); ?></li>
					<?php endif; ?>
					<?php if ( $_addr3 ) : ?>
					<li><?php echo esc_html( $_addr3 ); ?></li>
					<?php endif; ?>
					<?php if ( $_gemi ) : ?>
					<li>&#915;&#917;&#924;&#919;: <?php echo esc_html( $_gemi ); ?></li>
					<?php endif; ?>
				</ul>
			</section>

			<!-- Useful Links -->
			<section class="site-footer__column">
				<h3 class="site-footer__heading"><?php echo esc_html( Chic_Page_Content::get_text_uc( 'option', 'ss', 'useful_links' ) ); ?></h3>
				<ul class="site-footer__list">
					<li><a href="<?php echo esc_url( chic_localized_url( '/privacy-policy/' ) ); ?>"><?php echo esc_html( $_privacy ); ?></a></li>
					<li><a href="<?php echo esc_url( chic_localized_url( '/cookie-policy/' ) ); ?>"><?php echo esc_html( $_cookie ); ?></a></li>
					<li><a href="<?php echo esc_url( chic_localized_url( '/terms-and-conditions/' ) ); ?>"><?php echo esc_html( $_terms ); ?></a></li>
				</ul>
			</section>

			<!-- Awards -->
			<section class="site-footer__column">
				<h3 class="site-footer__heading"><?php echo esc_html( Chic_Page_Content::get_text_uc( 'option', 'ss', 'awards_heading' ) ); ?></h3>
				<?php if ( ! empty( $_awards ) ) : ?>
				<div class="site-footer__awards">
					<?php foreach ( $_awards as $_award ) :
						$_award_img = (string) ( $_award['image'] ?? '' );
						$_award_alt = (string) ( $_award['alt'] ?? '' );
						if ( empty( $_award_img ) ) continue;
					?>
					<img
						class="site-footer__award"
						src="<?php echo esc_url( $_award_img ); ?>"
						alt="<?php echo esc_attr( $_award_alt ); ?>"
						width="200"
						height="280"
						loading="lazy"
						decoding="async"
					>
					<?php endforeach; ?>
				</div>
				<?php else : ?>
				<?php
				// Fallback: hardcoded award images from uploads directory.
				$_fallback_uploads = wp_upload_dir();
				$_fallback_base    = empty( $_fallback_uploads['error'] ) ? trailingslashit( $_fallback_uploads['baseurl'] ) : '';
				$_fallback_awards  = [
					[ 'file' => 'booking-award-2026.avif',  'alt' => 'Booking.com Traveller Review Awards 2026 — 8.9 out of 10' ],
					[ 'file' => 'kayak-award.avif',          'alt' => 'KAYAK Travel Awards Winner' ],
					[ 'file' => 'hoterl-social-award.avif',  'alt' => 'Hotel.social Certificate of Excellence 2026 — 9.0 Review Score' ],
				];
				?>
				<div class="site-footer__awards">
					<?php foreach ( $_fallback_awards as $_fa ) : ?>
						<?php if ( '' === $_fallback_base ) { continue; } ?>
						<img
							class="site-footer__award"
							src="<?php echo esc_url( $_fallback_base . $_fa['file'] ); ?>"
							alt="<?php echo esc_attr( $_fa['alt'] ); ?>"
							width="200"
							height="280"
							loading="lazy"
							decoding="async"
						>
					<?php endforeach; ?>
				</div>
				<?php endif; ?>
			</section>

			<!-- Follow Us -->
			<section class="site-footer__column">
				<h3 class="site-footer__heading"><?php echo esc_html( Chic_Page_Content::get_text_uc( 'option', 'ss', 'follow_us' ) ); ?></h3>
				<ul class="site-footer__social">
					<?php if ( ! empty( $_social ) ) : ?>
						<?php foreach ( $_social as $_soc ) :
							$_soc_url      = $_soc['url'] ?? '';
							$_soc_platform = $_soc['platform'] ?? '';
							$_soc_aria     = $_soc['aria_label'] ?? ucfirst( $_soc_platform );
							if ( empty( $_soc_url ) ) continue;
						?>
						<li>
							<a href="<?php echo esc_url( $_soc_url ); ?>" class="site-footer__social-link" aria-label="<?php echo esc_attr( $_soc_aria ); ?>" target="_blank" rel="noopener">
								<?php if ( 'instagram' === $_soc_platform ) : ?>
								<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
									<rect x="2" y="2" width="20" height="20" rx="5" ry="5"/>
									<circle cx="12" cy="12" r="4.5"/>
									<circle cx="17.5" cy="6.5" r="0.5" fill="currentColor" stroke="none"/>
								</svg>
								<?php elseif ( 'facebook' === $_soc_platform ) : ?>
								<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
									<path d="M18 2h-3a5 5 0 0 0-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 0 1 1-1h3z"/>
								</svg>
								<?php endif; ?>
							</a>
						</li>
						<?php endforeach; ?>
					<?php else : ?>
					<li>
						<a href="#" class="site-footer__social-link" aria-label="Instagram" target="_blank" rel="noopener">
							<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
								<rect x="2" y="2" width="20" height="20" rx="5" ry="5"/>
								<circle cx="12" cy="12" r="4.5"/>
								<circle cx="17.5" cy="6.5" r="0.5" fill="currentColor" stroke="none"/>
							</svg>
						</a>
					</li>
					<li>
						<a href="#" class="site-footer__social-link" aria-label="Facebook" target="_blank" rel="noopener">
							<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
								<path d="M18 2h-3a5 5 0 0 0-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 0 1 1-1h3z"/>
							</svg>
						</a>
					</li>
					<?php endif; ?>
				</ul>
			</section>

		</div>

		<hr class="site-footer__divider">

		<div class="site-footer__bottom">
			<p class="site-footer__copyright chic-type-meta">
				&copy; <?php echo esc_html( date_i18n( 'Y' ) ); ?> Chic Centre Suites. <?php echo esc_html( Chic_Page_Content::get_text( 'option', 'footer', 'all_rights' ) ); ?>
			</p>
			<p class="site-footer__credit chic-type-meta">
				<?php echo esc_html( Chic_Page_Content::get_text( 'option', 'footer', 'developed_by' ) ); ?> <a href="https://balian.cy" target="_blank" rel="noopener">Balian Web Dev Co.</a>
			</p>
		</div>

	</div>
</footer>

<?php if ( function_exists( 'chic_output_cookie_banner' ) ) { chic_output_cookie_banner(); } ?>
<?php wp_footer(); ?>
</body>
</html>
