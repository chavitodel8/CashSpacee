-- Migraciones para el sistema de equipo

-- Tabla de equipos/referidos (para rastrear los miembros del equipo en diferentes niveles)
CREATE TABLE IF NOT EXISTS equipos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_padre_id INT NOT NULL COMMENT 'Usuario que invitó',
    usuario_referido_id INT NOT NULL COMMENT 'Usuario que fue invitado',
    nivel INT NOT NULL DEFAULT 1 COMMENT 'Nivel en el árbol de referidos (1, 2, 3)',
    fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_padre_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (usuario_referido_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_referido (usuario_padre_id, usuario_referido_id),
    INDEX idx_padre (usuario_padre_id),
    INDEX idx_referido (usuario_referido_id),
    INDEX idx_nivel (nivel)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla de recompensas de inversión del equipo
CREATE TABLE IF NOT EXISTS recompensas_equipo (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nivel INT NOT NULL DEFAULT 1 COMMENT 'Nivel de la recompensa (1-7)',
    monto_inversion_requerido DECIMAL(10, 2) NOT NULL COMMENT 'Monto total de inversión requerido del equipo nivel 1',
    monto_recompensa DECIMAL(10, 2) NOT NULL COMMENT 'Monto de la recompensa',
    descripcion TEXT,
    estado ENUM('activo', 'inactivo') DEFAULT 'activo',
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insertar recompensas iniciales
INSERT INTO recompensas_equipo (nivel, monto_inversion_requerido, monto_recompensa, descripcion) VALUES
(1, 500.00, 20.00, 'La inversión total de los miembros nivel 1 del equipo alcanzó los Bs 500.00'),
(2, 1000.00, 50.00, 'La inversión total de los miembros nivel 1 del equipo alcanzó los Bs 1000.00'),
(3, 2000.00, 100.00, 'La inversión total de los miembros nivel 1 del equipo alcanzó los Bs 2000.00'),
(4, 5000.00, 300.00, 'La inversión total de los miembros nivel 1 del equipo alcanzó los Bs 5000.00'),
(5, 20000.00, 1000.00, 'La inversión total de los miembros nivel 1 del equipo alcanzó los Bs 20000.00'),
(6, 50000.00, 5000.00, 'La inversión total de los miembros nivel 1 del equipo alcanzó los Bs 50000.00'),
(7, 150000.00, 10000.00, 'La inversión total de los miembros nivel 1 del equipo alcanzó los Bs 150000.00');

-- Tabla para rastrear recompensas recibidas por usuarios
CREATE TABLE IF NOT EXISTS recompensas_recibidas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    recompensa_id INT NOT NULL,
    monto_recibido DECIMAL(10, 2) NOT NULL,
    fecha_recibido TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (recompensa_id) REFERENCES recompensas_equipo(id) ON DELETE CASCADE,
    UNIQUE KEY unique_recompensa_usuario (usuario_id, recompensa_id),
    INDEX idx_usuario (usuario_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

