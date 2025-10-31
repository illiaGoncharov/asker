<?php
/**
 * Шаблон одиночной страницы (о компании, доставка, контакты и т.п.)
 */

get_header();
?>

<div class="container section">
    <?php while (have_posts()) : the_post(); ?>
        <article <?php post_class(); ?>>
            <h1 class="section__title"><?php the_title(); ?></h1>
            <div class="content">
                <?php the_content(); ?>
            </div>
        </article>
    <?php endwhile; ?>
</div>

<?php get_footer(); ?>










