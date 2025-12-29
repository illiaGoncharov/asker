<?php
/**
 * Template Name: Оплата
 * Страница с информацией о способах оплаты
 */

get_header();
?>

<div class="container">
    <nav class="breadcrumbs" aria-label="Breadcrumb">
        <a href="<?php echo esc_url(home_url('/')); ?>">Главная</a>
        <span class="breadcrumbs__separator">/</span>
        <span class="breadcrumbs__current">Оплата</span>
    </nav>

    <h1 class="page-title">Способы оплаты</h1>

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
                <h2>Способы оплаты</h2>
                <p>Мы принимаем следующие способы оплаты:</p>
                <ul>
                    <li>Банковские карты (Visa, MasterCard, МИР)</li>
                    <li>Безналичный расчет для юридических лиц</li>
                    <li>Наличные при самовывозе</li>
                    <li>Электронные кошельки</li>
                </ul>
                <h3>Безопасность платежей</h3>
                <p>Все платежи проходят через защищенное соединение. Мы не храним данные ваших банковских карт.</p>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</div>

<?php get_footer(); ?>

