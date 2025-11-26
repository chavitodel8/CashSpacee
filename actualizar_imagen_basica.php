<?php
/**
 * Script para actualizar la imagen de "Inversión Básica"
 * Ejecuta desde el navegador: http://localhost/CashSpace/actualizar_imagen_basica.php
 */

require_once 'config/config.php';

$conn = getConnection();

// Ruta de la imagen
$ruta_imagen = 'assets/images/investments/basica.jpg';

// Verificar si el archivo existe
$archivo_existe = file_exists($ruta_imagen);

// Actualizar en la base de datos
$stmt = $conn->prepare("UPDATE tipos_inversion SET imagen = ? WHERE nombre = 'Inversión Básica'");
$stmt->bind_param("s", $ruta_imagen);

if ($stmt->execute()) {
    $exito = true;
} else {
    $exito = false;
    $error = $stmt->error;
}
$stmt->close();

// Mostrar resultados
echo "<!DOCTYPE html>";
echo "<html lang='es'>";
echo "<head>";
echo "<meta charset='UTF-8'>";
echo "<meta name='viewport' content='width=device-width, initial-scale=1.0'>";
echo "<title>Imagen Actualizada - CashSpace</title>";
echo "<style>";
echo "body { font-family: Arial, sans-serif; padding: 20px; background: #f5f5f5; }";
echo ".container { max-width: 600px; margin: 0 auto; background: white; padding: 30px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }";
echo "h1 { color: #044990; }";
echo ".success { background: #d1fae5; color: #065f46; padding: 15px; border-radius: 8px; margin: 20px 0; }";
echo ".warning { background: #fef3c7; color: #92400e; padding: 15px; border-radius: 8px; margin: 20px 0; }";
echo ".error { background: #fee2e2; color: #991b1b; padding: 15px; border-radius: 8px; margin: 20px 0; }";
echo ".info { background: #dbeafe; color: #1e40af; padding: 15px; border-radius: 8px; margin: 20px 0; }";
echo "</style>";
echo "</head>";
echo "<body>";
echo "<div class='container'>";
echo "<h1>🖼️ Actualización de Imagen - Inversión Básica</h1>";

if ($exito) {
    echo "<div class='success'>";
    echo "✅ La ruta de la imagen se actualizó exitosamente en la base de datos.";
    echo "</div>";
} else {
    echo "<div class='error'>";
    echo "❌ Error al actualizar: " . htmlspecialchars($error);
    echo "</div>";
}

if ($archivo_existe) {
    echo "<div class='success'>";
    echo "✅ El archivo de imagen existe en: <strong>{$ruta_imagen}</strong>";
    echo "<br><br>";
    echo "<img src='{$ruta_imagen}' alt='Inversión Básica' style='max-width: 100%; border-radius: 10px; margin-top: 10px;'>";
    echo "</div>";
} else {
    echo "<div class='warning'>";
    echo "⚠️ El archivo de imagen NO existe en: <strong>{$ruta_imagen}</strong>";
    echo "<br><br>";
    echo "<strong>Instrucciones:</strong>";
    echo "<ol>";
    echo "<li>Crea la carpeta: <code>assets/images/investments/</code></li>";
    echo "<li>Coloca tu imagen como: <code>basica.jpg</code> (o .png, .webp)</li>";
    echo "<li>Vuelve a ejecutar este script</li>";
    echo "</ol>";
    echo "</div>";
}

echo "<div class='info'>";
echo "<strong>ℹ️ Nota:</strong> El sistema mostrará la imagen automáticamente si existe en la ruta, incluso sin actualizar la base de datos.";
echo "</div>";

echo "<p style='margin-top: 20px;'><a href='index.php' style='color: #044990; text-decoration: none; font-weight: 600;'>← Volver al inicio</a></p>";
echo "</div>";
echo "</body>";
echo "</html>";

closeConnection($conn);
?>

