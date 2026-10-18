<?php
/**
 * Indirizzo di accesso personalizzato: /dklogin al posto di wp-login.php, /wp-admin, /admin, /login.
 *
 * - Il login di WordPress risponde solo all'indirizzo scelto (predefinito: /dklogin).
 * - /wp-login.php e /wp-admin (per chi non è collegato) rispondono con un errore 404.
 * - Tutti i link generati da WordPress (logout, password dimenticata, e-mail di reset...) puntano al nuovo indirizzo.
 * - Funziona solo con i permalink "belli" (qualsiasi impostazione diversa da "Semplice"), altrimenti resta tutto com'è.
 *
 * Sicurezze:
 * - define( 'DRKAOS_DISABLE_LOGIN_URL', true );  in wp-config.php → torna tutto normale.
 * - define( 'DRKAOS_LOGIN_SLUG', 'miologin' );   in wp-config.php → cambia l'indirizzo da codice.
 * - Cambiando tema, il login torna automaticamente su wp-login.php.
 *
 * @package DrKaosWPTheme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( defined( 'DRKAOS_DISABLE_LOGIN_URL' ) && DRKAOS_DISABLE_LOGIN_URL ) {
	return;
}

/**
 * Indirizzi che non si possono usare come slug.
 *
 * @return array
 */
function drkaos_login_reserved() {
	return array( 'wp-admin', 'wp-login', 'wp-login.php', 'admin', 'login', 'dashboard', 'wp-content', 'wp-includes', 'wp-json', 'feed', 'xmlrpc.php', 'index.php', 'robots.txt', 'sitemap.xml', 'wp-cron.php', 'wp-signup', 'wp-register', 'wp-activate', 'wp-comments-post.php', 'wp-trackback.php', 'wp-mail.php', 'wp-links-opml.php', 'wp-blog-header.php', 'wp-config.php', 'wp-load.php', 'wp-settings.php', 'readme.html', 'license.txt' );
}

/**
 * Slug dell'indirizzo di accesso.
 *
 * @return string
 */
function drkaos_login_slug() {
	$slug = defined( 'DRKAOS_LOGIN_SLUG' ) ? DRKAOS_LOGIN_SLUG : get_theme_mod( 'drkaos_login_slug', 'dklogin' );
	$slug = sanitize_title_with_dashes( (string) $slug );

	if ( '' === $slug || in_array( $slug, drkaos_login_reserved(), true ) ) {
		$slug = 'dklogin';
	}
	return $slug;
}

/**
 * L'indirizzo personalizzato è attivo?
 *
 * @return bool
 */
function drkaos_login_active() {
	if ( ! get_theme_mod( 'drkaos_login_enable', true ) ) {
		return false;
	}
	// Senza permalink "belli" il server non instrada /dklogin: meglio non toccare nulla.
	if ( ! get_option( 'permalink_structure' ) ) {
		return false;
	}
	return (bool) apply_filters( 'drkaos_login_url_enabled', true );
}

/**
 * Nuovo indirizzo di accesso completo.
 *
 * @param string|null $scheme Schema opzionale.
 * @return string
 */
function drkaos_login_url( $scheme = null ) {
	return home_url( drkaos_login_slug(), $scheme );
}

/**
 * Percorso richiesto, relativo alla home del sito, senza barre iniziali/finali.
 *
 * @return string
 */
function drkaos_login_request_path() {
	$uri  = isset( $_SERVER['REQUEST_URI'] ) ? wp_unslash( $_SERVER['REQUEST_URI'] ) : ''; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput
	$path = wp_parse_url( $uri, PHP_URL_PATH );
	$path = $path ? rawurldecode( $path ) : '';
	$home = wp_parse_url( home_url( '/' ), PHP_URL_PATH );
	$home = $home ? $home : '/';

	if ( 0 === strpos( $path, $home ) ) {
		$path = substr( $path, strlen( $home ) );
	}
	return trim( $path, '/' );
}

/**
 * Chi ha un cookie di accesso (anche scaduto) deve poter arrivare a /wp-admin,
 * così WordPress lo rimanda al login invece di mostrargli un 404.
 *
 * @return bool
 */
function drkaos_login_has_login_cookie() {
	foreach ( array_keys( $_COOKIE ) as $name ) {
		if ( 0 === strpos( $name, 'wordpress_logged_in_' ) ) {
			return true;
		}
	}
	return false;
}

/**
 * Mostra il login su /dklogin e nasconde i vecchi indirizzi.
 */
function drkaos_login_router() {
	if ( ! drkaos_login_active() || ( defined( 'WP_CLI' ) && WP_CLI ) || wp_doing_cron() ) {
		return;
	}

	global $pagenow;

	// 1) Il nuovo indirizzo: carica il login di WordPress.
	if ( drkaos_login_request_path() === drkaos_login_slug() ) {
		if ( ! defined( 'DRKAOS_LOGIN_SERVING' ) ) {
			define( 'DRKAOS_LOGIN_SERVING', true );
		}
		$pagenow = 'wp-login.php';

		// Variabili globali che wp-login.php si aspetta.
		global $error, $interim_login, $action, $user_login;

		require_once ABSPATH . 'wp-login.php';
		exit;
	}

	// 2) Vecchi indirizzi: 404.
	$hide = false;

	if ( 'wp-login.php' === $pagenow && ! defined( 'DRKAOS_LOGIN_SERVING' ) ) {
		$hide = true;
	} elseif (
		is_admin()
		&& ! is_user_logged_in()
		&& ! drkaos_login_has_login_cookie()
		&& ! wp_doing_ajax()
		&& ! in_array( $pagenow, array( 'admin-post.php', 'admin-ajax.php', 'load-styles.php', 'load-scripts.php' ), true )
	) {
		$hide = true;
	}

	if ( $hide ) {
		nocache_headers();
		wp_die(
			'<h1>404</h1><p>' . esc_html__( 'Pagina non trovata.', 'drkaos-wp-theme' ) . '</p>',
			esc_html__( 'Pagina non trovata', 'drkaos-wp-theme' ),
			array( 'response' => 404 )
		);
	}
}
add_action( 'wp_loaded', 'drkaos_login_router', 0 );

/**
 * Sostituisce wp-login.php nei link generati da WordPress (login, logout, reset password, registrazione...).
 *
 * @param string      $url    Indirizzo.
 * @param string      $path   Percorso richiesto.
 * @param string|null $scheme Schema.
 * @return string
 */
function drkaos_login_filter_site_url( $url, $path = '', $scheme = null ) {
	if ( $path && 0 === strpos( $path, 'wp-login.php' ) && drkaos_login_active() ) {
		return drkaos_login_url( $scheme ) . substr( $path, strlen( 'wp-login.php' ) );
	}
	return $url;
}
add_filter( 'site_url', 'drkaos_login_filter_site_url', 10, 3 );
add_filter( 'network_site_url', 'drkaos_login_filter_site_url', 10, 3 );

/**
 * Anche i reindirizzamenti verso wp-login.php vanno al nuovo indirizzo.
 *
 * @param string $location Destinazione.
 * @return string
 */
function drkaos_login_filter_redirect( $location ) {
	if ( false === strpos( $location, 'wp-login.php' ) || ! drkaos_login_active() ) {
		return $location;
	}
	$base = trailingslashit( get_option( 'siteurl' ) ) . 'wp-login.php';
	foreach ( array( $base, set_url_scheme( $base, 'http' ), set_url_scheme( $base, 'https' ) ) as $old ) {
		if ( 0 === strpos( $location, $old ) ) {
			return drkaos_login_url() . substr( $location, strlen( $old ) );
		}
	}
	return $location;
}
add_filter( 'wp_redirect', 'drkaos_login_filter_redirect' );

/**
 * WordPress rimanda /login, /admin e /dashboard verso il login: così rivelerebbe il nuovo indirizzo.
 */
function drkaos_login_remove_default_redirects() {
	if ( drkaos_login_active() ) {
		remove_action( 'template_redirect', 'wp_redirect_admin_locations', 1000 );
	}
}
add_action( 'init', 'drkaos_login_remove_default_redirects' );

/**
 * Customizer: attivazione e indirizzo.
 *
 * @param WP_Customize_Manager $wp_customize Customizer.
 */
function drkaos_login_customize_register( $wp_customize ) {
	$wp_customize->add_section(
		'drkaos_login',
		array(
			'title'       => esc_html__( 'Indirizzo di accesso', 'drkaos-wp-theme' ),
			'description' => esc_html__( 'Sposta il login di WordPress su un indirizzo tuo. Gli indirizzi /wp-login.php, /wp-admin (da scollegati), /admin e /login mostrano un errore 404. Richiede i permalink diversi da "Semplice".', 'drkaos-wp-theme' ),
			'priority'    => 33,
		)
	);

	$wp_customize->add_setting( 'drkaos_login_enable', array( 'default' => true, 'sanitize_callback' => 'rest_sanitize_boolean' ) );
	$wp_customize->add_control(
		'drkaos_login_enable',
		array(
			'label'   => esc_html__( 'Usa un indirizzo di accesso personalizzato', 'drkaos-wp-theme' ),
			'section' => 'drkaos_login',
			'type'    => 'checkbox',
		)
	);

	$wp_customize->add_setting( 'drkaos_login_slug', array( 'default' => 'dklogin', 'sanitize_callback' => 'sanitize_title_with_dashes' ) );
	$wp_customize->add_control(
		'drkaos_login_slug',
		array(
			/* translators: %s: indirizzo del sito */
			'label'       => sprintf( esc_html__( 'Indirizzo: %s', 'drkaos-wp-theme' ), trailingslashit( home_url() ) ),
			'description' => esc_html__( 'Solo lettere, numeri e trattini. Segnatelo: è l’unico modo per accedere.', 'drkaos-wp-theme' ),
			'section'     => 'drkaos_login',
			'type'        => 'text',
		)
	);
}
add_action( 'customize_register', 'drkaos_login_customize_register' );
