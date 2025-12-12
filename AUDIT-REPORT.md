# Технический аудит WordPress темы Asker

## Executive Summary

**Общая оценка:** 🟡 Средняя (требуются улучшения)

Проект Asker представляет собой профессионально разработанную WordPress/WooCommerce тему для интернет-магазина запчастей. Архитектура в целом соответствует лучшим практикам WordPress с хорошей модульностью и разделением ответственности. Однако выявлены критические проблемы с безопасностью, производительностью JavaScript и необходимость рефакторинга больших файлов.

### Ключевые приоритеты:
1. 🔴 **Критично:** SQL-инъекция в модуле поиска (helpers.php)
2. 🔴 **Критично:** Отсутствие nonce-проверок в AJAX обработчиках
3. 🟠 **Важно:** Модуляризация main.js (2000+ строк)
4. 🟠 **Важно:** Рефакторинг woocommerce.php (3440 строк)
5. 🟡 **Желательно:** Оптимизация CSS (195KB main.css)

---

## 1. Архитектура PHP

### 1.1. Сильные стороны ✅

**Модульность:**
- Функционал разделен на 17 файлов в `inc/`
- Четкое разделение ответственности между модулями
- Чистая точка входа `functions.php` (91 строка)

**WordPress интеграция:**
- Корректное использование хуков и фильтров
- Правильное подключение стилей и скриптов через `wp_enqueue_scripts`
- Локализация AJAX URL через `wp_localize_script`

**WooCommerce интеграция:**
- Использование стандартных WooCommerce хуков
- Корректные оверрайды шаблонов в `woocommerce/`
- Правильная работа с сессиями корзины

**Кеширование:**
```php
// helpers.php - хороший пример использования transient кэша
$cache_key = 'asker_home_categories_v2';
$product_categories = get_transient($cache_key);
if ($product_categories === false) {
    // ... запрос к БД ...
    set_transient($cache_key, $product_categories, HOUR_IN_SECONDS);
}
```

**SEO и Schema.org:**
- Полноценная реализация JSON-LD разметки
- Open Graph теги для соцсетей
- Динамические meta description

### 1.2. Проблемы и рекомендации

#### 🔴 КРИТИЧНО: SQL-инъекция в поисковом модуле

**Файл:** `inc/helpers.php`, строки 60-76

```php
// ПРОБЛЕМА: Прямая интерполяция переменной в SQL без prepare()
foreach ($search_terms as $term) {
    if (!empty($term)) {
        $search .= " AND (
            ({$wpdb->posts}.post_title LIKE '%{$term}%') OR
            ({$wpdb->posts}.post_content LIKE '%{$term}%') OR
            ...
        )";
    }
}
```

**Рекомендация:**
```php
// ИСПРАВЛЕНИЕ: Использовать $wpdb->prepare()
foreach ($search_terms as $term) {
    if (!empty($term)) {
        $like_term = '%' . $wpdb->esc_like($term) . '%';
        $search .= $wpdb->prepare(
            " AND (
                ({$wpdb->posts}.post_title LIKE %s) OR
                ({$wpdb->posts}.post_content LIKE %s) OR
                ({$wpdb->posts}.post_excerpt LIKE %s) OR
                EXISTS (
                    SELECT * FROM {$wpdb->postmeta} 
                    WHERE {$wpdb->postmeta}.post_id = {$wpdb->posts}.ID 
                    AND {$wpdb->postmeta}.meta_key = '_sku' 
                    AND {$wpdb->postmeta}.meta_value LIKE %s
                )
            )",
            $like_term, $like_term, $like_term, $like_term
        );
    }
}
```

#### 🔴 КРИТИЧНО: Отсутствие nonce-проверок в AJAX

**Файлы:** `inc/woocommerce.php`, `inc/helpers.php`

Обработчики AJAX не проверяют nonce:
- `asker_ajax_get_cart_count` (строка 671)
- `asker_ajax_clear_cart` (строка 719)
- `asker_ajax_update_cart_item` (строка 754)
- `asker_ajax_remove_cart_item` (строка 775)
- `asker_sync_wishlist` (строка 987)
- `asker_toggle_wishlist` (строка 1006)
- `asker_get_wishlist_products` (строка 1070)

**Рекомендация:**
```php
function asker_ajax_get_cart_count() {
    // Добавить в начало каждого обработчика:
    check_ajax_referer('asker_ajax_nonce', 'nonce');
    // или
    if (!wp_verify_nonce($_POST['nonce'] ?? '', 'asker_ajax_nonce')) {
        wp_send_json_error(['message' => 'Ошибка безопасности']);
        return;
    }
    // ... остальной код
}
```

#### 🟠 ВАЖНО: Огромный файл woocommerce.php

**Файл:** `inc/woocommerce.php` — 3440 строк

**Проблема:** Файл выполняет слишком много функций:
- Управление Coming Soon режимом
- AJAX обработчики корзины
- AJAX обработчики избранного
- Кастомизация шаблонов
- Переводы и локализация
- Управление сессиями

**Рекомендация:** Разделить на модули:
```
inc/
├── woocommerce/
│   ├── cart-ajax.php          # AJAX корзины
│   ├── wishlist-ajax.php      # AJAX избранного  
│   ├── template-overrides.php # Кастомизация шаблонов
│   ├── session-handler.php    # Управление сессиями
│   └── localization.php       # Переводы
└── woocommerce.php            # Точка входа (подключает модули)
```

#### 🟠 ВАЖНО: Вывод IP без экранирования

**Файл:** `inc/helpers.php`, строка 258

```php
// ПРОБЛЕМА:
$email_body .= "IP адрес: " . $_SERVER['REMOTE_ADDR'] . "\n";

// ИСПРАВЛЕНИЕ:
$email_body .= "IP адрес: " . sanitize_text_field($_SERVER['REMOTE_ADDR'] ?? '') . "\n";
```

#### 🟡 ЖЕЛАТЕЛЬНО: Отсутствие валидации индивидуальной скидки

**Файл:** `inc/customer-levels.php`, строка 316

```php
// ПРОБЛЕМА: Нет проверки nonce
if ( isset( $_POST['individual_discount'] ) ) {
    $discount = intval( $_POST['individual_discount'] );
    ...
}

// ИСПРАВЛЕНИЕ: Добавить nonce проверку
if (!isset($_POST['_wpnonce']) || !wp_verify_nonce($_POST['_wpnonce'], 'update-user_' . $user_id)) {
    return;
}
```

#### 🟡 ЖЕЛАТЕЛЬНО: Дублирование Coming Soon фильтров

**Файл:** `inc/woocommerce.php`

Фильтры для отключения Coming Soon режима применяются многократно:
- `asker_disable_coming_soon_mode()` — строка 197
- `asker_force_store_available()` — строка 236
- `asker_disable_coming_soon_early()` — строка 256
- `asker_disable_block_theme_for_home()` — строка 285

**Рекомендация:** Консолидировать в одну функцию с ранним приоритетом.

### 1.3. Опасные зоны

| Файл:строка | Риск | Описание |
|-------------|------|----------|
| `helpers.php:60-76` | 🔴 Критический | SQL-инъекция через поиск |
| `woocommerce.php:671-798` | 🔴 Критический | AJAX без nonce |
| `helpers.php:258` | 🟠 Средний | XSS через IP |
| `setup.php:50-52` | 🟠 Средний | SVG загрузка без валидации |
| `customer-levels.php:316` | 🟡 Низкий | Нет nonce проверки |

### 1.4. Лучшие практики

**Рекомендуется внедрить:**

1. **Централизованная обработка AJAX:**
```php
// inc/ajax-handler.php
class Asker_Ajax_Handler {
    public function __construct() {
        // Все AJAX регистрации в одном месте
    }
    
    protected function verify_request() {
        if (!wp_verify_nonce($_POST['nonce'] ?? '', 'asker_ajax_nonce')) {
            wp_send_json_error(['message' => 'Ошибка безопасности']);
            exit;
        }
    }
}
```

2. **Валидация SVG при загрузке:**
```php
// setup.php
add_filter('wp_check_filetype_and_ext', function($data, $file, $filename, $mimes) {
    if (pathinfo($filename, PATHINFO_EXTENSION) === 'svg') {
        // Проверить содержимое SVG на вредоносный код
        $content = file_get_contents($file);
        if (preg_match('/<script|onclick|onerror|onload/i', $content)) {
            return ['ext' => false, 'type' => false, 'proper_filename' => false];
        }
    }
    return $data;
}, 10, 4);
```

---

## 2. Верстка (CSS)

### 2.1. Сильные стороны ✅

**Дизайн-токены:**
```css
/* base.css - хорошая система CSS переменных */
:root {
  --primary-yellow: #FFE600;
  --primary-black: #1a1a1a;
  --space-1: 4px;
  --space-2: 8px;
  /* ... */
}
```

**БЭМ методология:**
```css
/* Правильное использование БЭМ */
.btn { }
.btn--primary { }
.btn--secondary { }
.shop-product-card { }
.shop-product-card__image { }
```

**Адаптивность:**
- Mobile-first подход в некоторых компонентах
- Breakpoints 768px и 1024px
- Размеры тач-таргетов ≥44px для кнопок

### 2.2. Проблемы и рекомендации

#### 🔴 КРИТИЧНО: Использование !important

**Нарушение правил проекта** — запрет на `!important`

**Файл:** `assets/css/main.css`, строки 127-142

```css
/* ПРОБЛЕМА: */
.site-header .container,
.header-main .container {
  max-width: 1440px !important;  /* Нарушение правила */
  margin: 0 auto;
  padding: 0 20px;
  width: 100%;
}
```

**Рекомендация:** Увеличить специфичность селектора вместо !important:
```css
body .site-header .container,
body .header-main .container {
  max-width: 1440px;
}
```

**Также найдено в:**
- `assets/css/header.css`
- `assets/css/pages.css`

#### 🟠 ВАЖНО: Огромный размер main.css

**Файл:** `assets/css/main.css` — 195KB, 9738 строк

**Проблемы:**
1. Дублирование CSS переменных (в base.css и main.css)
2. Дублирование @import (шрифты Google Fonts загружаются 3 раза)
3. Мертвый код (неиспользуемые классы)

**Рекомендация:** Разделить на модули:
```
assets/css/
├── base.css           # Токены, сброс, типографика
├── components/
│   ├── buttons.css
│   ├── cards.css
│   ├── forms.css
│   └── modals.css
├── layout/
│   ├── header.css
│   ├── footer.css
│   └── grid.css
├── pages/
│   ├── home.css
│   ├── catalog.css
│   └── checkout.css
└── main.css           # Точка входа (только импорты)
```

#### 🟠 ВАЖНО: @import внутри CSS блокирует загрузку

**Файл:** `assets/css/main.css`, строки 4-7

```css
/* ПРОБЛЕМА: @import блокирует параллельную загрузку */
@import url('base.css');
@import url('header.css');
@import url('pages.css');
```

**Рекомендация:** Объединить файлы на этапе сборки или подключить отдельно в PHP:
```php
// enqueue.php
wp_enqueue_style('asker-base', get_template_directory_uri() . '/assets/css/base.css');
wp_enqueue_style('asker-header', get_template_directory_uri() . '/assets/css/header.css', ['asker-base']);
wp_enqueue_style('asker-pages', get_template_directory_uri() . '/assets/css/pages.css', ['asker-base']);
wp_enqueue_style('asker-main', get_template_directory_uri() . '/assets/css/main.css', ['asker-base', 'asker-header', 'asker-pages']);
```

#### 🟡 ЖЕЛАТЕЛЬНО: Дублирование CSS переменных

**Файлы:** `base.css` и `main.css`

CSS переменные объявлены дважды:
```css
/* base.css:6 */
:root {
  --primary-yellow: #FFE600;
  ...
}

/* main.css:73 */
:root {
  --primary-yellow: #FFE600;
  ...
}
```

**Рекомендация:** Оставить только в `base.css`, удалить из `main.css`.

#### 🟡 ЖЕЛАТЕЛЬНО: Тройная загрузка Google Fonts

**Файлы:**
1. `header.php:14` — через `<link>`
2. `base.css:48` — через `@import`
3. `main.css:90` — через `@import`

**Рекомендация:** Оставить только один способ (предпочтительно `<link>` с `preconnect`).

### 2.3. Опасные зоны

| Файл:строка | Риск | Описание |
|-------------|------|----------|
| `main.css:127-142` | 🟠 Средний | !important нарушает правила проекта |
| `main.css:4-7` | 🟠 Средний | @import блокирует загрузку |
| `main.css` | 🟡 Низкий | 195KB — требует оптимизации |

### 2.4. Лучшие практики

**CSS-in-PHP для критических стилей:**
```php
// header.php
<style id="critical-css">
<?php include get_template_directory() . '/assets/css/critical.css'; ?>
</style>
```

**Lazy-loading некритических стилей:**
```php
// enqueue.php
wp_enqueue_style('asker-pages', '...', [], null, 'print');
wp_style_add_data('asker-pages', 'onload', "this.media='all'");
```

---

## 3. JavaScript

### 3.1. Сильные стороны ✅

**Делегирование событий:**
```javascript
// main.js - правильное использование делегирования
document.addEventListener('click', function(e) {
    const button = e.target.closest('.btn-add-cart, .add_to_cart_button');
    if (!button) return;
    // ...
});
```

**Защита от двойных кликов:**
```javascript
if (button.hasAttribute('data-processing')) {
    return;
}
button.setAttribute('data-processing', 'true');
```

**Capture phase для раннего перехвата:**
```javascript
document.addEventListener('click', handleQuantityClick, true);
```

### 3.2. Проблемы и рекомендации

#### 🔴 КРИТИЧНО: Огромный файл main.js

**Файл:** `assets/js/main.js` — 2004 строки, 94KB

**Проблемы:**
1. Все функции в одном файле
2. Множественные DOMContentLoaded обработчики
3. Глобальные функции в window

**Рекомендация по модуляризации:**

```javascript
// assets/js/modules/wishlist.js
export const Wishlist = {
    init() { },
    add(productId) { },
    remove(productId) { },
    sync() { },
    updateCounter() { }
};

// assets/js/modules/cart.js
export const Cart = {
    init() { },
    add(productId, quantity) { },
    remove(cartItemKey) { },
    update(cartItemKey, quantity) { },
    updateCounter() { }
};

// assets/js/modules/filters.js
export const Filters = {
    init() { },
    applyPriceFilter() { },
    applyCategory() { }
};

// assets/js/main.js
import { Wishlist } from './modules/wishlist.js';
import { Cart } from './modules/cart.js';
import { Filters } from './modules/filters.js';

document.addEventListener('DOMContentLoaded', () => {
    Wishlist.init();
    Cart.init();
    Filters.init();
});
```

#### 🔴 КРИТИЧНО: Дублирование кода

**Файл:** `main.js`, строки 347-355, 370-377, 396-404, 420-428, 513-525, 535-553, 557-575

Массовое дублирование вызова `updateWishlistCounter`:

```javascript
// ПРОБЛЕМА: Этот паттерн повторяется 10+ раз
if (typeof updateWishlistCounter === 'function') {
    if (typeof updateWishlistCounter === 'function') {
        updateWishlistCounter();
    } else if (typeof updateWishlistCount === 'function') {
        updateWishlistCount();
    }
} else if (typeof updateWishlistCount === 'function') {
    updateWishlistCount();
}
```

**Рекомендация:**
```javascript
// Создать единую функцию
function safeUpdateWishlistCounter() {
    if (typeof updateWishlistCounter === 'function') {
        updateWishlistCounter();
    } else if (typeof updateWishlistCount === 'function') {
        updateWishlistCount();
    }
}

// Использовать везде
safeUpdateWishlistCounter();
```

#### 🟠 ВАЖНО: setInterval каждые 10 секунд

**Файл:** `main.js`, строки 556-575

```javascript
// ПРОБЛЕМА: Бесконечный polling создает лишнюю нагрузку
setInterval(() => {
    if (typeof updateWishlistCounter === 'function') { ... }
    if (typeof updateCartCounter === 'function') { ... }
}, 10000);
```

**Рекомендация:** Использовать событийную модель:
```javascript
// Обновлять счетчики только при реальных изменениях
window.addEventListener('storage', (e) => {
    if (e.key === 'favorites') safeUpdateWishlistCounter();
    if (e.key === 'cart') updateCartCounter();
});

// Или использовать BroadcastChannel для синхронизации между вкладками
const bc = new BroadcastChannel('asker_cart');
bc.onmessage = (e) => {
    if (e.data.type === 'cart_updated') updateCartCounter();
};
```

#### 🟠 ВАЖНО: Множественные setTimeout подряд

**Файл:** `main.js`, строки 503-553

```javascript
// ПРОБЛЕМА: Каскад setTimeout
setTimeout(() => { updateWishlistCounter(); updateCartCounter(); }, 100);
setTimeout(() => { updateWishlistCounter(); updateCartCounter(); }, 500);
// ... и далее setInterval
```

**Рекомендация:** Использовать единую функцию инициализации:
```javascript
function initCounters() {
    return new Promise(resolve => {
        // Первичная инициализация
        safeUpdateWishlistCounter();
        updateCartCounter();
        
        // Проверка после загрузки
        window.addEventListener('load', () => {
            safeUpdateWishlistCounter();
            updateCartCounter();
            resolve();
        }, { once: true });
    });
}
```

#### 🟡 ЖЕЛАТЕЛЬНО: console.log в продакшн коде

**Файлы:** `main.js` (множественные)

```javascript
console.log('🔧 Quantity buttons handler loaded (main.js)');
console.log('Filter script loaded');
console.log('Checkbox changed:', checkbox.checked, 'URL:', url);
```

**Рекомендация:** Удалить или обернуть в условие:
```javascript
const DEBUG = false; // или window.ASKER_DEBUG
if (DEBUG) console.log('...');
```

#### 🟡 ЖЕЛАТЕЛЬНО: Отсутствие проверки localStorage

**Файл:** `main.js`

```javascript
// ПРОБЛЕМА: localStorage может быть недоступен (приватный режим)
let favorites = JSON.parse(localStorage.getItem('favorites') || '[]');
```

**Рекомендация:**
```javascript
function getStorageItem(key, defaultValue = null) {
    try {
        const item = localStorage.getItem(key);
        return item ? JSON.parse(item) : defaultValue;
    } catch (e) {
        console.warn('localStorage недоступен:', e);
        return defaultValue;
    }
}

function setStorageItem(key, value) {
    try {
        localStorage.setItem(key, JSON.stringify(value));
        return true;
    } catch (e) {
        console.warn('localStorage недоступен:', e);
        return false;
    }
}
```

### 3.3. Опасные зоны

| Файл:строка | Риск | Описание |
|-------------|------|----------|
| `main.js:556-575` | 🟠 Средний | setInterval создает нагрузку |
| `main.js:347-428` | 🟠 Средний | Массовое дублирование кода |
| `main.js:503-553` | 🟡 Низкий | Каскад setTimeout |
| `main.js` (множ.) | 🟡 Низкий | console.log в продакшн |

### 3.4. Лучшие практики

**Debounce/Throttle для частых событий:**
```javascript
function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

// Использование
const debouncedPriceFilter = debounce(updatePriceFilter, 500);
priceInputMin.addEventListener('input', debouncedPriceFilter);
```

**Event Emitter для внутренней коммуникации:**
```javascript
const AskerEvents = {
    listeners: {},
    on(event, callback) {
        if (!this.listeners[event]) this.listeners[event] = [];
        this.listeners[event].push(callback);
    },
    emit(event, data) {
        if (this.listeners[event]) {
            this.listeners[event].forEach(cb => cb(data));
        }
    }
};

// Использование
AskerEvents.on('cart:updated', () => updateCartCounter());
AskerEvents.on('wishlist:updated', () => safeUpdateWishlistCounter());

// При изменении корзины
AskerEvents.emit('cart:updated', { count: newCount });
```

---

## 4. WordPress/WooCommerce специфика

### 4.1. Сильные стороны ✅

- Правильное использование `wc_get_product()` вместо прямых запросов
- Корректные оверрайды шаблонов с указанием версии
- Использование WooCommerce сессий для корзины
- Интеграция с YITH Wishlist (если установлен)

### 4.2. Проблемы и рекомендации

#### 🟠 ВАЖНО: N+1 проблема в customer-levels.php

**Файл:** `inc/customer-levels.php`, строки 47-60

```php
// ПРОБЛЕМА: Отдельный запрос для каждого заказа
$orders = wc_get_orders([...]);
foreach ($orders as $order_id) {
    $order = wc_get_order($order_id);  // N+1 запрос
    $total_spent += $order->get_total();
}
```

**Рекомендация:** Использовать агрегирующий запрос:
```php
function asker_get_customer_total_spent($user_id) {
    global $wpdb;
    
    $result = $wpdb->get_var($wpdb->prepare("
        SELECT SUM(pm.meta_value) 
        FROM {$wpdb->prefix}wc_orders o
        JOIN {$wpdb->prefix}wc_orders_meta pm ON o.id = pm.order_id
        WHERE o.customer_id = %d 
        AND o.status = 'wc-completed'
        AND pm.meta_key = '_order_total'
    ", $user_id));
    
    return floatval($result);
}
```

Или использовать WooCommerce метод:
```php
$customer = new WC_Customer($user_id);
$total_spent = $customer->get_total_spent();
```

#### 🟡 ЖЕЛАТЕЛЬНО: Версия шаблона устарела

**Файл:** `woocommerce/content-product.php`, строка 9

```php
* @version 9.4.0
```

**Рекомендация:** Проверить актуальность версии при обновлении WooCommerce.

### 4.3. Лучшие практики

**Кеширование данных корзины:**
```php
// Кешировать счетчик корзины для оптимизации
function asker_get_cached_cart_count() {
    $cache_key = 'asker_cart_count_' . WC()->session->get_customer_id();
    $count = wp_cache_get($cache_key);
    
    if ($count === false) {
        $count = WC()->cart->get_cart_contents_count();
        wp_cache_set($cache_key, $count, '', 300); // 5 минут
    }
    
    return $count;
}
```

---

## 5. Безопасность

### 5.1. Сильные стороны ✅

- Nonce используется в формах (`asker_contact_nonce`)
- Санитизация входных данных (`sanitize_text_field`, `sanitize_email`)
- Экранирование вывода (`esc_html`, `esc_attr`, `esc_url`)
- Honeypot защита от спама
- Rate limiting для форм

### 5.2. Критические уязвимости

| ID | Уязвимость | Файл:строка | CVSS | Описание |
|----|------------|-------------|------|----------|
| SEC-001 | SQL Injection | `helpers.php:60-76` | 9.8 | Прямая интерполяция в SQL |
| SEC-002 | Missing CSRF | `woocommerce.php:671-798` | 6.5 | AJAX без nonce проверки |
| SEC-003 | XSS | `helpers.php:258` | 4.3 | Вывод IP без экранирования |
| SEC-004 | Unsafe SVG | `setup.php:50-52` | 3.1 | SVG без валидации содержимого |

### 5.3. Рекомендации по исправлению

**SEC-001: SQL Injection**
```php
// helpers.php - КРИТИЧЕСКОЕ ИСПРАВЛЕНИЕ
$like_term = '%' . $wpdb->esc_like($term) . '%';
$search .= $wpdb->prepare("
    AND ({$wpdb->posts}.post_title LIKE %s)
", $like_term);
```

**SEC-002: Missing CSRF**
```php
// woocommerce.php - Добавить в каждый AJAX обработчик
check_ajax_referer('asker_ajax_nonce', 'nonce');
```

**SEC-003: XSS**
```php
// helpers.php
$email_body .= "IP адрес: " . esc_html($_SERVER['REMOTE_ADDR'] ?? 'Unknown') . "\n";
```

**SEC-004: Unsafe SVG**
```php
// setup.php - Добавить валидацию SVG
add_filter('wp_handle_upload_prefilter', function($file) {
    if ($file['type'] === 'image/svg+xml') {
        $content = file_get_contents($file['tmp_name']);
        if (preg_match('/<script|onclick|onerror|onload|javascript:/i', $content)) {
            $file['error'] = 'SVG содержит потенциально опасный код';
        }
    }
    return $file;
});
```

---

## 6. Производительность

### 6.1. Сильные стороны ✅

- Transient кеширование категорий и цен
- Lazy loading изображений (WordPress native)
- Версионирование по `filemtime()` для cache busting
- Defer загрузка скриптов

### 6.2. Проблемы

| Проблема | Влияние | Рекомендация |
|----------|---------|--------------|
| main.css 195KB | Высокое | Разделить на модули, использовать critical CSS |
| main.js 94KB | Высокое | Модуляризация, code splitting |
| Google Fonts 3x | Среднее | Оставить один способ загрузки |
| @import в CSS | Среднее | Заменить на `<link>` |
| setInterval 10s | Низкое | Заменить на события |
| N+1 запросы | Среднее | Оптимизировать запросы |

### 6.3. Метрики (ожидаемое улучшение)

| Метрика | До | После оптимизации |
|---------|----|--------------------|
| CSS Size | 195KB | ~50KB |
| JS Size | 94KB | ~30KB (gzip) |
| HTTP Requests (CSS) | 4 | 1-2 |
| FCP | ~2.5s | ~1.5s |

---

## 7. Доступность (a11y)

### 7.1. Сильные стороны ✅

- `aria-label` на кнопках без текста
- Размеры тач-таргетов ≥44px
- `focus-visible` стили для кнопок
- Семантическая разметка (`<header>`, `<nav>`, `<main>`)

### 7.2. Проблемы

#### 🟠 ВАЖНО: Отсутствие aria-hidden на декоративных элементах

**Файл:** `header.php`

```html
<!-- ПРОБЛЕМА: Декоративные иконки без aria-hidden -->
<img src=".../heart.svg" alt="Избранное" class="header-icon">

<!-- ИСПРАВЛЕНИЕ: -->
<img src=".../heart.svg" alt="" class="header-icon" aria-hidden="true">
<span class="visually-hidden">Избранное</span>
```

#### 🟡 ЖЕЛАТЕЛЬНО: Skip-link отсутствует

**Рекомендация:**
```html
<!-- header.php после <body> -->
<a href="#main-content" class="skip-link">Перейти к содержимому</a>

<!-- CSS -->
.skip-link {
    position: absolute;
    left: -9999px;
    top: auto;
    width: 1px;
    height: 1px;
    overflow: hidden;
}
.skip-link:focus {
    position: fixed;
    top: 0;
    left: 0;
    width: auto;
    height: auto;
    padding: 1rem;
    background: var(--primary-yellow);
    z-index: 99999;
}
```

---

## 8. Поддерживаемость кода

### 8.1. Сильные стороны ✅

- Комментарии на русском языке
- Документирование функций через PHPDoc
- Осмысленные имена переменных и функций
- Префиксы `asker_` для предотвращения конфликтов

### 8.2. Проблемы

- Файл `woocommerce.php` — 3440 строк (требует рефакторинга)
- Файл `main.js` — 2004 строки (требует модуляризации)
- Дублирование кода в JavaScript
- Отсутствие unit-тестов

### 8.3. Рекомендации

**Структура для рефакторинга:**
```
inc/
├── core/
│   ├── class-asker-ajax.php
│   ├── class-asker-cart.php
│   └── class-asker-wishlist.php
├── woocommerce/
│   ├── cart-ajax.php
│   ├── checkout-fixes.php
│   └── template-overrides.php
└── admin/
    ├── customer-levels.php
    └── managers.php

assets/js/
├── modules/
│   ├── cart.js
│   ├── wishlist.js
│   ├── filters.js
│   └── mobile-menu.js
├── utils/
│   ├── debounce.js
│   ├── storage.js
│   └── ajax.js
└── main.js (entry point)
```

---

## 9. Приоритизация рекомендаций

### Матрица приоритетов

| Рекомендация | Критичность | Сложность | Влияние | Приоритет |
|--------------|-------------|-----------|---------|-----------|
| SEC-001: SQL Injection | 🔴 Критично | Легко | Высокое | **P0** |
| SEC-002: CSRF в AJAX | 🔴 Критично | Легко | Высокое | **P0** |
| JS модуляризация | 🟠 Важно | Сложно | Высокое | **P1** |
| PHP рефакторинг | 🟠 Важно | Сложно | Среднее | **P1** |
| Удалить !important | 🟠 Важно | Легко | Низкое | **P2** |
| Оптимизация CSS | 🟡 Желательно | Средне | Среднее | **P2** |
| N+1 запросы | 🟡 Желательно | Средне | Среднее | **P2** |
| Console.log удаление | 🟡 Желательно | Легко | Низкое | **P3** |
| SVG валидация | 🟡 Желательно | Легко | Низкое | **P3** |

---

## 10. План действий

### Фаза 1: Безопасность (1-2 дня)
1. Исправить SQL-инъекцию в `helpers.php`
2. Добавить nonce проверки во все AJAX обработчики
3. Экранировать вывод IP адреса
4. Добавить валидацию SVG

### Фаза 2: Рефакторинг PHP (1 неделя)
1. Разделить `woocommerce.php` на модули
2. Создать базовый класс для AJAX обработчиков
3. Оптимизировать N+1 запросы

### Фаза 3: Рефакторинг JavaScript (1 неделя)
1. Разделить `main.js` на модули
2. Удалить дублирующийся код
3. Заменить setInterval на события
4. Удалить console.log

### Фаза 4: Оптимизация CSS (3-5 дней)
1. Удалить !important
2. Объединить CSS переменные
3. Убрать дублирование @import
4. Оптимизировать критический CSS

### Фаза 5: Тестирование и документация (2-3 дня)
1. Тестирование исправлений
2. Обновление документации
3. Code review

---

## Заключение

Проект Asker имеет хорошую архитектурную основу, но требует внимания к безопасности и оптимизации. Критические уязвимости (SQL-инъекция, отсутствие CSRF защиты) должны быть исправлены в первую очередь. Рефакторинг больших файлов улучшит поддерживаемость и производительность.

**Контакт для вопросов:** Создайте issue в репозитории проекта.

---

*Отчет сгенерирован: 2024*
*Версия темы: Asker*
*Аудитор: AI Assistant (Opus/Claude)*
