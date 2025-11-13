<?php
/**
 * Скрипт для детальной проверки content-product.php
 * ВАЖНО: Удалите этот файл после использования!
 *
 * Использование: откройте в браузере http://askerspb.beget.tech/wp-content/themes/asker/check-content-product.php
 */

// Загружаем WordPress
require_once('../../../wp-load.php');

// Проверяем права доступа
if (!current_user_can('manage_options')) {
    die('Доступ запрещен. Только администраторы.');
}

echo '<h1>Детальная проверка content-product.php</h1>';
echo '<pre>';

$template_dir = get_template_directory();
$file_path = $template_dir . '/woocommerce/content-product.php';

if (!file_exists($file_path)) {
    echo "✗ Файл не найден!\n";
    die();
}

$file_content = file_get_contents($file_path);
$file_size = filesize($file_path);
$file_time = filemtime($file_path);

echo "=== ИНФОРМАЦИЯ О ФАЙЛЕ ===\n";
echo "Размер: {$file_size} байт\n";
echo "Время модификации: " . date('Y-m-d H:i:s', $file_time) . "\n";
echo "Путь: {$file_path}\n\n";

echo "=== ПРОВЕРКА СОДЕРЖИМОГО ===\n";

// Проверка на старую кнопку "Подробнее"
$has_old_button = false;
$checks = array();

// Проверка 1: Наличие shop-btn-details
if (strpos($file_content, 'shop-btn-details') !== false) {
    echo "✗ СОДЕРЖИТ 'shop-btn-details' - старая кнопка найдена!\n";
    $has_old_button = true;
    $checks[] = 'shop-btn-details';
} else {
    echo "✓ НЕ содержит 'shop-btn-details'\n";
}

// Проверка 2: Наличие текста "Подробнее" в контексте кнопки
if (preg_match('/Подробнее.*?shop-btn-details|shop-btn-details.*?Подробнее/', $file_content)) {
    echo "✗ СОДЕРЖИТ кнопку 'Подробнее' - старая версия!\n";
    $has_old_button = true;
    $checks[] = 'Подробнее в кнопке';
} else {
    echo "✓ НЕ содержит кнопку 'Подробнее'\n";
}

// Проверка 3: Правильная структура
echo "\n=== ПРОВЕРКА ПРАВИЛЬНОЙ СТРУКТУРЫ ===\n";
$required_elements = array(
    'shop-product-card' => 'Основной контейнер карточки',
    'shop-product-content' => 'Контейнер контента',
    'shop-product-bottom' => 'Контейнер цены и кнопок',
    'shop-product-price' => 'Цена товара',
    'shop-product-actions' => 'Контейнер кнопок',
    'add_to_cart_button' => 'Кнопка "В корзину"',
    'product->get_name()' => 'Название товара',
);

foreach ($required_elements as $element => $description) {
    if (strpos($file_content, $element) !== false) {
        echo "✓ Содержит: {$element} ({$description})\n";
    } else {
        echo "✗ ОТСУТСТВУЕТ: {$element} ({$description})\n";
    }
}

// Проверка 4: Структура кнопок
echo "\n=== ПРОВЕРКА СТРУКТУРЫ КНОПОК ===\n";
if (strpos($file_content, 'shop-product-actions') !== false) {
    // Извлекаем блок с кнопками
    preg_match('/<div class="shop-product-actions">(.*?)<\/div>/s', $file_content, $matches);
    if (!empty($matches[1])) {
        $buttons_block = $matches[1];
        echo "Блок кнопок найден:\n";
        echo htmlspecialchars(substr($buttons_block, 0, 200)) . "...\n\n";
        
        // Проверяем количество кнопок
        $button_count = substr_count($buttons_block, '<a') + substr_count($buttons_block, '<button');
        echo "Найдено элементов кнопок: {$button_count}\n";
        
        if ($button_count > 1) {
            echo "⚠ ВНИМАНИЕ: Найдено больше одной кнопки - возможно старая версия!\n";
        } else {
            echo "✓ Только одна кнопка (правильно)\n";
        }
        
        // Проверяем наличие "В корзину"
        if (strpos($buttons_block, 'В корзину') !== false) {
            echo "✓ Содержит текст 'В корзину'\n";
        } else {
            echo "✗ НЕ содержит текст 'В корзину'\n";
        }
        
        // Проверяем отсутствие "Подробнее"
        if (strpos($buttons_block, 'Подробнее') !== false) {
            echo "✗ СОДЕРЖИТ текст 'Подробнее' - старая версия!\n";
            $has_old_button = true;
        } else {
            echo "✓ НЕ содержит текст 'Подробнее'\n";
        }
    }
} else {
    echo "✗ Блок shop-product-actions не найден!\n";
}

// Итоговая проверка
echo "\n=== ИТОГОВАЯ ПРОВЕРКА ===\n";
if ($has_old_button) {
    echo "✗ ФАЙЛ СОДЕРЖИТ СТАРУЮ ВЕРСИЮ!\n";
    echo "  Найдены элементы: " . implode(', ', $checks) . "\n";
    echo "\n⚠ НУЖНО ОБНОВИТЬ ФАЙЛ!\n";
} else {
    echo "✓ ФАЙЛ СООТВЕТСТВУЕТ НОВОЙ ВЕРСИИ\n";
}

// Показываем первые 500 символов файла для проверки
echo "\n=== ПРЕВЬЮ ФАЙЛА (первые 500 символов) ===\n";
echo htmlspecialchars(substr($file_content, 0, 500)) . "...\n";

echo "\n✅ Проверка завершена!\n";
echo '</pre>';

echo '<p><strong>ВАЖНО:</strong> Удалите этот файл после использования!</p>';
echo '<p><a href="' . home_url('/shop/') . '">Перейти на страницу каталога</a></p>';
echo '<p><a href="' . admin_url() . '">Вернуться в админку</a></p>';

