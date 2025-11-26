<?php
/**
 * Script para restaurar solo los 6 planes de inversión originales
 * (Básica, Plus, Premium, Gold, Platinum, Diamond)
 * Desactiva Master y cualquier otro plan adicional
 * Ejecuta desde el navegador: http://localhost/CashSpace/restaurar_planes_6_originales.php
 */

require_once 'config/config.php';

$conn = getConnection();

// Solo los 6 planes originales (sin Master)
$planes_originales = [
    [
        'id' => 1,
        'nombre' => 'Inversión Básica',
        'descripcion' => 'Perfecta para empezar en el mundo de las inversiones',
        'precio_inversion' => 100.00,
        'ganancia_diaria' => 12.00,
        'ganancia_mensual' => 300.00
    ],
    [
        'id' => 2,
        'nombre' => 'Inversión Plus',
        'descripcion' => 'Inversión ideal para obtener mejores rendimientos',
        'precio_inversion' => 200.00,
        'ganancia_diaria' => 25.00,
        'ganancia_mensual' => 600.00
    ],
    [
        'id' => 3,
        'nombre' => 'Inversión Premium',
        'descripcion' => 'Para inversionistas más experimentados',
        'precio_inversion' => 500.00,
        'ganancia_diaria' => 65.00,
        'ganancia_mensual' => 1500.00
    ],
    [
        'id' => 4,
        'nombre' => 'Inversión Gold',
        'descripcion' => 'Nivel avanzado de inversión',
        'precio_inversion' => 1000.00,
        'ganancia_diaria' => 130.00,
        'ganancia_mensual' => 3000.00
    ],
    [
        'id' => 5,
        'nombre' => 'Inversión Platinum',
        'descripcion' => 'Para inversionistas profesionales',
        'precio_inversion' => 2000.00,
        'ganancia_diaria' => 260.00,
        'ganancia_mensual' => 6000.00
    ],
    [
        'id' => 6,
        'nombre' => 'Inversión Diamond',
        'descripcion' => 'Máximo nivel de inversión',
        'precio_inversion' => 5000.00,
        'ganancia_diaria' => 650.00,
        'ganancia_mensual' => 15000.00
    ]
];

$actualizados = 0;
$desactivados = 0;
$errores = [];

// Actualizar los 6 planes originales
foreach ($planes_originales as $plan) {
    $stmt = $conn->prepare("UPDATE tipos_inversion SET nombre = ?, descripcion = ?, estado = 'activo' WHERE id = ?");
    $stmt->bind_param("ssi", $plan['nombre'], $plan['descripcion'], $plan['id']);

    if ($stmt->execute()) {
        $actualizados++;
    } else {
        $errores[] = "Error al actualizar ID {$plan['id']}: " . $stmt->error;
    }
    $stmt->close();
}

// Desactivar Master (ID 7) y cualquier otro plan que no esté en la lista
$ids_permitidos = [1, 2, 3, 4, 5, 6];
$placeholders = implode(',', array_fill(0, count($ids_permitidos), '?'));
$stmt = $conn->prepare("UPDATE tipos_inversion SET estado = 'inactivo' WHERE id NOT IN ($placeholders)");
$types = str_repeat('i', count($ids_permitidos));
$stmt->bind_param($types, ...$ids_permitidos);

if ($stmt->execute()) {
    $desactivados = $stmt->affected_rows;
} else {
    $errores[] = "Error al desactivar planes: " . $stmt->error;
}
$stmt->close();

// Mostrar resultados
echo "<!DOCTYPE html>";
echo "<html lang='es'>";
echo "<head>";
echo "<meta charset='UTF-8'>";
echo "<meta name='viewport' content='width=device-width, initial-scale=1.0'>";
echo "<title>Planes Restaurados - CashSpace</title>";
echo "<style>";
echo "body { font-family: Arial, sans-serif; padding: 20px; background: #f5f5f5; }";
echo ".container { max-width: 900px; margin: 0 auto; background: white; padding: 30px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }";
echo "h1 { color: #044990; }";
echo ".success { background: #d1fae5; color: #065f46; padding: 15px; border-radius: 8px; margin: 20px 0; }";
echo ".info { background: #dbeafe; color: #1e40af; padding: 15px; border-radius: 8px; margin: 20px 0; }";
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
echo "<h1>✅ Planes de Inversión Restaurados</h1>";

if ($actualizados > 0) {
    echo "<div class='success'>";
    echo "✅ Se restauraron exitosamente <strong>{$actualizados}</strong> plan(es) de inversión a los originales.";
    echo "</div>";
}

if ($desactivados > 0) {
    echo "<div class='info'>";
    echo "ℹ️ Se desactivaron <strong>{$desactivados}</strong> plan(es) adicional(es) (incluyendo Master).";
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

// Mostrar solo los planes activos
$result = $conn->query("SELECT id, nombre, descripcion, precio_inversion, ganancia_diaria, ganancia_mensual, limite_inversion, estado FROM tipos_inversion WHERE estado = 'activo' ORDER BY precio_inversion ASC");

echo "<h2>📋 Planes de Inversión Activos (6 planes originales):</h2>";
echo "<table>";
echo "<tr><th>ID</th><th>Nombre</th><th>Descripción</th><th>Inversión</th><th>Ganancia/Día</th><th>Ganancia/Mes</th><th>Límite</th><th>Estado</th></tr>";

while ($row = $result->fetch_assoc()) {
    echo "<tr>";
    echo "<td>{$row['id']}</td>";
    echo "<td><strong>{$row['nombre']}</strong></td>";
    echo "<td class='desc'>{$row['descripcion']}</td>";
    echo "<td>" . number_format($row['precio_inversion'], 2, ',', '.') . " Bs</td>";
    echo "<td style='color: #10b981; font-weight: 600;'>+" . number_format($row['ganancia_diaria'], 2, ',', '.') . " Bs</td>";
    echo "<td style='color: #059669; font-weight: 600;'>" . number_format($row['ganancia_mensual'], 2, ',', '.') . " Bs</td>";
    echo "<td>{$row['limite_inversion']}</td>";
    echo "<td><span style='color: #10b981; font-weight: 600;'>" . strtoupper($row['estado']) . "</span></td>";
    echo "</tr>";
}

echo "</table>";
echo "<p style='margin-top: 20px;'><a href='index.php' style='color: #044990; text-decoration: none; font-weight: 600;'>← Volver al inicio</a></p>";
echo "</div>";
echo "</body>";
echo "</html>";

closeConnection($conn);
?>

