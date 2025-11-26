<?php
require_once __DIR__ . '/../config/config.php';

// Función para registrar usuario
function registerUser($telefono, $password, $codigo_invitacion = null) {
    $conn = getConnection();
    
    // Verificar si el teléfono ya existe
    $stmt = $conn->prepare("SELECT id FROM users WHERE telefono = ?");
    $stmt->bind_param("s", $telefono);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $stmt->close();
        closeConnection($conn);
        return ['success' => false, 'message' => 'Este número de teléfono ya está registrado'];
    }
    
    // Generar código de invitación único
    $codigo_invitacion_generado = generateUniqueCode(8);
    $codigo_exists = true;
    while ($codigo_exists) {
        $check_stmt = $conn->prepare("SELECT id FROM users WHERE codigo_invitacion = ?");
        $check_stmt->bind_param("s", $codigo_invitacion_generado);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();
        if ($check_result->num_rows == 0) {
            $codigo_exists = false;
        } else {
            $codigo_invitacion_generado = generateUniqueCode(8);
        }
        $check_stmt->close();
    }
    
    // Hashear contraseña
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    
    // Verificar y obtener el usuario que invitó si se proporcionó código
    $usuario_padre_id = null;
    if ($codigo_invitacion) {
        $check_codigo = $conn->prepare("SELECT id FROM users WHERE codigo_invitacion = ?");
        $check_codigo->bind_param("s", $codigo_invitacion);
        $check_codigo->execute();
        $result_codigo = $check_codigo->get_result();
        if ($result_codigo->num_rows > 0) {
            $usuario_padre = $result_codigo->fetch_assoc();
            $usuario_padre_id = $usuario_padre['id'];
        }
        $check_codigo->close();
    }
    
    // Insertar usuario con bono de registro de 10 Bs
    $bono_registro = 10.00;
    $stmt = $conn->prepare("INSERT INTO users (telefono, password, codigo_invitacion, codigo_referido, saldo, saldo_disponible) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssdd", $telefono, $hashed_password, $codigo_invitacion_generado, $codigo_invitacion, $bono_registro, $bono_registro);
    
    if ($stmt->execute()) {
        $user_id = $conn->insert_id;
        $stmt->close();
        
        // Registrar transacción del bono de registro
        $stmt = $conn->prepare("INSERT INTO transacciones (usuario_id, tipo, monto, descripcion, saldo_anterior, saldo_nuevo) VALUES (?, 'codigo', ?, ?, 0.00, ?)");
        $descripcion = "Bono de bienvenida por registro";
        $stmt->bind_param("isdd", $user_id, $bono_registro, $descripcion, $bono_registro);
        $stmt->execute();
        $stmt->close();
        
        // Si hay un usuario padre, crear registros en la tabla equipos para los niveles
        if ($usuario_padre_id) {
            // Nivel 1: El usuario que invitó directamente
            $stmt_equipo1 = $conn->prepare("INSERT INTO equipos (usuario_padre_id, usuario_referido_id, nivel) VALUES (?, ?, 1)");
            $stmt_equipo1->bind_param("ii", $usuario_padre_id, $user_id);
            $stmt_equipo1->execute();
            $stmt_equipo1->close();
            
            // Nivel 2: Obtener el usuario padre del nivel 1
            $stmt_padre2 = $conn->prepare("SELECT DISTINCT e1.usuario_padre_id 
                                          FROM equipos e1 
                                          WHERE e1.usuario_referido_id = ? AND e1.nivel = 1 
                                          LIMIT 1");
            $stmt_padre2->bind_param("i", $usuario_padre_id);
            $stmt_padre2->execute();
            $result_padre2 = $stmt_padre2->get_result();
            if ($result_padre2->num_rows > 0) {
                $padre2 = $result_padre2->fetch_assoc();
                $padre2_id = $padre2['usuario_padre_id'];
                $stmt_equipo2 = $conn->prepare("INSERT INTO equipos (usuario_padre_id, usuario_referido_id, nivel) VALUES (?, ?, 2)");
                $stmt_equipo2->bind_param("ii", $padre2_id, $user_id);
                $stmt_equipo2->execute();
                $stmt_equipo2->close();
                
                // Nivel 3: Obtener el usuario padre del nivel 2
                $stmt_padre3 = $conn->prepare("SELECT DISTINCT e2.usuario_padre_id 
                                              FROM equipos e2 
                                              WHERE e2.usuario_referido_id = ? AND e2.nivel = 1 
                                              LIMIT 1");
                $stmt_padre3->bind_param("i", $padre2_id);
                $stmt_padre3->execute();
                $result_padre3 = $stmt_padre3->get_result();
                if ($result_padre3->num_rows > 0) {
                    $padre3 = $result_padre3->fetch_assoc();
                    $padre3_id = $padre3['usuario_padre_id'];
                    $stmt_equipo3 = $conn->prepare("INSERT INTO equipos (usuario_padre_id, usuario_referido_id, nivel) VALUES (?, ?, 3)");
                    $stmt_equipo3->bind_param("ii", $padre3_id, $user_id);
                    $stmt_equipo3->execute();
                    $stmt_equipo3->close();
                }
                $stmt_padre3->close();
            }
            $stmt_padre2->close();
        }
        
        closeConnection($conn);
        return ['success' => true, 'message' => 'Usuario registrado exitosamente', 'user_id' => $user_id];
    } else {
        $error = $stmt->error;
        $stmt->close();
        closeConnection($conn);
        return ['success' => false, 'message' => 'Error al registrar: ' . $error];
    }
}

// Función para iniciar sesión
function loginUser($telefono, $password) {
    $conn = getConnection();
    
    $stmt = $conn->prepare("SELECT id, telefono, password, nombre, tipo_usuario, estado, saldo, saldo_disponible FROM users WHERE telefono = ?");
    $stmt->bind_param("s", $telefono);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        $stmt->close();
        closeConnection($conn);
        return ['success' => false, 'message' => 'Teléfono o contraseña incorrectos'];
    }
    
    $user = $result->fetch_assoc();
    
    // Verificar contraseña
    if (!password_verify($password, $user['password'])) {
        $stmt->close();
        closeConnection($conn);
        return ['success' => false, 'message' => 'Teléfono o contraseña incorrectos'];
    }
    
    // Verificar si el usuario está activo
    if ($user['estado'] !== 'activo') {
        $stmt->close();
        closeConnection($conn);
        return ['success' => false, 'message' => 'Tu cuenta está desactivada'];
    }
    
    // Actualizar último acceso
    $update_stmt = $conn->prepare("UPDATE users SET ultimo_acceso = NOW() WHERE id = ?");
    $update_stmt->bind_param("i", $user['id']);
    $update_stmt->execute();
    $update_stmt->close();
    
    // Iniciar sesión
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['user_telefono'] = $user['telefono'];
    $_SESSION['user_nombre'] = $user['nombre'];
    $_SESSION['user_type'] = $user['tipo_usuario'];
    $_SESSION['user_saldo'] = $user['saldo_disponible'];
    
    $stmt->close();
    closeConnection($conn);
    
    return ['success' => true, 'message' => 'Inicio de sesión exitoso', 'user' => $user];
}

// Función para cerrar sesión
function logoutUser() {
    session_unset();
    session_destroy();
    return ['success' => true, 'message' => 'Sesión cerrada exitosamente'];
}
