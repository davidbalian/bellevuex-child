<?php

add_action( 'wp_enqueue_scripts', 'enqueue_child_theme_style', 9999 );
function enqueue_child_theme_style() {
	wp_enqueue_style( 'dtbwp_css_child', get_stylesheet_directory_uri() . '/style.css', array(
		'dtbwp_style',
	), 1.0 );
}
add_filter( 'the_title', 'woo_title_order_received', 10, 2 );

function woo_title_order_received( $title, $id ) {
	if ( function_exists( 'is_order_received_page' ) && 
	     is_order_received_page() && get_the_ID() === $id ) {
		$title = "Thank you for your reservation!";
	}
	return $title;
}
add_filter('woocommerce_thankyou_order_received_text', 'woo_change_order_received_text', 10, 2 );
function woo_change_order_received_text( $str, $order ) {
    $new_str = 'Thank you for your reservation! Booking receipt and booking details will be emailed to you shortly.';
    return $new_str;
}
add_filter('woocommerce_order_details__title', 'woo_change_order_received_texttitle', 10, 2 );
function woo_change_order_received_texttitle( $str, $order ) {
    $new_str = 'Payment Details: ';
    return $new_str;
}

include( STYLESHEETPATH . '/customizer-polylang.php');

add_filter( 'woocommerce_order_button_text', 'bbloomer_rename_place_order_button', 9999 );
  
function bbloomer_rename_place_order_button() {
    echo '<script>document.getElementsByClassName("payment_method_cardlink_payment_gateway_woocommerce")[0].getElementsByTagName("img")[0].src="https://chiccentresuites.com/wp-content/uploads/cardlinkLogo.svg";</script>';
   return 'CardLink Payment Gateway'; 
}
add_filter( 'mphb_sc_checkout_submit_wrapper_frm_submit_button', 'mphb_sc_checkout_submit_wrapper_frm_submit_buttonm', 9999 );
  
function mphb_sc_checkout_submit_wrapper_frm_submit_buttonm() {
   return 'Billing Details'; 
}

function my_custom_function() {
if (is_page(20900)) {
    echo '
    <script>
   if (document.readyState === "complete") {
    // Document already fully loaded
    showhide();
} else {
    // Add event listener for DOMContentLoaded (fires when document is fully loaded)
    document.addEventListener("DOMContentLoaded", showhide);
}

function showhide() {
//document.getElementsByTagName("select")[0].options[0].innerText=" ";
document.getElementsByClassName("frm_button_submit")[0].style.float="right";
var url_string = window.location.href;
var url = new URL(url_string);
var bid = url.searchParams.get("bid");
var email = url.searchParams.get("email");
document.getElementById("field_nq1zi").value=bid;
document.getElementById("field_m4ts8").value=email;
    document.getElementById("frm_field_55_container").style.display="none";
     document.getElementById("frm_field_56_container").style.display="none";
     document.getElementById("frm_field_57_container").style.display="none";
    document.getElementById("field_hx6n7_label").setAttribute ("style", "text-align: center !important;color: red !important;border:none;background:none;font-size:17px");
    document.getElementById("field_hx6n7").disabled=true,
    document.getElementById("field_hx6n7").setAttribute ("style", "display: none !important;");

document.getElementsByTagName("select")[0].onmouseleave = function(){
    
   
if(document.getElementsByTagName("select")[0].value == "Request Special Services") {
  document.getElementById("frm_field_55_container").style.display="block";
  document.getElementById("frm_field_56_container").style.display="none";
  document.getElementById("frm_field_57_container").style.display="none"
}else{
document.getElementById("frm_field_55_container").style.display="none"
}
if(document.getElementsByTagName("select")[0].value == "Cancelation of Booking") {
  document.getElementById("frm_field_56_container").style.display="block";
  document.getElementById("frm_field_55_container").style.display="none";
  document.getElementById("frm_field_57_container").style.display="none";
}else{
document.getElementById("frm_field_56_container").style.display="none"
}

if(document.getElementsByTagName("select")[0].value == "Change Dates") {
  document.getElementById("frm_field_57_container").style.display="block"
  document.getElementById("frm_field_56_container").style.display="none";
  document.getElementById("frm_field_55_container").style.display="none"
}else{
document.getElementById("frm_field_57_container").style.display="none"
}

};
}

</script>

    
    ';
}
if (is_page(20819)) {
    echo '<script>
       if (document.readyState === "complete") {
    // Document already fully loaded
    changeText();
} else {
    // Add event listener for DOMContentLoaded (fires when document is fully loaded)
    document.addEventListener("DOMContentLoaded", changeText);
}

function changeText() {
    
    
    if (document.getElementsByClassName("mphb_sc_search_results-info")[0].innerText.includes("No accommodations found")){
document.getElementsByClassName("mphb_sc_search_results-info")[0].innerText = "Sorry there is no availability on selected dates. Please Choose alternative dates or suite"
}

}</script>';
}
}
add_action( 'wp_head', 'my_custom_function' );

add_filter ('mphb_sc_search_results_room_top','theme_wrap_image_sc_search_results_room_top');

function theme_wrap_image_sc_search_results_room_top(){
echo '<div class="theme-room-type-images-wrapper">';
}

add_filter ('mphb_sc_search_results_before_info','theme_wrap_info_sc_search_results_before_info');

function theme_wrap_info_sc_search_results_before_info(){
echo '</div>';
echo '<div class="theme-room-type-info-wrapper">';
}

add_filter ('mphb_sc_search_results_after_info','theme_wrap_info_sc_search_results_after_info');

function theme_wrap_info_sc_search_results_after_info(){
echo '</div>';
}

function Calendar_Sync() {
    $current_user = wp_get_current_user();
    $user_id = $current_user->ID;

    //echo "Logged-in User ID: $user_id";
	if($user_id == 7){
		echo '<script>
		setInterval(function() {
     window.location.href="https://chiccentresuites.com/wp-admin/admin.php?page=mphb_ical_import&action=sync&accommodation_ids=all";
	}, 80 * 1000); 
		</script>';
	}
}
add_action('admin_notices', 'Calendar_Sync');

// ── Remove parent theme footer and WPML language switchers ───────────────────
add_filter( 'wpml_show_footer_language_selector', '__return_false' );

// ── i18n: custom Greek translation layer (must load before template_redirect) ──
require_once __DIR__ . '/inc/i18n.php';

// ── Header component ──────────────────────────────────────────────────────────
require_once __DIR__ . '/inc/header-defaults.php';
require_once __DIR__ . '/inc/header-acf.php';
require_once __DIR__ . '/inc/header-nav.php';
require_once __DIR__ . '/inc/header-admin-ui.php';

// ── Home page template helpers ────────────────────────────────────────────────
require_once __DIR__ . '/inc/home-data.php';
require_once __DIR__ . '/inc/home-admin-ui.php';

// ── Single accommodation template helpers ─────────────────────────────────────
require_once __DIR__ . '/inc/suite-data.php';

// ── Cookie consent banner ─────────────────────────────────────────────────────
require_once __DIR__ . '/inc/cookie-banner.php';

// ── Admin tools ───────────────────────────────────────────────────────────────
require_once __DIR__ . '/inc/tools-sync-galleries.php';
require_once __DIR__ . '/inc/tools-unused-images.php'; // recovery console — will be replaced with fixed scanner after recovery
require_once __DIR__ . '/inc/tools-aioseo-el.php';
require_once __DIR__ . '/inc/tools-sitemap-llms.php';

// ── JSON-LD structured data ───────────────────────────────────────────────────
require_once __DIR__ . '/inc/jsonld.php';

// ── Performance: targeted dequeues + hero LCP preload ────────────────────────
require_once __DIR__ . '/inc/perf-dequeue.php';

// ── Force single-mphb_room_type.php for all accommodation posts ──────────────
// Runs at priority 99 to override Elementor's template_include hook.
add_filter( 'template_include', function ( $template ) {
	if ( is_singular( 'mphb_room_type' ) ) {
		$custom = get_stylesheet_directory() . '/single-mphb_room_type.php';
		if ( file_exists( $custom ) ) {
			return $custom;
		}
	}
	return $template;
}, 99 );

// ── Fade-in boot guard: add `fade-in-ready` to <html> before any CSS loads ────
// Without this, no-JS users would see .fade-in elements stuck at opacity: 0.
add_action( 'wp_head', function () {
	echo "<script>document.documentElement.classList.add('fade-in-ready');</script>\n";
}, 1 );

// ── Hide plugin scroll-to-top button ─────────────────────────────────────────
add_action( 'wp_head', function () {
	echo "<style>#scrollUp{display:none!important}</style>\n";
} );

add_action( 'wp_enqueue_scripts', function () {
	$dir = get_stylesheet_directory_uri();
	$ver = wp_get_theme()->get( 'Version' );

	wp_enqueue_style( 'chic-manrope', "$dir/assets/fonts/manrope/manrope.css", [], $ver );
	wp_enqueue_style( 'chic-tokens',      "$dir/css/tokens.css",              [ 'chic-manrope' ], $ver );
	wp_enqueue_style( 'chic-buttons',           "$dir/css/buttons.css",                 [ 'chic-tokens' ],     $ver );
	wp_enqueue_style( 'chic-suite-card',        "$dir/css/suite-card.css",              [ 'chic-tokens' ],     $ver );
	wp_enqueue_style( 'chic-site-header',       "$dir/css/site-header.css",             [ 'chic-tokens' ],     $ver );
	wp_enqueue_style( 'chic-site-mega-nav',     "$dir/css/site-mega-nav.css",           [ 'chic-site-header' ], $ver );
	wp_enqueue_style( 'chic-mobile-disclosure', "$dir/css/mobile-disclosure.css",       [ 'chic-site-header' ], $ver );
	wp_enqueue_style( 'chic-header-dropdowns',  "$dir/css/site-header-dropdowns.css",   [ 'chic-site-header' ], $ver );
	wp_enqueue_style( 'chic-header-toolbar',    "$dir/css/site-header-toolbar.css",     [ 'chic-site-header' ], $ver );

	wp_enqueue_style( 'chic-site-footer', "$dir/css/site-footer.css", [ 'chic-tokens' ], $ver );

	wp_enqueue_style(  'chic-cookie-banner', "$dir/css/cookie-banner.css", [ 'chic-tokens', 'chic-buttons' ], $ver );
	wp_enqueue_script( 'chic-cookie-banner', "$dir/js/cookie-banner.js",   [],                                $ver, true );

	wp_enqueue_style(  'chic-scroll-fade-in', "$dir/css/scroll-fade-in.css", [], $ver );
	wp_enqueue_script( 'chic-scroll-fade-in', "$dir/js/scroll-fade-in.js",   [], $ver, true );
	wp_enqueue_script( 'chic-header-scroll',  "$dir/js/site-header-scroll.js", [], $ver, true );

	wp_enqueue_script( 'chic-mobile-nav', "$dir/js/mobile-nav.js", [], $ver, true );

	if ( is_page_template( 'page-home.php' ) ) {
		wp_enqueue_style(  'chic-swiper',          "$dir/assets/vendor/swiper/swiper-bundle.min.css", [],                                    $ver );
		wp_enqueue_script( 'chic-swiper',          "$dir/assets/vendor/swiper/swiper-bundle.min.js",  [],                                    $ver, true );
		wp_enqueue_style(  'chic-page-home',       "$dir/css/page-home.css",                          [ 'chic-tokens' ],                     $ver );
		wp_enqueue_style(  'chic-page-home-hero',  "$dir/css/page-home-hero.css",                     [ 'chic-tokens', 'chic-swiper' ],       $ver );
		wp_enqueue_script( 'chic-page-home-hero',   "$dir/js/page-home-hero.js",    [ 'chic-swiper', 'chic-scroll-fade-in' ], $ver, true );
		wp_enqueue_script( 'chic-page-home-suites', "$dir/js/page-home-suites.js", [ 'chic-swiper' ],                        $ver, true );
	}

	if ( is_page_template( 'page-explore-athens.php' ) ) {
		wp_enqueue_style(  'chic-page-home',           "$dir/css/page-home.css",           [ 'chic-tokens' ],                                          $ver );
		wp_enqueue_style(  'chic-page-home-hero',      "$dir/css/page-home-hero.css",      [ 'chic-tokens' ],                                          $ver );
		wp_enqueue_style(  'chic-page-explore-athens', "$dir/css/page-explore-athens.css", [ 'chic-tokens', 'chic-page-home', 'chic-page-home-hero' ], $ver );
		wp_enqueue_script( 'chic-page-explore-athens', "$dir/js/page-explore-athens.js",  [ 'chic-scroll-fade-in' ],                                  $ver, true );
	}

	if ( is_page_template( 'page-testimonials.php' ) ) {
		wp_enqueue_style(  'chic-page-home-hero',    "$dir/css/page-home-hero.css",    [ 'chic-tokens' ],                                          $ver );
		wp_enqueue_style(  'chic-page-testimonials', "$dir/css/page-testimonials.css", [ 'chic-tokens', 'chic-suite-card', 'chic-page-home-hero' ], $ver );
		wp_enqueue_script( 'chic-page-testimonials', "$dir/js/page-testimonials.js",   [ 'chic-scroll-fade-in' ],                                  $ver, true );
	}

	if ( is_page_template( [ 'page-privacy-policy.php', 'page-cookie-policy.php', 'page-terms-and-conditions.php' ] ) ) {
		wp_enqueue_style( 'chic-page-legal', "$dir/css/page-legal.css", [ 'chic-tokens' ], $ver );
	}

	if ( is_404() ) {
		wp_enqueue_style( 'chic-page-home-hero', "$dir/css/page-home-hero.css", [ 'chic-tokens' ], $ver );
		wp_enqueue_style( 'chic-page-404',       "$dir/css/page-404.css",       [ 'chic-tokens', 'chic-page-home-hero', 'chic-buttons' ], $ver );
	}

	if ( is_singular( 'mphb_room_type' ) ) {
		wp_enqueue_style(  'chic-fontawesome', "$dir/assets/vendor/fontawesome/css/fontawesome.min.css", [], '5.15.4' );
		wp_enqueue_style(  'chic-fa-solid',    "$dir/assets/vendor/fontawesome/css/solid.min.css", [ 'chic-fontawesome' ], '5.15.4' );
		wp_enqueue_style(  'chic-swiper',                 "$dir/assets/vendor/swiper/swiper-bundle.min.css", [],                                     $ver );
		wp_enqueue_script( 'chic-swiper',                 "$dir/assets/vendor/swiper/swiper-bundle.min.js",  [],                                     $ver, true );
		wp_enqueue_style(  'chic-page-home',              "$dir/css/page-home.css",                          [ 'chic-tokens' ],                      $ver );
		wp_enqueue_style(  'chic-page-home-hero',         "$dir/css/page-home-hero.css",                     [ 'chic-tokens' ],                      $ver );
		wp_enqueue_style(  'chic-single-accommodation',   "$dir/css/single-accommodation.css",               [ 'chic-tokens', 'chic-page-home-hero' ], $ver );
		wp_enqueue_script( 'chic-single-accommodation',   "$dir/js/single-accommodation.js",                [ 'chic-swiper', 'chic-scroll-fade-in' ], $ver, true );
	}
}, 20 );

// ES module: add type="module" to the script tag so it can use import statements.
add_filter( 'script_loader_tag', function ( $tag, $handle ) {
	if ( 'chic-mobile-nav' === $handle ) {
		return str_replace( '<script ', '<script type="module" ', $tag );
	}
	return $tag;
}, 10, 2 );

// Inject our custom header immediately after <body> via output buffering.
// This works even on parent themes that don't call wp_body_open().
add_action( 'template_redirect', 'chic_header_start_buffer' );
function chic_header_start_buffer() {
	if ( is_admin() ) return;
	// Pre-generate header HTML while all WP functions are available.
	ob_start();
	chic_output_custom_header();
	$GLOBALS['chic_header_html'] = ob_get_clean();

	// Buffer the full page output and splice our header in after <body ...>.
	ob_start( 'chic_header_inject_into_body' );
}

function chic_header_inject_into_body( string $buffer ): string {
	$html = $GLOBALS['chic_header_html'] ?? '';
	if ( '' === $html ) return $buffer;
	// Insert once, right after the opening <body ...> tag.
	return preg_replace( '/(<body[^>]*>)/i', '$1' . $html, $buffer, 1 );
}


function hide_booking_form() {


    echo '<script>
       if (document.readyState === "complete") {
    // Document already fully loaded
    hide_booking_form();
} else {
    // Add event listener for DOMContentLoaded (fires when document is fully loaded)
    document.addEventListener("DOMContentLoaded", hide_booking_form);
}

function hide_booking_form() {
    
if( window.location.href.includes("accommodation")){
console.log("HIDE BOOKINGS --> FUnctions.php Script Line 177");
   document.getElementsByClassName("elementor-section")[2].style.display="none";
document.getElementsByClassName("elementor-section")[3].style.display="none"
}

}</script>';

}
add_action( 'wp_head', 'hide_booking_form' );

