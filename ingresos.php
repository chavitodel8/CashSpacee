<?php
require_once 'config/config.php';
requireLogin();

$conn = getConnection();
$user_id = $_SESSION['user_id'];

// Obtener información del usuario
$stmt = $conn->prepare("SELECT saldo_disponible, saldo_invertido, codigo_invitacion FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();

// Obtener ganancias de hoy
$stmt = $conn->prepare("SELECT COALESCE(SUM(gd.monto), 0) as ganancias_hoy
                       FROM ganancias_diarias gd
                       WHERE gd.usuario_id = ? AND DATE(gd.fecha) = CURDATE()");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$ganancias_hoy = $result->fetch_assoc()['ganancias_hoy'];
$stmt->close();

// Filtro de estado (en progreso o expirado)
$filtro_estado = isset($_GET['estado']) ? sanitize($_GET['estado']) : 'activa';

// Contar proyectos por estado
if ($filtro_estado === 'activa') {
    $stmt = $conn->prepare("SELECT COUNT(*) as total FROM inversiones WHERE usuario_id = ? AND estado = 'activa'");
    $stmt->bind_param("i", $user_id);
} else {
    $stmt = $conn->prepare("SELECT COUNT(*) as total FROM inversiones WHERE usuario_id = ? AND estado IN ('completada', 'cancelada')");
    $stmt->bind_param("i", $user_id);
}
$stmt->execute();
$result = $stmt->get_result();
$total_proyectos = $result->fetch_assoc()['total'];
$stmt->close();

// Obtener inversiones según el filtro
if ($filtro_estado === 'activa') {
    $estado_query = "= 'activa'";
} else {
    $estado_query = "IN ('completada', 'cancelada')";
}

$query = "SELECT i.*, ti.nombre as tipo_inversion, ti.ganancia_diaria, ti.imagen as tipo_imagen
          FROM inversiones i
          JOIN tipos_inversion ti ON i.tipo_inversion_id = ti.id
          WHERE i.usuario_id = ? AND i.estado $estado_query
          ORDER BY i.fecha_creacion DESC";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$inversiones = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Contar total de proyectos
$stmt = $conn->prepare("SELECT COUNT(*) as total FROM inversiones WHERE usuario_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$total_proyectos_todos = $result->fetch_assoc()['total'];
$stmt->close();

closeConnection($conn);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inversión - CashSpace</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            background: var(--light-color);
        }
        .investment-header {
            background: white;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        }
        .summary-cards {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 15px;
            margin-bottom: 30px;
        }
        .summary-card {
            background: linear-gradient(135deg, #fbbf24 0%, #f59e0b 100%);
            border-radius: 15px;
            padding: 20px;
            color: white;
            position: relative;
            overflow: hidden;
        }
        .summary-card.red {
            background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
        }
        .summary-card-icon {
            position: absolute;
            top: 10px;
            right: 10px;
            opacity: 0.3;
            font-size: 40px;
        }
        .summary-card-label {
            font-size: 14px;
            opacity: 0.9;
            margin-bottom: 8px;
        }
        .summary-card-value {
            font-size: 24px;
            font-weight: 700;
        }
        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
        }
        .section-title {
            font-size: 18px;
            font-weight: 700;
            color: #1f2937;
        }
        .tabs {
            display: flex;
            gap: 10px;
        }
        .tab {
            padding: 8px 20px;
            border-radius: 20px;
            background: #e5e7eb;
            color: #6b7280;
            border: none;
            cursor: pointer;
            font-size: 14px;
            font-weight: 600;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .tab.active {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            background: white;
            border-radius: 15px;
            margin-top: 20px;
        }
        .empty-illustration {
            position: relative;
            width: 200px;
            height: 200px;
            margin: 0 auto 20px;
        }
        .empty-circle {
            width: 150px;
            height: 150px;
            background: #f3f4f6;
            border-radius: 50%;
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .empty-icon {
            font-size: 60px;
            color: #667eea;
        }
        .empty-clouds {
            position: absolute;
            width: 100%;
            height: 100%;
        }
        .empty-cloud {
            position: absolute;
            background: #e5e7eb;
            border-radius: 50px;
        }
        .empty-cloud:nth-child(1) {
            width: 60px;
            height: 60px;
            top: 10px;
            left: 20px;
        }
        .empty-cloud:nth-child(2) {
            width: 40px;
            height: 40px;
            top: 30px;
            right: 30px;
        }
        .empty-cloud:nth-child(3) {
            width: 50px;
            height: 50px;
            bottom: 20px;
            left: 40px;
        }
        .empty-line {
            position: absolute;
            border: 2px dashed #d1d5db;
            top: 20px;
            left: 30px;
            width: 80px;
            height: 60px;
        }
        .empty-plane {
            position: absolute;
            top: 0;
            right: 20px;
            font-size: 24px;
            color: #667eea;
            transform: rotate(-30deg);
        }
        .empty-title {
            font-size: 18px;
            font-weight: 700;
            color: #1f2937;
            margin-top: 20px;
        }
        .investment-list {
            background: white;
            border-radius: 15px;
            padding: 20px;
            margin-top: 20px;
        }
        .investment-item {
            background: white;
            border-radius: 15px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.08);
            display: flex;
            gap: 20px;
            align-items: flex-start;
        }
        .investment-item:last-child {
            margin-bottom: 0;
        }
        .investment-image-wrapper {
            flex-shrink: 0;
            width: 120px;
            height: 120px;
            border-radius: 12px;
            overflow: hidden;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .investment-image {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        .investment-image-placeholder {
            font-size: 48px;
            color: white;
        }
        .investment-details {
            flex: 1;
            min-width: 0;
        }
        .investment-name {
            font-size: 18px;
            font-weight: 700;
            color: #1f2937;
            margin-bottom: 15px;
        }
        .investment-info-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 15px;
            margin-bottom: 15px;
        }
        .investment-info-item {
            display: flex;
            flex-direction: column;
            gap: 4px;
        }
        .investment-info-label {
            font-size: 13px;
            color: #6b7280;
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .investment-info-value {
            font-size: 16px;
            font-weight: 700;
            color: #1f2937;
        }
        .investment-info-value.orange {
            color: #f59e0b;
        }
        .investment-info-value.red {
            color: #ef4444;
        }
        .investment-time-info {
            display: flex;
            flex-direction: column;
            gap: 8px;
            margin-top: 10px;
            padding-top: 15px;
            border-top: 1px solid #e5e7eb;
        }
        .investment-time-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .investment-time-label {
            font-size: 13px;
            color: #6b7280;
        }
        .investment-time-value {
            font-size: 13px;
            color: #f59e0b;
            font-weight: 600;
            font-family: monospace;
        }
        
        /* Responsive para Ingresos */
        @media (max-width: 768px) {
            .summary-cards {
                grid-template-columns: 1fr;
                gap: 12px;
            }
            
            .summary-card {
                padding: 18px;
            }
            
            .summary-card-value {
                font-size: 20px;
            }
            
            .section-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 15px;
            }
            
            .tabs {
                width: 100%;
                justify-content: stretch;
            }
            
            .tab {
                flex: 1;
                text-align: center;
                padding: 10px 15px;
            }
            
            .investment-item {
                flex-direction: column;
                gap: 15px;
                padding: 15px;
            }
            
            .investment-image-wrapper {
                width: 100%;
                height: 200px;
            }
            
            .investment-info-grid {
                grid-template-columns: 1fr;
                gap: 12px;
            }
            
            .investment-name {
                font-size: 16px;
            }
            
            .investment-info-value {
                font-size: 14px;
            }
        }
        
        @media (max-width: 480px) {
            .investment-header h1 {
                font-size: 20px;
            }
            
            .summary-card {
                padding: 15px;
            }
            
            .summary-card-value {
                font-size: 18px;
            }
            
            .section-title {
                font-size: 16px;
            }
            
            .tab {
                font-size: 13px;
                padding: 8px 12px;
            }
            
            .investment-item {
                padding: 12px;
            }
            
            .investment-image-wrapper {
                height: 150px;
            }
            
            .investment-name {
                font-size: 15px;
                margin-bottom: 12px;
            }
            
            .investment-info-label {
                font-size: 12px;
            }
            
            .investment-info-value {
                font-size: 14px;
            }
            
            .investment-time-label,
            .investment-time-value {
                font-size: 12px;
            }
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar">
        <div class="navbar-content">
            <a href="index.php" class="navbar-brand">
                <img src="assets/images/logo.png" alt="CashSpace" onerror="this.style.display='none'; this.nextElementSibling.style.display='block';">
                <span style="display:none; background: var(--gradient-primary); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text;">CashSpace</span>
            </a>
            <div class="navbar-user">
                <a href="index.php" class="btn btn-outline">Inicio</a>
                <a href="logout.php" class="btn btn-danger">Salir</a>
            </div>
        </div>
    </nav>

    <!-- Contenedor principal -->
    <div class="container">
        <div class="investment-header">
            <h1 style="text-align: center; font-size: 24px; font-weight: 700; color: #1f2937; margin: 0;">Inversión</h1>
        </div>

        <!-- Tarjetas de resumen -->
        <div class="summary-cards">
            <div class="summary-card">
                <div class="summary-card-icon">
                    <i class="fas fa-chart-line"></i>
                </div>
                <div class="summary-card-label">Mi saldo</div>
                <div class="summary-card-value"><?php echo formatCurrency($user['saldo_disponible']); ?></div>
            </div>
            <div class="summary-card red">
                <div class="summary-card-icon">
                    <i class="fas fa-chart-line"></i>
                </div>
                <div class="summary-card-label">Número de proyectos</div>
                <div class="summary-card-value"><?php echo number_format($total_proyectos_todos); ?></div>
            </div>
        </div>

        <!-- Mi proyecto de inversión -->
        <div>
            <div class="section-header">
                <h2 class="section-title">Mi proyecto de inversión</h2>
                <div class="tabs">
                    <a href="?estado=activa" class="tab <?php echo $filtro_estado === 'activa' ? 'active' : ''; ?>">
                        <i class="fas fa-chart-bar"></i>
                        En progreso
                    </a>
                    <a href="?estado=completada" class="tab <?php echo $filtro_estado !== 'activa' ? 'active' : ''; ?>">
                        Expirado
                    </a>
                </div>
            </div>

            <?php if (empty($inversiones)): ?>
                <div class="empty-state">
                    <div class="empty-illustration">
                        <div class="empty-clouds">
                            <div class="empty-cloud"></div>
                            <div class="empty-cloud"></div>
                            <div class="empty-cloud"></div>
                        </div>
                        <div class="empty-line"></div>
                        <div class="empty-circle">
                            <i class="fas fa-briefcase empty-icon"></i>
                        </div>
                        <div class="empty-plane">
                            <i class="fas fa-paper-plane"></i>
                        </div>
                    </div>
                    <div class="empty-title">¡Sin datos!</div>
                    <p style="color: #6b7280; margin-top: 10px;">
                        <?php echo $filtro_estado === 'activa' ? 'No tienes inversiones en progreso.' : 'No tienes inversiones expiradas.'; ?>
                    </p>
                    <?php if ($filtro_estado === 'activa'): ?>
                        <a href="index.php" class="btn btn-primary" style="margin-top: 20px;">
                            <i class="fas fa-plus"></i> Invertir ahora
                        </a>
                    <?php endif; ?>
                </div>
            <?php else: ?>
                <div class="investment-list">
                    <?php foreach ($inversiones as $inversion): 
                        // Calcular días restantes
                        $fecha_fin = new DateTime($inversion['fecha_fin']);
                        $fecha_hoy = new DateTime();
                        $dias_restantes = max(0, $fecha_hoy->diff($fecha_fin)->days);
                        $dias_totales = 30; // o calcular desde fecha_inicio a fecha_fin
                        $fecha_inicio = new DateTime($inversion['fecha_inicio']);
                        $dias_totales = $fecha_inicio->diff($fecha_fin)->days;
                        $dias_transcurridos = $dias_totales - $dias_restantes;
                    ?>
                        <div class="investment-item">
                            <!-- Imagen del producto -->
                            <div class="investment-image-wrapper">
                                <?php if (!empty($inversion['tipo_imagen'])): ?>
                                    <img src="<?php echo htmlspecialchars($inversion['tipo_imagen']); ?>" alt="<?php echo htmlspecialchars($inversion['tipo_inversion']); ?>" class="investment-image">
                                <?php else: ?>
                                    <div class="investment-image-placeholder">💎</div>
                                <?php endif; ?>
                            </div>
                            
                            <!-- Detalles de la inversión -->
                            <div class="investment-details">
                                <div class="investment-name"><?php echo htmlspecialchars($inversion['tipo_inversion']); ?></div>
                                
                                <div class="investment-info-grid">
                                    <div class="investment-info-item">
                                        <div class="investment-info-label">Ingreso acumulado</div>
                                        <div class="investment-info-value orange"><?php echo formatCurrency($inversion['ganancia_total_acumulada']); ?></div>
                                    </div>
                                    
                                    <div class="investment-info-item">
                                        <div class="investment-info-label">Días restantes</div>
                                        <div class="investment-info-value"><?php echo $dias_transcurridos; ?>/<?php echo $dias_totales; ?> Days</div>
                                    </div>
                                    
                                    <div class="investment-info-item">
                                        <div class="investment-info-label">Ingreso diario</div>
                                        <div class="investment-info-value red"><?php echo formatCurrency($inversion['ganancia_diaria']); ?></div>
                                    </div>
                                </div>
                                
                                <div class="investment-time-info">
                                    <div class="investment-time-item">
                                        <span class="investment-time-label">Tiempo de compra</span>
                                        <span class="investment-time-value"><?php echo date('Y-m-d H:i:s', strtotime($inversion['fecha_creacion'])); ?></span>
                                    </div>
                                    <div class="investment-time-item">
                                        <span class="investment-time-label">Fin del tiempo</span>
                                        <span class="investment-time-value"><?php echo date('Y-m-d H:i:s', strtotime($inversion['fecha_fin'])); ?></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Barra de navegación inferior -->
    <nav class="bottom-nav" style="position: fixed; bottom: 0; left: 0; right: 0; background: white; border-top: 1px solid #e5e7eb; padding: 10px 0; display: flex; justify-content: space-around; z-index: 1000; box-shadow: 0 -2px 10px rgba(0,0,0,0.1);">
        <a href="index.php" class="nav-item" style="display: flex; flex-direction: column; align-items: center; text-decoration: none; color: #9ca3af; padding: 5px 15px;">
            <i class="fas fa-home" style="font-size: 24px; margin-bottom: 5px;"></i>
            <span style="font-size: 12px;">Hogar</span>
        </a>
        <a href="ingresos.php" class="nav-item" style="display: flex; flex-direction: column; align-items: center; text-decoration: none; color: #667eea; padding: 5px 15px;">
            <i class="fas fa-chart-line" style="font-size: 24px; margin-bottom: 5px;"></i>
            <span style="font-size: 12px; font-weight: 600;">Ingreso</span>
            <div style="width: 100%; height: 3px; background: #667eea; margin-top: 5px; border-radius: 2px;"></div>
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
        .container {
            padding-bottom: 80px;
        }
        .nav-item:hover {
            color: #667eea !important;
        }
    </style>

    <script src="js/main.js"></script>
</body>
</html>

