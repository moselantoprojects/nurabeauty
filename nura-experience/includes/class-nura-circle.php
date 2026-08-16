<?php
/**
 * The NURA Circle - luxury client portal + membership.
 *
 * Adds WooCommerce My Account endpoints for:
 *  - Care schedule (auto-computed maintenance reminders from order dates)
 *  - Warranty certificates (per premium order, with certificate numbers)
 *  - Loyalty points ("Radiance Points", earned on completed orders)
 *  - VIP membership status (The NURA Circle)
 * Plus the [nura_circle_portal] shortcode for the marketing page.
 *
 * @package NURA_Experience
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class NURAX_Circle {

	const POINTS_META  = '_nurax_points';
	const MEMBER_META  = '_nurax_member';
	const RATE         = 100; // 1 point per KES 100 spent.

	public function __construct() {
		add_shortcode( 'nura_circle_portal', array( $this, 'portal_shortcode' ) );

		// Award points + create warranty record when an order completes.
		add_action( 'woocommerce_order_status_completed', array( $this, 'on_order_complete' ) );

		// My Account endpoints.
		add_action( 'init', array( $this, 'add_endpoints' ) );
		add_filter( 'woocommerce_account_menu_items', array( $this, 'menu_items' ) );
		add_action( 'woocommerce_account_nura-circle_endpoint', array( $this, 'render_dashboard' ) );
		add_action( 'woocommerce_account_care-schedule_endpoint', array( $this, 'render_care' ) );
		add_action( 'woocommerce_account_warranties_endpoint', array( $this, 'render_warranties' ) );
	}

	public function add_endpoints() {
		add_rewrite_endpoint( 'nura-circle', EP_ROOT | EP_PAGES );
		add_rewrite_endpoint( 'care-schedule', EP_ROOT | EP_PAGES );
		add_rewrite_endpoint( 'warranties', EP_ROOT | EP_PAGES );
	}

	public function menu_items( $items ) {
		$new = array();
		foreach ( $items as $key => $label ) {
			$new[ $key ] = $label;
			if ( 'dashboard' === $key ) {
				$new['nura-circle']   = __( 'The NURA Circle', 'nura-experience' );
				$new['care-schedule'] = __( 'Care Schedule', 'nura-experience' );
				$new['warranties']    = __( 'Warranty Certificates', 'nura-experience' );
			}
		}
		return $new;
	}

	/**
	 * Award loyalty points + record a warranty certificate per eligible item.
	 */
	public function on_order_complete( $order_id ) {
		$order = wc_get_order( $order_id );
		if ( ! $order ) {
			return;
		}
		$user_id = $order->get_user_id();
		if ( ! $user_id ) {
			return;
		}
		// Points.
		$earned = (int) floor( $order->get_total() / self::RATE );
		$current = (int) get_user_meta( $user_id, self::POINTS_META, true );
		update_user_meta( $user_id, self::POINTS_META, $current + $earned );

		// Warranty certificate per line item.
		$certs = get_user_meta( $user_id, '_nurax_warranties', true );
		$certs = is_array( $certs ) ? $certs : array();
		foreach ( $order->get_items() as $item ) {
			$certs[] = array(
				'number'  => 'NURA-W-' . $order_id . '-' . $item->get_id(),
				'product' => $item->get_name(),
				'date'    => $order->get_date_completed() ? $order->get_date_completed()->date( 'Y-m-d' ) : gmdate( 'Y-m-d' ),
				'order'   => $order_id,
			);
		}
		update_user_meta( $user_id, '_nurax_warranties', $certs );
	}

	public function get_points( $user_id ) {
		return (int) get_user_meta( $user_id, self::POINTS_META, true );
	}

	public function is_member( $user_id ) {
		return (bool) get_user_meta( $user_id, self::MEMBER_META, true );
	}

	/**
	 * Dashboard tab.
	 */
	public function render_dashboard() {
		$uid = get_current_user_id();
		$points = $this->get_points( $uid );
		$member = $this->is_member( $uid );
		echo '<div class="nurax-portal">';
		echo '<h3>' . esc_html__( 'Welcome to The NURA Circle', 'nura-experience' ) . '</h3>';
		echo '<p>' . sprintf( esc_html__( 'Radiance Points: %s', 'nura-experience' ), '<strong>' . esc_html( $points ) . '</strong>' ) . '</p>';
		echo '<p>' . ( $member
			? esc_html__( 'Status: VIP member. Enjoy member pricing, priority booking and exclusive drops.', 'nura-experience' )
			: esc_html__( 'Status: Client. Join the VIP membership for member pricing, priority booking and at-home service.', 'nura-experience' ) ) . '</p>';
		echo '<p><a class="nura-btn nura-btn--gold" href="' . esc_url( wc_get_endpoint_url( 'orders' ) ) . '">' . esc_html__( 'View my orders', 'nura-experience' ) . '</a></p>';
		echo '</div>';
	}

	/**
	 * Care schedule: compute next care date from most recent order.
	 */
	public function render_care() {
		$uid = get_current_user_id();
		$orders = wc_get_orders( array( 'customer_id' => $uid, 'limit' => 5, 'orderby' => 'date', 'order' => 'DESC' ) );
		echo '<div class="nurax-portal"><h3>' . esc_html__( 'Your care schedule', 'nura-experience' ) . '</h3>';
		if ( ! $orders ) {
			echo '<p>' . esc_html__( 'Once you receive your first unit, we will schedule your wash & revamp reminders here.', 'nura-experience' ) . '</p></div>';
			return;
		}
		echo '<p>' . esc_html__( 'We recommend a wash & revamp every 6-8 weeks of regular wear. Your upcoming care dates:', 'nura-experience' ) . '</p><ul>';
		foreach ( $orders as $order ) {
			$date = $order->get_date_created();
			if ( ! $date ) { continue; }
			$next = clone $date;
			$next->modify( '+49 days' );
			foreach ( $order->get_items() as $item ) {
				echo '<li><strong>' . esc_html( $item->get_name() ) . '</strong> - ' . esc_html__( 'next care:', 'nura-experience' ) . ' ' . esc_html( $next->date_i18n( get_option( 'date_format' ) ) ) . ' <a href="' . esc_url( home_url( '/book-appointment/' ) ) . '">' . esc_html__( 'Book revamp', 'nura-experience' ) . '</a></li>';
			}
		}
		echo '</ul></div>';
	}

	/**
	 * Warranty certificates tab.
	 */
	public function render_warranties() {
		$uid = get_current_user_id();
		$certs = get_user_meta( $uid, '_nurax_warranties', true );
		echo '<div class="nurax-portal"><h3>' . esc_html__( 'Your warranty certificates', 'nura-experience' ) . '</h3>';
		if ( empty( $certs ) || ! is_array( $certs ) ) {
			echo '<p>' . esc_html__( 'Your provenance and warranty certificates will appear here after your orders are completed.', 'nura-experience' ) . '</p></div>';
			return;
		}
		echo '<table class="shop_table"><tr><th>' . esc_html__( 'Certificate', 'nura-experience' ) . '</th><th>' . esc_html__( 'Unit', 'nura-experience' ) . '</th><th>' . esc_html__( 'Issued', 'nura-experience' ) . '</th></tr>';
		foreach ( $certs as $c ) {
			echo '<tr><td><code>' . esc_html( $c['number'] ) . '</code></td><td>' . esc_html( $c['product'] ) . '</td><td>' . esc_html( $c['date'] ) . '</td></tr>';
		}
		echo '</table></div>';
	}

	/**
	 * Public marketing shortcode: shows sign-in or a snapshot for logged-in clients.
	 */
	public function portal_shortcode() {
		if ( ! is_user_logged_in() ) {
			$login = function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'myaccount' ) : wp_login_url();
			return '<p><a class="nura-btn nura-btn--gold" href="' . esc_url( $login ) . '">' . esc_html__( 'Sign in / Join The Circle', 'nura-experience' ) . '</a></p>';
		}
		$uid = get_current_user_id();
		ob_start();
		echo '<div class="nurax-portal-card">';
		echo '<p>' . sprintf( esc_html__( 'You have %s Radiance Points.', 'nura-experience' ), '<strong>' . esc_html( $this->get_points( $uid ) ) . '</strong>' ) . '</p>';
		echo '<p><a class="nura-btn" href="' . esc_url( wc_get_endpoint_url( 'nura-circle', '', wc_get_page_permalink( 'myaccount' ) ) ) . '">' . esc_html__( 'Open my portal', 'nura-experience' ) . '</a></p>';
		echo '</div>';
		return ob_get_clean();
	}
}
