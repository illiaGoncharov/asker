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
    
    // Шорткод формы обратной связи (попап в хедере)
    $wp_customize->add_setting('popup_form_shortcode', [
        'default' => '',
        'sanitize_callback' => 'sanitize_text_field',
    ]);
    
    $wp_customize->add_control('popup_form_shortcode', [
        'label' => 'Шорткод формы для попапа',
        'description' => 'Форма обратной связи (имя, телефон, email). Шорткод CF7.',
        'section' => 'asker_theme_settings',
        'type' => 'text',
    ]);
    
    // Шорткод формы на главной странице
    $wp_customize->add_setting('homepage_form_shortcode', [
        'default' => '',
        'sanitize_callback' => 'sanitize_text_field',
    ]);
    
    $wp_customize->add_control('homepage_form_shortcode', [
        'label' => 'Шорткод формы на главной',
        'description' => 'Форма в секции "Оптовым клиентам" на главной странице.',
        'section' => 'asker_theme_settings',
        'type' => 'text',
    ]);
    
    // Шорткод формы на странице контактов
    $wp_customize->add_setting('contacts_form_shortcode', [
        'default' => '',
        'sanitize_callback' => 'sanitize_text_field',
    ]);
    
    $wp_customize->add_control('contacts_form_shortcode', [
        'label' => 'Шорткод формы на странице контактов',
        'description' => 'Форма обратной связи на странице "Контакты".',
        'section' => 'asker_theme_settings',
        'type' => 'text',
    ]);
    
    // URL карты из Конструктора Яндекс.Карт
    $wp_customize->add_setting('yandex_map_url', [
        'default' => '',
        'sanitize_callback' => 'esc_url_raw',
    ]);
    
    $wp_customize->add_control('yandex_map_url', [
        'label' => 'URL карты (Конструктор Яндекс)',
        'description' => 'Скопируйте только URL из src="..." в iframe коде. Например: https://yandex.ru/map-widget/v1/?um=constructor...',
        'section' => 'asker_theme_settings',
        'type' => 'url',
    ]);
    
    // API ключ Яндекс.Карт (устаревший способ)
    $wp_customize->add_setting('yandex_map_api_key', [
        'default' => '',
        'sanitize_callback' => 'sanitize_text_field',
    ]);
    
    $wp_customize->add_control('yandex_map_api_key', [
        'label' => 'API ключ Яндекс.Карт (альтернатива)',
        'description' => 'Используется только если iframe код не задан. Получите на <a href="https://developer.tech.yandex.ru/services/" target="_blank">developer.tech.yandex.ru</a>',
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
    
    // Изображение товара по умолчанию (placeholder)
    $wp_customize->add_setting('default_product_image', [
        'default' => '',
        'sanitize_callback' => 'absint',
    ]);
    
    $wp_customize->add_control(new WP_Customize_Media_Control($wp_customize, 'default_product_image', [
        'label' => 'Изображение товара по умолчанию',
        'description' => 'Картинка для товаров без фотографии',
        'section' => 'asker_theme_settings',
        'mime_type' => 'image',
    ]));
    
    // === Категории в футере ===
    // Получаем все категории WooCommerce
    $categories_choices = array( '' => '— Не выбрано —' );
    if ( class_exists( 'WooCommerce' ) ) {
        $product_categories = get_terms( array(
            'taxonomy'   => 'product_cat',
            'hide_empty' => false,
            'orderby'    => 'name',
            'order'      => 'ASC',
        ) );
        
        if ( ! empty( $product_categories ) && ! is_wp_error( $product_categories ) ) {
            foreach ( $product_categories as $category ) {
                $categories_choices[ $category->term_id ] = $category->name;
            }
        }
    }
    
    // Добавляем 5 настроек для категорий футера
    for ( $i = 1; $i <= 5; $i++ ) {
        $wp_customize->add_setting( 'footer_category_' . $i, [
            'default' => '',
            'sanitize_callback' => 'absint',
        ] );
        
        $wp_customize->add_control( 'footer_category_' . $i, [
            'label'   => 'Категория в футере #' . $i,
            'section' => 'asker_theme_settings',
            'type'    => 'select',
            'choices' => $categories_choices,
        ] );
    }
});





