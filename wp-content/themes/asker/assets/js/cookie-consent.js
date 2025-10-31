document.addEventListener('DOMContentLoaded', function() {
    // Простой баннер согласия на cookies. Храним флаг в localStorage.
    var KEY = 'asker_cookie_consent_v1';
    var bar = document.getElementById('cookie-bar');
    
    if (!bar) {
        return;
    }
    
    if (window.localStorage && localStorage.getItem(KEY)) {
        bar.style.display = 'none';
        return;
    }
    
    var btn = document.getElementById('cookie-bar__accept');
    
    if (btn) {
        btn.addEventListener('click', function(){
            try { 
                localStorage.setItem(KEY, '1');
            } catch(e) {
                console.error('Error saving to localStorage:', e);
            }
            bar.style.display = 'none';
        });
    }
});
