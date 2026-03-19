<?php
/**
 * Переопределение шаблона WooCommerce My Account
 * Используем наш кастомный дизайн вместо стандартного WooCommerce
 *
 * @see     https://docs.woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 3.5.0
 */

// Инициализируем переменные для аватара
$avatar_uploaded = false;
$new_avatar_url = '';

// Обработка сохранения профиля
if (isset($_POST['first_name']) && is_user_logged_in()) {
    $user_id = get_current_user_id();
    
    // Обработка загрузки аватара
    // Отладочный вывод для администраторов
    if ( current_user_can('administrator') ) {
        error_log('=== AVATAR UPLOAD DEBUG START ===');
        error_log('REQUEST_METHOD: ' . $_SERVER['REQUEST_METHOD']);
        error_log('CONTENT_TYPE: ' . (isset($_SERVER['CONTENT_TYPE']) ? $_SERVER['CONTENT_TYPE'] : 'NOT SET'));
        error_log('FILES array: ' . print_r($_FILES, true));
        error_log('POST array: ' . print_r($_POST, true));
        error_log('POST first_name: ' . (isset($_POST['first_name']) ? $_POST['first_name'] : 'NOT SET'));
        error_log('FILES avatar exists: ' . (isset($_FILES['avatar']) ? 'YES' : 'NO'));
        if (isset($_FILES['avatar'])) {
            error_log('FILES avatar name: ' . (isset($_FILES['avatar']['name']) ? $_FILES['avatar']['name'] : 'NOT SET'));
            error_log('FILES avatar error: ' . (isset($_FILES['avatar']['error']) ? $_FILES['avatar']['error'] : 'NOT SET'));
            error_log('FILES avatar size: ' . (isset($_FILES['avatar']['size']) ? $_FILES['avatar']['size'] : 'NOT SET'));
            error_log('FILES avatar type: ' . (isset($_FILES['avatar']['type']) ? $_FILES['avatar']['type'] : 'NOT SET'));
            error_log('FILES avatar tmp_name: ' . (isset($_FILES['avatar']['tmp_name']) ? $_FILES['avatar']['tmp_name'] : 'NOT SET'));
        }
        error_log('=== AVATAR UPLOAD DEBUG END ===');
    }
    
    // Проверяем, был ли отправлен файл
    $avatar_file_sent = false;
    if (isset($_FILES['avatar'])) {
        if (!empty($_FILES['avatar']['name'])) {
            $avatar_file_sent = true;
            if ($_FILES['avatar']['error'] !== UPLOAD_ERR_OK) {
                if ( current_user_can('administrator') ) {
                    error_log('Avatar upload error code: ' . $_FILES['avatar']['error']);
                }
            }
        }
    }
    
    if ($avatar_file_sent && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
        require_once(ABSPATH . 'wp-admin/includes/image.php');
        require_once(ABSPATH . 'wp-admin/includes/file.php');
        require_once(ABSPATH . 'wp-admin/includes/media.php');
        
        // Проверяем тип файла
        $allowed_types = array('image/jpeg', 'image/png', 'image/gif', 'image/webp');
        $file_type = wp_check_filetype($_FILES['avatar']['name']);
        $mime_type = $_FILES['avatar']['type'];
        
        if (in_array($mime_type, $allowed_types) || in_array($file_type['type'], $allowed_types)) {
            // Удаляем старый аватар если есть
            $old_avatar_id = get_user_meta($user_id, 'custom_avatar', true);
            if ($old_avatar_id) {
                wp_delete_attachment($old_avatar_id, true);
            }
            
            // Используем wp_handle_upload для загрузки файла
            $upload_overrides = array('test_form' => false);
            $uploaded_file = wp_handle_upload($_FILES['avatar'], $upload_overrides);
            
            if ( current_user_can('administrator') ) {
                error_log('wp_handle_upload result: ' . print_r($uploaded_file, true));
            }
            
            if (!isset($uploaded_file['error']) && isset($uploaded_file['file'])) {
                // Создаем attachment
                $attachment_data = array(
                    'post_mime_type' => $uploaded_file['type'],
                    'post_title' => sanitize_file_name(pathinfo($_FILES['avatar']['name'], PATHINFO_FILENAME)),
                    'post_content' => '',
                    'post_status' => 'inherit',
                    'post_author' => $user_id
                );
                
                $attachment_id = wp_insert_attachment($attachment_data, $uploaded_file['file']);
                
                if ( current_user_can('administrator') ) {
                    error_log('wp_insert_attachment result: ' . ($attachment_id ? $attachment_id : 'NULL'));
                    if (is_wp_error($attachment_id)) {
                        error_log('wp_insert_attachment error: ' . $attachment_id->get_error_message());
                    }
                }
                
                if (!is_wp_error($attachment_id) && $attachment_id) {
                    // Генерируем метаданные
                    $attach_data = wp_generate_attachment_metadata($attachment_id, $uploaded_file['file']);
                    wp_update_attachment_metadata($attachment_id, $attach_data);
                    
                    // Сохраняем attachment ID
                    $saved = update_user_meta($user_id, 'custom_avatar', $attachment_id);
                    
                    // Проверяем сразу после сохранения
                    $check_id = get_user_meta($user_id, 'custom_avatar', true);
                    if ( current_user_can('administrator') ) {
                        error_log('After update_user_meta: saved = ' . ($saved ? 'true' : 'false') . ', check_id = ' . $check_id);
                    }
                    
                    // Получаем URL нового аватара
                    $new_avatar_url = wp_get_attachment_image_url($attachment_id, 'thumbnail');
                    if (!$new_avatar_url) {
                        $new_avatar_url = wp_get_attachment_url($attachment_id);
                    }
                    
                    if ($new_avatar_url) {
                        update_user_meta($user_id, 'custom_avatar_url', $new_avatar_url);
                        $avatar_uploaded = true;
                        
                        // Проверяем сразу после сохранения URL
                        $check_url = get_user_meta($user_id, 'custom_avatar_url', true);
                        if ( current_user_can('administrator') ) {
                            error_log('Avatar uploaded successfully: ID=' . $attachment_id . ', URL=' . $new_avatar_url);
                            error_log('Avatar saved check: ID=' . $check_id . ', URL=' . $check_url);
                        }
                    } else {
                        error_log('Avatar uploaded but URL is empty: ID=' . $attachment_id);
                    }
                } else {
                    $error_msg = is_wp_error($attachment_id) ? $attachment_id->get_error_message() : 'Unknown error';
                    error_log('Avatar attachment creation error: ' . $error_msg);
                }
            } else {
                $error_msg = isset($uploaded_file['error']) ? $uploaded_file['error'] : 'Unknown error';
                error_log('Avatar upload error: ' . $error_msg);
                error_log('FILES array: ' . print_r($_FILES, true));
            }
        } else {
            error_log('Avatar upload: Invalid file type. MIME: ' . $mime_type . ', Type: ' . $file_type['type']);
        }
    } else {
        // Файл не был отправлен или была ошибка
        if ( current_user_can('administrator') ) {
            if (!$avatar_file_sent) {
                error_log('Avatar upload: File was not sent in form');
            } else {
                error_log('Avatar upload: File was sent but has error code: ' . $_FILES['avatar']['error']);
            }
        }
    }
    
    // Обновляем данные пользователя
    update_user_meta($user_id, 'first_name', sanitize_text_field($_POST['first_name']));
    update_user_meta($user_id, 'last_name', sanitize_text_field($_POST['last_name']));
    update_user_meta($user_id, 'billing_phone', sanitize_text_field($_POST['phone']));
    update_user_meta($user_id, 'billing_company', sanitize_text_field($_POST['company_name']));
    update_user_meta($user_id, 'billing_inn', sanitize_text_field($_POST['company_inn']));
    
    // Обновляем email если он изменился
    if (isset($_POST['email']) && $_POST['email'] !== wp_get_current_user()->user_email) {
        $user_data = array(
            'ID' => $user_id,
            'user_email' => sanitize_email($_POST['email'])
        );
        wp_update_user($user_data);
    }
    
    // Обновляем пароль если нужно
    if (isset($_POST['change_password']) && $_POST['change_password'] && !empty($_POST['new_password'])) {
        if ($_POST['new_password'] === $_POST['confirm_password']) {
            wp_set_password($_POST['new_password'], $user_id);
        }
    }
    
    // Показываем сообщение об успехе или ошибке
    if ($avatar_uploaded) {
        $success_message = '<div class="success-message" style="background: #D1FAE5; color: #065F46; padding: 12px 16px; border-radius: 6px; margin-bottom: 20px; text-align: center;">Профиль успешно обновлен! Аватар загружен.</div>';
    } else {
        $success_message = '<div class="success-message" style="background: #D1FAE5; color: #065F46; padding: 12px 16px; border-radius: 6px; margin-bottom: 20px; text-align: center;">Профиль успешно обновлен!</div>';
        
        // Показываем ошибку ТОЛЬКО если пользователь реально пытался загрузить файл
        // и произошла ошибка (не UPLOAD_ERR_NO_FILE который означает что файл просто не выбран)
        if (isset($_FILES['avatar']) && !empty($_FILES['avatar']['name']) && $_FILES['avatar']['error'] !== UPLOAD_ERR_OK && $_FILES['avatar']['error'] !== UPLOAD_ERR_NO_FILE) {
                        $error_codes = array(
                UPLOAD_ERR_INI_SIZE => 'Файл превышает максимальный размер',
                            UPLOAD_ERR_FORM_SIZE => 'Файл превышает максимальный размер формы',
                            UPLOAD_ERR_PARTIAL => 'Файл загружен частично',
                UPLOAD_ERR_NO_TMP_DIR => 'Ошибка сервера: отсутствует временная папка',
                UPLOAD_ERR_CANT_WRITE => 'Ошибка сервера: не удалось записать файл',
                UPLOAD_ERR_EXTENSION => 'Загрузка остановлена'
                        );
                        $error_msg = isset($error_codes[$_FILES['avatar']['error']]) ? $error_codes[$_FILES['avatar']['error']] : 'Неизвестная ошибка';
            $success_message .= '<div class="error-message" style="background: #FEE2E2; color: #991B1B; padding: 12px 16px; border-radius: 6px; margin-bottom: 20px; text-align: center;">Ошибка загрузки аватара: ' . $error_msg . '</div>';
        }
    }
}

// Проверяем WooCommerce endpoints - если мы на специальном endpoint, выводим его
global $wp;

// Если view-order endpoint - показываем шаблон просмотра заказа
if ( isset( $wp->query_vars['view-order'] ) && $wp->query_vars['view-order'] ) {
    $order_id = absint( $wp->query_vars['view-order'] );
    $order = wc_get_order( $order_id );
    
    // Проверяем, что заказ принадлежит текущему пользователю
    if ( $order && $order->get_user_id() === get_current_user_id() ) {
        wc_get_template( 'myaccount/view-order.php', array( 'order' => $order, 'order_id' => $order_id ) );
        return;
    } else {
        wc_print_notice( __( 'Недостаточно прав для просмотра этого заказа', 'woocommerce' ), 'error' );
        return;
    }
}

// Если orders endpoint - показываем список заказов (обрабатываем далее в нашем шаблоне)
// Если edit-account endpoint - показываем редактирование аккаунта (обрабатываем далее)
// Если lost-password endpoint - показываем форму восстановления (обрабатывается WooCommerce)
?>

<!-- 
==========================================
ASKER CUSTOM TEMPLATE my-account.php LOADED
Time: <?php echo date('Y-m-d H:i:s'); ?>
File: <?php echo __FILE__; ?>
==========================================
-->
<div class="account-page container" data-template="asker-custom-my-account">
    <!-- Уведомления WooCommerce -->
    <?php woocommerce_output_all_notices(); ?>
    
    <?php if (is_user_logged_in()): ?>
        <?php if (isset($success_message)): ?>
            <?php echo $success_message; ?>
        <?php endif; ?>
        <div class="account-layout">
                <!-- Сайдбар -->
                <aside class="account-sidebar">
                    <div class="sidebar-header">
                        <div class="account-icon">
                            <img src="<?php echo get_template_directory_uri(); ?>/assets/images/login/acc_main.svg" alt="Личный кабинет" width="24" height="24">
                        </div>
                        <div class="account-info">
                            <h2>Личный кабинет</h2>
                        </div>
                    </div>
                    
                    <nav class="account-nav">
                        <a href="#" class="nav-item active" data-tab="overview">
                            <img src="<?php echo get_template_directory_uri(); ?>/assets/images/login/acc_general.svg" alt="Обзор" width="20" height="20">
                            <span>Обзор</span>
                            <svg width="16" height="16" viewBox="0 0 16 16" fill="none">
                                <path d="M6 4L10 8L6 12" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </a>
                        
                        <a href="#" class="nav-item" data-tab="profile">
                            <img src="<?php echo get_template_directory_uri(); ?>/assets/images/login/acc_profile.svg" alt="Профиль" width="20" height="20">
                            <span>Профиль</span>
                            <svg width="16" height="16" viewBox="0 0 16 16" fill="none">
                                <path d="M6 4L10 8L6 12" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </a>
                        
                        <a href="#" class="nav-item" data-tab="orders">
                            <img src="<?php echo get_template_directory_uri(); ?>/assets/images/login/acc_order.svg" alt="Мои заказы" width="20" height="20">
                            <span>Мои заказы</span>
                            <svg width="16" height="16" viewBox="0 0 16 16" fill="none">
                                <path d="M6 4L10 8L6 12" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </a>
                        
                        <a href="#" class="nav-item" data-tab="wishlist">
                            <img src="<?php echo get_template_directory_uri(); ?>/assets/images/login/acc_like.svg" alt="Избранное" width="20" height="20">
                            <span>Избранное</span>
                            <svg width="16" height="16" viewBox="0 0 16 16" fill="none">
                                <path d="M6 4L10 8L6 12" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </a>
                    </nav>
                    

                <?php
                // Получаем уровень клиента и скидки
                $user_id = get_current_user_id();
                $level_data = asker_get_customer_level( $user_id );
                $level_discount = $level_data['discount']; // Скидка от уровня
                
                // Получаем индивидуальную скидку
                $individual_discount = get_user_meta( $user_id, 'individual_discount', true );
                $individual_discount = $individual_discount ? floatval( $individual_discount ) : 0;
                ?>
                <div class="user-level">
<!--                     Уровень
<div class="level-info">
    <span class="level-label">Ваш уровень:</span>
    <span class="level-name"><?php echo esc_html( $level_data['level'] ); ?></span>
    
    <div class="level-help-icon" data-tooltip="Правила уровней: Уровень определяется суммой ваших покупок. Чем больше сумма, тем выше уровень и больше скидка.">
        <svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
            <circle cx="8" cy="8" r="7" stroke="currentColor" stroke-width="1.2" fill="none"/>
            <path d="M8 6C7.44772 6 7 6.44772 7 7C7 7.55228 7.44772 8 8 8C8.55228 8 9 7.55228 9 7C9 6.44772 8.55228 6 8 6Z" fill="currentColor"/>
            <path d="M8 9.5V11.5" stroke="currentColor" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round"/>
            <circle cx="8" cy="12.5" r="0.5" fill="currentColor"/>
        </svg>
    </div>
</div>

Скидка от уровня
<div class="discount-info">
    <span class="discount-label">Ваша скидка:</span>
    <span class="discount-value"><?php echo esc_html( $level_discount ); ?>%</span>
</div> -->
                    
                    <?php if ( $individual_discount > 0 ) : ?>
                    <!-- Индивидуальная скидка (если есть) - в одну строку -->
                    <div class="individual-discount-info">
                        <span class="discount-label">Индивидуальная скидка:</span>
                        <span class="discount-value"><?php echo esc_html( $individual_discount ); ?>%</span>
                    </div>
                    <?php endif; ?>
                    
                    <?php 
                    // Кнопка "Запросить скидку" - только для Базового уровня
                    if ( $level_data['level'] === 'Базовый' ) : ?>
                    <button type="button" class="request-discount-btn" onclick="openDiscountRequestPopup()">
                        <svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M8 1L10.163 5.279L15 6.017L11.5 9.421L12.326 14.219L8 12L3.674 14.219L4.5 9.421L1 6.017L5.837 5.279L8 1Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                        Запросить скидку
                    </button>
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
                    
                    <?php
                    // Получаем данные пользователя для отображения в "Сфера успеха"
                    $user_id = get_current_user_id();
                    $user_company = get_user_meta( $user_id, 'billing_company', true );
                    $user_first_name = get_user_meta( $user_id, 'first_name', true );
                    $user_last_name = get_user_meta( $user_id, 'last_name', true );
                    
                    // Формируем текст для отображения
                    $display_text = 'Сфера успеха'; // По умолчанию
                    if ( !empty( $user_company ) ) {
                        // Если заполнено название организации - показываем его
                        $display_text = esc_html( $user_company );
                    } elseif ( !empty( $user_first_name ) ) {
                        // Иначе показываем имя пользователя
                        $display_text = esc_html( trim( $user_first_name . ' ' . $user_last_name ) );
                        if ( empty( trim( $display_text ) ) ) {
                            $display_text = esc_html( $user_first_name );
                        }
                    }
                    ?>
                    <div class="sidebar-footer">
                        <div class="success-sphere">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none">
                                <circle cx="12" cy="12" r="10" fill="currentColor"/>
                                <path d="M9 12L11 14L15 10" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                            <span><?php echo $display_text; ?></span>
                        </div>
                        <a href="<?php echo wp_nonce_url( add_query_arg( 'customer-logout', 'true', home_url('/') ), 'customer-logout' ); ?>" class="logout-link">
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
            // Получаем заказы пользователя из WooCommerce (МАКСИМУМ 5)
            if (class_exists('WooCommerce')) {
                $customer_orders = wc_get_orders(array(
                    'customer_id' => get_current_user_id(),
                    'status' => array('wc-pending', 'wc-processing', 'wc-on-hold', 'wc-completed', 'wc-cancelled', 'wc-refunded', 'wc-failed'),
                    'limit' => 5,
                    'orderby' => 'date',
                    'order' => 'DESC'
                ));
                
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
                    
                    <!-- Ссылка на все заказы -->
<!--                     <div style="margin-top: 20px; text-align: center;"> -->
<!--                         <a href="#" class="btn-secondary nav-item" data-tab="orders" style="display: inline-block; padding: 10px 24px; text-decoration: none;">Посмотреть все заказы</a> -->
<!--                     </div> -->
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
    
    <!-- Популярные товары -->
    <div class="content-section" style="margin-top: 40px;">
        <h2>Популярные товары</h2>
        <div class="products-grid" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(250px, 1fr)); gap: 20px; margin-top: 24px;">
            <?php
            // Получаем ID главной страницы
            $frontpage_id = get_option('page_on_front');
            
            // Получаем выбранные товары из ACF с главной страницы
            $featured_products = function_exists('get_field') ? get_field('featured_products', $frontpage_id) : false;
            
            // Отладка для администраторов
            if (current_user_can('administrator') && isset($_GET['debug_products'])) {
                echo '<!-- DEBUG: frontpage_id = ' . $frontpage_id . ', featured_products = ' . print_r($featured_products, true) . ' -->';
            }
            
            if ($featured_products && is_array($featured_products)) :
                $featured_products = array_slice($featured_products, 0, 3);
                foreach ($featured_products as $product_id) :
                    $product = wc_get_product($product_id);
                    if ($product) :
                        $product_image = wp_get_attachment_image_src(get_post_thumbnail_id($product_id), 'medium');
                        $product_url = get_permalink($product_id);
                        
                        // ========== ПЕРСОНАЛИЗАЦИЯ ЦЕН ==========
                        $has_discount = false;
                        $discount_percent = 0;
                        $price_html = '';
                        
                        if ( is_user_logged_in() ) {
                            $user_id = get_current_user_id();
                            
                            if ( function_exists( 'asker_get_total_discount' ) ) {
                                $discount_percent = asker_get_total_discount( $user_id );
                            } else {
                                $level_discount = get_user_meta( $user_id, 'user_level_discount', true );
                                $individual_discount = get_user_meta( $user_id, 'individual_discount', true );
                                $discount_percent = max( floatval( $level_discount ), floatval( $individual_discount ) );
                            }
                            
                            if ( $discount_percent > 0 ) {
                                $has_discount = true;
                            }
                        }
                        
                        if ( $has_discount ) {
                            $regular_price = $product->get_regular_price();
                            $sale_price = $product->get_sale_price();
                            
                            if ( ! empty( $regular_price ) ) {
                                if ( ! empty( $sale_price ) ) {
                                    $discounted_price = $sale_price * ( 1 - $discount_percent / 100 );
                                    $price_html = '<div class="price-with-discount-home">';
                                    $price_html .= '<span class="original-price-home"><del>' . wc_price( $regular_price ) . '</del></span>';
                                    $price_html .= '<span class="personal-price-home">' . wc_price( $discounted_price ) . '</span>';
                                    $price_html .= '<span class="discount-label-home">-' . esc_html( $discount_percent ) . '%</span>';
                                    $price_html .= '</div>';
                                } else {
                                    $discounted_price = $regular_price * ( 1 - $discount_percent / 100 );
                                    $price_html = '<div class="price-with-discount-home">';
                                    $price_html .= '<span class="original-price-home"><del>' . wc_price( $regular_price ) . '</del></span>';
                                    $price_html .= '<span class="personal-price-home">' . wc_price( $discounted_price ) . '</span>';
                                    $price_html .= '<span class="discount-label-home">-' . esc_html( $discount_percent ) . '%</span>';
                                    $price_html .= '</div>';
                                }
                            } else {
                                $price_html = $product->get_price_html();
                            }
                        } else {
                            $price_html = $product->get_price_html();
                        }
                        
                        $price_html = preg_replace( '/,00/', '', $price_html );
                        // ========== КОНЕЦ ПЕРСОНАЛИЗАЦИИ ==========
            ?>
                <div class="product-card">
                    <button class="favorite-btn" data-product-id="<?php echo esc_attr($product_id); ?>"></button>
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
                        <div class="product-price"><?php echo $price_html; ?></div>
                        <?php
                        $cart_qty = 0;
                        if ( function_exists( 'WC' ) && WC()->cart ) {
                            foreach ( WC()->cart->get_cart() as $cart_item ) {
                                if ( $cart_item['product_id'] == $product_id ) {
                                    $cart_qty = $cart_item['quantity'];
                                    break;
                                }
                            }
                        }
                        $btn_class = 'btn-add-cart add_to_cart_button';
                        if ( $cart_qty > 0 ) {
                            $btn_class .= ' has-items';
                        }
                        ?>
                        <button class="<?php echo esc_attr( $btn_class ); ?>" data-product-id="<?php echo esc_attr($product_id); ?>">
                            <span class="btn-text">В корзину</span>
                            <span class="btn-cart-count" data-count="<?php echo esc_attr( $cart_qty ); ?>"><?php echo esc_html( $cart_qty ); ?></span>
                        </button>
                    </div>
                </div>
            <?php
                    endif;
                endforeach;
            else :
                echo '<p style="grid-column: 1/-1; text-align: center; color: #999;">Товары не настроены. Добавьте товары в поле "Популярные товары" на главной странице.</p>';
            endif;
            ?>
        </div>
    </div>
</div>
                    
                    <!-- Вкладка Профиль -->
                    <div class="tab-content" id="profile">
                        <div class="content-section">
                            <h2>Личные данные</h2>
                            
                            <?php
                            // Получаем уровень клиента для шкалы и итоговую скидку
                            $level_data = asker_get_customer_level( get_current_user_id() );
                            $total_discount_profile = function_exists( 'asker_get_total_discount' ) 
                                ? asker_get_total_discount( get_current_user_id() ) 
                                : $level_data['discount'];
                            $current_level = mb_strtolower( trim($level_data['level']), 'UTF-8' );
                            
                            // Определяем активный уровень для шкалы
                            // Базовый = Базовый, Серебро = Премиум, Золото/Платина = VIP
                            $active_bar_level = 'basic'; // По умолчанию
                            
                            // Проверяем на базовый уровень
                            if ( in_array($current_level, ['базовый', 'basic', 'base']) ) {
                                $active_bar_level = 'basic';
                            }
                            // Проверяем на премиум/серебро
                            elseif ( in_array($current_level, ['серебро', 'silver', 'премиум', 'premium']) ) {
                                $active_bar_level = 'premium';
                            }
                            // Проверяем на VIP/золото/платина
                            elseif ( in_array($current_level, ['золото', 'gold', 'платина', 'platinum', 'vip']) ) {
                                $active_bar_level = 'vip';
                            }
                            
                            // Отладка для администраторов
                            if ( current_user_can('administrator') && isset($_GET['debug_level']) ) {
                                echo '<!-- DEBUG: current_level = "' . esc_html($current_level) . '", active_bar_level = "' . esc_html($active_bar_level) . '" -->';
                            }
                            ?>
                            
                            <!-- Шкала уровней -->
                            <div class="privilege-level-wrapper">
                                <?php
                                // Видимый отладочный блок для администраторов
                                if ( current_user_can('administrator') && isset($_GET['debug_avatar']) ) {
                                    $user_id = get_current_user_id();
                                    $custom_avatar_id = get_user_meta( $user_id, 'custom_avatar', true );
                                    $avatar_url = get_user_meta( $user_id, 'custom_avatar_url', true );
                                    
                                    // Получаем все user_meta для проверки
                                    $all_meta = get_user_meta($user_id);
                                    
                                    echo '<div style="background: #fff3cd; border: 1px solid #ffc107; padding: 10px; margin-bottom: 20px; border-radius: 4px; font-family: monospace; font-size: 12px;">';
                                    echo '<strong>DEBUG INFO:</strong><br>';
                                    echo 'User ID: ' . $user_id . '<br>';
                                    echo 'Avatar ID (custom_avatar): ' . ($custom_avatar_id ? $custom_avatar_id : 'NULL') . '<br>';
                                    echo 'Avatar URL (custom_avatar_url): ' . ($avatar_url ? $avatar_url : 'NULL') . '<br>';
                                    
                                    if ($custom_avatar_id) {
                                        $attachment = get_post($custom_avatar_id);
                                        echo 'Attachment exists: ' . ($attachment ? 'YES' : 'NO') . '<br>';
                                        if ($attachment) {
                                            echo 'Attachment post_type: ' . $attachment->post_type . '<br>';
                                            echo 'Attachment URL (wp_get_attachment_url): ' . wp_get_attachment_url($custom_avatar_id) . '<br>';
                                            echo 'Attachment Image URL (thumbnail): ' . wp_get_attachment_image_url($custom_avatar_id, 'thumbnail') . '<br>';
                                        }
                                    }
                                    
                                    echo '<br><strong>All user_meta keys:</strong><br>';
                                    foreach ($all_meta as $key => $value) {
                                        if (strpos($key, 'custom_avatar') !== false || strpos($key, 'avatar') !== false) {
                                            echo $key . ': ' . (is_array($value) ? print_r($value[0], true) : $value) . '<br>';
                                        }
                                    }
                                    
                                    echo '</div>';
                                }
                                ?>
                                <div class="avatar-upload">
                                    <div class="avatar-placeholder" id="avatar-preview">
                                        <?php
                                        $user_id = get_current_user_id();
                                        
                                        // Получаем данные об аватаре
                                        $custom_avatar_id = get_user_meta( $user_id, 'custom_avatar', true );
                                        $avatar_url = get_user_meta( $user_id, 'custom_avatar_url', true );
                                        
                                        // Отладочный вывод для администраторов
                                        if ( current_user_can('administrator') ) {
                                            echo '<!-- DEBUG: User ID = ' . $user_id . ', Avatar ID = ' . ($custom_avatar_id ? $custom_avatar_id : 'NULL') . ', Avatar URL = ' . ($avatar_url ? $avatar_url : 'NULL') . ' -->';
                                        }
                                        
                                        // Если URL нет, пробуем получить из attachment ID
                                        if ( empty($avatar_url) && $custom_avatar_id && is_numeric($custom_avatar_id) ) {
                                            // Проверяем, существует ли attachment
                                            $attachment = get_post($custom_avatar_id);
                                            if ($attachment && $attachment->post_type === 'attachment') {
                                                // Пробуем получить thumbnail
                                                $avatar_url = wp_get_attachment_image_url( $custom_avatar_id, 'thumbnail' );
                                                
                                                // Если thumbnail не получился, пробуем полный размер
                                                if ( !$avatar_url ) {
                                                    $avatar_url = wp_get_attachment_url( $custom_avatar_id );
                                                }
                                                
                                                // Если получили URL, сохраняем для кеша
                                                if ( $avatar_url ) {
                                                    update_user_meta( $user_id, 'custom_avatar_url', $avatar_url );
                                                }
                                            } else {
                                                // Attachment не существует, удаляем из мета
                                                delete_user_meta( $user_id, 'custom_avatar' );
                                                delete_user_meta( $user_id, 'custom_avatar_url' );
                                                $avatar_url = '';
                                            }
                                        }
                                        
                                        if ( !empty($avatar_url) ) {
                                            // Добавляем cache-busting параметр
                                            $avatar_url_with_cache = $avatar_url . (strpos($avatar_url, '?') !== false ? '&' : '?') . 'v=' . time();
                                            echo '<img src="' . esc_url( $avatar_url_with_cache ) . '" alt="Аватар" id="avatar-image" style="width: 100%; height: 100%; object-fit: cover; border-radius: 50%;">';
                                        } else {
                                            // Плейсхолдер для аватара
                                            echo '<div class="avatar-placeholder-icon">';
                                            echo '<svg width="48" height="48" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">';
                                            echo '<path d="M12 12C14.7614 12 17 9.76142 17 7C17 4.23858 14.7614 2 12 2C9.23858 2 7 4.23858 7 7C7 9.76142 9.23858 12 12 12Z" fill="currentColor"/>';
                                            echo '<path d="M12 14C7.58172 14 4 17.5817 4 22H20C20 17.5817 16.4183 14 12 14Z" fill="currentColor"/>';
                                            echo '</svg>';
                                            echo '</div>';
                                        }
                                        ?>
                                    </div>
                                    <label for="avatar" class="avatar-upload-label">Изменить фото</label>
                                </div>
                                <div class="privilege-level-bar">
                                    <p class="privilege-level-label">Индивидуальная скидка: 
                                    <p class="privilege-discount">Скидка: <span class="discount-value"><?php echo esc_html( $total_discount_profile ); ?>%</span> от розничной цены</p>
                                </div>
                            </div>
                            
                            <div class="profile-form">
                                <form method="post" action="<?php echo esc_url(get_permalink()); ?>" enctype="multipart/form-data">
                                    <!-- Input для аватара должен быть внутри формы -->
                                    <input type="file" id="avatar" name="avatar" accept="image/*" style="display: none;">
                                    <?php wp_nonce_field('update_profile', 'profile_nonce'); ?>
                                    
                                    <div class="form-row">
                                    <div class="form-group">
                                            <label for="first_name">Имя<span class="required">*</span></label>
                                        <input type="text" id="first_name" name="first_name" value="<?php echo esc_attr(get_user_meta(get_current_user_id(), 'first_name', true)); ?>" required>
                                    </div>
                                    <div class="form-group">
                                        <label for="last_name">Фамилия</label>
                                        <input type="text" id="last_name" name="last_name" value="<?php echo esc_attr(get_user_meta(get_current_user_id(), 'last_name', true)); ?>">
                                    </div>
                                    </div>
                                    
                                    <div class="form-row">
                                    <div class="form-group">
                                        <label for="phone">Телефон</label>
                                        <input type="tel" id="phone" name="phone" value="<?php echo esc_attr(get_user_meta(get_current_user_id(), 'billing_phone', true)); ?>">
                                        </div>
                                        <div class="form-group">
                                            <label for="email">E-mail<span class="required">*</span></label>
                                            <input type="email" id="email" name="email" value="<?php echo esc_attr(wp_get_current_user()->user_email); ?>" required>
                                        </div>
                                    </div>
                                    
                                    <div class="form-row">
                                        <div class="form-group">
                                            <label for="company_name">Название компании</label>
                                            <input type="text" id="company_name" name="company_name" value="<?php echo esc_attr(get_user_meta(get_current_user_id(), 'billing_company', true)); ?>">
                                        </div>
                                        <div class="form-group">
                                            <label for="company_inn">ИНН компании</label>
                                            <input type="text" id="company_inn" name="company_inn" value="<?php echo esc_attr(get_user_meta(get_current_user_id(), 'company_inn', true)); ?>">
                                        </div>
                                    </div>
                                    
                                    <div class="form-checkbox">
                                        <label>
                                            <input type="checkbox" id="change_password" name="change_password" value="1">
                                            <span>Сменить пароль</span>
                                        </label>
                                    </div>
                                    
                                    <div class="password-fields" style="display: none;">
                                        <div class="form-row">
                                            <div class="form-group">
                                                <label for="new_password">Новый пароль<span class="required">*</span></label>
                                                <input type="password" id="new_password" name="new_password">
                                            </div>
                                            <div class="form-group">
                                                <label for="confirm_password">Повторите пароль<span class="required">*</span></label>
                                                <input type="password" id="confirm_password" name="confirm_password">
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="form-checkbox">
                                        <label>
                                            <input type="checkbox" id="consent" name="consent" value="1" checked>
                                            <span>Согласие на обработку персональных данных <a href="#" class="consent-link">Подробнее</a></span>
                                        </label>
                                    </div>
                                    
                                    <button type="submit" name="save_profile" class="btn-save">Сохранить изменения</button>
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
                    
<!-- Вкладка Избранное - ОБНОВЛЕННАЯ С ПЕРСОНАЛЬНЫМИ ЦЕНАМИ -->
<div class="tab-content" id="wishlist">
    <div class="content-section">
        <h2>Ваши избранные товары</h2>
        <p class="wishlist-subtitle">Мы можете добавить товары в корзину и оформить заказ</p>
        <div class="wishlist-products">
            <?php
            $customer_id = get_current_user_id();
            $wishlist_items = get_user_meta($customer_id, 'asker_wishlist', true);
            
            // Если в user_meta пусто, пытаемся синхронизировать с localStorage через JS
            if (empty($wishlist_items) || !is_array($wishlist_items)) {
                $wishlist_items = array();
            }
            
            // Пагинация для избранного
            $paged = isset($_GET['wishlist_page']) ? max(1, intval($_GET['wishlist_page'])) : 1;
            $per_page = 10;
            $total_items = count($wishlist_items);
            $total_pages = ceil($total_items / $per_page);
            $offset = ($paged - 1) * $per_page;
            $paged_items = array_slice($wishlist_items, $offset, $per_page);
            
            if (!empty($paged_items)) :
                ?>
                <div class="wishlist-list">
                    <?php foreach ($paged_items as $product_id) :
                        $product = wc_get_product($product_id);
                        if ($product && $product->is_visible()) :
                            $product_image = wp_get_attachment_image_src(get_post_thumbnail_id($product_id), 'medium');
                            $product_url = get_permalink($product_id);
                            $sku = $product->get_sku();
                            
                            // ========== ПЕРСОНАЛИЗАЦИЯ ЦЕН ==========
                            $has_discount = false;
                            $discount_percent = 0;
                            $price_html = '';
                            
                            // Проверяем авторизацию и скидку пользователя
                            if ( is_user_logged_in() ) {
                                $user_id = get_current_user_id();
                                
                                // Получаем скидку пользователя
                                if ( function_exists( 'asker_get_total_discount' ) ) {
                                    $discount_percent = asker_get_total_discount( $user_id );
                                } else {
                                    // Fallback: получаем напрямую из мета-полей
                                    $level_discount = get_user_meta( $user_id, 'user_level_discount', true );
                                    $individual_discount = get_user_meta( $user_id, 'individual_discount', true );
                                    $discount_percent = max( floatval( $level_discount ), floatval( $individual_discount ) );
                                }
                                
                                if ( $discount_percent > 0 ) {
                                    $has_discount = true;
                                }
                            }
                            
                            // Формируем HTML цены
                            if ( $has_discount ) {
                                $regular_price = $product->get_regular_price();
                                $sale_price = $product->get_sale_price();
                                
                                if ( ! empty( $regular_price ) ) {
                                    if ( ! empty( $sale_price ) ) {
                                        // Товар со скидкой + персональная скидка
                                        $discounted_price = $sale_price * ( 1 - $discount_percent / 100 );
                                        $price_html = '<div class="price-with-discount-account">';
                                        $price_html .= '<span class="original-price-account"><del>' . wc_price( $regular_price ) . '</del></span>';
                                        $price_html .= '<span class="personal-price-account">' . wc_price( $discounted_price ) . '</span>';
                                        $price_html .= '<span class="discount-label-account">-' . esc_html( $discount_percent ) . '%</span>';
                                        $price_html .= '</div>';
                                    } else {
                                        // Обычный товар + персональная скидка
                                        $discounted_price = $regular_price * ( 1 - $discount_percent / 100 );
                                        $price_html = '<div class="price-with-discount-account">';
                                        $price_html .= '<span class="original-price-account"><del>' . wc_price( $regular_price ) . '</del></span>';
                                        $price_html .= '<span class="personal-price-account">' . wc_price( $discounted_price ) . '</span>';
                                        $price_html .= '<span class="discount-label-account">-' . esc_html( $discount_percent ) . '%</span>';
                                        $price_html .= '</div>';
                                    }
                                } else {
                                    // На всякий случай, если цены нет
                                    $price_html = $product->get_price_html();
                                }
                            } else {
                                // Обычная цена без персональной скидки
                                $price_html = $product->get_price_html();
                            }
                            
                            // Убираем копейки из цены
                            $price_html = preg_replace( '/,00/', '', $price_html );
                            // ========== КОНЕЦ ПЕРСОНАЛИЗАЦИИ ==========
                            ?>
                            <div class="wishlist-item">
                                <a href="<?php echo esc_url($product_url); ?>" class="wishlist-item-image">
                                    <?php if ($product_image) : ?>
                                        <img src="<?php echo esc_url($product_image[0]); ?>" alt="">
                                    <?php else : ?>
                                        <div class="product-placeholder"></div>
                                    <?php endif; ?>
                                </a>
                                <div class="wishlist-item-info">
                                    <h3 class="wishlist-item-title">
                                        <a href="<?php echo esc_url($product_url); ?>"><?php echo esc_html($product->get_name()); ?></a>
                                    </h3>
                                    <?php if ($sku) : ?>
                                        <p class="wishlist-item-sku">Артикул: <?php echo esc_html($sku); ?></p>
                                    <?php endif; ?>
                                </div>
                                <div class="wishlist-item-price"><?php echo $price_html; ?></div>
                                <button class="wishlist-item-remove" data-product-id="<?php echo esc_attr($product_id); ?>" aria-label="Удалить из избранного">
                                    <svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path d="M12 4L4 12M4 4L12 12" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                    </svg>
                                </button>
                                <div class="wishlist-item-right">
                                    <div class="wishlist-item-quantity">
                                        <button class="quantity-btn quantity-minus" data-product-id="<?php echo esc_attr($product_id); ?>">-</button>
                                        <input type="number" class="quantity-input" value="1" min="1" data-product-id="<?php echo esc_attr($product_id); ?>">
                                        <button class="quantity-btn quantity-plus" data-product-id="<?php echo esc_attr($product_id); ?>">+</button>
                                    </div>
                                    <?php
                                    // Получаем количество этого товара в корзине
                                    $cart_qty = 0;
                                    if ( function_exists( 'WC' ) && WC()->cart ) {
                                        foreach ( WC()->cart->get_cart() as $cart_item ) {
                                            if ( $cart_item['product_id'] == $product_id ) {
                                                $cart_qty = $cart_item['quantity'];
                                                break;
                                            }
                                        }
                                    }
                                    $btn_class = 'wishlist-item-add-cart btn-add-cart add_to_cart_button';
                                    if ( $cart_qty > 0 ) {
                                        $btn_class .= ' has-items';
                                    }
                                    ?>
                                    <button class="<?php echo esc_attr( $btn_class ); ?>" data-product-id="<?php echo esc_attr($product_id); ?>"><span class="btn-text">В корзину</span><span class="btn-cart-count" data-count="<?php echo esc_attr( $cart_qty ); ?>"><?php echo esc_html( $cart_qty ); ?></span></button>
                                </div>
                            </div>
                        <?php
                        endif;
                    endforeach; ?>
                </div>
                
                <?php if ($total_pages > 1) : ?>
                <div class="wishlist-pagination">
                    <?php if ($paged > 1) : ?>
                        <a href="?wishlist_page=<?php echo $paged - 1; ?>#wishlist" class="pagination-btn pagination-btn--prev">
                            <svg width="16" height="16" viewBox="0 0 16 16" fill="none">
                                <path d="M10 12L6 8L10 4" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                            Предыдущая
                        </a>
                    <?php endif; ?>
                    
                    <div class="pagination-numbers">
                        <?php
                        // Показываем максимум 7 страниц
                        $range = 3;
                        $start = max(1, $paged - $range);
                        $end = min($total_pages, $paged + $range);
                        
                        if ($start > 1) {
                            echo '<a href="?wishlist_page=1#wishlist" class="page-number">1</a>';
                            if ($start > 2) echo '<span class="page-dots">...</span>';
                        }
                        
                        for ($i = $start; $i <= $end; $i++) :
                            if ($i === $paged) : ?>
                                <span class="page-number page-number--active"><?php echo $i; ?></span>
                            <?php else : ?>
                                <a href="?wishlist_page=<?php echo $i; ?>#wishlist" class="page-number"><?php echo $i; ?></a>
                            <?php endif;
                        endfor;
                        
                        if ($end < $total_pages) {
                            if ($end < $total_pages - 1) echo '<span class="page-dots">...</span>';
                            echo '<a href="?wishlist_page=' . $total_pages . '#wishlist" class="page-number">' . $total_pages . '</a>';
                        }
                        ?>
                    </div>
                    
                    <?php if ($paged < $total_pages) : ?>
                        <a href="?wishlist_page=<?php echo $paged + 1; ?>#wishlist" class="pagination-btn pagination-btn--next">
                            Следующая
                            <svg width="16" height="16" viewBox="0 0 16 16" fill="none">
                                <path d="M6 4L10 8L6 12" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </a>
                    <?php endif; ?>
                </div>
                <?php endif; ?>
            <?php else : ?>
                <div class="no-products">
                    <p>В вашем избранном пока нет товаров.</p>
                    <a href="<?php echo esc_url(home_url('/shop')); ?>" class="btn-primary">Перейти в каталог</a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<style>
/* Стили для персонализированных цен в избранном личного кабинета */
.wishlist-item-price .price-with-discount-account {
    display: flex;
    flex-direction: column;
    gap: 4px;
    align-items: flex-start;
}

.wishlist-item-price .price-with-discount-account .original-price-account {
    font-size: 14px;
    color: #9CA3AF;
    font-weight: 400;
}

.wishlist-item-price .price-with-discount-account .original-price-account del {
    text-decoration: line-through;
}

.wishlist-item-price .price-with-discount-account .personal-price-account {
    font-size: 20px;
    font-weight: 700;
    color: #059669;
}

.wishlist-item-price .price-with-discount-account .personal-price-account .woocommerce-Price-amount {
    color: #059669;
}

.wishlist-item-price .price-with-discount-account .discount-label-account {
    display: inline-block;
    background: linear-gradient(135deg, #059669 0%, #10B981 100%);
    color: white;
    padding: 2px 8px;
    border-radius: 12px;
    font-size: 12px;
    font-weight: 600;
    box-shadow: 0 1px 3px rgba(5, 150, 105, 0.2);
}

/* Адаптив */
@media (max-width: 768px) {
    .wishlist-item-price .price-with-discount-account .personal-price-account {
        font-size: 18px;
    }
    
    .wishlist-item-price .price-with-discount-account .original-price-account {
        font-size: 12px;
    }
}
</style>
        
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
                if (typeof updateWishlistCount === 'function') {
                    updateWishlistCount();
                }
            });
            </script>
    <?php else: ?>
        <?php
        // Обработка сброса пароля БЕЗ предварительной проверки ключа
        // Проверка ключа происходит при отправке формы в WooCommerce
        if ( isset( $_GET['key'] ) && isset( $_GET['login'] ) ) {
            // Показываем форму сброса пароля — проверка будет при сабмите
            $reset_key = sanitize_text_field( wp_unslash( $_GET['key'] ) );
            $reset_login = sanitize_text_field( wp_unslash( $_GET['login'] ) );
            
            wc_get_template( 'myaccount/form-reset-password.php', array(
                'key'   => $reset_key,
                'login' => $reset_login,
            ) );
        } elseif ( isset( $_GET['show-reset-form'] ) ) {
            // Переход через cookie
            $cookie_name = 'wp-resetpass-' . COOKIEHASH;
            
            if ( isset( $_COOKIE[ $cookie_name ] ) && strpos( $_COOKIE[ $cookie_name ], ':' ) !== false ) {
                $value = sanitize_text_field( wp_unslash( $_COOKIE[ $cookie_name ] ) );
                list( $reset_login, $reset_key ) = explode( ':', $value, 2 );
                
                // Показываем форму сброса пароля — проверка будет при сабмите
                wc_get_template( 'myaccount/form-reset-password.php', array(
                    'key'   => $reset_key,
                    'login' => $reset_login,
                ) );
            } else {
                wc_add_notice( 'Ссылка для сброса пароля недействительна или истекла. Запросите новую.', 'error' );
                wc_get_template( 'myaccount/form-lost-password.php' );
            }
        } elseif ( isset( $_GET['lost-password'] ) || isset( $_GET['reset-link-sent'] ) ) {
            // Форма запроса сброса пароля
            wc_get_template( 'myaccount/form-lost-password.php' );
        } elseif ( isset( $_GET['password-reset'] ) ) {
            // Пароль успешно сброшен
            wc_add_notice( 'Пароль успешно изменён. Теперь вы можете войти с новым паролем.', 'success' );
            wc_get_template( 'myaccount/form-login.php' );
        } else {
            // Стандартная форма входа
            wc_get_template( 'myaccount/form-login.php' );
        }
        ?>
    <?php endif; ?>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Показать/скрыть поля пароля
    const changePasswordCheckbox = document.getElementById('change_password');
    const passwordFields = document.querySelector('.password-fields');
    
    if (changePasswordCheckbox && passwordFields) {
        changePasswordCheckbox.addEventListener('change', function() {
            if (this.checked) {
                passwordFields.style.display = 'block';
                document.getElementById('new_password').required = true;
                document.getElementById('confirm_password').required = true;
            } else {
                passwordFields.style.display = 'none';
                document.getElementById('new_password').required = false;
                document.getElementById('confirm_password').required = false;
                document.getElementById('new_password').value = '';
                document.getElementById('confirm_password').value = '';
            }
        });
    }
    
    // Загрузка аватара
    const avatarInput = document.getElementById('avatar');
    const avatarPreview = document.getElementById('avatar-preview');
    const avatarLabel = document.querySelector('.avatar-upload-label');
    
    if (avatarInput && avatarPreview && avatarLabel) {
        avatarLabel.addEventListener('click', function(e) {
            e.preventDefault();
            avatarInput.click();
        });
        
        avatarInput.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                console.log('Avatar file selected:', file.name, file.size, file.type);
                const reader = new FileReader();
                reader.onload = function(e) {
                    avatarPreview.innerHTML = '<img src="' + e.target.result + '" alt="Аватар" id="avatar-image" style="width: 100%; height: 100%; object-fit: cover; border-radius: 50%;">';
                };
                reader.readAsDataURL(file);
            }
        });
    }
    
    // Проверка формы перед отправкой
    const profileForm = document.querySelector('.profile-form form');
    if (profileForm) {
        profileForm.addEventListener('submit', function(e) {
            const avatarFile = document.getElementById('avatar');
            console.log('=== FORM SUBMIT DEBUG ===');
            console.log('Avatar file element:', avatarFile);
            console.log('Avatar file files:', avatarFile ? avatarFile.files : 'NOT FOUND');
            console.log('Avatar file files length:', avatarFile ? avatarFile.files.length : 'NOT FOUND');
            
            if (avatarFile && avatarFile.files.length > 0) {
                console.log('Form submitting with avatar file:', avatarFile.files[0].name);
                console.log('Form enctype:', profileForm.enctype);
                console.log('Form method:', profileForm.method);
                console.log('Form action:', profileForm.action);
                
                // Проверяем, что input находится внутри формы
                console.log('Avatar file is inside form:', profileForm.contains(avatarFile));
                
                // Проверяем, что форма отправляется обычным способом (не через AJAX)
                const formData = new FormData(profileForm);
                console.log('FormData entries:');
                let hasAvatar = false;
                for (let pair of formData.entries()) {
                    console.log(pair[0] + ': ' + (pair[1] instanceof File ? pair[1].name + ' (' + pair[1].size + ' bytes)' : pair[1]));
                    if (pair[0] === 'avatar' && pair[1] instanceof File) {
                        hasAvatar = true;
                    }
                }
                console.log('Avatar file in FormData:', hasAvatar);
                
                // Если файл есть в FormData, но не отправляется, возможно форма отправляется через AJAX
                // В этом случае нужно предотвратить отправку и отправить форму обычным способом
                if (hasAvatar) {
                    console.log('Avatar file is in FormData - allowing normal form submission');
                } else {
                    console.error('Avatar file is NOT in FormData! This is a problem.');
                    // Не блокируем отправку, но логируем проблему
                }
            } else {
                console.log('No avatar file selected');
            }
            console.log('=== END FORM SUBMIT DEBUG ===');
        });
    }
    
});
</script>

<script>
// Глобальное переопределение alert() для подавления модальных окон от расширения браузера
// Это нужно делать ДО загрузки jQuery, чтобы перехватить все асинхронные вызовы
// Выполняем сразу при загрузке страницы, до всех других скриптов
(function() {
    // Сохраняем оригинальные функции
    const originalAlert = window.alert;
    const originalConsoleError = console.error;
    const originalConsoleWarn = console.warn;
    
    // Переопределяем alert() глобально для подавления ошибок от расширения браузера
    window.alert = function(message) {
        const messageStr = String(message || '');
        // Если сообщение об ошибке добавления в корзину - подавляем его
        if (messageStr.includes('Ошибка добавления') || 
            messageStr.includes('ошибка добавления') ||
            messageStr.includes('Error adding') ||
            messageStr.includes('error adding') ||
            messageStr.toLowerCase().includes('добавления товара в корзину') ||
            messageStr.toLowerCase().includes('добавления в корзину') ||
            messageStr.includes('installHook')) {
            // Тихо логируем вместо показа alert
            if (console && console.log) {
                console.log('⚠️ Alert suppressed:', messageStr);
            }
            return; // Не показываем alert
        }
        // Для других сообщений используем оригинальный alert
        if (originalAlert) {
            return originalAlert.apply(window, arguments);
        }
    };
    
    // Также переопределяем console.error глобально
    if (console && console.error) {
        console.error = function() {
            const args = Array.from(arguments);
            const message = args.map(arg => String(arg)).join(' ');
            // Если это ошибка от installHook.js или об ошибке добавления в корзину - подавляем
            if (message.includes('installHook') || 
                message.includes('Ошибка добавления') || 
                message.includes('ошибка добавления') ||
                message.toLowerCase().includes('добавления товара в корзину') ||
                message.toLowerCase().includes('добавления в корзину')) {
                // Тихо логируем вместо console.error
                if (console && console.log) {
                    console.log('⚠️ Console.error suppressed:', message);
                }
                return; // Не показываем ошибку
            }
            // Для других ошибок используем оригинальный console.error
            if (originalConsoleError) {
                return originalConsoleError.apply(console, arguments);
            }
        };
    }
    
    // Также переопределяем console.warn для полной защиты
    if (console && console.warn) {
        console.warn = function() {
            const args = Array.from(arguments);
            const message = args.map(arg => String(arg)).join(' ');
            // Подавляем предупреждения от installHook
            if (message.includes('installHook') || 
                message.includes('Ошибка добавления') || 
                message.includes('ошибка добавления')) {
                if (console && console.log) {
                    console.log('⚠️ Console.warn suppressed:', message);
                }
                return;
            }
            // Для других предупреждений используем оригинальный console.warn
            if (originalConsoleWarn) {
                return originalConsoleWarn.apply(console, arguments);
            }
        };
    }
})();

// Обработчики событий для личного кабинета
jQuery(document).ready(function($) {
    // ОБРАБОТЧИК КНОПОК КОЛИЧЕСТВА перенесён в main.js
    // Работает глобально для всех страниц

    // Удаление из избранного теперь обрабатывается в main.js глобально для всех страниц
    
    // Добавление в корзину теперь обрабатывается в main.js для всех кнопок, включая избранное
    // Убрали отдельный обработчик, чтобы избежать конфликтов
}); // Конец jQuery(document).ready
</script>

<?php if ($avatar_uploaded && !empty($new_avatar_url)): ?>
<script>
// Если аватар был загружен, обновляем превью без перезагрузки страницы
(function() {
    var avatarPreview = document.getElementById('avatar-preview');
    if (avatarPreview) {
        var avatarUrl = '<?php echo esc_js($new_avatar_url); ?>?v=' + new Date().getTime();
        avatarPreview.innerHTML = '<img src="' + avatarUrl + '" alt="Аватар" id="avatar-image" style="width: 100%; height: 100%; object-fit: cover; border-radius: 50%;">';
    }
})();
</script>


<?php endif; ?>

