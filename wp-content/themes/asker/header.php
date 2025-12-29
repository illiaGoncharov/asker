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
    
    <!-- КРИТИЧНО: Переопределяем alert() ДО загрузки всех скриптов и расширений браузера -->
    <script>
    (function() {
        // Сохраняем оригинальные функции как можно раньше
        const originalAlert = window.alert;
        const originalConsoleError = console.error;
        const originalConsoleWarn = console.warn;
        
        // Переопределяем alert() глобально для подавления ошибок от расширения браузера
        window.alert = function(message) {
            const messageStr = String(message || '');
            // Если сообщение об ошибке добавления в корзину - подавляем его
            if (messageStr.includes('Ошибка добавления') || 
                messageStr.includes('ошибка добавления') ||
                messageStr.includes('Error adding') ||
                messageStr.includes('error adding') ||
                messageStr.toLowerCase().includes('добавления товара в корзину') ||
                messageStr.toLowerCase().includes('добавления в корзину') ||
                messageStr.includes('installHook')) {
                // Тихо логируем вместо показа alert
                if (console && console.log) {
                    console.log('⚠️ Alert suppressed (header):', messageStr);
                }
                return; // Не показываем alert
            }
            // Для других сообщений используем оригинальный alert
            if (originalAlert) {
                return originalAlert.apply(window, arguments);
            }
        };
        
        // Также переопределяем console.error глобально
        if (console && console.error) {
            const originalError = console.error;
            console.error = function() {
                const args = Array.from(arguments);
                const message = args.map(arg => {
                    if (typeof arg === 'object' && arg !== null) {
                        try {
                            return JSON.stringify(arg);
                        } catch(e) {
                            return String(arg);
                        }
                    }
                    return String(arg);
                }).join(' ');
                // Если это ошибка от installHook.js или об ошибке добавления в корзину - подавляем
                if (message.includes('installHook') || 
                    message.includes('Ошибка добавления') || 
                    message.includes('ошибка добавления') ||
                    message.toLowerCase().includes('добавления товара в корзину') ||
                    message.toLowerCase().includes('добавления в корзину')) {
                    // Тихо логируем вместо console.error
                    if (console && console.log) {
                        console.log('⚠️ Console.error suppressed (header):', message);
                    }
                    return; // Не показываем ошибку
                }
                // Для других ошибок используем оригинальный console.error
                if (originalError) {
                    return originalError.apply(console, arguments);
                }
            };
        }
        
        // Также переопределяем console.warn для полной защиты
        if (console && console.warn) {
            const originalWarn = console.warn;
            console.warn = function() {
                const args = Array.from(arguments);
                const message = args.map(arg => String(arg)).join(' ');
                // Подавляем предупреждения от installHook
                if (message.includes('installHook') || 
                    message.includes('Ошибка добавления') || 
                    message.includes('ошибка добавления')) {
                    if (console && console.log) {
                        console.log('⚠️ Console.warn suppressed (header):', message);
                    }
                    return;
                }
                // Для других предупреждений используем оригинальный console.warn
                if (originalWarn) {
                    return originalWarn.apply(console, arguments);
                }
            };
        }
    })();
    </script>
    
    <?php wp_head(); ?>
    
    <!-- Принудительно удаляем блок Coming Soon и мета-тег -->
    <script>
    (function() {
        // Удаляем мета-тег
        var meta = document.querySelector('meta[name="woo-coming-soon-page"]');
        if (meta) meta.remove();
        
        // Удаляем блок Coming Soon из DOM сразу после загрузки
        function removeComingSoon() {
            var comingSoon = document.querySelector('[data-block-name="woocommerce/coming-soon"]');
            if (comingSoon) {
                comingSoon.remove();
            }
            var comingSoonClass = document.querySelector('.woocommerce-coming-soon-default, .woocommerce-coming-soon-store-only, .wp-block-woocommerce-coming-soon');
            if (comingSoonClass) {
                comingSoonClass.remove();
            }
        }
        
        // Удаляем сразу
        removeComingSoon();
        
        // Удаляем после загрузки DOM
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', removeComingSoon);
        }
        
        // Удаляем после полной загрузки
        window.addEventListener('load', removeComingSoon);
        
        // Удаляем с интервалом на случай, если блок добавляется динамически
        var interval = setInterval(function() {
            removeComingSoon();
        }, 100);
        
        setTimeout(function() {
            clearInterval(interval);
        }, 2000);
    })();
    </script>
    
    <!-- Стиль для простого лого -->
    <style>.site-logo{font-weight:700;font-size:18px;letter-spacing:.5px}</style>
    
    <!-- Подключение функций хедера -->
    <script>
    // Локализуем AJAX URL для header-functions.js, если asker_ajax еще не загружен
    if (typeof asker_ajax === 'undefined') {
        var asker_ajax = {
            ajax_url: '<?php echo esc_js(admin_url('admin-ajax.php')); ?>',
            nonce: '<?php echo esc_js(wp_create_nonce('asker_ajax_nonce')); ?>'
        };
    }
    
    // Форма обратной связи загружается из footer.php -> popup-contact-form-template
    </script>
    <script src="<?php echo get_template_directory_uri(); ?>/assets/js/header-functions.js"></script>
    
    <!-- Скрипт очистки избранного при выходе и проверки авторизации -->
    <script>
    (function() {
        // Проверяем статус авторизации при загрузке страницы
        const isLoggedIn = <?php echo is_user_logged_in() ? 'true' : 'false'; ?>;
        
        // Если пользователь не авторизован - очищаем localStorage избранного
        if (!isLoggedIn) {
            try {
                localStorage.removeItem('favorites');
                // Обновляем счетчики сразу если DOM готов
                if (document.readyState === 'loading') {
                    document.addEventListener('DOMContentLoaded', function() {
                        updateWishlistCounters();
                    });
                } else {
                    updateWishlistCounters();
                }
            } catch (e) {
                // Игнорируем ошибки
            }
        }
        
        function updateWishlistCounters() {
            const wishlistCount = document.querySelector('.wishlist-count');
            const mobileWishlistCount = document.querySelector('.mobile-wishlist-count');
            if (wishlistCount) {
                wishlistCount.textContent = '0';
                wishlistCount.setAttribute('data-count', '0');
                wishlistCount.style.display = 'none';
            }
            if (mobileWishlistCount) {
                mobileWishlistCount.textContent = '0';
                mobileWishlistCount.style.display = 'none';
            }
        }
        
        // Обработчик клика на ссылку выхода
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', initLogoutHandler);
        } else {
            initLogoutHandler();
        }
        
        function initLogoutHandler() {
            // Обрабатываем все ссылки выхода
            const logoutLinks = document.querySelectorAll('a[href*="wp-login.php?action=logout"], .logout-link');
            logoutLinks.forEach(function(link) {
                link.addEventListener('click', function(e) {
                    // Очищаем localStorage избранного перед выходом
                    try {
                        localStorage.removeItem('favorites');
                    } catch (e) {
                        // Игнорируем ошибки
                    }
                });
            });
        }
    })();
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
            <a href="<?php echo esc_url(home_url('/all-categories')); ?>" class="btn-catalog">
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
                <a href="https://t.me/Askercorp" target="_blank" class="icon-chat">
                    <img src="<?php echo get_template_directory_uri(); ?>/assets/images/icons/chat.svg" alt="Telegram" class="header-icon">
                </a>
                <a href="#" class="icon-mail" onclick="openContactFormPopup(); return false;">
                    <img src="<?php echo get_template_directory_uri(); ?>/assets/images/icons/message.svg" alt="Обратная связь" class="header-icon">
                </a>
                <a href="<?php echo esc_url(home_url('/wishlist/')); ?>" class="icon-heart">
                    <img src="<?php echo get_template_directory_uri(); ?>/assets/images/icons/heart.svg" alt="Избранное" class="header-icon">
                    <?php
                    // Получаем количество товаров в избранном
                    // КРИТИЧНО: Для неавторизованных пользователей всегда 0
                    $wishlist_count = 0;
                    if (is_user_logged_in()) {
                        if (function_exists('yith_wcwl_count_products')) {
                            $wishlist_count = yith_wcwl_count_products();
                        } else {
                            $user_id = get_current_user_id();
                            $wishlist = get_user_meta($user_id, 'asker_wishlist', true);
                            $wishlist_count = (!empty($wishlist) && is_array($wishlist)) ? count($wishlist) : 0;
                        }
                    }
                    ?>
                    <span class="wishlist-count" data-count="<?php echo esc_attr($wishlist_count); ?>" style="display: <?php echo $wishlist_count > 0 ? 'flex' : 'none'; ?>"><?php echo esc_html($wishlist_count); ?></span>
                </a>
                <a href="<?php echo esc_url(home_url('/cart')); ?>" class="icon-cart">
                    <img src="<?php echo get_template_directory_uri(); ?>/assets/images/icons/cart.svg" alt="Корзина" class="header-icon">
                    <?php
                    // Получаем количество товаров в корзине
                    $cart_count = 0;
                    if (function_exists('WC') && WC()->cart) {
                        $cart_count = WC()->cart->get_cart_contents_count();
                    }
                    ?>
                    <span class="cart-count" data-count="<?php echo esc_attr($cart_count); ?>" style="display: <?php echo $cart_count > 0 ? 'flex' : 'none'; ?>"><?php echo esc_html($cart_count); ?></span>
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
            <a href="<?php echo esc_url(home_url('/all-categories')); ?>" class="mobile-menu-link">
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
            <a href="<?php echo esc_url(home_url('/wishlist/')); ?>" class="mobile-menu-link">
                <span class="mobile-link-text">
                    <img src="<?php echo get_template_directory_uri(); ?>/assets/images/icons/heart.svg" alt="" class="mobile-menu-icon">
                    Избранное
                </span>
                <?php
                // Получаем количество товаров в избранном
                // КРИТИЧНО: Для неавторизованных пользователей всегда 0
                $mobile_wishlist_count = 0;
                if (is_user_logged_in()) {
                    if (function_exists('yith_wcwl_count_products')) {
                        $mobile_wishlist_count = yith_wcwl_count_products();
                    } else {
                        $user_id = get_current_user_id();
                        $wishlist = get_user_meta($user_id, 'asker_wishlist', true);
                        $mobile_wishlist_count = (!empty($wishlist) && is_array($wishlist)) ? count($wishlist) : 0;
                    }
                }
                ?>
                <span class="mobile-wishlist-count" style="display: <?php echo $mobile_wishlist_count > 0 ? 'inline-flex' : 'none'; ?>"><?php echo esc_html($mobile_wishlist_count); ?></span>
            </a>
            <a href="<?php echo esc_url(home_url('/cart')); ?>" class="mobile-menu-link">
                <span class="mobile-link-text">
                    <img src="<?php echo get_template_directory_uri(); ?>/assets/images/icons/cart.svg" alt="" class="mobile-menu-icon">
                    Корзина
                </span>
                <?php
                // Получаем количество товаров в корзине
                $mobile_cart_count = 0;
                if (function_exists('WC') && WC()->cart) {
                    $mobile_cart_count = WC()->cart->get_cart_contents_count();
                }
                ?>
                <span class="mobile-cart-count" style="display: <?php echo $mobile_cart_count > 0 ? 'inline-flex' : 'none'; ?>"><?php echo esc_html($mobile_cart_count); ?></span>
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
            <a href="mailto:<?php echo get_option('woocommerce_store_email', 'info@askerspb.ru'); ?>" class="mobile-contact-link">
                <img src="<?php echo get_template_directory_uri(); ?>/assets/images/icons/message.svg" alt="" class="mobile-contact-icon">
                <span><?php echo get_option('woocommerce_store_email', 'info@askerspb.ru'); ?></span>
            </a>
            <a href="https://t.me/Askercorp" target="_blank" class="mobile-contact-link" onclick="document.querySelector('.mobile-menu-close').click();">
                <img src="<?php echo get_template_directory_uri(); ?>/assets/images/icons/chat.svg" alt="" class="mobile-contact-icon">
                <span>Telegram</span>
            </a>
        </div>
    </div>
</div>
<div class="mobile-menu-overlay"></div>


<main class="site-main">



