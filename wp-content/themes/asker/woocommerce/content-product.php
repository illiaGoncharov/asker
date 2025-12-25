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
				<!-- Цена -->
				<div class="shop-product-price">
					<?php echo $product->get_price_html(); ?>
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
