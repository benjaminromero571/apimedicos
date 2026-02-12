-- ============================================================
-- Migración: Tabla indicaciones_medicas
-- Fecha: 2026-02-12
-- Descripción: Crear tabla para indicaciones médicas de pacientes
-- ============================================================

-- electroc3_medicos.indicaciones_medicas definition

CREATE TABLE IF NOT EXISTS `indicaciones_medicas` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `paciente_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `indicaciones` text NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `created_by` int(11) DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_by` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- ============================================================
-- Verificación post-migración
-- ============================================================
-- SELECT COUNT(*) as total FROM indicaciones_medicas;
-- DESCRIBE indicaciones_medicas;
