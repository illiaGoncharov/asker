<?php
/**
 * Страница генерации тестового заказа в WP Admin
 * Позволяет создать тестовый заказ и перейти на страницу thankyou для проверки
 */

// Запрещаем прямой доступ
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Добавляем страницу в меню WooCommerce
 */
add_action( 'admin_menu', 'asker_add_test_order_page' );

function asker_add_test_order_page() {
    add_submenu_page(
        'woocommerce',
        'Тестовый заказ',
        '🧪 Тестовый заказ',
        'manage_options',
        'asker-test-order',
        'asker_test_order_page_content'
    );
}

/**
 * Обработка создания тестового заказа
 */
add_action( 'admin_init', 'asker_handle_test_order_creation' );

function asker_handle_test_order_creation() {
    if ( ! isset( $_POST['asker_create_test_order'] ) ) {
        return;
    }
    
    // Проверяем nonce
    if ( ! wp_verify_nonce( $_POST['_wpnonce'], 'asker_create_test_order' ) ) {
        wp_die( 'Ошибка безопасности' );
    }
    
    // Проверяем права
    if ( ! current_user_can( 'manage_options' ) ) {
        wp_die( 'Недостаточно прав' );
    }
    
    // Создаём тестовый заказ
    $order_id = asker_create_test_order();
    
    if ( $order_id ) {
        // Редирект на страницу thankyou
        $thankyou_url = home_url( '/thankyou/?order=' . $order_id );
        wp_redirect( $thankyou_url );
        exit;
    } else {
        wp_redirect( admin_url( 'admin.php?page=asker-test-order&error=1' ) );
        exit;
    }
}

/**
 * Создание тестового заказа с рандомными товарами
 */
function asker_create_test_order() {
    // Получаем несколько рандомных товаров
    $products = wc_get_products( array(
        'status'  => 'publish',
        'limit'   => 50,
        'orderby' => 'rand',
    ) );
    
    if ( empty( $products ) ) {
        return false;
    }
    
    // Выбираем 1-5 рандомных товаров
    $num_products = min( rand( 1, 5 ), count( $products ) );
    $selected_products = array_slice( $products, 0, $num_products );
    
    // Создаём заказ
    $order = wc_create_order( array(
        'status'      => 'pending',
        'customer_id' => get_current_user_id(),
    ) );
    
    if ( is_wp_error( $order ) ) {
        return false;
    }
    
    // Добавляем товары
    foreach ( $selected_products as $product ) {
        $quantity = rand( 1, 3 );
        $order->add_product( $product, $quantity );
    }
    
    // Устанавливаем billing данные от текущего пользователя
    $current_user = wp_get_current_user();
    
    $order->set_billing_first_name( $current_user->first_name ?: 'Тестовый' );
    $order->set_billing_last_name( $current_user->last_name ?: 'Заказ' );
    $order->set_billing_email( $current_user->user_email ?: 'test@example.com' );
    $order->set_billing_phone( get_user_meta( get_current_user_id(), 'billing_phone', true ) ?: '+7 (999) 123-45-67' );
    $order->set_billing_company( get_user_meta( get_current_user_id(), 'billing_company', true ) ?: 'Тестовая компания ООО' );
    $order->set_billing_address_1( 'ул. Тестовая, д. 1' );
    $order->set_billing_city( 'Санкт-Петербург' );
    $order->set_billing_postcode( '190000' );
    $order->set_billing_country( 'RU' );
    
    // Устанавливаем способ оплаты
    $order->set_payment_method( 'bacs' );
    $order->set_payment_method_title( 'По счету (тест)' );
    
    // Добавляем заметку
    $order->add_order_note( 'Тестовый заказ создан через админку для проверки страницы thankyou', false, true );
    
    // Пересчитываем итоги
    $order->calculate_totals();
    
    // Сохраняем
    $order->save();
    
    return $order->get_id();
}

/**
 * Контент страницы
 */
function asker_test_order_page_content() {
    // Получаем последние тестовые заказы
    $recent_orders = wc_get_orders( array(
        'limit'    => 5,
        'orderby'  => 'date',
        'order'    => 'DESC',
        'customer' => get_current_user_id(),
    ) );
    ?>
    <div class="wrap">
        <h1>🧪 Создание тестового заказа</h1>
        
        <?php if ( isset( $_GET['error'] ) ) : ?>
            <div class="notice notice-error">
                <p>Ошибка при создании заказа. Проверьте, есть ли товары в каталоге.</p>
            </div>
        <?php endif; ?>
        
        <div class="asker-test-order-card">
            <h2>Создать новый тестовый заказ</h2>
            <p>Эта функция создаст заказ с рандомными товарами из каталога и перенаправит на страницу <code>/thankyou</code> для проверки отображения.</p>
            
            <form method="post" action="">
                <?php wp_nonce_field( 'asker_create_test_order' ); ?>
                <p>
                    <button type="submit" name="asker_create_test_order" value="1" class="button button-primary button-hero">
                        🚀 Создать тестовый заказ
                    </button>
                </p>
            </form>
            
            <div class="asker-test-order-info">
                <h3>Что произойдёт:</h3>
                <ul>
                    <li>✅ Создаётся заказ со статусом "Pending"</li>
                    <li>✅ Добавляются 1-5 рандомных товаров</li>
                    <li>✅ Заполняются billing-данные от текущего пользователя</li>
                    <li>✅ Редирект на <code>/thankyou?order=ORDER_ID</code></li>
                </ul>
            </div>
        </div>
        
        <?php if ( ! empty( $recent_orders ) ) : ?>
        <div class="asker-test-order-card">
            <h2>Последние заказы</h2>
            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Дата</th>
                        <th>Статус</th>
                        <th>Сумма</th>
                        <th>Действия</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ( $recent_orders as $order ) : ?>
                    <tr>
                        <td><strong>#<?php echo $order->get_id(); ?></strong></td>
                        <td><?php echo $order->get_date_created() ? $order->get_date_created()->date( 'd.m.Y H:i' ) : '—'; ?></td>
                        <td><mark class="order-status status-<?php echo $order->get_status(); ?>"><?php echo wc_get_order_status_name( $order->get_status() ); ?></mark></td>
                        <td><?php echo $order->get_formatted_order_total(); ?></td>
                        <td>
                            <a href="<?php echo home_url( '/thankyou/?order=' . $order->get_id() ); ?>" class="button button-small" target="_blank">
                                👁 Thankyou
                            </a>
                            <a href="<?php echo admin_url( 'post.php?post=' . $order->get_id() . '&action=edit' ); ?>" class="button button-small">
                                ✏️ Редактировать
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </div>
    
    <style>
    .asker-test-order-card {
        background: #fff;
        border: 1px solid #ccd0d4;
        border-radius: 4px;
        padding: 20px 24px;
        margin: 20px 0;
        max-width: 800px;
    }
    
    .asker-test-order-card h2 {
        margin-top: 0;
        padding-bottom: 12px;
        border-bottom: 1px solid #eee;
    }
    
    .asker-test-order-info {
        background: #f8f9fa;
        border-left: 4px solid #2196f3;
        padding: 12px 16px;
        margin-top: 20px;
    }
    
    .asker-test-order-info h3 {
        margin: 0 0 10px 0;
        font-size: 14px;
    }
    
    .asker-test-order-info ul {
        margin: 0;
    }
    
    .asker-test-order-info li {
        margin-bottom: 4px;
    }
    
    .order-status {
        padding: 4px 8px;
        border-radius: 3px;
        font-size: 12px;
    }
    
    .status-pending { background: #f8dda7; }
    .status-processing { background: #c6e1c6; }
    .status-completed { background: #c8d7e1; }
    .status-cancelled { background: #e5e5e5; }
    </style>
    <?php
}


