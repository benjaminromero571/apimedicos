-- Migración: Agregar campos activo y motivo_baja a la tabla pacientes
-- Fecha: 2026-04-30

ALTER TABLE pacientes
ADD COLUMN activo TINYINT(1) NOT NULL DEFAULT 1 COMMENT '1 = activo, 0 = inactivo'
AFTER dirpaciente;

ALTER TABLE pacientes
ADD COLUMN motivo_baja VARCHAR(50) NULL DEFAULT NULL COMMENT 'sanado | baja | NULL'
AFTER activo;

-- Marcar todos los pacientes existentes como activos sin motivo de baja
UPDATE pacientes SET activo = 1, motivo_baja = NULL WHERE activo IS NULL;
