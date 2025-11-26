<?php
// Iniciar buffer de salida para capturar cualquier salida no deseada
ob_start();

require_once __DIR__ . '/../config/config.php';

// Limpiar cualquier salida que haya ocurrido antes del header
ob_clean();

header('Content-Type: application/json');
requireLogin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    exit();
}

$monto = isset($_POST['monto']) ? floatval($_POST['monto']) : 0;
$metodo_pago = isset($_POST['metodo_pago']) ? sanitize($_POST['metodo_pago']) : '';
$cuenta_destino = isset($_POST['cuenta_destino']) ? sanitize($_POST['cuenta_destino']) : '';
$observaciones = isset($_POST['observaciones']) ? sanitize($_POST['observaciones']) : '';

// Verificar si los retiros están bloqueados
$conn_check = getConnection();
$result = $conn_check->query("SELECT * FROM bloqueo_retiros WHERE activo = 1 ORDER BY fecha_creacion DESC LIMIT 1");
$bloqueo = $result->fetch_assoc();
closeConnection($conn_check);

if ($bloqueo) {
    // Verificar si el bloqueo aún es válido (si no es indefinido)
    $bloqueado = true;
    if (!$bloqueo['indefinido'] && $bloqueo['fecha_fin']) {
        $fecha_fin = strtotime($bloqueo['fecha_fin']);
        $ahora = time();
        if ($ahora > $fecha_fin) {
            // El bloqueo expiró, desactivarlo
            $conn_update = getConnection();
            $stmt = $conn_update->prepare("UPDATE bloqueo_retiros SET activo = 0 WHERE id = ?");
            $stmt->bind_param("i", $bloqueo['id']);
            $stmt->execute();
            $stmt->close();
            closeConnection($conn_update);
            $bloqueado = false;
        }
    }
    
    if ($bloqueado) {
        $mensaje = !empty($bloqueo['descripcion']) ? $bloqueo['descripcion'] : 'Los retiros están temporalmente deshabilitados. Por favor, intente más tarde.';
        echo json_encode(['success' => false, 'message' => $mensaje, 'bloqueado' => true]);
        exit();
    }
}

// Validar monto mínimo de 150 Bs
if ($monto < 150) {
    echo json_encode(['success' => false, 'message' => 'El monto mínimo de retiro es de 150 Bs']);
    exit();
}

// Validar horario de retiro (10:00 - 22:00)
$hora_actual = (int)date('H');
$minuto_actual = (int)date('i');
$hora_decimal = $hora_actual + ($minuto_actual / 60);

if ($hora_decimal < 10 || $hora_decimal >= 22) {
    echo json_encode(['success' => false, 'message' => 'Los retiros solo están disponibles de 10:00 a.m. a 10:00 p.m.']);
    exit();
}

if (empty($metodo_pago) || empty($cuenta_destino)) {
    echo json_encode(['success' => false, 'message' => 'Completa todos los campos requeridos']);
    exit();
}

// Solo permitir transferencia
if ($metodo_pago !== 'transferencia') {
    echo json_encode(['success' => false, 'message' => 'Solo se permite transferencia bancaria como método de pago']);
    exit();
}

$conn = getConnection();
$user_id = $_SESSION['user_id'];

// Verificar saldo disponible
$stmt = $conn->prepare("SELECT saldo_disponible FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();

// Verificar inversión mínima de 100 Bs
$stmt = $conn->prepare("SELECT COALESCE(SUM(monto_invertido), 0) as total_inversion FROM inversiones WHERE usuario_id = ? AND estado = 'activa'");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$inversion_result = $stmt->get_result()->fetch_assoc();
$stmt->close();

$total_inversion = floatval($inversion_result['total_inversion']);
if ($total_inversion < 100) {
    closeConnection($conn);
    echo json_encode(['success' => false, 'message' => 'Los retiros solo se habilitan después de una inversión mínima de 100 Bs']);
    exit();
}

// Calcular comisión del 5%
$comision = $monto * 0.05;
$monto_total = $monto + $comision;

if ($user['saldo_disponible'] < $monto_total) {
    closeConnection($conn);
    echo json_encode(['success' => false, 'message' => 'Saldo insuficiente. Saldo disponible: ' . formatCurrency($user['saldo_disponible']) . '. Se requiere: ' . formatCurrency($monto_total) . ' (monto + comisión 5%)']);
    exit();
}

// Verificar si existe la columna comision
$check_comision = $conn->query("SHOW COLUMNS FROM retiros LIKE 'comision'");
$has_comision_column = $check_comision->num_rows > 0;
$check_comision->close();

// Crear solicitud de retiro
if ($has_comision_column) {
    $stmt = $conn->prepare("INSERT INTO retiros (usuario_id, monto, comision, metodo_pago, cuenta_destino, observaciones, estado) VALUES (?, ?, ?, ?, ?, ?, 'pendiente')");
    $stmt->bind_param("iddsss", $user_id, $monto, $comision, $metodo_pago, $cuenta_destino, $observaciones);
} else {
    $stmt = $conn->prepare("INSERT INTO retiros (usuario_id, monto, metodo_pago, cuenta_destino, observaciones, estado) VALUES (?, ?, ?, ?, ?, 'pendiente')");
    $stmt->bind_param("idsss", $user_id, $monto, $metodo_pago, $cuenta_destino, $observaciones);
}

if ($stmt->execute()) {
    $retiro_id = $conn->insert_id;
    
    // Reservar el monto total (monto + comisión) del saldo disponible temporalmente
    $stmt2 = $conn->prepare("UPDATE users SET saldo_disponible = saldo_disponible - ? WHERE id = ?");
    $stmt2->bind_param("di", $monto_total, $user_id);
    $stmt2->execute();
    $stmt2->close();
    
    // Registrar transacción del retiro (monto total descontado)
    $stmt3 = $conn->prepare("INSERT INTO transacciones (usuario_id, tipo, monto, descripcion, referencia_id, saldo_anterior, saldo_nuevo) VALUES (?, 'retiro', ?, ?, ?, ?, ?)");
    $saldo_anterior = $user['saldo_disponible'];
    $saldo_nuevo = $user['saldo_disponible'] - $monto_total;
    $descripcion = "Solicitud de retiro #{$retiro_id} (Monto: " . formatCurrency($monto) . ", Comisión 5%: " . formatCurrency($comision) . ")";
    $stmt3->bind_param("isdiid", $user_id, $monto_total, $descripcion, $retiro_id, $saldo_anterior, $saldo_nuevo);
    $stmt3->execute();
    $stmt3->close();
    
    // Crear notificación para el admin
    $titulo = "Nueva Solicitud de Retiro";
    $mensaje = "Usuario #{$user_id} ha solicitado un retiro de " . formatCurrency($monto);
    $admin_stmt = $conn->prepare("INSERT INTO notificaciones (titulo, mensaje, tipo) VALUES (?, ?, 'warning')");
    $admin_stmt->bind_param("ss", $titulo, $mensaje);
    $admin_stmt->execute();
    $admin_stmt->close();
    
    $stmt->close();
    closeConnection($conn);
    
    echo json_encode(['success' => true, 'message' => 'Solicitud de retiro enviada exitosamente. Será revisada por un administrador.']);
} else {
    $error = $stmt->error;
    $stmt->close();
    closeConnection($conn);
    echo json_encode(['success' => false, 'message' => 'Error al crear la solicitud: ' . $error]);
}
?>

