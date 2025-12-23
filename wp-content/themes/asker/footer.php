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
            </div>
            
            <div class="footer__social-section">
                <h4 class="footer__title">Мы в соцсетях</h4>
                <div class="footer__social">
                    <a href="https://t.me/Ararat007_7" target="_blank" class="footer__social-link" aria-label="Telegram">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm4.64 6.8c-.15 1.58-.8 5.42-1.13 7.19-.14.75-.42 1-.68 1.03-.58.05-1.02-.38-1.58-.75-.88-.58-1.38-.94-2.23-1.5-.99-.65-.35-1.01.22-1.59.15-.15 2.71-2.48 2.76-2.69a.2.2 0 00-.05-.18c-.06-.05-.14-.03-.21-.02-.09.02-1.49.95-4.22 2.79-.4.27-.76.41-1.08.4-.36-.01-1.04-.2-1.55-.37-.63-.2-1.12-.31-1.08-.66.02-.18.27-.36.74-.55 2.92-1.27 4.86-2.11 5.83-2.51 2.78-1.16 3.35-1.36 3.73-1.36.08 0 .27.02.39.12.1.08.13.19.14.27-.01.06.01.24 0 .38z"/>
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
            <a href="https://join-site.ru" target="_blank" class="footer__legal-link">Разработка сайта Join-Site</a>
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
    
    <!-- Скрытый контейнер с формой для попапа обратной связи -->
    <div id="popup-contact-form-template" style="display: none;">
        <?php
        // Берём шорткод для попапа, если пусто - используем форму из футера
        $popup_form_shortcode = get_theme_mod('popup_form_shortcode', '');
        if (empty($popup_form_shortcode)) {
            $popup_form_shortcode = get_theme_mod('footer_form_shortcode', '');
        }
        
        if ($popup_form_shortcode) {
            echo do_shortcode($popup_form_shortcode);
        }
        ?>
    </div>
    
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
