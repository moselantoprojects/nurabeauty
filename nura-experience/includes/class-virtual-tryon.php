<?php
/**
 * Virtual Try-On.
 *
 * Lets a shopper upload a photo and preview a wig image over it on a canvas
 * (drag to position, slider to scale, opacity match). This client-side MVP works
 * with any product that has a transparent-PNG try-on image. For production-grade,
 * auto-aligned try-on, attach a face-landmark provider via the 'nurax_tryon_provider'
 * JS hook (documented in the plugin readme) - the UI stays the same.
 *
 * @package NURA_Experience
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class NURAX_Virtual_TryOn {

	public function __construct() {
		add_shortcode( 'nura_virtual_tryon', array( $this, 'render' ) );
		// Try-On button on single product pages.
		add_action( 'woocommerce_after_add_to_cart_button', array( $this, 'product_button' ) );
	}

	public function render( $atts ) {
		$atts = shortcode_atts( array( 'product' => 0 ), $atts );
		$overlay = '';
		if ( $atts['product'] ) {
			$overlay = wp_get_attachment_image_url( get_post_thumbnail_id( absint( $atts['product'] ) ), 'nura-portrait' );
		}
		ob_start(); ?>
		<div class="nurax-tryon" data-nurax-tryon data-overlay="<?php echo esc_url( $overlay ); ?>">
			<div class="nurax-tryon__stage">
				<canvas data-nurax-canvas width="600" height="750"></canvas>
				<p class="nurax-tryon__hint"><?php esc_html_e( 'Upload a front-facing photo to begin', 'nura-experience' ); ?></p>
			</div>
			<div class="nurax-tryon__controls">
				<label class="nura-btn"><?php esc_html_e( 'Upload photo', 'nura-experience' ); ?><input type="file" accept="image/*" data-nurax-photo hidden></label>
				<label><?php esc_html_e( 'Size', 'nura-experience' ); ?> <input type="range" min="20" max="200" value="100" data-nurax-scale></label>
				<label><?php esc_html_e( 'Blend', 'nura-experience' ); ?> <input type="range" min="40" max="100" value="100" data-nurax-opacity></label>
				<button class="nura-btn nura-btn--ghost" data-nurax-reset><?php esc_html_e( 'Reset', 'nura-experience' ); ?></button>
			</div>
			<p><small><?php esc_html_e( 'Drag the wig to position it. Your photo stays in your browser and is never uploaded or shared.', 'nura-experience' ); ?></small></p>
		</div>
		<?php
		return ob_get_clean();
	}

	/**
	 * "Try it on" button under add-to-cart, linking to the Virtual Try-On page with this product.
	 */
	public function product_button() {
		global $product;
		if ( ! $product ) {
			return;
		}
		$page = get_page_by_path( 'virtual-try-on' );
		if ( ! $page ) {
			return;
		}
		$url = add_query_arg( 'product', $product->get_id(), get_permalink( $page->ID ) );
		printf(
			'<a class="nura-btn nura-btn--ghost nurax-tryon-btn" href="%s">%s</a>',
			esc_url( $url ),
			esc_html__( 'Try it on', 'nura-experience' )
		);
	}
}
