<?php
/**
 * Template Name: Гарантии
 * Страница с информацией о гарантиях
 */

get_header();
?>

<div class="container">
    <nav class="breadcrumbs" aria-label="Breadcrumb">
        <a href="<?php echo esc_url(home_url('/')); ?>">Главная</a>
        <span class="breadcrumbs__separator">/</span>
        <span class="breadcrumbs__current">Гарантии</span>
    </nav>

    <h1 class="page-title">Гарантии</h1>

    <div class="content-page">
        <?php
        while (have_posts()) : the_post();
            the_content();
        endwhile;
        ?>

        <?php if (!get_the_content()) : ?>
            <h2>Гарантии качества</h2>
            <p>Мы гарантируем качество всех поставляемых товаров:</p>
            <ul>
                <li>Гарантия на все товары — от 6 до 24 месяцев</li>
                <li>Сертификаты качества на всю продукцию</li>
                <li>Возврат и обмен в течение 14 дней</li>
                <li>Техническая поддержка и консультации</li>
            </ul>
            <h3>Возврат товара</h3>
            <p>Если товар не подошел, вы можете вернуть его в течение 14 дней с момента получения. Товар должен быть в оригинальной упаковке, без следов использования.</p>
        <?php endif; ?>
    </div>
</div>

<?php get_footer(); ?>

