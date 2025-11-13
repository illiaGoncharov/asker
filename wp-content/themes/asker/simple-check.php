<?php
/**
 * ПРОСТАЯ ПРОВЕРКА - какой файл реально используется
 * ВАЖНО: Удалите этот файл после использования!
 */

require_once('../../../wp-load.php');

if (!current_user_can('manage_options')) {
    die('Доступ запрещен.');
}

echo '<h1>Простая проверка</h1>';
echo '<pre>';

// 1. Проверяем путь к файлу
$template_dir = get_template_directory();
$file_path = $template_dir . '/woocommerce/content-product.php';

echo "=== ПУТЬ К ФАЙЛУ ===\n";
echo "get_template_directory(): {$template_dir}\n";
echo "Полный путь: {$file_path}\n";
echo "Файл существует: " . (file_exists($file_path) ? 'ДА' : 'НЕТ') . "\n\n";

if (file_exists($file_path)) {
    // 2. Читаем файл напрямую
    $content = file_get_contents($file_path);
    $size = filesize($file_path);
    $time = filemtime($file_path);
    
    echo "=== ИНФОРМАЦИЯ О ФАЙЛЕ ===\n";
    echo "Размер: {$size} байт\n";
    echo "Время модификации: " . date('Y-m-d H:i:s', $time) . "\n\n";
    
    // 3. Проверяем содержимое
    echo "=== ПРОВЕРКА СОДЕРЖИМОГО ===\n";
    
    if (strpos($content, 'shop-btn-details') !== false) {
        echo "✗ СОДЕРЖИТ 'shop-btn-details' - СТАРАЯ ВЕРСИЯ!\n";
    } else {
        echo "✓ НЕ содержит 'shop-btn-details'\n";
    }
    
    if (strpos($content, 'shop-product-bottom') !== false) {
        echo "✓ Содержит 'shop-product-bottom'\n";
    } else {
        echo "✗ НЕ содержит 'shop-product-bottom'\n";
    }
    
    if (strpos($content, 'add_to_cart_button') !== false) {
        echo "✓ Содержит 'add_to_cart_button'\n";
    } else {
        echo "✗ НЕ содержит 'add_to_cart_button'\n";
    }
    
    // 4. Показываем первые 300 символов
    echo "\n=== ПРЕВЬЮ ФАЙЛА (первые 300 символов) ===\n";
    echo htmlspecialchars(substr($content, 0, 300)) . "...\n";
    
    // 5. Проверяем, что WooCommerce видит этот файл
    echo "\n=== ПРОВЕРКА WOOCOMMERCE ===\n";
    if (function_exists('wc_locate_template')) {
        $wc_template = wc_locate_template('content-product.php');
        echo "WooCommerce находит шаблон: {$wc_template}\n";
        
        if ($wc_template === $file_path) {
            echo "✓ WooCommerce использует правильный файл\n";
        } else {
            echo "⚠ WooCommerce использует ДРУГОЙ файл!\n";
            echo "  Ожидалось: {$file_path}\n";
            echo "  Найдено: {$wc_template}\n";
        }
    }
    
} else {
    echo "✗ ФАЙЛ НЕ НАЙДЕН!\n";
    echo "Проверьте путь к файлу.\n";
}

echo "\n=== ПРОВЕРКА OPcache ===\n";
if (function_exists('opcache_get_status')) {
    $status = opcache_get_status(false);
    if ($status && $status['opcache_enabled']) {
        echo "OPcache: ВКЛЮЧЕН\n";
        
        if (file_exists($file_path)) {
            // Инвалидируем файл
            if (function_exists('opcache_invalidate')) {
                opcache_invalidate($file_path, true);
                echo "✓ Файл инвалидирован в OPcache\n";
            }
        }
    } else {
        echo "OPcache: ВЫКЛЮЧЕН\n";
    }
}

echo "\n✅ Проверка завершена\n";
echo '</pre>';

echo '<p><a href="' . home_url('/shop/') . '">Перейти на каталог</a></p>';

