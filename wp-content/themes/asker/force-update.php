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

// 1. Очистка OPcache (агрессивная)
if (function_exists('opcache_reset')) {
    // Сначала инвалидируем конкретные файлы
    $critical_files = array(
        'woocommerce/archive-product.php',
        'woocommerce/content-product.php',
        'woocommerce/single-product.php',
        'woocommerce/content-single-product.php',
        'header.php',
        'inc/woocommerce.php',
    );
    
    $template_dir = get_template_directory();
    foreach ($critical_files as $file) {
        $file_path = $template_dir . '/' . $file;
        if (file_exists($file_path) && function_exists('opcache_invalidate')) {
            opcache_invalidate($file_path, true);
        }
    }
    
    // Затем очищаем весь кэш
    opcache_reset();
    echo "✓ OPcache очищен (включая инвалидацию критических файлов)\n";
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

// 8. Проверка ключевых файлов шаблонов
$template_files = array(
    'woocommerce/archive-product.php' => 'shop-wrapper',
    'woocommerce/content-product.php' => 'shop-product-content',
    'woocommerce/single-product.php' => 'container',
    'woocommerce/content-single-product.php' => 'single-product-page',
    'header.php' => 'wishlist-count',
    'inc/woocommerce.php' => 'asker_output_related_products',
    'assets/css/main.css' => 'shop-product-card',
);

echo "\n=== ПРОВЕРКА ФАЙЛОВ ШАБЛОНОВ ===\n";
foreach ($template_files as $file => $check_string) {
    $template_file = get_template_directory() . '/' . $file;
    if (file_exists($template_file)) {
        $file_content = file_get_contents($template_file);
        if (strpos($file_content, $check_string) !== false) {
            echo "✓ Файл {$file} содержит '{$check_string}'\n";
            echo "  Размер: " . filesize($template_file) . " байт, Дата: " . date('Y-m-d H:i:s', filemtime($template_file)) . "\n";
        } else {
            echo "⚠ Файл {$file} НЕ содержит '{$check_string}'!\n";
            echo "  Размер: " . filesize($template_file) . " байт, Дата: " . date('Y-m-d H:i:s', filemtime($template_file)) . "\n";
            echo "  Первые 200 символов: " . substr($file_content, 0, 200) . "...\n";
        }
    } else {
        echo "⚠ Файл {$file} не найден!\n";
    }
}

// 9. Проверка активного шаблона для категорий товаров
echo "\n=== ПРОВЕРКА ШАБЛОНОВ WOOCOMMERCE ===\n";
if (function_exists('wc_get_template')) {
    $template_loader = WC()->template_loader;
    echo "✓ WooCommerce Template Loader активен\n";
} else {
    echo "⚠ WooCommerce Template Loader не найден\n";
}

// Проверяем какой шаблон используется для категорий
$template_hierarchy = array(
    'taxonomy-product_cat.php',
    'archive-product.php',
    'archive.php',
    'index.php'
);

echo "\nПроверка иерархии шаблонов для категорий:\n";
foreach ($template_hierarchy as $template_name) {
    $template_path = get_template_directory() . '/' . $template_name;
    if (file_exists($template_path)) {
        echo "  ✓ {$template_name} существует\n";
    } else {
        echo "  ✗ {$template_name} не найден\n";
    }
}

echo "\n✅ Все операции завершены!\n";
echo '</pre>';

echo '<p><strong>ВАЖНО:</strong> Удалите этот файл после использования!</p>';
echo '<p><a href="' . home_url('/shop') . '">Перейти на страницу каталога</a></p>';
echo '<p><a href="' . admin_url() . '">Вернуться в админку</a></p>';


