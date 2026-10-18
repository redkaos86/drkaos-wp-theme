<?php
/**
 * Template 404
 *
 * @package DrKaosWPTheme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();
drkaos_render_content( 'single', '404' );
get_footer();
