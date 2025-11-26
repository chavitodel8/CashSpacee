<?php
require_once 'config/config.php';
require_once 'includes/investment.php';

requireLogin();

$investment_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$investment = getInvestmentType($investment_id);

if (!$investment) {
    header('Location: index.php');
    exit();
}

// Obtener información del usuario
$conn = getConnection();
$user_id = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT saldo_disponible, codigo_invitacion FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();

// Verificar si ya tiene una inversión de este tipo
$stmt = $conn->prepare("SELECT COUNT(*) as total FROM inversiones WHERE usuario_id = ? AND tipo_inversion_id = ? AND estado = 'activa'");
$stmt->bind_param("ii", $user_id, $investment_id);
$stmt->execute();
$inversion_count = $stmt->get_result()->fetch_assoc();
$stmt->close();
closeConnection($conn);

$puede_invertir = $user['saldo_disponible'] >= $investment['precio_inversion'] && $inversion_count['total'] < $investment['limite_inversion'];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($investment['nombre']); ?> - CashSpace</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .investment-layout {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 30px;
            align-items: start;
        }
        
        .investment-form-card {
            background: var(--white);
            border: 2px solid var(--primary-color);
            border-radius: 15px;
            padding: 30px;
            position: sticky;
            top: 100px;
        }
        
        @media (max-width: 768px) {
            .investment-layout {
                grid-template-columns: 1fr;
                gap: 20px;
            }
            
            .investment-form-card {
                position: static;
                top: auto;
                padding: 20px;
            }
            
            .investment-image-container {
                height: 250px !important;
                font-size: 80px !important;
            }
            
            .investment-title {
                font-size: 24px !important;
            }
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar">
        <div class="navbar-content">
            <a href="index.php" class="navbar-brand">
                <img src="assets/images/logo.png" alt="CashSpace" onerror="this.style.display='none'; this.nextElementSibling.style.display='block';">
                <span style="display:none; background: var(--gradient-primary); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text;">CashSpace</span>
            </a>
            <div class="navbar-user">
                <div class="user-info">
                    <div class="user-avatar">
                        <i class="fas fa-user"></i>
                    </div>
                    <div>
                        <div style="font-weight: 600;"><?php echo htmlspecialchars($_SESSION['user_telefono']); ?></div>
                        <div class="user-balance"><?php echo formatCurrency($user['saldo_disponible']); ?></div>
                    </div>
                </div>
                <a href="index.php" class="btn btn-outline">Volver</a>
            </div>
        </div>
    </nav>

    <!-- Contenedor principal -->
    <div class="container">
        <div class="card">
            <div class="investment-layout">
                <!-- Imagen y detalles -->
                <div>
                    <?php 
                    // Determinar la imagen según el nombre del plan
                    $imagen_path = null;
                    if ($investment['imagen'] && file_exists($investment['imagen'])) {
                        $imagen_path = $investment['imagen'];
                    } else {
                        $imagen_path = getInvestmentImagePath($investment['nombre']);
                    }
                    ?>
                    <div class="investment-image-container" style="width: 100%; height: 400px; background: var(--gradient-primary); border-radius: 15px; display: flex; align-items: center; justify-content: center; font-size: 128px; color: white; margin-bottom: 20px; overflow: hidden; position: relative;">
                        <?php if ($imagen_path && file_exists($imagen_path)): ?>
                            <img src="<?php echo htmlspecialchars($imagen_path); ?>" alt="<?php echo htmlspecialchars($investment['nombre']); ?>" style="width: 100%; height: 100%; object-fit: cover; border-radius: 15px;" loading="eager" decoding="async" onerror="this.style.display='none'; this.parentElement.innerHTML='💎';">
                        <?php else: ?>
                            💎
                        <?php endif; ?>
                    </div>
                    
                    <h1 class="investment-title" style="font-size: 32px; margin-bottom: 15px; color: var(--dark-color);">
                        <?php echo htmlspecialchars($investment['nombre']); ?>
                    </h1>
                    
                    <div style="background: #f3f4f6; padding: 20px; border-radius: 10px; margin-bottom: 20px;">
                        <h3 style="margin-bottom: 15px; color: var(--dark-color);">Detalles del Plan</h3>
                        <div style="display: grid; gap: 10px;">
                            <div style="display: flex; justify-content: space-between;">
                                <span style="color: #6b7280;">Inversión Requerida:</span>
                                <span style="font-weight: 600; font-size: 20px; color: var(--primary-color);">
                                    <?php echo formatCurrency($investment['precio_inversion']); ?>
                                </span>
                            </div>
                            <div style="display: flex; justify-content: space-between;">
                                <span style="color: #6b7280;">Ganancia Diaria:</span>
                                <span style="font-weight: 600; color: var(--secondary-color); font-size: 18px;">
                                    +<?php echo formatCurrency($investment['ganancia_diaria']); ?>
                                </span>
                            </div>
                            <div style="display: flex; justify-content: space-between;">
                                <span style="color: #6b7280;">Ganancia Mensual:</span>
                                <span style="font-weight: 600; color: var(--secondary-color); font-size: 18px;">
                                    <?php echo formatCurrency($investment['ganancia_mensual']); ?>
                                </span>
                            </div>
                            <div style="display: flex; justify-content: space-between;">
                                <span style="color: #6b7280;">Duración:</span>
                                <span style="font-weight: 600;"><?php echo $investment['duracion_dias']; ?> días</span>
                            </div>
                            <div style="display: flex; justify-content: space-between;">
                                <span style="color: #6b7280;">Límite:</span>
                                <span style="font-weight: 600;"><?php echo $investment['limite_inversion']; ?> inversión</span>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Formulario de inversión -->
                <div>
                    <div class="investment-form-card">
                        <h2 style="margin-bottom: 20px; color: var(--dark-color);">Realizar Inversión</h2>
                        
                        <?php if (!$puede_invertir): ?>
                            <?php if ($user['saldo_disponible'] < $investment['precio_inversion']): ?>
                                <div style="background: #fee2e2; color: #991b1b; padding: 15px; border-radius: 10px; margin-bottom: 20px;">
                                    <i class="fas fa-exclamation-circle"></i> Saldo insuficiente. Necesitas <?php echo formatCurrency($investment['precio_inversion'] - $user['saldo_disponible']); ?> más.
                                </div>
                                <a href="index.php" class="btn btn-primary">
                                    <i class="fas fa-wallet"></i> Recargar Saldo
                                </a>
                            <?php elseif ($inversion_count['total'] >= $investment['limite_inversion']): ?>
                                <div style="background: #fee2e2; color: #991b1b; padding: 15px; border-radius: 10px; margin-bottom: 20px;">
                                    <i class="fas fa-exclamation-circle"></i> Has alcanzado el límite de inversiones para este plan.
                                </div>
                            <?php endif; ?>
                        <?php else: ?>
                            <div style="margin-bottom: 20px;">
                                <div style="display: flex; justify-content: space-between; margin-bottom: 10px;">
                                    <span>Tu Saldo:</span>
                                    <strong style="color: var(--secondary-color);"><?php echo formatCurrency($user['saldo_disponible']); ?></strong>
                                </div>
                                <div style="display: flex; justify-content: space-between; margin-bottom: 10px;">
                                    <span>Inversión:</span>
                                    <strong style="color: var(--primary-color);"><?php echo formatCurrency($investment['precio_inversion']); ?></strong>
                                </div>
                                <hr style="margin: 15px 0; border: 1px solid #e5e7eb;">
                                <div style="display: flex; justify-content: space-between;">
                                    <span>Saldo Restante:</span>
                                    <strong><?php echo formatCurrency($user['saldo_disponible'] - $investment['precio_inversion']); ?></strong>
                                </div>
                            </div>
                            
                            <form onsubmit="invertir(<?php echo $investment_id; ?>, event)">
                                <button type="submit" class="btn btn-primary" style="width: 100%; font-size: 18px; padding: 15px;">
                                    <i class="fas fa-check-circle"></i> Invertir Ahora
                                </button>
                            </form>
                            
                            <div id="inversionMessage" style="margin-top: 15px;"></div>
                        <?php endif; ?>
                        
                        <div style="margin-top: 30px; padding-top: 20px; border-top: 1px solid #e5e7eb;">
                            <h3 style="font-size: 16px; margin-bottom: 15px; color: var(--dark-color);">¿Cómo funciona?</h3>
                            <ul style="list-style: none; padding: 0; display: grid; gap: 10px;">
                                <li style="display: flex; align-items: start; gap: 10px;">
                                    <i class="fas fa-check-circle" style="color: var(--secondary-color); margin-top: 5px;"></i>
                                    <span>Realiza tu inversión y comienza a generar ganancias diarias automáticamente.</span>
                                </li>
                                <li style="display: flex; align-items: start; gap: 10px;">
                                    <i class="fas fa-check-circle" style="color: var(--secondary-color); margin-top: 5px;"></i>
                                    <span>Recibe tus ganancias diarias directamente en tu saldo disponible.</span>
                                </li>
                                <li style="display: flex; align-items: start; gap: 10px;">
                                    <i class="fas fa-check-circle" style="color: var(--secondary-color); margin-top: 5px;"></i>
                                    <span>Al finalizar el período, habrás ganado casi el triple de tu inversión inicial.</span>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Descripción detallada -->
            <div style="margin-top: 30px; padding-top: 30px; border-top: 2px solid #e5e7eb;">
                <h2 style="margin-bottom: 15px; color: var(--dark-color);">Descripción</h2>
                <p style="color: #6b7280; line-height: 1.8; font-size: 16px;">
                    <?php echo nl2br(htmlspecialchars($investment['descripcion'] ?: 'Plan de inversión diseñado para generar ganancias diarias consistentes. Invierte con confianza y comienza a ver resultados desde el primer día.')); ?>
                </p>
            </div>
        </div>
    </div>

    <script src="js/main.js"></script>
</body>
</html>

