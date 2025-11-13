<?php
/**
 * ПРОВЕРКА РЕАЛЬНОГО HTML, КОТОРЫЙ ГЕНЕРИРУЕТСЯ
 * ВАЖНО: Удалите этот файл после использования!
 */

require_once('../../../wp-load.php');

if (!current_user_can('manage_options')) {
    die('Доступ запрещен.');
}

// Симулируем запрос к странице каталога
$_SERVER['REQUEST_URI'] = '/shop/';
$_SERVER['REQUEST_METHOD'] = 'GET';

// Захватываем вывод
ob_start();

// Загружаем шаблон архива товаров
if (function_exists('is_shop')) {
    // Устанавливаем глобальные переменные для симуляции страницы магазина
    global $wp_query, $post;
    
    // Получаем первый товар для теста
    $products = wc_get_products(array('limit' => 1));
    
    if (!empty($products)) {
        $product = $products[0];
        $post = get_post($product->get_id());
        setup_postdata($post);
        
        // Загружаем шаблон content-product
        $template_path = wc_locate_template('content-product.php');
        
        echo "=== РЕАЛЬНЫЙ HTML, КОТОРЫЙ ГЕНЕРИРУЕТСЯ ===\n\n";
        
        if ($template_path && file_exists($template_path)) {
            // Захватываем вывод шаблона
            ob_start();
            
            global $product;
            $product = wc_get_product($product->get_id());
            
            include $template_path;
            
            $html_output = ob_get_clean();
            
            echo "HTML (первые 2000 символов):\n";
            echo htmlspecialchars(substr($html_output, 0, 2000)) . "...\n\n";
            
            // Проверяем содержимое HTML
            echo "=== ПРОВЕРКА HTML ===\n";
            
            if (strpos($html_output, 'shop-btn-details') !== false) {
                echo "✗ HTML СОДЕРЖИТ 'shop-btn-details' - ПРОБЛЕМА В ШАБЛОНЕ!\n";
            } else {
                echo "✓ HTML НЕ содержит 'shop-btn-details'\n";
            }
            
            if (strpos($html_output, 'Подробнее') !== false) {
                // Проверяем контекст
                if (preg_match('/<a[^>]*>.*?Подробнее.*?<\/a>/s', $html_output)) {
                    echo "✗ HTML СОДЕРЖИТ кнопку 'Подробнее' - ПРОБЛЕМА В ШАБЛОНЕ!\n";
                } else {
                    echo "✓ 'Подробнее' найдено, но не в контексте кнопки\n";
                }
            } else {
                echo "✓ HTML НЕ содержит 'Подробнее'\n";
            }
            
            if (strpos($html_output, 'shop-product-bottom') !== false) {
                echo "✓ HTML содержит 'shop-product-bottom'\n";
            } else {
                echo "✗ HTML НЕ содержит 'shop-product-bottom'\n";
            }
            
            if (strpos($html_output, 'add_to_cart_button') !== false) {
                echo "✓ HTML содержит 'add_to_cart_button'\n";
            } else {
                echo "✗ HTML НЕ содержит 'add_to_cart_button'\n";
            }
            
            // Подсчитываем кнопки
            $button_count = substr_count($html_output, '<a') + substr_count($html_output, '<button');
            echo "\nКоличество элементов кнопок в HTML: {$button_count}\n";
            
            // Ищем блок shop-product-actions
            if (preg_match('/<div[^>]*class="[^"]*shop-product-actions[^"]*"[^>]*>(.*?)<\/div>/s', $html_output, $matches)) {
                echo "\n=== БЛОК shop-product-actions ===\n";
                echo htmlspecialchars($matches[1]) . "\n";
                
                $actions_count = substr_count($matches[1], '<a') + substr_count($matches[1], '<button');
                echo "\nКоличество кнопок в блоке actions: {$actions_count}\n";
                
                if ($actions_count > 1) {
                    echo "⚠ ВНИМАНИЕ: Больше одной кнопки - возможно старая версия!\n";
                }
            }
            
        } else {
            echo "✗ Шаблон не найден: {$template_path}\n";
        }
        
        wp_reset_postdata();
    } else {
        echo "✗ Товары не найдены для теста\n";
    }
}

ob_end_clean();

echo "\n=== ПРОВЕРКА КЭША ===\n";
echo "Если HTML правильный, но на сайте старая версия:\n";
echo "1. Очистите кэш браузера (Ctrl+Shift+Del)\n";
echo "2. Откройте сайт в режиме инкогнито\n";
echo "3. Проверьте, нет ли CDN или прокси-кэша\n";
echo "4. Проверьте консоль браузера на ошибки\n";

echo "\n✅ Проверка завершена\n";

