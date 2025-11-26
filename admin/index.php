<?php
require_once __DIR__ . '/../config/config.php';
requireAdmin();

// Obtener estadísticas
$conn = getConnection();

// Total de usuarios
$result = $conn->query("SELECT COUNT(*) as total FROM users WHERE tipo_usuario = 'user'");
$total_usuarios = $result->fetch_assoc()['total'];

// Total de inversiones activas
$result = $conn->query("SELECT COUNT(*) as total FROM inversiones WHERE estado = 'activa'");
$total_inversiones_activas = $result->fetch_assoc()['total'];

// Total de recargas pendientes
$result = $conn->query("SELECT COUNT(*) as total FROM recargas WHERE estado = 'pendiente'");
$total_recargas_pendientes = $result->fetch_assoc()['total'];

// Total de retiros pendientes
$result = $conn->query("SELECT COUNT(*) as total FROM retiros WHERE estado = 'pendiente'");
$total_retiros_pendientes = $result->fetch_assoc()['total'];

// Monto total de recargas pendientes
$result = $conn->query("SELECT COALESCE(SUM(monto), 0) as total FROM recargas WHERE estado = 'pendiente'");
$monto_recargas_pendientes = $result->fetch_assoc()['total'];

// Monto total de retiros pendientes
$result = $conn->query("SELECT COALESCE(SUM(monto), 0) as total FROM retiros WHERE estado = 'pendiente'");
$monto_retiros_pendientes = $result->fetch_assoc()['total'];

// Total invertido
$result = $conn->query("SELECT COALESCE(SUM(saldo_invertido), 0) as total FROM users");
$total_invertido = $result->fetch_assoc()['total'];

// Recargas pendientes
$result = $conn->query("SELECT r.*, u.telefono FROM recargas r JOIN users u ON r.usuario_id = u.id WHERE r.estado = 'pendiente' ORDER BY r.fecha_solicitud DESC LIMIT 10");
$recargas_pendientes = $result->fetch_all(MYSQLI_ASSOC);

// Retiros pendientes
$result = $conn->query("SELECT r.*, u.telefono, cb.nombre_titular 
                        FROM retiros r 
                        JOIN users u ON r.usuario_id = u.id 
                        LEFT JOIN cuenta_bancaria cb ON u.id = cb.usuario_id 
                        WHERE r.estado = 'pendiente' 
                        ORDER BY r.fecha_solicitud DESC 
                        LIMIT 10");
$retiros_pendientes = $result->fetch_all(MYSQLI_ASSOC);

closeConnection($conn);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel de Administración - CashSpace</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <!-- Navbar Admin -->
    <nav class="navbar">
        <div class="navbar-content">
            <a href="index.php" class="navbar-brand">
                <img src="../assets/images/logo.png" alt="CashSpace" onerror="this.style.display='none'; this.nextElementSibling.style.display='block';">
                <span style="display:none; background: var(--gradient-primary); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text;">CashSpace - Admin</span>
            </a>
            <div class="navbar-user">
                <a href="../index.php" class="btn btn-outline">Ir al Inicio</a>
                <a href="../logout.php" class="btn btn-danger">Salir</a>
            </div>
        </div>
    </nav>

    <!-- Contenedor principal -->
    <div class="container">
        <h1 style="margin-bottom: 30px; color: var(--dark-color);">Panel de Administración</h1>

        <!-- Estadísticas -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-value" style="color: var(--primary-color);"><?php echo number_format($total_usuarios); ?></div>
                <div class="stat-label">Usuarios Registrados</div>
            </div>
            <div class="stat-card">
                <div class="stat-value" style="color: var(--secondary-color);"><?php echo number_format($total_inversiones_activas); ?></div>
                <div class="stat-label">Inversiones Activas</div>
            </div>
            <div class="stat-card">
                <div class="stat-value" style="color: var(--warning-color);"><?php echo formatCurrency($monto_recargas_pendientes); ?></div>
                <div class="stat-label">Recargas Pendientes</div>
            </div>
            <div class="stat-card">
                <div class="stat-value" style="color: var(--danger-color);"><?php echo formatCurrency($monto_retiros_pendientes); ?></div>
                <div class="stat-label">Retiros Pendientes</div>
            </div>
            <div class="stat-card">
                <div class="stat-value" style="color: var(--primary-color);"><?php echo formatCurrency($total_invertido); ?></div>
                <div class="stat-label">Total Invertido</div>
            </div>
        </div>

        <!-- Navegación de admin -->
        <div class="action-buttons" style="margin-bottom: 30px;">
            <a href="bloqueo_retiros.php" class="action-btn" style="background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);">
                <i class="fas fa-ban" style="font-size: 24px; margin-bottom: 8px;"></i>
                <span class="action-btn-title">Pausar Retiros</span>
            </a>
            <a href="recargas.php" class="action-btn">
                <i class="fas fa-wallet action-btn-icon"></i>
                <span class="action-btn-title">Recargas</span>
                <span class="badge badge-warning"><?php echo $total_recargas_pendientes; ?> pendientes</span>
            </a>
            <a href="retiros.php" class="action-btn">
                <i class="fas fa-money-bill-wave action-btn-icon"></i>
                <span class="action-btn-title">Retiros</span>
                <span class="badge badge-warning"><?php echo $total_retiros_pendientes; ?> pendientes</span>
            </a>
            <a href="usuarios.php" class="action-btn">
                <i class="fas fa-users action-btn-icon"></i>
                <span class="action-btn-title">Usuarios</span>
            </a>
            <a href="inversiones.php" class="action-btn">
                <i class="fas fa-chart-line action-btn-icon"></i>
                <span class="action-btn-title">Inversiones</span>
            </a>
            <a href="codigos.php" class="action-btn">
                <i class="fas fa-ticket-alt action-btn-icon"></i>
                <span class="action-btn-title">Códigos</span>
            </a>
            <a href="avisos.php" class="action-btn">
                <i class="fas fa-bullhorn action-btn-icon"></i>
                <span class="action-btn-title">Avisos</span>
            </a>
            <a href="configuracion.php" class="action-btn">
                <i class="fas fa-cog action-btn-icon"></i>
                <span class="action-btn-title">Configuración</span>
            </a>
        </div>

        <!-- Recargas pendientes -->
        <?php if (!empty($recargas_pendientes)): ?>
        <div class="card">
            <div class="card-header">
                <h2 class="card-title">Recargas Pendientes</h2>
            </div>
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Usuario</th>
                            <th>Monto</th>
                            <th>Método</th>
                            <th>Comprobante</th>
                            <th>Fecha</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recargas_pendientes as $recarga): ?>
                            <tr>
                                <td>#<?php echo $recarga['id']; ?></td>
                                <td><?php echo htmlspecialchars($recarga['telefono']); ?></td>
                                <td style="font-weight: 600; color: var(--secondary-color);"><?php echo formatCurrency($recarga['monto']); ?></td>
                                <td><?php echo htmlspecialchars($recarga['metodo_pago']); ?></td>
                                <td><?php echo htmlspecialchars($recarga['comprobante'] ?: 'N/A'); ?></td>
                                <td><?php echo date('d/m/Y H:i', strtotime($recarga['fecha_solicitud'])); ?></td>
                                <td>
                                    <button onclick="aprobarRecarga(<?php echo $recarga['id']; ?>)" class="btn btn-secondary" style="padding: 5px 15px; font-size: 14px;">
                                        <i class="fas fa-check"></i> Aprobar
                                    </button>
                                    <button onclick="rechazarRecarga(<?php echo $recarga['id']; ?>)" class="btn btn-danger" style="padding: 5px 15px; font-size: 14px;">
                                        <i class="fas fa-times"></i> Rechazar
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php endif; ?>

        <!-- Retiros pendientes -->
        <?php if (!empty($retiros_pendientes)): ?>
        <div class="card">
            <div class="card-header">
                <h2 class="card-title">Retiros Pendientes</h2>
            </div>
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Usuario</th>
                            <th>Monto</th>
                            <th>Método</th>
                            <th>Cuenta Destino</th>
                            <th>Fecha</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($retiros_pendientes as $retiro): ?>
                            <tr>
                                <td>#<?php echo $retiro['id']; ?></td>
                                <td><?php echo htmlspecialchars($retiro['telefono']); ?></td>
                                <td style="font-weight: 600; color: var(--danger-color);"><?php echo formatCurrency($retiro['monto']); ?></td>
                                <td><?php echo htmlspecialchars($retiro['metodo_pago']); ?></td>
                                <td>
                                    <?php if ($retiro['metodo_pago'] === 'transferencia' || $retiro['metodo_pago'] === 'transferencia bancaria'): ?>
                                        <?php if (!empty($retiro['nombre_titular'])): ?>
                                            <strong><?php echo htmlspecialchars($retiro['nombre_titular']); ?></strong><br>
                                        <?php endif; ?>
                                        <small style="color: #666;"><?php echo htmlspecialchars($retiro['cuenta_destino']); ?></small>
                                    <?php else: ?>
                                        <?php echo htmlspecialchars($retiro['cuenta_destino']); ?>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo date('d/m/Y H:i', strtotime($retiro['fecha_solicitud'])); ?></td>
                                <td>
                                    <button onclick="aprobarRetiro(<?php echo $retiro['id']; ?>)" class="btn btn-secondary" style="padding: 5px 15px; font-size: 14px;">
                                        <i class="fas fa-check"></i> Aprobar
                                    </button>
                                    <button onclick="rechazarRetiro(<?php echo $retiro['id']; ?>)" class="btn btn-danger" style="padding: 5px 15px; font-size: 14px;">
                                        <i class="fas fa-times"></i> Rechazar
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <script src="../js/main.js"></script>
    <script src="js/admin.js"></script>
</body>
</html>

