-- ================================================================
-- CREACIÓN DE TABLA: historial_cuidador
-- ================================================================
-- Tabla para registrar historiales diarios de cuidadores sobre pacientes
-- Incluye auditoría completa (created_at, created_by, updated_at, updated_by)
-- ================================================================

CREATE TABLE IF NOT EXISTS `historial_cuidador` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `fecha_historial` datetime NOT NULL DEFAULT current_timestamp() COMMENT 'Fecha y hora del evento registrado',
  `detalle` varchar(255) NOT NULL COMMENT 'Descripción detallada del registro',
  `id_paciente` int(11) NOT NULL COMMENT 'FK: Referencia a tabla pacientes',
  `id_cuidador` int(11) NOT NULL COMMENT 'FK: Referencia a tabla usuario (rol Cuidador)',
  `created_at` datetime NOT NULL DEFAULT current_timestamp() COMMENT 'Fecha de creación del registro',
  `created_by` int(11) NOT NULL COMMENT 'FK: Usuario que creó el registro',
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp() COMMENT 'Fecha de última actualización',
  `updated_by` int(11) NOT NULL COMMENT 'FK: Usuario que actualizó el registro',
  PRIMARY KEY (`id`),
  KEY `idx_paciente` (`id_paciente`),
  KEY `idx_cuidador` (`id_cuidador`),
  KEY `idx_fecha` (`fecha_historial`),
  KEY `idx_created_by` (`created_by`),
  KEY `idx_updated_by` (`updated_by`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Historiales de cuidadores sobre pacientes';

-- ================================================================
-- DATOS DE EJEMPLO (Opcional)
-- ================================================================
-- NOTA: Asegúrate de tener pacientes y usuarios existentes antes de insertar
-- Ajusta los IDs según tu base de datos

-- Ejemplo 1: Registro de alimentación
INSERT INTO `historial_cuidador` 
(`detalle`, `id_paciente`, `id_cuidador`, `created_by`, `updated_by`, `fecha_historial`) 
VALUES 
('Paciente tomó desayuno completo sin complicaciones', 1, 1, 1, 1, '2024-01-15 08:30:00');

-- Ejemplo 2: Registro de medicación
INSERT INTO `historial_cuidador` 
(`detalle`, `id_paciente`, `id_cuidador`, `created_by`, `updated_by`, `fecha_historial`) 
VALUES 
('Administrada medicación prescrita a las 10:00 AM', 1, 1, 1, 1, '2024-01-15 10:00:00');

-- Ejemplo 3: Registro de actividad física
INSERT INTO `historial_cuidador` 
(`detalle`, `id_paciente`, `id_cuidador`, `created_by`, `updated_by`, `fecha_historial`) 
VALUES 
('Paciente realizó ejercicios de rehabilitación por 30 minutos', 1, 1, 1, 1, '2024-01-15 14:00:00');

-- Ejemplo 4: Registro de estado de ánimo
INSERT INTO `historial_cuidador` 
(`detalle`, `id_paciente`, `id_cuidador`, `created_by`, `updated_by`, `fecha_historial`) 
VALUES 
('Paciente mostró buen estado de ánimo durante la tarde', 1, 1, 1, 1, '2024-01-15 16:00:00');

-- Ejemplo 5: Registro de higiene personal
INSERT INTO `historial_cuidador` 
(`detalle`, `id_paciente`, `id_cuidador`, `created_by`, `updated_by`, `fecha_historial`) 
VALUES 
('Se asistió al paciente en higiene personal completa', 1, 1, 1, 1, '2024-01-15 18:00:00');

-- ================================================================
-- CONSULTAS ÚTILES
-- ================================================================

-- Ver todos los historiales con información completa
SELECT 
    hc.id,
    hc.fecha_historial,
    hc.detalle,
    CONCAT(p.nombre, ' ', p.apellidop, ' ', p.apellidom) as nombre_paciente,
    CONCAT(c.nomusuario, ' ', c.apellidopaterno, ' ', c.apellidomaterno) as nombre_cuidador,
    hc.created_at,
    CONCAT(u1.nomusuario, ' ', u1.apellidopaterno) as creado_por,
    hc.updated_at,
    CONCAT(u2.nomusuario, ' ', u2.apellidopaterno) as actualizado_por
FROM historial_cuidador hc
LEFT JOIN pacientes p ON hc.id_paciente = p.idpaciente
LEFT JOIN usuario c ON hc.id_cuidador = c.idusuario
LEFT JOIN usuario u1 ON hc.created_by = u1.idusuario
LEFT JOIN usuario u2 ON hc.updated_by = u2.idusuario
ORDER BY hc.fecha_historial DESC;

-- Historiales por paciente específico
SELECT * FROM historial_cuidador 
WHERE id_paciente = 1 
ORDER BY fecha_historial DESC;

-- Historiales por cuidador específico
SELECT * FROM historial_cuidador 
WHERE id_cuidador = 1 
ORDER BY fecha_historial DESC;

-- Contar historiales por paciente
SELECT 
    id_paciente,
    CONCAT(p.nombre, ' ', p.apellidop) as paciente,
    COUNT(*) as total_registros,
    MAX(fecha_historial) as ultimo_registro
FROM historial_cuidador hc
LEFT JOIN pacientes p ON hc.id_paciente = p.idpaciente
GROUP BY id_paciente
ORDER BY total_registros DESC;

-- Historiales en un rango de fechas
SELECT * FROM historial_cuidador 
WHERE fecha_historial BETWEEN '2024-01-01' AND '2024-12-31'
ORDER BY fecha_historial DESC;

-- Búsqueda por texto en detalle
SELECT * FROM historial_cuidador 
WHERE detalle LIKE '%medicación%'
ORDER BY fecha_historial DESC;

-- ================================================================
-- TRIGGERS (Opcional)
-- ================================================================
-- Actualizar automáticamente updated_at
DELIMITER $$

CREATE TRIGGER IF NOT EXISTS `historial_cuidador_before_update`
BEFORE UPDATE ON `historial_cuidador`
FOR EACH ROW
BEGIN
    SET NEW.updated_at = NOW();
END$$

DELIMITER ;

-- ================================================================
-- MANTENIMIENTO
-- ================================================================

-- Ver estadísticas de la tabla
SELECT 
    COUNT(*) as total_registros,
    COUNT(DISTINCT id_paciente) as total_pacientes,
    COUNT(DISTINCT id_cuidador) as total_cuidadores,
    MIN(fecha_historial) as primer_registro,
    MAX(fecha_historial) as ultimo_registro
FROM historial_cuidador;

-- Verificar integridad referencial (IDs inválidos)
SELECT 'Pacientes inválidos:', COUNT(*) 
FROM historial_cuidador hc
LEFT JOIN pacientes p ON hc.id_paciente = p.idpaciente
WHERE p.idpaciente IS NULL

UNION ALL

SELECT 'Cuidadores inválidos:', COUNT(*) 
FROM historial_cuidador hc
LEFT JOIN usuario u ON hc.id_cuidador = u.idusuario
WHERE u.idusuario IS NULL;

COMMIT;
