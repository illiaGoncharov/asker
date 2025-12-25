<?php
/**
 * Template Name: Contacts Page
 * Страница контактов с картой и формой обратной связи
 */

get_header();
?>

<div class="container">
    <nav class="breadcrumbs" aria-label="Breadcrumb">
        <a href="<?php echo esc_url(home_url('/')); ?>">Главная</a>
        <span class="breadcrumbs__separator">/</span>
        <span class="breadcrumbs__current">Контакты</span>
    </nav>

    <h1 class="page-title">Контакты</h1>

    <div class="content-page content-page--contacts">
            <!-- Левая колонка: контакты и форма -->
            <div class="contact-page__left">
                <!-- Карточка контактов -->
                <div class="contact-card">
                    <div class="contact-card__info">
                        <div class="contact-info__item">
                            <strong>Адрес:</strong>
                            <p>Санкт-Петербург, ул. Карпатская д. 16</p>
                        </div>
                        <div class="contact-info__item">
                            <strong>Email:</strong>
                            <p><a href="mailto:opt@asker-corp.ru">opt@asker-corp.ru</a></p>
                        </div>
                        <div class="contact-info__item">
                            <strong>Режим работы:</strong>
                            <p>ПН-ПТ с 9:00 до 18:00</p>
                        </div>
                    </div>
                    <a href="https://t.me/Ararat007_7" target="_blank" class="contact-card__button contact-card__button--chat">Написать директору</a>
                </div>

                <!-- Форма обратной связи -->
                <div class="contact-form-card">
                    <h2 class="contact-form-card__title">Обратная связь</h2>
                    <?php 
                    // Шорткод формы: сначала из Customizer, fallback на ACF
                    $contact_form_shortcode = get_theme_mod('contacts_form_shortcode', '');
                    if (empty($contact_form_shortcode) && function_exists('get_field')) {
                        $contact_form_shortcode = (string) get_field('contact_form_shortcode');
                    }
                    
                    if ($contact_form_shortcode) : 
                        echo do_shortcode($contact_form_shortcode);
                    else : 
                    ?>
                        <p class="form-placeholder">
                            Добавьте шорткод формы в Внешний вид → Настроить → Настройки Asker
                        </p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Правая колонка: карта -->
            <div class="contact-page__right">
                <div class="contact-map" id="yandex-map" data-api-key="<?php echo esc_attr(get_theme_mod('yandex_map_api_key', '')); ?>"></div>
            </div>
        </div>
</div>

<?php get_footer(); ?>


