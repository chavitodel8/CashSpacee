# 📤 Subir Proyecto a GitHub SIN CMD/PowerShell

Esta guía te muestra cómo subir tu proyecto CashSpace a GitHub usando solo la interfaz gráfica, sin escribir comandos.

## 🎯 Método 1: GitHub Desktop (Más Fácil) ⭐ RECOMENDADO

### Paso 1: Descargar GitHub Desktop

1. Ve a: https://desktop.github.com
2. Haz clic en **"Download for Windows"**
3. Instala el programa (sigue el asistente de instalación)
4. Abre GitHub Desktop

### Paso 2: Iniciar Sesión

1. Abre **GitHub Desktop**
2. Si no tienes cuenta de GitHub:
   - Haz clic en **"Sign up for GitHub"**
   - Crea tu cuenta (es gratis)
   - Verifica tu email
3. Si ya tienes cuenta:
   - Haz clic en **"Sign in to GitHub.com"**
   - Ingresa tus credenciales

### Paso 3: Crear Repositorio en GitHub

1. Ve a https://github.com en tu navegador
2. Inicia sesión
3. Haz clic en el botón **"+"** (arriba a la derecha)
4. Selecciona **"New repository"**
5. Completa:
   - **Repository name**: `cashspace` (o el nombre que quieras)
   - **Description**: "Proyecto CashSpace" (opcional)
   - **Public** o **Private** (elige según prefieras)
   - **NO marques** "Add a README file"
   - **NO marques** "Add .gitignore"
   - **NO marques** "Choose a license"
6. Haz clic en **"Create repository"**

### Paso 4: Agregar Proyecto en GitHub Desktop

1. En **GitHub Desktop**, haz clic en **"File"** → **"Add Local Repository"**
   - O haz clic en el botón **"+"** → **"Add Existing Repository"**
2. Haz clic en **"Choose..."**
3. Navega hasta tu carpeta del proyecto: `D:\XAMPP\htdocs\CashSpace`
4. Selecciona la carpeta **CashSpace**
5. Haz clic en **"Add repository"**

### Paso 5: Hacer el Primer Commit

1. En GitHub Desktop verás todos tus archivos listados
2. En la parte inferior izquierda, escribe un mensaje: **"Primera versión - CashSpace"**
3. Haz clic en el botón **"Commit to main"** (abajo a la izquierda)
4. Espera a que termine (verás una barra de progreso)

### Paso 6: Publicar en GitHub

1. Después del commit, verás un botón **"Publish repository"**
2. Haz clic en **"Publish repository"**
3. Selecciona el repositorio que creaste antes (si no aparece, selecciónalo del menú)
4. **NO marques** "Keep this code private" (a menos que quieras que sea privado)
5. Haz clic en **"Publish repository"**
6. ¡Listo! Tu proyecto está en GitHub 🎉

---

## 🎯 Método 2: Usando Solo el Navegador (Sin Instalar Nada)

### Paso 1: Crear Repositorio en GitHub

1. Ve a https://github.com
2. Inicia sesión (o crea cuenta si no tienes)
3. Haz clic en el botón **"+"** (arriba a la derecha)
4. Selecciona **"New repository"**
5. Completa:
   - **Repository name**: `cashspace`
   - **Description**: "Proyecto CashSpace"
   - **Public** o **Private**
   - **SÍ marca** "Add a README file" (esto es importante)
6. Haz clic en **"Create repository"**

### Paso 2: Subir Archivos Manualmente

1. En la página de tu repositorio, haz clic en **"uploading an existing file"**
   - O haz clic en el botón **"Add file"** → **"Upload files"**
2. Arrastra TODA la carpeta `CashSpace` al navegador
   - O haz clic en **"choose your files"** y selecciona todos los archivos
3. En la parte inferior, escribe un mensaje: **"Primera versión"**
4. Haz clic en **"Commit changes"**
5. Espera a que se suban todos los archivos
6. ¡Listo! 🎉

**Nota**: Este método puede tardar si tienes muchos archivos. Es mejor usar GitHub Desktop.

---

## 🎯 Método 3: Usando Visual Studio Code (Si lo tienes)

### Paso 1: Abrir Proyecto en VS Code

1. Abre **Visual Studio Code**
2. **File** → **Open Folder**
3. Selecciona: `D:\XAMPP\htdocs\CashSpace`

### Paso 2: Inicializar Git

1. En VS Code, ve a la pestaña **"Source Control"** (icono de ramificación, lado izquierdo)
2. Haz clic en **"Initialize Repository"**
3. Aparecerá un mensaje, haz clic en **"Initialize"**

### Paso 3: Hacer Commit

1. Verás todos tus archivos listados
2. Haz clic en el **"+"** al lado de cada archivo (o en "Changes" para agregar todos)
3. En el cuadro de mensaje arriba, escribe: **"Primera versión"**
4. Haz clic en el botón **"✓ Commit"** (o presiona Ctrl+Enter)

### Paso 4: Publicar en GitHub

1. Después del commit, verás un botón **"Publish Branch"**
2. Haz clic en **"Publish Branch"**
3. Selecciona **"GitHub"**
4. Si no estás conectado, te pedirá iniciar sesión
5. Elige si quieres que sea **Public** o **Private**
6. Haz clic en **"OK"** o **"Publish"**
7. ¡Listo! 🎉

---

## ✅ Verificar que Funcionó

1. Ve a https://github.com/tu-usuario/cashspace
   - (Reemplaza `tu-usuario` con tu nombre de usuario)
2. Deberías ver todos tus archivos
3. Si los ves, ¡está funcionando! ✅

---

## 🔄 Actualizar Cambios Futuros (GitHub Desktop)

Cada vez que hagas cambios:

1. Abre **GitHub Desktop**
2. Verás tus cambios en la lista
3. Escribe un mensaje (ej: "Agregué nueva función")
4. Haz clic en **"Commit to main"**
5. Haz clic en **"Push origin"** (arriba, botón azul)
6. ¡Listo! Los cambios están en GitHub

---

## 🆘 Solución de Problemas

### "No puedo encontrar mi carpeta"
- Asegúrate de que la ruta sea: `D:\XAMPP\htdocs\CashSpace`
- Verifica que la carpeta existe

### "GitHub Desktop no detecta cambios"
- Cierra y vuelve a abrir GitHub Desktop
- Haz clic en **"Repository"** → **"Reload"**

### "Error al publicar"
- Verifica tu conexión a internet
- Asegúrate de estar iniciado sesión en GitHub
- Intenta de nuevo

### "Los archivos no se suben"
- Verifica que no estén en `.gitignore`
- Asegúrate de hacer commit antes de publicar

---

## 📝 Recomendación

**Usa GitHub Desktop** - Es el método más fácil y visual. No necesitas saber comandos, todo se hace con clics.

---

**¿Listo?** Una vez que tu proyecto esté en GitHub, vuelve a Railway y selecciona ese repositorio. 🚀

