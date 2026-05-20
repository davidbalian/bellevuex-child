<?php
defined( 'ABSPATH' ) || exit;

require_once __DIR__ . '/class-chic-acf-base.php';

class Chic_Acf_Testimonials extends Chic_Acf_Base {

	public function __construct() {
		$this->register();
	}

	private function register(): void {
		$fields = array_merge(
			self::tab( 'Hero', 'tsm_hero' ),
			self::image_with_alt( 'chic_tsm_hero_image', 'Hero Image' ),
			self::bilingual( 'chic_tsm_hero_heading', 'Page Heading' ),
			self::bilingual( 'chic_tsm_section_aria',  'Reviews Section Aria-Label' ),
			self::tab( 'Reviews', 'tsm_reviews' ),
			[ $this->reviews_repeater() ]
		);

		acf_add_local_field_group( [
			'key'      => 'group_chic_testimonials',
			'title'    => 'Testimonials Page Content',
			'fields'   => $fields,
			'location' => [ [ [
				'param'    => 'page_template',
				'operator' => '==',
				'value'    => 'page-testimonials.php',
			] ] ],
		] );
	}

	private function reviews_repeater(): array {
		return [
			'key'          => 'field_chic_tsm_reviews',
			'name'         => 'chic_tsm_reviews',
			'label'        => 'Reviews',
			'type'         => 'repeater',
			'layout'       => 'block',
			'button_label' => 'Add Review',
			'sub_fields'   => [
				[
					'key'     => 'field_chic_tsm_rev_author',
					'name'    => 'author',
					'label'   => 'Author Name',
					'type'    => 'text',
					'wrapper' => [ 'width' => '40', 'class' => '', 'id' => '' ],
				],
				[
					'key'          => 'field_chic_tsm_rev_cc',
					'name'         => 'country_code',
					'label'        => 'Country Code (2-char, e.g. gb)',
					'type'         => 'text',
					'maxlength'    => 3,
					'wrapper'      => [ 'width' => '15', 'class' => '', 'id' => '' ],
				],
				[
					'key'           => 'field_chic_tsm_rev_suite',
					'name'          => 'suite',
					'label'         => 'Suite (link to room)',
					'type'          => 'post_object',
					'post_type'     => [ 'mphb_room_type' ],
					'return_format' => 'id',
					'allow_null'    => 1,
					'wrapper'       => [ 'width' => '45', 'class' => '', 'id' => '' ],
				],
				[
					'key'     => 'field_chic_tsm_rev_slabel_en',
					'name'    => 'suite_label_en',
					'label'   => 'Suite Label — English (blank = auto)',
					'type'    => 'text',
					'wrapper' => [ 'width' => '50', 'class' => '', 'id' => '' ],
				],
				[
					'key'     => 'field_chic_tsm_rev_slabel_el',
					'name'    => 'suite_label_el',
					'label'   => 'Suite Label — Ελληνικά (blank = auto)',
					'type'    => 'text',
					'wrapper' => [ 'width' => '50', 'class' => '', 'id' => '' ],
				],
				[
					'key'     => 'field_chic_tsm_rev_src_en',
					'name'    => 'source_label_en',
					'label'   => 'Source Label — English (e.g. Review from Booking.com)',
					'type'    => 'text',
					'wrapper' => [ 'width' => '50', 'class' => '', 'id' => '' ],
				],
				[
					'key'     => 'field_chic_tsm_rev_src_el',
					'name'    => 'source_label_el',
					'label'   => 'Source Label — Ελληνικά',
					'type'    => 'text',
					'wrapper' => [ 'width' => '50', 'class' => '', 'id' => '' ],
				],
				[
					'key'     => 'field_chic_tsm_rev_date_en',
					'name'    => 'date_label_en',
					'label'   => 'Date Label — English (e.g. July 2021)',
					'type'    => 'text',
					'wrapper' => [ 'width' => '50', 'class' => '', 'id' => '' ],
				],
				[
					'key'     => 'field_chic_tsm_rev_date_el',
					'name'    => 'date_label_el',
					'label'   => 'Date Label — Ελληνικά',
					'type'    => 'text',
					'wrapper' => [ 'width' => '50', 'class' => '', 'id' => '' ],
				],
				[
					'key'     => 'field_chic_tsm_rev_review_en',
					'name'    => 'review_en',
					'label'   => 'Review Text — English',
					'type'    => 'textarea',
					'rows'    => 5,
					'wrapper' => [ 'width' => '50', 'class' => '', 'id' => '' ],
				],
				[
					'key'     => 'field_chic_tsm_rev_review_el',
					'name'    => 'review_el',
					'label'   => 'Review Text — Ελληνικά',
					'type'    => 'textarea',
					'rows'    => 5,
					'wrapper' => [ 'width' => '50', 'class' => '', 'id' => '' ],
				],
			],
		];
	}
}
