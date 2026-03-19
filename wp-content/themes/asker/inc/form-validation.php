<?php
/**
 * Валидация форм (серверная)
 * Улучшенная валидация для WooCommerce checkout и других форм
 */

/**
 * Валидация пароля при регистрации WooCommerce
 * Требования: минимум 8 символов, цифры, заглавные буквы, спецсимволы
 */
function asker_validate_password_strength( $errors, $username, $email ) {
    // Получаем пароль из POST
    $password = isset( $_POST['password'] ) ? $_POST['password'] : '';
    
    if ( empty( $password ) ) {
        return $errors;
    }
    
    $password_errors = array();
    
    // Минимум 8 символов
    if ( strlen( $password ) < 8 ) {
        $password_errors[] = 'минимум 8 символов';
    }
    
    // Должна быть хотя бы одна цифра
    if ( ! preg_match( '/[0-9]/', $password ) ) {
        $password_errors[] = 'хотя бы одна цифра';
    }
    
    // Должна быть хотя бы одна заглавная буква
    if ( ! preg_match( '/[A-ZА-ЯЁ]/u', $password ) ) {
        $password_errors[] = 'хотя бы одна заглавная буква';
    }
    
    // Должен быть хотя бы один спецсимвол
    if ( ! preg_match( '/[!@#$%^&*()_+\-=\[\]{};\':"\\|,.<>\/?~`]/', $password ) ) {
        $password_errors[] = 'хотя бы один специальный символ (!@#$%^&* и др.)';
    }
    
    // Если есть ошибки - добавляем их
    if ( ! empty( $password_errors ) ) {
        $error_message = 'Пароль должен содержать: ' . implode( ', ', $password_errors ) . '.';
        $errors->add( 'weak_password', '<strong>Ненадёжный пароль.</strong> ' . $error_message );
    }
    
    return $errors;
}
add_filter( 'woocommerce_registration_errors', 'asker_validate_password_strength', 10, 3 );

/**
 * Также валидируем пароль при смене пароля в аккаунте
 */
function asker_validate_password_change( $errors, $user ) {
    if ( isset( $_POST['password_1'] ) && ! empty( $_POST['password_1'] ) ) {
        $password = $_POST['password_1'];
        $password_errors = array();
        
        // Минимум 8 символов
        if ( strlen( $password ) < 8 ) {
            $password_errors[] = 'минимум 8 символов';
        }
        
        // Должна быть хотя бы одна цифра
        if ( ! preg_match( '/[0-9]/', $password ) ) {
            $password_errors[] = 'хотя бы одна цифра';
        }
        
        // Должна быть хотя бы одна заглавная буква
        if ( ! preg_match( '/[A-ZА-ЯЁ]/u', $password ) ) {
            $password_errors[] = 'хотя бы одна заглавная буква';
        }
        
        // Должен быть хотя бы один спецсимвол
        if ( ! preg_match( '/[!@#$%^&*()_+\-=\[\]{};\':"\\|,.<>\/?~`]/', $password ) ) {
            $password_errors[] = 'хотя бы один специальный символ (!@#$%^&* и др.)';
        }
        
        // Если есть ошибки - добавляем их
        if ( ! empty( $password_errors ) ) {
            $error_message = 'Пароль должен содержать: ' . implode( ', ', $password_errors ) . '.';
            $errors->add( 'weak_password', '<strong>Ненадёжный пароль.</strong> ' . $error_message );
        }
    }
    
    return $errors;
}
add_filter( 'woocommerce_save_account_details_errors', 'asker_validate_password_change', 10, 2 );

/**
 * Валидация полей чекаута WooCommerce
 */
function asker_validate_checkout_fields( $data, $errors ) {
    // Валидация телефона
    if ( isset( $data['billing_phone'] ) && ! empty( $data['billing_phone'] ) ) {
        $phone = sanitize_text_field( $data['billing_phone'] );
        // Удаляем все символы кроме цифр, +, -, пробелов и скобок
        $phone_clean = preg_replace( '/[^\d\+\-\(\)\s]/', '', $phone );
        
        // Проверяем минимальную длину (10 цифр)
        $phone_digits = preg_replace( '/[^\d]/', '', $phone_clean );
        if ( strlen( $phone_digits ) < 10 ) {
            $errors->add( 'billing_phone', 'Номер телефона должен содержать минимум 10 цифр.' );
        }
    }
    
    // Валидация email
    if ( isset( $data['billing_email'] ) && ! empty( $data['billing_email'] ) ) {
        $email = sanitize_email( $data['billing_email'] );
        if ( ! is_email( $email ) ) {
            $errors->add( 'billing_email', 'Пожалуйста, введите корректный email адрес.' );
        }
    }
    
    // Валидация ИНН (если указан)
    if ( isset( $data['billing_tax_id'] ) && ! empty( $data['billing_tax_id'] ) ) {
        $inn = sanitize_text_field( $data['billing_tax_id'] );
        $inn_clean = preg_replace( '/[^\d]/', '', $inn );
        
        // ИНН должен быть 10 или 12 цифр
        if ( strlen( $inn_clean ) !== 10 && strlen( $inn_clean ) !== 12 ) {
            $errors->add( 'billing_tax_id', 'ИНН должен содержать 10 или 12 цифр.' );
        }
    }
    
    // Валидация полей доставки (если выбрана доставка)
    if ( isset( $data['delivery_type'] ) && $data['delivery_type'] === 'delivery' ) {
        if ( empty( $data['shipping_city'] ) ) {
            $errors->add( 'shipping_city', 'Пожалуйста, укажите город доставки.' );
        }
        
        if ( empty( $data['shipping_address_1'] ) ) {
            $errors->add( 'shipping_address_1', 'Пожалуйста, укажите улицу доставки.' );
        }
    }
    
    return $errors;
}
add_action( 'woocommerce_after_checkout_validation', 'asker_validate_checkout_fields', 10, 2 );

/**
 * Санитизация данных чекаута перед сохранением
 */
function asker_sanitize_checkout_fields( $data ) {
    if ( isset( $data['billing_phone'] ) ) {
        $data['billing_phone'] = sanitize_text_field( $data['billing_phone'] );
    }
    
    if ( isset( $data['billing_email'] ) ) {
        $data['billing_email'] = sanitize_email( $data['billing_email'] );
    }
    
    if ( isset( $data['billing_tax_id'] ) ) {
        $data['billing_tax_id'] = sanitize_text_field( $data['billing_tax_id'] );
    }
    
    if ( isset( $data['order_comments'] ) ) {
        $data['order_comments'] = sanitize_textarea_field( $data['order_comments'] );
    }
    
    return $data;
}
add_filter( 'woocommerce_checkout_posted_data', 'asker_sanitize_checkout_fields' );

/**
 * Добавляем HTML5 атрибуты валидации к полям чекаута
 */
function asker_add_checkout_field_attributes( $fields, $country = '' ) {
    // Телефон
    if ( isset( $fields['billing']['billing_phone'] ) ) {
        $fields['billing']['billing_phone']['input_class'][] = 'validate-phone';
        $fields['billing']['billing_phone']['custom_attributes']['pattern'] = '[\d\+\-\(\)\s]+';
        $fields['billing']['billing_phone']['custom_attributes']['minlength'] = '10';
    }
    
    // Email
    if ( isset( $fields['billing']['billing_email'] ) ) {
        $fields['billing']['billing_email']['input_class'][] = 'validate-email';
        $fields['billing']['billing_email']['custom_attributes']['type'] = 'email';
    }
    
    // ИНН
    if ( isset( $fields['billing']['billing_tax_id'] ) ) {
        $fields['billing']['billing_tax_id']['input_class'][] = 'validate-inn';
        $fields['billing']['billing_tax_id']['custom_attributes']['pattern'] = '[\d]+';
        $fields['billing']['billing_tax_id']['custom_attributes']['maxlength'] = '12';
    }
    
    return $fields;
}
add_filter( 'woocommerce_checkout_fields', 'asker_add_checkout_field_attributes', 10, 2 );

/**
 * Валидация Contact Form 7 (если используется)
 */
function asker_validate_cf7_form( $result, $tag ) {
    // Проверяем, что Contact Form 7 активен
    if ( ! class_exists( 'WPCF7_Validation' ) ) {
        return $result;
    }
    
    $name = $tag->name;
    // Используем безопасное получение данных через WPCF7
    $value = isset( $_POST[ $name ] ) ? sanitize_text_field( trim( $_POST[ $name ] ) ) : '';
    
    // Валидация телефона в CF7
    if ( $tag->basetype === 'tel' || strpos( $name, 'phone' ) !== false ) {
        $phone_clean = preg_replace( '/[^\d]/', '', $value );
        if ( ! empty( $value ) && strlen( $phone_clean ) < 10 ) {
            $result->invalidate( $tag, 'Номер телефона должен содержать минимум 10 цифр.' );
        }
    }
    
    // Валидация email в CF7
    if ( $tag->basetype === 'email' ) {
        if ( ! empty( $value ) && ! is_email( $value ) ) {
            $result->invalidate( $tag, 'Пожалуйста, введите корректный email адрес.' );
        }
    }
    
    return $result;
}
// Добавляем фильтры только если Contact Form 7 активен
if ( class_exists( 'WPCF7_ContactForm' ) ) {
    add_filter( 'wpcf7_validate_tel', 'asker_validate_cf7_form', 10, 2 );
    add_filter( 'wpcf7_validate_tel*', 'asker_validate_cf7_form', 10, 2 );
    add_filter( 'wpcf7_validate_email', 'asker_validate_cf7_form', 10, 2 );
    add_filter( 'wpcf7_validate_email*', 'asker_validate_cf7_form', 10, 2 );
}

/**
 * Переводим сообщения Contact Form 7 на русский
 */
function asker_cf7_translate_messages( $messages ) {
    $messages = array_merge( $messages, array(
        'mail_sent_ok' => array(
            'description' => 'Сообщение успешно отправлено',
            'default' => 'Спасибо! Ваше сообщение отправлено.',
        ),
        'mail_sent_ng' => array(
            'description' => 'Ошибка отправки',
            'default' => 'Произошла ошибка при отправке. Попробуйте позже.',
        ),
        'validation_error' => array(
            'description' => 'Ошибка валидации',
            'default' => 'Пожалуйста, проверьте заполненные поля.',
        ),
        'spam' => array(
            'description' => 'Спам',
            'default' => 'Сообщение отмечено как спам.',
        ),
        'accept_terms' => array(
            'description' => 'Согласие',
            'default' => 'Необходимо принять условия.',
        ),
        'invalid_required' => array(
            'description' => 'Обязательное поле',
            'default' => 'Это поле обязательно для заполнения.',
        ),
        'invalid_too_long' => array(
            'description' => 'Слишком длинное',
            'default' => 'Слишком длинное значение.',
        ),
        'invalid_too_short' => array(
            'description' => 'Слишком короткое',
            'default' => 'Слишком короткое значение.',
        ),
        'upload_failed' => array(
            'description' => 'Ошибка загрузки',
            'default' => 'Ошибка при загрузке файла.',
        ),
        'upload_file_type_invalid' => array(
            'description' => 'Неверный тип файла',
            'default' => 'Неверный тип файла.',
        ),
        'upload_file_too_large' => array(
            'description' => 'Файл слишком большой',
            'default' => 'Файл слишком большой.',
        ),
        'upload_failed_php_error' => array(
            'description' => 'PHP ошибка',
            'default' => 'Ошибка при загрузке файла.',
        ),
        'invalid_date' => array(
            'description' => 'Неверная дата',
            'default' => 'Неверный формат даты.',
        ),
        'date_too_early' => array(
            'description' => 'Слишком ранняя дата',
            'default' => 'Дата слишком ранняя.',
        ),
        'date_too_late' => array(
            'description' => 'Слишком поздняя дата',
            'default' => 'Дата слишком поздняя.',
        ),
        'invalid_number' => array(
            'description' => 'Неверное число',
            'default' => 'Неверный формат числа.',
        ),
        'number_too_small' => array(
            'description' => 'Число слишком маленькое',
            'default' => 'Число слишком маленькое.',
        ),
        'number_too_large' => array(
            'description' => 'Число слишком большое',
            'default' => 'Число слишком большое.',
        ),
        'quiz_answer_not_correct' => array(
            'description' => 'Неверный ответ',
            'default' => 'Неверный ответ.',
        ),
        'invalid_email' => array(
            'description' => 'Неверный email',
            'default' => 'Неверный адрес электронной почты.',
        ),
        'invalid_url' => array(
            'description' => 'Неверный URL',
            'default' => 'Неверный адрес URL.',
        ),
        'invalid_tel' => array(
            'description' => 'Неверный телефон',
            'default' => 'Неверный номер телефона.',
        ),
    ));
    
    return $messages;
}
add_filter( 'wpcf7_messages', 'asker_cf7_translate_messages' );

/**
 * Переводим сообщения CF7 через gettext
 */
function asker_cf7_gettext_messages( $translated, $text, $domain ) {
    if ( $domain !== 'contact-form-7' ) {
        return $translated;
    }
    
    $translations = array(
        'Thank you for your message. It has been sent.' => 'Спасибо! Ваше сообщение отправлено.',
        'There was an error trying to send your message. Please try again later.' => 'Произошла ошибка при отправке. Попробуйте позже.',
        'One or more fields have an error. Please check and try again.' => 'Пожалуйста, проверьте заполненные поля.',
        'There was an error trying to send your message.' => 'Ошибка при отправке сообщения.',
        'Please fill out this field.' => 'Это поле обязательно для заполнения.',
        'Please enter a valid email address.' => 'Введите корректный email адрес.',
        'Please enter a valid phone number.' => 'Введите корректный номер телефона.',
        'The field is too long.' => 'Слишком длинное значение.',
        'The field is too short.' => 'Слишком короткое значение.',
        'Sending ...' => 'Отправка...',
        'Submit' => 'Отправить',
        'Send' => 'Отправить',
    );
    
    if ( isset( $translations[ $text ] ) ) {
        return $translations[ $text ];
    }
    
    return $translated;
}
add_filter( 'gettext', 'asker_cf7_gettext_messages', 20, 3 );

/**
 * Переводим сообщения WooCommerce (регистрация, вход и т.д.)
 */
function asker_wc_gettext_messages( $translated, $text, $domain ) {
    if ( $domain !== 'woocommerce' ) {
        return $translated;
    }
    
    $translations = array(
        'An account is already registered with your email address. Please log in.' => 'Аккаунт с таким email уже существует. Пожалуйста, войдите.',
        'An account is already registered with your email address. <a href="%s">Please log in.</a>' => 'Аккаунт с таким email уже существует. <a href="%s">Пожалуйста, войдите.</a>',
        'Error: An account is already registered with your email address.' => 'Ошибка: Аккаунт с таким email уже существует.',
        'Please provide a valid email address.' => 'Пожалуйста, введите корректный email адрес.',
        'Please enter an account username.' => 'Пожалуйста, введите имя пользователя.',
        'Please enter an account password.' => 'Пожалуйста, введите пароль.',
        'Registration is disabled.' => 'Регистрация отключена.',
        'Lost your password?' => 'Забыли пароль?',
        'Username or email address' => 'Имя пользователя или Email',
        'Password' => 'Пароль',
        'Remember me' => 'Запомнить меня',
        'Log in' => 'Войти',
        'Register' => 'Зарегистрироваться',
    );
    
    if ( isset( $translations[ $text ] ) ) {
        return $translations[ $text ];
    }
    
    return $translated;
}
add_filter( 'gettext', 'asker_wc_gettext_messages', 20, 3 );

/**
 * Переводим сообщения CF7 при отправке формы (динамически)
 */
function asker_cf7_translate_response_message( $message, $status ) {
    $translations = array(
        'Thank you for your message. It has been sent.' => 'Спасибо! Ваше сообщение отправлено.',
        'There was an error trying to send your message. Please try again later.' => 'Произошла ошибка при отправке. Попробуйте позже.',
        'One or more fields have an error. Please check and try again.' => 'Пожалуйста, проверьте заполненные поля.',
        'There was an error trying to send your message.' => 'Ошибка при отправке сообщения.',
        'Please fill the required fields.' => 'Заполните обязательные поля.',
        'Sending ...' => 'Отправка...',
    );
    
    if ( isset( $translations[ $message ] ) ) {
        return $translations[ $message ];
    }
    
    return $message;
}
add_filter( 'wpcf7_display_message', 'asker_cf7_translate_response_message', 10, 2 );

/**
 * Логика регистрации (редирект, notices) перенесена в inc/registration.php
 * Теперь используется многошаговая регистрация с подтверждением email
 * @see inc/registration.php
 */

