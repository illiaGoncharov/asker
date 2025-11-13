<?php
/**
 * The Template for displaying all single products
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/single-product.php.
 *
 * @see https://docs.woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 1.6.4
 */

defined( 'ABSPATH' ) || exit;

get_header(); ?>

<div class="container">
	<?php
	// Кастомные хлебные крошки для страницы товара
	?>
	<nav class="breadcrumbs">
		<a href="<?php echo esc_url(home_url('/')); ?>">Главная</a>
		<span class="breadcrumbs__separator">/</span>
		<?php
		$shop_page_id = wc_get_page_id('shop');
		if ( $shop_page_id ) :
		?>
			<a href="<?php echo esc_url(get_permalink($shop_page_id)); ?>">Каталог</a>
		<?php else : ?>
			<a href="<?php echo esc_url(home_url('/all-categories')); ?>">Каталог</a>
		<?php endif; ?>
		
		<?php
		// Категории товара
		$categories = wp_get_post_terms( get_the_ID(), 'product_cat', array( 'orderby' => 'parent', 'order' => 'ASC' ) );
		if ( ! empty( $categories ) && ! is_wp_error( $categories ) ) :
			foreach ( $categories as $category ) :
		?>
			<span class="breadcrumbs__separator">/</span>
			<a href="<?php echo esc_url(get_term_link($category)); ?>"><?php echo esc_html($category->name); ?></a>
		<?php
			endforeach;
		endif;
		?>
		
		<span class="breadcrumbs__separator">/</span>
		<span class="breadcrumbs__current"><?php the_title(); ?></span>
	</nav>
	
	<?php
	/**
	 * woocommerce_before_main_content hook.
	 */
	do_action( 'woocommerce_before_main_content' );
	?>

	<?php while ( have_posts() ) : ?>
		<?php the_post(); ?>

		<?php wc_get_template_part( 'content', 'single-product' ); ?>

	<?php endwhile; // end of the loop. ?>

	<?php
	/**
	 * woocommerce_after_main_content hook.
	 */
	do_action( 'woocommerce_after_main_content' );
	?>
</div>

<?php
// Похожие товары выводятся ВНЕ .container через хук woocommerce_after_single_product_summary
// который вызывается в content-single-product.php
?>

<?php
get_footer();
