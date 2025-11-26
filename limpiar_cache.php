<?php
require_once 'config/config.php';
requireLogin();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Limpiar Cache - CashSpace</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        body {
            background: var(--light-color);
            padding: 20px;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
        }
        h1 {
            color: var(--blue-primary);
            margin-bottom: 30px;
            text-align: center;
        }
        .info-box {
            background: #f0f4ff;
            border-left: 4px solid var(--blue-primary);
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 30px;
        }
        .info-box h3 {
            color: var(--blue-primary);
            margin-top: 0;
        }
        .button-group {
            display: flex;
            flex-direction: column;
            gap: 15px;
            margin-top: 30px;
        }
        .btn-clean {
            padding: 15px 25px;
            border: none;
            border-radius: 10px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }
        .btn-clean.primary {
            background: linear-gradient(135deg, var(--blue-primary) 0%, #033a73 100%);
            color: white;
        }
        .btn-clean.primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(4, 73, 144, 0.3);
        }
        .btn-clean.secondary {
            background: #f3f4f6;
            color: var(--dark-color);
        }
        .btn-clean.secondary:hover {
            background: #e5e7eb;
        }
        .result-box {
            margin-top: 20px;
            padding: 15px;
            border-radius: 8px;
            display: none;
        }
        .result-box.success {
            background: #d1fae5;
            color: #065f46;
            border: 1px solid #10b981;
        }
        .result-box.error {
            background: #fee2e2;
            color: #991b1b;
            border: 1px solid #ef4444;
        }
        .result-box.info {
            background: #dbeafe;
            color: #1e40af;
            border: 1px solid #3b82f6;
        }
        .back-link {
            display: inline-block;
            margin-top: 20px;
            color: var(--blue-primary);
            text-decoration: none;
            font-weight: 600;
        }
        .back-link:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔧 Limpiar Cache y Datos</h1>
        
        <div class="info-box">
            <h3>⚠️ ¿Por qué limpiar el cache?</h3>
            <p>Si experimentas problemas como congelamiento, carga lenta o comportamientos extraños, puede deberse a:</p>
            <ul style="margin: 10px 0; padding-left: 20px; line-height: 1.8;">
                <li>Service Workers antiguos registrados</li>
                <li>Cache del navegador corrupto</li>
                <li>Datos almacenados en localStorage conflictivos</li>
                <li>Versión antigua de archivos JavaScript/CSS</li>
            </ul>
        </div>

        <div id="resultBox" class="result-box"></div>

        <div class="button-group">
            <button class="btn-clean primary" onclick="limpiarTodo()">
                <i class="fas fa-broom"></i>
                Limpiar Todo (Recomendado)
            </button>
            <button class="btn-clean secondary" onclick="limpiarServiceWorkers()">
                <i class="fas fa-cog"></i>
                Solo Service Workers
            </button>
            <button class="btn-clean secondary" onclick="limpiarCache()">
                <i class="fas fa-trash"></i>
                Solo Cache del Navegador
            </button>
            <button class="btn-clean secondary" onclick="limpiarLocalStorage()">
                <i class="fas fa-database"></i>
                Solo Datos Almacenados (localStorage)
            </button>
        </div>

        <a href="index.php" class="back-link">
            <i class="fas fa-arrow-left"></i> Volver al Inicio
        </a>
    </div>

    <script>
        function mostrarResultado(mensaje, tipo = 'success') {
            const resultBox = document.getElementById('resultBox');
            resultBox.className = 'result-box ' + tipo;
            resultBox.innerHTML = mensaje;
            resultBox.style.display = 'block';
            setTimeout(() => {
                resultBox.style.display = 'none';
            }, 5000);
        }

        async function limpiarServiceWorkers() {
            try {
                if ('serviceWorker' in navigator) {
                    const registrations = await navigator.serviceWorker.getRegistrations();
                    let limpiados = 0;
                    
                    for (let registration of registrations) {
                        await registration.unregister();
                        limpiados++;
                    }
                    
                    if (limpiados > 0) {
                        mostrarResultado(`✅ Se eliminaron ${limpiados} Service Worker(s) registrado(s)`, 'success');
                    } else {
                        mostrarResultado('ℹ️ No hay Service Workers registrados', 'info');
                    }
                } else {
                    mostrarResultado('ℹ️ Tu navegador no soporta Service Workers', 'info');
                }
            } catch (error) {
                mostrarResultado('❌ Error al limpiar Service Workers: ' + error.message, 'error');
            }
        }

        async function limpiarCache() {
            try {
                if ('caches' in window) {
                    const cacheNames = await caches.keys();
                    let limpiados = 0;
                    
                    for (let cacheName of cacheNames) {
                        await caches.delete(cacheName);
                        limpiados++;
                    }
                    
                    if (limpiados > 0) {
                        mostrarResultado(`✅ Se limpiaron ${limpiados} cache(s)`, 'success');
                    } else {
                        mostrarResultado('ℹ️ No hay cache para limpiar', 'info');
                    }
                } else {
                    mostrarResultado('ℹ️ Tu navegador no soporta Cache API', 'info');
                }
            } catch (error) {
                mostrarResultado('❌ Error al limpiar cache: ' + error.message, 'error');
            }
        }

        function limpiarLocalStorage() {
            try {
                const keys = Object.keys(localStorage);
                let limpiados = 0;
                
                // Limpiar solo las keys relacionadas con la app
                const keysApp = keys.filter(key => 
                    key.includes('pwa') || 
                    key.includes('cashspace') || 
                    key.includes('banner') ||
                    key.includes('dismissed')
                );
                
                keysApp.forEach(key => {
                    localStorage.removeItem(key);
                    limpiados++;
                });
                
                if (limpiados > 0) {
                    mostrarResultado(`✅ Se limpiaron ${limpiados} dato(s) del localStorage`, 'success');
                } else {
                    mostrarResultado('ℹ️ No hay datos de la app en localStorage', 'info');
                }
            } catch (error) {
                mostrarResultado('❌ Error al limpiar localStorage: ' + error.message, 'error');
            }
        }

        async function limpiarTodo() {
            mostrarResultado('🔄 Limpiando...', 'info');
            
            await limpiarServiceWorkers();
            await new Promise(resolve => setTimeout(resolve, 500));
            
            await limpiarCache();
            await new Promise(resolve => setTimeout(resolve, 500));
            
            limpiarLocalStorage();
            
            setTimeout(() => {
                mostrarResultado('✅ ¡Todo limpiado exitosamente!<br><br>La página se recargará en 3 segundos...', 'success');
                setTimeout(() => {
                    window.location.reload(true);
                }, 3000);
            }, 1000);
        }

        // Detectar y limpiar Service Workers antiguos automáticamente al cargar
        window.addEventListener('load', async () => {
            if ('serviceWorker' in navigator) {
                try {
                    const registrations = await navigator.serviceWorker.getRegistrations();
                    // Si hay más de 1 Service Worker registrado, limpiar los antiguos
                    if (registrations.length > 1) {
                        console.log('⚠️ Detectados múltiples Service Workers. Limpiando los antiguos...');
                        for (let i = 1; i < registrations.length; i++) {
                            await registrations[i].unregister();
                        }
                    }
                } catch (error) {
                    console.error('Error al verificar Service Workers:', error);
                }
            }
        });
    </script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</body>
</html>

