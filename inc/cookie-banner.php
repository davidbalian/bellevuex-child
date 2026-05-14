<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

function chic_output_cookie_banner() {
	?>
	<div id="chic-cookie-banner" class="cookie-banner" role="dialog"
		 aria-label="<?php esc_attr_e( 'Cookie consent', 'bellevuex' ); ?>"
		 aria-describedby="chic-cookie-banner-text" hidden>
		<button type="button" class="cookie-banner__close"
				aria-label="<?php esc_attr_e( 'Dismiss cookie banner', 'bellevuex' ); ?>">&times;</button>

		<h2 class="cookie-banner__title">We value your privacy</h2>

		<p class="cookie-banner__text" id="chic-cookie-banner-text">
			We use cookies to analyse site traffic and improve your experience.
			See our <a href="<?php echo esc_url( home_url( '/privacy-policy/' ) ); ?>">Privacy Policy</a> for details.
		</p>

		<div class="cookie-banner__actions">
			<button type="button" class="cookie-banner__accept btn btn--primary">Accept</button>
			<button type="button" class="cookie-banner__reject btn btn--secondary">Reject</button>
		</div>
	</div>
	<?php
}
