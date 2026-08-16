<?php
/**
 * Reusable presentation helpers.
 *
 * @package NURA_Beauty
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Accessible, schema-aware breadcrumb.
 */
function nura_breadcrumbs() {
	if ( is_front_page() ) {
		return;
	}
	$crumbs = array( array( 'name' => __( 'Home', 'nura-beauty' ), 'url' => home_url( '/' ) ) );

	if ( function_exists( 'is_shop' ) && is_shop() ) {
		$crumbs[] = array( 'name' => __( 'Shop', 'nura-beauty' ), 'url' => get_permalink( wc_get_page_id( 'shop' ) ) );
	} elseif ( is_singular( 'product' ) ) {
		$crumbs[] = array( 'name' => __( 'Shop', 'nura-beauty' ), 'url' => get_permalink( wc_get_page_id( 'shop' ) ) );
		$crumbs[] = array( 'name' => get_the_title(), 'url' => get_permalink() );
	} elseif ( is_singular() ) {
		$crumbs[] = array( 'name' => get_the_title(), 'url' => get_permalink() );
	} elseif ( is_category() || is_tax() ) {
		$crumbs[] = array( 'name' => single_term_title( '', false ), 'url' => '' );
	} elseif ( is_search() ) {
		$crumbs[] = array( 'name' => __( 'Search results', 'nura-beauty' ), 'url' => '' );
	}

	echo '<nav class="nura-breadcrumbs" aria-label="' . esc_attr__( 'Breadcrumb', 'nura-beauty' ) . '"><ol>';
	$last = count( $crumbs ) - 1;
	foreach ( $crumbs as $i => $c ) {
		if ( $i === $last || empty( $c['url'] ) ) {
			echo '<li aria-current="page">' . esc_html( $c['name'] ) . '</li>';
		} else {
			echo '<li><a href="' . esc_url( $c['url'] ) . '">' . esc_html( $c['name'] ) . '</a></li>';
		}
	}
	echo '</ol></nav>';

	if ( function_exists( 'nura_breadcrumb_schema' ) ) {
		nura_breadcrumb_schema( $crumbs );
	}
}

/**
 * Mini floating cart button (used in header + as a floating action).
 */
function nura_cart_button() {
	if ( ! class_exists( 'WooCommerce' ) ) {
		return;
	}
	$count = WC()->cart ? WC()->cart->get_cart_contents_count() : 0;
	printf(
		'<a class="nura-cart-btn" href="%1$s" aria-label="%2$s"><span class="nura-cart-icon" aria-hidden="true"></span><span class="nura-cart-count" data-count="%3$d">%3$d</span></a>',
		esc_url( wc_get_cart_url() ),
		esc_attr__( 'View cart', 'nura-beauty' ),
		absint( $count )
	);
}

/**
 * Announcement bar (edit text in the Customizer).
 */
function nura_announcement_bar() {
	$text = get_theme_mod( 'nura_announcement', __( 'Free same-day delivery in Nairobi on orders over KES 10,000. Pay with M-Pesa on delivery.', 'nura-beauty' ) );
	if ( ! $text ) {
		return;
	}
	echo '<div class="nura-announce"><div class="nura-container"><p>' . wp_kses_post( $text ) . '</p></div></div>';
}

/**
 * Fallback collection-tile image, used when a product category has no thumbnail.
 * Keyword-matched so it is meaningful, and overridden the moment a real category
 * image is set under Products -> Categories.
 */
function nura_cat_fallback_image( $name ) {
	$b = NURA_URI . 'assets/images/cats/';
	$n = strtolower( (string) $name );
	$map = array(
		'bridal'=>'bridal-occasion.jpg','occasion'=>'bridal-occasion.jpg','wedding'=>'bridal-occasion.jpg',
		'lace'=>'lace-front-hd.jpg','hd'=>'lace-front-hd.jpg','closure'=>'lace-front-hd.jpg','frontal'=>'lace-front-hd.jpg',
		'ready'=>'ready-to-wear.jpg','glueless'=>'ready-to-wear.jpg','headband'=>'ready-to-wear.jpg','everyday'=>'ready-to-wear.jpg',
		'confidence'=>'confidence-line.jpg','signature'=>'confidence-line.jpg',
	);
	foreach ( $map as $needle => $file ) {
		if ( false !== strpos( $n, $needle ) ) { return $b . $file; }
	}
	return $b . 'wigs.jpg';
}


if ( function_exists( 'nura_rail' ) === false ) {
	/**
	 * Output a horizontal product rail (heading row + scrollable WooCommerce products).
	 */
	function nura_rail( $eyebrow, $title, $shortcode, $view_all_url = '', $view_all_label = '' ) {
		if ( class_exists( 'WooCommerce' ) === false ) { return; }
		echo '<section class="section nura-rail-section"><div class="nura-container">';
		echo '<div class="nura-shead nura-shead--row nura-reveal"><div>';
		if ( $eyebrow ) { echo '<p class="nura-eyebrow">' . esc_html( $eyebrow ) . '</p>'; }
		echo '<h2>' . esc_html( $title ) . '</h2></div>';
		if ( $view_all_url ) {
			$label = $view_all_label ? $view_all_label : __( 'View all', 'nura-beauty' );
			echo '<a class="nura-rail__viewall" href="' . esc_url( $view_all_url ) . '">' . esc_html( $label ) . ' &#8594;</a>';
		}
		echo '</div>';
		echo '<div class="nura-rail nura-reveal" data-nura-rail>';
		echo '<button type="button" class="nura-rail__nav nura-rail__nav--prev" data-rail-prev aria-label="' . esc_attr__( 'Scroll left', 'nura-beauty' ) . '">&#8249;</button>';
		echo '<div class="nura-rail__track">' . do_shortcode( $shortcode ) . '</div>';
		echo '<button type="button" class="nura-rail__nav nura-rail__nav--next" data-rail-next aria-label="' . esc_attr__( 'Scroll right', 'nura-beauty' ) . '">&#8250;</button>';
		echo '</div></div></section>';
	}
}
