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
                $is_logged_in = is_user_logged_in();
                $wishlist_items = $is_logged_in ? get_user_meta($customer_id, 'asker_wishlist', true) : array();
                
                // Для авторизованных - берём из user_meta
                // Для гостей - JS загрузит из localStorage
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
                                $sku = $product->get_sku();
                                
                                // ========== ПЕРСОНАЛИЗАЦИЯ ЦЕН ==========
                                $has_discount = false;
                                $discount_percent = 0;
                                $price_html = '';
                                
                                // Проверяем авторизацию и скидку пользователя
                                if ( is_user_logged_in() ) {
                                    $user_id = get_current_user_id();
                                    
                                    // Получаем скидку пользователя
                                    if ( function_exists( 'asker_get_total_discount' ) ) {
                                        $discount_percent = asker_get_total_discount( $user_id );
                                    } else {
                                        // Fallback: получаем напрямую из мета-полей
                                        $level_discount = get_user_meta( $user_id, 'user_level_discount', true );
                                        $individual_discount = get_user_meta( $user_id, 'individual_discount', true );
                                        $discount_percent = max( floatval( $level_discount ), floatval( $individual_discount ) );
                                    }
                                    
                                    if ( $discount_percent > 0 ) {
                                        $has_discount = true;
                                    }
                                }
                                
                                // Формируем HTML цены
                                if ( $has_discount ) {
                                    $regular_price = $product->get_regular_price();
                                    $sale_price = $product->get_sale_price();
                                    
                                    if ( ! empty( $regular_price ) ) {
                                        if ( ! empty( $sale_price ) ) {
                                            // Товар со скидкой + персональная скидка
                                            $discounted_price = $sale_price * ( 1 - $discount_percent / 100 );
                                            $price_html = '<div class="price-with-discount-wishlist">';
                                            $price_html .= '<span class="original-price-wishlist"><del>' . wc_price( $regular_price ) . '</del></span>';
                                            $price_html .= '<span class="personal-price-wishlist">' . wc_price( $discounted_price ) . '</span>';
                                            $price_html .= '<span class="discount-label-wishlist">-' . esc_html( $discount_percent ) . '%</span>';
                                            $price_html .= '</div>';
                                        } else {
                                            // Обычный товар + персональная скидка
                                            $discounted_price = $regular_price * ( 1 - $discount_percent / 100 );
                                            $price_html = '<div class="price-with-discount-wishlist">';
                                            $price_html .= '<span class="original-price-wishlist"><del>' . wc_price( $regular_price ) . '</del></span>';
                                            $price_html .= '<span class="personal-price-wishlist">' . wc_price( $discounted_price ) . '</span>';
                                            $price_html .= '<span class="discount-label-wishlist">-' . esc_html( $discount_percent ) . '%</span>';
                                            $price_html .= '</div>';
                                        }
                                    } else {
                                        // На всякий случай, если цены нет
                                        $price_html = $product->get_price_html();
                                    }
                                } else {
                                    // Обычная цена без персональной скидки
                                    $price_html = $product->get_price_html();
                                }
                                
                                // Убираем копейки из цены
                                $price_html = preg_replace( '/,00/', '', $price_html );
                                // ========== КОНЕЦ ПЕРСОНАЛИЗАЦИИ ==========
                                ?>
                                <div class="wishlist-item">
                                    <a href="<?php echo esc_url($product_url); ?>" class="wishlist-item-image">
                                        <?php if ($product_image) : ?>
                                            <img src="<?php echo esc_url($product_image[0]); ?>" alt="">
                                        <?php else : ?>
                                            <div class="product-placeholder"></div>
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
                                    <div class="wishlist-item-price"><?php echo $price_html; ?></div>
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
                                        <?php
                                        // Получаем количество этого товара в корзине
                                        $cart_qty = 0;
                                        if ( function_exists( 'WC' ) && WC()->cart ) {
                                            foreach ( WC()->cart->get_cart() as $cart_item ) {
                                                if ( $cart_item['product_id'] == $product_id ) {
                                                    $cart_qty = $cart_item['quantity'];
                                                    break;
                                                }
                                            }
                                        }
                                        $btn_class = 'wishlist-item-add-cart btn-add-cart add_to_cart_button';
                                        if ( $cart_qty > 0 ) {
                                            $btn_class .= ' has-items';
                                        }
                                        ?>
                                        <button class="<?php echo esc_attr( $btn_class ); ?>" data-product-id="<?php echo esc_attr($product_id); ?>"><span class="btn-text">В корзину</span><span class="btn-cart-count" data-count="<?php echo esc_attr( $cart_qty ); ?>"><?php echo esc_html( $cart_qty ); ?></span></button>
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
                    <?php if (!$is_logged_in) : ?>
                        <!-- Для гостей: JS загрузит из localStorage -->
                        <div class="wishlist-loading">Загрузка избранного...</div>
                    <?php else : ?>
                        <div class="no-products">
                            <p>В вашем избранном пока нет товаров.</p>
                            <a href="<?php echo esc_url(home_url('/shop')); ?>" class="btn-primary">Перейти в каталог</a>
                        </div>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<style>
/* Стили для персонализированных цен в избранном */
.wishlist-item-price .price-with-discount-wishlist {
    display: flex;
    flex-direction: column;
    gap: 4px;
    align-items: flex-start;
}

.wishlist-item-price .price-with-discount-wishlist .original-price-wishlist {
    font-size: 14px;
    color: #9CA3AF;
    font-weight: 400;
}

.wishlist-item-price .price-with-discount-wishlist .original-price-wishlist del {
    text-decoration: line-through;
}

.wishlist-item-price .price-with-discount-wishlist .personal-price-wishlist {
    font-size: 20px;
    font-weight: 700;
    color: #059669;
}

.wishlist-item-price .price-with-discount-wishlist .personal-price-wishlist .woocommerce-Price-amount {
    color: #059669;
}

.wishlist-item-price .price-with-discount-wishlist .discount-label-wishlist {
    display: inline-block;
    background: linear-gradient(135deg, #059669 0%, #10B981 100%);
    color: white;
    padding: 2px 8px;
    border-radius: 12px;
    font-size: 12px;
    font-weight: 600;
    box-shadow: 0 1px 3px rgba(5, 150, 105, 0.2);
}

/* Адаптив */
@media (max-width: 768px) {
    .wishlist-item-price .price-with-discount-wishlist .personal-price-wishlist {
        font-size: 18px;
    }
    
    .wishlist-item-price .price-with-discount-wishlist .original-price-wishlist {
        font-size: 12px;
    }
}
</style>

<?php get_footer(); ?>
