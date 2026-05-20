<?php
defined( 'ABSPATH' ) || exit;

require_once __DIR__ . '/class-chic-acf-base.php';

class Chic_Acf_Home extends Chic_Acf_Base {

	public function __construct() {
		$this->register();
	}

	private function register(): void {
		$fields = array_merge(
			self::tab( 'Hero', 'home_hero' ),
			self::bilingual( 'chic_home_hero_aria',        'Hero Carousel Aria-Label' ),
			self::bilingual( 'chic_home_hero_heading',     'Hero Heading', 'text' ),
			self::bilingual( 'chic_home_hero_subhead',     'Hero Subheading', 'textarea' ),
			self::bilingual( 'chic_home_hero_cta_label',   'Hero CTA Label' ),
			[ [
				'key'   => 'field_chic_home_hero_cta_url',
				'name'  => 'chic_home_hero_cta_url',
				'label' => 'Hero CTA URL',
				'type'  => 'url',
			] ],
			self::bilingual( 'chic_home_hero_scroll_aria', 'Scroll Chevron Aria-Label' ),
			self::tab( 'Intro', 'home_intro' ),
			self::bilingual( 'chic_home_intro_heading', 'Intro Heading' ),
			self::bilingual( 'chic_home_intro_body',    'Intro Body', 'textarea' ),
			self::tab( 'Carousels', 'home_carousels' ),
			self::bilingual( 'chic_home_carousel_prev_aria', 'Previous Button Aria-Label' ),
			self::bilingual( 'chic_home_carousel_next_aria', 'Next Button Aria-Label' )
		);

		acf_add_local_field_group( [
			'key'      => 'group_chic_home',
			'title'    => 'Home Page Content',
			'fields'   => $fields,
			'location' => [ [ [
				'param'    => 'page_template',
				'operator' => '==',
				'value'    => 'page-home.php',
			] ] ],
		] );
	}
}
