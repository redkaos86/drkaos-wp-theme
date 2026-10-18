<?php
/**
 * Contenuto risultati di ricerca
 *
 * @package DrKaosWPTheme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<header class="page-header">
	<h1 class="entry-title">
		<?php
		/* translators: %s: query di ricerca */
		printf( esc_html__( 'Risultati per: %s', 'drkaos-wp-theme' ), '<span>' . esc_html( get_search_query() ) . '</span>' );
		?>
	</h1>
</header>

<div class="page-content">
	<?php if ( have_posts() ) : ?>
		<?php while ( have_posts() ) : the_post(); ?>
			<article id="post-<?php the_ID(); ?>" <?php post_class( 'post' ); ?>>
				<?php the_title( sprintf( '<h2 class="entry-title"><a href="%s">', esc_url( get_permalink() ) ), '</a></h2>' ); ?>
				<?php the_excerpt(); ?>
			</article>
		<?php endwhile; ?>
		<?php the_posts_pagination(); ?>
	<?php else : ?>
		<p><?php esc_html_e( 'Nessun risultato. Prova con altre parole chiave.', 'drkaos-wp-theme' ); ?></p>
		<?php get_search_form(); ?>
	<?php endif; ?>
</div>
