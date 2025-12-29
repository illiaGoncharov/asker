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
        formContent = '<p class="popup-form-notice">Форма не настроена.<br><br><a href="https://t.me/Askercorp" target="_blank" class="popup-tg-link">Написать в Telegram</a></p>';
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
    var cf7Form = popup.querySelector('.wpcf7');
    if (cf7Form) {
        var form = cf7Form.querySelector('form');
        var cf7Initialized = false;
        
        // Попробуем инициализировать CF7 стандартным способом
        if (typeof wpcf7 !== 'undefined' && form) {
            // CF7 5.4+ использует wpcf7.init() но ожидает form, а не wrapper
            if (typeof wpcf7.init === 'function') {
                try {
                    // CF7 5.4+ ожидает form element
                    wpcf7.init(form);
                    cf7Initialized = true;
                } catch (e) {
                    // CF7 init не сработал, используем fallback
                }
            } else if (typeof wpcf7.initForm === 'function') {
                // Старые версии CF7
                try {
                    wpcf7.initForm(form);
                    cf7Initialized = true;
                } catch (e) {
                    // CF7 initForm не сработал
                }
            }
        }
        
        // Триггерим событие для CF7 (поможет некоторым версиям)
        var cf7Event = new CustomEvent('wpcf7:init', { detail: { form: cf7Form } });
        document.dispatchEvent(cf7Event);
        
        // Добавляем fallback обработчик ТОЛЬКО если CF7 не инициализировался
        if (form && !cf7Initialized && !form.hasAttribute('data-cf7-popup-handler')) {
            form.setAttribute('data-cf7-popup-handler', 'true');
            
            form.addEventListener('submit', function(e) {
                e.preventDefault();
                
                var formData = new FormData(form);
                var submitBtn = form.querySelector('input[type="submit"], button[type="submit"]');
                var responseOutput = cf7Form.querySelector('.wpcf7-response-output');
                
                // Добавляем action для CF7
                var formId = cf7Form.getAttribute('data-id');
                if (!formId) {
                    var wpcf7Input = cf7Form.querySelector('input[name="_wpcf7"]');
                    if (wpcf7Input) {
                        formId = wpcf7Input.value;
                    }
                }
                if (!formId) {
                    // Пробуем получить из атрибута id
                    var idMatch = cf7Form.id && cf7Form.id.match(/wpcf7-f(\d+)/);
                    if (idMatch) {
                        formId = idMatch[1];
                    }
                }
                
                
                // Показываем состояние загрузки
                if (submitBtn) {
                    submitBtn.disabled = true;
                    submitBtn.value = submitBtn.value || 'Отправка...';
                }
                if (responseOutput) {
                    responseOutput.textContent = '';
                    responseOutput.classList.remove('wpcf7-mail-sent-ok', 'wpcf7-validation-errors');
                }
                
                // URL для отправки CF7
                var ajaxUrl = (typeof wpcf7 !== 'undefined' && wpcf7.api && wpcf7.api.root)
                    ? wpcf7.api.root + 'contact-form-7/v1/contact-forms/' + formId + '/feedback'
                    : '/wp-json/contact-form-7/v1/contact-forms/' + formId + '/feedback';
                
                fetch(ajaxUrl, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'Accept': 'application/json'
                    }
                })
                .then(function(response) {
                    return response.json();
                })
                .then(function(data) {
                    if (submitBtn) {
                        submitBtn.disabled = false;
                    }
                    
                    if (data.status === 'mail_sent') {
                        // Успех
                        if (responseOutput) {
                            responseOutput.textContent = data.message || 'Сообщение отправлено!';
                            responseOutput.classList.add('wpcf7-mail-sent-ok');
                            responseOutput.classList.remove('wpcf7-validation-errors');
                        }
                        // Очищаем форму
                        form.reset();
                        // Закрываем попап через 2 секунды
                        setTimeout(function() {
                            closeContactFormPopup();
                        }, 2000);
                    } else {
                        // Ошибка валидации или отправки
                        if (responseOutput) {
                            responseOutput.textContent = data.message || 'Ошибка отправки';
                            responseOutput.classList.add('wpcf7-validation-errors');
                            responseOutput.classList.remove('wpcf7-mail-sent-ok');
                        }
                        
                        // Показываем ошибки полей
                        if (data.invalid_fields && data.invalid_fields.length > 0) {
                            data.invalid_fields.forEach(function(field) {
                                var input = form.querySelector('[name="' + field.field + '"]');
                                if (input) {
                                    input.classList.add('wpcf7-not-valid');
                                    var tip = input.parentNode.querySelector('.wpcf7-not-valid-tip');
                                    if (!tip) {
                                        tip = document.createElement('span');
                                        tip.className = 'wpcf7-not-valid-tip';
                                        input.parentNode.appendChild(tip);
                                    }
                                    tip.textContent = field.message;
                                }
                            });
                        }
                    }
                })
                .catch(function(error) {
                    if (submitBtn) {
                        submitBtn.disabled = false;
                    }
                    if (responseOutput) {
                        responseOutput.textContent = 'Ошибка сети. Попробуйте позже.';
                        responseOutput.classList.add('wpcf7-validation-errors');
                    }
                });
            });
        }
    }
    
    // Активируем кнопку отправки (CF7 может её заблокировать)
    var submitBtn = popup.querySelector('.wpcf7-submit, input[type="submit"], button[type="submit"]');
    if (submitBtn) {
        submitBtn.removeAttribute('disabled');
        submitBtn.removeAttribute('aria-disabled');
        submitBtn.classList.remove('disabled');
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
