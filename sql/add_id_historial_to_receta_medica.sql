ALTER TABLE `receta_medica` ADD `id_historial` INT NOT NULL AFTER `updated_by`;
ALTER TABLE `historial_cuidador` ADD `fecha_historial_timestamp` DATETIME NOT NULL AFTER `fecha_historial`;
ALTER TABLE `historial_cuidador` CHANGE `fecha_historial` `fecha_historial` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP;
ALTER TABLE `historial_cuidador` ADD `registro` JSON NOT NULL DEFAULT '{}' AFTER `detalle`;
ALTER TABLE `historial_cuidador` CHANGE `detalle` `detalle` VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL;