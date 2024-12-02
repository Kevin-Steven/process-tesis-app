create database procesotitulacion;
use procesotitulacion;

CREATE TABLE usuarios (
	id INT AUTO_INCREMENT PRIMARY KEY,
	nombres VARCHAR(100) NOT NULL,
	apellidos VARCHAR(100) NOT NULL,
	email VARCHAR(100) NOT NULL UNIQUE,
	cedula VARCHAR(20) NOT NULL UNIQUE,
	direccion VARCHAR(255) NOT NULL,		
	telefono VARCHAR(20) NOT NULL,
	whatsapp VARCHAR(20) NOT NULL,
	carrera VARCHAR(100) NOT NULL,
	password VARCHAR(255) NOT NULL,
	pareja_tesis INT default 0,
	foto_perfil VARCHAR(255),
	fecha_subida TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
	rol ENUM('postulante', 'administrador', 'gestor', 'docente') NOT NULL DEFAULT 'postulante'
);

CREATE TABLE registro (
	id INT AUTO_INCREMENT PRIMARY KEY,
	cedula VARCHAR(20) NOT NULL,
	password VARCHAR(255) NOT NULL
);

CREATE TABLE recuperacion_clave (
	id INT AUTO_INCREMENT PRIMARY KEY,
	usuario_id INT NOT NULL,
	token VARCHAR(255) NOT NULL,
	expira DATETIME NOT NULL,
	FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
);

CREATE TABLE documentos_postulante (
	id INT AUTO_INCREMENT PRIMARY KEY,
	usuario_id INT NOT NULL,
	documento_carpeta VARCHAR(255) NOT NULL,
	estado_inscripcion VARCHAR(100) default 'No Completado',
	estado_registro int(2) default 0,
	fecha_subida TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
	FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
);

-- Crear tabla de tutores
CREATE TABLE tutores (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombres VARCHAR(255) NOT NULL,
    cedula VARCHAR(20) NOT NULL UNIQUE
);

-- Insertar datos en la tabla tutores
INSERT INTO tutores (nombres, cedula)
VALUES
('Alvarado Chang Jorge', '0908903230'),
('Castro Torres Paul', '1204345035'),
('Domínguez Ramos Fernando', '0922879721'),
('Hernández León Antony', '0924671704'),
('Manzano Araujo Renato', '0918359001'),
('Moncayo Pacheco Alberto', '0922493341'),
('Olvera Moran Mariuxi', '0920163334'),
('Plaza Quizhpi Jorge', '0920521465'),
('Tamayo Miranda Marco', '0920371887');

-- Crear tabla tema
CREATE TABLE tema (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    pareja_id INT,
    tema VARCHAR(255) NOT NULL,
    objetivo_general TEXT NOT NULL,
    objetivo_especifico_uno TEXT NOT NULL,
    objetivo_especifico_dos TEXT NOT NULL,
    objetivo_especifico_tres TEXT NOT NULL,
    tutor_id INT NOT NULL,
    anteproyecto VARCHAR(255),
    documento_tesis VARCHAR(255),
    estado_tema VARCHAR(100) DEFAULT 'Pendiente',
    estado_registro INT(2) DEFAULT 0,
    revisor_anteproyecto_id int,
    revisor_tesis_id int,
    observaciones_anteproyecto VARCHAR(255),
    observaciones_tesis VARCHAR(255),
    motivo_rechazo TEXT,
    fecha_subida TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    FOREIGN KEY (tutor_id) REFERENCES tutores(id) ON DELETE CASCADE
);

ALTER TABLE tema 
ADD COLUMN estado_tesis VARCHAR(50) DEFAULT 'Pendiente';

ALTER TABLE tema 
ADD COLUMN correcciones_tesis VARCHAR(255) DEFAULT NULL;

DELIMITER $$
CREATE TRIGGER after_user_insert
AFTER INSERT ON usuarios
FOR EACH ROW
BEGIN
	INSERT INTO registro (cedula, password) 
	VALUES (NEW.cedula, NEW.password);
END$$
DELIMITER ;