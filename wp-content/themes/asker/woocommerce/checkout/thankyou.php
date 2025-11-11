<?php
/**
 * Шаблон страницы подтверждения заказа
 *
 * @see     https://docs.woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 8.1.0
 */

defined( 'ABSPATH' ) || exit;

// Получаем заказ
$order_id = get_query_var( 'order-received' );
$order = $order_id ? wc_get_order( $order_id ) : false;

if ( ! $order ) {
    wp_redirect( wc_get_page_permalink( 'shop' ) );
    exit;
}

// Получаем данные менеджера
$user_id = $order->get_user_id();
$manager_id = $user_id ? get_user_meta( $user_id, 'assigned_manager_id', true ) : null;
$manager_name = '';
$manager_phone = '';
$manager_email = '';

if ( $manager_id ) {
    $manager_name = get_field( 'manager_name', $manager_id );
    $manager_phone = get_field( 'manager_phone', $manager_id );
    $manager_email = get_field( 'manager_email', $manager_id );
}

// Получаем статус заказа
$order_status = $order->get_status();
$status_labels = array(
    'pending' => 'Ожидает оплаты',
    'processing' => 'В обработке',
    'on-hold' => 'На удержании',
    'completed' => 'Завершен',
    'cancelled' => 'Отменен',
    'refunded' => 'Возвращен',
    'failed' => 'Ошибка',
);
$status_label = isset( $status_labels[ $order_status ] ) ? $status_labels[ $order_status ] : ucfirst( $order_status );

// Получаем способ оплаты
$payment_method_title = $order->get_payment_method_title();
if ( ! $payment_method_title ) {
    $payment_method_title = 'По счету';
}

get_header();
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
                        <span class="thankyou__detail-value">#<?php echo $order->get_order_number(); ?></span>
                    </div>
                    
                    <div class="thankyou__detail-row">
                        <span class="thankyou__detail-label">Дата оформления:</span>
                        <span class="thankyou__detail-value"><?php echo $order->get_date_created()->date_i18n('j F Y \в H:i'); ?></span>
                    </div>
                    
                    <div class="thankyou__detail-row">
                        <span class="thankyou__detail-label">Статус:</span>
                        <span class="thankyou__status-badge thankyou__status-badge--<?php echo esc_attr( $order_status ); ?>">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none">
                                <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="2"/>
                                <path d="M12 6v6l4 2" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                            </svg>
                            <?php echo esc_html( $status_label ); ?>
                        </span>
                    </div>
                    
                    <div class="thankyou__detail-row">
                        <span class="thankyou__detail-label">Способ оплаты:</span>
                        <span class="thankyou__detail-value"><?php echo esc_html( $payment_method_title ); ?></span>
                    </div>
                    
                    <div class="thankyou__detail-row">
                        <span class="thankyou__detail-label">Сумма заказа:</span>
                        <span class="thankyou__detail-value thankyou__detail-value--total"><?php echo $order->get_formatted_order_total(); ?></span>
                    </div>
                </div>
                
                <!-- Правая колонка - что дальше -->
                <div class="thankyou__next-steps">
                    <h2 class="thankyou__section-title">Что дальше?</h2>
                    
                    <div class="thankyou__steps">
                        <div class="thankyou__step">
                            <div class="thankyou__step-number">1</div>
                            <div class="thankyou__step-content">
                                <h3>Получите счет</h3>
                                <p>Счет будет отправлен на ваш email в течение 30 минут</p>
                            </div>
                        </div>
                        
                        <div class="thankyou__step">
                            <div class="thankyou__step-number">2</div>
                            <div class="thankyou__step-content">
                                <h3>Оплатите счет</h3>
                                <p>У вас есть 3 рабочих дня для оплаты</p>
                            </div>
                        </div>
                        
                        <div class="thankyou__step">
                            <div class="thankyou__step-number">3</div>
                            <div class="thankyou__step-content">
                                <h3>Получите товар</h3>
                                <p>Доставка в течение 2-5 рабочих дней после оплаты</p>
                            </div>
                        </div>
                    </div>
                </div>
                
            </div>
            
            <!-- Список товаров -->
            <div class="thankyou__order-items">
                <h2 class="thankyou__section-title">Состав заказа</h2>
                <div class="thankyou__items-list">
                    <?php
                    foreach ( $order->get_items() as $item_id => $item ) {
                        $product = $item->get_product();
                        if ( ! $product ) {
                            continue;
                        }
                        ?>
                        <div class="thankyou__item">
                            <div class="thankyou__item-image">
                                <?php echo $product->get_image( 'thumbnail' ); ?>
                            </div>
                            <div class="thankyou__item-details">
                                <h3 class="thankyou__item-name"><?php echo esc_html( $item->get_name() ); ?></h3>
                                <div class="thankyou__item-meta">
                                    <span class="thankyou__item-quantity">Количество: <?php echo esc_html( $item->get_quantity() ); ?></span>
                                    <span class="thankyou__item-price"><?php echo $order->get_formatted_line_subtotal( $item ); ?></span>
                                </div>
                            </div>
                        </div>
                        <?php
                    }
                    ?>
                </div>
            </div>
            
            <!-- Контактная информация -->
            <div class="thankyou__contact-info">
                <h2 class="thankyou__section-title">Контактная информация</h2>
                
                <div class="thankyou__contact-cards">
                    <?php if ( $manager_name ) : ?>
                    <div class="thankyou__contact-card">
                        <div class="thankyou__contact-icon">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none">
                                <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="2"/>
                                <path d="M12 2a10 10 0 0 0-10 10c0 1.5.5 3 1.5 4.5L12 22l8.5-5.5c1-1.5 1.5-3 1.5-4.5A10 10 0 0 0 12 2z"/>
                                <circle cx="12" cy="12" r="3" stroke="currentColor" stroke-width="2"/>
                            </svg>
                        </div>
                        <div class="thankyou__contact-details">
                            <h3>Ваш менеджер</h3>
                            <p><?php echo esc_html( $manager_name ); ?></p>
                            <?php if ( $manager_phone ) : ?>
                                <p class="thankyou__contact-meta"><?php echo esc_html( $manager_phone ); ?></p>
                            <?php endif; ?>
                            <?php if ( $manager_email ) : ?>
                                <p class="thankyou__contact-meta">
                                    <a href="mailto:<?php echo esc_attr( $manager_email ); ?>"><?php echo esc_html( $manager_email ); ?></a>
                                </p>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endif; ?>
                    
                    <div class="thankyou__contact-card">
                        <div class="thankyou__contact-icon">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none">
                                <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z" stroke="currentColor" stroke-width="2"/>
                                <polyline points="22,6 12,13 2,6" stroke="currentColor" stroke-width="2"/>
                            </svg>
                        </div>
                        <div class="thankyou__contact-details">
                            <h3>Email</h3>
                            <p><a href="mailto:<?php echo esc_attr( $order->get_billing_email() ); ?>"><?php echo esc_html( $order->get_billing_email() ); ?></a></p>
                        </div>
                    </div>
                    
                    <?php if ( $order->get_billing_phone() ) : ?>
                    <div class="thankyou__contact-card">
                        <div class="thankyou__contact-icon">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none">
                                <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z" stroke="currentColor" stroke-width="2"/>
                            </svg>
                        </div>
                        <div class="thankyou__contact-details">
                            <h3>Телефон</h3>
                            <p><a href="tel:<?php echo esc_attr( $order->get_billing_phone() ); ?>"><?php echo esc_html( $order->get_billing_phone() ); ?></a></p>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Важная информация -->
            <div class="thankyou__important-info">
                <div class="thankyou__important-header">
                    <div class="thankyou__important-icon">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none">
                            <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="2"/>
                            <path d="M12 8v4" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                            <path d="M12 16h.01" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                        </svg>
                    </div>
                    <h2 class="thankyou__section-title">Важная информация</h2>
                </div>
                
                <ul class="thankyou__important-list">
                    <li>Проверьте папку "Спам" если не получили счет в течение часа</li>
                    <li>Сохраните номер заказа для отслеживания статуса</li>
                    <li>При возникновении вопросов обращайтесь в службу поддержки</li>
                </ul>
            </div>
            
            <!-- Кнопки действий -->
            <div class="thankyou__actions">
                <a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="thankyou__btn thankyou__btn--primary">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none">
                        <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z" stroke="currentColor" stroke-width="2"/>
                        <polyline points="9,22 9,12 15,12 15,22" stroke="currentColor" stroke-width="2"/>
                    </svg>
                    Вернуться на главную
                </a>
                
                <?php if ( is_user_logged_in() && wc_get_page_permalink( 'myaccount' ) ) : ?>
                <a href="<?php echo esc_url( wc_get_account_endpoint_url( 'orders' ) ); ?>" class="thankyou__btn thankyou__btn--secondary">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none">
                        <path d="M9 11l3 3L22 4" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        <path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                    Мои заказы
                </a>
                <?php endif; ?>
                
                <button class="thankyou__btn thankyou__btn--secondary" onclick="window.print()">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none">
                        <polyline points="6,9 6,2 18,2 18,9" stroke="currentColor" stroke-width="2"/>
                        <path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2" stroke="currentColor" stroke-width="2"/>
                        <rect x="6" y="14" width="12" height="8" stroke="currentColor" stroke-width="2"/>
                    </svg>
                    Распечатать заказ
                </button>
            </div>
            
            <!-- Благодарность внизу -->
            <div class="thankyou__footer-message">
                <p>Спасибо, что выбрали наш магазин! Мы ценим ваше доверие.</p>
            </div>
            
        </div>
        
    </div>
</div>

<?php get_footer(); ?>
