<?php
/**
 * Тестирование checkout flow (процесса оформления заказа)
 * Откройте: http://askerspb.beget.tech/wp-content/themes/asker/test-checkout-flow.php
 * 
 * ВАЖНО: Удалите после проверки!
 */

require_once('../../../wp-load.php');

header('Content-Type: text/html; charset=utf-8');

// Простая проверка: нужно быть авторизованным
if (!is_user_logged_in()) {
    echo '<h1>Требуется авторизация</h1>';
    echo '<p>Пожалуйста, <a href="' . wp_login_url($_SERVER['REQUEST_URI']) . '">войдите в систему</a></p>';
    exit;
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Тест Checkout Flow</title>
    <style>
        body { font-family: system-ui; padding: 40px; max-width: 1200px; margin: 0 auto; background: #f5f5f5; }
        .container { background: white; padding: 30px; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); }
        h1 { color: #111827; }
        h2 { color: #374151; margin-top: 30px; border-bottom: 2px solid #e5e7eb; padding-bottom: 10px; }
        .success { background: #d1fae5; border-left: 4px solid #10b981; padding: 15px; margin: 15px 0; border-radius: 4px; }
        .error { background: #fee2e2; border-left: 4px solid #ef4444; padding: 15px; margin: 15px 0; border-radius: 4px; }
        .warning { background: #fff3cd; border-left: 4px solid #f59e0b; padding: 15px; margin: 15px 0; border-radius: 4px; }
        .info { background: #e0f2fe; border-left: 4px solid #3b82f6; padding: 15px; margin: 15px 0; border-radius: 4px; }
        code { background: #f3f4f6; padding: 2px 6px; border-radius: 4px; font-size: 13px; }
        table { width: 100%; border-collapse: collapse; margin: 20px 0; }
        th, td { padding: 10px; text-align: left; border: 1px solid #e5e7eb; }
        th { background: #f3f4f6; font-weight: bold; }
        .btn { display: inline-block; background: #3b82f6; color: white; padding: 12px 24px; text-decoration: none; border-radius: 6px; margin: 10px 10px 10px 0; font-weight: 500; border: none; cursor: pointer; }
        .btn:hover { background: #2563eb; }
        .btn-danger { background: #ef4444; }
        .btn-danger:hover { background: #dc2626; }
        .btn-success { background: #10b981; }
        .btn-success:hover { background: #059669; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🧪 Тест Checkout Flow</h1>
        
        <?php
        // Тест 1: Создание тестового заказа
        if (isset($_GET['action']) && $_GET['action'] === 'create_test_order') {
            echo '<h2>1. Создание тестового заказа</h2>';
            
            try {
                // Создаём заказ
                $order = wc_create_order();
                
                // Добавляем тестовый товар (первый найденный)
                $products = wc_get_products(array('limit' => 1));
                if (!empty($products)) {
                    $order->add_product($products[0], 1);
                }
                
                // Устанавливаем данные
                $order->set_customer_id(get_current_user_id());
                $order->set_billing_first_name('Тест');
                $order->set_billing_last_name('Тестов');
                $order->set_billing_email('test@example.com');
                $order->set_billing_phone('+7 (999) 123-45-67');
                $order->set_billing_city('Москва');
                $order->set_billing_address_1('ул. Тестовая, д. 1');
                
                $order->set_payment_method('bacs');
                $order->set_payment_method_title('По счёту');
                
                $order->calculate_totals();
                $order->save();
                
                echo '<div class="success">';
                echo '<p><strong>✓ Заказ создан!</strong></p>';
                echo '<p>ID заказа: <strong>' . $order->get_id() . '</strong></p>';
                echo '<p>Номер заказа: <strong>#' . $order->get_order_number() . '</strong></p>';
                echo '<p>Статус: <code>' . $order->get_status() . '</code></p>';
                echo '<p>Итого: <strong>' . $order->get_formatted_order_total() . '</strong></p>';
                echo '<p><a href="' . $order->get_view_order_url() . '" target="_blank">Посмотреть заказ</a></p>';
                echo '</div>';
                
            } catch (Exception $e) {
                echo '<div class="error">';
                echo '<p><strong>✗ Ошибка создания заказа:</strong></p>';
                echo '<p>' . $e->getMessage() . '</p>';
                echo '</div>';
            }
        }
        
        // Проверка 1: Email настройки
        echo '<h2>1. Email настройки</h2>';
        
        $email_settings = array(
            'woocommerce_email_from_name' => get_option('woocommerce_email_from_name'),
            'woocommerce_email_from_address' => get_option('woocommerce_email_from_address'),
        );
        
        echo '<table>';
        echo '<tr><th>Настройка</th><th>Значение</th></tr>';
        foreach ($email_settings as $key => $value) {
            echo '<tr>';
            echo '<td>' . $key . '</td>';
            echo '<td>' . ($value ? '<code>' . esc_html($value) . '</code>' : '<span class="error">Не установлено</span>') . '</td>';
            echo '</tr>';
        }
        echo '</table>';
        
        // Проверка 2: Последние заказы
        echo '<h2>2. Последние заказы</h2>';
        
        $orders = wc_get_orders(array(
            'limit' => 5,
            'orderby' => 'date',
            'order' => 'DESC',
        ));
        
        if (!empty($orders)) {
            echo '<table>';
            echo '<tr><th>ID</th><th>Номер</th><th>Дата</th><th>Статус</th><th>Клиент</th><th>Сумма</th><th>Действия</th></tr>';
            
            foreach ($orders as $order) {
                echo '<tr>';
                echo '<td>' . $order->get_id() . '</td>';
                echo '<td>#' . $order->get_order_number() . '</td>';
                echo '<td>' . $order->get_date_created()->date('d.m.Y H:i') . '</td>';
                echo '<td><code>' . wc_get_order_status_name($order->get_status()) . '</code></td>';
                echo '<td>' . $order->get_billing_first_name() . ' ' . $order->get_billing_last_name() . '</td>';
                echo '<td>' . $order->get_formatted_order_total() . '</td>';
                echo '<td>';
                echo '<a href="' . admin_url('post.php?post=' . $order->get_id() . '&action=edit') . '" target="_blank">Админ</a> | ';
                echo '<a href="' . $order->get_view_order_url() . '" target="_blank">Клиент</a>';
                echo '</td>';
                echo '</tr>';
            }
            
            echo '</table>';
        } else {
            echo '<div class="warning"><p>Заказов пока нет</p></div>';
        }
        
        // Проверка 3: Заказы текущего пользователя
        echo '<h2>3. Заказы текущего пользователя</h2>';
        
        $user_orders = wc_get_orders(array(
            'customer_id' => get_current_user_id(),
            'limit' => 10,
        ));
        
        if (!empty($user_orders)) {
            echo '<div class="success">';
            echo '<p>✓ Найдено заказов: <strong>' . count($user_orders) . '</strong></p>';
            echo '</div>';
            
            echo '<table>';
            echo '<tr><th>ID</th><th>Номер</th><th>Дата</th><th>Статус</th><th>Сумма</th></tr>';
            
            foreach ($user_orders as $order) {
                echo '<tr>';
                echo '<td>' . $order->get_id() . '</td>';
                echo '<td>#' . $order->get_order_number() . '</td>';
                echo '<td>' . $order->get_date_created()->date('d.m.Y H:i') . '</td>';
                echo '<td><code>' . wc_get_order_status_name($order->get_status()) . '</code></td>';
                echo '<td>' . $order->get_formatted_order_total() . '</td>';
                echo '</tr>';
            }
            
            echo '</table>';
        } else {
            echo '<div class="info"><p>У вас пока нет заказов</p></div>';
        }
        
        // Проверка 4: Email шаблоны
        echo '<h2>4. Email шаблоны WooCommerce</h2>';
        
        $email_templates = array(
            'new_order' => 'Новый заказ (админу)',
            'customer_processing_order' => 'Заказ в обработке (клиенту)',
            'customer_completed_order' => 'Заказ выполнен (клиенту)',
            'customer_invoice' => 'Счёт на оплату (клиенту)',
        );
        
        echo '<table>';
        echo '<tr><th>Шаблон</th><th>Описание</th><th>Статус</th></tr>';
        
        foreach ($email_templates as $template_id => $description) {
            $enabled = get_option('woocommerce_' . $template_id . '_settings');
            $is_enabled = isset($enabled['enabled']) && $enabled['enabled'] === 'yes';
            
            echo '<tr>';
            echo '<td><code>' . $template_id . '</code></td>';
            echo '<td>' . $description . '</td>';
            echo '<td>' . ($is_enabled ? '<span class="success">✓ Включен</span>' : '<span class="error">✗ Отключен</span>') . '</td>';
            echo '</tr>';
        }
        
        echo '</table>';
        
        // Проверка 5: Валидация данных
        echo '<h2>5. Проверка валидации</h2>';
        
        echo '<div class="info">';
        echo '<p><strong>Текущее состояние:</strong></p>';
        echo '<ul>';
        echo '<li>Серверная валидация: <code>inc/form-validation.php</code> ✓</li>';
        echo '<li>Клиентская валидация: <code>assets/js/form-validation.js</code> ✓</li>';
        echo '<li><strong class="error">Проблема:</strong> В <code>asker_create_order_ajax()</code> используются fallback значения</li>';
        echo '</ul>';
        echo '</div>';
        
        echo '<div class="warning">';
        echo '<p><strong>⚠️ Найдены fallback значения:</strong></p>';
        echo '<ul>';
        echo '<li>Имя: "Админ" / "Гость"</li>';
        echo '<li>Email: "guest@example.com"</li>';
        echo '<li>Телефон: "+7 (999) 123-45-67"</li>';
        echo '<li>Город: "Москва"</li>';
        echo '<li>Адрес: "ул. Тестовая, д. 1"</li>';
        echo '</ul>';
        echo '<p><strong>Решение:</strong> Отклонять создание заказа, если данные не заполнены.</p>';
        echo '</div>';
        
        // Действия
        echo '<h2>6. Действия</h2>';
        
        echo '<form method="get" action="" style="display: inline;">';
        echo '<input type="hidden" name="action" value="create_test_order">';
        echo '<button type="submit" class="btn btn-success">🧪 Создать тестовый заказ</button>';
        echo '</form>';
        
        echo '<a href="' . admin_url('admin.php?page=wc-settings&tab=email') . '" class="btn" target="_blank">⚙️ Настройки Email</a>';
        echo '<a href="' . admin_url('edit.php?post_type=shop_order') . '" class="btn" target="_blank">📦 Все заказы</a>';
        echo '<a href="' . home_url('/my-account/') . '" class="btn" target="_blank">👤 Личный кабинет</a>';
        
        ?>
        
        <hr style="margin: 30px 0;">
        <p class="error"><strong>ВАЖНО:</strong> Удалите этот файл после проверки!</p>
    </div>
</body>
</html>

