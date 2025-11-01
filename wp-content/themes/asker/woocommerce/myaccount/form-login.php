<?php
/**
 * Форма входа/регистрации
 *
 * Этот шаблон переопределяет стандартную форму WooCommerce
 *
 * @package WooCommerce\Templates
 * @version 7.0.1
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

?>

<div class="auth-page">
    <div class="auth-container">
        <!-- Вкладки Вход/Регистрация -->
        <div class="auth-tabs">
            <button class="auth-tab auth-tab--active" data-tab="login">Вход</button>
            <button class="auth-tab" data-tab="register">Зарегистрироваться</button>
        </div>
        
        <!-- Форма входа -->
        <div class="auth-form-wrapper auth-form-wrapper--login active">
            <?php do_action( 'woocommerce_before_customer_login_form' ); ?>
            
            <form class="auth-form woocommerce-form woocommerce-form-login login" method="post">
                <?php do_action( 'woocommerce_login_form_start' ); ?>
                
                <?php 
                $message = '';
                if ( isset( $_GET['login'] ) && $_GET['login'] === 'failed' ) {
                    $message = '<div class="woocommerce-error">Неверное имя пользователя или пароль.</div>';
                }
                if ( $message ) : 
                    echo wp_kses_post( $message ); 
                endif; 
                ?>
                
                <div class="form-group">
                    <label for="username">Логин или Email&nbsp;<span class="required">*</span></label>
                    <input type="text" class="woocommerce-Input woocommerce-Input--text input-text form-control" name="username" id="username" autocomplete="username" placeholder="Введите логин или email для входа" value="<?php echo ( ! empty( $_POST['username'] ) ) ? esc_attr( wp_unslash( $_POST['username'] ) ) : ''; ?>" required />
                </div>
                
                <div class="form-group form-group--password">
                    <label for="password"><?php esc_html_e( 'Пароль', 'woocommerce' ); ?>&nbsp;<span class="required">*</span></label>
                    <div class="password-input-wrapper">
                        <input class="woocommerce-Input woocommerce-Input--text input-text form-control" type="password" name="password" id="password" autocomplete="current-password" required />
                        <button type="button" class="password-toggle" aria-label="Показать пароль">
                            <svg width="20" height="20" viewBox="0 0 20 20" fill="none" class="password-toggle__icon password-toggle__icon--eye">
                                <path d="M10 3C5 3 1.73 7.11 1 10c.73 2.89 4 7 9 7s8.27-4.11 9-7c-.73-2.89-4-7-9-7zM10 15c-2.76 0-5-2.24-5-5s2.24-5 5-5 5 2.24 5 5-2.24 5-5 5zm0-8c-1.66 0-3 1.34-3 3s1.34 3 3 3 3-1.34 3-3-1.34-3-3-3z" fill="currentColor"/>
                            </svg>
                            <svg width="20" height="20" viewBox="0 0 20 20" fill="none" class="password-toggle__icon password-toggle__icon--eye-off" style="display: none;">
                                <path d="M1.707 1.293L18.293 17.879l-1.414 1.414L.293 2.707l1.414-1.414zM10 3c-5 0-8.27 4.11-9 7 .73 2.89 4 7 9 7 .85 0 1.66-.13 2.42-.36l-1.6-1.58C10.66 14.96 10.34 15 10 15c-2.76 0-5-2.24-5-5 0-.34.04-.66.09-.98L2.59 6.65C1.83 7.62 1.28 8.75 1 10c.73 2.89 4 7 9 7 .85 0 1.66-.13 2.42-.36l-1.6-1.58C10.66 14.96 10.34 15 10 15c-2.76 0-5-2.24-5-5 0-.34.04-.66.09-.98L2.59 6.65C1.83 7.62 1.28 8.75 1 10l7.41 3.35 1.6 1.58C9.34 14.96 9.66 15 10 15c2.76 0 5-2.24 5-5 0-.34-.04-.66-.09-.98l1.6 1.58c.76.65 1.31 1.78 1.59 2.98-.73-2.89-4-7-9-7z" fill="currentColor"/>
                            </svg>
                        </button>
                    </div>
                </div>
                
                <?php do_action( 'woocommerce_login_form' ); ?>
                
                <div class="form-group form-group--checkbox">
                    <label class="woocommerce-form__label woocommerce-form__label-for-checkbox woocommerce-form-login__rememberme checkbox-label">
                        <input class="woocommerce-form__input woocommerce-form__input-checkbox" name="rememberme" type="checkbox" id="rememberme" value="forever" />
                        <span><?php esc_html_e( 'Запомнить меня', 'woocommerce' ); ?></span>
                    </label>
                </div>
                
                <button type="submit" class="woocommerce-button button woocommerce-form-login__submit btn btn--primary btn--full auth-submit" name="login" value="<?php esc_attr_e( 'Войти', 'woocommerce' ); ?>"><?php esc_html_e( 'Войти', 'woocommerce' ); ?></button>
                
                <!-- Согласие на обработку данных -->
                <div class="auth-consent">
                    <label class="checkbox-label">
                        <input type="checkbox" name="consent" id="login-consent" required>
                        <span>Нажимая кнопку, вы даете согласие на обработку персональных данных <a href="#" class="auth-consent__link">Подробнее</a></span>
                    </label>
                </div>
                
                <div class="auth-links">
                    <a href="<?php echo esc_url( wp_lostpassword_url() ); ?>" class="auth-link"><?php esc_html_e( 'Забыли пароль?', 'woocommerce' ); ?></a>
                </div>
                
                <?php wp_nonce_field( 'woocommerce-login', 'woocommerce-login-nonce' ); ?>
                
                <?php do_action( 'woocommerce_login_form_end' ); ?>
            </form>
        </div>
        
        <!-- Форма регистрации -->
        <!-- Показываем форму регистрации всегда, независимо от настроек WooCommerce -->
        <div class="auth-form-wrapper auth-form-wrapper--register">
            <?php do_action( 'woocommerce_before_register_form' ); ?>
            
            <form method="post" class="auth-form woocommerce-form woocommerce-form-register register" <?php do_action( 'woocommerce_register_form_tag' ); ?> >
                <?php do_action( 'woocommerce_register_form_start' ); ?>
                
                <!-- Поле логина (username) - показываем всегда -->
                <div class="form-group">
                    <label for="reg_username">Логин&nbsp;<span class="required">*</span></label>
                    <input type="text" class="woocommerce-Input woocommerce-Input--text input-text form-control" name="username" id="reg_username" autocomplete="username" placeholder="Используется для входа в аккаунт" value="<?php echo ( ! empty( $_POST['username'] ) ) ? esc_attr( wp_unslash( $_POST['username'] ) ) : ''; ?>" required />
                </div>
                
                <!-- Поля имени и фамилии -->
                <div class="form-group form-group--two-columns">
                    <div>
                        <label for="reg_first_name"><?php esc_html_e( 'Имя', 'woocommerce' ); ?>&nbsp;<span class="required">*</span></label>
                        <input type="text" class="woocommerce-Input woocommerce-Input--text input-text form-control" name="first_name" id="reg_first_name" autocomplete="given-name" value="<?php echo ( ! empty( $_POST['first_name'] ) ) ? esc_attr( wp_unslash( $_POST['first_name'] ) ) : ''; ?>" required />
                    </div>
                    <div>
                        <label for="reg_last_name"><?php esc_html_e( 'Фамилия', 'woocommerce' ); ?></label>
                        <input type="text" class="woocommerce-Input woocommerce-Input--text input-text form-control" name="last_name" id="reg_last_name" autocomplete="family-name" value="<?php echo ( ! empty( $_POST['last_name'] ) ) ? esc_attr( wp_unslash( $_POST['last_name'] ) ) : ''; ?>" />
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="reg_email"><?php esc_html_e( 'Email', 'woocommerce' ); ?>&nbsp;<span class="required">*</span></label>
                    <input type="email" class="woocommerce-Input woocommerce-Input--text input-text form-control" name="email" id="reg_email" autocomplete="email" value="<?php echo ( ! empty( $_POST['email'] ) ) ? esc_attr( wp_unslash( $_POST['email'] ) ) : ''; ?>" required />
                </div>
                
                <!-- Поле пароля - показываем всегда -->
                <div class="form-group form-group--password">
                    <label for="reg_password"><?php esc_html_e( 'Пароль', 'woocommerce' ); ?>&nbsp;<span class="required">*</span></label>
                    <div class="password-input-wrapper">
                        <input type="password" class="woocommerce-Input woocommerce-Input--text input-text form-control" name="password" id="reg_password" autocomplete="new-password" required />
                        <button type="button" class="password-toggle" aria-label="Показать пароль">
                            <svg width="20" height="20" viewBox="0 0 20 20" fill="none" class="password-toggle__icon password-toggle__icon--eye">
                                <path d="M10 3C5 3 1.73 7.11 1 10c.73 2.89 4 7 9 7s8.27-4.11 9-7c-.73-2.89-4-7-9-7zM10 15c-2.76 0-5-2.24-5-5s2.24-5 5-5 5 2.24 5 5-2.24 5-5 5zm0-8c-1.66 0-3 1.34-3 3s1.34 3 3 3 3-1.34 3-3-1.34-3-3-3z" fill="currentColor"/>
                            </svg>
                            <svg width="20" height="20" viewBox="0 0 20 20" fill="none" class="password-toggle__icon password-toggle__icon--eye-off" style="display: none;">
                                <path d="M1.707 1.293L18.293 17.879l-1.414 1.414L.293 2.707l1.414-1.414zM10 3c-5 0-8.27 4.11-9 7 .73 2.89 4 7 9 7 .85 0 1.66-.13 2.42-.36l-1.6-1.58C10.66 14.96 10.34 15 10 15c-2.76 0-5-2.24-5-5 0-.34.04-.66.09-.98L2.59 6.65C1.83 7.62 1.28 8.75 1 10c.73 2.89 4 7 9 7 .85 0 1.66-.13 2.42-.36l-1.6-1.58C10.66 14.96 10.34 15 10 15c-2.76 0-5-2.24-5-5 0-.34.04-.66.09-.98L2.59 6.65C1.83 7.62 1.28 8.75 1 10l7.41 3.35 1.6 1.58C9.34 14.96 9.66 15 10 15c2.76 0 5-2.24 5-5 0-.34-.04-.66-.09-.98l1.6 1.58c.76.65 1.31 1.78 1.59 2.98-.73-2.89-4-7-9-7z" fill="currentColor"/>
                            </svg>
                        </button>
                    </div>
                </div>
                
                <?php do_action( 'woocommerce_register_form' ); ?>
                
                <button type="submit" class="woocommerce-Form-Register__submit woocommerce-button button woocommerce-form-register__submit btn btn--primary btn--full auth-submit" name="register" value="<?php esc_attr_e( 'Зарегистрироваться', 'woocommerce' ); ?>"><?php esc_html_e( 'Зарегистрироваться', 'woocommerce' ); ?></button>
                
                <!-- Согласие на обработку данных -->
                <div class="auth-consent">
                    <label class="checkbox-label">
                        <input type="checkbox" name="reg_consent" id="reg-consent" required>
                        <span>Нажимая кнопку, вы даете согласие на обработку персональных данных <a href="#" class="auth-consent__link">Подробнее</a></span>
                    </label>
                </div>
                
                <?php wp_nonce_field( 'woocommerce-register', 'woocommerce-register-nonce' ); ?>
                
                <?php do_action( 'woocommerce_register_form_end' ); ?>
            </form>
            
            <?php do_action( 'woocommerce_after_register_form' ); ?>
        </div>
        
        <?php do_action( 'woocommerce_after_customer_login_form' ); ?>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Переключение вкладок
    const tabs = document.querySelectorAll('.auth-tab');
    const wrappers = document.querySelectorAll('.auth-form-wrapper');
    
    tabs.forEach(tab => {
        tab.addEventListener('click', function() {
            const targetTab = this.getAttribute('data-tab');
            
            // Обновляем активную вкладку
            tabs.forEach(t => t.classList.remove('auth-tab--active'));
            this.classList.add('auth-tab--active');
            
            // Показываем нужную форму
            wrappers.forEach(w => {
                w.classList.remove('active');
                if (w.classList.contains('auth-form-wrapper--' + targetTab)) {
                    w.classList.add('active');
                }
            });
        });
    });
    
    // Показать/скрыть пароль
    const passwordToggles = document.querySelectorAll('.password-toggle');
    
    passwordToggles.forEach(toggle => {
        toggle.addEventListener('click', function() {
            const input = this.closest('.password-input-wrapper').querySelector('input[type="password"], input[type="text"]');
            const eyeIcon = this.querySelector('.password-toggle__icon--eye');
            const eyeOffIcon = this.querySelector('.password-toggle__icon--eye-off');
            
            if (input.type === 'password') {
                input.type = 'text';
                if (eyeIcon) eyeIcon.style.display = 'none';
                if (eyeOffIcon) eyeOffIcon.style.display = 'block';
            } else {
                input.type = 'password';
                if (eyeIcon) eyeIcon.style.display = 'block';
                if (eyeOffIcon) eyeOffIcon.style.display = 'none';
            }
        });
    });
});
</script>

