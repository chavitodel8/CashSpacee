<?php
require_once __DIR__ . '/../config/config.php';

// Función para obtener la ruta de la imagen de un plan de inversión
function getInvestmentImagePath($nombre_plan) {
    // Mapeo de nombres de planes a nombres de archivo
    $mapa_imagenes = [
        'Inversión Básica' => 'basica',
        'Inversión Plus' => 'plus',
        'Inversión Premium' => 'premium',
        'Inversión Gold' => 'gold',
        'Inversión Platinum' => 'platinum',
        'Inversión Elite' => 'elite',
        'Inversión Diamond' => 'diamond',
        'Inversión Master' => 'master',
        'Inversión Supreme' => 'supreme',
        'Inversión Ultimate' => 'ultimate'
    ];
    
    // Obtener el nombre base del archivo
    $nombre_base = isset($mapa_imagenes[$nombre_plan]) ? $mapa_imagenes[$nombre_plan] : null;
    
    if (!$nombre_base) {
        return null;
    }
    
    // Buscar la imagen con diferentes extensiones
    $extensiones = ['png', 'jpg', 'jpeg', 'webp', 'gif'];
    foreach ($extensiones as $ext) {
        $ruta = "assets/images/investments/{$nombre_base}.{$ext}";
        if (file_exists($ruta)) {
            return $ruta;
        }
    }
    
    return null;
}

// Obtener tipos de inversión disponibles (los 10 planes configurados)
function getAvailableInvestmentTypes() {
    $conn = getConnection();
    // Los 10 planes: Básica, Plus, Premium, Gold, Platinum, Elite, Diamond, Master, Supreme, Ultimate
    $nombres_planes = [
        'Inversión Básica',
        'Inversión Plus',
        'Inversión Premium',
        'Inversión Gold',
        'Inversión Platinum',
        'Inversión Elite',
        'Inversión Diamond',
        'Inversión Master',
        'Inversión Supreme',
        'Inversión Ultimate'
    ];
    $placeholders = implode(',', array_fill(0, count($nombres_planes), '?'));
    $stmt = $conn->prepare("SELECT * FROM tipos_inversion WHERE estado = 'activo' AND nombre IN ($placeholders) ORDER BY precio_inversion ASC");
    $types = str_repeat('s', count($nombres_planes));
    $stmt->bind_param($types, ...$nombres_planes);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    closeConnection($conn);
    return $result;
}

// Obtener tipo de inversión por ID
function getInvestmentType($id) {
    $conn = getConnection();
    $stmt = $conn->prepare("SELECT * FROM tipos_inversion WHERE id = ? AND estado = 'activo'");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    closeConnection($conn);
    return $result;
}

// Obtener inversiones activas de un usuario
function getActiveInvestments($user_id) {
    $conn = getConnection();
    $stmt = $conn->prepare("
        SELECT i.*, t.nombre 
        FROM inversiones i 
        JOIN tipos_inversion t ON i.tipo_inversion_id = t.id 
        WHERE i.usuario_id = ? AND i.estado = 'activa'
        ORDER BY i.fecha_creacion DESC
    ");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    closeConnection($conn);
    return $result;
}

// Realizar inversión
function makeInvestment($user_id, $tipo_inversion_id, $monto) {
    $conn = getConnection();
    
    // Iniciar transacción
    $conn->autocommit(FALSE);
    
    try {
        // Obtener información del tipo de inversión
        $stmt = $conn->prepare("SELECT * FROM tipos_inversion WHERE id = ? AND estado = 'activo'");
        $stmt->bind_param("i", $tipo_inversion_id);
        $stmt->execute();
        $tipo = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        
        if (!$tipo) {
            throw new Exception("Tipo de inversión no válido");
        }
        
        if ($monto != $tipo['precio_inversion']) {
            throw new Exception("El monto debe ser exactamente " . formatCurrency($tipo['precio_inversion']));
        }
        
        // Verificar saldo disponible
        $stmt = $conn->prepare("SELECT saldo_disponible FROM users WHERE id = ? FOR UPDATE");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $user = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        
        if ($user['saldo_disponible'] < $monto) {
            throw new Exception("Saldo insuficiente. Necesitas " . formatCurrency($monto));
        }
        
        // Verificar límite de inversión
        $stmt = $conn->prepare("SELECT COUNT(*) as total FROM inversiones WHERE usuario_id = ? AND tipo_inversion_id = ? AND estado = 'activa'");
        $stmt->bind_param("ii", $user_id, $tipo_inversion_id);
        $stmt->execute();
        $count = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        
        if ($count['total'] >= $tipo['limite_inversion']) {
            throw new Exception("Has alcanzado el límite de inversiones para este plan");
        }
        
        // Calcular fechas
        $fecha_inicio = date('Y-m-d');
        $fecha_fin = date('Y-m-d', strtotime('+' . $tipo['duracion_dias'] . ' days'));
        
        // Crear inversión
        $stmt = $conn->prepare("INSERT INTO inversiones (usuario_id, tipo_inversion_id, monto_invertido, ganancia_diaria, fecha_inicio, fecha_fin) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("iiddss", $user_id, $tipo_inversion_id, $monto, $tipo['ganancia_diaria'], $fecha_inicio, $fecha_fin);
        
        if (!$stmt->execute()) {
            throw new Exception("Error al crear la inversión");
        }
        
        $inversion_id = $conn->insert_id;
        $stmt->close();
        
        // Actualizar saldo del usuario
        $stmt = $conn->prepare("UPDATE users SET saldo_disponible = saldo_disponible - ?, saldo_invertido = saldo_invertido + ? WHERE id = ?");
        $stmt->bind_param("ddi", $monto, $monto, $user_id);
        
        if (!$stmt->execute()) {
            throw new Exception("Error al actualizar el saldo");
        }
        $stmt->close();
        
        // Registrar transacción
        $stmt = $conn->prepare("SELECT saldo_disponible, saldo_invertido FROM users WHERE id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $new_balance = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        
        $stmt = $conn->prepare("INSERT INTO transacciones (usuario_id, tipo, monto, descripcion, referencia_id, saldo_anterior, saldo_nuevo) VALUES (?, 'inversion', ?, ?, ?, ?, ?)");
        $saldo_anterior = $user['saldo_disponible'] + $monto;
        $descripcion = "Inversión en " . $tipo['nombre'];
        $stmt->bind_param("isdidd", $user_id, $monto, $descripcion, $inversion_id, $saldo_anterior, $new_balance['saldo_disponible']);
        $stmt->execute();
        $stmt->close();
        
        // Confirmar transacción
        $conn->commit();
        closeConnection($conn);
        
        return ['success' => true, 'message' => 'Inversión realizada exitosamente', 'inversion_id' => $inversion_id];
        
    } catch (Exception $e) {
        $conn->rollback();
        closeConnection($conn);
        return ['success' => false, 'message' => $e->getMessage()];
    }
}

// Generar ganancias diarias
function updateDailyGains($user_id) {
    $conn = getConnection();
    
    $hoy = date('Y-m-d');
    
    // Obtener inversiones activas
    $stmt = $conn->prepare("SELECT * FROM inversiones WHERE usuario_id = ? AND estado = 'activa' AND fecha_fin >= ?");
    $stmt->bind_param("is", $user_id, $hoy);
    $stmt->execute();
    $inversiones = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    
    $total_ganancia = 0;
    
    foreach ($inversiones as $inversion) {
        // Verificar si ya se generó ganancia hoy
        $stmt = $conn->prepare("SELECT id FROM ganancias_diarias WHERE inversion_id = ? AND fecha = ?");
        $stmt->bind_param("is", $inversion['id'], $hoy);
        $stmt->execute();
        $existe = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        
        if (!$existe) {
            // Generar ganancia diaria
            $stmt = $conn->prepare("INSERT INTO ganancias_diarias (inversion_id, usuario_id, monto, fecha) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("iids", $inversion['id'], $user_id, $inversion['ganancia_diaria'], $hoy);
            $stmt->execute();
            $stmt->close();
            
            // Actualizar ganancia total acumulada
            $stmt = $conn->prepare("UPDATE inversiones SET ganancia_total_acumulada = ganancia_total_acumulada + ? WHERE id = ?");
            $stmt->bind_param("di", $inversion['ganancia_diaria'], $inversion['id']);
            $stmt->execute();
            $stmt->close();
            
            $total_ganancia += $inversion['ganancia_diaria'];
        }
    }
    
    // Actualizar saldo disponible si hay ganancias nuevas
    if ($total_ganancia > 0) {
        $stmt = $conn->prepare("UPDATE users SET saldo_disponible = saldo_disponible + ?, saldo = saldo + ? WHERE id = ?");
        $stmt->bind_param("ddi", $total_ganancia, $total_ganancia, $user_id);
        $stmt->execute();
        $stmt->close();
        
        // Registrar transacciones de ganancias
        foreach ($inversiones as $inversion) {
            $stmt = $conn->prepare("SELECT id FROM ganancias_diarias WHERE inversion_id = ? AND fecha = ?");
            $stmt->bind_param("is", $inversion['id'], $hoy);
            $stmt->execute();
            $ganancia = $stmt->get_result()->fetch_assoc();
            $stmt->close();
            
            if ($ganancia) {
                // Obtener saldo actual del usuario (después de agregar la ganancia)
                $stmt = $conn->prepare("SELECT saldo_disponible FROM users WHERE id = ?");
                $stmt->bind_param("i", $user_id);
                $stmt->execute();
                $saldo = $stmt->get_result()->fetch_assoc();
                $stmt->close();
                
                // Calcular el saldo anterior restando la ganancia que se acaba de agregar
                $saldo_anterior = $saldo['saldo_disponible'] - $inversion['ganancia_diaria'];
                
                // Registrar transacción con saldo anterior y nuevo
                $stmt = $conn->prepare("INSERT INTO transacciones (usuario_id, tipo, monto, descripcion, referencia_id, saldo_anterior, saldo_nuevo) VALUES (?, 'ganancia', ?, ?, ?, ?, ?)");
                $descripcion = "Ganancia diaria de inversión #" . $inversion['id'];
                // bind_param: i=usuario_id, d=ganancia_diaria, s=descripcion, i=referencia_id, d=saldo_anterior, d=saldo_nuevo
                $stmt->bind_param("idsidd", $user_id, $inversion['ganancia_diaria'], $descripcion, $ganancia['id'], $saldo_anterior, $saldo['saldo_disponible']);
                $stmt->execute();
                $stmt->close();
            }
        }
    }
    
    closeConnection($conn);
    return $total_ganancia;
}
?>

