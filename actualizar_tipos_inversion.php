<?php
/**
 * Script para actualizar los tipos de inversión a activos reales
 * Ejecuta desde el navegador: http://localhost/CashSpace/actualizar_tipos_inversion.php
 */

require_once 'config/config.php';

$conn = getConnection();

// Nuevos tipos de inversión basados en activos reales
$nuevos_tipos = [
    [
        'id' => 1,
        'nombre' => 'Inversión en Bienes Raíces',
        'descripcion' => 'Invierte en propiedades inmobiliarias. Participa en proyectos de construcción y desarrollo urbano con rendimientos garantizados.',
        'precio_inversion' => 100.00,
        'ganancia_diaria' => 12.00,
        'ganancia_mensual' => 300.00
    ],
    [
        'id' => 2,
        'nombre' => 'Inversión en Productos',
        'descripcion' => 'Inversión en comercio y distribución de productos. Participa en la cadena de suministro y obtén ganancias del comercio.',
        'precio_inversion' => 200.00,
        'ganancia_diaria' => 25.00,
        'ganancia_mensual' => 600.00
    ],
    [
        'id' => 3,
        'nombre' => 'Inversión en Vehículos',
        'descripcion' => 'Invierte en flota de vehículos para transporte y logística. Genera ingresos pasivos del alquiler y transporte comercial.',
        'precio_inversion' => 500.00,
        'ganancia_diaria' => 65.00,
        'ganancia_mensual' => 1500.00
    ],
    [
        'id' => 4,
        'nombre' => 'Inversión en Tecnología',
        'descripcion' => 'Inversión en equipos tecnológicos y software. Participa en proyectos de innovación digital con alto potencial de crecimiento.',
        'precio_inversion' => 1000.00,
        'ganancia_diaria' => 130.00,
        'ganancia_mensual' => 3000.00
    ],
    [
        'id' => 5,
        'nombre' => 'Inversión en Energía',
        'descripcion' => 'Inversión en proyectos de energía renovable. Participa en la transición energética y obtén rendimientos sostenibles.',
        'precio_inversion' => 2000.00,
        'ganancia_diaria' => 260.00,
        'ganancia_mensual' => 6000.00
    ],
    [
        'id' => 6,
        'nombre' => 'Inversión en Agricultura',
        'descripcion' => 'Inversión en proyectos agrícolas y agroindustriales. Participa en la producción de alimentos y materias primas.',
        'precio_inversion' => 5000.00,
        'ganancia_diaria' => 650.00,
        'ganancia_mensual' => 15000.00
    ],
    [
        'id' => 7,
        'nombre' => 'Inversión en Infraestructura',
        'descripcion' => 'Inversión en proyectos de infraestructura pública y privada. Participa en construcción de carreteras, puentes y obras civiles.',
        'precio_inversion' => 10000.00,
        'ganancia_diaria' => 1300.00,
        'ganancia_mensual' => 30000.00
    ]
];

$actualizados = 0;
$errores = [];

foreach ($nuevos_tipos as $tipo) {
    $stmt = $conn->prepare("UPDATE tipos_inversion SET nombre = ?, descripcion = ? WHERE id = ?");
    $stmt->bind_param("ssi", $tipo['nombre'], $tipo['descripcion'], $tipo['id']);
    
    if ($stmt->execute()) {
        $actualizados++;
    } else {
        $errores[] = "Error al actualizar ID {$tipo['id']}: " . $stmt->error;
    }
    $stmt->close();
}

// Mostrar resultados
echo "<!DOCTYPE html>";
echo "<html lang='es'>";
echo "<head>";
echo "<meta charset='UTF-8'>";
echo "<meta name='viewport' content='width=device-width, initial-scale=1.0'>";
echo "<title>Tipos de Inversión Actualizados - CashSpace</title>";
echo "<style>";
echo "body { font-family: Arial, sans-serif; padding: 20px; background: #f5f5f5; }";
echo ".container { max-width: 900px; margin: 0 auto; background: white; padding: 30px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }";
echo "h1 { color: #044990; }";
echo ".success { background: #d1fae5; color: #065f46; padding: 15px; border-radius: 8px; margin: 20px 0; }";
echo ".error { background: #fee2e2; color: #991b1b; padding: 15px; border-radius: 8px; margin: 10px 0; }";
echo "table { width: 100%; border-collapse: collapse; margin-top: 20px; }";
echo "th, td { padding: 12px; text-align: left; border-bottom: 1px solid #ddd; }";
echo "th { background: #044990; color: white; }";
echo "tr:hover { background: #f9fafb; }";
echo ".desc { font-size: 13px; color: #6b7280; max-width: 300px; }";
echo "</style>";
echo "</head>";
echo "<body>";
echo "<div class='container'>";
echo "<h1>✅ Tipos de Inversión Actualizados</h1>";

if ($actualizados > 0) {
    echo "<div class='success'>";
    echo "✅ Se actualizaron exitosamente <strong>{$actualizados}</strong> tipo(s) de inversión.";
    echo "</div>";
}

if (!empty($errores)) {
    echo "<div class='error'>";
    echo "<strong>⚠️ Errores:</strong><ul>";
    foreach ($errores as $error) {
        echo "<li>{$error}</li>";
    }
    echo "</ul></div>";
}

// Mostrar todos los planes actualizados
$result = $conn->query("SELECT id, nombre, descripcion, precio_inversion, ganancia_diaria, ganancia_mensual, limite_inversion FROM tipos_inversion WHERE estado = 'activo' ORDER BY precio_inversion ASC");

echo "<h2>📋 Tipos de Inversión Actualizados:</h2>";
echo "<table>";
echo "<tr><th>ID</th><th>Nombre</th><th>Descripción</th><th>Inversión</th><th>Ganancia/Día</th><th>Ganancia/Mes</th></tr>";

while ($row = $result->fetch_assoc()) {
    echo "<tr>";
    echo "<td>{$row['id']}</td>";
    echo "<td><strong>{$row['nombre']}</strong></td>";
    echo "<td class='desc'>{$row['descripcion']}</td>";
    echo "<td>" . number_format($row['precio_inversion'], 2, ',', '.') . " Bs</td>";
    echo "<td style='color: #10b981; font-weight: 600;'>+" . number_format($row['ganancia_diaria'], 2, ',', '.') . " Bs</td>";
    echo "<td style='color: #059669; font-weight: 600;'>" . number_format($row['ganancia_mensual'], 2, ',', '.') . " Bs</td>";
    echo "</tr>";
}

echo "</table>";
echo "<p style='margin-top: 20px;'><a href='acerca_de.php' style='color: #044990; text-decoration: none; font-weight: 600;'>Ver página &quot;Acerca de&quot; actualizada →</a></p>";
echo "<p><a href='index.php' style='color: #044990; text-decoration: none; font-weight: 600;'>← Volver al inicio</a></p>";
echo "</div>";
echo "</body>";
echo "</html>";

closeConnection($conn);
?>

