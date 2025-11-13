<?php
/**
 * Диагностика проблем с обновлением файлов через FTP
 * ВАЖНО: Удалите этот файл после использования!
 *
 * Использование: откройте в браузере http://askerspb.beget.tech/wp-content/themes/asker/debug-ftp-upload.php
 */

// Загружаем WordPress
require_once('../../../wp-load.php');

// Проверяем права доступа
if (!current_user_can('manage_options')) {
    die('Доступ запрещен. Только администраторы.');
}

echo '<h1>Диагностика проблем с обновлением файлов</h1>';
echo '<pre>';

$template_dir = get_template_directory();

echo "=== ИНФОРМАЦИЯ О СЕРВЕРЕ ===\n";
echo "Путь к теме: {$template_dir}\n";
echo "Активная тема: " . get_stylesheet() . "\n";
echo "PHP версия: " . phpversion() . "\n";
echo "Время сервера: " . date('Y-m-d H:i:s') . "\n\n";

// Проверяем критический файл
$critical_file = 'woocommerce/content-product.php';
$file_path = $template_dir . '/' . $critical_file;

echo "=== ПРОВЕРКА ФАЙЛА: {$critical_file} ===\n";

if (!file_exists($file_path)) {
    echo "✗ ФАЙЛ НЕ СУЩЕСТВУЕТ!\n";
    echo "Путь: {$file_path}\n";
    die();
}

$file_size = filesize($file_path);
$file_time = filemtime($file_path);
$file_content = file_get_contents($file_path);
$file_perms = substr(sprintf('%o', fileperms($file_path)), -4);

echo "✓ Файл существует\n";
echo "Путь: {$file_path}\n";
echo "Размер: {$file_size} байт\n";
echo "Время модификации: " . date('Y-m-d H:i:s', $file_time) . "\n";
echo "Права доступа: {$file_perms}\n";
echo "Владелец: " . fileowner($file_path) . " (UID)\n";
echo "Группа: " . filegroup($file_path) . " (GID)\n";

// Проверяем, можем ли мы записать в файл
echo "\n=== ПРОВЕРКА ПРАВ НА ЗАПИСЬ ===\n";
if (is_writable($file_path)) {
    echo "✓ Файл доступен для записи\n";
} else {
    echo "✗ ФАЙЛ НЕ ДОСТУПЕН ДЛЯ ЗАПИСИ!\n";
    echo "⚠ Это может быть причиной проблемы!\n";
}

// Проверяем права на директорию
$dir_path = dirname($file_path);
echo "\n=== ПРОВЕРКА ПРАВ НА ДИРЕКТОРИЮ ===\n";
echo "Директория: {$dir_path}\n";
if (is_writable($dir_path)) {
    echo "✓ Директория доступна для записи\n";
} else {
    echo "✗ ДИРЕКТОРИЯ НЕ ДОСТУПНА ДЛЯ ЗАПИСИ!\n";
    echo "⚠ Это может быть причиной проблемы!\n";
}

// Проверяем содержимое файла
echo "\n=== ПРОВЕРКА СОДЕРЖИМОГО ФАЙЛА ===\n";

// Проверка на старую версию
$has_old_button = false;
if (strpos($file_content, 'shop-btn-details') !== false) {
    echo "✗ СОДЕРЖИТ 'shop-btn-details' - СТАРАЯ ВЕРСИЯ!\n";
    $has_old_button = true;
} else {
    echo "✓ НЕ содержит 'shop-btn-details'\n";
}

// Проверка на кнопку "Подробнее"
if (preg_match('/Подробнее.*?shop-btn-details|shop-btn-details.*?Подробнее/', $file_content)) {
    echo "✗ СОДЕРЖИТ кнопку 'Подробнее' - СТАРАЯ ВЕРСИЯ!\n";
    $has_old_button = true;
} else {
    echo "✓ НЕ содержит кнопку 'Подробнее'\n";
}

// Проверка правильной структуры
echo "\n=== ПРОВЕРКА СТРУКТУРЫ ===\n";
$required = array(
    'shop-product-card' => 'Основной контейнер',
    'shop-product-content' => 'Контейнер контента',
    'shop-product-bottom' => 'Контейнер цены и кнопок',
    'add_to_cart_button' => 'Кнопка "В корзину"',
);

foreach ($required as $element => $desc) {
    if (strpos($file_content, $element) !== false) {
        echo "✓ Содержит: {$element}\n";
    } else {
        echo "✗ ОТСУТСТВУЕТ: {$element} ({$desc})\n";
    }
}

// Проверка OPcache
echo "\n=== ПРОВЕРКА OPcache ===\n";
if (function_exists('opcache_get_status')) {
    $opcache_status = opcache_get_status(false);
    if ($opcache_status && isset($opcache_status['opcache_enabled']) && $opcache_status['opcache_enabled']) {
        echo "OPcache: ВКЛЮЧЕН\n";
        
        // Проверяем, закэширован ли файл
        $opcache_info = opcache_get_status(true);
        if (isset($opcache_info['scripts'][$file_path])) {
            $cached_info = $opcache_info['scripts'][$file_path];
            echo "⚠ ФАЙЛ ЗАКЭШИРОВАН В OPcache!\n";
            echo "  Время кэширования: " . date('Y-m-d H:i:s', $cached_info['timestamp']) . "\n";
            echo "  Время модификации файла: " . date('Y-m-d H:i:s', $file_time) . "\n";
            
            if ($cached_info['timestamp'] < $file_time) {
                echo "⚠ КЭШ СТАРШЕ ФАЙЛА - нужна инвалидация!\n";
            }
            
            // Пытаемся инвалидировать
            if (function_exists('opcache_invalidate')) {
                if (opcache_invalidate($file_path, true)) {
                    echo "✓ Файл инвалидирован в OPcache\n";
                } else {
                    echo "✗ Не удалось инвалидировать файл в OPcache\n";
                }
            }
        } else {
            echo "✓ Файл НЕ закэширован в OPcache\n";
        }
    } else {
        echo "OPcache: ВЫКЛЮЧЕН\n";
    }
} else {
    echo "OPcache: НЕ ДОСТУПЕН\n";
}

// Проверка кэша WordPress
echo "\n=== ПРОВЕРКА КЭША WORDPRESS ===\n";
if (function_exists('wp_cache_flush')) {
    wp_cache_flush();
    echo "✓ Кэш WordPress очищен\n";
} else {
    echo "⚠ Функция wp_cache_flush не доступна\n";
}

// Проверка кэша WooCommerce
echo "\n=== ПРОВЕРКА КЭША WOOCOMMERCE ===\n";
if (function_exists('wc_delete_product_transients')) {
    wc_delete_product_transients();
    echo "✓ Кэш WooCommerce очищен\n";
} else {
    echo "⚠ Функция wc_delete_product_transients не доступна\n";
}

// Проверка плагинов кэширования
echo "\n=== ПРОВЕРКА ПЛАГИНОВ КЭШИРОВАНИЯ ===\n";
$cache_plugins = array(
    'W3 Total Cache' => 'w3tc_flush_all',
    'WP Super Cache' => 'wp_super_cache_flush',
    'WP Rocket' => 'rocket_clean_domain',
    'LiteSpeed Cache' => 'litespeed_purge_all',
);

foreach ($cache_plugins as $plugin => $function) {
    if (function_exists($function)) {
        call_user_func($function);
        echo "✓ Кэш {$plugin} очищен\n";
    }
}

// Тест записи в файл
echo "\n=== ТЕСТ ЗАПИСИ В ФАЙЛ ===\n";
$test_marker = "\n<!-- FTP-UPLOAD-TEST: " . time() . " -->\n";
$test_content = $file_content . $test_marker;

if (file_put_contents($file_path, $test_content) !== false) {
    echo "✓ Успешно записано в файл\n";
    
    // Убираем тестовый маркер
    $original_content = str_replace($test_marker, '', file_get_contents($file_path));
    file_put_contents($file_path, $original_content);
    echo "✓ Тестовый маркер удален\n";
    
    // Обновляем время модификации
    touch($file_path);
    clearstatcache(true, $file_path);
    echo "✓ Время модификации обновлено\n";
} else {
    echo "✗ НЕ УДАЛОСЬ ЗАПИСАТЬ В ФАЙЛ!\n";
    echo "⚠ Это основная причина проблемы!\n";
}

// Итоговая проверка
echo "\n=== ИТОГОВАЯ ПРОВЕРКА ===\n";
if ($has_old_button) {
    echo "✗ ФАЙЛ СОДЕРЖИТ СТАРУЮ ВЕРСИЮ!\n";
    echo "\n⚠ РЕКОМЕНДАЦИИ:\n";
    echo "1. Проверьте, что загружаете файл в правильную директорию\n";
    echo "2. Убедитесь, что файл действительно перезаписывается (проверьте размер)\n";
    echo "3. Проверьте права доступа к файлу и директории\n";
    echo "4. Очистите OPcache после загрузки\n";
    echo "5. Очистите кэш браузера и проверьте в режиме инкогнито\n";
} else {
    echo "✓ ФАЙЛ СООТВЕТСТВУЕТ НОВОЙ ВЕРСИИ\n";
    echo "\n⚠ ЕСЛИ СТРАНИЦА ВСЕ ЕЩЕ НЕПРАВИЛЬНАЯ:\n";
    echo "1. Очистите кэш браузера (Ctrl+Shift+Del)\n";
    echo "2. Откройте сайт в режиме инкогнито\n";
    echo "3. Проверьте, нет ли CDN или прокси-кэша\n";
    echo "4. Проверьте консоль браузера на ошибки\n";
}

echo "\n✅ Диагностика завершена!\n";
echo '</pre>';

echo '<p><strong>ВАЖНО:</strong> Удалите этот файл после использования!</p>';
echo '<p><a href="' . home_url('/shop/') . '">Перейти на страницу каталога</a></p>';
echo '<p><a href="' . admin_url() . '">Вернуться в админку</a></p>';

