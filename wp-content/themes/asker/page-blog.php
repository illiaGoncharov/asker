<?php
/**
 * Template Name: Блог
 * Шаблон страницы блога с выводом записей
 */

get_header();

// Получаем записи
$paged = get_query_var( 'paged' ) ? get_query_var( 'paged' ) : 1;

$blog_query = new WP_Query( array(
    'post_type'      => 'post',
    'post_status'    => 'publish',
    'posts_per_page' => get_option( 'posts_per_page', 10 ),
    'paged'          => $paged,
    'orderby'        => 'date',
    'order'          => 'DESC',
) );

// DEBUG: Временно показываем информацию о запросе (удалить после отладки)
if ( current_user_can( 'administrator' ) ) {
    echo '<!-- DEBUG: Found ' . $blog_query->found_posts . ' posts, Query: ' . $blog_query->request . ' -->';
}
?>

<div class="container">
    <nav class="breadcrumbs" aria-label="Breadcrumb">
        <a href="<?php echo esc_url( home_url( '/' ) ); ?>">Главная</a>
        <span class="breadcrumbs__separator">/</span>
        <span class="breadcrumbs__current">Блог</span>
    </nav>

    <h1 class="page-title"><?php the_title(); ?></h1>
    
    <?php if ( $blog_query->have_posts() ) : ?>
        <div class="posts-grid">
            <?php while ( $blog_query->have_posts() ) : $blog_query->the_post(); ?>
                <article <?php post_class( 'post-card' ); ?>>
                    <a href="<?php the_permalink(); ?>" class="post-card__link">
                        <?php if ( has_post_thumbnail() ) : ?>
                            <div class="post-card__image">
                                <?php the_post_thumbnail( 'medium_large' ); ?>
                            </div>
                        <?php else : ?>
                            <div class="post-card__image post-card__image--placeholder">
                                <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                                    <rect x="3" y="3" width="18" height="18" rx="2" ry="2"/>
                                    <circle cx="8.5" cy="8.5" r="1.5"/>
                                    <polyline points="21 15 16 10 5 21"/>
                                </svg>
                            </div>
                        <?php endif; ?>
                        <div class="post-card__content">
                            <h2 class="post-card__title"><?php the_title(); ?></h2>
                            <div class="post-card__date"><?php echo get_the_date(); ?></div>
                            <div class="post-card__excerpt"><?php echo wp_trim_words( get_the_excerpt(), 20 ); ?></div>
                        </div>
                    </a>
                </article>
            <?php endwhile; ?>
        </div>
        
        <div class="pagination">
            <?php 
            echo paginate_links( array(
                'total'     => $blog_query->max_num_pages,
                'current'   => $paged,
                'prev_text' => '← Назад',
                'next_text' => 'Вперёд →',
            ) ); 
            ?>
        </div>
        <?php wp_reset_postdata(); ?>
    <?php else : ?>
        <div class="no-posts">
            <p><?php _e( 'Записи пока не добавлены.', 'asker' ); ?></p>
        </div>
    <?php endif; ?>
</div>

<?php get_footer(); ?>

