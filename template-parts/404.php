<?php
/**
 * Contenuto 404
 *
 * @package DrKaosWPTheme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class="site-404">
	<header class="page-header">
		<h1 class="entry-title"><?php esc_html_e( 'Pagina non trovata', 'drkaos-wp-theme' ); ?></h1>
	</header>
	<div class="page-content">
		<p><?php esc_html_e( 'Sembra che non ci sia nulla a questo indirizzo. Prova a cercare.', 'drkaos-wp-theme' ); ?></p>
		<?php get_search_form(); ?>
	</div>
</div>
