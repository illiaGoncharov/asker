<?php
/**
 * Template Name: Успешная регистрация
 * Страница успешной регистрации
 */

defined( 'ABSPATH' ) || exit;

get_header();
?>

<div class="registration-success-page">
    <div class="container">
        <div class="registration-success__card">
            
            <!-- Иконка успеха -->
            <div class="registration-success__icon">
                <svg width="64" height="64" viewBox="0 0 64 64" fill="none">
                    <circle cx="32" cy="32" r="32" fill="#4CAF50"/>
                    <path d="M20 32L28 40L44 24" stroke="white" stroke-width="4" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </div>
            
            <!-- Заголовок -->
            <h1 class="registration-success__title">Регистрация успешна</h1>
            
            <!-- Текст -->
            <div class="registration-success__content">
                <p>Благодарим Вас за регистрацию на сайте Asker-corp.ru.</p>
                
                <p>Подтвердите, пожалуйста, Вашу почту, перейдя по ссылке, которую мы отправили Вам в письме, и установите пароль</p>
                
<!--                 <p>Сразу после завершения проверки Вы получите доступ к личному кабинету, где сможете отслеживать свои заказы, управлять настройками и контактной информацией. Мы уведомим Вас отдельно, как только доступ будет открыт.</p> -->
            </div>
            
            <!-- Подсказка про почту -->
            <div class="registration-success__hint">
                <div class="registration-success__hint-icon">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none">
                        <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z" stroke="currentColor" stroke-width="2"/>
                        <polyline points="22,6 12,13 2,6" stroke="currentColor" stroke-width="2"/>
                    </svg>
                </div>
                <p>Проверьте папку «Входящие» и «Спам» — письмо с инструкциями уже в пути!</p>
            </div>
            
            <!-- Кнопка -->
            <div class="registration-success__actions">
                <a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="btn btn--primary">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none">
                        <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z" stroke="currentColor" stroke-width="2"/>
                        <polyline points="9,22 9,12 15,12 15,22" stroke="currentColor" stroke-width="2"/>
                    </svg>
                    На главную
                </a>
            </div>
            
        </div>
    </div>
</div>

<?php get_footer(); ?>

