<?php
/**
 * Footer di default (usato solo se Elementor Pro non definisce un footer)
 *
 * @package DrKaosWPTheme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<footer id="site-footer" class="site-footer">
	<?php if ( has_nav_menu( 'menu-2' ) ) : ?>
		<nav class="footer-navigation" aria-label="<?php esc_attr_e( 'Menu footer', 'drkaos-wp-theme' ); ?>">
			<?php
			wp_nav_menu(
				array(
					'theme_location' => 'menu-2',
					'container'      => false,
					'menu_class'     => 'menu',
					'depth'          => 1,
				)
			);
			?>
		</nav>
	<?php endif; ?>
	<p class="copyright">
		<?php
		$drkaos_credit = get_theme_mod( 'drkaos_footer_credit', '' );
		echo $drkaos_credit
			? esc_html( $drkaos_credit )
			: '&copy; ' . esc_html( gmdate( 'Y' ) ) . ' ' . esc_html( get_bloginfo( 'name' ) );
		?>
	</p>
</footer>
