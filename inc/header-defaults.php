<?php
defined( 'ABSPATH' ) || exit;

function chic_header_default_menu() {
	$ph = '#';
	return [
		[
			'label'       => 'Home',
			'link_type'   => 'url',
			'url'         => 'https://davidb1553.sg-host.com/',
			'page'        => 0,
			'is_mega'     => 0,
			'mega_groups' => [],
		],
		[
			'label'       => 'Explore Athens',
			'link_type'   => 'url',
			'url'         => 'https://davidb1553.sg-host.com/explore-and-experience-athens/',
			'page'        => 0,
			'is_mega'     => 0,
			'mega_groups' => [],
		],
		[
			'label'       => 'Buildings',
			'link_type'   => 'placeholder',
			'url'         => $ph,
			'page'        => 0,
			'is_mega'     => 1,
			'mega_groups' => [],
		],
		[
			'label'       => 'Reviews',
			'link_type'   => 'url',
			'url'         => 'https://davidb1553.sg-host.com/reviews-what-people-say/',
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
			'url'         => 'https://direct-book.com/properties/chiccentresuitesathens',
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
