<?php
/**
 * Исправления для работы checkout на боевом сервере
 * Решение проблем с сессиями и корзиной
 */

/**
 * Принудительная инициализация сессии WooCommerce
 * Особенно важно для серверов типа Beget
 */
function asker_force_wc_session_init() {
    // Инициализируем сессию только для WooCommerce страниц
    if ( ! is_admin() && class_exists( 'WooCommerce' ) ) {
        // Убеждаемся что сессия создана
        if ( ! WC()->session || ! WC()->session->has_session() ) {
            WC()->session->set_customer_session_cookie( true );
        }
        
        // Debug: логируем состояние сессии
        if ( is_cart() || is_checkout() ) {
            error_log( 'Session Check - Has Session: ' . ( WC()->session->has_session() ? 'YES' : 'NO' ) );
            error_log( 'Session Check - Customer ID: ' . WC()->session->get_customer_id() );
        }
    }
}
add_action( 'init', 'asker_force_wc_session_init', 5 );

/**
 * Сохраняем корзину перед переходом на checkout
 */
function asker_preserve_cart_before_checkout() {
    if ( is_cart() && ! is_admin() && ! wp_doing_ajax() ) {
        // Принудительно сохраняем корзину в сессию
        if ( WC()->cart && ! WC()->cart->is_empty() ) {
            WC()->session->set( 'cart', WC()->cart->get_cart_for_session() );
            WC()->session->set( 'cart_totals', WC()->cart->get_totals() );
            error_log( 'Cart preserved: ' . WC()->cart->get_cart_contents_count() . ' items' );
        }
    }
}
add_action( 'wp_footer', 'asker_preserve_cart_before_checkout' );

/**
 * Восстанавливаем корзину на странице checkout
 */
function asker_restore_cart_on_checkout() {
    if ( is_checkout() && ! is_order_received_page() && ! is_admin() && ! wp_doing_ajax() ) {
        // Проверяем, пуста ли корзина
        if ( WC()->cart && WC()->cart->is_empty() ) {
            error_log( 'Cart is empty on checkout, attempting to restore...' );
            
            // Пробуем восстановить из сессии
            $saved_cart = WC()->session->get( 'cart' );
            if ( ! empty( $saved_cart ) && is_array( $saved_cart ) ) {
                foreach ( $saved_cart as $cart_item_key => $cart_item ) {
                    if ( isset( $cart_item['product_id'] ) && isset( $cart_item['quantity'] ) ) {
                        WC()->cart->add_to_cart(
                            $cart_item['product_id'],
                            $cart_item['quantity'],
                            isset( $cart_item['variation_id'] ) ? $cart_item['variation_id'] : 0,
                            isset( $cart_item['variation'] ) ? $cart_item['variation'] : array()
                        );
                    }
                }
                error_log( 'Cart restored: ' . WC()->cart->get_cart_contents_count() . ' items' );
            } else {
                error_log( 'No saved cart found in session' );
            }
        } else {
            error_log( 'Cart has items: ' . WC()->cart->get_cart_contents_count() );
        }
    }
}
add_action( 'template_redirect', 'asker_restore_cart_on_checkout', 1 );

/**
 * Отключаем редирект пустой корзины ПРАВИЛЬНО
 * Возвращаем true, чтобы отключить редирект
 */
function asker_allow_empty_cart_checkout( $redirect ) {
    // Разрешаем показывать checkout даже если корзина пуста
    // (чтобы мы могли вывести свое сообщение и восстановить корзину)
    return true;
}
add_filter( 'woocommerce_checkout_redirect_empty_cart', 'asker_allow_empty_cart_checkout', 999 );

/**
 * Увеличиваем время жизни сессии WooCommerce
 */
function asker_extend_session_expiration( $expiration ) {
    // Увеличиваем с 2 дней до 7 дней
    return 60 * 60 * 24 * 7; // 7 дней
}
add_filter( 'wc_session_expiration', 'asker_extend_session_expiration' );

/**
 * Принудительно включаем cookies для корзины
 */
function asker_force_cart_cookies() {
    if ( ! is_admin() ) {
        // Устанавливаем cookie для корзины
        add_filter( 'woocommerce_set_cart_cookies', '__return_true' );
    }
}
add_action( 'init', 'asker_force_cart_cookies', 1 );

/**
 * Debug: Показываем информацию о корзине для авторизованных админов
 */
function asker_debug_cart_info() {
    if ( ( is_cart() || is_checkout() ) && current_user_can( 'manage_options' ) && isset( $_GET['debug'] ) ) {
        echo '<div style="background: #fff; border: 2px solid #f00; padding: 20px; margin: 20px; font-family: monospace;">';
        echo '<h3>🔍 Debug Cart Information</h3>';
        echo '<p><strong>Session ID:</strong> ' . ( WC()->session ? WC()->session->get_customer_id() : 'NO SESSION' ) . '</p>';
        echo '<p><strong>Has Session:</strong> ' . ( WC()->session && WC()->session->has_session() ? 'YES' : 'NO' ) . '</p>';
        echo '<p><strong>Cart Items:</strong> ' . ( WC()->cart ? WC()->cart->get_cart_contents_count() : 'NO CART' ) . '</p>';
        echo '<p><strong>Cart Empty:</strong> ' . ( WC()->cart && WC()->cart->is_empty() ? 'YES' : 'NO' ) . '</p>';
        echo '<p><strong>Session Cart Data:</strong> ' . ( WC()->session->get( 'cart' ) ? 'EXISTS' : 'EMPTY' ) . '</p>';
        
        if ( WC()->cart && ! WC()->cart->is_empty() ) {
            echo '<h4>Cart Contents:</h4>';
            echo '<pre>' . print_r( WC()->cart->get_cart(), true ) . '</pre>';
        }
        
        echo '<h4>Session Data:</h4>';
        echo '<pre>' . print_r( WC()->session->get_session_data(), true ) . '</pre>';
        echo '</div>';
    }
}
add_action( 'wp_footer', 'asker_debug_cart_info' );

