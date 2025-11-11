<?php
/**
 * Schema.org разметка (JSON-LD)
 * Product, Organization, BreadcrumbList для улучшения SEO
 */

/**
 * Добавляем Schema.org разметку для организации (глобально)
 */
function asker_add_organization_schema() {
    $site_name = get_bloginfo( 'name' );
    $site_url = home_url();
    $site_description = get_bloginfo( 'description' );
    
    // Получаем контактные данные из настроек (можно добавить в ACF)
    $organization_phone = get_field( 'organization_phone', 'option' ) ?: '+7 (812) 123-12-23';
    $organization_email = get_field( 'organization_email', 'option' ) ?: 'info@asker-corp.ru';
    $organization_address = get_field( 'organization_address', 'option' );
    
    $schema = array(
        '@context' => 'https://schema.org',
        '@type' => 'Organization',
        'name' => $site_name,
        'url' => $site_url,
        'description' => $site_description ?: 'Интернет-магазин запчастей для водонагревателей',
        'logo' => wp_get_attachment_image_url( get_theme_mod( 'custom_logo' ), 'full' ) ?: '',
    );
    
    // Контактная информация
    if ( $organization_phone || $organization_email ) {
        $schema['contactPoint'] = array(
            '@type' => 'ContactPoint',
        );
        
        if ( $organization_phone ) {
            $schema['contactPoint']['telephone'] = $organization_phone;
        }
        
        if ( $organization_email ) {
            $schema['contactPoint']['email'] = $organization_email;
        }
        
        $schema['contactPoint']['contactType'] = 'Customer Service';
    }
    
    // Адрес
    if ( $organization_address ) {
        $schema['address'] = array(
            '@type' => 'PostalAddress',
            'addressLocality' => is_array( $organization_address ) ? ( $organization_address['city'] ?? '' ) : $organization_address,
            'addressCountry' => 'RU',
        );
    }
    
    // Социальные сети (если есть в ACF)
    $social_links = get_field( 'social_links', 'option' );
    if ( $social_links && is_array( $social_links ) ) {
        $schema['sameAs'] = array();
        foreach ( $social_links as $link ) {
            if ( ! empty( $link['url'] ) ) {
                $schema['sameAs'][] = $link['url'];
            }
        }
    }
    
    echo '<script type="application/ld+json">' . "\n";
    echo wp_json_encode( $schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT );
    echo "\n" . '</script>' . "\n";
}
add_action( 'wp_head', 'asker_add_organization_schema', 5 );

/**
 * Добавляем Schema.org разметку для товара
 */
function asker_add_product_schema() {
    if ( ! is_product() ) {
        return;
    }
    
    global $product;
    if ( ! $product ) {
        return;
    }
    
    $site_name = get_bloginfo( 'name' );
    $product_id = $product->get_id();
    $product_name = $product->get_name();
    $product_description = $product->get_short_description() ?: wp_trim_words( $product->get_description(), 50 );
    $product_url = get_permalink( $product_id );
    $product_price = $product->get_price();
    $product_sku = $product->get_sku();
    $product_image_id = $product->get_image_id();
    
    // Основная схема товара
    $schema = array(
        '@context' => 'https://schema.org',
        '@type' => 'Product',
        'name' => $product_name,
        'description' => $product_description,
        'url' => $product_url,
        'sku' => $product_sku ?: (string) $product_id,
        'mpn' => $product_sku ?: (string) $product_id,
    );
    
    // Изображения товара
    $images = array();
    if ( $product_image_id ) {
        $main_image = wp_get_attachment_image_url( $product_image_id, 'full' );
        if ( $main_image ) {
            $images[] = $main_image;
        }
    }
    
    // Дополнительные изображения
    $gallery_ids = $product->get_gallery_image_ids();
    foreach ( $gallery_ids as $gallery_id ) {
        $gallery_image = wp_get_attachment_image_url( $gallery_id, 'full' );
        if ( $gallery_image ) {
            $images[] = $gallery_image;
        }
    }
    
    if ( ! empty( $images ) ) {
        $schema['image'] = count( $images ) === 1 ? $images[0] : $images;
    }
    
    // Бренд (если есть атрибут или термин)
    $brand = $product->get_attribute( 'pa_brand' );
    if ( ! $brand ) {
        $brand_terms = wp_get_post_terms( $product_id, 'pa_brand' );
        if ( ! empty( $brand_terms ) && ! is_wp_error( $brand_terms ) ) {
            $brand = $brand_terms[0]->name;
        }
    }
    
    if ( $brand ) {
        $schema['brand'] = array(
            '@type' => 'Brand',
            'name' => $brand,
        );
    } else {
        // Используем название сайта как бренд по умолчанию
        $schema['brand'] = array(
            '@type' => 'Brand',
            'name' => $site_name,
        );
    }
    
    // Цена и валюта
    if ( $product_price ) {
        $schema['offers'] = array(
            '@type' => 'Offer',
            'url' => $product_url,
            'priceCurrency' => 'RUB',
            'price' => number_format( (float) $product_price, 2, '.', '' ),
            'availability' => $product->is_in_stock() ? 'https://schema.org/InStock' : 'https://schema.org/OutOfStock',
            'seller' => array(
                '@type' => 'Organization',
                'name' => $site_name,
            ),
        );
        
        // Если товар на распродаже
        if ( $product->is_on_sale() ) {
            $regular_price = $product->get_regular_price();
            if ( $regular_price ) {
                $schema['offers']['priceSpecification'] = array(
                    '@type' => 'UnitPriceSpecification',
                    'price' => number_format( (float) $product_price, 2, '.', '' ),
                    'priceCurrency' => 'RUB',
                    'referenceQuantity' => array(
                        '@type' => 'QuantitativeValue',
                        'value' => 1,
                        'unitCode' => 'C62', // единица товара
                    ),
                );
            }
        }
    }
    
    // Категории товара
    $categories = wp_get_post_terms( $product_id, 'product_cat' );
    if ( ! empty( $categories ) && ! is_wp_error( $categories ) ) {
        $category_names = array();
        foreach ( $categories as $category ) {
            $category_names[] = $category->name;
        }
        $schema['category'] = implode( ', ', $category_names );
    }
    
    // Рейтинг и отзывы (если включены в WooCommerce)
    if ( $product->get_review_count() > 0 ) {
        $schema['aggregateRating'] = array(
            '@type' => 'AggregateRating',
            'ratingValue' => $product->get_average_rating(),
            'reviewCount' => $product->get_review_count(),
            'bestRating' => 5,
            'worstRating' => 1,
        );
    }
    
    echo '<script type="application/ld+json">' . "\n";
    echo wp_json_encode( $schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT );
    echo "\n" . '</script>' . "\n";
}
add_action( 'wp_head', 'asker_add_product_schema', 5 );

/**
 * Добавляем Schema.org разметку BreadcrumbList для навигации
 */
function asker_add_breadcrumb_schema() {
    // Только для страниц товаров, категорий и обычных страниц
    if ( is_front_page() || is_home() ) {
        return;
    }
    
    $breadcrumbs = array();
    $position = 1;
    
    // Главная страница
    $breadcrumbs[] = array(
        '@type' => 'ListItem',
        'position' => $position++,
        'name' => 'Главная',
        'item' => home_url( '/' ),
    );
    
    // Страница товара
    if ( is_product() ) {
        global $product;
        
        // Каталог
        $shop_page_id = wc_get_page_id( 'shop' );
        if ( $shop_page_id ) {
            $breadcrumbs[] = array(
                '@type' => 'ListItem',
                'position' => $position++,
                'name' => get_the_title( $shop_page_id ),
                'item' => get_permalink( $shop_page_id ),
            );
        }
        
        // Категории товара
        $categories = wp_get_post_terms( get_the_ID(), 'product_cat', array( 'orderby' => 'parent', 'order' => 'ASC' ) );
        if ( ! empty( $categories ) && ! is_wp_error( $categories ) ) {
            foreach ( $categories as $category ) {
                $breadcrumbs[] = array(
                    '@type' => 'ListItem',
                    'position' => $position++,
                    'name' => $category->name,
                    'item' => get_term_link( $category ),
                );
            }
        }
        
        // Товар
        $breadcrumbs[] = array(
            '@type' => 'ListItem',
            'position' => $position++,
            'name' => get_the_title(),
            'item' => get_permalink(),
        );
    }
    // Категория товаров
    elseif ( is_product_category() ) {
        $term = get_queried_object();
        
        // Каталог
        $shop_page_id = wc_get_page_id( 'shop' );
        if ( $shop_page_id ) {
            $breadcrumbs[] = array(
                '@type' => 'ListItem',
                'position' => $position++,
                'name' => get_the_title( $shop_page_id ),
                'item' => get_permalink( $shop_page_id ),
            );
        }
        
        // Категория
        if ( $term ) {
            $breadcrumbs[] = array(
                '@type' => 'ListItem',
                'position' => $position++,
                'name' => $term->name,
                'item' => get_term_link( $term ),
            );
        }
    }
    // Обычная страница
    elseif ( is_page() ) {
        global $post;
        
        // Родительские страницы
        $ancestors = get_post_ancestors( $post->ID );
        if ( ! empty( $ancestors ) ) {
            $ancestors = array_reverse( $ancestors );
            foreach ( $ancestors as $ancestor_id ) {
                $breadcrumbs[] = array(
                    '@type' => 'ListItem',
                    'position' => $position++,
                    'name' => get_the_title( $ancestor_id ),
                    'item' => get_permalink( $ancestor_id ),
                );
            }
        }
        
        // Текущая страница
        $breadcrumbs[] = array(
            '@type' => 'ListItem',
            'position' => $position++,
            'name' => get_the_title(),
            'item' => get_permalink(),
        );
    }
    
    // Если есть хлебные крошки - выводим схему
    if ( count( $breadcrumbs ) > 1 ) {
        $schema = array(
            '@context' => 'https://schema.org',
            '@type' => 'BreadcrumbList',
            'itemListElement' => $breadcrumbs,
        );
        
        echo '<script type="application/ld+json">' . "\n";
        echo wp_json_encode( $schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT );
        echo "\n" . '</script>' . "\n";
    }
}
add_action( 'wp_head', 'asker_add_breadcrumb_schema', 5 );

/**
 * Добавляем Schema.org разметку для списка товаров (CollectionPage)
 */
function asker_add_collection_schema() {
    // Только для страниц каталога и категорий
    if ( ! is_shop() && ! is_product_category() && ! is_product_tag() ) {
        return;
    }
    
    $schema = array(
        '@context' => 'https://schema.org',
        '@type' => 'CollectionPage',
        'name' => wp_get_document_title(),
        'url' => ( is_shop() ? get_permalink( wc_get_page_id( 'shop' ) ) : ( is_product_category() ? get_term_link( get_queried_object() ) : home_url( add_query_arg( array(), $GLOBALS['wp']->request ) ) ) ),
    );
    
    // Для категории добавляем описание
    if ( is_product_category() ) {
        $term = get_queried_object();
        if ( $term && ! empty( $term->description ) ) {
            $schema['description'] = wp_trim_words( $term->description, 30 );
        }
    }
    
    echo '<script type="application/ld+json">' . "\n";
    echo wp_json_encode( $schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT );
    echo "\n" . '</script>' . "\n";
}
add_action( 'wp_head', 'asker_add_collection_schema', 5 );

