# 📱 Instrucciones para Agregar Códigos QR Personalizados

## Cómo agregar tu QR de 100 Bs (y otros montos)

### Opción 1: Subir imagen a carpeta (Recomendado)

1. **Crea la carpeta `qr_codes`** en la raíz del proyecto (si no existe):
   ```
   CashSpace/
   └── qr_codes/
   ```

2. **Sube tu imagen QR** a esa carpeta:
   - Nombra el archivo: `qr_100.png` (para 100 Bs)
   - Formatos soportados: PNG, JPG, JPEG
   - Tamaño recomendado: 300x300 píxeles o más

3. **Edita el archivo `config/qr_codes.php`**:
   ```php
   return [
       '100' => 'qr_codes/qr_100.png',  // ← Agrega la ruta aquí
       '200' => 'qr_codes/qr_200.png',
       // etc...
   ];
   ```

### Opción 2: Usar Base64 (Para QRs estáticos)

1. **Convierte tu imagen a Base64:**
   - Usa un conversor online: https://www.base64-image.de/
   - O usa PHP:
     ```php
     <?php
     echo base64_encode(file_get_contents('ruta/a/tu/qr_100.png'));
     ?>
     ```

2. **Edita `config/qr_codes.php`**:
   ```php
   return [
       '100' => 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg==',
       // etc...
   ];
   ```

### Opción 3: Usar URL externa

Si tu QR está alojado en otro servidor:

```php
return [
    '100' => 'https://tudominio.com/qr/qr_100.png',
    // etc...
];
```

## Ejemplo completo para 100 Bs

Si tienes tu QR de 100 Bs como imagen:

1. **Guarda la imagen** en: `CashSpace/qr_codes/qr_100.png`

2. **Edita `config/qr_codes.php`**:
   ```php
   return [
       '100' => 'qr_codes/qr_100.png',  // ← Tu QR de 100 Bs
       '200' => '',
       '500' => '',
       '1000' => '',
       '2000' => '',
       '5000' => '',
       '10000' => '',
   ];
   ```

3. **¡Listo!** Cuando un usuario seleccione 100 Bs para recargar, verá tu QR personalizado.

## Verificar que funciona

1. Inicia sesión como usuario
2. Haz clic en "Recargar"
3. Selecciona "100,00 Bs"
4. Selecciona "Transferencia Bancaria"
5. Deberías ver tu QR personalizado aparecer

## Notas importantes

- **Formato de imagen**: PNG es recomendado para mejor calidad
- **Tamaño**: Mínimo 300x300 píxeles para buena legibilidad
- **Nombres de archivo**: Usa nombres descriptivos como `qr_100.png`, `qr_200.png`, etc.
- **Permisos**: Asegúrate de que la carpeta `qr_codes` tenga permisos de lectura

