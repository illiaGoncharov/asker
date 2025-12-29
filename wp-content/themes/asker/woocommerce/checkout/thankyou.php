<?php
/**
 * Шаблон страницы подтверждения заказа
 *
 * @see     https://docs.woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 8.1.0
 */

defined( 'ABSPATH' ) || exit;

// Получаем заказ несколькими способами
global $wp;
$order_id = 0;

// Способ 1: из query_vars
if ( isset( $wp->query_vars['order-received'] ) ) {
    $order_id = absint( $wp->query_vars['order-received'] );
}

// Способ 2: из GET параметра
if ( ! $order_id && isset( $_GET['order'] ) ) {
    $order_id = absint( $_GET['order'] );
}

// Способ 3: из order-received query var
if ( ! $order_id ) {
    $order_id = absint( get_query_var( 'order-received' ) );
}

// Логируем для отладки
error_log( 'THANKYOU PAGE: order_id = ' . $order_id );
error_log( 'THANKYOU PAGE: REQUEST_URI = ' . $_SERVER['REQUEST_URI'] );

$order = $order_id ? wc_get_order( $order_id ) : false;

if ( ! $order ) {
    error_log( 'THANKYOU PAGE: Order not found, showing message' );
    // Не редиректим, показываем сообщение
    get_header();
    ?>
    <div class="thankyou-page">
        <div class="container">
            <div class="thankyou__card">
                <div class="thankyou__header">
                    <h1 class="thankyou__title">Заказ оформлен</h1>
                    <p class="thankyou__subtitle">Спасибо за ваш заказ! Детали были отправлены на вашу почту.</p>
                </div>
                <div class="thankyou__actions">
                    <a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="thankyou__btn thankyou__btn--primary">Вернуться на главную</a>
                </div>
            </div>
        </div>
    </div>
    <?php
    get_footer();
    exit;
}

// Получаем данные менеджера
$user_id = $order->get_user_id();
$manager_id = $user_id ? get_user_meta( $user_id, 'assigned_manager_id', true ) : null;
$manager_name = '';
$manager_phone = '';
$manager_email = '';

if ( $manager_id ) {
    // Менеджер — это пост (CPT), имя берём из post_title
    $manager_post = get_post( $manager_id );
    if ( $manager_post ) {
        $manager_name = $manager_post->post_title;
        $manager_phone = get_field( 'manager_phone', $manager_id );
        $manager_email = get_field( 'manager_email', $manager_id );
    }
}

// Получаем статус заказа
$order_status = $order->get_status();
$status_labels = array(
    'pending' => 'Заказ на проверке',
    'processing' => 'В обработке',
    'on-hold' => 'На удержании',
    'completed' => 'Завершен',
    'cancelled' => 'Отменен',
    'refunded' => 'Возвращен',
    'failed' => 'Ошибка',
);
// Для только что созданного заказа показываем "Заказ на проверке"
$status_label = 'Заказ на проверке';
if ( $order_status !== 'pending' && isset( $status_labels[ $order_status ] ) ) {
    $status_label = $status_labels[ $order_status ];
}

// Получаем способ оплаты
$payment_method_title = $order->get_payment_method_title();
if ( ! $payment_method_title ) {
    $payment_method_title = 'По счету';
}

get_header();
?>

<!-- Print стили в main.css -->

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
                
                <!-- Правая колонка - что будет дальше -->
                <div class="thankyou__next-steps">
                    <h2 class="thankyou__section-title">Что будет дальше?</h2>
                    
                    <div class="thankyou__steps">
                        <div class="thankyou__step">
                            <div class="thankyou__step-number">1</div>
                            <div class="thankyou__step-content">
                                <h3>Заказ создан</h3>
                                <p>Мы получили Ваш заказ, менеджер проверяет наличие и цены.</p>
                            </div>
                        </div>
                        
                        <div class="thankyou__step">
                            <div class="thankyou__step-number">2</div>
                            <div class="thankyou__step-content">
                                <h3>Отправка счета</h3>
                                <p>Менеджер пришлет счет на оплату на почту, указанную при оформлении заказа.</p>
                            </div>
                        </div>
                        
                        <div class="thankyou__step">
                            <div class="thankyou__step-number">3</div>
                            <div class="thankyou__step-content">
                                <h3>Отправка товаров</h3>
                                <p>После оплаты счета товары по заказу будут отправлены выбранным способом доставки или подготовлены к самовывозу.</p>
                            </div>
                        </div>
                        
                        <div class="thankyou__step">
                            <div class="thankyou__step-number">4</div>
                            <div class="thankyou__step-content">
                                <h3>Трекинг</h3>
                                <p>После отправки в ТК менеджер отправит Вам трек-номер для отслеживания.</p>
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
            
            <!-- Контактная информация (менеджер или контакты по умолчанию) -->
            <div class="thankyou__contact-info">
                <h2 class="thankyou__section-title">Контактная информация</h2>
                
                <?php
                // Контакты по умолчанию (если нет менеджера)
                $default_phone = '+7 (931) 109 94 76';
                $default_email = 'sales@asker-corp.ru';
                
                // Определяем что показывать
                $show_manager = ! empty( $manager_name );
                $contact_phone = $show_manager && $manager_phone ? $manager_phone : $default_phone;
                $contact_email = $show_manager && $manager_email ? $manager_email : $default_email;
                $contact_title = $show_manager ? 'Ваш менеджер' : 'Отдел продаж';
                $contact_name = $show_manager ? $manager_name : 'Asker Parts';
                ?>
                
                <div class="thankyou__contact-cards">
                    <div class="thankyou__contact-card">
                        <div class="thankyou__contact-icon">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none">
                                <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2" stroke="currentColor" stroke-width="2"/>
                                <circle cx="12" cy="7" r="4" stroke="currentColor" stroke-width="2"/>
                            </svg>
                        </div>
                        <div class="thankyou__contact-details">
                            <h3><?php echo esc_html( $contact_title ); ?></h3>
                            <p><?php echo esc_html( $contact_name ); ?></p>
                        </div>
                    </div>
                    
                    <div class="thankyou__contact-card">
                        <div class="thankyou__contact-icon">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none">
                                <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z" stroke="currentColor" stroke-width="2"/>
                            </svg>
                        </div>
                        <div class="thankyou__contact-details">
                            <h3>Телефон</h3>
                            <p><a href="tel:<?php echo esc_attr( preg_replace( '/[^0-9+]/', '', $contact_phone ) ); ?>"><?php echo esc_html( $contact_phone ); ?></a></p>
                        </div>
                    </div>
                    
                    <div class="thankyou__contact-card">
                        <div class="thankyou__contact-icon">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none">
                                <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z" stroke="currentColor" stroke-width="2"/>
                                <polyline points="22,6 12,13 2,6" stroke="currentColor" stroke-width="2"/>
                            </svg>
                        </div>
                        <div class="thankyou__contact-details">
                            <h3>Email</h3>
                            <p><a href="mailto:<?php echo esc_attr( $contact_email ); ?>"><?php echo esc_html( $contact_email ); ?></a></p>
                        </div>
                    </div>
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
                    <li>Вы можете связаться с Вашим менеджером по контактам, указанным выше.</li>
                    <li>Для уточнения статуса заказа назовите менеджеру номер и дату оформления.</li>
                    <li>Иногда письма могут попадать в папку "Спам", проверьте ее.</li>
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
