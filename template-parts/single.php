<?php
/**
 * Contenuto articolo singolo / progetto / servizio
 *
 * Se il contenuto è costruito con Elementor, il tema non stampa titolo, immagine e dettagli:
 * il design è tutto in Elementor.
 *
 * @package DrKaosWPTheme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<?php while ( have_posts() ) : the_post(); ?>
	<?php $drkaos_built = drkaos_is_elementor_built( get_the_ID() ); ?>
	<article id="post-<?php the_ID(); ?>" <?php post_class( 'site-single' ); ?>>
		<?php if ( ! $drkaos_built && apply_filters( 'drkaos_page_title', true ) ) : ?>
			<header class="page-header">
				<?php the_title( '<h1 class="entry-title">', '</h1>' ); ?>
				<?php echo drkaos_cpt_subtitle( get_the_ID() ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
			</header>
		<?php endif; ?>

		<?php if ( ! $drkaos_built && has_post_thumbnail() ) : ?>
			<div class="post-thumbnail"><?php the_post_thumbnail( 'large' ); ?></div>
		<?php endif; ?>

		<div class="page-content">
			<?php
			the_content();
			wp_link_pages(
				array(
					'before' => '<nav class="page-links">',
					'after'  => '</nav>',
				)
			);
			?>
		</div>

		<?php if ( ! $drkaos_built ) : ?>
			<?php echo drkaos_cpt_details( get_the_ID() ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
		<?php endif; ?>

		<footer class="entry-footer">
			<?php
			if ( 'post' === get_post_type() ) {
				the_tags( '<div class="post-tags">', ' ', '</div>' );
			}
			?>
		</footer>

		<?php the_post_navigation(); ?>

		<?php if ( comments_open() || get_comments_number() ) : ?>
			<?php comments_template(); ?>
		<?php endif; ?>
	</article>
<?php endwhile; ?>
