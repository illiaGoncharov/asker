<?php
/**
 * Шаблон просмотра заказа в личном кабинете
 *
 * @see     https://docs.woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 3.0.0
 */

defined( 'ABSPATH' ) || exit;

// Получаем заказ
if ( ! $order ) {
    wc_print_notice( __( 'Заказ не найден', 'woocommerce' ), 'error' );
    return;
}

// Получаем данные менеджера
$user_id = $order->get_user_id();
$manager_id = $user_id ? get_user_meta( $user_id, 'assigned_manager_id', true ) : null;
$manager_name = '';
$manager_phone = '';
$manager_email = '';
$manager_telegram = '';

if ( $manager_id ) {
    $manager_name = get_field( 'manager_name', $manager_id );
    if ( ! $manager_name ) {
        $manager_name = get_the_title( $manager_id );
    }
    $manager_phone = get_field( 'manager_phone', $manager_id );
    $manager_email = get_field( 'manager_email', $manager_id );
    $manager_telegram = get_field( 'manager_telegram', $manager_id );
}

// Получаем статус заказа
$order_status = $order->get_status();
$status_labels = array(
    'pending'    => 'Заказ на проверке',
    'processing' => 'В обработке',
    'on-hold'    => 'На удержании',
    'completed'  => 'Завершен',
    'cancelled'  => 'Отменен',
    'refunded'   => 'Возвращен',
    'failed'     => 'Ошибка',
);
$status_label = isset( $status_labels[ $order_status ] ) ? $status_labels[ $order_status ] : ucfirst( $order_status );

// Способ оплаты
$payment_method_title = $order->get_payment_method_title();
if ( ! $payment_method_title ) {
    $payment_method_title = 'По счету';
}

// Получаем данные доставки
$shipping_method = '';
$shipping_items = $order->get_shipping_methods();
if ( ! empty( $shipping_items ) ) {
    foreach ( $shipping_items as $shipping_item ) {
        $shipping_method = $shipping_item->get_method_title();
        break;
    }
}

// Получаем транспортную компанию из мета заказа
$shipping_company = $order->get_meta( '_shipping_company' );
$shipping_companies = array(
    'cdek'    => 'СДЭК',
    'pek'     => 'ПЭК',
    'dellin'  => 'Деловые линии',
    'yandex'  => 'Яндекс доставка',
);
$shipping_company_name = isset( $shipping_companies[ $shipping_company ] ) ? $shipping_companies[ $shipping_company ] : $shipping_company;

// Трек-номер (если есть)
$tracking_number = $order->get_meta( '_tracking_number' );
?>

<div class="view-order-page">
    
    <!-- Заголовок -->
    <div class="view-order__header">
        <a href="<?php echo esc_url( wc_get_account_endpoint_url( 'orders' ) ); ?>" class="view-order__back-link">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none">
                <path d="M19 12H5M12 19l-7-7 7-7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
            Назад к заказам
        </a>
        <h1 class="view-order__title">Заказ #<?php echo $order->get_order_number(); ?></h1>
    </div>
    
    <!-- Основной контент -->
    <div class="view-order__content">
        
        <!-- Левая колонка - детали заказа -->
        <div class="view-order__details">
            <div class="view-order__card">
                <h2 class="view-order__section-title">Информация о заказе</h2>
                
                <div class="view-order__info-grid">
                    <div class="view-order__info-item">
                        <span class="view-order__label">Дата оформления</span>
                        <span class="view-order__value"><?php echo $order->get_date_created()->date_i18n( 'j F Y, H:i' ); ?></span>
                    </div>
                    
                    <div class="view-order__info-item">
                        <span class="view-order__label">Статус</span>
                        <span class="view-order__status view-order__status--<?php echo esc_attr( $order_status ); ?>">
                            <?php echo esc_html( $status_label ); ?>
                        </span>
                    </div>
                    
                    <div class="view-order__info-item">
                        <span class="view-order__label">Способ оплаты</span>
                        <span class="view-order__value"><?php echo esc_html( $payment_method_title ); ?></span>
                    </div>
                    
                    <?php if ( $shipping_method || $shipping_company_name ) : ?>
                    <div class="view-order__info-item">
                        <span class="view-order__label">Доставка</span>
                        <span class="view-order__value">
                            <?php 
                            if ( $shipping_method ) {
                                echo esc_html( $shipping_method );
                            }
                            if ( $shipping_company_name ) {
                                echo ( $shipping_method ? ' — ' : '' ) . esc_html( $shipping_company_name );
                            }
                            ?>
                        </span>
                    </div>
                    <?php endif; ?>
                    
                    <?php if ( $tracking_number ) : ?>
                    <div class="view-order__info-item view-order__info-item--full">
                        <span class="view-order__label">Трек-номер</span>
                        <span class="view-order__value view-order__tracking">
                            <code><?php echo esc_html( $tracking_number ); ?></code>
                            <button type="button" class="view-order__copy-btn" onclick="navigator.clipboard.writeText('<?php echo esc_js( $tracking_number ); ?>'); this.textContent='Скопировано!'">
                                Копировать
                            </button>
                        </span>
                    </div>
                    <?php endif; ?>
                </div>
                
                <!-- Сумма заказа -->
                <div class="view-order__total">
                    <span class="view-order__total-label">Сумма заказа:</span>
                    <span class="view-order__total-value"><?php echo $order->get_formatted_order_total(); ?></span>
                </div>
            </div>
            
            <!-- Состав заказа -->
            <div class="view-order__card">
                <h2 class="view-order__section-title">Состав заказа</h2>
                
                <div class="view-order__items">
                    <?php
                    foreach ( $order->get_items() as $item_id => $item ) {
                        $product = $item->get_product();
                        ?>
                        <div class="view-order__item">
                            <div class="view-order__item-image">
                                <?php 
                                if ( $product ) {
                                    echo $product->get_image( 'thumbnail' );
                                } else {
                                    echo '<div class="view-order__item-placeholder"></div>';
                                }
                                ?>
                            </div>
                            <div class="view-order__item-info">
                                <h3 class="view-order__item-name">
                                    <?php 
                                    if ( $product && $product->get_permalink() ) {
                                        echo '<a href="' . esc_url( $product->get_permalink() ) . '">' . esc_html( $item->get_name() ) . '</a>';
                                    } else {
                                        echo esc_html( $item->get_name() );
                                    }
                                    ?>
                                </h3>
                                <?php if ( $product && $product->get_sku() ) : ?>
                                    <span class="view-order__item-sku">Артикул: <?php echo esc_html( $product->get_sku() ); ?></span>
                                <?php endif; ?>
                            </div>
                            <div class="view-order__item-qty">
                                <?php echo esc_html( $item->get_quantity() ); ?> шт.
                            </div>
                            <div class="view-order__item-price">
                                <?php echo $order->get_formatted_line_subtotal( $item ); ?>
                            </div>
                        </div>
                        <?php
                    }
                    ?>
                </div>
                
                <!-- Итого -->
                <div class="view-order__summary">
                    <div class="view-order__summary-row">
                        <span>Подитог</span>
                        <span><?php echo wc_price( $order->get_subtotal() ); ?></span>
                    </div>
                    
                    <?php if ( $order->get_shipping_total() > 0 ) : ?>
                    <div class="view-order__summary-row">
                        <span>Доставка</span>
                        <span><?php echo wc_price( $order->get_shipping_total() ); ?></span>
                    </div>
                    <?php endif; ?>
                    
                    <?php if ( $order->get_total_discount() > 0 ) : ?>
                    <div class="view-order__summary-row view-order__summary-row--discount">
                        <span>Скидка</span>
                        <span>-<?php echo wc_price( $order->get_total_discount() ); ?></span>
                    </div>
                    <?php endif; ?>
                    
                    <div class="view-order__summary-row view-order__summary-row--total">
                        <span>Итого</span>
                        <span><?php echo $order->get_formatted_order_total(); ?></span>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Правая колонка -->
        <div class="view-order__sidebar">
            
            <!-- Менеджер -->
            <?php if ( $manager_name ) : ?>
            <div class="view-order__card">
                <h2 class="view-order__section-title">Ваш менеджер</h2>
                
                <div class="view-order__manager">
                    <div class="view-order__manager-avatar">
                        <svg width="32" height="32" viewBox="0 0 24 24" fill="none">
                            <path d="M12 12C14.7614 12 17 9.76142 17 7C17 4.23858 14.7614 2 12 2C9.23858 2 7 4.23858 7 7C7 9.76142 9.23858 12 12 12Z" fill="currentColor"/>
                            <path d="M12 14C7.58172 14 4 17.5817 4 22H20C20 17.5817 16.4183 14 12 14Z" fill="currentColor"/>
                        </svg>
                    </div>
                    <div class="view-order__manager-info">
                        <h3><?php echo esc_html( $manager_name ); ?></h3>
                        
                        <?php if ( $manager_phone ) : ?>
                        <a href="tel:<?php echo esc_attr( preg_replace( '/[^0-9+]/', '', $manager_phone ) ); ?>" class="view-order__manager-contact">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none">
                                <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z" stroke="currentColor" stroke-width="2"/>
                            </svg>
                            <?php echo esc_html( $manager_phone ); ?>
                        </a>
                        <?php endif; ?>
                        
                        <?php if ( $manager_email ) : ?>
                        <a href="mailto:<?php echo esc_attr( $manager_email ); ?>" class="view-order__manager-contact">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none">
                                <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z" stroke="currentColor" stroke-width="2"/>
                                <polyline points="22,6 12,13 2,6" stroke="currentColor" stroke-width="2"/>
                            </svg>
                            <?php echo esc_html( $manager_email ); ?>
                        </a>
                        <?php endif; ?>
                        
                        <?php if ( $manager_telegram ) : ?>
                        <a href="<?php echo esc_url( $manager_telegram ); ?>" class="view-order__manager-contact view-order__manager-contact--telegram" target="_blank">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor">
                                <path d="M12 0C5.373 0 0 5.373 0 12s5.373 12 12 12 12-5.373 12-12S18.627 0 12 0zm5.562 8.161c-.18 1.897-.962 6.502-1.359 8.627-.168.9-.5 1.201-.82 1.23-.697.064-1.226-.46-1.9-.903-1.056-.692-1.653-1.123-2.678-1.799-1.185-.781-.417-1.21.258-1.911.177-.184 3.247-2.977 3.307-3.23.007-.032.014-.15-.056-.212s-.174-.041-.249-.024c-.106.024-1.793 1.139-5.062 3.345-.479.329-.913.489-1.302.481-.428-.009-1.252-.242-1.865-.442-.752-.244-1.349-.374-1.297-.789.027-.216.325-.437.893-.663 3.498-1.524 5.831-2.529 6.998-3.014 3.332-1.386 4.025-1.627 4.476-1.635.099-.002.321.023.465.141.12.099.153.232.168.326.015.093.034.306.019.472z"/>
                            </svg>
                            Telegram
                        </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <?php endif; ?>
            
            <!-- Адрес доставки -->
            <?php if ( $order->get_formatted_shipping_address() ) : ?>
            <div class="view-order__card">
                <h2 class="view-order__section-title">Адрес доставки</h2>
                <address class="view-order__address">
                    <?php echo wp_kses_post( $order->get_formatted_shipping_address() ); ?>
                </address>
            </div>
            <?php endif; ?>
            
            <!-- Этапы заказа -->
            <div class="view-order__card">
                <h2 class="view-order__section-title">Этапы заказа</h2>
                
                <div class="view-order__steps">
                    <?php
                    // Определяем текущий этап
                    $current_step = 1;
                    if ( in_array( $order_status, array( 'processing' ) ) ) {
                        $current_step = 2;
                    } elseif ( $order_status === 'on-hold' ) {
                        $current_step = 2;
                    } elseif ( $tracking_number ) {
                        $current_step = 4;
                    } elseif ( $order_status === 'completed' ) {
                        $current_step = 4;
                    }
                    
                    $steps = array(
                        1 => array( 'title' => 'Заказ создан', 'desc' => 'Менеджер проверяет наличие' ),
                        2 => array( 'title' => 'Отправка счета', 'desc' => 'Счет на оплату' ),
                        3 => array( 'title' => 'Отправка товаров', 'desc' => 'Подготовка к отправке' ),
                        4 => array( 'title' => 'Трекинг', 'desc' => 'Отслеживание доставки' ),
                    );
                    
                    foreach ( $steps as $step_num => $step ) :
                        $is_completed = $step_num < $current_step;
                        $is_current = $step_num === $current_step;
                    ?>
                    <div class="view-order__step <?php echo $is_completed ? 'view-order__step--completed' : ''; ?> <?php echo $is_current ? 'view-order__step--current' : ''; ?>">
                        <div class="view-order__step-indicator">
                            <?php if ( $is_completed ) : ?>
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none">
                                <path d="M20 6L9 17l-5-5" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                            <?php else : ?>
                            <span><?php echo $step_num; ?></span>
                            <?php endif; ?>
                        </div>
                        <div class="view-order__step-content">
                            <h4><?php echo esc_html( $step['title'] ); ?></h4>
                            <p><?php echo esc_html( $step['desc'] ); ?></p>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            
            <!-- Действия -->
            <div class="view-order__actions">
                <?php if ( $order_status === 'completed' ) : ?>
                <a href="<?php echo esc_url( wp_nonce_url( add_query_arg( 'order_again', $order->get_id(), wc_get_cart_url() ), 'woocommerce-order_again' ) ); ?>" class="view-order__btn view-order__btn--primary">
                    Повторить заказ
                </a>
                <?php endif; ?>
                
                <button type="button" class="view-order__btn view-order__btn--secondary" onclick="window.print()">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none">
                        <polyline points="6,9 6,2 18,2 18,9" stroke="currentColor" stroke-width="2"/>
                        <path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2" stroke="currentColor" stroke-width="2"/>
                        <rect x="6" y="14" width="12" height="8" stroke="currentColor" stroke-width="2"/>
                    </svg>
                    Распечатать
                </button>
            </div>
            
        </div>
    </div>
</div>




