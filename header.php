<?php
/**
 * Chic Centre Suites — site header.
 * Replaces the parent theme header.php when get_header() is called.
 * Also output via wp_body_open hook for parent themes that bypass get_header().
 */
?><!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo( 'charset' ); ?>">
<meta name="viewport" content="width=device-width, initial-scale=1">
<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php
wp_body_open();
// Render our header here too (static flag in chic_output_custom_header prevents double output
// if the wp_body_open hook already fired it).
chic_output_custom_header();
?>
