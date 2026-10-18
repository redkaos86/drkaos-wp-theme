<?php
/**
 * Pagina di personalizzazione della modalità manutenzione (Aspetto → Manutenzione).
 *
 * @package DrKaosWPTheme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Registra opzione e voce di menu.
 */
function drkaos_maint_admin_init() {
	register_setting(
		'drkaos_maintenance_group',
		DRKAOS_MAINT_OPTION,
		array(
			'type'              => 'array',
			'sanitize_callback' => 'drkaos_maint_sanitize',
			'default'           => drkaos_maint_defaults(),
		)
	);
}
add_action( 'admin_init', 'drkaos_maint_admin_init' );

function drkaos_maint_admin_menu() {
	add_theme_page(
		__( 'Modalità manutenzione', 'drkaos-wp-theme' ),
		__( 'Manutenzione', 'drkaos-wp-theme' ),
		'manage_options',
		'drkaos-maintenance',
		'drkaos_maint_admin_page'
	);
}
add_action( 'admin_menu', 'drkaos_maint_admin_menu' );

/**
 * Risorse della pagina.
 *
 * @param string $hook Pagina corrente.
 */
function drkaos_maint_admin_assets( $hook ) {
	if ( 'appearance_page_drkaos-maintenance' !== $hook ) {
		return;
	}
	wp_enqueue_media();
	wp_enqueue_style( 'drkaos-maint', DRKAOS_THEME_URL . '/assets/css/maintenance.css', array(), drkaos_asset_ver( '/assets/css/maintenance.css' ) );
	wp_enqueue_style( 'drkaos-maint-admin', DRKAOS_THEME_URL . '/assets/css/maintenance-admin.css', array( 'drkaos-maint' ), drkaos_asset_ver( '/assets/css/maintenance-admin.css' ) );
	wp_enqueue_script( 'drkaos-maint-admin', DRKAOS_THEME_URL . '/assets/js/maintenance-admin.js', array(), drkaos_asset_ver( '/assets/js/maintenance-admin.js' ), true );

	$fonts = array();
	foreach ( drkaos_maint_fonts() as $key => $f ) {
		$fonts[ $key ] = array(
			'stack' => $f['stack'],
			'url'   => $f['g'] ? 'https://fonts.googleapis.com/css2?family=' . $f['g'] . '&display=swap' : '',
		);
	}
	wp_add_inline_script(
		'drkaos-maint-admin',
		'window.drkaosMaintAdmin = ' . drkaos_json(
			array(
				'fonts'     => $fonts,
				'defaultBg' => DRKAOS_THEME_URL . '/assets/img/login-bg.jpg',
				'defaultLogo' => DRKAOS_THEME_URL . '/assets/img/logo-white.png',
				'year'      => gmdate( 'Y' ),
				'site'      => get_bloginfo( 'name' ),
				'pick'      => __( 'Scegli immagine', 'drkaos-wp-theme' ),
				'use'       => __( 'Usa questa immagine', 'drkaos-wp-theme' ),
			)
		) . ';',
		'before'
	);
}
add_action( 'admin_enqueue_scripts', 'drkaos_maint_admin_assets' );

/**
 * Campo: menu dei font.
 */
function drkaos_maint_font_select( $name, $value ) {
	echo '<select name="' . esc_attr( DRKAOS_MAINT_OPTION . '[' . $name . ']' ) . '" data-dkm="' . esc_attr( $name ) . '">';
	foreach ( drkaos_maint_fonts() as $key => $f ) {
		printf( '<option value="%s"%s>%s</option>', esc_attr( $key ), selected( $value, $key, false ), esc_html( $f['label'] ) );
	}
	echo '</select>';
}

/**
 * Campo: menu dei pesi.
 */
function drkaos_maint_weight_select( $name, $value ) {
	$labels = array( 100 => 'Sottilissimo (100)', 200 => 'Extra leggero (200)', 300 => 'Leggero (300)', 400 => 'Normale (400)', 500 => 'Medio (500)', 600 => 'Semi-grassetto (600)', 700 => 'Grassetto (700)', 800 => 'Extra grassetto (800)', 900 => 'Nero (900)' );
	echo '<select name="' . esc_attr( DRKAOS_MAINT_OPTION . '[' . $name . ']' ) . '" data-dkm="' . esc_attr( $name ) . '">';
	foreach ( $labels as $w => $label ) {
		printf( '<option value="%d"%s>%s</option>', (int) $w, selected( (int) $value, $w, false ), esc_html( $label ) );
	}
	echo '</select>';
}

/**
 * Campo: cursore con numero.
 */
function drkaos_maint_range( $name, $value, $min, $max, $unit = 'px' ) {
	$n = esc_attr( DRKAOS_MAINT_OPTION . '[' . $name . ']' );
	printf(
		'<div class="dkma-range"><input type="range" min="%1$d" max="%2$d" value="%3$d" data-dkm-range="%4$s"><input type="number" min="%1$d" max="%2$d" value="%3$d" name="%5$s" data-dkm="%4$s"> <span>%6$s</span></div>',
		(int) $min,
		(int) $max,
		(int) $value,
		esc_attr( $name ),
		$n, // phpcs:ignore WordPress.Security.EscapeOutput
		esc_html( $unit )
	);
}

/**
 * Campo: selettore immagine dalla libreria media.
 */
function drkaos_maint_image_field( $name, $value, $placeholder ) {
	$n = DRKAOS_MAINT_OPTION . '[' . $name . ']';
	?>
	<div class="dkma-image" data-target="<?php echo esc_attr( $name ); ?>">
		<div class="dkma-image__thumb" style="background-image:url('<?php echo esc_url( $value ? $value : $placeholder ); ?>')"></div>
		<div>
			<input type="hidden" name="<?php echo esc_attr( $n ); ?>" value="<?php echo esc_url( $value ); ?>" data-dkm="<?php echo esc_attr( $name ); ?>">
			<button type="button" class="button" data-dkm-pick="<?php echo esc_attr( $name ); ?>"><?php esc_html_e( 'Scegli immagine', 'drkaos-wp-theme' ); ?></button>
			<button type="button" class="button-link" data-dkm-clear="<?php echo esc_attr( $name ); ?>"><?php esc_html_e( 'Ripristina predefinita', 'drkaos-wp-theme' ); ?></button>
		</div>
	</div>
	<?php
}

/**
 * Pagina.
 */
function drkaos_maint_admin_page() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}
	$o   = drkaos_maint_get();
	$opt = DRKAOS_MAINT_OPTION;
	?>
	<div class="wrap dkma">
		<h1><?php esc_html_e( 'Modalità manutenzione', 'drkaos-wp-theme' ); ?></h1>
		<p class="dkma-lead"><?php esc_html_e( 'Quando è attiva, i visitatori vedono questa pagina (con stato 503, corretto per Google). Tu e chi può modificare gli articoli continuate a vedere il sito normalmente. Puoi accendere e spegnere la modalità anche dalla barra in alto.', 'drkaos-wp-theme' ); ?></p>

		<form method="post" action="options.php" class="dkma-grid">
			<?php settings_fields( 'drkaos_maintenance_group' ); ?>

			<div class="dkma-col">
				<section class="dkma-card">
					<h2><?php esc_html_e( 'Stato', 'drkaos-wp-theme' ); ?></h2>
					<label class="dkma-switch">
						<input type="checkbox" name="<?php echo esc_attr( $opt ); ?>[enabled]" value="1" <?php checked( $o['enabled'] ); ?>>
						<span class="dkma-switch__ui"><i></i></span>
						<strong><?php esc_html_e( 'Modalità manutenzione attiva', 'drkaos-wp-theme' ); ?></strong>
					</label>
				</section>

				<section class="dkma-card">
					<h2><?php esc_html_e( 'Sfondo', 'drkaos-wp-theme' ); ?></h2>
					<?php drkaos_maint_image_field( 'bg_image', $o['bg_image'], DRKAOS_THEME_URL . '/assets/img/login-bg.jpg' ); ?>
					<p class="dkma-row"><label><?php esc_html_e( 'Scurisci lo sfondo', 'drkaos-wp-theme' ); ?></label>
						<?php drkaos_maint_range( 'overlay', $o['overlay'], 0, 100, '%' ); ?></p>
				</section>

				<section class="dkma-card">
					<h2><?php esc_html_e( 'Logo', 'drkaos-wp-theme' ); ?></h2>
					<?php drkaos_maint_image_field( 'logo', $o['logo'], DRKAOS_THEME_URL . '/assets/img/logo-white.png' ); ?>
					<p class="dkma-row"><label><?php esc_html_e( 'Larghezza del logo', 'drkaos-wp-theme' ); ?></label>
						<?php drkaos_maint_range( 'logo_width', $o['logo_width'], 40, 900 ); ?></p>
				</section>

				<section class="dkma-card">
					<h2><?php esc_html_e( 'Titolo sotto al logo', 'drkaos-wp-theme' ); ?></h2>
					<p class="dkma-row"><label><?php esc_html_e( 'Testo', 'drkaos-wp-theme' ); ?></label>
						<input type="text" class="large-text" name="<?php echo esc_attr( $opt ); ?>[title]" value="<?php echo esc_attr( $o['title'] ); ?>" data-dkm="title"></p>
					<p class="dkma-row"><label><?php esc_html_e( 'Font', 'drkaos-wp-theme' ); ?></label><?php drkaos_maint_font_select( 'title_font', $o['title_font'] ); ?></p>
					<p class="dkma-row"><label><?php esc_html_e( 'Dimensione', 'drkaos-wp-theme' ); ?></label><?php drkaos_maint_range( 'title_size', $o['title_size'], 12, 220 ); ?></p>
					<p class="dkma-row"><label><?php esc_html_e( 'Spessore', 'drkaos-wp-theme' ); ?></label><?php drkaos_maint_weight_select( 'title_weight', $o['title_weight'] ); ?></p>
					<p class="dkma-row"><label><?php esc_html_e( 'Colore', 'drkaos-wp-theme' ); ?></label>
						<input type="color" name="<?php echo esc_attr( $opt ); ?>[title_color]" value="<?php echo esc_attr( $o['title_color'] ); ?>" data-dkm="title_color"></p>
				</section>

				<section class="dkma-card">
					<h2><?php esc_html_e( 'Frase', 'drkaos-wp-theme' ); ?></h2>
					<p class="dkma-row"><label><?php esc_html_e( 'Testo', 'drkaos-wp-theme' ); ?></label>
						<textarea rows="3" class="large-text" name="<?php echo esc_attr( $opt ); ?>[text]" data-dkm="text"><?php echo esc_textarea( $o['text'] ); ?></textarea></p>
					<p class="dkma-row"><label><?php esc_html_e( 'Font', 'drkaos-wp-theme' ); ?></label><?php drkaos_maint_font_select( 'text_font', $o['text_font'] ); ?></p>
					<p class="dkma-row"><label><?php esc_html_e( 'Dimensione', 'drkaos-wp-theme' ); ?></label><?php drkaos_maint_range( 'text_size', $o['text_size'], 10, 100 ); ?></p>
					<p class="dkma-row"><label><?php esc_html_e( 'Spessore', 'drkaos-wp-theme' ); ?></label><?php drkaos_maint_weight_select( 'text_weight', $o['text_weight'] ); ?></p>
					<p class="dkma-row"><label><?php esc_html_e( 'Colore', 'drkaos-wp-theme' ); ?></label>
						<input type="color" name="<?php echo esc_attr( $opt ); ?>[text_color]" value="<?php echo esc_attr( $o['text_color'] ); ?>" data-dkm="text_color"></p>
				</section>

				<section class="dkma-card">
					<h2><?php esc_html_e( 'Barra inferiore trasparente (copy)', 'drkaos-wp-theme' ); ?></h2>
					<label class="dkma-switch dkma-switch--small">
						<input type="checkbox" name="<?php echo esc_attr( $opt ); ?>[bar_enabled]" value="1" <?php checked( $o['bar_enabled'] ); ?> data-dkm="bar_enabled">
						<span class="dkma-switch__ui"><i></i></span>
						<strong><?php esc_html_e( 'Mostra la barra', 'drkaos-wp-theme' ); ?></strong>
					</label>
					<p class="dkma-row"><label><?php esc_html_e( 'Testo', 'drkaos-wp-theme' ); ?></label>
						<textarea rows="2" class="large-text" name="<?php echo esc_attr( $opt ); ?>[bar_text]" data-dkm="bar_text"><?php echo esc_textarea( $o['bar_text'] ); ?></textarea>
						<span class="description"><?php esc_html_e( 'Usa [anno] per l’anno corrente e [sito] per il nome del sito. Sono ammessi link e a capo (<br>).', 'drkaos-wp-theme' ); ?></span></p>
					<p class="dkma-row"><label><?php esc_html_e( 'Font', 'drkaos-wp-theme' ); ?></label><?php drkaos_maint_font_select( 'bar_font', $o['bar_font'] ); ?></p>
					<p class="dkma-row"><label><?php esc_html_e( 'Dimensione', 'drkaos-wp-theme' ); ?></label><?php drkaos_maint_range( 'bar_size', $o['bar_size'], 9, 40 ); ?></p>
					<p class="dkma-row"><label><?php esc_html_e( 'Colore del testo', 'drkaos-wp-theme' ); ?></label>
						<input type="color" name="<?php echo esc_attr( $opt ); ?>[bar_color]" value="<?php echo esc_attr( $o['bar_color'] ); ?>" data-dkm="bar_color"></p>
					<p class="dkma-row"><label><?php esc_html_e( 'Opacità dello sfondo della barra', 'drkaos-wp-theme' ); ?></label><?php drkaos_maint_range( 'bar_opacity', $o['bar_opacity'], 0, 100, '%' ); ?></p>
				</section>

				<p class="dkma-note"><?php esc_html_e( 'I font diversi da quelli di sistema vengono caricati da Google Fonts solo nella pagina di manutenzione.', 'drkaos-wp-theme' ); ?></p>

				<?php submit_button( __( 'Salva modifiche', 'drkaos-wp-theme' ) ); ?>
			</div>

			<aside class="dkma-col dkma-col--preview">
				<div class="dkma-preview-wrap">
					<div class="dkma-preview-head">
						<strong><?php esc_html_e( 'Anteprima dal vivo', 'drkaos-wp-theme' ); ?></strong>
						<a href="<?php echo esc_url( add_query_arg( 'drkaos_maint_preview', '1', home_url( '/' ) ) ); ?>" target="_blank" rel="noopener"><?php esc_html_e( 'Apri a schermo intero ↗', 'drkaos-wp-theme' ); ?></a>
					</div>
					<div class="dkma-preview" id="dkma-preview">
						<div class="dkma-stage" id="dkma-stage"><?php echo drkaos_maint_markup( $o, true ); // phpcs:ignore WordPress.Security.EscapeOutput ?></div>
					</div>
					<p class="dkma-note"><?php esc_html_e( 'L’anteprima si aggiorna mentre scrivi. Ricorda di salvare.', 'drkaos-wp-theme' ); ?></p>
				</div>
			</aside>
		</form>
	</div>
	<?php
}
