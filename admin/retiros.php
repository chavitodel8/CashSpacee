<?php
require_once __DIR__ . '/../config/config.php';
requireAdmin();

// Obtener estadísticas de retiros
$conn = getConnection();

// Total de retiros pendientes
$result = $conn->query("SELECT COUNT(*) as total FROM retiros WHERE estado = 'pendiente'");
$total_retiros_pendientes = $result->fetch_assoc()['total'];

// Monto total de retiros pendientes
$result = $conn->query("SELECT COALESCE(SUM(monto), 0) as total FROM retiros WHERE estado = 'pendiente'");
$monto_retiros_pendientes = $result->fetch_assoc()['total'];

// Total de retiros aprobados (hoy)
$result = $conn->query("SELECT COALESCE(SUM(monto), 0) as total FROM retiros WHERE estado = 'aprobado' AND DATE(fecha_procesamiento) = CURDATE()");
$retiros_aprobados_hoy = $result->fetch_assoc()['total'];

// Total de retiros rechazados (este mes)
$result = $conn->query("SELECT COUNT(*) as total FROM retiros WHERE estado = 'rechazado' AND MONTH(fecha_procesamiento) = MONTH(CURDATE())");
$retiros_rechazados_mes = $result->fetch_assoc()['total'];

// Filtro de estado
$filtro_estado = isset($_GET['estado']) ? sanitize($_GET['estado']) : 'todos';

// Construir consulta según filtro
$query_where = "";
if ($filtro_estado !== 'todos') {
    $filtro_estado = in_array($filtro_estado, ['pendiente', 'aprobado', 'rechazado']) ? $filtro_estado : 'todos';
    if ($filtro_estado !== 'todos') {
        $query_where = "WHERE r.estado = '" . $conn->real_escape_string($filtro_estado) . "'";
    }
}

// Obtener retiros
$query = "SELECT r.*, u.telefono, u.nombre, cb.nombre_titular 
          FROM retiros r 
          JOIN users u ON r.usuario_id = u.id 
          LEFT JOIN cuenta_bancaria cb ON u.id = cb.usuario_id
          $query_where 
          ORDER BY r.fecha_solicitud DESC";
$result = $conn->query($query);
$retiros = $result->fetch_all(MYSQLI_ASSOC);

closeConnection($conn);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Retiros - CashSpace Admin</title>
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
            <h1 style="color: var(--dark-color); margin: 0;">Gestión de Retiros</h1>
            <a href="index.php" class="btn btn-outline">
                <i class="fas fa-arrow-left"></i> Volver al Panel
            </a>
        </div>

        <!-- Estadísticas -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-value" style="color: var(--warning-color);"><?php echo number_format($total_retiros_pendientes); ?></div>
                <div class="stat-label">Retiros Pendientes</div>
            </div>
            <div class="stat-card">
                <div class="stat-value" style="color: var(--warning-color);"><?php echo formatCurrency($monto_retiros_pendientes); ?></div>
                <div class="stat-label">Monto Pendiente</div>
            </div>
            <div class="stat-card">
                <div class="stat-value" style="color: var(--secondary-color);"><?php echo formatCurrency($retiros_aprobados_hoy); ?></div>
                <div class="stat-label">Aprobados Hoy</div>
            </div>
            <div class="stat-card">
                <div class="stat-value" style="color: var(--danger-color);"><?php echo number_format($retiros_rechazados_mes); ?></div>
                <div class="stat-label">Rechazados (Mes)</div>
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
                        <option value="aprobado" <?php echo $filtro_estado === 'aprobado' ? 'selected' : ''; ?>>Aprobados</option>
                        <option value="rechazado" <?php echo $filtro_estado === 'rechazado' ? 'selected' : ''; ?>>Rechazados</option>
                    </select>
                </form>
            </div>
        </div>

        <!-- Tabla de retiros -->
        <div class="card">
            <div class="card-header">
                <h2 class="card-title">Lista de Retiros</h2>
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
                            <th>Estado</th>
                            <th>Fecha Solicitud</th>
                            <th>Fecha Procesamiento</th>
                            <th>Observaciones</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($retiros)): ?>
                            <tr>
                                <td colspan="10" style="text-align: center; padding: 40px; color: #999;">
                                    <i class="fas fa-inbox" style="font-size: 48px; margin-bottom: 15px; display: block;"></i>
                                    No hay retiros registrados
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($retiros as $retiro): ?>
                                <tr>
                                    <td>#<?php echo $retiro['id']; ?></td>
                                    <td>
                                        <strong><?php echo htmlspecialchars($retiro['telefono']); ?></strong>
                                        <?php if ($retiro['nombre']): ?>
                                            <br><small style="color: #999;"><?php echo htmlspecialchars($retiro['nombre']); ?></small>
                                        <?php endif; ?>
                                    </td>
                                    <td style="font-weight: 600; color: var(--danger-color);"><?php echo formatCurrency($retiro['monto']); ?></td>
                                    <td><?php echo htmlspecialchars($retiro['metodo_pago'] ?: 'N/A'); ?></td>
                                    <td>
                                        <?php if ($retiro['metodo_pago'] === 'transferencia' || $retiro['metodo_pago'] === 'transferencia bancaria'): ?>
                                            <?php if (!empty($retiro['nombre_titular'])): ?>
                                                <strong><?php echo htmlspecialchars($retiro['nombre_titular']); ?></strong><br>
                                            <?php endif; ?>
                                            <small style="color: #666;"><?php echo htmlspecialchars($retiro['cuenta_destino'] ?: 'N/A'); ?></small>
                                        <?php else: ?>
                                            <?php echo htmlspecialchars($retiro['cuenta_destino'] ?: 'N/A'); ?>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php
                                        $estado_class = '';
                                        $estado_icon = '';
                                        switch($retiro['estado']) {
                                            case 'pendiente':
                                                $estado_class = 'badge-warning';
                                                $estado_icon = 'clock';
                                                break;
                                            case 'aprobado':
                                                $estado_class = 'badge-success';
                                                $estado_icon = 'check';
                                                break;
                                            case 'rechazado':
                                                $estado_class = 'badge-danger';
                                                $estado_icon = 'times';
                                                break;
                                        }
                                        ?>
                                        <span class="badge <?php echo $estado_class; ?>">
                                            <i class="fas fa-<?php echo $estado_icon; ?>"></i> 
                                            <?php echo ucfirst($retiro['estado']); ?>
                                        </span>
                                    </td>
                                    <td><?php echo date('d/m/Y H:i', strtotime($retiro['fecha_solicitud'])); ?></td>
                                    <td><?php echo $retiro['fecha_procesamiento'] ? date('d/m/Y H:i', strtotime($retiro['fecha_procesamiento'])) : '-'; ?></td>
                                    <td><?php echo htmlspecialchars($retiro['observaciones'] ?: '-'); ?></td>
                                    <td>
                                        <?php if ($retiro['estado'] === 'pendiente'): ?>
                                            <button onclick="aprobarRetiro(<?php echo $retiro['id']; ?>)" class="btn btn-secondary" style="padding: 5px 15px; font-size: 14px; margin-right: 5px;">
                                                <i class="fas fa-check"></i> Aprobar
                                            </button>
                                            <button onclick="rechazarRetiro(<?php echo $retiro['id']; ?>)" class="btn btn-danger" style="padding: 5px 15px; font-size: 14px;">
                                                <i class="fas fa-times"></i> Rechazar
                                            </button>
                                        <?php else: ?>
                                            <span style="color: #999; font-style: italic;">Procesado</span>
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

