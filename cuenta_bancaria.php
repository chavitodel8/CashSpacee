<?php
require_once 'config/config.php';
requireLogin();

$conn = getConnection();
$user_id = $_SESSION['user_id'];

// Obtener información del usuario
$stmt = $conn->prepare("SELECT id, telefono, nombre FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();

// Obtener información bancaria del usuario (si existe)
$stmt = $conn->prepare("SELECT nombre_titular, numero_cuenta, tipo_cuenta, banco FROM cuenta_bancaria WHERE usuario_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$bank_info = $result->fetch_assoc();
$stmt->close();

// Si no existe información bancaria, dejar vacío
$nombre_titular = $bank_info['nombre_titular'] ?? '';
$cuenta_bancaria = $bank_info['numero_cuenta'] ?? '';
$tipo_cartera = $bank_info['tipo_cuenta'] ?? null;
$banco = $bank_info['banco'] ?? null;

closeConnection($conn);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cuenta Bancaria - CashSpace</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #f5f7fa 0%, #e8ecf1 100%);
            min-height: 100vh;
            padding-bottom: 80px;
        }
        
        .page-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 20px;
            color: white;
            display: flex;
            align-items: center;
            gap: 15px;
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
        }
        
        .back-button {
            width: 40px;
            height: 40px;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            text-decoration: none;
            color: white;
            transition: all 0.3s;
            backdrop-filter: blur(10px);
        }
        
        .back-button:hover {
            background: rgba(255, 255, 255, 0.3);
            transform: scale(1.1);
        }
        
        .page-title {
            font-size: 22px;
            font-weight: 700;
            flex: 1;
        }
        
        .main-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 24px;
            margin: 20px;
            padding: 30px;
            color: white;
            box-shadow: 0 15px 40px rgba(102, 126, 234, 0.4);
            position: relative;
            overflow: hidden;
        }
        
        .main-card::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -20%;
            width: 300px;
            height: 300px;
            background: radial-gradient(circle, rgba(255,255,255,0.15) 0%, transparent 70%);
            border-radius: 50%;
        }
        
        .main-card-content {
            position: relative;
            z-index: 2;
            display: grid;
            grid-template-columns: 1fr auto;
            gap: 30px;
            align-items: center;
        }
        
        .account-info {
            flex: 1;
        }
        
        .info-label {
            font-size: 14px;
            opacity: 0.9;
            margin-bottom: 8px;
            font-weight: 500;
        }
        
        .info-value {
            font-size: 32px;
            font-weight: 700;
            margin-bottom: 25px;
            text-shadow: 0 2px 10px rgba(0, 0, 0, 0.2);
        }
        
        .account-number {
            font-size: 28px;
            font-weight: 700;
            letter-spacing: 2px;
            font-family: 'Courier New', monospace;
            text-shadow: 0 2px 10px rgba(0, 0, 0, 0.2);
        }
        
        .illustration {
            width: 200px;
            height: 200px;
            background: rgba(255, 255, 255, 0.15);
            border-radius: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            backdrop-filter: blur(10px);
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.2);
        }
        
        .illustration i {
            font-size: 100px;
            opacity: 0.8;
        }
        
        .details-card {
            background: white;
            border-radius: 20px;
            margin: 0 20px 20px 20px;
            padding: 25px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.08);
        }
        
        .details-title {
            font-size: 20px;
            font-weight: 700;
            color: #1f2937;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .details-title i {
            color: #667eea;
        }
        
        .detail-item {
            padding: 18px 0;
            border-bottom: 1px solid #e5e7eb;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .detail-item:last-child {
            border-bottom: none;
        }
        
        .detail-label {
            font-size: 14px;
            color: #6b7280;
            font-weight: 500;
        }
        
        .detail-value {
            font-size: 16px;
            color: #1f2937;
            font-weight: 600;
        }
        
        .instruction-text {
            background: #fef3c7;
            border-left: 4px solid #f59e0b;
            border-radius: 8px;
            padding: 15px 20px;
            margin: 20px;
            color: #92400e;
            font-size: 14px;
            line-height: 1.6;
        }
        
        .instruction-text i {
            margin-right: 8px;
            color: #f59e0b;
        }
        
        .modify-button {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 16px;
            padding: 18px 40px;
            font-size: 18px;
            font-weight: 700;
            cursor: pointer;
            width: calc(100% - 40px);
            margin: 0 20px 20px 20px;
            box-shadow: 0 8px 25px rgba(102, 126, 234, 0.4);
            transition: all 0.3s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }
        
        .modify-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 35px rgba(102, 126, 234, 0.5);
        }
        
        .modify-button:active {
            transform: translateY(0);
        }
        
        /* Modal de modificación */
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 1000;
            backdrop-filter: blur(5px);
        }
        
        .modal.active {
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .modal-content {
            background: white;
            border-radius: 24px;
            padding: 30px;
            width: 90%;
            max-width: 500px;
            max-height: 90vh;
            overflow-y: auto;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
        }
        
        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
        }
        
        .modal-title {
            font-size: 24px;
            font-weight: 700;
            color: #1f2937;
        }
        
        .close-modal {
            width: 36px;
            height: 36px;
            background: #f3f4f6;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            color: #6b7280;
            transition: all 0.3s;
        }
        
        .close-modal:hover {
            background: #e5e7eb;
            color: #1f2937;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-label {
            display: block;
            font-size: 14px;
            font-weight: 600;
            color: #374151;
            margin-bottom: 8px;
        }
        
        .form-control {
            width: 100%;
            padding: 14px 16px;
            border: 2px solid #e5e7eb;
            border-radius: 12px;
            font-size: 16px;
            transition: all 0.3s;
            box-sizing: border-box;
        }
        
        .form-control:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }
        
        .form-select {
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 12 12'%3E%3Cpath fill='%23374151' d='M6 9L1 4h10z'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 16px center;
            padding-right: 40px;
            appearance: none;
        }
        
        .submit-button {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 12px;
            padding: 16px;
            font-size: 16px;
            font-weight: 700;
            cursor: pointer;
            width: 100%;
            margin-top: 10px;
            transition: all 0.3s;
        }
        
        .submit-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(102, 126, 234, 0.4);
        }
        
        .submit-button:disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            .main-card {
                margin: 15px;
                padding: 25px;
            }
            
            .main-card-content {
                grid-template-columns: 1fr;
                gap: 20px;
            }
            
            .illustration {
                width: 150px;
                height: 150px;
                margin: 0 auto;
            }
            
            .illustration i {
                font-size: 80px;
            }
            
            .info-value {
                font-size: 28px;
            }
            
            .account-number {
                font-size: 24px;
            }
            
            .details-card {
                margin: 0 15px 15px 15px;
                padding: 20px;
            }
            
            .instruction-text {
                margin: 15px;
                padding: 12px 18px;
            }
            
            .modify-button {
                margin: 0 15px 15px 15px;
            }
        }
        
        @media (max-width: 480px) {
            .page-header {
                padding: 15px;
            }
            
            .page-title {
                font-size: 18px;
            }
            
            .main-card {
                margin: 12px;
                padding: 20px;
                border-radius: 20px;
            }
            
            .info-value {
                font-size: 24px;
            }
            
            .account-number {
                font-size: 20px;
            }
            
            .illustration {
                width: 120px;
                height: 120px;
            }
            
            .illustration i {
                font-size: 60px;
            }
            
            .details-card {
                margin: 0 12px 12px 12px;
                padding: 18px;
            }
            
            .modal-content {
                padding: 20px;
                border-radius: 20px;
            }
        }
    </style>
</head>
<body>
    <!-- Header -->
    <div class="page-header">
        <a href="mio.php" class="back-button">
            <i class="fas fa-arrow-left"></i>
        </a>
        <div class="page-title">Cuenta bancaria</div>
    </div>

    <!-- Tarjeta principal -->
    <div class="main-card">
        <div class="main-card-content">
            <div class="account-info">
                <div class="info-label">Nombre del titular</div>
                <div class="info-value"><?php echo htmlspecialchars($nombre_titular ?: 'No configurado'); ?></div>
                <div class="info-label">Cuenta bancaria</div>
                <div class="account-number"><?php echo htmlspecialchars($cuenta_bancaria ?: 'No configurado'); ?></div>
            </div>
            <div class="illustration">
                <i class="fas fa-university"></i>
            </div>
        </div>
    </div>

    <!-- Detalles de la cuenta -->
    <div class="details-card">
        <div class="details-title">
            <i class="fas fa-info-circle"></i>
            Información de la cuenta
        </div>
        <div class="detail-item">
            <span class="detail-label">Nombre del titular</span>
            <span class="detail-value"><?php echo htmlspecialchars($nombre_titular ?: 'No configurado'); ?></span>
        </div>
        <div class="detail-item">
            <span class="detail-label">Cuenta de billetera</span>
            <span class="detail-value"><?php echo htmlspecialchars($cuenta_bancaria ?: 'No configurado'); ?></span>
        </div>
        <div class="detail-item">
            <span class="detail-label">Tipo de cartera</span>
            <span class="detail-value"><?php echo htmlspecialchars($tipo_cartera ?: 'No configurado'); ?></span>
        </div>
    </div>

    <!-- Texto instructivo -->
    <div class="instruction-text">
        <i class="fas fa-exclamation-circle"></i>
        Verifique que los datos de su cuenta de retiro estén correctos antes de continuar.
    </div>

    <!-- Botón modificar -->
    <button class="modify-button" onclick="openModal()">
        <i class="fas fa-edit"></i>
        Modificar
    </button>

    <!-- Modal de modificación -->
    <div class="modal" id="modifyModal">
        <div class="modal-content">
            <div class="modal-header">
                <div class="modal-title">Modificar cuenta bancaria</div>
                <div class="close-modal" onclick="closeModal()">
                    <i class="fas fa-times"></i>
                </div>
            </div>
            <form id="modifyForm" onsubmit="submitForm(event)">
                <div class="form-group">
                    <label class="form-label">Nombre del titular</label>
                    <input type="text" class="form-control" name="nombre_titular" 
                           value="<?php echo htmlspecialchars($nombre_titular); ?>" 
                           placeholder="Ingrese el nombre completo" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Cuenta de billetera</label>
                    <input type="text" class="form-control" name="cuenta_bancaria" 
                           value="<?php echo htmlspecialchars($cuenta_bancaria); ?>" 
                           placeholder="Ingrese el número de cuenta" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Tipo de cartera</label>
                    <select class="form-control form-select" name="tipo_cartera" required>
                        <option value="" <?php echo empty($tipo_cartera) ? 'selected' : ''; ?>>Seleccione una opción</option>
                        <option value="Yape" <?php echo $tipo_cartera === 'Yape' ? 'selected' : ''; ?>>Yape</option>
                        <option value="Yasta" disabled>Yasta (Próximamente)</option>
                        <option value="BCP" disabled>BCP (Próximamente)</option>
                    </select>
                </div>
                <button type="submit" class="submit-button">
                    <i class="fas fa-save"></i> Guardar cambios
                </button>
            </form>
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

    <script>
        function openModal() {
            document.getElementById('modifyModal').classList.add('active');
            document.body.style.overflow = 'hidden';
        }
        
        function closeModal() {
            document.getElementById('modifyModal').classList.remove('active');
            document.body.style.overflow = 'auto';
        }
        
        // Cerrar modal al hacer clic fuera
        document.getElementById('modifyModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeModal();
            }
        });
        
        async function submitForm(event) {
            event.preventDefault();
            
            const form = event.target;
            const formData = new FormData(form);
            const submitButton = form.querySelector('.submit-button');
            const originalText = submitButton.innerHTML;
            
            // Deshabilitar botón
            submitButton.disabled = true;
            submitButton.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Guardando...';
            
            try {
                const response = await fetch('api/actualizar_cuenta_bancaria.php', {
                    method: 'POST',
                    body: formData
                });
                
                const data = await response.json();
                
                if (data.success) {
                    alert('Cuenta bancaria actualizada exitosamente');
                    location.reload();
                } else {
                    alert('Error: ' + (data.message || 'No se pudo actualizar la cuenta bancaria'));
                    submitButton.disabled = false;
                    submitButton.innerHTML = originalText;
                }
            } catch (error) {
                console.error('Error:', error);
                alert('Error al procesar la solicitud. Intente nuevamente.');
                submitButton.disabled = false;
                submitButton.innerHTML = originalText;
            }
        }
    </script>
</body>
</html>

