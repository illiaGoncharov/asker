<?php
/**
 * Шаблон архива (категории блога и т.п.).
 */

get_header();
?>

<div class="container section">
    <h1 class="section__title"><?php the_archive_title(); ?></h1>
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

<?php get_footer(); ?>





