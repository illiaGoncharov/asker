<?php
/**
 * Template Name: Категории товаров
 * Страница отображения всех категорий товаров с иконками
 */

get_header();
?>

<div class="container">
        <!-- Хлебные крошки -->
        <nav class="breadcrumbs" aria-label="Breadcrumb">
            <a href="<?php echo esc_url(home_url('/')); ?>">Главная</a>
            <span class="breadcrumbs__separator">/</span>
            <span class="breadcrumbs__current">Категории товаров</span>
        </nav>

        <!-- Заголовок -->
        <h1 class="page-title">Категории товаров</h1>

        <!-- Сетка категорий -->
        <section class="categories-section">
            <div class="categories-grid">
                <?php
                // Получаем все категории товаров WooCommerce
                if (class_exists('WooCommerce')) {
                    $product_categories = get_terms(array(
                        'taxonomy'   => 'product_cat',
                        'hide_empty' => false, // Показываем даже пустые категории
                        'orderby'    => 'menu_order', // По порядку меню
                        'order'      => 'ASC'
                    ));
                    
                    if (!empty($product_categories) && !is_wp_error($product_categories)) {
                        foreach ($product_categories as $category) {
                            // Получаем URL категории
                            $category_url = get_term_link($category);
                            if (is_wp_error($category_url)) {
                                continue;
                            }
                            
                            // Получаем иконку категории (thumbnail_id из WooCommerce)
                            $thumbnail_id = get_term_meta($category->term_id, 'thumbnail_id', true);
                            
                            // Если нет иконки, пытаемся найти по слагу (fallback на старые иконки)
                            $icon_url = '';
                            if ($thumbnail_id) {
                                $icon_url = wp_get_attachment_image_url($thumbnail_id, 'medium');
                            }
                            
                            // Fallback на старые иконки по слагу категории
                            if (!$icon_url) {
                                $slug_to_icon = array(
                                    'teny' => 'tens.svg',
                                    'anody' => 'anods.svg',
                                    'termostaty' => 'termostats.svg',
                                    'stiralnye' => 'washers.svg',
                                    'holodilniki' => 'freezers.svg',
                                );
                                
                                if (isset($slug_to_icon[$category->slug])) {
                                    $icon_url = get_template_directory_uri() . '/assets/images/hero/' . $slug_to_icon[$category->slug];
                                }
                            }
                            
                            // Если всё ещё нет иконки, используем заглушку
                            if (!$icon_url) {
                                $icon_url = get_template_directory_uri() . '/assets/images/hero/tens.svg'; // Дефолтная
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
                        // Если категорий нет, показываем сообщение
                        echo '<p class="no-categories">Категории товаров пока не добавлены.</p>';
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

