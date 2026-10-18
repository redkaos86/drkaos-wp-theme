<?php
/**
 * Template risultati di ricerca
 *
 * @package DrKaosWPTheme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();
drkaos_render_content( 'archive', 'search' );
get_footer();
