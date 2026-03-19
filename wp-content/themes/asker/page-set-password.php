<?php
/**
 * Template Name: Установка пароля
 * Страница для установки пароля после подтверждения email
 */

defined( 'ABSPATH' ) || exit;

// Получаем параметры из URL
$token = isset( $_GET['key'] ) ? sanitize_text_field( $_GET['key'] ) : '';
$user_id = isset( $_GET['uid'] ) ? absint( $_GET['uid'] ) : 0;

// Проверяем валидность токена
$token_valid = false;
$user = null;

if ( $user_id && $token ) {
    $user = get_user_by( 'id', $user_id );
    if ( $user && asker_verify_email_token( $user_id, $token ) ) {
        $token_valid = true;
    }
}

get_header();
?>

<div class="set-password-page">
    <div class="container">
        <div class="set-password__card">
            
            <?php if ( $token_valid ) : ?>
                
                <h1 class="set-password__title">Создайте пароль</h1>
                <p class="set-password__subtitle">Придумайте надёжный пароль для входа в личный кабинет</p>
                
                <?php woocommerce_output_all_notices(); ?>
                
                <form method="post" class="set-password__form">
                    
                    <div class="form-group">
                        <label for="password">Пароль&nbsp;<span class="required">*</span></label>
                        <input type="password" name="password" id="password" autocomplete="new-password" required minlength="8" />
                    </div>
                    
                    <div class="form-group">
                        <label for="password_confirm">Подтвердите пароль&nbsp;<span class="required">*</span></label>
                        <input type="password" name="password_confirm" id="password_confirm" autocomplete="new-password" required />
                    </div>
                    
                    <div class="password-requirements" id="password-requirements">
                            <p class="password-requirements__title">Пароль должен содержать только латиницу и/или спецсимволы:</p>
                            <ul class="password-requirements__list">
                                <li class="password-req" data-req="length"><span class="password-req__icon">○</span> Минимум 8 символов</li>
                                <li class="password-req" data-req="digit"><span class="password-req__icon">○</span> Минимум 1 цифру</li>
                            </ul>
                        </div>
                    
                    <input type="hidden" name="user_id" value="<?php echo esc_attr( $user_id ); ?>" />
                    <input type="hidden" name="token" value="<?php echo esc_attr( $token ); ?>" />
                    <?php wp_nonce_field( 'asker_set_password', 'asker_set_password_nonce' ); ?>
                    
                    <button type="submit" class="btn btn--primary">Сохранить пароль</button>
                    
                </form>
                
            <?php else : ?>
                
                <div class="set-password__error">
                    <svg width="48" height="48" viewBox="0 0 24 24" fill="none" style="margin: 0 auto 16px; display: block;">
                        <circle cx="12" cy="12" r="10" stroke="#EF4444" stroke-width="2"/>
                        <path d="M12 8v4" stroke="#EF4444" stroke-width="2" stroke-linecap="round"/>
                        <path d="M12 16h.01" stroke="#EF4444" stroke-width="2" stroke-linecap="round"/>
                    </svg>
                    <p>Ссылка недействительна или срок её действия истёк.</p>
                    <p>Попробуйте <a href="<?php echo esc_url( wc_get_page_permalink( 'myaccount' ) ); ?>">запросить новое письмо</a> или свяжитесь с нами.</p>
                </div>
                
            <?php endif; ?>
            
        </div>
    </div>
</div>

<?php if ( $token_valid ) : ?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const passwordInput = document.getElementById('password');
    const confirmInput = document.getElementById('password_confirm');
    const requirementsList = document.getElementById('password-requirements');
    const form = document.querySelector('.set-password__form');
    
    if (passwordInput && requirementsList) {
        const requirements = {
            length: { regex: /.{8,}/, element: requirementsList.querySelector('[data-req="length"]') },
            digit: { regex: /[0-9]/, element: requirementsList.querySelector('[data-req="digit"]') },
            latin: { regex: /^[A-Za-z0-9!@#$%^&*()_+\-=\[\]{};':"\\|,.<>\/?~`]+$/, element: requirementsList.querySelector('[data-req="latin"]') },
            upper: { regex: /[A-ZА-ЯЁ]/u, element: requirementsList.querySelector('[data-req="upper"]') },
            special: { regex: /[!@#$%^&*()_+\-=\[\]{};':"\\|,.<>\/?~`]/, element: requirementsList.querySelector('[data-req="special"]') }
        };
        
        function validatePassword() {
            const password = passwordInput.value;
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
        
        passwordInput.addEventListener('input', validatePassword);
        passwordInput.addEventListener('focus', function() {
            requirementsList.style.display = 'block';
        });
        
        // Валидация при отправке формы
        if (form) {
            form.addEventListener('submit', function(e) {
                if (!validatePassword()) {
                    e.preventDefault();
                    passwordInput.focus();
                    requirementsList.style.display = 'block';
                    alert('Пароль не соответствует требованиям');
                    return false;
                }
                
                if (passwordInput.value !== confirmInput.value) {
                    e.preventDefault();
                    confirmInput.focus();
                    alert('Пароли не совпадают');
                    return false;
                }
            });
        }
    }
});
</script>
<?php endif; ?>

<?php get_footer(); ?>

