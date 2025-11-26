<?php
require_once 'config/config.php';
requireLogin();

$conn = getConnection();
$user_id = $_SESSION['user_id'];

// Obtener información completa del usuario
$stmt = $conn->prepare("SELECT id, telefono, codigo_invitacion, saldo_disponible, saldo_invertido, fecha_registro FROM users WHERE id = ?");
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

// Contar inversiones activas
$stmt = $conn->prepare("SELECT COUNT(*) as total FROM inversiones WHERE usuario_id = ? AND estado = 'activa'");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$inversiones_activas = $result->fetch_assoc()['total'];
$stmt->close();

closeConnection($conn);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mío - CashSpace</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            background: var(--light-color);
        }
        .profile-header {
            background: var(--gradient-primary);
            padding: 30px 20px;
            color: white;
            position: relative;
            overflow: hidden;
            box-shadow: var(--shadow-md);
        }
        .profile-header::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%);
        }
        .logo-section {
            display: flex;
            align-items: center;
            gap: 15px;
            margin-bottom: 20px;
            position: relative;
            z-index: 1;
        }
        .logo-circle {
            width: 60px;
            height: 60px;
            background: rgba(255,255,255,0.25);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            padding: 8px;
            border: 2px solid rgba(212, 175, 55, 0.4);
            box-shadow: 0 4px 12px rgba(212, 175, 55, 0.3);
        }
        
        .logo-circle img {
            width: 100%;
            height: 100%;
            object-fit: contain;
            filter: brightness(1.3) drop-shadow(0 2px 8px rgba(212, 175, 55, 0.5));
        }
        .logo-text {
            flex: 1;
        }
        .logo-title {
            font-size: 24px;
            font-weight: 700;
            margin-bottom: 5px;
        }
        .user-id {
            font-size: 14px;
            opacity: 0.9;
        }
        .invitation-code {
            background: rgba(255,255,255,0.2);
            border-radius: 12px;
            padding: 15px;
            display: flex;
            border: 1px solid rgba(255, 255, 255, 0.3);
            box-shadow: var(--shadow-md);
            justify-content: space-between;
            align-items: center;
            margin-top: 15px;
            position: relative;
            z-index: 1;
        }
        .invitation-code-text {
            font-size: 14px;
        }
        .invitation-code-value {
            font-weight: 700;
            font-size: 16px;
            margin-top: 5px;
            font-family: monospace;
        }
        .copy-icon {
            background: rgba(255,255,255,0.2);
            width: 35px;
            height: 35px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: background 0.3s;
        }
        .copy-icon:hover {
            background: rgba(255,255,255,0.3);
        }
        .history-link {
            background: rgba(255,255,255,0.2);
            border-radius: 10px;
            padding: 10px 18px;
            color: white;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            font-size: 14px;
            font-weight: 600;
            transition: all 0.2s;
            position: relative;
            z-index: 1;
            border: 1px solid rgba(255,255,255,0.3);
        }
        .history-link:hover {
            background: rgba(255,255,255,0.3);
            transform: translateY(-2px);
        }
        .history-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: relative;
            z-index: 1;
            flex-wrap: wrap;
            gap: 10px;
        }
        .balance-card {
            background: linear-gradient(135deg, #1e3a8a 0%, #1e40af 100%);
            border-radius: 20px;
            padding: 25px;
            margin: -30px 20px 20px 20px;
            color: white;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
            position: relative;
            z-index: 2;
        }
        .balance-stats {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 20px;
            margin-top: 15px;
        }
        .balance-stat {
            text-align: center;
        }
        .balance-stat-value {
            font-size: 28px;
            font-weight: 700;
            margin-bottom: 5px;
        }
        .balance-stat-label {
            font-size: 14px;
            opacity: 0.9;
        }
        .action-buttons {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 15px;
            padding: 0 20px;
            margin-bottom: 30px;
        }
        .action-button {
            background: var(--white);
            border-radius: 12px;
            padding: 20px;
            text-align: center;
            text-decoration: none;
            color: var(--dark-color);
            box-shadow: var(--shadow);
            border: 1px solid var(--gray-light);
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }
        .action-button:hover {
            transform: translateY(-4px);
            box-shadow: var(--shadow-lg);
            border-color: var(--blue-primary);
        }
        .action-button.red {
            border-left: 4px solid #ef4444;
        }
        .action-button.blue {
            border-left: 4px solid var(--blue-primary);
        }
        .action-button-icon {
            width: 50px;
            height: 50px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 15px;
            font-size: 24px;
        }
        .action-button.red .action-button-icon {
            background: #fee2e2;
            color: #ef4444;
        }
        .action-button.blue .action-button-icon {
            background: #dbeafe;
            color: var(--blue-primary);
        }
        .action-button-title {
            font-size: 18px;
            font-weight: 700;
            margin-bottom: 5px;
        }
        .action-button-subtitle {
            font-size: 12px;
            color: #6b7280;
        }
        .functions-section {
            padding: 0 20px;
            margin-bottom: 30px;
        }
        .functions-title {
            font-size: 18px;
            font-weight: 700;
            color: #1f2937;
            margin-bottom: 15px;
        }
        .functions-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 15px;
        }
        .function-card {
            background: var(--white);
            border-radius: 12px;
            padding: 20px;
            text-decoration: none;
            color: var(--dark-color);
            box-shadow: var(--shadow);
            border: 1px solid var(--gray-light);
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }
        .function-card:hover {
            transform: translateY(-4px);
            box-shadow: var(--shadow-lg);
            border-color: var(--blue-primary);
        }
        .function-icon {
            width: 45px;
            height: 45px;
            background: #f3f4f6;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 15px;
            font-size: 20px;
            color: #667eea;
        }
        .function-title {
            font-size: 16px;
            font-weight: 600;
            margin-bottom: 5px;
        }
        .function-subtitle {
            font-size: 12px;
            color: #6b7280;
        }
        .footer {
            text-align: center;
            padding: 30px 20px;
        }
        .footer-logo {
            font-size: 24px;
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .footer-logo img {
            width: 40px;
            height: 40px;
            object-fit: contain;
        }
        .footer-text {
            font-size: 12px;
            color: #9ca3af;
        }
    </style>
</head>
<body>
    <!-- Header del perfil -->
    <div class="profile-header">
        <div class="history-header">
            <div class="logo-section">
                <div class="logo-circle">
                    <img src="assets/images/logo.png" alt="CashSpace" style="width: 100%; height: 100%; object-fit: contain; filter: brightness(1.3) drop-shadow(0 2px 8px rgba(212, 175, 55, 0.5));" onerror="this.style.display='none'; this.parentElement.innerHTML='💎';">
                </div>
                <div class="logo-text">
                    <div class="logo-title">CashSpace</div>
                    <div class="user-id">ID: <?php echo str_pad($user['id'], 8, '0', STR_PAD_LEFT); ?></div>
                </div>
            </div>
            <a href="historial.php" class="history-link">
                <i class="fas fa-file-invoice"></i> Historial
            </a>
        </div>
        <div style="display: flex; gap: 10px; align-items: center; margin-top: 15px; position: relative; z-index: 1;">
            <div class="invitation-code">
                <div>
                    <div class="invitation-code-text">Mi código de invitación:</div>
                    <div class="invitation-code-value" id="invitation_code"><?php echo htmlspecialchars($user['codigo_invitacion']); ?></div>
                </div>
                <div class="copy-icon" onclick="copiarCodigo()">
                    <i class="fas fa-copy"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Tarjeta de balance -->
    <div class="balance-card">
        <div class="balance-stats">
            <div class="balance-stat">
                <div class="balance-stat-value"><?php echo formatCurrency($ganancias_hoy); ?></div>
                <div class="balance-stat-label">Ganancias de hoy</div>
            </div>
            <div class="balance-stat">
                <div class="balance-stat-value"><?php echo formatCurrency($user['saldo_disponible']); ?></div>
                <div class="balance-stat-label">Mi saldo</div>
            </div>
        </div>
    </div>

    <!-- Botones de acción -->
    <div class="action-buttons">
        <a href="#" onclick="openModal('recargaModal'); return false;" class="action-button red">
            <div class="action-button-icon">
                <i class="fas fa-arrow-down"></i>
            </div>
            <div class="action-button-title">Recargar</div>
            <div class="action-button-subtitle">Recarga mínima Bs 100</div>
        </a>
        <a href="#" onclick="openModal('retiroModal'); return false;" class="action-button blue">
            <div class="action-button-icon">
                <i class="fas fa-arrow-up"></i>
            </div>
            <div class="action-button-title">Retirar</div>
            <div class="action-button-subtitle">Retiro mínimo Bs 150</div>
        </a>
    </div>

    <!-- Mis funciones -->
    <div class="functions-section">
        <h2 class="functions-title">Mis Funciones</h2>
        <div class="functions-grid">
            <a href="cuenta_bancaria.php" class="function-card">
                <div class="function-icon">
                    <i class="fas fa-credit-card"></i>
                </div>
                <div class="function-title">Cuenta bancaria</div>
                <div class="function-subtitle">Vincular el banco</div>
            </a>
            <a href="cambiar_contraseña.php" class="function-card">
                <div class="function-icon">
                    <i class="fas fa-lock"></i>
                </div>
                <div class="function-title">Contraseña</div>
                <div class="function-subtitle">Seguridad de fondos</div>
            </a>
            <a href="acerca_de.php" class="function-card">
                <div class="function-icon">
                    <i class="fas fa-info-circle"></i>
                </div>
                <div class="function-title">Acerca de</div>
                <div class="function-subtitle">Historia de la plataforma</div>
            </a>
            <a href="asistencia.php" class="function-card">
                <div class="function-icon">
                    <i class="fas fa-comments"></i>
                </div>
                <div class="function-title">Asistencia</div>
                <div class="function-subtitle">Centro de ayuda</div>
            </a>
            <a href="logout.php" class="function-card">
                <div class="function-icon">
                    <i class="fas fa-power-off"></i>
                </div>
                <div class="function-title">Cerrar sesión</div>
                <div class="function-subtitle">Cambiar cuenta</div>
            </a>
            <a href="limpiar_cache.php" class="function-card">
                <div class="function-icon" style="background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);">
                    <i class="fas fa-broom"></i>
                </div>
                <div class="function-info">
                    <div class="function-title">Limpiar Cache</div>
                    <div class="function-desc">Soluciona problemas de rendimiento</div>
                </div>
            </a>
            <a href="#" onclick="mostrarProximamente(); return false;" class="function-card" style="opacity: 0.6; cursor: not-allowed; pointer-events: auto;">
                <div class="function-icon" style="background: #e5e7eb; color: #9ca3af;">
                    <i class="fas fa-download"></i>
                </div>
                <div class="function-title">Descargar</div>
                <div class="function-subtitle" style="color: #f59e0b; font-weight: 600;">Próximamente</div>
            </a>
        </div>
    </div>

    <!-- Footer -->
    <div class="footer">
        <div class="footer-logo">
            <img src="assets/images/logo.png" alt="CashSpace" style="width: 40px; height: 40px; object-fit: contain;" onerror="this.style.display='none'; this.parentElement.innerHTML='💎';">
        </div>
        <div class="footer-text">© CashSpace LLC. Todos los derechos reservados.</div>
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
        <a href="equipo.php" class="nav-item" style="display: flex; flex-direction: column; align-items: center; text-decoration: none; color: #9ca3af; padding: 5px 15px;">
            <i class="fas fa-layer-group" style="font-size: 24px; margin-bottom: 5px;"></i>
            <span style="font-size: 12px;">Equipo</span>
        </a>
        <a href="mio.php" class="nav-item" style="display: flex; flex-direction: column; align-items: center; text-decoration: none; color: #667eea; padding: 5px 15px;">
            <i class="fas fa-user" style="font-size: 24px; margin-bottom: 5px;"></i>
            <span style="font-size: 12px; font-weight: 600;">Mío</span>
            <div style="width: 100%; height: 3px; background: #667eea; margin-top: 5px; border-radius: 2px;"></div>
        </a>
    </nav>

    <style>
        body {
            padding-bottom: 80px;
        }
        .nav-item:hover {
            color: #667eea !important;
        }
        
        /* Responsive para Mío */
        @media (max-width: 768px) {
            .profile-header {
                padding: 15px 12px;
            }
            
            .history-header {
                flex-direction: row;
                gap: 10px;
                align-items: center;
                justify-content: space-between;
            }
            
            .logo-section {
                flex: 1;
                gap: 10px;
            }
            
            .logo-circle {
                width: 45px;
                height: 45px;
                padding: 6px;
            }
            
            .logo-title {
                font-size: 18px;
            }
            
            .user-id {
                font-size: 11px;
            }
            
            .history-link {
                padding: 8px 12px;
                font-size: 12px;
                white-space: nowrap;
            }
            
            .invitation-code {
                margin-top: 12px;
                padding: 10px 12px;
                gap: 10px;
            }
            
            .invitation-code-text {
                font-size: 11px;
            }
            
            .invitation-code-value {
                font-size: 13px;
            }
            
            .copy-icon {
                width: 28px;
                height: 28px;
                font-size: 12px;
            }
            
            .balance-card {
                margin: -15px 12px 15px 12px;
                padding: 15px;
                border-radius: 16px;
            }
            
            .balance-stats {
                grid-template-columns: repeat(2, 1fr);
                gap: 12px;
            }
            
            .balance-stat-value {
                font-size: 20px;
            }
            
            .balance-stat-label {
                font-size: 11px;
            }
            
            .action-buttons {
                grid-template-columns: repeat(2, 1fr) !important;
                gap: 10px !important;
                padding: 0 12px;
            }
            
            .action-button {
                padding: 12px 10px !important;
                display: flex !important;
                flex-direction: column !important;
                align-items: center !important;
                text-align: center !important;
                gap: 8px !important;
            }
            
            .action-button-icon {
                width: 40px !important;
                height: 40px !important;
                margin: 0 !important;
                flex-shrink: 0 !important;
                font-size: 18px !important;
            }
            
            .action-button-title {
                font-size: 13px !important;
                margin-bottom: 2px !important;
            }
            
            .action-button-subtitle {
                font-size: 10px !important;
            }
            
            .functions-grid {
                grid-template-columns: repeat(2, 1fr);
                gap: 10px;
                padding: 0 12px;
            }
            
            .function-card {
                padding: 15px 12px;
            }
            
            .function-icon {
                width: 40px;
                height: 40px;
                font-size: 18px;
                margin-bottom: 10px;
            }
            
            .function-title {
                font-size: 13px;
            }
            
            .function-subtitle {
                font-size: 11px;
            }
        }
        
        @media (max-width: 480px) {
            .profile-header {
                padding: 12px 10px;
            }
            
            .logo-section {
                gap: 8px;
            }
            
            .logo-circle {
                width: 40px;
                height: 40px;
                padding: 5px;
            }
            
            .logo-title {
                font-size: 16px;
            }
            
            .user-id {
                font-size: 10px;
            }
            
            .history-link {
                padding: 6px 10px;
                font-size: 11px;
            }
            
            .invitation-code {
                padding: 8px 10px;
                gap: 8px;
            }
            
            .invitation-code-text {
                font-size: 10px;
            }
            
            .invitation-code-value {
                font-size: 12px;
            }
            
            .copy-icon {
                width: 26px;
                height: 26px;
                font-size: 11px;
            }
            
            .balance-card {
                margin: -12px 10px 12px 10px;
                padding: 12px;
                border-radius: 14px;
            }
            
            .balance-stat-value {
                font-size: 18px;
            }
            
            .balance-stat-label {
                font-size: 10px;
            }
            
            .action-buttons {
                padding: 0 10px;
                grid-template-columns: repeat(2, 1fr) !important;
                gap: 8px !important;
            }
            
            .action-button {
                padding: 10px 8px !important;
                gap: 6px !important;
            }
            
            .action-button-icon {
                width: 35px !important;
                height: 35px !important;
                font-size: 16px !important;
            }
            
            .action-button-title {
                font-size: 12px !important;
            }
            
            .action-button-subtitle {
                font-size: 9px !important;
            }
            
            .functions-grid {
                grid-template-columns: repeat(2, 1fr);
                gap: 8px;
                padding: 0 10px;
            }
            
            .function-card {
                padding: 12px 10px;
            }
            
            .function-icon {
                width: 35px;
                height: 35px;
                font-size: 16px;
                margin-bottom: 8px;
            }
            
            .function-title {
                font-size: 12px;
            }
            
            .function-subtitle {
                font-size: 10px;
            }
        }
    </style>

    <!-- Modales -->
    <?php include 'includes/modals.php'; ?>

    <script src="js/main.js"></script>
    <script>
        function copiarCodigo() {
            const codigo = document.getElementById('invitation_code').textContent;
            navigator.clipboard.writeText(codigo).then(() => {
                mostrarNotificacion('Código copiado al portapapeles', 'success');
            }).catch(err => {
                const textarea = document.createElement('textarea');
                textarea.value = codigo;
                document.body.appendChild(textarea);
                textarea.select();
                document.execCommand('copy');
                document.body.removeChild(textarea);
                mostrarNotificacion('Código copiado al portapapeles', 'success');
            });
        }
        
        function mostrarProximamente() {
            mostrarNotificacion('Esta función estará disponible próximamente', 'info');
        }
        
        function mostrarNotificacion(mensaje, tipo) {
            // Remover notificaciones anteriores
            const notificacionesAnteriores = document.querySelectorAll('.notificacion-toast');
            notificacionesAnteriores.forEach(n => n.remove());
            
            // Crear notificación
            const notificacion = document.createElement('div');
            notificacion.className = `notificacion-toast notificacion-${tipo}`;
            
            // Iconos según el tipo
            let icono = '';
            let colorFondo = '';
            let colorTexto = '';
            
            switch(tipo) {
                case 'success':
                    icono = '<i class="fas fa-check-circle"></i>';
                    colorFondo = '#10b981';
                    colorTexto = '#ffffff';
                    break;
                case 'error':
                    icono = '<i class="fas fa-exclamation-circle"></i>';
                    colorFondo = '#ef4444';
                    colorTexto = '#ffffff';
                    break;
                case 'info':
                    icono = '<i class="fas fa-info-circle"></i>';
                    colorFondo = '#3b82f6';
                    colorTexto = '#ffffff';
                    break;
                case 'warning':
                    icono = '<i class="fas fa-exclamation-triangle"></i>';
                    colorFondo = '#f59e0b';
                    colorTexto = '#ffffff';
                    break;
                default:
                    icono = '<i class="fas fa-bell"></i>';
                    colorFondo = '#6b7280';
                    colorTexto = '#ffffff';
            }
            
            notificacion.innerHTML = `
                <div style="display: flex; align-items: center; gap: 12px;">
                    <div style="font-size: 24px; color: ${colorTexto};">
                        ${icono}
                    </div>
                    <div style="flex: 1; color: ${colorTexto}; font-size: 15px; font-weight: 500;">
                        ${mensaje}
                    </div>
                </div>
            `;
            
            notificacion.style.cssText = `
                position: fixed;
                top: 20px;
                right: 20px;
                background: ${colorFondo};
                color: ${colorTexto};
                padding: 16px 20px;
                border-radius: 12px;
                box-shadow: 0 10px 25px rgba(0,0,0,0.2);
                z-index: 10000;
                min-width: 300px;
                max-width: 90%;
                animation: slideInRight 0.3s ease-out;
                font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            `;
            
            // Agregar animación CSS si no existe
            if (!document.getElementById('notificacion-styles')) {
                const style = document.createElement('style');
                style.id = 'notificacion-styles';
                style.textContent = `
                    @keyframes slideInRight {
                        from {
                            transform: translateX(100%);
                            opacity: 0;
                        }
                        to {
                            transform: translateX(0);
                            opacity: 1;
                        }
                    }
                    @keyframes slideOutRight {
                        from {
                            transform: translateX(0);
                            opacity: 1;
                        }
                        to {
                            transform: translateX(100%);
                            opacity: 0;
                        }
                    }
                    .notificacion-toast {
                        transition: all 0.3s ease;
                    }
                `;
                document.head.appendChild(style);
            }
            
            document.body.appendChild(notificacion);
            
            // Auto-remover después de 4 segundos
            setTimeout(() => {
                notificacion.style.animation = 'slideOutRight 0.3s ease-out';
                setTimeout(() => {
                    if (notificacion.parentNode) {
                        notificacion.remove();
                    }
                }, 300);
            }, 4000);
            
            // Permitir cerrar haciendo clic
            notificacion.addEventListener('click', () => {
                notificacion.style.animation = 'slideOutRight 0.3s ease-out';
                setTimeout(() => {
                    if (notificacion.parentNode) {
                        notificacion.remove();
                    }
                }, 300);
            });
        }
    </script>
</body>
</html>

