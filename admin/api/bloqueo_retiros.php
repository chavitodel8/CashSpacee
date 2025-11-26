<?php
require_once __DIR__ . '/../../config/config.php';

header('Content-Type: application/json');
requireAdmin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    exit();
}

$data = json_decode(file_get_contents('php://input'), true);
$accion = isset($data['accion']) ? $data['accion'] : '';
$tipo_duracion = isset($data['tipo_duracion']) ? $data['tipo_duracion'] : '';
$duracion = isset($data['duracion']) ? intval($data['duracion']) : 0;
$descripcion = isset($data['descripcion']) ? sanitize($data['descripcion']) : '';

if (!in_array($accion, ['activar', 'desactivar'])) {
    echo json_encode(['success' => false, 'message' => 'Acción no válida']);
    exit();
}

$conn = getConnection();
$admin_id = $_SESSION['user_id'];

try {
    // Obtener bloqueo actual
    $result = $conn->query("SELECT * FROM bloqueo_retiros ORDER BY fecha_creacion DESC LIMIT 1");
    $bloqueo_actual = $result->fetch_assoc();
    
    if ($accion === 'activar') {
        // Validar duración
        if ($tipo_duracion === 'indefinido') {
            $indefinido = 1;
            $duracion_horas = null;
            $duracion_dias = null;
            $fecha_fin = null;
        } else {
            $indefinido = 0;
            $fecha_inicio = date('Y-m-d H:i:s');
            
            if ($tipo_duracion === 'horas') {
                $duracion_horas = $duracion;
                $duracion_dias = null;
                $fecha_fin = date('Y-m-d H:i:s', strtotime("+{$duracion} hours"));
            } else {
                $duracion_horas = null;
                $duracion_dias = $duracion;
                $fecha_fin = date('Y-m-d H:i:s', strtotime("+{$duracion} days"));
            }
        }
        
        $fecha_fin_value = $indefinido ? null : $fecha_fin;
        
        if ($bloqueo_actual) {
            // Actualizar bloqueo existente
            $stmt = $conn->prepare("UPDATE bloqueo_retiros SET activo = 1, fecha_inicio = NOW(), fecha_fin = ?, duracion_horas = ?, duracion_dias = ?, indefinido = ?, descripcion = ?, admin_id = ? WHERE id = ?");
            $stmt->bind_param("siiisii", $fecha_fin_value, $duracion_horas, $duracion_dias, $indefinido, $descripcion, $admin_id, $bloqueo_actual['id']);
        } else {
            // Crear nuevo bloqueo
            $stmt = $conn->prepare("INSERT INTO bloqueo_retiros (activo, fecha_inicio, fecha_fin, duracion_horas, duracion_dias, indefinido, descripcion, admin_id) VALUES (1, NOW(), ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("siiisi", $fecha_fin_value, $duracion_horas, $duracion_dias, $indefinido, $descripcion, $admin_id);
        }
        
        $stmt->execute();
        $stmt->close();
        
        echo json_encode(['success' => true, 'message' => 'Bloqueo de retiros activado exitosamente']);
        
    } else {
        // Desactivar bloqueo
        if ($bloqueo_actual) {
            $stmt = $conn->prepare("UPDATE bloqueo_retiros SET activo = 0, fecha_fin = NOW(), descripcion = ?, admin_id = ? WHERE id = ?");
            $stmt->bind_param("sii", $descripcion, $admin_id, $bloqueo_actual['id']);
            $stmt->execute();
            $stmt->close();
        }
        
        echo json_encode(['success' => true, 'message' => 'Bloqueo de retiros desactivado exitosamente']);
    }
    
    closeConnection($conn);
    
} catch (Exception $e) {
    closeConnection($conn);
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}
?>

