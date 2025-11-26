<?php
require_once __DIR__ . '/../config/config.php';

header('Content-Type: application/json');
requireLogin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    exit();
}

$monto = isset($_POST['monto']) ? floatval($_POST['monto']) : 0;
$metodo_pago = isset($_POST['metodo_pago']) ? sanitize($_POST['metodo_pago']) : '';
$comprobante = isset($_POST['comprobante']) ? sanitize($_POST['comprobante']) : '';
$observaciones = isset($_POST['observaciones']) ? sanitize($_POST['observaciones']) : '';
$qr_code = isset($_POST['qr_code']) ? sanitize($_POST['qr_code']) : '';
$yape_numero = isset($_POST['yape_numero']) ? sanitize($_POST['yape_numero']) : '';
$yape_nombre = isset($_POST['yape_nombre']) ? sanitize($_POST['yape_nombre']) : '';

// Validar monto mínimo
if ($monto < 100) {
    echo json_encode(['success' => false, 'message' => 'El monto mínimo de recarga es de 100 Bs']);
    exit();
}

// Validar horario de recarga (10:00 - 22:00)
$hora_actual = (int)date('H');
$minuto_actual = (int)date('i');
$hora_decimal = $hora_actual + ($minuto_actual / 60);

if ($hora_decimal < 10 || $hora_decimal >= 22) {
    echo json_encode(['success' => false, 'message' => 'Las recargas solo están disponibles de 10:00 a 22:00']);
    exit();
}

// Validar que el monto esté en las opciones permitidas
$montos_permitidos = [100, 200, 500, 1000, 2000, 5000, 10000];
if (!in_array($monto, $montos_permitidos)) {
    echo json_encode(['success' => false, 'message' => 'El monto debe ser una de las opciones disponibles']);
    exit();
}

if (empty($metodo_pago)) {
    echo json_encode(['success' => false, 'message' => 'Debes seleccionar un método de pago']);
    exit();
}

// Validar método de pago
if (!in_array($metodo_pago, ['transferencia', 'yape'])) {
    echo json_encode(['success' => false, 'message' => 'Método de pago no válido']);
    exit();
}

// Si es Yape, obtener datos de la cuenta bancaria del usuario si no se enviaron
if ($metodo_pago === 'yape') {
    // Si no se enviaron los datos de Yape desde el frontend, obtenerlos de la cuenta bancaria configurada
    if (empty($yape_numero) || empty($yape_nombre)) {
        $conn_temp = getConnection();
        $stmt_temp = $conn_temp->prepare("SELECT cuenta_bancaria, nombre_titular FROM users WHERE id = ?");
        $stmt_temp->bind_param("i", $_SESSION['user_id']);
        $stmt_temp->execute();
        $user_bank = $stmt_temp->get_result()->fetch_assoc();
        $stmt_temp->close();
        closeConnection($conn_temp);
        
        if ($user_bank && $user_bank['cuenta_bancaria']) {
            $yape_numero = $user_bank['cuenta_bancaria'];
            $yape_nombre = $user_bank['nombre_titular'] ?? '';
        }
    }
    // El QR debe venir del frontend cuando se selecciona Yape
}

// Si es transferencia, no se genera QR (los QRs son solo para Yape)
if ($metodo_pago === 'transferencia') {
    $qr_code = ''; // No hay QR para transferencia bancaria
}

$conn = getConnection();
$user_id = $_SESSION['user_id'];

// Verificar si existen las columnas nuevas
$check_columns = $conn->query("SHOW COLUMNS FROM recargas LIKE 'qr_code'");
$has_qr_column = $check_columns->num_rows > 0;
$check_columns->close();

if ($has_qr_column) {
    $stmt = $conn->prepare("INSERT INTO recargas (usuario_id, monto, metodo_pago, comprobante, qr_code, yape_numero, yape_nombre, observaciones, estado) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'pendiente')");
    $stmt->bind_param("idssssss", $user_id, $monto, $metodo_pago, $comprobante, $qr_code, $yape_numero, $yape_nombre, $observaciones);
} else {
    $stmt = $conn->prepare("INSERT INTO recargas (usuario_id, monto, metodo_pago, comprobante, observaciones, estado) VALUES (?, ?, ?, ?, ?, 'pendiente')");
    $stmt->bind_param("idsss", $user_id, $monto, $metodo_pago, $comprobante, $observaciones);
}

if ($stmt->execute()) {
    $recarga_id = $conn->insert_id;
    
    // Crear notificación para el admin
    $titulo = "Nueva Solicitud de Recarga";
    $mensaje = "Usuario #{$user_id} ha solicitado una recarga de " . formatCurrency($monto);
    $admin_stmt = $conn->prepare("INSERT INTO notificaciones (titulo, mensaje, tipo) VALUES (?, ?, 'info')");
    $admin_stmt->bind_param("ss", $titulo, $mensaje);
    $admin_stmt->execute();
    $admin_stmt->close();
    
    $stmt->close();
    closeConnection($conn);
    
    echo json_encode(['success' => true, 'message' => 'Solicitud de recarga enviada exitosamente. Será revisada por un administrador.']);
} else {
    $error = $stmt->error;
    $stmt->close();
    closeConnection($conn);
    echo json_encode(['success' => false, 'message' => 'Error al crear la solicitud: ' . $error]);
}
?>

