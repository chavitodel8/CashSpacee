# ⚡ Instrucciones Rápidas: Railway

## 🎯 Estás en la Pantalla "New project" - Sigue estos pasos:

### 1️⃣ Conectar GitHub (Opción 1 - GitHub Repository)

1. **Haz clic en "GitHub Repository"** (ya está seleccionado)
2. **Autoriza Railway**:
   - Si es la primera vez, GitHub te pedirá autorizar Railway
   - Haz clic en "Authorize Railway" o "Autorizar Railway"
   - Selecciona los repositorios (o todos)
3. **Selecciona tu repositorio**:
   - Busca el repositorio donde está CashSpace
   - Si no lo tienes en GitHub, ve a la sección "Si no tienes GitHub" más abajo
4. **Railway detectará PHP automáticamente**
5. **Haz clic en "Deploy"** o deja que se despliegue automáticamente

### 2️⃣ Crear Base de Datos MySQL

**Después de que se despliegue tu proyecto:**

1. En el panel de tu proyecto, verás un botón **"+ New"** o **"+ Nuevo"**
2. Haz clic → Selecciona **"Database"** → **"MySQL"**
3. Railway creará la base de datos automáticamente

### 3️⃣ Obtener Credenciales de MySQL

1. Haz clic en el servicio **MySQL** que acabas de crear
2. Ve a la pestaña **"Variables"**
3. **Copia estos valores**:
   - `MYSQLHOST` 
   - `MYSQLUSER`
   - `MYSQLPASSWORD`
   - `MYSQLDATABASE`
   - `MYSQLPORT`

### 4️⃣ Configurar Variables en tu Proyecto PHP

1. Haz clic en tu **servicio PHP** (el proyecto principal, no MySQL)
2. Ve a **"Variables"**
3. **Agrega estas variables** (usa los valores que copiaste):

```
DB_HOST=<valor de MYSQLHOST>
DB_USER=<valor de MYSQLUSER>
DB_PASS=<valor de MYSQLPASSWORD>
DB_NAME=<valor de MYSQLDATABASE>
DB_PORT=<valor de MYSQLPORT>
ENVIRONMENT=production
```

**Nota**: El archivo `config/database.php` ya está actualizado para leer estas variables automáticamente.

### 5️⃣ Importar Base de Datos

1. En el servicio **MySQL**, ve a la pestaña **"Data"**
2. Haz clic en **"Connect"**
3. Te dará una URL de conexión
4. Usa un cliente MySQL (MySQL Workbench, DBeaver, HeidiSQL) para conectarte
5. Importa el archivo `database/cashspace.sql`

**O usa este método rápido:**

Crea un archivo temporal `import_db.php` en tu proyecto:

```php
<?php
require_once 'config/config.php';
$sql = file_get_contents('database/cashspace.sql');
$commands = array_filter(array_map('trim', explode(';', $sql)));
$conn = getConnection();
foreach ($commands as $command) {
    if (!empty($command) && !preg_match('/^--/', $command)) {
        $conn->query($command);
    }
}
echo "Base de datos importada!";
?>
```

Accede a `tu-url.railway.app/import_db.php` una vez, luego elimínalo.

### 6️⃣ Verificar

1. Railway te dará una URL tipo: `tu-proyecto.railway.app`
2. Accede a esa URL
3. ¡Debería funcionar! 🎉

---

## ❓ Si NO Tienes el Proyecto en GitHub

### Subir a GitHub Primero:

1. **Abre PowerShell o Terminal** en la carpeta de tu proyecto

2. **Inicializa Git** (si no lo has hecho):
   ```bash
   git init
   git add .
   git commit -m "Preparado para Railway"
   ```

3. **Crea repositorio en GitHub**:
   - Ve a https://github.com
   - Haz clic en "New repository" (botón verde)
   - Nómbralo "cashspace"
   - **NO marques** README, .gitignore, o licencia
   - Haz clic en "Create repository"

4. **Conecta y sube**:
   ```bash
   git remote add origin https://github.com/tu-usuario/cashspace.git
   git branch -M main
   git push -u origin main
   ```
   (Reemplaza `tu-usuario` con tu nombre de usuario de GitHub)

5. **Vuelve a Railway** y selecciona ese repositorio

---

## 🔧 Solución Rápida de Problemas

### "No se puede conectar a la base de datos"
- ✅ Verifica que agregaste las variables en el servicio PHP (no en MySQL)
- ✅ Asegúrate de que el servicio MySQL esté corriendo
- ✅ Revisa los logs en Railway

### "Base de datos no existe"
- ✅ Importa `database/cashspace.sql` primero
- ✅ Verifica que `DB_NAME` coincida con `MYSQLDATABASE`

### "Error 500"
- ✅ Revisa los logs en Railway (pestaña "Logs")
- ✅ Verifica que todas las variables estén configuradas

---

**¿Necesitas más ayuda?** Lee `GUIA_RAILWAY_PASO_A_PASO.md` para más detalles.

