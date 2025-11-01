/**
 * Функции для хедера: чат, счетчики корзины и избранного
 */

// Функция открытия попапа чата
function openChatPopup() {
    // Проверяем, что DOM загружен
    if (typeof document === 'undefined' || !document.body) {
        console.error('DOM не загружен');
        return;
    }
    
    // Создаем попап
    const popup = document.createElement('div');
    popup.className = 'chat-popup';
    popup.innerHTML = `
        <div class="chat-popup-content">
            <div class="chat-popup-header">
                <h3>Связаться с нами</h3>
                <button class="chat-popup-close" onclick="closeChatPopup()">&times;</button>
            </div>
            <div class="chat-popup-body">
                <p>Выберите удобный способ связи:</p>
                <div class="chat-options">
                    <a href="https://t.me/askerspb" target="_blank" class="chat-option telegram">
                        <span class="chat-icon">📱</span>
                        <span>Telegram</span>
                    </a>
                    <a href="https://wa.me/78121234567" target="_blank" class="chat-option whatsapp">
                        <span class="chat-icon">💬</span>
                        <span>WhatsApp</span>
                    </a>
                    <a href="#" class="chat-option live-chat" onclick="startLiveChat(); return false;">
                        <span class="chat-icon">💬</span>
                        <span>Живой чат</span>
                    </a>
                </div>
            </div>
        </div>
    `;
    
    document.body.appendChild(popup);
    
    // Анимация появления
    setTimeout(() => {
        popup.classList.add('show');
    }, 10);
}

// Функция закрытия попапа чата
function closeChatPopup() {
    if (typeof document === 'undefined') {
        return;
    }
    const popup = document.querySelector('.chat-popup');
    if (popup) {
        popup.classList.remove('show');
        setTimeout(() => {
            popup.remove();
        }, 300);
    }
}

// Функция запуска живого чата
function startLiveChat() {
    // Здесь можно интегрировать с сервисом живого чата
    alert('Функция живого чата будет доступна в ближайшее время');
    closeChatPopup();
}

// Функция обновления счетчика корзины
function updateCartCount() {
    // Используем правильный AJAX URL и action
    const ajaxUrl = (typeof asker_ajax !== 'undefined' && asker_ajax.ajax_url) 
        ? asker_ajax.ajax_url 
        : (window.wc_add_to_cart_params && window.wc_add_to_cart_params.ajax_url)
        ? window.wc_add_to_cart_params.ajax_url
        : null;
    
    if (!ajaxUrl) {
        return; // Не делаем запрос, если URL недоступен
    }
    
    // Получаем количество товаров в корзине через AJAX
    fetch(ajaxUrl, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'action=asker_get_cart_count'
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('HTTP error! status: ' + response.status);
        }
        return response.json();
    })
    .then(data => {
        if (data && data.success && data.data) {
            const cartCount = document.querySelector('.cart-count');
            if (cartCount) {
                cartCount.textContent = data.data.count || 0;
                cartCount.setAttribute('data-count', data.data.count || 0);
            }
        }
    })
    .catch(error => {
        // Тихий catch - не логируем ошибки, чтобы не засорять консоль
    });
}

// Функция обновления счетчика избранного
function updateWishlistCount() {
    // Используем правильный AJAX URL и action
    const ajaxUrl = (typeof asker_ajax !== 'undefined' && asker_ajax.ajax_url) 
        ? asker_ajax.ajax_url 
        : (window.wc_add_to_cart_params && window.wc_add_to_cart_params.ajax_url)
        ? window.wc_add_to_cart_params.ajax_url
        : null;
    
    if (!ajaxUrl) {
        return; // Не делаем запрос, если URL недоступен
    }
    
    // Получаем количество товаров в избранном через AJAX
    fetch(ajaxUrl, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'action=get_wishlist_count'
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('HTTP error! status: ' + response.status);
        }
        return response.json();
    })
    .then(data => {
        if (data && data.success && data.count !== undefined) {
            const wishlistCount = document.querySelector('.wishlist-count');
            if (wishlistCount) {
                wishlistCount.textContent = data.count;
                wishlistCount.setAttribute('data-count', data.count);
            }
        }
    })
    .catch(error => {
        // Тихий catch - не логируем ошибки
    });
}

// Инициализация при загрузке страницы
document.addEventListener('DOMContentLoaded', function() {
    // ВРЕМЕННО ОТКЛЮЧЕНО для исправления белого экрана
    // Обновляем счетчики только если asker_ajax доступен (после полной загрузки скриптов)
    // Вызываем с небольшой задержкой, чтобы asker_ajax точно был загружен
    /*
    setTimeout(function() {
        if (typeof asker_ajax !== 'undefined') {
            updateCartCount();
            updateWishlistCount();
        }
    }, 100);
    */
    
    // Закрытие попапа по клику вне его
    document.addEventListener('click', function(e) {
        const popup = document.querySelector('.chat-popup');
        if (popup && e.target === popup) {
            closeChatPopup();
        }
    });
    
    // Закрытие попапа по Escape
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeChatPopup();
        }
    });
});

// Экспортируем функции для глобального использования
window.openChatPopup = openChatPopup;
window.closeChatPopup = closeChatPopup;
window.startLiveChat = startLiveChat;
window.updateCartCount = updateCartCount;
window.updateWishlistCount = updateWishlistCount;
