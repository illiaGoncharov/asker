<?php
/**
 * Template Name: Каталог с фильтрами
 * Шаблон страницы каталога с сеткой 3 колонки
 */

get_header(); 
?>

<div class="container">
        <!-- Хлебные крошки -->
        <?php if (function_exists('woocommerce_breadcrumb')): ?>
            <nav class="woocommerce-breadcrumb" aria-label="Breadcrumb">
                <?php woocommerce_breadcrumb(); ?>
            </nav>
        <?php endif; ?>

        <h1 class="page-title">Каталог</h1>

        <?php if (have_posts()): while (have_posts()): the_post(); ?>
            <?php the_content(); ?>
        <?php endwhile; endif; ?>

        <div class="catalog-wrapper">
            <!-- Боковая панель с фильтрами -->
            <aside class="catalog-sidebar">
                <div class="filters-wrap">
                    <h3 class="filters-title">Фильтры</h3>

                    <!-- Фильтр по категориям -->
                    <div class="filter-block">
                        <h4 class="filter-block-title">Категория</h4>
                        <div class="filter-block-content">
                            <?php
                            $product_categories = get_terms([
                                'taxonomy' => 'product_cat',
                                'hide_empty' => false,
                            ]);

                            if (!empty($product_categories) && !is_wp_error($product_categories)):
                                foreach ($product_categories as $category):
                            ?>
                                <label class="filter-checkbox">
                                    <input type="checkbox" name="category[]" value="<?php echo esc_attr($category->slug); ?>">
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
                                <input type="number" name="min_price" placeholder="От" class="price-input" value="6000">
                                <span class="price-separator">—</span>
                                <input type="number" name="max_price" placeholder="До" class="price-input" value="256000">
                            </div>
                        </div>
                    </div>

                    <!-- Кнопка сброса фильтров -->
                    <button class="filter-reset-btn">Показать все категории</button>
                </div>
            </aside>

            <!-- Основной контент с товарами -->
            <div class="catalog-main">
                <!-- Сортировка -->
                <div class="catalog-controls">
                    <div class="catalog-sort">
                        <label for="product-catalog-sort">Сортировать по популярности</label>
                        <select id="product-catalog-sort" name="orderby" class="catalog-sort-select">
                            <option value="popularity">Популярности</option>
                            <option value="rating">Рейтингу</option>
                            <option value="date">Новизне</option>
                            <option value="price">Цене: по возрастанию</option>
                            <option value="price-desc">Цене: по убыванию</option>
                        </select>
                    </div>
                </div>

                <!-- Сетка товаров -->
                <?php
                $args = [
                    'post_type' => 'product',
                    'posts_per_page' => 12,
                    'post_status' => 'publish'
                ];
                $products = new WP_Query($args);
                ?>

                <?php if ($products->have_posts()): ?>
                    <div class="catalog-products-grid">
                        <?php while ($products->have_posts()): $products->the_post(); 
                            global $product;
                        ?>
                            <div class="catalog-product-card">
                                <!-- Кнопка избранного -->
                                <button class="favorite-btn" data-product-id="<?php echo get_the_ID(); ?>" aria-label="Добавить в избранное">
                                    <span class="heart-icon"></span>
                                </button>

                                <!-- Изображение товара -->
                                <div class="catalog-product-image">
                                    <?php if (has_post_thumbnail()): ?>
                                        <a href="<?php the_permalink(); ?>">
                                            <?php the_post_thumbnail('medium'); ?>
                                        </a>
                                    <?php else: ?>
                                        <div class="product-placeholder">Нет изображения</div>
                                    <?php endif; ?>
                                </div>

                                <!-- Название товара -->
                                <h3 class="catalog-product-title">
                                    <a href="<?php the_permalink(); ?>">
                                        <?php the_title(); ?>
                                    </a>
                                </h3>

                                <!-- Цена -->
                                <?php if ($product): ?>
                                    <div class="catalog-product-price">
                                        <?php echo $product->get_price_html(); ?>
                                    </div>

                                    <!-- Кнопки действий -->
                                    <div class="catalog-product-actions">
                                        <a href="<?php the_permalink(); ?>" class="catalog-btn-details">
                                            Подробнее
                                        </a>
                                        <button class="catalog-btn-cart" data-product-id="<?php echo get_the_ID(); ?>">
                                            В корзину
                                        </button>
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php endwhile; ?>
                    </div>

                    <?php wp_reset_postdata(); ?>
                <?php else: ?>
                    <div class="no-products">
                        <h2>Товары не найдены</h2>
                        <p>Попробуйте изменить параметры фильтра</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
</div>

<?php get_footer(); ?>

