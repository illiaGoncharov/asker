<?php
/**
 * Подвал сайта и куки-бар.
 */
?>
    </main>

<footer class="footer">
    <div class="footer__content">
        <!-- Левая колонка: Логотип, описание, контакты, соцсети -->
        <div class="footer__brand">
            <div class="footer__logo">
                <a href="<?php echo esc_url(home_url('/')); ?>">
                    <?php 
                    $footer_logo_id = get_theme_mod('footer_logo');
                    if ($footer_logo_id) {
                        $footer_logo_url = wp_get_attachment_image_url($footer_logo_id, 'full');
                    } else {
                        $footer_logo_url = get_template_directory_uri() . '/assets/images/logo[white].png';
                    }
                    ?>
                    <img src="<?php echo esc_url($footer_logo_url); ?>" alt="ASKER PARTS" class="footer__logo-img">
                </a>
            </div>
            
            <p class="footer__description">
                Asker — наша компания стремится удовлетворить потребности клиентов в качественных и доступных запчастях для бытовой техники. Мы ценим каждого клиента, поэтому индивидуальный подход это основа нашего отношения к клиентам.
            </p>
            
            <div class="footer__contacts">
                <h4 class="footer__title">Контакты</h4>
                <p class="footer__address">Санкт-Петербург, ул. Карпатская д. 16</p>
                <a href="mailto:sales@asker-corp.ru" class="footer__contact-link">sales@asker-corp.ru</a>
                <a href="tel:+79311099476" class="footer__contact-link">+7 (931) 109-94-76</a>
            </div>
            
            <div class="footer__social-section">
                <h4 class="footer__title">Мы в соцсетях</h4>
                <div class="footer__social">
                    <a href="#" class="footer__social-link" aria-label="Telegram">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm4.64 6.8c-.15 1.58-.8 5.42-1.13 7.19-.14.75-.42 1-.68 1.03-.58.05-1.02-.38-1.58-.75-.88-.58-1.38-.94-2.23-1.5-.99-.65-.35-1.01.22-1.59.15-.15 2.71-2.48 2.76-2.69a.2.2 0 00-.05-.18c-.06-.05-.14-.03-.21-.02-.09.02-1.49.95-4.22 2.79-.4.27-.76.41-1.08.4-.36-.01-1.04-.2-1.55-.37-.63-.2-1.12-.31-1.08-.66.02-.18.27-.36.74-.55 2.92-1.27 4.86-2.11 5.83-2.51 2.78-1.16 3.35-1.36 3.73-1.36.08 0 .27.02.39.12.1.08.13.19.14.27-.01.06.01.24 0 .38z"/>
                        </svg>
                    </a>
                    <a href="#" class="footer__social-link" aria-label="WhatsApp">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M12.04 2c-5.46 0-9.91 4.45-9.91 9.91 0 1.75.46 3.45 1.32 4.95L2.05 22l5.25-1.38c1.45.79 3.08 1.21 4.74 1.21 5.46 0 9.91-4.45 9.91-9.91 0-2.65-1.03-5.14-2.9-7.01A9.816 9.816 0 0012.04 2m.01 1.67c2.2 0 4.26.86 5.82 2.42a8.225 8.225 0 012.41 5.83c0 4.54-3.7 8.23-8.24 8.23-1.48 0-2.93-.39-4.19-1.15l-.3-.17-3.12.82.83-3.04-.2-.32a8.188 8.188 0 01-1.26-4.38c.01-4.54 3.7-8.24 8.25-8.24M8.53 7.33c-.16 0-.43.06-.66.31-.22.25-.87.86-.87 2.07 0 1.22.89 2.39 1 2.56.14.17 1.76 2.67 4.25 3.73.59.27 1.05.42 1.41.53.59.19 1.13.16 1.56.1.48-.07 1.46-.6 1.67-1.18.21-.58.21-1.07.15-1.18-.07-.1-.23-.15-.48-.27-.25-.12-1.47-.73-1.69-.81-.23-.08-.37-.12-.56.12-.16.25-.64.81-.78.97-.15.17-.29.19-.53.07-.26-.13-1.06-.39-2-1.23-.74-.66-1.23-1.47-1.38-1.72-.14-.25-.02-.39.11-.5.11-.11.25-.29.37-.44.13-.14.17-.25.25-.41.08-.17.04-.31-.02-.43-.06-.11-.56-1.35-.77-1.84-.2-.48-.4-.42-.56-.43-.14 0-.3-.01-.47-.01z"/>
                        </svg>
                    </a>
                    <a href="#" class="footer__social-link" aria-label="VK">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M12 2.04c-5.5 0-10 4.49-10 10.02 0 5 3.66 9.15 8.44 9.9v-7H7.9v-2.9h2.54V9.85c0-2.51 1.49-3.89 3.78-3.89 1.09 0 2.23.19 2.23.19v2.47h-1.26c-1.24 0-1.63.77-1.63 1.56v1.88h2.78l-.45 2.9h-2.33v7a10 10 0 008.44-9.9c0-5.53-4.5-10.02-10-10.02z"/>
                        </svg>
                    </a>
                </div>
            </div>
        </div>
        
        <!-- Правая часть: Форма + Навигационные колонки -->
        <div class="footer__right">
            <!-- Форма вверху правой части -->
            <div class="footer__form">
                <?php
                $footer_form_shortcode = get_theme_mod('footer_form_shortcode', '');
                
                if ($footer_form_shortcode) {
                    echo do_shortcode($footer_form_shortcode);
                } else {
                    echo '<div class="form-placeholder">Добавьте шорткод в Внешний вид → Настроить → Настройки Asker</div>';
                }
                ?>
            </div>
            
            <!-- Навигационные колонки внизу правой части -->
            <div class="footer__nav">
                <div class="footer__nav-col">
                    <h4 class="footer__title">Меню</h4>
                    <ul class="footer__list">
                        <li><a href="<?php echo esc_url(home_url('/about')); ?>" class="footer__link">О компании</a></li>
                        <li><a href="<?php echo esc_url(home_url('/blog')); ?>" class="footer__link">Блог</a></li>
                        <li><a href="<?php echo esc_url(home_url('/delivery')); ?>" class="footer__link">Оплата и доставка</a></li>
                        <li><a href="<?php echo esc_url(home_url('/contacts')); ?>" class="footer__link">Контакты</a></li>
                    </ul>
                </div>
                
                <div class="footer__nav-col">
                    <h4 class="footer__title">Каталог</h4>
                    <ul class="footer__list">
                        <?php
                        // Проверяем настроенные категории из Customizer
                        $custom_categories = array();
                        for ( $i = 1; $i <= 5; $i++ ) {
                            $cat_id = get_theme_mod( 'footer_category_' . $i );
                            if ( $cat_id ) {
                                $custom_categories[] = $cat_id;
                            }
                        }
                        
                        if ( ! empty( $custom_categories ) && class_exists( 'WooCommerce' ) ) {
                            // Выводим выбранные в Customizer категории
                            foreach ( $custom_categories as $cat_id ) {
                                $category = get_term( $cat_id, 'product_cat' );
                                if ( $category && ! is_wp_error( $category ) ) {
                                    $category_url = get_term_link( $category );
                                    if ( ! is_wp_error( $category_url ) ) {
                                        echo '<li><a href="' . esc_url( $category_url ) . '" class="footer__link">' . esc_html( $category->name ) . '</a></li>';
                                    }
                                }
                            }
                        } elseif ( class_exists( 'WooCommerce' ) ) {
                            // Fallback: автоматически выводим 5 первых категорий
                            $product_categories = get_terms( array(
                                'taxonomy'   => 'product_cat',
                                'hide_empty' => true,
                                'orderby'    => 'menu_order',
                                'order'      => 'ASC',
                                'number'     => 5
                            ) );
                            
                            if ( ! empty( $product_categories ) && ! is_wp_error( $product_categories ) ) {
                                foreach ( $product_categories as $category ) {
                                    $category_url = get_term_link( $category );
                                    if ( ! is_wp_error( $category_url ) ) {
                                        echo '<li><a href="' . esc_url( $category_url ) . '" class="footer__link">' . esc_html( $category->name ) . '</a></li>';
                                    }
                                }
                            }
                        }
                        ?>
                    </ul>
                </div>
                
                <div class="footer__nav-col">
                    <h4 class="footer__title">Покупателям</h4>
                    <ul class="footer__list">
                        <li><a href="<?php echo esc_url(home_url('/delivery')); ?>" class="footer__link">Доставка</a></li>
                        <li><a href="<?php echo esc_url(home_url('/payment')); ?>" class="footer__link">Оплата</a></li>
                        <li><a href="<?php echo esc_url(home_url('/guarantees')); ?>" class="footer__link">Гарантии</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Нижняя часть футера -->
    <div class="footer__bottom">
        <div class="footer__legal">
            <a href="<?php echo esc_url(home_url('/terms')); ?>" class="footer__legal-link">Пользовательское соглашение</a>
            <a href="<?php echo esc_url(home_url('/privacy')); ?>" class="footer__legal-link">Политика конфиденциальности</a>
            <a href="https://joinsite.ru" class="footer__legal-link">Разработка сайта Joinsite</a>
        </div>
    </div>
</footer>

    <!-- Cookie bar -->
    <div id="cookie-bar" role="dialog" aria-live="polite" aria-label="Cookie consent">
        <div id="cookie-bar__text">
            Мы используем файлы cookies. Продолжая пользоваться сайтом, вы соглашаетесь с нашей 
            <a href="<?php echo esc_url(get_privacy_policy_url()); ?>">политикой конфиденциальности</a>.
        </div>
        <button id="cookie-bar__accept">Принять</button>
    </div>

    <?php wp_footer(); ?>
    
    <!-- Скрипт для предотвращения disabled кнопки в футере -->
    <script>
    (function() {
        // Убираем disabled атрибут с кнопки отправки в футере
        function enableFooterSubmitButton() {
            const footerForm = document.querySelector('.footer__form');
            if (!footerForm) return;
            
            const submitButtons = footerForm.querySelectorAll('.wpcf7-submit, input[type="submit"]');
            submitButtons.forEach(function(btn) {
                // Убираем disabled если он есть
                if (btn.hasAttribute('disabled')) {
                    btn.removeAttribute('disabled');
                }
                
                // Убираем aria-disabled
                if (btn.hasAttribute('aria-disabled')) {
                    btn.removeAttribute('aria-disabled');
                }
                
                // Убираем класс disabled если есть
                btn.classList.remove('disabled');
            });
        }
        
        // Выполняем сразу
        enableFooterSubmitButton();
        
        // Выполняем после загрузки DOM
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', enableFooterSubmitButton);
        }
        
        // Отслеживаем изменения формы (Contact Form 7 может добавлять disabled динамически)
        const footerForm = document.querySelector('.footer__form');
        if (footerForm) {
            // Используем MutationObserver для отслеживания изменений
            const observer = new MutationObserver(function(mutations) {
                mutations.forEach(function(mutation) {
                    if (mutation.type === 'attributes' && mutation.attributeName === 'disabled') {
                        enableFooterSubmitButton();
                    }
                });
            });
            
            // Наблюдаем за изменениями атрибутов
            const submitButtons = footerForm.querySelectorAll('.wpcf7-submit, input[type="submit"]');
            submitButtons.forEach(function(btn) {
                observer.observe(btn, {
                    attributes: true,
                    attributeFilter: ['disabled', 'aria-disabled']
                });
            });
            
            // Также отслеживаем изменения через интервал (fallback)
            setInterval(enableFooterSubmitButton, 500);
        }
    })();
    </script>
</body>
</html>
