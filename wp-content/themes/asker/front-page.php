<?php
/**
 * Главная страница: hero, категории, товары.
 * Данные берутся из ACF (группа «Главная»).
 */

// ВРЕМЕННАЯ ДИАГНОСТИКА - проверяем, что front-page.php загружается
if ( ! defined( 'WP_USE_THEMES' ) || ! WP_USE_THEMES ) {
    return;
}

get_header();
?>

<?php
// Определяем URL страницы со всеми категориями
$asker_categories_url = home_url('/categories');

// Безопасное получение URL страницы категорий с проверками
if (function_exists('get_page_by_path') && function_exists('home_url')) {
    // 1. Сначала ищем страницу по слагу 'categories'
    $asker_categories_page = get_page_by_path('categories');
    if ($asker_categories_page && isset($asker_categories_page->post_status) && $asker_categories_page->post_status === 'publish') {
        $permalink = get_permalink($asker_categories_page->ID);
        if ($permalink && !is_wp_error($permalink)) {
            $asker_categories_url = $permalink;
        }
    } else {
        // 2. Ищем страницу по шаблону 'page-categories.php'
        if (function_exists('get_pages')) {
            $pages_with_template = get_pages(array(
                'meta_key'   => '_wp_page_template',
                'meta_value' => 'page-categories.php',
                'number'     => 1,
                'post_status' => 'publish'
            ));
            if (!empty($pages_with_template) && is_array($pages_with_template) && isset($pages_with_template[0])) {
                $permalink = get_permalink($pages_with_template[0]->ID);
                if ($permalink && !is_wp_error($permalink)) {
                    $asker_categories_url = $permalink;
                }
            } else {
                // 3. Фолбэк на каталог
                $asker_categories_url = home_url('/shop');
            }
        } else {
            // 3. Фолбэк на каталог
            $asker_categories_url = home_url('/shop');
        }
    }
}
?>

<!-- Навигация по категориям -->
<section class="categories-nav">
    <div class="categories-grid">
        <?php
        // Получаем категории товаров WooCommerce для главной страницы
        // Показываем первые 5 категорий или все, если меньше 5
        if (class_exists('WooCommerce')) {
            $product_categories = get_terms(array(
                'taxonomy'   => 'product_cat',
                'hide_empty' => true, // Показываем только с товарами на главной
                'orderby'    => 'menu_order',
                'order'      => 'ASC',
                'number'     => 5 // Максимум 5 на главной
            ));
            
            if (!empty($product_categories) && !is_wp_error($product_categories)) {
                foreach ($product_categories as $category) {
                    $category_url = get_term_link($category);
                    if (is_wp_error($category_url)) {
                        continue;
                    }
                    
                    // Получаем иконку категории из стандартного поля WooCommerce "Thumbnail"
                    $thumbnail_id = get_term_meta($category->term_id, 'thumbnail_id', true);
                    $icon_url = '';
                    
                    if ($thumbnail_id) {
                        // Проверяем тип файла для корректной обработки SVG
                        $mime_type = get_post_mime_type($thumbnail_id);
                        
                        if ($mime_type === 'image/svg+xml') {
                            // Для SVG используем прямой URL
                            $icon_url = wp_get_attachment_url($thumbnail_id);
                        } else {
                            // Для обычных изображений используем размер medium
                            $icon_url = wp_get_attachment_image_url($thumbnail_id, 'medium');
                            if (!$icon_url) {
                                // Если нет medium, используем полный размер
                                $icon_url = wp_get_attachment_url($thumbnail_id);
                            }
                        }
                    }
                    
                    // Fallback на иконки по названию категории (независимо от слага)
                    if (!$icon_url) {
                        $category_name_lower = mb_strtolower($category->name);
                        $category_slug_lower = mb_strtolower($category->slug);
                        
                        // Проверяем по названию и слагу
                        if (stripos($category_name_lower, 'тэн') !== false || 
                            stripos($category_name_lower, 'водонагревател') !== false ||
                            stripos($category_slug_lower, 'heaters') !== false ||
                            stripos($category_slug_lower, 'ten') !== false) {
                            $icon_url = get_template_directory_uri() . '/assets/images/hero/tens.svg';
                        } elseif (stripos($category_name_lower, 'анод') !== false ||
                                  stripos($category_slug_lower, 'anod') !== false) {
                            $icon_url = get_template_directory_uri() . '/assets/images/hero/anods.svg';
                        } elseif (stripos($category_name_lower, 'термостат') !== false ||
                                  stripos($category_slug_lower, 'thermostat') !== false) {
                            $icon_url = get_template_directory_uri() . '/assets/images/hero/termostats.svg';
                        } elseif (stripos($category_name_lower, 'стирал') !== false ||
                                  stripos($category_slug_lower, 'washer') !== false) {
                            $icon_url = get_template_directory_uri() . '/assets/images/hero/washers.svg';
                        } elseif (stripos($category_name_lower, 'холодил') !== false ||
                                  stripos($category_slug_lower, 'freezer') !== false) {
                            $icon_url = get_template_directory_uri() . '/assets/images/hero/freezers.svg';
                        }
                    }
                    
                    // Если всё ещё нет иконки, используем заглушку
                    if (!$icon_url) {
                        $icon_url = get_template_directory_uri() . '/assets/images/hero/tens.svg';
                    }
                    ?>
                    <a href="<?php echo esc_url($category_url); ?>" class="category-item">
                        <div class="category-icon">
                            <img src="<?php echo esc_url($icon_url); ?>" alt="<?php echo esc_attr($category->name); ?>">
                        </div>
                        <span class="category-name"><?php echo esc_html($category->name); ?></span>
                    </a>
                    <?php
                }
            }
        }
        ?>
        
        <a href="<?php echo esc_url($asker_categories_url); ?>" class="all-categories">
            <span class="all-categories-text">Все категории</span>
            <span class="all-categories-arrow">→</span>
        </a>
    </div>
</section>

<?php
// ACF поля (Free): безопасно получаем без фаталов, если ACF не установлен
$hero_title       = function_exists('get_field') ? (string) get_field('hero_title') : '';
$hero_subtitle    = function_exists('get_field') ? (string) get_field('hero_subtitle') : '';
$hero_cta_text    = function_exists('get_field') ? (string) get_field('hero_cta_text') : '';
$hero_cta_link    = function_exists('get_field') ? (string) get_field('hero_cta_link') : '';
$hero_cta2_text   = function_exists('get_field') ? (string) get_field('hero_cta2_text') : '';
$hero_cta2_link   = function_exists('get_field') ? (string) get_field('hero_cta2_link') : '';
$hero_image       = function_exists('get_field') ? get_field('hero_image') : null; // массив ACF image
$featured_cats    = function_exists('get_field') ? asker_to_array(get_field('featured_categories')) : [];
$featured_products= function_exists('get_field') ? asker_to_array(get_field('featured_products')) : [];
$contact_form_shortcode = function_exists('get_field') ? (string) get_field('contact_form_shortcode') : '';

?>

<!-- Hero секция -->
<section class="hero-section">
    <div class="container">
        <div class="hero-content">
            <div class="hero-text">
                <?php if ($hero_title) : ?>
                    <h1 class="hero-title"><?php echo esc_html($hero_title); ?></h1>
                <?php else : ?>
                    <h1 class="hero-title">Комплектующие для бытовой техники</h1>
                <?php endif; ?>
                
                <?php if ($hero_subtitle) : ?>
                    <p class="hero-subtitle"><?php echo esc_html($hero_subtitle); ?></p>
                <?php else : ?>
                    <p class="hero-subtitle">Широкий ассортимент запчастей и комплектующих для юридических лиц. Гарантия качества, быстрая доставка, специальные условия для оптовиков.</p>
                <?php endif; ?>
                
                <div class="hero-buttons">
                    <?php if ($hero_cta_text && $hero_cta_link) : ?>
                        <a class="btn btn--secondary" href="<?php echo esc_url($hero_cta_link); ?>"><?php echo esc_html($hero_cta_text); ?></a>
                    <?php else : ?>
                        <a class="btn btn--secondary" href="<?php echo esc_url(home_url('/shop')); ?>">Оформить заказ</a>
                    <?php endif; ?>
                    <?php if ($hero_cta2_text && $hero_cta2_link) : ?>
                        <a class="btn btn--outline" href="<?php echo esc_url($hero_cta2_link); ?>"><?php echo esc_html($hero_cta2_text); ?></a>
                    <?php else : ?>
                        <a class="btn btn--outline" href="<?php echo esc_url(home_url('/contact')); ?>">Получить скидку</a>
                    <?php endif; ?>
                </div>
            </div>
            <div class="hero-image">
                <?php if (!empty($hero_image['ID'])) : ?>
                    <?php echo wp_get_attachment_image((int) $hero_image['ID'], 'large'); ?>
                <?php else : ?>
                    <img src="https://via.placeholder.com/500x400/e5e7eb/1a1a1a?text=Склад+запчастей" alt="Склад запчастей">
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<!-- Популярные товары -->
<section class="products-section">
    <div class="container">
        <h2 class="section-title">Популярные товары</h2>
        <div class="products-grid">
            <?php
            // Получаем выбранные товары из ACF
            $featured_products = function_exists('get_field') ? get_field('featured_products') : false;
            
            if ($featured_products && is_array($featured_products)) :
                foreach ($featured_products as $product_id) :
                    $product = wc_get_product($product_id);
                    if ($product) :
                        $product_image = wp_get_attachment_image_src(get_post_thumbnail_id($product_id), 'medium');
                        $product_url = get_permalink($product_id);
                        $price = $product->get_price_html();
                        // Убираем копейки из цены
                        $price = preg_replace('/,00/', '', $price);
            ?>
                <div class="product-card">
                    <button class="favorite-btn" data-product-id="<?php echo esc_attr($product_id); ?>"></button>
                    <a href="<?php echo esc_url($product_url); ?>">
                        <?php if ($product_image) : ?>
                            <img class="product-image" src="<?php echo esc_url($product_image[0]); ?>" alt="<?php echo esc_attr($product->get_name()); ?>">
                        <?php else : ?>
                            <div class="product-placeholder"><?php echo esc_html($product->get_name()); ?></div>
                        <?php endif; ?>
                    </a>
                    <h3 class="product-title">
                        <a href="<?php echo esc_url($product_url); ?>"><?php echo esc_html($product->get_name()); ?></a>
                    </h3>
                    <div class="product-bottom">
                        <div class="product-price"><?php echo $price; ?></div>
                        <button class="btn-add-cart" data-product-id="<?php echo esc_attr($product_id); ?>">В корзину</button>
                    </div>
                </div>
            <?php
                    endif;
                endforeach;
            else :
                // Fallback - показываем тестовые товары для отладки
                $test_products = [55, 56, 57, 58]; // ID товаров из базы данных
                foreach ($test_products as $product_id) :
                    $product = wc_get_product($product_id);
                    if ($product) :
                        $product_image = wp_get_attachment_image_src(get_post_thumbnail_id($product_id), 'medium');
                        $product_url = get_permalink($product_id);
                        $price = $product->get_price_html();
                        // Убираем копейки из цены
                        $price = preg_replace('/,00/', '', $price);
            ?>
                <div class="product-card">
                    <button class="favorite-btn" data-product-id="<?php echo esc_attr($product_id); ?>"></button>
                    <a href="<?php echo esc_url($product_url); ?>">
                        <?php if ($product_image) : ?>
                            <img class="product-image" src="<?php echo esc_url($product_image[0]); ?>" alt="<?php echo esc_attr($product->get_name()); ?>">
                        <?php else : ?>
                            <div class="product-placeholder"><?php echo esc_html($product->get_name()); ?></div>
                        <?php endif; ?>
                    </a>
                    <h3 class="product-title">
                        <a href="<?php echo esc_url($product_url); ?>"><?php echo esc_html($product->get_name()); ?></a>
                    </h3>
                    <div class="product-bottom">
                        <div class="product-price"><?php echo $price; ?></div>
                        <button class="btn-add-cart" data-product-id="<?php echo esc_attr($product_id); ?>">В корзину</button>
                    </div>
                </div>
            <?php
                    endif;
                endforeach;
            endif; ?>
        </div>
        <a href="<?php echo esc_url(home_url('/shop')); ?>" class="btn-view-all">Посмотреть все</a>
    </div>
</section>

<!-- Способы доставки -->
<section class="delivery-section">
    <div class="container">
        <h2 class="section-title">Способы доставки</h2>
        <p class="section-subtitle">Выберите удобный для вас способ получения заказа</p>
        <div class="delivery-grid">
            <div class="delivery-card">
                <div class="delivery-icon">
                    <img src="<?php echo get_template_directory_uri(); ?>/assets/images/delivery/cargo.svg" alt="Транспортная компания">
                </div>
                <h3 class="delivery-title">Транспортная компания</h3>
                <p class="delivery-description">Доставка по всей России через ТК 2-7 дней</p>
                <div class="delivery-price">от 500 Р</div>
            </div>
            <div class="delivery-card">
                <div class="delivery-icon">
                    <img src="<?php echo get_template_directory_uri(); ?>/assets/images/delivery/courier.svg" alt="Курьерская доставка">
                </div>
                <h3 class="delivery-title">Курьерская доставка</h3>
                <p class="delivery-description">Доставка курьером по Санкт-Петербургу</p>
                <div class="delivery-price">от 500 Р</div>
            </div>
            <div class="delivery-card">
                <div class="delivery-icon">
                    <img src="<?php echo get_template_directory_uri(); ?>/assets/images/delivery/map.svg" alt="Самовывоз">
                </div>
                <h3 class="delivery-title">Самовывоз</h3>
                <p class="delivery-description">Забрать самостоятельно из офиса/склада</p>
                <div class="delivery-price">Бесплатно</div>
            </div>
        </div>
    </div>
</section>

<!-- О компании -->
<section class="about-section">
    <div class="container">
        <div class="about-content">
            <div class="about-text">
                <h2>О нашей компании</h2>
                <p>Asker - российская компания, специализирующаяся на производстве и поставке нагревательных элементов и комплектующих для бытового и промышленного оборудования. Работаем с 2022 года.</p>
                <p>Мы предлагаем широкий ассортимент качественных запчастей: нагревательные элементы, термостаты, прокладки и другие комплектующие.</p>
                <p>Более 500 довольных клиентов по всей России</p>
                
                <div class="about-features">
                    <div class="feature-item">
                        <div class="feature-icon">
                            <img src="<?php echo get_template_directory_uri(); ?>/assets/images/about/shield.svg" alt="Гарантия качества">
                        </div>
                        <div class="feature-content">
                            <h4 class="feature-title">Гарантия качества</h4>
                            <p class="feature-text">Гарантия на все товары и сервисная поддержка</p>
                        </div>
                    </div>
                    <div class="feature-item">
                        <div class="feature-icon">
                            <img src="<?php echo get_template_directory_uri(); ?>/assets/images/about/clock.svg" alt="Быстрая доставка">
                        </div>
                        <div class="feature-content">
                            <h4 class="feature-title">Быстрая доставка</h4>
                            <p class="feature-text">Собственный склад в Санкт-Петербурге и оперативная доставка</p>
                        </div>
                    </div>
                    <div class="feature-item">
                        <div class="feature-icon">
                            <img src="<?php echo get_template_directory_uri(); ?>/assets/images/about/headset.svg" alt="Техподдержка">
                        </div>
                        <div class="feature-content">
                            <h4 class="feature-title">Техподдержка</h4>
                            <p class="feature-text">Консультации по подбору запчастей и технической совместимости</p>
                        </div>
                    </div>
                    <div class="feature-item">
                        <div class="feature-icon">
                            <img src="<?php echo get_template_directory_uri(); ?>/assets/images/about/tag.svg" alt="Оптовые цены">
                        </div>
                        <div class="feature-content">
                            <h4 class="feature-title">Оптовые цены</h4>
                            <p class="feature-text">Прямые поставки от проверенных производителей</p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="about-image">
                <?php 
                $about_image = function_exists('get_field') ? get_field('about_image') : null;
                if (!empty($about_image['ID'])) : 
                ?>
                    <?php echo wp_get_attachment_image((int) $about_image['ID'], 'large'); ?>
                <?php else : ?>
                    <img src="https://via.placeholder.com/500x400/e5e7eb/1a1a1a?text=Склад+компании" alt="Склад компании">
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<!-- Для оптовых клиентов -->
<section class="wholesale-section">
    <div class="container">
        <div class="wholesale-content">
            <div class="wholesale-text">
                <h2>Для оптовых клиентов</h2>
                <p>Мы ценим долгосрочное сотрудничество и предлагаем специальные условия для оптовых покупателей, компаний и мастерских.</p>
                <ul class="wholesale-benefits">
                    <li>Индивидуальные цены в зависимости от объема закупок и регулярности заказов</li>
                    <li>Персональный менеджер для поддержки сделок, подбора продукции и решения организационных вопросов</li>
                    <li>Гибкие условия оплаты: безналичный расчет с отсрочкой платежа для постоянных партнеров</li>
                    <li>Приоритетная отгрузка: ускоренная обработка заказов и комплектация, резервирование товара на складе</li>
                    <li>Информационная поддержка: уведомления о наличии, новинках, акциях, рассылка каталогов и прайс-листов</li>
                </ul>
            </div>
            <div class="wholesale-form">
                <h3>Оставить заявку</h3>
                <?php if ($contact_form_shortcode) : ?>
                    <?php echo do_shortcode($contact_form_shortcode); ?>
                <?php else : ?>
                    <p class="form-placeholder">
                        Добавьте шорткод формы в ACF главной страницы
                    </p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>


<?php get_footer(); ?>



