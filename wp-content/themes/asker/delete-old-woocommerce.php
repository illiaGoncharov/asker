<?php
/**
 * УДАЛЕНИЕ СТАРОГО ФАЙЛА woocommerce.php
 * ВАЖНО: Удалите этот файл после использования!
 */

require_once('../../../wp-load.php');

if (!current_user_can('manage_options')) {
    die('Доступ запрещен.');
}

echo '<h1>Удаление старого файла woocommerce.php</h1>';
echo '<pre>';

$template_dir = get_template_directory();
$old_file = $template_dir . '/woocommerce.php';

echo "=== ПРОВЕРКА ФАЙЛА ===\n";
echo "Путь: {$old_file}\n";
echo "Существует: " . (file_exists($old_file) ? 'ДА' : 'НЕТ') . "\n\n";

if (file_exists($old_file)) {
    // Показываем содержимое файла
    $content = file_get_contents($old_file);
    $size = filesize($old_file);
    
    echo "Размер: {$size} байт\n";
    echo "Содержит 'shop-btn-details': " . (strpos($content, 'shop-btn-details') !== false ? 'ДА' : 'НЕТ') . "\n\n";
    
    // Показываем первые 500 символов
    echo "=== СОДЕРЖИМОЕ ФАЙЛА (первые 500 символов) ===\n";
    echo htmlspecialchars(substr($content, 0, 500)) . "...\n\n";
    
    // Удаляем файл
    echo "=== УДАЛЕНИЕ ФАЙЛА ===\n";
    if (unlink($old_file)) {
        echo "✓ Файл успешно удален!\n";
    } else {
        echo "✗ Не удалось удалить файл. Возможно, нет прав доступа.\n";
        echo "Попробуйте удалить файл вручную через FTP:\n";
        echo "{$old_file}\n";
    }
} else {
    echo "✓ Файл не существует - возможно, уже удален\n";
}

// Очищаем кэш
echo "\n=== ОЧИСТКА КЭША ===\n";
if (function_exists('opcache_reset')) {
    opcache_reset();
    echo "✓ OPcache очищен\n";
}

if (function_exists('wp_cache_flush')) {
    wp_cache_flush();
    echo "✓ WordPress кэш очищен\n";
}

echo "\n✅ Операция завершена!\n";
echo '</pre>';

echo '<p><strong>ВАЖНО:</strong> Удалите этот файл после использования!</p>';
echo '<p><a href="' . home_url('/shop/') . '">Перейти на каталог</a></p>';

