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

/**
 * КРИТИЧНО: Принудительно загружаем front-page.php для главной
 * Если template_include фильтр не сработал, используем template_redirect
 */
// НЕ используем template_redirect с exit - это блокирует wp_head() и wp_footer()
// Вместо этого полагаемся на template_include фильтр в woocommerce.php
