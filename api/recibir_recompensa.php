<?php
require_once __DIR__ . '/../config/config.php';

header('Content-Type: application/json');
requireLogin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    exit();
}

$data = json_decode(file_get_contents('php://input'), true);
$recompensa_id = isset($data['recompensa_id']) ? intval($data['recompensa_id']) : 0;

if ($recompensa_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'ID de recompensa inválido']);
    exit();
}

$conn = getConnection();
$user_id = $_SESSION['user_id'];

// Verificar que la recompensa existe y está activa
$stmt = $conn->prepare("SELECT * FROM recompensas_equipo WHERE id = ? AND estado = 'activo'");
$stmt->bind_param("i", $recompensa_id);
$stmt->execute();
$recompensa = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$recompensa) {
    closeConnection($conn);
    echo json_encode(['success' => false, 'message' => 'Recompensa no encontrada o inactiva']);
    exit();
}

// Verificar si ya fue recibida
$stmt = $conn->prepare("SELECT id FROM recompensas_recibidas WHERE usuario_id = ? AND recompensa_id = ?");
$stmt->bind_param("ii", $user_id, $recompensa_id);
$stmt->execute();
$ya_recibida = $stmt->get_result()->fetch_assoc();
$stmt->close();

if ($ya_recibida) {
    closeConnection($conn);
    echo json_encode(['success' => false, 'message' => 'Ya has recibido esta recompensa']);
    exit();
}

// Obtener inversión total del equipo nivel 1
// Usamos la tabla 'equipo' (singular) y la estructura actual:
// equipo.usuario_id  -> usuario que invita (líder)
// equipo.referido_id -> usuario invitado (miembro del equipo)
$stmt = $conn->prepare("SELECT COALESCE(SUM(u.saldo_invertido), 0) as inversion_total
                       FROM equipo e
                       JOIN users u ON e.referido_id = u.id
                       WHERE e.usuario_id = ? AND e.nivel = 1");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$inversion_total = $result->fetch_assoc()['inversion_total'];
$stmt->close();

// Verificar que se cumple el requisito
if ($inversion_total < $recompensa['monto_inversion_requerido']) {
    closeConnection($conn);
    echo json_encode(['success' => false, 'message' => 'Aún no se cumple el requisito de inversión para esta recompensa']);
    exit();
}

$conn->autocommit(FALSE);

try {
    // Registrar recompensa recibida
    $stmt = $conn->prepare("INSERT INTO recompensas_recibidas (usuario_id, recompensa_id, monto_recibido) VALUES (?, ?, ?)");
    $stmt->bind_param("iid", $user_id, $recompensa_id, $recompensa['monto_recompensa']);
    $stmt->execute();
    $stmt->close();
    
    // Añadir monto al saldo del usuario
    $stmt = $conn->prepare("UPDATE users SET saldo_disponible = saldo_disponible + ?, saldo = saldo + ? WHERE id = ?");
    $stmt->bind_param("ddi", $recompensa['monto_recompensa'], $recompensa['monto_recompensa'], $user_id);
    $stmt->execute();
    $stmt->close();
    
    // Obtener nuevo saldo
    $stmt = $conn->prepare("SELECT saldo_disponible FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    
    // Registrar transacción
    $stmt = $conn->prepare("INSERT INTO transacciones (usuario_id, tipo, monto, descripcion, saldo_nuevo) VALUES (?, 'codigo', ?, ?, ?)");
    $descripcion = "Recompensa de equipo nivel {$recompensa['nivel']} - {$recompensa['descripcion']}";
    $stmt->bind_param("idsd", $user_id, $recompensa['monto_recompensa'], $descripcion, $user['saldo_disponible']);
    $stmt->execute();
    $stmt->close();
    
    // Crear notificación
    $titulo = "Recompensa Recibida";
    $mensaje = "Has recibido una recompensa de " . formatCurrency($recompensa['monto_recompensa']) . " por el crecimiento de tu equipo.";
    $stmt = $conn->prepare("INSERT INTO notificaciones (usuario_id, titulo, mensaje, tipo) VALUES (?, ?, ?, 'success')");
    $stmt->bind_param("iss", $user_id, $titulo, $mensaje);
    $stmt->execute();
    $stmt->close();
    
    $conn->commit();
    closeConnection($conn);
    
    echo json_encode(['success' => true, 'message' => 'Recompensa recibida exitosamente']);
    
} catch (Exception $e) {
    $conn->rollback();
    closeConnection($conn);
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}
?>

