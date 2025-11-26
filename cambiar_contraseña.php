<?php
require_once 'config/config.php';
requireLogin();

$user_id = $_SESSION['user_id'];
$error = '';
$success = '';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cambiar Contraseña - CashSpace</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            background: var(--light-color);
            padding: 0;
            margin: 0;
        }
        
        .password-change-container {
            max-width: 500px;
            margin: 0 auto;
            padding: 20px;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }
        
        .password-header {
            display: flex;
            align-items: center;
            gap: 15px;
            margin-bottom: 30px;
            padding-top: 20px;
        }
        
        .back-button {
            width: 40px;
            height: 40px;
            border-radius: 10px;
            background: var(--white);
            border: 1px solid var(--gray-light);
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            text-decoration: none;
            color: var(--dark-color);
            transition: all 0.2s;
            box-shadow: var(--shadow);
        }
        
        .back-button:hover {
            background: var(--gray-light);
            transform: translateX(-2px);
        }
        
        .password-title {
            font-size: 24px;
            font-weight: 700;
            color: var(--dark-color);
            margin: 0;
        }
        
        .password-form {
            flex: 1;
            display: flex;
            flex-direction: column;
        }
        
        .form-group-password {
            margin-bottom: 20px;
        }
        
        .form-label-password {
            display: block;
            font-size: 14px;
            font-weight: 600;
            color: var(--dark-color);
            margin-bottom: 8px;
        }
        
        .form-input-password {
            width: 100%;
            padding: 14px 16px;
            border: 1px solid var(--gray-light);
            border-radius: 12px;
            font-size: 16px;
            background: var(--white);
            color: var(--dark-color);
            transition: all 0.2s;
            box-sizing: border-box;
        }
        
        .form-input-password:focus {
            outline: none;
            border-color: var(--blue-primary);
            box-shadow: 0 0 0 3px rgba(4, 73, 144, 0.1);
        }
        
        .form-input-password::placeholder {
            color: #9ca3af;
        }
        
        .error-message {
            background: #fee2e2;
            color: #991b1b;
            padding: 12px 16px;
            border-radius: 10px;
            margin-bottom: 20px;
            font-size: 14px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .success-message {
            background: #d1fae5;
            color: #065f46;
            padding: 12px 16px;
            border-radius: 10px;
            margin-bottom: 20px;
            font-size: 14px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .submit-button {
            width: 100%;
            padding: 16px;
            background: var(--blue-primary);
            color: white;
            border: none;
            border-radius: 12px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
            margin-top: auto;
            box-shadow: 0 4px 12px rgba(4, 73, 144, 0.3);
        }
        
        .submit-button:hover {
            background: #033a73;
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(4, 73, 144, 0.4);
        }
        
        .submit-button:active {
            transform: translateY(0);
        }
        
        .submit-button:disabled {
            background: #9ca3af;
            cursor: not-allowed;
            transform: none;
        }
        
        @media (max-width: 768px) {
            .password-change-container {
                padding: 15px;
            }
            
            .password-header {
                margin-bottom: 25px;
                padding-top: 15px;
            }
            
            .password-title {
                font-size: 20px;
            }
            
            .back-button {
                width: 36px;
                height: 36px;
            }
            
            .form-group-password {
                margin-bottom: 18px;
            }
            
            .form-label-password {
                font-size: 13px;
            }
            
            .form-input-password {
                padding: 12px 14px;
                font-size: 15px;
            }
            
            .submit-button {
                padding: 14px;
                font-size: 15px;
            }
        }
    </style>
</head>
<body>
    <div class="password-change-container">
        <div class="password-header">
            <a href="mio.php" class="back-button">
                <i class="fas fa-arrow-left"></i>
            </a>
            <h1 class="password-title">Cambiar contraseña</h1>
        </div>
        
        <?php if ($error): ?>
            <div class="error-message">
                <i class="fas fa-exclamation-circle"></i>
                <span><?php echo htmlspecialchars($error); ?></span>
            </div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="success-message">
                <i class="fas fa-check-circle"></i>
                <span><?php echo htmlspecialchars($success); ?></span>
            </div>
        <?php endif; ?>
        
        <form class="password-form" id="passwordForm" method="POST" action="api/cambiar_contraseña.php">
            <div class="form-group-password">
                <label class="form-label-password" for="password_actual">Contraseña anterior</label>
                <input 
                    type="password" 
                    id="password_actual" 
                    name="password_actual" 
                    class="form-input-password" 
                    placeholder="Por favor ingrese la contraseña anterior" 
                    required
                >
            </div>
            
            <div class="form-group-password">
                <label class="form-label-password" for="password_nueva">Nueva contraseña</label>
                <input 
                    type="password" 
                    id="password_nueva" 
                    name="password_nueva" 
                    class="form-input-password" 
                    placeholder="Por favor ingrese una nueva contraseña" 
                    required
                    minlength="6"
                >
            </div>
            
            <div class="form-group-password">
                <label class="form-label-password" for="password_confirmar">Nueva contraseña otra vez</label>
                <input 
                    type="password" 
                    id="password_confirmar" 
                    name="password_confirmar" 
                    class="form-input-password" 
                    placeholder="Por favor vuelva a ingresar la nueva contraseña" 
                    required
                    minlength="6"
                >
            </div>
            
            <button type="submit" class="submit-button" id="submitBtn">
                Confirmar el cambio
            </button>
        </form>
    </div>
    
    <script>
        const form = document.getElementById('passwordForm');
        const submitBtn = document.getElementById('submitBtn');
        
        form.addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const passwordActual = document.getElementById('password_actual').value;
            const passwordNueva = document.getElementById('password_nueva').value;
            const passwordConfirmar = document.getElementById('password_confirmar').value;
            
            // Validaciones
            if (passwordNueva.length < 6) {
                showError('La nueva contraseña debe tener al menos 6 caracteres');
                return;
            }
            
            if (passwordNueva !== passwordConfirmar) {
                showError('Las contraseñas nuevas no coinciden');
                return;
            }
            
            if (passwordActual === passwordNueva) {
                showError('La nueva contraseña debe ser diferente a la actual');
                return;
            }
            
            // Deshabilitar botón
            submitBtn.disabled = true;
            submitBtn.textContent = 'Procesando...';
            
            // Enviar datos
            const formData = new FormData(form);
            
            try {
                const response = await fetch('api/cambiar_contraseña.php', {
                    method: 'POST',
                    body: formData
                });
                
                const data = await response.json();
                
                if (data.success) {
                    showSuccess(data.message || 'Contraseña cambiada exitosamente');
                    form.reset();
                    
                    // Redirigir después de 2 segundos
                    setTimeout(() => {
                        window.location.href = 'mio.php';
                    }, 2000);
                } else {
                    showError(data.message || 'Error al cambiar la contraseña');
                    submitBtn.disabled = false;
                    submitBtn.textContent = 'Confirmar el cambio';
                }
            } catch (error) {
                showError('Error de conexión. Por favor intenta nuevamente.');
                submitBtn.disabled = false;
                submitBtn.textContent = 'Confirmar el cambio';
            }
        });
        
        function showError(message) {
            const errorDiv = document.createElement('div');
            errorDiv.className = 'error-message';
            errorDiv.innerHTML = '<i class="fas fa-exclamation-circle"></i><span>' + message + '</span>';
            
            const form = document.getElementById('passwordForm');
            const existingError = form.parentElement.querySelector('.error-message');
            if (existingError) {
                existingError.remove();
            }
            
            form.parentElement.insertBefore(errorDiv, form);
            
            // Scroll al error
            errorDiv.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        }
        
        function showSuccess(message) {
            const successDiv = document.createElement('div');
            successDiv.className = 'success-message';
            successDiv.innerHTML = '<i class="fas fa-check-circle"></i><span>' + message + '</span>';
            
            const form = document.getElementById('passwordForm');
            const existingSuccess = form.parentElement.querySelector('.success-message');
            const existingError = form.parentElement.querySelector('.error-message');
            
            if (existingSuccess) {
                existingSuccess.remove();
            }
            if (existingError) {
                existingError.remove();
            }
            
            form.parentElement.insertBefore(successDiv, form);
            
            // Scroll al mensaje
            successDiv.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        }
    </script>
</body>
</html>

