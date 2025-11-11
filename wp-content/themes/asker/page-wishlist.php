<?php
/**
 * Template Name: Избранное
 */

get_header(); ?>

<div class="container">
    <!-- Хлебные крошки -->
    <nav class="breadcrumbs" aria-label="Breadcrumb">
        <a href="<?php echo esc_url(home_url('/')); ?>">Главная</a>
        <span class="breadcrumbs__separator">/</span>
        <span class="breadcrumbs__current">Избранное</span>
    </nav>
    
    <div class="wishlist-page">
        <h1 class="page-title">Избранное</h1>
        
        <div class="wishlist-products">
            <div class="wishlist-loading">Загрузка избранного...</div>
        </div>
    </div>
</div>

<script>
jQuery(document).ready(function($) {
    const $wishlistContainer = $('.wishlist-products');
    
    // Функция загрузки избранного с сервера
    function loadWishlistFromServer() {
        if (typeof asker_ajax === 'undefined') {
            renderWishlistFromLocalStorage();
            return;
        }
        
        // Получаем список ID из localStorage
        const favorites = JSON.parse(localStorage.getItem('favorites') || '[]');
        const productIds = favorites.map(id => parseInt(id, 10)).filter(id => !isNaN(id));
        
        // Используем AJAX endpoint для получения HTML
        $.ajax({
            url: asker_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'asker_get_wishlist_products',
                product_ids: productIds
            },
            success: function(response) {
                if (response.success && response.data && response.data.html) {
                    $wishlistContainer.html(response.data.html);
                    
                    // Обновляем состояние кнопок лайков после загрузки
                    setTimeout(function() {
                        $('.favorite-btn').each(function() {
                            const productId = parseInt($(this).attr('data-product-id'));
                            if (productIds.includes(productId)) {
                                $(this).addClass('active');
                            }
                        });
                    }, 100);
                } else {
                    renderWishlistFromLocalStorage();
                }
            },
            error: function() {
                renderWishlistFromLocalStorage();
            }
        });
    }
    
    // Функция рендеринга избранного из localStorage (fallback)
    function renderWishlistFromLocalStorage() {
        const favorites = JSON.parse(localStorage.getItem('favorites') || '[]');
        
        if (favorites.length === 0) {
            $wishlistContainer.html('<div class="no-products"><p>В вашем избранном пока нет товаров.</p><a href="' + (window.location.origin || '') + '/shop" class="btn-primary">Перейти в каталог</a></div>');
            return;
        }
        
        // Пробуем загрузить через AJAX
        const productIds = favorites.map(id => parseInt(id, 10)).filter(id => !isNaN(id));
        
        if (productIds.length > 0 && typeof asker_ajax !== 'undefined') {
            $.ajax({
                url: asker_ajax.ajax_url,
                type: 'POST',
                data: {
                    action: 'asker_get_wishlist_products',
                    product_ids: productIds
                },
                success: function(response) {
                    if (response.success && response.data && response.data.html) {
                        $wishlistContainer.html(response.data.html);
                    } else {
                        showEmptyMessage();
                    }
                },
                error: function() {
                    showEmptyMessage();
                }
            });
        } else {
            showEmptyMessage();
        }
    }
    
    function showEmptyMessage() {
        $wishlistContainer.html('<div class="no-products"><p>В вашем избранном пока нет товаров.</p><a href="' + (window.location.origin || '') + '/shop" class="btn-primary">Перейти в каталог</a></div>');
    }
    
    // Загружаем избранное при загрузке страницы
    loadWishlistFromServer();
    
    // Слушаем изменения localStorage для автообновления
    window.addEventListener('storage', function(e) {
        if (e.key === 'favorites') {
            loadWishlistFromServer();
        }
    });
});
</script>

<?php get_footer(); ?>
