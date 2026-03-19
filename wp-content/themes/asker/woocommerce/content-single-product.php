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

			<!-- Кнопка "Нашли дешевле" - открывает попап с формой -->
			<button type="button" class="product-chat-btn" aria-label="Нашли дешевле?" onclick="openContactFormPopup(); return false;">
				<svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
					<path d="M2 10C2 5.58172 5.58172 2 10 2C14.4183 2 18 5.58172 18 10C18 14.4183 14.4183 18 10 18H2V10Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
				</svg>
				<span>Нашли дешевле?</span>
			</button>

			<!-- Цена с персонализацией -->
			<div class="product-price">
				<?php
				// Получаем базовую цену
				$regular_price = $product->get_regular_price();
				$sale_price = $product->get_sale_price();
				
				// Проверяем, авторизован ли пользователь и есть ли у него скидка
				$has_discount = false;
				$discount_percent = 0;
				
				if ( is_user_logged_in() ) {
					$user_id = get_current_user_id();
					
					// Получаем скидку пользователя
					if ( function_exists( 'asker_get_total_discount' ) ) {
						$discount_percent = asker_get_total_discount( $user_id );
					} else {
						// Если функция не существует, пробуем получить напрямую из мета-полей
						$level_discount = get_user_meta( $user_id, 'user_level_discount', true );
						$individual_discount = get_user_meta( $user_id, 'individual_discount', true );
						$discount_percent = max( floatval( $level_discount ), floatval( $individual_discount ) );
					}
					
					if ( $discount_percent > 0 ) {
						$has_discount = true;
					}
				}
				
				// Отображаем цену в зависимости от наличия скидки
				if ( $has_discount && ! empty( $regular_price ) ) :
					// У пользователя есть персональная скидка
					
					if ( ! empty( $sale_price ) ) {
						// Товар уже со скидкой (акция) + персональная скидка
						$discounted_price = $sale_price * ( 1 - $discount_percent / 100 );
						?>
						<div class="price-wrapper-personalized">
							<span class="regular-price-crossed"><del><?php echo wc_price( $regular_price ); ?></del></span>
							<span class="sale-price-crossed"><del><?php echo wc_price( $sale_price ); ?></del></span>
							<span class="personalized-price"><?php echo wc_price( $discounted_price ); ?></span>
							<span class="discount-badge">Ваша скидка: <?php echo esc_html( $discount_percent ); ?>%</span>
						</div>
						<?php
					} else {
						// Обычный товар + персональная скидка
						$discounted_price = $regular_price * ( 1 - $discount_percent / 100 );
						?>
						<div class="price-wrapper-personalized">
							<span class="regular-price-crossed"><del><?php echo wc_price( $regular_price ); ?></del></span>
							<span class="personalized-price"><?php echo wc_price( $discounted_price ); ?></span>
							<span class="discount-badge">Ваша скидка: <?php echo esc_html( $discount_percent ); ?>%</span>
						</div>
						<?php
					}
					
				else :
					// Обычная цена без персональной скидки
					echo $product->get_price_html();
				endif;
				?>
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
					$btn_class = 'single_add_to_cart_button button alt add_to_cart_button';
					if ( $cart_qty > 0 ) {
						$btn_class .= ' has-items';
					}
					?>
					<button type="submit" name="add-to-cart" value="<?php echo esc_attr( $product->get_id() ); ?>" class="<?php echo esc_attr( $btn_class ); ?>"><span class="btn-text">В корзину</span><span class="btn-cart-count" data-count="<?php echo esc_attr( $cart_qty ); ?>"><?php echo esc_html( $cart_qty ); ?></span></button>
				</div>

				<?php do_action( 'woocommerce_after_add_to_cart_button' ); ?>
			</form>


			<?php 
			// Кнопка "Купить на Ozon" если есть ссылка
			$ozon_link = asker_get_ozon_link( $product->get_id() );
			if ( $ozon_link ) : ?>
				<a href="<?php echo esc_url( $ozon_link ); ?>" target="_blank" rel="noopener noreferrer" class="ozon-link-btn">
					Купить на <span class="ozon-text">OZON</span>
				</a>
			<?php endif; ?>

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

<style>
/* Стили для персонализированной цены */
.price-wrapper-personalized {
    display: flex;
    flex-direction: column;
    gap: 8px;
    margin: 16px 0;
}

.price-wrapper-personalized .regular-price-crossed,
.price-wrapper-personalized .sale-price-crossed {
    font-size: 18px;
    color: #9CA3AF;
    font-weight: 400;
}

.price-wrapper-personalized .regular-price-crossed del,
.price-wrapper-personalized .sale-price-crossed del {
    text-decoration: line-through;
}

.price-wrapper-personalized .personalized-price {
    font-size: 32px;
    font-weight: 700;
    color: #059669;
}

.price-wrapper-personalized .personalized-price .woocommerce-Price-amount {
    color: #059669;
}

.discount-badge {
    display: inline-block;
    background: linear-gradient(135deg, #059669 0%, #10B981 100%);
    color: white;
    padding: 6px 16px;
    border-radius: 20px;
    font-size: 14px;
    font-weight: 600;
    width: fit-content;
    box-shadow: 0 2px 8px rgba(5, 150, 105, 0.2);
}

/* Информационный блок о скидке */
.user-discount-info-block {
    display: flex;
    align-items: center;
    gap: 8px;
    margin: 16px 0;
    padding: 12px 16px;
    background: #F0FDF4;
    border: 1px solid #D1FAE5;
    border-radius: 8px;
    font-size: 14px;
    color: #059669;
}

.user-discount-info-block svg {
    flex-shrink: 0;
}

.user-discount-info-block strong {
    font-weight: 700;
}

/* Адаптив */
@media (max-width: 768px) {
    .price-wrapper-personalized .personalized-price {
        font-size: 24px;
    }
    
    .price-wrapper-personalized .regular-price-crossed,
    .price-wrapper-personalized .sale-price-crossed {
        font-size: 16px;
    }
}
</style>

<?php endif; ?>