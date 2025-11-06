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
require_once get_template_directory() . '/inc/helpers.php';
require_once get_template_directory() . '/inc/create-pages-helper.php';
require_once get_template_directory() . '/inc/customer-levels.php';
require_once get_template_directory() . '/inc/managers.php';

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
