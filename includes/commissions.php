<?php
require_once __DIR__ . '/../config/config.php';

/**
 * Calcula la comisión por recarga según el monto
 * @param float $monto Monto de la recarga
 * @return float Comisión a pagar
 */
function calcularComisionRecarga($monto) {
    // Tabla de comisiones según monto recargado
    // Basado en los ejemplos: 100→25 (25%), 200→56 (28%)
    // Calculando proporcionalmente para otros montos
    $comisiones = [
        100 => 25,      // 100 Bs → 25 Bs comisión (25%)
        200 => 56,      // 200 Bs → 56 Bs comisión (28%)
        500 => 140,     // 500 Bs → 140 Bs comisión (28%)
        1000 => 280,    // 1000 Bs → 280 Bs comisión (28%)
        2000 => 560,    // 2000 Bs → 560 Bs comisión (28%)
        3000 => 840,    // 3000 Bs → 840 Bs comisión (28%)
        5000 => 1400,   // 5000 Bs → 1400 Bs comisión (28%)
        6000 => 1680,   // 6000 Bs → 1680 Bs comisión (28%)
        10000 => 2800,  // 10000 Bs → 2800 Bs comisión (28%)
        15000 => 4200,  // 15000 Bs → 4200 Bs comisión (28%)
        30000 => 8400,  // 30000 Bs → 8400 Bs comisión (28%)
        50000 => 14000, // 50000 Bs → 14000 Bs comisión (28%)
        100000 => 28000 // 100000 Bs → 28000 Bs comisión (28%)
    ];
    
    // Si el monto está en la tabla, devolver la comisión exacta
    if (isset($comisiones[$monto])) {
        return $comisiones[$monto];
    }
    
    // Si no está en la tabla, calcular basado en porcentaje (28% para montos >= 200, 25% para 100)
    if ($monto >= 200) {
        return round($monto * 0.28, 2);
    } else {
        return round($monto * 0.25, 2);
    }
}

/**
 * Procesa la comisión por recarga para el usuario referido
 * @param object $conn Conexión a la base de datos
 * @param int $usuario_id ID del usuario que hizo la recarga
 * @param float $monto_recarga Monto de la recarga
 * @param int $recarga_id ID de la recarga
 * @return array Resultado de la operación
 */
function procesarComisionRecarga($conn, $usuario_id, $monto_recarga, $recarga_id) {
    // Obtener el usuario que hizo la recarga para ver si tiene referido
    $stmt = $conn->prepare("SELECT codigo_referido FROM users WHERE id = ?");
    $stmt->bind_param("i", $usuario_id);
    $stmt->execute();
    $usuario = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    
    // Si no tiene código referido, no hay comisión
    if (empty($usuario['codigo_referido'])) {
        return ['success' => true, 'comision' => 0, 'message' => 'Usuario sin referido'];
    }
    
    // Buscar al usuario que lo invitó por su código de invitación
    $stmt = $conn->prepare("SELECT id, saldo_disponible FROM users WHERE codigo_invitacion = ?");
    $stmt->bind_param("s", $usuario['codigo_referido']);
    $stmt->execute();
    $referido = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    
    // Si no se encuentra el referido, no hay comisión
    if (!$referido) {
        return ['success' => true, 'comision' => 0, 'message' => 'Referido no encontrado'];
    }
    
    // Calcular la comisión
    $comision = calcularComisionRecarga($monto_recarga);
    
    if ($comision <= 0) {
        return ['success' => true, 'comision' => 0, 'message' => 'Sin comisión para este monto'];
    }
    
    // Obtener saldo anterior del referido
    $saldo_anterior_referido = $referido['saldo_disponible'];
    
    // Añadir comisión al saldo del referido
    $stmt = $conn->prepare("UPDATE users SET saldo_disponible = saldo_disponible + ?, saldo = saldo + ? WHERE id = ?");
    $stmt->bind_param("ddi", $comision, $comision, $referido['id']);
    $stmt->execute();
    $stmt->close();
    
    // Obtener nuevo saldo del referido
    $stmt = $conn->prepare("SELECT saldo_disponible FROM users WHERE id = ?");
    $stmt->bind_param("i", $referido['id']);
    $stmt->execute();
    $referido_nuevo = $stmt->get_result()->fetch_assoc();
    $saldo_nuevo_referido = $referido_nuevo['saldo_disponible'];
    $stmt->close();
    
    // Registrar transacción de comisión para el referido
    $stmt = $conn->prepare("INSERT INTO transacciones (usuario_id, tipo, monto, descripcion, referencia_id, saldo_anterior, saldo_nuevo) VALUES (?, 'comision', ?, ?, ?, ?, ?)");
    $descripcion = "Comisión por recarga de " . formatCurrency($monto_recarga) . " del referido #{$usuario_id}";
    $stmt->bind_param("isdiid", $referido['id'], $comision, $descripcion, $recarga_id, $saldo_anterior_referido, $saldo_nuevo_referido);
    $stmt->execute();
    $stmt->close();
    
    // Crear notificación para el referido
    $titulo = "Comisión por Recarga";
    $mensaje = "Has ganado " . formatCurrency($comision) . " de comisión por la recarga de " . formatCurrency($monto_recarga) . " de tu referido.";
    $stmt = $conn->prepare("INSERT INTO notificaciones (usuario_id, titulo, mensaje, tipo) VALUES (?, ?, ?, 'success')");
    $stmt->bind_param("iss", $referido['id'], $titulo, $mensaje);
    $stmt->execute();
    $stmt->close();
    
    return [
        'success' => true,
        'comision' => $comision,
        'referido_id' => $referido['id'],
        'message' => 'Comisión procesada exitosamente'
    ];
}
?>

