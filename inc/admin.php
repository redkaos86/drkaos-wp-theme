<?php
/**
 * Stile della dashboard di WordPress (wp-admin) e della barra admin.
 *
 * @package DrKaosWPTheme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * La dashboard brandizzata è attiva?
 * Per tornare alla dashboard standard: add_filter( 'drkaos_admin_style', '__return_false' );
 * oppure define( 'DRKAOS_DISABLE_ADMIN_STYLE', true ); in wp-config.php.
 *
 * @return bool
 */
function drkaos_admin_style_enabled() {
	if ( defined( 'DRKAOS_DISABLE_ADMIN_STYLE' ) && DRKAOS_DISABLE_ADMIN_STYLE ) {
		return false;
	}
	// L'editor di Elementor ha la sua interfaccia: non ci applichiamo lo stile della dashboard.
	if ( ( isset( $_GET['action'] ) && 'elementor' === $_GET['action'] ) || isset( $_GET['elementor-preview'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
		return false;
	}
	return (bool) apply_filters( 'drkaos_admin_style', true );
}

/**
 * Variabili CSS colore accento, condivise da admin e barra admin.
 */
function drkaos_admin_color_vars() {
	$accent = sanitize_hex_color( get_theme_mod( 'drkaos_accent', '#00e0b5' ) );
	if ( ! $accent ) {
		$accent = '#00e0b5';
	}
	$hex = ltrim( $accent, '#' );
	if ( 3 === strlen( $hex ) ) {
		$hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
	}
	$r = hexdec( substr( $hex, 0, 2 ) );
	$g = hexdec( substr( $hex, 2, 2 ) );
	$b = hexdec( substr( $hex, 4, 2 ) );
	return ':root{--dk-accent:' . $accent . ';--dk-accent-soft:rgba(' . $r . ',' . $g . ',' . $b . ',.14);}';
}

/**
 * CSS nella dashboard.
 */
function drkaos_admin_enqueue() {
	if ( ! drkaos_admin_style_enabled() ) {
		return;
	}
	wp_enqueue_style( 'drkaos-admin', DRKAOS_THEME_URL . '/assets/css/admin.css', array(), drkaos_asset_ver( '/assets/css/admin.css' ) );
	wp_enqueue_style( 'drkaos-adminbar', DRKAOS_THEME_URL . '/assets/css/adminbar.css', array( 'drkaos-admin' ), drkaos_asset_ver( '/assets/css/adminbar.css' ) );
	// Controllo automatico dei contrasti: vale per tutte le pagine, anche dei plugin.
	if ( apply_filters( 'drkaos_admin_contrast_fix', true ) ) {
		wp_enqueue_script( 'drkaos-admin-contrast', DRKAOS_THEME_URL . '/assets/js/admin-contrast.js', array(), drkaos_asset_ver( '/assets/js/admin-contrast.js' ), true );
	}

	$logo = esc_url( DRKAOS_THEME_URL . '/assets/img/logo-white.png' );
	$icon = esc_url( DRKAOS_THEME_URL . '/assets/img/icon-white.png' );
	$css  = drkaos_admin_color_vars();
	$css .= "#adminmenu li.drkaos-logo{margin:0;}#adminmenu li.drkaos-logo a{display:block;height:84px;margin:14px 12px 10px;border-radius:12px;background:url('{$logo}') center / contain no-repeat;transition:opacity .2s;}#adminmenu li.drkaos-logo a:hover{opacity:.8;background-color:transparent;}";
	$css .= "body.folded #adminmenu li.drkaos-logo a{height:34px;margin:14px 6px 10px;background-image:url('{$icon}');}";
	$css .= '@media (max-width:960px){body.auto-fold #adminmenu li.drkaos-logo a{height:34px;margin:14px 6px 10px;background-image:url(\'' . $icon . '\');}}';
	$css .= '@media (max-width:782px){#adminmenu li.drkaos-logo{display:none;}}';
	wp_add_inline_style( 'drkaos-admin', $css );
}
add_action( 'admin_enqueue_scripts', 'drkaos_admin_enqueue', 99 );

/**
 * Inserisce in cima al menu il logo cliccabile verso drkaos.it.
 */
function drkaos_admin_menu_logo() {
	if ( ! drkaos_admin_style_enabled() ) {
		return;
	}
	$url   = esc_url( apply_filters( 'drkaos_logo_url', 'https://drkaos.it' ) );
	$label = esc_attr__( 'DR.KAOS - vai al sito', 'drkaos-wp-theme' );
	?>
	<script>
		document.addEventListener('DOMContentLoaded', function () {
			var menu = document.getElementById('adminmenu');
			if (!menu || menu.querySelector('.drkaos-logo')) { return; }
			var li = document.createElement('li');
			li.className = 'drkaos-logo';
			li.innerHTML = '<a href="<?php echo esc_js( $url ); ?>" target="_blank" rel="noopener" aria-label="<?php echo esc_js( $label ); ?>" title="<?php echo esc_js( $label ); ?>"></a>';
			menu.insertBefore(li, menu.firstChild);
		});
	</script>
	<?php
}
add_action( 'admin_footer', 'drkaos_admin_menu_logo' );

/**
 * CSS della barra admin sul sito (solo per chi è loggato).
 */
function drkaos_adminbar_front_enqueue() {
	if ( ! drkaos_admin_style_enabled() || ! is_admin_bar_showing() ) {
		return;
	}
	wp_enqueue_style( 'drkaos-adminbar', DRKAOS_THEME_URL . '/assets/css/adminbar.css', array( 'admin-bar' ), drkaos_asset_ver( '/assets/css/adminbar.css' ) );
}
add_action( 'wp_enqueue_scripts', 'drkaos_adminbar_front_enqueue', 99 );
