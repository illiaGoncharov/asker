// Общие скрипты темы. Стараться держать без зависимостей.


// Бургер-меню навигации
document.addEventListener('DOMContentLoaded', function() {
    const navMenuToggle = document.getElementById('nav-menu-toggle');
    const navDropdownMenu = document.getElementById('nav-dropdown-menu');
    
    if (navMenuToggle && navDropdownMenu) {
        navMenuToggle.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            navDropdownMenu.classList.toggle('active');
        });
        
        // Закрываем меню при клике вне его
        document.addEventListener('click', function(e) {
            if (!navMenuToggle.contains(e.target) && !navDropdownMenu.contains(e.target)) {
                navDropdownMenu.classList.remove('active');
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
    
    // Обновляем счетчики при загрузке и при изменениях
    updateMobileMenuCounters();
    setInterval(updateMobileMenuCounters, 1000);
});

// Функционал лайков для товаров
document.addEventListener('DOMContentLoaded', function() {
    
    // Проверяем наличие кнопок лайков
    const favoriteButtons = document.querySelectorAll('.favorite-btn');
    
    // Проверяем наличие счетчика в хедере
    const wishlistCounter = document.querySelector('.wishlist-count');
        
        // Используем делегирование событий для динамически добавляемых элементов
        document.addEventListener('click', function(e) {
            if (e.target.classList.contains('favorite-btn') || e.target.closest('.favorite-btn') || 
                e.target.classList.contains('favorite-btn-single') || e.target.closest('.favorite-btn-single')) {
                e.preventDefault();
                e.stopPropagation();
                
                const button = e.target.classList.contains('favorite-btn') || e.target.classList.contains('favorite-btn-single') ? 
                    e.target : e.target.closest('.favorite-btn, .favorite-btn-single');
                const productId = button.getAttribute('data-product-id');
                
                console.log('Favorite button clicked, product ID:', productId);
                
                if (!productId) {
                    console.log('No product ID found');
                    return;
                }
                
                // Получаем текущее состояние из localStorage
                const favorites = JSON.parse(localStorage.getItem('favorites') || '[]');
                const isCurrentlyFavorite = favorites.includes(productId);
                
                if (isCurrentlyFavorite) {
                    // Удаляем из избранного
                    const index = favorites.indexOf(productId);
                    favorites.splice(index, 1);
                    button.classList.remove('active');
                    console.log('Removed from favorites:', productId);
                } else {
                    // Добавляем в избранное
                    favorites.push(productId);
                    button.classList.add('active');
                    console.log('Added to favorites:', productId);
                }
                
                localStorage.setItem('favorites', JSON.stringify(favorites));
                console.log('Current favorites:', favorites);
                
                // Обновляем счетчик в хедере
                updateWishlistCounter();
            }
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
                console.log('Restored favorite state for product:', productId);
            });
        });
        
        // Обновляем счетчик при загрузке
        updateWishlistCounter();
        updateCartCounter();
        
        // Принудительная синхронизация с сервером при загрузке
        fetchCartCountFromServer();
        
        // Дополнительная проверка через небольшую задержку
        setTimeout(() => {
            updateWishlistCounter();
            updateCartCounter();
            fetchCartCountFromServer();
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
        
        // Функционал кнопок "В корзину"
        const addToCartButtons = document.querySelectorAll('.btn-add-cart');
        addToCartButtons.forEach(button => {
            button.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                
                const productId = this.getAttribute('data-product-id');
                if (!productId) return;
                
                // Добавляем товар в корзину (localStorage)
                const cart = JSON.parse(localStorage.getItem('cart') || '[]');
                const existingItem = cart.find(item => item.id === productId);
                
                if (existingItem) {
                    existingItem.quantity += 1;
                } else {
                    cart.push({ id: productId, quantity: 1 });
                }
                
                localStorage.setItem('cart', JSON.stringify(cart));
                updateCartCounter();
                
                // Показываем уведомление
                showNotification('Товар добавлен в корзину!');
            });
        });
    });
    
    // Функция для обновления счетчика избранного (глобальная)
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
            } else {
                console.warn('⚠️ Desktop wishlist counter not found');
            }
            
            // Обновляем мобильный счетчик
            const mobileWishlistCount = document.querySelector('.mobile-wishlist-count');
            if (mobileWishlistCount) {
                mobileWishlistCount.textContent = count;
                mobileWishlistCount.style.display = count > 0 ? 'inline-flex' : 'none';
            } else {
                console.warn('⚠️ Mobile wishlist counter not found');
            }
            
            // Дополнительная проверка - если счетчики не найдены, попробуем через небольшую задержку
            if (!counter && !mobileWishlistCount) {
                setTimeout(() => {
                    window.updateWishlistCounter();
                }, 100);
            }
            
        } catch (error) {
            console.error('❌ Error updating wishlist counter:', error);
        }
    }
    
    // Функция для обновления счетчика корзины (глобальная)
    window.updateCartCounter = function() {
        // Сразу запрашиваем данные с сервера - это единственный источник истины для WooCommerce корзины
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
                    
                    if (json.data.removed_invalid > 0) {
                        console.log('🧹 Removed invalid items:', json.data.removed_invalid);
                    }
                    
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

    // Дергаем сервер после ключевых событий Woo
    if (window.jQuery) {
        const $ = window.jQuery;
        $(document.body).on('added_to_cart updated_wc_div wc_fragments_refreshed removed_from_cart', function(e) {
            console.log('WooCommerce event triggered:', e.type);
            // Небольшая задержка, чтобы дать серверу время обработать запрос
            setTimeout(() => {
                fetchCartCountFromServer();
            }, 100);
        });
    }
    
    // WooCommerce сам управляет корзиной через свои AJAX-запросы
    // Мы только слушаем события и обновляем счетчик
    
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
        console.log('🧹 Clearing cart on server...');
        
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

    // Фильтры каталога - переход по категориям
    document.querySelectorAll('.filter-checkbox input[type="checkbox"]').forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            const url = this.getAttribute('data-url');
            if (url && this.checked) {
                // Снимаем отметку с других чекбоксов
                document.querySelectorAll('.filter-checkbox input[type="checkbox"]').forEach(cb => {
                    if (cb !== this) {
                        cb.checked = false;
                    }
                });
                // Переходим на страницу категории
                window.location.href = url;
            } else if (!this.checked) {
                // Если снята галочка - возвращаемся в общий каталог
                window.location.href = document.querySelector('.filter-reset-btn').href;
            }
        });
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
    
    // Кнопки +/- для количества товара
    document.addEventListener('click', function(e) {
        // Кнопка "плюс"
        if (e.target.classList.contains('plus') || e.target.closest('.qty-btn.plus')) {
            e.preventDefault();
            const button = e.target.classList.contains('plus') ? e.target : e.target.closest('.qty-btn.plus');
            const qtyInput = button.parentElement.querySelector('input.qty');
            
            if (qtyInput) {
                const currentVal = parseInt(qtyInput.value) || 0;
                const maxVal = parseInt(qtyInput.getAttribute('max')) || 999;
                const step = parseInt(qtyInput.getAttribute('step')) || 1;
                
                if (currentVal < maxVal) {
                    qtyInput.value = currentVal + step;
                    qtyInput.dispatchEvent(new Event('change', { bubbles: true }));
                }
            }
        }
        
        // Кнопка "минус"
        if (e.target.classList.contains('minus') || e.target.closest('.qty-btn.minus')) {
            e.preventDefault();
            const button = e.target.classList.contains('minus') ? e.target : e.target.closest('.qty-btn.minus');
            const qtyInput = button.parentElement.querySelector('input.qty');
            
            if (qtyInput) {
                const currentVal = parseInt(qtyInput.value) || 0;
                const minVal = parseInt(qtyInput.getAttribute('min')) || 1;
                const step = parseInt(qtyInput.getAttribute('step')) || 1;
                
                if (currentVal > minVal) {
                    qtyInput.value = currentVal - step;
                    qtyInput.dispatchEvent(new Event('change', { bubbles: true }));
                }
            }
        }
    });
    
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
    console.log('🔍 ЛК: проверка элементов...');
    const accountNav = document.querySelector('.account-nav');
    
    if (!accountNav) {
        console.warn('❌ ЛК: .account-nav не найден. Находимся на странице ЛК?');
        return false;
    }
    
    const navItemsCount = accountNav.querySelectorAll('.nav-item').length;
    const tabContents = document.querySelectorAll('.tab-content');
    
    console.log('✅ ЛК: элементы найдены');
    console.log('   Найдено вкладок навигации:', navItemsCount);
    console.log('   Найдено контентных блоков:', tabContents.length);
    
    // Используем делегирование событий для надёжности
    accountNav.addEventListener('click', function(e) {
        const navItem = e.target.closest('.nav-item');
        if (!navItem) {
            console.log('❌ ЛК: клик не по .nav-item');
            return;
        }
        
        e.preventDefault();
        
        const targetTab = navItem.getAttribute('data-tab');
        if (!targetTab) {
            console.error('❌ ЛК: не найден атрибут data-tab у вкладки');
            return;
        }
        
        console.log('🖱️ ЛК: клик по вкладке:', targetTab);
        
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
            console.log('✅ ЛК: показана вкладка:', targetTab);
        } else {
            console.error('❌ ЛК: не найден контент для вкладки:', targetTab);
        }
    });
    
    console.log('✅ ЛК: обработчик событий установлен');
    return true;
}

// Инициализация при загрузке DOM
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initAccountTabs);
} else {
    // DOM уже загружен
    initAccountTabs();
}