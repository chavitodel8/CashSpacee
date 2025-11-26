-- Migración para agregar tipo 'comision' a la tabla transacciones
ALTER TABLE transacciones MODIFY COLUMN tipo ENUM('recarga', 'retiro', 'inversion', 'ganancia', 'codigo', 'comision') NOT NULL;

