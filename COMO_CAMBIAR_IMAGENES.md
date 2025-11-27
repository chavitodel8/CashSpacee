# 🖼️ Cómo Cambiar las Imágenes de las Inversiones

## 📍 Ubicación de las Imágenes

Las imágenes de las inversiones están en:
```
assets/images/investments/
```

## 🔄 Pasos para Cambiar una Imagen

### 1. **Prepara tu nueva imagen**
   - Formatos soportados: `.webp` ✅ (recomendado, más liviano), `.png`, `.jpg` / `.jpeg`, `.gif`
   - Tamaño recomendado: 800x600 píxeles o similar
   - Nombre: debe coincidir con el nombre del plan (ver abajo)

### 2. **Nombres de archivo correctos**

Cada plan de inversión tiene un nombre específico que debes usar:

| Plan de Inversión | Nombre del archivo |
|-------------------|-------------------|
| Inversión Básica  | `basica.webp` (o `.png`, `.jpg`) |
| Inversión Plus    | `plus.webp` (o `.png`, `.jpg`) |
| Inversión Premium | `premium.webp` (o `.png`, `.jpg`) |
| Inversión Gold    | `gold.webp` (o `.png`, `.jpg`) |
| Inversión Platinum| `platinum.webp` (o `.png`, `.jpg`) |
| Inversión Elite   | `elite.webp` (o `.png`, `.jpg`) |
| Inversión Diamond | `diamond.webp` (o `.png`, `.jpg`) |
| Inversión Master  | `master.webp` (o `.png`, `.jpg`) |
| Inversión Supreme | `supreme.webp` (o `.png`, `.jpg`) |
| Inversión Ultimate| `ultimate.webp` (o `.png`, `.jpg`) |

### 3. **Reemplaza la imagen**

**Opción A: Desde el explorador de archivos**
1. Ve a la carpeta: `D:\XAMPP\htdocs\CashSpace\assets\images\investments\`
2. Busca la imagen que quieres cambiar (ej: `basica.png`)
3. Reemplázala con tu nueva imagen (debe tener el mismo nombre)
4. Si tu imagen tiene otro nombre, renómbrala al nombre correcto

**Opción B: Desde Cursor/Editor**
1. Abre la carpeta `assets/images/investments/`
2. Arrastra tu nueva imagen a esa carpeta
3. Si ya existe una imagen con ese nombre, reemplázala

### 4. **Formatos soportados**

El sistema acepta estos formatos (en orden de prioridad):
- `.webp` ✅ **Recomendado** (más liviano, mejor calidad)
- `.png` ✅ (transparencia)
- `.jpg` / `.jpeg` ✅ (compatible universal)
- `.gif` ✅ (animaciones)

### 5. **Verificar que funciona**

1. Recarga la página de **Ingresos** (`ingresos.php`)
2. Recarga la página de **Inicio** (`index.php`)
3. Deberías ver tu nueva imagen en lugar de la anterior

## ⚠️ Importante

- **El nombre del archivo DEBE ser exactamente** el que aparece en la tabla de arriba
- Si cambias `basica.png` por `basica_nueva.png`, **NO funcionará**
- El sistema busca automáticamente la imagen, no necesitas cambiar código
- Si la imagen no aparece, verifica:
  - ✅ El nombre del archivo es correcto
  - ✅ La imagen está en `assets/images/investments/`
  - ✅ El formato es `.png`, `.jpg`, `.jpeg`, `.webp` o `.gif`
  - ✅ Recargaste la página (Ctrl+F5 para limpiar caché)

## 📝 Ejemplo Práctico

**Quieres cambiar la imagen de "Inversión Básica":**

1. Tienes una imagen llamada `mi_imagen_basica.webp` (o `.jpg`, `.png`)
2. Renómbrala a: `basica.webp` (o `basica.png`, `basica.jpg`)
3. Cópiala a: `D:\XAMPP\htdocs\CashSpace\assets\images\investments\`
4. Si ya existe `basica.png` o `basica.jpg`, reemplázala
5. ¡Listo! La nueva imagen aparecerá automáticamente

**Nota:** Si tienes `basica.webp` y `basica.png`, el sistema usará `.webp` primero (prioridad).

## 🚀 Después de cambiar las imágenes

Si estás usando Railway (producción):
1. Haz commit de los cambios en GitHub Desktop
2. Haz push a GitHub
3. Railway desplegará automáticamente con las nuevas imágenes

