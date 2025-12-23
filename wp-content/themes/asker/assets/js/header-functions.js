/**
 * Функции для хедера: чат, счетчики корзины и избранного, форма обратной связи
 */

// Функция открытия попапа чата (устаревшая, оставлена для совместимости)
function openChatPopup() {
    // Перенаправляем на Telegram
    window.open('https://t.me/Ararat007_7', '_blank');
}

// Функция закрытия попапа чата (устаревшая, оставлена для совместимости)
function closeChatPopup() {
    const popup = document.querySelector('.chat-popup');
    if (popup) {
        popup.classList.remove('show');
        setTimeout(() => {
            popup.remove();
        }, 300);
    }
}

// Функция запуска живого чата (устаревшая, оставлена для совместимости)
function startLiveChat() {
    window.open('https://t.me/Ararat007_7', '_blank');
}

// Функция открытия попапа формы обратной связи (CF7)
function openContactFormPopup() {
    // Проверяем, есть ли уже попап - если да, показываем его
    var existingPopup = document.querySelector('.contact-form-popup');
    if (existingPopup) {
        existingPopup.classList.add('show');
        return;
    }
    
    // Получаем форму CF7 из скрытого контейнера
    var formTemplate = document.getElementById('popup-contact-form-template');
    var formContent = '';
    
    if (formTemplate && formTemplate.innerHTML.trim()) {
        formContent = formTemplate.innerHTML;
    } else {
        // Фолбэк - сообщение со ссылкой на Telegram
        formContent = '<p class="popup-form-notice">Форма не настроена.<br><br><a href="https://t.me/Ararat007_7" target="_blank" class="popup-tg-link">Написать в Telegram</a></p>';
    }
    
    // Создаем попап
    var popup = document.createElement('div');
    popup.className = 'contact-form-popup show'; // Сразу добавляем show
    popup.id = 'contact-form-popup';
    
    popup.innerHTML = 
        '<div class="contact-form-popup-content">' +
            '<div class="contact-form-popup-header">' +
                '<h3>Обратная связь</h3>' +
                '<button type="button" class="contact-form-popup-close" onclick="closeContactFormPopup()">&times;</button>' +
            '</div>' +
            '<div class="contact-form-popup-body">' +
                formContent +
            '</div>' +
        '</div>';
    
    document.body.appendChild(popup);
    
    // Инициализируем CF7 для новой формы
    if (typeof wpcf7 !== 'undefined' && wpcf7.init) {
        var cf7Form = popup.querySelector('.wpcf7');
        if (cf7Form) {
            wpcf7.init(cf7Form);
        }
    }
    
    // Фокус на первое поле
    var firstInput = popup.querySelector('input[type="text"], input[type="email"], input[type="tel"]');
    if (firstInput) {
        setTimeout(function() {
            firstInput.focus();
        }, 100);
    }
}

// Функция закрытия попапа формы обратной связи
function closeContactFormPopup() {
    const popup = document.querySelector('.contact-form-popup');
    if (popup) {
        popup.classList.remove('show');
        setTimeout(() => {
            popup.remove();
        }, 300);
    }
}

// СРАЗУ экспортируем функции для onclick в HTML
window.openContactFormPopup = openContactFormPopup;
window.closeContactFormPopup = closeContactFormPopup;
window.openChatPopup = openChatPopup;
window.closeChatPopup = closeChatPopup;
window.startLiveChat = startLiveChat;

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
            const count = data.data.count || 0;
            const cartCount = document.querySelector('.cart-count');
            if (cartCount) {
                cartCount.textContent = count;
                cartCount.setAttribute('data-count', count);
                // Скрываем счетчик если 0
                cartCount.style.display = count > 0 ? 'flex' : 'none';
            }
            // Обновляем мобильный счетчик
            const mobileCartCount = document.querySelector('.mobile-cart-count');
            if (mobileCartCount) {
                mobileCartCount.textContent = count;
                mobileCartCount.style.display = count > 0 ? 'inline-flex' : 'none';
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
        if (data && data.success && data.data && data.data.count !== undefined) {
            const wishlistCount = document.querySelector('.wishlist-count');
            if (wishlistCount) {
                wishlistCount.textContent = data.data.count;
                wishlistCount.setAttribute('data-count', data.data.count);
                // Скрываем счетчик если 0
                wishlistCount.style.display = data.data.count > 0 ? 'flex' : 'none';
            }
            // Обновляем мобильный счетчик
            const mobileWishlistCount = document.querySelector('.mobile-wishlist-count');
            if (mobileWishlistCount) {
                mobileWishlistCount.textContent = data.data.count;
                mobileWishlistCount.style.display = data.data.count > 0 ? 'inline-flex' : 'none';
            }
        }
    })
    .catch(error => {
        // Тихий catch - не логируем ошибки
    });
}

// Инициализация при загрузке страницы
document.addEventListener('DOMContentLoaded', function() {
    // Обновляем счетчики только если asker_ajax доступен (после полной загрузки скриптов)
    // Вызываем с небольшой задержкой, чтобы asker_ajax точно был загружен
    setTimeout(function() {
        if (typeof asker_ajax !== 'undefined') {
            updateCartCount();
            updateWishlistCount();
        } else {
            // Если asker_ajax еще не загружен, пробуем еще раз через 200мс
            setTimeout(function() {
                updateCartCount();
                updateWishlistCount();
            }, 200);
        }
    }, 100);
    
    // Закрытие попапа по клику вне его
    document.addEventListener('click', function(e) {
        // Закрытие попапа формы обратной связи
        const contactPopup = document.querySelector('.contact-form-popup');
        if (contactPopup && e.target === contactPopup) {
            closeContactFormPopup();
        }
        // Закрытие старого попапа чата (для совместимости)
        const chatPopup = document.querySelector('.chat-popup');
        if (chatPopup && e.target === chatPopup) {
            closeChatPopup();
        }
    });
    
    // Закрытие попапа по Escape
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeContactFormPopup();
            closeChatPopup();
        }
    });
});

// Экспортируем функции для глобального использования
window.openChatPopup = openChatPopup;
window.closeChatPopup = closeChatPopup;
window.startLiveChat = startLiveChat;
window.openContactFormPopup = openContactFormPopup;
window.closeContactFormPopup = closeContactFormPopup;
window.updateCartCount = updateCartCount;
window.updateWishlistCount = updateWishlistCount;
