<?php
/**
 * Script para actualizar el límite de inversión a 8 para todos los planes
 * Ejecuta este script desde el navegador o línea de comandos
 */

require_once __DIR__ . '/../config/config.php';

$conn = getConnection();

// Actualizar límite de inversión a 8 para todos los tipos de inversión activos
$sql = "UPDATE tipos_inversion SET limite_inversion = 8 WHERE estado = 'activo'";

if ($conn->query($sql)) {
    $affected = $conn->affected_rows;
    echo "✅ Límite de inversión actualizado exitosamente a 8 para {$affected} plan(es) de inversión.\n";
    
    // Mostrar los planes actualizados
    $result = $conn->query("SELECT id, nombre, limite_inversion FROM tipos_inversion WHERE estado = 'activo'");
    echo "\n📊 Planes actualizados:\n";
    echo str_repeat("-", 50) . "\n";
    while ($row = $result->fetch_assoc()) {
        echo "ID: {$row['id']} | {$row['nombre']} | Límite: {$row['limite_inversion']}\n";
    }
} else {
    echo "❌ Error al actualizar: " . $conn->error . "\n";
}

closeConnection($conn);
?>

