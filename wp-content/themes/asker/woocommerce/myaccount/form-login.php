<?php
/**
 * Форма входа/регистрации
 *
 * Этот шаблон переопределяет стандартную форму WooCommerce
 *
 * @package WooCommerce\Templates
 * @version 9.9.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

?>

<!-- ASKER CUSTOM form-login.php LOADED: <?php echo date('Y-m-d H:i:s'); ?> -->
<div class="auth-page" data-template="asker-custom-form-login">
    <!-- Вкладки Вход/Регистрация - вне белой карточки -->
        <div class="auth-tabs">
            <button class="auth-tab auth-tab--active" data-tab="login">Вход</button>
        <span class="auth-tab-separator">/</span>
            <button class="auth-tab" data-tab="register">Зарегистрироваться</button>
        </div>
        
    <div class="auth-container">
        <!-- Уведомления об ошибках и успехе -->
        <?php woocommerce_output_all_notices(); ?>
        
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
                    <label for="username">Имя пользователя или Email&nbsp;<span class="required">*</span></label>
                    <input type="text" class="woocommerce-Input woocommerce-Input--text input-text form-control" name="username" id="username" autocomplete="username" value="<?php echo ( ! empty( $_POST['username'] ) ) ? esc_attr( wp_unslash( $_POST['username'] ) ) : ''; ?>" required />
                </div>
                
                <div class="form-group form-group--password">
                    <label for="password"><?php esc_html_e( 'Пароль', 'woocommerce' ); ?>&nbsp;<span class="required">*</span></label>
                    <div class="password-input-wrapper">
                        <input class="woocommerce-Input woocommerce-Input--text input-text form-control" type="password" name="password" id="password" autocomplete="current-password" required />
                        <button type="button" class="password-toggle" aria-label="Показать пароль">
                            <svg width="20" height="20" viewBox="0 0 20 20" fill="none" class="password-toggle__icon password-toggle__icon--closed">
                                <path d="M2.5 2.5L17.5 17.5M10 3.75C6.25 3.75 3.125 6.25 1.875 10C3.125 13.75 6.25 16.25 10 16.25C13.75 16.25 16.875 13.75 18.125 10C16.875 6.25 13.75 3.75 10 3.75Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                <path d="M10 12.5C11.3807 12.5 12.5 11.3807 12.5 10C12.5 8.61929 11.3807 7.5 10 7.5C8.61929 7.5 7.5 8.61929 7.5 10C7.5 11.3807 8.61929 12.5 10 12.5Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                            <svg width="20" height="20" viewBox="0 0 20 20" fill="none" class="password-toggle__icon password-toggle__icon--open" style="display: none;">
                                <path d="M10 3.75C6.25 3.75 3.125 6.25 1.875 10C3.125 13.75 6.25 16.25 10 16.25C13.75 16.25 16.875 13.75 18.125 10C16.875 6.25 13.75 3.75 10 3.75Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                <path d="M10 12.5C11.3807 12.5 12.5 11.3807 12.5 10C12.5 8.61929 11.3807 7.5 10 7.5C8.61929 7.5 7.5 8.61929 7.5 10C7.5 11.3807 8.61929 12.5 10 12.5Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
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
                        <span>Нажимая кнопку, вы даете согласие на обработку персональных данных <a href="<?php 
                            // Сначала ищем страницу по слагу
                            $privacy_page = get_page_by_path( 'privacy-policy' );
                            if ( $privacy_page && $privacy_page->post_status === 'publish' ) {
                                // Используем slug для URL
                                $privacy_url = home_url( '/privacy-policy' );
                            } else {
                                // Если не найдена по слагу, пробуем через настройки WordPress
                                $privacy_id = get_option( 'wp_page_for_privacy_policy' );
                                if ( $privacy_id ) {
                                    $privacy_page = get_post( $privacy_id );
                                    if ( $privacy_page && $privacy_page->post_status === 'publish' ) {
                                        // Обновляем slug если нужно
                                        if ( $privacy_page->post_name !== 'privacy-policy' ) {
                                            wp_update_post([
                                                'ID' => $privacy_id,
                                                'post_name' => 'privacy-policy'
                                            ]);
                                        }
                                        $privacy_url = home_url( '/privacy-policy' );
                                    } else {
                                        $privacy_url = home_url( '/privacy-policy' );
                                    }
                                } else {
                                    $privacy_url = home_url( '/privacy-policy' );
                                }
                            }
                            echo esc_url( $privacy_url );
                        ?>" class="auth-consent__link" target="_blank">Подробнее</a></span>
                    </label>
                </div>
                
                <div class="auth-links">
                    <a href="<?php echo esc_url( add_query_arg( 'lost-password', '1', wc_get_page_permalink( 'myaccount' ) ) ); ?>" class="auth-link"><?php esc_html_e( 'Забыли пароль?', 'woocommerce' ); ?></a>
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
                
                <!-- Поле имени -->
                <div class="form-group">
                    <label for="reg_first_name">Имя&nbsp;<span class="required">*</span></label>
                    <input type="text" class="woocommerce-Input woocommerce-Input--text input-text form-control" name="first_name" id="reg_first_name" autocomplete="given-name" value="<?php echo ( ! empty( $_POST['first_name'] ) ) ? esc_attr( wp_unslash( $_POST['first_name'] ) ) : ''; ?>" required />
                </div>
                
                <!-- Поле телефона -->
                <div class="form-group">
                    <label for="reg_phone">Телефон&nbsp;<span class="required">*</span></label>
                    <input type="tel" class="woocommerce-Input woocommerce-Input--text input-text form-control" name="billing_phone" id="reg_phone" autocomplete="tel" value="<?php echo ( ! empty( $_POST['billing_phone'] ) ) ? esc_attr( wp_unslash( $_POST['billing_phone'] ) ) : ''; ?>" required />
                </div>
                
                <!-- Поле email -->
                <div class="form-group">
                    <label for="reg_email">E-mail&nbsp;<span class="required">*</span></label>
                    <input type="email" class="woocommerce-Input woocommerce-Input--text input-text form-control" name="email" id="reg_email" autocomplete="email" value="<?php echo ( ! empty( $_POST['email'] ) ) ? esc_attr( wp_unslash( $_POST['email'] ) ) : ''; ?>" required />
                </div>
                
                <!-- Поле пароля -->
                <div class="form-group">
                    <label for="reg_password">Пароль&nbsp;<span class="required">*</span></label>
                    <input type="password" class="woocommerce-Input woocommerce-Input--text input-text form-control" name="password" id="reg_password" autocomplete="new-password" required minlength="8" />
                    <div class="password-requirements" id="password-requirements">
                        <p class="password-requirements__title">Пароль должен содержать:</p>
                        <ul class="password-requirements__list">
                            <li class="password-req" data-req="length"><span class="password-req__icon">○</span> Минимум 8 символов</li>
                            <li class="password-req" data-req="digit"><span class="password-req__icon">○</span> Хотя бы одну цифру</li>
                            <li class="password-req" data-req="upper"><span class="password-req__icon">○</span> Хотя бы одну заглавную букву</li>
                            <li class="password-req" data-req="special"><span class="password-req__icon">○</span> Хотя бы один спецсимвол (!@#$%^&* и др.)</li>
                        </ul>
                    </div>
                </div>
                
                <?php do_action( 'woocommerce_register_form' ); ?>
                
                <button type="submit" class="woocommerce-Form-Register__submit woocommerce-button button woocommerce-form-register__submit btn btn--primary btn--full auth-submit" name="register" value="<?php esc_attr_e( 'Зарегистрироваться', 'woocommerce' ); ?>"><?php esc_html_e( 'Зарегистрироваться', 'woocommerce' ); ?></button>
                
                <!-- Согласие на обработку данных -->
                <div class="auth-consent">
                    <label class="checkbox-label">
                        <input type="checkbox" name="reg_consent" id="reg-consent" required>
                        <span>Нажимая кнопку, вы даете согласие на обработку персональных данных <a href="<?php 
                            // Сначала ищем страницу по слагу
                            $privacy_page = get_page_by_path( 'privacy-policy' );
                            if ( $privacy_page && $privacy_page->post_status === 'publish' ) {
                                // Используем slug для URL
                                $privacy_url = home_url( '/privacy-policy' );
                            } else {
                                // Если не найдена по слагу, пробуем через настройки WordPress
                                $privacy_id = get_option( 'wp_page_for_privacy_policy' );
                                if ( $privacy_id ) {
                                    $privacy_page = get_post( $privacy_id );
                                    if ( $privacy_page && $privacy_page->post_status === 'publish' ) {
                                        // Обновляем slug если нужно
                                        if ( $privacy_page->post_name !== 'privacy-policy' ) {
                                            wp_update_post([
                                                'ID' => $privacy_id,
                                                'post_name' => 'privacy-policy'
                                            ]);
                                        }
                                        $privacy_url = home_url( '/privacy-policy' );
                                    } else {
                                        $privacy_url = home_url( '/privacy-policy' );
                                    }
                                } else {
                                    $privacy_url = home_url( '/privacy-policy' );
                                }
                            }
                            echo esc_url( $privacy_url );
                        ?>" class="auth-consent__link" target="_blank">Подробнее</a></span>
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
        toggle.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            const wrapper = this.closest('.password-input-wrapper');
            if (!wrapper) return;
            
            const input = wrapper.querySelector('input[type="password"], input[type="text"]');
            if (!input) return;
            
            const closedIcon = this.querySelector('.password-toggle__icon--closed');
            const openIcon = this.querySelector('.password-toggle__icon--open');
            
            if (input.type === 'password') {
                input.type = 'text';
                if (closedIcon) closedIcon.style.display = 'none';
                if (openIcon) openIcon.style.display = 'block';
            } else {
                input.type = 'password';
                if (closedIcon) closedIcon.style.display = 'block';
                if (openIcon) openIcon.style.display = 'none';
            }
        });
    });
    
    // Валидация пароля в реальном времени
    const regPassword = document.getElementById('reg_password');
    const requirementsList = document.getElementById('password-requirements');
    
    if (regPassword && requirementsList) {
        const requirements = {
            length: { regex: /.{8,}/, element: requirementsList.querySelector('[data-req="length"]') },
            digit: { regex: /[0-9]/, element: requirementsList.querySelector('[data-req="digit"]') },
            upper: { regex: /[A-ZА-ЯЁ]/u, element: requirementsList.querySelector('[data-req="upper"]') },
            special: { regex: /[!@#$%^&*()_+\-=\[\]{};':"\\|,.<>\/?~`]/, element: requirementsList.querySelector('[data-req="special"]') }
        };
        
        function validatePassword() {
            const password = regPassword.value;
            let allValid = true;
            
            for (const [key, req] of Object.entries(requirements)) {
                if (req.element) {
                    const icon = req.element.querySelector('.password-req__icon');
                    if (req.regex.test(password)) {
                        req.element.classList.add('password-req--valid');
                        req.element.classList.remove('password-req--invalid');
                        if (icon) icon.textContent = '✓';
                    } else {
                        req.element.classList.remove('password-req--valid');
                        if (password.length > 0) {
                            req.element.classList.add('password-req--invalid');
                        } else {
                            req.element.classList.remove('password-req--invalid');
                        }
                        if (icon) icon.textContent = '○';
                        allValid = false;
                    }
                }
            }
            
            return allValid;
        }
        
        regPassword.addEventListener('input', validatePassword);
        regPassword.addEventListener('focus', function() {
            requirementsList.style.display = 'block';
        });
        
        // Валидация при отправке формы
        const regForm = regPassword.closest('form');
        if (regForm) {
            regForm.addEventListener('submit', function(e) {
                if (!validatePassword()) {
                    e.preventDefault();
                    regPassword.focus();
                    requirementsList.style.display = 'block';
                }
            });
        }
    }
});
</script>

