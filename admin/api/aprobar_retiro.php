<?php
require_once __DIR__ . '/../../config/config.php';

header('Content-Type: application/json');
requireAdmin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    exit();
}

$data = json_decode(file_get_contents('php://input'), true);
$retiro_id = isset($data['id']) ? intval($data['id']) : 0;
$accion = isset($data['accion']) ? $data['accion'] : '';
$observaciones = isset($data['observaciones']) ? sanitize($data['observaciones']) : '';

if ($retiro_id <= 0 || !in_array($accion, ['aprobar', 'rechazar'])) {
    echo json_encode(['success' => false, 'message' => 'Datos inválidos']);
    exit();
}

$conn = getConnection();
$admin_id = $_SESSION['user_id'];

// Obtener información del retiro
$stmt = $conn->prepare("SELECT * FROM retiros WHERE id = ? AND estado = 'pendiente'");
$stmt->bind_param("i", $retiro_id);
$stmt->execute();
$retiro = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$retiro) {
    closeConnection($conn);
    echo json_encode(['success' => false, 'message' => 'Retiro no encontrado o ya procesado']);
    exit();
}

$conn->autocommit(FALSE);

try {
    if ($accion === 'aprobar') {
        // Aprobar retiro (el monto total ya fue descontado al crear la solicitud)
        $estado = 'aprobado';
        $stmt = $conn->prepare("UPDATE retiros SET estado = ?, admin_id = ?, fecha_procesamiento = NOW(), observaciones = ? WHERE id = ?");
        $stmt->bind_param("sisi", $estado, $admin_id, $observaciones, $retiro_id);
        $stmt->execute();
        $stmt->close();
        
        // Obtener comisión (si existe)
        $comision = isset($retiro['comision']) ? floatval($retiro['comision']) : ($retiro['monto'] * 0.05);
        $monto_total = $retiro['monto'] + $comision;
        
        // Descontar del saldo total (el monto total ya fue descontado del saldo_disponible)
        $stmt = $conn->prepare("UPDATE users SET saldo = saldo - ? WHERE id = ?");
        $stmt->bind_param("di", $monto_total, $retiro['usuario_id']);
        $stmt->execute();
        $stmt->close();
        
        // Crear notificación para el usuario
        $titulo = "Retiro Aprobado";
        $mensaje = "Tu retiro de " . formatCurrency($retiro['monto']) . " ha sido aprobado y será procesado. Comisión aplicada: " . formatCurrency($comision);
        $stmt = $conn->prepare("INSERT INTO notificaciones (usuario_id, titulo, mensaje, tipo) VALUES (?, ?, ?, 'success')");
        $stmt->bind_param("iss", $retiro['usuario_id'], $titulo, $mensaje);
        $stmt->execute();
        $stmt->close();
        
    } else {
        // Rechazar retiro - devolver el monto total (monto + comisión) al saldo disponible
        $estado = 'rechazado';
        $stmt = $conn->prepare("UPDATE retiros SET estado = ?, admin_id = ?, fecha_procesamiento = NOW(), observaciones = ? WHERE id = ?");
        $stmt->bind_param("sisi", $estado, $admin_id, $observaciones, $retiro_id);
        $stmt->execute();
        $stmt->close();
        
        // Obtener comisión (si existe)
        $comision = isset($retiro['comision']) ? floatval($retiro['comision']) : ($retiro['monto'] * 0.05);
        $monto_total = $retiro['monto'] + $comision;
        
        // Devolver monto total al saldo disponible
        $stmt = $conn->prepare("UPDATE users SET saldo_disponible = saldo_disponible + ? WHERE id = ?");
        $stmt->bind_param("di", $monto_total, $retiro['usuario_id']);
        $stmt->execute();
        $stmt->close();
        
        // Obtener nuevo saldo
        $stmt = $conn->prepare("SELECT saldo_disponible FROM users WHERE id = ?");
        $stmt->bind_param("i", $retiro['usuario_id']);
        $stmt->execute();
        $user = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        
        // Registrar transacción de devolución
        $stmt = $conn->prepare("INSERT INTO transacciones (usuario_id, tipo, monto, descripcion, referencia_id, saldo_nuevo) VALUES (?, 'retiro', ?, ?, ?, ?)");
        $descripcion = "Retiro rechazado - monto devuelto #{$retiro_id}";
        $stmt->bind_param("isdi", $retiro['usuario_id'], $monto_total, $descripcion, $retiro_id, $user['saldo_disponible']);
        $stmt->execute();
        $stmt->close();
        
        // Crear notificación para el usuario
        $titulo = "Retiro Rechazado";
        $mensaje = "Tu retiro de " . formatCurrency($retiro['monto']) . " ha sido rechazado y el monto total ha sido devuelto a tu saldo." . ($observaciones ? " Motivo: {$observaciones}" : "");
        $stmt = $conn->prepare("INSERT INTO notificaciones (usuario_id, titulo, mensaje, tipo) VALUES (?, ?, ?, 'error')");
        $stmt->bind_param("iss", $retiro['usuario_id'], $titulo, $mensaje);
        $stmt->execute();
        $stmt->close();
    }
    
    $conn->commit();
    closeConnection($conn);
    
    echo json_encode(['success' => true, 'message' => 'Retiro ' . ($accion === 'aprobar' ? 'aprobado' : 'rechazado') . ' exitosamente']);
    
} catch (Exception $e) {
    $conn->rollback();
    closeConnection($conn);
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}
?>

