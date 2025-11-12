<?php
/**
 * The Template for displaying product archives, including the main shop page which is a post type archive
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/archive-product.php.
 *
 * @see     https://docs.woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 8.6.0
 */

defined( 'ABSPATH' ) || exit;

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
            <a href="<?php echo esc_url(home_url('/')); ?>">Главная</a>
            <span class="breadcrumbs__separator">/</span>
            <a href="<?php echo esc_url(get_permalink(wc_get_page_id('shop'))); ?>">Каталог</a>
            <?php
            if (is_product_category() || is_product_tag()) {
                echo '<span class="breadcrumbs__separator">/</span>';
                
                // Если это категория товара
                if (is_product_category()) {
                    $term = get_queried_object();
                    echo '<span class="breadcrumbs__current">' . esc_html($term->name) . '</span>';
                }
                
                // Если это тег товара  
                if (is_product_tag()) {
                    $term = get_queried_object();
                    echo '<span class="breadcrumbs__current">' . esc_html($term->name) . '</span>';
                }
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

        <!-- Кнопка открытия фильтров на мобильных -->
        <button class="filters-toggle-btn" aria-label="Открыть фильтры">
            <span>Фильтры</span>
            <svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M2 4h12M4 8h8M6 12h4" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
            </svg>
        </button>

        <!-- Overlay для мобильных фильтров -->
        <div class="shop-sidebar-overlay"></div>

        <div class="shop-wrapper">
            <!-- Боковая панель с фильтрами -->
            <aside class="shop-sidebar">
                <div class="filters-wrap">
                    <div class="filters-header">
                        <h3 class="filters-title">Фильтры</h3>
                        <button class="filters-close-btn" aria-label="Закрыть фильтры">
                            <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M15 5L5 15M5 5l10 10" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                            </svg>
                        </button>
                    </div>

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
                                    // Проверяем, что URL получен без ошибок
                                    if (is_wp_error($cat_url)) {
                                        $cat_url = '';
                                    }
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

                    <!-- Сброс фильтров -->
                    <a href="<?php echo get_permalink(wc_get_page_id('shop')); ?>" class="filter-reset-btn">
                        Показать все категории
                    </a>

                    <!-- Фильтр по цене -->
                    <?php
                    // Получаем динамический диапазон цен товаров
                    $price_range = asker_get_product_price_range();
                    $default_min = $price_range['min'];
                    $default_max = $price_range['max'];
                    
                    // ВРЕМЕННО: отладка (убрать после проверки)
                    // var_dump($price_range); // Раскомментировать для проверки значений
                    
                    // Используем значения из GET или значения по умолчанию
                    $current_min = isset($_GET['min_price']) && $_GET['min_price'] !== '' 
                        ? intval($_GET['min_price']) 
                        : $default_min;
                    $current_max = isset($_GET['max_price']) && $_GET['max_price'] !== '' 
                        ? intval($_GET['max_price']) 
                        : $default_max;
                    ?>
                    <div class="filter-block">
                        <h4 class="filter-block-title">Цена, ₽</h4>
                        <div class="filter-block-content">
                            <div class="price-range">
                                <input type="number" 
                                       name="min_price" 
                                       placeholder="От" 
                                       class="price-input" 
                                       value="<?php echo esc_attr($current_min); ?>"
                                       min="<?php echo esc_attr($default_min); ?>"
                                       max="<?php echo esc_attr($default_max); ?>"
                                       data-min="<?php echo esc_attr($default_min); ?>"
                                       data-max="<?php echo esc_attr($default_max); ?>">
                                <span class="price-separator">—</span>
                                <input type="number" 
                                       name="max_price" 
                                       placeholder="До" 
                                       class="price-input" 
                                       value="<?php echo esc_attr($current_max); ?>"
                                       min="<?php echo esc_attr($default_min); ?>"
                                       max="<?php echo esc_attr($default_max); ?>"
                                       data-min="<?php echo esc_attr($default_min); ?>"
                                       data-max="<?php echo esc_attr($default_max); ?>">
                            </div>
                            <div class="price-slider-wrapper" 
                                 data-min="<?php echo esc_attr($default_min); ?>" 
                                 data-max="<?php echo esc_attr($default_max); ?>">
                                <input type="range" 
                                       class="price-slider price-slider-min" 
                                       min="<?php echo esc_attr($default_min); ?>" 
                                       max="<?php echo esc_attr($default_max); ?>" 
                                       value="<?php echo esc_attr($current_min); ?>"
                                       step="1000">
                                <input type="range" 
                                       class="price-slider price-slider-max" 
                                       min="<?php echo esc_attr($default_min); ?>" 
                                       max="<?php echo esc_attr($default_max); ?>" 
                                       value="<?php echo esc_attr($current_max); ?>"
                                       step="1000">
                            </div>
                        </div>
                    </div>
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
                        <span class="shop-sort__label">Сортировать по популярности</span>
                        <span class="shop-sort__arrow"></span>
                        <?php woocommerce_catalog_ordering(); ?>
                    </div>
                </div>

                <?php
                if ( woocommerce_product_loop() ) :

                    /**
                     * Hook: woocommerce_before_shop_loop.
                     *
                     * @hooked woocommerce_output_all_notices - 10
                     */
                    do_action( 'woocommerce_before_shop_loop' );

                    woocommerce_product_loop_start();

                    if ( wc_get_loop_prop( 'is_shortcode' ) ) {
                        $columns = absint( wc_get_loop_prop( 'columns' ) );
                        $columns = $columns ? $columns : wc_get_default_products_per_row();
                        woocommerce_products_columns( $columns );
                    }

                    while ( have_posts() ) {
                        the_post();

                        /**
                         * Hook: woocommerce_shop_loop.
                         */
                        do_action( 'woocommerce_shop_loop' );

                        // Используем наш кастомный шаблон
                        wc_get_template_part( 'content', 'product' );
                    }

                    woocommerce_product_loop_end();

                    /**
                     * Hook: woocommerce_after_shop_loop.
                     *
                     * @hooked woocommerce_pagination - 10
                     */
                    do_action( 'woocommerce_after_shop_loop' );

                else :

                    /**
                     * Hook: woocommerce_no_products_found.
                     *
                     * @hooked wc_no_products_found - 10
                     */
                    do_action( 'woocommerce_no_products_found' );

                endif;
                ?>
            </div>
        </div>

    <?php else: ?>
        <!-- Для других страниц WooCommerce (корзина, оформление заказа и т.д.) -->
        <?php woocommerce_content(); ?>
    <?php endif; ?>
</div>

<?php
get_footer();
