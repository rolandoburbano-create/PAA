CREATE DATABASE IF NOT EXISTS alcaldia_paa CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE alcaldia_paa;

CREATE TABLE IF NOT EXISTS usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    nombre_completo VARCHAR(150) NOT NULL,
    dependencia VARCHAR(150) NOT NULL COMMENT 'Secretaría u Oficina a cargo',
    rol ENUM('funcionario','supervisor','admin') DEFAULT 'funcionario'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS adquisiciones_paa (
    id INT AUTO_INCREMENT PRIMARY KEY,
    codigos_unspsc VARCHAR(50) NOT NULL,
    descripcion TEXT NOT NULL,
    fecha_estimada_inicio VARCHAR(50) NOT NULL,
    duracion_meses DECIMAL(5,1) NOT NULL,
    modalidad_seleccion VARCHAR(100) NOT NULL,
    fuente_recursos VARCHAR(200) NOT NULL,
    valor_total_estimado DECIMAL(15,2) NOT NULL,
    valor_vigencia_actual DECIMAL(15,2) NOT NULL,
    requiere_vigencias_futuras VARCHAR(5) NOT NULL DEFAULT 'NO',
    estado_vigencias_futuras VARCHAR(30) NOT NULL DEFAULT 'NA',
    contacto_responsable VARCHAR(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS notificaciones_sistema (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario VARCHAR(255) NOT NULL,
    accion VARCHAR(50) NOT NULL,
    detalle TEXT NOT NULL,
    leido TINYINT(1) NOT NULL DEFAULT 0,
    creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Usuario administrador global (contraseña: admin123)
INSERT INTO usuarios (username, password, nombre_completo, dependencia, rol) VALUES
('admin', '$2y$10$T4N4./ilwLdfBInlL5cJLOO8iXSX.ldAPfMS9PYJK.bVeaBha6R.W', 'Administrador', 'Administración', 'admin');

-- Usuarios por dependencia (contraseña por defecto: 123456)
INSERT INTO usuarios (username, password, nombre_completo, dependencia, rol) VALUES
('gobierno', '$2y$10$Jptblb8M1dOzTWns9fCcZeNuGSRtajJ7H5MZG71LMgIwwge.wbqTG', 'Secretaría de Gobierno y Participación Ciudadana', 'Secretaría de Gobierno y Participación Ciudadana', 'funcionario'),
('administrativa', '$2y$10$Jptblb8M1dOzTWns9fCcZeNuGSRtajJ7H5MZG71LMgIwwge.wbqTG', 'Secretaría Administrativa y Financiera', 'Secretaría Administrativa y Financiera', 'funcionario'),
('planeacion', '$2y$10$Jptblb8M1dOzTWns9fCcZeNuGSRtajJ7H5MZG71LMgIwwge.wbqTG', 'Oficina Asesora de Planeación', 'Oficina Asesora de Planeación', 'funcionario'),
('infraestructura', '$2y$10$Jptblb8M1dOzTWns9fCcZeNuGSRtajJ7H5MZG71LMgIwwge.wbqTG', 'Secretaría de Infraestructura', 'Secretaría de Infraestructura', 'funcionario'),
('bienestar', '$2y$10$Jptblb8M1dOzTWns9fCcZeNuGSRtajJ7H5MZG71LMgIwwge.wbqTG', 'Secretaría de Bienestar y Desarrollo Social', 'Secretaría de Bienestar y Desarrollo Social', 'funcionario'),
('productivo', '$2y$10$Jptblb8M1dOzTWns9fCcZeNuGSRtajJ7H5MZG71LMgIwwge.wbqTG', 'Secretaría de Desarrollo Productivo y Ambiental', 'Secretaría de Desarrollo Productivo y Ambiental', 'funcionario'),
('juridica', '$2y$10$Jptblb8M1dOzTWns9fCcZeNuGSRtajJ7H5MZG71LMgIwwge.wbqTG', 'Oficina Asesora Jurídica', 'Oficina Asesora Jurídica', 'funcionario'),
('concejo', '$2y$10$Jptblb8M1dOzTWns9fCcZeNuGSRtajJ7H5MZG71LMgIwwge.wbqTG', 'Concejo Municipal', 'Concejo Municipal', 'funcionario'),
('personeria', '$2y$10$Jptblb8M1dOzTWns9fCcZeNuGSRtajJ7H5MZG71LMgIwwge.wbqTG', 'Personería Municipal', 'Personería Municipal', 'funcionario');
