<?php
require_once 'config/config.php';
require_once 'includes/investment.php';

requireLogin();

// Obtener información del usuario
$conn = getConnection();
$user_id = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT saldo, saldo_disponible, saldo_invertido, codigo_invitacion FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();

// Actualizar saldo disponible (sumar ganancias pendientes)
updateDailyGains($user_id);

// Obtener inversiones activas
$inversiones_activas = getActiveInvestments($user_id);

// Obtener todas las inversiones disponibles
$tipos_inversion = getAvailableInvestmentTypes();

// Obtener notificaciones
$stmt = $conn->prepare("SELECT * FROM notificaciones WHERE (usuario_id = ? OR usuario_id IS NULL) AND leida = 0 ORDER BY fecha_creacion DESC LIMIT 5");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$notificaciones = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Obtener avisos activos (dentro del rango de fechas y activos)
$stmt = $conn->prepare("SELECT * FROM avisos WHERE estado = 'activo' AND fecha_inicio <= NOW() AND fecha_fin >= NOW() ORDER BY prioridad DESC, fecha_creacion DESC");
$stmt->execute();
$avisos_activos = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

closeConnection($conn);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0, user-scalable=yes">
    <title>Inicio - CashSpace</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
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
                <div class="user-info">
                    <div class="user-avatar">
                        <i class="fas fa-user"></i>
                    </div>
                    <div>
                        <div style="font-weight: 600;"><?php echo htmlspecialchars($_SESSION['user_telefono']); ?></div>
                        <div class="user-balance"><?php echo formatCurrency($user['saldo_disponible']); ?></div>
                    </div>
                </div>
                <?php if (isAdmin()): ?>
                    <a href="admin/index.php" class="btn btn-outline">Admin</a>
                <?php endif; ?>
                <a href="logout.php" class="btn btn-danger">Salir</a>
            </div>
        </div>
    </nav>

    <!-- Contenedor principal -->
    <div class="container">
        <!-- Avisos del Admin -->
        <?php if (!empty($avisos_activos)): ?>
            <?php foreach ($avisos_activos as $aviso): 
                $prioridad = intval($aviso['prioridad']);
                $clase_prioridad = 'prioridad-normal';
                if ($prioridad >= 9) {
                    $clase_prioridad = 'prioridad-critica';
                } elseif ($prioridad >= 6) {
                    $clase_prioridad = 'prioridad-alta';
                } elseif ($prioridad >= 3) {
                    $clase_prioridad = 'prioridad-media';
                }
                
                $iconos = [
                    'info' => 'fa-info-circle',
                    'success' => 'fa-check-circle',
                    'warning' => 'fa-exclamation-triangle',
                    'error' => 'fa-times-circle',
                    'celebracion' => 'fa-gift'
                ];
                $icono = isset($iconos[$aviso['tipo']]) ? $iconos[$aviso['tipo']] : 'fa-bullhorn';
            ?>
                <div class="aviso-banner aviso-tipo-<?php echo $aviso['tipo']; ?> <?php echo $clase_prioridad; ?>" style="margin-bottom: 20px;">
                    <div class="aviso-banner-decoration"></div>
                    <div class="aviso-banner-content">
                        <div class="aviso-banner-icon-wrapper">
                            <div class="aviso-banner-icon">
                                <i class="fas <?php echo $icono; ?>"></i>
                            </div>
                            <?php if ($prioridad >= 6): ?>
                                <div class="aviso-priority-badge">
                                    <i class="fas fa-star"></i>
                                    <span><?php echo $prioridad; ?></span>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="aviso-banner-text aviso-text-centered">
                            <div class="aviso-banner-header">
                                <strong class="aviso-titulo"><?php echo htmlspecialchars($aviso['titulo']); ?></strong>
                                <?php if ($prioridad >= 9): ?>
                                    <span class="aviso-urgente-badge">URGENTE</span>
                                <?php endif; ?>
                            </div>
                            <p class="aviso-mensaje"><?php echo nl2br(htmlspecialchars($aviso['mensaje'])); ?></p>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
        
        <!-- Resumen de saldo (Reorganizado: primero lo más importante) -->
        <div class="card">
            <div class="card-header">
                <h2 class="card-title">Mi Balance</h2>
            </div>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px;">
                <div>
                    <div style="color: #6b7280; font-size: 14px;">Saldo Disponible</div>
                    <div style="font-size: 28px; font-weight: bold; color: var(--secondary-color);">
                        <?php echo formatCurrency($user['saldo_disponible']); ?>
                    </div>
                </div>
                <div>
                    <div style="color: #6b7280; font-size: 14px;">Saldo Invertido</div>
                    <div style="font-size: 28px; font-weight: bold; color: var(--primary-color);">
                        <?php echo formatCurrency($user['saldo_invertido']); ?>
                    </div>
                </div>
                <div>
                    <div style="color: #6b7280; font-size: 14px;">Total</div>
                    <div style="font-size: 28px; font-weight: bold; color: var(--dark-color);">
                        <?php echo formatCurrency($user['saldo']); ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Botones de acción (Reorganizado: después del balance) -->
        <div class="action-buttons">
            <a href="#" onclick="openModal('recargaModal'); return false;" class="action-btn">
                <i class="fas fa-wallet action-btn-icon"></i>
                <span class="action-btn-title">Recargar</span>
                <span style="color: #6b7280; font-size: 14px;">Añadir fondos</span>
            </a>
            <a href="#" onclick="openModal('codigoModal'); return false;" class="action-btn">
                <i class="fas fa-ticket-alt action-btn-icon"></i>
                <span class="action-btn-title">Canjear Código</span>
                <span style="color: #6b7280; font-size: 14px;">Obtener beneficios</span>
            </a>
            <a href="#" onclick="openModal('retiroModal'); return false;" class="action-btn">
                <i class="fas fa-money-bill-wave action-btn-icon"></i>
                <span class="action-btn-title">Retirar</span>
                <span style="color: #6b7280; font-size: 14px;">Transferir fondos</span>
            </a>
            <a href="equipo.php" class="action-btn">
                <i class="fas fa-users action-btn-icon"></i>
                <span class="action-btn-title">Mi Equipo</span>
                <span style="color: #6b7280; font-size: 14px;">Ver mi equipo</span>
            </a>
        </div>
        
        <!-- Aviso Animado (Reorganizado: después de los botones de acción) -->
        <div class="aviso-animado-container">
            <div class="aviso-animado-content">
                <div class="aviso-icono-wrapper">
                    <span class="aviso-icono">🔔</span>
                </div>
                <div class="aviso-texto-wrapper">
                    <div class="aviso-texto-scroll">
                        <span>✨ ¡Gana comisiones invitando amigos! Cada referido te da beneficios</span>
                        <span>💎 Tus inversiones generan ganancias diarias automáticamente</span>
                        <span>🚀 Niveles de equipo: más miembros, mayores comisiones</span>
                        <span>🎁 Canjea códigos promocionales para obtener bonos extra</span>
                        <span>👥 Invita a tus amigos y aumenta tus ganancias</span>
                        <!-- Duplicado para efecto continuo -->
                        <span>✨ ¡Gana comisiones invitando amigos! Cada referido te da beneficios</span>
                        <span>💎 Tus inversiones generan ganancias diarias automáticamente</span>
                        <span>🚀 Niveles de equipo: más miembros, mayores comisiones</span>
                        <span>🎁 Canjea códigos promocionales para obtener bonos extra</span>
                        <span>👥 Invita a tus amigos y aumenta tus ganancias</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Inversiones disponibles -->
        <div class="card">
            <div class="card-header">
                <h2 class="card-title">Planes de Inversión</h2>
            </div>
            <div class="investments-grid">
                <?php foreach ($tipos_inversion as $inversion): 
                    // Determinar la imagen según el nombre del plan
                    $imagen_path = null;
                    if ($inversion['imagen'] && file_exists($inversion['imagen'])) {
                        $imagen_path = $inversion['imagen'];
                    } else {
                        $imagen_path = getInvestmentImagePath($inversion['nombre']);
                    }
                ?>
                    <div class="investment-card">
                        <div class="investment-image" style="background: var(--gradient-primary); display: flex; align-items: center; justify-content: center; color: white; font-size: 64px; overflow: hidden; position: relative;">
                            <?php if ($imagen_path && file_exists($imagen_path)): ?>
                                <img src="<?php echo htmlspecialchars($imagen_path); ?>" alt="<?php echo htmlspecialchars($inversion['nombre']); ?>" style="width: 100%; height: 100%; object-fit: cover;" loading="lazy" decoding="async" onerror="this.style.display='none'; this.parentElement.innerHTML='💎';">
                            <?php else: ?>
                                💎
                            <?php endif; ?>
                        </div>
                        <div class="investment-content">
                            <div class="investment-name"><?php echo htmlspecialchars($inversion['nombre']); ?></div>
                            <div class="investment-price"><?php echo formatCurrency($inversion['precio_inversion']); ?></div>
                            <div class="investment-daily">
                                <i class="fas fa-arrow-up"></i> +<?php echo formatCurrency($inversion['ganancia_diaria']); ?> diarios
                            </div>
                            <div class="investment-limit">
                                <i class="fas fa-info-circle"></i> Límite: <?php echo $inversion['limite_inversion']; ?> inversión
                            </div>
                            <a href="investment-detail.php?id=<?php echo $inversion['id']; ?>" class="investment-btn">
                                Ver Detalles e Invertir
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Inversiones activas -->
        <?php if (!empty($inversiones_activas)): ?>
        <div class="card">
            <div class="card-header">
                <h2 class="card-title">Mis Inversiones Activas</h2>
            </div>
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>Plan</th>
                            <th>Invertido</th>
                            <th>Ganancia Diaria</th>
                            <th>Total Ganado</th>
                            <th>Fecha Fin</th>
                            <th>Estado</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($inversiones_activas as $inv): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($inv['nombre']); ?></td>
                                <td><?php echo formatCurrency($inv['monto_invertido']); ?></td>
                                <td style="color: var(--secondary-color); font-weight: 600;">
                                    +<?php echo formatCurrency($inv['ganancia_diaria']); ?>
                                </td>
                                <td style="color: var(--secondary-color); font-weight: 600;">
                                    <?php echo formatCurrency($inv['ganancia_total_acumulada']); ?>
                                </td>
                                <td><?php echo date('d/m/Y', strtotime($inv['fecha_fin'])); ?></td>
                                <td><span class="badge badge-success">Activa</span></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <!-- Modales -->
    <?php include 'includes/modals.php'; ?>

    <!-- Barra de navegación inferior -->
    <nav class="bottom-nav" style="position: fixed; bottom: 0; left: 0; right: 0; background: white; border-top: 1px solid #e5e7eb; padding: 10px 0; display: flex; justify-content: space-around; z-index: 1000; box-shadow: 0 -2px 10px rgba(0,0,0,0.1); pointer-events: auto;">
        <a href="index.php" class="nav-item" style="display: flex; flex-direction: column; align-items: center; text-decoration: none; color: #667eea; padding: 5px 15px;">
            <i class="fas fa-home" style="font-size: 24px; margin-bottom: 5px;"></i>
            <span style="font-size: 12px; font-weight: 600;">Hogar</span>
            <div style="width: 100%; height: 3px; background: #667eea; margin-top: 5px; border-radius: 2px;"></div>
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
        .container {
            padding-bottom: 80px; /* Espacio para la barra inferior */
            position: relative;
            z-index: 1;
        }
        .nav-item:hover {
            color: #667eea !important;
        }
        
        /* Scroll optimizado para móviles */
        @media (max-width: 768px) {
            html {
                height: auto !important;
                min-height: 100% !important;
                overflow-x: hidden;
                overflow-y: auto !important;
                -webkit-overflow-scrolling: touch;
            }
            
            body {
                height: auto !important;
                min-height: 100vh !important;
                overflow-x: hidden;
                overflow-y: auto !important;
                -webkit-overflow-scrolling: touch;
                position: relative;
            }
            
            .container {
                padding-bottom: 80px;
                overflow: visible;
                min-height: auto;
                height: auto;
            }
            
            /* Asegurar que los elementos animados no bloqueen scroll */
            .aviso-animado-container,
            .aviso-texto-wrapper {
                pointer-events: auto;
                touch-action: pan-y;
            }
            
            .aviso-texto-scroll {
                pointer-events: none;
                touch-action: none;
            }
            
            .aviso-texto-wrapper::before,
            .aviso-texto-wrapper::after {
                pointer-events: none !important;
                touch-action: none !important;
            }
        }
        
        /* Responsive para Index */
        @media (max-width: 768px) {
            .dashboard-header {
                padding: 20px 15px;
            }
            
            .dashboard-title {
                font-size: 24px;
            }
            
            .balance-card {
                margin: 0 15px 20px 15px;
                padding: 20px;
            }
            
            .balance-amount {
                font-size: 32px;
            }
            
            .action-buttons {
                grid-template-columns: 1fr;
                gap: 12px;
                padding: 0 15px;
            }
            
            .action-btn {
                padding: 18px;
            }
            
            .investments-grid {
                grid-template-columns: repeat(2, 1fr) !important;
                grid-template-rows: repeat(5, auto);
                gap: 15px;
                padding: 0 15px;
                display: grid !important;
            }
        }
        
        @media (max-width: 480px) {
            .dashboard-header {
                padding: 15px 12px;
            }
            
            .dashboard-title {
                font-size: 20px;
            }
            
            .balance-card {
                margin: 0 12px 15px 12px;
                padding: 18px;
            }
            
            .balance-label {
                font-size: 13px;
            }
            
            .balance-amount {
                font-size: 28px;
            }
            
            .action-buttons {
                padding: 0 12px;
            }
            
            .action-btn {
                padding: 15px;
            }
            
            .action-btn-icon {
                font-size: 24px;
            }
            
            .action-btn-title {
                font-size: 15px;
            }
            
            .investments-grid {
                padding: 0 12px;
                gap: 10px !important;
            }
            
            .investment-card {
                padding: 10px !important;
                border-radius: 10px;
            }
            
            .investment-image {
                width: 100% !important;
                height: 100px !important;
                min-height: 100px !important;
                font-size: 24px !important;
                border-radius: 8px;
                margin-bottom: 6px;
                overflow: hidden;
            }
            
            .investment-image img {
                width: 100% !important;
                height: 100% !important;
                object-fit: cover !important;
            }
            
            .investment-content {
                padding: 0 !important;
            }
            
            .investment-name {
                font-size: 13px !important;
                margin-bottom: 5px;
                line-height: 1.2;
            }
            
            .investment-price {
                font-size: 16px !important;
                margin-bottom: 3px;
            }
            
            .investment-daily {
                font-size: 11px !important;
                margin-bottom: 5px;
            }
            
            .investment-limit {
                font-size: 10px !important;
                margin-bottom: 6px;
            }
            
            .investment-btn {
                padding: 7px 10px !important;
                font-size: 11px !important;
                border-radius: 6px;
                white-space: normal;
                line-height: 1.2;
            }
        }
    </style>

    <style>
        /* Aviso Animado - Diseño Premium Mejorado */
        .aviso-animado-container {
            width: 100%;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 50%, #f093fb 100%);
            border-radius: 16px;
            padding: 3px;
            box-shadow: 0 10px 40px rgba(102, 126, 234, 0.4), 
                        0 0 20px rgba(118, 75, 162, 0.3),
                        inset 0 1px 0 rgba(255, 255, 255, 0.2);
            margin: 25px 0;
            overflow: hidden;
            position: relative;
            z-index: 1;
            pointer-events: auto;
        }
        
        .aviso-animado-container::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, 
                transparent, 
                rgba(255, 255, 255, 0.3), 
                transparent);
            animation: shine 3s infinite;
        }
        
        @keyframes shine {
            0% { left: -100%; }
            100% { left: 100%; }
        }
        
        .aviso-animado-content {
            background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%);
            border-radius: 13px;
            padding: 16px 24px;
            display: flex;
            align-items: center;
            gap: 18px;
            overflow: hidden;
            position: relative;
            box-shadow: inset 0 2px 10px rgba(0, 0, 0, 0.3);
        }
        
        .aviso-icono-wrapper {
            flex-shrink: 0;
            width: 48px;
            height: 48px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.5),
                        inset 0 2px 5px rgba(255, 255, 255, 0.2);
            animation: icon-rotate 3s ease-in-out infinite;
        }
        
        @keyframes icon-rotate {
            0%, 100% { 
                transform: rotate(0deg) scale(1);
                box-shadow: 0 4px 15px rgba(102, 126, 234, 0.5),
                           inset 0 2px 5px rgba(255, 255, 255, 0.2);
            }
            50% { 
                transform: rotate(10deg) scale(1.05);
                box-shadow: 0 6px 20px rgba(102, 126, 234, 0.7),
                           inset 0 2px 5px rgba(255, 255, 255, 0.3);
            }
        }
        
        .aviso-icono {
            font-size: 26px;
            filter: drop-shadow(0 2px 4px rgba(0, 0, 0, 0.3));
        }
        
        .aviso-texto-wrapper {
            flex: 1;
            overflow: hidden;
            position: relative;
            height: 28px;
            z-index: 1;
            pointer-events: auto;
        }
        
        .aviso-texto-wrapper::before,
        .aviso-texto-wrapper::after {
            content: '';
            position: absolute;
            top: 0;
            width: 50px;
            height: 100%;
            z-index: 2;
            pointer-events: none;
            touch-action: none;
        }
        
        .aviso-texto-wrapper::before {
            left: 0;
            background: linear-gradient(to right, #1a1a2e, transparent);
        }
        
        .aviso-texto-wrapper::after {
            right: 0;
            background: linear-gradient(to left, #1a1a2e, transparent);
        }
        
        .aviso-texto-scroll {
            display: inline-flex;
            white-space: nowrap;
            animation: scroll-text 35s linear infinite;
            will-change: transform;
            align-items: center;
            height: 100%;
            pointer-events: none;
            touch-action: none;
        }
        
        .aviso-texto-scroll span {
            color: #fff;
            font-size: 17px;
            font-weight: 600;
            padding: 0 40px;
            display: inline-flex;
            align-items: center;
            text-shadow: 0 2px 8px rgba(0, 0, 0, 0.5),
                         0 0 20px rgba(102, 126, 234, 0.3);
            letter-spacing: 0.3px;
            white-space: nowrap;
        }
        
        @keyframes scroll-text {
            0% {
                transform: translateX(0);
            }
            100% {
                transform: translateX(-50%);
            }
        }
        
        @media (max-width: 768px) {
            .aviso-animado-container {
                margin: 20px 0;
                border-radius: 14px;
            }
            
            .aviso-animado-content {
                padding: 14px 18px;
                gap: 15px;
            }
            
            .aviso-icono-wrapper {
                width: 42px;
                height: 42px;
            }
            
            .aviso-icono {
                font-size: 22px;
            }
            
            .aviso-texto-wrapper {
                height: 24px;
            }
            
            .aviso-texto-scroll span {
                font-size: 15px;
                padding: 0 35px;
            }
        }
        
        @media (max-width: 480px) {
            .aviso-animado-container {
                margin: 15px 0;
                border-radius: 12px;
            }
            
            .aviso-animado-content {
                padding: 12px 15px;
                gap: 12px;
            }
            
            .aviso-icono-wrapper {
                width: 38px;
                height: 38px;
            }
            
            .aviso-icono {
                font-size: 20px;
            }
            
            .aviso-texto-wrapper {
                height: 22px;
            }
            
            .aviso-texto-scroll span {
                font-size: 13px;
                padding: 0 30px;
            }
        }
    </style>
    <script src="js/main.js"></script>
</body>
</html>

