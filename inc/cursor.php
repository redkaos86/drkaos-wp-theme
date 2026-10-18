<?php
/**
 * Cursore personalizzato (solo sito pubblico): pallina che segue il mouse,
 * anello, icone, logo/immagine, ingrandimento su immagini/link e effetto negativo.
 *
 * Si imposta da Aspetto → Personalizza → "Cursore personalizzato".
 * Disattivazione via codice: add_filter( 'drkaos_cursor_enabled', '__return_false' );
 *
 * @package DrKaosWPTheme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Impostazioni di default.
 *
 * @return array
 */
function drkaos_cursor_defaults() {
	return array(
		'enable'         => false,
		'shape'          => 'dot',
		'color'          => '#ffffff',
		'size'           => 18,
		'ring_size'      => 44,
		'hover_scale'    => 2.5,
		'smooth'         => 18,
		'invert'         => true,
		'icon'           => 'arrow',
		'image'          => '',
		'image_anchor'   => 'center',
		'hide_native'    => true,
		'grow_selector'  => 'a, button, img, picture, video, [role="button"], [data-cursor="grow"]',
	);
}

/**
 * Forme disponibili.
 *
 * @return array
 */
function drkaos_cursor_shapes() {
	return array(
		'dot'      => __( 'Pallina che segue il mouse', 'drkaos-wp-theme' ),
		'ring'     => __( 'Anello', 'drkaos-wp-theme' ),
		'dot_ring' => __( 'Pallina + anello', 'drkaos-wp-theme' ),
		'icon'     => __( 'Icona dal set', 'drkaos-wp-theme' ),
		'image'    => __( 'Logo / immagine personalizzata', 'drkaos-wp-theme' ),
	);
}

/**
 * Set di icone (SVG 24x24, colorate con il colore scelto). 'drkaos' usa il simbolo del logo.
 *
 * @return array nome => array( label, svg, anchor ).
 */
function drkaos_cursor_icons() {
	return array(
		'arrow'     => array( __( 'Freccia', 'drkaos-wp-theme' ), '<svg viewBox="0 0 24 24" fill="currentColor"><path d="M5 3l14 8-6 1.8L10 20z"/></svg>', 'topleft' ),
		'star'      => array( __( 'Stella', 'drkaos-wp-theme' ), '<svg viewBox="0 0 24 24" fill="currentColor"><path d="M12 2l2.9 6.9 7.1.6-5.4 4.7 1.7 7-6.3-3.8-6.3 3.8 1.7-7L2 9.5l7.1-.6z"/></svg>', 'center' ),
		'heart'     => array( __( 'Cuore', 'drkaos-wp-theme' ), '<svg viewBox="0 0 24 24" fill="currentColor"><path d="M12 21s-8-5.3-8-11a4.5 4.5 0 018-2.8A4.5 4.5 0 0120 10c0 5.7-8 11-8 11z"/></svg>', 'center' ),
		'plus'      => array( __( 'Più', 'drkaos-wp-theme' ), '<svg viewBox="0 0 24 24" fill="currentColor"><path d="M10.5 3h3v7.5H21v3h-7.5V21h-3v-7.5H3v-3h7.5z"/></svg>', 'center' ),
		'bolt'      => array( __( 'Fulmine', 'drkaos-wp-theme' ), '<svg viewBox="0 0 24 24" fill="currentColor"><path d="M13 2L4 14h6l-1 8 9-12h-6z"/></svg>', 'center' ),
		'crosshair' => array( __( 'Mirino', 'drkaos-wp-theme' ), '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="6"/><path d="M12 1v6M12 17v6M1 12h6M17 12h6"/></svg>', 'center' ),
		'eye'       => array( __( 'Occhio', 'drkaos-wp-theme' ), '<svg viewBox="0 0 24 24" fill="currentColor" fill-rule="evenodd"><path d="M12 5C6.5 5 2.5 9 1 12c1.5 3 5.5 7 11 7s9.5-4 11-7c-1.5-3-5.5-7-11-7zm0 3a4 4 0 110 8 4 4 0 010-8z"/></svg>', 'center' ),
		'hexagon'   => array( __( 'Esagono', 'drkaos-wp-theme' ), '<svg viewBox="0 0 24 24" fill="currentColor"><path d="M12 2l8.7 5v10L12 22l-8.7-5V7z"/></svg>', 'center' ),
		'drkaos'    => array( __( 'Simbolo DR.KAOS', 'drkaos-wp-theme' ), '', 'center' ),
	);
}

/**
 * Valore di un'impostazione.
 *
 * @param string $key Chiave.
 * @return mixed
 */
function drkaos_cursor_opt( $key ) {
	$defaults = drkaos_cursor_defaults();
	return get_theme_mod( 'drkaos_cursor_' . $key, $defaults[ $key ] );
}

/**
 * Customizer.
 *
 * @param WP_Customize_Manager $wp_customize Customizer.
 */
function drkaos_cursor_customize_register( $wp_customize ) {
	$d = drkaos_cursor_defaults();

	$wp_customize->add_section(
		'drkaos_cursor',
		array(
			'title'       => esc_html__( 'Cursore personalizzato', 'drkaos-wp-theme' ),
			'description' => esc_html__( 'Sostituisce il cursore del mouse sul sito (non sui dispositivi touch). Non compare dentro l’editor di Elementor.', 'drkaos-wp-theme' ),
			'priority'    => 32,
		)
	);

	$is_shape = function ( $shapes ) {
		return function () use ( $shapes ) {
			return in_array( get_theme_mod( 'drkaos_cursor_shape', 'dot' ), $shapes, true );
		};
	};

	$add = function ( $key, $args, $control ) use ( $wp_customize, $d ) {
		$wp_customize->add_setting(
			'drkaos_cursor_' . $key,
			array(
				'default'           => $d[ $key ],
				'sanitize_callback' => $args['sanitize'],
			)
		);
		$control['section'] = 'drkaos_cursor';
		if ( isset( $args['class'] ) ) {
			$class = $args['class'];
			$wp_customize->add_control( new $class( $wp_customize, 'drkaos_cursor_' . $key, $control ) );
		} else {
			$wp_customize->add_control( 'drkaos_cursor_' . $key, $control );
		}
	};

	$add( 'enable', array( 'sanitize' => 'rest_sanitize_boolean' ), array(
		'label' => esc_html__( 'Attiva il cursore personalizzato', 'drkaos-wp-theme' ),
		'type'  => 'checkbox',
	) );

	$add( 'shape', array( 'sanitize' => function ( $v ) { return array_key_exists( $v, drkaos_cursor_shapes() ) ? $v : 'dot'; } ), array(
		'label'   => esc_html__( 'Forma', 'drkaos-wp-theme' ),
		'type'    => 'select',
		'choices' => drkaos_cursor_shapes(),
	) );

	$icon_choices = array();
	foreach ( drkaos_cursor_icons() as $slug => $icon ) {
		$icon_choices[ $slug ] = $icon[0];
	}
	$add( 'icon', array( 'sanitize' => function ( $v ) { return array_key_exists( $v, drkaos_cursor_icons() ) ? $v : 'arrow'; } ), array(
		'label'           => esc_html__( 'Icona', 'drkaos-wp-theme' ),
		'type'            => 'select',
		'choices'         => $icon_choices,
		'active_callback' => $is_shape( array( 'icon' ) ),
	) );

	$add( 'image', array( 'sanitize' => 'esc_url_raw', 'class' => 'WP_Customize_Image_Control' ), array(
		'label'           => esc_html__( 'Logo / immagine (meglio PNG o SVG trasparente)', 'drkaos-wp-theme' ),
		'active_callback' => $is_shape( array( 'image' ) ),
	) );

	$add( 'image_anchor', array( 'sanitize' => function ( $v ) { return 'topleft' === $v ? 'topleft' : 'center'; } ), array(
		'label'           => esc_html__( 'Punto che clicca', 'drkaos-wp-theme' ),
		'type'            => 'select',
		'choices'         => array(
			'center'  => esc_html__( 'Centro dell’immagine', 'drkaos-wp-theme' ),
			'topleft' => esc_html__( 'Angolo in alto a sinistra (come una freccia)', 'drkaos-wp-theme' ),
		),
		'active_callback' => $is_shape( array( 'image' ) ),
	) );

	$add( 'color', array( 'sanitize' => 'sanitize_hex_color', 'class' => 'WP_Customize_Color_Control' ), array(
		'label'       => esc_html__( 'Colore', 'drkaos-wp-theme' ),
		'description' => esc_html__( 'Con l’effetto negativo attivo il bianco dà un negativo puro; altri colori danno tinte diverse.', 'drkaos-wp-theme' ),
	) );

	$add( 'size', array( 'sanitize' => function ( $v ) { return max( 4, min( 200, absint( $v ) ) ); } ), array(
		'label'       => esc_html__( 'Dimensione (px)', 'drkaos-wp-theme' ),
		'type'        => 'range',
		'input_attrs' => array( 'min' => 6, 'max' => 120, 'step' => 1 ),
	) );

	$add( 'ring_size', array( 'sanitize' => function ( $v ) { return max( 16, min( 300, absint( $v ) ) ); } ), array(
		'label'           => esc_html__( 'Dimensione dell’anello (px)', 'drkaos-wp-theme' ),
		'type'            => 'range',
		'input_attrs'     => array( 'min' => 20, 'max' => 160, 'step' => 1 ),
		'active_callback' => $is_shape( array( 'ring', 'dot_ring' ) ),
	) );

	$add( 'hover_scale', array( 'sanitize' => function ( $v ) { return max( 1, min( 8, (float) $v ) ); } ), array(
		'label'       => esc_html__( 'Ingrandimento su immagini e link (×)', 'drkaos-wp-theme' ),
		'description' => esc_html__( '1 = nessun ingrandimento.', 'drkaos-wp-theme' ),
		'type'        => 'range',
		'input_attrs' => array( 'min' => 1, 'max' => 6, 'step' => 0.1 ),
	) );

	$add( 'smooth', array( 'sanitize' => function ( $v ) { return max( 4, min( 100, absint( $v ) ) ); } ), array(
		'label'       => esc_html__( 'Reattività', 'drkaos-wp-theme' ),
		'description' => esc_html__( 'Basso = scia morbida e lenta, 100 = segue il mouse all’istante.', 'drkaos-wp-theme' ),
		'type'        => 'range',
		'input_attrs' => array( 'min' => 4, 'max' => 100, 'step' => 1 ),
	) );

	$add( 'invert', array( 'sanitize' => 'rest_sanitize_boolean' ), array(
		'label'       => esc_html__( 'Effetto negativo sotto il cursore', 'drkaos-wp-theme' ),
		'description' => esc_html__( 'Inverte i colori di immagini e testo che passano sotto il cursore.', 'drkaos-wp-theme' ),
		'type'        => 'checkbox',
	) );

	$add( 'grow_selector', array( 'sanitize' => 'sanitize_text_field' ), array(
		'label'       => esc_html__( 'Elementi che ingrandiscono il cursore (selettori CSS)', 'drkaos-wp-theme' ),
		'description' => esc_html__( 'Aggiungi data-cursor="grow" a qualsiasi elemento in Elementor per farlo ingrandire.', 'drkaos-wp-theme' ),
		'type'        => 'text',
	) );

	$add( 'hide_native', array( 'sanitize' => 'rest_sanitize_boolean' ), array(
		'label' => esc_html__( 'Nascondi il cursore normale del sistema', 'drkaos-wp-theme' ),
		'type'  => 'checkbox',
	) );
}
add_action( 'customize_register', 'drkaos_cursor_customize_register' );

/**
 * Il cursore va caricato?
 *
 * @return bool
 */
function drkaos_cursor_active() {
	if ( is_admin() || ! drkaos_cursor_opt( 'enable' ) || ! apply_filters( 'drkaos_cursor_enabled', true ) ) {
		return false;
	}
	// Mai dentro l'editor/anteprima di Elementor.
	if ( isset( $_GET['elementor-preview'] ) || ( class_exists( '\Elementor\Plugin' ) && \Elementor\Plugin::$instance->preview->is_preview_mode() ) ) { // phpcs:ignore WordPress.Security.NonceVerification
		return false;
	}
	return true;
}

/**
 * CSS e JS sul sito.
 */
function drkaos_cursor_enqueue() {
	if ( ! drkaos_cursor_active() ) {
		return;
	}

	wp_enqueue_style( 'drkaos-cursor', DRKAOS_THEME_URL . '/assets/css/cursor.css', array(), drkaos_asset_ver( '/assets/css/cursor.css' ) );
	wp_enqueue_script( 'drkaos-cursor', DRKAOS_THEME_URL . '/assets/js/cursor.js', array(), drkaos_asset_ver( '/assets/js/cursor.js' ), true );

	$shape  = drkaos_cursor_opt( 'shape' );
	$icons  = drkaos_cursor_icons();
	$icon   = drkaos_cursor_opt( 'icon' );
	$markup = '';
	$anchor = 'center';

	if ( 'icon' === $shape && isset( $icons[ $icon ] ) ) {
		$anchor = $icons[ $icon ][2];
		$markup = 'drkaos' === $icon
			? '<img src="' . esc_url( DRKAOS_THEME_URL . '/assets/img/icon-white.png' ) . '" alt="">'
			: $icons[ $icon ][1];
	} elseif ( 'image' === $shape && drkaos_cursor_opt( 'image' ) ) {
		$anchor = 'topleft' === drkaos_cursor_opt( 'image_anchor' ) ? 'topleft' : 'center';
		$markup = '<img src="' . esc_url( drkaos_cursor_opt( 'image' ) ) . '" alt="">';
	} elseif ( in_array( $shape, array( 'icon', 'image' ), true ) ) {
		$shape = 'dot'; // Nessuna immagine scelta: ripiega sulla pallina.
	}

	$color = sanitize_hex_color( drkaos_cursor_opt( 'color' ) );

	$config = array(
		'shape'       => $shape,
		'markup'      => $markup,
		'anchor'      => $anchor,
		'color'       => $color ? $color : '#ffffff',
		'size'        => (int) drkaos_cursor_opt( 'size' ),
		'ringSize'    => (int) drkaos_cursor_opt( 'ring_size' ),
		'hoverScale'  => (float) drkaos_cursor_opt( 'hover_scale' ),
		'smooth'      => max( 4, min( 100, (int) drkaos_cursor_opt( 'smooth' ) ) ) / 100,
		'invert'      => (bool) drkaos_cursor_opt( 'invert' ),
		'hideNative'  => (bool) drkaos_cursor_opt( 'hide_native' ),
		'growSelector' => (string) drkaos_cursor_opt( 'grow_selector' ),
	);

	wp_add_inline_script( 'drkaos-cursor', 'window.drkaosCursor = ' . drkaos_json( $config ) . ';', 'before' );
}
add_action( 'wp_enqueue_scripts', 'drkaos_cursor_enqueue' );
