<?php
/**
 * Checkout Form
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/checkout/form-checkout.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see https://woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 9.4.0
 */

if (!defined('ABSPATH')) {
	exit;
}

do_action('woocommerce_before_checkout_form', $checkout);

// If checkout registration is disabled and not logged in, the user cannot checkout.
if (!$checkout->is_registration_enabled() && $checkout->is_registration_required() && !is_user_logged_in()) {
	echo esc_html(apply_filters('woocommerce_checkout_must_be_logged_in_message', __('You must be logged in to checkout.', 'woocommerce')));
	return;
}

?>

<div class="checkout-page-container">
	<!--<h1 class="checkout-title"><?php esc_html_e('Checkout', 'woocommerce'); ?></h1>-->

	<form name="checkout" method="post" class="checkout woocommerce-checkout"
		action="<?php echo esc_url(wc_get_checkout_url()); ?>" enctype="multipart/form-data"
		aria-label="<?php echo esc_attr__('Checkout', 'woocommerce'); ?>">

		<div class="checkout-layout">
			<div class="checkout-main">

				<?php if ($checkout->get_checkout_fields()): ?>

					<?php do_action('woocommerce_checkout_before_customer_details'); ?>

					<div id="customer_details">
						<section class="checkout-section">
							<?php do_action('woocommerce_checkout_billing'); ?>
						</section>

						<?php if (WC()->cart->needs_shipping_address() === true): ?>
							<section class="checkout-section">
								<?php do_action('woocommerce_checkout_shipping'); ?>
							</section>
						<?php endif; ?>
					</div>

					<?php do_action('woocommerce_checkout_after_customer_details'); ?>

				<?php endif; ?>

				<?php do_action('woocommerce_checkout_before_order_review_heading'); ?>

				<?php do_action('woocommerce_checkout_before_order_review'); ?>

				<section class="checkout-section payment-section">
					<h2 id="order_review_heading"><?php esc_html_e('Método de Pago', 'medical-theme'); ?></h2>
					<div id="payment_details_main">
						<?php do_action('medical_checkout_payment'); ?>
					</div>
				</section>

				<?php do_action('woocommerce_checkout_after_order_review'); ?>
			</div>

			<div class="checkout-sidebar">
				<div class="order-review-card">
					<h2><?php esc_html_e('Tu Pedido', 'medical-theme'); ?></h2>
					<div id="order_review_sidebar">
						<?php do_action('medical_checkout_order_review'); ?>
					</div>
				</div>

				<div class="secure-badge">
					<span class="material-symbols-outlined">verified_user</span>
					<p><?php esc_html_e('Checkout seguro y encriptado (SSL)', 'medical-theme'); ?></p>
				</div>
			</div>
		</div>

	</form>

</div>

<?php do_action('woocommerce_after_checkout_form', $checkout); ?>