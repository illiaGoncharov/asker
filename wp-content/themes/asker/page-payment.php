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
    </div>
</div>

<?php get_footer(); ?>

