<?php
// Capturar todos los errores pero no mostrarlos directamente
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

// Iniciar buffer de salida para capturar cualquier salida no deseada
ob_start();

require_once __DIR__ . '/../../config/config.php';

// Limpiar cualquier salida que haya ocurrido antes del header
ob_clean();

header('Content-Type: application/json');
requireAdmin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    exit();
}

$action = isset($_GET['action']) ? $_GET['action'] : '';

$conn = getConnection();
$admin_id = $_SESSION['user_id'];

try {
    switch ($action) {
        case 'create':
            $titulo = isset($_POST['titulo']) ? sanitize($_POST['titulo']) : '';
            $mensaje = isset($_POST['mensaje']) ? sanitize($_POST['mensaje']) : '';
            $tipo = isset($_POST['tipo']) ? sanitize($_POST['tipo']) : 'info';
            $fecha_inicio = isset($_POST['fecha_inicio']) ? sanitize($_POST['fecha_inicio']) : '';
            $fecha_fin = isset($_POST['fecha_fin']) ? sanitize($_POST['fecha_fin']) : '';
            $prioridad = isset($_POST['prioridad']) ? intval($_POST['prioridad']) : 0;
            
            if (empty($titulo) || empty($mensaje) || empty($fecha_inicio) || empty($fecha_fin)) {
                throw new Exception('Todos los campos son requeridos');
            }
            
            // Validar fechas
            $fecha_inicio_obj = new DateTime($fecha_inicio);
            $fecha_fin_obj = new DateTime($fecha_fin);
            
            if ($fecha_fin_obj <= $fecha_inicio_obj) {
                throw new Exception('La fecha de fin debe ser posterior a la fecha de inicio');
            }
            
            // Validar tipo
            $tipos_validos = ['info', 'success', 'warning', 'error', 'celebracion'];
            if (!in_array($tipo, $tipos_validos)) {
                throw new Exception('Tipo de aviso no válido');
            }
            
            $stmt = $conn->prepare("INSERT INTO avisos (titulo, mensaje, tipo, fecha_inicio, fecha_fin, prioridad, admin_id) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("sssssii", $titulo, $mensaje, $tipo, $fecha_inicio, $fecha_fin, $prioridad, $admin_id);
            
            if (!$stmt->execute()) {
                throw new Exception('Error al crear el aviso');
            }
            
            $stmt->close();
            closeConnection($conn);
            
            echo json_encode(['success' => true, 'message' => 'Aviso creado exitosamente']);
            break;
            
        case 'update':
            $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
            $titulo = isset($_POST['titulo']) ? sanitize($_POST['titulo']) : '';
            $mensaje = isset($_POST['mensaje']) ? sanitize($_POST['mensaje']) : '';
            $tipo = isset($_POST['tipo']) ? sanitize($_POST['tipo']) : 'info';
            $fecha_inicio = isset($_POST['fecha_inicio']) ? sanitize($_POST['fecha_inicio']) : '';
            $fecha_fin = isset($_POST['fecha_fin']) ? sanitize($_POST['fecha_fin']) : '';
            $prioridad = isset($_POST['prioridad']) ? intval($_POST['prioridad']) : 0;
            
            if ($id <= 0 || empty($titulo) || empty($mensaje) || empty($fecha_inicio) || empty($fecha_fin)) {
                throw new Exception('Todos los campos son requeridos');
            }
            
            // Validar fechas
            $fecha_inicio_obj = new DateTime($fecha_inicio);
            $fecha_fin_obj = new DateTime($fecha_fin);
            
            if ($fecha_fin_obj <= $fecha_inicio_obj) {
                throw new Exception('La fecha de fin debe ser posterior a la fecha de inicio');
            }
            
            // Validar tipo
            $tipos_validos = ['info', 'success', 'warning', 'error', 'celebracion'];
            if (!in_array($tipo, $tipos_validos)) {
                throw new Exception('Tipo de aviso no válido');
            }
            
            $stmt = $conn->prepare("UPDATE avisos SET titulo = ?, mensaje = ?, tipo = ?, fecha_inicio = ?, fecha_fin = ?, prioridad = ? WHERE id = ?");
            $stmt->bind_param("sssssii", $titulo, $mensaje, $tipo, $fecha_inicio, $fecha_fin, $prioridad, $id);
            
            if (!$stmt->execute()) {
                throw new Exception('Error al actualizar el aviso');
            }
            
            $stmt->close();
            closeConnection($conn);
            
            echo json_encode(['success' => true, 'message' => 'Aviso actualizado exitosamente']);
            break;
            
        case 'toggle':
            $data = json_decode(file_get_contents('php://input'), true);
            $id = isset($data['id']) ? intval($data['id']) : 0;
            
            if ($id <= 0) {
                throw new Exception('ID de aviso no válido');
            }
            
            // Obtener estado actual
            $stmt = $conn->prepare("SELECT estado FROM avisos WHERE id = ?");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows === 0) {
                $stmt->close();
                throw new Exception('Aviso no encontrado');
            }
            
            $aviso = $result->fetch_assoc();
            $nuevo_estado = $aviso['estado'] === 'activo' ? 'inactivo' : 'activo';
            $stmt->close();
            
            // Actualizar estado
            $stmt = $conn->prepare("UPDATE avisos SET estado = ? WHERE id = ?");
            $stmt->bind_param("si", $nuevo_estado, $id);
            
            if (!$stmt->execute()) {
                throw new Exception('Error al cambiar el estado del aviso');
            }
            
            $stmt->close();
            closeConnection($conn);
            
            echo json_encode(['success' => true, 'message' => 'Estado del aviso actualizado exitosamente']);
            break;
            
        case 'delete':
            $data = json_decode(file_get_contents('php://input'), true);
            $id = isset($data['id']) ? intval($data['id']) : 0;
            
            if ($id <= 0) {
                throw new Exception('ID de aviso no válido');
            }
            
            $stmt = $conn->prepare("DELETE FROM avisos WHERE id = ?");
            $stmt->bind_param("i", $id);
            
            if (!$stmt->execute()) {
                throw new Exception('Error al eliminar el aviso');
            }
            
            $stmt->close();
            closeConnection($conn);
            
            echo json_encode(['success' => true, 'message' => 'Aviso eliminado exitosamente']);
            break;
            
        default:
            throw new Exception('Acción no válida');
    }
    
} catch (Exception $e) {
    closeConnection($conn);
    error_log("Error en avisos.php: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>

