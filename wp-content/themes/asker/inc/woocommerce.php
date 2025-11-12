<?php
/**
 * Базовая интеграция WooCommerce. Без агрессивных оверрайдов.
 */

/**
 * Принудительно устанавливаем русский язык для WooCommerce
 */
function asker_force_woocommerce_russian() {
    // Устанавливаем локаль для WooCommerce
    add_filter('locale', function($locale) {
        return 'ru_RU';
    }, 999);
    
    // Принудительно загружаем русский язык для WooCommerce
    add_filter('load_textdomain_mofile', function($mofile, $domain) {
        if ($domain === 'woocommerce') {
            // Пытаемся найти русский языковой файл
            $ru_mofile = str_replace('/en_US.mo', '/ru_RU.mo', $mofile);
            if (file_exists($ru_mofile)) {
                return $ru_mofile;
            }
        }
        return $mofile;
    }, 10, 2);
    
    // Загружаем языковой пакет WooCommerce при инициализации
    if (class_exists('WooCommerce')) {
        $lang_dir = WP_LANG_DIR . '/plugins/';
        $woocommerce_ru = $lang_dir . 'woocommerce-ru_RU.mo';
        
        if (file_exists($woocommerce_ru)) {
            load_textdomain('woocommerce', $woocommerce_ru);
        }
    }
}
add_action('init', 'asker_force_woocommerce_russian', 1);

// Пример: включить поддержку миниатюр галереи (по мере необходимости)
// add_theme_support('wc-product-gallery-zoom');
// add_theme_support('wc-product-gallery-lightbox');
// add_theme_support('wc-product-gallery-slider');

/**
 * Убедиться, что сессия WooCommerce инициализирована
 * Важно для неавторизованных пользователей (инкогнито)
 * ВРЕМЕННО ОТКЛЮЧЕНО для диагностики белого экрана
 */
function asker_ensure_cart_session() {
    if ( ! function_exists( 'WC' ) ) {
        return;
    }
    
    try {
        $wc = WC();
        if ( ! $wc || ! isset( $wc->session ) || ! $wc->session ) {
            return;
        }
        
        // Инициализируем сессию для всех пользователей, включая неавторизованных
        if ( ! $wc->session->has_session() ) {
            $wc->session->set_customer_session_cookie( true );
        }
    } catch ( Exception $e ) {
        // Игнорируем ошибки сессии для предотвращения белого экрана
        return;
    }
}
// Включаем сессию корзины для всех пользователей (важно для прода)
add_action( 'wp_loaded', 'asker_ensure_cart_session', 5 );

/**
 * Отключаем режим "Coming Soon" в WooCommerce
 * Это важно для показа товаров всем пользователям
 */
function asker_disable_coming_soon_mode() {
    // Отключаем рендеринг блока "Coming Soon" через фильтр шаблона
    add_filter( 'render_block_woocommerce/coming-soon', '__return_empty_string', 999 );
    
    // Также отключаем через фильтр блоков - удаляем блок полностью
    add_filter( 'render_block_data', function( $parsed_block, $source_block, $parent_block ) {
        if ( isset( $parsed_block['blockName'] ) && $parsed_block['blockName'] === 'woocommerce/coming-soon' ) {
            return array();
        }
        return $parsed_block;
    }, 999, 3 );
    
    // Отключаем рендеринг через фильтр content
    add_filter( 'the_content', function( $content ) {
        // Удаляем блок coming-soon из контента (рекурсивно, включая вложенные div)
        $content = preg_replace( '/<div[^>]*data-block-name=["\']woocommerce\/coming-soon["\'][^>]*>.*?<\/div>/is', '', $content );
        $content = preg_replace( '/<div[^>]*class=["\'][^"\']*woocommerce-coming-soon[^"\']*["\'][^>]*>.*?<\/div>/is', '', $content );
        $content = preg_replace( '/<div[^>]*data-block-name=["\']woocommerce\/coming-soon["\'][^>]*>.*?<\/div>/is', '', $content );
        return $content;
    }, 999 );
    
    // Также фильтруем через блоки темы
    add_filter( 'render_block', function( $block_content, $block ) {
        if ( isset( $block['blockName'] ) && $block['blockName'] === 'woocommerce/coming-soon' ) {
            return '';
        }
        return $block_content;
    }, 999, 2 );
    
    // Удаляем мета-тег coming soon из head
    remove_action( 'wp_head', 'wc_coming_soon_page_meta', 10 );
}
add_action( 'init', 'asker_disable_coming_soon_mode', 10 );

/**
 * Принудительно включаем магазин для всех пользователей
 * ВАЖНО: Применяется на хуке wp_loaded, когда WooCommerce точно загружен
 */
function asker_force_store_available() {
    // Принудительно включаем магазин только если WooCommerce загружен
    if ( class_exists( 'WooCommerce' ) ) {
        // Принудительно отключаем режим Coming Soon через опции
        update_option( 'woocommerce_coming_soon_page_id', 0 );
        delete_option( 'woocommerce_coming_soon_page_id' );
        
        add_filter( 'woocommerce_is_store_available', '__return_true', 999 );
        // Принудительно отключаем режим Coming Soon через фильтр
        add_filter( 'woocommerce_coming_soon_page_id', '__return_zero', 999 );
        // Отключаем мета-тег coming soon
        remove_action( 'wp_head', 'wc_coming_soon_page_meta', 10 );
    }
}
add_action( 'wp_loaded', 'asker_force_store_available', 5 );

/**
 * ОТКЛЮЧАЕМ РЕЖИМ COMING SOON НА РАННЕМ ЭТАПЕ
 * Это критично - блок рендерится до загрузки страницы
 */
function asker_disable_coming_soon_early() {
    if ( ! class_exists( 'WooCommerce' ) ) {
        return;
    }
    
    // ПРИНУДИТЕЛЬНО удаляем опцию Coming Soon из БД
    global $wpdb;
    $wpdb->delete( 
        $wpdb->options, 
        array( 'option_name' => 'woocommerce_coming_soon_page_id' ), 
        array( '%s' ) 
    );
    
    // Удаляем через стандартные функции WordPress
    delete_option( 'woocommerce_coming_soon_page_id' );
    update_option( 'woocommerce_coming_soon_page_id', 0, false );
    
    // Отключаем фильтр, который проверяет Coming Soon
    add_filter( 'woocommerce_is_store_available', '__return_true', 1 );
    add_filter( 'woocommerce_coming_soon_page_id', '__return_zero', 1 );
}
add_action( 'plugins_loaded', 'asker_disable_coming_soon_early', 1 );
add_action( 'init', 'asker_disable_coming_soon_early', 1 );
add_action( 'after_setup_theme', 'asker_disable_coming_soon_early', 1 );

/**
 * ПРИНУДИТЕЛЬНО отключаем блочную тему для главной страницы
 * КРИТИЧНО: Должно выполняться РАНЬШЕ всех остальных хуков
 */
function asker_disable_block_theme_for_home() {
    if ( ! is_admin() && ( is_front_page() || is_home() ) ) {
        // Отключаем блочную тему полностью
        add_filter( 'wp_is_block_theme', '__return_false', 1 );
        add_filter( 'block_template_can_be_used', '__return_false', 1 );
        add_filter( 'block_template_part_can_be_used', '__return_false', 1 );
        
        // Удаляем только действия, связанные с coming-soon
        // НЕ удаляем wp_footer - там могут быть наши скрипты!
        
        // Перехватываем рендеринг блоков
        add_filter( 'render_block', function( $block_content, $block ) {
            if ( isset( $block['blockName'] ) && $block['blockName'] === 'woocommerce/coming-soon' ) {
                return '';
            }
            return $block_content;
        }, 1, 2 );
    }
}
// Выполняем на САМОМ РАННЕМ этапе
add_action( 'after_setup_theme', 'asker_disable_block_theme_for_home', 1 );
add_action( 'init', 'asker_disable_block_theme_for_home', 1 );
add_action( 'template_redirect', 'asker_disable_block_theme_for_home', 1 );

/**
 * Перехватываем шаблон для главной страницы
 * КРИТИЧНО: Должен иметь приоритет ВЫШЕ блочных шаблонов
 */
function asker_override_homepage_template( $template ) {
    // Только для главной страницы (не админка)
    if ( is_admin() ) {
        return $template;
    }
    
    // Проверяем главную страницу (более агрессивная проверка)
    $is_home = is_front_page() || is_home();
    if ( ! $is_home ) {
        // Проверяем по URL
        $request_uri = isset( $_SERVER['REQUEST_URI'] ) ? $_SERVER['REQUEST_URI'] : '';
        $request_uri = rtrim( $request_uri, '/' );
        $is_home = ( $request_uri === '' || $request_uri === '/' || $request_uri === '/index.php' );
    }
    
    if ( $is_home ) {
        // Если есть front-page.php, используем его ВСЕГДА
        $front_page_template = get_template_directory() . '/front-page.php';
        if ( file_exists( $front_page_template ) ) {
            // КРИТИЧНО: Отключаем блочную тему ПЕРЕД загрузкой шаблона
            add_filter( 'wp_is_block_theme', '__return_false', 1 );
            add_filter( 'block_template_can_be_used', '__return_false', 1 );
            add_filter( 'block_template_part_can_be_used', '__return_false', 1 );
            
            // Удаляем только действия, связанные с блочными шаблонами coming-soon
            // НЕ удаляем все действия, чтобы не сломать другие функции
            global $wp_filter;
            if ( isset( $wp_filter['wp_body_open'] ) ) {
                foreach ( $wp_filter['wp_body_open']->callbacks as $priority => $callbacks ) {
                    foreach ( $callbacks as $key => $callback ) {
                        // Удаляем только если это связано с coming-soon
                        if ( is_array( $callback['function'] ) && 
                             is_string( $callback['function'][0] ) && 
                             strpos( $callback['function'][0], 'coming-soon' ) !== false ) {
                            remove_action( 'wp_body_open', $key, $priority );
                        }
                    }
                }
            }
            
            // Перехватываем только render_block для coming-soon, не удаляем все фильтры
            
            return $front_page_template;
        }
    }
    
    return $template;
}
// КРИТИЧНО: Приоритет 1 - раньше всех остальных фильтров
add_filter( 'template_include', 'asker_override_homepage_template', 1 );
add_filter( 'home_template', 'asker_override_homepage_template', 1 );
add_filter( 'frontpage_template', 'asker_override_homepage_template', 1 );

/**
 * Перехватываем весь вывод страницы и удаляем блок Coming Soon
 * ВАЖНО: Должен запускаться РАНЬШЕ всех других хуков
 */
// ВРЕМЕННО ОТКЛЮЧЕНО output buffering - может блокировать загрузку скриптов
// Coming Soon уже выключен через админку
/*
function asker_buffer_output_start() {
    if ( ! is_admin() && ( is_front_page() || is_home() ) ) {
        // Запускаем буферизацию только если её еще нет
        if ( ! ob_get_level() ) {
            ob_start( 'asker_remove_coming_soon_from_output' );
        }
    }
}
add_action( 'template_redirect', 'asker_buffer_output_start', 999 );
*/

function asker_remove_coming_soon_from_output( $buffer ) {
    if ( empty( $buffer ) || ! is_string( $buffer ) ) {
        return $buffer;
    }
    
    // Удаляем только блоки coming-soon и wp-site-blocks, НЕ трогаем остальное
    
    // 1. Удаляем мета-тег coming-soon (самое простое и безопасное)
    $buffer = preg_replace( '/<meta[^>]*name=["\']woo-coming-soon-page["\'][^>]*>/is', '', $buffer );
    
    // 2. Удаляем блок coming-soon любой вложенности (рекурсивно)
    $buffer = preg_replace( '/<div[^>]*data-block-name=["\']woocommerce\/coming-soon["\'][^>]*>[\s\S]*?<\/div>/ims', '', $buffer );
    
    // 3. Удаляем по классу coming-soon
    $buffer = preg_replace( '/<div[^>]*class=["\'][^"\']*woocommerce-coming-soon[^"\']*["\'][^>]*>[\s\S]*?<\/div>/ims', '', $buffer );
    
    // 4. Удаляем wp-site-blocks полностью для главной страницы
    // Это блок перекрывает весь контент, поэтому удаляем его агрессивно
    // Используем рекурсивное удаление для вложенных div'ов
    $max_iterations = 10;
    $iteration = 0;
    while ( ( strpos( $buffer, 'wp-site-blocks' ) !== false || strpos( $buffer, 'wp-block-woocommerce-coming-soon' ) !== false ) && $iteration < $max_iterations ) {
        // Удаляем wp-site-blocks с любой вложенностью
        $buffer = preg_replace( '/<div[^>]*class=["\'][^"\']*wp-site-blocks[^"\']*["\'][^>]*>[\s\S]*?<\/div>/ims', '', $buffer );
        // Удаляем блок coming-soon
        $buffer = preg_replace( '/<div[^>]*data-block-name=["\']woocommerce\/coming-soon["\'][^>]*>[\s\S]*?<\/div>/ims', '', $buffer );
        $buffer = preg_replace( '/<div[^>]*class=["\'][^"\']*woocommerce-coming-soon[^"\']*["\'][^>]*>[\s\S]*?<\/div>/ims', '', $buffer );
        $buffer = preg_replace( '/<div[^>]*class=["\'][^"\']*wp-block-woocommerce-coming-soon[^"\']*["\'][^>]*>[\s\S]*?<\/div>/ims', '', $buffer );
        $iteration++;
    }
    
    // Также удаляем через простую замену строк (на случай если regex не сработал)
    $buffer = str_replace( '<div class="wp-site-blocks">', '', $buffer );
    $buffer = str_replace( '<div class=\'wp-site-blocks\'>', '', $buffer );
    
    // 5. Удаляем только стили связанные С coming-soon
    $buffer = preg_replace( '/<style[^>]*>.*?coming-soon.*?<\/style>/is', '', $buffer );
    
    // 6. Удаляем только скрипты связанные С coming-soon
    $buffer = preg_replace( '/<script[^>]*>.*?coming-soon.*?<\/script>/is', '', $buffer );
    
    return $buffer;
}

/**
 * Полностью удаляем wp-site-blocks с Coming Soon блоком через JS
 */
function asker_remove_coming_soon_completely() {
    if ( is_front_page() || is_home() ) {
        add_action( 'wp_body_open', function() {
            ?>
            <script>
            (function() {
                // Удаляем весь wp-site-blocks сразу
                function removeSiteBlocks() {
                    var siteBlocks = document.querySelector('.wp-site-blocks');
                    if (siteBlocks) {
                        var hasComingSoon = siteBlocks.querySelector('[data-block-name="woocommerce/coming-soon"]');
                        if (hasComingSoon) {
                            siteBlocks.style.display = 'none';
                            siteBlocks.remove();
                        }
                    }
                }
                removeSiteBlocks();
                if (document.readyState === 'loading') {
                    document.addEventListener('DOMContentLoaded', removeSiteBlocks);
                }
                window.addEventListener('load', removeSiteBlocks);
                var interval = setInterval(function() {
                    removeSiteBlocks();
                }, 100);
                setTimeout(function() { clearInterval(interval); }, 1000);
            })();
            </script>
            <?php
        }, 1 );
    }
}
add_action( 'wp', 'asker_remove_coming_soon_completely', 1 );

/**
 * Убеждаемся, что товары видны всем пользователям (включая неавторизованных)
 * ВАЖНО: Применяется ТОЛЬКО на страницах WooCommerce
 * ВРЕМЕННО ОТКЛЮЧЕНО для диагностики белого экрана
 */
function asker_ensure_products_visible() {
    // НЕ применяем на админке или если это не страница WooCommerce
    if ( is_admin() ) {
        return;
    }
    
    // Проверяем, что это действительно страница WooCommerce
    if ( ! function_exists( 'is_woocommerce' ) || ! is_woocommerce() ) {
        return;
    }
    
    // Для страниц товаров - убираем ограничения видимости
    if ( is_shop() || is_product_category() || is_product_taxonomy() || is_product() ) {
        // Убеждаемся, что товары не фильтруются по правам доступа
        add_filter( 'woocommerce_product_is_visible', '__return_true', 999 );
        
        // Убеждаемся, что товары доступны для покупки
        add_filter( 'woocommerce_is_purchasable', '__return_true', 999 );
    }
}

/**
 * Убираем проверку доступности товара перед добавлением в корзину
 */
function asker_force_product_purchasable( $purchasable, $product ) {
    // Для всех товаров делаем доступными для покупки
    return true;
}
add_filter( 'woocommerce_is_purchasable', 'asker_force_product_purchasable', 999, 2 );

/**
 * Убираем валидацию добавления в корзину для всех товаров
 */
function asker_skip_add_to_cart_validation( $passed, $product_id, $quantity ) {
    // Разрешаем добавление всех товаров
    return true;
}
add_filter( 'woocommerce_add_to_cart_validation', 'asker_skip_add_to_cart_validation', 999, 3 );
// ВРЕМЕННО ОТКЛЮЧЕНО для диагностики
// add_action( 'wp', 'asker_ensure_products_visible', 10 );

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

    // Создаем страницу "Условия использования" (Terms and conditions)
    $terms_page = get_page_by_path('terms');
    if (!$terms_page) {
        $terms_id = wp_insert_post([
            'post_title' => 'Условия использования',
            'post_name' => 'terms',
            'post_content' => '<h2>Условия использования</h2><p>Здесь будут размещены условия использования сайта.</p>',
            'post_status' => 'publish',
            'post_type' => 'page',
        ]);
        
        if ($terms_id && !is_wp_error($terms_id)) {
            // Назначаем страницу в настройках WooCommerce
            update_option('woocommerce_terms_page_id', $terms_id);
        }
    } else {
        // Обновляем настройки WooCommerce, если страница уже существует
        update_option('woocommerce_terms_page_id', $terms_page->ID);
    }
}

// Запускаем создание страниц при активации темы
add_action('after_switch_theme', 'asker_create_woocommerce_pages');

// Также создаем страницы при каждом запросе админки (на случай если они были удалены)
add_action('admin_init', function() {
    // Создаем страницы только если WooCommerce активен и мы в админке
    if ( is_admin() && class_exists('WooCommerce') && current_user_can('manage_options') ) {
        // Проверяем, существует ли страница Terms
        $terms_page = get_page_by_path('terms');
        if ( !$terms_page && get_option('woocommerce_terms_page_id') ) {
            // Страница была удалена, но настройка осталась - создаем заново
            asker_create_woocommerce_pages();
        }
    }
}, 99);

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
        $removed = WC()->cart->remove_cart_item( $cart_item_key );
        if ( $removed ) {
            WC()->cart->calculate_totals(); // Пересчитываем после удаления
            wp_send_json_success( [ 
                'message' => 'Товар удален',
                'cart_count' => WC()->cart->get_cart_contents_count()
            ] );
        } else {
            wp_send_json_error( [ 'message' => 'Товар не найден в корзине' ] );
        }
    }
    
    wp_send_json_error( [ 'message' => 'Ошибка удаления' ] );
}
add_action( 'wp_ajax_remove_cart_item', 'asker_ajax_remove_cart_item' );
add_action( 'wp_ajax_nopriv_remove_cart_item', 'asker_ajax_remove_cart_item' );

/**
 * Переопределяем шаблон карточки товара в цикле
 */
function asker_custom_product_card_template() {
    // Убираем стандартные хуки WooCommerce
    remove_action( 'woocommerce_before_shop_loop_item', 'woocommerce_template_loop_product_link_open', 10 );
    remove_action( 'woocommerce_after_shop_loop_item', 'woocommerce_template_loop_product_link_close', 5 );
    remove_action( 'woocommerce_after_shop_loop_item', 'woocommerce_template_loop_add_to_cart', 10 );
    
    // Убираем стандартный вывод сортинга и счетчика результатов (чтобы не дублировать)
    remove_action( 'woocommerce_before_shop_loop', 'woocommerce_catalog_ordering', 30 );
    remove_action( 'woocommerce_before_shop_loop', 'woocommerce_result_count', 20 );
    
    // Убираем стандартный заголовок страницы My Account
    add_filter( 'woocommerce_account_menu_items', '__return_empty_array', 999 );
    add_filter( 'woocommerce_show_page_title', '__return_false' );
    
    // Убираем стандартное сообщение "Great things are on the horizon"
    remove_action( 'woocommerce_no_products_found', 'wc_no_products_found', 10 );
    
    // Добавляем кастомное сообщение об отсутствии товаров
    add_action( 'woocommerce_no_products_found', 'asker_no_products_found', 10 );
    
    // Изменяем текст кнопки "Add to cart" на "В корзину"
    add_filter( 'woocommerce_product_add_to_cart_text', 'asker_change_add_to_cart_text', 10, 2 );
    add_filter( 'woocommerce_product_single_add_to_cart_text', 'asker_change_add_to_cart_text', 10, 2 );
    
    // Добавляем кастомные хуки
    add_action( 'woocommerce_before_shop_loop_item', 'asker_custom_product_link_open', 10 );
    add_action( 'woocommerce_after_shop_loop_item', 'asker_custom_product_link_close', 5 );
    // Не добавляем кастомную кнопку - используем встроенную в content-product.php
    // add_action( 'woocommerce_after_shop_loop_item', 'asker_custom_add_to_cart_button', 10 );
}
add_action( 'init', 'asker_custom_product_card_template' );

/**
 * Кастомное сообщение об отсутствии товаров
 */
function asker_no_products_found() {
    ?>
    <div class="no-products">
        <h2>Товары не найдены</h2>
        <p>К сожалению, в данном разделе пока нет товаров.</p>
        <p><a href="<?php echo esc_url( wc_get_page_permalink( 'shop' ) ); ?>" class="btn btn--primary">Вернуться в каталог</a></p>
    </div>
    <?php
}

/**
 * AJAX: Синхронизация избранного (localStorage -> user_meta)
 */
function asker_sync_wishlist() {
    if (!is_user_logged_in()) {
        wp_send_json_error(['message' => 'Требуется авторизация']);
        return;
    }
    
    $product_ids = isset($_POST['product_ids']) ? array_map('intval', $_POST['product_ids']) : array();
    
    // Сохраняем в user_meta
    $user_id = get_current_user_id();
    update_user_meta($user_id, 'asker_wishlist', $product_ids);
    
    wp_send_json_success(['message' => 'Избранное синхронизировано', 'count' => count($product_ids)]);
}
add_action('wp_ajax_asker_sync_wishlist', 'asker_sync_wishlist');

/**
 * AJAX: Добавить/удалить товар из избранного
 */
function asker_toggle_wishlist() {
    // Разрешаем работу с избранным для всех пользователей
    // Для авторизованных - сохраняем в user_meta, для неавторизованных - работаем через localStorage
    
    $product_id = isset($_POST['product_id']) ? intval($_POST['product_id']) : 0;
    $action = isset($_POST['action_type']) ? sanitize_text_field($_POST['action_type']) : 'toggle';
    
    if (!$product_id) {
        wp_send_json_error(['message' => 'Неверный ID товара']);
        return;
    }
    
    // Для авторизованных пользователей сохраняем в user_meta
    // Для неавторизованных - просто возвращаем успех (данные в localStorage)
    if (is_user_logged_in()) {
        $user_id = get_current_user_id();
        $wishlist = get_user_meta($user_id, 'asker_wishlist', true);
        
        if (empty($wishlist) || !is_array($wishlist)) {
            $wishlist = array();
        }
        
        if ($action === 'add') {
            // Добавляем товар, если его еще нет
            if (!in_array($product_id, $wishlist)) {
                $wishlist[] = $product_id;
            }
        } elseif ($action === 'remove') {
            // Удаляем товар
            $wishlist = array_diff($wishlist, array($product_id));
            $wishlist = array_values($wishlist); // Переиндексация
        } elseif ($action === 'toggle') {
            // Переключаем состояние
            if (!in_array($product_id, $wishlist)) {
                $wishlist[] = $product_id;
            } else {
                $wishlist = array_diff($wishlist, array($product_id));
                $wishlist = array_values($wishlist); // Переиндексация
            }
        }
        
        update_user_meta($user_id, 'asker_wishlist', $wishlist);
        
        wp_send_json_success([
            'message' => 'Избранное обновлено',
            'is_favorite' => in_array($product_id, $wishlist),
            'count' => count($wishlist)
        ]);
    } else {
        // Для неавторизованных пользователей просто возвращаем успех
        // Данные управляются через localStorage на клиенте
        wp_send_json_success([
            'message' => 'Избранное обновлено (локально)',
            'is_favorite' => $action === 'add',
            'count' => 0
        ]);
    }
}
add_action('wp_ajax_asker_toggle_wishlist', 'asker_toggle_wishlist');
add_action('wp_ajax_nopriv_asker_toggle_wishlist', 'asker_toggle_wishlist');

/**
 * AJAX: Получить HTML список товаров из избранного
 */
function asker_get_wishlist_products() {
    // Разрешаем для всех пользователей - для неавторизованных используем переданные product_ids
    $product_ids = isset($_POST['product_ids']) ? array_map('intval', $_POST['product_ids']) : array();
    
    // Для авторизованных - используем user_meta если product_ids пуст
    if (is_user_logged_in()) {
        $user_id = get_current_user_id();
        $wishlist = get_user_meta($user_id, 'asker_wishlist', true);
        
        // Используем список из user_meta если передан пустой массив
        if (empty($product_ids) && !empty($wishlist) && is_array($wishlist)) {
            $product_ids = $wishlist;
        }
    }
    // Для неавторизованных - используем только переданные product_ids
    
    if (empty($product_ids)) {
        wp_send_json_success(['html' => '<div class="no-products"><p>В вашем избранном пока нет товаров.</p><a href="' . esc_url(home_url('/shop')) . '" class="btn-primary">Перейти в каталог</a></div>']);
        return;
    }
    
    ob_start();
    ?>
    <div class="products-grid">
        <?php foreach ($product_ids as $product_id) :
            $product = wc_get_product($product_id);
            if ($product && $product->is_visible()) :
                $product_image = wp_get_attachment_image_src(get_post_thumbnail_id($product_id), 'medium');
                $product_url = get_permalink($product_id);
                $price = $product->get_price_html();
                $price = preg_replace('/,00/', '', $price);
                ?>
                <div class="product-card">
                    <button class="product-favorite active favorite-btn" data-product-id="<?php echo esc_attr($product_id); ?>" aria-label="Удалить из избранного">
                        <img src="<?php echo get_template_directory_uri(); ?>/assets/images/ui/like[active].svg" alt="Избранное" class="favorite-icon favorite-icon--active">
                        <img src="<?php echo get_template_directory_uri(); ?>/assets/images/ui/like[idle].svg" alt="Добавить в избранное" class="favorite-icon favorite-icon--idle">
                    </button>
                    <a href="<?php echo esc_url($product_url); ?>">
                        <?php if ($product_image) : ?>
                            <img class="product-image" src="<?php echo esc_url($product_image[0]); ?>" alt="<?php echo esc_attr($product->get_name()); ?>">
                        <?php else : ?>
                            <div class="product-placeholder"><?php echo esc_html($product->get_name()); ?></div>
                        <?php endif; ?>
                    </a>
                    <h3 class="product-title">
                        <a href="<?php echo esc_url($product_url); ?>"><?php echo esc_html($product->get_name()); ?></a>
                    </h3>
                    <div class="product-bottom">
                        <div class="product-price"><?php echo $price; ?></div>
                        <button class="btn-add-cart add_to_cart_button" data-product-id="<?php echo esc_attr($product_id); ?>">В корзину</button>
                    </div>
                </div>
            <?php
            endif;
        endforeach; ?>
    </div>
    <?php
    $html = ob_get_clean();
    
    wp_send_json_success(['html' => $html]);
}
add_action('wp_ajax_asker_get_wishlist_products', 'asker_get_wishlist_products');
add_action('wp_ajax_nopriv_asker_get_wishlist_products', 'asker_get_wishlist_products');

/**
 * Изменяем текст кнопки "Add to cart" на "В корзину"
 */
function asker_change_add_to_cart_text( $text, $product ) {
    return 'В корзину';
}

/**
 * Переводим сообщение "has been added to your cart" на русский
 */
function asker_translate_add_to_cart_message( $message, $products, $show_qty ) {
    // Если $products - массив с ID товаров
    if ( is_array( $products ) && ! empty( $products ) ) {
        $product_count = count( $products );
        
        if ( $product_count > 1 ) {
            $message = sprintf( 
                '%d товара добавлены в корзину. <a href="%s" class="button wc-forward">%s</a>',
                $product_count,
                esc_url( wc_get_cart_url() ),
                'Посмотреть корзину'
            );
        } else {
            // Получаем первый товар
            $product_id = is_array( $products ) ? array_values( $products )[0] : $products;
            $product = wc_get_product( $product_id );
            
            if ( $product ) {
                $product_name = $product->get_name();
                $message = sprintf( 
                    '«%s» добавлен в корзину. <a href="%s" class="button wc-forward">%s</a>',
                    esc_html( $product_name ),
                    esc_url( wc_get_cart_url() ),
                    'Посмотреть корзину'
                );
            }
        }
    } else {
        // Если передан уже готовый текст, просто заменяем английские строки на русские
        $message = str_replace( 'has been added to your cart', 'добавлен в корзину', $message );
        $message = str_replace( 'View cart', 'Посмотреть корзину', $message );
    }
    
    return $message;
}
add_filter( 'wc_add_to_cart_message_html', 'asker_translate_add_to_cart_message', 10, 3 );

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
    // Устанавливаем валюту только если она еще не установлена или не RUB
    $current_currency = get_option( 'woocommerce_currency' );
    if ( $current_currency !== 'RUB' ) {
    update_option( 'woocommerce_currency', 'RUB' );
    update_option( 'woocommerce_currency_symbol', '₽' );
    }
    
    update_option( 'woocommerce_price_thousand_sep', ' ' );
    update_option( 'woocommerce_price_decimal_sep', ',' );
    update_option( 'woocommerce_price_num_decimals', 0 );
    
    // Устанавливаем страну по умолчанию
    update_option( 'woocommerce_default_country', 'RU' );
}
add_action( 'after_switch_theme', 'asker_set_woocommerce_currency' );

// Также проверяем при каждом запросе админки (на случай если настройки были изменены)
add_action( 'admin_init', function() {
    if ( is_admin() && class_exists('WooCommerce') && current_user_can('manage_options') ) {
        $current_currency = get_option( 'woocommerce_currency' );
        if ( $current_currency !== 'RUB' ) {
            // Валюта не RUB - устанавливаем автоматически
            update_option( 'woocommerce_currency', 'RUB' );
            update_option( 'woocommerce_currency_symbol', '₽' );
        }
    }
}, 99 );

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
        $symbol = '₽';
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
 * Указываем версию шаблонов WooCommerce для совместимости
 * Это убирает предупреждение об устаревших шаблонах
 */
function asker_wc_template_version() {
    return '9.0'; // Версия WooCommerce, с которой совместимы наши шаблоны
}
add_filter( 'woocommerce_get_template_version', 'asker_wc_template_version' );

/**
 * Скрываем предупреждение об устаревших шаблонах (опционально)
 * Раскомментируй, если хочешь скрыть предупреждение
 */
// function asker_hide_outdated_template_notice() {
//     remove_action( 'admin_notices', array( 'WC_Admin_Notices', 'template_file_check_notice' ) );
// }
// add_action( 'admin_init', 'asker_hide_outdated_template_notice' );

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
                
                // Обработчик для кнопок удаления товаров - только для конкретных кнопок
                // УБРАНО: обработчик для .btn-remove-selected - он теперь в page-cart.php
                // чтобы не было конфликта
                document.addEventListener('click', function(e) {
                    // Пропускаем .btn-remove-selected - обрабатывается в page-cart.php
                    if (e.target.classList.contains('btn-remove-selected') || e.target.closest('.btn-remove-selected')) {
                        return; // Позволяем обработчику из page-cart.php обработать клик
                    }
                    
                    // Обычные кнопки удаления - только .remove-item
                    if (e.target.classList.contains('remove-item') || e.target.closest('.remove-item')) {
                        e.preventDefault();
                        e.stopPropagation();
                        
                        const removeBtn = e.target.classList.contains('remove-item') ? e.target : e.target.closest('.remove-item');
                        
                        // Защита от двойных кликов
                        if (removeBtn.hasAttribute('data-processing')) {
                            return;
                        }
                        removeBtn.setAttribute('data-processing', 'true');
                        removeBtn.style.opacity = '0.6';
                        removeBtn.style.pointerEvents = 'none';
                        
                        const cartItemKey = removeBtn.getAttribute('data-key');
                        
                        if (cartItemKey) {
                            // Убираем confirm для быстрого удаления, можно вернуть по желанию
                            // if (confirm('Удалить товар из корзины?')) {
                            fetch('<?php echo admin_url( 'admin-ajax.php' ); ?>', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/x-www-form-urlencoded',
                                },
                                body: 'action=woocommerce_remove_cart_item&cart_item_key=' + cartItemKey
                                })
                                .then(response => {
                                    if (!response.ok) {
                                        throw new Error('Network response was not ok');
                                    }
                                    return response.json();
                                })
                                .then(data => {
                                    // Небольшая задержка для визуальной обратной связи
                                    setTimeout(() => {
                                location.reload();
                                    }, 300);
                                })
                                .catch(error => {
                                    console.error('Ошибка удаления:', error);
                                    alert('Ошибка при удалении товара. Попробуйте еще раз.');
                                    // Восстанавливаем кнопку при ошибке
                                    removeBtn.removeAttribute('data-processing');
                                    removeBtn.style.opacity = '1';
                                    removeBtn.style.pointerEvents = 'auto';
                                });
                            // } else {
                            //     // Если пользователь отменил, снимаем блокировку
                            //     removeBtn.removeAttribute('data-processing');
                            //     removeBtn.style.opacity = '1';
                            //     removeBtn.style.pointerEvents = 'auto';
                            // }
                        }
                        return;
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
    if (!function_exists('WC') || !WC() || !WC()->cart) {
        wp_send_json_error(['message' => 'WooCommerce не доступен']);
        return;
    }
    WC()->cart->empty_cart();
    wp_send_json_success(['message' => 'Корзина очищена']);
}
add_action( 'wp_ajax_woocommerce_clear_cart', 'asker_clear_cart_ajax' );
add_action( 'wp_ajax_nopriv_woocommerce_clear_cart', 'asker_clear_cart_ajax' );

/**
 * AJAX обработчик для удаления товара из корзины (для action woocommerce_remove_cart_item)
 */
function asker_remove_cart_item_ajax() {
    // Останавливаем любые буферы вывода
    while ( ob_get_level() > 0 ) {
        ob_end_clean();
    }
    
    // Устанавливаем правильные заголовки для JSON
    if ( ! headers_sent() ) {
        header( 'Content-Type: application/json; charset=utf-8' );
    }
    
    // Быстрая проверка параметров
    if ( ! isset( $_POST['cart_item_key'] ) || empty( $_POST['cart_item_key'] ) ) {
        wp_send_json_error( [ 'message' => 'Неверные параметры' ] );
        return;
    }
    
    // Проверяем WooCommerce доступность
    if ( ! function_exists( 'WC' ) || ! WC() || ! WC()->cart ) {
        wp_send_json_error( [ 'message' => 'Корзина недоступна' ] );
        return;
    }
    
    $cart_item_key = sanitize_text_field( $_POST['cart_item_key'] );
    
    // Проверяем, существует ли товар в корзине перед удалением
    // Используем прямой доступ для быстроты
    $cart_items = WC()->cart->get_cart();
    
    if ( ! isset( $cart_items[ $cart_item_key ] ) ) {
        // Товар уже удален - это не ошибка, возвращаем успех
        wp_send_json_success( [ 
            'message' => 'Товар уже удален',
            'cart_count' => WC()->cart->get_cart_contents_count()
        ] );
        return;
    }
    
    // Удаляем товар
    $removed = WC()->cart->remove_cart_item( $cart_item_key );
    
    if ( $removed ) {
        // Быстрый пересчет только если нужно
        WC()->cart->calculate_totals();
        
        // Останавливаем все буферы перед отправкой JSON
        while ( ob_get_level() > 0 ) {
            ob_end_clean();
        }
        
        // Возвращаем успех сразу
        wp_send_json_success( [ 
            'message' => 'Товар удален',
            'cart_count' => WC()->cart->get_cart_contents_count()
        ] );
    } else {
        // Если remove_cart_item вернул false, проверяем еще раз
        $cart_items_after = WC()->cart->get_cart();
        if ( ! isset( $cart_items_after[ $cart_item_key ] ) ) {
            // Товар все же удален
            // Останавливаем все буферы перед отправкой JSON
            while ( ob_get_level() > 0 ) {
                ob_end_clean();
            }
            
            wp_send_json_success( [ 
                'message' => 'Товар удален',
                'cart_count' => WC()->cart->get_cart_contents_count()
            ] );
        } else {
            // Останавливаем все буферы перед отправкой JSON
            while ( ob_get_level() > 0 ) {
                ob_end_clean();
            }
            
            wp_send_json_error( [ 'message' => 'Не удалось удалить товар' ] );
        }
    }
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
                            // Показываем сообщение об ошибке
                            const errorMsg = data.data && data.data.message ? data.data.message : 'Ошибка добавления товара в корзину';
                            alert(errorMsg);
                            console.error('Ошибка добавления в корзину:', data);
                        }
                    })
                    .catch(error => {
                        console.error('Ошибка AJAX запроса:', error);
                        alert('Произошла ошибка при добавлении товара в корзину');
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
 * Перехватываем стандартную форму добавления в корзину и обрабатываем через AJAX
 */
function asker_intercept_add_to_cart_form() {
    if ( ! is_product() ) {
        return;
    }
    ?>
    <script>
    jQuery(document).ready(function($) {
        // Перехватываем отправку формы добавления в корзину
        $('form.cart').on('submit', function(e) {
            e.preventDefault();
            
            const $form = $(this);
            const $button = $form.find('.single_add_to_cart_button');
            const productId = $button.attr('value');
            const quantity = $form.find('input[name="quantity"]').val() || 1;
            
            if (!productId) {
                console.error('Не найден ID товара');
                return false;
            }
            
            // Блокируем кнопку
            $button.prop('disabled', true);
            const originalText = $button.text();
            $button.text('Добавляется...');
            
            // Отправляем AJAX запрос
            $.ajax({
                url: '<?php echo admin_url( 'admin-ajax.php' ); ?>',
                type: 'POST',
                dataType: 'json', // Явно указываем, что ожидаем JSON
                data: {
                    action: 'woocommerce_add_to_cart',
                    product_id: productId,
                    quantity: quantity
                },
                success: function(response) {
                    // WooCommerce может вернуть либо {success: true, data: {...}}, либо просто {fragments: {...}, cart_hash: '...'}
                    // Проверяем оба варианта
                    const isSuccess = response.success || (response.fragments !== undefined);
                    
                    if (isSuccess) {
                        // Обновляем счетчик корзины
                        if (typeof updateCartCounter === 'function') {
                            updateCartCounter();
                        }
                        
                        // Показываем успех
                        $button.text('Добавлено!');
                        $button.css('background', '#4CAF50');
                        
                        // Обновляем фрагменты корзины
                        const fragments = response.data?.fragments || response.fragments || {};
                        const cartHash = response.data?.cart_hash || response.cart_hash || '';
                        
                        // Триггерим событие added_to_cart (обработчик в main.js скроет view cart кнопку)
                        if (fragments && Object.keys(fragments).length > 0) {
                            $(document.body).trigger('added_to_cart', [fragments, cartHash, $button]);
                        } else {
                            $(document.body).trigger('added_to_cart', [{}, cartHash, $button]);
                        }
                        
                        setTimeout(function() {
                            $button.text(originalText);
                            $button.css('background', '');
                            $button.prop('disabled', false);
                        }, 2000);
                    } else {
                        // Показываем ошибку
                        const errorMsg = (response.data && response.data.message) ? response.data.message : 'Ошибка добавления товара';
                        alert(errorMsg);
                        console.error('Ошибка добавления в корзину:', response);
                        
                        $button.text(originalText);
                        $button.prop('disabled', false);
                    }
                },
                error: function(xhr, status, error) {
                    console.error('AJAX ошибка:', error, 'Status:', status);
                    
                    // Проверяем, возможно сервер вернул HTML вместо JSON
                    if (xhr.responseText && xhr.responseText.trim().startsWith('<')) {
                        console.error('Сервер вернул HTML вместо JSON:', xhr.responseText.substring(0, 200));
                        alert('Ошибка: сервер вернул некорректный ответ. Проверьте консоль для подробностей.');
                    } else {
                        alert('Произошла ошибка при добавлении товара в корзину');
                    }
                    
                    $button.text(originalText);
                    $button.prop('disabled', false);
                }
            });
            
            return false;
        });
    });
    </script>
    <?php
}
add_action( 'wp_footer', 'asker_intercept_add_to_cart_form' );

/**
 * AJAX обработчик для добавления товара в корзину
 */
function asker_add_to_cart_ajax() {
    // Останавливаем любые буферы вывода
    while ( ob_get_level() > 0 ) {
        ob_end_clean();
    }
    
    // Устанавливаем правильные заголовки для JSON
    if ( ! headers_sent() ) {
        header( 'Content-Type: application/json; charset=utf-8' );
    }
    
    // ВРЕМЕННО отключаем хук, который выводит скрипт в AJAX ответе
    // Это предотвращает вывод <script> перед JSON
    remove_action( 'woocommerce_add_to_cart', 'asker_update_cart_count_ajax' );
    
    // Проверяем nonce для безопасности (но не блокируем если нет)
    if ( isset( $_POST['security'] ) ) {
        if ( ! check_ajax_referer( 'woocommerce-add-to-cart', 'security', false ) ) {
            wp_send_json_error( array( 'message' => 'Ошибка безопасности' ) );
            return;
        }
    }
    
    $product_id = intval( $_POST['product_id'] ?? 0 );
    $quantity = intval( $_POST['quantity'] ?? 1 );
    
    if ( ! $product_id ) {
        wp_send_json_error( array( 'message' => 'Неверный ID товара' ) );
        return;
    }
    
    // Инициализируем корзину WooCommerce
    if ( ! isset( WC()->cart ) ) {
        wc_load_cart();
    }
    
    // Получаем товар
    $product = wc_get_product( $product_id );
    
    if ( ! $product ) {
        wp_send_json_error( array( 'message' => 'Товар не найден' ) );
        return;
    }
    
    // Очищаем все уведомления WooCommerce перед добавлением
    wc_clear_notices();
    
    // Добавляем товар в корзину (проверки доступности отключены через фильтры woocommerce_is_purchasable и woocommerce_add_to_cart_validation)
        $cart_item_key = WC()->cart->add_to_cart( $product_id, $quantity );
        
        if ( $cart_item_key ) {
        // Пересчитываем корзину
        WC()->cart->calculate_totals();
        
        // Останавливаем все буферы перед отправкой JSON
        while ( ob_get_level() > 0 ) {
            ob_end_clean();
        }
        
        // Восстанавливаем хук обратно после добавления
        add_action( 'woocommerce_add_to_cart', 'asker_update_cart_count_ajax' );
        
        // Формируем ответ в формате WooCommerce (fragments + cart_hash) для совместимости
        $fragments = apply_filters( 'woocommerce_add_to_cart_fragments', array() );
        $cart_hash = WC()->cart->get_cart_hash();
        
            wp_send_json_success( array(
                'cart_item_key' => $cart_item_key,
            'cart_count' => WC()->cart->get_cart_contents_count(),
            'message' => 'Товар добавлен в корзину',
            'fragments' => $fragments,
            'cart_hash' => $cart_hash
            ) );
        } else {
        // Получаем ошибки от WooCommerce
        $notices = wc_get_notices( 'error' );
        $error_message = 'Не удалось добавить товар в корзину';
        
        if ( ! empty( $notices ) ) {
            // Берем первую ошибку
            $error_message = is_array( $notices[0] ) ? ( $notices[0]['notice'] ?? $error_message ) : $notices[0];
        }
        
        // Если ошибок нет, но товар не добавился - проверяем дополнительные причины
        if ( empty( $notices ) ) {
            // Проверяем, может товар уже в корзине
            $cart_contents = WC()->cart->get_cart();
            foreach ( $cart_contents as $cart_item ) {
                if ( $cart_item['product_id'] == $product_id || $cart_item['variation_id'] == $product_id ) {
                    $error_message = 'Товар уже в корзине';
                    break;
                }
            }
        }
        
        // Останавливаем все буферы перед отправкой JSON
        while ( ob_get_level() > 0 ) {
            ob_end_clean();
        }
        
        // Восстанавливаем хук перед отправкой ошибки
        add_action( 'woocommerce_add_to_cart', 'asker_update_cart_count_ajax' );
        
        wp_send_json_error( array( 
            'message' => $error_message,
            'reason' => 'add_failed',
            'product_id' => $product_id
        ) );
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
        
        // Устанавливаем адрес доставки и привязываем к пользователю
        if ( is_user_logged_in() ) {
            $user_id = get_current_user_id();
            $billing_data = get_user_meta( $user_id );
            
            // ВАЖНО: Привязываем заказ к пользователю
            $order->set_customer_id( $user_id );
            
            $order->set_billing_first_name( $billing_data['billing_first_name'][0] ?? 'Админ' );
            $order->set_billing_last_name( $billing_data['billing_last_name'][0] ?? 'Пользователь' );
            $order->set_billing_email( $billing_data['billing_email'][0] ?? get_userdata( $user_id )->user_email );
            $order->set_billing_phone( $billing_data['billing_phone'][0] ?? '+7 (999) 123-45-67' );
            $order->set_billing_city( $billing_data['billing_city'][0] ?? 'Москва' );
            $order->set_billing_address_1( $billing_data['billing_address_1'][0] ?? 'ул. Тестовая, д. 1' );
        } else {
            // Для неавторизованных пользователей
            // Пытаемся найти пользователя по email из формы
            $guest_email = isset( $_POST['billing_email'] ) ? sanitize_email( $_POST['billing_email'] ) : '';
            
            if ( $guest_email && email_exists( $guest_email ) ) {
                // Если есть пользователь с таким email - привязываем к нему
                $user = get_user_by( 'email', $guest_email );
                if ( $user ) {
                    $order->set_customer_id( $user->ID );
                }
            }
            
            $order->set_billing_first_name( isset( $_POST['billing_first_name'] ) ? sanitize_text_field( $_POST['billing_first_name'] ) : 'Гость' );
            $order->set_billing_last_name( isset( $_POST['billing_last_name'] ) ? sanitize_text_field( $_POST['billing_last_name'] ) : 'Пользователь' );
            $order->set_billing_email( $guest_email ?: 'guest@example.com' );
            $order->set_billing_phone( isset( $_POST['billing_phone'] ) ? sanitize_text_field( $_POST['billing_phone'] ) : '+7 (999) 123-45-67' );
            $order->set_billing_city( isset( $_POST['billing_city'] ) ? sanitize_text_field( $_POST['billing_city'] ) : 'Москва' );
            $order->set_billing_address_1( isset( $_POST['billing_address_1'] ) ? sanitize_text_field( $_POST['billing_address_1'] ) : 'ул. Тестовая, д. 1' );
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
 * WooCommerce уже имеет встроенное поле "Category thumbnail" 
 * которое автоматически сохраняется в thumbnail_id
 * Используем только стандартное поле WooCommerce - никаких кастомных полей!
 * 
 * При редактировании категории используй стандартное поле WooCommerce "Thumbnail"
 * Оно синхронизируется с thumbnail_id и работает везде на сайте
 */

/**
 * Получить минимальную и максимальную цену товаров в каталоге
 * 
 * Функция использует WooCommerce API для получения цен всех товаров.
 * Это более надежный способ, чем прямой SQL запрос, так как учитывает
 * вариативные товары, скидки и другие особенности WooCommerce.
 * 
 * @return array Массив с ключами 'min' и 'max' (округленные до тысяч)
 */
function asker_get_product_price_range() {
    // Проверяем, что WooCommerce активен
    if (!class_exists('WooCommerce')) {
        return [
            'min' => 0,
            'max' => 256000
        ];
    }
    
    // Используем кеш для оптимизации (кеш на 1 час)
    $cache_key = 'asker_price_range';
    
    // ВРЕМЕННО: очищаем кеш для отладки (раскомментировать для принудительного обновления)
    delete_transient($cache_key);
    
    $cached = get_transient($cache_key);
    
    if ($cached !== false) {
        return $cached;
    }
    
    $min_price = null;
    $max_price = null;
    
    // Получаем все опубликованные товары через WP_Query для оптимизации
    $args = [
        'post_type' => 'product',
        'post_status' => 'publish',
        'posts_per_page' => -1, // Все товары
        'fields' => 'ids', // Только ID для оптимизации
        'meta_query' => [
            [
                'key' => '_price',
                'value' => 0,
                'compare' => '>',
                'type' => 'DECIMAL'
            ]
        ]
    ];
    
    $query = new WP_Query($args);
    
    if ($query->have_posts()) {
        foreach ($query->posts as $product_id) {
            $product = wc_get_product($product_id);
            
            if (!$product) {
                continue;
            }
            
            // Получаем цену товара (учитывает скидки и вариации)
            $price = $product->get_price();
            
            // Пропускаем товары без цены или с нулевой ценой
            if (empty($price) || floatval($price) <= 0) {
                continue;
            }
            
            $price = floatval($price);
            
            // Обновляем минимум и максимум
            if ($min_price === null || $price < $min_price) {
                $min_price = $price;
            }
            
            if ($max_price === null || $price > $max_price) {
                $max_price = $price;
            }
        }
        
        wp_reset_postdata();
    }
    
    // Если цены найдены, округляем до тысяч
    if ($min_price !== null && $max_price !== null) {
        $min_price = floor($min_price / 1000) * 1000; // Округляем вниз до тысяч
        $max_price = ceil($max_price / 1000) * 1000; // Округляем вверх до тысяч
        
        // Минимум должен быть хотя бы 0
        $min_price = max(0, $min_price);
        
        $result = [
            'min' => intval($min_price),
            'max' => intval($max_price)
        ];
        
        // Сохраняем в кеш на 1 час
        set_transient($cache_key, $result, HOUR_IN_SECONDS);
        
        return $result;
    }
    
    // Значения по умолчанию, если товаров нет или цены не найдены
    $default = [
        'min' => 0,
        'max' => 256000
    ];
    
    // Сохраняем в кеш даже значения по умолчанию (на 5 минут)
    set_transient($cache_key, $default, 5 * MINUTE_IN_SECONDS);
    
    return $default;
}

/**
 * Очистка кеша диапазона цен при обновлении товара
 * Это нужно, чтобы диапазон цен обновлялся автоматически
 */
add_action('woocommerce_update_product', 'asker_clear_price_range_cache');
add_action('woocommerce_new_product', 'asker_clear_price_range_cache');
add_action('woocommerce_delete_product', 'asker_clear_price_range_cache');
function asker_clear_price_range_cache() {
    delete_transient('asker_price_range');
}

/**
 * Фильтр товаров по цене (min_price и max_price из GET параметров)
 * Применяется ТОЛЬКО если есть GET параметры
 */
function asker_price_filter_query($query) {
    // Проверяем, что это не админка и основной запрос
    if (is_admin() || !$query->is_main_query()) {
        return;
    }
    
    // Применяем только для страниц товаров
    if (!(is_shop() || is_product_category() || is_product_taxonomy())) {
        return;
    }
    
    // Убеждаемся, что товары публичные и доступны всем
    $query->set('post_status', 'publish');
    $query->set('post_type', 'product');
    
    // Фильтр по цене применяется ТОЛЬКО если есть GET параметры
    $min_price = isset($_GET['min_price']) && $_GET['min_price'] !== '' ? floatval($_GET['min_price']) : null;
    $max_price = isset($_GET['max_price']) && $_GET['max_price'] !== '' ? floatval($_GET['max_price']) : null;
    
    // Применяем фильтр только если есть явные параметры в URL
    if ($min_price !== null || $max_price !== null) {
        $meta_query = $query->get('meta_query') ?: [];
        
        // WooCommerce хранит цену в _price (минимальная цена товара)
        // Используем правильный способ фильтрации через meta_query
        if ($min_price !== null && $max_price !== null && $min_price > 0 && $max_price > 0) {
            // Диапазон цен - используем BETWEEN для более точной фильтрации
            $meta_query[] = [
                'key' => '_price',
                'value' => [$min_price, $max_price],
                'compare' => 'BETWEEN',
                'type' => 'DECIMAL'
            ];
        } elseif ($min_price !== null && $min_price > 0) {
            // Только минимальная цена
            $meta_query[] = [
                'key' => '_price',
                'value' => $min_price,
                'compare' => '>=',
                'type' => 'DECIMAL'
            ];
        } elseif ($max_price !== null && $max_price > 0) {
            // Только максимальная цена
            $meta_query[] = [
                'key' => '_price',
                'value' => $max_price,
                'compare' => '<=',
                'type' => 'DECIMAL'
            ];
        }
        
        if (!empty($meta_query)) {
            $query->set('meta_query', $meta_query);
        }
    }
}
add_action('pre_get_posts', 'asker_price_filter_query', 20);

/**
 * Исправление некорректных запросов: если /product/slug открывается как товар,
 * но на самом деле это категория - делаем редирект
 */
function asker_fix_product_category_requests() {
    if (!class_exists('WooCommerce')) {
        return;
    }
    
    // Проверяем только если это 404 или запрос на товар, который не найден
    if (!is_404() && !is_singular('product')) {
        return;
    }
    
    // Получаем slug из URL
    $slug = get_query_var('name') ?: get_query_var('product');
    
    if (empty($slug)) {
        return;
    }
    
    // Проверяем, есть ли категория с таким slug
    $category = get_term_by('slug', $slug, 'product_cat');
    
    if ($category && !is_wp_error($category)) {
        // Проверяем, есть ли товар с таким slug (чтобы не редиректить если товар существует)
        $product_query = new WP_Query(array(
            'name' => $slug,
            'post_type' => 'product',
            'post_status' => 'publish',
            'posts_per_page' => 1,
        ));
        
        $product_exists = $product_query->have_posts();
        wp_reset_postdata();
        
        // Если товара нет, но есть категория - делаем редирект
        if (!$product_exists) {
            $category_link = get_term_link($category);
            if (!is_wp_error($category_link)) {
                wp_redirect($category_link, 301);
                exit;
            }
        }
    }
}
add_action('template_redirect', 'asker_fix_product_category_requests', 1);

/**
 * Сохраняем имя и фамилию при регистрации пользователя
 */
function asker_save_user_name_on_registration( $customer_id, $new_customer_data, $password_generated ) {
    // Сохраняем имя и фамилию из POST данных
    if ( isset( $_POST['first_name'] ) && ! empty( $_POST['first_name'] ) ) {
        $first_name = sanitize_text_field( $_POST['first_name'] );
        update_user_meta( $customer_id, 'first_name', $first_name );
        update_user_meta( $customer_id, 'billing_first_name', $first_name );
    }
    
    if ( isset( $_POST['last_name'] ) && ! empty( $_POST['last_name'] ) ) {
        $last_name = sanitize_text_field( $_POST['last_name'] );
        update_user_meta( $customer_id, 'last_name', $last_name );
        update_user_meta( $customer_id, 'billing_last_name', $last_name );
    }
    
    // Если имя не указано, но указан username - используем его как имя
    if ( ! isset( $_POST['first_name'] ) || empty( $_POST['first_name'] ) ) {
        if ( isset( $_POST['username'] ) && ! empty( $_POST['username'] ) ) {
            $username = sanitize_text_field( $_POST['username'] );
            update_user_meta( $customer_id, 'first_name', $username );
            update_user_meta( $customer_id, 'billing_first_name', $username );
        }
    }
}
add_action( 'woocommerce_created_customer', 'asker_save_user_name_on_registration', 10, 3 );

/**
 * ========================================
 * БЕЗОПАСНОСТЬ
 * ========================================
 */

/**
 * Скрываем версию WordPress из HEAD
 * Защита от автоматических атак на известные уязвимости
 */
function asker_remove_wp_version() {
    return '';
}
add_filter( 'the_generator', 'asker_remove_wp_version' );
remove_action( 'wp_head', 'wp_generator' );

/**
 * Скрываем версию WooCommerce из скриптов
 */
function asker_remove_wc_version( $src ) {
    if ( strpos( $src, 'ver=' ) ) {
        $src = remove_query_arg( 'ver', $src );
    }
    return $src;
}
add_filter( 'script_loader_src', 'asker_remove_wc_version', 15, 1 );
add_filter( 'style_loader_src', 'asker_remove_wc_version', 15, 1 );

/**
 * Отключаем XML-RPC (частая цель атак)
 * Если нужен для мобильных приложений - закомментировать
 */
add_filter( 'xmlrpc_enabled', '__return_false' );

/**
 * Добавляем security headers
 */
function asker_add_security_headers() {
    // Защита от clickjacking
    header( 'X-Frame-Options: SAMEORIGIN' );
    
    // Защита от MIME-sniffing
    header( 'X-Content-Type-Options: nosniff' );
    
    // Включаем XSS защиту браузера
    header( 'X-XSS-Protection: 1; mode=block' );
    
    // Referrer Policy
    header( 'Referrer-Policy: strict-origin-when-cross-origin' );
}
add_action( 'send_headers', 'asker_add_security_headers' );

/**
 * Отключаем file editing через админку
 * Если нужно редактировать файлы через админку - закомментировать
 */
if ( ! defined( 'DISALLOW_FILE_EDIT' ) ) {
    define( 'DISALLOW_FILE_EDIT', true );
}

/**
 * Ограничиваем количество попыток входа (базовая защита от брутфорса)
 * Для production лучше использовать Wordfence или подобный плагин
 */
function asker_check_login_attempts( $user, $username, $password ) {
    // Получаем IP пользователя
    $ip = $_SERVER['REMOTE_ADDR'] ?? '';
    
    if ( empty( $ip ) ) {
        return $user;
    }
    
    // Ключ для transient
    $transient_key = 'asker_login_attempts_' . md5( $ip );
    
    // Получаем количество попыток
    $attempts = get_transient( $transient_key );
    
    // Если больше 5 попыток за 15 минут - блокируем
    if ( $attempts && $attempts >= 5 ) {
        return new WP_Error(
            'too_many_attempts',
            sprintf(
                'Слишком много попыток входа. Попробуйте через %d минут.',
                ceil( ( 900 - ( time() - get_option( $transient_key . '_time', time() ) ) ) / 60 )
            )
        );
    }
    
    return $user;
}
add_filter( 'authenticate', 'asker_check_login_attempts', 30, 3 );

/**
 * Увеличиваем счетчик неудачных попыток входа
 */
function asker_failed_login_attempts( $username ) {
    $ip = $_SERVER['REMOTE_ADDR'] ?? '';
    
    if ( empty( $ip ) ) {
        return;
    }
    
    $transient_key = 'asker_login_attempts_' . md5( $ip );
    $attempts = get_transient( $transient_key );
    
    if ( ! $attempts ) {
        $attempts = 1;
        update_option( $transient_key . '_time', time() );
    } else {
        $attempts++;
    }
    
    // Блокируем на 15 минут
    set_transient( $transient_key, $attempts, 900 );
}
add_action( 'wp_login_failed', 'asker_failed_login_attempts' );

/**
 * Сбрасываем счетчик при успешном входе
 */
function asker_reset_login_attempts( $user_login, $user ) {
    $ip = $_SERVER['REMOTE_ADDR'] ?? '';
    
    if ( empty( $ip ) ) {
        return;
    }
    
    $transient_key = 'asker_login_attempts_' . md5( $ip );
    delete_transient( $transient_key );
    delete_option( $transient_key . '_time' );
}
add_action( 'wp_login', 'asker_reset_login_attempts', 10, 2 );

/**
 * Добавляем заголовок на страницу восстановления пароля
 */
function asker_add_lost_password_title() {
    if ( isset( $_GET['lost-password'] ) || isset( $_GET['reset-link-sent'] ) ) {
        echo '<h1 class="auth-page-title">Восстановление пароля</h1>';
        echo '<p class="auth-page-description">Введите E-mail, указанный при регистрации и мы отправим вам ссылку для восстановления пароля</p>';
    }
}
add_action( 'woocommerce_before_lost_password_form', 'asker_add_lost_password_title', 5 );


