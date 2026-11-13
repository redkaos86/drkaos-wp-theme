<?php
/**
 * Tipi di contenuto "Portfolio" (progetto) e "Servizi" (servizio).
 *
 * Servono a tenere pulite le pagine del sito: i lavori e i servizi si inseriscono come elementi
 * a parte e si mostrano dove si vuole (archivio del tema, Loop Grid di Elementor, ecc.).
 *
 * - Se il sito ha già un tipo con la stessa chiave (es. registrato da un tema figlio o da un plugin),
 *   il tema non lo registra di nuovo e non lo modifica.
 * - L'archivio (/portfolio/, /servizi/) si disattiva da solo se esiste già una pagina con lo stesso indirizzo.
 * - Disattivazione: add_filter( 'drkaos_register_progetto', '__return_false' ); (e/o 'drkaos_register_servizio').
 *
 * @package DrKaosWPTheme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Configurazione dei tipi di contenuto.
 *
 * @return array
 */
function drkaos_cpt_config() {
	return array(
		'progetto' => array(
			'slug'     => 'portfolio',
			'icon'     => 'dashicons-portfolio',
			'position' => 21,
			'supports' => array( 'title', 'editor', 'thumbnail', 'excerpt', 'revisions' ),
			'labels'   => array(
				'name'               => __( 'Portfolio', 'drkaos-wp-theme' ),
				'singular_name'      => __( 'Progetto', 'drkaos-wp-theme' ),
				'menu_name'          => __( 'Portfolio', 'drkaos-wp-theme' ),
				'all_items'          => __( 'Tutti i progetti', 'drkaos-wp-theme' ),
				'add_new'            => __( 'Aggiungi progetto', 'drkaos-wp-theme' ),
				'add_new_item'       => __( 'Aggiungi nuovo progetto', 'drkaos-wp-theme' ),
				'edit_item'          => __( 'Modifica progetto', 'drkaos-wp-theme' ),
				'new_item'           => __( 'Nuovo progetto', 'drkaos-wp-theme' ),
				'view_item'          => __( 'Visualizza progetto', 'drkaos-wp-theme' ),
				'search_items'       => __( 'Cerca progetti', 'drkaos-wp-theme' ),
				'not_found'          => __( 'Nessun progetto trovato.', 'drkaos-wp-theme' ),
				'not_found_in_trash' => __( 'Nessun progetto nel cestino.', 'drkaos-wp-theme' ),
			),
			'taxonomy' => array(
				'key'    => 'settore',
				'slug'   => 'settore',
				'labels' => array(
					'name'          => __( 'Settori', 'drkaos-wp-theme' ),
					'singular_name' => __( 'Settore', 'drkaos-wp-theme' ),
					'menu_name'     => __( 'Settori', 'drkaos-wp-theme' ),
					'all_items'     => __( 'Tutti i settori', 'drkaos-wp-theme' ),
					'edit_item'     => __( 'Modifica settore', 'drkaos-wp-theme' ),
					'add_new_item'  => __( 'Aggiungi nuovo settore', 'drkaos-wp-theme' ),
					'search_items'  => __( 'Cerca settori', 'drkaos-wp-theme' ),
					'not_found'     => __( 'Nessun settore trovato.', 'drkaos-wp-theme' ),
				),
			),
			'fields'   => array(
				'drkaos_cliente' => array( 'label' => __( 'Cliente', 'drkaos-wp-theme' ), 'type' => 'text', 'sanitize' => 'sanitize_text_field' ),
				'drkaos_anno'    => array( 'label' => __( 'Anno', 'drkaos-wp-theme' ), 'type' => 'text', 'sanitize' => 'drkaos_cpt_sanitize_year' ),
				'drkaos_url'     => array( 'label' => __( 'Link al progetto / sito', 'drkaos-wp-theme' ), 'type' => 'url', 'sanitize' => 'esc_url_raw' ),
			),
		),
		'servizio' => array(
			'slug'     => 'servizi',
			'icon'     => 'dashicons-star-filled',
			'position' => 22,
			'supports' => array( 'title', 'editor', 'thumbnail', 'excerpt', 'page-attributes', 'revisions' ),
			'labels'   => array(
				'name'               => __( 'Servizi', 'drkaos-wp-theme' ),
				'singular_name'      => __( 'Servizio', 'drkaos-wp-theme' ),
				'menu_name'          => __( 'Servizi', 'drkaos-wp-theme' ),
				'all_items'          => __( 'Tutti i servizi', 'drkaos-wp-theme' ),
				'add_new'            => __( 'Aggiungi servizio', 'drkaos-wp-theme' ),
				'add_new_item'       => __( 'Aggiungi nuovo servizio', 'drkaos-wp-theme' ),
				'edit_item'          => __( 'Modifica servizio', 'drkaos-wp-theme' ),
				'new_item'           => __( 'Nuovo servizio', 'drkaos-wp-theme' ),
				'view_item'          => __( 'Visualizza servizio', 'drkaos-wp-theme' ),
				'search_items'       => __( 'Cerca servizi', 'drkaos-wp-theme' ),
				'not_found'          => __( 'Nessun servizio trovato.', 'drkaos-wp-theme' ),
				'not_found_in_trash' => __( 'Nessun servizio nel cestino.', 'drkaos-wp-theme' ),
			),
			'taxonomy' => null,
			'fields'   => array(
				'drkaos_sottotitolo' => array( 'label' => __( 'Sottotitolo', 'drkaos-wp-theme' ), 'type' => 'text', 'sanitize' => 'sanitize_text_field' ),
				'drkaos_info'        => array( 'label' => __( 'Prezzo / informazioni', 'drkaos-wp-theme' ), 'placeholder' => __( 'es. a partire da 300 €', 'drkaos-wp-theme' ), 'type' => 'text', 'sanitize' => 'sanitize_text_field' ),
			),
		),
	);
}

/**
 * Anno a 4 cifre.
 *
 * @param mixed $value Valore.
 * @return string
 */
function drkaos_cpt_sanitize_year( $value ) {
	return substr( preg_replace( '/[^0-9]/', '', (string) $value ), 0, 4 );
}

/**
 * Chiavi dei tipi registrati da questo tema (non quelli già presenti da altre parti).
 *
 * @param string|null $add Chiave da aggiungere.
 * @return array
 */
function drkaos_own_cpts( $add = null ) {
	static $own = array();
	if ( null !== $add ) {
		$own[] = $add;
	}
	return $own;
}

/* ---------------------------------------------------------------------------
 * Archivio attivo o no: si spegne se esiste già una pagina con lo stesso indirizzo
 * ------------------------------------------------------------------------- */

/**
 * Esiste una pagina pubblicata con questo indirizzo?
 *
 * @param string $slug Indirizzo.
 * @return bool
 */
function drkaos_cpt_page_exists( $slug ) {
	global $wpdb;
	return (bool) $wpdb->get_var( $wpdb->prepare( "SELECT ID FROM {$wpdb->posts} WHERE post_type = 'page' AND post_status = 'publish' AND post_name = %s LIMIT 1", $slug ) ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery
}

/**
 * Calcola lo stato degli archivi.
 *
 * @return array
 */
function drkaos_cpt_compute_state() {
	$state = array( 'v' => DRKAOS_THEME_VERSION, 'archive' => array() );
	foreach ( drkaos_cpt_config() as $key => $c ) {
		$state['archive'][ $key ] = ! drkaos_cpt_page_exists( $c['slug'] );
	}
	return $state;
}

/**
 * Stato salvato; si ricalcola (e si aggiornano gli indirizzi) a ogni nuova versione del tema.
 *
 * @return array
 */
function drkaos_cpt_state() {
	$state = get_option( 'drkaos_cpt_state' );
	if ( is_array( $state ) && isset( $state['v'] ) && DRKAOS_THEME_VERSION === $state['v'] ) {
		return $state;
	}
	$state = drkaos_cpt_compute_state();
	update_option( 'drkaos_cpt_state', $state );
	$GLOBALS['drkaos_cpt_flush'] = true;
	return $state;
}

/** Aggiorna gli indirizzi dei permalink, se richiesto, dopo la registrazione. */
function drkaos_cpt_maybe_flush() {
	if ( ! empty( $GLOBALS['drkaos_cpt_flush'] ) ) {
		flush_rewrite_rules( false );
	}
}
add_action( 'wp_loaded', 'drkaos_cpt_maybe_flush', 5 );

/**
 * Quando si salva una pagina, controlla se un archivio va acceso o spento.
 *
 * @param string  $new_status Nuovo stato.
 * @param string  $old_status Vecchio stato.
 * @param WP_Post $post       Contenuto.
 */
function drkaos_cpt_on_page_change( $new_status, $old_status, $post ) {
	if ( 'page' !== $post->post_type ) {
		return;
	}
	$old = get_option( 'drkaos_cpt_state' );
	$new = drkaos_cpt_compute_state();
	if ( ! is_array( $old ) || ( isset( $old['archive'] ) && $old['archive'] !== $new['archive'] ) ) {
		update_option( 'drkaos_cpt_state', $new );
		delete_option( 'rewrite_rules' ); // Si rigenerano alla prossima richiesta.
	}
}
add_action( 'transition_post_status', 'drkaos_cpt_on_page_change', 10, 3 );

/* ---------------------------------------------------------------------------
 * Registrazione
 * ------------------------------------------------------------------------- */

/**
 * Registra tipi, tassonomie e campi.
 */
function drkaos_register_post_types() {
	$state = drkaos_cpt_state();

	foreach ( drkaos_cpt_config() as $key => $c ) {
		// Già presente (tema figlio, plugin) o disattivato: non si tocca.
		if ( ! apply_filters( "drkaos_register_{$key}", true ) || post_type_exists( $key ) ) {
			continue;
		}

		$archive = ! isset( $state['archive'][ $key ] ) || $state['archive'][ $key ];

		register_post_type(
			$key,
			array(
				'labels'              => $c['labels'],
				'public'              => true,
				'show_in_rest'        => true,
				'has_archive'         => $archive ? $c['slug'] : false,
				'rewrite'             => array( 'slug' => $c['slug'], 'with_front' => false ),
				'menu_icon'           => $c['icon'],
				'menu_position'       => $c['position'],
				'supports'            => $c['supports'],
				'exclude_from_search' => false,
			)
		);
		drkaos_own_cpts( $key );

		if ( ! empty( $c['taxonomy'] ) ) {
			$tax = $c['taxonomy'];
			if ( ! taxonomy_exists( $tax['key'] ) ) {
				register_taxonomy(
					$tax['key'],
					$key,
					array(
						'labels'            => $tax['labels'],
						'hierarchical'      => true,
						'public'            => true,
						'show_in_rest'      => true,
						'show_admin_column' => true,
						'rewrite'           => array( 'slug' => $tax['slug'], 'with_front' => false ),
					)
				);
			} else {
				register_taxonomy_for_object_type( $tax['key'], $key );
			}
		}

		foreach ( $c['fields'] as $meta_key => $f ) {
			register_post_meta(
				$key,
				$meta_key,
				array(
					'type'              => 'string',
					'single'            => true,
					'show_in_rest'      => true,
					'sanitize_callback' => $f['sanitize'],
					'auth_callback'     => function () {
						return current_user_can( 'edit_posts' );
					},
				)
			);
		}

		// Colonne nell'elenco: miniatura e campi principali.
		add_filter(
			"manage_{$key}_posts_columns",
			function ( $columns ) use ( $c ) {
				$new = array();
				foreach ( $columns as $id => $label ) {
					$new[ $id ] = $label;
					if ( 'cb' === $id ) {
						$new['drkaos_thumb'] = __( 'Immagine', 'drkaos-wp-theme' );
					}
				}
				foreach ( array_slice( $c['fields'], 0, 2, true ) as $meta_key => $f ) {
					$new[ $meta_key ] = $f['label'];
				}
				return $new;
			}
		);
		add_action(
			"manage_{$key}_posts_custom_column",
			function ( $column, $post_id ) {
				if ( 'drkaos_thumb' === $column ) {
					echo get_the_post_thumbnail( $post_id, array( 60, 60 ) ) ? get_the_post_thumbnail( $post_id, array( 60, 60 ) ) : '—'; // phpcs:ignore WordPress.Security.EscapeOutput
				} elseif ( 0 === strpos( $column, 'drkaos_' ) ) {
					$value = get_post_meta( $post_id, $column, true );
					echo '' !== $value ? esc_html( $value ) : '—';
				}
			},
			10,
			2
		);
	}
}
add_action( 'init', 'drkaos_register_post_types', 11 );

/**
 * Su un sito nuovo, Elementor propone di default anche questi tipi (si può togliere da Elementor → Impostazioni).
 *
 * @param mixed $default Valore predefinito.
 * @return array
 */
function drkaos_elementor_cpt_default( $default ) {
	return array_values( array_unique( array_merge( (array) $default, drkaos_own_cpts() ) ) );
}
add_filter( 'default_option_elementor_cpt_support', 'drkaos_elementor_cpt_default' );

/** I servizi si mostrano nell'ordine scelto (campo "Ordine" nella pagina del servizio). */
function drkaos_cpt_order_services( $query ) {
	if ( is_admin() || ! $query->is_main_query() || ! in_array( 'servizio', drkaos_own_cpts(), true ) ) {
		return;
	}
	if ( $query->is_post_type_archive( 'servizio' ) ) {
		$query->set( 'orderby', array( 'menu_order' => 'ASC', 'title' => 'ASC' ) );
	}
}
add_action( 'pre_get_posts', 'drkaos_cpt_order_services' );

/* ---------------------------------------------------------------------------
 * Campi nella pagina di modifica
 * ------------------------------------------------------------------------- */

function drkaos_cpt_add_metabox() {
	foreach ( drkaos_own_cpts() as $key ) {
		add_meta_box( 'drkaos_cpt_details', __( 'Dettagli', 'drkaos-wp-theme' ), 'drkaos_cpt_metabox_html', $key, 'normal', 'high' );
	}
}
add_action( 'add_meta_boxes', 'drkaos_cpt_add_metabox' );

/**
 * Contenuto del riquadro.
 *
 * @param WP_Post $post Contenuto.
 */
function drkaos_cpt_metabox_html( $post ) {
	$config = drkaos_cpt_config();
	if ( ! isset( $config[ $post->post_type ] ) ) {
		return;
	}
	wp_nonce_field( 'drkaos_cpt_meta', 'drkaos_cpt_nonce' );
	echo '<table class="form-table" role="presentation"><tbody>';
	foreach ( $config[ $post->post_type ]['fields'] as $meta_key => $f ) {
		printf(
			'<tr><th scope="row"><label for="%1$s">%2$s</label></th><td><input type="%3$s" class="large-text" id="%1$s" name="drkaos_f[%4$s]" value="%5$s" placeholder="%6$s"></td></tr>',
			esc_attr( 'drkaos_f_' . $meta_key ),
			esc_html( $f['label'] ),
			'url' === $f['type'] ? 'url' : 'text',
			esc_attr( $meta_key ),
			esc_attr( get_post_meta( $post->ID, $meta_key, true ) ),
			esc_attr( isset( $f['placeholder'] ) ? $f['placeholder'] : ( 'url' === $f['type'] ? 'https://' : '' ) )
		);
	}
	echo '</tbody></table>';
}

/**
 * Salvataggio.
 *
 * @param int $post_id ID contenuto.
 */
function drkaos_cpt_save_meta( $post_id ) {
	if ( ! isset( $_POST['drkaos_cpt_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['drkaos_cpt_nonce'] ) ), 'drkaos_cpt_meta' ) ) {
		return;
	}
	if ( ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) || wp_is_post_revision( $post_id ) || ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}

	$config = drkaos_cpt_config();
	$type   = get_post_type( $post_id );
	if ( ! isset( $config[ $type ] ) || ! in_array( $type, drkaos_own_cpts(), true ) ) {
		return;
	}

	$raw = isset( $_POST['drkaos_f'] ) && is_array( $_POST['drkaos_f'] ) ? wp_unslash( $_POST['drkaos_f'] ) : array(); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput

	foreach ( $config[ $type ]['fields'] as $meta_key => $f ) {
		if ( ! isset( $raw[ $meta_key ] ) || ! is_string( $raw[ $meta_key ] ) ) {
			continue;
		}
		$value = call_user_func( $f['sanitize'], $raw[ $meta_key ] );
		if ( '' === $value ) {
			delete_post_meta( $post_id, $meta_key );
		} else {
			update_post_meta( $post_id, $meta_key, $value );
		}
	}
}
add_action( 'save_post', 'drkaos_cpt_save_meta' );

/* ---------------------------------------------------------------------------
 * Visualizzazione nel tema (si può sostituire con i template di Elementor)
 * ------------------------------------------------------------------------- */

/**
 * Sottotitolo del servizio.
 *
 * @param int $post_id ID contenuto.
 * @return string HTML già escapato.
 */
function drkaos_cpt_subtitle( $post_id ) {
	if ( ! in_array( get_post_type( $post_id ), drkaos_own_cpts(), true ) ) {
		return '';
	}
	$text = get_post_meta( $post_id, 'drkaos_sottotitolo', true );
	return $text ? '<p class="entry-subtitle">' . esc_html( $text ) . '</p>' : '';
}

/**
 * Elenco dei dettagli (cliente, anno, link, prezzo).
 *
 * @param int $post_id ID contenuto.
 * @return string HTML già escapato.
 */
function drkaos_cpt_details( $post_id ) {
	$type   = get_post_type( $post_id );
	$config = drkaos_cpt_config();
	if ( ! isset( $config[ $type ] ) || ! in_array( $type, drkaos_own_cpts(), true ) ) {
		return '';
	}

	$items = '';
	foreach ( $config[ $type ]['fields'] as $meta_key => $f ) {
		if ( 'drkaos_sottotitolo' === $meta_key ) {
			continue;
		}
		$value = get_post_meta( $post_id, $meta_key, true );
		if ( '' === $value ) {
			continue;
		}
		if ( 'url' === $f['type'] ) {
			$host  = wp_parse_url( $value, PHP_URL_HOST );
			$value = '<a href="' . esc_url( $value ) . '" target="_blank" rel="noopener">' . esc_html( $host ? $host : $value ) . '</a>';
		} else {
			$value = esc_html( $value );
		}
		$items .= '<dt>' . esc_html( $f['label'] ) . '</dt><dd>' . $value . '</dd>';
	}

	return $items ? '<dl class="drkaos-details">' . $items . '</dl>' : '';
}
