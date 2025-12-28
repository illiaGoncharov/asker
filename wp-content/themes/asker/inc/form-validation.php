<?php
/**
 * Валидация форм (серверная)
 * Улучшенная валидация для WooCommerce checkout и других форм
 */

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

