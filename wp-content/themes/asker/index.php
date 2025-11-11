<?php
/**
 * Фолбэк-шаблон. Используется для архивов/блога при отсутствии спец-шаблонов.
 * Для главной страницы принудительно загружаем front-page.php
 */

// НЕ используем exit здесь - это блокирует wp_footer()
// Вместо этого полагаемся на template_include фильтр в woocommerce.php

// НЕ вызываем get_header() здесь, если это главная страница
// т.к. front-page.php сам вызовет get_header()
// Это предотвращает дублирование header
if ( ! is_front_page() && ! is_home() ) {
    get_header();
}
?>

<div class="container section">
    <h1 class="section__title"><?php echo esc_html(get_the_title(get_option('page_for_posts')) ?: __('Записи', 'asker')); ?></h1>
    <?php if (have_posts()) : ?>
        <div class="posts-grid">
            <?php while (have_posts()) : the_post(); ?>
                <article <?php post_class('card'); ?>>
                    <a href="<?php the_permalink(); ?>">
                        <?php if (has_post_thumbnail()) { the_post_thumbnail('medium'); } ?>
                        <h2><?php the_title(); ?></h2>
                    </a>
                </article>
            <?php endwhile; ?>
        </div>
        <div class="pagination">
            <?php the_posts_pagination(); ?>
        </div>
    <?php else : ?>
        <p><?php _e('Ничего не найдено.', 'asker'); ?></p>
    <?php endif; ?>
</div>

<?php 
// НЕ вызываем get_footer() если это главная страница
// т.к. front-page.php сам вызовет get_footer()
if ( ! is_front_page() && ! is_home() ) {
    get_footer();
}
?>





