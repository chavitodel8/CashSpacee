<?php
require_once 'config/config.php';
requireLogin();

$conn = getConnection();
$user_id = $_SESSION['user_id'];

// Filtro de tipo
$filtro_tipo = isset($_GET['tipo']) ? sanitize($_GET['tipo']) : 'todos';

// Obtener recargas del usuario
$query_recargas = "SELECT id, monto, metodo_pago, estado, fecha_solicitud, fecha_aprobacion 
                   FROM recargas 
                   WHERE usuario_id = ? 
                   ORDER BY fecha_solicitud DESC";
$stmt_recargas = $conn->prepare($query_recargas);
$stmt_recargas->bind_param("i", $user_id);
$stmt_recargas->execute();
$recargas = $stmt_recargas->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt_recargas->close();

// Obtener retiros del usuario
$query_retiros = "SELECT id, monto, metodo_pago, estado, fecha_solicitud, fecha_procesamiento 
                  FROM retiros 
                  WHERE usuario_id = ? 
                  ORDER BY fecha_solicitud DESC";
$stmt_retiros = $conn->prepare($query_retiros);
$stmt_retiros->bind_param("i", $user_id);
$stmt_retiros->execute();
$retiros = $stmt_retiros->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt_retiros->close();

// Combinar y ordenar todas las transacciones
$transacciones = [];

foreach ($recargas as $recarga) {
    $estado_texto = 'Recarga en revisión';
    if ($recarga['estado'] === 'aprobada') {
        $estado_texto = 'Recarga exitosa';
    } elseif ($recarga['estado'] === 'rechazada') {
        $estado_texto = 'Recarga rechazada';
    }
    
    $transacciones[] = [
        'tipo' => 'recarga',
        'id' => $recarga['id'],
        'monto' => $recarga['monto'],
        'estado' => $recarga['estado'],
        'estado_texto' => $estado_texto,
        'fecha' => $recarga['fecha_solicitud'],
        'fecha_procesamiento' => $recarga['fecha_aprobacion']
    ];
}

foreach ($retiros as $retiro) {
    $estado_texto = 'Retiro en revisión';
    if ($retiro['estado'] === 'aprobado') {
        $estado_texto = 'Retiro exitoso';
    } elseif ($retiro['estado'] === 'rechazado') {
        $estado_texto = 'Retiro rechazado';
    }
    
    $transacciones[] = [
        'tipo' => 'retiro',
        'id' => $retiro['id'],
        'monto' => $retiro['monto'],
        'estado' => $retiro['estado'],
        'estado_texto' => $estado_texto,
        'fecha' => $retiro['fecha_solicitud'],
        'fecha_procesamiento' => $retiro['fecha_procesamiento']
    ];
}

// Ordenar por fecha descendente
usort($transacciones, function($a, $b) {
    return strtotime($b['fecha']) - strtotime($a['fecha']);
});

// Filtrar por tipo si es necesario
if ($filtro_tipo !== 'todos') {
    $transacciones = array_filter($transacciones, function($trans) use ($filtro_tipo) {
        return $trans['tipo'] === $filtro_tipo;
    });
    $transacciones = array_values($transacciones); // Reindexar
}

closeConnection($conn);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mi Factura - CashSpace</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            background: #f5f7fa;
        }
        .historial-header {
            background: white;
            padding: 20px;
            margin-bottom: 0;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
            position: relative;
        }
        .back-button {
            position: absolute;
            left: 20px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            font-size: 24px;
            color: #1f2937;
            cursor: pointer;
            padding: 5px 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: color 0.3s;
        }
        .back-button:hover {
            color: #667eea;
        }
        .historial-title {
            text-align: center;
            font-size: 20px;
            font-weight: 700;
            color: #1f2937;
            margin: 0;
        }
        .tabs-container {
            background: white;
            padding: 0 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        }
        .tabs {
            display: flex;
            gap: 0;
            border-bottom: 1px solid #e5e7eb;
        }
        .tab {
            padding: 15px 20px;
            background: none;
            border: none;
            color: #9ca3af;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            position: relative;
            transition: color 0.3s;
        }
        .tab:hover {
            color: #667eea;
        }
        .tab.active {
            color: #667eea;
        }
        .tab.active::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 20px;
            right: 20px;
            height: 3px;
            background: #667eea;
            border-radius: 2px 2px 0 0;
        }
        .transactions-list {
            padding: 20px;
        }
        .transaction-item {
            background: white;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 15px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        }
        .transaction-info {
            flex: 1;
        }
        .transaction-status {
            font-size: 16px;
            font-weight: 600;
            color: #1f2937;
            margin-bottom: 5px;
        }
        .transaction-date {
            font-size: 14px;
            color: #6b7280;
        }
        .transaction-amount {
            font-size: 18px;
            font-weight: 700;
            color: #1f2937;
            text-align: right;
        }
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            background: white;
            border-radius: 15px;
            margin: 20px;
        }
        .empty-icon {
            font-size: 64px;
            color: #d1d5db;
            margin-bottom: 20px;
        }
        .empty-title {
            font-size: 18px;
            font-weight: 700;
            color: #1f2937;
            margin-bottom: 10px;
        }
        .empty-text {
            color: #6b7280;
        }
        .detail-view {
            background: white;
            border-radius: 15px;
            padding: 25px;
            margin: 20px;
            display: none;
        }
        .detail-view.active {
            display: block;
        }
        .detail-row {
            display: flex;
            justify-content: space-between;
            padding: 15px 0;
            border-bottom: 1px solid #e5e7eb;
        }
        .detail-row:last-child {
            border-bottom: none;
        }
        .detail-label {
            color: #6b7280;
            font-size: 14px;
        }
        .detail-value {
            color: #1f2937;
            font-weight: 600;
            font-size: 16px;
        }
        
        /* Responsive para Historial */
        @media (max-width: 768px) {
            .historial-header {
                padding: 15px;
            }
            
            .historial-title {
                font-size: 20px;
            }
            
            .back-button {
                left: 15px;
                font-size: 20px;
            }
            
            .tabs-container {
                padding: 15px;
            }
            
            .tabs {
                gap: 8px;
            }
            
            .tab {
                padding: 10px 16px;
                font-size: 14px;
            }
            
            .transactions-list {
                padding: 15px;
            }
            
            .transaction-item {
                padding: 15px;
                flex-direction: column;
                align-items: flex-start;
                gap: 12px;
            }
            
            .transaction-info {
                width: 100%;
            }
            
            .transaction-amount {
                width: 100%;
                text-align: left;
            }
            
            .transaction-status {
                font-size: 13px;
            }
            
            .transaction-date {
                font-size: 12px;
            }
            
            .detail-item {
                padding: 12px 15px;
            }
            
            .detail-label {
                font-size: 13px;
            }
            
            .detail-value {
                font-size: 14px;
            }
        }
        
        @media (max-width: 480px) {
            .historial-header {
                padding: 12px;
            }
            
            .historial-title {
                font-size: 18px;
                padding-right: 40px;
            }
            
            .back-button {
                left: 12px;
                padding: 5px 8px;
                font-size: 18px;
            }
            
            .tabs-container {
                padding: 12px;
            }
            
            .tab {
                padding: 8px 12px;
                font-size: 13px;
                flex: 1;
                text-align: center;
            }
            
            .transactions-list {
                padding: 12px;
            }
            
            .transaction-item {
                padding: 12px;
            }
            
            .transaction-type {
                font-size: 13px;
                padding: 6px 10px;
            }
            
            .transaction-title {
                font-size: 15px;
            }
            
            .transaction-amount-value {
                font-size: 18px;
            }
            
            .transaction-amount-label {
                font-size: 11px;
            }
            
            .transaction-status {
                font-size: 12px;
                padding: 4px 8px;
            }
            
            .transaction-date {
                font-size: 11px;
            }
            
            .empty-state {
                padding: 40px 15px;
            }
            
            .empty-title {
                font-size: 16px;
            }
            
            .detail-item {
                padding: 10px 12px;
            }
        }
    </style>
</head>
<body>
    <!-- Header -->
    <div class="historial-header">
        <a href="mio.php" class="back-button" style="text-decoration: none; color: #1f2937;">
            <i class="fas fa-chevron-left"></i>
        </a>
        <h1 class="historial-title">Mi factura</h1>
    </div>

    <!-- Tabs -->
    <div class="tabs-container">
        <div class="tabs">
            <a href="?tipo=todos" class="tab <?php echo $filtro_tipo === 'todos' ? 'active' : ''; ?>">Todo</a>
            <a href="?tipo=recarga" class="tab <?php echo $filtro_tipo === 'recarga' ? 'active' : ''; ?>">Recargar</a>
            <a href="?tipo=retiro" class="tab <?php echo $filtro_tipo === 'retiro' ? 'active' : ''; ?>">Retirar</a>
        </div>
    </div>

    <!-- Lista de transacciones -->
    <div class="transactions-list">
        <?php if (empty($transacciones)): ?>
            <div class="empty-state">
                <div class="empty-icon">
                    <i class="fas fa-receipt"></i>
                </div>
                <div class="empty-title">Sin datos</div>
                <div class="empty-text">No hay transacciones registradas</div>
            </div>
        <?php else: ?>
            <?php foreach ($transacciones as $trans): ?>
                <div class="transaction-item" onclick="mostrarDetalle(<?php echo htmlspecialchars(json_encode($trans)); ?>)">
                    <div class="transaction-info">
                        <div class="transaction-status"><?php echo htmlspecialchars($trans['estado_texto']); ?></div>
                        <div class="transaction-date"><?php echo date('Y-m-d H:i:s', strtotime($trans['fecha'])); ?></div>
                    </div>
                    <div class="transaction-amount">
                        <?php echo formatCurrency($trans['monto']); ?>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <!-- Vista de detalle -->
    <div id="detalleView" class="detail-view"></div>

    <!-- Barra de navegación inferior -->
    <nav class="bottom-nav" style="position: fixed; bottom: 0; left: 0; right: 0; background: white; border-top: 1px solid #e5e7eb; padding: 10px 0; display: flex; justify-content: space-around; z-index: 1000; box-shadow: 0 -2px 10px rgba(0,0,0,0.1);">
        <a href="index.php" class="nav-item" style="display: flex; flex-direction: column; align-items: center; text-decoration: none; color: #9ca3af; padding: 5px 15px;">
            <i class="fas fa-home" style="font-size: 24px; margin-bottom: 5px;"></i>
            <span style="font-size: 12px;">Hogar</span>
        </a>
        <a href="ingresos.php" class="nav-item" style="display: flex; flex-direction: column; align-items: center; text-decoration: none; color: #9ca3af; padding: 5px 15px;">
            <i class="fas fa-chart-line" style="font-size: 24px; margin-bottom: 5px;"></i>
            <span style="font-size: 12px;">Ingreso</span>
        </a>
        <a href="equipo.php" class="nav-item" style="display: flex; flex-direction: column; align-items: center; text-decoration: none; color: #9ca3af; padding: 5px 15px;">
            <i class="fas fa-layer-group" style="font-size: 24px; margin-bottom: 5px;"></i>
            <span style="font-size: 12px;">Equipo</span>
        </a>
        <a href="mio.php" class="nav-item" style="display: flex; flex-direction: column; align-items: center; text-decoration: none; color: #9ca3af; padding: 5px 15px;">
            <i class="fas fa-user" style="font-size: 24px; margin-bottom: 5px;"></i>
            <span style="font-size: 12px;">Mío</span>
        </a>
    </nav>

    <style>
        body {
            padding-bottom: 80px;
        }
    </style>

    <script>
        function mostrarDetalle(trans) {
            const detalleView = document.getElementById('detalleView');
            
            if (trans.tipo === 'retiro') {
                // Mostrar vista de detalle para retiro
                const fechaProcesamiento = trans.fecha_procesamiento ? 
                    new Date(trans.fecha_procesamiento).toLocaleString('es-ES', {year: 'numeric', month: '2-digit', day: '2-digit', hour: '2-digit', minute: '2-digit', second: '2-digit'}) : 
                    '-';
                
                detalleView.innerHTML = `
                    <h2 style="margin-bottom: 20px; color: #1f2937;">Detalle del Retiro</h2>
                    <div class="detail-row">
                        <div class="detail-label">Cantidad de retiro</div>
                        <div class="detail-value">${formatCurrency(trans.monto)}</div>
                    </div>
                    <div class="detail-row">
                        <div class="detail-label">Cantidad recibida</div>
                        <div class="detail-value">${trans.estado === 'aprobado' ? formatCurrency(trans.monto) : 'Bs 0,00'}</div>
                    </div>
                    <div class="detail-row">
                        <div class="detail-label">${fechaProcesamiento}</div>
                        <div class="detail-value">${trans.estado_texto}</div>
                    </div>
                `;
            } else {
                // Mostrar vista de detalle para recarga
                const fechaProcesamiento = trans.fecha_procesamiento ? 
                    new Date(trans.fecha_procesamiento).toLocaleString('es-ES', {year: 'numeric', month: '2-digit', day: '2-digit', hour: '2-digit', minute: '2-digit', second: '2-digit'}) : 
                    '-';
                
                detalleView.innerHTML = `
                    <h2 style="margin-bottom: 20px; color: #1f2937;">Detalle de la Recarga</h2>
                    <div class="detail-row">
                        <div class="detail-label">Cantidad de recarga</div>
                        <div class="detail-value">${formatCurrency(trans.monto)}</div>
                    </div>
                    <div class="detail-row">
                        <div class="detail-label">Cantidad recibida</div>
                        <div class="detail-value">${trans.estado === 'aprobada' ? formatCurrency(trans.monto) : 'Bs 0,00'}</div>
                    </div>
                    <div class="detail-row">
                        <div class="detail-label">${fechaProcesamiento}</div>
                        <div class="detail-value">${trans.estado_texto}</div>
                    </div>
                `;
            }
            
            detalleView.classList.add('active');
            setTimeout(() => {
                detalleView.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }, 100);
        }

        function formatCurrency(amount) {
            return new Intl.NumberFormat('es-ES', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            }).format(amount) + ' Bs';
        }

        // Cerrar detalle al hacer clic fuera
        document.addEventListener('click', function(e) {
            const detalleView = document.getElementById('detalleView');
            if (detalleView.classList.contains('active') && !detalleView.contains(e.target) && !e.target.closest('.transaction-item')) {
                detalleView.classList.remove('active');
            }
        });
    </script>
</body>
</html>

