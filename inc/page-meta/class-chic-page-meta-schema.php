<?php
defined( 'ABSPATH' ) || exit;

/**
 * Schema registry for page-meta defaults.
 *
 * Each schema file (schema/schema-{context}.php) returns an array keyed by
 * field slug.  Entries look like:
 *
 *   'hero_heading' => [
 *       'type'    => 'text',          // text|textarea|wysiwyg|image|url|number|select
 *       'label'   => 'Hero Heading',  // admin label
 *       'default' => 'Stay in...',    // EN default (text / textarea / wysiwyg / url / number)
 *   ]
 *
 *   'hero_image' => [
 *       'type'        => 'image',
 *       'label'       => 'Hero Image',
 *       'default_url' => 'https://...',
 *       'alt_default' => 'Alt text',
 *   ]
 *
 * Schema files are registered explicitly in chic-page-meta.php.
 */
class Chic_Page_Meta_Schema {

	/** @var array<string, array<string, array>> context => [ key => definition ] */
	private static array $registry = [];

	/**
	 * Register a context.  Called from each schema/schema-*.php file.
	 */
	public static function register( string $context, array $schema ): void {
		self::$registry[ $context ] = $schema;
	}

	/**
	 * Returns the full definition for a single field, or null if unknown.
	 */
	public static function get_field( string $context, string $key ): ?array {
		return self::$registry[ $context ][ $key ] ?? null;
	}

	/**
	 * Returns the English default value for a field.
	 * For text/textarea/wysiwyg/url/number this is the 'default' key.
	 */
	public static function get_default( string $context, string $key ): string {
		$def = self::get_field( $context, $key );
		return (string) ( $def['default'] ?? '' );
	}

	/**
	 * Returns the default image URL for an image-type field.
	 */
	public static function get_default_url( string $context, string $key ): string {
		$def = self::get_field( $context, $key );
		return (string) ( $def['default_url'] ?? '' );
	}

	/**
	 * Returns the English default alt text for an image-type field.
	 */
	public static function get_alt_default( string $context, string $key ): string {
		$def = self::get_field( $context, $key );
		return (string) ( $def['alt_default'] ?? '' );
	}
}
