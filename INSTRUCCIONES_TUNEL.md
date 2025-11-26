# 🌐 Exponer tu Sistema Local a Internet (Túnel)

## Opción 1: ngrok (Recomendado)

### Paso 1: Descargar ngrok
1. Ve a: https://ngrok.com/download
2. Descarga la versión para Windows
3. Extrae el archivo `ngrok.exe` en una carpeta (ej: `C:\ngrok\`)

### Paso 2: Configurar ngrok
1. Abre CMD (Símbolo del sistema)
2. Navega a la carpeta donde está ngrok.exe:
   ```cmd
   cd C:\ngrok
   ```
3. Ejecuta ngrok (puerto 80 es el de Apache):
   ```cmd
   ngrok http 80
   ```
   
   O si quieres especificar un dominio personalizado:
   ```cmd
   ngrok http 80 --domain=tu-dominio.ngrok-free.app
   ```

### Paso 3: Obtener el enlace público
ngrok te mostrará algo como:
```
Forwarding    https://abc123.ngrok-free.app -> http://localhost:80
```

**Ese enlace `https://abc123.ngrok-free.app` es tu URL pública.**

### Paso 4: Acceder desde cualquier dispositivo
Usa la URL que te da ngrok:
```
https://abc123.ngrok-free.app/CashSpace/
```

**Nota:** ngrok te dará una URL temporal. Cada vez que reinicies ngrok, la URL cambiará (a menos que tengas cuenta premium).

---

## Opción 2: localtunnel (Gratis, sin registro)

### Paso 1: Instalar Node.js
1. Descarga Node.js desde: https://nodejs.org/
2. Instálalo

### Paso 2: Instalar localtunnel
Abre CMD y ejecuta:
```cmd
npm install -g localtunnel
```

### Paso 3: Ejecutar localtunnel
```cmd
lt --port 80
```

Te dará una URL como:
```
your url is: https://random-name.loca.lt
```

### Paso 4: Acceder
```
https://random-name.loca.lt/CashSpace/
```

---

## Opción 3: Cloudflare Tunnel (cloudflared)

### Paso 1: Descargar cloudflared
1. Ve a: https://github.com/cloudflare/cloudflared/releases
2. Descarga `cloudflared-windows-amd64.exe`
3. Renómbralo a `cloudflared.exe`

### Paso 2: Ejecutar
```cmd
cloudflared tunnel --url http://localhost:80
```

Te dará una URL como:
```
https://random-name.trycloudflare.com
```

---

## Opción 4: serveo.net (Sin instalación)

### Paso 1: Abrir CMD
```cmd
ssh -R 80:localhost:80 serveo.net
```

Te dará una URL pública.

---

## ⚠️ IMPORTANTE - Configurar BASE_URL

Si usas un túnel, necesitas actualizar la BASE_URL en `config/config.php`:

```php
// Para ngrok (ejemplo)
define('BASE_URL', 'https://abc123.ngrok-free.app/CashSpace/');

// O mejor, detectar automáticamente:
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$host = $_SERVER['HTTP_HOST'];
define('BASE_URL', $protocol . '://' . $host . '/CashSpace/');
```

---

## 🎯 Recomendación

**Para pruebas rápidas:** Usa **ngrok** (Opción 1)
- Es el más popular
- Fácil de usar
- URL HTTPS automática
- Funciona de inmediato

**Para uso continuo:** Considera una cuenta gratuita de ngrok para URLs estables.

---

## 📝 Ejemplo Completo con ngrok

1. **Descarga ngrok** y colócalo en `C:\ngrok\`

2. **Abre CMD** y ejecuta:
   ```cmd
   cd C:\ngrok
   ngrok http 80
   ```

3. **Copia la URL** que te da (ej: `https://abc123.ngrok-free.app`)

4. **Accede desde tu teléfono:**
   ```
   https://abc123.ngrok-free.app/CashSpace/
   ```

5. **Listo!** Tu sistema local ahora es accesible desde internet.

---

## 🔒 Seguridad

⚠️ **ADVERTENCIA:** Al exponer tu servidor local a internet, cualquier persona con el enlace puede acceder.

**Recomendaciones:**
- Solo usa esto para pruebas
- No expongas datos sensibles
- Cierra el túnel cuando no lo uses
- Considera usar autenticación adicional

