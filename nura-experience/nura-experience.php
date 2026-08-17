<?php
/**
 * Plugin Name:       NURA Experience
 * Plugin URI:        https://nura.co.ke
 * Description:       NURA's three exclusive features: AI Wig Finder, Virtual Try-On, and The NURA Circle luxury client portal (order history, care schedule, warranty certificates, maintenance reminders, loyalty points, VIP membership). Requires WooCommerce.
 * Version:           1.3.0
 * Author:            NURA - The House of Radiant Confidence
 * License:           GPL-2.0-or-later
 * Text Domain:       nura-experience
 * Requires at least: 6.2
 * Requires PHP:      7.4
 * WC requires at least: 8.0
 *
 * @package NURA_Experience
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'NURAX_VERSION', '1.3.0' );
define( 'NURAX_DIR', plugin_dir_path( __FILE__ ) );
define( 'NURAX_URL', plugin_dir_url( __FILE__ ) );

require_once NURAX_DIR . 'includes/class-ai-wig-finder.php';
require_once NURAX_DIR . 'includes/class-virtual-tryon.php';
require_once NURAX_DIR . 'includes/class-nura-circle.php';
require_once NURAX_DIR . 'includes/class-settings.php';
require_once NURAX_DIR . 'includes/class-ai-stylist.php';
require_once NURAX_DIR . 'includes/class-shop-enhance.php';
require_once NURAX_DIR . 'includes/class-wig-attributes.php';

/**
 * Boot.
 */
function nurax_init() {
	new NURAX_AI_Wig_Finder();
	new NURAX_Virtual_TryOn();
	new NURAX_Circle();
	new NURAX_Settings();
	new NURAX_AI_Stylist();
	new NURAX_Shop_Enhance();
	new NURAX_Wig_Attributes();
}
add_action( 'plugins_loaded', 'nurax_init' );

/**
 * Shared front-end assets.
 */
function nurax_assets() {
	wp_enqueue_style( 'nurax', NURAX_URL . 'assets/css/nurax.css', array(), NURAX_VERSION );
	wp_enqueue_script( 'nurax', NURAX_URL . 'assets/js/nurax.js', array(), NURAX_VERSION, true );
	wp_localize_script( 'nurax', 'NURAX', array(
		'ajaxUrl' => admin_url( 'admin-ajax.php' ),
		'nonce'   => wp_create_nonce( 'nurax' ),
		'rest'    => esc_url_raw( rest_url( 'nurax/v1/' ) ),
		'shopUrl' => ( function_exists( 'wc_get_page_id' ) ? get_permalink( wc_get_page_id( 'shop' ) ) : home_url( '/shop/' ) ),
	) );
}
add_action( 'wp_enqueue_scripts', 'nurax_assets' );

/**
 * Admin notice if WooCommerce is missing.
 */
function nurax_wc_notice() {
	if ( ! class_exists( 'WooCommerce' ) ) {
		echo '<div class="notice notice-warning"><p><strong>NURA Experience</strong> needs WooCommerce active for the client portal, try-on cart actions and product recommendations.</p></div>';
	}
}
add_action( 'admin_notices', 'nurax_wc_notice' );
