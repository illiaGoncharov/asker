<?php
/**
 * The template for displaying product content in the single-product.php template
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/content-single-product.php.
 *
 * @see     https://docs.woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 9.0.0
 */

defined( 'ABSPATH' ) || exit;

global $product;

// WooCommerce автоматически устанавливает $product через хук woocommerce_single_product_summary
// Но на всякий случай проверяем и инициализируем если нужно
if ( empty( $product ) || ! is_a( $product, 'WC_Product' ) ) {
	$product = wc_get_product( get_the_ID() );
}

/**
 * Hook: woocommerce_before_single_product.
 *
 * @hooked woocommerce_output_all_notices - 10
 */
do_action( 'woocommerce_before_single_product' );

if ( post_password_required() ) {
	echo get_the_password_form();
	return;
}

// КРИТИЧНО: Если $product не установлен - выводим ошибку, но НЕ делаем return
if ( ! $product ) {
	echo '<div class="woocommerce"><div class="woocommerce-notices-wrapper"></div><p class="woocommerce-info">Товар не найден. Post ID: ' . get_the_ID() . '</p></div>';
	// НЕ делаем return - пусть WooCommerce сам обработает
}
?>

<?php if ( $product ) : ?>
<div id="product-<?php the_ID(); ?>" <?php echo function_exists( 'wc_product_class' ) ? wc_product_class( 'single-product-page', $product ) : 'class="single-product-page"'; ?>>

	<div class="product-main">
		<!-- Галерея изображений слева -->
		<div class="product-gallery">
			<?php
			// Получаем все изображения товара
			$attachment_ids = $product->get_gallery_image_ids();
			$main_image_id = $product->get_image_id();
			
			// Объединяем главное изображение с галереей
			$all_images = array();
			if ( $main_image_id ) {
				$all_images[] = $main_image_id;
			}
			$all_images = array_merge( $all_images, $attachment_ids );
			
			// Главное изображение
			if ( !empty( $all_images ) ) :
			?>
				<div class="product-gallery-main">
					<img src="<?php echo esc_url( wp_get_attachment_image_url( $all_images[0], 'large' ) ); ?>" alt="<?php echo esc_attr( $product->get_name() ); ?>">
				</div>
				
				<!-- Миниатюры -->
				<?php if ( count( $all_images ) > 1 ) : ?>
					<div class="product-gallery-thumbs">
						<?php foreach ( array_slice( $all_images, 0, 3 ) as $image_id ) : ?>
							<div class="product-thumb">
								<img src="<?php echo esc_url( wp_get_attachment_image_url( $image_id, 'thumbnail' ) ); ?>" alt="<?php echo esc_attr( $product->get_name() ); ?>">
							</div>
						<?php endforeach; ?>
					</div>
				<?php endif; ?>
			<?php else : ?>
				<div class="product-gallery-main">
					<img src="<?php echo esc_url( wc_placeholder_img_src() ); ?>" alt="<?php echo esc_attr( $product->get_name() ); ?>">
				</div>
			<?php endif; ?>
		</div>

		<!-- Информация о товаре справа -->
		<div class="product-summary">
			<!-- Название товара -->
			<h1 class="product-title"><?php the_title(); ?></h1>

			<!-- Артикул -->
			<?php if ( $product->get_sku() ) : ?>
				<div class="product-sku">
					Артикул: <span><?php echo esc_html( $product->get_sku() ); ?></span>
				</div>
			<?php endif; ?>

			<!-- Кнопка чата (желтая кнопка справа вверху) -->
			<button class="product-chat-btn" aria-label="Нашли дешевле?">
				<svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
					<path d="M2 10C2 5.58172 5.58172 2 10 2C14.4183 2 18 5.58172 18 10C18 14.4183 14.4183 18 10 18H2V10Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
				</svg>
				<span>Нашли дешевле?</span>
			</button>

			<!-- Цена -->
			<div class="product-price">
				<?php echo $product->get_price_html(); ?>
			</div>

			<!-- Форма добавления в корзину -->
			<form class="cart" action="<?php echo esc_url( apply_filters( 'woocommerce_add_to_cart_form_action', $product->get_permalink() ) ); ?>" method="post" enctype='multipart/form-data'>
				<?php do_action( 'woocommerce_before_add_to_cart_button' ); ?>

				<div class="cart-form-group">
					<!-- Счетчик количества с кнопками +/- -->
					<div class="quantity-wrapper">
						<button type="button" class="qty-btn qty-minus" aria-label="Уменьшить количество">−</button>
					<?php
					$min_value = apply_filters( 'woocommerce_quantity_input_min', $product->get_min_purchase_quantity(), $product );
					$max_value = apply_filters( 'woocommerce_quantity_input_max', $product->get_max_purchase_quantity(), $product );
					// Если max = -1 (без ограничений), ставим 999
					if ( $max_value <= 0 || $max_value == -1 ) {
						$max_value = 999;
					}
					$input_value = isset( $_POST['quantity'] ) ? wc_stock_amount( wp_unslash( $_POST['quantity'] ) ) : $min_value;
					?>
					<input type="number" 
						   name="quantity" 
						   value="<?php echo esc_attr( $input_value ); ?>" 
						   min="<?php echo esc_attr( $min_value ); ?>" 
						   max="<?php echo esc_attr( $max_value ); ?>" 
						   step="1" 
						   class="qty">
						<button type="button" class="qty-btn qty-plus" aria-label="Увеличить количество">+</button>
					</div>

					<button type="submit" name="add-to-cart" value="<?php echo esc_attr( $product->get_id() ); ?>" class="single_add_to_cart_button button alt">
						В корзину
					</button>
				</div>

				<?php do_action( 'woocommerce_after_add_to_cart_button' ); ?>
			</form>

			<!-- Краткое описание товара (дубликат названия) -->
			<div class="product-short-desc">
				<?php echo esc_html( $product->get_name() ); ?>
			</div>

			<!-- Описание -->
			<?php if ( $product->get_short_description() ) : ?>
				<div class="product-description">
					<h3>Описание</h3>
					<div class="product-description-content">
						<?php echo apply_filters( 'woocommerce_short_description', $product->get_short_description() ); ?>
					</div>
				</div>
			<?php endif; ?>
		</div>
	</div>
</div>

	<!-- Похожие товары - выводим ВНЕ div.single-product-page и ВНЕ .container -->
	<?php
	/**
	 * Hook: woocommerce_after_single_product_summary.
	 *
	 * @hooked woocommerce_output_product_data_tabs - 10
	 * @hooked woocommerce_upsell_display - 15
	 * @hooked asker_output_related_products - 20
	 */
	do_action( 'woocommerce_after_single_product_summary' );
	?>

<?php do_action( 'woocommerce_after_single_product' ); ?>
<?php endif; ?>
