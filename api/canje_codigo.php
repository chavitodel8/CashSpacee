<?php
// Capturar todos los errores pero no mostrarlos directamente
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

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

$codigo = isset($_POST['codigo']) ? strtoupper(sanitize($_POST['codigo'])) : '';

if (empty($codigo)) {
    echo json_encode(['success' => false, 'message' => 'Debes ingresar un código']);
    exit();
}

$conn = getConnection();
$user_id = $_SESSION['user_id'];

// Iniciar transacción con nivel de aislamiento para evitar condiciones de carrera
$conn->autocommit(FALSE);

try {
    // Bloquear la fila del código promocional para evitar condiciones de carrera
    // Usar SELECT ... FOR UPDATE para bloquear la fila hasta que termine la transacción
    $stmt = $conn->prepare("SELECT * FROM codigos_promocionales WHERE codigo = ? AND estado = 'activo' AND (fecha_expiracion IS NULL OR fecha_expiracion >= CURDATE()) FOR UPDATE");
    $stmt->bind_param("s", $codigo);
    $stmt->execute();
    $codigo_data = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    
    if (!$codigo_data) {
        // Verificar si el código existe pero está inactivo o expirado
        $stmt = $conn->prepare("SELECT estado, limite_activaciones, activaciones_usadas FROM codigos_promocionales WHERE codigo = ?");
        $stmt->bind_param("s", $codigo);
        $stmt->execute();
        $codigo_check = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        
        if ($codigo_check) {
            if ($codigo_check['estado'] !== 'activo') {
                throw new Exception('Este código no está activo');
            }
            $limite = $codigo_check['limite_activaciones'] ?? 0;
            $usadas = $codigo_check['activaciones_usadas'] ?? 0;
            if ($limite > 0 && $usadas >= $limite) {
                throw new Exception('Este código ha alcanzado su límite de activaciones');
            }
            throw new Exception('Código promocional no válido o expirado');
        } else {
            throw new Exception('Código promocional no válido o expirado');
        }
    }
    
    // Verificar límite de activaciones
    $limite = $codigo_data['limite_activaciones'] ?? 0;
    $activaciones_usadas = $codigo_data['activaciones_usadas'] ?? 0;
    
    if ($limite > 0 && $activaciones_usadas >= $limite) {
        // Marcar como usado si alcanzó el límite
        $stmt = $conn->prepare("UPDATE codigos_promocionales SET estado = 'usado' WHERE id = ?");
        $stmt->bind_param("i", $codigo_data['id']);
        $stmt->execute();
        $stmt->close();
        throw new Exception('Este código ha alcanzado su límite de activaciones');
    }
    
    // Verificar si este usuario ya canjeó este código específico
    $stmt = $conn->prepare("SELECT id FROM codigos_canjeados WHERE codigo_id = ? AND usuario_id = ? LIMIT 1");
    $stmt->bind_param("ii", $codigo_data['id'], $user_id);
    $stmt->execute();
    $canje_existente = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    
    if ($canje_existente) {
        throw new Exception('Ya has canjeado este código anteriormente');
    }

    // Generar monto aleatorio entre 1 y 50 Bs (solo números enteros, sin centavos)
    $monto = mt_rand(1, 50); // Genera entre 1 y 50 (números enteros)
    
    // Obtener saldo anterior ANTES de actualizar
    $stmt = $conn->prepare("SELECT saldo_disponible FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $user_antes = $stmt->get_result()->fetch_assoc();
    $saldo_anterior = $user_antes['saldo_disponible'];
    $stmt->close();
    
    // Incrementar contador de activaciones
    $nuevas_activaciones = $activaciones_usadas + 1;
    $nuevo_estado = ($limite > 0 && $nuevas_activaciones >= $limite) ? 'usado' : 'activo';
    
    $stmt = $conn->prepare("UPDATE codigos_promocionales SET activaciones_usadas = ?, estado = ? WHERE id = ? AND estado = 'activo'");
    $stmt->bind_param("isi", $nuevas_activaciones, $nuevo_estado, $codigo_data['id']);
    
    if (!$stmt->execute()) {
        throw new Exception("Error al actualizar el código");
    }
    
    // Verificar que exactamente 1 fila fue afectada
    if ($stmt->affected_rows !== 1) {
        $stmt->close();
        throw new Exception('Error al procesar el código. Intente nuevamente.');
    }
    $stmt->close();
    
    // Registrar el canje en la tabla codigos_canjeados
    $stmt = $conn->prepare("INSERT INTO codigos_canjeados (codigo_id, usuario_id, monto) VALUES (?, ?, ?)");
    $stmt->bind_param("iid", $codigo_data['id'], $user_id, $monto);
    
    if (!$stmt->execute()) {
        throw new Exception("Error al registrar el canje");
    }
    $stmt->close();
    
    // Añadir monto al saldo del usuario
    $stmt = $conn->prepare("UPDATE users SET saldo_disponible = saldo_disponible + ?, saldo = saldo + ? WHERE id = ?");
    $stmt->bind_param("ddi", $monto, $monto, $user_id);
    
    if (!$stmt->execute()) {
        throw new Exception("Error al actualizar el saldo");
    }
    $stmt->close();
    
    // Obtener nuevo saldo después de la actualización
    $stmt = $conn->prepare("SELECT saldo_disponible FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();
    $saldo_nuevo = $user['saldo_disponible'];
    $stmt->close();
    
    // Registrar transacción
    $stmt = $conn->prepare("INSERT INTO transacciones (usuario_id, tipo, monto, descripcion, referencia_id, saldo_anterior, saldo_nuevo) VALUES (?, 'codigo', ?, ?, ?, ?, ?)");
    $descripcion = "Canje de código promocional: {$codigo}";
    $stmt->bind_param("isdiid", $user_id, $monto, $descripcion, $codigo_data['id'], $saldo_anterior, $saldo_nuevo);
    
    if (!$stmt->execute()) {
        throw new Exception("Error al registrar la transacción");
    }
    $stmt->close();
    
    $conn->commit();
    closeConnection($conn);
    
    echo json_encode(['success' => true, 'message' => 'Código canjeado exitosamente. Se añadieron ' . formatCurrency($monto) . ' a tu saldo.']);
    
} catch (Exception $e) {
    $conn->rollback();
    closeConnection($conn);
    error_log("Error en canje_codigo.php: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>

