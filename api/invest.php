<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/investment.php';

header('Content-Type: application/json');
requireLogin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    exit();
}

$data = json_decode(file_get_contents('php://input'), true);
$tipo_inversion_id = isset($data['tipo_inversion_id']) ? intval($data['tipo_inversion_id']) : 0;

if ($tipo_inversion_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'ID de inversión inválido']);
    exit();
}

$investment = getInvestmentType($tipo_inversion_id);

if (!$investment) {
    echo json_encode(['success' => false, 'message' => 'Tipo de inversión no encontrado']);
    exit();
}

$result = makeInvestment($_SESSION['user_id'], $tipo_inversion_id, $investment['precio_inversion']);

echo json_encode($result);
?>

