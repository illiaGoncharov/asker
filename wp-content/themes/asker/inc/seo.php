<?php
/**
 * SEO мета-теги и Open Graph для соцсетей
 */

/**
 * Добавляем Open Graph и базовые SEO мета-теги
 */
function asker_add_seo_meta_tags() {
    // Базовые данные сайта
    $site_name = get_bloginfo( 'name' );
    $site_url = home_url();
    $site_description = get_bloginfo( 'description' );
    
    // Определяем тип страницы и получаем данные
    $og_title = '';
    $og_description = '';
    $og_image = '';
    $og_url = '';
    $og_type = 'website';
    
    // Главная страница
    if ( is_front_page() ) {
        // Проверяем кастомные SEO поля из ACF
        $custom_seo_title = get_field( 'seo_title' );
        $custom_seo_description = get_field( 'seo_description' );
        
        $og_title = $custom_seo_title ?: $site_name;
        $og_description = $custom_seo_description ?: ( $site_description ?: 'Интернет-магазин запчастей для водонагревателей' );
        $og_url = $site_url;
        
        // Пытаемся получить изображение из ACF (если есть hero изображение)
        $hero_image = get_field( 'hero_image' );
        if ( $hero_image ) {
            $og_image = is_array( $hero_image ) ? $hero_image['url'] : wp_get_attachment_image_url( $hero_image, 'large' );
        }
    }
    // Страница товара
    elseif ( is_product() ) {
        global $product;
        if ( $product ) {
            $og_title = $product->get_name() . ' — ' . $site_name;
            $og_description = $product->get_short_description() ?: wp_trim_words( $product->get_description(), 20 );
            $og_url = get_permalink( $product->get_id() );
            $og_type = 'product';
            
            // Изображение товара
            $image_id = $product->get_image_id();
            if ( $image_id ) {
                $og_image = wp_get_attachment_image_url( $image_id, 'large' );
            }
            
            // Добавляем мета для цены товара
            $price = $product->get_price();
            if ( $price ) {
                echo '<meta property="product:price:amount" content="' . esc_attr( $price ) . '" />' . "\n";
                echo '<meta property="product:price:currency" content="RUB" />' . "\n";
            }
        }
    }
    // Категория товаров
    elseif ( is_product_category() ) {
        $term = get_queried_object();
        if ( $term ) {
            $og_title = $term->name . ' — ' . $site_name;
            $og_description = $term->description ?: 'Категория товаров ' . $term->name;
            $og_url = get_term_link( $term );
            $og_type = 'website';
            
            // Изображение категории
            $image_id = get_term_meta( $term->term_id, 'thumbnail_id', true );
            if ( $image_id ) {
                $og_image = wp_get_attachment_image_url( $image_id, 'large' );
            }
        }
    }
    // Обычная страница
    elseif ( is_page() ) {
        global $post;
        $og_title = get_the_title() . ' — ' . $site_name;
        $og_description = wp_trim_words( get_the_excerpt() ?: get_the_content(), 25 );
        $og_url = get_permalink();
        
        // Изображение страницы
        if ( has_post_thumbnail() ) {
            $og_image = get_the_post_thumbnail_url( get_the_ID(), 'large' );
        }
    }
    // Запись блога (если есть)
    elseif ( is_single() ) {
        $og_title = get_the_title() . ' — ' . $site_name;
        $og_description = wp_trim_words( get_the_excerpt() ?: get_the_content(), 25 );
        $og_url = get_permalink();
        
        if ( has_post_thumbnail() ) {
            $og_image = get_the_post_thumbnail_url( get_the_ID(), 'large' );
        }
    }
    
    // Если нет изображения - используем логотип или дефолтное
    if ( ! $og_image ) {
        $custom_logo_id = get_theme_mod( 'custom_logo' );
        if ( $custom_logo_id ) {
            $og_image = wp_get_attachment_image_url( $custom_logo_id, 'large' );
        } else {
            // Дефолтное изображение (можно заменить на реальное)
            $og_image = $site_url . '/wp-content/themes/asker/assets/images/og-default.jpg';
        }
    }
    
    // Убеждаемся, что URL изображения полный
    if ( $og_image && ! preg_match( '/^https?:\/\//', $og_image ) ) {
        $og_image = $site_url . $og_image;
    }
    
    // Если нет описания - используем дефолтное
    if ( ! $og_description ) {
        $og_description = 'Интернет-магазин запчастей для водонагревателей. Широкий ассортимент, быстрая доставка по Санкт-Петербургу и России.';
    }
    
    // Если нет заголовка - используем название сайта
    if ( ! $og_title ) {
        $og_title = $site_name;
    }
    
    // Если нет URL - используем текущий
    if ( ! $og_url ) {
        $og_url = home_url( add_query_arg( array(), $GLOBALS['wp']->request ) );
        // Если все еще пусто, используем текущий запрос
        if ( ! $og_url ) {
            $og_url = ( is_ssl() ? 'https://' : 'http://' ) . ( isset( $_SERVER['HTTP_HOST'] ) ? esc_url_raw( $_SERVER['HTTP_HOST'] ) : '' ) . ( isset( $_SERVER['REQUEST_URI'] ) ? esc_url_raw( $_SERVER['REQUEST_URI'] ) : '' );
        }
    }
    
    // Выводим Open Graph теги
    echo "\n<!-- Open Graph мета-теги -->\n";
    echo '<meta property="og:title" content="' . esc_attr( $og_title ) . '" />' . "\n";
    echo '<meta property="og:description" content="' . esc_attr( $og_description ) . '" />' . "\n";
    echo '<meta property="og:image" content="' . esc_url( $og_image ) . '" />' . "\n";
    echo '<meta property="og:url" content="' . esc_url( $og_url ) . '" />' . "\n";
    echo '<meta property="og:type" content="' . esc_attr( $og_type ) . '" />' . "\n";
    echo '<meta property="og:site_name" content="' . esc_attr( $site_name ) . '" />' . "\n";
    echo '<meta property="og:locale" content="ru_RU" />' . "\n";
    
    // Twitter Card теги
    echo "\n<!-- Twitter Card мета-теги -->\n";
    echo '<meta name="twitter:card" content="summary_large_image" />' . "\n";
    echo '<meta name="twitter:title" content="' . esc_attr( $og_title ) . '" />' . "\n";
    echo '<meta name="twitter:description" content="' . esc_attr( $og_description ) . '" />' . "\n";
    echo '<meta name="twitter:image" content="' . esc_url( $og_image ) . '" />' . "\n";
    
    // Дополнительные мета-теги
    echo "\n<!-- Дополнительные SEO мета-теги -->\n";
    echo '<meta name="description" content="' . esc_attr( $og_description ) . '" />' . "\n";
    echo '<link rel="canonical" href="' . esc_url( $og_url ) . '" />' . "\n";
}
add_action( 'wp_head', 'asker_add_seo_meta_tags', 1 );

/**
 * Улучшаем title для страниц
 */
function asker_improve_page_title( $title ) {
    // Для товаров добавляем цену в title (опционально)
    if ( is_product() ) {
        global $product;
        if ( $product && $product->get_price() ) {
            $price = wc_price( $product->get_price() );
            $title = $title . ' — ' . strip_tags( $price );
        }
    }
    
    return $title;
}
add_filter( 'wp_title', 'asker_improve_page_title', 10, 1 );
add_filter( 'document_title_parts', function( $title_parts ) {
    if ( is_product() ) {
        global $product;
        if ( $product && $product->get_price() ) {
            $price = wc_price( $product->get_price() );
            $title_parts['title'] .= ' — ' . strip_tags( $price );
        }
    }
    return $title_parts;
} );

