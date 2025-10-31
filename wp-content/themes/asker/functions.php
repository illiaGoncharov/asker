<?php
/**
 * Точка входа темы: подключение модулей из inc/*.
 * Разносим функционал по файлам для читаемости и поддержки.
 */

require_once get_template_directory() . '/inc/setup.php';
require_once get_template_directory() . '/inc/enqueue.php';
require_once get_template_directory() . '/inc/customizer.php';
require_once get_template_directory() . '/inc/acf.php';
require_once get_template_directory() . '/inc/woocommerce.php';
require_once get_template_directory() . '/inc/helpers.php';
require_once get_template_directory() . '/inc/create-pages-helper.php'; // Хелпер для создания страниц
