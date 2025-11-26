<?php
require_once 'config/config.php';
requireLogin();

// Configuración de contacto (puedes actualizar estos valores)
$telegram_contact = 'https://t.me/cashspace_support'; // Actualizar con el enlace real
$whatsapp_contact = 'https://wa.me/1234567890'; // Actualizar con el número real
$telegram_channel = 'https://t.me/cashspace_channel'; // Actualizar con el enlace real
$whatsapp_channel = 'https://wa.me/channel1234567890'; // Actualizar con el enlace real
$horario_atencion = '09:00 AM - 21:00 PM';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Servicio al Cliente - CashSpace</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            background: var(--light-color);
            padding: 0;
            margin: 0;
        }
        
        .support-container {
            max-width: 600px;
            margin: 0 auto;
            padding: 0;
            min-height: 100vh;
            background: var(--white);
        }
        
        .support-header {
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
        
        .support-title {
            font-size: 20px;
            font-weight: 700;
            color: var(--dark-color);
            margin: 0;
        }
        
        .support-image {
            width: 100%;
            height: 250px;
            background: linear-gradient(135deg, var(--blue-primary) 0%, #033a73 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            overflow: hidden;
        }
        
        .support-image::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-image: url('data:image/svg+xml,<svg width="100" height="100" xmlns="http://www.w3.org/2000/svg"><circle cx="50" cy="50" r="2" fill="%23ffffff" opacity="0.1"/></svg>');
            background-size: 50px 50px;
        }
        
        .support-image-icon {
            font-size: 80px;
            color: rgba(255, 255, 255, 0.9);
            position: relative;
            z-index: 1;
        }
        
        .welcome-section {
            background: var(--blue-primary);
            padding: 30px 20px;
            color: white;
        }
        
        .welcome-title {
            font-size: 28px;
            font-weight: 700;
            font-style: italic;
            margin-bottom: 15px;
        }
        
        .welcome-text {
            font-size: 15px;
            line-height: 1.6;
            opacity: 0.95;
        }
        
        .contact-section {
            padding: 30px 20px;
            background: var(--white);
        }
        
        .section-title {
            font-size: 18px;
            font-weight: 700;
            color: var(--blue-primary);
            margin-bottom: 20px;
        }
        
        .contact-card {
            background: var(--white);
            border: 1px solid var(--gray-light);
            border-radius: 12px;
            padding: 18px;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            gap: 15px;
            transition: all 0.2s;
            cursor: pointer;
        }
        
        .contact-card:hover {
            border-color: var(--blue-primary);
            box-shadow: 0 4px 12px rgba(4, 73, 144, 0.1);
            transform: translateY(-2px);
        }
        
        .contact-icon {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            background: var(--blue-primary);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 24px;
            flex-shrink: 0;
        }
        
        .contact-info {
            flex: 1;
        }
        
        .contact-name {
            font-size: 16px;
            font-weight: 600;
            color: var(--dark-color);
            margin-bottom: 5px;
        }
        
        .contact-hours {
            font-size: 13px;
            color: #6b7280;
        }
        
        .contact-label {
            font-size: 12px;
            color: #9ca3af;
            margin-top: 3px;
        }
        
        .contact-button {
            background: var(--blue-primary);
            color: white;
            border: none;
            border-radius: 8px;
            padding: 10px 20px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 8px;
            transition: all 0.2s;
            text-decoration: none;
        }
        
        .contact-button:hover {
            background: #033a73;
            transform: scale(1.05);
        }
        
        .channel-section {
            padding: 30px 20px;
            background: var(--white);
            border-top: 1px solid var(--gray-light);
        }
        
        .channel-card {
            background: var(--white);
            border: 1px solid var(--gray-light);
            border-radius: 12px;
            padding: 18px;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            gap: 15px;
            transition: all 0.2s;
            cursor: pointer;
        }
        
        .channel-card:hover {
            border-color: var(--blue-primary);
            box-shadow: 0 4px 12px rgba(4, 73, 144, 0.1);
            transform: translateY(-2px);
        }
        
        .channel-info {
            flex: 1;
        }
        
        .channel-description {
            font-size: 13px;
            color: #6b7280;
            margin-bottom: 5px;
        }
        
        .channel-name {
            font-size: 16px;
            font-weight: 600;
            color: var(--dark-color);
        }
        
        .footer-text {
            padding: 25px 20px;
            background: var(--light-color);
            text-align: center;
            font-size: 13px;
            color: #6b7280;
            line-height: 1.6;
        }
        
        @media (max-width: 768px) {
            .support-header {
                padding: 12px 15px;
            }
            
            .support-title {
                font-size: 18px;
            }
            
            .support-image {
                height: 200px;
            }
            
            .support-image-icon {
                font-size: 60px;
            }
            
            .welcome-section {
                padding: 25px 15px;
            }
            
            .welcome-title {
                font-size: 24px;
            }
            
            .welcome-text {
                font-size: 14px;
            }
            
            .contact-section,
            .channel-section {
                padding: 25px 15px;
            }
            
            .section-title {
                font-size: 16px;
            }
            
            .contact-card,
            .channel-card {
                padding: 15px;
            }
            
            .contact-icon {
                width: 45px;
                height: 45px;
                font-size: 20px;
            }
            
            .contact-name,
            .channel-name {
                font-size: 15px;
            }
            
            .contact-hours,
            .channel-description {
                font-size: 12px;
            }
            
            .contact-button {
                padding: 8px 16px;
                font-size: 13px;
            }
            
            .footer-text {
                padding: 20px 15px;
                font-size: 12px;
            }
        }
    </style>
</head>
<body>
    <div class="support-container">
        <!-- Header -->
        <div class="support-header">
            <a href="mio.php" class="back-button">
                <i class="fas fa-arrow-left"></i>
            </a>
            <h1 class="support-title">Servicio al cliente</h1>
        </div>
        
        <!-- Imagen de soporte -->
        <div class="support-image">
            <div class="support-image-icon">
                <i class="fas fa-headset"></i>
            </div>
        </div>
        
        <!-- Mensaje de bienvenida -->
        <div class="welcome-section">
            <div class="welcome-title">Hola ~</div>
            <div class="welcome-text">
                Un equipo profesional de atención al cliente a su servicio. Si tiene alguna pregunta, no dude en preguntarnos directamente. Estaremos encantados de responderle.
            </div>
        </div>
        
        <!-- Información de contacto -->
        <div class="contact-section">
            <h2 class="section-title">Información de contacto</h2>
            
            <div class="contact-card" onclick="window.open('<?php echo $telegram_contact; ?>', '_blank');">
                <div class="contact-icon">
                    <i class="fab fa-telegram-plane"></i>
                </div>
                <div class="contact-info">
                    <div class="contact-name">Telegram</div>
                    <div class="contact-hours"><?php echo $horario_atencion; ?></div>
                    <div class="contact-label">Contacto directo</div>
                </div>
                <a href="<?php echo $telegram_contact; ?>" target="_blank" class="contact-button" onclick="event.stopPropagation();">
                    Entrar <i class="fas fa-arrow-right"></i>
                </a>
            </div>
            
            <div class="contact-card" onclick="window.open('<?php echo $whatsapp_contact; ?>', '_blank');">
                <div class="contact-icon" style="background: #25D366;">
                    <i class="fab fa-whatsapp"></i>
                </div>
                <div class="contact-info">
                    <div class="contact-name">WhatsApp</div>
                    <div class="contact-hours"><?php echo $horario_atencion; ?></div>
                    <div class="contact-label">Contacto directo</div>
                </div>
                <a href="<?php echo $whatsapp_contact; ?>" target="_blank" class="contact-button" style="background: #25D366;" onclick="event.stopPropagation();">
                    Entrar <i class="fas fa-arrow-right"></i>
                </a>
            </div>
        </div>
        
        <!-- Nuestro canal -->
        <div class="channel-section">
            <h2 class="section-title">Nuestro canal</h2>
            
            <div class="channel-card" onclick="window.open('<?php echo $telegram_channel; ?>', '_blank');">
                <div class="contact-icon">
                    <i class="fab fa-telegram-plane"></i>
                </div>
                <div class="channel-info">
                    <div class="channel-description">Aprende más sobre la información del canal</div>
                    <div class="channel-name">Canal de Telegram</div>
                </div>
                <a href="<?php echo $telegram_channel; ?>" target="_blank" class="contact-button" onclick="event.stopPropagation();">
                    Entrar <i class="fas fa-arrow-right"></i>
                </a>
            </div>
            
            <div class="channel-card" onclick="window.open('<?php echo $whatsapp_channel; ?>', '_blank');">
                <div class="contact-icon" style="background: #25D366;">
                    <i class="fab fa-whatsapp"></i>
                </div>
                <div class="channel-info">
                    <div class="channel-description">Aprende más sobre la información del canal</div>
                    <div class="channel-name">Canal de WhatsApp</div>
                </div>
                <a href="<?php echo $whatsapp_channel; ?>" target="_blank" class="contact-button" style="background: #25D366;" onclick="event.stopPropagation();">
                    Entrar <i class="fas fa-arrow-right"></i>
                </a>
            </div>
        </div>
        
        <!-- Footer -->
        <div class="footer-text">
            Un equipo dedicado para responder a sus solicitudes de preventa, promoción, prensa y servicio.
        </div>
    </div>
</body>
</html>

