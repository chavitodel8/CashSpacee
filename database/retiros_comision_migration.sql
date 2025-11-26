-- Migración para agregar campo comision a la tabla retiros
ALTER TABLE retiros ADD COLUMN IF NOT EXISTS comision DECIMAL(10, 2) DEFAULT 0.00 AFTER monto;

