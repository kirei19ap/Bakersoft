		/*BASE DE DATOS LOGIN*/

-- Crear la base de datos
CREATE DATABASE sistema_gestion CHARACTER SET utf8mb4 COLLATE utf8mb4_spanish_ci;


-- Usar la base de datos
USE sistema_gestion;

-- creamos tabla 

CREATE TABLE usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario VARCHAR(50) NOT NULL UNIQUE, -- nombre de usuario
    contrasena VARCHAR(255) NOT NULL,    -- contraseña cifrada
    fecha_creacion DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
);


-- insertamos datos

INSERT INTO usuarios (usuario, contrasena)
VALUES ('admin', MD5('admin123'));  

-- MD5 (CONTRASEÑA NO VISIBLE, EN CASO DE NO USAR SOLO LA BORRO)
