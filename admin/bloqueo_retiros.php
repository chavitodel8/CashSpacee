<?php
require_once __DIR__ . '/../config/config.php';
requireAdmin();

$conn = getConnection();
$admin_id = $_SESSION['user_id'];

// Obtener estado actual del bloqueo
$result = $conn->query("SELECT * FROM bloqueo_retiros ORDER BY fecha_creacion DESC LIMIT 1");
$bloqueo_actual = $result->fetch_assoc();

// Si no existe registro, crear uno por defecto
if (!$bloqueo_actual) {
    $stmt = $conn->prepare("INSERT INTO bloqueo_retiros (activo, admin_id) VALUES (0, ?)");
    $stmt->bind_param("i", $admin_id);
    $stmt->execute();
    $stmt->close();
    
    $result = $conn->query("SELECT * FROM bloqueo_retiros ORDER BY fecha_creacion DESC LIMIT 1");
    $bloqueo_actual = $result->fetch_assoc();
}

closeConnection($conn);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bloquear Retiros - CashSpace Admin</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .bloqueo-container {
            max-width: 800px;
            margin: 0 auto;
        }
        .status-card {
            background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);
            border-radius: 16px;
            padding: 30px;
            margin-bottom: 30px;
            border: 2px solid;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }
        .status-card.activo {
            border-color: #ef4444;
            background: linear-gradient(135deg, #fee2e2 0%, #ffffff 100%);
        }
        .status-card.inactivo {
            border-color: #10b981;
            background: linear-gradient(135deg, #d1fae5 0%, #ffffff 100%);
        }
        .status-badge {
            display: inline-block;
            padding: 8px 16px;
            border-radius: 20px;
            font-weight: 600;
            font-size: 14px;
            margin-bottom: 15px;
        }
        .status-badge.activo {
            background: #ef4444;
            color: white;
        }
        .status-badge.inactivo {
            background: #10b981;
            color: white;
        }
        .form-section {
            background: white;
            border-radius: 16px;
            padding: 30px;
            margin-bottom: 20px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        }
        .form-group {
            margin-bottom: 20px;
        }
        .form-label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: var(--dark-color);
        }
        .form-control {
            width: 100%;
            padding: 12px;
            border: 2px solid #e5e7eb;
            border-radius: 10px;
            font-size: 16px;
            transition: all 0.3s ease;
        }
        .form-control:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(4, 73, 144, 0.1);
        }
        .radio-group {
            display: flex;
            gap: 20px;
            flex-wrap: wrap;
        }
        .radio-option {
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .radio-option input[type="radio"] {
            width: 20px;
            height: 20px;
            cursor: pointer;
        }
        .btn-toggle {
            width: 100%;
            padding: 15px;
            font-size: 18px;
            font-weight: 600;
            border: none;
            border-radius: 12px;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-top: 20px;
        }
        .btn-toggle.activar {
            background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
            color: white;
        }
        .btn-toggle.desactivar {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: white;
        }
        .btn-toggle:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
        }
        .info-box {
            background: #f0f9ff;
            border-left: 4px solid #3b82f6;
            border-radius: 8px;
            padding: 15px;
            margin-top: 15px;
        }
        .info-box p {
            margin: 0;
            color: #0369a1;
            font-size: 14px;
        }
    </style>
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
                <a href="index.php" class="btn btn-outline">Volver al Panel</a>
                <a href="../logout.php" class="btn btn-danger">Salir</a>
            </div>
        </div>
    </nav>

    <!-- Contenedor principal -->
    <div class="container">
        <div class="bloqueo-container">
            <h1 style="margin-bottom: 30px; color: var(--dark-color);">
                <i class="fas fa-ban"></i> Bloquear Retiros
            </h1>

            <!-- Estado actual -->
            <div class="status-card <?php echo $bloqueo_actual['activo'] ? 'activo' : 'inactivo'; ?>">
                <span class="status-badge <?php echo $bloqueo_actual['activo'] ? 'activo' : 'inactivo'; ?>">
                    <?php echo $bloqueo_actual['activo'] ? 'RETIROS BLOQUEADOS' : 'RETIROS ACTIVOS'; ?>
                </span>
                <h2 style="margin: 10px 0; color: var(--dark-color);">
                    <?php echo $bloqueo_actual['activo'] ? 'Los retiros están actualmente bloqueados' : 'Los retiros están actualmente activos'; ?>
                </h2>
                <?php if ($bloqueo_actual['activo']): ?>
                    <?php if ($bloqueo_actual['indefinido']): ?>
                        <p style="color: #666; margin: 10px 0;"><strong>Duración:</strong> Indefinido</p>
                    <?php elseif ($bloqueo_actual['fecha_fin']): ?>
                        <p style="color: #666; margin: 10px 0;"><strong>Hasta:</strong> <?php echo date('d/m/Y H:i', strtotime($bloqueo_actual['fecha_fin'])); ?></p>
                    <?php endif; ?>
                    <?php if (!empty($bloqueo_actual['descripcion'])): ?>
                        <div class="info-box">
                            <p><strong>Mensaje para usuarios:</strong></p>
                            <p><?php echo nl2br(htmlspecialchars($bloqueo_actual['descripcion'])); ?></p>
                        </div>
                    <?php endif; ?>
                <?php endif; ?>
            </div>

            <!-- Formulario de configuración -->
            <div class="form-section">
                <h2 style="margin-bottom: 20px; color: var(--dark-color);">
                    <?php echo $bloqueo_actual['activo'] ? 'Desactivar Bloqueo' : 'Activar Bloqueo'; ?>
                </h2>

                <form id="bloqueoForm">
                    <input type="hidden" name="accion" value="<?php echo $bloqueo_actual['activo'] ? 'desactivar' : 'activar'; ?>">
                    
                    <?php if (!$bloqueo_actual['activo']): ?>
                        <div class="form-group">
                            <label class="form-label">Duración del Bloqueo</label>
                            <div class="radio-group">
                                <div class="radio-option">
                                    <input type="radio" name="tipo_duracion" value="horas" id="horas" checked>
                                    <label for="horas">Por Horas</label>
                                </div>
                                <div class="radio-option">
                                    <input type="radio" name="tipo_duracion" value="dias" id="dias">
                                    <label for="dias">Por Días</label>
                                </div>
                                <div class="radio-option">
                                    <input type="radio" name="tipo_duracion" value="indefinido" id="indefinido">
                                    <label for="indefinido">Indefinido</label>
                                </div>
                            </div>
                        </div>

                        <div class="form-group" id="duracionGroup">
                            <label class="form-label" id="duracionLabel">Cantidad de Horas</label>
                            <input type="number" name="duracion" class="form-control" id="duracionInput" min="1" value="24" required>
                        </div>
                    <?php endif; ?>

                    <div class="form-group">
                        <label class="form-label">Mensaje para Usuarios (Opcional)</label>
                        <textarea name="descripcion" class="form-control" rows="4" placeholder="Ejemplo: Los retiros están temporalmente deshabilitados por mantenimiento del sistema. Estarán disponibles nuevamente pronto."><?php echo htmlspecialchars($bloqueo_actual['descripcion'] ?? ''); ?></textarea>
                        <div class="info-box">
                            <p>Este mensaje se mostrará a los usuarios cuando intenten realizar un retiro mientras esté bloqueado.</p>
                        </div>
                    </div>

                    <button type="submit" class="btn-toggle <?php echo $bloqueo_actual['activo'] ? 'desactivar' : 'activar'; ?>">
                        <i class="fas fa-<?php echo $bloqueo_actual['activo'] ? 'check-circle' : 'ban'; ?>"></i>
                        <?php echo $bloqueo_actual['activo'] ? 'Desactivar Bloqueo' : 'Activar Bloqueo'; ?>
                    </button>
                </form>
            </div>
        </div>
    </div>

    <script>
        // Manejar cambio de tipo de duración
        document.querySelectorAll('input[name="tipo_duracion"]').forEach(radio => {
            radio.addEventListener('change', function() {
                const duracionGroup = document.getElementById('duracionGroup');
                const duracionLabel = document.getElementById('duracionLabel');
                const duracionInput = document.getElementById('duracionInput');
                
                if (this.value === 'indefinido') {
                    duracionGroup.style.display = 'none';
                    duracionInput.removeAttribute('required');
                } else {
                    duracionGroup.style.display = 'block';
                    duracionInput.setAttribute('required', 'required');
                    if (this.value === 'horas') {
                        duracionLabel.textContent = 'Cantidad de Horas';
                        duracionInput.min = 1;
                    } else {
                        duracionLabel.textContent = 'Cantidad de Días';
                        duracionInput.min = 1;
                    }
                }
            });
        });

        // Manejar envío del formulario
        document.getElementById('bloqueoForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const accion = formData.get('accion');
            const tipoDuracion = formData.get('tipo_duracion');
            const duracion = formData.get('duracion');
            const descripcion = formData.get('descripcion');
            
            // Validar duración si no es indefinido
            if (accion === 'activar' && tipoDuracion !== 'indefinido' && (!duracion || duracion < 1)) {
                alert('Por favor, ingrese una duración válida');
                return;
            }
            
            const data = {
                accion: accion,
                tipo_duracion: tipoDuracion,
                duracion: duracion,
                descripcion: descripcion
            };
            
            try {
                const response = await fetch('api/bloqueo_retiros.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(data)
                });
                
                const result = await response.json();
                
                if (result.success) {
                    alert(result.message);
                    window.location.reload();
                } else {
                    alert('Error: ' + result.message);
                }
            } catch (error) {
                console.error('Error:', error);
                alert('Error al procesar la solicitud');
            }
        });
    </script>
</body>
</html>

