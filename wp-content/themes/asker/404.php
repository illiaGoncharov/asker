<?php
/**
 * 404 - Страница не найдена
 */

get_header();
?>

<div class="container section">
    <!-- Хлебные крошки -->
    <nav class="breadcrumbs" aria-label="Breadcrumb">
        <a href="<?php echo esc_url(home_url('/')); ?>">Главная</a>
        <span class="breadcrumbs__separator">/</span>
        <span class="breadcrumbs__current">Страница не найдена</span>
    </nav>
    
    <div class="error-404-page">
        <div class="error-content">
            <h1 class="error-code">404</h1>
            <h2 class="error-title">Страница не найдена</h2>
            <p class="error-description">К сожалению, запрашиваемая страница не существует или была перемещена.</p>
            <div class="error-actions">
                <a href="<?php echo esc_url(home_url('/')); ?>" class="btn btn--primary">На главную</a>
                <a href="<?php echo esc_url(home_url('/shop')); ?>" class="btn btn--outline">В каталог</a>
            </div>
        </div>
    </div>
</div>

<?php get_footer(); ?>
