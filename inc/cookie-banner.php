<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

function chic_output_cookie_banner() {
	?>
	<div id="chic-cookie-banner" class="cookie-banner" role="dialog"
		 aria-label="<?php echo ta( 'Cookie consent' ); ?>"
		 aria-describedby="chic-cookie-banner-text" hidden>
		<button type="button" class="cookie-banner__close"
				aria-label="<?php echo ta( 'Dismiss cookie banner' ); ?>">&times;</button>

		<h2 class="cookie-banner__title"><?php te( 'We value your privacy' ); ?></h2>

		<p class="cookie-banner__text" id="chic-cookie-banner-text">
			<?php te( 'We use cookies to analyse site traffic and improve your experience.' ); ?>
			<?php printf(
				t( 'See our %s for details.' ),
				sprintf( '<a href="%s">%s</a>', esc_url( chic_localized_url( '/privacy-policy/' ) ), esc_html( t( 'Privacy Policy' ) ) )
			); ?>
		</p>

		<div class="cookie-banner__actions">
			<button type="button" class="cookie-banner__accept btn btn--primary"><?php te_uc( 'Accept' ); ?></button>
			<button type="button" class="cookie-banner__reject btn btn--secondary"><?php te_uc( 'Reject' ); ?></button>
		</div>
	</div>
	<?php
}
