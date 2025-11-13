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
                    <a href="<?php echo esc_url( $product_url ); ?>">
                        <?php if ( $product_image ) : ?>
                            <img class="product-image" src="<?php echo esc_url( $product_image[0] ); ?>" alt="<?php echo esc_attr( $product->get_name() ); ?>">
                        <?php else : ?>
                            <div class="product-placeholder"><?php echo esc_html( $product->get_name() ); ?></div>
                        <?php endif; ?>
                    </a>
                    <h3 class="product-title">
                        <a href="<?php echo esc_url( $product_url ); ?>"><?php echo esc_html( $product->get_name() ); ?></a>
                    </h3>
                    <div class="product-bottom">
                        <div class="product-price"><?php echo $price; ?></div>
                        <button class="btn-add-cart" data-product-id="<?php echo esc_attr( $product_id ); ?>">В корзину</button>
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

