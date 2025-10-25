# CURSOR Rules для проекта Asker

## 🎯 Основные принципы

### 1. **БЭМ методология**
- Используем БЭМ для всех CSS классов
- Блок: `.header`, `.footer`, `.product-card`
- Элемент: `.header__logo`, `.product-card__title`, `.product-card__price`
- Модификатор: `.button--primary`, `.button--secondary`, `.product-card--featured`

### 2. **Структура классов**
```css
/* ✅ Правильно */
.header__logo
.header__nav
.header__nav-item
.header__nav-item--active
.product-card__image
.product-card__title
.product-card__price

/* ❌ Неправильно */
.frame-15
.text-wrapper-47
.div-wrapper-2
.container-13
```

### 3. **Именование компонентов**
- **Секции**: `.hero`, `.products`, `.delivery`, `.about`, `.wholesale`
- **Карточки**: `.product-card`, `.delivery-card`, `.feature-card`
- **Формы**: `.contact-form`, `.wholesale-form`
- **Кнопки**: `.btn`, `.btn--primary`, `.btn--secondary`, `.btn--outline`

## 🚫 Что НЕ использовать

### Anima/Figma мусор:
- `frame-*`, `frame-wrapper`
- `text-wrapper-*`, `text-wrapper`
- `div-wrapper-*`, `div-wrapper`
- `container-*` (кроме основного `.container`)
- `view-*`, `view-wrapper`
- `group-*`, `vector-*`
- `background-*`, `margin-*`

### Плохие практики:
- Генерируемые имена классов
- Неописательные названия
- Слишком длинные цепочки классов
- Дублирование стилей

## 📁 Структура файлов

```
wp-content/themes/asker/
├── assets/
│   ├── css/
│   │   └── main.css          # Основные стили
│   └── js/
│       ├── main.js           # Основная логика
│       └── cookie-consent.js # Cookie баннер
├── inc/                      # Модули PHP
├── acf-json/                 # ACF поля
├── templates/                # Шаблоны страниц
└── woocommerce/              # WooCommerce оверрайды
```

## 🎨 CSS организация

### 1. **Порядок стилей**
```css
/* 1. CSS переменные */
:root { ... }

/* 2. Сброс и базовые стили */
* { ... }
body { ... }

/* 3. Утилиты */
.container { ... }

/* 4. Компоненты по БЭМ */
.header { ... }
.header__logo { ... }
.header__nav { ... }

/* 5. Адаптивность */
@media (max-width: 768px) { ... }
```

### 2. **Комментарии**
```css
/* ===== HEADER ===== */
.header { ... }

/* ===== PRODUCTS ===== */
.products { ... }
.product-card { ... }
```

## 🔧 PHP стандарты

### 1. **WordPress функции**
```php
// ✅ Правильно
<?php echo esc_url(home_url('/')); ?>
<?php echo esc_html($title); ?>
<?php echo wp_get_attachment_image($image_id, 'large'); ?>

// ❌ Неправильно
<?php echo $title; ?> // Без экранирования
```

### 2. **ACF поля**
```php
// ✅ Правильно
<?php 
$hero_title = get_field('hero_title');
if ($hero_title) : 
?>
    <h1><?php echo esc_html($hero_title); ?></h1>
<?php endif; ?>

// ❌ Неправильно
<?php echo get_field('hero_title'); ?> // Без проверки
```

## 📱 Адаптивность

### Breakpoints:
- **Mobile**: до 768px
- **Tablet**: 768px - 1024px  
- **Desktop**: 1024px+

### Подход:
```css
/* Mobile First */
.product-card {
  width: 100%;
}

/* Tablet */
@media (min-width: 768px) {
  .product-card {
    width: calc(50% - 16px);
  }
}

/* Desktop */
@media (min-width: 1024px) {
  .product-card {
    width: calc(25% - 24px);
  }
}
```

## 🚀 Git workflow

### Conventional Commits:
```
feat: добавить секцию оптовых клиентов
fix: исправить отображение логотипа в header
style: обновить цвета кнопок
refactor: переписать footer на БЭМ
docs: обновить README
```

### Scopes:
- `header` - изменения в header
- `footer` - изменения в footer  
- `hero` - hero секция
- `products` - секция товаров
- `css` - стили
- `acf` - ACF поля
- `woo` - WooCommerce

## 🧪 Тестирование

### Перед коммитом проверить:
1. **Валидность HTML** - нет ошибок в консоли
2. **Адаптивность** - работает на всех устройствах
3. **Производительность** - быстрая загрузка
4. **Доступность** - семантичная разметка
5. **Браузеры** - Chrome, Firefox, Safari

## 📝 Документация

### Обязательно документировать:
- Новые компоненты в README
- Сложную логику в комментариях
- Изменения в CHANGELOG
- Настройки в SETUP-GUIDE

---

**Помни**: Код должен быть читаемым, поддерживаемым и следовать стандартам WordPress!




