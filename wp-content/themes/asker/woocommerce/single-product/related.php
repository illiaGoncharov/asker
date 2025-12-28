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
                    $price = $product->get_price_html();
                    // Убираем копейки из цены
                    $price = preg_replace( '/,00/', '', $price );
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
                        <div class="product-price"><?php echo $price; ?></div>
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

<?php
endif;

wp_reset_postdata();

