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

			<!-- Кнопка "В корзину" -->
			<div class="shop-product-actions">
				<?php
				// Простая кнопка "В корзину" без quantity-wrapper (только на странице товара)
				echo sprintf(
					'<a href="%s" data-quantity="1" class="button product_type_%s add_to_cart_button ajax_add_to_cart" data-product_id="%s" data-product_sku="%s" aria-label="%s" rel="nofollow">В корзину</a>',
					esc_url( $product->add_to_cart_url() ),
					esc_attr( $product->get_type() ),
					esc_attr( $product->get_id() ),
					esc_attr( $product->get_sku() ),
					esc_attr( $product->add_to_cart_description() )
				);
				?>
			</div>
			</div>
		</div>
	</div>
</li>
