<?php
/**
 * Шаблон результатов поиска
 */

get_header(); ?>

<div class="container">
    <div class="search-results">
        <h1 class="page-title">
            <?php
            printf(
                esc_html__('Результаты поиска для: %s', 'asker'),
                '<span>' . get_search_query() . '</span>'
            );
            ?>
        </h1>

        <?php if (have_posts()) : ?>
            <div class="search-results-grid">
                <?php while (have_posts()) : the_post(); ?>
                    <article class="search-result-item">
                        <?php if (has_post_thumbnail()) : ?>
                            <div class="search-result-thumbnail">
                                <a href="<?php the_permalink(); ?>">
                                    <?php the_post_thumbnail('medium'); ?>
                                </a>
                            </div>
                        <?php endif; ?>
                        
                        <div class="search-result-content">
                            <h2 class="search-result-title">
                                <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                            </h2>
                            
                            <div class="search-result-excerpt">
                                <?php the_excerpt(); ?>
                            </div>
                            
                            <div class="search-result-meta">
                                <span class="search-result-type">
                                    <?php echo get_post_type_object(get_post_type())->labels->singular_name; ?>
                                </span>
                                <span class="search-result-date">
                                    <?php echo get_the_date(); ?>
                                </span>
                            </div>
                        </div>
                    </article>
                <?php endwhile; ?>
            </div>

            <?php
            // Пагинация
            the_posts_pagination(array(
                'prev_text' => __('« Предыдущая', 'asker'),
                'next_text' => __('Следующая »', 'asker'),
            ));
            ?>

        <?php else : ?>
            <div class="no-results">
                <h2><?php esc_html_e('Ничего не найдено', 'asker'); ?></h2>
                <p><?php esc_html_e('К сожалению, по вашему запросу ничего не найдено. Попробуйте изменить поисковый запрос.', 'asker'); ?></p>
                
                <div class="search-suggestions">
                    <h3><?php esc_html_e('Возможно, вас заинтересует:', 'asker'); ?></h3>
                    <ul>
                        <li><a href="<?php echo esc_url(home_url('/shop')); ?>"><?php esc_html_e('Все товары', 'asker'); ?></a></li>
                        <li><a href="<?php echo esc_url(home_url('/categories')); ?>"><?php esc_html_e('Категории товаров', 'asker'); ?></a></li>
                    </ul>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php get_footer(); ?>



















