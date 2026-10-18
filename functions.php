<?php
/**
 * DR.KAOS WP THEME - functions
 *
 * @package DrKaosWPTheme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'DRKAOS_THEME_VERSION', '2.0.1' );
define( 'DRKAOS_THEME_DIR', get_template_directory() );
define( 'DRKAOS_THEME_URL', get_template_directory_uri() );

/**
 * Versione di un asset per il cache-busting. In produzione usa la versione del tema
 * (nessun accesso al disco); con WP_DEBUG attivo usa la data di modifica del file.
 *
 * @param string $relative Percorso relativo alla cartella del tema (es. /assets/css/admin.css).
 * @return string
 */
function drkaos_asset_ver( $relative ) {
	if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
		$file = DRKAOS_THEME_DIR . $relative;
		if ( file_exists( $file ) ) {
			return DRKAOS_THEME_VERSION . '.' . filemtime( $file );
		}
	}
	return DRKAOS_THEME_VERSION;
}

/**
 * Codifica JSON sicura per le configurazioni stampate dentro a un tag <script>.
 *
 * @param mixed $data Dati.
 * @return string
 */
function drkaos_json( $data ) {
	return wp_json_encode( $data, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT );
}

require_once DRKAOS_THEME_DIR . '/inc/branding.php';
require_once DRKAOS_THEME_DIR . '/inc/layout.php';
require_once DRKAOS_THEME_DIR . '/inc/admin.php';
require_once DRKAOS_THEME_DIR . '/inc/login-math.php';
require_once DRKAOS_THEME_DIR . '/inc/promo-kaosslider.php';
require_once DRKAOS_THEME_DIR . '/inc/cursor.php';
require_once DRKAOS_THEME_DIR . '/inc/maintenance.php';
require_once DRKAOS_THEME_DIR . '/inc/login-url.php';

/**
 * Setup del tema.
 */
function drkaos_setup() {
	load_theme_textdomain( 'drkaos-wp-theme', DRKAOS_THEME_DIR . '/languages' );

	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'automatic-feed-links' );
	add_theme_support( 'responsive-embeds' );
	add_theme_support( 'align-wide' );
	add_theme_support( 'customize-selective-refresh-widgets' );
	add_theme_support( 'editor-styles' );
	add_theme_support( 'html5', array( 'search-form', 'comment-form', 'comment-list', 'gallery', 'caption', 'script', 'style' ) );
	add_theme_support(
		'custom-logo',
		array(
			'height'      => 100,
			'width'       => 350,
			'flex-height' => true,
			'flex-width'  => true,
		)
	);

	register_nav_menus(
		array(
			'menu-1' => esc_html__( 'Header', 'drkaos-wp-theme' ),
			'menu-2' => esc_html__( 'Footer', 'drkaos-wp-theme' ),
		)
	);

	// WooCommerce.
	add_theme_support( 'woocommerce' );
	add_theme_support( 'wc-product-gallery-zoom' );
	add_theme_support( 'wc-product-gallery-lightbox' );
	add_theme_support( 'wc-product-gallery-slider' );

}
add_action( 'after_setup_theme', 'drkaos_setup' );

/**
 * Larghezza contenuto per embed/oembed.
 */
function drkaos_content_width() {
	$GLOBALS['content_width'] = apply_filters( 'drkaos_content_width', 1140 );
}
add_action( 'after_setup_theme', 'drkaos_content_width', 0 );

/**
 * Stili e script (minimi): un solo foglio di stile.
 */
function drkaos_enqueue_assets() {
	wp_enqueue_style( 'drkaos-theme', DRKAOS_THEME_URL . '/assets/css/theme.css', array(), drkaos_asset_ver( '/assets/css/theme.css' ) );

	if ( is_singular() && comments_open() && get_option( 'thread_comments' ) ) {
		wp_enqueue_script( 'comment-reply' );
	}
}
add_action( 'wp_enqueue_scripts', 'drkaos_enqueue_assets' );

/**
 * Link "Salta al contenuto" (accessibilità).
 */
function drkaos_skip_link() {
	echo '<a class="skip-link screen-reader-text" href="#content">' . esc_html__( 'Salta al contenuto', 'drkaos-wp-theme' ) . '</a>';
}
add_action( 'wp_body_open', 'drkaos_skip_link' );

/**
 * Header/footer di default, usati solo se Elementor Pro non ha un template per la location.
 */
function drkaos_render_header() {
	// Header del tema disattivato: nessun output, lo gestisce un plugin esterno (via wp_body_open o shortcode).
	if ( ! drkaos_part_enabled( 'header' ) ) {
		do_action( 'drkaos_header_disabled' );
		return;
	}
	if ( function_exists( 'elementor_theme_do_location' ) && elementor_theme_do_location( 'header' ) ) {
		return;
	}
	get_template_part( 'template-parts/header' );
}

function drkaos_render_footer() {
	if ( ! drkaos_part_enabled( 'footer' ) ) {
		do_action( 'drkaos_footer_disabled' );
		return;
	}
	if ( function_exists( 'elementor_theme_do_location' ) && elementor_theme_do_location( 'footer' ) ) {
		return;
	}
	get_template_part( 'template-parts/footer' );
}

/**
 * Rende un template-part di contenuto, rispettando le Theme Locations di Elementor Pro.
 *
 * @param string $location single|archive o vuoto.
 * @param string $part     Nome del template-part.
 */
function drkaos_render_content( $location, $part ) {
	if ( $location && function_exists( 'elementor_theme_do_location' ) && elementor_theme_do_location( $location ) ) {
		return;
	}
	get_template_part( 'template-parts/' . $part );
}

/**
 * Registra le Theme Locations di Elementor Pro.
 *
 * @param object $elementor_theme_manager Manager delle locations.
 */
function drkaos_register_elementor_locations( $elementor_theme_manager ) {
	$elementor_theme_manager->register_all_core_location();
}
add_action( 'elementor/theme/register_locations', 'drkaos_register_elementor_locations' );

/**
 * Aggiunge la classe del tema al body.
 *
 * @param array $classes Classi body.
 * @return array
 */
function drkaos_body_class( $classes ) {
	$classes[] = 'drkaos-wp-theme';
	return $classes;
}
add_filter( 'body_class', 'drkaos_body_class' );

/**
 * Nasconde il titolo di pagina del tema quando la pagina è costruita con Elementor
 * (il titolo, se serve, si gestisce direttamente in Elementor).
 *
 * @param bool $show Mostra il titolo.
 * @return bool
 */
function drkaos_show_page_title( $show ) {
	if ( is_page() && class_exists( '\Elementor\Plugin' ) ) {
		$doc = \Elementor\Plugin::$instance->documents->get( get_the_ID() );
		if ( $doc && $doc->is_built_with_elementor() ) {
			return false;
		}
	}
	return $show;
}
add_filter( 'drkaos_page_title', 'drkaos_show_page_title' );
