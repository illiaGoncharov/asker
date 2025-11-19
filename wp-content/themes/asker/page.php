<?php
/**
 * Шаблон одиночной страницы (о компании, доставка, контакты и т.п.)
 */

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










