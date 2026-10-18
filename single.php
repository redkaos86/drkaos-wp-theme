<?php
/**
 * Template articolo singolo / custom post type
 *
 * @package DrKaosWPTheme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();
drkaos_render_content( 'single', 'single' );
get_footer();
