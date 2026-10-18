<?php
/**
 * Attivazione/disattivazione di header e footer del tema.
 * Globale (Customizer) e per singolo contenuto (metabox).
 *
 * @package DrKaosWPTheme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Header/footer del tema sono attivi?
 *
 * Ordine di priorità: opzione per singolo contenuto > opzione globale > filtro.
 *
 * @param string $part 'header' o 'footer'.
 * @return bool
 */
function drkaos_part_enabled( $part ) {
	$enabled = ! get_theme_mod( 'drkaos_disable_' . $part, false );

	if ( is_singular() ) {
		$override = get_post_meta( get_queried_object_id(), '_drkaos_hide_' . $part, true );
		if ( '1' === $override ) {
			$enabled = false;
		}
	}

	return (bool) apply_filters( 'drkaos_enable_' . $part, $enabled );
}

/**
 * Customizer: interruttori globali.
 *
 * @param WP_Customize_Manager $wp_customize Customizer.
 */
function drkaos_layout_customize_register( $wp_customize ) {
	$wp_customize->add_section(
		'drkaos_layout',
		array(
			'title'       => esc_html__( 'Header e footer del tema', 'drkaos-wp-theme' ),
			'description' => esc_html__( 'Disattivali se usi un header/footer creato con Elementor o con un altro plugin.', 'drkaos-wp-theme' ),
			'priority'    => 31,
		)
	);

	$controls = array(
		'drkaos_disable_header' => esc_html__( 'Disattiva header del tema', 'drkaos-wp-theme' ),
		'drkaos_disable_footer' => esc_html__( 'Disattiva footer del tema', 'drkaos-wp-theme' ),
	);

	foreach ( $controls as $id => $label ) {
		$wp_customize->add_setting(
			$id,
			array(
				'default'           => false,
				'sanitize_callback' => 'rest_sanitize_boolean',
			)
		);
		$wp_customize->add_control(
			$id,
			array(
				'label'   => $label,
				'section' => 'drkaos_layout',
				'type'    => 'checkbox',
			)
		);
	}
}
add_action( 'customize_register', 'drkaos_layout_customize_register' );

/**
 * Metabox per pagina/articolo.
 */
function drkaos_add_layout_metabox() {
	$post_types = get_post_types( array( 'public' => true ) );
	unset( $post_types['attachment'] );

	foreach ( $post_types as $post_type ) {
		add_meta_box(
			'drkaos_layout',
			esc_html__( 'DR.KAOS: header e footer', 'drkaos-wp-theme' ),
			'drkaos_layout_metabox_html',
			$post_type,
			'side',
			'default'
		);
	}
}
add_action( 'add_meta_boxes', 'drkaos_add_layout_metabox' );

/**
 * HTML del metabox.
 *
 * @param WP_Post $post Contenuto corrente.
 */
function drkaos_layout_metabox_html( $post ) {
	wp_nonce_field( 'drkaos_layout_save', 'drkaos_layout_nonce' );
	?>
	<p>
		<label>
			<input type="checkbox" name="drkaos_hide_header" value="1" <?php checked( get_post_meta( $post->ID, '_drkaos_hide_header', true ), '1' ); ?>>
			<?php esc_html_e( 'Nascondi header del tema', 'drkaos-wp-theme' ); ?>
		</label>
	</p>
	<p>
		<label>
			<input type="checkbox" name="drkaos_hide_footer" value="1" <?php checked( get_post_meta( $post->ID, '_drkaos_hide_footer', true ), '1' ); ?>>
			<?php esc_html_e( 'Nascondi footer del tema', 'drkaos-wp-theme' ); ?>
		</label>
	</p>
	<?php
}

/**
 * Salvataggio del metabox.
 *
 * @param int $post_id ID contenuto.
 */
function drkaos_save_layout_metabox( $post_id ) {
	if ( ! isset( $_POST['drkaos_layout_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['drkaos_layout_nonce'] ) ), 'drkaos_layout_save' ) ) {
		return;
	}
	if ( ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) || wp_is_post_revision( $post_id ) ) {
		return;
	}
	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}

	foreach ( array( 'header', 'footer' ) as $part ) {
		if ( ! empty( $_POST[ 'drkaos_hide_' . $part ] ) ) {
			update_post_meta( $post_id, '_drkaos_hide_' . $part, '1' );
		} else {
			delete_post_meta( $post_id, '_drkaos_hide_' . $part );
		}
	}
}
add_action( 'save_post', 'drkaos_save_layout_metabox' );
