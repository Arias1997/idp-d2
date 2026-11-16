-- =============================================================
--  IDP D-2 · Iglesia de Dios de la Profecía · Distrito 2 Sacaba
--  Base de datos para MySQL 8 (también funciona en MariaDB 10.4+)
--
--  Importar (crea la base idp_d2 con tablas y datos de ejemplo):
--      mysql -u root -p < database/idp_d2.sql
--  o en phpMyAdmin / MySQL Workbench: abrir este archivo y ejecutarlo.
--
--  Cuentas de prueba:
--      Administrador: admin@idp.bo    / Admin12345
--      Usuario:       usuario@idp.bo  / Usuario12345
-- =============================================================

CREATE DATABASE IF NOT EXISTS idp_d2
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;
USE idp_d2;

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS transmision_red;
DROP TABLE IF EXISTS transmisiones;
DROP TABLE IF EXISTS redes;
DROP TABLE IF EXISTS usuarios;
DROP TABLE IF EXISTS roles;
DROP TABLE IF EXISTS actividades;
DROP TABLE IF EXISTS grupos;
DROP TABLE IF EXISTS iglesias;
DROP TABLE IF EXISTS zonas;

SET FOREIGN_KEY_CHECKS = 1;

-- -------------------------------------------------------------
--  Estructura
-- -------------------------------------------------------------

-- Sectores del distrito
CREATE TABLE zonas (
    id      BIGINT       NOT NULL AUTO_INCREMENT,
    nombre  VARCHAR(100) NOT NULL,
    PRIMARY KEY (id),
    UNIQUE KEY zonas_nombre_key (nombre)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Iglesias (cada una pertenece a una zona)
CREATE TABLE iglesias (
    id         BIGINT        NOT NULL AUTO_INCREMENT,
    zona_id    BIGINT        NOT NULL,
    nombre     VARCHAR(150)  NOT NULL,
    direccion  VARCHAR(200)  NOT NULL,
    latitud    DECIMAL(10,7) NULL,
    longitud   DECIMAL(10,7) NULL,
    pastor     VARCHAR(120)  NULL,
    telefono   VARCHAR(25)   NULL,
    horarios   TEXT          NULL,
    activa     TINYINT(1)    NOT NULL DEFAULT 1,
    imagen     VARCHAR(255)  NULL,
    PRIMARY KEY (id),
    KEY iglesias_zona_id_idx (zona_id),
    CONSTRAINT iglesias_latitud_check  CHECK (latitud  BETWEEN -90  AND 90),
    CONSTRAINT iglesias_longitud_check CHECK (longitud BETWEEN -180 AND 180),
    CONSTRAINT iglesias_zona_id_fkey FOREIGN KEY (zona_id) REFERENCES zonas (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Actividades de cada iglesia (reservada para una próxima versión)
CREATE TABLE actividades (
    id           BIGINT       NOT NULL AUTO_INCREMENT,
    iglesia_id   BIGINT       NOT NULL,
    titulo       VARCHAR(150) NOT NULL,
    descripcion  TEXT         NULL,
    fecha_hora   DATETIME     NOT NULL,
    PRIMARY KEY (id),
    KEY actividades_iglesia_id_idx (iglesia_id),
    CONSTRAINT actividades_iglesia_id_fkey FOREIGN KEY (iglesia_id) REFERENCES iglesias (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Grupos de cada iglesia (reservada para una próxima versión)
CREATE TABLE grupos (
    id           BIGINT       NOT NULL AUTO_INCREMENT,
    iglesia_id   BIGINT       NOT NULL,
    nombre       VARCHAR(100) NOT NULL,
    descripcion  TEXT         NULL,
    PRIMARY KEY (id),
    KEY grupos_iglesia_id_idx (iglesia_id),
    CONSTRAINT grupos_iglesia_id_fkey FOREIGN KEY (iglesia_id) REFERENCES iglesias (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Roles del sistema: administrador y usuario
CREATE TABLE roles (
    id      BIGINT      NOT NULL AUTO_INCREMENT,
    nombre  VARCHAR(30) NOT NULL,
    PRIMARY KEY (id),
    UNIQUE KEY roles_nombre_key (nombre)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Cuentas (la contraseña se guarda con password_hash, nunca en texto)
CREATE TABLE usuarios (
    id          BIGINT       NOT NULL AUTO_INCREMENT,
    rol_id      BIGINT       NOT NULL,
    iglesia_id  BIGINT       NULL,
    nombre      VARCHAR(120) NOT NULL,
    email       VARCHAR(150) NOT NULL,
    password    VARCHAR(255) NOT NULL,
    activo      TINYINT(1)   NOT NULL DEFAULT 1,
    foto        VARCHAR(255) NULL,
    PRIMARY KEY (id),
    UNIQUE KEY usuarios_email_key (email),
    KEY usuarios_rol_id_idx (rol_id),
    KEY usuarios_iglesia_id_idx (iglesia_id),
    CONSTRAINT usuarios_rol_id_fkey FOREIGN KEY (rol_id) REFERENCES roles (id),
    CONSTRAINT usuarios_iglesia_id_fkey FOREIGN KEY (iglesia_id) REFERENCES iglesias (id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Redes sociales donde se publica una transmisión
CREATE TABLE redes (
    id      BIGINT      NOT NULL AUTO_INCREMENT,
    nombre  VARCHAR(60) NOT NULL,
    PRIMARY KEY (id),
    UNIQUE KEY redes_nombre_key (nombre)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Cultos transmitidos en vivo
CREATE TABLE transmisiones (
    id          BIGINT       NOT NULL AUTO_INCREMENT,
    iglesia_id  BIGINT       NOT NULL,
    titulo      VARCHAR(150) NOT NULL,
    fecha_hora  DATETIME     NOT NULL,
    estado      VARCHAR(20)  NOT NULL DEFAULT 'programada',
    PRIMARY KEY (id),
    KEY transmisiones_iglesia_id_idx (iglesia_id),
    CONSTRAINT transmisiones_estado_check CHECK (estado IN ('programada', 'en_vivo', 'finalizada')),
    CONSTRAINT transmisiones_iglesia_id_fkey FOREIGN KEY (iglesia_id) REFERENCES iglesias (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Enlace de cada transmisión en cada red (relación N:M)
CREATE TABLE transmision_red (
    transmision_id  BIGINT       NOT NULL,
    red_id          BIGINT       NOT NULL,
    url             VARCHAR(500) NOT NULL,
    PRIMARY KEY (transmision_id, red_id),
    KEY transmision_red_red_id_idx (red_id),
    CONSTRAINT transmision_red_transmision_id_fkey FOREIGN KEY (transmision_id) REFERENCES transmisiones (id) ON DELETE CASCADE,
    CONSTRAINT transmision_red_red_id_fkey FOREIGN KEY (red_id) REFERENCES redes (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------------------------
--  Datos de ejemplo
-- -------------------------------------------------------------

INSERT INTO zonas (id, nombre) VALUES
    (1, 'Sacaba'),
    (2, 'Zona el Abra'),
    (3, 'Zona Guadalupe');

INSERT INTO iglesias (id, zona_id, nombre, direccion, latitud, longitud, pastor, telefono, horarios, activa, imagen) VALUES
    (1, 3, 'IDP-Guadalupe', 'Zona Guadalupe', -17.3826979, -66.0744487, 'Pastor de ejemplo 1', '+591 70000001',
        'Domingo 10:00 am\nDomingo 19:30 pm\nMartes 19:30 pm\nJueves 19:30 pm\nSábado 19:30 pm', 1, NULL),
    (2, 2, 'IDP-Abra', 'OTB/La guardia', -17.4138286, -66.0900168, 'Pastor de ejemplo 2', '+591 70000002',
        'Domingo 10:00 am\nDomingo 19:30 pm\nMartes 19:30 pm\nJueves 19:30 pm\nSábado 19:30 pm', 1, NULL),
    (3, 1, 'IDP-Miraflores', 'Av. Villazon km. 12', -17.3980338, -66.0457705, 'Pastor de ejemplo 3', '+591 70000003',
        'Domingo 10:00 am\nDomingo 19:30 pm\nMartes 19:30 pm\nJueves 19:30 pm\nSábado 19:30 pm', 1, NULL);

INSERT INTO roles (id, nombre) VALUES
    (1, 'administrador'),
    (2, 'usuario');

-- Contraseñas: Admin12345 y Usuario12345 (hash bcrypt generado con password_hash)
INSERT INTO usuarios (id, rol_id, iglesia_id, nombre, email, password, activo, foto) VALUES
    (1, 1, NULL, 'Administrador General', 'admin@idp.bo',
        '$2y$10$6kEtv86korugXBQvKci7m.wj7Kj3ptfRvkQHV/MHHnU9PEwy57tZ6', 1, NULL),
    (2, 2, NULL, 'Usuario de Prueba', 'usuario@idp.bo',
        '$2y$10$IYNwJY2OPfG3aGAJk00o1OQgXg897wBAmszJLj/xqvvlk6Gegd3u6', 1, NULL);

INSERT INTO redes (id, nombre) VALUES
    (1, 'YouTube'),
    (2, 'Facebook'),
    (3, 'TikTok');

INSERT INTO transmisiones (id, iglesia_id, titulo, fecha_hora, estado) VALUES
    (1, 1, 'Culto de adoración', '2026-11-06 15:25:00', 'finalizada'),
    (2, 2, 'Transmisión especial', '2026-11-09 18:30:00', 'finalizada');

INSERT INTO transmision_red (transmision_id, red_id, url) VALUES
    (1, 1, 'https://www.youtube.com/watch?v=fy_fYd3ZwPE'),
    (2, 1, 'https://www.youtube.com/watch?v=-rnXw-YpjxA');

-- Los próximos registros continúan después de los datos de ejemplo
ALTER TABLE zonas         AUTO_INCREMENT = 4;
ALTER TABLE iglesias      AUTO_INCREMENT = 4;
ALTER TABLE roles         AUTO_INCREMENT = 3;
ALTER TABLE usuarios      AUTO_INCREMENT = 3;
ALTER TABLE redes         AUTO_INCREMENT = 4;
ALTER TABLE transmisiones AUTO_INCREMENT = 3;
