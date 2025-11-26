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
    // Actualizar información bancaria
    $stmt = $conn->prepare("UPDATE users SET nombre_titular = ?, cuenta_bancaria = ?, tipo_cartera = ? WHERE id = ?");
    $stmt->bind_param("sssi", $nombre_titular, $cuenta_bancaria, $tipo_cartera, $user_id);
    
    if ($stmt->execute()) {
        $stmt->close();
        closeConnection($conn);
        echo json_encode(['success' => true, 'message' => 'Cuenta bancaria actualizada exitosamente']);
    } else {
        $stmt->close();
        closeConnection($conn);
        echo json_encode(['success' => false, 'message' => 'Error al actualizar la cuenta bancaria']);
    }
} catch (Exception $e) {
    closeConnection($conn);
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}

