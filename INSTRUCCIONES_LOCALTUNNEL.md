# 🌐 Usar localtunnel para Acceso Público

## 📋 Requisitos Previos

### Paso 1: Instalar Node.js
1. Ve a: https://nodejs.org/
2. Descarga la versión LTS (Long Term Support)
3. Instálalo (marca la opción "Add to PATH" durante la instalación)
4. Reinicia tu computadora después de instalar

### Paso 2: Verificar Instalación
Abre CMD y ejecuta:
```cmd
node --version
npm --version
```
Si muestra versiones, Node.js está instalado correctamente.

---

## 🚀 Instalación de localtunnel

### Paso 1: Instalar localtunnel globalmente
Abre CMD (como Administrador) y ejecuta:
```cmd
npm install -g localtunnel
```

Esto instalará localtunnel en tu sistema.

---

## 🎯 Usar localtunnel

### Opción A: Comando Simple
Abre CMD y ejecuta:
```cmd
lt --port 80
```

### Opción B: Con Subdominio Personalizado
```cmd
lt --port 80 --subdomain mi-sistema
```
Esto te dará: `https://mi-sistema.loca.lt`

### Opción C: Con Puerto Específico
Si tu Apache está en otro puerto (ej: 8080):
```cmd
lt --port 8080
```

---

## 📱 Acceder desde tu Teléfono

1. **Ejecuta localtunnel:**
   ```cmd
   lt --port 80
   ```

2. **Te mostrará algo como:**
   ```
   your url is: https://random-name.loca.lt
   ```

3. **Copia esa URL y úsala en tu teléfono:**
   ```
   https://random-name.loca.lt/CashSpace/
   ```

---

## ⚙️ Opciones Avanzadas

### Usar el mismo subdominio siempre
```cmd
lt --port 80 --subdomain cashspace
```
URL resultante: `https://cashspace.loca.lt`

### Especificar región
```cmd
lt --port 80 --region eu
```
Regiones disponibles: `us`, `eu`, `ap`, `au`, `sa`, `jp`, `in`

---

## 🔧 Script Automático

He creado un archivo `iniciar_tunel.bat` que puedes usar para iniciar localtunnel fácilmente.

Solo haz doble clic en el archivo y se abrirá automáticamente.

---

## ⚠️ Notas Importantes

1. **Mantén la ventana CMD abierta:** Si cierras la ventana, el túnel se cerrará.

2. **URL temporal:** La URL cambia cada vez que reinicias localtunnel (a menos que uses `--subdomain`).

3. **Primera vez:** La primera vez que accedas desde tu teléfono, localtunnel te pedirá que presiones "Continue" en una página web. Esto es normal.

4. **HTTPS automático:** localtunnel usa HTTPS automáticamente, así que tu conexión será segura.

---

## 🐛 Solución de Problemas

### Error: "lt no se reconoce como comando"
- Asegúrate de haber instalado Node.js
- Reinstala localtunnel: `npm install -g localtunnel`
- Reinicia CMD después de instalar

### Error: "Puerto 80 ya en uso"
- Verifica que Apache esté corriendo en XAMPP
- Si usas otro puerto, cambia el comando: `lt --port 8080`

### La página se queda cargando
- Verifica que Apache esté corriendo
- Asegúrate de usar la URL completa: `https://random-name.loca.lt/CashSpace/`
- Incluye la barra final `/` después de `CashSpace`

### "Continue" page en el navegador
- Es normal la primera vez
- Haz clic en "Continue" y luego accede a tu URL

---

## 📝 Ejemplo Completo

1. **Abre CMD**

2. **Ejecuta:**
   ```cmd
   lt --port 80 --subdomain cashspace
   ```

3. **Espera a que aparezca:**
   ```
   your url is: https://cashspace.loca.lt
   ```

4. **Abre en tu teléfono:**
   ```
   https://cashspace.loca.lt/CashSpace/
   ```

5. **¡Listo!** Tu sistema ahora es accesible desde cualquier dispositivo con internet.

---

## 🔒 Seguridad

⚠️ **ADVERTENCIA:** Al usar localtunnel, tu servidor local es accesible públicamente.

- Solo úsalo para pruebas
- No expongas datos sensibles sin protección adicional
- Cierra el túnel cuando no lo uses (Ctrl+C en CMD)

