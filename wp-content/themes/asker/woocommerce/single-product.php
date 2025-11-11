<?php
/**
 * The Template for displaying all single products
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/single-product.php.
 *
 * @see         https://docs.woocommerce.com/document/template-structure/
 * @package     WooCommerce\Templates
 * @version     9.0.0
 */

defined( 'ABSPATH' ) || exit;

get_header();
?>

<div class="container">
    <!-- Хлебные крошки -->
    <nav class="breadcrumbs">
        <a href="<?php echo esc_url(home_url('/')); ?>">Главная</a>
        <span class="breadcrumbs__separator">/</span>
        <a href="<?php echo esc_url(get_permalink(wc_get_page_id('shop'))); ?>">Каталог</a>
        <span class="breadcrumbs__separator">/</span>
        <?php
        // Показываем категории товара
        $terms = wp_get_post_terms(get_the_ID(), 'product_cat', array('orderby' => 'parent', 'order' => 'ASC'));
        if (!empty($terms) && !is_wp_error($terms)) {
            $main_category = $terms[0];
            $category_link = get_term_link($main_category);
            if (!is_wp_error($category_link)) {
                echo '<a href="' . esc_url($category_link) . '">' . esc_html($main_category->name) . '</a>';
                echo '<span class="breadcrumbs__separator">/</span>';
            }
        }
        ?>
        <span class="breadcrumbs__current"><?php the_title(); ?></span>
    </nav>

    <?php while ( have_posts() ) : the_post(); ?>

        <?php wc_get_template_part( 'content', 'single-product' ); ?>

    <?php endwhile; ?>
</div>

<?php
get_footer();

