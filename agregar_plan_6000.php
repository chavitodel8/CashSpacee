<?php
/**
 * Script para agregar el nuevo plan de inversión de Bs 6000
 * Ejecuta desde el navegador: http://localhost/CashSpace/agregar_plan_6000.php
 */

require_once 'config/config.php';

$conn = getConnection();

// Nuevo plan de inversión
$nuevo_plan = [
    'nombre' => 'Inversión Elite',
    'descripcion' => 'Para inversionistas de élite con máximo rendimiento',
    'precio_inversion' => 6000.00,
    'ganancia_diaria' => 900.00,
    'ganancia_mensual' => 27000.00, // 900 * 30 días
    'limite_inversion' => 1,
    'duracion_dias' => 30,
    'estado' => 'activo'
];

// Verificar si ya existe un plan con este precio
$stmt = $conn->prepare("SELECT id FROM tipos_inversion WHERE precio_inversion = ?");
$stmt->bind_param("d", $nuevo_plan['precio_inversion']);
$stmt->execute();
$existe = $stmt->get_result()->fetch_assoc();
$stmt->close();

if ($existe) {
    // Si existe, actualizar
    $stmt = $conn->prepare("UPDATE tipos_inversion SET nombre = ?, descripcion = ?, ganancia_diaria = ?, ganancia_mensual = ?, limite_inversion = ?, duracion_dias = ?, estado = ? WHERE precio_inversion = ?");
    $stmt->bind_param("ssddiiss", 
        $nuevo_plan['nombre'],
        $nuevo_plan['descripcion'],
        $nuevo_plan['ganancia_diaria'],
        $nuevo_plan['ganancia_mensual'],
        $nuevo_plan['limite_inversion'],
        $nuevo_plan['duracion_dias'],
        $nuevo_plan['estado'],
        $nuevo_plan['precio_inversion']
    );
    $accion = "actualizado";
} else {
    // Si no existe, insertar nuevo
    $stmt = $conn->prepare("INSERT INTO tipos_inversion (nombre, descripcion, precio_inversion, ganancia_diaria, ganancia_mensual, limite_inversion, duracion_dias, estado) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssdddiis", 
        $nuevo_plan['nombre'],
        $nuevo_plan['descripcion'],
        $nuevo_plan['precio_inversion'],
        $nuevo_plan['ganancia_diaria'],
        $nuevo_plan['ganancia_mensual'],
        $nuevo_plan['limite_inversion'],
        $nuevo_plan['duracion_dias'],
        $nuevo_plan['estado']
    );
    $accion = "agregado";
}

if ($stmt->execute()) {
    $exito = true;
    if (!$existe) {
        $plan_id = $conn->insert_id;
    }
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
echo "<title>Plan Agregado - CashSpace</title>";
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
echo "<h1>" . ($exito ? "✅ Plan de Inversión " . ucfirst($accion) : "❌ Error al Agregar Plan") . "</h1>";

if ($exito) {
    echo "<div class='success'>";
    echo "✅ El plan de inversión se ha " . $accion . " exitosamente.";
    if (!$existe) {
        echo " ID del plan: <strong>{$plan_id}</strong>";
    }
    echo "</div>";
} else {
    echo "<div class='error'>";
    echo "❌ Error: " . htmlspecialchars($error);
    echo "</div>";
}

// Mostrar todos los planes activos ordenados por precio
$result = $conn->query("SELECT id, nombre, descripcion, precio_inversion, ganancia_diaria, ganancia_mensual, limite_inversion, estado FROM tipos_inversion WHERE estado = 'activo' ORDER BY precio_inversion ASC");

echo "<h2>📋 Planes de Inversión Activos:</h2>";
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

