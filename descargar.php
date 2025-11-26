<?php
require_once 'config/config.php';
requireLogin();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Descargar Aplicación - CashSpace</title>
    <link rel="manifest" href="manifest.json">
    <meta name="theme-color" content="#044990">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <meta name="apple-mobile-web-app-title" content="CashSpace">
    <link rel="apple-touch-icon" href="assets/images/logo.png">
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            background: var(--light-color);
            padding: 0;
            margin: 0;
        }
        
        .download-container {
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            min-height: 100vh;
        }
        
        .download-header {
            background: var(--white);
            padding: 15px 20px;
            display: flex;
            align-items: center;
            gap: 15px;
            border-bottom: 1px solid var(--gray-light);
            position: sticky;
            top: 0;
            z-index: 100;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
            margin: -20px -20px 20px -20px;
        }
        
        .back-button {
            width: 40px;
            height: 40px;
            border-radius: 10px;
            background: var(--white);
            border: 1px solid var(--gray-light);
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            text-decoration: none;
            color: var(--dark-color);
            transition: all 0.2s;
        }
        
        .back-button:hover {
            background: var(--gray-light);
            transform: translateX(-2px);
        }
        
        .download-title {
            font-size: 20px;
            font-weight: 700;
            color: var(--dark-color);
            margin: 0;
        }
        
        .download-card {
            background: var(--white);
            border-radius: 16px;
            padding: 30px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.08);
            margin-bottom: 20px;
            text-align: center;
        }
        
        .download-icon {
            width: 120px;
            height: 120px;
            margin: 0 auto 25px;
            background: linear-gradient(135deg, var(--blue-primary) 0%, #033a73 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 60px;
            color: white;
        }
        
        .download-card-title {
            font-size: 24px;
            font-weight: 700;
            color: var(--dark-color);
            margin-bottom: 15px;
        }
        
        .download-card-text {
            font-size: 16px;
            color: #6b7280;
            line-height: 1.6;
            margin-bottom: 30px;
        }
        
        .install-button {
            background: var(--blue-primary);
            color: white;
            border: none;
            border-radius: 12px;
            padding: 16px 32px;
            font-size: 18px;
            font-weight: 600;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 12px;
            transition: all 0.2s;
            text-decoration: none;
            margin-bottom: 20px;
        }
        
        .install-button:hover {
            background: #033a73;
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(4, 73, 144, 0.3);
        }
        
        .install-button:disabled {
            background: #9ca3af;
            cursor: not-allowed;
            transform: none;
        }
        
        .instructions-section {
            background: var(--white);
            border-radius: 16px;
            padding: 25px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.08);
            margin-top: 20px;
        }
        
        .instructions-title {
            font-size: 20px;
            font-weight: 700;
            color: var(--dark-color);
            margin-bottom: 20px;
            text-align: center;
        }
        
        .instruction-item {
            display: flex;
            align-items: flex-start;
            gap: 15px;
            padding: 15px;
            background: #f8f9fa;
            border-radius: 12px;
            margin-bottom: 15px;
        }
        
        .instruction-number {
            width: 35px;
            height: 35px;
            background: var(--blue-primary);
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            flex-shrink: 0;
        }
        
        .instruction-text {
            flex: 1;
            color: var(--dark-color);
            line-height: 1.6;
        }
        
        .benefits-section {
            background: linear-gradient(135deg, #f0f4ff 0%, #e0ebff 100%);
            border-radius: 16px;
            padding: 25px;
            margin-top: 20px;
        }
        
        .benefits-title {
            font-size: 18px;
            font-weight: 700;
            color: var(--blue-primary);
            margin-bottom: 15px;
            text-align: center;
        }
        
        .benefit-item {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px;
            margin-bottom: 10px;
        }
        
        .benefit-icon {
            width: 40px;
            height: 40px;
            background: var(--blue-primary);
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }
        
        .benefit-text {
            color: var(--dark-color);
            font-size: 15px;
        }
        
        .pwa-status {
            text-align: center;
            padding: 15px;
            background: #d1fae5;
            color: #065f46;
            border-radius: 12px;
            margin-bottom: 20px;
            display: none;
        }
        
        .pwa-status.installed {
            display: block;
        }
        
        @media (max-width: 768px) {
            .download-container {
                padding: 15px;
            }
            
            .download-header {
                margin: -15px -15px 15px -15px;
                padding: 12px 15px;
            }
            
            .download-title {
                font-size: 18px;
            }
            
            .download-card {
                padding: 25px 20px;
            }
            
            .download-icon {
                width: 100px;
                height: 100px;
                font-size: 50px;
            }
            
            .download-card-title {
                font-size: 20px;
            }
            
            .download-card-text {
                font-size: 15px;
            }
            
            .install-button {
                padding: 14px 28px;
                font-size: 16px;
            }
            
            .instructions-section {
                padding: 20px;
            }
            
            .instructions-title {
                font-size: 18px;
            }
        }
    </style>
</head>
<body>
    <div class="download-container">
        <!-- Header -->
        <div class="download-header">
            <a href="mio.php" class="back-button">
                <i class="fas fa-arrow-left"></i>
            </a>
            <h1 class="download-title">Descargar aplicación</h1>
        </div>
        
        <!-- Estado de instalación -->
        <div id="pwa-status" class="pwa-status">
            <i class="fas fa-check-circle"></i> La aplicación ya está instalada en tu dispositivo
        </div>
        
        <!-- Tarjeta principal -->
        <div class="download-card">
            <div class="download-icon">
                <i class="fas fa-mobile-alt"></i>
            </div>
            <h2 class="download-card-title">Instalar CashSpace</h2>
            <p class="download-card-text">
                Tienes dos opciones para instalar CashSpace en tu dispositivo móvil:
            </p>
            
            <div style="display: flex; flex-direction: column; gap: 15px; margin-top: 25px;">
                <!-- Opción 1: PWA -->
                <div style="border: 2px solid var(--blue-primary); border-radius: 12px; padding: 20px; background: #f0f4ff;">
                    <div style="font-weight: 600; color: var(--blue-primary); margin-bottom: 10px; display: flex; align-items: center; gap: 10px;">
                        <i class="fas fa-globe"></i>
                        Opción 1: Instalación Web (PWA)
                    </div>
                    <p style="font-size: 14px; color: #6b7280; margin-bottom: 15px;">
                        Instala directamente desde el navegador. No requiere descargar archivo APK.
                    </p>
                    <button id="install-button" class="install-button" style="width: 100%; margin: 0;">
                        <i class="fas fa-download"></i>
                        Instalar desde Navegador
                    </button>
                </div>
                
                <!-- Opción 2: APK -->
                <div style="border: 2px solid #10b981; border-radius: 12px; padding: 20px; background: #f0fdf4;">
                    <div style="font-weight: 600; color: #10b981; margin-bottom: 10px; display: flex; align-items: center; gap: 10px;">
                        <i class="fas fa-mobile-alt"></i>
                        Opción 2: Descargar APK
                    </div>
                    <p style="font-size: 14px; color: #6b7280; margin-bottom: 15px;">
                        Genera un archivo APK para instalar como aplicación nativa de Android usando PWA Builder.
                    </p>
                    <div style="display: flex; flex-direction: column; gap: 10px;">
                        <a href="https://www.pwabuilder.com/" target="_blank" class="install-button" style="background: #10b981; text-decoration: none; width: 100%; margin: 0; justify-content: center;">
                            <i class="fas fa-external-link-alt"></i>
                            Generar APK con PWA Builder
                        </a>
                        <button onclick="mostrarInstruccionesAPK()" style="background: transparent; border: 2px solid #10b981; color: #10b981; padding: 12px 20px; border-radius: 8px; font-weight: 600; cursor: pointer; width: 100%;">
                            <i class="fas fa-info-circle"></i>
                            Ver Instrucciones
                        </button>
                    </div>
                </div>
            </div>
            
            <p style="font-size: 13px; color: #9ca3af; margin-top: 20px; text-align: center;">
                <i class="fas fa-info-circle"></i> 
                La instalación web es más rápida. El APK es útil si prefieres instalar manualmente.
            </p>
        </div>
        
        <!-- Beneficios -->
        <div class="benefits-section">
            <h3 class="benefits-title">Ventajas de instalar la aplicación</h3>
            <div class="benefit-item">
                <div class="benefit-icon">
                    <i class="fas fa-bolt"></i>
                </div>
                <div class="benefit-text">Acceso rápido desde la pantalla de inicio</div>
            </div>
            <div class="benefit-item">
                <div class="benefit-icon">
                    <i class="fas fa-wifi"></i>
                </div>
                <div class="benefit-text">Funciona sin conexión (modo offline básico)</div>
            </div>
            <div class="benefit-item">
                <div class="benefit-icon">
                    <i class="fas fa-bell"></i>
                </div>
                <div class="benefit-text">Notificaciones push (próximamente)</div>
            </div>
            <div class="benefit-item">
                <div class="benefit-icon">
                    <i class="fas fa-rocket"></i>
                </div>
                <div class="benefit-text">Rendimiento optimizado</div>
            </div>
        </div>
        
        <!-- Modal de Instrucciones APK -->
        <div id="modal-apk" style="display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.5); z-index: 10000; align-items: center; justify-content: center; padding: 20px;">
            <div style="background: white; border-radius: 16px; padding: 30px; max-width: 600px; max-height: 80vh; overflow-y: auto; position: relative;">
                <button onclick="cerrarModalAPK()" style="position: absolute; top: 15px; right: 15px; background: #f3f4f6; border: none; width: 35px; height: 35px; border-radius: 50%; cursor: pointer; display: flex; align-items: center; justify-content: center;">
                    <i class="fas fa-times"></i>
                </button>
                <h3 style="font-size: 24px; font-weight: 700; color: var(--dark-color); margin-bottom: 20px;">Cómo Generar el APK</h3>
                
                <div style="margin-bottom: 25px;">
                    <h4 style="color: var(--blue-primary); font-size: 18px; margin-bottom: 10px;">📱 Método 1: PWA Builder (Más Fácil)</h4>
                    <ol style="line-height: 2; color: var(--dark-color); padding-left: 20px;">
                        <li>Visita: <a href="https://www.pwabuilder.com/" target="_blank" style="color: var(--blue-primary);">https://www.pwabuilder.com/</a></li>
                        <li>Ingresa la URL de tu aplicación (ej: <code>https://tudominio.com/CashSpace/</code>)</li>
                        <li>Haz clic en "Start" y espera el análisis</li>
                        <li>Haz clic en "Build My PWA" → "Android"</li>
                        <li>Descarga el APK generado</li>
                        <li>Transfiere el APK a tu teléfono e instálalo</li>
                    </ol>
                </div>
                
                <div style="background: #f0f4ff; padding: 15px; border-radius: 10px; margin-top: 20px;">
                    <strong style="color: var(--blue-primary);">💡 Nota Importante:</strong>
                    <p style="color: var(--dark-color); margin-top: 5px; line-height: 1.6;">
                        Para usar PWA Builder, tu aplicación debe estar desplegada en línea con HTTPS. Una vez desplegada, simplemente ingresa la URL en PWA Builder y descarga el APK generado.
                    </p>
                </div>
            </div>
        </div>
        
        <!-- Instrucciones -->
        <div class="instructions-section">
            <h3 class="instructions-title">Instrucciones de instalación (PWA)</h3>
            
            <!-- Instrucciones iOS -->
            <div id="ios-instructions" class="instruction-item">
                <div class="instruction-number">1</div>
                <div class="instruction-text">
                    <strong>iOS (Safari):</strong> Abre esta página en Safari (no en Chrome u otros navegadores).
                </div>
            </div>
            <div class="instruction-item">
                <div class="instruction-number">2</div>
                <div class="instruction-text">
                    Toca el botón de compartir <i class="fas fa-share"></i> (cuadrado con flecha hacia arriba) en la parte inferior de la pantalla.
                </div>
            </div>
            <div class="instruction-item">
                <div class="instruction-number">3</div>
                <div class="instruction-text">
                    Desplázate hacia abajo y selecciona <strong>"Añadir a pantalla de inicio"</strong> o <strong>"Agregar a inicio"</strong>.
                </div>
            </div>
            <div class="instruction-item">
                <div class="instruction-number">4</div>
                <div class="instruction-text">
                    Personaliza el nombre si lo deseas y toca <strong>"Añadir"</strong> en la esquina superior derecha.
                </div>
            </div>
            
            <!-- Instrucciones Android -->
            <div id="android-instructions" class="instruction-item">
                <div class="instruction-number">1</div>
                <div class="instruction-text">
                    <strong>Android (Chrome/Edge):</strong> Abre esta página en Chrome o Microsoft Edge.
                </div>
            </div>
            <div class="instruction-item">
                <div class="instruction-number">2</div>
                <div class="instruction-text">
                    Toca el menú <i class="fas fa-ellipsis-v"></i> (3 puntos) en la esquina superior derecha del navegador.
                </div>
            </div>
            <div class="instruction-item">
                <div class="instruction-number">3</div>
                <div class="instruction-text">
                    Busca y selecciona <strong>"Añadir a pantalla de inicio"</strong> o <strong>"Instalar app"</strong> o <strong>"Instalar aplicación"</strong>.
                </div>
            </div>
            <div class="instruction-item">
                <div class="instruction-number">4</div>
                <div class="instruction-text">
                    Confirma la instalación tocando <strong>"Instalar"</strong> o <strong>"Añadir"</strong> en el diálogo que aparece.
                </div>
            </div>
            
            <!-- Instrucciones Desktop -->
            <div id="desktop-instructions" class="instruction-item">
                <div class="instruction-number">1</div>
                <div class="instruction-text">
                    <strong>Escritorio (Chrome/Edge):</strong> Busca el icono de instalación <i class="fas fa-plus-circle"></i> en la barra de direcciones (al lado de la URL).
                </div>
            </div>
            <div class="instruction-item">
                <div class="instruction-number">2</div>
                <div class="instruction-text">
                    Haz clic en el icono y selecciona <strong>"Instalar"</strong> en el diálogo que aparece.
                </div>
            </div>
            <div class="instruction-item">
                <div class="instruction-number">3</div>
                <div class="instruction-text">
                    Alternativamente, puedes usar el menú del navegador (3 puntos) y buscar la opción <strong>"Instalar CashSpace"</strong> o <strong>"Instalar aplicación"</strong>.
                </div>
            </div>
        </div>
    </div>
    
    <script src="js/pwa-install.js"></script>
    <script>
        // Esperar a que el DOM esté listo
        document.addEventListener('DOMContentLoaded', function() {
            // Detectar el dispositivo y mostrar instrucciones apropiadas
            const isIOS = /iPad|iPhone|iPod/.test(navigator.userAgent);
            const isAndroid = /Android/.test(navigator.userAgent);
            const isMobile = /Mobile/.test(navigator.userAgent);
            
            // Ocultar todas las instrucciones primero
            const iosInstructions = document.getElementById('ios-instructions');
            const androidInstructions = document.getElementById('android-instructions');
            const desktopInstructions = document.getElementById('desktop-instructions');
            
            // Ocultar todas
            if (iosInstructions) {
                iosInstructions.style.display = 'none';
                // Ocultar también los siguientes elementos de iOS
                const iosItems = iosInstructions.parentElement.querySelectorAll('.instruction-item');
                iosItems.forEach((item, index) => {
                    if (index > 0 && index <= 3) item.style.display = 'none';
                });
            }
            if (androidInstructions) {
                androidInstructions.style.display = 'none';
                // Ocultar también los siguientes elementos de Android
                const androidItems = androidInstructions.parentElement.querySelectorAll('.instruction-item');
                androidItems.forEach((item, index) => {
                    if (index > 4 && index <= 7) item.style.display = 'none';
                });
            }
            if (desktopInstructions) {
                desktopInstructions.style.display = 'none';
            }
            
            // Mostrar solo las instrucciones del dispositivo detectado
            if (isIOS) {
                if (iosInstructions) {
                    iosInstructions.style.display = 'flex';
                    // Mostrar los siguientes 3 elementos
                    const allItems = iosInstructions.parentElement.querySelectorAll('.instruction-item');
                    allItems.forEach((item, index) => {
                        if (index >= 1 && index <= 3) item.style.display = 'flex';
                    });
                }
            } else if (isAndroid) {
                if (androidInstructions) {
                    androidInstructions.style.display = 'flex';
                    // Mostrar los siguientes 3 elementos
                    const allItems = androidInstructions.parentElement.querySelectorAll('.instruction-item');
                    allItems.forEach((item, index) => {
                        if (index >= 5 && index <= 8) item.style.display = 'flex';
                    });
                }
            } else {
                if (desktopInstructions) {
                    desktopInstructions.style.display = 'flex';
                    // Mostrar los siguientes 2 elementos
                    const allItems = desktopInstructions.parentElement.querySelectorAll('.instruction-item');
                    allItems.forEach((item, index) => {
                        if (index >= 9 && index <= 11) item.style.display = 'flex';
                    });
                }
            }
            
            // Verificar si ya está instalada
            if (typeof isPWAInstalled === 'function' && isPWAInstalled()) {
                const statusDiv = document.getElementById('pwa-status');
                const installBtn = document.getElementById('install-button');
                if (statusDiv) statusDiv.classList.add('installed');
                if (installBtn) {
                    installBtn.disabled = true;
                    installBtn.innerHTML = '<i class="fas fa-check"></i> Ya Instalada';
                }
            }
            
            // Configurar el botón de instalación
            const installButton = document.getElementById('install-button');
            if (installButton) {
                installButton.addEventListener('click', () => {
                    if (window.installPWA) {
                        window.installPWA();
                    } else {
                        // Si no hay prompt disponible, mostrar instrucciones
                        const message = isIOS 
                            ? 'Por favor, usa Safari y el menú de compartir para añadir a pantalla de inicio'
                            : isAndroid 
                            ? 'Por favor, usa el menú del navegador (3 puntos) y selecciona "Añadir a pantalla de inicio"'
                            : 'Busca el icono de instalación en la barra de direcciones o usa el menú del navegador';
                        alert(message);
                    }
                });
            }
        });
        
        // Funciones para el modal de APK
        function mostrarInstruccionesAPK() {
            const modal = document.getElementById('modal-apk');
            if (modal) {
                modal.style.display = 'flex';
                document.body.style.overflow = 'hidden';
            }
        }
        
        function cerrarModalAPK() {
            const modal = document.getElementById('modal-apk');
            if (modal) {
                modal.style.display = 'none';
                document.body.style.overflow = 'auto';
            }
        }
        
        // Cerrar modal al hacer clic fuera
        const modalAPK = document.getElementById('modal-apk');
        if (modalAPK) {
            modalAPK.addEventListener('click', function(e) {
                if (e.target === this) {
                    cerrarModalAPK();
                }
            });
        }
    </script>
</body>
</html>

