# 📸 Instrucciones para Agregar Imágenes a las Inversiones

## 📁 Ubicación de las Imágenes

1. Crea la carpeta `assets/images/investments/` si no existe
2. Coloca las imágenes de las inversiones en esa carpeta

## 🖼️ Para "Inversión Básica"

1. Guarda la imagen como: `assets/images/investments/basica.jpg`
   - También puedes usar: `.png`, `.webp`, etc.
   - El sistema buscará automáticamente esta imagen

## 🔧 Actualizar la Base de Datos (Opcional)

Si quieres guardar la ruta en la base de datos, puedes ejecutar este SQL:

```sql
UPDATE tipos_inversion 
SET imagen = 'assets/images/investments/basica.jpg' 
WHERE nombre = 'Inversión Básica';
```

O ejecuta el script: `actualizar_imagen_basica.php` (se creará a continuación)

## 📝 Formato de Nombres de Archivo

Para otros planes, usa estos nombres:
- `basica.jpg` - Inversión Básica
- `plus.jpg` - Inversión Plus
- `premium.jpg` - Inversión Premium
- `gold.jpg` - Inversión Gold
- `platinum.jpg` - Inversión Platinum
- `elite.jpg` - Inversión Elite
- `diamond.jpg` - Inversión Diamond
- `master.jpg` - Inversión Master
- `supreme.jpg` - Inversión Supreme
- `ultimate.jpg` - Inversión Ultimate

## ✅ El sistema automáticamente:

- Muestra la imagen si existe en la ruta esperada
- Si no encuentra la imagen, muestra el icono 💎 por defecto
- Funciona tanto en la lista de inversiones como en la página de detalle

