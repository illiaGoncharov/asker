<?php
/**
 * Google Analytics 4 интеграция
 * Простая базовая настройка
 */

/**
 * Добавляем код GA4 в header
 */
function asker_add_ga4_code() {
    // Получаем GA4 ID из настроек ACF или константы
    $ga4_id = get_field( 'ga4_measurement_id', 'option' );
    
    // Если нет в ACF, проверяем константу wp-config.php
    if ( ! $ga4_id && defined( 'ASKER_GA4_ID' ) ) {
        $ga4_id = ASKER_GA4_ID;
    }
    
    // Если нет ID - не выводим код
    if ( ! $ga4_id ) {
        return;
    }
    
    ?>
    <!-- Google Analytics 4 -->
    <script async src="https://www.googletagmanager.com/gtag/js?id=<?php echo esc_attr( $ga4_id ); ?>"></script>
    <script>
        window.dataLayer = window.dataLayer || [];
        function gtag(){dataLayer.push(arguments);}
        gtag('js', new Date());
        gtag('config', '<?php echo esc_js( $ga4_id ); ?>');
    </script>
    <?php
}
add_action( 'wp_head', 'asker_add_ga4_code', 10 );

/**
 * Отслеживание добавления товара в корзину через JavaScript событие
 * Используем событие added_to_cart, которое триггерится WooCommerce
 */
function asker_add_cart_tracking_script() {
    if ( is_admin() ) {
        return;
    }
    ?>
    <script>
    (function() {
        // Отслеживание добавления в корзину через событие WooCommerce
        document.body.addEventListener('added_to_cart', function(event, fragments, cartHash, $button) {
            if (typeof gtag === 'undefined') return;
            
            // Получаем данные из кнопки или из события
            const button = $button && $button.length ? $button[0] : (event.target || null);
            if (!button) return;
            
            const productId = button.getAttribute('data-product-id') || button.closest('[data-product-id]')?.getAttribute('data-product-id');
            if (!productId) return;
            
            // Получаем quantity из кнопки или из формы
            let quantity = parseInt(button.getAttribute('data-quantity') || button.closest('form')?.querySelector('input.qty')?.value || 1, 10);
            if (isNaN(quantity) || quantity < 1) quantity = 1;
            
            // Отправляем событие в GA4
            // Примечание: точную цену и название можно получить через AJAX, но для простоты используем базовые данные
            gtag('event', 'add_to_cart', {
                'currency': 'RUB',
                'items': [{
                    'item_id': productId.toString(),
                    'quantity': quantity
                }]
            });
        });
    })();
    </script>
    <?php
}
add_action( 'wp_footer', 'asker_add_cart_tracking_script' );

/**
 * Отслеживание покупки (на странице благодарности)
 */
function asker_track_purchase() {
    if ( ! is_order_received_page() ) {
        return;
    }
    
    $order_id = get_query_var( 'order-received' );
    if ( ! $order_id ) {
        return;
    }
    
    $order = wc_get_order( $order_id );
    if ( ! $order ) {
        return;
    }
    
    $items = array();
    foreach ( $order->get_items() as $item ) {
        $product = $item->get_product();
        if ( $product ) {
            $items[] = array(
                'item_id' => (string) $product->get_id(),
                'item_name' => $item->get_name(),
                'price' => (float) $order->get_item_total( $item ),
                'quantity' => (int) $item->get_quantity(),
            );
        }
    }
    
    ?>
    <script>
    if (typeof gtag !== 'undefined') {
        gtag('event', 'purchase', {
            'transaction_id': '<?php echo esc_js( $order->get_order_number() ); ?>',
            'value': <?php echo esc_js( $order->get_total() ); ?>,
            'currency': 'RUB',
            'items': <?php echo json_encode( $items ); ?>
        });
    }
    </script>
    <?php
}
add_action( 'wp_footer', 'asker_track_purchase' );

/**
 * Отслеживание начала оформления заказа
 */
function asker_track_begin_checkout() {
    if ( ! is_checkout() ) {
        return;
    }
    
    $cart = WC()->cart;
    if ( ! $cart || $cart->is_empty() ) {
        return;
    }
    
    $items = array();
    foreach ( $cart->get_cart() as $cart_item_key => $cart_item ) {
        $product = $cart_item['data'];
        if ( $product ) {
            $items[] = array(
                'item_id' => (string) $product->get_id(),
                'item_name' => $product->get_name(),
                'price' => (float) $product->get_price(),
                'quantity' => (int) $cart_item['quantity'],
            );
        }
    }
    
    ?>
    <script>
    if (typeof gtag !== 'undefined') {
        gtag('event', 'begin_checkout', {
            'value': <?php echo esc_js( $cart->get_total( 'edit' ) ); ?>,
            'currency': 'RUB',
            'items': <?php echo json_encode( $items ); ?>
        });
    }
    </script>
    <?php
}
add_action( 'wp_footer', 'asker_track_begin_checkout' );

