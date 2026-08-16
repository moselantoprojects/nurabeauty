<?php
/**
 * Footer.
 * @package NURA_Beauty
 */
$payments = array_filter( array_map( 'trim', explode( ',', (string) nura_opt( 'nura_payments' ) ) ) );
?>
<footer class="site-footer" id="site-footer">
	<div class="nura-container">
		<div class="nura-foot-grid">
			<div class="nura-foot-col nura-foot-brand">
				<?php if ( has_custom_logo() ) { the_custom_logo(); } else { echo '<div class="nura-logo">' . esc_html( nura_opt( 'nura_brand_name' ) ) . '</div>'; } ?>
				<p><?php echo esc_html( nura_opt( 'nura_bio' ) ); ?></p>
				<div class="nura-newsletter">
					<h4><?php esc_html_e( 'Join The House', 'nura-beauty' ); ?></h4>
					<form action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" method="post">
						<input type="hidden" name="action" value="nura_subscribe">
						<input type="email" name="nura_email" placeholder="<?php esc_attr_e( 'Your email', 'nura-beauty' ); ?>" required>
						<button type="submit" class="nura-btn nura-btn--gold" style="width:100%"><?php esc_html_e( 'Subscribe', 'nura-beauty' ); ?></button>
					</form>
				</div>
			</div>

			<div class="nura-foot-col">
				<h4><?php esc_html_e( 'Shop', 'nura-beauty' ); ?></h4>
				<?php wp_nav_menu( array( 'theme_location' => 'footer_shop', 'container' => false, 'fallback_cb' => false, 'depth' => 1 ) ); ?>
			</div>
			<div class="nura-foot-col">
				<h4><?php esc_html_e( 'Help & Support', 'nura-beauty' ); ?></h4>
				<?php wp_nav_menu( array( 'theme_location' => 'footer_help', 'container' => false, 'fallback_cb' => false, 'depth' => 1 ) ); ?>
			</div>
			<div class="nura-foot-col">
				<h4><?php esc_html_e( 'The House', 'nura-beauty' ); ?></h4>
				<?php wp_nav_menu( array( 'theme_location' => 'footer_company', 'container' => false, 'fallback_cb' => false, 'depth' => 1 ) ); ?>
				<ul>
					<li><a href="<?php echo esc_url( nura_opt( 'nura_whatsapp' ) ); ?>"><?php echo esc_html( nura_opt( 'nura_phone' ) ); ?></a></li>
					<li><a href="mailto:<?php echo esc_attr( nura_opt( 'nura_email' ) ); ?>"><?php echo esc_html( nura_opt( 'nura_email' ) ); ?></a></li>
					<li><?php echo esc_html( nura_opt( 'nura_address' ) ); ?></li>
				</ul>
			</div>
		</div>

		<div class="nura-foot-bottom">
			<span class="nura-signoff"><?php echo esc_html( nura_opt( 'nura_signoff' ) ); ?></span>
			<?php if ( $payments ) : ?>
			<div class="nura-pay">
				<?php foreach ( $payments as $p ) : ?><span><?php echo esc_html( $p ); ?></span><?php endforeach; ?>
			</div>
			<?php endif; ?>
			<span>&copy; <?php echo esc_html( date_i18n( 'Y' ) ); ?> <?php echo esc_html( nura_opt( 'nura_brand_name' ) ); ?>. <?php esc_html_e( 'All rights reserved.', 'nura-beauty' ); ?></span>
		</div>
	</div>
</footer>

<?php
$wa = nura_opt( 'nura_whatsapp' );
if ( $wa ) {
	printf( '<a class="nura-wa" href="%s" aria-label="%s" target="_blank" rel="noopener">WA</a>', esc_url( $wa ), esc_attr__( 'Chat on WhatsApp', 'nura-beauty' ) );
}
?>
<?php wp_footer(); ?>
</body>
</html>
