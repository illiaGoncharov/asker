<?php
/**
 * Шаблон одиночной страницы (о компании, доставка, контакты и т.п.)
 */

// GLOBAL DEBUG - покажет в любом случае
if ( current_user_can( 'administrator' ) && isset( $_SERVER['REQUEST_URI'] ) && stripos( $_SERVER['REQUEST_URI'], 'blog' ) !== false ) {
    error_log( 'PAGE.PHP LOADED for /blog' );
}

// Если это страница назначена как "Страница записей" или URL содержит /blog - загружаем шаблон блога
global $post, $wp;
$page_slug = $post ? $post->post_name : '';
$request_uri = isset( $_SERVER['REQUEST_URI'] ) ? $_SERVER['REQUEST_URI'] : '';
$is_blog_page = is_home() 
    || ( $page_slug && ( stripos( $page_slug, 'blog' ) !== false || stripos( $page_slug, 'блог' ) !== false ) )
    || ( stripos( $request_uri, '/blog' ) !== false );

if ( $is_blog_page ) {
    // Получаем записи вручную
    $paged = get_query_var( 'paged' ) ? get_query_var( 'paged' ) : 1;
    
    // Сбрасываем глобальный query и получаем посты напрямую
    $blog_args = array(
        'post_type'      => 'post',
        'post_status'    => 'publish',
        'posts_per_page' => get_option( 'posts_per_page', 10 ),
        'paged'          => $paged,
        'orderby'        => 'date',
        'order'          => 'DESC',
    );
    
    $blog_query = new WP_Query( $blog_args );
    
    get_header();
    
    // DEBUG: временная отладка
    if ( current_user_can( 'administrator' ) ) {
        echo '<!-- DEBUG: is_blog_page=' . ( $is_blog_page ? 'true' : 'false' ) . ' -->';
        echo '<!-- DEBUG: page_slug=' . esc_html( $page_slug ) . ' -->';
        echo '<!-- DEBUG: request_uri=' . esc_html( $request_uri ) . ' -->';
        echo '<!-- DEBUG: found_posts=' . $blog_query->found_posts . ' -->';
        echo '<!-- DEBUG: post_count=' . $blog_query->post_count . ' -->';
    }
    ?>
    <div class="container">
        <nav class="breadcrumbs" aria-label="Breadcrumb">
            <a href="<?php echo esc_url( home_url( '/' ) ); ?>">Главная</a>
            <span class="breadcrumbs__separator">/</span>
            <span class="breadcrumbs__current">Блог</span>
        </nav>

        <h1 class="page-title"><?php _e( 'Блог', 'asker' ); ?></h1>
        
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
                <?php if ( current_user_can( 'administrator' ) ) : ?>
                    <div style="background: #fee; padding: 20px; margin-top: 20px; border: 1px solid #f00;">
                        <strong>DEBUG INFO:</strong><br>
                        found_posts: <?php echo $blog_query->found_posts; ?><br>
                        post_count: <?php echo $blog_query->post_count; ?><br>
                        SQL: <?php echo esc_html( $blog_query->request ); ?><br>
                        <?php 
                        // Проверим напрямую из БД
                        global $wpdb;
                        $direct_count = $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_type = 'post' AND post_status = 'publish'" );
                        echo "Direct DB count: " . $direct_count . "<br>";
                        ?>
                    </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
    <?php
    get_footer();
    return;
}

get_header();
?>

<div class="container section">
    <?php while (have_posts()) : the_post(); ?>
        <article <?php post_class(); ?>>
            <?php 
            // Не выводим заголовок для страницы аккаунта (там свой шаблон)
            if (!is_account_page()) : 
            ?>
                <h1 class="section__title"><?php the_title(); ?></h1>
            <?php endif; ?>
            <div class="content">
                <?php 
                // Для страницы my-account используем кастомный шаблон напрямую
                if (is_account_page()) {
                    $custom_template = get_template_directory() . '/woocommerce/myaccount/my-account.php';
                    if (file_exists($custom_template)) {
                        // Загружаем наш кастомный шаблон
                        include $custom_template;
                    } else {
                        // Fallback на стандартный вывод
                        the_content();
                    }
                } else {
                    // Для остальных страниц выводим стандартный контент
                    the_content();
                }
                ?>
            </div>
        </article>
    <?php endwhile; ?>
</div>

<?php get_footer(); ?>










