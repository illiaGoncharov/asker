<?php
/**
 * Шаблон отдельной записи блога
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

<div class="container">
    <?php while ( have_posts() ) : the_post(); ?>
        
        <!-- Хлебные крошки -->
        <nav class="breadcrumbs" aria-label="Breadcrumb">
            <a href="<?php echo esc_url( home_url( '/' ) ); ?>">Главная</a>
            <span class="breadcrumbs__separator">/</span>
            <?php
            // Ссылка на страницу блога
            $blog_page_id = get_option( 'page_for_posts' );
            if ( $blog_page_id ) :
            ?>
                <a href="<?php echo esc_url( get_permalink( $blog_page_id ) ); ?>">Блог</a>
            <?php else : ?>
                <a href="<?php echo esc_url( home_url( '/blog/' ) ); ?>">Блог</a>
            <?php endif; ?>
            <span class="breadcrumbs__separator">/</span>
            <span class="breadcrumbs__current"><?php the_title(); ?></span>
        </nav>

        <article <?php post_class( 'single-post' ); ?>>
            
            <!-- Заголовок -->
            <h1 class="page-title"><?php the_title(); ?></h1>
            
            <!-- Мета-информация -->
            <div class="single-post__meta">
                <span class="single-post__date">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/>
                        <line x1="16" y1="2" x2="16" y2="6"/>
                        <line x1="8" y1="2" x2="8" y2="6"/>
                        <line x1="3" y1="10" x2="21" y2="10"/>
                    </svg>
                    <?php echo get_the_date(); ?>
                </span>
                <?php
                $categories = get_the_category();
                if ( ! empty( $categories ) ) :
                ?>
                    <span class="single-post__category">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"/>
                        </svg>
                        <a href="<?php echo esc_url( get_category_link( $categories[0]->term_id ) ); ?>">
                            <?php echo esc_html( $categories[0]->name ); ?>
                        </a>
                    </span>
                <?php endif; ?>
            </div>
            
            <!-- Изображение статьи -->
            <?php if ( has_post_thumbnail() ) : ?>
                <div class="single-post__image">
                    <?php the_post_thumbnail( 'large' ); ?>
                </div>
            <?php endif; ?>
            
            <!-- Контент статьи -->
            <div class="content-page">
                <?php the_content(); ?>
            </div>
            
            <!-- Теги -->
            <?php
            $tags = get_the_tags();
            if ( ! empty( $tags ) ) :
            ?>
                <div class="single-post__tags">
                    <span class="single-post__tags-label">Теги:</span>
                    <?php foreach ( $tags as $tag ) : ?>
                        <a href="<?php echo esc_url( get_tag_link( $tag->term_id ) ); ?>" class="single-post__tag">
                            <?php echo esc_html( $tag->name ); ?>
                        </a>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
            
        </article>
        
        <!-- Навигация между статьями -->
        <nav class="post-navigation">
            <?php
            $prev_post = get_previous_post();
            $next_post = get_next_post();
            ?>
            
            <?php if ( $prev_post ) : ?>
                <a href="<?php echo esc_url( get_permalink( $prev_post ) ); ?>" class="post-navigation__link post-navigation__link--prev">
                    <span class="post-navigation__arrow">←</span>
                    <span class="post-navigation__text">
                        <span class="post-navigation__label">Предыдущая статья</span>
                        <span class="post-navigation__title"><?php echo esc_html( $prev_post->post_title ); ?></span>
                    </span>
                </a>
            <?php else : ?>
                <div class="post-navigation__link post-navigation__link--empty"></div>
            <?php endif; ?>
            
            <?php if ( $next_post ) : ?>
                <a href="<?php echo esc_url( get_permalink( $next_post ) ); ?>" class="post-navigation__link post-navigation__link--next">
                    <span class="post-navigation__text">
                        <span class="post-navigation__label">Следующая статья</span>
                        <span class="post-navigation__title"><?php echo esc_html( $next_post->post_title ); ?></span>
                    </span>
                    <span class="post-navigation__arrow">→</span>
                </a>
            <?php endif; ?>
        </nav>
        
    <?php endwhile; ?>
</div>

<?php get_footer(); ?>
