<?php
/**
 * Заглушка кастомайзера. На будущее: настройки темы через WP Customizer.
 */

add_action('customize_register', function ($wp_customize) {
    // Секция для настроек темы
    $wp_customize->add_section('asker_theme_settings', [
        'title' => 'Настройки Asker',
        'priority' => 30,
    ]);
    
    // Логотип футера
    $wp_customize->add_setting('footer_logo', [
        'default' => '',
        'sanitize_callback' => 'absint',
    ]);
    
    $wp_customize->add_control(new WP_Customize_Media_Control($wp_customize, 'footer_logo', [
        'label' => 'Логотип в футере (белый)',
        'description' => 'Загрузите белый логотип для футера',
        'section' => 'asker_theme_settings',
        'mime_type' => 'image',
    ]));
    
    // Шорткод формы футера
    $wp_customize->add_setting('footer_form_shortcode', [
        'default' => '',
        'sanitize_callback' => 'sanitize_text_field',
    ]);
    
    $wp_customize->add_control('footer_form_shortcode', [
        'label' => 'Шорткод формы в футере',
        'description' => 'Вставьте шорткод CF7, например: [contact-form-7 id="123"]',
        'section' => 'asker_theme_settings',
        'type' => 'text',
    ]);
    
    // API ключ Яндекс.Карт
    $wp_customize->add_setting('yandex_map_api_key', [
        'default' => '',
        'sanitize_callback' => 'sanitize_text_field',
    ]);
    
    $wp_customize->add_control('yandex_map_api_key', [
        'label' => 'API ключ Яндекс.Карт',
        'description' => 'Получите ключ на <a href="https://developer.tech.yandex.ru/services/" target="_blank">developer.tech.yandex.ru</a>',
        'section' => 'asker_theme_settings',
        'type' => 'text',
    ]);
    
    // Email для форм обратной связи
    $wp_customize->add_setting('contact_form_email', [
        'default' => get_option('admin_email'),
        'sanitize_callback' => 'sanitize_email',
    ]);
    
    $wp_customize->add_control('contact_form_email', [
        'label' => 'Email для форм обратной связи',
        'description' => 'На этот email будут отправляться сообщения с форм обратной связи',
        'section' => 'asker_theme_settings',
        'type' => 'email',
    ]);
});





