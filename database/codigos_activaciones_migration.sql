-- Migración para modificar tabla de códigos promocionales
-- Agregar campos para límite de activaciones y contador

-- Nota: Si las columnas ya existen, estos comandos fallarán. 
-- En ese caso, simplemente ignore los errores o ejecute solo los que falten.

ALTER TABLE codigos_promocionales 
ADD COLUMN limite_activaciones INT DEFAULT NULL,
ADD COLUMN activaciones_usadas INT DEFAULT 0;

-- Crear tabla para registrar cada canje de código (evitar que un usuario canjee el mismo código dos veces)
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

