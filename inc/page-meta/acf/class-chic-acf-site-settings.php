<?php
defined( 'ABSPATH' ) || exit;

require_once __DIR__ . '/class-chic-acf-base.php';

class Chic_Acf_Site_Settings extends Chic_Acf_Base {

	public function __construct() {
		if ( function_exists( 'acf_add_options_page' ) ) {
			acf_add_options_page( [
				'page_title' => 'Site Settings',
				'menu_title' => 'Site Settings',
				'menu_slug'  => 'chic-site-settings',
				'capability' => 'manage_options',
				'position'   => 5,
				'icon_url'   => 'dashicons-admin-settings',
			] );
		}
		$this->register();
	}

	private function register(): void {
		$fields = array_merge(
			self::tab( 'Common Labels', 'ss_labels' ),
			self::labels_fields(),
			self::tab( 'Buildings', 'ss_buildings' ),
			self::buildings_repeater(),
			self::tab( 'Suite Facilities', 'ss_facilities' ),
			self::facilities_repeater(),
			self::tab( 'Hygiene Notes', 'ss_hygiene' ),
			self::hygiene_repeater(),
			self::tab( 'Footer', 'ss_footer' ),
			self::footer_fields(),
			self::tab( '404 Page', 'ss_404' ),
			self::page_404_fields()
		);

		acf_add_local_field_group( [
			'key'      => 'group_chic_site_settings',
			'title'    => 'Site Settings',
			'fields'   => $fields,
			'location' => [ [ [
				'param'    => 'options_page',
				'operator' => '==',
				'value'    => 'chic-site-settings',
			] ] ],
		] );
	}

	private static function labels_fields(): array {
		return array_merge(
			[ [
				'key'   => 'field_chic_ss_booking_url',
				'name'  => 'chic_ss_booking_url',
				'label' => 'Default Booking URL',
				'type'  => 'url',
			] ],
			self::bilingual( 'chic_ss_book_now',          'Book Now' ),
			self::bilingual( 'chic_ss_view',               'View (link text)' ),
			self::bilingual( 'chic_ss_photos',             'Photos (section heading)' ),
			self::bilingual( 'chic_ss_suite_description',  'Suite Description (heading)' ),
			self::bilingual( 'chic_ss_suite_facilities',   'Suite Facilities (heading)' ),
			self::bilingual( 'chic_ss_prev_photo_aria',    'Previous Photo Aria-Label' ),
			self::bilingual( 'chic_ss_next_photo_aria',    'Next Photo Aria-Label' ),
			self::bilingual( 'chic_ss_contact_info',       'Contact Info (column heading)' ),
			self::bilingual( 'chic_ss_useful_links',       'Useful Links (column heading)' ),
			self::bilingual( 'chic_ss_awards_heading',     'Awards (column heading)' ),
			self::bilingual( 'chic_ss_follow_us',          'Follow Us (column heading)' )
		);
	}

	private static function buildings_repeater(): array {
		return [ [
			'key'          => 'field_chic_ss_buildings',
			'name'         => 'chic_ss_buildings',
			'label'        => 'Buildings',
			'type'         => 'repeater',
			'layout'       => 'block',
			'button_label' => 'Add Building',
			'sub_fields'   => [
				[
					'key'               => 'field_chic_ss_bld_term',
					'name'              => 'term_slug',
					'label'             => 'Taxonomy Term Slug (do not change)',
					'type'              => 'text',
					'instructions'      => 'The mphb_room_type_category slug. E.g. thiseos-11',
					'wrapper'           => [ 'width' => '30', 'class' => '', 'id' => '' ],
				],
				[
					'key'     => 'field_chic_ss_bld_slabel_en',
					'name'    => 'short_label_en',
					'label'   => 'Short Label — English',
					'type'    => 'text',
					'wrapper' => [ 'width' => '35', 'class' => '', 'id' => '' ],
				],
				[
					'key'     => 'field_chic_ss_bld_slabel_el',
					'name'    => 'short_label_el',
					'label'   => 'Short Label — Ελληνικά',
					'type'    => 'text',
					'wrapper' => [ 'width' => '35', 'class' => '', 'id' => '' ],
				],
				[
					'key'     => 'field_chic_ss_bld_addr_en',
					'name'    => 'address_en',
					'label'   => 'Full Address — English',
					'type'    => 'text',
					'wrapper' => [ 'width' => '50', 'class' => '', 'id' => '' ],
				],
				[
					'key'     => 'field_chic_ss_bld_addr_el',
					'name'    => 'address_el',
					'label'   => 'Full Address — Ελληνικά',
					'type'    => 'text',
					'wrapper' => [ 'width' => '50', 'class' => '', 'id' => '' ],
				],
				[
					'key'  => 'field_chic_ss_bld_maps',
					'name' => 'maps_url',
					'label' => 'Google Maps URL',
					'type' => 'url',
				],
				[
					'key'           => 'field_chic_ss_bld_img',
					'name'          => 'building_image',
					'label'         => 'Building Image',
					'type'          => 'image',
					'return_format' => 'array',
					'preview_size'  => 'medium',
				],
			],
		] ];
	}

	private static function facilities_repeater(): array {
		return [ [
			'key'          => 'field_chic_ss_facilities',
			'name'         => 'chic_ss_facilities',
			'label'        => 'Suite Facilities List',
			'type'         => 'repeater',
			'layout'       => 'table',
			'button_label' => 'Add Facility',
			'sub_fields'   => [
				[
					'key'     => 'field_chic_ss_fac_label_en',
					'name'    => 'label_en',
					'label'   => 'Label — English',
					'type'    => 'text',
					'wrapper' => [ 'width' => '50', 'class' => '', 'id' => '' ],
				],
				[
					'key'     => 'field_chic_ss_fac_label_el',
					'name'    => 'label_el',
					'label'   => 'Label — Ελληνικά',
					'type'    => 'text',
					'wrapper' => [ 'width' => '50', 'class' => '', 'id' => '' ],
				],
			],
		] ];
	}

	private static function hygiene_repeater(): array {
		return [ [
			'key'          => 'field_chic_ss_hygiene',
			'name'         => 'chic_ss_hygiene',
			'label'        => 'Hygiene Notes',
			'type'         => 'repeater',
			'layout'       => 'block',
			'button_label' => 'Add Note',
			'sub_fields'   => [
				[
					'key'     => 'field_chic_ss_hyg_head_en',
					'name'    => 'heading_en',
					'label'   => 'Heading — English',
					'type'    => 'text',
					'wrapper' => [ 'width' => '50', 'class' => '', 'id' => '' ],
				],
				[
					'key'     => 'field_chic_ss_hyg_head_el',
					'name'    => 'heading_el',
					'label'   => 'Heading — Ελληνικά',
					'type'    => 'text',
					'wrapper' => [ 'width' => '50', 'class' => '', 'id' => '' ],
				],
				[
					'key'     => 'field_chic_ss_hyg_body_en',
					'name'    => 'body_en',
					'label'   => 'Body — English',
					'type'    => 'textarea',
					'rows'    => 3,
					'wrapper' => [ 'width' => '50', 'class' => '', 'id' => '' ],
				],
				[
					'key'     => 'field_chic_ss_hyg_body_el',
					'name'    => 'body_el',
					'label'   => 'Body — Ελληνικά',
					'type'    => 'textarea',
					'rows'    => 3,
					'wrapper' => [ 'width' => '50', 'class' => '', 'id' => '' ],
				],
			],
		] ];
	}

	private static function footer_fields(): array {
		return array_merge(
			[
				[ 'key' => 'field_chic_footer_brand',       'name' => 'chic_footer_brand_name',  'label' => 'Brand Name',           'type' => 'text', 'wrapper' => [ 'width' => '50', 'class' => '', 'id' => '' ] ],
				[ 'key' => 'field_chic_footer_email',       'name' => 'chic_footer_email',        'label' => 'Contact Email',        'type' => 'email','wrapper' => [ 'width' => '50', 'class' => '', 'id' => '' ] ],
				[ 'key' => 'field_chic_footer_phone1',      'name' => 'chic_footer_phone_1',      'label' => 'Phone 1 (display)',    'type' => 'text', 'wrapper' => [ 'width' => '25', 'class' => '', 'id' => '' ] ],
				[ 'key' => 'field_chic_footer_phone1href',  'name' => 'chic_footer_phone_1_href', 'label' => 'Phone 1 (tel: href)',  'type' => 'text', 'wrapper' => [ 'width' => '25', 'class' => '', 'id' => '' ] ],
				[ 'key' => 'field_chic_footer_phone2',      'name' => 'chic_footer_phone_2',      'label' => 'Phone 2 (display)',    'type' => 'text', 'wrapper' => [ 'width' => '25', 'class' => '', 'id' => '' ] ],
				[ 'key' => 'field_chic_footer_phone2href',  'name' => 'chic_footer_phone_2_href', 'label' => 'Phone 2 (tel: href)',  'type' => 'text', 'wrapper' => [ 'width' => '25', 'class' => '', 'id' => '' ] ],
				[ 'key' => 'field_chic_footer_addr1',       'name' => 'chic_footer_address_1',    'label' => 'Address Line 1',       'type' => 'text', 'wrapper' => [ 'width' => '50', 'class' => '', 'id' => '' ] ],
				[ 'key' => 'field_chic_footer_addr2',       'name' => 'chic_footer_address_2',    'label' => 'Address Line 2',       'type' => 'text', 'wrapper' => [ 'width' => '50', 'class' => '', 'id' => '' ] ],
				[ 'key' => 'field_chic_footer_addr3',       'name' => 'chic_footer_address_3',    'label' => 'Address Line 3',       'type' => 'text', 'wrapper' => [ 'width' => '50', 'class' => '', 'id' => '' ] ],
				[ 'key' => 'field_chic_footer_gemi',        'name' => 'chic_footer_gemi',         'label' => 'ΓΕΜΗ Number',          'type' => 'text', 'wrapper' => [ 'width' => '50', 'class' => '', 'id' => '' ] ],
			],
			self::bilingual( 'chic_footer_privacy_label', 'Privacy Policy Link Text' ),
			self::bilingual( 'chic_footer_cookie_label',  'Cookie Policy Link Text' ),
			self::bilingual( 'chic_footer_terms_label',   'Terms & Conditions Link Text' ),
			self::bilingual( 'chic_footer_all_rights',    'Copyright Suffix' ),
			self::bilingual( 'chic_footer_developed_by',  '"Developed by" Label' ),
			[ self::awards_repeater() ],
			[ self::social_repeater() ]
		);
	}

	private static function awards_repeater(): array {
		return [
			'key'          => 'field_chic_ss_awards',
			'name'         => 'chic_ss_awards',
			'label'        => 'Awards',
			'type'         => 'repeater',
			'layout'       => 'block',
			'button_label' => 'Add Award',
			'sub_fields'   => [
				[
					'key'           => 'field_chic_ss_award_img',
					'name'          => 'image',
					'label'         => 'Award Image',
					'type'          => 'image',
					'return_format' => 'array',
					'preview_size'  => 'thumbnail',
				],
				[
					'key'     => 'field_chic_ss_award_alt_en',
					'name'    => 'alt_en',
					'label'   => 'Alt Text — English',
					'type'    => 'text',
					'wrapper' => [ 'width' => '50', 'class' => '', 'id' => '' ],
				],
				[
					'key'     => 'field_chic_ss_award_alt_el',
					'name'    => 'alt_el',
					'label'   => 'Alt Text — Ελληνικά',
					'type'    => 'text',
					'wrapper' => [ 'width' => '50', 'class' => '', 'id' => '' ],
				],
			],
		];
	}

	private static function social_repeater(): array {
		return [
			'key'          => 'field_chic_ss_social',
			'name'         => 'chic_ss_social',
			'label'        => 'Social Links',
			'type'         => 'repeater',
			'layout'       => 'table',
			'button_label' => 'Add Social Link',
			'sub_fields'   => [
				[
					'key'     => 'field_chic_ss_soc_platform',
					'name'    => 'platform',
					'label'   => 'Platform',
					'type'    => 'select',
					'choices' => [ 'instagram' => 'Instagram', 'facebook' => 'Facebook' ],
					'wrapper' => [ 'width' => '20', 'class' => '', 'id' => '' ],
				],
				[
					'key'     => 'field_chic_ss_soc_url',
					'name'    => 'url',
					'label'   => 'URL',
					'type'    => 'url',
					'wrapper' => [ 'width' => '40', 'class' => '', 'id' => '' ],
				],
				[
					'key'     => 'field_chic_ss_soc_aria_en',
					'name'    => 'aria_label_en',
					'label'   => 'Aria Label — English',
					'type'    => 'text',
					'wrapper' => [ 'width' => '20', 'class' => '', 'id' => '' ],
				],
				[
					'key'     => 'field_chic_ss_soc_aria_el',
					'name'    => 'aria_label_el',
					'label'   => 'Aria Label — Ελληνικά',
					'type'    => 'text',
					'wrapper' => [ 'width' => '20', 'class' => '', 'id' => '' ],
				],
			],
		];
	}

	private static function page_404_fields(): array {
		return array_merge(
			self::bilingual( 'chic_404_aria',         '404 Section Aria-Label' ),
			self::bilingual( 'chic_404_error_label',  '404 Error Tag' ),
			self::bilingual( 'chic_404_heading',      'Page Heading' ),
			self::bilingual( 'chic_404_body',         'Body Text', 'textarea' ),
			self::bilingual( 'chic_404_return_label', 'Return Link Label' )
		);
	}
}
