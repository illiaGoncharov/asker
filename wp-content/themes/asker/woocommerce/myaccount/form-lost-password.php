<?php
/**
 * Форма восстановления пароля
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

<div class="auth-page">
    <!-- Заголовок в стиле табов -->
    <div class="auth-tabs">
        <span class="auth-tab auth-tab--active">Восстановление пароля</span>
    </div>
    <p class="auth-page-description">Введите E-mail, указанный при регистрации и мы отправим вам ссылку для восстановления пароля</p>
    
    <div class="auth-container">
        <?php
        wc_print_notices();
        
        $lost_password_sent = isset( $_GET['reset-link-sent'] );
        ?>
        
        <?php if ( $lost_password_sent ) : ?>
            <div class="woocommerce-message">
                <?php echo esc_html( apply_filters( 'woocommerce_lost_password_confirmation_message', esc_html__( 'Ссылка для сброса пароля была отправлена на ваш email.', 'woocommerce' ) ) ); ?>
            </div>
        <?php else : ?>
            
            <form method="post" class="auth-form woocommerce-ResetPassword lost_reset_password">
                <?php do_action( 'woocommerce_lostpassword_form_start' ); ?>
                
                <div class="form-group">
                    <label for="user_login">Email&nbsp;<span class="required">*</span></label>
                    <input class="woocommerce-Input woocommerce-Input--text input-text form-control" type="email" name="user_login" id="user_login" autocomplete="username" required />
                </div>
                
                <?php do_action( 'woocommerce_lostpassword_form' ); ?>
                
                <input type="hidden" name="wc_reset_password" value="true" />
                <?php wp_nonce_field( 'lost_password', 'woocommerce-lost-password-nonce' ); ?>
                
                <button type="submit" class="woocommerce-button button woocommerce-ResetPassword__submit btn btn--primary btn--full auth-submit" value="<?php esc_attr_e( 'Отправить', 'woocommerce' ); ?>"><?php esc_html_e( 'Отправить', 'woocommerce' ); ?></button>
                
                <!-- Согласие на обработку данных -->
                <div class="auth-consent">
                    <label class="checkbox-label">
                        <input type="checkbox" name="consent" id="lost-password-consent" required>
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
                
                <?php do_action( 'woocommerce_lostpassword_form_end' ); ?>
            </form>
            
        <?php endif; ?>
        
        <div class="auth-links">
            <a href="<?php echo esc_url( wc_get_page_permalink( 'myaccount' ) ); ?>" class="auth-link">← Вернуться к входу</a>
        </div>
    </div>
</div>

