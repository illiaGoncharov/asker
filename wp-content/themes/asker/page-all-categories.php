<?php
/**
 * Template Name: Все категории
 * Страница отображения всех категорий товаров с иконками
 * 
 * Этот шаблон автоматически применяется для страницы со слагом all-categories
 */

// Проверяем, что мы на правильной странице
if (!defined('ABSPATH')) {
    exit;
}

get_header();
?>

<div class="container">
    <!-- Хлебные крошки -->
    <nav class="breadcrumbs" aria-label="Breadcrumb">
        <a href="<?php echo esc_url(home_url('/')); ?>">Главная</a>
        <span class="breadcrumbs__separator">/</span>
        <span class="breadcrumbs__current">Все категории</span>
    </nav>

    <!-- Заголовок -->
    <h1 class="page-title">Все категории</h1>

    <!-- Сетка категорий -->
    <section class="categories-nav all-categories-page">
        <div class="categories-grid">
            <?php
            // Получаем все активные категории товаров WooCommerce (как на главной)
            if (class_exists('WooCommerce')) {
                // Пробуем разные варианты запроса
                $product_categories = get_terms(array(
                    'taxonomy'   => 'product_cat',
                    'hide_empty' => true, // Показываем только категории с товарами (как на главной)
                    'orderby'    => 'menu_order',
                    'order'      => 'ASC',
                    'number'     => 0 // Без ограничения
                ));
                
                // Если вернулась ошибка или пусто, пробуем без menu_order
                if (is_wp_error($product_categories) || empty($product_categories)) {
                    $product_categories = get_terms(array(
                        'taxonomy'   => 'product_cat',
                        'hide_empty' => true,
                        'orderby'    => 'name',
                        'order'      => 'ASC'
                    ));
                }
                
                if (!empty($product_categories) && !is_wp_error($product_categories)) {
                    foreach ($product_categories as $category) {
                        // Получаем URL категории (ведет на каталог с фильтром)
                        $category_url = get_term_link($category);
                        if (is_wp_error($category_url)) {
                            continue;
                        }
                        
                        // Получаем иконку категории (thumbnail_id из WooCommerce)
                        $thumbnail_id = get_term_meta($category->term_id, 'thumbnail_id', true);
                        $icon_url = '';
                        
                        if ($thumbnail_id) {
                            // Проверяем тип файла для корректной обработки SVG
                            $mime_type = get_post_mime_type($thumbnail_id);
                            
                            if ($mime_type === 'image/svg+xml') {
                                // Для SVG используем прямой URL
                                $icon_url = wp_get_attachment_url($thumbnail_id);
                            } else {
                                // Для обычных изображений используем размер medium
                                $icon_url = wp_get_attachment_image_url($thumbnail_id, 'medium');
                                if (!$icon_url) {
                                    // Если нет medium, используем полный размер
                                    $icon_url = wp_get_attachment_url($thumbnail_id);
                                }
                            }
                        }
                        
                        // Fallback на иконки по названию категории
                        if (!$icon_url) {
                            $category_name_lower = mb_strtolower($category->name);
                            $category_slug_lower = mb_strtolower($category->slug);
                            
                            // Проверяем по названию и слагу
                            if (stripos($category_name_lower, 'тэн') !== false || 
                                stripos($category_name_lower, 'водонагревател') !== false ||
                                stripos($category_slug_lower, 'heaters') !== false ||
                                stripos($category_slug_lower, 'ten') !== false) {
                                $icon_url = get_template_directory_uri() . '/assets/images/hero/tens.svg';
                            } elseif (stripos($category_name_lower, 'анод') !== false ||
                                      stripos($category_slug_lower, 'anod') !== false) {
                                $icon_url = get_template_directory_uri() . '/assets/images/hero/anods.svg';
                            } elseif (stripos($category_name_lower, 'термостат') !== false ||
                                      stripos($category_slug_lower, 'thermostat') !== false) {
                                $icon_url = get_template_directory_uri() . '/assets/images/hero/termostats.svg';
                            } elseif (stripos($category_name_lower, 'стирал') !== false ||
                                      stripos($category_slug_lower, 'washer') !== false) {
                                $icon_url = get_template_directory_uri() . '/assets/images/hero/washers.svg';
                            } elseif (stripos($category_name_lower, 'холодил') !== false ||
                                      stripos($category_slug_lower, 'freezer') !== false) {
                                $icon_url = get_template_directory_uri() . '/assets/images/hero/freezers.svg';
                            }
                        }
                        
                        // Если всё ещё нет иконки, используем заглушку
                        if (!$icon_url) {
                            $icon_url = get_template_directory_uri() . '/assets/images/hero/tens.svg';
                        }
                        ?>
                        <a href="<?php echo esc_url($category_url); ?>" class="category-item">
                            <div class="category-icon">
                                <img src="<?php echo esc_url($icon_url); ?>" alt="<?php echo esc_attr($category->name); ?>">
                            </div>
                            <span class="category-name"><?php echo esc_html($category->name); ?></span>
                        </a>
                        <?php
                    }
                } else {
                    // Если категорий нет, показываем сообщение с отладкой
                    $debug_info = '';
                    if (current_user_can('manage_options')) {
                        $all_terms = get_terms(array('taxonomy' => 'product_cat', 'hide_empty' => false));
                        $debug_info = '<p style="color: red; font-size: 12px;">Отладка: WooCommerce ' . (class_exists('WooCommerce') ? 'активен' : 'НЕ активен') . 
                                     '. Найдено категорий (включая пустые): ' . (is_array($all_terms) ? count($all_terms) : '0') . 
                                     '. Ошибка get_terms: ' . (is_wp_error($product_categories) ? $product_categories->get_error_message() : 'нет') . '</p>';
                    }
                    echo '<p class="no-categories">Категории товаров пока не добавлены.</p>' . $debug_info;
                }
            } else {
                // Если WooCommerce не установлен
                echo '<p class="no-categories">WooCommerce не установлен. Установите плагин для отображения категорий.</p>';
            }
            ?>
        </div>
    </section>
</div>

<?php get_footer(); ?>

