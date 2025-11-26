-- Base de datos para CashSpace
CREATE DATABASE IF NOT EXISTS cashspace CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE cashspace;

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
('Inversión Básica', 'Perfecta para empezar en el mundo de las inversiones', 100.00, 12.00, 300.00, 1, 30),
('Inversión Plus', 'Inversión ideal para obtener mejores rendimientos', 200.00, 25.00, 600.00, 1, 30),
('Inversión Premium', 'Para inversionistas más experimentados', 500.00, 65.00, 1500.00, 1, 30),
('Inversión Gold', 'Nivel avanzado de inversión', 1000.00, 130.00, 3000.00, 1, 30),
('Inversión Platinum', 'Para inversionistas profesionales', 2000.00, 260.00, 6000.00, 1, 30),
('Inversión Diamond', 'Máximo nivel de inversión', 5000.00, 650.00, 15000.00, 1, 30),
('Inversión Master', 'El nivel más exclusivo de inversión', 10000.00, 1300.00, 30000.00, 1, 30);

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
    monto DECIMAL(10, 2) NOT NULL,
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
    tipo ENUM('recarga', 'retiro', 'inversion', 'ganancia', 'codigo') NOT NULL,
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

-- El usuario admin se creará automáticamente al ejecutar install.php
-- O puedes ejecutar setup_password.php para crearlo manualmente

-- Crear algunas notificaciones de ejemplo
INSERT INTO notificaciones (usuario_id, titulo, mensaje, tipo) VALUES
(NULL, 'Bienvenido a CashSpace', '¡Gracias por unirte a nuestra plataforma de inversión! Empieza a invertir hoy y genera ganancias diarias.', 'success'),
(NULL, 'Nuevas Oportunidades', 'Hemos agregado nuevos planes de inversión con excelentes rendimientos. ¡Explóralos ahora!', 'info');

