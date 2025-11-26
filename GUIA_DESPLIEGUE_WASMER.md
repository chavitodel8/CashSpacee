# Guía de Despliegue en Wasmer - CashSpace

Esta guía te ayudará a desplegar tu proyecto CashSpace en Wasmer para pruebas.

## 📋 Requisitos Previos

1. **Cuenta en Wasmer**: Regístrate en [Wasmer.io](https://wasmer.io/es/registro)
2. **Proyecto preparado**: Asegúrate de tener todos los archivos listos
3. **Base de datos**: Necesitarás configurar MySQL/MariaDB (Wasmer puede requerir servicios externos)

## 🚀 Pasos para Desplegar

### Paso 1: Preparar el Proyecto

#### 1.1. Limpiar archivos innecesarios
```bash
# Eliminar archivos de desarrollo
- .git (si no quieres el historial)
- node_modules (si existe)
- Archivos temporales
- Logs locales
```

#### 1.2. Crear archivo `.wasmer.json` (si es necesario)
```json
{
  "name": "cashspace",
  "version": "1.0.0",
  "type": "php",
  "php_version": "8.1",
  "document_root": "/",
  "entry_point": "index.php"
}
```

#### 1.3. Ajustar configuración para producción

Edita `config/config.php`:
```php
// Cambiar configuración de errores
error_reporting(E_ALL);
ini_set('display_errors', 0); // Ocultar errores en producción
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/../logs/error.log');
```

### Paso 2: Preparar Base de Datos

Wasmer puede no ofrecer MySQL directamente. Opciones:

#### Opción A: Base de datos externa (Recomendado)
- **Railway** ⭐ (Recomendado - $5 crédito gratis/mes, muy fácil)
- **Clever Cloud** (MySQL gratuito sin límite de tiempo)
- **Aiven** ($300 crédito gratis por 30 días)
- **AWS RDS Free Tier** (750 horas/mes gratis por 12 meses)
- **Hostman** (MySQL gratuito para desarrollo)

**📖 Ver `BASES_DE_DATOS_GRATUITAS.md` para más opciones y guías detalladas.**

#### Opción B: Usar SQLite (Solo para pruebas simples)
Si Wasmer no soporta MySQL, podrías necesitar adaptar el código para SQLite.

### Paso 3: Subir Proyecto a Wasmer

#### Método 1: Desde GitHub (Recomendado)

1. **Sube tu proyecto a GitHub**:
   ```bash
   git init
   git add .
   git commit -m "Preparado para Wasmer"
   git remote add origin https://github.com/tu-usuario/cashspace.git
   git push -u origin main
   ```

2. **En Wasmer**:
   - Ve al panel de control
   - Selecciona "Importar desde GitHub"
   - Conecta tu cuenta de GitHub
   - Selecciona el repositorio `cashspace`
   - Configura las opciones de despliegue

#### Método 2: Subir Archivos Directamente

1. **Comprime tu proyecto**:
   ```bash
   # En Windows (PowerShell)
   Compress-Archive -Path * -DestinationPath cashspace.zip -Force
   
   # En Linux/Mac
   zip -r cashspace.zip . -x "*.git*" -x "node_modules/*"
   ```

2. **En Wasmer**:
   - Ve al panel de control
   - Selecciona "Arrastra y suelta tu sitio web"
   - Arrastra el archivo ZIP o selecciona los archivos
   - Espera a que se procese

### Paso 4: Configurar Variables de Entorno

En el panel de Wasmer, configura estas variables:

```env
# Base de Datos
DB_HOST=tu-host-de-base-de-datos
DB_USER=tu-usuario
DB_PASS=tu-contraseña
DB_NAME=cashspace
DB_PORT=3306

# Aplicación
BASE_URL=https://tu-dominio.wasmer.app
ENVIRONMENT=production
```

**Nota**: Usa el archivo `config/database.production.php` que ya está preparado para leer variables de entorno. En producción, puedes renombrarlo a `database.php` o modificar `config.php` para incluirlo condicionalmente.

### Paso 5: Configurar Base de Datos

1. **Importar esquema**:
   - Conéctate a tu base de datos externa
   - Ejecuta el archivo `database/cashspace.sql`
   - O usa phpMyAdmin/MySQL Workbench

2. **Verificar conexión**:
   - Crea un archivo `test_db.php` temporal:
   ```php
   <?php
   require_once 'config/config.php';
   $conn = getConnection();
   if ($conn) {
       echo "Conexión exitosa!";
   } else {
       echo "Error de conexión";
   }
   ?>
   ```

### Paso 6: Configurar Permisos y Rutas

#### 6.1. Crear directorio de logs
```bash
# En Wasmer, asegúrate de que exista:
logs/
  - error.log
  - access.log
```

#### 6.2. Verificar rutas de archivos
- Ajusta las rutas en `config/config.php` si es necesario
- Verifica que `BASE_URL` se detecte correctamente

### Paso 7: Configurar Dominio (Opcional)

1. En el panel de Wasmer:
   - Ve a "Configuración" → "Dominios"
   - Agrega tu dominio personalizado (si tienes uno)
   - O usa el dominio proporcionado por Wasmer (ej: `tu-proyecto.wasmer.app`)

### Paso 8: Verificar Despliegue

1. **Accede a tu URL**:
   ```
   https://tu-proyecto.wasmer.app
   ```

2. **Verifica**:
   - ✅ Página de inicio carga
   - ✅ Login funciona
   - ✅ Base de datos conecta
   - ✅ Imágenes cargan
   - ✅ CSS/JS cargan correctamente

3. **Prueba funcionalidades**:
   - Registro de usuario
   - Login
   - Recarga
   - Inversiones

## 🔧 Solución de Problemas

### Error: "No se puede conectar a la base de datos"
- Verifica las variables de entorno
- Asegúrate de que la base de datos externa permita conexiones remotas
- Revisa el firewall de la base de datos

### Error: "Archivo no encontrado"
- Verifica las rutas en `config/config.php`
- Asegúrate de que `BASE_URL` esté configurado correctamente
- Revisa los permisos de archivos

### Error: "Sesión no funciona"
- Verifica que las sesiones estén habilitadas
- Revisa la configuración de cookies
- Asegúrate de que `session_start()` se llame correctamente

### Imágenes no cargan
- Verifica las rutas de las imágenes
- Asegúrate de que la carpeta `assets/images/` esté incluida
- Revisa los permisos de archivos

## 📝 Checklist Pre-Despliegue

- [ ] Archivos de configuración ajustados para producción
- [ ] Variables de entorno configuradas
- [ ] Base de datos creada e importada
- [ ] Archivos sensibles (como `.env`) no están en el repositorio
- [ ] Logs configurados
- [ ] Errores ocultos en producción
- [ ] Pruebas locales completadas

## 🔐 Seguridad

1. **Oculta información sensible**:
   - No subas archivos con contraseñas
   - Usa variables de entorno
   - Oculta errores en producción

2. **Configura HTTPS**:
   - Wasmer debería proporcionar HTTPS automáticamente
   - Verifica que funcione correctamente

3. **Permisos de archivos**:
   - Archivos: 644
   - Directorios: 755
   - Logs: 600 (solo lectura/escritura para el servidor)

## 📚 Recursos Adicionales

- [Documentación de Wasmer](https://wasmer.io/es/ayuda/guia)
- [PHP en Wasmer](https://wasmer.io/es/plantillas/php)
- [Configuración de Base de Datos Externa](https://wasmer.io/es/ayuda/base-de-datos)

## ⚠️ Notas Importantes

1. **Base de Datos**: Wasmer puede no ofrecer MySQL directamente. Considera usar un servicio externo como PlanetScale, Railway, o Aiven.

2. **PHP Version**: Verifica que Wasmer soporte la versión de PHP que necesitas (probablemente 8.0+).

3. **Límites**: El plan gratuito de Wasmer puede tener límites. Revisa los términos.

4. **Backups**: Configura backups regulares de tu base de datos.

5. **Monitoreo**: Configura alertas y monitoreo para tu aplicación.

## 🆘 Soporte

Si encuentras problemas:
1. Revisa los logs en Wasmer
2. Verifica la documentación de Wasmer
3. Contacta el soporte de Wasmer
4. Revisa los logs de errores de PHP

---

**Última actualización**: 2024
**Versión del proyecto**: CashSpace 1.0

