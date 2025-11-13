<?php
/**
 * НАХОДИМ РЕАЛЬНЫЙ ШАБЛОН, КОТОРЫЙ ИСПОЛЬЗУЕТСЯ
 * ВАЖНО: Удалите этот файл после использования!
 */

require_once('../../../wp-load.php');

if (!current_user_can('manage_options')) {
    die('Доступ запрещен.');
}

echo '<h1>Поиск реального шаблона</h1>';
echo '<pre>';

// 1. Проверяем активную тему
echo "=== АКТИВНАЯ ТЕМА ===\n";
$active_theme = get_stylesheet();
$template_dir = get_template_directory();
$stylesheet_dir = get_stylesheet_directory();

echo "Активная тема (stylesheet): {$active_theme}\n";
echo "Путь к теме (template_directory): {$template_dir}\n";
echo "Путь к стилям (stylesheet_directory): {$stylesheet_dir}\n";

if ($template_dir !== $stylesheet_dir) {
    echo "⚠ ВНИМАНИЕ: template_directory и stylesheet_directory РАЗНЫЕ!\n";
    echo "  Это может означать, что используется дочерняя тема\n";
}

// 2. Проверяем все возможные пути к файлу
echo "\n=== ВСЕ ВОЗМОЖНЫЕ ПУТИ К ФАЙЛУ ===\n";
$possible_paths = array(
    '1. get_template_directory()' => $template_dir . '/woocommerce/content-product.php',
    '2. get_stylesheet_directory()' => $stylesheet_dir . '/woocommerce/content-product.php',
    '3. WP_CONTENT_DIR' => WP_CONTENT_DIR . '/themes/' . $active_theme . '/woocommerce/content-product.php',
    '4. WP_CONTENT_DIR (template)' => WP_CONTENT_DIR . '/themes/' . get_template() . '/woocommerce/content-product.php',
);

foreach ($possible_paths as $label => $path) {
    echo "{$label}:\n";
    echo "  Путь: {$path}\n";
    echo "  Существует: " . (file_exists($path) ? 'ДА' : 'НЕТ') . "\n";
    if (file_exists($path)) {
        $size = filesize($path);
        $time = filemtime($path);
        echo "  Размер: {$size} байт\n";
        echo "  Время: " . date('Y-m-d H:i:s', $time) . "\n";
        
        // Проверяем содержимое
        $content = file_get_contents($path);
        if (strpos($content, 'shop-btn-details') !== false) {
            echo "  ⚠ СОДЕРЖИТ 'shop-btn-details' - СТАРАЯ ВЕРСИЯ!\n";
        } else {
            echo "  ✓ НЕ содержит 'shop-btn-details' - новая версия\n";
        }
    }
    echo "\n";
}

// 3. Проверяем, что WooCommerce реально использует
echo "=== ЧТО WOOCOMMERCE РЕАЛЬНО ИСПОЛЬЗУЕТ ===\n";
if (function_exists('wc_locate_template')) {
    $wc_template = wc_locate_template('content-product.php');
    echo "WooCommerce находит: {$wc_template}\n";
    echo "Существует: " . (file_exists($wc_template) ? 'ДА' : 'НЕТ') . "\n";
    
    if (file_exists($wc_template)) {
        $content = file_get_contents($wc_template);
        $size = filesize($wc_template);
        $time = filemtime($wc_template);
        
        echo "Размер: {$size} байт\n";
        echo "Время: " . date('Y-m-d H:i:s', $time) . "\n";
        
        if (strpos($content, 'shop-btn-details') !== false) {
            echo "✗ СОДЕРЖИТ 'shop-btn-details' - СТАРАЯ ВЕРСИЯ!\n";
        } else {
            echo "✓ НЕ содержит 'shop-btn-details'\n";
        }
        
        // Показываем первые 200 символов
        echo "\nПервые 200 символов:\n";
        echo htmlspecialchars(substr($content, 0, 200)) . "...\n";
    }
}

// 4. Проверяем фильтры, которые могут переопределять шаблоны
echo "\n=== ФИЛЬТРЫ, ПЕРЕОПРЕДЕЛЯЮЩИЕ ШАБЛОНЫ ===\n";
global $wp_filter;

$template_filters = array(
    'woocommerce_locate_template',
    'template_include',
    'woocommerce_locate_template_part',
);

foreach ($template_filters as $filter_name) {
    if (isset($wp_filter[$filter_name])) {
        echo "Фильтр '{$filter_name}':\n";
        $callbacks = $wp_filter[$filter_name]->callbacks;
        foreach ($callbacks as $priority => $hooks) {
            foreach ($hooks as $hook) {
                $function_name = 'unknown';
                if (is_string($hook['function'])) {
                    $function_name = $hook['function'];
                } elseif (is_array($hook['function'])) {
                    if (is_object($hook['function'][0])) {
                        $function_name = get_class($hook['function'][0]) . '::' . $hook['function'][1];
                    } else {
                        $function_name = $hook['function'][0] . '::' . $hook['function'][1];
                    }
                }
                echo "  Приоритет {$priority}: {$function_name}\n";
            }
        }
    } else {
        echo "Фильтр '{$filter_name}': не найден\n";
    }
}

// 5. Проверяем активные плагины
echo "\n=== АКТИВНЫЕ ПЛАГИНЫ ===\n";
$active_plugins = get_option('active_plugins');
echo "Всего активных плагинов: " . count($active_plugins) . "\n";
foreach ($active_plugins as $plugin) {
    echo "  - {$plugin}\n";
}

// 6. Проверяем, есть ли дочерняя тема
echo "\n=== ПРОВЕРКА ДОЧЕРНЕЙ ТЕМЫ ===\n";
$parent_theme = get_template();
$child_theme = get_stylesheet();

if ($parent_theme !== $child_theme) {
    echo "⚠ ИСПОЛЬЗУЕТСЯ ДОЧЕРНЯЯ ТЕМА!\n";
    echo "  Родительская тема: {$parent_theme}\n";
    echo "  Дочерняя тема: {$child_theme}\n";
    echo "  Проверьте файлы в дочерней теме!\n";
} else {
    echo "✓ Дочерняя тема не используется\n";
}

// 7. Проверяем все файлы content-product.php в системе
echo "\n=== ПОИСК ВСЕХ ФАЙЛОВ content-product.php ===\n";
$search_dirs = array(
    WP_CONTENT_DIR . '/themes',
    WP_CONTENT_DIR . '/plugins',
);

foreach ($search_dirs as $dir) {
    if (is_dir($dir)) {
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($dir),
            RecursiveIteratorIterator::LEAVES_ONLY
        );
        
        foreach ($iterator as $file) {
            if ($file->isFile() && $file->getFilename() === 'content-product.php') {
                $file_path = $file->getRealPath();
                $size = filesize($file_path);
                $time = filemtime($file_path);
                
                echo "Найден: {$file_path}\n";
                echo "  Размер: {$size} байт\n";
                echo "  Время: " . date('Y-m-d H:i:s', $time) . "\n";
                
                $content = file_get_contents($file_path);
                if (strpos($content, 'shop-btn-details') !== false) {
                    echo "  ✗ СОДЕРЖИТ 'shop-btn-details' - СТАРАЯ ВЕРСИЯ!\n";
                } else {
                    echo "  ✓ НЕ содержит 'shop-btn-details'\n";
                }
                echo "\n";
            }
        }
    }
}

echo "\n✅ Проверка завершена\n";
echo '</pre>';

echo '<p><strong>ВАЖНО:</strong> Удалите этот файл после использования!</p>';
echo '<p><a href="' . home_url('/shop/') . '">Перейти на каталог</a></p>';

