<?php
/**
 * Кастомный шаблон чекаута
 */

// Отладочная информация
error_log( 'Asker: Custom checkout template loaded' );

get_header();
?>


<div class="woocommerce-checkout">
    <div class="container">
        
        <!-- Хлебные крошки -->
        <div class="checkout__breadcrumbs">
            <a href="<?php echo esc_url( home_url( '/' ) ); ?>">Главная</a>
            <span class="checkout__breadcrumbs-separator">/</span>
            <a href="<?php echo esc_url( wc_get_cart_url() ); ?>">Корзина</a>
            <span class="checkout__breadcrumbs-separator">/</span>
            <span class="checkout__breadcrumbs-current">Оформление заказа</span>
        </div>

        <!-- Заголовок -->
        <h1 class="section__title">Оформление заказа</h1>

        <?php
        // Проверяем корзину
        if ( function_exists( 'WC' ) && WC()->cart && ! WC()->cart->is_empty() ) :
            // Корзина не пуста - показываем форму чекаута
            ?>
            <div class="woocommerce checkout-grid">
                <form name="checkout" method="post" class="checkout woocommerce-checkout" action="<?php echo esc_url( wc_get_checkout_url() ); ?>" enctype="multipart/form-data">
                    
                    <!-- Левая колонка - формы -->
                    <div class="checkout__forms">
                        <?php do_action( 'woocommerce_checkout_billing' ); ?>
                        <?php do_action( 'woocommerce_checkout_shipping' ); ?>
                    </div>
                    
                    <!-- Правая колонка - сводка заказа -->
                    <div class="checkout__order-summary">
                        <h3><?php esc_html_e( 'Ваш заказ', 'woocommerce' ); ?></h3>
                        <div id="order_review" class="checkout__order-review">
                            <?php do_action( 'woocommerce_checkout_order_review' ); ?>
                        </div>
                    </div>
                    
                </form>
            </div>
            <?php
        else :
            // Корзина пуста
            ?>
            <div class="checkout__empty-cart">
                <p>Ваша корзина пуста.</p>
                <a href="<?php echo esc_url( wc_get_page_permalink( 'shop' ) ); ?>" class="btn btn--primary">Перейти в каталог</a>
            </div>
            <?php
        endif;
        ?>
        
    </div>
</div>

<?php get_footer(); ?>
