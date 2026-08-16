<?php
/**
 * Brand + form shortcodes.
 *
 * These let page copy reference live Customizer values (phone, email, address,
 * WhatsApp) instead of hardcoding them, and provide simple contact/booking
 * forms that upgrade to Contact Form 7 automatically when it is installed.
 *
 * @package NURA_Beauty
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/* Brand value shortcodes: [nura_phone] [nura_email] [nura_address] [nura_hours] [nura_whatsapp] */
foreach ( array( 'nura_phone', 'nura_email', 'nura_address', 'nura_hours', 'nura_whatsapp' ) as $sc ) {
	add_shortcode( $sc, function () use ( $sc ) {
		return esc_html( nura_opt( $sc ) );
	} );
}

/**
 * [nura_contact_form] - falls back to a simple form; uses CF7 form if one exists.
 */
add_shortcode( 'nura_contact_form', function () {
	// If Contact Form 7 is active and a form exists, prefer it.
	if ( defined( 'WPCF7_VERSION' ) ) {
		$forms = get_posts( array( 'post_type' => 'wpcf7_contact_form', 'numberposts' => 1 ) );
		if ( $forms ) {
			return do_shortcode( '[contact-form-7 id="' . $forms[0]->ID . '"]' );
		}
	}
	ob_start(); ?>
	<form class="nura-form" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" method="post">
		<input type="hidden" name="action" value="nura_contact">
		<?php wp_nonce_field( 'nura_contact' ); ?>
		<p><input type="text" name="nura_name" placeholder="<?php esc_attr_e( 'Your name', 'nura-beauty' ); ?>" required></p>
		<p><input type="text" name="nura_contact_val" placeholder="<?php esc_attr_e( 'Phone / WhatsApp or email', 'nura-beauty' ); ?>" required></p>
		<p><textarea name="nura_message" rows="5" placeholder="<?php esc_attr_e( 'How can we help?', 'nura-beauty' ); ?>" required></textarea></p>
		<p><button type="submit" class="nura-btn nura-btn--gold"><?php esc_html_e( 'Send message', 'nura-beauty' ); ?></button></p>
	</form>
	<?php
	return ob_get_clean();
} );

/**
 * [nura_booking_form] - a simple appointment request form.
 */
add_shortcode( 'nura_booking_form', function () {
	ob_start(); ?>
	<form class="nura-form" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" method="post">
		<input type="hidden" name="action" value="nura_contact">
		<input type="hidden" name="nura_kind" value="booking">
		<?php wp_nonce_field( 'nura_contact' ); ?>
		<p><input type="text" name="nura_name" placeholder="<?php esc_attr_e( 'Your name', 'nura-beauty' ); ?>" required></p>
		<p><input type="text" name="nura_contact_val" placeholder="<?php esc_attr_e( 'Phone / WhatsApp', 'nura-beauty' ); ?>" required></p>
		<p>
			<select name="nura_service">
				<option><?php esc_html_e( 'Virtual consultation (free)', 'nura-beauty' ); ?></option>
				<option><?php esc_html_e( 'In-studio consultation & fitting', 'nura-beauty' ); ?></option>
				<option><?php esc_html_e( 'Installation', 'nura-beauty' ); ?></option>
				<option><?php esc_html_e( 'Styling / colour / revamp', 'nura-beauty' ); ?></option>
				<option><?php esc_html_e( 'Bridal & occasion', 'nura-beauty' ); ?></option>
			</select>
		</p>
		<p><input type="date" name="nura_date"></p>
		<p><textarea name="nura_message" rows="4" placeholder="<?php esc_attr_e( 'Tell us the look you want', 'nura-beauty' ); ?>"></textarea></p>
		<p><button type="submit" class="nura-btn nura-btn--gold"><?php esc_html_e( 'Request appointment', 'nura-beauty' ); ?></button></p>
	</form>
	<?php
	return ob_get_clean();
} );

/**
 * Handle contact/booking + newsletter submissions by emailing the site admin.
 * (A real deployment can route these to a CRM / WhatsApp instead.)
 */
function nura_handle_contact() {
	if ( ! isset( $_POST['_wpnonce'] ) || ! wp_verify_nonce( $_POST['_wpnonce'], 'nura_contact' ) ) {
		wp_safe_redirect( home_url( '/' ) );
		exit;
	}
	$name    = sanitize_text_field( wp_unslash( $_POST['nura_name'] ?? '' ) );
	$contact = sanitize_text_field( wp_unslash( $_POST['nura_contact_val'] ?? '' ) );
	$message = sanitize_textarea_field( wp_unslash( $_POST['nura_message'] ?? '' ) );
	$kind    = sanitize_text_field( wp_unslash( $_POST['nura_kind'] ?? 'enquiry' ) );
	$service = sanitize_text_field( wp_unslash( $_POST['nura_service'] ?? '' ) );
	$date    = sanitize_text_field( wp_unslash( $_POST['nura_date'] ?? '' ) );

	$body = "New {$kind} from the NURA site:\n\nName: {$name}\nContact: {$contact}\nService: {$service}\nDate: {$date}\n\nMessage:\n{$message}";
	wp_mail( nura_opt( 'nura_email' ), 'NURA - new ' . $kind, $body );

	wp_safe_redirect( add_query_arg( 'nura_sent', '1', wp_get_referer() ?: home_url( '/' ) ) );
	exit;
}
add_action( 'admin_post_nopriv_nura_contact', 'nura_handle_contact' );
add_action( 'admin_post_nura_contact', 'nura_handle_contact' );

/** Simple newsletter capture -> admin email. */
function nura_handle_subscribe() {
	$email = sanitize_email( wp_unslash( $_POST['nura_email'] ?? '' ) );
	if ( is_email( $email ) ) {
		wp_mail( nura_opt( 'nura_email' ), 'NURA - newsletter signup', $email );
	}
	wp_safe_redirect( add_query_arg( 'nura_sub', '1', wp_get_referer() ?: home_url( '/' ) ) );
	exit;
}
add_action( 'admin_post_nopriv_nura_subscribe', 'nura_handle_subscribe' );
add_action( 'admin_post_nura_subscribe', 'nura_handle_subscribe' );

/** Success notices after form submit. */
add_filter( 'the_content', function ( $content ) {
	if ( isset( $_GET['nura_sent'] ) ) {
		$content = '<div class="woocommerce-message">' . esc_html__( 'Thank you - we have received your message and will reply shortly.', 'nura-beauty' ) . '</div>' . $content;
	}
	return $content;
} );
