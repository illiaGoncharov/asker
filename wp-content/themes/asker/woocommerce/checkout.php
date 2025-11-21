<?php
/**
 * Кастомный шаблон чекаута
 */

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

<div class="woocommerce-checkout">
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
                <div class="checkout__content">
                    
                    <!-- Левая колонка - формы -->
                    <div class="checkout__forms">
                    
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
                            <div class="checkout__field-group">
                                <input type="text" name="billing_company" placeholder="Название организации"
                                       value="<?php echo esc_attr( get_user_meta( get_current_user_id(), 'billing_company', true ) ); ?>">
                                <input type="text" name="billing_tax_id" placeholder="ИНН организации"
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
                                <p><strong>г. Москва, ул. Промышленная, д. 15, стр. 2</strong></p>
                                <p>Время работы: Пн-Пт 9:00-18:00, Сб 10:00-16:00</p>
                                <p>Телефон: +7 (495) 123-45-67</p>
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
                                        <span class="checkout__product-price"><?php echo wc_price( $product->get_price() * $quantity ); ?></span>
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
    // Переключение между доставкой и самовывозом
    const deliveryRadios = document.querySelectorAll('input[name="delivery_type"]');
    const deliveryFields = document.querySelector('.checkout__delivery-fields');
    const pickupFields = document.querySelector('.checkout__pickup-fields');
    
    // Загружаем сохраненные данные пользователя
    if (typeof asker_checkout_data !== 'undefined') {
        // Заполняем поля формы сохраненными данными
        Object.keys(asker_checkout_data).forEach(fieldName => {
            const field = document.querySelector(`input[name="${fieldName}"], textarea[name="${fieldName}"]`);
            if (field && asker_checkout_data[fieldName]) {
                field.value = asker_checkout_data[fieldName];
            }
        });
        
        // Загружаем сохраненные предпочтения доставки
        if (asker_checkout_data.delivery_type) {
            const savedType = asker_checkout_data.delivery_type;
            const radioToSelect = document.querySelector(`input[name="delivery_type"][value="${savedType}"]`);
            if (radioToSelect) {
                radioToSelect.checked = true;
                if (savedType === 'delivery') {
                    deliveryFields.style.display = 'block';
                    pickupFields.style.display = 'none';
                } else {
                    deliveryFields.style.display = 'none';
                    pickupFields.style.display = 'block';
                }
            }
        }
    }
    
    deliveryRadios.forEach(radio => {
        radio.addEventListener('change', function() {
            if (this.value === 'delivery') {
                deliveryFields.style.display = 'block';
                pickupFields.style.display = 'none';
            } else {
                deliveryFields.style.display = 'none';
                pickupFields.style.display = 'block';
            }
            
            // Сохраняем предпочтение
            saveFormData();
        });
    });
    
    // Счетчик символов для комментария
    const commentTextarea = document.querySelector('textarea[name="order_comments"]');
    const charCount = document.querySelector('.checkout__char-count');
    
    if (commentTextarea && charCount) {
        commentTextarea.addEventListener('input', function() {
            const length = this.value.length;
            charCount.textContent = length + '/500 символов';
            
            if (length > 500) {
                charCount.style.color = '#ff4444';
            } else {
                charCount.style.color = '#999999';
            }
        });
    }
    
    // Базовая валидация формы
    const form = document.querySelector('.checkout__form');
    const submitBtn = document.querySelector('.checkout__submit-btn');
    
    if (form && submitBtn) {
        form.addEventListener('submit', function(e) {
            // Проверяем обязательные поля
            const requiredFields = form.querySelectorAll('input[required]');
            let isValid = true;
            
            requiredFields.forEach(field => {
                if (!field.value.trim()) {
                    field.style.borderColor = '#ff4444';
                    isValid = false;
                } else {
                    field.style.borderColor = '#E0E0E0';
                }
            });
            
            // Проверяем email
            const emailField = form.querySelector('input[type="email"]');
            if (emailField && emailField.value) {
                const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                if (!emailRegex.test(emailField.value)) {
                    emailField.style.borderColor = '#ff4444';
                    isValid = false;
                }
            }
            
            // Проверяем телефон
            const phoneField = form.querySelector('input[type="tel"]');
            if (phoneField && phoneField.value) {
                const phoneRegex = /^[\+]?[0-9\s\-\(\)]{10,}$/;
                if (!phoneRegex.test(phoneField.value)) {
                    phoneField.style.borderColor = '#ff4444';
                    isValid = false;
                }
            }
            
            if (!isValid) {
                e.preventDefault();
                // Показываем сообщение об ошибке
                submitBtn.textContent = 'Исправьте ошибки в форме';
                submitBtn.style.background = '#ff4444';
                
                setTimeout(() => {
                    submitBtn.textContent = 'Подтвердить заказ';
                    submitBtn.style.background = '#FFEB3B';
                }, 3000);
            } else {
                // Если форма валидна, позволяем WooCommerce обработать её
                submitBtn.textContent = 'Обрабатываем заказ...';
                submitBtn.style.background = '#4CAF50';
                submitBtn.disabled = true;
            }
        });
    }
    
    // Автосохранение данных формы
    const formInputs = document.querySelectorAll('.checkout__form input, .checkout__form textarea');
    formInputs.forEach(input => {
        input.addEventListener('blur', function() {
            saveFormData();
        });
    });
});

// Функция сохранения данных формы
function saveFormData() {
    const formData = new FormData(document.querySelector('.checkout__form'));
    
    fetch('<?php echo admin_url('admin-ajax.php'); ?>', {
        method: 'POST',
        body: formData.append('action', 'save_checkout_data')
    }).catch(error => {
        console.log('Ошибка сохранения данных:', error);
    });
}

// Функция показа страницы подтверждения
function showThankYouPage() {
    // Создаем модальное окно с страницей подтверждения
    const modal = document.createElement('div');
    modal.className = 'thankyou-modal';
    
    modal.innerHTML = `
        <div class="thankyou-page">
            <div class="container">
                <div class="thankyou__card">
                    <!-- Кнопка закрытия -->
                    <button class="thankyou__close-btn" onclick="closeModal()">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none">
                            <line x1="18" y1="6" x2="6" y2="18" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                            <line x1="6" y1="6" x2="18" y2="18" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                        </svg>
                    </button>
                    <div class="thankyou__header">
                        <div class="thankyou__success-icon">
                            <svg width="48" height="48" viewBox="0 0 24 24" fill="none">
                                <circle cx="12" cy="12" r="12" fill="#4CAF50"/>
                                <path d="M8 12L11 15L16 9" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </div>
                        <h1 class="thankyou__title">Заказ успешно оформлен!</h1>
                        <p class="thankyou__subtitle">Спасибо за ваш заказ. Мы свяжемся с вами в ближайшее время.</p>
                    </div>
                    
                    <div class="thankyou__content">
                        <div class="thankyou__order-details">
                            <h2 class="thankyou__section-title">Детали заказа</h2>
                            <div class="thankyou__detail-row">
                                <span class="thankyou__detail-label">Номер заказа:</span>
                                <span class="thankyou__detail-value">#${Math.floor(Math.random() * 900000) + 100000}</span>
                            </div>
                            <div class="thankyou__detail-row">
                                <span class="thankyou__detail-label">Дата оформления:</span>
                                <span class="thankyou__detail-value">${new Date().toLocaleDateString('ru-RU', { day: 'numeric', month: 'long', year: 'numeric' })} в ${new Date().toLocaleTimeString('ru-RU', { hour: '2-digit', minute: '2-digit' })}</span>
                            </div>
                            <div class="thankyou__detail-row">
                                <span class="thankyou__detail-label">Статус:</span>
                                <span class="thankyou__status-badge">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none">
                                        <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="2"/>
                                        <path d="M12 6v6l4 2" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                                    </svg>
                                    Ожидает оплаты
                                </span>
                            </div>
                            <div class="thankyou__detail-row">
                                <span class="thankyou__detail-label">Способ оплаты:</span>
                                <span class="thankyou__detail-value">По счету</span>
                            </div>
                        </div>
                        
                        <div class="thankyou__next-steps">
                            <h2 class="thankyou__section-title">Что дальше?</h2>
                            <div class="thankyou__steps">
                                <div class="thankyou__step">
                                    <div class="thankyou__step-number">1</div>
                                    <div class="thankyou__step-content">
                                        <h3>Получите счет</h3>
                                        <p>Счет будет отправлен на ваш email в течение 30 минут</p>
                                    </div>
                                </div>
                                <div class="thankyou__step">
                                    <div class="thankyou__step-number">2</div>
                                    <div class="thankyou__step-content">
                                        <h3>Оплатите счет</h3>
                                        <p>У вас есть 3 рабочих дня для оплаты</p>
                                    </div>
                                </div>
                                <div class="thankyou__step">
                                    <div class="thankyou__step-number">3</div>
                                    <div class="thankyou__step-content">
                                        <h3>Получите товар</h3>
                                        <p>Доставка в течение 2-5 рабочих дней после оплаты</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="thankyou__contact-info">
                        <h2 class="thankyou__section-title">Контактная информация</h2>
                        <div class="thankyou__contact-cards">
                            <div class="thankyou__contact-card">
                                <div class="thankyou__contact-icon">
                                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none">
                                        <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="2"/>
                                        <path d="M12 2a10 10 0 0 0-10 10c0 1.5.5 3 1.5 4.5L12 22l8.5-5.5c1-1.5 1.5-3 1.5-4.5A10 10 0 0 0 12 2z"/>
                                        <circle cx="12" cy="12" r="3" stroke="currentColor" stroke-width="2"/>
                                    </svg>
                                </div>
                                <div class="thankyou__contact-details">
                                    <h3>Ваш менеджер</h3>
                                    <p>Владимир Курдов</p>
                                </div>
                            </div>
                            <div class="thankyou__contact-card">
                                <div class="thankyou__contact-icon">
                                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none">
                                        <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z" stroke="currentColor" stroke-width="2"/>
                                        <polyline points="22,6 12,13 2,6" stroke="currentColor" stroke-width="2"/>
                                    </svg>
                                </div>
                                <div class="thankyou__contact-details">
                                    <h3>Email</h3>
                                    <p>opt@asker-corp.ru</p>
                                </div>
                            </div>
                            <div class="thankyou__contact-card">
                                <div class="thankyou__contact-icon">
                                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none">
                                        <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z" stroke="currentColor" stroke-width="2"/>
                                    </svg>
                                </div>
                                <div class="thankyou__contact-details">
                                    <h3>Телефон</h3>
                                    <p>+7 (812) 123-12-23</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="thankyou__important-info">
                        <div class="thankyou__important-header">
                            <div class="thankyou__important-icon">
                                <svg width="24" height="24" viewBox="0 0 24 24" fill="none">
                                    <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="2"/>
                                    <path d="M12 8v4" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                                    <path d="M12 16h.01" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                                </svg>
                            </div>
                            <h2 class="thankyou__section-title">Важная информация</h2>
                        </div>
                        <ul class="thankyou__important-list">
                            <li>Проверьте папку "Спам" если не получили счет в течение часа</li>
                            <li>Сохраните номер заказа для отслеживания статуса</li>
                            <li>При возникновении вопросов обращайтесь в службу поддержки</li>
                        </ul>
                    </div>
                    
                    <div class="thankyou__actions">
                        <a href="${window.location.origin}" class="thankyou__btn thankyou__btn--primary">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none">
                                <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z" stroke="currentColor" stroke-width="2"/>
                                <polyline points="9,22 9,12 15,12 15,22" stroke="currentColor" stroke-width="2"/>
                            </svg>
                            Вернуться на главную
                        </a>
                        <button class="thankyou__btn thankyou__btn--secondary" onclick="window.print()">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none">
                                <polyline points="6,9 6,2 18,2 18,9" stroke="currentColor" stroke-width="2"/>
                                <path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2" stroke="currentColor" stroke-width="2"/>
                                <rect x="6" y="14" width="12" height="8" stroke="currentColor" stroke-width="2"/>
                            </svg>
                            Распечатать заказ
                        </button>
                    </div>
                    
                    <div class="thankyou__footer-message">
                        <p>Спасибо, что выбрали наш магазин! Мы ценим ваше доверие.</p>
                    </div>
                </div>
            </div>
        </div>
    `;
    
    document.body.appendChild(modal);
    
    // Добавляем класс для скрытия хедера и футера
    document.body.classList.add('thankyou-modal-open');
    
    // Закрытие по клику на фон
    modal.addEventListener('click', function(e) {
        if (e.target === modal) {
            closeModal();
        }
    });
    
    // Закрытие по Escape
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeModal();
        }
    });
    
    function closeModal() {
        document.body.removeChild(modal);
        document.body.classList.remove('thankyou-modal-open');
    }
}
</script>

<?php get_footer(); ?>