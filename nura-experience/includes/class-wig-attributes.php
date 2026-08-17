<?php
/**
 * NURA Wig Setup - one-click WooCommerce attributes for variable wig products.
 *
 * Creates the global product attributes (Length, Texture, Lace Type, Density,
 * Colour) and their terms so wigs can be sold as VARIABLE products with proper
 * selectable size/length/lace options. Adds a "NURA Wig Setup" tools page under
 * WooCommerce with a one-click button and a downloadable variable-product CSV.
 *
 * @package NURA_Experience
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class NURAX_Wig_Attributes {

	public function __construct() {
		add_action( 'admin_menu', array( $this, 'menu' ) );
		add_action( 'admin_post_nurax_make_attributes', array( $this, 'handle' ) );
	}

	public static function defs() {
		return array(
			'length'  => array( 'label' => 'Length',    'terms' => array( '8"', '10"', '12"', '14"', '16"', '18"', '20"', '22"', '24"', '26"', '28"', '30"' ) ),
			'texture' => array( 'label' => 'Texture',   'terms' => array( 'Straight', 'Body Wave', 'Deep Wave', 'Water Wave', 'Curly', 'Kinky Straight' ) ),
			'lace'    => array( 'label' => 'Lace Type', 'terms' => array( 'Lace Closure', 'Lace Frontal', 'HD Lace', '360 Lace', 'Full Lace', 'U-Part', 'Glueless' ) ),
			'density' => array( 'label' => 'Density',   'terms' => array( '130%', '150%', '180%', '200%', '250%' ) ),
			'colour'  => array( 'label' => 'Colour',    'terms' => array( 'Natural Black', 'Jet Black', 'Off Black', 'Brown', 'Highlighted', 'Blonde', 'Ombre', 'Burgundy' ) ),
		);
	}

	public function menu() {
		add_submenu_page( 'woocommerce', 'NURA Wig Setup', 'NURA Wig Setup', 'manage_woocommerce', 'nurax-wig-setup', array( $this, 'page' ) );
	}

	private function existing() {
		$rows = array();
		foreach ( self::defs() as $slug => $d ) {
			$rows[ $slug ] = taxonomy_exists( wc_attribute_taxonomy_name( $slug ) );
		}
		return $rows;
	}

	public function page() {
		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			return;
		}
		$existing = $this->existing();
		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'NURA Wig Setup', 'nura-experience' ); ?></h1>
			<p style="max-width:680px"><?php esc_html_e( 'One click creates the standard wig attributes so you can sell variable products with selectable Length, Texture, Lace Type, Density and Colour options.', 'nura-experience' ); ?></p>
			<?php if ( isset( $_GET['done'] ) ) : ?>
				<div class="notice notice-success"><p><?php esc_html_e( 'Wig attributes are ready. Now edit a product, set Product data to "Variable product", add these attributes on the Attributes tab (tick "Used for variations"), then build the variations.', 'nura-experience' ); ?></p></div>
			<?php endif; ?>
			<table class="widefat" style="max-width:680px;margin:1em 0">
				<thead><tr><th><?php esc_html_e( 'Attribute', 'nura-experience' ); ?></th><th><?php esc_html_e( 'Status', 'nura-experience' ); ?></th></tr></thead>
				<tbody>
				<?php foreach ( self::defs() as $slug => $d ) : ?>
					<tr>
						<td><strong><?php echo esc_html( $d['label'] ); ?></strong> <code>pa_<?php echo esc_html( $slug ); ?></code><br><small><?php echo esc_html( implode( ', ', $d['terms'] ) ); ?></small></td>
						<td><?php echo $existing[ $slug ] ? '<span style="color:#2e7d32;font-weight:600">Created</span>' : '<span style="color:#a00">Not yet</span>'; ?></td>
					</tr>
				<?php endforeach; ?>
				</tbody>
			</table>
			<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
				<input type="hidden" name="action" value="nurax_make_attributes">
				<?php wp_nonce_field( 'nurax_make_attributes' ); ?>
				<?php submit_button( __( 'Create / update wig attributes', 'nura-experience' ) ); ?>
			</form>
			<h2><?php esc_html_e( 'Turn a product into selectable sizes/lengths', 'nura-experience' ); ?></h2>
			<ol style="max-width:680px">
				<li><?php esc_html_e( 'Click the button above once to create the attributes.', 'nura-experience' ); ?></li>
				<li><?php esc_html_e( 'Edit a product > Product data dropdown > choose "Variable product".', 'nura-experience' ); ?></li>
				<li><?php esc_html_e( 'Attributes tab: add Length and Lace Type, pick the values, and tick "Used for variations". Save.', 'nura-experience' ); ?></li>
				<li><?php esc_html_e( 'Variations tab: "Generate variations", then set a price (and optional SKU/stock) per variation.', 'nura-experience' ); ?></li>
			</ol>
			<p><?php esc_html_e( 'Prefer a head start? Import the ready-made file in the plugin folder at samples/nura-variable-products-template.csv via WooCommerce > Products > Import.', 'nura-experience' ); ?></p>
		</div>
		<?php
	}

	public function handle() {
		if ( ! current_user_can( 'manage_woocommerce' ) || ! isset( $_POST['_wpnonce'] ) || ! wp_verify_nonce( $_POST['_wpnonce'], 'nurax_make_attributes' ) ) {
			wp_die( esc_html__( 'Not allowed', 'nura-experience' ) );
		}
		if ( ! function_exists( 'wc_create_attribute' ) ) {
			wp_safe_redirect( admin_url( 'admin.php?page=nurax-wig-setup' ) );
			exit;
		}
		foreach ( self::defs() as $slug => $d ) {
			$tax = wc_attribute_taxonomy_name( $slug );
			if ( ! taxonomy_exists( $tax ) ) {
				wc_create_attribute( array(
					'name'         => $d['label'],
					'slug'         => $slug,
					'type'         => 'select',
					'order_by'     => 'menu_order',
					'has_archives' => false,
				) );
				register_taxonomy( $tax, array( 'product' ), array() );
			}
			foreach ( $d['terms'] as $term ) {
				if ( ! term_exists( $term, $tax ) ) {
					wp_insert_term( $term, $tax );
				}
			}
		}
		delete_transient( 'wc_attribute_taxonomies' );
		wp_safe_redirect( admin_url( 'admin.php?page=nurax-wig-setup&done=1' ) );
		exit;
	}
}
