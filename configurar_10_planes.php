<?php
/**
 * Script para configurar los 10 planes de inversión exactos
 * Ejecuta desde el navegador: http://localhost/CashSpace/configurar_10_planes.php
 */

require_once 'config/config.php';

$conn = getConnection();

// Los 10 planes exactos solicitados
$planes_solicitados = [
    [
        'precio_inversion' => 100.00,
        'nombre' => 'Inversión Básica',
        'descripcion' => 'Inversión en ganadería sostenible. Invierte en proyectos de cría y producción ganadera con enfoque en sostenibilidad y calidad. Genera rendimientos constantes del sector agropecuario.',
        'ganancia_diaria' => 12.00,
        'ganancia_mensual' => 300.00
    ],
    [
        'precio_inversion' => 200.00,
        'nombre' => 'Inversión Plus',
        'descripcion' => 'Inversión en inteligencia artificial y tecnología avanzada. Invierte en proyectos innovadores de IA, machine learning y automatización que están transformando el futuro digital.',
        'ganancia_diaria' => 25.00,
        'ganancia_mensual' => 600.00
    ],
    [
        'precio_inversion' => 500.00,
        'nombre' => 'Inversión Premium',
        'descripcion' => 'Inversión en minería responsable. Invierte en proyectos mineros de extracción de recursos naturales con prácticas sostenibles y tecnologías modernas de procesamiento.',
        'ganancia_diaria' => 65.00,
        'ganancia_mensual' => 1500.00
    ],
    [
        'precio_inversion' => 1000.00,
        'nombre' => 'Inversión Gold',
        'descripcion' => 'Inversión en investigación científica y desarrollo tecnológico. Apoya proyectos de investigación en medicina, biotecnología, energía y otras áreas científicas de vanguardia.',
        'ganancia_diaria' => 130.00,
        'ganancia_mensual' => 3000.00
    ],
    [
        'precio_inversion' => 3000.00,
        'nombre' => 'Inversión Platinum',
        'descripcion' => 'Inversión en pesca comercial y acuicultura. Invierte en proyectos de pesca sostenible, piscicultura y procesamiento de productos marinos con certificaciones de calidad internacional.',
        'ganancia_diaria' => 390.00, // 3000 * 0.13
        'ganancia_mensual' => 9000.00 // 390 * 30
    ],
    [
        'precio_inversion' => 6000.00,
        'nombre' => 'Inversión Elite',
        'descripcion' => 'Inversión en agricultura moderna y agroindustria. Invierte en proyectos agrícolas de alta tecnología, cultivos especializados y transformación agroindustrial con valor agregado.',
        'ganancia_diaria' => 900.00,
        'ganancia_mensual' => 27000.00
    ],
    [
        'precio_inversion' => 15000.00,
        'nombre' => 'Inversión Diamond',
        'descripcion' => 'Inversión en bienes raíces y desarrollo inmobiliario. Invierte en proyectos de construcción, desarrollo urbano y comercialización de propiedades con alto potencial de valorización.',
        'ganancia_diaria' => 2250.00, // 15000 * 0.15
        'ganancia_mensual' => 67500.00 // 2250 * 30
    ],
    [
        'precio_inversion' => 30000.00,
        'nombre' => 'Inversión Master',
        'descripcion' => 'Inversión en bolsa de valores y mercados financieros. Accede a portafolios diversificados de acciones, bonos y derivados gestionados por expertos en mercados internacionales.',
        'ganancia_diaria' => 4500.00, // 30000 * 0.15
        'ganancia_mensual' => 135000.00 // 4500 * 30
    ],
    [
        'precio_inversion' => 50000.00,
        'nombre' => 'Inversión Supreme',
        'descripcion' => 'Inversión en industria automotriz y movilidad. Invierte en proyectos de fabricación de vehículos, desarrollo de tecnologías de transporte y comercialización de automóviles premium.',
        'ganancia_diaria' => 7500.00, // 50000 * 0.15
        'ganancia_mensual' => 225000.00 // 7500 * 30
    ],
    [
        'precio_inversion' => 100000.00,
        'nombre' => 'Inversión Ultimate',
        'descripcion' => 'Inversión en estación espacial y tecnología aeroespacial. Forma parte de proyectos espaciales de vanguardia, investigación en microgravedad y desarrollo de tecnologías para la exploración espacial.',
        'ganancia_diaria' => 15000.00, // 100000 * 0.15
        'ganancia_mensual' => 450000.00 // 15000 * 30
    ]
];

$agregados = 0;
$actualizados = 0;
$desactivados = 0;
$errores = [];

// Obtener precios de los planes solicitados
$precios_solicitados = array_column($planes_solicitados, 'precio_inversion');

// Desactivar todos los planes que no están en la lista
$placeholders = implode(',', array_fill(0, count($precios_solicitados), '?'));
$stmt = $conn->prepare("UPDATE tipos_inversion SET estado = 'inactivo' WHERE precio_inversion NOT IN ($placeholders)");
$types = str_repeat('d', count($precios_solicitados));
$stmt->bind_param($types, ...$precios_solicitados);

if ($stmt->execute()) {
    $desactivados = $stmt->affected_rows;
} else {
    $errores[] = "Error al desactivar planes: " . $stmt->error;
}
$stmt->close();

// Procesar cada plan solicitado
foreach ($planes_solicitados as $plan) {
    // Verificar si existe un plan con este precio
    $stmt = $conn->prepare("SELECT id FROM tipos_inversion WHERE precio_inversion = ?");
    $stmt->bind_param("d", $plan['precio_inversion']);
    $stmt->execute();
    $existe = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    
    if ($existe) {
        // Actualizar plan existente
        $stmt = $conn->prepare("UPDATE tipos_inversion SET nombre = ?, descripcion = ?, ganancia_diaria = ?, ganancia_mensual = ?, limite_inversion = 8, duracion_dias = 30, estado = 'activo' WHERE precio_inversion = ?");
        $stmt->bind_param("ssddd", 
            $plan['nombre'],
            $plan['descripcion'],
            $plan['ganancia_diaria'],
            $plan['ganancia_mensual'],
            $plan['precio_inversion']
        );
        
        if ($stmt->execute()) {
            $actualizados++;
        } else {
            $errores[] = "Error al actualizar plan de {$plan['precio_inversion']} Bs: " . $stmt->error;
        }
        $stmt->close();
    } else {
        // Insertar nuevo plan
        $stmt = $conn->prepare("INSERT INTO tipos_inversion (nombre, descripcion, precio_inversion, ganancia_diaria, ganancia_mensual, limite_inversion, duracion_dias, estado) VALUES (?, ?, ?, ?, ?, 8, 30, 'activo')");
        $stmt->bind_param("ssddd", 
            $plan['nombre'],
            $plan['descripcion'],
            $plan['precio_inversion'],
            $plan['ganancia_diaria'],
            $plan['ganancia_mensual']
        );
        
        if ($stmt->execute()) {
            $agregados++;
        } else {
            $errores[] = "Error al agregar plan de {$plan['precio_inversion']} Bs: " . $stmt->error;
        }
        $stmt->close();
    }
}

// Mostrar resultados
echo "<!DOCTYPE html>";
echo "<html lang='es'>";
echo "<head>";
echo "<meta charset='UTF-8'>";
echo "<meta name='viewport' content='width=device-width, initial-scale=1.0'>";
echo "<title>10 Planes Configurados - CashSpace</title>";
echo "<style>";
echo "body { font-family: Arial, sans-serif; padding: 20px; background: #f5f5f5; }";
echo ".container { max-width: 1000px; margin: 0 auto; background: white; padding: 30px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }";
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
echo "<h1>✅ Configuración de 10 Planes de Inversión</h1>";

if ($agregados > 0 || $actualizados > 0) {
    echo "<div class='success'>";
    echo "✅ Se agregaron <strong>{$agregados}</strong> plan(es) nuevos.<br>";
    echo "✅ Se actualizaron <strong>{$actualizados}</strong> plan(es) existentes.";
    echo "</div>";
}

if ($desactivados > 0) {
    echo "<div class='info'>";
    echo "ℹ️ Se desactivaron <strong>{$desactivados}</strong> plan(es) que no están en la lista de los 10 planes solicitados.";
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

// Mostrar todos los planes activos ordenados por precio
$result = $conn->query("SELECT id, nombre, descripcion, precio_inversion, ganancia_diaria, ganancia_mensual, limite_inversion, estado FROM tipos_inversion WHERE estado = 'activo' ORDER BY precio_inversion ASC");

echo "<h2>📋 Los 10 Planes de Inversión Activos:</h2>";
echo "<table>";
echo "<tr><th>ID</th><th>Nombre</th><th>Descripción</th><th>Inversión</th><th>Ganancia/Día</th><th>Ganancia/Mes</th><th>Límite</th><th>Estado</th></tr>";

$contador = 0;
while ($row = $result->fetch_assoc()) {
    $contador++;
    echo "<tr>";
    echo "<td>{$row['id']}</td>";
    echo "<td><strong>{$row['nombre']}</strong></td>";
    echo "<td class='desc'>{$row['descripcion']}</td>";
    echo "<td><strong>" . number_format($row['precio_inversion'], 2, ',', '.') . " Bs</strong></td>";
    echo "<td style='color: #10b981; font-weight: 600;'>+" . number_format($row['ganancia_diaria'], 2, ',', '.') . " Bs</td>";
    echo "<td style='color: #059669; font-weight: 600;'>" . number_format($row['ganancia_mensual'], 2, ',', '.') . " Bs</td>";
    echo "<td>{$row['limite_inversion']}</td>";
    echo "<td><span style='color: #10b981; font-weight: 600;'>" . strtoupper($row['estado']) . "</span></td>";
    echo "</tr>";
}

echo "</table>";

if ($contador != 10) {
    echo "<div class='error' style='margin-top: 20px;'>";
    echo "⚠️ Advertencia: Se encontraron <strong>{$contador}</strong> planes activos, pero deberían ser exactamente <strong>10</strong>.";
    echo "</div>";
} else {
    echo "<div class='success' style='margin-top: 20px;'>";
    echo "✅ Perfecto: Se encontraron exactamente <strong>10</strong> planes activos.";
    echo "</div>";
}

echo "<p style='margin-top: 20px;'><a href='index.php' style='color: #044990; text-decoration: none; font-weight: 600;'>← Volver al inicio</a></p>";
echo "</div>";
echo "</body>";
echo "</html>";

closeConnection($conn);
?>

