<?php
// Iniciar output buffering para capturar cualquier salida no deseada
if (ob_get_level() == 0) {
    ob_start();
}

require_once __DIR__ . '/../config/config.php';
requireLogin();

// Limpiar cualquier salida previa antes de enviar JSON
while (ob_get_level() > 0) {
    ob_end_clean();
}
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    exit;
}

$monto = isset($_POST['monto']) ? floatval($_POST['monto']) : 0;

if ($monto <= 0) {
    echo json_encode(['success' => false, 'message' => 'Monto inválido']);
    exit;
}

// Cargar QRs personalizados
$qr_codes_config = [];
$qr_config_file = __DIR__ . '/../config/qr_codes.php';

if (file_exists($qr_config_file)) {
    try {
        $qr_codes_config = require $qr_config_file;
    } catch (Exception $e) {
        error_log('Error al cargar qr_codes.php: ' . $e->getMessage());
        $qr_codes_config = [];
    }
} else {
    error_log('Archivo qr_codes.php no encontrado en: ' . $qr_config_file);
}

$monto_str = (string)$monto;

// Verificar si hay un QR personalizado para este monto
if (isset($qr_codes_config[$monto_str]) && !empty($qr_codes_config[$monto_str])) {
    $qr_path = trim($qr_codes_config[$monto_str]);
    
    // Si es base64, usar directamente
    if (strpos($qr_path, 'data:image') === 0) {
        echo json_encode([
            'success' => true,
            'qr_url' => $qr_path,
            'qr_type' => 'base64',
            'monto' => $monto
        ]);
        exit;
    }
    
    // Si es una URL completa, usar directamente
    if (strpos($qr_path, 'http://') === 0 || strpos($qr_path, 'https://') === 0) {
        echo json_encode([
            'success' => true,
            'qr_url' => $qr_path,
            'qr_type' => 'url',
            'monto' => $monto
        ]);
        exit;
    }
    
    // Si es una ruta relativa, convertir a URL
    // Remover barra inicial si existe
    $qr_path_clean = ltrim($qr_path, '/');
    // Construir URL completa
    $base_url = defined('BASE_URL') ? BASE_URL : 'http://' . $_SERVER['HTTP_HOST'] . dirname(dirname($_SERVER['SCRIPT_NAME'])) . '/';
    $qr_url = rtrim($base_url, '/') . '/' . $qr_path_clean;
    
    // Verificar si el archivo existe localmente
    $file_path = __DIR__ . '/../' . $qr_path_clean;
    if (file_exists($file_path)) {
        echo json_encode([
            'success' => true,
            'qr_url' => $qr_url,
            'qr_type' => 'local',
            'monto' => $monto
        ]);
        exit;
    } else {
        // Si el archivo no existe, intentar usar la URL directamente
        echo json_encode([
            'success' => true,
            'qr_url' => $qr_url,
            'qr_type' => 'url',
            'monto' => $monto
        ]);
        exit;
    }
}

// Si no hay QR personalizado, generar uno dinámico
$qr_data = json_encode([
    'monto' => $monto,
    'moneda' => 'BOB',
    'concepto' => 'Recarga CashSpace',
    'fecha' => date('Y-m-d H:i:s')
]);

// Usar API de QR code como fallback
$qr_url = 'https://api.qrserver.com/v1/create-qr-code/?size=300x300&data=' . urlencode($qr_data);

echo json_encode([
    'success' => true,
    'qr_url' => $qr_url,
    'qr_type' => 'generated',
    'qr_data' => $qr_data,
    'monto' => $monto
]);

