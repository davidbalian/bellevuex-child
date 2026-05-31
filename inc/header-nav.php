<?php
defined( 'ABSPATH' ) || exit;


// Thin wrapper so header-nav.php callers don't need to know about header-acf.php.
function chic_header_get_config_id(): int {
	return chic_header_config_id();
}

function chic_header_item_href( array $item ): string {
	// Hard-coded primary nav targets — keyed on English label (routing key, not display text).
	$label_key = strtolower( trim( $item['label_en'] ?? $item['label'] ?? '' ) );
	$hard_nav  = [
		'home'           => chic_localized_url( '/' ),
		'explore athens' => chic_localized_url( '/explore-and-experience-athens/' ),
		'reviews'        => chic_localized_url( '/reviews-what-people-say/' ),
		'signup / login' => chic_localized_url( '/my-account' ),
		'book now'       => 'https://direct-book.com/properties/chiccentresuitesathens',
	];
	if ( isset( $hard_nav[ $label_key ] ) ) {
		return esc_url( $hard_nav[ $label_key ] );
	}

	$type = $item['link_type'] ?? 'placeholder';
	if ( 'page' === $type && ! empty( $item['page'] ) ) {
		$url = get_permalink( (int) $item['page'] );
		return $url ? esc_url( $url ) : '#';
	}
	if ( 'url' === $type && ! empty( $item['url'] ) && '#' !== $item['url'] ) {
		return esc_url( $item['url'] );
	}
	return '#';
}

function chic_header_get_menu(): array {
	$pid = chic_header_config_id();

	// ACF Repeater — preferred source (populated after seeder runs).
	if ( $pid && function_exists( 'get_field' ) ) {
		$acf_rows = get_field( 'chic_hdr_menu_items', $pid );
		if ( ! empty( $acf_rows ) && is_array( $acf_rows ) ) {
			$items = [];
			foreach ( $acf_rows as $row ) {
				$items[] = [
					'label_en'  => $row['label_en'] ?? '',
					'label_el'  => $row['label_el'] ?? '',
					'link_type' => $row['link_type'] ?? 'placeholder',
					'url'       => $row['url'] ?? '',
					'page'      => $row['page_id'] ?? 0,
					'is_mega'   => ! empty( $row['is_mega'] ),
				];
			}
			return $items;
		}
	}

	// Fallback: legacy post-meta storage.
	$raw   = $pid ? get_post_meta( $pid, '_chic_header_menu', true ) : [];
	$items = [];
	if ( is_array( $raw ) ) {
		foreach ( $raw as $item ) {
			if ( ! is_array( $item ) ) {
				continue;
			}
			$items[] = [
				'label_en'  => $item['label_en'] ?? $item['label'] ?? '',
				'label_el'  => $item['label_el'] ?? '',
				'link_type' => $item['link_type'] ?? 'placeholder',
				'url'       => $item['url'] ?? '',
				'page'      => $item['page'] ?? 0,
				'is_mega'   => ! empty( $item['is_mega'] ),
			];
		}
	}
	return $items;
}

function chic_header_get_mphb_mega_groups(): array {
	$groups = [];
	foreach ( chic_site_buildings() as $b ) {
		$suites = chic_home_get_suites( $b['term'], 'full' );
		if ( empty( $suites ) ) continue;
		$groups[] = [
			'building_label' => $b['short_label'] ?? $b['label'],
			'building_image' => $b['building_image'] ?? '',
			'suites'         => array_map( fn( $s ) => [
				'label'     => chic_translate_suite_title( $s['title'] ),
				'href'      => $s['permalink'],
				'image_url' => $s['thumb_url'],
			], $suites ),
		];
	}
	return $groups;
}

function chic_header_render_items( bool $mobile = false ): void {
	$items = chic_header_get_menu();
	if ( empty( $items ) ) return;

	$hard_labels = [ 'suites' => 'Buildings' ];

	foreach ( $items as $item ) {
		$route_label   = strtolower( trim( $item['label_en'] ?? $item['label'] ?? '' ) );
		$english_label = $hard_labels[ $route_label ] ?? ( $item['label_en'] ?? $item['label'] ?? '' );
		$href          = chic_header_item_href( $item );
		$is_book       = 'book now' === $route_label;
		$is_mega       = ! empty( $item['is_mega'] );
		$lang          = chic_get_current_lang();

		if ( 'el' === $lang ) {
			$acf_el = trim( (string) ( $item['label_el'] ?? '' ) );
			if ( $acf_el !== '' && $acf_el !== $english_label && $acf_el !== ( $item['label_en'] ?? '' ) ) {
				$display = $acf_el;
			} else {
				$display = t( $english_label );
			}
		} else {
			$display = $english_label;
		}

		$label = esc_html( $is_book ? chic_el_strip_monotonic_tonos( $display ) : $display );
		$classes  = 'menu-item';
		if ( $is_mega ) $classes .= ' menu-item-has-children menu-item-has-mega';

		// On mobile the footer CTA already shows Book Now — skip it in the list.
		if ( $mobile && $is_book ) continue;

		echo '<li class="' . esc_attr( $classes ) . '">';

		if ( $is_mega ) {
			echo '<a href="#" aria-expanded="false" aria-haspopup="true">';
			echo $label;
			echo '<span class="nav-submenu-chevron" aria-hidden="true">';
			echo '<svg xmlns="http://www.w3.org/2000/svg" width="10" height="6" viewBox="0 0 10 6" fill="none" aria-hidden="true"><path d="M1 1L5 5L9 1" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>';
			echo '</span></a>';
			chic_header_render_mega_panel( chic_header_get_mphb_mega_groups() );
		} elseif ( $is_book ) {
			echo '<a href="' . esc_attr( $href ) . '" class="btn btn--primary">' . $label . '</a>';
		} else {
			echo '<a href="' . esc_attr( $href ) . '">' . $label . '</a>';
		}

		echo '</li>';
	}
}

function chic_header_render_mega_panel( array $groups ): void {
	?>
	<div class="nav-submenu-clip mega-panel mobile-disclosure__panel" data-mega-clip>
		<div class="mega-panel__body nav-submenu-body mobile-disclosure__inner">
			<div class="mega-panel__height" data-mega-height>
				<div class="mega-panel__surface">
					<div class="mega-panel__layout">

						<div class="mega-panel__main">
							<div class="mega-panel__tabs" role="tablist">
								<?php foreach ( $groups as $idx => $group ) : ?>
									<button
										type="button"
										class="mega-panel__tab<?php echo 0 === $idx ? ' is-active' : ''; ?>"
										data-mega-tab="<?php echo esc_attr( $idx ); ?>"
										role="tab"
										aria-selected="<?php echo 0 === $idx ? 'true' : 'false'; ?>"
									><?php echo esc_html( $group['building_label'] ?? '' ); ?></button>
								<?php endforeach; ?>
							</div>

							<div class="mega-panel__panels">
								<?php foreach ( $groups as $bIdx => $group ) : ?>
									<div
										class="mega-panel__panel<?php echo 0 === $bIdx ? ' is-active' : ''; ?>"
										data-mega-panel="<?php echo esc_attr( $bIdx ); ?>"
										role="tabpanel"
									>
										<?php if ( ! empty( $group['building_label'] ) ) : ?>
											<p class="mega-panel__eyebrow chic-type-meta"><?php echo esc_html( $group['building_label'] ); ?></p>
										<?php endif; ?>
										<ul class="mega-panel__list" role="list">
											<?php
											foreach ( ( $group['suites'] ?? [] ) as $sIdx => $suite ) :
												$suite_href = $suite['href'] ?? '#';
												$suite_text = trim( $suite['label'] ?? '' );
											?>
												<li class="menu-item">
													<a href="<?php echo esc_url( $suite_href ); ?>"
													   data-mega-preview-target="<?php echo esc_attr( "$bIdx-$sIdx" ); ?>">
														<span class="mega-panel__link-text"><?php echo esc_html( $suite_text ); ?></span>
													</a>
												</li>
											<?php endforeach; ?>
										</ul>
									</div>
								<?php endforeach; ?>
							</div>
						</div>

						<div class="mega-panel__preview" aria-hidden="true">
							<?php foreach ( $groups as $bIdx => $group ) : ?>
								<div
									class="mega-panel__preview-group<?php echo 0 === $bIdx ? ' is-active' : ''; ?>"
									data-mega-preview-group="<?php echo esc_attr( $bIdx ); ?>"
								>
									<?php if ( ! empty( $group['building_image'] ) ) : ?>
										<div class="mega-panel__preview-img is-active">
											<img src="<?php echo esc_url( $group['building_image'] ); ?>"
											     alt="<?php echo esc_attr( $group['building_label'] ?? '' ); ?>"
											     loading="lazy">
										</div>
									<?php endif; ?>
									<?php foreach ( ( $group['suites'] ?? [] ) as $sIdx => $suite ) :
										$img = ! empty( $suite['image_url'] ) ? $suite['image_url'] : chic_upload_url( 'off-your-first-reservation.jpg' );
										$alt = esc_attr( $suite['label'] ?? '' );
										$active = empty( $group['building_image'] ) && 0 === $sIdx;
									?>
										<div
											class="mega-panel__preview-img<?php echo $active ? ' is-active' : ''; ?>"
											data-mega-preview-suite="<?php echo esc_attr( "$bIdx-$sIdx" ); ?>"
										>
											<img src="<?php echo esc_url( $img ); ?>" alt="<?php echo $alt; ?>" loading="lazy">
										</div>
									<?php endforeach; ?>
								</div>
							<?php endforeach; ?>
						</div>

					</div>
				</div>
			</div>
		</div>
	</div>
	<?php
}

function chic_header_book_now_url(): string {
	$items = chic_header_get_menu();
	if ( ! empty( $items ) ) {
		foreach ( $items as $item ) {
			$route_label = strtolower( trim( $item['label_en'] ?? $item['label'] ?? '' ) );
			if ( 'book now' === $route_label ) {
				return chic_header_item_href( $item );
			}
		}
	}
	return '#';
}

function chic_output_custom_header(): void {
	?>
	<header class="site-header chic-site-header" id="site-header">
		<div class="site-header__inner">

			<a class="site-header__brand" href="<?php echo esc_url( chic_localized_url( '/' ) ); ?>" rel="home">
				<?php
				$_hdr_logo_pid = chic_header_config_id();
				$_hdr_logo_src = Chic_Page_Content::get_image_url( $_hdr_logo_pid, 'hdr', 'logo', 'full' )
					?: chic_upload_url( 'chic-centre-suites-logo-no-text.png' );
				$_hdr_logo_alt = Chic_Page_Content::get_image_alt( $_hdr_logo_pid, 'hdr', 'logo' )
					?: get_bloginfo( 'name' );
				?>
				<img
					src="<?php echo esc_url( $_hdr_logo_src ); ?>"
					alt="<?php echo esc_attr( $_hdr_logo_alt ); ?>"
					class="site-header__logo"
					loading="eager"
				>
			</a>

			<div class="site-header__tools">
				<?php
				$_chic_cur_lang   = chic_get_current_lang();
				$_chic_other_lang = 'en' === $_chic_cur_lang ? 'el' : 'en';
				$_chic_desk_label = [ 'en' => 'EN', 'el' => 'ΕΛ' ][ $_chic_other_lang ];
				$_chic_flag_code  = [ 'en' => 'gb', 'el' => 'gr' ][ $_chic_other_lang ];
				$_chic_uploads    = wp_upload_dir();
				$_chic_flag_url   = empty( $_chic_uploads['error'] ) ? trailingslashit( $_chic_uploads['baseurl'] ) . 'flag-' . $_chic_flag_code . '.png' : '';
				?>
				<div class="site-header__lang" role="navigation" aria-label="Language">
					<a href="<?php echo esc_url( chic_lang_switch_url( $_chic_other_lang ) ); ?>"
					   class="site-header__lang-btn"
					   hreflang="<?php echo esc_attr( $_chic_other_lang ); ?>">
						<?php if ( $_chic_flag_url ) : ?>
							<img class="site-header__lang-flag" src="<?php echo esc_url( $_chic_flag_url ); ?>" alt="" aria-hidden="true">
						<?php endif; ?>
						<?php echo esc_html( $_chic_desk_label ); ?>
					</a>
				</div>
				<nav class="primary-navigation" aria-label="<?php echo ta( 'Primary' ); ?>">
					<ul class="nav-menu" role="list">
						<?php chic_header_render_items( false ); ?>
					</ul>
					<button
						type="button"
						class="mobile-nav-toggle"
						aria-controls="mobile-nav"
						aria-expanded="false"
						aria-label="<?php echo ta( 'Open menu' ); ?>"
					>
						<span class="mobile-nav-toggle__bar"></span>
						<span class="mobile-nav-toggle__bar"></span>
						<span class="mobile-nav-toggle__bar"></span>
					</button>
				</nav>
			</div>

		</div>
		<div class="site-mega-backdrop" id="site-mega-backdrop" aria-hidden="true"></div>
	</header>

	<div id="mobile-nav" class="mobile-nav-overlay" aria-hidden="true">
		<div class="mobile-nav__inner">
			<ul class="mobile-nav__links" role="list">
				<?php chic_header_render_items( true ); ?>
			</ul>
			<?php
			$_chic_cur_lang_mob   = chic_get_current_lang();
			$_chic_other_lang_mob = 'en' === $_chic_cur_lang_mob ? 'el' : 'en';
			$_chic_mob_label      = [ 'en' => 'English', 'el' => 'Ελληνικά' ][ $_chic_other_lang_mob ];
			$_chic_mob_flag_code  = [ 'en' => 'gb', 'el' => 'gr' ][ $_chic_other_lang_mob ];
			$_chic_mob_uploads    = wp_upload_dir();
			$_chic_mob_flag_url   = empty( $_chic_mob_uploads['error'] ) ? trailingslashit( $_chic_mob_uploads['baseurl'] ) . 'flag-' . $_chic_mob_flag_code . '.png' : '';
			?>
			<div class="mobile-nav__lang">
				<a href="<?php echo esc_url( chic_lang_switch_url( $_chic_other_lang_mob ) ); ?>"
				   class="mobile-nav__lang-btn"
				   hreflang="<?php echo esc_attr( $_chic_other_lang_mob ); ?>">
					<?php if ( $_chic_mob_flag_url ) : ?>
						<img class="mobile-nav__lang-flag" src="<?php echo esc_url( $_chic_mob_flag_url ); ?>" alt="" aria-hidden="true">
					<?php endif; ?>
					<?php echo esc_html( $_chic_mob_label ); ?>
				</a>
			</div>
			<div class="mobile-nav__footer">
				<a href="<?php echo esc_url( chic_header_book_now_url() ); ?>" class="mobile-nav__cta btn btn--primary">
					<?php te_uc( 'Book Now' ); ?>
				</a>
			</div>
		</div>
	</div>
	<?php
}
