<?php
/**
 * Template Name: Избранное
 */

get_header(); ?>

<div class="container section">
    <!-- Хлебные крошки -->
    <nav class="breadcrumbs" aria-label="Breadcrumb">
        <a href="<?php echo esc_url(home_url('/')); ?>">Главная</a>
        <span class="breadcrumbs__separator">/</span>
        <span class="breadcrumbs__current">Избранное</span>
    </nav>
    
    <div class="wishlist-page">
        <h1 class="page-title">Избранное</h1>
        
        <div class="content-section">
            <div class="wishlist-products">
                <?php
                $customer_id = get_current_user_id();
                $wishlist_items = get_user_meta($customer_id, 'asker_wishlist', true);
                
                // Если в user_meta пусто, пытаемся синхронизировать с localStorage через JS
                if (empty($wishlist_items) || !is_array($wishlist_items)) {
                    $wishlist_items = array();
                }
                
                // Пагинация для избранного
                $paged = isset($_GET['wishlist_page']) ? max(1, intval($_GET['wishlist_page'])) : 1;
                $per_page = 10;
                $total_items = count($wishlist_items);
                $total_pages = ceil($total_items / $per_page);
                $offset = ($paged - 1) * $per_page;
                $paged_items = array_slice($wishlist_items, $offset, $per_page);
                
                if (!empty($paged_items)) :
                    ?>
                    <div class="wishlist-list">
                        <?php foreach ($paged_items as $product_id) :
                            $product = wc_get_product($product_id);
                            if ($product && $product->is_visible()) :
                                $product_image = wp_get_attachment_image_src(get_post_thumbnail_id($product_id), 'medium');
                                $product_url = get_permalink($product_id);
                                $price = $product->get_price_html();
                                $sku = $product->get_sku();
                                ?>
                                <div class="wishlist-item">
                                    <a href="<?php echo esc_url($product_url); ?>" class="wishlist-item-image">
                                        <?php if ($product_image) : ?>
                                            <img src="<?php echo esc_url($product_image[0]); ?>" alt="">
                                        <?php else : ?>
                                            <div class="product-placeholder"><?php echo esc_html($product->get_name()); ?></div>
                                        <?php endif; ?>
                                    </a>
                                    <div class="wishlist-item-info">
                                        <h3 class="wishlist-item-title">
                                            <a href="<?php echo esc_url($product_url); ?>"><?php echo esc_html($product->get_name()); ?></a>
                                        </h3>
                                        <?php if ($sku) : ?>
                                            <p class="wishlist-item-sku">Артикул: <?php echo esc_html($sku); ?></p>
                                        <?php endif; ?>
                                    </div>
                                    <div class="wishlist-item-price"><?php echo $price; ?></div>
                                    <button class="wishlist-item-remove" data-product-id="<?php echo esc_attr($product_id); ?>" aria-label="Удалить из избранного">
                                        <svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                                            <path d="M12 4L4 12M4 4L12 12" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                        </svg>
                                    </button>
                                    <div class="wishlist-item-right">
                                        <div class="wishlist-item-quantity">
                                            <button class="quantity-btn quantity-minus" data-product-id="<?php echo esc_attr($product_id); ?>">-</button>
                                            <input type="number" class="quantity-input" value="1" min="1" data-product-id="<?php echo esc_attr($product_id); ?>">
                                            <button class="quantity-btn quantity-plus" data-product-id="<?php echo esc_attr($product_id); ?>">+</button>
                                        </div>
                                        <button class="wishlist-item-add-cart btn-add-cart add_to_cart_button" data-product-id="<?php echo esc_attr($product_id); ?>">В корзину</button>
                                    </div>
                                </div>
                            <?php
                            endif;
                        endforeach; ?>
                    </div>
                    
                    <?php if ($total_pages > 1) : ?>
                    <div class="wishlist-pagination">
                        <?php if ($paged > 1) : ?>
                            <a href="?wishlist_page=<?php echo $paged - 1; ?>" class="pagination-btn pagination-btn--prev">
                                <svg width="16" height="16" viewBox="0 0 16 16" fill="none">
                                    <path d="M10 12L6 8L10 4" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                                Предыдущая
                            </a>
                        <?php endif; ?>
                        
                        <div class="pagination-numbers">
                            <?php
                            // Показываем максимум 7 страниц
                            $range = 3;
                            $start = max(1, $paged - $range);
                            $end = min($total_pages, $paged + $range);
                            
                            if ($start > 1) {
                                echo '<a href="?wishlist_page=1" class="page-number">1</a>';
                                if ($start > 2) echo '<span class="page-dots">...</span>';
                            }
                            
                            for ($i = $start; $i <= $end; $i++) :
                                if ($i === $paged) : ?>
                                    <span class="page-number page-number--active"><?php echo $i; ?></span>
                                <?php else : ?>
                                    <a href="?wishlist_page=<?php echo $i; ?>" class="page-number"><?php echo $i; ?></a>
                                <?php endif;
                            endfor;
                            
                            if ($end < $total_pages) {
                                if ($end < $total_pages - 1) echo '<span class="page-dots">...</span>';
                                echo '<a href="?wishlist_page=' . $total_pages . '" class="page-number">' . $total_pages . '</a>';
                            }
                            ?>
                        </div>
                        
                        <?php if ($paged < $total_pages) : ?>
                            <a href="?wishlist_page=<?php echo $paged + 1; ?>" class="pagination-btn pagination-btn--next">
                                Следующая
                                <svg width="16" height="16" viewBox="0 0 16 16" fill="none">
                                    <path d="M6 4L10 8L6 12" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                            </a>
                        <?php endif; ?>
                    </div>
                    <?php endif; ?>
                    
                <?php else : ?>
                    <div class="no-products">
                        <p>В вашем избранном пока нет товаров.</p>
                        <a href="<?php echo esc_url(home_url('/shop')); ?>" class="btn-primary">Перейти в каталог</a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php get_footer(); ?>

