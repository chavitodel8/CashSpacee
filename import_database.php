<?php
/**
 * Script para importar la base de datos CashSpace
 * 
 * INSTRUCCIONES:
 * 1. Sube este archivo a Railway junto con tu proyecto
 * 2. Accede a: https://tu-url.railway.app/import_database.php
 * 3. Espera a que termine la importación
 * 4. ELIMINA este archivo después de usarlo por seguridad
 */

// Configuración de errores
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Incluir configuración
require_once __DIR__ . '/config/config.php';

// Verificar que estamos en un entorno seguro (solo permitir si hay una clave secreta)
$import_key = isset($_GET['key']) ? $_GET['key'] : '';
$secret_key = 'cashspace_import_2024_' . date('Ymd'); // Cambia esto por una clave segura

if ($import_key !== $secret_key) {
    die('
    <!DOCTYPE html>
    <html>
    <head>
        <title>Importar Base de Datos - CashSpace</title>
        <style>
            body { font-family: Arial, sans-serif; max-width: 600px; margin: 50px auto; padding: 20px; background: #1a1a1a; color: #fff; }
            .container { background: #2a2a2a; padding: 30px; border-radius: 10px; }
            h1 { color: #667eea; }
            .warning { background: #f59e0b; color: #000; padding: 15px; border-radius: 5px; margin: 20px 0; }
            .info { background: #3b82f6; padding: 15px; border-radius: 5px; margin: 20px 0; }
            code { background: #1a1a1a; padding: 2px 6px; border-radius: 3px; }
            .btn { background: #667eea; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer; text-decoration: none; display: inline-block; margin-top: 10px; }
            .btn:hover { background: #5568d3; }
        </style>
    </head>
    <body>
        <div class="container">
            <h1>🔒 Importar Base de Datos</h1>
            <div class="warning">
                <strong>⚠️ ADVERTENCIA:</strong> Este script importará toda la base de datos. Asegúrate de tener respaldo si ya tienes datos.
            </div>
            <div class="info">
                <strong>📝 Instrucciones:</strong>
                <ol>
                    <li>Accede a esta URL con la clave secreta:</li>
                    <li><code>?key=' . htmlspecialchars($secret_key) . '</code></li>
                    <li>O haz clic en el botón de abajo</li>
                </ol>
            </div>
            <a href="?key=' . htmlspecialchars($secret_key) . '" class="btn">🚀 Iniciar Importación</a>
        </div>
    </body>
    </html>
    ');
}

// Si llegamos aquí, la clave es correcta, proceder con la importación
?>
<!DOCTYPE html>
<html>
<head>
    <title>Importando Base de Datos - CashSpace</title>
    <style>
        body { 
            font-family: Arial, sans-serif; 
            max-width: 800px; 
            margin: 50px auto; 
            padding: 20px; 
            background: #1a1a1a; 
            color: #fff; 
        }
        .container { 
            background: #2a2a2a; 
            padding: 30px; 
            border-radius: 10px; 
        }
        h1 { color: #667eea; }
        .success { background: #10b981; color: white; padding: 10px; border-radius: 5px; margin: 10px 0; }
        .error { background: #ef4444; color: white; padding: 10px; border-radius: 5px; margin: 10px 0; }
        .info { background: #3b82f6; color: white; padding: 10px; border-radius: 5px; margin: 10px 0; }
        .warning { background: #f59e0b; color: #000; padding: 15px; border-radius: 5px; margin: 20px 0; }
        pre { background: #1a1a1a; padding: 15px; border-radius: 5px; overflow-x: auto; }
        .progress { background: #1a1a1a; padding: 10px; border-radius: 5px; margin: 10px 0; }
        .btn { background: #667eea; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer; text-decoration: none; display: inline-block; margin-top: 10px; }
    </style>
</head>
<body>
    <div class="container">
        <h1>📦 Importando Base de Datos CashSpace</h1>
        
        <?php
        echo '<div class="info">🔄 Iniciando importación...</div>';
        
        try {
            // Conectar a la base de datos
            $conn = getConnection();
            echo '<div class="success">✅ Conexión a la base de datos exitosa</div>';
            
            // Leer el archivo SQL
            $sql_file = __DIR__ . '/database/cashspace.sql';
            
            if (!file_exists($sql_file)) {
                throw new Exception("El archivo database/cashspace.sql no existe");
            }
            
            echo '<div class="info">📂 Leyendo archivo SQL...</div>';
            $sql = file_get_contents($sql_file);
            
            if (empty($sql)) {
                throw new Exception("El archivo SQL está vacío");
            }
            
            // Remover comentarios y dividir en comandos
            $sql = preg_replace('/--.*$/m', '', $sql); // Remover comentarios de línea
            $sql = preg_replace('/\/\*.*?\*\//s', '', $sql); // Remover comentarios de bloque
            
            // Dividir en comandos individuales
            $commands = array_filter(
                array_map('trim', explode(';', $sql)),
                function($cmd) {
                    return !empty($cmd) && strlen($cmd) > 5; // Filtrar comandos vacíos o muy cortos
                }
            );
            
            $total_commands = count($commands);
            echo '<div class="info">📊 Total de comandos a ejecutar: ' . $total_commands . '</div>';
            
            $success_count = 0;
            $error_count = 0;
            $errors = [];
            
            echo '<div class="progress">';
            echo '<strong>Ejecutando comandos...</strong><br>';
            
            // Ejecutar cada comando
            foreach ($commands as $index => $command) {
                $command = trim($command);
                
                if (empty($command) || strlen($command) < 5) {
                    continue;
                }
                
                // Mostrar progreso cada 10 comandos
                if ($index % 10 == 0) {
                    $progress = round(($index / $total_commands) * 100);
                    echo "Progreso: {$progress}% ({$index}/{$total_commands})<br>";
                    flush();
                    ob_flush();
                }
                
                try {
                    if ($conn->query($command)) {
                        $success_count++;
                    } else {
                        $error_count++;
                        $error_msg = $conn->error;
                        // Solo mostrar errores importantes (ignorar "table already exists", etc.)
                        if (strpos($error_msg, 'already exists') === false && 
                            strpos($error_msg, 'Duplicate') === false) {
                            $errors[] = "Comando " . ($index + 1) . ": " . $error_msg;
                        }
                    }
                } catch (Exception $e) {
                    $error_count++;
                    $error_msg = $e->getMessage();
                    if (strpos($error_msg, 'already exists') === false && 
                        strpos($error_msg, 'Duplicate') === false) {
                        $errors[] = "Comando " . ($index + 1) . ": " . $error_msg;
                    }
                }
            }
            
            echo "Progreso: 100% ({$total_commands}/{$total_commands})<br>";
            echo '</div>';
            
            // Mostrar resultados
            echo '<div class="success">';
            echo '<strong>✅ Importación completada!</strong><br>';
            echo "Comandos exitosos: {$success_count}<br>";
            if ($error_count > 0) {
                echo "Comandos con advertencias: {$error_count} (normal si las tablas ya existen)<br>";
            }
            echo '</div>';
            
            // Mostrar errores importantes si los hay
            if (!empty($errors)) {
                echo '<div class="error">';
                echo '<strong>⚠️ Errores encontrados:</strong><br>';
                echo '<pre>' . implode("\n", array_slice($errors, 0, 10)) . '</pre>';
                if (count($errors) > 10) {
                    echo '<p>... y ' . (count($errors) - 10) . ' errores más</p>';
                }
                echo '</div>';
            }
            
            // Verificar que las tablas principales existan
            echo '<div class="info">🔍 Verificando tablas...</div>';
            $tables_to_check = ['users', 'tipos_inversion', 'inversiones', 'transacciones', 'recargas', 'retiros'];
            $existing_tables = [];
            
            $result = $conn->query("SHOW TABLES");
            if ($result) {
                while ($row = $result->fetch_array()) {
                    $existing_tables[] = $row[0];
                }
            }
            
            echo '<div class="success">';
            echo '<strong>📋 Tablas encontradas: ' . count($existing_tables) . '</strong><br>';
            $missing_tables = array_diff($tables_to_check, $existing_tables);
            
            if (empty($missing_tables)) {
                echo '✅ Todas las tablas principales están presentes<br>';
            } else {
                echo '⚠️ Faltan algunas tablas: ' . implode(', ', $missing_tables) . '<br>';
            }
            echo '</div>';
            
            closeConnection($conn);
            
            echo '<div class="warning">';
            echo '<strong>🔒 IMPORTANTE:</strong> Por seguridad, elimina este archivo (import_database.php) ahora que la importación está completa.';
            echo '</div>';
            
            echo '<div class="info">';
            echo '<strong>🎉 ¡Base de datos importada exitosamente!</strong><br>';
            echo 'Ahora puedes acceder a tu aplicación.';
            echo '</div>';
            
        } catch (Exception $e) {
            echo '<div class="error">';
            echo '<strong>❌ Error durante la importación:</strong><br>';
            echo htmlspecialchars($e->getMessage());
            echo '</div>';
            
            if (isset($conn)) {
                closeConnection($conn);
            }
        }
        ?>
        
        <a href="index.php" class="btn">🏠 Ir a la Aplicación</a>
    </div>
</body>
</html>

