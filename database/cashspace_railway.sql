-- Script SQL para Railway (sin CREATE DATABASE ni USE)
-- Las tablas se crearán en la base de datos actual (railway)

-- Tabla de usuarios
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    telefono VARCHAR(20) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    nombre VARCHAR(100),
    codigo_invitacion VARCHAR(10) UNIQUE,
    codigo_referido VARCHAR(10),
    saldo DECIMAL(10, 2) DEFAULT 0.00,
    saldo_disponible DECIMAL(10, 2) DEFAULT 0.00,
    saldo_invertido DECIMAL(10, 2) DEFAULT 0.00,
    tipo_usuario ENUM('user', 'admin') DEFAULT 'user',
    estado ENUM('activo', 'inactivo') DEFAULT 'activo',
    fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    ultimo_acceso TIMESTAMP NULL,
    INDEX idx_telefono (telefono),
    INDEX idx_codigo_invitacion (codigo_invitacion)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla de tipos de inversión
CREATE TABLE IF NOT EXISTS tipos_inversion (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    descripcion TEXT,
    precio_inversion DECIMAL(10, 2) NOT NULL,
    ganancia_diaria DECIMAL(10, 2) NOT NULL,
    ganancia_mensual DECIMAL(10, 2) NOT NULL,
    limite_inversion INT DEFAULT 1,
    imagen VARCHAR(255),
    duracion_dias INT DEFAULT 30,
    estado ENUM('activo', 'inactivo') DEFAULT 'activo',
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insertar tipos de inversión iniciales
INSERT INTO tipos_inversion (nombre, descripcion, precio_inversion, ganancia_diaria, ganancia_mensual, limite_inversion, duracion_dias) VALUES
('Inversión Básica', 'Invierte en proyectos de cría y producción ganadera con enfoque en sostenibilidad y calidad, generando rendimientos estables.', 100.00, 12.00, 300.00, 8, 30),
('Inversión Plus', 'Invierte en tecnologías de inteligencia artificial y machine learning, aprovechando el crecimiento exponencial del sector tecnológico.', 200.00, 25.00, 600.00, 8, 30),
('Inversión Premium', 'Invierte en operaciones mineras responsables y sostenibles, generando rendimientos consistentes del sector extractivo.', 500.00, 65.00, 1500.00, 8, 30),
('Inversión Gold', 'Invierte en proyectos de investigación científica y desarrollo tecnológico de vanguardia.', 1000.00, 130.00, 3000.00, 8, 30),
('Inversión Platinum', 'Invierte en operaciones pesqueras sostenibles y acuicultura, aprovechando los recursos marinos de manera responsable.', 3000.00, 390.00, 9000.00, 8, 30),
('Inversión Elite', 'Invierte en proyectos agrícolas modernos y tecnificados, generando rendimientos del sector agropecuario.', 6000.00, 900.00, 22500.00, 8, 30),
('Inversión Diamond', 'Invierte en bienes raíces y desarrollo inmobiliario, aprovechando la apreciación de propiedades y rentas.', 15000.00, 2250.00, 56250.00, 8, 30),
('Inversión Master', 'Invierte en la bolsa de valores y mercados financieros globales con estrategias diversificadas.', 30000.00, 4500.00, 112500.00, 8, 30),
('Inversión Supreme', 'Invierte en el sector automotriz, incluyendo fabricación, distribución y servicios relacionados con vehículos.', 50000.00, 7500.00, 187500.00, 8, 30),
('Inversión Ultimate', 'Invierte en proyectos espaciales y tecnología aeroespacial, el futuro de la exploración y desarrollo espacial.', 100000.00, 15000.00, 375000.00, 8, 30)
ON DUPLICATE KEY UPDATE nombre=nombre;

-- Tabla de inversiones de usuarios
CREATE TABLE IF NOT EXISTS inversiones (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    tipo_inversion_id INT NOT NULL,
    monto_invertido DECIMAL(10, 2) NOT NULL,
    ganancia_diaria DECIMAL(10, 2) NOT NULL,
    ganancia_total_acumulada DECIMAL(10, 2) DEFAULT 0.00,
    fecha_inicio DATE NOT NULL,
    fecha_fin DATE NOT NULL,
    estado ENUM('activa', 'completada', 'cancelada') DEFAULT 'activa',
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (tipo_inversion_id) REFERENCES tipos_inversion(id),
    INDEX idx_usuario (usuario_id),
    INDEX idx_estado (estado)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla de ganancias diarias generadas
CREATE TABLE IF NOT EXISTS ganancias_diarias (
    id INT AUTO_INCREMENT PRIMARY KEY,
    inversion_id INT NOT NULL,
    usuario_id INT NOT NULL,
    monto DECIMAL(10, 2) NOT NULL,
    fecha DATE NOT NULL,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (inversion_id) REFERENCES inversiones(id) ON DELETE CASCADE,
    FOREIGN KEY (usuario_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_ganancia_dia (inversion_id, fecha),
    INDEX idx_usuario_fecha (usuario_id, fecha)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla de recargas
CREATE TABLE IF NOT EXISTS recargas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    monto DECIMAL(10, 2) NOT NULL,
    metodo_pago VARCHAR(50),
    comprobante VARCHAR(255),
    estado ENUM('pendiente', 'aprobada', 'rechazada') DEFAULT 'pendiente',
    admin_id INT,
    fecha_solicitud TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    fecha_aprobacion TIMESTAMP NULL,
    observaciones TEXT,
    FOREIGN KEY (usuario_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_estado (estado),
    INDEX idx_usuario (usuario_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla de retiros
CREATE TABLE IF NOT EXISTS retiros (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    monto DECIMAL(10, 2) NOT NULL,
    comision DECIMAL(10, 2) DEFAULT 0.00,
    metodo_pago VARCHAR(50),
    cuenta_destino VARCHAR(255),
    estado ENUM('pendiente', 'aprobado', 'rechazado') DEFAULT 'pendiente',
    admin_id INT,
    fecha_solicitud TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    fecha_procesamiento TIMESTAMP NULL,
    observaciones TEXT,
    FOREIGN KEY (usuario_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_estado (estado),
    INDEX idx_usuario (usuario_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla de códigos promocionales
CREATE TABLE IF NOT EXISTS codigos_promocionales (
    id INT AUTO_INCREMENT PRIMARY KEY,
    codigo VARCHAR(20) UNIQUE NOT NULL,
    limite_activaciones INT DEFAULT NULL,
    activaciones_usadas INT DEFAULT 0,
    usuario_id INT,
    usado_por INT,
    estado ENUM('activo', 'usado', 'expirado') DEFAULT 'activo',
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    fecha_uso TIMESTAMP NULL,
    fecha_expiracion DATE,
    FOREIGN KEY (usuario_id) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_codigo (codigo),
    INDEX idx_estado (estado)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla de códigos canjeados
CREATE TABLE IF NOT EXISTS codigos_canjeados (
    id INT AUTO_INCREMENT PRIMARY KEY,
    codigo_id INT NOT NULL,
    usuario_id INT NOT NULL,
    monto DECIMAL(10, 2) NOT NULL,
    fecha_canje TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (codigo_id) REFERENCES codigos_promocionales(id) ON DELETE CASCADE,
    FOREIGN KEY (usuario_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_codigo_usuario (codigo_id, usuario_id),
    INDEX idx_codigo (codigo_id),
    INDEX idx_usuario (usuario_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla de notificaciones
CREATE TABLE IF NOT EXISTS notificaciones (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT,
    titulo VARCHAR(200) NOT NULL,
    mensaje TEXT NOT NULL,
    tipo ENUM('info', 'success', 'warning', 'error') DEFAULT 'info',
    leida BOOLEAN DEFAULT FALSE,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_usuario_leida (usuario_id, leida)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla de transacciones (historial completo)
CREATE TABLE IF NOT EXISTS transacciones (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    tipo ENUM('recarga', 'retiro', 'inversion', 'ganancia', 'codigo', 'bono_registro', 'comision') NOT NULL,
    monto DECIMAL(10, 2) NOT NULL,
    descripcion TEXT,
    referencia_id INT,
    saldo_anterior DECIMAL(10, 2),
    saldo_nuevo DECIMAL(10, 2),
    fecha TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_usuario_fecha (usuario_id, fecha),
    INDEX idx_tipo (tipo)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla de avisos del admin
CREATE TABLE IF NOT EXISTS avisos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    titulo VARCHAR(200) NOT NULL,
    mensaje TEXT NOT NULL,
    prioridad INT DEFAULT 5,
    estado ENUM('activo', 'inactivo') DEFAULT 'activo',
    fecha_inicio DATETIME DEFAULT CURRENT_TIMESTAMP,
    fecha_fin DATETIME NULL,
    admin_id INT,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    fecha_actualizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (admin_id) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_estado_fechas (estado, fecha_inicio, fecha_fin)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla de bloqueo de retiros
CREATE TABLE IF NOT EXISTS bloqueo_retiros (
    id INT AUTO_INCREMENT PRIMARY KEY,
    activo TINYINT(1) DEFAULT 0,
    fecha_inicio DATETIME DEFAULT CURRENT_TIMESTAMP,
    fecha_fin DATETIME NULL,
    duracion_horas INT NULL,
    duracion_dias INT NULL,
    indefinido TINYINT(1) DEFAULT 0,
    descripcion TEXT,
    admin_id INT,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    fecha_actualizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (admin_id) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_activo (activo)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla de cuenta bancaria
CREATE TABLE IF NOT EXISTS cuenta_bancaria (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL UNIQUE,
    banco VARCHAR(100),
    numero_cuenta VARCHAR(50),
    tipo_cuenta VARCHAR(20),
    nombre_titular VARCHAR(100),
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    fecha_actualizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla de equipo (referidos)
CREATE TABLE IF NOT EXISTS equipo (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    referido_id INT NOT NULL,
    nivel INT NOT NULL,
    fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (referido_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_usuario_referido_nivel (usuario_id, referido_id, nivel),
    INDEX idx_usuario (usuario_id),
    INDEX idx_referido (referido_id),
    INDEX idx_nivel (nivel)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Crear algunas notificaciones de ejemplo
INSERT INTO notificaciones (usuario_id, titulo, mensaje, tipo) VALUES
(NULL, 'Bienvenido a CashSpace', '¡Gracias por unirte a nuestra plataforma de inversión! Empieza a invertir hoy y genera ganancias diarias.', 'success'),
(NULL, 'Nuevas Oportunidades', 'Hemos agregado nuevos planes de inversión con excelentes rendimientos. ¡Explóralos ahora!', 'info')
ON DUPLICATE KEY UPDATE titulo=titulo;

