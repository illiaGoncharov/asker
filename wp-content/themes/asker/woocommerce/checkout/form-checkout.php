<?php
/**
 * Кастомный шаблон формы чекаута
 *
 * @see     https://docs.woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 9.4.0
 */

defined( 'ABSPATH' ) || exit;

do_action( 'woocommerce_checkout_before_customer_details' );
?>


<!-- Левая колонка - формы -->
<div class="woocommerce-checkout">
    
    <?php do_action( 'woocommerce_checkout_billing' ); ?>
    
    <?php do_action( 'woocommerce_checkout_shipping' ); ?>
    
    <?php do_action( 'woocommerce_checkout_after_customer_details' ); ?>
    
</div>

<!-- Правая колонка - сводка заказа -->
<div class="woocommerce-checkout-review-order">
    
    <?php do_action( 'woocommerce_checkout_before_order_review' ); ?>
    
    <h3 id="order_review_heading"><?php esc_html_e( 'Ваш заказ', 'woocommerce' ); ?></h3>
    
    <div id="order_review" class="woocommerce-checkout-review-order">
        <?php do_action( 'woocommerce_checkout_order_review' ); ?>
    </div>
    
    <?php do_action( 'woocommerce_checkout_after_order_review' ); ?>
    
</div>

<?php do_action( 'woocommerce_checkout_after_customer_details' ); ?>
