CREATE DATABASE IF NOT EXISTS kids_english_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE kids_english_db;

DROP TABLE IF EXISTS actividades_registro;
DROP TABLE IF EXISTS lecciones;
DROP TABLE IF EXISTS categorias;
DROP TABLE IF EXISTS usuarios;

CREATE TABLE categorias (
 id_categoria INT AUTO_INCREMENT PRIMARY KEY,
 nombre VARCHAR(80) NOT NULL UNIQUE,
 descripcion VARCHAR(255),
 icono VARCHAR(255),
 estado TINYINT(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE lecciones (
 id_leccion INT AUTO_INCREMENT PRIMARY KEY,
 id_categoria INT NOT NULL,
 palabra_espanol VARCHAR(80) NOT NULL,
 palabra_ingles VARCHAR(80) NOT NULL,
 pronunciacion VARCHAR(80),
 ruta_imagen VARCHAR(255),
 ruta_audio VARCHAR(255),
 orden INT DEFAULT 0,
 estado TINYINT(1) DEFAULT 1,
 FOREIGN KEY (id_categoria) REFERENCES categorias(id_categoria) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE actividades_registro (
 id_registro INT AUTO_INCREMENT PRIMARY KEY,
 id_leccion INT NOT NULL,
 aciertos INT DEFAULT 0,
 estrellas INT DEFAULT 0,
 fecha_interaccion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
 FOREIGN KEY (id_leccion) REFERENCES lecciones(id_leccion) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE usuarios (
 id_usuario INT AUTO_INCREMENT PRIMARY KEY,
 usuario VARCHAR(50) NOT NULL UNIQUE,
 password VARCHAR(255) NOT NULL,
 nombre VARCHAR(100) NOT NULL,
 creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO usuarios(usuario,password,nombre) VALUES
('admin','$2y$12$nXGus.iHWey9cSWvpRRoxOV/OygLnpwCrnfzlep.M2sadYeLyaEt2','Administrador');
-- contraseña: admin123

INSERT INTO categorias(nombre,descripcion,icono) VALUES
('Colores','Aprende los colores primarios en inglés','assets/img/categorias/colores.jpg'),
('Números','Aprende a contar del 1 al 10 en inglés','assets/img/categorias/numeros.jpg'),
('Vocales','Aprende las vocales en inglés','assets/img/categorias/vocales.jpg');

INSERT INTO lecciones(id_categoria,palabra_espanol,palabra_ingles,pronunciacion,ruta_imagen,ruta_audio,orden) VALUES
(1,'Rojo','Red','red','assets/img/colores/red.jpg','assets/audio/colores/red.wav',1),
(1,'Azul','Blue','blu','assets/img/colores/blue.jpg','assets/audio/colores/blue.wav',2),
(1,'Amarillo','Yellow','iélou','assets/img/colores/yellow.jpg','assets/audio/colores/yellow.wav',3),
(2,'Uno','One','uan','assets/img/numeros/one.jpg','assets/audio/numeros/one.wav',1),
(2,'Dos','Two','tu','assets/img/numeros/two.jpg','assets/audio/numeros/two.wav',2),
(2,'Tres','Three','zrii','assets/img/numeros/three.jpg','assets/audio/numeros/three.wav',3),
(2,'Cuatro','Four','for','assets/img/numeros/four.jpg','assets/audio/numeros/four.wav',4),
(2,'Cinco','Five','faiv','assets/img/numeros/five.jpg','assets/audio/numeros/five.wav',5),
(2,'Seis','Six','siks','assets/img/numeros/six.jpg','assets/audio/numeros/six.wav',6),
(2,'Siete','Seven','se-ven','assets/img/numeros/seven.jpg','assets/audio/numeros/seven.wav',7),
(2,'Ocho','Eight','eit','assets/img/numeros/eight.jpg','assets/audio/numeros/eight.wav',8),
(2,'Nueve','Nine','nain','assets/img/numeros/nine.jpg','assets/audio/numeros/nine.wav',9),
(2,'Diez','Ten','ten','assets/img/numeros/ten.jpg','assets/audio/numeros/ten.wav',10),
(3,'A','A','ei','assets/img/vocales/a.jpg','assets/audio/vocales/a.wav',1),
(3,'E','E','ii','assets/img/vocales/e.jpg','assets/audio/vocales/e.wav',2),
(3,'I','I','ai','assets/img/vocales/i.jpg','assets/audio/vocales/i.wav',3),
(3,'O','O','ou','assets/img/vocales/o.jpg','assets/audio/vocales/o.wav',4),
(3,'U','U','iu','assets/img/vocales/u.jpg','assets/audio/vocales/u.wav',5);
