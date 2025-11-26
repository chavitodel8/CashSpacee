<?php
/**
 * Script para agregar columna admin_id a la tabla avisos
 * Ejecutar una vez: https://tu-url.railway.app/fix_avisos_admin_id.php?key=cashspace_fix_2024
 */

require_once __DIR__ . '/config/config.php';

$import_key = isset($_GET['key']) ? $_GET['key'] : '';
$secret_key = 'cashspace_fix_2024';

if ($import_key !== $secret_key) {
    die('Acceso denegado. Usa: ?key=cashspace_fix_2024');
}

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html>
<head>
    <title>Fix Avisos - CashSpace</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 800px; margin: 50px auto; padding: 20px; background: #1a1a1a; color: #fff; }
        .container { background: #2a2a2a; padding: 30px; border-radius: 10px; }
        h1 { color: #667eea; }
        .success { background: #10b981; color: white; padding: 10px; border-radius: 5px; margin: 10px 0; }
        .error { background: #ef4444; color: white; padding: 10px; border-radius: 5px; margin: 10px 0; }
        .info { background: #3b82f6; color: white; padding: 10px; border-radius: 5px; margin: 10px 0; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔧 Fix: Agregar admin_id a tabla avisos</h1>
        
        <?php
        try {
            $conn = getConnection();
            echo '<div class="info">🔄 Verificando estructura de la tabla avisos...</div>';
            
            // Verificar si la columna admin_id existe
            $result = $conn->query("SHOW COLUMNS FROM avisos LIKE 'admin_id'");
            $column_exists = $result->num_rows > 0;
            
            if (!$column_exists) {
                echo '<div class="info">📝 Agregando columna admin_id...</div>';
                
                // Agregar columna admin_id
                $conn->query("ALTER TABLE avisos ADD COLUMN admin_id INT NULL AFTER fecha_fin");
                $conn->query("ALTER TABLE avisos ADD COLUMN fecha_actualizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP AFTER admin_id");
                $conn->query("ALTER TABLE avisos ADD FOREIGN KEY (admin_id) REFERENCES users(id) ON DELETE SET NULL");
                
                echo '<div class="success">✅ Columna admin_id agregada exitosamente</div>';
            } else {
                echo '<div class="info">ℹ️ La columna admin_id ya existe</div>';
            }
            
            // Verificar fecha_actualizacion
            $result = $conn->query("SHOW COLUMNS FROM avisos LIKE 'fecha_actualizacion'");
            if ($result->num_rows == 0) {
                $conn->query("ALTER TABLE avisos ADD COLUMN fecha_actualizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP AFTER admin_id");
                echo '<div class="success">✅ Columna fecha_actualizacion agregada</div>';
            }
            
            closeConnection($conn);
            
            echo '<div class="success">';
            echo '<strong>🎉 ¡Fix completado!</strong><br>';
            echo 'La tabla avisos ahora tiene la columna admin_id.';
            echo '</div>';
            
            echo '<div class="info">';
            echo '<strong>🔒 IMPORTANTE:</strong> Elimina este archivo (fix_avisos_admin_id.php) después de usarlo.';
            echo '</div>';
            
        } catch (Exception $e) {
            echo '<div class="error">';
            echo '<strong>❌ Error:</strong><br>';
            echo htmlspecialchars($e->getMessage());
            echo '</div>';
        }
        ?>
        
        <a href="admin/avisos.php" style="display: inline-block; margin-top: 20px; padding: 10px 20px; background: #667eea; color: white; text-decoration: none; border-radius: 5px;">Ir a Avisos</a>
    </div>
</body>
</html>

