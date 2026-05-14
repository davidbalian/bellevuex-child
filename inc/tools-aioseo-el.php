<?php
/**
 * Admin Tool: Apply Greek SEO Content
 * Tools → Greek SEO
 *
 * Writes Greek meta titles + descriptions to custom postmeta fields on all
 * mphb_room_type posts and the home page. These are served by AIOSEO filters
 * in inc/i18n.php on /el/... URLs so Greek pages are properly indexed.
 */
defined( 'ABSPATH' ) || exit;

add_action( 'admin_menu', function () {
	add_management_page(
		'Greek SEO',
		'Greek SEO',
		'manage_options',
		'chic-seo-el',
		'chic_seo_el_render_page'
	);
} );

function chic_seo_el_render_page(): void {
	if ( ! current_user_can( 'manage_options' ) ) return;
	$nonce    = wp_create_nonce( 'chic_seo_el_apply' );
	$dry_run  = ! empty( $_GET['dry_run'] ) ? '1' : '0';
	$run_live = isset( $_GET['run'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_GET['_wpnonce'] ?? '' ) ), 'chic_seo_el_apply' );
	?>
	<div class="wrap">
		<h1>Greek SEO Content Updater</h1>
		<p>
			Writes Greek meta titles and descriptions (<code>_chic_aioseo_title_el</code> /
			<code>_chic_aioseo_description_el</code>) to all suite posts and the home page.
			AIOSEO filters in <code>inc/i18n.php</code> serve these on <code>/el/&hellip;</code> URLs.
		</p>
		<p>
			<a class="button button-secondary" href="<?php echo esc_url( add_query_arg( [ 'page' => 'chic-seo-el', 'dry_run' => '1', 'run' => '1', '_wpnonce' => $nonce ] ) ); ?>">
				&#128065; Dry Run (preview only)
			</a>
			&nbsp;
			<a class="button button-primary" href="<?php echo esc_url( add_query_arg( [ 'page' => 'chic-seo-el', 'dry_run' => '0', 'run' => '1', '_wpnonce' => $nonce ] ) ); ?>"
				onclick="return confirm('Write Greek SEO meta to all posts?');">
				&#9654; Run Live
			</a>
		</p>
		<?php
		if ( $run_live ) {
			$_GET['dry_run'] = $dry_run;
			require_once __DIR__ . '/../tools/apply-seo-content-el.php';
		}
		?>
	</div>
	<?php
}
