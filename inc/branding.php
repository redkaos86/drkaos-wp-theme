<?php
/**
 * Branding DR.KAOS: Customizer (colori), login, favicon di default, admin.
 *
 * @package DrKaosWPTheme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Colori di default del brand.
 */
function drkaos_default_colors() {
	return array(
		'drkaos_accent' => '#00e0b5',
		'drkaos_dark'   => '#02121a',
		'drkaos_text'   => '#1d2b33',
	);
}

/**
 * Customizer: sezione "Brand DR.KAOS".
 *
 * @param WP_Customize_Manager $wp_customize Customizer.
 */
function drkaos_customize_register( $wp_customize ) {
	$wp_customize->add_section(
		'drkaos_brand',
		array(
			'title'    => esc_html__( 'Brand DR.KAOS', 'drkaos-wp-theme' ),
			'priority' => 30,
		)
	);

	$labels = array(
		'drkaos_accent' => esc_html__( 'Colore accento (link, pulsanti)', 'drkaos-wp-theme' ),
		'drkaos_dark'   => esc_html__( 'Colore scuro (header/footer)', 'drkaos-wp-theme' ),
		'drkaos_text'   => esc_html__( 'Colore testo', 'drkaos-wp-theme' ),
	);

	foreach ( drkaos_default_colors() as $id => $default ) {
		$wp_customize->add_setting(
			$id,
			array(
				'default'           => $default,
				'sanitize_callback' => 'sanitize_hex_color',
				'transport'         => 'refresh',
			)
		);
		$wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, $id, array(
			'label'   => $labels[ $id ],
			'section' => 'drkaos_brand',
		) ) );
	}

	$wp_customize->add_setting(
		'drkaos_footer_credit',
		array(
			'default'           => '',
			'sanitize_callback' => 'sanitize_text_field',
		)
	);
	$wp_customize->add_control(
		'drkaos_footer_credit',
		array(
			'label'       => esc_html__( 'Testo footer (vuoto = © anno + nome sito)', 'drkaos-wp-theme' ),
			'section'     => 'drkaos_brand',
			'type'        => 'text',
		)
	);

	$wp_customize->add_setting(
		'drkaos_favicon',
		array(
			'default'           => '',
			'sanitize_callback' => 'esc_url_raw',
		)
	);
	$wp_customize->add_control(
		new WP_Customize_Image_Control(
			$wp_customize,
			'drkaos_favicon',
			array(
				'label'       => esc_html__( 'Favicon del sito', 'drkaos-wp-theme' ),
				'description' => esc_html__( 'Immagine quadrata (PNG, SVG o ICO), meglio 512×512. Ha la precedenza sull’icona del sito di WordPress, senza passaggio di ritaglio. Se la lasci vuota si usa l’icona del sito di WordPress, oppure quella del tema.', 'drkaos-wp-theme' ),
				'section'     => 'drkaos_brand',
			)
		)
	);
}
add_action( 'customize_register', 'drkaos_customize_register' );

/**
 * Variabili CSS dei colori. Usabili in Elementor come var(--drkaos-accent).
 */
function drkaos_print_color_vars() {
	$css = ':root{';
	foreach ( drkaos_default_colors() as $id => $default ) {
		$css .= '--' . str_replace( '_', '-', $id ) . ':' . esc_html( get_theme_mod( $id, $default ) ) . ';';
	}
	$css .= '}';
	wp_add_inline_style( 'drkaos-theme', $css );
}
add_action( 'wp_enqueue_scripts', 'drkaos_print_color_vars', 20 );

/**
 * Indirizzo della favicon in uso. Priorità: favicon scelta nel Customizer del tema,
 * icona del sito di WordPress, icona di default del tema.
 *
 * @return string
 */
function drkaos_favicon_url() {
	$custom = get_theme_mod( 'drkaos_favicon', '' );
	if ( $custom ) {
		return $custom;
	}
	if ( has_site_icon() ) {
		return get_site_icon_url( 64 );
	}
	return DRKAOS_THEME_URL . '/assets/img/favicon.png';
}

/**
 * Stampa la favicon. Se non c'è né quella del tema né l'icona del sito di WordPress,
 * usa quella di default (l'icona del sito di WordPress la stampa WordPress stesso).
 */
function drkaos_print_favicon() {
	$custom = get_theme_mod( 'drkaos_favicon', '' );

	if ( ! $custom && has_site_icon() ) {
		return;
	}

	$url  = drkaos_favicon_url();
	$type = wp_check_filetype( wp_parse_url( $url, PHP_URL_PATH ) );
	echo '<link rel="icon" href="' . esc_url( $url ) . '"' . ( ! empty( $type['type'] ) ? ' type="' . esc_attr( $type['type'] ) . '"' : '' ) . '>' . "\n";
}
add_action( 'wp_head', 'drkaos_print_favicon', 5 );
add_action( 'login_head', 'drkaos_print_favicon', 5 );
add_action( 'admin_head', 'drkaos_print_favicon', 5 );

/**
 * Con una favicon scelta nel tema, WordPress non deve stampare anche la sua (evita due icone in conflitto).
 */
function drkaos_favicon_override() {
	if ( get_theme_mod( 'drkaos_favicon', '' ) ) {
		foreach ( array( 'wp_head', 'login_head', 'admin_head' ) as $hook ) {
			remove_action( $hook, 'wp_site_icon', 99 );
		}
	}
}
add_action( 'init', 'drkaos_favicon_override' );

/**
 * Pagina di login brandizzata.
 */
function drkaos_login_styles() {
	$accent = esc_html( get_theme_mod( 'drkaos_accent', '#00e0b5' ) );
	$logo   = esc_url( DRKAOS_THEME_URL . '/assets/img/logo-white.png' );
	$bg     = esc_url( DRKAOS_THEME_URL . '/assets/img/login-bg.jpg' );
	?>
	<style>
		body.login { background: linear-gradient(rgba(2,18,26,.82), rgba(2,18,26,.88)), #02121a url('<?php echo $bg; // phpcs:ignore WordPress.Security.EscapeOutput ?>') center / cover no-repeat fixed !important; }
		body.login #login h1 a {
			background: url('<?php echo $logo; // phpcs:ignore WordPress.Security.EscapeOutput ?>') center / contain no-repeat;
			width: 220px; height: 120px; margin-bottom: 20px;
		}
		body.login #loginform, body.login #lostpasswordform, body.login #registerform { background: #fff !important; border: 1px solid rgba(0, 224, 181, .6); border-radius: 16px; box-shadow: 0 16px 50px rgba(0,0,0,.55); padding: 26px 24px; }
		body.login #loginform label, body.login #lostpasswordform label, body.login #registerform label,
		body.login .forgetmenot label, body.login .user-pass-wrap label { color: #1d2b33 !important; font-weight: 600; }
		body.login .forgetmenot label { font-weight: 500; }
		body.login #nav a, body.login #backtoblog a, body.login .privacy-policy-page-link a, body.login .language-switcher label { color: #fff !important; text-shadow: 0 1px 6px rgba(0,0,0,.6); }
		body.login #nav a:hover, body.login #backtoblog a:hover { color: <?php echo $accent; // phpcs:ignore WordPress.Security.EscapeOutput ?> !important; }
		body.login .language-switcher .button { color: #fff; border-color: <?php echo $accent; // phpcs:ignore WordPress.Security.EscapeOutput ?>; background: transparent; }
		body.login .language-switcher select { color: #1d2b33; }
		.login form .input, .login input[type=text], .login input[type=password], .login input[type=email] { background: #f4f7f8; color: #1d2b33; border-color: rgba(0,224,181,.5); }
		.login input::placeholder { color: #5b6b73; }
		.login input:-webkit-autofill { -webkit-text-fill-color: #1d2b33; box-shadow: 0 0 0 1000px #f4f7f8 inset; }
		.login .button.wp-hide-pw { color: #1d2b33; }
		.login .button.wp-hide-pw:hover { color: #02121a; }
		.login input:focus { border-color: <?php echo $accent; // phpcs:ignore WordPress.Security.EscapeOutput ?> !important; box-shadow: 0 0 0 1px <?php echo $accent; // phpcs:ignore WordPress.Security.EscapeOutput ?> !important; }
		body.login .wp-core-ui .button-primary, body.login #wp-submit { background: <?php echo $accent; // phpcs:ignore WordPress.Security.EscapeOutput ?> !important; border-color: <?php echo $accent; // phpcs:ignore WordPress.Security.EscapeOutput ?> !important; color: #02121a !important; font-weight: 600; text-shadow: none; box-shadow: none; border-radius: 999px; }
		body.login .wp-core-ui .button-primary:hover, body.login #wp-submit:hover { filter: brightness(1.1); }
		body.login input[type=checkbox] { background: #fff; border-color: #5b6b73; }
		body.login input[type=checkbox]:checked { background: <?php echo $accent; // phpcs:ignore WordPress.Security.EscapeOutput ?>; border-color: <?php echo $accent; // phpcs:ignore WordPress.Security.EscapeOutput ?>; }
		.login .message, .login .success, .login #login_error { background: #fff; color: #1d2b33; border-left-color: <?php echo $accent; // phpcs:ignore WordPress.Security.EscapeOutput ?>; }
	</style>
	<?php
}
add_action( 'login_enqueue_scripts', 'drkaos_login_styles' );

add_filter( 'login_headerurl', function () {
	return home_url( '/' );
} );
add_filter( 'login_headertext', function () {
	return get_bloginfo( 'name' );
} );

/**
 * Firma nel footer del pannello admin.
 */
add_filter(
	'admin_footer_text',
	function () {
		return 'Sito realizzato con <strong>DR.KAOS WP THEME</strong>';
	}
);
