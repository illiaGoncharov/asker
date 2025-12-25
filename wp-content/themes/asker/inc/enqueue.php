<?php
/**
 * Подключение стилей и скриптов с версионированием по времени изменения файла.
 */

add_action('wp_enqueue_scripts', function () {
    try {
        // style.css темы (глобальные стили)
        $style_css_path = get_stylesheet_directory() . '/style.css';
        if (file_exists($style_css_path) && is_readable($style_css_path)) {
            $style_version = filemtime($style_css_path) ?: time();
            wp_enqueue_style(
                'asker-style',
                get_stylesheet_uri(),
                [],
                $style_version
            );
        }

        // main.css для пользовательских стилей
        $main_css_path = get_template_directory() . '/assets/css/main.css';
        if (file_exists($main_css_path) && is_readable($main_css_path)) {
            // Агрессивный cache-bust: filemtime + размер файла
            $main_css_version = filemtime($main_css_path) . '.' . filesize($main_css_path);
            wp_enqueue_style(
                'asker-main',
                get_template_directory_uri() . '/assets/css/main.css',
                [],
                $main_css_version
            );
        }
        
        // header.css для стилей шапки
        $header_css_path = get_template_directory() . '/assets/css/header.css';
        if (file_exists($header_css_path) && is_readable($header_css_path)) {
            $header_css_version = filemtime($header_css_path) ?: time();
            wp_enqueue_style(
                'asker-header',
                get_template_directory_uri() . '/assets/css/header.css',
                ['asker-main'], // Зависит от main.css
                $header_css_version
            );
        }
        
        // pages.css для стилей страниц (вход, регистрация, контакты и т.д.)
        $pages_css_path = get_template_directory() . '/assets/css/pages.css';
        if (file_exists($pages_css_path) && is_readable($pages_css_path)) {
            $pages_css_version = filemtime($pages_css_path) ?: time();
            wp_enqueue_style(
                'asker-pages',
                get_template_directory_uri() . '/assets/css/pages.css',
                ['asker-main'], // Зависит от main.css
                $pages_css_version
            );
        }

        // Основной скрипт темы (зависит от jQuery)
        $main_js_path = get_template_directory() . '/assets/js/main.js';
        if (file_exists($main_js_path) && is_readable($main_js_path)) {
            $main_js_version = filemtime($main_js_path) ?: time();
            wp_enqueue_script(
                'asker-main',
                get_template_directory_uri() . '/assets/js/main.js',
                ['jquery'], // jQuery как зависимость
                $main_js_version,
                true
            );
            // Локализация для AJAX
            wp_localize_script('asker-main', 'asker_ajax', array(
                'ajax_url' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('asker_ajax_nonce')
            ));
        }

        // Скрипт согласия на cookies
        $cookie_js_path = get_template_directory() . '/assets/js/cookie-consent.js';
        if (file_exists($cookie_js_path) && is_readable($cookie_js_path)) {
            $cookie_js_version = filemtime($cookie_js_path) ?: time();
            wp_enqueue_script(
                'asker-cookie',
                get_template_directory_uri() . '/assets/js/cookie-consent.js',
                [],
                $cookie_js_version,
                true
            );
        }

        // Скрипт валидации форм
        $validation_js_path = get_template_directory() . '/assets/js/form-validation.js';
        if (file_exists($validation_js_path) && is_readable($validation_js_path)) {
            $validation_js_version = filemtime($validation_js_path) ?: time();
            wp_enqueue_script(
                'asker-form-validation',
                get_template_directory_uri() . '/assets/js/form-validation.js',
                [],
                $validation_js_version,
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
            if (file_exists($yandex_map_js_path) && is_readable($yandex_map_js_path)) {
                $yandex_map_version = filemtime($yandex_map_js_path) ?: time();
                wp_enqueue_script(
                    'asker-yandex-map',
                    get_template_directory_uri() . '/assets/js/yandex-map.js',
                    [],
                    $yandex_map_version,
                    true
                );
            }
        }
    } catch (Exception $e) {
        // Игнорируем ошибки для предотвращения белого экрана
        return;
    }
});








