<?php
require_once __DIR__ . '/../config/config.php';
requireAdmin();

// Obtener estadísticas de recargas
$conn = getConnection();

// Total de recargas pendientes
$result = $conn->query("SELECT COUNT(*) as total FROM recargas WHERE estado = 'pendiente'");
$total_recargas_pendientes = $result->fetch_assoc()['total'];

// Monto total de recargas pendientes
$result = $conn->query("SELECT COALESCE(SUM(monto), 0) as total FROM recargas WHERE estado = 'pendiente'");
$monto_recargas_pendientes = $result->fetch_assoc()['total'];

// Total de recargas aprobadas (hoy)
$result = $conn->query("SELECT COALESCE(SUM(monto), 0) as total FROM recargas WHERE estado = 'aprobada' AND DATE(fecha_aprobacion) = CURDATE()");
$recargas_aprobadas_hoy = $result->fetch_assoc()['total'];

// Total de recargas rechazadas (este mes)
$result = $conn->query("SELECT COUNT(*) as total FROM recargas WHERE estado = 'rechazada' AND MONTH(fecha_aprobacion) = MONTH(CURDATE())");
$recargas_rechazadas_mes = $result->fetch_assoc()['total'];

// Filtro de estado
$filtro_estado = isset($_GET['estado']) ? sanitize($_GET['estado']) : 'todos';

// Construir consulta según filtro
$query_where = "";
if ($filtro_estado !== 'todos') {
    $filtro_estado = in_array($filtro_estado, ['pendiente', 'aprobada', 'rechazada']) ? $filtro_estado : 'todos';
    if ($filtro_estado !== 'todos') {
        $query_where = "WHERE r.estado = '" . $conn->real_escape_string($filtro_estado) . "'";
    }
}

// Obtener recargas (incluyendo nuevos campos si existen)
$query = "SELECT r.*, u.telefono, u.nombre 
          FROM recargas r 
          JOIN users u ON r.usuario_id = u.id 
          $query_where 
          ORDER BY r.fecha_solicitud DESC";
$result = $conn->query($query);
$recargas = $result->fetch_all(MYSQLI_ASSOC);

// Verificar si existen las columnas nuevas
$check_columns = $conn->query("SHOW COLUMNS FROM recargas LIKE 'qr_code'");
$has_qr_column = $check_columns->num_rows > 0;
$check_columns->close();

closeConnection($conn);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Recargas - CashSpace Admin</title>
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
                <a href="index.php" class="btn btn-outline">Panel</a>
                <a href="../index.php" class="btn btn-outline">Ir al Inicio</a>
                <a href="../logout.php" class="btn btn-danger">Salir</a>
            </div>
        </div>
    </nav>

    <!-- Contenedor principal -->
    <div class="container">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px;">
            <h1 style="color: var(--dark-color); margin: 0;">Gestión de Recargas</h1>
            <a href="index.php" class="btn btn-outline">
                <i class="fas fa-arrow-left"></i> Volver al Panel
            </a>
        </div>

        <!-- Estadísticas -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-value" style="color: var(--warning-color);"><?php echo number_format($total_recargas_pendientes); ?></div>
                <div class="stat-label">Recargas Pendientes</div>
            </div>
            <div class="stat-card">
                <div class="stat-value" style="color: var(--warning-color);"><?php echo formatCurrency($monto_recargas_pendientes); ?></div>
                <div class="stat-label">Monto Pendiente</div>
            </div>
            <div class="stat-card">
                <div class="stat-value" style="color: var(--secondary-color);"><?php echo formatCurrency($recargas_aprobadas_hoy); ?></div>
                <div class="stat-label">Aprobadas Hoy</div>
            </div>
            <div class="stat-card">
                <div class="stat-value" style="color: var(--danger-color);"><?php echo number_format($recargas_rechazadas_mes); ?></div>
                <div class="stat-label">Rechazadas (Mes)</div>
            </div>
        </div>

        <!-- Filtros -->
        <div class="card" style="margin-bottom: 30px;">
            <div class="card-header">
                <h2 class="card-title">Filtros</h2>
            </div>
            <div style="padding: 20px;">
                <form method="GET" style="display: flex; gap: 15px; align-items: center;">
                    <label style="font-weight: 600;">Estado:</label>
                    <select name="estado" style="padding: 10px; border-radius: 5px; border: 1px solid #ddd;" onchange="this.form.submit()">
                        <option value="todos" <?php echo $filtro_estado === 'todos' ? 'selected' : ''; ?>>Todos</option>
                        <option value="pendiente" <?php echo $filtro_estado === 'pendiente' ? 'selected' : ''; ?>>Pendientes</option>
                        <option value="aprobada" <?php echo $filtro_estado === 'aprobada' ? 'selected' : ''; ?>>Aprobadas</option>
                        <option value="rechazada" <?php echo $filtro_estado === 'rechazada' ? 'selected' : ''; ?>>Rechazadas</option>
                    </select>
                </form>
            </div>
        </div>

        <!-- Tabla de recargas -->
        <div class="card">
            <div class="card-header">
                <h2 class="card-title">Lista de Recargas</h2>
            </div>
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Usuario</th>
                            <th>Monto</th>
                            <th>Método</th>
                            <th>Información de Pago</th>
                            <th>Estado</th>
                            <th>Fecha Solicitud</th>
                            <th>Fecha Aprobación</th>
                            <th>Observaciones</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($recargas)): ?>
                            <tr>
                                <td colspan="10" style="text-align: center; padding: 40px; color: #999;">
                                    <i class="fas fa-inbox" style="font-size: 48px; margin-bottom: 15px; display: block;"></i>
                                    No hay recargas registradas
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($recargas as $recarga): ?>
                                <tr>
                                    <td>#<?php echo $recarga['id']; ?></td>
                                    <td>
                                        <strong><?php echo htmlspecialchars($recarga['telefono']); ?></strong>
                                        <?php if ($recarga['nombre']): ?>
                                            <br><small style="color: #999;"><?php echo htmlspecialchars($recarga['nombre']); ?></small>
                                        <?php endif; ?>
                                    </td>
                                    <td style="font-weight: 600; color: var(--secondary-color);"><?php echo formatCurrency($recarga['monto']); ?></td>
                                    <td>
                                        <?php 
                                        $metodo = $recarga['metodo_pago'] ?: 'N/A';
                                        echo htmlspecialchars(ucfirst($metodo));
                                        ?>
                                    </td>
                                    <td>
                                        <?php if ($has_qr_column): ?>
                                            <?php if ($recarga['metodo_pago'] === 'transferencia' && !empty($recarga['qr_code'])): ?>
                                                <div style="display: flex; align-items: center; gap: 10px;">
                                                    <img src="<?php echo htmlspecialchars($recarga['qr_code']); ?>" 
                                                         alt="QR Code" 
                                                         style="width: 80px; height: 80px; border: 1px solid #ddd; border-radius: 5px; cursor: pointer;"
                                                         onclick="window.open('<?php echo htmlspecialchars($recarga['qr_code']); ?>', '_blank')">
                                                    <div>
                                                        <small style="display: block; color: #6b7280;">QR de Pago</small>
                                                        <small style="display: block; color: #999; font-size: 11px;">Click para ampliar</small>
                                                    </div>
                                                </div>
                                            <?php elseif ($recarga['metodo_pago'] === 'yape'): ?>
                                                <div style="padding: 8px; background: #f0f9ff; border-radius: 5px;">
                                                    <?php if (!empty($recarga['yape_numero'])): ?>
                                                        <small style="display: block; color: #0369a1;"><strong>Número:</strong> <?php echo htmlspecialchars($recarga['yape_numero']); ?></small>
                                                    <?php endif; ?>
                                                    <?php if (!empty($recarga['yape_nombre'])): ?>
                                                        <small style="display: block; color: #0369a1;"><strong>Nombre:</strong> <?php echo htmlspecialchars($recarga['yape_nombre']); ?></small>
                                                    <?php endif; ?>
                                                    <?php if (empty($recarga['yape_numero']) && empty($recarga['yape_nombre'])): ?>
                                                        <small style="color: #999;">Datos no disponibles</small>
                                                    <?php endif; ?>
                                                </div>
                                            <?php else: ?>
                                                <small style="color: #999;"><?php echo htmlspecialchars($recarga['comprobante'] ?: 'N/A'); ?></small>
                                            <?php endif; ?>
                                        <?php else: ?>
                                            <small style="color: #999;"><?php echo htmlspecialchars($recarga['comprobante'] ?: 'N/A'); ?></small>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php
                                        $estado_class = '';
                                        $estado_icon = '';
                                        switch($recarga['estado']) {
                                            case 'pendiente':
                                                $estado_class = 'badge-warning';
                                                $estado_icon = 'clock';
                                                break;
                                            case 'aprobada':
                                                $estado_class = 'badge-success';
                                                $estado_icon = 'check';
                                                break;
                                            case 'rechazada':
                                                $estado_class = 'badge-danger';
                                                $estado_icon = 'times';
                                                break;
                                        }
                                        ?>
                                        <span class="badge <?php echo $estado_class; ?>">
                                            <i class="fas fa-<?php echo $estado_icon; ?>"></i> 
                                            <?php echo ucfirst($recarga['estado']); ?>
                                        </span>
                                    </td>
                                    <td><?php echo date('d/m/Y H:i', strtotime($recarga['fecha_solicitud'])); ?></td>
                                    <td><?php echo $recarga['fecha_aprobacion'] ? date('d/m/Y H:i', strtotime($recarga['fecha_aprobacion'])) : '-'; ?></td>
                                    <td><?php echo htmlspecialchars($recarga['observaciones'] ?: '-'); ?></td>
                                    <td>
                                        <?php if ($recarga['estado'] === 'pendiente'): ?>
                                            <button onclick="aprobarRecarga(<?php echo $recarga['id']; ?>)" class="btn btn-secondary" style="padding: 5px 15px; font-size: 14px; margin-right: 5px;">
                                                <i class="fas fa-check"></i> Aprobar
                                            </button>
                                            <button onclick="rechazarRecarga(<?php echo $recarga['id']; ?>)" class="btn btn-danger" style="padding: 5px 15px; font-size: 14px;">
                                                <i class="fas fa-times"></i> Rechazar
                                            </button>
                                        <?php else: ?>
                                            <span style="color: #999; font-style: italic;">Procesada</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script src="../js/main.js"></script>
    <script src="js/admin.js"></script>
</body>
</html>

