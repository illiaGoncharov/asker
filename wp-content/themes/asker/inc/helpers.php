<?php
/**
 * Вспомогательные функции темы.
 */

/**
 * Улучшенный поиск для WooCommerce товаров
 */
add_action('pre_get_posts', function($query) {
    if (!is_admin() && $query->is_main_query()) {
        if ($query->is_search()) {
            // Ищем только в товарах WooCommerce
            $query->set('post_type', array('product'));
            
            // Ищем по названию, описанию и артикулу
            add_filter('posts_search', function($search, $wp_query) {
                global $wpdb;
                
                if (empty($search)) {
                    return $search;
                }
                
                $search_terms = $wp_query->query_vars['s'];
                $search_terms = explode(' ', $search_terms);
                
                $search = '';
                foreach ($search_terms as $term) {
                    if (!empty($term)) {
                        $search .= " AND (
                            ({$wpdb->posts}.post_title LIKE '%{$term}%') OR
                            ({$wpdb->posts}.post_content LIKE '%{$term}%') OR
                            ({$wpdb->posts}.post_excerpt LIKE '%{$term}%') OR
                            EXISTS (
                                SELECT * FROM {$wpdb->postmeta} 
                                WHERE {$wpdb->postmeta}.post_id = {$wpdb->posts}.ID 
                                AND {$wpdb->postmeta}.meta_key = '_sku' 
                                AND {$wpdb->postmeta}.meta_value LIKE '%{$term}%'
                            )
                        )";
                    }
                }
                
                return $search;
            }, 10, 2);
        }
    }
});

/**
 * Добавляем поиск по артикулу товара
 */
add_filter('woocommerce_product_data_store_cpt_get_products_query', function($query, $query_vars) {
    if (!empty($query_vars['s'])) {
        $query['meta_query'][] = array(
            'key' => '_sku',
            'value' => $query_vars['s'],
            'compare' => 'LIKE'
        );
    }
    return $query;
}, 10, 2);

/**
 * URL миниатюры термина product_cat для карточек категорий.
 */
function asker_get_term_thumbnail_url($term_id, $size = 'medium') {
    $thumb_id = get_term_meta($term_id, 'thumbnail_id', true);
    if (!$thumb_id) {
        return '';
    }
    $src = wp_get_attachment_image_src((int) $thumb_id, $size);
    return $src && is_array($src) ? (string) $src[0] : '';
}

/**
 * Безопасное получение массива из произвольного значения.
 */
function asker_to_array($value) {
    return is_array($value) ? $value : (empty($value) ? [] : [$value]);
}

/**
 * AJAX обработчики для счетчиков корзины и избранного
 */

// Получение количества товаров в корзине
add_action('wp_ajax_get_cart_count', 'asker_get_cart_count');
add_action('wp_ajax_nopriv_get_cart_count', 'asker_get_cart_count');

function asker_get_cart_count() {
    if (function_exists('WC')) {
        $cart_count = WC()->cart->get_cart_contents_count();
    } else {
        $cart_count = 0;
    }
    
    wp_send_json_success(['count' => $cart_count]);
}

// Получение количества товаров в избранном
add_action('wp_ajax_get_wishlist_count', 'asker_get_wishlist_count');
add_action('wp_ajax_nopriv_get_wishlist_count', 'asker_get_wishlist_count');

function asker_get_wishlist_count() {
    // КРИТИЧНО: Для неавторизованных пользователей возвращаем 0
    // Избранное должно быть привязано к аккаунту
    if (!is_user_logged_in()) {
        wp_send_json_success(['count' => 0]);
        return;
    }
    
    // Проверяем, есть ли плагин избранного (например, YITH Wishlist)
    if (function_exists('yith_wcwl_count_products')) {
        $wishlist_count = yith_wcwl_count_products();
    } else {
        // Используем user_meta для авторизованных пользователей
        $user_id = get_current_user_id();
        $wishlist = get_user_meta($user_id, 'asker_wishlist', true);
        $wishlist_count = (!empty($wishlist) && is_array($wishlist)) ? count($wishlist) : 0;
    }
    
    wp_send_json_success(['count' => $wishlist_count]);
}

// Обновление счетчиков при изменении корзины
// НЕ используем для AJAX запросов, чтобы не портить JSON ответ
add_action('woocommerce_add_to_cart', 'asker_update_cart_count_ajax');
add_action('woocommerce_cart_item_removed', 'asker_update_cart_count_ajax');
add_action('woocommerce_cart_item_quantity_updated', 'asker_update_cart_count_ajax');

function asker_update_cart_count_ajax() {
    // Не выводим скрипт в AJAX контексте (admin-ajax.php)
    // Это предотвращает вывод <script> перед JSON ответом
    if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
        return;
    }
    
    // Также проверяем по запросу
    if ( isset( $_SERVER['REQUEST_URI'] ) && strpos( $_SERVER['REQUEST_URI'], 'admin-ajax.php' ) !== false ) {
        return;
    }
    
    // Отправляем обновление счетчика через JavaScript только для обычных запросов
    ?>
    <script>
        if (typeof updateCartCount === 'function') {
            updateCartCount();
        }
    </script>
    <?php
}

/**
 * Получение URL страницы по слагу или шаблону
 * 
 * @param string $slug Слаг страницы
 * @param string $template_name Имя шаблона (опционально)
 * @param string $fallback_url URL для фолбэка
 * @return string URL страницы
 */
function asker_get_page_url($slug, $template_name = '', $fallback_url = '') {
    // Сначала ищем по слагу
    $page = get_page_by_path($slug);
    if ($page && $page->post_status === 'publish') {
        return get_permalink($page->ID);
    }
    
    // Если не нашли по слагу и указан шаблон, ищем по шаблону
    if ($template_name) {
        $pages = get_pages(array(
            'meta_key'   => '_wp_page_template',
            'meta_value' => $template_name,
            'number'     => 1,
            'post_status' => 'publish'
        ));
        if (!empty($pages)) {
            return get_permalink($pages[0]->ID);
        }
    }
    
    // Фолбэк
    if ($fallback_url) {
        return $fallback_url;
    }
    
    // Последний фолбэк - home_url со слагом
    return home_url('/' . $slug);
}

/**
 * Обработчик формы обратной связи на странице контактов
 */
add_action('admin_post_asker_contact_feedback', 'asker_handle_contact_feedback');
add_action('admin_post_nopriv_asker_contact_feedback', 'asker_handle_contact_feedback');

function asker_handle_contact_feedback() {
    // Проверяем nonce
    if (!isset($_POST['asker_contact_nonce']) || !wp_verify_nonce($_POST['asker_contact_nonce'], 'asker_contact_form')) {
        wp_die('Ошибка безопасности. Попробуйте еще раз.');
    }
    
    // Получаем данные формы
    $name = isset($_POST['contact_name']) ? sanitize_text_field($_POST['contact_name']) : '';
    $phone = isset($_POST['contact_phone']) ? sanitize_text_field($_POST['contact_phone']) : '';
    $message = isset($_POST['contact_message']) ? sanitize_textarea_field($_POST['contact_message']) : '';
    $consent = isset($_POST['contact_consent']) ? true : false;
    
    // Валидация
    if (empty($name) || empty($phone) || empty($message) || !$consent) {
        wp_redirect(add_query_arg('contact_error', '1', wp_get_referer()));
        exit;
    }
    
    // Email получателя из настроек темы или админский email
    $to = get_theme_mod('contact_form_email', get_option('admin_email'));
    $subject = 'Новое сообщение с сайта Asker Parts';
    
    // Формируем письмо
    $email_body = "Новое сообщение с формы обратной связи на сайте Asker Parts\n\n";
    $email_body .= "Имя: {$name}\n";
    $email_body .= "Телефон: {$phone}\n";
    $email_body .= "Сообщение:\n{$message}\n";
    $email_body .= "\n---\n";
    $email_body .= "Время отправки: " . current_time('mysql') . "\n";
    $email_body .= "IP адрес: " . $_SERVER['REMOTE_ADDR'] . "\n";
    
    $headers = array(
        'Content-Type: text/plain; charset=UTF-8',
        'From: Asker Parts <' . get_option('admin_email') . '>'
    );
    
    // Отправляем письмо
    $sent = wp_mail($to, $subject, $email_body, $headers);
    
    if ($sent) {
        // Перенаправляем с сообщением об успехе
        wp_redirect(add_query_arg('contact_success', '1', wp_get_referer()));
    } else {
        // Перенаправляем с сообщением об ошибке
        wp_redirect(add_query_arg('contact_error', '2', wp_get_referer()));
    }
    exit;
}

/**
 * Принудительное создание страниц WooCommerce
 * Можно вызвать через wp-admin или через URL: /wp-admin/admin.php?page=asker-create-pages
 */
function asker_force_create_woocommerce_pages() {
    // Проверяем права доступа
    if (!current_user_can('manage_options')) {
        wp_die('Недостаточно прав для выполнения этой операции.');
    }

    echo '<div class="wrap">';
    echo '<h1>Создание страниц WooCommerce</h1>';
    
    // Создаем страницы
    asker_create_woocommerce_pages();
    
    // Проверяем результат
    $shop_page = get_page_by_path('shop');
    $account_page = get_page_by_path('my-account');
    $cart_page = get_page_by_path('cart');
    $checkout_page = get_page_by_path('checkout');
    $wishlist_page = get_page_by_path('wishlist');
    
    echo '<h2>Результат:</h2>';
    echo '<ul>';
    echo '<li>Страница "Каталог" (shop): ' . ($shop_page ? '✅ Создана (ID: ' . $shop_page->ID . ')' : '❌ Не создана') . '</li>';
    echo '<li>Страница "Мой аккаунт" (my-account): ' . ($account_page ? '✅ Создана (ID: ' . $account_page->ID . ')' : '❌ Не создана') . '</li>';
    echo '<li>Страница "Корзина" (cart): ' . ($cart_page ? '✅ Создана (ID: ' . $cart_page->ID . ')' : '❌ Не создана') . '</li>';
    echo '<li>Страница "Оформление заказа" (checkout): ' . ($checkout_page ? '✅ Создана (ID: ' . $checkout_page->ID . ')' : '❌ Не создана') . '</li>';
    echo '<li>Страница "Избранное" (wishlist): ' . ($wishlist_page ? '✅ Создана (ID: ' . $wishlist_page->ID . ')' : '❌ Не создана') . '</li>';
    echo '</ul>';
    
    // Проверяем настройки WooCommerce
    if (class_exists('WooCommerce')) {
        echo '<h2>Настройки WooCommerce:</h2>';
        echo '<ul>';
        echo '<li>Страница магазина: ' . (get_option('woocommerce_shop_page_id') ? '✅ Назначена' : '❌ Не назначена') . '</li>';
        echo '<li>Страница корзины: ' . (get_option('woocommerce_cart_page_id') ? '✅ Назначена' : '❌ Не назначена') . '</li>';
        echo '<li>Страница оформления: ' . (get_option('woocommerce_checkout_page_id') ? '✅ Назначена' : '❌ Не назначена') . '</li>';
        echo '<li>Страница аккаунта: ' . (get_option('woocommerce_myaccount_page_id') ? '✅ Назначена' : '❌ Не назначена') . '</li>';
        echo '</ul>';
        
        echo '<h2>Проверка ссылок:</h2>';
        echo '<ul>';
        echo '<li><a href="' . home_url('/shop') . '" target="_blank">Каталог</a></li>';
        echo '<li><a href="' . home_url('/my-account') . '" target="_blank">Мой аккаунт</a></li>';
        echo '<li><a href="' . home_url('/cart') . '" target="_blank">Корзина</a></li>';
        echo '<li><a href="' . home_url('/checkout') . '" target="_blank">Оформление заказа</a></li>';
        echo '<li><a href="' . home_url('/wishlist/') . '" target="_blank">Избранное</a></li>';
        echo '</ul>';
    } else {
        echo '<div class="notice notice-warning"><p>WooCommerce не установлен!</p></div>';
    }
    
    echo '</div>';
}

/**
 * Очистка дублирующихся страниц WooCommerce
 */
function asker_cleanup_duplicate_pages() {
    if (!current_user_can('manage_options')) {
        wp_die('Недостаточно прав для выполнения этой операции.');
    }

    echo '<div class="wrap">';
    echo '<h1>Очистка дублирующихся страниц</h1>';
    
    $pages_to_check = ['shop', 'my-account', 'cart', 'checkout', 'wishlist'];
    $deleted_count = 0;
    
    foreach ($pages_to_check as $slug) {
        $pages = get_posts([
            'name' => $slug,
            'post_type' => 'page',
            'post_status' => ['publish', 'draft', 'trash'],
            'numberposts' => -1
        ]);
        
        if (count($pages) > 1) {
            echo '<h3>Найдены дубликаты для "' . $slug . '":</h3>';
            echo '<ul>';
            
            // Оставляем первую страницу, остальные удаляем
            $keep_page = array_shift($pages);
            echo '<li>✅ Оставляем: "' . $keep_page->post_title . '" (ID: ' . $keep_page->ID . ', статус: ' . $keep_page->post_status . ')</li>';
            
            foreach ($pages as $duplicate) {
                wp_delete_post($duplicate->ID, true);
                echo '<li>🗑️ Удален: "' . $duplicate->post_title . '" (ID: ' . $duplicate->ID . ', статус: ' . $duplicate->post_status . ')</li>';
                $deleted_count++;
            }
            echo '</ul>';
        } else {
            echo '<p>✅ Дубликатов для "' . $slug . '" не найдено</p>';
        }
    }
    
    echo '<div class="notice notice-success"><p>Очистка завершена. Удалено страниц: ' . $deleted_count . '</p></div>';
    echo '</div>';
}

/**
 * Принудительная настройка постоянных ссылок
 */
function asker_fix_permalinks() {
    if (!current_user_can('manage_options')) {
        wp_die('Недостаточно прав для выполнения этой операции.');
    }

    echo '<div class="wrap">';
    echo '<h1>Настройка постоянных ссылок</h1>';
    
    // Устанавливаем структуру постоянных ссылок
    update_option('permalink_structure', '/%postname%/');
    
    // Очищаем правила перезаписи
    flush_rewrite_rules();
    
    echo '<div class="notice notice-success"><p>Постоянные ссылки настроены и правила перезаписи обновлены!</p></div>';
    
    // Проверяем текущие настройки
    $permalink_structure = get_option('permalink_structure');
    echo '<h2>Текущие настройки:</h2>';
    echo '<p><strong>Структура ссылок:</strong> ' . ($permalink_structure ? $permalink_structure : 'По умолчанию') . '</p>';
    
    echo '<h2>Проверка ссылок:</h2>';
    echo '<ul>';
    echo '<li><a href="' . home_url('/shop') . '" target="_blank">Каталог</a></li>';
    echo '<li><a href="' . home_url('/my-account') . '" target="_blank">Мой аккаунт</a></li>';
    echo '<li><a href="' . home_url('/cart') . '" target="_blank">Корзина</a></li>';
    echo '<li><a href="' . home_url('/checkout') . '" target="_blank">Оформление заказа</a></li>';
    echo '<li><a href="' . home_url('/wishlist') . '" target="_blank">Избранное</a></li>';
    echo '</ul>';
    
    echo '</div>';
}

// Добавляем пункты в админ-меню
add_action('admin_menu', function() {
    add_management_page(
        'Создать страницы WooCommerce',
        'Создать страницы WC',
        'manage_options',
        'asker-create-pages',
        'asker_force_create_woocommerce_pages'
    );
    
    add_management_page(
        'Очистить дубликаты страниц',
        'Очистить дубликаты',
        'manage_options',
        'asker-cleanup-pages',
        'asker_cleanup_duplicate_pages'
    );
    
    add_management_page(
        'Настроить постоянные ссылки',
        'Настроить ссылки',
        'manage_options',
        'asker-fix-permalinks',
        'asker_fix_permalinks'
    );
});



