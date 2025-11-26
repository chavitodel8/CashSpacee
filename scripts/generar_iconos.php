<?php
/**
 * Script para generar iconos PWA desde el logo existente
 * Ejecuta este script una vez para generar los iconos necesarios
 * Requiere la extensión GD de PHP
 */

// Verificar si GD está disponible
if (!extension_loaded('gd')) {
    die('Error: La extensión GD de PHP no está disponible. Por favor, habilítala en php.ini');
}

// Ruta del logo original
$logoPath = __DIR__ . '/../assets/images/logo.png';

if (!file_exists($logoPath)) {
    die('Error: No se encontró el logo en: ' . $logoPath);
}

// Crear directorio de iconos si no existe
$iconsDir = __DIR__ . '/../assets/images/icons/';
if (!is_dir($iconsDir)) {
    mkdir($iconsDir, 0755, true);
}

// Tamaños de iconos requeridos
$sizes = [
    192 => 'icon-192x192.png',
    512 => 'icon-512x512.png',
    180 => 'apple-touch-icon.png' // Para iOS
];

// Cargar la imagen original
$sourceImage = imagecreatefrompng($logoPath);
if (!$sourceImage) {
    die('Error: No se pudo cargar la imagen del logo');
}

$sourceWidth = imagesx($sourceImage);
$sourceHeight = imagesy($sourceImage);

echo "Generando iconos PWA...\n\n";

foreach ($sizes as $size => $filename) {
    // Crear nueva imagen con el tamaño requerido
    $newImage = imagecreatetruecolor($size, $size);
    
    // Hacer el fondo transparente
    imagealphablending($newImage, false);
    imagesavealpha($newImage, true);
    $transparent = imagecolorallocatealpha($newImage, 0, 0, 0, 127);
    imagefill($newImage, 0, 0, $transparent);
    
    // Calcular dimensiones para mantener la proporción
    $ratio = min($size / $sourceWidth, $size / $sourceHeight);
    $newWidth = $sourceWidth * $ratio;
    $newHeight = $sourceHeight * $ratio;
    $x = ($size - $newWidth) / 2;
    $y = ($size - $newHeight) / 2;
    
    // Redimensionar y copiar la imagen
    imagealphablending($newImage, true);
    imagecopyresampled(
        $newImage, 
        $sourceImage, 
        $x, $y, 0, 0, 
        $newWidth, $newHeight, 
        $sourceWidth, $sourceHeight
    );
    
    // Guardar la imagen
    $outputPath = $iconsDir . $filename;
    if (imagepng($newImage, $outputPath)) {
        echo "✓ Generado: {$filename} ({$size}x{$size})\n";
    } else {
        echo "✗ Error al generar: {$filename}\n";
    }
    
    imagedestroy($newImage);
}

imagedestroy($sourceImage);

echo "\n¡Iconos generados exitosamente!\n";
echo "Ubicación: {$iconsDir}\n";
echo "\nAhora actualiza el manifest.json con las rutas correctas.\n";
?>

