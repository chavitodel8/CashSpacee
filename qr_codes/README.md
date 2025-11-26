# Códigos QR Personalizados

Esta carpeta es para almacenar tus códigos QR personalizados por monto.

## Cómo agregar tus QRs:

1. **Sube tus imágenes QR aquí:**
   - `qr_100.png` - QR para 100 Bs
   - `qr_200.png` - QR para 200 Bs
   - `qr_500.png` - QR para 500 Bs
   - `qr_1000.png` - QR para 1000 Bs
   - `qr_2000.png` - QR para 2000 Bs
   - `qr_5000.png` - QR para 5000 Bs
   - `qr_10000.png` - QR para 10000 Bs

2. **Configura las rutas en `config/qr_codes.php`:**
   ```php
   '100' => 'qr_codes/qr_100.png',
   '200' => 'qr_codes/qr_200.png',
   // etc...
   ```

3. **O usa Base64 (recomendado para QRs estáticos):**
   - Convierte tu imagen a base64 usando un conversor online
   - O usa PHP: `base64_encode(file_get_contents('qr_codes/qr_100.png'))`
   - Pega el resultado en `config/qr_codes.php`:
   ```php
   '100' => 'data:image/png;base64,iVBORw0KGgo...',
   ```

## Formato de imagen recomendado:
- Formato: PNG o JPG
- Tamaño: 300x300 píxeles o más
- Resolución: Mínimo 300 DPI para mejor calidad

