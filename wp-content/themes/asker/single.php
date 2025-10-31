<?php
/**
 * Запись блога/произвольного типа по умолчанию.
 */

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










