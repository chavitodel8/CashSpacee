<!-- Banner de instalación PWA - DESHABILITADO TEMPORALMENTE -->
<!-- El banner estaba causando problemas de scroll, se deshabilitó hasta resolver el problema -->
<div id="pwa-install-banner" style="display: none !important; visibility: hidden !important; position: fixed; bottom: 70px; left: 0; right: 0; background: linear-gradient(135deg, var(--blue-primary) 0%, #033a73 100%); color: white; padding: 15px 20px; box-shadow: 0 -4px 20px rgba(0,0,0,0.2); z-index: 1; pointer-events: none; touch-action: none;">
    <div style="display: flex; align-items: center; justify-content: space-between; max-width: 1200px; margin: 0 auto; gap: 15px;">
        <div style="flex: 1;">
            <div style="font-weight: 600; font-size: 16px; margin-bottom: 5px;">
                <i class="fas fa-mobile-alt" style="margin-right: 8px;"></i>
                Instala CashSpace
            </div>
            <div style="font-size: 13px; opacity: 0.9;">
                Acceso rápido desde tu pantalla de inicio
            </div>
        </div>
        <div style="display: flex; gap: 10px;">
            <button id="pwa-install-button" style="background: white; color: var(--blue-primary); border: none; padding: 10px 20px; border-radius: 8px; font-weight: 600; cursor: pointer; white-space: nowrap; pointer-events: none;">
                Instalar
            </button>
            <button id="pwa-dismiss-button" style="background: transparent; color: white; border: 1px solid rgba(255,255,255,0.3); padding: 10px 15px; border-radius: 8px; cursor: pointer; pointer-events: none;">
                <i class="fas fa-times"></i>
            </button>
        </div>
    </div>
</div>

<script>
// Banner PWA DESHABILITADO - No ejecutar código
(function() {
    'use strict';
    
    // Forzar banner siempre oculto y sin interacción
    function forceBannerHidden() {
        const banner = document.getElementById('pwa-install-banner');
        if (banner) {
            banner.style.display = 'none';
            banner.style.visibility = 'hidden';
            banner.style.pointerEvents = 'none';
            banner.style.touchAction = 'none';
            banner.style.zIndex = '-1';
            // Remover del flujo del documento
            banner.style.position = 'absolute';
            banner.style.left = '-9999px';
        }
    }
    
    // Ejecutar inmediatamente y repetidamente
    forceBannerHidden();
    
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', forceBannerHidden);
    }
    
    window.addEventListener('load', forceBannerHidden);
    
    // Monitorear cambios
    const observer = new MutationObserver(function() {
        forceBannerHidden();
    });
    
    if (document.body) {
        observer.observe(document.body, {
            childList: true,
            subtree: true,
            attributes: true,
            attributeFilter: ['style', 'class']
        });
    }
    
    // Forzar cada segundo
    setInterval(forceBannerHidden, 1000);
})();
</script>

