<?php
/**
 * ПРИНУДИТЕЛЬНАЯ ПЕРЕЗАГРУЗКА ВСЕХ ФАЙЛОВ
 * ВАЖНО: Удалите этот файл после использования!
 * 
 * Использование: откройте в браузере http://askerspb.beget.tech/wp-content/themes/asker/force-reload.php
 */

// Загружаем WordPress
require_once('../../../wp-load.php');

// Проверяем права доступа
if (!current_user_can('manage_options')) {
    die('Доступ запрещен. Только администраторы.');
}

echo '<h1>ПРИНУДИТЕЛЬНАЯ ПЕРЕЗАГРУЗКА</h1>';
echo '<pre>';

$template_dir = get_template_directory();
echo "Путь к теме: {$template_dir}\n";
echo "Активная тема: " . get_stylesheet() . "\n\n";

// 1. АГРЕССИВНАЯ очистка OPcache
echo "=== ОЧИСТКА OPcache ===\n";
if (function_exists('opcache_reset')) {
    // Инвалидируем ВСЕ файлы темы
    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($template_dir, RecursiveDirectoryIterator::SKIP_DOTS),
        RecursiveIteratorIterator::SELF_FIRST
    );
    
    $invalidated = 0;
    foreach ($iterator as $file) {
        if ($file->isFile() && $file->getExtension() === 'php') {
            if (function_exists('opcache_invalidate')) {
                opcache_invalidate($file->getRealPath(), true);
                $invalidated++;
            }
        }
    }
    
    opcache_reset();
    echo "✓ Инвалидировано {$invalidated} PHP файлов\n";
    echo "✓ OPcache полностью очищен\n\n";
} else {
    echo "⚠ OPcache не доступен\n\n";
}

// 2. Очистка всех кэшей
echo "=== ОЧИСТКА КЭШЕЙ ===\n";
if (function_exists('wp_cache_flush')) {
    wp_cache_flush();
    echo "✓ WordPress кэш очищен\n";
}

global $wpdb;
$deleted = $wpdb->query("DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_%' OR option_name LIKE '_site_transient_%'");
echo "✓ Удалено {$deleted} transients\n";

if (function_exists('wc_delete_product_transients')) {
    wc_delete_product_transients();
    echo "✓ WooCommerce кэш очищен\n";
}

// 3. Принудительная перезагрузка темы
echo "\n=== ПЕРЕЗАГРУЗКА ТЕМЫ ===\n";
if (function_exists('switch_theme')) {
    $current_theme = get_stylesheet();
    switch_theme($current_theme);
    echo "✓ Тема перезагружена: {$current_theme}\n";
}

// 4. Проверка критических файлов с ПРИНУДИТЕЛЬНЫМ обновлением времени модификации
echo "\n=== ПРИНУДИТЕЛЬНОЕ ОБНОВЛЕНИЕ ФАЙЛОВ ===\n";
$critical_files = array(
    'woocommerce/archive-product.php',
    'woocommerce/content-product.php',
    'woocommerce/single-product.php',
    'woocommerce/content-single-product.php',
    'header.php',
    'inc/woocommerce.php',
    'assets/css/main.css',
    'inc/enqueue.php',
);

foreach ($critical_files as $file) {
    $file_path = $template_dir . '/' . $file;
    if (file_exists($file_path)) {
        // Принудительно обновляем время модификации файла
        touch($file_path);
        clearstatcache(true, $file_path);
        
        $file_size = filesize($file_path);
        $file_time = filemtime($file_path);
        $file_content = file_get_contents($file_path);
        
        echo "✓ {$file}\n";
        echo "  Размер: {$file_size} байт\n";
        echo "  Время модификации: " . date('Y-m-d H:i:s', $file_time) . "\n";
        
        // Проверяем содержимое
        $checks = array();
        switch ($file) {
            case 'woocommerce/archive-product.php':
                $checks = array('shop-wrapper', 'wc_get_template_part');
                break;
            case 'woocommerce/content-product.php':
                $checks = array('shop-product-card', 'shop-product-content', 'product->get_name()', 'shop-product-bottom');
                // Проверяем, что НЕТ старой кнопки "Подробнее"
                if (strpos($file_content, 'shop-btn-details') !== false || (strpos($file_content, 'Подробнее') !== false && strpos($file_content, 'shop-product-actions') === false)) {
                    echo "  ⚠ СОДЕРЖИТ СТАРУЮ КНОПКУ 'Подробнее' - файл не обновился!\n";
                    echo "  ⚠ РАЗМЕР ФАЙЛА: {$file_size} байт (ожидается ~2150 байт)\n";
                } else {
                    echo "  ✓ НЕ содержит старую кнопку 'Подробнее' - файл обновлен\n";
                }
                // Проверяем наличие правильной структуры
                if (strpos($file_content, 'shop-product-bottom') === false) {
                    echo "  ⚠ ОТСУТСТВУЕТ 'shop-product-bottom' - файл не обновлен!\n";
                }
                if (strpos($file_content, 'add_to_cart_button') === false) {
                    echo "  ⚠ ОТСУТСТВУЕТ 'add_to_cart_button' - файл не обновлен!\n";
                }
                break;
            case 'woocommerce/single-product.php':
                $checks = array('container', 'breadcrumbs');
                break;
            case 'woocommerce/content-single-product.php':
                $checks = array('single-product-page');
                break;
            case 'header.php':
                $checks = array('wishlist-count');
                break;
            case 'inc/woocommerce.php':
                $checks = array('asker_output_related_products');
                break;
            case 'assets/css/main.css':
                $checks = array('shop-product-card', 'shop-product-content');
                break;
            case 'inc/enqueue.php':
                $checks = array('wp_enqueue_style', 'filemtime');
                break;
        }
        
        foreach ($checks as $check) {
            if (strpos($file_content, $check) !== false) {
                echo "  ✓ Содержит: '{$check}'\n";
            } else {
                echo "  ⚠ НЕ содержит: '{$check}'!\n";
            }
        }
        echo "\n";
    } else {
        echo "✗ {$file} - ФАЙЛ НЕ НАЙДЕН!\n\n";
    }
}

// 5. Проверка, какой шаблон используется для категорий
echo "=== ПРОВЕРКА ШАБЛОНОВ WOOCOMMERCE ===\n";
if (function_exists('wc_get_template')) {
    echo "✓ WooCommerce Template Loader активен\n";
    
    // Проверяем иерархию шаблонов
    $template_hierarchy = array(
        'taxonomy-product_cat.php',
        'woocommerce/taxonomy-product_cat.php',
        'archive-product.php',
        'woocommerce/archive-product.php',
        'archive.php',
        'index.php'
    );
    
    echo "\nПроверка иерархии шаблонов:\n";
    foreach ($template_hierarchy as $template_name) {
        $template_path = $template_dir . '/' . $template_name;
        if (file_exists($template_path)) {
            $mtime = filemtime($template_path);
            echo "  ✓ {$template_name} (изменён: " . date('Y-m-d H:i:s', $mtime) . ")\n";
        } else {
            echo "  ✗ {$template_name} не найден\n";
        }
    }
}

// 6. Принудительная очистка кэша шаблонов WordPress
echo "\n=== ОЧИСТКА КЭША ШАБЛОНОВ ===\n";
if (function_exists('wp_cache_flush_group')) {
    wp_cache_flush_group('themes');
    echo "✓ Кэш тем очищен\n";
}

// Очищаем кэш локализации
if (function_exists('wp_cache_flush_group')) {
    wp_cache_flush_group('l10n');
    echo "✓ Кэш локализации очищен\n";
}

echo "\n✅ ВСЕ ОПЕРАЦИИ ЗАВЕРШЕНЫ!\n";
echo '</pre>';

echo '<p><strong>ВАЖНО:</strong> Удалите этот файл после использования!</p>';
echo '<p><a href="' . home_url('/product-category/anods/') . '">Перейти на страницу категории</a></p>';
echo '<p><a href="' . admin_url() . '">Вернуться в админку</a></p>';

