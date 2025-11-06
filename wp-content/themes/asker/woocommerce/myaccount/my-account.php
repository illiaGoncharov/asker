<?php
/**
 * Переопределение шаблона WooCommerce My Account
 * Используем наш кастомный дизайн вместо стандартного WooCommerce
 */

// Обработка сохранения профиля
if (isset($_POST['first_name']) && is_user_logged_in()) {
    $user_id = get_current_user_id();
    
    // Обновляем данные пользователя
    update_user_meta($user_id, 'first_name', sanitize_text_field($_POST['first_name']));
    update_user_meta($user_id, 'last_name', sanitize_text_field($_POST['last_name']));
    update_user_meta($user_id, 'billing_phone', sanitize_text_field($_POST['phone']));
    
    // Обновляем email если он изменился
    if (isset($_POST['email']) && $_POST['email'] !== wp_get_current_user()->user_email) {
        $user_data = array(
            'ID' => $user_id,
            'user_email' => sanitize_email($_POST['email'])
        );
        wp_update_user($user_data);
    }
    
    // Показываем сообщение об успехе
    echo '<div class="success-message" style="background: #D1FAE5; color: #065F46; padding: 12px 16px; border-radius: 6px; margin-bottom: 20px; text-align: center;">Профиль успешно обновлен!</div>';
}

?>

<div class="account-page container">
    <?php if (is_user_logged_in()): ?>
        <div class="account-layout">
                <!-- Сайдбар -->
                <aside class="account-sidebar">
                    <div class="sidebar-header">
                        <div class="account-icon">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none">
                                <path d="M12 12C14.7614 12 17 9.76142 17 7C17 4.23858 14.7614 2 12 2C9.23858 2 7 4.23858 7 7C7 9.76142 9.23858 12 12 12Z" fill="currentColor"/>
                                <path d="M12 14C7.58172 14 4 17.5817 4 22H20C20 17.5817 16.4183 14 12 14Z" fill="currentColor"/>
                            </svg>
                        </div>
                        <div class="account-info">
                            <h2>Личный кабинет</h2>
                            <p class="user-name">
                                <?php 
                                $first_name = get_user_meta(get_current_user_id(), 'first_name', true);
                                $last_name = get_user_meta(get_current_user_id(), 'last_name', true);
                                if ($first_name || $last_name) {
                                    echo esc_html(trim($first_name . ' ' . $last_name));
                                } else {
                                    echo esc_html(wp_get_current_user()->display_name);
                                }
                                ?>
                            </p>
                        </div>
                    </div>
                    
                    <nav class="account-nav">
                        <a href="#" class="nav-item active" data-tab="overview">
                            <svg width="20" height="20" viewBox="0 0 20 20" fill="none">
                                <path d="M3 4C3 3.44772 3.44772 3 4 3H16C16.5523 3 17 3.44772 17 4V6C17 6.55228 16.5523 7 16 7H4C3.44772 7 3 6.55228 3 6V4Z" fill="currentColor"/>
                                <path d="M3 10C3 9.44772 3.44772 9 4 9H10C10.5523 9 11 9.44772 11 10V16C11 16.5523 10.5523 17 10 17H4C3.44772 17 3 16.5523 3 16V10Z" fill="currentColor"/>
                                <path d="M13 9C12.4477 9 12 9.44772 12 10V16C12 16.5523 12.4477 17 13 17H16C16.5523 17 17 16.5523 17 16V10C17 9.44772 16.5523 9 16 9H13Z" fill="currentColor"/>
                            </svg>
                            <span>Обзор</span>
                            <svg width="16" height="16" viewBox="0 0 16 16" fill="none">
                                <path d="M6 4L10 8L6 12" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </a>
                        
                        <a href="#" class="nav-item" data-tab="profile">
                            <svg width="20" height="20" viewBox="0 0 20 20" fill="none">
                                <path d="M10 9C11.6569 9 13 7.65685 13 6C13 4.34315 11.6569 3 10 3C8.34315 3 7 4.34315 7 6C7 7.65685 8.34315 9 10 9Z" fill="currentColor"/>
                                <path d="M10 11C6.68629 11 4 13.6863 4 17H16C16 13.6863 13.3137 11 10 11Z" fill="currentColor"/>
                            </svg>
                            <span>Профиль</span>
                            <svg width="16" height="16" viewBox="0 0 16 16" fill="none">
                                <path d="M6 4L10 8L6 12" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </a>
                        
                        <a href="#" class="nav-item" data-tab="orders">
                            <svg width="20" height="20" viewBox="0 0 20 20" fill="none">
                                <path d="M2 3H4L4.4 5M7 13H13L17 5H4.4M7 13L4.4 5M7 13L5.2 15.4C5.1 15.5 5 15.7 5 16V18C5 18.6 5.4 19 6 19H16C16.6 19 17 18.6 17 18V16C17 15.7 16.9 15.5 16.8 15.4L15 13M7 13H15" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                            <span>Мои заказы</span>
                            <svg width="16" height="16" viewBox="0 0 16 16" fill="none">
                                <path d="M6 4L10 8L6 12" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </a>
                        
                        <a href="#" class="nav-item" data-tab="wishlist">
                            <svg width="20" height="20" viewBox="0 0 20 20" fill="none">
                                <path d="M20.84 4.61C20.3292 4.099 19.7228 3.69364 19.0554 3.41708C18.3879 3.14052 17.6725 2.99817 16.95 2.99817C16.2275 2.99817 15.5121 3.14052 14.8446 3.41708C14.1772 3.69364 13.5708 4.099 13.06 4.61L12 5.67L10.94 4.61C9.9083 3.5783 8.50903 2.9987 7.05 2.9987C5.59096 2.9987 4.19169 3.5783 3.16 4.61C2.1283 5.6417 1.5487 7.04097 1.5487 8.5C1.5487 9.95903 2.1283 11.3583 3.16 12.39L4.22 13.45L12 21.23L19.78 13.45L20.84 12.39C21.351 11.8792 21.7563 11.2728 22.0329 10.6053C22.3095 9.93789 22.4518 9.22248 22.4518 8.5C22.4518 7.77752 22.3095 7.06211 22.0329 6.39467C21.7563 5.72723 21.351 5.1208 20.84 4.61Z" fill="currentColor"/>
                            </svg>
                            <span>Избранное</span>
                            <svg width="16" height="16" viewBox="0 0 16 16" fill="none">
                                <path d="M6 4L10 8L6 12" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </a>
                    </nav>
                    
                    <?php
                    // Получаем уровень клиента
                    $level_data = asker_get_customer_level( get_current_user_id() );
                    ?>
                    <div class="user-level">
                        <div class="level-info">
                            <span class="level-label">Ваш уровень:</span>
                            <span class="level-name"><?php echo esc_html( $level_data['level'] ); ?></span>
                            <svg width="16" height="16" viewBox="0 0 16 16" fill="none">
                                <circle cx="8" cy="8" r="7" stroke="currentColor" stroke-width="2"/>
                                <path d="M8 5V8L10 10" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </div>
                        <div class="discount-info">
                            <span class="discount-label">Ваша скидка:</span>
                            <span class="discount-value"><?php echo esc_html( $level_data['discount'] ); ?>%</span>
                        </div>
                        
                        <?php if ( $level_data['next_level'] ) : 
                            $next_level_min = floatval( $level_data['next_level']['level_min'] );
                            $remaining = $next_level_min - $level_data['total_spent'];
                            $progress = ( $level_data['total_spent'] - $level_data['level_min'] ) / ( $next_level_min - $level_data['level_min'] ) * 100;
                            $progress = max( 0, min( 100, $progress ) );
                        ?>
                        <div class="level-progress" style="margin-top: 16px; padding: 12px; background: #F5F6F8; border-radius: 8px;">
                            <p style="font-size: 12px; color: #6B7280; margin: 0 0 8px;">
                                До уровня "<?php echo esc_html( $level_data['next_level']['level_name'] ); ?>": 
                                <strong><?php echo wc_price( $remaining ); ?></strong>
                            </p>
                            <div style="background: #E5E7EB; height: 6px; border-radius: 3px; overflow: hidden;">
                                <div style="background: #FFD600; height: 100%; width: <?php echo round( $progress ); ?>%;"></div>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>
                    
                    <?php
                    // Получаем данные менеджера
                    $manager_id = get_user_meta( get_current_user_id(), 'assigned_manager_id', true );
                    
                    if ( $manager_id ) {
                        $manager = get_post( $manager_id );
                        $manager_phone = get_field( 'manager_phone', $manager_id );
                        $manager_email = get_field( 'manager_email', $manager_id );
                        $manager_telegram = get_field( 'manager_telegram', $manager_id );
                        $manager_whatsapp = get_field( 'manager_whatsapp', $manager_id );
                        $manager_photo = get_the_post_thumbnail_url( $manager_id, 'thumbnail' );
                    } else {
                        // Fallback если менеджер не назначен
                        $manager = null;
                        $manager_phone = '+7 (812) 123-12-23';
                        $manager_email = 'opt@asker-corp.ru';
                        $manager_telegram = null;
                        $manager_whatsapp = null;
                        $manager_photo = null;
                    }
                    ?>
                    
                    <div class="personal-manager">
                        <h3>Ваш персональный менеджер</h3>
                        <div class="manager-card">
                            <div class="manager-avatar">
                                <?php if ( $manager_photo ) : ?>
                                    <img src="<?php echo esc_url( $manager_photo ); ?>" alt="<?php echo esc_attr( $manager ? $manager->post_title : 'Менеджер' ); ?>" style="width: 60px; height: 60px; border-radius: 50%; object-fit: cover;">
                                <?php else : ?>
                                <div class="avatar-placeholder" style="display: flex;">
                                    <svg width="40" height="40" viewBox="0 0 40 40" fill="none">
                                        <circle cx="20" cy="20" r="20" fill="#E5E7EB"/>
                                        <path d="M20 12C22.7614 12 25 14.2386 25 17C25 19.7614 22.7614 22 20 22C17.2386 22 15 19.7614 15 17C15 14.2386 17.2386 12 20 12Z" fill="#9CA3AF"/>
                                        <path d="M20 24C14.4772 24 10 28.4772 10 34H30C30 28.4772 25.5228 24 20 24Z" fill="#9CA3AF"/>
                                    </svg>
                                </div>
                                <?php endif; ?>
                            </div>
                            <div class="manager-info">
                                <h4><?php echo $manager ? esc_html( $manager->post_title ) : 'Владимир Курдов'; ?></h4>
                                <p class="manager-phone">
                                    <a href="tel:<?php echo esc_attr( preg_replace( '/[^0-9+]/', '', $manager_phone ) ); ?>">
                                        <?php echo esc_html( $manager_phone ); ?>
                                    </a>
                                </p>
                                <p class="manager-email">
                                    <a href="mailto:<?php echo esc_attr( $manager_email ); ?>">
                                        <?php echo esc_html( $manager_email ); ?>
                                    </a>
                                </p>
                                
                                <?php if ( $manager_telegram ) : ?>
                                <a href="https://t.me/<?php echo esc_attr( $manager_telegram ); ?>" target="_blank" class="btn-telegram" style="display: inline-block; margin-top: 8px; padding: 6px 12px; background: #0088cc; color: white; text-decoration: none; border-radius: 4px; font-size: 12px;">
                                    💬 Telegram
                                </a>
                                <?php endif; ?>
                                
                                <?php if ( $manager_whatsapp ) : ?>
                                <a href="https://wa.me/<?php echo esc_attr( preg_replace( '/[^0-9]/', '', $manager_whatsapp ) ); ?>" target="_blank" class="btn-whatsapp" style="display: inline-block; margin-top: 8px; padding: 6px 12px; background: #25D366; color: white; text-decoration: none; border-radius: 4px; font-size: 12px;">
                                    📱 WhatsApp
                                </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    
                    <div class="sidebar-footer">
                        <div class="success-sphere">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none">
                                <circle cx="12" cy="12" r="10" fill="currentColor"/>
                                <path d="M9 12L11 14L15 10" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                            <span>Сфера успеха</span>
                        </div>
                        <a href="<?php echo wp_logout_url(home_url('/')); ?>" class="logout-link">
                            Выйти
                            <svg width="16" height="16" viewBox="0 0 16 16" fill="none">
                                <path d="M6 4L10 8L6 12" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </a>
                    </div>
                </aside>
                
                <!-- Основной контент -->
                <main class="account-content">
                    <!-- Вкладка Обзор -->
                    <div class="tab-content active" id="overview">
                        <div class="content-section">
                            <h2>Ваши последние заказы</h2>
                            
                            <div class="orders-table">
                                <?php
                                // Получаем заказы пользователя из WooCommerce
                                if (class_exists('WooCommerce')) {
                                    $customer_orders = wc_get_orders(array(
                                        'customer_id' => get_current_user_id(),
                                        'status' => array('wc-pending', 'wc-processing', 'wc-on-hold', 'wc-completed', 'wc-cancelled', 'wc-refunded', 'wc-failed'),
                                        'limit' => 10,
                                        'orderby' => 'date',
                                        'order' => 'DESC'
                                    ));
                                    
                                    // Безопасная проверка на случай, если wc_get_orders вернет null или WP_Error
                                    if (is_wp_error($customer_orders)) {
                                        $customer_orders = array();
                                    }
                                    if (!is_array($customer_orders)) {
                                        $customer_orders = array();
                                    }
                                    
                                    if (!empty($customer_orders)) {
                                        ?>
                                        <table>
                                            <thead>
                                                <tr>
                                                    <th>№ заказа</th>
                                                    <th>Дата</th>
                                                    <th>Статус</th>
                                                    <th>Сумма заказа</th>
                                                    <th>Действия</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($customer_orders as $order): ?>
                                                    <tr>
                                                        <td>#<?php echo $order->get_order_number(); ?></td>
                                                        <td><?php echo $order->get_date_created()->date('d.m.Y'); ?></td>
                                                        <td>
                                                            <?php
                                                            $status = $order->get_status();
                                                            $status_label = wc_get_order_status_name($status);
                                                            $status_class = '';
                                                            $status_icon = '';
                                                            
                                                            switch ($status) {
                                                                case 'completed':
                                                                    $status_class = 'completed';
                                                                    $status_icon = '<svg width="16" height="16" viewBox="0 0 16 16" fill="none"><path d="M13.5 4.5L6 12L2.5 8.5" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>';
                                                                    break;
                                                                case 'processing':
                                                                case 'on-hold':
                                                                    $status_class = 'delivery';
                                                                    $status_icon = '<svg width="16" height="16" viewBox="0 0 16 16" fill="none"><path d="M1 3H15L13 9H3L1 3Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/><path d="M13 9V13C13 13.6 12.6 14 12 14H4C3.4 14 3 13.6 3 13V9" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>';
                                                                    break;
                                                                case 'cancelled':
                                                                case 'failed':
                                                                    $status_class = 'cancelled';
                                                                    $status_icon = '<svg width="16" height="16" viewBox="0 0 16 16" fill="none"><path d="M12 4L4 12M4 4L12 12" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>';
                                                                    break;
                                                                default:
                                                                    $status_class = 'pending';
                                                                    $status_icon = '<svg width="16" height="16" viewBox="0 0 16 16" fill="none"><circle cx="8" cy="8" r="7" stroke="currentColor" stroke-width="2"/><path d="M8 5V8L10 10" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>';
                                                            }
                                                            ?>
                                                            <span class="status <?php echo $status_class; ?>">
                                                                <?php echo $status_icon; ?>
                                                                <?php echo $status_label; ?>
                                                            </span>
                                                        </td>
                                                        <td><?php echo $order->get_formatted_order_total(); ?></td>
                                                        <td>
                                                            <div class="order-actions">
                                                                <a href="<?php echo $order->get_view_order_url(); ?>" class="btn-secondary">Посмотреть</a>
                                                                <?php if ($status === 'completed'): ?>
                                                                    <a href="<?php echo wp_nonce_url(add_query_arg('order_again', $order->get_id(), wc_get_cart_url()), 'woocommerce-order_again'); ?>" class="btn-primary">Повторить</a>
                                                                <?php endif; ?>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                        <?php
                                    } else {
                                        ?>
                                        <div class="no-orders">
                                            <p>У вас пока нет заказов</p>
                                            <a href="<?php echo esc_url(home_url('/shop')); ?>" class="btn-primary">Перейти в каталог</a>
                                        </div>
                                        <?php
                                    }
                                }
                                ?>
                            </div>
                            
                            <?php
                            // Показываем пагинацию только если заказов больше 10
                            $all_orders = wc_get_orders(array(
                                'customer_id' => get_current_user_id(),
                                'status' => array('wc-pending', 'wc-processing', 'wc-on-hold', 'wc-completed', 'wc-cancelled', 'wc-refunded', 'wc-failed'),
                                'limit' => -1
                            ));
                            // Безопасная проверка на случай, если wc_get_orders вернет null или WP_Error
                            if (is_wp_error($all_orders)) {
                                $all_orders = array();
                            }
                            if (!is_array($all_orders)) {
                                $all_orders = array();
                            }
                            $orders_count = count($all_orders);
                            if ($orders_count > 10):
                            ?>
                            <div class="pagination">
                                <button class="pagination-btn prev" disabled>
                                    <svg width="16" height="16" viewBox="0 0 16 16" fill="none">
                                        <path d="M10 12L6 8L10 4" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                    </svg>
                                    Предыдущая
                                </button>
                                
                                <div class="pagination-numbers">
                                    <span class="page-number active">1</span>
                                    <span class="page-number">2</span>
                                    <span class="page-number">3</span>
                                </div>
                                
                                <button class="pagination-btn next">
                                    Следующая
                                    <svg width="16" height="16" viewBox="0 0 16 16" fill="none">
                                        <path d="M6 4L10 8L6 12" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                    </svg>
                                </button>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <!-- Вкладка Профиль -->
                    <div class="tab-content" id="profile">
                        <div class="content-section">
                            <h2>Мой профиль</h2>
                            <div class="profile-form">
                                <form method="post" action="<?php echo esc_url(get_permalink()); ?>">
                                    <?php wp_nonce_field('update_profile', 'profile_nonce'); ?>
                                    <div class="form-group">
                                        <label for="first_name">Имя</label>
                                        <input type="text" id="first_name" name="first_name" value="<?php echo esc_attr(get_user_meta(get_current_user_id(), 'first_name', true)); ?>" required>
                                    </div>
                                    <div class="form-group">
                                        <label for="last_name">Фамилия</label>
                                        <input type="text" id="last_name" name="last_name" value="<?php echo esc_attr(get_user_meta(get_current_user_id(), 'last_name', true)); ?>">
                                    </div>
                                    <div class="form-group">
                                        <label for="email">Email</label>
                                        <input type="email" id="email" name="email" value="<?php echo esc_attr(wp_get_current_user()->user_email); ?>" required>
                                    </div>
                                    <div class="form-group">
                                        <label for="phone">Телефон</label>
                                        <input type="tel" id="phone" name="phone" value="<?php echo esc_attr(get_user_meta(get_current_user_id(), 'billing_phone', true)); ?>">
                                    </div>
                                    <button type="submit" class="btn-primary">Сохранить изменения</button>
                                </form>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Вкладка Заказы -->
                    <div class="tab-content" id="orders">
                        <div class="content-section">
                            <h2>Все мои заказы</h2>
                            <div class="orders-table">
                                <?php
                                // Пагинация заказов
                                if (class_exists('WooCommerce')) {
                                    $paged = isset( $_GET['orders_page'] ) ? max( 1, intval( $_GET['orders_page'] ) ) : 1;
                                    $per_page = 15;
                                    
                                    // Получаем все заказы для подсчета
                                    $total_orders_ids = wc_get_orders(array(
                                        'customer_id' => get_current_user_id(),
                                        'status' => array('wc-pending', 'wc-processing', 'wc-on-hold', 'wc-completed', 'wc-cancelled', 'wc-refunded', 'wc-failed'),
                                        'limit' => -1,
                                        'return' => 'ids'
                                    ));
                                    
                                    $total_orders = is_array($total_orders_ids) ? count($total_orders_ids) : 0;
                                    $total_pages = ceil( $total_orders / $per_page );
                                    
                                    // Получаем заказы для текущей страницы
                                    $all_orders = wc_get_orders(array(
                                        'customer_id' => get_current_user_id(),
                                        'status' => array('wc-pending', 'wc-processing', 'wc-on-hold', 'wc-completed', 'wc-cancelled', 'wc-refunded', 'wc-failed'),
                                        'limit' => $per_page,
                                        'offset' => ( $paged - 1 ) * $per_page,
                                        'orderby' => 'date',
                                        'order' => 'DESC'
                                    ));
                                    
                                    // Безопасная проверка на случай, если wc_get_orders вернет null или WP_Error
                                    if (is_wp_error($all_orders)) {
                                        $all_orders = array();
                                    }
                                    if (!is_array($all_orders)) {
                                        $all_orders = array();
                                    }
                                    
                                    if (!empty($all_orders)) {
                                        ?>
                                        <table>
                                            <thead>
                                                <tr>
                                                    <th>№ заказа</th>
                                                    <th>Дата</th>
                                                    <th>Статус</th>
                                                    <th>Сумма заказа</th>
                                                    <th>Действия</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($all_orders as $order): ?>
                                                    <tr>
                                                        <td>#<?php echo $order->get_order_number(); ?></td>
                                                        <td><?php echo $order->get_date_created()->date('d.m.Y'); ?></td>
                                                        <td>
                                                            <?php
                                                            $status = $order->get_status();
                                                            $status_label = wc_get_order_status_name($status);
                                                            $status_class = '';
                                                            $status_icon = '';
                                                            
                                                            switch ($status) {
                                                                case 'completed':
                                                                    $status_class = 'completed';
                                                                    $status_icon = '<svg width="16" height="16" viewBox="0 0 16 16" fill="none"><path d="M13.5 4.5L6 12L2.5 8.5" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>';
                                                                    break;
                                                                case 'processing':
                                                                case 'on-hold':
                                                                    $status_class = 'delivery';
                                                                    $status_icon = '<svg width="16" height="16" viewBox="0 0 16 16" fill="none"><path d="M1 3H15L13 9H3L1 3Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/><path d="M13 9V13C13 13.6 12.6 14 12 14H4C3.4 14 3 13.6 3 13V9" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>';
                                                                    break;
                                                                case 'cancelled':
                                                                case 'failed':
                                                                    $status_class = 'cancelled';
                                                                    $status_icon = '<svg width="16" height="16" viewBox="0 0 16 16" fill="none"><path d="M12 4L4 12M4 4L12 12" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>';
                                                                    break;
                                                                default:
                                                                    $status_class = 'pending';
                                                                    $status_icon = '<svg width="16" height="16" viewBox="0 0 16 16" fill="none"><circle cx="8" cy="8" r="7" stroke="currentColor" stroke-width="2"/><path d="M8 5V8L10 10" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>';
                                                            }
                                                            ?>
                                                            <span class="status <?php echo $status_class; ?>">
                                                                <?php echo $status_icon; ?>
                                                                <?php echo $status_label; ?>
                                                            </span>
                                                        </td>
                                                        <td><?php echo $order->get_formatted_order_total(); ?></td>
                                                        <td>
                                                            <div class="order-actions">
                                                                <a href="<?php echo $order->get_view_order_url(); ?>" class="btn-secondary">Посмотреть</a>
                                                                <?php if ($status === 'completed'): ?>
                                                                    <a href="<?php echo wp_nonce_url(add_query_arg('order_again', $order->get_id(), wc_get_cart_url()), 'woocommerce-order_again'); ?>" class="btn-primary">Повторить</a>
                                                                <?php endif; ?>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                        
                                        <?php if ( $total_pages > 1 ) : ?>
                                        <div class="pagination" style="display: flex; align-items: center; justify-content: center; gap: 8px; margin-top: 24px;">
                                            <?php if ( $paged > 1 ) : ?>
                                                <a href="?orders_page=<?php echo $paged - 1; ?>#orders" class="pagination-btn" style="padding: 8px 16px; background: #fff; border: 1px solid #E5E7EB; border-radius: 6px; text-decoration: none; color: #111827;">
                                                    ← Предыдущая
                                                </a>
                                            <?php endif; ?>
                                            
                                            <div class="pagination-numbers" style="display: flex; gap: 4px;">
                                                <?php
                                                // Показываем максимум 7 страниц
                                                $range = 3; // Сколько страниц показывать по бокам от текущей
                                                $start = max( 1, $paged - $range );
                                                $end = min( $total_pages, $paged + $range );
                                                
                                                if ( $start > 1 ) {
                                                    echo '<a href="?orders_page=1#orders" style="padding: 8px 12px; background: #fff; border: 1px solid #E5E7EB; border-radius: 6px; text-decoration: none; color: #111827;">1</a>';
                                                    if ( $start > 2 ) echo '<span style="padding: 8px 12px;">...</span>';
                                                }
                                                
                                                for ( $i = $start; $i <= $end; $i++ ) :
                                                    if ( $i === $paged ) : ?>
                                                        <span class="page-number active" style="padding: 8px 12px; background: #FFD600; border: 1px solid #FFD600; border-radius: 6px; font-weight: 600; color: #111827;"><?php echo $i; ?></span>
                                                    <?php else : ?>
                                                        <a href="?orders_page=<?php echo $i; ?>#orders" style="padding: 8px 12px; background: #fff; border: 1px solid #E5E7EB; border-radius: 6px; text-decoration: none; color: #111827;"><?php echo $i; ?></a>
                                                    <?php endif; ?>
                                                <?php endfor;
                                                
                                                if ( $end < $total_pages ) {
                                                    if ( $end < $total_pages - 1 ) echo '<span style="padding: 8px 12px;">...</span>';
                                                    echo '<a href="?orders_page=' . $total_pages . '#orders" style="padding: 8px 12px; background: #fff; border: 1px solid #E5E7EB; border-radius: 6px; text-decoration: none; color: #111827;">' . $total_pages . '</a>';
                                                }
                                                ?>
                                            </div>
                                            
                                            <?php if ( $paged < $total_pages ) : ?>
                                                <a href="?orders_page=<?php echo $paged + 1; ?>#orders" class="pagination-btn" style="padding: 8px 16px; background: #fff; border: 1px solid #E5E7EB; border-radius: 6px; text-decoration: none; color: #111827;">
                                                    Следующая →
                                                </a>
                                            <?php endif; ?>
                                        </div>
                                        <?php endif; ?>
                                        
                                        <?php
                                    } else {
                                        ?>
                                        <div class="no-orders">
                                            <p>У вас пока нет заказов</p>
                                            <a href="<?php echo esc_url(home_url('/shop')); ?>" class="btn-primary">Перейти в каталог</a>
                                        </div>
                                        <?php
                                    }
                                }
                                ?>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Вкладка Избранное -->
                    <div class="tab-content" id="wishlist">
                        <div class="content-section">
                            <h2>Мое избранное</h2>
                            <div class="wishlist-products">
                                <?php
                                $customer_id = get_current_user_id();
                                $wishlist_items = get_user_meta($customer_id, 'asker_wishlist', true);
                                
                                // Если в user_meta пусто, пытаемся синхронизировать с localStorage через JS
                                if (empty($wishlist_items) || !is_array($wishlist_items)) {
                                    $wishlist_items = array();
                                }
                                
                                if (!empty($wishlist_items)) :
                                    ?>
                                    <div class="products-grid">
                                        <?php foreach ($wishlist_items as $product_id) :
                                            $product = wc_get_product($product_id);
                                            if ($product && $product->is_visible()) :
                                                $product_image = wp_get_attachment_image_src(get_post_thumbnail_id($product_id), 'medium');
                                                $product_url = get_permalink($product_id);
                                                $price = $product->get_price_html();
                                                // Показываем полную цену (не убираем копейки)
                                                ?>
                                                <div class="product-card">
                                                    <button class="product-favorite active favorite-btn" data-product-id="<?php echo esc_attr($product_id); ?>" aria-label="Удалить из избранного">
                                                        <img src="<?php echo get_template_directory_uri(); ?>/assets/images/ui/like[active].svg" alt="Избранное" class="favorite-icon favorite-icon--active">
                                                        <img src="<?php echo get_template_directory_uri(); ?>/assets/images/ui/like[idle].svg" alt="Добавить в избранное" class="favorite-icon favorite-icon--idle">
                                                    </button>
                                                    <a href="<?php echo esc_url($product_url); ?>">
                                                        <?php if ($product_image) : ?>
                                                            <img class="product-image" src="<?php echo esc_url($product_image[0]); ?>" alt="<?php echo esc_attr($product->get_name()); ?>">
                                                        <?php else : ?>
                                                            <div class="product-placeholder"><?php echo esc_html($product->get_name()); ?></div>
                                                        <?php endif; ?>
                                                    </a>
                                                    <h3 class="product-title">
                                                        <a href="<?php echo esc_url($product_url); ?>"><?php echo esc_html($product->get_name()); ?></a>
                                                    </h3>
                                                    <div class="product-bottom">
                                                        <div class="product-price"><?php echo $price; ?></div>
                                                        <button class="btn-add-cart add_to_cart_button" data-product-id="<?php echo esc_attr($product_id); ?>">В корзину</button>
                                                    </div>
                                                </div>
                                            <?php
                                            endif;
                                        endforeach; ?>
                                    </div>
                                <?php else : ?>
                                    <div class="no-products">
                                        <p>В вашем избранном пока нет товаров.</p>
                                        <a href="<?php echo esc_url(home_url('/shop')); ?>" class="btn-primary">Перейти в каталог</a>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </main>
        </div>
        
        <script>
            // Синхронизация избранного: загружаем из user_meta в localStorage при загрузке ЛК
            jQuery(document).ready(function($) {
                <?php
                $wishlist_items = get_user_meta(get_current_user_id(), 'asker_wishlist', true);
                if (!empty($wishlist_items) && is_array($wishlist_items)) {
                    $wishlist_json = json_encode(array_map('intval', $wishlist_items));
                } else {
                    $wishlist_json = '[]';
                }
                ?>
                const serverWishlist = <?php echo $wishlist_json; ?>;
                const localWishlist = JSON.parse(localStorage.getItem('favorites') || '[]');
                
                // Объединяем: приоритет у сервера (если есть)
                let mergedWishlist = serverWishlist.length > 0 ? serverWishlist : localWishlist;
                
                // Убираем дубликаты
                mergedWishlist = [...new Set(mergedWishlist)];
                
                // Сохраняем объединённый список
                localStorage.setItem('favorites', JSON.stringify(mergedWishlist));
                
                // Если были изменения, синхронизируем с сервером
                if (JSON.stringify(mergedWishlist) !== JSON.stringify(serverWishlist)) {
                    if (typeof asker_ajax !== 'undefined') {
                        $.ajax({
                            url: asker_ajax.ajax_url,
                            type: 'POST',
                            data: {
                                action: 'asker_sync_wishlist',
                                product_ids: mergedWishlist
                            }
                        });
                    }
                }
                
                // Обновляем счетчик
                if (typeof updateWishlistCounter === 'function') {
                    updateWishlistCounter();
                }
            });
            </script>
    <?php else: ?>
        <?php
        // Используем стандартный шаблон WooCommerce для формы входа
        // Он автоматически использует наш переопределенный form-login.php
        wc_get_template('myaccount/form-login.php');
        ?>
    <?php endif; ?>
</div>

