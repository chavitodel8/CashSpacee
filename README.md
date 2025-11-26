# 💎 CashSpace - Plataforma de Inversión

CashSpace es una plataforma web completa para gestión de inversiones con sistema de ganancias diarias automáticas. Permite a los usuarios invertir en diferentes planes y generar ganancias de manera programada.

## 🚀 Características Principales

- ✅ **Sistema de Autenticación**: Login y registro con número de teléfono y código de invitación opcional
- 💰 **Gestión de Inversiones**: 7 planes de inversión (100, 200, 500, 1000, 2000, 5000, 10000 Bs)
- 📈 **Ganancias Automáticas**: Sistema de generación de ganancias diarias
- 💳 **Recarga de Saldo**: Solicitud y aprobación de recargas por administradores
- 💸 **Retiro de Fondos**: Sistema de retiro con aprobación administrativa
- 🎟️ **Códigos Promocionales**: Sistema de canje de códigos promocionales
- 👨‍💼 **Panel de Administración**: Dashboard completo para gestión de la plataforma
- 📱 **Diseño Responsive**: Interfaz moderna y adaptable a todos los dispositivos

## 📋 Requisitos

- XAMPP (Apache + MySQL + PHP 7.4 o superior)
- Navegador web moderno
- PHP con extensiones: mysqli, PDO

## 🔧 Instalación

### 1. Configurar XAMPP

1. Descarga e instala XAMPP desde [https://www.apachefriends.org/](https://www.apachefriends.org/)
2. Inicia Apache y MySQL desde el panel de control de XAMPP

### 2. Instalar la Base de Datos

1. Abre phpMyAdmin (http://localhost/phpmyadmin)
2. Crea una nueva base de datos llamada `cashspace` (o importa el archivo SQL directamente)
3. Importa el archivo `database/cashspace.sql` desde phpMyAdmin
4. **IMPORTANTE**: Después de importar la base de datos, ejecuta `install.php` desde tu navegador:
   - Ve a: `http://localhost/CashSpace/install.php`
   - Esto creará el usuario administrador con las credenciales correctas

### 3. Configurar la Aplicación

1. Copia la carpeta `CashSpace` a la carpeta `htdocs` de XAMPP:
   - Windows: `C:\xampp\htdocs\CashSpace`
   - Linux/Mac: `/opt/lampp/htdocs/CashSpace`

2. Verifica la configuración de base de datos en `config/database.php`:
   ```php
   define('DB_HOST', 'localhost');
   define('DB_USER', 'root');
   define('DB_PASS', ''); // Tu contraseña de MySQL si la tienes configurada
   define('DB_NAME', 'cashspace');
   ```

3. Actualiza la BASE_URL en `config/config.php` si es necesario:
   ```php
   define('BASE_URL', 'http://localhost/CashSpace/');
   ```

### 4. Crear Usuario Administrador

**IMPORTANTE**: Antes de acceder, ejecuta el script de instalación:

1. Abre tu navegador y ve a: `http://localhost/CashSpace/install.php`
2. Este script creará automáticamente el usuario administrador
3. Después de ejecutarlo, elimina el archivo `install.php` por seguridad

### 5. Acceder a la Plataforma

1. Abre tu navegador y ve a: `http://localhost/CashSpace/`
2. Para acceder como administrador:
   - **Teléfono**: `admin`
   - **Contraseña**: `admin123`
   - ⚠️ **IMPORTANTE**: Cambia esta contraseña inmediatamente después del primer acceso

## 👤 Cuenta de Administrador

- **Teléfono**: `admin`
- **Contraseña**: `admin123` (cámbiala después)

### Cambiar Contraseña del Admin

1. Inicia sesión como administrador
2. Ve al panel de administración
3. Selecciona "Configuración" > "Cambiar Contraseña"

## 📱 Uso de la Plataforma

### Para Usuarios

1. **Registro**: Crea una cuenta con tu número de teléfono
2. **Recargar Saldo**: Solicita una recarga desde el botón "Recargar"
3. **Invertir**: Selecciona un plan de inversión y realiza tu inversión
4. **Ganancias**: Recibe ganancias diarias automáticas en tu saldo
5. **Retirar**: Solicita retiros de tu saldo disponible

### Para Administradores

1. **Dashboard**: Ve estadísticas generales de la plataforma
2. **Recargas**: Aprueba o rechaza solicitudes de recarga
3. **Retiros**: Gestiona solicitudes de retiro de usuarios
4. **Usuarios**: Administra usuarios, cambia contraseñas, etc.
5. **Códigos**: Crea y gestiona códigos promocionales
6. **Configuración**: Ajusta parámetros de la plataforma

## 💡 Planes de Inversión

| Plan | Inversión | Ganancia Diaria | Ganancia Mensual |
|------|-----------|-----------------|------------------|
| Básica | 100 Bs | 12 Bs | 300 Bs |
| Plus | 200 Bs | 25 Bs | 600 Bs |
| Premium | 500 Bs | 65 Bs | 1,500 Bs |
| Gold | 1,000 Bs | 130 Bs | 3,000 Bs |
| Platinum | 2,000 Bs | 260 Bs | 6,000 Bs |
| Diamond | 5,000 Bs | 650 Bs | 15,000 Bs |
| Master | 10,000 Bs | 1,300 Bs | 30,000 Bs |

## 🔐 Seguridad

- Las contraseñas están hasheadas con bcrypt
- Validación de entrada en todos los formularios
- Protección contra SQL Injection con prepared statements
- Sesiones seguras para autenticación
- Verificación de permisos de administrador

## 📁 Estructura del Proyecto

```
CashSpace/
├── admin/                  # Panel de administración
│   ├── api/               # APIs del admin
│   ├── js/                # JavaScript del admin
│   └── index.php          # Dashboard principal
├── api/                   # APIs públicas
│   ├── invest.php         # Realizar inversión
│   ├── recarga.php        # Solicitar recarga
│   ├── retiro.php         # Solicitar retiro
│   └── canje_codigo.php   # Canjear código
├── config/                # Configuración
│   ├── config.php         # Configuración general
│   └── database.php       # Conexión a BD
├── css/                   # Estilos
│   └── style.css          # Estilos principales
├── database/              # Base de datos
│   └── cashspace.sql      # Script SQL
├── includes/              # Funciones comunes
│   ├── auth.php           # Autenticación
│   ├── investment.php     # Funciones de inversión
│   └── modals.php         # Modales HTML
├── js/                    # JavaScript
│   └── main.js            # Funciones principales
├── index.php              # Página principal
├── login.php              # Login
├── register.php           # Registro
├── investment-detail.php  # Detalle de inversión
└── logout.php             # Cerrar sesión
```

## 🛠️ Personalización

### Cambiar Configuración de Base de Datos

Edita `config/database.php` con tus credenciales de MySQL.

### Cambiar URL Base

Edita `config/config.php` y actualiza `BASE_URL` según tu configuración.

### Modificar Planes de Inversión

Los planes están definidos en la tabla `tipos_inversion`. Puedes modificarlos desde:
1. phpMyAdmin directamente
2. Panel de administración (si implementas esa funcionalidad)
3. Ejecutando consultas SQL

## ⚠️ Notas Importantes

1. **Seguridad**: Cambia la contraseña del administrador inmediatamente
2. **Backup**: Realiza backups regulares de la base de datos
3. **Producción**: Antes de usar en producción:
   - Cambia `display_errors` a `0` en `config/config.php`
   - Configura HTTPS
   - Revisa todas las validaciones de seguridad
   - Configura límites de tasa de solicitudes

## 🐛 Solución de Problemas

### Error de conexión a la base de datos
- Verifica que MySQL esté corriendo en XAMPP
- Revisa las credenciales en `config/database.php`
- Asegúrate de que la base de datos `cashspace` existe

### Las ganancias diarias no se generan automáticamente
- Las ganancias se generan cuando el usuario accede a su cuenta
- Para automatizar completamente, configura un cron job que ejecute una tarea diaria

### No puedo iniciar sesión como admin
- Verifica que el usuario admin existe en la base de datos
- Usa: `SELECT * FROM users WHERE telefono = 'admin';`
- Si no existe, ejecuta nuevamente el script SQL

## 📞 Soporte

Para problemas o preguntas sobre la plataforma, revisa la documentación o contacta al equipo de desarrollo.

## 📝 Licencia

Este proyecto es de uso interno. Todos los derechos reservados.

---

**Desarrollado con ❤️ para CashSpace**

