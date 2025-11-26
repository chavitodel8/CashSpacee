<?php
// Configuración de la base de datos para producción
// Lee las variables de entorno o usa valores por defecto

// Obtener variables de entorno o usar valores por defecto
$db_host = getenv('DB_HOST') ?: (defined('DB_HOST') ? DB_HOST : 'localhost');
$db_user = getenv('DB_USER') ?: (defined('DB_USER') ? DB_USER : 'root');
$db_pass = getenv('DB_PASS') ?: (defined('DB_PASS') ? DB_PASS : '');
$db_name = getenv('DB_NAME') ?: (defined('DB_NAME') ? DB_NAME : 'cashspace');
$db_port = getenv('DB_PORT') ?: 3306;

// Definir constantes si no están definidas
if (!defined('DB_HOST')) define('DB_HOST', $db_host);
if (!defined('DB_USER')) define('DB_USER', $db_user);
if (!defined('DB_PASS')) define('DB_PASS', $db_pass);
if (!defined('DB_NAME')) define('DB_NAME', $db_name);
if (!defined('DB_PORT')) define('DB_PORT', $db_port);

// Crear conexión
function getConnection() {
    global $db_host, $db_user, $db_pass, $db_name, $db_port;
    
    try {
        // Si el puerto es diferente al default, inclúyelo en el host
        $host = $db_port != 3306 ? $db_host . ':' . $db_port : $db_host;
        
        $conn = new mysqli($host, $db_user, $db_pass, $db_name);
        
        if ($conn->connect_error) {
            error_log("Error de conexión a la base de datos: " . $conn->connect_error);
            // En producción, no mostrar el error real al usuario
            if (getenv('ENVIRONMENT') === 'production' || ini_get('display_errors') == 0) {
                die("Error de conexión a la base de datos. Por favor, contacta al administrador.");
            } else {
                die("Error de conexión: " . $conn->connect_error);
            }
        }
        
        $conn->set_charset("utf8mb4");
        return $conn;
    } catch (Exception $e) {
        error_log("Excepción en conexión a BD: " . $e->getMessage());
        if (getenv('ENVIRONMENT') === 'production' || ini_get('display_errors') == 0) {
            die("Error de conexión a la base de datos. Por favor, contacta al administrador.");
        } else {
            die("Error: " . $e->getMessage());
        }
    }
}

// Función para cerrar conexión
function closeConnection($conn) {
    if ($conn) {
        $conn->close();
    }
}
?>

