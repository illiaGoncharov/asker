<?php
/**
 * Страница благодарности - точная копия макета
 */

get_header();

// Получаем ID заказа из URL
$order_id = isset($_GET['order']) ? intval($_GET['order']) : 0;
$order = null;

if ($order_id) {
    $order = wc_get_order($order_id);
}

// Получаем данные менеджера
$manager_name = '';
$manager_email = '';
$manager_phone = '';
$user_email = '';
$user_phone = '';

if ( $order ) {
    // Email и телефон из заказа
    $user_email = $order->get_billing_email();
    $user_phone = $order->get_billing_phone();
    
    // Получаем менеджера пользователя
    $user_id = $order->get_user_id();
    if ( $user_id ) {
        $manager_id = get_user_meta( $user_id, 'assigned_manager_id', true );
        if ( $manager_id ) {
            $manager_name = get_the_title( $manager_id );
            $manager_email = get_field( 'manager_email', $manager_id );
            $manager_phone = get_field( 'manager_phone', $manager_id );
        }
    }
}

// Fallback значения если менеджер не назначен
if ( empty( $manager_name ) ) {
    $manager_name = 'Менеджер Asker';
}
if ( empty( $manager_email ) ) {
    $manager_email = get_option( 'admin_email' );
}
if ( empty( $manager_phone ) ) {
    $manager_phone = '+7 (812) 123-12-23'; // Общий телефон компании
}
?>

<div class="thankyou-page">
    <div class="container">
        
        <!-- Основная карточка подтверждения -->
        <div class="thankyou__card">
            
            <!-- Иконка успеха и заголовок -->
            <div class="thankyou__header">
                <div class="thankyou__success-icon">
                    <svg width="48" height="48" viewBox="0 0 24 24" fill="none">
                        <circle cx="12" cy="12" r="12" fill="#4CAF50"/>
                        <path d="M8 12L11 15L16 9" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </div>
                <h1 class="thankyou__title">Заказ успешно оформлен!</h1>
                <p class="thankyou__subtitle">Спасибо за ваш заказ. Мы свяжемся с вами в ближайшее время.</p>
            </div>
            
            <!-- Основной контент в две колонки -->
            <div class="thankyou__content">
                
                <!-- Левая колонка - детали заказа -->
                <div class="thankyou__order-details">
                    <h2 class="thankyou__section-title">Детали заказа</h2>
                    
                    <div class="thankyou__detail-row">
                        <span class="thankyou__detail-label">Номер заказа:</span>
                        <span class="thankyou__detail-value">#<?php echo $order ? $order->get_order_number() : ($order_id ? $order_id : '513178'); ?></span>
                    </div>
                    
                    <div class="thankyou__detail-row">
                        <span class="thankyou__detail-label">Дата оформления:</span>
                        <span class="thankyou__detail-value"><?php echo $order ? $order->get_date_created()->date('j F Y \г. в H:i') : date('j F Y \г. в H:i'); ?></span>
                    </div>
                    
                    <div class="thankyou__detail-row">
                        <span class="thankyou__detail-label">Статус:</span>
                        <span class="thankyou__status-badge thankyou__status-badge--pending">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none">
                                <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="2"/>
                                <path d="M12 6v6l4 2" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                            </svg>
                            Заказ на проверке
                        </span>
                    </div>
                    
                    <div class="thankyou__detail-row">
                        <span class="thankyou__detail-label">Способ оплаты:</span>
                        <span class="thankyou__detail-value"><?php echo $order ? $order->get_payment_method_title() : 'По счету'; ?></span>
                    </div>
                    
                    <div class="thankyou__detail-row">
                        <span class="thankyou__detail-label">Сумма к оплате:</span>
                        <span class="thankyou__detail-value thankyou__total-amount"><?php echo $order ? $order->get_formatted_order_total() : '16 800 ₽'; ?></span>
                    </div>
                </div>
                
                <!-- Правая колонка - что дальше -->
                <div class="thankyou__next-steps">
                    <h2 class="thankyou__section-title">Что будет дальше?</h2>
                    
                    <div class="thankyou__step">
                        <div class="thankyou__step-number">1</div>
                        <div class="thankyou__step-content">
                            <h3 class="thankyou__step-title">Заказ создан</h3>
                            <p class="thankyou__step-description">Мы получили Ваш заказ, менеджер проверяет наличие и цены.</p>
                        </div>
                    </div>
                    
                    <div class="thankyou__step">
                        <div class="thankyou__step-number">2</div>
                        <div class="thankyou__step-content">
                            <h3 class="thankyou__step-title">Отправка счета</h3>
                            <p class="thankyou__step-description">Менеджер пришлет счет на оплату на почту, указанную при оформлении заказа.</p>
                        </div>
                    </div>
                    
                    <div class="thankyou__step">
                        <div class="thankyou__step-number">3</div>
                        <div class="thankyou__step-content">
                            <h3 class="thankyou__step-title">Отправка товаров</h3>
                            <p class="thankyou__step-description">После оплаты счета товары по заказу будут отправлены выбранным способом доставки или подготовлены к самовывозу.</p>
                        </div>
                    </div>
                    
                    <div class="thankyou__step">
                        <div class="thankyou__step-number">4</div>
                        <div class="thankyou__step-content">
                            <h3 class="thankyou__step-title">Трекинг</h3>
                            <p class="thankyou__step-description">После отправки в ТК менеджер отправит Вам трек-номер для отслеживания.</p>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Контактная информация -->
            <div class="thankyou__contact-section">
                <h2 class="thankyou__section-title">Контактная информация</h2>
                
                <div class="thankyou__contact-cards">
                    <div class="thankyou__contact-card">
                        <div class="thankyou__contact-avatar">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none">
                                <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="2"/>
                                <path d="M8 14s1.5 2 4 2 4-2 4-2" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                <path d="M9 9h.01" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                <path d="M15 9h.01" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </div>
                        <div class="thankyou__contact-info">
                            <div class="thankyou__contact-label">Ваш менеджер</div>
                            <div class="thankyou__contact-value"><?php echo esc_html( $manager_name ); ?></div>
                        </div>
                    </div>
                    
                    <div class="thankyou__contact-card">
                        <div class="thankyou__contact-icon thankyou__contact-icon--email">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none">
                                <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                <polyline points="22,6 12,13 2,6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </div>
                        <div class="thankyou__contact-info">
                            <div class="thankyou__contact-label">Email</div>
                            <div class="thankyou__contact-value">
                                <a href="mailto:<?php echo esc_attr( $manager_email ); ?>"><?php echo esc_html( $manager_email ); ?></a>
                            </div>
                        </div>
                    </div>
                    
                    <div class="thankyou__contact-card">
                        <div class="thankyou__contact-icon thankyou__contact-icon--phone">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none">
                                <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </div>
                        <div class="thankyou__contact-info">
                            <div class="thankyou__contact-label">Телефон</div>
                            <div class="thankyou__contact-value">
                                <a href="tel:<?php echo esc_attr( preg_replace( '/[^0-9+]/', '', $manager_phone ) ); ?>"><?php echo esc_html( $manager_phone ); ?></a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Важная информация -->
            <div class="thankyou__important-info">
                <div class="thankyou__important-icon">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none">
                        <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="2"/>
                        <path d="M12 16v-4" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        <path d="M12 8h.01" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </div>
                <div class="thankyou__important-content">
                    <h3 class="thankyou__important-title">Важная информация</h3>
                    <ul class="thankyou__important-list">
                        <li>Вы можете связаться с Вашим менеджером по контактам, указанным выше.</li>
                        <li>Для уточнения статуса заказа назовите менеджеру номер и дату оформления.</li>
                        <li>Иногда письма могут попадать в папку "Спам", проверьте ее.</li>
                    </ul>
                </div>
            </div>
            
            <!-- Кнопки действий -->
            <div class="thankyou__actions">
                <a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="thankyou__btn thankyou__btn--primary">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none">
                        <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        <polyline points="9,22 9,12 15,12 15,22" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                    Вернуться на главную
                </a>
                <a href="#" class="thankyou__btn thankyou__btn--secondary" onclick="window.print(); return false;">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none">
                        <polyline points="6,9 6,2 18,2 18,9" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        <path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        <rect x="6" y="14" width="12" height="8" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                    Распечатать заказ
                </a>
            </div>
        </div>
        
        <!-- Футер сообщение -->
        <div class="thankyou__footer-message">
            <p>Спасибо, что выбрали наш магазин! Мы ценим ваше доверие.</p>
        </div>
        
        <?php
        // Отладочный блок - только для залогиненных администраторов
        if ( is_user_logged_in() && current_user_can( 'administrator' ) && $order ) :
        ?>
        <div class="thankyou__debug" id="debug-panel">
            <div class="thankyou__debug-header" onclick="document.getElementById('debug-content').classList.toggle('show')">
                <h3>🔧 Отладочная информация (только для админа)</h3>
                <span class="debug-toggle">▼</span>
            </div>
            <div class="thankyou__debug-content" id="debug-content">
                
                <!-- Основная информация о заказе -->
                <div class="debug-section">
                    <h4>Основные данные заказа</h4>
                    <table class="debug-table">
                        <tr><td>Order ID:</td><td><strong><?php echo $order->get_id(); ?></strong></td></tr>
                        <tr><td>Order Number:</td><td><?php echo $order->get_order_number(); ?></td></tr>
                        <tr><td>Status:</td><td><code><?php echo $order->get_status(); ?></code></td></tr>
                        <tr><td>User ID:</td><td><?php echo $order->get_user_id() ?: 'Guest'; ?></td></tr>
                        <tr><td>Date Created:</td><td><?php echo $order->get_date_created() ? $order->get_date_created()->format('Y-m-d H:i:s') : 'N/A'; ?></td></tr>
                        <tr><td>Date Modified:</td><td><?php echo $order->get_date_modified() ? $order->get_date_modified()->format('Y-m-d H:i:s') : 'N/A'; ?></td></tr>
                        <tr><td>Total:</td><td><?php echo $order->get_formatted_order_total(); ?></td></tr>
                        <tr><td>Payment Method:</td><td><?php echo $order->get_payment_method_title(); ?> (<?php echo $order->get_payment_method(); ?>)</td></tr>
                        <tr><td>Customer Note:</td><td><?php echo $order->get_customer_note() ?: '—'; ?></td></tr>
                    </table>
                </div>
                
                <!-- Billing данные -->
                <div class="debug-section">
                    <h4>Billing данные</h4>
                    <table class="debug-table">
                        <tr><td>Name:</td><td><?php echo $order->get_billing_first_name() . ' ' . $order->get_billing_last_name(); ?></td></tr>
                        <tr><td>Company:</td><td><?php echo $order->get_billing_company() ?: '—'; ?></td></tr>
                        <tr><td>Email:</td><td><?php echo $order->get_billing_email(); ?></td></tr>
                        <tr><td>Phone:</td><td><?php echo $order->get_billing_phone(); ?></td></tr>
                        <tr><td>Address:</td><td><?php echo $order->get_billing_address_1() . ' ' . $order->get_billing_address_2(); ?></td></tr>
                        <tr><td>City:</td><td><?php echo $order->get_billing_city(); ?></td></tr>
                        <tr><td>Postcode:</td><td><?php echo $order->get_billing_postcode(); ?></td></tr>
                    </table>
                </div>
                
                <!-- Товары в заказе -->
                <div class="debug-section">
                    <h4>Товары в заказе (<?php echo count( $order->get_items() ); ?>)</h4>
                    <table class="debug-table debug-table--items">
                        <thead>
                            <tr><th>ID</th><th>Название</th><th>SKU</th><th>Кол-во</th><th>Цена</th><th>Сумма</th></tr>
                        </thead>
                        <tbody>
                        <?php foreach ( $order->get_items() as $item_id => $item ) : 
                            $product = $item->get_product();
                        ?>
                            <tr>
                                <td><?php echo $product ? $product->get_id() : 'N/A'; ?></td>
                                <td><?php echo $item->get_name(); ?></td>
                                <td><code><?php echo $product ? $product->get_sku() : '—'; ?></code></td>
                                <td><?php echo $item->get_quantity(); ?></td>
                                <td><?php echo wc_price( $item->get_subtotal() / $item->get_quantity() ); ?></td>
                                <td><?php echo wc_price( $item->get_total() ); ?></td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                
                <!-- Мета-данные заказа -->
                <div class="debug-section">
                    <h4>Мета-данные заказа</h4>
                    <details>
                        <summary>Показать все мета-поля (<?php echo count( $order->get_meta_data() ); ?>)</summary>
                        <pre class="debug-meta"><?php 
                            $meta_data = [];
                            foreach ( $order->get_meta_data() as $meta ) {
                                $meta_data[ $meta->key ] = $meta->value;
                            }
                            echo esc_html( print_r( $meta_data, true ) ); 
                        ?></pre>
                    </details>
                </div>
                
                <!-- Ссылки -->
                <div class="debug-section debug-actions">
                    <a href="<?php echo admin_url( 'post.php?post=' . $order->get_id() . '&action=edit' ); ?>" class="debug-btn" target="_blank">
                        📝 Открыть заказ в админке
                    </a>
                    <a href="<?php echo admin_url( 'admin.php?page=wc-orders&action=edit&id=' . $order->get_id() ); ?>" class="debug-btn" target="_blank">
                        📋 WooCommerce Orders (HPOS)
                    </a>
                    <?php if ( $order->get_user_id() ) : ?>
                    <a href="<?php echo admin_url( 'user-edit.php?user_id=' . $order->get_user_id() ); ?>" class="debug-btn" target="_blank">
                        👤 Профиль пользователя
                    </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

<style>
/* Основные стили страницы */
.thankyou-page {
    background: linear-gradient(135deg, #f8fff8 0%, #ffffff 100%);
    min-height: 100vh;
    padding: 40px 0;
    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
}

.container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 0 20px;
}

/* Основная карточка */
.thankyou__card {
    background: white;
    border-radius: 12px;
    padding: 40px;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
    max-width: 900px;
    margin: 0 auto;
}

/* Заголовок */
.thankyou__header {
    text-align: center;
    margin-bottom: 40px;
}

.thankyou__success-icon {
    margin-bottom: 20px;
}

.thankyou__title {
    font-size: 28px;
    font-weight: 700;
    color: #1a1a1a;
    margin-bottom: 10px;
    line-height: 1.2;
}

.thankyou__subtitle {
    font-size: 16px;
    color: #666666;
    margin: 0;
    line-height: 1.5;
}

/* Основной контент */
.thankyou__content {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 40px;
    margin-bottom: 40px;
}

.thankyou__section-title {
    font-size: 18px;
    font-weight: 600;
    color: #1a1a1a;
    margin-bottom: 20px;
}

/* Детали заказа */
.thankyou__detail-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 12px 0;
    border-bottom: 1px solid #f0f0f0;
}

.thankyou__detail-row:last-child {
    border-bottom: none;
}

.thankyou__detail-label {
    font-weight: 500;
    color: #666666;
    font-size: 14px;
}

.thankyou__detail-value {
    font-weight: 600;
    color: #1a1a1a;
    font-size: 14px;
}

.thankyou__status-badge {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    background: #DBEAFE;
    color: #1E40AF;
    padding: 6px 12px;
    border-radius: 6px;
    font-size: 13px;
    font-weight: 500;
    border: 1px solid #93C5FD;
}

.thankyou__status-badge--pending {
    background: #FEF3C7;
    color: #92400E;
    border-color: #FCD34D;
}

/* Что дальше */
.thankyou__step {
    display: flex;
    align-items: flex-start;
    gap: 12px;
    margin-bottom: 20px;
}

.thankyou__step:last-child {
    margin-bottom: 0;
}

.thankyou__step-number {
    width: 24px;
    height: 24px;
    background: #007bff;
    color: white;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 12px;
    font-weight: 600;
    flex-shrink: 0;
}

.thankyou__step-title {
    font-size: 14px;
    font-weight: 600;
    color: #1a1a1a;
    margin: 0 0 4px 0;
}

.thankyou__step-description {
    font-size: 13px;
    color: #666666;
    margin: 0;
    line-height: 1.4;
}

/* Контактная информация */
.thankyou__contact-section {
    margin-bottom: 30px;
}

.thankyou__contact-cards {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 16px;
}

.thankyou__contact-card {
    background: #f8f9fa;
    border-radius: 8px;
    padding: 16px;
    display: flex;
    align-items: center;
    gap: 12px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
}

.thankyou__contact-avatar {
    width: 40px;
    height: 40px;
    background: #e1bee7;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #7b1fa2;
    flex-shrink: 0;
}

.thankyou__contact-icon {
    width: 40px;
    height: 40px;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

.thankyou__contact-icon--email {
    background: #e3f2fd;
    color: #1976d2;
}

.thankyou__contact-icon--phone {
    background: #e8f5e8;
    color: #388e3c;
}

.thankyou__contact-label {
    font-size: 12px;
    color: #666666;
    margin-bottom: 2px;
}

.thankyou__contact-value {
    font-size: 14px;
    font-weight: 600;
    color: #1a1a1a;
}

/* Важная информация */
.thankyou__important-info {
    background: #fff3cd;
    border-radius: 8px;
    padding: 16px;
    display: flex;
    align-items: flex-start;
    gap: 12px;
    margin-bottom: 30px;
    border: 1px solid #ffeaa7;
}

.thankyou__important-icon {
    width: 24px;
    height: 24px;
    background: #ffc107;
    color: white;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

.thankyou__important-title {
    font-size: 14px;
    font-weight: 600;
    color: #1a1a1a;
    margin: 0 0 8px 0;
}

.thankyou__important-list {
    margin: 0;
    padding-left: 16px;
    color: #666666;
    font-size: 13px;
    line-height: 1.4;
}

.thankyou__important-list li {
    margin-bottom: 4px;
}

/* Кнопки действий */
.thankyou__actions {
    display: flex;
    gap: 16px;
    justify-content: center;
    margin-bottom: 30px;
}

.thankyou__btn {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 12px 24px;
    border-radius: 8px;
    text-decoration: none;
    font-weight: 500;
    font-size: 14px;
    transition: all 0.3s ease;
    border: none;
    cursor: pointer;
}

.thankyou__btn--primary {
    background: #ffeb3b;
    color: #1a1a1a;
}

.thankyou__btn--primary:hover {
    background: #fdd835;
    transform: translateY(-1px);
}

.thankyou__btn--secondary {
    background: white;
    color: #1a1a1a;
    border: 1px solid #ddd;
}

.thankyou__btn--secondary:hover {
    background: #f8f9fa;
    border-color: #bbb;
}

/* Футер сообщение */
.thankyou__footer-message {
    text-align: center;
    margin-top: 20px;
}

.thankyou__footer-message p {
    color: #666666;
    font-size: 14px;
    margin: 0;
}

/* Адаптивность */
@media (max-width: 768px) {
    .thankyou__content {
        grid-template-columns: 1fr;
        gap: 30px;
    }
    
    .thankyou__contact-cards {
        grid-template-columns: 1fr;
    }
    
    .thankyou__actions {
        flex-direction: column;
    }
    
    .thankyou__card {
        padding: 20px;
    }
    
    .thankyou__title {
        font-size: 24px;
    }
}

@media (max-width: 480px) {
    .container {
        padding: 0 15px;
    }
    
    .thankyou__card {
        padding: 16px;
    }
    
    .thankyou__title {
        font-size: 20px;
    }
}

/* ===== ОТЛАДОЧНЫЙ БЛОК (только для админов) ===== */
.thankyou__debug {
    background: #1e1e1e;
    border-radius: 12px;
    margin-top: 40px;
    overflow: hidden;
    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', monospace;
}

.thankyou__debug-header {
    background: #2d2d2d;
    padding: 16px 20px;
    cursor: pointer;
    display: flex;
    justify-content: space-between;
    align-items: center;
    border-bottom: 1px solid #444;
}

.thankyou__debug-header h3 {
    margin: 0;
    color: #ffc107;
    font-size: 14px;
    font-weight: 600;
}

.thankyou__debug-header .debug-toggle {
    color: #888;
    transition: transform 0.3s;
}

.thankyou__debug-content {
    max-height: 0;
    overflow: hidden;
    transition: max-height 0.3s ease;
}

.thankyou__debug-content.show {
    max-height: 2000px;
    padding: 20px;
}

.debug-section {
    margin-bottom: 24px;
}

.debug-section h4 {
    color: #4fc3f7;
    font-size: 13px;
    margin: 0 0 12px 0;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.debug-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 13px;
}

.debug-table td, .debug-table th {
    padding: 8px 12px;
    border-bottom: 1px solid #333;
    color: #ddd;
    text-align: left;
}

.debug-table td:first-child {
    color: #888;
    width: 140px;
}

.debug-table code {
    background: #333;
    padding: 2px 6px;
    border-radius: 3px;
    color: #4caf50;
}

.debug-table--items th {
    background: #2d2d2d;
    color: #888;
    font-weight: 500;
}

.debug-meta {
    background: #252525;
    padding: 12px;
    border-radius: 6px;
    color: #aaa;
    font-size: 11px;
    max-height: 300px;
    overflow: auto;
    margin-top: 8px;
}

.debug-section details summary {
    color: #888;
    cursor: pointer;
    font-size: 12px;
}

.debug-actions {
    display: flex;
    gap: 12px;
    flex-wrap: wrap;
}

.debug-btn {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 10px 16px;
    background: #333;
    color: #fff;
    text-decoration: none;
    border-radius: 6px;
    font-size: 13px;
    transition: background 0.2s;
}

.debug-btn:hover {
    background: #444;
    color: #ffc107;
}

@media print {
    .thankyou__debug {
        display: none;
    }
}
</style>

<?php get_footer(); ?>