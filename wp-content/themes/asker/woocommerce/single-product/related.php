<?php
/**
 * Related Products - использует точно такую же структуру как на главной
 *
 * @see         https://docs.woocommerce.com/document/template-structure/
 * @package     WooCommerce\Templates
 * @version     10.3.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( $related_products ) : ?>

<section class="products-section related">
    <div class="container">
        <h2 class="section-title">Похожие товары</h2>
        <div class="products-grid">
            <?php foreach ( $related_products as $related_product ) : 
                // $related_product уже является объектом WC_Product
                $product = is_a( $related_product, 'WC_Product' ) ? $related_product : wc_get_product( $related_product );
                if ( $product ) :
                    $product_id = $product->get_id();
                    $product_image = wp_get_attachment_image_src( get_post_thumbnail_id( $product_id ), 'medium' );
                    $product_url = get_permalink( $product_id );
                    
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
                                $price_html = '<div class="price-with-discount">';
                                $price_html .= '<span class="original-price"><del>' . wc_price( $regular_price ) . '</del></span>';
                                $price_html .= '<span class="personal-price">' . wc_price( $discounted_price ) . '</span>';
                                $price_html .= '<span class="discount-label">-' . esc_html( $discount_percent ) . '%</span>';
                                $price_html .= '</div>';
                            } else {
                                // Обычный товар + персональная скидка
                                $discounted_price = $regular_price * ( 1 - $discount_percent / 100 );
                                $price_html = '<div class="price-with-discount">';
                                $price_html .= '<span class="original-price"><del>' . wc_price( $regular_price ) . '</del></span>';
                                $price_html .= '<span class="personal-price">' . wc_price( $discounted_price ) . '</span>';
                                $price_html .= '<span class="discount-label">-' . esc_html( $discount_percent ) . '%</span>';
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
                <div class="product-card">
                    <button class="favorite-btn" data-product-id="<?php echo esc_attr( $product_id ); ?>"></button>
                    <a href="<?php echo esc_url( $product_url ); ?>" class="product-image-link">
                        <div class="product-image">
                            <?php if ( $product_image ) : ?>
                                <img src="<?php echo esc_url( $product_image[0] ); ?>" alt="<?php echo esc_attr( $product->get_name() ); ?>">
                            <?php else : 
                                // Используем placeholder из настроек или SVG заглушку
                                $placeholder_id = get_theme_mod( 'default_product_image' );
                                if ( $placeholder_id ) {
                                    $placeholder_url = wp_get_attachment_image_url( $placeholder_id, 'medium' );
                                    if ( $placeholder_url ) {
                                        echo '<img src="' . esc_url( $placeholder_url ) . '" alt="">';
                                    }
                                } else {
                                    echo '<img src="data:image/svg+xml,%3Csvg xmlns=\'http://www.w3.org/2000/svg\' viewBox=\'0 0 300 300\'%3E%3Crect fill=\'%23f5f5f5\' width=\'300\' height=\'300\'/%3E%3C/svg%3E" alt="">';
                                }
                            endif; ?>
                        </div>
                    </a>
                    <h3 class="product-title">
                        <a href="<?php echo esc_url( $product_url ); ?>"><?php echo esc_html( $product->get_name() ); ?></a>
                    </h3>
                    <div class="product-bottom">
                        <div class="product-price"><?php echo $price_html; ?></div>
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
                        $btn_class = 'btn-add-cart add_to_cart_button';
                        if ( $cart_qty > 0 ) {
                            $btn_class .= ' has-items';
                        }
                        ?>
                        <button class="<?php echo esc_attr( $btn_class ); ?>" data-product-id="<?php echo esc_attr( $product_id ); ?>"><span class="btn-text">В корзину</span><span class="btn-cart-count" data-count="<?php echo esc_attr( $cart_qty ); ?>"><?php echo esc_html( $cart_qty ); ?></span></button>
                    </div>
                </div>
            <?php
                endif;
            endforeach; ?>
        </div>
    </div>
</section>

<style>
/* Стили для персонализированных цен в карточках */
.product-card .price-with-discount {
    display: flex;
    flex-direction: column;
    gap: 4px;
    align-items: flex-start;
}

.product-card .price-with-discount .original-price {
    font-size: 14px;
    color: #9CA3AF;
    font-weight: 400;
}

.product-card .price-with-discount .original-price del {
    text-decoration: line-through;
}

.product-card .price-with-discount .personal-price {
    font-size: 18px;
    font-weight: 700;
    color: #059669;
}

.product-card .price-with-discount .personal-price .woocommerce-Price-amount {
    color: #059669;
}

.product-card .price-with-discount .discount-label {
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
    .product-card .price-with-discount .personal-price {
        font-size: 16px;
    }
    
    .product-card .price-with-discount .original-price {
        font-size: 12px;
    }
}
</style>

<?php
endif;

wp_reset_postdata();
