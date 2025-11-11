/**
 * Клиентская валидация форм
 * Улучшенная валидация для всех форм на сайте
 */

(function() {
    'use strict';

    /**
     * Утилиты для валидации
     */
    const ValidationUtils = {
        /**
         * Валидация телефона
         */
        validatePhone: function(phone) {
            if (!phone) return false;
            // Удаляем все символы кроме цифр
            const phoneDigits = phone.replace(/[^\d]/g, '');
            // Проверяем минимальную длину (10 цифр)
            return phoneDigits.length >= 10;
        },

        /**
         * Валидация email
         */
        validateEmail: function(email) {
            if (!email) return false;
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            return emailRegex.test(email);
        },

        /**
         * Валидация ИНН
         */
        validateINN: function(inn) {
            if (!inn) return true; // Необязательное поле
            const innDigits = inn.replace(/[^\d]/g, '');
            return innDigits.length === 10 || innDigits.length === 12;
        },

        /**
         * Форматирование телефона при вводе
         */
        formatPhone: function(input) {
            let value = input.value.replace(/[^\d]/g, '');
            
            if (value.length > 0) {
                if (value[0] === '8') {
                    value = '7' + value.substring(1);
                }
                
                let formatted = '';
                if (value.length > 0) {
                    formatted = '+7';
                }
                if (value.length > 1) {
                    formatted += ' (' + value.substring(1, 4);
                }
                if (value.length >= 4) {
                    formatted += ') ' + value.substring(4, 7);
                }
                if (value.length >= 7) {
                    formatted += '-' + value.substring(7, 9);
                }
                if (value.length >= 9) {
                    formatted += '-' + value.substring(9, 11);
                }
                
                input.value = formatted;
            }
        },

        /**
         * Показать сообщение об ошибке
         */
        showError: function(field, message) {
            // Удаляем предыдущее сообщение
            this.removeError(field);
            
            // Создаем элемент с ошибкой
            const errorElement = document.createElement('span');
            errorElement.className = 'field-error';
            errorElement.textContent = message;
            
            // Добавляем класс ошибки к полю
            field.classList.add('has-error');
            
            // Вставляем сообщение после поля
            field.parentNode.insertBefore(errorElement, field.nextSibling);
        },

        /**
         * Удалить сообщение об ошибке
         */
        removeError: function(field) {
            field.classList.remove('has-error');
            const errorElement = field.parentNode.querySelector('.field-error');
            if (errorElement) {
                errorElement.remove();
            }
        },

        /**
         * Валидация поля в реальном времени
         */
        validateField: function(field) {
            const fieldType = field.type || '';
            const fieldName = field.name || '';
            const fieldValue = field.value.trim();
            
            // Проверка обязательных полей
            if (field.hasAttribute('required') && !fieldValue) {
                this.showError(field, 'Это поле обязательно для заполнения.');
                return false;
            }
            
            // Валидация по типу поля
            if (fieldType === 'email' || fieldName.includes('email')) {
                if (fieldValue && !this.validateEmail(fieldValue)) {
                    this.showError(field, 'Пожалуйста, введите корректный email адрес.');
                    return false;
                }
            }
            
            if (fieldType === 'tel' || fieldName.includes('phone')) {
                if (fieldValue && !this.validatePhone(fieldValue)) {
                    this.showError(field, 'Номер телефона должен содержать минимум 10 цифр.');
                    return false;
                }
            }
            
            if (fieldName.includes('tax_id') || fieldName.includes('inn')) {
                if (fieldValue && !this.validateINN(fieldValue)) {
                    this.showError(field, 'ИНН должен содержать 10 или 12 цифр.');
                    return false;
                }
            }
            
            // Если все проверки пройдены
            this.removeError(field);
            return true;
        }
    };

    /**
     * Валидация формы WooCommerce Checkout
     */
    function initCheckoutValidation() {
        const checkoutForm = document.querySelector('form.checkout, form.woocommerce-checkout');
        if (!checkoutForm) return;

        // Валидация при потере фокуса
        const fields = checkoutForm.querySelectorAll('input[required], textarea[required], select[required]');
        fields.forEach(function(field) {
            field.addEventListener('blur', function() {
                ValidationUtils.validateField(field);
            });

            // Форматирование телефона при вводе
            if (field.type === 'tel' || field.name.includes('phone')) {
                field.addEventListener('input', function() {
                    ValidationUtils.formatPhone(field);
                });
            }
        });

        // Валидация перед отправкой
        checkoutForm.addEventListener('submit', function(e) {
            let isValid = true;
            
            fields.forEach(function(field) {
                if (!ValidationUtils.validateField(field)) {
                    isValid = false;
                }
            });

            // Дополнительная валидация для доставки
            const deliveryType = checkoutForm.querySelector('input[name="delivery_type"]:checked');
            if (deliveryType && deliveryType.value === 'delivery') {
                const shippingCity = checkoutForm.querySelector('input[name="shipping_city"]');
                const shippingAddress = checkoutForm.querySelector('input[name="shipping_address_1"]');
                
                if (shippingCity && !shippingCity.value.trim()) {
                    ValidationUtils.showError(shippingCity, 'Пожалуйста, укажите город доставки.');
                    isValid = false;
                }
                
                if (shippingAddress && !shippingAddress.value.trim()) {
                    ValidationUtils.showError(shippingAddress, 'Пожалуйста, укажите улицу доставки.');
                    isValid = false;
                }
            }

            if (!isValid) {
                e.preventDefault();
                e.stopPropagation();
                
                // Прокрутка к первой ошибке
                const firstError = checkoutForm.querySelector('.has-error');
                if (firstError) {
                    firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    firstError.focus();
                }
                
                return false;
            }
        });
    }

    /**
     * Валидация Contact Form 7
     */
    function initCF7Validation() {
        const cf7Forms = document.querySelectorAll('.wpcf7-form');
        if (!cf7Forms.length) return;

        cf7Forms.forEach(function(form) {
            const fields = form.querySelectorAll('input[type="email"], input[type="tel"], input[name*="phone"], input[name*="email"]');
            
            fields.forEach(function(field) {
                field.addEventListener('blur', function() {
                    ValidationUtils.validateField(field);
                });

                // Форматирование телефона
                if (field.type === 'tel' || field.name.includes('phone')) {
                    field.addEventListener('input', function() {
                        ValidationUtils.formatPhone(field);
                    });
                }
            });
        });
    }

    /**
     * Валидация кастомных форм (wholesale-form, footer__form)
     */
    function initCustomFormsValidation() {
        const customForms = document.querySelectorAll('.wholesale-form form, .footer__form form');
        
        customForms.forEach(function(form) {
            const fields = form.querySelectorAll('input[required], textarea[required]');
            
            fields.forEach(function(field) {
                field.addEventListener('blur', function() {
                    ValidationUtils.validateField(field);
                });

                if (field.type === 'tel' || field.name.includes('phone')) {
                    field.addEventListener('input', function() {
                        ValidationUtils.formatPhone(field);
                    });
                }
            });

            form.addEventListener('submit', function(e) {
                let isValid = true;
                
                fields.forEach(function(field) {
                    if (!ValidationUtils.validateField(field)) {
                        isValid = false;
                    }
                });

                if (!isValid) {
                    e.preventDefault();
                    e.stopPropagation();
                    
                    const firstError = form.querySelector('.has-error');
                    if (firstError) {
                        firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
                        firstError.focus();
                    }
                    
                    return false;
                }
            });
        });
    }

    /**
     * Инициализация при загрузке DOM
     */
    function init() {
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', function() {
                initCheckoutValidation();
                initCF7Validation();
                initCustomFormsValidation();
            });
        } else {
            initCheckoutValidation();
            initCF7Validation();
            initCustomFormsValidation();
        }
    }

    // Запуск
    init();

})();

