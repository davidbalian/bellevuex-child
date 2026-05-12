<?php
defined( 'ABSPATH' ) || exit;

function chic_header_default_menu() {
	$ph = '#';
	return [
		[
			'label'       => 'Home',
			'link_type'   => 'placeholder',
			'url'         => $ph,
			'page'        => 0,
			'is_mega'     => 0,
			'mega_groups' => [],
		],
		[
			'label'       => 'Explore Athens',
			'link_type'   => 'placeholder',
			'url'         => $ph,
			'page'        => 0,
			'is_mega'     => 0,
			'mega_groups' => [],
		],
		[
			'label'       => 'Suites',
			'link_type'   => 'placeholder',
			'url'         => $ph,
			'page'        => 0,
			'is_mega'     => 1,
			'mega_groups' => [
				[
					'building_label' => 'Chavriou 2',
					'image'          => 0,
					'suites'         => [
						[ 'label' => 'Suite 1', 'link_type' => 'url', 'url' => $ph, 'page' => 0, 'image' => 0 ],
						[ 'label' => 'Suite 2', 'link_type' => 'url', 'url' => $ph, 'page' => 0, 'image' => 0 ],
						[ 'label' => 'Suite 3', 'link_type' => 'url', 'url' => $ph, 'page' => 0, 'image' => 0 ],
						[ 'label' => 'Suite 4', 'link_type' => 'url', 'url' => $ph, 'page' => 0, 'image' => 0 ],
						[ 'label' => 'Suite 5', 'link_type' => 'url', 'url' => $ph, 'page' => 0, 'image' => 0 ],
						[ 'label' => 'Suite 6', 'link_type' => 'url', 'url' => $ph, 'page' => 0, 'image' => 0 ],
					],
				],
				[
					'building_label' => 'Thiseos 11',
					'image'          => 0,
					'suites'         => [
						[ 'label' => 'Avra Suite',      'link_type' => 'url', 'url' => $ph, 'page' => 0, 'image' => 0 ],
						[ 'label' => 'Zakynthos Suite', 'link_type' => 'url', 'url' => $ph, 'page' => 0, 'image' => 0 ],
						[ 'label' => 'Santorini Suite', 'link_type' => 'url', 'url' => $ph, 'page' => 0, 'image' => 0 ],
						[ 'label' => 'Kohili Suite',    'link_type' => 'url', 'url' => $ph, 'page' => 0, 'image' => 0 ],
						[ 'label' => 'Korali Suite',    'link_type' => 'url', 'url' => $ph, 'page' => 0, 'image' => 0 ],
						[ 'label' => 'Mykonos Suite',   'link_type' => 'url', 'url' => $ph, 'page' => 0, 'image' => 0 ],
						[ 'label' => 'Paros Suite',     'link_type' => 'url', 'url' => $ph, 'page' => 0, 'image' => 0 ],
						[ 'label' => 'Ammos Suite',     'link_type' => 'url', 'url' => $ph, 'page' => 0, 'image' => 0 ],
						[ 'label' => 'Ermou Suite',     'link_type' => 'url', 'url' => $ph, 'page' => 0, 'image' => 0 ],
						[ 'label' => 'Pelagos Suite',   'link_type' => 'url', 'url' => $ph, 'page' => 0, 'image' => 0 ],
					],
				],
				[
					'building_label' => 'Thiseos 13',
					'image'          => 0,
					'suites'         => [
						[ 'label' => 'Ocean Suite',    'link_type' => 'url', 'url' => $ph, 'page' => 0, 'image' => 0 ],
						[ 'label' => 'Ginger Suite',   'link_type' => 'url', 'url' => $ph, 'page' => 0, 'image' => 0 ],
						[ 'label' => 'Gray Suite',     'link_type' => 'url', 'url' => $ph, 'page' => 0, 'image' => 0 ],
						[ 'label' => 'Sunshine Suite', 'link_type' => 'url', 'url' => $ph, 'page' => 0, 'image' => 0 ],
						[ 'label' => 'Forest Suite',   'link_type' => 'url', 'url' => $ph, 'page' => 0, 'image' => 0 ],
					],
				],
			],
		],
		[
			'label'       => 'Reviews',
			'link_type'   => 'placeholder',
			'url'         => $ph,
			'page'        => 0,
			'is_mega'     => 0,
			'mega_groups' => [],
		],
		[
			'label'       => 'Signup / Login',
			'link_type'   => 'url',
			'url'         => $ph,
			'page'        => 0,
			'is_mega'     => 0,
			'mega_groups' => [],
		],
		[
			'label'       => 'Book Now',
			'link_type'   => 'url',
			'url'         => $ph,
			'page'        => 0,
			'is_mega'     => 0,
			'mega_groups' => [],
		],
	];
}

add_action( 'admin_init', 'chic_header_seed_defaults_once' );
function chic_header_seed_defaults_once() {
	$post_id = chic_header_config_id();
	if ( ! $post_id ) return;
	$existing = get_post_meta( $post_id, '_chic_header_menu', true );
	if ( ! empty( $existing ) ) return;
	update_post_meta( $post_id, '_chic_header_menu', chic_header_default_menu() );
}
