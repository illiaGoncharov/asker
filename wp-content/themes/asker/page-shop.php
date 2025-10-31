<?php
/**
 * Шаблон страницы каталога (shop)
 * Работает независимо от WooCommerce
 */

get_header(); ?>

<div class="container">
    <div class="shop-page">
        <h1>Каталог товаров</h1>
        
        <?php if (class_exists('WooCommerce')): ?>
            <!-- WooCommerce установлен -->
            <div class="woocommerce-notice success">
                <p>✅ WooCommerce активен. Показываем каталог товаров.</p>
            </div>
            
            <?php
            // Получаем товары WooCommerce
            $args = [
                'post_type' => 'product',
                'posts_per_page' => 12,
                'post_status' => 'publish'
            ];
            $products = new WP_Query($args);
            ?>
            
            <?php if ($products->have_posts()): ?>
                <div class="products-grid">
                    <?php while ($products->have_posts()): $products->the_post(); ?>
                        <div class="shop-product-card">
                            <?php if (has_post_thumbnail()): ?>
                                <div class="shop-product-image">
                                    <a href="<?php the_permalink(); ?>">
                                        <?php the_post_thumbnail('medium'); ?>
                                    </a>
                                    <!-- Кнопка избранного -->
                                    <button class="favorite-btn" data-product-id="<?php echo get_the_ID(); ?>"></button>
                                </div>
                            <?php endif; ?>
                            
                            <div class="shop-product-content">
                                <h3 class="shop-product-title">
                                    <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                                </h3>
                                
                                <?php
                                $product = wc_get_product(get_the_ID());
                                if ($product):
                                ?>
                                    <div class="shop-product-bottom">
                                        <div class="shop-product-price">
                                            <?php echo $product->get_price_html(); ?>
                                        </div>
                                        
                                        <div class="shop-product-actions">
                                            <a href="<?php echo esc_url($product->add_to_cart_url()); ?>" class="button shop-btn-cart">
                                                В корзину
                                            </a>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>
                
                <?php wp_reset_postdata(); ?>
            <?php else: ?>
                <div class="no-products">
                    <h2>Товары не найдены</h2>
                    <p>Добавьте товары в админке WordPress:</p>
                    <ol>
                        <li>Перейдите в <strong>Товары → Добавить товар</strong></li>
                        <li>Создайте несколько тестовых товаров</li>
                        <li>Добавьте изображения и описания</li>
                        <li>Установите цены</li>
                    </ol>
                </div>
            <?php endif; ?>
            
        <?php else: ?>
            <!-- WooCommerce не установлен -->
            <div class="woocommerce-notice warning">
                <h2>WooCommerce не установлен</h2>
                <p>Для работы каталога необходимо установить плагин WooCommerce.</p>
                
                <div class="installation-steps">
                    <h3>Инструкция по установке:</h3>
                    <ol>
                        <li>Перейдите в <strong>Плагины → Добавить новый</strong></li>
                        <li>Найдите "WooCommerce"</li>
                        <li>Установите и активируйте плагин</li>
                        <li>Запустите мастер настройки WooCommerce</li>
                        <li>Создайте страницы магазина</li>
                    </ol>
                </div>
                
                <div class="test-products">
                    <h3>Тестовые товары (без WooCommerce):</h3>
                    <div class="products-grid">
                        <div class="product-card">
                            <div class="product-image">
                                <div class="product-placeholder">
                                    Изображение товара
                                </div>
                            </div>
                            <h3 class="product-title">Тестовый товар 1</h3>
                            <div class="product-price">1 500 ₽</div>
                            <div class="product-description">Описание тестового товара для демонстрации каталога.</div>
                            <button class="product-button">Подробнее</button>
                        </div>
                        
                        <div class="product-card">
                            <div class="product-image">
                                <div class="product-placeholder">
                                    Изображение товара
                                </div>
                            </div>
                            <h3 class="product-title">Тестовый товар 2</h3>
                            <div class="product-price">2 300 ₽</div>
                            <div class="product-description">Еще один тестовый товар для демонстрации.</div>
                            <button class="product-button">Подробнее</button>
                        </div>
                        
                        <div class="product-card">
                            <div class="product-image">
                                <div class="product-placeholder">
                                    Изображение товара
                                </div>
                            </div>
                            <h3 class="product-title">Тестовый товар 3</h3>
                            <div class="product-price">3 100 ₽</div>
                            <div class="product-description">Третий тестовый товар для полноты каталога.</div>
                            <button class="product-button">Подробнее</button>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php get_footer(); ?>
