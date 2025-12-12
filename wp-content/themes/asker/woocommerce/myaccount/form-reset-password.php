<?php
/**
 * Форма установки нового пароля
 *
 * Этот шаблон показывается после перехода по ссылке из email
 *
 * @package WooCommerce\Templates
 * @version 9.9.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

?>

<div class="auth-page">
    <div class="auth-tabs">
        <span class="auth-tab auth-tab--active">Новый пароль</span>
    </div>
    
    <div class="auth-container">
        <p class="auth-page-description">Введите новый пароль для вашего аккаунта</p>
        
        <?php wc_print_notices(); ?>
        
        <form method="post" class="auth-form woocommerce-ResetPassword lost_reset_password">
            <?php do_action( 'woocommerce_resetpassword_form_start' ); ?>
            
            <div class="form-group">
                <label for="password_1">Новый пароль&nbsp;<span class="required">*</span></label>
                <input type="password" class="woocommerce-Input woocommerce-Input--text input-text form-control" name="password_1" id="password_1" autocomplete="new-password" required />
            </div>
            
            <div class="form-group">
                <label for="password_2">Подтвердите пароль&nbsp;<span class="required">*</span></label>
                <input type="password" class="woocommerce-Input woocommerce-Input--text input-text form-control" name="password_2" id="password_2" autocomplete="new-password" required />
            </div>
            
            <?php do_action( 'woocommerce_resetpassword_form' ); ?>
            
            <input type="hidden" name="reset_key" value="<?php echo esc_attr( $args['key'] ); ?>" />
            <input type="hidden" name="reset_login" value="<?php echo esc_attr( $args['login'] ); ?>" />
            <input type="hidden" name="wc_reset_password" value="true" />
            <?php wp_nonce_field( 'reset_password', 'woocommerce-reset-password-nonce' ); ?>
            
            <button type="submit" class="woocommerce-button button btn btn--primary btn--full auth-submit" value="<?php esc_attr_e( 'Сохранить', 'woocommerce' ); ?>"><?php esc_html_e( 'Сохранить пароль', 'woocommerce' ); ?></button>
            
            <?php do_action( 'woocommerce_resetpassword_form_end' ); ?>
        </form>
        
        <div class="auth-links">
            <a href="<?php echo esc_url( wc_get_page_permalink( 'myaccount' ) ); ?>" class="auth-link">← Вернуться к входу</a>
        </div>
    </div>
</div>




