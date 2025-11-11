<?php
/**
 * Защита форм от спама
 * Honeypot поля и rate limiting для форм
 */

/**
 * Добавляем honeypot поле к формам WooCommerce checkout
 */
function asker_add_checkout_honeypot() {
    if ( ! is_checkout() ) {
        return;
    }
    ?>
    <div style="position: absolute; left: -9999px; opacity: 0; pointer-events: none;" aria-hidden="true">
        <label for="asker_website_url">Website URL</label>
        <input type="text" id="asker_website_url" name="asker_website_url" value="" autocomplete="off" tabindex="-1">
    </div>
    <?php
}
add_action( 'woocommerce_checkout_before_customer_details', 'asker_add_checkout_honeypot' );

/**
 * Проверяем honeypot поле при отправке чекаута
 */
function asker_validate_checkout_honeypot( $data, $errors ) {
    // Если honeypot поле заполнено - это бот
    if ( isset( $_POST['asker_website_url'] ) && ! empty( $_POST['asker_website_url'] ) ) {
        $errors->add( 'spam', 'Обнаружена подозрительная активность. Пожалуйста, попробуйте еще раз.' );
    }
    
    return $errors;
}
add_action( 'woocommerce_after_checkout_validation', 'asker_validate_checkout_honeypot', 5, 2 );

/**
 * Rate limiting для чекаута (максимум 3 попытки за 5 минут с одного IP)
 */
function asker_checkout_rate_limit( $data, $errors ) {
    $ip_address = asker_get_client_ip();
    $transient_key = 'asker_checkout_attempts_' . md5( $ip_address );
    $attempts = get_transient( $transient_key );
    
    if ( $attempts === false ) {
        // Первая попытка
        set_transient( $transient_key, 1, 5 * MINUTE_IN_SECONDS );
    } elseif ( $attempts >= 3 ) {
        // Превышен лимит попыток
        $errors->add( 'rate_limit', 'Слишком много попыток оформления заказа. Пожалуйста, подождите несколько минут и попробуйте снова.' );
    } else {
        // Увеличиваем счетчик
        set_transient( $transient_key, $attempts + 1, 5 * MINUTE_IN_SECONDS );
    }
    
    return $errors;
}
add_action( 'woocommerce_after_checkout_validation', 'asker_checkout_rate_limit', 1, 2 );

/**
 * Сбрасываем счетчик попыток при успешном заказе
 */
function asker_reset_checkout_rate_limit( $order_id ) {
    $ip_address = asker_get_client_ip();
    $transient_key = 'asker_checkout_attempts_' . md5( $ip_address );
    delete_transient( $transient_key );
}
add_action( 'woocommerce_checkout_order_processed', 'asker_reset_checkout_rate_limit' );

/**
 * Защита Contact Form 7 через honeypot
 */
function asker_add_cf7_honeypot( $content ) {
    // Проверяем, что это форма CF7
    if ( ! class_exists( 'WPCF7_ContactForm' ) ) {
        return $content;
    }
    
    // Добавляем honeypot поле к форме
    $honeypot = '<div style="position: absolute; left: -9999px; opacity: 0; pointer-events: none;" aria-hidden="true">';
    $honeypot .= '<label for="asker_cf7_honeypot">Оставьте это поле пустым</label>';
    $honeypot .= '<input type="text" id="asker_cf7_honeypot" name="asker_cf7_honeypot" value="" autocomplete="off" tabindex="-1">';
    $honeypot .= '</div>';
    
    // Вставляем перед закрывающим тегом формы
    $content = str_replace( '</form>', $honeypot . '</form>', $content );
    
    return $content;
}
add_filter( 'wpcf7_form_elements', 'asker_add_cf7_honeypot' );

/**
 * Валидация honeypot для CF7
 */
function asker_validate_cf7_honeypot( $result, $tag ) {
    // Проверяем honeypot поле
    if ( isset( $_POST['asker_cf7_honeypot'] ) && ! empty( $_POST['asker_cf7_honeypot'] ) ) {
        $result->invalidate( $tag, 'Обнаружена подозрительная активность.' );
    }
    
    return $result;
}
// Добавляем валидацию для всех полей CF7
if ( class_exists( 'WPCF7_ContactForm' ) ) {
    add_filter( 'wpcf7_validate', 'asker_validate_cf7_honeypot', 10, 2 );
}

/**
 * Rate limiting для CF7 форм (максимум 5 отправок за 10 минут с одного IP)
 */
function asker_cf7_rate_limit( $result, $tag ) {
    if ( ! class_exists( 'WPCF7_ContactForm' ) ) {
        return $result;
    }
    
    $ip_address = asker_get_client_ip();
    $transient_key = 'asker_cf7_attempts_' . md5( $ip_address );
    $attempts = get_transient( $transient_key );
    
    if ( $attempts === false ) {
        set_transient( $transient_key, 1, 10 * MINUTE_IN_SECONDS );
    } elseif ( $attempts >= 5 ) {
        $result->invalidate( $tag, 'Слишком много попыток отправки формы. Пожалуйста, подождите несколько минут.' );
    } else {
        set_transient( $transient_key, $attempts + 1, 10 * MINUTE_IN_SECONDS );
    }
    
    return $result;
}
if ( class_exists( 'WPCF7_ContactForm' ) ) {
    add_filter( 'wpcf7_validate', 'asker_cf7_rate_limit', 5, 2 );
}

/**
 * Получаем реальный IP адрес клиента
 */
function asker_get_client_ip() {
    $ip_keys = array(
        'HTTP_CF_CONNECTING_IP', // Cloudflare
        'HTTP_X_REAL_IP',        // Nginx proxy
        'HTTP_X_FORWARDED_FOR',  // Proxy
        'REMOTE_ADDR',           // Стандартный
    );
    
    foreach ( $ip_keys as $key ) {
        if ( isset( $_SERVER[ $key ] ) && ! empty( $_SERVER[ $key ] ) ) {
            $ip = sanitize_text_field( $_SERVER[ $key ] );
            
            // Если это список IP (X-Forwarded-For), берем первый
            if ( strpos( $ip, ',' ) !== false ) {
                $ip = trim( explode( ',', $ip )[0] );
            }
            
            // Проверяем валидность IP
            if ( filter_var( $ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE ) ) {
                return $ip;
            }
        }
    }
    
    // Fallback на REMOTE_ADDR
    return isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( $_SERVER['REMOTE_ADDR'] ) : '0.0.0.0';
}

/**
 * Защита от подозрительных паттернов в данных формы
 */
function asker_detect_spam_patterns( $data, $errors ) {
    // Проверяем наличие спам-слов в комментариях
    if ( isset( $data['order_comments'] ) && ! empty( $data['order_comments'] ) ) {
        $spam_words = array( 'viagra', 'casino', 'loan', 'credit', 'http://', 'https://', '[url', '[link' );
        $comment_lower = mb_strtolower( $data['order_comments'] );
        
        foreach ( $spam_words as $spam_word ) {
            if ( strpos( $comment_lower, $spam_word ) !== false ) {
                // Не блокируем полностью, но логируем
                error_log( 'Asker: Potential spam detected in order comment from IP: ' . asker_get_client_ip() );
                break;
            }
        }
    }
    
    return $errors;
}
add_action( 'woocommerce_after_checkout_validation', 'asker_detect_spam_patterns', 15, 2 );

/**
 * Добавляем время заполнения формы (защита от ботов)
 */
function asker_add_form_timestamp() {
    if ( ! is_checkout() ) {
        return;
    }
    ?>
    <input type="hidden" name="asker_form_start_time" value="<?php echo esc_attr( time() ); ?>">
    <?php
}
add_action( 'woocommerce_checkout_before_customer_details', 'asker_add_form_timestamp' );

/**
 * Проверяем время заполнения формы (слишком быстро = бот)
 */
function asker_validate_form_time( $data, $errors ) {
    if ( isset( $_POST['asker_form_start_time'] ) ) {
        $start_time = intval( $_POST['asker_form_start_time'] );
        $current_time = time();
        $form_time = $current_time - $start_time;
        
        // Если форма заполнена менее чем за 5 секунд - подозрительно
        if ( $form_time < 5 ) {
            $errors->add( 'form_time', 'Форма заполнена слишком быстро. Пожалуйста, заполните все поля внимательно.' );
        }
    }
    
    return $errors;
}
add_action( 'woocommerce_after_checkout_validation', 'asker_validate_form_time', 10, 2 );

