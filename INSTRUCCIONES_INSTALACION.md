# 📋 Instrucciones de Instalación - CashSpace

## ✅ Paso a Paso para Instalar la Plataforma

### PASO 1: Verificar XAMPP
1. Abre el Panel de Control de XAMPP
2. Asegúrate de que **Apache** y **MySQL** estén corriendo (botones en verde)
3. Si no están corriendo, haz clic en "Start" para iniciarlos

### PASO 2: Crear la Base de Datos

**Opción A - Desde phpMyAdmin (Recomendado):**
1. Abre tu navegador y ve a: `http://localhost/phpmyadmin`
2. Haz clic en la pestaña "Importar" (arriba)
3. Haz clic en "Seleccionar archivo"
4. Busca y selecciona: `database/cashspace.sql`
5. Haz clic en "Ejecutar" o "Importar"
6. Espera a que aparezca el mensaje de éxito ✅

**Opción B - Desde SQL:**
1. Abre phpMyAdmin: `http://localhost/phpmyadmin`
2. Haz clic en "Nueva" (para crear base de datos)
3. Nombre: `cashspace`
4. Clic en "Crear"
5. Selecciona la base de datos `cashspace`
6. Ve a la pestaña "SQL"
7. Copia TODO el contenido del archivo `database/cashspace.sql`
8. Pega en el cuadro SQL y haz clic en "Ejecutar"

### PASO 3: Verificar la Configuración de la Base de Datos

1. Abre el archivo: `config/database.php`
2. Verifica que tenga estos valores:
   ```php
   define('DB_HOST', 'localhost');
   define('DB_USER', 'root');
   define('DB_PASS', ''); // Si tienes contraseña, ponla aquí
   define('DB_NAME', 'cashspace');
   ```

### PASO 4: Crear el Usuario Administrador

1. Abre tu navegador
2. Ve a: `http://localhost/CashSpace/install.php`
3. Deberías ver un mensaje de éxito ✅
4. **IMPORTANTE**: Después de esto, ELIMINA el archivo `install.php` por seguridad
   - O simplemente renómbralo a `install.php.bak`

### PASO 5: Acceder a la Plataforma

1. Ve a: `http://localhost/CashSpace/`
2. Verás la página de Login

**Credenciales de Administrador:**
- **Teléfono**: `admin`
- **Contraseña**: `admin123`

⚠️ **MUY IMPORTANTE**: Cambia esta contraseña después de iniciar sesión por primera vez.

### PASO 6: Probar la Plataforma

**Como Administrador:**
1. Inicia sesión con las credenciales de admin
2. Verás el panel principal con tus estadísticas
3. Puedes ir al panel de administración haciendo clic en "Admin"

**Como Usuario Normal:**
1. Haz clic en "Regístrate aquí" en la página de login
2. Crea una cuenta nueva con tu número de teléfono
3. Opcionalmente, ingresa un código de invitación si tienes uno

## 🔧 Solución de Problemas Comunes

### Error: "No se puede conectar a la base de datos"
- Verifica que MySQL esté corriendo en XAMPP
- Revisa las credenciales en `config/database.php`
- Asegúrate de que la base de datos `cashspace` exista

### Error 404 en las páginas
- Verifica que Apache esté corriendo
- Asegúrate de que los archivos estén en `C:\xampp\htdocs\CashSpace\`
- Verifica la URL base en `config/config.php`

### No puedo iniciar sesión como admin
- Ejecuta `install.php` nuevamente si aún no lo hiciste
- Verifica que el usuario admin exista en la base de datos
- Usa phpMyAdmin y ejecuta:
  ```sql
  SELECT * FROM users WHERE telefono = 'admin';
  ```

### Los estilos no se ven
- Verifica que los archivos CSS estén en la carpeta `css/`
- Revisa la consola del navegador (F12) para ver errores
- Asegúrate de que Font Awesome se cargue correctamente

## 📝 Próximos Pasos Después de la Instalación

1. ✅ Cambia la contraseña del administrador
2. ✅ Crea algunos usuarios de prueba
3. ✅ Crea códigos promocionales desde el panel de admin
4. ✅ Prueba realizar una inversión de prueba
5. ✅ Configura las notificaciones que deseas mostrar

## 🎯 Funcionalidades a Probar

- ✅ Registro de nuevo usuario
- ✅ Login y logout
- ✅ Visualizar planes de inversión
- ✅ Realizar una inversión
- ✅ Solicitar recarga de saldo
- ✅ Solicitar retiro
- ✅ Canjear código promocional
- ✅ Panel de administración
- ✅ Aprobar/rechazar recargas y retiros

¡Tu plataforma CashSpace está lista para usar! 🚀

