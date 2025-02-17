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
    orcid VARCHAR(255) DEFAULT NULL,
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
    motivo_rechazo_correcciones TEXT DEFAULT NULL,
    fecha_subida TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    estado_tesis VARCHAR(50),
    correcciones_tesis VARCHAR(255) DEFAULT NULL,
    id_revisor_plagio INT, 
	doc_plagio VARCHAR(255), 
	id_jurado_uno INT, 
	id_jurado_dos INT, 
	id_jurado_tres INT, 
	ult_correcc_doc_tesis VARCHAR(255), 
	estado_ultimas_obs VARCHAR(100) DEFAULT 'Pendiente',
	rubrica_calificacion VARCHAR(255) DEFAULT NULL,
	certificados VARCHAR(255) DEFAULT NULL,
	nota_revisor_tesis DECIMAL(4,2) DEFAULT NULL,
	enlace_plagio VARCHAR(255) DEFAULT NULL,
	motivo_rechazo_enlace TEXT DEFAULT NULL,
	estado_enlace VARCHAR(100) DEFAULT 'Pendiente',
	sede VARCHAR(255) DEFAULT NULL,
	aula VARCHAR(255) DEFAULT NULL,
	fecha_sustentar DATE DEFAULT NULL,
	hora_sustentar TIME DEFAULT NULL,
	j1_nota_sustentar DECIMAL(4,2) DEFAULT NULL,
	j2_nota_sustentar DECIMAL(4,2) DEFAULT NULL,
	j3_nota_sustentar DECIMAL(4,2) DEFAULT NULL,
    j1_nota_sustentar_2 DECIMAL(4,2) DEFAULT NULL,
	j2_nota_sustentar_2 DECIMAL(4,2) DEFAULT NULL,
	j3_nota_sustentar_2 DECIMAL(4,2) DEFAULT NULL,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    FOREIGN KEY (tutor_id) REFERENCES tutores(id) ON DELETE CASCADE
);

CREATE TABLE periodo_academico (
	id INT AUTO_INCREMENT PRIMARY KEY,
    periodo VARCHAR(255) NOT NULL
);

INSERT INTO procesotitulacion.periodo_academico (periodo) value ('II periodo académico del año 2024');
INSERT INTO procesotitulacion.periodo_academico (periodo) value ('III periodo académico del año 2024');

CREATE TABLE informes_tutores (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tutor_id INT NOT NULL,
    archivo VARCHAR(255) NOT NULL,
	estado INT(2) DEFAULT 0,
    fecha_subida TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (tutor_id) REFERENCES usuarios(id) ON DELETE CASCADE
);

CREATE TABLE informes_tesis (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tutor_id INT NOT NULL,
    informe_tesis VARCHAR(255) NOT NULL,
	estado INT(2) DEFAULT 0,
    fecha_subida TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (tutor_id) REFERENCES usuarios(id) ON DELETE CASCADE
);

DELIMITER $$
CREATE TRIGGER after_user_insert
AFTER INSERT ON usuarios
FOR EACH ROW
BEGIN
	INSERT INTO registro (cedula, password) 
	VALUES (NEW.cedula, NEW.password);
END$$
DELIMITER ;