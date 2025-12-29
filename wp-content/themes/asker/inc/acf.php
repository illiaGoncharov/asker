<?php
/**
 * ACF интеграция: Local JSON и будущие группы полей.
 */

// Сохраняем/читаем JSON групп полей в папке темы `acf-json/`
add_filter('acf/settings/save_json', function () {
    return get_template_directory() . '/acf-json';
});

add_filter('acf/settings/load_json', function ($paths) {
    $paths[] = get_template_directory() . '/acf-json';
    return $paths;
});

// Расширяем условия для ACF групп - добавляем поддержку страниц по slug
add_filter('acf/location/rule_match/page', function($match, $rule, $options) {
    // Проверяем текущую редактируемую страницу
    global $post;
    $current_page_id = isset($post) ? $post->ID : 0;
    $rule_value = (string) $rule['value'];
    
    // Если правило для страницы по slug
    if ($rule['operator'] === '==' && isset($post)) {
        // Проверка по slug
        if ($post->post_name === $rule_value) {
            return true;
        }
        // Проверка по ID
        if (is_numeric($rule_value)) {
            $page_id = (int) $rule_value;
            if ($current_page_id === $page_id) {
                return true;
            }
            if (isset($options['post_id']) && (int) $options['post_id'] === $page_id) {
                return true;
            }
        }
    }
    return $match;
}, 10, 3);

// Добавляем поддержку страниц по slug для группы "Две колонки"
add_filter('acf/location/rule_match/page', function($match, $rule, $options) {
    if ($rule['param'] !== 'page' || $rule['operator'] !== '==') {
        return $match;
    }
    
    global $post;
    if (!isset($post) || $post->post_type !== 'page') {
        return $match;
    }
    
    $rule_value = (string) $rule['value'];
    $page_slugs = ['payment', 'delivery', 'about'];
    
    // Проверяем, если правило для одной из наших страниц
    if (in_array($rule_value, $page_slugs) && $post->post_name === $rule_value) {
        return true;
    }
    
    return $match;
}, 20, 3);

// Убрали страницу настроек - теперь всё в Customizer и ACF





