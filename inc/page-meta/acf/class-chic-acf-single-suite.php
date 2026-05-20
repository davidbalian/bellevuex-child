<?php
defined( 'ABSPATH' ) || exit;

require_once __DIR__ . '/class-chic-acf-base.php';

class Chic_Acf_Single_Suite extends Chic_Acf_Base {

	public function __construct() {
		$this->register();
	}

	private function register(): void {
		$fields = array_merge(
			self::tab( 'Description', 'suite_desc' ),
			[
				[
					'key'           => 'field_chic_suite_description_en',
					'name'          => 'chic_suite_description_en',
					'label'         => 'Suite Description — English',
					'type'          => 'wysiwyg',
					'toolbar'       => 'basic',
					'media_upload'  => 0,
					'wrapper'       => [ 'width' => '50', 'class' => '', 'id' => '' ],
				],
				[
					'key'           => 'field_chic_suite_description_el',
					'name'          => 'chic_suite_description_el',
					'label'         => 'Suite Description — Ελληνικά',
					'type'          => 'wysiwyg',
					'toolbar'       => 'basic',
					'media_upload'  => 0,
					'wrapper'       => [ 'width' => '50', 'class' => '', 'id' => '' ],
				],
			],
			self::tab( 'Amenities', 'suite_amenities' ),
			self::bilingual( 'chic_suite_capacity', 'Capacity Label' ),
			self::bilingual( 'chic_suite_sofa',     'Sofa / Feature Label' ),
			[
				[
					'key'          => 'field_chic_suite_highlight',
					'name'         => 'chic_suite_highlight',
					'label'        => 'Highlight Feature (drives icon)',
					'type'         => 'select',
					'choices'      => [
						'jacuzzi' => 'Jacuzzi',
						'balcony' => 'Balcony',
						'terrace' => 'Terrace',
						'shower'  => 'Shower',
					],
					'allow_null'   => 1,
					'wrapper'      => [ 'width' => '33', 'class' => '', 'id' => '' ],
				],
				[
					'key'     => 'field_chic_suite_size',
					'name'    => 'chic_suite_size',
					'label'   => 'Size (m²)',
					'type'    => 'number',
					'wrapper' => [ 'width' => '33', 'class' => '', 'id' => '' ],
				],
				[
					'key'     => 'field_chic_suite_booking_url',
					'name'    => 'chic_suite_booking_url',
					'label'   => 'Booking URL Override (blank = use global)',
					'type'    => 'url',
					'wrapper' => [ 'width' => '34', 'class' => '', 'id' => '' ],
				],
			]
		);

		acf_add_local_field_group( [
			'key'      => 'group_chic_suite',
			'title'    => 'Suite Content',
			'fields'   => $fields,
			'location' => [ [ [
				'param'    => 'post_type',
				'operator' => '==',
				'value'    => 'mphb_room_type',
			] ] ],
		] );
	}
}
