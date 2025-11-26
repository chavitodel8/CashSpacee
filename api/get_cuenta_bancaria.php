<?php
require_once __DIR__ . '/../config/config.php';
requireLogin();

header('Content-Type: application/json');

$conn = getConnection();
$user_id = $_SESSION['user_id'];

// Obtener información bancaria del usuario
$stmt = $conn->prepare("SELECT nombre_titular, numero_cuenta, tipo_cuenta, banco FROM cuenta_bancaria WHERE usuario_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$bank_info = $result->fetch_assoc();
$stmt->close();

closeConnection($conn);

if ($bank_info) {
    echo json_encode([
        'success' => true,
        'data' => [
            'nombre_titular' => $bank_info['nombre_titular'] ?? '',
            'cuenta_bancaria' => $bank_info['numero_cuenta'] ?? '',
            'tipo_cartera' => $bank_info['tipo_cuenta'] ?? ''
        ]
    ]);
} else {
    echo json_encode([
        'success' => true,
        'data' => [
            'nombre_titular' => '',
            'cuenta_bancaria' => '',
            'tipo_cartera' => ''
        ]
    ]);
}

