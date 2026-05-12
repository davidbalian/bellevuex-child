<?php

/**
 * Building configuration and suite data helper for the Home page template.
 */

function chic_home_buildings(): array {
	return [
		[
			'term'  => 'thiseos-11',
			'label' => '11 Thiseos, Floor 1, 10562 Athens',
			'maps'  => 'https://maps.app.goo.gl/hCFxRXMY6xKPDG3T7',
		],
		[
			'term'  => 'thiseos13',
			'label' => '13 Thiseos, Floor 4, 10562 Athens',
			'maps'  => 'https://maps.app.goo.gl/YbpkkEj8YbHoQoV4A',
		],
		[
			'term'  => 'chavriou2',
			'label' => '2 Chavriou, Floor 2, 10562 Athens',
			'maps'  => 'https://maps.app.goo.gl/kmVN1pyNxdU6rt8m8',
		],
	];
}

/**
 * Returns simplified card data for all suites in a given building taxonomy term.
 *
 * @param string $term_slug  mphb_room_type_category slug (e.g. 'thiseos-11').
 * @return array[]  Each item: { id, title, permalink, thumb_url, capacity_label }
 */
function chic_home_get_suites( string $term_slug ): array {
	$query = new WP_Query( [
		'post_type'      => 'mphb_room_type',
		'posts_per_page' => -1,
		'no_found_rows'  => true,
		'tax_query'      => [ [
			'taxonomy' => 'mphb_room_type_category',
			'field'    => 'slug',
			'terms'    => $term_slug,
		] ],
		'orderby' => 'menu_order title',
		'order'   => 'ASC',
	] );

	$cards = [];

	foreach ( $query->posts as $post ) {
		$terms    = get_the_terms( $post->ID, 'mphb_room_type_category' );
		$capacity = '';

		if ( $terms && ! is_wp_error( $terms ) ) {
			foreach ( $terms as $term ) {
				if ( preg_match( '/^up-to-(\d+)-guests$/', $term->slug, $m ) ) {
					$capacity = 'Hosting up to ' . $m[1] . ' guests';
					break;
				}
			}
		}

		$cards[] = [
			'id'              => $post->ID,
			'title'           => get_the_title( $post ),
			'permalink'       => get_permalink( $post ),
			'thumb_url'       => get_the_post_thumbnail_url( $post->ID, 'large' ) ?: '',
			'capacity_label'  => $capacity,
		];
	}

	return $cards;
}
