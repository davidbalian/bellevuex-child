<?php
/**
 * Chic Centre Suites — footer.php
 * Overrides the parent theme footer entirely.
 */
defined( 'ABSPATH' ) || exit;
?>

<footer class="site-footer chic-site-footer fade-in" role="contentinfo">
	<div class="site-footer__inner">

		<div class="site-footer__columns">

			<!-- Contact Info -->
			<section class="site-footer__column">
				<h3 class="site-footer__heading"><?php te( 'Contact Info' ); ?></h3>
				<ul class="site-footer__list site-footer__list--contact">
					<li class="site-footer__brand-name">P. Yiatros I.K.E</li>
					<li><a href="mailto:contact@chiccentresuites.com">contact@chiccentresuites.com</a></li>
					<li><a href="tel:+35799674630">+357 99674630</a></li>
					<li><a href="tel:+306982102221">+30 6982102221</a></li>
					<li>11 Thiseos, 10562 Athens, Greece</li>
					<li>13 Thiseos, 10562 Athens, Greece</li>
					<li>2 Chavriou, 10562 Athens, Greece</li>
					<li>&#915;&#917;&#924;&#919;: 150824901000</li>
				</ul>
			</section>

			<!-- Useful Links -->
			<section class="site-footer__column">
				<h3 class="site-footer__heading"><?php te( 'Useful Links' ); ?></h3>
				<ul class="site-footer__list">
					<li><a href="<?php echo esc_url( chic_localized_url( '/privacy-policy/' ) ); ?>"><?php te( 'Privacy Policy' ); ?></a></li>
					<li><a href="<?php echo esc_url( chic_localized_url( '/cookie-policy/' ) ); ?>"><?php te( 'Cookie Policy' ); ?></a></li>
					<li><a href="<?php echo esc_url( chic_localized_url( '/terms-and-conditions/' ) ); ?>"><?php te( 'Terms &amp; Conditions' ); ?></a></li>
				</ul>
			</section>

			<!-- Awards -->
			<section class="site-footer__column">
				<h3 class="site-footer__heading"><?php te( 'Awards' ); ?></h3>
				<div class="site-footer__awards">
					<div class="site-footer__award" aria-hidden="true"></div>
					<div class="site-footer__award" aria-hidden="true"></div>
					<div class="site-footer__award" aria-hidden="true"></div>
				</div>
			</section>

			<!-- Follow Us -->
			<section class="site-footer__column">
				<h3 class="site-footer__heading"><?php te( 'Follow Us' ); ?></h3>
				<ul class="site-footer__social">
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
				</ul>
			</section>

		</div>

		<hr class="site-footer__divider">

		<div class="site-footer__bottom">
			<p class="site-footer__copyright chic-type-meta">
				&copy; <?php echo esc_html( date_i18n( 'Y' ) ); ?> Chic Centre Suites. <?php te( 'All rights reserved.' ); ?>
			</p>
			<p class="site-footer__credit chic-type-meta">
				<?php te( 'Developed by' ); ?> <a href="https://balian.cy" target="_blank" rel="noopener">Balian Web Dev Co.</a>
			</p>
		</div>

	</div>
</footer>

<?php if ( function_exists( 'chic_output_cookie_banner' ) ) { chic_output_cookie_banner(); } ?>
<?php wp_footer(); ?>
</body>
</html>
