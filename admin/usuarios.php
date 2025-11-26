<?php
require_once __DIR__ . '/../config/config.php';
requireAdmin();

$conn = getConnection();

// Estadísticas
$result = $conn->query("SELECT COUNT(*) as total FROM users WHERE tipo_usuario = 'user'");
$total_usuarios = $result->fetch_assoc()['total'];

$result = $conn->query("SELECT COUNT(*) as total FROM users WHERE tipo_usuario = 'user' AND estado = 'activo'");
$usuarios_activos = $result->fetch_assoc()['total'];

$result = $conn->query("SELECT COALESCE(SUM(saldo_disponible), 0) as total FROM users WHERE tipo_usuario = 'user'");
$saldo_total = $result->fetch_assoc()['total'];

$result = $conn->query("SELECT COALESCE(SUM(saldo_invertido), 0) as total FROM users WHERE tipo_usuario = 'user'");
$saldo_invertido_total = $result->fetch_assoc()['total'];

// Filtros
$filtro_estado = isset($_GET['estado']) ? sanitize($_GET['estado']) : 'todos';
$filtro_busqueda = isset($_GET['busqueda']) ? sanitize($_GET['busqueda']) : '';

$query_where = "WHERE tipo_usuario = 'user'";

if ($filtro_estado !== 'todos') {
    $filtro_estado = in_array($filtro_estado, ['activo', 'inactivo']) ? $filtro_estado : 'todos';
    if ($filtro_estado !== 'todos') {
        $query_where .= " AND estado = '" . $conn->real_escape_string($filtro_estado) . "'";
    }
}

if (!empty($filtro_busqueda)) {
    $busqueda = $conn->real_escape_string($filtro_busqueda);
    $query_where .= " AND (telefono LIKE '%$busqueda%' OR nombre LIKE '%$busqueda%' OR codigo_invitacion LIKE '%$busqueda%')";
}

// Obtener usuarios
$query = "SELECT * FROM users $query_where ORDER BY fecha_registro DESC LIMIT 100";
$result = $conn->query($query);
$usuarios = $result->fetch_all(MYSQLI_ASSOC);

closeConnection($conn);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Usuarios - CashSpace Admin</title>
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
            <h1 style="color: var(--dark-color); margin: 0;">Gestión de Usuarios</h1>
            <a href="index.php" class="btn btn-outline">
                <i class="fas fa-arrow-left"></i> Volver al Panel
            </a>
        </div>

        <!-- Estadísticas -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-value" style="color: var(--primary-color);"><?php echo number_format($total_usuarios); ?></div>
                <div class="stat-label">Total Usuarios</div>
            </div>
            <div class="stat-card">
                <div class="stat-value" style="color: var(--secondary-color);"><?php echo number_format($usuarios_activos); ?></div>
                <div class="stat-label">Usuarios Activos</div>
            </div>
            <div class="stat-card">
                <div class="stat-value" style="color: var(--success-color);"><?php echo formatCurrency($saldo_total); ?></div>
                <div class="stat-label">Saldo Total Disponible</div>
            </div>
            <div class="stat-card">
                <div class="stat-value" style="color: var(--primary-color);"><?php echo formatCurrency($saldo_invertido_total); ?></div>
                <div class="stat-label">Saldo Total Invertido</div>
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
                            <option value="activo" <?php echo $filtro_estado === 'activo' ? 'selected' : ''; ?>>Activos</option>
                            <option value="inactivo" <?php echo $filtro_estado === 'inactivo' ? 'selected' : ''; ?>>Inactivos</option>
                        </select>
                    </div>
                    <div style="flex: 1; min-width: 200px;">
                        <label style="font-weight: 600; display: block; margin-bottom: 5px;">Buscar:</label>
                        <input type="text" name="busqueda" value="<?php echo htmlspecialchars($filtro_busqueda); ?>" 
                               placeholder="Teléfono, nombre o código..." 
                               style="padding: 10px; border-radius: 5px; border: 1px solid #ddd; width: 100%;">
                    </div>
                    <div style="align-self: flex-end;">
                        <button type="submit" class="btn btn-primary" style="margin-right: 10px;">
                            <i class="fas fa-search"></i> Buscar
                        </button>
                        <a href="usuarios.php" class="btn btn-outline">
                            <i class="fas fa-times"></i> Limpiar
                        </a>
                    </div>
                </form>
            </div>
        </div>

        <!-- Tabla de usuarios -->
        <div class="card">
            <div class="card-header">
                <h2 class="card-title">Lista de Usuarios</h2>
            </div>
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Teléfono</th>
                            <th>Nombre</th>
                            <th>Código Invitación</th>
                            <th>Código Referido</th>
                            <th>Saldo Total</th>
                            <th>Saldo Disponible</th>
                            <th>Saldo Invertido</th>
                            <th>Estado</th>
                            <th>Fecha Registro</th>
                            <th>Último Acceso</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($usuarios)): ?>
                            <tr>
                                <td colspan="11" style="text-align: center; padding: 40px; color: #999;">
                                    <i class="fas fa-users" style="font-size: 48px; margin-bottom: 15px; display: block;"></i>
                                    No hay usuarios registrados
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($usuarios as $usuario): ?>
                                <tr>
                                    <td>#<?php echo $usuario['id']; ?></td>
                                    <td><strong><?php echo htmlspecialchars($usuario['telefono']); ?></strong></td>
                                    <td><?php echo htmlspecialchars($usuario['nombre'] ?: 'N/A'); ?></td>
                                    <td><code><?php echo htmlspecialchars($usuario['codigo_invitacion'] ?: 'N/A'); ?></code></td>
                                    <td><code><?php echo htmlspecialchars($usuario['codigo_referido'] ?: 'N/A'); ?></code></td>
                                    <td style="font-weight: 600;"><?php echo formatCurrency($usuario['saldo']); ?></td>
                                    <td style="color: var(--success-color);"><?php echo formatCurrency($usuario['saldo_disponible']); ?></td>
                                    <td style="color: var(--primary-color);"><?php echo formatCurrency($usuario['saldo_invertido']); ?></td>
                                    <td>
                                        <?php
                                        $estado_class = $usuario['estado'] === 'activo' ? 'badge-success' : 'badge-danger';
                                        ?>
                                        <span class="badge <?php echo $estado_class; ?>">
                                            <i class="fas fa-<?php echo $usuario['estado'] === 'activo' ? 'check' : 'times'; ?>"></i> 
                                            <?php echo ucfirst($usuario['estado']); ?>
                                        </span>
                                    </td>
                                    <td><?php echo date('d/m/Y H:i', strtotime($usuario['fecha_registro'])); ?></td>
                                    <td><?php echo $usuario['ultimo_acceso'] ? date('d/m/Y H:i', strtotime($usuario['ultimo_acceso'])) : 'Nunca'; ?></td>
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

