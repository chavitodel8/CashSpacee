-- Actualizar límite de inversión a 8 para todos los tipos de inversión
UPDATE tipos_inversion SET limite_inversion = 8 WHERE estado = 'activo';

