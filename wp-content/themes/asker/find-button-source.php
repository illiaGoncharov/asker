<?php
/**
 * НАХОДИМ ОТКУДА БЕРЕТСЯ КНОПКА "ПОДРОБНЕЕ"
 * ВАЖНО: Удалите этот файл после использования!
 */

require_once('../../../wp-load.php');

if (!current_user_can('manage_options')) {
    die('Доступ запрещен.');
}

echo '<h1>Поиск источника кнопки "Подробнее"</h1>';
echo '<pre>';

// Проверяем все хуки, которые могут добавлять кнопки
global $wp_filter;

echo "=== ХУКИ, КОТОРЫЕ МОГУТ ДОБАВЛЯТЬ КНОПКИ ===\n\n";

$relevant_hooks = array(
    'woocommerce_after_shop_loop_item',
    'woocommerce_before_shop_loop_item',
    'woocommerce_shop_loop_item_title',
    'woocommerce_after_shop_loop_item_title',
    'woocommerce_before_shop_loop_item_title',
);

foreach ($relevant_hooks as $hook_name) {
    if (isset($wp_filter[$hook_name])) {
        echo "Хук: {$hook_name}\n";
        $callbacks = $wp_filter[$hook_name]->callbacks;
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
                
                // Если это наша функция, проверяем её код
                if (is_string($function_name) && function_exists($function_name)) {
                    $reflection = new ReflectionFunction($function_name);
                    $code = file_get_contents($reflection->getFileName());
                    $lines = explode("\n", $code);
                    $start_line = $reflection->getStartLine() - 1;
                    $end_line = min($reflection->getEndLine(), $start_line + 20);
                    
                    if (strpos($code, 'shop-btn-details') !== false || strpos($code, 'Подробнее') !== false) {
                        echo "    ⚠ ЭТА ФУНКЦИЯ СОДЕРЖИТ 'shop-btn-details' или 'Подробнее'!\n";
                        echo "    Файл: " . $reflection->getFileName() . "\n";
                        echo "    Строки " . ($start_line + 1) . "-" . ($end_line + 1) . ":\n";
                        for ($i = $start_line; $i < $end_line; $i++) {
                            if (isset($lines[$i])) {
                                echo "      " . ($i + 1) . ": " . htmlspecialchars($lines[$i]) . "\n";
                            }
                        }
                    }
                }
            }
        }
        echo "\n";
    }
}

// Проверяем фильтры шаблонов
echo "=== ФИЛЬТРЫ ШАБЛОНОВ ===\n";
if (isset($wp_filter['woocommerce_locate_template'])) {
    $callbacks = $wp_filter['woocommerce_locate_template']->callbacks;
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
            echo "Приоритет {$priority}: {$function_name}\n";
        }
    }
}

// Проверяем, может быть используется другой файл
echo "\n=== ПРОВЕРКА ВСЕХ ФАЙЛОВ С 'shop-btn-details' ===\n";
$template_dir = get_template_directory();
$iterator = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($template_dir),
    RecursiveIteratorIterator::LEAVES_ONLY
);

$found_files = array();
foreach ($iterator as $file) {
    if ($file->isFile() && $file->getExtension() === 'php') {
        $file_path = $file->getRealPath();
        $content = file_get_contents($file_path);
        if (strpos($content, 'shop-btn-details') !== false) {
            $found_files[] = $file_path;
            echo "Найден файл: {$file_path}\n";
            
            // Показываем контекст
            $lines = explode("\n", $content);
            foreach ($lines as $line_num => $line) {
                if (strpos($line, 'shop-btn-details') !== false) {
                    $start = max(0, $line_num - 2);
                    $end = min(count($lines) - 1, $line_num + 2);
                    echo "  Строки " . ($start + 1) . "-" . ($end + 1) . ":\n";
                    for ($i = $start; $i <= $end; $i++) {
                        echo "    " . ($i + 1) . ": " . htmlspecialchars($lines[$i]) . "\n";
                    }
                    echo "\n";
                    break;
                }
            }
        }
    }
}

if (empty($found_files)) {
    echo "✓ Файлов с 'shop-btn-details' не найдено в теме\n";
} else {
    echo "⚠ Найдено " . count($found_files) . " файлов с 'shop-btn-details'\n";
}

echo "\n✅ Проверка завершена\n";
echo '</pre>';

echo '<p><strong>ВАЖНО:</strong> Удалите этот файл после использования!</p>';

