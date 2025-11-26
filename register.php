<?php
require_once 'config/config.php';
require_once 'includes/auth.php';

// Si ya está logueado, redirigir
if (isLoggedIn()) {
    header('Location: index.php');
    exit();
}

$error = '';
$success = '';

// Obtener código de referido de la URL si existe
$codigo_referido_url = isset($_GET['ref']) ? sanitize($_GET['ref']) : '';

// Procesar formulario de registro
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['register'])) {
    $telefono = sanitize($_POST['telefono']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    // Priorizar el código del formulario, si no existe, usar el de la URL
    $codigo_invitacion = !empty($_POST['codigo_invitacion']) ? sanitize($_POST['codigo_invitacion']) : 
                        (!empty($codigo_referido_url) ? $codigo_referido_url : null);
    
    if (empty($telefono) || empty($password)) {
        $error = 'El teléfono y la contraseña son obligatorios';
    } elseif ($password !== $confirm_password) {
        $error = 'Las contraseñas no coinciden';
    } elseif (strlen($password) < 6) {
        $error = 'La contraseña debe tener al menos 6 caracteres';
    } else {
        $result = registerUser($telefono, $password, $codigo_invitacion);
        if ($result['success']) {
            $success = 'Registro exitoso. Inicia sesión ahora.';
        } else {
            $error = $result['message'];
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro - CashSpace</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="auth-container">
        <div class="auth-card">
            <div class="auth-header">
                <div class="logo">
                    <img src="assets/images/logo.png" alt="CashSpace" onerror="this.style.display='none'; this.nextElementSibling.style.display='block';">
                    <span class="logo-text" style="display:none;">CashSpace</span>
                </div>
                <p class="auth-subtitle">Crea tu cuenta y comienza a invertir</p>
            </div>
            
            <?php if ($error): ?>
                <div style="background: #fee2e2; color: #991b1b; padding: 15px; border-radius: 10px; margin-bottom: 20px;">
                    <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
                </div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div style="background: #d1fae5; color: #065f46; padding: 15px; border-radius: 10px; margin-bottom: 20px;">
                    <i class="fas fa-check-circle"></i> <?php echo $success; ?>
                    <br><a href="login.php" class="link" style="margin-top: 10px; display: inline-block;">Ir al login</a>
                </div>
            <?php endif; ?>
            
            <?php if (!$success): ?>
            <form method="POST" action="">
                <div class="form-group">
                    <label class="form-label">Número de Teléfono</label>
                    <div class="input-wrapper">
                        <i class="fas fa-phone input-icon"></i>
                        <input type="text" name="telefono" class="form-control" placeholder="+591 73598635" required>
                    </div>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Contraseña</label>
                    <div class="input-wrapper">
                        <i class="fas fa-lock input-icon"></i>
                        <input type="password" name="password" class="form-control" placeholder="Mínimo 6 caracteres" required minlength="6">
                    </div>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Confirmar Contraseña</label>
                    <div class="input-wrapper">
                        <i class="fas fa-lock input-icon"></i>
                        <input type="password" name="confirm_password" class="form-control" placeholder="Confirma tu contraseña" required minlength="6">
                    </div>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Código de Invitación (Opcional)</label>
                    <div class="input-wrapper">
                        <i class="fas fa-gift input-icon"></i>
                        <input type="text" name="codigo_invitacion" class="form-control" placeholder="Código de referencia" value="<?php echo htmlspecialchars($codigo_referido_url); ?>">
                    </div>
                </div>
                
                <button type="submit" name="register" class="btn btn-primary">
                    <i class="fas fa-user-plus"></i>
                    Registrarse
                </button>
            </form>
            <?php endif; ?>
            
            <div class="text-center mt-3">
                <p>¿Ya tienes cuenta? <a href="login.php" class="link">Inicia sesión aquí</a></p>
            </div>
        </div>
    </div>
</body>
</html>

