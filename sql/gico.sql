SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

CREATE TABLE asignaciones (
  id int(11) NOT NULL,
  user_id int(11) NOT NULL COMMENT 'ID del Profesional o Cuidador',
  paciente_id int(11) NOT NULL COMMENT 'ID del Paciente asignado',
  fecha_asignacion datetime DEFAULT current_timestamp() COMMENT 'Fecha de la asignación',
  created_at date DEFAULT current_timestamp(),
  created_by int(11) DEFAULT NULL,
  updated_at date DEFAULT NULL,
  updated_by int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE historial (
  idhistorial int(11) NOT NULL,
  pesohistorial text NOT NULL,
  tallahistorial text NOT NULL,
  fchistorial text NOT NULL,
  frhistorial text NOT NULL,
  ahhistorial text NOT NULL,
  apnphistorial text NOT NULL,
  hemotipohistorial text NOT NULL,
  alergiashistorial text NOT NULL,
  apphistorial text NOT NULL,
  citahistorial text NOT NULL,
  idpaciente int(11) NOT NULL,
  fechahistorial text NOT NULL,
  diagnostico text NOT NULL,
  created_at date DEFAULT current_timestamp(),
  created_by int(11) DEFAULT NULL,
  updated_at date DEFAULT NULL,
  updated_by int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

CREATE TABLE historial_cuidador (
  id int(11) NOT NULL,
  fecha_historial date NOT NULL DEFAULT current_timestamp(),
  detalle varchar(255) NOT NULL,
  registro JSON NOT NULL DEFAULT '{}',
  id_paciente int(11) NOT NULL,
  id_cuidador int(11) NOT NULL,
  created_at date NOT NULL DEFAULT current_timestamp(),
  created_by int(11) NOT NULL,
  updated_at date NOT NULL DEFAULT current_timestamp(),
  updated_by int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE pacientes (
  rutpaciente varchar(13) NOT NULL,
  idpaciente int(11) NOT NULL,
  nompaciente text NOT NULL,
  edadpaciente text CHARACTER SET utf8 COLLATE utf8_spanish_ci NOT NULL,
  telpaciente text NOT NULL,
  dirpaciente text NOT NULL,
  created_at date DEFAULT current_timestamp(),
  created_by int(11) DEFAULT NULL,
  updated_at date DEFAULT NULL,
  updated_by int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

CREATE TABLE paciente_cuidador (
  id_paciente int(11) NOT NULL,
  id_cuidador int(11) NOT NULL,
  created_at date NOT NULL DEFAULT current_timestamp(),
  created_by int(11) DEFAULT NULL,
  updated_at date DEFAULT NULL,
  updated_by int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE paciente_profesional (
  id_paciente int(11) NOT NULL,
  id_profesional int(11) NOT NULL,
  created_at date DEFAULT current_timestamp(),
  created_by int(11) DEFAULT NULL,
  updated_at date DEFAULT NULL,
  updated_by int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE profesionales (
  id int(11) NOT NULL,
  nombre varchar(255) NOT NULL,
  telefono varchar(255) DEFAULT NULL,
  documento varchar(255) NOT NULL,
  especialidad varchar(255) NOT NULL,
  id_user int(11) DEFAULT NULL,
  created_at date DEFAULT current_timestamp(),
  created_by int(11) DEFAULT NULL,
  updated_at date DEFAULT NULL,
  updated_by int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE receta_medica (
  id int(11) NOT NULL,
  detalle varchar(255) NOT NULL,
  fecha date NOT NULL DEFAULT current_timestamp(),
  id_medico int(11) NOT NULL,
  created_at date NOT NULL DEFAULT current_timestamp(),
  created_by int(11) NOT NULL,
  updated_at date NOT NULL DEFAULT current_timestamp(),
  updated_by int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE users (
  id int(11) NOT NULL,
  name varchar(255) NOT NULL,
  password varchar(255) NOT NULL,
  email varchar(255) NOT NULL,
  rol varchar(50) NOT NULL DEFAULT 'Cuidador',
  created_at date DEFAULT current_timestamp(),
  created_by int(11) DEFAULT NULL,
  updated_at date DEFAULT NULL,
  updated_by int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;


ALTER TABLE asignaciones
  ADD PRIMARY KEY (id),
  ADD UNIQUE KEY uk_user_paciente (user_id,paciente_id);

ALTER TABLE historial
  ADD PRIMARY KEY (idhistorial);

ALTER TABLE historial_cuidador
  ADD PRIMARY KEY (id);

ALTER TABLE pacientes
  ADD PRIMARY KEY (idpaciente);

ALTER TABLE profesionales
  ADD PRIMARY KEY (id);

ALTER TABLE receta_medica
  ADD PRIMARY KEY (id);

ALTER TABLE users
  ADD PRIMARY KEY (id);


ALTER TABLE asignaciones
  MODIFY id int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE historial
  MODIFY idhistorial int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE historial_cuidador
  MODIFY id int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE pacientes
  MODIFY idpaciente int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE profesionales
  MODIFY id int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE receta_medica
  MODIFY id int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE users
  MODIFY id int(11) NOT NULL AUTO_INCREMENT;


ALTER TABLE asignaciones
  ADD CONSTRAINT fk_asignacion_user FOREIGN KEY (user_id) REFERENCES `users` (id) ON DELETE CASCADE ON UPDATE CASCADE;
