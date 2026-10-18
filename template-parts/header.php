<?php
/**
 * Header di default (usato solo se Elementor Pro non definisce un header)
 *
 * @package DrKaosWPTheme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<header id="site-header" class="site-header">
	<div class="site-branding">
		<?php if ( has_custom_logo() ) : ?>
			<div class="site-logo"><?php the_custom_logo(); ?></div>
		<?php else : ?>
			<p class="site-title">
				<a href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home"><?php bloginfo( 'name' ); ?></a>
			</p>
			<?php if ( get_bloginfo( 'description' ) ) : ?>
				<p class="site-description"><?php bloginfo( 'description' ); ?></p>
			<?php endif; ?>
		<?php endif; ?>
	</div>

	<?php if ( has_nav_menu( 'menu-1' ) ) : ?>
		<nav class="site-navigation" aria-label="<?php esc_attr_e( 'Menu principale', 'drkaos-wp-theme' ); ?>">
			<?php
			wp_nav_menu(
				array(
					'theme_location' => 'menu-1',
					'container'      => false,
					'menu_class'     => 'menu',
				)
			);
			?>
		</nav>
	<?php endif; ?>
</header>
