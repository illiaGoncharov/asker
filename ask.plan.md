<!-- 0ede86a6-2396-42c7-ac39-4046a3cc1b53 9479c618-d658-450d-afcf-18fa8b482d8c -->
# План: WP-тема «Asker» (классическая, ACF Free, WooC, CF7)

## 1) Структура темы и базовая подготовка

- Директория темы: `wp-content/themes/asker/`
- Ключевые файлы/папки:
                                - `style.css` (шапка темы + базовые стили)
                                - `functions.php` (инициализация темы)
                                - `header.php`, `footer.php`, `index.php`, `front-page.php`, `single.php`, `page.php`, `archive.php`
                                - `templates/` (частные шаблоны при необходимости)
                                - `parts/` (фрагменты: `hero.php`, `header-nav.php`, `footer-widgets.php`)
                                - `inc/` (модули темы: `setup.php`, `enqueue.php`, `customizer.php`, `acf.php`, `woocommerce.php`, `helpers.php`)
                                - `woocommerce/` (будущие оверрайды WooCommerce)
                                - `assets/css/`, `assets/js/`, `assets/img/`, `assets/fonts/`
                                - `languages/` (i18n .po/.mo)
                                - `acf-json/` (Local JSON ACF)
                                - `screenshot.png` (1200×900 превью темы)

- `style.css` (шапка):
```css
/*
Theme Name: Asker
Theme URI: https://askerspb.ru
Author: Asker Team
Description: Классическая тема-магазин на WooCommerce + ACF + CF7
Version: 0.1.0
Text Domain: asker
*/
```

- `functions.php` (ядро инициализации):
```php
require_once get_template_directory() . '/inc/setup.php';
require_once get_template_directory() . '/inc/enqueue.php';
require_once get_template_directory() . '/inc/customizer.php';
require_once get_template_directory() . '/inc/acf.php';
require_once get_template_directory() . '/inc/woocommerce.php';
require_once get_template_directory() . '/inc/helpers.php';
```


## 2) Инициализация темы (inc/setup.php)

- `title-tag`, `post-thumbnails`, `html5`, `woocommerce`
- Меню: `primary`, `footer`
- Текстовый домен `asker`

## 3) Ассеты (inc/enqueue.php)

- `assets/css/main.css`, `assets/js/main.js`, `assets/js/cookie-consent.js`
- Версионирование по `filemtime`

## 4) ACF Free: Local JSON (inc/acf.php)

- Включить сохранение/загрузку в `acf-json/`
- Группа «Главная»: hero, категории, товары, SEO

## 5) Главная (front-page.php)

- Hero из ACF
- «Популярные категории» (`product_cat`)
- «Рекомендуемые товары» (выбранные/фолбэк шорткод)

## 6) WooCommerce минимум (inc/woocommerce.php)

- Поддержка WooC, без оверрайдов на старте

## 7) Contact Form 7

- Шорткод на странице «Контакты», базовые стили в `main.css`

## 8) Куки‑баннер

- Разметка в `footer.php`, логика в `cookie-consent.js` (localStorage)

## 9) Страницы/меню

- Создать юридические и системные, привязать Woo страницы, назначить меню

## 10) Плагины

- WooCommerce, ACF, Contact Form 7 (+по желанию SEO/кэш/SMTP)

## 11) Git и единый источник истины

- Репозиторий содержит только `wp-content/themes/asker/`
- Корневой `.gitignore` игнорит всё кроме темы; в теме — приват/временные и `NOTES.private.md`
- Conventional Commits (scopes: `theme`, `home`, `woo`, `acf`, `cf7`, `cookie`, `seo`, `styles`)

Корневой `.gitignore`:
```
*
!/.gitignore
!/ask.plan.md
!/docker-compose.yml
!/CURSOR.md
!/.cursor/**
!/.github/**
!wp-content/
wp-content/*
!wp-content/themes/
wp-content/themes/*
!wp-content/themes/asker/
```

`.gitignore` в теме:
```
*.log
.DS_Store
.vscode/
.idea/
.env
assets/src/
NOTES.private.md
```

## 12) FTP (Beget)

- Заливка только папки темы, активация, плагины, статическая главная

## 13) Комментарии и чистота кода

- Комментарии только для неочевидной логики; функции в `inc/*`

## 14) Docker локалка

Файлы в корне:

- `docker-compose.yml` (db: MySQL 8, wp: WordPress Apache, pma: опц.)
- `.env.example` → скопировать в `.env` (опционально, есть дефолты)
- Монтирование: `./wp-content/themes/asker:/var/www/html/wp-content/themes/asker`
- Порты: WP `http://localhost:8080`, PMA `http://localhost:8081`

Скетч compose:
```yaml
db:
  image: mysql:8
  environment:
    MYSQL_DATABASE: wordpress
    MYSQL_USER: wordpress
    MYSQL_PASSWORD: wordpress
    MYSQL_ROOT_PASSWORD: root
  volumes:
    - db_data:/var/lib/mysql
wp:
  image: wordpress:6-php8.2-apache
  ports:
    - "8080:80"
  environment:
    WORDPRESS_DB_HOST: db:3306
    WORDPRESS_DB_USER: wordpress
    WORDPRESS_DB_PASSWORD: wordpress
    WORDPRESS_DB_NAME: wordpress
  volumes:
    - ./wp-content/themes/asker:/var/www/html/wp-content/themes/asker
  depends_on:
    - db
pma:
  image: phpmyadmin:latest
  ports:
    - "8081:80"
  environment:
    PMA_HOST: db
volumes:
  db_data:
```

Пример `.env.example`:
```
MYSQL_DATABASE=wordpress
MYSQL_USER=wordpress
MYSQL_PASSWORD=wordpress
MYSQL_ROOT_PASSWORD=root
WP_PORT=8080
PMA_PORT=8081
```

## 15) Cursor Rules (CURSOR.md)

- Короткие статус‑апдейты, внятные коммиты
- Вести To‑Dos, отмечать прогресс
- Не смешивать изменения, соблюдать читаемость
- Для WP: логика в `inc/*`, минимальные зависимости

## 16) Релизы/статистика GitHub

- Версию хранить в `style.css` → `Version`
- `.github/workflows/release.yml` — по тегу `v*` собрать zip темы и опубликовать Release

Workflow (скетч):
```yaml
name: release-theme
on:
  push:
    tags:
      - 'v*'
jobs:
  build:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v4
      - name: Zip theme
        run: |
          cd wp-content/themes
          zip -r asker.zip asker -x "**/.git/**" "**/NOTES.private.md"
      - name: Upload Release Asset
        uses: softprops/action-gh-release@v2
        with:
          files: wp-content/themes/asker.zip
```

## 17) Порядок работ

1. Docker up: добавить `docker-compose.yml`, `.env.example`, запустить, активировать тему.
2. Главная по Фигме: адаптировать `front-page.php`, `assets/css/main.css`, заполнить ACF.
3. Локальная проверка (меню, Woo, CF7, cookies).
4. GitHub: init/remote, пуш `main` (единый источник правды).
5. Release: создать тег `v0.1.0` — GH Actions публикует zip.
6. FTP на Beget и финальная проверка.

---

### To-dos

- [ ] Docker локалка — compose + env; WP: http://localhost:8080, PMA: http://localhost:8081
  - Команды:
    - cp .env.example .env (опц.)
    - docker compose up -d
    - открыть http://localhost:8080
  - Критерии: БД поднимается, WP устанавливается, тема монтируется, PMA доступен
- [ ] Главная по Фигме — шрифты/сетка/цвета, адаптив
  - Критерии: визуальное соответствие ≥90%, корректные брейкпоинты
- [ ] Локальная проверка — меню, Woo страницы, CF7, cookies
  - Критерии: меню назначены, Woo страницы привязаны, форма отправляется, баннер скрывается
- [ ] Корневой .gitignore — единый источник истины (трек только темы + инфраструктура)
- [ ] CURSOR.md — правила процесса (статусы, To‑Dos, коммиты)
- [ ] Push на GitHub — init/remote/push main
  - Команды:
    - git init
    - git add .
    - git commit -m "feat(theme): init Asker theme skeleton"
    - git remote add origin https://github.com/illiaGoncharov/asker-shop.git
    - git branch -M main
    - git push -u origin main
- [ ] Release flow — GH Actions + тег релиза
  - Команды:
    - git tag v0.1.0
    - git push origin v0.1.0
  - Критерии: создаётся Release с zip темы
- [ ] FTP на Beget — заливка и smoke‑тест
  - Критерии: активная тема, открываются главная/каталог/корзина/оформление



