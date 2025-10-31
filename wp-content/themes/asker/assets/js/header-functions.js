/**
 * Функции для хедера: чат, счетчики корзины и избранного
 */

// Функция открытия попапа чата
function openChatPopup() {
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
    // Получаем количество товаров в корзине через AJAX
    fetch('/wp-admin/admin-ajax.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'action=get_cart_count'
    })
    .then(response => response.json())
    .then(data => {
        const cartCount = document.querySelector('.cart-count');
        if (cartCount) {
            cartCount.textContent = data.count;
            cartCount.setAttribute('data-count', data.count);
        }
    })
    .catch(error => {
        console.log('Ошибка получения количества товаров в корзине:', error);
    });
}

// Функция обновления счетчика избранного
function updateWishlistCount() {
    // Получаем количество товаров в избранном через AJAX
    fetch('/wp-admin/admin-ajax.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'action=get_wishlist_count'
    })
    .then(response => response.json())
    .then(data => {
        const wishlistCount = document.querySelector('.wishlist-count');
        if (wishlistCount) {
            wishlistCount.textContent = data.count;
            wishlistCount.setAttribute('data-count', data.count);
        }
    })
    .catch(error => {
        console.log('Ошибка получения количества товаров в избранном:', error);
    });
}

// Инициализация при загрузке страницы
document.addEventListener('DOMContentLoaded', function() {
    // Обновляем счетчики
    updateCartCount();
    updateWishlistCount();
    
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
