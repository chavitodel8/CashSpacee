<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../config/config.php';

// Verificar que el usuario esté logueado
if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Debes iniciar sesión']);
    exit;
}

// Verificar método POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    exit;
}

$user_id = $_SESSION['user_id'];
$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['subscription']) || !isset($data['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Datos incompletos']);
    exit;
}

// Conectar a la base de datos
$conn = getConnection();

// Crear tabla de suscripciones push si no existe
$createTable = "CREATE TABLE IF NOT EXISTS push_subscriptions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    endpoint TEXT NOT NULL,
    p256dh VARCHAR(255) NOT NULL,
    auth VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_id (user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

$conn->query($createTable);

// Extraer datos de la suscripción
$subscription = $data['subscription'];
$endpoint = $subscription['endpoint'];
$keys = $subscription['keys'];
$p256dh = $keys['p256dh'];
$auth = $keys['auth'];

// Verificar si ya existe una suscripción para este usuario
$stmt = $conn->prepare("SELECT id FROM push_subscriptions WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    // Actualizar suscripción existente
    $stmt->close();
    $update_stmt = $conn->prepare("UPDATE push_subscriptions SET endpoint = ?, p256dh = ?, auth = ?, updated_at = NOW() WHERE user_id = ?");
    $update_stmt->bind_param("sssi", $endpoint, $p256dh, $auth, $user_id);
    
    if ($update_stmt->execute()) {
        $update_stmt->close();
        closeConnection($conn);
        echo json_encode(['success' => true, 'message' => 'Suscripción actualizada']);
    } else {
        $update_stmt->close();
        closeConnection($conn);
        echo json_encode(['success' => false, 'message' => 'Error al actualizar suscripción']);
    }
} else {
    // Crear nueva suscripción
    $stmt->close();
    $insert_stmt = $conn->prepare("INSERT INTO push_subscriptions (user_id, endpoint, p256dh, auth) VALUES (?, ?, ?, ?)");
    $insert_stmt->bind_param("isss", $user_id, $endpoint, $p256dh, $auth);
    
    if ($insert_stmt->execute()) {
        $insert_stmt->close();
        closeConnection($conn);
        echo json_encode(['success' => true, 'message' => 'Suscripción creada']);
    } else {
        $insert_stmt->close();
        closeConnection($conn);
        echo json_encode(['success' => false, 'message' => 'Error al crear suscripción']);
    }
}
?>

