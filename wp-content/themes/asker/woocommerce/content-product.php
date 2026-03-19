<?php
/**
 * The template for displaying product content within loops
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/content-product.php.
 *
 * @see     https://docs.woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 9.4.0
 */
defined( 'ABSPATH' ) || exit;
global $product;
// Ensure visibility.
if ( empty( $product ) || ! $product->is_visible() ) {
	return;
}

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
			$price_html = '<div class="price-with-discount-shop">';
			$price_html .= '<span class="original-price-shop"><del>' . wc_price( $regular_price ) . '</del></span>';
			$price_html .= '<span class="personal-price-shop">' . wc_price( $discounted_price ) . '</span>';
			$price_html .= '<span class="discount-label-shop">-' . esc_html( $discount_percent ) . '%</span>';
			$price_html .= '</div>';
		} else {
			// Обычный товар + персональная скидка
			$discounted_price = $regular_price * ( 1 - $discount_percent / 100 );
			$price_html = '<div class="price-with-discount-shop">';
			$price_html .= '<span class="original-price-shop"><del>' . wc_price( $regular_price ) . '</del></span>';
			$price_html .= '<span class="personal-price-shop">' . wc_price( $discounted_price ) . '</span>';
			$price_html .= '<span class="discount-label-shop">-' . esc_html( $discount_percent ) . '%</span>';
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
<li <?php wc_product_class( '', $product ); ?>>
	<div class="shop-product-card">
		<!-- Кнопка избранного -->
		<button class="favorite-btn" data-product-id="<?php echo esc_attr( $product->get_id() ); ?>"></button>
		
		<!-- Изображение -->
		<div class="shop-product-image">
			<a href="<?php echo esc_url( get_permalink( $product->get_id() ) ); ?>">
				<?php echo woocommerce_get_product_thumbnail(); ?>
			</a>
		</div>
		<!-- Контент -->
		<div class="shop-product-content">
			<!-- Название -->
			<h3 class="shop-product-title">
				<a href="<?php echo esc_url( get_permalink( $product->get_id() ) ); ?>">
					<?php echo esc_html( $product->get_name() ); ?>
				</a>
			</h3>
			<!-- Цена и кнопки -->
			<div class="shop-product-bottom">
				<!-- Цена с персонализацией -->
				<div class="shop-product-price">
					<?php echo $price_html; ?>
				</div>
			<!-- Кнопка "В корзину" с счётчиком -->
			<div class="shop-product-actions">
				<?php
				// Получаем количество этого товара в корзине
				$cart_qty = 0;
				if ( function_exists( 'WC' ) && WC()->cart ) {
					foreach ( WC()->cart->get_cart() as $cart_item ) {
						if ( $cart_item['product_id'] == $product->get_id() ) {
							$cart_qty = $cart_item['quantity'];
							break;
						}
					}
				}
				
				// Кнопка с бейджем количества
				$btn_class = 'button product_type_' . esc_attr( $product->get_type() ) . ' add_to_cart_button ajax_add_to_cart';
				if ( $cart_qty > 0 ) {
					$btn_class .= ' has-items';
				}
				
				echo sprintf(
					'<a href="%s" data-quantity="1" class="%s" data-product_id="%s" data-product_sku="%s" aria-label="%s" rel="nofollow"><span class="btn-text">В корзину</span><span class="btn-cart-count" data-count="%d">%d</span></a>',
					esc_url( $product->add_to_cart_url() ),
					esc_attr( $btn_class ),
					esc_attr( $product->get_id() ),
					esc_attr( $product->get_sku() ),
					esc_attr( $product->add_to_cart_description() ),
					$cart_qty,
					$cart_qty
				);
				?>
			</div>
			</div>
		</div>
	</div>
</li>

<style>
/* Стили для персонализированных цен в shop-карточках */
.shop-product-card .price-with-discount-shop {
    display: flex;
    flex-direction: column;
    gap: 4px;
    align-items: flex-start;
}

.shop-product-card .price-with-discount-shop .original-price-shop {
    font-size: 14px;
    color: #9CA3AF;
    font-weight: 400;
}

.shop-product-card .price-with-discount-shop .original-price-shop del {
    text-decoration: line-through;
}

.shop-product-card .price-with-discount-shop .personal-price-shop {
    font-size: 18px;
    font-weight: 700;
    color: #059669;
}

.shop-product-card .price-with-discount-shop .personal-price-shop .woocommerce-Price-amount {
    color: #059669;
}

.shop-product-card .price-with-discount-shop .discount-label-shop {
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
    .shop-product-card .price-with-discount-shop .personal-price-shop {
        font-size: 16px;
    }
    
    .shop-product-card .price-with-discount-shop .original-price-shop {
        font-size: 12px;
    }
}
</style>
