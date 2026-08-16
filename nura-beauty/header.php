<?php
/**
 * Header.
 * @package NURA_Beauty
 */
?><!doctype html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<link rel="profile" href="https://gmpg.org/xfn/11">
	<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<a class="skip-link screen-reader-text" href="#primary"><?php esc_html_e( 'Skip to content', 'nura-beauty' ); ?></a>

<?php nura_announcement_bar(); ?>

<header class="site-header" id="site-header">
	<div class="nura-container nura-header-inner">
		<button class="nura-icon-btn nura-burger" aria-label="<?php esc_attr_e( 'Open menu', 'nura-beauty' ); ?>" data-nura-drawer><span aria-hidden="true">&#9776;</span></button>

		<div class="nura-logo-wrap">
			<?php
			if ( has_custom_logo() ) {
				the_custom_logo();
			} else {
				printf(
					'<a class="nura-logo" href="%s">%s</a>',
					esc_url( home_url( '/' ) ),
					esc_html( nura_opt( 'nura_brand_name' ) )
				);
			}
			?>
		</div>

		<nav class="nura-nav" aria-label="<?php esc_attr_e( 'Primary', 'nura-beauty' ); ?>">
			<?php
			wp_nav_menu( array(
				'theme_location' => 'primary',
				'container'      => false,
				'fallback_cb'    => 'nura_default_menu',
				'depth'          => 2,
			) );
			?>
		</nav>

		<div class="nura-header-actions">
			<a class="nura-icon-btn nura-search-txt" href="<?php echo esc_url( home_url( '/?s=' ) ); ?>" aria-label="<?php esc_attr_e( 'Search', 'nura-beauty' ); ?>"><?php esc_html_e( 'Search', 'nura-beauty' ); ?></a>
			<?php if ( function_exists( 'nura_cart_button' ) ) { nura_cart_button(); } ?>
		</div>
	</div>
</header>

<!-- Mobile drawer -->
<div class="nura-overlay" data-nura-overlay></div>
<aside class="nura-drawer" data-nura-drawer-panel aria-hidden="true">
	<a class="nura-logo" href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php echo esc_html( nura_opt( 'nura_brand_name' ) ); ?></a>
	<?php
	wp_nav_menu( array(
		'theme_location' => 'mobile',
		'container'      => false,
		'fallback_cb'    => 'nura_default_menu',
	) );
	?>
	<a class="nura-btn nura-btn--gold" style="width:100%" href="<?php echo esc_url( nura_opt( 'nura_whatsapp' ) ); ?>"><?php esc_html_e( 'Chat on WhatsApp', 'nura-beauty' ); ?></a>
</aside>
<?php
/**
 * Simple fallback menu so the header is never empty before menus are assigned.
 */
function nura_default_menu() {
	echo '<ul>';
	echo '<li><a href="' . esc_url( home_url( '/' ) ) . '">' . esc_html__( 'Home', 'nura-beauty' ) . '</a></li>';
	if ( function_exists( 'wc_get_page_id' ) ) {
		echo '<li><a href="' . esc_url( get_permalink( wc_get_page_id( 'shop' ) ) ) . '">' . esc_html__( 'Shop', 'nura-beauty' ) . '</a></li>';
	}
	echo '<li><a href="' . esc_url( home_url( '/about-us/' ) ) . '">' . esc_html__( 'About', 'nura-beauty' ) . '</a></li>';
	echo '<li><a href="' . esc_url( home_url( '/contact-us/' ) ) . '">' . esc_html__( 'Contact', 'nura-beauty' ) . '</a></li>';
	echo '</ul>';
}
