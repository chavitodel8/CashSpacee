<?php
/**
 * Script para actualizar el límite de inversión a 8 para todos los planes
 * Ejecuta este archivo desde el navegador: http://localhost/CashSpace/actualizar_limite.php
 */

require_once 'config/config.php';

// Verificar que el usuario sea admin (opcional, puedes comentar esto si quieres)
// requireAdmin();

$conn = getConnection();

// Actualizar límite de inversión a 8 para todos los tipos de inversión activos
$sql = "UPDATE tipos_inversion SET limite_inversion = 8 WHERE estado = 'activo'";

if ($conn->query($sql)) {
    $affected = $conn->affected_rows;
    
    // Mostrar los planes actualizados
    $result = $conn->query("SELECT id, nombre, limite_inversion FROM tipos_inversion WHERE estado = 'activo' ORDER BY precio_inversion ASC");
    
    echo "<!DOCTYPE html>";
    echo "<html lang='es'>";
    echo "<head>";
    echo "<meta charset='UTF-8'>";
    echo "<meta name='viewport' content='width=device-width, initial-scale=1.0'>";
    echo "<title>Límite Actualizado - CashSpace</title>";
    echo "<style>";
    echo "body { font-family: Arial, sans-serif; padding: 20px; background: #f5f5f5; }";
    echo ".container { max-width: 600px; margin: 0 auto; background: white; padding: 30px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }";
    echo "h1 { color: #044990; }";
    echo ".success { background: #d1fae5; color: #065f46; padding: 15px; border-radius: 8px; margin: 20px 0; }";
    echo "table { width: 100%; border-collapse: collapse; margin-top: 20px; }";
    echo "th, td { padding: 12px; text-align: left; border-bottom: 1px solid #ddd; }";
    echo "th { background: #044990; color: white; }";
    echo "tr:hover { background: #f9fafb; }";
    echo "</style>";
    echo "</head>";
    echo "<body>";
    echo "<div class='container'>";
    echo "<h1>✅ Límite de Inversión Actualizado</h1>";
    echo "<div class='success'>";
    echo "El límite de inversión se actualizó exitosamente a <strong>8</strong> para {$affected} plan(es) de inversión.";
    echo "</div>";
    
    echo "<h2>📊 Planes Actualizados:</h2>";
    echo "<table>";
    echo "<tr><th>ID</th><th>Nombre del Plan</th><th>Límite Actual</th></tr>";
    
    while ($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>{$row['id']}</td>";
        echo "<td>{$row['nombre']}</td>";
        echo "<td><strong>{$row['limite_inversion']}</strong></td>";
        echo "</tr>";
    }
    
    echo "</table>";
    echo "<p style='margin-top: 20px;'><a href='index.php' style='color: #044990;'>← Volver al inicio</a></p>";
    echo "</div>";
    echo "</body>";
    echo "</html>";
} else {
    echo "<!DOCTYPE html>";
    echo "<html lang='es'>";
    echo "<head>";
    echo "<meta charset='UTF-8'>";
    echo "<title>Error - CashSpace</title>";
    echo "</head>";
    echo "<body>";
    echo "<h1>❌ Error al actualizar</h1>";
    echo "<p>Error: " . $conn->error . "</p>";
    echo "</body>";
    echo "</html>";
}

closeConnection($conn);
?>

