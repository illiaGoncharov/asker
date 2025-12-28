<?php
/**
 * Кастомный шаблон чекаута
 */

// Если это страница order-received — загружаем thankyou шаблон
if ( is_wc_endpoint_url( 'order-received' ) ) {
    wc_get_template( 'checkout/thankyou.php' );
    exit;
}

// РАСШИРЕННАЯ отладочная информация
error_log( '=== ASKER CHECKOUT DEBUG ===' );
error_log( 'Custom checkout template loaded' );
error_log( 'Session ID: ' . ( WC()->session ? WC()->session->get_customer_id() : 'NO SESSION' ) );
error_log( 'Cart items count: ' . ( WC()->cart ? WC()->cart->get_cart_contents_count() : 'NO CART' ) );
error_log( 'Cart is empty: ' . ( WC()->cart && WC()->cart->is_empty() ? 'YES' : 'NO' ) );
error_log( 'User ID: ' . get_current_user_id() );
error_log( 'Current URL: ' . ( isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : 'UNKNOWN' ) );
error_log( '=============================' );

// Проверяем, доступен ли WooCommerce
if ( ! function_exists( 'WC' ) || ! WC()->cart ) {
    error_log( 'CRITICAL: WooCommerce or Cart not available!' );
    echo '<div class="woocommerce-error">Ошибка: WooCommerce не инициализирован. Обратитесь к администратору.</div>';
    get_footer();
    exit;
}

get_header();
?>

<div class="checkout-page woocommerce-checkout" data-custom-checkout="true">
    <div class="container">
        
        <!-- Хлебные крошки -->
        <div class="checkout__breadcrumbs">
            <a href="<?php echo esc_url( home_url( '/' ) ); ?>">Главная</a>
            <span class="checkout__breadcrumbs-separator">/</span>
            <a href="<?php echo esc_url( wc_get_cart_url() ); ?>">Корзина</a>
            <span class="checkout__breadcrumbs-separator">/</span>
            <span class="checkout__breadcrumbs-current">Оформление заказа</span>
        </div>

        <!-- Заголовок -->
        <h1 class="section__title">Оформление заказа</h1>
        
        <!-- Индикатор сохранения данных -->
        <?php if ( is_user_logged_in() ) : ?>
        <div class="checkout__save-indicator">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none">
                <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z" stroke="currentColor" stroke-width="2"/>
                <polyline points="17,21 17,13 7,13 7,21" stroke="currentColor" stroke-width="2"/>
                <polyline points="7,3 7,8 15,8" stroke="currentColor" stroke-width="2"/>
            </svg>
            <span>Данные автоматически сохраняются</span>
        </div>
        <?php endif; ?>

        <?php
        // Проверяем корзину
        if ( function_exists( 'WC' ) && WC()->cart && ! WC()->cart->is_empty() ) :
            ?>
            <form class="checkout__form" method="post" action="<?php echo esc_url( wc_get_checkout_url() ); ?>" enctype="multipart/form-data">
                <?php wp_nonce_field( 'woocommerce-process_checkout' ); ?>
                <input type="hidden" name="woocommerce_checkout_place_order" value="1">
                <?php 
                // Получаем доступные методы оплаты
                $available_gateways = WC()->payment_gateways->get_available_payment_gateways();
                $default_gateway = '';
                if ( ! empty( $available_gateways ) ) {
                    $default_gateway = key( $available_gateways );
                }
                ?>
                <input type="hidden" name="payment_method" value="<?php echo esc_attr( $default_gateway ); ?>">
                
                <!-- Обязательные поля WooCommerce (скрытые, с дефолтными значениями) -->
                <input type="hidden" name="billing_country" value="RU">
                <input type="hidden" name="billing_state" value="SPE">
                <input type="hidden" name="billing_postcode" value="000000">
                <input type="hidden" name="billing_city" id="hidden_billing_city" value="-">
                <input type="hidden" name="billing_address_1" id="hidden_billing_address" value="-">
                <div class="checkout__content">
                    
                    <!-- Левая колонка - формы -->
                    <div class="checkout__forms">
                    
                    <!-- Тип клиента: юр/физ лицо -->
                    <div class="checkout__form-card">
                        <h3 class="checkout__form-title">Тип клиента</h3>
                        <div class="checkout__entity-toggle">
                            <label class="checkout__radio-option checkout__radio-option--entity">
                                <input type="radio" name="customer_type" value="legal" checked>
                                <span>Юридическое лицо</span>
                            </label>
                            <label class="checkout__radio-option checkout__radio-option--entity">
                                <input type="radio" name="customer_type" value="individual">
                                <span>Физическое лицо</span>
                            </label>
                        </div>
                    </div>
                    
                    <!-- Контактные данные -->
                    <div class="checkout__form-card">
                        <h3 class="checkout__form-title">Контактные данные</h3>
                        <div class="checkout__form-fields">
                            <div class="checkout__field-group">
                                <input type="text" name="billing_first_name" placeholder="Имя*" required 
                                       value="<?php echo esc_attr( get_user_meta( get_current_user_id(), 'billing_first_name', true ) ); ?>">
                                <input type="text" name="billing_last_name" placeholder="Фамилия"
                                       value="<?php echo esc_attr( get_user_meta( get_current_user_id(), 'billing_last_name', true ) ); ?>">
                            </div>
                            <div class="checkout__field-group">
                                <input type="tel" name="billing_phone" placeholder="Телефон*" required
                                       value="<?php echo esc_attr( get_user_meta( get_current_user_id(), 'billing_phone', true ) ); ?>">
                                <input type="email" name="billing_email" placeholder="E-mail*" required
                                       value="<?php echo esc_attr( get_user_meta( get_current_user_id(), 'billing_email', true ) ?: wp_get_current_user()->user_email ); ?>">
                            </div>
                            <!-- Поля для юр. лица (обязательные) -->
                            <div class="checkout__field-group checkout__legal-fields">
                                <input type="text" name="billing_company" id="billing_company" placeholder="Название организации*" required
                                       value="<?php echo esc_attr( get_user_meta( get_current_user_id(), 'billing_company', true ) ); ?>">
                                <input type="text" name="billing_tax_id" id="billing_tax_id" placeholder="ИНН организации*" required
                                       value="<?php echo esc_attr( get_user_meta( get_current_user_id(), 'billing_tax_id', true ) ); ?>">
                            </div>
                        </div>
                    </div>
                    
                    <!-- Доставка -->
                    <div class="checkout__form-card">
                        <div class="checkout__delivery-options">
                            <label class="checkout__radio-option">
                                <input type="radio" name="delivery_type" value="delivery" checked>
                                <span>Доставка</span>
                            </label>
                            <label class="checkout__radio-option">
                                <input type="radio" name="delivery_type" value="pickup">
                                <span>Самовывоз</span>
                            </label>
                        </div>
                        
                        <!-- Поля для доставки -->
                        <div class="checkout__form-fields checkout__delivery-fields">
                                   <!-- Выбор транспортной компании -->
                                   <div class="checkout__field-group checkout__field-group--full">
                                       <label for="shipping_company" class="checkout__field-label">Транспортная компания</label>
                                       <select name="shipping_company" id="shipping_company" class="checkout__select">
                                           <option value="">— Выберите ТК —</option>
                                           <option value="cdek" <?php selected( get_user_meta( get_current_user_id(), 'shipping_company', true ), 'cdek' ); ?>>СДЭК</option>
                                           <option value="pek" <?php selected( get_user_meta( get_current_user_id(), 'shipping_company', true ), 'pek' ); ?>>ПЭК</option>
                                           <option value="dellin" <?php selected( get_user_meta( get_current_user_id(), 'shipping_company', true ), 'dellin' ); ?>>Деловые линии</option>
                                           <option value="yandex" <?php selected( get_user_meta( get_current_user_id(), 'shipping_company', true ), 'yandex' ); ?>>Яндекс доставка</option>
                                       </select>
                                   </div>
                                   <div class="checkout__field-group">
                                       <input type="text" name="shipping_city" placeholder="Город"
                                              value="<?php echo esc_attr( get_user_meta( get_current_user_id(), 'shipping_city', true ) ); ?>">
                                       <input type="text" name="shipping_address_1" placeholder="Улица"
                                              value="<?php echo esc_attr( get_user_meta( get_current_user_id(), 'shipping_address_1', true ) ); ?>">
                                       <input type="text" name="shipping_address_2" placeholder="Дом"
                                              value="<?php echo esc_attr( get_user_meta( get_current_user_id(), 'shipping_address_2', true ) ); ?>">
                                   </div>
                                   <div class="checkout__field-group">
                                       <input type="text" name="shipping_apartment" placeholder="Офис/квартира"
                                              value="<?php echo esc_attr( get_user_meta( get_current_user_id(), 'shipping_apartment', true ) ); ?>">
                                       <input type="text" name="shipping_entrance" placeholder="Подъезд"
                                              value="<?php echo esc_attr( get_user_meta( get_current_user_id(), 'shipping_entrance', true ) ); ?>">
                                       <input type="text" name="shipping_floor" placeholder="Этаж"
                                              value="<?php echo esc_attr( get_user_meta( get_current_user_id(), 'shipping_floor', true ) ); ?>">
                                   </div>
                        </div>
                        
                        <!-- Поля для самовывоза -->
                        <div class="checkout__form-fields checkout__pickup-fields" style="display: none;">
                            <div class="checkout__pickup-info">
                                <h4>Адрес склада для самовывоза:</h4>
                                <p><strong>Санкт-Петербург, ул. Карпатская д. 16</strong></p>
                                <p>Время работы: Пн-Пт: 09.00 - 18.00</p>
                            </div>
                            <div class="checkout__field-group">
                                <input type="text" name="pickup_contact_person" placeholder="Контактное лицо для получения">
                                <input type="tel" name="pickup_phone" placeholder="Телефон для связи">
                            </div>
                        </div>
                    </div>
                    
                    <!-- Комментарий -->
                    <div class="checkout__form-card">
                        <h3 class="checkout__form-title">Комментарий и пожелания к заказу</h3>
                        <div class="checkout__comment-field">
                            <textarea name="order_comments" placeholder="Ваш комментарий" maxlength="500"></textarea>
                            <div class="checkout__char-count">0/500 символов</div>
                        </div>
                    </div>
                    
                </div>
                
                <!-- Правая колонка - сводка заказа -->
                <div class="checkout__order-summary">
                    <div class="checkout__order-card">
                        <h3 class="checkout__order-title">Ваш заказ</h3>
                        
                        <!-- Товары -->
                        <div class="checkout__products">
                            <?php foreach ( WC()->cart->get_cart() as $cart_item_key => $cart_item ) :
                                $product = $cart_item['data'];
                                $quantity = $cart_item['quantity'];
                            ?>
                            <div class="checkout__product">
                                <div class="checkout__product-image">
                                    <?php echo $product->get_image('thumbnail'); ?>
                                </div>
                                <div class="checkout__product-info">
                                    <div class="checkout__product-name"><?php echo $product->get_name(); ?></div>
                                    <div class="checkout__product-meta">
                                        <span class="checkout__product-qty"><?php echo $quantity; ?>шт</span>
                                        <span class="checkout__product-price"><?php echo wc_price( (float) $product->get_price() * (int) $quantity ); ?></span>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        
                        <!-- Итоги -->
                        <div class="checkout__totals">
                            <div class="checkout__total-row">
                                <span>Итого:</span>
                                <span><?php echo WC()->cart->get_cart_subtotal(); ?></span>
                            </div>
                            <?php if ( WC()->cart->get_discount_total() > 0 ) : ?>
                            <div class="checkout__total-row checkout__total-row--discount">
                                <span>Скидка</span>
                                <span>-<?php echo wc_price( WC()->cart->get_discount_total() ); ?></span>
                            </div>
                            <?php endif; ?>
                            <div class="checkout__total-row">
                                <span>Способ оплаты:</span>
                                <span>По счету</span>
                            </div>
                            <div class="checkout__total-row checkout__total-row--final">
                                <span>К оплате</span>
                                <span><?php echo WC()->cart->get_total(); ?></span>
                            </div>
                        </div>
                        
                        <!-- Кнопка подтверждения -->
                        <button type="submit" class="checkout__submit-btn">Подтвердить заказ</button>
                        
                        <!-- Дополнительная информация -->
                        <div class="checkout__order-info">
                            <p>НДС включен в стоимость товаров</p>
                            <p>Стоимость доставки рассчитывается отдельно</p>
                        </div>
                        
                    </div>
                </div>
                
            </form>
            <?php
        else :
            // Корзина пуста
            ?>
            <div class="checkout__empty-cart">
                <p>Ваша корзина пуста.</p>
                <a href="<?php echo esc_url( wc_get_page_permalink( 'shop' ) ); ?>" class="btn btn--primary">Перейти в каталог</a>
            </div>
            <?php
        endif;
        ?>

    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.querySelector('.checkout__form');
    if (!form) return;
    
    // Переключение доставка/самовывоз
    const deliveryRadios = document.querySelectorAll('input[name="delivery_type"]');
    const deliveryFields = document.querySelector('.checkout__delivery-fields');
    const pickupFields = document.querySelector('.checkout__pickup-fields');
    
    function toggleDeliveryFields() {
        const selectedValue = document.querySelector('input[name="delivery_type"]:checked')?.value;
        if (selectedValue === 'delivery') {
            if (deliveryFields) deliveryFields.style.display = 'flex';
            if (pickupFields) pickupFields.style.display = 'none';
        } else {
            if (deliveryFields) deliveryFields.style.display = 'none';
            if (pickupFields) pickupFields.style.display = 'flex';
        }
    }
    
    deliveryRadios.forEach(radio => {
        radio.addEventListener('change', toggleDeliveryFields);
    });
    
    // Инициализация при загрузке
    toggleDeliveryFields();
    
    let errorDiv = document.createElement('div');
    errorDiv.className = 'checkout__error-message';
    errorDiv.style.cssText = 'display:none;background:#ff4444;color:white;padding:15px;border-radius:8px;margin-bottom:20px;';
    form.insertBefore(errorDiv, form.firstChild);
    
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        
        errorDiv.style.display = 'none';
        
        this.querySelectorAll('input[required], select[required]').forEach(f => {
            f.style.borderColor = '#E0E0E0';
        });
        
        const requiredFields = this.querySelectorAll('input[required], select[required]');
        let hasEmptyFields = false;
        let emptyFieldNames = [];
        
        requiredFields.forEach(field => {
            if (!field.value.trim()) {
                field.style.borderColor = '#ff4444';
                hasEmptyFields = true;
                emptyFieldNames.push(field.placeholder || field.name);
            }
        });
        
        if (hasEmptyFields) {
            errorDiv.textContent = 'Заполните обязательные поля: ' + emptyFieldNames.join(', ');
            errorDiv.style.display = 'block';
            errorDiv.scrollIntoView({ behavior: 'smooth', block: 'center' });
            return false;
        }
        
        const btn = this.querySelector('.checkout__submit-btn');
        if (btn) {
            btn.textContent = 'Обрабатываем заказ...';
            btn.style.background = '#4CAF50';
            btn.disabled = true;
        }
        
        const formData = new FormData(this);
        formData.append('action', 'asker_create_order');
        
        fetch('<?php echo admin_url('admin-ajax.php'); ?>', {
            method: 'POST',
            body: formData
        })
        .then(r => r.json())
        .then(data => {
            if (data.result === 'success' && data.redirect) {
                window.location.href = data.redirect;
            } else {
                throw new Error(data.messages || 'Неизвестная ошибка');
            }
        })
        .catch(err => {
            errorDiv.textContent = err.message;
            errorDiv.style.display = 'block';
            errorDiv.scrollIntoView({ behavior: 'smooth', block: 'center' });
            
            if (btn) {
                btn.textContent = 'Подтвердить заказ';
                btn.style.background = '#FFEB3B';
                btn.disabled = false;
            }
        });
    });
});
</script>

<?php get_footer(); ?>
