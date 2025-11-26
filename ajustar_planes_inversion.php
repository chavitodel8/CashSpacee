<?php
/**
 * Ajusta los planes de inversión para:
 *  - Cambiar duraciones y límites por plan
 *  - Calcular ganancias diarias para que el total sea el TRIPLE de la inversión
 *
 * Reglas solicitadas:
 *  - Inversión Básica y Inversión Plus: 2 meses (~60 días), límite de inversión 1
 *  - Inversión Premium y Inversión Gold: 100 días, límite de inversión 2
 *  - Demás inversiones: 6 meses (~180 días), se mantiene límite actual (o 8 si no hay)
 *
 * Fórmula:
 *  ganancia_total = 3 * precio_inversion
 *  ganancia_diaria = ganancia_total / duracion_dias
 *  ganancia_mensual = ganancia_diaria * 30
 *
 * Ejecutar desde el navegador:
 *  - Local:   http://localhost/CashSpace/ajustar_planes_inversion.php
 *  - Railway: https://TU_DOMINIO/ajustar_planes_inversion.php
 */

require_once 'config/config.php';

$conn = getConnection();

// Configuración por grupos de planes
$config_planes = [
    // Básica y Plus: 2 meses, límite 1
    'Inversión Básica' => [
        'duracion_dias'    => 60,
        'limite_inversion' => 1,
    ],
    'Inversión Plus' => [
        'duracion_dias'    => 60,
        'limite_inversion' => 1,
    ],
    // Premium y Gold: 100 días, límite 2
    'Inversión Premium' => [
        'duracion_dias'    => 100,
        'limite_inversion' => 2,
    ],
    'Inversión Gold' => [
        'duracion_dias'    => 100,
        'limite_inversion' => 2,
    ],
    // Las demás (Platinum, Elite, Diamond, Master, Supreme, Ultimate): 6 meses
    'Inversión Platinum' => [
        'duracion_dias'    => 180,
        'limite_inversion' => null, // se mantiene el que ya tiene
    ],
    'Inversión Elite' => [
        'duracion_dias'    => 180,
        'limite_inversion' => null,
    ],
    'Inversión Diamond' => [
        'duracion_dias'    => 180,
        'limite_inversion' => null,
    ],
    'Inversión Master' => [
        'duracion_dias'    => 180,
        'limite_inversion' => null,
    ],
    'Inversión Supreme' => [
        'duracion_dias'    => 180,
        'limite_inversion' => null,
    ],
    'Inversión Ultimate' => [
        'duracion_dias'    => 180,
        'limite_inversion' => null,
    ],
];

$actualizados = [];
$errores = [];

foreach ($config_planes as $nombre_plan => $config) {
    // Obtener el plan por nombre
    $stmt = $conn->prepare("SELECT id, precio_inversion, limite_inversion FROM tipos_inversion WHERE nombre = ? AND estado = 'activo' LIMIT 1");
    $stmt->bind_param("s", $nombre_plan);
    $stmt->execute();
    $plan = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$plan) {
        $errores[] = "No se encontró el plan '{$nombre_plan}' activo.";
        continue;
    }

    $precio       = (float)$plan['precio_inversion'];
    $duracion     = (int)$config['duracion_dias'];
    $limite_nuevo = $config['limite_inversion'];

    if ($duracion <= 0 || $precio <= 0) {
        $errores[] = "Datos inválidos para '{$nombre_plan}' (precio: {$precio}, duración: {$duracion}).";
        continue;
    }

    // Calcular ganancias para que el total sea el TRIPLE de la inversión
    $ganancia_total   = 3 * $precio;
    $ganancia_diaria  = round($ganancia_total / $duracion, 2);
    $ganancia_mensual = round($ganancia_diaria * 30, 2);

    if ($limite_nuevo === null) {
        // Mantener límite actual
        $stmt = $conn->prepare("UPDATE tipos_inversion SET ganancia_diaria = ?, ganancia_mensual = ?, duracion_dias = ? WHERE id = ?");
        $stmt->bind_param("ddii", $ganancia_diaria, $ganancia_mensual, $duracion, $plan['id']);
    } else {
        // Actualizar también el límite
        $stmt = $conn->prepare("UPDATE tipos_inversion SET ganancia_diaria = ?, ganancia_mensual = ?, duracion_dias = ?, limite_inversion = ? WHERE id = ?");
        $stmt->bind_param("ddiii", $ganancia_diaria, $ganancia_mensual, $duracion, $limite_nuevo, $plan['id']);
    }

    if ($stmt->execute()) {
        $actualizados[] = [
            'nombre'           => $nombre_plan,
            'precio'           => $precio,
            'duracion_dias'    => $duracion,
            'ganancia_diaria'  => $ganancia_diaria,
            'ganancia_mensual' => $ganancia_mensual,
            'limite_nuevo'     => $limite_nuevo ?? $plan['limite_inversion'],
        ];
    } else {
        $errores[] = "Error al actualizar '{$nombre_plan}': " . $stmt->error;
    }
    $stmt->close();
}

// Salida bonita en HTML (como los otros scripts de configuración)
echo "<!DOCTYPE html>";
echo "<html lang='es'>";
echo "<head>";
echo "<meta charset='UTF-8'>";
echo "<meta name='viewport' content='width=device-width, initial-scale=1.0'>";
echo "<title>Planes de Inversión Ajustados - CashSpace</title>";
echo "<style>";
echo "body { font-family: Arial, sans-serif; padding: 20px; background: #f5f5f5; }";
echo ".container { max-width: 1000px; margin: 0 auto; background: white; padding: 30px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }";
echo "h1 { color: #044990; }";
echo ".success { background: #d1fae5; color: #065f46; padding: 15px; border-radius: 8px; margin: 20px 0; }";
echo ".error { background: #fee2e2; color: #991b1b; padding: 15px; border-radius: 8px; margin: 10px 0; }";
echo "table { width: 100%; border-collapse: collapse; margin-top: 20px; }";
echo "th, td { padding: 12px; text-align: left; border-bottom: 1px solid #ddd; }";
echo "th { background: #044990; color: white; }";
echo "tr:hover { background: #f9fafb; }";
echo "</style>";
echo "</head>";
echo "<body>";
echo "<div class='container'>";
echo "<h1>✅ Planes de Inversión Ajustados</h1>";

if (!empty($actualizados)) {
    echo "<div class='success'>";
    echo "Se actualizaron <strong>" . count($actualizados) . "</strong> planes de inversión.";
    echo "</div>";

    echo "<table>";
    echo "<tr><th>Nombre</th><th>Inversión</th><th>Duración (días)</th><th>Ganancia/Día</th><th>Ganancia/Mes</th><th>Límite</th></tr>";
    foreach ($actualizados as $p) {
        echo "<tr>";
        echo "<td><strong>{$p['nombre']}</strong></td>";
        echo "<td>" . number_format($p['precio'], 2, ',', '.') . " Bs</td>";
        echo "<td>{$p['duracion_dias']}</td>";
        echo "<td style='color:#10b981;font-weight:600;'>+" . number_format($p['ganancia_diaria'], 2, ',', '.') . " Bs</td>";
        echo "<td style='color:#059669;font-weight:600;'>" . number_format($p['ganancia_mensual'], 2, ',', '.') . " Bs</td>";
        echo "<td>{$p['limite_nuevo']}</td>";
        echo "</tr>";
    }
    echo "</table>";
}

if (!empty($errores)) {
    echo "<div class='error'>";
    echo "<strong>Errores:</strong><ul>";
    foreach ($errores as $e) {
        echo "<li>{$e}</li>";
    }
    echo "</ul></div>";
}

echo "<p style='margin-top:20px;'><a href='index.php' style='color:#044990;text-decoration:none;font-weight:600;'>← Volver al inicio</a></p>";
echo "</div>";
echo "</body>";
echo "</html>";

closeConnection($conn);


