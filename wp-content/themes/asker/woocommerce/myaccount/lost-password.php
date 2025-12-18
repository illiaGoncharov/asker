<?php
/**
 * Lost password page - обработка endpoint'а lost-password
 * 
 * Просто передаём управление стандартным WooCommerce шаблонам
 * БЕЗ дополнительной проверки ключа (проверка происходит при отправке формы)
 *
 * @package WooCommerce\Templates
 * @version 9.9.0
 */

defined( 'ABSPATH' ) || exit;

// Если есть show-reset-form — показываем форму сброса пароля БЕЗ проверки ключа
// Проверка ключа произойдёт при отправке формы
if ( isset( $_GET['show-reset-form'] ) ) {
    
    $cookie_name = 'wp-resetpass-' . COOKIEHASH;
    
    if ( isset( $_COOKIE[ $cookie_name ] ) && strpos( $_COOKIE[ $cookie_name ], ':' ) !== false ) {
        $value = sanitize_text_field( wp_unslash( $_COOKIE[ $cookie_name ] ) );
        list( $rp_login, $rp_key ) = explode( ':', $value, 2 );
        
        // Показываем форму сброса пароля — проверка ключа будет при сабмите
        wc_get_template(
            'myaccount/form-reset-password.php',
            array(
                'key'   => $rp_key,
                'login' => $rp_login,
            )
        );
    } else {
        // Cookie нет — показываем форму запроса
        wc_add_notice( __( 'Ссылка для сброса пароля недействительна или истекла. Запросите новую.', 'woocommerce' ), 'error' );
        wc_get_template( 'myaccount/form-lost-password.php' );
    }
    
} elseif ( isset( $_GET['key'] ) && isset( $_GET['login'] ) ) {
    // Прямой переход по ссылке с ключом в URL
    $reset_key = sanitize_text_field( wp_unslash( $_GET['key'] ) );
    $reset_login = sanitize_text_field( wp_unslash( $_GET['login'] ) );
    
    // Показываем форму сброса пароля напрямую — проверка будет при сабмите
    wc_get_template(
        'myaccount/form-reset-password.php',
        array(
            'key'   => $reset_key,
            'login' => $reset_login,
        )
    );
    
} else {
    // Просто форма запроса сброса пароля
    wc_get_template( 'myaccount/form-lost-password.php' );
}

