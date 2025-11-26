<?php
/**
 * Script para corregir la tabla `avisos` en producción (Railway)
 *
 * Problema:
 *  - El código usa la columna `tipo` (info, success, warning, error, celebracion)
 *  - En Railway la tabla `avisos` se creó sin esa columna → error:
 *      "Unknown column 'tipo' in 'field list'"
 *
 * Solución:
 *  - Verificar si la columna `tipo` existe
 *  - Si NO existe, agregarla con:
 *      tipo ENUM('info','success','warning','error','celebracion') DEFAULT 'info'
 *
 * Cómo ejecutarlo:
 *  - Localhost:  http://localhost/CashSpace/fix_avisos_tipo.php
 *  - Railway:    https://TU_DOMINIO/fix_avisos_tipo.php
 *  (ejecútalo una sola vez; luego puedes borrarlo si quieres)
 */

require_once 'config/config.php';

$conn = getConnection();

$errores = [];
$acciones = [];

try {
    // Verificar que la tabla avisos existe
    $result = $conn->query("SHOW TABLES LIKE 'avisos'");
    if (!$result || $result->num_rows === 0) {
        throw new Exception("La tabla 'avisos' no existe en la base de datos actual.");
    }
    if ($result) {
        $result->close();
    }

    // Verificar si la columna `tipo` ya existe
    $result = $conn->query("SHOW COLUMNS FROM avisos LIKE 'tipo'");
    if ($result && $result->num_rows > 0) {
        $acciones[] = "La columna 'tipo' ya existe en la tabla 'avisos'. No se realizaron cambios.";
        $result->close();
    } else {
        if ($result) {
            $result->close();
        }

        // Agregar la columna `tipo` después de `mensaje`
        $sql = "ALTER TABLE avisos 
                ADD COLUMN tipo ENUM('info','success','warning','error','celebracion') 
                NOT NULL DEFAULT 'info' 
                AFTER mensaje";

        if ($conn->query($sql) === TRUE) {
            $acciones[] = "Se agregó correctamente la columna 'tipo' a la tabla 'avisos'.";
        } else {
            throw new Exception("Error al agregar la columna 'tipo': " . $conn->error);
        }
    }
} catch (Exception $e) {
    $errores[] = $e->getMessage();
}

// Salida HTML sencilla
echo "<!DOCTYPE html>";
echo "<html lang='es'>";
echo "<head>";
echo "<meta charset='UTF-8'>";
echo "<meta name='viewport' content='width=device-width, initial-scale=1.0'>";
echo "<title>Corrección de avisos - CashSpace</title>";
echo "<style>";
echo "body { font-family: Arial, sans-serif; padding: 20px; background: #f5f5f5; }";
echo ".container { max-width: 800px; margin: 0 auto; background: white; padding: 25px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }";
echo "h1 { color: #044990; margin-top: 0; }";
echo ".ok { background: #d1fae5; color: #065f46; padding: 12px 15px; border-radius: 8px; margin-bottom: 10px; }";
echo ".error { background: #fee2e2; color: #991b1b; padding: 12px 15px; border-radius: 8px; margin-bottom: 10px; }";
echo "ul { margin: 0; padding-left: 20px; }";
echo "a { color: #044990; text-decoration: none; font-weight: 600; }";
echo "</style>";
echo "</head>";
echo "<body>";
echo "<div class='container'>";
echo "<h1>Corrección de tabla de avisos</h1>";

if (!empty($acciones)) {
    foreach ($acciones as $msg) {
        echo "<div class='ok'>✅ " . htmlspecialchars($msg) . "</div>";
    }
}

if (!empty($errores)) {
    echo "<div class='error'><strong>Errores:</strong><ul>";
    foreach ($errores as $err) {
        echo "<li>" . htmlspecialchars($err) . "</li>";
    }
    echo "</ul></div>";
}

echo "<p><a href='admin/avisos.php'>← Volver a Gestión de Avisos</a></p>";
echo "<p><a href='index.php'>Ir al inicio</a></p>";

echo "</div>";
echo "</body>";
echo "</html>";

closeConnection($conn);


