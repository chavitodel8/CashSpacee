<?php
require_once '../config/config.php';
requireLogin();

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    exit;
}

$user_id = $_SESSION['user_id'];
$nombre_titular = trim($_POST['nombre_titular'] ?? '');
$cuenta_bancaria = trim($_POST['cuenta_bancaria'] ?? '');
$tipo_cartera = trim($_POST['tipo_cartera'] ?? 'Yape');

// Validaciones
if (empty($nombre_titular)) {
    echo json_encode(['success' => false, 'message' => 'El nombre del titular es requerido']);
    exit;
}

if (empty($cuenta_bancaria)) {
    echo json_encode(['success' => false, 'message' => 'La cuenta bancaria es requerida']);
    exit;
}

if (empty($tipo_cartera)) {
    echo json_encode(['success' => false, 'message' => 'El tipo de cartera es requerido']);
    exit;
}

// Validar longitud
if (strlen($nombre_titular) > 100) {
    echo json_encode(['success' => false, 'message' => 'El nombre del titular es demasiado largo']);
    exit;
}

if (strlen($cuenta_bancaria) > 255) {
    echo json_encode(['success' => false, 'message' => 'La cuenta bancaria es demasiado larga']);
    exit;
}

$conn = getConnection();

try {
    // Verificar si ya existe un registro
    $stmt = $conn->prepare("SELECT id FROM cuenta_bancaria WHERE usuario_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $exists = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    
    // Determinar banco según tipo de cartera
    $banco = null;
    if ($tipo_cartera === 'Yape') {
        $banco = 'Yape';
    } elseif ($tipo_cartera === 'Yasta') {
        $banco = 'Yasta';
    } elseif ($tipo_cartera === 'BCP') {
        $banco = 'BCP';
    }
    
    if ($exists) {
        // Actualizar registro existente
        $stmt = $conn->prepare("UPDATE cuenta_bancaria SET nombre_titular = ?, numero_cuenta = ?, tipo_cuenta = ?, banco = ? WHERE usuario_id = ?");
        $stmt->bind_param("ssssi", $nombre_titular, $cuenta_bancaria, $tipo_cartera, $banco, $user_id);
    } else {
        // Insertar nuevo registro
        $stmt = $conn->prepare("INSERT INTO cuenta_bancaria (usuario_id, nombre_titular, numero_cuenta, tipo_cuenta, banco) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("issss", $user_id, $nombre_titular, $cuenta_bancaria, $tipo_cartera, $banco);
    }
    
    if ($stmt->execute()) {
        $stmt->close();
        closeConnection($conn);
        echo json_encode(['success' => true, 'message' => 'Cuenta bancaria actualizada exitosamente']);
    } else {
        $error = $stmt->error;
        $stmt->close();
        closeConnection($conn);
        echo json_encode(['success' => false, 'message' => 'Error al actualizar la cuenta bancaria: ' . $error]);
    }
} catch (Exception $e) {
    closeConnection($conn);
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}

