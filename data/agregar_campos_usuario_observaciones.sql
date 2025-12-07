-- Script para agregar campos de usuario que modifica observaciones
-- Base de datos: roel
-- Tabla: reservas
-- Fecha: 2025-12-07

USE roel;

-- Agregar campo para ID de usuario que modificó la observación general
ALTER TABLE reservas
ADD COLUMN id_usuario_obs INT(11) NULL AFTER observaciones,
ADD INDEX idx_usuario_obs (id_usuario_obs),
ADD CONSTRAINT fk_reservas_usuario_obs
    FOREIGN KEY (id_usuario_obs) REFERENCES usuarios(id)
    ON DELETE SET NULL ON UPDATE CASCADE;

-- Agregar campo para ID de usuario que modificó la observación de picking
ALTER TABLE reservas
ADD COLUMN id_usuario_obs_picking INT(11) NULL AFTER observaciones_picking,
ADD INDEX idx_usuario_obs_picking (id_usuario_obs_picking),
ADD CONSTRAINT fk_reservas_usuario_obs_picking
    FOREIGN KEY (id_usuario_obs_picking) REFERENCES usuarios(id)
    ON DELETE SET NULL ON UPDATE CASCADE;

-- Agregar campo para ID de usuario que modificó la observación de packing
ALTER TABLE reservas
ADD COLUMN id_usuario_obs_packing INT(11) NULL AFTER observaciones_packing,
ADD INDEX idx_usuario_obs_packing (id_usuario_obs_packing),
ADD CONSTRAINT fk_reservas_usuario_obs_packing
    FOREIGN KEY (id_usuario_obs_packing) REFERENCES usuarios(id)
    ON DELETE SET NULL ON UPDATE CASCADE;

-- Verificar que los campos se agregaron correctamente
DESCRIBE reservas;
