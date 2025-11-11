<?php
/**
 * Template Name: О компании
 * Страница с информацией о компании
 */

get_header();
?>

<div class="container">
    <nav class="breadcrumbs" aria-label="Breadcrumb">
        <a href="<?php echo esc_url(home_url('/')); ?>">Главная</a>
        <span class="breadcrumbs__separator">/</span>
        <span class="breadcrumbs__current">О компании</span>
    </nav>

    <h1 class="page-title">О компании</h1>

    <div class="content-page">
            <?php
            while (have_posts()) : the_post();
                the_content();
            endwhile;
            ?>

            <?php if (!get_the_content()) : ?>
                <div class="content-page__default">
                    <p>
                        Asker – российская компания, специализирующаяся на производстве и поставке нагревательных элементов и комплектующих для бытового и промышленного оборудования. Работаем с 2022 года.
                    </p>
                    <p>
                        Мы предлагаем широкий ассортимент качественных запчастей: нагревательные элементы, термостаты, прокладки и другие комплектующие.
                    </p>
                    <p>
                        Более 500 довольных клиентов по всей России.
                    </p>

                    <div class="content-page__features">
                        <div class="content-page__feature">
                            <h3>Гарантия качества</h3>
                            <p>Гарантия на все товары и сервисная поддержка</p>
                        </div>
                        <div class="content-page__feature">
                            <h3>Быстрая доставка</h3>
                            <p>Собственный склад в Санкт-Петербурге и оперативная доставка</p>
                        </div>
                        <div class="content-page__feature">
                            <h3>Техподдержка</h3>
                            <p>Консультации по подбору запчастей и технической совместимости</p>
                        </div>
                        <div class="content-page__feature">
                            <h3>Оптовые цены</h3>
                            <p>Прямые поставки от проверенных производителей</p>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
</div>

<?php get_footer(); ?>


