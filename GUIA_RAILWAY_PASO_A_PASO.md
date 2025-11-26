# 🚂 Guía Paso a Paso: Railway para CashSpace

## Paso 1: Conectar GitHub Repository

### 1.1. Selecciona "GitHub Repository"
- Haz clic en la opción que está resaltada (GitHub Repository)
- Te pedirá autorizar Railway para acceder a tus repositorios

### 1.2. Autorizar Railway
- Si es la primera vez, GitHub te pedirá autorizar Railway
- Haz clic en "Authorize Railway" o "Autorizar Railway"
- Selecciona los repositorios que quieres dar acceso (o todos)

### 1.3. Seleccionar Repositorio
- Railway mostrará una lista de tus repositorios
- Busca y selecciona el repositorio donde está tu proyecto CashSpace
- Si no lo has subido aún, verás cómo hacerlo más abajo

### 1.4. Configurar el Proyecto
- Railway detectará automáticamente que es PHP
- Puedes dejar la configuración por defecto
- Haz clic en "Deploy" o "Desplegar"

## Paso 2: Crear Base de Datos MySQL

### 2.1. Agregar Base de Datos
Una vez que tu proyecto esté desplegado:

1. En el panel de tu proyecto, verás un botón **"+ New"** o **"+ Nuevo"**
2. Haz clic en él
3. Selecciona **"Database"** → **"MySQL"**
4. Railway creará automáticamente la base de datos MySQL

### 2.2. Obtener Credenciales
1. Haz clic en el servicio MySQL que acabas de crear
2. Ve a la pestaña **"Variables"** o **"Variables de Entorno"**
3. Verás estas variables (copia los valores):
   - `MYSQLHOST` → Este es tu **DB_HOST**
   - `MYSQLUSER` → Este es tu **DB_USER**
   - `MYSQLPASSWORD` → Este es tu **DB_PASS**
   - `MYSQLDATABASE` → Este es tu **DB_NAME**
   - `MYSQLPORT` → Este es tu **DB_PORT** (generalmente 3306)

### 2.3. Importar Esquema de Base de Datos

**Opción A: Usando el Panel de Railway (Más Fácil)**
1. En el servicio MySQL, ve a la pestaña **"Data"**
2. Haz clic en **"Connect"** o **"Conectar"**
3. Te dará una URL de conexión tipo: `mysql://usuario:contraseña@host:puerto/base_de_datos`
4. Usa esta URL con un cliente MySQL como:
   - **MySQL Workbench** (recomendado)
   - **phpMyAdmin** (si tienes acceso)
   - **DBeaver** (gratis)
   - **HeidiSQL** (Windows)

**Opción B: Usando la Terminal de Railway**
1. En el servicio MySQL, ve a la pestaña **"Connect"**
2. Copia el comando de conexión
3. Usa Railway CLI o conecta desde tu terminal local

**Opción C: Usando un Script PHP Temporal**
Crea un archivo `import_db.php` en tu proyecto:

```php
<?php
require_once 'config/config.php';

$sql_file = 'database/cashspace.sql';
$sql = file_get_contents($sql_file);

// Dividir en comandos individuales
$commands = array_filter(array_map('trim', explode(';', $sql)));

$conn = getConnection();

foreach ($commands as $command) {
    if (!empty($command) && !preg_match('/^--/', $command)) {
        if (!$conn->query($command)) {
            echo "Error: " . $conn->error . "\n";
        }
    }
}

echo "Base de datos importada exitosamente!";
$conn->close();
?>
```

Luego ejecútalo una vez desde Railway o localmente con las credenciales correctas.

## Paso 3: Configurar Variables de Entorno en Railway

### 3.1. En tu Proyecto PHP (no en MySQL)
1. Haz clic en tu servicio PHP (el proyecto principal)
2. Ve a la pestaña **"Variables"**
3. Agrega estas variables:

```
DB_HOST=<valor de MYSQLHOST>
DB_USER=<valor de MYSQLUSER>
DB_PASS=<valor de MYSQLPASSWORD>
DB_NAME=<valor de MYSQLDATABASE>
DB_PORT=<valor de MYSQLPORT>
ENVIRONMENT=production
```

### 3.2. Actualizar database.php
Asegúrate de que `config/database.php` lea las variables de entorno. Si no lo hace, usa `config/database.production.php` o modifica `database.php`:

```php
<?php
// Leer de variables de entorno o usar valores por defecto
$db_host = getenv('DB_HOST') ?: 'localhost';
$db_user = getenv('DB_USER') ?: 'root';
$db_pass = getenv('DB_PASS') ?: '';
$db_name = getenv('DB_NAME') ?: 'cashspace';
$db_port = getenv('DB_PORT') ?: 3306;

define('DB_HOST', $db_host);
define('DB_USER', $db_user);
define('DB_PASS', $db_pass);
define('DB_NAME', $db_name);
define('DB_PORT', $db_port);
// ... resto del código
?>
```

## Paso 4: Verificar Despliegue

1. Railway te dará una URL tipo: `tu-proyecto.railway.app`
2. Accede a esa URL
3. Verifica que:
   - ✅ La página carga
   - ✅ Puedes registrarte/iniciar sesión
   - ✅ La base de datos funciona

## ⚠️ Si No Tienes el Proyecto en GitHub

### Subir a GitHub Primero:

1. **Inicializar Git** (si no lo has hecho):
   ```bash
   git init
   git add .
   git commit -m "Preparado para Railway"
   ```

2. **Crear repositorio en GitHub**:
   - Ve a https://github.com
   - Haz clic en "New repository"
   - Nómbralo "cashspace" (o el nombre que prefieras)
   - **NO** inicialices con README, .gitignore, o licencia
   - Haz clic en "Create repository"

3. **Conectar y subir**:
   ```bash
   git remote add origin https://github.com/tu-usuario/cashspace.git
   git branch -M main
   git push -u origin main
   ```

4. **Luego vuelve a Railway** y selecciona ese repositorio

## 🔧 Solución de Problemas

### Error: "No se puede conectar a la base de datos"
- Verifica que las variables de entorno estén configuradas correctamente
- Asegúrate de que el servicio MySQL esté corriendo
- Verifica que el puerto sea correcto

### Error: "Base de datos no existe"
- Asegúrate de haber importado `database/cashspace.sql`
- Verifica que `DB_NAME` coincida con el nombre de la base de datos

### Error: "Tabla no existe"
- Importa el esquema completo desde `database/cashspace.sql`
- Verifica que todas las tablas se hayan creado

## 📝 Checklist

- [ ] Proyecto conectado desde GitHub
- [ ] Servicio PHP desplegado
- [ ] Base de datos MySQL creada
- [ ] Credenciales copiadas
- [ ] Esquema de base de datos importado
- [ ] Variables de entorno configuradas
- [ ] Aplicación funcionando en la URL de Railway

---

**¿Necesitas ayuda?** Revisa los logs en Railway haciendo clic en tu servicio y luego en "Logs".

