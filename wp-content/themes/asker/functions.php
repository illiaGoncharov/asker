<?php
/**
 * Точка входа темы: подключение модулей из inc/*.
 * Разносим функционал по файлам для читаемости и поддержки.
 */

/**
 * КРИТИЧНО: Отключаем блочную тему ДО загрузки модулей
 * Это нужно, чтобы front-page.php загружался вместо блочного шаблона
 */
add_filter( 'wp_is_block_theme', '__return_false', 1 );
add_filter( 'block_template_can_be_used', '__return_false', 1 );
add_filter( 'block_template_part_can_be_used', '__return_false', 1 );

require_once get_template_directory() . '/inc/setup.php';
require_once get_template_directory() . '/inc/enqueue.php';
require_once get_template_directory() . '/inc/customizer.php';
require_once get_template_directory() . '/inc/acf.php';
require_once get_template_directory() . '/inc/woocommerce.php';
require_once get_template_directory() . '/inc/checkout-fixes.php'; // Исправления для checkout
require_once get_template_directory() . '/inc/helpers.php';
require_once get_template_directory() . '/inc/create-pages-helper.php';
require_once get_template_directory() . '/inc/customer-levels.php';
require_once get_template_directory() . '/inc/managers.php';
require_once get_template_directory() . '/inc/emails.php';
require_once get_template_directory() . '/inc/seo.php';
require_once get_template_directory() . '/inc/analytics.php';
require_once get_template_directory() . '/inc/form-validation.php';
require_once get_template_directory() . '/inc/registration.php'; // Логика регистрации с верификацией
require_once get_template_directory() . '/inc/schema.php';
require_once get_template_directory() . '/inc/image-optimization.php';
require_once get_template_directory() . '/inc/spam-protection.php';
require_once get_template_directory() . '/inc/hide-post-types.php'; // Скрытие неиспользуемых типов постов
require_once get_template_directory() . '/inc/admin-test-order.php'; // Страница генерации тестового заказа

/**
 * КРИТИЧНО: Принудительно загружаем front-page.php для главной
 * Если template_include фильтр не сработал, используем template_redirect
 */
// НЕ используем template_redirect с exit - это блокирует wp_head() и wp_footer()
// Вместо этого полагаемся на template_include фильтр в woocommerce.php

/**
 * Принудительно используем шаблон page-all-categories.php для страницы all-categories
 * Используем template_include для работы даже если страница еще не создана
 */
add_filter('template_include', function($template) {
    // Игнорируем админку и AJAX запросы
    if (is_admin() || (defined('DOING_AJAX') && DOING_AJAX)) {
        return $template;
    }
    
    // Проверяем по URL (самый надежный способ)
    $request_uri = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '';
    $parsed_uri = parse_url($request_uri, PHP_URL_PATH);
    $parsed_uri = trim($parsed_uri, '/');
    
    // Проверяем различные варианты URL
    $is_all_categories = false;
    
    // Вариант 1: /all-categories
    if ($parsed_uri === 'all-categories') {
        $is_all_categories = true;
    }
    
    // Вариант 2: проверка по слагу страницы
    global $post;
    if ($post && isset($post->post_name) && $post->post_name === 'all-categories') {
        $is_all_categories = true;
    }
    
    // Вариант 3: проверка по шаблону в мета
    if ($post && isset($post->ID)) {
        $page_template = get_post_meta($post->ID, '_wp_page_template', true);
        if ($page_template === 'page-all-categories.php') {
            $is_all_categories = true;
        }
    }
    
    // Вариант 4: проверка через is_page()
    if (function_exists('is_page') && is_page('all-categories')) {
        $is_all_categories = true;
    }
    
    if ($is_all_categories) {
        $custom_template = get_template_directory() . '/page-all-categories.php';
        if (file_exists($custom_template)) {
            return $custom_template;
        }
    }
    
    return $template;
}, 20);


/**
 * Убираем ненужные контактные методы (соцсети) из профиля пользователя
 */
function asker_remove_contact_methods( $contactmethods ) {
    unset( $contactmethods['twitter'] );
    unset( $contactmethods['facebook'] );
    unset( $contactmethods['instagram'] );
    unset( $contactmethods['linkedin'] );
    unset( $contactmethods['myspace'] );
    unset( $contactmethods['pinterest'] );
    unset( $contactmethods['soundcloud'] );
    unset( $contactmethods['tumblr'] );
    unset( $contactmethods['youtube'] );
    unset( $contactmethods['wikipedia'] );
    unset( $contactmethods['github'] );
    unset( $contactmethods['wordpress'] );
    return $contactmethods;
}
add_filter( 'user_contactmethods', 'asker_remove_contact_methods', 999 );

// Показываем поле в профиле пользователя (админка)
add_action('show_user_profile', 'add_company_inn_to_admin_profile');
add_action('edit_user_profile', 'add_company_inn_to_admin_profile');

function add_company_inn_to_admin_profile($user) {
    ?>
    <h3>Дополнительные данные</h3>
    <table class="form-table">
        <tr>
            <th><label for="company_inn">ИНН компании</label></th>
            <td>
                <input
                    type="text"
                    name="company_inn"
                    id="company_inn"
                    value="<?php echo esc_attr(get_user_meta($user->ID, 'company_inn', true)); ?>"
                    class="regular-text"
                    
                />
                <p class="description">ИНН, указанный пользователем при регистрации</p>
            </td>
        </tr>
    </table>
    <?php
}


//Проверка на спам выкл у запроса из ЛК
// Отключаем проверку на спам для формы запроса скидки
add_filter('wpcf7_spam', function($spam) {
    if (isset($_POST['_wpcf7']) && $_POST['_wpcf7'] === 'eaa3825') {
        return false; // Не спам
    }
    return $spam;
});


/**
 * Добавляем попап для запроса скидки
 */
add_action('wp_footer', function() {
    if (!is_account_page()) {
        return; // Только на странице ЛК
    }
    
    $user_id = get_current_user_id();
    $user = wp_get_current_user();
    ?>
    
    <!-- Скрытый контейнер с формой -->
    <div id="discount-form-template" style="display: none;">
        <?php echo do_shortcode('[fluentform id="3"]'); ?>
    </div>
    
    <script>
    (function() {
        const userId = '<?php echo $user_id; ?>';
        const userEmail = '<?php echo esc_js($user->user_email); ?>';
        const userName = '<?php echo esc_js($user->display_name); ?>';
        
        window.openDiscountRequestPopup = function() {
            const requestSent = localStorage.getItem('discount_request_sent_' + userId);
            
            const popup = document.createElement('div');
            popup.className = 'discount-popup-overlay';
            
            if (requestSent === 'true') {
                popup.innerHTML = '<div class="discount-popup">' +
                    '<button class="popup-close" onclick="closeDiscountPopup()">&times;</button>' +
                    '<div class="discount-request-popup">' +
                    '<h3>Ваша заявка в обработке</h3>' +
                    '<p>Мы получили ваш запрос и свяжемся с вами в ближайшее время.</p>' +
                    '</div></div>';
            } else {
                popup.innerHTML = '<div class="discount-popup">' +
                    '<button class="popup-close" onclick="closeDiscountPopup()">&times;</button>' +
                    '<div class="discount-request-popup">' +
                    '<h3>Хотите получить скидку?</h3>' +
                    '<p>Мы рассмотрим вашу заявку и свяжемся с вами в ближайшее время.</p>' +
                    '<div class="form-container"></div>' +
                    '</div></div>';
                
                document.body.appendChild(popup);
                
                // Копируем форму из скрытого контейнера
                const template = document.getElementById('discount-form-template');
                const formContainer = popup.querySelector('.form-container');
                formContainer.innerHTML = template.innerHTML;
                
                // Заполняем поля
                setTimeout(function() {
                    const form = popup.querySelector('.frm-fluent-form');
                    if (form) {
                        const userIdField = form.querySelector('input[name="user_id"]');
                        const userEmailField = form.querySelector('input[name="user_email"]');
                        const userNameField = form.querySelector('input[name="user_name"]');
                        
                        if (userIdField) userIdField.value = userId;
                        if (userEmailField) userEmailField.value = userEmail;
                        if (userNameField) userNameField.value = userName;
                    }
                }, 500);
                
                document.body.style.overflow = 'hidden';
                
                popup.addEventListener('click', function(e) {
                    if (e.target === popup) {
                        closeDiscountPopup();
                    }
                });
                
                return;
            }
            
            document.body.appendChild(popup);
            document.body.style.overflow = 'hidden';
            
            popup.addEventListener('click', function(e) {
                if (e.target === popup) {
                    closeDiscountPopup();
                }
            });
        };
        
        window.closeDiscountPopup = function() {
            const popup = document.querySelector('.discount-popup-overlay');
            if (popup) {
                popup.remove();
                document.body.style.overflow = '';
            }
        };
        
        document.addEventListener('fluentform_submission_success', function(event) {
            const popup = document.querySelector('.discount-popup-overlay');
            
            if (popup) {
                const contentDiv = popup.querySelector('.discount-request-popup');
                
                if (contentDiv) {
                    contentDiv.innerHTML = '<h3>Ваша заявка отправлена</h3>' +
                        '<p>Мы свяжемся с вами в ближайшее время.</p>';
                    
                    localStorage.setItem('discount_request_sent_' + userId, 'true');
                    
                    setTimeout(function() {
                        closeDiscountPopup();
                    }, 3000);
                }
            }
        });
    })();
    </script>
    
    <?php
});

// Временная диагностика — удалить после проверки
add_action('woocommerce_created_customer', function($customer_id) {
    $all_meta = get_user_meta($customer_id);
    $relevant = array();
    foreach ($all_meta as $key => $value) {
        if (strpos($key, 'billing') !== false || strpos($key, 'company') !== false || strpos($key, 'inn') !== false || strpos($key, 'tax') !== false || strpos($key, 'customer_type') !== false) {
            $relevant[$key] = $value[0];
        }
    }
    error_log('[ASKER REG] User #' . $customer_id . ' meta: ' . print_r($relevant, true));
}, 999);

// Диагностика — отслеживаем удаление/изменение мета документов
add_action('updated_order_meta', function($meta_id, $order_id, $meta_key, $meta_value) {
    if (in_array($meta_key, ['_invoice_file_id', '_waybill_file_id'])) {
        error_log("[DOCS DEBUG] updated_order_meta: order=$order_id key=$meta_key value=$meta_value");
        error_log("[DOCS DEBUG] trace: " . wp_debug_backtrace_summary());
    }
}, 10, 4);

add_action('deleted_order_meta', function($meta_ids, $order_id, $meta_key) {
    if (in_array($meta_key, ['_invoice_file_id', '_waybill_file_id'])) {
        error_log("[DOCS DEBUG] DELETED meta: order=$order_id key=$meta_key");
        error_log("[DOCS DEBUG] trace: " . wp_debug_backtrace_summary());
    }
}, 10, 3);