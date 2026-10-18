<?php
/**
 * Contenuto archivio / elenco articoli
 *
 * @package DrKaosWPTheme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<?php if ( have_posts() ) : ?>
	<header class="page-header">
		<?php
		the_archive_title( '<h1 class="entry-title">', '</h1>' );
		the_archive_description( '<div class="archive-description">', '</div>' );
		?>
	</header>

	<div class="page-content">
		<?php while ( have_posts() ) : the_post(); ?>
			<article id="post-<?php the_ID(); ?>" <?php post_class( 'post' ); ?>>
				<?php if ( has_post_thumbnail() ) : ?>
					<a class="post-thumbnail" href="<?php the_permalink(); ?>"><?php the_post_thumbnail( 'large' ); ?></a>
				<?php endif; ?>
				<?php the_title( sprintf( '<h2 class="entry-title"><a href="%s">', esc_url( get_permalink() ) ), '</a></h2>' ); ?>
				<?php the_excerpt(); ?>
			</article>
		<?php endwhile; ?>
	</div>

	<?php the_posts_pagination(); ?>
<?php else : ?>
	<div class="page-content">
		<p><?php esc_html_e( 'Nessun contenuto trovato.', 'drkaos-wp-theme' ); ?></p>
	</div>
<?php endif; ?>
