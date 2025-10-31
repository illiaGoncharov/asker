<?php
/**
 * Основной шаблон WooCommerce
 * Используется для страницы Shop и других страниц WooCommerce
 */

get_header();

/**
 * Проверяем что WooCommerce активен
 */
if (!class_exists('WooCommerce')) {
    echo '<div class="container"><p>WooCommerce не установлен</p></div>';
    get_footer();
    return;
}
?>

<div class="container">
        <?php
        // Если это страница магазина (архив товаров)
        if (is_shop() || is_product_taxonomy()):
        ?>
            <!-- Хлебные крошки -->
            <nav class="breadcrumbs">
                <?php
                echo '<a href="' . home_url() . '">Главная</a>';
                echo ' / ';
                
                if (is_product_category() || is_product_tag()) {
                    echo '<a href="' . get_permalink(wc_get_page_id('shop')) . '">Каталог</a>';
                    echo ' / ';
                    
                    // Если это категория товара
                    if (is_product_category()) {
                        $term = get_queried_object();
                        echo '<span>' . esc_html($term->name) . '</span>';
                    }
                    
                    // Если это тег товара  
                    if (is_product_tag()) {
                        $term = get_queried_object();
                        echo '<span>' . esc_html($term->name) . '</span>';
                    }
                } else {
                    echo '<span>Каталог</span>';
                }
                ?>
            </nav>

            <!-- Заголовок страницы -->
            <h1 class="page-title">
                <?php
                if (is_shop()) {
                    echo 'Каталог';
                } elseif (is_product_category() || is_product_tag()) {
                    echo single_term_title('', false);
                } else {
                    echo 'Товары';
                }
                ?>
            </h1>

            <div class="shop-wrapper">
                <!-- Боковая панель с фильтрами -->
                <aside class="shop-sidebar">
                    <div class="filters-wrap">
                        <h3 class="filters-title">Фильтры</h3>

                        <!-- Фильтр по категориям -->
                        <div class="filter-block">
                            <h4 class="filter-block-title">Категория</h4>
                            <div class="filter-block-content">
                                <?php
                                $product_categories = get_terms([
                                    'taxonomy' => 'product_cat',
                                    'hide_empty' => true,
                                    'parent' => 0,
                                ]);

                                if (!empty($product_categories) && !is_wp_error($product_categories)):
                                    foreach ($product_categories as $category):
                                        $checked = '';
                                        $cat_url = get_term_link($category);
                                        if (is_product_category() && get_queried_object_id() == $category->term_id) {
                                            $checked = 'checked';
                                        }
                                ?>
                                    <label class="filter-checkbox">
                                        <input type="checkbox" 
                                               name="category[]" 
                                               value="<?php echo esc_attr($category->slug); ?>" 
                                               data-url="<?php echo esc_url($cat_url); ?>"
                                               <?php echo $checked; ?>>
                                        <span><?php echo esc_html($category->name); ?></span>
                                    </label>
                                <?php
                                    endforeach;
                                endif;
                                ?>
                            </div>
                        </div>

                        <!-- Фильтр по цене -->
                        <div class="filter-block">
                            <h4 class="filter-block-title">Цена, ₽</h4>
                            <div class="filter-block-content">
                                <div class="price-range">
                                    <input type="number" 
                                           name="min_price" 
                                           placeholder="От" 
                                           class="price-input" 
                                           value="<?php echo isset($_GET['min_price']) ? esc_attr($_GET['min_price']) : '6000'; ?>"
                                           min="0">
                                    <span class="price-separator">—</span>
                                    <input type="number" 
                                           name="max_price" 
                                           placeholder="До" 
                                           class="price-input" 
                                           value="<?php echo isset($_GET['max_price']) ? esc_attr($_GET['max_price']) : '256000'; ?>"
                                           min="0">
                                </div>
                                
                                <!-- Слайдер цены -->
                                <div class="price-slider-wrapper">
                                    <div class="price-slider" id="price-slider"></div>
                                </div>
                            </div>
                        </div>

                        <!-- Сброс фильтров -->
                        <a href="<?php echo get_permalink(wc_get_page_id('shop')); ?>" class="filter-reset-btn">
                            Показать все категории
                        </a>
                    </div>
                </aside>

                <!-- Основной контент с товарами -->
                <div class="shop-main">
                    <!-- Сортировка -->
                    <div class="shop-controls">
                        <div class="shop-result-count">
                            <?php
                            $total = wc_get_loop_prop('total');
                            $showing_text = sprintf(
                                _n('Показан %s товар', 'Показано %s товаров', $total, 'asker'),
                                '<strong>' . $total . '</strong>'
                            );
                            echo $showing_text;
                            ?>
                        </div>
                        <div class="shop-sort">
                            <?php woocommerce_catalog_ordering(); ?>
                        </div>
                    </div>

                    <?php
                    if (woocommerce_product_loop()):
                        woocommerce_product_loop_start();

                        if (wc_get_loop_prop('total')):
                            while (have_posts()):
                                the_post();
                                
                                /**
                                 * Выводим кастомную карточку товара
                                 */
                                global $product;
                                ?>
                                <div class="shop-product-card">
                                    <!-- Кнопка избранного -->
                                    <button class="favorite-btn" data-product-id="<?php echo get_the_ID(); ?>" aria-label="Добавить в избранное"></button>

                                    <!-- Изображение -->
                                    <div class="shop-product-image">
                                        <a href="<?php the_permalink(); ?>">
                                            <?php echo woocommerce_get_product_thumbnail('medium'); ?>
                                        </a>
                                    </div>

                                    <!-- Название -->
                                    <h3 class="shop-product-title">
                                        <a href="<?php the_permalink(); ?>">
                                            <?php the_title(); ?>
                                        </a>
                                    </h3>

                                    <!-- Цена -->
                                    <div class="shop-product-price">
                                        <?php echo $product->get_price_html(); ?>
                                    </div>

                                    <!-- Кнопки -->
                                    <div class="shop-product-actions">
                                        <a href="<?php the_permalink(); ?>" class="shop-btn-details">
                                            Подробнее
                                        </a>
                                        <?php
                                        echo apply_filters(
                                            'woocommerce_loop_add_to_cart_link',
                                            sprintf(
                                                '<a href="%s" data-quantity="%s" class="%s shop-btn-cart" %s>%s</a>',
                                                esc_url($product->add_to_cart_url()),
                                                esc_attr(isset($args['quantity']) ? $args['quantity'] : 1),
                                                esc_attr(isset($args['class']) ? $args['class'] : 'button'),
                                                isset($args['attributes']) ? wc_implode_html_attributes($args['attributes']) : '',
                                                esc_html('В корзину')
                                            ),
                                            $product,
                                            $args
                                        );
                                        ?>
                                    </div>
                                </div>
                                <?php
                            endwhile;
                        endif;

                        woocommerce_product_loop_end();
                    else:
                        echo '<div class="no-products"><p>Товары не найдены</p></div>';
                    endif;
                    ?>

                    <!-- Пагинация -->
                    <?php woocommerce_pagination(); ?>
                </div>
            </div>

        <?php else: ?>
            <!-- Для других страниц WooCommerce (корзина, оформление заказа и т.д.) -->
            <?php woocommerce_content(); ?>
        <?php endif; ?>
</div>

<?php get_footer(); ?>

