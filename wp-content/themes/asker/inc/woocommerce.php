<?php
/**
 * Базовая интеграция WooCommerce. Без агрессивных оверрайдов.
 */

// Пример: включить поддержку миниатюр галереи (по мере необходимости)
// add_theme_support('wc-product-gallery-zoom');
// add_theme_support('wc-product-gallery-lightbox');
// add_theme_support('wc-product-gallery-slider');

/**
 * Убедиться, что сессия WooCommerce инициализирована
 */
function asker_ensure_cart_session() {
    if ( function_exists( 'WC' ) && WC()->session && ! WC()->session->has_session() ) {
        WC()->session->set_customer_session_cookie( true );
    }
}
add_action( 'wp_loaded', 'asker_ensure_cart_session', 5 );

/**
 * Создание страниц WooCommerce и контентных страниц при активации темы
 */
function asker_create_woocommerce_pages() {
    // Проверяем, установлен ли WooCommerce
    if (!class_exists('WooCommerce')) {
        return;
    }

    // Создаем страницу магазина
    $shop_page = get_page_by_path('shop');
    if (!$shop_page) {
        $shop_id = wp_insert_post([
            'post_title' => 'Каталог',
            'post_name' => 'shop',
            'post_content' => '[products]',
            'post_status' => 'publish',
            'post_type' => 'page',
        ]);
        
        if ($shop_id && !is_wp_error($shop_id)) {
            update_option('woocommerce_shop_page_id', $shop_id);
        }
    } else {
        // Обновляем настройки WooCommerce, если страница уже существует
        update_option('woocommerce_shop_page_id', $shop_page->ID);
    }
    
    // Создаем контентные страницы
    $content_pages = [
        'payment' => ['title' => 'Оплата', 'template' => 'page-payment.php'],
        'delivery' => ['title' => 'Доставка', 'template' => 'page-delivery.php'],
        'guarantees' => ['title' => 'Гарантии', 'template' => 'page-guarantees.php'],
        'about' => ['title' => 'О компании', 'template' => 'page-about.php'],
        'contacts' => ['title' => 'Контакты', 'template' => 'page-contacts.php'],
    ];
    
    foreach ($content_pages as $slug => $data) {
        $page = get_page_by_path($slug);
        if (!$page) {
            $page_id = wp_insert_post([
                'post_title' => $data['title'],
                'post_name' => $slug,
                'post_content' => '',
                'post_status' => 'publish',
                'post_type' => 'page',
            ]);
            
            if ($page_id && !is_wp_error($page_id)) {
                update_post_meta($page_id, '_wp_page_template', $data['template']);
            }
        }
    }

    // Создаем страницу корзины
    $cart_page = get_page_by_path('cart');
    if (!$cart_page) {
        $cart_id = wp_insert_post([
            'post_title' => 'Корзина',
            'post_name' => 'cart',
            'post_content' => '[woocommerce_cart]',
            'post_status' => 'publish',
            'post_type' => 'page',
        ]);
        
        if ($cart_id && !is_wp_error($cart_id)) {
            update_option('woocommerce_cart_page_id', $cart_id);
        }
    } else {
        update_option('woocommerce_cart_page_id', $cart_page->ID);
    }

    // Создаем страницу оформления заказа
    $checkout_page = get_page_by_path('checkout');
    if (!$checkout_page) {
        $checkout_id = wp_insert_post([
            'post_title' => 'Оформление заказа',
            'post_name' => 'checkout',
            'post_content' => '[woocommerce_checkout]',
            'post_status' => 'publish',
            'post_type' => 'page',
        ]);
        
        if ($checkout_id && !is_wp_error($checkout_id)) {
            update_option('woocommerce_checkout_page_id', $checkout_id);
        }
    } else {
        update_option('woocommerce_checkout_page_id', $checkout_page->ID);
    }

    // Создаем страницу моего аккаунта
    $account_page = get_page_by_path('my-account');
    if (!$account_page) {
        $account_id = wp_insert_post([
            'post_title' => 'Мой аккаунт',
            'post_name' => 'my-account',
            'post_content' => '[woocommerce_my_account]',
            'post_status' => 'publish',
            'post_type' => 'page',
        ]);
        
        if ($account_id && !is_wp_error($account_id)) {
            update_option('woocommerce_myaccount_page_id', $account_id);
        }
    } else {
        update_option('woocommerce_myaccount_page_id', $account_page->ID);
    }

    // Создаем страницу избранного
    $wishlist_page = get_page_by_path('wishlist');
    if (!$wishlist_page) {
        wp_insert_post([
            'post_title' => 'Избранное',
            'post_name' => 'wishlist',
            'post_content' => 'Список избранных товаров будет здесь.',
            'post_status' => 'publish',
            'post_type' => 'page',
        ]);
    }
}

// Запускаем создание страниц при активации темы
add_action('after_switch_theme', 'asker_create_woocommerce_pages');

/**
 * AJAX: вернуть количество товаров в корзине
 */
function asker_ajax_get_cart_count() {
    if ( function_exists( 'WC' ) && WC()->cart ) {
        // Получаем корзину
        $cart_contents = WC()->cart->get_cart();
        
        // Проверяем каждый товар на валидность
        $valid_count = 0;
        $invalid_items = [];
        
        foreach ( $cart_contents as $cart_item_key => $cart_item ) {
            // Проверяем, есть ли product_id и data
            if ( isset( $cart_item['product_id'] ) && isset( $cart_item['data'] ) && $cart_item['data'] ) {
                $product = $cart_item['data'];
                // Проверяем, что товар существует и доступен для покупки
                if ( $product->exists() && $product->is_purchasable() ) {
                    $valid_count += intval( $cart_item['quantity'] );
                } else {
                    $invalid_items[] = $cart_item_key;
                }
            } else {
                $invalid_items[] = $cart_item_key;
            }
        }
        
        // Удаляем недействительные товары
        foreach ( $invalid_items as $cart_item_key ) {
            WC()->cart->remove_cart_item( $cart_item_key );
        }
        
        // Если удалили что-то, пересчитываем корзину
        if ( ! empty( $invalid_items ) ) {
            WC()->cart->calculate_totals();
            $valid_count = WC()->cart->get_cart_contents_count();
        }
        
        wp_send_json_success( [
            'count' => $valid_count,
            'removed_invalid' => count( $invalid_items )
        ] );
    }
    wp_send_json_success( [ 'count' => 0 ] );
}
add_action( 'wp_ajax_asker_get_cart_count', 'asker_ajax_get_cart_count' );
add_action( 'wp_ajax_nopriv_asker_get_cart_count', 'asker_ajax_get_cart_count' );

/**
 * AJAX: очистить корзину на сервере
 */
function asker_ajax_clear_cart() {
    if ( function_exists( 'WC' ) && WC()->cart ) {
        // Получаем все товары в корзине
        $cart_items = WC()->cart->get_cart();
        
        // Удаляем каждый товар по отдельности
        foreach ( $cart_items as $cart_item_key => $cart_item ) {
            WC()->cart->remove_cart_item( $cart_item_key );
        }
        
        // Дополнительно очищаем корзину
        WC()->cart->empty_cart();
        
        // Очищаем сессию корзины
        if ( WC()->session ) {
            WC()->session->set( 'cart', array() );
        }
        
        // Проверяем, что корзина действительно пустая
        $count = WC()->cart->get_cart_contents_count();
        
        wp_send_json_success( [ 
            'message' => 'Корзина очищена',
            'count' => $count,
            'removed_items' => count( $cart_items )
        ] );
    }
    wp_send_json_error( [ 'message' => 'Ошибка очистки корзины' ] );
}
add_action( 'wp_ajax_asker_clear_cart', 'asker_ajax_clear_cart' );
add_action( 'wp_ajax_nopriv_asker_clear_cart', 'asker_ajax_clear_cart' );

/**
 * AJAX: обновить количество товара в корзине
 */
function asker_ajax_update_cart_item() {
    if ( ! isset( $_POST['cart_item_key'] ) || ! isset( $_POST['quantity'] ) ) {
        wp_send_json_error( [ 'message' => 'Неверные параметры' ] );
    }
    
    $cart_item_key = sanitize_text_field( $_POST['cart_item_key'] );
    $quantity = absint( $_POST['quantity'] );
    
    if ( function_exists( 'WC' ) && WC()->cart ) {
        WC()->cart->set_quantity( $cart_item_key, $quantity );
        wp_send_json_success( [ 'message' => 'Корзина обновлена' ] );
    }
    
    wp_send_json_error( [ 'message' => 'Ошибка обновления' ] );
}
add_action( 'wp_ajax_update_cart_item', 'asker_ajax_update_cart_item' );
add_action( 'wp_ajax_nopriv_update_cart_item', 'asker_ajax_update_cart_item' );

/**
 * AJAX: удалить товар из корзины
 */
function asker_ajax_remove_cart_item() {
    if ( ! isset( $_POST['cart_item_key'] ) ) {
        wp_send_json_error( [ 'message' => 'Неверные параметры' ] );
    }
    
    $cart_item_key = sanitize_text_field( $_POST['cart_item_key'] );
    
    if ( function_exists( 'WC' ) && WC()->cart ) {
        WC()->cart->remove_cart_item( $cart_item_key );
        wp_send_json_success( [ 'message' => 'Товар удален' ] );
    }
    
    wp_send_json_error( [ 'message' => 'Ошибка удаления' ] );
}

/**
 * Переопределяем шаблон карточки товара в цикле
 */
function asker_custom_product_card_template() {
    // Убираем стандартные хуки WooCommerce
    remove_action( 'woocommerce_before_shop_loop_item', 'woocommerce_template_loop_product_link_open', 10 );
    remove_action( 'woocommerce_after_shop_loop_item', 'woocommerce_template_loop_product_link_close', 5 );
    remove_action( 'woocommerce_after_shop_loop_item', 'woocommerce_template_loop_add_to_cart', 10 );
    
    // Добавляем кастомные хуки
    add_action( 'woocommerce_before_shop_loop_item', 'asker_custom_product_link_open', 10 );
    add_action( 'woocommerce_after_shop_loop_item', 'asker_custom_product_link_close', 5 );
    add_action( 'woocommerce_after_shop_loop_item', 'asker_custom_add_to_cart_button', 10 );
}
add_action( 'init', 'asker_custom_product_card_template' );

/**
 * Кастомное открытие ссылки на товар
 */
function asker_custom_product_link_open() {
    global $product;
    echo '<a href="' . esc_url( get_permalink( $product->get_id() ) ) . '" class="woocommerce-loop-product__link">';
}

/**
 * Кастомное закрытие ссылки на товар
 */
function asker_custom_product_link_close() {
    echo '</a>';
}

/**
 * Кастомная кнопка "В корзину"
 */
function asker_custom_add_to_cart_button() {
    global $product;
    echo '<div class="shop-product-actions">';
    woocommerce_template_loop_add_to_cart();
    echo '</div>';
}

/**
 * Добавляем кнопку избранного в карточку товара
 */
function asker_add_favorite_button_to_product_card() {
    global $product;
    echo '<button class="favorite-btn" data-product-id="' . esc_attr( $product->get_id() ) . '"></button>';
}
add_action( 'woocommerce_before_shop_loop_item_title', 'asker_add_favorite_button_to_product_card', 15 );

/**
 * Настройка валюты WooCommerce
 */
function asker_set_woocommerce_currency() {
    update_option( 'woocommerce_currency', 'RUB' );
    update_option( 'woocommerce_currency_symbol', 'руб.' );
    update_option( 'woocommerce_price_thousand_sep', ',' );
    update_option( 'woocommerce_price_decimal_sep', '.' );
    update_option( 'woocommerce_price_num_decimals', 0 );
}
add_action( 'after_switch_theme', 'asker_set_woocommerce_currency' );

/**
 * Изменяем формат цены: число + "руб." (рубль после числа)
 */
function asker_change_price_format( $format, $currency_pos ) {
    $format = '%2$s %1$s';
    return $format;
}
add_filter( 'woocommerce_price_format', 'asker_change_price_format', 10, 2 );

/**
 * Изменяем символ валюты
 */
function asker_change_currency_symbol( $symbol, $currency ) {
    if ( $currency == 'RUB' ) {
        $symbol = 'руб.';
    }
    return $symbol;
}
add_filter( 'woocommerce_currency_symbol', 'asker_change_currency_symbol', 10, 2 );

/**
 * Убираем копейки из цены
 */
function asker_remove_decimals( $decimals ) {
    return 0;
}
add_filter( 'woocommerce_price_num_decimals', 'asker_remove_decimals' );

/**
 * Отключаем блочный чекаут WooCommerce и используем классический
 */
function asker_disable_block_checkout() {
    return false;
}
add_filter( 'woocommerce_checkout_is_block_based', 'asker_disable_block_checkout', 10 );

/**
 * Устанавливаем Россию как страной по умолчанию
 */
function asker_set_default_country() {
    return 'RU';
}
add_filter( 'default_checkout_billing_country', 'asker_set_default_country' );
add_filter( 'default_checkout_shipping_country', 'asker_set_default_country' );

/**
 * Отключаем редирект с чекаута на корзину для тестирования
 */
function asker_disable_checkout_redirect() {
    return false;
}
add_filter( 'woocommerce_checkout_redirect_empty_cart', 'asker_disable_checkout_redirect' );

/**
 * Переопределяем шаблон чекаута через template_include (правильный подход)
 */
function asker_override_checkout_template( $template ) {
    // Защита от рекурсии - проверяем, что мы не в процессе загрузки нашего же шаблона
    static $loading = false;
    if ( $loading ) {
        return $template;
    }
    
    if ( function_exists('is_checkout') && function_exists('is_order_received_page') && is_checkout() && ! is_order_received_page() ) {
        $custom_template = get_template_directory() . '/woocommerce/checkout.php';
        if ( file_exists( $custom_template ) ) {
            $loading = true;
            return $custom_template;
        }
    }
    return $template;
}
add_filter( 'template_include', 'asker_override_checkout_template', 20 );

/**
 * Сохраняем данные формы чекаута в профиль пользователя
 */
function asker_save_checkout_data() {
    if ( ! is_user_logged_in() ) {
        return;
    }
    
    $user_id = get_current_user_id();
    
    // Сохраняем биллинговые данные
    if ( isset( $_POST['billing_first_name'] ) ) {
        update_user_meta( $user_id, 'billing_first_name', sanitize_text_field( $_POST['billing_first_name'] ) );
    }
    if ( isset( $_POST['billing_last_name'] ) ) {
        update_user_meta( $user_id, 'billing_last_name', sanitize_text_field( $_POST['billing_last_name'] ) );
    }
    if ( isset( $_POST['billing_phone'] ) ) {
        update_user_meta( $user_id, 'billing_phone', sanitize_text_field( $_POST['billing_phone'] ) );
    }
    if ( isset( $_POST['billing_email'] ) ) {
        update_user_meta( $user_id, 'billing_email', sanitize_email( $_POST['billing_email'] ) );
    }
    if ( isset( $_POST['billing_company'] ) ) {
        update_user_meta( $user_id, 'billing_company', sanitize_text_field( $_POST['billing_company'] ) );
    }
    if ( isset( $_POST['billing_tax_id'] ) ) {
        update_user_meta( $user_id, 'billing_tax_id', sanitize_text_field( $_POST['billing_tax_id'] ) );
    }
    
    // Сохраняем данные доставки
    if ( isset( $_POST['shipping_city'] ) ) {
        update_user_meta( $user_id, 'shipping_city', sanitize_text_field( $_POST['shipping_city'] ) );
    }
    if ( isset( $_POST['shipping_address_1'] ) ) {
        update_user_meta( $user_id, 'shipping_address_1', sanitize_text_field( $_POST['shipping_address_1'] ) );
    }
    if ( isset( $_POST['shipping_address_2'] ) ) {
        update_user_meta( $user_id, 'shipping_address_2', sanitize_text_field( $_POST['shipping_address_2'] ) );
    }
    if ( isset( $_POST['shipping_apartment'] ) ) {
        update_user_meta( $user_id, 'shipping_apartment', sanitize_text_field( $_POST['shipping_apartment'] ) );
    }
    if ( isset( $_POST['shipping_entrance'] ) ) {
        update_user_meta( $user_id, 'shipping_entrance', sanitize_text_field( $_POST['shipping_entrance'] ) );
    }
    if ( isset( $_POST['shipping_floor'] ) ) {
        update_user_meta( $user_id, 'shipping_floor', sanitize_text_field( $_POST['shipping_floor'] ) );
    }
    
    // Сохраняем предпочтения доставки
    if ( isset( $_POST['delivery_type'] ) ) {
        update_user_meta( $user_id, 'preferred_delivery_type', sanitize_text_field( $_POST['delivery_type'] ) );
    }
}
add_action( 'wp_ajax_save_checkout_data', 'asker_save_checkout_data' );
add_action( 'wp_ajax_nopriv_save_checkout_data', 'asker_save_checkout_data' );

/**
 * Загружаем сохраненные данные пользователя при загрузке страницы
 */
function asker_load_saved_checkout_data() {
    if ( ! is_user_logged_in() ) {
        return;
    }
    
    $user_id = get_current_user_id();
    $checkout_data = array();
    
    // Загружаем тип доставки
    $delivery_type = get_user_meta( $user_id, 'preferred_delivery_type', true );
    if ( $delivery_type ) {
        $checkout_data['delivery_type'] = $delivery_type;
    }
    
    // Загружаем все биллинговые данные
    $billing_fields = array(
        'billing_first_name',
        'billing_last_name', 
        'billing_phone',
        'billing_email',
        'billing_company',
        'billing_tax_id'
    );
    
    foreach ( $billing_fields as $field ) {
        $value = get_user_meta( $user_id, $field, true );
        if ( $value ) {
            $checkout_data[$field] = $value;
        }
    }
    
    // Загружаем данные доставки
    $shipping_fields = array(
        'shipping_city',
        'shipping_address_1',
        'shipping_address_2',
        'shipping_apartment',
        'shipping_entrance',
        'shipping_floor'
    );
    
    foreach ( $shipping_fields as $field ) {
        $value = get_user_meta( $user_id, $field, true );
        if ( $value ) {
            $checkout_data[$field] = $value;
        }
    }
    
    // Передаем данные в JavaScript
    if ( ! empty( $checkout_data ) ) {
        wp_localize_script( 'main', 'asker_checkout_data', $checkout_data );
    }
}
add_action( 'wp_enqueue_scripts', 'asker_load_saved_checkout_data' );

/**
 * Обработка успешного заказа - показываем кастомную страницу подтверждения
 */
function asker_handle_successful_order( $order_id ) {
    // Сохраняем ID заказа в сессии для показа на странице подтверждения
    WC()->session->set( 'asker_order_id', $order_id );
    
    // Перенаправляем на страницу подтверждения
    wp_redirect( add_query_arg( 'order_id', $order_id, wc_get_checkout_url() ) );
    exit;
}
add_action( 'woocommerce_thankyou', 'asker_handle_successful_order' );

/**
 * Показываем кастомную страницу подтверждения после успешного заказа
 */
function asker_show_custom_thankyou_page() {
    if ( isset( $_GET['order_id'] ) && is_numeric( $_GET['order_id'] ) ) {
        $order_id = intval( $_GET['order_id'] );
        $order = wc_get_order( $order_id );
        
        if ( $order && $order->get_status() !== 'failed' ) {
            // Показываем кастомную страницу подтверждения
            ?>
            <script>
            document.addEventListener('DOMContentLoaded', function() {
                showThankYouPageWithOrder(<?php echo $order_id; ?>);
            });
            
            function showThankYouPageWithOrder(orderId) {
                const modal = document.createElement('div');
                modal.className = 'thankyou-modal';
                
                modal.innerHTML = `
                    <div class="thankyou-page">
                        <div class="container">
                            <div class="thankyou__card">
                                <button class="thankyou__close-btn" onclick="closeModal()">
                                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none">
                                        <line x1="18" y1="6" x2="6" y2="18" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                                        <line x1="6" y1="6" x2="18" y2="18" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                                    </svg>
                                </button>
                                <div class="thankyou__header">
                                    <div class="thankyou__success-icon">
                                        <svg width="48" height="48" viewBox="0 0 24 24" fill="none">
                                            <circle cx="12" cy="12" r="12" fill="#4CAF50"/>
                                            <path d="M8 12L11 15L16 9" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                        </svg>
                                    </div>
                                    <h1 class="thankyou__title">Заказ успешно оформлен!</h1>
                                    <p class="thankyou__subtitle">Спасибо за ваш заказ. Мы свяжемся с вами в ближайшее время.</p>
                                </div>
                                
                                <div class="thankyou__content">
                                    <div class="thankyou__order-details">
                                        <h2 class="thankyou__section-title">Детали заказа</h2>
                                        <div class="thankyou__detail-row">
                                            <span class="thankyou__detail-label">Номер заказа:</span>
                                            <span class="thankyou__detail-value">#${orderId}</span>
                                        </div>
                                        <div class="thankyou__detail-row">
                                            <span class="thankyou__detail-label">Дата оформления:</span>
                                            <span class="thankyou__detail-value">${new Date().toLocaleDateString('ru-RU', { day: 'numeric', month: 'long', year: 'numeric' })} в ${new Date().toLocaleTimeString('ru-RU', { hour: '2-digit', minute: '2-digit' })}</span>
                                        </div>
                                        <div class="thankyou__detail-row">
                                            <span class="thankyou__detail-label">Статус:</span>
                                            <span class="thankyou__status-badge">
                                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none">
                                                    <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="2"/>
                                                    <path d="M12 6v6l4 2" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                                                </svg>
                                                Ожидает оплаты
                                            </span>
                                        </div>
                                        <div class="thankyou__detail-row">
                                            <span class="thankyou__detail-label">Способ оплаты:</span>
                                            <span class="thankyou__detail-value">По счету</span>
                                        </div>
                                    </div>
                                    
                                    <div class="thankyou__next-steps">
                                        <h2 class="thankyou__section-title">Что дальше?</h2>
                                        <div class="thankyou__steps">
                                            <div class="thankyou__step">
                                                <div class="thankyou__step-number">1</div>
                                                <div class="thankyou__step-content">
                                                    <h3>Получите счет</h3>
                                                    <p>Счет будет отправлен на ваш email в течение 30 минут</p>
                                                </div>
                                            </div>
                                            <div class="thankyou__step">
                                                <div class="thankyou__step-number">2</div>
                                                <div class="thankyou__step-content">
                                                    <h3>Оплатите счет</h3>
                                                    <p>У вас есть 3 рабочих дня для оплаты</p>
                                                </div>
                                            </div>
                                            <div class="thankyou__step">
                                                <div class="thankyou__step-number">3</div>
                                                <div class="thankyou__step-content">
                                                    <h3>Получите товар</h3>
                                                    <p>Доставка в течение 2-5 рабочих дней после оплаты</p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="thankyou__contact-info">
                                    <h2 class="thankyou__section-title">Контактная информация</h2>
                                    <div class="thankyou__contact-cards">
                                        <div class="thankyou__contact-card">
                                            <div class="thankyou__contact-icon">
                                                <svg width="24" height="24" viewBox="0 0 24 24" fill="none">
                                                    <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="2"/>
                                                    <path d="M12 2a10 10 0 0 0-10 10c0 1.5.5 3 1.5 4.5L12 22l8.5-5.5c1-1.5 1.5-3 1.5-4.5A10 10 0 0 0 12 2z"/>
                                                    <circle cx="12" cy="12" r="3" stroke="currentColor" stroke-width="2"/>
                                                </svg>
                                            </div>
                                            <div class="thankyou__contact-details">
                                                <h3>Ваш менеджер</h3>
                                                <p>Владимир Курдов</p>
                                            </div>
                                        </div>
                                        <div class="thankyou__contact-card">
                                            <div class="thankyou__contact-icon">
                                                <svg width="24" height="24" viewBox="0 0 24 24" fill="none">
                                                    <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z" stroke="currentColor" stroke-width="2"/>
                                                    <polyline points="22,6 12,13 2,6" stroke="currentColor" stroke-width="2"/>
                                                </svg>
                                            </div>
                                            <div class="thankyou__contact-details">
                                                <h3>Email</h3>
                                                <p>opt@asker-corp.ru</p>
                                            </div>
                                        </div>
                                        <div class="thankyou__contact-card">
                                            <div class="thankyou__contact-icon">
                                                <svg width="24" height="24" viewBox="0 0 24 24" fill="none">
                                                    <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z" stroke="currentColor" stroke-width="2"/>
                                                </svg>
                                            </div>
                                            <div class="thankyou__contact-details">
                                                <h3>Телефон</h3>
                                                <p>+7 (812) 123-12-23</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="thankyou__important-info">
                                    <div class="thankyou__important-header">
                                        <div class="thankyou__important-icon">
                                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none">
                                                <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="2"/>
                                                <path d="M12 8v4" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                                                <path d="M12 16h.01" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                                            </svg>
                                        </div>
                                        <h2 class="thankyou__section-title">Важная информация</h2>
                                    </div>
                                    <ul class="thankyou__important-list">
                                        <li>Проверьте папку "Спам" если не получили счет в течение часа</li>
                                        <li>Сохраните номер заказа для отслеживания статуса</li>
                                        <li>При возникновении вопросов обращайтесь в службу поддержки</li>
                                    </ul>
                                </div>
                                
                                <div class="thankyou__actions">
                                    <a href="${window.location.origin}" class="thankyou__btn thankyou__btn--primary">
                                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none">
                                            <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z" stroke="currentColor" stroke-width="2"/>
                                            <polyline points="9,22 9,12 15,12 15,22" stroke="currentColor" stroke-width="2"/>
                                        </svg>
                                        Вернуться на главную
                                    </a>
                                    <button class="thankyou__btn thankyou__btn--secondary" onclick="window.print()">
                                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none">
                                            <polyline points="6,9 6,2 18,2 18,9" stroke="currentColor" stroke-width="2"/>
                                            <path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2" stroke="currentColor" stroke-width="2"/>
                                            <rect x="6" y="14" width="12" height="8" stroke="currentColor" stroke-width="2"/>
                                        </svg>
                                        Распечатать заказ
                                    </button>
                                </div>
                                
                                <div class="thankyou__footer-message">
                                    <p>Спасибо, что выбрали наш магазин! Мы ценим ваше доверие.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                `;
                
                document.body.appendChild(modal);
                document.body.classList.add('thankyou-modal-open');
                
                function closeModal() {
                    document.body.removeChild(modal);
                    document.body.classList.remove('thankyou-modal-open');
                }
                
                modal.addEventListener('click', function(e) {
                    if (e.target === modal) {
                        closeModal();
                    }
                });
                
                document.addEventListener('keydown', function(e) {
                    if (e.key === 'Escape') {
                        closeModal();
                    }
                });
            }
            </script>
            <?php
        }
    }
}
add_action( 'wp_footer', 'asker_show_custom_thankyou_page' );

/**
 * Добавляем базовые способы оплаты для тестирования
 */
function asker_add_payment_methods() {
    // Включаем банковский перевод
    update_option( 'woocommerce_bacs_settings', array(
        'enabled' => 'yes',
        'title' => 'Банковский перевод',
        'description' => 'Оплата по счету',
        'instructions' => 'Оплатите по реквизитам, которые мы отправим вам на email.',
    ));
    
    // Включаем оплату при доставке
    update_option( 'woocommerce_cod_settings', array(
        'enabled' => 'yes',
        'title' => 'Оплата при доставке',
        'description' => 'Оплата наличными при получении',
        'instructions' => 'Оплатите наличными курьеру при получении заказа.',
    ));
}
add_action( 'init', 'asker_add_payment_methods' );

/**
 * Исправляем редирект на страницу благодарности
 */
function asker_fix_thankyou_redirect( $order_id ) {
    if ( $order_id ) {
        $order = wc_get_order( $order_id );
        if ( $order ) {
            // Устанавливаем статус "Ожидает оплаты" для новых заказов
            $order->set_status( 'pending' );
            $order->save();
        }
    }
}
add_action( 'woocommerce_checkout_order_processed', 'asker_fix_thankyou_redirect' );

/**
 * Принудительный редирект после успешного чекаута
 */
function asker_force_thankyou_redirect() {
    if ( is_checkout() && ! is_order_received_page() ) {
        // Проверяем есть ли заказ в сессии
        $order_id = WC()->session->get( 'order_awaiting_payment' );
        if ( $order_id ) {
            $order = wc_get_order( $order_id );
            if ( $order && $order->get_status() === 'pending' ) {
                $thankyou_url = $order->get_checkout_order_received_url();
                wp_redirect( $thankyou_url );
                exit;
            }
        }
    }
}
add_action( 'wp_loaded', 'asker_force_thankyou_redirect' );

/**
 * Принудительно показываем страницу благодарности
 */
function asker_force_thankyou_page() {
    if ( is_order_received_page() ) {
        // Проверяем есть ли заказ
        $order_id = get_query_var( 'order-received' );
        if ( $order_id ) {
            $order = wc_get_order( $order_id );
            if ( $order ) {
                // Устанавливаем переменную $order для шаблона
                global $wp_query;
                $wp_query->query_vars['order'] = $order;
            }
        }
    }
}
add_action( 'template_redirect', 'asker_force_thankyou_page' );

/**
 * Отключаем AJAX чекаут и делаем простой редирект
 */
function asker_disable_checkout_ajax() {
    if ( is_checkout() || is_cart() ) {
        // Отключаем только AJAX чекаут, но не весь скрипт
        wp_dequeue_script( 'wc-checkout' );
        
        // Убеждаемся что основные скрипты WooCommerce загружены
        wp_enqueue_script( 'wc-add-to-cart' );
        wp_enqueue_script( 'wc-cart' );
        
        // Добавляем простой редирект
        ?>
        <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Ждем загрузки кнопки
            setTimeout(function() {
                const checkoutBtn = document.querySelector('button[name="woocommerce_checkout_place_order"]') || 
                                   document.querySelector('.checkout__submit-btn') ||
                                   document.querySelector('button[type="submit"]') ||
                                   document.querySelector('a[href*="checkout"]') ||
                                   document.querySelector('.checkout-button');
                
                if (checkoutBtn) {
                    checkoutBtn.addEventListener('click', function(e) {
                        e.preventDefault();
                        
                        // Если это страница чекаута - создаем заказ и идем на thankyou
                        if (window.location.pathname.includes('checkout')) {
                            fetch('<?php echo admin_url( 'admin-ajax.php' ); ?>', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/x-www-form-urlencoded',
                                },
                                body: 'action=asker_create_order'
                            }).then(response => response.json())
                            .then(data => {
                                if (data.success) {
                                    window.location.href = '<?php echo home_url( '/thankyou/' ); ?>?order=' + data.data.order_id;
                                } else {
                                    alert('Ошибка создания заказа: ' + data.data);
                                }
                            });
                        } else {
                            // Если это страница корзины - просто переходим на чекаут
                            window.location.href = '<?php echo wc_get_checkout_url(); ?>';
                        }
                    });
                }
                
                // Обработчик для кнопок удаления товаров
                document.addEventListener('click', function(e) {
                    
                    // Кнопка "Удалить выбранные" - пробуем разные селекторы
                    if (e.target.classList.contains('btn-remove-selected') || 
                        e.target.closest('.btn-remove-selected') ||
                        e.target.textContent.includes('Удалить выбранные') ||
                        e.target.textContent.includes('Удалить выбранное')) {
                        
                        e.preventDefault();
                        
                        // Ищем чекбоксы разными способами
                        let selectedItems = document.querySelectorAll('input[type="checkbox"]:checked');
                        
                        // Если не найдены, пробуем другие селекторы
                        if (selectedItems.length === 0) {
                            selectedItems = document.querySelectorAll('.cart-item-checkbox:checked');
                        }
                        if (selectedItems.length === 0) {
                            selectedItems = document.querySelectorAll('[name*="cart_item_key"]:checked');
                        }
                        if (selectedItems.length === 0) {
                            selectedItems = document.querySelectorAll('input[name*="cart"]:checked');
                        }
                        
                        const cartItemKeys = Array.from(selectedItems).map(item => item.value);
                        
                        if (cartItemKeys.length > 0) {
                            // Удаляем все выбранные товары
                            Promise.all(cartItemKeys.map(key => {
                                return fetch('<?php echo admin_url( 'admin-ajax.php' ); ?>', {
                                    method: 'POST',
                                    headers: {
                                        'Content-Type': 'application/x-www-form-urlencoded',
                                    },
                                    body: 'action=woocommerce_remove_cart_item&cart_item_key=' + key
                                }).then(response => response.json());
                            })).then(() => {
                                location.reload();
                            }).catch(error => {
                                alert('Ошибка при удалении товаров');
                            });
                        } else {
                            alert('Выберите товары для удаления');
                        }
                    }
                    // Обычные кнопки удаления
                    else if (e.target.classList.contains('remove') || e.target.closest('.remove')) {
                        e.preventDefault();
                        const removeBtn = e.target.classList.contains('remove') ? e.target : e.target.closest('.remove');
                        const cartItemKey = removeBtn.getAttribute('data-cart_item_key');
                        
                        if (cartItemKey) {
                            fetch('<?php echo admin_url( 'admin-ajax.php' ); ?>', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/x-www-form-urlencoded',
                                },
                                body: 'action=woocommerce_remove_cart_item&cart_item_key=' + cartItemKey
                            }).then(() => {
                                location.reload();
                            });
                        }
                    }
                });
            }, 1000);
        });
        </script>
        <?php
    }
}
add_action( 'wp_footer', 'asker_disable_checkout_ajax' );

/**
 * AJAX обработчик для очистки корзины
 */
function asker_clear_cart_ajax() {
    WC()->cart->empty_cart();
    wp_die();
}
add_action( 'wp_ajax_woocommerce_clear_cart', 'asker_clear_cart_ajax' );
add_action( 'wp_ajax_nopriv_woocommerce_clear_cart', 'asker_clear_cart_ajax' );

/**
 * AJAX обработчик для удаления товара из корзины
 */
function asker_remove_cart_item_ajax() {
    $cart_item_key = sanitize_text_field( $_POST['cart_item_key'] );
    
    if ( $cart_item_key ) {
        WC()->cart->remove_cart_item( $cart_item_key );
        wp_send_json_success();
    }
    
    wp_send_json_error();
}
add_action( 'wp_ajax_woocommerce_remove_cart_item', 'asker_remove_cart_item_ajax' );
add_action( 'wp_ajax_nopriv_woocommerce_remove_cart_item', 'asker_remove_cart_item_ajax' );

/**
 * Убеждаемся что основные скрипты WooCommerce загружены
 */
function asker_ensure_woocommerce_scripts() {
    if ( class_exists( 'WooCommerce' ) ) {
        wp_enqueue_script( 'wc-add-to-cart' );
        wp_enqueue_script( 'wc-cart' );
        wp_enqueue_script( 'wc-single-product' );
    }
}
add_action( 'wp_enqueue_scripts', 'asker_ensure_woocommerce_scripts' );

/**
 * Добавляем обработчик для кнопок "В корзину" на главной странице
 */
function asker_add_cart_button_handler() {
    ?>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Обработчик для кнопок .btn-add-cart на главной странице
        document.addEventListener('click', function(e) {
            if (e.target.classList.contains('btn-add-cart')) {
                e.preventDefault();
                const productId = e.target.getAttribute('data-product-id');
                
                if (productId) {
                    // Добавляем товар в корзину через AJAX
                    fetch('<?php echo admin_url( 'admin-ajax.php' ); ?>', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                        body: 'action=woocommerce_add_to_cart&product_id=' + productId + '&quantity=1'
                    }).then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            // Обновляем счетчик корзины
                            if (typeof updateCartCounter === 'function') {
                                updateCartCounter();
                            }
                            // Показываем уведомление
                            e.target.textContent = 'Добавлено!';
                            e.target.style.background = '#4CAF50';
                            setTimeout(() => {
                                e.target.textContent = 'В корзину';
                                e.target.style.background = '';
                            }, 2000);
                        } else {
                            alert('Ошибка добавления товара в корзину');
                        }
                    });
                }
            }
        });
    });
    </script>
    <?php
}
add_action( 'wp_footer', 'asker_add_cart_button_handler' );

/**
 * AJAX обработчик для добавления товара в корзину
 */
function asker_add_to_cart_ajax() {
    $product_id = intval( $_POST['product_id'] );
    $quantity = intval( $_POST['quantity'] ) ?: 1;
    
    if ( $product_id ) {
        $cart_item_key = WC()->cart->add_to_cart( $product_id, $quantity );
        
        if ( $cart_item_key ) {
            wp_send_json_success( array(
                'cart_item_key' => $cart_item_key,
                'cart_count' => WC()->cart->get_cart_contents_count()
            ) );
        } else {
            wp_send_json_error( 'Не удалось добавить товар в корзину' );
        }
    } else {
        wp_send_json_error( 'Неверный ID товара' );
    }
}
add_action( 'wp_ajax_woocommerce_add_to_cart', 'asker_add_to_cart_ajax' );
add_action( 'wp_ajax_nopriv_woocommerce_add_to_cart', 'asker_add_to_cart_ajax' );

/**
 * AJAX обработчик для создания заказа
 */
function asker_create_order_ajax() {
    try {
        // Получаем данные из корзины
        $cart = WC()->cart;
        
        if ( $cart->is_empty() ) {
            wp_send_json_error( 'Корзина пуста' );
            return;
        }
        
        // Создаем заказ
        $order = wc_create_order();
        
        // Добавляем товары из корзины
        foreach ( $cart->get_cart() as $cart_item_key => $cart_item ) {
            $product = $cart_item['data'];
            $quantity = $cart_item['quantity'];
            
            $order->add_product( $product, $quantity );
        }
        
        // Устанавливаем адрес доставки (если есть данные пользователя)
        if ( is_user_logged_in() ) {
            $user_id = get_current_user_id();
            $billing_data = get_user_meta( $user_id );
            
            $order->set_billing_first_name( $billing_data['billing_first_name'][0] ?? 'Админ' );
            $order->set_billing_last_name( $billing_data['billing_last_name'][0] ?? 'Пользователь' );
            $order->set_billing_email( $billing_data['billing_email'][0] ?? get_userdata( $user_id )->user_email );
            $order->set_billing_phone( $billing_data['billing_phone'][0] ?? '+7 (999) 123-45-67' );
            $order->set_billing_city( $billing_data['billing_city'][0] ?? 'Москва' );
            $order->set_billing_address_1( $billing_data['billing_address_1'][0] ?? 'ул. Тестовая, д. 1' );
        } else {
            // Для неавторизованных пользователей
            $order->set_billing_first_name( 'Гость' );
            $order->set_billing_last_name( 'Пользователь' );
            $order->set_billing_email( 'guest@example.com' );
            $order->set_billing_phone( '+7 (999) 123-45-67' );
            $order->set_billing_city( 'Москва' );
            $order->set_billing_address_1( 'ул. Тестовая, д. 1' );
        }
        
        // Устанавливаем способ оплаты
        $order->set_payment_method( 'bacs' ); // Банковский перевод
        $order->set_payment_method_title( 'По счету' );
        
        // Рассчитываем итоги
        $order->calculate_totals();
        
        // Сохраняем заказ
        $order->save();
        
        // Очищаем корзину
        $cart->empty_cart();
        
        wp_send_json_success( array(
            'order_id' => $order->get_id(),
            'order_number' => $order->get_order_number()
        ) );
        
    } catch ( Exception $e ) {
        wp_send_json_error( $e->getMessage() );
    }
}
add_action( 'wp_ajax_asker_create_order', 'asker_create_order_ajax' );
add_action( 'wp_ajax_nopriv_asker_create_order', 'asker_create_order_ajax' );

/**
 * Исправляем сохранение полей чекаута
 */
function asker_fix_checkout_field_saving( $order_id ) {
    if ( ! $order_id ) {
        return;
    }
    
    $order = wc_get_order( $order_id );
    if ( ! $order ) {
        return;
    }
    
    // Сохраняем все поля биллинга
    $billing_fields = array(
        'billing_first_name',
        'billing_last_name', 
        'billing_phone',
        'billing_email',
        'billing_company',
        'billing_vat',
        'billing_city',
        'billing_address_1',
        'billing_address_2',
        'billing_postcode',
        'billing_state'
    );
    
    foreach ( $billing_fields as $field ) {
        if ( ! empty( $_POST[ $field ] ) ) {
            $order->update_meta_data( '_' . $field, sanitize_text_field( $_POST[ $field ] ) );
        }
    }
    
    $order->save();
}
add_action( 'woocommerce_checkout_update_order_meta', 'asker_fix_checkout_field_saving' );

/**
 * Отладка чекаута - логируем ошибки
 */
function asker_debug_checkout_errors() {
    if ( is_checkout() && ! is_order_received_page() ) {
        // Проверяем есть ли товары в корзине
        if ( WC()->cart->is_empty() ) {
            wc_add_notice( 'Корзина пуста. Добавьте товары для оформления заказа.', 'error' );
        }
        
        // Проверяем способы оплаты
        $available_payment_methods = WC()->payment_gateways->get_available_payment_gateways();
        if ( empty( $available_payment_methods ) ) {
            wc_add_notice( 'Нет доступных способов оплаты.', 'error' );
        }
    }
}
add_action( 'woocommerce_before_checkout_form', 'asker_debug_checkout_errors' );

/**
 * Исправляем сохранение данных чекаута в сессии
 */
function asker_save_checkout_data_to_session() {
    if ( ! is_checkout() ) {
        return;
    }
    
    // Сохраняем данные в сессии WooCommerce
    $checkout_data = array();
    
    // Данные биллинга
    $billing_fields = array(
        'billing_first_name',
        'billing_last_name',
        'billing_phone',
        'billing_email',
        'billing_company',
        'billing_vat',
        'billing_city',
        'billing_address_1',
        'billing_address_2',
        'billing_postcode',
        'billing_state'
    );
    
    foreach ( $billing_fields as $field ) {
        if ( ! empty( $_POST[ $field ] ) ) {
            $checkout_data[ $field ] = sanitize_text_field( $_POST[ $field ] );
            WC()->session->set( $field, $checkout_data[ $field ] );
        }
    }
    
    // Сохраняем в localStorage через JavaScript
    if ( ! empty( $checkout_data ) ) {
        ?>
        <script>
        document.addEventListener('DOMContentLoaded', function() {
            const checkoutData = <?php echo json_encode( $checkout_data ); ?>;
            Object.keys(checkoutData).forEach(function(key) {
                localStorage.setItem(key, checkoutData[key]);
            });
        });
        </script>
        <?php
    }
}
add_action( 'woocommerce_checkout_process', 'asker_save_checkout_data_to_session' );

/**
 * Загружаем сохраненные данные в поля чекаута
 */
function asker_load_checkout_data_from_session() {
    if ( ! is_checkout() ) {
        return;
    }
    
    $billing_fields = array(
        'billing_first_name',
        'billing_last_name',
        'billing_phone',
        'billing_email',
        'billing_company',
        'billing_vat',
        'billing_city',
        'billing_address_1',
        'billing_address_2',
        'billing_postcode',
        'billing_state'
    );
    
    ?>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const fields = <?php echo json_encode( $billing_fields ); ?>;
        
        fields.forEach(function(fieldName) {
            const field = document.querySelector('input[name="' + fieldName + '"]');
            if (field) {
                // Загружаем из localStorage
                const savedValue = localStorage.getItem(fieldName);
                if (savedValue) {
                    field.value = savedValue;
                }
                
                // Сохраняем при изменении
                field.addEventListener('input', function() {
                    localStorage.setItem(fieldName, this.value);
                });
            }
        });
    });
    </script>
    <?php
}
add_action( 'woocommerce_after_checkout_form', 'asker_load_checkout_data_from_session' );

/**
 * Загружаем данные пользователя в поля чекаута через JavaScript
 */
function asker_load_user_data_to_checkout_js() {
    if ( is_checkout() && is_user_logged_in() ) {
        $user_id = get_current_user_id();
        $user_data = get_userdata( $user_id );
        $billing_data = get_user_meta( $user_id );
        
        // Если нет данных биллинга, создаем тестовые
        if ( empty( $billing_data['billing_phone'][0] ) ) {
            update_user_meta( $user_id, 'billing_phone', '+7 (999) 123-45-67' );
            update_user_meta( $user_id, 'billing_first_name', 'Админ' );
            update_user_meta( $user_id, 'billing_last_name', 'Пользователь' );
            update_user_meta( $user_id, 'billing_city', 'Москва' );
            update_user_meta( $user_id, 'billing_address_1', 'ул. Тестовая, д. 1' );
            
            // Обновляем данные
            $billing_data = get_user_meta( $user_id );
        }
        
        ?>
        <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Данные пользователя
            const userData = {
                firstName: '<?php echo esc_js( $user_data->first_name ?: 'Админ' ); ?>',
                lastName: '<?php echo esc_js( $user_data->last_name ?: 'Пользователь' ); ?>',
                email: '<?php echo esc_js( $user_data->user_email ); ?>',
                phone: '<?php echo esc_js( isset( $billing_data['billing_phone'][0] ) ? $billing_data['billing_phone'][0] : '+7 (999) 123-45-67' ); ?>',
                company: '<?php echo esc_js( isset( $billing_data['billing_company'][0] ) ? $billing_data['billing_company'][0] : '' ); ?>',
                city: '<?php echo esc_js( isset( $billing_data['billing_city'][0] ) ? $billing_data['billing_city'][0] : 'Москва' ); ?>',
                address: '<?php echo esc_js( isset( $billing_data['billing_address_1'][0] ) ? $billing_data['billing_address_1'][0] : 'ул. Тестовая, д. 1' ); ?>'
            };
            
            // Заполняем поля
            const fields = {
                'billing_first_name': userData.firstName,
                'billing_last_name': userData.lastName,
                'billing_email': userData.email,
                'billing_phone': userData.phone,
                'billing_company': userData.company,
                'billing_city': userData.city,
                'billing_address_1': userData.address
            };
            
            Object.keys(fields).forEach(function(fieldName) {
                const field = document.querySelector('input[name="' + fieldName + '"]');
                if (field && fields[fieldName]) {
                    field.value = fields[fieldName];
                }
            });
        });
        </script>
        <?php
    }
}
add_action( 'wp_footer', 'asker_load_user_data_to_checkout_js' );

/**
 * Включаем поддержку SVG в медиа-библиотеке WordPress
 */
add_filter('upload_mimes', 'asker_enable_svg_upload');
function asker_enable_svg_upload($mimes) {
    $mimes['svg'] = 'image/svg+xml';
    $mimes['svgz'] = 'image/svg+xml';
    return $mimes;
}

add_filter('wp_prepare_attachment_for_js', 'asker_fix_svg_media_library', 10, 3);
function asker_fix_svg_media_library($response, $attachment, $meta) {
    if ($response['type'] === 'image' && $response['subtype'] === 'svg+xml') {
        $response['image'] = array(
            'src' => $response['url'],
            'width' => 150,
            'height' => 150
        );
        $response['thumb'] = array(
            'src' => $response['url'],
            'width' => 150,
            'height' => 150
        );
        // Для SVG sizes не создаются, используем оригинальный URL
        $response['sizes'] = array(
            'full' => array(
                'url' => $response['url'],
                'width' => 150,
                'height' => 150
            )
        );
    }
    return $response;
}

/**
 * Улучшаем отображение поля thumbnail для категорий товаров в админке
 * WooCommerce уже поддерживает это поле, но мы улучшим интерфейс
 */
add_action('product_cat_add_form_fields', 'asker_category_icon_field_add');
add_action('product_cat_edit_form_fields', 'asker_category_icon_field_edit');

function asker_category_icon_field_add() {
    // Получаем placeholder изображение
    $placeholder = '';
    if (function_exists('wc_placeholder_img_src')) {
        $placeholder = wc_placeholder_img_src();
    }
    if (!$placeholder || strpos($placeholder, 'placeholder') === false) {
        // Если нет placeholder от WooCommerce, используем прозрачный пиксель или дефолтную иконку
        $placeholder = 'data:image/svg+xml;base64,' . base64_encode('<svg width="60" height="60" xmlns="http://www.w3.org/2000/svg"><rect width="60" height="60" fill="#f0f0f0" stroke="#ddd"/></svg>');
    }
    ?>
                <div class="form-field term-thumbnail-wrap">
        <label><?php esc_html_e('Иконка категории', 'woocommerce'); ?></label>
        <div id="product_cat_thumbnail" style="float: left; margin-right: 10px;">
            <img src="<?php echo esc_url($placeholder); ?>" width="60px" height="60px" style="background: #f0f0f0; border: 1px solid #ddd; display: block; object-fit: contain;" alt="Placeholder" />
        </div>
        <div style="line-height: 60px;">
            <input type="hidden" id="product_cat_thumbnail_id" name="product_cat_thumbnail_id" />
            <button type="button" class="upload_image_button button"><?php esc_html_e('Загрузить/Добавить изображение', 'woocommerce'); ?></button>
            <button type="button" class="remove_image_button button" style="display:none;"><?php esc_html_e('Удалить изображение', 'woocommerce'); ?></button>
        </div>
        <div class="clear"></div>
        <p class="description"><?php esc_html_e('Загрузите SVG или PNG иконку для этой категории. Рекомендуемый размер: 60x60px. Иконка будет отображаться на главной странице и странице категорий.', 'woocommerce'); ?></p>
    </div>
    <?php
}

function asker_category_icon_field_edit($term) {
    $thumbnail_id = get_term_meta($term->term_id, 'thumbnail_id', true);
    
    // Получаем изображение или placeholder
    if ($thumbnail_id) {
        // Проверяем тип файла
        $mime_type = get_post_mime_type($thumbnail_id);
        
        if ($mime_type === 'image/svg+xml') {
            // Для SVG используем оригинальный URL напрямую
            $image = wp_get_attachment_url($thumbnail_id);
        } else {
            // Для обычных изображений пытаемся получить thumbnail
            $image = wp_get_attachment_image_url($thumbnail_id, 'thumbnail');
            if (!$image) {
                // Если нет thumbnail, используем medium
                $image = wp_get_attachment_image_url($thumbnail_id, 'medium');
            }
            if (!$image) {
                // Последний вариант - полный размер
                $image = wp_get_attachment_image_url($thumbnail_id, 'full');
            }
            if (!$image) {
                // Если вообще не получилось, используем прямой URL
                $image = wp_get_attachment_url($thumbnail_id);
            }
        }
    }
    
    // Если нет изображения, используем placeholder
    if (empty($image)) {
        if (function_exists('wc_placeholder_img_src')) {
            $image = wc_placeholder_img_src();
        }
        if (!$image || strpos($image, 'placeholder') === false) {
            // Создаем простой SVG placeholder
            $image = 'data:image/svg+xml;base64,' . base64_encode('<svg width="60" height="60" xmlns="http://www.w3.org/2000/svg"><rect width="60" height="60" fill="#f0f0f0" stroke="#ddd"/></svg>');
        }
    }
    ?>
    <tr class="form-field term-thumbnail-wrap">
        <th scope="row" valign="top">
            <label><?php esc_html_e('Иконка категории', 'woocommerce'); ?></label>
        </th>
        <td>
            <div id="product_cat_thumbnail" style="float: left; margin-right: 10px;">
                <img src="<?php echo esc_url($image); ?>" width="60px" height="60px" style="background: #f0f0f0; border: 1px solid #ddd; display: block; object-fit: contain;" alt="<?php echo esc_attr($term->name); ?>" onerror="this.src='data:image/svg+xml;base64,<?php echo base64_encode('<svg width="60" height="60" xmlns="http://www.w3.org/2000/svg"><rect width="60" height="60" fill="#f0f0f0" stroke="#ddd"/></svg>'); ?>';" />
            </div>
            <div style="line-height: 60px;">
                <input type="hidden" id="product_cat_thumbnail_id" name="product_cat_thumbnail_id" value="<?php echo esc_attr($thumbnail_id ?: ''); ?>" />
                <button type="button" class="upload_image_button button"><?php esc_html_e('Загрузить/Добавить изображение', 'woocommerce'); ?></button>
                <button type="button" class="remove_image_button button" <?php echo $thumbnail_id ? '' : 'style="display:none;"'; ?>><?php esc_html_e('Удалить изображение', 'woocommerce'); ?></button>
            </div>
            <div class="clear"></div>
            <p class="description"><?php esc_html_e('Загрузите SVG или PNG иконку для этой категории. Рекомендуемый размер: 60x60px. Иконка будет отображаться на главной странице и странице категорий.', 'woocommerce'); ?></p>
        </td>
    </tr>
    <?php
}

// Сохраняем иконку
add_action('created_product_cat', 'asker_save_category_icon');
add_action('edited_product_cat', 'asker_save_category_icon');

function asker_save_category_icon($term_id) {
    if (isset($_POST['product_cat_thumbnail_id']) && !empty($_POST['product_cat_thumbnail_id'])) {
        update_term_meta($term_id, 'thumbnail_id', absint($_POST['product_cat_thumbnail_id']));
    } else {
        // Если поле пустое, удаляем иконку
        delete_term_meta($term_id, 'thumbnail_id');
    }
}

// Скрипты для загрузки изображений в админке
add_action('admin_enqueue_scripts', 'asker_category_icon_admin_scripts');

function asker_category_icon_admin_scripts() {
    $screen = get_current_screen();
    if ($screen && ($screen->id === 'edit-product_cat' || $screen->id === 'product_cat')) {
        wp_enqueue_media();
        ?>
        <script>
        jQuery(document).ready(function($) {
            // Удаление существующего обработчика WooCommerce, если он есть
            $('body').off('click', '.upload_image_button');
            $('body').off('click', '.remove_image_button');
            
            // Загрузка изображения
            $('body').on('click', '.upload_image_button', function(e) {
                e.preventDefault();
                
                var button = $(this);
                var file_frame = wp.media({
                    title: 'Выберите иконку категории',
                    button: {
                        text: 'Использовать это изображение'
                    },
                    multiple: false,
                    library: {
                        type: 'image'
                    }
                });
                
                file_frame.on('select', function() {
                    var attachment = file_frame.state().get('selection').first().toJSON();
                    var thumbnail_id = attachment.id;
                    
                    // Определяем URL изображения
                    // Для SVG нет sizes.thumbnail, для обычных изображений используем thumbnail или medium
                    var image_url = attachment.url;
                    if (attachment.mime === 'image/svg+xml') {
                        // Для SVG используем оригинальный URL
                        image_url = attachment.url;
                    } else if (attachment.sizes && attachment.sizes.thumbnail) {
                        image_url = attachment.sizes.thumbnail.url;
                    } else if (attachment.sizes && attachment.sizes.medium) {
                        image_url = attachment.sizes.medium.url;
                    }
                    
                    $('#product_cat_thumbnail_id').val(thumbnail_id);
                    $('#product_cat_thumbnail img').attr('src', image_url);
                    $('.remove_image_button').show();
                });
                
                file_frame.open();
            });
            
            // Удаление изображения
            $('body').on('click', '.remove_image_button', function(e) {
                e.preventDefault();
                
                $('#product_cat_thumbnail_id').val('');
                var placeholder = '<?php 
                    $placeholder = function_exists("wc_placeholder_img_src") ? wc_placeholder_img_src() : "";
                    if (!$placeholder) {
                        $placeholder = 'data:image/svg+xml;base64,' . base64_encode('<svg width="60" height="60" xmlns="http://www.w3.org/2000/svg"><rect width="60" height="60" fill="#f0f0f0" stroke="#ddd"/></svg>');
                    }
                    echo esc_js($placeholder); 
                ?>';
                $('#product_cat_thumbnail img').attr('src', placeholder);
                $('.remove_image_button').hide();
            });
        });
        </script>
        <?php
    }
}

/**
 * Фильтр товаров по цене (min_price и max_price из GET параметров)
 */
function asker_price_filter_query($query) {
    if (!is_admin() && $query->is_main_query() && (is_shop() || is_product_category() || is_product_taxonomy())) {
        $min_price = isset($_GET['min_price']) ? floatval($_GET['min_price']) : 0;
        $max_price = isset($_GET['max_price']) ? floatval($_GET['max_price']) : 0;
        
        if ($min_price > 0 || $max_price > 0) {
            $meta_query = $query->get('meta_query') ?: [];
            
            if ($min_price > 0 && $max_price > 0) {
                $meta_query[] = [
                    'key' => '_price',
                    'value' => [$min_price, $max_price],
                    'compare' => 'BETWEEN',
                    'type' => 'NUMERIC'
                ];
            } elseif ($min_price > 0) {
                $meta_query[] = [
                    'key' => '_price',
                    'value' => $min_price,
                    'compare' => '>=',
                    'type' => 'NUMERIC'
                ];
            } elseif ($max_price > 0) {
                $meta_query[] = [
                    'key' => '_price',
                    'value' => $max_price,
                    'compare' => '<=',
                    'type' => 'NUMERIC'
                ];
            }
            
            $query->set('meta_query', $meta_query);
        }
    }
}
add_action('pre_get_posts', 'asker_price_filter_query');


