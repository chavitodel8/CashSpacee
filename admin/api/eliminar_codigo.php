<?php
// Habilitar mostrar errores para debugging
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

// Iniciar buffer de salida
ob_start();

require_once __DIR__ . '/../../config/config.php';

// Limpiar cualquier salida antes del header
ob_clean();

header('Content-Type: application/json');
requireAdmin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    exit();
}

$data = json_decode(file_get_contents('php://input'), true);
$codigo_id = isset($data['id']) ? intval($data['id']) : 0;

if ($codigo_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'ID de código inválido']);
    exit();
}

$conn = getConnection();

try {
    // Log para debugging
    error_log("Intentando eliminar código ID: " . $codigo_id);
    
    // Verificar que el código existe (sin importar el estado - activo, usado, expirado)
    $stmt = $conn->prepare("SELECT id, codigo, estado FROM codigos_promocionales WHERE id = ?");
    if (!$stmt) {
        throw new Exception("Error al preparar consulta: " . $conn->error);
    }
    
    $stmt->bind_param("i", $codigo_id);
    $stmt->execute();
    $codigo = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    
    if (!$codigo) {
        closeConnection($conn);
        error_log("Código no encontrado: " . $codigo_id);
        echo json_encode(['success' => false, 'message' => 'Código no encontrado']);
        exit();
    }
    
    error_log("Código encontrado: " . $codigo['codigo'] . " - Estado: " . $codigo['estado']);
    
    // Obtener el estado para el mensaje (sin restricciones - se puede eliminar cualquier código)
    $estado_codigo = $codigo['estado'];
    
    // Desactivar temporalmente las foreign keys para permitir eliminación
    $conn->query("SET FOREIGN_KEY_CHECKS = 0");
    
    try {
        // Primero eliminar registros relacionados en codigos_canjeados si existe la tabla
        $check_table = $conn->query("SHOW TABLES LIKE 'codigos_canjeados'");
        if ($check_table && $check_table->num_rows > 0) {
            $stmt = $conn->prepare("DELETE FROM codigos_canjeados WHERE codigo_id = ?");
            if ($stmt) {
                $stmt->bind_param("i", $codigo_id);
                $stmt->execute();
                $stmt->close();
            }
            $check_table->close();
        }
        
        // Eliminar transacciones relacionadas si existen
        $stmt = $conn->prepare("DELETE FROM transacciones WHERE tipo = 'codigo' AND referencia_id = ?");
        if ($stmt) {
            $stmt->bind_param("i", $codigo_id);
            $stmt->execute();
            $stmt->close();
        }
        
        // Eliminar el código (sin restricciones de estado)
        $stmt = $conn->prepare("DELETE FROM codigos_promocionales WHERE id = ?");
        if (!$stmt) {
            throw new Exception("Error al preparar DELETE: " . $conn->error);
        }
        
        $stmt->bind_param("i", $codigo_id);
        
        if ($stmt->execute()) {
            $affected_rows = $stmt->affected_rows;
            $stmt->close();
            
            error_log("Filas afectadas: " . $affected_rows);
            
            // Reactivar foreign keys
            $conn->query("SET FOREIGN_KEY_CHECKS = 1");
            closeConnection($conn);
            
            if ($affected_rows > 0) {
                $mensaje = 'Código eliminado exitosamente';
                if ($estado_codigo === 'activo') {
                    $mensaje .= ' (el código estaba activo)';
                }
                error_log("Código eliminado exitosamente: " . $codigo_id);
                echo json_encode(['success' => true, 'message' => $mensaje]);
            } else {
                error_log("No se pudo eliminar el código. Filas afectadas: 0");
                echo json_encode(['success' => false, 'message' => 'No se pudo eliminar el código. Puede que ya haya sido eliminado o haya un problema con la base de datos.']);
            }
        } else {
            $error = $stmt->error;
            $stmt->close();
            $conn->query("SET FOREIGN_KEY_CHECKS = 1");
            closeConnection($conn);
            error_log("Error al ejecutar DELETE: " . $error);
            echo json_encode(['success' => false, 'message' => 'Error al eliminar: ' . $error]);
        }
    } catch (Exception $e) {
        // Asegurar reactivar foreign keys en caso de error
        $conn->query("SET FOREIGN_KEY_CHECKS = 1");
        closeConnection($conn);
        throw $e;
    }
    
} catch (Exception $e) {
    if (isset($conn)) {
        closeConnection($conn);
    }
    error_log("Error en eliminar_codigo.php: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}
?>

