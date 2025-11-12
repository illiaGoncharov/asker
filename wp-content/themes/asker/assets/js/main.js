// Общие скрипты темы. Стараться держать без зависимостей.

// Бургер-меню навигации
document.addEventListener('DOMContentLoaded', function() {
    const navMenuToggle = document.getElementById('nav-menu-toggle');
    const navDropdownMenu = document.getElementById('nav-dropdown-menu');
    
    if (navMenuToggle && navDropdownMenu) {
        navMenuToggle.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            navDropdownMenu.classList.toggle('show');
        });
        
        // Закрываем меню при клике вне его
        document.addEventListener('click', function(e) {
            if (!navMenuToggle.contains(e.target) && !navDropdownMenu.contains(e.target)) {
                navDropdownMenu.classList.remove('show');
            }
        });
    }
});

// Функционал мобильного меню
document.addEventListener('DOMContentLoaded', function() {
    const menuToggle = document.querySelector('.mobile-menu-toggle');
    const mobileMenu = document.querySelector('.mobile-menu');
    const mobileMenuOverlay = document.querySelector('.mobile-menu-overlay');
    const menuClose = document.querySelector('.mobile-menu-close');
    
    // Открытие меню
    if (menuToggle) {
        menuToggle.addEventListener('click', function() {
            menuToggle.classList.add('active');
            mobileMenu.classList.add('active');
            mobileMenuOverlay.classList.add('active');
            document.body.style.overflow = 'hidden'; // Блокируем прокрутку страницы
        });
    }
    
    // Закрытие меню при клике на крестик
    if (menuClose) {
        menuClose.addEventListener('click', closeMobileMenu);
    }
    
    // Закрытие меню при клике на overlay
    if (mobileMenuOverlay) {
        mobileMenuOverlay.addEventListener('click', closeMobileMenu);
    }
    
    // Функция закрытия меню
    function closeMobileMenu() {
        if (menuToggle) menuToggle.classList.remove('active');
        if (mobileMenu) mobileMenu.classList.remove('active');
        if (mobileMenuOverlay) mobileMenuOverlay.classList.remove('active');
        document.body.style.overflow = ''; // Возвращаем прокрутку страницы
    }
    
    // Обновление счетчиков в мобильном меню
    function updateMobileMenuCounters() {
        // Обновляем счетчик избранного
        const favorites = JSON.parse(localStorage.getItem('favorites') || '[]');
        const mobileWishlistCount = document.querySelector('.mobile-wishlist-count');
        
        if (mobileWishlistCount) {
            mobileWishlistCount.textContent = favorites.length;
            mobileWishlistCount.style.display = favorites.length > 0 ? 'inline-flex' : 'none';
        }
        
        // Счетчик корзины обновляется через fetchCartCountFromServer
        // который уже обновляет .mobile-cart-count
    }
    
    // Обновляем счетчики при загрузке (без setInterval - он создавал бесконечный цикл)А 
    updateMobileMenuCounters();
});

// Функционал лайков для товаров
document.addEventListener('DOMContentLoaded', function() {
    
    // Проверяем наличие кнопок лайков
    const favoriteButtons = document.querySelectorAll('.favorite-btn');
    
    // Проверяем наличие счетчика в хедере
    const wishlistCounter = document.querySelector('.wishlist-count');
        
        // Используем делегирование событий для динамически добавляемых элементов
        document.addEventListener('click', function(e) {
            // Сначала проверяем btn-remove-favorite (кнопка удаления в списке избранного)
            let button = null;
            
            if (e.target.classList.contains('btn-remove-favorite')) {
                button = e.target;
            } else {
                button = e.target.closest('.btn-remove-favorite');
            }
            
            if (button) {
                // Обработка кнопки удаления из избранного
                e.preventDefault();
                e.stopPropagation();
                
                const productId = button.getAttribute('data-product-id');
                if (!productId) {
                    return;
                }
                
                const productIdNum = parseInt(productId, 10);
                if (isNaN(productIdNum)) {
                    return;
                }
                
                // Предотвращаем двойную обработку
                if (button.hasAttribute('data-processing')) {
                    return;
                }
                button.setAttribute('data-processing', 'true');
                setTimeout(function() {
                    button.removeAttribute('data-processing');
                }, 1000);
                
                // Удаляем из избранного
                let favorites = JSON.parse(localStorage.getItem('favorites') || '[]');
                favorites = favorites.map(id => parseInt(id, 10)).filter(id => !isNaN(id) && id !== productIdNum);
                localStorage.setItem('favorites', JSON.stringify(favorites));
                
                // Обновляем счетчик
                updateWishlistCounter();
                
                // Синхронизируем с сервером
                if (typeof jQuery !== 'undefined' && typeof asker_ajax !== 'undefined') {
                    jQuery.ajax({
                        url: asker_ajax.ajax_url,
                        type: 'POST',
                        data: {
                            action: 'asker_toggle_wishlist',
                            product_id: productIdNum,
                            action_type: 'remove'
                        },
                        success: function(response) {
                            updateWishlistCounter();
                            
                            // Обновляем список если в ЛК
                            const $wishlistTab = jQuery('#wishlist');
                            if ($wishlistTab.length && $wishlistTab.is(':visible')) {
                                setTimeout(function() {
                                    if (typeof renderWishlistFromLocalStorage === 'function') {
                                        renderWishlistFromLocalStorage();
                                    } else if (typeof loadWishlistFromServer === 'function') {
                                        loadWishlistFromServer();
                                    }
                                }, 100);
                            }
                        }
                    });
                }
                
                // Удаляем элемент из DOM если он в списке избранного
                if (typeof jQuery !== 'undefined') {
                    const $item = jQuery(button).closest('.wishlist-item, .product-card');
                    if ($item.length) {
                        $item.fadeOut(300, function() {
                            jQuery(this).remove();
                            // Проверяем, пуст ли список
                            const $container = jQuery('.wishlist-products, .wishlist-items, #wishlist-content');
                            if ($container.length) {
                                // Проверяем, остались ли видимые элементы после удаления
                                setTimeout(function() {
                                    const $visibleItems = $container.find('.wishlist-item, .product-card').filter(':visible');
                                    if ($visibleItems.length === 0) {
                                        $container.html('<div class="no-products"><p>В вашем избранном пока нет товаров.</p><a href="' + (window.location.origin || '') + '/shop" class="btn-primary">Перейти в каталог</a></div>');
                                    }
                                }, 50);
                            }
                        });
                    } else {
                        // Если не найден через jQuery, пробуем через обычный DOM
                        const item = button.closest('.wishlist-item, .product-card');
                        if (item) {
                            item.style.opacity = '0';
                            setTimeout(function() {
                                item.remove();
                                // Обновляем список если в ЛК
                                const $wishlistTab = jQuery('#wishlist');
                                if ($wishlistTab.length && $wishlistTab.is(':visible')) {
                                    setTimeout(function() {
                                        if (typeof renderWishlistFromLocalStorage === 'function') {
                                            renderWishlistFromLocalStorage();
                                        }
                                    }, 100);
                                }
                            }, 300);
                        }
                    }
                }
                
                return;
            }
            
            // Ищем кнопку избранного (может быть сам target или родитель)
            // Если кликнули прямо на кнопку
            if (e.target.classList.contains('favorite-btn') || e.target.classList.contains('favorite-btn-single')) {
                button = e.target;
            }
            // Если кликнули на дочерний элемент (например, img внутри кнопки)
            else {
                button = e.target.closest('.favorite-btn, .favorite-btn-single');
            }
            
            // Если кнопка не найдена - выходим
            if (!button) {
                return;
            }
            
                e.preventDefault();
                e.stopPropagation();
                
                const productId = button.getAttribute('data-product-id');
                
                if (!productId) {
                    return;
                }
            
            // Предотвращаем двойную обработку - проверяем, не обрабатывается ли уже
            if (button.hasAttribute('data-processing')) {
                return;
            }
            
            // Ставим флаг обработки
            button.setAttribute('data-processing', 'true');
            setTimeout(function() {
                button.removeAttribute('data-processing');
            }, 1000);
                
                // Получаем текущее состояние из localStorage
            // Важно: приводим productId к числу для сравнения
            const productIdNum = parseInt(productId, 10);
            if (isNaN(productIdNum)) {
                return; // Некорректный ID
            }
            
            let favorites = JSON.parse(localStorage.getItem('favorites') || '[]');
            // Преобразуем все ID в числа для корректного сравнения
            favorites = favorites.map(id => parseInt(id, 10)).filter(id => !isNaN(id));
            
            // Проверяем состояние: в localStorage ИЛИ кнопка имеет класс active
            const isCurrentlyFavorite = favorites.includes(productIdNum) || button.classList.contains('active');
            
            // Сохраняем старое состояние для обновления списка
            const wasFavorite = isCurrentlyFavorite;
                
                if (isCurrentlyFavorite) {
                    // Удаляем из избранного
                const index = favorites.indexOf(productIdNum);
                if (index !== -1) {
                    favorites.splice(index, 1);
                }
                    button.classList.remove('active');
                } else {
                    // Добавляем в избранное
                if (!favorites.includes(productIdNum)) {
                    favorites.push(productIdNum);
                }
                    button.classList.add('active');
                }
                
            // Сохраняем обновленный список
                localStorage.setItem('favorites', JSON.stringify(favorites));
            
            // Сразу обновляем счетчик локально
            updateWishlistCounter();
                
            // Синхронизируем с сервером, если пользователь залогинен
            if (typeof jQuery !== 'undefined' && typeof asker_ajax !== 'undefined') {
                jQuery.ajax({
                    url: asker_ajax.ajax_url,
                    type: 'POST',
                    data: {
                        action: 'asker_toggle_wishlist',
                        product_id: productIdNum,
                        action_type: wasFavorite ? 'remove' : 'add'
                    },
                    success: function(response) {
                        // Обновляем счетчик всегда
                        updateWishlistCounter();
                        
                        // Если удалили из избранного и вкладка "Избранное" видна - обновляем список
                        if (wasFavorite) {
                            const $wishlistTab = jQuery('#wishlist');
                            if ($wishlistTab.length && $wishlistTab.is(':visible')) {
                                // Сразу обновляем список из localStorage (быстрее)
                                setTimeout(function() {
                                    if (typeof renderWishlistFromLocalStorage === 'function') {
                                        renderWishlistFromLocalStorage();
                                    } else if (typeof loadWishlistFromServer === 'function') {
                                        loadWishlistFromServer();
                                    }
                                }, 100);
                            }
                        }
                    },
                    error: function(xhr, status, error) {
                        // При ошибке AJAX всё равно обновляем счетчик
                        updateWishlistCounter();
                        
                        // И обновляем список если в ЛК
                        const $wishlistTab = jQuery('#wishlist');
                        if ($wishlistTab.length && $wishlistTab.is(':visible')) {
                            setTimeout(function() {
                                if (typeof renderWishlistFromLocalStorage === 'function') {
                                    renderWishlistFromLocalStorage();
                                }
                            }, 100);
                        }
                    }
                });
            }
            
            // Обновляем счетчик в хедере
            updateWishlistCounter();
        });
        
        // Синхронизируем избранное при загрузке страницы (если пользователь залогинен)
        // ВРЕМЕННО ОТКЛЮЧЕНО для исправления белого экрана - переносим на событие после загрузки
        /*
        if (typeof asker_ajax !== 'undefined' && typeof jQuery !== 'undefined') {
            const favorites = JSON.parse(localStorage.getItem('favorites') || '[]');
            if (favorites.length > 0) {
                // Пытаемся синхронизировать с сервером
                jQuery.ajax({
                    url: asker_ajax.ajax_url,
                    type: 'POST',
                    data: {
                        action: 'asker_sync_wishlist',
                        product_ids: favorites
                    },
                            success: function(response) {
                                // Wishlist synced
                            }
                });
            }
        }
        */
        // Переносим на событие после полной загрузки
        window.addEventListener('load', function() {
            setTimeout(function() {
                if (typeof asker_ajax !== 'undefined' && typeof jQuery !== 'undefined') {
                    const favorites = JSON.parse(localStorage.getItem('favorites') || '[]');
                    if (favorites.length > 0) {
                        jQuery.ajax({
                            url: asker_ajax.ajax_url,
                            type: 'POST',
                            data: {
                                action: 'asker_sync_wishlist',
                                product_ids: favorites
                            },
                            success: function(response) {
                                // Wishlist synced
                            },
                            error: function() {
                                // Тихий fail
                            }
                        });
                    }
                }
            }, 2000); // Задержка 2 секунды после полной загрузки
        });
        
        // Восстанавливаем состояние лайков при загрузке
        const favorites = JSON.parse(localStorage.getItem('favorites') || '[]');
        
        // Очищаем все кнопки от лишних элементов
        document.querySelectorAll('.favorite-btn, .favorite-btn-single').forEach(button => {
            button.classList.remove('active');
            // Удаляем все дочерние элементы кроме псевдоэлементов
            const children = Array.from(button.children);
            children.forEach(child => child.remove());
            // Убираем все атрибуты кроме data-product-id
            const attrs = Array.from(button.attributes);
            attrs.forEach(attr => {
                if (attr.name !== 'data-product-id' && attr.name !== 'class') {
                    button.removeAttribute(attr.name);
                }
            });
        });
        
        // Затем добавляем active только для товаров в избранном
        favorites.forEach(productId => {
            const buttons = document.querySelectorAll(`.favorite-btn[data-product-id="${productId}"], .favorite-btn-single[data-product-id="${productId}"]`);
            buttons.forEach(button => {
                button.classList.add('active');
            });
        });
        
        // Обновляем счетчик при загрузке
        updateWishlistCounter();
        updateCartCounter();
        
        // Дополнительная проверка через небольшую задержку
        setTimeout(() => {
            updateWishlistCounter();
            updateCartCounter();
        }, 100);
        
        // Еще одна проверка через большую задержку для надежности
        setTimeout(() => {
            updateWishlistCounter();
            updateCartCounter();
        }, 500);
        
        // Периодически обновляем счетчики (каждые 10 секунд, чтобы не мигало)
        setInterval(() => {
            updateWishlistCounter();
            updateCartCounter();
        }, 10000);
        
        // Функционал кнопок "В корзину" - используем делегирование событий
        // Обработка через addEventListener с делегированием для динамически добавляемых кнопок
        document.addEventListener('click', function(e) {
            // Проверяем .btn-add-cart или .add_to_cart_button
            const button = e.target.closest('.btn-add-cart, .add_to_cart_button');
            if (!button) return;
            
            // Защита от двойных кликов
            if (button.hasAttribute('data-processing') || button.classList.contains('loading')) {
                e.preventDefault();
                e.stopPropagation();
                return;
            }
                
            const productId = button.getAttribute('data-product-id');
                if (!productId) return;
                
            // Для .btn-add-cart используем наш AJAX
            if (button.classList.contains('btn-add-cart')) {
                e.preventDefault();
                e.stopPropagation();
                
                // Помечаем как обрабатываемый
                button.setAttribute('data-processing', 'true');
                button.disabled = true;
                const originalText = button.textContent || button.innerText;
                button.textContent = 'Добавляется...';
                
                // Используем правильный AJAX endpoint
                const ajaxUrl = (typeof asker_ajax !== 'undefined' && asker_ajax.ajax_url) 
                    ? asker_ajax.ajax_url 
                    : '/wp-admin/admin-ajax.php';
                
                fetch(ajaxUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: new URLSearchParams({
                        action: 'woocommerce_add_to_cart',
                        product_id: productId,
                        quantity: 1
                    })
                })
                .then(response => {
                    // Проверяем Content-Type перед парсингом JSON
                    const contentType = response.headers.get('content-type');
                    if (!contentType || !contentType.includes('application/json')) {
                        // Если не JSON, читаем как текст для отладки
                        return response.text().then(text => {
                            console.error('Ожидался JSON, получен:', text.substring(0, 200));
                            throw new Error('Ответ не в формате JSON');
                        });
                    }
                    return response.json();
                })
                .then(data => {
                    // WooCommerce может вернуть два формата:
                    // 1. {success: true, data: {fragments: {...}, cart_hash: '...'}}
                    // 2. {fragments: {...}, cart_hash: '...'} (прямой формат фрагментов)
                    
                    // Проверяем успех по наличию fragments или success
                    const isSuccess = data.success === true || data.fragments !== undefined || (data.data && data.data.fragments);
                    
                    if (isSuccess) {
                        // Получаем fragments и cart_hash из разных мест ответа
                        const fragments = data.data?.fragments || data.fragments || {};
                        const cartHash = data.data?.cart_hash || data.cart_hash || '';
                        
                        // Сразу обновляем счетчик локально (быстрое обновление UI)
                        const cartCountEl = document.querySelector('.cart-count, .cart-counter');
                        if (cartCountEl) {
                            const currentCount = parseInt(cartCountEl.textContent || cartCountEl.getAttribute('data-count') || '0');
                            cartCountEl.textContent = currentCount + 1;
                            cartCountEl.setAttribute('data-count', currentCount + 1);
                            if (cartCountEl.style.display === 'none') {
                                cartCountEl.style.display = 'flex';
                            }
                        }
                        
                        // Обновляем через WooCommerce события - передаем jQuery объект, а не нативный DOM
                        if (typeof jQuery !== 'undefined') {
                            try {
                                // Конвертируем button в jQuery объект для WooCommerce
                                const $button = jQuery(button);
                                // Триггерим событие с правильными параметрами
                                jQuery(document.body).trigger('added_to_cart', [fragments, cartHash, $button]);
                            } catch (e) {
                                // Игнорируем ошибки WooCommerce скриптов
                                console.warn('Ошибка в WooCommerce событии (игнорируем):', e);
                            }
                        }
                        
                        // Обновляем через updateCartCounter для надежности
                        if (typeof updateCartCounter === 'function') {
                            setTimeout(() => updateCartCounter(), 500);
                        }
                        
                        // Показываем успех
                        button.textContent = 'Добавлено!';
                        button.style.background = '#4CAF50';
                        
                        setTimeout(() => {
                            button.textContent = originalText;
                            button.style.background = '';
                            button.removeAttribute('data-processing');
                            button.disabled = false;
                        }, 2000);
                    } else {
                        // Обрабатываем разные форматы ошибок от сервера
                        const errorMessage = data.data?.message || data.data || data.message || 'Ошибка добавления товара в корзину';
                        throw new Error(errorMessage);
                    }
                })
                .catch(error => {
                    console.error('Ошибка AJAX запроса:', error);
                    const errorMsg = error.message || 'Ошибка при добавлении товара в корзину';
                    alert(errorMsg);
                    button.textContent = originalText;
                    button.removeAttribute('data-processing');
                    button.disabled = false;
                });
            }
            // Для .add_to_cart_button - WooCommerce обработает сам, но мы тоже можем добавить защиту
        }, true); // Используем capture фазу для раннего перехвата
    
    // Функция для обновления счетчика избранного (глобальная)
    // Убираем рекурсивный вызов, который создавал бесконечный цикл
    window.updateWishlistCounter = function() {
        try {
            const favorites = JSON.parse(localStorage.getItem('favorites') || '[]');
            const count = favorites.length;
            
            // Обновляем десктопный счетчик
            const counter = document.querySelector('.wishlist-count');
            if (counter) {
                counter.textContent = count;
                counter.setAttribute('data-count', count.toString());
                
                if (count > 0) {
                    counter.style.opacity = '1';
                    counter.style.visibility = 'visible';
                    counter.style.display = 'flex';
                } else {
                    counter.style.opacity = '0';
                    counter.style.visibility = 'hidden';
                    counter.style.display = 'none';
                }
            }
            
            // Обновляем мобильный счетчик
            const mobileWishlistCount = document.querySelector('.mobile-wishlist-count');
            if (mobileWishlistCount) {
                mobileWishlistCount.textContent = count;
                mobileWishlistCount.style.display = count > 0 ? 'inline-flex' : 'none';
            }
            
            // УБРАНО: рекурсивный вызов создавал бесконечный цикл
            // Если счетчики не найдены - это нормально, не нужно пытаться снова
            
        } catch (error) {
            console.error('❌ Error updating wishlist counter:', error);
        }
    }
    
    // Функция для обновления счетчика корзины (глобальная)
    window.updateCartCounter = function() {
        fetchCartCountFromServer();
    }

    // Получить серверный счетчик корзины из WooCommerce
    function fetchCartCountFromServer() {
        try {
            const ajaxUrl = (window?.wc_add_to_cart_params?.ajax_url) || (window?.wp_urls?.ajax_url) || '/wp-admin/admin-ajax.php';
            const payload = new URLSearchParams();
            payload.append('action', 'asker_get_cart_count');
            fetch(ajaxUrl, { method: 'POST', credentials: 'same-origin', headers: { 'Content-Type': 'application/x-www-form-urlencoded' }, body: payload.toString() })
                .then(r => r.json())
                .then(json => {
                    if (!json || !json.success) return;
                    const serverCount = parseInt(json.data && json.data.count ? json.data.count : '0', 10) || 0;
                    
                    // Removed invalid items if needed
                    
                    // Обновляем счетчик на основе серверных данных
                    const counter = document.querySelector('.cart-count');
                    if (counter) {
                        counter.textContent = serverCount;
                        counter.setAttribute('data-count', String(serverCount));
                        if (serverCount > 0) {
                            counter.style.opacity = '1';
                            counter.style.visibility = 'visible';
                        } else {
                            counter.style.opacity = '0';
                            counter.style.visibility = 'hidden';
                        }
                    }
                    // Обновим также мобильный счетчик
                    const mobileCartCount = document.querySelector('.mobile-cart-count');
                    if (mobileCartCount) {
                        mobileCartCount.textContent = serverCount;
                        mobileCartCount.style.display = serverCount > 0 ? 'inline-flex' : 'none';
                    }
                })
                .catch(() => {});
        } catch (e) {}
    }

    // Обновляем счетчик после событий WooCommerce
    if (window.jQuery) {
        const $ = window.jQuery;
        $(document.body).on('added_to_cart updated_wc_div wc_fragments_refreshed removed_from_cart', function(e) {
            // Небольшая задержка, чтобы дать серверу время обработать запрос
            setTimeout(() => {
                fetchCartCountFromServer();
            }, 100);
        });
    }
    
    // WooCommerce сам управляет корзиной через свои AJAX-запросы
    // Мы слушаем события и обновляем счетчик
    
    // Дополнительная инициализация при полной загрузке страницы
    window.addEventListener('load', function() {
        setTimeout(() => {
            updateWishlistCounter();
            updateCartCounter();
        }, 200);
    });
    
    // Обработчик для изменения видимости страницы (когда пользователь возвращается на вкладку)
    document.addEventListener('visibilitychange', function() {
        if (!document.hidden) {
            setTimeout(() => {
                updateWishlistCounter();
                updateCartCounter();
            }, 100);
        }
    });
    

    // Функция очистки корзины на сервере
    window.clearCartOnServer = function() {
        
        // Сначала очищаем localStorage
        localStorage.removeItem('cart');
        
        // Принудительно обновляем счетчики
        const counter = document.querySelector('.cart-count');
        if (counter) {
            counter.textContent = '0';
            counter.setAttribute('data-count', '0');
            counter.style.opacity = '0';
            counter.style.visibility = 'hidden';
        }
        
        // Обновляем мобильный счетчик
        const mobileCartCount = document.querySelector('.mobile-cart-count');
        if (mobileCartCount) {
            mobileCartCount.textContent = '0';
            mobileCartCount.style.display = 'none';
        }
        
        // Показываем уведомление
        showNotification('Корзина очищена!');
        
        // Перезагружаем страницу для полной синхронизации с сервером
        setTimeout(() => {
            window.location.reload();
        }, 1000);
    };
    
    // Функция для показа уведомлений
    function showNotification(message) {
        // Создаем элемент уведомления
        const notification = document.createElement('div');
        notification.className = 'notification';
        notification.textContent = message;
        notification.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            background: #4CAF50;
            color: white;
            padding: 12px 20px;
            border-radius: 4px;
            z-index: 10000;
            font-size: 14px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            transform: translateX(100%);
            transition: transform 0.3s ease;
        `;
        
        document.body.appendChild(notification);
        
        // Анимация появления
        setTimeout(() => {
            notification.style.transform = 'translateX(0)';
        }, 100);
        
        // Удаляем через 3 секунды
        setTimeout(() => {
            notification.style.transform = 'translateX(100%)';
            setTimeout(() => {
                if (notification.parentNode) {
                    notification.parentNode.removeChild(notification);
                }
            }, 300);
        }, 3000);
    }
    
}); // Конец DOMContentLoaded для избранного и корзины

// Мобильные фильтры - открытие/закрытие боковой панели
document.addEventListener('DOMContentLoaded', function() {
    const filtersToggleBtn = document.querySelector('.filters-toggle-btn');
    const filtersCloseBtn = document.querySelector('.filters-close-btn');
    const shopSidebar = document.querySelector('.shop-sidebar');
    const shopSidebarOverlay = document.querySelector('.shop-sidebar-overlay');
    
    // Закрытие фильтров
    function closeFilters() {
        if (shopSidebar) {
            shopSidebar.classList.remove('active');
        }
        if (shopSidebarOverlay) {
            shopSidebarOverlay.classList.remove('active');
        }
        document.body.style.overflow = ''; // Возвращаем прокрутку страницы
    }
    
    // Открытие фильтров
    if (filtersToggleBtn && shopSidebar) {
        filtersToggleBtn.addEventListener('click', function(e) {
            e.preventDefault();
            shopSidebar.classList.add('active');
            if (shopSidebarOverlay) {
                shopSidebarOverlay.classList.add('active');
            }
            document.body.style.overflow = 'hidden'; // Блокируем прокрутку страницы
        });
    }
    
    // Закрытие через кнопку закрытия
    if (filtersCloseBtn) {
        filtersCloseBtn.addEventListener('click', closeFilters);
    }
    
    // Закрытие при клике на overlay
    if (shopSidebarOverlay) {
        shopSidebarOverlay.addEventListener('click', closeFilters);
    }
    
    // Закрытие при нажатии Escape
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && shopSidebar && shopSidebar.classList.contains('active')) {
            closeFilters();
        }
    });
});

// Фильтры каталога - переход по категориям (с делегированием событий для динамических элементов)
document.addEventListener('DOMContentLoaded', function() {
    console.log('Filter script loaded'); // Для отладки
    
    // Обработчик изменения чекбокса
    function handleCategoryChange(checkbox) {
        const url = checkbox.getAttribute('data-url');
        console.log('Checkbox changed:', checkbox.checked, 'URL:', url); // Для отладки
        
        if (!url) {
            console.error('No URL found for checkbox');
            return;
        }
        
        if (checkbox.checked) {
            // Снимаем отметку с других чекбоксов
            document.querySelectorAll('.filter-checkbox input[type="checkbox"]').forEach(cb => {
                if (cb !== checkbox) {
                    cb.checked = false;
                }
            });
            // Переходим на страницу категории
            console.log('Navigating to:', url);
            window.location.href = url;
        } else {
            // Если снята галочка - возвращаемся в общий каталог
            const resetBtn = document.querySelector('.filter-reset-btn');
            if (resetBtn && resetBtn.href) {
                console.log('Resetting to shop page:', resetBtn.href);
                window.location.href = resetBtn.href;
            }
        }
    }
    
    // Используем делегирование событий для работы с динамически добавляемыми элементами
    document.addEventListener('change', function(e) {
        // Проверяем, что изменение было на чекбоксе категории
        if (e.target && e.target.matches('.filter-checkbox input[type="checkbox"]')) {
            e.preventDefault();
            e.stopPropagation();
            handleCategoryChange(e.target);
        }
    });
    
    // Также обрабатываем клики на label (на случай если change не срабатывает)
    document.addEventListener('click', function(e) {
        // Проверяем клик на label или span внутри label
        const label = e.target.closest('.filter-checkbox');
        if (label && !e.target.matches('input[type="checkbox"]')) {
            e.preventDefault();
            e.stopPropagation();
            const checkbox = label.querySelector('input[type="checkbox"]');
            if (checkbox && !checkbox.disabled) {
                // Переключаем состояние чекбокса вручную
                checkbox.checked = !checkbox.checked;
                // Вызываем обработчик
                handleCategoryChange(checkbox);
            }
        }
    });
    
    // Инициализация для уже существующих элементов
    document.querySelectorAll('.filter-checkbox input[type="checkbox"]').forEach(checkbox => {
        console.log('Found checkbox:', checkbox.getAttribute('data-url'));
    });
});

// Range slider для фильтра цены в shop sidebar
document.addEventListener('DOMContentLoaded', function() {
    const priceSliderMin = document.querySelector('.price-slider-min');
    const priceSliderMax = document.querySelector('.price-slider-max');
    const priceInputMin = document.querySelector('input[name="min_price"]');
    const priceInputMax = document.querySelector('input[name="max_price"]');
    const priceSliderWrapper = document.querySelector('.price-slider-wrapper');
    
    // Получаем динамические значения min/max из data-атрибутов
    let dynamicMin = 0;
    let dynamicMax = 256000;
    
    if (priceSliderWrapper) {
        dynamicMin = parseInt(priceSliderWrapper.getAttribute('data-min')) || 0;
        dynamicMax = parseInt(priceSliderWrapper.getAttribute('data-max')) || 256000;
    } else if (priceInputMin && priceInputMax) {
        // Fallback: получаем из input полей
        dynamicMin = parseInt(priceInputMin.getAttribute('data-min')) || parseInt(priceInputMin.getAttribute('min')) || 0;
        dynamicMax = parseInt(priceInputMax.getAttribute('data-max')) || parseInt(priceInputMax.getAttribute('max')) || 256000;
    }
    
    // Функция обновления фильтра по цене
    let priceFilterTimeout;
    function updatePriceFilterFromInputs() {
        clearTimeout(priceFilterTimeout);
        // Добавляем небольшую задержку для слайдера, чтобы не перезагружать страницу при каждом движении
        priceFilterTimeout = setTimeout(function() {
            const minPrice = priceInputMin.value;
            const maxPrice = priceInputMax.value;
            
            const url = new URL(window.location);
            // Удаляем старые параметры цены
            url.searchParams.delete('min_price');
            url.searchParams.delete('max_price');
            
            // Добавляем параметры цены только если они отличаются от значений по умолчанию
            // или если они были явно изменены пользователем
            if (minPrice && minPrice != dynamicMin) {
                url.searchParams.set('min_price', minPrice);
            }
            if (maxPrice && maxPrice != dynamicMax) {
                url.searchParams.set('max_price', maxPrice);
            }
            
            // Перезагружаем страницу с новыми параметрами
            window.location.href = url.toString();
        }, 500); // Задержка 500мс для слайдера
    }
    
    if (priceSliderMin && priceSliderMax && priceInputMin && priceInputMax) {
        // Синхронизация slider -> input
        priceSliderMin.addEventListener('input', function() {
            const minValue = parseInt(this.value);
            const maxValue = parseInt(priceSliderMax.value);
            if (minValue > maxValue) {
                priceSliderMax.value = minValue;
                priceInputMax.value = minValue;
            }
            priceInputMin.value = minValue;
            updatePriceSliderBackground();
            // Применяем фильтр при изменении слайдера
            updatePriceFilterFromInputs();
        });
        
        priceSliderMax.addEventListener('input', function() {
            const maxValue = parseInt(this.value);
            const minValue = parseInt(priceSliderMin.value);
            if (maxValue < minValue) {
                priceSliderMin.value = maxValue;
                priceInputMin.value = maxValue;
            }
            priceInputMax.value = maxValue;
            updatePriceSliderBackground();
            // Применяем фильтр при изменении слайдера
            updatePriceFilterFromInputs();
        });
        
        // Синхронизация input -> slider
        priceInputMin.addEventListener('input', function() {
            const minValue = parseInt(this.value) || 0;
            const maxValue = parseInt(priceSliderMax.value);
            if (minValue > maxValue) {
                priceSliderMax.value = minValue;
                priceInputMax.value = minValue;
            }
            priceSliderMin.value = minValue;
            updatePriceSliderBackground();
        });
        
        priceInputMax.addEventListener('input', function() {
            const maxValue = parseInt(this.value) || 256000;
            const minValue = parseInt(priceSliderMin.value);
            if (maxValue < minValue) {
                priceSliderMin.value = maxValue;
                priceInputMin.value = maxValue;
            }
            priceSliderMax.value = maxValue;
            updatePriceSliderBackground();
        });
        
        // Функция обновления фона слайдера
        function updatePriceSliderBackground() {
            const minValue = parseInt(priceSliderMin.value);
            const maxValue = parseInt(priceSliderMax.value);
            const sliderMax = parseInt(priceSliderMax.getAttribute('max')) || dynamicMax;
            
            // Вычисляем проценты относительно динамического максимума
            const minPercent = sliderMax > 0 ? (minValue / sliderMax) * 100 : 0;
            const maxPercent = sliderMax > 0 ? (maxValue / sliderMax) * 100 : 100;
            
            const wrapper = document.querySelector('.price-slider-wrapper');
            if (wrapper) {
                wrapper.style.setProperty('--min-percent', minPercent + '%');
                wrapper.style.setProperty('--max-percent', maxPercent + '%');
            }
        }
        
        // Инициализация при загрузке
        updatePriceSliderBackground();
    }
});

    // Простой слайдер цены (визуальный, без jQuery UI)
    const priceSlider = document.getElementById('price-slider');
    if (priceSlider) {
        const minInput = document.querySelector('input[name="min_price"]');
        const maxInput = document.querySelector('input[name="max_price"]');
        
        // Создаем простую визуализацию диапазона
        const updateSlider = () => {
            const min = parseInt(minInput.value) || 0;
            const max = parseInt(maxInput.value) || 256000;
            const minPercent = (min / 256000) * 100;
            const maxPercent = (max / 256000) * 100;
            
            priceSlider.style.background = `linear-gradient(to right, 
                #ddd ${minPercent}%, 
                var(--primary-yellow) ${minPercent}%, 
                var(--primary-yellow) ${maxPercent}%, 
                #ddd ${maxPercent}%)`;
        };
        
        updateSlider();
        
        minInput.addEventListener('input', updateSlider);
        maxInput.addEventListener('input', updateSlider);
    }
    
    // Full-bleed стили теперь применяются через CSS без JavaScript

// Фильтр цены в каталоге
document.addEventListener('DOMContentLoaded', function() {
    const minPriceInput = document.querySelector('input[name="min_price"]');
    const maxPriceInput = document.querySelector('input[name="max_price"]');
    
    if (minPriceInput && maxPriceInput) {
        // Обновляем URL при изменении цены
        function updatePriceFilter() {
            const minPrice = minPriceInput.value;
            const maxPrice = maxPriceInput.value;
            
            const url = new URL(window.location);
            if (minPrice) url.searchParams.set('min_price', minPrice);
            else url.searchParams.delete('min_price');
            
            if (maxPrice) url.searchParams.set('max_price', maxPrice);
            else url.searchParams.delete('max_price');
            
            window.location.href = url.toString();
        }
        
        // Применяем фильтр при нажатии Enter
        minPriceInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
            e.preventDefault();
                updatePriceFilter();
            }
        });
        
        maxPriceInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                updatePriceFilter();
            }
        });
        
        // Применяем фильтр при потере фокуса (blur)
        minPriceInput.addEventListener('blur', updatePriceFilter);
        maxPriceInput.addEventListener('blur', updatePriceFilter);
    }
});

// Переключение вкладок в личном кабинете
function initAccountTabs() {
    const accountNav = document.querySelector('.account-nav');
    
    if (!accountNav) {
        return false;
    }
    
    // Используем делегирование событий для надёжности
    accountNav.addEventListener('click', function(e) {
        const navItem = e.target.closest('.nav-item');
        if (!navItem) {
            return;
        }
        
            e.preventDefault();
        
        const targetTab = navItem.getAttribute('data-tab');
        if (!targetTab) {
            return;
        }
        
        // Убираем активный класс у всех элементов навигации
        accountNav.querySelectorAll('.nav-item').forEach(nav => {
            nav.classList.remove('active');
        });
        
        // Добавляем активный класс к текущему элементу
        navItem.classList.add('active');
        
        // Скрываем все вкладки (ищем по всему документу, не только внутри .account-content)
        document.querySelectorAll('.tab-content').forEach(tab => {
            tab.classList.remove('active');
        });
        
        // Показываем нужную вкладку
        const targetContent = document.getElementById(targetTab);
        if (targetContent) {
            targetContent.classList.add('active');
            
            // Если переключились на вкладку "Избранное" - обновляем список
            if (targetTab === 'wishlist') {
                updateWishlistTab();
            }
        }
    });
    
    return true;
}

// Функция обновления вкладки "Избранное"
function updateWishlistTab() {
    if (typeof jQuery === 'undefined' || typeof asker_ajax === 'undefined') {
        console.warn('⚠️ jQuery или asker_ajax не доступен');
        return;
    }
    
    const $wishlistContainer = jQuery('.wishlist-products');
    if (!$wishlistContainer.length) {
        console.warn('⚠️ Контейнер избранного не найден');
        return;
    }
    
    // Показываем индикатор загрузки
    $wishlistContainer.html('<div class="wishlist-loading">Обновление избранного...</div>');
    
    // Синхронизируем localStorage с сервером
    const localFavorites = JSON.parse(localStorage.getItem('favorites') || '[]');
    
    if (localFavorites.length > 0) {
        // Сначала синхронизируем с сервером
        jQuery.ajax({
            url: asker_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'asker_sync_wishlist',
                product_ids: localFavorites
            },
            success: function(response) {
                if (response.success) {
                    // Затем загружаем актуальный список
                    loadWishlistFromServer();
                }
            },
            error: function() {
                // Если синхронизация не удалась, всё равно загружаем список
                loadWishlistFromServer();
            }
        });
    } else {
        // Если localStorage пуст, просто загружаем с сервера
        loadWishlistFromServer();
    }
}

// Функция загрузки избранного с сервера
function loadWishlistFromServer() {
    if (typeof jQuery === 'undefined' || typeof asker_ajax === 'undefined') {
        renderWishlistFromLocalStorage();
        return;
    }
    
    const $wishlistContainer = jQuery('.wishlist-products');
    
    // Используем AJAX endpoint для получения HTML
    jQuery.ajax({
        url: asker_ajax.ajax_url,
        type: 'POST',
        data: {
            action: 'asker_get_wishlist_products',
            product_ids: [] // Пустой массив = загрузить из user_meta для авторизованных
        },
        success: function(response) {
            if (response.success && response.data && response.data.html) {
                $wishlistContainer.html(response.data.html);
                
                // Обновляем состояние кнопок лайков после загрузки
                setTimeout(function() {
                    const favorites = JSON.parse(localStorage.getItem('favorites') || '[]');
                    jQuery('.favorite-btn').each(function() {
                        const productId = parseInt(jQuery(this).attr('data-product-id'));
                        if (favorites.includes(productId)) {
                            jQuery(this).addClass('active');
                        }
                    });
                }, 100);
            } else {
                renderWishlistFromLocalStorage();
            }
        },
        error: function() {
            renderWishlistFromLocalStorage();
        }
    });
}

// Функция рендеринга избранного из localStorage
function renderWishlistFromLocalStorage() {
    if (typeof jQuery === 'undefined') {
        return;
    }
    
    const $wishlistContainer = jQuery('.wishlist-products');
    if (!$wishlistContainer.length) {
        return;
    }
    
    const favorites = JSON.parse(localStorage.getItem('favorites') || '[]');
    
    // Преобразуем в числа и фильтруем валидные ID
    const favoriteIds = favorites.map(id => parseInt(id, 10)).filter(id => !isNaN(id) && id > 0);
    
    if (favoriteIds.length === 0) {
        $wishlistContainer.html('<div class="no-products"><p>В вашем избранном пока нет товаров.</p><a href="' + window.location.origin + '/shop" class="btn-primary">Перейти в каталог</a></div>');
        return;
    }
    
    // Загружаем информацию о товарах
    if (typeof asker_ajax !== 'undefined') {
        jQuery.ajax({
            url: asker_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'asker_get_wishlist_products',
                product_ids: favoriteIds
            },
            success: function(response) {
                if (response.success && response.data && response.data.html) {
                    $wishlistContainer.html(response.data.html);
                    
                    // Обновляем состояние кнопок лайков после загрузки
                    setTimeout(function() {
                        const currentFavorites = JSON.parse(localStorage.getItem('favorites') || '[]').map(id => parseInt(id, 10));
                        jQuery('.favorite-btn, .favorite-btn-single').each(function() {
                            const productId = parseInt(jQuery(this).attr('data-product-id'));
                            if (currentFavorites.includes(productId)) {
                                jQuery(this).addClass('active');
                            } else {
                                jQuery(this).removeClass('active');
                            }
                        });
                    }, 100);
                } else {
                    $wishlistContainer.html('<div class="no-products"><p>Не удалось загрузить избранное.</p></div>');
                }
            },
            error: function() {
                $wishlistContainer.html('<div class="no-products"><p>Ошибка загрузки избранного.</p></div>');
            }
        });
    } else {
        $wishlistContainer.html('<div class="no-products"><p>Ошибка загрузки избранного.</p></div>');
    }
}

// Инициализация при загрузке DOM
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initAccountTabs);
} else {
    // DOM уже загружен
    initAccountTabs();
}

// ===== КНОПКИ +/- для количества товара =====
// КРИТИЧНО: Ждем загрузки jQuery перед выполнением кода
(function() {
    'use strict';
    
    function initJQueryCode() {
        if (typeof jQuery === 'undefined') {
            setTimeout(initJQueryCode, 50);
            return;
        }
        
        jQuery(document).ready(function($) {
    
    // Функция обновления quantity у кнопки корзины
    function updateCartButtonQuantity(input) {
        const $input = $(input);
        const newValue = $input.val() || $input.attr('data-quantity') || $input.attr('min') || 1;
        
        // Обновляем data-quantity у input
        $input.attr('data-quantity', newValue);
        $input.val(newValue); // Синхронизируем значение
        
        // Обновляем data-quantity у кнопки корзины
        const $productCard = $input.closest('.shop-product-card');
        if ($productCard.length > 0) {
            const $cartBtn = $productCard.find('.add_to_cart_button');
            if ($cartBtn.length > 0) {
                $cartBtn.attr('data-quantity', newValue);
            }
        }
    }
    
    // Обработчик кнопок +/-
    $(document).on('click', '.qty-minus, .qty-plus', function(e) {
            e.preventDefault();
        e.stopPropagation();
        
        const $button = $(this);
        const $wrapper = $button.closest('.quantity-wrapper');
        const $input = $wrapper.find('input.qty');
        
        if ($input.length === 0) return false;
        
        let currentValue = parseInt($input.val(), 10);
        if (isNaN(currentValue) || currentValue < 1) {
            currentValue = parseInt($input.attr('data-quantity'), 10) || 
                          parseInt($input.attr('min'), 10) || 1;
        }
        
        const minValue = parseInt($input.attr('min'), 10) || 1;
        const maxValue = parseInt($input.attr('max'), 10) || 999;
        
        // Изменяем значение
        if ($button.hasClass('qty-minus') && currentValue > minValue) {
            $input.val(currentValue - 1);
            updateCartButtonQuantity($input);
        } else if ($button.hasClass('qty-plus') && currentValue < maxValue) {
            $input.val(currentValue + 1);
            updateCartButtonQuantity($input);
        }
        
        return false;
    });
    
    // Перехватываем клик на кнопку "В корзину" ПЕРЕД WooCommerce (но НЕ блокируем!)
    $(document).on('click', '.add_to_cart_button', function(e) {
        const $btn = $(this);
        
        // НЕ используем preventDefault - пусть WooCommerce обрабатывает клик стандартно
        
        // Только для карточек товаров в каталоге
        const $productCard = $btn.closest('.shop-product-card');
        if ($productCard.length > 0) {
            const $input = $productCard.find('input.qty');
            
            if ($input.length > 0) {
                // Получаем quantity из разных источников (приоритет: value > data-quantity > min > 1)
                let quantity = parseInt($input.val(), 10);
                
                if (isNaN(quantity) || quantity < 1) {
                    quantity = parseInt($input.attr('data-quantity'), 10);
                }
                
                if (isNaN(quantity) || quantity < 1) {
                    quantity = parseInt($input.attr('min'), 10);
                }
                
                if (isNaN(quantity) || quantity < 1) {
                    quantity = 1;
                }
                
                // Обновляем все атрибуты
                $btn.attr('data-quantity', quantity);
                $input.val(quantity);
                $input.attr('data-quantity', quantity);
                
                // Отключаем встроенную валидацию HTML5 (сбрасываем ошибку)
                if ($input[0]) {
                    $input[0].setCustomValidity('');
                }
                
                // Обновляем href если есть
                const currentHref = $btn.attr('href');
                if (currentHref) {
                    try {
                        const url = new URL(currentHref, window.location.origin);
                        url.searchParams.set('quantity', quantity);
                        $btn.attr('href', url.toString());
                    } catch (err) {
                        // Игнорируем ошибки парсинга URL
                    }
                }
            } else {
                // Если input не найден, ставим 1
                $btn.attr('data-quantity', '1');
            }
            
            // Сохраняем оригинальный текст для восстановления
            if (!$btn.data('original-text')) {
                $btn.data('original-text', $btn.text().trim() || 'В корзину');
            }
        }
    });
    
    // Обработка состояния кнопок "В корзину" после AJAX запроса WooCommerce
    $(document.body).on('adding_to_cart', function(e, $button, data) {
        // WooCommerce добавляет класс loading автоматически
        const $btn = $($button);
        
        if (!$btn || !$btn.length) {
            // Если кнопка не передана, ищем все кнопки в состоянии loading
            $('.add_to_cart_button.loading').each(function() {
                const $btn2 = $(this);
                const timeoutId = setTimeout(function() {
                    if ($btn2.hasClass('loading')) {
                        console.warn('⚠️ Таймаут: принудительно убираем loading с кнопки');
                        clearLoadingState($btn2);
                    }
                }, 5000); // 5 секунд максимум
                $btn2.data('loading-timeout', timeoutId);
            });
            return;
        }
        
        // Устанавливаем таймаут на случай, если событие added_to_cart не сработает
        const timeoutId = setTimeout(function() {
            if ($btn.hasClass('loading')) {
                console.warn('⚠️ Таймаут: принудительно убираем loading с кнопки');
                clearLoadingState($btn);
            }
        }, 5000); // 5 секунд максимум (уменьшили с 10)
        
        // Сохраняем ID таймаута в data атрибут
        $btn.data('loading-timeout', timeoutId);
    });
    
    // Функция очистки состояния загрузки
    function clearLoadingState($btn) {
        $btn.removeClass('loading');
        $btn.prop('disabled', false);
        
        // Убираем таймаут если есть
        const timeoutId = $btn.data('loading-timeout');
        if (timeoutId) {
            clearTimeout(timeoutId);
            $btn.removeData('loading-timeout');
        }
    }
    
    $(document.body).on('added_to_cart', function(e, fragments, cart_hash, $button) {
        // Предотвращаем появление кнопки "View cart" в карточках товаров
        // Удаляем все появившиеся элементы .added_to_cart или .wc-forward
        setTimeout(function() {
            $('.shop-product-card .added_to_cart, .shop-product-card .wc-forward, .product-card .added_to_cart, .product-card .wc-forward').remove();
            $('.add_to_cart_button.added').removeClass('added').text('В корзину');
        }, 100);
        
        // Товар успешно добавлен - убираем состояние загрузки
        let $btn = $($button);
        
        // Если кнопка не передана, ищем все кнопки в состоянии loading
        if (!$btn || !$btn.length) {
            $('.add_to_cart_button.loading').each(function() {
                const $btn2 = $(this);
                clearLoadingState($btn2);
                
                const originalText = $btn2.text().trim() || 'В корзину';
                $btn2.text('Добавлено!').css({
                    'background-color': '#4CAF50',
                    'opacity': '1'
                });
                
                setTimeout(function() {
                    $btn2.text(originalText).css({
                        'background-color': '',
                        'opacity': ''
                    });
                }, 2000);
            });
        } else {
            // Убираем класс loading
            clearLoadingState($btn);
            
            // Показываем краткое уведомление
            const originalText = $btn.text().trim() || 'В корзину';
            $btn.text('Добавлено!').css({
                'background-color': '#4CAF50',
                'opacity': '1'
            });
            
            // Возвращаем исходное состояние через 2 секунды
            setTimeout(function() {
                $btn.text(originalText).css({
                    'background-color': '',
                    'opacity': ''
                });
            }, 2000);
        }
        
        // Обновляем счетчик корзины
        if (typeof updateCartCounter === 'function') {
            updateCartCounter();
        }
        // Также обновляем через сервер для надежности
        setTimeout(function() {
            if (typeof fetchCartCountFromServer === 'function') {
                fetchCartCountFromServer();
            }
        }, 200);
    });
    
    // Дополнительная очистка при обновлении фрагментов WooCommerce
    $(document.body).on('wc_fragments_refreshed updated_wc_div', function(e) {
        // Обновляем счетчик при обновлении фрагментов
        if (typeof fetchCartCountFromServer === 'function') {
            setTimeout(function() {
                fetchCartCountFromServer();
            }, 100);
        }
        // WooCommerce обновил фрагменты - очищаем все залипшие кнопки
        setTimeout(function() {
            $('.add_to_cart_button.loading').each(function() {
                const $btn = $(this);
                console.log('🧹 Очистка loading после обновления фрагментов WooCommerce');
                clearLoadingState($btn);
                
                // Восстанавливаем текст если нужно
                const originalText = $btn.data('original-text') || 'В корзину';
                if ($btn.text().trim() === '' || $btn.text().trim() === 'Добавление...') {
                    $btn.text(originalText).css({
                        'background-color': '',
                        'opacity': '1'
                    });
                }
            });
        }, 100);
    });
    
    // Дополнительная очистка при любых AJAX запросах jQuery
    $(document).ajaxComplete(function(event, xhr, settings) {
        // Если это AJAX запрос WooCommerce на добавление в корзину
        if (settings.url && (
            settings.url.indexOf('wc-ajax') !== -1 || 
            settings.url.indexOf('add_to_cart') !== -1 ||
            settings.url.indexOf('admin-ajax.php') !== -1
        )) {
            // После завершения AJAX запроса проверяем кнопки
            setTimeout(function() {
                $('.add_to_cart_button.loading').each(function() {
                    const $btn = $(this);
                    const loadingSince = $btn.data('loading-since') || Date.now();
                    const loadingTime = Date.now() - loadingSince;
                    
                    // Если запрос завершился, но кнопка все еще в loading больше 2 секунд - очищаем
                    if (loadingTime > 2000) {
                        console.log('🧹 Очистка loading после завершения AJAX запроса');
                        clearLoadingState($btn);
                        
                        const originalText = $btn.data('original-text') || 'В корзину';
                        if ($btn.text().trim() === '' || $btn.text().trim() === 'Добавление...') {
                            $btn.text(originalText).css({
                                'background-color': '',
                                'opacity': '1'
                            });
                        }
                    }
                });
            }, 500);
        }
    });
    
    // Обработка ошибок при добавлении
    $(document.body).on('wc_add_to_cart_error', function(e, $button, data) {
        const $btn = $($button);
        
        // Убираем состояние загрузки
        clearLoadingState($btn);
        
        // Показываем ошибку
        const originalText = $btn.text().trim() || 'В корзину';
        $btn.text('Ошибка').css({
            'background-color': '#dc3545',
            'opacity': '1'
        });
        
        setTimeout(function() {
            $btn.text(originalText).css({
                'background-color': '',
                'opacity': ''
            });
        }, 2000);
    });
    
    // Принудительная очистка всех залипших кнопок при загрузке страницы
    $(document).ready(function() {
        $('.add_to_cart_button.loading').each(function() {
            const $btn = $(this);
            console.warn('🧹 Найдена залипшая кнопка, очищаем состояние');
            clearLoadingState($btn);
        });
    });
    
    // Мониторинг залипших кнопок каждые 2 секунды (fallback) - более агрессивный
    setInterval(function() {
        $('.add_to_cart_button.loading').each(function() {
            const $btn = $(this);
            const loadingSince = $btn.data('loading-since') || Date.now();
            const loadingTime = Date.now() - loadingSince;
            
            // Если кнопка в состоянии loading больше 6 секунд - принудительно очищаем
            if (loadingTime > 6000) {
                console.warn('🧹 Принудительная очистка залипшей кнопки (была в loading ' + Math.round(loadingTime / 1000) + ' секунд)');
                clearLoadingState($btn);
                
                // Восстанавливаем текст кнопки
                const originalText = $btn.data('original-text') || $btn.text().trim() || 'В корзину';
                $btn.text(originalText).css({
                    'background-color': '',
                    'opacity': '1'
                });
            }
        });
    }, 2000); // Проверяем каждые 2 секунды
    
    // Отслеживаем момент добавления класса loading
    $(document).on('DOMNodeInserted DOMSubtreeModified', function() {
        // Используем MutationObserver для более эффективного отслеживания
    });
    
    // MutationObserver для отслеживания изменений класса loading
    if (typeof MutationObserver !== 'undefined') {
        const observer = new MutationObserver(function(mutations) {
            mutations.forEach(function(mutation) {
                if (mutation.type === 'attributes' && mutation.attributeName === 'class') {
                    const $btn = $(mutation.target);
                    if ($btn.hasClass('loading') && $btn.hasClass('add_to_cart_button')) {
                        if (!$btn.data('loading-since')) {
                            $btn.data('loading-since', Date.now());
                        }
                    }
                }
            });
        });
        
        // Наблюдаем за всеми кнопками добавления в корзину
        $(document).ready(function() {
            $('.add_to_cart_button').each(function() {
                observer.observe(this, {
                    attributes: true,
                    attributeFilter: ['class']
                });
            });
            
            // Также наблюдаем за динамически добавленными кнопками
            $(document).on('DOMNodeInserted', '.add_to_cart_button', function() {
                observer.observe(this, {
                    attributes: true,
                    attributeFilter: ['class']
                });
            });
        });
    }
    
        }); // Конец jQuery(document).ready для кнопок +/- и корзины
    }
    
    // Кликабельная сортировка - открываем select при клике на текст/стрелку
    document.addEventListener('DOMContentLoaded', function() {
        const shopSort = document.querySelector('.shop-sort');
        if (shopSort) {
            const select = shopSort.querySelector('select');
            const label = shopSort.querySelector('.shop-sort__label');
            
            if (select && label) {
                // Маппинг значений на тексты
                const sortLabels = {
                    'menu_order': 'Популярности',
                    'popularity': 'Популярности',
                    'rating': 'Рейтингу',
                    'date': 'Новизне',
                    'price': 'Цене: по возрастанию',
                    'price-desc': 'Цене: по убыванию'
                };
                
                // Функция обновления текста
                function updateSortLabel() {
                    const selectedValue = select.value;
                    const selectedOption = select.options[select.selectedIndex];
                    const optionText = selectedOption ? selectedOption.text.trim() : '';
                    
                    // Используем текст из option, если есть, иначе из маппинга
                    if (optionText) {
                        label.textContent = 'Сортировать по ' + optionText.toLowerCase();
                    } else if (sortLabels[selectedValue]) {
                        label.textContent = 'Сортировать по ' + sortLabels[selectedValue].toLowerCase();
                    }
                }
                
                // Обновляем при загрузке страницы
                updateSortLabel();
                
                // Обновляем при изменении select
                select.addEventListener('change', updateSortLabel);
                
                // При клике на весь блок сортировки открываем select
                shopSort.addEventListener('click', function(e) {
                    // Не открываем, если клик был непосредственно на select
                    if (e.target !== select) {
                        e.preventDefault();
                        select.focus();
                        select.click();
                    }
                });
            }
        }
    });

    // Запускаем инициализацию
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initJQueryCode);
    } else {
        initJQueryCode();
    }
})(); // Конец IIFE

// ===== КНОПКА ЧАТА НА СТРАНИЦЕ ТОВАРА =====
// Обработчик клика на кнопку чата в карточке товара
// Проверяем наличие jQuery перед использованием
if (typeof jQuery !== 'undefined') {
    jQuery(document).ready(function($) {
        $(document).on('click', '.product-chat-btn', function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            if (typeof openChatPopup === 'function') {
                openChatPopup();
            } else if (typeof window.openChatPopup === 'function') {
                window.openChatPopup();
            }
        });
    });
}

// Sticky header и кнопка "Наверх"
document.addEventListener('DOMContentLoaded', function() {
    const siteHeader = document.querySelector('.site-header');
    let scrollTimeout;
    
    // Добавляем класс scrolled при прокрутке
    function handleScroll() {
        if (window.scrollY > 50) {
            if (siteHeader) {
                siteHeader.classList.add('scrolled');
            }
        } else {
            if (siteHeader) {
                siteHeader.classList.remove('scrolled');
            }
        }
    }
    
    window.addEventListener('scroll', function() {
        clearTimeout(scrollTimeout);
        scrollTimeout = setTimeout(handleScroll, 10);
    });
    
    // Создаем кнопку "Наверх"
    const scrollToTopBtn = document.createElement('button');
    scrollToTopBtn.className = 'scroll-to-top';
    scrollToTopBtn.setAttribute('aria-label', 'Наверх');
    scrollToTopBtn.innerHTML = '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M12 19V5M12 5L5 12M12 5L19 12" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>';
    document.body.appendChild(scrollToTopBtn);
    
    // Показываем/скрываем кнопку при прокрутке
    function toggleScrollToTop() {
        if (window.scrollY > 300) {
            scrollToTopBtn.classList.add('visible');
        } else {
            scrollToTopBtn.classList.remove('visible');
        }
    }
    
    window.addEventListener('scroll', toggleScrollToTop);
    
    // Плавная прокрутка наверх
    scrollToTopBtn.addEventListener('click', function() {
        window.scrollTo({
            top: 0,
            behavior: 'smooth'
        });
    });
});