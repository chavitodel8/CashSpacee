-- Migración para crear tabla de bloqueo de retiros
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

