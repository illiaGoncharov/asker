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
                    <h2 class="contact-card__title">Контакты</h2>
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
                    <a href="mailto:opt@asker-corp.ru" class="contact-card__button">Написать директору</a>
                </div>

                <!-- Форма обратной связи -->
                <div class="contact-form-card">
                    <h2 class="contact-form-card__title">Обратная связь</h2>
                    <form class="contact-form" id="contact-feedback-form" method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
                        <?php wp_nonce_field('asker_contact_form', 'asker_contact_nonce'); ?>
                        <input type="hidden" name="action" value="asker_contact_feedback">
                        
                        <div class="form-group">
                            <label for="contact-name">Имя</label>
                            <input type="text" id="contact-name" name="contact_name" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="contact-phone">Телефон</label>
                            <input type="tel" id="contact-phone" name="contact_phone" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="contact-message">Сообщение</label>
                            <textarea id="contact-message" name="contact_message" rows="4" required></textarea>
                        </div>
                        
                        <div class="form-group form-group--checkbox">
                            <label class="checkbox-label">
                                <input type="checkbox" name="contact_consent" required>
                                <span>Согласен на обработку персональных данных <a href="<?php echo esc_url(asker_get_page_url('privacy', 'page-privacy.php', home_url('/privacy'))); ?>" target="_blank">Подробнее</a></span>
                            </label>
                        </div>
                        
                        <button type="submit" class="btn btn--primary btn--submit">Отправить</button>
                    </form>
                    
                    <!-- Сообщения -->
                    <?php if (isset($_GET['contact_success'])) : ?>
                        <div class="contact-form__success">
                            <p>Спасибо! Ваше сообщение отправлено. Мы свяжемся с вами в ближайшее время.</p>
                        </div>
                    <?php endif; ?>
                    
                    <?php if (isset($_GET['contact_error'])) : ?>
                        <div class="contact-form__error">
                            <p>Произошла ошибка при отправке сообщения. Пожалуйста, попробуйте еще раз или свяжитесь с нами по телефону.</p>
                        </div>
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


