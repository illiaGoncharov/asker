<?php
/**
 * Хелпер для создания страниц в WordPress
 * Можно вызвать через wp-admin или через URL: /wp-admin/admin.php?page=asker-create-pages
 */

add_action('admin_menu', 'asker_create_pages_menu');

function asker_create_pages_menu() {
    add_submenu_page(
        'tools.php',
        'Создать страницы Asker',
        'Создать страницы Asker',
        'manage_options',
        'asker-create-pages',
        'asker_create_pages_page'
    );
}

function asker_create_pages_page() {
    if (!current_user_can('manage_options')) {
        wp_die('Недостаточно прав для выполнения этой операции.');
    }

    if (isset($_POST['create_pages']) && check_admin_referer('asker_create_pages')) {
        $pages_to_create = array(
            array(
                'title' => 'Контакты',
                'slug' => 'contacts',
                'template' => 'page-contacts.php',
                'content' => ''
            ),
            array(
                'title' => 'О компании',
                'slug' => 'about',
                'template' => 'page-about.php',
                'content' => ''
            ),
            array(
                'title' => 'Оплата',
                'slug' => 'payment',
                'template' => 'page-payment.php',
                'content' => ''
            ),
            array(
                'title' => 'Доставка',
                'slug' => 'delivery',
                'template' => 'page-delivery.php',
                'content' => ''
            ),
            array(
                'title' => 'Гарантии',
                'slug' => 'warranty',
                'template' => 'page-warranty.php',
                'content' => ''
            ),
        );

        $created = 0;
        $updated = 0;

        foreach ($pages_to_create as $page_data) {
            // Проверяем, существует ли страница
            $existing_page = get_page_by_path($page_data['slug']);
            
            if ($existing_page) {
                // Обновляем существующую страницу
                $page_id = $existing_page->ID;
                update_post_meta($page_id, '_wp_page_template', $page_data['template']);
                $updated++;
            } else {
                // Создаем новую страницу
                $page_id = wp_insert_post(array(
                    'post_title' => $page_data['title'],
                    'post_name' => $page_data['slug'],
                    'post_content' => $page_data['content'],
                    'post_status' => 'publish',
                    'post_type' => 'page',
                    'post_author' => get_current_user_id(),
                ));
                
                if ($page_id && !is_wp_error($page_id)) {
                    update_post_meta($page_id, '_wp_page_template', $page_data['template']);
                    $created++;
                }
            }
        }

        echo '<div class="notice notice-success"><p>';
        echo "Создано страниц: {$created}, обновлено: {$updated}";
        echo '</p></div>';
    }

    ?>
    <div class="wrap">
        <h1>Создать страницы Asker</h1>
        <p>Эта утилита создаст или обновит основные страницы сайта с правильными шаблонами.</p>
        
        <form method="post" action="">
            <?php wp_nonce_field('asker_create_pages'); ?>
            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th>Название</th>
                        <th>Slug</th>
                        <th>Шаблон</th>
                        <th>Статус</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $pages_list = array(
                        array('Контакты', 'contacts', 'page-contacts.php'),
                        array('О компании', 'about', 'page-about.php'),
                        array('Оплата', 'payment', 'page-payment.php'),
                        array('Доставка', 'delivery', 'page-delivery.php'),
                        array('Гарантии', 'warranty', 'page-warranty.php'),
                    );
                    
                    foreach ($pages_list as $page_data) {
                        list($title, $slug, $template) = $page_data;
                        $existing = get_page_by_path($slug);
                        $status = $existing ? '<span style="color: green;">✓ Существует</span>' : '<span style="color: red;">✗ Не создана</span>';
                        
                        echo '<tr>';
                        echo '<td>' . esc_html($title) . '</td>';
                        echo '<td>' . esc_html($slug) . '</td>';
                        echo '<td>' . esc_html($template) . '</td>';
                        echo '<td>' . $status . '</td>';
                        echo '</tr>';
                    }
                    ?>
                </tbody>
            </table>
            
            <p class="submit">
                <input type="submit" name="create_pages" class="button button-primary" value="Создать/Обновить страницы">
            </p>
        </form>
        
        <hr>
        <h2>Ручное создание</h2>
        <p>Если автоматическое создание не работает, создайте страницы вручную:</p>
        <ol>
            <li>Перейдите в <strong>Страницы → Добавить новую</strong></li>
            <li>Для каждой страницы укажите название и ярлык (slug)</li>
            <li>В настройках страницы выберите нужный шаблон из списка</li>
            <li>Опубликуйте страницу</li>
        </ol>
        
        <h3>Список страниц и шаблонов:</h3>
        <ul>
            <li><strong>Контакты</strong> (slug: contacts) → Шаблон: Contacts Page</li>
            <li><strong>О компании</strong> (slug: about) → Шаблон: О компании</li>
            <li><strong>Оплата</strong> (slug: payment) → Шаблон: Оплата</li>
            <li><strong>Доставка</strong> (slug: delivery) → Шаблон: Доставка</li>
            <li><strong>Гарантии</strong> (slug: warranty) → Шаблон: Гарантии</li>
        </ul>
    </div>
    <?php
}
