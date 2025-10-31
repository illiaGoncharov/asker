<?php
/**
 * Шапка сайта: мета, лого, основное меню.
 */
?><!doctype html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    
    <!-- Подключение шрифтов Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700&family=Poppins:wght@300;400;500;600;700&family=Manrope:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <?php wp_head(); ?>
    
    <!-- Стиль для простого лого -->
    <style>.site-logo{font-weight:700;font-size:18px;letter-spacing:.5px}</style>
    
    <!-- Подключение функций хедера -->
    <script src="<?php echo get_template_directory_uri(); ?>/assets/js/header-functions.js"></script>
    
    <!-- Отладочная информация для проверки разрешения -->
    <script>
        console.log('Разрешение экрана:', window.screen.width + 'x' + window.screen.height);
        console.log('Разрешение окна браузера:', window.innerWidth + 'x' + window.innerHeight);
        console.log('Device Pixel Ratio:', window.devicePixelRatio);
        console.log('CSS пиксели (window.innerWidth):', window.innerWidth);
    </script>
    
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<header class="site-header">
    <div class="header-main">
        <div class="container">
            <!-- Кнопка бургер-меню (только на мобильных) -->
            <button class="mobile-menu-toggle" aria-label="Открыть меню">
                <span></span>
                <span></span>
                <span></span>
            </button>
            
            <div class="logo">
                <a href="<?php echo esc_url(home_url('/')); ?>">
                    <?php if (has_custom_logo()) : ?>
                        <?php the_custom_logo(); ?>
                    <?php else : ?>
                        <span class="logo-main">ASKER</span>
                        <span class="logo-parts">PARTS</span>
                    <?php endif; ?>
                </a>
            </div>
            <!-- Бургер-меню для навигации -->
            <div class="nav-dropdown">
                <button class="btn-nav-menu" id="nav-menu-toggle" aria-label="Меню навигации">
                    <span class="burger-icon">☰</span>
                </button>
                <div class="nav-dropdown-menu" id="nav-dropdown-menu">
                    <a href="<?php echo esc_url(home_url('/payment')); ?>" class="nav-menu-item">Оплата</a>
                    <a href="<?php echo esc_url(home_url('/delivery')); ?>" class="nav-menu-item">Доставка</a>
                    <a href="<?php echo esc_url(home_url('/guarantees')); ?>" class="nav-menu-item">Гарантии</a>
                    <a href="<?php echo esc_url(home_url('/about')); ?>" class="nav-menu-item">О компании</a>
                    <a href="<?php echo esc_url(home_url('/contacts')); ?>" class="nav-menu-item">Контакты</a>
                </div>
            </div>
            
            <!-- Кнопка Каталог -->
            <a href="<?php echo esc_url(home_url('/shop')); ?>" class="btn-catalog">
                Каталог
            </a>
            <div class="search-bar">
                <form role="search" method="get" action="<?php echo esc_url(home_url('/')); ?>">
                    <div class="search-input-wrapper">
                        <input class="search-input" type="search" name="s" placeholder="Поиск по названию / категории / артикулу" value="<?php echo get_search_query(); ?>" autocomplete="off">
                        <img src="<?php echo get_template_directory_uri(); ?>/assets/images/icons/magnifyer.svg" alt="Поиск" class="search-icon">
                    </div>
                    <input type="hidden" name="post_type" value="product">
                </form>
            </div>
            <div class="header-actions">
                <a href="tel:<?php echo get_option('woocommerce_store_phone', '+7 (812) 123-45-67'); ?>" class="icon-phone">
                    <img src="<?php echo get_template_directory_uri(); ?>/assets/images/icons/phone.svg" alt="Телефон" class="header-icon">
                </a>
                <a href="#" class="icon-chat" onclick="openChatPopup(); return false;">
                    <img src="<?php echo get_template_directory_uri(); ?>/assets/images/icons/chat.svg" alt="Чат" class="header-icon">
                </a>
                <a href="mailto:<?php echo get_option('woocommerce_store_email', 'info@askerspb.ru'); ?>" class="icon-mail">
                    <img src="<?php echo get_template_directory_uri(); ?>/assets/images/icons/message.svg" alt="Сообщение" class="header-icon">
                </a>
                <a href="<?php echo esc_url(home_url('/wishlist')); ?>" class="icon-heart">
                    <img src="<?php echo get_template_directory_uri(); ?>/assets/images/icons/heart.svg" alt="Избранное" class="header-icon">
                    <span class="wishlist-count" data-count="0">0</span>
                </a>
                <a href="<?php echo esc_url(home_url('/cart')); ?>" class="icon-cart">
                    <img src="<?php echo get_template_directory_uri(); ?>/assets/images/icons/cart.svg" alt="Корзина" class="header-icon">
                    <span class="cart-count" data-count="0">0</span>
                </a>
                <a href="<?php echo esc_url(home_url('/my-account')); ?>" class="btn-login">
                    <img src="<?php echo get_template_directory_uri(); ?>/assets/images/icons/account.svg" alt="Аккаунт" class="header-icon">
                    <span><?php echo is_user_logged_in() ? 'ЛК' : 'Войти'; ?></span>
                </a>
            </div>
        </div>
    </div>
</header>

<!-- Мобильное меню -->
<div class="mobile-menu">
    <div class="mobile-menu-header">
        <div class="logo">
            <a href="<?php echo esc_url(home_url('/')); ?>">
                <?php if (has_custom_logo()) : ?>
                    <?php the_custom_logo(); ?>
                <?php else : ?>
                    <span class="logo-main">ASKER</span>
                    <span class="logo-parts">PARTS</span>
                <?php endif; ?>
            </a>
        </div>
        <button class="mobile-menu-close" aria-label="Закрыть меню">&times;</button>
    </div>
    <div class="mobile-menu-content">
        <div class="mobile-search">
            <form role="search" method="get" action="<?php echo esc_url(home_url('/')); ?>">
                <input class="search-input" type="search" name="s" placeholder="Поиск товаров..." value="<?php echo get_search_query(); ?>">
                <button type="submit" class="mobile-search-btn">
                    <img src="<?php echo get_template_directory_uri(); ?>/assets/images/icons/magnifyer.svg" alt="Поиск">
                </button>
                <input type="hidden" name="post_type" value="product">
            </form>
        </div>
        <nav class="mobile-menu-nav">
            <a href="<?php echo esc_url(home_url('/shop')); ?>" class="mobile-menu-link">
                <span class="mobile-link-text">
                    <svg class="mobile-menu-icon" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <rect x="3" y="3" width="8" height="8" rx="2" stroke="currentColor" stroke-width="2"/>
                        <rect x="3" y="13" width="8" height="8" rx="2" stroke="currentColor" stroke-width="2"/>
                        <rect x="13" y="3" width="8" height="8" rx="2" stroke="currentColor" stroke-width="2"/>
                        <rect x="13" y="13" width="8" height="8" rx="2" stroke="currentColor" stroke-width="2"/>
                    </svg>
                    Каталог
                </span>
            </a>
            <a href="<?php echo esc_url(home_url('/wishlist')); ?>" class="mobile-menu-link">
                <span class="mobile-link-text">
                    <img src="<?php echo get_template_directory_uri(); ?>/assets/images/icons/heart.svg" alt="" class="mobile-menu-icon">
                    Избранное
                </span>
                <span class="mobile-wishlist-count">0</span>
            </a>
            <a href="<?php echo esc_url(home_url('/cart')); ?>" class="mobile-menu-link">
                <span class="mobile-link-text">
                    <img src="<?php echo get_template_directory_uri(); ?>/assets/images/icons/cart.svg" alt="" class="mobile-menu-icon">
                    Корзина
                </span>
                <span class="mobile-cart-count">0</span>
            </a>
            <a href="<?php echo esc_url(home_url('/my-account')); ?>" class="mobile-menu-link">
                <span class="mobile-link-text">
                    <img src="<?php echo get_template_directory_uri(); ?>/assets/images/icons/account.svg" alt="" class="mobile-menu-icon">
                    Личный кабинет
                </span>
            </a>
        </nav>
        <div class="mobile-menu-contacts">
            <h4>Контакты</h4>
            <a href="tel:<?php echo get_option('woocommerce_store_phone', '+7 (812) 123-45-67'); ?>" class="mobile-contact-link">
                <img src="<?php echo get_template_directory_uri(); ?>/assets/images/icons/phone.svg" alt="" class="mobile-contact-icon">
                <span><?php echo get_option('woocommerce_store_phone', '+7 (812) 123-45-67'); ?></span>
            </a>
            <a href="mailto:<?php echo get_option('woocommerce_store_email', 'info@askerspb.ru'); ?>" class="mobile-contact-link">
                <img src="<?php echo get_template_directory_uri(); ?>/assets/images/icons/message.svg" alt="" class="mobile-contact-icon">
                <span><?php echo get_option('woocommerce_store_email', 'info@askerspb.ru'); ?></span>
            </a>
            <a href="#" class="mobile-contact-link" onclick="openChatPopup(); document.querySelector('.mobile-menu-close').click(); return false;">
                <img src="<?php echo get_template_directory_uri(); ?>/assets/images/icons/chat.svg" alt="" class="mobile-contact-icon">
                <span>Онлайн-чат</span>
            </a>
        </div>
    </div>
</div>
<div class="mobile-menu-overlay"></div>


<main class="site-main">



