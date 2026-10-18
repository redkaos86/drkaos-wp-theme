<?php
/**
 * Popup nella Bacheca che invita a scaricare il plugin KaosSlider.
 *
 * - Compare solo agli amministratori, solo nella Bacheca (index.php).
 * - Non compare se KaosSlider è già installato.
 * - "Più tardi" lo nasconde per 7 giorni, "Non mostrare più" per sempre (per utente).
 * - Anteprima forzata: /wp-admin/index.php?drkaos_promo=1
 * - Disattivazione: add_filter( 'drkaos_promo_kaosslider', '__return_false' );
 *
 * @package DrKaosWPTheme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

const DRKAOS_PROMO_META = '_drkaos_promo_kaosslider';

/**
 * URL della pagina di download. Modificabile da Personalizza → Brand DR.KAOS, o via filtro.
 *
 * @return string
 */
function drkaos_promo_url() {
	$url = get_theme_mod( 'drkaos_kaosslider_url', 'https://it.wordpress.org/plugins/kaosslider/' );
	return esc_url_raw( apply_filters( 'drkaos_kaosslider_url', $url ) );
}

/**
 * Customizer: campo per l'indirizzo di download.
 *
 * @param WP_Customize_Manager $wp_customize Customizer.
 */
function drkaos_promo_customize_register( $wp_customize ) {
	$wp_customize->add_setting(
		'drkaos_kaosslider_url',
		array(
			'default'           => 'https://it.wordpress.org/plugins/kaosslider/',
			'sanitize_callback' => 'esc_url_raw',
		)
	);
	$wp_customize->add_control(
		'drkaos_kaosslider_url',
		array(
			'label'       => esc_html__( 'Pagina di download di KaosSlider', 'drkaos-wp-theme' ),
			'description' => esc_html__( 'Dove porta il pulsante del popup nella Bacheca.', 'drkaos-wp-theme' ),
			'section'     => 'drkaos_brand',
			'type'        => 'url',
		)
	);
}
add_action( 'customize_register', 'drkaos_promo_customize_register' );

/**
 * Il popup va mostrato?
 *
 * @return bool
 */
function drkaos_promo_should_show() {
	if ( ! apply_filters( 'drkaos_promo_kaosslider', true ) || ! current_user_can( 'manage_options' ) ) {
		return false;
	}

	$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
	if ( ! $screen || 'dashboard' !== $screen->id ) {
		return false;
	}

	// Anteprima forzata.
	if ( isset( $_GET['drkaos_promo'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
		return true;
	}

	if ( file_exists( WP_PLUGIN_DIR . '/kaosslider/kaosslider.php' ) ) {
		return false;
	}

	$state = get_user_meta( get_current_user_id(), DRKAOS_PROMO_META, true );
	if ( 'never' === $state ) {
		return false;
	}
	if ( $state && (int) $state > time() ) {
		return false;
	}

	return true;
}

/**
 * Salvataggio della scelta (Più tardi / Non mostrare più).
 */
function drkaos_promo_dismiss() {
	check_ajax_referer( 'drkaos_promo', 'nonce' );
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_send_json_error( null, 403 );
	}

	$forever = ! empty( $_POST['forever'] ); // phpcs:ignore WordPress.Security.NonceVerification
	update_user_meta( get_current_user_id(), DRKAOS_PROMO_META, $forever ? 'never' : time() + 7 * DAY_IN_SECONDS );
	wp_send_json_success();
}
add_action( 'wp_ajax_drkaos_promo_dismiss', 'drkaos_promo_dismiss' );

/**
 * Markup, stile e script del popup.
 */
function drkaos_promo_render() {
	if ( ! drkaos_promo_should_show() ) {
		return;
	}

	$bg    = esc_url( DRKAOS_THEME_URL . '/assets/img/login-bg.jpg' );
	$logo  = esc_url( DRKAOS_THEME_URL . '/assets/img/logo-white.png' );
	$url   = drkaos_promo_url();
	$nonce = wp_create_nonce( 'drkaos_promo' );
	?>
	<style>
		.dkp-overlay { position: fixed; inset: 0; z-index: 100100; display: flex; align-items: center; justify-content: center; padding: 20px; background: rgba(2, 10, 14, .72); backdrop-filter: blur(6px); opacity: 0; transition: opacity .35s ease; }
		.dkp-overlay.is-open { opacity: 1; }
		.dkp-modal { position: relative; width: 100%; max-width: 560px; max-height: calc(100vh - 40px); overflow: auto; background: #0f242d; border: 1px solid rgba(0, 224, 181, .35); border-radius: 22px; box-shadow: 0 30px 80px rgba(0, 0, 0, .6), 0 0 60px rgba(0, 224, 181, .12); color: #e6f2ef; transform: translateY(24px) scale(.97); transition: transform .4s cubic-bezier(.2, .9, .3, 1.2); }
		.dkp-overlay.is-open .dkp-modal { transform: none; }
		.dkp-hero { position: relative; height: 190px; display: flex; align-items: center; justify-content: center; background: linear-gradient(rgba(2, 18, 26, .35), #0f242d), url('<?php echo $bg; // phpcs:ignore WordPress.Security.EscapeOutput ?>') center / cover no-repeat; }
		.dkp-hero img { height: 96px; width: auto; filter: drop-shadow(0 6px 20px rgba(0, 0, 0, .5)); }
		.dkp-badge { position: absolute; top: 16px; left: 18px; padding: 5px 12px; border-radius: 999px; background: rgba(0, 224, 181, .16); border: 1px solid rgba(0, 224, 181, .5); color: #00e0b5; font-size: 11px; font-weight: 700; letter-spacing: .08em; text-transform: uppercase; }
		.dkp-body { padding: 8px 32px 28px; }
		.dkp-body h2 { margin: 0 0 8px; padding: 0; color: #fff; font-size: 26px; line-height: 1.2; font-weight: 700; }
		.dkp-body h2 span { color: #00e0b5; }
		.dkp-lead { margin: 0 0 16px; color: #c7dcdc; font-size: 14.5px; line-height: 1.55; }
		.dkp-list { margin: 0 0 22px; padding: 0; list-style: none; display: grid; gap: 9px; }
		.dkp-list li { display: flex; gap: 10px; align-items: flex-start; margin: 0; color: #e6f2ef; font-size: 13.5px; line-height: 1.4; }
		.dkp-list li::before { content: ""; flex: 0 0 18px; height: 18px; margin-top: 1px; border-radius: 50%; background: rgba(0, 224, 181, .18) url("data:image/svg+xml;utf8,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 20 20'%3E%3Cpath d='M5.5 10.5l3 3 6-7' fill='none' stroke='%2300e0b5' stroke-width='2.2' stroke-linecap='round' stroke-linejoin='round'/%3E%3C/svg%3E") center / 14px no-repeat; }
		.dkp-actions { display: flex; flex-wrap: wrap; gap: 10px; align-items: center; }
		body.wp-admin .dkp-modal a.dkp-btn, body.wp-admin .dkp-modal button.dkp-btn { display: inline-flex; align-items: center; justify-content: center; gap: 8px; min-height: 46px; padding: 0 24px; border-radius: 999px; font-size: 14px; font-weight: 700; text-decoration: none; cursor: pointer; transition: transform .15s, background .15s; }
		body.wp-admin .dkp-modal a.dkp-btn-primary { background: #008f73; border: 1px solid #00c9a3; color: #fff !important; box-shadow: 0 8px 24px rgba(0, 143, 115, .45); }
		body.wp-admin .dkp-modal a.dkp-btn-primary:hover { background: #00a887; transform: translateY(-1px); color: #fff !important; }
		body.wp-admin .dkp-modal button.dkp-btn-ghost { background: transparent; border: 1px solid rgba(255, 255, 255, .25); color: #e6f2ef !important; }
		body.wp-admin .dkp-modal button.dkp-btn-ghost:hover { border-color: #00e0b5; color: #00e0b5 !important; }
		.dkp-never { margin-left: auto; padding: 0; border: 0; background: none; color: #9db7b7 !important; font-size: 12px; text-decoration: underline; cursor: pointer; }
		.dkp-never:hover { color: #fff !important; }
		.dkp-close { position: absolute; top: 12px; right: 14px; width: 34px; height: 34px; border: 0; border-radius: 50%; background: rgba(2, 18, 26, .6); color: #fff; font-size: 20px; line-height: 1; cursor: pointer; }
		.dkp-close:hover { background: rgba(0, 224, 181, .25); }
		@media (max-width: 520px) { .dkp-body { padding: 8px 20px 22px; } .dkp-body h2 { font-size: 22px; } .dkp-never { margin-left: 0; } }
		@media (prefers-reduced-motion: reduce) { .dkp-overlay, .dkp-modal { transition: none; } }
	</style>

	<div class="dkp-overlay" id="dkp-overlay" role="presentation">
		<div class="dkp-modal" role="dialog" aria-modal="true" aria-labelledby="dkp-title">
			<button type="button" class="dkp-close" data-dkp="later" aria-label="<?php esc_attr_e( 'Chiudi', 'drkaos-wp-theme' ); ?>">&times;</button>
			<div class="dkp-hero">
				<span class="dkp-badge"><?php esc_html_e( 'Plugin per WordPress', 'drkaos-wp-theme' ); ?></span>
				<img src="<?php echo $logo; // phpcs:ignore WordPress.Security.EscapeOutput ?>" alt="DR.KAOS">
			</div>
			<div class="dkp-body">
				<h2 id="dkp-title"><?php esc_html_e( 'Crea slide da urlo con', 'drkaos-wp-theme' ); ?> <span>KaosSlider</span></h2>
				<p class="dkp-lead"><?php esc_html_e( 'Slider, caroselli e sezioni hero animate, con un editor visivo a livelli. Dai al tuo sito un impatto che le slide normali non hanno.', 'drkaos-wp-theme' ); ?></p>
				<ul class="dkp-list">
					<li><?php esc_html_e( 'Animazioni di entrata, uscita e loop su testi, pulsanti, immagini e forme', 'drkaos-wp-theme' ); ?></li>
					<li><?php esc_html_e( 'Sfondi con colori, gradienti, immagini e video (file, YouTube, Vimeo)', 'drkaos-wp-theme' ); ?></li>
					<li><?php esc_html_e( 'Parallax, effetti WebGL, vetro e ombre personalizzate', 'drkaos-wp-theme' ); ?></li>
					<li><?php esc_html_e( 'Compatibile con Gutenberg, Elementor e Divi, e perfetto su desktop, tablet e mobile', 'drkaos-wp-theme' ); ?></li>
				</ul>
				<div class="dkp-actions">
					<a class="dkp-btn dkp-btn-primary" href="<?php echo esc_url( $url ); ?>" target="_blank" rel="noopener" data-dkp="later"><?php esc_html_e( 'Scarica KaosSlider', 'drkaos-wp-theme' ); ?> &rarr;</a>
					<button type="button" class="dkp-btn dkp-btn-ghost" data-dkp="later"><?php esc_html_e( 'Più tardi', 'drkaos-wp-theme' ); ?></button>
					<button type="button" class="dkp-never" data-dkp="never"><?php esc_html_e( 'Non mostrare più', 'drkaos-wp-theme' ); ?></button>
				</div>
			</div>
		</div>
	</div>

	<script>
		(function () {
			var overlay = document.getElementById('dkp-overlay');
			if (!overlay) { return; }
			var preview = <?php echo isset( $_GET['drkaos_promo'] ) ? 'true' : 'false'; // phpcs:ignore WordPress.Security.NonceVerification ?>;
			var lastFocus = document.activeElement;

			function close(forever) {
				overlay.classList.remove('is-open');
				setTimeout(function () { overlay.remove(); }, 350);
				document.removeEventListener('keydown', onKey);
				if (lastFocus && lastFocus.focus) { lastFocus.focus(); }
				if (preview) { return; }
				var data = new URLSearchParams({ action: 'drkaos_promo_dismiss', nonce: '<?php echo esc_js( $nonce ); ?>', forever: forever ? '1' : '' });
				fetch(window.ajaxurl, { method: 'POST', credentials: 'same-origin', body: data });
			}
			function onKey(e) { if (e.key === 'Escape') { close(false); } }

			overlay.addEventListener('click', function (e) {
				if (e.target === overlay) { close(false); return; }
				var t = e.target.closest('[data-dkp]');
				if (t) { close(t.getAttribute('data-dkp') === 'never'); }
			});
			document.addEventListener('keydown', onKey);
			setTimeout(function () {
				overlay.classList.add('is-open');
				var primary = overlay.querySelector('.dkp-btn-primary');
				if (primary) { primary.focus({ preventScroll: true }); }
			}, 600);
		})();
	</script>
	<?php
}
add_action( 'admin_footer', 'drkaos_promo_render' );
