/**
 * Яндекс.Карты для страницы контактов
 * Требуется API ключ Яндекс.Карт
 */

(function () {
    'use strict';

    // Координаты офиса (получены через геокодер Яндекса)
    const officeCoords = [59.915211, 30.264751]; // Санкт-Петербург, ул. Карпатская д. 16
    const officeAddress = 'Санкт-Петербург, ул. Карпатская д. 16';

    function initYandexMap() {
        const mapContainer = document.getElementById('yandex-map');
        if (!mapContainer) {
            return;
        }

        // API ключ из data-атрибута (берется из Customizer)
        const apiKey = mapContainer.getAttribute('data-api-key') || 'd28e3471-49d9-44e9-bc04-b5e023d5956a';

        // Загружаем API Яндекс.Карт
        const script = document.createElement('script');
        script.src = `https://api-maps.yandex.ru/2.1/?apikey=${apiKey}&lang=ru_RU`;
        script.async = true;
        
        script.onload = function() {
            if (typeof ymaps !== 'undefined') {
                ymaps.ready(function() {
                    const map = new ymaps.Map('yandex-map', {
                        center: officeCoords,
                        zoom: 15,
                        controls: ['zoomControl', 'fullscreenControl']
                    });

                    // Добавляем маркер
                    const placemark = new ymaps.Placemark(officeCoords, {
                        balloonContent: officeAddress,
                        hintContent: 'Asker Parts'
                    }, {
                        preset: 'islands#redDotIcon'
                    });

                    map.geoObjects.add(placemark);
                    
                    // Открываем балун по умолчанию
                    placemark.balloon.open();
                });
            }
        };

        script.onerror = function() {
            mapContainer.innerHTML = '<p style="padding: 20px; text-align: center; color: #d00;">Ошибка загрузки Яндекс.Карт. Проверьте подключение к интернету.</p>';
        };

        document.head.appendChild(script);
    }

    // Инициализация при загрузке страницы
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initYandexMap);
    } else {
        initYandexMap();
    }
})();

