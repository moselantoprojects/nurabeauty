<?php
/**
 * WooCommerce integration: layout, badges, product loop tuning, cart fragments.
 *
 * @package NURA_Beauty
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Products per page and columns tuned for a luxury 3-up grid.
add_filter( 'loop_shop_per_page', function () { return 12; }, 20 );
add_filter( 'loop_shop_columns', function () { return 3; }, 20 );
add_filter( 'woocommerce_related_products_args', function ( $args ) {
	$args['posts_per_page'] = 3;
	$args['columns']        = 3;
	return $args;
} );

// Remove the default WooCommerce wrappers so we control markup.
remove_action( 'woocommerce_before_main_content', 'woocommerce_output_content_wrapper', 10 );
remove_action( 'woocommerce_after_main_content', 'woocommerce_output_content_wrapper_end', 10 );

add_action( 'woocommerce_before_main_content', function () {
	echo '<main id="primary" class="site-main woo-main"><div class="nura-container">';
}, 10 );
add_action( 'woocommerce_after_main_content', function () {
	echo '</div></main>';
}, 10 );

// Full-width shop & product pages: remove the default WooCommerce sidebar
// (the leftover Search / Recent Posts / Recent Comments widget column).
remove_action( 'woocommerce_sidebar', 'woocommerce_get_sidebar', 10 );
add_filter( 'body_class', function ( $classes ) {
	if ( function_exists( 'is_woocommerce' ) && ( is_woocommerce() || is_cart() || is_checkout() || is_account_page() ) ) {
		$classes[] = 'nura-woo-fullwidth';
	}
	return $classes;
} );

// Add a sale / new badge on product cards.
add_action( 'woocommerce_before_shop_loop_item_title', 'nura_product_badges', 9 );
function nura_product_badges() {
	global $product;
	if ( ! $product ) {
		return;
	}
	echo '<div class="nura-badges">';
	if ( $product->is_on_sale() ) {
		echo '<span class="nura-badge nura-badge--sale">' . esc_html__( 'Sale', 'nura-beauty' ) . '</span>';
	}
	$created = strtotime( get_the_date( 'c', $product->get_id() ) );
	if ( $created && ( time() - $created ) < ( 30 * DAY_IN_SECONDS ) ) {
		echo '<span class="nura-badge nura-badge--new">' . esc_html__( 'New', 'nura-beauty' ) . '</span>';
	}
	echo '</div>';
}

/**
 * Sticky "Add to cart" bar data on single product (rendered by JS in main.js).
 */
add_action( 'woocommerce_after_single_product_summary', 'nura_sticky_atc', 5 );
function nura_sticky_atc() {
	global $product;
	if ( ! $product ) {
		return;
	}
	printf(
		'<div class="nura-sticky-atc" data-product="%1$d" aria-hidden="true"><span class="nura-sticky-atc__title">%2$s</span><span class="nura-sticky-atc__price">%3$s</span></div>',
		absint( $product->get_id() ),
		esc_html( $product->get_name() ),
		wp_kses_post( $product->get_price_html() )
	);
}

// Refresh the floating mini-cart count without a reload.
add_filter( 'woocommerce_add_to_cart_fragments', function ( $fragments ) {
	$count = WC()->cart ? WC()->cart->get_cart_contents_count() : 0;
	ob_start();
	?>
	<span class="nura-cart-count" data-count="<?php echo esc_attr( $count ); ?>"><?php echo esc_html( $count ); ?></span>
	<?php
	$fragments['.nura-cart-count'] = ob_get_clean();
	return $fragments;
} );

/**
 * Trust strip under the add-to-cart button (KE-specific reassurance).
 */
add_action( 'woocommerce_single_product_summary', 'nura_trust_strip', 35 );
function nura_trust_strip() {
	$items = array(
		__( '100% human hair, quality guaranteed', 'nura-beauty' ),
		__( 'Same-day delivery in Nairobi', 'nura-beauty' ),
		__( 'Pay with M-Pesa, card or on delivery', 'nura-beauty' ),
		__( 'Free virtual wig consultation', 'nura-beauty' ),
	);
	echo '<ul class="nura-trust">';
	foreach ( $items as $item ) {
		echo '<li>' . esc_html( $item ) . '</li>';
	}
	echo '</ul>';
}
