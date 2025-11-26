<?php
require_once 'config/config.php';
requireLogin();

$conn = getConnection();
$user_id = $_SESSION['user_id'];

// Obtener información del usuario (incluyendo saldo actualizado)
$stmt = $conn->prepare("SELECT codigo_invitacion, telefono, saldo_disponible, saldo FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();

// Generar link de invitación
$link_invitacion = BASE_URL . 'register.php?ref=' . urlencode($user['codigo_invitacion']);

// Obtener estadísticas del equipo por nivel
$stats_nivel = [];
for ($nivel = 1; $nivel <= 3; $nivel++) {
    // Contar miembros por nivel
    $stmt = $conn->prepare("SELECT COUNT(*) as total_miembros 
                           FROM equipo 
                           WHERE usuario_id = ? AND nivel = ?");
    $stmt->bind_param("ii", $user_id, $nivel);
    $stmt->execute();
    $result = $stmt->get_result();
    $total_miembros = $result->fetch_assoc()['total_miembros'];
    $stmt->close();
    
    // Calcular contribución total (inversiones) por nivel
    $stmt = $conn->prepare("SELECT COALESCE(SUM(u.saldo_invertido), 0) as contribucion
                           FROM equipo e
                           JOIN users u ON e.referido_id = u.id
                           WHERE e.usuario_id = ? AND e.nivel = ?");
    $stmt->bind_param("ii", $user_id, $nivel);
    $stmt->execute();
    $result = $stmt->get_result();
    $contribucion = $result->fetch_assoc()['contribucion'];
    $stmt->close();
    
    // Tasa de comisión según nivel
    $tasa_comision = 0;
    switch($nivel) {
        case 1:
            $tasa_comision = 33;
            break;
        case 2:
            $tasa_comision = 1;
            break;
        case 3:
            $tasa_comision = 1;
            break;
    }
    
    $stats_nivel[$nivel] = [
        'miembros' => $total_miembros,
        'contribucion' => $contribucion,
        'tasa_comision' => $tasa_comision
    ];
}

// Obtener inversión total del equipo nivel 1
$stmt = $conn->prepare("SELECT COALESCE(SUM(u.saldo_invertido), 0) as inversion_total
                       FROM equipo e
                       JOIN users u ON e.referido_id = u.id
                       WHERE e.usuario_id = ? AND e.nivel = 1");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$inversion_total_nivel1 = $result->fetch_assoc()['inversion_total'];
$stmt->close();

// Obtener recompensas disponibles (si la tabla existe)
$recompensas = [];
try {
    // Verificar si la tabla existe
    $check_table = $conn->query("SHOW TABLES LIKE 'recompensas_equipo'");
    if ($check_table && $check_table->num_rows > 0) {
        $stmt = $conn->prepare("SELECT r.*, 
                               COALESCE(rec.fecha_recibido, NULL) as fecha_recibido,
                               CASE WHEN rec.id IS NOT NULL THEN 1 ELSE 0 END as recibida
                               FROM recompensas_equipo r
                               LEFT JOIN recompensas_recibidas rec ON r.id = rec.recompensa_id AND rec.usuario_id = ?
                               WHERE r.estado = 'activo'
                               ORDER BY r.nivel ASC");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $recompensas = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
    }
    if ($check_table) $check_table->close();
} catch (Exception $e) {
    // Si la tabla no existe, dejar recompensas vacío
    $recompensas = [];
}

closeConnection($conn);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mi Equipo - CashSpace</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            background: var(--light-color);
        }
        .share-card {
            background: var(--white);
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 15px;
            box-shadow: var(--shadow);
            border: 1px solid var(--gray-light);
            display: flex;
            align-items: center;
            gap: 15px;
        }
        .share-icon {
            width: 50px;
            height: 50px;
            background: var(--gradient-primary);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 24px;
            flex-shrink: 0;
            box-shadow: var(--shadow-md);
        }
        .share-content {
            flex: 1;
            min-width: 0;
        }
        .share-label {
            font-size: 14px;
            color: #6b7280;
            margin-bottom: 5px;
        }
        .share-value {
            font-weight: 600;
            color: #1f2937;
            word-break: break-all;
            font-family: monospace;
        }
        .copy-btn {
            background: var(--primary-color);
            color: white;
            border: none;
            padding: 8px 15px;
            border-radius: 8px;
            cursor: pointer;
            font-size: 14px;
            display: flex;
            align-items: center;
            gap: 5px;
            transition: background 0.3s;
        }
        .copy-btn:hover {
            background: var(--secondary-color);
        }
        .level-card {
            background: white;
            border-radius: 12px;
            padding: 0;
            margin-bottom: 20px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.08);
            border: 1px solid #e5e7eb;
            overflow: hidden;
            transition: transform 0.3s, box-shadow 0.3s;
        }
        .level-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 30px rgba(0,0,0,0.12);
        }
        .level-header {
            padding: 25px 25px 20px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            border-bottom: 2px solid #f3f4f6;
        }
        .level-header-left {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        .level-badge {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 18px;
            color: white;
        }
        .level-info h3 {
            margin: 0;
            font-size: 20px;
            font-weight: 700;
            color: #1f2937;
        }
        .level-info p {
            margin: 4px 0 0 0;
            font-size: 13px;
            color: #6b7280;
        }
        .level-icon {
            width: 40px;
            height: 40px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
        }
        .level-stats {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 0;
            padding: 0;
        }
        .stat-item {
            padding: 25px 20px;
            text-align: center;
            border-right: 1px solid #f3f4f6;
            position: relative;
        }
        .stat-item:last-child {
            border-right: none;
        }
        .stat-item::before {
            content: '';
            position: absolute;
            top: 0;
            left: 50%;
            transform: translateX(-50%);
            width: 40px;
            height: 3px;
            border-radius: 0 0 3px 3px;
        }
        .stat-label {
            font-size: 13px;
            color: #6b7280;
            margin-bottom: 8px;
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .stat-value {
            font-size: 24px;
            font-weight: 700;
            color: #1f2937;
            line-height: 1.2;
        }
        .level-1 .level-badge { background: linear-gradient(135deg, #4b5563 0%, #374151 100%); }
        .level-1 .level-icon { background: #f3f4f6; color: #4b5563; }
        .level-1 .stat-item::before { background: #4b5563; }
        
        .level-2 .level-badge { background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%); }
        .level-2 .level-icon { background: #dbeafe; color: #2563eb; }
        .level-2 .stat-item::before { background: #3b82f6; }
        
        .level-3 .level-badge { background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%); }
        .level-3 .level-icon { background: #fee2e2; color: #dc2626; }
        .level-3 .stat-item::before { background: #ef4444; }
        .reward-card {
            background: white;
            border-radius: 15px;
            padding: 20px;
            margin-bottom: 15px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            display: flex;
            gap: 15px;
            align-items: flex-start;
        }
        .reward-number {
            width: 40px;
            height: 40px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 700;
            font-size: 18px;
            flex-shrink: 0;
        }
        .reward-content {
            flex: 1;
        }
        .reward-description {
            margin-bottom: 10px;
            color: #1f2937;
        }
        .reward-progress {
            margin-bottom: 10px;
        }
        .progress-label {
            font-size: 14px;
            color: #6b7280;
            margin-bottom: 5px;
        }
        .progress-bar {
            background: #e5e7eb;
            border-radius: 10px;
            height: 8px;
            overflow: hidden;
            margin-bottom: 5px;
        }
        .progress-fill {
            background: linear-gradient(135deg, #f59e0b 0%, #ef4444 100%);
            height: 100%;
            transition: width 0.3s;
        }
        .progress-text {
            font-size: 14px;
            font-weight: 600;
            color: #f59e0b;
        }
        .reward-received {
            font-size: 14px;
            margin-bottom: 10px;
        }
        .reward-received strong {
            color: #059669;
        }
        .receive-btn {
            background: var(--primary-color);
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 8px;
            cursor: pointer;
            font-size: 14px;
            font-weight: 600;
            transition: background 0.3s;
        }
        .receive-btn:hover:not(:disabled) {
            background: var(--secondary-color);
        }
        .receive-btn:disabled {
            background: #9ca3af;
            cursor: not-allowed;
        }
        
        /* Responsive para Equipo */
        @media (max-width: 768px) {
            .share-card {
                flex-direction: column;
                align-items: flex-start;
                padding: 15px;
            }
            
            .share-icon {
                width: 45px;
                height: 45px;
                font-size: 20px;
            }
            
            .share-value {
                font-size: 13px;
            }
            
            .copy-btn {
                width: 100%;
                justify-content: center;
                margin-top: 10px;
            }
            
            .level-stats {
                grid-template-columns: 1fr;
            }
            
            .stat-item {
                border-right: none;
                border-bottom: 1px solid #f3f4f6;
                padding: 18px 15px;
            }
            
            .stat-item:last-child {
                border-bottom: none;
            }
            
            .level-header {
                padding: 20px 15px 15px;
            }
            
            .level-badge {
                width: 40px;
                height: 40px;
                font-size: 16px;
            }
            
            .level-info h3 {
                font-size: 18px;
            }
            
            .reward-card {
                flex-direction: column;
                padding: 15px;
            }
            
            .reward-number {
                width: 35px;
                height: 35px;
                font-size: 16px;
            }
        }
        
        @media (max-width: 480px) {
            .share-card {
                padding: 12px;
            }
            
            .share-label {
                font-size: 13px;
            }
            
            .share-value {
                font-size: 12px;
                word-break: break-all;
            }
            
            .copy-btn {
                padding: 8px 12px;
                font-size: 13px;
            }
            
            .level-header {
                padding: 15px 12px 12px;
                flex-wrap: wrap;
            }
            
            .level-header-left {
                width: 100%;
                margin-bottom: 10px;
            }
            
            .level-info h3 {
                font-size: 16px;
            }
            
            .level-info p {
                font-size: 12px;
            }
            
            .stat-item {
                padding: 15px 12px;
            }
            
            .stat-label {
                font-size: 12px;
            }
            
            .stat-value {
                font-size: 20px;
            }
            
            .reward-card {
                padding: 12px;
            }
            
            .reward-description {
                font-size: 14px;
            }
            
            .progress-label,
            .progress-text,
            .reward-received {
                font-size: 13px;
            }
            
            .receive-btn {
                width: 100%;
                padding: 12px;
                font-size: 14px;
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
                <div class="user-info">
                    <div class="user-avatar">
                        <i class="fas fa-user"></i>
                    </div>
                    <div>
                        <div style="font-weight: 600;"><?php echo htmlspecialchars($_SESSION['user_telefono']); ?></div>
                        <div class="user-balance"><?php echo formatCurrency($user['saldo_disponible'] ?? 0); ?></div>
                    </div>
                </div>
                <?php if (isAdmin()): ?>
                    <a href="admin/index.php" class="btn btn-outline">Admin</a>
                <?php endif; ?>
                <a href="index.php" class="btn btn-outline">Inicio</a>
                <a href="logout.php" class="btn btn-danger">Salir</a>
            </div>
        </div>
    </nav>

    <!-- Contenedor principal -->
    <div class="container">
        <h1 style="margin-bottom: 30px; color: var(--blue-primary); font-size: 32px; font-weight: 700;">Mi Equipo</h1>

        <!-- Método de compartir -->
        <div class="card" style="margin-bottom: 30px;">
            <div class="card-header">
                <h2 class="card-title">Método de compartir</h2>
            </div>
            <div style="padding: 20px;">
                <!-- Código de invitación -->
                <div class="share-card">
                    <div class="share-icon">
                        <i class="fas fa-gift"></i>
                    </div>
                    <div class="share-content">
                        <div class="share-label">Compartir el código de invitación</div>
                        <div class="share-value" id="codigo_invitacion"><?php echo htmlspecialchars($user['codigo_invitacion']); ?></div>
                    </div>
                    <button class="copy-btn" onclick="copiarCodigo()">
                        <i class="fas fa-copy"></i> Copiar
                    </button>
                </div>

                <!-- Link de invitación -->
                <div class="share-card">
                    <div class="share-icon">
                        <i class="fas fa-link"></i>
                    </div>
                    <div class="share-content">
                        <div class="share-label">Compartir el enlace de invitación</div>
                        <div class="share-value" id="link_invitacion"><?php echo htmlspecialchars($link_invitacion); ?></div>
                    </div>
                    <button class="copy-btn" onclick="copiarLink()">
                        <i class="fas fa-copy"></i> Copiar
                    </button>
                </div>
            </div>
        </div>

        <!-- Beneficios de mi equipo -->
        <div class="card" style="margin-bottom: 30px;">
            <div class="card-header">
                <h2 class="card-title">Beneficios de mi equipo</h2>
            </div>
            <div style="padding: 20px;">
                <p style="color: #6b7280; margin-bottom: 20px; font-size: 14px;">
                    Haz clic en el interruptor para ver los detalles de los registros correspondientes al equipo
                </p>
                
                <!-- Nivel 1 -->
                <div class="level-card level-1">
                    <div class="level-header">
                        <div class="level-header-left">
                            <div class="level-badge">1</div>
                            <div class="level-info">
                                <h3>Nivel 1 - Directo</h3>
                                <p>Referidos directos</p>
                            </div>
                        </div>
                        <div class="level-icon">
                            <i class="fas fa-users"></i>
                        </div>
                    </div>
                    <div class="level-stats">
                        <div class="stat-item">
                            <div class="stat-label">Tasa de comisión</div>
                            <div class="stat-value"><?php echo $stats_nivel[1]['tasa_comision']; ?>%</div>
                        </div>
                        <div class="stat-item">
                            <div class="stat-label">Miembros</div>
                            <div class="stat-value"><?php echo number_format($stats_nivel[1]['miembros']); ?></div>
                        </div>
                        <div class="stat-item">
                            <div class="stat-label">Contribución</div>
                            <div class="stat-value"><?php echo formatCurrency($stats_nivel[1]['contribucion']); ?></div>
                        </div>
                    </div>
                </div>

                <!-- Nivel 2 -->
                <div class="level-card level-2">
                    <div class="level-header">
                        <div class="level-header-left">
                            <div class="level-badge">2</div>
                            <div class="level-info">
                                <h3>Nivel 2 - Secundario</h3>
                                <p>Referidos de nivel 1</p>
                            </div>
                        </div>
                        <div class="level-icon">
                            <i class="fas fa-user-friends"></i>
                        </div>
                    </div>
                    <div class="level-stats">
                        <div class="stat-item">
                            <div class="stat-label">Tasa de comisión</div>
                            <div class="stat-value"><?php echo $stats_nivel[2]['tasa_comision']; ?>%</div>
                        </div>
                        <div class="stat-item">
                            <div class="stat-label">Miembros</div>
                            <div class="stat-value"><?php echo number_format($stats_nivel[2]['miembros']); ?></div>
                        </div>
                        <div class="stat-item">
                            <div class="stat-label">Contribución</div>
                            <div class="stat-value"><?php echo formatCurrency($stats_nivel[2]['contribucion']); ?></div>
                        </div>
                    </div>
                </div>

                <!-- Nivel 3 -->
                <div class="level-card level-3">
                    <div class="level-header">
                        <div class="level-header-left">
                            <div class="level-badge">3</div>
                            <div class="level-info">
                                <h3>Nivel 3 - Terciario</h3>
                                <p>Referidos de nivel 2</p>
                            </div>
                        </div>
                        <div class="level-icon">
                            <i class="fas fa-user-network"></i>
                        </div>
                    </div>
                    <div class="level-stats">
                        <div class="stat-item">
                            <div class="stat-label">Tasa de comisión</div>
                            <div class="stat-value"><?php echo $stats_nivel[3]['tasa_comision']; ?>%</div>
                        </div>
                        <div class="stat-item">
                            <div class="stat-label">Miembros</div>
                            <div class="stat-value"><?php echo number_format($stats_nivel[3]['miembros']); ?></div>
                        </div>
                        <div class="stat-item">
                            <div class="stat-label">Contribución</div>
                            <div class="stat-value"><?php echo formatCurrency($stats_nivel[3]['contribucion']); ?></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recompensas de inversión del equipo -->
        <div class="card">
            <div class="card-header">
                <h2 class="card-title">Recompensas de inversión del equipo</h2>
            </div>
            <div style="padding: 20px;">
                <?php foreach ($recompensas as $recompensa): ?>
                    <?php
                    $inversion_actual = $inversion_total_nivel1;
                    $meta = $recompensa['monto_inversion_requerido'];
                    $porcentaje = $meta > 0 ? min(100, ($inversion_actual / $meta) * 100) : 0;
                    $puede_recibir = $inversion_actual >= $meta && !$recompensa['recibida'];
                    ?>
                    <div class="reward-card">
                        <div class="reward-number"><?php echo $recompensa['nivel']; ?></div>
                        <div class="reward-content">
                            <div class="reward-description">
                                <?php echo htmlspecialchars($recompensa['descripcion']); ?>, Y se puede recibir <strong style="color: #f59e0b;"><?php echo formatCurrency($recompensa['monto_recompensa']); ?></strong>
                            </div>
                            <div class="reward-progress">
                                <div class="progress-label">Cantidad de inversión:</div>
                                <div class="progress-bar">
                                    <div class="progress-fill" style="width: <?php echo $porcentaje; ?>%"></div>
                                </div>
                                <div class="progress-text">
                                    <?php echo formatCurrency($inversion_actual); ?> / <?php echo formatCurrency($meta); ?>
                                </div>
                            </div>
                            <div class="reward-received">
                                Recibido: <strong><?php echo $recompensa['recibida'] ? formatCurrency($recompensa['monto_recompensa']) : 'Bs 0,00'; ?></strong>
                            </div>
                            <button class="receive-btn" 
                                    onclick="recibirRecompensa(<?php echo $recompensa['id']; ?>)" 
                                    <?php echo $puede_recibir ? '' : 'disabled'; ?>>
                                Recibir
                            </button>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

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
        <a href="equipo.php" class="nav-item" style="display: flex; flex-direction: column; align-items: center; text-decoration: none; color: #667eea; padding: 5px 15px;">
            <i class="fas fa-layer-group" style="font-size: 24px; margin-bottom: 5px;"></i>
            <span style="font-size: 12px; font-weight: 600;">Equipo</span>
            <div style="width: 100%; height: 3px; background: #667eea; margin-top: 5px; border-radius: 2px;"></div>
        </a>
        <a href="mio.php" class="nav-item" style="display: flex; flex-direction: column; align-items: center; text-decoration: none; color: #9ca3af; padding: 5px 15px;">
            <i class="fas fa-user" style="font-size: 24px; margin-bottom: 5px;"></i>
            <span style="font-size: 12px;">Mío</span>
        </a>
    </nav>

    <style>
        .container {
            padding-bottom: 80px; /* Espacio para la barra inferior */
        }
        .nav-item:hover {
            color: #667eea !important;
        }
    </style>

    <script src="js/main.js"></script>
    <script>
        function copiarCodigo() {
            const codigo = document.getElementById('codigo_invitacion').textContent;
            navigator.clipboard.writeText(codigo).then(() => {
                alert('Código copiado al portapapeles');
            }).catch(err => {
                // Fallback para navegadores que no soportan clipboard API
                const textarea = document.createElement('textarea');
                textarea.value = codigo;
                document.body.appendChild(textarea);
                textarea.select();
                document.execCommand('copy');
                document.body.removeChild(textarea);
                alert('Código copiado al portapapeles');
            });
        }

        function copiarLink() {
            const link = document.getElementById('link_invitacion').textContent;
            navigator.clipboard.writeText(link).then(() => {
                alert('Enlace copiado al portapapeles');
            }).catch(err => {
                // Fallback
                const textarea = document.createElement('textarea');
                textarea.value = link;
                document.body.appendChild(textarea);
                textarea.select();
                document.execCommand('copy');
                document.body.removeChild(textarea);
                alert('Enlace copiado al portapapeles');
            });
        }

        async function recibirRecompensa(recompensaId) {
            if (!confirm('¿Estás seguro de recibir esta recompensa?')) {
                return;
            }

            try {
                const response = await fetch('api/recibir_recompensa.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        recompensa_id: recompensaId
                    })
                });

                const data = await response.json();

                if (data.success) {
                    alert(data.message);
                    location.reload();
                } else {
                    alert('Error: ' + data.message);
                }
            } catch (error) {
                alert('Error al procesar la solicitud');
            }
        }
    </script>
</body>
</html>

