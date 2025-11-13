<?php
/**
 * Скрипт для проверки файлов на сервере
 * ВАЖНО: Удалите этот файл после использования!
 * 
 * Использование: откройте в браузере http://askerspb.beget.tech/wp-content/themes/asker/check-files.php
 */

// Загружаем WordPress
require_once('../../../wp-load.php');

// Проверяем права доступа
if (!current_user_can('manage_options')) {
    die('Доступ запрещен. Только администраторы.');
}

echo '<h1>Проверка файлов на сервере</h1>';
echo '<pre>';

$template_dir = get_template_directory();

// Список критических файлов для проверки
$critical_files = array(
    'woocommerce/archive-product.php',
    'woocommerce/content-product.php',
    'woocommerce/single-product.php',
    'woocommerce/content-single-product.php',
    'header.php',
    'inc/woocommerce.php',
    'assets/css/main.css',
);

echo "=== ПРОВЕРКА КРИТИЧЕСКИХ ФАЙЛОВ ===\n\n";

foreach ($critical_files as $file) {
    $file_path = $template_dir . '/' . $file;
    
    if (file_exists($file_path)) {
        $file_size = filesize($file_path);
        $file_time = filemtime($file_path);
        $file_content = file_get_contents($file_path);
        
        echo "✓ {$file}\n";
        echo "  Размер: {$file_size} байт\n";
        echo "  Дата изменения: " . date('Y-m-d H:i:s', $file_time) . "\n";
        
        // Проверяем кодировку файла
        $encoding = mb_detect_encoding($file_content, array('UTF-8', 'Windows-1251', 'ISO-8859-1'), true);
        echo "  Кодировка: " . ($encoding ? $encoding : 'не определена') . "\n";
        
        // Проверяем ключевые строки в зависимости от файла
        $checks = array();
        switch ($file) {
            case 'woocommerce/archive-product.php':
                $checks[] = array('shop-wrapper', 'shop-wrapper');
                $checks[] = array('wc_get_template_part', 'wc_get_template_part');
                break;
            case 'woocommerce/content-product.php':
                $checks[] = array('shop-product-card', 'shop-product-card');
                $checks[] = array('shop-product-content', 'shop-product-content');
                $checks[] = array('product->get_name()', 'product->get_name()');
                break;
            case 'woocommerce/single-product.php':
                $checks[] = array('container', 'container');
                $checks[] = array('breadcrumbs', 'breadcrumbs');
                break;
            case 'woocommerce/content-single-product.php':
                $checks[] = array('single-product-page', 'single-product-page');
                break;
            case 'header.php':
                $checks[] = array('wishlist-count', 'wishlist-count');
                break;
            case 'inc/woocommerce.php':
                $checks[] = array('asker_output_related_products', 'asker_output_related_products');
                break;
            case 'assets/css/main.css':
                $checks[] = array('shop-product-card', 'shop-product-card');
                break;
        }
        
        foreach ($checks as $check) {
            if (strpos($file_content, $check[0]) !== false) {
                echo "  ✓ Содержит: '{$check[0]}'\n";
            } else {
                echo "  ⚠ НЕ содержит: '{$check[0]}'!\n";
            }
        }
        
        echo "\n";
    } else {
        echo "✗ {$file} - ФАЙЛ НЕ НАЙДЕН!\n\n";
    }
}

// Проверяем версию WooCommerce
if (class_exists('WooCommerce')) {
    echo "\n=== ИНФОРМАЦИЯ О WOOCOMMERCE ===\n";
    echo "Версия WooCommerce: " . WC()->version . "\n";
    echo "Версия WordPress: " . get_bloginfo('version') . "\n";
    echo "Активная тема: " . get_stylesheet() . "\n";
}

// Проверяем кэш
echo "\n=== ИНФОРМАЦИЯ О КЭШЕ ===\n";
if (function_exists('opcache_get_status')) {
    $opcache_status = opcache_get_status();
    if ($opcache_status && isset($opcache_status['opcache_enabled']) && $opcache_status['opcache_enabled']) {
        echo "OPcache: ВКЛЮЧЕН\n";
        echo "Кэшированных скриптов: " . $opcache_status['opcache_statistics']['num_cached_scripts'] . "\n";
    } else {
        echo "OPcache: ВЫКЛЮЧЕН\n";
    }
} else {
    echo "OPcache: НЕ ДОСТУПЕН\n";
}

echo "\n✅ Проверка завершена!\n";
echo '</pre>';

echo '<p><strong>ВАЖНО:</strong> Удалите этот файл после использования!</p>';
echo '<p><a href="' . home_url('/product-category/anods/') . '">Перейти на страницу категории</a></p>';
echo '<p><a href="' . admin_url() . '">Вернуться в админку</a></p>';

