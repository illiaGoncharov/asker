<?php
/**
 * Template Name: Доставка
 * Страница с информацией о доставке
 */

get_header();
?>

<div class="container">
    <nav class="breadcrumbs" aria-label="Breadcrumb">
        <a href="<?php echo esc_url(home_url('/')); ?>">Главная</a>
        <span class="breadcrumbs__separator">/</span>
        <span class="breadcrumbs__current">Доставка</span>
    </nav>

    <h1 class="page-title">Доставка</h1>

    <div class="content-page">
        <?php
        while (have_posts()) : the_post();
            the_content();
        endwhile;
        ?>

        <?php if (!get_the_content()) : ?>
            <h2>Условия доставки</h2>
            <p>Мы доставляем товары по всей России:</p>
            <ul>
                <li><strong>По Санкт-Петербургу</strong> — курьером в течение 1-2 дней</li>
                <li><strong>По России</strong> — транспортными компаниями 3-7 дней</li>
                <li><strong>Самовывоз</strong> — бесплатно со склада в СПб</li>
            </ul>
            <h3>Стоимость доставки</h3>
            <p>Стоимость доставки рассчитывается индивидуально в зависимости от региона и веса заказа.</p>
        <?php endif; ?>
    </div>
</div>

<?php get_footer(); ?>

