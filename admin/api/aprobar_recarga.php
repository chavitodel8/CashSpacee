<?php
// Capturar todos los errores pero no mostrarlos directamente
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

// Iniciar buffer de salida para capturar cualquier salida no deseada
ob_start();

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/commissions.php';

// Limpiar cualquier salida que haya ocurrido antes del header
ob_clean();

header('Content-Type: application/json');
requireAdmin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    exit();
}

$data = json_decode(file_get_contents('php://input'), true);
$recarga_id = isset($data['id']) ? intval($data['id']) : 0;
$accion = isset($data['accion']) ? $data['accion'] : '';
$observaciones = isset($data['observaciones']) ? sanitize($data['observaciones']) : '';

if ($recarga_id <= 0 || !in_array($accion, ['aprobar', 'rechazar'])) {
    echo json_encode(['success' => false, 'message' => 'Datos inválidos']);
    exit();
}

$conn = getConnection();
$admin_id = $_SESSION['user_id'];

// Obtener información de la recarga
$stmt = $conn->prepare("SELECT * FROM recargas WHERE id = ? AND estado = 'pendiente'");
$stmt->bind_param("i", $recarga_id);
$stmt->execute();
$recarga = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$recarga) {
    closeConnection($conn);
    echo json_encode(['success' => false, 'message' => 'Recarga no encontrada o ya procesada']);
    exit();
}

$conn->autocommit(FALSE);

try {
    if ($accion === 'aprobar') {
        // Aprobar recarga
        $estado = 'aprobada';
        $stmt = $conn->prepare("UPDATE recargas SET estado = ?, admin_id = ?, fecha_aprobacion = NOW(), observaciones = ? WHERE id = ?");
        $stmt->bind_param("sisi", $estado, $admin_id, $observaciones, $recarga_id);
        $stmt->execute();
        $stmt->close();
        
        // Obtener saldo anterior ANTES de actualizar
        $stmt = $conn->prepare("SELECT saldo_disponible FROM users WHERE id = ?");
        $stmt->bind_param("i", $recarga['usuario_id']);
        $stmt->execute();
        $user_antes = $stmt->get_result()->fetch_assoc();
        $saldo_anterior = $user_antes['saldo_disponible'];
        $stmt->close();
        
        // Añadir monto al saldo del usuario
        $stmt = $conn->prepare("UPDATE users SET saldo_disponible = saldo_disponible + ?, saldo = saldo + ? WHERE id = ?");
        $stmt->bind_param("ddi", $recarga['monto'], $recarga['monto'], $recarga['usuario_id']);
        $stmt->execute();
        $stmt->close();
        
        // Obtener nuevo saldo DESPUÉS de actualizar
        $stmt = $conn->prepare("SELECT saldo_disponible FROM users WHERE id = ?");
        $stmt->bind_param("i", $recarga['usuario_id']);
        $stmt->execute();
        $user = $stmt->get_result()->fetch_assoc();
        $saldo_nuevo = $user['saldo_disponible'];
        $stmt->close();
        
        // Registrar transacción
        $stmt = $conn->prepare("INSERT INTO transacciones (usuario_id, tipo, monto, descripcion, referencia_id, saldo_anterior, saldo_nuevo) VALUES (?, 'recarga', ?, ?, ?, ?, ?)");
        $descripcion = "Recarga aprobada #{$recarga_id}";
        $stmt->bind_param("isdiid", $recarga['usuario_id'], $recarga['monto'], $descripcion, $recarga_id, $saldo_anterior, $saldo_nuevo);
        $stmt->execute();
        $stmt->close();
        
        // Crear notificación para el usuario
        $titulo = "Recarga Aprobada";
        $mensaje = "Tu recarga de " . formatCurrency($recarga['monto']) . " ha sido aprobada y añadida a tu saldo.";
        $stmt = $conn->prepare("INSERT INTO notificaciones (usuario_id, titulo, mensaje, tipo) VALUES (?, ?, ?, 'success')");
        $stmt->bind_param("iss", $recarga['usuario_id'], $titulo, $mensaje);
        $stmt->execute();
        $stmt->close();
        
        // Procesar comisión para el referido si existe
        $resultado_comision = procesarComisionRecarga($conn, $recarga['usuario_id'], $recarga['monto'], $recarga_id);
        
    } else {
        // Rechazar recarga
        $estado = 'rechazada';
        $stmt = $conn->prepare("UPDATE recargas SET estado = ?, admin_id = ?, fecha_aprobacion = NOW(), observaciones = ? WHERE id = ?");
        $stmt->bind_param("sisi", $estado, $admin_id, $observaciones, $recarga_id);
        $stmt->execute();
        $stmt->close();
        
        // Crear notificación para el usuario
        $titulo = "Recarga Rechazada";
        $mensaje = "Tu recarga de " . formatCurrency($recarga['monto']) . " ha sido rechazada." . ($observaciones ? " Motivo: {$observaciones}" : "");
        $stmt = $conn->prepare("INSERT INTO notificaciones (usuario_id, titulo, mensaje, tipo) VALUES (?, ?, ?, 'error')");
        $stmt->bind_param("iss", $recarga['usuario_id'], $titulo, $mensaje);
        $stmt->execute();
        $stmt->close();
    }
    
    $conn->commit();
    closeConnection($conn);
    
    echo json_encode(['success' => true, 'message' => 'Recarga ' . ($accion === 'aprobar' ? 'aprobada' : 'rechazada') . ' exitosamente']);
    
} catch (Exception $e) {
    if (isset($conn)) {
        try {
            $conn->rollback();
        } catch (Exception $rollbackError) {
            // Ignorar errores de rollback
        }
        closeConnection($conn);
    }
    
    // Limpiar cualquier salida antes de enviar JSON
    ob_clean();
    
    // Log del error para debugging
    error_log('Error en aprobar_recarga.php: ' . $e->getMessage());
    error_log('Stack trace: ' . $e->getTraceAsString());
    
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
} catch (Error $e) {
    if (isset($conn)) {
        try {
            if ($conn->autocommit(FALSE)) {
                $conn->rollback();
            }
        } catch (Exception $rollbackError) {
            // Ignorar errores de rollback
        }
        closeConnection($conn);
    }
    
    ob_clean();
    
    error_log('Error fatal en aprobar_recarga.php: ' . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Error fatal: ' . $e->getMessage()]);
}
?>

