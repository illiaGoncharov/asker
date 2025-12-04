<?php
/**
 * Исправления для работы checkout на боевом сервере
 * Решение проблем с сессиями и корзиной (Beget и подобные хостинги)
 */

/**
 * Принудительная инициализация сессии WooCommerce
 * Критично для серверов с особыми настройками PHP сессий
 */
function asker_force_wc_session_init() {
    if ( ! is_admin() && class_exists( 'WooCommerce' ) && WC()->session ) {
        // Убеждаемся что сессия создана на страницах магазина
        if ( ! WC()->session->has_session() ) {
            WC()->session->set_customer_session_cookie( true );
        }
    }
}
add_action( 'init', 'asker_force_wc_session_init', 5 );

/**
 * Восстанавливаем корзину на странице checkout если она пуста
 * Это нативный подход через стандартные методы WooCommerce
 */
function asker_restore_cart_on_checkout() {
    if ( is_checkout() && ! is_order_received_page() && ! is_admin() && ! wp_doing_ajax() ) {
        // Если корзина пуста, пробуем восстановить из сессии
        if ( WC()->cart && WC()->cart->is_empty() && WC()->session ) {
            $saved_cart = WC()->session->get( 'cart' );
            if ( ! empty( $saved_cart ) && is_array( $saved_cart ) ) {
                foreach ( $saved_cart as $cart_item ) {
                    if ( isset( $cart_item['product_id'], $cart_item['quantity'] ) ) {
                        WC()->cart->add_to_cart(
                            $cart_item['product_id'],
                            $cart_item['quantity'],
                            $cart_item['variation_id'] ?? 0,
                            $cart_item['variation'] ?? array()
                        );
                    }
                }
            }
        }
    }
}
add_action( 'template_redirect', 'asker_restore_cart_on_checkout', 1 );

/**
 * Увеличиваем время жизни сессии WooCommerce с 2 до 7 дней
 */
function asker_extend_session_expiration( $expiration ) {
    return 60 * 60 * 24 * 7; // 7 дней
}
add_filter( 'wc_session_expiration', 'asker_extend_session_expiration' );

/**
 * Принудительно включаем cookies для корзины
 */
function asker_force_cart_cookies() {
    if ( ! is_admin() ) {
        add_filter( 'woocommerce_set_cart_cookies', '__return_true' );
    }
}
add_action( 'init', 'asker_force_cart_cookies', 1 );
