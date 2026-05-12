<?php
defined( 'ABSPATH' ) || exit;

define( 'CHIC_PLACEHOLDER_IMG', 'https://davidb1553.sg-host.com/wp-content/uploads/off-your-first-reservation.jpg' );

// Thin wrapper so header-nav.php callers don't need to know about header-acf.php.
function chic_header_get_config_id(): int {
	return chic_header_config_id();
}

function chic_header_item_href( array $item ): string {
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

function chic_header_render_items( bool $mobile = false ): void {
	$items = get_field( 'menu_items', chic_header_config_id() );
	if ( empty( $items ) ) return;

	foreach ( $items as $item ) {
		$label   = esc_html( $item['label'] ?? '' );
		$href    = chic_header_item_href( $item );
		$is_mega = ! empty( $item['is_mega'] ) && ! empty( $item['mega_groups'] );
		$classes = 'menu-item';
		if ( $is_mega ) $classes .= ' menu-item-has-children menu-item-has-mega';

		echo '<li class="' . esc_attr( $classes ) . '">';

		if ( $is_mega ) {
			echo '<a href="#" aria-expanded="false" aria-haspopup="true">';
			echo $label;
			echo '<span class="nav-submenu-chevron" aria-hidden="true">';
			echo '<svg xmlns="http://www.w3.org/2000/svg" width="10" height="6" viewBox="0 0 10 6" fill="none" aria-hidden="true"><path d="M1 1L5 5L9 1" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>';
			echo '</span></a>';
			chic_header_render_mega_panel( $item['mega_groups'] );
		} else {
			echo '<a href="' . esc_attr( $href ) . '">' . $label . '</a>';
		}

		echo '</li>';
	}
}

function chic_header_render_mega_panel( array $groups ): void {
	$all_suites = [];
	foreach ( $groups as $g ) {
		foreach ( ( $g['suites'] ?? [] ) as $s ) {
			$all_suites[] = $s;
		}
	}
	?>
	<div class="nav-submenu-clip mega-panel mobile-disclosure__panel" data-mega-clip>
		<div class="mega-panel__body nav-submenu-body mobile-disclosure__inner">
			<div class="mega-panel__height" data-mega-height>
				<div class="mega-panel__surface">
					<div class="mega-panel__layout">
						<div class="mega-panel__grid">

							<?php foreach ( $groups as $group ) : ?>
								<div class="mega-panel__column">
									<?php if ( ! empty( $group['building_label'] ) ) : ?>
										<p class="mega-panel__eyebrow"><?php echo esc_html( $group['building_label'] ); ?></p>
									<?php endif; ?>
									<ul class="mega-panel__list" role="list">
										<?php foreach ( ( $group['suites'] ?? [] ) as $suite ) :
											$suite_href = chic_header_item_href( $suite );
											$suite_text = trim( $suite['label'] ?? '' );
										?>
											<li class="menu-item">
												<a href="<?php echo esc_attr( $suite_href ); ?>">
													<span class="mega-panel__link-text"><?php echo esc_html( $suite_text ); ?></span>
												</a>
											</li>
										<?php endforeach; ?>
									</ul>
								</div>
							<?php endforeach; ?>

							<div class="mega-panel__column mega-panel__preview" aria-hidden="true">
								<div class="mega-panel__preview-img mega-panel__preview-img--active">
									<img src="<?php echo esc_url( CHIC_PLACEHOLDER_IMG ); ?>" alt="" loading="lazy">
								</div>
								<?php foreach ( $all_suites as $suite ) :
									$img  = ! empty( $suite['image'] ) ? $suite['image'] : CHIC_PLACEHOLDER_IMG;
									$stxt = trim( $suite['label'] ?? '' );
								?>
									<div class="mega-panel__preview-img" data-mega-preview-for="<?php echo esc_attr( $stxt ); ?>">
										<img src="<?php echo esc_url( $img ); ?>" alt="<?php echo esc_attr( $stxt ); ?>" loading="lazy">
									</div>
								<?php endforeach; ?>
							</div>

						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
	<?php
}

function chic_header_book_now_url(): string {
	$items = get_field( 'menu_items', chic_header_config_id() );
	if ( ! empty( $items ) ) {
		foreach ( $items as $item ) {
			if ( 'book now' === strtolower( trim( $item['label'] ?? '' ) ) ) {
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

			<a class="site-header__brand" href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home">
				<?php
				$logo = get_custom_logo();
				if ( $logo ) {
					echo $logo;
				} else {
					echo '<span class="site-header__brand-text">' . esc_html( get_bloginfo( 'name' ) ) . '</span>';
				}
				?>
			</a>

			<div class="site-header__tools">
				<nav class="primary-navigation" aria-label="<?php esc_attr_e( 'Primary', 'bellevue' ); ?>">
					<ul class="nav-menu" role="list">
						<?php chic_header_render_items( false ); ?>
					</ul>
					<button
						type="button"
						class="mobile-nav-toggle"
						aria-controls="mobile-nav"
						aria-expanded="false"
						aria-label="<?php esc_attr_e( 'Open menu', 'bellevue' ); ?>"
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
			<button
				type="button"
				class="mobile-nav-close"
				aria-label="<?php esc_attr_e( 'Close menu', 'bellevue' ); ?>"
			>
				<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" aria-hidden="true">
					<path d="M18 6L6 18M6 6L18 18" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
				</svg>
			</button>
			<ul class="mobile-nav__links" role="list">
				<?php chic_header_render_items( true ); ?>
			</ul>
			<div class="mobile-nav__footer">
				<a href="<?php echo esc_url( chic_header_book_now_url() ); ?>" class="mobile-nav__cta">
					<?php esc_html_e( 'Book Now', 'bellevue' ); ?>
				</a>
			</div>
		</div>
	</div>
	<?php
}
