<?php
/**
 * Логика регистрации пользователей
 * 
 * Флоу:
 * 1. Пользователь регистрируется (без пароля)
 * 2. Приходит письмо для подтверждения email
 * 3. Переходит по ссылке и устанавливает пароль
 * 4. Приходит письмо об ожидании верификации
 * 5. Админ получает уведомление
 * 6. Админ активирует через WP Approve User
 * 7. Пользователь получает письмо об активации
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * ========================================
 * ОТКЛЮЧЕНИЕ СТАНДАРТНЫХ ПИСЕМ WOOCOMMERCE
 * ========================================
 */

/**
 * Отключаем стандартное письмо WooCommerce "New Account" 
 * Мы отправляем своё кастомное письмо с ссылкой для установки пароля
 */
add_filter( 'woocommerce_email_enabled_customer_new_account', '__return_false' );

/**
 * ========================================
 * ВАЛИДАЦИЯ РЕГИСТРАЦИИ
 * ========================================
 */

/**
 * Проверяем, что email уникален (дополнительная проверка)
 */
function asker_validate_unique_email( $errors, $username, $email ) {
    // DEBUG: Проверяем, вызывается ли функция
    error_log( 'ASKER DEBUG: asker_validate_unique_email called with email: ' . $email );
    
    if ( email_exists( $email ) ) {
        error_log( 'ASKER DEBUG: Email exists! Adding error.' );
        $errors->add( 'email_exists', 'Аккаунт с таким email уже существует. <a href="' . esc_url( wc_get_page_permalink( 'myaccount' ) ) . '">Войдите</a> или используйте другой email.' );
    }
    return $errors;
}
add_filter( 'woocommerce_registration_errors', 'asker_validate_unique_email', 5, 3 );

/**
 * Валидация email перед созданием пользователя
 * Используем transient для передачи ошибки между запросами
 */
function asker_early_registration_validation() {
    // Проверяем, что это POST-запрос регистрации WooCommerce
    if ( ! isset( $_POST['register'] ) || ! isset( $_POST['email'] ) || ! isset( $_POST['woocommerce-register-nonce'] ) ) {
        return;
    }
    
    // Проверяем nonce
    if ( ! wp_verify_nonce( sanitize_key( $_POST['woocommerce-register-nonce'] ), 'woocommerce-register' ) ) {
        return;
    }
    
    $email = sanitize_email( $_POST['email'] );
    
    if ( email_exists( $email ) ) {
        // Сохраняем ошибку в transient (живёт 60 секунд)
        set_transient( 'asker_registration_error_' . md5( $email ), 'Аккаунт с таким email уже существует. Войдите или используйте другой email.', 60 );
        
        // Помечаем, что регистрация заблокирована
        $_POST['_registration_blocked'] = true;
        
        // Удаляем register чтобы WooCommerce не создавал пользователя
        unset( $_POST['register'] );
    }
}
add_action( 'wp_loaded', 'asker_early_registration_validation', 5 );

/**
 * Показываем ошибку регистрации из transient
 */
function asker_show_registration_error_from_transient() {
    if ( ! isset( $_POST['email'] ) || ! isset( $_POST['_registration_blocked'] ) ) {
        return;
    }
    
    $email = sanitize_email( $_POST['email'] );
    $error = get_transient( 'asker_registration_error_' . md5( $email ) );
    
    if ( $error ) {
        wc_add_notice( $error, 'error' );
        delete_transient( 'asker_registration_error_' . md5( $email ) );
        
        // Восстанавливаем POST register чтобы вкладка регистрации была активна
        $_POST['register'] = true;
    }
}
add_action( 'woocommerce_before_customer_login_form', 'asker_show_registration_error_from_transient', 1 );

/**
 * ========================================
 * ОБРАБОТКА РЕГИСТРАЦИИ
 * ========================================
 */

/**
 * Сохраняем тип клиента и данные компании при регистрации
 */
function asker_save_customer_type_on_registration( $customer_id, $new_customer_data, $password_generated ) {
    // Тип клиента (физ/юр лицо)
    $customer_type = isset( $_POST['customer_type'] ) ? sanitize_text_field( $_POST['customer_type'] ) : 'individual';
    update_user_meta( $customer_id, 'customer_type', $customer_type );
    
    // Телефон (обязателен для всех)
    if ( isset( $_POST['phone'] ) && ! empty( $_POST['phone'] ) ) {
        $phone = sanitize_text_field( $_POST['phone'] );
        update_user_meta( $customer_id, 'billing_phone', $phone );
    }
    
    // Для юр. лица сохраняем название компании и ИНН
    if ( $customer_type === 'legal_entity' ) {
        if ( isset( $_POST['company_name'] ) && ! empty( $_POST['company_name'] ) ) {
            $company_name = sanitize_text_field( $_POST['company_name'] );
            update_user_meta( $customer_id, 'company_name', $company_name );
            update_user_meta( $customer_id, 'billing_company', $company_name );
        }
        
        if ( isset( $_POST['company_inn'] ) && ! empty( $_POST['company_inn'] ) ) {
            $company_inn = sanitize_text_field( $_POST['company_inn'] );
            // Оставляем только цифры
            $company_inn = preg_replace( '/[^0-9]/', '', $company_inn );
            update_user_meta( $customer_id, 'company_inn', $company_inn );
            update_user_meta( $customer_id, 'billing_tax_id', $company_inn );
        }
    }
    
    // Имя менеджера, с которым клиент уже работает (необязательное поле)
    if ( isset( $_POST['known_manager_name'] ) && ! empty( $_POST['known_manager_name'] ) ) {
        update_user_meta( $customer_id, 'known_manager_name', sanitize_text_field( $_POST['known_manager_name'] ) );
    }
    
    // Помечаем email как неподтверждённый
    update_user_meta( $customer_id, 'email_verified', false );
    
    // Помечаем пользователя как неодобренного для WP Approve User плагина
    update_user_meta( $customer_id, 'wp-approve-user', false );
    
    // пароль ещё НЕ установлен
    update_user_meta( $customer_id, 'password_set', false );
    
    // Генерируем токен подтверждения email
    $token = asker_generate_email_verification_token( $customer_id );
    
    // Отправляем письмо подтверждения
    asker_send_email_verification( $customer_id, $token );
}
add_action( 'woocommerce_created_customer', 'asker_save_customer_type_on_registration', 5, 3 );

/**
 * Генерируем случайный пароль при регистрации (пользователь его не знает)
 * Настоящий пароль устанавливается после подтверждения email
 */
function asker_generate_random_password_on_registration( $customer_data ) {
    // Генерируем случайный пароль, который пользователь не знает
    $customer_data['user_pass'] = wp_generate_password( 24, true, true );
    return $customer_data;
}
add_filter( 'woocommerce_new_customer_data', 'asker_generate_random_password_on_registration', 1000, 1 );

/**
 * Отключаем автоматический вход после регистрации
 * Используем фильтр WooCommerce для предотвращения автологина
 */
function asker_disable_auto_login_after_registration( $value ) {
    // Возвращаем false — пользователь НЕ будет автоматически залогинен
    return false;
}
add_filter( 'woocommerce_registration_auth_new_customer', 'asker_disable_auto_login_after_registration', 9999 );

/**
 * Дополнительно: разлогиниваем и делаем редирект на страницу успеха
 */
function asker_force_logout_and_redirect_after_registration( $customer_id ) {
    // Очищаем куки авторизации
    wp_clear_auth_cookie();
    
    // Сбрасываем текущего пользователя
    wp_set_current_user( 0 );
    
    // Получаем URL страницы успеха
    $success_page = get_page_by_path( 'registration-success' );
    if ( $success_page ) {
        $redirect_url = get_permalink( $success_page->ID );
    } else {
        $redirect_url = home_url( '/registration-success/' );
    }
    
    // Принудительный редирект
    wp_safe_redirect( $redirect_url );
    exit;
}
add_action( 'woocommerce_created_customer', 'asker_force_logout_and_redirect_after_registration', 9999 );

/**
 * Редирект после регистрации на страницу успеха (fallback)
 */
function asker_registration_redirect_to_success( $redirect ) {
    // Получаем страницу успешной регистрации
    $success_page = get_page_by_path( 'registration-success' );
    if ( $success_page ) {
        return get_permalink( $success_page->ID );
    }
    
    // Fallback на кастомный URL
    return home_url( '/registration-success/' );
}
add_filter( 'woocommerce_registration_redirect', 'asker_registration_redirect_to_success', 100 );


/**
 * ========================================
 * ГЕНЕРАЦИЯ И ВЕРИФИКАЦИЯ ТОКЕНА
 * ========================================
 */

/**
 * Генерируем токен подтверждения email
 */
function asker_generate_email_verification_token( $user_id ) {
    $token = wp_generate_password( 32, false );
    $token_hash = hash( 'sha256', $token );
    
    // Сохраняем хеш токена и время создания
    update_user_meta( $user_id, 'email_verification_token', $token_hash );
    update_user_meta( $user_id, 'email_verification_token_time', time() );
    
    return $token;
}

/**
 * Проверяем токен подтверждения email
 */
function asker_verify_email_token( $user_id, $token ) {
    $stored_hash = get_user_meta( $user_id, 'email_verification_token', true );
    $token_time = get_user_meta( $user_id, 'email_verification_token_time', true );
    
    if ( ! $stored_hash || ! $token_time ) {
        return false;
    }
    
    // Проверяем не истёк ли токен (24 часа)
    if ( time() - $token_time > 24 * HOUR_IN_SECONDS ) {
        return false;
    }
    
    // Сравниваем хеши
    $token_hash = hash( 'sha256', $token );
    if ( ! hash_equals( $stored_hash, $token_hash ) ) {
        return false;
    }
    
    return true;
}

/**
 * Генерируем URL для подтверждения email и установки пароля
 */
function asker_get_set_password_url( $user_id, $token ) {
    $set_password_page = get_page_by_path( 'set-password' );
    $base_url = $set_password_page ? get_permalink( $set_password_page->ID ) : home_url( '/set-password/' );
    
    return add_query_arg( array(
        'key'  => $token,
        'uid'  => $user_id,
    ), $base_url );
}


/**
 * ========================================
 * УСТАНОВКА ПАРОЛЯ
 * ========================================
 */

/**
 * Обрабатываем запрос на установку пароля
 */
function asker_handle_set_password_request() {
    // Проверяем что это страница установки пароля
    if ( ! is_page( 'set-password' ) ) {
        return;
    }
    
    // Если это POST запрос - обрабатываем форму
    if ( $_SERVER['REQUEST_METHOD'] === 'POST' && isset( $_POST['asker_set_password_nonce'] ) ) {
        asker_process_set_password();
    }
}
add_action( 'template_redirect', 'asker_handle_set_password_request' );

/**
 * Обрабатываем установку пароля
 */
function asker_process_set_password() {
    // Проверяем nonce
    if ( ! wp_verify_nonce( $_POST['asker_set_password_nonce'], 'asker_set_password' ) ) {
        wc_add_notice( 'Ошибка безопасности. Попробуйте ещё раз.', 'error' );
        return;
    }
    
    $user_id = isset( $_POST['user_id'] ) ? absint( $_POST['user_id'] ) : 0;
    $token = isset( $_POST['token'] ) ? sanitize_text_field( $_POST['token'] ) : '';
    $password = isset( $_POST['password'] ) ? $_POST['password'] : '';
    $password_confirm = isset( $_POST['password_confirm'] ) ? $_POST['password_confirm'] : '';
    
    // Проверяем токен
    if ( ! asker_verify_email_token( $user_id, $token ) ) {
        wc_add_notice( 'Ссылка недействительна или истекла. Запросите новое письмо.', 'error' );
        return;
    }
    
    // Проверяем пароли
    if ( empty( $password ) ) {
        wc_add_notice( 'Пожалуйста, введите пароль.', 'error' );
        return;
    }
    
    if ( $password !== $password_confirm ) {
        wc_add_notice( 'Пароли не совпадают.', 'error' );
        return;
    }
    
    // Валидируем пароль
    $password_errors = asker_validate_password( $password );
    if ( ! empty( $password_errors ) ) {
        wc_add_notice( 'Пароль должен содержать: ' . implode( ', ', $password_errors ) . '.', 'error' );
        return;
    }
    
    // Устанавливаем пароль
    wp_set_password( $password, $user_id );
    
    // Помечаем email как подтверждённый
    update_user_meta( $user_id, 'email_verified', true );
    
    // Помечаем пароль как подтверждённый
    update_user_meta( $user_id, 'password_set', true );
    
    // Удаляем токен
    delete_user_meta( $user_id, 'email_verification_token' );
    delete_user_meta( $user_id, 'email_verification_token_time' );
    
    // Отправляем письмо об ожидании верификации
    asker_send_awaiting_verification_email( $user_id );
    
    // Отправляем уведомление админу
    asker_notify_admin_new_registration( $user_id );
    
    // Редирект на страницу успеха
    $success_page = get_page_by_path( 'password-success' );
    
    $redirect_url = $success_page
        ? get_permalink( $success_page->ID )
        : home_url( '/password-success/' );
    
    wp_safe_redirect( $redirect_url );
    exit;
}

/**
 * Валидация пароля
 */
function asker_validate_password( $password ) {
    $errors = array();
    
    if ( strlen( $password ) < 8 ) {
        $errors[] = 'минимум 8 символов';
    }
    
    if ( ! preg_match( '/[0-9]/', $password ) ) {
        $errors[] = 'хотя бы одна цифра';
    }
    
        // Только латинские буквы и цифры
    if ( ! preg_match( '/^[A-Za-z0-9]+$/', $password ) ) {
        $errors[] = 'только латинские буквы и цифры';
    }
    
    return $errors;
}


/**
 * ========================================
 * ОТПРАВКА EMAIL
 * ========================================
 */

/**
 * Отправляем письмо подтверждения email
 */
function asker_send_email_verification( $user_id, $token ) {
    $user = get_user_by( 'id', $user_id );
    if ( ! $user ) {
        return false;
    }
    
    $set_password_url = asker_get_set_password_url( $user_id, $token );
    $first_name = get_user_meta( $user_id, 'first_name', true );
    $customer_type = get_user_meta( $user_id, 'customer_type', true );
    
    // Получаем имя для приветствия
    $greeting_name = ! empty( $first_name ) ? $first_name : 'клиент';
    
    // Получаем данные менеджера (если назначен)
    $manager_id = get_user_meta( $user_id, 'assigned_manager_id', true );
    $manager_name = '';
    $manager_phone = '';
    
    if ( $manager_id ) {
        $manager_post = get_post( $manager_id );
        if ( $manager_post ) {
            $manager_name = $manager_post->post_title;
            $manager_phone = get_field( 'manager_phone', $manager_id );
        }
    }
    
    // Контакты по умолчанию если менеджер не назначен
    if ( empty( $manager_name ) ) {
        $manager_name = 'Отдел продаж';
        $manager_phone = '+7 (931) 109 94 76';
    }
    
    $subject = 'Спасибо за регистрацию на портале компании Asker!';
    
    // Формируем HTML письмо
    $message = asker_get_email_template( 'verification', array(
        'greeting_name'    => $greeting_name,
        'set_password_url' => $set_password_url,
        'customer_type'    => $customer_type,
        'manager_name'     => $manager_name,
        'manager_phone'    => $manager_phone,
    ) );
    
    $headers = array( 'Content-Type: text/html; charset=UTF-8' );
    
    return wp_mail( $user->user_email, $subject, $message, $headers );
}

/**
 * Отправляем письмо об ожидании верификации
 */
function asker_send_awaiting_verification_email( $user_id ) {
    $user = get_user_by( 'id', $user_id );
    if ( ! $user ) {
        return false;
    }
    
    $first_name = get_user_meta( $user_id, 'first_name', true );
    $greeting_name = ! empty( $first_name ) ? $first_name : 'клиент';
    
    // Получаем данные менеджера (если назначен)
    $manager_id = get_user_meta( $user_id, 'assigned_manager_id', true );
    $manager_name = '';
    $manager_phone = '';
    $manager_email = '';
    
    if ( $manager_id ) {
        $manager_post = get_post( $manager_id );
        if ( $manager_post ) {
            $manager_name = $manager_post->post_title;
            $manager_phone = get_field( 'manager_phone', $manager_id );
            $manager_email = get_field( 'manager_email', $manager_id );
        }
    }
    
    // Контакты по умолчанию
    if ( empty( $manager_name ) ) {
        $manager_name = 'Отдел продаж Asker Parts';
        $manager_phone = '+7 (931) 109 94 76';
        $manager_email = 'sales@asker-corp.ru';
    }
    
    $subject = 'Ваш аккаунт на проверке — Asker Parts';
    
    $message = asker_get_email_template( 'awaiting_verification', array(
        'greeting_name'  => $greeting_name,
        'manager_name'   => $manager_name,
        'manager_phone'  => $manager_phone,
        'manager_email'  => $manager_email,
    ) );
    
    $headers = array( 'Content-Type: text/html; charset=UTF-8' );
    
    return wp_mail( $user->user_email, $subject, $message, $headers );
}

/**
 * Уведомление админу о новой регистрации
 */
function asker_notify_admin_new_registration( $user_id ) {
    $user = get_user_by( 'id', $user_id );
    if ( ! $user ) {
        return false;
    }
    
    $admin_email = get_option( 'admin_email' );
    $first_name = get_user_meta( $user_id, 'first_name', true );
    $customer_type = get_user_meta( $user_id, 'customer_type', true );
    $company_name = get_user_meta( $user_id, 'company_name', true );
    $company_inn = get_user_meta( $user_id, 'company_inn', true );
    
    $customer_type_label = $customer_type === 'legal_entity' ? 'Юридическое лицо' : 'Физическое лицо';
    $known_manager = get_user_meta( $user_id, 'known_manager_name', true );
    
    $subject = 'Новая регистрация — требуется активация';
    
    $message = asker_get_email_template( 'admin_new_registration', array(
        'user_email'          => $user->user_email,
        'first_name'          => $first_name,
        'customer_type_label' => $customer_type_label,
        'company_name'        => $company_name,
        'company_inn'         => $company_inn,
        'known_manager_name'  => $known_manager,
        'user_id'             => $user_id,
        'admin_url'           => admin_url( 'users.php' ),
    ) );
    
    $headers = array( 'Content-Type: text/html; charset=UTF-8' );
    
    return wp_mail( $admin_email, $subject, $message, $headers );
}






/**
 * ОТЛАДКА: что возвращает наша функция проверки
 */
function asker_debug_block_result( $user, $password ) {
    if ( is_wp_error( $user ) ) {
        error_log( '=== BLOCK CHECK RESULT ===' );
        error_log( 'Returned WP_Error' );
        error_log( 'Error code: ' . $user->get_error_code() );
        error_log( 'Error message: ' . $user->get_error_message() );
        error_log( '==========================' );
    }
    return $user;
}
add_filter( 'wp_authenticate_user', 'asker_debug_block_result', 999, 2 );



/**
 * ========================================
 * БЛОКИРОВКА ВХОДА
 * ========================================
 */

/**
 * Блокируем вход если email не подтверждён или аккаунт не одобрен
 * 
 * Логика:
 * - Если мета email_verified не существует — пропускаем (старый пользователь)
 * - Если email не подтверждён — показываем сообщение про письмо
 * - Если email подтверждён, но аккаунт не одобрен — показываем сообщение про ожидание
 */
function asker_block_login_if_email_not_verified( $user, $password ) {
    if ( is_wp_error( $user ) ) {
        return $user;
    }
    
    // Проверяем, существует ли мета email_verified вообще
    $meta_exists = metadata_exists( 'user', $user->ID, 'email_verified' );
    
    // Если мета не существует — это старый пользователь, пропускаем
    if ( ! $meta_exists ) {
        return $user;
    }
    
    $email_verified = get_user_meta( $user->ID, 'email_verified', true );
    $is_approved = get_user_meta( $user->ID, 'wp-approve-user', true );
    
    // Проверяем email: блокируем если НЕ подтверждён
    // Считаем подтверждённым: true, 1, '1', 'true'
    $email_is_verified = ( 
        $email_verified === true || 
        $email_verified === 1 || 
        $email_verified === '1' || 
        $email_verified === 'true' 
    );
    
    if ( ! $email_is_verified ) {
        return new WP_Error(
            'email_not_verified',
            'Проверьте почту и перейдите по ссылке из письма для установки пароля.'
        );
    }
    
    // Проверяем одобрение: блокируем если НЕ одобрен
    // WP Approve User может использовать: true, 1, '1', 'true', 'approved'
    // Считаем одобренным: true, 1, '1', 'true', 'approved'
    $user_is_approved = ( 
        $is_approved === true || 
        $is_approved === 1 || 
        $is_approved === '1' || 
        $is_approved === 'true' ||
        $is_approved === 'approved'
    );
    
    if ( ! $user_is_approved ) {
        return new WP_Error(
            'account_not_approved',
            'Ваш аккаунт проходит проверку. Вы получите письмо, когда доступ будет открыт.'
        );
    }
    
    return $user;
}

add_filter( 'wp_authenticate_user', 'asker_block_login_if_email_not_verified', 10, 2 );

/**
 * ФИНАЛЬНАЯ ЗАМЕНА: перехватываем сообщения прямо перед показом формы
 */
function asker_replace_wc_login_errors_final() {
    // Получаем все ошибки WooCommerce
    $error_notices = wc_get_notices( 'error' );
    
    if ( empty( $error_notices ) ) {
        return;
    }
    
    error_log( '=== FINAL ERROR REPLACEMENT ===' );
    error_log( 'Found errors: ' . print_r( $error_notices, true ) );
    
    // Проверяем был ли POST запрос входа
    $is_login_attempt = isset( $_POST['login'] ) && isset( $_POST['username'] );
    
    if ( ! $is_login_attempt ) {
        error_log( 'Not a login attempt, skipping' );
        return;
    }
    
    // Получаем username из POST
    $username = sanitize_user( $_POST['username'] );
    
    // Пытаемся найти пользователя
    $user = false;
    if ( is_email( $username ) ) {
        $user = get_user_by( 'email', $username );
    } else {
        $user = get_user_by( 'login', $username );
    }
    
    // Если пользователь не найден - оставляем стандартное сообщение
    if ( ! $user ) {
        error_log( 'User not found, keeping standard message' );
        return;
    }
    
    error_log( 'User found: ' . $user->ID );
    
    // Проверяем статус пользователя
    $email_verified = get_user_meta( $user->ID, 'email_verified', true );
    $is_approved = get_user_meta( $user->ID, 'wp-approve-user', true );
    
    error_log( 'email_verified: ' . var_export( $email_verified, true ) );
    error_log( 'wp-approve-user: ' . var_export( $is_approved, true ) );
    
    // Проверяем подтверждён ли email
    $email_is_verified = ( 
        $email_verified === true || 
        $email_verified === 1 || 
        $email_verified === '1' || 
        $email_verified === 'true' 
    );
    
    // Проверяем одобрен ли пользователь
    $user_is_approved = ( 
        $is_approved === true || 
        $is_approved === 1 || 
        $is_approved === '1' || 
        $is_approved === 'true' ||
        $is_approved === 'approved'
    );
    
    // Если email подтверждён, но пользователь не одобрен - показываем сообщение об ожидании
    if ( $email_is_verified && ! $user_is_approved ) {
        error_log( 'Email verified but not approved - replacing message' );
        wc_clear_notices();
        wc_add_notice( 
            '<strong>Внимание:</strong> Ваш аккаунт проходит проверку. Вы получите письмо, когда доступ будет открыт.', 
            'error' 
        );
    }
    // Если email не подтверждён - показываем сообщение о подтверждении
    elseif ( ! $email_is_verified ) {
        error_log( 'Email not verified - replacing message' );
        wc_clear_notices();
        wc_add_notice( 
            '<strong>Внимание:</strong> Проверьте почту и перейдите по ссылке из письма для установки пароля.', 
            'error' 
        );
    }
    // Иначе - это действительно неправильный пароль, оставляем стандартное сообщение
    else {
        error_log( 'User is verified and approved - keeping standard error (wrong password)' );
    }
    
    error_log( '=================================' );
}
add_action( 'woocommerce_before_customer_login_form', 'asker_replace_wc_login_errors_final', 999 );


/**
 * ========================================
 * ВАЛИДАЦИЯ ФОРМЫ РЕГИСТРАЦИИ
 * ========================================
 */

/**
 * Валидируем поля компании при регистрации
 */
function asker_validate_company_fields( $errors, $username, $email ) {
    $customer_type = isset( $_POST['customer_type'] ) ? sanitize_text_field( $_POST['customer_type'] ) : 'individual';
    
// Проверяем телефон (обязателен для всех)
    if ( empty( $_POST['phone'] ) ) {
        $errors->add( 'phone_required', 'Пожалуйста, укажите номер телефона.' );
    } else {
        $phone = preg_replace( '/[^0-9+]/', '', $_POST['phone'] );
        if ( strlen( $phone ) < 10 ) {
            $errors->add( 'phone_invalid', 'Укажите корректный номер телефона.' );
        }
    }
    
    if ( $customer_type === 'legal_entity' ) {
        // Проверяем название компании
        if ( empty( $_POST['company_name'] ) ) {
            $errors->add( 'company_name_required', 'Пожалуйста, укажите название компании.' );
        }
        
        // Проверяем ИНН
        if ( empty( $_POST['company_inn'] ) ) {
            $errors->add( 'company_inn_required', 'Пожалуйста, укажите ИНН компании.' );
        } else {
            $inn = preg_replace( '/[^0-9]/', '', $_POST['company_inn'] );
            if ( strlen( $inn ) !== 10 && strlen( $inn ) !== 12 ) {
                $errors->add( 'company_inn_invalid', 'ИНН должен содержать 10 или 12 цифр.' );
            }
        }
    }
    
    return $errors;
}
add_filter( 'woocommerce_registration_errors', 'asker_validate_company_fields', 10, 3 );

/**
 * Отключаем валидацию пароля при регистрации (пароль генерируется автоматически)
 */
function asker_disable_password_validation( $errors, $username, $email ) {
    // Удаляем ошибки связанные с паролем
    if ( isset( $errors->errors['weak_password'] ) ) {
        unset( $errors->errors['weak_password'] );
    }
    
    return $errors;
}
add_filter( 'woocommerce_registration_errors', 'asker_disable_password_validation', 100, 3 );


/**
 * ========================================
 * ПЕРЕОТПРАВКА ПИСЬМА ПОДТВЕРЖДЕНИЯ
 * ========================================
 */

/**
 * AJAX обработчик для переотправки письма подтверждения
 */
function asker_resend_verification_email() {
    check_ajax_referer( 'asker_resend_verification', 'nonce' );
    
    $email = isset( $_POST['email'] ) ? sanitize_email( $_POST['email'] ) : '';
    
    if ( empty( $email ) ) {
        wp_send_json_error( array( 'message' => 'Укажите email адрес.' ) );
    }
    
    $user = get_user_by( 'email', $email );
    
    if ( ! $user ) {
        wp_send_json_error( array( 'message' => 'Пользователь с таким email не найден.' ) );
    }
    
    $email_verified = get_user_meta( $user->ID, 'email_verified', true );
    
    if ( $email_verified === true || $email_verified === '1' ) {
        wp_send_json_error( array( 'message' => 'Email уже подтверждён. Попробуйте войти.' ) );
    }
    
    // Генерируем новый токен
    $token = asker_generate_email_verification_token( $user->ID );
    
    // Отправляем письмо
    $sent = asker_send_email_verification( $user->ID, $token );
    
    if ( $sent ) {
        wp_send_json_success( array( 'message' => 'Письмо отправлено! Проверьте почту.' ) );
    } else {
        wp_send_json_error( array( 'message' => 'Не удалось отправить письмо. Попробуйте позже.' ) );
    }
}
add_action( 'wp_ajax_nopriv_asker_resend_verification', 'asker_resend_verification_email' );
add_action( 'wp_ajax_asker_resend_verification', 'asker_resend_verification_email' );



/**
 * Запрещаем активацию пользователя, если пароль ещё не установлен
 */
function asker_block_approval_if_password_not_set( $user_id ) {
    $password_set = get_user_meta( $user_id, 'password_set', true );

    if ( ! $password_set ) {
        // Отменяем одобрение
        update_user_meta( $user_id, 'wp-approve-user', false );

        // Сообщение админу
        wp_die(
            'Нельзя активировать аккаунт: пользователь ещё не установил пароль.',
            'Ошибка активации',
            array( 'response' => 403 )
        );
    }
}
add_action( 'wpau_approve', 'asker_block_approval_if_password_not_set', 5 );

function asker_add_password_set_column( $columns ) {
    $columns['password_set'] = 'Пароль задан';
    return $columns;
}
add_filter( 'manage_users_columns', 'asker_add_password_set_column' );

function asker_render_password_set_column( $value, $column_name, $user_id ) {
    if ( $column_name === 'password_set' ) {
        $password_set = get_user_meta( $user_id, 'password_set', true );

        if ( $password_set ) {
            return '✅ Да';
        } else {
            return '❌ Нет';
        }
    }

    return $value;
}
add_filter( 'manage_users_custom_column', 'asker_render_password_set_column', 10, 3 );


/**
 * ========================================
 * ИНТЕГРАЦИЯ С WP APPROVE USER
 * ========================================
 */

/**
 * Отправляем кастомное письмо при одобрении пользователя через WP Approve User
 * Хук wpau_approve вызывается плагином при активации пользователя
 */
function asker_send_approved_email( $user_id ) {
    $user = get_user_by( 'id', $user_id );
    if ( ! $user ) {
        return;
    }
    
    // Помечаем email как подтверждённый (чтобы пользователь мог войти)
    update_user_meta( $user_id, 'email_verified', true );
    
    $first_name = get_user_meta( $user_id, 'first_name', true );
    $greeting_name = ! empty( $first_name ) ? $first_name : 'клиент';
    
    $subject = 'Ваш аккаунт активирован — Asker Parts';
    
    $message = asker_get_email_template( 'account_approved', array(
        'greeting_name' => $greeting_name,
        'login_url'     => wc_get_page_permalink( 'myaccount' ),
    ) );
    
    $headers = array( 'Content-Type: text/html; charset=UTF-8' );
    
    wp_mail( $user->user_email, $subject, $message, $headers );
}
add_action( 'wpau_approve', 'asker_send_approved_email', 20 );


/**
 * ========================================
 * КОЛОНКИ В ТАБЛИЦЕ ПОЛЬЗОВАТЕЛЕЙ (АДМИНКА)
 * ========================================
 */

/**
 * Добавляем колонки «Компания» и «ИНН» в список пользователей
 */
function asker_add_company_columns( $columns ) {
    $new_columns = array();
    
    foreach ( $columns as $key => $value ) {
        $new_columns[ $key ] = $value;
        if ( $key === 'email' ) {
            $new_columns['company_name_col']  = 'Компания';
            $new_columns['company_inn_col']   = 'ИНН';
            $new_columns['known_manager_col'] = 'Указ. менеджер';
        }
    }
    
    return $new_columns;
}
add_filter( 'manage_users_columns', 'asker_add_company_columns' );

function asker_show_company_columns( $value, $column_name, $user_id ) {
    switch ( $column_name ) {
        case 'company_name_col':
            $company = get_user_meta( $user_id, 'company_name', true );
            return $company ? esc_html( $company ) : '—';
        case 'company_inn_col':
            $inn = get_user_meta( $user_id, 'company_inn', true );
            return $inn ? esc_html( $inn ) : '—';
        case 'known_manager_col':
            $known = get_user_meta( $user_id, 'known_manager_name', true );
            return $known ? esc_html( $known ) : '—';
    }
    return $value;
}
add_filter( 'manage_users_custom_column', 'asker_show_company_columns', 10, 3 );


/**
 * ========================================
 * ПОЛЯ В ПРОФИЛЕ ПОЛЬЗОВАТЕЛЯ (АДМИНКА)
 * ========================================
 */

/**
 * Показываем «Указанный менеджер при регистрации» в профиле пользователя
 */
function asker_show_known_manager_in_profile( $user ) {
    $known_manager = get_user_meta( $user->ID, 'known_manager_name', true );
    if ( ! $known_manager ) {
        return;
    }
    ?>
    <h3>Информация при регистрации</h3>
    <table class="form-table">
        <tr>
            <th><label>Указанный менеджер</label></th>
            <td>
                <p><?php echo esc_html( $known_manager ); ?></p>
                <p class="description">Клиент указал это имя при регистрации в поле «Если вы уже работаете с нами».</p>
            </td>
        </tr>
    </table>
    <?php
}
add_action( 'show_user_profile', 'asker_show_known_manager_in_profile' );
add_action( 'edit_user_profile', 'asker_show_known_manager_in_profile' );

