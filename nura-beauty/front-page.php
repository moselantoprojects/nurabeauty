<?php
/**
 * Homepage - NURA "layered discovery" layout (v1.1.0).
 * Hero slider, shop-by-type grid, interleaved product rails, editorial story,
 * client reviews, book-a-fitting, and the exclusive features.
 * All copy/images pull from the Customizer (nura_opt) and live WooCommerce data.
 * @package NURA_Beauty
 */
get_header();

$shop_url = function_exists( 'wc_get_page_id' ) ? get_permalink( wc_get_page_id( 'shop' ) ) : home_url( '/shop/' );
$wa       = nura_opt( 'nura_whatsapp' );

$slides = array(
	array(
		'img'      => ( nura_opt( 'nura_hero_image' ) ? nura_opt( 'nura_hero_image' ) : NURA_URI . 'assets/images/hero.jpg' ),
		'eyebrow'  => ( nura_opt( 'nura_hero_eyebrow' ) ? nura_opt( 'nura_hero_eyebrow' ) : __( 'The House of Radiant Confidence', 'nura-beauty' ) ),
		'title'    => ( nura_opt( 'nura_hero_title' ) ? nura_opt( 'nura_hero_title' ) : __( 'Luxury Human Hair Wigs, Made for You', 'nura-beauty' ) ),
		'sub'      => ( nura_opt( 'nura_hero_subtitle' ) ? nura_opt( 'nura_hero_subtitle' ) : __( 'Verified human hair, HD lace and glueless units, hand-crafted in Nairobi and delivered across Kenya.', 'nura-beauty' ) ),
		'cta'      => ( nura_opt( 'nura_hero_cta' ) ? nura_opt( 'nura_hero_cta' ) : __( 'Shop the Collection', 'nura-beauty' ) ),
		'cta_url'  => $shop_url,
		'cta2'     => ( nura_opt( 'nura_hero_cta2' ) ? nura_opt( 'nura_hero_cta2' ) : __( 'Book a Consultation', 'nura-beauty' ) ),
		'cta2_url' => home_url( '/book-appointment/' ),
	),
	array(
		'img'      => NURA_URI . 'assets/images/hero-2.jpg',
		'eyebrow'  => __( 'Bridal & Occasion', 'nura-beauty' ),
		'title'    => __( 'Your Crown for the Big Day', 'nura-beauty' ),
		'sub'      => __( 'Bespoke bridal units, hand-finished and fitted for a flawless, photograph-ready finish.', 'nura-beauty' ),
		'cta'      => __( 'Explore Bridal', 'nura-beauty' ),
		'cta_url'  => $shop_url,
		'cta2'     => __( 'Book a Fitting', 'nura-beauty' ),
		'cta2_url' => home_url( '/book-appointment/' ),
	),
	array(
		'img'      => NURA_URI . 'assets/images/hero-3.jpg',
		'eyebrow'  => __( 'The Confidence Line', 'nura-beauty' ),
		'title'    => __( 'Wear Your Crown', 'nura-beauty' ),
		'sub'      => __( 'Statement HD-lace units with an invisible hairline and undeniable presence.', 'nura-beauty' ),
		'cta'      => __( 'Shop New In', 'nura-beauty' ),
		'cta_url'  => $shop_url,
		'cta2'     => __( 'Try it On', 'nura-beauty' ),
		'cta2_url' => home_url( '/virtual-try-on/' ),
	),
);

$reviews = array(
	array( 'q' => 'My HD lace melted seamlessly and no one could tell it was a wig. Same-day delivery in Nairobi too. I feel like a queen.', 'n' => 'Wanjiru M.', 'r' => 'Nairobi' ),
	array( 'q' => 'The quality is unreal for the price. Soft, full, and still gorgeous months later with the care guide they sent.', 'n' => 'Amina H.', 'r' => 'Mombasa' ),
	array( 'q' => 'Booked a fitting and got exactly the bridal look I wanted for my wedding. Worth every shilling.', 'n' => 'Cynthia O.', 'r' => 'Kisumu' ),
);
?>
<main id="primary" class="site-main">

	<!-- HERO SLIDER -->
	<section class="nura-hero-slider" data-nura-slider>
		<div class="nura-slides">
			<?php foreach ( $slides as $i => $s ) : ?>
				<div class="nura-slide<?php echo 0 === $i ? ' is-active' : ''; ?>">
					<div class="nura-slide__media" style="background-image:url('<?php echo esc_url( $s['img'] ); ?>')" role="img" aria-label="<?php echo esc_attr( $s['title'] ); ?>"></div>
					<div class="nura-container nura-slide__inner">
						<p class="nura-eyebrow"><?php echo esc_html( $s['eyebrow'] ); ?></p>
						<h1><?php echo esc_html( $s['title'] ); ?></h1>
						<p class="nura-lede"><?php echo esc_html( $s['sub'] ); ?></p>
						<div class="nura-hero__cta">
							<a class="nura-btn nura-btn--gold" href="<?php echo esc_url( $s['cta_url'] ); ?>"><?php echo esc_html( $s['cta'] ); ?></a>
							<a class="nura-btn nura-btn--ghost" href="<?php echo esc_url( $s['cta2_url'] ); ?>"><?php echo esc_html( $s['cta2'] ); ?></a>
						</div>
					</div>
				</div>
			<?php endforeach; ?>
		</div>
		<button type="button" class="nura-slider-nav nura-slider-nav--prev" data-slider-prev aria-label="<?php esc_attr_e( 'Previous slide', 'nura-beauty' ); ?>">&#8249;</button>
		<button type="button" class="nura-slider-nav nura-slider-nav--next" data-slider-next aria-label="<?php esc_attr_e( 'Next slide', 'nura-beauty' ); ?>">&#8250;</button>
		<div class="nura-slider-dots" data-slider-dots></div>
	</section>

	<!-- TRUST BAR -->
	<div class="nura-trustbar">
		<div class="nura-container">
			<ul>
				<li><?php echo esc_html( nura_opt( 'nura_trust_1' ) ); ?></li>
				<li><?php echo esc_html( nura_opt( 'nura_trust_2' ) ); ?></li>
				<li><?php echo esc_html( nura_opt( 'nura_trust_3' ) ); ?></li>
				<li><?php echo esc_html( nura_opt( 'nura_trust_4' ) ); ?></li>
			</ul>
		</div>
	</div>

	<!-- SHOP BY CATEGORY -->
	<?php if ( class_exists( 'WooCommerce' ) ) : ?>
	<section class="section">
		<div class="nura-container">
			<div class="nura-shead nura-reveal">
				<p class="nura-eyebrow"><?php esc_html_e( 'The Collections', 'nura-beauty' ); ?></p>
				<h2><?php esc_html_e( 'Find your crown', 'nura-beauty' ); ?></h2>
				<p><?php esc_html_e( 'Swipe or use the arrows to explore every collection in the house.', 'nura-beauty' ); ?></p>
			</div>
			<div class="nura-cats-wrap nura-reveal">
				<button type="button" class="nura-cats-nav nura-cats-nav--prev" aria-label="<?php esc_attr_e( 'Previous collections', 'nura-beauty' ); ?>" data-nura-cats-prev>&#8249;</button>
				<div class="nura-cats" data-nura-cats>
					<?php
					$cats = get_terms( array(
						'taxonomy'   => 'product_cat',
						'hide_empty' => true,
						'parent'     => 0,
						'orderby'    => 'count',
						'order'      => 'DESC',
						'exclude'    => array( (int) get_option( 'default_product_cat' ) ),
					) );
					if ( ! is_wp_error( $cats ) && $cats ) :
						foreach ( $cats as $cat ) :
							$thumb_id = get_term_meta( $cat->term_id, 'thumbnail_id', true );
							$img      = $thumb_id ? wp_get_attachment_image_url( $thumb_id, 'nura-portrait' ) : '';
							if ( empty( $img ) ) {
								$img = function_exists( 'nura_cat_fallback_image' ) ? nura_cat_fallback_image( $cat->name ) : '';
							}
							$count = (int) $cat->count;
							?>
							<a class="nura-cat<?php echo $img ? '' : ' nura-cat--noimg'; ?>" href="<?php echo esc_url( get_term_link( $cat ) ); ?>">
								<?php if ( $img ) : ?><img src="<?php echo esc_url( $img ); ?>" alt="<?php echo esc_attr( $cat->name ); ?>" loading="lazy"><?php endif; ?>
								<span class="nura-cat__label">
									<?php echo esc_html( $cat->name ); ?>
									<?php if ( $count ) : ?><small><?php echo esc_html( sprintf( _n( '%d style', '%d styles', $count, 'nura-beauty' ), $count ) ); ?></small><?php endif; ?>
								</span>
							</a>
						<?php endforeach;
					endif; ?>
				</div>
				<button type="button" class="nura-cats-nav nura-cats-nav--next" aria-label="<?php esc_attr_e( 'More collections', 'nura-beauty' ); ?>" data-nura-cats-next>&#8250;</button>
			</div>
		</div>
	</section>

	<!-- RAIL: FRESH ARRIVALS -->
	<?php nura_rail( __( 'New in', 'nura-beauty' ), __( 'Fresh arrivals', 'nura-beauty' ), '[products limit="12" columns="12" orderby="date" order="DESC" visibility="visible" class="nura-rail-products"]', $shop_url ); ?>
	<?php endif; ?>

	<!-- EDITORIAL STORY -->
	<section class="section section--ivory">
		<div class="nura-container nura-split nura-reveal">
			<div class="nura-split__media">
				<img src="<?php echo esc_url( NURA_URI . 'assets/images/model-editorial.jpg' ); ?>" alt="<?php echo esc_attr( nura_opt( 'nura_brand_name' ) ); ?>" loading="lazy">
			</div>
			<div class="nura-split__body">
				<p class="nura-eyebrow"><?php echo esc_html( nura_opt( 'nura_tagline' ) ); ?></p>
				<h2><?php esc_html_e( 'Hand-crafted in Nairobi, made for you', 'nura-beauty' ); ?></h2>
				<p class="nura-lede"><?php echo esc_html( nura_opt( 'nura_bio' ) ); ?></p>
				<p><?php esc_html_e( 'Every NURA unit is sewn from verified human hair and finished by hand. Each order ships with a provenance note and a written longevity guarantee, wrapped in our signature black-and-gold ritual.', 'nura-beauty' ); ?></p>
				<a class="nura-btn nura-btn--ghost" href="<?php echo esc_url( home_url( '/about-us/' ) ); ?>"><?php esc_html_e( 'Our Story', 'nura-beauty' ); ?></a>
			</div>
		</div>
	</section>

	<!-- RAIL: MOST LOVED -->
	<?php if ( class_exists( 'WooCommerce' ) ) : ?>
	<?php nura_rail( __( 'Most loved', 'nura-beauty' ), __( 'The house favourites', 'nura-beauty' ), '[products limit="12" columns="12" orderby="popularity" class="nura-rail-products"]', $shop_url ); ?>
	<?php endif; ?>

	<!-- REVIEWS -->
	<section class="section">
		<div class="nura-container">
			<div class="nura-shead nura-reveal">
				<p class="nura-eyebrow"><?php esc_html_e( 'Loved by clients', 'nura-beauty' ); ?></p>
				<h2><?php esc_html_e( 'Crowned with confidence', 'nura-beauty' ); ?></h2>
			</div>
			<div class="nura-reviews nura-reveal">
				<?php foreach ( $reviews as $rv ) : ?>
					<figure class="nura-review">
						<div class="nura-review__stars" aria-hidden="true">&#9733;&#9733;&#9733;&#9733;&#9733;</div>
						<blockquote><?php echo esc_html( $rv['q'] ); ?></blockquote>
						<figcaption>
							<span class="nura-review__avatar"><?php echo esc_html( mb_substr( $rv['n'], 0, 1 ) ); ?></span>
							<span><strong><?php echo esc_html( $rv['n'] ); ?></strong><br><small><?php echo esc_html( $rv['r'] ); ?></small></span>
						</figcaption>
					</figure>
				<?php endforeach; ?>
			</div>
		</div>
	</section>

	<!-- RAIL: GREAT VALUE -->
	<?php if ( class_exists( 'WooCommerce' ) ) : ?>
	<?php nura_rail( __( 'Great value', 'nura-beauty' ), __( 'Under KES 5,000', 'nura-beauty' ), '[products limit="12" columns="12" orderby="price" order="ASC" class="nura-rail-products"]', $shop_url ); ?>
	<?php endif; ?>

	<!-- BOOK A FITTING -->
	<section class="section section--ivory" id="book-a-fitting">
		<div class="nura-container nura-book nura-reveal">
			<div class="nura-book__intro">
				<p class="nura-eyebrow"><?php esc_html_e( 'Personal service', 'nura-beauty' ); ?></p>
				<h2><?php esc_html_e( 'Book a free fitting or virtual consultation', 'nura-beauty' ); ?></h2>
				<p class="nura-lede"><?php esc_html_e( 'Tell us the look you want. Our stylists guide you to your perfect unit - by video call, WhatsApp or in our Nairobi studio.', 'nura-beauty' ); ?></p>
				<ul class="nura-book__points">
					<li><?php esc_html_e( 'Free 15-minute consultation', 'nura-beauty' ); ?></li>
					<li><?php esc_html_e( 'Same-day Nairobi delivery', 'nura-beauty' ); ?></li>
					<li><?php esc_html_e( 'M-Pesa, card or pay on delivery', 'nura-beauty' ); ?></li>
				</ul>
			</div>
			<form class="nura-book__form" data-nura-book data-wa="<?php echo esc_attr( $wa ); ?>">
				<label><?php esc_html_e( 'Full name', 'nura-beauty' ); ?><input type="text" name="name" required></label>
				<label><?php esc_html_e( 'Phone / WhatsApp', 'nura-beauty' ); ?><input type="tel" name="phone" required></label>
				<label><?php esc_html_e( 'What do you need?', 'nura-beauty' ); ?>
					<select name="service">
						<option><?php esc_html_e( 'Wig consultation', 'nura-beauty' ); ?></option>
						<option><?php esc_html_e( 'Bridal fitting', 'nura-beauty' ); ?></option>
						<option><?php esc_html_e( 'Virtual try-on help', 'nura-beauty' ); ?></option>
						<option><?php esc_html_e( 'Installation booking', 'nura-beauty' ); ?></option>
						<option><?php esc_html_e( 'Other', 'nura-beauty' ); ?></option>
					</select>
				</label>
				<label><?php esc_html_e( 'Preferred date', 'nura-beauty' ); ?><input type="date" name="date"></label>
				<label class="nura-book__full"><?php esc_html_e( 'Tell us more', 'nura-beauty' ); ?><textarea name="note" rows="3"></textarea></label>
				<button type="submit" class="nura-btn nura-btn--gold nura-book__full"><?php esc_html_e( 'Send via WhatsApp', 'nura-beauty' ); ?></button>
				<p class="nura-book__alt"><?php esc_html_e( 'Prefer to talk now?', 'nura-beauty' ); ?> <a href="<?php echo esc_url( $wa ); ?>"><?php esc_html_e( 'Chat on WhatsApp', 'nura-beauty' ); ?></a></p>
			</form>
		</div>
	</section>

	<!-- EXCLUSIVE FEATURES -->
	<section class="section section--ink">
		<div class="nura-container">
			<div class="nura-shead nura-reveal">
				<p class="nura-eyebrow"><?php esc_html_e( 'Only at NURA', 'nura-beauty' ); ?></p>
				<h2><?php esc_html_e( 'A first for Kenyan beauty', 'nura-beauty' ); ?></h2>
			</div>
			<div class="nura-grid nura-grid--3 nura-reveal">
				<?php
				$features = array(
					array( 'AI Wig Finder', 'Answer a few questions or upload a selfie and let NURA recommend the perfect wig for your face shape, skin tone, lifestyle and budget.', '/ai-wig-finder/' ),
					array( 'Virtual Try-On', 'Preview any wig on your own photo before you buy - see your crown before it arrives.', '/virtual-try-on/' ),
					array( 'The NURA Circle', 'Your luxury client portal: order history, care schedule, warranty certificates and loyalty rewards.', '/nura-circle/' ),
				);
				foreach ( $features as $f ) : ?>
					<a class="nura-feature" href="<?php echo esc_url( home_url( $f[2] ) ); ?>">
						<div class="icn">&#10022;</div>
						<h3><?php echo esc_html( $f[0] ); ?></h3>
						<p><?php echo esc_html( $f[1] ); ?></p>
					</a>
				<?php endforeach; ?>
			</div>
		</div>
	</section>

	<!-- CTA -->
	<section class="section text-center">
		<div class="nura-container nura-reveal">
			<p class="nura-eyebrow"><?php esc_html_e( 'Not sure where to start?', 'nura-beauty' ); ?></p>
			<h2><?php esc_html_e( 'Meet your NURA Stylist', 'nura-beauty' ); ?></h2>
			<p class="nura-lede" style="max-width:620px;margin-inline:auto"><?php esc_html_e( 'Chat with our AI stylist any time, or book a free virtual consultation and we will guide you to your perfect unit.', 'nura-beauty' ); ?></p>
			<p style="margin-top:1.4rem">
				<a class="nura-btn nura-btn--gold" href="<?php echo esc_url( home_url( '/book-appointment/' ) ); ?>"><?php esc_html_e( 'Book Now', 'nura-beauty' ); ?></a>
				<a class="nura-btn nura-btn--ghost" href="<?php echo esc_url( $wa ); ?>"><?php esc_html_e( 'Chat on WhatsApp', 'nura-beauty' ); ?></a>
			</p>
		</div>
	</section>

</main>
<?php get_footer();
