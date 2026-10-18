<?php
/**
 * Modalità manutenzione personalizzabile.
 *
 * - Interruttore ON/OFF + "Personalizza" sulla barra admin (sempre disponibile, anche sul sito).
 * - Pagina di personalizzazione: Aspetto → Manutenzione (vedi maintenance-admin.php).
 * - Ai visitatori mostra una pagina con stato HTTP 503 (corretta per i motori di ricerca).
 * - Chi può modificare gli articoli (autori e superiori) vede normalmente il sito.
 * - Anteprima per gli amministratori: aggiungi ?drkaos_maint_preview=1 a un indirizzo del sito.
 *
 * Disattivazione di emergenza: define( 'DRKAOS_DISABLE_MAINTENANCE', true ); in wp-config.php.
 *
 * @package DrKaosWPTheme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( defined( 'DRKAOS_DISABLE_MAINTENANCE' ) && DRKAOS_DISABLE_MAINTENANCE ) {
	return;
}

const DRKAOS_MAINT_OPTION = 'drkaos_maintenance';

/**
 * Valori predefiniti.
 *
 * @return array
 */
function drkaos_maint_defaults() {
	return array(
		'enabled'        => false,
		'bg_image'       => '',
		'overlay'        => 55,
		'logo'           => '',
		'logo_width'     => 280,
		'title'          => 'Torniamo presto',
		'title_font'     => 'montserrat',
		'title_size'     => 56,
		'title_weight'   => 700,
		'title_color'    => '#ffffff',
		'text'           => 'Stiamo lavorando a qualcosa di speciale. Torna a trovarci tra poco.',
		'text_font'      => 'inter',
		'text_size'      => 20,
		'text_weight'    => 400,
		'text_color'     => '#d6fff6',
		'bar_enabled'    => true,
		'bar_text'       => '© [anno] [sito] — Tutti i diritti riservati',
		'bar_font'       => 'inter',
		'bar_size'       => 13,
		'bar_color'      => '#ffffff',
		'bar_opacity'    => 30,
	);
}

/**
 * Font disponibili: 'stack' = font-family CSS, 'g' = famiglia Google Fonts ('' per i font di sistema).
 *
 * @return array
 */
function drkaos_maint_fonts() {
	return array(
		'system'           => array( 'label' => 'Sistema (predefinito)', 'stack' => 'system-ui, -apple-system, "Segoe UI", Roboto, Arial, sans-serif', 'g' => '' ),
		'arial'            => array( 'label' => 'Arial', 'stack' => 'Arial, Helvetica, sans-serif', 'g' => '' ),
		'georgia'          => array( 'label' => 'Georgia', 'stack' => 'Georgia, "Times New Roman", serif', 'g' => '' ),
		'mono'             => array( 'label' => 'Monospazio', 'stack' => '"Courier New", Courier, monospace', 'g' => '' ),
		'montserrat'       => array( 'label' => 'Montserrat', 'stack' => '"Montserrat", sans-serif', 'g' => 'Montserrat:wght@100..900' ),
		'inter'            => array( 'label' => 'Inter', 'stack' => '"Inter", sans-serif', 'g' => 'Inter:wght@100..900' ),
		'poppins'          => array( 'label' => 'Poppins', 'stack' => '"Poppins", sans-serif', 'g' => 'Poppins:wght@100;200;300;400;500;600;700;800;900' ),
		'raleway'          => array( 'label' => 'Raleway', 'stack' => '"Raleway", sans-serif', 'g' => 'Raleway:wght@100..900' ),
		'outfit'           => array( 'label' => 'Outfit', 'stack' => '"Outfit", sans-serif', 'g' => 'Outfit:wght@100..900' ),
		'sora'             => array( 'label' => 'Sora', 'stack' => '"Sora", sans-serif', 'g' => 'Sora:wght@100..800' ),
		'manrope'          => array( 'label' => 'Manrope', 'stack' => '"Manrope", sans-serif', 'g' => 'Manrope:wght@200..800' ),
		'space-grotesk'    => array( 'label' => 'Space Grotesk', 'stack' => '"Space Grotesk", sans-serif', 'g' => 'Space+Grotesk:wght@300..700' ),
		'oswald'           => array( 'label' => 'Oswald', 'stack' => '"Oswald", sans-serif', 'g' => 'Oswald:wght@200..700' ),
		'bebas-neue'       => array( 'label' => 'Bebas Neue', 'stack' => '"Bebas Neue", sans-serif', 'g' => 'Bebas+Neue' ),
		'playfair-display' => array( 'label' => 'Playfair Display', 'stack' => '"Playfair Display", serif', 'g' => 'Playfair+Display:wght@400..900' ),
		'lora'             => array( 'label' => 'Lora', 'stack' => '"Lora", serif', 'g' => 'Lora:wght@400..700' ),
		'dm-serif-display' => array( 'label' => 'DM Serif Display', 'stack' => '"DM Serif Display", serif', 'g' => 'DM+Serif+Display' ),
	);
}

/**
 * Impostazioni correnti (con i valori predefiniti dove mancano).
 *
 * @return array
 */
function drkaos_maint_get() {
	$saved = get_option( DRKAOS_MAINT_OPTION, array() );
	return wp_parse_args( is_array( $saved ) ? $saved : array(), drkaos_maint_defaults() );
}

function drkaos_maint_is_on() {
	$o = drkaos_maint_get();
	return ! empty( $o['enabled'] );
}

/**
 * Pulizia dei valori salvati dalla pagina di personalizzazione.
 *
 * @param mixed $in Valori inviati.
 * @return array
 */
function drkaos_maint_sanitize( $in ) {
	$d     = drkaos_maint_defaults();
	$in    = is_array( $in ) ? $in : array();
	$fonts = drkaos_maint_fonts();
	$out   = array();

	$out['enabled']     = ! empty( $in['enabled'] );
	$out['bar_enabled'] = ! empty( $in['bar_enabled'] );

	foreach ( array( 'bg_image', 'logo' ) as $k ) {
		$out[ $k ] = isset( $in[ $k ] ) ? esc_url_raw( $in[ $k ] ) : '';
	}

	$clamp = function ( $v, $min, $max, $def ) {
		return is_numeric( $v ) ? max( $min, min( $max, (int) $v ) ) : $def;
	};
	$out['overlay']     = $clamp( $in['overlay'] ?? null, 0, 100, $d['overlay'] );
	$out['logo_width']  = $clamp( $in['logo_width'] ?? null, 40, 900, $d['logo_width'] );
	$out['title_size']  = $clamp( $in['title_size'] ?? null, 12, 220, $d['title_size'] );
	$out['text_size']   = $clamp( $in['text_size'] ?? null, 10, 100, $d['text_size'] );
	$out['bar_size']    = $clamp( $in['bar_size'] ?? null, 9, 40, $d['bar_size'] );
	$out['bar_opacity'] = $clamp( $in['bar_opacity'] ?? null, 0, 100, $d['bar_opacity'] );

	foreach ( array( 'title_weight', 'text_weight' ) as $k ) {
		$w          = $clamp( $in[ $k ] ?? null, 100, 900, $d[ $k ] );
		$out[ $k ] = (int) ( round( $w / 100 ) * 100 );
	}

	foreach ( array( 'title_font', 'text_font', 'bar_font' ) as $k ) {
		$out[ $k ] = ( isset( $in[ $k ] ) && isset( $fonts[ $in[ $k ] ] ) ) ? $in[ $k ] : $d[ $k ];
	}

	foreach ( array( 'title_color', 'text_color', 'bar_color' ) as $k ) {
		$c         = isset( $in[ $k ] ) ? sanitize_hex_color( $in[ $k ] ) : '';
		$out[ $k ] = $c ? $c : $d[ $k ];
	}

	$out['title']    = isset( $in['title'] ) ? sanitize_text_field( $in['title'] ) : '';
	$out['text']     = isset( $in['text'] ) ? wp_kses_post( $in['text'] ) : '';
	$out['bar_text'] = isset( $in['bar_text'] ) ? wp_kses_post( $in['bar_text'] ) : '';

	return $out;
}

/**
 * Variabili CSS della pagina (usate anche dall'anteprima nell'editor).
 *
 * @param array $o Impostazioni.
 * @return string Contenuto dell'attributo style.
 */
function drkaos_maint_css_vars( $o ) {
	$fonts = drkaos_maint_fonts();
	$bg    = $o['bg_image'] ? $o['bg_image'] : DRKAOS_THEME_URL . '/assets/img/login-bg.jpg';

	$vars = array(
		'--dkm-bg'           => 'url("' . esc_url_raw( $bg ) . '")',
		'--dkm-overlay'      => ( (int) $o['overlay'] ) / 100,
		'--dkm-logo-n'       => (int) $o['logo_width'],
		'--dkm-title-font'   => $fonts[ $o['title_font'] ]['stack'],
		'--dkm-title-n'      => (int) $o['title_size'],
		'--dkm-title-weight' => (int) $o['title_weight'],
		'--dkm-title-color'  => $o['title_color'],
		'--dkm-text-font'    => $fonts[ $o['text_font'] ]['stack'],
		'--dkm-text-n'       => (int) $o['text_size'],
		'--dkm-text-weight'  => (int) $o['text_weight'],
		'--dkm-text-color'   => $o['text_color'],
		'--dkm-bar-font'     => $fonts[ $o['bar_font'] ]['stack'],
		'--dkm-bar-n'        => (int) $o['bar_size'],
		'--dkm-bar-color'    => $o['bar_color'],
		'--dkm-bar-op'       => ( (int) $o['bar_opacity'] ) / 100,
	);

	$css = '';
	foreach ( $vars as $name => $value ) {
		$css .= $name . ':' . str_replace( array( ';', '<', '>' ), '', (string) $value ) . ';';
	}
	return $css;
}

/**
 * Sostituisce i segnaposto [anno] e [sito] nei testi.
 *
 * @param string $text Testo.
 * @return string
 */
function drkaos_maint_tokens( $text ) {
	return str_replace( array( '[anno]', '[sito]' ), array( gmdate( 'Y' ), get_bloginfo( 'name' ) ), $text );
}

/**
 * HTML della pagina di manutenzione (usato dal sito e dall'anteprima).
 *
 * @param array $o       Impostazioni.
 * @param bool  $preview true nell'anteprima dentro l'editor.
 * @return string
 */
function drkaos_maint_markup( $o, $preview = false ) {
	$logo = $o['logo'] ? $o['logo'] : DRKAOS_THEME_URL . '/assets/img/logo-white.png';

	ob_start();
	?>
	<div class="dkm<?php echo $preview ? ' dkm--preview' : ''; ?>" style="<?php echo esc_attr( drkaos_maint_css_vars( $o ) ); ?>">
		<div class="dkm__bg"></div>
		<div class="dkm__overlay"></div>
		<main class="dkm__content">
			<img class="dkm__logo" src="<?php echo esc_url( $logo ); ?>" alt="<?php echo esc_attr( get_bloginfo( 'name' ) ); ?>">
			<h1 class="dkm__title"><?php echo esc_html( $o['title'] ); ?></h1>
			<div class="dkm__text"><?php echo wp_kses_post( wpautop( drkaos_maint_tokens( $o['text'] ) ) ); ?></div>
		</main>
		<?php if ( $o['bar_enabled'] || $preview ) : ?>
			<footer class="dkm__bar"<?php echo $o['bar_enabled'] ? '' : ' hidden'; ?>><?php echo wp_kses_post( drkaos_maint_tokens( $o['bar_text'] ) ); ?></footer>
		<?php endif; ?>
	</div>
	<?php
	return ob_get_clean();
}

/**
 * URL dei Google Fonts necessari.
 *
 * @param array $o Impostazioni.
 * @return string Vuoto se si usano solo font di sistema.
 */
function drkaos_maint_fonts_url( $o ) {
	$fonts = drkaos_maint_fonts();
	$fams  = array();
	foreach ( array_unique( array( $o['title_font'], $o['text_font'], $o['bar_font'] ) ) as $key ) {
		if ( ! empty( $fonts[ $key ]['g'] ) ) {
			$fams[] = 'family=' . $fonts[ $key ]['g'];
		}
	}
	return $fams ? 'https://fonts.googleapis.com/css2?' . implode( '&', $fams ) . '&display=swap' : '';
}

/**
 * I visitatori vanno bloccati?
 *
 * @return bool
 */
function drkaos_maint_should_block() {
	if ( ! drkaos_maint_is_on() ) {
		return false;
	}
	// Anteprima forzata per gli amministratori.
	if ( isset( $_GET['drkaos_maint_preview'] ) && current_user_can( 'manage_options' ) ) { // phpcs:ignore WordPress.Security.NonceVerification
		return true;
	}
	if ( wp_doing_ajax() || wp_doing_cron() || ( defined( 'REST_REQUEST' ) && REST_REQUEST ) || ( defined( 'WP_CLI' ) && WP_CLI ) || is_customize_preview() ) {
		return false;
	}
	if ( is_user_logged_in() && current_user_can( 'edit_posts' ) ) {
		return false;
	}
	return (bool) apply_filters( 'drkaos_maintenance_block', true );
}

/**
 * Mostra la pagina di manutenzione ai visitatori.
 */
function drkaos_maint_maybe_render() {
	if ( ! drkaos_maint_should_block() ) {
		return;
	}

	$o = drkaos_maint_get();

	if ( ! defined( 'DONOTCACHEPAGE' ) ) {
		define( 'DONOTCACHEPAGE', true );
	}
	status_header( 503 );
	nocache_headers();
	header( 'Retry-After: 3600' );
	header( 'Content-Type: text/html; charset=' . get_option( 'blog_charset' ) );

	$fonts_url = drkaos_maint_fonts_url( $o );
	$css_url   = DRKAOS_THEME_URL . '/assets/css/maintenance.css?ver=' . drkaos_asset_ver( '/assets/css/maintenance.css' );
	?>
<!doctype html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<meta name="robots" content="noindex, nofollow">
	<title><?php echo esc_html( get_bloginfo( 'name' ) ); ?> — <?php echo esc_html( $o['title'] ? $o['title'] : __( 'Manutenzione', 'drkaos-wp-theme' ) ); ?></title>
	<?php if ( $fonts_url ) : ?>
		<link rel="preconnect" href="https://fonts.googleapis.com">
		<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
		<link rel="stylesheet" href="<?php echo esc_url( $fonts_url ); ?>">
	<?php endif; ?>
	<link rel="stylesheet" href="<?php echo esc_url( $css_url ); ?>">
	<?php if ( has_site_icon() ) : ?>
		<link rel="icon" href="<?php echo esc_url( get_site_icon_url( 64 ) ); ?>">
	<?php else : ?>
		<link rel="icon" href="<?php echo esc_url( DRKAOS_THEME_URL . '/assets/img/favicon.png' ); ?>" type="image/png">
	<?php endif; ?>
</head>
<body class="dkm-page">
	<?php echo drkaos_maint_markup( $o ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
</body>
</html>
	<?php
	exit;
}
add_action( 'template_redirect', 'drkaos_maint_maybe_render', 0 );

/* ---------------------------------------------------------------------------
 * Barra admin: interruttore ON/OFF + Personalizza
 * ------------------------------------------------------------------------- */

/**
 * Indirizzo della pagina di personalizzazione.
 *
 * @return string
 */
function drkaos_maint_settings_url() {
	return admin_url( 'themes.php?page=drkaos-maintenance' );
}

/**
 * Nodi della barra admin.
 *
 * @param WP_Admin_Bar $bar Barra admin.
 */
function drkaos_maint_admin_bar( $bar ) {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	$on    = drkaos_maint_is_on();
	$title = '<span class="dkm-ab"><span class="dkm-ab__label">' . esc_html__( 'Manutenzione', 'drkaos-wp-theme' ) . '</span>'
		. '<span class="dkm-ab__state">' . ( $on ? 'ON' : 'OFF' ) . '</span>'
		. '<span class="dkm-ab__switch" role="switch" aria-checked="' . ( $on ? 'true' : 'false' ) . '"><i></i></span></span>';

	$bar->add_node(
		array(
			'id'     => 'drkaos-maint',
			'parent' => false,
			'title'  => $title,
			'href'   => wp_nonce_url( admin_url( 'admin-post.php?action=drkaos_maint_toggle' ), 'drkaos_maint_toggle' ),
			'meta'   => array(
				'class' => 'drkaos-maint ' . ( $on ? 'is-on' : 'is-off' ),
				'title' => __( 'Attiva o disattiva la modalità manutenzione', 'drkaos-wp-theme' ),
			),
		)
	);
	$bar->add_node(
		array(
			'id'     => 'drkaos-maint-customize',
			'parent' => 'drkaos-maint',
			'title'  => esc_html__( 'Personalizza', 'drkaos-wp-theme' ),
			'href'   => drkaos_maint_settings_url(),
		)
	);
	$bar->add_node(
		array(
			'id'     => 'drkaos-maint-preview',
			'parent' => 'drkaos-maint',
			'title'  => esc_html__( 'Anteprima della pagina', 'drkaos-wp-theme' ),
			'href'   => add_query_arg( 'drkaos_maint_preview', '1', home_url( '/' ) ),
			'meta'   => array( 'target' => '_blank', 'rel' => 'noopener' ),
		)
	);
}
add_action( 'admin_bar_menu', 'drkaos_maint_admin_bar', 90 );

/**
 * Cambia lo stato ON/OFF.
 *
 * @return bool Nuovo stato.
 */
function drkaos_maint_toggle() {
	$o            = drkaos_maint_get();
	$o['enabled'] = empty( $o['enabled'] );
	update_option( DRKAOS_MAINT_OPTION, $o );
	return $o['enabled'];
}

/** Senza JavaScript: link con nonce, poi torna alla pagina precedente. */
function drkaos_maint_toggle_link() {
	check_admin_referer( 'drkaos_maint_toggle' );
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( esc_html__( 'Non hai i permessi per farlo.', 'drkaos-wp-theme' ), 403 );
	}
	drkaos_maint_toggle();
	wp_safe_redirect( wp_get_referer() ? wp_get_referer() : admin_url() );
	exit;
}
add_action( 'admin_post_drkaos_maint_toggle', 'drkaos_maint_toggle_link' );

/** Con JavaScript: cambia al volo, senza ricaricare la pagina. */
function drkaos_maint_toggle_ajax() {
	check_ajax_referer( 'drkaos_maint_toggle', 'nonce' );
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_send_json_error( null, 403 );
	}
	wp_send_json_success( array( 'on' => drkaos_maint_toggle() ) );
}
add_action( 'wp_ajax_drkaos_maint_toggle', 'drkaos_maint_toggle_ajax' );

/**
 * Stile e script dell'interruttore (sito e dashboard).
 */
function drkaos_maint_bar_assets() {
	if ( ! is_admin_bar_showing() || ! current_user_can( 'manage_options' ) ) {
		return;
	}
	wp_enqueue_style( 'drkaos-maint-bar', DRKAOS_THEME_URL . '/assets/css/maintenance-bar.css', array( 'admin-bar' ), drkaos_asset_ver( '/assets/css/maintenance-bar.css' ) );
	wp_enqueue_script( 'drkaos-maint-bar', DRKAOS_THEME_URL . '/assets/js/maintenance-bar.js', array(), drkaos_asset_ver( '/assets/js/maintenance-bar.js' ), true );
	wp_add_inline_script(
		'drkaos-maint-bar',
		'window.drkaosMaintBar = ' . drkaos_json(
			array(
				'ajax'  => admin_url( 'admin-ajax.php' ),
				'nonce' => wp_create_nonce( 'drkaos_maint_toggle' ),
			)
		) . ';',
		'before'
	);
}
add_action( 'wp_enqueue_scripts', 'drkaos_maint_bar_assets' );
add_action( 'admin_enqueue_scripts', 'drkaos_maint_bar_assets' );

require_once DRKAOS_THEME_DIR . '/inc/maintenance-admin.php';
