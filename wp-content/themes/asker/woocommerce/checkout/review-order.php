<?php
/**
 * Кастомный шаблон блока заказа в чекауте
 */

defined( 'ABSPATH' ) || exit;

do_action( 'woocommerce_checkout_before_order_review' );
?>

<div id="order_review" class="woocommerce-checkout-review-order">
    
    <?php do_action( 'woocommerce_checkout_order_review' ); ?>
    
    <!-- Сводка заказа -->
    <div class="checkout__order-totals">
        <div class="checkout__total-row">
            <span class="checkout__total-label">Итого:</span>
            <span class="checkout__total-value"><?php wc_cart_totals_subtotal_html(); ?></span>
        </div>
        
        <?php if ( WC()->cart->get_discount_total() > 0 ) : ?>
        <div class="checkout__total-row checkout__total-row--discount">
            <span class="checkout__total-label">Скидка:</span>
            <span class="checkout__total-value">-<?php echo wc_price( WC()->cart->get_discount_total() ); ?></span>
        </div>
        <?php endif; ?>
        
        <div class="checkout__total-row">
            <span class="checkout__total-label">Способ оплаты:</span>
            <span class="checkout__total-value">По счету</span>
        </div>
        
        <div class="checkout__total-row checkout__total-row--final">
            <span class="checkout__total-label">К оплате:</span>
            <span class="checkout__total-value"><?php wc_cart_totals_order_total_html(); ?></span>
        </div>
    </div>
    
    <!-- Кнопка подтверждения -->
    <div class="checkout__submit">
        <button type="submit" class="checkout__submit-btn" name="woocommerce_checkout_place_order" id="place_order" value="<?php esc_attr_e( 'Place order', 'woocommerce' ); ?>" data-value="<?php esc_attr_e( 'Place order', 'woocommerce' ); ?>">
            Подтвердить заказ
        </button>
    </div>
    
    <!-- Дополнительная информация -->
    <div class="checkout__order-info">
        <p class="checkout__info-text">НДС включен в стоимость товаров</p>
        <p class="checkout__info-text">Стоимость доставки рассчитывается отдельно</p>
    </div>
    
</div>

<?php do_action( 'woocommerce_checkout_after_order_review' ); ?>
