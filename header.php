<?php
/**
 * Chic Centre Suites — fallback header.php.
 * Only used if the parent theme calls get_header(). The custom header
 * is injected via output buffering in functions.php regardless.
 */
?><!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo( 'charset' ); ?>">
<meta name="viewport" content="width=device-width, initial-scale=1">
<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
