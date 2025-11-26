# Instrucciones para Configurar la PWA de CashSpace

## 1. Generar Iconos PWA

Para generar los iconos necesarios (192x192 y 512x512), ejecuta el script:

```bash
php scripts/generar_iconos.php
```

O desde el navegador:
```
http://localhost/CashSpace/scripts/generar_iconos.php
```

Este script:
- Genera `icon-192x192.png` (192x192 píxeles)
- Genera `icon-512x512.png` (512x512 píxeles)
- Genera `apple-touch-icon.png` (180x180 píxeles para iOS)
- Los guarda en `assets/images/icons/`

**Requisitos:**
- La extensión GD de PHP debe estar habilitada
- El logo debe existir en `assets/images/logo.png`

## 2. Configurar Notificaciones Push

### Paso 1: Generar Claves VAPID

Las notificaciones push requieren claves VAPID (Voluntary Application Server Identification). Puedes generarlas usando:

**Opción 1: Usar herramienta online**
- Visita: https://web-push-codelab.glitch.me/
- Genera las claves pública y privada

**Opción 2: Usar Node.js**
```bash
npm install -g web-push
web-push generate-vapid-keys
```

### Paso 2: Actualizar la Clave Pública

Edita `js/push-notifications.js` y reemplaza la línea:
```javascript
const vapidPublicKey = 'TU_CLAVE_PUBLICA_AQUI';
```

Con tu clave pública VAPID generada.

### Paso 3: Configurar el Servidor (Opcional)

Para enviar notificaciones desde el servidor, necesitarás:
1. La clave privada VAPID
2. Un script PHP que use la biblioteca web-push de PHP

Ejemplo de instalación:
```bash
composer require minishlink/web-push
```

## 3. Verificar la Instalación

1. **Verificar Service Worker:**
   - Abre las herramientas de desarrollador (F12)
   - Ve a la pestaña "Application" o "Aplicación"
   - Verifica que el Service Worker esté registrado

2. **Verificar Manifest:**
   - En la misma pestaña, busca "Manifest"
   - Verifica que todos los iconos estén cargando correctamente

3. **Probar Instalación:**
   - En Chrome/Edge: Busca el icono de instalación en la barra de direcciones
   - En móviles: Usa el menú del navegador para "Añadir a pantalla de inicio"

## 4. Funcionalidades Implementadas

✅ **PWA Básica:**
- Manifest.json configurado
- Service Worker para cache offline
- Iconos PWA (requiere ejecutar el script de generación)

✅ **Banner de Instalación:**
- Se muestra automáticamente cuando está disponible
- Se puede cerrar y no se muestra de nuevo el mismo día

✅ **Notificaciones Push:**
- Sistema básico implementado
- Requiere configuración de claves VAPID

✅ **Cache Offline:**
- Cachea páginas principales
- Cachea recursos estáticos (CSS, JS, imágenes)
- Funciona sin conexión

## 5. Solución de Problemas

### Los iconos no se muestran
- Verifica que hayas ejecutado `scripts/generar_iconos.php`
- Verifica que los archivos existan en `assets/images/icons/`
- Limpia el cache del navegador

### El Service Worker no se registra
- Verifica que estés usando HTTPS o localhost
- Verifica la consola del navegador para errores
- Asegúrate de que `service-worker.js` esté en la raíz del proyecto

### Las notificaciones no funcionan
- Verifica que hayas configurado las claves VAPID
- Verifica que el usuario haya dado permiso
- Revisa la consola del navegador para errores

### El banner no aparece
- Verifica que `includes/pwa-banner.php` esté incluido en la página
- Verifica que `js/pwa-install.js` esté cargado
- Limpia el localStorage del navegador

## 6. Próximos Pasos (Opcional)

- [ ] Configurar servidor de notificaciones push completo
- [ ] Agregar más páginas al cache offline
- [ ] Implementar sincronización en segundo plano
- [ ] Agregar más shortcuts en el manifest

