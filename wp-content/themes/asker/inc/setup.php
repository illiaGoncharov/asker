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


