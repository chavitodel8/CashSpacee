<?php
require_once __DIR__ . '/../config/config.php';
requireAdmin();

$conn = getConnection();

// Estadísticas
$result = $conn->query("SELECT COUNT(*) as total FROM inversiones WHERE estado = 'activa'");
$inversiones_activas = $result->fetch_assoc()['total'];

$result = $conn->query("SELECT COUNT(*) as total FROM inversiones WHERE estado = 'completada'");
$inversiones_completadas = $result->fetch_assoc()['total'];

$result = $conn->query("SELECT COALESCE(SUM(monto_invertido), 0) as total FROM inversiones WHERE estado = 'activa'");
$monto_invertido_activo = $result->fetch_assoc()['total'];

$result = $conn->query("SELECT COALESCE(SUM(ganancia_total_acumulada), 0) as total FROM inversiones WHERE estado = 'activa'");
$ganancias_acumuladas = $result->fetch_assoc()['total'];

// Filtros
$filtro_estado = isset($_GET['estado']) ? sanitize($_GET['estado']) : 'todos';
$filtro_busqueda = isset($_GET['busqueda']) ? sanitize($_GET['busqueda']) : '';

$query_where = "WHERE 1=1";

if ($filtro_estado !== 'todos') {
    $filtro_estado = in_array($filtro_estado, ['activa', 'completada', 'cancelada']) ? $filtro_estado : 'todos';
    if ($filtro_estado !== 'todos') {
        $query_where .= " AND i.estado = '" . $conn->real_escape_string($filtro_estado) . "'";
    }
}

if (!empty($filtro_busqueda)) {
    $busqueda = $conn->real_escape_string($filtro_busqueda);
    $query_where .= " AND (u.telefono LIKE '%$busqueda%' OR u.nombre LIKE '%$busqueda%' OR ti.nombre LIKE '%$busqueda%')";
}

// Obtener inversiones
$query = "SELECT i.*, u.telefono, u.nombre as usuario_nombre, ti.nombre as tipo_inversion, ti.ganancia_diaria, ti.ganancia_mensual
          FROM inversiones i
          JOIN users u ON i.usuario_id = u.id
          JOIN tipos_inversion ti ON i.tipo_inversion_id = ti.id
          $query_where
          ORDER BY i.fecha_creacion DESC
          LIMIT 100";
$result = $conn->query($query);
$inversiones = $result->fetch_all(MYSQLI_ASSOC);

closeConnection($conn);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Inversiones - CashSpace Admin</title>
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
            <h1 style="color: var(--dark-color); margin: 0;">Gestión de Inversiones</h1>
            <a href="index.php" class="btn btn-outline">
                <i class="fas fa-arrow-left"></i> Volver al Panel
            </a>
        </div>

        <!-- Estadísticas -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-value" style="color: var(--secondary-color);"><?php echo number_format($inversiones_activas); ?></div>
                <div class="stat-label">Inversiones Activas</div>
            </div>
            <div class="stat-card">
                <div class="stat-value" style="color: var(--success-color);"><?php echo number_format($inversiones_completadas); ?></div>
                <div class="stat-label">Inversiones Completadas</div>
            </div>
            <div class="stat-card">
                <div class="stat-value" style="color: var(--primary-color);"><?php echo formatCurrency($monto_invertido_activo); ?></div>
                <div class="stat-label">Monto Invertido Activo</div>
            </div>
            <div class="stat-card">
                <div class="stat-value" style="color: var(--secondary-color);"><?php echo formatCurrency($ganancias_acumuladas); ?></div>
                <div class="stat-label">Ganancias Acumuladas</div>
            </div>
        </div>

        <!-- Filtros y búsqueda -->
        <div class="card" style="margin-bottom: 30px;">
            <div class="card-header">
                <h2 class="card-title">Filtros y Búsqueda</h2>
            </div>
            <div style="padding: 20px;">
                <form method="GET" style="display: flex; gap: 15px; align-items: center; flex-wrap: wrap;">
                    <div>
                        <label style="font-weight: 600; display: block; margin-bottom: 5px;">Estado:</label>
                        <select name="estado" style="padding: 10px; border-radius: 5px; border: 1px solid #ddd;">
                            <option value="todos" <?php echo $filtro_estado === 'todos' ? 'selected' : ''; ?>>Todos</option>
                            <option value="activa" <?php echo $filtro_estado === 'activa' ? 'selected' : ''; ?>>Activas</option>
                            <option value="completada" <?php echo $filtro_estado === 'completada' ? 'selected' : ''; ?>>Completadas</option>
                            <option value="cancelada" <?php echo $filtro_estado === 'cancelada' ? 'selected' : ''; ?>>Canceladas</option>
                        </select>
                    </div>
                    <div style="flex: 1; min-width: 200px;">
                        <label style="font-weight: 600; display: block; margin-bottom: 5px;">Buscar:</label>
                        <input type="text" name="busqueda" value="<?php echo htmlspecialchars($filtro_busqueda); ?>" 
                               placeholder="Usuario o tipo de inversión..." 
                               style="padding: 10px; border-radius: 5px; border: 1px solid #ddd; width: 100%;">
                    </div>
                    <div style="align-self: flex-end;">
                        <button type="submit" class="btn btn-primary" style="margin-right: 10px;">
                            <i class="fas fa-search"></i> Buscar
                        </button>
                        <a href="inversiones.php" class="btn btn-outline">
                            <i class="fas fa-times"></i> Limpiar
                        </a>
                    </div>
                </form>
            </div>
        </div>

        <!-- Tabla de inversiones -->
        <div class="card">
            <div class="card-header">
                <h2 class="card-title">Lista de Inversiones</h2>
            </div>
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Usuario</th>
                            <th>Tipo Inversión</th>
                            <th>Monto Invertido</th>
                            <th>Ganancia Diaria</th>
                            <th>Ganancia Acumulada</th>
                            <th>Fecha Inicio</th>
                            <th>Fecha Fin</th>
                            <th>Estado</th>
                            <th>Días Restantes</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($inversiones)): ?>
                            <tr>
                                <td colspan="10" style="text-align: center; padding: 40px; color: #999;">
                                    <i class="fas fa-chart-line" style="font-size: 48px; margin-bottom: 15px; display: block;"></i>
                                    No hay inversiones registradas
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($inversiones as $inversion): ?>
                                <?php
                                $fecha_fin = new DateTime($inversion['fecha_fin']);
                                $fecha_actual = new DateTime();
                                $dias_restantes = $fecha_actual < $fecha_fin ? $fecha_actual->diff($fecha_fin)->days : 0;
                                ?>
                                <tr>
                                    <td>#<?php echo $inversion['id']; ?></td>
                                    <td>
                                        <strong><?php echo htmlspecialchars($inversion['telefono']); ?></strong>
                                        <?php if ($inversion['usuario_nombre']): ?>
                                            <br><small style="color: #999;"><?php echo htmlspecialchars($inversion['usuario_nombre']); ?></small>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo htmlspecialchars($inversion['tipo_inversion']); ?></td>
                                    <td style="font-weight: 600; color: var(--primary-color);"><?php echo formatCurrency($inversion['monto_invertido']); ?></td>
                                    <td style="color: var(--secondary-color);"><?php echo formatCurrency($inversion['ganancia_diaria']); ?></td>
                                    <td style="color: var(--success-color); font-weight: 600;"><?php echo formatCurrency($inversion['ganancia_total_acumulada']); ?></td>
                                    <td><?php echo date('d/m/Y', strtotime($inversion['fecha_inicio'])); ?></td>
                                    <td><?php echo date('d/m/Y', strtotime($inversion['fecha_fin'])); ?></td>
                                    <td>
                                        <?php
                                        $estado_class = '';
                                        $estado_icon = '';
                                        switch($inversion['estado']) {
                                            case 'activa':
                                                $estado_class = 'badge-success';
                                                $estado_icon = 'check-circle';
                                                break;
                                            case 'completada':
                                                $estado_class = 'badge-primary';
                                                $estado_icon = 'check-double';
                                                break;
                                            case 'cancelada':
                                                $estado_class = 'badge-danger';
                                                $estado_icon = 'times-circle';
                                                break;
                                        }
                                        ?>
                                        <span class="badge <?php echo $estado_class; ?>">
                                            <i class="fas fa-<?php echo $estado_icon; ?>"></i> 
                                            <?php echo ucfirst($inversion['estado']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php if ($inversion['estado'] === 'activa'): ?>
                                            <strong style="color: var(--warning-color);"><?php echo $dias_restantes; ?> días</strong>
                                        <?php else: ?>
                                            <span style="color: #999;">-</span>
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

