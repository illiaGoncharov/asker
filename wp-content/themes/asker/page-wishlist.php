<?php
/**
 * Template Name: Избранное
 */

get_header(); ?>

<div class="container">
    <div class="wishlist-page">
        <h1>Избранное</h1>
        
        <div id="wishlist-content">
            <div class="wishlist-loading">Загрузка избранного...</div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const wishlistContent = document.getElementById('wishlist-content');
    
    function renderWishlist() {
        const favorites = JSON.parse(localStorage.getItem('favorites') || '[]');
        console.log('Rendering wishlist:', favorites);
        
        if (favorites.length === 0) {
            wishlistContent.innerHTML = '<div class="empty-wishlist">Избранное пусто</div>';
            return;
        }
        
        let html = '<div class="wishlist-items">';
        
        favorites.forEach(productId => {
            html += `
                <div class="wishlist-item" data-product-id="${productId}">
                    <div class="wishlist-item-info">
                        <h3>Товар ID: ${productId}</h3>
                        <p>Товар добавлен в избранное</p>
                    </div>
                    <div class="wishlist-item-actions">
                        <button class="btn-remove-favorite" data-product-id="${productId}">Удалить из избранного</button>
                    </div>
                </div>
            `;
        });
        
        html += '</div>';
        
        wishlistContent.innerHTML = html;
        
        // Добавляем обработчики для кнопок удаления
        document.querySelectorAll('.btn-remove-favorite').forEach(btn => {
            btn.addEventListener('click', function() {
                const productId = this.getAttribute('data-product-id');
                removeFromWishlist(productId);
            });
        });
    }
    
    function removeFromWishlist(productId) {
        const productIdNum = parseInt(productId, 10);
        if (isNaN(productIdNum)) {
            return;
        }
        
        const favorites = JSON.parse(localStorage.getItem('favorites') || '[]');
        // Приводим все ID к числам для корректного сравнения
        const newFavorites = favorites
            .map(id => parseInt(id, 10))
            .filter(id => !isNaN(id) && id !== productIdNum);
        
        localStorage.setItem('favorites', JSON.stringify(newFavorites));
        
        // Обновляем счетчик в хедере
        if (window.updateWishlistCounter) {
            window.updateWishlistCounter();
        }
        
        // Синхронизируем с сервером если пользователь залогинен
        if (typeof jQuery !== 'undefined' && typeof asker_ajax !== 'undefined') {
            jQuery.ajax({
                url: asker_ajax.ajax_url,
                type: 'POST',
                data: {
                    action: 'asker_toggle_wishlist',
                    product_id: productIdNum,
                    action_type: 'remove'
                }
            });
        }
        
        // Перерендериваем список
        renderWishlist();
    }
    
    // Рендерим избранное при загрузке
    renderWishlist();
});
</script>

<style>
.wishlist-page {
    padding: 40px 0;
}

.wishlist-loading, .empty-wishlist {
    text-align: center;
    padding: 40px;
    color: #666;
}

.wishlist-items {
    margin: 20px 0;
}

.wishlist-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 20px;
    border: 1px solid #ddd;
    border-radius: 8px;
    margin-bottom: 10px;
}

.wishlist-item-info h3 {
    margin: 0 0 10px 0;
}

.wishlist-item-actions {
    display: flex;
    gap: 10px;
}

.btn-remove-favorite {
    background: #ff4757;
    color: white;
    border: none;
    padding: 8px 16px;
    border-radius: 4px;
    cursor: pointer;
}
</style>

<?php get_footer(); ?>
