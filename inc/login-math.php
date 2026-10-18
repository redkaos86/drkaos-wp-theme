<?php
/**
 * Verifica matematica nel login di WordPress (anti-bot).
 *
 * Disattivazione di emergenza: define( 'DRKAOS_DISABLE_LOGIN_MATH', true ); in wp-config.php
 * oppure add_filter( 'drkaos_login_math', '__return_false' );
 *
 * @package DrKaosWPTheme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Verifica attiva?
 *
 * @return bool
 */
function drkaos_login_math_enabled() {
	if ( defined( 'DRKAOS_DISABLE_LOGIN_MATH' ) && DRKAOS_DISABLE_LOGIN_MATH ) {
		return false;
	}
	return (bool) apply_filters( 'drkaos_login_math', true );
}

/**
 * Mostra la domanda nel form di login. La risposta resta sul server (transient monouso).
 */
function drkaos_login_math_field() {
	if ( ! drkaos_login_math_enabled() ) {
		return;
	}

	// Solo addizioni con numeri da 0 a 10.
	$a        = wp_rand( 0, 10 );
	$b        = wp_rand( 0, 10 );
	$question = $a . ' + ' . $b;
	$answer   = $a + $b;

	$id = wp_generate_password( 20, false, false );
	set_transient( 'drkaos_math_' . $id, $answer, 15 * MINUTE_IN_SECONDS );
	?>
	<p class="drkaos-math">
		<label for="drkaos_math_answer">
			<?php esc_html_e( 'Verifica anti-bot: quanto fa', 'drkaos-wp-theme' ); ?>
			<strong><?php echo esc_html( $question ); ?> = ?</strong>
		</label>
		<input type="text" name="drkaos_math_answer" id="drkaos_math_answer" class="input" inputmode="numeric" pattern="[0-9]*" autocomplete="off" required>
		<input type="hidden" name="drkaos_math_id" value="<?php echo esc_attr( $id ); ?>">
	</p>
	<?php
}
add_action( 'login_form', 'drkaos_login_math_field' );

/**
 * Controlla la risposta quando si invia il form di login di wp-login.php.
 *
 * @param WP_User|WP_Error|null $user     Risultato dei controlli precedenti.
 * @return WP_User|WP_Error|null
 */
function drkaos_login_math_check( $user ) {
	// Solo il form di wp-login.php: REST, XML-RPC, WooCommerce e app non vengono toccati.
	if ( ! drkaos_login_math_enabled() || 'wp-login.php' !== $GLOBALS['pagenow'] || 'POST' !== ( $_SERVER['REQUEST_METHOD'] ?? '' ) || ! isset( $_POST['log'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
		return $user;
	}

	$id     = isset( $_POST['drkaos_math_id'] ) ? preg_replace( '/[^A-Za-z0-9]/', '', wp_unslash( $_POST['drkaos_math_id'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification, WordPress.Security.ValidatedSanitizedInput
	$given  = isset( $_POST['drkaos_math_answer'] ) ? trim( sanitize_text_field( wp_unslash( $_POST['drkaos_math_answer'] ) ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification
	$stored = $id ? get_transient( 'drkaos_math_' . $id ) : false;

	if ( $id ) {
		delete_transient( 'drkaos_math_' . $id ); // Monouso.
	}

	if ( false !== $stored && '' !== $given && ctype_digit( $given ) && (int) $given === (int) $stored ) {
		return $user;
	}

	return new WP_Error(
		'drkaos_math_failed',
		'<strong>' . esc_html__( 'Errore:', 'drkaos-wp-theme' ) . '</strong> ' . esc_html__( 'la verifica anti-bot è errata o scaduta. Riprova con il nuovo calcolo.', 'drkaos-wp-theme' )
	);
}
add_filter( 'authenticate', 'drkaos_login_math_check', 99 );

/**
 * Stile del campo, coerente con la pagina di login.
 */
function drkaos_login_math_styles() {
	if ( ! drkaos_login_math_enabled() ) {
		return;
	}
	?>
	<style>
		.drkaos-math { margin-bottom: 16px; }
		.drkaos-math label { display: block; margin-bottom: 4px; }
		.drkaos-math strong { color: #007a63; font-size: 1.15em; letter-spacing: .04em; white-space: nowrap; }
		.drkaos-math input.input { width: 100%; margin: 4px 0 0; font-size: 1.2rem; }
	</style>
	<?php
}
add_action( 'login_head', 'drkaos_login_math_styles' );
