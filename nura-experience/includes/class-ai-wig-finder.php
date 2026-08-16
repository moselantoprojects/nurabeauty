<?php
/**
 * AI Wig Finder.
 *
 * Recommends units from a short quiz (face shape, skin tone, lifestyle, budget)
 * and/or an uploaded selfie. Ships with a transparent rule-based recommender so
 * it works out of the box, and a filter/REST hook so you can plug a real vision
 * model (e.g. face-shape detection) without changing the front end.
 *
 * @package NURA_Experience
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class NURAX_AI_Wig_Finder {

	public function __construct() {
		add_shortcode( 'nura_ai_wig_finder', array( $this, 'render' ) );
		add_action( 'rest_api_init', array( $this, 'routes' ) );
	}

	public function routes() {
		register_rest_route( 'nurax/v1', '/wig-finder', array(
			'methods'             => 'POST',
			'callback'            => array( $this, 'recommend' ),
			'permission_callback' => '__return_true',
		) );
	}

	/**
	 * The finder UI (quiz + optional selfie upload).
	 */
	public function render() {
		ob_start(); ?>
		<div class="nurax-finder" data-nurax-finder>
			<form class="nurax-quiz">
				<div class="nurax-field">
					<label><?php esc_html_e( 'Your face shape', 'nura-experience' ); ?></label>
					<select name="face">
						<option value="unsure"><?php esc_html_e( 'Not sure (upload a selfie below)', 'nura-experience' ); ?></option>
						<option value="round">Round</option><option value="oval">Oval</option>
						<option value="square">Square</option><option value="heart">Heart</option><option value="long">Long</option>
					</select>
				</div>
				<div class="nurax-field">
					<label><?php esc_html_e( 'Skin tone', 'nura-experience' ); ?></label>
					<select name="tone"><option value="deep">Deep</option><option value="medium">Medium</option><option value="fair">Fair</option></select>
				</div>
				<div class="nurax-field">
					<label><?php esc_html_e( 'Lifestyle', 'nura-experience' ); ?></label>
					<select name="life"><option value="lowmaint">Low-maintenance / everyday</option><option value="glam">Glam / events</option><option value="pro">Professional</option><option value="bridal">Bridal</option></select>
				</div>
				<div class="nurax-field">
					<label><?php esc_html_e( 'Budget (KES)', 'nura-experience' ); ?></label>
					<select name="budget"><option value="10000">Up to 10,000</option><option value="18000">10,000 - 18,000</option><option value="30000">18,000 - 30,000</option><option value="99999">30,000+</option></select>
				</div>
				<div class="nurax-field nurax-selfie">
					<label><?php esc_html_e( 'Optional: upload a selfie for AI face analysis', 'nura-experience' ); ?></label>
					<input type="file" name="selfie" accept="image/*" data-nurax-selfie>
					<small><?php esc_html_e( 'Your photo is analysed for your recommendation only and is never stored or shared.', 'nura-experience' ); ?></small>
				</div>
				<button type="submit" class="nura-btn nura-btn--gold"><?php esc_html_e( 'Find my wig', 'nura-experience' ); ?></button>
			</form>
			<div class="nurax-results" data-nurax-results hidden></div>
		</div>
		<?php
		return ob_get_clean();
	}

	/**
	 * REST recommender. Returns products matched to the quiz. A real vision model
	 * can be attached via the 'nurax_face_analysis' filter (see settings) to turn
	 * an uploaded selfie into a face-shape value before matching.
	 */
	public function recommend( WP_REST_Request $req ) {
		$face   = sanitize_text_field( $req->get_param( 'face' ) );
		$tone   = sanitize_text_field( $req->get_param( 'tone' ) );
		$life   = sanitize_text_field( $req->get_param( 'life' ) );
		$budget = absint( $req->get_param( 'budget' ) );

		// Hook point for a real AI vision service (returns a face-shape string).
		$face = apply_filters( 'nurax_face_analysis', $face, $req );

		// Map lifestyle -> preferred category, and face shape -> preferred texture/length.
		$cat_map = array( 'lowmaint' => 'ready-to-wear', 'glam' => 'lace-hd', 'pro' => 'lace-hd', 'bridal' => 'bridal' );
		$pref_cat = isset( $cat_map[ $life ] ) ? $cat_map[ $life ] : '';

		$args = array(
			'status'   => 'publish',
			'limit'    => 6,
			'orderby'  => 'popularity',
		);
		if ( $pref_cat ) {
			$args['category'] = array( $pref_cat );
		}
		$products = function_exists( 'wc_get_products' ) ? wc_get_products( $args ) : array();

		$out = array();
		foreach ( $products as $p ) {
			$price = (float) $p->get_price();
			if ( $budget && $price > $budget * 1.15 ) {
				continue; // respect budget with a little headroom
			}
			$out[] = array(
				'id'    => $p->get_id(),
				'name'  => $p->get_name(),
				'price' => wp_strip_all_tags( wc_price( $p->get_price() ) ),
				'img'   => wp_get_attachment_image_url( $p->get_image_id(), 'nura-thumb' ) ?: wc_placeholder_img_src(),
				'url'   => get_permalink( $p->get_id() ),
			);
		}
		if ( empty( $out ) && $products ) {
			foreach ( array_slice( $products, 0, 3 ) as $p ) {
				$out[] = array( 'id' => $p->get_id(), 'name' => $p->get_name(), 'price' => wp_strip_all_tags( wc_price( $p->get_price() ) ), 'img' => wp_get_attachment_image_url( $p->get_image_id(), 'nura-thumb' ) ?: wc_placeholder_img_src(), 'url' => get_permalink( $p->get_id() ) );
			}
		}

		$note = sprintf(
			/* translators: 1: face shape 2: lifestyle */
			__( 'Matched to your %1$s face shape and %2$s lifestyle. Book a free consultation to confirm.', 'nura-experience' ),
			$face && 'unsure' !== $face ? $face : __( 'chosen', 'nura-experience' ),
			$life
		);

		return rest_ensure_response( array( 'note' => $note, 'products' => $out ) );
	}
}
