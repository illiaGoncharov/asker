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

// Расширяем условия для ACF группы "Контакты" - добавляем поддержку страницы по slug "contacts"
add_filter('acf/location/rule_match/page', function($match, $rule, $options) {
    // Проверяем текущую редактируемую страницу
    global $post;
    $current_page_id = isset($post) ? $post->ID : 0;
    $rule_value = (string) $rule['value'];
    
    // Если правило для страницы со slug "contacts" или ID 12
    if ($rule['operator'] === '==') {
        // Проверка по slug
        if ($rule_value === 'contacts' && isset($post) && $post->post_name === 'contacts') {
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

// Убрали страницу настроек - теперь всё в Customizer и ACF





