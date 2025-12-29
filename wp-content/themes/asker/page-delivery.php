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
        // Получаем ACF поля для двух колонок
        $left_column = get_field('left_column');
        $right_column = get_field('right_column');
        
        if ($left_column || $right_column) : ?>
            <div class="content-page__two-columns">
                <?php if ($left_column) : ?>
                    <div class="content-page__column content-page__column--left">
                        <?php echo wp_kses_post($left_column); ?>
                    </div>
                <?php endif; ?>
                
                <?php if ($right_column) : ?>
                    <div class="content-page__column content-page__column--right">
                        <?php echo wp_kses_post($right_column); ?>
                    </div>
                <?php endif; ?>
            </div>
        <?php else : ?>
            <?php
            // Fallback: стандартный контент страницы
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
        <?php endif; ?>
    </div>
</div>

<?php get_footer(); ?>

