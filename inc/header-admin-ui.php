<?php
defined( 'ABSPATH' ) || exit;

// ── Admin assets ──────────────────────────────────────────────────────────────

add_action( 'admin_enqueue_scripts', function ( $hook ) {
	global $post;
	if ( ! in_array( $hook, [ 'post.php', 'post-new.php' ], true ) ) return;
	if ( ! $post || 'chic_header_cfg' !== $post->post_type ) return;

	wp_enqueue_media();
	$dir = get_stylesheet_directory_uri();
	$ver = wp_get_theme()->get( 'Version' );
	wp_enqueue_style(  'chic-header-admin', "$dir/css/header-admin.css", [], $ver );
	wp_enqueue_script( 'chic-header-admin', "$dir/js/header-admin.js",   [], $ver, true );
} );

// ── Meta box ──────────────────────────────────────────────────────────────────

add_action( 'add_meta_boxes', function () {
	add_meta_box(
		'chic_header_menu_box',
		'Header Menu',
		'chic_header_menu_box_render',
		'chic_header_cfg',
		'normal',
		'high'
	);
} );

function chic_header_menu_box_render( WP_Post $post ): void {
	wp_nonce_field( 'chic_header_save', 'chic_header_nonce' );

	$items = chic_header_get_menu();
	if ( empty( $items ) ) {
		$items = chic_header_default_menu();
	}

	$placeholder = CHIC_PLACEHOLDER_IMG;
	?>
	<div id="chic-header-builder" data-placeholder-img="<?php echo esc_attr( $placeholder ); ?>">

		<?php // ── Templates (cloned by JS when adding rows) ──────────────────── ?>

		<template id="chic-tpl-item">
			<?php chic_render_item_row( '{{i}}', [
				'label'       => '',
				'link_type'   => 'placeholder',
				'url'         => '',
				'page'        => 0,
				'is_mega'     => 0,
				'mega_groups' => [],
			] ); ?>
		</template>

		<template id="chic-tpl-building">
			<?php chic_render_building_row( '{{i}}', '{{j}}', [
				'building_label' => '',
				'image'          => 0,
				'suites'         => [],
			] ); ?>
		</template>

		<template id="chic-tpl-suite">
			<?php chic_render_suite_row( '{{i}}', '{{j}}', '{{k}}', [
				'label'     => '',
				'link_type' => 'url',
				'url'       => '',
				'page'      => 0,
				'image'     => 0,
			] ); ?>
		</template>

		<?php // ── Existing rows ─────────────────────────────────────────────── ?>

		<div class="chic-items-list">
			<?php foreach ( $items as $i => $item ) :
				chic_render_item_row( $i, $item );
			endforeach; ?>
		</div>

		<button type="button" class="button chic-add-item">+ Add Menu Item</button>
	</div>
	<?php
}

// ── Row renderers ─────────────────────────────────────────────────────────────

function chic_render_item_row( $i, array $item ): void {
	$label     = $item['label']     ?? '';
	$link_type = $item['link_type'] ?? 'placeholder';
	$url       = $item['url']       ?? '';
	$page      = (int) ( $item['page']    ?? 0 );
	$is_mega   = ! empty( $item['is_mega'] ) ? 1 : 0;
	$groups    = $item['mega_groups'] ?? [];

	$base = "chic_header[items][$i]";
	?>
	<div class="chic-row chic-row--item" data-level="item">
		<div class="chic-row__head">
			<span class="chic-row__title"><?php echo esc_html( $label ?: '(new item)' ); ?></span>
			<button type="button" class="chic-remove" aria-label="Remove item">&times;</button>
		</div>
		<div class="chic-row__body">
			<div class="chic-field">
				<label>Label</label>
				<input type="text" name="<?php echo esc_attr( $base ); ?>[label]"
					value="<?php echo esc_attr( $label ); ?>" class="widefat chic-item-label">
			</div>
			<div class="chic-field">
				<label>Link Type</label>
				<select name="<?php echo esc_attr( $base ); ?>[link_type]" class="chic-link-type">
					<?php foreach ( [ 'placeholder' => 'Placeholder (#)', 'url' => 'Custom URL', 'page' => 'Page' ] as $val => $txt ) : ?>
						<option value="<?php echo esc_attr( $val ); ?>"<?php selected( $link_type, $val ); ?>><?php echo esc_html( $txt ); ?></option>
					<?php endforeach; ?>
				</select>
			</div>
			<div class="chic-field chic-field-url"<?php echo 'url' !== $link_type ? ' style="display:none"' : ''; ?>>
				<label>URL</label>
				<input type="url" name="<?php echo esc_attr( $base ); ?>[url]"
					value="<?php echo esc_attr( $url ); ?>" class="widefat">
			</div>
			<div class="chic-field chic-field-page"<?php echo 'page' !== $link_type ? ' style="display:none"' : ''; ?>>
				<label>Page ID</label>
				<input type="number" name="<?php echo esc_attr( $base ); ?>[page]"
					value="<?php echo esc_attr( $page ); ?>" class="widefat">
			</div>
			<div class="chic-field chic-field-mega">
				<label>
					<input type="checkbox" name="<?php echo esc_attr( $base ); ?>[is_mega]"
						value="1" class="chic-is-mega"<?php checked( $is_mega, 1 ); ?>>
					Mega Panel?
				</label>
			</div>

			<div class="chic-mega-groups"<?php echo ! $is_mega ? ' style="display:none"' : ''; ?>>
				<div class="chic-buildings-list">
					<?php foreach ( $groups as $j => $bldg ) :
						chic_render_building_row( $i, $j, $bldg );
					endforeach; ?>
				</div>
				<button type="button" class="button chic-add-building">+ Add Building</button>
			</div>
		</div>
	</div>
	<?php
}

function chic_render_building_row( $i, $j, array $bldg ): void {
	$label     = $bldg['building_label'] ?? '';
	$suites    = $bldg['suites'] ?? [];
	$image_id  = (int) ( $bldg['image'] ?? 0 );
	$img_url   = $image_id ? wp_get_attachment_image_url( $image_id, 'thumbnail' ) : '';
	$base      = "chic_header[items][$i][mega_groups][$j]";
	?>
	<div class="chic-row chic-row--building" data-level="building">
		<div class="chic-row__head">
			<span class="chic-row__title"><?php echo esc_html( $label ?: '(new building)' ); ?></span>
			<button type="button" class="chic-remove" aria-label="Remove building">&times;</button>
		</div>
		<div class="chic-row__body">
			<div class="chic-field">
				<label>Building Name</label>
				<input type="text" name="<?php echo esc_attr( $base ); ?>[building_label]"
					value="<?php echo esc_attr( $label ); ?>" class="widefat chic-building-label">
			</div>
			<div class="chic-field chic-field-image">
				<label>Building Image</label>
				<div class="chic-image-picker">
					<input type="hidden" name="<?php echo esc_attr( $base ); ?>[image]"
						value="<?php echo esc_attr( $image_id ); ?>" class="chic-image-id">
					<img class="chic-image-preview"
						src="<?php echo esc_url( $img_url ?: CHIC_PLACEHOLDER_IMG ); ?>"
						alt="" style="width:80px;height:60px;object-fit:cover;display:block;margin-bottom:4px;">
					<button type="button" class="button chic-pick-image">Choose Image</button>
					<button type="button" class="button chic-clear-image"<?php echo $image_id ? '' : ' style="display:none"'; ?>>Remove</button>
				</div>
			</div>
			<div class="chic-suites-list">
				<?php foreach ( $suites as $k => $suite ) :
					chic_render_suite_row( $i, $j, $k, $suite );
				endforeach; ?>
			</div>
			<button type="button" class="button chic-add-suite">+ Add Suite</button>
		</div>
	</div>
	<?php
}

function chic_render_suite_row( $i, $j, $k, array $suite ): void {
	$label     = $suite['label']     ?? '';
	$link_type = $suite['link_type'] ?? 'url';
	$url       = $suite['url']       ?? '';
	$page      = (int) ( $suite['page']  ?? 0 );
	$image_id  = (int) ( $suite['image'] ?? 0 );
	$img_url   = $image_id ? wp_get_attachment_image_url( $image_id, 'thumbnail' ) : '';
	$base      = "chic_header[items][$i][mega_groups][$j][suites][$k]";
	?>
	<div class="chic-row chic-row--suite" data-level="suite">
		<div class="chic-row__head">
			<span class="chic-row__title"><?php echo esc_html( $label ?: '(new suite)' ); ?></span>
			<button type="button" class="chic-remove" aria-label="Remove suite">&times;</button>
		</div>
		<div class="chic-row__body">
			<div class="chic-field">
				<label>Suite Name</label>
				<input type="text" name="<?php echo esc_attr( $base ); ?>[label]"
					value="<?php echo esc_attr( $label ); ?>" class="widefat chic-suite-label">
			</div>
			<div class="chic-field">
				<label>Link Type</label>
				<select name="<?php echo esc_attr( $base ); ?>[link_type]" class="chic-link-type">
					<?php foreach ( [ 'url' => 'Custom URL', 'page' => 'Page', 'placeholder' => 'Placeholder (#)' ] as $val => $txt ) : ?>
						<option value="<?php echo esc_attr( $val ); ?>"<?php selected( $link_type, $val ); ?>><?php echo esc_html( $txt ); ?></option>
					<?php endforeach; ?>
				</select>
			</div>
			<div class="chic-field chic-field-url"<?php echo 'url' !== $link_type ? ' style="display:none"' : ''; ?>>
				<label>URL</label>
				<input type="url" name="<?php echo esc_attr( $base ); ?>[url]"
					value="<?php echo esc_attr( $url ); ?>" class="widefat">
			</div>
			<div class="chic-field chic-field-page"<?php echo 'page' !== $link_type ? ' style="display:none"' : ''; ?>>
				<label>Page ID</label>
				<input type="number" name="<?php echo esc_attr( $base ); ?>[page]"
					value="<?php echo esc_attr( $page ); ?>" class="widefat">
			</div>
			<div class="chic-field chic-field-image">
				<label>Preview Image</label>
				<div class="chic-image-picker">
					<input type="hidden" name="<?php echo esc_attr( $base ); ?>[image]"
						value="<?php echo esc_attr( $image_id ); ?>" class="chic-image-id">
					<img class="chic-image-preview"
						src="<?php echo esc_url( $img_url ?: CHIC_PLACEHOLDER_IMG ); ?>"
						alt=""
						style="width:80px;height:60px;object-fit:cover;display:block;margin-bottom:4px;">
					<button type="button" class="button chic-pick-image">Choose Image</button>
					<?php if ( $image_id ) : ?>
						<button type="button" class="button chic-clear-image">Remove</button>
					<?php else : ?>
						<button type="button" class="button chic-clear-image" style="display:none">Remove</button>
					<?php endif; ?>
				</div>
			</div>
		</div>
	</div>
	<?php
}

// ── Save handler ──────────────────────────────────────────────────────────────

add_action( 'save_post_chic_header_cfg', function ( int $post_id ) {
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;
	if ( ! current_user_can( 'edit_post', $post_id ) ) return;
	if ( empty( $_POST['chic_header_nonce'] ) ) return;
	if ( ! wp_verify_nonce( $_POST['chic_header_nonce'], 'chic_header_save' ) ) return;

	$raw   = isset( $_POST['chic_header']['items'] ) ? (array) $_POST['chic_header']['items'] : [];
	$clean = chic_header_sanitize_items( $raw );
	update_post_meta( $post_id, '_chic_header_menu', $clean );
} );

function chic_header_sanitize_items( array $rows ): array {
	$out = [];
	foreach ( $rows as $row ) {
		if ( ! is_array( $row ) ) continue;
		$label = sanitize_text_field( $row['label'] ?? '' );
		if ( '' === $label ) continue;

		$link_type = in_array( $row['link_type'] ?? '', [ 'url', 'page', 'placeholder' ], true )
			? $row['link_type']
			: 'placeholder';

		$is_mega = ! empty( $row['is_mega'] ) ? 1 : 0;

		$groups = [];
		if ( $is_mega && ! empty( $row['mega_groups'] ) && is_array( $row['mega_groups'] ) ) {
			foreach ( $row['mega_groups'] as $bldg ) {
				if ( ! is_array( $bldg ) ) continue;
				$blabel = sanitize_text_field( $bldg['building_label'] ?? '' );
				if ( '' === $blabel ) continue;

				$suites = [];
				if ( ! empty( $bldg['suites'] ) && is_array( $bldg['suites'] ) ) {
					foreach ( $bldg['suites'] as $suite ) {
						if ( ! is_array( $suite ) ) continue;
						$slabel = sanitize_text_field( $suite['label'] ?? '' );
						if ( '' === $slabel ) continue;

						$slink = in_array( $suite['link_type'] ?? '', [ 'url', 'page', 'placeholder' ], true )
							? $suite['link_type']
							: 'url';

						$img_id = (int) ( $suite['image'] ?? 0 );
						if ( $img_id && ! wp_get_attachment_url( $img_id ) ) {
							$img_id = 0;
						}

						$suites[] = [
							'label'     => $slabel,
							'link_type' => $slink,
							'url'       => esc_url_raw( $suite['url'] ?? '' ),
							'page'      => (int) ( $suite['page'] ?? 0 ),
							'image'     => $img_id,
						];
					}
				}
				$bldg_img = (int) ( $bldg['image'] ?? 0 );
				if ( $bldg_img && ! wp_get_attachment_url( $bldg_img ) ) {
					$bldg_img = 0;
				}
				$groups[] = [
					'building_label' => $blabel,
					'image'          => $bldg_img,
					'suites'         => array_values( $suites ),
				];
			}
		}

		$out[] = [
			'label'       => $label,
			'link_type'   => $link_type,
			'url'         => esc_url_raw( $row['url'] ?? '' ),
			'page'        => (int) ( $row['page'] ?? 0 ),
			'is_mega'     => $is_mega,
			'mega_groups' => array_values( $groups ),
		];
	}
	return array_values( $out );
}
