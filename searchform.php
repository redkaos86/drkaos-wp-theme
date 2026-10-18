<?php
/**
 * Form di ricerca
 *
 * @package DrKaosWPTheme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<form role="search" method="get" class="search-form" action="<?php echo esc_url( home_url( '/' ) ); ?>">
	<label>
		<span class="screen-reader-text"><?php esc_html_e( 'Cerca:', 'drkaos-wp-theme' ); ?></span>
		<input type="search" class="search-field" placeholder="<?php esc_attr_e( 'Cerca…', 'drkaos-wp-theme' ); ?>" value="<?php echo esc_attr( get_search_query() ); ?>" name="s">
	</label>
	<button type="submit" class="search-submit"><?php esc_html_e( 'Cerca', 'drkaos-wp-theme' ); ?></button>
</form>
