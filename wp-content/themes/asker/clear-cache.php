<?php
/**
 * Скрипт для очистки кэша WordPress и OPcache
 * ВАЖНО: Удалите этот файл после использования!
 * 
 * Использование: откройте в браузере http://askerspb.beget.tech/wp-content/themes/asker/clear-cache.php
 */

// Защита от прямого доступа (можно убрать для тестирования)
if (!defined('ABSPATH')) {
    // Загружаем WordPress
    require_once('../../../wp-load.php');
}

// Проверяем права доступа (только для администраторов)
if (!current_user_can('manage_options')) {
    die('Доступ запрещен. Только администраторы могут очищать кэш.');
}

echo '<h1>Очистка кэша</h1>';
echo '<pre>';

// 1. Очистка OPcache
if (function_exists('opcache_reset')) {
    opcache_reset();
    echo "✓ OPcache очищен\n";
} else {
    echo "⚠ OPcache не доступен\n";
}

// 2. Очистка кэша WordPress
if (function_exists('wp_cache_flush')) {
    wp_cache_flush();
    echo "✓ WordPress кэш очищен\n";
}

// 3. Очистка кэша плагинов
if (function_exists('w3tc_flush_all')) {
    w3tc_flush_all();
    echo "✓ W3 Total Cache очищен\n";
}

if (function_exists('wp_super_cache_flush')) {
    wp_super_cache_flush();
    echo "✓ WP Super Cache очищен\n";
}

// 4. Очистка кэша WooCommerce
if (function_exists('wc_delete_product_transients')) {
    wc_delete_product_transients();
    echo "✓ WooCommerce кэш очищен\n";
}

// 5. Очистка transients
global $wpdb;
$wpdb->query("DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_%'");
$wpdb->query("DELETE FROM {$wpdb->options} WHERE option_name LIKE '_site_transient_%'");
echo "✓ Transients очищены\n";

echo "\n✅ Кэш полностью очищен!\n";
echo '</pre>';

echo '<p><strong>ВАЖНО:</strong> Удалите этот файл после использования!</p>';
echo '<p><a href="' . home_url() . '">Вернуться на сайт</a></p>';


