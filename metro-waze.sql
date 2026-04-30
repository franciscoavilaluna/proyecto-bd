DROP DATABASE IF EXISTS metro;
CREATE DATABASE metro CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE metro;

CREATE TABLE usuario (
    id_usuario INT PRIMARY KEY AUTO_INCREMENT NOT NULL,
    nombre VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(100) NOT NULL,
    puntos_reputacion INT NOT NULL DEFAULT 0,
    es_admin BOOLEAN NOT NULL DEFAULT FALSE
);

CREATE TABLE linea (
    id_linea INT PRIMARY KEY AUTO_INCREMENT NOT NULL,
    nombre VARCHAR(100) NOT NULL
);

CREATE TABLE estacion (
    id_estacion INT PRIMARY KEY AUTO_INCREMENT NOT NULL,
    nombre VARCHAR(100) NOT NULL,
    id_linea INT NOT NULL,
    tiempo_espera_segundos INT NOT NULL DEFAULT 180,
    FOREIGN KEY (id_linea) REFERENCES linea(id_linea) ON DELETE CASCADE ON UPDATE CASCADE
);

CREATE TABLE log (
    id_historico INT PRIMARY KEY AUTO_INCREMENT NOT NULL,
    id_estacion INT NOT NULL,
    hora_fecha DATETIME NOT NULL,
    afluencia_promedio FLOAT NOT NULL,
    FOREIGN KEY (id_estacion) REFERENCES estacion(id_estacion) ON DELETE CASCADE ON UPDATE CASCADE
);

CREATE TABLE reporte (
    id_reporte INT PRIMARY KEY AUTO_INCREMENT NOT NULL,
    id_usuario INT NOT NULL,
    id_estacion INT NOT NULL,
    categoria VARCHAR(50) NOT NULL,
    descripcion VARCHAR(150) NOT NULL,
    fecha_hora DATETIME NOT NULL,
    activo BOOLEAN NOT NULL DEFAULT TRUE,
    FOREIGN KEY (id_usuario) REFERENCES usuario(id_usuario) ON DELETE CASCADE,
    FOREIGN KEY (id_estacion) REFERENCES estacion(id_estacion) ON DELETE CASCADE
);

CREATE TABLE validacion (
    id_validacion INT PRIMARY KEY AUTO_INCREMENT NOT NULL,
    id_usuario INT NOT NULL,
    id_reporte INT NOT NULL,
    estado BOOLEAN NOT NULL,
    FOREIGN KEY (id_usuario) REFERENCES usuario(id_usuario) ON DELETE CASCADE,
    FOREIGN KEY (id_reporte) REFERENCES reporte(id_reporte) ON DELETE CASCADE
);

SET FOREIGN_KEY_CHECKS = 1;

INSERT INTO usuario (nombre, email, password, puntos_reputacion, es_admin) VALUES
('Admin Metro', 'admin@metrowaze.com', 'admin123', 100, TRUE),
('Juan Pérez', 'juan@mail.com', 'juan123', 25, FALSE),
('María López', 'maria@mail.com', 'maria123', 40, FALSE);

INSERT INTO linea (id_linea, nombre) VALUES
(1, 'Línea 1'),
(9, 'Línea 9');

INSERT INTO estacion (nombre, id_linea, tiempo_espera_segundos) VALUES
('Observatorio', 1, 120), ('Tacubaya', 1, 150), ('Juanacatlán', 1, 90), ('Chapultepec', 1, 100),
('Sevilla', 1, 110), ('Insurgentes', 1, 130), ('Cuauhtémoc', 1, 140), ('Balderas', 1, 120),
('Salto del Agua', 1, 150), ('Isabel la Católica', 1, 160), ('Pino Suárez', 1, 170), ('Merced', 1, 180),
('Candelaria', 1, 140), ('San Lázaro', 1, 130), ('Moctezuma', 1, 120), ('Balbuena', 1, 110),
('Boulevard Puerto Aéreo', 1, 100), ('Gómez Farías', 1, 90), ('Zaragoza', 1, 80), ('Pantitlán', 1, 70),
('Tacubaya', 9, 130), ('Patriotismo', 9, 120), ('Chilpancingo', 9, 110), ('Centro Médico', 9, 140),
('Lázaro Cárdenas', 9, 130), ('Chabacano', 9, 150), ('Jamaica', 9, 140), ('Mixiuhca', 9, 120),
('Velódromo', 9, 110), ('Ciudad Deportiva', 9, 100), ('Puebla', 9, 90), ('Pantitlán', 9, 80);

INSERT INTO log (id_estacion, hora_fecha, afluencia_promedio)
SELECT e.id_estacion, NOW() - INTERVAL FLOOR(RAND()*5) HOUR, ROUND(50 + RAND()*200,2)
FROM estacion e;

INSERT INTO reporte (id_usuario, id_estacion, categoria, descripcion, fecha_hora, activo)
VALUES (
    2,
    (SELECT id_estacion FROM estacion WHERE nombre = 'Sevilla' LIMIT 1),
    'Limpieza',
    'Basura en el andén',
    NOW() - INTERVAL 2 DAY,
    TRUE
),
(
    3,
    (SELECT id_estacion FROM estacion WHERE nombre = 'Pino Suárez' LIMIT 1),
    'Seguridad',
    'Personas sospechosas',
    NOW() - INTERVAL 1 DAY,
    TRUE
),
(
    2,
    (SELECT id_estacion FROM estacion WHERE nombre = 'Zaragoza' LIMIT 1),
    'Mantenimiento',
    'Escaleras eléctricas dañadas',
    NOW() - INTERVAL 3 HOUR,
    TRUE
);

INSERT INTO validacion (id_usuario, id_reporte, estado)
SELECT 2, id_reporte, TRUE FROM reporte WHERE descripcion = 'Basura en el andén' LIMIT 1;

INSERT INTO validacion (id_usuario, id_reporte, estado)
SELECT 3, id_reporte, TRUE FROM reporte WHERE descripcion = 'Basura en el andén' LIMIT 1;

INSERT INTO validacion (id_usuario, id_reporte, estado)
SELECT 1, id_reporte, TRUE FROM reporte WHERE descripcion = 'Personas sospechosas' LIMIT 1;

INSERT INTO validacion (id_usuario, id_reporte, estado)
SELECT 2, id_reporte, FALSE FROM reporte WHERE descripcion = 'Escaleras eléctricas dañadas' LIMIT 1;


ALTER TABLE validacion 
DROP FOREIGN KEY validacion_ibfk_1,
DROP FOREIGN KEY validacion_ibfk_2,
ADD CONSTRAINT fk_validacion_usuario FOREIGN KEY (id_usuario) REFERENCES usuario(id_usuario) ON DELETE CASCADE,
ADD CONSTRAINT fk_validacion_reporte FOREIGN KEY (id_reporte) REFERENCES reporte(id_reporte) ON DELETE CASCADE;
