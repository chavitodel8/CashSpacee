<?php
require_once __DIR__ . '/../config/config.php';
requireAdmin();

$conn = getConnection();

// Procesar cambios de configuración
$mensaje = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['cambiar_password_admin'])) {
        $password_actual = isset($_POST['password_actual']) ? $_POST['password_actual'] : '';
        $password_nueva = isset($_POST['password_nueva']) ? $_POST['password_nueva'] : '';
        $password_confirmar = isset($_POST['password_confirmar']) ? $_POST['password_confirmar'] : '';
        
        if (empty($password_actual) || empty($password_nueva) || empty($password_confirmar)) {
            $error = "Todos los campos son requeridos.";
        } elseif ($password_nueva !== $password_confirmar) {
            $error = "Las contraseñas nuevas no coinciden.";
        } elseif (strlen($password_nueva) < 6) {
            $error = "La contraseña debe tener al menos 6 caracteres.";
        } else {
            // Verificar contraseña actual
            $admin_id = $_SESSION['user_id'];
            $stmt = $conn->prepare("SELECT password FROM users WHERE id = ?");
            $stmt->bind_param("i", $admin_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $admin = $result->fetch_assoc();
            $stmt->close();
            
            if ($admin && password_verify($password_actual, $admin['password'])) {
                // Actualizar contraseña
                $password_hash = password_hash($password_nueva, PASSWORD_DEFAULT);
                $stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
                $stmt->bind_param("si", $password_hash, $admin_id);
                
                if ($stmt->execute()) {
                    $mensaje = "Contraseña cambiada exitosamente.";
                } else {
                    $error = "Error al cambiar la contraseña.";
                }
                $stmt->close();
            } else {
                $error = "La contraseña actual es incorrecta.";
            }
        }
    } elseif (isset($_POST['activar_usuario'])) {
        $usuario_id = isset($_POST['usuario_id']) ? intval($_POST['usuario_id']) : 0;
        $estado = isset($_POST['estado']) ? sanitize($_POST['estado']) : '';
        
        if ($usuario_id > 0 && in_array($estado, ['activo', 'inactivo'])) {
            $stmt = $conn->prepare("UPDATE users SET estado = ? WHERE id = ? AND tipo_usuario = 'user'");
            $stmt->bind_param("si", $estado, $usuario_id);
            
            if ($stmt->execute()) {
                $mensaje = "Estado del usuario actualizado exitosamente.";
            } else {
                $error = "Error al actualizar el estado del usuario.";
            }
            $stmt->close();
        }
    }
}

// Obtener información del sistema
$result = $conn->query("SELECT COUNT(*) as total FROM users");
$total_usuarios_sistema = $result->fetch_assoc()['total'];

$result = $conn->query("SELECT COUNT(*) as total FROM inversiones");
$total_inversiones_sistema = $result->fetch_assoc()['total'];

$result = $conn->query("SELECT COALESCE(SUM(saldo), 0) as total FROM users");
$saldo_total_sistema = $result->fetch_assoc()['total'];

$result = $conn->query("SELECT COUNT(*) as total FROM codigos_promocionales WHERE estado = 'activo'");
$codigos_activos_sistema = $result->fetch_assoc()['total'];

// Información del admin actual
$admin_id = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->bind_param("i", $admin_id);
$stmt->execute();
$admin_actual = $stmt->get_result()->fetch_assoc();
$stmt->close();

closeConnection($conn);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Configuración - CashSpace Admin</title>
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
            <h1 style="color: var(--dark-color); margin: 0;">Configuración del Sistema</h1>
            <a href="index.php" class="btn btn-outline">
                <i class="fas fa-arrow-left"></i> Volver al Panel
            </a>
        </div>

        <!-- Mensajes -->
        <?php if ($mensaje): ?>
            <div class="alert alert-success" style="margin-bottom: 20px;">
                <i class="fas fa-check-circle"></i> <?php echo $mensaje; ?>
            </div>
        <?php endif; ?>
        <?php if ($error): ?>
            <div class="alert alert-danger" style="margin-bottom: 20px;">
                <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
            </div>
        <?php endif; ?>

        <!-- Información del Sistema -->
        <div class="card" style="margin-bottom: 30px;">
            <div class="card-header">
                <h2 class="card-title">Información del Sistema</h2>
            </div>
            <div style="padding: 20px;">
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-value" style="color: var(--primary-color);"><?php echo number_format($total_usuarios_sistema); ?></div>
                        <div class="stat-label">Total Usuarios</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-value" style="color: var(--secondary-color);"><?php echo number_format($total_inversiones_sistema); ?></div>
                        <div class="stat-label">Total Inversiones</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-value" style="color: var(--success-color);"><?php echo formatCurrency($saldo_total_sistema); ?></div>
                        <div class="stat-label">Saldo Total Sistema</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-value" style="color: var(--warning-color);"><?php echo number_format($codigos_activos_sistema); ?></div>
                        <div class="stat-label">Códigos Activos</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Cambiar Contraseña -->
        <div class="card" style="margin-bottom: 30px;">
            <div class="card-header">
                <h2 class="card-title">Cambiar Contraseña de Administrador</h2>
            </div>
            <div style="padding: 20px;">
                <form method="POST">
                    <div style="max-width: 400px;">
                        <div style="margin-bottom: 15px;">
                            <label style="font-weight: 600; display: block; margin-bottom: 5px;">Usuario Actual:</label>
                            <input type="text" value="<?php echo htmlspecialchars($admin_actual['telefono']); ?>" disabled 
                                   style="padding: 10px; border-radius: 5px; border: 1px solid #ddd; width: 100%; background: #f5f5f5;">
                        </div>
                        <div style="margin-bottom: 15px;">
                            <label style="font-weight: 600; display: block; margin-bottom: 5px;">Contraseña Actual:</label>
                            <input type="password" name="password_actual" required 
                                   placeholder="Ingresa tu contraseña actual" 
                                   style="padding: 10px; border-radius: 5px; border: 1px solid #ddd; width: 100%;">
                        </div>
                        <div style="margin-bottom: 15px;">
                            <label style="font-weight: 600; display: block; margin-bottom: 5px;">Nueva Contraseña:</label>
                            <input type="password" name="password_nueva" required minlength="6"
                                   placeholder="Mínimo 6 caracteres" 
                                   style="padding: 10px; border-radius: 5px; border: 1px solid #ddd; width: 100%;">
                        </div>
                        <div style="margin-bottom: 15px;">
                            <label style="font-weight: 600; display: block; margin-bottom: 5px;">Confirmar Nueva Contraseña:</label>
                            <input type="password" name="password_confirmar" required minlength="6"
                                   placeholder="Confirma la nueva contraseña" 
                                   style="padding: 10px; border-radius: 5px; border: 1px solid #ddd; width: 100%;">
                        </div>
                        <button type="submit" name="cambiar_password_admin" class="btn btn-primary">
                            <i class="fas fa-key"></i> Cambiar Contraseña
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Información del Administrador -->
        <div class="card">
            <div class="card-header">
                <h2 class="card-title">Información del Administrador</h2>
            </div>
            <div style="padding: 20px;">
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 15px;">
                    <div>
                        <label style="font-weight: 600; color: #999; display: block; margin-bottom: 5px;">ID:</label>
                        <div style="font-size: 16px;">#<?php echo $admin_actual['id']; ?></div>
                    </div>
                    <div>
                        <label style="font-weight: 600; color: #999; display: block; margin-bottom: 5px;">Teléfono:</label>
                        <div style="font-size: 16px;"><?php echo htmlspecialchars($admin_actual['telefono']); ?></div>
                    </div>
                    <div>
                        <label style="font-weight: 600; color: #999; display: block; margin-bottom: 5px;">Estado:</label>
                        <span class="badge badge-success">
                            <i class="fas fa-check"></i> <?php echo ucfirst($admin_actual['estado']); ?>
                        </span>
                    </div>
                    <div>
                        <label style="font-weight: 600; color: #999; display: block; margin-bottom: 5px;">Fecha Registro:</label>
                        <div style="font-size: 16px;"><?php echo date('d/m/Y H:i', strtotime($admin_actual['fecha_registro'])); ?></div>
                    </div>
                    <div>
                        <label style="font-weight: 600; color: #999; display: block; margin-bottom: 5px;">Último Acceso:</label>
                        <div style="font-size: 16px;"><?php echo $admin_actual['ultimo_acceso'] ? date('d/m/Y H:i', strtotime($admin_actual['ultimo_acceso'])) : 'Nunca'; ?></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Versión del Sistema -->
        <div class="card" style="margin-top: 30px;">
            <div class="card-header">
                <h2 class="card-title">Versión del Sistema</h2>
            </div>
            <div style="padding: 20px;">
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <div>
                        <strong>CashSpace</strong> - Plataforma de Inversiones
                        <br><small style="color: #999;">Versión 1.0.0</small>
                    </div>
                    <div>
                        <i class="fas fa-diamond" style="font-size: 32px; color: var(--primary-color);"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="../js/main.js"></script>
    <script src="js/admin.js"></script>
</body>
</html>

