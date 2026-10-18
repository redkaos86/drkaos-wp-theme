<?php
/**
 * Template pagina
 *
 * @package DrKaosWPTheme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();
drkaos_render_content( 'single', 'page' );
get_footer();
