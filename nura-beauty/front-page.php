<?php
/**
 * Homepage. Every string/image here is pulled from the Customizer (nura_opt)
 * or WooCommerce data - nothing is hardcoded.
 * @package NURA_Beauty
 */
get_header();

$hero_img  = nura_opt( 'nura_hero_image' );
if ( empty( $hero_img ) ) {
	$hero_img = NURA_URI . 'assets/images/hero.jpg';
}
$shop_url  = function_exists( 'wc_get_page_id' ) ? get_permalink( wc_get_page_id( 'shop' ) ) : home_url( '/shop/' );
?>
<main id="primary" class="site-main">

	<!-- HERO -->
	<section class="nura-hero">
		<?php if ( $hero_img ) : ?>
			<div class="nura-hero__media"><img src="<?php echo esc_url( $hero_img ); ?>" alt="" fetchpriority="high" width="1920" height="1080"></div>
		<?php endif; ?>
		<div class="nura-container nura-hero__inner nura-reveal">
			<p class="nura-eyebrow"><?php echo esc_html( nura_opt( 'nura_hero_eyebrow' ) ); ?></p>
			<h1><?php echo esc_html( nura_opt( 'nura_hero_title' ) ); ?></h1>
			<p class="nura-lede"><?php echo esc_html( nura_opt( 'nura_hero_subtitle' ) ); ?></p>
			<div class="nura-hero__cta">
				<a class="nura-btn nura-btn--gold" href="<?php echo esc_url( $shop_url ); ?>"><?php echo esc_html( nura_opt( 'nura_hero_cta' ) ); ?></a>
				<a class="nura-btn nura-btn--ghost" href="<?php echo esc_url( home_url( '/book-appointment/' ) ); ?>"><?php echo esc_html( nura_opt( 'nura_hero_cta2' ) ); ?></a>
			</div>
		</div>
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
				<p><?php esc_html_e( 'Six collections in view - swipe or use the arrows to explore the full house.', 'nura-beauty' ); ?></p>
			</div>
			<div class="nura-cats-wrap nura-reveal">
				<button type="button" class="nura-cats-nav nura-cats-nav--prev" aria-label="<?php esc_attr_e( 'Previous collections', 'nura-beauty' ); ?>" data-nura-cats-prev>&#8249;</button>
				<div class="nura-cats" data-nura-cats>
					<?php
					$cats = get_terms( array(
						'taxonomy'   => 'product_cat',
						'hide_empty' => false,
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
							$count    = (int) $cat->count;
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
	<?php endif; ?>

	<!-- BESTSELLERS -->
	<section class="section section--ivory">
		<div class="nura-container">
			<div class="nura-shead nura-reveal">
				<p class="nura-eyebrow"><?php esc_html_e( 'Most Loved', 'nura-beauty' ); ?></p>
				<h2><?php esc_html_e( 'The house favourites', 'nura-beauty' ); ?></h2>
			</div>
			<div class="nura-reveal">
				<?php echo do_shortcode( '[products limit="6" columns="3" orderby="popularity" class="nura-home-products"]' ); ?>
			</div>
			<p class="text-center" style="margin-top:2rem"><a class="nura-btn" href="<?php echo esc_url( $shop_url ); ?>"><?php esc_html_e( 'View all wigs', 'nura-beauty' ); ?></a></p>
		</div>
	</section>

	<!-- EDITORIAL STORY -->
	<section class="section">
		<div class="nura-container nura-split nura-reveal">
			<div class="nura-split__media">
				<img src="<?php echo esc_url( nura_opt( 'nura_default_share_image' ) ? nura_opt( 'nura_default_share_image' ) : NURA_URI . 'assets/images/model-editorial.jpg' ); ?>" alt="<?php echo esc_attr( nura_opt( 'nura_brand_name' ) ); ?>" loading="lazy">
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
					array( 'AI Wig Finder', 'Upload a selfie and let NURA recommend the perfect wig for your face shape, skin tone, lifestyle and budget.', '/ai-wig-finder/' ),
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
	<section class="section section--ivory text-center">
		<div class="nura-container nura-reveal">
			<p class="nura-eyebrow"><?php esc_html_e( 'Not sure where to start?', 'nura-beauty' ); ?></p>
			<h2><?php esc_html_e( 'Book a free virtual consultation', 'nura-beauty' ); ?></h2>
			<p class="nura-lede" style="max-width:620px;margin-inline:auto"><?php esc_html_e( 'Tell us the look you want and we will guide you to your perfect unit - by video call or in-studio.', 'nura-beauty' ); ?></p>
			<p style="margin-top:1.4rem">
				<a class="nura-btn nura-btn--gold" href="<?php echo esc_url( home_url( '/book-appointment/' ) ); ?>"><?php esc_html_e( 'Book Now', 'nura-beauty' ); ?></a>
				<a class="nura-btn nura-btn--ghost" href="<?php echo esc_url( nura_opt( 'nura_whatsapp' ) ); ?>"><?php esc_html_e( 'Chat on WhatsApp', 'nura-beauty' ); ?></a>
			</p>
		</div>
	</section>

</main>
<?php get_footer();
