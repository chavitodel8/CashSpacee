<?php
require_once 'config/config.php';
requireLogin();

$conn = getConnection();

// Obtener las inversiones más populares (las 4 primeras)
$stmt = $conn->prepare("SELECT * FROM tipos_inversion WHERE estado = 'activo' ORDER BY precio_inversion ASC LIMIT 4");
$stmt->execute();
$inversiones = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

closeConnection($conn);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Acerca de Nosotros - CashSpace</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            background: var(--light-color);
            padding: 0;
            margin: 0;
        }
        
        .about-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0;
            min-height: 100vh;
        }
        
        .about-header {
            background: var(--white);
            padding: 15px 20px;
            display: flex;
            align-items: center;
            gap: 15px;
            border-bottom: 1px solid var(--gray-light);
            position: sticky;
            top: 0;
            z-index: 100;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
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
        }
        
        .back-button:hover {
            background: var(--gray-light);
            transform: translateX(-2px);
        }
        
        .about-title {
            font-size: 20px;
            font-weight: 700;
            color: var(--dark-color);
            margin: 0;
        }
        
        .company-section {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            padding: 40px 20px;
            position: relative;
            overflow: hidden;
        }
        
        .company-section::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-image: url('data:image/svg+xml,<svg width="100" height="100" xmlns="http://www.w3.org/2000/svg"><circle cx="50" cy="50" r="1" fill="%23d1d5db" opacity="0.3"/></svg>');
            background-size: 50px 50px;
            opacity: 0.3;
        }
        
        .company-content {
            max-width: 800px;
            margin: 0 auto;
            position: relative;
            z-index: 1;
            text-align: center;
        }
        
        .company-logo {
            margin-bottom: 20px;
        }
        
        .company-logo img {
            width: 120px;
            height: 120px;
            object-fit: contain;
            filter: drop-shadow(0 4px 12px rgba(4, 73, 144, 0.2));
        }
        
        .company-year {
            font-size: 18px;
            color: var(--blue-primary);
            font-weight: 600;
            margin-bottom: 10px;
        }
        
        .company-section-title {
            font-size: 32px;
            font-weight: 700;
            color: var(--blue-primary);
            margin-bottom: 30px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        
        .company-description {
            text-align: left;
            font-size: 16px;
            line-height: 1.8;
            color: var(--dark-color);
            background: var(--white);
            padding: 30px;
            border-radius: 16px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.08);
        }
        
        .company-description p {
            margin-bottom: 20px;
        }
        
        .company-description p:last-child {
            margin-bottom: 0;
        }
        
        .highlight {
            color: var(--blue-primary);
            font-weight: 600;
        }
        
        .investments-section {
            padding: 50px 20px;
            background: var(--white);
        }
        
        .investments-title {
            font-size: 24px;
            font-weight: 700;
            color: var(--dark-color);
            margin-bottom: 30px;
            text-align: center;
            text-transform: uppercase;
        }
        
        .investments-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 25px;
            max-width: 1000px;
            margin: 0 auto;
        }
        
        .investment-card {
            background: var(--white);
            border-radius: 16px;
            padding: 25px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.08);
            border: 1px solid var(--gray-light);
            transition: all 0.3s;
            text-align: center;
        }
        
        .investment-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 30px rgba(4, 73, 144, 0.15);
            border-color: var(--blue-primary);
        }
        
        .investment-icon {
            font-size: 48px;
            margin-bottom: 15px;
        }
        
        .investment-name {
            font-size: 20px;
            font-weight: 700;
            color: var(--dark-color);
            margin-bottom: 15px;
        }
        
        .investment-details {
            text-align: left;
            margin-top: 20px;
        }
        
        .investment-detail-item {
            display: flex;
            justify-content: space-between;
            padding: 10px 0;
            border-bottom: 1px solid var(--gray-light);
        }
        
        .investment-detail-item:last-child {
            border-bottom: none;
        }
        
        .investment-detail-label {
            font-size: 14px;
            color: #6b7280;
        }
        
        .investment-detail-value {
            font-size: 16px;
            font-weight: 600;
            color: var(--blue-primary);
        }
        
        .logistics-section {
            padding: 50px 20px;
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
        }
        
        .logistics-content {
            max-width: 1000px;
            margin: 0 auto;
        }
        
        .logistics-card {
            background: var(--white);
            border-radius: 16px;
            padding: 30px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.08);
            margin-bottom: 25px;
        }
        
        .logistics-card:last-child {
            margin-bottom: 0;
        }
        
        .logistics-icon {
            font-size: 40px;
            color: var(--blue-primary);
            margin-bottom: 20px;
        }
        
        .logistics-title {
            font-size: 22px;
            font-weight: 700;
            color: var(--dark-color);
            margin-bottom: 15px;
            text-transform: uppercase;
        }
        
        .logistics-text {
            font-size: 16px;
            line-height: 1.8;
            color: var(--dark-color);
        }
        
        @media (max-width: 768px) {
            .about-header {
                padding: 12px 15px;
            }
            
            .about-title {
                font-size: 18px;
            }
            
            .company-section {
                padding: 30px 15px;
            }
            
            .company-logo img {
                width: 100px;
                height: 100px;
            }
            
            .company-section-title {
                font-size: 24px;
            }
            
            .company-description {
                padding: 20px;
                font-size: 15px;
            }
            
            .investments-section {
                padding: 40px 15px;
            }
            
            .investments-title {
                font-size: 20px;
            }
            
            .investments-grid {
                grid-template-columns: 1fr;
                gap: 20px;
            }
            
            .investment-card {
                padding: 20px;
            }
            
            .logistics-section {
                padding: 40px 15px;
            }
            
            .logistics-card {
                padding: 20px;
            }
            
            .logistics-title {
                font-size: 18px;
            }
            
            .logistics-text {
                font-size: 15px;
            }
        }
    </style>
</head>
<body>
    <div class="about-container">
        <!-- Header -->
        <div class="about-header">
            <a href="mio.php" class="back-button">
                <i class="fas fa-arrow-left"></i>
            </a>
            <h1 class="about-title">Acerca de nosotros</h1>
        </div>
        
        <!-- Sección de la Empresa -->
        <div class="company-section">
            <div class="company-content">
                <div class="company-logo">
                    <img src="assets/images/logo.png" alt="CashSpace" onerror="this.style.display='none';">
                </div>
                <div class="company-year">- 2025 -</div>
                <h2 class="company-section-title">Acerca de CashSpace</h2>
                <div class="company-description">
                    <p>
                        CashSpace es una empresa de inversión y gestión financiera con sede en <span class="highlight">Zúrich, Suiza</span>, especializada en ofrecer oportunidades de inversión accesibles y rentables. Nuestra filosofía empresarial se basa en tres pilares fundamentales: <span class="highlight">la integridad, la eficiencia y el beneficio mutuo</span>.
                    </p>
                    <p>
                        Desde nuestros inicios, hemos crecido de ser una plataforma regional a una empresa moderna que ofrece múltiples niveles de inversión adaptados a diferentes perfiles de inversionistas. Nuestro compromiso es ofrecer transparencia total, seguridad en cada transacción y rendimientos justos para todos nuestros usuarios.
                    </p>
                    <p>
                        Con años de experiencia en el mercado financiero suizo y una red global de socios estratégicos, CashSpace ha establecido un sistema robusto de gestión de inversiones que garantiza la estabilidad y el crecimiento sostenible de las inversiones de nuestros clientes.
                    </p>
                </div>
            </div>
        </div>
        
        <!-- Sección de Inversiones -->
        <div class="investments-section">
            <h2 class="investments-title">
                Nuestras Inversiones Más Populares
            </h2>
            <div class="investments-grid">
                <?php foreach ($inversiones as $inversion): ?>
                    <div class="investment-card">
                        <div class="investment-icon">
                            💎
                        </div>
                        <div class="investment-name"><?php echo htmlspecialchars($inversion['nombre']); ?></div>
                        <div class="investment-details">
                            <div class="investment-detail-item">
                                <span class="investment-detail-label">Inversión:</span>
                                <span class="investment-detail-value"><?php echo formatCurrency($inversion['precio_inversion']); ?></span>
                            </div>
                            <div class="investment-detail-item">
                                <span class="investment-detail-label">Ganancia Diaria:</span>
                                <span class="investment-detail-value"><?php echo formatCurrency($inversion['ganancia_diaria']); ?></span>
                            </div>
                            <div class="investment-detail-item">
                                <span class="investment-detail-label">Ganancia Mensual:</span>
                                <span class="investment-detail-value"><?php echo formatCurrency($inversion['ganancia_mensual']); ?></span>
                            </div>
                            <div class="investment-detail-item">
                                <span class="investment-detail-label">Duración:</span>
                                <span class="investment-detail-value"><?php echo $inversion['duracion_dias']; ?> días</span>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        
        <!-- Sección de Logística y Operaciones -->
        <div class="logistics-section">
            <div class="logistics-content">
                <div class="logistics-card">
                    <div class="logistics-icon">
                        <i class="fas fa-ship"></i>
                    </div>
                    <h3 class="logistics-title">Diversificación de Inversiones</h3>
                    <p class="logistics-text">
                        CashSpace ofrece múltiples niveles de inversión diseñados para adaptarse a diferentes perfiles de inversionistas, desde principiantes hasta profesionales. Nuestro equipo de expertos selecciona cuidadosamente cada oportunidad de inversión para garantizar rentabilidad y seguridad. Contamos con presencia en Suiza, Europa y mercados internacionales, asegurando la diversificación geográfica y sectorial de las inversiones.
                    </p>
                </div>
                
                <div class="logistics-card">
                    <div class="logistics-icon">
                        <i class="fas fa-shield-alt"></i>
                    </div>
                    <h3 class="logistics-title">Transparencia y Seguridad</h3>
                    <p class="logistics-text">
                        Todas nuestras inversiones están respaldadas por activos reales y documentación legal completa. Aplicamos sistemáticamente procedimientos conformes con las regulaciones financieras internacionales. Cada proyecto es auditado y verificado antes de ser incluido en nuestro portafolio. Hemos establecido fuertes relaciones de cooperación con autoridades regulatorias y entidades financieras en muchos países para garantizar un manejo seguro y eficiente de los fondos de inversión, cumpliendo con los más altos estándares de transparencia y seguridad.
                    </p>
                </div>
            </div>
        </div>
    </div>
</body>
</html>

