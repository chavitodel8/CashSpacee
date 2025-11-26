<?php
require_once __DIR__ . '/../config/config.php';
requireLogin();

header('Content-Type: application/json');

$conn = getConnection();
$user_id = $_SESSION['user_id'];

// Obtener información bancaria del usuario
$stmt = $conn->prepare("SELECT nombre_titular, cuenta_bancaria, tipo_cartera FROM users WHERE id = ?");
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
            'cuenta_bancaria' => $bank_info['cuenta_bancaria'] ?? '',
            'tipo_cartera' => $bank_info['tipo_cartera'] ?? ''
        ]
    ]);
} else {
    echo json_encode([
        'success' => false,
        'data' => [
            'nombre_titular' => '',
            'cuenta_bancaria' => '',
            'tipo_cartera' => ''
        ]
    ]);
}

