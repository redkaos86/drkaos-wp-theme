<?php
/**
 * Template archivi
 *
 * @package DrKaosWPTheme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();
drkaos_render_content( 'archive', 'archive' );
get_footer();
