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

// Убрали страницу настроек - теперь всё в Customizer и ACF





