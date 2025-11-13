<?php
/**
 * Принудительное обновление файлов и очистка кэша
 * ВАЖНО: Удалите этот файл после использования!
 * 
 * Использование: откройте в браузере http://askerspb.beget.tech/wp-content/themes/asker/force-update.php
 */

// Загружаем WordPress
require_once('../../../wp-load.php');

// Проверяем права доступа
if (!current_user_can('manage_options')) {
    die('Доступ запрещен. Только администраторы.');
}

echo '<h1>Принудительное обновление</h1>';
echo '<pre>';

// 1. Очистка OPcache
if (function_exists('opcache_reset')) {
    opcache_reset();
    echo "✓ OPcache очищен\n";
} else {
    echo "⚠ OPcache не доступен\n";
}

// 2. Очистка всех кэшей WordPress
if (function_exists('wp_cache_flush')) {
    wp_cache_flush();
    echo "✓ WordPress кэш очищен\n";
}

// 3. Очистка кэша плагинов
if (function_exists('w3tc_flush_all')) {
    w3tc_flush_all();
    echo "✓ W3 Total Cache очищен\n";
}

if (function_exists('wp_super_cache_flush')) {
    wp_super_cache_flush();
    echo "✓ WP Super Cache очищен\n";
}

// 4. Очистка кэша WooCommerce
if (function_exists('wc_delete_product_transients')) {
    wc_delete_product_transients();
    echo "✓ WooCommerce кэш очищен\n";
}

// 5. Очистка transients
global $wpdb;
$deleted = $wpdb->query("DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_%' OR option_name LIKE '_site_transient_%'");
echo "✓ Удалено {$deleted} transients\n";

// 6. Очистка кэша FacetWP (если установлен)
if (class_exists('FacetWP')) {
    if (function_exists('FWP')) {
        FWP()->facet->renderer->reset_cache();
        echo "✓ FacetWP кэш очищен\n";
    }
}

// 7. Принудительная перезагрузка темы
if (function_exists('switch_theme')) {
    $current_theme = get_stylesheet();
    switch_theme($current_theme);
    echo "✓ Тема перезагружена\n";
}

// 8. Проверка файла content-product.php
$template_file = get_template_directory() . '/woocommerce/content-product.php';
if (file_exists($template_file)) {
    $file_content = file_get_contents($template_file);
    if (strpos($file_content, 'shop-product-content') !== false) {
        echo "✓ Файл content-product.php содержит shop-product-content\n";
        echo "  Размер файла: " . filesize($template_file) . " байт\n";
        echo "  Дата изменения: " . date('Y-m-d H:i:s', filemtime($template_file)) . "\n";
    } else {
        echo "⚠ Файл content-product.php НЕ содержит shop-product-content!\n";
    }
} else {
    echo "⚠ Файл content-product.php не найден!\n";
}

echo "\n✅ Все операции завершены!\n";
echo '</pre>';

echo '<p><strong>ВАЖНО:</strong> Удалите этот файл после использования!</p>';
echo '<p><a href="' . home_url('/shop') . '">Перейти на страницу каталога</a></p>';
echo '<p><a href="' . admin_url() . '">Вернуться в админку</a></p>';


