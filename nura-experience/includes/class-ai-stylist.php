<?php
/**
 * NURA AI Stylist - a floating chat concierge.
 *
 * Renders a luxury chat widget in the site footer and exposes a REST endpoint
 * that proxies to an LLM (OpenAI or Google Gemini) using the API key set in
 * NURA Experience settings. With no key it falls back to a guided assistant
 * (smart canned replies + product picks + WhatsApp hand-off) so the widget
 * always works. Nothing is hardcoded - everything reads from settings.
 *
 * @package NURA_Experience
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class NURAX_AI_Stylist {

	public function __construct() {
		add_action( 'rest_api_init', array( $this, 'routes' ) );
		add_action( 'wp_footer', array( $this, 'widget' ) );
	}

	public function routes() {
		register_rest_route( 'nurax/v1', '/stylist', array(
			'methods'             => 'POST',
			'callback'            => array( $this, 'chat' ),
			'permission_callback' => '__return_true',
		) );
	}

	/** Is a real LLM configured? */
	public static function has_ai() {
		$prov = NURAX_Settings::get( 'stylist_provider', 'openai' );
		$key  = NURAX_Settings::get( 'stylist_key', '' );
		return ( ! empty( $key ) && 'off' !== $prov );
	}

	/** Render the floating widget in the footer. */
	public function widget() {
		if ( is_admin() ) {
			return;
		}
		if ( 'off' === NURAX_Settings::get( 'stylist_enable', 'on' ) ) {
			return;
		}
		$greeting = NURAX_Settings::get( 'stylist_greeting', '' );
		if ( empty( $greeting ) ) {
			$greeting = __( 'Hi, I am your NURA Stylist. Tell me the occasion, your budget, or the look you love, and I will help you find your perfect wig.', 'nura-experience' );
		}
		$wa = NURAX_Settings::get( 'whatsapp', get_theme_mod( 'nura_whatsapp', '' ) );
		?>
		<div class="nurax-stylist" data-nurax-stylist data-has-ai="<?php echo self::has_ai() ? '1' : '0'; ?>" data-wa="<?php echo esc_attr( $wa ); ?>">
			<button type="button" class="nurax-stylist__fab" data-stylist-toggle aria-label="<?php esc_attr_e( 'Chat with the NURA Stylist', 'nura-experience' ); ?>">
				<span class="nurax-stylist__fab-icon" aria-hidden="true">&#9826;</span>
				<span class="nurax-stylist__fab-label"><?php esc_html_e( 'NURA Stylist', 'nura-experience' ); ?></span>
			</button>
			<div class="nurax-stylist__panel" data-stylist-panel hidden>
				<div class="nurax-stylist__head">
					<div>
						<strong><?php esc_html_e( 'NURA Stylist', 'nura-experience' ); ?></strong>
						<small><?php esc_html_e( 'Your personal wig concierge', 'nura-experience' ); ?></small>
					</div>
					<button type="button" class="nurax-stylist__close" data-stylist-close aria-label="<?php esc_attr_e( 'Close', 'nura-experience' ); ?>">&times;</button>
				</div>
				<div class="nurax-stylist__log" data-stylist-log>
					<div class="nurax-msg nurax-msg--bot"><?php echo esc_html( $greeting ); ?></div>
				</div>
				<div class="nurax-stylist__quick" data-stylist-quick>
					<button type="button" data-q="<?php esc_attr_e( 'I need a wig for my wedding', 'nura-experience' ); ?>"><?php esc_html_e( 'Bridal', 'nura-experience' ); ?></button>
					<button type="button" data-q="<?php esc_attr_e( 'Show me HD lace front wigs', 'nura-experience' ); ?>"><?php esc_html_e( 'HD Lace', 'nura-experience' ); ?></button>
					<button type="button" data-q="<?php esc_attr_e( 'What do you have under KES 5000?', 'nura-experience' ); ?>"><?php esc_html_e( 'Under 5K', 'nura-experience' ); ?></button>
					<button type="button" data-q="<?php esc_attr_e( 'How do I care for my wig?', 'nura-experience' ); ?>"><?php esc_html_e( 'Wig care', 'nura-experience' ); ?></button>
				</div>
				<form class="nurax-stylist__input" data-stylist-form>
					<input type="text" name="msg" autocomplete="off" placeholder="<?php esc_attr_e( 'Ask me anything...', 'nura-experience' ); ?>" required>
					<button type="submit" aria-label="<?php esc_attr_e( 'Send', 'nura-experience' ); ?>">&#10148;</button>
				</form>
			</div>
		</div>
		<?php
	}

	/** REST chat handler. */
	public function chat( WP_REST_Request $req ) {
		$messages = $req->get_param( 'messages' );
		if ( ! is_array( $messages ) ) {
			$messages = array();
		}
		$clean = array();
		foreach ( array_slice( $messages, -10 ) as $m ) {
			$role    = ( isset( $m['role'] ) && 'assistant' === $m['role'] ) ? 'assistant' : 'user';
			$content = isset( $m['content'] ) ? wp_strip_all_tags( (string) $m['content'] ) : '';
			$content = trim( mb_substr( $content, 0, 800 ) );
			if ( '' !== $content ) {
				$clean[] = array( 'role' => $role, 'content' => $content );
			}
		}
		if ( empty( $clean ) ) {
			return rest_ensure_response( array( 'reply' => __( 'Tell me a little about the look you want and I will help.', 'nura-experience' ), 'products' => array() ) );
		}

		$last      = end( $clean );
		$last_text = isset( $last['content'] ) ? $last['content'] : '';

		if ( ! self::has_ai() ) {
			return rest_ensure_response( $this->guided( $last_text ) );
		}

		$system   = $this->system_prompt();
		$provider = NURAX_Settings::get( 'stylist_provider', 'openai' );
		$reply    = ( 'gemini' === $provider ) ? $this->call_gemini( $system, $clean ) : $this->call_openai( $system, $clean );

		if ( is_wp_error( $reply ) || '' === $reply ) {
			$g         = $this->guided( $last_text );
			$g['note'] = __( 'AI is busy right now - here is a quick hand pick.', 'nura-experience' );
			return rest_ensure_response( $g );
		}

		return rest_ensure_response( array( 'reply' => $reply, 'products' => $this->suggest_products( $last_text ) ) );
	}

	/** Build the system prompt with a live catalog snapshot. */
	private function system_prompt() {
		$brand  = get_bloginfo( 'name' );
		$cat    = $this->catalog_snippet();
		$custom = NURAX_Settings::get( 'stylist_prompt', '' );
		$base   = "You are the NURA Stylist, a warm, expert personal wig concierge for {$brand}, a luxury human-hair wig boutique in Nairobi, Kenya. "
			. "Help customers choose the right wig by face shape, occasion, texture, length, lace type and budget (prices are in Kenyan Shillings, KES). "
			. "Be concise, friendly and practical - reply in 2 to 4 short sentences. Recommend specific units from the catalog below by name when relevant, and suggest booking a free consultation or chatting on WhatsApp for fittings and orders. "
			. "Mention same-day Nairobi delivery, M-Pesa and pay-on-delivery when relevant. Only discuss NURA, wigs, hair care, orders and delivery. Never invent prices or products that are not in the catalog; if unsure, offer a free consultation.\n\nCATALOG:\n{$cat}";
		if ( ! empty( $custom ) ) {
			$base .= "\n\nADDITIONAL BRAND NOTES:\n" . $custom;
		}
		return $base;
	}

	private function catalog_snippet() {
		$out = '';
		if ( ! function_exists( 'wc_get_products' ) ) {
			return $out;
		}
		$products = wc_get_products( array( 'status' => 'publish', 'limit' => 24, 'orderby' => 'popularity' ) );
		foreach ( $products as $prod ) {
			$out .= '- ' . $prod->get_name() . ' (' . wp_strip_all_tags( wc_price( $prod->get_price() ) ) . ') ' . get_permalink( $prod->get_id() ) . "\n";
		}
		return $out;
	}

	private function call_openai( $system, $messages ) {
		$key   = NURAX_Settings::get( 'stylist_key', '' );
		$model = NURAX_Settings::get( 'stylist_model', '' );
		if ( empty( $model ) ) {
			$model = 'gpt-4o-mini';
		}
		$msgs = array( array( 'role' => 'system', 'content' => $system ) );
		foreach ( $messages as $m ) {
			$msgs[] = $m;
		}
		$resp = wp_remote_post( 'https://api.openai.com/v1/chat/completions', array(
			'timeout' => 25,
			'headers' => array(
				'Content-Type'  => 'application/json',
				'Authorization' => 'Bearer ' . $key,
			),
			'body'    => wp_json_encode( array(
				'model'       => $model,
				'messages'    => $msgs,
				'temperature' => 0.6,
				'max_tokens'  => 320,
			) ),
		) );
		if ( is_wp_error( $resp ) ) {
			return $resp;
		}
		$data = json_decode( wp_remote_retrieve_body( $resp ), true );
		if ( isset( $data['choices'][0]['message']['content'] ) ) {
			return trim( $data['choices'][0]['message']['content'] );
		}
		return '';
	}

	private function call_gemini( $system, $messages ) {
		$key   = NURAX_Settings::get( 'stylist_key', '' );
		$model = NURAX_Settings::get( 'stylist_model', '' );
		if ( empty( $model ) ) {
			$model = 'gemini-1.5-flash';
		}
		$contents = array();
		foreach ( $messages as $m ) {
			$role       = ( 'assistant' === $m['role'] ) ? 'model' : 'user';
			$contents[] = array( 'role' => $role, 'parts' => array( array( 'text' => $m['content'] ) ) );
		}
		$url  = 'https://generativelanguage.googleapis.com/v1beta/models/' . rawurlencode( $model ) . ':generateContent?key=' . rawurlencode( $key );
		$resp = wp_remote_post( $url, array(
			'timeout' => 25,
			'headers' => array( 'Content-Type' => 'application/json' ),
			'body'    => wp_json_encode( array(
				'system_instruction' => array( 'parts' => array( array( 'text' => $system ) ) ),
				'contents'           => $contents,
				'generationConfig'   => array( 'temperature' => 0.6, 'maxOutputTokens' => 320 ),
			) ),
		) );
		if ( is_wp_error( $resp ) ) {
			return $resp;
		}
		$data = json_decode( wp_remote_retrieve_body( $resp ), true );
		if ( isset( $data['candidates'][0]['content']['parts'][0]['text'] ) ) {
			return trim( $data['candidates'][0]['content']['parts'][0]['text'] );
		}
		return '';
	}

	/** Rule-based fallback so the widget always helps, even with no key. */
	private function guided( $text ) {
		$t     = strtolower( $text );
		$reply = __( 'I can help with that. Tell me your budget and the occasion, or book a free consultation and a stylist will guide you.', 'nura-experience' );
		if ( false !== strpos( $t, 'bridal' ) || false !== strpos( $t, 'wedding' ) ) {
			$reply = __( 'Congratulations! For weddings our brides love our HD-lace bridal units for a flawless, photo-ready hairline. Book a free bridal fitting and we will tailor the look to your dress and face shape.', 'nura-experience' );
		} elseif ( false !== strpos( $t, 'care' ) || false !== strpos( $t, 'wash' ) || false !== strpos( $t, 'maintain' ) ) {
			$reply = __( 'To keep your unit gorgeous: wash gently with sulphate-free shampoo every 2 to 3 weeks, condition mid-to-ends, air-dry on a stand, and wrap in silk at night. Every NURA order ships with a full care guide.', 'nura-experience' );
		} elseif ( false !== strpos( $t, '5000' ) || false !== strpos( $t, '5,000' ) || false !== strpos( $t, 'cheap' ) || false !== strpos( $t, 'budget' ) || false !== strpos( $t, 'afford' ) ) {
			$reply = __( 'We have beautiful units at friendly prices - here are a few great-value picks. Would you like me to filter by length or texture?', 'nura-experience' );
		} elseif ( false !== strpos( $t, 'lace' ) || false !== strpos( $t, 'hd' ) || false !== strpos( $t, 'frontal' ) ) {
			$reply = __( 'Our HD lace fronts give an invisible, melt-into-skin hairline. Here are some favourites - I can also help you book a fitting.', 'nura-experience' );
		} elseif ( false !== strpos( $t, 'deliver' ) || false !== strpos( $t, 'ship' ) || false !== strpos( $t, 'mpesa' ) || false !== strpos( $t, 'm-pesa' ) || false !== strpos( $t, 'pay' ) ) {
			$reply = __( 'We offer same-day delivery in Nairobi and countrywide shipping, with M-Pesa, card or pay-on-delivery. Ready to order?', 'nura-experience' );
		}
		return array( 'reply' => $reply, 'products' => $this->suggest_products( $text ) );
	}

	/** Pick a few relevant products for the chat cards. */
	private function suggest_products( $text ) {
		if ( ! function_exists( 'wc_get_products' ) ) {
			return array();
		}
		$t    = strtolower( $text );
		$args = array( 'status' => 'publish', 'limit' => 3, 'orderby' => 'popularity' );
		$map  = array(
			'bridal'  => 'bridal-occasion',
			'wedding' => 'bridal-occasion',
			'lace'    => 'lace-front-hd',
			'hd'      => 'lace-front-hd',
			'ready'   => 'ready-to-wear',
		);
		foreach ( $map as $kw => $slug ) {
			if ( false !== strpos( $t, $kw ) ) {
				$args['category'] = array( $slug );
				break;
			}
		}
		if ( false !== strpos( $t, '5000' ) || false !== strpos( $t, 'budget' ) || false !== strpos( $t, 'cheap' ) || false !== strpos( $t, 'afford' ) ) {
			$args['orderby'] = 'price';
			$args['order']   = 'ASC';
		}
		$products = wc_get_products( $args );
		if ( empty( $products ) ) {
			$products = wc_get_products( array( 'status' => 'publish', 'limit' => 3, 'orderby' => 'popularity' ) );
		}
		$out = array();
		foreach ( $products as $prod ) {
			$img   = wp_get_attachment_image_url( $prod->get_image_id(), 'woocommerce_thumbnail' );
			$out[] = array(
				'name'  => $prod->get_name(),
				'price' => wp_strip_all_tags( wc_price( $prod->get_price() ) ),
				'img'   => $img ? $img : wc_placeholder_img_src(),
				'url'   => get_permalink( $prod->get_id() ),
			);
		}
		return $out;
	}
}
