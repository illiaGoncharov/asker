<?php
/**
 * Оптимизация изображений
 * Lazy loading и улучшенная загрузка изображений
 */

/**
 * Добавляем loading="lazy" ко всем изображениям по умолчанию
 * Исключения: первое изображение товара, логотип, изображения выше fold
 */
function asker_add_lazy_loading_to_images( $attr, $attachment, $size ) {
    // Не добавляем lazy для логотипа
    if ( isset( $attr['class'] ) && strpos( $attr['class'], 'custom-logo' ) !== false ) {
        return $attr;
    }
    
    // Не добавляем lazy если уже указан loading
    if ( isset( $attr['loading'] ) ) {
        return $attr;
    }
    
    // Для изображений товаров в каталоге - lazy
    if ( is_shop() || is_product_category() || is_product_tag() || is_archive() ) {
        $attr['loading'] = 'lazy';
        $attr['decoding'] = 'async';
    }
    // Для главной страницы - lazy для всех кроме hero
    elseif ( is_front_page() ) {
        // Проверяем, не является ли это hero изображением
        if ( isset( $attr['class'] ) && strpos( $attr['class'], 'hero' ) === false ) {
            $attr['loading'] = 'lazy';
            $attr['decoding'] = 'async';
        }
    }
    // Для остальных страниц - lazy по умолчанию
    else {
        $attr['loading'] = 'lazy';
        $attr['decoding'] = 'async';
    }
    
    return $attr;
}
add_filter( 'wp_get_attachment_image_attributes', 'asker_add_lazy_loading_to_images', 10, 3 );

/**
 * Добавляем lazy loading к изображениям WooCommerce товаров в каталоге
 */
function asker_add_lazy_to_woocommerce_images( $html, $post_id, $post_thumbnail_id, $size, $attr ) {
    // Только для каталога и категорий
    if ( ! is_shop() && ! is_product_category() && ! is_product_tag() && ! is_archive() ) {
        return $html;
    }
    
    // Если уже есть loading - не трогаем
    if ( strpos( $html, 'loading=' ) !== false ) {
        return $html;
    }
    
    // Добавляем loading="lazy" и decoding="async"
    $html = str_replace( '<img ', '<img loading="lazy" decoding="async" ', $html );
    
    return $html;
}
add_filter( 'post_thumbnail_html', 'asker_add_lazy_to_woocommerce_images', 10, 5 );

/**
 * Для галереи товара: первое изображение - eager, остальные - lazy
 */
function asker_optimize_product_gallery_images( $html, $attachment_id, $size, $main_image ) {
    // Только на странице товара
    if ( ! is_product() ) {
        return $html;
    }
    
    // Если это главное изображение - eager (загружаем сразу)
    if ( $main_image ) {
        $html = str_replace( '<img ', '<img loading="eager" ', $html );
    } else {
        // Для миниатюр галереи - lazy
        if ( strpos( $html, 'loading=' ) === false ) {
            $html = str_replace( '<img ', '<img loading="lazy" decoding="async" ', $html );
        }
    }
    
    return $html;
}
add_filter( 'woocommerce_single_product_image_thumbnail_html', 'asker_optimize_product_gallery_images', 10, 4 );

/**
 * Добавляем fetchpriority="high" для первого изображения товара
 */
function asker_add_fetchpriority_to_main_image( $html, $post_id, $post_thumbnail_id, $size, $attr ) {
    // Только на странице товара
    if ( ! is_product() ) {
        return $html;
    }
    
    // Проверяем, что это главное изображение товара
    global $product;
    if ( $product && $product->get_image_id() == $post_thumbnail_id ) {
        // Добавляем fetchpriority="high" для первого изображения
        if ( strpos( $html, 'fetchpriority=' ) === false ) {
            $html = str_replace( '<img ', '<img fetchpriority="high" ', $html );
        }
    }
    
    return $html;
}
add_filter( 'post_thumbnail_html', 'asker_add_fetchpriority_to_main_image', 10, 5 );

/**
 * Добавляем width и height атрибуты для предотвращения layout shift
 */
function asker_add_image_dimensions( $attr, $attachment, $size ) {
    // Если размеры уже есть - не трогаем
    if ( isset( $attr['width'] ) && isset( $attr['height'] ) ) {
        return $attr;
    }
    
    // Получаем размеры изображения
    $image_meta = wp_get_attachment_metadata( $attachment->ID );
    if ( $image_meta ) {
        // Определяем размеры для указанного размера
        $image_size = is_array( $size ) ? $size : image_get_intermediate_size( $attachment->ID, $size );
        
        if ( $image_size ) {
            $attr['width'] = $image_size['width'];
            $attr['height'] = $image_size['height'];
        } elseif ( isset( $image_meta['width'] ) && isset( $image_meta['height'] ) ) {
            // Используем оригинальные размеры
            $attr['width'] = $image_meta['width'];
            $attr['height'] = $image_meta['height'];
        }
    }
    
    return $attr;
}
add_filter( 'wp_get_attachment_image_attributes', 'asker_add_image_dimensions', 10, 3 );

/**
 * Добавляем srcset и sizes для адаптивных изображений (если еще нет)
 */
function asker_ensure_responsive_images( $html, $post_id, $post_thumbnail_id, $size, $attr ) {
    // Если уже есть srcset - не трогаем
    if ( strpos( $html, 'srcset=' ) !== false ) {
        return $html;
    }
    
    // Добавляем srcset через wp_get_attachment_image (если возможно)
    if ( $post_thumbnail_id ) {
        $image = wp_get_attachment_image( $post_thumbnail_id, $size, false, $attr );
        if ( $image ) {
            return $image;
        }
    }
    
    return $html;
}
add_filter( 'post_thumbnail_html', 'asker_ensure_responsive_images', 20, 5 );

/**
 * Оптимизация изображений в контенте (для обычных страниц и постов)
 */
function asker_optimize_content_images( $content ) {
    // Только для контента страниц и постов
    if ( ! is_singular() ) {
        return $content;
    }
    
    // Находим все изображения в контенте
    preg_match_all( '/<img[^>]+>/i', $content, $matches );
    
    foreach ( $matches[0] as $img_tag ) {
        // Если уже есть loading - пропускаем
        if ( strpos( $img_tag, 'loading=' ) !== false ) {
            continue;
        }
        
        // Добавляем lazy loading
        $new_img_tag = str_replace( '<img ', '<img loading="lazy" decoding="async" ', $img_tag );
        $content = str_replace( $img_tag, $new_img_tag, $content );
    }
    
    return $content;
}
add_filter( 'the_content', 'asker_optimize_content_images', 99 );

/**
 * Добавляем preload для критически важных изображений (hero, первое изображение товара)
 */
function asker_preload_critical_images() {
    // Hero изображение на главной
    if ( is_front_page() ) {
        $hero_image = get_field( 'hero_image' );
        if ( $hero_image ) {
            $image_url = is_array( $hero_image ) ? $hero_image['url'] : wp_get_attachment_image_url( $hero_image, 'large' );
            if ( $image_url ) {
                echo '<link rel="preload" as="image" href="' . esc_url( $image_url ) . '">' . "\n";
            }
        }
    }
    
    // Первое изображение товара
    if ( is_product() ) {
        global $product;
        if ( $product ) {
            $image_id = $product->get_image_id();
            if ( $image_id ) {
                $image_url = wp_get_attachment_image_url( $image_id, 'woocommerce_single' );
                if ( $image_url ) {
                    echo '<link rel="preload" as="image" href="' . esc_url( $image_url ) . '">' . "\n";
                }
            }
        }
    }
}
add_action( 'wp_head', 'asker_preload_critical_images', 1 );

