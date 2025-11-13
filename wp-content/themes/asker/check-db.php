<?php
/**
 * Скрипт для проверки подключения к базе данных
 * ВАЖНО: Удалите этот файл после использования!
 *
 * Использование: откройте в браузере http://askerspb.beget.tech/wp-content/themes/asker/check-db.php
 */

// Загружаем WordPress
require_once('../../../wp-load.php');

// Проверяем права доступа
if (!current_user_can('manage_options')) {
    die('Доступ запрещен. Только администраторы.');
}

echo '<h1>Проверка подключения к базе данных</h1>';
echo '<pre>';

global $wpdb;

// Проверка 1: Существует ли объект $wpdb
if (!isset($wpdb)) {
    echo "✗ Объект \$wpdb не существует\n";
    echo "\n=== ИНФОРМАЦИЯ О СЕРВЕРЕ ===\n";
    echo "PHP версия: " . phpversion() . "\n";
    echo "Версия WordPress: " . (defined('WP_VERSION') ? WP_VERSION : 'не определена') . "\n";
    die();
}

echo "✓ Объект \$wpdb существует\n\n";

// Проверка 2: Подключение к базе данных
echo "=== ПРОВЕРКА ПОДКЛЮЧЕНИЯ ===\n";
$connection = $wpdb->db_connect();

if ($connection === false) {
    echo "✗ Не удалось подключиться к базе данных\n";
} else {
    echo "✓ Подключение к базе данных успешно\n";
}

// Проверка 3: Информация о базе данных (без паролей)
echo "\n=== ИНФОРМАЦИЯ О БАЗЕ ДАННЫХ ===\n";
echo "Имя базы данных: " . (defined('DB_NAME') ? DB_NAME : 'не определено') . "\n";
echo "Хост базы данных: " . (defined('DB_HOST') ? DB_HOST : 'не определено') . "\n";
echo "Пользователь базы данных: " . (defined('DB_USER') ? DB_USER : 'не определено') . "\n";
echo "Префикс таблиц: " . $wpdb->prefix . "\n";

// Проверка 4: Проверка существования таблиц
echo "\n=== ПРОВЕРКА ТАБЛИЦ ===\n";
$tables = $wpdb->get_results("SHOW TABLES LIKE '{$wpdb->prefix}%'", ARRAY_N);

if (empty($tables)) {
    echo "⚠ Таблицы WordPress не найдены\n";
} else {
    echo "✓ Найдено таблиц: " . count($tables) . "\n";
    echo "Первые 5 таблиц:\n";
    foreach (array_slice($tables, 0, 5) as $table) {
        echo "  - " . $table[0] . "\n";
    }
}

// Проверка 5: Простой запрос
echo "\n=== ТЕСТОВЫЙ ЗАПРОС ===\n";
$result = $wpdb->get_var("SELECT 1");

if ($result === '1') {
    echo "✓ Тестовый запрос выполнен успешно\n";
} else {
    echo "✗ Тестовый запрос не выполнен\n";
}

// Проверка 6: Последняя ошибка
echo "\n=== ПРОВЕРКА ОШИБОК ===\n";
$last_error = $wpdb->last_error;
if (empty($last_error)) {
    echo "✓ Ошибок нет\n";
} else {
    echo "⚠ Последняя ошибка: " . $last_error . "\n";
}

// Проверка 7: Информация о сервере
echo "\n=== ИНФОРМАЦИЯ О СЕРВЕРЕ ===\n";
echo "PHP версия: " . phpversion() . "\n";
echo "Версия MySQL: " . (function_exists('mysqli_get_server_info') ? mysqli_get_server_info($wpdb->dbh) : 'не доступна') . "\n";
echo "Версия WordPress: " . (defined('WP_VERSION') ? WP_VERSION : 'не определена') . "\n";

// Проверка 8: Проверка wp-config.php
echo "\n=== ПРОВЕРКА WP-CONFIG.PHP ===\n";
$wp_config_path = ABSPATH . 'wp-config.php';
if (file_exists($wp_config_path)) {
    echo "✓ Файл wp-config.php существует\n";
    $wp_config_size = filesize($wp_config_path);
    echo "Размер файла: {$wp_config_size} байт\n";
    
    // Проверяем наличие критических констант
    $wp_config_content = file_get_contents($wp_config_path);
    $required_constants = array('DB_NAME', 'DB_USER', 'DB_PASSWORD', 'DB_HOST');
    foreach ($required_constants as $constant) {
        if (strpos($wp_config_content, $constant) !== false) {
            echo "✓ Константа {$constant} найдена\n";
        } else {
            echo "⚠ Константа {$constant} не найдена!\n";
        }
    }
} else {
    echo "✗ Файл wp-config.php не найден по пути: {$wp_config_path}\n";
}

echo "\n✅ Проверка завершена!\n";
echo '</pre>';

echo '<p><strong>ВАЖНО:</strong> Удалите этот файл после использования!</p>';
echo '<p><a href="' . admin_url() . '">Вернуться в админку</a></p>';

