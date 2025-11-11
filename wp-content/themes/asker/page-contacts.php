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
                            <strong>Телефон:</strong>
                            <p><a href="tel:+79311099476">+7 (931) 109-94-76</a></p>
                        </div>
                        <div class="contact-info__item">
                            <strong>Режим работы:</strong>
                            <p>ПН-ПТ с 9:00 до 18:00</p>
                        </div>
                    </div>
                    <button type="button" class="contact-card__button contact-card__button--chat" onclick="openChatPopup(); return false;">Написать директору</button>
                </div>

                <!-- Форма обратной связи -->
                <div class="contact-form-card">
                    <h2 class="contact-form-card__title">Обратная связь</h2>
                    <?php 
                    // Получаем шорткод формы из ACF для текущей страницы (страницы контактов)
                    $contact_form_shortcode = function_exists('get_field') ? (string) get_field('contact_form_shortcode') : '';
                    if ($contact_form_shortcode) : 
                        echo do_shortcode($contact_form_shortcode);
                    else : 
                    ?>
                        <p class="form-placeholder" style="padding: 20px; background: #f5f5f5; border-radius: 8px; color: #666;">
                            Добавьте шорткод формы Contact Form 7 в ACF настройках этой страницы (поле "Шорткод контактной формы")
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


