<?php
/**
 * Custom Post Type: Менеджеры
 */

/**
 * Регистрируем CPT "Менеджеры"
 */
function asker_register_managers_cpt() {
    register_post_type( 'manager', array(
        'labels' => array(
            'name' => 'Менеджеры',
            'singular_name' => 'Менеджер',
            'add_new' => 'Добавить менеджера',
            'add_new_item' => 'Добавить нового менеджера',
            'edit_item' => 'Редактировать менеджера',
            'all_items' => 'Все менеджеры',
            'view_item' => 'Посмотреть менеджера',
            'search_items' => 'Искать менеджера',
            'not_found' => 'Менеджеры не найдены',
        ),
        'public' => false,
        'show_ui' => true,
        'menu_icon' => 'dashicons-businessman',
        'supports' => array( 'title', 'thumbnail' ),
        'has_archive' => false,
        'show_in_rest' => false,
        'menu_position' => 61,
    ) );
}
add_action( 'init', 'asker_register_managers_cpt' );

/**
 * Назначаем менеджера новому клиенту (round-robin)
 */
function asker_assign_manager_to_customer( $customer_id ) {
    // Получаем всех активных менеджеров
    $managers = get_posts( array(
        'post_type' => 'manager',
        'posts_per_page' => -1,
        'post_status' => 'publish',
        'orderby' => 'date',
        'order' => 'ASC'
    ) );
    
    if ( empty( $managers ) ) {
        return; // Нет менеджеров
    }
    
    // Получаем ID последнего назначенного менеджера
    $last_assigned = get_option( 'asker_last_assigned_manager', 0 );
    
    // Находим следующего менеджера по кругу
    $current_index = 0;
    
    if ( $last_assigned ) {
        foreach ( $managers as $i => $manager ) {
            if ( $manager->ID === $last_assigned ) {
                $current_index = ( $i + 1 ) % count( $managers );
                break;
            }
        }
    }
    
    $assigned_manager = $managers[ $current_index ];
    
    // Сохраняем ID менеджера в user_meta
    update_user_meta( $customer_id, 'assigned_manager_id', $assigned_manager->ID );
    
    // Обновляем последнего назначенного
    update_option( 'asker_last_assigned_manager', $assigned_manager->ID );
}
add_action( 'woocommerce_created_customer', 'asker_assign_manager_to_customer' );

/**
 * Добавляем секцию "Персональный менеджер" на странице редактирования пользователя
 */
function asker_render_manager_section( $user ) {
    // Показываем только администраторам
    if ( ! current_user_can( 'edit_users' ) ) {
        return;
    }
    
    $assigned_manager_id = get_user_meta( $user->ID, 'assigned_manager_id', true );
    
    // Получаем всех менеджеров
    $managers = get_posts( array(
        'post_type' => 'manager',
        'posts_per_page' => -1,
        'post_status' => 'publish',
        'orderby' => 'title',
        'order' => 'ASC'
    ) );
    
    wp_nonce_field( 'asker_save_manager', 'asker_manager_nonce' );
    ?>
    <h2>Персональный менеджер</h2>
    <table class="form-table">
        <tr>
            <th><label for="assigned_manager_id">Выберите менеджера</label></th>
            <td>
                <select name="assigned_manager_id" id="assigned_manager_id" style="width: 300px;">
                    <option value="">— Не назначен —</option>
                    <?php foreach ( $managers as $manager ) : ?>
                        <option value="<?php echo esc_attr( $manager->ID ); ?>" <?php selected( $assigned_manager_id, $manager->ID ); ?>>
                            <?php echo esc_html( $manager->post_title ); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <p class="description">Персональный менеджер, которого увидит клиент в личном кабинете</p>
            </td>
        </tr>
    </table>
    <?php
}
add_action( 'show_user_profile', 'asker_render_manager_section' );
add_action( 'edit_user_profile', 'asker_render_manager_section' );

function asker_save_manager_metabox( $user_id ) {
    if ( ! isset( $_POST['asker_manager_nonce'] ) || ! wp_verify_nonce( $_POST['asker_manager_nonce'], 'asker_save_manager' ) ) {
        return;
    }
    
    if ( ! current_user_can( 'edit_user', $user_id ) ) {
        return;
    }
    
    if ( isset( $_POST['assigned_manager_id'] ) ) {
        $manager_id = intval( $_POST['assigned_manager_id'] );
        if ( $manager_id > 0 ) {
            update_user_meta( $user_id, 'assigned_manager_id', $manager_id );
        } else {
            delete_user_meta( $user_id, 'assigned_manager_id' );
        }
    }
}
add_action( 'personal_options_update', 'asker_save_manager_metabox' );
add_action( 'edit_user_profile_update', 'asker_save_manager_metabox' );

/**
 * Добавляем колонку "Менеджер" в список пользователей
 */
function asker_add_manager_column( $columns ) {
    $columns['assigned_manager'] = 'Менеджер';
    return $columns;
}
add_filter( 'manage_users_columns', 'asker_add_manager_column' );

function asker_show_manager_column( $value, $column_name, $user_id ) {
    if ( $column_name === 'assigned_manager' ) {
        $manager_id = get_user_meta( $user_id, 'assigned_manager_id', true );
        if ( $manager_id ) {
            $manager = get_post( $manager_id );
            if ( $manager ) {
                return esc_html( $manager->post_title );
            }
        }
        return '—';
    }
    return $value;
}
add_filter( 'manage_users_custom_column', 'asker_show_manager_column', 10, 3 );

