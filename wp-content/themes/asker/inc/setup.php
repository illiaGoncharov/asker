<?php
/**
 * Инициализация темы: поддержка возможностей, меню, локализация.
 */

add_action('after_setup_theme', function () {
    // Принудительно устанавливаем русский язык для WordPress и WooCommerce
    if ( !get_option('WPLANG') ) {
        update_option('WPLANG', 'ru_RU');
    }
    
    // Устанавливаем локаль
    add_filter('locale', function($locale) {
        return 'ru_RU';
    }, 10, 1);
    
    // Принудительно загружаем русский язык для WooCommerce
    add_filter('plugin_locale', function($locale, $domain) {
        if ($domain === 'woocommerce') {
            return 'ru_RU';
        }
        return $locale;
    }, 10, 2);
    
    // Локализация строк темы
    load_theme_textdomain('asker', get_template_directory() . '/languages');
    
    // Пытаемся загрузить русский язык для WooCommerce
    if (class_exists('WooCommerce')) {
        load_plugin_textdomain('woocommerce', false, dirname(plugin_basename(WC_PLUGIN_FILE)) . '/languages/');
    }

    // Базовая поддержка
    add_theme_support('title-tag');
    add_theme_support('post-thumbnails');
    add_theme_support('html5', ['search-form','comment-form','comment-list','gallery','caption','style','script']);

    // WooCommerce
    add_theme_support('woocommerce');
    
    // Поддержка кастомного логотипа
    add_theme_support('custom-logo', [
        'height'      => 60,
        'width'       => 200,
        'flex-height' => true,
        'flex-width'  => true,
    ]);
    
    // Поддержка SVG
    add_filter('upload_mimes', function($mimes) {
        $mimes['svg'] = 'image/svg+xml';
        return $mimes;
    });
    
    add_filter('wp_check_filetype_and_ext', function($data, $file, $filename, $mimes) {
        $filetype = wp_check_filetype($filename, $mimes);
        return [
            'ext'             => $filetype['ext'],
            'type'            => $filetype['type'],
            'proper_filename' => $data['proper_filename']
        ];
    }, 10, 4);

    // Меню
    register_nav_menus([
        'primary' => __('Primary Menu', 'asker'),
        'footer'  => __('Footer Menu', 'asker'),
    ]);
});

// Отключение Gutenberg (блочного редактора)
add_filter('use_block_editor_for_post', '__return_false', 10);
add_filter('use_block_editor_for_post_type', '__return_false', 10);

// Отключение Gutenberg для виджетов
add_filter('use_widgets_block_editor', '__return_false');

// Отключение Gutenberg для кастомайзера
add_filter('use_block_editor_for_post_type', function($use_block_editor, $post_type) {
    if ($post_type === 'page') {
        return false;
    }
    return $use_block_editor;
}, 10, 2);

/**
 * Создаём базовые страницы при активации темы: главная и юридические.
 * Не трогаем страницы WooCommerce — их создаст мастер Woo.
 */
function asker_create_page_if_missing($title, $slug, $content = '') {
    $existing = get_page_by_path($slug, OBJECT, 'page');
    if ($existing instanceof WP_Post) {
        return (int) $existing->ID;
    }
    $id = wp_insert_post([
        'post_title'   => wp_strip_all_tags($title),
        'post_name'    => sanitize_title($slug),
        'post_content' => $content,
        'post_status'  => 'publish',
        'post_type'    => 'page',
    ]);
    return is_wp_error($id) ? 0 : (int) $id;
}

add_action('after_switch_theme', function () {
    // Главная страница
    $home_id = asker_create_page_if_missing(__('Главная', 'asker'), 'home');
    if ($home_id) {
        update_option('show_on_front', 'page');
        update_option('page_on_front', $home_id);
    }

    // Юридические страницы
    $privacy_id = asker_create_page_if_missing(__('Политика конфиденциальности', 'asker'), 'privacy-policy');
    if ($privacy_id) {
        // Назначаем страницу политики конфиденциальности
        update_option('wp_page_for_privacy_policy', $privacy_id);
    }

    asker_create_page_if_missing(__('Условия использования', 'asker'), 'terms');
    asker_create_page_if_missing(__('Политика cookies', 'asker'), 'cookies');
    asker_create_page_if_missing(__('О компании', 'asker'), 'about');
    asker_create_page_if_missing(__('Доставка и оплата', 'asker'), 'delivery-payment');
    asker_create_page_if_missing(__('Гарантия и возврат', 'asker'), 'returns-warranty');
    asker_create_page_if_missing(__('Контакты', 'asker'), 'contacts');
    
    // Настройка постоянных ссылок
    if (!get_option('permalink_structure')) {
        update_option('permalink_structure', '/%postname%/');
        flush_rewrite_rules();
    }
});

/**
 * Исправляем страницу политики конфиденциальности и другие страницы
 * Убеждаемся, что они имеют правильный slug и опубликованы
 */
function asker_fix_privacy_page() {
    // Проверяем, существует ли страница по слагу
    $privacy_page = get_page_by_path('privacy-policy');
    
    if (!$privacy_page) {
        // Страницы нет - создаём её
        $privacy_id = asker_create_page_if_missing(__('Политика конфиденциальности', 'asker'), 'privacy-policy', '<h2>Политика конфиденциальности</h2><p>Здесь будет размещена политика конфиденциальности.</p>');
        if ($privacy_id) {
            update_option('wp_page_for_privacy_policy', $privacy_id);
        }
    } else {
        // Страница есть - проверяем и исправляем
        if ($privacy_page->post_status !== 'publish') {
            wp_update_post([
                'ID' => $privacy_page->ID,
                'post_status' => 'publish'
            ]);
        }
        
        if ($privacy_page->post_name !== 'privacy-policy') {
            wp_update_post([
                'ID' => $privacy_page->ID,
                'post_name' => 'privacy-policy'
            ]);
        }
        
        // Назначаем страницу в настройках WordPress
        update_option('wp_page_for_privacy_policy', $privacy_page->ID);
    }
    
    // Проверяем страницу с ID=3 (если это политика конфиденциальности)
    $page_id_3 = get_post(3);
    if ($page_id_3 && $page_id_3->post_type === 'page') {
        // Если это страница политики конфиденциальности, исправляем её
        if (stripos($page_id_3->post_title, 'политика') !== false || 
            stripos($page_id_3->post_title, 'privacy') !== false ||
            stripos($page_id_3->post_title, 'конфиденциальн') !== false) {
            
            // Обновляем slug если он неправильный
            if ($page_id_3->post_name !== 'privacy-policy') {
                wp_update_post([
                    'ID' => 3,
                    'post_name' => 'privacy-policy',
                    'post_status' => 'publish'
                ]);
            }
            
            // Назначаем страницу в настройках WordPress
            update_option('wp_page_for_privacy_policy', 3);
        }
    }
    
    // Убеждаемся, что постоянные ссылки настроены
    $permalink_structure = get_option('permalink_structure');
    if (empty($permalink_structure)) {
        update_option('permalink_structure', '/%postname%/');
        flush_rewrite_rules();
    }
}
add_action('admin_init', 'asker_fix_privacy_page', 1);
add_action('init', 'asker_fix_privacy_page', 1);

/**
 * Редирект с ?page_id=3 на нормальный URL
 */
function asker_redirect_page_id_to_slug() {
    if (isset($_GET['page_id']) && $_GET['page_id'] == 3) {
        $page = get_post(3);
        if ($page && $page->post_type === 'page' && $page->post_status === 'publish') {
            $permalink = get_permalink($page->ID);
            if ($permalink && $permalink !== home_url($_SERVER['REQUEST_URI'])) {
                wp_redirect($permalink, 301);
                exit;
            }
        }
    }
}
add_action('template_redirect', 'asker_redirect_page_id_to_slug', 1);


