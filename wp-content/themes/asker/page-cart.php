<?php
/**
 * Template Name: Корзина
 */

get_header(); ?>

<div class="container">
        <!-- Хлебные крошки -->
        <div class="breadcrumbs">
            <a href="<?php echo home_url('/'); ?>">Главная</a> / <span>Корзина</span>
        </div>
        
        <h1 class="page-title">Корзина</h1>

        <?php if ( WC()->cart->is_empty() ) : ?>
            
            <div class="cart-empty">
                <p>Корзина пуста</p>
                <a href="<?php echo esc_url( wc_get_page_permalink( 'shop' ) ); ?>" class="btn-primary">Перейти в каталог</a>
</div>

        <?php else : ?>

            <div class="cart-layout">
                <!-- Левая колонка: таблица товаров -->
                <div class="cart-table-wrapper">
                    <form class="woocommerce-cart-form" action="<?php echo esc_url( wc_get_cart_url() ); ?>" method="post">
                        <?php wp_nonce_field( 'woocommerce-cart', 'woocommerce-cart-nonce' ); ?>
                        
                        <table class="cart-table">
                            <thead>
                                <tr>
                                    <th class="cart-checkbox">
                                        <input type="checkbox" id="select-all" />
                                    </th>
                                    <th class="cart-product-name">Наименование</th>
                                    <th class="cart-product-price">Цена</th>
                                    <th class="cart-product-quantity">Количество</th>
                                    <th class="cart-product-subtotal">Стоимость</th>
                                    <th class="cart-product-remove"></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                foreach ( WC()->cart->get_cart() as $cart_item_key => $cart_item ) {
                                    $_product   = apply_filters( 'woocommerce_cart_item_product', $cart_item['data'], $cart_item, $cart_item_key );
                                    $product_id = apply_filters( 'woocommerce_cart_item_product_id', $cart_item['product_id'], $cart_item, $cart_item_key );

                                    if ( $_product && $_product->exists() && $cart_item['quantity'] > 0 && apply_filters( 'woocommerce_cart_item_visible', true, $cart_item, $cart_item_key ) ) {
                                        $product_permalink = apply_filters( 'woocommerce_cart_item_permalink', $_product->is_visible() ? $_product->get_permalink( $cart_item ) : '', $cart_item, $cart_item_key );
                                        ?>
                                        <tr class="cart-item" data-key="<?php echo esc_attr( $cart_item_key ); ?>">
                                            <td class="cart-checkbox">
                                                <input type="checkbox" name="cart_item_select[]" value="<?php echo esc_attr( $cart_item_key ); ?>" class="cart-item-checkbox" />
                                            </td>
                                            <td class="cart-product-name">
                                                <div class="product-info">
                                                    <?php
                                                    $thumbnail = apply_filters( 'woocommerce_cart_item_thumbnail', $_product->get_image(), $cart_item, $cart_item_key );
                                                    if ( ! $product_permalink ) {
                                                        echo $thumbnail;
                                                    } else {
                                                        printf( '<a href="%s">%s</a>', esc_url( $product_permalink ), $thumbnail );
                                                    }
                                                    ?>
                                                    <div class="product-name-text">
                                                        <?php
                                                        if ( ! $product_permalink ) {
                                                            echo wp_kses_post( apply_filters( 'woocommerce_cart_item_name', $_product->get_name(), $cart_item, $cart_item_key ) );
                                                        } else {
                                                            echo wp_kses_post( apply_filters( 'woocommerce_cart_item_name', sprintf( '<a href="%s">%s</a>', esc_url( $product_permalink ), $_product->get_name() ), $cart_item, $cart_item_key ) );
                                                        }
                                                        ?>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="cart-product-price">
                                                <?php echo apply_filters( 'woocommerce_cart_item_price', WC()->cart->get_product_price( $_product ), $cart_item, $cart_item_key ); ?>
                                            </td>
                                            <td class="cart-product-quantity">
                                                <div class="quantity-controls">
                                                    <button type="button" class="qty-btn qty-minus" data-key="<?php echo esc_attr( $cart_item_key ); ?>">-</button>
                                                    <input type="number" 
                                                           class="qty-input" 
                                                           name="cart[<?php echo $cart_item_key; ?>][qty]" 
                                                           value="<?php echo esc_attr( $cart_item['quantity'] ); ?>" 
                                                           min="1" 
                                                           readonly />
                                                    <button type="button" class="qty-btn qty-plus" data-key="<?php echo esc_attr( $cart_item_key ); ?>">+</button>
                                                </div>
                                            </td>
                                            <td class="cart-product-subtotal">
                                                <?php echo apply_filters( 'woocommerce_cart_item_subtotal', WC()->cart->get_product_subtotal( $_product, $cart_item['quantity'] ), $cart_item, $cart_item_key ); ?>
                                            </td>
                                            <td class="cart-product-remove">
                                                <button type="button" class="remove-item" data-key="<?php echo esc_attr( $cart_item_key ); ?>" title="Удалить">×</button>
                                            </td>
                                        </tr>
                                        <?php
                                    }
                                }
                                ?>
                            </tbody>
                        </table>

                        <div class="cart-actions-bottom">
                            <a href="<?php echo esc_url( wc_get_page_permalink( 'shop' ) ); ?>" class="btn-continue">
                                ← Продолжить покупки
                            </a>
                            <button type="button" class="btn-remove-selected">Удалить выбранные</button>
                        </div>
                    </form>
                </div>

                <!-- Правая колонка: итоги -->
                <div class="cart-summary">
                    <button type="button" class="btn-update-cart" onclick="location.reload()">
                        Обновить корзину <span class="icon-refresh">↻</span>
                    </button>

                    <div class="coupon-section">
                        <form class="coupon-form" method="post">
                            <?php wp_nonce_field( 'woocommerce-cart', 'woocommerce-cart-nonce' ); ?>
                            <input type="text" name="coupon_code" placeholder="Введите промокод" />
                            <button type="submit" name="apply_coupon" class="btn-apply-coupon">Применить промокод</button>
                        </form>
                    </div>

                    <div class="cart-totals">
                        <div class="total-row">
                            <span class="total-label">Итого:</span>
                            <span class="total-value"><?php echo WC()->cart->get_cart_subtotal(); ?></span>
                        </div>

                        <?php if ( WC()->cart->get_discount_total() > 0 ) : ?>
                        <div class="total-row discount">
                            <span class="total-label">Скидка</span>
                            <span class="total-value">-<?php echo wc_price( WC()->cart->get_discount_total() ); ?></span>
                        </div>
                        <?php endif; ?>

                        <div class="total-row total-final">
                            <span class="total-label">К оплате</span>
                            <span class="total-value"><?php echo WC()->cart->get_total(); ?></span>
                        </div>
                    </div>

                    <a href="<?php echo esc_url( wc_get_checkout_url() ); ?>" class="btn-checkout">
                        Оформить заказ
                    </a>
                </div>
            </div>

        <?php endif; ?>
    </div>

<style>
/* Основные стили корзины */
.cart-page .page-title {
    font-size: 32px;
    font-weight: 700;
    color: #111827;
    margin: 24px 0;
}

/* Стили breadcrumbs унифицированы в main.css */

.cart-layout {
    display: grid;
    grid-template-columns: 1fr 400px;
    gap: 32px;
    margin: 32px 0;
}

/* Таблица товаров */
.cart-table-wrapper {
    background: #F5F6F8;
    border-radius: 16px;
    padding: 24px;
}

.cart-table {
    width: 100%;
    border-collapse: collapse;
}

.cart-table thead th {
    font-size: 14px;
    font-weight: 600;
    color: #6B7280;
    text-align: left;
    padding: 12px 16px;
    border-bottom: 1px solid #E5E7EB;
}

.cart-table tbody tr {
    border-bottom: 1px solid #E5E7EB;
}

.cart-table tbody td {
    padding: 24px 16px;
    vertical-align: middle;
}

.cart-checkbox {
    width: 40px;
}

.cart-checkbox input[type="checkbox"] {
    width: 20px;
    height: 20px;
    cursor: pointer;
}

.product-info {
    display: flex;
    align-items: center;
    gap: 16px;
}

.product-info img {
    width: 64px;
    height: 64px;
    object-fit: cover;
    border-radius: 8px;
}

.product-name-text {
    font-size: 14px;
    font-weight: 500;
    color: #111827;
}

.product-name-text a {
    color: #111827;
    text-decoration: none;
}

.product-name-text a:hover {
    color: #FFD600;
}

.cart-product-price {
    font-size: 16px;
    font-weight: 600;
    color: #111827;
}

.quantity-controls {
    display: inline-flex;
    align-items: center;
    border: 1px solid #E5E7EB;
    border-radius: 50px;
    overflow: hidden;
}

.qty-btn {
    width: 32px;
    height: 32px;
    background: transparent;
    border: none;
    cursor: pointer;
    font-size: 18px;
    color: #6B7280;
    transition: all 0.2s;
}

.qty-btn:hover {
    background: #F3F4F6;
    color: #111827;
}

.qty-input {
    width: 50px;
    height: 32px;
    border: none;
    text-align: center;
    font-size: 14px;
    font-weight: 600;
    color: #111827;
    background: transparent;
}

.cart-product-subtotal {
    font-size: 16px;
    font-weight: 600;
    color: #111827;
}

.remove-item {
    width: 32px;
    height: 32px;
    background: transparent;
    border: none;
    font-size: 24px;
    color: #9CA3AF;
    cursor: pointer;
    transition: all 0.2s;
}

.remove-item:hover {
    color: #EF4444;
}

/* Нижние кнопки */
.cart-actions-bottom {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-top: 24px;
}

.btn-continue {
    font-size: 14px;
    color: #6B7280;
    text-decoration: none;
    transition: color 0.2s;
}

.btn-continue:hover {
    color: #111827;
}

.btn-remove-selected {
    padding: 12px 24px;
    background: white;
    border: 1px solid #E5E7EB;
    border-radius: 50px;
    font-size: 14px;
    font-weight: 500;
    color: #111827;
    cursor: pointer;
    transition: all 0.2s;
}

.btn-remove-selected:hover {
    background: #F3F4F6;
}

/* Правая колонка */
.cart-summary {
    background: #F5F6F8;
    border-radius: 16px;
    padding: 24px;
    height: fit-content;
}

.btn-update-cart {
    width: 100%;
    padding: 12px;
    background: white;
    border: 1px solid #E5E7EB;
    border-radius: 50px;
    font-size: 14px;
    font-weight: 500;
    color: #111827;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    margin-bottom: 16px;
    transition: all 0.2s;
}

.btn-update-cart:hover {
    background: #F3F4F6;
}

.icon-refresh {
    font-size: 18px;
}

.coupon-section {
    margin-bottom: 24px;
}

.coupon-form {
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.coupon-form input {
    padding: 12px 16px;
    border: 1px solid #E5E7EB;
    border-radius: 8px;
    font-size: 14px;
}

.btn-apply-coupon {
    padding: 12px;
    background: #FFD600;
    border: none;
    border-radius: 50px;
    font-size: 14px;
    font-weight: 600;
    color: #111827;
    cursor: pointer;
    transition: all 0.2s;
}

.btn-apply-coupon:hover {
    background: #FFC700;
}

.cart-totals {
    margin: 24px 0;
    padding: 24px 0;
    border-top: 1px solid #E5E7EB;
    border-bottom: 1px solid #E5E7EB;
}

.total-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 16px;
}

.total-row:last-child {
    margin-bottom: 0;
}

.total-label {
    font-size: 16px;
    color: #6B7280;
}

.total-value {
    font-size: 16px;
    font-weight: 600;
    color: #111827;
}

.total-final .total-label,
.total-final .total-value {
    font-size: 20px;
    font-weight: 700;
}

.btn-checkout {
    width: 100%;
    padding: 16px;
    background: #111827;
    border: none;
    border-radius: 50px;
    font-size: 16px;
    font-weight: 600;
    color: white;
    text-decoration: none;
    display: block;
    text-align: center;
    cursor: pointer;
    transition: all 0.2s;
    margin-top: 24px;
}

.btn-checkout:hover {
    background: #FFD600;
    color: #111827;
}

/* Пустая корзина */
.cart-empty {
    text-align: center;
    padding: 80px 20px;
}

.cart-empty p {
    font-size: 18px;
    color: #6B7280;
    margin-bottom: 24px;
}

.btn-primary {
    display: inline-block;
    padding: 16px 32px;
    background: #111827;
    color: white;
    border-radius: 50px;
    text-decoration: none;
    font-weight: 600;
    transition: all 0.2s;
}

.btn-primary:hover {
    background: #FFD600;
    color: #111827;
}

/* Адаптив */
@media (max-width: 1024px) {
    .cart-layout {
        grid-template-columns: 1fr;
    }
}

@media (max-width: 768px) {
    .cart-table {
        font-size: 12px;
    }
    
    .product-info {
        flex-direction: column;
        align-items: flex-start;
        gap: 8px;
    }
    
    .cart-actions-bottom {
        flex-direction: column;
        gap: 12px;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Выбрать все товары
    const selectAll = document.getElementById('select-all');
    if (selectAll) {
        selectAll.addEventListener('change', function() {
            const checkboxes = document.querySelectorAll('.cart-item-checkbox');
            checkboxes.forEach(cb => cb.checked = this.checked);
        });
    }
    
    // Количество +/-
    document.querySelectorAll('.qty-minus').forEach(btn => {
        btn.addEventListener('click', function() {
            const input = this.parentElement.querySelector('.qty-input');
            const currentValue = parseInt(input.value);
            if (currentValue > 1) {
                input.value = currentValue - 1;
                input.dispatchEvent(new Event('change', { bubbles: true }));
                updateCart(this.dataset.key, currentValue - 1);
            }
        });
    });
    
    document.querySelectorAll('.qty-plus').forEach(btn => {
        btn.addEventListener('click', function() {
            const input = this.parentElement.querySelector('.qty-input');
            const currentValue = parseInt(input.value);
            input.value = currentValue + 1;
            input.dispatchEvent(new Event('change', { bubbles: true }));
            updateCart(this.dataset.key, currentValue + 1);
        });
    });
    
    // Удалить товар
    document.querySelectorAll('.remove-item').forEach(btn => {
        btn.addEventListener('click', function() {
            if (confirm('Удалить товар из корзины?')) {
                removeItem(this.dataset.key);
            }
        });
    });
    
    // Удалить выбранные - через делегирование событий
    document.addEventListener('click', function(e) {
        const btn = e.target.closest('.btn-remove-selected');
        if (!btn) return;
        
        e.preventDefault();
        e.stopPropagation();
        
        const selected = document.querySelectorAll('.cart-item-checkbox:checked');
        
        if (selected.length === 0) {
            alert('Выберите товары для удаления');
            return;
        }
        
        // Собираем ключи
        const keys = Array.from(selected).map(cb => cb.value).filter(Boolean);
        
        if (keys.length === 0) {
            alert('Не удалось определить товары для удаления');
            return;
        }
        
        // Визуальная обратная связь
        const originalText = btn.textContent;
        btn.textContent = 'Удаление...';
        btn.disabled = true;
        
        // Удаляем товары
        removeItemsSequentially(keys).finally(() => {
            setTimeout(() => {
                btn.textContent = originalText;
                btn.disabled = false;
            }, 2000);
        });
    });
    
    function updateCart(key, qty) {
        const formData = new FormData();
        formData.append('action', 'update_cart_item');
        formData.append('cart_item_key', key);
        formData.append('quantity', qty);
        
        fetch('<?php echo admin_url('admin-ajax.php'); ?>', {
            method: 'POST',
            body: formData
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                location.reload();
            }
        });
    }
    
    // Защита от двойных кликов - храним обрабатываемые ключи
    const processingKeys = new Set();
    
    function removeItem(key, reload = true) {
        // Проверяем, не обрабатывается ли уже этот товар
        if (processingKeys.has(key)) {
            return Promise.resolve({ success: false, message: 'Уже обрабатывается' });
        }
        
        // Помечаем как обрабатываемый
        processingKeys.add(key);
        
        const formData = new FormData();
        formData.append('action', 'woocommerce_remove_cart_item');
        formData.append('cart_item_key', key);
        
        // Создаем таймаут для предотвращения зависания
        const timeoutPromise = new Promise((resolve) => {
            setTimeout(() => {
                resolve({ success: false, message: 'Таймаут запроса' });
            }, 10000); // 10 секунд максимум
        });
        
        // Создаем AbortController для таймаута (fallback для старых браузеров)
        let abortController;
        let timeoutId;
        
        if (typeof AbortController !== 'undefined') {
            abortController = new AbortController();
            
            // Используем AbortSignal.timeout если доступен, иначе создаем свой таймаут
            if (typeof AbortSignal !== 'undefined' && AbortSignal.timeout) {
                abortController.signal = AbortSignal.timeout(8000);
            } else {
                timeoutId = setTimeout(() => {
                    abortController.abort();
                }, 8000);
            }
        }
        
        const fetchPromise = fetch('<?php echo admin_url('admin-ajax.php'); ?>', {
            method: 'POST',
            body: formData,
            signal: abortController ? abortController.signal : undefined
        })
        .then(r => {
            if (!r.ok) {
                throw new Error(`HTTP error! status: ${r.status}`);
            }
            return r.json();
        })
        .then(data => {
            // Очищаем таймаут если он был установлен
            if (timeoutId) {
                clearTimeout(timeoutId);
            }
            
            // Убираем из обрабатываемых после успеха
            processingKeys.delete(key);
            
            if (data.success && reload) {
                // Небольшая задержка перед перезагрузкой, чтобы пользователь видел реакцию
                setTimeout(() => {
                location.reload();
                }, 300);
            }
            return data;
        })
        .catch(error => {
            // Очищаем таймаут если он был установлен
            if (timeoutId) {
                clearTimeout(timeoutId);
            }
            
            // Убираем из обрабатываемых при ошибке
            processingKeys.delete(key);
            
            // Игнорируем ошибки от AbortController (таймаут)
            if (error.name === 'AbortError') {
                return { success: false, error: 'Таймаут запроса' };
            }
            
            return { success: false, error: error.message || error };
        });
        
        // Используем Promise.race для таймаута (fallback)
        return Promise.race([fetchPromise, timeoutPromise]);
    }
    
    // Удаление нескольких товаров последовательно
    async function removeItemsSequentially(keys) {
        if (!keys || keys.length === 0) {
            return Promise.resolve({ successes: [], errors: [] });
        }
        
        const errors = [];
        const successes = [];
        
        // Собираем все строки ДО начала удаления, чтобы не потерять их
        const rowsToRemove = [];
        
        keys.forEach((key) => {
            if (!key || key === 'undefined' || key === 'null') {
                return;
            }
            
            // Ищем чекбокс разными способами
            let checkbox = document.querySelector(`input[value="${key}"].cart-item-checkbox`);
            
            if (!checkbox) {
                checkbox = document.querySelector(`input[data-key="${key}"].cart-item-checkbox`);
            }
            
            if (!checkbox) {
                const rowWithKey = document.querySelector(`tr[data-key="${key}"]`);
                if (rowWithKey) {
                    checkbox = rowWithKey.querySelector('.cart-item-checkbox');
                }
            }
            
            if (checkbox) {
                const row = checkbox.closest('tr');
                if (row && !row.dataset.markedForRemoval) {
                    rowsToRemove.push({ key, row, checkbox });
                    row.dataset.markedForRemoval = 'true';
                    row.dataset.originalKey = key;
                }
            }
        });
        
        if (rowsToRemove.length === 0) {
            return Promise.resolve({ successes: [], errors: ['Не найдено строк'] });
        }
        
        // Удаляем товары последовательно
        for (let i = 0; i < rowsToRemove.length; i++) {
            const { key, row } = rowsToRemove[i];
            
            if (!row || !row.parentNode || row.dataset.removed === 'true') {
                continue;
            }
            
            try {
                if (i > 0) {
                    await new Promise(resolve => setTimeout(resolve, 200));
                }
                
                const result = await removeItem(key, false);
                
                if (result && result.success) {
                    successes.push(key);
                    if (row && row.parentNode) {
                        row.dataset.removed = 'true';
                    }
                } else if (result && result.data && result.data.message === 'Товар уже удален') {
                    successes.push(key);
                    if (row && row.parentNode) {
                        row.dataset.removed = 'true';
                    }
                } else {
                    errors.push(key);
                }
            } catch (error) {
                errors.push(key);
            }
        }
        
        // Только ПОСЛЕ завершения всех запросов удаляем элементы из DOM
        rowsToRemove.forEach(({ row }) => {
            if (row && row.parentNode && row.dataset.removed === 'true') {
                // Анимация исчезновения
                row.style.transition = 'opacity 0.3s ease-out, transform 0.3s ease-out';
                row.style.opacity = '0';
                row.style.transform = 'translateX(-20px)';
                
                setTimeout(() => {
                    if (row.parentNode) {
                        row.remove();
                    }
                }, 300);
            }
        });
        
        // Показываем ошибку только если есть реальные ошибки
        // И игнорируем случаи, когда товар уже был удален
        if (errors.length > 0 && errors.length === keys.length) {
            // Все попытки провалились
            alert('Ошибка при удалении товаров');
            return Promise.reject(new Error('Все удаления провалились'));
        }
        
        // После всех удалений ждем завершения анимаций и обновляем корзину
        setTimeout(() => {
            // Проверяем, остались ли еще товары в корзине
            const remainingRows = document.querySelectorAll('.cart-table tbody tr');
            const visibleRows = Array.from(remainingRows).filter(row => 
                row.offsetParent !== null && 
                !row.classList.contains('cart-empty-row') &&
                !row.dataset.markedForRemoval &&
                !row.dataset.removed &&
                row.querySelector('.cart-item-checkbox')
            );
            
            if (visibleRows.length === 0) {
                // Корзина пуста - показываем сообщение
                const cartTable = document.querySelector('.cart-table-wrapper');
                const cartLayout = document.querySelector('.cart-layout');
                if (cartTable) {
                    cartTable.innerHTML = '<div class="cart-empty"><p>Корзина пуста</p><a href="/shop" class="btn-primary">Перейти в каталог</a></div>';
                } else if (cartLayout) {
                    cartLayout.innerHTML = '<div class="cart-empty"><p>Корзина пуста</p><a href="/shop" class="btn-primary">Перейти в каталог</a></div>';
                } else {
                    location.reload();
                }
            } else {
                // Есть еще товары - перезагружаем страницу для обновления итогов и актуальных ключей
                location.reload();
            }
        }, 1000); // Увеличиваем задержку, чтобы все анимации завершились
        
        return Promise.resolve({ successes, errors });
    }
});
</script>

<?php get_footer(); ?>
