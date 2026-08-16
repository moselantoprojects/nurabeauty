<?php
/**
 * NURA Experience settings.
 *
 * A single place to (optionally) plug real AI providers into the Wig Finder and
 * Virtual Try-On, and to toggle features. Everything works without keys using
 * the built-in rule-based recommender and client-side try-on; keys simply
 * upgrade those to full AI.
 *
 * @package NURA_Experience
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class NURAX_Settings {

	const OPTION = 'nurax_settings';

	public function __construct() {
		add_action( 'admin_menu', array( $this, 'menu' ) );
		add_action( 'admin_init', array( $this, 'register' ) );
	}

	public function menu() {
		add_options_page( 'NURA Experience', 'NURA Experience', 'manage_options', 'nurax', array( $this, 'page' ) );
	}

	public function register() {
		register_setting( 'nurax', self::OPTION );
	}

	public static function get( $key, $default = '' ) {
		$opts = get_option( self::OPTION, array() );
		return isset( $opts[ $key ] ) ? $opts[ $key ] : $default;
	}

	public function page() {
		$o = get_option( self::OPTION, array() );
		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'NURA Experience', 'nura-experience' ); ?></h1>
			<p><?php esc_html_e( 'The three exclusive features work out of the box. Add API details below only if you want to upgrade the AI Wig Finder to real face-shape vision analysis, or the Virtual Try-On to auto-aligned face tracking.', 'nura-experience' ); ?></p>
			<form method="post" action="options.php">
				<?php settings_fields( 'nurax' ); ?>
				<table class="form-table">
					<tr>
						<th><?php esc_html_e( 'AI vision API endpoint', 'nura-experience' ); ?></th>
						<td><input type="url" style="width:420px" name="<?php echo esc_attr( self::OPTION ); ?>[ai_endpoint]" value="<?php echo esc_attr( $o['ai_endpoint'] ?? '' ); ?>" placeholder="https://api.provider.com/face-shape"></td>
					</tr>
					<tr>
						<th><?php esc_html_e( 'AI API key', 'nura-experience' ); ?></th>
						<td><input type="password" style="width:420px" name="<?php echo esc_attr( self::OPTION ); ?>[ai_key]" value="<?php echo esc_attr( $o['ai_key'] ?? '' ); ?>"></td>
					</tr>
					<tr>
						<th><?php esc_html_e( 'Try-On face-tracking provider (JS)', 'nura-experience' ); ?></th>
						<td><input type="text" style="width:420px" name="<?php echo esc_attr( self::OPTION ); ?>[tryon_provider]" value="<?php echo esc_attr( $o['tryon_provider'] ?? '' ); ?>" placeholder="e.g. mediapipe, banuba, custom"></td>
					</tr>
					<tr>
						<th><?php esc_html_e( 'VIP membership price (KES/yr)', 'nura-experience' ); ?></th>
						<td><input type="number" name="<?php echo esc_attr( self::OPTION ); ?>[member_price]" value="<?php echo esc_attr( $o['member_price'] ?? '6000' ); ?>"></td>
					</tr>
				</table>
				<?php submit_button(); ?>
			</form>
		</div>
		<?php
	}
}
