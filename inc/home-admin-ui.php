<?php
defined( 'ABSPATH' ) || exit;

// ── Admin assets ──────────────────────────────────────────────────────────────

add_action( 'admin_enqueue_scripts', function ( $hook ) {
	global $post;
	if ( ! in_array( $hook, [ 'post.php', 'post-new.php' ], true ) ) return;
	if ( ! $post || 'page' !== $post->post_type ) return;
	if ( 'page-home.php' !== get_page_template_slug( $post->ID ) ) return;

	wp_enqueue_media();
	$dir = get_stylesheet_directory_uri();
	$ver = wp_get_theme()->get( 'Version' );
	wp_enqueue_script( 'chic-home-admin', "$dir/js/home-admin-ui.js", [], $ver, true );
} );

// ── Meta box ──────────────────────────────────────────────────────────────────

add_action( 'add_meta_boxes', function () {
	add_meta_box(
		'chic_home_hero_slides',
		'Home Hero Slides',
		'chic_home_hero_slides_box_render',
		'page',
		'normal',
		'high'
	);
} );

function chic_home_hero_slides_box_render( WP_Post $post ): void {
	if ( 'page-home.php' !== get_page_template_slug( $post->ID ) ) {
		echo '<p style="color:#888;font-style:italic;">Only visible on the Home page template.</p>';
		return;
	}

	wp_nonce_field( 'chic_home_hero_slides_save', 'chic_home_hero_slides_nonce' );

	$raw        = get_post_meta( $post->ID, '_chic_home_hero_slides', true );
	$ids        = is_string( $raw ) ? json_decode( $raw, true ) : [];
	$ids        = is_array( $ids ) ? array_map( 'intval', $ids ) : [];
	?>
	<style>
		#chic-hero-slides-wrap { margin-top: 8px; }
		#chic-hero-slides-list { display: flex; flex-wrap: wrap; gap: 10px; margin-bottom: 12px; min-height: 40px; }
		.chic-hs-thumb { position: relative; width: 80px; cursor: grab; }
		.chic-hs-thumb img { display: block; width: 80px; height: 60px; object-fit: cover; border: 2px solid #ddd; border-radius: 2px; }
		.chic-hs-thumb.drag-over img { border-color: #2271b1; }
		.chic-hs-remove { position: absolute; top: -7px; right: -7px; background: #cc1818; color: #fff; border: none; border-radius: 50%; width: 20px; height: 20px; font-size: 13px; line-height: 18px; text-align: center; cursor: pointer; padding: 0; }
		#chic-hero-slides-wrap .description { margin: 4px 0 0; color: #888; }
	</style>
	<div id="chic-hero-slides-wrap">
		<div id="chic-hero-slides-list">
			<?php foreach ( $ids as $id ) :
				$thumb = wp_get_attachment_image_url( $id, 'thumbnail' );
				if ( ! $thumb ) continue;
			?>
			<div class="chic-hs-thumb" draggable="true" data-id="<?php echo esc_attr( $id ); ?>">
				<img src="<?php echo esc_url( $thumb ); ?>" alt="">
				<button type="button" class="chic-hs-remove" aria-label="Remove slide">&times;</button>
			</div>
			<?php endforeach; ?>
		</div>
		<input
			type="hidden"
			id="chic-hero-slides-data"
			name="chic_hero_slides_data"
			value="<?php echo esc_attr( wp_json_encode( $ids ) ); ?>"
		>
		<button type="button" id="chic-hero-slides-add" class="button">+ Add / Replace Slides</button>
		<p class="description">Drag thumbnails to reorder. First slide loads with highest priority.</p>
	</div>
	<?php
}

// ── Save ──────────────────────────────────────────────────────────────────────

add_action( 'save_post_page', function ( int $post_id ) {
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;
	if ( ! current_user_can( 'edit_post', $post_id ) ) return;
	if ( empty( $_POST['chic_home_hero_slides_nonce'] ) ) return;
	if ( ! wp_verify_nonce( $_POST['chic_home_hero_slides_nonce'], 'chic_home_hero_slides_save' ) ) return;
	if ( 'page-home.php' !== get_page_template_slug( $post_id ) ) return;

	$raw = isset( $_POST['chic_hero_slides_data'] ) ? wp_unslash( $_POST['chic_hero_slides_data'] ) : '[]';
	$ids = json_decode( $raw, true );

	if ( ! is_array( $ids ) ) {
		delete_post_meta( $post_id, '_chic_home_hero_slides' );
		return;
	}

	$clean = array_values( array_filter( array_map( 'intval', $ids ) ) );
	update_post_meta( $post_id, '_chic_home_hero_slides', wp_json_encode( $clean ) );
} );
