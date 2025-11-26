<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../config/config.php';

// Verificar que el usuario esté logueado
if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Debes iniciar sesión para cambiar tu contraseña']);
    exit;
}

$user_id = $_SESSION['user_id'];

// Verificar método POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    exit;
}

// Obtener datos del formulario
$password_actual = $_POST['password_actual'] ?? '';
$password_nueva = $_POST['password_nueva'] ?? '';
$password_confirmar = $_POST['password_confirmar'] ?? '';

// Validaciones
if (empty($password_actual) || empty($password_nueva) || empty($password_confirmar)) {
    echo json_encode(['success' => false, 'message' => 'Por favor completa todos los campos']);
    exit;
}

if (strlen($password_nueva) < 6) {
    echo json_encode(['success' => false, 'message' => 'La nueva contraseña debe tener al menos 6 caracteres']);
    exit;
}

if ($password_nueva !== $password_confirmar) {
    echo json_encode(['success' => false, 'message' => 'Las contraseñas nuevas no coinciden']);
    exit;
}

if ($password_actual === $password_nueva) {
    echo json_encode(['success' => false, 'message' => 'La nueva contraseña debe ser diferente a la actual']);
    exit;
}

// Conectar a la base de datos
$conn = getConnection();

// Obtener la contraseña actual del usuario
$stmt = $conn->prepare("SELECT password FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    $stmt->close();
    closeConnection($conn);
    echo json_encode(['success' => false, 'message' => 'Usuario no encontrado']);
    exit;
}

$user = $result->fetch_assoc();
$stmt->close();

// Verificar que la contraseña actual sea correcta
if (!password_verify($password_actual, $user['password'])) {
    closeConnection($conn);
    echo json_encode(['success' => false, 'message' => 'La contraseña anterior es incorrecta']);
    exit;
}

// Hashear la nueva contraseña
$hashed_password = password_hash($password_nueva, PASSWORD_DEFAULT);

// Actualizar la contraseña
$update_stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
$update_stmt->bind_param("si", $hashed_password, $user_id);

if ($update_stmt->execute()) {
    $update_stmt->close();
    closeConnection($conn);
    echo json_encode(['success' => true, 'message' => 'Contraseña cambiada exitosamente']);
} else {
    $error = $update_stmt->error;
    $update_stmt->close();
    closeConnection($conn);
    echo json_encode(['success' => false, 'message' => 'Error al actualizar la contraseña: ' . $error]);
}
?>

