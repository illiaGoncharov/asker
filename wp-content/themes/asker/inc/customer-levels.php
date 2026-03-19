<?php
/**
 * Система уровней клиентов и динамических скидок
 */

// Создаем страницу настроек в админке
if( function_exists('acf_add_options_page') ) {
    acf_add_options_page(array(
        'page_title'    => 'Настройки Asker',
        'menu_title'    => 'Настройки Asker',
        'menu_slug'     => 'asker-settings',
        'capability'    => 'manage_options',
        'icon_url'      => 'dashicons-admin-generic',
        'position'      => 60,
    ));
}

/**
 * Получить уровень клиента на основе суммы заказов
 * @param int $user_id
 * @return array ['level' => 'Золото', 'discount' => 15, 'total_spent' => 200000, 'next_level' => array()]
 */
function asker_get_customer_level( $user_id ) {
    if ( ! $user_id ) {
        return array(
            'level' => 'Базовый',
            'discount' => 0,
            'total_spent' => 0,
            'next_level' => null
        );
    }
    
    // Получаем настройки уровней из ACF
    $levels_config = get_field('levels_config', 'option');
    
    // Дефолтные уровни если ACF не настроен
    // Для юр-лиц базовый уровень начинается со скидки 0%
    if ( empty( $levels_config ) ) {
        $levels_config = array(
            array('level_name' => 'Базовый', 'level_min' => 0, 'level_max' => 50000, 'level_discount' => 0),
            array('level_name' => 'Серебро', 'level_min' => 50001, 'level_max' => 150000, 'level_discount' => 10),
            array('level_name' => 'Золото', 'level_min' => 150001, 'level_max' => 500000, 'level_discount' => 15),
            array('level_name' => 'Платина', 'level_min' => 500001, 'level_max' => null, 'level_discount' => 20),
        );
    }
    
    // Получаем общую сумму завершенных заказов
    $orders = wc_get_orders( array(
        'customer_id' => $user_id,
        'status'      => array( 'completed' ),
        'limit'       => -1,
        'return'      => 'ids',
    ) );
    
    $total_spent = 0;
    foreach ( $orders as $order_id ) {
        $order = wc_get_order( $order_id );
        if ( $order ) {
            $total_spent += $order->get_total();
        }
    }
    
    // Определяем текущий уровень
    $current_level = null;
    $next_level = null;
    
    foreach ( $levels_config as $index => $level ) {
        $min = floatval( $level['level_min'] );
        $max = ! empty( $level['level_max'] ) ? floatval( $level['level_max'] ) : PHP_FLOAT_MAX;
        
        if ( $total_spent >= $min && $total_spent <= $max ) {
            $current_level = $level;
            // Следующий уровень
            if ( isset( $levels_config[ $index + 1 ] ) ) {
                $next_level = $levels_config[ $index + 1 ];
            }
            break;
        }
    }
    
    // Если уровень не найден, берем первый
    if ( ! $current_level ) {
        $current_level = $levels_config[0];
        if ( isset( $levels_config[1] ) ) {
            $next_level = $levels_config[1];
        }
    }
    
    return array(
        'level'        => $current_level['level_name'],
        'discount'     => intval( $current_level['level_discount'] ),
        'total_spent'  => $total_spent,
        'next_level'   => $next_level,
        'level_min'    => floatval( $current_level['level_min'] ),
        'level_max'    => ! empty( $current_level['level_max'] ) ? floatval( $current_level['level_max'] ) : null,
    );
}

/**
 * Обновляем уровень клиента после завершения заказа
 */
function asker_update_customer_level_on_order( $order_id ) {
    $order = wc_get_order( $order_id );
    if ( ! $order ) {
        return;
    }
    
    $user_id = $order->get_customer_id();
    if ( ! $user_id ) {
        return;
    }
    
    // Получаем старый уровень
    $old_level = get_user_meta( $user_id, 'customer_level', true );
    
    // Пересчитываем уровень
    $level_data = asker_get_customer_level( $user_id );
    
    // Сохраняем в user_meta
    update_user_meta( $user_id, 'customer_level', $level_data['level'] );
    update_user_meta( $user_id, 'customer_discount', $level_data['discount'] );
    update_user_meta( $user_id, 'customer_total_spent', $level_data['total_spent'] );
    
    // Если уровень изменился - отправляем email и записываем в историю
    if ( $old_level && $old_level !== $level_data['level'] ) {
        // История уровней
        $level_history = get_user_meta( $user_id, 'level_history', true );
        if ( ! is_array( $level_history ) ) {
            $level_history = array();
        }
        
        $level_history[] = array(
            'date'        => current_time( 'mysql' ),
            'level'       => $level_data['level'],
            'total_spent' => $level_data['total_spent'],
        );
        
        update_user_meta( $user_id, 'level_history', $level_history );
        
        // Отправляем email о повышении
        asker_send_level_up_email( $user_id, $level_data );
    }
}
add_action( 'woocommerce_order_status_completed', 'asker_update_customer_level_on_order' );

/**
 * Email уведомление о повышении уровня
 */
function asker_send_level_up_email( $user_id, $level_data ) {
    $user = get_userdata( $user_id );
    if ( ! $user ) {
        return;
    }
    
    $to = $user->user_email;
    $subject = '🎉 Ваш уровень повышен — ' . $level_data['level'];
    
    $message = '<html><body style="font-family: Arial, sans-serif;">';
    $message .= '<div style="max-width: 600px; margin: 0 auto; background: #fff; border: 1px solid #ddd; border-radius: 8px; overflow: hidden;">';
    $message .= '<div style="background: #111827; color: #fff; padding: 30px; text-align: center;">';
    $message .= '<h1 style="margin: 0;">🎉 Поздравляем!</h1>';
    $message .= '</div>';
    $message .= '<div style="padding: 30px;">';
    $message .= '<p style="font-size: 16px;">Здравствуйте, ' . esc_html( $user->first_name ) . '!</p>';
    $message .= '<p style="font-size: 16px;">Ваш уровень повышен до <strong style="color: #FFD600;">' . esc_html( $level_data['level'] ) . '</strong></p>';
    $message .= '<p style="font-size: 16px;">Теперь ваша персональная скидка: <strong>' . esc_html( $level_data['discount'] ) . '%</strong></p>';
    $message .= '<p style="font-size: 14px; color: #666;">Сумма покупок: ' . wc_price( $level_data['total_spent'] ) . '</p>';
    $message .= '<div style="text-align: center; margin: 30px 0;">';
    $message .= '<a href="' . wc_get_account_endpoint_url( 'dashboard' ) . '" style="display: inline-block; padding: 15px 30px; background: #FFD600; color: #111827; text-decoration: none; border-radius: 50px; font-weight: bold;">Перейти в личный кабинет</a>';
    $message .= '</div>';
    $message .= '</div>';
    $message .= '<div style="background: #f5f5f5; padding: 20px; text-align: center; font-size: 12px; color: #666;">';
    $message .= '<p>&copy; ' . date('Y') . ' Asker. Все права защищены.</p>';
    $message .= '</div>';
    $message .= '</div>';
    $message .= '</body></html>';
    
    $headers = array('Content-Type: text/html; charset=UTF-8');
    
    wp_mail( $to, $subject, $message, $headers );
}

/**
 * Применяем персональную скидку в корзине (уровень + индивидуальная)
 */

/**
 * Применяем персональную скидку напрямую к цене товаров
 * Скидка учитывается в стоимости товара, а не выносится отдельной строкой
 */
function asker_apply_customer_level_discount( $cart ) {
    if ( is_admin() && ! defined( 'DOING_AJAX' ) ) {
        return;
    }
    
    if ( ! is_user_logged_in() ) {
        return;
    }
    
    // Защита от рекурсии
    static $discount_applied = false;
    if ( $discount_applied ) {
        return;
    }
    $discount_applied = true;
    
    $user_id = get_current_user_id();
    $level_data = asker_get_customer_level( $user_id );
    $individual_discount = intval( get_user_meta( $user_id, 'individual_discount', true ) );
    
    // Итоговая скидка = скидка по уровню + индивидуальная
    $total_discount_percent = $level_data['discount'] + $individual_discount;
    
    if ( ! $total_discount_percent || $total_discount_percent <= 0 ) {
        return;
    }
    
    // Применяем скидку к каждому товару в корзине
    foreach ( $cart->get_cart() as $cart_item_key => $cart_item ) {
        // Получаем оригинальную цену товара
        $product = $cart_item['data'];
        $original_price = floatval( $product->get_regular_price() );
        
        // Если regular_price нет, берем текущую цену
        if ( ! $original_price ) {
            $original_price = floatval( $product->get_price() );
        }
        
        // Рассчитываем цену со скидкой
        $discounted_price = $original_price * ( 1 - $total_discount_percent / 100 );
        
        // Устанавливаем новую цену товара
        $product->set_price( $discounted_price );
    }
}
add_action( 'woocommerce_before_calculate_totals', 'asker_apply_customer_level_discount', 10, 1 );


/**
 * Добавляем колонки в список пользователей
 */
function asker_add_customer_level_columns( $columns ) {
    $columns['customer_level'] = 'Уровень';
    $columns['customer_discount'] = 'Скидка';
    $columns['total_spent'] = 'Сумма покупок';
    return $columns;
}
add_filter( 'manage_users_columns', 'asker_add_customer_level_columns' );

function asker_show_customer_level_columns( $value, $column_name, $user_id ) {
    $level_data = asker_get_customer_level( $user_id );
    $individual_discount = intval( get_user_meta( $user_id, 'individual_discount', true ) );
    
    switch ( $column_name ) {
        case 'customer_level':
            return esc_html( $level_data['level'] );
        case 'customer_discount':
            // Показываем итоговую скидку (уровень + индивидуальная)
            $total_discount = $level_data['discount'] + $individual_discount;
            $output = '<strong>' . esc_html( $total_discount ) . '%</strong>';
            
            // Детализация
            $details = array();
            if ( $level_data['discount'] > 0 ) {
                $details[] = 'уровень: ' . $level_data['discount'] . '%';
            }
            if ( $individual_discount > 0 ) {
                $details[] = '<span style="color: #0073aa;">инд: ' . $individual_discount . '%</span>';
            }
            if ( ! empty( $details ) ) {
                $output .= '<br><small style="color: #666;">(' . implode( ' + ', $details ) . ')</small>';
            }
            return $output;
        case 'total_spent':
            return wc_price( $level_data['total_spent'] );
    }
    
    return $value;
}
add_filter( 'manage_users_custom_column', 'asker_show_customer_level_columns', 10, 3 );

/**
 * Добавляем поле индивидуальной скидки в профиль пользователя (админка)
 */
function asker_add_individual_discount_field( $user ) {
    if ( ! current_user_can( 'edit_users' ) ) {
        return;
    }
    
    $individual_discount = get_user_meta( $user->ID, 'individual_discount', true );
    $level_data = asker_get_customer_level( $user->ID );
    $total_discount = intval( $level_data['discount'] ) + intval( $individual_discount );
    ?>
    <h2>Скидки клиента</h2>
    <table class="form-table">
        <tr>
            <th><label>Скидка по уровню</label></th>
            <td>
                <strong><?php echo esc_html( $level_data['discount'] ); ?>%</strong>
                <span style="color: #666; margin-left: 10px;">(Уровень: <?php echo esc_html( $level_data['level'] ); ?>)</span>
                <p class="description">Автоматически рассчитывается на основе суммы покупок: <?php echo wc_price( $level_data['total_spent'] ); ?></p>
            </td>
        </tr>
        <tr>
            <th><label for="individual_discount">Индивидуальная скидка</label></th>
            <td>
                <input type="number" name="individual_discount" id="individual_discount" 
                       value="<?php echo esc_attr( $individual_discount ); ?>" 
                       min="0" max="100" step="1" style="width: 80px;" /> %
                <p class="description">Дополнительная скидка, назначаемая администратором вручную</p>
            </td>
        </tr>
        <tr>
            <th><label>Итоговая скидка</label></th>
            <td>
                <strong style="font-size: 18px; color: #0073aa;"><?php echo esc_html( $total_discount ); ?>%</strong>
                <p class="description">Сумма скидки по уровню и индивидуальной скидки</p>
            </td>
        </tr>
    </table>
    <?php
}
add_action( 'show_user_profile', 'asker_add_individual_discount_field' );
add_action( 'edit_user_profile', 'asker_add_individual_discount_field' );

/**
 * Сохраняем индивидуальную скидку
 */
function asker_save_individual_discount( $user_id ) {
    if ( ! current_user_can( 'edit_user', $user_id ) ) {
        return;
    }
    
    if ( isset( $_POST['individual_discount'] ) ) {
        $discount = intval( $_POST['individual_discount'] );
        // Ограничиваем значение 0-100
        $discount = max( 0, min( 100, $discount ) );
        update_user_meta( $user_id, 'individual_discount', $discount );
    }
}
add_action( 'personal_options_update', 'asker_save_individual_discount' );
add_action( 'edit_user_profile_update', 'asker_save_individual_discount' );

/**
 * Получить итоговую скидку пользователя (уровень + индивидуальная)
 */
function asker_get_total_discount( $user_id ) {
    if ( ! $user_id ) {
        return 0;
    }
    
    $level_data = asker_get_customer_level( $user_id );
    $individual_discount = intval( get_user_meta( $user_id, 'individual_discount', true ) );
    
    return $level_data['discount'] + $individual_discount;
}

