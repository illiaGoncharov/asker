<?php
/**
 * Кастомные email шаблоны для WooCommerce
 * Красивые HTML письма в стиле сайта
 */

/**
 * Подключаем кастомные шаблоны email
 */
function asker_load_email_templates( $template, $template_name, $template_path ) {
    // Путь к нашим шаблонам
    $custom_template = get_template_directory() . '/woocommerce/emails/' . $template_name;
    
    // Если наш шаблон существует - используем его
    if ( file_exists( $custom_template ) ) {
        return $custom_template;
    }
    
    return $template;
}
add_filter( 'woocommerce_locate_template', 'asker_load_email_templates', 10, 3 );

/**
 * Кастомные стили для email писем
 */
function asker_email_styles( $css ) {
    // Переопределяем стандартные стили WooCommerce
    $css = '
        body {
            background-color: #f5f5f5;
            font-family: "Montserrat", Arial, sans-serif;
            color: #333333;
        }
        .wrapper {
            background-color: #f5f5f5;
            padding: 20px;
        }
        .template-container {
            max-width: 600px;
            margin: 0 auto;
            background-color: #ffffff;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .email-header {
            background: linear-gradient(135deg, #111827 0%, #1f2937 100%);
            padding: 30px;
            text-align: center;
        }
        .email-header h1 {
            color: #ffffff;
            margin: 0;
            font-size: 24px;
            font-weight: 600;
        }
        .email-body {
            padding: 30px;
        }
        .email-body h2 {
            color: #111827;
            font-size: 20px;
            margin-top: 0;
            margin-bottom: 20px;
        }
        .email-body p {
            color: #666666;
            font-size: 16px;
            line-height: 1.6;
            margin-bottom: 15px;
        }
        .order-details {
            background-color: #f9fafb;
            border-radius: 6px;
            padding: 20px;
            margin: 20px 0;
        }
        .order-details table {
            width: 100%;
            border-collapse: collapse;
        }
        .order-details th {
            text-align: left;
            padding: 10px;
            color: #111827;
            font-weight: 600;
            border-bottom: 1px solid #e5e7eb;
        }
        .order-details td {
            padding: 10px;
            color: #666666;
            border-bottom: 1px solid #e5e7eb;
        }
        .order-total {
            background-color: #111827;
            color: #ffffff;
            font-weight: 600;
            padding: 15px;
            text-align: right;
            font-size: 18px;
        }
        .email-footer {
            background-color: #f9fafb;
            padding: 20px;
            text-align: center;
            font-size: 12px;
            color: #999999;
        }
        .button {
            display: inline-block;
            padding: 15px 30px;
            background-color: #FFD600;
            color: #111827;
            text-decoration: none;
            border-radius: 50px;
            font-weight: 600;
            margin: 20px 0;
        }
        .button:hover {
            background-color: #e6c100;
        }
    ';
    
    return $css;
}
add_filter( 'woocommerce_email_styles', 'asker_email_styles' );

/**
 * Кастомный подвал для email (через фильтр footer_text)
 */
function asker_email_footer_text( $footer_text ) {
    $site_name = get_bloginfo( 'name' );
    $site_url = home_url();
    
    $footer_text = '<div style="text-align: center; padding: 20px; font-size: 12px; color: #999999;">';
    $footer_text .= '<p>&copy; ' . date('Y') . ' ' . esc_html( $site_name ) . '. Все права защищены.</p>';
    $footer_text .= '<p>Это письмо отправлено автоматически, пожалуйста, не отвечайте на него.</p>';
    $footer_text .= '<p><a href="' . esc_url( $site_url ) . '" style="color: #666666;">' . esc_html( $site_url ) . '</a></p>';
    $footer_text .= '</div>';
    
    return $footer_text;
}
add_filter( 'woocommerce_email_footer_text', 'asker_email_footer_text' );

/**
 * Улучшаем шаблон письма "Новый заказ" (клиенту)
 */
function asker_customize_new_order_email( $order, $sent_to_admin, $plain_text, $email ) {
    // Это письмо отправляется клиенту, не админу
    if ( $sent_to_admin ) {
        return;
    }
    
    // Проверяем тип письма
    if ( $email->id !== 'customer_new_order' ) {
        return;
    }
    
    // Переопределяем стандартный текст письма полностью
    // Удаляем стандартный текст "Получен заказ от покупателя"
    remove_action( 'woocommerce_email_order_details', array( $email, 'order_details' ), 10 );
    
    // Добавляем кастомный контент перед таблицей заказа
    echo '<div style="padding: 20px 0;">';
    echo '<p style="font-size: 16px; color: #666666; margin-bottom: 15px;">Здравствуйте!</p>';
    echo '<p style="font-size: 16px; color: #666666; margin-bottom: 15px;">Спасибо за ваш заказ. Мы получили его и начали обработку.</p>';
    echo '</div>';
}
add_action( 'woocommerce_email_before_order_table', 'asker_customize_new_order_email', 5, 4 );

/**
 * Переопределяем стандартный текст письма админу "Новый заказ"
 */
function asker_customize_admin_new_order_email( $order, $sent_to_admin, $plain_text, $email ) {
    // Это письмо отправляется админу
    if ( ! $sent_to_admin ) {
        return;
    }
    
    // Проверяем тип письма
    if ( $email->id !== 'new_order' ) {
        return;
    }
    
    // Переопределяем стандартный текст
    echo '<div style="padding: 20px 0;">';
    echo '<p style="font-size: 16px; color: #666666; margin-bottom: 15px;">Получен новый заказ от покупателя ' . esc_html( $order->get_billing_first_name() . ' ' . $order->get_billing_last_name() ) . '.</p>';
    echo '</div>';
}
add_action( 'woocommerce_email_before_order_table', 'asker_customize_admin_new_order_email', 5, 4 );

/**
 * Улучшаем шаблон письма "Заказ обработан"
 */
function asker_customize_processing_email( $order, $sent_to_admin, $plain_text, $email ) {
    if ( $sent_to_admin ) {
        return;
    }
    
    // Проверяем тип письма
    if ( $email->id !== 'customer_processing_order' ) {
        return;
    }
    
    echo '<div style="padding: 20px 0;">';
    echo '<p style="font-size: 16px; color: #666666; margin-bottom: 15px;">Ваш заказ #' . esc_html( $order->get_order_number() ) . ' обработан и готов к отправке.</p>';
    echo '<p style="font-size: 16px; color: #666666; margin-bottom: 15px;">Мы свяжемся с вами для уточнения деталей доставки.</p>';
    echo '</div>';
}
add_action( 'woocommerce_email_before_order_table', 'asker_customize_processing_email', 10, 4 );

/**
 * Улучшаем шаблон письма "Заказ отправлен"
 */
function asker_customize_completed_email( $order, $sent_to_admin, $plain_text, $email ) {
    if ( $sent_to_admin ) {
        return;
    }
    
    // Проверяем тип письма
    if ( $email->id !== 'customer_completed_order' ) {
        return;
    }
    
    // Получаем трек-номер из мета заказа (если есть)
    $tracking_number = $order->get_meta( '_tracking_number' );
    
    echo '<div style="padding: 20px 0;">';
    echo '<p style="font-size: 16px; color: #666666; margin-bottom: 15px;">Отличные новости! Ваш заказ #' . esc_html( $order->get_order_number() ) . ' отправлен.</p>';
    
    if ( $tracking_number ) {
        echo '<div style="background-color: #f9fafb; border-radius: 6px; padding: 20px; margin: 20px 0;">';
        echo '<h3 style="color: #111827; font-size: 18px; margin-top: 0; margin-bottom: 10px;">Трек-номер для отслеживания:</h3>';
        echo '<p style="font-size: 20px; font-weight: 600; color: #111827; margin: 0;">' . esc_html( $tracking_number ) . '</p>';
        echo '</div>';
    }
    
    echo '<p style="font-size: 16px; color: #666666; margin-bottom: 15px;">Вы можете отслеживать статус доставки в личном кабинете.</p>';
    echo '<p style="margin: 20px 0;"><a href="' . esc_url( wc_get_account_endpoint_url( 'orders' ) ) . '" style="display: inline-block; padding: 15px 30px; background-color: #FFD600; color: #111827; text-decoration: none; border-radius: 50px; font-weight: 600;">Перейти в личный кабинет</a></p>';
    echo '</div>';
}
add_action( 'woocommerce_email_before_order_table', 'asker_customize_completed_email', 10, 4 );

/**
 * Добавляем информацию о менеджере в письма
 */
function asker_add_manager_info_to_email( $order, $sent_to_admin, $plain_text, $email ) {
    if ( $sent_to_admin ) {
        return;
    }
    
    $user_id = $order->get_customer_id();
    if ( ! $user_id ) {
        return;
    }
    
    $manager_id = get_user_meta( $user_id, 'assigned_manager_id', true );
    if ( ! $manager_id ) {
        return;
    }
    
    $manager_post = get_post( $manager_id );
    if ( ! $manager_post ) {
        return;
    }
    
    $manager_name = get_field( 'manager_name', $manager_id );
    $manager_phone = get_field( 'manager_phone', $manager_id );
    $manager_email = get_field( 'manager_email', $manager_id );
    
    if ( $manager_name ) {
        echo '<div style="background-color: #f9fafb; border-radius: 6px; padding: 20px; margin: 30px 0;">';
        echo '<h3 style="color: #111827; font-size: 18px; margin-top: 0; margin-bottom: 15px;">Ваш персональный менеджер</h3>';
        echo '<p style="font-size: 16px; color: #111827; font-weight: 600; margin-bottom: 10px;">' . esc_html( $manager_name ) . '</p>';
        if ( $manager_phone ) {
            echo '<p style="font-size: 14px; color: #666666; margin-bottom: 5px;">Телефон: ' . esc_html( $manager_phone ) . '</p>';
        }
        if ( $manager_email ) {
            echo '<p style="font-size: 14px; color: #666666; margin-bottom: 0;">Email: <a href="mailto:' . esc_attr( $manager_email ) . '" style="color: #111827;">' . esc_html( $manager_email ) . '</a></p>';
        }
        echo '</div>';
    }
}
add_action( 'woocommerce_email_after_order_table', 'asker_add_manager_info_to_email', 10, 4 );

/**
 * Переводим заголовки писем на русский
 */
function asker_translate_email_subjects( $subject, $email ) {
    $translations = array(
        'customer_new_order' => 'Новый заказ #%s',
        'customer_processing_order' => 'Ваш заказ #%s обработан',
        'customer_completed_order' => 'Ваш заказ #%s отправлен',
        'customer_refunded_order' => 'Возврат средств по заказу #%s',
        'customer_invoice' => 'Счет на оплату заказа #%s',
        'customer_note' => 'Обновление по заказу #%s',
        'customer_reset_password' => 'Сброс пароля',
        'customer_on_hold_order' => 'Заказ #%s ожидает оплаты',
    );
    
    if ( isset( $translations[ $email->id ] ) ) {
        // Получаем номер заказа из subject (если есть)
        $order_number = '';
        if ( is_a( $email->object, 'WC_Order' ) ) {
            $order_number = $email->object->get_order_number();
        }
        
        if ( $order_number ) {
            $subject = sprintf( $translations[ $email->id ], $order_number );
        } else {
            $subject = str_replace( array( '#%s', '%s' ), '', $translations[ $email->id ] );
        }
    }
    
    return $subject;
}
add_filter( 'woocommerce_email_subject_customer_new_order', 'asker_translate_email_subjects', 10, 2 );
add_filter( 'woocommerce_email_subject_customer_processing_order', 'asker_translate_email_subjects', 10, 2 );
add_filter( 'woocommerce_email_subject_customer_completed_order', 'asker_translate_email_subjects', 10, 2 );

/**
 * Переводим заголовки писем через общий фильтр
 */
function asker_translate_all_email_subjects( $subject, $email ) {
    return asker_translate_email_subjects( $subject, $email );
}
add_filter( 'woocommerce_email_subject', 'asker_translate_all_email_subjects', 10, 2 );

/**
 * Переводим заголовки писем (email_heading)
 */
function asker_translate_email_heading( $heading, $email ) {
    $translations = array(
        'customer_new_order' => 'Новый заказ',
        'customer_processing_order' => 'Заказ обработан',
        'customer_completed_order' => 'Заказ отправлен',
        'customer_refunded_order' => 'Возврат средств',
        'customer_invoice' => 'Счет на оплату',
        'customer_note' => 'Обновление по заказу',
        'customer_reset_password' => 'Сброс пароля',
        'customer_on_hold_order' => 'Заказ ожидает оплаты',
    );
    
    if ( isset( $translations[ $email->id ] ) ) {
        $heading = $translations[ $email->id ];
    }
    
    return $heading;
}
add_filter( 'woocommerce_email_heading', 'asker_translate_email_heading', 10, 2 );

/**
 * Переводим названия колонок в таблице заказа
 */
function asker_translate_order_table_headers( $headers ) {
    return array(
        'product' => 'Товар',
        'quantity' => 'Количество',
        'price' => 'Цена',
    );
}
add_filter( 'woocommerce_email_order_items_table_columns', 'asker_translate_order_table_headers' );

/**
 * Переводим стандартные тексты WooCommerce в письмах
 */
function asker_translate_email_strings( $translated_text, $text, $domain ) {
    // Переводим только тексты WooCommerce
    if ( $domain !== 'woocommerce' ) {
        return $translated_text;
    }
    
    $translations = array(
        'Order details' => 'Детали заказа',
        'Billing address' => 'Адрес плательщика',
        'Shipping address' => 'Адрес доставки',
        'Subtotal' => 'Подытог',
        'Total' => 'Итого',
        'Order number:' => 'Номер заказа:',
        'Order date:' => 'Дата заказа:',
        'Payment method:' => 'Способ оплаты:',
        'Thank you for your order.' => 'Спасибо за ваш заказ.',
        'We have received your order and are now processing it.' => 'Мы получили ваш заказ и начали его обработку.',
        'Your order has been received and is now being processed.' => 'Ваш заказ получен и обрабатывается.',
        'Your order on %s has been completed.' => 'Ваш заказ на %s завершен.',
        'Hi %s,' => 'Здравствуйте, %s!',
        'Thanks for your order.' => 'Спасибо за ваш заказ.',
        'You have received an order from %s.' => 'Получен заказ от покупателя %s.',
        'You have received an order from %s. The order is as follows:' => 'Получен заказ от покупателя %s. Детали заказа:',
        'Congratulations. You have received an order.' => 'Поздравляем вас с продажей.',
        'You can view this order in the dashboard:' => 'Работать с заказами можно в приложении.',
        'Note:' => 'Примечание:',
        'This is a note added to your order. It may contain several lines. If no note is added, this section will be hidden.' => 'Это примечание клиента. Клиенты могут добавлять примечание к своему заказу при его оформлении. Оно может содержать несколько строк. Если примечание отсутствует, этот раздел будет скрыт.',
    );
    
    if ( isset( $translations[ $text ] ) ) {
        return $translations[ $text ];
    }
    
    return $translated_text;
}
add_filter( 'gettext', 'asker_translate_email_strings', 20, 3 );

/**
 * Перехватываем форматированные строки в письмах WooCommerce
 */
function asker_translate_email_formatted_strings( $string, $email ) {
    // Переводим строки с переменными
    $translations = array(
        'You have received an order from %s.' => 'Получен заказ от покупателя %s.',
        'You have received an order from %s. The order is as follows:' => 'Получен заказ от покупателя %s. Детали заказа:',
    );
    
    foreach ( $translations as $en => $ru ) {
        if ( strpos( $string, $en ) !== false ) {
            // Заменяем английский текст на русский, сохраняя переменные
            $string = str_replace( $en, $ru, $string );
        }
    }
    
    return $string;
}
add_filter( 'woocommerce_email_format_string', 'asker_translate_email_formatted_strings', 10, 2 );

/**
 * Переводим тексты в шаблоне письма "Новый заказ"
 */
function asker_translate_new_order_email_text( $text, $email ) {
    if ( $email->id === 'customer_new_order' ) {
        $translations = array(
            'Hi there,' => 'Здравствуйте!',
            'You have received an order from %s.' => 'Вы получили заказ от %s.',
        );
        
        if ( isset( $translations[ $text ] ) ) {
            return $translations[ $text ];
        }
    }
    
    return $text;
}
add_filter( 'woocommerce_email_format_string', 'asker_translate_new_order_email_text', 10, 2 );

