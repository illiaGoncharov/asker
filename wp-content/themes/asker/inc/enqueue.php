<?php
/**
 * Подключение стилей и скриптов с версионированием по времени изменения файла.
 */

add_action('wp_enqueue_scripts', function () {
    // style.css темы (глобальные стили)
    $style_css_path = get_stylesheet_directory() . '/style.css';
    if (file_exists($style_css_path)) {
        wp_enqueue_style(
            'asker-style',
            get_stylesheet_uri(),
            [],
            filemtime($style_css_path)
        );
    }

    // main.css для пользовательских стилей
    $main_css_path = get_template_directory() . '/assets/css/main.css';
    if (file_exists($main_css_path)) {
        wp_enqueue_style(
            'asker-main',
            get_template_directory_uri() . '/assets/css/main.css',
            [],
            filemtime($main_css_path)
        );
    }

    // Основной скрипт темы
    $main_js_path = get_template_directory() . '/assets/js/main.js';
    if (file_exists($main_js_path)) {
        wp_enqueue_script(
            'asker-main',
            get_template_directory_uri() . '/assets/js/main.js',
            [],
            filemtime($main_js_path),
            true
        );
    }

    // Скрипт согласия на cookies
    $cookie_js_path = get_template_directory() . '/assets/js/cookie-consent.js';
    if (file_exists($cookie_js_path)) {
        wp_enqueue_script(
            'asker-cookie',
            get_template_directory_uri() . '/assets/js/cookie-consent.js',
            [],
            filemtime($cookie_js_path),
            true
        );
    }

    // Скрипт Яндекс.Карт - только на странице контактов
    global $post;
    $is_contact_page = is_page_template('templates/page-contact.php') || 
                       is_page('contacts') || 
                       is_page_template('page-contacts.php') || 
                       is_page_template('Contacts Page') ||
                       (isset($post) && $post->post_name === 'contacts');
    
    if ($is_contact_page) {
        $yandex_map_js_path = get_template_directory() . '/assets/js/yandex-map.js';
        if (file_exists($yandex_map_js_path)) {
            wp_enqueue_script(
                'asker-yandex-map',
                get_template_directory_uri() . '/assets/js/yandex-map.js',
                [],
                filemtime($yandex_map_js_path),
                true
            );
        }
    }
});








