<?php
/**
 * Запись блога/произвольного типа по умолчанию.
 * НЕ используем для товаров WooCommerce - они используют single-product.php
 */

// КРИТИЧНО: Если это товар WooCommerce - редиректим на правильный шаблон
if ( function_exists( 'is_product' ) && is_product() ) {
    $product_template = get_template_directory() . '/woocommerce/single-product.php';
    if ( file_exists( $product_template ) ) {
        include $product_template;
        return;
    }
}

get_header();
?>

<div class="container section">
    <?php while (have_posts()) : the_post(); ?>
        <article <?php post_class(); ?>>
            <h1 class="section__title"><?php the_title(); ?></h1>
            <?php if (has_post_thumbnail()) : ?>
                <div class="post-thumb"><?php the_post_thumbnail('large'); ?></div>
            <?php endif; ?>
            <div class="content">
                <?php the_content(); ?>
            </div>
        </article>
    <?php endwhile; ?>
</div>

<?php get_footer(); ?>














