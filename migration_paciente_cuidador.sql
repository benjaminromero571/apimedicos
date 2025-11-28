-- ====================================================================
-- MIGRACIÓN: Cambio de relación 1:1 a N:M entre Pacientes y Cuidadores
-- ====================================================================
-- Este script migra los datos existentes de la columna id_cuidador 
-- en la tabla pacientes a la nueva tabla intermedia paciente_cuidador
-- ====================================================================

-- 1. Verificar que la tabla paciente_cuidador existe
-- Si no existe, crearla:
CREATE TABLE IF NOT EXISTS `paciente_cuidador` (
  `id_paciente` int(11) NOT NULL,
  `id_cuidador` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `created_by` int(11) DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  `updated_by` int(11) DEFAULT NULL,
  PRIMARY KEY (`id_paciente`, `id_cuidador`),
  KEY `fk_pc_cuidador` (`id_cuidador`),
  KEY `fk_pc_paciente` (`id_paciente`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- 2. Migrar datos existentes de pacientes que tienen cuidador asignado
-- Solo si la columna id_cuidador aún existe en la tabla pacientes
INSERT INTO `paciente_cuidador` (`id_paciente`, `id_cuidador`, `created_at`)
SELECT 
    `idpaciente`, 
    `id_cuidador`, 
    NOW()
FROM `pacientes`
WHERE `id_cuidador` IS NOT NULL
ON DUPLICATE KEY UPDATE `id_paciente` = `id_paciente`; -- Evitar duplicados

-- 3. Verificar la migración
SELECT 
    COUNT(*) as total_relaciones_migradas,
    COUNT(DISTINCT id_paciente) as pacientes_con_cuidador,
    COUNT(DISTINCT id_cuidador) as cuidadores_asignados
FROM `paciente_cuidador`;

-- 4. IMPORTANTE: Eliminar la columna id_cuidador de la tabla pacientes
-- ADVERTENCIA: Ejecutar este paso SOLO después de verificar que la migración fue exitosa
-- y que la aplicación está funcionando correctamente con la nueva tabla
-- 
-- DESCOMENTAR LA SIGUIENTE LÍNEA SOLO CUANDO ESTÉS SEGURO:
-- ALTER TABLE `pacientes` DROP COLUMN `id_cuidador`;

-- 5. Agregar restricciones de clave foránea (opcional pero recomendado)
-- DESCOMENTAR SI DESEAS AGREGAR CONSTRAINTS:
-- ALTER TABLE `paciente_cuidador`
--   ADD CONSTRAINT `fk_pc_paciente` FOREIGN KEY (`id_paciente`) REFERENCES `pacientes` (`idpaciente`) ON DELETE CASCADE,
--   ADD CONSTRAINT `fk_pc_cuidador` FOREIGN KEY (`id_cuidador`) REFERENCES `users` (`id`) ON DELETE CASCADE;

-- ====================================================================
-- FIN DE LA MIGRACIÓN
-- ====================================================================
