<?php
/**
 * Скрытие неиспользуемых типов постов из меню админки
 * 
 * Эти типы постов были созданы ранее (плагином или старой темой)
 * и остались в базе данных. Просто скрываем их из меню админки.
 */

/**
 * Список типов постов, которые нужно скрыть
 * Добавь сюда точные названия (slug) типов постов
 */
function asker_get_hidden_post_types() {
    return [
        'stati',        // Статьи (точное название из админки)
        'uslugi',       // Услуги (точное название из админки)
        // Проекты уже удалились, но оставляем на случай, если появятся снова
        'project',
        'projects',
    ];
}

/**
 * Скрываем типы постов и страницы настроек из меню админки
 * Используем хук admin_menu с высоким приоритетом, чтобы выполниться после всех регистраций
 */
add_action('admin_menu', function() {
    // Скрываем типы постов
    $hidden_post_types = asker_get_hidden_post_types();
    
    foreach ($hidden_post_types as $post_type) {
        // Проверяем, существует ли тип поста
        if (!post_type_exists($post_type)) {
            continue;
        }
        
        // Удаляем главный пункт меню
        remove_menu_page('edit.php?post_type=' . $post_type);
        
        // Удаляем подменю (если есть)
        global $submenu;
        $menu_slug = 'edit.php?post_type=' . $post_type;
        if (isset($submenu[$menu_slug])) {
            unset($submenu[$menu_slug]);
        }
    }
    
    // Скрываем страницы настроек ACF
    // Это главные пункты меню (toplevel_page), а не подменю
    $hidden_pages = [
        'common_site_content',      // Общий контент сайта (главный пункт меню)
        // 'common_site_settings' - убрали, эта страница нужна
    ];
    
    foreach ($hidden_pages as $page_slug) {
        // Пробуем стандартный способ
        remove_menu_page($page_slug);
        
        // Если не сработало, удаляем напрямую через глобальную переменную $menu
        global $menu;
        if (isset($menu) && is_array($menu)) {
            foreach ($menu as $key => $item) {
                // Проверяем разные варианты slug
                if (isset($item[2]) && (
                    $item[2] === $page_slug || 
                    $item[2] === 'admin.php?page=' . $page_slug ||
                    $item[2] === 'toplevel_page_' . $page_slug
                )) {
                    unset($menu[$key]);
                }
            }
        }
    }
}, 999); // Высокий приоритет - выполнится после всех регистраций

